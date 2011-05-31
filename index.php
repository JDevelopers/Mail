<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */
	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/'));

	require WM_ROOTPATH.'common/class_session.php';

	require_once(WM_ROOTPATH.'common/inc_top.php');
	require_once(WM_ROOTPATH.'common/inc_constants.php');
	
	@ob_start(USE_INDEX_GZIP ? 'obStartGzip' : 'obStartNoGzip');

	require_once(WM_ROOTPATH.'common/class_settings.php');
	require_once(WM_ROOTPATH.'common/class_folders.php');
	require_once(WM_ROOTPATH.'common/class_mailprocessor.php');
	require_once(WM_ROOTPATH.'common/class_filesystem.php');
	require_once WM_ROOTPATH.'common/class_tempfiles.php';
	require_once WM_ROOTPATH.'common/class_log.php';

	$errorClass = 'wm_hide';
	$errorDesc = '';
	$null = null;
	$error = isset($_GET['error']) ? $_GET['error'] : '';
	$isconfig = true;

	unset($_SESSION[SESSION_LANG], $_SESSION[SESSION_RESET_STEP], $_SESSION[SESSION_RESET_ACCT_ID]);

	$settings =& Settings::CreateInstance();
	if (!$settings || !$settings->isLoad)
	{
		$isconfig = false;
		$error = '3';
	}

	$langs =& FileSystem::GetLangList();
	if (isset($_GET['lang']) && in_array($_GET['lang'], $langs))
	{
		define('defaultLanguage', $_GET['lang']);
		setcookie('awm_defLang', defaultLanguage, time() + 31104000);
	}
	else if ($settings->AllowLanguageOnLogin && isset($_COOKIE['awm_defLang']) && in_array($_COOKIE['awm_defLang'], $langs))
	{
		define('defaultLanguage', $_COOKIE['awm_defLang']);
	}

	if (!defined('defaultLanguage'))
	{
		define('defaultLanguage', $settings->DefaultLanguage);
	}

	if ($isconfig && !$settings->IncludeLang(defaultLanguage))
	{
		$isconfig = false;
		$error = '1';
	}

	$_log =& CLog::CreateInstance($settings);
	
	if ($isconfig && isset($_SESSION[ACCOUNT_ID]))
	{
		$acct =& Account::LoadFromDb((int) $_SESSION[ACCOUNT_ID]);
		if ($acct)
		{
			$_log->SetEventPrefixByAccount($acct);
			$tempFiles =& CTempFiles::CreateInstance($acct);
			$tempFiles->ClearAccountCompletely();
			unset($tempFiles, $acct);
		}
	}
	
	$_rtl = in_array(defaultLanguage, explode('|', RTL_ARRAY));
	$_style = ($_rtl) ? '<link rel="stylesheet" href="skins/'.ConvertUtils::AttributeQuote($settings->DefaultSkin).'/styles-rtl.css" type="text/css">' : '';
	$_js_rtl = ($_rtl) ? 'var RTL = true;' : '';
	
	$mode = isset($_GET['mode']) ? $_GET['mode'] : 'standard';
	if ($mode == 'logout')
	{
		$_log->WriteEvent('User logout');
		if (isset($_SESSION['CPANEL_INTEGRATION']) && $_SESSION['CPANEL_INTEGRATION'] && @file_exists(WM_ROOTPATH.'/cpanel_integration/cpanel.php'))
		{
			// $_SESSION = array();
			@session_destroy();
			include (WM_ROOTPATH.'/cpanel_integration/cpanel.php');
		}
		else
		{
			if (isset($_COOKIE['awm_autologin_data']))
			{
				unset($_COOKIE['awm_autologin_data']);
			}
			if (isset($_COOKIE['awm_autologin_id']))
			{
				unset($_COOKIE['awm_autologin_id']);
			}

			@session_destroy();
			// $_SESSION = array();
		}
	}
	
	define('LOGIN_EMAIL', $settings->GetDev('index.email'));
	define('LOGIN_LOGIN', $settings->GetDev('index.login'));
	define('LOGIN_PASS', $settings->GetDev('index.pass'));
	
	define('JS_VERS', ConvertUtils::GetJsVersion());
		
	if (!$isconfig)
	{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Pragma" content="cache" />
	<meta http-equiv="Cache-Control" content="public" />
	<link rel="shortcut icon" href="favicon.ico" />
	<title>WebMail is not configured properly</title>
	<link rel="stylesheet" href="skins/AfterLogic/styles.css" type="text/css" />
	<?php echo $_style; ?>
</head>
<body>
<div align="center" id="content" class="wm_content">
	<div class="wm_logo" id="logo" tabindex="-1" onfocus="this.blur();"></div>
	<br /><br />
	<div class="wm_login_error">
		<div class="wm_login_error_icon"></div>
	    <div class="wm_login_error_message">WebMail is not configured properly.</div>
	</div>
	<div class="wm_copyright" id="copyright">
<?php
		@require('inc.footer.php');
		exit('</div></div>');
	}
	
	if ($error == '6') // lang error
	{
		$errorDesc = 'Can\'t find required language file.';
		$errorClass = 'wm_login_error';
	}
	else if ($error == '1') // session error 
	{
		$errorDesc = PROC_SESSION_ERROR;
		$errorClass = 'wm_login_error';
	}
	else if ($error == '2') // account error 
	{
		$errorDesc = PROC_CANT_LOAD_ACCT;
		$errorClass = 'wm_login_error';
	}
	else if ($error == '3') // settings error 
	{
		$errorDesc = PROC_CANT_GET_SETTINGS;
		$errorClass = 'wm_login_error';
	}
	else if ($error == '5') // connection error 
	{
		$errorDesc = PROC_CANT_LOAD_DB;
		$errorClass = 'wm_login_error';
	} 
	else 
	{
		if (isset($_COOKIE['awm_autologin_data'], $_COOKIE['awm_autologin_id']))
		{
			require_once(WM_ROOTPATH.'common/class_account.php');
			
			$account = &Account::LoadFromDb($_COOKIE['awm_autologin_id']);
			
			if ($account != null && $_COOKIE['awm_autologin_data'] == 
					md5(ConvertUtils::EncodePassword($account->MailIncPassword, $account)))
			{
				$_SESSION[ACCOUNT_ID] = $account->Id; 
				$_SESSION[USER_ID] = $account->IdUser;
				$_SESSION[SESSION_LANG] = $account->DefaultLanguage;
				
				header('Location: webmail.php?check=1');
				exit();
			}
		}
	}

	$dbStorage =& DbStorageCreator::CreateDatabaseStorage($null);
	
	@header('Content-type: text/html; charset=utf-8');

	define('defaultTitle', $settings->WindowTitle);
	
	$skins =& FileSystem::GetSkinsList();
	
	foreach ($skins as $skinName)
	{
		if ($skinName == $settings->DefaultSkin)
		{
			define('defaultSkin', $settings->DefaultSkin);
			break;
		}
	}
	
	if (!defined('defaultSkin'))
	{
		define('defaultSkin', (count($skins) > 0) ? $skins[0] : 'AfterLogic');
	}

	$_langDiv = '';
	if ($settings->AllowLanguageOnLogin)
	{
		$langs =& FileSystem::GetLangList();
		if (count($langs) > 0)
		{
			$_langDiv .= '
								<span class="wm_language_place">
									<a id="langs_selected" href="#" class="wm_reg" onclick="return false;" style="padding-right: 0px;"><span>'.GetNameByLang(defaultLanguage).'</span><font>&nbsp;</font><span class="wm_login_lang_switcher">&nbsp;</span></a>
									<input type="hidden" value="'.(isset($_GET['lang']) ? defaultLanguage : '').'" id="language" name="language">
									<br />
									<div id="langs_collection">';

			foreach ($langs as $langName)
			{
				$_langDiv .= '<a href="#" name="lng_'.ConvertUtils::AttributeQuote($langName).'" onclick="ChangeLang(this); return false;">'.GetNameByLang($langName).'</a>';
			}

			$_langDiv .= '
									</div>
								</span>';
		}
	}

	define('defaultIncServer', $settings->IncomingMailServer);
	define('defaultIncPort', $settings->IncomingMailPort);
	define('defaultOutServer', $settings->OutgoingMailServer);
	define('defaultOutPort', $settings->OutgoingMailPort);
	define('defaultUseSmtpAuth', $settings->ReqSmtpAuth);
	define('defaultSignMe', false);
	define('defaultIsAjax', 'true');
	define('defaultAllowAdvancedLogin', $settings->AllowAdvancedLogin);
	define('defaultHideLoginMode', $settings->HideLoginMode);
	
	$pop3Selected = ' selected="selected"';
	$imap4Selected = '';
	
	if ($settings->IncomingMailProtocol == IMAP4_PROTOCOL)
	{
		$imap4Selected = ' selected="selected"';
		$pop3Selected = '';
	}

	$smtpAuthChecked = (defaultUseSmtpAuth) ? ' checked="checked"' : '';
	$signMeChecked = (defaultSignMe) ? ' checked="checked"' : '';
	
	$emailClass = '';
	$emailTabindex = '1';
	$loginClass = '';
	$loginTabindex = '2';
	$loginWidth = '224px';
	$domainContent = '';
	$advancedLogin = false;
	$advancedDisplay = 'none';
	$switcherHref = '?mode=advanced';
	$switcherText = JS_LANG_AdvancedLogin;
	$optLogin = '';
	$domainOptional = '';
	$mode = isset($_GET['mode']) ? $_GET['mode'] : 'standard';
	switch ($mode)
	{
		default:
			$switcherHref = '?mode=advanced';
			$switcherText = JS_LANG_AdvancedLogin;
			$advancedDisplay = 'none';
			$advancedLogin = '0';
			if ($settings->HideLoginMode >= 20)
			{
				$emailClass = ' class="wm_hide"';
				$emailTabindex = '-1';
			}
			if ($settings->HideLoginMode == 10 || $settings->HideLoginMode == 11)
			{
				$loginClass = ' class="wm_hide"';
				$loginTabindex = '-1';
			}
			if ($settings->HideLoginMode == 21 || $settings->HideLoginMode == 23)
			{
				$loginWidth = '120px';
				$domainOptional = $settings->DefaultDomainOptional;
			}
			if ($settings->UseMultipleDomainsSelection)
			{
				$loginWidth = '120px';
				$domainsArray = $dbStorage->GetDomainsArray();
				if ($domainsArray)
				{
					if (count($domainsArray) > 1)
					{
						$domainOptional = '&nbsp;<select name="domainSelect" id="domainSelect" style="width: 80px">';
						foreach ($domainsArray as $domainItem)
						{
							$domainOptional .= '<option value="'.$domainItem[0].'">'.$domainItem[0].'</option>';
						}
						$domainOptional .= '</select>';
					}
					else if (count($domainsArray) == 1)
					{
						foreach ($domainsArray as $domainItem)
						{
							$domainOptional = '<input name="domainSelect" id="domainSelect" type="hidden" value="'.$domainItem[0].'" />'.$domainItem[0];
							break;
						}
					}
				}
			}
			break;
		case 'advanced':
			$switcherHref = '?mode=standard';
			$switcherText = JS_LANG_StandardLogin;
			$advancedDisplay = 'block';
			$advancedLogin = '1';
			break;
	}

	if (strlen($domainOptional) > 0)
	{
		$domainContent = '@'.$domainOptional;
		define('defaultDomainOptional', $domainOptional);
	}
	else
	{
		define('defaultDomainOptional', $settings->DefaultDomainOptional);
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<link rel="shortcut icon" href="favicon.ico" />
	<meta http-equiv="Pragma" content="cache" />
	<meta http-equiv="Cache-Control" content="public" />
	<title><?php echo defaultTitle; ?></title>
	<link rel="stylesheet" href="skins/<?php echo defaultSkin; ?>/styles.css" type="text/css" id="skin" />
<?php if ($settings->FlagsLangSelect): ?>
	<link rel="stylesheet" href="skins/<?php echo defaultSkin; ?>/login.css" type="text/css" id="skin" />
<?php endif; ?>
	<?php echo $_style; ?>
	
	<script type="text/javascript">
		var WebMailUrl = "webmail.php";
		var LoginUrl = "index.php";
		var ActionUrl = "processing.php";
		var Title = "<?php echo ConvertUtils::ClearJavaScriptString(defaultTitle, '"'); ?>";
		var SkinName = "<?php echo ConvertUtils::ClearJavaScriptString(defaultSkin, '"'); ?>";
		var DefLang = "<?php echo ConvertUtils::ClearJavaScriptString(defaultLanguage, '"'); ?>";
		var HideLoginMode = <?php echo defaultHideLoginMode; ?>;
		var DomainOptional = "<?php echo ConvertUtils::ClearJavaScriptString(defaultDomainOptional, '"'); ?>";
		var AllowAdvancedLogin = "<?php echo defaultAllowAdvancedLogin; ?>";
		var AdvancedLogin = "<?php echo $advancedLogin; ?>";
		var EmptyHtmlUrl = "empty.html";
		var CheckMailUrl = "check-mail.php";
		var LanguageUrl = "langs.js.php";
		var WmVersion = "<?php echo JS_VERS; ?>";
<?php 
		echo $_js_rtl; 
?>
		var NeedToSubmit = false;
		var PdaUrl = "pda/index.php";
<?php
		if (!USE_DB)
		{
			echo '		var UseDb = false;';
		}
?>	
	</script>
	<script type="text/javascript" src="langs.js.php?v=<?php echo JS_VERS; ?>&lang=<?php echo ConvertUtils::AttributeQuote(defaultLanguage); ?>"></script>
<?php if (USE_JS_GZIP && IS_SUPPORT_GZIP) { ?>
	<script type="text/javascript" src="cache-loader.php?v=<?php echo JS_VERS; ?>&t=def"></script>
	<script type="text/javascript" src="cache-loader.php?v=<?php echo JS_VERS; ?>&t=login"></script>
<?php } else { ?>
	<script type="text/javascript" src="js/common/defines.js"></script>
	<script type="text/javascript" src="js/common/common-helpers.js"></script>
	<script type="text/javascript" src="js/common/loaders.js"></script>
	<script type="text/javascript" src="js/common/functions.js"></script>
	<script type="text/javascript" src="js/common/popups.js"></script>
	<script type="text/javascript" src="js/login/login-screen.js"></script>
<?php }
	if (USE_JS_GZIP && IS_SUPPORT_GZIP):
?>
	<script type="text/javascript">
		WebMailScripts = ['cache-loader.php?v=<?php echo JS_VERS; ?>&t=wm', 'cache-loader.php?v=<?php echo JS_VERS; ?>&t=cont'];
	</script>
<?php endif; ?>
	<script type="text/javascript">
		function ChangeLang(object) {
			if (object && object.name && object.name.length > 4 && object.name.substr(0, 4) == 'lng_') {
				document.location = LoginUrl + '?lang=' + object.name.substr(4);
			}
		}
<?php if ($settings->FlagsLangSelect): ?>
		function defaultInit(){
            if (LoginDemoLangClass) LoginDemoLangClass.CheckLang('lng_' + DefLang);
		}
<?php endif; ?>
	</script>
	
</head>
<body onload="Init();" id="mbody">
	<table class="wm_hide" cellpadding="0" cellspacing="0" style="right: auto; width: auto; top: 0px; left: 604px;" id="info">
		<tr style="position:relative;z-index:20">
			<td class="wm_shadow" style="width:2px;"></td>
			<td>
				<div class="wm_info_message" id="info_message"></div>
				<div class="a">&nbsp;</div>
				<div class="b">&nbsp;</div>
			</td>
			<td class="wm_shadow" style="width:2px;font-size:1px;">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="wm_shadow" style="height:2px;background:none;">
				<div class="a">&nbsp;</div>
				<div class="b">&nbsp;</div>
			</td>
		</tr>
		<tr style="position:relative;z-index:19">
			<td colspan="3" style="height:2px;">
				<div class="a wm_shadow" style="margin:0px 2px;height:2px; top:-4px; position:relative; border:0px;background:#555;">&nbsp;</div>
			</td>
		</tr>
	</table>
<div align="center" class="wm_content">
	<div id="content">
		<div class="wm_logo" id="logo" tabindex="-1" onfocus="this.blur();"></div>
	</div>
	<div id="login_screen">
		<div class="<?php echo $errorClass; ?>" id="login_error">
			<div class="wm_login_error_icon"></div>
    		<div id="login_error_message" class="wm_login_error_message"><?php echo $errorDesc; ?></div>
	    </div>
		<form action="index.php?mode=submit" method="post" id="login_form" name="login_form" onsubmit="NeedToSubmit = true; return false;">
			<input type="hidden" name="advanced_login" value="<?php echo $advancedLogin; ?>" />
			<div class="wm_login" >
				<div class="a top"></div>
				<div class="b top"></div>
				<div class="login_table">
					<div class="wm_login_header" id="lang_LoginInfo"><?php echo LANG_LoginInfo?></div>
					<div class="wm_login_content">
						<table id="login_table" border="0" cellspacing="0" cellpadding="10">
							<tr id="email_cont"<?php echo $emailClass; ?>>
								<td class="wm_title" style="font-size:12px; width: 70px;" id="lang_Email"><?php echo LANG_Email?>:</td>
								<td colspan="4">
									<input style="width:224px; font-size:16px;" class="wm_input" type="text" value="<?php echo LOGIN_EMAIL; ?>" id="email" name="email" maxlength="255" 
										onfocus="this.className = 'wm_input_focus';" onblur="this.className = 'wm_input';" tabindex="<?php echo $emailTabindex;?>" />
								</td>
							</tr>
							<tr id="login_cont"<?php echo $loginClass; ?>>
								<td class="wm_title" style="font-size:12px; width: 70px;" id="lang_Login"><?php echo LANG_Login?>:</td>
								<td colspan="4" id="login_parent">
									<nobr>
										<input style="width: <?php echo $loginWidth;?>;font-size:16px;" class="wm_input" type="text" value="<?php echo LOGIN_LOGIN; ?>" id="login" name="login" maxlength="255"
											onfocus="this.className = 'wm_input_focus';" onblur="this.className = 'wm_input';" tabindex="<?php echo $loginTabindex;?>" />
										<span id="domain"><?php echo $domainContent; ?></span>
									</nobr>
								</td>
							</tr>
							<tr>
								<td class="wm_title" style="font-size:12px; width: 70px;" id="lang_Password"><?php echo LANG_Password; ?>:</td>
								<td colspan="4">
									<input tabindex="3" style="width:224px; font-size:16px;" class="wm_input wm_password_input" type="password" value="<?php echo LOGIN_PASS; ?>" id="password" name="password" maxlength="255"
										onfocus="this.className = 'wm_input_focus wm_password_input';" onblur="this.className = 'wm_input wm_password_input';" />
								</td>
							</tr>
<?php if ($settings->AllowPasswordReset): ?>
							<tr>
								<td></td>
								<td colspan="4">
									<a tabindex="4" class="wm_recover_link" href="password-reset.php" id="reset_link_id"><?php echo IndexResetLink;?></a>
								</td>
							</tr>
<?php 
	endif;

	if ($settings->UseCaptcha):
		$capthcaClass = 'wm_hide';
		if ((CATCHA_COUNT_LIMIT === 0) ||
			(isset($_SESSION['captcha_count']) && $_SESSION['captcha_count'] >= CATCHA_COUNT_LIMIT))
		{
			$capthcaClass = '';
		}

?>
							<tr valign="top" id="captcha_content" class="<?php echo $capthcaClass; ?>">
								<td class="wm_title" style="font-size:12px; width: 70px; padding-top:9px;" id="lang_CaptchaTitle"><?php echo CaptchaTitle; ?>:</td>
								<td align="center">
									<input tabindex="5" style="width:95px; font-size:16px;" class="wm_input" type="text" value="" id="captcha" name="captcha" maxlength="6"
										onfocus="this.className = 'wm_input_focus';" onblur="this.className = 'wm_input';" />
									<span class="wm_message_right"><a href="#" class="wm_reg" id="lang_CaptchaReloadLink"><?php echo CaptchaReloadLink; ?></a></span>
								</td>
								<td colspan="3">
									<img src="captcha.php?<?php echo 'PHPWEBMAILSESSID='.@session_id().'&c='.rand(100, 999); ?>"
										 id="captcha_img" width="120" height="46" class="wm_chaptcha" />
								</td>
							</tr>
<?php endif; ?>
						</table>
<?php if ($settings->AllowAdvancedLogin): ?>
						<div id="advanced_fields" style="margin:0px; height:95px; display:<?php echo $advancedDisplay?>; overflow:hidden; padding:0px;">
						<table cellspacing="0" cellpadding="6">
							<tr id="incoming">
								<td class="wm_title" id="lang_IncServer"><?php echo LANG_IncServer?>:</td>
								<td>
									<input tabindex="6" class="wm_advanced_input" type="text" value="<?php echo defaultIncServer?>" id="inc_server" name="inc_server" maxlength="255"
										onfocus="this.className = 'wm_advanced_input_focus';" onblur="this.className = 'wm_advanced_input';" />
								</td>
								<td>
									<select tabindex="7" class="wm_advanced_input" id="inc_protocol" name="inc_protocol"
										onfocus="this.className = 'wm_advanced_input_focus';" onblur="this.className = 'wm_advanced_input';">
										<option value="<?php echo POP3_PROTOCOL?>" <?php echo $pop3Selected?>><?php echo LANG_PopProtocol?></option>
										<option value="<?php echo IMAP4_PROTOCOL?>" <?php echo $imap4Selected?>><?php echo LANG_ImapProtocol?></option>
									</select>
								</td>
								<td class="wm_title" id="lang_IncPort"><?php echo LANG_IncPort?>:</td>
								<td>
									<input tabindex="8" class="wm_advanced_input" type="text" value="<?php echo defaultIncPort?>" id="inc_port" name="inc_port" maxlength="5"
										onfocus="this.className = 'wm_advanced_input_focus';" onblur="this.className = 'wm_advanced_input';" />
								</td>
							</tr>
							<tr id="outgoing">
								<td class="wm_title" id="lang_OutServer"><?php echo LANG_OutServer?>:</td>
								<td colspan="2">
									<input tabindex="9" class="wm_advanced_input" type="text" value="<?php echo defaultOutServer?>" id="out_server" name="out_server" maxlength="255"
										onfocus="this.className = 'wm_advanced_input_focus';" onblur="this.className = 'wm_advanced_input';" />
								</td>
								<td class="wm_title" id="lang_OutPort"><?php echo LANG_OutPort?>:</td>
								<td align="right">
									<input tabindex="10" class="wm_advanced_input" type="text" value="<?php echo defaultOutPort?>" id="out_port" name="out_port" maxlength="5"
										onfocus="this.className = 'wm_advanced_input_focus';" onblur="this.className = 'wm_advanced_input';" />
								</td>
							</tr>
							<tr id="authentication">
								<td colspan="5">
									<input tabindex="11" class="wm_checkbox" type="checkbox" value="1" id="smtp_auth" name="smtp_auth"<?php echo $smtpAuthChecked?> />
									<label for="smtp_auth" id="lang_UseSmtpAuth" style="font-size: 12px;"><?php echo LANG_UseSmtpAuth?></label>
								</td>
							</tr>
						</table>
						</div>
<?php endif; ?>
						<table>
							<tr class="<?php echo (USE_DB) ? '': 'wm_hide'; ?>">
								<td>
									<input tabindex="12" class="wm_checkbox" type="checkbox" value="1" id="sign_me" name="sign_me"<?php echo $signMeChecked?> />
									<label for="sign_me" id="lang_SignMe" style="font-size: 12px;"><?php echo LANG_SignMe?></label>
								</td>
							</tr>
							<tr>
								<td>
									<span class="wm_login_button">
										<input tabindex="14" class="wm_button" type="submit" id="submit" name="submit" value="<?php echo LANG_Enter?>" />
									</span>
<?php if (defaultAllowAdvancedLogin): ?>
									<span class="wm_login_switcher">
										<a tabindex="13" class="wm_reg" href="<?php echo $switcherHref?>" id="login_mode_switcher" onclick="return false;"><?php echo $switcherText?></a>
									</span>
<?php
	endif;
	echo $_langDiv;
?>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="b"></div>			
				<div class="a"></div>
			</div>
		</form>
	</div>
	<div class="info" id="demo_info" dir="ltr">
		
<?php if ($settings->FlagsLangSelect): ?>
		<div class="top">
			<div style="clear: both; margin: 0; padding: 0;height: 0; overflow: hidden;"></div>
			<div class="r2"></div>
			<div class="r1"></div>
		</div>
		<div class="middle">
            <div class="title">WebMail in your language</div>
			<div style="width: 40%; float: left; margin-left: 30px;" id="langDemoTop">
				<a name="lng_English"  href="#" class="sprite lang_eng" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;"><span class="sprite lang_usa"></span>English</a><br />
				<a name="lng_Arabic" href="#" class="sprite lang_arb" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">العربية</a><br />
				<a name="lng_Chinese-Simplified" href="#" class="sprite lang_ch_simple" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">中文(简体)</a><br />
				<a name="lng_Chinese-Traditional" href="#" class="sprite lang_ch_tr" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">中文(香港)</a><br />
				<a name="lng_Danish" href="#" class="sprite lang_dan" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Dansk</a><br />
				<a name="lng_Dutch" href="#" class="sprite lang_dut" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Nederlands</a><br />
				<a name="lng_French" href="#" class="sprite lang_frn" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Français</a><br />

				<a name="lng_German" href="#" class="sprite lang_gmn" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Deutsch</a><br />
				<a name="lng_Greek" href="#" class="sprite lang_grk" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Ελληνικά</a><br />
				<a name="lng_Hebrew" href="#" class="sprite lang_hbw" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">עברית</a><br />
				<a name="lng_Hungarian" href="#" class="sprite lang_hng" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Magyar</a>
				
			</div>

			<div style="width: 45%; float: left;" id="langDemoBottom">
				<a name="lng_Italian" href="#" class="sprite lang_itl" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Italiano</a><br />
				<a name="lng_Japanese" href="#" class="sprite lang_jap" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">日本語</a><br />
				<a name="lng_Norwegian" href="#" class="sprite lang_nrw" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Norsk</a><br />
				<a name="lng_Polish" href="#" class="sprite lang_pls" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Polski</a><br />
				<a name="lng_Portuguese-Brazil" href="#" class="sprite lang_prt" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Portuguese-Brazil</a><br />
				<a name="lng_Russian" href="#" class="sprite lang_rss" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Русский</a><br />
				<a name="lng_Spanish" href="#" class="sprite lang_spn" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Español</a><br />

				<a name="lng_Swedish" href="#" class="sprite lang_swd" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Svenska</a><br />
				<a name="lng_Thai" href="#" class="sprite lang_tha" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">ภาษาไทย</a><br />
				<a name="lng_Turkish" href="#" class="sprite lang_trk" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Türkçe</a><br />
				<a name="lng_Ukrainian" href="#" class="sprite lang_ukr" onclick="LoginDemoLangClass.CheckLang(this.name); if (LoginScreen) LoginScreen.ChangeLang(this); return false;">Українська</a><br />
			</div>
			<div class="clear"></div>
			
		</div>
		<div class="bottom">
			<div class="r1"></div>
			<div class="r2"></div>
			<div style="clear: both; margin: 0; padding: 0; height: 0; overflow: hidden;"></div>
		</div>
		<?php endif; ?>
	</div>
<?php if ($settings->AllowRegistration): ?>
	<a class="wm_reg_link" href="reg.php" id="reg_link_id"><?php echo IndexRegLink; ?></a>
<?php endif; ?>
	
	<div id="dummy"></div>
</div>
<div class="wm_copyright" id="copyright">
<?php @require('inc.footer.php'); ?>
</div>
</body>
</html>
<?php 
	echo '<!-- '.WMVERSION.' -->';
