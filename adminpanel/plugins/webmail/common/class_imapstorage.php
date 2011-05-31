<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

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
	function ImapStorage(&$account, $pathToClassFolder)
	{
		MailServerStorage::MailServerStorage($account);
		require_once($pathToClassFolder.'libs/class_imap.php');
		require_once($pathToClassFolder.'class_folders.php');
		
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
		return $this->_imapMail->GetNameSpacePrefix();
	}

	/**
	 * @return bool
	 */
	function Connect()
	{
		if($this->_imapMail->connection != false)
		{
			return true;
		}
		
		if (!$this->_imapMail->open())
		{
			$this->SetError(ap_Utils::TakePhrase('WM_ERROR_IMAP4_CONNECT'));
			return false;
		}
		else
		{
			register_shutdown_function(array(&$this, 'Disconnect'));	
		}
		
		if (!$this->_imapMail->login($this->_account->MailIncLogin, $this->_account->MailIncPassword))
		{
			$this->SetError(ap_Utils::TakePhrase('WM_ERROR_POP3IMAP4AUTH'));
			return false;				
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function Disconnect()
	{
		if(!$this->_imapMail->connection)
		{
			return true;
		}
		return $this->_imapMail->close();
	}

	/**
	 * @return FolderCollection
	 */
	function GetFolders(&$accountDelimiter)
	{
		$folderCollection = new FolderCollection();
		
		$folders =& $this->_imapMail->list_mailbox($accountDelimiter);
		$subsScrFolders = $this->_imapMail->list_subscribed_mailbox($accountDelimiter);
		
		$existsIndex = array('VirusAdd' => true); 
		$folderCollection = $this->GetFolderCollectionFromArrays($folders, $subsScrFolders, $accountDelimiter, $existsIndex);

		/* custom class */
		ap_Custom::StaticUseMethod('wm_ChangeServerImapFoldersAfterGet', array(&$folderCollection));
		
		return $folderCollection;
	}
	
	function SetFolderType(&$folderObj, &$existsIndex)
	{
		switch ($folderObj->Type)
		{
			case WM_FOLDERTYPE_Inbox:
				if (isset($existsIndex['InboxAdd'])) $folderObj->Type = WM_FOLDERTYPE_Custom;
				$existsIndex['InboxAdd'] = true;
				break;
			case WM_FOLDERTYPE_SentItems:
				if (isset($existsIndex['SentAdd'])) $folderObj->Type = WM_FOLDERTYPE_Custom;
				$existsIndex['SentAdd'] = true;
				break;
			case WM_FOLDERTYPE_Drafts:
				if (isset($existsIndex['DraftsAdd'])) $folderObj->Type = WM_FOLDERTYPE_Custom;
				$existsIndex['DraftsAdd'] = true;
				break;
			case WM_FOLDERTYPE_Spam:
				if (isset($existsIndex['SpamAdd'])) $folderObj->Type = WM_FOLDERTYPE_Custom;
				$existsIndex['SpamAdd'] = true;
				break;
			case WM_FOLDERTYPE_Trash:
				if ($this->_account->_settings && $this->_account->_settings->Imap4DeleteLikePop3)
				{
					if (isset($existsIndex['TrashAdd'])) $folderObj->Type = WM_FOLDERTYPE_Custom;
				}
				else 
				{
					$folderObj->Type = WM_FOLDERTYPE_Custom;
				}
				$existsIndex['TrashAdd'] = true;
				break;
			default:
				$folderObj->Type = WM_FOLDERTYPE_Custom;
				break;					
		}
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
			$this->_imapMail->unsubscribe_mailbox($folder->FullName);
		}
		else
		{
			$this->_imapMail->subscribe_mailbox($folder->FullName);
		}
	}

	/**
	 * @param Folder $folder
	 * @return bool
	 */
	function DeleteFolder(&$folder)
	{
		return $this->_imapMail->delete_mailbox($folder->FullName);
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

}