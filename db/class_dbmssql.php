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
    define('DB_FETCH_RESULT_TYPE_OBJECT', 'mssql_fetch_object');
    define('DB_FETCH_RESULT_TYPE_ARRAY', 'mssql_fetch_array');
    define('DB_FETCH_RESULT_TYPE_ASSOC', 'mssql_fetch_assoc');

    
	require_once(WM_ROOTPATH.'db/class_dbsql.php');
    
	class DbMsSql extends DbSql
	{
		/**
		 * @param string $host
		 * @param string $user
		 * @param string $password
		 * @param string $dbName
		 * @return DbMsSql
		 */
		function DbMsSql($host, $user, $password, $dbName)
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
			//if ($this->_conectionHandle != false) return true;
			if (!extension_loaded('mssql'))
			{
				$this->ErrorDesc = 'Can\'t load MsSQL extension.';
				setGlobalError($this->ErrorDesc);
				$this->_log->WriteLine($this->ErrorDesc, LOG_LEVEL_ERROR);
				return false;
			}
			
			if ($this->_log->Enabled)
			{
				$ti = getmicrotime();
			}
			$this->_conectionHandle = @mssql_connect($this->_host, $this->_user, $this->_password);
			if ($this->_conectionHandle && $this->_log->Enabled)
			{
				$this->_log->WriteLine(':: connection time -> '. (getmicrotime() - $ti));
			}
			
			if($this->_conectionHandle)
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
			$result = true;
			if($this->_conectionHandle)
			{
                $this->FreeResult();
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
			$query = ConvertUtils::mainClear($query);
			if ($this->_log->Enabled)
			{
				$this->_log->WriteLine('MSSQL Query: '.$query);
				/* $ti = getmicrotime();*/ 
			}

			if (is_resource($this->_conectionHandle))
			{
				$this->_resultId = @mssql_query($query, $this->_conectionHandle);
				/* if ($this->_resultId && $this->_log->Enabled)
				{
					$this->_log->WriteLine(':: time -> '. (getmicrotime() - $ti));
				}*/
			}
			else
			{
				$this->_log->WriteLine('MSSQL Error: not connected', LOG_LEVEL_ERROR);
			}

			if($this->_resultId === false)
			{
				$this->_setSqlError();
				return false;
			}
			else 
			{
				return $this->_resultId;
			}
		}
			
        function _fetchRow($fetchFuncName = DB_FETCH_RESULT_TYPE_OBJECT)
        {
            $result = false;
            if (is_callable($fetchFuncName))
            {
                $result = @$fetchFuncName($this->_resultId);
                if ($result) //MSSQL-PHP Empty field bug fix. see http://bugs.php.net/bug.php?id=26315
                {
                    if (is_object($result))
                    {
                        $fields = get_object_vars($result);
    					foreach ($fields as $key => $value)
        				{
            				if (' ' == $value)
                			{
                    			$result->$key = '';
                        	}
                        }
                    }
                    else if (is_array($result))
                    {
                        foreach ($result as $key => $value)
            			{
                			if (' ' == $value)
                    		{
                        		$result[$key] = '';
                            }
                        }
                    }
                }
            }
            return $result;
        }

		/**
		 * @return int
		 */
		function GetLastInsertId()
		{
			if ($this->Execute('SELECT SCOPE_IDENTITY() AS [identity]'))
			{
				$insertId = -1;
				while (($row =& $this->GetNextRecord()) != false)
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
		 * @return bool
		 */
		function FreeResult()
		{
			if (is_resource($this->_resultId))
			{
				@mssql_free_result($this->_resultId);
            }
            $this->_resultId = null;
            return true;
		}
		
		/**
		 * @return int
		 */
		function ResultCount()
		{
		    return @mssql_num_rows($this->_resultId);
		}
		
		/**
		 * @access private
		 */
		function _setSqlError()
		{
			$this->ErrorCode = 0;
			$this->ErrorDesc = @mssql_get_last_message();
			
			setGlobalError($this->ErrorDesc);
			$this->_log->WriteLine('SQL Error: '.$this->ErrorDesc, LOG_LEVEL_ERROR);
		}
		
	}