<?php

class WmFillStandartScreen
{
	function Db(&$_screen, &$_settings, &$_ap)
	{
		$_screen->data->SetValue('intDbTypeValue0', AP_DB_MSSQLSERVER);
		$_screen->data->SetValue('intDbTypeValue1', AP_DB_MYSQL);

		$_screen->data->SetValue('isMySQL_JS',
			@extension_loaded('mysql') ? 'true' : 'false');

		$_screen->data->SetValue('isMSSQL_JS',
			@extension_loaded('mssql') ? 'true' : 'false');

		$_screen->data->SetValue('isODBC_JS',
			@extension_loaded('odbc') ? 'true' : 'false');

		switch ($_settings->DbType)
		{
			case AP_DB_MSSQLSERVER:
				$_screen->data->SetValue('intDbType0', true);
				break;
			default:
			case AP_DB_MYSQL:
				$_screen->data->SetValue('intDbType1', true);
				break;
		}

		$_screen->data->SetValue('txtSqlLogin', $_settings->DbLogin);
		if (strlen($_settings->DbPassword) > 0)
		{
			$_screen->data->SetValue('txtSqlPassword', AP_DUMMYPASSWORD);
		}
		$_screen->data->SetValue('txtSqlName', $_settings->DbName);

		$_screen->data->SetValue('txtSqlSrc', $_settings->DbHost);

		if ($_settings->UseCustomConnectionString)
		{
			$_screen->data->SetValue('useCS', $_settings->UseCustomConnectionString);
		}
		else
		{
			$_screen->data->SetValue('useDSN', $_settings->UseDsn);
		}

		$_screen->data->SetValue('txtSqlDsn', $_settings->DbDsn);
		$_screen->data->SetValue('odbcConnectionString', $_settings->DbCustomConnectionString);

		if ($this->_useDbCreate || $_ap->IsEnable())
		{
			$_screen->data->SetValue('txtCreateDropDb', '<br /><br />
			<input type="button" name="backup_btn" id="update_btn" value="Backup" class="wm_button" style="font-size: 11px;" onclick="PopUpWindow(\''.AP_INDEX_FILE.'?mode=pop&type=db&action=backup\');" />
			<input type="submit" name="create_db" id="create_db" value="Create Database" class="wm_button" style="font-size: 11px;" title="Create Database" />
			<input type="submit" name="drop_db" id="drop_db" value="Drop Database" class="wm_button" style="font-size: 11px;" title="Drop Database" />');
		}
	}

	function Common(&$_screen, &$_settings, $_host = '')
	{
		if (strlen($_host) > 0)
		{
			$_screen->data->SetValue('txtDefaultClassUrl', 'wm_hide');
		}
		else
		{
			$_screen->data->SetValue('txtDomainClassUrl', 'wm_hide');
		}
		
		$_screen->data->SetValue('txtFilterHost', $_host);

		$_screen->data->SetValue('txtSiteName', $_settings->WindowTitle);
		$_screen->data->SetValue('txtIncomingMail', $_settings->IncomingMailServer);
		$_screen->data->SetValue('intIncomingMailPort', $_settings->IncomingMailPort);

		$_screen->data->SetValue('intIncomingMailProtocolPop3Value', WM_MAILPROTOCOL_POP3);
		$_screen->data->SetValue('intIncomingMailProtocolImap4Value', WM_MAILPROTOCOL_IMAP4);
		switch ($_settings->IncomingMailProtocol)
		{
			default:
			case WM_MAILPROTOCOL_POP3:
				$_screen->data->SetValue('intIncomingMailProtocol0', $_settings->IncomingMailPort);
				break;
			case WM_MAILPROTOCOL_IMAP4:
				$_screen->data->SetValue('intIncomingMailProtocol1', $_settings->IncomingMailPort);
				break;
		}

		$_screen->data->SetValue('txtOutgoingMail', $_settings->OutgoingMailServer);
		$_screen->data->SetValue('intOutgoingMailPort', $_settings->OutgoingMailPort);

		$_screen->data->SetValue('intReqSmtpAuthentication', $_settings->ReqSmtpAuth);
		$_screen->data->SetValue('intAllowDirectMode', $_settings->AllowDirectMode);
		$_screen->data->SetValue('intDirectModeIsDefault', $_settings->DirectModeIsDefault);

		$_screen->data->SetValue('intAttachmentSizeLimit', ceil($_settings->AttachmentSizeLimit / 1024));
		$_screen->data->SetValue('intEnableAttachSizeLimit', $_settings->EnableAttachmentSizeLimit);

		$_screen->data->SetValue('intMailboxSizeLimit', ceil($_settings->MailboxSizeLimit / 1024));
		$_screen->data->SetValue('intEnableMailboxSizeLimit', $_settings->EnableMailboxSizeLimit);

		$_screen->data->SetValue('intTakeImapQuota', $_settings->TakeImapQuota);

		$_screen->data->SetValue('intAllowUsersChangeEmailSettings', $_settings->AllowUsersChangeEmailSettings);
		$_screen->data->SetValue('intAllowNewUsersRegister', $_settings->AllowNewUsersRegister);
		$_screen->data->SetValue('intAllowUsersAddNewAccounts', $_settings->AllowUsersAddNewAccounts);
		$_screen->data->SetValue('intAllowUsersChangeAccountsDef', $_settings->AllowUsersChangeAccountsDef);

		$_charsets =& $this->GetCharsetsList();
		$_charsetsString = '';
		foreach ($_charsets as $_charset)
		{
			$_selected = ($_charset[0] == $_settings->DefaultUserCharset) ? ' selected="selected"' : '';
			$_charsetsString .= '<option value="'.ap_Utils::AttributeQuote($_charset[0]).'"'.$_selected.'>'.ap_Utils::EncodeSpecialXmlChars($_charset[1]).'</option>'.AP_CRLF;
		}
		$_screen->data->SetValue('txtDefaultUserCharset', $_charsetsString);
		$_screen->data->SetValue('intAllowUsersChangeCharset', $_settings->AllowUsersChangeCharset);

		$_timezones =& $this->GetTimeZoneList();
		$_timezonesString = '';
		foreach ($_timezones as $_timezoneKey => $_timezoneValue)
		{
			$_selected = ($_timezoneKey == $_settings->DefaultTimeZone) ? ' selected="selected"' : '';
			$_timezonesString .= '<option value="'.ap_Utils::AttributeQuote($_timezoneKey).'"'.$_selected.'>'.ap_Utils::EncodeSpecialXmlChars($_timezoneValue).'</option>'.AP_CRLF;
		}
		$_screen->data->SetValue('txtDefaultTimeZone', $_timezonesString);
		$_screen->data->SetValue('intAllowUsersChangeTimeZone', $_settings->AllowUsersChangeTimeZone);
	}

	function WmInterface(&$_screen, &$_settings, $_host = '')
	{
		$_screen->data->SetValue('intMailsPerPage', $_settings->MailsPerPage);
		
		$_screen->data->SetValue('intRightMessagePane', $_settings->ViewMode & WM_NEW_VIEW_MODE_CENTRAL_LIST_PANE);
		$_screen->data->SetValue('intAlwaysShowPictures', $_settings->ViewMode & WM_NEW_VIEW_MODE_SHOW_PICTURES);

		$_skins =& $this->GetSkinsList();
		$_skinsString = '';
		foreach ($_skins as $_skin)
		{
			$_selected = ($_skin == $_settings->DefaultSkin) ? ' selected="selected"' : '';
			$_skinsString .= '<option value="'.ap_Utils::AttributeQuote($_skin).'"'.$_selected.'>'.ap_Utils::EncodeSpecialXmlChars($_skin).'</option>'.AP_CRLF;
		}
		$_screen->data->SetValue('txtDefaultSkin', $_skinsString);
		$_screen->data->SetValue('intAllowUsersChangeSkin', $_settings->AllowUsersChangeSkin);

		$_langs =& $this->GetLangsList();
		$_langsString = '';
		foreach ($_langs as $_lang)
		{
			$_selected = ($_lang == $_settings->DefaultLanguage) ? ' selected="selected"' : '';
			$_langsString .= '<option value="'.ap_Utils::AttributeQuote($_lang).'"'.$_selected.'>'.ap_Utils::EncodeSpecialXmlChars($_lang).'</option>'.AP_CRLF;
		}
		$_screen->data->SetValue('txtDefaultLanguage', $_langsString);
		$_screen->data->SetValue('intAllowUsersChangeLanguage', $_settings->AllowUsersChangeLanguage);

		$_screen->data->SetValue('intShowTextLabels', $_settings->ShowTextLabels);
		$_screen->data->SetValue('intAllowAjaxVersion', $_settings->AllowAjax);
		$_screen->data->SetValue('intAllowDHTMLEditor', $_settings->AllowDhtmlEditor);
		$_screen->data->SetValue('intAllowContacts', $_settings->AllowContacts);
		$_screen->data->SetValue('intAllowCalendar', $_settings->AllowCalendar);
		$_screen->data->SetValue('classAllowCalendar', CAdminPanel::UseDb() ? '' : 'wm_hide');

		$_screen->data->SetValue('classAllowCalendar', CAdminPanel::UseDb() ? '' : 'wm_hide');

		$_screen->data->SetValue('SaveInSentAlwaysIntValue', WM_SAVE_IN_SENT_ALWAYS);
		$_screen->data->SetValue('SaveInSentOnIntValue', WM_SAVE_IN_SENT_DEFAULT_ON);
		$_screen->data->SetValue('SaveInSentOffIntValue', WM_SAVE_IN_SENT_DEFAULT_OFF);

		$_screen->data->SetValue('SaveInSentAlways', WM_SAVE_IN_SENT_ALWAYS === $_settings->SaveInSent);
		$_screen->data->SetValue('SaveInSentOn', WM_SAVE_IN_SENT_DEFAULT_ON === $_settings->SaveInSent);
		$_screen->data->SetValue('SaveInSentOff', WM_SAVE_IN_SENT_DEFAULT_OFF === $_settings->SaveInSent);
	}

	function Login(&$_screen, &$_settings, $host, $isDomainsExist = false)
	{
		$_hlm = (string) $_settings->HideLoginMode;
		$_hlm_len = strlen($_hlm);
		if ($_hlm_len > 0)
		{
			switch ($_hlm{0})
			{
				case '0':
					$_screen->data->SetValue('hideLoginRadionButton1', true);
					break;
				case '1':
					$_screen->data->SetValue('hideLoginRadionButton2', true);
					if ($_hlm_len > 1)
					{
						switch ($_hlm{1})
						{
							case '0':
								$_screen->data->SetValue('hideLoginSelect0', true);
								break;
							case '1':
								$_screen->data->SetValue('hideLoginSelect1', true);
								break;
						}
					}
					break;
				case '2':
					$_screen->data->SetValue('hideLoginRadionButton3', true);
					if ($_hlm_len > 1)
					{
						switch ($_hlm{1})
						{
							case '1':
								$_screen->data->SetValue('intDisplayDomainAfterLoginField', true);
								break;
							case '2':
								$_screen->data->SetValue('intLoginAsConcatination', true);
								break;
							case '3':
								$_screen->data->SetValue('intDisplayDomainAfterLoginField', true);
								$_screen->data->SetValue('intLoginAsConcatination', true);
								break;
						}
					}
					break;
			}
		}

		$useMultipleDomainsSelection = ($isDomainsExist) ? $_settings->UseMultipleDomainsSelection : false;

		$_screen->data->SetValue('intDomainsExistValue', $isDomainsExist ? '1' : '0');
		$_screen->data->SetValue('intDomainDisplayType1', $useMultipleDomainsSelection);
		$_screen->data->SetValue('intDomainDisplayType2', !$useMultipleDomainsSelection);
				
		$_screen->data->SetValue('txtUseDomain', $_settings->DefaultDomainOptional);
		$_screen->data->SetValue('intAllowAdvancedLogin', $_settings->AllowAdvancedLogin);
		$_screen->data->SetValue('intAutomaticHideLogin', $_settings->AutomaticCorrectLoginSettings);
		$_screen->data->SetValue('intAllowLangOnLogin', $_settings->AllowLanguageOnLogin);
		if (function_exists('imagecreatefrompng'))
		{
			$_screen->data->SetValue('intUseCaptcha', $_settings->UseCaptcha);
			$_screen->data->SetValue('classUseCaptchaError', 'wm_hide');
		}
		else
		{
			$_screen->data->SetValue('intUseCaptchaDisabled', true);
			$_screen->data->SetValue('styleUseCaptchaLabel', 'color: #aaaaaa');
		}
	}

	function Contacts(&$_screen, &$_settings)
	{
		$sGlobalAddressBook = $_settings->GlobalAddressBook;
		switch ($sGlobalAddressBook)
		{
			case WM_GLOBAL_ADDRESS_BOOK_SYSTEM:
				$_screen->data->SetValue('dGlobalAddressBookAreaSystem', true);
				break;
			default:
			case WM_GLOBAL_ADDRESS_BOOK_DOMAIN:
				$_screen->data->SetValue('dGlobalAddressBookAreaDomain', true);
				break;
			case WM_GLOBAL_ADDRESS_BOOK_OFF:
				$_screen->data->SetValue('dGlobalAddressBookAreaOff', true);
				break;
		}
	}

	function Cal(&$_screen, &$_settings)
	{
		$_screen->data->SetValue('defTimeFormat0', ($_settings->Cal_DefaultTimeFormat == 1));
		$_screen->data->SetValue('defTimeFormat1', ($_settings->Cal_DefaultTimeFormat == 2));

		$_timestamp = time();
		if ((int) date('m', $_timestamp) == (int) date('d', $_timestamp))
		{
			$_timestamp += 86400;
		}

		$_screen->data->SetValue('defDateFormatValue1', date('m/d/Y', $_timestamp));
		$_screen->data->SetValue('defDateFormatValue2', date('d/m/Y', $_timestamp));
		$_screen->data->SetValue('defDateFormatValue3', date('Y-m-d', $_timestamp));
		$_screen->data->SetValue('defDateFormatValue4', date('M d, Y', $_timestamp));
		$_screen->data->SetValue('defDateFormatValue5', date('d M Y', $_timestamp));

		switch ($_settings->Cal_DefaultDateFormat)
		{
			case 1: $_screen->data->SetValue('defDateFormat1', true); break;
			case 2: $_screen->data->SetValue('defDateFormat2', true); break;
			case 3: $_screen->data->SetValue('defDateFormat3', true); break;
			case 4: $_screen->data->SetValue('defDateFormat4', true); break;
			case 5: $_screen->data->SetValue('defDateFormat5', true); break;
		}

		$_screen->data->SetValue('showWeekends', $_settings->Cal_ShowWeekends);
		$_screen->data->SetValue('showWorkDay', $_settings->Cal_ShowWorkDay);

		switch ($_settings->Cal_WeekStartsOn)
		{
			case 0: $_screen->data->SetValue('weekStartsOn0', true); break;
			case 1: $_screen->data->SetValue('weekStartsOn1', true); break;
		}

		switch ($_settings->Cal_DefaultTab)
		{
			case 1: $_screen->data->SetValue('defTab1', true); break;
			case 2: $_screen->data->SetValue('defTab2', true); break;
			case 3: $_screen->data->SetValue('defTab3', true); break;
		}

		$_country = '';
		if (@file_exists($_settings->_path.'/country/country.dat'))
		{
			$_countryCode = $_countryName = '';
			$_i = 1;
			$_fp = @fopen($_settings->_path.'/country/country.dat', 'r');
			if ($_fp)
			{
				while (!feof($_fp))
				{
					$_str = trim(fgets($_fp));
					list($_countryCode, $_countryName) = explode('-', $_str);
					$_country .= '<option value="'.ap_Utils::AttributeQuote($_countryCode).'"';
					if ($_settings->Cal_DefaultCountry == $_countryCode)
					{
						$_country .= ' selected="selected"';
					}
					$_country .= '>'.ap_Utils::EncodeSpecialXmlChars($_countryName).'</option>'.AP_CRLF;
					$_i++;
					if ($_i > 300)
					{
						break;
					}
				}
				@fclose($_fp);
			}
		}

		$_screen->data->SetValue('Country_dat', $_country);

		$_screen->data->SetValue('allTimeZones', $_settings->Cal_AllTimeZones);

		$_screen->data->SetValue('Cal_WorkdayStarts', $_settings->Cal_WorkdayStarts);
		$_screen->data->SetValue('Cal_WorkdayEnds', $_settings->Cal_WorkdayEnds);
		$_screen->data->SetValue('Cal_DefaultTimeFormat', $_settings->Cal_DefaultTimeFormat);
		$_screen->data->SetValue('Cal_DefaultTimeZone', $_settings->Cal_DefaultTimeZone);
		$_screen->data->SetValue('CheckWorkdayTimeError', ap_Utils::TakePhrase('WM_INFO_CAL_CHECKWORKDAYTIMEERROR'));
		
		$_screen->data->SetValue('allowReminder', $_settings->Cal_AllowReminders);
	}

	function Debug(&$_screen, &$_settings)
	{
		$_fileName = $_settings->_path.'/logs/log_'.date('Y-m-d').'.txt';

		$_screen->data->SetValue('intEnableLogging', $_settings->EnableLogging);
		$_screen->data->SetValue('txtPathForLog', $_fileName);

		$_size = 0;
		if (@file_exists($_fileName))
		{
			$_size = @filesize($_fileName);
		}
		$_size = ap_Utils::GetFriendlySize($_size);
		$_screen->data->SetValue('txtLogSize', $_size);

		$optLogLevel = '';

		$LOG_LEVELS = array (
			WM_LOG_LEVEL_DEBUG => 'Full debug',
			WM_LOG_LEVEL_WARNING => 'Warning',
			WM_LOG_LEVEL_ERROR => 'Error'
		);

		foreach ($LOG_LEVELS as $level => $name)
		{
			$isSelected = ($level == $_settings->LogLevel) ? ' selected="selected" ' : '';
			$optLogLevel .= '<option value="'.$level.'"'.$isSelected.'>'.$name.'</option>';
		}

		$_screen->data->SetValue('optLogLevel', $optLogLevel);

		$_fileName = $_settings->_path.'/logs/events_'.date('Y-m-d').'.txt';
		$_screen->data->SetValue('intEnableEventLogging', $_settings->EnableEventsLogging);
		$_screen->data->SetValue('txtPathForEventLog', $_fileName);

		$_size = 0;
		if (@file_exists($_fileName))
		{
			$_size = @filesize($_fileName);
		}
		$_size = ap_Utils::GetFriendlySize($_size);
		$_screen->data->SetValue('txtEventLogSize', $_size);
	}

	function Mobile(&$_screen, &$_settings)
	{
		$_screen->data->SetValue('txtPathForMobileSync', $_settings->MobileSyncUrl);
		$_screen->data->SetValue('txtMobileSyncContactDataBase', $_settings->MobileSyncContactDataBase);
		$_screen->data->SetValue('txtMobileSyncCalendarDataBase', $_settings->MobileSyncCalendarDataBase);
		if (function_exists('mcrypt_encrypt'))
		{
			$_screen->data->SetValue('chEnableMobileSync', $_settings->EnableMobileSync);
			$_screen->data->SetValue('classEnableMobileSyncError', 'wm_hide');
		}
		else
		{
			$_screen->data->SetValue('chEnableMobileSync', false);
			$_screen->data->SetValue('intEnableMobileSyncDisabled', true);
			$_screen->data->SetValue('styleEnableMobileSyncLabel', 'color: #aaaaaa');
		}
	}

	function Integration(&$_screen, &$_settings)
	{
		$_screen->data->SetValue('intEnableWmServer', $_settings->EnableWmServer);
		$_screen->data->SetValue('txtWmServerRootPath', $_settings->WmServerRootPath);
		$_screen->data->SetValue('txtWmServerHostName', $_settings->WmServerHost);
		$_screen->data->SetValue('intWmAllowManageXMailAccounts', $_settings->WmAllowManageXMailAccounts);
	}
}

class WmFillSettingsFromPost
{
	function Db(&$_settings)
	{
		if (isset($_POST['intDbType']))
		{
			switch ($_POST['intDbType'])
			{
				case AP_DB_MSSQLSERVER:
				case AP_DB_MYSQL:
					$_settings->DbType = (int) $_POST['intDbType'];
					break;
				default:
					$_settings->DbType = AP_DB_MYSQL;
					break;
			}
		}

		$_settings->DbLogin = isset($_POST['txtSqlLogin']) ? $_POST['txtSqlLogin'] : $_settings->DbLogin;
		if (isset($_POST['txtSqlPassword']))
		{
			if ($_POST['txtSqlPassword'] != AP_DUMMYPASSWORD)
			{
				$_settings->DbPassword = $_POST['txtSqlPassword'];
			}
		}
		$_settings->DbName = isset($_POST['txtSqlName']) ? $_POST['txtSqlName'] : $_settings->DbName;
		$_settings->DbHost = isset($_POST['txtSqlSrc']) ? $_POST['txtSqlSrc'] : $_settings->DbHost;


		$_settings->UseDsn = (isset($_POST['useDSN']) && $_POST['useDSN'] == 1);
		$_settings->DbDsn = isset($_POST['txtSqlDsn']) ? $_POST['txtSqlDsn'] : $_settings->DbDsn;

		$_settings->DbCustomConnectionString = isset($_POST['odbcConnectionString']) ? $_POST['odbcConnectionString'] : $_settings->DbCustomConnectionString;
		$_settings->UseCustomConnectionString = (isset($_POST['useCS']) && $_POST['useCS'] == 1);

	}

	function Common(&$_settings)
	{
		$_settings->WindowTitle = isset($_POST['txtSiteName']) ? $_POST['txtSiteName'] : $_settings->WindowTitle;

		$_settings->IncomingMailServer = isset($_POST['txtIncomingMail']) ? $_POST['txtIncomingMail'] : $_settings->IncomingMailServer;
		$_settings->IncomingMailPort = isset($_POST['intIncomingMailPort']) ? (int) $_POST['intIncomingMailPort'] : $_settings->IncomingMailPort;

		if (isset($_POST['intIncomingMailProtocol']))
		{
			switch ($_POST['intIncomingMailProtocol'])
			{
				case WM_MAILPROTOCOL_IMAP4:
				case WM_MAILPROTOCOL_POP3:
					$_settings->IncomingMailProtocol = (int) $_POST['intIncomingMailProtocol'];
					break;
				default:
					$_settings->IncomingMailProtocol = WM_MAILPROTOCOL_POP3;
					break;
			}
		}

		$_settings->OutgoingMailServer = isset($_POST['txtOutgoingMail']) ? $_POST['txtOutgoingMail'] : $_settings->OutgoingMailServer;
		$_settings->OutgoingMailPort = isset($_POST['intOutgoingMailPort']) ? (int) $_POST['intOutgoingMailPort'] : $_settings->OutgoingMailPort;

		$_settings->ReqSmtpAuth = (isset($_POST['intReqSmtpAuthentication']) && $_POST['intReqSmtpAuthentication'] == 1);

		$_settings->AllowDirectMode = (isset($_POST['intAllowDirectMode']) && $_POST['intAllowDirectMode'] == 1);
		$_settings->DirectModeIsDefault = (isset($_POST['intDirectModeIsDefault']) && $_POST['intDirectModeIsDefault'] == 1);

		if (isset($_POST['intAttachmentSizeLimit']) && strlen($_POST['intAttachmentSizeLimit']) < 8)
		{
			$_settings->AttachmentSizeLimit = GetGoodBigInt($_POST['intAttachmentSizeLimit'] * 1024);
		}
		$_settings->EnableAttachmentSizeLimit = (isset($_POST['intEnableAttachSizeLimit']) && $_POST['intEnableAttachSizeLimit'] == 1);

		if (isset($_POST['intMailboxSizeLimit']) && strlen($_POST['intMailboxSizeLimit']) < 8)
		{
			$_settings->MailboxSizeLimit = GetGoodBigInt($_POST['intMailboxSizeLimit'] * 1024);
		}
		$_settings->EnableMailboxSizeLimit = (isset($_POST['intEnableMailboxSizeLimit']) && $_POST['intEnableMailboxSizeLimit'] == 1);

		$_settings->TakeImapQuota = (isset($_POST['intTakeImapQuota']) && $_POST['intTakeImapQuota'] == 1);

		$_settings->AllowUsersChangeEmailSettings = (isset($_POST['intAllowUsersChangeEmailSettings']) && $_POST['intAllowUsersChangeEmailSettings'] == 1);
		$_settings->AllowNewUsersRegister = (isset($_POST['intAllowNewUsersRegister']) && $_POST['intAllowNewUsersRegister'] == 1);
		$_settings->AllowUsersAddNewAccounts = (isset($_POST['intAllowUsersAddNewAccounts']) && $_POST['intAllowUsersAddNewAccounts'] == 1);
		$_settings->AllowUsersChangeAccountsDef = (isset($_POST['intAllowUsersChangeAccountsDef']) && $_POST['intAllowUsersChangeAccountsDef'] == 1);

		$_settings->DefaultUserCharset = isset($_POST['txtDefaultUserCharset']) ? $_POST['txtDefaultUserCharset'] : $_settings->DefaultUserCharset;
		$_settings->AllowUsersChangeCharset = (isset($_POST['intAllowUsersChangeCharset']) && $_POST['intAllowUsersChangeCharset'] == 1);
		$_settings->DefaultTimeZone = isset($_POST['txtDefaultTimeZone']) ? $_POST['txtDefaultTimeZone'] : $_settings->DefaultTimeZone;
		$_settings->AllowUsersChangeTimeZone = (isset($_POST['intAllowUsersChangeTimeZone']) && $_POST['intAllowUsersChangeTimeZone'] == 1);
	}

	function WmInterface(&$_settings)
	{
		$_settings->MailsPerPage = isset($_POST['intMailsPerPage']) ? (int) $_POST['intMailsPerPage'] : $_settings->MailsPerPage;
		$_settings->DefaultSkin = isset($_POST['txtDefaultSkin']) ? $_POST['txtDefaultSkin'] : $_settings->DefaultSkin;
		$_settings->AllowUsersChangeSkin = (isset($_POST['intAllowUsersChangeSkin']) && $_POST['intAllowUsersChangeSkin'] == 1);
		$_settings->DefaultLanguage = isset($_POST['txtDefaultLanguage']) ? $_POST['txtDefaultLanguage'] : $_settings->DefaultLanguage;
		$_settings->AllowUsersChangeLanguage = (isset($_POST['intAllowUsersChangeLanguage']) && $_POST['intAllowUsersChangeLanguage'] == 1);
		$_settings->ShowTextLabels = (isset($_POST['intShowTextLabels']) && $_POST['intShowTextLabels'] == 1);
		$_settings->AllowAjax = true;
		$_settings->AllowDhtmlEditor = (isset($_POST['intAllowDHTMLEditor']) && $_POST['intAllowDHTMLEditor'] == 1);
		$_settings->AllowContacts = (isset($_POST['intAllowContacts']) && $_POST['intAllowContacts'] == 1);
		$_settings->AllowCalendar = (isset($_POST['intAllowCalendar']) && $_POST['intAllowCalendar'] == 1);

		$_settings->ViewMode =
			((int) (isset($_POST['intRightMessagePane']) && $_POST['intRightMessagePane'] == 1)) * WM_NEW_VIEW_MODE_CENTRAL_LIST_PANE
			|
			((int) (isset($_POST['intAlwaysShowPictures']) && $_POST['intAlwaysShowPictures'] == 1)) * WM_NEW_VIEW_MODE_SHOW_PICTURES;

		$_settings->SaveInSent = (isset($_POST['selSaveInSent'])) ? (int) $_POST['selSaveInSent'] : $_settings->SaveInSent;
	}

	function Login(&$_settings)
	{
		$_settings->AllowAdvancedLogin = (isset($_POST['intAllowAdvancedLogin']) && $_POST['intAllowAdvancedLogin'] == 1);
		$_settings->DefaultDomainOptional = isset($_POST['txtUseDomain']) ? $_POST['txtUseDomain'] : $_settings->DefaultDomainOptional;
		$_settings->AllowLanguageOnLogin = (isset($_POST['intAllowLangOnLogin']) && $_POST['intAllowLangOnLogin'] == 1);
		$_settings->AutomaticCorrectLoginSettings = (isset($_POST['intAutomaticHideLogin']) && $_POST['intAutomaticHideLogin'] == 1);

		$_hideLoginMode = 0;
		if (isset($_POST['hideLoginRadionButton']))
		{
			switch ($_POST['hideLoginRadionButton'])
			{
				case '0': break;
				case '1':
					$_hideLoginMode = 10;
					if (isset($_POST['hideLoginSelect']) && $_POST['hideLoginSelect'] == '1')
					{
						$_hideLoginMode++;
					}
					break;
				case '2':
					$_hideLoginMode = 20;
					if (isset($_POST['intDisplayDomainAfterLoginField']) && $_POST['intDisplayDomainAfterLoginField'] == 1)
					{
						$_hideLoginMode++;
					}
					if (isset($_POST['intLoginAsConcatination']) && $_POST['intLoginAsConcatination'] == 1)
					{
						$_hideLoginMode = $_hideLoginMode + 2;
					}
					break;
			}
		}

		$_settings->UseMultipleDomainsSelection = (isset($_POST['intDomainDisplayType']) && $_POST['intDomainDisplayType'] == 1);
		$_settings->HideLoginMode = $_hideLoginMode;
		$_settings->UseCaptcha = (isset($_POST['intUseCaptcha']) && $_POST['intUseCaptcha'] == 1);
	}

	function Contacts(&$_settings)
	{
		$_settings->GlobalAddressBook = isset($_POST['bGlobalAddressBookArea']) ? $_POST['bGlobalAddressBookArea'] : $_settings->GlobalAddressBook;
	}
	
	function Cal(&$_settings)
	{
		$_settings->Cal_DefaultTimeFormat = isset($_POST['defTimeFormat']) ? $_POST['defTimeFormat'] : $_settings->Cal_DefaultTimeFormat;
		$_settings->Cal_DefaultDateFormat = isset($_POST['defDateFormat']) ? $_POST['defDateFormat'] : $_settings->Cal_DefaultDateFormat;
		$_settings->Cal_ShowWeekends = ((isset($_POST['showWeekends']) && $_POST['showWeekends'] == 1) ? 1 : 0);
		$_settings->Cal_WorkdayStarts = isset($_POST['WorkdayStarts']) ? $_POST['WorkdayStarts'] : $_settings->Cal_WorkdayStarts;
		$_settings->Cal_WorkdayEnds = isset($_POST['WorkdayEnds']) ? $_POST['WorkdayEnds'] : $_settings->Cal_WorkdayEnds;
		$_settings->Cal_ShowWorkDay = ((isset($_POST['showWorkDay']) && $_POST['showWorkDay'] == 1) ? 1 : 0);
		$_settings->Cal_WeekStartsOn = isset($_POST['weekStartsOn']) ? $_POST['weekStartsOn'] : $_settings->Cal_WeekStartsOn;
		$_settings->Cal_DefaultTab = isset($_POST['defTab']) ? $_POST['defTab'] : $_settings->Cal_DefaultTab;
		$_settings->Cal_DefaultCountry = isset($_POST['defCountry']) ? $_POST['defCountry'] : $_settings->Cal_DefaultCountry;
		$_settings->Cal_DefaultTimeZone = isset($_POST['defTimeZone']) ? $_POST['defTimeZone'] : $_settings->Cal_DefaultTimeZone;
		$_settings->Cal_AllTimeZones = ((isset($_POST['allTimeZones']) && $_POST['allTimeZones'] == 1) ? 1 : 0);
		$_settings->Cal_AllowReminders = ((isset($_POST['allowReminder']) && $_POST['allowReminder'] == 1));
	}

	function Integr(&$_settings)
	{
		$_settings->WmServerRootPath = isset($_POST['txtWmServerRootPath']) ? ap_Utils::PathPreparation($_POST['txtWmServerRootPath']) : $_settings->WmServerRootPath;
		$_settings->WmServerHost = isset($_POST['txtWmServerHostName']) ? $_POST['txtWmServerHostName'] : $_settings->WmServerHost;
		$_settings->WmAllowManageXMailAccounts = (isset($_POST['intWmAllowManageXMailAccounts']) && $_POST['intWmAllowManageXMailAccounts'] == 1);
	}

	function Debug(&$_settings)
	{
		$_settings->EnableLogging = (isset($_POST['intEnableLogging']) && $_POST['intEnableLogging'] == 1);
		$_settings->EnableEventsLogging = (isset($_POST['intEnableEventLogging']) && $_POST['intEnableEventLogging'] == 1);
		$_settings->LogLevel = isset($_POST['intLogLevel']) ? $_POST['intLogLevel'] : WM_LOG_LEVEL_DEBUG;
	}

	function Mobile(&$_settings)
	{
		$_settings->EnableMobileSync = (isset($_POST['chEnableMobileSync']) && $_POST['chEnableMobileSync'] == 1);
		$_settings->MobileSyncUrl = isset($_POST['txtPathForMobileSync']) ? $_POST['txtPathForMobileSync'] : $_settings->MobileSyncUrl;
		$_settings->MobileSyncContactDataBase = isset($_POST['txtMobileSyncContactDataBase']) ? $_POST['txtMobileSyncContactDataBase'] : $_settings->MobileSyncContactDataBase;
		$_settings->MobileSyncCalendarDataBase = isset($_POST['txtMobileSyncCalendarDataBase']) ? $_POST['txtMobileSyncCalendarDataBase'] : $_settings->MobileSyncCalendarDataBase;
	}
}

class WMResetSettingsByScreen
{
	function Common(&$defaultSettings, &$resetSettings)
	{
		$resetSettings->WindowTitle = $defaultSettings->WindowTitle;
		$resetSettings->IncomingMailServer = $defaultSettings->IncomingMailServer;
		$resetSettings->IncomingMailPort = $defaultSettings->IncomingMailPort;
		$resetSettings->IncomingMailProtocol = $defaultSettings->IncomingMailProtocol;
		$resetSettings->OutgoingMailServer = $defaultSettings->OutgoingMailServer;
		$resetSettings->OutgoingMailPort = $defaultSettings->OutgoingMailPort;
		$resetSettings->ReqSmtpAuth = $defaultSettings->ReqSmtpAuth;
		$resetSettings->AllowDirectMode = $defaultSettings->AllowDirectMode;
		$resetSettings->DirectModeIsDefault = $defaultSettings->DirectModeIsDefault;
		$resetSettings->AttachmentSizeLimit = $defaultSettings->AttachmentSizeLimit;
		$resetSettings->EnableAttachmentSizeLimit = $defaultSettings->EnableAttachmentSizeLimit;
		$resetSettings->MailboxSizeLimit = $defaultSettings->MailboxSizeLimit;
		$resetSettings->EnableMailboxSizeLimit = $defaultSettings->EnableMailboxSizeLimit;
		$resetSettings->TakeImapQuota = $defaultSettings->TakeImapQuota;
		$resetSettings->AllowUsersChangeEmailSettings = $defaultSettings->AllowUsersChangeEmailSettings;
		$resetSettings->AllowNewUsersRegister = $defaultSettings->AllowNewUsersRegister;
		$resetSettings->AllowUsersAddNewAccounts = $defaultSettings->AllowUsersAddNewAccounts;
		$resetSettings->AllowUsersChangeAccountsDef = $defaultSettings->AllowUsersChangeAccountsDef;
		$resetSettings->DefaultUserCharset = $defaultSettings->DefaultUserCharset;
		$resetSettings->AllowUsersChangeCharset = $defaultSettings->AllowUsersChangeCharset;
		$resetSettings->DefaultTimeZone = $defaultSettings->DefaultTimeZone;
		$resetSettings->AllowUsersChangeTimeZone = $defaultSettings->AllowUsersChangeTimeZone;
	}

	function WmInterface(&$defaultSettings, &$resetSettings)
	{
		$resetSettings->MailsPerPage = $defaultSettings->MailsPerPage;
		$resetSettings->DefaultSkin = $defaultSettings->DefaultSkin;
		$resetSettings->AllowUsersChangeSkin = $defaultSettings->AllowUsersChangeSkin;
		$resetSettings->DefaultLanguage = $defaultSettings->DefaultLanguage;
		$resetSettings->AllowUsersChangeLanguage = $defaultSettings->AllowUsersChangeLanguage;
		$resetSettings->ShowTextLabels = $defaultSettings->ShowTextLabels;
		$resetSettings->AllowAjax = $defaultSettings->AllowAjax;
		$resetSettings->AllowDhtmlEditor = $defaultSettings->AllowDhtmlEditor;
		$resetSettings->AllowContacts = $defaultSettings->AllowContacts;
		$resetSettings->AllowCalendar = $defaultSettings->AllowCalendar;
		$resetSettings->ViewMode = $defaultSettings->ViewMode;
	}

	function Login(&$defaultSettings, &$resetSettings)
	{
		$resetSettings->AllowAdvancedLogin = $defaultSettings->AllowAdvancedLogin;
		$resetSettings->DefaultDomainOptional = $defaultSettings->DefaultDomainOptional;
		$resetSettings->AllowLanguageOnLogin = $defaultSettings->AllowLanguageOnLogin;
		$resetSettings->AutomaticCorrectLoginSettings = $defaultSettings->AutomaticCorrectLoginSettings;
		$resetSettings->HideLoginMode = $defaultSettings->HideLoginMode;
	}
}

class WmMainFillClass
{
	/**
	 * @param	Account	$account
	 * @param	int		$synchronize
	 */
	function AccountFromPost(&$_account, &$_synchronize)
	{
		$_account->MailboxSize = 0;
		if (isset($_POST['intLimitMailbox']) && strlen($_POST['intLimitMailbox']) < 8)
		{
			$_account->MailboxLimit = GetGoodBigInt(round($_POST['intLimitMailbox'] * 1024));
		}

		if (isset($_POST['MaxMailboxSize']) && strlen($_POST['MaxMailboxSize']) < 8)
		{
			$_account->MailboxLimit = GetGoodBigInt(round($_POST['MaxMailboxSize']) * 1024);
		}

		$_account->FriendlyName = isset($_POST['txtFriendlyName']) ? $_POST['txtFriendlyName'] : $_account->FriendlyName;

		if ($_account->DomainId === 0)
		{
			$_account->MailIncHost = isset($_POST['txtIncomingMail']) ? $_POST['txtIncomingMail'] : $_account->MailIncHost;
			$_account->MailIncPort = isset($_POST['intIncomingPort']) ? (int) $_POST['intIncomingPort'] : $_account->MailIncPort;
			$_account->MailOutHost = isset($_POST['txtSmtpServer']) ? $_POST['txtSmtpServer'] : $_account->MailOutHost;
			$_account->MailOutPort = isset($_POST['intSmtpPort']) ? (int) $_POST['intSmtpPort'] : $_account->MailOutPort;
		}
		$_account->MailIncLogin = isset($_POST['txtIncomingLogin']) ? $_POST['txtIncomingLogin'] : $_account->MailIncLogin;

		$_password = isset($_POST['txtIncomingPassword']) ? $_POST['txtIncomingPassword'] : AP_DUMMYPASSWORD;
		if ($_password != AP_DUMMYPASSWORD)
		{
			$_account->MailIncPassword = $_password;
		}

		$_password = isset($_POST['UserPassword_PassMode']) ? $_POST['UserPassword_PassMode'] : AP_DUMMYPASSWORD;
		if ($_password != AP_DUMMYPASSWORD)
		{
			$_account->MailIncPassword = $_password;
		}

		$_account->MailOutLogin = isset($_POST['txtSmtpLogin']) ? $_POST['txtSmtpLogin'] : $_account->MailOutLogin;

		$_password = isset($_POST['txtSmtpPassword']) ? $_POST['txtSmtpPassword'] : AP_DUMMYPASSWORD;
		if ($_password != AP_DUMMYPASSWORD)
		{
			$_account->MailOutPassword = $_password;
		}

		$_protocol = isset($_POST['intMailProtocol']) ? $_POST['intMailProtocol'] : WM_MAILPROTOCOL_POP3;
		switch ($_protocol)
		{
			default:
				$_account->MailProtocol = WM_MAILPROTOCOL_POP3;
				break;
			case WM_MAILPROTOCOL_POP3:
			case WM_MAILPROTOCOL_IMAP4:
			case WM_MAILPROTOCOL_WMSERVER:
				$_account->MailProtocol = (int) $_protocol;
				break;
		}

		if ($_account->MailProtocol != WM_MAILPROTOCOL_WMSERVER)
		{
			$_account->Email = isset($_POST['txtEmail']) ? strtolower($_POST['txtEmail']) : $_account->Email;
		}

		if ($_account->DomainId === 0)
		{
			$_account->MailOutAuthentication = (isset($_POST['chkUseSmtpAuth']));
		}

		if ($this->_settings->TakeImapQuota && $_account->MailProtocol == WM_MAILPROTOCOL_IMAP4)
		{
			$_account->ImapQuota = (int) isset($_POST['intTakeImapQuota']);
		}

		$_account->UseFriendlyName = (isset($_POST['chkUseFriendlyName']));
		$_account->GetMailAtLogin = (isset($_POST['chkGetMailAtLogin']));
		if ($_account->MailProtocol !== WM_MAILPROTOCOL_WMSERVER)
		{
			$_account->AllowDirectMode = (isset($_POST['chkAllowDM']));
		}
		$_account->AllowChangeSettings = (isset($_POST['chkAllowChangeEmail']));
		$_account->Deleted = (isset($_POST['chkUserEnabled']) ? false : true);		

		$_synchronize = (isset($_POST['synchronizeSelect']) && $_account->MailProtocol == WM_MAILPROTOCOL_POP3)
			? (int) $_POST['synchronizeSelect'] : $_account->GetDefaultFolderSync();

		if ($_account->MailProtocol === WM_MAILPROTOCOL_POP3)
		{
			$_account->MailsOnServerDays = isset($_POST['txtKeepMsgsDays']) ? (int) $_POST['txtKeepMsgsDays'] : $_account->MailsOnServerDays;

			if (isset($_POST['chkDelMsgsDB']))
			{
				if ($_synchronize == WM_FOLDERSYNC_NewHeadersOnly || $_synchronize == WM_FOLDERSYNC_NewEntireMessages)
				{
					$_synchronize++;
				}
			}

			$_mailmode = WM_MAILMODE_LeaveMessagesOnServer;

			if (isset($_POST['mailMode']))
			{
				if ((int) $_POST['mailMode'] == 1)
				{
					$_mailmode = WM_MAILMODE_DeleteMessagesFromServer;
				}
				else
				{
					$_p = 0;
					if (isset($_POST['chkKeepMsgs']))
					{
						$_mailmode = WM_MAILMODE_KeepMessagesOnServer;
						$_p++;
					}
					if (isset($_POST['chkDelMsgsSrv']))
					{
						$_mailmode = WM_MAILMODE_DeleteMessageWhenItsRemovedFromTrash;
						$_p++;
					}
					if ($_p == 2)
					{
						$_mailmode = WM_MAILMODE_KeepMessagesOnServerAndDeleteMessageWhenItsRemovedFromTrash;
					}
				}
			}

			$_account->MailMode = $_mailmode;
		}

		// Advanced
		$_account->MailsPerPage = isset($_POST['txtMessagesPerPage']) ? (int) $_POST['txtMessagesPerPage'] : $_account->MailsPerPage;
		$_account->ContactsPerPage = isset($_POST['txtContactsPerPage']) ? (int) $_POST['txtContactsPerPage'] : $_account->ContactsPerPage;
		$_account->AllowDhtmlEditor = !(isset($_POST['intDisableRichEditor']) && $_POST['intDisableRichEditor'] == 1);

		$isPreviewPane = (isset($_POST['intMessageListWithPreviewPane']) && $_POST['intMessageListWithPreviewPane'] == 1);
		$isAlwaysShowPictures = (isset($_POST['intAlwaysShowPictures']) && $_POST['intAlwaysShowPictures'] == 1);

		if ($isPreviewPane)
		{
			$_account->ViewMode = ($isAlwaysShowPictures)
				? WM_VIEW_MODE_PREVIEW_PANE : WM_VIEW_MODE_PREVIEW_PANE_NO_IMG;
		}
		else
		{
			$_account->ViewMode = ($isAlwaysShowPictures)
				? WM_VIEW_MODE_WITHOUT_PREVIEW_PANE : WM_VIEW_MODE_WITHOUT_PREVIEW_PANE_NO_IMG;
		}

		$_account->DefaultSkin = isset($_POST['txtDefaultSkin']) ? $_POST['txtDefaultSkin'] : $_account->DefaultSkin;
		$_account->DefaultIncCharset = isset($_POST['txtDefaultUserCharset']) ? $_POST['txtDefaultUserCharset'] : $_account->DefaultIncCharset;
		$_account->DefaultOutCharset = isset($_POST['txtDefaultUserCharset']) ? $_POST['txtDefaultUserCharset'] : $_account->DefaultOutCharset;
		$_account->DefaultTimeZone = isset($_POST['txtDefaultTimeZone']) ? $_POST['txtDefaultTimeZone'] : $_account->DefaultTimeZone;
		$_account->DefaultLanguage = isset($_POST['txtDefaultLanguage']) ? $_POST['txtDefaultLanguage'] : $_account->DefaultLanguage;

		if (isset($_POST['AliasesListDDL']) && is_array($_POST['AliasesListDDL']))
		{
			$_account->Aliases = $_POST['AliasesListDDL'];
		}
		else
		{
			$_account->Aliases = array();
		}

		if (isset($_POST['ForwardsListDDL']) && is_array($_POST['ForwardsListDDL']))
		{
			$_account->Forwards = $_POST['ForwardsListDDL'];
		}
		else
		{
			$_account->Forwards = array();
		}
		
		if (isset($_POST['ListMembersDDL']) && is_array($_POST['ListMembersDDL']))
		{
			$_account->MailingList = $_POST['ListMembersDDL'];
		}
		else
		{
			$_account->MailingList = array();
		}
	}

	/**
	 * @param ap_Screen_Data $data
	 * @param Account $account
	 */
	function ScreenDataFromDomain(&$_data, &$_domain, $bPro = true)
	{
		$_data->SetValue('filterHref', $_domain->_name);
		$_data->SetValue('DomainName', $_domain->_name);
		$_data->SetValue('txtIncomingMail_domain', $_domain->_mailIncomingHost);
		$_data->SetValue('intIncomingMailPort_domain', $_domain->_mailIncomingPort);
		$_data->SetValue('txtOutgoingMail_domain', $_domain->_mailSmtpHost);
		$_data->SetValue('intOutgoingMailPort_domain', $_domain->_mailSmtpPort);
		$_data->SetValue('intReqSmtpAuthentication_domain', $_domain->_mailSmtpAuth);
		$_data->SetValue('intIsInternal_domain',  (int) $_domain->_isInternal);
		$_data->SetValue('intDomainGlobalAddrBook',  (int) $_domain->_globalAddrBook);
		$_data->SetValue('intLDAPAuth',  (int) $_domain->_ldapAuth);
		$_data->SetValue('hideGlobaAddressBookClass', $bPro ? '' : 'wm_hide');
		$_data->SetValue('hideLDAPAuthClass', (bool) $_domain->_isInternal ? '' : 'wm_hide');
		$_data->SetValue('hideLDAPAuthClass', 'wm_hide');

		$_data->SetValue('DomainTopTitle',
				$_domain->_isInternal
					? 'Contains users hosted by this server.'
					: 'Contains users hosted by other mail services (e.g. gmx.com).'
		);

		$_data->SetValue('classNewDomainEditZone',	$_domain->_isInternal ? 'wm_hide' : '');

		if ($_domain->_mailProtocol === WM_MAILPROTOCOL_IMAP4)
		{
			$_data->SetValue('intIncomingMailProtocolIMAP4_domain', true);
		}
		else
		{
			$_data->SetValue('intIncomingMailProtocolPOP3_domain', true);
		}
	}

	/**
	 * @param ap_Screen_Data $data
	 * @param Account $account
	 */
	function ScreenDataFromAccount(&$_data, &$_account, $_isNewAccount = false)
	{
		$_data->SetValue('txtIncomingLogin', $_account->MailIncLogin);
		if (!$_isNewAccount)
		{
			if (strlen($_account->MailIncPassword) > 0)
			{
				$_data->SetValue('txtIncomingPassword', AP_DUMMYPASSWORD);
			}

			if (strlen($_account->MailOutPassword) > 0)
			{
				$_data->SetValue('txtSmtpPassword', AP_DUMMYPASSWORD);
			}
		}

		$_data->SetValue('selectMailProtocolPop3', ($_account->MailProtocol == WM_MAILPROTOCOL_POP3));
		$_data->SetValue('selectMailProtocolImap4', ($_account->MailProtocol == WM_MAILPROTOCOL_IMAP4));

		$_data->SetValue('txtIncomingMail', $_account->MailIncHost);
		$_data->SetValue('txtSmtpServer', $_account->MailOutHost);

		$_data->SetValue('intIncomingPort', (int) $_account->MailIncPort);
		$_data->SetValue('intSmtpPort', (int) $_account->MailOutPort);

		$_data->SetValue('intLimitMailbox', (int) ceil($_account->MailboxLimit / 1024));
		$_data->SetValue('txtFriendlyName', $_account->FriendlyName);
		$_data->SetValue('txtEmail', $_account->Email);

		$_quotaJs = '';
		if ($this->_settings->TakeImapQuota)
		{
			$_quotaJs .= ' var TakeImapQuota = true; ';
			$_data->SetValue('intTakeImapQuota', (int) ($_account->ImapQuota === 1));
		}
		else
		{
			$_quotaJs .= ' var TakeImapQuota = false; ';
			$_data->SetValue('classTakeImapQuota', 'wm_hide');
		}

		if ($_account->ImapQuota === -1)
		{
			$_data->SetValue('infoTakeImapQuotaText', '<br />(IMAP quota is not supported by the server.)');
			$_quotaJs .= ' SetDisabled(document.getElementById("intTakeImapQuota"), true, true); ';
		}

		if (strlen($_quotaJs) > 0)
		{
			$_data->SetValue('infoTakeImapQuotaJs', '<script> '.$_quotaJs.' </script>');
		}

		$_data->SetValue('txtSmtpLogin', $_account->MailOutLogin);
		$_data->SetValue('txtKeepMsgsDays', $_account->MailsOnServerDays);

		$_data->SetValue('chkUseSmtpAuth', $_account->MailOutAuthentication);
		$_data->SetValue('chkUseFriendlyName', $_account->UseFriendlyName);

		$_data->SetValue('chkGetMailAtLogin', $_account->GetMailAtLogin);
		$_data->SetValue('chkAllowDM', $_account->AllowDirectMode);
		$_data->SetValue('chkAllowChangeEmail', $_account->AllowChangeSettings);
		$_data->SetValue('chkUserEnabled', !$_account->Deleted);
		

		switch ($_account->MailMode)
		{
			case WM_MAILMODE_DeleteMessagesFromServer:
				$_data->SetValue('radioDelRecvMsgs', true);
				break;
			case WM_MAILMODE_LeaveMessagesOnServer:
				$_data->SetValue('radioLeaveMsgs', true);
				break;
			case WM_MAILMODE_KeepMessagesOnServer:
				$_data->SetValue('radioLeaveMsgs', true);
				$_data->SetValue('chkKeepMsgs', true);
				break;
			case WM_MAILMODE_DeleteMessageWhenItsRemovedFromTrash:
				$_data->SetValue('radioLeaveMsgs', true);
				$_data->SetValue('chkDelMsgsSrv', true);
				break;
			case WM_MAILMODE_KeepMessagesOnServerAndDeleteMessageWhenItsRemovedFromTrash:
				$_data->SetValue('radioLeaveMsgs', true);
				$_data->SetValue('chkKeepMsgs', true);
				$_data->SetValue('chkDelMsgsSrv', true);
				break;
		}

		$_folderSyncType = $_data->GetValueAsInt('folderSyncType');
		if ($_folderSyncType == WM_FOLDERSYNC_NewHeadersOnly || $_folderSyncType == WM_FOLDERSYNC_AllHeadersOnly)
		{
			$_data->SetValue('synchronizeSelect1', true);
		}
		else if ($_folderSyncType == WM_FOLDERSYNC_DirectMode)
		{
			$_data->SetValue('synchronizeSelect5', true);
		}
		else
		{
			$_data->SetValue('synchronizeSelect3', true);
		}

		if ($_folderSyncType == WM_FOLDERSYNC_AllHeadersOnly || $_folderSyncType == WM_FOLDERSYNC_AllEntireMessages)
		{
			$_data->SetValue('chkDelMsgsDB', true);
		}

		// Advanced
		$_data->SetValue('txtMessagesPerPage', $_account->MailsPerPage);
		$_data->SetValue('txtContactsPerPage', $_account->ContactsPerPage);
		$_data->SetValue('intDisableRichEditor', !$_account->AllowDhtmlEditor);
		$_data->SetValue('intMessageListWithPreviewPane', $_account->ViewMode == WM_VIEW_MODE_PREVIEW_PANE || $_account->ViewMode == WM_VIEW_MODE_PREVIEW_PANE_NO_IMG);
		$_data->SetValue('intAlwaysShowPictures', $_account->ViewMode == WM_VIEW_MODE_PREVIEW_PANE || $_account->ViewMode == WM_VIEW_MODE_WITHOUT_PREVIEW_PANE);

		$_skins =& $this->GetSkinsList();
		$_skinsString = '';
		foreach ($_skins as $_skin)
		{
			$_selected = ($_skin == $_account->DefaultSkin) ? ' selected="selected"' : '';
			$_skinsString .= '<option value="'.ap_Utils::AttributeQuote($_skin).'"'.$_selected.'>'.ap_Utils::EncodeSpecialXmlChars($_skin).'</option>'.AP_CRLF;
		}
		$_data->SetValue('txtDefaultSkin', $_skinsString);

		$_charsets =& $this->GetCharsetsList();
		$_charsetsString = '';
		foreach ($_charsets as $_charset)
		{
			$_selected = ($_charset[0] == $_account->DefaultIncCharset) ? ' selected="selected"' : '';
			$_charsetsString .= '<option value="'.ap_Utils::AttributeQuote($_charset[0]).'"'.$_selected.'>'.ap_Utils::EncodeSpecialXmlChars($_charset[1]).'</option>'.AP_CRLF;
		}
		$_data->SetValue('txtDefaultUserCharset', $_charsetsString);

		$_timezones =& $this->GetTimeZoneList();
		$_timezonesString = '';
		foreach ($_timezones as $_timezoneKey => $_timezoneValue)
		{
			$_selected = ($_timezoneKey == $_account->DefaultTimeZone) ? ' selected="selected"' : '';
			$_timezonesString .= '<option value="'.ap_Utils::AttributeQuote($_timezoneKey).'"'.$_selected.'>'.ap_Utils::EncodeSpecialXmlChars($_timezoneValue).'</option>'.AP_CRLF;
		}
		$_data->SetValue('txtDefaultTimeZone', $_timezonesString);

		$_langs =& $this->GetLangsList();
		$_langsString = '';
		foreach ($_langs as $_lang)
		{
			$_selected = ($_lang == $_account->DefaultLanguage) ? ' selected="selected"' : '';
			$_langsString .= '<option value="'.ap_Utils::AttributeQuote($_lang).'"'.$_selected.'>'.ap_Utils::EncodeSpecialXmlChars($_lang).'</option>'.AP_CRLF;
		}
		$_data->SetValue('txtDefaultLanguage', $_langsString);

		if ($_account->Aliases && count($_account->Aliases) > 0)
		{
			$text = '';
			foreach ($_account->Aliases as $value)
			{
				$text .= '<option value="'.ap_Utils::AttributeQuote($value).'">'.ap_Utils::EncodeSpecialXmlChars($value).'</option>';
			}
			if (strlen($text) > 0)
			{
				$_data->SetValue('AliasesListDDL', $text);
				$text = '';
			}
		}

		if ($_account->Forwards && count($_account->Forwards) > 0)
		{
			$text = '';
			foreach ($_account->Forwards as $value)
			{
				$text .= '<option value="'.ap_Utils::AttributeQuote($value).'">'.ap_Utils::EncodeSpecialXmlChars($value).'</option>';
			}
			if (strlen($text) > 0)
			{
				$_data->SetValue('ForwardsListDDL', $text);
				$text = '';
			}
		}
	}
}
