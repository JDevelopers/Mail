<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));
	
	$dataPath = $dPath = null;
	if (!defined('IS_SETTINGS_REQUIRE'))
	{
		if (@file_exists(WM_ROOTPATH.'inc_settings_path.php'))
		{
			require_once(WM_ROOTPATH.'inc_settings_path.php');
			if (null === $dataPath)
			{
				exit('<font color="red" face="tahoma">Can\'t find $dataPath in <b>inc_settings_path.php</b> file</font>');
			}
			define('IS_SETTINGS_REQUIRE', 1);
		}
		else 
		{
			exit('<font color="red" face="tahoma">Can\'t find <b>inc_settings_path.php</b> file</font>');
		}
	}

	$dataPath = (null !== $dataPath) ? str_replace('\\', '/', rtrim(trim($dataPath), '/\\')) : null;
	if ($dataPath !== null)
	{
		$dPath = str_replace('\\', '/', rtrim(realpath($dataPath), '/\\'));
	}
	
	define('INI_DIR', @is_dir($dPath) ? $dPath : WM_ROOTPATH.$dataPath);
	
	require_once(WM_ROOTPATH.'common/inc_constants.php');
	require_once(WM_ROOTPATH.'common/class_xmldocument.php');
	require_once(WM_ROOTPATH.'common/class_convertutils.php');
	require_once(WM_ROOTPATH.'common/class_log.php');
	require_once(WM_ROOTPATH.'common/class_objectcache.php');
	require_once(WM_ROOTPATH.'common/class_dbstorage.php');
	require_once(WM_ROOTPATH.'common/class_domainsettings.php');
	
	/* custom class file include */
	if (@file_exists(INI_DIR.'/custom/custom_data_class.php'))
	{
		require_once(INI_DIR.'/custom/custom_data_class.php');
	}
	require_once(WM_ROOTPATH.'common/class_custom.php');

	define('LOG_PATH', 'logs');
		
	define('DB_MSSQLSERVER', 1);
	define('DB_MYSQL', 3);

	class Settings
	{
		/**
		 * @var string
		 */
		var $WindowTitle;

		/**
		 * @var string
		 */
		var $LicenseKey;
	
		/**
		 * @var string
		 */
		var $AdminPassword;
	
		/**
		 * @var int
		 */
		var $DbType;
	
		/**
		 * @var string
		 */
		var $DbLogin;
	
		/**
		 * @var string
		 */
		var $DbPassword;
	
		/**
		 * @var string
		 */
		var $DbName;
		
		/**
		 * @var bool
		 */
		var $UseDsn;
	
		/**
		 * @var string
		 */
		var $DbDsn;
	
		/**
		 * @var string
		 */
		var $DbHost;

		/**
		 * @var bool
		 */
		var $UseCustomConnectionString;
	
		/**
		 * @var string
		 */
		var $DbCustomConnectionString;
	
		/**
		 * @var string
		 */
		var $DbPrefix;
	
		/**
		 * @var int
		 */
		var $IncomingMailProtocol;
		
		/**
		 * @var string
		 */
		var $IncomingMailServer;
	
		/**
		 * @var int
		 */
		var $IncomingMailPort;
	
		/**
		 * @var string
		 */
		var $OutgoingMailServer;
	
		/**
		 * @var int
		 */
		var $OutgoingMailPort;
	
		/**
		 * @var bool
		 */
		var $ReqSmtpAuth;
	
		/**
		 * @var bool
		 */
		var $AllowAdvancedLogin;
	
		/**
		 * @var int
		 */
		var $HideLoginMode;
	
		/**
		 * @var string
		 */
		var $DefaultDomainOptional;

		/**
		 * @var bool
		 */
		var $UseMultipleDomainsSelection;

		/**
		 * @var bool
		 */
		var $UseCaptcha;
	
		/**
		 * @var bool
		 */
		var $ShowTextLabels;
	
		/**
		 * @var bool
		 */
		var $AutomaticCorrectLoginSettings;
	
		/**
		 * @var bool
		 */
		var $EnableLogging;

		/**
		 * @var bool
		 */
		var $LoggingSpecialUsers;

		/**
		 * @var int
		 */
		var $LogLevel;

		/**
		 * @var bool
		 */
		var $EnableEventsLogging;
	
		/**
		 * @var bool
		 */
		var $DisableErrorHandling;
	
		/**
		 * @var bool
		 */
		var $AllowAjax;
	
		/**
		 * @var int
		 */
		var $MailsPerPage;
		
		/**
		 * @var bool
		 */
		var $EnableAttachmentSizeLimit;
	
		/**
		 * @var long
		 */
		var $AttachmentSizeLimit;
	
		/**
		 * @var bool
		 */
		var $EnableMailboxSizeLimit;
		
		/**
		 * @var long
		 */
		var $MailboxSizeLimit;

		/**
		 * @var bool
		 */
		var $TakeImapQuota;
	
		/**
		 * @var bool
		 */
		var $AllowUsersChangeTimeZone;
	
		/**
		 * @var string
		 */
		var $DefaultUserCharset;
	
		/**
		 * @var bool
		 */
		var $AllowUsersChangeCharset;
	
		/**
		 * @var string
		 */
		var $DefaultSkin;
	
		/**
		 * @var bool
		 */
		var $AllowUsersChangeSkin;
	
		/**
		 * @var string
		 */
		var $DefaultLanguage;
	
		/**
		 * @var bool
		 */
		var $AllowUsersChangeLanguage;
	
		/**
		 * @var bool
		 */
		var $AllowDhtmlEditor;
	
		/**
		 * @var bool
		 */
		var $AllowUsersChangeEmailSettings;
	
		/**
		 * @var bool
		 */
		var $AllowDirectMode;
	
		/**
		 * @var bool
		 */
		var $DirectModeIsDefault;
	
		/**
		 * @var bool
		 */
		var $AllowNewUsersRegister;
	
		/**
		 * @var bool
		 */
		var $AllowUsersAddNewAccounts;

		/**
		 * @var	bool
		 */
		var $AllowUsersChangeAccountsDef;
	
		/**
		 * @var bool
		 */
		var $StoreMailsInDb;
		
		/**
		 * @var bool
		 */
		var $EnableWmServer;
		
		/**
		 * @var string
		 */
		var $WmServerRootPath;
		
		/**
		 * @var string
		 */
		var $WmServerHost;
		
		/**
		 * @var bool
		 */
		var $WmAllowManageXMailAccounts;
		
		/**
		 * @var bool
		 */
		var $AllowContacts;
		
		/**
		 * @var bool
		 */
		var $AllowCalendar;
		
		/**
		 * @var int
		 */
		var $DefaultTimeZone;
		
		/**
		 * @var int
		 */
		var $Cal_DefaultTimeFormat;
		
		/**
		 * @var int
		 */
		var $Cal_DefaultTimeZone;
		
		/**
		 * @var int
		 */
		var $Cal_DefaultDateFormat;
		
		/**
		 * @var bool
		 */
		var $Cal_ShowWeekends;
		
		/**
		 * @var int
		 */
		var $Cal_WorkdayStarts;
		
		/**
		 * @var int
		 */
		var $Cal_WorkdayEnds;
		
		/**
		 * @var int
		 */
		var $Cal_ShowWorkDay;
		
		/**
		 * @var int
		 */
		var $Cal_WeekStartsOn;

		/**
		 * @var int
		 */
		var $Cal_DefaultTab;
		
		/**
		 * @var string
		 */
		var $Cal_DefaultCountry;
		
		/**
		 * @var bool
		 */
		var $Cal_AllTimeZones;
		
		/**
		 * @var bool
		 */		
		var $Cal_AllowReminders;

		/**
		 * @var bool
		 */
		var $Cal_AutoAddInvitation;
		
		/**
		 * @var bool
		 */
		var $Imap4DeleteLikePop3;

		/**
		 * @var bool
		 */
		var $AllowLanguageOnLogin;

		/**
		 * @var bool
		 */
		var $EnableMobileSync;

		/**
		 * @var string
		 */
		var $MobileSyncUrl;

		/**
		 * @var	string
		 */
		var $MobileSyncContactDataBase;

		/**
		 * @var	string
		 */
		var $MobileSyncCalendarDataBase;

		/**
		 * @var	int
		 */
		var $ViewMode;

		/**
		 * @var	int
		 */
		var $SaveInSent;

		/**
		 * @var string
		 */
		var $Dev;
		
		/**
		 * @var bool
		 */
		var $isLoad;
		
		/**
		 * @var bool
		 */
		var $_langIsInclude;

		/**
		 * @var int
		 */
		var $IdleSessionTimeout;

		/**
		 * @var bool
		 */
		var $AllowInsertImage;

		/**
		 * @var bool
		 */
		var $AllowBodySize;

		/**
		 * @var int
		 */
		var $MaxBodySize;

		/**
		 * @var int
		 */
		var $MaxSubjectSize;

		/**
		 * @var bool
		 */
		var $AllowRegistration;

		/**
		 * @var bool
		 */
		var $AllowPasswordReset;

		/**
		 * @var bool
		 */
		var $FlagsLangSelect;
		
		/**
		 * @var string
		 */
		var $GlobalAddressBook;

		/**
		 * @static
		 * @return Settings
		 */
		function &CreateInstance()
		{
			static $instance = null;
    		if (null === $instance)
    		{
				$instance = new Settings(null);
    		}

    		return $instance;
		}
		
		/**
		* @access private
		*/
		function Settings($param = true)
		{
		    if (!is_null($param))
		    {
		    	die('can\'t call Settings class.');
		    }

			$this->UseDsn = false;
			$this->WmServerHost = '127.0.0.1';
			$this->AllowUsersChangeAccountsDef = false;
			$this->WmAllowManageXMailAccounts = false;
			$this->AllowContacts = true;
			$this->AllowCalendar = true;
			$this->UseCaptcha = true;

			$this->Cal_DefaultTimeFormat = 1;
			$this->Cal_DefaultTimeZone = 38;
			$this->Cal_DefaultDateFormat = 1;
			$this->Cal_ShowWeekends = true;
			$this->Cal_WorkdayStarts = 9;
			$this->Cal_WorkdayEnds = 18;
			$this->Cal_ShowWorkDay = 1;
			$this->Cal_WeekStartsOn = 0;
			$this->Cal_DefaultTab = 2;
			$this->Cal_DefaultCountry = 'US';
			$this->Cal_AllTimeZones = false;
			$this->Cal_AllowReminders = false;
			$this->Cal_AutoAddInvitation = 0;

			$this->Imap4DeleteLikePop3 = true;
			$this->AllowLanguageOnLogin = true;

			$this->AllowInsertImage = true;
			$this->AllowBodySize = false;
			$this->MaxBodySize = 600;
			$this->MaxSubjectSize = 255;

			$this->AllowRegistration = false;
			$this->AllowPasswordReset = false;
			$this->GlobalAddressBook = GLOBAL_ADDRESS_BOOK_OFF;
			$this->EnableMobileSync = false;
			$this->MobileSyncUrl = '';
			$this->MobileSyncContactDataBase =  'card';
			$this->MobileSyncCalendarDataBase =  'cal';
			$this->FlagsLangSelect = false;
			$this->ViewMode = 1;
			$this->SaveInSent = 0;

			$this->isLoad = false;
			$this->_langIsInclude = false;
			
			$this->Dev = null;
			
			$settingsCacher = new SettingsCacher();
			$settingsRaw = $settingsCacher->Load();
			if (false === $settingsRaw)
			{
				$settingsRaw = $settingsCacher->LoadRoot();
				if ($settingsRaw == false)
				{
					setGlobalError($settingsCacher->GetError());
				}
				else
				{
					$settingsCacher->Save($settingsRaw);	
				}
			}
			else if (is_array($settingsRaw))
			{
				$this->_loadFromArray($settingsRaw);
				$this->isLoad = true;
			}

			if (is_string($settingsRaw))
			{
				$xmlDocument = new XmlDocument();
				if ($xmlDocument->LoadFromString($settingsRaw))
				{
					$this->_loadFromXML($xmlDocument->XmlRoot);
					$this->isLoad = true;
				}
			}

			if ($this->isLoad)
			{
				

				if (!@function_exists('imagecreatefrompng'))
				{
					$this->UseCaptcha = false;
				}

				$xmlDomainSettings =& DomainSettings::CreateInstance();
				$xmlDomainSettings->UpdateSettingsByDomain($this);

				/* custom class */
				wm_Custom::StaticUseMethod('ChangeSettingsAfterLoad', array(&$this));
			}
		}
		
		/**
		 * @return bool
		 */
		function IncludeLang($langName = null)
		{
			if (!$this->isLoad)
			{
				return false;
			}
			
			if ($this->_langIsInclude)
			{
				return true;
			}

			$lang = $this->DefaultLanguage;

			if ($langName)
			{
				$lang = $langName;
			}
			else 
			{
				$lang = isset($_SESSION[SESSION_LANG]) ? $_SESSION[SESSION_LANG] : $this->DefaultLanguage;
			}
			
			$lang = ConvertUtils::ClearFileName($lang);
			
			if (@file_exists(WM_ROOTPATH.'lang/'.$lang.'.php'))
			{
				include_once(WM_ROOTPATH.'lang/'.$lang.'.php');
				$_SESSION[SESSION_LANG] = $lang;
				$this->_langIsInclude = true;
			}
			elseif (@file_exists(WM_ROOTPATH.'lang/English.php'))
			{
				include_once(WM_ROOTPATH.'lang/English.php');
				$_SESSION[SESSION_LANG] = 'English';
				$this->_langIsInclude = true;
			}
			
			return $this->_langIsInclude;
		}

		/**
		 * @access private
		 * @param array $array
		 */
		function _loadFromArray(&$array)
		{
			foreach ($array as $key => $value)
			{
				$this->$key = $value;
			}
		}
		
		/**
		 * @access private
		 * @param XmlDomNode $xmlTree
		 */
		function _loadFromXML(&$xmlTree)
		{
			foreach ($xmlTree->Children as $node)
			{
				switch ($node->TagName)
				{
					case 'Common':
					case 'WebMail':
					case 'Calendar':
						if (count($node->Children) > 0)
						{						
							$this->_loadFromXML($node);
						}
						break;
					case 'SiteName':
						$this->WindowTitle = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;					
					case 'WindowTitle':
						$this->WindowTitle = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'LicenseKey':
						$this->LicenseKey = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'AdminPassword':
						$this->AdminPassword = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'DBType':
						$this->DbType = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'DBLogin':
						$this->DbLogin = trim(ConvertUtils::WMBackHtmlSpecialChars($node->Value));
						break;
					case 'DBPassword':
						$this->DbPassword = trim(ConvertUtils::WMBackHtmlSpecialChars($node->Value));
						break;
					case 'DBName':
						$this->DbName = trim(ConvertUtils::WMBackHtmlSpecialChars($node->Value));
						break;
					case 'UseDsn':
						$this->UseDsn = (bool) $node->Value;
					case 'DBDSN':
						$this->DbDsn = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'DBHost':
						$this->DbHost = trim(ConvertUtils::WMBackHtmlSpecialChars($node->Value));
						break;
					case 'UseCustomConnectionString':
						$this->UseCustomConnectionString = (bool) $node->Value;
						break;
					case 'DBCustomConnectionString':
						$this->DbCustomConnectionString = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'DBPrefix':
						$this->DbPrefix = ConvertUtils::ClearPrefix(ConvertUtils::WMBackHtmlSpecialChars($node->Value));
						break;
					case 'IncomingMailProtocol':
						$this->IncomingMailProtocol = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'IncomingMailServer':
						$this->IncomingMailServer = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'IncomingMailPort':
						$this->IncomingMailPort = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'OutgoingMailServer':
						$this->OutgoingMailServer = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'OutgoingMailPort':
						$this->OutgoingMailPort = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'ReqSmtpAuth':
						$this->ReqSmtpAuth = (bool) $node->Value;
						break;
					case 'AllowAdvancedLogin':
						$this->AllowAdvancedLogin = (bool) $node->Value;
						break;
					case 'HideLoginMode':
						$this->HideLoginMode = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'DefaultDomainOptional':
						$this->DefaultDomainOptional = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'UseMultipleDomainsSelection':
						$this->UseMultipleDomainsSelection = (bool) $node->Value;
						break;
					case 'UseCaptcha':
						$this->UseCaptcha = (bool) $node->Value;
						break;
					case 'ShowTextLabels':
						$this->ShowTextLabels = (bool) $node->Value;
						break;
					case 'AutomaticCorrectLoginSettings':
						$this->AutomaticCorrectLoginSettings = (bool) $node->Value;
						break;
					case 'EnableLogging':
						$this->EnableLogging = (bool) (0 < (int) $node->Value);
						$this->LoggingSpecialUsers = (bool) (2 === (int) $node->Value);
						break;
					case 'LogLevel':
						$this->LogLevel = (int) $node->Value;
						break;
					case 'EnableEventsLogging':
						$this->EnableEventsLogging = (bool) $node->Value;
						break;
					case 'DisableErrorHandling':
						$this->DisableErrorHandling = (bool) $node->Value;
						break;
					case 'AllowAjax':
						$this->AllowAjax = true;
						break;
					case 'MailsPerPage':
						$this->MailsPerPage = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'EnableAttachmentSizeLimit':
						$this->EnableAttachmentSizeLimit = (bool) $node->Value;
						break;						
					case 'AttachmentSizeLimit':
						$this->AttachmentSizeLimit = GetGoodBigInt(ConvertUtils::WMBackHtmlSpecialChars($node->Value));
						break;
					case 'EnableMailboxSizeLimit':
						$this->EnableMailboxSizeLimit = (bool) $node->Value;
						break;
					case 'MailboxSizeLimit':
						$this->MailboxSizeLimit = GetGoodBigInt(ConvertUtils::WMBackHtmlSpecialChars($node->Value));
						break;
					case 'TakeImapQuota':
						$this->TakeImapQuota = (bool) $node->Value;
						break;
					case 'AllowUsersChangeTimeZone':
						$this->AllowUsersChangeTimeZone = (bool) $node->Value;
						break;
					case 'DefaultUserCharset':
						$this->DefaultUserCharset = ConvertUtils::GetCodePageName($node->Value);
						break;
					case 'AllowUsersChangeCharset':
						$this->AllowUsersChangeCharset = (bool) ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'DefaultSkin':
						$this->DefaultSkin = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'AllowUsersChangeSkin':
						$this->AllowUsersChangeSkin = (bool) $node->Value;
						break;
					case 'DefaultLanguage':
						$this->DefaultLanguage =ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'AllowUsersChangeLanguage':
						$this->AllowUsersChangeLanguage = (bool) $node->Value;
						break;
					case 'AllowDHTMLEditor':
						$this->AllowDhtmlEditor = (bool) $node->Value;
						break;
					case 'AllowUsersChangeEmailSettings':
						$this->AllowUsersChangeEmailSettings = (bool) $node->Value;
						break;
					case 'AllowDirectMode':
						$this->AllowDirectMode = (bool) $node->Value;
						break;
					case 'DirectModeIsDefault':
						$this->DirectModeIsDefault = (bool) $node->Value;
						break;
					case 'AllowNewUsersRegister':
						$this->AllowNewUsersRegister = true;
						
						break;
					case 'AllowUsersAddNewAccounts':
						$this->AllowUsersAddNewAccounts = false;
						
						break;
					case 'AllowUsersChangeAccountsDef':
						$this->AllowUsersChangeAccountsDef = (bool) $node->Value;
						break;
					case 'StoreMailsInDb':
						$this->StoreMailsInDb = (bool) $node->Value;
						break;
					case 'Imap4DeleteLikePop3':
						$this->Imap4DeleteLikePop3 = (bool) $node->Value;
						break;
					case 'EnableWmServer':
						$this->EnableWmServer = (bool) $node->Value;
						break;				
					case 'WmServerRootPath':
						$this->WmServerRootPath = rtrim(ConvertUtils::WMBackHtmlSpecialChars($node->Value), '\\/');
						break;
					case 'WmServerHost':
						$this->WmServerHost = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;	
					case 'WmAllowManageXMailAccounts':
						$this->WmAllowManageXMailAccounts = (bool) $node->Value;
						break;						
					case 'AllowContacts':
						$this->AllowContacts = (bool) $node->Value;
						break;	
					case 'AllowCalendar':
						$this->AllowCalendar = false;
						
						break;	
					case 'DefaultTimeZone':
						if ($xmlTree->TagName == 'Calendar')
						{
							$this->Cal_DefaultTimeZone = (int) $node->Value;
						}
						else 
						{
							$this->DefaultTimeZone = (int) $node->Value;
						}
						break;	
					case 'DefaultTimeFormat':
						$this->Cal_DefaultTimeFormat = (int) $node->Value;
						break;
					case 'DefaultDateFormat':
						$this->Cal_DefaultDateFormat = (int) $node->Value;
						break;
					case 'ShowWeekends':
						$this->Cal_ShowWeekends = (int) $node->Value;
						break;
					case 'WorkdayStarts':
						$this->Cal_WorkdayStarts = (int) $node->Value;
						break;
					case 'WorkdayEnds':
						$this->Cal_WorkdayEnds = (int) $node->Value;
						break;
					case 'ShowWorkDay':
						$this->Cal_ShowWorkDay = (int) $node->Value;
						break;
					case 'WeekStartsOn':
						$this->Cal_WeekStartsOn = (int) $node->Value;
						break;
					case 'DefaultTab':
						$this->Cal_DefaultTab = (int) $node->Value;
						break;
					case 'DefaultCountry':
						$this->Cal_DefaultCountry = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'AllTimeZones':
						$this->Cal_AllTimeZones = (int) $node->Value;
						break;
					case 'AllowReminders':
						$this->Cal_AllowReminders = (bool) $node->Value;
						break;
					case 'AutoAddInvitation':
						$this->Cal_AutoAddInvitation = (int) $node->Value;
						break;
					case 'AllowLanguageOnLogin':
						$this->AllowLanguageOnLogin = (bool) $node->Value;
						break;
					case 'IdleSessionTimeout':
						$this->IdleSessionTimeout =  (int) $node->Value;
						break;
					case 'AllowInsertImage':
						$this->AllowInsertImage =  (bool) $node->Value;
						break;
					case 'AllowBodySize':
						$this->AllowBodySize =  (bool) $node->Value;
						break;
					case 'MaxBodySize':
						$this->MaxBodySize =  (int) $node->Value;
						break;
					case 'MaxSubjectSize':
						$this->MaxSubjectSize =  (int) $node->Value;
						break;
					case 'AllowRegistration':
						$this->AllowRegistration =  (int) $node->Value;
						break;
					case 'AllowPasswordReset':
						$this->AllowPasswordReset =  (int) $node->Value;
						break;
					case 'GlobalAddressBook':
						$this->GlobalAddressBook = GLOBAL_ADDRESS_BOOK_OFF;
						
						break;
					case 'EnableMobileSync':
						$this->EnableMobileSync = (bool) $node->Value;
						break;
					case 'MobileSyncUrl':
						$this->MobileSyncUrl =  ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'MobileSyncContactDataBase':
						$this->MobileSyncContactDataBase =  ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'MobileSyncCalendarDataBase':
						$this->MobileSyncCalendarDataBase =  ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
					case 'FlagsLangSelect':
						$this->FlagsLangSelect = (bool) $node->Value;
						break;
					case 'ViewMode':
						$this->ViewMode = (int) $node->Value;
						break;
					case 'SaveInSent':
						$this->SaveInSent = (int) $node->Value;
						break;
					case 'Dev':
						$this->Dev = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						break;
				}
			}
		}
	
		function GetDev($key)
		{
			static $static;
			if (!$static)
			{
				if (strlen($this->Dev) > 0)
				{
					$static = array();
					$aTemp = explode('|', $this->Dev);
					foreach($aTemp as $line)
					{
						$aTemp2 = explode('=', $line, 2);
						if (count($aTemp2) == 2)
						{
							$static[$aTemp2[0]] = $aTemp2[1];
						}
					}
				}
			}
			
			return isset($static[$key]) ? $static[$key] : null;
		}
	}
		
	class SettingsCacher
	{
		private $_cacherDriver;
		private $_error;
		
		public function __construct()
		{
			$this->_cacherDriver = new SettingsDbCacher();
		}

		/**
		 * @return	string|false
		 */
		public function Load()
		{
			return $this->_cacherDriver->Load();
		}
		
		/**
		 * @param	string	$string
		 * @return	bool
		 */
		public function Save($string)
		{
			return $this->_cacherDriver->Save($string);
		}
		
		public function LoadRoot()
		{
			$return = @file_get_contents(INI_DIR.'/settings/settings.xml');
			if (false === $return)
			{
				$this->_error = 'Can\'t load "'.INI_DIR.'/settings/settings.xml" ('.__FILE__.' - '.__LINE__.')';
			}
			return $return;
		}
		
		public function GetError()
		{
			return $this->_error;
		}
	}
	
	class SettingsDbCacher
	{
		private $_error;
		
		public function __construct()
		{}
		
		/**
		 * @return	string|false
		 */
		public function Load()
		{
			return false;
		}
		
		/**
		 * @param	string	$string
		 * @return	bool
		 */
		public function Save($string)
		{
			return true;
		}
		
		public function GetError()
		{
			return $this->_error;
		}
	}

	class SettingsPhpCacher
	{
		const PhpCacheFileName = 'settings.xml.cache';
		
		private $_error;

		public function __construct()
		{}

		/**
		 * @return	string|false
		 */
		public function Load()
		{
			$path = INI_DIR.'/settings/'.self::PhpCacheFileName;
			if (@file_exists($path))
			{
				return include $path;
			}
			return false;
		}

		/**
		 * @param	string	$string
		 * @return	bool
		 */
		public function Save($string)
		{
			return true;
		}

		public function GetError()
		{
			return $this->_error;
		}
	}