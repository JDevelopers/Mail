<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/'));
	
	require_once(WM_ROOTPATH.'common/class_settings.php');
	require_once(WM_ROOTPATH.'common/class_mailstorage.php');
	require_once(WM_ROOTPATH.'common/class_imapstorage.php');
	require_once(WM_ROOTPATH.'common/class_pop3storage.php');
	require_once(WM_ROOTPATH.'common/class_folders.php');
	require_once(WM_ROOTPATH.'common/class_i18nstring.php');
	require_once(WM_ROOTPATH.'common/class_datetime.php');
	require_once(WM_ROOTPATH.'common/class_validate.php');
	require_once(WM_ROOTPATH.'common/wmserver/class_wmserver.php');
	
	define('MAILPROTOCOL_POP3', 0);
	define('MAILPROTOCOL_IMAP4', 1);
	define('MAILPROTOCOL_WMSERVER', 2);

	define('MAILMODE_DeleteMessagesFromServer', 0);
	define('MAILMODE_LeaveMessagesOnServer', 1);
	define('MAILMODE_KeepMessagesOnServer', 2);
	define('MAILMODE_DeleteMessageWhenItsRemovedFromTrash', 3);
	define('MAILMODE_KeepMessagesOnServerAndDeleteMessageWhenItsRemovedFromTrash', 4);

	define('SIGNATURE_OPTION_DontAdd', 0);
	define('SIGNATURE_OPTION_AddToAll', 1);
	define('SIGNATURE_OPTION_AddToNewOnly', 2);
	
	define('VIEW_MODE_WITHOUT_PREVIEW_PANE_NO_IMG', 0);
	define('VIEW_MODE_PREVIEW_PANE_NO_IMG', 1);
	define('VIEW_MODE_WITHOUT_PREVIEW_PANE', 2);
	define('VIEW_MODE_PREVIEW_PANE', 3);
	
	define('ACCOUNT_DEF_TIME_FORMAT_24', 0);
	define('ACCOUNT_DEF_TIME_FORMAT_12', 1);

	define('SUGGESTCONTACTS', 20);
	define('DEMOACCOUNTEMAIL', 'xxx@xxx');
	define('DEMOACCOUNTPASS', 'xxxxxx');
	define('DEMOACCOUNTALLOW', 0);
	
	define('CREATE_FOLDERS_IF_NOT_EXIST', (USE_DB) ? false : true);
	
	class Account
	{
		/**
		 * @var int
		 */
		var $Id;

		/**
		 * @var int
		 */
		var $IdUser = 0;
		
		/**
		 * @var int
		 */
		var $IdDomain = 0;

		/**
		 * @var bool
		 */
		var $DefaultAccount = false;

		/**
		 * @var bool
		 */
		var $Deleted = false;
    
		/**
		 * @var string
		 */
		var $Email;
    
		/**
		 * @var short
		 */
		var $MailProtocol = MAILPROTOCOL_POP3;

		/**
		 * @var string
		 */
    	var $MailIncHost;

		/**
		 * @var string
		 */
		var $MailIncLogin;
		
		/**
		 * @var string
		 */
		var $MailIncProxyLogin = null;

		/**
		 * @var string
		 */
		var $MailIncPassword;
    
		/**
		 * @var short
		 */
		var $MailIncPort = 110;
    
		/**
		 * @var string
		 */
		var $MailOutHost;

		/**
		 * @var string
		 */
		var $MailOutLogin = '';

		/**
		 * @var string
		 */
		var $MailOutPassword = '';

		/**
		 * @var short
		 */
		var $MailOutPort = 25;
    
		/**
		 * @var bool
		 */
		var $MailOutAuthentication = true;

		/**
		 * @var string
		 */
		var $FriendlyName;

		/**
		 * @var bool
		 */
		var $UseFriendlyName = true;

		/**
		 * @var int
		 */
		var $DefaultOrder = 0;

		/**
		 * @var bool
		 */
		var $GetMailAtLogin = USE_DB;

		/**
		 * @var short
		 */
		var $MailMode = MAILMODE_LeaveMessagesOnServer;

		/**
		 * @var short
		 */
		var $MailsOnServerDays = 7;

		/**
		 * @var string
		 */
		var $Signature;

		/**
		 * @var short
		 */
		var $SignatureType = 1;

		/**
		 * @var short
		 */
		var $SignatureOptions = 0;

		/**
		 * @var bool
		 */
		var $HideContacts;

		/**
		 * @var string
		 */
		var $Delimiter = '/';

		/**
		 * @var string
		 */
		var $NameSpace = '';

		/**
		 * @var short
		 */
		var $MailsPerPage;

		/**
		 * @var bool
		 */
		var $WhiteListing = false;

		/**
		 * @var bool
		 */
		var $XSpam = false;

		/**
		 * @var CDateTime
		 */
		var $LastLogin;

		/**
		 * @var int
		 */
		var $LoginsCount = 0;

		/**
		 * @var string
		 */
		var $DefaultSkin = DEFAULT_SKIN;

		/**
		 * @var string
		 */
		var $DefaultLanguage;

		/**
		 * @var string
		 */
		var $DefaultIncCharset = CPAGE_ISO8859_1;

		/**
		 * @var string
		 */
		var $DefaultOutCharset = CPAGE_ISO8859_1;

		/**
		 * @var short
		 */
		var $DefaultTimeZone;

		/**
		 * @var string
		 */
		var $DefaultDateFormat = 'Default';
		
		/**
		 * @var int
		 */
		var $DefaultTimeFormat = ACCOUNT_DEF_TIME_FORMAT_12;

		/**
		 * @var bool
		 */
		var $HideFolders;

		/**
		 * @var long
		 */
		var $MailboxLimit;

		/**
		 * @var long
		 */
		var $MailboxSize = 0;

		/**
		 * @var bool
		 */
		var $AllowChangeSettings = true;

		/**
		 * @var bool
		 */
		var $AllowDhtmlEditor = true;

		/**
		 * @var bool
		 */
		var $AllowDirectMode;
		
		/**
		 * @var string
		 */
		var $DbCharset = CPAGE_UTF8;
		
		/**
		 * @var int
		 */
		var $HorizResizer = 150;
		
		/**
		 * @var int
		 */
		var $VertResizer = 115;
		
		/**
		 * @var int
		 */
		var $Mark;
		
		/**
		 * @var int
		 */
		var $Reply;
		
		/**
		 * @var int
		 */
		var $ContactsPerPage = 20;
		
		/**
		 * @var short
		 */
		var $ViewMode = VIEW_MODE_PREVIEW_PANE_NO_IMG;
		
		/**
		 * @var array
		 */
		var $Columns;
		
		/**
		 * @var bool
		 */
		var $IsMailList = false;

		/**
		 * @var int
		 */
		var $ImapQuota = 0;
		
		/**
		 * @var bool
		 */
		var $IsDemo = false;

		/**
		 * @var bool
		 */
		var $IsInternal = false;
		
		/**
		 * @var bool
		 */
		var $DomainAddressBook = false;

		/**
		 * @var bool
		 */
		var $EnableMobileSync = false;

		/**
		 * @var int
		 */
		var $SaveInSent;

		/**
		 * @var array
		 */
		var $CustomValues = array();

		/**
		 * @var string
		 */
		var $Question1;
		var $Answer1;
		var $Question2;
		var $Answer2;

		/**
		 * @var	int
		 */
		var $AutoCheckMailInterval;
		
		/**
		 * @return Account
		 */
		function Account()
		{
			$settings =& Settings::CreateInstance();
			
			$this->MailsPerPage = ((int) $settings->MailsPerPage > 0) ? (int) $settings->MailsPerPage : 20;
			$this->DefaultSkin = $settings->DefaultSkin;
			$this->DefaultLanguage = $settings->DefaultLanguage;
			$this->DefaultTimeZone = $settings->DefaultTimeZone;
			$this->MailboxLimit = $settings->MailboxSizeLimit;
			$this->AllowDirectMode = $settings->AllowDirectMode;
			$this->AllowChangeSettings = $settings->AllowUsersChangeEmailSettings;
			
			$this->MailIncHost = $settings->IncomingMailServer;
			$this->MailIncPort = $settings->IncomingMailPort;
			$this->MailOutHost = $settings->OutgoingMailServer;
			$this->MailOutPort = $settings->OutgoingMailPort;
			
			$this->MailProtocol = $settings->IncomingMailProtocol;
			
			$this->DefaultIncCharset = $settings->DefaultUserCharset;
			$this->DefaultOutCharset = $settings->DefaultUserCharset;
			
			$this->MailOutAuthentication = $settings->ReqSmtpAuth;

			$this->ImapQuota = (int) $settings->TakeImapQuota;
			$this->SaveInSent = (int) $settings->SaveInSent;
			
			$this->Columns = array();
			
			$this->IsDemo = false;
			$this->NameSpace = '';
			
			$this->AllowDhtmlEditor = $settings->AllowDhtmlEditor;
			$this->AutoCheckMailInterval = 0;
			$this->EnableMobileSync = false;
			$this->ViewMode = $settings->ViewMode;
			
			/* custom class */
			wm_Custom::StaticUseMethod('ChangeAccountAfterClassCreate', array(&$this));
		}

		/**
		 * @return bool
		 */
		function IsSpamAccount()
		{
			return false;
		}
		
		/**
		 * @return string
		 */
		function GetDefaultIncCharset()
		{
			if ($this->DefaultIncCharset == 'default')
			{
				$settings =& Settings::CreateInstance();
				if ($settings && $settings->DefaultUserCharset != 'default')
				{
					return $settings->DefaultUserCharset;
				}
				return CPAGE_ISO8859_1;
			}
			return $this->DefaultIncCharset;
		}
		
		/**
		 * @return string
		 */
		function GetDefaultOutCharset()
		{
			if ($this->DefaultOutCharset == 'default')
			{
				$settings =& Settings::CreateInstance();
				if ($settings && $settings->DefaultUserCharset != 'default')
				{
					return $settings->DefaultUserCharset;
				}
				return CPAGE_UTF8;
			}
			return $this->DefaultOutCharset;
		}

		/**
		 * @return string
		 */
		function GetUserCharset()
		{
			return CPAGE_UTF8;
		}
		
		/**
		 * @param	Settings	$_settings
		 * @return	int
		 */
		function GetDefaultFolderSync($_settings)
		{
			$s = FOLDERSYNC_AllHeadersOnly;
			if ($_settings && $_settings->AllowDirectMode && $_settings->DirectModeIsDefault)
			{							
				$s = FOLDERSYNC_DirectMode;
			}
			else 
			{
				switch ($this->MailProtocol)
				{
					case MAILPROTOCOL_POP3:
						$s = FOLDERSYNC_NewEntireMessages;
						break;
					case MAILPROTOCOL_IMAP4:
						// $s = FOLDERSYNC_AllHeadersOnly;
						$s = FOLDERSYNC_DirectMode;
						break;
				}
			}
			
			/* custom class */
			wm_Custom::StaticUseMethod('UpdateDefaultFolderSync', array(&$s, $this->MailProtocol));
			return $s;
		}
		
		/**
		 * @return short
		 */
		function GetDefaultTimeOffset($otherTimeZone = null)
		{
			if (isset($_SESSION[JS_TIMEOFFSET]))
			{
				return $_SESSION[JS_TIMEOFFSET];
			}
			
			$timeArray = localtime(time(), true);
			
			$daylightSaveMinutes = isset($timeArray['tm_isdst']) ? $timeArray['tm_isdst'] * 60 : 0;
			
			$timeOffset = 0;
			
			$varForSwitch = ($otherTimeZone !== null)  ? $otherTimeZone : $this->DefaultTimeZone;
			
			switch ($varForSwitch)
			{
				default:
				case 0:
					// return (ConvertUtils::GmtMkTime() - mktime())/60;										//return (ConvertUtils::GmtMkTime()-mktime())/60;
					return date('O') / 100 * 60;
					break;
				case 1:
					$timeOffset = -12 * 60;
					break;
				case 2:
					$timeOffset = -11 * 60;
					break;
				case 3:
					$timeOffset = -10 * 60;
					break;
				case 4:
					$timeOffset = -9 * 60;
					break;
				case 5:
					$timeOffset =  -8*60;
					break;
				case 6:
				case 7:
					$timeOffset = -7 * 60;
					break;
				case 8:
				case 9:
				case 10:
				case 11:
					$timeOffset = -6 * 60;
					break;
				case 12:
				case 13:
				case 14:
					$timeOffset = -5 * 60;
					break;
				case 15:
				case 16:
				case 17:
					$timeOffset = -4 * 60;
					break;
				case 18:
					$timeOffset = -3.5 * 60;
					break;
				case 19:
				case 20:
				case 21:
					$timeOffset = -3 * 60;
					break;
				case 22:
					$timeOffset = -2 * 60;
					break;
				case 23:
				case 24:
					$timeOffset = -60;
					break;
				case 25:
				case 26:
					$timeOffset = 0;
					break;
				case 27:
				case 28:
				case 29:
				case 30:
				case 31:
					$timeOffset = 60;
					break;
				case 32:
				case 33:
				case 34:
				case 35:
				case 36:
				case 37:
					$timeOffset = 2 * 60;
					break;
				case 38:
				case 39:
				case 40:
				case 41:
					$timeOffset = 3 * 60;
					break;
				case 42:
					$timeOffset = 3.5 * 60;
					break;
				case 43:
				case 44:
					$timeOffset = 4 * 60;
					break;
				case 45:
					$timeOffset = 4.5 * 60;
					break;
				case 46:
				case 47:
					$timeOffset = 5 * 60;
					break;
				case 48:
					$timeOffset = 5.5 * 60;
					break;
				case 49:
					$timeOffset = 5 * 60 + 45;
					break;
				case 50:
				case 51:
				case 52:
					$timeOffset = 6 * 60;
					break;
				case 53:
					$timeOffset = 6.5 * 60;
				case 54:
				case 55:
					$timeOffset = 7 * 60;
					break;
				case 56:
				case 57:
				case 58:
				case 59:
				case 60:
					$timeOffset = 8 * 60;
					break;
				case 61:
				case 62:
				case 63:
					$timeOffset = 9 * 60;
					break;
				case 64:
				case 65:
					$timeOffset = 9.5 * 60;
					break;
				case 66:
				case 67:
				case 68:
				case 69:
				case 70:
					$timeOffset = 10 * 60;
					break;
				case 71:
					$timeOffset = 11 * 60;
					break;
				case 72:
				case 73:
					$timeOffset = 12 * 60;
					break;
				case 74:
					$timeOffset = 13 * 60;
					break;
			}
			
			return $timeOffset + $daylightSaveMinutes;
		}
		
		
		function GetObjFromSession()
		{
			 return isset($_SESSION[ACCOUNT_OBJ]) ? unserialize($_SESSION[ACCOUNT_OBJ]) : null;
		}
		
		function SaveInSession($account)
		{
			$_SESSION[ACCOUNT_OBJ] = serialize($account);
		}

		/**
		 * @return bool
		 */
		function UpdateDelimiter()
		{
			if (!USE_DB)
			{
				Account::SaveInSession($this);
				return true;
			}
			
			$dbStorage =& DbStorageCreator::CreateDatabaseStorage($this);
			if ($dbStorage->Connect())
			{
				return $dbStorage->UpdateDelimiter($this->Id, $this->Delimiter);
			}

			return false;
		}

		/**
		 * @return bool
		 */
		function UpdateNameSpace()
		{
			if (!USE_DB)
			{
				Account::SaveInSession($this);
				return true;
			}

			$dbStorage =& DbStorageCreator::CreateDatabaseStorage($this);
			if ($dbStorage->Connect())
			{
				return $dbStorage->UpdateNameSpace($this->Id, $this->NameSpace);
			}

			return false;
		}
		
		/**
		 * @return bool
		 */
		function UpdateDefaultOrder()
		{
			if (!USE_DB)
			{
				Account::SaveInSession($this);
				return true;
			}
			
			$dbStorage =& DbStorageCreator::CreateDatabaseStorage($this);
			if ($dbStorage->Connect())
			{
				return $dbStorage->UpdateDefaultOrder($this->Id, $this->DefaultOrder);
			}
			
			return false;
		}
		
		/**
		 * @return bool
		 */
		function UpdateDefaultLanguage()
		{
			if ($this->IsDemo)
			{
				$_SESSION[DEMO_SES][DEMO_S_DefaultLanguage] = $this->DefaultLanguage;
				return true;
			}
			if (!USE_DB)
			{
				Account::SaveInSession($this);
				return true;
			}
			
			$dbStorage =& DbStorageCreator::CreateDatabaseStorage($this);
			if ($dbStorage->Connect())
			{
				return $dbStorage->UpdateDefaultLanguage($this->IdUser, $this->DefaultLanguage);
			}
			
			return false;
		}
		
		/**
		 * @return bool
		 */
		function UpdateDefaultIncCharset()
		{
			if (!USE_DB)
			{
				Account::SaveInSession($this);
				return true;
			}
			
			$dbStorage =& DbStorageCreator::CreateDatabaseStorage($this);
			if ($dbStorage->Connect())
			{
				return $dbStorage->UpdateDefaultIncCharset($this->IdUser, $this->DefaultIncCharset);
			}
			
			return false;
		}

		/**
		 * @return bool
		 */
		function UpdatePasswords()
		{
			if (!USE_DB)
			{
				Account::SaveInSession($this);
				return true;
			}
			
			$dbStorage =& DbStorageCreator::CreateDatabaseStorage($this);
			if ($dbStorage->Connect())
			{
				return $dbStorage->UpdateAccountPasswords($this->Id, 
						ConvertUtils::EncodePassword($this->MailIncPassword, $this),
						ConvertUtils::EncodePassword($this->MailOutPassword, $this));
			}

			return false;
		}

		/**
		 * @return bool
		 */
		function UpdateMailBoxLimit()
		{
			if (!USE_DB)
			{
				Account::SaveInSession($this);
				return true;
			}
			
			$dbStorage =& DbStorageCreator::CreateDatabaseStorage($this);
			if ($dbStorage->Connect())
			{
				return $dbStorage->UpdateMailBoxLimit($this->IdUser, $this->MailboxLimit);
			}

			return false;
		}

		/**
		 * @param short $pop3InboxSyncType optional
		 * @return bool
		 */
		function Update($pop3InboxSyncType = null, $updateXmail = false)
		{
			if ($this->IsDemo)
			{
				return true;
			}
			
			/* custom class */
			wm_Custom::StaticUseMethod('ChangeAccountBeforeUpadateDb', array(&$this));

			if (!USE_DB)
			{
				Account::SaveInSession($this);
				if (USE_LDAP_SETTINGS_STORAGE)
				{
					wm_Custom::StaticUseMethod('UpdateLdapSettingsOnAccountUpdate', array(&$this));
				}

				return true;
			}
			
			$result = true;
			
			$dbStorage =& DbStorageCreator::CreateDatabaseStorage($this);
			
			if ($dbStorage->Connect())
			{
				if (($this->MailProtocol == MAILPROTOCOL_POP3 && $pop3InboxSyncType != null) || ($this->MailProtocol == MAILPROTOCOL_WMSERVER && $pop3InboxSyncType != null))
				{
					$folders =& $dbStorage->GetFolders();
					$inboxFolder =& $folders->GetFolderByType(FOLDERTYPE_Inbox);
					$inboxFolder->SyncType = $pop3InboxSyncType;
					$result = $dbStorage->UpdateFolder($inboxFolder);
				}
				
				if ($this->DefaultAccount && 
					$dbStorage->SelectAccountsCountByLogin($this->Email, $this->MailIncLogin, true, $this->Id) > 0)
				{
					setGlobalError(ACCT_CANT_UPD_TO_DEF_ACCT);
					return false;
				}
				else if (!$this->DefaultAccount)
				{
					$accounts = $dbStorage->SelectAccounts($this->IdUser);
					if (is_array($accounts) && count($accounts) > 0)
					{
						$defArray = array();
						foreach ($accounts As $id_acct => $mainAcct)
						{
							if ($mainAcct[6])
							{
								$defArray[] = $id_acct;
							}
						}
						
						if (count($defArray) < 2)
						{
							if (in_array($this->Id, $defArray))	
							{
								$this->DefaultAccount = true;
							}
						}
					}					
				}

				if ($this->IsInternal)
				{
					if (@file_exists('/usr/mailsuite/scripts/maildirquota.sh'))
					{
						$aExplodeArray = explode('@', $this->MailIncLogin, 2);
						@system('/usr/mailsuite/scripts/maildirquota.sh '.trim($aExplodeArray[1]).' '.trim($aExplodeArray[0]).' '.$this->MailboxLimit);
					}
				}
				
				if ($dbStorage->UpdateAccountData($this))
				{
					return $result;
				}
			}
			return false;
		}

		/**
		 * @static 		 
		 * @param int $id
		 * @return Account
		 */
		function &LoadFromDb($id, $getSignature = true, $getColumns = true, $dbStorage = null)
		{
			$account = null;
			if (null === $dbStorage)
			{
				$null = null;
				$dbStorage =& DbStorageCreator::CreateDatabaseStorage($null);
			}
			
			if ($dbStorage->Connect())
			{
				if (USE_DB)
				{
					$account =& $dbStorage->SelectAccountData($id, $getSignature, $getColumns);
					
					if ($account && strtolower($account->Email) == DEMOACCOUNTEMAIL)
					{
						$account->IsDemo = DEMOACCOUNTALLOW;
						if ($account->IsDemo && isset($_SESSION[DEMO_SES]))
						{
							$acctDArray = $_SESSION[DEMO_SES];
							
							$account->MailsPerPage = isset($acctDArray[DEMO_S_MessagesPerPage]) ? $acctDArray[DEMO_S_MessagesPerPage] : $account->MailsPerPage;
							$account->AllowDhtmlEditor = isset($acctDArray[DEMO_S_AllowDhtmlEditor]) ? $acctDArray[DEMO_S_AllowDhtmlEditor] : $account->AllowDhtmlEditor;
							$account->DefaultSkin = isset($acctDArray[DEMO_S_DefaultSkin]) ? $acctDArray[DEMO_S_DefaultSkin] : $account->DefaultSkin;
							$account->DefaultOutCharset = isset($acctDArray[DEMO_S_DefaultOutCharset]) ? $acctDArray[DEMO_S_DefaultOutCharset] : $account->DefaultOutCharset;
							$account->DefaultTimeZone = isset($acctDArray[DEMO_S_DefaultTimeZone]) ? $acctDArray[DEMO_S_DefaultTimeZone] : $account->DefaultTimeZone;
							$account->DefaultLanguage = isset($acctDArray[DEMO_S_DefaultLanguage]) ? $acctDArray[DEMO_S_DefaultLanguage] : $account->DefaultLanguage;
							$account->DefaultDateFormat = isset($acctDArray[DEMO_S_DefaultDateFormat]) ? $acctDArray[DEMO_S_DefaultDateFormat] : $account->DefaultDateFormat;						
							$account->DefaultTimeFormat = isset($acctDArray[DEMO_S_DefaultTimeFormat]) ? $acctDArray[DEMO_S_DefaultTimeFormat] : $account->DefaultTimeFormat;						
							$account->ViewMode = isset($acctDArray[DEMO_S_ViewMode]) ? $acctDArray[DEMO_S_ViewMode] : $account->ViewMode;						
							
							$account->ContactsPerPage = isset($acctDArray[DEMO_S_ContactsPerPage]) ? $acctDArray[DEMO_S_ContactsPerPage] : $account->ContactsPerPage;
							$account->AutoCheckMailInterval = isset($acctDArray[DEMO_S_AutoCheckMailInterval]) ? $acctDArray[DEMO_S_AutoCheckMailInterval] : $account->AutoCheckMailInterval;
							
							$account->DefaultIncCharset = $account->DefaultOutCharset;
						}
					}
					
					Account::UpdateByDomain($account, $dbStorage);
				}
				else if (isset($_SESSION[ACCOUNT_OBJ]))
				{
					$account = Account::GetObjFromSession(); 
				}
			}

			/* custom class */
			wm_Custom::StaticUseMethod('ChangeAccountLoadFromDb', array(&$account));
			
			return $account;
		}

		/**
		 * @static 
		 * @param string $email
		 * @param string $login
		 * @return Array
		 */
		function &LoadFromDbByLogin($email, $login)
		{
			$null = null;
			$false = false;
			if (USE_DB)
			{
				$dbStorage = &DbStorageCreator::CreateDatabaseStorage($null);
				if ($dbStorage->Connect())
				{
					$array = &$dbStorage->SelectAccountDataByLogin($email, $login, true);
					return $array;
				}
			}
			
			return $false;
		}
		
		/**
		 * @param Account $_account
		 */
		function UpdateByDomain(&$_account, $_dbStorage = null)
		{
			if (USE_DB && $_account && $_account->IdDomain > 0)
			{
				if (null === $_dbStorage)
				{
					$_null = null;
					$_dbStorage =& DbStorageCreator::CreateDatabaseStorage($_null);
				}
				$_domain =& $_dbStorage->SelectDomainById($_account->IdDomain);
				if ($_domain)
				{
					$_settings =& Settings::CreateInstance();
					$_domain->UpdateAccount($_account, $_settings);
				}
			}
		}
		
		/**
		 * @static 
		 * @param string $email
		 * @return Array
		 */
		function &LoadFromDbOnlyByEmail($email)
		{
			$null = null;
			$false = false;
			if (USE_DB)
			{
				$dbStorage = &DbStorageCreator::CreateDatabaseStorage($null);
				if ($dbStorage->Connect())
				{
					$array = &$dbStorage->SelectAccountDataOnlyByEmail($email);
					return $array;
				}
			}

			return $false;		
		}
		
		/**
		 * @static 
		 * @param int $id
		 * @return bool
		 */
		function DeleteFromDb($id, $deleteDemo = false)
		{
			$account =& Account::LoadFromDb($id);
			if ((!$deleteDemo && $account->IsDemo) || !USE_DB)
			{
				return true;
			}

			$null = null;
			$dbStorage = &DbStorageCreator::CreateDatabaseStorage($null);
			if ($dbStorage->Connect())
			{
				$settings =& Settings::CreateInstance();
				if ($settings->EnableWmServer && $settings->WmAllowManageXMailAccounts)
				{
					if ($account && $account->MailProtocol == MAILPROTOCOL_WMSERVER)
					{
						$WMConsole = new CWmServerConsole();
						
						if (!$WMConsole->Connect())
						{
							setGlobalError(PROC_CANT_DEL_ACCT_BY_ID);
							return false;							
						}
						$domain = ConvertUtils::ParseEmail($account->Email);
						if ($domain)
						{
							$WMConsole->DeleteUser($domain[1], EmailAddress::GetAccountNameFromEmail($account->MailIncLogin));
						}
					}
				}

				if ($dbStorage->DeleteAccountData($id))
				{
					return true;
				}
				else 
				{
					setGlobalError(PROC_CANT_DEL_ACCT_BY_ID);
				}
			}
			return false;
		}
		
		/**
		 * @return string
		 */
		function GetFriendlyEmail()
		{
			if ($this->UseFriendlyName && strlen($this->FriendlyName) > 0)
			{
				return '"'.$this->FriendlyName.'" <'.$this->Email.'>';
			}
			return $this->Email;
		}
		
		/**
		 * @return string/boot
		 */
		function ValidateData()
		{
			if (!ConvertUtils::CheckFileName($this->Email))
			{
				return JS_LANG_WarningCorrectEmail;
			}
			elseif(empty($this->Email))
			{
				return JS_LANG_WarningEmailFieldBlank;
			}
			elseif(!Validate::checkEmail($this->Email))
			{
				return JS_LANG_WarningCorrectEmail;
			}
			elseif(empty($this->MailIncLogin))
			{
				return WarningLoginFieldBlank;
			}
			elseif(empty($this->MailIncPassword))
			{
				return WarningPassBlank;
			}
			elseif(empty($this->MailIncHost))
			{
				return JS_LANG_WarningIncServerBlank;
			}
			elseif(!Validate::checkServerName($this->MailIncHost))
			{
				return WarningCorrectIncServer;
			}
			elseif(empty($this->MailIncPort))
			{
				return JS_LANG_WarningIncPortBlank;
			}
			elseif(!Validate::checkPort($this->MailIncPort))
			{
				return JS_LANG_WarningIncPortNumber.' '.JS_LANG_DefaultIncPortNumber;
			}
			elseif(empty($this->MailOutHost))
			{
				return WarningCorrectSMTPServer;
			}
			elseif(!Validate::checkServerName($this->MailOutHost))
			{
				return WarningCorrectSMTPServer;
			}
			elseif(empty($this->MailOutPort))
			{
				return JS_LANG_WarningOutPortBlank;
			}
			elseif(!Validate::checkPort($this->MailOutPort))
			{
				return JS_LANG_WarningOutPortNumber.' '.JS_LANG_DefaultOutPortNumber;
			}				
			return true;	
		}
	}
	
	class User
	{
		/**
		 * @var int
		 */
		var $Id;	
		
		/**
		 * @var bool
		 */
		var $Deleted = false;
		
		/**
		 * @static
		 * @param Account $account
		 * @return User
		 */
		function &CreateUser($account = null)
		{
            $user = null;
            $dbStorage =& DbStorageCreator::CreateDatabaseStorage($user);
            if ($dbStorage->Connect())
            {
                $user = new User();
                if (USE_DB)
                {
	                if ($dbStorage->InsertUserData($user))
	                {
	                    if (!$account)
	                    {
	                        $account = new Account();
	                    }
	
	                    $account->IdUser = $user->Id;
	
	                    if (!$dbStorage->InsertSettings($account))
	                    {
	                        $dbStorage->EraseUserData($user->Id);
	                        $user = null;
	                    }
	                }
                }
                else
                {
                	$user->Id = 1;
                }
            }
            return $user;
		}
		
		/**
		 * @param Account $account
		 * @param int $inboxSyncType = FOLDERSYNC_NewEntireMessages
		 * @param bool $integrCall = false
		 * @return bool
		 */
		function CreateAccount(&$account, $inboxSyncType = FOLDERSYNC_NewEntireMessages, $integrCall = false, $mailStorage = null)
		{
			$null = $folders = null;
			$account->IdUser = $this->Id;
			$result = false;
			setGlobalError(PROC_ERROR_ACCT_CREATE);
			
			$dbStorage =& DbStorageCreator::CreateDatabaseStorage($account);
			if ($dbStorage->Connect())
			{
				if (USE_DB && !$account->IsInternal)
				{
					$defaultAccount =& $dbStorage->SelectAccountDataByLogin($account->Email, $account->MailIncLogin, true);
					
					if ($account->DefaultAccount && $defaultAccount != null && $defaultAccount[2] == 1)
					{
						setGlobalError(ACCT_CANT_ADD_DEF_ACCT);
						return false;
					}
				}
				
				$settings =& Settings::CreateInstance();
				
				if ($settings->AllowDirectMode && $settings->DirectModeIsDefault) 
				{
					$inboxSyncType = FOLDERSYNC_DirectMode;
				}

				$addFolders = null;
				
				/* load or create folder tree here */
				switch ($account->MailProtocol)
				{
					case MAILPROTOCOL_POP3:
						
						$result = ($account->IsInternal)
							? $dbStorage->UpdateOnlyAccoutnData($account)
							: $dbStorage->InsertAccountData($account);
					
						if ($result)
						{
							$folders = new FolderCollection();
							$inboxFolder = new Folder($account->Id, -1, FOLDERNAME_Inbox, FOLDERNAME_Inbox);
							
							$inboxFolder->SyncType = $inboxSyncType;
							$folders->Add($inboxFolder);

							$createSystemFoldersInInbox = false;

							/* custom class */
							wm_Custom::StaticUseMethod('ChangeValueOfSystemFoldersInInbox', array(&$createSystemFoldersInInbox));
							
							$folderPrefix =	'';
							if ($createSystemFoldersInInbox)
							{
								$folderPrefix = $inboxFolder->FullName.$account->Delimiter;
								$inboxFolder->SubFolders = ($inboxFolder->SubFolders) ? $inboxFolder->SubFolders : new FolderCollection();
								$addFolders =& $inboxFolder->SubFolders;
							}
							else
							{
								$addFolders =& $folders;
							}
							
							$addFolders->Add(new Folder($account->Id, -1, $folderPrefix.FOLDERNAME_SentItems, FOLDERNAME_SentItems, FOLDERSYNC_DontSync));
							$addFolders->Add(new Folder($account->Id, -1, $folderPrefix.FOLDERNAME_Drafts, FOLDERNAME_Drafts, FOLDERSYNC_DontSync));
							$addFolders->Add(new Folder($account->Id, -1, $folderPrefix.FOLDERNAME_Spam, FOLDERNAME_Spam, FOLDERSYNC_DontSync));
							$addFolders->Add(new Folder($account->Id, -1, $folderPrefix.FOLDERNAME_Trash, FOLDERNAME_Trash, FOLDERSYNC_DontSync));
						}
						break;
						
					case MAILPROTOCOL_IMAP4:

						setGlobalError(ACCT_CANT_CREATE_IMAP_ACCT);
						if (null === $mailStorage)
						{
							$mailStorage = new ImapStorage($account, $null);
						}

						if ($mailStorage->Connect())
						{
							if ($settings->TakeImapQuota)
							{
								if ($mailStorage->IsQuotaSupport())
								{
									if ($account->ImapQuota === 1)
									{
										$mbl = $mailStorage->GetQuota();
										if (false !== $mbl)
										{
											$account->MailboxLimit = GetGoodBigInt($mbl);
										}
										else
										{
											$account->ImapQuota = -1;
										}
									}
								}
								else
								{
									$account->ImapQuota = -1;
								}
							}

							if (USE_DB)
							{ 
								$result = ($account->IsInternal)
									? $dbStorage->UpdateOnlyAccoutnData($account)
									: $dbStorage->InsertAccountData($account);

								if (!$result)
								{
									return false;
								}
							}
							else
							{
								$result = true;
							}

							$folders =& $mailStorage->GetFolders();
							if ($folders == null)
							{
								if (USE_DB)
								{
									$dbStorage->DeleteAccountData($account->Id);
								}
								setGlobalError(PROC_ERROR_ACCT_CREATE);
								return false;
							}
							
							$inb =& $folders->GetFolderByType(FOLDERTYPE_Inbox);
							if ($inb === null)
							{
								if (USE_DB)
								{
									$dbStorage->DeleteAccountData($account->Id);
								}
								setGlobalError(PROC_ERROR_ACCT_CREATE);
								return false;
							}
							
							$folders->SetSyncTypeToAll($inboxSyncType);
						
							if (!$result)
							{
								return false;
							}
							
							$s = $d = $t = $sp = null;
							
							$s =& $folders->GetFolderByType(FOLDERTYPE_SentItems);
							$d =& $folders->GetFolderByType(FOLDERTYPE_Drafts);
							$sp =& $folders->GetFolderByType(FOLDERTYPE_Spam);
							if ($settings->Imap4DeleteLikePop3)
							{
								$t =& $folders->GetFolderByType(FOLDERTYPE_Trash);
							}

							$account->NameSpace = $mailStorage->GetNameSpacePrefix();
							$account->UpdateNameSpace();

							$createSystemFoldersInInbox = (0 === strpos($account->NameSpace, $inb->FullName));
							$createFoldersIfNotExist = ($account->IsInternal)
								? true : CREATE_FOLDERS_IF_NOT_EXIST;
							
							/* custom class */
							wm_Custom::StaticUseMethod('ChangeValueOfSystemFoldersInInbox', array(&$createSystemFoldersInInbox));
							wm_Custom::StaticUseMethod('ChangeValueOfCreateFolderIfNotExist', array(&$createFoldersIfNotExist));

							$folderPrefix =	'';
							if ($createSystemFoldersInInbox)
							{
								$folderPrefix = $inb->FullName.$account->Delimiter;
								$inb->SubFolders = ($inb->SubFolders)
									? $inb->SubFolders : new FolderCollection();
								
								$addFolders =& $inb->SubFolders;
							}
							else
							{
								$addFolders =& $folders;
							}
							
							if ($s === null)
							{
								$sentFolder = new Folder($account->Id, -1, $folderPrefix.FOLDERNAME_SentItems, FOLDERNAME_SentItems, FOLDERSYNC_DontSync);
								if ($createFoldersIfNotExist)
								{
									$sentFolder->SetFolderSync($inboxSyncType);
									$mailStorage->CreateFolder($sentFolder);
								}
								$addFolders->Add($sentFolder);
							}
							
							if ($d === null)
							{
								$draftsFolder = new Folder($account->Id, -1, $folderPrefix.FOLDERNAME_Drafts, FOLDERNAME_Drafts, FOLDERSYNC_DontSync);
								if ($createFoldersIfNotExist)
								{
									$draftsFolder->SetFolderSync($inboxSyncType);
									$mailStorage->CreateFolder($draftsFolder);
								}
								$addFolders->Add($draftsFolder);
							}

							if ($sp === null)
							{
								$spamFolder = new Folder($account->Id, -1, $folderPrefix.FOLDERNAME_Spam, FOLDERNAME_Spam, FOLDERSYNC_DontSync);
								if ($createFoldersIfNotExist)
								{
									$spamFolder->SetFolderSync($inboxSyncType);
									$mailStorage->CreateFolder($spamFolder);
								}
								$addFolders->Add($spamFolder);
							}
							
							if ($settings->Imap4DeleteLikePop3 && $t === null)
							{
								$trashFolder = new Folder($account->Id, -1, $folderPrefix.FOLDERNAME_Trash, FOLDERNAME_Trash, FOLDERSYNC_DontSync);
								if ($createFoldersIfNotExist)
								{
									$trashFolder->SetFolderSync($inboxSyncType);
									$mailStorage->CreateFolder($trashFolder);
								}
								$addFolders->Add($trashFolder);
							}

							$mailStorage->Disconnect();
						}
						else
						{
							return false;
						}
						break;
						
					default:
						return false;
				}
				
				if ($result && $folders)
				{
					$folders = $folders->SortRootTree();
					if (USE_DB)
					{
						$result &= $dbStorage->CreateFolders($folders);
					}
				}
			}
			
			if ($result)
			{
				setGlobalError('');
				if ($account && $account->MailProtocol == MAILPROTOCOL_IMAP4 && $account->ImapQuota === 1)
				{
					$account->UpdateMailBoxLimit();
				}
			}
					
			return $result;
		}
		
		/**
		 * @static
		 * @param int $id
		 * @return bool
		 */
		function DeleteUser($id)
		{
			$null = null;
			$dbStorage = &DbStorageCreator::CreateDatabaseStorage($null);
			if ($dbStorage->Connect())
			{
				if ($dbStorage->DeleteUserData($id))
				{
					return true;
				}
			}
			return false;
		}
		
		/**
		 * @static
		 * @param int $id
		 * @return bool
		 */
		function DeleteUserSettings($id)
		{
			$null = null;
			$dbStorage = &DbStorageCreator::CreateDatabaseStorage($null);
			if ($dbStorage->Connect())
			{
				if (USE_DB && $dbStorage->DeleteSettingsData($id))
				{
					return true;
				}
			}
			return false;
		}
		
		/**
		 * @param int $id
		 * @return bool
		 */
		function AccountAccess($id)
		{
			if ($id == $_SESSION[ACCOUNT_ID])
			{
				return true;
			}
			
			$result = false;
			if (isset($_SESSION[ACCOUNT_IDS]) && is_array($_SESSION[ACCOUNT_IDS]))
			{
				$result = in_array($id, $_SESSION[ACCOUNT_IDS]);		
			}
			else 
			{
				if (isset($_SESSION[ACCOUNT_ID]))
				{
					$_account = null;
					if (!isset($_SESSION[USER_ID]))
					{
						$_account =& Account::LoadFromDb($_SESSION[ACCOUNT_ID], false, false);
						if ($_account)
						{
							$_SESSION[USER_ID] = $_account->IdUser;
						}
					}
					
					if (isset($_SESSION[USER_ID]))
					{
						$_dbStorage =& DbStorageCreator::CreateDatabaseStorage($_account);
						if ($_dbStorage->Connect())
						{
							$_SESSION[ACCOUNT_IDS] = $_dbStorage->GetAccountListByUserId($_SESSION[USER_ID]);
							$result = in_array($id, $_SESSION[ACCOUNT_IDS]);	
						}
					}
				}
			}
			
			if (!$result)
			{
				$_log =& CLog::CreateInstance();
				$_log->WriteLine('Access Error: Unauthorized access '.$id.' && '.$_SESSION[ACCOUNT_ID], LOG_LEVEL_WARNING);
			}
			
			return $result;
		}

		/**
		 * @param int $_deleteId
		 * @param int $_edit_id
		 * @param bool $_clearClassic = false
		 * @return bool|7 (7 - logout)
		 */
		function ProcessDeleteAccount($_deleteId, &$_edit_id, $_clearClassic = false)
		{
			if (!isset($_SESSION[ACCOUNT_ID], $_SESSION[USER_ID]) || !User::AccountAccess($_deleteId))
			{
				setGlobalError(PROC_WRONG_ACCT_ACCESS);
				return false;
			}
			
			$null = null;
			$_accounts = array();
			$_dbStorage =& DbStorageCreator::CreateDatabaseStorage($null);
			if ($_dbStorage->Connect())
			{
				$_accounts =& $_dbStorage->SelectAccounts($_SESSION[USER_ID]);
				if (!$_accounts)
				{
					setGlobalError(PROC_CANT_GET_ACCT_LIST);
					return false;
				}
			}
			else 
			{
				return false;
			}
			
			$_is_def = false;
			$_is_lastdef = false;
			$_is_edit = false;
			
			$_c = count($_accounts);
			if ($_c > 1)
			{
				foreach ($_accounts AS $_id => $_currAccount)
				{
					if ($_id == $_deleteId && isset($_currAccount[6]) && $_currAccount[6])
					{
						$_is_def = true;
					}
				}
				if ($_is_def)
				{
					$_is_lastdef = true;
					foreach ($_accounts AS $_id => $_currAccount)
					{
						if ($_id != $_deleteId && isset($_currAccount[6]) && $_currAccount[6])
						{
							$_is_lastdef = false;
						}
					}
				}
				
				if ($_edit_id == $_deleteId)
				{
					$_is_edit = true;
				}
			}
			else if ($_c == 1) 
			{
				if (isset($_accounts[$_deleteId]))
				{
					$_is_edit = true;
				}
			}
			else 
			{
				setGlobalError(PROC_CANT_DEL_ACCT_BY_ID);
				return false;
			}
			
			if ($_c > 1)
			{
				if ($_is_lastdef) 
				{
					setGlobalError(ACCT_CANT_DEL_LAST_DEF_ACCT);
					return false;
				}
			}
			else 
			{
				if (!self::ProcessDeleteAccountEnd($_deleteId, $_dbStorage))
				{
					setGlobalError(PROC_CANT_DEL_ACCT_BY_ID);
					return false;
				}
				return 7;
			}
				
			if ($_SESSION[ACCOUNT_ID] == $_deleteId)
			{
				foreach ($_accounts AS $_id => $_currAccount)
				{
					if ($_id != $_deleteId && isset($_currAccount[6]) && $_currAccount[6])
					{
						$_SESSION[ACCOUNT_ID] = $_id;
						if ($_clearClassic)
						{
							unset($_SESSION[SARRAY][FOLDER_ID], $_SESSION[SARRAY][PAGE]);
						}
						break;
					}
				}
					
				if ($_SESSION[ACCOUNT_ID] == $_deleteId)
				{
					foreach ($_accounts AS $_id => $_currAccount)
					{
						if ($_id != $_deleteId)
						{
							$_SESSION[ACCOUNT_ID] = $_id;
							if ($_clearClassic)
							{	
								unset($_SESSION[SARRAY][FOLDER_ID], $_SESSION[SARRAY][PAGE]);
							}
							break;
						}
					}			
				}
				
				if (!self::ProcessDeleteAccountEnd($_deleteId, $_dbStorage))
				{
					setGlobalError(PROC_CANT_DEL_ACCT_BY_ID);
					return false;
				}
			}
			else 
			{
				if ($_is_edit)
				{
					foreach ($_accounts AS $_id => $_currAccount)
					{
						if ($_id != $_deleteId && $_currAccount[6])
						{
							$_edit_id = $_id;
							break;
						}
					}
					
					if ($_SESSION[SARRAY][EDIT_ACCOUNT_ID] == $_deleteId)
					{
						foreach ($_accounts AS $_id => $_currAccount)
						{
							if ($_id != $_deleteId)
							{
								$_edit_id = $_id;
								break;
							}
						}			
					}
				}
				
				if (!self::ProcessDeleteAccountEnd($_deleteId, $_dbStorage))
				{
					setGlobalError(PROC_CANT_DEL_ACCT_BY_ID);
					return false;
				}
				
			}
			
			return true;
		}

		function ProcessDeleteAccountEnd($acctId, &$dbStorage)
		{
			$account = null;
			if ($dbStorage->Connect())
			{
				$account =& $dbStorage->SelectAccountData($acctId);
			}

			if ($account)
			{
				$processor = new MailProcessor($account);
				return $processor->DeleteAccount();
			}
			return false;
		}
	}