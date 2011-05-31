<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__) . '/'));

require_once (WM_ROOTPATH . 'libs/class_imap.php');
require_once (WM_ROOTPATH . 'common/class_webmailmessages.php');
require_once (WM_ROOTPATH . 'common/class_folders.php');
require_once (WM_ROOTPATH . 'common/class_mailstorage.php');
require_once (WM_ROOTPATH . 'common/class_bodystructure.php');

class ImapStorage extends MailServerStorage
{
	/**
	 * @access private
	 * @var IMAPMAIL
	 */
	var $_imapMail;

	/**
	 * @param Account $account
	 * @return ImapStorage
	 */
	function ImapStorage(&$account, &$mp)
	{
		$this->mailproc =& $mp;
		MailServerStorage::MailServerStorage($account);
		$this->_imapMail = new IMAPMAIL();
		$this->_imapMail->host = $account->MailIncHost;
		$this->_imapMail->port = $account->MailIncPort;
		$this->_imapMail->user = $account->MailIncLogin;
		$this->_imapMail->password = $account->MailIncPassword;
	}

	/**
	 * @return	string
	 */
	function GetNameSpacePrefix()
	{
		if ($this->_imapMail->IsNameSpaceSupport())
		{
			return $this->_imapMail->GetNameSpacePrefix();
		}
		return '';
	}

	/**
	 * @param $arg[optional] = false
	 * @return bool
	 */
	function Connect()
	{
		if($this->_imapMail->connection != false)
		{
			return true;
		}
		
		@register_shutdown_function(array(&$this, 'Disconnect'));
		if (!$this->_imapMail->open())
		{
			setGlobalError(ErrorIMAP4Connect);
			return false;
		}
		
		if (!$this->_imapMail->login($this->Account->MailIncLogin, $this->Account->MailIncPassword, $this->Account->MailIncProxyLogin))
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
		if ($this->_imapMail->connection == false)
		{
			return true;
		}
		return $this->_imapMail->close();
	}

	/**
	 * @param Array $messageIndexSet
	 * @param bool $indexAsUid
	 * @param Folder $folder
	 * @return WebMailMessageCollection
	 */
	function LoadMessages(&$messageIndexSet, $indexAsUid, &$folder, $imapUids = null, $imapUidFlags = null, $imapUidSizes = null)
	{
		$messageCollection = null;
		if ($this->_imapMail->open_mailbox($folder->FullName, false))
		{
			$_imapUids = array();
			$_imapUidFlags = array();
			$_imapUidSizes = array();
				
			if ($imapUids == null)
			{
				//Get uid, flags and size from imap Server
				$paramsMessages = $this->_imapMail->getParamsMessages();
				if (!is_array($paramsMessages))
				{
					return $messageCollection;
				}
				
				foreach($paramsMessages as $key => $value)
				{
					$_imapUids[$key] = $value["uid"];
					$_imapUidFlags[$value["uid"]] = $value["flag"];
					$_imapUidSizes[$value["uid"]] = $value["size"];
				}
			}
			
			$messageCollection = new WebMailMessageCollection();
			foreach($messageIndexSet as $idx)
			{
				$response = $this->_imapMail->get_message($idx, $indexAsUid);
				if ($response)
				{
					$msg = new WebMailMessage();
					$msg->LoadMessageFromRawBody($response, true);
					if($indexAsUid)
					{
						$msg->Uid = $idx;
					} 
					else
					{
						if ($imapUids == null) 
						{
							$imapUids = $_imapUids;
						}
						$msg->Uid = $imapUids[$idx];
					}
					
					if ($imapUidSizes == null)
					{
						$imapUidSizes = $_imapUidSizes;
					}
									
					$msg->Size = $imapUidSizes[$msg->Uid];
					
					if ($imapUidFlags == null)
					{
						$imapUidFlags = $_imapUidSizes;
					}
					
					$this->_setMessageFlags($msg, $imapUidFlags[$idx]);
					$messageCollection->Add($msg);
					unset($msg);
				}
			}
			if ($messageCollection->Count() > 0)
			{
				return $messageCollection;
			}
		}
		return $messageCollection;
	}

	function GetBodyPartByIndex($bsIndex, $messageUid, $folder)
	{
		$out = '';
		if ($this->_imapMail->open_mailbox($folder->FullName, false))
		{
			$out = $this->_imapMail->getBodyPartByIndex($bsIndex, $messageUid);
		}
		return $out;
	}
	
	/**
	 * @param string $messageUid
	 * @param bool $indexAsUid
	 * @param Folder $folder
	 * @return WebMailMessage
	 */
	function LoadMessage($messageUid, $indexAsUid, &$folder, $mode = null)
	{
		$msg = null;
		if ($indexAsUid && $this->_imapMail->open_mailbox($folder->FullName, false))
		{
			if (null !== $mode)
			{
				$bodyStructureObject = $this->_imapMail->getMessageBodyStructure($messageUid);
				if ($bodyStructureObject && $bodyStructureObject->GetSize() > BODYSTRUCTURE_MGSSIZE_LIMIT)
				{
					$this->_imapMail->FillBodyStructureByMode($messageUid, $mode, $bodyStructureObject);

					$msg = new WebMailMessage();
					$msg->FillByBodyStructure($bodyStructureObject, $this->Account->GetDefaultIncCharset());
					$msg->Uid = $messageUid;
					$msg->Size = $bodyStructureObject->GetSize();
					$this->_setMessageFlags($msg, $bodyStructureObject->GetFlags());
				}
			}

			if (null === $msg)
			{
				$responseArray = $this->_imapMail->getMessageWithFlag($messageUid);
				if ($responseArray && count($responseArray) == 2)
				{
					$msg = new WebMailMessage();
					$msg->LoadMessageFromRawBody($responseArray[0], true);
					$msg->Uid = $messageUid;
					$msg->Size = strlen($responseArray[0]);
					$this->_setMessageFlags($msg, $responseArray[1]);
				}
				else
				{
					setGlobalError(PROC_MSG_HAS_DELETED);
				}
			}
		}
		return $msg;
	}

	/**
	 * @param int $pageNumber
	 * @param Folder $folder
	 * @param string $condition
	 * @param bool $inHeadersOnly
	 * @return WebMailMessageCollection
	 */
	function &DmImapSearchMessages($pageNumber, &$folder, $condition, $inHeadersOnly, &$refMsgCount)
	{
		$webMailMessageCollection = $paramsMessages = null;
		if ($this->_imapMail->open_mailbox($folder->FullName, false))
		{
			$searchRequest = 'OR (OR FROM "'.$condition.'" TO "'.$condition.'") SUBJECT "'.$condition.'"';
			if (!$inHeadersOnly)
			{
				$searchRequest = 'OR ('.$searchRequest.') BODY "'.$condition.'"';
			}
			
			$isSortSupport = $this->_imapMail->IsSortSupport();

			$order_by = null;
			if ($isSortSupport)
			{
				$order_by = $this->GetOrderByForImapSort();
			}

			$searchMessagesIndexsValues = $this->_imapMail->search_mailbox($searchRequest, 'UTF-8', $order_by);

			$msgCount = count($searchMessagesIndexsValues);
			if ($searchMessagesIndexsValues == false || $msgCount == 0)
			{
				$newcoll = new WebMailMessageCollection();
				return $newcoll;
			}

			$searchMessagesIndexsValues = array_reverse($searchMessagesIndexsValues);

			$pages = ceil($msgCount / $this->Account->MailsPerPage);

			if ($pageNumber > $pages)
			{
				$pageNumber = 1;
			}
			
			$refMsgCount = $msgCount;

			$messageIndexSet = array();
			$start = ($pageNumber - 1) * $this->Account->MailsPerPage;
			$messageIndexSet = array_slice($searchMessagesIndexsValues, $start, $this->Account->MailsPerPage);
			$webMailMessageCollection =& $this->LoadMessageHeadersInOneRequest($folder, $messageIndexSet);
		}
		
		return $webMailMessageCollection;
	}

	/**
	 * @param Folder $folder
	 * @param string $condition
	 * @return array|false
	 */
	function HeadersBodyImapSearchMessagesUids($folder, $condition)
	{
		$uids = false;
		if ($this->_imapMail->open_mailbox($folder->FullName, false))
		{
			$searchRequest = 'OR (OR FROM "'.$condition.'" TO "'.$condition.'") SUBJECT "'.$condition.'"';
			$searchRequest = 'OR ('.$searchRequest.') BODY "'.$condition.'"';

			$isSortSupport = $this->_imapMail->IsSortSupport();

			if ($isSortSupport)
			{
					$order_by = $this->GetOrderByForImapSort();
			}
			else
			{
					$order_by = null;
			}

			$uids = $this->_imapMail->uid_search_mailbox($searchRequest, 'UTF-8', $order_by);
		}
		return $uids;
	}

	/**
	 * @param int $pageNumber
	 * @param Folder $folder
	 * @return WebMailMessageCollection
	 */
	function &LoadMessageHeaders($pageNumber, &$folder)
	{
		$webMailMessageCollection = null;
		if ($this->_imapMail->open_mailbox($folder->FullName, false))
		{
			if ($folder->SyncType == FOLDERSYNC_DirectMode)
			{
				if ($folder->MessageCount < 1)
				{
					$newcoll = new WebMailMessageCollection();
					return $newcoll;
				}

				$msgCount = $folder->MessageCount;
				$messageIndexSet = array();

				$isSortSupport = $this->_imapMail->IsSortSupport();

				if ($isSortSupport)
				{
					$order_by = $this->GetOrderByForImapSort();
					$messageSortedIndexSet = array();
					$messageSortedIndexSet = $this->_imapMail->search_mailbox('', 'UTF-8', $order_by);
					$msgCount = count($messageSortedIndexSet);
				}
                                
				for ($i = $msgCount - ($pageNumber - 1) * $this->Account->MailsPerPage,
						$t = $msgCount - $pageNumber * $this->Account->MailsPerPage; $i > $t; $i--)
				{
					if ($isSortSupport)
					{
						if ($i < 1) break;
						$messageIndexSet[] = $messageSortedIndexSet[$i - 1];
					}
					else
					{
						if ($i == 0) break;
						$messageIndexSet[] = $i;
					}
				}

				$webMailMessageCollection =& $this->LoadMessageHeadersInOneRequest($folder, $messageIndexSet);
			}
			else
			{
				$paramsMessages = $this->_imapMail->getParamsMessages();
				$imapFlags = array();
				$imapUids = array();
				$imapSizes = array();
				if (!is_array($paramsMessages))
				{
					return $webMailMessageCollection;
				}

				foreach($paramsMessages as $key => $value)
				{
					$imapFlags[$key] = $value["flag"];
					$imapUids[$key] = $value["uid"];
					$imapSizes[$key] = $value["size"];
				}

				if(count($paramsMessages) < 1)
				{
					$newcoll = new WebMailMessageCollection();
					return $newcoll;
				}

				$msgCount = count($imapUids);
				$messageIndexSet = array();
				//$imapNFlags = $imapNSizes = array();
				for($i = $msgCount - ($pageNumber - 1) * $this->Account->MailsPerPage; 
					$i > $msgCount - $pageNumber * $this->Account->MailsPerPage; $i--)
				{
					if ($i == 0) break;
					$messageIndexSet[] = $imapUids[$i];
					//$imapNFlags[$imapUids[$i]] = $imapFlags[$i];
					//$imapNSizes[$imapUids[$i]] = $imapSizes[$i];
				}
				$webMailMessageCollection =& $this->LoadMessageHeadersInOneRequest($folder, $messageIndexSet, true);
			}
		}
		return $webMailMessageCollection;
	}

	/**
	 * @param FolderCollection $folders
	 * @return bool
	 */
	function SynchronizeFolder(&$folder)
	{
		$dbStorage = &DbStorageCreator::CreateDatabaseStorage($this->Account);
		if ($dbStorage->Connect())
		{
			return $this->_synchronizeFolderWithOpenDbConnection($folder, $dbStorage);
		}
		return false;
	}

	/**
	 * @param FolderCollection $folders
	 * @return bool
	 */
	function Synchronize(&$folders)
	{
		$result = true;
		$dbStorage =& DbStorageCreator::CreateDatabaseStorage($this->Account);
		if ($dbStorage->Connect() && $folders)
		{
			$folderList = $folders->CreateFolderListFromTree(); //copy tree object here
			for ($i = 0, $icount = $folderList->Count(); $i < $icount; $i++)
			{
				$folder =& $folderList->Get($i);
				$result &= $this->_synchronizeFolderWithOpenDbConnection($folder, $dbStorage);
				unset($folder);
				
				if (!$result)
				{
					break;
				}
			}
			return $result;
		}
		return false;
	}

	/**
	 * @return bool
	 */
	function SynchronizeFolders()
	{
		$result = true;
		if (!USE_DB)
		{
			return $result;
		}
			
		$dbStorage =& DbStorageCreator::CreateDatabaseStorage($this->Account);
		$serverFoldersTree =& $this->GetFolders();
		if ($serverFoldersTree && $dbStorage->Connect())
		{
			$dbFoldersTree =& $dbStorage->GetFolders();
			$serverFoldersList =& $serverFoldersTree->CreateFolderListFromTree();
			$dbFoldersList =& $dbFoldersTree->CreateFolderListFromTree();
			$delimiter = $this->Account->Delimiter;
			$serverFoldersListKeys = array_keys($serverFoldersList->Instance());
			foreach ($serverFoldersListKeys as $mkey)
			{
				$mailFolder =& $serverFoldersList->Get($mkey);
				$folderExist = false;
				$dbFoldersListKeys = array_keys($dbFoldersList->Instance());
				foreach ($dbFoldersListKeys as $skey)
				{
					$dbFolder =& $dbFoldersList->Get($skey);
					if (trim($mailFolder->FullName, $delimiter) == trim($dbFolder->FullName, $delimiter))
					{
						$folderExist = true;
						if ($mailFolder->SubFolders)
						{
							foreach(array_keys($mailFolder->SubFolders->Instance()) as $subkey)
							{
								$subFld =& $mailFolder->SubFolders->Get($subkey);
								$subFld->IdParent = $dbFolder->IdDb;
							}
						}
						
						if ($dbFolder->Hide != $mailFolder->Hide && $dbFolder->SyncType != FOLDERSYNC_DontSync)
						{
							$dbFolder->Hide = $mailFolder->Hide;
							$dbStorage->UpdateFolder($dbFolder);
						}
						
						break;
					}
					unset($dbFolder);
				}

				if (!$folderExist && $mailFolder)
				{
					$mailFolder->SyncType = $this->Account->GetDefaultFolderSync($this->_settings);
					if (FOLDERTYPE_Custom !== $mailFolder->Type)
					{
						$searchFolder =& $dbFoldersList->GetFolderByType($mailFolder->Type);
						if (null != $searchFolder)
						{
							$mailFolder->Type = FOLDERTYPE_Custom;
						}
					}
					
					$result &= $dbStorage->CreateFolder($mailFolder);
				}
			}

			$dbFoldersListKeys = array_keys($dbFoldersList->Instance());
			foreach ($dbFoldersListKeys as $skey)
			{
				$dbFolder =& $dbFoldersList->Get($skey);
				$folderExist = false;
				$serverFoldersListKeys = array_keys($serverFoldersList->Instance());
				foreach ($serverFoldersListKeys as $mkey)
				{
					$mailFolder =& $serverFoldersList->Get($mkey);
					if (trim($mailFolder->FullName, $delimiter) == trim($dbFolder->FullName, $delimiter))
					{
						$folderExist = true;
						break;
					}
					unset($mailFolder);
				}
				
				if (!$folderExist && $dbFolder->SyncType != FOLDERSYNC_DontSync)
				{
					if ($dbFolder->SyncType == FOLDERSYNC_DirectMode && $dbFolder->MessageCount == 0)
					{
						$dbStorage->DeleteFolder($dbFolder);
					}
					else
					{
						$dbFolder->SyncType = FOLDERSYNC_DontSync;
						$dbStorage->UpdateFolder($dbFolder);
					}
				}
			}
		}
		return $result;
	}
	
	/**
	 * @param array $paramsMessages
	 * @param array $imapUids
	 * @param array $imapSizes
	 * @param array $imapUidFlags
	 * @param array $imapUidSizes
	 */
	function _imapArrayForeach(&$paramsMessages, &$imapUids, &$imapSizes, &$imapUidFlags, &$imapUidSizes)
	{
		foreach ($paramsMessages as $key => $value)
		{
			$imapUids[$key] = $value["uid"];
			$imapSizes[$key] = $value["size"];
			$imapUidFlags[$value["uid"]] = $value["flag"];
			$imapUidSizes[$value["uid"]] = $value["size"];
		}
	}

	/**
	 * @param Folder $folders
	 * @param DbStorage $dbStorage
	 * @param int $lastIdMsg
	 * @return bool
	 */
	function _synchronizeFolderWithOpenDbConnection(&$folder, &$dbStorage)
	{
		$log =& CLog::CreateInstance();
		$result = true;
		if ($folder->SyncType == FOLDERSYNC_DontSync || $folder->SyncType == FOLDERSYNC_DirectMode || $folder->Hide)
		{
			if ($this->UpdateFolderHandler != null && $folder->SyncType == FOLDERSYNC_DirectMode)
			{
				call_user_func_array($this->UpdateFolderHandler, array($folder->IdDb, $folder->FullName));
			}
			return true;
		}

		$foldername = '';
		if ($this->DownloadedMessagesHandler != null)
		{
			$foldername = $folder->GetFolderName($this->Account);
			call_user_func_array($this->DownloadedMessagesHandler, array($foldername, 0));
		}
		
		if (!$this->_imapMail->open_mailbox($folder->FullName, false, true))
		{
			return true;
		}
		
		$_isAllUpdate = ($folder->SyncType == FOLDERSYNC_AllHeadersOnly || $folder->SyncType == FOLDERSYNC_AllEntireMessages);
		$_isUidsOnly = ($folder->SyncType == FOLDERSYNC_NewHeadersOnly); 
		
		/* get uid, flags and size from IMAP4 Server */
		if ($log->Enabled)
		{
			$start = getmicrotime();
		}

		$paramsMessages = $this->_imapMail->getParamsMessages();
		if ($log->Enabled)
		{
			$log->WriteLine('IMAP4: getParamsMessages()='.(getmicrotime() - $start));
		}

		if (!is_array($paramsMessages))
		{
			return false;
		}

		$imapUids = $imapSizes = $imapUidFlags = $imapUidSizes = array();
		$this->_imapArrayForeach($paramsMessages, $imapUids, $imapSizes, $imapUidFlags, $imapUidSizes);
		unset($paramsMessages);

		$dbUidsIdMsgsFlags =& $dbStorage->SelectIdMsgAndUidByIdMsgDesc($folder);
		
		$dbUids = $dbUidsFlag = array();
		foreach ($dbUidsIdMsgsFlags as $value)
		{
			$dbUids[] = $value[1];
			$dbUidsFlag[$value[1]] = $value[2];
		}
		unset($dbUidsIdMsgsFlags);
		
		/* array need added to DB */
		//$newUids = array_diff($imapUids, $dbUids);
		$newUids = array();
		foreach ($imapUids as $_imUid) 
		{
			if (!isset($dbUidsFlag[$_imUid]))
			{
				$newUids[] = $_imUid;
			}
		}
		
		if ($this->DownloadedMessagesHandler != null && count($newUids) > 0)
		{
			call_user_func_array($this->DownloadedMessagesHandler, array($foldername, count($newUids)));
		}
		
		if ($_isAllUpdate)
		{
			/* update flags */
			$_flags4Update = array();
			/* intersect uids */
			foreach ($imapUids as $_imUid) 
			{
				if (isset($dbUidsFlag[$_imUid]))
				{
					$flagBD = (int) $dbUidsFlag[$_imUid];
					$flagImap = (int) $this->getIntFlags($imapUidFlags[$_imUid]);
					/* update messages whith different flags */
					if ($flagBD != $flagImap)
					{
						$_flags4Update[$flagImap][] = $_imUid;
					}
				}
			}

			if (count($_flags4Update) > 0)
			{
				foreach ($_flags4Update as $_flag => $_uidArray)
				{
					if (is_array($_uidArray))
					{
						$dbStorage->UpdateMessageFlags($_uidArray, true, $folder, $_flag, $this->Account);
					}
				}
				if ($this->UpdateFolderHandler != null)
				{
					call_user_func_array($this->UpdateFolderHandler, array($folder->IdDb, $folder->FullName));
				}
			}
		
			/* delete from DB */
			
			//$uidsToDelete = array_diff($dbUids, $imapUids);
			$uidsToDelete = array();
			foreach ($dbUids as $_dbUid) 
			{
				if (!isset($imapUidFlags[$_dbUid]))
				{
					//$dbUidsFlag[$_dbUid] = $value[2];
					$uidsToDelete[] = $_dbUid;
				}
			}				
			if (count($uidsToDelete) > 0)
			{
				if ($this->UpdateFolderHandler != null)
				{
					call_user_func_array($this->UpdateFolderHandler, array($folder->IdDb, $folder->FullName));
				}

				// $result &= $dbStorage->SetMessagesFlags($uidsToDelete, true, $folder, MESSAGEFLAGS_Deleted, ACTION_Set);
				$result &= $dbStorage->DeleteMessages($uidsToDelete, true, $folder);
				$result &= $dbStorage->UpdateMailboxSize();
			}
		}
		
		$maxEnvelopesPerSession = 1;
		
		/* get size all messages in DB */
		$mailBoxesSize = $dbStorage->SelectMailboxesSize();
		
		$filters =& $dbStorage->SelectFilters($this->Account->Id, true);

		if ($folder->SyncType == FOLDERSYNC_NewHeadersOnly || $folder->SyncType == FOLDERSYNC_AllHeadersOnly)
		{
			$syncCycles = ceil(count($newUids) / MAX_ENVELOPES_PER_SESSION);
			for ($q = 0; $q < $syncCycles; $q++)
			{
				if (!$this->_imapMail->open_mailbox($folder->FullName))
				{
					return true;
				}
				
				$cyclesNewUids = array_slice($newUids, $q * MAX_ENVELOPES_PER_SESSION, MAX_ENVELOPES_PER_SESSION);

				$mailMessageCollection = null;
				$mailMessageCollection =& $this->LoadMessageHeadersInOneRequest($folder, $cyclesNewUids, true);

				if ($mailMessageCollection)
				{
					for ($i = 0, $c = $mailMessageCollection->Count(); $i < $c; $i++)
					{
						if ($this->DownloadedMessagesHandler != null && function_exists($this->DownloadedMessagesHandler))
						{
							call_user_func($this->DownloadedMessagesHandler);
						}

						$message =& $mailMessageCollection->Get($i);

						$mailBoxesSize += $message->Size;

						if ($this->_settings->EnableMailboxSizeLimit && $this->Account->MailboxLimit > 0 && $this->Account->MailboxLimit < $mailBoxesSize)
						{
							$result = false;
							setGlobalError(ErrorGetMailLimit);
							break 2;
						}

						if (!$this->ApplyFilters($message, $dbStorage, $folder, $filters))
						{
							$result = false;
						}
					}
				}
			}
		}
		else
		{
			$syncCycles = ceil(count($newUids) / $maxEnvelopesPerSession);
			for ($i = 0; $i < $syncCycles; $i++)
			{
				$mailBoxesSize += $imapSizes[$i + 1];

				if ($this->_settings->EnableMailboxSizeLimit && $this->Account->MailboxLimit > 0 && $this->Account->MailboxLimit < $mailBoxesSize)
				{
					$result = false;
					setGlobalError(ErrorGetMailLimit);
					break;
				}

				if (!$this->_imapMail->open_mailbox($folder->FullName))
				{
					return true;
				}

				$listPartToDownload = ($i != $syncCycles - 1) ? array_slice($newUids, $i * $maxEnvelopesPerSession, $maxEnvelopesPerSession) : array_slice($newUids, $i * $maxEnvelopesPerSession);

				if ($this->DownloadedMessagesHandler != null && function_exists($this->DownloadedMessagesHandler))
				{
					call_user_func($this->DownloadedMessagesHandler);
				}

				$mailMessageCollection = null;
				$mailMessageCollection =& $this->LoadMessages($listPartToDownload, true, $folder, $imapUids, $imapUidFlags, $imapUidSizes);

				if ($mailMessageCollection && $mailMessageCollection->Count() > 0)
				{
					$message =& $mailMessageCollection->Get(0);
					if (!$this->ApplyFilters($message, $dbStorage, $folder, $filters))
					{
						$result = false;
						break;
					}
				}
			}
		}
		
		$result &= $dbStorage->UpdateMailboxSize();
		return $result;
	}

	/**
	 * @param array $messageIdUidSet
	 * @param Folder $fromFolder
	 * @return bool
	 */
	function SpamMessages($messageIdUidSet, $fromFolder, $isSpam = true)
	{
		if (false && $this->Account && $this->Account->IsInternal) // !!! off
		{
			$messageUids = array_values($messageIdUidSet);

			$toFolder = ($isSpam)
				? new Folder($this->Account->Id, -1, FOLDERFULLNAME_SharedSpam, FOLDERNAME_SharedSpam, FOLDERSYNC_AllHeadersOnly)
				: new Folder($this->Account->Id, -1, FOLDERFULLNAME_SharedUnSpam, FOLDERNAME_SharedUnSpam, FOLDERSYNC_AllHeadersOnly);

			return $this->SetEximSpamMessages($messageUids, $fromFolder, $toFolder);
		}
		
		return true;
	}

	/**
	 * @param array $indexs
	 * @param bool $indexsAsUid = false
	 * @return WebMailMessageCollection | null
	 */
	function &LoadMessageHeadersInOneRequest_Old($folder, $indexs, $indexsAsUid = false, $imapFlags = null, $imapSizes = null)
	{
		$messageCollection = null;
		$indexsStr = trim(implode(',', $indexs));
		$preText = ' ';
		if (null === $imapFlags)
		{
			$preText .= 'FLAGS ';
		}
		if (null === $imapSizes)
		{
			$preText .= 'RFC822.SIZE ';
		}

		$rString = 'FETCH '.$indexsStr.' (UID'.$preText.'BODY.PEEK[HEADER])';
//		$rString = 'FETCH '.$indexsStr.' (UID'.$preText.'BODY.PEEK[HEADER.FIELDS (RETURN-PATH RECEIVED MIME-VERSION FROM TO CC DATE SUBJECT X-MSMAIL-PRIORITY IMPORTANCE X-PRIORITY CONTENT-TYPE)])';
		if ($indexsAsUid)
		{
			$rString = 'UID '.$rString;
		}

		$responseArray = $this->_imapMail->getResponseAsArray($rString);
		if (is_array($responseArray))
		{
			$messageCollection = new WebMailMessageCollection();
			$headersString = implode('', $responseArray);

			$pieces = preg_split('/\* [\d]+ FETCH /', $headersString);
			foreach ($pieces as $key => $text)
			{
				$uid = $size = $flags = null;
				$lines = explode("\n", trim($text));
				$firstline = array_shift($lines);
				$lastline = array_pop($lines);
				$matchUid = $matchSize = $matchFlags = array();

				preg_match('/UID (\d+)/', $firstline, $matchUid);
				if (isset($matchUid[1]))
				{
					$uid = (int) $matchUid[1];
				}

				if (null === $imapFlags)
				{
					preg_match('/FLAGS \(([^\)]*)\)/', $firstline, $matchFlags);
					if (isset($matchFlags[1]))
					{
						$flags = trim(trim($matchFlags[1]), '()');
					}
				}
				else if (isset($imapFlags[$uid]))
				{
					$flags = $imapFlags[$uid];
				}

				if (null === $imapSizes)
				{
					preg_match('/RFC822\.SIZE ([\d]+)/', $firstline, $matchSize);
					if (isset($matchSize[1]))
					{
						$size = (int) $matchSize[1];
					}
				}
				else if (isset($imapSizes[$uid]))
				{
					$size = (int) $imapSizes[$uid];
				}

				if (null === $uid)
				{
					$match = array();
					preg_match('/UID (\d+)/', $lastline, $match);
					if (isset($match[1]))
					{
						$uid = (int) $match[1];
					}
				}

				$text = implode("\n", $lines);
				$pieces[$key] = array($uid, trim($text), $size, $flags);
			}

			if (!$this->_imapMail->IsSortSupport())
			{
				arsort($pieces);
			}

			foreach ($pieces as $headerArray)
			{
				if (is_array($headerArray) && count($headerArray) == 4 && $headerArray[0] > 0 && strlen($headerArray[1]) > 10)
				{
					$msg = new WebMailMessage();
					$msg->LoadMessageFromRawBody($headerArray[1]);
					$msg->IdFolder = $folder->IdDb;
					$msg->IdMsg = $headerArray[0];
					$msg->Uid = $headerArray[0];
					$msg->Size = (int) $headerArray[2];
					$this->_setMessageFlags($msg, $headerArray[3]);
					$messageCollection->Add($msg);
					unset($msg);
				}
			}
		}

		return $messageCollection;
	}

	/**
	 * @param array $indexs
	 * @param bool $indexsAsUid = false
	 * @return WebMailMessageCollection | null
	 */
	function &LoadMessageHeadersInOneRequest($folder, $indexs, $indexsAsUid = false, $imapFlags = null, $imapSizes = null)
	{
		$messageCollection = null;
		$indexsStr = trim(implode(',', $indexs));
		$preText = ' ';
		if (null === $imapFlags)
		{
			$preText .= 'FLAGS ';
		}
		if (null === $imapSizes)
		{
			$preText .= 'RFC822.SIZE ';
		}

		$rString = 'FETCH '.$indexsStr.' (UID'.$preText.'BODY.PEEK[HEADER])';
//		$rString = 'FETCH '.$indexsStr.' (UID'.$preText.'BODY.PEEK[HEADER.FIELDS (RETURN-PATH RECEIVED MIME-VERSION FROM TO CC DATE SUBJECT X-MSMAIL-PRIORITY IMPORTANCE X-PRIORITY CONTENT-TYPE)])';
		if ($indexsAsUid)
		{
			$rString = 'UID '.$rString;
		}

		$responseArray = $this->_imapMail->getResponseAsArray($rString);
		if (is_array($responseArray))
		{
			$messageCollection = new WebMailMessageCollection();
			$headersString = implode('', $responseArray);
			unset($responseArray);

			$piecesOut = array();
			$pieces = preg_split('/\* [\d]+ FETCH /', $headersString);

			$tmpArray = array();
			preg_match_all('/\* ([\d]+) FETCH /', $headersString, $tmpArray);
			$piecesFetchId = (isset($tmpArray[1])) ? $tmpArray[1] : array();
			unset($tmpArray, $headersString);
			
			foreach ($pieces as $key => $text)
			{
				if (isset($piecesFetchId[$key - 1]))
				{
					$index = $piecesFetchId[$key - 1];
					$uid = $size = $flags = null;
					$lines = explode("\n", trim($text));
					$firstline = array_shift($lines);
					$lastline = array_pop($lines);
					$matchUid = $matchSize = $matchFlags = array();

					preg_match('/UID (\d+)/', $firstline, $matchUid);
					if (isset($matchUid[1]))
					{
						$uid = (int) $matchUid[1];
					}

					if (null === $imapFlags)
					{
						preg_match('/FLAGS \(([^\)]*)\)/', $firstline, $matchFlags);
						if (isset($matchFlags[1]))
						{
							$flags = trim(trim($matchFlags[1]), '()');
						}
					}
					else if (isset($imapFlags[$uid]))
					{
						$flags = $imapFlags[$uid];
					}

					if (null === $imapSizes)
					{
						preg_match('/RFC822\.SIZE ([\d]+)/', $firstline, $matchSize);
						if (isset($matchSize[1]))
						{
							$size = (int) $matchSize[1];
						}
					}
					else if (isset($imapSizes[$uid]))
					{
						$size = (int) $imapSizes[$uid];
					}

					if (null === $uid)
					{
						$match = array();
						preg_match('/UID (\d+)/', $lastline, $match);
						if (isset($match[1]))
						{
							$uid = (int) $match[1];
						}
					}

					$piecesOut[($indexsAsUid) ? $uid : $index] = array($uid, trim(implode("\n", $lines)), $size, $flags);
				}
			}

			unset($pieces);

			foreach ($indexs as $value)
			{
				if (isset($piecesOut[$value]))
				{
					$headerArray = $piecesOut[$value];
					if (is_array($headerArray) && count($headerArray) == 4 && $headerArray[0] > 0 && strlen($headerArray[1]) > 10)
					{
						$msg = new WebMailMessage();
						$msg->LoadMessageFromRawBody($headerArray[1]);
						$msg->IdFolder = $folder->IdDb;
						$msg->IdMsg = $headerArray[0];
						$msg->Uid = $headerArray[0];
						$msg->Size = (int) $headerArray[2];
						$this->_setMessageFlags($msg, $headerArray[3]);
						$messageCollection->Add($msg);
						unset($msg);
					}
				}
			}
		}

		return $messageCollection;
	}

	/**
	 * @return FolderCollection
	 */
	function GetFolders()
	{
		ConvertUtils::SetLimits();
		
		$lastD = $this->Account->Delimiter;
		$folderCollection = new FolderCollection();
		$folders =& $this->_imapMail->list_mailbox($this->Account->Delimiter);

		$subsScrFolders = $this->_imapMail->list_subscribed_mailbox($this->Account->Delimiter);

		$existsIndex = array('VirusAdd' => true);
		$folderCollection = $this->GetFolderCollectionFromArrays($folders, $subsScrFolders, $this->Account->Delimiter, $existsIndex);

		if ($lastD != $this->Account->Delimiter)
		{
			$this->Account->UpdateDelimiter();
		}

		/* custom class */
		wm_Custom::StaticUseMethod('ChangeServerImapFoldersAfterGet', array(&$folderCollection));
		
		return $folderCollection;
	}

	/**
	 * @return FolderCollection
	 */
	function GetLSubFolders()
	{
		ConvertUtils::SetLimits();

		$subsScrFolders = $this->_imapMail->list_subscribed_mailbox($this->Account->Delimiter);

		return $subsScrFolders;
	}

	/**
	 * @param Folder $folder
	 * @return bool
	 */
	function CreateFolder(&$folder)
	{
		if ($this->_imapMail->create_mailbox($folder->FullName))
		{
			if (!$folder->Hide)
			{
				$this->_imapMail->subscribe_mailbox($folder->FullName);
			}
			return true;
		}
		return false;
	}
	
	function SubscribeFolder(&$folder, $isHide = false)
	{
		if ($isHide)
		{
			return (USE_LSUB) ? false : $this->_imapMail->unsubscribe_mailbox($folder->FullName);
		}
		else
		{
			return $this->_imapMail->subscribe_mailbox($folder->FullName);
		}
	}

	/**
	 * @param Folder $folder
	 * @return bool
	 */
	function DeleteFolder(&$folder)
	{
		if ($this->_imapMail->delete_mailbox($folder->FullName))
		{
			$this->_imapMail->unsubscribe_mailbox($folder->FullName);
			return true;
		}
		return false;;
	}

	/**
	 * @param Folder $folder
	 * @param string $newName
	 * @param array $aLsubFolder
	 * @return bool
	 */
	function RenameFolder(&$folder, $newName, $aLsubFolder, $sDelimiter = '/')
	{
		if ($folder && $folder->FullName != $newName && $this->_imapMail->rename_mailbox($folder->FullName, $newName))
		{
			if (is_array($aLsubFolder))
			{
				foreach ($aLsubFolder as $sLSubFullName)
				{
					if (0 === strpos($sLSubFullName, $folder->FullName.$sDelimiter))
					{
						$this->_imapMail->unsubscribe_mailbox($sLSubFullName);
						$sNewFullName = $newName.$sDelimiter
							.substr($sLSubFullName, strlen($folder->FullName.$sDelimiter));
						$this->_imapMail->subscribe_mailbox($sNewFullName);
					}
				}
			}

			$this->_imapMail->unsubscribe_mailbox($folder->FullName);
			
			if (!$folder->Hide)
			{
				$this->_imapMail->subscribe_mailbox($newName);
			}
			
			return true;
		}
		return false;
	}

	/**
	 * @return bool
	 */
	function IsQuotaSupport()
	{
		return $this->_imapMail->IsQuotaSupport();
	}

	/**
	 * @return int | false
	 */
	function GetQuota()
	{
		return $this->_imapMail->get_quota();
	}
	
	/**
	 * @return int | false
	 */
	function GetUsedQuota()
	{
		return $this->_imapMail->get_used_quota();
	}
	
	/**
	 * @param WebMailMessage $message
	 * @param Folder $folder
	 * @return bool
	 */
	function SaveMessage(&$message, &$folder)
	{
		$flagsStr = '';
		if(($message->Flags & MESSAGEFLAGS_Seen) == MESSAGEFLAGS_Seen)
		{
			$flagsStr .= ' \Seen';
		}
		if(($message->Flags & MESSAGEFLAGS_Flagged) == MESSAGEFLAGS_Flagged)
		{
			$flagsStr .= ' \Flagged';
		}
		if(($message->Flags & MESSAGEFLAGS_Deleted) == MESSAGEFLAGS_Deleted)
		{
			$flagsStr .= ' \Deleted';
		}
		if(($message->Flags & MESSAGEFLAGS_Answered) == MESSAGEFLAGS_Answered)
		{
			$flagsStr .= ' \Answered';
		}
		return $this->_imapMail->append_mail($folder->FullName, $flagsStr, $message->TryToGetOriginalMailMessage());
	}

	/**
	 * @param WebMailMessageCollection $messages
	 * @param Folder $folder
	 * @return bool
	 */
	function SaveMessages(&$messages, &$folder)
	{
		$result = true;
		for ($i = 0, $c = $messages->Count(); $i < $c; $i++)
		{
			$result &= $this->SaveMessage($messages->Get($i), $folder);
		}
		return $result;
	}

	function SetFolderType(&$folderObj, &$existsIndex)
	{
		switch ($folderObj->Type)
		{
			case FOLDERTYPE_Inbox:
				if (isset($existsIndex['InboxAdd'])) $folderObj->Type = FOLDERTYPE_Custom;
				$existsIndex['InboxAdd'] = true;
				break;
			case FOLDERTYPE_SentItems:
				if (isset($existsIndex['SentAdd'])) $folderObj->Type = FOLDERTYPE_Custom;
				$existsIndex['SentAdd'] = true;
				break;
			case FOLDERTYPE_Drafts:
				if (isset($existsIndex['DraftsAdd'])) $folderObj->Type = FOLDERTYPE_Custom;
				$existsIndex['DraftsAdd'] = true;
				break;
			case FOLDERTYPE_Spam:
				if (isset($existsIndex['SpamAdd'])) $folderObj->Type = FOLDERTYPE_Custom;
				$existsIndex['SpamAdd'] = true;
				break;
			case FOLDERTYPE_Trash:
				if ($this->_settings && $this->_settings->Imap4DeleteLikePop3)
				{
					if (isset($existsIndex['TrashAdd'])) $folderObj->Type = FOLDERTYPE_Custom;
				}
				else 
				{
					$folderObj->Type = FOLDERTYPE_Custom;
				}
				$existsIndex['TrashAdd'] = true;
				break;
			default:
				$folderObj->Type = FOLDERTYPE_Custom;
				break;
		}
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
	 * @param string $messageUid
	 * @param Folder $folder
	 * @param int $flags
	 * @param short $action
	 * @return bool
	 */
	function SetMessagesFlag($messageUid, &$folder, $flags, $action)
	{
		$messageUidSet = array($messageUid);
		return $this->SetMessagesFlags($messageUidSet, true, $folder, $flags, $action);
	}

	/**
	 * return bool
	 */
	function IsMailBoxEmpty()
	{
		return $this->_imapMail->isMailBoxEmpty();
	}

	/**
	 * @param Array $messageIndexSet
	 * @param bool $indexAsUid
	 * @param Folder $folder
	 * @param int $flags
	 * @param short $action
	 * @return bool
	 */
	function SetMessagesFlags(&$messageIndexSet, $indexAsUid, &$folder, $flags, $action)
	{
		if ($this->_imapMail->open_mailbox($folder->FullName, false))
		{
			$flagsStr = '';
			if(($flags & MESSAGEFLAGS_Seen) == MESSAGEFLAGS_Seen)
			{
				$flagsStr .= ' \Seen';
			}
			if(($flags & MESSAGEFLAGS_Flagged) == MESSAGEFLAGS_Flagged)
			{
				$flagsStr .= ' \Flagged';
			}
			if(($flags & MESSAGEFLAGS_Deleted) == MESSAGEFLAGS_Deleted)
			{
				$flagsStr .= ' \Deleted';
			}
			if (($flags & MESSAGEFLAGS_Answered) == MESSAGEFLAGS_Answered)
			{
				$flagsStr .= ' \Answered';
			}

			$messageIndexes = $actionName = null;
			switch($action)
			{
				case ACTION_Set:
					$actionName = '+FLAGS';
					break;
				case ACTION_Remove:
					$actionName = '-FLAGS';
					break;
			}

			if ($messageIndexSet == null)
			{
				$messageIndexes = '1:*';
				$indexAsUid = false;
				if ($this->isMailBoxEmpty())
				{
					return true;
				}
			}
			else
			{
				$messageIndexes = implode(',', $messageIndexSet);
			}

			if (null !== $actionName && '' !== $flagsStr)
			{
				if ($indexAsUid)
				{
					return $this->_imapMail->uid_store_mail_flag($messageIndexes, $actionName, $flagsStr);
				}
				else
				{
					return $this->_imapMail->store_mail_flag($messageIndexes, $actionName, $flagsStr);
				}
			}
		}
		return false;
	}

	/**
	 * @param Array $messageIndexSet
	 * @param bool $indexAsUid
	 * @param Folder $folder
	 * @return bool
	 */
	function DeleteMessages(&$messageIndexSet, $indexAsUid, &$folder)
	{
		return $this->SetMessagesFlags($messageIndexSet, $indexAsUid, $folder, MESSAGEFLAGS_Deleted, ACTION_Set);
	}
	
	/**
	 * @param Array $messageIndexSet
	 * @param Folder $folder
	 * @return bool
	 */
	function SetDeleteFlagAndPurgeByUids(&$messageUidSet, &$folder)
	{
		return $this->SetMessagesFlags($messageUidSet, true, $folder, MESSAGEFLAGS_Deleted, ACTION_Set) &&
			$this->PurgeUidOrFolder($folder, $messageUidSet);
	}
	
	/**
	 * @param Array $messageIndexSet
	 * @param bool $indexAsUid
	 * @param Folder $folder
	 * @param Folder $toFolder
	 * @return bool
	 */
	function MoveMessages(&$messageIndexSet, $indexAsUid, &$folder, &$toFolder)
	{
		if ($folder->IdDb != $toFolder->IdDb)
		{
			return $this->CopyMessages($messageIndexSet, $indexAsUid, $folder, $toFolder) &
				$this->DeleteMessages($messageIndexSet, $indexAsUid, $folder) &
				$this->PurgeFolder($folder);
		}
		return true;
	}

	/**
	 * @param Folder $folder
	 * @return bool
	 */
	function PurgeFolder(&$folder)
	{
		return $this->_imapMail->open_mailbox($folder->FullName, false) && $this->_imapMail->expunge_mailbox();
	}
	
	/**
	 * @param Folder $folder
	 * @param Array $arrayUids
	 */
	function PurgeUidFolder(&$folder, $arrayUids)
	{
		if (is_array($arrayUids) && count($arrayUids) > 0)
		{
			$strUids = implode(',', $arrayUids);
			return $this->_imapMail->open_mailbox($folder->FullName, false) && $this->_imapMail->expunge_uid_mailbox($strUids);
		}
		
		return true;
	}

	/**
	 * @param Folder $folder
	 * @param Array $arrayUids
	 */
	function PurgeUidOrFolder(&$folder, $arrayUids)
	{
		if (is_array($arrayUids) && count($arrayUids) > 0)
		{
			$strUids = implode(',', $arrayUids);
			if ($this->_imapMail->open_mailbox($folder->FullName, false))
			{
				return $this->_imapMail->expunge_uid_or_not_mailbox($strUids);
			}
		}
		
		return false;
	}
	
	/**
	 * @param Array $messageIndexSet
	 * @param bool $indexAsUid
	 * @param Folder $fromFolder
	 * @param Folder $toFolder
	 * @return bool
	 */
	function CopyMessages(&$messageIndexSet, $indexAsUid, &$fromFolder, &$toFolder)
	{
		$messageIndexes = implode(',', $messageIndexSet);
		if ($this->_imapMail->open_mailbox($fromFolder->FullName, false))
		{
			return ($indexAsUid)
				? $this->_imapMail->uid_copy_mail($messageIndexes, $toFolder->FullName)
				: $this->_imapMail->copy_mail($messageIndexes, $toFolder->FullName);
		}
		return false;
	}

	function SetEximSpamMessages($messageUidSet, $fromFolder, $toFolder)
	{
		$messageUids = trim(implode(',', $messageUidSet));
		if (strlen($messageUids) > 0)
		{
			if ($this->_imapMail->open_mailbox($fromFolder->FullName, false))
			{
				return $this->_imapMail->uid_copy_mail($messageUids, $toFolder->FullName);
			}
		}
		
		return false;
	}

	/**
	 * @param Folder $folder
	 * @return bool
	 */
	function GetFolderMessageCount(&$folder)
	{
		$countArray = $this->_imapMail->get_all_and_unnread_msg_count($folder->FullName);
		if($countArray == null)
		{
			return false;
		}
		$folder->MessageCount = $countArray[HKC_ALL_MSG];
		$folder->UnreadMessageCount = $countArray[HKC_UNSEEN_MSG];
		return true;
	}

	/**
	 * @access private
	 * @param WebMailMessage $message
	 * @param string $flags
	 */
	function _setMessageFlags(&$message, $flags)
	{
		$message->Flags = $this->getIntFlags($flags);
	}

	/**
	 * @param String $strFlags
	 * @return Integer
	 */
	function getIntFlags($flags)
	{
		$intFlags = 0;
		$flags = explode(' ', strtolower($flags));
		foreach($flags as $flag)
		{
			switch(trim($flag))
			{
				case '\seen':		$intFlags |= MESSAGEFLAGS_Seen;		break;
				case '\answered':	$intFlags |= MESSAGEFLAGS_Answered;	break;
				case '\flagged':	$intFlags |= MESSAGEFLAGS_Flagged;	break;
				case '\deleted':	$intFlags |= MESSAGEFLAGS_Deleted;	break;
				case '\draft':		$intFlags |= MESSAGEFLAGS_Draft;	break;
				case '\recent':		$intFlags |= MESSAGEFLAGS_Recent;	break;
			}
		}
		return $intFlags;
	}

	function GetOrderByForImapSort()
	{
		$result = '';
		switch ($this->Account->DefaultOrder)
		{
			case DEFAULTORDER_FromDesc:		$result = 'FROM';				break;
			case DEFAULTORDER_From:			$result = 'REVERSE FROM';		break;
			case DEFAULTORDER_ToDesc:		$result = 'TO';					break;
			case DEFAULTORDER_To:			$result = 'REVERSE TO';			break;
			case DEFAULTORDER_SubjDesc:		$result = 'SUBJECT';			break;
			case DEFAULTORDER_Subj:			$result = 'REVERSE SUBJECT';	break;
			case DEFAULTORDER_DateDesc:		$result = 'ARRIVAL';			break;
			case DEFAULTORDER_Date:			$result = 'REVERSE ARRIVAL';	break;
			case DEFAULTORDER_SizeDesc:		$result = 'SIZE';				break;
			case DEFAULTORDER_Size:			$result = 'REVERSE SIZE';		break;
		}
		return $result;
	}

}
