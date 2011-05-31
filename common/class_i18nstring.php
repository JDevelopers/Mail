<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));
	
	require_once(WM_ROOTPATH.'common/class_convertutils.php');
	
	define('CPAGE_AUTOSELECT', 'auto');
	
	define('CPAGE_UTF7_Imap', 'utf7-imap');
	define('CPAGE_UTF8', 'utf-8');
	
	define('CPAGE_IBM855', 'cp855');
	define('CPAGE_IBM866', 'cp866');
	
	define('CPAGE_KOI8R', 'koi8-r');
	
	define('CPAGE_MAC_CYRILLIC', 'x-mac-cyrillic');
						
	define('CPAGE_ISO8859_1', 'iso-8859-1');
	define('CPAGE_ISO8859_2', 'iso-8859-2');
	define('CPAGE_ISO8859_3', 'iso-8859-3');
	define('CPAGE_ISO8859_4', 'iso-8859-4');
	define('CPAGE_ISO8859_5', 'iso-8859-5');
	define('CPAGE_ISO8859_6', 'iso-8859-6');
	define('CPAGE_ISO8859_7', 'iso-8859-7');
	define('CPAGE_ISO8859_8', 'iso-8859-8');
	define('CPAGE_ISO8859_9', 'iso-8859-9');
	define('CPAGE_ISO8859_10', 'iso-8859-10');
	define('CPAGE_ISO8859_11', 'iso-8859-11');
	define('CPAGE_ISO8859_13', 'iso-8859-13');
	define('CPAGE_ISO8859_14', 'iso-8859-14');
	define('CPAGE_ISO8859_15', 'iso-8859-15');
	define('CPAGE_ISO8859_16', 'iso-8859-16');
	
	define('CPAGE_WINDOWS_1250', 'windows-1250');
	define('CPAGE_WINDOWS_1251', 'windows-1251');
	define('CPAGE_WINDOWS_1252', 'windows-1252');
	define('CPAGE_WINDOWS_1253', 'windows-1253');
	define('CPAGE_WINDOWS_1254', 'windows-1254');
	define('CPAGE_WINDOWS_1255', 'windows-1255');
	define('CPAGE_WINDOWS_1256', 'windows-1256');
	define('CPAGE_WINDOWS_1257', 'windows-1257');
	define('CPAGE_WINDOWS_1258', 'windows-1258');
	

	class I18nString
	{
		/**
		 * @access private
		 * @var Array
		 */
		var $_text = array();
		
		/**
		 * input encoding
		 * @var string
		 */
		var $DefaultCodePage;
		
		/**
		 * output encoding
		 * @var string
		 */
		var $TextCodePage = CPAGE_UTF8;
		
		function I18nString($text, $encoding = CPAGE_UTF8)
		{
			$this->DefaultCodePage = strtolower($encoding);
			$this->_text[$this->DefaultCodePage] = $text;
		}
		
		/**
		 * @return Array
		 */
		function &GetUtf8MappingList()
		{
			static $utf8MappingList = array(
				CPAGE_IBM855, CPAGE_IBM866, CPAGE_KOI8R,
				CPAGE_ISO8859_1, CPAGE_ISO8859_2,	CPAGE_ISO8859_3, CPAGE_ISO8859_4,
				CPAGE_ISO8859_5, CPAGE_ISO8859_6,	CPAGE_ISO8859_6, CPAGE_ISO8859_7,
				CPAGE_ISO8859_8, CPAGE_ISO8859_9,	CPAGE_ISO8859_10, CPAGE_ISO8859_11,
				CPAGE_ISO8859_13, CPAGE_ISO8859_14, CPAGE_ISO8859_15, CPAGE_ISO8859_16,
				CPAGE_WINDOWS_1250, CPAGE_WINDOWS_1251, CPAGE_WINDOWS_1252,	CPAGE_WINDOWS_1253,
				CPAGE_WINDOWS_1254, CPAGE_WINDOWS_1255, CPAGE_WINDOWS_1256,	CPAGE_WINDOWS_1257,
				CPAGE_WINDOWS_1258);
				
			return $utf8MappingList;
		}
		
		/**
		 * @param string $value
		 */
		function SetText($value)
		{
			$this->_text = array();
			$this->DefaultCodePage = strtolower($this->DefaultCodePage);
			$this->_text[$this->DefaultCodePage] = $value;
		}
		
		/**
		 * @return string
		 */
		function GetDefaultText()
		{
			return $this->_text[$this->DefaultCodePage];
		}
		
		/**
		 * @param string $encoding optional
		 * @return string
		 */
		function GetText($encoding = null)
		{
			if ($encoding === null)
			{
				$encoding = $this->TextCodePage;
			}
			
			$this->DefaultCodePage = strtolower($this->DefaultCodePage);
			$encoding = strtolower($encoding);

			if (isset($this->_text[$encoding]))
			{
				return $this->_text[$encoding];
			}
			
			if ($this->DefaultCodePage == CPAGE_UTF8 && $encoding == CPAGE_UTF7_Imap)
			{
				$this->_text[$encoding] = ConvertUtils::Utf8to7($this->_text[$this->DefaultCodePage]);
				return $this->_text[$encoding];
			}
			if ($this->DefaultCodePage == CPAGE_UTF7_Imap && $encoding == CPAGE_UTF8)
			{
				$this->_text[$encoding] = ConvertUtils::Utf7to8($this->_text[$this->DefaultCodePage]);
				return $this->_text[$encoding];
			}
			
			if (extension_loaded('xml'))
			{
				// convert here
				if ($this->DefaultCodePage == CPAGE_ISO8859_1 && $encoding == CPAGE_UTF8)
				{
					$this->_text[$encoding] = utf8_encode($this->_text[$this->DefaultCodePage]);
					return $this->_text[$encoding];
				}
				if ($this->DefaultCodePage == CPAGE_UTF8 && $encoding == CPAGE_ISO8859_1)
				{
					$this->_text[$encoding] = utf8_decode($this->_text[$this->DefaultCodePage]);
					return $this->_text[$encoding];
				}
			}
			
			$cyrCodePages = array(CPAGE_KOI8R => 'k',
									CPAGE_WINDOWS_1251 => 'w',
									CPAGE_ISO8859_5 => 'i',
									CPAGE_IBM866 => 'd',
									CPAGE_MAC_CYRILLIC => 'm');

			if (isset($cyrCodePages[$this->DefaultCodePage]) &&	isset($cyrCodePages[$encoding]))
			{
				$this->_text[$encoding] = convert_cyr_string($this->_text[$this->DefaultCodePage],
											$cyrCodePages[$this->DefaultCodePage], $cyrCodePages[$encoding]);
				return $this->_text[$encoding];
			}
			
			if (IS_SUPPORT_ICONV && USE_ICONV && $this->DefaultCodePage != CPAGE_UTF7_Imap)
			{
				return ConvertUtils::ClassIconv($this->DefaultCodePage, $encoding, $this->_text[$this->DefaultCodePage]);
			}
			
			if (function_exists('mb_convert_encoding') && USE_MBSTRING)
			{
				return ConvertUtils::ClassMb_convert_encoding($this->DefaultCodePage, $encoding, $this->_text[$this->DefaultCodePage]);				
			}
			
			if ($encoding == CPAGE_UTF8)
			{
				return $this->GetTextAsUtf8();
			}
			
			if (!isset($this->_text[CPAGE_UTF8]))
			{
				$this->GetTextAsUtf8();
			}
			
			if (isset($this->_text[CPAGE_UTF8]))
			{
				$utf8MappingList =& $this->GetUtf8MappingList();
				if (in_array($encoding, $utf8MappingList))
				{
					require_once(WM_ROOTPATH.'common/utf8encode/'.$encoding.'.php');
					$this->_text[$encoding] = call_user_func(
						'charset_encode_'.str_replace('-', '_', $encoding),
						$this->_text[CPAGE_UTF8]);
					return $this->_text[$encoding];
				}
				
			}
			
			// undefined codepage, we'll return original string
			return $this->_text[$this->DefaultCodePage];
			
		}
		
		/**
		 * @return string
		 */
		function GetTextAsUtf8()
		{
			if (function_exists('utf8_encode') && $this->DefaultCodePage == CPAGE_ISO8859_1)
			{
				$this->_text[CPAGE_UTF8] = utf8_encode($this->_text[$this->DefaultCodePage]);
				return $this->_text[CPAGE_UTF8];
			}
			
			if (function_exists('mb_convert_encoding') && USE_MBSTRING)
			{
				$this->_text[CPAGE_UTF8] = ConvertUtils::ClassMb_convert_encoding($this->DefaultCodePage, CPAGE_UTF8, $this->_text[$this->DefaultCodePage]);
				return $this->_text[CPAGE_UTF8];
			}
			
			if (IS_SUPPORT_ICONV && USE_ICONV)
			{
				$this->_text[CPAGE_UTF8] = ConvertUtils::ClassIconv($this->DefaultCodePage, CPAGE_UTF8, $this->_text[$this->DefaultCodePage]);
				return $this->_text[CPAGE_UTF8];
			}
			
			$utf8MappingList = &$this->GetUtf8MappingList();
			if (in_array($this->DefaultCodePage, $utf8MappingList))
			{
				require_once(WM_ROOTPATH.'common/utf8decode/'.$this->DefaultCodePage.'.php');
				$this->_text[CPAGE_UTF8] = call_user_func(
					'charset_decode_'.str_replace('-', '_', $this->DefaultCodePage),
					$this->_text[$this->DefaultCodePage]);
				return $this->_text[CPAGE_UTF8];
			}

			return $this->_text[$this->DefaultCodePage];
		}
		
		
		/**
		 * @return int
		 */
		function Length()
		{
			if (function_exists('mb_strlen') && USE_MBSTRING)
			{
				return mb_strlen($this->_text[$this->DefaultCodePage], $this->DefaultCodePage);
			}
			
			if (function_exists('iconv_strlen') && USE_ICONV)
			{
				return iconv_strlen($this->_text[$this->DefaultCodePage], $this->DefaultCodePage);
			}

			return strlen($this->_text[$this->DefaultCodePage]);
		}
		
		/**
		 * @param int $start
		 * @param int $length optional
		 * @return string
		 */
		function Substring($start, $length = null)
		{
			if ($this->TextCodePage != CPAGE_UTF8)
			{
				return substr($this->_text[$this->TextCodePage], $start, $length);
			}
			
			if (function_exists('mb_substr') && USE_MBSTRING)
			{
				return mb_substr($this->_text[$this->TextCodePage], $start, $length, $this->TextCodePage);
			}
			
			if (function_exists('iconv_substr') && USE_ICONV)
			{
				return iconv_substr($this->_text[$this->TextCodePage], $start, $length, $this->TextCodePage);
			}
			
			return substr($this->_text[$this->TextCodePage], $start, $length);

		}
		
		/**
		 * @return string
		 */
		function ToLower($encoding = null)
		{
			if ($encoding === null)
			{
				$encoding = $this->TextCodePage;
			}
			else 
			{
				$this->GetText($encoding);
			}
			
			if (function_exists('mb_strtolower') && USE_MBSTRING)
			{
				return mb_strtolower($this->_text[$encoding], $encoding);
			}

			if ($encoding == CPAGE_UTF8)
			{
				if (isset($this->_text[CPAGE_UTF8]))
				{
					require_once(WM_ROOTPATH.'common/utf8utils/utf8_strtolower.php');
					return Utf8StrToLower($this->_text[CPAGE_UTF8]);	
				}
				else 
				{
					require_once(WM_ROOTPATH.'common/utf8utils/utf8_strtolower.php');
					return Utf8StrToLower($this->GetTextAsUtf8());	
				}
			}
			
			return strtolower($this->_text[$encoding]);
		}
		
		/**
		 * @return string
		 */
		function ToUpper($encoding = null)
		{
			if ($encoding === null)
			{
				$encoding = $this->TextCodePage;
			}
			else 
			{
				$this->GetText($encoding);
			}
			
			if (function_exists('mb_strtoupper') && USE_MBSTRING)
			{
				return mb_strtoupper($this->_text[$encoding], $encoding);
			}
			
			if ($encoding == CPAGE_UTF8)
			{
				if (isset($this->_text[CPAGE_UTF8]))
				{
					require_once(WM_ROOTPATH.'common/utf8utils/utf8_strtolower.php');
					return Utf8StrToLower($this->_text[CPAGE_UTF8]);	
				}
				else 
				{
					require_once(WM_ROOTPATH.'common/utf8utils/utf8_strtolower.php');
					return Utf8StrToLower($this->GetTextAsUtf8());	
				}
			}
			return strtoupper($this->_text[$encoding]);
		}

		/**
		 * @param int $size
		 * @return string
		 */
		function Truncate($size)
		{
			if (!isset($this->_text[$this->TextCodePage]))
			{
				$this->GetText();
			}
			
			if (strlen($this->_text[$this->TextCodePage]) <= $size)
			{
				return ($this->TextCodePage == CPAGE_UTF8) ?
					ConvertUtils::ClearUtf8($this->_text[$this->TextCodePage]) :
					$this->_text[$this->TextCodePage];
			}
			
			if ($this->TextCodePage == CPAGE_UTF8)
			{
				while (ord($this->_text[CPAGE_UTF8]{$size}) >> 6 == 2)
				{
					$size--;
				}
				
				return ConvertUtils::ClearUtf8(substr($this->_text[CPAGE_UTF8], 0, $size));
			}
			
			return substr($this->_text[$this->TextCodePage], 0, $size);
		}
	}