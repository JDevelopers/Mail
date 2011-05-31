<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	class DbOdbc extends DbGeneralSql
	{
		/**
		 * @var	string
		 */
		var $_dbCustomConnectionString;
		
		/**
		 * @var	int
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
		 * @param	string	$customConnectionString
		 * @param	string	$user[optional] = ''
		 * @param	string	$password[optional] = ''
		 * @return	DbOdbc
		 */
		function DbOdbc($customConnectionString, $dbType, $user = '', $pass = '')
		{
			$this->_dbCustomConnectionString = $customConnectionString;
			$this->_dbType = $dbType;
			
			$this->_user = $user;
			$this->_pass = $pass;
		}
		
		/**
		 * @return	bool
		 */
		function Connect($withSelect = true)
		{
			$this->ErrorCode = 0;
			if (!extension_loaded('odbc'))
			{
				$this->ErrorDesc = 'Can\'t load ODBC extension.';
				return false;
			}
			
			if ($this->_dbType == AP_DB_MSSQLSERVER && (strlen($this->_dbCustomConnectionString) == 0 || strlen($this->_user) == 0))
			{
				$this->ErrorDesc = 'Not enough details required to establish connection.';
				return false;
			}

			$this->_conectionHandle = @odbc_connect($this->_dbCustomConnectionString, $this->_user, $this->_pass, SQL_CUR_USE_ODBC);
			if($this->_conectionHandle)
			{
				return ($withSelect) ? $this->Select() : true; 
			}
			else 
			{
				$this->_setSqlError();
				return false;
			}
		}
		
		/**
		 * @return	bool
		 */
		function ConnectNoSelect()
		{
			return $this->Connect(false);
		}
		
		function Select()
		{
			if ($this->_dbType == AP_DB_MYSQL)
			{
				@odbc_exec($this->_conectionHandle, 'SET NAMES utf8');
			}
			return true;
		}

		/**
		 * @return	bool
		 */
		function Disconnect()
		{
			if($this->_conectionHandle)
			{
				if($this->_resultId)
				{
					@odbc_free_result($this->_resultId);
					$this->_resultId = null;
				}
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
		 * @param	string	$query
		 * @return	bool
		 */
		function Execute($query)
		{
			CAdminPanel::Log('DB'.(++$GLOBALS[AP_DB_COUNT]).' > '.$query);
			$this->_resultId = @odbc_exec($this->_conectionHandle, trim($query));
			if($this->_resultId === false)
			{
				$this->_setSqlError();
			}
			
			return ($this->_resultId !== false);
		}
			
		/**
		 * @param	bool	$autoFree[optional] = true
		 * @return	object
		 */
		function &GetNextRecord($autoFree = true)
		{
			if($this->_resultId)
			{
				$result = @odbc_fetch_object($this->_resultId);
				if (!$result && $autoFree)
				{
					$this->FreeResult();
				}
				return $result;
			}
			else
			{
				$this->_setSqlError();
				return false;
			}		
		}
		
		/**
		 * @param	bool	$autoFree[optional] = true
		 * @return	array
		 */
		function &GetNextArrayRecord($autoFree = true)
		{
			if ($this->_resultId)
			{
				$result = @odbc_fetch_array($this->_resultId);
				if (!$result && $autoFree)
				{
					$this->FreeResult();
				}

				return $result;
			}
			else
			{
				$null = null;
				$this->_setSqlError();
				return $null;
			}		
		}
				
		/**
		 * @return	int
		 */
		function GetLastInsertId()
		{	
			switch ($this->_dbType)
			{
				case AP_DB_MSSQLSERVER:
					$result = $this->Execute('SELECT @@IDENTITY AS ident');
					break;
				case AP_DB_MYSQL:
					$result = $this->Execute('SELECT LAST_INSERT_ID() AS ident');
					break;
				/*case AP_DB_PGSQL:
					$result = $this->Execute('SELECT lastval() AS ident');
					break;*/
				default:
					$result = $this->Execute('SELECT @@IDENTITY AS ident');
					break;
			}

			if ($result)
			{
				$insertId = -1;
				while (false !== ($row =& $this->GetNextRecord()))
				{
					$insertId = $row->ident;
					break;
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
		 * @return	bool
		 */
		function FreeResult()
		{
			if ($this->_resultId)
			{
				if (!@odbc_free_result($this->_resultId))
				{
					$this->_setSqlError();
					return false;
				}
				else 
				{
					$this->_resultId = null;
				}
				return true;
			}
			else 
			{
				return true;
			}
		}
		
		/**
		 * @return	int
		 */
		function ResultCount()
		{
		    return @odbc_num_rows($this->_resultId);
		}
		
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
			
			if (strlen($this->ErrorDesc) > 0)
			{
				CAdminPanel::Log('DB ERROR < '.trim($this->ErrorDesc));
			}
		}
	}