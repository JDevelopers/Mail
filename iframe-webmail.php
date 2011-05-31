<?php

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/'));

	require_once(WM_ROOTPATH.'common/inc_top.php');
	
	require_once(WM_ROOTPATH.'common/inc_constants.php');
	
	@ob_start(USE_INDEX_GZIP ? 'obStartGzip' : 'obStartNoGzip');

	require WM_ROOTPATH.'common/class_session.php';
	require_once(WM_ROOTPATH.'common/class_settings.php');
	require_once(WM_ROOTPATH.'common/class_filesystem.php');

	$settings =& Settings::CreateInstance();
	if (!$settings || !$settings->isLoad)
	{
		header('Location: index.php?error=3');
		exit();
	}
	elseif (!$settings->IncludeLang())
	{
		header('Location: index.php?error=6');
		exit();
	}

	$start = isset($_GET['start']) ? (int) $_GET['start'] : 0;
	$to = isset($_GET['to']) ? $_GET['to'] : '';

	$params = array();
	if ($start > 0)
	{
		$params[] = 'start='.$start;
	}
	if (strlen($to) > 0)
	{
		$params[] = 'to='.$to;
	}

	define('defaultTitle', $settings->WindowTitle);

	header('Content-type: text/html; charset=utf-8');
	header('Content-script-type: text/javascript');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="favicon.ico" />
	<title><?php echo defaultTitle; ?></title>
</head>
<frameset rows="100,*" cols="*">
	<frame src="empty.html" name="topFrame" scrolling="no" noresize="noresize" />
	<frameset cols="100,*,100">
		<frame src="empty.html" name="leftFrame" scrolling="no" noresize="noresize" frameborder="0" />
		<frame src="webmail.php?iframe&<?php echo implode('&', $params); ?>" name="mainFrame" frameborder="0" />
		<frame src="empty.html" name="rightFrame" scrolling="no" noresize="noresize" frameborder="0" />
	</frameset>
</frameset>
</html><?php echo '<!-- '.WMVERSION.' -->';


