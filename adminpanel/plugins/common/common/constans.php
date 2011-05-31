<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	define('CM_MODE_LICENSE', 'license');
	define('CM_MODE_ENABLE', 'enable');
	define('CM_MODE_AUTH', 'auth');

	define('CM_SESS_SUBADMIN', 'sesssubadminobj');
	
	/* langs */
	define('CM_TABNAME_LICENSE', 'License Key Settings');
	define('CM_TABNAME_ENABLE', 'Additional Options');
	define('CM_TABNAME_AUTH', 'Authentication Settings');
	
	define('CM_INFO_SAVESUCCESSFUL', 'Saved successfully.');
	define('CM_INFO_SAVEUNSUCCESSFUL', 'Failed to save.');
	
	define('CM_INFO_ERROR', 'Error');
	define('CM_INFO_LOGCLEARSUCCESSFUL', 'Log cleared successfully.');
	
	define('CM_NOT_ENOUGH_DATA_FILENOTEXIST', 'Path to '.AP_LANG_NAME.' data folder is incorrect as file "'.AP_XML_CFG_FILE.'" is not found.');
	define('CM_NOT_ENOUGH_DATA_PATH', AP_LANG_NAME.' data folder path should be specified in "'.AP_CFG_FILE.'" config file.');
	define('CM_NOT_ENOUGH_DATA_FOR_LOGIN', 'Some settings required for logging in are missing in '.AP_LANG_NAME.' config file.');
	define('CM_FILE_ERROR_OCCURRED', 'Failed to access "'.AP_XML_CFG_FILE.'" config file.');
	
	define('CM_ENTER_VALID_KEY_HERE', 'Please specify trial or permanent license key here.');
	define('CM_ENTER_VALID_KEY', 'Please specify valid license key.');
	define('CM_FAILED_SAVE_SETTINGS', 'Failed to save settings.');
	define('CM_PASSWORDS_NOT_MATCH', 'The password and its confirmation don\'t match.');

	define('CM_LANG_NOADMINS', 'No admins');

	define('CM_INFO_CONNECTSUCCESSFUL', 'Connected successfully.');
	define('CM_INFO_CONNECTUNSUCCESSFUL', 'Failed to connect.');
	define('CM_DELETE_SUCCESSFUL', 'Deleted successfully.');
	define('CM_DELETE_UNSUCCESSFUL', 'Failed to delete.');
	define('CM_SUBADMIN_EXIST', 'Such admin already exists.');

	define('CM_INFO_CANTLOAD_SETTINGS', 'Can\'t load settings.xml');
	define('CM_NOT_DOMAIN_SELECT_NAME', '[Users not in domain]');

	define('CM_REQ_FIELDS_CANNOT_BE_EMPTY', 'Required fields cannot be empty.');
	define('CM_DEMO_LKEY', 'WM500-DEMO-DEMO-DEMO-DEMO-DEMO-DEMO-123457A');
	