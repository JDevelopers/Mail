<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));
	
	require_once (WM_ROOTPATH.'libs/class_pop3.php');
	require_once (WM_ROOTPATH.'common/class_mailstorage.php');

	define('USE_UIDL_CACHE_FILE', false);

	class Pop3Storage extends MailServerStorage
	{
		/**
		 * @access private
		 * @var CPOP3
		 */
		var $_pop3Mail;
		
		/**
		 * @access private
		 * @var Array
		 */
		var $_pop3Uids = null;
		
		/**
		 * @access private
		 * @var Array
		 */
		var $_pop3Sizes = null;
		
		/**
		 * @param Account $account
		 * @return Pop3Storage
		 */
		function Pop3Storage(&$account, &$mp)
		{
			$this->mailproc =& $mp;
			MailServerStorage::MailServerStorage($account);
			$this->_pop3Mail = new CPOP3();
		}
		
		/**
		 * @param $arg[optional] = false
		 * @return bool
		 */
		function Connect()
		{
			if ($this->_pop3Mail->socket != false)
			{
				return true;
			}
			
			if (!$this->_pop3Mail->connect($this->Account->MailIncHost, $this->Account->MailIncPort))
			{
				setGlobalError(ErrorPOP3Connect);
				return false;
			}
			else
			{
				register_shutdown_function(array(&$this, 'Disconnect'));
			}
			
			if (!$this->_pop3Mail->login($this->Account->MailIncLogin, $this->Account->MailIncPassword))
			{
				setGlobalError(ErrorPOP3IMAP4Auth);
				return false;				
			}
			
			return true;
		}
		
		/**
		 * @return bool
		 */
		function Disconnect()
		{
			if ($this->_pop3Mail->socket == false)
			{
				return true;
			}
			return $this->_pop3Mail->close();
		}

		function SynchronizeFolders()
		{
			return true;
		}
		
		/**
		 * @param Folder $folders
		 * @return bool
		 */
		function SynchronizeFolder(&$folder)
		{
			if ($folder->Type != FOLDERTYPE_Inbox)
			{
				return true;
			}
			
			$dbStorage = &DbStorageCreator::CreateDatabaseStorage($this->Account);
			if (!$dbStorage->Connect())
			{
				return false;
			}
			
			return $this->_synchronizeFolderWithOpenDbConnection($folder, $dbStorage);
		}

		/**
		 * @param FolderCollection $folders
		 * @return bool
		 */
		function Synchronize(&$folders)
		{
			$folderList = $folders->CreateFolderListFromTree(); //copy tree object here

			$inboxFolder = &$folderList->GetFolderByType(FOLDERTYPE_Inbox);
			if ($inboxFolder == null)
			{
				return true;
			}
			
			$dbStorage =& DbStorageCreator::CreateDatabaseStorage($this->Account);
			
			if (!$dbStorage->Connect())
			{
				return false;
			}
			
			return $this->_synchronizeFolderWithOpenDbConnection($inboxFolder, $dbStorage);
		}
		
		/**
		 * @param Folder $folders
		 * @param DbStorage $dbStorage
		 * @return bool
		 */
		function _synchronizeFolderWithOpenDbConnection(&$folder, &$dbStorage)
		{
			$result = true;
			
			if ($folder->SyncType == FOLDERSYNC_DontSync || $folder->SyncType == FOLDERSYNC_DirectMode || $folder->Hide)
			{
				if ($this->UpdateFolderHandler != null && $folder->SyncType == FOLDERSYNC_DirectMode)
				{
					call_user_func_array($this->UpdateFolderHandler, array($folder->IdDb, $folder->FullName));
				}
				return true;
			}
			
			if ($this->DownloadedMessagesHandler != null)
			{
				call_user_func_array($this->DownloadedMessagesHandler, array($folder->GetFolderName($this->Account), 0));
			}
			
			//get uids from pop3 server
			$pop3Uids = &$this->_getPop3Uids(true);
			
			//get uids from DB
			$dbUids =& $dbStorage->SelectReadsRecords();
			$dbUids = array_keys($dbUids);
			
			//get only new messages from pop3 server
			$newUids = array_diff($pop3Uids, $dbUids);
			
			//get deletd uids from pop3 server
			$uidsToDelete = array_diff($dbUids, $pop3Uids);
			
			//get Array sizes all messages on pop3 server
			$pop3Sizes = &$this->_getPop3Sizes(true);
			
			//get size all messages in DB
			$mailBoxesSize = $dbStorage->SelectMailboxesSize();
			
			if ($this->DownloadedMessagesHandler != null && count($newUids) > 0)
			{
				call_user_func_array($this->DownloadedMessagesHandler, array($folder->GetFolderName($this->Account), count($newUids)));
			}
			
			$_filters =& $dbStorage->SelectFilters($this->Account->Id, true);
						
			foreach ($newUids as $newUid)
			{
				//get id message from uid pop3 server
				$index = $this->_getMessageIndexFromUid($pop3Uids, $newUid);
				
				$indexArray = array($index);
				
				$mailBoxesSize += $pop3Sizes[$index];
				
				if (!$this->_settings->EnableMailboxSizeLimit || $this->Account->MailboxLimit == 0 || $this->Account->MailboxLimit > $mailBoxesSize)
				{
					if ($this->DownloadedMessagesHandler != null)
					{
						call_user_func($this->DownloadedMessagesHandler);
					}

					$mailMessageCollection = null;
					//Check sync mode
					if ($folder->SyncType == FOLDERSYNC_NewEntireMessages ||
						$folder->SyncType == FOLDERSYNC_AllEntireMessages)
					{
						//Entire Message
						$mailMessageCollection = &$this->LoadMessages($indexArray, false, $folder);
					}
					else if ($folder->SyncType == FOLDERSYNC_NewHeadersOnly ||
							$folder->SyncType == FOLDERSYNC_AllHeadersOnly)
					{
						//Entire Header
						$mailMessageCollection = &$this->_loadMessageHeaders($indexArray, false, $folder);
					}
					
					/* write to DB */
					if ($mailMessageCollection != null && $mailMessageCollection->Count() > 0)
					{
						$message =& $mailMessageCollection->Get(0);
						if (!$this->ApplyFilters($message, $dbStorage, $folder, $_filters))
						{
							return false;
							//break;
						}
					}
					
					//Check mailmode to delete from server
					if($this->Account->MailMode == MAILMODE_DeleteMessagesFromServer)
					{
						//Delete received messages from server
						$this->_pop3Mail->delete_mail($index);
					}
					
					//Save uid to reads table
					$dbStorage->InsertReadsRecords(array($newUid));	
				}
				else 
				{
					$result = false;
					setGlobalError(ErrorGetMailLimit);
					break;
				}	
			}
			
			//delete from DB
			if (count($uidsToDelete) > 0)
			{
				if($folder->SyncType == FOLDERSYNC_AllHeadersOnly ||
				   $folder->SyncType == FOLDERSYNC_AllEntireMessages)
				{
					$result &= $dbStorage->DeleteMessages($uidsToDelete, true, $folder);
					if ($this->UpdateFolderHandler != null)
					{
						call_user_func_array($this->UpdateFolderHandler, array($folder->IdDb, $folder->FullName));
					}
				}	
			}
			
			if (($this->Account->MailMode == MAILMODE_KeepMessagesOnServer ||
					$this->Account->MailMode == MAILMODE_KeepMessagesOnServerAndDeleteMessageWhenItsRemovedFromTrash) &&
					$this->Account->MailsOnServerDays > 0)
			{
				$expiredUids = &$dbStorage->SelectExpiredMessageUids();
				
				$result &= $this->DeleteMessages($expiredUids, true, $folder);
				if ($this->UpdateFolderHandler != null)
				{
					call_user_func_array($this->UpdateFolderHandler, array($folder->IdDb, $folder->FullName));
				}
			}
			
			if(!empty($uidsToDelete))
			{
				$result &= $dbStorage->DeleteReadsRecordsByUids($uidsToDelete);
			}
			
			$result &= $dbStorage->UpdateMailboxSize();	
			
			return $result;
		}

		/**
		 * @param array $messageIdUidSet
		 * @param Folder $folder
		 * @return bool
		 */
		function SpamMessages($messageIdUidSet, $folder, $isSpam = true)
		{
			return true;
		}

		/**
		 * @return	string
		 */
		function GetNameSpacePrefix()
		{
			return '';
		}
		
		/**
		 * @param string $messageIndex
		 * @param bool $indexAsUid
		 * @return WebMailMessage
		 */
		function &LoadMessage($messageIndex, $indexAsUid)
		{
			$message = null;

			$idx = $messageIndex;
			$uid = $messageIndex;
			if ($indexAsUid)
			{
				$uids =& $this->_getPop3Uids();
				$idx = $this->_getMessageIndexFromUid($uids, $messageIndex);
				if ($idx != -1)
				{
					$uid = $uids[$idx];
				}
			}

			if ($idx < 0)
			{
				setGlobalError(PROC_MSG_HAS_DELETED);
				return $message;
			}
			
			$log =& CLog::CreateInstance();
						
			$msgText = $this->_pop3Mail->get_mail($idx);
			if (!$msgText)
			{
				return $message;
			}

			$message = new WebMailMessage();
			$message->LoadMessageFromRawBody($msgText, true);
			$message->Uid = $uid;
			$message->Size = strlen($msgText);

			/*
			$size = &$this->_getPop3Sizes();
			$message->Size = $size[$idx];
			*/
			
			return $message;
		}

		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @return WebMailMessageCollection
		 */
		function &LoadMessages(&$messageIndexSet, $indexAsUid)
		{
			$messageCollection = new WebMailMessageCollection();
			$uids =& $this->_getPop3Uids();
			$size =& $this->_getPop3Sizes();
			
			foreach ($messageIndexSet as $index)
			{
				$idx = ($indexAsUid) ? $this->_getMessageIndexFromUid($uids, $index) : $index;

				if ($idx < 0 || $idx > count($uids))
				{
					continue;
				}
				
				$msgText = $this->_pop3Mail->get_mail($idx);
				if (!$msgText)
				{
					continue;
				}
				
				$message = new WebMailMessage();
				$message->LoadMessageFromRawBody($msgText, true);
				$message->Uid = $uids[$idx];
				$message->Size = isset($size[$idx]) ? $size[$idx] : strlen($msgText);
				
				$messageCollection->Add($message);
				unset($message);
				
			}
			
			return $messageCollection;

		}

		/**
		 * @param int $pageNumber
		 * @param Folder $folder
		 * @return WebMailMessageCollection
		 */
		function &LoadMessageHeaders($pageNumber, &$folder)
		{
			$messageIndexSet = array();
			/*
			$uids = &$this->_getPop3Uids();
			$msgCount = count($uids);
			 */
			$msgCount = $folder->MessageCount;
			
	  		for ($i = $msgCount - ($pageNumber - 1) * $this->Account->MailsPerPage,
					$t = $msgCount - $pageNumber * $this->Account->MailsPerPage; $i >= $t; $i--)
	  		{
	  			if ($i == 0) break;
	  			$messageIndexSet[] = $i;
	  		}
	  		/* $messageCollection =& $this->_loadMessageHeaders($messageIndexSet, false, $folder); */
			$messageCollection =& $this->_loadMessageHeadersByIndexs($messageIndexSet, $folder);
	  		return $messageCollection;
		}

		function _loadMessageHeadersByIndexs($messageIndexSet, &$folder)
		{
			$messageCollection = new WebMailMessageCollection();
			foreach($messageIndexSet as $idx)
			{
				$uid = $this->_pop3Mail->uidl($idx);
				$uid = isset ($uid[$idx]) ? $uid[$idx] : '';
				$size = $this->_pop3Mail->msglist($idx);
				$size = isset ($size[$idx]) ? $size[$idx] : '';
				$response = $this->_pop3Mail->get_top($idx);
				if ($response)
				{
					$msg = new WebMailMessage();
					$msg->LoadMessageFromRawBody($response);
					$msg->IdMsg = $idx;
					$msg->Uid = $uid;
					$msg->Size = $size;
					$msg->IdFolder = $folder->IdDb;
					if ($folder->SyncType == FOLDERSYNC_DirectMode)
					{
						$msg->Flags |= MESSAGEFLAGS_Seen;
					}
					$messageCollection->AddCopy($msg);
					unset($msg);
				}
			}

			if ($messageCollection->Count() > 0)
			{
				return $messageCollection;
			}

			$messageCollection = null;

			return $messageCollection;
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @return bool
		 */
		function DeleteMessages(&$messageIndexSet, $indexAsUid)
		{
			$result = true;
			$uids = &$this->_getPop3Uids(true);
			$new_uids = array();
			
			foreach ($uids as $index => $uid)
			{
				$new_uids[$uid] = $index;
			}
			
			if ($this->ShowDeletingMessageNumber != null)
			{
				call_user_func_array($this->ShowDeletingMessageNumber, array(true));
			}
			
			foreach ($messageIndexSet as $index)
			{
				$idx = ($indexAsUid) ? $this->_getMessageIndexFromUidNew($new_uids, $index) : $index;
				
				if ($idx < 0 || $idx > count($uids))
				{
					continue;
				}
				
				$result &= $this->_pop3Mail->delete_mail($idx);
			}
			return $result;
		}

		/**
		 * @return bool
		 */
		function ClearFolder()
		{
			return true;
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @param Folder $toFolder
		 * @return bool
		 */
		function MoveMessages()
		{
			return true;
		}
		
		/**
		 * @access private
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @return WebMailMessageCollection
		 */
		function &_loadMessageHeaders(&$messageIndexSet, $indexAsUid, $folder)
		{
			$messageCollection = new WebMailMessageCollection();
			$uids = &$this->_getPop3Uids();
			$size = &$this->_getPop3Sizes();
			
			foreach ($messageIndexSet as $index)
			{
				$idx = $index;
				if ($indexAsUid)
				{
					$idx = $this->_getMessageIndexFromUid($uids, $index);
				}

				if ($idx < 0 || $idx > count($uids))
				{
					continue;
				}
				
				$msgText = $this->_pop3Mail->get_top($idx);
				if (!$msgText)
				{
					continue;
				}
				
				$message = new WebMailMessage();
				$message->LoadMessageFromRawBody($msgText);
				$message->IdMsg = $idx;
				$message->Uid = $uids[$idx];
				$size = &$this->_getPop3Sizes();
				$message->Size = $size[$idx];
				
				if ($folder->SyncType == FOLDERSYNC_DirectMode)
				{
					$message->Flags |= MESSAGEFLAGS_Seen;
				}

				$messageCollection->Add($message);
				unset($message);
				
			}
			return $messageCollection;
		}

		/**
		 * @param string $messageIndex
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @return WebMailMessage
		 */
		function &LoadMessageHeader($messageIndex, $indexAsUid)
		{
			$uids = &$this->_getPop3Uids();

			$idx = $messageIndex;
			if ($indexAsUid)
			{
				$idx = $this->_getMessageIndexFromUid($uids, $messageIndex);
			}

			if ($idx < 0 || $idx > count($uids))
			{
				return null;
			}
			
			$msgText = $this->_pop3Mail->get_top($idx);
			
			if (!$msgText)
			{
				return null;
			}
			
			$message = new WebMailMessage();
			$message->LoadMessageFromRawBody($msgText);
			$message->IdMsg = $idx;
			$message->Uid = $uids[$idx];
			$size = &$this->_getPop3Sizes();
			$message->Size = $size[$idx];
			
			return $message;
		}
		
		
		/**
		 * @access private
		 * @return Array
		 */
		function &_getPop3Uids()
		{
			if (is_null($this->_pop3Uids))
			{
				if (USE_UIDL_CACHE_FILE)
				{
					$_stat = $this->_pop3Mail->_stats();
					if ($_stat)
					{
						$file_prefix = 'cache_';
						$tempFiles =& CTempFiles::CreateInstance($this->Account);
						
						$_stat = implode('|', $_stat);
						if (isset($_SESSION['pop3UidsHash']) && md5($_stat) == $_SESSION['pop3UidsHash'] && $tempFiles->IsFileExist($file_prefix.$_SESSION['pop3UidsHash']))
						{
							$this->_pop3Uids = unserialize($tempFiles->LoadFile($file_prefix.$_SESSION['pop3UidsHash']));
						}

						if (is_null($this->_pop3Uids))
						{
							$this->_pop3Uids = $this->_pop3Mail->uidl();
							$_SESSION['pop3UidsHash'] = md5($_stat);
							$tempFiles->SaveFile($file_prefix.$_SESSION['pop3UidsHash'], serialize($this->_pop3Uids));
						}

						unset($tempFiles);
					}
				}
				else
				{
					$this->_pop3Uids = $this->_pop3Mail->uidl();
				}
			}
			
			return $this->_pop3Uids;
		}
		
		/**
		 * @access private
		 * @return Array
		 */
		function &_getPop3Sizes()
		{
			if (is_null($this->_pop3Sizes))
			{
				$this->_pop3Sizes = $this->_pop3Mail->msglist();
			}
			$size = &$this->_pop3Sizes;
			return $size;
		}

		/**
		 * @return int
		 */
		function GetFolderMessageCount(&$folder)
		{
			$arr = $this->_pop3Mail->_stats();
			if ($arr && isset($arr['count_mails']))
			{
				$folder->MessageCount = (int) $arr['count_mails'];
				$folder->UnreadMessageCount = 0;
				return true;
			}
			return false;
		}

		/**
		 * @param string $messageUid
		 * @param Folder $folder
		 * @param int $flags
		 * @param short $action
		 * @return bool
		 */
		function SetMessagesFlag($messageUid, &$folder, $flags, $action)
		{
			return true;
		}

		/**
		 * @access private
		 * @param Array $uidList
		 * @param string $uid
		 * @return int
		 */
		function _getMessageIndexFromUid(&$uidList, $uid)
		{
			$searchKey = -1;
			if ($uidList)
			{
				$searchKey = array_search($uid, $uidList);
				if ($searchKey === null || $searchKey === false)
				{
					$searchKey = -1;
				}
			}
			return $searchKey;
		}
		
		/**
		 * @param Array $uidList
		 * @param string $uid
		 * @return int
		 */
		function _getMessageIndexFromUidNew($uidList, $uid)
		{
			if ($uidList != null)
			{
				return isset($uidList[$uid]) ? $uidList[$uid] : -1;
			}
			return -1;
		}
		
	}