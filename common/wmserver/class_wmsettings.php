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

	define('WEBMAILCONFIGTAB', 'wm.tab');

class WMSettings
{
	/**
	 * @access public
	 * @var string
	 */
	var $Host = '127.0.0.1';
	
	/**
	 * @access public
	 * @var int
	 */
	var $AdminPort;
	
	/**
	 * @access public
	 * @var string
	 */
	var $AdminLogin;
	
	/**
	 * @access public
	 * @var string
	 */
	var $AdminPassword;
	
	/**
	 * @access public
	 * @var int
	 */
	var $OutPort = 25;
	
	/**
	 * @access public
	 * @var Settings
	 */
	var $Settings;
	
	/**
	 * @access public
	 * @var bool
	 */
	var $IsLoad = false;
	
	/**
	 * @access private
	 * @return WMSettings
	 */
	function WMSettings($param = true)
	{
	 	if (!is_null($param))
	    {
	    	die('error: WMSettings::CreateInstance()');
	    }
		
		$this->Settings =& Settings::CreateInstance();
		$this->IsLoad = $this->Settings->isLoad;

		$this->Host = $this->Settings->WmServerHost;
		   
		$this->IsLoad &= $this->_parse($this->Settings->WmServerRootPath);
	}
	
	/**
	 * @static
	 * @access public
	 * @return WMSettings
	 */
	function &CreateInstance()
	{
		static $instance;
    	if (!is_object($instance))
    	{
			$instance = new WMSettings(null);
    	}
    	return $instance;
	}

	/**
	 * @access private
	 * @param string $mailRootpath
	 * @return bool
	 */
	function _parse($mailRootpath)
	{
		$result = true;
		$wmtab = $mailRootpath.'/'.WEBMAILCONFIGTAB;
		$ctrtab = $mailRootpath.'/ctrlaccounts.tab';
		
		if (@file_exists($wmtab))
		{
			$file = @file($wmtab);
			if ($file && count($file) > 0)
			{
				foreach ($file as $fileLine)
				{
					$fileLine = trim($fileLine);
					if (strlen($fileLine) == 0 || $fileLine{0} == '#')
					{
						continue;
					}
					
					$array = explode("\t", trim($fileLine));
					if (is_array($array) && count($array) > 1)
					{
						$name = trim($array[0], '"');
						$value = trim($array[1], '"');
						
						switch ($name)
						{
							case 'CtrlPort':
								$this->AdminPort = (int) $value;
								break;
							case 'SmtpPort':
								$this->OutPort = (int) $value;
								break;
						}
					}
				}
			}
			else
			{
				$result = false;
				setGlobalError('Empty file: '.$wmtab);
			}
		}
		else
		{
			$result = false;
			setGlobalError('Can\'t find file: '.$wmtab);
		}
		
		if ($result)
		{
			if (@file_exists($ctrtab))
			{
				$file = @file($ctrtab);
				if ($file && count($file) > 0)
				{
					foreach ($file as $fileLine)
					{
						$fileLine = trim($fileLine);
						if (strlen($fileLine) == 0 || $fileLine{0} == '#')
						{
							continue;
						}
						
						$array = explode("\t", trim($fileLine));
						if (is_array($array) && count($array) == 2)
						{
							$this->AdminLogin = trim($array[0], '"');
							$this->AdminPassword = ConvertUtils::WmServerDeCrypt(trim($array[1], '"'));
							break;
						}
					}
				}
				else
				{
					$result = false;
					setGlobalError('Empty file: '.$ctrtab);
				}
			}
			else
			{
				$result = false;
				setGlobalError('Can\'t find file: '.$ctrtab);
			}
		}
		
		return $result;
	}
}