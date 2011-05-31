<?php

	/*
	 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
	 *
	 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
	 * Distributed under the terms of the license described in COPYING
	 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));
	require_once(WM_ROOTPATH.'common/inc_constants.php');

	$expireTime = 31536000;
	$nTime = time();
	$eTag = '"7a86e-c3eb-1d612a0f"';

	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $eTag)
	{
		ReturnCache();
	}
	else if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && !empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
	{
		ReturnCache();
	}

	header('Cache-Control: Public');
	Header('Pragma: Public');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', $nTime - $expireTime).' UTC');
	Header('Expires: '.date('D, j M Y H:i:s', $nTime + $expireTime).' UTC');
	Header('Etag: '.$eTag);

	function ReturnCache()
	{
		global $nTime, $expireTime, $eTag;
		
		if (isset($_SERVER['SERVER_PROTOCOL']) && !empty($_SERVER['SERVER_PROTOCOL']))
		{
			header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
		}
		else
		{
			header('HTTP/1.0 304 Not Modified');
		}
		header('Cache-Control: Public');
		Header('Pragma: Public');
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE']);
		}
		Header('Expires: '.date('D, j M Y H:i:s', $nTime + $expireTime).' UTC');
		Header('Etag: '.$eTag);
		header('Content-Length: 0');
		exit();
	}
/**/