<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));

	require_once(WM_ROOTPATH.'common/class_collectionbase.php');
	require_once(WM_ROOTPATH.'common/class_systemfolders.php');
	
	define('FOLDERTYPE_Inbox', 1);
	define('FOLDERTYPE_SentItems', 2);
	define('FOLDERTYPE_Drafts', 3);
	define('FOLDERTYPE_Trash', 4);
	define('FOLDERTYPE_Spam', 5);
	define('FOLDERTYPE_Virus', 6);
	define('FOLDERTYPE_System', 9);
	define('FOLDERTYPE_Custom', 10);

	define('FOLDERSYNC_DontSync', 0);
	define('FOLDERSYNC_NewHeadersOnly', 1);
	define('FOLDERSYNC_AllHeadersOnly', 2);
	define('FOLDERSYNC_NewEntireMessages', 3);
	define('FOLDERSYNC_AllEntireMessages', 4);
	define('FOLDERSYNC_DirectMode', 5);
	
	define('FOLDERNAME_Inbox', 'Inbox');
	define('FOLDERNAME_SentItems', 'Sent Items');
	define('FOLDERNAME_Sent', 'Sent');
	define('FOLDERNAME_Sent_Items', 'Sent-Items');
	define('FOLDERNAME_Drafts', 'Drafts');
	define('FOLDERNAME_Trash', 'Trash');
	define('FOLDERNAME_Spam', 'Spam');
	define('FOLDERNAME_Virus', 'Quarantine');

	define('FOLDERNAME_SharedSpam', 'blacklist');
	define('FOLDERNAME_SharedUnSpam', 'whitelist');

	define('FOLDERFULLNAME_SharedSpam', 'shared.spam.blacklist');
	define('FOLDERFULLNAME_SharedUnSpam', 'shared.spam.whitelist');

	class Folder
	{
		/**
		 * @var int
		 */
		var $IdDb;
		
		/**
		 * @var int
		 */
		var $IdAcct;
		
		/**
		 * @var int
		 */
		var $IdParent = -1;
		
		/**
		 * @var short
		 */
		var $Type;

		/**
		 * @var string
		 */
		var $Name;
		
		/**
		 * @var string
		 */
		var $FullName;
		
		/**
		 * @var short
		 */
		var $SyncType;
		
		/**
		 * @var bool
		 */
		var $Hide = false;
		
		/**
		 * @var int
		 */
		var $FolderOrder;
		
		/**
		 * @var int
		 */
		var $MessageCount = 0;

		/**
		 * @var int
		 */
		var $UnreadMessageCount = 0;
		
		/**
		 * @var int
		 */
		var $Size = 0;
		
		/**
		 * @var FolderCollection
		 */
		var $SubFolders = null;
		
		/**
		 * @var int
		 */
		var $Level;
		
		/**
		 * @var bool
		 */
		var $ToFolder = false;
		
		/**
		 * @param string $name
		 * @param string $fullName
		 * @param string $name optional
		 * @return Folder
		 */
		function Folder($idAcct, $idDb, $fullName, $name = null, $syncType = FOLDERSYNC_DontSync, $forceType = null)
		{
			$this->IdAcct = (int) $idAcct;
			$this->IdDb = (int) $idDb;
			$this->FullName = $fullName;

			if ($name != null)
			{
				$this->Name = $name;
				
				$this->SyncType = $syncType;
				
				if (null !== $forceType)
				{
					$this->Type = $forceType;
				}
				else 
				{
					switch(strtolower($name))
					{
						case strtolower(FOLDERNAME_Inbox):
							$this->Type = FOLDERTYPE_Inbox;
							break;
						case strtolower(FOLDERNAME_Sent):
						case strtolower(FOLDERNAME_SentItems):
						case strtolower(FOLDERNAME_Sent_Items):
							$this->Type = FOLDERTYPE_SentItems;
							break;
						case strtolower(FOLDERNAME_Drafts):
							$this->Type = FOLDERTYPE_Drafts;
							break;
						case strtolower(FOLDERNAME_Trash):
							$this->Type = FOLDERTYPE_Trash;
							break;
						case strtolower(FOLDERNAME_Spam):
							$this->Type = FOLDERTYPE_Spam;
							break;
						case strtolower(FOLDERNAME_Virus):
							$this->Type = FOLDERTYPE_Virus;
							break;
						default:
							$this->Type = FOLDERTYPE_Custom;
					}
				}
			}
		}
		
		/**
		 * @return string/bool
		 */
		function ValidateData()
		{
			if (empty($this->Name))
			{
				return JS_LANG_WarningEmptyFolderName;
			}
			elseif(!ConvertUtils::CheckDefaultWordsFileName($this->Name) || Validate::HasSpecSymbols($this->Name))
			{
				return WarningCorrectFolderName;
			}
			
			return true;	
		}

		function GetFolderName($account)
		{
			$foldername = $this->Name;
			switch($this->Type)
			{
				case FOLDERTYPE_Inbox:
					$foldername = FolderInbox;
					break;
				case FOLDERTYPE_SentItems:
					$foldername = FolderSentItems;
					break;
				case FOLDERTYPE_Drafts:
					$foldername = FolderDrafts;
					break;
				case FOLDERTYPE_Trash:
					$foldername = FolderTrash;
					break;
				case FOLDERTYPE_Spam:
					$foldername = FolderSpam;
					break;
				case FOLDERTYPE_Virus:
					$foldername = FolderQuarantine;
					break;
				default:
					$foldername = ConvertUtils::IsLatin($this->Name)
						? ConvertUtils::ConvertEncoding($this->Name, CPAGE_UTF7_Imap, $account->GetUserCharset())
						: ConvertUtils::ConvertEncoding($this->Name, $account->DefaultIncCharset, $account->GetUserCharset());
					break;
			}
			
			return $foldername;
		}

		function GetFolderFullName($account)
		{
			return ConvertUtils::IsLatin($this->FullName)
				? ConvertUtils::ConvertEncoding($this->FullName, CPAGE_UTF7_Imap, $account->GetUserCharset())
				: ConvertUtils::ConvertEncoding($this->FullName, $account->DefaultIncCharset, $account->GetUserCharset());
		}
		
		function SetFolderSync($syncType = FOLDERSYNC_DontSync)
		{
			$this->SyncType = $syncType;
		}
	}
	
	class FolderCollection extends CollectionBase
	{
		function FolderCollection()
		{
			CollectionBase::CollectionBase();
		}
		
		/**
		 * @param Folder $folder
		 */
		function Add(&$folder)
		{
			$this->List->Add($folder);
		}
		
		/**
		 * @param Folder $folder
		 */
		function AddCopy($folder)
		{
			$this->List->Add($folder);
		}
		
		/**
		 * @param int $index
		 * @return Folder
		 */
		function &Get($index)
		{
			return $this->List->Get($index);
		}
		
		/**
		 * @param short $type
		 * @return Folder
		 */
		function &GetFolderByType($type)
		{
			$null = null;
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder =& $this->Get($i);
				if ($folder->Type == $type)
				{
					return $folder;
				}
				if ($folder->Type == FOLDERTYPE_Inbox && $folder->SubFolders != null)
				{
					$inboxSubFolder =& $folder->SubFolders->GetFolderByType($type);
					if ($inboxSubFolder)
					{
						return $inboxSubFolder;
					}
				}
				unset($folder);
			}

			return $null;
		}
		
		function InitSystemFolders($account)
		{
			$systemFolders = SystemFolders::StaticGetSystemFoldersNames();
			$systemFolders = array_map('strtolower', $systemFolders);

			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder =& $this->Get($i);
				if ($folder->Type == FOLDERTYPE_Custom)
				{
					$loverCaseName = strtolower($folder->GetFolderFullName($account));
					if (in_array($loverCaseName, $systemFolders))
					{
						$folder->Type = FOLDERTYPE_System;
					}
				}
				
				if ($folder->SubFolders != null)
				{
					$folder->SubFolders->InitSystemFolders($account);
				}
			}
		}

	
		/**
		 * @param string $name
		 * @return Folder
		 */
		function &GetFolderByName($name)
		{
			$null = null;
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder =& $this->Get($i);
				if ($folder->Name == $name)
				{
					return $folder;
				}
			}

			return $null;
		}
		
		/**
		 * @param short $type
		 * @return Folder
		 */
		function &GetFirstNotHideFolder()
		{
			$null = null;
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder = &$this->Get($i);
				if (!$folder->Hide)
				{
					return $folder;
				}
			}

			return $null;
		}
		
		/**
		 * @param int $type
		 * @return Folder
		 */
		function &GetFolderById($id)
		{
			$folders =& $this->CreateFolderListFromTree();
			$null = null;
			for ($i = 0, $c = $folders->Count(); $i < $c; $i++)
			{
				$curfolder =& $folders->Get($i);
				if ($curfolder->IdDb == $id)
				{
					return $curfolder;
				}
			}

			return $null;
		}
		
		/**
		 * @return FolderCollection
		 */
		function &CreateFolderListFromTree()
		{
			$folderList = new FolderCollection();
			$this->_createFolderListFromTree($folderList);
			return $folderList;
		}

		/**
		 * @access private
		 * @param FolderCollection $folderList
		 */
		function _createFolderListFromTree(&$folderList)
		{
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder =& $this->Get($i);
				$folderList->Add($folder);
				if ($folder->SubFolders !== null && $folder->SubFolders->Count() > 0)
				{
					$folder->SubFolders->_createFolderListFromTree($folderList);
				}
				unset($folder);
			}
		}
		
		function SaveToSession($folders)
		{
			$_SESSION[ACCOUNT_FOLDERS] = serialize($folders);
		}
		
		function GetFromSession()
		{
			return (isset($_SESSION[ACCOUNT_FOLDERS])) ? unserialize($_SESSION[ACCOUNT_FOLDERS]) : null;
		}
		
		function SetSyncTypeToAll($syncType)
		{
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder =& $this->Get($i);
				$folder->SyncType = $syncType;
				
				if (!is_null($folder->SubFolders) && $folder->SubFolders->Count() > 0)
				{
					$folder->SubFolders->SetSyncTypeToAll($syncType);
				}
			}			
		}
		
		function GetDMFolderCountsToAll(&$mailStorage)
		{
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder =& $this->Get($i);
				if ($folder && $folder->SyncType == FOLDERSYNC_DirectMode)
				{
					$mailStorage->GetFolderMessageCount($folder);
				}
				if (!is_null($folder->SubFolders) && $folder->SubFolders->Count() > 0)
				{
					$folder->SubFolders->GetDMFolderCountsToAll($mailStorage);
				}
			}			
		}
		
		function SetDMFolderIds($acctId, $start = true, $parentId = -1)
		{
			static $f_ids;
			if ($start)
			{
				$f_ids = 1;
			}
			
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder =& $this->Get($i);
				$folder->IdAcct = $acctId;
				$folder->IdDb = ++$f_ids;
				$folder->IdParent = $parentId;
				
				if (!is_null($folder->SubFolders) && $folder->SubFolders->Count() > 0)
				{
					$folder->SubFolders->SetDMFolderIds($acctId, false, $folder->IdDb);
				}
			}			
		}
		
		/**
		 * @param Folder $folder
		 */
		function InitToFolder(&$folder)
		{
			$sent =& $this->GetFolderByType(FOLDERTYPE_SentItems);
			if ($sent)
			{
				if ($sent->IdDb == $folder->IdDb)
				{
					$folder->ToFolder = true;
					return;
				}
				else if ($sent->SubFolders && $sent->SubFolders->Count() > 0)
				{
					$sent->SubFolders->_setToFolderInSentDrafts($folder);
				}
			}
			
			$drafts =& $this->GetFolderByType(FOLDERTYPE_Drafts);
			if ($drafts)
			{
				if ($drafts->IdDb == $folder->IdDb)
				{
					$folder->ToFolder = true;
					return;
				}
				else if ($drafts->SubFolders && $drafts->SubFolders->Count() > 0)
				{
					$drafts->SubFolders->_setToFolderInSentDrafts($folder);
				}
			}
		}
		
		/**
		 * @param Folder $initFolder
		 */
		function _setToFolderInSentDrafts(&$initFolder)
		{
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder =& $this->Get($i);
				if ($folder)
				{
					if ($initFolder->IdDb == $folder->IdDb)
					{
						$initFolder->ToFolder = true;
						return;
					}
					else if ($folder->SubFolders && $folder->SubFolders->Count() > 0)
					{
						$folder->SubFolders->_setToFolderInSentDrafts($initFolder);
					}
				}
			}
		}
		
		/**
		 * @return FolderCollection $folders
		 */
		function SortRootTree()
		{
			return $this->_sortFolderCollection(true);
		}
		
		/**
		 * @param bool $sortSpecialFolders[optional] = false
		 * @return FolderCollection
		 */
		function _sortFolderCollection($sortSpecialFolders = false)
		{
			$newFoldersArray = $topArray = $footArray = array();
			$newFolders = new FolderCollection();
			
			foreach ($this->Instance() as $folder)
			{
				if (strlen($folder->Name) > 0 && $folder->Name[0] == '&')
				{
					$footArray[] = $folder->Name;
				}
				else 
				{
					$topArray[] = $folder->Name;
				}
			}
			unset($folder);

			natcasesort($topArray);
			
			foreach ($topArray as $value)
			{
				$newFoldersArray[strtolower($value)] = $value;
			}
			foreach ($footArray as $value)
			{
				$newFoldersArray[strtolower($value)] = $value;
			}
			unset($topArray, $footArray);

			if ($sortSpecialFolders)
			{
				$this->_localSortFunction(FOLDERNAME_Inbox, $newFoldersArray, $newFolders, true);
				$this->_localSortFunction(FOLDERNAME_SentItems, $newFoldersArray, $newFolders);
				$this->_localSortFunction(FOLDERNAME_Sent, $newFoldersArray, $newFolders);
				$this->_localSortFunction(FOLDERNAME_Drafts, $newFoldersArray, $newFolders);
				$this->_localSortFunction(FOLDERNAME_Spam, $newFoldersArray, $newFolders);
				$this->_localSortFunction(FOLDERNAME_Trash, $newFoldersArray, $newFolders);
			}
			
			foreach ($newFoldersArray as $folderName)
			{
				$folder =& $this->GetFolderByName($folderName);
				if ($folder)
				{
					if ($folder->SubFolders && $folder->SubFolders->Count() > 0)
					{
						$folder->SubFolders = $folder->SubFolders->_sortFolderCollection();
					}
					$newFolders->Add($folder);
				}
				unset($folder);
			}
			
			return $newFolders;
		}

		function _localSortFunction($folderName, &$newFoldersArray, &$newFolders, $subSort = false)
		{
			if (isset($newFoldersArray[strtolower($folderName)]))
			{
				$folder =& $this->GetFolderByName($newFoldersArray[strtolower($folderName)]);
				if ($folder)
				{
					if ($folder->SubFolders && $folder->SubFolders->Count() > 0)
					{
						$folder->SubFolders = $folder->SubFolders->_sortFolderCollection($subSort);
					}
					$newFolders->Add($folder);
					unset($newFoldersArray[strtolower($folderName)]);
					unset($folder);
				}
			}
		}
	}