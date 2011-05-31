<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	class CWebMailDomain
	{
		/**
		 * @var	id
		 */
		var $_id;
		
		/**
		 * @var	string
		 */
		var $_name;
		
		/**
		 * @var	int
		 */
		var $_mailProtocol = WM_MAILPROTOCOL_POP3;
		
		/**
		 * @var	string
		 */
		var $_mailIncomingHost;
		
		/**
		 * @var	int
		 */
		var $_mailIncomingPort = 110;
		
		/**
		 * @var	string
		 */
		var $_mailSmtpHost;
		
		/**
		 * @var	int
		 */
		var $_mailSmtpPort = 25;
		
		/**
		 * @var	bool
		 */
		var $_mailSmtpAuth = true;

		/**
		 * @var	bool
		 */
		var $_isInternal = false;

		/**
		 * @var	bool
		 */
		var $_globalAddrBook = false;

		/**
		 * @var	bool
		 */
		var $_ldapAuth = false;

		/**
		 * @var array
		 */
		var $_settingsValues = array();
		
		/**
		 * @param	string	$name
		 * @param	int		$incProtocol
		 * @param	string	$incHost
		 * @param	int		$incPort
		 * @param	string	$smtpHost
		 * @param	int		$smtpPort
		 * @param	bool	$smtpAuth
		 * @param	bool	$isInternal = false
		 */
		function Init($name, $incProtocol, $incHost, $incPort, $smtpHost, $smtpPort, $smtpAuth, $isInternal = false, $globalAddrBook = false, $ldapAuth = false)
		{
			$this->_name = $name;
			$this->_mailProtocol = (int) $incProtocol;
			$this->_mailIncomingHost = $incHost;
			$this->_mailIncomingPort = ($incPort === null)
				? ($this->_mailProtocol == WM_MAILPROTOCOL_IMAP4) ? 143 : 110 
				: $incPort;
			$this->_mailSmtpHost = $smtpHost;
			$this->_mailSmtpPort = ($incPort !== null) ? (int) $smtpPort : 25;
			$this->_mailSmtpAuth = (bool) $smtpAuth;
			$this->_globalAddrBook = $globalAddrBook;
			$this->_ldapAuth = $ldapAuth;
			
			$this->SetInternal($isInternal);
		}

		/**
		 * @param	bool	$isInternal = false
		 */
		function SetInternal($isInternal = false)
		{
			$this->_isInternal = (bool) $isInternal;
		}

		/**
		 * @return	bool
		 */
		function IsInternal()
		{
			return $this->_isInternal;
		}

		/**
		 * @param	Account	$account
		 */
		function InitInternalAccountLogin(&$account, $addDomain = true)
		{
			if ($this->_isInternal && $account)
			{
				$account->IsInternal = true;
				$loginArray = explode('@', $account->MailIncLogin);
				if ($addDomain)
				{
					$account->MailIncLogin = isset($loginArray[0])
						? $loginArray[0].'@'.$this->Name()
						: $account->MailIncLogin.'@'.$this->Name();

					$account->Email = $account->MailIncLogin;
				}
				else
				{
					$account->MailIncLogin = isset($loginArray[0])
						? $loginArray[0]
						: $account->MailIncLogin;
				}
			}
		}
		
		function InitByDbRow($row)
		{
			if (!$row)
			{
				return false;
			}

			$this->_id = (int) $row->id_domain;
			$this->_name = $row->name;
			$this->_mailProtocol = (int) $row->mail_protocol;
			$this->_mailIncomingHost = $row->mail_inc_host;
			$this->_mailIncomingPort = ($row->mail_inc_port == null)
				? ($this->_mailProtocol == WM_MAILPROTOCOL_IMAP4) ? 143 : 110 
				: $row->mail_inc_port;
			$this->_mailSmtpHost = $row->mail_out_host;
			$this->_mailSmtpPort = ($row->mail_out_port != null) ? (int) $row->mail_out_port : 25;
			$this->_mailSmtpAuth = (bool) $row->mail_out_auth;
			$this->_isInternal = (bool) $row->is_internal;
			$this->_globalAddrBook = (bool) $row->global_addr_book;
			$this->_ldapAuth = (bool) $row->ldap_auth;
			
			$this->SetSettingsValue('url', $row->url);
			$this->SetSettingsValue('site_name', $row->site_name);
			$this->SetSettingsValue('settings_mail_protocol', (int) $row->settings_mail_protocol);
			$this->SetSettingsValue('settings_mail_inc_host', $row->settings_mail_inc_host);
			$this->SetSettingsValue('settings_mail_inc_port', (int) $row->settings_mail_inc_port);
			$this->SetSettingsValue('settings_mail_out_host', $row->settings_mail_out_host);
			$this->SetSettingsValue('settings_mail_out_port', (int) $row->settings_mail_out_port);
			$this->SetSettingsValue('settings_mail_out_auth', (bool) $row->settings_mail_out_auth);
			$this->SetSettingsValue('allow_direct_mode', (bool) $row->allow_direct_mode);
			$this->SetSettingsValue('direct_mode_id_def', (bool) $row->direct_mode_id_def);
			$this->SetSettingsValue('attachment_size_limit', (int) $row->attachment_size_limit);
			$this->SetSettingsValue('allow_attachment_limit', (bool) $row->allow_attachment_limit);
			$this->SetSettingsValue('mailbox_size_limit', (int) $row->mailbox_size_limit);
			$this->SetSettingsValue('allow_mailbox_limit',(bool) $row->allow_mailbox_limit);
			$this->SetSettingsValue('take_quota', (bool) $row->take_quota);
			$this->SetSettingsValue('allow_new_users_change_set', (bool) $row->allow_new_users_change_set);
			$this->SetSettingsValue('allow_auto_reg_on_login', (bool) $row->allow_auto_reg_on_login);
			$this->SetSettingsValue('allow_users_add_accounts', (bool) $row->allow_users_add_accounts);
			$this->SetSettingsValue('allow_users_change_account_def', (bool) $row->allow_users_change_account_def);
			$this->SetSettingsValue('def_user_charset', (int) $row->def_user_charset);
			$this->SetSettingsValue('allow_users_change_charset', (bool) $row->allow_users_change_charset);
			$this->SetSettingsValue('def_user_timezone', (int) $row->def_user_timezone);
			$this->SetSettingsValue('allow_users_change_timezone', (bool) $row->allow_users_change_timezone);
			$this->SetSettingsValue('msgs_per_page', (int) $row->msgs_per_page);
			$this->SetSettingsValue('skin', $row->skin);
			$this->SetSettingsValue('allow_users_change_skin', (bool) $row->allow_users_change_skin);
			$this->SetSettingsValue('lang', $row->lang);
			$this->SetSettingsValue('allow_users_change_lang', (bool) $row->allow_users_change_lang);
			$this->SetSettingsValue('show_text_labels', (bool) $row->show_text_labels);
			$this->SetSettingsValue('allow_ajax', (bool) $row->allow_ajax);
			$this->SetSettingsValue('allow_editor', (bool) $row->allow_editor);
			$this->SetSettingsValue('allow_contacts', (bool) $row->allow_contacts);
			$this->SetSettingsValue('allow_calendar', (bool) $row->allow_calendar);
			$this->SetSettingsValue('hide_login_mode', (int) $row->hide_login_mode);
			$this->SetSettingsValue('domain_to_use', $row->domain_to_use);
			$this->SetSettingsValue('allow_choosing_lang', (bool) $row->allow_choosing_lang);
			$this->SetSettingsValue('allow_advanced_login', (bool) $row->allow_advanced_login);
			$this->SetSettingsValue('allow_auto_detect_and_correct', (bool) $row->allow_auto_detect_and_correct);
			$this->SetSettingsValue('use_captcha', (bool) $row->use_captcha);
			$this->SetSettingsValue('use_domain_selection', (bool) $row->use_domain_selection);
			$this->SetSettingsValue('view_mode', (int) $row->view_mode);
			$this->SetSettingsValue('save_mail', (int) $row->save_mail);
		}

		/**
		 * @param	string	$url
		 */
		function SetUrl($url)
		{
			$this->SetSettingsValue('url', $url);
		}

		/**
		 * @return	string
		 */
		function Name()
		{
			return $this->_name;
		}

		function InitBySettings($settings)
		{
			if (!$settings)
			{
				return false;
			}

			$this->SetSettingsValue('site_name', $settings->WindowTitle);
			$this->SetSettingsValue('settings_mail_protocol', (int) $settings->IncomingMailProtocol);
			$this->SetSettingsValue('settings_mail_inc_host', $settings->IncomingMailServer);
			$this->SetSettingsValue('settings_mail_inc_port', (int) $settings->IncomingMailPort);
			$this->SetSettingsValue('settings_mail_out_host', $settings->OutgoingMailServer);
			$this->SetSettingsValue('settings_mail_out_port', (int) $settings->OutgoingMailPort);
			$this->SetSettingsValue('settings_mail_out_auth', (bool) $settings->ReqSmtpAuth);
			$this->SetSettingsValue('allow_direct_mode', (bool) $settings->AllowDirectMode);
			$this->SetSettingsValue('direct_mode_id_def', (bool) $settings->DirectModeIsDefault);
			$this->SetSettingsValue('attachment_size_limit', (int) $settings->AttachmentSizeLimit);
			$this->SetSettingsValue('allow_attachment_limit', (bool) $settings->EnableAttachmentSizeLimit);
			$this->SetSettingsValue('mailbox_size_limit', (int) $settings->MailboxSizeLimit);
			$this->SetSettingsValue('allow_mailbox_limit',(bool) $settings->EnableMailboxSizeLimit);
			$this->SetSettingsValue('take_quota', (bool) $settings->TakeImapQuota);
			$this->SetSettingsValue('allow_new_users_change_set', (bool) $settings->AllowUsersChangeEmailSettings);
			$this->SetSettingsValue('allow_auto_reg_on_login', (bool) $settings->AllowNewUsersRegister);
			$this->SetSettingsValue('allow_users_add_accounts', (bool) $settings->AllowUsersAddNewAccounts);
			$this->SetSettingsValue('allow_users_change_account_def', (bool) $settings->AllowUsersChangeAccountsDef);
			$this->SetSettingsValue('def_user_charset', (int) CWebMail_Plugin::GetCodePageNumber($settings->DefaultUserCharset));
			$this->SetSettingsValue('allow_users_change_charset', (bool) $settings->AllowUsersChangeCharset);
			$this->SetSettingsValue('def_user_timezone', (int) $settings->DefaultTimeZone);
			$this->SetSettingsValue('allow_users_change_timezone', (bool) $settings->AllowUsersChangeTimeZone);
			$this->SetSettingsValue('msgs_per_page', (int) $settings->MailsPerPage);
			$this->SetSettingsValue('skin', $settings->DefaultSkin);
			$this->SetSettingsValue('allow_users_change_skin', (bool) $settings->AllowUsersChangeSkin);
			$this->SetSettingsValue('lang', $settings->DefaultLanguage);
			$this->SetSettingsValue('allow_users_change_lang', (bool) $settings->AllowUsersChangeLanguage);
			$this->SetSettingsValue('show_text_labels', (bool) $settings->ShowTextLabels);
			$this->SetSettingsValue('allow_ajax', true);
			$this->SetSettingsValue('allow_editor', (bool) $settings->AllowDhtmlEditor);
			$this->SetSettingsValue('allow_contacts', (bool) $settings->AllowContacts);
			$this->SetSettingsValue('allow_calendar', (bool) $settings->AllowCalendar);
			$this->SetSettingsValue('hide_login_mode', (int) $settings->HideLoginMode);
			$this->SetSettingsValue('domain_to_use', $settings->DefaultDomainOptional);
			$this->SetSettingsValue('allow_choosing_lang', (bool) $settings->AllowLanguageOnLogin);
			$this->SetSettingsValue('allow_advanced_login', (bool) $settings->AllowAdvancedLogin);
			$this->SetSettingsValue('allow_auto_detect_and_correct', (bool) $settings->AutomaticCorrectLoginSettings);
			$this->SetSettingsValue('use_captcha', (bool) $settings->UseCaptcha);
			$this->SetSettingsValue('use_domain_selection', (bool) $settings->UseMultipleDomainsSelection);
			$this->SetSettingsValue('view_mode', (int) $settings->ViewMode);
			$this->SetSettingsValue('save_mail', (int) $settings->SaveInSent);
		}

		function UpdateSettings(&$settings)
		{
			if (!$settings)
			{
				return false;
			}

			$settings->WindowTitle = $this->GetSettingsValue('site_name');
			$settings->IncomingMailProtocol = (int) $this->GetSettingsValue('settings_mail_protocol');
			$settings->IncomingMailServer = $this->GetSettingsValue('settings_mail_inc_host');
			$settings->IncomingMailPort = (int) $this->GetSettingsValue('settings_mail_inc_port');
			$settings->OutgoingMailServer = $this->GetSettingsValue('settings_mail_out_host');
			$settings->OutgoingMailPort = (int) $this->GetSettingsValue('settings_mail_out_port');
			$settings->ReqSmtpAuth = (bool) $this->GetSettingsValue('settings_mail_out_auth');
			$settings->AllowDirectMode = (bool) $this->GetSettingsValue('allow_direct_mode');
			$settings->DirectModeIsDefault = (bool) $this->GetSettingsValue('direct_mode_id_def');
			$settings->AttachmentSizeLimit = (int) $this->GetSettingsValue('attachment_size_limit');
			$settings->EnableAttachmentSizeLimit = (bool) $this->GetSettingsValue('allow_attachment_limit');
			$settings->MailboxSizeLimit = (int) $this->GetSettingsValue('mailbox_size_limit');
			$settings->EnableMailboxSizeLimit = (bool) $this->GetSettingsValue('allow_mailbox_limit');
			$settings->TakeImapQuota = (bool) $this->GetSettingsValue('take_quota');
			$settings->AllowUsersChangeEmailSettings = (bool) $this->GetSettingsValue('allow_new_users_change_set');
			$settings->AllowNewUsersRegister = (bool) $this->GetSettingsValue('allow_auto_reg_on_login');
			$settings->AllowUsersAddNewAccounts = (bool) $this->GetSettingsValue('allow_users_add_accounts');
			$settings->AllowUsersChangeAccountsDef = (bool) $this->GetSettingsValue('allow_users_change_account_def');
			$settings->DefaultUserCharset = CWebMail_Plugin::GetCodePageName((int) $this->GetSettingsValue('def_user_charset'));
			$settings->AllowUsersChangeCharset = (bool) $this->GetSettingsValue('allow_users_change_charset');
			$settings->DefaultTimeZone = (int) $this->GetSettingsValue('def_user_timezone');
			$settings->AllowUsersChangeTimeZone = (bool) $this->GetSettingsValue('allow_users_change_timezone');
			$settings->MailsPerPage = (int) $this->GetSettingsValue('msgs_per_page');
			$settings->DefaultSkin = $this->GetSettingsValue('skin');
			$settings->AllowUsersChangeSkin = (bool) $this->GetSettingsValue('allow_users_change_skin');
			$settings->DefaultLanguage = $this->GetSettingsValue('lang');
			$settings->AllowUsersChangeLanguage = (bool) $this->GetSettingsValue('allow_users_change_lang');
			$settings->ShowTextLabels = (bool) $this->GetSettingsValue('show_text_labels');
			$settings->AllowAjax = true;
			$settings->AllowDhtmlEditor = (bool) $this->GetSettingsValue('allow_editor');
			$settings->AllowContacts = (bool) $this->GetSettingsValue('allow_contacts');
			$settings->AllowCalendar = (bool) $this->GetSettingsValue('allow_calendar');
			$settings->HideLoginMode = (int) $this->GetSettingsValue('hide_login_mode');
			$settings->DefaultDomainOptional = $this->GetSettingsValue('domain_to_use');
			$settings->AllowLanguageOnLogin = (bool) $this->GetSettingsValue('allow_choosing_lang');
			$settings->AllowAdvancedLogin = (bool) $this->GetSettingsValue('allow_advanced_login');
			$settings->AutomaticCorrectLoginSettings = (bool) $this->GetSettingsValue('allow_auto_detect_and_correct');
			$settings->UseCaptcha = (bool) $this->GetSettingsValue('use_captcha');
			$settings->UseMultipleDomainsSelection = (bool) $this->GetSettingsValue('use_domain_selection');
			$settings->ViewMode = (int) $this->GetSettingsValue('view_mode');
			$settings->SaveInSent = (int) $this->GetSettingsValue('save_mail');
		}
		
		function SetSessionArray()
		{
			$array = array(
'_id' => $this->_id,
'_name' => $this->_name,
'_mailProtocol' => $this->_mailProtocol,
'_mailIncomingHost' => $this->_mailIncomingHost,
'_mailIncomingPort' => $this->_mailIncomingPort,
'_mailSmtpHost' => $this->_mailSmtpHost,
'_mailSmtpPort' => $this->_mailSmtpPort,
'_mailSmtpAuth' => $this->_mailSmtpAuth,
'_isInternal' => $this->_isInternal,
'_globalAddrBook' => $this->_globalAddrBook,
'_ldapAuth' => $this->_ldapAuth
				);
					
			$_SESSION[WM_SESS_DOMAIN] = $array;
		}
		
		function ClearSessionArray()
		{
			if (isset($_SESSION[WM_SESS_DOMAIN]))
			{
				unset($_SESSION[WM_SESS_DOMAIN]);
			}
		}

		/**
		 * @param	string	$name
		 * @return	mixed
		 */
		function GetSettingsValue($name)
		{
			if (isset($this->_settingsValues[$name]))
			{
				return $this->_settingsValues[$name];
			}
			return null;
		}

		/**
		 * @param	string	$name
		 * @param	mixed	$value
		 */
		function SetSettingsValue($name, $value)
		{
			$this->_settingsValues[$name] = $value;
		}
		
		/**
		 * @return bool
		 */
		function IsSessionData()
		{
			return isset($_SESSION[WM_SESS_DOMAIN]);
		}
		
		function UpdateFromSessionArray()
		{
			$sessionArray = isset($_SESSION[WM_SESS_DOMAIN]) ? $_SESSION[WM_SESS_DOMAIN] : array();
			if (count($sessionArray) > 0)
			{
				$this->_id = ap_Utils::ArrayValue($sessionArray, '_id', $this->_id);
				$this->_name = ap_Utils::ArrayValue($sessionArray, '_name', $this->_name);
				
				$this->_mailProtocol = ap_Utils::ArrayValue($sessionArray, '_mailProtocol', $this->_mailProtocol);
				$this->_mailIncomingHost = ap_Utils::ArrayValue($sessionArray, '_mailIncomingHost', $this->_mailIncomingHost);
				$this->_mailIncomingPort = ap_Utils::ArrayValue($sessionArray, '_mailIncomingPort', $this->_mailIncomingPort);
				$this->_mailSmtpHost = ap_Utils::ArrayValue($sessionArray, '_mailSmtpHost', $this->_mailSmtpHost);
				$this->_mailSmtpPort = ap_Utils::ArrayValue($sessionArray, '_mailSmtpPort', $this->_mailSmtpPort);
				$this->_mailSmtpAuth = ap_Utils::ArrayValue($sessionArray, '_mailSmtpAuth', $this->_mailSmtpAuth);
				$this->_isInternal = ap_Utils::ArrayValue($sessionArray, '_isInternal', $this->_isInternal);
				$this->_globalAddrBook = ap_Utils::ArrayValue($sessionArray, '_globalAddrBook', $this->_globalAddrBook);
				$this->_ldapAuth = ap_Utils::ArrayValue($sessionArray, '_ldapAuth', $this->_ldapAuth);

				$this->ClearSessionArray();
			}
		}

		/**
		 * @param Account $account
		 */
		function UpdateAccount(&$account)
		{
			if ($this->_mailProtocol != WM_MAILPROTOCOL_ALL)
			{
				$account->IdDomain = $this->_id;
				$account->MailProtocol = $this->_mailProtocol;
				$account->MailIncHost = $this->_mailIncomingHost;
				$account->MailIncPort = $this->_mailIncomingPort;
				$account->MailOutHost = $this->_mailSmtpHost;
				$account->MailOutPort = $this->_mailSmtpPort;
				$account->MailOutAuthentication = $this->_mailSmtpAuth;

				$this->SetInternal($this->_isInternal);
			}
		}
	}