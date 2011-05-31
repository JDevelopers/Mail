<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	defined('WM_DB_LIBS_PATH') || define('WM_DB_LIBS_PATH', ap_Utils::PathPreparation(dirname(__FILE__))); 
	
	include_once CAdminPanel::RootPath().'/core/db/class_dbsql.php';

	define('DBTABLE_A_USERS', 'a_users');
	define('DBTABLE_A_SESSIONS', 'a_sessions');
	define('DBTABLE_A_TEST', 'a_test');
		
	define('DBTABLE_AWM_SETTINGS', 'awm_settings');
	define('DBTABLE_AWM_MESSAGES', 'awm_messages');
	define('DBTABLE_AWM_MESSAGES_BODY', 'awm_messages_body');
	define('DBTABLE_AWM_READS', 'awm_reads');
	define('DBTABLE_AWM_ACCOUNTS', 'awm_accounts');
	define('DBTABLE_AWM_ADDR_GROUPS', 'awm_addr_groups');
	define('DBTABLE_AWM_ADDR_BOOK', 'awm_addr_book');
	define('DBTABLE_AWM_ADDR_GROUPS_CONTACTS', 'awm_addr_groups_contacts');
	define('DBTABLE_AWM_FOLDERS', 'awm_folders');
	define('DBTABLE_AWM_FOLDERS_TREE', 'awm_folders_tree');
	define('DBTABLE_AWM_FILTERS', 'awm_filters');
	define('DBTABLE_AWM_TEMP', 'awm_temp');
	define('DBTABLE_AWM_SENDERS', 'awm_senders');
	define('DBTABLE_AWM_COLUMNS', 'awm_columns');
	define('DBTABLE_AWM_TEMPFILES', 'awm_tempfiles');
	define('DBTABLE_AWM_LOGS', 'awm_logs');
	define('DBTABLE_AWM_MAILALIASES', 'awm_mailaliases');
	define('DBTABLE_AWM_MAILINGLISTS', 'awm_mailinglists');
	define('DBTABLE_AWM_MAILFORWARDS', 'awm_mailforwards');
	
	define('DBTABLE_CAL_USERS_DATA', 'acal_users_data');
	define('DBTABLE_CAL_CALENDARS', 'acal_calendars');
	define('DBTABLE_CAL_EVENTS', 'acal_events');

	define('DBTABLE_AWM_MESSAGES_INDEX', 'awm_messages_index');
	define('DBTABLE_AWM_MESSAGES_BODY_INDEX', 'awm_messages_body_index');
	
	define('DBTABLE_AWM_DOMAINS', 'awm_domains');
	
	define('DBTABLE_CAL_SHARING', 'acal_sharing');
	define('DBTABLE_CAL_PUBLICATIONS', 'acal_publications');
	
	define('DBTABLE_CAL_EXCLUSIONS', 'acal_exclusions');
	define('DBTABLE_CAL_EVENTREPEATS', 'acal_eventrepeats');

	define('DBTABLE_CAL_REMINDERS', 'acal_reminders');
	define('DBTABLE_CAL_CRON_RUNS', 'acal_cron_runs');
	define('DBTABLE_CAL_APPOINTMENTS', 'acal_appointments');

	define('DBTABLE_AWM_SUBADMINS', 'awm_subadmins');
	define('DBTABLE_AWM_SUBADMIN_DOMAINS', 'awm_subadmin_domains');

	define('DBTABLE_AWM_FNBL_RUNS', 'acal_awm_fnbl_runs');

	/**
	 * @return	array
	 */
	function GetTablesArray($pref)
	{
		return array(
				$pref.DBTABLE_A_USERS,

				$pref.DBTABLE_AWM_SETTINGS, $pref.DBTABLE_AWM_MESSAGES,
				$pref.DBTABLE_AWM_MESSAGES_BODY, $pref.DBTABLE_AWM_READS, $pref.DBTABLE_AWM_ACCOUNTS,
				$pref.DBTABLE_AWM_ADDR_GROUPS, $pref.DBTABLE_AWM_ADDR_BOOK,
				$pref.DBTABLE_AWM_ADDR_GROUPS_CONTACTS, $pref.DBTABLE_AWM_FOLDERS,
				$pref.DBTABLE_AWM_FOLDERS_TREE, $pref.DBTABLE_AWM_FILTERS, $pref.DBTABLE_AWM_TEMP,
				$pref.DBTABLE_AWM_SENDERS, $pref.DBTABLE_AWM_COLUMNS, $pref.DBTABLE_AWM_DOMAINS,
				$pref.DBTABLE_AWM_MAILALIASES, $pref.DBTABLE_AWM_MAILINGLISTS, $pref.DBTABLE_AWM_MAILFORWARDS,
				$pref.DBTABLE_AWM_SUBADMINS, $pref.DBTABLE_AWM_SUBADMIN_DOMAINS, $pref.DBTABLE_AWM_FNBL_RUNS,
				/* indexs */
				$pref.DBTABLE_AWM_MESSAGES_INDEX, $pref.DBTABLE_AWM_MESSAGES_BODY_INDEX,
				
			);
	}
	
	/**
	 * @return	array
	 */
	function GetIndexsArray()
	{
		return array(
			DBTABLE_AWM_SETTINGS => array('id_user'),
			DBTABLE_AWM_READS => array('id_acct'),
			DBTABLE_AWM_COLUMNS => array('id_user', 'id_column'),  
			DBTABLE_AWM_MESSAGES => array('id_folder_srv', 'id_folder_db', 'id_msg'),
			DBTABLE_AWM_SENDERS => array('id_user'),
			DBTABLE_AWM_ACCOUNTS => array('id_acct', 'id_user'),
			DBTABLE_AWM_ADDR_GROUPS => array('id_user'),	  
			DBTABLE_AWM_ADDR_BOOK => array('id_user'),
			DBTABLE_AWM_FOLDERS => array('id_acct', 'id_parent'),
			DBTABLE_AWM_FOLDERS_TREE => array('id_folder', 'id_parent'),
			DBTABLE_AWM_FILTERS => array('id_acct', 'id_folder')
		);
	}
	
	class main_DbStorage
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
		 * @param	string	$_msg
		 * @return	bool
		 */
		function AdminAllTableCreate(&$_msg, $isInstall = false)
		{
			$is_good = $this->CheckExistTable($this->_settings->DbPrefix);
			if ($is_good === true)
			{
				$is_good = $this->CreateTables($this->_settings->DbPrefix);
				if ($is_good === true)
				{
					if ($this->CreateAllIndex($this->_settings->DbPrefix))
					{
						$_msg = 'Congratulations!
						All tables have been created successfully.';			
					}
					else 
					{
						$is_good = false;
						$error = strlen($this->GetError()) > 5 ? $this->GetError() : '';
						$_msg = 'When creating index, an error in database occurred!
						'.$error;						
					}
				}
				else if ($is_good && strlen($is_good) > 0)
				{
					$error = strlen($this->GetError()) > 5 ? $this->GetError() : '';
					$_msg = 'When creating the table ('.$is_good.') , an error in database occurred!
					'.$error;	
				}
				else 
				{
					$error = strlen($this->GetError()) > 5 ? $this->GetError() : '';
					$_msg = 'When creating the table, an error in database occurred!
					'.$error;	
				}	
			}
			else if ($is_good && strlen($is_good) > 0)
			{
				if (strlen($this->_settings->DbPrefix) > 0)
				{
					$_msg = 'The data tables with "'.$this->_settings->DbPrefix.'" prefix already exist. To proceed, specify another prefix
						or delete the existing tables.';
				}
				else 
				{
					$_msg = 'The data tables without prefix already exist. To proceed, specify another prefix
						or delete the existing tables.';
				}
				
				$_msg .= ($isInstall) ? '
Or uncheck "Create Database Tables" checkbox to use existing tables.' : '';
			}
			else 
			{
				$error = strlen($this->GetError()) > 5 ? $this->GetError() : '';
				$_msg = 'A database error occurred.
				Perhaps, it is the connection error or database name is incorrect.
				'.$error.'
				
				Change the database connection settings and retry.';			
			}

			if ($is_good === true)
			{
				$this->CreateFunctions();
			}
			
			return ($is_good === true);
		}

		function AdminTestTableCreate()
		{
			$return = true;
			if (!$this->IsTableExist($this->_settings->DbPrefix, DBTABLE_A_TEST))
			{
				if ($this->CreateTable(DBTABLE_A_TEST))
				{
					$this->DropTable(DBTABLE_A_TEST);
				}
				else
				{
					$return = false;
				}
			}
			else
			{
				$this->DropTable(DBTABLE_A_TEST);
			}
			
			return $return;
		}

		function CreateFunctions()
		{
			$return = $this->_connector->Execute($this->_commandCreator->CreateFunctions('DP1'));
			return $return;
		}
		
		function CreateTable($_tableName)
		{
			return $this->_connector->Execute($this->_commandCreator->CreateTable($_tableName, $this->_settings->DbPrefix));
		}
		
		function DropTable($_tableName)
		{
			return $this->_connector->Execute($this->_commandCreator->DropTable($_tableName, $this->_settings->DbPrefix));
		}
		
		/**
		 * @param	string	$_dbName
		 * @return	bool
		 */
		function CreateDatabase($_dbName)
		{
			return $this->_connector->Execute($this->_commandCreator->CreateDatabase($_dbName));
		}

		/**
		 * @param	string	$_dbName
		 * @return	bool
		 */
		function DropDatabase($_dbName)
		{
			return $this->_connector->Execute($this->_commandCreator->DropDatabase($_dbName));
		}

		/**
		 * @return bool
		 */
		function UpdateRequestDomains($settings)
		{
			return $this->_connector->Execute($this->_commandCreator->ScriptUpdateDomainCustomData($settings));
		}

		/**
		 * @return bool
		 */
		function UpdateRequestCalendarActive()
		{
			$this->_connector->Execute($this->_commandCreator->SelectCountOfActiveCalendars());
			$row = $this->_connector->GetNextRecord();
			if ($row && (int) $row->cnt == 0)
			{
				return $this->_connector->Execute($this->_commandCreator->SetAllCalendarsActive());
			}

			return true;
		}

		/**
		 * @param	CWebMailDomain	$domain
		 * @return	bool
		 */
		function DomainExist($domain)
		{
			$this->_connector->Execute($this->_commandCreator->SelectDomainsIdByName($domain->_name));
			return ($this->_connector->ResultCount() > 0);
		}
		
		/**
		 * @param	CWebMailDomain	$domain
		 * @return	bool
		 */
		function CreateDomain(&$domain)
		{
			if ($this->_connector->Execute($this->_commandCreator->CreateDomain($domain, $this->_settings)))
			{
				$domain->_id = $this->_connector->GetLastInsertId();
				$this->UpdateAccountsNullDomainId($domain);	
				return true;
			}
			return false;
		}
		
		/**
		 * @param	CWebMailDomain	$domain
		 * @return	bool
		 */
		function UpdateAccountsNullDomainId($domain)
		{
			return $this->_connector->Execute($this->_commandCreator->UpdateAccountsNullDomainId($domain));
		}
		
		/**
		 * @param	array	$ids
		 * @return	bool
		 */
		function DeleteDomainsByIds($ids)
		{
			if ($this->_connector->Execute($this->_commandCreator->DeleteDomainsByIds($ids)))
			{
				$this->UpdateAccountsSetNullDomainIdByIds($ids);
				return true;
			}
			return false;
		}
		
		/**
		 * @param	array	$ids
		 * @return	bool
		 */
		function UpdateAccountsSetNullDomainIdByIds($ids)
		{
			return $this->_connector->Execute($this->_commandCreator->UpdateAccountsSetNullDomainIdByIds($ids));
		}
		
		/**
		 * @param	array	$names
		 * @return	bool
		 */
		function DeleteDomainsByNames($names)
		{
			if ($this->_connector->Execute($this->_commandCreator->DeleteDomainsByNames($names)))
			{
				$this->UpdateAccountsSetNullDomainIdByNames($names);
				return true;
			}
			return false;
		}
		
		/**
		 * @param	array	$ids
		 * @return	bool
		 */
		function UpdateAccountsSetNullDomainIdByNames($names)
		{
			return $this->_connector->Execute($this->_commandCreator->UpdateAccountsSetNullDomainIdByNames($names));
		}

		/**
		 * @param	CWebMailDomain	$domain
		 * @return	bool
		 */
		function UpdateDomainById($domain)
		{
			return $this->_connector->Execute($this->_commandCreator->UpdateDomainById($domain));
		}

		/**
		 * @param	int		$id
		 * @return	CWebMailDomain|null
		 */
		function SelectDomainById($id)
		{
			return $this->SelectDomainByX($id, 'id');
		}
		
		/**
		 * @param	string		$name
		 * @return	CWebMailDomain|null
		 */
		function SelectDomainByName($name)
		{
			return $this->SelectDomainByX($name, 'name');
		}

		function SelectDomainByX($xvalue, $xtype)
		{
			$return = null;
			switch ($xtype)
			{
				case 'id':
					if (!$this->_connector->Execute($this->_commandCreator->SelectDomainById($xvalue)))
					{
						return $return;
					}
					break;
				case 'name':
					if (!$this->_connector->Execute($this->_commandCreator->SelectDomainByName($xvalue)))
					{
						return $return;
					}
					break;
				default:
					return $return;
					break;
			}

			$row = $this->_connector->GetNextRecord();
			if ($row)
			{
				$return = new CWebMailDomain();
				$return->InitByDbRow($row);
			}

			return $return;
		}
		
		/**
		 * @param	Account	$account
		 * @return	FolderCollection
		 */
		function &GetFolders(&$account)
		{
			if (!$this->_connector->Execute($this->_commandCreator->GetFolders($account->Id)))
			{
				$null = null;
				return $null;
			}
			
			$folders = array();
			while (($row = $this->_connector->GetNextRecord()) != false)
			{
				$folder = new Folder($account->Id, (int) $row->id_folder,
										substr($row->full_path, 0, -1), substr($row->name, 0, -1));
										
				$folder->IdParent = $row->id_parent;
				$folder->Type = (int) $row->type;
				$folder->SyncType = (int) $row->sync_type;
				$folder->Hide = (bool) abs($row->hide);
				$folder->FolderOrder = (int) $row->fld_order;
				$folder->MessageCount = (int) $row->message_count;
				$folder->UnreadMessageCount = (int) $row->unread_message_count;
				$folder->Size = GetGoodBigInt($row->folder_size);
				$folder->Level = (int) $row->level;
				$folders[] =& $folder;
				unset($folder);
			}
			
			$folderCollection = new FolderCollection();
			$this->_addLevelToFolderTree($account, $folderCollection, $folders);

			/* custom class */
			ap_Custom::StaticUseMethod('wm_ChangeDbFoldersAfterGet', array(&$folderCollection));
			
			return $folderCollection;
		}

		/**
		 * @param Account $account
		 * @return bool
		 */
		function SaveMailAliases($account)
		{
			if ($account && $account->IsInternal)
			{
				if ($this->_connector->Execute($this->_commandCreator->ClearMailAliases($account->Id)))
				{
					$return = true;

					$aliasTo = $account->MailIncLogin;
					$aliasArray = explode('@', $aliasTo);
					$aliasDomain = isset($aliasArray[1]) ? $aliasArray[1] : '';

					if (strlen($aliasDomain) > 0 &&	is_array($account->Aliases) && count($account->Aliases) > 0)
					{

						$aliases = array_unique($account->Aliases);
						$addAliases = array();

						foreach ($aliases as $aliasName)
						{
							$add1 = $add2 = false;
							$result = $this->_connector->Execute(
								$this->_commandCreator->IsAliasValidToCreateInAccounts($aliasName, $aliasDomain));
							if ($result)
							{
								$row = $this->_connector->GetNextRecord();
								if (is_object($row))
								{
									if (0 === (int) $row->cnt)
									{
										$add1 = true;
									}
									$this->_connector->FreeResult();
								}
							}
							$result = $this->_connector->Execute(
								$this->_commandCreator->IsAliasValidToCreateInAliases($aliasName, $aliasDomain));
							if ($result)
							{
								$row = $this->_connector->GetNextRecord();
								if (is_object($row))
								{
									if (0 === (int) $row->cnt)
									{
										$add2 = true;
									}
									$this->_connector->FreeResult();
								}
							}

							if ($add1 && $add2)
							{
								$addAliases[] = $aliasName;
							}
						}

						if (count($addAliases) > 0)
						{
							foreach ($addAliases as $aliasName)
							{
								$return &= $this->_connector->Execute(
										$this->_commandCreator->InsertMailAlias($account->Id, $aliasName, $aliasDomain, $aliasTo));
							}
						}
					}
					
					return $return;
				}

				return false;
			}
			
			return true;
		}

		/**
		 * @param Account $account
		 * @return bool
		 */
		function SaveMailForwards($account)
		{
			if ($account && $account->IsInternal)
			{
				if ($this->_connector->Execute($this->_commandCreator->ClearMailForwards($account->Id)))
				{
					$forwardName = $forwardDomain = '';
					$return = true;
					list($forwardName, $forwardDomain) = explode('@', $account->MailIncLogin, 2);
					
					if (strlen($forwardName) > 0 && strlen($forwardDomain) > 0 && is_array($account->Forwards) && count($account->Forwards) > 0)
					{
						$forwards = array_unique($account->Forwards);
						if (count($forwards) > 0)
						{
							foreach ($forwards as $forwardTo)
							{
								$return &= $this->_connector->Execute(
										$this->_commandCreator->InsertMailForward($account->Id, $forwardName, $forwardDomain, $forwardTo));
							}
						}
					}

					return $return;
				}

				return false;
			}

			return true;
		}


		/**
		 * @param Account $account
		 * @return bool
		 */
		function SaveMailingList($account)
		{
			if ($account && $account->IsMailList)
			{
				if ($this->_connector->Execute($this->_commandCreator->ClearMailingList($account->Id)))
				{
					$return = true;

					if (is_array($account->MailingList) && count($account->MailingList) > 0)
					{
						$mailinglist = array_unique($account->MailingList);
						foreach ($mailinglist as $mailingListItem)
						{
							$return &= $this->_connector->Execute(
									$this->_commandCreator->InsertMailingListItem($account->Id, $account->Email, $mailingListItem));
						}
					}

					return $return;
				}

				return false;
			}

			return true;
		}

		/**
		 * @param Account $account
		 * @return bool
		 */
		function DeleteEximAccountData(&$account)
		{
			$sql = 'DELETE FROM %sawm_accounts WHERE id_acct = %d';
			$query = sprintf($sql, $this->_settings->DbPrefix, $account->Id);
			return $this->_connector->Execute($query);
		}

		/**
		 * @param Account $account
		 * @return bool
		 */
		function UpdateEximAccountData(&$account)
		{
			return $this->_connector->Execute($this->_commandCreator->UpdateAccount($account));
		}
		
		/**
		 * @access	private
		 * @param	Account				$account
		 * @param	FolderCollection	$folderCollection
		 * @param	array				$folders
		 * @param	string				$rootPrefix[optional] = ''
		 * @param	bool				$isToFolder[optional] = false
		 */
		function _addLevelToFolderTree(&$account, &$folderCollection, &$folders, $rootPrefix = '', $isToFolder = false)
		{
			$prefixLen = strlen($rootPrefix);
			$foldersCount = count($folders);
			for ($i = 0; $i < $foldersCount; $i++)
			{
				$folderFullName = $folders[$i]->FullName;
				if ($rootPrefix != $folderFullName && strlen($folderFullName) > $prefixLen &&
					substr($folderFullName, 0, $prefixLen) == $rootPrefix &&
					strpos($folderFullName, $account->Delimiter, $prefixLen + 1) === false)
				{
					$folderObj =& $folders[$i];
					$isTo = ($isToFolder || $folderObj->Type == WM_FOLDERTYPE_Drafts || $folderObj->Type == WM_FOLDERTYPE_SentItems);
					
					$folderObj->ToFolder = $isTo;
					$folderCollection->Add($folderObj);
					
					$newCollection = new FolderCollection();
					$this->_addLevelToFolderTree($account, $newCollection, $folders, $folderFullName.$account->Delimiter, $isTo);
					if ($newCollection->Count() > 0)
					{
						$folderObj->SubFolders = $newCollection;
					}
					unset($folderObj, $newCollection);
				}
			}
		}
		
		/**
		 * @param int $type
		 * @return int
		 */
		function GetFolderSyncTypeByFolderType($acctId, $type)
		{
			$result = -1;
			if (!$this->_connector->Execute($this->_commandCreator->GetFolderSyncType($acctId, $type)))
			{
				return $result;
			}
			
			$row = $this->_connector->GetNextRecord();
			if ($row)
			{
				$result = $row->sync_type;
			}
			return $result;
		}
		
		/**
		 * @param	string	$email
		 * @param	string	$login
		 * @param	bool	$onlyDef[optional] = false
		 * @param	int		$notInAcctId[optional] = false
		 * @return array(id_acct, mail_inc_pass, def_acct,id_user)
		 */
		function &SelectAccountDataByLogin($email, $login, $onlyDef = false, $notInAcctId = null)
		{
			$resArray = null;
			$result = $this->_connector->Execute($this->_commandCreator->SelectAccountDataByLogin($email, $login, $onlyDef, $notInAcctId));
			if ($result)
			{
				$row = $this->_connector->GetNextRecord();
				if (is_object($row))
				{
					$resArray = array($row->id_acct, $row->mail_inc_pass, $row->def_acct, $row->id_user);
					$this->_connector->FreeResult();
				}
			}
			
			return $resArray;
		}
		
		/**
		 * @param int $id
		 * @return Account
		 */
		function &SelectAccountData($id)
		{
			$null = null;
			if (!$this->_connector->Execute($this->_commandCreator->SelectAccountData($id)))
			{
				return $null;
			}
			
			$account = new Account($this->_settings);
			
			$row = $this->_connector->GetNextRecord();
			if ($row)
			{
				$account->Id = (int) $row->id_acct;
				$account->IdUser = (int) $row->id_user;
				$account->DefaultAccount = (bool) abs($row->def_acct);
				$account->Deleted = (bool) abs($row->deleted);
				$account->Email = $row->email;
				$account->MailProtocol = (int) $row->mail_protocol;
				$account->MailIncHost = $row->mail_inc_host;
				$account->MailIncLogin = $row->mail_inc_login;
				$account->MailIncPort = (int) $row->mail_inc_port;
				$account->MailOutHost = $row->mail_out_host;
				$account->MailOutLogin = $row->mail_out_login;
				$account->MailOutPort = (int) $row->mail_out_port;
				$account->MailOutAuthentication = (int) $row->mail_out_auth;
				$account->FriendlyName = $row->friendly_nm;
				$account->UseFriendlyName = (bool) abs($row->use_friendly_nm);
				$account->DefaultOrder = (int) $row->def_order;
				$account->GetMailAtLogin = (bool) abs($row->getmail_at_login);
				$account->MailMode = (int) $row->mail_mode;
				$account->MailsOnServerDays = (int) $row->mails_on_server_days;
				$account->SignatureType = (int) $row->signature_type;
				$account->SignatureOptions = (int) $row->signature_opt;
				$account->HideContacts = (bool) abs($row->hide_contacts);
				$account->MailsPerPage = ((int) $row->msgs_per_page > 0) ? (int) $row->msgs_per_page : 20;
				$account->Delimiter = $row->delimiter;
				$account->NameSpace = $row->personal_namespace;
				$account->DomainId = (int) $row->id_domain;
				
				$account->WhiteListing = (bool) abs($row->white_listing);
				$account->XSpam = (bool) abs($row->x_spam);
				$account->LastLogin = (int) $row->last_login;
				$account->LoginsCount = (int) $row->logins_count;
				$account->DefaultSkin = $row->def_skin;
				$account->DefaultLanguage = $row->def_lang;
				$account->DefaultIncCharset = ap_Utils::GetCodePageName((int) $row->def_charset_inc);
				$account->DefaultOutCharset = ap_Utils::GetCodePageName((int) $row->def_charset_out);
				$account->DefaultTimeZone = (int) $row->def_timezone;
				
				$account->DefaultDateFormat = CDateTime::GetDateFormatFromBd($row->def_date_fmt);
				$account->DefaultTimeFormat = CDateTime::GetTimeFormatFromBd($row->def_date_fmt);
				
				$account->HideFolders = (bool) abs($row->hide_folders);
				$account->MailboxLimit = GetGoodBigInt($row->mailbox_limit);
				$account->MailboxSize = GetGoodBigInt($row->mailbox_size);
				$account->AllowChangeSettings = (bool) abs($row->allow_change_settings);
				$account->AllowDhtmlEditor = (bool) abs($row->allow_dhtml_editor);
				$account->AllowDirectMode = (bool) abs($row->allow_direct_mode);
				$account->DbCharset = ap_Utils::GetCodePageName((int) $row->db_charset);
				$account->HorizResizer = (int) $row->horiz_resizer;
				$account->VertResizer = (int) $row->vert_resizer;
				$account->Mark = (int) $row->mark;
				$account->Reply = (int) $row->reply;
				$account->ContactsPerPage = ((int) $row->contacts_per_page > 0) ? (int) $row->contacts_per_page : 20;
				$account->ViewMode = (int) $row->view_mode;				
				$account->MailIncPassword = ap_Utils::DecodePassword($row->mail_inc_pass);
				$account->MailOutPassword = ap_Utils::DecodePassword($row->mail_out_pass);
				$account->IsMailList = (bool) $row->mailing_list;

				$account->ImapQuota = (int) $row->imap_quota;

				$this->_connector->FreeResult();
			}
			else
			{
				$account = $null;
			}

			if ($account && !$account->IsMailList)
			{
				if (!is_object($account) || !$this->_connector->Execute($this->_commandCreator->SelectSignature($account->Id)))
				{
					return $null;
				}
				$row = $this->_connector->GetNextRecord();
				if ($row)
				{
					$account->Signature = $row->signature;
					$this->_connector->FreeResult();
				}

				if (!is_object($account) || !$this->_connector->Execute($this->_commandCreator->SelectAccountColumnsData($account->IdUser)))
				{
					return $null;
				}
				while (($row = $this->_connector->GetNextRecord()) != false)
				{
					$account->Columns[(int) $row->id_column] = $row->column_value;
				}

				if ($account->DomainId > 0 && $this->_connector->Execute($this->_commandCreator->SelectAccountAliases($account->Id)))
				{
					while (($row = $this->_connector->GetNextRecord()) != false)
					{
						$account->Aliases[] = $row->alias_name;
					}
				}

				if ($account->DomainId > 0 && $this->_connector->Execute($this->_commandCreator->SelectAccountForwards($account->Id)))
				{
					while (($row = $this->_connector->GetNextRecord()) != false)
					{
						$account->Forwards[] = $row->forward_to;
					}
				}
			}
			else if ($account && $account->IsMailList)
			{
				if ($this->_connector->Execute($this->_commandCreator->SelectMailListAccountUsers($account->Id)))
				{
					while (($row = $this->_connector->GetNextRecord()) != false)
					{
						$account->MailingList[] = $row->list_to;
					}
				}
			}
			

			return $account;
		}
		
		/**
		 * @param	string	$condition[options] = null
		 * @return	array|false
		 */
		function DomainList($condition = null)
		{
			$array = array();
			if (!$this->_connector->Execute($this->_commandCreator->GetDomainsList($condition)))
			{
				return false;
			}
			
			while (($row = $this->_connector->GetNextRecord()) != false)
			{
				$array[$row->id_domain] = array($row->name, $row->mail_protocol, (bool) $row->is_internal, (bool) $row->ldap_auth);
			}
			
			return $array;
		}

		/**
		 * @param	string	$domain
		 * @return	bool
		 */
		function SetLdapDomain($domain)
		{
			$this->_connector->Execute($this->_commandCreator->ClearLdapDomain());
			return $this->_connector->Execute($this->_commandCreator->SetLdapDomain($domain));
		}

		function FilterDomainList()
		{
			$array = array();
			if (!$this->_connector->Execute($this->_commandCreator->FilterDomainList()))
			{
				return false;
			}

			while (($row = $this->_connector->GetNextRecord()) != false)
			{
				$array[$row->id_domain] = array($row->name, $row->url);
			}

			return $array;
		}
		
		/**
		 * @return array
		 */
		function CountAllMailboxSizes()
		{
			$mailBoxSizes = array();
			if ($this->_connector->Execute($this->_commandCreator->CountAllMailboxSizes()))
			{
				while (($row = $this->_connector->GetNextRecord()) != false)
				{
					$mailBoxSizes[$row->id_user] = $row->mailboxes_size;
				}
			}
			return $mailBoxSizes;
		}
			
			
		/**
		 * @param	int		$domainId
		 * @param	int		$page
		 * @param	int		$pageCnt
		 * @param	string	$orderBy[optional] = null
		 * @param	bool	$asc[optional] = null
		 * @param	string	$condition[optional] = null
		 * @return	array|false
		 */
		function AccountList($domainId, $page, $pageCnt, $orderBy = null, $asc = null, $condition = null)
		{
			$array = array();
			if (!$this->_connector->Execute($this->_commandCreator->GetAccountList($domainId, $page, $pageCnt, $orderBy, $asc, $condition)))
			{
				return false;
			}
			
			while (($row = $this->_connector->GetNextRecord()) != false)
			{
				$array[$row->id_acct] = array($row->id_user, $row->email, $row->nlast_login,
						$row->logins_count, $row->mailbox_size, $row->mailbox_limit, (bool) $row->mailing_list, $row->mail_protocol, $row->deleted);
			}
			
			return $array;
		}
		
		/**
		 * @param	int		$domainId
		 * @param	string	$condition[optional] = null
		 * @return	int
		 */
		function AccountCount($domainId, $condition = null)
		{
			$cnt = 0;
			if (!$this->_connector->Execute($this->_commandCreator->GetAccountCount($domainId, $condition)))
			{
				return false;
			}
			
			$row = $this->_connector->GetNextRecord();
			if ($row)
			{
				$cnt = (int) $row->acct_cnt;
				$this->_connector->FreeResult();
			}
			
			return $cnt;
		}
		
		/**
		 * @return	int
		 */
		function AllUserCount()
		{
			$cnt = 0;
			if (!$this->_connector->Execute($this->_commandCreator->AllUserCount()))
			{
				return false;
			}
			
			$row = $this->_connector->GetNextRecord();
			if ($row)
			{
				$cnt = (int) $row->user_cnt;
				$this->_connector->FreeResult();
			}
			
			return $cnt;
		}
		
		/**
		 * @param	Account $account
		 * @param	bool	$isUserLimit = false
		 * @return	bool
		 */
		function InsertUserData(&$account)
		{
			if (!$this->_connector->Execute($this->_commandCreator->InsertNewUser()))
			{
				return false;
			}
			
			$id = $this->_connector->GetLastInsertId();
			if ($id > 0)
			{
				$account->IdUser = $id;
			}
			else
			{
				return false;
			}
			
			if (!$this->_connector->Execute($this->_commandCreator->InsertSettings($account)))
			{
				$this->DeleteUserData($account->IdUser);
				return false;
			}
			
			return true;
		}
		
		/**
		 * @param int $userId
		 * @return bool
		 */
		function DeleteUserData($userId, $acct_id = null)
		{
			$result = true;
			if ($acct_id !== 0 && $acct_id > 0)
			{
				$result &= $this->DeleteAccountData($acct_id);
			}
			$result &= $this->_connector->Execute($this->_commandCreator->DeleteUserData($userId));
			return $result;
		}
		
		/**
		 * @param int $acct_id
		 * @return bool
		 */
		function DeleteAccountData($acct_id,$email)
		{
			$count = 0;
			$user_id = -1;
			
			if ($acct_id < 0)
			{
				return false;
			}
			
			if (!$this->_connector->Execute($this->_commandCreator->GetUserIdFromAcctId($acct_id)))
			{
				return false;
			}
			
			$row = $this->_connector->GetNextRecord();
			if ($row)
			{
				$user_id = $row->id_user;
			}

			if ($user_id < 0)
			{
				return false;
			}
			
			if (!$this->_connector->Execute($this->_commandCreator->CountAccountsByUserId($user_id)))
			{
				return false;
			}

			$row = $this->_connector->GetNextRecord();
			if ($row)
			{
				$count = $row->cnt;
			}

			$result = true;
			
			if ($count > 0)
			{
				$sql = 'DELETE FROM %sawm_accounts WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $acct_id);
				$result = $this->_connector->Execute($query);
				
				$sql = 'DELETE FROM %sawm_messages WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $acct_id);
				$result &= $this->_connector->Execute($query);
				
				$sql = 'DELETE FROM %sawm_messages_body WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $acct_id);
				$result &= $this->_connector->Execute($query);
	
				$sql = 'DELETE FROM %sawm_filters WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $acct_id);
				$result &= $this->_connector->Execute($query);
				
				$sql = 'DELETE FROM %sawm_reads WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $acct_id);
				$result &= $this->_connector->Execute($query);
			
				$result &= $this->_connector->Execute($this->_commandCreator->DeleteFolderTreeById($acct_id));
				
				$sql = 'DELETE FROM %sawm_folders WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $acct_id);
				$result &= $this->_connector->Execute($query);

				$sql = 'DELETE FROM %sawm_mailaliases WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $acct_id);
				$result &= $this->_connector->Execute($query);

				$sql = 'DELETE FROM %sawm_mailinglists WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $acct_id);
				$result &= $this->_connector->Execute($query);
				
				/*$sql = 'DELETE FROM %sawm_tempfiles WHERE id_acct = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $acct_id);
				$result &= $this->_connector->Execute($query);*/
			}
			
			if ($count == 1)
			{
				$sql = 'DELETE FROM %sawm_addr_book WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $user_id);
				$result &= $this->_connector->Execute($query);
				
				$sql = 'DELETE FROM %sawm_settings WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $user_id);
				$result &= $this->_connector->Execute($query);
				
				/* contacts */
				$result &= $this->_connector->Execute($this->_commandCreator->DeleteAddrGroupsContactsById($user_id));

				$sql = 'DELETE FROM %sawm_addr_groups WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $user_id);
				$result &= $this->_connector->Execute($query);
				
				$sql = 'DELETE FROM %sawm_columns WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $user_id);
				$result &= $this->_connector->Execute($query);
				
				$sql = 'DELETE FROM %sawm_senders WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $user_id);
				$result &= $this->_connector->Execute($query);

				$sql = 'DELETE FROM %sa_users WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix , $user_id);
				$result &= $this->_connector->Execute($query);
				
				/* calendar */
				
				$cal_ids = $this->GetCalendarIdsByUserId($user_id);
				if ($cal_ids && is_array($cal_ids) && count($cal_ids) > 0)
				{
					$result &= $this->_connector->Execute($this->_commandCreator->DeleteCalendarExclusions($cal_ids));
					
					$result &= $this->_connector->Execute($this->_commandCreator->DeleteCalendarEventrepeats($cal_ids));
					
					$result &= $this->_connector->Execute($this->_commandCreator->DeleteCalendarEvents($user_id));
				
					$sql = 'DELETE FROM %sacal_calendars WHERE user_id = %d';
					$query = sprintf($sql, $this->_settings->DbPrefix, $user_id);
					$result &= $this->_connector->Execute($query);
				}
								
				$sql = 'DELETE FROM %sacal_users_data WHERE user_id = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $user_id);
				$result &= $this->_connector->Execute($query);
				
				$sql = 'DELETE FROM %sacal_publications WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $user_id);
				$result &= $this->_connector->Execute($query);
				
				$sql = 'DELETE FROM %sacal_sharing WHERE id_user = %d';
				$query = sprintf($sql, $this->_settings->DbPrefix, $user_id);
				$result &= $this->_connector->Execute($query);

				$query = $this->_commandCreator->DeleteFunambolContacts($email);
				// this commands does not affect result
				$this->_connector->Execute($query);

				$query = $this->_commandCreator->DeleteFunambolEvents($email);
				// this commands does not affect result
				$this->_connector->Execute($query);
			}
			
			return $result;
		}
		
		/**
		 * @param	int	$userId
		 * @return	array|false
		 */
		function GetCalendarIdsByUserId($userId)
		{
			$result = array();
			if (!$this->_connector->Execute($this->_commandCreator->GetCalendarIdsByUserId($userId)))
			{
				return false;
			}
			
			while (($row = $this->_connector->GetNextRecord()) !== false)
			{
				$result[] = (int) $row->calendar_id;
			}
			
			return $result;
		}
		
		/**
		 * @param Account $account
		 * @return bool
		 */
		function InsertAccountData(&$account)
		{
			if (!$this->_connector->Execute($this->_commandCreator->InsertAccount($account)))
			{
				return false;
			}
			$account->Id = $this->_connector->GetLastInsertId();
			return true;
		}
		
		/**
		 * @param Account $account
		 * @return bool
		 */
		function UpdateAccountData(&$account)
		{
			if (!$this->_connector->Execute($this->_commandCreator->UpdateAccount($account)) ||
				!$this->_connector->Execute($this->_commandCreator->UpdateSettings($account)))
			{
				return false;
			}
			
			return $this->UpdateColumns($account);
		}
		
		/**
		 * @param int $accountId
		 * @param string $accountDelimiter
		 * @return bool
		 */
		function UpdateAccountDelimiter($accountId, $accountDelimiter)
		{
			$accountDelimiter = (strlen(trim($accountDelimiter)) == 1) ? trim($accountDelimiter) : '/';
			return $this->_connector->Execute($this->_commandCreator->UpdateAccountDelimiter($accountId, $accountDelimiter));
		}

		/**
		 * @param int $accountId
		 * @param string $accountNameSpace
		 * @return bool
		 */
		function UpdateAccountNameSpace($accountId, $accountNameSpace)
		{
			return $this->_connector->Execute($this->_commandCreator->UpdateAccountNameSpace($accountId, $accountNameSpace));
		}
		

		/**
		 * @param int $userLicenceNum
		 * @return bool
		 */
		function UpdateAUsersByLicences($userLicenceNum)
		{
			return $this->_connector->Execute($this->_commandCreator->UpdateAUsersByLicences($userLicenceNum));
		}
		
		/**
		 * @return bool
		 */
		function UpdateDeleteAllAUsers()
		{
			return $this->_connector->Execute($this->_commandCreator->UpdateDeleteAllAUsers());
		}

		/**
		 *
		 * @param Account $account
		 * @return bool
		 */
		function UpdateAccountImapQuota($account)
		{
			if ($account->ImapQuota == 1)
			{
				$this->_connector->Execute($this->_commandCreator->UpdateAccountImapQuotaOff($account->IdUser));
			}
			return $this->_connector->Execute($this->_commandCreator->UpdateAccountImapQuota($account->ImapQuota, $account->Id));
		}
		
		/**
		 * @param Account $account
		 * @return bool
		 */
		function UpdateColumns(&$account)
		{
			$existColumns = array();
			if (is_array($account->Columns) && count($account->Columns) > 0)
			{
				if (!$this->_connector->Execute($this->_commandCreator->SelectAccountColumnsData($account->IdUser)))
				{
					return false;
				}
				else 
				{
					while (($row = $this->_connector->GetNextRecord()) != false)
					{
						$existColumns[(int) $row->id_column] = $row->column_value;
					}
				}
				
				$cnt = count($existColumns);
				foreach ($account->Columns As $id_column => $colun_value)
				{
					if ($cnt > 0)
					{
						if (isset($existColumns[$id_column]))
						{
							if ($existColumns[$id_column] != $colun_value)
							{
								$result = $this->_connector->Execute($this->_commandCreator->UpdateColumnData($account->IdUser, $id_column, $colun_value));
								if (!$result)
								{
									return false;
								}
							}
						}
						else
						{
							$result = $this->_connector->Execute($this->_commandCreator->InsertColumnData($account->IdUser, $id_column, $colun_value));
							if (!$result)
							{
								return false;
							}							
						}
					}
					else 
					{
						$result = $this->_connector->Execute($this->_commandCreator->InsertColumnData($account->IdUser, $id_column, $colun_value));
						if (!$result)
						{
							return false;
						}	
					}
				}
			}
			return true;
		}
		
		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function UpdateFolder(&$folder)
		{
			return $this->_connector->Execute($this->_commandCreator->UpdateFolder($folder));
		}
		
		/**
		 * @param FolderCollection $folders
		 * @return bool
		 */
		function CreateFolders(&$folders)
		{
			$result = true;
			if ($folders == null)
			{
				return $result;
			}
			
			for ($i = 0, $c = $folders->Count(); $i < $c; $i++)
			{
				$folder =& $folders->Get($i);
				if ($result && $folder)
				{
					$result &= $this->CreateFolder($folder);
					
					if ($folder->SubFolders && $result)
					{
						for ($j = 0, $t = count($folder->SubFolders->Instance()); $j < $t; $j++)
						{
							$subFolder =& $folder->SubFolders->Get($j);
							$subFolder->IdParent = $folder->IdDb;
							unset($subFolder);
						}
						$result &= $this->CreateFolders($folder->SubFolders);
					}
				}
				unset($folder);
			}

			return $result;										
		}
		
		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function CreateFolder(&$folder)
		{
			if (!$this->_connector->Execute($this->_commandCreator->SelectForCreateFolder($folder)))
			{
				return false;
			}
			else 
			{
				$row = $this->_connector->GetNextRecord();
				$folder->FolderOrder = ($row && isset($row->norder)) ? (int) ($row->norder + 1) : 0;
			}
			
			if (!$this->_connector->Execute($this->_commandCreator->CreateFolder($folder)))
			{
				return false;
			}
			
			$folder->IdDb = $this->_connector->GetLastInsertId();

			if (!$this->_connector->Execute($this->_commandCreator->CreateFolderTree($folder)))
			{
				return false;
			}
			
			if (!$this->_connector->Execute($this->_commandCreator->SelectForCreateFolderTree($folder)))
			{
				return false;
			}
			else 
			{
				$result = array(); 
				while (($row = $this->_connector->GetNextRecord()) != false)
				{
					$IdParent = ($row && isset($row->id_parent)) ? (int) $row->id_parent : -1;
					$Level = ($row && isset($row->folder_level)) ? (int) ($row->folder_level + 1) : 0;
					
					$result[] = array($IdParent, $Level);
				}
				
				if ($result)
				{
					foreach ($result as $folderData)
					{
						if (!is_array($folderData)) 
						{
							continue;
						}
						$folder->IdParent = $folderData[0];
						$folder->Level = $folderData[1];
						if(!$this->_connector->Execute($this->_commandCreator->CreateSelectFolderTree($folder)))
						{
							return false;
						}	
					}
					
				}
			}

			return true;
		}
		
		/**
		 * @param	string	$pref
		 * @return	bool
		 */
		function CheckExistTable($pref)
		{
			$tableArray = GetTablesArray($pref);
			
			if (!$this->_connector->Execute($this->_commandCreator->AllTableNames()))
			{
				return false;
			}
			
			while (false !== ($array = $this->_connector->GetNextArrayRecord()))
			{
				$tableName = '';
				foreach ($array as $value)
				{
					$tableName = $value; 
					break;
				}
				
				if (in_array($tableName, $tableArray))
				{
					return $tableName;
				}
			}
			
			return true;
		}

		function IsInternalAccountExist($login)
		{
			$return = false;
			
			if ($this->_connector->Execute($this->_commandCreator->IsInternalAccountExist($login)))
			{
				$row = $this->_connector->GetNextArrayRecord();
				if (isset($row['acount']))
				{
					$return = ((int) $row['acount'] > 1);
				}
			}

			return $return;
		}
		
		/**
		 * @param	string	$pref
		 * @return	bool
		 */
		function CreateAllIndex($pref)
		{
			$AddIndexArray = GetIndexsArray();
			foreach ($AddIndexArray as $tableName => $indexData) 
			{
				if (is_array($indexData))
				{
					foreach ($indexData as $fieldName) 
					{
						if (!$this->CheckExistIndex($pref, $tableName, $fieldName))
						{
							if (!$this->CreateIndex($pref, $tableName, $fieldName))
							{
								return false;
							}
						}
					}
				}
			}
			return true;
		}
		
		/**
		 * @param	string	$pref
		 * @param	string	$TableName
		 * @param 	string	$fieldName
		 * @return	bool
		 */
		function CheckExistIndex($pref, $TableName, $fieldName)
		{
			$indexArray = $this->GetIndexsOfTable($pref, $TableName);
			if (is_array($indexArray))
			{
				return in_array($fieldName, $indexArray);
			}
			
			return false;	
		}

		/**
		 * @param 	string	$pref
		 * @param	string	$tableName
		 * @param	string	$fieldName
		 * @return	bool
		 */
		function CreateIndex($pref, $tableName, $fieldName)
		{
			return $this->_connector->Execute($this->_commandCreator->CreateIndex($pref, $tableName, $fieldName));
		}
		
		/**
		 * @param	string	$pref
		 * @param	string	$tablename
		 * @return	bool
		 */
		function IsTableExist($pref, $tablename)
		{
			if (!$this->_connector->Execute($this->_commandCreator->AllTableNames()))
			{
				return false;
			}
			
			while (($array = $this->_connector->GetNextArrayRecord()) != false)
			{
				$tableName = '';
				foreach ($array as $value)
				{
					$tableName = $value; 
					break;
				}
				
				if ($tableName == $pref.$tablename)
				{
					return true;
				}
			}
			
			return false;
		}
		
		/**
		 * @return array
		 */
		function AllTableNames()
		{
			$return = array();
			if (!$this->_connector->Execute($this->_commandCreator->AllTableNames()))
			{
				return false;
			}
			
			while (($array = $this->_connector->GetNextArrayRecord()) != false)
			{
				$tableName = '';
				foreach ($array as $value)
				{
					$tableName = $value; 
					break;
				}
				
				if (strlen($tableName) > 0)
				{
					$return[] = $tableName; 
				}
			}
			
			return $return;
		}
		
		function CreateOneTable($pref, $tableName)
		{
			$sql = trim($this->_commandCreator->CreateTable($tableName, $pref));
			if ($sql === '') 
			{
				return false;
			}
			if (!$this->_connector->Execute($sql))
			{
				return false;
			}		
			return true;

		}
		
		/**
		 * @param string $pref
		 * @param string $tableName
		 * @return array/bool
		 */
		function GetTablesColumns($pref, $tableName)
		{
			$returnArray = array();
			
			if (!$this->_connector->Execute($this->_commandCreator->GetTablesColumns($pref, $tableName)))
			{
				return false;
			}
			
			while (($array = $this->_connector->GetNextArrayRecord()) != false)
			{
				$tableColumn = '';
				foreach ($array as $value)
				{
					$tableColumn = $value; 
					break;
				}

				if (strlen($tableColumn) > 0)
				{
					$returnArray[] = $tableColumn;
				}
			}
			
			return $returnArray;			
		}
		
		/**
		 * @param	string	$pref
		 * @return	bool
		 */
		function CreateTables($pref)
		{
			$tableArray = GetTablesArray($pref);
			$original = GetTablesArray('');
			foreach ($tableArray as $key => $tname)			
			{
				$sql = trim($this->_commandCreator->CreateTable($original[$key], $pref));
				if ($sql === '') 
				{
					return false;
				}
				if (!$this->_connector->Execute($sql))
				{
					return $tname;	
				}				
			}

			return true;
		}
	}
	
	class webmail_MySQL_DbStorage extends main_DbStorage
	{
		/**
		 * @param	WebMail_Settings	$settings
		 * @return	webmail_MySQL_DbStorage
		 */
		function webmail_MySQL_DbStorage(&$settings)
		{
			$this->_settings =& $settings;
			
			include_once WM_DB_LIBS_PATH.'/commandcreator.php';
			$this->_commandCreator = new MySQL_CommandCreator(AP_DB_QUOTE_ESCAPE, array('`', '`'), $this->_settings->DbPrefix);
			
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
	
	class webmail_MSSQL_DbStorage extends main_DbStorage
	{
		/**
		 * @param	WebMail_Settings	$settings
		 * @return	webmail_MSSQL_DbStorage
		 */
		function webmail_MSSQL_DbStorage(&$settings)
		{
			$this->_settings =& $settings;
			
			include_once WM_DB_LIBS_PATH.'/commandcreator.php';
			$this->_commandCreator = new MSSQL_CommandCreator(AP_DB_QUOTE_ESCAPE,  array('[', ']'), $this->_settings->DbPrefix);
			
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
	class DbStorageCreator
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
					$instance = new webmail_MySQL_DbStorage($settings);
					break;
				case AP_DB_MSSQLSERVER:
					$instance = new webmail_MSSQL_DbStorage($settings);
					break;
			}
    		
			return $instance;
		}
	}