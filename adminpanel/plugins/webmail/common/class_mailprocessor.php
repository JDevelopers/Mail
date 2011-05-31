<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	class MailProcessor
	{
		/**
		 * @var ImapStorage|Pop3Storage
		 */
		var $MailStorage = null;
		
		/**
		 * @var MySqlStorage|MsSqlStorage
		 */
		var $DbStorage = null;
		
		/**
		 * @access private
		 * @var Account
		 */
		var $_account;
		
		/**
		 * @var	string
		 */
		var $_error = '';
		
		/**
		 * @param	Account			$account
		 * @param	MySqlStorage	$dbStorage
		 * @param	string			$pathToClassFolder
		 * @return	MailProcessor
		 */
		function MailProcessor(&$account, &$dbStorage, $pathToClassFolder)
		{
			require_once $pathToClassFolder.'class_mailstorage.php';
			require_once $pathToClassFolder.'class_filesystem.php';
			
			$this->_account =& $account;
			
			switch ($account->MailProtocol)
			{
				case WM_MAILPROTOCOL_POP3:
					require_once $pathToClassFolder.'class_pop3storage.php';
					$this->MailStorage = new Pop3Storage($account, $pathToClassFolder);
					break;
					
				case WM_MAILPROTOCOL_IMAP4:
					require_once $pathToClassFolder.'class_imapstorage.php';
					$this->MailStorage = new ImapStorage($account, $pathToClassFolder);
					break;
			}
			
			$this->DbStorage =& $dbStorage;
		}
		
		/**
		 * @param	Account		$account
		 * @param	int			$sync = null
		 * @return	bool
		 */
		function CreateAccount(&$account, $sync = null)
		{
			$result = true;
			if ($this->DbStorage->Connect())
			{
				if ($account->DefaultAccount && !$account->IsInternal)
				{
					$defaultAccountArray = $this->DbStorage->SelectAccountDataByLogin($account->Email, $account->MailIncLogin, true);
					if (is_array($defaultAccountArray) && count($defaultAccountArray) > 0)
					{
						$this->SetError(ap_Utils::TakePhrase('WM_CANT_ADD_DEF_ACCT'));
						$result = false;
					}
				}
								
				if ($result)
				{
					if ($account->MailProtocol == WM_MAILPROTOCOL_IMAP4)
					{
						if (!$this->IsQuotaSupport())
						{
							$account->ImapQuota = -1;
						}

						if ($account->ImapQuota === 1)
						{
							$quota = $this->GetQuota();
							if (false !== $quota && $quota > 0)
							{
								$account->MailboxLimit = GetGoodBigInt($quota);
							}
							else
							{
								$account->ImapQuota = -1;
							}
						}
					}

					if ($this->DbStorage->InsertUserData($account))
					{
						$addFolders = null;
						$folders = new FolderCollection();
						$result = ($account->IsInternal)
							? $this->DbStorage->UpdateEximAccountData($account)
							: $this->DbStorage->InsertAccountData($account);
						
						if ($result)
						{
							switch ($account->MailProtocol)
							{
								case WM_MAILPROTOCOL_POP3:
									$inboxFolder = new Folder($account->Id, -1, WM_FOLDERNAME_Inbox, WM_FOLDERNAME_Inbox, WM_FOLDERSYNC_DontSync, WM_FOLDERTYPE_Inbox);
									if ($inboxFolder)
									{
										if ($account->_settings->AllowDirectMode && $account->_settings->DirectModeIsDefault)
										{
											$inboxFolder->SyncType = WM_FOLDERSYNC_DirectMode;
										}
										else
										{
											$inboxFolder->SyncType = ($sync !== null) ? $sync : WM_FOLDERSYNC_AllEntireMessages;
										}
									}

									$createSystemFoldersInInbox = false;
									$createSystemFoldersNameSpace = ($inboxFolder && 0 === strpos($nameSpacePrefix, $inboxFolder->FullName));

									/* custom class */
									ap_Custom::StaticUseMethod('wm_ChangeValueOfSystemFoldersInInbox', array(&$createSystemFoldersInInbox));

									$folderPrefix =	'';
									if ($createSystemFoldersInInbox && $inboxFolder)
									{
										$folderPrefix = $inboxFolder->FullName.$account->Delimiter;
										$inboxFolder->SubFolders = ($inboxFolder->SubFolders) ? $inboxFolder->SubFolders : new FolderCollection();
										$addFolders =& $inboxFolder->SubFolders;
									}
									else
									{
										$addFolders =& $folders;
									}
									
									$folders->Add($inboxFolder);
									$addFolders->Add(new Folder($account->Id, -1, $folderPrefix.WM_FOLDERNAME_SentItems, WM_FOLDERNAME_SentItems, WM_FOLDERSYNC_DontSync, WM_FOLDERTYPE_SentItems));
									$addFolders->Add(new Folder($account->Id, -1, $folderPrefix.WM_FOLDERNAME_Drafts, WM_FOLDERNAME_Drafts, WM_FOLDERSYNC_DontSync, WM_FOLDERTYPE_Drafts));
									$addFolders->Add(new Folder($account->Id, -1, $folderPrefix.WM_FOLDERNAME_Spam, WM_FOLDERNAME_Spam, WM_FOLDERSYNC_DontSync, WM_FOLDERTYPE_Spam));
									$addFolders->Add(new Folder($account->Id, -1, $folderPrefix.WM_FOLDERNAME_Trash, WM_FOLDERNAME_Trash, WM_FOLDERSYNC_DontSync, WM_FOLDERTYPE_Trash));
									break;
									
								case WM_MAILPROTOCOL_IMAP4:
									$accountDelimiter = $account->Delimiter;
									$folders =& $this->MailStorage->GetFolders($accountDelimiter);
									if ($accountDelimiter != $account->Delimiter)
									{
										$account->Delimiter = $accountDelimiter;
										$this->DbStorage->UpdateAccountDelimiter($account->Id, $accountDelimiter);
									}

									$result &= ($folders != null);

									if ($result)
									{
										$inb =& $folders->GetFolderByType(WM_FOLDERTYPE_Inbox);
										if ($inb === null)
										{
											$result = false;
											break;
										}

										$inboxSyncType = ($sync !== null) ? $sync : WM_FOLDERSYNC_AllHeadersOnly;
										
										if ($account->_settings->AllowDirectMode && $account->_settings->DirectModeIsDefault)
										{
											$inboxSyncType = WM_FOLDERSYNC_DirectMode;
										}
										
										$folders->SetSyncTypeToAll($inboxSyncType);

										$account->NameSpace = $this->MailStorage->GetNameSpacePrefix();
										$this->DbStorage->UpdateAccountNameSpace($account->Id, $account->NameSpace);

										$createSystemFoldersInInbox = (0 === strpos($account->NameSpace, $inb->FullName));
										$createFoldersIfNotExist = $account->IsInternal;

										/* custom class */
										ap_Custom::StaticUseMethod('wm_ChangeValueOfSystemFoldersInInbox', array(&$createSystemFoldersInInbox));
										ap_Custom::StaticUseMethod('wm_ChangeValueOfCreateFolderIfNotExist', array(&$createFoldersIfNotExist));

										$folderPrefix =	'';
										if ($createSystemFoldersInInbox)
										{
											$folderPrefix = $inb->FullName.$account->Delimiter;
											$inb->SubFolders = ($inb->SubFolders) ? $inb->SubFolders : new FolderCollection();
											$addFolders =& $inb->SubFolders;
										}
										else
										{
											$addFolders =& $folders;
										}

										$s = $d = $t = $sp = null;

										$s =& $folders->GetFolderByType(WM_FOLDERTYPE_SentItems);
										$d =& $folders->GetFolderByType(WM_FOLDERTYPE_Drafts);
										if ($account->_settings->Imap4DeleteLikePop3)
										{
											$t =& $folders->GetFolderByType(WM_FOLDERTYPE_Trash);
										}
										$sp =& $folders->GetFolderByType(WM_FOLDERTYPE_Spam);

										if ($s === null)
										{
											$sentFolder = new Folder($account->Id, -1, $folderPrefix.WM_FOLDERNAME_SentItems, WM_FOLDERNAME_SentItems, WM_FOLDERSYNC_DontSync, WM_FOLDERTYPE_SentItems);
											if ($createFoldersIfNotExist)
											{
												$sentFolder->SetFolderSync($inboxSyncType);
												$this->MailStorage->CreateFolder($sentFolder);
											}
											$addFolders->Add($sentFolder);
										}

										if ($d === null)
										{
											$draftsFolder = new Folder($account->Id, -1, $folderPrefix.WM_FOLDERNAME_Drafts, WM_FOLDERNAME_Drafts, WM_FOLDERSYNC_DontSync, WM_FOLDERTYPE_Drafts);
											if ($createFoldersIfNotExist)
											{
												$draftsFolder->SetFolderSync($inboxSyncType);
												$this->MailStorage->CreateFolder($draftsFolder);
											}
											$addFolders->Add($draftsFolder);
										}

										if ($sp === null)
										{
											$spamFolder = new Folder($account->Id, -1, $folderPrefix.WM_FOLDERNAME_Spam, WM_FOLDERNAME_Spam, WM_FOLDERSYNC_DontSync, WM_FOLDERTYPE_Spam);
											if ($createFoldersIfNotExist)
											{
												$spamFolder->SetFolderSync($inboxSyncType);
												$this->MailStorage->CreateFolder($spamFolder);
											}
											$addFolders->Add($spamFolder);
										}

										if ($account->_settings->Imap4DeleteLikePop3 && $t === null)
										{
											$trashFolder = new Folder($account->Id, -1, $folderPrefix.WM_FOLDERNAME_Trash, WM_FOLDERNAME_Trash, WM_FOLDERSYNC_DontSync, WM_FOLDERTYPE_Trash);
											if ($createFoldersIfNotExist)
											{
												$trashFolder->SetFolderSync($inboxSyncType);
												$this->MailStorage->CreateFolder($trashFolder);
											}
											$addFolders->Add($trashFolder);
										}
									}
									else
									{
										$this->DbStorage->DeleteUserData($account->IdUser, $account->Id);
									}

									$this->MailStorage->Disconnect();
									break;
							}
						}
						else
						{
							$result = false;
							$this->DbStorage->DeleteUserData($account->IdUser);
						}

						if ($result && $folders && $folders->Count() > 0)
						{
							$folders = $folders->SortRootTree();
							$this->DbStorage->CreateFolders($folders);
						}
					}
				}
			}
			else
			{
				$result = false;
			}

			if ($result && $account->IsInternal)
			{
				$this->DbStorage->SaveMailAliases($account);
				$this->DbStorage->SaveMailForwards($account);
			}
			
			if (!$result)
			{
				$this->SetError(ap_Utils::TakePhrase('WM_CANT_CREATE_ACCOUNT'));
			}
			
			return $result;
		}
		
		/**
		 * @param	Account	$account
		 * @param	int		$sync[optional] = null
		 * @return	bool
		 */
		function UpdateAccount($account, $sync = null)
		{
			$result = true;
			
			if ($this->DbStorage->Connect())
			{
				if ($account->DefaultAccount)
				{
					$defaultAccountArray = $this->DbStorage->SelectAccountDataByLogin($account->Email, $account->MailIncLogin, true, $account->Id);
					if (is_array($defaultAccountArray) && count($defaultAccountArray) > 0)
					{
						$this->SetError(ap_Utils::TakePhrase('WM_CANT_ADD_DEF_ACCT'));
						$result = false;
					}
				}
				
				if ($result && $account->MailProtocol == WM_MAILPROTOCOL_POP3 && $sync != null)
				{
					$folders =& $this->DbStorage->GetFolders($account);
					if ($folders)
					{
						$inboxFolder =& $folders->GetFolderByType(WM_FOLDERTYPE_Inbox);
						if ($inboxFolder)
						{
							$inboxFolder->SyncType = $sync;
							$result &= $this->DbStorage->UpdateFolder($inboxFolder);
						}
					}
				}

				if ($result && $account->MailProtocol == WM_MAILPROTOCOL_IMAP4 && $this->_account->_settings->TakeImapQuota)
				{
					if ($this->_account->ImapQuota === 1)
					{
						$mbl = $this->GetQuota();
						if (false !== $mbl && $mbl >= 0)
						{
							$account->MailboxLimit = GetGoodBigInt($mbl);
						}
					}
					else if ($this->_account->ImapQuota === -1 && $this->IsQuotaSupport())
					{
						$this->_account->ImapQuota = 0;
					}

					$result = $this->DbStorage->UpdateAccountImapQuota($account);
				}
				
				if ($result)
				{
					$result = $this->DbStorage->UpdateAccountData($account);
				}

				if ($result && $account->IsInternal)
				{
					$result &= $this->DbStorage->SaveMailAliases($account);
					$result &= $this->DbStorage->SaveMailForwards($account);
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

				$fs = new FileSystem(WM_INI_DIR.'/mail', $account->Email, $account->Id);
				$fs->DeleteAccountDirs();

				$fs2 = new FileSystem(WM_INI_DIR.'/temp', $account->Email, $account->Id);
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
		 * @return FolderCollection
		 */
		function &GetFolders()
		{
			$folders = null;
			if ($this->DbStorage->Connect())
			{
				$folders = &$this->DbStorage->GetFolders();
			}
			return $folders;
		}
			
		/**
		 * @param Folder $folder
		 */
		function GetFolderInfo(&$folder)
		{
			if ($this->DbStorage->Connect())
			{
				$this->DbStorage->GetFolderInfo($folder);
			}
		}
		
		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function CreateFolder(&$folder, $forceCreate = false)
		{
			$result = true;
			if ($this->_account->MailProtocol == WM_MAILPROTOCOL_IMAP4 && ($folder->SyncType != WM_FOLDERSYNC_DontSync || $forceCreate))
			{
				$result &= $this->MailStorage->Connect() && $this->MailStorage->CreateFolder($folder);
			}

			return $result && $this->DbStorage->Connect() && $this->DbStorage->CreateFolder($folder);
		}
		
		/**
		 * @param string $str
		 */
		function SetError($str)
		{
			if (strlen($this->_error) == 0)
			{
				$this->_error = $str;
			}
		}
		
		/**
		 * @return string
		 */
		function GetError()
		{
			return $this->_error;
		}

		/**
		 * @return bool
		 */
		function IsQuotaSupport()
		{
			if ($this->_account->MailProtocol == WM_MAILPROTOCOL_IMAP4 && $this->MailStorage->Connect())
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
			if ($this->_account->MailProtocol == WM_MAILPROTOCOL_IMAP4 && $this->MailStorage->Connect())
			{
				return $this->MailStorage->GetQuota();
			}
			return false;
		}
	}