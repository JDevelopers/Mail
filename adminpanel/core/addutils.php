<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	class ap_AddUtils
	{
		/**
		 * @return bool
		 */
		function IsWin()
		{
			return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
		}
		
		/**
		 * @param	string	$path
		 * @param	string	$prefix = null
		 * @return	string
		 */
		function GetFullPath($path, $prefix = null)
		{
			if ($prefix !== null && !@is_dir(realpath($path)))
			{
				if (!ap_AddUtils::IsFullPath($path))
				{
					$path = $prefix.'/'.$path;
				}
			}
	
			if (@is_dir($path))
			{
				$path = rtrim(str_replace('\\', '/', realpath($path)), '/');
			}
			
			return $path;
		}
		
		/**
		 * @param	string	$_path
		 * @return	bool
		 */
		function IsFullPath($_path)
		{
			if (strlen($_path) > 0)
			{
				return (($_path{0} == '/' || $_path{0} == '\\') || (strlen($_path) > 1 && ap_AddUtils::IsWin() && $_path{1} == ':'));
			}
			return false;
		}
		
		function InstallLog($_str, $_path)
		{
			static $_isFirst = true;
			
			if ($_isFirst)
			{
				$_date = date('d/m/Y H:i:s');
			}
			else 
			{
				$_date = @date('H:i:s').'.'.((int) (ap_AddUtils::getmicrosec() * 1000));
			}
			
			@error_log((($_isFirst) ? AP_CRLF : '').'['.$_date.'] '.$_str.AP_CRLF, 3, $_path.'/logs/'.AP_INSTALL_LOG_FILE);
		}
		
		function getmicrotime() 
		{
	    	list($usec, $sec) = explode(' ', microtime()); 
	    	return ((float)$usec + (float)$sec); 
		}
	}