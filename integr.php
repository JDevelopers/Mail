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
	require_once(WM_ROOTPATH.'common/class_account.php');
	require_once(WM_ROOTPATH.'common/class_settings.php');
	require_once(WM_ROOTPATH.'common/class_mailprocessor.php');
	
	if (@file_exists(WM_ROOTPATH.'/lang/English.php'))
	{
		require_once(WM_ROOTPATH.'/lang/English.php');
	}
	else 
	{
		die('Can\'t read English.php file');
	}
	
	define('START_PAGE_IS_MAILBOX', 0);
	define('START_PAGE_IS_NEW_MESSAGE', 1);
	define('START_PAGE_IS_SETTINGS', 2);
	define('START_PAGE_IS_CONTACTS', 3);
	define('START_PAGE_IS_CALENDAR', 4);
	
class CIntegration
{
	/**
	 * @var Account
	 */
	var $Account = null;
	
	/**
	 * @var string
	 */
	var $_webmailroot;
	
	/**
	 * @var string
	 */
	var $_errorMessage = '';
	
	function CIntegration($webmailrootpath = '')
	{
		$this->_webmailroot = (trim($webmailrootpath)) ? rtrim(trim($webmailrootpath), '/\\').'/' : '';
	}
	
	/**
	 * @param string $email
	 * @param string $login
	 * @param int $startPage
	 * @param string $password optional
	 * @return bool
	 */
	function UserLoginByEmail($email, $login, $startPage = START_PAGE_IS_MAILBOX, $password = null, $toEmail = null, $separated = false)
	{
		$newAccount = new Account();
		$settings =& Settings::CreateInstance();
		if (!$settings || !$settings->isLoad) 
		{
			$this->SetError(PROC_CANT_GET_SETTINGS);
			return false;
		}

		$url = 'webmail.php?check=1';
		switch ($startPage)
		{
			default:
				$url .= '&start='.START_PAGE_IS_MAILBOX;
				break;
			case START_PAGE_IS_NEW_MESSAGE:
				$url .= '&start='.START_PAGE_IS_NEW_MESSAGE;

				if ($toEmail && strlen($toEmail) > 0)
				{
					$url .= '&to='.$toEmail;
				}
				break;
			case START_PAGE_IS_MAILBOX:
			case START_PAGE_IS_SETTINGS:
			case START_PAGE_IS_CONTACTS:
				$url .= '&start='.$startPage;
				break;
			case START_PAGE_IS_CALENDAR:
				if ($separated)
				{
					$url = 'calendar.php';
				}
				else
				{
					$url .= '&start='.$startPage;
				}
				break;
		}
		
		$loginArray =& Account::LoadFromDbByLogin($email, $login);
		if ($loginArray != null)
		{
			if ($loginArray[2] == '1')
			{
				if ($password === null)
				{
					$this->SetLoginInfo($loginArray[0], $loginArray[3], null, $separated);
					$this->ChangeLocation($url);
					return true;
				}	
				else if ($password == ConvertUtils::DecodePassword($loginArray[1], $newAccount))
				{
					$this->SetLoginInfo($loginArray[0], $loginArray[3], null, $separated);
					$this->ChangeLocation($url);
					return true;
				}
				else
				{
					$account =& Account::LoadFromDb($loginArray[0]);
					$account->MailIncPassword = $password;

					$newprocessor = new MailProcessor($account);
					
					if ($newprocessor->MailStorage->Connect(true))
					{
						if ($account->Update())
						{
							$this->SetLoginInfo($account->Id, $account->IdUser, $account->DefaultLanguage, $separated);
							$this->ChangeLocation($url);
							return true;
						}
						else 
						{
							$this->SetError(getGlobalError());
						}
					}
					else 
					{
						$this->SetError(PROC_WRONG_ACCT_PWD);
					}
				}
			}
			else 
			{
				$this->SetError(PROC_CANT_LOG_NONDEF);
			}
		}
		else 
		{
			$this->SetError(ErrorPOP3IMAP4Auth);
		}

		return false;
	}
	
	
	
	/**
	 * @param string $settings
	 * @param string $getTemp
	 */
	function ChangeLocation($url)
	{
		header('Location: '.$this->_webmailroot.$url);
	}

	function SetLoginInfo($id_account, $id_user, $lang = null, $isSeparated = false)
	{
		@session_write_close();
		@session_name('PHPWEBMAILSESSID');
		@session_start();

		$_SESSION[ACCOUNT_ID] = $id_account;
		$_SESSION[USER_ID] = $id_user;
		if (null !== $lang)
		{
			$_SESSION[SESSION_LANG] = $lang;
		}
		$_SESSION[SEPARATED] = $isSeparated;
	}
	
	/**
	 * @return string
	 */
	function GetErrorString()
	{
		return $this->_errorMessage;
	}
	
	/**
	 * @param string $string
	 */
	function SetError($string = null)
	{
		$this->_errorMessage = ($string) ? $string : getGlobalError();
	}
}
