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
	require_once(WM_ROOTPATH.'common/inc_constants.php');
	require_once(WM_ROOTPATH.'common/class_mailprocessor.php');
	require_once(WM_ROOTPATH.'common/class_settings.php');

	require WM_ROOTPATH.'common/class_session.php';
	defined('CRLF') || define('CRLF', "\r\n");

	$errorDesc = '';
	$globalForders4Update = array();
	
	/**
	 * @global $globalForders4Update
	 * @param int $id
	 * @param string $fullName
	 */
	function AddFolder4Update($id, $fullName)
	{
		global $globalForders4Update;
		$globalForders4Update[$id] = $fullName;
	}
	
	/**
	 * @global $globalForders4Update
	 * @return string
	 */
	function Folders4UpdateToJsArray()
	{
		global $globalForders4Update;
		$return = array();
		if ($globalForders4Update && count($globalForders4Update) > 0)
		{
			foreach ($globalForders4Update as $id => $name) 
			{
				$return[] = '{id: '.((int) $id).', fullName: \''.ConvertUtils::ClearJavaScriptString($name, '\'').'\'}';
			}
		}
		
		return '['.implode(',', $return).']';
	}
	
	$settings =& Settings::CreateInstance();
	if (!$settings || !$settings->isLoad || !$settings->IncludeLang())
	{
		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html>
<head>
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<link rel="shortcut icon" href="favicon.ico" />
</head>
<body onload="parent.CheckEndCheckMailHandler();">
	<script>parent.EndCheckMailHandler("Can't Load Language file");</script>
</body>
</html><?php
		exit();
	}
	
	if (!isset($_SESSION[ACCOUNT_ID]))
	{
		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
		<html>
		<head>
			<meta http-equiv="Content-Script-Type" content="text/javascript" />
			<link rel="shortcut icon" href="favicon.ico" />
		</head>
      	<body onload="parent.CheckEndCheckMailHandler();">
			<script>parent.EndCheckMailHandler("session_error");</script>
		</body>
		</html>
		<?php
		exit();
	}
	
	@ob_start();
	@ob_end_flush();
		
	function myFlush($bAdd = false)
	{
		if ($bAdd)
		{
		//	echo str_repeat('              ', 256);
		}
		
		@ob_flush();
		@flush();
	}

	/**
	 * @param string $folderName
	 * @param int $messageCount
	 */
	function ShowDownloadedMessageNumber($folderName = '', $messageCount = -1) 
	{
		static $msgNumber = 0;
		static $msgTime = 0;

		if ($folderName != '' && $messageCount != -1)
		{
			$msgNumber = 0;
			$msgTime = 0;
			echo '<script>parent.SetCheckingFolderHandler("'.$folderName.'", '.$messageCount.');</script>'.CRLF;
			if ($messageCount == 0)
			{
				echo '<script>parent.SetStateTextHandler(parent.Lang.GettingMsgsNum);</script>'.CRLF;
			}
			myFlush(true);
		}
		else
		{
			$msgNumber++;
			if (time() - $msgTime > 0)
			{
				echo '<script>parent.SetRetrievingMessageHandler('.$msgNumber.');</script>'.CRLF;
				$msgTime = time();
				myFlush(true);
			}
		}
	}
	
	function ShowLoggingToServer() 
	{
		echo '<script>parent.SetStateTextHandler("'.ConvertUtils::ClearJavaScriptString(JS_LANG_LoggingToServer, '"').'");</script>'.CRLF;
		myFlush(true);
	}
	
	function ShowLoggingOffFromServer() 
	{
		echo '<script>parent.SetStateTextHandler("'.ConvertUtils::ClearJavaScriptString(LoggingOffFromServer, '"').'");</script>'.CRLF;
		myFlush(true);
	}
	
	function ShowDeletingMessageNumber($resetCount = false)
	{
		static $msgNumber = 0;
		static $msgTime = 0;
		
		if ($resetCount)
		{
			$msgNumber = 0;
			$msgTime = 0;
		}
		else
		{
			$msgNumber++;
			if (time() - $msgTime > 0)
			{
				echo '<script>parent.SetDeletingMessageHandler('.$msgNumber.');</script>'.CRLF;
				$msgTime = time();
				myFlush(true);
			}
		}
	}
	
	/**
	 * @param string $text
	 */
	function SetError($text)
	{
		$_SESSION[INFORMATION] = $text;
		$_SESSION[ISINFOERROR] = true;	
	}
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html>
<head>
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<link rel="shortcut icon" href="favicon.ico" />
</head>
<body onload="parent.CheckEndCheckMailHandler();">
<?php
		
	$account =& Account::LoadFromDb($_SESSION[ACCOUNT_ID]);
	
	ConvertUtils::SetLimits();
	
	$GLOBALS['useFilters'] = true;
	$type = isset($_POST['Type']) ? (int) $_POST['Type'] : 0;
	if (1 === $type)
	{
		$dbStorage =& DbStorageCreator::CreateDatabaseStorage($account);
		if ($dbStorage->Connect() && USE_DB)
		{
			$accounts =& $dbStorage->SelectAccounts($account->IdUser);
			if ($accounts !== null)
			{
				foreach ($accounts as $acct_id => $acctArray)
				{
					if ($acctArray[5])
					{ 
						$newAcct =& Account::LoadFromDb($acct_id, false, false);

						$seeEmail = $newAcct->Email;
						/* custom class */
						wm_Custom::StaticUseMethod('ChangeAccountEmailToFake', array(&$seeEmail));

						echo '<script>parent.SetCheckingAccountHandler("'.$seeEmail.'");</script>'.CRLF;
						
						myFlush(true);

						ShowLoggingToServer();
						
						$processor = new MailProcessor($newAcct);
			
						$folders =& $processor->GetFolders();
						
						$processor->MailStorage->DownloadedMessagesHandler = 'ShowDownloadedMessageNumber';
							
						if (!$processor->Synchronize($folders))
						{
							$errorDesc .= getGlobalError();
						}
						
						ShowLoggingOffFromServer();
						
						$processor->MailStorage->Disconnect();
						
						unset($newAcct, $folders, $processor);
					}
				}
			}
		}
		
		$errorDesc = trim($errorDesc);
		if (strlen($errorDesc) > 0) 
		{
			SetError($errorDesc);
		}
		
		echo '<script>parent.EndCheckMailHandler();</script>.CRLF';
	}
	else if (2 === $type)
	{
		$processor = new MailProcessor($account);

		$folders =& $processor->GetFolders();

		$processor->MailStorage->DownloadedMessagesHandler = null;
		$processor->MailStorage->UpdateFolderHandler = 'AddFolder4Update';

		$inboxFolder = $folders->GetFolderByType(FOLDERTYPE_Inbox);

		if ($inboxFolder)
		{
			$inboxFolder->SubFolders = null;
			$foldersForInboxSynchronize = new FolderCollection();
			$foldersForInboxSynchronize->Add($inboxFolder);

			if (!$processor->Synchronize($foldersForInboxSynchronize))
			{
				$errorDesc = getGlobalError();
			}

			$processor->MailStorage->Disconnect();
		}
		else
		{
			$errorDesc = '';
		}

		$errorDesc = trim($errorDesc);
		echo '<script>
parent.SetUpdatedFolders('.Folders4UpdateToJsArray().', false);
parent.EndCheckMailHandler("'.ConvertUtils::ClearJavaScriptString($errorDesc, '"').'");
</script>'.CRLF;
	}
	else
	{
		ShowLoggingToServer();

		$processor = new MailProcessor($account);

		$folders =& $processor->GetFolders();
		
		$processor->MailStorage->DownloadedMessagesHandler = 'ShowDownloadedMessageNumber';
		$processor->MailStorage->UpdateFolderHandler = 'AddFolder4Update';
					
		if (!$processor->Synchronize($folders))
		{
			$errorDesc = getGlobalError();
		}
		
		ShowLoggingOffFromServer();
		
		$processor->MailStorage->Disconnect();
		
		$errorDesc = trim($errorDesc);
		echo '<script>
parent.SetUpdatedFolders('.Folders4UpdateToJsArray().');
parent.EndCheckMailHandler("'.ConvertUtils::ClearJavaScriptString($errorDesc, '"').'");
</script>'.CRLF;
	}

	myFlush(true);
?>
</body>
</html>
