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
	require_once(WM_ROOTPATH.'common/inc_constants.php');
	
	@ob_start(USE_INDEX_GZIP ? 'obStartGzip' : 'obStartNoGzip');

	require WM_ROOTPATH.'common/class_session.php';

	require_once(WM_ROOTPATH.'common/class_account.php');
	
	if (!isset($_SESSION[ACCOUNT_ID]))
	{
		if (USE_DB && isset($_COOKIE['awm_autologin_data']) && isset($_COOKIE['awm_autologin_id']))
		{
			$account = &Account::LoadFromDb($_COOKIE['awm_autologin_id'], false, false);
			if ($account != null && $_COOKIE['awm_autologin_data'] == 
					md5(ConvertUtils::EncodePassword($account->MailIncPassword, $account)))
			{
				$_SESSION[ACCOUNT_ID] = $account->Id; 
				$_SESSION[USER_ID] = $account->IdUser;
				$_SESSION[SESSION_LANG] = $account->DefaultLanguage;
				header('Location: webmail.php?check=1');
				exit;
			}
		}
	}
	
	if (!isset($_SESSION[ACCOUNT_ID]))
	{
		header('Location: index.php?error=1');
		exit;
	}

	$check = isset($_GET['check']) ? $_GET['check'] : 0;
	$start = isset($_GET['start']) ? $_GET['start'] : null;
	$start = isset($_POST['start']) ? $_POST['start'] : $start;
	$start = null === $start ? 0 : $start;
	$to = isset($_GET['to']) ? preg_replace('/[^a-zA-Z\.\-@]/', '', $_GET['to']) : '';

	$null = null;

	$params = array();
	if ($start > 0)
	{
		$params[] = 'start='.$start;
	}
	if (strlen($to) > 0)
	{
		$params[] = 'to='.$to;
	}
	
	$paramsLine = implode('&', $params);
	
	if ($check)
	{
		$account =& Account::LoadFromDb($_SESSION[ACCOUNT_ID], false, false);
		if (!$account)
		{
			header('Location: index.php?error=2');
			exit();
		}
		
		$_SESSION[SESSION_LANG] = $account->DefaultLanguage;


		$paramsLine = strlen($paramsLine) > 0 ? '?'.$paramsLine : '';
		
		define('G_WEBMAILURL', 'webmail.php'.$paramsLine);
		require_once(WM_ROOTPATH.'check-mail-at-login.php');
	}
	else
	{
		$paramsLine = strlen($paramsLine) > 0 ? '?'.$paramsLine : '';
		
		if (!isset($_GET['iframe']) && defined('USE_IFRAME_WEBMAIL') && strlen(USE_IFRAME_WEBMAIL) > 0)
		{
			header('Location: '.USE_IFRAME_WEBMAIL.$paramsLine);
			exit();
		}

		require_once(WM_ROOTPATH.'common/class_settings.php');
		require_once(WM_ROOTPATH.'common/class_filesystem.php');
		
		$settings =& Settings::CreateInstance();
		if (!$settings || !$settings->isLoad)
		{
			header('Location: index.php?error=3');
			exit();		
		}
		elseif (!$settings->IncludeLang())
		{
			header('Location: index.php?error=6');
			exit();			
		}
		
		$nAcct = isset($_GET['nacct']) ? (int) $_GET['nacct'] : null;
	
		if ($nAcct !== null  && USE_DB)
		{
			$dbStorage =& DbStorageCreator::CreateDatabaseStorage($null);
			if ($dbStorage->Connect() && $dbStorage->IsAccountInRing($_SESSION[ACCOUNT_ID], $nAcct))
			{
				$_SESSION[ACCOUNT_ID] = $nAcct;
			}
			else 
			{
				header('Location: index.php?error=2');
				exit();	
			}
		}
		
		$account =& Account::LoadFromDb($_SESSION[ACCOUNT_ID]);
		if (!$account)
		{
			header('Location: index.php?error=2');
			exit();
		}
		
		define('defaultTitle', $settings->WindowTitle);
		$skins =& FileSystem::GetSkinsList();
	
		$hasDefSettingsSkin = false;
		foreach ($skins as $skinName)
		{
			if ($skinName == $settings->DefaultSkin)
			{
				$hasDefSettingsSkin = true;
			}
			
			if ($skinName == $account->DefaultSkin)
			{
				define('defaultSkin', $account->DefaultSkin);
				break;
			}
		}
		
		if (!defined('defaultSkin'))
		{
			if ($hasDefSettingsSkin)
			{
				define('defaultSkin', $settings->DefaultSkin);
			}
			else
			{
				define('defaultSkin', $skins[0]);
			}
		}
		
		$_rtl = in_array($account->DefaultLanguage, explode('|', RTL_ARRAY));
		$_style = ($_rtl) ? '<link rel="stylesheet" href="skins/'.$account->DefaultSkin.'/styles-rtl.css" type="text/css" id="skin-rtl">' : '';
		$_js_rtl = ($_rtl) ? 'var RTL = true;' : '';
		
		define('JS_VERS', ConvertUtils::GetJsVersion());
	
		header('Content-type: text/html; charset=utf-8');
		header('Content-script-type: text/javascript');
		header('Pragma: cache');
		header('Cache-control: public');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html id="html">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Pragma" content="cache" />
	<meta http-equiv="Cache-Control" content="public" />
	<link rel="shortcut icon" href="favicon.ico" />
	<title><?php echo defaultTitle; ?></title>
	<link rel="stylesheet" href="skins/<?php echo ConvertUtils::AttributeQuote(defaultSkin); ?>/styles.css" type="text/css" id="skin" />
	<?php echo $_style; ?>
	<script type="text/javascript">
        var JSLoadedCount = 1;
		var TotalJSFilesCount = 52;
		function JSFileLoaded()
		{
			JSLoadedCount++;
			var percent = Math.ceil((JSLoadedCount)*100/(TotalJSFilesCount + 1));
			if (percent >= 0)
			{
				var jsProgressLoaded = document.getElementById('jsProgressLoaded');
				if (jsProgressLoaded)
				{
					percent = (percent > 100) ? 100 : percent;
					jsProgressLoaded.style.width = percent + 'px';
				}
			}
		}

        function BodyLoaded()
        {
            if (JSLoadedCount >= TotalJSFilesCount) {
                Init();
                window.onresize = ResizeBodyHandler;
                document.onkeyup = EventBodyHandler;
            }
        }
	</script>
</head>

<body onload="BodyLoaded();">
	<table class="wm_information wm_loading_information" cellpadding="0" cellspacing="0" style="right: auto; width: auto; top: 0px; left: 604px;" id="info_cont">
		<tr style="position:relative;z-index:20">
			<td class="wm_shadow" style="width:2px;font-size:1px;"></td>
			<td>
				<div class="wm_info_message" id="info_message">
					<span><?php echo JS_LANG_InfoWebMailLoading;?></span>
					<div class="wm_progressbar">
						<div id="jsProgressLoaded" class="wm_progressbar_used" style="width: 95px;"></div>
					</div>
				</div>
				<div class="a">&nbsp;</div>
				<div class="b">&nbsp;</div>
			</td>
			<td class="wm_shadow" style="width:2px;font-size:1px;"></td>
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
	<div align="center" id="content" class="wm_hide">
		<div class="wm_logo" id="logo" tabindex="-1" onfocus="this.blur();"></div>
	</div>
	<div id="spell_popup_menu" class="wm_hide"><?php echo SpellWait; ?></div>
	<div class="wm_hide" id="copyright">
		<?php require('inc.footer.php'); ?>
	</div>
</body>
<script type="text/javascript">
	var LoginUrl = 'index.php';
	var WebMailUrl = 'webmail.php<?php echo (isset($_GET['iframe'])) ? '?iframe' : ''; ?>';
	var ActionUrl = 'processing.php';
	var EditAreaUrl = 'edit-area.php';
	var EmptyHtmlUrl = 'empty.html';
	var UploadUrl = 'upload.php';
	var ImportUrl = 'import.php';
	var HistoryStorageUrl = 'history-storage.php';
	var CheckMailUrl = 'check-mail.php';
	var LanguageUrl = 'langs.js.php';
	var SpellcheckerUrl = 'spellcheck.php';
	var CalendarUrl = 'calendar.php';
	var CalendarProcessingUrl = 'calendar/processing.php';
	var ImageUploaderUrl = 'image-uploader.php';
	var MiniWebMailUrl = 'mini-webmail.php';
	var SessionSaverUrl = 'session-saver.php';
	
	var Title = "<?php echo ConvertUtils::ClearJavaScriptString(defaultTitle, '"'); ?>";
	var SkinName = "<?php echo ConvertUtils::ClearJavaScriptString(defaultSkin, '"'); ?>";
	var Start = <?php echo (int) $start; ?>;
	var ToAddr = "<?php echo ConvertUtils::ClearJavaScriptString($to, '"'); ?>";
	var XType = "<?php echo (int) XTYPE; ?>";
	var WmVersion = "<?php echo JS_VERS; ?>";
	var Seporated = <?php echo (isset($_SESSION[SEPARATED]) && $_SESSION[SEPARATED]) ? 'true' : 'false'; ?>;
	var CSType = <?php echo (@file_exists('CS')) ? 'true' : 'false'; ?>;
	<?php echo $_js_rtl; ?>
	var UseDb = true;
	var UseLdapSettings = false;
	var Browser, WebMail, HistoryStorage, SwfUploader;
	var ViewMessage = null;
	var WebMailSessId = '<?php echo @session_id(); ?>';
	
	function GetWidth() {
		var w = 1024;
		if (document.documentElement && document.documentElement.clientWidth) {
			w = document.documentElement.clientWidth;
		} else if (document.body.clientWidth) {
			w = document.body.clientWidth;
		} else if (self.innerWidth) {
			w = self.innerWidth;
		}
		return w;
	}
	var infoCont = document.getElementById('info_cont');
	if (infoCont) {
		infoCont.style.right = 'auto';
		infoCont.style.left = Math.round((GetWidth() - infoCont.offsetWidth)/2) + 'px';
	}
</script>
<script type="text/javascript" src="langs.js.php?v=<?php echo JS_VERS; ?>&lang=<?php echo ConvertUtils::AttributeQuote($account->DefaultLanguage); ?>"></script>
<?php if (USE_JS_GZIP && IS_SUPPORT_GZIP) { ?>
<script type="text/javascript" src="cache-loader.php?v=<?php echo JS_VERS; ?>&t=def"></script>
<script type="text/javascript" src="cache-loader.php?v=<?php echo JS_VERS; ?>&t=wm"></script>
<script type="text/javascript" src="cache-loader.php?v=<?php echo JS_VERS; ?>&t=wmp"></script>
<script type="text/javascript" src="cache-loader.php?v=<?php echo JS_VERS; ?>&t=cont"></script>
<?php } else { ?>
<script type="text/javascript" src="js/common/defines.js"></script>
<script type="text/javascript" src="js/common/calendar-screen.js"></script>
<script type="text/javascript" src="js/common/common-handlers.js"></script>
<script type="text/javascript" src="js/common/common-helpers.js"></script>
<script type="text/javascript" src="js/common/data-source.js"></script>
<script type="text/javascript" src="js/common/functions.js"></script>
<script type="text/javascript" src="js/common/loaders.js"></script>
<script type="text/javascript" src="js/common/page-switcher.js"></script>
<script type="text/javascript" src="js/common/popups.js"></script>
<script type="text/javascript" src="js/common/toolbar.js"></script>
<script type="text/javascript" src="js/common/variable-table.js"></script>
<script type="text/javascript" src="js/common/webmail.js"></script>

<script type="text/javascript" src="js/mail/autocomplete-recipients.js"></script>
<script type="text/javascript" src="js/mail/folders-pane.js"></script>
<script type="text/javascript" src="js/mail/html-editor.js"></script>
<script type="text/javascript" src="js/mail/mail-data.js"></script>
<script type="text/javascript" src="js/mail/mail-handlers.js"></script>
<script type="text/javascript" src="js/mail/message-headers.js"></script>
<script type="text/javascript" src="js/mail/message-info.js"></script>
<script type="text/javascript" src="js/mail/message-line.js"></script>
<script type="text/javascript" src="js/mail/message-list-prototype.js"></script>
<script type="text/javascript" src="js/mail/message-list-central-pane.js"></script>
<script type="text/javascript" src="js/mail/message-list-central-screen.js"></script>
<script type="text/javascript" src="js/mail/message-list-display.js"></script>
<script type="text/javascript" src="js/mail/message-list-top-screen.js"></script>
<script type="text/javascript" src="js/mail/new-message-screen.js"></script>
<script type="text/javascript" src="js/mail/message-reply-pane.js"></script>
<script type="text/javascript" src="js/mail/resizers.js"></script>
<script type="text/javascript" src="js/mail/swfupload.js"></script>
<script type="text/javascript" src="js/mail/view-message-screen.js"></script>

<script type="text/javascript" src="js/contacts/contact-line.js"></script>
<script type="text/javascript" src="js/contacts/contacts-data.js"></script>
<script type="text/javascript" src="js/contacts/contacts-handlers.js"></script>
<script type="text/javascript" src="js/contacts/contacts-screen.js"></script>
<script type="text/javascript" src="js/contacts/edit-contact.js"></script>
<script type="text/javascript" src="js/contacts/edit-group.js"></script>
<script type="text/javascript" src="js/contacts/import.js"></script>
<script type="text/javascript" src="js/contacts/view-contact.js"></script>

<script type="text/javascript" src="js/settings/account-list.js"></script>
<script type="text/javascript" src="js/settings/account-properties.js"></script>
<script type="text/javascript" src="js/settings/autoresponder.js"></script>
<script type="text/javascript" src="js/settings/calendar.js"></script>
<script type="text/javascript" src="js/settings/common.js"></script>
<script type="text/javascript" src="js/settings/defines-calendar.js"></script>
<script type="text/javascript" src="js/settings/filters.js"></script>
<script type="text/javascript" src="js/settings/folders.js"></script>
<script type="text/javascript" src="js/settings/mobile-sync.js"></script>
<script type="text/javascript" src="js/settings/settings-data.js"></script>
<script type="text/javascript" src="js/settings/signature.js"></script>
<script type="text/javascript" src="js/settings/user-settings-screen.js"></script>
<?php } ?>
<script type="text/javascript">
	function Init() {
		Browser = new CBrowser();
		if (Browser.IE && Browser.Version < 7) {
			try {
				document.execCommand('BackgroundImageCache', false, true);
			} catch(e) {}
		}
		HtmlEditorField.Build(<?php echo USE_DB ? 'false' : 'true'; ?>);
		var DataTypes = [
			new CDataType(TYPE_BASE, false, 0, false, { }, 'base' ),
			new CDataType(TYPE_ACCOUNT_BASE, false, 0, false, { IdAcct: 'id_acct', ChangeAcct: 'change_acct' }, 'account_base' ),
			new CDataType(TYPE_MESSAGES_BODIES, false, 0, false, { }, 'messages_bodies' ),
			new CDataType(TYPE_FOLDERS_BASE, false, 0, false, { }, 'folders_base' ),
			new CDataType(TYPE_SETTINGS_LIST, false, 0, false, { }, 'settings_list' ),
			new CDataType(TYPE_ACCOUNT_LIST, false, 0, false, { }, 'accounts' ),
			new CDataType(TYPE_FOLDER_LIST, true, 10, false, { IdAcct: 'id_acct', Sync: 'sync' }, 'folders_list' ),
			new CDataType(TYPE_MESSAGE_LIST, true, 20, false, { IdAcct: 'id_acct', Page: 'page', SortField: 'sort_field', SortOrder: 'sort_order' }, 'messages' ),
			new CDataType(TYPE_MESSAGES_OPERATION, false, 0, false, { }, '' ),
			new CDataType(TYPE_MESSAGE, true, 100, true, { Id: 'id', Charset: 'charset' }, 'message' ),
			new CDataType(TYPE_USER_SETTINGS, false, 0, false, { }, 'settings' ),
			new CDataType(TYPE_ACCOUNT_PROPERTIES, false, 0, false, { IdAcct: 'id_acct' }, 'account' ),
			new CDataType(TYPE_FILTERS, false, 0, false, { IdAcct: 'id_acct' }, 'filters' ),
			new CDataType(TYPE_FILTER_PROPERTIES, false, 0, false, { IdFilter: 'id_filter', IdAcct: 'id_acct' }, 'filter' ),
			new CDataType(TYPE_SIGNATURE, false, 0, false, { IdAcct: 'id_acct' }, 'signature' ),
			new CDataType(TYPE_AUTORESPONDER, false, 0, false, { IdAcct: 'id_acct' }, 'autoresponder' ),
			new CDataType(TYPE_MOBILE_SYNC, false, 0, false, { }, 'mobile_sync' ),
			new CDataType(TYPE_CONTACTS, true, 5, false, { Page: 'page', SortField: 'sort_field', SortOrder: 'sort_order' }, 'contacts_groups' ),
			new CDataType(TYPE_CONTACT, true, 20, false, { IdAddr: 'id_addr' }, 'contact' ),
			new CDataType(TYPE_GROUPS, false, 0, false, { }, 'groups' ),
			new CDataType(TYPE_GROUP, true, 10, false, { IdGroup: 'id_group' }, 'group' ),
			new CDataType(TYPE_SPELLCHECK, false, 0, false, { Word: 'word' }, 'spellcheck')
		];
		WebMail = new CWebMail(Title, SkinName);
		WebMail.DataSource = new CDataSource( DataTypes, ActionUrl, ErrorHandler, LoadHandler, TakeDataHandler, ShowLoadingInfoHandler );
		HistoryStorage = new CHistoryStorage(
			{
				Document: document,
				HistoryStorageObjectName: "HistoryStorage",
				PathToPageInIframe: HistoryStorageUrl,
				MaxLimitSteps: 50,
				Browser: Browser
			}
		);

		if (Start) {
			WebMail.SetStartScreen(Start);
		}
		
		WebMail.DataSource.Get(TYPE_BASE, { }, [], '');
		setTimeout(CreateSessionSaver, 20000);
		
<?php

	if (isset($_GET['prefetch_off']))
	{
		$_SESSION['awm_prefetch_off'] = true;
	} 
	else if (isset($_GET['prefetch_on']) && isset($_SESSION['awm_prefetch_off']))
	{
		unset($_SESSION['awm_prefetch_off']);
	}

	if (isset($_GET['logging_on']))
	{
		$_SESSION['awm_logging_on'] = true;
	}
	else if (isset($_GET['logging_off']) && isset($_SESSION['awm_logging_on']))
	{
		unset($_SESSION['awm_logging_on']);
	}
	
	if (isset($_SESSION['awm_prefetch_off']))
	{
		echo "\t\t".'UsePrefetch = false;'."\r\n";
	}
	
	if (!USE_DB)
	{
		echo "\t\t".'UseDb = false;'."\r\n";
	}

	if (USE_LDAP_SETTINGS_STORAGE)
	{
		echo "\t\t".'UseLdapSettings = true;'."\r\n";
	}

?>
	}
</script>

<script type="text/javascript">
	var FLASH_INSTALLED = 2
	var FLASH_NOT_INSTALLED = 1;
	var FLASH_UNKNOWN = 0;
	
	var hasMimeTypes = (navigator.mimeTypes && navigator.mimeTypes.length);
	var MSDetect = (hasMimeTypes) ? "false" : "true";
	
	var flashInstalled = FLASH_UNKNOWN;
	if (hasMimeTypes) {
		var flashMType = navigator.mimeTypes['application/x-shockwave-flash'];
		flashInstalled = (flashMType && flashMType.enabledPlugin) ? FLASH_INSTALLED : FLASH_NOT_INSTALLED;
	}
</script>
<script type="text/vbscript">
	If MSDetect = "true" Then
		flashInstalled = FLASH_NOT_INSTALLED
		On error resume next
		If (IsObject(CreateObject("ShockwaveFlash.ShockwaveFlash"))) Then
			flashInstalled = FLASH_INSTALLED
		End If
		If Err.Number<>0 Then
			flashInstalled = FLASH_NOT_INSTALLED
		End If
	End If
</script>
<script type="text/javascript">
	// flashInstalled = FLASH_NOT_INSTALLED;
</script>
</html><?php
	
		echo '<!-- '.WMVERSION.' -->';
	}
