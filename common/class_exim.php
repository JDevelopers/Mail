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

class CExim
{
	/**
	 * @param	string	$login
	 * @param	string	$domain
	 * @param	int		$quota
	 * @return	bool
	 */
	static public function CreateUserShell($login, $domain, $quota)
	{
		$loginArr = explode('@', $login, 2);
		$file = '/usr/mailsuite/scripts/maildirmake.sh';
		$cmd = trim($domain.' '.$login.' '.$quota);
		return self::ExecCmd($file, $cmd, __FUNCTION__);;
	}

	/**
	 * @param	string	$login
	 * @param	string	$domain
	 * @return	bool
	 */
	static public function DeleteUserShell($login, $domain)
	{
		$loginArr = explode('@', $login, 2);
		$file = '/usr/mailsuite/scripts/maildirdel.sh';
		$cmd = trim($domain.' '.$loginArr[0]);
		return self::ExecCmd($file, $cmd, __FUNCTION__);;
	}

	/**
	 * @param	string	$login
	 * @param	string	$domain
	 * @return	bool
	 */
	static public function DisableAutoresponder($login, $domain)
	{
		$loginArr = explode('@', $login, 2);
		$file = '/usr/mailsuite/scripts/autoresponder.sh';
		$cmd = trim($domain.' '.$loginArr[0].' 0');
		return self::ExecCmd($file, $cmd, __FUNCTION__);;
	}

	/**
	 * @param	string	$login
	 * @param	string	$domain
	 * @return	bool
	 */
	static public function EnableAutoresponder($login, $domain)
	{
		$loginArr = explode('@', $login, 2);
		$file = '/usr/mailsuite/scripts/autoresponder.sh';
		$cmd = trim($domain.' '.$loginArr[0].' 1');
		return self::ExecCmd($file, $cmd, __FUNCTION__);;
	}

	static private function ExecCmd($file, $cmd, $functionName)
	{
		$log =& CLog::CreateInstance();

		$log->WriteLine('Exim: '.$functionName.' / exec(\''.$file.' '.$cmd.'\');');
		if (@file_exists($file))
		{
			$output = array();
			@exec($file.' '.$cmd, $output);
			if (is_array($output) && count($output) > 0)
			{
				$log->WriteLine('Exim: '.$functionName.' / $output = '.print_r($output, true));
			}
		}
		else
		{
			$log->WriteLine('Exim [error]: '.$file.' not exist!');
			return false;
		}

		return true;
	}
}