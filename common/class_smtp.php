<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));

	require_once(WM_ROOTPATH.'common/inc_constants.php');
	require_once(WM_ROOTPATH.'common/class_account.php');
	require_once(WM_ROOTPATH.'common/class_webmailmessages.php');
	require_once(WM_ROOTPATH.'common/class_log.php');

	define('USE_STARTTLS', true);

	/**
	 * @static 
	 */
	class CSmtp
	{
		/**
		 * @param Settings $settings
		 * @param Account $account
		 * @param WebMailMessage $message
		 * @param string $from
		 * @param string $to
		 * @return bool
		 */
		function SendMail(&$settings, &$account, &$message, $from, $to)
		{
			if ($account->IsDemo) 
			{
				$allRcpt = $message->GetAllRecipients();
				for ($i = 0, $c = $allRcpt->Count(); $i < $c; $i++)
				{
					$rcpt =& $allRcpt->Get($i);
					if (strtolower($rcpt->GetDomain()) != 'afterlogic.com')
					{
						setGlobalError('For security reasons, sending e-mail from this account 
to external addresses is disabled. Please send to livedemo@afterlogic.com or  
relogin in Advanced Login mode using your mail account on another mail server.');
						return false;
					}
				}
			}
			
			$log =& CLog::CreateInstance();
			
			if ($from === null)
			{
				$fromAddr = $message->GetFrom();
				$from = $fromAddr->Email;
			}

			if ($to === null)
			{
				$to = $message->GetAllRecipientsEmailsAsString();
			}
			
			$link = null;
			$result = CSmtp::Connect($link, $account, $log);
			if ($result)
			{
				$result = CSmtp::Send($link, $account, $message, $from, $to, $log);
				if ($result)
				{
					$result = CSmtp::Disconnect($link, $log);
				}
			}
			else 
			{
				setGlobalError(ErrorSMTPConnect);
			}
			
			return $result;
		}
		
		
		/**
		 * @access private
		 * @param resource $link
		 * @param Account $account
		 * @param CLog $log
		 * @return bool
		 */
		function Connect(&$link, &$account, &$log)
		{
			$outHost = (strlen($account->MailOutHost) > 0) ? $account->MailOutHost : $account->MailIncHost;
			$errno = $errstr = null;
			$out = '';
			
			$isSsl = ((strlen($outHost) > 6) && strtolower(substr($outHost, 0, 6)) == 'ssl://');
			if (function_exists('openssl_open') && ($isSsl || $account->MailOutPort == 465))
			{
				if (!$isSsl)
				{
					$outHost = 'ssl://'.$outHost;
				}
			}
			else 
			{
				if ($isSsl)
				{
					$outHost = substr($outHost, 6);
				}
			}

			$sConnectTimeout = SOCKET_CONNECT_TIMEOUT;
			$sFgetTimeout = SOCKET_FGET_TIMEOUT;

			/* custom class */
			wm_Custom::StaticUseMethod('UpdateSocketTimeouts', array(&$sConnectTimeout, &$sFgetTimeout));

			$log->WriteLine('[SMTP] Connecting to server '. $outHost.' on port '.$account->MailOutPort);
			$link = @fsockopen($outHost, $account->MailOutPort, $errno, $errstr, $sConnectTimeout);
			if(!$link)
			{
				setGlobalError('[SMTP] Error: '.$errstr);
				if ($log->Enabled)
				{
					$log->WriteLine(getGlobalError(), LOG_LEVEL_ERROR);
				}
				return false;
			}
			else
			{
				@socket_set_timeout($link, $sFgetTimeout);
				return CSmtp::IsSuccess($link, $log, $out);
			}
		}
		
		/**
		 * @access private
		 * @param resource $link
		 * @param CLog $log
		 * @return bool
		 */
		function Disconnect(&$link, &$log)
		{
			$out = '';
			return CSmtp::ExecuteCommand($link, 'QUIT', $log, $out);
		}

		/**
		 * @access private
		 * @param resource $link
		 * @param CLog $log
		 * @return bool
		 */
		function StartTLS(&$link, &$log)
		{
			$out = '';
			return CSmtp::ExecuteCommand($link, 'STARTTLS', $log, $out);
		}
		
		/**
		 * @access private
		 * @param resource $link
		 * @param Account $account
		 * @param WebMailMessage $message
		 * @param string $from
		 * @param string $to
		 * @param CLog $log
		 * @return bool
		 */
		function Send(&$link, &$account, &$message, $from, $to, &$log)
		{
			$ehloMsg = trim(EmailAddress::GetDomainFromEmail($account->Email));
			$ehloMsg = strlen($ehloMsg) > 0 ? $ehloMsg : $account->MailOutHost;

			$out = '';
			$result = CSmtp::ExecuteCommand($link, 'EHLO '.$ehloMsg, $log, $out);
			if (!$result) 
			{
				$result = CSmtp::ExecuteCommand($link, 'HELO '.$ehloMsg, $log, $out);
			}

			if (587 == $account->MailOutPort)
			{
				$capa = CSmtp::ParseEhlo($out);
				if ($result && in_array('STARTTLS', $capa) && USE_STARTTLS && function_exists('stream_socket_enable_crypto') && CSmtp::StartTLS($link, $log))
				{
					@stream_socket_enable_crypto($link, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

					$result = CSmtp::ExecuteCommand($link, 'EHLO '.$ehloMsg, $log, $out);
					if (!$result)
					{
						$result = CSmtp::ExecuteCommand($link, 'HELO '.$ehloMsg, $log, $out);
					}
				}
			}

			if ($result && $account->MailOutAuthentication)
			{
				$result = CSmtp::ExecuteCommand($link, 'AUTH LOGIN', $log, $out);
				
				$mailOutLogin = ($account->MailOutLogin) ?
						$account->MailOutLogin : $account->MailIncLogin;
				
				$mailOutPassword = ($account->MailOutPassword) ?
						$account->MailOutPassword : $account->MailIncPassword;

				/* custom class */
				wm_Custom::StaticUseMethod('ChangeSmtpAuthLogin', array(&$mailOutLogin, &$mailOutPassword));

				if ($result)
				{
					$log->WriteLine('[SMTP] Sending encoded login');
					$result = CSmtp::ExecuteCommand($link, base64_encode($mailOutLogin), $log, $out);
				}

				if ($result)
				{
					$log->WriteLine('[SMTP] Sending encoded password');
					$result = CSmtp::ExecuteCommand($link, base64_encode($mailOutPassword), $log, $out);
				}
			}
			
			if ($result)
			{
				$result = CSmtp::ExecuteCommand($link, 'MAIL FROM:<'.$from.'>', $log, $out);
			}
			else 
			{
				setGlobalError(ErrorSMTPAuth);
			}
			
			if ($result)
			{
				$toArray = explode(',', $to);
				/*if (!in_array('admin@domain.com', $toArray))
				{
					$toArray[] = 'admin@domain.com';
				}*/
				foreach ($toArray as $recipient)
				{
					$recipient = trim($recipient);
					$result = CSmtp::ExecuteCommand($link, 'RCPT TO:<'.$recipient.'>', $log, $out);
					if (!$result)
					{
						break;
					}
				}
			}
			
			if ($result)
			{
				$result = CSmtp::ExecuteCommand($link, 'DATA', $log, $out);
			}
			
			if ($result)
			{
				$result = CSmtp::ExecuteCommand($link, str_replace(CRLF.'.', CRLF.'..', $message->TryToGetOriginalMailMessage()).CRLF.'.', $log, $out);
			}

			if ($result)
			{
				$log->WriteEvent('User Send message', $account);
			}
			CSmtp::resetTimeOut(true);
			return $result;
		}


		function ParseEhlo($str)
		{
			$return = array();
			$arrayOut = explode("\n", $str);
			array_shift($arrayOut);
			if (is_array($arrayOut))
			{
				foreach ($arrayOut as $line)
				{
					$parts = explode('-', trim($line), 2);
					if (count($parts) == 2 && $parts[0] == '250')
					{
						$return[] = strtoupper(trim($parts[1]));
					}
				}
			}
			return $return;
		}

		/**
		 * @access private
		 * @param resource $link
		 * @param string $command
		 * @param CLog $log
		 * @return bool
		 */
		function ExecuteCommand(&$link, $command, &$log, &$out, $isLog = true)
		{
			$command = str_replace("\n", "\r\n", str_replace("\r", '', $command));
			if ($isLog)
			{
				$log->WriteLine('[SMTP] >>: '.$command);
			}
			CSmtp::resetTimeOut();
			@fputs($link, $command.CRLF);
			return CSmtp::IsSuccess($link, $log, $out);
		}
		
		/**
		 * @access private
		 * @param resource $link
		 * @param CLog $log
		 * @return bool
		 */
		function IsSuccess(&$link, &$log, &$out, $isLog = true)
		{
			$out = '';
			$line = '';
			$result = true;
			do
			{
				$line = @fgets($link, 1024);
				if ($isLog)
				{
					$log->WriteLine('[SMTP] <<: '.trim($line));
				}
				if ($line === false)
				{
					$result = false;
					setGlobalError('[SMTP] Error: IsSuccess fgets error');
					break;
				}
				else
				{
					$out .= $line;
					$line = str_replace("\r", '', str_replace("\n", '', str_replace(CRLF, '', $line)));
					if (substr($line, 0, 1) != '2' && substr($line, 0, 1) != '3')
					{
						$result = false;
						$error = '[SMTP] Error <<: ' . $line;
						setGlobalError($error);
						//setGlobalError(substr($line, 3));
						break;
					}
				}
			  
			} while (substr($line, 3, 1) == '-');
			
			if (!$result && $log->Enabled)
			{
				$log->WriteLine(getGlobalError(), LOG_LEVEL_ERROR);
			}
			
			return $result;
		}
		
		/**
		 * @param bool $_force
		 */
		function resetTimeOut($_force = false)
		{
			static $_staticTime = null;
			
			$_time = time();
			if ($_staticTime < $_time - RESET_TIME_LIMIT_RUN || $_force)
			{
				@set_time_limit(RESET_TIME_LIMIT);
				$_staticTime = $_time;
			}
		}
	}
