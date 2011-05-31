<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	$db =& DbStorageCreator::CreateDatabaseStorage($this->_settings);
	$prefix = $this->_settings->DbPrefix;

	if ($db->_settings->DbType != AP_DB_MYSQL && $db->_settings->DbType != AP_DB_MSSQLSERVER)
	{
		exit('Warning: Incorrect database type.	Tables can be created in MySql or MSSql database only!');
	}
	
	if (!$db->Connect())
	{
		exit('Connect Error: '.ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL').' '.$db->GetError());
	}
	
	$bodyText = '';
	if($db->AdminAllTableCreate($bodyText))
	{
		$bodyText = nl2br($bodyText);
	}
	else 
	{
		$bodyText = '<font color="red"><b>Warning!</b></font><br />'.nl2br($bodyText);
	}
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html>
<head>
	<?php echo AP_META_CONTENT_TYPE; ?>
	<title>WebMail</title>
</head>
<body style="font-family: Tahoma, Verdana;">
<table width="100%">
	<tr>
		<td>
			<?php echo $bodyText; ?>
		</td>
	</tr>
</table>
</body>
</html>	