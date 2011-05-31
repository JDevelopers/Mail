<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	define('USE_LSUB', false);

/**
 * @author Harish Chauhan
 */
class IMAPMAIL
{
	var $host;			/* host like 127.0.0.1 or mail.yoursite.com */
	var $port;			/* port default is 110 or 143 */
	var $user;			/* user for logon */
	var $password;		/* user paswword */
	var $state;			/* variable define diffrent state of connection */
	var $connection;	/* handle to a open connection */
	var $error;			/* error string */
	var $must_update;
	var $tag;
	var $mail_box;
	var $response_text;
	
	var $_oLog = null;
	var $_bLogEnable = false;

	var $_capas = null;
	
	function IMAPMAIL()
	{
		$this->host = null;
		$this->port = 143;
		$this->user = '';
		$this->password = '';
		$this->state = 'DISCONNECTED';
		$this->connection = null;
		$this->error = '';
		$this->must_update = false;
		$this->UpdateTag();
		
		$this->_oLog = true;
		$this->_bLogEnable = true;
	}
	
	/**
	 * @param	string	$_string
	 */
	function _log($_string)
	{
		if (null !== $this->_oLog && $this->_bLogEnable)
		{
			CAdminPanel::Log($_string);
		}
	}
	
	/**
	 * This functiuon set the host
	 * @example popmail::set_host("mail.yoursite.com")
	 * 
	 * @param string $host
	 */
	function set_host($host)
	{
		$this->host = $host;
	}
	
	/**
	 * This functiuon set the port
	 * @example popmail::set_port(110)
	 * 
	 * @param int $port
	 */
	function set_port($port)
	{
		$this->port = $port;
	}
	
	/**
	 * This functiuon is to retrive the error of last operation
	 * @example popmail::get_error()
	 * 
	 * @return string
	 */
	function get_error()
	{
		return $this->error;
	}
	
	/**
	 * This functiuon is to retrive the state of connaction
	 *
	 * @return string
	 */
	function get_state()
	{
		return $this->state;
	}

	/**
	 * Function is used to open connection
	 *
	 * @param	string	$host
	 * @param	int		$port
	 * @return	bool
	 */
	function open($host = '', $port = '')
	{
		if (!empty($host))
		{
			$this->host = $host;
		}
		if (!empty($port))
		{
			$this->port = $port;
		}
		
		return $this->open_connection();
	}

	/**
	 * close the active connection
	 *
	 * @return bool
	 */
	function close()
	{
		$this->logout();
		@fclose($this->connection);
		$this->connection = null;
		$this->state = 'DISCONNECTED';
		return true;
	}

	/*
	 * The Functions is written bellow is the subordinate functions used in
	 * communication with SERVER.
	 */

	/* This function is used to get response line from server */
	function get_line()
	{
		$return = false;
		if ($this->connection)
		{
			$return = @fgets($this->connection, 512);
			if ($this->_bLogEnable)
			{
				$this->_log('IMAP4 < '.ap_Utils::ShowCRLF($return));
			}

			if (!$return)
			{
				$_socket_status = @socket_get_status($this->connection);
				if (isset($_socket_status['timed_out']) && $_socket_status['timed_out'])
				{
					$this->error = 'Error : Socket timeout reached during IMAP4 connection.';
				}
			}
		}
		
		return $return;
	}
	
	/* This functiuon is to retrive the full response message from server */
	function get_server_responce()
	{	
		$response = array();
		$l = strlen($this->tag);
		while(1)
		{
			$new = $this->get_line();
			if ($new == false)
			{
				break;
			}

			if (substr($new, 0, $l) == $this->tag)
			{
				$response[] = $new;
				break;
			}

			$response[] = $new;
		}
		return trim(implode("\r\n", $response));
	}

	/**
	 * this functiuon is to send the command to server
	 *
	 * @param string $msg
	 * @return bool
	 */
	function put_line($msg = '')
	{
		if ($this->_bLogEnable)
		{
			$this->_log('IMAP > '.$msg);
		}
		
		if (!@fputs($this->connection, $msg."\r\n"))
		{
			$this->error = 'Error : Could not send user request.';
			return false;
		}
		return true;
	}

	/*
	 * The Functions is written bellow is the main commands defined in IMAP
	 * protocol.
	 */
	
	/**
	 * This functiuon is to open the connection to the server
	 *
	 * @return bool
	 */
	function open_connection()
	{
		if (!$this->_checkState_0('DISCONNECTED')) return false;
		
		if (empty($this->host) || empty($this->port))
		{
			$this->error = 'Error : Either HOST or PORT is undifined!';
			return false;
		}
		
		$host = $this->host;
		$isSsl = ((strlen($host) > 6) && strtolower(substr($host, 0, 6)) == 'ssl://');
		if (function_exists('openssl_open') && ($isSsl || $this->port == 993))
		{
			if (!$isSsl)
			{
				$host = 'ssl://'.$host;
			}
		}
		else 
		{
			if ($isSsl)
			{
				$host = substr($host, 6);
			}
		}
		
		$errno = $errstr = null;
		CAdminPanel::Log('IMAP4 > Start connect to '.$host.':'.$this->port);
		$this->connection = @fsockopen($host, $this->port, $errno, $errstr, 10);
		if (!$this->connection)
		{
			$this->error = 'Could not make a connection to server , Error : '.$errstr.' ('.$errno.')';
			return false;
		}

		/* set socket timeout
         * it is valid for all other functions! */
		@socket_set_timeout($this->connection, 20);
        /* socket_set_blocking($this->connection, true); */
        
		$this->get_line();
		$this->state = 'AUTHORIZATION';
		// $this->InitCapa();
		return true;
	}

	/**
	 * The get_capability function returns a listing of capabilities that the
	 * server supports.
	 * 
	 * @return string|false
	 */
	function get_capability()
	{
		if (!$this->_checkState_0('AUTHORIZATION')) return false;

		$this->UpdateTag();
		if ($this->put_line($this->tag.' CAPABILITY'))
		{
			$response = $this->get_server_responce();
			if ($this->_checkResponse($response))
			{
				return $response;
			}
		}

		return false;
	}

	/**
	 * noop function can be used as a periodic poll for new messages or 
	 * message status updates during a period of inactivity
	 *
	 * @return bool
	 */
	function noop()
	{
		if (!$this->_checkState_0('AUTHORIZATION')) return false;

		$this->UpdateTag();
		if ($this->put_line($this->tag.' NOOP'))
		{
			if ($this->_checkResponse($this->get_server_responce()))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * The logout function informs the server that the client is done with the connection.
	 *
	 * @return bool
	 */
	function logout()
	{
		if (!$this->_checkState_0('AUTHORIZATION')) return false;

		$this->UpdateTag();
		if ($this->put_line($this->tag.' LOGOUT'))
		{
			if ($this->_checkResponse($this->get_server_responce()))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * This function is used to authenticate the user
	 * arguments $auth_str is a authorization String example LOGIN
	 * $ans_str1 and $ans_str2 is a base 64 encoded answer string to server
	 * Example if it authentication type is login then user your userid and password
	 * as ans_str1 and ans_str2
	 * 
	 * @param unknown_type $auth_str
	 * @param unknown_type $ans_str1
	 * @param unknown_type $ans_str2
	 * @return unknown
	 */
	function authenticate($auth_str, $ans_str1 = '', $ans_str2 = '')
	{
		if (!$this->_checkState_1('DISCONNECTED')) return false;
		if (!$this->_checkState_1('AUTHENTICATED')) return false;

		$this->UpdateTag();
		if ($this->put_line($this->tag.' AUTHENTICATE '.$auth_str))
		{
			$response = $this->get_line();
			if (strtok($response, ' ') == '+')
			{
				$ans_str1 = base64_encode($ans_str1);
				$this->put_line($ans_str1);
			}
			else
			{
				$this->error = 'Error : '.$response;
				return false;
			}
			$response = $this->get_line();
			if (strtok($response, ' ') == '+')
			{
				$ans_str2 = base64_encode($ans_str2);
				$this->put_line($ans_str2);
			}
			else
			{
				$this->error = 'Error : '.$response;
				return false;
			}
			$response = $this->get_line();
			if ($this->_checkResponse($response))
			{
				$this->state = 'AUTHENTICATED';
				return $response;
			}
		}

		return false;
	}
	
	/**
	 * This function is used to login into server
	 * $user is a valid username and $pwd is a valid password.
	 *
	 * @param string $user
	 * @param string $pwd
	 * @return bool
	 */
	function login($user, $pwd)
	{
		if (!$this->_checkState_1('DISCONNECTED')) return false;
		if (!$this->_checkState_1('AUTHENTICATED')) return false;

		$this->UpdateTag();
		if ($this->put_line($this->tag.' LOGIN "'.$this->quote($user).'" "'.$this->quote($pwd).'"'))
		{
			if ($this->_checkResponse($this->get_server_responce()))
			{
				$this->state = 'AUTHENTICATED';
				return true;
			}
		}

		return false;
	}
	
	/**
	 * @param string $text
	 * @return int
	 */
	function get_mailbox_count($text)
	{
		$arr = array();
		preg_match_all('/\* (\d+) EXISTS/i', $text, $arr);	
		
		return isset($arr[1][0]) ? (int) $arr[1][0] : 0;
	}
	
	/**
	 * @param string $str
	 * @return string
	 */
	function quote($str)
	{
		return strtr($str, array('"' => '\\"', '\\' => '\\\\'));
	}
	
	/**
	 * This function create a mail box
	 *
	 * @param string $mailbox_name
	 * @return bool
	 */
	function create_mailbox($mailbox_name)
	{
		if (!$this->_checkState_1('AUTHORIZATION')) return false;

		$this->UpdateTag();
		if ($this->put_line($this->tag.' CREATE "'.$mailbox_name.'"'))
		{
			if ($this->_checkResponse($this->get_server_responce()))
			{
				return true;
			}
		}
		
		setGlobalError($this->error);
		return false;
	}

	/**
	 * This function delete exists mail box
	 *
	 * @param string $mailbox_name
	 * @return bool
	 */
	function delete_mailbox($mailbox_name)
	{
		if (!$this->_checkState_1('AUTHORIZATION')) return false;

		$this->UpdateTag();
		if ($this->put_line($this->tag.' DELETE "'.$mailbox_name.'"'))
		{
			if ($this->_checkResponse($this->get_server_responce()))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * The subscribe_mailbox command adds the specified mailbox name to the
	 * server's set of "active" or "subscribed" mailboxes
	 * 
	 * @param string $mailbox_name
	 * @return bool
	 */
	function subscribe_mailbox($mailbox_name)
	{
		if (!$this->_checkState_1('AUTHORIZATION')) return false;

		$this->UpdateTag();
		if ($this->put_line($this->tag.' SUBSCRIBE "'.$mailbox_name.'"'))
		{
			if ($this->_checkResponse($this->get_server_responce()))
			{
				return true;
			}
		}

		return false;
	}
	
	/* 
	 * The subscribe_mailbox command removes the specified mailbox name to the
	 * server's set of "active" or "subscribed" mailboxes 
	 * 
	 * @param string $mailbox_name
	 * @return bool
	 */
	function unsubscribe_mailbox($mailbox_name)
	{
		if (!$this->_checkState_1('AUTHORIZATION')) return false;

		$this->UpdateTag();
		if ($this->put_line($this->tag.' UNSUBSCRIBE "'.$mailbox_name.'"'))
		{
			if ($this->_checkResponse($this->get_server_responce()))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $delimiter
	 * @param string $ref_mail_box = ''
	 * @param string $wild_card = '*'
	 * @param string $_isSub = false
	 * @return array
	 */
	function &_in_list_mailbox(&$delimiter, $ref_mail_box = '', $wild_card = '*', $_isSub = USE_LSUB)
	{
		$false = false;
		if (!$this->_checkState_1('AUTHORIZATION')) return $false;
		
		$return_arr = null;
		$firstDelimiter = $delimiter;
		
		if (trim($ref_mail_box) == '')
		{
			$ref_mail_box = '""';
		}
		
		$_line = ($_isSub) ? 'LSUB' : 'LIST';

		$this->UpdateTag();
		if ($this->put_line($this->tag.' '.$_line.' '.$ref_mail_box.' '.$wild_card))
		{
			$response = $this->get_server_responce();
			if (!$this->_checkResponse($response))
			{
				return $return_arr;
			}
		}
		else
		{
			$this->error = 'Error : Could not send User request.';
			return $return_arr;
		}
		
		$temp_arr = explode("\r\n", $response);
		 
		$lit = null;
		$litString = null;
		$return_arr = array();
		for ($i = 0, $c = count($temp_arr) - 1; $i < $c; $i++)
		{
			$line = $temp_arr[$i];
			if (substr($line, 0, 6) == '* '.$_line)
			{
				$foldersParts = explode(')', $line, 2);
				if (strpos(strtolower($foldersParts[0]), '\noselect') !== false)
				{
					continue;
				}
				
				$parts = explode(' ', $foldersParts[1], 3);
				if (trim($parts[1]) != 'NIL')
				{
					$delimiter = trim($parts[1], '"');
				}
				
				$delimiter = (strlen($delimiter) > 0) ? $delimiter{strlen($delimiter) - 1} : $firstDelimiter;
				
				$name = $parts[2];
				
				if ($name{strlen($name) - 1} == '}' && strpos($name, '{') !== false)
				{
					$start = strpos($name, '"');;
					$startIndex =  strpos($name, '{');
					$endIndex =  strpos($name, '}');
				
					if (($start === false || $start > $startIndex) && $startIndex < $endIndex)
					{
						$lit = substr($name, $startIndex + 1, $endIndex - $startIndex - 1);
						if (is_numeric($lit))
						{
							$lit = (int) $lit;
						}
						else
						{
							$lit = null;
						}
					}
				}
				
				if ($lit === null)
				{
					$name = trim($name, '"'.$delimiter);
					array_push($return_arr, $name);
				}
				else
				{
					$litString = $name;
				}
			}
			else if ($lit > 0)
			{
				$litline = substr($line, 0, $lit);
				$litString = str_replace('{'.$lit.'}', $litline, $litString);
				
				$litString = trim($litString, '"'.$delimiter);
				array_push($return_arr, $litString);
				
				$litString = null;
				$lit = null;
			}
		}
		
		return $return_arr;
	}
	
	/**
	 * 
	 * 	The list_mailbox command gets the specified list of mailbox
	 *
	 *	$ref_mail_box	$wild_card   	Interpretation
	 *	Reference    	Mailbox Name  	Interpretation
	 *	------------  	------------  	--------------
	 *	~smith/Mail/  	foo.*         	~smith/Mail/foo.*
	 *	archive/      	%             	archive/%
	 *	#news.        	comp.mail.*   	#news.comp.mail.*
	 *	~smith/Mail/  	/usr/doc/foo  	/usr/doc/foo
	 *	archive/      	~fred/Mail/*  	~fred/Mail/*
	 * 
	 * @param string $delimiter
	 * @param string $ref_mail_box = ''
	 * @param string $wild_card = '*'
	 * @return array
	 */
	function &list_mailbox(&$delimiter, $ref_mail_box = '', $wild_card = '*')
	{
		$_sub =& $this->_in_list_mailbox($delimiter, $ref_mail_box, $wild_card); 
		return $_sub; 
	}
	
	/**
	 * function is same as list_mailbox rather than it returns active mail box list
	 *
	 * @param string $delimiter
	 * @param string $ref_mail_box = ''
	 * @param string $wild_card = '*'
	 * @return array
	 */
	function &list_subscribed_mailbox(&$delimiter, $ref_mail_box = '', $wild_card = '*')
	{
		$_sub =& $this->_in_list_mailbox($delimiter, $ref_mail_box, $wild_card, true); 
		return $_sub; 
	}

	function InitCapa()
	{
		if (null === $this->_capas)
		{
			$resp = $this->get_capability();
			if ($resp)
			{
				$this->_parseCapability($resp);
			}
		}
	}

	/**
	 * @param string $_str
	 * @return bool
	 */
	function _isSupport($_str)
	{
		return is_array($this->_capas) ? in_array($_str, $this->_capas) : false;
	}

	/**
	 * @return bool
	 */
	function IsUidPlusSupport()
	{
		if (null === $this->_capas)
		{
			$this->InitCapa();
		}

		return $this->_isSupport('UIDPLUS');
	}

	/**
	 * @return bool
	 */
	function IsQuotaSupport()
	{
		if (null === $this->_capas)
		{
			$this->InitCapa();
		}

		return $this->_isSupport('QUOTA');
	}

	/**
	 * @return bool
	 */
	function IsNameSpaceSupport()
	{
		if (null === $this->_capas)
		{
			$this->InitCapa();
		}

		return $this->_isSupport('NAMESPACE');
	}

	/**
	 * @return	string
	 */
	function GetNameSpacePrefix()
	{
		if (!$this->_checkState_1('AUTHORIZATION')) return '';

		$sNameSpacePrefix = '';
		if ($this->IsNameSpaceSupport())
		{
			$this->UpdateTag('NS');
			if ($this->put_line($this->tag.' NAMESPACE'))
			{
				$response = $this->get_server_responce();
				if ($this->_checkResponse($response))
				{
					$a = array();
					if (false !== preg_match_all('/\(\(".*?"\)\)/', $response, $a)
						&& isset($a[0][0]) && is_string($a[0][0]))
					{
						$b = array();
						if (false !== preg_match('/\(\("([^"]*)" "/', $a[0][0], $b) && isset($b[1]))
						{
							$sNameSpacePrefix = $b[1];
						}
					}
				}
			}
		}
		return $sNameSpacePrefix;
	}

	/**
	 * @param string $str
	 */
	function _parseCapability($str)
	{
		$this->_capas = array();
		$capasLineArray = explode("\n", $str);
		foreach ($capasLineArray as $capasLine)
		{
			$capa = strtoupper(trim($capasLine));
			if (substr($capa, 0, 12) == '* CAPABILITY')
			{
				$capa = substr($capa, 12);
				$cArray = explode(' ', $capa);

				foreach ($cArray as $c)
				{
					if (strlen($c) > 0)
					{
						$this->_capas[] = $c;
					}
				}
			}
		}
	}

	/**
	 * @return int | false
	 */
	function get_quota()
	{
		if ($this->IsQuotaSupport())
		{
			$this->UpdateTag();
			if ($this->put_line($this->tag.' GETQUOTAROOT "INBOX"'))
			{
				$response = $this->get_server_responce();
				if (!$this->_checkResponse($response))
				{
					return false;
				}

				if (preg_match('/STORAGE (\d+) (\d+)/i', $response, $match) && count($match) > 2)
				{
					return $match[2] * 1024;
				}
			}
		}

		return false;
	}
	
	/**
	 * @param string $response
	 * @return string
	 */
	function _checkResponse($response)
	{
		if (substr($response, strpos($response, $this->tag.' ') + strlen($this->tag) + 1, 2) != 'OK')
		{
			if (trim($response) == '')
			{ 
				$this->error = 'Error : Null response';
			}
			else 
			{
				$this->error = 'Error response: '.$response;
			}
			
			return false;
		}
		return true;
	}
	
	/**
	 * @param string $_state
	 * @return bool
	 */
	function _checkState_0($_state)
	{
		if ($this->state != $_state)
		{
			$this->error = $this->_getState_0_Error($_state);
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param string $_state
	 * @return bool
	 */
	function _checkState_1($_state)
	{
		if ($this->state == $_state)
		{
			$this->error = $this->_getState_1_Error($_state);
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param string $_state
	 * @return string
	 */
	function _getState_0_Error($_state)
	{
		switch ($_state)
		{
			default:				return 'Error : Unknown state.';
			case 'SELECTED':		return 'Error : No mail box is selected.';
			case 'DISCONNECTED':	return 'Error : Already Connected!';
			case 'AUTHORIZATION':	return 'Error : No Connection Found!';
		}
	}
	
	/**
	 * @param string $_state
	 * @return string
	 */
	function _getState_1_Error($_state)
	{
		switch ($_state)
		{
			default:				return 'Error : Unknown state.';
			case 'DISCONNECTED':	return 'Error : No Connection Found!';
			case 'AUTHENTICATED':	return 'Error : Already Authenticated!';
			case 'AUTHORIZATION':	return 'Error : User is not authorised or logged in!';
		}
	}

	/**
	 * @param string $_prefix = null
	 * @return string
	 */
	function GetTag($_prefix = null)
	{
		if (null === $_prefix)
		{
			$_prefix = 'ADM';
		}

		return $_prefix.rand(1000, 9999);
	}

	/**
	 * @param string $_prefix = null
	 */
	function UpdateTag($_prefix = null)
	{
		$this->tag = $this->GetTag($_prefix);
	}
}