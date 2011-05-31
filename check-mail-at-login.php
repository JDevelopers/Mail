<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */
	
	@header('Content-type: text/html; charset=utf-8');

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/'));

	require_once(WM_ROOTPATH.'common/inc_top.php');
	require_once(WM_ROOTPATH.'common/class_settings.php');
	$settings =& Settings::CreateInstance();
	if (!$settings || !$settings->isLoad)
	{
		header('Location: index.php?error=3');
		exit();
	}
	
	if (!isset($_SESSION[ACCOUNT_ID]))
	{
		header('Location: index.php?error=2');
		exit();
	}

	if (!isset($_SESSION[SESSION_LANG]))
	{
		require_once(WM_ROOTPATH.'common/class_account.php');
		$_account = Account::LoadFromDb($_SESSION[ACCOUNT_ID], false, false);
		if (!$_account)
		{
			header('Location: index.php?error=2');
			exit();
		}
		define('defaultLang', $_account->DefaultLanguage);
	}
	else 
	{
		define('defaultLang', $_SESSION[SESSION_LANG]);
	}
	
	define('defaultTitle', $settings->WindowTitle);
	define('defaultSkin', $settings->DefaultSkin);
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html>
<head>
	<link rel="shortcut icon" href="favicon.ico" />
	<title><?php echo defaultTitle; ?></title>
	<link rel="stylesheet" href="skins/<?php echo ConvertUtils::AttributeQuote(defaultSkin);?>/styles.css" type="text/css" id="skin" />
	<script type="text/javascript" src="langs.js.php?v=<?php echo ConvertUtils::GetJsVersion(); ?>&lang=<?php echo ConvertUtils::AttributeQuote(defaultLang); ?>"></script>
<?php if (USE_JS_GZIP && IS_SUPPORT_GZIP) { ?>
	<script type="text/javascript" src="cache-loader.php?v=<?php echo ConvertUtils::GetJsVersion(); ?>&t=def"></script>
<?php } else { ?>
	<script type="text/javascript" src="js/common/defines.js"></script>
	<script type="text/javascript" src="js/common/common-helpers.js"></script>
	<script type="text/javascript" src="js/common/loaders.js"></script>
	<script type="text/javascript" src="js/common/functions.js"></script>
	<script type="text/javascript" src="js/common/popups.js"></script>
<?php } ?>	
	<script type="text/javascript">
		var checkMail;
		var WebMailUrl = '<?php echo G_WEBMAILURL; ?>';
		var LoginUrl = 'index.php';
		var CheckMailUrl = 'check-mail.php';
		var EmptyHtmlUrl = 'empty.html';
		var Browser = new CBrowser();

		function Init()
		{
			checkMail = new CCheckMail(1);
			checkMail.Start();
		}
		
		function SetCheckingAccountHandler(accountName)
		{
			checkMail.SetAccount(accountName);
		}
		
		function SetStateTextHandler(text) {
			checkMail.SetText(text);
		}
		
		function SetCheckingFolderHandler(folder, count) {
			checkMail.SetFolder(folder, count);
		}
		
		function SetRetrievingMessageHandler(number) {
			checkMail.SetMsgNumber(number);
		}
		
		function SetDeletingMessageHandler(number) {
			checkMail.DeleteMsg(number);
		}
		
		function EndCheckMailHandler(error) {
			if (error == 'session_error') {
				document.location = LoginUrl + '?error=1';
			} else {
				document.location = WebMailUrl;
			}
		}
		
		function CheckEndCheckMailHandler() {
			if (checkMail.started) {
				document.location = WebMailUrl;
			}
		}
	</script>
</head>
<body onload="Init();">
<div align="center" id="content" class="wm_content">
	<div class="wm_logo" id="logo" tabindex="-1" onfocus="this.blur();"></div>
</div>
<div class="wm_copyright" id="copyright">
	<?php require('inc.footer.php'); ?>
</div>
</body>
</html>