<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));

	define('ErrorDesc', 'ErrorDesc');
	define('MEMORYLIMIT', '200M');
	define('TIMELIMIT', 3000);
	define('RESET_TIME_LIMIT', 60);
	define('RESET_TIME_LIMIT_RUN', (int) (RESET_TIME_LIMIT / 2));

	define('DEFAULT_SKIN', 'AfterLogic');
	define('SESSION_LANG', 'session_lang');
	define('ATTACH_DIR', 'attachtempdir');
	define('MAILADMLOGIN', 'mailadm');
	
	define('DEFAULTORDER_DateDesc', 0);
	define('DEFAULTORDER_Date', 1);

	define('DEFAULTORDER_FromDesc', 2);
	define('DEFAULTORDER_From', 3);

	define('DEFAULTORDER_ToDesc', 4);
	define('DEFAULTORDER_To', 5);

	define('DEFAULTORDER_SizeDesc', 6);
	define('DEFAULTORDER_Size', 7);

	define('DEFAULTORDER_SubjDesc', 8);
	define('DEFAULTORDER_Subj', 9);

	define('DEFAULTORDER_AttachDesc', 10);
	define('DEFAULTORDER_Attach', 11);

	define('DEFAULTORDER_FlagDesc', 12);
	define('DEFAULTORDER_Flag', 13);
	
	define('USE_DB', true);
	define('USE_LDAP_LOGIN', false);
		define('LDAP_LOGIN_HOST', '192.168.0.230');
		define('LDAP_LOGIN_PORT', '389');
		define('LDAP_LOGIN_BIND_DN', 'cn=Directory Manager');
		define('LDAP_LOGIN_PASSWORD', 'password');
		define('LDAP_LOGIN_DN', 'ou=People,o=subdomain,o=domain');

	define('USE_LDAP_SETTINGS_STORAGE', false);
		define('LDAP_SETTINGS_FIELD', 'nswmExtendedUserPrefs');
		define('LDAP_SETTINGS_HOST', LDAP_LOGIN_HOST);
		define('LDAP_SETTINGS_PORT', LDAP_LOGIN_PORT);
		define('LDAP_SETTINGS_BIND_DN', 'cn=Directory Manager');
		define('LDAP_SETTINGS_PASSWORD', 'password');

	define('USE_LDAP_CONTACT', false);
		define('LDAP_CONTACT_HOST', LDAP_LOGIN_HOST);
		define('LDAP_CONTACT_PORT', LDAP_LOGIN_PORT);
		define('LDAP_CONTACT_BIND_DN', LDAP_LOGIN_BIND_DN);
		define('LDAP_CONTACT_PASSWORD', LDAP_LOGIN_PASSWORD);
		define('LDAP_CONTACT_DN', 'o=pab');
	
	$_app_version = @file_get_contents(WM_ROOTPATH.'VERSION');
	define('WMVERSION', (false === $_app_version) ? '0.0.0' : $_app_version);

	define('USE_JS_GZIP', true);
	define('USE_PROCESSING_GZIP', false);
	define('USE_INDEX_GZIP', false);
	define('CATCHA_COUNT_LIMIT', 3);

	define('USE_IFRAME_WEBMAIL', null); // iframe-webmail.php
	
	define('IS_SUPPORT_GZIP', (bool) ((strpos(isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '', 'gzip') !== false) && function_exists('gzencode')));
	define('IS_SUPPORT_ICONV', function_exists('iconv'));

	define('SESSION_RESET_STEP', 'sessionresetstep');
	define('SESSION_RESET_ACCT_ID', 'sessionresetacctid');

	define('MOBILE_SYNC_LOGIN_PREFIX', 'wm_');

	define('XTYPE', false);
	define('USE_FULLPARSE_XML_LOG', false);

	define('POP3_PROTOCOL', 0);
	define('IMAP4_PROTOCOL', 1);
	
	define('GETFOLDERBASECOUNT', 5);
	
	define('SOCKET_CONNECT_TIMEOUT', 20);
	define('SOCKET_FGET_TIMEOUT', 60);
	
	define('PRELOADBODYSIZE', 76800);
	
	define('JS_TIMEOFFSET', 'jstimeoffset');
	
	define('ACCOUNT_ID', 'id_account');
	define('ACCOUNT_OBJ', 'account_obj');
	define('ACCOUNT_FOLDERS', 'account_folders');
	define('ACCOUNT_IDS', 'all_accounts_ids');
	define('USER_ID', 'AUserId');
	define('CALENDAR_ID', 'PubCalendarId');
	define('ACCESS_LEVEL', 'PubCalendarAccess');
	define('SEPARATED', 'separated_apl');
	
	define('XMAILHOST', 'LOCALXMAIL');
	define('XMAILERHEADERVALUE', 'AfterLogic WebMail PHP');
	
	define('DUMMYPASSWORD', '1111111111111111111111');
	define('MAX_INT', 1023998976);

	defined('INFORMATION') || define('INFORMATION', 'information');
	defined('ISINFOERROR') || define('ISINFOERROR', 'infoErr');
	
	define('DEMO_SES', 'demoses');
		define('DEMO_S_ContactsPerPage', 'contactsperpage');
		define('DEMO_S_MessagesPerPage', 'messagesperpage');
		define('DEMO_S_AllowDhtmlEditor', 'allowdhtmleditor');
		define('DEMO_S_DefaultSkin', 'defaultskin');
		define('DEMO_S_DefaultOutCharset', 'defaultoutcharset');
		define('DEMO_S_DefaultTimeZone', 'defaulttimezone');
		define('DEMO_S_DefaultLanguage', 'defaultlanguage');
		define('DEMO_S_DefaultDateFormat', 'defaultdateformat');
		define('DEMO_S_DefaultTimeFormat', 'defaulttimeformat');
		define('DEMO_S_ViewMode', 'viewmode');
		define('DEMO_S_AutoCheckMailInterval', 'autocheckmailinterval');

	define('RTL_ARRAY', 'Hebrew|Arabic');

	define('MAX_ENVELOPES_PER_SESSION', 20);
	define('BODYSTRUCTURE_MGSSIZE_LIMIT', 20000);

	define('IMAP_BS_ENCODETYPE_BASE64', 0);
	define('IMAP_BS_ENCODETYPE_QPRINTABLE', 1);
	define('IMAP_BS_ENCODETYPE_XUUE', 2);
	define('IMAP_BS_ENCODETYPE_NONE', 5);

	define('MESSAGE_VIEW_TYPE_PRINT', '0');
	define('MESSAGE_VIEW_TYPE_FULL', '1');
	define('MESSAGE_VIEW_TYPE_ATTACH', '2');

	define('SYNC_TYPE_FUNAMBOL', 1);

	define('USE_FOLDER_SYNC_ON_BASE', true);

	define('GLOBAL_ADDRESS_BOOK_SYSTEM', 'systemwide');
	define('GLOBAL_ADDRESS_BOOK_DOMAIN', 'domainwide');
	define('GLOBAL_ADDRESS_BOOK_OFF', 'off');

	/* ---------- */
	
	$defaultTimeZone = function_exists('date_default_timezone_get')
		? @date_default_timezone_get() : 'US/Pacific';
	
	define('SERVER_TIME_ZONE', ($defaultTimeZone && strlen($defaultTimeZone) > 0)
		? $defaultTimeZone : 'US/Pacific');
	
	include WM_ROOTPATH.'common/inc_arrays.php';
	include WM_ROOTPATH.'common/inc_functions.php';
