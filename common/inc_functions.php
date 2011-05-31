<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 *
 */

	/**
	 * @param string $Name
	 * @return string
	 */
	function GetNameByLang($Name)
	{
		if (isset($GLOBALS['LangNamesArray'][$Name]))
		{
			return $GLOBALS['LangNamesArray'][$Name];
		}
		return $Name;
	}

	/**
	 * @return string
	 */
	function getGlobalError()
	{
		return isset($GLOBALS[ErrorDesc]) ? $GLOBALS[ErrorDesc] : '';
	}

	/**
	 * @param string $errorString
	 */
	function setGlobalError($errorString)
	{
		$GLOBALS[ErrorDesc]	= $errorString;
	}

	/**
	 * @return string
	 */
	function GetSessionAttachDir()
	{
		if (!isset($_SESSION[ATTACH_DIR]) || strlen($_SESSION[ATTACH_DIR]) == 0)
		{
			$_SESSION[ATTACH_DIR] = md5(session_id());
		}
		
		return $_SESSION[ATTACH_DIR];
	}

	/**
	 * @param	string	$output
	 * @return	string
	 */
	function obStartGzip($output)
	{
		if (IS_SUPPORT_GZIP && !ini_get('zlib.output_compression'))
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
	
	/**
	 * @return float
	 */
	function getmicrotime() 
	{ 
    	list($usec, $sec) = explode(' ', microtime()); 
    	return ((float)$usec + (float)$sec); 
	}

	/**
	 * @return	string
	 */
	function GetCurrentHost()
	{
		$host = isset($_SERVER['HTTP_HOST']) ? strtolower(trim($_SERVER['HTTP_HOST'])) : '';
		if (substr($host, 0, 4) === 'www.')
		{
			$host = substr($host, 4);
		}
		
		return $host;
	}

	class CMessageInfo
	{
		var $id;
		var $uid;
		var $folderId;
		var $folderFullName;

		/**
		 * @return string
		 */
		function GetUrl()
		{
			return 'msg_id='.urlencode($this->id).'&msg_uid='.urlencode($this->uid).'&folder_id='.urlencode($this->folderId).'&folder_fname='.urlencode($this->folderFullName);
		}

		/**
		 * @return string
		 */
		function GetShortUrl()
		{
			return 'msg_id='.urlencode($this->id).'&msg_uid='.urlencode($this->uid);
		}

		function SetInfo($id, $uid, $folderId = '', $folderFullName = '')
		{
			$this->id = $id;
			$this->uid = $uid;
			$this->folderId = $folderId;
			$this->folderFullName = $folderFullName;
		}

		function Id()
		{
			return (int) $this->id;
		}

		function Uid()
		{
			return $this->uid;
		}

		function FolderId()
		{
			return (int) $this->folderId;
		}

		function FolderFullName()
		{
			return $this->folderFullName;
		}
	}

	class Post
	{
		function has($key) { return isset($_POST[$key]); }
		function val($key, $default = null) { return Post::has($key) ? $_POST[$key] : $default;	}
	}

	class Get
	{
		function has($key) { return isset($_GET[$key]); }
		function val($key, $default = null)	{ return Get::has($key) ? $_GET[$key] : $default; }
	}

	class Session
	{
		function has($key) { return isset($_SESSION[$key]); }
		function val($key, $default = null)	{ return Session::has($key) ? $_SESSION[$key] : $default; }
	}

	/* timezone fix code */
	if (defined('SERVER_TIME_ZONE') && function_exists('date_default_timezone_set'))
	{
		@date_default_timezone_set(SERVER_TIME_ZONE);
	}
