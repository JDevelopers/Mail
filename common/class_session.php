<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 *
 */

/*
defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));
require_once(WM_ROOTPATH.'common/class_dbstorage.php');

function Wm_SessionOpen($path, $name)
{
	return true;
}

function Wm_SessionClose()
{
	return true;
}

function Wm_SessionRead($hash)
{
	$_null = null;
	$result = false;
	$_dbStorageSess =& DbStorageCreator::CreateDatabaseStorage($_null);
	
	$result = $_dbStorageSess->Connect();
	if ($result)
	{
		$result = $_dbStorageSess->SessionRead($hash);
	}

	return $result;
}

function Wm_SessionWrite($hash, $sess_data)
{
	$_null = null;
	$_dbStorageSess =& DbStorageCreator::CreateDatabaseStorage($_null);

	return $_dbStorageSess->Connect() && $_dbStorageSess->SessionWrite($hash, $sess_data);
}

function Wm_SessionDestroy($id)
{
	$_null = null;
	$_dbStorageSess =& DbStorageCreator::CreateDatabaseStorage($_null);

	return $_dbStorageSess->Connect() && $_dbStorageSess->SessionDestroy($id);
}

function Wm_SessionGC()
{
	$_null = null;
	$_dbStorageSess =& DbStorageCreator::CreateDatabaseStorage($_null);

	return $_dbStorageSess->Connect() && $_dbStorageSess->SessionGC(time() - 7200);
}

ini_set('session.save_handler', 'user');
session_set_save_handler('Wm_SessionOpen', 'Wm_SessionClose', 'Wm_SessionRead', 'Wm_SessionWrite', 'Wm_SessionDestroy', 'Wm_SessionGC');
/**/

if (defined('UPDATE_SESSION_COOKIE'))
{
	$cookieHash = isset($_COOKIE['PHPWEBMAILSESSID']) ? $_COOKIE['PHPWEBMAILSESSID'] : null;
	if ($cookieHash)
	{
		@setcookie('PHPWEBMAILSESSID', $cookieHash, time() + 3600 * 3, '/');
	}

	if (isset($_COOKIE['awm_autologin_data'], $_COOKIE['awm_autologin_id']))
	{
		$time = 3600 * 24 * 14;
		@setcookie('awm_autologin_data', $_COOKIE['awm_autologin_data'], time() + $time, '/');
		@setcookie('awm_autologin_id', $_COOKIE['awm_autologin_id'], time() + $time, '/');
	}
}

// @session_set_cookie_params(3600 * 3);
@session_name('PHPWEBMAILSESSID');
@session_start();