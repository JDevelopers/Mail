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
	
	require_once WM_ROOTPATH.'common/class_account.php';
	require_once WM_ROOTPATH.'common/class_folders.php';
	require_once WM_ROOTPATH.'common/class_mailprocessor.php';
	require_once WM_ROOTPATH.'common/class_webmailmessages.php';
	require_once WM_ROOTPATH.'common/class_tempfiles.php';
	
	function setContentLength($data) 
	{
		header('Content-Length: '.strlen($data));
		return $data;
	}
	
	@ob_start('setContentLength');

	if (!isset($_SESSION[ACCOUNT_ID]))
	{
		exit();
	}

	$_settings =& Settings::CreateInstance();
	if (!$_settings || !$_settings->isLoad || !$_settings->IncludeLang())
	{
		exit();
	}
	
	$account =& Account::LoadFromDb($_SESSION[ACCOUNT_ID]);
	if (!$account)
	{
		exit();
	}
	
	$data = '';
	if (isset($_GET['msg_id'], $_GET['msg_uid'], $_GET['folder_id'], $_GET['folder_fname']))
	{
		$folder = new Folder($_SESSION[ACCOUNT_ID], $_GET['folder_id'], $_GET['folder_fname']);
		
		$dbStorage =& DbStorageCreator::CreateDatabaseStorage($account);
		if (USE_DB && $dbStorage->Connect())
		{
			$dbStorage->GetFolderInfo($folder);
		}
		else if (!USE_DB)
		{
			$folder->SyncType = FOLDERSYNC_DirectMode;
		}
		
		$processor = new MailProcessor($account);

		if (isset($_GET['bsi'], $_GET['tn'])) // bodystructure_index
		{
			$tempName = ConvertUtils::ClearFileName($_GET['tn']);
			$tempFiles =& CTempFiles::CreateInstance($account);
			if ($tempFiles->IsFileExist($tempName))
			{
				$data = $tempFiles->LoadFile($tempName);
			}
			else
			{
				$data = $processor->GetBodyPartByIndex($_GET['bsi'], $_GET['msg_uid'], $folder);
				$encode = 'none';
				if (isset($_GET['bse']) && strlen($data) > 0)
				{
					$encode = ConvertUtils::GetBodyStructureEncodeString($_GET['bse']);
					$data = ConvertUtils::DecodeBodyByType($data, $encode);
				}
				
				$tempFiles->SaveFile($tempName, $data);
			}
			
			AddAttachmentHeaders($account->GetUserCharset(), $tempName);
		}
		else
		{
			$message =& $processor->GetMessage($_GET['msg_id'], $_GET['msg_uid'], $folder);
			if (!$message)
			{
				exit();
			}

			$data = $message->TryToGetOriginalMailMessage();
			$fileNameToSave = trim(ConvertUtils::ClearFileName($message->GetSubject()));
			if (empty($fileNameToSave))
			{
				$fileNameToSave = 'message';
			}

			if (ConvertUtils::IsIE())
			{
				$fileNameToSave = rawurlencode($fileNameToSave);
			}
			
			AddFileNameHeaders($account->GetUserCharset(), $fileNameToSave.'.eml');
		}
	}
	else if (isset($_SESSION[ACCOUNT_ID], $_GET['tn']))
	{
		$tempName = ConvertUtils::ClearFileName($_GET['tn']);
		$tempFiles =& CTempFiles::CreateInstance($account);
		$data = $tempFiles->LoadFile($tempName);
		
		AddAttachmentHeaders($account->GetUserCharset(), $tempName);
	}
	else
	{
		exit();
	}

	echo $data;
	
	function AddAttachmentHeaders($userCharset, $tempName)
	{
		if (isset($_GET['filename']))
		{
			$filename = trim(ConvertUtils::ClearFileName(urldecode($_GET['filename'])));
			$filename = (strlen($filename)) > 0 ? $filename : 'attachmentname';
			if (ConvertUtils::IsIE())
			{
				$filename = rawurlencode($filename);
			}

			if (isset($_GET['img']))
			{
				header('Content-Disposition: attachment; filename="'.$filename.'"; charset='.$userCharset);
				header('Content-Type: '.ConvertUtils::GetContentTypeFromFileName($tempName));
			}
			else
			{
				AddFileNameHeaders($userCharset, $filename);
			}
		}
		else
		{
			header('Content-Type: '.ConvertUtils::GetContentTypeFromFileName($tempName));
		}
	}

	function AddFileNameHeaders($userCharset, $filename)
	{
		// IE
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		header('Content-Type: application/octet-stream; charset='.$userCharset);
		header('Content-Type: application/download; charset='.$userCharset);

		header('Accept-Ranges: bytes');
		header('Content-Disposition: attachment; filename="'.$filename.'"; charset='.$userCharset);
		header('Content-Transfer-Encoding: binary');
	}
