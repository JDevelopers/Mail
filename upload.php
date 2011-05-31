<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/'));
	
	header('Content-type: text/html; charset=utf-8');

	require_once(WM_ROOTPATH.'common/inc_top.php');
	require WM_ROOTPATH.'common/class_session.php';

	if (isset($_POST['flash_upload'], $_POST['PHPWEBMAILSESSID']) && (int) $_POST['flash_upload'] == 1)
	{
		$PHPWEBMAILSESSID = $_POST['PHPWEBMAILSESSID'];
		if (@session_id() != $PHPWEBMAILSESSID)
		{
			@session_write_close();
			@session_id($PHPWEBMAILSESSID);
			@session_start();
		}
	}

	$errorSymbols = array('<', '>');
	$Error_Desc = '';

	define('FILE_UPLOAD_KEY', 'Filedata');
	
	require_once(WM_ROOTPATH.'common/class_settings.php');
	require_once(WM_ROOTPATH.'common/class_account.php');
	require_once(WM_ROOTPATH.'common/class_log.php');
	require_once(WM_ROOTPATH.'common/class_convertutils.php');
	require_once(WM_ROOTPATH.'common/class_tempfiles.php');

	ConvertUtils::SetLimits();
	
	$log =& CLog::CreateInstance();

	@ob_start();
	
	$settings =& Settings::CreateInstance();
	if (!$settings || !$settings->isLoad)
	{
		$Error_Desc = 'Can\'t Load Settings file';
	}
	else if (!$settings->IncludeLang())
	{
		$Error_Desc = 'Can\'t Load Language file';
	}
	else
	{
		$Error_Desc = getGlobalError();	
	}

	$account = null;
	$tempFiles = null;

	if (!isset($_SESSION[ACCOUNT_ID]))
	{
		$Error_Desc = UnknownUploadError;
	}

	if (empty($Error_Desc))
	{
		$account =& Account::LoadFromDb($_SESSION[ACCOUNT_ID]);
		if ($account)
		{
			$tempFiles =& CTempFiles::CreateInstance($account);
		}
		else
		{
			$Error_Desc = UnknownUploadError;
		}
	}

	$filename = '';
	$filesize = 0;

	if (empty($Error_Desc) && $account && $tempFiles)
	{
		if (isset($_FILES[FILE_UPLOAD_KEY]) && is_uploaded_file($_FILES[FILE_UPLOAD_KEY]['tmp_name']))
		{
			$log->WriteLine('#07');
			if ($settings->EnableAttachmentSizeLimit && ($_FILES[FILE_UPLOAD_KEY]['size'] > $settings->AttachmentSizeLimit))
			{
				$Error_Desc = FileLargerAttachment;
			}
			else
			{
				$tempname = basename($_FILES[FILE_UPLOAD_KEY]['tmp_name']);
				$filename = $_FILES[FILE_UPLOAD_KEY]['name'];
				
				$idx = '';
				while ($tempFiles->IsFileExist($idx.$tempname))
				{
					$idx = ($idx === '') ? 1 : (int) $idx + 1;
				}
				
				$tempname = $idx.$tempname;

				if (!$tempFiles->MoveUploadedFile($_FILES[FILE_UPLOAD_KEY]['tmp_name'], $tempname))
				{
					$log->WriteLine('#08');
					switch ($_FILES[FILE_UPLOAD_KEY]['error'])
					{
						case 1:
						case 2:
							$Error_Desc = FileIsTooBig;
							break;
						case 3:
							$Error_Desc = FilePartiallyUploaded;
							break;
						case 4:
							$Error_Desc = NoFileUploaded;
							break;
						case 6:
							$Error_Desc = MissingTempFolder;
							break;
						default:
							$Error_Desc = UnknownUploadError;
							break;
					}
				}
				else
				{
					$log->WriteLine('#09');
					$filesize = $tempFiles->FileSize($tempname);
					if ($filesize === false)
					{
						$Error_Desc = MissingTempFile;	
					}
				}
			}
		}
		else 
		{
			$log->WriteLine('#10');
			$log->WriteLine('$_FILES = '.print_r($_FILES, true));
			$postsize = @ini_get('upload_max_filesize');
			$Error_Desc = ($postsize) ? FileLargerThan.$postsize : FileIsTooBig;
			if (isset($_FILES[FILE_UPLOAD_KEY]) && $_FILES[FILE_UPLOAD_KEY]['size'] > $settings->AttachmentSizeLimit)
			{
				$log->WriteLine('#11');
				$Error_Desc = FileIsTooBig;
			}
		}
	}

	$isFlashupload = (isset($_POST['flash_upload']) && '1' == $_POST['flash_upload']);

	$isInline = false;
	if ($Error_Desc == '')
	{
		$mime = trim($_FILES[FILE_UPLOAD_KEY]['type']);
		if ($mime == 'application/octet-stream')
		{
			$mime = ConvertUtils::GetContentTypeFromFileName($filename);
		}

		$isInline = (isset($_POST['inline_image']) && $_POST['inline_image'] == '1');
		$isInline &= (strpos($mime, 'image') === 0);
	}

	$addStr = ($isInline) ? 'img&' : '';

	if ($isFlashupload)
	{
		$exitStr = '';
		if ($Error_Desc != '')
		{
			$log->WriteLine($Error_Desc, LOG_LEVEL_ERROR);
			$exitStr = 'attachment = { Error: "'.ConvertUtils::ClearJavaScriptString($Error_Desc, '"').'" };';
		}
		else
		{
			$exitStr = 'attachment = { FileName: "'.ConvertUtils::ClearJavaScriptString($filename, '"').
'", TempName: "'.ConvertUtils::ClearJavaScriptString($tempname, '"').
'", Size: '.((int) $filesize).', MimeType: "'.ConvertUtils::ClearJavaScriptString($mime, '"').
'", Inline: '.(($isInline) ? 'true' : 'false').
', Url: "'.ConvertUtils::ClearJavaScriptString('attach.php?'.$addStr.'tn='.$tempname.'&filename='.$filename, '"').'", Error: "" };';
		}

		$log->WriteLine('exit text: ', $exitStr);
		exit($exitStr);
	}

?>
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html>
<head>
	<title></title>
</head>
<body>
<?php
if ($Error_Desc != '')
{
	$log->WriteLine($Error_Desc, LOG_LEVEL_ERROR);
?>
<script type="text/javascript">
	alert("<?php echo ConvertUtils::ClearJavaScriptString($Error_Desc, '"');?>");
</script>
<?php
}
else 
{
	$jsOut = '{
FileName: "'.ConvertUtils::ClearJavaScriptString($filename, '"').'",
TempName: "'.ConvertUtils::ClearJavaScriptString($tempname, '"').'",
Size: '.((int) $filesize).', Inline: '.(($isInline) ? 'true' : 'false').',
MimeType: "'.ConvertUtils::ClearJavaScriptString($mime, '"').'",
Url: "'.ConvertUtils::ClearJavaScriptString('attach.php?'.$addStr.'tn='.$tempname.'&filename='.$filename, '"').'"
}';
	$log->WriteLine($jsOut);
?>
<script type="text/javascript">
	parent.LoadAttachmentHandler(<?php echo $jsOut; ?>);
</script>
<?php
}
?>
</body>
</html>
<?php @ob_end_flush();