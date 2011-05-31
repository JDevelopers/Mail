<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));

	require_once(WM_ROOTPATH.'common/class_account.php');

	class MailProcessor
	{
		/**
		 * @var ImapStorage
		 */
		var $MailStorage = null;
		
		/**
		 * @var MySqlStorage
		 */
		var $DbStorage = null;
		
		/**
		 * @access private
		 * @var Account
		 */
		var $_account;

		/**
		 * @var bool
		 */
		var $IsMoveError = false;
		
		/**
		 * @param Account $account
		 * @return MailProcessor
		 */
		function MailProcessor(&$account, $notUseAccoutForDb = false)
		{
			$this->_account =& $account;
			switch ($account->MailProtocol)
			{
				case MAILPROTOCOL_POP3:
					require_once(WM_ROOTPATH.'common/class_pop3storage.php');
					$this->MailStorage = new Pop3Storage($account, $this);
					break;
					
				case MAILPROTOCOL_IMAP4:
					require_once(WM_ROOTPATH.'common/class_imapstorage.php');
					$this->MailStorage = new ImapStorage($account, $this);
					break;
			}

			if ($notUseAccoutForDb)
			{
				$null = null;
				$this->DbStorage =& DbStorageCreator::CreateDatabaseStorage($null);
			}
			else
			{
				$this->DbStorage =& DbStorageCreator::CreateDatabaseStorage($account);
			}

		}
		
		/**
		 * @param FolderCollection $folders
		 * @return bool
		 */
		function Synchronize(&$folders)
		{
			$GLOBALS[MailDefaultCharset] = $this->_account->GetDefaultIncCharset();
			$GLOBALS[MailOutputCharset] = $this->_account->DbCharset;
			
			return $this->MailStorage->Connect() && $this->MailStorage->Synchronize($folders);
		}
		
		/**
		 * @return bool
		 */
		function SynchronizeFolders()
		{
			return $this->MailStorage->Connect() && $this->MailStorage->SynchronizeFolders();
		}
		
		/**
		 * @param int $pageNumber
		 * @param Folder $folder optional
		 * @return WebMailMessageCollection
		 */
		function &GetMessageHeaders($pageNumber, &$folder)
		{
			$messageHeaders = null;
			
			ConvertUtils::SetLimits();
			
			if ($folder && $folder->SyncType == FOLDERSYNC_DirectMode)
			{
				if ($this->MailStorage->Connect())
				{
					$GLOBALS[MailDefaultCharset] = $this->_account->GetDefaultIncCharset();
					$GLOBALS[MailOutputCharset] = $this->_account->GetUserCharset();
					$messageHeaders =& $this->MailStorage->LoadMessageHeaders($pageNumber, $folder);
				}
				return $messageHeaders;
			}
			
			if ($folder && $this->DbStorage->Connect())
			{
				$GLOBALS[MailDefaultCharset] = $this->_account->DbCharset;
				$GLOBALS[MailOutputCharset] = $this->_account->GetUserCharset();
				$messageHeaders =& $this->DbStorage->LoadMessageHeaders($pageNumber, $folder);
			}
			return $messageHeaders;
		}
		
		/**
		 * @param array $messageIdSet
		 * @param Folder $folder
		 * @return MessageCollection
		 */
		function GetMessages(&$messageIdUidSet, &$folder, $setRead = false)
		{
			$mailCollection = new WebMailMessageCollection();
			
			$GLOBALS[MailDefaultCharset] = $this->_account->GetDefaultIncCharset();
			$GLOBALS[MailOutputCharset] = $this->_account->GetUserCharset();

			ConvertUtils::SetLimits();
			
			$messageIdSet = array_keys($messageIdUidSet);
			$messageUidSet = array_values($messageIdUidSet);
			
			for ($i = 0, $c = count($messageIdUidSet); $i < $c; $i++)
			{
				$messageId =& $messageIdSet[$i];
				$messageUid =& $messageUidSet[$i];
				$mailMess = null;

				if ($folder->SyncType == FOLDERSYNC_DirectMode)
				{
					if ($this->MailStorage->Connect())
					{	
						$mailMess = &$this->MailStorage->LoadMessage($messageUid, true, $folder);
					}
				}
				elseif ($this->DbStorage->Connect())
				{
					if ($this->DbStorage->GetMessageDownloadedFlag($messageId, $folder))
					{
						$mailMess = &$this->DbStorage->LoadMessage($messageId, false, $folder);
					}
					elseif ($this->MailStorage->Connect())
					{
						$mailMess = &$this->MailStorage->LoadMessage($messageUid, true, $folder);
					}
				}
				
				if ($setRead && $mailMess)
				{
					$mailMess->Flags = $mailMess->Flags | MESSAGEFLAGS_Seen;
				}
				
				if ($mailMess)
				{
					$mailMess->IdFolder = $folder->IdDb;
				}
				
				$mailCollection->Add($mailMess);
				unset($mailMess);
			}
			
			return $mailCollection;	
		}

		/**
		 * @param string $bsIndex
		 * @param string $messageUid
		 * @param Folder $folder
		 * @return string
		 */
		function GetBodyPartByIndex($bsIndex, $messageUid, $folder)
		{
			$out = null;
			if (MAILPROTOCOL_IMAP4 == $this->_account->MailProtocol)
			{
				ConvertUtils::SetLimits();
				if ($this->MailStorage->Connect())
				{
					$out = $this->MailStorage->GetBodyPartByIndex($bsIndex, $messageUid, $folder);
				}
			}
			return $out;
		}
		
		/**
		 * @param int $messageId
		 * @param string $messageUid
		 * @param Folder $folder
		 * @return WebMailMessage
		 */
		function &GetMessage($messageId, $messageUid, &$folder, $mode = null, $_onlyDownloaded = false)
		{
			$mailMess = null;
			$GLOBALS[MailDefaultCharset] = $this->_account->GetDefaultIncCharset();
			$GLOBALS[MailOutputCharset] = $this->_account->GetUserCharset();

			ConvertUtils::SetLimits();
			if ($folder->SyncType == FOLDERSYNC_DirectMode && !$_onlyDownloaded)
			{
				if ($this->MailStorage->Connect())
				{	
					$mailMess =& $this->MailStorage->LoadMessage($messageUid, true, $folder, $mode);
				}
			}
			else if (USE_DB && $this->DbStorage->Connect())
			{
				$messageIndexSet = array($messageId);
				$_preData = $this->DbStorage->PreLoadMessagesFromDB($messageIndexSet, false, $folder);
				$_firstArr = null;
				if (count($_preData) > 0)
				{
					foreach ($_preData as $_arr)
					{
						$_firstArr = $_arr;
						break;
					}
					
					if (isset($_firstArr[3]) && $_firstArr[3] == 1)
					{
						$mailMess =& $this->DbStorage->LoadMessage($messageId, false, $folder, $_preData);
					}
					else if (!$_onlyDownloaded && $this->MailStorage->Connect())
					{
						$mailMess =& $this->MailStorage->LoadMessage($messageUid, true, $folder, $mode);
						if ($mailMess && isset($_preData[$messageId]) && count($_preData[$messageId]) > 2)
						{
							$mailMess->DbPriority = $_preData[$messageId][1];
							$mailMess->Flags = $_preData[$messageId][2];
						}
					}
				}
			}
			
			if ($mailMess)
			{
				$mailMess->IdMsg = $messageId;
				$mailMess->Uid = $messageUid;
				$mailMess->IdFolder = $folder->IdDb;
			}
			else
			{
				setGlobalError(PROC_MSG_HAS_DELETED);
			}
			
			return $mailMess;
		}

		/**
		 * @return	string
		 */
		function GetNameSpacePrefix()
		{
			if ($this->_account->MailProtocol == MAILPROTOCOL_IMAP4)
			{
				return $this->MailStorage->GetNameSpacePrefix();
			}
			return '';
		}

		/**
		 * @return array
		 */
		function &GetLsubFolders()
		{
			$folders = null;
			if ($this->_account->MailProtocol == MAILPROTOCOL_IMAP4 && $this->MailStorage->Connect())
			{
				$folders = $this->MailStorage->GetLSubFolders();
			}

			return $folders;
		}
		
		/**
		 * @return FolderCollection
		 */
		function &GetFolders()
		{
			$folders = null;
			if (USE_DB)
			{
				if ($this->DbStorage->Connect())
				{
					$folders =& $this->DbStorage->GetFolders();
				}
			}
			else
			{
				if ($this->MailStorage->Connect())
				{
					$folders =& $this->MailStorage->GetFolders();
					$folders->SetSyncTypeToAll(FOLDERSYNC_DirectMode);
					$folders->GetDMFolderCountsToAll($this->MailStorage);
					$folders->SetDMFolderIds($this->_account->Id);
					$folders = $folders->SortRootTree();
					FolderCollection::SaveToSession($folders);
				}
			}

			if ($folders)
			{
				$folders->InitSystemFolders($this->_account);
			}

			return $folders;
		}

		/**
		 * @return bool
		 */
		function IsQuotaSupport()
		{
			if ($this->_account->MailProtocol == MAILPROTOCOL_IMAP4)
			{
				return $this->MailStorage->IsQuotaSupport();
			}
			return false;
		}

		/**
		 * @return int | false
		 */
		function GetQuota()
		{
			if ($this->_account->MailProtocol == MAILPROTOCOL_IMAP4)
			{
				return $this->MailStorage->GetQuota();
			}
			return false;
		}
		
		/**
		 * @return int | false
		 */
		function GetUsedQuota()
		{
			if ($this->_account->MailProtocol == MAILPROTOCOL_IMAP4)
			{
				return $this->MailStorage->GetUsedQuota();
			}
			return false;
		}
		
		/**
		 * @param Folder $folder
		 */
		function GetFolderMessageCount(&$folder)
		{
			if (!$folder)
			{
				return;
			}
			else if ($folder->SyncType == FOLDERSYNC_DirectMode)
			{
				switch ($this->_account->MailProtocol)
				{
					case MAILPROTOCOL_POP3:
					case MAILPROTOCOL_IMAP4:
						if ($this->MailStorage->Connect())
						{
							$this->MailStorage->GetFolderMessageCount($folder);
						}
						break;
				}
			}
			else if ($this->DbStorage->Connect())
			{
				$this->DbStorage->GetFolderMessageCount($folder);
			}
		}
		
		/**
		 * @param Folder $folder
		 * @param bool $useCache = false
		 */
		function GetFolderInfo(&$folder, $useCache = false)
		{
			if ($folder && $folder->IdDb > 0)
			{
				if (USE_DB && $this->DbStorage->Connect())
				{
					return $this->DbStorage->GetFolderInfo($folder, $useCache);
				}
				else if (isset($_SESSION[ACCOUNT_FOLDERS]))
				{
					$mfolder = null;
					$folders = FolderCollection::GetFromSession();
					if ($folders)
					{
						$mfolder =& $folders->GetFolderById($folder->IdDb);
						if ($mfolder)
						{
							$folder = $mfolder;
						}
					}
				}
			}
			return false;
		}
		
		/**
		 * @param int $foderId
		 * @param int $id_acct
		 * @return string|false
		 */
		function GetFolderFullName($folderId, $id_acct)
		{
			if ($folderId > 0 && $this->DbStorage->Connect())
			{
				if (USE_DB)
				{
					return $this->DbStorage->GetFolderFullName($folderId, $id_acct);
				}
				else if (isset($_SESSION[ACCOUNT_FOLDERS]))
				{
					$folder = null;
					$folders = FolderCollection::GetFromSession();
					if ($folders)
					{
						$folder =& $folders->GetFolderById($folderId);
					}
					if ($folder)
					{
						return $folder->FullName;
					}
				}
				
			}
		
			return false;
		}
		
		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function CreateFolder(&$folder, $forceCreate = false)
		{
			$result = true;
			if ($this->_account->MailProtocol != MAILPROTOCOL_POP3 && ($folder->SyncType != FOLDERSYNC_DontSync || $forceCreate))
			{
				$result &= $this->MailStorage->Connect() & $this->MailStorage->CreateFolder($folder);
			}

			return (USE_DB) ? $result && $this->DbStorage->Connect() && $this->DbStorage->CreateFolder($folder) : $result;
		}
		
		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function DeleteFolder(&$folder)
		{
			$result = $this->DbStorage->Connect();
			if (!$result)
			{
				return false;
			}
			
			switch ($this->_account->MailProtocol)
			{
				case MAILPROTOCOL_WMSERVER:
				case MAILPROTOCOL_IMAP4:
					if ($folder->SyncType != FOLDERSYNC_DontSync)
					{
						if ($this->MailStorage->Connect())
						{
							if ($this->MailStorage->DeleteFolder($folder))
							{
								if (USE_LSUB)
								{
									$this->MailStorage->SubscribeFolder($folder, true);
								}
								
								if (USE_DB)
								{
									$result &= $this->DbStorage->DeleteFolderFilters($folder->IdDb, $this->_account->Id);
									$result &= $this->DbStorage->DeleteFolder($folder);
								}
								
								return $result;
							}
						}
					}
					else 
					{
						$result &= $this->DbStorage->DeleteFolderFilters($folder->IdDb, $this->_account->Id);
						$result &= $this->DbStorage->DeleteFolder($folder);
						return $result;
					}
					
					return false;
					
				case MAILPROTOCOL_POP3:
					$rootFolders = &$this->GetFolders();
					$folders = $rootFolders->CreateFolderListFromTree();
					
					foreach (array_keys($folders->Instance()) as $key)
					{
						$fld = &$folders->Get($key);
						if ($fld->IdDb == $folder->IdDb)
						{
							$this->_deletePop3FolderTree($fld, $result);
							return $result;
						}
						unset($fld);
					}
					return true;
			}
			
			return false;
		}
		
		/**
		 * @param Folder $folderTree
		 * @param bool $folder
		 */
		function _deletePop3FolderTree(&$folderTree, $result)
		{
			if ($folderTree->SubFolders != null && $folderTree->SubFolders->Count())
			{
				foreach (array_keys($folderTree->SubFolders->Instance()) as $key)
				{
					$folder = &$folderTree->SubFolders->Get($key);
					$this->_deletePop3FolderTree($folder, $result);
					unset($folder);
				}
			}
			
			$result &= $this->PurgeFolder($folderTree);
			$result &= $this->DbStorage->DeleteFolder($folderTree);
			$result &= $this->DbStorage->DeleteFolderFilters($folderTree->IdDb, $this->_account->Id);
		}
		
		function SetHide(&$folder, $isHide)
		{
			$result = true;
			if ($this->_account->MailProtocol != MAILPROTOCOL_POP3
				&& $folder->SyncType != FOLDERSYNC_DontSync && $folder->Type != FOLDERTYPE_Inbox)
			{
				$result &= $this->MailStorage->Connect() && $this->MailStorage->SubscribeFolder($folder, $isHide);
			}
			return $result;
		}
		
		/**
		 * @param Folder $folder
		 * @param string $newName
		 * @param string $delimiter
		 * @param array $aLsubFolder
		 * @return bool
		 */
		function RenameFolder(&$folder, $newName, $delimiter, $aLsubFolder)
		{
			$newName = str_replace($delimiter, '', $newName);
			$newFullName = $newName;
			/* $newName = str_replace('&', '', $newName); bug in UTF-7-imap fix */
			$pos = strrpos($folder->FullName, $delimiter);
			if (false !== $pos)
			{
				$newFullName = substr($folder->FullName, 0, $pos).$delimiter.$newName;
			}
			
			$result = true;
			if (USE_DB)
			{
				$result &= $this->DbStorage->Connect() && $this->DbStorage->RenameFolder($folder, $newFullName);
			}
			
			$fs = new FileSystem(INI_DIR.'/mail', strtolower($this->_account->Email), $this->_account->Id);
			if ($result && $fs->IsFolderExist($folder->FullName))
			{
				$result &= $fs->MoveSubFolders($folder->FullName, $newFullName);
			}
			
			if ($result && $this->_account->MailProtocol != MAILPROTOCOL_POP3 && $folder->SyncType != FOLDERSYNC_DontSync)
			{
				$result &= $this->MailStorage->Connect() && $this->MailStorage->RenameFolder($folder, $newFullName, $aLsubFolder, $delimiter);
			}
			
			return $result;
		}
		
		/**
		 * @param string $messageIdUidSet
		 * @param Folder $folder
		 * @param int $flags
		 * @param short $action
		 * @return bool
		 */
		function SetFlags(&$messageIdUidSet, &$folder, $flags, $action, $updateDb = true)
		{
			$messageIdSet = null;
			$messageUidSet = null;
			if ($messageIdUidSet != null)
			{
				$messageIdSet = array_keys($messageIdUidSet);
				$messageUidSet = array_values($messageIdUidSet);
			}

			$result = true;
			
			if ($updateDb && $folder->SyncType != FOLDERSYNC_DirectMode)
			{
				if ($this->DbStorage->Connect())
				{
					$result &= $this->DbStorage->SetMessagesFlags($messageIdSet, false, $folder, $flags, $action);
				}	
			}
			
			if ($this->_account->MailProtocol != MAILPROTOCOL_POP3 && $folder->SyncType != FOLDERSYNC_DontSync)
			{
				if ($this->MailStorage->Connect())
				{
					$result &= $this->MailStorage->SetMessagesFlags($messageUidSet, true, $folder, $flags, $action);
				}
			}
			return $result;
		}
		
		function SetFlag(&$messageIdUid, &$folder, $flags, $action)
		{
			$messageIdSet = null;
			$messageUidSet = null;
			if ($messageIdUid != null)
			{
				$messageIdSet = array_keys($messageIdUid);
				$messageUidSet = array_values($messageIdUid);
			}
			
			$result = true;
			
			if ($folder->SyncType != FOLDERSYNC_DirectMode)
			{
				if ($this->DbStorage->Connect())
				{
					$result &= $this->DbStorage->SetMessagesFlags($messageIdSet, false, $folder, $flags, $action);
				}	
			}

			if ($folder->SyncType != FOLDERSYNC_DontSync && $this->_account->MailProtocol != MAILPROTOCOL_POP3)
			{
				if (count($messageUidSet) == 1 && $this->MailStorage->Connect())
				{
					$result &= $this->MailStorage->SetMessagesFlag($messageUidSet[0], $folder, $flags, $action);
				}
			}
			
			return $result;
		}

		/**
		 *
		 * @param int $messId
		 * @param string $messUid
		 * @param int $folderId
		 * @param string $folderFullName
		 * @param int $flag
		 */
		function SetFlagFromReply($messId, $messUid, $folderId, $folderFullName, $flag)
		{
			$folder = new Folder($this->_account->Id, $folderId, $folderFullName);
			$this->DbStorage->GetFolderInfo($folder);

			$messageIdUid = array($messId => $messUid);
			$this->SetFlag($messageIdUid, $folder, $flag, ACTION_Set);
		}
		
		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function EmptyFolder(&$folder)
		{
			$result = true;

			$messageUidSet = array();
			if (USE_DB && $this->DbStorage->Connect() && $this->_account->MailProtocol != MAILPROTOCOL_WMSERVER)
			{
				$messageUidSet = $this->DbStorage->SelectAllMessagesUidSetByFolder($folder);
			}
			
			if ($folder->SyncType != FOLDERSYNC_DirectMode)
			{
				if (USE_DB && $this->DbStorage->Connect())
				{
					$result &= $this->DbStorage->PurgeFolder($folder, true);
					$result &= $this->DbStorage->UpdateMailboxSize();
				}
			}

			switch ($this->_account->MailProtocol)
			{
				case MAILPROTOCOL_IMAP4:
					if ($folder->SyncType != FOLDERSYNC_DontSync)
					{
						if ($this->MailStorage->Connect())
						{
							$messageUidSet = null;
							$result &= $this->MailStorage->SetMessagesFlags($messageUidSet, true, $folder, MESSAGEFLAGS_Deleted, ACTION_Set);
							$result &= $this->MailStorage->PurgeFolder($folder);
						}
					}
					break;
				case MAILPROTOCOL_POP3:
					if ($this->_account->MailMode == MAILMODE_DeleteMessageWhenItsRemovedFromTrash ||
						$this->_account->MailMode == MAILMODE_KeepMessagesOnServerAndDeleteMessageWhenItsRemovedFromTrash)
					{
						$newMessageUidSet = array();
						foreach ($messageUidSet as $_uid) 
						{
							if (strlen(trim($_uid)) > 0)
							{
								$newMessageUidSet[] = $_uid;
							}
						}
						
						if (count($newMessageUidSet) > 0)
						{						
							$result &= $this->MailStorage->Connect() && $this->MailStorage->DeleteMessages($messageUidSet, true, $folder);
						}
					}
					break;
				case MAILPROTOCOL_WMSERVER:
					$result &= $this->MailStorage->Connect() && $this->MailStorage->ClearFolder($folder);
					break;	
			}
			//$fs->DeleteAccountDirs();
			return $result;
		}
		
		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function EmptySpam()
		{
			$result = false;
			if ($this->_account)
			{
				$folders =& $this->GetFolders();
				if ($folders)
				{
					$spamFolder =& $folders->GetFolderByType(FOLDERTYPE_Spam);
					if ($spamFolder)
					{
						if ($this->_account->MailProtocol == MAILPROTOCOL_WMSERVER)
						{
							$result = $this->MailStorage->Connect() && $this->MailStorage->ClearFolder($spamFolder);
							if ($result && $this->DbStorage->Connect())
							{
								$result &= $this->DbStorage->ClearDbFolder($spamFolder, $this->_account);
								$result &= $this->DbStorage->UpdateMailboxSize();
							}
						}
						else if ($this->_account->MailProtocol == MAILPROTOCOL_POP3 || $this->_account->MailProtocol == MAILPROTOCOL_IMAP4)
						{
							$result = $this->EmptyFolder($spamFolder);
						}

					}
				}
			}
			
			return $result;
		}
		
		/**
		 * @return bool
		 */
		function EmptyTrash()
		{
			$result = false;
			$folders =& $this->GetFolders();
			if ($this->_account && $folders)
			{
				$trashFolder =& $folders->GetFolderByType(FOLDERTYPE_Trash);
				if ($trashFolder)
				{
					$result = $this->EmptyFolder($trashFolder);
				}
			}
			
			return $result;
		}

		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function PurgeFolder(&$folder)
		{
			$result = false;
			if ($this->_account->MailProtocol == MAILPROTOCOL_IMAP4)
			{
				$result = true;
				if ($folder->SyncType != FOLDERSYNC_DirectMode)
				{
					if ($this->DbStorage->Connect())
					{
						$result &= $this->DbStorage->PurgeFolder($folder);
						$result &= $this->DbStorage->UpdateMailboxSize();
					}
				}

				if ($folder->SyncType != FOLDERSYNC_DontSync)
				{
					if ($this->MailStorage->Connect())
					{
						$result &= $this->MailStorage->PurgeFolder($folder);
					}
				}
			}

			return $result;
		}

		/**
		 * @param int $id[optional] = null
		 * @return bool
		 */
		function DeleteAccount($id = null)
		{
			$result = true;
			$account = null;
			if ($id > 0)
			{
				$account =& $this->DbStorage->SelectAccountData($id);
			}
			else
			{
				$account =& $this->_account;
			}

			if ($account)
			{
				$result &= $this->DbStorage->DeleteAccountData($account->Id,$account->Email);

				$fs = new FileSystem(INI_DIR.'/mail', strtolower($account->Email), $account->Id);
				$fs->DeleteAccountDirs();

				$fs2 = new FileSystem(INI_DIR.'/temp', strtolower($account->Email), $account->Id);
				$fs2->DeleteAccountDirs();
				unset($fs, $fs2);
			}
			else
			{
				$result = false;
			}

			return $result;
		}

		/**
		 * @param Array $messageIdUidSet
		 * @param Folder $folder optional
		 * @return bool
		 */
		function DeleteFromServerImmediately($messageIdUidSet, &$folder)
		{
			ConvertUtils::SetLimits();
			$messageUidSet = array_values($messageIdUidSet);
			switch ($this->_account->MailProtocol)
			{
				default:
					return false;

				case MAILPROTOCOL_WMSERVER:
				case MAILPROTOCOL_POP3:
					return $this->MailStorage->Connect() && $this->MailStorage->DeleteMessages($messageUidSet, true, $folder);
					
				case MAILPROTOCOL_IMAP4:
					return $this->SetFlags($messageIdUidSet, $folder, MESSAGEFLAGS_Deleted, ACTION_Set, false) && $this->PurgeFolder($folder); 
			}
		}

		/**
		 * @param Array $messageIdUidSet
		 * @param Folder $folder optional
		 * @return bool
		 */
		function _DeleteMessages(&$messageIdUidSet, &$folder)
		{
			ConvertUtils::SetLimits();

			$messageIdSet = array_keys($messageIdUidSet);
			$messageUidSet = array_values($messageIdUidSet);

			$settings =& Settings::CreateInstance();

			$result = true;
			switch ($this->_account->MailProtocol)
			{
				default:
					return false;

				case MAILPROTOCOL_POP3:
					if ($folder->Type == FOLDERTYPE_Trash)
					{
						if ($folder->SyncType != FOLDERSYNC_DirectMode)
						{
							if ($this->DbStorage->Connect())
							{
								$result &= $this->DbStorage->DeleteMessages($messageIdSet, false, $folder);
								$result &= $this->DbStorage->UpdateMailboxSize();
							}
						}

						if ($this->_account->MailMode == MAILMODE_DeleteMessageWhenItsRemovedFromTrash ||
								$this->_account->MailMode ==
									MAILMODE_KeepMessagesOnServerAndDeleteMessageWhenItsRemovedFromTrash)
						{
							$result &= $this->MailStorage->Connect() && $this->MailStorage->DeleteMessages($messageUidSet, true, $folder);
						}

						return $result;
					}
					elseif ($folder->SyncType == FOLDERSYNC_DirectMode)
					{
						return $result && $this->MailStorage->Connect() && $this->MailStorage->DeleteMessages($messageUidSet, true, $folder);
					}

					$folderList = &$this->GetFolders();
					$trashFolder = &$folderList->GetFolderByType(FOLDERTYPE_Trash);

					return $this->MoveMessages($messageIdUidSet, $folder, $trashFolder);

				case MAILPROTOCOL_WMSERVER:
					if ($folder->Type == FOLDERTYPE_Trash || $folder->Type == FOLDERTYPE_Spam)
					{
						if ($folder->SyncType != FOLDERSYNC_DirectMode)
						{
							if ($this->DbStorage->Connect())
							{
								$result &= $this->DbStorage->DeleteMessages($messageIdSet, false, $folder);
								$result &= $this->DbStorage->UpdateMailboxSize();
							}
						}

						return $result && $this->MailStorage->Connect() && $this->MailStorage->DeleteMessages($messageUidSet, true, $folder);
					}
					else if ($folder->SyncType == FOLDERSYNC_DirectMode)
					{
						return $result && $this->MailStorage->Connect() && $this->MailStorage->DeleteMessages($messageUidSet, true, $folder);
					}

					$folderList =& $this->GetFolders();
					$trashFolder =& $folderList->GetFolderByType(FOLDERTYPE_Trash);
					if (!$trashFolder)
					{
						$trashFolder = new Folder($this->_account->Id, -1, FOLDERNAME_Trash, FOLDERNAME_Trash, FOLDERSYNC_AllHeadersOnly);
						if (!$this->CreateFolder($trashFolder))
						{
							return false;
						}
					}

					return $this->MoveMessages($messageIdUidSet, $folder, $trashFolder);

				case MAILPROTOCOL_IMAP4:

					//if ($settings->Imap4DeleteLikePop3 && $folder->SyncType != FOLDERSYNC_DirectMode)
					if ($settings->Imap4DeleteLikePop3)
					{
						$folderList =& $this->GetFolders();
						if ($folderList)
						{
							$trashFolderType =& $folderList->GetFolderByType(FOLDERTYPE_Trash);
							if (!$trashFolderType)
							{
								$trashFolderName =& $folderList->GetFolderByName(FOLDERNAME_Trash);
								if ($trashFolderName)
								{
									$trashFolderName->Type = FOLDERTYPE_Trash;
									$trashFolder = $trashFolderName;
									if ($this->DbStorage->Connect())
									{
										$result &= $this->DbStorage->UpdateFolder($trashFolderName);
									}
								}
								else
								{
									$inboxFolderType =& $folderList->GetFolderByType(FOLDERTYPE_Inbox);
									if ($inboxFolderType && $inboxFolderType->SubFolders &&  $inboxFolderType->SubFolders->Count() > 0)
									{
										$trashFolderName2 =& $inboxFolderType->SubFolders->GetFolderByName(FOLDERNAME_Trash);
										if ($trashFolderName2)
										{
											$trashFolderName2->Type = FOLDERTYPE_Trash;
											$trashFolder = $trashFolderName2;
											if ($this->DbStorage->Connect())
											{
												$result &= $this->DbStorage->UpdateFolder($trashFolderName2);
											}
										}
									}

									if (!$trashFolder)
									{
										$trashFolder = new Folder($this->_account->Id, -1,
											FOLDERNAME_Trash, FOLDERNAME_Trash, $this->_account->GetDefaultFolderSync($settings));

										if (!$this->CreateFolder($trashFolder, true))
										{
											$trashFolder = null;
										}
									}
								}
							}
							else
							{
								$trashFolder = $trashFolderType;
							}

							if ($trashFolder)
							{
								if ($folder->Type == FOLDERTYPE_Trash)
								{
									$result = true;
									$result &= $this->SetFlags($messageIdUidSet, $folder, MESSAGEFLAGS_Deleted, ACTION_Set);
									$result &= $this->PurgeFolder($folder);
									return $result;
								}

								return $this->MoveMessages($messageIdUidSet, $folder, $trashFolder);
							}

							return false;
						}
					}

					return $this->SetFlags($messageIdUidSet, $folder, MESSAGEFLAGS_Deleted, ACTION_Set) && $this->PurgeFolder($folder);
			}
		}
		
		/**
		 * @param Array $messageIdUidSet
		 * @param Folder $folder optional
		 * @return bool
		 */
		function DeleteMessages(&$messageIdUidSet, &$folder, $noMove = false)
		{
			ConvertUtils::SetLimits();
			
			$messageIdSet = array_keys($messageIdUidSet);
			$messageUidSet = array_values($messageIdUidSet);
			
			$settings =& Settings::CreateInstance();
			
			$result = true;
			switch ($this->_account->MailProtocol)
			{
				default:
					return false;
					
				case MAILPROTOCOL_POP3:
					if ($folder->Type == FOLDERTYPE_Trash || $noMove)
					{
						if ($folder->SyncType != FOLDERSYNC_DirectMode)
						{
							if ($this->DbStorage->Connect())
							{
								$result &= $this->DbStorage->DeleteMessages($messageIdSet, false, $folder);
								$result &= $this->DbStorage->UpdateMailboxSize();
							}
						}
				
						if ($this->_account->MailMode == MAILMODE_DeleteMessageWhenItsRemovedFromTrash ||
								$this->_account->MailMode ==
									MAILMODE_KeepMessagesOnServerAndDeleteMessageWhenItsRemovedFromTrash)
						{
							$result &= $this->MailStorage->Connect() && $this->MailStorage->DeleteMessages($messageUidSet, true, $folder);
						}
							
						return $result;
					}
					else if ($folder->SyncType == FOLDERSYNC_DirectMode)
					{
						return $result && $this->MailStorage->Connect() && $this->MailStorage->DeleteMessages($messageUidSet, true, $folder);
					}
					
					$folderList =& $this->GetFolders();
					$trashFolder =& $folderList->GetFolderByType(FOLDERTYPE_Trash);
					
					return ($noMove) ? true : $this->MoveMessages($messageIdUidSet, $folder, $trashFolder);
					
				case MAILPROTOCOL_WMSERVER:
					if ($folder->Type == FOLDERTYPE_Trash || $folder->Type == FOLDERTYPE_Spam || $noMove)
					{
						if ($folder->SyncType != FOLDERSYNC_DirectMode)
						{
							if ($this->DbStorage->Connect())
							{
								$result &= $this->DbStorage->DeleteMessages($messageIdSet, false, $folder);
								$result &= $this->DbStorage->UpdateMailboxSize();
							}
						}
				
						return $result && $this->MailStorage->Connect() && $this->MailStorage->DeleteMessages($messageUidSet, true, $folder);
					}
					else if ($folder->SyncType == FOLDERSYNC_DirectMode)
					{
						return $result && $this->MailStorage->Connect() && $this->MailStorage->DeleteMessages($messageUidSet, true, $folder);
					}
					
					$folderList =& $this->GetFolders();
					$trashFolder =& $folderList->GetFolderByType(FOLDERTYPE_Trash);
					if (!$trashFolder)
					{
						$trashFolder = new Folder($this->_account->Id, -1, FOLDERNAME_Trash, FOLDERNAME_Trash, FOLDERSYNC_AllHeadersOnly);
						if (!$this->CreateFolder($trashFolder))
						{
							return false;
						}
					}
					
					return $noMove ? true : $this->MoveMessages($messageIdUidSet, $folder, $trashFolder);
				
				case MAILPROTOCOL_IMAP4:
					//if ($settings->Imap4DeleteLikePop3 && $folder->SyncType != FOLDERSYNC_DirectMode)
					if ($settings->Imap4DeleteLikePop3)
					{
						$trashFolder = null;
						$folderList =& $this->GetFolders();
						if ($folderList)
						{
							$trashFolderType =& $folderList->GetFolderByType(FOLDERTYPE_Trash);
							if (!$trashFolderType)
							{
								$trashFolderName =& $folderList->GetFolderByName(FOLDERNAME_Trash);
								if ($trashFolderName)
								{
									$trashFolderName->Type = FOLDERTYPE_Trash;
									$trashFolder = $trashFolderName;
									if (USE_DB && $this->DbStorage->Connect())
									{
										$result &= $this->DbStorage->UpdateFolder($trashFolderName);
									}
								}
								else
								{
									$inboxFolderType =& $folderList->GetFolderByType(FOLDERTYPE_Inbox);
									if ($inboxFolderType && $inboxFolderType->SubFolders &&  $inboxFolderType->SubFolders->Count() > 0)
									{
										$trashFolderName2 =& $inboxFolderType->SubFolders->GetFolderByName(FOLDERNAME_Trash);
										if ($trashFolderName2)
										{
											$trashFolderName2->Type = FOLDERTYPE_Trash;
											$trashFolder = $trashFolderName2;
											if (USE_DB && $this->DbStorage->Connect())
											{
												$result &= $this->DbStorage->UpdateFolder($trashFolderName2);
											}
										}
									}

									if (!$trashFolder)
									{
										$trashFolder = new Folder($this->_account->Id, -1,
											FOLDERNAME_Trash, FOLDERNAME_Trash, FOLDERSYNC_DontSync);
										/*
										$trashFolder = new Folder($this->_account->Id, -1,
											FOLDERNAME_Trash, FOLDERNAME_Trash, $this->_account->GetDefaultFolderSync($settings));
										*/

										if (!$this->CreateFolder($trashFolder))
										{
											$trashFolder = null;
										}
									}
								}
							}
							else
							{
								$trashFolder = $trashFolderType;
							}

							if ($trashFolder)
							{
								if ($folder->Type == FOLDERTYPE_Trash || $noMove)
								{
									$result = true;
									$result &= $this->SetFlags($messageIdUidSet, $folder, MESSAGEFLAGS_Deleted, ACTION_Set);
									$result &= $this->PurgeFolder($folder);
									
									return $result;
								}
													
								return $noMove ? true : $this->MoveMessages($messageIdUidSet, $folder, $trashFolder);
							}

							return false;
						}
					}
					
					return $this->SetFlags($messageIdUidSet, $folder, MESSAGEFLAGS_Deleted, ACTION_Set) && $this->PurgeFolder($folder);
			}
		}
		
		/**
		 * @param string $_subject
		 * @param string $_body
		 * @return bool
		 */
		function SetAutoresponder($_subject, $_body)
		{
			if ($this->_account)
			{
				if ($this->_account->IsInternal)
				{
					require_once(WM_ROOTPATH.'common/class_sieve.php');

					$oSieve = new CSieveStorage($this->_account);
					if ($oSieve->EnableAutoresponder())
					{
						return $oSieve->SetAutoresponderText($_body);
					}

					return false;
				}
			}
			return false;
		}
		
		/**
		 * @return array|false
		 */
		function GetAutoresponder()
		{
			if ($this->_account)
			{
				if ($this->_account->IsInternal)
				{
					$returnArray = array('e' => false, 's' => '', 'b' => '');
					
					require_once(WM_ROOTPATH.'common/class_sieve.php');
					
					$oSieve = new CSieveStorage($this->_account);
					if ($oSieve->IsAutoresponderEnabled())
					{
						$returnArray['e'] = true;
					}

					$returnArray['b'] = $oSieve->GetAutoresponderText();
					return $returnArray;
				}
			}

			setGlobalError('Can\'t get autoresponder.');
			return false;
		}
		
		/**
		 * @return bool
		 */
		function DisableAutoresponder()
		{
			if ($this->_account)
			{
				if ($this->_account->IsInternal)
				{
					require_once(WM_ROOTPATH.'common/class_sieve.php');
					$oSieve = new CSieveStorage($this->_account);
					return $oSieve->DisableAutoresponder();
				}
			}
			return false;
		}
		
		/**
		 * @param	array	$messageIdUidSet
		 * @param	Folder	$fromFolder
		 * @param	bool	$isSpam = true
		 * @return	bool
		 */
		function SpamMessages(&$messageIdUidSet, &$fromFolder, $isSpam = true)
		{
			$result = true;
			if (!$this->DbStorage->Connect() || !$this->MailStorage->Connect(true))
			{
				return false;
			}

			$toFolder = null;
			$folders =& $this->DbStorage->GetFolders();
			if ($isSpam)
			{
				$toFolder =& $folders->GetFolderByType(FOLDERTYPE_Spam);
			}
			else
			{
				$toFolder =& $folders->GetFolderByType(FOLDERTYPE_Inbox);
			}

			if ($toFolder)
			{
				$needSystemSpamSet = true;
				
				/* custom class */
				wm_Custom::StaticUseMethod('MailStorageSpamMessages', 
					array(&$this, &$needSystemSpamSet, &$messageIdUidSet, &$fromFolder, &$toFolder, &$isSpam));

				if ($needSystemSpamSet)
				{
					$this->MailStorage->SpamMessages($messageIdUidSet, $fromFolder, $isSpam);
				}

				$result = $this->MoveMessages($messageIdUidSet, $fromFolder, $toFolder);
			}

			return $result;
		}
		
		/**
		 * @param Array $messageIdUidSet
		 * @param Folder $fromFolder
		 * @param Folder $toFolder
		 * @return bool
		 */
		function MoveMessages(&$messageIdUidSet, &$fromFolder, &$toFolder)
		{
			$GLOBALS[MailDefaultCharset] = $this->_account->DefaultIncCharset;
			$GLOBALS[MailOutputCharset] = $this->_account->DefaultOutCharset;
			
			$messageIdSet = array_keys($messageIdUidSet);
			$messageUidSet = array_values($messageIdUidSet);
			
			$result = true;
			switch ($this->_account->MailProtocol)
			{
				case MAILPROTOCOL_WMSERVER:
					if (!$this->MailStorage->Connect())
					{
						return false;
					}
					
					$result = $this->MailStorage->MoveMessages($messageUidSet, $fromFolder, $toFolder);
					if (!$result)
					{
						$this->IsMoveError = true;
					}
					$_index = 0;
					foreach ($messageIdSet as $_id)
					{
						$messageIdUidSet[$_id] = $messageUidSet[$_index];
						$_index = $_index + 1;
					}
					
					if ($result && $this->DbStorage->Connect())
					{
						$result &= $this->DbStorage->MoveMessagesWithUidUpdate($messageIdUidSet, $fromFolder, $toFolder);
					}
					return $result;
					break;
					
				case MAILPROTOCOL_POP3:
					switch ($fromFolder->SyncType)
					{
						case FOLDERSYNC_DontSync:
							if (!$this->DbStorage->Connect())
							{
								return false;
							}
							return $this->DbStorage->MoveMessages($messageIdSet, false, $fromFolder, $toFolder);	
							break;		
						default:
							if (!$this->DbStorage->Connect())
							{
								return false;
							}
							$result = $this->DbStorage->SaveMessages($this->GetMessages($messageIdUidSet, $fromFolder), $toFolder);
							if ($result)
							{
								if ($result && $fromFolder->SyncType != FOLDERSYNC_DirectMode)
								{
									$result &= $this->DbStorage->DeleteMessages($messageIdSet, false, $fromFolder);
								}
								else
								{
									/*
									if (!$this->MailStorage->Connect())
									{
										$result = false;
									}
									else 
									{
										$result &= $this->MailStorage->DeleteMessages($messageUidSet, true, $fromFolder);
									}*/
								}
							}
							return $result;
							break;
					}					
					break;

				case MAILPROTOCOL_IMAP4:
					switch ($fromFolder->SyncType)
					{
						case FOLDERSYNC_DontSync:
							switch ($toFolder->SyncType)
							{
								case FOLDERSYNC_DontSync:
									if ($this->DbStorage->Connect())
									{
										return $this->DbStorage->MoveMessages($messageIdSet, false, $fromFolder, $toFolder);
									}
									return false;
									
								case FOLDERSYNC_AllEntireMessages:
								case FOLDERSYNC_AllHeadersOnly:
								case FOLDERSYNC_NewEntireMessages:
								case FOLDERSYNC_NewHeadersOnly:
									
									if (!$this->DbStorage->Connect() || !$this->MailStorage->Connect())
									{
										return false;
									}

									$result = $this->MailStorage->SaveMessages(
											$this->GetMessages($messageIdUidSet, $fromFolder), $toFolder);
							
									if ($result)
									{
										$result &= $this->DbStorage->DeleteMessages($messageIdSet, false, $fromFolder);
										$result &= $this->MailStorage->SynchronizeFolder($toFolder);
									}
									else
									{
										$this->IsMoveError = true;
									}
									return $result;

								case FOLDERSYNC_DirectMode:
									if (!$this->DbStorage->Connect() || !$this->MailStorage->Connect())
									{
										return false;
									}
									
									$result = $this->MailStorage->SaveMessages(
											$this->DbStorage->LoadMessages($messageIdSet, false, $fromFolder), $toFolder);
									
									if ($result)
									{
										$result &= $this->DbStorage->DeleteMessages($messageIdSet, false, $fromFolder);
									}
									else
									{
										$this->IsMoveError = true;
									}
									return $result;
											
							}
							break;
							
						case FOLDERSYNC_AllEntireMessages:
						case FOLDERSYNC_AllHeadersOnly:
						case FOLDERSYNC_NewEntireMessages:
						case FOLDERSYNC_NewHeadersOnly:
							if (!$this->DbStorage->Connect() || !$this->MailStorage->Connect())
							{
								return false;
							}
							
							switch ($toFolder->SyncType)
							{
								case FOLDERSYNC_DontSync:
									
									$result = $this->DbStorage->SaveMessages(
										$this->GetMessages($messageIdUidSet, $fromFolder), $toFolder);
									
									if ($result)
									{
										$result &= $this->DbStorage->DeleteMessages($messageIdSet, false, $fromFolder);
										$result &= $this->MailStorage->SetDeleteFlagAndPurgeByUids($messageUidSet, $fromFolder);
									}
									
									return $result;
									
								case FOLDERSYNC_AllEntireMessages:
								case FOLDERSYNC_AllHeadersOnly:
								case FOLDERSYNC_NewEntireMessages:
								case FOLDERSYNC_NewHeadersOnly:
									$result = $this->MailStorage->CopyMessages($messageUidSet, true, $fromFolder, $toFolder);
									
									if ($result)
									{
										$result &= $this->DbStorage->DeleteMessages($messageIdSet, false, $fromFolder);
										$result &= $this->MailStorage->SetDeleteFlagAndPurgeByUids($messageUidSet, $fromFolder);
										
										$GLOBALS[MailDefaultCharset] = $this->_account->GetDefaultIncCharset();
										$GLOBALS[MailOutputCharset] = $this->_account->DbCharset;
										$result &= $this->MailStorage->SynchronizeFolder($toFolder);
									}
									else
									{
										$this->IsMoveError = true;
									}
									return $result;
									
								case FOLDERSYNC_DirectMode:
									$result = $this->MailStorage->CopyMessages($messageUidSet, true, $fromFolder, $toFolder);

									if ($result)
									{
										$result &= $this->DbStorage->DeleteMessages($messageIdSet, false, $fromFolder);
										$result &= $this->MailStorage->SetDeleteFlagAndPurgeByUids($messageUidSet, $fromFolder);
									}
									else
									{
										$this->IsMoveError = true;
									}
									return $result;
							}
							break;
							
						case FOLDERSYNC_DirectMode:
							if (!$this->DbStorage->Connect() || !$this->MailStorage->Connect())
							{
								return false;
							}
							
							switch ($toFolder->SyncType)
							{
								case FOLDERSYNC_DontSync:
									$result = $this->DbStorage->SaveMessages(
											$this->GetMessages($messageIdUidSet, $fromFolder), $toFolder);

									if ($result)
									{
										$result &= $this->MailStorage->SetDeleteFlagAndPurgeByUids($messageUidSet, $fromFolder);
									}
									return $result;
									
								case FOLDERSYNC_AllEntireMessages:
								case FOLDERSYNC_AllHeadersOnly:
								case FOLDERSYNC_NewEntireMessages:
								case FOLDERSYNC_NewHeadersOnly:
									$result = $this->MailStorage->CopyMessages($messageUidSet, true, $fromFolder, $toFolder);
									
									if ($result)
									{
										$result &= $this->MailStorage->SetDeleteFlagAndPurgeByUids($messageUidSet, $fromFolder);

										$GLOBALS[MailDefaultCharset] = $this->_account->GetDefaultIncCharset();
										$GLOBALS[MailOutputCharset] = $this->_account->DbCharset;
										$result &= $this->MailStorage->SynchronizeFolder($toFolder);
									}
									else
									{
										$this->IsMoveError = true;
									}
									return $result;
									
								case FOLDERSYNC_DirectMode:
									$result = $this->MailStorage->CopyMessages($messageUidSet, true, $fromFolder, $toFolder);
									if ($result)
									{
										$result &= $this->MailStorage->SetDeleteFlagAndPurgeByUids($messageUidSet, $fromFolder);
									}
									else
									{
										$this->IsMoveError = true;
									}
									return $result;
							}
							break;
					}
					
					if ($fromFolder->SyncType != FOLDERSYNC_DirectMode)
					{
						if ($this->DbStorage->Connect())
						{
							$result &= $this->DbStorage->DeleteMessages($messageIdSet, true, $fromFolder, $toFolder);
						}
					}
					
					if ($fromFolder->SyncType != FOLDERSYNC_DontSync && $toFolder->SyncType != FOLDERSYNC_DontSync)
					{
						if ($this->MailStorage->Connect())
						{
							$result &= $this->MailStorage->MoveMessages($messageUidSet, false, $fromFolder, $toFolder);
							$folders =& $this->GetFolders();
							$result &= $this->Synchronize($folders);
						}
						
					}
					break;
			}
			
			return $result;
		}
		
		/**
		 * @param string $condition
		 * @param FolderCollection $folders
		 * @param bool $inHeadersOnly
		 * @return int
		 */
		function SearchMessagesCount($condition, &$folders, $inHeadersOnly)
		{
			$messageCount = 0;
			if ($this->DbStorage->Connect())
			{
				$messageCount = $this->DbStorage->SearchMessagesCount($condition, $folders, $inHeadersOnly);
			}
			return $messageCount;
		}
				
		/**
		 * @param int $pageNumber
		 * @param string $condition
		 * @param FolderCollection $folders
		 * @param bool $inHeadersOnly
		 * @param int $countMessages
		 * @return WebMailMessageCollection
		 */
		function &SearchMessages($pageNumber, $condition, &$folders, $inHeadersOnly, $countMessages)
		{
			$webMailMessageCollection = null;
			if ($this->DbStorage->Connect())
			{
				$webMailMessageCollection = &$this->DbStorage->SearchMessages($pageNumber, $condition, $folders, $inHeadersOnly, $countMessages);
			}
			return $webMailMessageCollection;
		}
		
		/**
		 * @param int $pageNumber
		 * @param string $condition
		 * @param Folder $folder
		 * @param bool $inHeadersOnly
		 * @param int $countMessages
		 * @return WebMailMessageCollection
		 */
		function &DmImapSearchMessages($pageNumber, $condition, &$folder, $inHeadersOnly, &$refMsgCount)
		{
			$webMailMessageCollection = null;
			if ($this->_account->MailProtocol == MAILPROTOCOL_IMAP4 && $folder->SyncType == FOLDERSYNC_DirectMode)
			{
				ConvertUtils::SetLimits();

				if ($this->MailStorage->Connect())
				{
					$GLOBALS[MailDefaultCharset] = $this->_account->GetDefaultIncCharset();
					$GLOBALS[MailOutputCharset] = $this->_account->GetUserCharset();
					$webMailMessageCollection =& $this->MailStorage->DmImapSearchMessages($pageNumber, $folder, $condition, $inHeadersOnly, $refMsgCount);
					if ($webMailMessageCollection)
					{
						$webMailMessageCollection->SetAllMessageFolderId($folder->IdDb);
					}
				}
			}
			return $webMailMessageCollection;
		}

		function HeadersFullImapSearchMessages($pageNumber,	$condition, $folder, &$refMsgCount)
		{
			$webMailMessageCollection = null;
			if ($this->_account->MailProtocol == MAILPROTOCOL_IMAP4 && ($folder->SyncType == FOLDERSYNC_AllHeadersOnly || $folder->SyncType == FOLDERSYNC_NewHeadersOnly))
			{
				ConvertUtils::SetLimits();

				if ($this->MailStorage->Connect())
				{
					$imapUids = $this->MailStorage->HeadersBodyImapSearchMessagesUids($folder, $condition);
					if ($imapUids !== false)
					{
						if (count($imapUids) > 0)
						{
							if (USE_DB && $this->DbStorage->Connect())
							{
								$dbUids = $this->DbStorage->SearchMessagesUids($condition, $folder);
								if ($dbUids !== false)
								{
									if (count($dbUids) > 0)
									{
										$resultUids = array_intersect($dbUids, $imapUids);
										$refMsgCount = count($resultUids);
										$start = ($pageNumber - 1) * $this->_account->MailsPerPage;

										$messageUidSet = array_slice($resultUids, $start, $this->_account->MailsPerPage);

										$webMailMessageCollection =& $this->DbStorage->LoadMessageHeadersByIntUids($messageUidSet, $folder);
										$webMailMessageCollection->SetAllMessageFolderId($folder->IdDb);
									}
									else
									{
										$webMailMessageCollection = new WebMailMessageCollection();
									}
								}
							}
						}
						else
						{
							$webMailMessageCollection = new WebMailMessageCollection();
						}

						//$GLOBALS[MailDefaultCharset] = $this->_account->GetDefaultIncCharset();
						//$GLOBALS[MailOutputCharset] = $this->_account->GetUserCharset();
						//$webMailMessageCollection =& $this->MailStorage->DmImapSearchMessages($pageNumber, $folder, $condition, $inHeadersOnly, $refMsgCount);
					}
				}
			}
			return $webMailMessageCollection;
		}

		/**
		 * @param WebMailMessage $message
		 * @param Folder $folder
		 * @return bool
		 */
		function UpdateMessage(&$message, &$folder)
		{
			if ($message == null || $folder == null)
			{
				return false;
			}
			
			switch ($this->_account->MailProtocol)
			{
				case MAILPROTOCOL_POP3:
					$result = true;
					if (!$this->DbStorage->Connect())
					{
						return false;
					}
					$size = $this->DbStorage->MessageSize($message, $folder);
					if ($size > -1)
					{
						$mailBoxesSize = $this->DbStorage->SelectMailboxesSize() - $size + $message->GetMailSize();
						if ($this->MailStorage->_settings->EnableMailboxSizeLimit && $this->_account->MailboxLimit > 0 && $this->_account->MailboxLimit < $mailBoxesSize)
						{
							setGlobalError(ErrorGetMailLimit);
							return false;
						}
						
						$result &= $this->DbStorage->UpdateMessage($message, $folder);
					}
					else
					{
						$result &= $this->DbStorage->SaveMessage($message, $folder);
					}
					return $result;
					break;
				default:
					return $this->SaveMessage($message, $folder);
					break;
			}
		}
		
		/**
		 * @param WebMailMessage $message
		 * @param Folder $folder
		 * @return bool
		 */
		function SaveMessage(&$message, &$folder, $fromFolder = null, $bOnlyDelete = false)
		{
			if ($message === null || $folder === null)
			{
				return false;
			}
			
			//Get size all messages in DB
			if (USE_DB && !$bOnlyDelete)
			{
				$mailBoxesSize = $this->DbStorage->SelectMailboxesSize() + $message->GetMailSize();
				
				if ($this->MailStorage->_settings->EnableMailboxSizeLimit && $this->_account->MailboxLimit > 0 && $this->_account->MailboxLimit < $mailBoxesSize)
				{
					setGlobalError(ErrorGetMailLimit);
					return false;
				}
			}
			
			switch ($this->_account->MailProtocol)
			{
				case MAILPROTOCOL_POP3:
					$result = true;
					if ($this->DbStorage->Connect())
					{
						/*
						if ($message->IdMsg != -1)
						{
							$messageIdSet = array($message->IdMsg);
							$result &= $this->DbStorage->DeleteMessages($messageIdSet, false, $folder);
						}
						else
						{
							$message->IdMsg = $this->DbStorage->SelectLastIdMsg();
						}
						*/
						return $result && $this->DbStorage->SaveMessage($message, $folder);
					}
					break;

				case MAILPROTOCOL_IMAP4:
					$result = true;
					if ($message->IdMsg != -1)
					{
						$messageIdUidSet = array();
						$messageIdUidSet[$message->IdMsg] = $message->Uid;
						$nfolder = ($fromFolder) ? $fromFolder : $folder;
						$result &= $this->DeleteMessages($messageIdUidSet, $nfolder, true);

						if ($bOnlyDelete)
						{
							return $result;
						}
					}
					
					if ($folder->SyncType != FOLDERSYNC_DontSync)
					{
						if ($this->MailStorage->Connect())
						{
							$result &= $this->MailStorage->SaveMessage($message, $folder);
							
							$GLOBALS[MailDefaultCharset] = $this->_account->GetDefaultIncCharset();
							unset($GLOBALS[MailInputCharset]);
							$GLOBALS[MailOutputCharset] = $this->_account->GetUserCharset();
							
							$result &= $this->MailStorage->SynchronizeFolder($folder);
						}
						return $result;
					}
					else if ($this->DbStorage->Connect())
					{
						/*
						if ($message->IdMsg == -1)
						{
							$message->IdMsg = $this->DbStorage->SelectLastIdMsg();
						}
						*/
						$result &= $this->DbStorage->SaveMessage($message, $folder);
						return $result;
					}
					break;
			}
			return false;
		}
	}
