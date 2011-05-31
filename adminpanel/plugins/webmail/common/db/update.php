<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	define('COLOR_RED', 'red');
	define('COLOR_GREEN', 'green');
	define('COLOR_GREY', '#afafaf');

	echo '<html><head><title>Update</title></head><body>';

	myFlush();

	$db =& DbStorageCreator::CreateDatabaseStorage($this->_settings);
	$prefix = $this->_settings->DbPrefix;
	
	$UpdateIsGood = true;

	$AddTableArray = array(
		DBTABLE_AWM_SENDERS,
		DBTABLE_AWM_COLUMNS,
		DBTABLE_AWM_DOMAINS,
		DBTABLE_AWM_MAILALIASES,
		DBTABLE_AWM_MAILINGLISTS,
		DBTABLE_AWM_MAILFORWARDS,
		
	);
	
	$RenameColumnArray = array(

	);
	
	$AddColumnArray = array(
		DBTABLE_AWM_ADDR_BOOK =>
			array(
				array('use_frequency', 'int(11) NOT NULL default 0', '[int] NOT NULL DEFAULT (0)'),
				array('auto_create', 'tinyint(1) NOT NULL default 0', '[bit] NOT NULL DEFAULT (0)'),
				array('deleted', 'tinyint(1) NOT NULL default 0', '[bit] NOT NULL DEFAULT (0)'),
				array('date_created', 'datetime NULL', '[datetime] NULL'),
				array('date_modified', 'datetime NULL', '[datetime] NULL'),
				array('str_id', 'varchar(100) default NULL', '[varchar] (100) NULL'),
				array('fnbl_pim_id', 'bigint(20) NULL', '[bigint] NULL')
			),
			
		DBTABLE_AWM_ADDR_GROUPS =>
			array(
				array('use_frequency', 'int(11) NOT NULL default 0', '[int] NOT NULL DEFAULT (0)'),
				array('email', 'varchar(255) default NULL', '[varchar] (255) NULL'),
				array('company', 'varchar(200) default NULL', '[varchar] (200) NULL'),
				array('street', 'varchar(255) default NULL', '[varchar] (255) NULL'),
				array('city', 'varchar(200) default NULL', '[varchar] (200) NULL'),
				array('state', 'varchar(200) default NULL', '[varchar] (200) NULL'),
				array('zip', 'varchar(10) default NULL', '[varchar] (10) NULL'),
				array('country', 'varchar(200) default NULL', '[varchar] (200) NULL'),
				array('phone', 'varchar(50) default NULL', '[varchar] (50) NULL'),
				array('fax', 'varchar(50) default NULL', '[varchar] (50) NULL'),
				array('web', 'varchar(255) default NULL', '[varchar] (255) NULL'),
				array('organization', 'tinyint(1) NOT NULL default 0', '[bit] NOT NULL DEFAULT (0)'),
				array('group_str_id', 'varchar(100) default NULL', '[varchar] (100) NULL')
			),
			
		DBTABLE_AWM_ACCOUNTS =>
			array(
				array('id_domain', 'int(11) NOT NULL default 0', '[int] NOT NULL DEFAULT (0)'),
				array('mailing_list', 'tinyint(1) NOT NULL default 0', '[bit] NOT NULL DEFAULT (0)'),
				array('imap_quota', 'tinyint(1) NOT NULL default -1', '[smallint] NOT NULL DEFAULT ((-1))'),
				array('personal_namespace', 'varchar(50) NOT NULL default \'\'', '[varchar] (255) NOT NULL DEFAULT (\'\')')
			),
			
		DBTABLE_AWM_DOMAINS =>
			array(
				array('is_internal', 'tinyint(1) NOT NULL default 0', '[bit] NOT NULL DEFAULT (0)'),
				array('url', 'varchar(255) NULL', '[varchar] (255) NULL'),
				array('site_name', 'varchar(255) NULL', '[varchar] (255) NULL'),
				array('settings_mail_protocol', 'tinyint(1) NULL', '[bit] NULL'),
				array('settings_mail_inc_host', 'varchar(255) NULL', '[varchar] (255) NULL'),
				array('settings_mail_inc_port', 'int(11) NULL', '[int] NULL'),
				array('settings_mail_out_host', 'varchar(255) NULL', '[varchar] (255) NULL'),
				array('settings_mail_out_port', 'int(11) NULL', '[int] NULL'),
				array('settings_mail_out_auth', 'tinyint(1) NULL', '[bit] NULL'),
				array('allow_direct_mode', 'tinyint(1) NULL', '[bit] NULL'),
				array('direct_mode_id_def', 'tinyint(1) NULL', '[bit] NULL'),
				array('attachment_size_limit', 'bigint(20) NULL', '[bigint] NULL'),
				array('allow_attachment_limit', 'tinyint(1) NULL', '[bit] NULL'),
				array('mailbox_size_limit', 'bigint(20) NULL', '[bigint] NULL'),
				array('allow_mailbox_limit', 'tinyint(1) NULL', '[bit] NULL'),
				array('take_quota', 'tinyint(1) NULL', '[bit] NULL'),
				array('allow_new_users_change_set', 'tinyint(1) NULL', '[bit] NULL'),
				array('allow_auto_reg_on_login', 'tinyint(1) NULL', '[bit] NULL'),
				array('allow_users_add_accounts', 'tinyint(1) NULL', '[bit] NULL'),
				array('allow_users_change_account_def', 'tinyint(1) NULL', '[bit] NULL'),
				array('def_user_charset', 'int(11) NULL', '[int] NULL'),
				array('allow_users_change_charset', 'tinyint(1) NULL', '[bit] NULL'),
				array('def_user_timezone', 'int(11) NULL', '[int] NULL'),
				array('allow_users_change_timezone', 'tinyint(1) NULL', '[bit] NULL'),
				array('msgs_per_page', 'smallint(6) NULL', '[smallint] NULL'),
				array('skin', 'varchar(50) NULL', '[varchar] (50) NULL'),
				array('allow_users_change_skin', 'tinyint(1) NULL', '[bit] NULL'),
				array('lang', 'varchar(50) NULL', '[varchar] (50) NULL'),
				array('allow_users_change_lang', 'tinyint(1) NULL', '[bit] NULL'),
				array('show_text_labels', 'tinyint(1) NULL', '[bit] NULL'),
				array('allow_ajax', 'tinyint(1) NULL', '[bit] NULL'),
				array('allow_editor', 'tinyint(1) NULL', '[bit] NULL'),
				array('allow_contacts', 'tinyint(1) NULL', '[bit] NULL'),
				array('allow_calendar', 'tinyint(1) NULL', '[bit] NULL'),
				array('hide_login_mode', 'tinyint(1) NULL', '[bit] NULL'),
				array('domain_to_use', 'varchar(255) NULL', '[varchar] (255) NULL'),
				array('allow_choosing_lang', 'tinyint(1) NULL', '[bit] NULL'),
				array('allow_advanced_login', 'tinyint(1) NULL', '[bit] NULL'),
				array('allow_auto_detect_and_correct', 'tinyint(1) NULL', '[bit] NULL'),
				array('use_captcha', 'tinyint(1) NULL', '[bit] NULL'),
				array('use_domain_selection', 'tinyint(1) NULL', '[bit] NULL'),
				array('global_addr_book', 'tinyint(1) NOT NULL default 0', '[bit] NOT NULL DEFAULT (0)'),
				array('view_mode', 'tinyint(4) NOT NULL default 1', '[tinyint] NOT NULL DEFAULT (1)'),
				array('ldap_auth', 'tinyint(1) NOT NULL default 0', '[bit] NOT NULL DEFAULT (0)'),
				array('save_mail', 'tinyint(4) NOT NULL default 0', '[tinyint] NOT NULL DEFAULT (0)')
			),
		
		DBTABLE_AWM_SETTINGS =>
			array(
				array('question_1', 'varchar(255) default NULL', '[varchar] (255) NULL'),
				array('answer_1', 'varchar(255) default NULL', '[varchar] (255) NULL'),
				array('question_2', 'varchar(255) default NULL', '[varchar] (255) NULL'),
				array('answer_2', 'varchar(255) default NULL', '[varchar] (255) NULL'),
				array('auto_checkmail_interval', 'int(11) default 0', '[int] DEFAULT (0)'),
				array('enable_fnbl_sync', 'int(11) default 0', '[int] DEFAULT (0)')
			),
		
		DBTABLE_AWM_FILTERS =>
			array(
				array('applied', 'tinyint(1) NOT NULL default 1', '[bit] NOT NULL DEFAULT (1)')
			),

		DBTABLE_AWM_MESSAGES =>
			array(
				array('sensitivity', 'tinyint(4) default 0', '[tinyint] NULL DEFAULT (0)')
			),

	);
	
	$UpdateColumnArray = array(
		DBTABLE_AWM_MESSAGES =>
			array (
				array('flags', 'int(11) default NULL', '[int] NULL'),
			),

	);
	
	if ($db->_settings->DbType != AP_DB_MYSQL && $db->_settings->DbType != AP_DB_MSSQLSERVER)
	{
		exit('Warning: Incorrect database type.	Tables can be created in MySql or MSSql database only!');
	}
	
	if (!$db->Connect())
	{
		exit('Connect Error: '.ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL').' '.$db->GetError());
	}
	

	echo '<font color="black" size="3" style="font-family: Tahoma, Verdana;"><h3>WebMail Lite PHP Update Script:</h3>';

	$p = 0;
	
	if (!$db->IsTableExist($prefix, DBTABLE_A_USERS))
	{
		exit('Error: Tables are not created yet. You need to press "Create Tables" before "Update".');
	}

	if (count($AddTableArray) > 0)
	{
		foreach ($AddTableArray as $tabelName)
		{
			CreateTableOnUpdate($db, $tabelName, ++$p);
			myFlush();
		}
	}
	
	echo '<br /><b>'.(++$p).'</b>. Start updating tables: <br />';
	myFlush();

	foreach ($RenameColumnArray As $tableName => $ColumnNames)
	{
		echo '<br /> - Update '.$prefix.$tableName.': <br />';
		if ($db->IsTableExist($prefix, $tableName))		
		{
			$oldColumns = $db->GetTablesColumns($prefix, $tableName);
			if ($oldColumns)
			{
				foreach ($ColumnNames As $names)
				{
					if (in_array($names[1], $oldColumns))
					{
						echo '<font color="'.COLOR_GREY.'"> - '.$names[1].' column already exists in the table</font><br />';
					}			
					else if (!in_array($names[0], $oldColumns))
					{
						echo '<font color="'.COLOR_GREY.'"> - '.$names[0].' column not exists in the table</font><br />';	
					}
					else
					{
						$isGood = false;
						echo ' - rename <b>'.$names[0].'</b> -> <b>'.$names[1].'</b> column in the table: ';
						switch ($db->_settings->DbType)
						{
							case AP_DB_MYSQL:
								$isGood = $db->_connector->Execute('ALTER TABLE `'.$prefix.$tableName.'` CHANGE COLUMN `'.$names[0].'` `'.$names[1].'`'.' '.$names[2][0]);
								break;	
							case AP_DB_MSSQLSERVER:
								$isGood = $db->_connector->Execute('EXEC sp_rename \''.$prefix.$tableName.'.['.$names[0].']\', \''.$names[1].'\', \'COLUMN\'');
								break;	
						}
						
						if ($isGood)
						{
							echo ' <font color="'.COLOR_GREEN.'"><b>done!</b></font><br />';
						}
						else
						{
							$error = (strlen($db->GetError()) > 5) ? '<br />'.$db->GetError() : '';
							echo ' <font color="'.COLOR_RED.'"><b>error!</b>'.$error.'</font><br />';
							$UpdateIsGood = false;
						}
					}			
				}
			}
			else 
			{
				$error = (strlen($db->GetError()) > 5) ? '<br />'.$db->GetError() : '';
				echo '<font color="'.COLOR_RED.'"> - can\'t get '.$prefix.$tableName.' columns names'.$error.'</font><br />';
				$UpdateIsGood = false;
			}		
		}
		else 
		{
			echo '<font color="'.COLOR_RED.'"> - '.$prefix.$tableName.' doesn\'t exist</font><br />';
			$UpdateIsGood = false;
		}
		myFlush();
	}
	
	
	foreach ($AddColumnArray As $tableName => $ColumnNames)
	{
		echo '<br /> - Update '.$prefix.$tableName.': <br />';
		if ($db->IsTableExist($prefix, $tableName))		
		{
			$oldColumns = $db->GetTablesColumns($prefix, $tableName);
			if ($oldColumns)
			{
				foreach ($ColumnNames As $AddrBookArray)
				{
					if (!in_array($AddrBookArray[0], $oldColumns))
					{
						$isGood = false;
						echo ' - add <b>'.$AddrBookArray[0].'</b> column in the table: ';
						switch ($db->_settings->DbType)
						{
							case AP_DB_MYSQL:
								$isGood = $db->_connector->Execute('ALTER TABLE `'.$prefix.$tableName.'` ADD `'.$AddrBookArray[0].'` '.$AddrBookArray[1]);
								break;	
							case AP_DB_MSSQLSERVER:
								$isGood = $db->_connector->Execute('ALTER TABLE ['.$prefix.$tableName.'] ADD ['.$AddrBookArray[0].'] '.$AddrBookArray[2]);
								break;	
						}
						
						if ($isGood)
						{
							echo ' <font color="'.COLOR_GREEN.'"><b>done!</b></font><br />';
						}
						else
						{
							$error = (strlen($db->GetError()) > 5) ? '<br />'.$db->GetError() : '';
							echo ' <font color="'.COLOR_RED.'"><b>error!</b>'.$error.'</font><br />';
							$UpdateIsGood = false;
						}
						
					}
					else echo '<font color="'.COLOR_GREY.'"> - '.$AddrBookArray[0].' column already exists in the table</font><br />';				
				}
			}
			else 
			{
				$error = (strlen($db->GetError()) > 5) ? '<br />'.$db->GetError() : '';
				echo '<font color="'.COLOR_RED.'"> - can\'t get '.$prefix.$tableName.' columns names'.$error.'</font><br />';
				$UpdateIsGood = false;
			}		
		}
		else 
		{
			echo '<font color="'.COLOR_RED.'"> - '.$prefix.$tableName.' doesn\'t exist</font><br />';
			$UpdateIsGood = false;
		}
		myFlush();
	}

	$db->UpdateRequestDomains($this->_settings);
	$db->UpdateRequestCalendarActive();
	
	foreach ($UpdateColumnArray As $tableName => $ColumnNames)
	{
		echo '<br /> - Update '.$prefix.$tableName.': <br />';
		if ($db->IsTableExist($prefix, $tableName))		
		{
			$oldColumns = $db->GetTablesColumns($prefix, $tableName);
			if ($oldColumns && is_array($oldColumns))
			{
				foreach ($ColumnNames As $AddrBookArray)
				{
					if (in_array($AddrBookArray[0], $oldColumns))
					{
						$isGood = false;
						echo ' - modify <b>'.$AddrBookArray[0].'</b> column in the table: ';
						switch ($db->_settings->DbType)
						{
							case AP_DB_MYSQL:
								$isGood = $db->_connector->Execute('ALTER TABLE `'.$prefix.$tableName.'` MODIFY COLUMN `'.$AddrBookArray[0].'` '.$AddrBookArray[1]);
								break;	
							case AP_DB_MSSQLSERVER:
								$isGood = $db->_connector->Execute('ALTER TABLE ['.$prefix.$tableName.'] ALTER COLUMN ['.$AddrBookArray[0].'] '.$AddrBookArray[2]);
								break;	
						}
						
						if ($isGood)
						{
							echo ' <font color="'.COLOR_GREEN.'"><b>done!</b></font><br />';
						}
						else
						{
							$error = (strlen($db->GetError()) > 5) ? '<br />'.$db->GetError() : '';
							echo ' <font color="'.COLOR_RED.'"><b>error!</b>'.$error.'</font><br />';
							$UpdateIsGood = false;
						}
						
					}
					else echo '<font color="'.COLOR_RED.'"> - '.$AddrBookArray[0].' column doesn\'t exist in the table</font><br />';				
				}
			}
			else 
			{
				$error = (strlen($db->GetError()) > 5) ? '<br />'.$db->GetError() : '';
				echo '<font color="'.COLOR_RED.'"> - can\'t get '.$prefix.$tableName.' columns names'.$error.'</font><br />';
				$UpdateIsGood = false;
			}		
		}
		else 
		{
			echo '<font color="'.COLOR_RED.'"> - '.$prefix.$tableName.' doesn\'t exist</font><br />';
			$UpdateIsGood = false;
		}
		myFlush();
	}
	
	echo '<br /><b>'.(++$p).'</b>. Start creating/updating indexs: <br />';
	myFlush();

	$AddIndexArray = GetIndexsArray();
	$AllTables = $db->AllTableNames();
	foreach ($AddIndexArray as $tableName => $indexData) 
	{
		if (is_array($indexData))
		{
			foreach ($indexData as $fieldName) 
			{
				if ($db->CheckExistIndex($prefix, $tableName, $fieldName))
				{
					echo '<font color="'.COLOR_GREY.'"> - index on '.$fieldName.' already exists in '.$prefix.$tableName.' table</font><br />';
				}
				else 
				{
					if (!in_array($prefix.$tableName, $AllTables))
					{
						echo '<font color="'.COLOR_RED.'"> - can\'t create index for '.$fieldName.' in '.$prefix.$tableName.' table (the table doesn\'t exist)</font><br />';
					}
					else if ($db->CreateIndex($prefix, $tableName, $fieldName))
					{
						echo ' - add index on '.$fieldName.' in '.$prefix.$tableName.' table: <font color="'.COLOR_GREEN.'"><b>done!</b></font><br />';
					}
					else 
					{
						$error = (strlen($db->GetError()) > 5) ? '<br />'.$db->GetError() : '';
						echo '<font color="'.COLOR_RED.'"> - can\'t create index for '.$fieldName.' in '.$prefix.$tableName.' table'.$error.'</font><br />';
						$UpdateIsGood = false;
					}
				}
			}
		}
		myFlush();
	}

	@$db->CreateFunctions();

	@$db->Disconnect();
	
	echo '<br /><b>'.(++$p).'</b>. Start updating settings.xml file: ';
	if ($db->_settings->SaveToXml())
	{
		 echo ' <font color="'.COLOR_GREEN.'"><b>done!</b></font><br />';
	}
	else
	{
		echo '<font color="'.COLOR_RED.'"> - can\'t update settings.xml file</font><br />';
		$UpdateIsGood = false;
	}
	
	echo ($UpdateIsGood) 
			? '<br /><br /><font color="'.COLOR_GREEN.'"><b>Update done!</b></font>' 
			: '<br /><br /><font color="'.COLOR_RED.'"><b>Update failed!</b></font></font>';

	/**
	 * @param	webmail_DbStorage	$dbStorage
	 * @param	string				$tableName
	 * @param	int					$number
	 */
	function CreateTableOnUpdate(&$dbStorage, $tableName, $number)
	{
		global $UpdateIsGood;
		
		$prefix = $dbStorage->_settings->DbPrefix;
		echo '<br /><b>'.$number.'</b>. Start creating <b>'.$prefix.$tableName.'</b> table: <br />';
	
		if (!$dbStorage->IsTableExist($prefix, $tableName))
		{
			if ($dbStorage->CreateOneTable($prefix, $tableName))
			{
				echo '<font color="'.COLOR_GREEN.'"> - '.$prefix.$tableName.' created successfully</font><br />';
			}
			else 
			{
				$error = $dbStorage->GetError();
				echo '<font color="'.COLOR_RED.'"> - '.$prefix.$tableName.' don\'t create '.$error.'</font><br />';
				$UpdateIsGood = false;
			}
		}
		else 
		{
			echo '<font color="'.COLOR_GREY.'"> - '.$prefix.$tableName.' already exists</font><br />';
		}
	}


	function myFlush()
	{
		@flush();
	}
	
	echo '</body></html>';
	myFlush();
