<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	include_once CAdminPanel::RootPath().'/core/db/commandcreator.php';

	/**
	 * @abstract
	 */
	class main_CommonCommandCreator extends baseMain_CommandCreator
	{
		/**
		 * @param	int		$type
		 * @param	array	$columnEscape
		 * @param	string	$prefix
		 * @return	main_CommonCommandCreator
		 */
		function main_CommonCommandCreator($type, $columnEscape = array('', ''), $prefix = '')
		{
			baseMain_CommandCreator::baseMain_CommandCreator($type, $columnEscape, $prefix);
		}

		/**
		 * @return	string
		 */
		function GetDomainArray()
		{
			return sprintf('SELECT id_domain, name FROM %sawm_domains', $this->_prefix);
		}

		/**
		 * @return	string
		 */
		function GetSubAdminDomainsById($id)
		{
			return sprintf('SELECT id_domain FROM %sawm_subadmin_domains WHERE id_admin = %d', $this->_prefix, $id);
		}

		/**
		 * @return	string
		 */
		function GetSubAdminById($id)
		{
			$sql = 'SELECT id_admin, login, password, description FROM %sawm_subadmins WHERE id_admin = %d';

			return sprintf($sql, $this->_prefix, $id);
		}

		/**
		 * @return	string
		 */
		function GetSubAdminByLoginPassword($login, $password)
		{
			$sql = 'SELECT id_admin, login, password, description FROM %sawm_subadmins WHERE login = %s AND password = %s';

			return sprintf($sql, $this->_prefix, 
									$this->_escapeString(strtolower($login)),
									$this->_escapeString($password));
		}

		/**
		 * @param	CCommonSubAdmin	$newSubadmin
		 * @return	string
		 */
		function CreateSubAdmin($newSubadmin)
		{
			$sql = 'INSERT INTO %sawm_subadmins (login, password, description)
					VALUES (%s, %s, %s)';

			return sprintf($sql, $this->_prefix, 
									$this->_escapeString(strtolower($newSubadmin->Login)),
									$this->_escapeString($newSubadmin->Password),
									$this->_escapeString($newSubadmin->Description));
		}

		function UpdateSubAdmin($subAdmin)
		{
			$sql = 'UPDATE %sawm_subadmins SET
login = %s,
password = %s,
description = %s
WHERE id_admin = %d';

			return sprintf($sql, $this->_prefix,
									$this->_escapeString(strtolower($subAdmin->Login)),
									$this->_escapeString($subAdmin->Password),
									$this->_escapeString($subAdmin->Description),
									$subAdmin->Id);
		}

		function ClearSubAdminDomains($subAdminId)
		{
			return sprintf('DELETE FROM %sawm_subadmin_domains WHERE id_admin = %d', $this->_prefix, $subAdminId);
		}
		
		function DeleteSubAdminById($subAdminId)
		{
			return sprintf('DELETE FROM %sawm_subadmins WHERE id_admin = %d', $this->_prefix, $subAdminId);
		}

		function AddSubAdminDomain($subAdminId, $domainsId)
		{
			$sql = 'INSERT INTO %sawm_subadmin_domains (id_admin, id_domain) VALUES ';
			$domainSql = array();
			foreach ($domainsId as $domainId)
			{
				$domainSql[] = '('.((int) $subAdminId).', '.((int) $domainId).')';
			}

			return sprintf($sql, $this->_prefix).implode(', ', $domainSql);
		}

		/**
		 * @param	string	$condition[optional] = null
		 * @return	string
		 */
		function SubAdminCount($condition = null)
		{
            $where = '';
			if ($condition !== null && strlen($condition) > 0)
			{
				$where = ' WHERE login LIKE '.$this->_escapeString('%'.strtolower($condition).'%').
' OR description LIKE '.$this->_escapeString('%'.$condition.'%');
			}

			$sql = 'SELECT COUNT(id_admin) AS sabadmin_cnt FROM %sawm_subadmins%s';
			return sprintf($sql, $this->_prefix, $where);
		}


		function IsSubAdminExist($subAdmin)
		{
			$sql = 'SELECT COUNT(id_admin) AS sabadmin_cnt FROM %sawm_subadmins WHERE login = %s AND id_admin <> %d';
			return sprintf($sql, $this->_prefix, $this->_escapeString(strtolower($subAdmin->Login)), $subAdmin->Id);
		}
		
		/**
		 * @param	int		$page
		 * @param	int		$pageCnt
		 * @param	string	$orderBy[optional] = null
		 * @param	bool	$asc[optional] = null
		 * @param	string	$condition[optional] = null
		 * @return	string
		 */
		function GetSubAdminsList($page, $pageCnt, $orderBy = null, $asc = null, $condition = null)
		{
			$where = '';
			if ($condition !== null && strlen($condition) > 0)
			{
				$where = 'WHERE login LIKE '.$this->_escapeString('%'.strtolower($condition).'%').
' OR description LIKE '.$this->_escapeString('%'.$condition.'%');
			}

			if ($orderBy === null)
			{
				$orderBy = 'login';
			}

			if ($asc === null)
			{
				$asc = true;
			}
			
			$sql = '
SELECT id_admin, login, description FROM %sawm_subadmins %s
ORDER BY %s %s LIMIT %d, %d';

			$start = ($page > 0) ? ($page - 1) * $pageCnt : 0;
			return sprintf($sql, $this->_prefix, $where,
						$orderBy, ($asc) ? 'ASC' : 'DESC',
						$start, $pageCnt);
		}
	}

	class MySQL_CommonCommandCreator extends main_CommonCommandCreator
	{
		/**
		 * @param	int	$type
		 * @return	MySQL_CommonCommandCreator
		 */
		function MySQL_CommonCommandCreator($type, $columnEscape = array('', ''), $prefix = '')
		{
			main_CommonCommandCreator::main_CommonCommandCreator($type, $columnEscape, $prefix);
		}

		function GetDateFormat($fieldName)
		{
			return 'DATE_FORMAT('.$fieldName.', "%Y-%m-%d %T")';
		}

		function UpdateDateFormat($fieldValue)
		{
			return $this->_escapeString($fieldValue);
		}
	}
	
	class MSSQL_CommonCommandCreator extends main_CommonCommandCreator
	{
		/**
		 * @param	int	$type
		 * @return	MSSQL_CommandCreator
		 */
		function MSSQL_CommonCommandCreator($type, $columnEscape = array('', ''), $prefix = '')
		{
			main_CommonCommandCreator::main_CommonCommandCreator($type, $columnEscape, $prefix);
		}
		
		function GetDateFormat($fieldName)
		{
			return 'CONVERT(VARCHAR, '.$fieldName.', 120)';
		}

		function UpdateDateFormat($fieldValue)
		{
			return 'CONVERT(DATETIME, '.$this->_escapeString($fieldValue).', 120)';
		}
	}
