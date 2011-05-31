<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	class Pop3Storage extends MailServerStorage
	{
		/**
		 * @access private
		 * @var CPOP3
		 */
		var $_pop3Mail;
		
		/**
		 * @param Account $account
		 * @param string $pathToClassFolder
		 * @return Pop3Storage
		 */
		function Pop3Storage(&$account, $pathToClassFolder)
		{
			MailServerStorage::MailServerStorage($account);
			require_once($pathToClassFolder.'libs/class_pop3.php');
			$this->_pop3Mail = new CPOP3();
		}
		
		/**
		 * @return bool
		 */
		function Connect()
		{
			if ($this->_pop3Mail->socket != false)
			{
				return true;
			}
			
			if (!$this->_pop3Mail->connect($this->_account->MailIncHost, $this->_account->MailIncPort))
			{
				/* $this->SetError($this->_pop3Mail->error); */
				$this->SetError(ap_Utils::TakePhrase('WM_ERROR_POP3_CONNECT'));
				return false;
			}
			else
			{
				register_shutdown_function(array(&$this, 'Disconnect'));
			}
			
			if (!$this->_pop3Mail->login($this->_account->MailIncLogin, $this->_account->MailIncPassword))
			{
				/* $this->SetError($this->_pop3Mail->error); */
				$this->SetError(ap_Utils::TakePhrase('WM_ERROR_POP3IMAP4AUTH'));
				return false;				
			}
			
			return true;
		}
		
		/**
		 * @return bool
		 */
		function Disconnect()
		{
			if (!$this->_pop3Mail->socket)
			{
				return true;
			}
			return $this->_pop3Mail->close();
		}
	}