<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	class CExminUserMini
	{
		/**
		 * @var	string
		 */
		var $_login = '';
		
		function SetSessionArray()
		{
			$array = array(
'_login' => $this->_login
				);
					
			$_SESSION[WM_SESS_USERMINI] = $array;
		}
		
		function ClearSessionArray()
		{
			if (isset($_SESSION[WM_SESS_USERMINI]))
			{
				unset($_SESSION[WM_SESS_USERMINI]);
			}
		}		

		/**
		 * @return bool
		 */
		function IsSessionData()
		{
			return isset($_SESSION[WM_SESS_USERMINI]);
		}
		
		function UpdateFromSessionArray()
		{
			$sessionArray = isset($_SESSION[WM_SESS_USERMINI]) ? $_SESSION[WM_SESS_USERMINI] : array();
			if (count($sessionArray) > 0)
			{
				$this->_login = ap_Utils::ArrayValue($sessionArray, '_login', $this->_login);
				
				$this->ClearSessionArray();
			}
		}
	}