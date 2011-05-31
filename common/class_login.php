<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 *
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));

	require_once(WM_ROOTPATH.'common/class_mailprocessor.php');
	require_once(WM_ROOTPATH.'common/class_tempfiles.php');

class CWebMailLoginInfo
{
	var $_email;
	var $_login;
	var $_password;
	var $_language;
	var $_advancedLogin;
	var $_advMailProtocol;
	var $_advMailIncHost;
	var $_advMailIncPort;
	var $_advMailOutHost;
	var $_advMailOutPort;
	var $_advMailOutAuth;
	var $_domainsSelectValue;

	function CWebMailLoginInfo($_email, $_login, $_password, $_language,
		$_advancedLogin, $_advMailProtocol, $_advMailIncHost, $_advMailIncPort,
		$_advMailOutHost, $_advMailOutPort, $_advMailOutAuth, $_domainsSelectValue)
	{
		$this->_email = $_email;
		$this->_login = $_login;
		$this->_password = $_password;
		$this->_language = $_language;
		$this->_advancedLogin = $_advancedLogin;
		$this->_advMailProtocol = $_advMailProtocol;
		$this->_advMailIncHost = $_advMailIncHost;
		$this->_advMailIncPort = $_advMailIncPort;
		$this->_advMailOutHost = $_advMailOutHost;
		$this->_advMailOutPort = $_advMailOutPort;
		$this->_advMailOutAuth = $_advMailOutAuth;
		$this->_domainsSelectValue = $_domainsSelectValue;
	}

	function getEmail()
	{
		return $this->_email;
	}

	function setEmail($email)
	{
		$this->_email = $email;
	}

	function getLogin()
	{
		return $this->_login;
	}

	function getPassword()
	{
		return $this->_password;
	}

	function getLanguage()
	{
		return $this->_language;
	}

	function getAdvancedLogin()
	{
		return (bool) $this->_advancedLogin;
	}

	function getMailProtocol()
	{
		return (int) $this->_advMailProtocol;
	}

	function getMailIncHost()
	{
		return $this->_advMailIncHost;
	}

	function getMailIncPort()
	{
		return (int) $this->_advMailIncPort;
	}

	function getMailOutHost()
	{
		return $this->_advMailOutHost;
	}

	function getMailOutPort()
	{
		return (int) $this->_advMailOutPort;
	}

	function getMailOutAuth()
	{
		return (bool) $this->_advMailOutAuth;
	}

	function getDomainsSelectValue()
	{
		return ($this->_domainsSelectValue) ? $this->_domainsSelectValue : null;
	}
	
	function SaveToSession()
	{
		$_SESSION['WebMailLoginInfo'] = array(
			'_email' => $this->_email,
			'_login' => $this->_login,
			'_password' => $this->_password,
			'_language' => $this->_language,
			'_advancedLogin' => $this->_advancedLogin,
			'_advMailProtocol' => $this->_advMailProtocol,
			'_advMailIncHost' => $this->_advMailIncHost,
			'_advMailIncPort' => $this->_advMailIncPort,
			'_advMailOutHost' => $this->_advMailOutHost,
			'_advMailOutPort' => $this->_advMailOutPort,
			'_advMailOutAuth' => $this->_advMailOutAuth,
			'_domainsSelectValue' => $this->_domainsSelectValue
		);
	}
	
	function LoadFromSession()
	{
		if (isset($_SESSION['WebMailLoginInfo']) && count($_SESSION['WebMailLoginInfo']) > 10)
		{
			$this->_email = $_SESSION['WebMailLoginInfo']['_email'];
			$this->_login = $_SESSION['WebMailLoginInfo']['_login'];
			$this->_password = $_SESSION['WebMailLoginInfo']['_password'];
			$this->_language = $_SESSION['WebMailLoginInfo']['_language'];
			$this->_advancedLogin = $_SESSION['WebMailLoginInfo']['_advancedLogin'];
			$this->_advMailProtocol = $_SESSION['WebMailLoginInfo']['_advMailProtocol'];
			$this->_advMailIncHost = $_SESSION['WebMailLoginInfo']['_advMailIncHost'];
			$this->_advMailIncPort = $_SESSION['WebMailLoginInfo']['_advMailIncPort'];
			$this->_advMailOutHost = $_SESSION['WebMailLoginInfo']['_advMailOutHost'];
			$this->_advMailOutPort = $_SESSION['WebMailLoginInfo']['_advMailOutPort'];
			$this->_advMailOutAuth = $_SESSION['WebMailLoginInfo']['_advMailOutAuth'];
			$this->_domainsSelectValue = $_SESSION['WebMailLoginInfo']['_domainsSelectValue'];
		}
	}
	
	function ClearSession()
	{
		if (isset($_SESSION['WebMailLoginInfo']))
		{
			unset($_SESSION['WebMailLoginInfo']);
		}
	}
	
	function IsSessionExist()
	{
		return isset($_SESSION['WebMailLoginInfo']);
	}
}


class CWebMailLogin
{
	/**
	 * @param Settings $_settings
	 * @param MySqlStorage $_dbStorage
	 * @param CWebMailLoginInfo $loginInfo
	 * @param Account $refAccount
	 * @param string $errorString
	 */
	function Init(&$_settings, &$_dbStorage, &$loginInfo, &$refAccount, &$errorString)
	{
		$accountCustomValues = array();
		$_log =& CLog::CreateInstance();
		
		$_isNoLoginField = false;
		$_sendSettingsList = false;

		/* custom class */
		wm_Custom::StaticUseMethod('ChangeLoginInfoBeforeInit', array(&$loginInfo));

		$_infoEmail = trim($loginInfo->getEmail());
		$_infoLogin = trim($loginInfo->getLogin());
		$_infoPassword = $loginInfo->getPassword();
		$_infoAdvancedLogin = $loginInfo->getAdvancedLogin();
		$_infoLang = trim($loginInfo->getLanguage());
		$_domain = $loginInfo->getDomainsSelectValue();

		$_email = $_login = $_optLogin = '';
		if ($_infoAdvancedLogin && $_settings->AllowAdvancedLogin)
		{
			$_email = $_infoEmail;
			$_login = $_infoLogin;
		}
		else
		{
			switch ($_settings->HideLoginMode)
			{
				case 0:
					$_email = $_infoEmail;
					$_login = $_infoLogin;
					break;

				case 10:
					$_email = $_infoEmail;
					$_isNoLoginField = true;

					$_emailAddress = new EmailAddress();
					$_emailAddress->SetAsString($_email);

					$_optLogin = $_emailAddress->GetAccountName();
					break;

				case 11:
					$_email = $_infoEmail;
					$_isNoLoginField = true;

					$_optLogin = $_email;
					break;

				case 20:
				case 21:
					$_login = $_infoLogin;
					
					$loginArray = ConvertUtils::ParseEmail($_login);
					if (20 == $_settings->HideLoginMode)
					{
						if (is_array($loginArray) && 2 === count($loginArray))
						{
							$_email = $_login;
						}
						else
						{
							$_email = $_login.'@';
							$_email .= ($_domain && $_settings->UseMultipleDomainsSelection) ? $_domain : $_settings->DefaultDomainOptional;
						}
					}
					else
					{
						$_email = (is_array($loginArray) && 2 === count($loginArray))
							? $loginArray[0].'@' : $_login.'@';
						$_email .= ($_domain && $_settings->UseMultipleDomainsSelection) ? $_domain : $_settings->DefaultDomainOptional;
					}
					break;

				case 22:
				case 23:
					$loginArray = ConvertUtils::ParseEmail($_infoLogin);
					$_login = (is_array($loginArray) && isset($loginArray[0]))
						? $loginArray[0].'@' : $_infoLogin.'@';
					$_login .= ($_domain && $_settings->UseMultipleDomainsSelection) ? $_domain : $_settings->DefaultDomainOptional;
					$_email = $_login;
			}
		}

		/* custom class */
		wm_Custom::StaticUseMethod('ChangeLoginDuringInit', array(&$_login, &$_email));

		$bReturn = true;
		wm_Custom::StaticUseMethod('LdapCustomLoginFunction',
			array(&$_login, &$_email, &$_infoPassword, &$accountCustomValues, &$errorString, &$bReturn));
		
		if (!$bReturn)
		{
			return false;
		}
		
		$_loginArray = null;
		if (USE_DB)
		{
			if ($_isNoLoginField)
			{
				$_loginArray =& Account::LoadFromDbOnlyByEmail($_email);
				if (is_array($_loginArray) && count($_loginArray) > 3)
				{
					$_eAccount =& Account::LoadFromDb((int) $_loginArray[0]);
					if ($_eAccount)
					{
						if ($_loginArray[5])
						{ 
							$errorString = 'Your account is inactive, please contact the system administrator on this.';
							return false;
						}
						$_login = (ConvertUtils::DecodePassword($_loginArray[1], $_eAccount) == $_infoPassword)
							? $_loginArray[4] : $_optLogin;
					}
					else
					{
						$_login = $_optLogin;
					}
				}
				else
				{
					$_login = $_optLogin;
				}
	
				/* custom class */
				wm_Custom::StaticUseMethod('ChangeLoginInfoAfterInit', array(&$_login, &$_email));
			}
			else
			{
				/* custom class */
				wm_Custom::StaticUseMethod('ChangeLoginInfoAfterInit', array(&$_login, &$_email));
	
				$_loginArray =& Account::LoadFromDbByLogin($_email, $_login);
				if ($_loginArray[4])
				{ 
					$errorString = 'Your account is inactive, please contact the system administrator on this.';
					return false;
				}
			}
		}
		
		if (!$_dbStorage || !$_dbStorage->Connect())
		{
			$_sendSettingsList = false;
			$errorString = getGlobalError();
			return false;
		}

		if ($_loginArray === false)
		{
			$errorString = getGlobalError();
			return false;
		}
		else if ($_loginArray === null)
		{
			if ($_settings->AllowNewUsersRegister)
			{
				if (!NumOLCallBackFunction($_settings, $_dbStorage, $errorString))
				{
					return false;
				}

				$_account = new Account();
				$_account->DefaultAccount = true;
				$_account->Email = $_email;
				$_account->MailIncLogin = $_login;
				$_account->MailIncPassword = $_infoPassword;
				if (strlen($_infoLang) > 0)
				{
					$_account->DefaultLanguage = $_infoLang;
				}
				
				$_account->CustomValues = $accountCustomValues;

				if ($_infoAdvancedLogin && $_settings->AllowAdvancedLogin)
				{
					$_account->MailProtocol = $loginInfo->getMailProtocol();
					$_account->MailIncPort = $loginInfo->getMailIncPort();
					$_account->MailOutPort = $loginInfo->getMailOutPort();
					$_account->MailOutAuthentication = $loginInfo->getMailOutAuth();
					$_account->MailIncHost = $loginInfo->getMailIncHost();
					$_account->MailOutHost = $loginInfo->getMailOutHost();
				}
				else
				{
					$_account->MailProtocol = (int) $_settings->IncomingMailProtocol;
					$_account->MailIncPort = (int) $_settings->IncomingMailPort;
					$_account->MailOutPort = (int) $_settings->OutgoingMailPort;
					$_account->MailOutAuthentication = (bool) $_settings->ReqSmtpAuth;
					$_account->MailIncHost = $_settings->IncomingMailServer;
					$_account->MailOutHost = $_settings->OutgoingMailServer;
				}

				if (DEMOACCOUNTALLOW && $_email == DEMOACCOUNTEMAIL)
				{
					$_account->MailIncPassword = DEMOACCOUNTPASS;
				}

				/* custom class */
				wm_Custom::StaticUseMethod('InitLdapSettingsAccountOnLogin', array(&$_account));

				if (0 < strlen($_infoLang))
				{
					$_account->DefaultLanguage = $_infoLang;
				}

				/* custom class */
				wm_Custom::StaticUseMethod('ChangeAccountBeforeCreateOnLogin', array(&$_account));

				if (USE_DB)
				{
					$_domain =& $_dbStorage->SelectDomainByName(EmailAddress::GetDomainFromEmail($_account->Email));
					if (null !== $_domain)
					{
						$_domain->UpdateAccount($_account, $_settings);
					}
				}

				$_validate = $_account->ValidateData();
				if ($_validate !== true)
				{
					$errorString = $_validate;
					return false;
				}
				else
				{
					if ($_account->IsInternal)
					{
						$errorString = ErrorPOP3IMAP4Auth;
						$_log->WriteLine('LOGIN Error: IsInternal = true', LOG_LEVEL_WARNING);
						return false;
					}

					$_processor = new MailProcessor($_account);
					if ($_processor->MailStorage->Connect(true))
					{
						$_user =& User::CreateUser($_account);
						if ($_user && $_account)
						{
							if (!USE_DB)
							{
								$_account->Id = 1;
							}
							$_account->IdUser = $_user->Id;
						}

						$_inboxSyncType = $_account->GetDefaultFolderSync($_settings);

						if ($_user != null && $_user->CreateAccount($_account, $_inboxSyncType, false, $_processor->MailStorage))
						{
							if ($_settings->EnableMobileSync && function_exists('mcrypt_encrypt'))
							{
								// create Funambol user for loginable user
								require_once(WM_ROOTPATH.'common/class_funambol_sync_users.php');
								$fnSyncUsers = new FunambolSyncUsers($_account);
								$fnSyncUsers->PerformSync();
							}

							$_SESSION[ACCOUNT_ID] = $_account->Id;
							$_SESSION[USER_ID] = $_account->IdUser;
							$_SESSION[SESSION_LANG] = $_account->DefaultLanguage;
							$_sendSettingsList = true;
							if (!USE_DB)
							{
								Account::SaveInSession($_account);
							}
							
							$_log->WriteEvent('User login', $_account);

							self::AfterLoginAction($_account, $_processor, $_settings);
						}
						else
						{
							if ($_user)
							{
								User::DeleteUserSettings($_user->Id);
							}

							$_error = getGlobalError();
							$_error = strlen($_error) > 0 ? $_error : CantCreateUser;

							$errorString = $_error;
							return false;
						}
					}
					else
					{
						$errorString = getGlobalError();
						return false;
					}
				}
			}
			else
			{
				$_log->WriteLine('LOGIN Error: AllowNewUsersRegister = false', LOG_LEVEL_WARNING);
				$errorString = ErrorPOP3IMAP4Auth;
				return false;
			}
		}
		else if ($_loginArray[2] == 0)
		{
			$errorString = PROC_CANT_LOG_NONDEF;
			return false;
		}
		else
		{
			if (USE_DB)
			{
				$_newAccount =& Account::LoadFromDb($_loginArray[0]);
				if (!$_newAccount)
				{
					$errorString = getGlobalError();
					return false;
				}
				else
				{
					$_deleted = $_dbStorage->GetAUserDeleted($_newAccount->IdUser);
					if (false === $_deleted)
					{
						$errorString = getGlobalError();
						return false;
					}
					else if (1 === $_deleted)
					{
						$errorString = ErrorMaximumUsersLicenseIsExceeded;
						return false;
					}
	
					$_mailIncPass = $_infoPassword;
	
					if (DEMOACCOUNTALLOW && $_email == DEMOACCOUNTEMAIL)
					{
						$_mailIncPass = DEMOACCOUNTPASS;
					}
	
					$_useLangUpdate = false;
					if (strlen($_infoLang) > 0 && $_newAccount->DefaultLanguage != $_infoLang)
					{
						$_newAccount->DefaultLanguage = $_infoLang;
						$_useLangUpdate = true;
					}
	
					$_account = null;

					$bIsPasswordCorrect = ConvertUtils::DecodePassword($_loginArray[1], $_newAccount) == $_mailIncPass;

					$_account =& $_newAccount;
					$_account->MailIncPassword = $_mailIncPass;

					$_newprocessor = new MailProcessor($_account);
					if ($_newprocessor->MailStorage->Connect(true))
					{
						if (!$bIsPasswordCorrect && !$_account->Update())
						{
							return ErrorPOP3IMAP4Auth;
						}

						$_SESSION[ACCOUNT_ID] = $_account->Id;
						$_SESSION[USER_ID] = $_account->IdUser;
						$_SESSION[SESSION_LANG] = $_account->DefaultLanguage;

						$tempFiles =& CTempFiles::CreateInstance($_account);
						$tempFiles->ClearAccount();
						unset($tempFiles);

						$_sendSettingsList = true;

						$_log->WriteEvent('User login', $_account);

						if ($_account->MailProtocol == MAILPROTOCOL_IMAP4 && $_account->ImapQuota === 1)
						{
							$quota = $_newprocessor->GetQuota();
							if ($quota !== false && $quota !== $_account->MailboxLimit)
							{
								$_account->MailboxLimit = GetGoodBigInt($quota);
								$_account->UpdateMailBoxLimit();
							}
						}

						self::AfterLoginAction($_account, $_newprocessor, $_settings);
					}
					else
					{
						$errorString = ErrorPOP3IMAP4Auth;
						return false;
					}
				}
			}
		}

		if ($_sendSettingsList && USE_DB)
		{
			if (!$_dbStorage->UpdateLastLoginAndLoginsCount($_account->IdUser))
			{
				$_sendSettingsList = false;
				$errorString = getGlobalError();
				return false;
			}
		}
		
		if (isset($_account))
		{
			$refAccount = $_account;
		}
		
		return true;
	}

	function AfterLoginAction(&$_account, &$_newprocessor, $_settings)
	{
		// create spam folder action
		if ($_account &&
				($_account->MailProtocol == MAILPROTOCOL_POP3 || $_account->MailProtocol == MAILPROTOCOL_IMAP4))
		{
			if ($_newprocessor->MailStorage->Connect(true))
			{
				$_account->NameSpace = $_newprocessor->GetNameSpacePrefix();
				$_account->UpdateNameSpace();
				
				$folders = $_newprocessor->GetFolders();
				if ($folders)
				{
					self::checkSystemFolderAndCreate($_settings, $_newprocessor, $_account, $folders, FOLDERTYPE_Spam, FOLDERNAME_Spam);
					self::checkSystemFolderAndCreate($_settings, $_newprocessor, $_account, $folders, FOLDERTYPE_Trash, FOLDERNAME_Trash);
				}
			}
		}
	}

	function checkSystemFolderAndCreate(&$_settings, &$_newprocessor, &$_account, &$folders, $folderType, $folderName)
	{
		$createSystemFoldersInInbox = (0 === strpos($_account->NameSpace, 'INBOX'));
		$createFoldersIfNotExist = CREATE_FOLDERS_IF_NOT_EXIST;

		/* custom class */
		wm_Custom::StaticUseMethod('ChangeValueOfSystemFoldersInInbox', array(&$createSystemFoldersInInbox));
		wm_Custom::StaticUseMethod('ChangeValueOfCreateFolderIfNotExist', array(&$createFoldersIfNotExist));

		$folderByName = $folders->GetFolderByName($folderName);
		$folderByType = $folders->GetFolderByType($folderType);

		if ($folderByName && !$folderByType)
		{
			if ($folderByName->Type !== $folderType)
			{
				$folderByName->Type = $folderType;
				if ($createFoldersIfNotExist && $folderByName->SyncType == FOLDERSYNC_DontSync)
				{
					$folderByName->SetFolderSync($_account->GetDefaultFolderSync($_settings));
					$_newprocessor->CreateFolder($folderByName);
				}
				else if ($_newprocessor->DbStorage->Connect())
				{
					$_newprocessor->DbStorage->UpdateFolder($folderByName);
				}
			}
		}
		else if (!$folderByType)
		{
			$prefix = '';
			if ($createSystemFoldersInInbox)
			{
				$inboxFolder = $folders->GetFolderByType(FOLDERTYPE_Inbox);
				if ($inboxFolder)
				{
					$prefix = $inboxFolder->FullName.$_account->Delimiter;
				}
			}

			$folder = new Folder($_account->Id, -1, $prefix.$folderName, $folderName, FOLDERSYNC_DontSync, $folderType);
			if ($createFoldersIfNotExist)
			{
				$folder->SetFolderSync($_account->GetDefaultFolderSync($_settings));
			}

			$_newprocessor->CreateFolder($folder);
		}
	}
}
