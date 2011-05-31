<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));
    
	/**
     * these constants are used for fetch functions family (declarated in DbGeneralSql)
     */
    define('DB_FETCH_RESULT_TYPE_OBJECT', 'odbc_fetch_object');
    define('DB_FETCH_RESULT_TYPE_ARRAY', 'odbc_fetch_array');
    define('DB_FETCH_RESULT_TYPE_ASSOC', 'odbc_fetch_array');

	require_once(WM_ROOTPATH.'db/class_dbsql.php');
	require_once(WM_ROOTPATH.'common/class_mailstorage.php');

	class DbOdbc extends DbGeneralSql
	{
		/**
		 * @access private
		 * @var string
		 */
		var $_dbCustomConnectionString;
		
		/**
		 * @access private
		 * @var short
		 */
		var $_dbType;
		
		/**
		 * @var	string
		 */
		var $_user;
		
		/**
		 * @var string
		 */
		var $_pass;

		/**
		 * @param string $customConnectionString
		 * @return DbOdbc
		 */
		function DbOdbc($customConnectionString, $dbType, $user = '', $pass = '')
		{
			$this->_dbCustomConnectionString = $customConnectionString;
			$this->_dbType = $dbType;
			
			$this->_user = $user;
			$this->_pass = $pass;
			$this->_log =& CLog::CreateInstance();
		}
		
		/**
		 * @return bool
		 */
		function Connect()
		{
			if (!extension_loaded('odbc'))
			{
				$this->ErrorDesc = 'Can\'t load ODBC extension.';
				setGlobalError($this->ErrorDesc);
				$this->_log->WriteLine($this->ErrorDesc, LOG_LEVEL_ERROR);
				return false;
			}
			
			if ($this->_log->Enabled)
			{
				$ti = getmicrotime();
			}
			
			$this->_conectionHandle = @odbc_connect($this->_dbCustomConnectionString, $this->_user, $this->_pass, SQL_CUR_USE_ODBC);
			if ($this->_conectionHandle && $this->_log->Enabled)
			{
				$this->_log->WriteLine(':: connection time -> '. (getmicrotime() - $ti));
			}
				
			if ($this->_conectionHandle)
			{
				if ($this->_dbType == DB_MYSQL)
				{
					@odbc_exec($this->_conectionHandle, 'SET NAMES utf8');
				}
				return true;
			}
			else 
			{
				$this->_setSqlError();
				return false;
			}
		}

		/**
		 * @return bool
		 */
		function Disconnect()
		{
			if($this->_conectionHandle)
			{
				$this->FreeResult();
				@odbc_close($this->_conectionHandle);
				$this->_conectionHandle = null;
				return true;
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * @param string $query
		 * @return mixed
		 */
		function Execute($query)
		{
			$query = ConvertUtils::mainClear($query);
			if ($this->_log->Enabled)
			{
				$this->_log->WriteLine('ODBC Query: '.$query);
				/* $ti = getmicrotime(); */
			}
			if (is_resource($this->_conectionHandle))
			{
				$this->_resultId = @odbc_exec($this->_conectionHandle, $query);
				/*if ($this->_resultId && $this->_log->Enabled)
				{
					$this->_log->WriteLine(':: time -> '. (getmicrotime() - $ti));
				}*/
			}
			else
			{
				$this->_log->WriteLine('ODBC Error: not connected', LOG_LEVEL_ERROR);
			}
			
			if($this->_resultId)
			{
				return $this->_resultId !== false;
			}
			else 
			{
				$this->_setSqlError();
				return false;
			}
		}
			
		/**
		 * @return int
		 */
		function GetLastInsertId()
		{
			$result = false;
			switch ($this->_dbType)
			{
				case DB_MSSQLSERVER:
					$result = $this->Execute('SELECT SCOPE_IDENTITY() ident');
					break;
				case DB_MYSQL:
					$result = $this->Execute('SELECT LAST_INSERT_ID() AS ident');
					break;
				default:
					$result = $this->Execute('SELECT @@IDENTITY AS ident');
			}

			if ($result)
			{
				$insertId = -1;
				while (($row =& $this->GetNextRecord()) != false)
				{
					$insertId = $row->ident;
				}
				return $insertId;
			}
			else
			{
				$this->_setSqlError();
				return -1;
			}
		}
		
		/**
		 * @return bool
		 */
		function FreeResult()
		{
			if (is_resource($this->_resultId))
			{
				@odbc_free_result($this->_resultId);
			}
			$this->_resultId = null;
            return true;
		}
		
		/**
		 * @return int
		 */
		function ResultCount()
		{
		    return @odbc_num_rows($this->_resultId);
		}
		
		/**
		 * @access private
		 */
		function _setSqlError($errorDesc = '')
		{
			if ($errorDesc)
			{
				$this->ErrorDesc = $errorDesc;
				$this->ErrorCode = 0;	
			}
			elseif ($this->_conectionHandle)
			{
				$this->ErrorDesc = @odbc_errormsg($this->_conectionHandle);
				$this->ErrorCode = @odbc_error($this->_conectionHandle);
			}
			else
			{
				$this->ErrorDesc = @odbc_errormsg();
				$this->ErrorCode = @odbc_error();
			}

			setGlobalError($this->ErrorDesc);
			$this->_log->WriteLine('ErrorDesc: '.$this->ErrorDesc."\tErrorCode ".$this->ErrorCode, LOG_LEVEL_ERROR);
		}
		
	}