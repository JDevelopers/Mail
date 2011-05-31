<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));
	
	require_once(WM_ROOTPATH.'common/class_settings.php');
	require_once(WM_ROOTPATH.'common/class_domains.php');
	require_once(WM_ROOTPATH.'common/class_tempfiles.php');
	require_once(WM_ROOTPATH.'common/class_log.php');
	
	define('ACTION_Remove', 0);
	define('ACTION_Set', 1);
	
	define('FOLDER_LIST_INDEX_DELIMITR', '#$%$#');
	
	/**
	 * @abstract
	 */
	class MailStorage
	{
		/**
		 * @var MailProcessor
		 */		
		var $mailproc = null; 
		
		/**
		 * @access protected
		 * @var Account
		 */
		var $Account;
	
		/**
		 * @access protected
		 * @var Settings
		 */
		var $_settings;
	
		/**
		 * @access protected
		 * @var CLog
		 */
		var $_log;
		
		/**
		 * @access protected
		 * @var resource
		 */
		var $_connectionHandle = null;
		
		/**
		 * @var string
		 */
		var $DownloadedMessagesHandler = null;
		
		/**
		 * @var string
		 */
		var $ShowDeletingMessageNumber = null;

		/**
		 * @var string
		 */
		var $UpdateFolderHandler = null;
		
		/**
		 * @param Account $account
		 * @return MailStorage
		 */
		function MailStorage(&$account, $settings = null)
		{
			if (null === $settings)
			{
				$this->_settings =& Settings::CreateInstance();
			}
			else
			{
				$this->_settings =& $settings;
			}
			$this->_log =& CLog::CreateInstance($this->_settings);
			$this->Account =& $account;
		}
		
		/**
		 * @param WebMailMessage $message
		 * @param DbStorage $dbStorage
		 * @param Folder $folder
		 * @return bool
		 */	
		function ApplyFilters(&$message, &$dbStorage, &$folder, &$filters)
		{
			$result = true;
			$needToSave = true;
			
			if ($folder->Type == FOLDERTYPE_Inbox && $result && isset($GLOBALS['useFilters']))
			{
				$mailProcessor = null;
				if (null == $this->mailproc)
				{
					$this->mailproc = new MailProcessor($this->Account);
				}

				$mailProcessor =& $this->mailproc;
				
				$messageIdUidSet = array($message->IdMsg => $message->Uid);
				
				$filtersKeys = array_keys($filters->Instance());
				foreach ($filtersKeys as $key)
				{
					$filter =& $filters->Get($key);
					$action = $filter->GetActionToApply($message);
	
					switch ($action)
					{
						case FILTERACTION_DeleteFromServerImmediately:
							$result &= $mailProcessor->DeleteFromServerImmediately($messageIdUidSet, $folder);
							$needToSave = false;
							break 2;

						case FILTERACTION_MoveToSpamFolder:
							$folders = $mailProcessor->GetFolders();
							$spamFolder = $folders->GetFolderByType(FOLDERTYPE_Spam);
							if ($spamFolder && $spamFolder->IdDb)
							{
								$filter->IdAcct = $this->Account->Id;
								$filter->IdFolder = $spamFolder->IdDb;
							}
							else
							{
								break;
							}
							
						case FILTERACTION_MoveToFolder:
							if ($filter->IdFolder != $folder->IdDb)
							{
								if ($folder->SyncType == FOLDERSYNC_NewEntireMessages || $folder->SyncType == FOLDERSYNC_AllEntireMessages)
								{
									$result &= $dbStorage->SaveMessage($message, $folder);
								}
								else if ($folder->SyncType == FOLDERSYNC_NewHeadersOnly || $folder->SyncType == FOLDERSYNC_AllHeadersOnly)
								{
									$result &= $dbStorage->SaveMessageHeader($message, $folder, false);
								}
								
								$messageIdUidSet = array($message->IdMsg => $message->Uid);
							
								if ($result)
								{
									$needToSave = false;
									$toFolder = new Folder($filter->IdAcct, $filter->IdFolder, '');
									$dbStorage->GetFolderInfo($toFolder);

									$_tDowmloaded = $this->DownloadedMessagesHandler;
									$_tDeleting = $this->ShowDeletingMessageNumber;

									$this->DownloadedMessagesHandler = $this->ShowDeletingMessageNumber = null;

									$result &= $mailProcessor->MoveMessages($messageIdUidSet, $folder, $toFolder);

									$this->DownloadedMessagesHandler = $_tDowmloaded;
									$this->ShowDeletingMessageNumber = $_tDeleting;
									
									if ($this->UpdateFolderHandler != null)
									{
										call_user_func_array($this->UpdateFolderHandler, array($toFolder->IdDb, $toFolder->FullName));
									}
								}
								else 
								{
									if ($this->UpdateFolderHandler != null)
									{
										call_user_func_array($this->UpdateFolderHandler, array($folder->IdDb, $folder->FullName));
									}
								}
							}
							break 2;
							
						case FILTERACTION_MarkGrey:
							$result &= $mailProcessor->SetFlags($messageIdUidSet, $folder, MESSAGEFLAGS_Grayed, ACTION_Set, false);
							$message->Flags |= MESSAGEFLAGS_Grayed;
							break;
					}
					
					unset($filter);
				}
			}
			
			if ($needToSave)
			{
				if ($folder->SyncType == FOLDERSYNC_NewEntireMessages || $folder->SyncType == FOLDERSYNC_AllEntireMessages)
				{
					$result &= $dbStorage->SaveMessage($message, $folder);
					if ($this->UpdateFolderHandler != null)
					{
						call_user_func_array($this->UpdateFolderHandler, array($folder->IdDb, $folder->FullName));
					}
				}
				else if ($folder->SyncType == FOLDERSYNC_NewHeadersOnly || $folder->SyncType == FOLDERSYNC_AllHeadersOnly)
				{
					$result &= $dbStorage->SaveMessageHeader($message, $folder, false);
					if ($this->UpdateFolderHandler != null)
					{
						call_user_func_array($this->UpdateFolderHandler, array($folder->IdDb, $folder->FullName));
					}
				}
			}
			
			return $result;
		}
	
		function GetFolderCollectionFromArrays($folders, $subScrFolders, $seporator, $existsIndex)
		{
			$newFolderArray = array();
			foreach ($folders as $folder)
			{
				$p = null;
				$fullName = array();
				$temp = array();
				$p =& $temp;
				$seporatedNames = explode($seporator, $folder);
				foreach ($seporatedNames as $name)
				{
					$fullName[] = $name;
					$name .= FOLDER_LIST_INDEX_DELIMITR.implode($seporator, $fullName);
					$temp[$name] = null;
					$temp =& $temp[$name];	
				}
				
				$newFolderArray = array_merge_recursive($newFolderArray, $p);
				unset($p, $temp, $fullName);
			}
			
			$folderCollection = new FolderCollection();
			$this->_recFillFolderCollection($folderCollection, $newFolderArray, $subScrFolders, $existsIndex);
			
			return $folderCollection;
		}
	
		function _recFillFolderCollection(&$folderCollection, $folders, $subScrFolders, &$existsIndex, $isInbox = false)
		{
			foreach ($folders as $folder => $subFolders)
			{
				$folderName = $folderFullName = null;
				$tArray = explode(FOLDER_LIST_INDEX_DELIMITR, $folder);
				if (count($tArray) != 2)
				{
					continue;
				}
				
				$folderName = $tArray[0];
				$folderFullName = $tArray[1];
				
				$folderObj = new Folder($this->Account->Id, -1, $folderFullName, $folderName);
							
				if ($isInbox || $folderName == $folderFullName)
				{
					$this->SetFolderType($folderObj, $existsIndex);
				}
				else 
				{
					$folderObj->Type = FOLDERTYPE_Custom;
				}
				
				$folderObj->Hide = (defined('USE_LSUB') && USE_LSUB) ? false : !in_array($folderObj->FullName, $subScrFolders);
				if ($folderObj->Type != FOLDERTYPE_Custom)
				{
					$folderObj->Hide = false;
				}
				
				if (null !== $subFolders && is_array($subFolders))
				{
					$newCollection = new FolderCollection();
					
					$this->_recFillFolderCollection($newCollection, $subFolders, $subScrFolders, $existsIndex, $folderObj->Type == FOLDERTYPE_Inbox);
					
					if ($newCollection->Count() > 0)
					{
						$folderObj->SubFolders = $newCollection;
					}
					
					unset($newCollection);
				}
				
				$folderCollection->Add($folderObj);
				unset($folderObj);
			}
		}
	
		function SetFolderType(&$folderObj, &$existsIndex)
		{
			$folderObj->Type = FOLDERTYPE_Custom;
		}
	}
	
	/**
	 * @abstract
	 */
	class MailServerStorage extends MailStorage
	{
		/**
		 * @param Account $account
		 * @return MailServerStorage
		 */
		function MailServerStorage(&$account, $settings = null)
		{
			MailStorage::MailStorage($account, $settings);
		}
	}
	
	/**
	 * @static
	 */
	class DbStorageCreator
	{
		/**
		 * @param Account $account
		 * @return MySqlStorage
		 */
		function &CreateDatabaseStorage(&$account, $settings = null)
		{
			/**
			 * @var DbStorage
			 */
			static $instance;
			
    		if (is_object($instance))
    		{
    			if ($account)
    			{
    				$instance->Account = $account;	
    			}
    			return $instance;
    		}
			
			require_once(WM_ROOTPATH.'common/class_dbstorage.php');

			if (null === $settings)
			{
				$settings =& Settings::CreateInstance();
			}
			
			switch ($settings->DbType)
			{
				case DB_MSSQLSERVER:
					$instance = new MsSqlStorage($account, $settings);
					break;
				default:
				case DB_MYSQL:
					$instance = new MySqlStorage($account, $settings);
					break;
			}
    		
			if ($account)
    		{
    			$instance->Account = $account;	
    		}
			return $instance;
		}
	}
