<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the proprietary AfterLogic Computer License (LICENSE.txt)
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));

	/**
     * these constants are used for fetch functions family(declarated in DbGeneralSql)
     */
    define('DB_FETCH_RESULT_TYPE_OBJECT', 'mysql_fetch_object');
    define('DB_FETCH_RESULT_TYPE_ARRAY', 'mysql_fetch_array');
    define('DB_FETCH_RESULT_TYPE_ASSOC', 'mysql_fetch_assoc');


	require_once(WM_ROOTPATH.'db/class_dbsql.php');

    
	class DbMySql extends DbSql
	{
		/**
		 * @param string $host
		 * @param string $user
		 * @param string $password
		 * @param string $dbName
		 * @return DbMySql
		 */
		function DbMySql($host, $user, $password, $dbName)
		{
			$this->_host = $host;
			$this->_user = $user;
			$this->_password = $password;
			$this->_dbName = $dbName;
			$this->_log =& CLog::CreateInstance();
		}
		
		/**
		 * @return bool
		 */
		function Connect()
		{
			if (!extension_loaded('mysql'))
			{
				$this->ErrorDesc = 'Can\'t load MySQL extension.';
				setGlobalError($this->ErrorDesc);
				$this->_log->WriteLine($this->ErrorDesc, LOG_LEVEL_ERROR);
				return false;
			}

			$ti = 0;
			if ($this->_log->Enabled)
			{
				$ti = getmicrotime();
			}
			
			$this->_conectionHandle = @mysql_connect($this->_host, $this->_user, $this->_password);
			if ($this->_conectionHandle && $this->_log->Enabled)
			{
				$this->_log->WriteLine(':: connection time -> '. (getmicrotime() - $ti));
			}
		
			if ($this->_conectionHandle)
			{
				if(strlen($this->_dbName) > 0)
				{
					$dbselect = @mysql_select_db($this->_dbName, $this->_conectionHandle);
					if(!$dbselect)
					{
						$this->_setSqlError();
						$this->_conectionHandle = $dbselect;
						@mysql_close($this->_conectionHandle);
						return false;
					}
				}
				
				/*
				@mysql_query('SET @@collation_connection = @@collation_database', $this->_conectionHandle);
				@mysql_query("SET CHARACTER utf8", $this->_conectionHandle);
				*/
				@mysql_query('SET NAMES utf8', $this->_conectionHandle);
				
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
			$result = true;
			if ($this->_conectionHandle)
			{
                $this->FreeResult();
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
		 * @param string $query
		 * @return mixed
		 */
		function Execute($query)
		{
			$query = ConvertUtils::mainClear($query);
			if ($this->_log->Enabled)
			{
				$this->_log->WriteLine('MySQL Query: '.$query);
			}

			if (is_resource($this->_conectionHandle))
			{
				$this->_resultId = @mysql_query($query, $this->_conectionHandle);
			}
			else
			{
				$this->_log->WriteLine('MySQL Error: not connected', LOG_LEVEL_ERROR);
			}

			if ($this->_resultId)
			{
				return $this->_resultId;
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
			return @mysql_insert_id();
		}
		
		/**
		 * @return bool
		 */
		function FreeResult()
		{
			if (is_resource($this->_resultId))
			{
				@mysql_free_result($this->_resultId);
			}
			$this->_resultId = null;
			return true;
		}
		
		/**
		 * @return int
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

			setGlobalError($this->ErrorDesc);
			$this->_log->WriteLine('ErrorDesc ['.$this->ErrorCode.']: '.$this->ErrorDesc, LOG_LEVEL_ERROR);
		}
		
	}