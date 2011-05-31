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

	define('QUOTECHAR', '`');

	define('BACKUPFILENAME', AP_DATA_FOLDER.'/backup/db_backup['.date('Y-m-d_H.i.s').'].sql');

	echo '<html><head><title>Backup</title></head><body>';

	myFlush();

	$db =& DbStorageCreator::CreateDatabaseStorage($this->_settings);
	$prefix = $this->_settings->DbPrefix;

	$RowTypes = array();
	$BackupIsGood = true;
	if ($db->_settings->DbType != AP_DB_MYSQL)
	{
		exit('Warning: Incorrect database type.	Tables can be backaped in MySql database only!');
	}

	if (!$db->Connect())
	{
		exit('Connect Error: '.ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL').' '.$db->GetError());
	}

	if (!@is_dir(AP_DATA_FOLDER.'/backup'))
	{
		@mkdir(AP_DATA_FOLDER.'/backup', 0777);
	}

	$fp = @fopen(BACKUPFILENAME, 'wb');
	if (!$fp)
	{
		exit('Error: Can\'t open file '.BACKUPFILENAME);
	}


	echo '<font color="black" size="3" style="font-family: Tahoma, Verdana;"><h3>WebMail Lite PHP Backup Script:</h3>';

	
	$AllTables = GetTablesArray('');

	$tables = array();
	for ($t = 0; $t < count($AllTables); $t++)
	{
		$name = $AllTables[$t];
		if ($name !== DBTABLE_AWM_MESSAGES_INDEX && $name !== DBTABLE_AWM_MESSAGES_BODY_INDEX)
		{
			$tables[] = $db->_settings->DbPrefix.$AllTables[$t];
		}
	}

	$dbConnector =& $db->GetConnector();

	$mysql_server_info = @mysql_get_server_info();
	$TypeEngineKey = (version_compare($mysql_server_info, '4.0.0', '>=') ? 'Engine' : 'Type'); // MySQL 4.and higher, the 'Type' of database is now 'Engine' <thanks Philippe Soussan>

	$alltablesstructure = '';

	for ($t = 0; $t < count($tables); $t++)
	{
		$fieldnames     = array();
		$structurelines = array();

		$dbConnector->Execute('SHOW FIELDS FROM '.QUOTECHAR.$tables[$t].QUOTECHAR);
		while (($row = $dbConnector->GetNextArrayRecord()) != false)
		{
			$line  = QUOTECHAR.$row['Field'].QUOTECHAR;
			$line .= ' '.$row['Type'];
			$line .= ' '.($row['Null'] ? '' : 'NOT ').'NULL';
			eregi('^[a-z]+', $row['Type'], $matches);
			$RowTypes[$tables[$t]][$row['Field']] = $matches[0];
			if (isset($row['Default']) && $row['Default'])
			{
				if (eregi('^(tiny|medium|long)?(text|blob)', $row['Type']))
				{
					// no default values
				}
				else
				{
					$line .= ' default \''.$row['Default'].'\'';
				}
			}
			$line .= isset($row['Extra']) ? ' '.$row['Extra'] : '';
			$structurelines[] = $line;

			$fieldnames[] = $row['Field'];
		}
		$dbConnector->FreeResult();

		$tablekeys    = array();
		$uniquekeys   = array();
		$fulltextkeys = array();
		$dbConnector->Execute('SHOW INDEX FROM '.QUOTECHAR.$tables[$t].QUOTECHAR);
		$INDICES = array();
		while (($row = $dbConnector->GetNextArrayRecord()) != false)
		{
			$INDICES[$row['Key_name']][$row['Seq_in_index']] = $row;
		}
		$dbConnector->FreeResult();

		foreach ($INDICES as $index_name => $columndata)
		{
			$structureline  = '';
			if ($index_name == 'PRIMARY')
			{
				$structureline .= 'PRIMARY ';
			}
			else if ((isset($columndata[1]['Index_type']) && $columndata[1]['Index_type'] == 'FULLTEXT')
					|| (isset($columndata[1]['Comment']) && $columndata[1]['Comment'] == 'FULLTEXT'))
			{
				$structureline .= 'FULLTEXT ';
			}
			else if (!isset($columndata[1]['Non_unique'])
					|| (isset($columndata[1]['Non_unique']) && !$columndata[1]['Non_unique']))
			{
				$structureline .= 'UNIQUE ';
			}
			$structureline .= 'KEY';
			if ($index_name != 'PRIMARY')
			{
				$structureline .= ' '.QUOTECHAR.$index_name.QUOTECHAR;
			}
			$structureline .= ' (';
			$firstkeyname = true;
			foreach ($columndata as $seq_in_index => $row)
			{
				if (!$firstkeyname)
				{
					$structureline .= ',';
				}
				$structureline .= QUOTECHAR.$row['Column_name'].QUOTECHAR;
				if (isset($row['Sub_part']) && $row['Sub_part'])
				{
					$structureline .= '('.$row['Sub_part'].')';
				}
				$firstkeyname = false;
			}
			$structureline .= ')';
			$structurelines[] = $structureline;
		}

		$add = '';
		$dbConnector->Execute('SHOW TABLE STATUS LIKE "'.$tables[$t].'"');
		$row = $dbConnector->GetNextArrayRecord();
		if ($row)
		{
			$add = ' TYPE='.$row[$TypeEngineKey];
			if (isset($row['Auto_increment']) && $row['Auto_increment'] !== null)
			{
				$add .= ' AUTO_INCREMENT='.$row['Auto_increment'];
			}
		}
		$dbConnector->FreeResult();

		$tablestructure  = 'CREATE TABLE '.QUOTECHAR.$tables[$t].QUOTECHAR.' ('.AP_CRLF;
		$tablestructure .= '  '.implode(','.AP_CRLF.'  ', $structurelines).AP_CRLF;
		$tablestructure .= ') /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */'.$add;
		$tablestructure .= ';'.AP_CRLF.AP_CRLF;

		WriteBackupFile($fp, str_replace(' ,', ',', $tablestructure));
	}

	$processedrows    = 0;
	$rows = array();
	for ($t = 0; $t < count($tables); $t++)
	{
		$fieldnames = array();
		$dbConnector->Execute('SELECT * FROM '.$tables[$t]);
		$rows[$t] = $dbConnector->ResultCount();
		if ($rows[$t] > 0)
		{
			WriteBackupFile($fp, str_replace(' ,', ',', AP_CRLF.'# dumping data for '.$tables[$t].AP_CRLF));
		}

		$result =& $dbConnector->GetResult();
		for ($i = 0, $c = mysql_num_fields($result); $i < $c; $i++)
		{
			$fieldnames[] = mysql_field_name($result, $i);
		}

		$insertstatement = 'INSERT INTO '.QUOTECHAR.$tables[$t].QUOTECHAR.' ('.QUOTECHAR.implode(QUOTECHAR.', '.QUOTECHAR, $fieldnames).QUOTECHAR.') VALUES (';
		$currentrow       = 0;
		$thistableinserts = '';

		while (($row = $dbConnector->GetNextArrayRecord()) != false)
		{
			unset($valuevalues);
			foreach ($fieldnames as $key => $val)
			{
				if (!isset($row[$key]) || (isset($row[$key]) && $row[$key] === null))
				{
					$valuevalues[] = 'NULL';
				}
				else
				{
					switch ($RowTypes[$tables[$t]][$val])
					{
						// binary data dump, two hex characters per byte
						case 'tinyblob':
						case 'blob':
						case 'mediumblob':
						case 'longblob':
							$data = $row[$key];
							$data_len = strlen($data);
							if ($HexBLOBs && $data_len)
							{
								$hexstring = '0x';
								for ($i = 0; $i < $data_len; $i++)
								{
									$hexstring .= str_pad(dechex(ord($data{$i})), 2, '0', STR_PAD_LEFT);
								}
								$valuevalues[] = $hexstring;
							}
							else
							{
								$valuevalues[] = '\''.mysql_escape_string($data).'\'';
							}
							break;

						// just the (numeric) value, not surrounded by quotes
						case 'tinyint':
						case 'smallint':
						case 'mediumint':
						case 'int':
						case 'bigint':
						case 'float':
						case 'double':
						case 'decimal':
						case 'year':
							$valuevalues[] = mysql_escape_string($row[$key]);
							break;

						// value surrounded by quotes
						case 'varchar':
						case 'char':
						case 'tinytext':
						case 'text':
						case 'mediumtext':
						case 'longtext':
						case 'enum':
						case 'set':
						case 'date':
						case 'datetime':
						case 'time':
						case 'timestamp':
						default:
							$valuevalues[] = '\''.mysql_escape_string($row[$key]).'\'';
							break;
					}
				}
			}
			$thistableinserts .= $insertstatement.implode(', ', $valuevalues).');'.AP_CRLF;
			WriteBackupFile($fp, $thistableinserts);
		}
	}

	@$db->Disconnect();
	@fclose($fp);

	/**/
	if ($BackupIsGood)
	{
		echo '<br /><br />Backup file saved in <b>'.BACKUPFILENAME.'</b>';
		echo '<br /><font color="'.COLOR_GREEN.'"><b>Backup done!</b></font>';
	}
	else
	{
		echo '<br /><br /><font color="'.COLOR_RED.'"><b>Backup failed!</b></font></font>';
	}
	/**/
	echo '</body></html>';
	myFlush();

	## Functions

	function myFlush()
	{
		@flush();
	}

	function WriteBackupFile(&$fp, $text)
	{
		fwrite($fp, $text, strlen($text));
	}

