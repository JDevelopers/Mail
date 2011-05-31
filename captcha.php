<?php

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/'));

	require_once(WM_ROOTPATH.'common/inc_top.php');
	require WM_ROOTPATH.'libs/kcaptcha/kcaptcha.php';
	require WM_ROOTPATH.'common/class_session.php';

	$captcha = new KCAPTCHA();
	if (isset($_GET['PHPWEBMAILSESSID']))
	{
		$_SESSION['captcha_keystring'] = $captcha->getKeyString();
	}
