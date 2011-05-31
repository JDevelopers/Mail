<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	class DbMSSql extends DbSql
	{
		/**
		 * @param	string	$host
		 * @param	string	$user
		 * @param	string	$password
		 * @param	string	$dbName
		 * @return	DbMSSql
		 */
		function DbMSSql($host, $user, $password, $dbName)
		{
			$this->_host = $host;
			$this->_user = $user;
			$this->_password = $password;
			$this->_dbName = $dbName;
		}
		
		/**
		 * @return	bool
		 */
		function Connect($withSelect = true)
		{
			$this->ErrorCode = 0;
			if (!extension_loaded('mssql'))
			{
				$this->ErrorDesc = 'Can\'t load MsSQL extension.';
				return false;
			}
			
			if (strlen($this->_host) == 0 || strlen($this->_user) == 0 || strlen($this->_dbName) == 0)
			{
				$this->ErrorDesc = 'Not enough details required to establish connection.';
				return false;
			}
			
			$this->_conectionHandle = @mssql_connect($this->_host, $this->_user, $this->_password);
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
			if(strlen($this->_dbName) > 0)
			{
				$dbselect = @mssql_select_db($this->_dbName, $this->_conectionHandle);
				if(!$dbselect)
				{
					$this->_setSqlError();
					$this->_conectionHandle = $dbselect;
					@mssql_close($this->_conectionHandle);
					return false;
				}
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
			if($this->_conectionHandle)
			{
				if($this->_resultId)
				{
					@mssql_free_result($this->_resultId);
					$this->_resultId = null;
				}
				$result = @mssql_close($this->_conectionHandle);
				$this->_conectionHandle = null;
				return $result;
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
			CAdminPanel::Log('DB'.(++$GLOBALS[AP_DB_COUNT]).' > '.$query);
			$this->_resultId = @mssql_query(trim($query), $this->_conectionHandle);
			if($this->_resultId === false)
			{
				$this->_setSqlError();
			}

			return ($this->_resultId !== false);
		}
			
		/**
		 * @param	bool	$autoFree[optional] = optional
		 * @return	object
		 */
		function &GetNextRecord($autoFree = true)
		{
			if($this->_resultId)
			{
				$result = @mssql_fetch_object($this->_resultId);
				if (!$result && $autoFree)
				{
					$this->FreeResult();
				}
				
				/* MSSQL-PHP Empty field bug fix. see http://bugs.php.net/bug.php?id=26315 */
				if ($result) 
				{
					$fields = array_keys(get_object_vars($result));
					foreach ($fields as $name)
					{
						if ($result->$name == ' ')
						{
							$result->$name = '';
						}
					}
					unset($fields);
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
		 * @param	bool	$autoFree[optional] = optional
		 * @return	array
		 */
		function &GetNextArrayRecord($autoFree = true)
		{
			if($this->_resultId)
			{
				$result = @mssql_fetch_array($this->_resultId);
				if (!$result && $autoFree)
				{
					$this->FreeResult();
				}
				
				/* MSSQL-PHP Empty field bug fix. see http://bugs.php.net/bug.php?id=26315 */
				if ($result) 
				{
					$fields = array_keys($result);
					foreach ($fields as $name)
					{
						if ($result[$name] == ' ')
						{
							$result[$name] = '';
						}
					}
					unset($fields);
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
		 * @return	int
		 */
		function GetLastInsertId()
		{
			if ($this->Execute('SELECT SCOPE_IDENTITY() AS [identity]'))
			{
				$insertId = -1;
				while (false !== ($row = $this->GetNextRecord()))
				{
					$insertId = $row->identity;
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
				if (!@mssql_free_result($this->_resultId))
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
		    return @mssql_num_rows($this->_resultId);
		}
		
		function _setSqlError($errmess = '')
		{
			$this->ErrorCode = 0;
			$this->ErrorDesc = ($errmess) ? $errmess : @mssql_get_last_message();
			
			if (strlen($this->ErrorDesc) > 0)
			{
				CAdminPanel::Log('DB ERROR < '.trim($this->ErrorDesc));
			}
		}
	}