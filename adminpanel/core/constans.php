<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	define('AP_FOLDER', '.');
	
	define('AP_CRLF', "\r\n");
	define('AP_HTML_BR', '<br />');
	define('AP_TAB', "\t");
	define('AP_TEST_P', 'licensekey');
	
	define('AP_INDEX_FILE_TEMP', 'index.php');
	
	define('AP_CFG_FILE', '_config_path.php');
	define('AP_XML_CFG_FILE', 'adminpanel.xml');
	
	define('AP_LOG_FILE', '_admin_panel.log');
	define('AP_INSTALL_LOG_FILE', 'install.log');


	define('AP_DEMO_LOGIN', '');
	
	define('AP_USE_DB', true);
	define('AP_USE_XML_CACHE', false);

	define('AP_XML_CFG_SESS', 'apxmlsesscfg');
	define('AP_XML_CFG_SESS_TIME', 'apxmlsesscfgtime');
	
	define('AP_USE_GZIP', false);
	define('AP_IS_SUPPORT_GZIP', (bool) ((strpos(isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '', 'gzip') !== false) && function_exists('gzencode')));
	
	define('AP_MEMORYLIMIT', '50M');
	define('AP_TIMELIMIT', '60');
	
	define('AP_TYPE_WEBMAIL', 'wm');
	define('AP_TYPE_XMAIL', 'xm');
	define('AP_TYPE_XMAIL_CUSTOM', 'xmc');
	define('AP_TYPE_BUNDLE', 'bndl');
	
	define('AP_P', 2);
	define('AP_L', 3);

	define('AP_DB_MSSQLSERVER', 1);
	define('AP_DB_MYSQL', 3);
	
	define('AP_DB_QUOTE_ESCAPE', 1);
	define('AP_DB_QUOTE_DOUBLE', 2);

	define('AP_TYPE_DELIMITER', '_');
	define('AP_UIDS_DELIMITER', ':');
	
	define('AP_SESS_GOODORBAD', 'apsessgoodorbad');
	
	define('AP_DUMMYPASSWORD', '**********');
	
	define('AP_SESS_DOENTER', 'apsessdoenter');
	
	define('AP_SESS_TAB', 'apsesstab');
	define('AP_SESS_MODE', 'apsessmode');
	define('AP_SESS_PAGE', 'apsesspage');
	define('AP_SESS_FILTER', 'apsessfilter');
	define('AP_SESS_STANDARD_FILTER', 'apsessstandardfilter');
	
	define('AP_SESS_INSTALL', 'apsessinstall');
	
	define('AP_SESS_COLUMN', 'apsesscolumn');
	define('AP_SESS_ORDER', 'apsessorder');
	
	define('AP_SESS_AUTH', 'apsessauth');
	define('AP_SESS_AUTH_TYPE', 'apsessauthtype');
	define('AP_SESS_AUTH_DOMAINS', 'apsessauthdomains');

	define('AP_SESS_AUTH_TYPE_NONE', -1);
	define('AP_SESS_AUTH_TYPE_SUPER_ADMIN', 0);
	define('AP_SESS_AUTH_TYPE_SUBADMIN', 1);
	define('AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD', 2);

	define('AP_SESS_SEARCHDESC', 'apsesssearchdesc');
	
	define('AP_SESS_ENABLE', 'apsessenable');
	
	define('AP_START_TIME', 'apstarttime');
	define('AP_DB_COUNT', 'apdbcount');
	define('AP_WM_COUNT', 'apwmcount');
	define('AP_WM_TIME', 'apwmtime');
	define('AP_USEINFO', 'apuseinfo');
	define('AP_USELOG', 'apuselog');
	define('AP_USEZIP', 'apusezip');
	define('AP_USEOBF', 'apuseobf');
	
	define('AP_GLOBAL_USERFILTER_HREFS', 'apglobaluserfilterhrefs');
	
	define('AP_SESS_INFORMATION', 'apsessinfo');
	define('AP_SESS_INFORMATION_TYPE', 'apsessinfotype');
	
	define('AP_SCR_SWITCHER_TYPE_SELECT', 0);
	define('AP_SCR_SWITCHER_TYPE_TABS', 1);
	
	define('AP_META_CONTENT_TYPE', '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />');
	
	/* langs */
	
	define('AP_LANG_SAVING', 'Saving ...');
	
	define('AP_LANG_NODOMAINS', 'No domains');
	define('AP_LANG_NOUSERSINDOMAIN', 'No users in this domain');
	define('AP_LANG_RESULTEMPTY', 'The result is empty');
	define('AP_LANG_TOTAL_USERS', 'Total users');
	define('AP_LANG_NO_CREATED_DOMAIN', 'Users cannot be created until you create at least one domain.');
	
	define('AP_LANG_CANTGETSCREENNAME', 'Can\'t get screen name from plugin');
	
	define('AP_LANG_LOGIN_AUTH_ERROR', 'Wrong login and/or password. Authentication failed.');
	define('AP_LANG_LOGIN_SESS_ERROR', 'Previous session was terminated due to a timeout.');
	define('AP_LANG_LOGIN_ACCESS_ERROR', 'An attempt of unauthorized access.');
	
	define('AP_LANG_NAME', 'Admin Panel');
	define('AP_LANG_NOT_CONFIGURED', AP_LANG_NAME.' is not configured properly.');
	define('AP_LANG_NOT_ENOUGH_DATA', 'Some settings are missing in '.AP_LANG_NAME.' config file.');
	define('AP_LANG_NOT_ENOUGH_DATA_FOR_LOGIN', 'Some settings required for logging in are missing in '.AP_LANG_NAME.' config file.');
	define('AP_LANG_NOT_ENOUGH_DATA_PLUGINS', 'No plugins installed for '.AP_LANG_NAME);
	define('AP_LANG_NOT_ENOUGH_DATA_FILENOTEXIST', 'Path to '.AP_LANG_NAME.' data folder is incorrect as file "'.AP_XML_CFG_FILE.'" is not found.');
	define('AP_LANG_NOT_ENOUGH_DATA_PATH', AP_LANG_NAME.' data folder path should be specified in "'.AP_CFG_FILE.'" config file.');
	
	define('AP_LANG_FILE_ERROR_OCCURRED', 'Failed to access "'.AP_XML_CFG_FILE.'" config file.');
	define('AP_LANG_SESSION_ERROR', 'An error with creating/validating session.');

	define('AP_LANG_ADMIN_ONLY_READ', 'This is just a AdminPanel demo, saving changes is disabled.');

	$defaultTimeZone = function_exists('date_default_timezone_get')
		? @date_default_timezone_get() : 'US/Pacific';

	define('AP_SERVER_TIME_ZONE', ($defaultTimeZone && strlen($defaultTimeZone) > 0)
		? $defaultTimeZone : 'US/Pacific');

	/* timezone fix code */
	if (defined('AP_SERVER_TIME_ZONE') && function_exists('date_default_timezone_set'))
	{
		@date_default_timezone_set(AP_SERVER_TIME_ZONE);
	}
