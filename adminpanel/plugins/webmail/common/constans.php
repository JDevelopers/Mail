<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	define('WM_MODE_DB', 'db');
	define('WM_MODE_WEBMAIL', 'webmail');
	define('WM_MODE_INTERFACE', 'interface');
	define('WM_MODE_LOGIN', 'login');
	define('WM_MODE_CONTACTS', 'contacts');
	define('WM_MODE_CAL', 'cal');
	define('WM_MODE_DEBUG', 'debug');
	define('WM_MODE_MOBILE_SYNC', 'mobile');
	define('WM_MODE_INTEGRATION', 'integr');

	define('WM_GLOBAL_ADDRESS_BOOK_SYSTEM', 'systemwide');
	define('WM_GLOBAL_ADDRESS_BOOK_DOMAIN', 'domainwide');
	define('WM_GLOBAL_ADDRESS_BOOK_OFF', 'off');
	
	define('WM_INST_MODE_CHECK', 'check');
	define('WM_INST_MODE_LICENSETEXT', 'license');
	define('WM_INST_MODE_LICENSE', 'licensekey');
	define('WM_INST_MODE_DB', 'db');
	define('WM_INST_MODE_MOBILESYNC', 'mobilesync');
	define('WM_INST_MODE_DATAPATH', 'data');
	define('WM_INST_MODE_COMMON', 'common');
	define('WM_INST_MODE_SOCKET', 'socket');
	define('WM_INST_MODE_END', 'end');

	define('WM_INST_CONNECTION_TEST_SSL', false);
	
	define('WM_INST_SESS_MSG_TYPE', 'wminstsessionmsgtype');
		define('WM_INST_MSG_TYPE_BAD', 'wminstmsgtypebag');
		define('WM_INST_MSG_TYPE_GOOD', 'wminstmsgtypegood');
	define('WM_INST_SESS_MSG_TEXT', 'wminstsessionmsgtext');
	define('WM_INST_SESS_MSG_TEST_TEXT', 'wminstsessionmsgtesttext');
	define('WM_INST_SESS_MSG_CREATE_DB_TEXT', 'wminstsessionmsgcreatedbtext');
	
	define('WM_INST_SESS_SETTINGS', 'wminstsessionsettings');

	define('WM_INST_SESS_CH_HOST', 'wminstsessionchhost');
	define('WM_INST_SESS_CH_PROTOCOLS', 'wminstsessioncprotocols');
	define('WM_INST_SESS_CH_MSG', 'wminstsessioncmsg');
	
	define('WM_INST_IS_F_K', 'wminstisfk');
	define('WM_INST_IS_ADM_PSW', 'wminstisfadmpsw');
	
	define('WM_SESS_ACCOUNT', 'wmsessionaccount');
	define('WM_SESS_DOMAIN', 'wmsessiondomain');
	define('WM_SESS_USERMINI', 'wmsessusermini');
	
	define('WM_DEFAULT_SKIN', 'AfterLogic');
	
	define('WM_MAILPROTOCOL_POP3', 0);
	define('WM_MAILPROTOCOL_IMAP4', 1);
	define('WM_MAILPROTOCOL_WMSERVER', 2);
	define('WM_MAILPROTOCOL_ALL', 9);

	define('WM_LOG_LEVEL_DEBUG', 100);
	define('WM_LOG_LEVEL_WARNING', 50);
	define('WM_LOG_LEVEL_ERROR', 20);
	
	define('WM_MAILMODE_DeleteMessagesFromServer', 0);
	define('WM_MAILMODE_LeaveMessagesOnServer', 1);
	define('WM_MAILMODE_KeepMessagesOnServer', 2);
	define('WM_MAILMODE_DeleteMessageWhenItsRemovedFromTrash', 3);
	define('WM_MAILMODE_KeepMessagesOnServerAndDeleteMessageWhenItsRemovedFromTrash', 4);

	define('WM_VIEW_MODE_WITHOUT_PREVIEW_PANE_NO_IMG', 0);
	define('WM_VIEW_MODE_PREVIEW_PANE_NO_IMG', 1);
	define('WM_VIEW_MODE_WITHOUT_PREVIEW_PANE', 2);
	define('WM_VIEW_MODE_PREVIEW_PANE', 3);

	define('WM_NEW_VIEW_MODE_CENTRAL_LIST_PANE', 1);
	define('WM_NEW_VIEW_MODE_SHOW_PICTURES', 2);

	define('WM_FOLDERTYPE_Inbox', 1);
	define('WM_FOLDERTYPE_SentItems', 2);
	define('WM_FOLDERTYPE_Drafts', 3);
	define('WM_FOLDERTYPE_Trash', 4);
	define('WM_FOLDERTYPE_Spam', 5);
	define('WM_FOLDERTYPE_Virus', 6);
	define('WM_FOLDERTYPE_Custom', 10);

	define('WM_FOLDERSYNC_DontSync', 0);
	define('WM_FOLDERSYNC_NewHeadersOnly', 1);
	define('WM_FOLDERSYNC_AllHeadersOnly', 2);
	define('WM_FOLDERSYNC_NewEntireMessages', 3);
	define('WM_FOLDERSYNC_AllEntireMessages', 4);
	define('WM_FOLDERSYNC_DirectMode', 5);
	
	define('WM_FOLDERNAME_Inbox', 'Inbox');
	define('WM_FOLDERNAME_SentItems', 'Sent Items');
	define('WM_FOLDERNAME_Sent', 'Sent');
	define('WM_FOLDERNAME_Drafts', 'Drafts');
	define('WM_FOLDERNAME_Trash', 'Trash');
	define('WM_FOLDERNAME_Spam', 'Spam');
	define('WM_FOLDERNAME_Virus', 'Quarantine');
	
	define('WM_DATEFORMAT_DEFAULT', 0);
	define('WM_DATEFORMAT_DDMMYY', 1);
	define('WM_DATEFORMAT_MMDDYY', 2);
	define('WM_DATEFORMAT_DDMonth', 3);
	define('WM_DATEFORMAT_Advanced', 4);
	
	define('WM_DATEFORMAT_FLAG', '|#');

	define('WM_SAVE_IN_SENT_ALWAYS', 0);
	define('WM_SAVE_IN_SENT_DEFAULT_ON', 1);
	define('WM_SAVE_IN_SENT_DEFAULT_OFF', 2);

	/* langs */
	define('WM_INFO_SAVESUCCESSFUL', 'Saved successfully.');
	define('WM_INFO_SAVEUNSUCCESSFUL', 'Failed to save.');
	define('WM_INFO_CONNECTSUCCESSFUL', 'Connected successfully.');
	define('WM_INFO_CONNECTUNSUCCESSFUL', 'Failed to connect.');
	define('WM_INFO_ERROR', 'Error');
	define('WM_INFO_LOGCLEARSUCCESSFUL', 'Log cleared successfully.');
	
	define('WM_INFO_DBCREATESUCCESSFUL', 'Database created successfully.');
	define('WM_INFO_DBCREATEUNSUCCESSFUL', 'Failed to created database.');
	
	define('WM_INFO_DBTESTDATABESERROR', 'Test connection to provided database failed');
	define('WM_INFO_DBTESTDATABESSUCCESS', 'Test connection performed successfully');
	define('WM_INFO_DBTESTDATABESCREATETESTERROR', 'Make sure you have enough privileges to modify the database.');
	define('WM_INFO_DBTESTDATABESSELECTERRORADD', 'Make sure you have created a database.');
		
	define('WM_INFO_DBDROPSUCCESSFUL', 'Database droped successfully.');
	define('WM_INFO_DBDROPUNSUCCESSFUL', 'Failed to droped database.');
	
	define('WM_INFO_CAL_CHECKWORKDAYTIMEERROR', 'The "Workday ends" time must be greater than the "Workday starts" time.');
	
	define('WM_INFO_ROOTPATHINCORRECT', 'Path to AfterLogic XMail Server mailroot folder is incorrect.');
	define('WM_INFO_CANTFIND', 'Can\'t find ');
	define('WM_INFO_CANTLOAD_SETTINGS', 'Can\'t load settings.xml');
	
	define('WM_TABNAME_DB', 'Database Settings');
	define('WM_TABNAME_WEBMAIL', 'Common Settings');
	define('WM_TABNAME_INTERFACE', 'Interface Settings');
	define('WM_TABNAME_LOGIN', 'Login Page Settings');
	define('WM_TABNAME_CAL', 'Calendar Settings');
	define('WM_TABNAME_DEBUG', 'Debug & Logging');
	define('WM_TABNAME_MOBILE_SYNC', 'Mobile Sync');
	define('WM_TABNAME_INTEGRATION', 'Server Integration');
	define('WM_TABNAME_CONTACTS', 'Address Book Settings');
	
	define('WM_FILTER_ALL', '[Users not in domain]');
	
	define('WM_DOMAIN_EXIST', 'Such domain already exists.');
	define('WM_DOMAIN_CREATESUCCESSFUL', 'Domain saved successfully.');
	define('WM_DOMAIN_CREATEUNSUCCESSFUL', 'Failed to save domain.');

	define('WM_DOMAIN_SAVE_ERROR_OCCURED', 'An error has occured while saving domain settings.');
	
	define('WM_ACCOUNT_CREATESUCCESSFUL', 'Account saved successfully.');
	define('WM_ACCOUNT_COUDNT_CREATE_LIMIT', 'User couldn\'t be created because max number of users allowed by your license exceeded.');
	define('WM_ACCOUNT_CREATEUNSUCCESSFUL', 'Failed to save account.');
	
	define('WM_CANT_CREATE_ACCOUNT', 'Can\'t create account.');
	define('WM_CANT_ADD_DEF_ACCT', 'This account cannot be added because it\'s used as a default account by another user.');
	
	define('WM_ERROR_POP3_CONNECT', 'Can\'t connect to POP3 server, check POP3 server settings.');
	define('WM_ERROR_IMAP4_CONNECT', 'Can\'t connect to IMAP4 server, check IMAP4 server settings.');
	define('WM_ERROR_XMAIL_CONNECT', 'Can\'t connect to XMAIL server, check XMAIL server settings.');
	define('WM_ERROR_POP3IMAP4AUTH', 'Wrong email/login and/or password. Authentication failed.');
	
	define('WM_CANTGET_ACCOUNTS_LIST', 'Can\'t get accounts list.');
	define('WM_ERROR_NOT_ENOUGH_WEB_PATH', 'Path to WebMail web folder should be specified in "'.AP_CFG_FILE.'" config file.');
	define('WM_ERROR_PATH_FILE_NOT_FOUND', 'Path to WebMail web folder is incorrect as "inc_settings_path.php" file is not found.');
	define('WM_DELETE_SUCCESSFUL', 'Deleted successfully.');
	define('WM_DELETE_UNSUCCESSFUL', 'Failed to delete.');
	define('WM_ENABLE_SUCCESSFUL', 'Enabled successfully.');
	define('WM_ENABLE_UNSUCCESSFUL', 'Failed to enable.');
	define('WM_DISABLE_SUCCESSFUL', 'Disabled successfully.');
	define('WM_DISABLE_UNSUCCESSFUL', 'Failed to disable.');
	
	define('WM_ERROR_USERNAMENULL', 'Username is not specified.');
	define('WM_ERROR_USERNAMEPASSWORDNULL', 'Username or password is not specified.');
	
	define('WM_WEBMAILCONFIGTAB', 'wm.tab');
	
	define('WM_INST_TABNAME_CHECK', 'Compatibility Test');
	define('WM_INST_TABNAME_LICENSETEXT', 'License Agreement');
	define('WM_INST_TABNAME_LICENSE', 'License Key');
	define('WM_INST_TABNAME_DB', 'Database Settings');
	define('WM_INST_TABNAME_MOBILESYNC', 'Mobile Sync');
	define('WM_INST_TABNAME_DATAPATH', 'Data Path');
	define('WM_INST_TABNAME_COMMON', 'Admin Panel Settings');
	define('WM_INST_TABNAME_SOCKET', 'E-mail Server Test');
	define('WM_INST_TABNAME_END', 'Completed');
	
	define('WM_INST_ENTER_VALID_KEY_HERE', 'Please specify trial or permanent license key here.');
	define('WM_INST_ENTER_VALID_KEY', 'Please specify valid license key.');
	
	define('WM_INST_PASSWORDS_NOT_MATCH', 'The password and its confirmation don\'t match.');
	
	define('WM_INST_TITLE', 'AfterLogic WebMail Pro Installation');
	