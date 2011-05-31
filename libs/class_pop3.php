<?php

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));

	require_once(WM_ROOTPATH.'common/class_log.php');
	require_once(WM_ROOTPATH.'common/inc_constants.php');

/**
 * @from
 * @author Jointy <bestmischmaker@web.de>
 * @version 1.16 (final)
 * 
 * @changed by AfterLogic
 */
class CPOP3 
{
	/**
	 * @var bool
	 */
    var $socket = false;
    
    /**
     * @var bool
     */
    var $socket_status = false;
    
    /**
     * @var string
     */
    var $socket_timeout = '90,500';

    /**
     * @var string
     */
    var $error = 'No Errors';
    
    /**
     * @var string
     */
    var $state = 'DISCONNECTED';
    
    /**
     * @var string
     */
    var $apop_banner = '';
    
    /**
     * @var bool
     */
    var $apop_detect;

	/**
	 * @access private
	 * @var CLog
	 */
	var $_log;

	/**
	 * @access public
	 * @param bool $apop_detect[optional] = false
	 * @return CPOP3
	 */
    function CPOP3($apop_detect = false)
    {
        $this->apop_detect = $apop_detect;
		$this->_log =& CLog::CreateInstance();
    }

    /**
     * @access private
     */
    function _cleanup()
    {
        $this->state = 'DISCONNECTED';

        if (is_array($this->socket_status)) 
        {
        	$this->socket_status = FALSE;
        }

        if (is_resource($this->socket))
        {
            /* socket_set_blocking($this->socket, false); */
            @fclose($this->socket);
            $this->socket = FALSE;
        }
    }

    /**
     * @access private
     * @param string $string
     * @return bool
     */
    function _logging()
    {
        return true;
    }

    /**
     * - $server ( Server IP or DNS )
     * - $port ( Server port default is "110" )
     * - $timeout ( Connection timeout for connect to server )
     * - $sock_timeout ( Socket timeout for all actions   (10 sec 500 msec) = (10,500))
     * 
     * If all right you get true, when not you get false and on $this->error = msg
     * 
     * @access public
     * @param string $server
     * @param string $port[optional] = 110
     * @param string $timeout[optional] = 20
     * @param string $sock_timeout[optional] = '10,500'
     * @return bool
     */
    function connect($server, $port = 110, $timeout = SOCKET_CONNECT_TIMEOUT , $sock_timeout = SOCKET_FGET_TIMEOUT)
    {
        if($this->socket)
        {
            $this->error = 'POP3 connect() - Error: Connection also avalible!';
            $this->setGlobalErrorAndWriteLog();
            return false;
        }

        if(!trim($server))
        {
            $this->error = 'POP3 connect() - Error: Please give a server address.';
            $this->setGlobalErrorAndWriteLog();
            return false;
        }

        if($port < 1 && $port > 65535 || !trim($port))
        {
            $this->error = 'POP3 connect() - Error: Port not set or out of range (1 - 65535)';
            $this->setGlobalErrorAndWriteLog();
            return false;
        }

        /*
        if(!ereg("([0-9]{2}),([0-9]{3})",$sock_timeout))
        {
            $this->error = "POP3 connect() - Error: Socket Timeout in invalid Format (Right Format xx,xxx \"10,500\")";
			$this->setGlobalErrorAndWriteLog();
            return false;
        }
        */

        if(!$this->_checkstate('connect')) 
        {
        	return false;
        }

		$isSsl = ((strlen($server) > 6) && strtolower(substr($server, 0, 6)) == 'ssl://');
		if (function_exists('openssl_open') && ($isSsl || $port == 995))
		{
			if (!$isSsl)
			{
				$server = 'ssl://'.$server;
			}
		}
		else 
		{
			if ($isSsl)
			{
				$server = substr($server, 6);
			}
		}        
		
        $errstr = '';
		$errno = 0;

		/* custom class */
		wm_Custom::StaticUseMethod('UpdateSocketTimeouts', array(&$timeout, &$sock_timeout));
		
		$this->_log->WriteLine('POP3 : start connect to '.$server.':'.$port);
		$this->socket = @fsockopen($server, $port, $errno, $errstr, $timeout);
        if(!$this->socket)
        {
            $this->error = 'POP3 connect() - Error: Can\'t connect to Server. Error: '.$errno.' -- '.$errstr;
            $this->setGlobalErrorAndWriteLog();
            return false;
        }

        // set socket timeout
        // it is valid for all other functions!
        @socket_set_timeout($this->socket, $sock_timeout);
        /* @socket_set_blocking($this->socket, true); */

        $response = $this->_getnextstring();

        if (substr($response, 0, 1) != '+')
        {
            $this->_cleanup();
            $this->error = 'POP3 connect() - Error: '.$response;
            $this->setGlobalErrorAndWriteLog();
            return false;
        }

        // get the server banner for APOP
        $this->apop_banner = $this->_parse_banner($response);
        $this->state = 'AUTHORIZATION';
        return true;
    }

    /**
     * @access public
     * @param string $user
     * @param string $pass
     * @param bool $apop[optional] = false
     * @return bool
     */
    function login($user, $pass, $apop = false)
    {
        if(!$this->socket)
        {
            $this->error = 'POP3 login() - Error: No connection avalible.';
            $this->setGlobalErrorAndWriteLog();
            $this->_cleanup();
            return false;
        }

        if($this->_checkstate('login'))
		{
	        if($this->apop_detect && $this->apop_banner != '')
	        {
	                $apop = true;
	        }

	        if(!$apop)
	        {
	            if(!$this->_putline('USER '.$user)) 
	            {
	            	return false;
	            }
	
	            $response = $this->_getnextstring();
	            if (substr($response, 0, 1) != '+' )
	            {
	                $this->error = 'POP3 login() - Error: '.$response;
	                $this->setGlobalErrorAndWriteLog();
	                $this->_cleanup();
	                return false;
	            }
	
	            if(!$this->_putline('PASS '.$pass)) 
	            {
	            	return false;
	            }
	            $response = $this->_getnextstring();
	            if (substr($response, 0, 1) != '+' )
	            {
	            	$this->error = 'POP3 login() - Error: '.$response;
	                $this->setGlobalErrorAndWriteLog();
	                $this->_cleanup();
	                return false;
	            }
	            $this->state = 'TRANSACTION';
	            return true;
	        }
	        else
	        {
	        	// check is server banner for APOP command given!
	            if(empty($this->apop_banner))
	            {
	                $this->error = 'POP3 login() (APOP) - Error: No Server Banner -- aborted and close connection';
	                $this->setGlobalErrorAndWriteLog();
	                $this->_cleanup();
	                return false;
	            }
	
	            if(!$this->_putline('APOP '. $user .' '. md5($this->apop_banner.$pass))) 
	            {
	            	return false;
	            }

	            $response = $this->_getnextstring();
	            if(substr($response, 0, 1) != '+' )
	            {
	                $this->error = 'POP3 login() (APOP) - Error: '.$response;
	                $this->setGlobalErrorAndWriteLog();
	                $this->_cleanup();
	                return false;
	            }
	            $this->state = 'TRANSACTION';
	            return true;
	        }
        }
        return false;
    }
    
    /**
     * @access public
     * @param int $msg_number
     * @param int $lines[optional] = 0
     * @return string/bool
     */
    function get_top($msg_number , $lines = 0)
    {
        if(!$this->socket)
        {
            $this->error = 'POP3 get_top() - Error: No connection avalible.';
            $this->setGlobalErrorAndWriteLog();
            return false;
        }

        if(!$this->_checkstate('get_top')) 
        {
        	return false;
        }

        if(!$this->_putline('TOP ' . $msg_number .' '. $lines)) 
        {
        	return false;
        }

        $response = $this->_getnextstring();
        if(substr($response, 0, 3) != '+OK')
        {
            $this->error = 'POP3 get_top() - Error: '.$response;
            $this->setGlobalErrorAndWriteLog();
            return false;
        }

        /* get headers */
        $output = '';
        $response = $this->_getnextstring();
        /* while(!eregi("^\.\r\n",$response)) */
        while (substr($response, 0, 3) != ".\r\n")
        {
       		if (strlen($response) > 1 && substr($response, 0, 2) == '..')
			{
				$response = substr($response, 1);
			}
           	$output .= $response;
            $response = $this->_getnextstring();
        	if ($response === false)
            {
            	break;
            }
        }

        /* get body */ 
        if($lines > 0)
        {
            for ($g = 0; $g < $lines; $g++)
            {
                /* if(eregi("^\.\r\n",$response)) */
            	if (substr($response, 0, 3) == ".\r\n") 
                {
                	break;
                }
            	
                if (strlen($response) > 1 && substr($response, 0, 2) == '..')
				{
					$response = substr($response, 1);
				}
	           	$output .= $response;
                $response = $this->_getnextstring(false);
	            if ($response === false)
	            {
	            	break;
	            }
            }
        }

        $this->_resetTimeOut(true);
        return $output;
    }

    /**
     * @access public
     * @param int $msg_number
     * @param bool $qmailer[optional] = false
     * @return string/bool
     */
    function get_mail($msg_number, $qmailer = false)
    {
        if(!$this->socket)
        {
            $this->error = 'POP3 get_mail() - Error: No connection avalible.';
			$this->setGlobalErrorAndWriteLog();
            return FALSE;
        }

        if(!$this->_checkstate('get_mail')) 
        {
        	return false;
        }

        if(!$this->_putline('RETR '.$msg_number)) 
        {
        	return false;
        }

        $response = $this->_getnextstring();
        if ($qmailer)
		{
			if(substr($response, 0, 1) != '.') 
			{
				$this->error = 'POP3 get_mail() - qmailer Error: '.$response;
				$this->setGlobalErrorAndWriteLog();
				return false;
			}
		}
		else 
		{
			if(substr($response, 0, 3) != '+OK') 
			{
				$this->error = 'POP3 get_mail() - Error: '.$response;
				$this->setGlobalErrorAndWriteLog();
				return false;
			}
		}

        $output = array();
        $response = $this->_getnextstring();
        // while(!ereg("^\.\r\n", $response))
        while(substr($response, 0, 3) != ".\r\n")
        {
			if (substr($response, 0, 2) == '..')
			{
				$response = substr($response, 1);
			}
			
	       	$output[] = $response;
            $response = $this->_getnextstring();
            if ($response === false)
            {
            	$output = array();
            	break;
            }
        }
        $this->_resetTimeOut(true);
        return implode('', $output);
    }

    /**
     * @access private
     * @param string $string
     * @return bool
     */
    function _checkstate($string)
    {
        // check for delete_mail func
        if($string == 'delete_mail' || $string == 'get_office_status' || $string == 'get_mail' || 
        	$string == 'get_top' || $string == 'noop' || $string == 'reset' ||
        	$string == 'uidl' || $string == 'stats')
        {
            $state = 'TRANSACTION';
            if($this->state != $state)
            {
                $this->error = 'POP3 _checkstate('.$string.') - Error: state must be in "'.$state.'" mode! Your state: "'.$this->state.'"!';
                $this->setGlobalErrorAndWriteLog();
                return false;
            }
            return true;
        }

        // check for connect func
        if($string == 'connect')
        {
            $state = 'DISCONNECTED';
            $state_1 = 'UPDATE';
            if($this->state == $state or $this->state == $state_1)
            {
                return true;
            }
            $this->error= 'POP3 _checkstate('.$string.') - Error: state must be in "'.$state.'" or "'.$state_1.'" mode! Your state: "'.$this->state.'"!';
            $this->setGlobalErrorAndWriteLog();
            return false;
        }

        // check for login func
        if($string == 'login')
        {
            $state = 'AUTHORIZATION';
            if($this->state != $state)
            {
                $this->error = 'POP3 _checkstate('.$string.') - Error: state must be in "'.$state.'" mode! Your state: "'.$this->state.'"!';
                $this->setGlobalErrorAndWriteLog();
                return false;
            }
            return true;
        }
        
        $this->error = 'POP3 _checkstate() - Error: Not allowed string given!';
        $this->setGlobalErrorAndWriteLog();
        return false;
    }

    /**
     * @access public
     * @param int $msg_number[optional] = 0
     * @return bool
     */
    function delete_mail($msg_number = 0)
    {
		if(!$this->socket)
		{
			$this->error = 'POP3 delete_mail() - Error: No connection avalible.';
			$this->setGlobalErrorAndWriteLog();
			return false;
		}
		
        if(!$this->_checkstate('delete_mail')) 
        {
        	return false;
        }

        if($msg_number == 0)
        {
            $this->error = 'POP3 delete_mail() - Error: Please give a valid Messagenumber (Number can\'t be "0").';
            $this->setGlobalErrorAndWriteLog();
            return FALSE;
        }
        
        // delete mail
        if(!$this->_putline('DELE '.$msg_number))
        {
        	return false;
        }
        
        $response = $this->_getnextstring();
        if(substr($response, 0, 1) != '+')
        {
           $this->error = 'POP3 delete_mail() - Error: '.$response;
           $this->setGlobalErrorAndWriteLog();
           return false;
        }
        return true;
    }

    /**
     * output an array
     * 
     * Array
     * {
     * 		[count_mails] => 3
     * 		[octets] => 2496
     * 		[2] => Array
     * 			{
     * 				[size] => 832
     * 				[uid] => 617999468
     * 			}
     * 		[2] => Array
     * 			{
     * 				[size] => 9842
     * 				[uid] => 617999616
     * 			}
     * 		[3] => Array
     * 			{
     * 				[size] => 1726
     * 				[uid] => 617999782
     * 			}
     * 
     * 		[error] => No Errors
     * }
     *
     * @access public
     * @return array
     */
    function get_office_status()
    {
        if(!$this->socket)
        {
            $this->error = 'POP3 get_office_status() - Error: No connection avalible.';
            $this->setGlobalErrorAndWriteLog();
            $this->_cleanup();
            return false;
        }

        if(!$this->_checkstate('get_office_status'))
        {
            $this->_cleanup();
            return false;
        }

        if(!$this->_logging('STAT')) 
        {
        	return false;
        }
        if(!$this->_putline('STAT'))
        {
        	return false;
        }

        $response = $this->_getnextstring();
        if(!$this->_logging($response)) 
        {
        	return false;
        }
		if(substr($response, 0, 3) != '+OK')
        {
            $this->error = 'POP3 get_office_status() - Error: '.$response;
            $this->setGlobalErrorAndWriteLog();
            if(!$this->_logging($this->error)) 
            {
            	return false;
            }
            $this->_cleanup();
            return false;
        }
        $response = trim($response);

        /**
         * some server send the STAT string is finished by "."
         * (+OK 3 52422.) - "Yahoo Server"
         */
        $lastdigit = substr($response, -1);
        if(!ereg('(0-9)', $lastdigit))
        {
            $response = substr($response, 0, strlen($response) - 1);
        }
        unset($lastdigit);

        $array = explode(' ', $response);
        $output['count_mails'] = $array[1];
        $output['octets'] = $array[2];
        unset($array);

        $response = '';
        if($output['count_mails'] != '0'){

            if(!$this->_logging('LIST')) 
            {
            	return false;
            }
            if(!$this->_putline('LIST'))
            {
            	return false;
            }
            
            $response = $this->_getnextstring();
            if(!$this->_logging($response)) 
            {
            	return false;
            }
            if(substr($response, 0, 3) != '+OK')
            {
                $this->error = 'POP3 get_office_status() - Error: '.$response;
                $this->setGlobalErrorAndWriteLog();
                $this->_cleanup();
                return false;
            }
            
            // get message number and size
            $response = '';
            for($i = 0; $i < (int) $output['count_mails']; $i++)
            {
                $nr = $i+1;
                $response = trim($this->_getnextstring());
                if(!$this->_logging($response)) 
                {
                	return false;
                }
                $array = explode(' ', $response);
                $output[$nr]['size'] = $array[1];
                $response = '';
                unset($array);
                unset($nr);
            }

            // check is server send "."
            if(trim($this->_getnextstring()) != '.')
            {
                $this->error = 'POP3 get_office_status() - Error: Server does not send "." at the end.';
                $this->setGlobalErrorAndWriteLog();
                $this->_cleanup();
                return FALSE;
            }
            if(!$this->_logging('.')) 
            {
            	return false;
            }

            if(!$this->_logging('UIDL')) 
            {
            	return false;
            }
            if(!$this->_putline('UIDL')) 
            {
            	return false;
            }
            
            $response = $this->_getnextstring();
            if(!$this->_logging($response)) 
            {
            	return false;
            }
            if(substr($response,0,3) != '+OK')
            {
                $this->error = 'POP3 get_office_status() - Error: '.$response;
                $this->setGlobalErrorAndWriteLog();
                $this->_cleanup();
                return false;
            }
            
            // get UID's
            for($i = 0; $i < (int) $output["count_mails"]; $i++){
                $nr = $i + 1;
                $response = trim($this->_getnextstring());
                if(!$this->_logging($response)) 
                {
                	return false;
                }
                $array = explode(' ', $response);
                $output[$nr]['uid'] = $array[1];
                $response = '';
                unset($array);
                unset($nr);
            }

            // check is server send "."
            if(trim($this->_getnextstring()) != '.')
            {
                $this->error = 'POP3 get_office_status() - Error: Server does not send "." at the end.';
                $this->setGlobalErrorAndWriteLog();
                $this->_cleanup();
                return false;
            }
            if(!$this->_logging('.')) 
            {
            	return false;
            }
        }
        return $output;
    }

    /**
     * @access public
     * @return bool
     */
    function noop()
    {
        if(!$this->socket)
        {
            $this->error = 'POP3 noop() - Error: No connection avalible.';
            $this->setGlobalErrorAndWriteLog();
            if(!$this->_logging($this->error)) 
            {
            	return false;
            }
            return false;
        }
        if(!$this->_checkstate('noop')) 
        {
        	return false;
        }

        if(!$this->_logging('NOOP')) 
        {
        	return false;
        }
        if(!$this->_putline('NOOP'))
        {
        	return false;
        }

        $response = $this->_getnextstring();
        if(!$this->_logging($response))
        {
			return false;
        }
        if(substr($response,0,1) != '+')
        {
            $this->error = 'POP3 noop() - Error: '.$response;
            $this->setGlobalErrorAndWriteLog();
            return false;
        }
        return true;
    }
    
    /**
     * @access public
     * @return bool
     */
    function reset()
    {
        if(!$this->socket)
        {
            $this->error = 'POP3 reset() - Error: No connection avalible.';
            $this->setGlobalErrorAndWriteLog();
            if(!$this->_logging($this->error)) 
            {
            	return false;
            }
            return false;
        }

        if(!$this->_checkstate('reset'))
        {
        	return false;
        }
        if(!$this->_logging('RSET')) 
        {
        	return false;
        }
        if(!$this->_putline('RSET')) 
        {
        	return false;
        }
        $response = $this->_getnextstring();
        if(!$this->_logging($response))
        {
        	return false;
        }
        if(substr($response,0,1) != '+')
        {
            $this->error = 'POP3 reset() - Error: '.$response;
            $this->setGlobalErrorAndWriteLog();
            return false;
        }
        return true;
    }
    
    /**
     * get only count of mails and size of maildrop
     *
     * @return array/bool
     */
    function _stats()
    {
        if(!$this->socket)
        {
            $this->error = 'POP3 _stats() - Error: No connection avalible.';
            $this->setGlobalErrorAndWriteLog();
            return false;
        }

        if(!$this->_checkstate('stats')) 
        {
        	return false;
        }
        if(!$this->_putline('STAT')) 
        {
        	return false;
        }

        $response = $this->_getnextstring();
        if(substr($response,0,1) != '+')
        {
            $this->error = 'POP3 _stats() - Error: '.$response;
            $this->setGlobalErrorAndWriteLog();
            return FALSE;
        }
        $response = trim($response);

        $array = explode(' ',$response);
        $output['count_mails'] = $array[1];
        $output['octets'] = $array[2];

        return $output;
    }

    /**
     * @access public
     * @param int $msg_number[optional] = 0
     * @return array/bool
     */
    function uidl($msg_number = 0)
    {
        if(!$this->socket)
        {
            $this->error = 'POP3 uidl() - Error: No connection avalible.';
            $this->setGlobalErrorAndWriteLog();
            return false;
        }

        if(!$this->_checkstate('uidl'))
        {
        	return false;
        }

        if($msg_number == 0)
        {
            // get count of mails
            $mails = $this->_stats();
            if(!$mails) 
            {
            	return false;
            }

            if(!$this->_logging('UIDL')) 
            {
            	return false;
            }
            if(!$this->_putline('UIDL'))
            {
            	return false;
            }

            $response = $this->_getnextstring();
            if(!$this->_logging($response)) 
            {
            	return false;
            }
            if(substr($response, 0, 1) != '+')
            {
				$this->error = 'POP3 uidl() - Error: '.$response;
				$this->setGlobalErrorAndWriteLog();
				return false;
            }

            $output = array();
            for($i = 1, $c = (int) $mails['count_mails']; $i <= $c; $i++)
            {
                $response = $this->_getnextstring();
                if(!$this->_logging($response)) 
                {
                	return false;
                }
                $response = trim($response);
                $array = explode(' ', $response);
                if (count($array) > 1)
                {
                	$output[(int) $array[0]] = $array[1];
                }
            }
            $this->_getnextstring();
            $this->_resetTimeOut(true);
            return $output;
        }
        else
        {
            if(!$this->_logging('UIDL '.$msg_number)) 
            {
            	return false;
            }
            if(!$this->_putline('UIDL '.$msg_number)) 
            {
            	return false;
            }

            $response = $this->_getnextstring();
            if(!$this->_logging($response)) 
            {
            	return false;
            }
            if(substr($response, 0, 1) != '+')
            {
               $this->error = 'POP3 uidl() - Error: '.$response;
               $this->setGlobalErrorAndWriteLog();
               return false;
            }

            $response = trim($response);
            $array = explode(' ',$response);
			if (count($array) > 2)
			{
				$output[(int) $array[1]] = $array[2];
			}
            return $output;
        }
    }

    /**
     * @param int $msg_number[optional] = 0
     * @return array/bool
     */
    function msglist($msg_number = 0)
    {
        if(!$this->socket)
        {
            $this->error = 'POP3 uidl() - Error: No connection avalible.';
            $this->setGlobalErrorAndWriteLog();
            return false;
        }

        if(!$this->_checkstate('uidl')) 
        {
        	return false;
        }

        if($msg_number == 0)
        {
            // get count of mails
            $mails = $this->_stats();
            if(!$mails)
            {
            	return false;
            }
            if(!$this->_logging('LIST'))
            {
            	return false;
            }
            if(!$this->_putline('LIST'))
        	{
            	return false;
            }

            $response = $this->_getnextstring();
            if(!$this->_logging($response))
            {
            	return false;
            }
            if(substr($response, 0, 1) != '+')
            {
               $this->error = 'POP3 uidl() - Error: '.$response;
               $this->setGlobalErrorAndWriteLog();
               return false;
            }

            $output = array();
            for($i = 1, $c = (int) $mails['count_mails']; $i <= $c; $i++)
            {
                $response = $this->_getnextstring();
                if(!$this->_logging($response)) 
                {
                	return false;
                }
                $response = trim($response);
                $array = explode(' ',$response);
                $output[$i] = $array[1];
				if (count($array) > 1)
				{
					$output[(int) $array[0]] = $array[1];
				}
            }
            $this->_getnextstring();
            $this->_resetTimeOut(true);
            return $output;
        }
        else
        {
            if(!$this->_logging('LIST '.$msg_number)) 
            {
            	return false;
            }
            if(!$this->_putline('LIST '.$msg_number))
            {
            	return false;
            }

            $response = $this->_getnextstring();
            if(!$this->_logging($response)) 
            {
            	return false;
            }
            if(substr($response,0,1) != '+')
            {
               $this->error = 'POP3 uidl() - Error: '.$response;
               $this->setGlobalErrorAndWriteLog();
               return FALSE;
            }

            $response = trim($response);
            $array = explode(' ', $response);
			if (count($array) > 2)
			{
				$output[(int) $array[1]] = $array[2];
			}
            return $output;
        }
    }
    
    
    /**
     * close POP3 connection
     *
     * @access public
     * @return bool
     */
    function close()
    {
        if(!$this->_logging('QUIT')) 
        {
        	return false;
        }
        if(!$this->_putline('QUIT'))
        {
        	return false;
        }

        if($this->state == 'AUTHORIZATION')
        {
            $this->state = 'DISCONNECTED';
        }
        elseif($this->state == 'TRANSACTION')
        {
            $this->state = 'UPDATE';
        }

        $response = $this->_getnextstring();
        if(!$this->_logging($response)) 
        {
        	return false;
        }
        if(substr($response,0,1) != '+')
        {
            $this->error = 'POP3 close() - Error: '.$response;
            $this->setGlobalErrorAndWriteLog();
            return false;
        }
        $this->socket = false;
        $this->_cleanup();
        return true;
    }

    /**
     * @access private
     * @param bool $isLog = true
     * @return string
     */
    function _getnextstring($isLog = true)
    {
    	$this->_resetTimeOut();
        $buffer = @fgets($this->socket , 2048);
		if ($isLog && $this->_log->Enabled)
		{
			$this->_log->WriteLine('POP3 <<: '.ConvertUtils::ShowCRLF($buffer));
    	}
        
    	if (false === $buffer)
    	{
	        $this->socket_status = @socket_get_status($this->socket);
	        if (isset($this->socket_status['timed_out']) && $this->socket_status['timed_out'])
	        {
	            $this->_cleanup();
	            $this->error = "Socket timeout reached during POP3 connection.";
	            $this->setGlobalErrorAndWriteLog();
	        }
    	}
        $this->socket_status = false;
        return $buffer;
    }

    /**
     * @access private
     * @param string $string
     * @return bool
     */
    function _putline($string, $isLog = true)
    {
		if ($isLog && $this->_log->Enabled)
		{
			$this->_log->WriteLine('POP3 >>: '.ConvertUtils::ShowCRLF($string));
		}
		
        $line = $string."\r\n";
        
        $this->_resetTimeOut();
        if(!@fwrite($this->socket , $line , strlen($line)))
        {
            $this->error = 'POP3 _putline() - Error while send "'.$string.'". -- Connection closed.';
            $this->setGlobalErrorAndWriteLog();
            $this->_cleanup();
            return false;
        }
        return true;
    }

    /**
     * @access private
     * @param string &$server_text
     * @return string
     */
    function _parse_banner(&$server_text)
    {
		$outside = true;
		$banner = '';
		$length = strlen($server_text);
		for($count = 0; $count < $length; $count++)
		{
			$digit = substr($server_text, $count, 1);
			if($digit != '')
			{
				if(!$outside && $digit != '<' && $digit != '>')
				{
					$banner .= $digit;
					continue;
				}
				if ($digit == '<')
				{
					$outside = false;
				}
				elseif ($digit == '>')
				{
					$outside = true;
				}
			}
		}
		
		$banner = trim($banner);
        if(strlen($banner) != 0)
        {
            return '<'. $banner .'>';
        }
        return '';
	}
	
	function setGlobalErrorAndWriteLog()
	{
		if (strlen($this->error) > 0)
		{
			setGlobalError($this->error);
            $this->_log->WriteLine('POP3 Error: '.$this->error, LOG_LEVEL_ERROR);
		}
	}
	
	/**
	 * @param bool $_force
	 */
	function _resetTimeOut($_force = false)
	{
		static $_staticTime = null;
		
		$_time = time();
		if ($_staticTime < $_time - RESET_TIME_LIMIT_RUN || $_force)
		{
			@set_time_limit(RESET_TIME_LIMIT);
			$_staticTime = $_time;
		}
	}
}
