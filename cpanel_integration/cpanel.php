<?php

	if (isset($_GET['mode']) && $_GET['mode'] == 'logout')
	{
		header('Location: /webmaillogout.cgi');
		die();
	}

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));

	$userLogin = $_SERVER['REMOTE_USER'];
	$userPassword = $_SERVER['REMOTE_PASSWORD'];
	$userHost = $_SERVER['DOMAIN'];
	
	$arr = explode('@', $userLogin);
	$userEmail = $arr[0].'@'.$userHost;
	
	include WM_ROOTPATH.'api/user/user_manager.php';
	include WM_ROOTPATH.'api/webmail/webmail_manager.php';

	$error == '';
	$return = ApiLogin($userEmail, $userLogin, $userPassword, $error);
	if (false !== $return)
	{
		header('Location: ../'.$return);
	}
	else
	{
		exit($error);
	}
	
	/**
	 * @param	string	$email
	 * @param	string	$login
	 * @param	string	$password
	 * @param	string	&$relError
	 * @return	false|string
	 */
	function ApiLogin($email, $login, $password, &$relError)
	{
		global $userHost; 
		$userManager = new UserManager();
		$webMailManager = new WebMailManager();
		
		if ($userManager->InitManager())
		{
			if ($webMailManager->InitManager())
			{
				if (!$webMailManager->AccountExist($email, $login))
				{
					$userId = $userManager->CreateUser();
					if (false !== $userId)
					{
						$account = new Account();
						$account->IdUser = $userId;
						$account->Email = $email;
						$account->MailIncLogin = $login;
						$account->MailIncPassword = $password;
						$account->DefaultAccount = true;
						
						$account->MailProtocol = MAILPROTOCOL_IMAP4;
						$account->MailIncHost = 'mail.'.$userHost;
						$account->MailIncPort = 143;
						$account->MailOutHost = 'mail.'.$userHost;
						$account->MailOutPort = 25;
						$account->MailOutAuthentication = true;
						
						if ($webMailManager->CreateAccount($account))
						{
							if ($webMailManager->UserLoginByEmail($account->Email, $account->MailIncLogin, $account->MailIncPassword))
							{
								$_SESSION['CPANEL_INTEGRATION'] = true;
								$headerUrl = $webMailManager->GetApplicationBaseUrl();
								if (false !== $headerUrl)
								{
									return $headerUrl;
								}
							}
						}
					}
					else
					{
						$relError = 'Error: '.$userManager->GetLastError();
						return false;
					}
				}
				else
				{
					if ($webMailManager->UserLoginByEmail($email, $login, $password))
					{
						$_SESSION['CPANEL_INTEGRATION'] = true;
						$headerUrl = $webMailManager->GetApplicationBaseUrl();
						if (false !== $headerUrl)
						{
							return $headerUrl;
						}
					}
				}

				$relError = 'Error: '.$webMailManager->GetLastError();
				return false;
			}
			else
			{
				$relError = 'Error: '.$webMailManager->GetLastError();
				return false;
			}
		}
		
		$relError = 'Error: '.$userManager->GetLastError();
		return false;
	}