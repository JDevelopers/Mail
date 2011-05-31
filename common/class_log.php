<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));
	
	require_once(WM_ROOTPATH.'common/class_settings.php');

	define ('LOG_FORMAT_OPTIONS_NONE', 0);		// Use default formatting.
	define ('LOG_FORMAT_OPTIONS_ADD_DATE', 1);	// Include the current date in the timestamp.
	
	define('LOG_FILENAME', 'log_'.date('Y-m-d').'.txt');
	define('EVENTS_FILENAME', 'events_'.date('Y-m-d').'.txt');
	define('LOG_LINELIMIT', 8000);
	define('LOG_TIMERUN', 200);

	define('LOG_LEVEL_DEBUG', 100);
	define('LOG_LEVEL_WARNING', 50);
	define('LOG_LEVEL_ERROR', 20);
	
	class COldLog
	{
		/**
		 * @access public
		 * @var bool
		 */
		var $Enabled;

		/**
		 * @access public
		 * @var short
		 * @example 
		 *		$this->Format = LOG_FORMAT_OPTIONS_NONE;
		 *		$this->Format = LOG_FORMAT_OPTIONS_ADD_DATE;
		 */
		var $Format = LOG_FORMAT_OPTIONS_ADD_DATE;
		
		/**
		 * @access public
		 * @var string
		 */
		var $LogFilePath;
		
		/**
		 * @access private
		 * @param bool $param[optional] = true
		 * @return CLog
		 */
		function CLog($param = true, $settings = null)
		{
		    if (!is_null($param))
		    {
		    	die(CANT_CALL_CONSTRUCTOR);
		    }		

			if (null === $settings)
			{
				$settings =& Settings::CreateInstance();
			}
			
			$this->Enabled = $settings->EnableLogging;

			$this->LogFilePath = INI_DIR.'/'.LOG_PATH.'/'.LOG_FILENAME;
			if ($this->Enabled && !is_dir(INI_DIR.'/'.LOG_PATH))
			{
				@mkdir(INI_DIR.'/'.LOG_PATH);
			}
		}
		
		/**
		 * @static
		 * @access public
		 * @return CLog
		 */
		function &CreateInstance($settings = null)
		{
			static $instance;
    		if (!is_object($instance))
    		{
				$instance = new CLog(null, $settings);
    		}
    		return $instance;
		}

		/**
		 * @access public
		 * @param string $errorDesc
		 * @param int $line[optional] = ''
		 */
		function WriteLine($errorDesc, $line = '')
		{
			if (!$this->Enabled)
			{
				return;
			}
			
			$this->_writeLine($errorDesc, $line);
		}

		/**
		 * @access private
		 * @param string $errorDesc
		 * @param int $line
		 */
		function _writeLine($errorDesc, $line)
		{
			static $_isFirst = true;
			static $_lastTime;
			
			if ($_isFirst)
			{
				$_ftime = date('d/m/Y H:i:s');
				$_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
				$_isFirst = false;
				$_post = (isset($_POST) && count($_POST) > 0) ? ' [POST('.count($_POST).')]' : '';
				@error_log("\r\n".'['.$_ftime.'] URL '.$_uri.$_post."\r\n", 3, $this->LogFilePath);
				$_server = isset($_SERVER['SERVER_SOFTWARE']) ? ', '.$_SERVER['SERVER_SOFTWARE'] : '';
				@error_log('['.$_ftime.'] INFO > ver.'.WMVERSION.', PHP-'.phpversion().$_server."\r\n", 3, $this->LogFilePath);
				if (strlen($_post) > 0)
				{
					@error_log('['.$_ftime.'] POST > ['.implode(', ', array_keys($_POST))."]\r\n", 3, $this->LogFilePath);
				}
			}
			
			if (LOG_LINELIMIT && strlen($errorDesc) > LOG_LINELIMIT * 2)
			{
				$errorDesc = 
					substr($errorDesc, 0, LOG_LINELIMIT).
					"\r\n ----------- cut ------------ \r\n".
					substr($errorDesc, - (int) ceil(LOG_LINELIMIT/2));
			}
			
			$line = (strlen($line) > 0) ? '[line: '.$line.']' : '';
			$_newTime = CLog::getmicrotime();
			$_newSTime = (int) (CLog::getmicrosec() * 1000);  
			$_tmpTime = (int) (($_newTime - $_lastTime) * 1000);
			$_pref = '';
			if ($_lastTime && $_tmpTime > LOG_TIMERUN)
			{
				$_pref = $_tmpTime.'::';
			}
			$_lastTime = $_newTime;
			@error_log($_pref.'['.date('H:i:s', $_newTime).'.'.$_newSTime.']'.$line.' '.$errorDesc ."\r\n", 3, $this->LogFilePath);
		}
		
		/**
		 * @return float
		 */
		function getmicrotime() 
		{ 
	    	list($usec, $sec) = explode(' ', microtime()); 
	    	return ((float)$usec + (float)$sec); 
		}
		
		/**
		 * @return float
		 */
		function getmicrosec() 
		{ 
	    	$usec = explode(' ', microtime()); 
	    	return isset($usec[0]) ? (float) $usec[0] : -1; 
		}
	}
	
	class CLog
	{
		/**
		 * @var bool
		 */
		var $Enabled;

		/**
		 * @var bool
		 */
		var $LoggingSpecialUsers;

		/**
		 * @var bool
		 */
		var $IsLoggingSpecialUsersOn;

		/**
		 * @var bool
		 */
		var $EnabledEventsLog;

		/**
		 * @var bool
		 */
		var $LogLevel;
		
		/**
		 * @var CLogFileDriver
		 */
		var $_driver;
		
		function CLog($settings = null)
		{
			if (null === $settings)
			{
				$settings =& Settings::CreateInstance();
			}
			
			$this->Enabled = $settings->EnableLogging;
			$this->LoggingSpecialUsers = $settings->LoggingSpecialUsers;
			$this->LoggingSpecialUsers = @file_exists(WM_ROOTPATH.'.LOG');
			$this->IsLoggingSpecialUsersOn = isset($_SESSION['awm_logging_on']);
			$this->EnabledEventsLog = $settings->EnableEventsLogging;
			$this->LogLevel = $settings->LogLevel;
			
			$this->_driver = (false)
				? new CLogDataBaseDriver($settings)
				: new CLogFileDriver($settings);
		}
		
		/**
		 * @param string	$msgDesc
		 * @param int		$logLevel
		 */
		function WriteLine($msgDesc, $logLevel = LOG_LEVEL_DEBUG)
		{
			if ($this->Enabled && $logLevel <= $this->LogLevel)
			{
				if (!$this->LoggingSpecialUsers ||
					($this->LoggingSpecialUsers && $this->IsLoggingSpecialUsersOn))
				{
					$this->_driver->WriteLine($msgDesc);
				}
			}
		}
		
		/**
		 * @param string $eventString
		 * @param Account $account = null
		 */
		function WriteEvent($eventString, $account = null)
		{
			if ($this->EnabledEventsLog)
			{
				if ($account)
				{
					$this->_driver->SetEventPrefixByAccount($account);
				}
				$this->_driver->WriteEvent($eventString);
			}
		}

		/**
		 * @param Account $account
		 */
		function SetEventPrefixByAccount($account)
		{
			$this->_driver->SetEventPrefixByAccount($account);
		}
		
		/**
		 * @return CLog
		 */
		function &CreateInstance($settings = null)
		{
			static $instance;
    		if (!is_object($instance))
    		{
				$instance = new CLog($settings);
    		}
    		return $instance;
		}
	}
	
	class CLogFileDriver extends CLogDriver
	{
		/**
		 * @param Settings $settings
		 * @return CLogFileDriver
		 */
		function CLogFileDriver($settings)
		{
			$this->SetLogPath(INI_DIR.'/'.LOG_PATH.'/'.LOG_FILENAME, INI_DIR.'/'.LOG_PATH.'/'.EVENTS_FILENAME);
			if (!is_dir(INI_DIR.'/'.LOG_PATH))
			{
				@mkdir(INI_DIR.'/'.LOG_PATH);
			}
		}
		
		/**
		 * @param	string	$errorDesc
		 */
		function WriteLine($errorDesc)
		{
			static $_isFirst = true;
			
			if ($_isFirst)
			{
				@error_log("\r\n".$this->GetFirstLineSection()."\r\n", 3, $this->GetLogPath());
				$_isFirst = false;
			}
			
			if (LOG_LINELIMIT && strlen($errorDesc) > LOG_LINELIMIT * 2)
			{
				$errorDesc = 
					substr($errorDesc, 0, LOG_LINELIMIT).
					"\r\n ----------- cut ------------ \r\n".
					substr($errorDesc, - (int) ceil(LOG_LINELIMIT/2));
			}
			
			@error_log($this->GetTimerSection().' '.$errorDesc."\r\n", 3, $this->GetLogPath());
		}

		/**
		 * @param string $eventString
		 */
		function WriteEvent($eventString)
		{
			@error_log($this->GetTime().'['.$this->GetEventPrefix().'] '.$eventString."\r\n", 3, $this->GetEventLogPath());
		}
	}
	
	class CLogDataBaseDriver extends CLogDriver
	{
		/**
		 * @param Settings $settings
		 * @return CLogDataBaseDriver
		 */
		function CLogDataBaseDriver($settings)
		{
			$this->SetLogPath('DataBase::'.LOG_FILENAME, 'DataBase::'.EVENTS_FILENAME);
		}
		
		/**
		 * @param	string	$errorDesc
		 * @param	int		$logLevel
		 */
		function WriteLine($errorDesc, $logLevel)
		{
			
		}

		/**
		 * @param string $eventString
		 */
		function WriteEvent($eventString)
		{
			
		}
	}
	
	class CLogDriver
	{
		/**
		 * @var string
		 */
		var $_logPath;

		/**
		 * @var string
		 */
		var $_eventLogPath;

		/**
		 * @var string
		 */
		var $_eventPrefix;
		
		/**
		 * @return string
		 */
		function GetLogPath()
		{
			return $this->_logPath;
		}

		/**
		 * @param string $logFile
		 * @param string $eventFile
		 */
		function SetLogPath($logFile, $eventFile)
		{
			$this->_logPath = $logFile;
			$this->_eventLogPath = $eventFile;
		}

		/**
		 * @param Account $account
		 */
		function SetEventPrefixByAccount($account)
		{
			$this->_eventPrefix = $account->Email;
			if (USE_DB)
			{
				$this->_eventPrefix .= '('.$account->Id.'/'.$account->IdUser.')';
			}
		}

		/**
		 * @return string
		 */
		function GetEventPrefix()
		{
			return $this->_eventPrefix;
		}

		/**
		 * @return string
		 */
		function GetEventLogPath()
		{
			return $this->_eventLogPath;
		}
		
		/**
		 * @return string
		 */
		function GetTimerSection()
		{
			static $_lastTime = null;
			
			$tmpTime = 0;
			$prefix = '';
			$lastTime = $_lastTime;
			$mTime = $this->getMicroTime();
			$mSecTime = (int) ($this->getMicroSec() * 1000);
			 
			if (null !== $lastTime)
			{
				$tmpTime = (int) (($mTime - $lastTime) * 1000);
				if ($tmpTime > LOG_TIMERUN)
				{
					$prefix = $tmpTime.'::';		
				}
			}
			$_lastTime = $mTime;
			return $prefix.'['.date('H:i:s', $mTime).'.'.$mSecTime.']';
		}

		function GetTime()
		{
			$mTime = $this->getMicroTime();
			$mSecTime = (int) ($this->getMicroSec() * 1000);
			return '['.date('H:i:s', $mTime).'.'.$mSecTime.']';
		}

		/**
		 * @return string
		 */		
		function GetFirstLineSection()
		{
			$line = '['.date('d/m/Y').']'.$this->GetTimerSection()." INFORMATION: \r\n";
			$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'].' ' : '';
			$user_ip = isset($_SERVER['REMOTE_ADDR']) ? '[USER-IP:'.$_SERVER['REMOTE_ADDR'].']' : '';
			$server_ip = isset($_SERVER['SERVER_ADDR']) ? '[SERVER-IP:'.$_SERVER['SERVER_ADDR'].']' : '';
			$getPost = (isset($_POST) && count($_POST) > 0) ? '[POST('.count($_POST).')] ' : '[GET] ';
			$server = isset($_SERVER['SERVER_SOFTWARE']) ? ', '.$_SERVER['SERVER_SOFTWARE'] : '';
			
			$line .= '   > '.$getPost.$uri."\r\n";
			$line .= '   > ver.'.WMVERSION.' '.$server_ip.$user_ip."\r\n";
			$line .= '   > PHP-'.phpversion().$server;
			return $line;
		}
		
		/**
		 * @return float
		 */
		function getMicroTime() 
		{ 
	    	list($usec, $sec) = explode(' ', microtime()); 
	    	return ((float)$usec + (float)$sec); 
		}
		
		/**
		 * @return float
		 */
		function getMicroSec() 
		{ 
	    	$usec = explode(' ', microtime()); 
	    	return isset($usec[0]) ? (float) $usec[0] : -1; 
		}
	}
