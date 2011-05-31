<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	class WebMail_Settings
	{
		/**
		 * @var	string
		 */
		var $WindowTitle;

		/**
		 * @var	string
		 */
		var $LicenseKey;
	
		/**
		 * @var	string
		 */
		var $AdminPassword;
	
		/**
		 * @var	int
		 */
		var $DbType;
	
		/**
		 * @var	string
		 */
		var $DbLogin;
	
		/**
		 * @var	string
		 */
		var $DbPassword;
	
		/**
		 * @var	string
		 */
		var $DbName;
		
		/**
		 * @var bool
		 */
		var $UseDsn;
	
		/**
		 * @var	string
		 */
		var $DbDsn;
	
		/**
		 * @var	string
		 */
		var $DbHost;

		/**
		 * @var	bool
		 */
		var $UseCustomConnectionString;
	
		/**
		 * @var	string
		 */
		var $DbCustomConnectionString;
	
		/**
		 * @var	string
		 */
		var $DbPrefix;
	
		/**
		 * @var	int
		 */
		var $IncomingMailProtocol;
		
		/**
		 * @var	string
		 */
		var $IncomingMailServer;
	
		/**
		 * @var	int
		 */
		var $IncomingMailPort;
	
		/**
		 * @var	string
		 */
		var $OutgoingMailServer;
	
		/**
		 * @var	int
		 */
		var $OutgoingMailPort;
	
		/**
		 * @var	bool
		 */
		var $ReqSmtpAuth;
	
		/**
		 * @var	bool
		 */
		var $AllowAdvancedLogin;
	
		/**
		 * @var	int
		 */
		var $HideLoginMode;
	
		/**
		 * @var	string
		 */
		var $DefaultDomainOptional;

		/**
		 * @var bool
		 */
		var $UseMultipleDomainsSelection;

		/**
		 * @var	bool
		 */
		var $UseCaptcha;
	
		/**
		 * @var	bool
		 */
		var $ShowTextLabels;
	
		/**
		 * @var	bool
		 */
		var $AutomaticCorrectLoginSettings;
	
		/**
		 * @var	bool
		 */
		var $EnableLogging;

		/**
		 * @var	bool
		 */
		var $LoggingSpecialUsers;

		/**
		 * @var	int
		 */
		var $LogLevel;

		/**
		 * @var	bool
		 */
		var $EnableEventsLogging;
	
		/**
		 * @var	bool
		 */
		var $DisableErrorHandling;
	
		/**
		 * @var	bool
		 */
		var $AllowAjax;
	
		/**
		 * @var	int
		 */
		var $MailsPerPage;
		
		/**
		 * @var	bool
		 */
		var $EnableAttachmentSizeLimit;
	
		/**
		 * @var	long
		 */
		var $AttachmentSizeLimit;
	
		/**
		 * @var	bool
		 */
		var $EnableMailboxSizeLimit;
		
		/**
		 * @var	long
		 */
		var $MailboxSizeLimit;

		/**
		 * @var bool
		 */
		var $TakeImapQuota;
	
		/**
		 * @var	bool
		 */
		var $AllowUsersChangeTimeZone;
	
		/**
		 * @var	string
		 */
		var $DefaultUserCharset;
	
		/**
		 * @var	bool
		 */
		var $AllowUsersChangeCharset;
	
		/**
		 * @var	string
		 */
		var $DefaultSkin;
	
		/**
		 * @var	bool
		 */
		var $AllowUsersChangeSkin;
	
		/**
		 * @var	string
		 */
		var $DefaultLanguage;
	
		/**
		 * @var	bool
		 */
		var $AllowUsersChangeLanguage;
	
		/**
		 * @var	bool
		 */
		var $AllowDhtmlEditor;
	
		/**
		 * @var	bool
		 */
		var $AllowUsersChangeEmailSettings;
	
		/**
		 * @var	bool
		 */
		var $AllowDirectMode;
	
		/**
		 * @var	bool
		 */
		var $DirectModeIsDefault;
	
		/**
		 * @var	bool
		 */
		var $AllowNewUsersRegister;
	
		/**
		 * @var	bool
		 */
		var $AllowUsersAddNewAccounts;

		/**
		 * @var	bool
		 */
		var $AllowUsersChangeAccountsDef;
	
		/**
		 * @var	bool
		 */
		var $StoreMailsInDb;
		
		/**
		 * @var	bool
		 */
		var $EnableWmServer;
		
		/**
		 * @var	string
		 */
		var $WmServerRootPath;
		
		/**
		 * @var	string
		 */
		var $WmServerHost;
		
		/**
		 * @var	bool
		 */
		var $WmAllowManageXMailAccounts;
		
		/**
		 * @var	bool
		 */
		var $AllowContacts;
		
		/**
		 * @var	bool
		 */
		var $AllowCalendar;
		
		/**
		 * @var	int
		 */
		var $DefaultTimeZone;
		
		/**
		 * @var	int
		 */
		var $Cal_DefaultTimeFormat;
		
		/**
		 * @var	int
		 */
		var $Cal_DefaultTimeZone;
		
		/**
		 * @var	int
		 */
		var $Cal_DefaultDateFormat;
		
		/**
		 * @var	bool
		 */
		var $Cal_ShowWeekends;
		
		/**
		 * @var	int
		 */
		var $Cal_WorkdayStarts;
		
		/**
		 * @var	int
		 */
		var $Cal_WorkdayEnds;
		
		/**
		 * @var	int
		 */
		var $Cal_ShowWorkDay;
		
		/**
		 * @var	int
		 */
		var $Cal_WeekStartsOn;

		/**
		 * @var	int
		 */
		var $Cal_DefaultTab;
		
		/**
		 * @var	string
		 */
		var $Cal_DefaultCountry;
		
		/**
		 * @var	bool
		 */
		var $Cal_AllTimeZones;
		
		/**
		 * @var	bool
		 */
		var $Cal_AllowReminders;
		/**
		 * @var	bool
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
		 * @var	string
		 */
		var $GlobalAddressBook;

		/**
		 * @var	bool
		 */
		var $EnableMobileSync;
		
		/**
		 * @var	string
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
		 * @var bool
		 */
		var $FlagsLangSelect;
		
		/**
		 * @var bool
		 */
		var $SaveInSent;

		/**
		 * @var int
		 */
		var $ViewMode;

		/**
		 * @var string
		 */
		var $Dev;
		
		/**
		 * @var	bool
		 */
		var $isLoad;
		
		/**
		 * @var	bool
		 */
		var $_langIsInclude;
		
		/**
		 * @var	string
		 */
		var $_path;
		
		/**
		 * @var	string
		 */
		var $_webpath;
		
		/**
		* @param	string	$dataFolderPath
		*/
		function WebMail_Settings($dataFolderPath, $webFolderPath)
		{
			$this->_path =& $dataFolderPath;
			$this->_webpath =& $webFolderPath;

			$this->UseDsn = false;
			$this->AllowUsersChangeAccountsDef = false;
			$this->AllowContacts = true;
			$this->AllowCalendar = true;
			$this->Imap4DeleteLikePop3 = true;
			$this->AllowLanguageOnLogin = true;
			$this->UseCaptcha = true;

			$this->EnableWmServer = false;
			$this->WmServerHost = '127.0.0.1';
			$this->WmAllowManageXMailAccounts = false;

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

			$this->AllowInsertImage = true;
			$this->AllowBodySize = false;
			$this->MaxBodySize = 600;
			$this->MaxSubjectSize = 255;

			$this->AllowRegistration = false;
			$this->AllowPasswordReset = false;

			$this->EnableEventsLogging = false;
			$this->LoggingSpecialUsers = false;
			$this->LogLevel = WM_LOG_LEVEL_DEBUG;

			$this->UseMultipleDomainsSelection = false;
			$this->GlobalAddressBook = WM_GLOBAL_ADDRESS_BOOK_DOMAIN;
			$this->EnableMobileSync = false;
			$this->MobileSyncUrl = 'http://your.host.com:8080/funambol/ds';
			$this->MobileSyncContactDataBase =  'card';
			$this->MobileSyncCalendarDataBase =  'cal';
			$this->FlagsLangSelect = false;
			$this->ViewMode = WM_NEW_VIEW_MODE_CENTRAL_LIST_PANE;
			$this->SaveInSent = WM_SAVE_IN_SENT_ALWAYS;

			$this->isLoad = false;
			$this->_langIsInclude = false;

		    $xmlDocument = new XmlDocument();
		    if ($xmlDocument->LoadFromFile($this->_path . '/settings/settings.xml'))
		    {
		    	$this->isLoad = true;
		    	$this->_loadFromXML($xmlDocument->XmlRoot);
		    	
    			/* custom class */
				ap_Custom::StaticUseMethod('wm_ChangeSettingsAfterLoad', array(&$this));
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
						$this->WindowTitle = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;					
					case 'WindowTitle':
						$this->WindowTitle = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'LicenseKey':
						$this->LicenseKey = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'AdminPassword':
						$this->AdminPassword = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'DBType':
						$this->DbType = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'DBLogin':
						$this->DbLogin = trim(ap_Utils::DecodeSpecialXmlChars($node->Value));
						break;
					case 'DBPassword':
						$this->DbPassword = trim(ap_Utils::DecodeSpecialXmlChars($node->Value));
						break;
					case 'DBName':
						$this->DbName = trim(ap_Utils::DecodeSpecialXmlChars($node->Value));
						break;
					case 'UseDsn':
						$this->UseDsn = (bool) $node->Value;
						break;
					case 'DBDSN':
						$this->DbDsn = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'DBHost':
						$this->DbHost = trim(ap_Utils::DecodeSpecialXmlChars($node->Value));
						break;
					case 'UseCustomConnectionString':
						$this->UseCustomConnectionString = (bool) $node->Value;
						break;
					case 'DBCustomConnectionString':
						$this->DbCustomConnectionString = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'DBPrefix':
						$this->DbPrefix = ap_Utils::ClearPrefix(ap_Utils::DecodeSpecialXmlChars($node->Value)); 
						break;
					case 'IncomingMailProtocol':
						$this->IncomingMailProtocol = (int) $node->Value;
						break;
					case 'IncomingMailServer':
						$this->IncomingMailServer = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'IncomingMailPort':
						$this->IncomingMailPort = (int) $node->Value;
						break;
					case 'OutgoingMailServer':
						$this->OutgoingMailServer = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'OutgoingMailPort':
						$this->OutgoingMailPort = (int) $node->Value;
						break;
					case 'ReqSmtpAuth':
						$this->ReqSmtpAuth = (bool) $node->Value;
						break;
					case 'AllowAdvancedLogin':
						$this->AllowAdvancedLogin = (bool) $node->Value;
						break;
					case 'HideLoginMode':
						$this->HideLoginMode = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'DefaultDomainOptional':
						$this->DefaultDomainOptional = ap_Utils::DecodeSpecialXmlChars($node->Value);
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
						$this->MailsPerPage = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'EnableAttachmentSizeLimit':
						$this->EnableAttachmentSizeLimit = (bool) $node->Value;
						break;						
					case 'AttachmentSizeLimit':
						$this->AttachmentSizeLimit = GetGoodBigInt(ap_Utils::DecodeSpecialXmlChars($node->Value));
						break;
					case 'EnableMailboxSizeLimit':
						$this->EnableMailboxSizeLimit = (bool) $node->Value;
						break;
					case 'MailboxSizeLimit':
						$this->MailboxSizeLimit = GetGoodBigInt(ap_Utils::DecodeSpecialXmlChars($node->Value));
						break;
					case 'TakeImapQuota':
						$this->TakeImapQuota = (bool) $node->Value;
						break;
					case 'AllowUsersChangeTimeZone':
						$this->AllowUsersChangeTimeZone = (bool) $node->Value;
						break;
					case 'DefaultUserCharset':
						$this->DefaultUserCharset = CWebMail_Plugin::GetCodePageName($node->Value);
						break;
					case 'AllowUsersChangeCharset':
						$this->AllowUsersChangeCharset = (bool) $node->Value;
						break;
					case 'DefaultSkin':
						$this->DefaultSkin = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'AllowUsersChangeSkin':
						$this->AllowUsersChangeSkin = (bool) $node->Value;
						break;
					case 'DefaultLanguage':
						$this->DefaultLanguage = ap_Utils::DecodeSpecialXmlChars($node->Value);
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
						$this->AllowNewUsersRegister = (bool) $node->Value;
						break;
					case 'AllowUsersAddNewAccounts':
						$this->AllowUsersAddNewAccounts = (bool) $node->Value;
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
						$this->WmServerRootPath = rtrim(ap_Utils::DecodeSpecialXmlChars($node->Value), '\\/');
						break;
					case 'WmServerHost':
						$this->WmServerHost = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;	
					case 'WmAllowManageXMailAccounts':
						$this->WmAllowManageXMailAccounts = (bool) $node->Value;
						break;						
					case 'AllowContacts':
						$this->AllowContacts = (bool) $node->Value;
						break;	
					case 'AllowCalendar':
						$this->AllowCalendar = (bool) $node->Value;
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
						$this->Cal_DefaultCountry = ap_Utils::DecodeSpecialXmlChars($node->Value);
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
						$this->IdleSessionTimeout = (int) $node->Value;
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
						$this->GlobalAddressBook = $node->Value;
						break;
					case 'EnableMobileSync':
						$this->EnableMobileSync =  (bool) $node->Value;
						break;
					case 'MobileSyncUrl':
						$this->MobileSyncUrl = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'MobileSyncContactDataBase':
						$this->MobileSyncContactDataBase = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'MobileSyncCalendarDataBase':
						$this->MobileSyncCalendarDataBase = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
					case 'FlagsLangSelect':
						$this->FlagsLangSelect =  (bool) $node->Value;
						break;
					case 'ViewMode':
						$this->ViewMode = (int) $node->Value;
						break;
					case 'SaveInSent':
						$this->SaveInSent = (int) $node->Value;
						break;
					case 'Dev':
						$this->Dev = ap_Utils::DecodeSpecialXmlChars($node->Value);
						break;
				}
			}
		}

		function AddNode(&$node, $name, $value)
		{
			if (!is_int($value))
			{
				$value = ap_Utils::EncodeSpecialXmlChars($value);
			}
			
			$node->AppendChild(new XmlDomNode($name, $value));
		}
		
		/**
		 * @return bool
		 */
		function SaveToXml()
		{
			$xmlDocument = new XmlDocument();
			$xmlDocument->CreateElement('Settings');
			$xmlDocument->XmlRoot->AppendAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
			$xmlDocument->XmlRoot->AppendAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
			
			$common = new XmlDomNode('Common');
			$this->AddNode($common, 'SiteName',							$this->WindowTitle);
			$this->AddNode($common, 'LicenseKey',						$this->LicenseKey);
			$this->AddNode($common, 'AdminPassword',					$this->AdminPassword);
			$this->AddNode($common, 'DBType',							(int) $this->DbType);
			$this->AddNode($common, 'DBLogin',							$this->DbLogin);
			$this->AddNode($common, 'DBPassword',						$this->DbPassword);
			$this->AddNode($common, 'DBName',							$this->DbName);
			$this->AddNode($common, 'UseDsn',							(int) $this->UseDsn);
			$this->AddNode($common, 'DBDSN',							$this->DbDsn);
			$this->AddNode($common, 'DBHost',							$this->DbHost);
			$this->AddNode($common, 'UseCustomConnectionString',		(int) $this->UseCustomConnectionString);
			$this->AddNode($common, 'DBCustomConnectionString',			$this->DbCustomConnectionString);
			$this->AddNode($common, 'DBPrefix',							ap_Utils::ClearPrefix($this->DbPrefix));
			$this->AddNode($common, 'DefaultSkin',						$this->DefaultSkin);
			$this->AddNode($common, 'AllowUsersChangeSkin',				(int) $this->AllowUsersChangeSkin);
			$this->AddNode($common, 'DefaultLanguage',					$this->DefaultLanguage);
			$this->AddNode($common, 'AllowUsersChangeLanguage',			(int) $this->AllowUsersChangeLanguage);
			$this->AddNode($common, 'EnableMobileSync',					(bool) $this->EnableMobileSync);
			$this->AddNode($common, 'MobileSyncUrl',					$this->MobileSyncUrl);
			$this->AddNode($common, 'MobileSyncContactDataBase',		$this->MobileSyncContactDataBase);
			$this->AddNode($common, 'MobileSyncCalendarDataBase',		$this->MobileSyncCalendarDataBase);
			$xmlDocument->XmlRoot->AppendChild($common);

			$webmail = new XmlDomNode('WebMail');
			$this->AddNode($webmail, 'IncomingMailProtocol',			(int) $this->IncomingMailProtocol);
			$this->AddNode($webmail, 'IncomingMailServer',				$this->IncomingMailServer);
			$this->AddNode($webmail, 'IncomingMailPort',				(int) $this->IncomingMailPort);
			$this->AddNode($webmail, 'OutgoingMailServer',				$this->OutgoingMailServer);
			$this->AddNode($webmail, 'OutgoingMailPort',				(int) $this->OutgoingMailPort);
			$this->AddNode($webmail, 'ReqSmtpAuth',						(int) $this->ReqSmtpAuth);

			$this->AddNode($webmail, 'AllowAdvancedLogin',				(int) $this->AllowAdvancedLogin);
			$this->AddNode($webmail, 'HideLoginMode',					(int) $this->HideLoginMode);
			$this->AddNode($webmail, 'DefaultDomainOptional',			$this->DefaultDomainOptional);
			$this->AddNode($webmail, 'UseMultipleDomainsSelection',		(int) $this->UseMultipleDomainsSelection);
			$this->AddNode($webmail, 'UseCaptcha',						(int) $this->UseCaptcha);

			$this->AddNode($webmail, 'ShowTextLabels',					(int) $this->ShowTextLabels);
			$this->AddNode($webmail, 'AutomaticCorrectLoginSettings',	(int) $this->AutomaticCorrectLoginSettings);

			$EnableLogging = (int) $this->EnableLogging;
			if ($this->LoggingSpecialUsers)
			{
				$EnableLogging++;
			}

			$this->AddNode($webmail, 'EnableLogging',					(int) $EnableLogging);
			$this->AddNode($webmail, 'LogLevel',						(int) $this->LogLevel);
			$this->AddNode($webmail, 'EnableEventsLogging',				(int) $this->EnableEventsLogging);
			$this->AddNode($webmail, 'DisableErrorHandling',			(int) $this->DisableErrorHandling);
			$this->AddNode($webmail, 'AllowAjax',						(int) $this->AllowAjax);
			$this->AddNode($webmail, 'MailsPerPage',					(int) $this->MailsPerPage);
			$this->AddNode($webmail, 'EnableAttachmentSizeLimit',		(int) $this->EnableAttachmentSizeLimit);
			$this->AddNode($webmail, 'AttachmentSizeLimit',				GetGoodBigInt($this->AttachmentSizeLimit));
			$this->AddNode($webmail, 'EnableMailboxSizeLimit',			(int) $this->EnableMailboxSizeLimit);
			$this->AddNode($webmail, 'MailboxSizeLimit',				GetGoodBigInt($this->MailboxSizeLimit));
			$this->AddNode($webmail, 'TakeImapQuota',					(int) $this->TakeImapQuota);

			$this->AddNode($webmail, 'DefaultTimeZone',					$this->DefaultTimeZone);
			$this->AddNode($webmail, 'AllowUsersChangeTimeZone',		(int) $this->AllowUsersChangeTimeZone);
			$this->AddNode($webmail, 'DefaultUserCharset',				(int) CWebMail_Plugin::GetCodePageNumber($this->DefaultUserCharset));
			$this->AddNode($webmail, 'AllowUsersChangeCharset',			(int) $this->AllowUsersChangeCharset);
			$this->AddNode($webmail, 'AllowDHTMLEditor',				(int) $this->AllowDhtmlEditor);
			$this->AddNode($webmail, 'AllowUsersChangeEmailSettings',	(int) $this->AllowUsersChangeEmailSettings);
			$this->AddNode($webmail, 'AllowDirectMode',					(int) $this->AllowDirectMode);
			$this->AddNode($webmail, 'DirectModeIsDefault',				(int) $this->DirectModeIsDefault);
			$this->AddNode($webmail, 'AllowNewUsersRegister',			(int) $this->AllowNewUsersRegister);
			$this->AddNode($webmail, 'AllowUsersAddNewAccounts',		(int) $this->AllowUsersAddNewAccounts);
			$this->AddNode($webmail, 'AllowUsersChangeAccountsDef',		(int) $this->AllowUsersChangeAccountsDef);

			$this->AddNode($webmail, 'StoreMailsInDb',					(int) $this->StoreMailsInDb);
			$this->AddNode($webmail, 'AllowContacts',					(int) $this->AllowContacts);
			$this->AddNode($webmail, 'AllowCalendar',					(int) $this->AllowCalendar);
			$this->AddNode($webmail, 'AllowLanguageOnLogin',			(int) $this->AllowLanguageOnLogin);
			$this->AddNode($webmail, 'Imap4DeleteLikePop3',				(int) $this->Imap4DeleteLikePop3);
			
			$this->AddNode($webmail, 'AllowInsertImage',				(int) $this->AllowInsertImage);
			$this->AddNode($webmail, 'AllowBodySize',					(int) $this->AllowBodySize);
			$this->AddNode($webmail, 'MaxBodySize',						(int) $this->MaxBodySize);
			$this->AddNode($webmail, 'MaxSubjectSize',					(int) $this->MaxSubjectSize);

			$this->AddNode($webmail, 'EnableWmServer',					(int) $this->EnableWmServer);
			$this->AddNode($webmail, 'WmServerRootPath',				$this->WmServerRootPath);
			$this->AddNode($webmail, 'WmServerHost',					$this->WmServerHost);
			$this->AddNode($webmail, 'WmAllowManageXMailAccounts',		(int) $this->WmAllowManageXMailAccounts);
			$this->AddNode($webmail, 'IdleSessionTimeout',				(int) $this->IdleSessionTimeout);
			$this->AddNode($webmail, 'AllowRegistration',				(int) $this->AllowRegistration);
			$this->AddNode($webmail, 'AllowPasswordReset',				(int) $this->AllowPasswordReset);
			$this->AddNode($webmail, 'GlobalAddressBook',				$this->GlobalAddressBook);
			$this->AddNode($webmail, 'FlagsLangSelect',					(int) $this->FlagsLangSelect);
			$this->AddNode($webmail, 'ViewMode',						(int) $this->ViewMode);
			$this->AddNode($webmail, 'SaveInSent',						(int) $this->SaveInSent);
			$xmlDocument->XmlRoot->AppendChild($webmail);
			
			$calendar = new XmlDomNode('Calendar');
			$this->AddNode($calendar, 'DefaultTimeFormat',				(int) $this->Cal_DefaultTimeFormat);
			$this->AddNode($calendar, 'DefaultDateFormat',				(int) $this->Cal_DefaultDateFormat);
			$this->AddNode($calendar, 'ShowWeekends',					(int) $this->Cal_ShowWeekends);
			$this->AddNode($calendar, 'WorkdayStarts',					(int) $this->Cal_WorkdayStarts);
			$this->AddNode($calendar, 'WorkdayEnds',					(int) $this->Cal_WorkdayEnds);
			$this->AddNode($calendar, 'ShowWorkDay',					(int) $this->Cal_ShowWorkDay);
			$this->AddNode($calendar, 'WeekStartsOn',					(int) $this->Cal_WeekStartsOn);
			$this->AddNode($calendar, 'DefaultTab',						(int) $this->Cal_DefaultTab);
			$this->AddNode($calendar, 'DefaultCountry',					$this->Cal_DefaultCountry);
			$this->AddNode($calendar, 'DefaultTimeZone',				(int) $this->Cal_DefaultTimeZone);
			$this->AddNode($calendar, 'AllTimeZones',					(int) $this->Cal_AllTimeZones);
			$this->AddNode($calendar, 'AllowReminders',					(int) $this->Cal_AllowReminders);
			$this->AddNode($calendar, 'AutoAddInvitation',				(int) $this->Cal_AutoAddInvitation);
			$xmlDocument->XmlRoot->AppendChild($calendar);
			
			if (strlen($this->Dev) > 0)
			{
				$xmlDocument->XmlRoot->AppendChild(new XmlDomNode('Dev', ap_Utils::EncodeSpecialXmlChars($this->Dev)));
			}

			if (AP_USE_XML_CACHE)
			{
				$out = array();
				foreach ($this as $key => $value)
				{

					if (strlen($key) > 0)
					{
						if ($key[0] === '_' || $key === 'isLoad')
						{
							continue;
						}
						
						if (is_int($value))
						{
							$out[] = '\''.$key.'\'=>'.$value;
						}
						else
						{
							$out[] = '\''.$key.'\'=>\''.ap_Utils::ClearStringValue(ap_Utils::EncodeSpecialXmlChars($value), '\'').'\'';
						}
					}
				}
				
				file_put_contents($this->_path.'/settings/settings.xml.cache', '<?php return array('.implode(",\r\n", $out).');');
			}
			
			return $xmlDocument->SaveToFile($this->_path.'/settings/settings.xml');
		}
	}