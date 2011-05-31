<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	$cookieHash = isset($_COOKIE['PHPWMADMINSESSID']) ? $_COOKIE['PHPWMADMINSESSID'] : null;
	if ($cookieHash)
	{
		@setcookie('PHPWMADMINSESSID', $cookieHash, time() + 3600, '/');
	}

	@session_name('PHPWMADMINSESSID');
	$session_is_start = (bool) @session_start();
