<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));

	require_once(WM_ROOTPATH.'common/class_log.php');
	require_once(WM_ROOTPATH.'common/inc_constants.php');


    if (!defined('DB_FETCH_RESULT_TYPE_OBJECT'))
    {
        define('DB_FETCH_RESULT_TYPE_OBJECT', '');
    }
    if (!defined('DB_FETCH_RESULT_TYPE_ARRAY'))
    {
        define('DB_FETCH_RESULT_TYPE_ARRAY', '');
    }
    if (!defined('DB_FETCH_RESULT_TYPE_ASSOC'))
    {
        define('DB_FETCH_RESULT_TYPE_ASSOC', '');
    }

	class DbGeneralSql
	{
		/**
		 * @var resource
		 */
		var $_conectionHandle;

		/**
		 * @var resource
		 */
		var $_resultId;
		
		/**
		 * @var int
		 */
		var $ErrorCode;

		/**
		 * @var string
		 */
		var $ErrorDesc = '';
		
		/**
		 * @access protected
		 * @var CLog
		 */
		var $_log;

		/**
		 * @return	bool
		 */
		function IsConnected()
		{
			return is_resource($this->_conectionHandle);
		}

        /**
         * @return bool
         */
        function FreeResult(){}

        /**
         * @param bool $autoFree optional
		 * @return object
		 */
		function &GetNextRecord($autoFree = true)
		{
            return $this->_getNextRow($autoFree, DB_FETCH_RESULT_TYPE_OBJECT);
		}

		/**
		 * @param bool $autoFree optional
		 * @return array
		 */
		function &GetNextArrayRecord($autoFree = true)
		{
            return $this->_getNextRow($autoFree, DB_FETCH_RESULT_TYPE_ARRAY);
		}

        /**
		 * @param bool $autoFree optional
		 * @return array
		 */
        function &GetNextAssocArrayRecord($autoFree = true)
		{
            return $this->_getNextRow($autoFree, DB_FETCH_RESULT_TYPE_ASSOC);
        }

        function &_getNextRow($autoFree = true, $fetchFuncName = DB_FETCH_RESULT_TYPE_OBJECT)
        {
            if ($this->_resultId)
			{
                $result = $this->_fetchRow($fetchFuncName);
				if (!$result && $autoFree)
				{
					$this->FreeResult();
				}
				return $result;
			}
			$this->_setSqlError();
			return false;
        }

        function _fetchRow($fetchFuncName = DB_FETCH_RESULT_TYPE_OBJECT)
        {
            if (!is_callable($fetchFuncName))
            {
                return false;
            }
            return @$fetchFuncName($this->_resultId);
        }

        /**
         * @return array
         */
        function &GetResultAsAssocArray()
        {
            $result = array();
            while (false != ($row = $this->GetNextAssocArrayRecord(false)))
            {
                $result[] = $row;
            }
            $this->FreeResult();
            return $result;
        }

	}
	
	class DbSql extends DbGeneralSql
	{
		/**
		 * @var string
		 */
		var $_host;
		
		/**
		 * @var string
		 */
		var $_user;
		
		/**
		 * @var string
		 */
		var $_password;
		
		/**
		 * @var string
		 */
		var $_dbName;
	}
