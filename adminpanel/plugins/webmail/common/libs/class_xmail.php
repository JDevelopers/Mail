<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

class CWmServerConsoleAdm
{
	/**
	 * @var	string
	 */
	var $_admHost;
	
	/**
	 * @var	int
	 */
	var $_admPort;
	
	/**
	 * @var	string
	 */
	var $_admLogin;

	/**
	 * @var	string
	 */
	var $_admPassword;
	
	/**
	 * @var	resourse
	 */
	var $_socket;
	
	/**
	 * @var	array
	 */
	var $_socket_status;
	
	/**
	 * @var	string
	 */
	var $_error;
	
	/**
	 * @var	WMSettings
	 */
	var $WmSettings;
	
	var $_crlf;
	var $_tab;
	
	/**
	 * @param	WebMail_Settings	$settings
	 * @param	string				$host[optional] = null
	 * @return	CWmServerConsoleAdm
	 */
	function CWmServerConsoleAdm($settings, $host = null)
	{	
		if ($settings && $settings->isLoad)
		{
			$this->WmSettings = new WMSettings($settings->WmServerRootPath, $settings->WmServerHost);
			
			$this->_admHost = (null !== $host) ? $host : $this->WmSettings->Host;
			$this->_admPort = $this->WmSettings->AdminPort;
			$this->_admLogin = $this->WmSettings->AdminLogin;
			$this->_admPassword = $this->WmSettings->AdminPassword;
		}

		$this->_crlf = AP_CRLF;
		$this->_tab = AP_TAB;
	}
	
	/**
	 * @access public
	 * @return bool
	 */
	function Connect()
	{
		return $this->OnlyConnect() && $this->AdmLogin();
	}
	
	/**
	 * @access public
	 * @return bool
	 */
	function Disconnect()
	{
		if ($this->_socket == false)
		{
			return true;
		}
		$this->Logout();
		@fclose($this->_socket);
		$this->_socket = false;
		return true;
	}
	
	/**
	 * @access public
	 * @return bool
	 */
	function Logout()
	{
		$this->_write('quit');
		return $this->_checkResponse($this->_readline(), 'Logout()', __LINE__);
	}
	
	/**
	 * @access public
	 * @return bool
	 */
	function OnlyConnect()
	{
		$errstr = '';
		$errno = 0;
		$connect_timeout = 10;
		
		if(!$this->_socket = @fsockopen($this->_admHost, $this->_admPort, $errno, $errstr, $connect_timeout))
		{
			$err = 'Can\'t connect to WebMail Server ('. $this->_admHost.':'. $this->_admPort.').';
			if (strlen($errstr) > 0)
			{
				$err .= $errstr;
			}
			if ($errno > 0)
			{
				$err .= ' ('.$errno.')';
			}
			$this->_setError($err, __LINE__);
			return false;
		}	
		
		if (!$this->_checkResponse($this->_readline(), 'Connect()', __LINE__))
		{
			return false;
		}
		
 		@socket_set_timeout($this->_socket, 10, 0);
		@socket_set_blocking($this->_socket, true);
		
		return true;
	}

	/**
	 * @access public
	 * @return bool
	 */
	function AdmLogin()
	{
		$this->_write($this->_admLogin.$this->_tab.$this->_admPassword);
		return $this->_checkResponse($this->_readline(), 'AdmLogin()', __LINE__);
	}

	/**
	 * @param string $domain
	 * @param string $username
	 * @param int $mbsize
	 * @return bool
	 */
	function ChangeMaxMailBox($domain, $username, $mbsizeInKb) 
	{
		$this->_write("uservarsset\t$domain\t$username\tMaxMBSize\t$mbsizeInKb");
		return $this->_checkResponse($this->_readline(), 'ChangeMaxMailBox()');	
	}

	/**
	 * @access private
	 */
	function _cleanup()
	{
		if (is_resource($this->_socket))
		{
			@socket_set_blocking($this->_socket, false);
			@fclose($this->_socket);
			$this->_socket = false;
		}
	}
	
	/**
	 * @access private
	 * @param string $string
	 * @return bool
	 */
	function _write($string)
	{
		CAdminPanel::Log('XM > '.trim($string));
		if(!@fwrite($this->_socket , $string.$this->_crlf , strlen($string.$this->_crlf)))
		{
			$this->_setError('_write() - Error while send "'.$string.'". Connection closed.', __LINE__);
			$this->_cleanup();
			return false;
		}

		return true;
	}
	
	/**
	 * @access	private
	 * @param	int		$buffer_size[optional] = 512
	 * @return	string
	 */
	function _readline($buffer_size = 512)
	{
		$buffer = @fgets( $this->_socket , $buffer_size );
		CAdminPanel::Log('XM < '.trim($buffer));
		$this->socket_status = @socket_get_status($this->_socket);
		if(isset($this->socket_status["timed_out"]) && $this->socket_status["timed_out"])
		{
			$this->_setError('_readline() - Socket timeout reached.', __LINE__);
			$this->_cleanup();
		    return false;
		}
		$this->socket_status = false;
		
		return $buffer;
	}
	
	/**
	 * @access	private
	 * @param	string	$response
	 * @param	string	$functionName
	 * @param	int		$lineIsNeed[optional] = null
	 * @return	bool
	 */
	function _checkResponse($response, $functionName, $lineIsNeed = null)
	{
		if (strlen($response) < 1) 
		{
			$this->_setError($functionName.' - Error: response is null', $lineIsNeed);
			$this->_cleanup();			
			return false;
		}
		if (substr($response,0,1) == '-' )
		{
			$this->_setError($functionName.' - Error: '.$response, $lineIsNeed);
			$this->_cleanup();	
			return false;
		}
		else if (substr($response,0,1) == '+' )
		{
			return true;
		}
		else 
		{
			$this->_setError($functionName.' - Unknown Error: '.$response, $lineIsNeed);
			$this->_cleanup();
			return false;
		}
	}
	
	/**
	 * @param	string	$errorDesc
	 */
	function _setError($errorDesc)
	{
		$this->_error = ($this->_error) ? $this->_error : $errorDesc;
		CAdminPanel::Log('XM ERROR < '.trim($this->_error));
	}
	
	/**
	 * @return	string
	 */
	function GetError()
	{
		return $this->_error;
	}
}

class WMSettings
{
	/**
	 * @var	string
	 */
	var $Host = '127.0.0.1';
	
	/**
	 * @var	int
	 */
	var $AdminPort;
	
	/**
	 * @var	string
	 */
	var $AdminLogin;
	
	/**
	 * @var	string
	 */
	var $AdminPassword;
	
	/**
	 * @var	int
	 */
	var $OutPort = 25;
	
	/**
	 * @var	bool
	 */
	var $IsLoad = false;
	
	/**
	 * @param	string		$wmServerRootPath
	 * @param	string		$host
	 * @return	WMSettings
	 */
	function WMSettings($wmServerRootPath, $host)
	{
		$this->Host = $host;
		$this->IsLoad = $this->_parse($wmServerRootPath);
	}
	
	/**
	 * @access	private
	 * @param	string		$mailRootpath
	 * @return	bool
	 */
	function _parse($mailRootpath)
	{
		$result = true;
		$wmtab = $mailRootpath.'/'.WM_WEBMAILCONFIGTAB;
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
			}
		}
		else
		{
			$result = false;
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
							$this->AdminPassword = $this->WmServerDeCrypt(trim($array[1], '"'));
							break;
						}
					}
				}
				else
				{
					$result = false;
				}
			}
			else
			{
				$result = false;
			}
		}
		
		return $result;
	}
	
	/**
	 * @param	string	$value
	 * @return	string
	 */
	function WmServerDecrypt($value)
	{
		$return = '';
		$len = strlen($value);
		
		if ($len > 0 && $len % 2 == 0)
		{
			$startIndex = 0;
			while($startIndex < $len)
			{
				$temp = (int) hexdec(substr($value, $startIndex, 2));
				$return .= chr(($temp & 0xFF) ^ 101);
				$startIndex += 2;
			}
		}
		
		return $return;
	}
}