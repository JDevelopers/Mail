<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 *
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));

	require_once(WM_ROOTPATH.'common/class_log.php');
	require_once(WM_ROOTPATH.'libs/sieve/sieve-php.lib.php');
	
	class CSieveStorage
	{
		const AutoresponderFileName = 'vacation.msg';
		const AutoresponderTextFileName = 'vacation.msg.txt';

		const HOST = '127.0.0.1';
		const PORT = 2000;

		/**
		 * @var string
		 */
		private $_sLogin;

		/**
		 * @var string
		 */
		private $_sPassword;

		/**
		 * @var sieve
		 */
		private $_oSieve;

		/**
		 * @var CLog
		 */
		private $_oLog;

		/**
		 * @param Account $oAccount
		 * @return CSieveStorage
		 */
		public function __construct(Account $oAccount)
		{
			$this->_oLog =& CLog::CreateInstance();
			
			$this->_sLogin = $oAccount->MailIncLogin;
			$this->_sPassword = $oAccount->MailIncPassword;
			$this->_oSieve = null;
		}

		/**
		 * @return bool
		 */
		private function _connect()
		{
			if (null === $this->_oSieve)
			{
				$this->_oSieve = new sieve(self::HOST, self::PORT, $this->_sLogin, $this->_sPassword);
				$this->_oSieve->Log =& $this->_oLog;
			}

			if ($this->_oSieve->sieve_alive())
			{
				return true;
			}
			else
			{
				if ($this->_oSieve->sieve_login())
				{
					@register_shutdown_function(array(&$this->_oSieve, 'sieve_logout'));
					return true;
				}
			}

			return false;
		}

		/**
		 * @param string $sScriptName
		 * @return array | bool
		 */
		public function GetList()
		{
			if ($this->_connect())
			{
				if ($this->_oSieve->sieve_listscripts())
				{
					if (is_array($this->_oSieve->response))
					{
						return $this->_oSieve->response;
					}
					else
					{
						return array();
					}
				}
			}
			return false;
		}

		/**
		 * @param string $sScriptName
		 * @return string | bool
		 */
		public function GetScript($sScriptName)
		{
			if ($this->_connect() && $this->_oSieve->sieve_getscript($sScriptName))
			{
				return is_array($this->_oSieve->response) ? implode($this->_oSieve->response) : false;
			}
			return false;
		}

		/**
		 * @param string $sScriptName
		 * @param string $sScriptText
		 * @return bool
		 */
		public function SetScript($sScriptName, $sScriptText)
		{
			if ($this->_connect())
			{
				return $this->_oSieve->sieve_sendscript($sScriptName, $sScriptText);
			}
			return false;
		}

		/**
		 * @param string $sScriptName
		 * @return bool
		 */
		public function DeleteScript($sScriptName)
		{
			if ($this->_connect())
			{
				return $this->_oSieve->sieve_deletescript($sScriptName);
			}
			return false;
		}

		/**
		 * @return bool
		 */
		public function IsAutoresponderEnabled()
		{
			$aList = $this->GetList();
			return (is_array($aList) && in_array(self::AutoresponderFileName, $aList));
		}

		/**
		 * @return string
		 */
		public function GetAutoresponderText()
		{
			$sReturn = $this->GetScript(self::AutoresponderTextFileName);
			return (false !== $sReturn) ? $sReturn : '';
		}

		/**
		 * @param string $AutoresponderText
		 * @return bool
		 */
		public function SetAutoresponderText($AutoresponderText)
		{
			return $this->SetScript(self::AutoresponderTextFileName, $AutoresponderText);
		}

		/**
		 * @return bool
		 */
		public function DisableAutoresponder()
		{
			return $this->DeleteScript(self::AutoresponderFileName);
		}

		/**
		 * @return bool
		 */
		public function EnableAutoresponder()
		{
			$sScript = '
if ($h_subject: does not contain "SPAM?" and personal) then
mail

##### This is the only thing that a user can set when they      #####
##### decide to enable vacation messaging. The vacation.msg.txt #####

	expand file /usr/mailsuite/data/${domain}/${local_part}/filters/vacation.msg.txt
	once /usr/mailsuite/data/${domain}/${local_part}/vacation.db
	log /usr/mailsuite/data/${domain}/${local_part}/vacation.log

	once_repeat 1d

	to $reply_address
	from $local_part\\\@$domain
	extra_headers "MIME-Version: 1.0;\\\nContent-Type: text/plain; charset=UTF-8;\\\nContent-Transfer-Encoding: 8bit"
	subject "This is autoresponse to: \'$h_subject:\'"

endif
';
			return $this->SetScript(self::AutoresponderFileName, $sScript);
		}
	}