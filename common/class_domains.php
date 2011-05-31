<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));

	class CWebMailDomain
	{
		/**
		 * @var	id
		 */
		var $_id = 0;
		
		/**
		 * @var	string
		 */
		var $_name;
		
		/**
		 * @var	int
		 */
		var $_mailProtocol = MAILPROTOCOL_POP3;
		
		/**
		 * @var	string
		 */
		var $_mailIncomingHost;
		
		/**
		 * @var	int
		 */
		var $_mailIncomingPort = 110;
		
		/**
		 * @var	string
		 */
		var $_mailSmtpHost;
		
		/**
		 * @var	int
		 */
		var $_mailSmtpPort = 25;
		
		/**
		 * @var	bool
		 */
		var $_mailSmtpAuth = true;

		/**
		 * @var	bool
		 */
		var $_isInternal = false;

		/**
		 * @var	bool
		 */
		var $_globalAddrBook = false;

		/**
		 * @var	int
		 */
		var $SaveInSent = 0;
		
		/**
		 * @param	string	$name
		 * @param	int		$incProtocol
		 * @param	string	$incHost
		 * @param	int		$incPort
		 * @param	string	$smtpHost
		 * @param	int		$smtpPort
		 * @param	bool	$smtpAuth
		 */
		function Init($name, $incProtocol, $incHost, $incPort, $smtpHost, $smtpPort, $smtpAuth, $isInternal = false, $globalAddrBook = false, $iSaveInSent = 0)
		{
			$this->_name = $name;
			$this->_mailProtocol = (int) $incProtocol;
			$this->_mailIncomingHost = $incHost;
			$this->_mailIncomingPort = ($incPort === null)
				? ($this->_mailProtocol == MAILPROTOCOL_IMAP4) ? 143 : 110 
				: $incPort;
			$this->_mailSmtpHost = $smtpHost;
			$this->_mailSmtpPort = ($incPort !== null) ? (int) $smtpPort : 25;
			$this->_mailSmtpAuth = (bool) $smtpAuth;
			$this->_isInternal = (bool) $isInternal;
			$this->_globalAddrBook = (bool) $globalAddrBook;
			$this->SaveInSent = (int) $iSaveInSent;
		}
		
		/**
		 * @param int $_id
		 */
		function SetId($_id)
		{
			$this->_id = (int) $_id;
		}

		/**
		 * @return bool
		 */
		function IsInternal()
		{
			return $this->_isInternal;
		}
		
		/**
		 * @param	Account		$_account
		 * @param	Settings	$_settings
		 */
		function UpdateAccount(&$_account, $_settings)
		{
			$_account->IdDomain = $this->_id;
			$_account->MailProtocol = $this->_mailProtocol;
			$_account->MailIncHost = $this->_mailIncomingHost;
			$_account->MailIncPort = $this->_mailIncomingPort;
			$_account->MailOutHost = $this->_mailSmtpHost;
			$_account->MailOutPort = $this->_mailSmtpPort;
			$_account->MailOutAuthentication = $this->_mailSmtpAuth;
			$_account->IsInternal = $this->_isInternal;
			$_account->DomainAddressBook = $this->_globalAddrBook;
			$_account->SaveInSent = $this->SaveInSent;
		}
	}