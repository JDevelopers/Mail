<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 *
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/'));

	@header('Content-Type: text/html; charset=utf-8');

	require_once(WM_ROOTPATH.'common/inc_top.php');
	require WM_ROOTPATH.'common/class_session.php';

	function fixed_array_map_stripslashes($array)
	{
		if (is_array($array))
		{
			foreach ($array as $key => $value)
			{
				$array[$key] = (is_array($value))
						? @fixed_array_map_stripslashes($value)
						: @stripslashes($value);
			}
		}
		return $array;
	}

	function disable_magic_quotes_gpc()
	{
		if (@get_magic_quotes_gpc() == 1)
		{
			$_GET = fixed_array_map_stripslashes($_GET);
			$_POST = fixed_array_map_stripslashes($_POST);
		}
	}

	@disable_magic_quotes_gpc();

	require_once(WM_ROOTPATH.'common/inc_constants.php');
	require_once(WM_ROOTPATH.'common/class_settings.php');
	require_once(WM_ROOTPATH.'common/message_preview.php');

	$viewType = Get::val('type', false);

	if (false === $viewType)
	{
		exit();
	}

	$settings =& Settings::CreateInstance();

	if (!$settings || !$settings->isLoad)
	{
		if ($viewType == MESSAGE_VIEW_TYPE_FULL)
		{
			exit('Settings Error');
		}
		else
		{
			exit('<script>parent.changeLocation("'.LOGINFILE.'?error=3");</script>');
		}
	}
	else if (!$settings->IncludeLang())
	{
		if ($viewType == MESSAGE_VIEW_TYPE_FULL)
		{
			exit('Language Error');
		}
		else
		{
			exit('<script>parent.changeLocation("'.LOGINFILE.'?error=6");</script>');
		}
	}

	require_once(WM_ROOTPATH.'common/class_account.php');
	require_once(WM_ROOTPATH.'common/class_log.php');
	require_once(WM_ROOTPATH.'common/class_getmessagebase.php');

	$log =& CLog::CreateInstance();

	if (!Session::has(ACCOUNT_ID))
	{
		if ($viewType == MESSAGE_VIEW_TYPE_FULL)
		{
			exit(PROC_CANT_LOAD_ACCT);
		}
		else
		{
			exit('<script>parent.changeLocation("'.LOGINFILE.'?error=1");</script>');
		}
	}

	$account =& Account::LoadFromDb(Session::val(ACCOUNT_ID, -1));

	if (!$account)
	{
		if ($viewType == MESSAGE_VIEW_TYPE_FULL)
		{
			exit(PROC_CANT_LOAD_ACCT);
		}
		else
		{
			exit('<script>parent.changeLocation("'.LOGINFILE.'?error=2");</script>');
		}
	}

	$message = false;
	$isNull = true;

	$_rtl = in_array($account->DefaultLanguage, explode('|', RTL_ARRAY));
	
	$fromString = $toString = $ccString = $dateString =
		$subjectString = $attachString = $fullBodyText = '';

	$mes_id = Get::val('msg_id', '');
	$mes_uid = Get::val('msg_uid', '');
	$folder_id = Get::val('folder_id', '');
	$folder_name = Get::val('folder_fname', '');
	$mes_charset = Get::val('charset', -1);
	$bodytype = (int) Get::val('bodytype', 1);
	$tempNameFromGet = Get::val('tn', '');

	switch ($viewType)
	{
		case MESSAGE_VIEW_TYPE_PRINT:

			$GLOBALS['PRINTFILE'] = true;
			if ($mes_uid || $mes_id)
			{
				$message = new GetMessageBase($account, $mes_id, $mes_uid, $folder_id, $folder_name, $mes_charset);
				if ($message && $message->msg)
				{
					$isNull = false;
				}
			}

			if ($isNull)
			{
				exit(PROC_MSG_HAS_DELETED);
			}

			$fromString = $message->PrintFrom(true);
			$toString = $message->PrintTo(true);
			$ccString = $message->PrintCc(true);
			$dateString = $message->PrintDate();
			$subjectString = $message->PrintSubject(true);
			
			$attachString = '';
			if ($message->msg->Attachments && $message->msg->Attachments->Count() > 0)
			{
				$AttachNames = array();
				foreach (array_keys($message->msg->Attachments->Instance()) as $key)
				{
					$attachment =& $message->msg->Attachments->Get($key);
					$fileName = ConvertUtils::ClearFileName(ConvertUtils::ClearUtf8($attachment->GetFilenameFromMime(), $GLOBALS[MailInputCharset], $account->GetUserCharset()));
					$AttachNames[] = $fileName;
					unset($attachment);
				}

				$attachString = implode(', ', $AttachNames);
			}
			$attachString = trim($attachString, ', ');

			$textCharset = $message->msg->GetTextCharset();
			$fullBodyText = ($message->msg->HasHtmlText())
				? ConvertUtils::ReplaceJSMethod($message->PrintHtmlBody(true))
				: $message->PrintPlainBody();


			break;

		case MESSAGE_VIEW_TYPE_FULL:

			$GLOBALS[MIMEConst_DoNotUseMTrim] = true;
			if ($mes_uid || $mes_id)
			{
				$message = new GetMessageBase($account, $mes_id, $mes_uid, $folder_id, $folder_name, $mes_charset);
				if ($message && $message->msg)
				{
					$isNull = false;
				}
			}

			if ($isNull)
			{
				exit(PROC_MSG_HAS_DELETED);
			}

			$fromString = $message->PrintFrom(true);
			$toString = $message->PrintTo(true);
			$ccString = $message->PrintCc(true);
			$dateString = $message->PrintDate();
			$subjectString = $message->PrintSubject(true);
			$attachString = null;

			$textCharset = $message->msg->GetTextCharset();
			$fullBodyText = ($bodytype === 1)
				? ConvertUtils::ReplaceJSMethod($message->PrintHtmlBody(true))
				: $message->PrintPlainBody();

			break;
		
		case MESSAGE_VIEW_TYPE_ATTACH;

			if ($tempNameFromGet)
			{
				$tempFiles =& CTempFiles::CreateInstance($account);
				
				$GLOBALS[MailDefaultCharset] = $account->GetDefaultIncCharset();
				$GLOBALS[MailOutputCharset] = $account->GetUserCharset();

				$message = $messageBody = null;
				if ($tempFiles->IsFileExist($tempNameFromGet))
				{
					$messageBody = $tempFiles->LoadFile($tempNameFromGet);
				}
				else if (isset($_GET['bsi'])) // bodystructure_index
				{
					$processor = new MailProcessor($account);
					$folder = new Folder($account->Id, $folder_id, $folder_name);
					
					$messageBody = $processor->GetBodyPartByIndex($_GET['bsi'], $mes_uid, $folder);
					$encode = 'base64';
					if (isset($_GET['bse']) && strlen($messageBody) > 0)
					{
						$encode = ConvertUtils::GetBodyStructureEncodeString($_GET['bse']);
						$messageBody = ConvertUtils::DecodeBodyByType($messageBody, $encode);
					}
					
					$tempFiles->SaveFile($tempNameFromGet, $messageBody);
				}
				
				if ($messageBody)
				{
					$message = new WebMailMessage();
					$message->LoadMessageFromRawBody($messageBody, true);
				}
				
				if ($message)
				{
					$isNull = false;
				}
			}

			if ($isNull)
			{
				exit(PROC_MSG_HAS_DELETED);
			}

			$fromString = $message->GetFromAsString(true);
			$toString = $message->GetToAsString(true);
			$ccString = $message->GetCcAsString(true);
			$subjectString = $message->GetSubject(true);

			$date =& $message->GetDate();
			$date->FormatString = $account->DefaultDateFormat;
			$date->TimeFormat = $account->DefaultTimeFormat;
			$dateString = $date->GetFormattedDate($account->GetDefaultTimeOffset());
	
			$attachString = '';
			if ($message->Attachments && $message->Attachments->Count() > 0)
			{
				$attachmentsKeys = array_keys($message->Attachments->Instance());
				foreach ($attachmentsKeys as $key)
				{
					$attachment =& $message->Attachments->Get($key);
					$tempName = $key.'_'.ConvertUtils::ClearFileName($attachment->GetTempName());
					$fileName = ConvertUtils::ClearFileName(ConvertUtils::ClearUtf8($attachment->GetFilenameFromMime(), $GLOBALS[MailInputCharset], $account->GetUserCharset()));

					$view = $download = null;
					$size = $tempFiles->SaveFile($tempName, $attachment->GetBinaryBody());
					if ($size > -1)
					{
						$download = 'attach.php?tn='.urlencode($tempName).'&filename='.urlencode($fileName);

						$lowerAttachFileName = strtolower($fileName);
						$contentType = ConvertUtils::GetContentTypeFromFileName($fileName);
						if (substr($lowerAttachFileName, -4) == '.eml')
						{
							$view = 'message-view.php?type='.MESSAGE_VIEW_TYPE_ATTACH.'&tn='.urlencode($tempName);
						}
						else if (false !== strpos($contentType, 'image'))
						{
							$view = 'message-view.php?type='.MESSAGE_VIEW_TYPE_ATTACH.'&tn='.urlencode($tempName);
						}
					}

					$attachString .= ' <a href="'.ConvertUtils::AttributeQuote($download).'">'.$fileName.'</a>';
					$attachString .= (null !== $view) ? ' (<a href="'.ConvertUtils::AttributeQuote($view).'">'.JS_LANG_View.'</a>)' : '';
					$attachString .= ',';

					unset($attachment);
				}
			}

			$attachString = trim($attachString, ', ');

			$textCharset = $message->GetTextCharset();
			$fullBodyText = ($message->HasHtmlText())
				? ConvertUtils::ReplaceJSMethod(PrintHtmlBodyForViewMsgScreen($message, $account, true))
				: nl2br($message->GetCensoredTextBody(true));
				
			break;
	}

	PrintMessagePreview($account->DefaultSkin, $_rtl, $fullBodyText, $textCharset,
		$fromString, $toString, $dateString, $subjectString,
		$attachString, $ccString, $viewType == MESSAGE_VIEW_TYPE_PRINT);
