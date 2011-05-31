<?php

/*
 * 
 * If you're reading this text on attempt to install the application,
 * your server doesn't have PHP engine configured properly.
 * 
 */

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	include_once 'core/session.php';
	include_once 'core/constans.php';
	include_once 'cadminpanel.php';
	
	if ((!isset($_GET) || count($_GET) == 0 || (count($_GET) == 1 && isset($_GET['key']))))
	{
		/* step 1 */
		
		@session_destroy();

		@session_name('PHPWMADMINSESSID');
		@session_start();
		$_SESSION['checksessionindex'] = true;
		$_SESSION['licensekeysession'] = isset($_GET['key']) ? $_GET['key'] : null;
		@header('Location: install.php?check');
		exit();

		/* -- step 1 */
	}
	else if (isset($_GET['enter'], $_SESSION[AP_SESS_DOENTER]))
	{
		/* step 4 */

		unset($_SESSION[AP_SESS_DOENTER]);
		@header('Location: index.php?enter');
		exit();
		
		/* -- 4 */
	}
	else if (isset($_GET['check']))
	{
		/* step 2 */
		
		include 'core/addutils.php';
		
		$max = 8;
		if (!CAdminPanel::PType()) { $max -= 2; }
		
		function GoodOrBadAction($_goodOrBad)
		{
			echo ($_goodOrBad) ? 'install.php?mode=license' : 'install.php';
		}
			
		function GoodOrBadButton($_goodOrBad)
		{
			echo ($_goodOrBad) 
				? '<input type="submit" name="submit_btn" value="Next" class="wm_install_button" style="width: 100px">'
				: '<input type="submit" name="retry_btn" value="Retry" class="wm_install_button" style="width: 100px">';
		}
		
		/**
		 * @param int $_value
		 * @param string $_errorMsg = ''
		 * @return string
		 */
		function GetGreenOkOrNotDetected($_value, $_errorMsg = '')
		{
			$_img = '<img src="./images/alarm.png" /> ';
			return ($_value === 1) ? '<font color="green">OK</font>' 
				: (strlen($_errorMsg) > 0 ? $_img.'Not detected. '.$_errorMsg : $_img.'Not detected');
		}
		
		/**
		 * @param int $_value
		 * @param string $_errorMsg = ''
		 * @return string
		 */
		function GetGreenOkOrError($_value, $_errorMsg = '', $_infoMsg = '')
		{
			return ($_value === 1) ? '<font color="green">OK</font>' 
				: '<font color="red">'.(strlen($_errorMsg) > 0 ? 'Error, '.$_errorMsg : 'Error').'</font>'.$_infoMsg;
		}
		
		/**
		 * @param int $_value
		 * @param string $_errorMsg = ''
		 * @return string
		 */
		function GetGreenFoundOrNot($_value, $_errorMsg = '', $_infoMsg = '')
		{
			return ($_value === 1) ? '<font color="green">Found</font>' 
				: '<font color="red">'.(strlen($_errorMsg) > 0 ? 'Not Found, '.$_errorMsg : 'Not Found').'</font>'.$_infoMsg;
		}
	
		/**
		 * @return string
		 */
		function RootPath()
		{
			if (!defined('INST_ROOTPATH'))
			{
				define('INST_ROOTPATH', rtrim(dirname(__FILE__), '/\\'));
			}
			return INST_ROOTPATH;
		}

		/**
		 * @var int $inc
		 * @var string $_color
		 * @return string
		 */
		function GetColorString($inc, $_color)
		{
			return (ceil($inc / 2) == $inc / 2) ? ' bgcolor="'.$_color.'"' : '';
		}
		
		/**
		 * @return array|false
		 */
		function InitPath(&$_initPathError)
		{
			$_cfg = array();
			$_result = true;
			if (@file_exists(RootPath().'/'.AP_CFG_FILE))
			{
				$settings_path = null;
				if ((@include RootPath().'/'.AP_CFG_FILE) && isset($settings_path))
				{
					if (is_array($settings_path) && count($settings_path) > 0)
					{
						foreach ($settings_path as $_name => $_path)
						{
							if (is_string($_path))
							{
								$_cfg[$_name.'_path'] = ap_AddUtils::GetFullPath($_path, RootPath());
							}
						}
						
						$dataPath = null;
						if (@isset($_cfg['webmail_web_path']))
						{
							if (@file_exists($_cfg['webmail_web_path'].'/inc_settings_path.php'))
							{
								if ((@include $_cfg['webmail_web_path'].'/inc_settings_path.php') && isset($dataPath) && $dataPath !== null)
								{
									$_cfg['webmail_data_path'] = ap_AddUtils::GetFullPath($dataPath, $_cfg['webmail_web_path']);
								}
								else
								{
									$_initPathError = 'Installer can\'t include '.$_cfg['webmail_web_path'].'/inc_settings_path.php file (probably, because PHP process does not have permissions to access inc_settings_path.php file or the file\'s content is incorrect).';
								}
							}
							else
							{
								$_initPathError = $_cfg['webmail_web_path'].'/inc_settings_path.php file doesn\'t exist (or PHP process does not have permissions to check this file for existance).';
							}
						}
						else
						{
							$_initPathError = RootPath().'/'.AP_CFG_FILE.' file\'s content is incorrect.';
						}
						
						unset($dataPath);
					}
				}
				else
				{
					$_initPathError = 'Installer can\'t include '.RootPath().'/'.AP_CFG_FILE.' file (probably, because PHP process does not have permissions to access '.AP_CFG_FILE.' file or the file\'s content is incorrect).';
				}

				unset($settings_path);
			}
			else
			{
				$_initPathError = RootPath().'/'.AP_CFG_FILE.' file doesn\'t exist (or PHP process does not have permissions to check this file for existance).';
				$_result = false;
			}
			
			return ($_result) ? $_cfg : false;
		}
		
		$appName = 'Webmail';
		$appName .= ' Lite';
		$faqLink = 'faq-webmail-lite-php';
		
		
		$_color = '#f6f6f6';
		$redColor = '#ff0000';
		
		define('B_T_L', ' style="border-top: 1px solid '.$redColor.'; border-left: 1px solid '.$redColor.';" ');
		define('B_T_R', ' style="border-top: 1px solid '.$redColor.'; border-right: 1px solid '.$redColor.';" ');
		define('B_M_L', ' style="border-left: 1px solid '.$redColor.';" ');
		define('B_M_R', ' style="border-right: 1px solid '.$redColor.';" ');
		
		define('B_ALL_L', ' style="border-bottom: 1px solid '.$redColor.'; border-top: 1px solid '.$redColor.'; border-left: 1px solid '.$redColor.';" ');
		define('B_ALL_R', ' style="border-bottom: 1px solid '.$redColor.'; border-top: 1px solid '.$redColor.'; border-right: 1px solid '.$redColor.';" ');

		define('LKEY', isset($_SESSION['licensekeysession']) ? $_SESSION['licensekeysession'] : '');
		define('RND', (int) rand(10000, 99999));
		
		/* -------------- */ 
	
		$inc = 1;
		$_allGood = $_NotSomeErrors = true; 
		
		$_phpver = phpversion();
		$_phpverIsValid = (int) (version_compare($_phpver, '5.1.0') > -1);
		
		$_allGood &= $_phpverIsValid;
		
		$_phpSafeMode = @ini_get('safe_mode');
		$_phpSafeMode = is_numeric($_phpSafeMode)
			? !((bool) $_phpSafeMode) : ('off' === strtolower($_phpSafeMode) || empty($_phpSafeMode));

		$_allGood &= $_phpSafeMode;
		$_phpSafeMode = (int) $_phpSafeMode;
		
		$_isMySQL = (int) extension_loaded('mysql');
		$_isMSSQL = (int) extension_loaded('mssql');
		$_isODBC = (int) extension_loaded('odbc'); 
		
		$_allDb = ($_isMySQL + $_isMSSQL + $_isODBC > 0);
		
		$_allGood &= $_allDb;
		
		$dmTr = $dmTopL = $dmTopR = $dmLeft = $dmRight = '';
		$dmLeftRight = '';
		if (!$_allDb)
		{
			$dmTopL = B_T_L;
			$dmTopR = B_T_R;
			$dmLeft = B_M_L;
			$dmRight = B_M_R;
			$dmTr = '
<tr'.GetColorString(++$inc, $_color).'>
	<td colspan="2" class="wm_install_error_td">
		<font color="red">Error, PHP database extensions not detected.</font>
		<br /><br />
		You need to install PHP extension for the appropriate database engine (MySQL or MS SQL). 
		Alternatively, you can install ODBC extension which would allow using both MySQL and MS SQL through ODBC,
		but it\'s recommended to use native driver for performance sake. 
		For installation instructions, please refer to the official PHP documentation:
		<a href="http://php.net/manual/en/mysql.installation.php" target="_blank">My SQL</a>,
		<a href="http://php.net/manual/en/mssql.installation.php" target="_blank">MS SQL</a> or
		<a href="http://php.net/manual/en/odbc.installation.php" target="_blank">ODBC</a>.
	</td>
</tr>
';
		}
		else
		{
			$dmTr = '
<tr'.GetColorString(++$inc, $_color).'>
	<td colspan="2" class="wm_install_note_td">
		Note: even if PHP database extensions are enabled, this does not
guarantee the corresponding database server is available. On "Database
Settings" stage, you\'ll be prompted to check database connectivity.
	</td>
</tr>
';
		}
			
		$_isSocket = (int) function_exists('fsockopen');
		$_allGood &= ($_isSocket === 1);
		
		$_isSsl = (int) extension_loaded('openssl');
		$_NotSomeErrors &= ($_isSsl == 1);
	
		$_isIniGet = (int) function_exists('ini_get');
		$_isIniSet = (int) function_exists('ini_set');
		$_isMem = (int) ($_isIniGet + $_isIniSet == 2);
		$_isSetTimeLimit = (int) function_exists('set_time_limit');
		
		if ($_isSetTimeLimit + $_isIniGet == 2)
		{
			@set_time_limit(100);
			if (100 !== (int) ini_get('max_execution_time') && strlen(ini_get('max_execution_time')) > 0)
			{
				$_isSetTimeLimit = 0;
			}
		}
		
		if ($_isMem == 1)
		{
			@ini_set('memory_limit', '50M');
			if ('50M' != ini_get('memory_limit') && strlen(ini_get('memory_limit')) > 0)
			{
				$_isMem = 0;
			}
		}
		
		$_NotSomeErrors &= ($_isSetTimeLimit == 1);
		$_NotSomeErrors &= ($_isMem == 1);
		
		$_dataFolder = '';

		$_initPathError = '';
		$_cfg = InitPath($_initPathError);
		
		if ($_cfg && isset($_cfg['webmail_data_path']))
		{
			$_dataFolder = $_cfg['webmail_data_path'];
		}
		
		$_isSessionGood = (int) (function_exists('session_start') && isset($_SESSION['checksessionindex']));
		$_allGood &= ($_isSessionGood === 1);
		
		$_dataFolderString = $_dataFolder;
		
		$_isDirExist = (int) (false !== $_cfg && @file_exists($_dataFolder));
		
		$_globalDataFolder = '';
		
		if ($_isDirExist == 1)
		{
			$_globalDataFolder = '
	<tr'.GetColorString(2, $_color).'>
		<td valign="top">
<b>WebMail&nbsp;data&nbsp;folder:</b>
		</td>
		<td>
'.GetGreenFoundOrNot(1).'
		</td>
	</tr>
';
			$_tempPathName = '_test_'.md5(time().__FILE__);
		
			$_isCreateDir = (int) @mkdir($_dataFolder.'/'.$_tempPathName);
			
			$_isCreateFile = (int) (bool) @fopen($_dataFolder.'/'.$_tempPathName.'/'.$_tempPathName.'.txt', 'w+');
			$_isDeleteFile = (int) @unlink($_dataFolder.'/'.$_tempPathName.'/'.$_tempPathName.'.txt');
			
			$_isDeleteDir = (int) @rmdir($_dataFolder.'/'.$_tempPathName);
			
			if ($_isCreateDir + $_isCreateFile + $_isDeleteFile + $_isDeleteDir !== 4)
			{
				$_allGood = false;
			}
			
			$_globalDataFolder .= '
	<tr>
		<td valign="top">
&nbsp;&nbsp;&nbsp;&nbsp;Creating/deleting&nbsp;folders
		</td>
		<td>
'.GetGreenOkOrError(($_isCreateDir + $_isDeleteDir == 2) ? 1 : 0, 'can\'t create/delete sub-folders in the data folder.',
'<br /><br />
You need to grant read/write permission over '.$appName.' data folder and all its contents to your web server user.
For instructions, please refer to this section of '.$appName.' documentation and our 
<a href="http://www.afterlogic.com/support/'.$faqLink.'#3.1" target="_blank">FAQ</a>.'
			).'	
		</td>
	</tr>
	<tr>
		<td valign="top">
&nbsp;&nbsp;&nbsp;&nbsp;Creating/deleting&nbsp;files 
		</td>
		<td>
'.GetGreenOkOrError(($_isCreateFile + $_isDeleteFile == 2) ? 1 : 0, 'can\'t create/delete files in the data folder.',
'<br /><br />
You need to grant read/write permission over '.$appName.' data folder and all its contents to your web server user.
For instructions, please refer to this section of '.$appName.' documentation and our 
<a href="http://www.afterlogic.com/support/'.$faqLink.'#3.1" target="_blank">FAQ</a>.'			
			).'	
		</td>
	</tr>
';
			/* --- */
					
			$_adminpanelXml = $_dataFolder.'/settings/'.AP_XML_CFG_FILE;
			$_isAdminSettingsReadable = $_isAdminSettingsWriteble = 0;
			$_isAdminSettingsExist = (int) @file_exists($_adminpanelXml);
			if ($_isAdminSettingsExist === 1)
			{
				$_isAdminSettingsReadable = (int) @is_readable($_adminpanelXml);
				$_isAdminSettingsWriteble = (int) @is_writable($_adminpanelXml);
				
				$_isAdminSettingsWRText = ($_isAdminSettingsReadable + $_isAdminSettingsWriteble == 2) ? '' :
'<br /><br />
You should grant read/write permission over '.$appName.' admin panel settings file to your web server user.
For instructions, please refer to this section of '.$appName.' documentation and our
<a href="http://www.afterlogic.com/support/'.$faqLink.'#3.1" target="_blank">FAQ</a>.';
				
				$_globalDataFolder .= '
	<tr'.GetColorString(2, $_color).'>
		<td valign="top">
			<b>Admin&nbsp;Panel&nbsp;Settings&nbsp;File:</b>
		</td>
		<td>
'.GetGreenFoundOrNot(1).'
		</td>
	</tr>
	<tr>
		<td valign="top">
&nbsp;&nbsp;&nbsp;&nbsp;Read/write&nbsp;settings&nbsp;file
		</td>
		<td>'.
GetGreenOkOrError($_isAdminSettingsReadable, 'can\'t read <nobr>"'.$_adminpanelXml.'"</nobr> file.'
)
.' / '.
GetGreenOkOrError($_isAdminSettingsWriteble, 'can\'t write <nobr>"'.$_adminpanelXml.'"</nobr> file.')
.$_isAdminSettingsWRText.'
		</td>
	</tr>
';
			}
			else 
			{
				$_globalDataFolder .= '
	<tr'.GetColorString(2, $_color).'>
		<td'.B_ALL_L.' valign="top">
			<b>Admin&nbsp;Panel&nbsp;Settings&nbsp;File:</b>
		</td>
		<td'.B_ALL_R.'>
'.GetGreenFoundOrNot(0, 'can\'t find <nobr>"'.$_adminpanelXml.'"</nobr> file.', 
'<br /><br />
Make sure you completely copied the data folder
with all its contents from '.$appName.' installation package
into the location you specified in inc_settings_path.php file.').'
		</td>
	</tr>
';
			}
			
			if ($_isAdminSettingsReadable + $_isAdminSettingsWriteble !== 2)
			{
				$_allGood = false;
			}
			
			/* --- */
			
			$_settingsXml = $_dataFolder.'/settings/settings.xml';
			$_isWmSettingsReadable = $_isWmSettingsWriteble = 0;
			$_isWmSettingsExist = (int) @file_exists($_settingsXml);
			if ($_isWmSettingsExist === 1)
			{
				$_isWmSettingsReadable = (int) @is_readable($_settingsXml);
				$_isWmSettingsWriteble = (int) @is_writable($_settingsXml);
				
				$_isWmSettingsWRText = ($_isWmSettingsReadable + $_isWmSettingsWriteble == 2) ? '' :
'<br /><br />
You should grant read/write permission over '.$appName.' settings file to your web server user.
For instructions, please refer to this section of '.$appName.' documentation and our
<a href="http://www.afterlogic.com/support/'.$faqLink.'#3.1" target="_blank">FAQ</a>.';
				
				$_globalDataFolder .= '
	<tr'.GetColorString(2, $_color).'>
		<td valign="top">
			<b>WebMail&nbsp;Settings&nbsp;File:</b>
		</td>
		<td>
'.GetGreenFoundOrNot(1).'
		</td>
	</tr>
	<tr'.GetColorString(++$inc, $_color).'>
		<td valign="top">
&nbsp;&nbsp;&nbsp;&nbsp;Read/write&nbsp;settings&nbsp;file
		</td>
		<td>'.
GetGreenOkOrError($_isWmSettingsReadable, 'can\'t read <nobr>"'.$_settingsXml.'"</nobr> file.')
.' / '.
GetGreenOkOrError($_isWmSettingsWriteble, 'can\'t write <nobr>"'.$_settingsXml.'"</nobr> file.')
.$_isWmSettingsWRText.'
		</td>
	</tr>
';
			}
			else 
			{
				$_globalDataFolder .= '
	<tr'.GetColorString(2, $_color).'>
		<td'.B_ALL_L.' valign="top">
			<b>WebMail&nbsp;Settings&nbsp;File:</b>
		</td>
		<td'.B_ALL_R.'>
'.GetGreenFoundOrNot(0, 'can\'t find <nobr>"'.$_settingsXml.'"</nobr> file.', '
<br /><br />
Make sure you completely copied the data folder with all its contents from '.$appName.' installation package
into the location you specified in inc_settings_path.php file.').'
		</td>
	</tr>
';
			}
			
			if ($_isWmSettingsReadable + $_isWmSettingsWriteble !== 2)
			{
				$_allGood = false;
			}
		}
		else 
		{
			$_allGood = false;
			$_globalDataFolder = '
	<tr'.GetColorString(++$inc, $_color).'>
		<td'.B_ALL_L.' valign="top">
<b>Data&nbsp;folder:</b>
		</td>
		<td'.B_ALL_R.'>
'.GetGreenOkOrError(0, ' data folder path discovery failure.', '<br /><br />'.$_initPathError).'
		</td>
	</tr>
';
		}
	
	
		$_SESSION[AP_SESS_GOODORBAD] = $_allGood;
		
		$_lastMessage = ($_allGood)
			? (($_NotSomeErrors) 
				? 'The current server environment meets all the requirements. Click Next to proceed.' 
				: 'The current server environment meets main requirements. Click Next to proceed.')
			: 'Please make sure that all the requirements are met and click Retry.';
			
		$_lastMessageClass = ($_allGood) ? 'wm_install_last_div_ok' : 'wm_install_last_div_error';
		
		$_text = '
<table width="540" class="wm_install_check_table">
	<tr'.GetColorString(++$inc, $_color).'>
		<td width="180" valign="top">
<b>PHP&nbsp;version:</b>
		</td>
		<td width="360">
'.GetGreenOkOrError($_phpverIsValid, $_phpver.' detected, 5.1.0 or above required.',
'<br /><br />
You need to upgrade PHP engine installed on your server. 
If it\'s a dedicated or your local server, you can download the latest version of PHP from its 
<a href="http://php.net/downloads.php" target="_blank">official site</a> and install it yourself.
In case of a shared hosting, you need to ask your hosting provider to perform the upgrade.
').'
		</td>
	</tr>
	<tr'.GetColorString(++$inc, $_color).'>
		<td width="180" valign="top">
<b>Safe Mode is off:</b>
		</td>
		<td width="360">
'.GetGreenOkOrError($_phpSafeMode, 'safe_mode is enabled.',
'<br /><br />
You need to <a href="http://php.net/manual/en/ini.sect.safe-mode.php" target="_blank">disable it in your php.ini</a> or contact your hosting provider and ask to do this.
').'
		</td>
	</tr>
	<tr'.GetColorString(++$inc, $_color).'>
		<td'.$dmTopL.' valign="top">
<b>MySQL&nbsp;PHP&nbsp;Extension:</b>
		</td>
		<td'.$dmTopR.'>
'.GetGreenOkOrNotDetected($_isMySQL,
'<br /><br />
If you\'re going to use MySQL as database backend, you need to install PHP extension for MySQL.
For installation instructions, please refer to the
<a href="http://php.net/manual/en/mysql.installation.php" target="_blank">official PHP documentation</a>.
In case of a shared hosting, you need to ask your hosting provider to install the extension.
Alternatively, you may configure connection to your MySQL through ODBC,
but it\'s recommended to use native driver for performance sake.').'
		</td>
	</tr>
	<tr'.GetColorString(++$inc, $_color).'>
		<td'.$dmLeft.' valign="top">
<b>MS&nbsp;SQL&nbsp;PHP&nbsp;Extension:</b>
		</td>
		<td'.$dmRight.'>
'.GetGreenOkOrNotDetected($_isMSSQL,
'<br /><br />
If you\'re going to use MS SQL as database backend, you need to install PHP extension for MS SQL.
For installation instructions, please refer to the
<a href="http://php.net/manual/en/mssql.installation.php" target="_blank">official PHP documentation</a>.
In case of a shared hosting, you need to ask your hosting provider to install the extension.
Alternatively, you may configure connection to your MS SQL through ODBC,
but it\'s recommended to use native driver for performance sake.').'
		</td>
	</tr>
	<tr'.GetColorString(++$inc, $_color).'>
		<td'.$dmLeft.' valign="top">
<b>ODBC/DSN&nbsp;support:</b>
		</td>
		<td'.$dmRight.'>
'.GetGreenOkOrNotDetected($_isODBC,
'<br /><br />
You may ignore it if you\'re going to configure database connection through hostname.
If you need to configure database connection through DSN (Data Source Name) or
specify your own custom connection string, you should install PHP extension for ODBC.
For installation instructions, please refer to the 
<a href="http://php.net/manual/en/odbc.installation.php" target="_blank">official PHP documentation</a>.
In case of a shared hosting, you need to ask your hosting provider to install the extension.'
		).'
		</td>
	</tr>
'.$dmTr.'
	<tr'.GetColorString(++$inc, $_color).' valign="top">
		<td>
<b>PHP&nbsp;sessions:</b>
		</td>
		<td>
'.GetGreenOkOrError($_isSessionGood, 'session support in PHP must be enabled.',
'<br /><br />
 To enable sessions, you should make sure the correct location is specified in session.save_path directive
 of your php.ini file and PHP is allowed to write into that location. You can learn more from 
 <a href="http://php.net/manual/en/session.installation.php" target="_blank">official PHP documentation</a>. 
 In case of a shared hosting, you need to ask your hosting provider to do this.'		
		).'
		</td>
	</tr>
	<tr'.GetColorString(++$inc, $_color).'>
		<td valign="top">
<b>Sockets:</b>
		</td>
		<td>
'.GetGreenOkOrError($_isSocket, 'creating network sockets must be enabled.',
'<br /><br />
To enable sockets, you should remove fsockopen function from the list of prohibited functions
in disable_functions directive of your php.ini file. In case of a shared hosting,
you need to ask your hosting provider to do this.'		
		).'
		</td>
	</tr>
	<tr'.GetColorString(++$inc, $_color).' valign="top">
		<td>
<b>SSL&nbsp;(OpenSSL&nbsp;extension):</b>
		</td>
		<td>
'.GetGreenOkOrNotDetected($_isSsl, '<br /><br />SSL connections (like Gmail) will not be available.
<br /><br />
You need to enable OpenSSL support in your PHP configuration and make sure
OpenSSL library is installed on your server. For instructions, please refer to the
<a href="http://php.net/manual/en/openssl.installation.php" target="_blank">official PHP documentation</a>.
In case of a shared hosting, you need to ask your hosting provider to enable OpenSSL support.
You may ignore this if you\'re not going to connect to SSL-only mail servers (like Gmail).
').'
		</td>
	</tr>
	<tr'.GetColorString(++$inc, $_color).'>
		<td valign="top">
<b>Setting&nbsp;memory&nbsp;limits:</b>
		</td>
		<td>
'.GetGreenOkOrNotDetected($_isMem, '
<br /><br />
Opening large e-mails may fail.
<br /><br />
You need to enable setting memory limits in your PHP configuration,
i.e. remove ini_get and ini_set functions from the list of prohibited functions
in disable_functions directive of your php.ini file. In case of a shared hosting,
you need to ask your hosting provider to do this.
').'
		</td>
	</tr>
	<tr'.GetColorString(++$inc, $_color).'>
		<td valign="top">
<b>Setting&nbsp;script&nbsp;timeout:</b>
		</td>
		<td>
'.GetGreenOkOrNotDetected($_isSetTimeLimit, '
<br /><br />
Downloading large mailboxes may fail.
<br /><br />
To enable setting script timeout, you should remove set_time_limit function from the list of prohibited functions
in disable_functions directive of your php.ini file. In case of a shared hosting,
you need to ask your hosting provider to do this.').'
		</td>
	</tr>
'.$_globalDataFolder.'
</table>
<div class="'.$_lastMessageClass.'">'.$_lastMessage.'</div>';

		
	$titleClass = 'wm_content_webmail_';
	$titleClass .= 'lite';
	$keySrc = 'wml-php-install-logo.png';
	
	
	$fullSrc = defined('CS_TYPE') ? 'images/'.$keySrc : 'http://afterlogic.com/img/'.$keySrc.'?key='.LKEY.'&step=2&rnd='.RND;
	

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo AP_META_CONTENT_TYPE; ?>
	<title>Compatibility Test — AfterLogic <?php echo $appName; ?> Installation</title>
	<link rel="stylesheet" href="./styles/styles.css?777" type="text/css" />
</head>
<body>
	<div align="center" class="<?php echo $titleClass; ?>">
		<div class="wm_logo" id="logo"></div>
		<div style="background-color:#ffffff;">
<table class="wm_settings">
	<tr>
		<td class="wm_install_nav" id="settings_nav" valign="top" align="left">
			<br />
			<div>
				<img style="width:100px; height:75px; margin: 0px 0px 0px 15px;" src="<?php echo $fullSrc; ?>" />
			</div>
			<div class="wm_selected_install_item" id="check_div">
				<nobr><b> Compatibility Test</b></nobr>
			</div>
			<div class="wm_install_item_noactiv">
				<nobr><b> License Agreement</b></nobr>
			</div>
<?php if (CAdminPanel::PType()) { ?>
			<div class="wm_install_item_noactiv">
				<nobr><b> License Key</b></nobr>
			</div>
<?php } ?>
			<div class="wm_install_item_noactiv">
				<nobr><b> Database Settings</b></nobr>
			</div>
<?php if (CAdminPanel::PType()) { ?>
			<div class="wm_install_item_noactiv">
				<nobr><b> Mobile Sync</b></nobr>
			</div>
<?php } ?>
			<div class="wm_install_item_noactiv">
				<nobr><b> Admin Panel Settings</b></nobr>
			</div>
			<div class="wm_install_item_noactiv">
				<nobr><b> E-mail Server Test</b></nobr>
			</div>
			<div class="wm_install_item_noactiv">
				<nobr><b> Completed</b></nobr>
			</div>
			<br />
		</td>
		<td class="wm_settings_cont" valign="top" id="center_tables">
		
<form action="<?php GoodOrBadAction($_allGood); ?>" method="post" id="check_form" >

<table class="wm_admin_center" width="550">
	<tr>
		<td colspan="2"><br /></td>
	</tr>
	<tr>
		<td colspan="2">
			<span style="font-size: 14px">Step 1 of <?php echo $max; ?>:</span>
			<br />
			<span style="font-size: 18px">Server Compatibility Test and Pre-Installation Check</span>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2">
The installer will now check that all the required server software is installed,
has correct versions and configured properly. It will also check if
WebMail data folder is specified correctly.
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2">

<?php echo $_text; ?>

		</td>
	</tr>    
	<tr><td colspan="2"><br /></td></tr>
	<tr><td colspan="2"><hr size="1" /></td></tr>
	<tr>
		<td colspan="2" align="right">
			<?php GoodOrBadButton($_allGood); ?>
		</td>
	</tr>
	<tr><td colspan="2"><br /><br /></td></tr>
</table>
</form>
		</td>
	</tr>
</table>
		</div>
	</div>
</body>
</html>
<?php		
		
		/* -- step 2 */
	}
	else if (isset($_SESSION[AP_SESS_GOODORBAD]) && $_SESSION[AP_SESS_GOODORBAD])
	{
		/* step 3 */

		$AdminPanel = new CAdminPanel(__FILE__);
		$AdminPanel->Write();
		
		/* -- step 3 */
	}
	else
	{
		@session_destroy();
		@header('Location: install.php');
		exit();
	}
