<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

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
		var $MailProtocol = WM_MAILPROTOCOL_POP3;

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
		var $MailOutAuthentication = 1;

		/**
		 * @var string
		 */
		var $FriendlyName;

		/**
		 * @var bool
		 */
		var $UseFriendlyName = 1;

		/**
		 * @var int
		 */
		var $DefaultOrder = 0;

		/**
		 * @var bool
		 */
		var $GetMailAtLogin = true;

		/**
		 * @var short
		 */
		var $MailMode = WM_MAILMODE_LeaveMessagesOnServer;

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
		var $MailsPerPage = 20;

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
		var $DefaultSkin = WM_DEFAULT_SKIN;

		/**
		 * @var string
		 */
		var $DefaultLanguage;

		/**
		 * @var string
		 */
		var $DefaultIncCharset = 'iso-8859-1';

		/**
		 * @var string
		 */
		var $DefaultOutCharset = 'iso-8859-1';

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
		var $DefaultTimeFormat = 1; // 0/1 - 24/12

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
		var $DbCharset = 'utf-8';
		
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
		var $ViewMode = WM_VIEW_MODE_PREVIEW_PANE_NO_IMG;
		
		/**
		 * @var array
		 */
		var $Columns;

		/**
		 * @var array
		 */
		var $Aliases;

		/**
		 * @var array
		 */
		var $Forwards;

		/**
		 * @var array
		 */
		var $MailingList;
		
		/**
		 * @var	int
		 */
		var $DomainId;
		
		/**
		 * @var bool
		 */
		var $IsMailList = false;

		/**
		 * @var bool
		 */
		var $IsInternal = false;

		/**
		 * @var int
		 */
		var $ImapQuota = 0;

		/**
		 * @var WebMail_Settings
		 */
		var $_settings;
		
		/**
		 * 
		 * @param	WebMail_Settings	$settings
		 * @param	CWebMailDomain		$domain[optional] = null
		 * @return Account
		 */
		function Account($settings, $domain = null)
		{
			$this->_settings =& $settings;
			if ($settings->MailsPerPage > 0)
			{
				$this->MailsPerPage = (int) $settings->MailsPerPage;
			}

			$this->DefaultSkin = $settings->DefaultSkin;
			$this->DefaultLanguage = $settings->DefaultLanguage;
			$this->DefaultTimeZone = $settings->DefaultTimeZone;
			$this->MailboxLimit = GetGoodBigInt($settings->MailboxSizeLimit);
			$this->AllowDirectMode = $settings->AllowDirectMode;
			$this->AllowChangeSettings = $settings->AllowUsersChangeEmailSettings;
			$this->ViewMode = $settings->ViewMode;

			if ($domain !== null)
			{
				$this->MailProtocol = $domain->_mailProtocol;
				
				$this->DomainId = $domain->_id;
				$this->MailIncHost = $domain->_mailIncomingHost;
				$this->MailIncPort = $domain->_mailIncomingPort;
				$this->MailOutHost = $domain->_mailSmtpHost;
				$this->MailOutPort = $domain->_mailSmtpPort;
				
				$this->MailOutAuthentication = $domain->_mailSmtpAuth;

				$this->IsInternal = (bool) $domain->_isInternal;
			}
			
			$this->DefaultIncCharset = $settings->DefaultUserCharset;
			$this->DefaultOutCharset = $settings->DefaultUserCharset;
			
			$this->Columns = array();
			$this->Aliases = array();
			$this->Forwards = array();
			$this->MailingList = array();
			
			$this->AllowDhtmlEditor = $settings->AllowDhtmlEditor;

			$this->ImapQuota = (int) $settings->TakeImapQuota;
			$this->ViewMode = (int) $settings->ViewMode;
			
		    /* custom class */
			ap_Custom::StaticUseMethod('wm_ChangeAccountAfterClassCreate', array(&$this));
		}
		
		/**
		 * @return	int
		 */
		function GetDefaultFolderSync()
		{
			$s = WM_FOLDERSYNC_AllHeadersOnly;
			if ($this->_settings && $this->_settings->AllowDirectMode && $this->_settings->DirectModeIsDefault && $this->MailProtocol != WM_MAILPROTOCOL_WMSERVER)
			{							
				$s = WM_FOLDERSYNC_DirectMode;
			}
			else 
			{
				switch ($this->MailProtocol)
				{
					case WM_MAILPROTOCOL_POP3:
						$s = WM_FOLDERSYNC_AllEntireMessages;
						break;
					case WM_MAILPROTOCOL_IMAP4:
						$s = WM_FOLDERSYNC_DirectMode;
						//$s = WM_FOLDERSYNC_AllHeadersOnly;
						break;
				}
			}
			
			/* custom class */
			ap_Custom::StaticUseMethod('wm_UpdateDefaultFolderSync', array(&$s, $this->MailProtocol));
			
			return $s;
		}
		
		function SetSessionArray()
		{
			$array = array(
'Id' => $this->Id,
'IdUser' => $this->IdUser,
'DomainId' => $this->DomainId,
'DefaultAccount' => $this->DefaultAccount,
'Email' => $this->Email,
'MailProtocol' => $this->MailProtocol,
'MailIncHost' => $this->MailIncHost,
'MailIncLogin' => $this->MailIncLogin,
'MailIncPassword' => $this->MailIncPassword,
'MailIncPort' => $this->MailIncPort,
'MailOutHost' => $this->MailOutHost,
'MailOutLogin' => $this->MailOutLogin,
'MailOutPassword' => $this->MailOutPassword,
'MailOutPort' => $this->MailOutPort,
'MailOutAuthentication' => $this->MailOutAuthentication,
'AllowDirectMode' => $this->AllowDirectMode,
'FriendlyName' => $this->FriendlyName,
'UseFriendlyName' => $this->UseFriendlyName,
'GetMailAtLogin' => $this->GetMailAtLogin,
'MailMode' => $this->MailMode,
'MailsOnServerDays' => $this->MailsOnServerDays,
'MailboxLimit' => $this->MailboxLimit,
'AllowChangeSettings' => $this->AllowChangeSettings,
'ImapQuota' => $this->ImapQuota,

'MailsPerPage' => $this->MailsPerPage,
'ContactsPerPage' => $this->ContactsPerPage,
'AllowDhtmlEditor' => $this->AllowDhtmlEditor,
'ViewMode' => $this->ViewMode,
'DefaultSkin' => $this->DefaultSkin,
'DefaultIncCharset' => $this->DefaultIncCharset,
'DefaultTimeZone' => $this->DefaultTimeZone,
'DefaultLanguage' => $this->DefaultLanguage
				);
					
			$_SESSION[WM_SESS_ACCOUNT] = $array;
		}
		
		function ClearSessionArray()
		{
			if (isset($_SESSION[WM_SESS_ACCOUNT]))
			{
				unset($_SESSION[WM_SESS_ACCOUNT]);
			}
		}

		/**
		 * @return bool
		 */
		function IsSessionData()
		{
			return isset($_SESSION[WM_SESS_ACCOUNT]);
		}
		
		function UpdateFromSessionArray()
		{
			$sessionArray = isset($_SESSION[WM_SESS_ACCOUNT]) ? $_SESSION[WM_SESS_ACCOUNT] : array();
			if (count($sessionArray) > 0)
			{
				$this->Id = ap_Utils::ArrayValue($sessionArray, 'Id', $this->Id); 
				$this->IdUser = ap_Utils::ArrayValue($sessionArray, 'IdUser', $this->IdUser);
				$this->DomainId = ap_Utils::ArrayValue($sessionArray, 'DomainId', $this->DomainId);
				$this->DefaultAccount = ap_Utils::ArrayValue($sessionArray, 'DefaultAccount', $this->DefaultAccount);
				$this->Email = ap_Utils::ArrayValue($sessionArray, 'Email', $this->Email);
				$this->MailProtocol = ap_Utils::ArrayValue($sessionArray, 'MailProtocol', $this->MailProtocol);
				$this->MailIncHost = ap_Utils::ArrayValue($sessionArray, 'MailIncHost', $this->MailIncHost);
				$this->MailIncLogin = ap_Utils::ArrayValue($sessionArray, 'MailIncLogin', $this->MailIncLogin);
				$this->MailIncPassword = ap_Utils::ArrayValue($sessionArray, 'MailIncPassword', $this->MailIncPassword);
				$this->MailIncPort = ap_Utils::ArrayValue($sessionArray, 'MailIncPort', $this->MailIncPort);
				$this->MailOutHost = ap_Utils::ArrayValue($sessionArray, 'MailOutHost', $this->MailOutHost);
				
				$this->MailOutLogin = ap_Utils::ArrayValue($sessionArray, 'MailOutLogin', $this->MailOutLogin);
				$this->MailOutPassword = ap_Utils::ArrayValue($sessionArray, 'MailOutPassword', $this->MailOutPassword);
				$this->MailOutPort = ap_Utils::ArrayValue($sessionArray, 'MailOutPort', $this->MailOutPort);
				$this->MailOutAuthentication = ap_Utils::ArrayValue($sessionArray, 'MailOutAuthentication', $this->MailOutAuthentication);
				$this->AllowDirectMode = ap_Utils::ArrayValue($sessionArray, 'AllowDirectMode', $this->AllowDirectMode);
				$this->FriendlyName = ap_Utils::ArrayValue($sessionArray, 'FriendlyName', $this->FriendlyName);
				$this->UseFriendlyName = ap_Utils::ArrayValue($sessionArray, 'UseFriendlyName', $this->UseFriendlyName);
				$this->GetMailAtLogin = ap_Utils::ArrayValue($sessionArray, 'GetMailAtLogin', $this->GetMailAtLogin);
				$this->MailMode = ap_Utils::ArrayValue($sessionArray, 'MailMode', $this->MailMode);
				
				$this->MailsOnServerDays = ap_Utils::ArrayValue($sessionArray, 'MailsOnServerDays', $this->MailsOnServerDays);
				$this->MailboxLimit = ap_Utils::ArrayValue($sessionArray, 'MailboxLimit', $this->MailboxLimit);
				$this->AllowChangeSettings = ap_Utils::ArrayValue($sessionArray, 'AllowChangeSettings', $this->AllowChangeSettings);

				$this->ImapQuota = (int) ap_Utils::ArrayValue($sessionArray, 'ImapQuota', $this->ImapQuota);

				$this->MailsPerPage = (int) ap_Utils::ArrayValue($sessionArray, 'MailsPerPage', $this->MailsPerPage);
				$this->ContactsPerPage = (int) ap_Utils::ArrayValue($sessionArray, 'ContactsPerPage', $this->ContactsPerPage);
				$this->ViewMode = (int) ap_Utils::ArrayValue($sessionArray, 'ViewMode', $this->ViewMode);
				$this->DefaultSkin = ap_Utils::ArrayValue($sessionArray, 'DefaultSkin', $this->DefaultSkin);
				$this->DefaultIncCharset = ap_Utils::ArrayValue($sessionArray, 'DefaultIncCharset', $this->DefaultIncCharset);
				$this->DefaultTimeZone = ap_Utils::ArrayValue($sessionArray, 'DefaultTimeZone', $this->DefaultTimeZone);
				$this->DefaultLanguage = ap_Utils::ArrayValue($sessionArray, 'DefaultLanguage', $this->DefaultLanguage);
				
				$this->ClearSessionArray();
			}
/*

'Deleted' => $this->Deleted,
'DefaultOrder' => $this->DefaultOrder,
'Signature' => $this->Signature,
'SignatureType' => $this->SignatureType,
'SignatureOptions' => $this->SignatureOptions,
'HideContacts' => $this->HideContacts,
'Delimiter' => $this->Delimiter,
'MailsPerPage' => $this->MailsPerPage,
'WhiteListing' => $this->WhiteListing,
'XSpam' => $this->XSpam,
'LastLogin' => $this->LastLogin,
'LoginsCount' => $this->LoginsCount,
'DefaultSkin' => $this->DefaultSkin,
'DefaultLanguage' => $this->DefaultLanguage,
'DefaultIncCharset' => $this->DefaultIncCharset,
'DefaultOutCharset' => $this->DefaultOutCharset,
'DefaultTimeZone' => $this->DefaultTimeZone,			
'DefaultDateFormat' => $this->DefaultDateFormat,
'DefaultTimeFormat' => $this->DefaultTimeFormat,
'HideFolders' => $this->HideFolders,
'MailboxSize' => $this->MailboxSize,
'AllowDhtmlEditor' => $this->AllowDhtmlEditor,			
'DbCharset' => $this->DbCharset,			
'HorizResizer' => $this->HorizResizer,			
'VertResizer' => $this->VertResizer,			
'Mark' => $this->Mark,
'Reply' => $this->Reply,
'ContactsPerPage' => $this->ContactsPerPage,
'ViewMode' => $this->ViewMode
			*/
		}
		
		/**
		 * @return string/boot
		 */
		function ValidateData()
		{
			if (!ap_Utils::CheckFileName($this->Email))
			{
				return 'You should specify a correct e-mail.';
			}
			elseif(empty($this->Email))
			{
				return 'You cannot leave Email field blank';
			}
			elseif(!ap_Utils::checkEmail($this->Email))
			{
				return 'You should specify a correct e-mail.';
			}
			elseif(empty($this->MailIncLogin))
			{
				return 'You cannot leave Login field blank.';
			}
			elseif(empty($this->MailIncPassword))
			{
				return 'You cannot leave Password field blank.';
			}
			elseif(empty($this->MailIncHost))
			{
				return 'You cannot leave POP3(IMAP4) Server field blank.';
			}
			elseif(!ap_Utils::checkServerName($this->MailIncHost))
			{
				return 'You should specify a correct POP3(IMAP) server address.';
			}
			elseif(empty($this->MailIncPort))
			{
				return 'You cannot leave POP3(IMAP4) Server Port field blank.';
			}
			elseif(!ap_Utils::checkPort($this->MailIncPort))
			{
				return 'You should specify a positive number in POP3(IMAP4) port field. Default POP3(IMAP4) port number is 110(143).';
			}
			elseif(empty($this->MailOutHost))
			{
				return 'You should specify a correct SMTP server address.';
			}
			elseif(!ap_Utils::checkServerName($this->MailOutHost))
			{
				return 'You should specify a correct SMTP server address.';
			}
			elseif(empty($this->MailOutPort))
			{
				return 'You cannot leave SMTP Server Port field blank.';
			}
			elseif(!ap_Utils::checkPort($this->MailOutPort))
			{
				return 'You should specify a positive number in SMTP port field. Default SMTP port number is 25.';
			}				
			return true;	
		}
	}
	