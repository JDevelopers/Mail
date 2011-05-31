<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	class DbMySql extends DbSql
	{
		/**
		 * @param	string	$host
		 * @param	string	$user
		 * @param	string	$password
		 * @param	string	$dbName
		 * @return	DbMySql
		 */
		function DbMySql($host, $user, $password, $dbName)
		{
			$this->_host = trim($host);
			$this->_user = trim($user);
			$this->_password = trim($password);
			$this->_dbName = trim($dbName);
		}
		
		/**
		 * @return	bool
		 */
		function Connect($withSelect = true)
		{
			$this->ErrorCode = 0;
			if (!extension_loaded('mysql'))
			{
				$this->ErrorDesc = 'Can\'t load MySQL extension.';
				return false;
			}

			if (strlen($this->_host) == 0 || strlen($this->_user) == 0 || strlen($this->_dbName) == 0)
			{
				$this->ErrorDesc = 'Not enough details required to establish connection.';
				return false;
			}
			
			@ini_set('mysql.connect_timeout', 3);
			
			$this->_conectionHandle = @mysql_connect($this->_host, $this->_user, $this->_password);
			if ($this->_conectionHandle)
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
			if (strlen($this->_dbName) > 0)
			{
				$dbselect = @mysql_select_db($this->_dbName, $this->_conectionHandle);
				if(!$dbselect)
				{
					$this->_setSqlError();
					$this->_conectionHandle = $dbselect;
					@mysql_close($this->_conectionHandle);
					return false;
				}
				
				/*
				@mysql_query('SET @@collation_connection = @@collation_database', $this->_conectionHandle);
				@mysql_query('SET CHARACTER utf8', $this->_conectionHandle);
				*/
				@mysql_query('SET NAMES utf8', $this->_conectionHandle);
				
				return true;
			}
			return false;
		}
		
		/**
		 * @return	bool
		 */
		function Disconnect()
		{
			$result = true;
			if ($this->_conectionHandle)
			{
				if($this->_resultId)
				{
					@mysql_free_result($this->_resultId);
					$this->_resultId = null;
				}
				$result = @mysql_close($this->_conectionHandle);
				$this->_conectionHandle = null;
				return $result;
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
			$this->_resultId = @mysql_query(trim($query), $this->_conectionHandle);
			if ($this->_resultId === false)
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
			if ($this->_resultId)
			{
				$result = @mysql_fetch_object($this->_resultId);
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
		 * @param	bool	$autoFree[optional] = true
		 * @return	array
		 */
		function &GetNextArrayRecord($autoFree = true)
		{
			if ($this->_resultId)
			{
				$result = @mysql_fetch_array($this->_resultId);
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
			return @mysql_insert_id();
		}
		
		/**
		 * @return	bool
		 */
		function FreeResult()
		{
			if ($this->_resultId)
			{
				if (!@mysql_free_result($this->_resultId))
				{
					$this->_setSqlError();
					return false;
				}
				else 
				{
					$this->_resultId = null;
				}
			}
			return true;
		}
		
		/**
		 * @return	int
		 */
		function ResultCount()
		{
		    return @mysql_num_rows($this->_resultId);
		}
		
		function _setSqlError()
		{
			if ($this->_conectionHandle)
			{
				$this->ErrorDesc = @mysql_error($this->_conectionHandle);
				$this->ErrorCode = @mysql_errno($this->_conectionHandle);
			}
			else
			{
				$this->ErrorDesc = @mysql_error();
				$this->ErrorCode = @mysql_errno();
			}
			
			if (strlen($this->ErrorDesc) > 0)
			{
				CAdminPanel::Log('DB ERROR < '.trim($this->ErrorDesc));
			}
		}
		
	}