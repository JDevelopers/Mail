<?php 

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/'));

	require_once(WM_ROOTPATH.'common/inc_top.php');
	
	define('UPDATE_SESSION_COOKIE', true);
	require WM_ROOTPATH.'common/class_session.php'; 
	
	@header('Content-type: text/html; charset=utf-8');
	
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" /><html><head><meta http-equiv="refresh" content="420; URL=session-saver.php?<?php echo md5(time());?>" /></head><body></body></html>
