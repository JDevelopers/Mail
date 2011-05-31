<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	/**
	 * @abstract
	 */
	class baseMain_CommandCreator
	{
		/**
		 * @var	int
		 */
		var $_escapeType;
		
		/**
		 * @var	string
		 */
		var $_prefix = '';
		
		/**
		 * @var	array
		 */
		var $_escapeColumn;
		
		/**
		 * @param	int		$type
		 * @param	array	$columnEscape
		 * @param	string	$prefix
		 * @return	baseMain_CommandCreator
		 */
		function baseMain_CommandCreator($type, $columnEscape = array('', ''), $prefix = '')
		{
			$this->_escapeType = $type;
			$this->_escapeColumn = count($columnEscape) == 2 ? $columnEscape : array('', '');
			$this->_prefix = $prefix;
		}
		
		/**
		 * @param	string	$str
		 * @return	string
		 */
		function _escapeString($str)
		{
			if ($str === '' || $str === null) return "''";
			switch ($this->_escapeType)
			{
				case AP_DB_QUOTE_ESCAPE:
					return "'".addslashes($str)."'";
				case AP_DB_QUOTE_DOUBLE:
					return "'".str_replace("'", "''", $str)."'";
				default:
					return "'".$str."'";
			}
		}
		
		/**
		 * @param	array	$array
		 * @return	array
		 */
		function _escapeArray($array)
		{
			return array_map(array(&$this, '_escapeString'), $array);
		}
		
		/**
		 * @param	string	$str
		 * @return	string
		 */
		function _escapeColumn($str)
		{
			if (strlen($str) == 0) 
			{
				return $str;
			}
			return $this->_escapeColumn[0].$str.$this->_escapeColumn[1];
		}
	}