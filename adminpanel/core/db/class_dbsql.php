<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	class DbGeneralSql
	{
		/**
		 * @var	resource
		 */
		var $_conectionHandle;
		
		/**
		 * @var	resource
		 */
		var $_resultId;
		
		/**
		 * @var	int
		 */
		var $ErrorCode;

		/**
		 * @var	string
		 */
		var $ErrorDesc = '';

		/**
		 * @return	resource
		 */
		function &GetResult()
		{
			return $this->_resultId;
		}
	}
	
	class DbSql extends DbGeneralSql
	{
		/**
		 * @var	string
		 */
		var $_host;
		
		/**
		 * @var	string
		 */
		var $_user;
		
		/**
		 * @var	string
		 */
		var $_password;
		
		/**
		 * @var	string
		 */
		var $_dbName;
		
	}