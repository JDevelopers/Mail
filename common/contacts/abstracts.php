<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 *
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

	require_once(WM_ROOTPATH.'common/class_settings.php');
	require_once(WM_ROOTPATH.'common/class_account.php');
	require_once(WM_ROOTPATH.'common/class_log.php');
	
abstract class ContactsDriver
{
	/**
	 * @var Account
	 */
	var $_account;

	/**
	 * @var Settings
	 */
	var $_settings;

	/**
	 * @param	Account		$account
	 * @param	Settings	$settings
	 * @return	ContactsDriver
 	 */
	final function ContactsDriver($account, $settings)
	{
		$this->InitMain($account, $settings);
		$this->InitDriver();
	}

	public function InitMain($account, $settings)
	{
		$this->_account = $account;
		$this->_settings = $settings;
	}

	/**
	 * Initialize driver class params
	 */
	abstract public function InitDriver();

	/**
	 * @param string $string
	 */
	public function WriteLog($string, $logLevel = LOG_LEVEL_DEBUG)
	{
		$log =& CLog::CreateInstance();
		$log->WriteLine($string, $logLevel);
	}
}

