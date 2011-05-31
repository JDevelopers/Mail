<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	class ap_Utils
	{
		/**
		 * @param	string	$email
		 * @return	false|array
		 */
		function ParseEmail($email)
		{
			$arr = explode('@', $email);
		
			if(count($arr) == 2 && strlen($arr[0]) && strlen($arr[1]))
			{
				return $arr;
			}
			else 
			{
				return false;
			}
		}
		
		/**
		 * @return	float
		 */
		function Microtime()
		{ 
			list($usec, $sec) = explode(' ', microtime()); 
			return ((float) $usec + (float) $sec); 
		}
		
		function SetLimits()
		{
			/* custom class */
			if (ap_Custom::StaticMethodExist('ap_SetLimits'))
			{
				ap_Custom::StaticUseMethod('ap_SetLimits');
			}
			else 
			{
				@ini_set('memory_limit', AP_MEMORYLIMIT);
				@set_time_limit(AP_TIMELIMIT);
			}
		}

		/**
		 * @param	string	$str
		 * @return	string
		 */
		function AttributeQuote($str)
		{
			/* return str_replace(array('"', '\''), array('&quot;', ), $str); */
			return str_replace('\'', '&#039;', str_replace('"', '&quot;', $str));
		}
		
		/**
		 * @param	string	$_str
		 * @return	string
		 */
		function ShowCRLF($_str)
		{
			if (false === $_str)
			{
				return 'bool:false';
			}
			else if (true === $_str)
			{
				return 'bool:true';
			}
			
			return str_replace(array("\r", "\n", "\t"), array('\r', '\n', '\t'), $_str);
		}
		
		/**
		 * @param	string	$_prefix
		 * @return	string
		 */
		function ClearPrefix($_prefix)
		{
			$_new = preg_replace('/[^a-z0-9_]/i', '_', $_prefix);
			if ($_new !== $_prefix)
			{
				$_new = preg_replace('/[_]+/', '_', $_new);
			}
			return $_new;
		}
		
		/**
		 * @param	string	$_srt
		 * @return	string
		 */
		function CheckDefaultWordsFileName($_srt)
		{
			$words = array('CON', 'AUX', 'COM1', 'COM2', 'COM3', 'COM4', 'LPT1', 'LPT2', 'LPT3', 'PRN', 'NUL');
			foreach ($words as $value)
			{
				if (strtoupper($_srt) == $value)
				{
					return false;
				}
			}
			return true;
		}
		
		/**
		 * @param	string	$strValue
		 * @return	bool
		 */
		function HasSpecSymbols($_srt)
	    {
            return preg_match('/["\/\\\*\?<>\|:]/', $_srt);
	    }
		
		/**
		 * @param	string	$str
		 * @return	string
		 */		
		function EncodeSpecialXmlChars($str)
		{
			return str_replace('>', '&gt;', str_replace('<', '&lt;', str_replace('&', '&amp;', $str)));
		}
		
		/**
		 * @param	string	$str
		 * @return	string
		 */		
		function DecodeSpecialXmlChars($str)
		{
			return str_replace('&amp;', '&', str_replace('&lt;', '<', str_replace('&gt;', '>', $str)));
		}
		
		/**
		 * @param	string	$path
		 * @return	string
		 */
		function PathPreparation($path)
		{
			return str_replace('\\', '/', rtrim(trim($path), '/\\'));
		}
		
		/**
		 * @param	int	$byteSize
		 * @return	string
		 */
		function GetFriendlySize($byteSize)
		{
			$size = ceil($byteSize / 1024);
			$mbSize = $size / 1024;
			$size = ($mbSize > 1) ? (ceil($mbSize*10)/10).'MB' : $size.'KB';
			return $size;
		}
		
		/**
		 * @param	string	$name
		 * @return	string
		 */
		function TakePhrase($name)
		{
			return (defined($name)) ? constant($name) : 'LANG:'.$name;
		}
		
		/**
		 * @param	string $_uid
		 * @return	array
		 */
		function UidExplode($_uid)
		{
			$_return = array('', '');
			$_start = strpos($_uid, AP_TYPE_DELIMITER);
			if ($_start !== false)
			{
				$_return[0] = substr($_uid, 0, $_start);
				$_return[1] = substr($_uid, $_start + strlen(AP_TYPE_DELIMITER));
			}
			return $_return;
		}
		
		/**
		 * @param	string	$jsString
		 * @return	string
		 */
		function ReBuildStringToJavaScript($jsString, $deq = null)
		{
			$jsString = ap_Utils::ClearStringValue($jsString, $deq);
			return str_replace(array("\r", "\n", "\t"), array('\r', '\n', '\t'), trim($jsString));
		}

		function ClearStringValue($string, $deq = null)
		{
			$string = str_replace('\\', '\\\\', $string);
			if ($deq !== null && strlen($deq) == 1)
			{
				$string = str_replace($deq, '\\'.$deq, $string);
			}
			return $string;
		}
		
		/**
		 * @param	string	$str
		 * @param	string	$char
		 * @return	string
		 */
		function MyTrim($str, $char)
		{
			if (strlen($str) > 0 && $str{0} == $char)
			{
				$str = substr($str, 1);
			}
			
			$len = strlen($str);
			if ($len > 0 && $str{$len - 1} == $char)
			{
				$str = substr($str, 0, -1);
			}
			return $str;
		}
		
		/**
		 * @param string $password
		 * @return string
		 */
		function EncodePassword($password)
		{
			if ($password === '' || $password === null)
			{
				return $password;
			}
			
			$plainBytes = $password;
			$encodeByte = $plainBytes{0};
			$result = bin2hex($encodeByte);
			
            for ($i = 1, $icount = strlen($plainBytes); $i < $icount; $i++)
            {
                $plainBytes{$i} = ($plainBytes{$i} ^ $encodeByte);
                $result .= bin2hex($plainBytes{$i});
            }
            
            return $result;
		}
		
		/**
		 * @param string $password
		 * @return string
		 */
		function DecodePassword($password)
		{
			$passwordLen = strlen($password);
		
			if (strlen($password) > 0 && strlen($password) % 2 == 0)
			{
				$decodeByte = chr(hexdec(substr($password, 0, 2)));
				$plainBytes = $decodeByte;
				$startIndex = 2;
				$currentByte = 1;
	
				do
				//while ($startIndex < $passwordLen)
				{
					$hexByte = substr($password, $startIndex, 2);
					$plainBytes .= (chr(hexdec($hexByte)) ^ $decodeByte);
					
					$startIndex += 2;
					$currentByte++;
				}
				while ($startIndex < $passwordLen);

				return $plainBytes;
			}
			
			return '';
		}
		
		/**
		 * @param string $strFileName
		 * @return bool
		 */
		function CheckFileName($strFileName)
		{
			if (strpos($strFileName, '"') !== false)
			{
				return false;
			}
			elseif (strpos($strFileName, '/') !== false)
			{
				return false;
			}
			elseif (strpos($strFileName, '\\') !== false)
			{
				return false;	
			}
			elseif (strpos($strFileName, '*') !== false)
			{
				return false;	
			}
			elseif (strpos($strFileName, '?') !== false)
			{
				return false;		
			}
			elseif (strpos($strFileName, '<') !== false)
			{
				return false;		
			}
			elseif (strpos($strFileName, '>') !== false)
			{
				return false;	
			}
			elseif (strpos($strFileName, '|') !== false)
			{
				return false;	
			}
			elseif (strpos($strFileName, ':') !== false)
			{
				return false;	
			}			
			return true;
		}
		
		/**
		 * @param String $strEmail
		 * @return bool
		 */
		function checkEmail($strEmail)
		{
			$pattern = '/[A-Z0-9\!#\$%\^\{\}`~&\'\+-=_\.]+@[A-Z0-9\.-]/i';  
			$strEmail = substr(trim($strEmail), 0, 255);
			
			return preg_match($pattern, $strEmail);
		}
		
		/**
		 * @param String $strServerName
		 * @return bool
		 */
		function checkServerName($strServerName)
		{
			return !preg_match('/[^A-Z0-9\.-]/i', substr(trim($strServerName), 0, 255));
		}
		
		/**
		 * @param int $port
		 * @return bool
		 */
		function checkPort($port)
		{
			$port = intval($port);
			return ($port > 0 && $port < 65535);
		}
		
		/**
		 * @param string $strDate
		 * @return int
		 */
		function GetTimeFromString($strDate)
		{
			$matches = array();
			$datePattern = '/^(([a-z]*),[\s]*){0,1}(\d{1,2}).([a-z]*).(\d{2,4})[\s]*(\d{1,2}).(\d{1,2}).(\d{1,2})([\s]+([+-]?\d{1,4}))?([\s]*(\(?(\w+)\)?))?/i';
			if (preg_match($datePattern, $strDate, $matches))
			{
				$year = $matches[5];
				$month = ap_Utils::GetMonthIndex(strtolower($matches[4]));
				if ($month == -1) 
				{
					$month = 1;
				}
				$day = $matches[3];
				$hour = $matches[6];
				$minute = $matches[7];
				$second = $matches[8];
				
				$_dt = ap_Utils::GmtMkTime($hour, $minute, $second, $month, $day, $year);
				
				$zone = null;
				if (isset($matches[13]))
				{
					$zone = strtolower($matches[13]);
				}
				
				$off = null;
				if (isset($matches[10]))
				{
					$off = strtolower($matches[10]);
				}
					
				return ap_Utils::ApplyOffsetForDate($_dt, $off, $zone);
			}
			
			return time();
		}
		
		/**
		 * @param string $month
		 * @return short
		 */
		function GetMonthIndex($month)
		{
			if ($month == 'jan')
			{
				return 1;
			} 
			elseif ($month == 'feb') 
			{
				return 2;
			} 
			elseif ($month == 'mar') 
			{
				return 3;
			} 
			elseif ($month == 'apr') 
			{
				return 4;
			} 
			elseif ($month == 'may')
			{
				return 5;
			} 
			elseif ($month == 'jun')
			{
				return 6;
			}
			elseif ($month == 'jul')
			{
				return 7;
			} 
			elseif ($month == 'aug')
			{
				return 8;
			}
			elseif ($month == 'sep') 
			{
				return 9;
			}
			elseif ($month == 'oct')
			{
				return 10;
			}
			elseif ($month == 'nov')
			{
				return 11;
			} 
			elseif ($month == 'dec')
			{
				return 12;
			} 
			else 
			{
				return -1;
			}
		}
		
		/**
		 * @param int $hour
		 * @param int $minute
		 * @param int $second
		 * @param int $month
		 * @param int $day
		 * @param int $year
		 * @return int
		 */
		function GmtMkTime($hour = -1, $minute = -1, $second = -1, $month = -1, $day = -1, $year = -1)
		{
			if ($hour == -1)
			{
				$hour = date('H');
			}
			if ($minute == -1)
			{
				$minute = date('i');
			}
			if ($second == -1)
			{
				$second = date('s');
			}
			if ($month == -1)
			{
				$month = date('n');
			}
			if ($day == -1)
			{
				$day = date('j');
			}
			if ($year == -1)
			{
				$year = date('Y');
			}
			
			return mktime($hour, $minute, $second, $month, $day, $year);
		}
		
		/**
		 * @param int $dt
		 * @param int $offset
		 * @param string $zone
		 * @return int
		 */
		function ApplyOffsetForDate($dt, $offset, $zone)
		{
			$result = $dt;
			
			if (($offset != null) && (strlen($offset) != 0))
			{
				$offset = trim($offset);
				$sign = $offset{0};
				$offset = substr($offset, 1);
				
				$nOffset = 0;
				
				if (is_numeric($offset))
				{
					$nOffset = (int) $offset;
				}
				
				$hours = $nOffset / 100;
				$minutes = $nOffset % 100;
				$multiplier = 1;
				if($sign == '-')
				{
					$multiplier = -1;
				}
				
				$result -= $multiplier*$hours*60*60;
				$result -= $multiplier*$minutes*60;
			}
			elseif (($zone != null) && (strlen($zone) != 0))
			{
				$zone = trim($zone, ' ()');
				
				switch($zone)
				{
					case 'ut':
					case 'gmt':
					case 'z':
					{
						break;
					}
					case 'est':
					case 'cdt':
					{
						$dt -= 5*60*60;
						break;
					}
					case 'edt':
					{
						$dt -= 4*60*60;
						break;
					}
					case 'cst':
					case 'mdt':
					{
						$dt -= 6*60*60;
						break;
					}
					case 'mst':
					case 'pdt':
					{
						$dt -= 7*60*60;
						break;
					}
					case 'pst':
					{
						$dt -= 8*60*60;
						break;
					}
				}
			}
			
			return $result;
		}
		
		/**
		 * @param int $codePageNum
		 * @return string
		 */
		function GetCodePageName($codePageNum)
		{
			static $mapping = array(
						0 => 'default',
						51936 => 'euc-cn',
						936 => 'gb2312',
						950 => 'big5',
						946 => 'euc-kr',
						50225 => 'iso-2022-kr',
						50220 => 'iso-2022-jp',
						932 => 'shift-jis',
						65000 => 'utf-7',
						65001 => 'utf-8',
						1250 => 'windows-1250',
						1251 => 'windows-1251',
						1252 => 'windows-1252',
						1253 => 'windows-1253',
						1254 => 'windows-1254',
						1255 => 'windows-1255',
						1256 => 'windows-1256',
						1257 => 'windows-1257',
						1258 => 'windows-1258',
						20866 => 'koi8-r',
						28591 => 'iso-8859-1',
						28592 => 'iso-8859-2',
						28593 => 'iso-8859-3',
						28594 => 'iso-8859-4',
						28595 => 'iso-8859-5',
						28596 => 'iso-8859-6',
						28597 => 'iso-8859-7',
						28598 => 'iso-8859-8');

			if (isset($mapping[$codePageNum]))
			{
				return $mapping[$codePageNum];
			}
			return '';
		}
		
		/**
		 * @param string $codePageName
		 * @return int
		 */
		function GetCodePageNumber($codePageName)
		{
			static $mapping = array(
						'default' => 0,
						'euc-cn' => 51936,
						'gb2312' => 936,
						'big5' => 950,
						'euc-kr' => 949,
						'iso-2022-kr' => 50225,
						'iso-2022-jp' => 50220,
						'shift-jis' => 932,
						'utf-7' => 65000,
						'utf-8' => 65001,
						'windows-1250' => 1250,
						'windows-1251' => 1251,
						'windows-1252' => 1252,
						'windows-1253' => 1253,
						'windows-1254' => 1254,
						'windows-1255' => 1255,
						'windows-1256' => 1256,
						'windows-1257' => 1257,
						'windows-1258' => 1258,
						'koi8-r' => 20866,
						'iso-8859-1' => 28591,
						'iso-8859-2' => 28592,
						'iso-8859-3' => 28593,
						'iso-8859-4' => 28594,
						'iso-8859-5' => 28595,
						'iso-8859-6' => 28596,
						'iso-8859-7' => 28597,
						'iso-8859-8' => 28598);
    
			if (isset($mapping[$codePageName]))
			{
				return $mapping[$codePageName];
			}
			return 0;
		}
		
		/**
		 * @param	array	$array
		 * @param	string	$key
		 * @param	mix		$default
		 * @return	mix
		 */
		function ArrayValue($array, $key, $default)
		{
			return (isset($array[$key])) ? $array[$key] : $default;
		}
		
		/**
		 * @param	mix		$mix
		 * @param	bool	$exit[optional] = false
		 */
		function VarDump($mix, $exit = false)
		{
			echo '<pre>';
			var_dump($mix);
			echo '</pre>';
			if ($exit)
			{
				exit();
			}
		}
	}

	if (!defined('PHP_INT_MAX'))
	{
		define('PHP_INT_MAX', (int) get_int_max());
	}
	if (!defined('PHP_INT_MIN'))
	{
		define('PHP_INT_MIN', (int) (PHP_INT_MAX + 1));
	}

	/**
	 * @param int $bigInt
	 * @return int
	 */
	function GetGoodBigInt($bigInt)
	{
		if (null === $bigInt || false == $bigInt)
		{
			return 0;
		}
		else if ($bigInt > PHP_INT_MAX)
		{
			return PHP_INT_MAX;
		}
		else if ($bigInt < PHP_INT_MIN)
		{
			return PHP_INT_MIN;
		}

		return (int) $bigInt;
	}

	/**
	 * @return int
	 */
	function get_int_max()
	{
		$max=0x7fff;
		$probe = 0x7fffffff;
		while ($max == ($probe>>16))
		{
			$max = $probe;
			$probe = ($probe << 16) + 0xffff;
		}
		return $max;
	}

	/**
	 * @param	string	$output
	 * @return	string
	 */
	function obStartGzip($output)
	{
		if (AP_IS_SUPPORT_GZIP && !ini_get('zlib.output_compression'))
		{
			$output = gzencode($output);
			/* $output = myGZip($output); */
			if ($output !== false)
			{
				@header('Content-Encoding: gzip');
			}
		}
		return $output;
	}

	/**
	 * @param	string		$data
	 * @return	string | false
	 */
	function myGZip($data)
	{
		if (function_exists('gzcompress'))
		{
			$size = strlen($data);
			$crc = crc32($data);
			$data = gzcompress($data, 2);
			if (false === $data)
			{
				return false;
			}

			$content = "\x1f\x8b\x08\x00\x00\x00\x00\x00";
			$data = substr($data, 0, strlen($data) - 4);
			$content .= $data;
			$content .= (pack('V', $crc));
			$content .= (pack('V', $size));
			return $content;
		}
		return false;
	}

	/**
	 * @param	string	$output
	 * @return	string
	 */
	function obStartNoGzip($output)
	{
		return $output;
	}