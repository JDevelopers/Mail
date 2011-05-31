<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 *
 */
	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/'));

	require_once(WM_ROOTPATH.'common/inc_top.php');
	
	require WM_ROOTPATH.'common/class_session.php';

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

	unset($_SESSION[SESSION_LANG]);

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

	if (!$settings->AllowRegistration)
	{
		@header('Location: ./index.php');
		exit();
	}

	$_langDiv = '';
	if ($settings->AllowLanguageOnLogin)
	{
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

	$_log =& CLog::CreateInstance($settings);

	$_rtl = in_array(defaultLanguage, explode('|', RTL_ARRAY));
	$_style = ($_rtl) ? '<link rel="stylesheet" href="skins/'.ConvertUtils::AttributeQuote($settings->DefaultSkin).'/styles-rtl.css" type="text/css">' : '';
	$_js_rtl = ($_rtl) ? 'var RTL = true;' : '';
	$_isGdSupport = @function_exists('imagecreatefrompng');

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

	$eximDomains = $dbStorage->GetDomainsArray();
	foreach ($eximDomains as $idDomain => $domainArray)
	{
		if (isset($domainArray[2]) && !$domainArray[2])
		{
			unset($eximDomains[$idDomain]);
		}
	}

	function GetDomainStringLine($eximDomains)
	{
		$onlyOne = (count($eximDomains) == 1);
		$return = '';
		if (!$onlyOne)
		{
			$return .= '&nbsp;<select class="wm_input" id="reg_domain" style="width:130px;">';
		}
		foreach ($eximDomains as $domainArray)
		{
			if ($onlyOne)
			{
				$return = $domainArray[0].'<input type="hidden" id="reg_domain" value="'.$domainArray[0].'" />';
				break;
			}
			else
			{
				$return .= '<option value="'.$domainArray[0].'">'.$domainArray[0].'</option>';
			}
		}
		if (!$onlyOne)
		{
			$return .= '</select>';
		}
		
		return $return;
	}

	$domainStr = '';
	if ($eximDomains && count($eximDomains) > 0)
	{
		$domainStr = GetDomainStringLine($eximDomains);
	}
	else
	{
		@header('Location: ./index.php');
		exit();
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
	<link rel="stylesheet" href="skins/<?php echo defaultSkin; ?>/reg-styles.css" type="text/css" />
	<?php echo $_style; ?>

	<script type="text/javascript">
		var NeedToSubmit = false;
		var WebMailUrl = "webmail.php";
		var LoginUrl = "reg.php";
		var ActionUrl = "processing.php";
		var LanguageUrl = "langs.js.php";
		var WmVersion = "<?php echo JS_VERS; ?>";
<?php
		echo $_js_rtl;
?>
	</script>
	<script type="text/javascript" src="langs.js.php?v=<?php echo JS_VERS; ?>&lang=<?php echo ConvertUtils::AttributeQuote(defaultLanguage); ?>"></script>
<?php if (USE_JS_GZIP && IS_SUPPORT_GZIP) { ?>
	<script type="text/javascript" src="cache-loader.php?v=<?php echo JS_VERS; ?>&t=def"></script>
	<script type="text/javascript" src="cache-loader.php?v=<?php echo JS_VERS; ?>&t=reg"></script>
<?php } else { ?>
	<script type="text/javascript" src="js/common/defines.js"></script>
	<script type="text/javascript" src="js/common/common-helpers.js"></script>
	<script type="text/javascript" src="js/common/loaders.js"></script>
	<script type="text/javascript" src="js/common/functions.js"></script>
	<script type="text/javascript" src="js/common/popups.js"></script>
	<script type="text/javascript" src="js/login/reg-screen.js"></script>
<?php } ?>
	<script type="text/javascript">
		function ChangeLang(object) {
			if (object && object.name && object.name.length > 4 && object.name.substr(0, 4) == 'lng_') {
				document.location = LoginUrl + '?lang=' + object.name.substr(4);
			}
		}
	</script>
</head>
<body onload="Init();" id="mbody">
	<table class="wm_hide" cellpadding="0" cellspacing="0" style="right: auto; width: auto; top: 0px; left: 604px;" id="info">
		<tr style="position:relative;z-index:20">
			<td class="wm_shadow" style="width:2px;font-size:1px;"></td>
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
	<div id="registration_screen">
		<div class="<?php echo $errorClass; ?>" id="reg_error">
			<div class="wm_login_error_icon"></div>
    		<div id="reg_error_message" class="wm_login_error_message"><?php echo $errorDesc; ?></div>
	    </div>
		<form action="reg.php?mode=submit" method="post" id="reg_form" name="reg_form" onsubmit="NeedToSubmit = true; return false;">
			<div class="wm_registration" >
				<div class="a top"></div>
				<div class="b top"></div>
				<div class="login_table">
					<div class="wm_login_header" id="lang_LoginInfo">
						<span id="lang_RegTitle"><?php echo RegRegistrationTitle; ?></span>
						<?php echo $_langDiv; ?>
					</div>
					<div class="wm_login_content">
						<div class="fieldset">
							<br />
							<div class="field">
								<label id="lang_RegName"><?php echo RegName; ?>:</label>
								<span class="inputfield">
									<input class="wm_input" type="text" id="reg_name" />
								</span>
							</div>
							<div class="field">
								<label id="lang_RegEmail"><?php echo RegEmail; ?>:</label>
								<span class="inputfield">
									<input type="text" class="wm_input" style="width:150px; text-align:right" id="reg_login" /> @<?php echo $domainStr; ?>
									<span class="text_desc" id="lang_RegEmailDesc">
										<?php echo RegEmailDesc; ?>
									</span>
								</span>
							</div>
							<div class="field">
								<span class="inputfield">
									<input class="wm_checkbox" type="checkbox" value="1" id="sign_me" name="sign_me" />
									<label for="sign_me" id="lang_SignMe" style="font-size: 12px;"><?php echo RegSignMe; ?></label>
									<span class="text_desc" id="lang_RegSignMeDesc">
										<?php echo RegSignMeDesc; ?>
									</span>
								</span>
							</div>
							<div class="separator"></div>
							<div class="field">
								<label id="lang_RegPass1"><?php echo RegPass1; ?>:</label>
								<span class="inputfield">
									<input class="wm_input" type="password" id="reg_pass_1" />
								</span>
							</div>
							<div class="field">
								<label id="lang_RegPass2"><?php echo RegPass2; ?>:</label>
								<span class="inputfield">
									<input class="wm_input" type="password" id="reg_pass_2" />
								</span>
							</div>
							<div class="separator"></div>
							<div class="field">
								<span class="inputfield">
									<span class="text_desc" id="lang_RegQuestionDesc">
										<?php echo RegQuestionDesc; ?>
									</span>
								</span>
							</div>
							<div class="field">
								<label id="lang_RegQuestion1"><?php echo RegQuestion1; ?>:</label>
								<span class="inputfield">
									<input class="wm_input" type="text" id="reg_question_1" />
								</span>
							</div>
							<div class="field">
								<label id="lang_RegAnswer1"><?php echo RegAnswer1; ?>:</label>
								<span class="inputfield">
									<input class="wm_input" type="text" id="reg_answer_1" />
								</span>
							</div>
							<div class="field">
								<label id="lang_RegQuestion2"><?php echo RegQuestion2; ?>:</label>
								<span class="inputfield">
									<input class="wm_input" type="text"  id="reg_question_2" />
								</span>
							</div>
							<div class="field">
								<label id="lang_RegAnswer2"><?php echo RegAnswer2; ?>:</label>
								<span class="inputfield">
									<input class="wm_input" type="text" id="reg_answer_2" />
								</span>
							</div>
							<div class="separator"></div>
							<div class="field">
								<label id="lang_RegTimeZone"><?php echo RegTimeZone; ?>:</label>
								<span class="inputfield">
									<select class="wm_input" id="reg_timezone">
<?php
	$posTime = 0;
	foreach ($TIMEZONE as $timeItem)
	{
		echo '<option value="'.$posTime++.'">'.$timeItem.'</option>';
	}
?>
									</select>
								</span>
							</div>
							<div class="field">
								<label id="lang_RegLang"><?php echo RegLang; ?>:</label>
								<span class="inputfield">
									<select class="wm_input" id="reg_lang">
<?php
	foreach ($langs as $lang)
	{
		$selected = (defaultLanguage === $lang) ? ' selected="selected"' : '';
		echo '<option value="'.$lang.'"'.$selected.'>'.GetNameByLang($lang).'</option>';
	}
?>
									</select>
								</span>
							</div>
<?php if ($_isGdSupport): ?>
							<div class="separator"></div>
							<div class="field">
								<label id="lang_RegCaptcha"><?php echo RegCaptcha; ?>:</label>
								<span class="inputfield">
									<input style="width:95px; font-size:16px;" class="wm_input" type="text" id="captcha" name="captcha" maxlength="6"
										onfocus="this.className = 'wm_input_focus';" onblur="this.className = 'wm_input';" />
									<br /><br />
									<img src="captcha.php?<?php echo 'PHPWEBMAILSESSID='.@session_id().'&c='.rand(100, 999); ?>"
										 id="captcha_img" width="120" height="46" class="wm_chaptcha" />
									<br />
									<a href="#" class="wm_reg" id="lang_CaptchaReloadLink"><?php echo CaptchaReloadLink; ?></a>
								</span>
							</div>
<?php endif; ?>
							<div class="separator"></div>
							<div class="field" style="text-align:right">
								<input class="wm_button" type="submit" id="submitId" name="submitName" value="<?php echo RegSubmitButtonValue; ?>" />
							</div>
						</div>
						<div class="clear"></div>
					</div>
				</div>
				<div class="b"></div>
				<div class="a"></div>
			</div>
		</form>
		<div class="clear"></div>
	</div>
	<a class="wm_reg_link" href="index.php" id="return_id"><?php echo RegReturnLink; ?></a>
	<div id="dummy"></div>
</div>
<div class="wm_copyright" id="copyright">
<?php @require('inc.footer.php'); ?>
</div>
</body>
</html>
<?php
	echo '<!-- '.WMVERSION.' -->';
