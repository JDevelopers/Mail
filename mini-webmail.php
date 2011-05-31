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

	$openMode = isset($_GET['open_mode']) ? $_GET['open_mode'] : 0;
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
		$title = defaultTitle;

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

?>
<?php
	if ($openMode == 'view') {
		require_once(WM_ROOTPATH.'common/class_mailprocessor.php');

		$mes_id = isset($_GET['msg_id']) ? (int) $_GET['msg_id'] : -1;
		$mes_uid = isset($_GET['msg_uid']) ? $_GET['msg_uid'] : '';
		$folder_id = isset($_GET['folder_id']) ? (int) $_GET['folder_id'] : -1;
		$folder_name = isset($_GET['folder_full_name']) ? $_GET['folder_full_name'] : '';
		$mes_charset = isset($_GET['charset']) ? (int) $_GET['charset'] : -1;
		$msgSize = isset($_GET['size']) ? (int) $_GET['size'] : 0;
		$mode = isset($_GET['mode']) ? (int) $_GET['mode'] : 0;

		if ($mes_charset > 0)
		{
			$account->DefaultIncCharset = ConvertUtils::GetCodePageName($mes_charset);
			$GLOBALS[MailInputCharset] = $_account->DefaultIncCharset;
			$account->UpdateDefaultIncCharset();
		}

		$processor = new MailProcessor($account);

		$folder = null;
		if (!empty($folder_id) && !empty($folder_name))
		{
			$folder = new Folder($account->Id, $folder_id, $folder_name);
			$processor->GetFolderInfo($folder);

			if (!$folder || $folder->IdDb < 1)
			{
				///!!!!
			}
		}
		else
		{
			///!!!!
		}

		$msgIdUid = array($mes_id => $mes_uid);

		$_messageInfo = new CMessageInfo();
		$_messageInfo->SetInfo($mes_id, $mes_uid, $folder->IdDb, $folder->FullName);

		$modeForGet = $mode;
		if (empty($msgSize) || (int) $msgSize < BODYSTRUCTURE_MGSSIZE_LIMIT ||	// size
				($folder && FOLDERTYPE_Drafts == $folder->Type) ||				// draft
				(($mode & 8) == 8 || ($mode & 16) == 16 ||						// forward
					($mode & 32) == 32 || ($mode & 64) == 64))
		{
			$modeForGet = null;
		}

		$message = null;
		$message =& $processor->GetMessage($mes_id, $mes_uid, $folder, $modeForGet);

		if (null != $message)
		{
			if (($message->Flags & MESSAGEFLAGS_Seen) != MESSAGEFLAGS_Seen)
			{
				$processor->SetFlag($msgIdUid, $folder, MESSAGEFLAGS_Seen, ACTION_Set);
			}

			$_isFromSave = false;
			if (USE_DB && ($modeForGet === null || (($modeForGet & 1) == 1)))
			{
				$_fromObj = new EmailAddress();
				$_fromObj->Parse($message->GetFromAsString(true));

				if ($_fromObj->Email)
				{
					$_isFromSave = $processor->DbStorage->SelectSenderSafetyByEmail($_fromObj->Email, $account->IdUser);
				}

				if ($folder->SyncType != FOLDERSYNC_DirectMode && $processor->DbStorage->Connect())
				{
					$processor->DbStorage->UpdateMessageCharset($mes_id, $mes_charset, $message);
				}
			}

			$textCharset = $message->GetTextCharset();
			$isRTL = false;
			if (null !== $textCharset)
			{
				switch (ConvertUtils::GetCodePageNumber($textCharset))
				{
					case 1255:
					case 1256:
					case 28596:
					case 28598:
						$isRTL = true;
						break;
				}
			}

			$accountOffset = ($settings->AllowUsersChangeTimeZone)
				? $account->GetDefaultTimeOffset()
				: $account->GetDefaultTimeOffset($settings->DefaultTimeZone);

			$date =& $message->GetDate();
			$date->FormatString = $account->DefaultDateFormat;
			$date->TimeFormat = $account->DefaultTimeFormat;

			$from4search =& $message->GetFrom();
	//			if ($from4search && USE_DB)
	//			{
	//				$id_addr = $processor->DbStorage->GetContactIdByEmail($from4search->Email, $account->IdUser);
	//			}
	//
	//			if ($id_addr > 0)
	//			{
	//				$_fromNode->AppendAttribute('contact_id', $id_addr);
	//				$_bigContactNode = CXmlProcessing::GetContactNodeFromAddressBookRecord($account, $settings, $id_addr);
	//				if (null != $_bigContactNode)
	//				{
	//					$_xmlRes->XmlRoot->AppendChild($_bigContactNode);
	//				}
	//			}

			$safety = true;
			$HtmlBody = '';
			$PlainBody = '';

			$_messageClassType = $message->TextBodies->ClassType();

			if (($mode & 2) == 2 && ($_messageClassType & 2) == 2)
			{
				$HtmlBody = ConvertUtils::ReplaceJSMethod(
					$message->GetCensoredHtmlWithImageLinks(true, $_messageInfo));

				if (($account->ViewMode == VIEW_MODE_PREVIEW_PANE_NO_IMG ||
					$account->ViewMode == VIEW_MODE_WITHOUT_PREVIEW_PANE_NO_IMG) && !$_isFromSave)
				{
					$HtmlBody = ConvertUtils::HtmlBodyWithoutImages($HtmlBody);
					if (isset($GLOBALS[GL_WITHIMG]) && $GLOBALS[GL_WITHIMG])
					{
						$GLOBALS[GL_WITHIMG] = false;
						$safety = false;
					}
				}
			}

			if (($mode & 4) == 4 || ($mode & 2) == 2 && ($_messageClassType & 2) != 2)
			{
				$PlainBody = $message->GetCensoredTextBody(true);
			}

			$addAttachArray = array();

			$_msqAttachLine = 'msg_id='.$mes_id.'&msg_uid='.urlencode($mes_uid).
				'&folder_id='.$folder->IdDb.'&folder_fname='.urlencode($folder->FullName);

			if (($mode & 256) == 256 || ($mode & 8) == 8 || ($mode & 16) == 16 || ($mode & 32) == 32 || ($mode & 64) == 64)
			{
				$_attachments =& $message->Attachments;
				if ($_attachments && $_attachments->Count() > 0)
				{
					$tempFiles =& CTempFiles::CreateInstance($account);
					$_attachmentsNode = new XmlDomNode('attachments');
					$_attachmentsKeys = array_keys($_attachments->Instance());
					foreach ($_attachmentsKeys as $_key)
					{
						$attachArray = array();

						$_attachment =& $_attachments->Get($_key);
						$_tempname = $message->IdMsg.'-'.$_key.'_'.ConvertUtils::ClearFileName($_attachment->GetTempName());
						$_filename = ConvertUtils::ClearFileName(ConvertUtils::ClearUtf8($_attachment->GetFilenameFromMime(), $GLOBALS[MailInputCharset], $account->GetUserCharset()));
						$_size = 0;
						$_isBodyStructureAttachment = false;
						if ($_attachment->MimePart && $_attachment->MimePart->BodyStructureIndex !== null && $_attachment->MimePart->BodyStructureSize !== null)
						{
							$_isBodyStructureAttachment = true;
							$_size = $_attachment->MimePart->BodyStructureSize;
						}
						else
						{
							$_size = $tempFiles->SaveFile($_tempname, $_attachment->GetBinaryBody());
							$_size = ($_size < 0) ? 0 : $_size;
						}

						$attachArray['name'] = $_filename;
						$attachArray['tempname'] = $_tempname;
						$attachArray['size'] = (int) $_size;

						$_bodyStructureUrlAdd = '';
						if ($_isBodyStructureAttachment)
						{
							$_bodyStructureUrlAdd = 'bsi='.urlencode($_attachment->MimePart->BodyStructureIndex);
							if ($_attachment->MimePart->BodyStructureEncode !== null && strlen($_attachment->MimePart->BodyStructureEncode) > 0)
							{
								$_bodyStructureUrlAdd .= '&bse='.urlencode(ConvertUtils::GetBodyStructureEncodeType($_attachment->MimePart->BodyStructureEncode));
							}
						}

						$attachArray['inline'] = (bool) $_attachment->IsInline;
						$attachArray['filename'] = $_filename;

						$viewUrl = (substr(strtolower($_filename), -4) == '.eml')
							? 'message-view.php?type='.MESSAGE_VIEW_TYPE_ATTACH.'&tn='.urlencode($_tempname)
							: 'view-image.php?img&tn='.urlencode($_tempname).'&filename='.urlencode($_filename);

						if ($_isBodyStructureAttachment)
						{
							$viewUrl .= '&'.$_bodyStructureUrlAdd.'&'.$_msqAttachLine;
						}

						$attachArray['view'] = $viewUrl;

						$linkUrl = 'attach.php?tn='.urlencode($_tempname);
						if ($_isBodyStructureAttachment)
						{
							$linkUrl .= '&'.$_bodyStructureUrlAdd.'&'.$_msqAttachLine;
						}

						$downloadUrl = $linkUrl.'&filename='.urlencode($_filename);

						$attachArray['download'] = $downloadUrl;
						$attachArray['link'] = $linkUrl;

						$mime_type = ConvertUtils::GetContentTypeFromFileName($_filename);
						$attachArray['mime_type'] = $mime_type;
						$attachArray['download'] = $downloadUrl;

						$addAttachArray[] = $attachArray;
						unset($_attachment, $_attachNode, $attachArray);
					}
				}
			}
			$title = ConvertUtils::ReBuildStringToJavaScript($message->GetSubject(true), '\'').' - '.defaultTitle;
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html id="html">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Pragma" content="cache" />
	<meta http-equiv="Cache-Control" content="public" />
	<link rel="shortcut icon" href="favicon.ico" />
	<title><?php echo $title; ?></title>
	<link rel="stylesheet" href="skins/<?php echo ConvertUtils::AttributeQuote(defaultSkin); ?>/styles.css" type="text/css" id="skin" />
	<?php echo $_style; ?>
	<script type="text/javascript">
		var WebMailSessId = '<?php echo @session_id(); ?>';
		var OpenMode = '<?php echo $openMode; ?>';
		var ToAddr = '<?php echo $to; ?>';
		var ActionUrl = 'processing.php';
		var LoginUrl = 'index.php';
		var EmptyHtmlUrl = 'empty.html';
		var Browser;
		var PreviewPane, NewMessageScreen;
		var UseDb = true;
		var UploadUrl = 'upload.php';
		var EditAreaUrl = 'edit-area.php';
		var WebMail = {
			_title: '<?php echo defaultTitle; ?>',
			Settings: {
				AllowContacts: false,
				AllowDhtmlEditor: <?php echo $settings->AllowDhtmlEditor ? 'true' : 'false' ?>,
				ShowTextLabels: <?php echo $settings->ShowTextLabels ? 'true' : 'false' ?>
			}
		}
		var CurrentAccount = {
			Id: <?php echo $account->Id ?>,
			UseFriendlyNm: <?php echo $account->UseFriendlyName ? 'true' : 'false' ?>,
			FriendlyNm: '<?php echo ConvertUtils::ReBuildStringToJavaScript($account->FriendlyName) ?>',
			Email: '<?php echo ConvertUtils::ReBuildStringToJavaScript($account->Email) ?>',
			MailProtocol: <?php echo $account->MailProtocol ?>,
			SignatureOpt: <?php echo $account->SignatureOptions ?>,
			SignatureType: <?php echo $account->SignatureType ?>,
			Signature: '<?php echo ConvertUtils::ReBuildStringToJavaScript($account->Signature) ?>'
		};
	</script>
</head>

<body onload="BodyLoaded();">
	<table class="wm_information wm_loading_information" cellpadding="0" cellspacing="0" style="right: auto; width: auto; top: 0px; left: 272px;" id="info_cont">
		<tr style="position:relative;z-index:20">
			<td class="wm_shadow" style="width:2px;font-size:1px;"></td>
			<td>
				<div class="wm_info_message" id="info_message">
					<span><?php echo JS_LANG_Loading;?></span>
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
</body>
<script type="text/javascript" src="langs.js.php?v=<?php echo JS_VERS; ?>&lang=<?php echo ConvertUtils::AttributeQuote($account->DefaultLanguage); ?>"></script>
<script type="text/javascript" src="js/common/common-helpers.js"></script>
<script type="text/javascript" src="js/common/common-handlers.js"></script>
<script type="text/javascript" src="js/common/data-source.js"></script>
<script type="text/javascript" src="js/common/defines.js"></script>
<script type="text/javascript" src="js/common/functions.js"></script>
<script type="text/javascript" src="js/common/loaders.js"></script>
<script type="text/javascript" src="js/common/popups.js"></script>
<script type="text/javascript" src="js/common/toolbar.js"></script>
<script type="text/javascript" src="js/common/webmail.js"></script>
<script type="text/javascript" src="js/mail/autocomplete-recipients.js"></script>
<script type="text/javascript" src="js/mail/html-editor.js"></script>
<script type="text/javascript" src="js/mail/mail-data.js"></script>
<script type="text/javascript" src="js/mail/mail-handlers.js"></script>
<script type="text/javascript" src="js/mail/message-headers.js"></script>
<script type="text/javascript" src="js/mail/message-info.js"></script>
<script type="text/javascript" src="js/mail/message-list-prototype.js"></script>
<script type="text/javascript" src="js/mail/new-message-screen.js"></script>
<script type="text/javascript" src="js/mail/message-reply-pane.js"></script>
<script type="text/javascript" src="js/mail/mini-webmail-window.js"></script>
<script type="text/javascript" src="js/mail/resizers.js"></script>
<script type="text/javascript" src="js/mail/swfupload.js"></script>
<script type="text/javascript" src="js/mail/view-message-screen.js"></script>
<script type="text/javascript" src="js/contacts/contacts-data.js"></script>
<script type="text/javascript">
<?php
	if (($openMode == 'view') && (
			($mode & 256) == 256 ||
			($mode & 8) == 8 ||
			($mode & 16) == 16 ||
			($mode & 32) == 32 ||
			($mode & 64) == 64))
	{
		echo "	ViewMessage = new CMessage();
	ViewMessage.FolderId = ".$folder->IdDb.";
	ViewMessage.FolderFullName = '".ConvertUtils::ReBuildStringToJavaScript($folder->FullName, '\'')."';
	ViewMessage.Size = ".((int) $message->Size).";
	ViewMessage.Id = ".$message->IdMsg.";
	ViewMessage.Uid = '".ConvertUtils::ReBuildStringToJavaScript($message->Uid, '\'')."';
	ViewMessage.HasHtml = ".($message->HasHtmlText() ? 'true' : 'false').";
	ViewMessage.HasPlain = ".($message->HasPlainText() ? 'true' : 'false').";
	ViewMessage.Importance = ".$message->GetPriorityStatus().";
	ViewMessage.Sensivity = ".$message->GetSensitivity().";
	ViewMessage.Charset = ".$mes_charset.";
	ViewMessage.HasCharset = ".($message->HasCharset ? 'true' : 'false').";
	ViewMessage.RTL = ".($isRTL ? 'true' : 'false').";
	ViewMessage.Safety = ".((int) $safety).";
	ViewMessage.Downloaded = ".($message->Downloaded ? 'true' : 'false').";
	ViewMessage.FromAddr = '".ConvertUtils::ReBuildStringToJavaScript($from4search->ToDecodedString(), '\'')."';
	ViewMessage.FromDisplayName = '".ConvertUtils::ReBuildStringToJavaScript(WebMailMessage::ClearForSend(trim($from4search->DisplayName)), '\'')."';
	ViewMessage.ToAddr = '".ConvertUtils::ReBuildStringToJavaScript($message->GetToAsString(true), '\'')."';
	ViewMessage.ShortToAddr = '".ConvertUtils::ReBuildStringToJavaScript($message->GetToAsString(true), '\'')."';
	ViewMessage.CCAddr = '".ConvertUtils::ReBuildStringToJavaScript($message->GetCcAsString(true), '\'')."';
	ViewMessage.BCCAddr = '".ConvertUtils::ReBuildStringToJavaScript($message->GetBccAsString(true), '\'')."';
	ViewMessage.ReplyToAddr = '".ConvertUtils::ReBuildStringToJavaScript($message->GetReplyToAsString(true), '\'')."';
	ViewMessage.Subject = '".ConvertUtils::ReBuildStringToJavaScript($message->GetSubject(true), '\'')."';
	ViewMessage.Date = '".ConvertUtils::ReBuildStringToJavaScript($date->GetFormattedShortDate($accountOffset), '\'')."';
	ViewMessage.FullDate = '".ConvertUtils::ReBuildStringToJavaScript($date->GetFormattedFullDate($accountOffset), '\'')."';
	ViewMessage.Time = '".ConvertUtils::ReBuildStringToJavaScript($date->GetFormattedTime($accountOffset), '\'')."';
	ViewMessage.HtmlBody = '".ConvertUtils::ReBuildStringToJavaScript($HtmlBody, '\'')."';
	ViewMessage.PlainBody = '".ConvertUtils::ReBuildStringToJavaScript($PlainBody, '\'')."';
	ViewMessage.ClearPlainBody = '".ConvertUtils::ReBuildStringToJavaScript($PlainBody, '\'')."';
	ViewMessage.SaveLink = '".ConvertUtils::ReBuildStringToJavaScript('attach.php?'.$_msqAttachLine, '\'')."';
	ViewMessage.PrintLink = '".ConvertUtils::ReBuildStringToJavaScript('message-view.php?type='.MESSAGE_VIEW_TYPE_PRINT.'&'.$_msqAttachLine.'&charset='.$mes_charset, '\'')."';
	ViewMessage.Attachments = [];";
		foreach ($addAttachArray as $attachItem)
		{
			echo '
	ViewMessage.Attachments.push({
		Id: -1, Inline: '.(($attachItem['inline']) ? 'true' : 'false').', Size: '.$attachItem['size'].', MimeType: "'.ConvertUtils::ReBuildStringToJavaScript($attachItem['mime_type'], '"').'",
		FileName: "'.ConvertUtils::ReBuildStringToJavaScript($attachItem['filename'], '"').'",
		Download: "'.ConvertUtils::ReBuildStringToJavaScript($attachItem['download'], '"').'",
		View: "'.ConvertUtils::ReBuildStringToJavaScript($attachItem['view'], '"').'",
		TempName: "'.ConvertUtils::ReBuildStringToJavaScript($attachItem['tempname'], '"').'"
	});'."\r\n";
		}
		$_signature_html = '';
		$_signature_plain = '';

		if ($account->SignatureOptions == SIGNATURE_OPTION_AddToAll)
		{
			if ($account->SignatureType == 1)
			{
				$_signature_html = '<br />'.$account->Signature;

				require_once WM_ROOTPATH.'libs/class_converthtml.php';
				$_pars = new convertHtml($account->Signature, false);
				$_signature_plain = CRLF.$_pars->get_text();
			}
			else
			{
				$_signature_plain = CRLF.$account->Signature;
				$_signature_html = '<br />'.nl2br($account->Signature);
			}

			$_signature_plain = ConvertUtils::WMHtmlSpecialChars($_signature_plain);
		}

		$replyAsHtml = $message->GetRelpyAsHtml(true, $accountOffset, $_messageInfo);
		if (($account->ViewMode == VIEW_MODE_PREVIEW_PANE_NO_IMG ||
			$account->ViewMode == VIEW_MODE_WITHOUT_PREVIEW_PANE_NO_IMG) && !$_isFromSave)
		{
			echo '
	ViewMessage.ReplyHtml = \''.
				ConvertUtils::ReBuildStringToJavaScript(ConvertUtils::AddToLinkMailToCheck(
					ConvertUtils::HtmlBodyWithoutImages(
						ConvertUtils::ReplaceJSMethod($_signature_html.$replyAsHtml))), '\'').'\';';
		}
		else
		{
			echo '
	ViewMessage.ReplyHtml = \''.
				ConvertUtils::ReBuildStringToJavaScript(ConvertUtils::AddToLinkMailToCheck(
					ConvertUtils::ReplaceJSMethod($_signature_html.$replyAsHtml)), '\'').'\';';
		}
		echo '
	ViewMessage.ReplyPlain = \''.
			ConvertUtils::ReBuildStringToJavaScript(ConvertUtils::AddToLinkMailToCheck(
				$_signature_plain.$message->GetRelpyAsPlain(true, $accountOffset)), '\'').'\';';
	echo '
	ViewMessage.IsReplyHtml = true;
	ViewMessage.IsReplyPlain = true;
	ViewMessage.IsForwardHtml = true;
	ViewMessage.IsForwardPlain = true;
	ViewMessage.ForwardHtml = ViewMessage.ReplyHtml;
	ViewMessage.ForwardPlain = ViewMessage.ReplyPlain;';
	}
?>
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
