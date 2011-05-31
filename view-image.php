<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	$qs = '?';
	foreach ($_GET as $key => $value)
	{
		 $qs .= $key.'='.urlencode($value).'&';
	}
	
	@header('Content-type: text/html; charset=utf-8');
	
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<link rel="shortcut icon" href="favicon.ico" />
		<title></title>
	</head>
	<body bgcolor="Silver">
		<table width="100%" height="100%">
			<tr>
				<td align="center" valign="middle">
					<img border="1" style="border: 1 px Black;" src="attach.php<?php echo $qs;?>">
				</td>
			</tr>
		</table>
	</body>
</html>