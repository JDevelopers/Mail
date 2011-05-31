<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	defined('CM_DB_LIBS_PATH') || define('CM_DB_LIBS_PATH', ap_Utils::PathPreparation(dirname(__FILE__)));
	
	include_once CAdminPanel::RootPath().'/core/db/class_dbsql.php';

	class mainCommon_DbStorage
	{
		/**
		 * @var	DbMySql
		 */
		var $_connector;
		
		/**
		 * @var	MySQL_CommandCreator
		 */
		var $_commandCreator;
		
		/**
		 * @var	WebMail_Settings
		 */
		var $_settings;

		/**
		 * @return	DbMySql
		 */
		function &GetConnector()
		{
			return $this->_connector;
		}

		/**
		 * @return	bool
		 */
		function Connect()
		{
			if ($this->_connector->_conectionHandle != null)
			{
				return true;
			}
			return $this->_connector->Connect();
		}
		
		/**
		 * @return	bool
		 */
		function ConnectNoSelect()
		{
			if ($this->_connector->_conectionHandle != null)
			{
				return true;
			}
			return $this->_connector->ConnectNoSelect();
		}
		
		/**
		 * @return	bool
		 */
		function Disconnect()
		{
			return $this->_connector->Disconnect();
		}
		
		/**
		 * @return	bool
		 */		
		function Select()
		{
			return $this->_connector->Select();
		}
		
		/**
		 * @return	string
		 */
		function GetError()
		{
			return '#'.$this->_connector->ErrorCode.': '.$this->_connector->ErrorDesc;
		}

		/**
		 * @return	array | false
		 */
		function GetDomainArrayForSelect()
		{
			if ($this->_connector->Execute($this->_commandCreator->GetDomainArray()))
			{
				$domains = array();
				$domains[0] = ap_Utils::TakePhrase('CM_NOT_DOMAIN_SELECT_NAME');
				while (($row = $this->_connector->GetNextRecord()) != false)
				{
					$domains[$row->id_domain] = $row->name;
				}
				return $domains;
			}
			return false;
		}

		function IsSubAdminExist($subAdmin)
		{
			if (strtolower($subAdmin->Login) === 'mailadm' || (strlen(AP_DEMO_LOGIN) > 0 && strtolower($subAdmin->Login) === AP_DEMO_LOGIN))
			{
				return true;
			}
			if (!$this->_connector->Execute($this->_commandCreator->IsSubAdminExist($subAdmin)))
			{
				return true;
			}

			$row = $this->_connector->GetNextRecord();
			return ($row && (int) $row->sabadmin_cnt > 0);
		}

		/**
		 * @param	CCommonSubAdmin	$newSubadmin
		 * @return	bool
		 */
		function CreateSubAdmin(&$newSubadmin)
		{
			if ($this->_connector->Execute($this->_commandCreator->CreateSubAdmin($newSubadmin)))
			{
				$newSubadmin->Id = $this->_connector->GetLastInsertId();
				if (count($newSubadmin->DomainIds) > 0)
				{
					return $this->_connector->Execute($this->_commandCreator->AddSubAdminDomain($newSubadmin->Id, $newSubadmin->DomainIds));
				}
				return true;
			}
			
			return false;
		}

		/**
		 * @param	CCommonSubAdmin	$subAdmin
		 * @return	bool
		 */
		function UpdateSubAdmin(&$subAdmin)
		{
			if ($this->_connector->Execute($this->_commandCreator->UpdateSubAdmin($subAdmin)))
			{
				$this->_connector->Execute($this->_commandCreator->ClearSubAdminDomains($subAdmin->Id));
				if (count($subAdmin->DomainIds) > 0)
				{
					return $this->_connector->Execute($this->_commandCreator->AddSubAdminDomain($subAdmin->Id, $subAdmin->DomainIds));
				}
				return true;
			}

			return false;
		}

		/**
		 * @param	string	$condition[optional] = null
		 * @return	int
		 */
		function SubAdminCount($condition = null)
		{
			$cnt = 0;
			if (!$this->_connector->Execute($this->_commandCreator->SubAdminCount($condition)))
			{
				return false;
			}

			$row = $this->_connector->GetNextRecord();
			if ($row)
			{
				$cnt = (int) $row->sabadmin_cnt;
				$this->_connector->FreeResult();
			}

			return $cnt;
		}

		/**
		 * @return	CCommonSubAdmin
		 */
		function GetSubAdminById($id)
		{
			$_subadmin = false;
			if (!$this->_connector->Execute($this->_commandCreator->GetSubAdminById($id)))
			{
				return $_subadmin;
			}

			$row = $this->_connector->GetNextRecord();
			if ($row)
			{
				$_subadmin = new CCommonSubAdmin();
				$_subadmin->InitByDbRow($row);
				$this->_connector->FreeResult();
				$this->InitSubAdminDomains($_subadmin);
			}

			return $_subadmin;
		}

		/**
		 * @return	CCommonSubAdmin
		 */
		function GetSubAdminByLoginPassword($login, $password)
		{
			$_subadmin = false;
			if (!$this->_connector->Execute($this->_commandCreator->GetSubAdminByLoginPassword($login, $password)))
			{
				return $_subadmin;
			}

			$row = $this->_connector->GetNextRecord();
			if ($row)
			{
				$_subadmin = new CCommonSubAdmin();
				$_subadmin->InitByDbRow($row);
				$this->_connector->FreeResult();

				$this->InitSubAdminDomains($_subadmin);
			}

			return $_subadmin;
		}

		function InitSubAdminDomains(&$_subadmin)
		{
			if ($_subadmin && $this->_connector->Execute($this->_commandCreator->GetSubAdminDomainsById($_subadmin->Id)))
			{
				$subAdminDomains = array();
				while (($row = $this->_connector->GetNextRecord()) != false)
				{
					$subAdminDomains[] = (int) $row->id_domain;
				}

				$_subadmin->DomainIds = $subAdminDomains;
			}
		}

		/**
		 * @param	int		$page
		 * @param	int		$pageCnt
		 * @param	string	$orderBy[optional] = null
		 * @param	bool	$asc[optional] = null
		 * @param	string	$condition[optional] = null
		 * @return	array | false
		 */
		function GetSubAdminsList($page, $pageCnt, $orderBy = null, $asc = null, $condition = null)
		{
			if ($this->_connector->Execute($this->_commandCreator->GetSubAdminsList($page, $pageCnt, $orderBy, $asc, $condition)))
			{
				$subAdmins = array();
				while (($row = $this->_connector->GetNextRecord()) != false)
				{
					$subAdmins[$row->id_admin] = array($row->login, $row->description);
				}
				return $subAdmins;
			}
			return false;
		}

		function DeleteSubAdminsByIds($subAdminsIds)
		{
			if (is_array($subAdminsIds) && count($subAdminsIds) > 0)
			{
				foreach($subAdminsIds as $subAdminId)
				{
					$this->_connector->Execute($this->_commandCreator->ClearSubAdminDomains($subAdminId));
					$this->_connector->Execute($this->_commandCreator->DeleteSubAdminById($subAdminId));
				}

				return true;
			}
			return false;
		}
	}
	
	class common_MySQL_DbStorage extends mainCommon_DbStorage
	{
		/**
		 * @param	WebMail_Settings	$settings
		 * @return	common_MySQL_DbStorage
		 */
		function common_MySQL_DbStorage(&$settings)
		{
			$this->_settings =& $settings;
			
			include_once CM_DB_LIBS_PATH.'/commandcreator.php';
			$this->_commandCreator = new MySQL_CommonCommandCreator(AP_DB_QUOTE_ESCAPE, array('`', '`'), $this->_settings->DbPrefix);
			
			if ($settings->UseCustomConnectionString || $settings->UseDsn)
			{
				include_once CAdminPanel::RootPath().'/core/db/class_dbodbc.php';
				if ($settings->UseCustomConnectionString)
				{
					$this->_connector = new DbOdbc($settings->DbCustomConnectionString, $settings->DbType);
				}
				else
				{
					$this->_connector = new DbOdbc('DSN='.$settings->DbDsn.';', $settings->DbType);
				}
			}
			else
			{
				include_once CAdminPanel::RootPath().'/core/db/class_dbmysql.php';
				$this->_connector = new DbMySql($settings->DbHost, $settings->DbLogin, $settings->DbPassword, $settings->DbName);
			}
		}
		
		/**
		 * @param	string	$pref
		 * @param	string	$tableName
		 * @return	array|bool
		 */
		function GetIndexsOfTable($pref, $tableName)
		{
			$returnArray = array();
			if (!$this->_connector->Execute($this->_commandCreator->GetIndexsOfTable($pref, $tableName)))
			{
				return false;
			}

			while (($array = $this->_connector->GetNextArrayRecord()) != false)
			{
				if (isset($array['Column_name']))
				{
					$returnArray[] = trim($array['Column_name']);
				}
			}
			return $returnArray;
		}
	}
	
	class common_MSSQL_DbStorage extends mainCommon_DbStorage
	{
		/**
		 * @param	WebMail_Settings	$settings
		 * @return	webmail_MSSQL_DbStorage
		 */
		function common_MSSQL_DbStorage(&$settings)
		{
			$this->_settings =& $settings;
			
			include_once WM_DB_LIBS_PATH.'/commandcreator.php';
			$this->_commandCreator = new MSSQL_CommonCommandCreator(AP_DB_QUOTE_ESCAPE,  array('[', ']'), $this->_settings->DbPrefix);
			
			if ($settings->UseCustomConnectionString || $settings->UseDsn)
			{
				include_once CAdminPanel::RootPath().'/core/db/class_dbodbc.php';
				if ($settings->UseCustomConnectionString)
				{
					$this->_connector = new DbOdbc($settings->DbCustomConnectionString, $settings->DbType,  $settings->DbLogin, $settings->DbPassword);
				}
				else
				{
					$this->_connector = new DbOdbc('DSN='.$settings->DbDsn.';', $settings->DbType, $settings->DbLogin, $settings->DbPassword);
				}
			}
			else
			{
				include_once CAdminPanel::RootPath().'/core/db/class_dbmssql.php';
				$this->_connector = new DbMSSql($settings->DbHost, $settings->DbLogin, $settings->DbPassword, $settings->DbName);
			}
		}
		
		/**
		 * @param	string $pref
		 * @param	string $tableName
		 * @return	array|bool
		 */
		function GetIndexsOfTable($pref, $tableName)
		{
			$returnArray = false;
			if (!$this->_connector->Execute($this->_commandCreator->GetIndexsOfTable($pref, $tableName)))
			{
				return $returnArray;
			}

			$returnArray = array();
			while (($array = $this->_connector->GetNextArrayRecord()) != false)
			{
				//  MsSQL			 ||	MsSQL ODBC
				if (isset($array[2]) || isset($array['index_keys']))
				{
					$cArray = isset($array[2]) ? $array[2] : '';
					$cArray = isset($array['index_keys']) ? $array['index_keys'] : '';
					
					$temp = explode(',', trim($cArray));
					if (is_array($temp))
					{
						if (count($temp) > 1)
						{
							foreach ($temp as $value)
							{
								if (is_string($value) && strlen($value) > 0)
								{
									$returnArray[] = trim($value);
								}
							}
						}
						else if (isset($temp[0]) && is_string($temp[0]) && strlen($temp[0]) > 0)
						{
							$returnArray[] = trim($temp[0]);
						}
					}
				}
			}
			
			return $returnArray;
		}
	}
	
	/**
	 * @static
	 */
	class CommonDbStorageCreator
	{
		/**
		 * @param	WebMail_Settings	$settings
		 * @return	webmail_MySQL_DbStorage
		 */
		function &CreateDatabaseStorage(&$settings)
		{
			static $instance;
			
    		if (is_object($instance))
    		{
    			return $instance;
    		}
			
			switch ($settings->DbType)
			{
				default:
				case AP_DB_MYSQL:
					$instance = new common_MySQL_DbStorage($settings);
					break;
				case AP_DB_MSSQLSERVER:
					$instance = new common_MSSQL_DbStorage($settings);
					break;
			}
    		
			return $instance;
		}
	}