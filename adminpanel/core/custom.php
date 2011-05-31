<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	class ap_Custom
	{
		/**
		 * var ap_CustomDataClass
		 */
		var $_driver;
		
		function ap_Custom()
		{
			$this->_driver = null;
			if (class_exists('ap_CustomDataClass'))
			{
				$this->_driver = new ap_CustomDataClass();
			}
		}
		
		/**
		 * @param string $name
		 * @param array $arg = array()
		 */
		function UseMethod($name, $arg = array())
		{
			if (null !== $this->_driver && $this->MethodExist($name))
			{
				if (count($arg) > 0)
				{
					call_user_func_array(array(&$this->_driver, $name), $arg);
				}
				else
				{
					call_user_func(array(&$this->_driver, $name));
				}
				return true;
			}
			return false;
		}

		/**
		 * @param string $name
		 * @param array $arg = array()
		 */
		function StaticUseMethod($name, $arg = array())
		{
			$c =& ap_Custom::CreateInstance();
			return $c->UseMethod($name, $arg);
		}

		/**
		 * @param string $name
		 * return bool
		 */
		function MethodExist($name)
		{
			return (null !== $this->_driver && method_exists($this->_driver , $name) &&
				$name != 'CreateInstance' && $name != 'UseMethod' && $name != 'MethodExist');
		}

		/**
		 * @param string $name
		 * return bool
		 */
		function StaticMethodExist($name)
		{
			$c =& ap_Custom::CreateInstance();
			return $c->MethodExist($name);
		}

		/**
		 * @return ap_Custom
		 */
		function &CreateInstance()
		{
			static $instance;
			if (!is_object($instance))
			{
				$instance = new ap_Custom();
			}
			return $instance;
		}
	}
	