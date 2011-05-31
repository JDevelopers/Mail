<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));
	
	require_once(WM_ROOTPATH.'common/class_mailstorage.php');
	require_once(WM_ROOTPATH.'common/class_commandcreator.php');
	require_once(WM_ROOTPATH.'common/class_filesystem.php');
	require_once(WM_ROOTPATH.'common/class_filters.php');
	require_once(WM_ROOTPATH.'common/class_convertutils.php');

	define('DBTABLE_A_USERS', 'a_users');
	define('DBTABLE_AWM_SETTINGS', 'awm_settings');
	define('DBTABLE_AWM_MESSAGES', 'awm_messages');
	define('DBTABLE_AWM_MESSAGES_BODY', 'awm_messages_body');
	define('DBTABLE_AWM_READS', 'awm_reads');
	define('DBTABLE_AWM_ACCOUNTS', 'awm_accounts');
	define('DBTABLE_AWM_ADDR_GROUPS', 'awm_addr_groups');
	define('DBTABLE_AWM_ADDR_BOOK', 'awm_addr_book');
	define('DBTABLE_AWM_ADDR_GROUPS_CONTACTS', 'awm_addr_groups_contacts');
	define('DBTABLE_AWM_FOLDERS', 'awm_folders');
	define('DBTABLE_AWM_FOLDERS_TREE', 'awm_folders_tree');
	define('DBTABLE_AWM_FILTERS', 'awm_filters');
	define('DBTABLE_AWM_TEMP', 'awm_temp');
	define('DBTABLE_AWM_SENDERS', 'awm_senders');
	define('DBTABLE_AWM_COLUMNS', 'awm_columns');
	define('DBTABLE_AWM_TEMPFILES', 'awm_tempfiles');
	define('DBTABLE_AWM_LOGS', 'awm_logs');
	define('DBTABLE_AWM_MAILALIASES', 'awm_mailaliases');
	define('DBTABLE_AWM_MAILINGLISTS', 'awm_mailinglists');
	
	define('DBTABLE_CAL_USERS_DATA', 'acal_users_data');
	define('DBTABLE_CAL_CALENDARS', 'acal_calendars');
	define('DBTABLE_CAL_EVENTS', 'acal_events');
	define('DBTABLE_CAL_SHARING', 'acal_sharing');
	define('DBTABLE_CAL_PUBLICATIONS', 'acal_publications');
	
	define('DBTABLE_AWM_MESSAGES_INDEX', 'awm_messages_index');
	define('DBTABLE_AWM_MESSAGES_BODY_INDEX', 'awm_messages_body_index');
	
	/**
	 * @abstract
	 */
	class DbStorage extends MailStorage
	{
		/**
		 * @access private
		 * @var short
		 */
		var $_escapeType;
		
		/**
		 * @access protected
		 * @var DbMySql
		 */
		var $_dbConnection;
		
		/**
		 * @access protected
		 * @var MySqlCommandCreator
		 */
		var $_commandCreator;
		
		/**
		 * @param Account $account
		 * @return MailServerStorage
		 */
		function DbStorage(&$account, $settings = null)
		{
			MailStorage::MailStorage($account, $settings);
		}
		
		/**
		 * @return bool
		 */
		function Connect()
		{
			if (!USE_DB || $this->_dbConnection->IsConnected())
			{
				return true;
			}

			if ($this->_dbConnection->Connect())
			{
				@register_shutdown_function(array(&$this, 'Disconnect'));
				return true;
			}
			else 
			{
				setGlobalError(defined('PROC_CANT_LOAD_DB') ? PROC_CANT_LOAD_DB : 'Can\'t connect to database.');
				return false;
			}
		}
		
		/**
		 * @return bool
		 */
		function Disconnect()
		{
			return USE_DB ? $this->_dbConnection->Disconnect() : true;
		}
		
		/**
		 * @param array $emailsString
		 * @return array/bool
		 */	
		function SelectExistEmails(&$account, $emailsArray)
		{
			$returnArray = array();
			
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectExistEmails($account, $emailsArray)))
			{
				return false;
			}
			
			while (false !== ($array = $this->_dbConnection->GetNextArrayRecord()))
			{
				if (is_array($array))
				{
					/* h_email, b_email, other_email */
					if ($array['h_email'] != '')
					{
						$returnArray[] = $array['h_email'];
					}
					if ($array['b_email'] != '')
					{
						$returnArray[] = $array['b_email'];
					}
					if ($array['other_email'] != '')
					{
						$returnArray[] = $array['other_email'];
					}
				}
			}
			return $returnArray;
		}
		
		function TempFilesLoadFile($tempName, $hash)
		{
			if ($this->_dbConnection->Execute($this->_commandCreator->TempFilesLoadFile($this->Account->Id, $hash, $tempName)))
			{
				$row = $this->_dbConnection->GetNextRecord();
				if (false !== $row)
				{
					return $row->file_body;
				}
			}
			return false;
		}
		
		function TempFilesSaveFile($tempName, $hash, $rawbody)
		{
			if ($this->_dbConnection->Execute($this->_commandCreator->TempFilesSaveFile($this->Account->Id, $hash, $tempName, $rawbody)))
			{
				return strlen($rawbody);
			}
			return -1;
		}
		
		function TempFilesIsFileExist($tempName, $hash)
		{
			if ($this->_dbConnection->Execute($this->_commandCreator->TempFilesFileSize($this->Account->Id, $hash, $tempName)))
			{
				$row = $this->_dbConnection->GetNextRecord();
				if (false !== $row)
				{
					return true;
				}
			}
			
			return false;
		}
		
		function TempFilesFileSize($tempName, $hash)
		{
			if ($this->_dbConnection->Execute($this->_commandCreator->TempFilesFileSize($this->Account->Id, $hash, $tempName)))
			{
				$row = $this->_dbConnection->GetNextRecord();
				if (false !== $row)
				{
					return (int) $row->file_size;
				}
			}
			
			return false;
		}
		
		function TempFilesClearAccountCompletely()
		{
			return $this->_dbConnection->Execute($this->_commandCreator->TempFilesClearAccount($this->Account->Id));
		}
		
		function TempFilesClearAccount($hash)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->TempFilesClearAccount($this->Account->Id, $hash));
		}
		
		/**
		 * @return array
		 */
		function GetAllAccountsIds()
		{
			$returnArray = array();
			
			if (!$this->_dbConnection->Execute($this->_commandCreator->GetAllAccountsIds()))
			{
				return false;
			}
			
			while (false !== ($row = $this->_dbConnection->GetNextRecord()))
			{
				$returnArray[] = $row->id_acct;
			}
			
			return $returnArray;
		}
		
		/**
		 * @param	string	$email
		 * @param	int		$idUser
		 * @return	bool
		 */
		function SelectSenderSafetyByEmail($email, $idUser)
		{
			static $safetyCache = array();
			
			if (isset($safetyCache[$email.$idUser]))
			{
				return $safetyCache[$email.$idUser];
			}
			else if ($this->_dbConnection->Execute($this->_commandCreator->SelectSendersByEmail($email, $idUser)))
			{
				$row = $this->_dbConnection->GetNextRecord();
				if ($row)
				{
					$safetyCache[$email.$idUser] = (bool) $row->safety;
					return (bool) $row->safety;
				}
				$safetyCache[$email.$idUser] = false;
			}
			
			return false;
		}
		
		/**
		 * @param	string	$email
		 * @param	bool	$safety
		 * @param	int		$idUser
		 * @return	bool
		 */
		function SetSenders($email, $safety, $idUser)
		{
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectSendersByEmail($email, $idUser)))
			{
				return false;
			}
			
			if ($this->_dbConnection->ResultCount() > 0)
			{
				$row = &$this->_dbConnection->GetNextRecord();
				if (is_object($row) && isset($row->safety) && $row->safety != $safety)
				{
					if (!$this->_dbConnection->Execute($this->_commandCreator->UpdateSenders($email, $safety, $idUser)))
					{
						return false;
					}
				}
			}
			else 
			{
				if (!$this->_dbConnection->Execute($this->_commandCreator->InsertSenders($email, $safety, $idUser)))
				{
					return false;
				}
			}
			
			return true;
		}
		
		/**
		 * @param Account $account
		 * @return bool
		 */
		function InsertAccountData(&$account)
		{
			if (!$this->_dbConnection->Execute($this->_commandCreator->InsertAccount($account)))
			{
				return false;
			}	
			
			$account->Id = $this->_dbConnection->GetLastInsertId();
			
			return true;
		}
		
		/**
		 * @param int $userId
		 * @return Array
		 */
		function &SelectAccounts($userId)
		{
			$outArray = null;
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectAccounts($userId)))
			{
				return $outArray;
			}
			
			$outArray = array();
			
			while (($row = $this->_dbConnection->GetNextRecord()) != false)
			{
				$outArray[$row->id_acct] = array($row->mail_protocol, $row->def_order, $row->use_friendly_nm,
													$row->friendly_nm, $row->email,
													(bool) abs($row->getmail_at_login), (bool) abs($row->def_acct),
													$row->mailbox_size);
			}
			
			$outArray = ConvertUtils::SortAccoutArray($outArray);
			return $outArray;
		}
		
		/**
		 * @param string $email
		 * @return Array
		 */
		function &SelectAccountDataOnlyByEmail($email)
		{
			$resArray = null;
			if ($this->_dbConnection->Execute($this->_commandCreator->SelectAccountDataOnlyByEmail($email)))
			{
				$row = $this->_dbConnection->GetNextRecord();
				if (is_object($row))
				{
					$resArray = array($row->id_acct, $row->mail_inc_pass, $row->def_acct, $row->id_user, $row->mail_inc_login, $row->deleted);
					$this->_dbConnection->FreeResult();
				}
			}
			return $resArray;
		}

		/**
		 * @param string $email
		 * @param string $login
		 * @param bool $onlyDef
		 * @return Array
		 */
		function &SelectAccountDataByLogin($email, $login, $onlyDef = false)
		{
			$resArray = null;

			$result = ($onlyDef)
				? $this->_dbConnection->Execute($this->_commandCreator->SelectDefAccountDataByLogin($email, $login))
				: $this->_dbConnection->Execute($this->_commandCreator->SelectAccountDataByLogin($email, $login));
			
			if ($result)
			{
				$row = $this->_dbConnection->GetNextRecord();
				if (is_object($row))
				{
					$resArray = array($row->id_acct, $row->mail_inc_pass, $row->def_acct, $row->id_user, $row->deleted);
					$this->_dbConnection->FreeResult();
				}
			}
			
			return $resArray;
		}

		/**
		 * @param int $accountId
		 * @param int $newAccountId
		 * @return bool
		 */
		function IsAccountInRing($accountId, $newAccountId)
		{
			if ($accountId == $newAccountId)
			{
				return true;
			}
			
			$result = false;
			if ($this->_dbConnection->Execute($this->_commandCreator->SelectIsAccountInRing($accountId, $newAccountId)))
			{
				$row =& $this->_dbConnection->GetNextRecord();
				if ($row)
				{
					$result = $row->acct_count > 0;
				}
			}
			
			return $result;
		}
		
		/**
		 * @param string $email
		 * @param string $login
		 * @param bool $onlyDef
		 * @return int
		 */
		function SelectAccountsCountByLogin($email, $login, $onlyDef = false, $isAcct = -1)
		{
			$count = 0;
			$result = ($onlyDef)
				? $this->_dbConnection->Execute($this->_commandCreator->SelectDefAccountsCountByLogin($email, $login, $isAcct))
				: $this->_dbConnection->Execute($this->_commandCreator->SelectAccountsCountByLogin($email, $login));
				
			if ($result)
			{
				while (($row = $this->_dbConnection->GetNextRecord()) != false)
				{
					$count = $row->acct_count;
				}
			}
			
			return $count;
		}
		
		/**
		 * @param string $email
		 * @param int $id_user
		 * @return int
		 */
		function GetContactIdByEmail($email, $id_user)
		{
			static $idCache = array();
			
			$id = -1;
			if (isset($idCache[$email.$id_user]))
			{
				$id = $idCache[$email.$id_user];
			} 
			else if ($this->_dbConnection->Execute($this->_commandCreator->GetContactIdByEmail($email, $id_user)))
			{
				$row = $this->_dbConnection->GetNextRecord();
				if ($row)
				{
					$id = $row->id_addr;
				}
				$idCache[$email.$id_user] = $id;
				$this->_dbConnection->FreeResult();
			}
			
			return $id;
		}
		
		/**
		 * @param string $_name
		 * @return CWebMailDomain
		 */
		function &SelectDomainByName($_name)
		{
			$_domain = null;
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectDomainByName($_name)))
			{
				return $_domain;
			}

			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$_domain = new CWebMailDomain();
				$_domain->SetId($row->id_domain);
				$_domain->Init($row->name, $row->mail_protocol, $row->mail_inc_host, $row->mail_inc_port, 
					$row->mail_out_host, $row->mail_out_port, $row->mail_out_auth, $row->is_internal,
					$row->global_addr_book, $row->save_mail);

				$this->_dbConnection->FreeResult();
			}
			
			return $_domain;
		}
		
		/**
		 * @param int $_id
		 * @return CWebMailDomain
		 */
		function &SelectDomainById($_id)
		{
			$_domain = null;
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectDomainById($_id)))
			{
				return $_domain;
			}

			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$_domain = new CWebMailDomain();
				$_domain->SetId($row->id_domain);
				$_domain->Init($row->name, $row->mail_protocol, $row->mail_inc_host, $row->mail_inc_port, 
					$row->mail_out_host, $row->mail_out_port, $row->mail_out_auth, $row->is_internal,
					$row->global_addr_book, $row->save_mail);

				$this->_dbConnection->FreeResult();
			}
			
			return $_domain;
		}

		/**
		 * @return array|false
		 */
		function GetDomainsArray()
		{
			$return = false;
			if ($this->_dbConnection->Execute($this->_commandCreator->GetDomains()))
			{
				$return = array();
				while (($row = $this->_dbConnection->GetNextRecord()) != false)
				{
					$return[(int) $row->id_domain] = array(
						$row->name,
						$row->mail_protocol,
						(bool) $row->is_internal
					);
				}
			}

			return $return;
		}

		/**
		 * @param Settings $settings
		 * @return bool
		 */
		function UpdateSettingsByDomain(&$settings)
		{
			$host = GetCurrentHost();
			if (strlen($host) == 0 || !$settings || !$this->_dbConnection->Execute(
					$this->_commandCreator->GetSettingsByDomainHost($host)))
			{
				return false;
			}
			
			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$settings->WindowTitle = $row->site_name;
				$settings->IncomingMailProtocol = (int) $row->settings_mail_protocol;
				$settings->IncomingMailServer = $row->settings_mail_inc_host;
				$settings->IncomingMailPort = (int) $row->settings_mail_inc_port;
				$settings->OutgoingMailServer = $row->settings_mail_out_host;
				$settings->OutgoingMailPort = (int) $row->settings_mail_out_port;
				$settings->ReqSmtpAuth = (bool)$row->settings_mail_out_auth;
				$settings->AllowDirectMode = (bool) $row->allow_direct_mode;
				$settings->DirectModeIsDefault = (bool) $row->direct_mode_id_def;
				$settings->AttachmentSizeLimit = (int) $row->attachment_size_limit;
				$settings->EnableAttachmentSizeLimit = (bool) $row->allow_attachment_limit;
				$settings->MailboxSizeLimit = (int) $row->mailbox_size_limit;
				$settings->EnableMailboxSizeLimit = (bool) $row->allow_mailbox_limit;
				$settings->TakeImapQuota = (bool) $row->take_quota;
				$settings->AllowUsersChangeEmailSettings = (bool) $row->allow_new_users_change_set;
				$settings->AllowNewUsersRegister = (bool) $row->allow_auto_reg_on_login;
				$settings->AllowUsersAddNewAccounts = (bool) $row->allow_users_add_accounts;
				$settings->AllowUsersChangeAccountsDef = (bool) $row->allow_users_change_account_def;
				$settings->DefaultUserCharset = ConvertUtils::GetCodePageName((int) $row->def_user_charset);
				$settings->AllowUsersChangeCharset = (bool) $row->allow_users_change_charset;
				$settings->DefaultTimeZone = (int) $row->def_user_timezone;
				$settings->AllowUsersChangeTimeZone = (bool) $row->allow_users_change_timezone;
				$settings->MailsPerPage = (int) $row->msgs_per_page;
				$settings->DefaultSkin = $row->skin;
				$settings->AllowUsersChangeSkin = (bool) $row->allow_users_change_skin;
				$settings->DefaultLanguage = $row->lang;
				$settings->AllowUsersChangeLanguage = (bool) $row->allow_users_change_lang;
				$settings->ShowTextLabels = (bool) $row->show_text_labels;
				$settings->AllowAjax = true;
				$settings->AllowDhtmlEditor = (bool) $row->allow_editor;
				$settings->AllowContacts = (bool) $row->allow_contacts;
				$settings->AllowCalendar = (bool) $row->allow_calendar;
				$settings->HideLoginMode = (int) $row->hide_login_mode;
				$settings->DefaultDomainOptional = $row->domain_to_use;
				$settings->AllowLanguageOnLogin = (bool) $row->allow_choosing_lang;
				$settings->AllowAdvancedLogin = (bool) $row->allow_advanced_login;
				$settings->AutomaticCorrectLoginSettings = (bool) $row->allow_auto_detect_and_correct;
				$settings->UseCaptcha = (bool) $row->use_captcha;
				$settings->UseMultipleDomainsSelection = (bool) $row->use_domain_selection;
				$settings->ViewMode = (int) $row->view_mode;
				
				return true;
			}
			
			return false;
		}
		
		/**
		 * @param int $id
		 * @return Account
		 */
		function &SelectAccountData($id, $getSignature = true, $getColumns = true, $accountByUserId = false)
		{
			$null = null;
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectAccountData($id, $accountByUserId)))
			{
				return $null;
			}
			
			$account = new Account();
			
			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$account->Id = (int) $row->id_acct;
				$account->IdUser = (int) $row->id_user;
				$account->IdDomain = (int) $row->id_domain;
				$account->DefaultAccount = (bool) abs($row->def_acct);
				$account->Deleted = (bool) abs($row->deleted);
				$account->Email = $row->email;
				$account->MailProtocol = (int) $row->mail_protocol;
				$account->MailIncHost = $row->mail_inc_host;
				$account->MailIncLogin = $row->mail_inc_login;
				$account->MailIncPort = (int) $row->mail_inc_port;
				$account->MailOutHost = $row->mail_out_host;
				$account->MailOutLogin = $row->mail_out_login;
				$account->MailOutPort = (int) $row->mail_out_port;
				$account->MailOutAuthentication = (int) $row->mail_out_auth;
				$account->FriendlyName = $row->friendly_nm;
				$account->UseFriendlyName = (bool) abs($row->use_friendly_nm);
				$account->DefaultOrder = (int) $row->def_order;
				$account->GetMailAtLogin = (bool) abs($row->getmail_at_login);
				$account->MailMode = (int) $row->mail_mode;
				$account->MailsOnServerDays = (int) $row->mails_on_server_days;
				$account->SignatureType = (int) $row->signature_type;
				$account->SignatureOptions = (int) $row->signature_opt;
				$account->HideContacts = (bool) abs($row->hide_contacts);
				$account->MailsPerPage = ((int) $row->msgs_per_page > 0) ? (int) $row->msgs_per_page : 20;
				$account->Delimiter = $row->delimiter;
				$account->NameSpace = $row->personal_namespace;
				$account->WhiteListing = (bool) abs($row->white_listing);
				$account->XSpam = (bool) abs($row->x_spam);
				$account->LastLogin = (int) $row->last_login;
				$account->LoginsCount = (int) $row->logins_count;
				$account->DefaultSkin = $row->def_skin;
				$account->DefaultLanguage = $row->def_lang;
				$account->DefaultIncCharset = ConvertUtils::GetCodePageName((int)$row->def_charset_inc);
				$account->DefaultOutCharset = ConvertUtils::GetCodePageName((int)$row->def_charset_out);
				$account->DefaultTimeZone = (int) $row->def_timezone;
				
				$account->DefaultDateFormat = CDateTime::GetDateFormatFromBd($row->def_date_fmt);
				$account->DefaultTimeFormat = CDateTime::GetTimeFormatFromBd($row->def_date_fmt);
				
				$account->HideFolders = (bool) abs($row->hide_folders);
				$account->MailboxLimit = GetGoodBigInt($row->mailbox_limit);
				$account->MailboxSize = GetGoodBigInt($row->mailbox_size);
				$account->AllowChangeSettings = (bool) abs($row->allow_change_settings);
				$account->AllowDhtmlEditor = (bool) abs($row->allow_dhtml_editor);
				$account->AllowDirectMode = (bool) abs($row->allow_direct_mode);
				$account->DbCharset = ConvertUtils::GetCodePageName((int) $row->db_charset);
				$account->HorizResizer = (int) $row->horiz_resizer;
				$account->VertResizer = (int) $row->vert_resizer;
				$account->Mark = (int) $row->mark;
				$account->Reply = (int) $row->reply;
				$account->ContactsPerPage = ((int) $row->contacts_per_page > 0) ? (int) $row->contacts_per_page : 20;
				$account->ViewMode = (int) $row->view_mode;				
				$account->MailIncPassword = ConvertUtils::DecodePassword($row->mail_inc_pass, $account);
				$account->MailOutPassword = ConvertUtils::DecodePassword($row->mail_out_pass, $account);
				
				$account->IsMailList = (bool) abs($row->mailing_list);

				$account->ImapQuota = (int) $row->imap_quota;

				$account->Question1 = $row->question_1;
				$account->Answer1 = $row->answer_1;
				$account->Question2 = $row->question_2;
				$account->Answer2 = $row->answer_2;

				$account->AutoCheckMailInterval = (int) $row->auto_checkmail_interval;
				$account->EnableMobileSync = (bool) $row->enable_fnbl_sync;

				$this->_dbConnection->FreeResult();
			}
			else
			{
				$account = $null;
			}
			
			if ($getSignature)
			{
				if (!is_object($account) || !$this->_dbConnection->Execute($this->_commandCreator->SelectSignature($account->Id)))
				{
					return $null;
				}
				
				$row = $this->_dbConnection->GetNextRecord();
				if ($row)
				{
					$account->Signature = $row->signature;
					$this->_dbConnection->FreeResult();
				}
			}
			
			if ($getColumns)
			{
				if (!is_object($account) || !$this->_dbConnection->Execute($this->_commandCreator->SelectAccountColumnsData($account->IdUser)))
				{
					return $null;
				}
				
				while (false !== ($row = $this->_dbConnection->GetNextRecord())) 
				{
					if (!is_object($row))
					{
						continue;
					}
					$account->Columns[(int) $row->id_column] = $row->column_value;
				}
			}

			return $account;
		}
		
		/**
		 * @param string $email
		 * @param string $login
		 * @return Account
		 */
		function &SelectAccountFullDataByLogin($email, $login)
		{
			$null = null;
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectAccountFullDataByLogin($email, $login)))
			{
				return $null;
			}
			
			$account = new Account();
			
			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$account->Id = (int) $row->id_acct;
				$account->IdUser = (int) $row->id_user;
				$account->IdDomain = (int) $row->id_domain;
				$account->DefaultAccount = (bool) $row->def_acct;
				$account->Deleted = (bool) $row->deleted;
				$account->Email = $row->email;
				$account->MailProtocol = (int) $row->mail_protocol;
				$account->MailIncHost = $row->mail_inc_host;
				$account->MailIncLogin = $row->mail_inc_login;
				$account->MailIncPort = (int) $row->mail_inc_port;
				$account->MailOutHost = $row->mail_out_host;
				$account->MailOutLogin = $row->mail_out_login;
				$account->MailOutPort = (int) $row->mail_out_port;
				$account->MailOutAuthentication = (int) $row->mail_out_auth;
				$account->FriendlyName = $row->friendly_nm;
				$account->UseFriendlyName = (bool) $row->use_friendly_nm;
				$account->DefaultOrder = (int) $row->def_order;
				$account->GetMailAtLogin = (bool) $row->getmail_at_login;
				$account->MailMode = (int) $row->mail_mode;
				$account->MailsOnServerDays = (int) $row->mails_on_server_days;
				$account->SignatureType = (int) $row->signature_type;
				$account->SignatureOptions = (int) $row->signature_opt;
				$account->HideContacts = (bool) $row->hide_contacts;
				$account->MailsPerPage = (int) $row->msgs_per_page;
				$account->Delimiter = $row->delimiter;
				$account->NameSpace = $row->personal_namespace;
				$account->WhiteListing = (bool) $row->white_listing;
				$account->XSpam = (bool) $row->x_spam;
				$account->LastLogin = (int) $row->last_login;
				$account->LoginsCount = (int) $row->logins_count;
				$account->DefaultSkin = $row->def_skin;
				$account->DefaultLanguage = $row->def_lang;
				$account->DefaultIncCharset = ConvertUtils::GetCodePageName((int)$row->def_charset_inc);
				$account->DefaultOutCharset = ConvertUtils::GetCodePageName((int)$row->def_charset_out);
				$account->DefaultTimeZone = (int) $row->def_timezone;
				$account->DefaultDateFormat = $row->def_date_fmt;
				$account->HideFolders = (bool) $row->hide_folders;
				$account->MailboxLimit = GetGoodBigInt($row->mailbox_limit);
				$account->MailboxSize = GetGoodBigInt($row->mailbox_size);
				$account->AllowChangeSettings = (bool) $row->allow_change_settings;
				$account->AllowDhtmlEditor = (bool) $row->allow_dhtml_editor;
				$account->AllowDirectMode = (bool) $row->allow_direct_mode;
				$account->DbCharset = ConvertUtils::GetCodePageName((int) $row->db_charset);
				$account->HorizResizer = (int) $row->horiz_resizer;
				$account->VertResizer = (int) $row->vert_resizer;
				$account->Mark = (int) $row->mark;
				$account->Reply = (int) $row->reply;
				$account->ContactsPerPage = (int) $row->contacts_per_page;
				$account->ViewMode = (int) $row->view_mode;
				$account->ImapQuota = (int) $row->imap_quota;
				
				$account->MailIncPassword = ConvertUtils::DecodePassword($row->mail_inc_pass, $account);
				$account->MailOutPassword = ConvertUtils::DecodePassword($row->mail_out_pass, $account);

				$this->_dbConnection->FreeResult();
			}
			else
			{
				$account = $null;
			}
			
			if (!is_object($account) || !$this->_dbConnection->Execute($this->_commandCreator->SelectSignature($account->Id)))
			{
				return $null;
			}
			
			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$account->Signature = $row->signature;
				$this->_dbConnection->FreeResult();
			}
			
			return $account;
		}
		
		/**
		 * @param int $groupId
		 * @return AddressGroup
		 */
		function SelectGroupById($groupId)
		{
			$group = null;

			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectGroupById($groupId)))
			{
				return $group;
			}			
			
			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{			
				$group = new AddressGroup();
				$group->Id = $groupId;
				$group->GroupStrId = $row->group_str_id;
				$group->IdUser = $row->id_user;
				$group->Name = $row->group_nm;
				$group->Email = $row->email;
				$group->Company = $row->company;
				$group->Street = $row->street;
				$group->City = $row->city;
				$group->State = $row->state;
				$group->Zip = $row->zip;
				$group->Country = $row->country;
				$group->Phone = $row->phone;
				$group->Fax = $row->fax;
				$group->Web = $row->web;
				$group->IsOrganization = (bool) $row->organization;
				
				$this->_dbConnection->FreeResult();
			}

			return $group;
		}
		
		/**
		 * @param array $arrIds
		 * @return bool
		 */
		function UpdateGroupsFrequency($arrIds)
		{
			if (count($arrIds) > 0)
			{
				return $this->_dbConnection->Execute($this->_commandCreator->UpdateGroupsFrequency($arrIds));	
			}
			return true;
		}
		
		/**
		 * @param Account $account
		 * @return bool
		 */
		function UpdateAccountData(&$account)
		{
			if (!$this->_dbConnection->Execute($this->_commandCreator->UpdateAccount($account)) ||
				!$this->_dbConnection->Execute($this->_commandCreator->UpdateSettings($account)))
			{
				return false;
			}
			
			return $this->UpdateColumns($account);
		}

		/**
		 * @param Account $account
		 * @return bool
		 */
		function UpdateOnlyAccoutnData($account)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateAccount($account));
		}

		
		/**
		 * @param int $_accountId
		 * @param string $_namespace
		 * @return bool
		 */
		function UpdateNameSpace($_accountId, $_namespace)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateNameSpace($_accountId, $_namespace));
		}

		/**
		 * @param int $_accountId
		 * @param string $_delimiter
		 * @return bool
		 */
		function UpdateDelimiter($_accountId, $_delimiter)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateDelimiter($_accountId, $_delimiter));
		}
		
		/**
		 * @param int $_accountId
		 * @param int $_defOrder
		 * @return bool
		 */
		function UpdateDefaultOrder($_accountId, $_defOrder)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateDefaultOrder($_accountId, $_defOrder));
		}

		/**
	 	 * @param int $_accountId
		 * @param string $_IncPass
		 * @param string $_OutPass
		 * @return bool
		 */
		function UpdateAccountPasswords($_accountId, $_IncPass, $_OutPass)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateAccountPasswords($_accountId, $_IncPass, $_OutPass));
		}


		/**
		 * @param int $_userId
		 * @param int $_limit
		 * @return bool
		 */
		function UpdateMailBoxLimit($_userId, $_limit)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateMailBoxLimit($_userId, $_limit));
		}
		
		/**
		 * @param int $_accountId
		 * @param string $_lang
		 * @return bool
		 */
		function UpdateDefaultLanguage($_userId, $_lang)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateDefaultLanguage($_userId, $_lang));
		}
		
	 	/**
	 	 * @param int $_userId
		 * @param string $_defaultIncCharset
		 * @return bool
		 */
		function UpdateDefaultIncCharset($_userId, $_defaultIncCharset)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateDefaultIncCharset($_userId, $_defaultIncCharset));
		}
		
		/**
		 * @param Account $account
		 * @return bool
		 */
		function UpdateColumns(&$account)
		{
			$existColumns = array();
			if (is_array($account->Columns) && count($account->Columns) > 0)
			{
				if (!$this->_dbConnection->Execute($this->_commandCreator->SelectAccountColumnsData($account->IdUser)))
				{
					return false;
				}
				else 
				{
					while (false !== ($row = $this->_dbConnection->GetNextRecord()))
					{
						if (is_object($row))
						{
							$existColumns[(int) $row->id_column] = $row->column_value;
						}
					}
				}
				
				$cnt = count($existColumns);
				foreach ($account->Columns As $id_column => $colun_value)
				{
					if ($cnt > 0)
					{
						if (isset($existColumns[$id_column]))
						{
							if ($existColumns[$id_column] != $colun_value)
							{
								$result = $this->_dbConnection->Execute($this->_commandCreator->UpdateColumnData($account->IdUser, $id_column, $colun_value));
								if (!$result)
								{
									return false;
								}
							}
						}
						else
						{
							$result = $this->_dbConnection->Execute($this->_commandCreator->InsertColumnData($account->IdUser, $id_column, $colun_value));
							if (!$result)
							{
								return false;
							}							
						}
					}
					else 
					{
						$result = $this->_dbConnection->Execute($this->_commandCreator->InsertColumnData($account->IdUser, $id_column, $colun_value));
						if (!$result)
						{
							return false;
						}	
					}
				}
			}
			return true;
		}
		
		
		/**
		 * @param Array $messageIndexSet
		 * @param Boolean $indexAsUid
		 * @param Folder $folder
		 * @param Int $flags
		 * @param Account $account
		 * @return unknown
		 */
		function UpdateMessageFlags($messageIndexSet, $indexAsUid, &$folder, $flags, &$account)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateMessageFlags($messageIndexSet, $indexAsUid, $folder, $flags, $account));					
		}
	
		/**
		 * @param int $userId
		 * @return bool
		 */
		function UpdateLastLoginAndLoginsCount($userId)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateLastLoginAndLoginsCount($userId));
		}

		/**
		 * @param int $idAcct
		 * @return bool
		 */
		function DeleteOnlyAccountData($idAcct)
		{
			$sql = 'DELETE FROM %sawm_accounts WHERE id_acct = %d';
			$query = sprintf($sql, $this->_settings->DbPrefix, $idAcct);
			return $this->_dbConnection->Execute($query);
		}
		
		/**
		 * @param int $id
		 * @return bool
		 */
		function DeleteAccountData($id,$email=NULL)
		{
			$count = 0;
			
			if (!$this->_dbConnection->Execute($this->_commandCreator->CountAccounts($id)))
			{
				return false;
			}
			
			while (false !== ($row = $this->_dbConnection->GetNextRecord()))
			{
				$count = $row->count;
				$id_user = $row->id_user;
			}
			
			$result = true;

			if ($count > 0)
			{
				$sql = 'DELETE FROM %sawm_accounts WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $id);
				$result = $this->_dbConnection->Execute($query);
				
				$sql = 'DELETE FROM %sawm_messages WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $id);
				$result &= $this->_dbConnection->Execute($query);
				
				$sql = 'DELETE FROM %sawm_messages_body WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $id);
				$result &= $this->_dbConnection->Execute($query);
	
				$sql = 'DELETE FROM %sawm_filters WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $id);
				$result &= $this->_dbConnection->Execute($query);
				
				$sql = 'DELETE FROM %sawm_reads WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $id);
				$result &= $this->_dbConnection->Execute($query);
			
				$result &= $this->_dbConnection->Execute($this->_commandCreator->DeleteFolderTreeById($id));
				
				$sql = 'DELETE FROM %sawm_folders WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $id);
				$result &= $this->_dbConnection->Execute($query);
			}
			
			/* last account */
			if ($count == 1)
			{
				$sql = 'DELETE FROM %sawm_addr_book WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $id_user);
				$result &= $this->_dbConnection->Execute($query);
				
				$sql = 'DELETE FROM %sawm_settings WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $id_user);
				$result &= $this->_dbConnection->Execute($query);
				
				/* contacts */
				$result &= $this->_dbConnection->Execute($this->_commandCreator->DeleteAddrGroupsContactsById($id_user));

				$sql = 'DELETE FROM %sawm_addr_groups WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $id_user);
				$result &= $this->_dbConnection->Execute($query);
				
				$sql = 'DELETE FROM %sawm_columns WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $id_user);
				$result &= $this->_dbConnection->Execute($query);
				
				$sql = 'DELETE FROM %sawm_senders WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $id_user);
				$result &= $this->_dbConnection->Execute($query);
				
				$sql = 'DELETE FROM %sa_users WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix , $id_user);
				$result &= $this->_dbConnection->Execute($query);
				
				/* calendar */
				
			}
			
			return $result;
		}
		
		/**
		 * @param int $id_user
		 * @return bool
		 */
		function DeleteSettingsData($id_user)
		{
			$sql = 'DELETE FROM %sawm_settings WHERE id_user = %d';
			$query = sprintf($sql, $this->_settings->DbPrefix, $id_user);
			$result = $this->_dbConnection->Execute($query);
			
			$sql = 'DELETE FROM %sa_users WHERE id_user = %d';
			$query = sprintf($sql, $this->_settings->DbPrefix, $id_user);
			$result &= $this->_dbConnection->Execute($query);
			return $result;
		}
		
		function IsSettingsExists($id_user)
		{
			if (!$this->_dbConnection->Execute($this->_commandCreator->IsSettingsExists($id_user)))
			{
				return false;
			}
			$cnt = 0;
			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$cnt = $row->cnt;
			}

			return ($cnt > 0);
		}
		
		/**
		 * @param User $user
		 * @return bool
		 */
		function InsertUserData(&$user)
		{
			if (!$this->_dbConnection->Execute($this->_commandCreator->InsertUserData($user->Deleted)))
			{
				return false;
			}
			
			$user->Id = $this->_dbConnection->GetLastInsertId();

			return true;
		}

		/**
		 * @param int $id
		 * @return bool
		 */
		function EraseUserData($id)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->EraseUserData($id));
		}

		/**
		 * @param int $id
		 * @return bool
		 */
		function DeleteUserData($id)
		{
			$sql = 'UPDATE %sa_users SET deleted = 1 WHERE id_user = %d';
			$query = sprintf($sql, $this->_settings->DbPrefix, $id);

			return $this->_dbConnection->Execute($query);
		}

		/**
		 * @param Account $account
		 * @return bool
		 */
		function InsertSettings(&$account)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->InsertSettings($account));
		}
		
		/**
		 * @param FolderCollection $folders
		 * @return bool
		 */
		function CreateFolders(&$folders)
		{
			$result = true;
			if ($folders == null)
			{
				return $result;
			}
			
			for ($i = 0, $count = $folders->Count(); $i < $count; $i++)
			{
				$folder =& $folders->Get($i);

				$result &= $this->CreateFolder($folder);
				
				if (!is_null($folder->SubFolders))
				{
					for ($j = 0, $cc = $folder->SubFolders->Count(); $j < $cc; $j++)
					{
						$subFolder =& $folder->SubFolders->Get($j);
						$subFolder->IdParent = $folder->IdDb;
						unset($subFolder);
					}
					$result &= $this->CreateFolders($folder->SubFolders);
				}
				
				unset($folder);
			}

			return $result;										
		}
		
		/**
		 * @return FolderCollection
		 */
		function &GetFolders()
		{
			if (!$this->_dbConnection->Execute($this->_commandCreator->GetFolders($this->Account->Id)))
			{
				$null = null;
				return $null;
			}
			
			$folders = array();
			while (false !== ($row = $this->_dbConnection->GetNextRecord()))
			{
				$folder = new Folder($this->Account->Id, (int) $row->id_folder,
										substr($row->full_path, 0, -1), substr($row->name, 0, -1));
										
				$folder->IdParent = $row->id_parent;
				$folder->Type = (int) $row->type;
				$folder->SyncType = (int) $row->sync_type;
				$folder->Hide = (bool) abs($row->hide);
				$folder->FolderOrder = (int) $row->fld_order;
				$folder->MessageCount = (int) $row->message_count;
				$folder->UnreadMessageCount = (int) $row->unread_message_count;
				$folder->Size = GetGoodBigInt($row->folder_size);
				$folder->Level = (int) $row->level;
				$folders[] =& $folder;
				unset($folder);
			}

			$folderCollection = new FolderCollection();
			$this->_addLevelToFolderTree($folderCollection, $folders);
			
			/* custom class */
			wm_Custom::StaticUseMethod('ChangeDbFoldersAfterGet', array(&$folderCollection));
						
			return $folderCollection;
		}
		
		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function DeleteFolder(&$folder)
		{
			$result = true;
			$result &= $this->_dbConnection->Execute($this->_commandCreator->DeleteFolder($folder));
			$result &= $this->_dbConnection->Execute($this->_commandCreator->DeleteFolderTree($folder));
			return $result;
		}

		/**
		 * @param Folder $folder
		 * @param string $newName
		 * @return bool
		 */
		function RenameFolder(&$folder, $newName)
		{
			$result = $this->_dbConnection->Execute($this->_commandCreator->RenameFolder($folder, $newName));
			
			/*
			$newSubPath = substr($folder->FullName, 0,
						strrpos(trim($folder->FullName, $this->Account->Delimiter), $this->Account->Delimiter));
			$newSubPath .= $newName;
			*/
			
			$foldersId = array();
			if ($this->_dbConnection->Execute($this->_commandCreator->SelectSubFoldersId($folder)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$foldersId[] = $row->id_folder;
				}
			}
			
			if (count($foldersId) > 0)
			{
				$result &= $this->_dbConnection->Execute($this->_commandCreator->RenameSubFoldersPath($folder, $foldersId, $newName));
			}
			
			return $result; 
		}
		
		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function UpdateFolder(&$folder)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateFolder($folder));
		}
		
		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function GetFolderMessageCount(&$folder)
		{
		
			if (!$this->_dbConnection->Execute($this->_commandCreator->GetFolderMessageCountAll($folder)))
			{
				return false;
			}
			
			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$folder->MessageCount = ($row->message_count > 0) ? $row->message_count : 0;
			}
			else 
			{
				$folder->MessageCount = 0;
			}
			
			if (!$this->_dbConnection->Execute($this->_commandCreator->GetFolderMessageCountUnread($folder)))
			{
				return false;
			}
			
			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$folder->UnreadMessageCount = ($row->unread_message_count > 0) ? $row->unread_message_count : 0;
			}
			else 
			{
				$folder->UnreadMessageCount = 0;
			}
			
			return true;
		}
		
		/**
		 * @param int $id
		 * @param int $id_acct
		 * @return string|false
		 */
		function GetFolderFullName($id, $id_acct)
		{
			if ($this->_dbConnection->Execute($this->_commandCreator->GetFolderFullName($id, $id_acct)))
			{
				$row = $this->_dbConnection->GetNextRecord();
				if ($row)
				{
					return substr($row->full_path, 0, -1);
				}
			}
			return false;
		}
		
		/**
		 * @param Folder $folder
		 * @param bool $useCache = false
		 */
		function GetFolderInfo(&$folder, $useCache = false)
		{
			if ($folder)
			{
				$row = null;
				$_sql = $this->_commandCreator->GetFolderInfo($folder);
				
				$_cacher =& CObjectCache::CreateInstance();
				if ($useCache && $_cacher->Has('sql='.$_sql))
				{
					$row =& $_cacher->Get('sql='.$_sql);
				}
				else if ($this->_dbConnection->Execute($_sql))
				{
					$row = $this->_dbConnection->GetNextRecord();
					@$this->_dbConnection->FreeResult();
					$_cacher->Set('sql='.$_sql, $row);
				}
				
				if ($row)
				{
					$folder->FullName = substr($row->full_path, 0, -1);
					$folder->Name = substr($row->name, 0, -1);
					$folder->Type = $row->type;
					$folder->SyncType = $row->sync_type;
					$folder->Hide = (bool) abs($row->hide);
					$folder->FolderOrder = (int) $row->fld_order;
					$folder->IdParent = (int) $row->id_parent;
					return true;
				}
			}
			return false;
		}
		
		/**
		 * @param Folder $folder
		 * @return int
		 */
		function GetFolderChildCount(&$folder)
		{
			$result = -1;
			if ($this->_dbConnection->Execute($this->_commandCreator->GetFolderChildCount($folder)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$result = ($row->child_count != null) ? $row->child_count : 0;
				}
			}

			return $result;
		}

		/**
		 * @param short $type
		 * @return short
		 */
		function GetFolderSyncType($type)
		{
			$result = -1;
			if (!$this->_dbConnection->Execute($this->_commandCreator->GetFolderSyncType($this->Account->Id, $type)))
			{
				return $result;
			}
			
			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$result = $row->sync_type;
			}
			return $result;
		}

		/**
		 * @param short $type
		 * @return short
		 */
		function GetFolderSyncTypeByIdAcct($idAcct, $type)
		{
			$result = -1;
			if (!$this->_dbConnection->Execute($this->_commandCreator->GetFolderSyncType($idAcct, $type)))
			{
				return $result;
			}

			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$result = $row->sync_type;
			}
			return $result;
		}
		
		/**
		 * @access private
		 * @param FolderCollection $folderCollection
		 * @param Array $folders
		 * @param string $rootPrefix optional
		 */
		function _addLevelToFolderTree(&$folderCollection, &$folders, $rootPrefix = '', $isToFolder = false)
		{
			$prefixLen = strlen($rootPrefix);
			$foldersCount = count($folders);
			for ($i = 0; $i < $foldersCount; $i++)
			{
				$folderFullName = $folders[$i]->FullName;
				if ($rootPrefix != $folderFullName && strlen($folderFullName) > $prefixLen &&
					substr($folderFullName, 0, $prefixLen) == $rootPrefix &&
					strpos($folderFullName, $this->Account->Delimiter, $prefixLen + 1) === false)
				{
					$folderObj =& $folders[$i];
					$isTo = ($isToFolder || $folderObj->Type == FOLDERTYPE_Drafts || $folderObj->Type == FOLDERTYPE_SentItems);
					
					$folderObj->ToFolder = $isTo;
					$folderCollection->Add($folderObj);
					
					$newCollection = new FolderCollection();
					$this->_addLevelToFolderTree($newCollection, $folders, $folderFullName.$this->Account->Delimiter, $isTo);
					if ($newCollection->Count() > 0)
					{
						$folderObj->SubFolders = $newCollection;
					}
					unset($folderObj, $newCollection);
				}
			}
		}
		
		/**
		 * @param WebMailMessage $message
		 * @param Folder $folder
		 * @param bool $downloaded
		 * @return bool
		 */
		function SaveMessageHeader(&$message, &$folder, $downloaded, $_id_msg = null)
		{
			if (null === $_id_msg)
			{
				$_id_msg = $this->SelectLastIdMsg();
			}
			
			$message->IdMsg = ($_id_msg) ? $_id_msg : 1; 
			$result = $this->_dbConnection->Execute($this->_commandCreator->SaveMessageHeader($message, $folder, $downloaded, $this->Account));
			
			$message->IdDb = $this->_dbConnection->GetLastInsertId();

			return $result;
		}
		
		/**
		 * @param WebMailMessage $message
		 * @param Folder $folder
		 * @return bool
		 */
		function UpdateMessageHeader(&$message, &$folder)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateMessageHeader($message, $folder, $this->Account));
		}
		
		/**
		 * @param WebMailMessageCollection $messages
		 * @param Folder $folder
		 * @param bool $downloaded
		 * @return bool
		 */
		function SaveMessageHeaders(&$messages, &$folder, $downloaded)
		{
			$result = true;
			for ($i = 0, $count = $messages->Count(); $i < $count; $i++)
			{
				$msg =& $messages->Get($i);
				$result &= $this->SaveMessageHeader($msg, $folder, $downloaded);
				unset($msg);
			}
			return $result;
		}
		
		/**
		 * @param WebMailMessage $message
		 * @param Folder $folder
		 * @return int|false
		 */
		function MessageSize(&$message, &$folder)
		{
			$result = -1;
			if (!$this->_dbConnection->Execute($this->_commandCreator->GetMessageSize($message, $folder, $this->Account->Id)))
			{
				return false;
			}
			
			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$result = $row->size;
			}
			
			$this->_dbConnection->FreeResult();
			
			return $result;
		}
		
		/**
		 * @param WebMailMessage $message
		 * @param Folder $folder
		 * @return bool
		 */
		function UpdateMessage(&$message, &$folder)
		{
			if (!$this->UpdateMessageHeader($message, $folder, true))
			{
				return false;
			}
			
			$result = true;
			
			if ($this->_settings->StoreMailsInDb)
			{
				$result = $this->_dbConnection->Execute($this->_commandCreator->UpdateBody($message, $this->Account->Id));
			}
			else
			{
				$fs = new FileSystem(INI_DIR.'/mail', strtolower($this->Account->Email), $this->Account->Id);
				$result = $fs->UpdateMessage($message, $folder);
			}
			
			if (!$result)
			{
				setGlobalError(PROC_CANT_SAVE_MSG);
			}
			
			return $result;
		}
		
		/**
		 * @param WebMailMessage $message
		 * @param Folder $folder
		 * @return bool
		 */
		function SaveMessage(&$message, &$folder)
		{
			if (!$this->SaveMessageHeader($message, $folder, true))
			{
				return false;
			}
			
			$result = true;
			
			if ($this->_settings->StoreMailsInDb)
			{
				/* save body */
				$result = $this->_dbConnection->Execute($this->_commandCreator->SaveBody($message, $this->Account->Id));
			}
			else
			{
				$fs = new FileSystem(INI_DIR.'/mail', strtolower($this->Account->Email), $this->Account->Id);
				$result = $fs->SaveMessage($message, $folder);
			}
			
			if (!$result)
			{
				setGlobalError(PROC_CANT_SAVE_MSG);
				$tempArray = array($message->IdMsg);
				$this->DeleteMessages($tempArray, false, $folder);
			}
			
			return $result;
		}
		
		/**
		 * @param WebMailMessageCollection $messages
		 * @param Folder $folder
		 * @return bool
		 */
		function SaveMessages(&$messages, &$folder)
		{
			$result = true;
			for ($i = 0, $count = $messages->Count(); $i < $count; $i++)
			{
				$mess =& $messages->Get($i);
				if ($mess) 
				{
					$result &= $this->SaveMessage($mess, $folder);
				}
				else 
				{
					$result = false;
				}
			}
			return $result;
		}

		/**
		 *
		 * @param array $intUids
		 * @param Folder $folder
		 * @return WebMailMessageCollection
		 */
		function &LoadMessageHeadersByIntUids($intUids, $folder)
		{
			$mailCollection = $msg = null;
			if (!$this->_dbConnection->Execute($this->_commandCreator->LoadMessageHeadersByIntUids($intUids, $folder, $this->Account)))
			{
				return $mailCollection;
			}

			$mailCollection = new WebMailMessageCollection();
			$pre_array = array();
			
			while (false !== ($row = $this->_dbConnection->GetNextRecord()))
			{
				$msg =& $this->_rowToWebMailMessage($row);
				$pre_array[$row->uid] =& $msg;
				unset($msg);
			}

			foreach ($intUids as $uid)
			{
				if (isset($pre_array[$uid]))
				{
					$mailCollection->Add($pre_array[$uid]);
				}
			}

			unset($pre_array);

			$this->_dbConnection->FreeResult();

			return $mailCollection;
		}
		
		/**
		 * @param int $pageNumber
		 * @param Folder $folder
		 * @return WebMailMessageCollection
		 */
		function &LoadMessageHeaders($pageNumber, &$folder)
		{
			$mailCollection = null;
			if (!$this->_dbConnection->Execute($this->_commandCreator->LoadMessageHeaders($pageNumber, $folder, $this->Account)))
			{
				return $mailCollection;
			}
		
			$mailCollection = new WebMailMessageCollection();
			
			while (false !== ($row = $this->_dbConnection->GetNextRecord()))
			{
				$msg =& $this->_rowToWebMailMessage($row);
				$mailCollection->Add($msg);
				unset($msg);
			}
			
			$this->_dbConnection->FreeResult();

			return $mailCollection;	
		}

		/**
		 * @param obj $row
		 * return WebMailMessage|null
		 */
		function &_rowToWebMailMessage($row)
		{
			$msg = null;
			if ($row)
			{
				$msg = new WebMailMessage();
				$msg->SetFromAsString($row->from_msg);
				$msg->SetToAsString($row->to_msg);
				$msg->SetCcAsString($row->cc_msg);
				$msg->SetBccAsString($row->bcc_msg);

				$date = new CDateTime();
				$date->SetFromANSI($row->nmsg_date);
				$date->TimeStamp += $date->GetServerTimeZoneOffset();
				$msg->SetDate($date);

				$msg->SetSubject($row->subject);

				$msg->IdMsg = $row->id_msg;
				$msg->IdFolder = $row->id_folder_db;
				$msg->Uid = $row->uid;
				$msg->Size = $row->size;
				$msg->DbPriority = $row->priority;
				$msg->DbXSpam = (bool) abs($row->x_spam);

				$msg->DbHasAttachments = $row->attachments;

				$msg->Sensitivity = $row->sensitivity;

				$msg->Flags = 0;

				if ($row->seen)
				{
					$msg->Flags |= MESSAGEFLAGS_Seen;
				}
				if ($row->flagged)
				{
					$msg->Flags |= MESSAGEFLAGS_Flagged;
				}
				if ($row->deleted)
				{
					$msg->Flags |= MESSAGEFLAGS_Deleted;
				}
				if ($row->replied)
				{
					$msg->Flags |= MESSAGEFLAGS_Answered;
				}
				if ($row->forwarded)
				{
					$msg->Flags |= MESSAGEFLAGS_Forwarded;
				}
				if ($row->grayed)
				{
					$msg->Flags |= MESSAGEFLAGS_Grayed;
				}

				$msg->Charset = $row->charset;
			}
			
			return $msg;
		}
		
		function PreLoadMessagesFromDB($messageIndexSet, $indexAsUid, &$folder)
		{
			$_preData = array();
			if (!$this->_dbConnection->Execute($this->_commandCreator->PreLoadMessagesFromDB($messageIndexSet, $indexAsUid, $folder, $this->Account)))
			{
				return null;
			}
			
			while (false !== ($row = $this->_dbConnection->GetNextRecord()))
			{
				$_preData[$row->id_msg] = array($row->uid, $row->priority, $row->flags, $row->downloaded, $row->size);
			}
			
			$this->_dbConnection->FreeResult();
			
			return $_preData;
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @return WebMailMessageCollection
		 */
		function &LoadMessages(&$messageIndexSet, $indexAsUid, &$folder, $_preData = null)
		{
			$mailCollection = new WebMailMessageCollection();
			if ($this->_settings->StoreMailsInDb)
			{
				if (null === $_preData)
				{
					$_preData = $this->PreLoadMessagesFromDB($messageIndexSet, $indexAsUid, $folder);
				}
				
				$_msgArray = array_keys($_preData);
				
				/* read messages from db */
				if (!$this->_dbConnection->Execute($this->_commandCreator->LoadMessagesFromDB($_msgArray, $this->Account->Id)))
				{
					return null;
				}
			
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					if ($row)
					{
						if (isset($_preData[$row->id_msg]))
						{
							$msg = new WebMailMessage();
							$msg->LoadMessageFromRawBody($row->msg);
							$msg->IdMsg = $row->id_msg;
							$msg->IdFolder = $folder->IdDb;
							$msg->Uid = $_preData[$row->id_msg][0];
							$msg->DbPriority = $_preData[$row->id_msg][1];
							$msg->Flags = $_preData[$row->id_msg][2];
							$msg->Size = strlen($row->msg);
							$mailCollection->Add($msg);
							unset($msg);
						}
					}
				}
				
				$this->_dbConnection->FreeResult();

			}
			else
			{
				$fs = new FileSystem(INI_DIR.'/mail', strtolower($this->Account->Email), $this->Account->Id);
								
				if (null === $_preData)
				{
					$_preData = $this->PreLoadMessagesFromDB($messageIndexSet, $indexAsUid, $folder);
				}
				
				foreach ($_preData as $_id_msg => $_varArray)
				{
					$msg =& $fs->LoadMessage($_id_msg, $folder);
					if ($msg !== null)
					{
						$msg->IdMsg = $_id_msg;
						$msg->Uid = $_varArray[0];
						$msg->DbPriority = $_varArray[1];
						$msg->Flags = $_varArray[2];
						$msg->Size = $_varArray[4];

						$mailCollection->Add($msg);
					}
					unset($msg);
					
				}
			}

			return $mailCollection;	
		}
		
		/**
		 * @param string $messageIndex
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @return WebMailMessage
		 */
		function &LoadMessage($messageIndex, $indexAsUid, &$folder, $_preData = null)
		{
			$messageIndexArray = array($messageIndex);
			if ($this->_settings->StoreMailsInDb)
			{
				if (null === $_preData)
				{
					$_preData = $this->PreLoadMessagesFromDB($messageIndexArray, $indexAsUid, $folder);
				}
				
				$_msgArray = array_keys($_preData);
								
				$message = null;
				
				//read messages from db
				if (!$this->_dbConnection->Execute($this->_commandCreator->LoadMessagesFromDB($_msgArray, $this->Account)))
				{
					return $message;
				}

				$row = $this->_dbConnection->GetNextRecord();
				if ($row && isset($_preData[$row->id_msg]))
				{
					$message = new WebMailMessage();
					$message->LoadMessageFromRawBody($row->msg);
					$message->IdMsg = $row->id_msg;
					$message->IdFolder = $folder->IdDb;
					$message->Uid = $_preData[$row->id_msg][0];
					$message->DbPriority = $_preData[$row->id_msg][1];
					$message->Flags = $_preData[$row->id_msg][2];
					$message->Size = strlen($row->msg);
					$message->Downloaded = true;
				}
				else 
				{
					setGlobalError(PROC_MSG_HAS_DELETED);
				}
				
				$this->_dbConnection->FreeResult();
			}
			else
			{
				$fs = new FileSystem(INI_DIR.'/mail', strtolower($this->Account->Email), $this->Account->Id);
				
				if (null === $_preData)
				{
					$_preData = $this->PreLoadMessagesFromDB($messageIndexArray, $indexAsUid, $folder);
				}
				
				foreach ($_preData as $_id_msg => $_varArray)
				{
					$message =& $fs->LoadMessage($_id_msg, $folder);
					if ($message != null && is_array($_varArray))
					{
						$message->IdMsg = $_id_msg;
						$message->Uid = $_varArray[0];
						$message->IdFolder = $folder->IdDb;
						$message->DbPriority = $_varArray[1];
						$message->Flags = $_varArray[2];
						$message->Size = $_varArray[4];
						$message->Downloaded = true;
					}
					else 
					{
						setGlobalError(PROC_MSG_HAS_DELETED);
					}
					break;
				}
			}

			return $message;	
		}

		/**
		 * @param Folder $folder
		 * @return Array
		 */
		function &SelectIdMsgAndUidByIdMsgDesc(&$folder)
		{
			$outData = array();
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectIdMsgAndUid($folder, $this->Account)))
			{
				return $outData;
			}

			while (($row = $this->_dbConnection->GetNextRecord()) != false)
			{
				$outData[] = array($row->id_msg, $row->uid, $row->flag);
			}
			
			$this->_dbConnection->FreeResult();
		
			return $outData;
		}

		
		/**
		 * @return int
		 */
		function SelectLastIdMsg()
		{
			$idMsg = null;
			if ($this->_dbConnection->Execute($this->_commandCreator->SelectLastIdMsg($this->Account->Id)))
			{
				$row = $this->_dbConnection->GetNextRecord();
				if ($row)
				{
					$idMsg = $row->nid_msg;
				}
				$this->_dbConnection->FreeResult();
			}
			
			return ($idMsg == null) ? 0 : $idMsg + rand(1, 5);
		}
		
		/**
		 * @param int $messageId
		 * @param Folder $folder
		 * @return bool
		 */
		function GetMessageDownloadedFlag($messageId, &$folder)
		{
			$downloaded = false;
			if ($this->_dbConnection->Execute($this->_commandCreator->GetMessageDownloadedFlag($messageId, $folder, $this->Account->Id)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$downloaded = (bool) abs($row->downloaded);
				}
				$this->_dbConnection->FreeResult();
			}
			return $downloaded;
		}
		
		/**
		 * @param int $msgId
		 * @param int $charset
		 * @param WebMailMessage $message
		 * @return bool
		 */
		function UpdateMessageCharset($msgId, $charset, &$message)
		{
			$this->_dbConnection->Execute(
				$this->_commandCreator->UpdateMessageCharset($this->Account, $msgId, $charset, $message));
		}
		
		/**
		 * @param int $userId
		 * @return array
		 */
		function GetAccountListByUserId($userId)
		{
			$out = array();
			
			if ($this->_dbConnection->Execute($this->_commandCreator->GetAccountListByUserId($userId)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$out[] = (int) $row->id_acct;
				}
			}
			
			return $out;
		}
				
		/**
		 * @param int $userId
		 * @return array
		 */
		function GetFullAccountListByUserId($userId)
		{
			$out = array();
			
			if ($this->_dbConnection->Execute($this->_commandCreator->GetFullAccountListByUserId($userId)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$temp = array();
					$temp[] = (int) $row->id_acct;
					$temp[] = (bool) abs($row->def_acct);
					$out[] = $temp;
					unset($temp);
				}
			}
			
			return $out;
		}
		
		/**
		 * @param string $name
		 * @param string $email
		 * @param int $idUser
		 * @return bool
		 */
		function ExistAddressBookRecordDoublet($name, $email, $idUser)
		{
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectAddressBookRecordsByNameEmail($name, $email, $idUser)))
			{
				return false;
			}
			
			$existDoublet = false;
			while (false !== ($row = $this->_dbConnection->GetNextRecord()))
			{
				$primaryEmail = null;
				switch ($row->primary_email)
				{
					case PRIMARYEMAIL_Home:
						$primaryEmail = $row->h_email;
						break;
					case PRIMARYEMAIL_Business:
						$primaryEmail = $row->b_email;
						break;
					case PRIMARYEMAIL_Other:
						$primaryEmail = $row->other_email;
						break;
				}
				if ($primaryEmail == $email && $primaryEmail !== null)
				{
					$existDoublet = true;
					break;
				}
			}
			
			return $existDoublet;
		}
		
		/**
		 * @param int $user_id
		 * @return array
		 */
		function GetAccountIdsByUserId($user_id)
		{
			$outData = array();
			if (!$this->_dbConnection->Execute($this->_commandCreator->GetAccountIdsByUserId($user_id)))
			{
				return $outData;
			}

			while (($row = $this->_dbConnection->GetNextRecord()) != false)
			{
				$outData[] = $row->id_acct;
			}
			
			return $outData;
		}
		
		/**
		 * @param AddressBookRecord $addressBookRecordRecord
		 * @return bool
		 */
		function InsertAddressBookRecord(&$addressBookRecordRecord)
		{
			if ($this->_dbConnection->Execute($this->_commandCreator->InsertAddressBookRecord($addressBookRecordRecord)))
			{
				$addressBookRecordRecord->IdAddress = $this->_dbConnection->GetLastInsertId();
				$addressBookRecordRecord->StrId = AddressBookRecord::STR_PREFIX.$addressBookRecordRecord->IdAddress;
				return $this->UpdateContactStrId($addressBookRecordRecord->IdAddress, $addressBookRecordRecord->StrId);
			}
			return false;
		}

		/**
		 * @return bool
		 */
		function UpdateContactStrId($contactId, $contactStrId)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateContactStrId($contactId, $contactStrId));
		}
		
		/**
		 * @param AddressBookRecord $addressBookRecordRecord
		 * @return bool
		 */
		function UpdateAddressBookRecord(&$addressBookRecordRecord)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateAddressBookRecord($addressBookRecordRecord));
		}
		
		/**
		 * @param long $idAddress
		 * @return bool
		 */
		function DeleteAddressBookRecord($idAddress)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->DeleteAddressBookRecord($idAddress)) &&
					$this->_dbConnection->Execute($this->_commandCreator->DeleteAddressGroupsContactsByIdAddress($idAddress));
		}
		
		/**
		 * @param int $idAddress
		 * @return bool
		 */
		function DeleteAddressGroup($idGroup)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->DeleteAddressGroup($idGroup)) &&
					$this->_dbConnection->Execute($this->_commandCreator->DeleteAddressGroupsContactsByIdGroup($idGroup));
		}
		
		/**
		 * @param AddressGroup $group
		 * @return bool
		 */
		function InsertAddressGroup(&$group)
		{
			if ($this->_dbConnection->Execute($this->_commandCreator->InsertAddressGroup($group)))
			{
				$group->Id = $this->_dbConnection->GetLastInsertId();
				$group->GroupStrId = AddressGroup::STR_PREFIX.$group->Id;
				return $this->UpdateGroupStrId($group->Id, $group->GroupStrId);
			}
			return false;
		}

		/**
		 * @param AddressGroup $group
		 * @return bool
		 */
		function UpdateGroupStrId($groupId, $groupStrId)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateGroupStrId($groupId, $groupStrId));
		}

		/**
		 * @param AddressGroup $group
		 * @return bool
		 */
		function UpdateAddressGroup(&$group)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateAddressGroup($group));
		}
		
		/**
		 * @param long $idAddress
		 * @return AddressBookRecord
		 */
		function &SelectAddressBookRecord($idAddress)
		{
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectAddressBookRecord($idAddress, $this->Account->IdUser)))
			{
				return null;
			}

			$addressBookRecord = null;
			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$addressBookRecord = new AddressBookRecord();
				
				$addressBookRecord->IdAddress = $row->id_addr;
				$addressBookRecord->StrId = $row->str_id;
				$addressBookRecord->IdUser = $row->id_user;
				$addressBookRecord->HomeEmail = $row->h_email;
				$addressBookRecord->FullName = $row->fullname;
				$addressBookRecord->Notes = $row->notes;
				$addressBookRecord->UseFriendlyName = (bool) abs($row->use_friendly_nm);
				$addressBookRecord->HomeStreet = $row->h_street;
				$addressBookRecord->HomeCity = $row->h_city;
				$addressBookRecord->HomeState = $row->h_state;
				$addressBookRecord->HomeZip = $row->h_zip;
				$addressBookRecord->HomeCountry = $row->h_country;
				$addressBookRecord->HomePhone = $row->h_phone;
				$addressBookRecord->HomeFax = $row->h_fax;
				$addressBookRecord->HomeMobile = $row->h_mobile;
				$addressBookRecord->HomeWeb = $row->h_web;
				$addressBookRecord->BusinessEmail = $row->b_email;
				$addressBookRecord->BusinessCompany = $row->b_company;
				$addressBookRecord->BusinessStreet = $row->b_street;
				$addressBookRecord->BusinessCity = $row->b_city;
				$addressBookRecord->BusinessState = $row->b_state;
				$addressBookRecord->BusinessZip = $row->b_zip;
				$addressBookRecord->BusinessCountry = $row->b_country;
				$addressBookRecord->BusinessJobTitle = $row->b_job_title;
				$addressBookRecord->BusinessDepartment = $row->b_department;
				$addressBookRecord->BusinessOffice = $row->b_office;
				$addressBookRecord->BusinessPhone = $row->b_phone;
				$addressBookRecord->BusinessFax = $row->b_fax;
				$addressBookRecord->BusinessWeb = $row->b_web;
				$addressBookRecord->OtherEmail = $row->other_email;
				$addressBookRecord->PrimaryEmail = (int) $row->primary_email;
				$addressBookRecord->IdPreviousAddress = (int) $row->id_addr_prev;
				$addressBookRecord->Temp = (bool) abs($row->tmp);
				$addressBookRecord->BirthdayDay = $row->birthday_day;
				$addressBookRecord->BirthdayMonth = $row->birthday_month;
				$addressBookRecord->BirthdayYear = $row->birthday_year;
			}
			
			return $addressBookRecord;
		}

		/**
		 * @param int $idAddress
		 * @return Array
		 */
		function &SelectAddressGroupContact($idAddress)
		{
			$outData = array();
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectAddressGroupContact($idAddress)))
			{
				return $outData;
			}

			$outData = array();
			while (false !== ($row = $this->_dbConnection->GetNextRecord()))
			{
				$outData[$row->group_id] = $row->group_nm;
			}
		
			return $outData;
		}
		
		/**
		 * @param int $idGroup
		 * @return ContactCollection
		 */
		function &SelectAddressGroupContacts($idGroup)
		{
			$contacts = null;
			if ($this->_dbConnection->Execute($this->_commandCreator->SelectAddressGroupContacts($idGroup)))
			{
				$contacts = new ContactCollection();
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$contact = new Contact();
					$contact->Id = $row->id;
					$contact->Name = $row->fullname;
					$contact->Email = $row->email;
					$contact->UseFriendlyName = (bool) $row->usefriendlyname;
					
					$contacts->Add($contact);
					unset($contact);
				}
			}

			return $contacts;
		}
		
		
		/**
		 * @param int $idAddress
		 * @param int $idGroup
		 * @return bool
		 */
		function InsertAddressGroupContact($idAddress, $idGroup)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->InsertAddressGroupContact($idAddress, $idGroup));
		}
		
		/**
		 * @param int $idAddress
		 * @return bool
		 */
		function DeleteAddressGroupsContactsByIdAddress($idAddress)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->DeleteAddressGroupsContactsByIdAddress($idAddress));
		}
		
		/**
		 * @param int $idGroup
		 * @return bool
		 */
		function DeleteAddressGroupsContactsByIdGroup($idGroup)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->DeleteAddressGroupsContactsByIdGroup($idGroup));
		}

		/**
		 * @param long $idAddress
		 * @param int $idGroup
		 * @return bool
		 */
		function DeleteAddressGroupsContacts($idAddress, $idGroup)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->DeleteAddressGroupsContacts($idAddress, $idGroup));
		}
		
		/**
		 * @param int $idGroup
		 * @return Array
		 */
		function &SelectAddressContactsAndGroupsCount($lookForType, $idUser, $condition = null, $idGroup = null)
		{
			$outArray = array(0, 0);
			if ($this->_dbConnection->Execute($this->_commandCreator->SelectAddressContactsCount($lookForType, $idUser, $condition, $idGroup)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$outArray[0] = $row->contacts_count;
				}
			}

			if ($this->_dbConnection->Execute($this->_commandCreator->SelectAddressGroupsCount($lookForType, $idUser, $condition)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$outArray[1] = $row->groups_count;
				}
			}
			return $outArray;
		}

		function SelectAccountsCountForEmailSharing($lookForType, $lookForField, $account)
		{
			$return = 0;
			if ($lookForType == 1)
			{
				$doSqlQuery = false;
				$domainId = null;
				if ($this->_settings->GlobalAddressBook === GLOBAL_ADDRESS_BOOK_DOMAIN && $account->DomainAddressBook && $account->IdDomain > 0)
				{
					$doSqlQuery = true;
					$domainId = $account->IdDomain;
				}
				else if ($this->_settings->GlobalAddressBook === GLOBAL_ADDRESS_BOOK_SYSTEM)
				{
					$doSqlQuery = true;
				}
				
				if ($doSqlQuery && $this->_dbConnection->Execute($this->_commandCreator->SelectAccountsCountForEmailSharing($lookForField, $domainId)))
				{
					while (false !== ($row = $this->_dbConnection->GetNextRecord()))
					{
						$return = (int) $row->contacts_count;
					}
				}
			}
			return $return;
		}
		
		/**
		 * @param int $idGroup
		 * @return string
		 */
		function SelectAddressGroupName($idGroup)
		{
			$groupName = '';
			if ($this->_dbConnection->Execute($this->_commandCreator->SelectAddressGroupName($idGroup)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$groupName = $row->group_nm;
				}
			}

			return $groupName;
		}
		
		/**
		 * @param String $strGroupName
		 * @param int $idAcct
		 * @return bool
		 */
		function CheckExistsAddresGroupByName($strGroupName, $idUser)
		{
			if($this->_dbConnection->Execute($this->_commandCreator->CheckExistsGroupByName($strGroupName, $idUser)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$count = $row->mcount;
					if($count > 0)
					{
						return true;	
					}
					
					return false;
				}
				
				return false;
			}
			
			return false;
		}
		
		/**
		 * @return Array
		 */
		function &SelectUserAddressGroupNames()
		{
			$groupsArray = array();
			if ($this->_dbConnection->Execute($this->_commandCreator->SelectUserAddressGroupNames($this->Account->IdUser)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$groupsArray[$row->id_group] = $row->group_nm;
				}
			}
			
			return $groupsArray;
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
		
			return $this->_dbConnection->Execute(
					$this->_commandCreator->SetMessagesFlags($messageIndexSet, $indexAsUid, $folder,
																$flags, $action, $this->Account));
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $fromFolder
		 * @param Folder $toFolder
		 * @return bool
		 */
		function MoveMessages(&$messageIndexSet, $indexAsUid, &$fromFolder, &$toFolder)
		{
			$result = true;
			if (!$this->_settings->StoreMailsInDb &&
					$this->_dbConnection->Execute(
						$this->_commandCreator->SelectDownloadedMessagesIdSet($messageIndexSet, $indexAsUid,
																				$this->Account)))
			{
				$downloadedMsgIdSet = array();

				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$downloadedMsgIdSet[] = $row->id_msg;
				}

				if (count($downloadedMsgIdSet) > 0)													
				{
					$fs = new FileSystem(INI_DIR.'/mail', strtolower($this->Account->Email), $this->Account->Id);
					if (!$fs->MoveMessages($downloadedMsgIdSet, $fromFolder, $toFolder))
					{	
						$this->_log->WriteLine('ERROR: Can\'t move message on file system', LOG_LEVEL_ERROR);
						// return false;
					}
					
				}	
			}
			
			if ($result)
			{
				$result = $this->_dbConnection->Execute(
							$this->_commandCreator->MoveMessages($messageIndexSet, $indexAsUid, $fromFolder,
																	$toFolder, $this->Account));															
			}
			else 
			{
				$this->_log->WriteLine('ERROR: Can\'t save message to DB', LOG_LEVEL_ERROR);
			}
		
			return $result;
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $fromFolder
		 * @param Folder $toFolder
		 * @return bool
		 */
		function FullMoveMessages(&$messageIndexSet, $indexAsUid, &$fromFolder, &$toFolder)
		{
			if(!$this->_dbConnection->Execute(
				$this->_commandCreator->FullMoveMessages($messageIndexSet, $indexAsUid, $fromFolder, $toFolder, $this->Account)))
			{
				$this->_log->WriteLine('ERROR: Can\'t save message to DB', LOG_LEVEL_ERROR);
				return false;	
			}
			return true;
		}
		
		function MoveMessagesWithUidUpdate(&$messageIndexUidSet, &$fromFolder, &$toFolder)
		{
			$result = true;
			foreach ($messageIndexUidSet as $_id => $_uid)
			{
				if(!$this->_dbConnection->Execute(
					$this->_commandCreator->MoveMessageWithUidUpdate($_id, $_uid, $fromFolder, $toFolder)))
				{
					$this->_log->WriteLine('ERROR: Can\'t move message with uid update', LOG_LEVEL_ERROR);
					$result = false;
				}
			}
			
			return $result;
		}
		
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $folder optional
		 * @return bool
		 */
		function DeleteMessages(&$messageIndexSet, $indexAsUid, &$folder)
		{
			if ($this->_settings->StoreMailsInDb)
			{
				$this->_dbConnection->Execute(
					$this->_commandCreator->DeleteMessagesBody($messageIndexSet, $indexAsUid, $folder, $this->Account));
			}
			else 
			{
				$fs = new FileSystem(INI_DIR.'/mail', strtolower($this->Account->Email), $this->Account->Id);
				$fs->DeleteMessages($messageIndexSet, $folder, true);
			}
			
			return $this->_dbConnection->Execute($this->_commandCreator->DeleteMessagesHeaders($messageIndexSet, $indexAsUid, $folder, $this->Account));
		}
		
		/**
		 * @param Folder $folder
		 * @param Account $account
		 * @return bool
		 */
		function ClearDbFolder($folder, $account)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->ClearDbFolder($folder, $account));
		}
		
		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function PurgeFolder(&$folder, $pop3EmptyTrash = false)
		{
			$result = true;

			if ($this->_settings->StoreMailsInDb)
			{
				/* remove messages from db, read messages from file system */
				if (!$this->_dbConnection->Execute($this->_commandCreator->SelectDeletedMessagesId($folder, $this->Account, $pop3EmptyTrash)))
				{
					return false;
				}
	
				$msgIdSet = array();
				while (false !== ($row =  $this->_dbConnection->GetNextRecord()))
				{
					$msgIdSet[] = $row->id_msg;
				}
				
				if(count($msgIdSet) > 0)
				{
					$result &= $this->_dbConnection->Execute(
										$this->_commandCreator->PurgeAllMessagesBody($msgIdSet, $this->Account->Id));
					$result &= $this->_dbConnection->Execute(
										$this->_commandCreator->PurgeAllMessageHeaders($folder, $this->Account, $pop3EmptyTrash));
										
					return $result;
				}
				else 
				{
					return true;
				}
			}
				
			/* read messages from file system */
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectAllDeletedMsgId($folder, $this->Account, $pop3EmptyTrash)))
			{
				return false;
			}

			$messageIdSet = array();
			while (false !== ($row =  $this->_dbConnection->GetNextRecord()))
			{
				$messageIdSet[] = $row->id_msg;
			}

			if (count($messageIdSet) > 0)
			{
				$fs = new FileSystem(INI_DIR.'/mail', strtolower($this->Account->Email), $this->Account->Id);
				$result &= $fs->DeleteMessages($messageIdSet, $folder);
			}
			
			return $result && $this->_dbConnection->Execute(
						$this->_commandCreator->PurgeAllMessageHeaders($folder, $this->Account, $pop3EmptyTrash));
		}
		
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @return string
		 */
		function &SelectDownloadedMessagesIdSet($messageIndexSet, $indexAsUid)
		{
			$messagesIdSet = array();
			if ($this->_dbConnection->Execute(
					$this->_commandCreator->SelectDownloadedMessagesIdSet($messageIndexSet, $indexAsUid, $this->Account)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$messagesIdSet[] = $row->id_msg;
				}
			}
			
			return $messagesIdSet;
		}
		
		/**
		 * @param Folder $folder
		 * @return Array
		 */
		function &SelectAllMessagesUidSetByFolder(&$folder)
		{
			$messagesUidSet = array();
			if ($this->_dbConnection->Execute(
					$this->_commandCreator->SelectAllMessagesUidSetByFolder($folder, $this->Account)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$messagesUidSet[] = $row->uid;
				}
			}
			
			return $messagesUidSet;
		}
		
		/**
		 * @param int $accountId
		 * @return FilterCollection
		 */
		function &SelectFilters($accountId, $useCache = false)
		{
			$filters = null;
			$_sql = $this->_commandCreator->SelectFilters($accountId);
			
			$_cacher =& CObjectCache::CreateInstance();
			if ($useCache && $_cacher->Has('sql='.$_sql))
			{
				$filters =& $_cacher->Get('sql='.$_sql);
			}
			else if ($this->_dbConnection->Execute($_sql, true))
			{
				$filters = new FilterCollection();
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$filter = new Filter();
					$filter->Id = $row->id_filter;
					$filter->IdAcct = $accountId;
					$filter->Field = $row->field;
					$filter->Condition = $row->condition;
					$filter->Filter = $row->filter;
					$filter->Action = $row->action;
					$filter->IdFolder = $row->id_folder;
					$filter->Applied = $row->applied;
					
					$filters->Add($filter);
					unset($filter);
				}
				
				if ($useCache)
				{
					$_cacher->Set('sql='.$_sql, $filters);
				}
			}

			return $filters;
		}
		
		/**
		 * @param Filter $filter
		 * @return bool
		 */
		function InsertFilter(&$filter)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->InsertFilter($filter));
		}
		
		/**
		 * @param Filter $filter
		 * @return bool
		 */
		function UpdateFilter(&$filter)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->UpdateFilter($filter));
		}

		/**
		 * @param int $filterId
		 * @param int $accountId
		 * @return bool
		 */
		function DeleteFilter($filterId, $accountId)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->DeleteFilter($filterId, $accountId));
		}
		
		/**
		 * @param int $folderId
		 * @param int $accountId
		 * @return bool
		 */
		function DeleteFolderFilters($folderId, $accountId)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->DeleteFolderFilters($folderId, $accountId));
		}

		/**
		 * @param string $condition
		 * @param Folder $folders
		 * @return array|false
		 */
		function SearchMessagesUids($condition, &$folder)
		{
			$uids = false;

			if ($this->_dbConnection->Execute(
					$this->_commandCreator->SearchMessagesUids($condition, $folder, $this->Account)))
			{
				$uids = array();
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$uids[] = (int) $row->uid;
				}
			}

			return $uids;
		}
		
		/**
		 * @param string $condition
		 * @param FolderCollection $folders
		 * @param bool $inHeadersOnly
		 * @return int
		 */
		function SearchMessagesCount($condition, &$folders, $inHeadersOnly)
		{
			$mailCollectionCount = 0;
			
			if ($this->_dbConnection->Execute(
					$this->_commandCreator->SearchMessagesCount(
						$condition, $folders->CreateFolderListFromTree(), $inHeadersOnly, $this->Account)))
			{
				
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$mailCollectionCount = $row->msg_count;
				}
			}
			
			return $mailCollectionCount;
		}
		
		/**
		 * @param int $pageNumber
		 * @param string $condition
		 * @param FolderCollection $folders
		 * @param bool $inHeadersOnly
		 * @return WebMailMessageCollection
		 */
		function &SearchMessages($pageNumber, $condition, &$folders, $inHeadersOnly)
		{
			$mailCollection = null;
			if ($this->_dbConnection->Execute(
					$this->_commandCreator->SearchMessages(
						$pageNumber, $condition, $folders->CreateFolderListFromTree(), $inHeadersOnly, $this->Account)))
			{
				$mailCollection = new WebMailMessageCollection();
				
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$msg =& $this->_rowToWebMailMessage($row);
					$mailCollection->Add($msg);
					unset($msg);
				}
			}

			return $mailCollection;	
		}
		
		/**
		 * @return Array
		 */
		function &SelectReadsRecords()
		{
			$readsRecords = array();
			if ($this->_dbConnection->Execute($this->_commandCreator->SelectReadsRecords($this->Account->Id)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$readsRecords[$row->uid] = '';
				}
			}

			return $readsRecords;
		}

		/**
		 * @return bool
		 */
		function DeleteReadsRecords()
		{
			return $this->_dbConnection->Execute($this->_commandCreator->DeleteReadsRecords($this->Account->Id));
		}
		
		/**
		 * @param array $uids
		 * @return bool
		 */
		function DeleteReadsRecordsByUids($uids)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->DeleteReadsRecordsByUid($this->Account->Id, $uids));
		}
		
		/**
		 * @param bool $sortOrder
		 * @return bool
		 */
		function InsertReadsRecords($uidArray)
		{
			$result = true;
			
			foreach ($uidArray as $uid)
			{
				$result &= $this->_dbConnection->Execute($this->_commandCreator->InsertReadsRecord($this->Account->Id, $uid));
			}
			return $result;
		}

		/**
		 * @param int $pageNumber
		 * @param string $sortField
		 * @param bool $sortOrder
		 * @return ContactCollection
		 */
		function &LoadContactsAndGroups($pageNumber, $sortField, $sortOrder)
		{
			$contacts = null;
			if ($this->_dbConnection->Execute(
					$this->_commandCreator->LoadContactsAndGroups($pageNumber, $sortField, $sortOrder, $this->Account)))
			{
				$contacts = new ContactCollection();
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$contact = new Contact();
					$contact->Id = $row->id;
					$contact->IsGroup = (bool) $row->is_group;
					$contact->Name = $row->name;
					$contact->Email = $row->email;
					$contact->UseFriendlyName = (bool) $row->usefriendlyname;
					
					if ($sortField == 3 && !$contact->UseFriendlyName)
					{
						$contact->Name = '';
					}
					
					$contacts->Add($contact);
					unset($contact);
				}
			}

			return $contacts;
		}

		/**
		 * @param Array $contactIds
		 * @return ContactCollection
		 */
		function &LoadContactsById($contactIds)
		{
			$contacts = null;
			if ($this->_dbConnection->Execute(
					$this->_commandCreator->LoadContactsById($contactIds, $this->Account)))
			{
				$contacts = new ContactCollection();
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$contact = new Contact();
					$contact->Id = $row->id;
					$contact->IsGroup = $row->is_group;
					$contact->Name = $row->name;
					$contact->Email = $row->email;
					$contact->Frequency = $row->frequency;
					$contact->UseFriendlyName = (bool) $row->usefriendlyname;
					
					$contacts->Add($contact);
					unset($contact);
				}
			}

			return $contacts;
		}
		
		
		/**
		 * @param int $pageNumber
		 * @param string $condition
		 * @param int $groupId
		 * @param string $sortField
		 * @param bool $sortOrder
		 * @return ContactCollection
		 */
		function &SearchContactsAndGroups($pageNumber, $condition, $groupId, $sortField, $sortOrder, $lookForType)
		{
			$contacts = null;
			if ($this->_dbConnection->Execute(
					$this->_commandCreator->SearchContactsAndGroups($pageNumber, $condition,
						$groupId, $sortField, $sortOrder, $this->Account, $lookForType)))
			{
				$contacts = new ContactCollection();
				
				$k = 0;
				
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					if ($lookForType == 1 && $k > SUGGESTCONTACTS)
					{
						$this->_dbConnection->FreeResult();
						break;	
					}
									
					$contact = new Contact();
					$contact->Id = $row->id;
					$contact->IsGroup = $row->is_group;
					$contact->Name = $row->name;
					$contact->Email = $row->email;
					$contact->Frequency = $row->frequency;
					$contact->UseFriendlyName = (bool) $row->usefriendlyname;
					
					$contacts->Add($contact);
					unset($contact);
					$k++;
				}
			}
			
			return $contacts;
		}
		
		/**
		 * @return bool
		 */
		function UpdateMailboxSize()
		{
			$mailBoxSize = 0;
			if ($this->_dbConnection->Execute($this->_commandCreator->CountMailboxSize($this->Account->Id)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$mailBoxSize = $row->mailbox_size;
				}
			}

			return $this->_dbConnection->Execute(
						$this->_commandCreator->UpdateMailboxSize($mailBoxSize, $this->Account->Id));
		}
		
		/**
		 * @return int
		 */
		function SelectMailboxesSize()
		{
			$mailBoxesSize = 0;
			if ($this->_dbConnection->Execute($this->_commandCreator->SelectMailboxesSize($this->Account->IdUser)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$mailBoxesSize = GetGoodBigInt($row->mailboxes_size);
				}
			}
			
			return $mailBoxesSize;
		}
		
		/**
		 * @return Array
		 */
		function &SelectExpiredMessageUids()
		{
			$expiredUids = array();

			if ($this->_dbConnection->Execute($this->_commandCreator->SelectExpiredMessageUids($this->Account)))
			{
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$expiredUids[] = $row->str_uid;
				}
			}
			
			return $expiredUids;
		}
		
		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function CreateFolder(&$folder)
		{
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectForCreateFolder($folder)))
			{
				return false;
			}
			else 
			{
				$row = $this->_dbConnection->GetNextRecord();
				$folder->FolderOrder = ($row && isset($row->norder)) ? (int) $row->norder + 1 : 0;
			}
			
			if (!$this->_dbConnection->Execute($this->_commandCreator->CreateFolder($folder)))
			{
				return false;
			}
					
			$folder->IdDb = $this->_dbConnection->GetLastInsertId();

			if (!$this->_dbConnection->Execute($this->_commandCreator->CreateFolderTree($folder)))
			{
				return false;
			}
			
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectForCreateFolderTree($folder)))
			{
				return false;
			}
			else 
			{
				$result = array(); 
				while (false !== ($row = $this->_dbConnection->GetNextRecord()))
				{
					$IdParent = ($row && isset($row->id_parent)) ? (int) $row->id_parent : -1;
					$Level = ($row && isset($row->folder_level)) ? (int) $row->folder_level + 1 : 0;
					
					$result[] = array($IdParent, $Level);
				}
				
				if ($result && count($result) > 0)
				{
					foreach ($result as $folderData)
					{
						$folder->IdParent = $folderData[0];
						$folder->Level = $folderData[1];
						if (!$this->_dbConnection->Execute($this->_commandCreator->CreateSelectFolderTree($folder)))
						{
							return false;
						}	
					}
					
				}
			}

			return true;
		}
		
		/**
		 * @param Account $account
		 * @param Array $arrayEmails
		 * @return bool
		 */
		function UpdateSuggestTable(&$account, $arrayEmailsWithFName)
		{
			$arrayEmails = array_keys($arrayEmailsWithFName);
						
			$DBEmails = $this->SelectExistEmails($account, $arrayEmails);
			
			if($DBEmails === false)
			{
				return false;
			}
		
			$arrayEmails = array_unique($arrayEmails);
			$DBEmails = array_unique($DBEmails);
			
			$NewEmails = array_diff($arrayEmails, $DBEmails);
			
			$UpdateEmails = $arrayEmails;
			
			if (count($UpdateEmails) > 0)
			{
				if(!$this->_dbConnection->Execute($this->_commandCreator->UpdateContactFrequencyByEmail($account, $UpdateEmails)))
				{
					return false;	
				}
			}
			
			if (count($NewEmails) > 0)	
			{
				foreach ($NewEmails as $key) 
				{
					if(strlen($key) > 0)
					{
						/* $arrayEmailsWithFName[$key] */
						if(!$this->_dbConnection->Execute($this->_commandCreator->InsertAutoCreateContact($account, $key, $arrayEmailsWithFName[$key])))
						{
							return false;
						}
					}
				}
			}
			
			return true;
		}

		/**
		 * @param int $id_user
		 * @return int | false
		 */
		function GetAUserDeleted($id_user)
		{
			if ($this->_dbConnection->Execute($this->_commandCreator->GetAUserDeleted($id_user)))
			{
				$row = $this->_dbConnection->GetNextRecord();
				if ($row)
				{
					$this->_dbConnection->FreeResult();
					return (int) $row->deleted;
				}
			}
			return false;
		}

		/**
		 * @return	int
		 */
		function AllUserCount()
		{
			$cnt = 0;
			if (!$this->_dbConnection->Execute($this->_commandCreator->AllUserCount()))
			{
				return false;
			}

			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$cnt = (int) $row->user_cnt;
				$this->_dbConnection->FreeResult();
			}

			return $cnt;
		}

		function SelectLastFunabolCronRun()
		{
			$run_date = null;
			if (!$this->_dbConnection->Execute($this->_commandCreator->SelectLastFunabolCronRun()))
			{
				return false;
			}

			$row = $this->_dbConnection->GetNextRecord();
			if ($row)
			{
				$run_date = $row->run_date;
				$this->_dbConnection->FreeResult();
			}

			return $run_date;
		}

		function WriteLastFunabolCronRun( $date )
		{
			return $this->_dbConnection->Execute($this->_commandCreator->WriteLastFunambolCronRun( $date ));
		}


		/*----------*/
		
		function SessionRead($hash)
		{
			$data = false;
			$time = time() - 7200;
			if (!$this->_dbConnection->Execute($this->_commandCreator->SessionRead($hash, $time)))
			{
				return $data;
			}

			if ($this->_dbConnection->ResultCount() > 0)
			{
				$row = $this->_dbConnection->GetNextRecord();
				if ($row)
				{
					$data = $row->sess_data;
					// $data = base64_decode($row->sess_data);
				}
			}
			else
			{
				if (!$this->_dbConnection->Execute($this->_commandCreator->SessionInsertNew($hash)))
				{
					return false;
				}
			}

			return $data;
		}

		function SessionWrite($hash, $data)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->SessionUpdate($hash, $data));
			//return $this->_dbConnection->Execute($this->_commandCreator->SessionUpdate($hash, base64_encode($data)));
		}

		function SessionDestroy($hash)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->SessionDestroy($hash));
		}

		function SessionGC($time)
		{
			return $this->_dbConnection->Execute($this->_commandCreator->SessionGC($time));
		}
	}
	
	class MySqlStorage extends DbStorage
	{
		/**
		 * @param Account $account
		 * @return MySqlStorage
		 */
		function MySqlStorage(&$account, $settings = null)
		{
			DbStorage::DbStorage($account, $settings);
			$this->_escapeType = QUOTE_ESCAPE;
			$this->_commandCreator = new MySqlCommandCreator($settings);
			
			if ($this->_settings->UseCustomConnectionString || $this->_settings->UseDsn)
			{
				require_once(WM_ROOTPATH.'db/class_dbodbc.php');
				if ($this->_settings->UseCustomConnectionString)
				{
					$this->_dbConnection = new DbOdbc($this->_settings->DbCustomConnectionString, $this->_settings->DbType);
				}
				else
				{
					$this->_dbConnection = new DbOdbc('DSN='.$this->_settings->DbDsn.';', $this->_settings->DbType);
				}
			}
			else
			{
				require_once(WM_ROOTPATH.'db/class_dbmysql.php');
				$this->_dbConnection = new DbMySql($this->_settings->DbHost, $this->_settings->DbLogin,
													$this->_settings->DbPassword, $this->_settings->DbName);
			}
		}

		/**
		 * @param string $email
		 * @return int
		 */
		function CheckCountOfUserAccounts($email)
		{
			$this->_dbConnection->Execute($this->_commandCreator->CheckCountOfUserAccounts($email));
			return $this->_dbConnection->ResultCount();
		}
	}
		

	class MsSqlStorage extends DbStorage
	{
		/**
		 * @param Account $account
		 * @return MsSqlStorage
		 */
		function MsSqlStorage(&$account, $settings = null)
		{
			DbStorage::DbStorage($account, $settings);
			$this->_escapeType = QUOTE_DOUBLE;
			$this->_commandCreator = new MsSqlCommandCreator($settings);

			if ($this->_settings->UseCustomConnectionString || $this->_settings->UseDsn)
			{
				require_once(WM_ROOTPATH.'db/class_dbodbc.php');
				if ($this->_settings->UseCustomConnectionString)
				{
					$this->_dbConnection = new DbOdbc($this->_settings->DbCustomConnectionString, $this->_settings->DbType, $this->_settings->DbLogin, $this->_settings->DbPassword);
				}
				else
				{
					$this->_dbConnection = new DbOdbc('DSN='.$this->_settings->DbDsn.';', $this->_settings->DbType, $this->_settings->DbLogin, $this->_settings->DbPassword);
				}
			}
			else
			{
				require_once(WM_ROOTPATH.'db/class_dbmssql.php');
				$this->_dbConnection = new DbMsSql($this->_settings->DbHost, $this->_settings->DbLogin,
													$this->_settings->DbPassword, $this->_settings->DbName);
			}
		}
	}