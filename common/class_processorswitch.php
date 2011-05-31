<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 *
 */

if (!class_exists('wm_CustomProcessingClass'))
{
	class wm_CustomProcessingClass {}
}

/**
 * only for processing.php
 */
class CProcessingSwitch extends wm_CustomProcessingClass
{
	var $_args;
	var $_log;

	function CProcessingSwitch()
	{
		$this->_args = null;
		$this->_log = null;
	}

	function DoResetpassword()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);

		$isGdSupport = @function_exists('imagecreatefrompng');
		
		$step = $_xmlObj->GetParamValueByName('step');
		if ($step == 1)
		{
			$_SESSION[SESSION_RESET_STEP] = 1;
			unset($_SESSION[SESSION_RESET_ACCT_ID]);
			
			$captcha = $_xmlObj->GetParamTagValueByName('captcha');
			if ($isGdSupport && (!isset($_SESSION['captcha_keystring']) || (string) $captcha !== (string) $_SESSION['captcha_keystring']))
			{
				CXmlProcessing::PrintErrorAndExit(CaptchaError, $_xmlRes);
			}

			$login = $_xmlObj->GetParamTagValueByName('login');
			$domain = $_xmlObj->GetParamTagValueByName('domain');

			$email = $login.'@'.$domain;
			$_loginArray =& Account::LoadFromDbOnlyByEmail($email);
			if (is_array($_loginArray) && count($_loginArray) > 3)
			{
				$_eAccount =& Account::LoadFromDb((int) $_loginArray[0]);
				if ($_eAccount && $_eAccount->IsInternal)
				{
					if (strlen($_eAccount->Question1.$_eAccount->Question2) > 0)
					{
						$_SESSION[SESSION_RESET_STEP] = 2;
						$_SESSION[SESSION_RESET_ACCT_ID] = $_eAccount->Id;
						
						$_resetNode = new XmlDomNode('reset');
						$_resetNode->AppendAttribute('step', 2);
						$_resetNode->AppendChild(new XmlDomNode('email', $_eAccount->Email, true));
						$_resetNode->AppendChild(new XmlDomNode('q1', $_eAccount->Question1, true));
						$_resetNode->AppendChild(new XmlDomNode('q2', $_eAccount->Question2, true));

						$_xmlRes->XmlRoot->AppendChild($_resetNode);
						return;
					}
					else
					{
						CXmlProcessing::PrintErrorAndExit(RegUnrecoverableAccount, $_xmlRes);
					}
				}
				else
				{
					CXmlProcessing::PrintErrorAndExit(WebMailException, $_xmlRes);
				}
			}
			else
			{
				CXmlProcessing::PrintErrorAndExit(RegUnknownAdress, $_xmlRes);
			}
		}
		else if ($step == 2 && isset($_SESSION[SESSION_RESET_ACCT_ID], $_SESSION[SESSION_RESET_STEP])
				&& $_SESSION[SESSION_RESET_STEP] > 1)
		{
			$account =& Account::LoadFromDb($_SESSION[SESSION_RESET_ACCT_ID]);
			if ($account)
			{
				$answer1 = $_xmlObj->GetParamTagValueByName('answer1');
				$answer2 = $_xmlObj->GetParamTagValueByName('answer2');

				if ((string) $account->Answer1 === (string) $answer1 && (string) $account->Answer2 === (string) $answer2)
				{
					$_SESSION[SESSION_RESET_STEP] = 3;

					$_resetNode = new XmlDomNode('reset');
					$_resetNode->AppendAttribute('step', 3);
					$_resetNode->AppendChild(new XmlDomNode('email', $account->Email, true));
					$_resetNode->AppendChild(new XmlDomNode('name', $account->FriendlyName, true));

					$_xmlRes->XmlRoot->AppendChild($_resetNode);
					return;
				}
				else
				{
					CXmlProcessing::PrintErrorAndExit(RegAnswersIncorrect, $_xmlRes);
				}
			}
		}
		else if ($step == 3 && isset($_SESSION[SESSION_RESET_ACCT_ID], $_SESSION[SESSION_RESET_STEP])
				&& $_SESSION[SESSION_RESET_STEP] > 2)
		{
			$account =& Account::LoadFromDb($_SESSION[SESSION_RESET_ACCT_ID]);
			if ($account)
			{
				$password1 = $_xmlObj->GetParamTagValueByName('password1');
				$password2 = $_xmlObj->GetParamTagValueByName('password2');

				if ((string) $password1 === (string) $password2)
				{
					$account->MailIncPassword = $password1;
					$account->MailOutPassword = $password1;
					if ($account->UpdatePasswords())
					{
						unset($_SESSION[SESSION_RESET_ACCT_ID], $_SESSION[SESSION_RESET_STEP]);
						
						$_resetNode = new XmlDomNode('reset');
						$_resetNode->AppendAttribute('step', 4);

						$_xmlRes->XmlRoot->AppendChild($_resetNode);
						return true;
					}
					else
					{
						CXmlProcessing::PrintErrorAndExit(PROC_CANT_UPDATE_ACCT, $_xmlRes);
					}
				}
				else
				{
					CXmlProcessing::PrintErrorAndExit(WarningPassNotMatch, $_xmlRes);
				}
			}
		}

		CXmlProcessing::PrintErrorAndExit(WebMailException, $_xmlRes);
	}

	function DoRegistration()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		$isGdSupport = @function_exists('imagecreatefrompng');

		$captcha = $_xmlObj->GetParamTagValueByName('captcha');
		if ($isGdSupport && (!isset($_SESSION['captcha_keystring']) || $captcha != $_SESSION['captcha_keystring']))
		{
			CXmlProcessing::PrintErrorAndExit(CaptchaError, $_xmlRes);
		}

		if (true)
		{
			$_login = trim($_xmlObj->GetParamTagValueByName('login'));
			$_domainName = trim($_xmlObj->GetParamTagValueByName('domain'));

			$_domain =& $_dbStorage->SelectDomainByName($_domainName);
			if (!$_domain)
			{
				CXmlProcessing::PrintErrorAndExit(RegDomainNotExist, $_xmlRes);
			}

			$_email = $_login.'@'.$_domainName;

			$_loginArray =& Account::LoadFromDbOnlyByEmail($_email);
			if (is_array($_loginArray) && count($_loginArray) > 3)
			{
				CXmlProcessing::PrintErrorAndExit(RegAccountExist, $_xmlRes);
			}	

			$_password = trim($_xmlObj->GetParamTagValueByName('pass'));
			$_lang = trim($_xmlObj->GetParamTagValueByName('lang'));
			$_timezone = trim($_xmlObj->GetParamTagValueByName('timezone'));

			$_account = new Account();
			$_account->DefaultAccount = true;
			$_account->Email = $_email;
			$_account->MailIncLogin = $_email;
			$_account->MailIncPassword = $_password;
			$_account->DefaultLanguage = $_lang;
			$_account->DefaultTimeZone = $_timezone;
			$_account->FriendlyName = trim($_xmlObj->GetParamTagValueByName('name'));
			$_account->Delimiter = '.';
			
			$_account->Question1 = trim($_xmlObj->GetParamTagValueByName('question_1'));
			$_account->Answer1 = trim($_xmlObj->GetParamTagValueByName('answer_1'));
			$_account->Question2 = trim($_xmlObj->GetParamTagValueByName('question_2'));
			$_account->Answer2 = trim($_xmlObj->GetParamTagValueByName('answer_2'));

			$_domain->UpdateAccount($_account, $_settings);

			$_validate = $_account->ValidateData();
			if ($_validate !== true)
			{
				CXmlProcessing::PrintErrorAndExit($_validate, $_xmlRes);
			}
			else
			{
				if ($_account->IsInternal)
				{
					require_once(WM_ROOTPATH.'common/class_exim.php');
					if (!CExim::CreateUserShell($_login, $_domainName, $_account->MailboxLimit))
					{
						CXmlProcessing::PrintErrorAndExit(CantCreateUser, $_xmlRes);
					}
				
					$_dbStorage->InsertAccountData($_account);
				}

				$_processor = new MailProcessor($_account);
				if ($_processor->MailStorage->Connect(true))
				{
					$_user =& User::CreateUser($_account);
					if ($_user && $_account)
					{
						$_account->IdUser = $_user->Id;
					}

					$_inboxSyncType = $_account->GetDefaultFolderSync($_settings);

					if ($_user != null && $_user->CreateAccount($_account, $_inboxSyncType, false, $_processor->MailStorage))
					{
						$_SESSION[ACCOUNT_ID] = $_account->Id;
						$_SESSION[USER_ID] = $_account->IdUser;
						$_SESSION[SESSION_LANG] = $_account->DefaultLanguage;
					}
					else
					{
						if ($_user)
						{
							User::DeleteUserSettings($_user->Id);
						}

						$_error = getGlobalError();
						$_error = strlen($_error) > 0 ? $_error : CantCreateUser;

						CXmlProcessing::PrintErrorAndExit($_error, $_xmlRes);
					}
				}
				else
				{
					if ($_account->IsInternal)
					{
						$_dbStorage->DeleteOnlyAccountData($_account->Id);
						CExim::DeleteUserShell($_login, $_domainName);
					}
					CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
				}
			}

			$_regNode = new XmlDomNode('registration');
			if ($_xmlObj->GetParamValueByName('sign_me') && $_account)
			{
				$_regNode->AppendAttribute('id_acct', $_account->Id);
				$_regNode->AppendChild(new XmlDomNode('hash',
					md5(ConvertUtils::EncodePassword($_account->MailIncPassword, $_account)), true));
			}

			$_xmlRes->XmlRoot->AppendChild($_regNode);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit('error', $_xmlRes);
		}
	}

	function DoLogin()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$jsTimeOffset = $_xmlObj->GetParamValueByName('js_timeoffset');
		if (strlen($jsTimeOffset) > 0)
		{
			$_SESSION[JS_TIMEOFFSET] = $jsTimeOffset;
		}

		if ($_settings->UseCaptcha)
		{
			$captcha = $_xmlObj->GetParamValueByName('captcha');

			if (isset($_SESSION['captcha_count']) && (int) $_SESSION['captcha_count'] >= CATCHA_COUNT_LIMIT)
			{
				if (!isset($_SESSION['captcha_keystring']) || $captcha != $_SESSION['captcha_keystring'])
				{
					CXmlProcessing::PrintErrorAndExit(CaptchaError, $_xmlRes);
				}
			}
			
			$_SESSION['captcha_count'] = isset($_SESSION['captcha_count']) ?
				(int) $_SESSION['captcha_count'] + 1 : 1;

			if ((int) $_SESSION['captcha_count'] >= CATCHA_COUNT_LIMIT)
			{
				$_captchaOn = new XmlDomNode('captcha', '1');
				$_xmlRes->XmlRoot->AppendChild($_captchaOn);
			}
		}
		
		require_once(WM_ROOTPATH.'common/class_login.php');

		$domain = null;
		if ($_settings->UseMultipleDomainsSelection)
		{
			$domain = trim($_xmlObj->GetParamTagValueByName('domain_name'));
			if ('' == $domain)
			{
				$domain = null;
			}
		}
		
		$loginInfo = new CWebMailLoginInfo(
			$_xmlObj->GetParamTagValueByName('email'),
			$_xmlObj->GetParamTagValueByName('mail_inc_login'),
			$_xmlObj->GetParamTagValueByName('mail_inc_pass'),
			$_xmlObj->GetParamTagValueByName('language'),
			$_xmlObj->GetParamValueByName('advanced_login'),
			$_xmlObj->GetParamValueByName('mail_protocol'),
			$_xmlObj->GetParamTagValueByName('mail_inc_host'),
			$_xmlObj->GetParamValueByName('mail_inc_port'),
			$_xmlObj->GetParamTagValueByName('mail_out_host'),
			$_xmlObj->GetParamValueByName('mail_out_port'),
			$_xmlObj->GetParamValueByName('mail_out_auth'),
			$domain
		);

		$errorString = $account = null;
		if (CWebMailLogin::Init($_settings, $_dbStorage, $loginInfo, $account, $errorString, 'NumOLProcessingCallBackFunction'))
		{
			$_loginNode = new XmlDomNode('login');
		
			if ($_xmlObj->GetParamValueByName('sign_me') && $account)
			{
				$_loginNode->AppendAttribute('id_acct', $account->Id);
				$_loginNode->AppendChild(new XmlDomNode('hash',
					md5(ConvertUtils::EncodePassword($account->MailIncPassword, $account)), true));
			}

			$_xmlRes->XmlRoot->AppendChild($_loginNode);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit($errorString, $_xmlRes);
		}
	}

	function DoNewAccount()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId);
		if (!$_account->AllowChangeSettings || !$_settings->AllowUsersAddNewAccounts)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_ERROR_ACCT_CREATE, $_xmlRes);
		}

		if (!$_account)
		{
			CXmlProcessing::PrintErrorAndExit('', $_xmlRes, 2);
		}

		$_dbStorage =& DbStorageCreator::CreateDatabaseStorage($_account);
		
		if ($_account->IsDemo)
		{
			CXmlProcessing::GetAccountList($_dbStorage, $_xmlRes, $_account, $_account->Id);
			break;
		}

		$_newAccount = new Account();
		$_newAccount->DefaultAccount = false;

		CXmlProcessing::UpdateAccountFromRequest($_xmlObj->XmlRoot, $_newAccount);

		if (!$_settings->AllowUsersChangeAccountsDef)
		{
			$_newAccount->DefaultAccount = false;
		}

		$_domain =& $_dbStorage->SelectDomainByName(EmailAddress::GetDomainFromEmail($_newAccount->Email));
		if ($_domain && !$_domain->IsInternal())
		{
			$_domain->UpdateAccount($_newAccount, $_settings);
		}

		$_newAccount->ImapQuota = 0;

		$_accountNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('account');

		$_user = new User();
		$_user->Id = $_account->IdUser;
		$_SESSION[USER_ID] = $_account->IdUser;
		$_newAccount->IdUser = $_account->IdUser;

		$_folderSync = $_accountNode->GetAttribute('inbox_sync_type', FOLDERSYNC_AllEntireMessages);
		if ($_newAccount->MailProtocol != MAILPROTOCOL_POP3)
		{
			$_folderSync = $_newAccount->GetDefaultFolderSync($_settings);
		}
		
		$_validatedError = $_newAccount->ValidateData();
		if (true !== $_validatedError)
		{
			CXmlProcessing::PrintErrorAndExit($_validatedError, $_xmlRes);
		}

		if ($_user->CreateAccount($_newAccount, $_folderSync))
		{
			if (isset($_SESSION[ACCOUNT_IDS]))
			{
				unset($_SESSION[ACCOUNT_IDS]);
			}
			CXmlProcessing::GetAccountList($_dbStorage, $_xmlRes, $_account, $_newAccount->Id);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
		}
	}

	function DoNewFilter()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_editAccount = null;

		$_filter =& CXmlProcessing::GetFilterFromRequest($_xmlObj->XmlRoot);
		if (!$_filter)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_INS_NEW_FILTER, $_xmlRes);
		}

		CXmlProcessing::CheckAccountAccess($_filter->IdAcct, $_xmlRes);

		$_editAccount =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_filter->IdAcct, false, false);

		if ($_editAccount->IsDemo)
		{
			CXmlProcessing::GetFiltersList($_xmlRes, $_filter->IdAcct);
			break;
		}

		if (empty($_filter->Filter))
		{
			CXmlProcessing::PrintErrorAndExit(JS_LANG_WarningEmptyFilter, $_xmlRes);
		}
		else if ($_dbStorage->InsertFilter($_filter))
		{
			CXmlProcessing::GetFiltersList($_xmlRes, $_filter->IdAcct);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_INS_NEW_FILTER, $_xmlRes);
		}
	}

	function DoSyncContacts()
	{
		$_dbStorage = $settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $settings, $_xmlObj, $_xmlRes, $_accountId);

		$account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);

		$_syncType = (int) $_xmlObj->GetParamValueByName('sync_type');
		if ($_syncType === SYNC_TYPE_FUNAMBOL)
		{
			require_once WM_ROOTPATH.'common/sync_funambol_contacts.php';
			require_once WM_ROOTPATH.'common/sync_funambol_calendars.php';
			require_once WM_ROOTPATH.'common/sync_funambol_users.php';

			$syncFunambolContacts	= new SyncFunambolContacts($account, $settings);
			$syncFunambolCalendars	= new SyncFunambolCalendars($account, $settings);
			$syncFunambolUsers		= new SyncFunambolUsers($account);

			if ($syncFunambolUsers->PerformSync() &&
					$syncFunambolContacts->performSync() && $syncFunambolCalendars->performSync())
			{
				$_updateNode = new XmlDomNode('update');
				$_updateNode->AppendAttribute('value', 'sync_contacts');
				$_xmlRes->XmlRoot->AppendChild($_updateNode);
			}
			else
			{
				CXmlProcessing::PrintErrorAndExit(ContactSyncError, $_xmlRes);
			}
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(ContactSyncError, $_xmlRes);
		}
	}

	function DoNewFolder()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_acctId = (int) $_xmlObj->GetParamValueByName('id_acct');

		CXmlProcessing::CheckAccountAccess($_acctId, $_xmlRes);

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_acctId, false, false);
		$_folder = null;

		$_parentId = $_xmlObj->GetParamValueByName('id_parent');

		$_folderName = ConvertUtils::ConvertEncoding(
			$_xmlObj->GetParamTagValueByName('name'),
			$_account->GetUserCharset(), CPAGE_UTF7_Imap);

		$_parentPath = ($_parentId == -1) ? '' : $_xmlObj->GetParamTagValueByName('full_name_parent').$_account->Delimiter;

		$_create = (bool) $_xmlObj->GetParamValueByName('create');

		if ($_account->MailProtocol == MAILPROTOCOL_WMSERVER)
		{
			$_folder = new Folder($_acctId, -1, $_parentPath.$_folderName, $_folderName, FOLDERSYNC_AllHeadersOnly);
		}
		else if ($_account->MailProtocol == MAILPROTOCOL_IMAP4)
		{
			$_folderSync = $_account->GetDefaultFolderSync($_settings);

			$_folder = new Folder($_acctId, -1, $_parentPath.$_folderName, $_folderName,
				($_create) ? $_folderSync : FOLDERSYNC_DontSync);
		}
		else
		{
			$_folder = new Folder($_acctId, -1, $_parentPath.$_folderName, $_folderName);
		}

		$_folder->IdParent = $_parentId;
		$_folder->Type = FOLDERTYPE_Custom;
		$_folder->Hide = false;

		$_validate = $_folder->ValidateData();
		if (true !== $_validate)
		{
			CXmlProcessing::PrintErrorAndExit($_validate, $_xmlRes);
		}

		$_processor = new MailProcessor($_account);

		$_folders =& $_processor->GetFolders();

		$_folderList =& $_folders->CreateFolderListFromTree();

		$_folderExist = false;
		$_folderListKeys = array_keys($_folderList->Instance());
		foreach ($_folderListKeys as $_key)
		{
			$_listFolder =& $_folderList->Get($_key);

			if (strtolower($_listFolder->FullName) == strtolower($_folder->FullName))
			{
				$_folderExist = true;
				break;
			}

			unset($_listFolder);
		}

		if ($_folderExist)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_FOLDER_EXIST, $_xmlRes);
		}
		else if ($_account->IsDemo || $_processor->CreateFolder($_folder, $_create))
		{
			if (!$_account->IsDemo)
			{
				$log =& CLog::CreateInstance();
				$log->WriteEvent('User create personal folder ("'.$_folder->FullName.'")', $_account);
			}

			$_folders =& $_processor->GetFolders();

			$_foldersList = new XmlDomNode('folders_list');
			$_foldersList->AppendAttribute('sync', -1);
			$_foldersList->AppendAttribute('id_acct', $_acctId);
			$_foldersList->AppendAttribute('namespace', $_account->NameSpace);

			CXmlProcessing::GetFoldersTreeXml($_folders, $_foldersList, $_processor);
			$_xmlRes->XmlRoot->AppendChild($_foldersList);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_CREATE_FLD, $_xmlRes);
		}
	}

	function DoNewContact()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		if (!$_settings->AllowContacts)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_INS_NEW_CONTS, $_xmlRes);
		}

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);
		$contactManager =& ContactCreator::CreateContactStorage($_account, $_settings);
		
		$_addressBookRecord = new AddressBookRecord();

		CXmlProcessing::UpdateContactFromRequest($_xmlObj->XmlRoot, $_addressBookRecord, $_accountId);

		$_validatedError = $_addressBookRecord->validateData();
		if (true !== $_validatedError)
		{
			CXmlProcessing::PrintErrorAndExit($_validatedError, $_xmlRes);
		}

		$_primaryEmail = null;
		switch ($_addressBookRecord->PrimaryEmail)
		{
			default:
			case PRIMARYEMAIL_Home:
				$_primaryEmail = $_addressBookRecord->HomeEmail;
				break;
			case PRIMARYEMAIL_Business:
				$_primaryEmail = $_addressBookRecord->BusinessEmail;
				break;
			case PRIMARYEMAIL_Other:
				$_primaryEmail = $_addressBookRecord->OtherEmail;
				break;
		}

		if ($contactManager->IsContactExist($_addressBookRecord->FullName, $_primaryEmail))
		{
			CXmlProcessing::PrintErrorAndExit(ErrorContactExists, $_xmlRes);
		}

		$_isError = true;
		if ($contactManager->CreateContact($_addressBookRecord))
		{
			$_contactNode = $_groupsNode = null;
			$_contactNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('contact');
			if ($_contactNode)
			{
				$_groupsNode =& $_contactNode->GetChildNodeByTagName('groups');
				if ($_groupsNode)
				{
					$_result = true;
					$_groupsKeys = array_keys($_groupsNode->Children);
					foreach ($_groupsKeys as $_key)
					{
						$_gr =& $_groupsNode->Children[$_key];
						$_idGr = $_gr->GetAttribute('id', -1);
						if ($_idGr > 0)
						{
							$_result &= $contactManager->InsertContactToGroup($_addressBookRecord->IdAddress, $_idGr); 
						}
						else
						{
							$_result = false;
						}
						unset($_gr);
					}

					$_isError = false;
					CXmlProcessing::GetContactList($_account, $_settings, $_xmlObj, $_xmlRes, $_addressBookRecord->IdAddress);
				}
			}
		}

		if ($_isError)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_INS_NEW_CONTS, $_xmlRes);
		}
		else
		{
			$log =& CLog::CreateInstance();
			$log->WriteEvent('User add PAB (contact)', $_account);
		}
	}

	function DoNewGroup()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		if (!$_settings->AllowContacts)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_INS_NEW_GROUP, $_xmlRes);
		}

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);
		$contactManager =& ContactCreator::CreateContactStorage($_account, $_settings);
		
		$_groupNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('group');

		$_group = new AddressGroup();

		$_group->IdUser = $_account->IdUser;
		$_group->Name = $_groupNode->GetChildValueByTagName('name', true);

		$_group->IsOrganization = false;
		if (isset($_groupNode->Attributes['organization']))
		{
			$_group->IsOrganization = (bool) $_groupNode->Attributes['organization'];
		}

		if ($_group->IsOrganization)
		{
			$_group->Email = $_groupNode->GetChildValueByTagName('email', true);
			$_group->Company = $_groupNode->GetChildValueByTagName('company', true);
			$_group->Street = $_groupNode->GetChildValueByTagName('street', true);
			$_group->City = $_groupNode->GetChildValueByTagName('city', true);
			$_group->State = $_groupNode->GetChildValueByTagName('state', true);
			$_group->Zip = $_groupNode->GetChildValueByTagName('zip', true);
			$_group->Country = $_groupNode->GetChildValueByTagName('country', true);
			$_group->Phone = $_groupNode->GetChildValueByTagName('phone', true);
			$_group->Fax = $_groupNode->GetChildValueByTagName('fax', true);
			$_group->Web = $_groupNode->GetChildValueByTagName('web', true);
		}

		$_result = false;

		$_validatedError = $_group->validateData();

		if (true !== $_validatedError)
		{
			CXmlProcessing::PrintErrorAndExit($_validatedError, $_xmlRes);
		}

		if ($contactManager->IsGroupExist($_group->Name))
		{
			CXmlProcessing::PrintErrorAndExit(WarningGroupAlreadyExist, $_xmlRes);
		}

		if ($contactManager->CreateGroup($_group))
		{
			$_result = true;

			$_contactsNode =& $_groupNode->GetChildNodeByTagName('contacts');
			$_contactsKeys = array_keys($_contactsNode->Children);
			foreach ($_contactsKeys as $_key)
			{
				$_cc =& $_contactsNode->Children[$_key];
				if (isset($_cc->Attributes['id']))
				{
					$_result &= $contactManager->InsertContactToGroup($_cc->Attributes['id'], $_group->Id); 
				}
				else
				{
					$_result = false;
				}
				unset($_cc);
			}

			$_contactsNode =& $_groupNode->GetChildNodeByTagName('new_contacts');

			$_contactsKeys= array_keys($_contactsNode->Children);
			foreach ($_contactsKeys as $_key)
			{
				$_cc =& $_contactsNode->Children[$_key];
				$_personalNode =& $_cc->GetChildNodeByTagName('personal');

				$_addressBookRecord = new AddressBookRecord();
				$_addressBookRecord->IdUser = $_account->IdUser;
				$_addressBookRecord->HomeEmail = $_personalNode->GetChildValueByTagName('email');
				$_addressBookRecord->PrimaryEmail = PRIMARYEMAIL_Home;

				$_result &= $contactManager->CreateContact($_addressBookRecord);
				$_result &= $contactManager->InsertContactToGroup($_addressBookRecord->IdAddress, $_group->Id);
				unset($_cc, $_personalNode, $_addressBookRecord);
			}
		}

		if ($_result)
		{
			CXmlProcessing::GetContactList($_account, $_settings, $_xmlObj, $_xmlRes);
			$log =& CLog::CreateInstance();
			$log->WriteEvent('User add PAB (group "'.$_group->Name.'")', $_account);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_INS_NEW_GROUP, $_xmlRes);
		}
	}

	function DoSetSender()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		if (isset($_SESSION[USER_ID]))
		{
			$_value = (int) $_xmlObj->GetParamValueByName('safety');
			$_emailString = trim($_xmlObj->GetParamTagValueByName('sender'));
			$_emailObj = new EmailAddress();
			$_emailObj->Parse($_emailString);
			if ($_emailObj->Email)
			{
				$_dbStorage->SetSenders($_emailObj->Email, $_value, $_SESSION[USER_ID]);
			}
		}

		$_updateNode = new XmlDomNode('update');
		$_updateNode->AppendAttribute('value', 'set_sender');
		$_xmlRes->XmlRoot->AppendChild($_updateNode);
	}

	function DoAddContacts()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		if (!$_settings->AllowContacts)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_ADD_NEW_CONT_TO_GRP, $_xmlRes);
		}

		$_groupId = $_xmlObj->GetParamValueByName('id_group');

		$_contactsNode = $_xmlObj->XmlRoot->GetChildNodeByTagName('contacts');

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);
		$contactManager =& ContactCreator::CreateContactStorage($_account, $_settings);
		
		$_result = true;
		$_contactsKeys= array_keys($_contactsNode->Children);
		foreach ($_contactsKeys as $_key)
		{
			$_cc =& $_contactsNode->Children[$_key];
			if (isset($_cc->Attributes['id']))
			{
				$_result &= $contactManager->AddContactToGroup($_cc->Attributes['id'], $_groupId);
			}
			else
			{
				$_result = false;
			}
			unset($_cc);
		}
		if ($_result)
		{
			$_updateNode = new XmlDomNode('update');
			$_updateNode->AppendAttribute('value', 'group');
			$_xmlRes->XmlRoot->AppendChild($_updateNode);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_ADD_NEW_CONT_TO_GRP, $_xmlRes);
		}
	}

	function DoUpdateIdAcct()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_idAcct = $_xmlObj->GetParamValueByName('id_acct');
		if (CXmlProcessing::CheckAccountAccess($_idAcct, $_xmlRes))
		{
			$_SESSION[ACCOUNT_ID] = $_idAcct;
		}

		$_settingsNodeAdd = new XmlDomNode('update');
		$_settingsNodeAdd->AppendAttribute('value', 'id_acct');
		$_xmlRes->XmlRoot->AppendChild($_settingsNodeAdd);
	}

	function DoUpdateDefOrder()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);
		$_tmpOrder = $_xmlObj->GetParamValueByName('def_order');
		if (strlen($_tmpOrder) > 0 && $_tmpOrder != $_account->DefaultOrder)
		{
			$_account->DefaultOrder = $_tmpOrder;
			$_account->UpdateDefaultOrder();
		}

		$_settingsNodeAdd = new XmlDomNode('update');
		$_settingsNodeAdd->AppendAttribute('value', 'def_order');
		$_xmlRes->XmlRoot->AppendChild($_settingsNodeAdd);
	}

	function DoUpdateCookieSettings()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId);
		$_account->HideFolders = $_xmlObj->GetParamValueByName('hide_folders');
		$_account->HorizResizer = $_xmlObj->GetParamValueByName('horiz_resizer');
		$_account->VertResizer = $_xmlObj->GetParamValueByName('vert_resizer');
		$_account->Mark = $_xmlObj->GetParamValueByName('mark');
		$_account->Reply = $_xmlObj->GetParamValueByName('reply');

		$_columnsNode = $_xmlObj->XmlRoot->GetChildNodeByTagName('columns');

		if ($_columnsNode != null)
		{
			$_columnKeys = array_keys($_columnsNode->Children);
			foreach ($_columnKeys as $_key)
			{
				$_cc =& $_columnsNode->Children[$_key];
				$_id = isset($_cc->Attributes['id']) ? (int) $_cc->Attributes['id'] : -1;
				$_value = isset($_cc->Attributes['value']) ? (int) $_cc->Attributes['value'] : -1;
				if ($_id > -1 && $_value > -1)
				{
					$_account->Columns[$_id] = $_value;
				}
				unset($_cc);
			}
		}

		$_account->Update();

		$_settingsNodeAdd = new XmlDomNode('update');
		$_settingsNodeAdd->AppendAttribute('value', 'cookie_settings');
		$_xmlRes->XmlRoot->AppendChild($_settingsNodeAdd);
	}

	function DoUpdateAutoresponder()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_idAcct = $_xmlObj->GetParamValueByName('id_acct');
		$_autoresponderNode = $_xmlObj->XmlRoot->GetChildNodeByTagName('autoresponder');
		$_isEnable = (bool) $_autoresponderNode->GetAttribute('enable', 0);
		$_subject = $_autoresponderNode->GetChildValueByTagName('subject');
		$_message = $_autoresponderNode->GetChildValueByTagName('message');

		CXmlProcessing::CheckAccountAccess($_idAcct, $_xmlRes);

		$_editAccount =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_idAcct);
		if (!$_editAccount->AllowChangeSettings)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_UPDATE_ACCT, $_xmlRes);
		}

		$_editProcessor = new MailProcessor($_editAccount);
		if ($_isEnable)
		{
			$_editProcessor->SetAutoresponder($_subject, $_message);
		}
		else
		{
			$_editProcessor->DisableAutoresponder();
		}

		$_updateNode = new XmlDomNode('update');
		$_updateNode->AppendAttribute('value', 'autoresponder');
		$_xmlRes->XmlRoot->AppendChild($_updateNode);
	}

	function DoUpdateSettings()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId);
		$_settingsReqNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('settings');

		$_MailsPerPage = (int) $_settingsReqNode->GetAttribute('msgs_per_page', $_account->MailsPerPage);
		if ($_MailsPerPage < 1)
		{
			$_MailsPerPage = 1;
		}
		
		$_ContactsPerPage = (int) $_settingsReqNode->GetAttribute('contacts_per_page', $_account->ContactsPerPage);
		if ($_ContactsPerPage < 1)
		{
			$_ContactsPerPage = 1;
		}

		$_AutoCheckMailInterval = (int) $_settingsReqNode->GetAttribute('auto_checkmail_interval', $_account->AutoCheckMailInterval);
		if (!in_array($_AutoCheckMailInterval, array(0, 1, 5, 10, 15, 30)))
		{
			$_AutoCheckMailInterval = 0;
		}
		
		$_AllowDhtmlEditor = (bool) $_settingsReqNode->GetAttribute('allow_dhtml_editor', $_account->AllowDhtmlEditor);

		$_DefaultOutCharset = $_account->DefaultOutCharset;
		if ($_settings->AllowUsersChangeCharset)
		{
			$_DefaultOutCharset = ConvertUtils::GetCodePageName($_settingsReqNode->GetAttribute('def_charset_out', 0));
		}

		$_DefaultTimeZone = $_account->DefaultTimeZone;
		if ($_settings->AllowUsersChangeTimeZone)
		{
			$_DefaultTimeZone = $_settingsReqNode->GetAttribute('def_timezone', $_account->DefaultTimeZone);
		}

		$_ViewMode = (int) $_settingsReqNode->GetAttribute('view_mode', $_account->ViewMode);

		$_DefaultSkin = $_account->DefaultSkin;
		if ($_settings->AllowUsersChangeSkin)
		{
			$_DefaultSkin = $_settingsReqNode->GetChildValueByTagName('def_skin');
		}

		$_DefaultLanguage = $_account->DefaultLanguage;
		if ($_settings->AllowUsersChangeLanguage)
		{
			$_DefaultLanguage = $_settingsReqNode->GetChildValueByTagName('def_lang');
			$_SESSION[SESSION_LANG] = $_DefaultLanguage;
			setcookie('awm_defLang', $_DefaultLanguage, time() + 31104000);
		}

		$_dateFormat = $_settingsReqNode->GetChildValueByTagName('def_date_fmt');
		if ($_dateFormat === '')
		{
			$_dateFormat = $_account->DefaultDateFormat;
		}

		$_timeFormat = $_settingsReqNode->GetAttribute('time_format', $_account->DefaultTimeFormat);

		if ($_account->IsDemo)
		{
			$_SESSION[DEMO_SES][DEMO_S_MessagesPerPage] = $_MailsPerPage;
			$_SESSION[DEMO_SES][DEMO_S_ContactsPerPage] = $_ContactsPerPage;
			$_SESSION[DEMO_SES][DEMO_S_AllowDhtmlEditor] = $_AllowDhtmlEditor;
			$_SESSION[DEMO_SES][DEMO_S_DefaultOutCharset] = $_DefaultOutCharset;
			$_SESSION[DEMO_SES][DEMO_S_DefaultTimeZone] = $_DefaultTimeZone;
			$_SESSION[DEMO_SES][DEMO_S_ViewMode] = $_ViewMode;
			$_SESSION[DEMO_SES][DEMO_S_DefaultSkin] = $_DefaultSkin;
			$_SESSION[DEMO_SES][DEMO_S_DefaultLanguage] = $_DefaultLanguage;
			$_SESSION[DEMO_SES][DEMO_S_DefaultDateFormat] = $_dateFormat;
			$_SESSION[DEMO_SES][DEMO_S_DefaultTimeFormat] = $_timeFormat;
			$_SESSION[DEMO_SES][DEMO_S_AutoCheckMailInterval] = $_AutoCheckMailInterval;
		}
		else
		{
			$_account->MailsPerPage = $_MailsPerPage;
			$_account->ContactsPerPage = $_ContactsPerPage;
			$_account->AllowDhtmlEditor = $_AllowDhtmlEditor;
			$_account->DefaultOutCharset = $_DefaultOutCharset;
			$_account->DefaultTimeZone = $_DefaultTimeZone;
			$_account->ViewMode = $_ViewMode;
			$_account->DefaultSkin = $_DefaultSkin;
			$_account->DefaultLanguage = $_DefaultLanguage;
			$_account->DefaultDateFormat = $_dateFormat;
			$_account->DefaultTimeFormat = $_timeFormat;
			$_account->AutoCheckMailInterval = $_AutoCheckMailInterval;
			
			$_account->DefaultIncCharset = $_account->DefaultOutCharset;
		}

		$_validate = $_account->ValidateData();
		if (true !== $_validate)
		{
			CXmlProcessing::PrintErrorAndExit($_validate, $_xmlRes);
		}
		else if ($_account->Update(null))
		{
			$_updateNode = new XmlDomNode('update');
			$_updateNode->AppendAttribute('value', 'settings');
			$_xmlRes->XmlRoot->AppendChild($_updateNode);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_ERROR_ACCT_UPDATE, $_xmlRes);
		}
	}

	function DoUpdateContactsSettings()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		if (!$_settings->AllowContacts)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_UPDATE_CONT_SETTINGS, $_xmlRes);
		}

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId);

		$_ContactsPerPage = (int) $_xmlObj->GetParamValueByName('contacts_per_page');

		if ($_account->IsDemo)
		{
			$_SESSION[DEMO_SES][DEMO_S_ContactsPerPage] = $_ContactsPerPage;
		}
		else
		{
			$_account->ContactsPerPage = $_ContactsPerPage;
		}

		$_validate = $_account->ValidateData();
		if (true !== $_validate)
		{
			CXmlProcessing::PrintErrorAndExit($_validate, $_xmlRes);
		}
		elseif ($_account->Update())
		{
			$_contactSettingsNode = new XmlDomNode('contacts_settings');
			$_contactSettingsNode->AppendAttribute('contacts_per_page', $_ContactsPerPage);
			$_xmlRes->XmlRoot->AppendChild($_contactSettingsNode);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_UPDATE_CONT_SETTINGS, $_xmlRes);
		}
	}

	function DoUpdateAccount()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_accountNode = $_xmlObj->XmlRoot->GetChildNodeByTagName('account');
		$_idAcct = $_accountNode->GetAttribute('id', -1);

		CXmlProcessing::CheckAccountAccess($_idAcct, $_xmlRes);

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_idAcct);

		$_oldEmail = $_account->Email;
		$_oldDef = $_account->DefaultAccount;
		
		CXmlProcessing::UpdateAccountFromRequest($_xmlObj->XmlRoot, $_account, true);
		
		if (!$_settings->AllowUsersChangeAccountsDef)
		{
			 $_account->DefaultAccount = $_oldDef;
		}

		if ($_account->MailProtocol == MAILPROTOCOL_WMSERVER)
		{
			 $_account->Email = $_oldEmail;
		}

		$_validate = $_account->ValidateData();
		if (true !== $_validate)
		{
			CXmlProcessing::PrintErrorAndExit($_validate, $_xmlRes);
		}
		else
		{
			if ($_account->AllowChangeSettings && !$_settings->StoreMailsInDb && $_account->Email != $_oldEmail && $_account->MailProtocol != MAILPROTOCOL_WMSERVER)
			{
				$_fs = new FileSystem(INI_DIR.'/mail', strtolower($_oldEmail), $_account->Id);
				if (!$_fs->MoveFolders($_account->Email))
				{
					CXmlProcessing::PrintErrorAndExit(PROC_CANT_UPDATE_ACCT, $_xmlRes);
				}
			}

			$inbox_sync = isset($_accountNode->Attributes['inbox_sync_type']) ? $_accountNode->Attributes['inbox_sync_type'] : false;
			if (!$_account->AllowChangeSettings)
			{
				$inbox_sync = null;
			}

			if (false !== $inbox_sync && $_account->Update($inbox_sync, true))
			{
				$_updateNode = new XmlDomNode('update');
				$_updateNode->AppendAttribute('value', 'account');
				$_xmlRes->XmlRoot->AppendChild($_updateNode);
			}
			else
			{
				if (isset($GLOBALS[ErrorDesc]))
				{
					CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
				}
				else
				{
					CXmlProcessing::PrintErrorAndExit(PROC_CANT_UPDATE_ACCT, $_xmlRes);
				}
			}
		}
	}

	function DoUpdateSignature()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_idAcct = $_xmlObj->GetParamValueByName('id_acct');
		CXmlProcessing::CheckAccountAccess($_idAcct, $_xmlRes);

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_idAcct);

		$_signatureNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('signature');
		$_account->SignatureType = $_signatureNode->GetAttribute('type', $_account->SignatureType);
		$_account->SignatureOptions = $_signatureNode->GetAttribute('opt', $_account->SignatureOptions);
		$_account->Signature = ConvertUtils::WMBackHtmlSpecialChars($_signatureNode->Value);

		$_account->Update(null);

		$_updateNode = new XmlDomNode('update');
		$_updateNode->AppendAttribute('value', 'signature');
		$_xmlRes->XmlRoot->AppendChild($_updateNode);
	}

	function DoUpdateFilter()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_filterNode = $_xmlObj->XmlRoot->GetChildNodeByTagName('filter');
		$_idAcct = $_filterNode->GetAttribute('id_acct', -1);

		CXmlProcessing::CheckAccountAccess($_idAcct, $_xmlRes);

		$_editAccount =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_idAcct);

		$_filter =& CXmlProcessing::GetFilterFromRequest($_xmlObj->XmlRoot);
		if (!$_filter)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_INS_NEW_FILTER, $_xmlRes);
		}
		else if (empty($_filter->Filter))
		{
			CXmlProcessing::PrintErrorAndExit(JS_LANG_WarningEmptyFilter, $_xmlRes);
		}
		else if ($_editAccount->IsDemo || $_dbStorage->UpdateFilter($_filter))
		{
			CXmlProcessing::GetFiltersList($_xmlRes, $_filter->IdAcct);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_INS_NEW_FILTER, $_xmlRes);
		}
	}

	function DoUpdateFilters()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);

		$_filtersNode = $_xmlObj->XmlRoot->GetChildNodeByTagName('filters');
		$_idAcct = $_filtersNode->GetAttribute('id_acct', -1);

		CXmlProcessing::CheckAccountAccess($_idAcct, $_xmlRes);

		$_editAccount =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_idAcct);

		$_success = true;
		if (!$_editAccount->IsDemo)
		{
			for ($_key = count($_filtersNode->Children) - 1; $_key >= 0; $_key--)
			{
				$_filterNode =& $_filtersNode->Children[$_key];
				if (isset($_filterNode->Attributes['status']))
				{
					$_status = $_filterNode->Attributes['status'];
					switch ($_status)
					{
						case 'new':
							$_filter =& CXmlProcessing::GetOneFilterFromRequest($_filterNode, $_idAcct);
							if ($_filter)
							{
								$_success &= $_dbStorage->InsertFilter($_filter);
							}
							else
							{
								$_success = false;
							}
							break;

						case 'removed':
							if (isset($_filterNode->Attributes['id']))
							{
								$_id = (int) $_filterNode->Attributes['id'];
								$_success &= $_dbStorage->DeleteFilter($_id, $_idAcct);
							}
							else
							{
								$_success = false;
							}
							break;

						case 'updated':
							$_filter =& CXmlProcessing::GetOneFilterFromRequest($_filterNode, $_idAcct);
							if ($_filter)
							{
								$_success &= $_dbStorage->UpdateFilter($_filter);
							}
							else
							{
								$_success = false;
							}
							break;
					}
				}
			}
		}

		if ($_success)
		{
			CXmlProcessing::GetFiltersList($_xmlRes, $_idAcct);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(ErrorCantUpdateFilters, $_xmlRes);
		}
	}

	function DoUpdateFolders()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);

		$_acctId = $_xmlObj->GetParamValueByName('id_acct');
		
		CXmlProcessing::CheckAccountAccess($_acctId, $_xmlRes);

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_acctId);

		$_processor = new MailProcessor($_account);

		$_foldersNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('folders');

		$_result = true;

		if (!$_processor->MailStorage->Connect())
		{
			CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
		}

		$_serverFoldersName = array();

		if ($_account->MailProtocol != MAILPROTOCOL_POP3)
		{
			$_tempFolders = $_processor->MailStorage->GetFolders();
			$_serverFolders = $_tempFolders->CreateFolderListFromTree();
			$_serverFoldersArray = $_serverFolders->Instance();

			foreach ($_serverFoldersArray as $_sFolder)
			{
				$_serverFoldersName[] = strtolower($_sFolder->FullName);
			}

			unset($_tempFolders, $_serverFolders, $_serverFoldersArray, $_sFolder);
		}

		$aLSubList = null;
		$log =& CLog::CreateInstance();
		$log->SetEventPrefixByAccount($_account);
		for ($_key = count($_foldersNode->Children) - 1; $_key >= 0; $_key--)
		{
			$_folderNode =& $_foldersNode->Children[$_key];

			if (!ConvertUtils::CheckDefaultWordsFileName($_folderNode->GetChildValueByTagName('name')) ||
					!ConvertUtils::CheckFileName($_folderNode->GetChildValueByTagName('name')))
			{
				CXmlProcessing::PrintErrorAndExit(PROC_CANT_UPD_FLD, $_xmlRes);
			}

			$_newFolderName = ConvertUtils::ConvertEncoding(
				ConvertUtils::ClearFileName(
					ConvertUtils::WMBackHtmlSpecialChars($_folderNode->GetChildValueByTagName('name'))),
				$_account->GetUserCharset(), CPAGE_UTF7_Imap);

			$_newFolderHide = (bool) $_folderNode->GetAttribute('hide', false);
			$_newFolderType = (int) $_folderNode->GetAttribute('type', 0);
			$_newFolderType = ($_newFolderType === 0) ? 10 : $_newFolderType;

			$_fullFolderName = $_folderNode->GetChildValueByTagName('full_name');

			$_folder = new Folder($_acctId, $_folderNode->GetAttribute('id', -1), $_fullFolderName);
			$_processor->GetFolderInfo($_folder);

			$_isRename = false;
			if ($_folder->Name != $_newFolderName)
			{
				$log->WriteLine('personal folder (rename "'.$_folder->FullName.'" => "'.$_newFolderName.'")');
				if (!$_account->IsDemo && null === $aLSubList)
				{
					$aLSubList = $_processor->GetLsubFolders();
				}
				
				$_oldName = $_folder->Name;
				$_folder->Name = $_newFolderName;
				$_validate = $_folder->ValidateData();
				if (true !== $_validate)
				{
					CXmlProcessing::PrintErrorAndExit($_validate, $_xmlRes);
				}
				else
				{
					$_folder->Name = $_oldName;
				}

				$_result &= ($_account->IsDemo) ? true : $_processor->RenameFolder(
					$_folder, $_newFolderName, $_account->Delimiter, $aLSubList);
				
				$log->WriteEvent('User edit personal folder (rename "'.$_folder->FullName.'" => "'.$_newFolderName.'")');
				$_isRename = true;
			}

			if ($_folder->Hide != $_newFolderHide)
			{
				$_folder->Hide = $_newFolderHide;
				$_processor->SetHide($_folder, $_newFolderHide);
			}

			if ($_folder->Type != $_newFolderType)
			{
				$_folder->Type = $_newFolderType;
			}

			$_folder->Name = $_newFolderName;
			$_folder->SyncType = (int) $_folderNode->GetAttribute('sync_type', FOLDERSYNC_DontSync);
			$_folder->FolderOrder = (int) $_folderNode->GetAttribute('fld_order', 0);

			if (!$_isRename && $_account->MailProtocol != MAILPROTOCOL_POP3 &&
					!in_array(strtolower($_folder->FullName), $_serverFoldersName) &&
					$_folder->SyncType != FOLDERSYNC_DontSync)
			{
				$_result &= $_processor->MailStorage->CreateFolder($_folder);
			}

			if ($_result)
			{
				$_result &= ($_account->IsDemo || !USE_DB) ? true : $_processor->DbStorage->UpdateFolder($_folder);
			}
			else
			{
				CXmlProcessing::PrintErrorAndExit(PROC_CANT_UPD_FLD, $_xmlRes);
			}
		}

		if ($_result)
		{
			$_folders =& $_processor->GetFolders();

			$_foldersList = new XmlDomNode('folders_list');
			$_foldersList->AppendAttribute('sync', -1);
			$_foldersList->AppendAttribute('id_acct', $_acctId);
			$_foldersList->AppendAttribute('namespace', $_account->NameSpace);

			CXmlProcessing::GetFoldersTreeXml($_folders, $_foldersList, $_processor);
			$_xmlRes->XmlRoot->AppendChild($_foldersList);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_UPD_FLD, $_xmlRes);
		}
	}

	function DoUpdateContact()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		if (!$_settings->AllowContacts)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_UPDATE_CONT, $_xmlRes);
		}

		$_result = true;

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);
		$contactManager =& ContactCreator::CreateContactStorage($_account, $_settings);
		
		$_addressBookRecord = new AddressBookRecord();

		CXmlProcessing::UpdateContactFromRequest($_xmlObj->XmlRoot, $_addressBookRecord, $_accountId);

		$_validatedError = $_addressBookRecord->validateData();
		if (true !== $_validatedError)
		{
			CXmlProcessing::PrintErrorAndExit($_validatedError, $_xmlRes);
		}

		if ($contactManager->UpdateContact($_addressBookRecord))
		{
			CXmlProcessing::GetContactList($_account, $_settings, $_xmlObj, $_xmlRes);

			$log =& CLog::CreateInstance();
			$log->WriteEvent('User edit PAB (contact)', $_account);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_UPDATE_CONT, $_xmlRes);
		}
	}

	function DoUpdateGroup()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		if (!$_settings->AllowContacts)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_UPDATE_CONT, $_xmlRes);
		}

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);
		$contactManager =& ContactCreator::CreateContactStorage($_account, $_settings);
		
		$_groupNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('group');

		$_group = new AddressGroup();
		$_group->Id = $_groupNode->GetAttribute('id', -1);
		$_group->IdUser = $_account->IdUser;

		$_group->Name = $_groupNode->GetChildValueByTagName('name', true);
		$_group->IsOrganization = (bool) $_groupNode->GetAttribute('organization', false);

		$_group->Email = $_groupNode->GetChildValueByTagName('email', true);
		$_group->Company = $_groupNode->GetChildValueByTagName('company', true);
		$_group->Street = $_groupNode->GetChildValueByTagName('street', true);
		$_group->City = $_groupNode->GetChildValueByTagName('city', true);
		$_group->State = $_groupNode->GetChildValueByTagName('state', true);
		$_group->Zip = $_groupNode->GetChildValueByTagName('zip', true);
		$_group->Country = $_groupNode->GetChildValueByTagName('country', true);
		$_group->Phone = $_groupNode->GetChildValueByTagName('phone', true);
		$_group->Fax = $_groupNode->GetChildValueByTagName('fax', true);
		$_group->Web = $_groupNode->GetChildValueByTagName('web', true);
		
		$_contactsNode =& $_groupNode->GetChildNodeByTagName('contacts');
		$_contactsKeys = array_keys($_contactsNode->Children);
		foreach ($_contactsKeys as $_key)
		{
			$_cc =& $_contactsNode->Children[$_key];
			$_group->ContactsIds[] = $_cc->GetAttribute('id', -1);
			unset($_cc);
		}

		$_result = false;

		$_validatedError = $_group->validateData();

		if (true !== $_validatedError)
		{
			CXmlProcessing::PrintErrorAndExit($_validatedError, $_xmlRes);
		}
		else if ($contactManager->UpdateGroup($_group))
		{
			$_result = true;
			
			$_contactsNode =& $_groupNode->GetChildNodeByTagName('new_contacts');

			$_contactsKeys = array_keys($_contactsNode->Children);
			foreach ($_contactsKeys as $_key)
			{
				$_cc =& $_contactsNode->Children[$_key];
				$_personalNode =& $_cc->GetChildNodeByTagName('personal');

				$_addressBookRecord = new AddressBookRecord();
				$_addressBookRecord->IdUser = $_account->IdUser;
				$_addressBookRecord->HomeEmail = $_personalNode->GetChildValueByTagName('email');
				$_addressBookRecord->PrimaryEmail = PRIMARYEMAIL_Home;
				
				$_result &= $contactManager->CreateContact($_addressBookRecord);
				$_result &= $contactManager->InsertContactToGroup($_addressBookRecord->IdAddress, $_group->Id);
				unset($_cc, $_personalNode, $_addressBookRecord);
			}
		}

		if ($_result)
		{
			CXmlProcessing::GetContactList($_account, $_settings, $_xmlObj, $_xmlRes);

			$log =& CLog::CreateInstance();
			$log->WriteEvent('User edit PAB (group "'.$_group->Name.'")', $_account);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_INS_NEW_CONTS, $_xmlRes);
		}
	}

	function DoGetFoldersBase()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId);
		$_processor = new MailProcessor($_account);
		$_folders =& $_processor->GetFolders();

		if (!$_folders)
		{
			CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
		}

		$_xmlRes->XmlRoot->AppendAttribute('complex', 'folders_base');

		$_sortField = $_account->DefaultOrder / 2;
		$_sortOrder = 0;
		if (ceil($_account->DefaultOrder / 2) != $_sortField)
		{
			$_sortField = ($_account->DefaultOrder - 1) / 2;
			$_sortOrder = 1;
		}

		$_folder = null;
		for ($_i = 0, $_c = $_folders->Count(), $_q = 0; $_i < $_c; $_i++)
		{
			if ($_q >= GETFOLDERBASECOUNT)
			{
				break;
			}

			$_folder =& $_folders->Get($_i);
			if (!$_folder || $_folder->Type === FOLDERTYPE_Inbox)
			{
				continue;
			}
			
			if ($_folder->SyncType == FOLDERSYNC_DirectMode)
			{
				$_processor->GetFolderMessageCount($_folder);
			}

			$_q++;

			$_messageCollection =& $_processor->GetMessageHeaders(1, $_folder);

			if ($_messageCollection)
			{
				CXmlProcessing::GetMessagesList($_xmlRes, $_messageCollection,
					$_account, $_settings, $_processor,
					$_folder, '', 0, 1, $_sortField, $_sortOrder);
			}

			unset($_folder, $_messageCollection);
		}
	}
	
	function DoGetAccountBase()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_acct_id = $_xmlObj->GetParamValueByName('id_acct');
		
		CXmlProcessing::CheckAccountAccess($_acct_id, $_xmlRes);

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_acct_id);

		if (1 === (int) $_xmlObj->GetParamValueByName('change_acct'))
		{
			$_SESSION[ACCOUNT_ID] = $_acct_id;
		}

		$_processor = new MailProcessor($_account);
		$_folders =& $_processor->GetFolders();

		if ($_folders != null)
		{
			$_foldersList = new XmlDomNode('folders_list');
			$_foldersList->AppendAttribute('sync', 0);
			$_foldersList->AppendAttribute('id_acct', $_account->Id);
			$_foldersList->AppendAttribute('namespace', $_account->NameSpace);

			CXmlProcessing::GetFoldersTreeXml($_folders, $_foldersList, $_processor);
			$_xmlRes->XmlRoot->AppendChild($_foldersList);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_FLDS, $_xmlRes);
		}

		$_xmlRes->XmlRoot->AppendAttribute('complex', 'account_base');

		$_folder =& $_folders->GetFolderByType(FOLDERTYPE_Inbox);

		$_sortOrder = 0;
		$_sortField = $_account->DefaultOrder / 2;
		if (ceil($_account->DefaultOrder / 2) != $_sortField)
		{
			$_sortOrder = 1;
			$_sortField = ($_account->DefaultOrder - 1) / 2;
		}

		$_messageCollection =& $_processor->GetMessageHeaders(1, $_folder);

		CXmlProcessing::GetSignature($_xmlRes, $_account);
		
		CXmlProcessing::GetMessagesList($_xmlRes, $_messageCollection,
			$_account, $_settings, $_processor,
			$_folder, '', 0, 1, $_sortField, $_sortOrder);
	}
	
	function DoGetBase()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId);

		$_dbStorage =& DbStorageCreator::CreateDatabaseStorage($_account);

		$_xmlRes->XmlRoot->AppendAttribute('complex', 'base');

		GetBaseProcessingCallBackFunction($_settings, $_xmlRes);

		$_processor = new MailProcessor($_account);

		CXmlProcessing::GetSettingsList($_xmlRes, $_account, $_settings, $_dbStorage, $_processor);
		CXmlProcessing::GetAccountList($_dbStorage, $_xmlRes, $_account, -1);
		CXmlProcessing::GetSignature($_xmlRes, $_account);

		$_syncRes = true;
		$_folders = null;

		if (USE_FOLDER_SYNC_ON_BASE)
		{
			$_syncRes = $_processor->SynchronizeFolders();
		}

		$_folders =& $_processor->GetFolders();
		
		if ($_syncRes && $_folders != null)
		{
			$_foldersList = new XmlDomNode('folders_list');
			$_foldersList->AppendAttribute('sync', 0);
			$_foldersList->AppendAttribute('id_acct', $_accountId);
			$_foldersList->AppendAttribute('namespace', $_account->NameSpace);

			CXmlProcessing::GetFoldersTreeXml($_folders, $_foldersList, $_processor);
			$_xmlRes->XmlRoot->AppendChild($_foldersList);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_FLDS, $_xmlRes);
		}
		
		$_folder =& $_folders->GetFolderByType(FOLDERTYPE_Inbox);

		$_sortOrder = 0;
		$_sortField = $_account->DefaultOrder / 2;
		if (ceil($_account->DefaultOrder / 2) != $_sortField)
		{
			$_sortOrder = 1;
			$_sortField = ($_account->DefaultOrder - 1) / 2;
		}

		$_messageCollection =& $_processor->GetMessageHeaders(1, $_folder);
		
		CXmlProcessing::GetMessagesList($_xmlRes, $_messageCollection,
			$_account, $_settings, $_processor,
			$_folder, '', 0, 1, $_sortField, $_sortOrder);

		
	}
	
	function DoGetMessagesBodies()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, true, false);
		$_processor = new MailProcessor($_account);
		$_startIncCharset = $_account->DefaultIncCharset;

		$_xmlRes->XmlRoot->AppendAttribute('complex', 'messages_bodies');
		/* return true; */

		$_folders = array();
		if (null != $_xmlObj->XmlRoot->Children)
		{
			$_webXmlKeys = array_keys($_xmlObj->XmlRoot->Children);
			foreach ($_webXmlKeys as $_key)
			{
				$_xmlNode =& $_xmlObj->XmlRoot->Children[$_key];
				if ($_xmlNode && $_xmlNode->TagName == 'folder')
				{
					$_folder_id = (int) $_xmlNode->GetAttribute('id', -1);
					if ($_folder_id > 0 && !isset($_folders[$_folder_id]))
					{
						$_folders[$_folder_id] = array('', array());
						if (null != $_xmlNode->Children)
						{
							$_folderKeys = array_keys($_xmlNode->Children);
							foreach ($_folderKeys as $_key)
							{
								$_msgNode =& $_xmlNode->Children[$_key];
								if ($_msgNode)
								{
									if ($_msgNode->TagName == 'message')
									{
										$_msg_id = $_msgNode->GetAttribute('id', -1);
										$_msg_charset = $_msgNode->GetAttribute('charset', -1);
										$_msg_size = $_msgNode->GetAttribute('size', -1);
										$_msg_uid = $_msgNode->GetChildValueByTagName('uid');
										$_folders[$_folder_id][1][] = array($_msg_id, $_msg_uid, $_msg_charset, $_msg_size);
									}
									else if ($_msgNode->TagName == 'full_name')
									{
										$_folders[$_folder_id][0] = $_msgNode->Value;
									}
								}
								unset($_msgNode);
							}
						}
					}
				}
				unset($_xmlNode);
			}
		}

		$_folderArray = array();
		foreach ($_folders as $_f_id => $_array)
		{
			if ($_f_id < 1)
			{
				CXmlProcessing::PrintErrorAndExit(WebMailException, $_xmlRes);
			}

			$_folder = null;
			if (isset($_folderArray[$_f_id]))
			{
				$_folder =& $_folderArray[$_f_id];
			}
			else
			{
				$_folder = new Folder($_accountId, $_f_id, $_array[0]);
				$_processor->GetFolderInfo($_folder);
				$_folderArray[$_f_id] =& $_folder;
			}

			if (!$_folder || ($_account->MailProtocol == MAILPROTOCOL_POP3 && (
					 $_folder->SyncType == FOLDERSYNC_AllHeadersOnly ||
					 $_folder->SyncType == FOLDERSYNC_NewHeadersOnly))
					 )
			{
				continue;
			}
			
			if (is_array($_array[1]) && count($_array[1]) > 0)
			{
				foreach ($_array[1] as $_values)
				{
					if (is_array($_values) && count($_values) > 3)
					{
						$_charsetNum = $_values[2];
						if ($_charsetNum > 0)
						{
							$_processor->_account->DefaultIncCharset = ConvertUtils::GetCodePageName($_charsetNum);
							$GLOBALS[MailInputCharset] = $_processor->_account->DefaultIncCharset;
						}
						else
						{
							$_processor->_account->DefaultIncCharset = $_startIncCharset;
							if (isset($GLOBALS[MailInputCharset]))
							{
								unset($GLOBALS[MailInputCharset]);
							}
						}
						
						$_msgSize = $_values[3];
						$_modeForGet = false;
						if ((int) $_msgSize > 0 && (int) $_msgSize < PRELOADBODYSIZE)
						{
							$_modeForGet = null;
							if ($_processor->_account->MailProtocol == MAILPROTOCOL_IMAP4 && $_folder->Type != FOLDERTYPE_Drafts)
							{
								$_modeForGet = 263;
							}
						}
						else if ($_processor->_account->MailProtocol == MAILPROTOCOL_IMAP4)
						{
							$_modeForGet = false;
							if ($_folder->Type != FOLDERTYPE_Drafts)
							{
								$_modeForGet = 263;
							}
						}
						
						$_message = null;
						if (false !== $_modeForGet)
						{
							$_message =& $_processor->GetMessage($_values[0], $_values[1], $_folder, $_modeForGet, ($_account->MailProtocol == MAILPROTOCOL_POP3));
						}
						
						if (null != $_message && ($_message->Size < PRELOADBODYSIZE || $_processor->_account->MailProtocol == MAILPROTOCOL_IMAP4))
						{
							$_fromObj = new EmailAddress();
							$_fromObj->Parse($_message->GetFromAsString(true));

							$_isFromSave = false;
							if (USE_DB && strlen($_fromObj->Email) > 0)
							{
								$_isFromSave = $_processor->DbStorage->SelectSenderSafetyByEmail($_fromObj->Email, $_account->IdUser);
							}
							
							$_modeForGet = ($_folder->Type == FOLDERTYPE_Drafts) ? 263 + 512 : 263;
							CXmlProcessing::GetMessageNode($_xmlRes, $_message, $_folder, $_processor, $_account, $_settings, $_modeForGet, $_charsetNum, $_isFromSave);
						}

						unset($_message);
					}
				}
			}

			unset($_folder);
		}
	}
	
	function DoGetAccounts()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);
		
		CXmlProcessing::GetAccountList($_dbStorage, $_xmlRes, $_account, -1);
	}
	
	function DoGetAccount()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_acct_id = $_xmlObj->GetParamValueByName('id_acct');
		if (CXmlProcessing::CheckAccountAccess($_acct_id, $_xmlRes))
		{
			$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_acct_id, true, false);
			CXmlProcessing::GetAccount($_xmlRes->XmlRoot, $_account, $_dbStorage);
		}
	}
	
	function DoGetFoldersList()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_idAcct = (int) $_xmlObj->GetParamValueByName('id_acct');
		$_syncType = (int) $_xmlObj->GetParamValueByName('sync');
		$_changeAccount = false;

		if ($_syncType != -1)
		{
			if ($_idAcct != $_SESSION[ACCOUNT_ID] && CXmlProcessing::CheckAccountAccess($_idAcct, $_xmlRes))
			{
				if ($_syncType != 2)
				{
					$_changeAccount = true;
					$_SESSION[ACCOUNT_ID] = $_idAcct;
				}
			}
			else
			{
				$_idAcct = $_SESSION[ACCOUNT_ID];
			}
		}

		$_folders = null;
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_idAcct, false, false);
		$_processor = new MailProcessor($_account);

		$_syncRes = true;
		switch ($_syncType)
		{
			case -1:
			case 0:
			case 1:
				$_folders =& $_processor->GetFolders();
				break;

			case 2:
				$_syncRes = $_processor->SynchronizeFolders();
				if ($_syncRes)
				{
					$_folders =& $_processor->GetFolders();
				}
				break;
		}

		if (!$_syncRes)
		{
			CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
		}
		else if ($_folders != null)
		{
			$_foldersList = new XmlDomNode('folders_list');
			$_foldersList->AppendAttribute('sync', $_syncType);
			$_foldersList->AppendAttribute('id_acct', $_idAcct);
			$_foldersList->AppendAttribute('namespace', $_account->NameSpace);

			CXmlProcessing::GetFoldersTreeXml($_folders, $_foldersList, $_processor);
			$_xmlRes->XmlRoot->AppendChild($_foldersList);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_FLDS, $_xmlRes);
		}
	}
	
	function DoGetMessages()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_acct_id = $_xmlObj->GetParamValueByName('id_acct');
		if (strlen($_acct_id) == 0)
		{
			$_acct_id = $_accountId;
		}

		CXmlProcessing::CheckAccountAccess($_acct_id, $_xmlRes);

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_acct_id, false, false);
		$_processor = new MailProcessor($_account);
		$_page = 1;

		$_folderNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('folder');

		$log =& CLog::CreateInstance();
		
		$_folders = $_folder = null;
		if (isset($_folderNode->Attributes['id']))
		{
			$_folder = new Folder($_account->Id, $_folderNode->Attributes['id'], $_folderNode->GetChildValueByTagName('full_name'));
			if (!USE_DB)
			{
				$_folder->SyncType = FOLDERSYNC_DirectMode;
			}
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_MSG_LIST, $_xmlRes);
		}

		$_searchNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('look_for');

		if (!isset($_searchNode->Attributes['fields']))
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_MSG_LIST, $_xmlRes);
		}

		$_sortField = $_xmlObj->GetParamValueByName('sort_field');
		$_sortOrder = $_xmlObj->GetParamValueByName('sort_order');

		if ($_sortField + $_sortOrder != $_account->DefaultOrder)
		{
			$_account->DefaultOrder = $_sortField + $_sortOrder;
			$_account->UpdateDefaultOrder();
		}

		if ($_searchNode->Value === '' || $_searchNode->Value === null)
		{
			$_processor->GetFolderInfo($_folder);
			$_processor->GetFolderMessageCount($_folder);

			if (ceil($_folder->MessageCount/$_account->MailsPerPage) < (int) $_xmlObj->GetParamValueByName('page'))
			{
				$_page = $_xmlObj->GetParamValueByName('page') - 1;
				$_page = ($_page < 1) ? 1 : $_page;
			}
			else
			{
				$_page = $_xmlObj->GetParamValueByName('page');
			}

			$_messageCollection =& $_processor->GetMessageHeaders($_page, $_folder);
		}
		else
		{
			if ($_folder->IdDb == -1)
			{
				if (USE_DB)
				{
					$_folders =& $_processor->GetFolders();
				}
				else
				{
					$_Allfolders =& $_processor->GetFolders();
					$_folder = $_Allfolders->GetFolderByType(FOLDERTYPE_Inbox);
				}
			}
			else
			{
				$_processor->GetFolderInfo($_folder);

				$_folders = new FolderCollection();
				$_folders->Add($_folder);
			}

			$_page = (int) $_xmlObj->GetParamValueByName('page');

			$log->WriteLine(print_r($_folder, true));

			if ($_account->MailProtocol == MAILPROTOCOL_IMAP4 && $_folder->SyncType == FOLDERSYNC_DirectMode && $_folder->IdDb > 0)
			{
				$_processor->GetFolderInfo($_folder);

				$msgCount = 0;
				$_messageCollection =& $_processor->DmImapSearchMessages(
					$_page,	$_searchNode->Value, $_folder,
					(bool) !$_searchNode->Attributes['fields'], $msgCount);

				$_folder->MessageCount = $msgCount;

			}
			else if ($_account->MailProtocol == MAILPROTOCOL_IMAP4 &&
					(bool) $_searchNode->Attributes['fields'] && $_folder->IdDb > 0 &&
					($_folder->SyncType == FOLDERSYNC_AllHeadersOnly || $_folder->SyncType == FOLDERSYNC_NewHeadersOnly))
			{
				$_processor->GetFolderInfo($_folder);

				$msgCount = 0;
				$_messageCollection =& $_processor->HeadersFullImapSearchMessages(
					$_page,	$_searchNode->Value, $_folder, $msgCount);

				$_folder->MessageCount = $msgCount;
			}
			else
			{
				$_folder->MessageCount = $_processor->SearchMessagesCount(
						ConvertUtils::ConvertEncoding($_searchNode->Value,
							$_account->GetUserCharset(), $_account->DbCharset),
						$_folders, (bool) !$_searchNode->Attributes['fields']);

				$_messageCollection =& $_processor->SearchMessages($_page,
						ConvertUtils::ConvertEncoding($_searchNode->Value,
							$_account->GetUserCharset(), $_account->DbCharset),
						$_folders, (bool) !$_searchNode->Attributes['fields'], $_folder->MessageCount);
			}
		}

		CXmlProcessing::GetMessagesList($_xmlRes, $_messageCollection,
			$_account, $_settings, $_processor,
			$_folder, $_searchNode->Value, $_searchNode->Attributes['fields'],
			$_page, $_sortField, $_sortOrder);

	}
	
	function DoGetMessage()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, true, false);

		$_charsetNum = $_xmlObj->GetParamValueByName('charset');

		if ($_charsetNum > 0)
		{
			$_account->DefaultIncCharset = ConvertUtils::GetCodePageName($_charsetNum);
			$GLOBALS[MailInputCharset] = $_account->DefaultIncCharset;
			$_account->UpdateDefaultIncCharset();
		}

		$_processor = new MailProcessor($_account);

		$_folderNodeRequest =& $_xmlObj->XmlRoot->GetChildNodeByTagName('folder');

		$_folder = null;
		if (isset($_folderNodeRequest->Attributes['id']))
		{
			$_folder = new Folder($_accountId, $_folderNodeRequest->Attributes['id'], $_folderNodeRequest->GetChildValueByTagName('full_name'));
			$_processor->GetFolderInfo($_folder);

			if (!$_folder || $_folder->IdDb < 1)
			{
				CXmlProcessing::PrintErrorAndExit(WebMailException, $_xmlRes);
			}
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(WebMailException, $_xmlRes);
		}

		$_msgId = $_xmlObj->GetParamValueByName('id');
		$_msgUid = $_xmlObj->GetParamTagValueByName('uid');
		$_msgSize = $_xmlObj->GetParamValueByName('size');

		$_msgIdUid = array($_msgId => $_msgUid);

		$_mode = (int) $_xmlObj->GetParamValueByName('mode');
		$_modeForGet = $_mode;
		if (empty($_msgSize) || (int) $_msgSize < BODYSTRUCTURE_MGSSIZE_LIMIT ||	// size
				($_folder && FOLDERTYPE_Drafts == $_folder->Type) ||				// draft
				(($_mode & 8) == 8 || ($_mode & 16) == 16 ||						// forward
					($_mode & 32) == 32 || ($_mode & 64) == 64))
		{
			$_modeForGet = null;
		}

		$_message = null;
		$_message =& $_processor->GetMessage($_msgId, $_msgUid, $_folder, $_modeForGet);

		if (null != $_message)
		{
			if (($_message->Flags & MESSAGEFLAGS_Seen) != MESSAGEFLAGS_Seen)
			{
				$_processor->SetFlag($_msgIdUid, $_folder, MESSAGEFLAGS_Seen, ACTION_Set);
			}

			$_isFromSave = false;
			if (USE_DB && ($_modeForGet === null || (($_modeForGet & 1) == 1)))
			{
				$_fromObj = new EmailAddress();
				$_fromObj->Parse($_message->GetFromAsString(true));

				if ($_fromObj->Email)
				{
					$_isFromSave = $_processor->DbStorage->SelectSenderSafetyByEmail($_fromObj->Email, $_account->IdUser);
				}

				if ($_folder->SyncType != FOLDERSYNC_DirectMode && $_processor->DbStorage->Connect())
				{
					$_processor->DbStorage->UpdateMessageCharset($_msgId, $_charsetNum, $_message);
				}
			}

			CXmlProcessing::GetMessageNode($_xmlRes, $_message, $_folder, $_processor, $_account, $_settings, $_mode, $_charsetNum, $_isFromSave);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
		}
	}
	
	function DoGetAutoresponder()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_id_acct = $_xmlObj->GetParamValueByName('id_acct');
		CXmlProcessing::CheckAccountAccess($_id_acct, $_xmlRes);

		$_editAccount =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_id_acct, false, false);
		$_processor = new MailProcessor($_editAccount);

		$_respArra = $_processor->GetAutoresponder();
		if ($_respArra === false)
		{
			CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
		}

		$_autoresponderNode = new XmlDomNode('autoresponder');

		if (isset($_respArra['s'], $_respArra['b'], $_respArra['e']))
		{
			$_autoresponderNode->AppendAttribute('enable', (bool) $_respArra['e']);
			$_autoresponderNode->AppendAttribute('id_acct', $_id_acct);
			$_autoresponderNode->AppendChild(new XmlDomNode('subject', $_respArra['s'], true));
			$_autoresponderNode->AppendChild(new XmlDomNode('message', $_respArra['b'], true));
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
		}

		$_xmlRes->XmlRoot->AppendChild($_autoresponderNode);
	}

	function DoGetSettings()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);

		$_settingsNode = new XmlDomNode('settings');
		$_settingsNode->AppendAttribute('msgs_per_page', (int) $_account->MailsPerPage);
		$_settingsNode->AppendAttribute('contacts_per_page', (int) $_account->ContactsPerPage);
		$_settingsNode->AppendAttribute('allow_dhtml_editor', (int) $_account->AllowDhtmlEditor);
		$_settingsNode->AppendAttribute('auto_checkmail_interval', (int) $_account->AutoCheckMailInterval);

		if ($_settings->AllowUsersChangeCharset)
		{
			$_settingsNode->AppendAttribute('def_charset_inc', ConvertUtils::GetCodePageNumber($_account->DefaultIncCharset));
			$_settingsNode->AppendAttribute('def_charset_out', ConvertUtils::GetCodePageNumber($_account->DefaultOutCharset));
		}

		if ($_settings->AllowUsersChangeTimeZone)
		{
			$_settingsNode->AppendAttribute('def_timezone', (int) $_account->DefaultTimeZone);
		}

		$_settingsNode->AppendAttribute('view_mode', (int) $_account->ViewMode);

		if ($_settings->AllowUsersChangeSkin)
		{
			$_skinsNode = new XmlDomNode('skins');

			$_skinsList =& FileSystem::GetSkinsList();

			foreach ($_skinsList as $_skin)
			{
				$_skinNode = new XmlDomNode('skin', $_skin, true);
				$_skinNode->AppendAttribute('def', (int) (strtolower($_account->DefaultSkin) == strtolower($_skin)));

				$_skinsNode->AppendChild($_skinNode);
				unset($_skinNode);
			}

			$_settingsNode->AppendChild($_skinsNode);
		}

		if ($_settings->AllowUsersChangeLanguage)
		{
			$_langsNode = new XmlDomNode('langs');

			$_langList =& FileSystem::GetLangList();

			foreach ($_langList as $_lang)
			{
				$_langNode = new XmlDomNode('lang', $_lang, true);
				$_langNode->AppendAttribute('def', (int) (strtolower($_account->DefaultLanguage) == strtolower($_lang)));

				$_langsNode->AppendChild($_langNode);
				unset($_langNode);
			}

			$_settingsNode->AppendChild($_langsNode);
		}

		$_settingsNode->AppendChild(new XmlDomNode('def_date_fmt', $_account->DefaultDateFormat));
		$_settingsNode->AppendAttribute('time_format', $_account->DefaultTimeFormat);

		$_xmlRes->XmlRoot->AppendChild($_settingsNode);
	}

	function DoGetMobileSync()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);

		$_mobileSyncNode = new XmlDomNode('mobile_sync');
		$_mobileSyncNode->AppendAttribute('enable_system', (int) ($_settings->EnableMobileSync && function_exists('mcrypt_encrypt')));

		if ($_settings->EnableMobileSync && function_exists('mcrypt_encrypt'))
		{
			$_mobileSyncNode->AppendChild(new XmlDomNode('url', $_settings->MobileSyncUrl, true));
			$_mobileSyncNode->AppendChild(new XmlDomNode('contact_db', $_settings->MobileSyncContactDataBase, true));
			$_mobileSyncNode->AppendChild(new XmlDomNode('calendar_db', $_settings->MobileSyncCalendarDataBase, true));
			$fnblAcc = $_dbStorage->SelectAccountData($_account->IdUser, false, false, true);
			if ($fnblAcc)
			{
				$_mobileSyncNode->AppendAttribute('enable_account', (int) $fnblAcc->EnableMobileSync);
				$_mobileSyncNode->AppendChild(new XmlDomNode('login', $fnblAcc->Email, true));
			}
		}
		else
		{
			$_mobileSyncNode->AppendAttribute('enable_account', '0');
			$_mobileSyncNode->AppendChild(new XmlDomNode('url', '', true));
			$_mobileSyncNode->AppendChild(new XmlDomNode('contact_db', '', true));
			$_mobileSyncNode->AppendChild(new XmlDomNode('calendar_db', '', true));
			$_mobileSyncNode->AppendChild(new XmlDomNode('login', '', true));
		}

		$_xmlRes->XmlRoot->AppendChild($_mobileSyncNode);
	}

	function DoUpdateMobileSync()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId);

		if ($_settings->EnableMobileSync && function_exists('mcrypt_encrypt'))
		{
			$_mobileSyncReqNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('mobile_sync');

			$_account->EnableMobileSync = (bool) $_mobileSyncReqNode->GetAttribute('enable_account', $_account->EnableMobileSync);

			$_validate = $_account->ValidateData();
			if (true !== $_validate)
			{
				CXmlProcessing::PrintErrorAndExit($_validate, $_xmlRes);
			}
			else if ($_account->Update(null))
			{
				$_updateNode = new XmlDomNode('update');
				$_updateNode->AppendAttribute('value', 'mobile_sync');
				$_xmlRes->XmlRoot->AppendChild($_updateNode);
			}
			else
			{
				CXmlProcessing::PrintErrorAndExit(PROC_ERROR_ACCT_UPDATE, $_xmlRes);
			}
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_ERROR_ACCT_UPDATE, $_xmlRes);
		}
	}

	function DoGetContactsSettings()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		if (!$_settings->AllowContacts)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_SETTINGS, $_xmlRes);
		}

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);

		$_contactSettingsNode = new XmlDomNode('contacts_settings');
		$_contactSettingsNode->AppendAttribute('contacts_per_page', $_account->ContactsPerPage);
		$_xmlRes->XmlRoot->AppendChild($_contactSettingsNode);
	}

	function DoGetXSpam()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);

		$_xSpamNode = new XmlDomNode('x_spam');
		$_xSpamNode->AppendAttribute('value', (int) $_account->XSpam);
		$_xmlRes->XmlRoot->AppendChild($_xSpamNode);
	}

	function DoGetSignature()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_acct_id = $_xmlObj->GetParamValueByName('id_acct');
		CXmlProcessing::CheckAccountAccess($_acct_id, $_xmlRes);

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_acct_id, true, false);
		CXmlProcessing::GetSignature($_xmlRes, $_account);
	}

	function DoGetFilters()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_acct_id = $_xmlObj->GetParamValueByName('id_acct');
		CXmlProcessing::CheckAccountAccess($_acct_id, $_xmlRes);
		CXmlProcessing::GetFiltersList($_xmlRes, $_acct_id);
	}

	function DoGetContactsGroups()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		if (!$_settings->AllowContacts)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_CONTS_FROM_DB, $_xmlRes);
		}
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);
		CXmlProcessing::GetContactList($_account, $_settings, $_xmlObj, $_xmlRes);
	}

	function DoGetContact()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		if (!$_settings->AllowContacts)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_CONT_FROM_DB, $_xmlRes);
		}

		$_idAddress = $_xmlObj->GetParamValueByName('id_addr');

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, true, false);

		$_contactNode = CXmlProcessing::GetContactNodeFromAddressBookRecord($_account, $_settings, $_idAddress);
		if (null != $_contactNode)
		{
			$_xmlRes->XmlRoot->AppendChild($_contactNode);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_CONT_FROM_DB, $_xmlRes);
		}
	}

	function DoGetGroup()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		if (!$_settings->AllowContacts)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_CONTS_FROM_DB, $_xmlRes);
		}
		$_groupId = $_xmlObj->GetParamValueByName('id_group');
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);

		$contactManager =& ContactCreator::CreateContactStorage($_account, $_settings);
		$_group = $contactManager->GetGroup($_groupId);
		if ($_group)
		{
			$_groupNode = new XmlDomNode('group');
			$_groupNode->AppendAttribute('id', $_groupId);
			$_groupNode->AppendAttribute('organization', (int) $_group->IsOrganization);

			$_groupNode->AppendChild(new XmlDomNode('name', $_group->Name, true));
			$_groupNode->AppendChild(new XmlDomNode('email', $_group->Email, true));
			$_groupNode->AppendChild(new XmlDomNode('company', $_group->Company, true));
			$_groupNode->AppendChild(new XmlDomNode('street', $_group->Street, true));
			$_groupNode->AppendChild(new XmlDomNode('city', $_group->City, true));
			$_groupNode->AppendChild(new XmlDomNode('state', $_group->State, true));
			$_groupNode->AppendChild(new XmlDomNode('zip', $_group->Zip, true));
			$_groupNode->AppendChild(new XmlDomNode('country', $_group->Country, true));
			$_groupNode->AppendChild(new XmlDomNode('phone', $_group->Phone, true));
			$_groupNode->AppendChild(new XmlDomNode('fax', $_group->Fax, true));
			$_groupNode->AppendChild(new XmlDomNode('web', $_group->Web, true));

			$_contacts = $contactManager->GetContactsOfGroup($_groupId);

			$_contactsNode = new XmlDomNode('contacts');

			if ($_contacts != null)
			{
				$_contactsKeys = array_keys($_contacts->Instance());
				foreach ($_contactsKeys as $_key)
				{
					$_contact =& $_contacts->Get($_key);

					$_contactNode = new XmlDomNode('contact');
					$_contactNode->AppendAttribute('id', $_contact->Id);
					$_contactNode->AppendChild(new XmlDomNode('fullname', $_contact->Name, true));
					$_contactNode->AppendChild(new XmlDomNode('email', $_contact->Email, true));

					$_contactsNode->AppendChild($_contactNode);
					unset($_contactNode);
				}

				$_groupNode->AppendChild($_contactsNode);
				$_xmlRes->XmlRoot->AppendChild($_groupNode);
			}
			else
			{
				CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_CONTS_FROM_DB, $_xmlRes);
			}
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
		}
	}

	function DoGetGroups()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		if (!$_settings->AllowContacts)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_CONTS_FROM_DB, $_xmlRes);
		}

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);

		$_groupsNode = new XmlDomNode('groups');

		$contactManager =& ContactCreator::CreateContactStorage($_account, $_settings);
		$_groupNames = $contactManager->GetGroups();

		if (is_array($_groupNames))
		{
			foreach ($_groupNames as $_id => $_name)
			{
				$_groupNode = new XmlDomNode('group');
				$_groupNode->AppendAttribute('id', $_id);
				$_groupNode->AppendChild(new XmlDomNode('name', $_name, true));

				$_groupsNode->AppendChild($_groupNode);
				unset($_groupNode);
			}
		}

		$_xmlRes->XmlRoot->AppendChild($_groupsNode);
	}

	function DoGetSettingsList()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, true);
		$_dbStorage =& DbStorageCreator::CreateDatabaseStorage($_account);
		$_processor = new MailProcessor($_account);
		CXmlProcessing::GetSettingsList($_xmlRes, $_account, $_settings, $_dbStorage, $_processor);
	}

	function DoDeleteAccount()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_idAcct = (int) $_xmlObj->GetParamValueByName('id_acct');
		CXmlProcessing::CheckAccountAccess($_idAcct, $_xmlRes);

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);
		$_accountToDelete =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_idAcct, false, false);
		
		if (!$_accountToDelete->AllowChangeSettings)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_DEL_ACCT_BY_ID, $_xmlRes);
		}

		if ($_account->IsDemo || $_accountToDelete->IsDemo || $_accountToDelete->IsInternal)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_DEL_ACCT_BY_ID, $_xmlRes);
		}

		$_resp = User::ProcessDeleteAccount($_idAcct, $_null);
		if ($_resp === false)
		{
			CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
		}
		
		if (isset($_SESSION[ACCOUNT_IDS]))
		{
			unset($_SESSION[ACCOUNT_IDS]);
		}

		$_dbStorage =& DbStorageCreator::CreateDatabaseStorage($_account);
		CXmlProcessing::GetAccountList($_dbStorage, $_xmlRes, $_account, -1, ($_resp === 7) ? -1 : $_SESSION[ACCOUNT_ID]);
	}

	function DoDeleteFilter()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_acctId = $_xmlObj->GetParamValueByName('id_acct');
		CXmlProcessing::CheckAccountAccess($_acctId, $_xmlRes);

		$_editAccount =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_acctId, false, false);

		if ($_editAccount->IsDemo || $_dbStorage->DeleteFilter($_xmlObj->GetParamValueByName('id_filter'), $_acctId))
		{
			CXmlProcessing::GetFiltersList($_xmlRes, $_acctId);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_DEL_FILTER_BY_ID, $_xmlRes);
		}
	}

	function DoDeleteFolders()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_acctId = $_xmlObj->GetParamValueByName('id_acct');
		CXmlProcessing::CheckAccountAccess($_acctId, $_xmlRes);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_acctId, false, false);

		$_processor = new MailProcessor($_account);

		$_foldersNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('folders');

		$_result = true;
		$_foldersKeys = array_keys($_foldersNode->Children);
		foreach ($_foldersKeys as $_key)
		{
			$_fc =& $_foldersNode->Children[$_key];
			if (isset($_fc->Attributes['id']))
			{
				$_folder = new Folder($_acctId, $_fc->Attributes['id'], $_fc->GetChildValueByTagName('full_name'));
			}
			else
			{
				$_result = false;
				unset($_fc);
				break;
			}

			$_processor->GetFolderInfo($_folder);
			$_processor->GetFolderMessageCount($_folder);

			$_childCount = (USE_DB) ? $_processor->DbStorage->GetFolderChildCount($_folder) : 0;

			if ($_account->MailProtocol != MAILPROTOCOL_POP3 &&	($_folder->MessageCount > 0 || $_childCount != 0))
			{
				$_result = false;
			}
			else
			{
				$_result &= ($_account->IsDemo) ? true : $_processor->DeleteFolder($_folder);
				if ($_result && !$_account->IsDemo)
				{
					$log =& CLog::CreateInstance();
					$log->WriteEvent('User delete personal folder ("'.$_folder->FullName.'")', $_account);
				}
			}
			unset($_fc, $_folder);
		}

		if ($_result)
		{
			$_folders =& $_processor->GetFolders();

			$_foldersList = new XmlDomNode('folders_list');
			$_foldersList->AppendAttribute('sync', -1);
			$_foldersList->AppendAttribute('id_acct', $_acctId);
			$_foldersList->AppendAttribute('namespace', $_account->NameSpace);

			CXmlProcessing::GetFoldersTreeXml($_folders, $_foldersList, $_processor);
			$_xmlRes->XmlRoot->AppendChild($_foldersList);

		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_ERROR_DEL_FLD, $_xmlRes);
		}
	}

	function DoDeleteContacts()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		if (!$_settings->AllowContacts)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_DEL_CONT_GROUPS, $_xmlRes);
		}

		$_result = true;
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);
		$contactManager =& ContactCreator::CreateContactStorage($_account, $_settings);

		$log =& CLog::CreateInstance();
		$log->SetEventPrefixByAccount($_account);
		
		$_contactsNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('contacts');
		$_contactsKeys = array_keys($_contactsNode->Children);
		foreach ($_contactsKeys as $_key)
		{
			$_cc =& $_contactsNode->Children[$_key];
			if (isset($_cc->Attributes['id']))
			{
				$_result &= $contactManager->DeleteContact($_cc->Attributes['id']);
				if ($_result)
				{
					$log->WriteEvent('User delete PAB (contact id="'.$_cc->Attributes['id'].'")');
				}
			}
			else
			{
				$_result = false;
			}
			unset($_cc);
		}

		$_groupsNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('groups');
		$_groupsKeys = array_keys($_groupsNode->Children);
		foreach ($_groupsKeys as $_key)
		{
			$_gc =& $_groupsNode->Children[$_key];
			if (isset($_gc->Attributes['id']))
			{
				$_result &= $contactManager->DeleteGroup($_gc->Attributes['id']);
				if ($_result)
				{
					$log->WriteEvent('User delete PAB (group id="'.$_gc->Attributes['id'].'")');
				}
			}
			else
			{
				$_result = false;
			}
			unset($_gc);
		}

		if ($_result)
		{
			CXmlProcessing::GetContactList($_account, $_settings, $_xmlObj, $_xmlRes);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_DEL_CONT_GROUPS, $_xmlRes);
		}
	}

	function DoSendConfirmation()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);

		$_message = null;
		$_message =& CXmlProcessing::CreateConfirmationMessage($_account, $_xmlObj, $_xmlRes);

		$_result = false;
		if ($_message)
		{
			$_message->OriginalMailMessage = $_message->ToMailString(true);
			$_message->Flags |= MESSAGEFLAGS_Seen;
			$_result = CSmtp::SendMail($_settings, $_account, $_message, null, null);
		}

		if ($_result)
		{
			$_updateNode = new XmlDomNode('update');
			$_updateNode->AppendAttribute('value', 'send_confirmation');
			$_xmlRes->XmlRoot->AppendChild($_updateNode);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_SEND_MSG, $_xmlRes);
		}
	}

	function DoSendMessage()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);

		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);
		$_message =& CXmlProcessing::CreateMessage($_account, $_xmlObj, $_xmlRes);

		/* custom class */
		wm_Custom::StaticUseMethod('ChangeMessageBeforeSend', array(&$_message));

		$_processor = new MailProcessor($_account);
		$_folders =& $_processor->GetFolders();
		$_folder =& $_folders->GetFolderByType(FOLDERTYPE_SentItems);

		$_message->OriginalMailMessage = $_message->ToMailString(true);
		$_message->Flags |= MESSAGEFLAGS_Seen;

		$_from =& $_message->GetFrom();

		$_result = true;
		$_needToDelete = ($_message->IdMsg != -1);
		$_idtoDelete = $_message->IdMsg;
		if (CSmtp::SendMail($_settings, $_account, $_message, null, null))
		{
			$_messageNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('message');
			$_attachmentsNode =& $_messageNode->GetChildNodeByTagName('attachments');

			$bSaveInSent = (isset($_messageNode->Attributes['save_mail']) && 1 === (int) $_messageNode->Attributes['save_mail']);

			if ($_processor->DbStorage->Connect())
			{
				$_draftsFolder = null;
				if ($_needToDelete)
				{
					$_draftsFolder =& $_folders->GetFolderByType(FOLDERTYPE_Drafts);
					if ($_draftsFolder)
					{
						if (!$_processor->SaveMessage($_message, $_folder, $_draftsFolder, !$bSaveInSent))
						{
							$_needToDelete = false;
						}
					}
				}
				else
				{
					if ($bSaveInSent)
					{
						if (!$_processor->SaveMessage($_message, $_folder))
						{
							$_needToDelete = false;
						}
					}
				}

				/* suggestion */
				$_mNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('message');
				$_hNode =& $_mNode->GetChildNodeByTagName('headers');
				$_gNode =& $_hNode->GetChildNodeByTagName('groups');
				$_toNode =& $_hNode->GetChildNodeByTagName('to');
				$_ccNode =& $_hNode->GetChildNodeByTagName('cc');
				$_bccNode =& $_hNode->GetChildNodeByTagName('bcc');
				$_emailsString = '';
				$_gids = array();

				if ($_gNode != null && $_gNode->Value != null)
				{
					if (count($_gNode->Children) > 0)
					{
						$_gKeys = array_keys($_gNode->Children);
						foreach ($_gKeys as $_key)
						{
							$_oneGNode =& $_gNode->Children[$_key];
							$_gids[] = isset($_oneGNode->Attributes['id']) ? (int) $_oneGNode->Attributes['id'] : -1;
							unset($_oneGNode);
						}
					}
				}

				if ($_toNode != null && $_toNode->Value != null)
				{
					$_emailsString .= ConvertUtils::WMBackHtmlSpecialChars($_toNode->Value) . ', ';
				}

				if ($_ccNode != null && $_ccNode->Value != null)
				{
					$_emailsString .= ConvertUtils::WMBackHtmlSpecialChars($_ccNode->Value) . ', ';
				}

				if ($_bccNode != null && $_bccNode->Value != null)
				{
					$_emailsString .= ConvertUtils::WMBackHtmlSpecialChars($_bccNode->Value);
				}

				$_emailsString = trim(trim($_emailsString), ',');

				$_emailsCollection = new EmailAddressCollection($_emailsString);

				$_arrEmails = array();

				for($_z = 0, $_lc = $_emailsCollection->Count(); $_z < $_lc; $_z++)
				{
					$_emailObj =& $_emailsCollection->Get($_z);
					if ($_emailObj && trim($_emailObj->Email))
					{
						$_arrEmails[$_emailObj->Email] = trim($_emailObj->DisplayName);
					}
				}

				/* reply */
				if (USE_DB)
				{
					CXmlProcessing::ReplySetFlag($_mNode, $_processor);
				}

				/* update group frequency */
				if (USE_DB)
				{
					$_processor->DbStorage->UpdateGroupsFrequency($_gids);
	
					$_processor->DbStorage->UpdateSuggestTable($_account, $_arrEmails);
				}
				/* end suggestion */

				if ($_needToDelete && $_draftsFolder)
				{
					$_messageIdSet = array($_idtoDelete);
					if ($_account->MailProtocol == MAILPROTOCOL_IMAP4)
					{
						if ($_processor->PurgeFolder($_draftsFolder) && USE_DB)
						{
							$_processor->DbStorage->DeleteMessages($_messageIdSet, false, $_draftsFolder);
						}
					}
					else if (USE_DB)
					{
						$_processor->DbStorage->DeleteMessages($_messageIdSet, false, $_draftsFolder);
					}
				}

				if (USE_DB)
				{
					$_processor->DbStorage->UpdateMailboxSize();
				}
			}

			$_result = true;
		}
		else
		{
			$_result = false;
		}

		if ($_result)
		{
			$_updateNode = new XmlDomNode('update');
			$_updateNode->AppendAttribute('value', 'send_message');
			$_xmlRes->XmlRoot->AppendChild($_updateNode);
		}
		else
		{
			//CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_SEND_MSG, $_xmlRes);
		}
	}

	function DoSaveMessage()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);
		$_message =& CXmlProcessing::CreateMessage($_account, $_xmlObj, $_xmlRes);

		/* suggestion */
		$_mNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('message');
		$_hNode =& $_mNode->GetChildNodeByTagName('headers');
		$_gNode =& $_hNode->GetChildNodeByTagName('groups');
		$_toNode =& $_hNode->GetChildNodeByTagName('to');
		$_ccNode =& $_hNode->GetChildNodeByTagName('cc');
		$_bccNode =& $_hNode->GetChildNodeByTagName('bcc');
		$_emailsString = '';
		$_gids = array();

		if ($_gNode != null)
		{
			$_gKeys = array_keys($_gNode->Children);
			foreach ($_gKeys as $_key)
			{
				$_oneGNode =& $_gNode->Children[$_key];
				$_gids[] = isset($_oneGNode->Attributes['id']) ? (int) $_oneGNode->Attributes['id'] : -1;
				unset($_oneGNode);
			}
		}

		$_result = true;

		$_processor = new MailProcessor($_account);
		if (!$_processor->DbStorage->Connect())
		{
			CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
		}

		/* reply */
		CXmlProcessing::ReplySetFlag($_mNode, $_processor);

		/* update group frequency */
		$_processor->DbStorage->UpdateGroupsFrequency($_gids);

		$_folders =& $_processor->GetFolders();
		$_folder =& $_folders->GetFolderByType(FOLDERTYPE_Drafts);

		$_from =& $_message->GetFrom();
		$_message->OriginalMailMessage = $_message->ToMailString();
		$_message->Flags |= MESSAGEFLAGS_Seen;

		$_messageIdUidSet = array();
		$_messageIdUidSet[$_message->IdMsg] = $_message->Uid;

		$_messageIdSet = null;

		$_isFromDrafts = ($_message->IdMsg != -1);

		if ($_isFromDrafts)
		{
			$_messageIdSet = array($_message->IdMsg);
		}

		if ($_result)
		{
			$_result = ($_isFromDrafts)
				? $_processor->UpdateMessage($_message, $_folder)
				: $_processor->SaveMessage($_message, $_folder);

			$_messageIdUidSet[$_message->IdMsg] = $_message->Uid;

			if ($_result)
			{
				if ($_processor->SetFlags($_messageIdUidSet, $_folder, MESSAGEFLAGS_Seen, ACTION_Set))
				{
					//if ($_messageIdSet !== null && $_account->MailProtocol == MAILPROTOCOL_IMAP4)
					//{
					//	if ($_processor->PurgeFolder($_folder) && USE_DB)
					//	{
					//		$_processor->DbStorage->DeleteMessages($_messageIdSet, false, $_folder);
					//	}
					//}
				}
			}

			if (USE_DB)
			{
				$_processor->DbStorage->UpdateMailboxSize();
			}
		}
		else
		{
			$_result = false;
		}

		if ($_result)
		{
			$_updateNode = new XmlDomNode('update');
			$_updateNode->AppendAttribute('value', 'save_message');
			if ($_message)
			{
				$_updateNode->AppendAttribute('id', $_message->IdMsg);
				$_uidNode = new XmlDomNode('uid', $_message->Uid, true);
				$_updateNode->AppendChild($_uidNode);
			}
			$_xmlRes->XmlRoot->AppendChild($_updateNode);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_SAVE_MSG, $_xmlRes);
		}
	}

	function DoOperationMessagesFunction()
	{
		$_dbStorage = $_settings = $_xmlObj = $_xmlRes = $_accountId = null;
		$this->_initFuncArgs($_dbStorage, $_settings, $_xmlObj, $_xmlRes, $_accountId);
		
		$_account =& CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_accountId, false, false);
		$_processor = new MailProcessor($_account);

		$_messagesRequestNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('messages');
		if (!$_messagesRequestNode)
		{
			CXmlProcessing::PrintErrorAndExit(WebMailException, $_xmlRes);
		}

		$_getmsg = (isset($_messagesRequestNode->Attributes['getmsg']) && $_messagesRequestNode->Attributes['getmsg'] == '1');

		$_folderNodeRequest =& $_messagesRequestNode->GetChildNodeByTagName('folder');

		if (isset($_folderNodeRequest->Attributes['id']))
		{
			$_folder = new Folder($_accountId, $_folderNodeRequest->Attributes['id'],
				ConvertUtils::WMBackHtmlSpecialChars($_folderNodeRequest->GetChildValueByTagName('full_name')));
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(WebMailException, $_xmlRes);
		}

		$_processor->GetFolderInfo($_folder, true);

		$_toFolderNodeRequest =& $_messagesRequestNode->GetChildNodeByTagName('to_folder');

		if (isset($_toFolderNodeRequest->Attributes['id']))
		{
			$_toFolder = new Folder($_accountId, $_toFolderNodeRequest->Attributes['id'],
				ConvertUtils::WMBackHtmlSpecialChars($_toFolderNodeRequest->GetChildValueByTagName('full_name')));
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(WebMailException, $_xmlRes);
		}

		$_processor->GetFolderInfo($_toFolder, true);

		$_operationNode = new XmlDomNode('operation_messages');

		$_toFolderNode = new XmlDomNode('to_folder', $_toFolder->FullName, true);
		$_toFolderNode->AppendAttribute('id', $_toFolder->IdDb);
		$_operationNode->AppendChild($_toFolderNode);

		$_folderNode = new XmlDomNode('folder', $_folder->FullName, true);
		$_folderNode->AppendAttribute('id', $_folder->IdDb);
		$_operationNode->AppendChild($_folderNode);

		$_messagesNode = new XmlDomNode('messages');
		$_messagesNode->AppendAttribute('getmsg', $_getmsg ? '1' : '0');

		$_messageIdUidSet = array();

		$_folders = array();

		$_messagesKeys = array_keys($_messagesRequestNode->Children);
		foreach ($_messagesKeys as $_nodeKey)
		{
			$_messageNode =& $_messagesRequestNode->Children[$_nodeKey];

			if ($_messageNode->TagName != 'message')
			{
				continue;
			}

			if (!isset($_messageNode->Attributes['id']) || !isset($_messageNode->Attributes['charset']) || !isset($_messageNode->Attributes['size']))
			{
				continue;
			}

			$_msgId = $_messageNode->Attributes['id'];
			$_msgCharset = $_messageNode->Attributes['charset'];
			$_msgSize = $_messageNode->Attributes['size'];
			$_msgUid = $_messageNode->GetChildValueByTagName('uid', true);

			$_msgFolder =& $_messageNode->GetChildNodeByTagName('folder');

			if (!isset($_msgFolder->Attributes['id']))
			{
				continue;
			}

			$_msgFolderId = $_msgFolder->Attributes['id'];
			$_folders[$_msgFolderId] = $_msgFolder->GetChildValueByTagName('full_name', true);

			if (!isset($_messageIdUidSet[$_msgFolderId]))
			{
				$_messageIdUidSet[$_msgFolderId] = array();
			}
			$_messageIdUidSet[$_msgFolderId][$_msgId] = $_msgUid;

			$_message = new XmlDomNode('message');
			$_message->AppendAttribute('id', $_msgId);
			$_message->AppendAttribute('charset', $_msgCharset);
			$_message->AppendAttribute('size', $_msgSize);
			$_message->AppendChild(new XmlDomNode('uid', $_msgUid, true));

			$_msgFolderNode = new XmlDomNode('folder', $_folders[$_msgFolderId], true);
			$_msgFolderNode->AppendAttribute('id', $_msgFolderId);

			$_message->AppendChild($_msgFolderNode);

			$_messagesNode->AppendChild($_message);

			unset($_messageNode, $_msgFolder, $_message, $_msgFolderNode);
		}

		$_operationNode->AppendChild($_messagesNode);

		$_errorString = $_typeString = '';

		$_request = $_xmlObj->GetParamValueByName('request');

		switch ($_request)
		{
			case 'mark_all_read':
				$_messageIdUidSet = null;
				if ($_processor->SetFlags($_messageIdUidSet, $_folder, MESSAGEFLAGS_Seen, ACTION_Set))
				{
					$_typeString = 'mark_all_read';
				}
				else
				{
					$_errorString = PROC_CANT_MARK_ALL_MSG_READ;
				}
				break;
			case 'mark_all_unread':
				$_messageIdUidSet = null;
				if ($_processor->SetFlags($_messageIdUidSet, $_folder, MESSAGEFLAGS_Seen, ACTION_Remove))
				{
					$_typeString = 'mark_all_unread';
				}
				else
				{
					$_errorString = PROC_CANT_MARK_ALL_MSG_UNREAD;
				}
				break;
			case 'purge':
				if (!$_settings->Imap4DeleteLikePop3 && $_account->MailProtocol == MAILPROTOCOL_IMAP4)
				{
					if ($_processor->PurgeFolder($_folder))
					{
						$_typeString = 'purge';
					}
					else
					{
						$_errorString = PROC_CANT_PURGE_MSGS;
					}
				}
				else
				{
					if ($_processor->EmptyTrash())
					{
						$_typeString = 'purge';
					}
					else
					{
						$_errorString = PROC_CANT_PURGE_MSGS;
					}
				}
				break;
			case 'clear_spam':
				if ($_processor->EmptySpam())
				{
					$_typeString = 'clear_spam';
				}
				else
				{
					$_errorString = PROC_CANT_PURGE_MSGS;
				}
				break;
		}

		$_deleteFolderAppendError = false;
		$_foldersArray = array();
		foreach ($_folders as $_idFolder => $_fullNameFolder)
		{
			if (isset($_foldersArray[$_idFolder]))
			{
				$_folder =& $_foldersArray[$_idFolder];
			}
			else
			{
				$_folder = new Folder($_accountId, $_idFolder, $_fullNameFolder);
				$_processor->GetFolderInfo($_folder, true);
				$_foldersArray[$_idFolder] =& $_folder;
			}

			switch ($_request)
			{
				case 'no_move_delete':
					if ($_processor->DeleteMessages($_messageIdUidSet[$_idFolder], $_folder, true))
					{
						$_typeString = 'no_move_delete';
					}
					else
					{
						$_errorString = PROC_CANT_DEL_MSGS;
					}
					break;
				case 'delete':
					if ($_processor->DeleteMessages($_messageIdUidSet[$_idFolder], $_folder))
					{
						$_typeString = 'delete';
					}
					else
					{
						if ($_processor->IsMoveError)
						{
							$_typeString = 'delete';
							$_deleteFolderAppendError = true;
						}
						$_errorString = PROC_CANT_DEL_MSGS;
					}
					break;
				case 'undelete':
					if ($_processor->SetFlags($_messageIdUidSet[$_idFolder], $_folder, MESSAGEFLAGS_Deleted, ACTION_Remove))
					{
						$_typeString = 'undelete';
					}
					else
					{
						$_errorString = PROC_CANT_UNDEL_MSGS;
					}
					break;
				case 'mark_read':
					if ($_processor->SetFlags($_messageIdUidSet[$_idFolder], $_folder, MESSAGEFLAGS_Seen, ACTION_Set))
					{
						$_typeString = 'mark_read';
					}
					else
					{
						$_errorString = PROC_CANT_MARK_MSGS_READ;
					}
					break;
				case 'mark_unread':
					if ($_processor->SetFlags($_messageIdUidSet[$_idFolder], $_folder, MESSAGEFLAGS_Seen, ACTION_Remove))
					{
						$_typeString = 'mark_unread';
					}
					else
					{
						$_errorString = PROC_CANT_MARK_MSGS_UNREAD;
					}
					break;
				case 'flag':
					if ($_processor->SetFlags($_messageIdUidSet[$_idFolder], $_folder, MESSAGEFLAGS_Flagged, ACTION_Set))
					{
						$_typeString = 'flag';
					}
					else
					{
						$_errorString = PROC_CANT_SET_MSG_FLAGS;
					}
					break;
				case 'unflag':
					if ($_processor->SetFlags($_messageIdUidSet[$_idFolder], $_folder, MESSAGEFLAGS_Flagged, ACTION_Remove))
					{
						$_typeString = 'unflag';
					}
					else
					{
						$_errorString = PROC_CANT_REMOVE_MSG_FLAGS;
					}
					break;
				case 'copy_to_folder':
					// TODO
					if ($_processor->MoveMessages($_messageIdUidSet[$_idFolder], $_folder, $_toFolder))
					{
						$_typeString = 'copy_to_folder';
					}
					else
					{
						$_errorString = PROC_CANT_CHANGE_MSG_FLD;
					}
					break;
					
				case 'move_to_folder':
					if ($_processor->MoveMessages($_messageIdUidSet[$_idFolder], $_folder, $_toFolder))
					{
						$_typeString = 'move_to_folder';
					}
					else
					{
						$_errorString = PROC_CANT_CHANGE_MSG_FLD;
					}
					break;

				case 'spam':
					if ($_processor->SpamMessages($_messageIdUidSet[$_idFolder], $_folder, true))
					{
						$_typeString = 'spam';
					}
					else
					{
						$_errorString = PROC_CANT_SET_MSG_AS_SPAM;
					}
					break;

				case 'not_spam':
					if ($_processor->SpamMessages($_messageIdUidSet[$_idFolder], $_folder, false))
					{
						$_typeString = 'not_spam';
					}
					else
					{
						$_errorString = PROC_CANT_SET_MSG_AS_NOTSPAM;
					}
					break;
			}

			unset($_folder);

			if (strlen($_errorString) > 0)
			{
				break;
			}
		}

		if (strlen($_errorString) == 0 && strlen($_typeString) > 0)
		{
			$_operationNode->AppendAttribute('type', $_typeString);
			$_xmlRes->XmlRoot->AppendChild($_operationNode);
		}
		else if ($_deleteFolderAppendError)
		{
			$_operationNode->AppendAttribute('type', $_typeString);
			$_messagesNode->AppendAttribute('no_move', '1');
			$_xmlRes->XmlRoot->AppendChild($_operationNode);
		}
		else
		{
			if (strlen($_errorString) == 0)
			{
				$_errorString = WebMailException;
			}
			$_xmlRes->XmlRoot->AppendChild(new XmlDomNode('error', $_errorString, true));
		}
	}

	/* ### main ### */

	/**
	 * @param string $action
	 * @param string $request
	 * @param array $arg
	 */
	function UseMethod($action, $request, &$arg)
	{
		$log =& CLog::CreateInstance();
		$c =& CProcessingSwitch::_createInstance();
		$name = $c->_prepareMethodName($action, $request);
		if ($c->_methodExist($name))
		{
			$c->InitArgs($arg);
			$c->InitLog($log);

			/* custom class */
			wm_Custom::StaticUseMethod('ProcessingBeforeCallFunction', array($name, &$arg));

			$log->WriteLine('Do: CProcessingSwitch->'.$name);
			call_user_func(array(&$c, $name));

			/* custom class */
			wm_Custom::StaticUseMethod('ProcessingAfterCallFunction', array($name, &$arg));
			return true;
		}
		else
		{
			$log->WriteLine('Error: CProcessingSwitch->'.$name.' not exist!', LOG_LEVEL_ERROR);
			return false;
		}
	}

	function Log($string)
	{
		if ($this && $this->_log)
		{
			$this->_log->WriteLine('PROC: '.$string);
		}
	}

	function InitLog(&$log)
	{
		if ($this)
		{
			$this->_log =& $log;
		}
	}

	function InitArgs(&$args)
	{
		if (isset($this))
		{
			$this->_args = $args;
		}
	}
	
	function _initFuncArgs(&$_dbStorage, &$_settings, &$_xmlObj, &$_xmlRes, &$_accountId)
	{
		if ($this->_args)
		{
			$_dbStorage = $this->_args['_dbStorage'];
			$_settings = $this->_args['_settings'];
			$_xmlObj = $this->_args['_xmlRequest'];
			$_xmlRes = $this->_args['_xmlResponse'];
			$_accountId = $this->_args['_accountId'];
		}
	}
		
	/**
	 * @param string $action
	 * @param string $request
	 * return string
	 */	
	function _prepareMethodName($action, $request)
	{
		$name = '';
		if ('operation_messages' == $action)
		{
			$name = 'DoOperationMessagesFunction';
		}
		else
		{
			$name = str_replace('_', ' ', strtolower('Do '.$action.' '.$request));
			$namesArray = array_map('ucfirst', explode(' ', $name));
			$name = implode('', $namesArray);
		}
		return $name;
	}

	/**
	 * @param string $name
	 * return bool
	 */
	function _methodExist($name)
	{
		return (isset($this) && 'Do' === substr($name, 0, 2) && method_exists($this, $name));
	}

	/**
	 * @return CProcessingSwitch
	 */
	function &_createInstance()
	{
		static $instance;
		if (!is_object($instance))
		{
			$instance = new CProcessingSwitch();
		}
		return $instance;
	}
}
