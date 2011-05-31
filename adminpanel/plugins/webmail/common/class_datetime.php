<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	define('AP_DATEFORMAT_DEF0', 'mm/dd/yy');
	define('AP_DATEFORMAT_DEF1', 'd/m/y');
	
	class CDateTime
	{
		/**
		 * @var int
		 */
		var $TimeStamp;
		
		/**
		 * @var string
		 */
		var $FormatString = 'Default';
		
		/**
		 * @var int
		 */
		var $TimeFormat = 0;
		
		/**
		 * @param int $timestamp optional
		 * @return CDateTime
		 */
		function CDateTime($timestamp = null)
		{
			if ($timestamp != null)
			{
				$this->TimeStamp = $timestamp;
			}
		}
		
		/**
		 * @static
		 * @param string $str
		 * @return CDateTime
		 */
		function &CreateFromStr($str)
		{
			$return = new CDateTime(ap_Utils::GetTimeFromString($str));
			return $return; 
		}
		
		/**
		 * @return string
		 */
		function GetAsStr()
		{
			return date('D, j M Y H:i:s O (T)', $this->TimeStamp);
		}
		
		/**
		 * $datetime should have YYYY-MM-DD HH:II:SS format 
		 * @param string $datetime
		 */
		function SetFromANSI($datetime)
		{
			$dt = explode(' ', $datetime);
			$date = explode('-', $dt[0]);
			$time = explode(':', $dt[1]);
			$this->TimeStamp = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
		}
		
		/**
		 * return current timestamp in ANSI format
		 * @return string
		 */
		function ToANSI($newStamp = null)
		{
			if ($newStamp != null) 
			{
				return date('Y-m-d H:i:s', $newStamp);
			}
			return date('Y-m-d H:i:s', $this->TimeStamp);
		}
		
		/**
		 * @return int
		 */
		function GetServerTimeZoneOffset() 
		{
			return date("O") / 100 * 60 * 60; /* Seconds from GMT */
		}
		
		/**
		 * @param short $timeOffsetInMinutes
		 * @return string
		 */
		function GetFormattedDate($timeOffsetInMinutes)
		{
			$localTimeStamp = $this->TimeStamp + $timeOffsetInMinutes * 60;
			
			$timeTemp = 'H:i';
			if ($this->TimeFormat)
			{
				$timeTemp = 'g:i A';
			}
			
			switch ($this->GetDateFormatTypeByString())
			{
				default:
				case WM_DATEFORMAT_DEFAULT:
					return date(AP_DATEFORMAT_DEF1.' '.$timeTemp, $localTimeStamp);
					
				case WM_DATEFORMAT_DDMMYY:
					return date('d/m/y '.$timeTemp, $localTimeStamp);
					
				case WM_DATEFORMAT_MMDDYY:
					return date('m/d/y '.$timeTemp, $localTimeStamp);
					
				case WM_DATEFORMAT_DDMonth:
					return date('d M '.$timeTemp, $localTimeStamp);
					
				case WM_DATEFORMAT_Advanced:
					$outStr = $this->FormatString;
					$outStr = preg_replace('/month/i', date('M', $localTimeStamp), $outStr);
					$outStr = preg_replace('/yyyy/i', date('Y', $localTimeStamp), $outStr);
					$outStr = preg_replace('/yy/i', date('y', $localTimeStamp), $outStr);
					$outStr = str_replace('y', date('z', $localTimeStamp)+1, $outStr);
					$outStr = preg_replace('/dd/i', date('d', $localTimeStamp), $outStr);
					$outStr = preg_replace('/mm/i', date('m', $localTimeStamp), $outStr);
					$outStr = str_replace('q', floor((date('n', $localTimeStamp)-1)/4)+1, $outStr);
					$outStr = str_replace('ww', date('W', $localTimeStamp), $outStr);
					$outStr = str_replace('w', date('w', $localTimeStamp)+1, $outStr);
					$outStr .= date(' '.$timeTemp, $localTimeStamp);

					return $outStr;
			}
		}
		
		/**
		 * @return short
		 */
		function GetDateFormatTypeByString()
		{
			switch (strtolower($this->FormatString))
			{
				case 'default':
					return WM_DATEFORMAT_DEFAULT;
				case 'dd/mm/yy':
					return WM_DATEFORMAT_DDMMYY;
				case 'mm/dd/yy':
					return WM_DATEFORMAT_MMDDYY;
				case 'dd month':
					return WM_DATEFORMAT_DDMonth;
				default:
					return WM_DATEFORMAT_Advanced;
			}
		}

		/**
		 * @param string $dateFormat
		 * @param int $timeFormat
		 * @return string
		 */
		function GetDbDateFormat($dateFormat, $timeFormat)
		{
			if ($timeFormat) 
			{
				$dateFormat .= WM_DATEFORMAT_FLAG;
			}
			return $dateFormat;
		}
		
		/**
		 * @param string $bdDateFormat
		 * @return string
		 */
		function GetDateFormatFromBd($bdDateFormat)
		{
			if (strtolower($bdDateFormat) == 'default' || strtolower($bdDateFormat) == 'default'.WM_DATEFORMAT_FLAG)
			{
				$bdDateFormat = AP_DATEFORMAT_DEF0;
			}
			
			if (!$bdDateFormat) return '';
			$l = strlen($bdDateFormat);
			
			if ($l > 2 && substr($bdDateFormat, -2) == WM_DATEFORMAT_FLAG)
			{
				return substr($bdDateFormat, 0, $l - 2);
			}
			return $bdDateFormat;
		}
		
		/**
		 * @param string $bdDateFormat
		 * @return int
		 */
		function GetTimeFormatFromBd($bdDateFormat)
		{
			if (!$bdDateFormat) 
			{
				return 0;
			}
			
			$l = strlen($bdDateFormat);
			return (int) ($l > 2 && substr($bdDateFormat, -2) == WM_DATEFORMAT_FLAG);
		}
	}
