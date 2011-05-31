<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	class CWebMail_Plugin extends ap_Plugin
	{
		/**
		 * @var	WebMail_Settings
		 */
		var $_settings;

		/**
		 * @var webmail_MySQL_DbStorage
		 */
		var $_db;
		
		/**
		 * @var	bool
		 */
		var $_isConnect = false;
		
		/**
		 * @var	bool
		 */
		var $_isLoad = true;
		
		/**
		 * @var int
		 */
		var $_ptype = AP_L;
		
		/**
		 * @var	bool
		 */
		var $_useDbCreate = false;
		
		/**
		 * @var	bool
		 */
		var $HasInstall = true;

		/**
		 * @var	array
		 */
		var $HideDomains = array('afterlogic.com', 'mailstone.net', 'nerve.ru');

		/**
		 * @param	string	$_tab
		 * @return	string|false
		 */
		function GetScreenName($_tab)
		{
			$_return = false;
			switch ($_tab)
			{
				case 'wm':
					$_return = 'ap_Screen_Standard';
					break;
				case 'users':
				case 'domains':
					$_return = 'ap_Screen_Tables';
					break;
				case 'install':
					$_return = 'ap_Screen_Install';
					break;
			}
			
			return $_return;
		}
		
		/**
		 * @param	array	$_cfg
		 * @return	true|string
		 */
		function ValidateCfg($_cfg)
		{
			if (!isset($_cfg['webmail_web_path']))
			{
				return 'Path to WebMail web folder should be specified in "'.AP_CFG_FILE.'" config file.';
			}
		
			if (!@file_exists($_cfg['webmail_web_path'].'/inc_settings_path.php'))
			{
				return 'Path to WebMail web folder is incorrect as "inc_settings_path.php" file is not found.';
			}

			return true;
		}

		/**
		 * @param string $action
		 * @param array $arg
		 * @return bool
		 */
		function GlobalFunction($action, &$arg)
		{
			switch ($action)
			{
				case 'getInternalDomains':
					if ($this->Connect())
					{
						$list = $this->_db->DomainList();
						if (is_array($list))
						{
							$arg['list'] = $list;
						}
						return true;
					}
					break;
					
				case 'setLdapDomain':
					if ($this->Connect())
					{
						return $this->_db->SetLdapDomain($arg['domain']);
					}
					break;
				case 'setLicenseKey':
					$result = true;
					if (isset($arg['license_key'], $arg['db_string']))
					{
						$this->_settings->LicenseKey = $arg['license_key'];

						$arg['db_string'] =
							$this->_settings->DbLogin.
							$this->_settings->DbDsn.
							$this->_settings->DbCustomConnectionString;

						$result &= $this->_settings->SaveToXml();

						if ($result)
						{
							$ap =& $this->GetAp();
							$userLN = $ap->OLInfo($arg['license_key']);
							$userO = $userLN->ObjValues();
							if ($userLN->IsValid())
							{
								if ($this->Connect())
								{
									if (1 == $userO[1])
									{
										if ($userO[2] > 0)
										{
											$this->_db->UpdateDeleteAllAUsers();
											$this->_db->UpdateAUsersByLicences($userO[2]);
										}
									}
									else
									{
										$this->_db->UpdateAUsersByLicences(0);
									}
								}
							}
						}
					}

					return $result;
					break;
			}

			return true;
		}
		
		/**
		 * @return	string
		 */
		function WriteAction()
		{
			$_ap =& $this->GetAp();
			$_ref = null;
			$_errorDesc = $_infoDesc = '';
			$_saveSettings = true;
			$_saveSettingsNoSee = false;
			$isErrorInfo = false;
			
			if (!$this->_isLoad)
			{
				$this->_setError(ap_Utils::TakePhrase('WM_INFO_CANTLOAD_SETTINGS'));
				return $_ref;
			}

			if ($_ap->AuthType() === AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD ||
					$_ap->AuthType() === AP_SESS_AUTH_TYPE_NONE)
			{
				$this->_setError(ap_Utils::TakePhrase('AP_LANG_ADMIN_ONLY_READ'));
				return $_ref;
			}
			
			if (isset($_POST['form_id']) && strlen($_POST['form_id']) > 0)
			{
				if ($_ap->Tab() == 'wm')
				{
					$_customDomainSettings = $_domain = null;
					$_filter = isset($_POST['filter_host']) ? $_POST['filter_host'] : '';
					if ($_ap->PType() && strlen($_filter) > 0)
					{
						if ($this->Connect())
						{
							include $this->PluginPath().'/common/class_domains.php';
							$_domain = $this->_db->SelectDomainByName($_filter);
							if ($_domain)
							{
								$_customDomainSettings = clone $this->_settings;
								
								$_domain->UpdateSettings($_customDomainSettings);
								if (isset($_POST['txtUrl']))
								{
									$_domain->SetUrl($_POST['txtUrl']);
								}
							}
						}
					}

					switch ($_POST['form_id'])
					{
						case 'webmail':
						case 'interface':
						case 'login':

							$functionName = '';
							switch ($_POST['form_id'])
							{
								case 'webmail':		$functionName = 'Common'; 		break;
								case 'interface':	$functionName = 'WmInterface';	break;
								case 'login':		$functionName = 'Login';		break;
							}
							
							if (strlen($functionName) > 0)
							{
								if ($_customDomainSettings)
								{
									if (isset($_POST['submit']))
									{
										WmFillSettingsFromPost::$functionName($_customDomainSettings);
									}
									else
									{
										WMResetSettingsByScreen::$functionName($this->_settings, $_customDomainSettings);
									}
								}
								else
								{
									WmFillSettingsFromPost::$functionName($this->_settings);
								}
							}

							if ($_customDomainSettings && $_domain)
							{
								$_domain->InitBySettings($_customDomainSettings);
								if (!$this->_db->UpdateDomainById($_domain))
								{
									$_errorDesc = ap_Utils::TakePhrase('WM_DOMAIN_SAVE_ERROR_OCCURED').' '.$this->_db->GetError();
								}
							}

							break;

						case 'db':
							WmFillSettingsFromPost::Db($this->_settings);

							if ((isset($_POST['isTestConnection']) && $_POST['isTestConnection'] == '1')
									|| isset($_POST['test_btn']))
							{
								include $this->PluginPath().'/common/db/dbstorage.php';
	
								$_db =& DbStorageCreator::CreateDatabaseStorage($this->_settings);
								
								$_start = ap_Utils::Microtime();
								if ($_db->Connect())
								{ 
									$_infoDesc = ap_Utils::TakePhrase('WM_INFO_CONNECTSUCCESSFUL').' ('.substr((ap_Utils::Microtime() - $_start), 0, 6).' sec)';
									@$_db->Disconnect();
								}
								else
								{
									$_dberror = $_db->GetError();
									$_dberror = strlen($_dberror) > 5 ? AP_HTML_BR.$_dberror : '';
									$_errorDesc = ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL').$_dberror;
								}
							}
							else if (isset($_POST['create_db']) && $_ap->IsEnable())
							{
								include $this->PluginPath().'/common/db/dbstorage.php';
	
								$_db =& DbStorageCreator::CreateDatabaseStorage($this->_settings);
								
								if ($_db->ConnectNoSelect())
								{
									if ($_db->CreateDatabase($this->_settings->DbName))
									{
										$_infoDesc = ap_Utils::TakePhrase('WM_INFO_DBCREATESUCCESSFUL');		
									}
									else 
									{
										$_dberror = $_db->GetError();
										$_dberror = strlen($_dberror) > 5 ? AP_HTML_BR.$_dberror : '';
										$_errorDesc = ap_Utils::TakePhrase('WM_INFO_DBCREATEUNSUCCESSFUL').$_dberror;
									}
									
									@$_db->Disconnect();
								}
								else
								{
									$_dberror = $_db->GetError();
									$_dberror = strlen($_dberror) > 5 ? AP_HTML_BR.$_dberror : '';
									$_errorDesc = ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL').$_dberror;
								}
							}
							else if (isset($_POST['drop_db']) && $_ap->IsEnable())
							{
								include $this->PluginPath().'/common/db/dbstorage.php';
	
								$_db =& DbStorageCreator::CreateDatabaseStorage($this->_settings);
								
								if ($_db->ConnectNoSelect())
								{
									if ($_db->DropDatabase($this->_settings->DbName))
									{
										$_infoDesc = ap_Utils::TakePhrase('WM_INFO_DBDROPSUCCESSFUL');		
									}
									else 
									{
										$_dberror = $_db->GetError();
										$_dberror = strlen($_dberror) > 5 ? AP_HTML_BR.$_dberror : '';
										$_errorDesc = ap_Utils::TakePhrase('WM_INFO_DBCDROPUNSUCCESSFUL').$_dberror;
									}
									
									@$_db->Disconnect();
								}
								else
								{
									$_dberror = $_db->GetError();
									$_dberror = strlen($_dberror) > 5 ? AP_HTML_BR.$_dberror : '';
									$_errorDesc = ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL').$_dberror;
								}
							}
						
							//$_ref = '?mode='.WM_MODE_DB;
							break;
						case 'contacts':
							if ($_ap->PType())
							{
								WmFillSettingsFromPost::Contacts($this->_settings);
							}
							//$_ref = '?mode='.WM_MODE_CAL;
							
						case 'calendar':
							if ($_ap->PType())
							{
								WmFillSettingsFromPost::Cal($this->_settings);
							}
							//$_ref = '?mode='.WM_MODE_CAL;
							break;
						case 'mobile':
							WmFillSettingsFromPost::Mobile($this->_settings);
							break;
						case 'debug':
							if (isset($_POST['submit_btn']))
							{
								WmFillSettingsFromPost::Debug($this->_settings);
							}
							else
							{
								$_saveSettings = false;
								$fileName = '';
								if (isset($_POST['clear_event_btn']) && isset($_POST['txtPathForEventLog']))
								{
									$fileName = $_POST['txtPathForEventLog'];
								}
								else if (isset($_POST['clear_log_btn']) && isset($_POST['txtPathForLog']))
								{
									$fileName = $_POST['txtPathForLog'];
								}
								
								if (strlen($fileName) > 0)
								{
									if (@file_exists($fileName))
									{
										if (@unlink($fileName))
										{
											$_infoDesc = ap_Utils::TakePhrase('WM_INFO_LOGCLEARSUCCESSFUL');
										}
										else
										{
											$_errorDesc = ap_Utils::TakePhrase('WM_INFO_ERROR');
										}
									}
									else
									{
										$_infoDesc = ap_Utils::TakePhrase('WM_INFO_LOGCLEARSUCCESSFUL');
									}
								}
								else
								{
									$_errorDesc = ap_Utils::TakePhrase('WM_INFO_ERROR');
								}
							}
							//$_ref = '?mode='.WM_MODE_DEBUG;
							break;
							
						case 'integr':
							
							include_once $this->PluginPath().'/common/libs/class_xmail.php';
							
							if (!$this->_settings->EnableWmServer && $this->_settings->IncomingMailProtocol == WM_MAILPROTOCOL_WMSERVER)
							{
								$this->_settings->IncomingMailProtocol = WM_MAILPROTOCOL_POP3;
							}

							WmFillSettingsFromPost::Integr($this->_settings);
							
							if ($this->_settings->EnableWmServer)
							{
								$_rootPath = ap_Utils::PathPreparation($this->_settings->WmServerRootPath);
								if (strlen($_rootPath) > 1)
								{
									if (!@is_dir($_rootPath.'/domains'))
									{
										$_errorDesc = ap_Utils::TakePhrase('WM_INFO_ROOTPATHINCORRECT');
									}
									elseif (!@file_exists($_rootPath.'/'.WM_WEBMAILCONFIGTAB))
									{
										$_errorDesc = ap_Utils::TakePhrase('WM_INFO_CANTFIND').WM_WEBMAILCONFIGTAB;
									}
								}
								else
								{
									$_errorDesc = ap_Utils::TakePhrase('WM_INFO_ROOTPATHINCORRECT');
								}
								
								if (strlen(trim($this->_settings->WmServerHost)) == 0)
								{
									$_errorDesc = ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL');
								}
								
								if (strlen($_errorDesc) == 0)
								{					
									$_WMServer = new CWmServerConsoleAdm($this->_settings, $this->_settings->WmServerHost);
									if ($_WMServer)
									{
										$_req = $_WMServer->Connect();
										if (true !== $_req)
										{
											$_errorDesc = ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL').AP_HTML_BR.str_replace(array("\r", "\t", "\n"), ' ', $_WMServer->GetError());
										}
										else
										{
											$_WMServer->Disconnect();
										}
									}
									else
									{
										$_errorDesc = ap_Utils::TakePhrase('WM_INFO_ERROR');
									}
								}
							}
							//$_ref = '?mode='.WM_MODE_INTEGRATION;
							break;
					}
				}
				else if ($_ap->Tab() == 'install')
				{
					switch ($_POST['form_id'])
					{	
						case WM_INST_MODE_LICENSETEXT:
							$_saveSettings = false;
							$_ref = $_ap->PType() ? '?mode='.WM_INST_MODE_LICENSE : '?mode='.WM_INST_MODE_DB;
							break;
						case WM_INST_MODE_LICENSE:
							$_saveSettingsNoSee = true;
							$_ref = '?mode='.WM_INST_MODE_LICENSE;
							
							if (isset($_POST['txtLicenseKey']))
							{
								$this->_settings->LicenseKey = trim($_POST['txtLicenseKey']);
								
								if (!$_ap->SaveLicenseKeyData($this->_settings->LicenseKey))
								{
									$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_BAD;
									$_SESSION[WM_INST_SESS_MSG_TEXT] = ap_Utils::TakePhrase('AP_LANG_FILE_ERROR_OCCURRED');
								}
								else if ($_ap->PType() && $_ap->InstallOL($this->_settings->LicenseKey))						
								{
									$_ref = '?mode='.WM_INST_MODE_DB;
								}
							}
							break;
							
						case WM_INST_MODE_DB:

							$_ref = '?mode='.WM_INST_MODE_DB;
							if (isset($_SESSION[WM_INST_SESS_SETTINGS]) && is_string($_SESSION[WM_INST_SESS_SETTINGS]))
							{
								$this->_settings = unserialize($_SESSION[WM_INST_SESS_SETTINGS]);
								unset($_SESSION[WM_INST_SESS_SETTINGS]);
							}
							
							if (isset($_POST['intDbType']))
							{
								switch ($_POST['intDbType'])
								{
									case AP_DB_MSSQLSERVER:
										$this->_settings->DbType = AP_DB_MSSQLSERVER;
										break;
									default:
									case AP_DB_MYSQL:
										$this->_settings->DbType = AP_DB_MYSQL;
										break;
								}
							}
							
							$this->_settings->DbLogin = isset($_POST['txtSqlLogin']) ? $_POST['txtSqlLogin'] : $this->_settings->DbLogin;
							if (isset($_POST['txtSqlPassword']))
							{
								if ($_POST['txtSqlPassword'] != AP_DUMMYPASSWORD)
								{
									$this->_settings->DbPassword = $_POST['txtSqlPassword'];
								}
							}
							$this->_settings->DbName = isset($_POST['txtSqlName']) ? $_POST['txtSqlName'] : $this->_settings->DbName;
							$this->_settings->DbHost = isset($_POST['txtSqlSrc']) ? $_POST['txtSqlSrc'] : $this->_settings->DbHost;
							
							$this->_settings->UseDsn = (isset($_POST['useDSN']) && $_POST['useDSN'] == 1);
							$this->_settings->DbDsn = isset($_POST['txtSqlDsn']) ? $_POST['txtSqlDsn'] : $this->_settings->DbDsn;
							
							$this->_settings->DbCustomConnectionString = isset($_POST['odbcConnectionString']) ? $_POST['odbcConnectionString'] : $this->_settings->DbCustomConnectionString;
							$this->_settings->UseCustomConnectionString = (isset($_POST['useCS']) && $_POST['useCS'] == 1);
							
							if ((isset($_POST['isTestConnection']) && $_POST['isTestConnection'] == '1')
									|| isset($_POST['test_btn']))
							{
								$this->_settings->DbPrefix = isset($_POST['prefixString']) ? $_POST['prefixString'] : $this->_settings->DbPrefix;
							}
							else 
							{
								$_prefix = isset($_POST['prefixString']) ? $_POST['prefixString'] : $this->_settings->DbPrefix;
								$this->_settings->DbPrefix = ap_Utils::ClearPrefix($_prefix);
								
								if ($_prefix != $this->_settings->DbPrefix)
								{
									$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_BAD;
									$_SESSION[WM_INST_SESS_MSG_TEXT] = 'Only letters, digits and underscore ("_") allowed.';
									$_ref = '?mode='.WM_INST_MODE_DB;
									$_SESSION[WM_INST_SESS_SETTINGS] = serialize($this->_settings);
									break;
								}
							}
													
							include $this->PluginPath().'/common/db/dbstorage.php';

							$_db =& DbStorageCreator::CreateDatabaseStorage($this->_settings);
							$_start = ap_Utils::Microtime();
							
							if ((isset($_POST['isTestConnection']) && $_POST['isTestConnection'] == '1')
									|| isset($_POST['test_btn']))
							{
								if ($_db->ConnectNoSelect())
								{
									if ($_db->Select())
									{
										if ($_db->AdminTestTableCreate())
										{
											$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_GOOD;
											$_SESSION[WM_INST_SESS_MSG_TEST_TEXT] = ap_Utils::TakePhrase('WM_INFO_DBTESTDATABESSUCCESS');	
										}
										else
										{
											$_dberror = $_db->GetError();
											$_dberror = strlen($_dberror) > 5 ? AP_CRLF.$_dberror : '';
											$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_BAD;
											$_SESSION[WM_INST_SESS_MSG_TEST_TEXT] = nl2br(
											ap_Utils::TakePhrase('WM_INFO_DBTESTDATABESERROR').$_dberror.AP_CRLF.
											ap_Utils::TakePhrase('WM_INFO_DBTESTDATABESCREATETESTERROR'));
										}
									}
									else
									{
										$_dberror = $_db->GetError();
										$_dberror = strlen($_dberror) > 5 ? AP_CRLF.$_dberror : '';
										$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_BAD;
										$_SESSION[WM_INST_SESS_MSG_TEST_TEXT] = nl2br(
										ap_Utils::TakePhrase('WM_INFO_DBTESTDATABESERROR').$_dberror.AP_CRLF.
										ap_Utils::TakePhrase('WM_INFO_DBTESTDATABESSELECTERRORADD'));
									}

									@$_db->Disconnect();
								}
								else
								{
									$_dberror = $_db->GetError();
									$_dberror = strlen($_dberror) > 5 ? AP_CRLF.$_dberror : '';
									$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_BAD;
									$_SESSION[WM_INST_SESS_MSG_TEST_TEXT] = nl2br(ap_Utils::TakePhrase('WM_INFO_DBTESTDATABESERROR').$_dberror);
								}
								$_ref = '?mode='.WM_INST_MODE_DB;
								$_SESSION[WM_INST_SESS_SETTINGS] = serialize($this->_settings);
							}
							else if ((isset($_POST['isCreateDb']) && $_POST['isCreateDb'] == '1')
									|| isset($_POST['create_db_btn'])) 
							{
								if ($_db->ConnectNoSelect())
								{
									if ($_db->CreateDatabase($this->_settings->DbName))
									{
										$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_GOOD;
										$_SESSION[WM_INST_SESS_MSG_CREATE_DB_TEXT] = ap_Utils::TakePhrase('WM_INFO_DBCREATESUCCESSFUL');
									}
									else
									{
										$_dberror = $_db->GetError();
										$_dberror = strlen($_dberror) > 5 ? AP_CRLF.$_dberror : '';
										$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_BAD;
										$_SESSION[WM_INST_SESS_MSG_CREATE_DB_TEXT] = nl2br(ap_Utils::TakePhrase('WM_INFO_DBCREATEUNSUCCESSFUL').$_dberror);
									}
									
									@$_db->Disconnect();
								}
								else
								{
									$_dberror = $_db->GetError();
									$_dberror = strlen($_dberror) > 5 ? AP_CRLF.$_dberror : '';
									$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_BAD;
									$_SESSION[WM_INST_SESS_MSG_CREATE_DB_TEXT] = nl2br(ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL').$_dberror);
								}
								$_ref = '?mode='.WM_INST_MODE_DB;
								$_SESSION[WM_INST_SESS_SETTINGS] = serialize($this->_settings);
							}
							else 
							{
								if ($_db->Connect())
								{
									$_bodyText = '';
									if (!isset($_POST['chNotCreate']))
									{
										$_saveSettingsNoSee = true;
										$_ref = $_ap->PType() ? '?mode='.WM_INST_MODE_MOBILESYNC : '?mode='.WM_INST_MODE_COMMON;
										unset($_SESSION[WM_INST_SESS_SETTINGS]);
									}
									else if ($_db->AdminAllTableCreate($_bodyText, true))
									{
										$_saveSettingsNoSee = true;
										$_ref = $_ap->PType() ? '?mode='.WM_INST_MODE_MOBILESYNC : '?mode='.WM_INST_MODE_COMMON;
										unset($_SESSION[WM_INST_SESS_SETTINGS]);
									}
									else 
									{
										$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_BAD;
										$_SESSION[WM_INST_SESS_MSG_TEXT] = nl2br($_bodyText);
										$_ref = '?mode='.WM_INST_MODE_DB;
										$_SESSION[WM_INST_SESS_SETTINGS] = serialize($this->_settings);
									}
									
									@$_db->Disconnect();
								}
								else
								{
									$_dberror = $_db->GetError();
									$_dberror = strlen($_dberror) > 5 ? AP_HTML_BR.$_dberror : '';
									$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_BAD;
									$_SESSION[WM_INST_SESS_MSG_TEXT] = nl2br(ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL').$_dberror);
									$_ref = '?mode='.WM_INST_MODE_DB;
									$_SESSION[WM_INST_SESS_SETTINGS] = serialize($this->_settings);
								}
							}
							
							if (!$_saveSettingsNoSee)
							{
								$_saveSettings = false;
							}
							break;
						case WM_INST_MODE_MOBILESYNC:
							$_ref = '?mode='.WM_INST_MODE_MOBILESYNC;

							if (isset($_POST['chEnableMobileSync']) && $_POST['chEnableMobileSync'] == '1')
							{
								$this->_settings->EnableMobileSync = true;

								$this->_settings->MobileSyncUrl = isset($_POST['txtMobileSyncUrl'])
									? $_POST['txtMobileSyncUrl'] : $this->_settings->MobileSyncUrl;
								$this->_settings->MobileSyncContactDataBase = isset($_POST['txtMobileSyncContactDatabase'])
									? $_POST['txtMobileSyncContactDatabase'] : $this->_settings->MobileSyncContactDataBase;
								$this->_settings->MobileSyncCalendarDataBase = isset($_POST['txtMobileSyncCalendarDatabase'])
									? $_POST['txtMobileSyncCalendarDatabase'] : $this->_settings->MobileSyncCalendarDataBase;
							}
							else
							{
								$this->_settings->EnableMobileSync = false;
							}

							$_ref = '?mode='.WM_INST_MODE_COMMON;
							break;
						case WM_INST_MODE_COMMON:
							$_saveSettings = false;
							$_ref = '?mode='.WM_INST_MODE_COMMON;
							
							if (isset($_POST['txtPassword1'], $_POST['txtPassword2']))
							{
								if ($_POST['txtPassword1'] == $_POST['txtPassword2'])
								{
									$_SESSION[WM_INST_IS_ADM_PSW] = true;
									
									if ($_POST['txtPassword1'] == AP_DUMMYPASSWORD || $_ap->SaveAdminPassword($_POST['txtPassword1']))
									{
										$_ref = '?mode='.WM_INST_MODE_SOCKET;
									}
									else 
									{
										$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_BAD;
										$_SESSION[WM_INST_SESS_MSG_TEXT] = ap_Utils::TakePhrase('AP_LANG_FILE_ERROR_OCCURRED');
									}	
								}
								else
								{
									$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_BAD;
									$_SESSION[WM_INST_SESS_MSG_TEXT] = ap_Utils::TakePhrase('WM_INST_PASSWORDS_NOT_MATCH');
								}
							}
							break;
						case WM_INST_MODE_SOCKET:
							$_saveSettings = false;
							if ((isset($_POST['isTestConnection']) && $_POST['isTestConnection'] == '1')
									|| isset($_POST['test_btn']))
							{
								if (isset($_POST['txtHost']) && strlen($_POST['txtHost']) > 0)
								{
									$_msg = '';
									$_host = $_POST['txtHost'];
									$_p = 0;
									$_addMsg = false;
									$_isSSL = (isset($_POST['chSSL']) && $_POST['chSSL'] == '1');
									
									$_new_host = $_host;
									if (strtolower(substr($_host, 0, 6)) != 'ssl://')
									{
										if ($_isSSL)
										{
											$_new_host = 'ssl://'.$_host;
										}
									}
									
									$_err_n = 0;
									$_err_s = '';
									if (isset($_POST['chSMTP']) && $_POST['chSMTP'] == '1')
									{
										$_p |= 4;
										$_res = @fsockopen($_new_host, $_isSSL ? 465 : 25, $_err_n, $_err_s, 5);
										if (is_resource($_res))
										{
											@fclose($_res);
											$_msg .= '<font color="green">SMTP connection to port '.($_isSSL ? 465 : 25).' successful, sending outgoing e-mail over SMTP should work.</font><br />';
										}
										else 
										{
											$_addMsg = true;
											$_msg .= '<font color="red">SMTP connection to port '.($_isSSL ? 465 : 25).' failed: '.$_err_s.' (Error code: '.$_err_n.')</font><br />';
										}
									}
									
									$_err_n = 0;
									$_err_s = '';
									if (isset($_POST['chPOP3']) && $_POST['chPOP3'] == '1')
									{
										$_p |= 1;
										$_res = @fsockopen($_new_host, $_isSSL ? 995 : 110, $_err_n, $_err_s, 5);
										if (@is_resource($_res))
										{
											@fclose($_res);
											$_msg .= '<font color="green">POP3 connection to port '.($_isSSL ? 995 : 110).' successful, checking and downloading incoming e-mail over POP3 should work.</font><br />';
										}
										else 
										{
											$_addMsg = true;
											$_msg .= '<font color="red">POP3 connection to port '.($_isSSL ? 995 : 110).' failed: '.$_err_s.' (Error code: '.$_err_n.')</font><br />';
										}
									}
									
									$_err_n = 0;
									$_err_s = '';
									if (isset($_POST['chIMAP4']) && $_POST['chIMAP4'] == '1')
									{
										$_p |= 2;
										$_res = @fsockopen($_new_host, $_isSSL ? 993 : 143, $_err_n, $_err_s, 5);
										if (is_resource($_res))
										{
											@fclose($_res);
											$_msg .= '<font color="green">IMAP connection to port '.($_isSSL ? 993 : 143).' successful, checking and downloading incoming e-mail over IMAP should work.</font><br />';
										}
										else 
										{
											$_addMsg = true;
											$_msg .= '<font color="red">IMAP connection to port '.($_isSSL ? 993 : 143).' failed: '.$_err_s.' (Error code: '.$_err_n.')</font><br />';
										}
									}
									
									if ($_addMsg)
									{
										$_msg .= '<br />
Check your firewall settings and e-mail server configuration, make
sure the e-mail server host name is spelled correctly and the computer
running this installer can access the e-mail server over the required
ports.';
									}
									
									$_SESSION[WM_INST_SESS_CH_HOST] = $_host;
									$_SESSION[WM_INST_SESS_CH_PROTOCOLS] = $_p;
									$_SESSION[WM_INST_SESS_CH_MSG] = $_msg;
								}
								else
								{
									$_SESSION[WM_INST_SESS_MSG_TYPE] = WM_INST_MSG_TYPE_BAD;
									$_SESSION[WM_INST_SESS_MSG_TEXT] = ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL');
								}
								
								$_ref = '?mode='.WM_INST_MODE_SOCKET;
							}
							else 
							{
								$_ref = '?mode='.WM_INST_MODE_END;
							}
							break;
						case WM_INST_MODE_END:
							$_saveSettings = false;
							/* $_ref = '?logout'; */
							
							$_SESSION[AP_SESS_AUTH] = session_id();
							$_SESSION[AP_SESS_DOENTER] = true;
							$_ap->SetAdminAccessType(AP_SESS_AUTH_TYPE_SUPER_ADMIN);
							unset($_SESSION[AP_SESS_INSTALL]);
							unset($_SESSION[AP_SESS_TAB]);
							unset($_SESSION[AP_SESS_MODE]);
							$_ref = '?enter';
							break;
					}
				}
			}
			else if (isset($_POST['mode_name']) && strlen($_POST['mode_name']) > 0)
			{
				$_saveSettings = false;

				if ($this->Connect())
				{
					if ($_ap->Tab() == 'domains')
					{
						switch ($_POST['mode_name'])
						{
							case 'collection':
								if (isset($_POST['chCollection']) && count($_POST['chCollection']) > 0)
								{
									$_domains_for_delete = array();
									foreach ($_POST['chCollection'] as $_value)
									{
										$_expl = explode(AP_UIDS_DELIMITER, $_value);
										if (is_array($_expl))
										{
											foreach ($_expl as $_item)
											{
												$_arr = ap_Utils::UidExplode($_item);
												if ($_arr[0] == AP_TYPE_WEBMAIL)
												{
													$_domains_for_delete[] = (int) $_arr[1];
												}				
											}
										}
									}
	
									if (count($_domains_for_delete) > 0)
									{
										if ($this->_db->DeleteDomainsByIds($_domains_for_delete))
										{
											$_infoDesc = ap_Utils::TakePhrase('WM_DELETE_SUCCESSFUL');
										}
										else
										{
											$_errorDesc = ap_Utils::TakePhrase('WM_DELETE_UNSUCCESSFUL');
										}
									}
								}
								
								break;
	
							case 'new':
								if (isset($_POST['switchElement']))
								{
									switch ($_POST['switchElement'])
									{
										case 'ext':
											if (isset($_POST['textDomainName']) && strlen($_POST['textDomainName']) > 0)
											{
												$_POST['textDomainName'] = strtolower($_POST['textDomainName']);
												include_once $this->PluginPath().'/common/class_domains.php';
												
												$_domain = new CWebMailDomain();
												$_domain->Init(
													$_POST['textDomainName'],
													(isset($_POST['intIncomingMailProtocol_domain']) && $_POST['intIncomingMailProtocol_domain'] == 'IMAP4') ? WM_MAILPROTOCOL_IMAP4 : WM_MAILPROTOCOL_POP3,
													(isset($_POST['txtIncomingMail_domain'])) ? $_POST['txtIncomingMail_domain'] : null,
													(isset($_POST['intIncomingMailPort_domain'])) ? (int) $_POST['intIncomingMailPort_domain'] : null,
													(isset($_POST['txtOutgoingMail_domain'])) ? $_POST['txtOutgoingMail_domain'] : null,
													(isset($_POST['intOutgoingMailPort_domain'])) ? (int) $_POST['intOutgoingMailPort_domain'] : null,
													(isset($_POST['intReqSmtpAuthentication_domain']) && $_POST['intReqSmtpAuthentication_domain'] == 1),
													(isset($_POST['intIsInternal_domain']) && $_POST['intIsInternal_domain'] == 1),
													(isset($_POST['intDomainGlobalAddrBook']) && $_POST['intDomainGlobalAddrBook'] == 1),
													(isset($_POST['intLDAPAuth']) && $_POST['intLDAPAuth'] == 1)
												);
												
												$_domain->SetUrl($_domain->Name());
												$_domain->InitBySettings($this->_settings);
												$_domain->SetSessionArray();
												
												if ($this->_db->DomainExist($_domain))
												{
													$_ref = '?mode=new&type='.urlencode($_POST['switchElement']);
													$_errorDesc = ap_Utils::TakePhrase('WM_DOMAIN_EXIST');
												}
												else
												{
													if ($this->_db->CreateDomain($_domain))
													{
														$_domain->ClearSessionArray();
														$_infoDesc = ap_Utils::TakePhrase('WM_DOMAIN_CREATESUCCESSFUL');
														$GLOBALS[AP_GLOBAL_USERFILTER_HREFS][] = AP_TYPE_WEBMAIL.AP_TYPE_DELIMITER.$_domain->_id;
													}
													else
													{
														$_ref = '?mode=new&type='.urlencode($_POST['switchElement']);
														$_errorDesc = ap_Utils::TakePhrase('WM_DOMAIN_CREATEUNSUCCESSFUL');
													}
												}
											}
											break;
											
										case 'int':
											if (isset($_POST['textDomainName']) && strlen($_POST['textDomainName']) > 0)
											{
												$_POST['textDomainName'] = strtolower($_POST['textDomainName']);
												
												include $this->PluginPath().'/common/class_domains.php';
												include $this->PluginPath().'/common/libs/class_xmail.php';
												
												$_WmSettings = new WMSettings($this->_settings->WmServerRootPath, $this->_settings->WmServerHost);
												$_domain = new CWebMailDomain();
												$_domain->Init(
													$_POST['textDomainName'],
													WM_MAILPROTOCOL_WMSERVER,
													$_WmSettings->Host,
													null,
													$_WmSettings->Host,
													$_WmSettings->OutPort,
													true,
													true
												);

												$_domain->SetUrl($_domain->Name());
												$_domain->InitBySettings($this->_settings);
												$_domain->SetSessionArray();
												
												if ($this->_db->DomainExist($_domain))
												{
													$_ref = '?mode=new&type='.urlencode($_POST['switchElement']);
													$_errorDesc = ap_Utils::TakePhrase('WM_DOMAIN_EXIST');
												}
												else
												{
													if ($this->_db->CreateDomain($_domain))
													{
														$_domain->ClearSessionArray();
														$_infoDesc = ap_Utils::TakePhrase('WM_DOMAIN_CREATESUCCESSFUL');
														$GLOBALS[AP_GLOBAL_USERFILTER_HREFS][] = AP_TYPE_WEBMAIL.AP_TYPE_DELIMITER.$_domain->_id;
													}
													else
													{
														$_ref = '?mode=new&type='.urlencode($_POST['switchElement']);
														$_errorDesc = ap_Utils::TakePhrase('WM_DOMAIN_CREATEUNSUCCESSFUL');
													}
												}
											}	
											break;
									}
								}
								break;
							case 'edit':
								if (isset($_POST['uid']) && strlen($_POST['uid']) > 2 && strpos($_POST['uid'], 'wm') !== false) 
								{
									include $this->PluginPath().'/common/class_domains.php';

									$_uid = urldecode($_POST['uid']);
									$_domainId = -1;
									$_arr = explode(AP_UIDS_DELIMITER, $_uid);
									foreach ($_arr as $_uid)
									{
										$_arrU = ap_Utils::UidExplode($_uid);
										if (isset($_arrU[0], $_arrU[1]) && $_arrU[0] == AP_TYPE_WEBMAIL)
										{
											$_domainId = (int) $_arrU[1];
										}
									}
									
									$_domain = $this->_db->SelectDomainById($_domainId);
									if ($_domain)
									{
										$_domain->Init(
											null,
											null,
											(isset($_POST['txtIncomingMail_domain'])) ? $_POST['txtIncomingMail_domain'] : null,
											(isset($_POST['intIncomingMailPort_domain'])) ? (int) $_POST['intIncomingMailPort_domain'] : null,
											(isset($_POST['txtOutgoingMail_domain'])) ? $_POST['txtOutgoingMail_domain'] : null,
											(isset($_POST['intOutgoingMailPort_domain'])) ? (int) $_POST['intOutgoingMailPort_domain'] : null,
											(isset($_POST['intReqSmtpAuthentication_domain']) && $_POST['intReqSmtpAuthentication_domain'] == 1),
											(isset($_POST['intIsInternal_domain']) && $_POST['intIsInternal_domain'] == 1),
											(isset($_POST['intDomainGlobalAddrBook']) && $_POST['intDomainGlobalAddrBook'] == 1),
											(isset($_POST['intLDAPAuth']) && $_POST['intLDAPAuth'] == 1)
										);

										if (!isset($_POST['save']))
										{
											$_domain->SetUrl($_domain->Name());
										}

										$_domain->SetSessionArray();
									}

									if ($_domain && $this->_db->UpdateDomainById($_domain))
									{
										$_domain->ClearSessionArray();
										$_infoDesc = ap_Utils::TakePhrase('WM_DOMAIN_CREATESUCCESSFUL');
									}
									else
									{
										$_ref = '?mode=edit&uid='.urlencode($_POST['uid']);
										$_errorDesc = ap_Utils::TakePhrase('WM_DOMAIN_CREATEUNSUCCESSFUL').$this->_db->GetError();
									}
								}
								break;
						} /* switch ($_POST['mode_name']) */
					} /* if ($_ap->Tab() == 'domains') */
					else if ($_ap->Tab() == 'users')
					{
						$_domain = null;
						$_domainId = -1;
						$_key = (isset($_SESSION[AP_SESS_FILTER]) && strlen($_SESSION[AP_SESS_FILTER]) > 0) ? $_SESSION[AP_SESS_FILTER] : null;
						$_resp = $this->_thisIsMyUid($_key);
						
						if ($_resp == AP_TYPE_WEBMAIL.AP_TYPE_DELIMITER.'all') 
						{
							$_domainId = 0;
						}
						else
						{
							if ($_resp !== false)
							{
								$_arr = ap_Utils::UidExplode($_resp);
								if (strlen($_arr[0]) > 0)
								{
									$_domainId = (int) $_arr[1];
								}
							}
						}
						
						if ($_domainId === 0)
						{
							include_once $this->PluginPath().'/common/class_domains.php';
							$_domain = new CWebMailDomain();
							$_domain->Init(ap_Utils::TakePhrase('WM_FILTER_ALL'),
								WM_MAILPROTOCOL_ALL, 
								$this->_settings->IncomingMailServer,
								$this->_settings->IncomingMailPort, 
								$this->_settings->OutgoingMailServer, 
								$this->_settings->OutgoingMailPort,
								true);
							
							$_domain->_id = 0;
						}
						else if ($_domainId > 0)
						{
							include $this->PluginPath().'/common/class_domains.php';
							$_domain = $this->_db->SelectDomainById($_domainId);
						}
						
						switch ($_POST['mode_name'])
						{
							case 'collection':
								if ($_domain && isset($_POST['chCollection']) && count($_POST['chCollection']) > 0)
								{
									$_users_for_action = array();
									foreach ($_POST['chCollection'] as $_value)
									{
										$_expl = explode(AP_UIDS_DELIMITER, $_value);
										if (is_array($_expl))
										{
											foreach ($_expl as $_item)
											{
												$_arr = ap_Utils::UidExplode($_item);
												if ($_arr[0] == AP_TYPE_WEBMAIL)
												{
													$_users_for_action[] = (int) $_arr[1];
												}				
											}
										}
									}

									$_result = true;
									if (count($_users_for_action) > 0)
									{
										foreach ($_users_for_action as $_id)
										{
											if ($_result)
											{
												$_account =& $this->_db->SelectAccountData($_id);
												if ($_account)
												{
													$_mailProcessor = new MailProcessor($_account, $this->_db, $this->PluginPath().'/common/');
													
													// delete users
													if ($_POST['action'] && $_POST['action'] == "delete")
													{
														if ($_mailProcessor->DeleteAccount())
														{
															if ($_domain->_isInternal)
															{
																$_globalArg = array(
																	'domain' => $_domain->Name(),
																	'login' => $_account->MailIncLogin,
																	'quota' => $_account->MailboxLimit
																);
																$_ap->GlobalFunction('deleteEximUser', $_globalArg);
															}
														}
														else
														{
															$_result = false;
														}
													}
													// enable users
													else if ($_POST['action'] && $_POST['action'] == "enable")
													{
														$_account->Deleted = false;
														if (!$_mailProcessor->UpdateAccount($_account))
														{
															$_result = false;
														}
													}
													// disable users
													else if ($_POST['action'] && $_POST['action'] == "disable")
													{
														$_account->Deleted = true;
														if (!$_mailProcessor->UpdateAccount($_account))
														{
															$_result = false;
														}
													}
													else
													{
														$_result = false;
													}
													
												}
												else
												{
													$_result = false;
												}
												unset($_account);
											}
										}										
									}
									
									if ($_POST['action'] && $_POST['action'] == "delete")
									{
										if ($_result)
										{
											$_infoDesc = ap_Utils::TakePhrase('WM_DELETE_SUCCESSFUL');
										}
										else
										{
											$_errorDesc = ap_Utils::TakePhrase('WM_DELETE_UNSUCCESSFUL');
										}
									}
									if ($_POST['action'] && $_POST['action'] == "enable")
									{
										if ($_result)
										{
											$_infoDesc = ap_Utils::TakePhrase('WM_ENABLE_SUCCESSFUL');
										}
										else
										{
											$_errorDesc = ap_Utils::TakePhrase('WM_ENABLE_UNSUCCESSFUL');
										}
									}
									if ($_POST['action'] && $_POST['action'] == "disable")
									{
										if ($_result)
										{
											$_infoDesc = ap_Utils::TakePhrase('WM_DISABLE_SUCCESSFUL');
										}
										else
										{
											$_errorDesc = ap_Utils::TakePhrase('WM_DISABLE_UNSUCCESSFUL');
										}
									}
								}
								
								break;
	
							case 'new':
								
								if (isset($_POST['switchElement']))
								{
									switch ($_POST['switchElement'])
									{
										case 'createUser':
											if ($_domain && $_domain->_mailProtocol != WM_MAILPROTOCOL_WMSERVER)
											{
												$isUserLimit = (bool) $this->_checkIsUserCountLimit();

												$_settings = $this->_settings;

												if ($_ap->PType() && $_domain->_id > 0)
												{
													$_domain->UpdateSettings($_settings);
												}

												$_account = new Account($_settings, $_domain);
												$_account->DefaultAccount = true;
												$_sync = $_account->GetDefaultFolderSync();
												WmMainFillClass::AccountFromPost($_account, $_sync);

												$_account->SetSessionArray();
												if (!$isUserLimit)
												{
													if ($_domain->_isInternal)
													{
														$_domain->InitInternalAccountLogin($_account);
													}

													$validate = $_account->ValidateData();
													if (true !== $validate)
													{
														$_errorDesc = $validate;
													}

													if (strlen($_errorDesc) == 0 && $_account->DefaultAccount && $_account->IsInternal)
													{
														if ($this->_db->IsInternalAccountExist($_account->MailIncLogin))
														{
															$_errorDesc = ap_Utils::TakePhrase('WM_CANT_ADD_DEF_ACCT');
														}
													}

													if (strlen($_errorDesc) == 0 && $_domain->_isInternal)
													{
														$_globalArg = array(
															'domain' => $_domain->Name(),
															'login' => $_account->MailIncLogin,
															'quota' => $_account->MailboxLimit
														);

														if (!$_ap->GlobalFunction('createEximUser', $_globalArg))
														{
															$_errorDesc = WM_ACCOUNT_CREATEUNSUCCESSFUL;
														}
													}

													if (strlen($_errorDesc) == 0)
													{
														$_mailProcessor = new MailProcessor($_account, $this->_db, $this->PluginPath().'/common/');
														if ($_account->IsInternal && $_mailProcessor->DbStorage->Connect())
														{
															$_mailProcessor->DbStorage->InsertAccountData($_account); // need for exim (login before create account)
														}

														if ($_mailProcessor->MailStorage->Connect(true))
														{
															if ($_mailProcessor->CreateAccount($_account, $_sync))
															{
																$_account->ClearSessionArray();
																$_infoDesc = ap_Utils::TakePhrase('WM_ACCOUNT_CREATESUCCESSFUL');
															}
															else
															{
																if ($_domain->_isInternal)
																{
																	$_mailProcessor->DbStorage->DeleteEximAccountData($_account);
																	$_ap->GlobalFunction('deleteEximUser', $_globalArg);
																}
																$_errorDesc = $_mailProcessor->GetError();
															}
														}
														else
														{
															if ($_domain->_isInternal)
															{
																$_mailProcessor->DbStorage->DeleteEximAccountData($_account);
																$_ap->GlobalFunction('deleteEximUser', $_globalArg);
															}
															$_errorDesc = $_mailProcessor->MailStorage->GetError();
														}
													}
												}
												else
												{
													$_errorDesc = WM_ACCOUNT_COUDNT_CREATE_LIMIT;
												}
											}

											if (strlen($_errorDesc) > 0)
											{
												$_ref = '?mode=new&type=new';
											}
				
											break;

										case 'new':
											if ($_domain && $_domain->_mailProtocol == WM_MAILPROTOCOL_WMSERVER && isset($_POST['userType']) && isset($_POST['txtXmailLogin']) && isset($_POST['txtXmailPassword']) && $_POST['userType'] == 'new')
											{
												if ($this->_settings->EnableWmServer && !@file_exists($this->_settings->WmServerRootPath.'/server.tab'))
												{
													$_ref = '?mode=new&type=new';
													$_errorDesc = ap_Utils::TakePhrase('WM_INFO_ROOTPATHINCORRECT');
												} 
												else if (strlen(trim($_POST['txtXmailLogin'])) == 0 || strlen(trim($_POST['txtXmailPassword'])) == 0)
												{
													$_errorDesc = ap_Utils::TakePhrase('WM_ERROR_USERNAMEPASSWORDNULL');
												}
												else
												{
													$isUserLimit = (bool) $this->_checkIsUserCountLimit();
													if (!$isUserLimit)
													{
														$_account = new Account($this->_settings, $_domain);
														$_account->DefaultAccount = true;
														
														$_account->MailProtocol = WM_MAILPROTOCOL_WMSERVER;
														$_account->Email = $_POST['txtXmailLogin'].'@'.$_domain->_name;
														$_account->MailIncLogin = $_POST['txtXmailLogin'];
														$_account->MailIncPassword = $_POST['txtXmailPassword'];
														$_account->Delimiter = '.';

														$_sync = $_account->GetDefaultFolderSync();
														
														$_mailProcessor = new MailProcessor($_account, $this->_db, $this->PluginPath().'/common/');
														if ($_mailProcessor->CreateAccount($_account, $_sync))
														{
															$_infoDesc = ap_Utils::TakePhrase('WM_ACCOUNT_CREATESUCCESSFUL'); 
														}
														else
														{
															$_ref = '?mode=new&type=new';
															$_errorDesc = $_mailProcessor->GetError();
														}
													}
													else
													{
														$_ref = '?mode=new&type=new';
														$_errorDesc = WM_ACCOUNT_COUDNT_CREATE_LIMIT;
													}
												}
											}
											break;
										case 'mlist':
											if ($_domain && 
													($_domain->_mailProtocol == WM_MAILPROTOCOL_WMSERVER || $_domain->_isInternal)
													&& isset($_POST['userType']) && isset($_POST['txtXmailLogin']) && $_POST['userType'] == 'mlist')
											{
												if (strlen(trim($_POST['txtXmailLogin'])) == 0)
												{
													$_errorDesc = ap_Utils::TakePhrase('WM_ERROR_USERNAMENULL');
												}
												else
												{
													$_account = new Account($this->_settings, $_domain);
													$_account->DefaultAccount = true;
													$_account->IsMailList = true;
													
													$_account->MailProtocol = $_domain->_mailProtocol;
													$_account->Email = $_POST['txtXmailLogin'].'@'.$_domain->_name;
													$_account->MailIncLogin = $_POST['txtXmailLogin'];
													$_account->MailIncPassword = '';
													$_account->Delimiter = '.';
													
													$_sync = $_account->GetDefaultFolderSync();
													$_mailProcessor = new MailProcessor($_account, $this->_db, $this->PluginPath().'/common/');
													
													if ($_account->IsInternal && $_mailProcessor->DbStorage->Connect())
													{
														$_mailProcessor->DbStorage->InsertUserData($_account);
														$_mailProcessor->DbStorage->InsertAccountData($_account);
														$_infoDesc = ap_Utils::TakePhrase('WM_ACCOUNT_CREATESUCCESSFUL');
													}
													else
													{
														$_ref = '?mode=new&type=mlist';
														$_errorDesc = $_mailProcessor->GetError();
													}
												}
											}
											break;
									}
								}
								break;
							case 'edit':
								if (isset($_POST['switchElement']))
								{
									switch ($_POST['switchElement'])
									{
										case 'edit':

											if ($_domain && isset($_POST['uid']) && strlen($_POST['uid']) > 2 &&
													isset($_POST['intAccountId']) && (int) $_POST['intAccountId'] > 0)
											{
												$_postArray = new Account($this->_settings, $_domain);

												$_sync = $_postArray->GetDefaultFolderSync();
												WmMainFillClass::AccountFromPost($_postArray, $_sync);
												$_postArray->SetSessionArray();
											
												$_account =& $this->_db->SelectAccountData($_POST['intAccountId']);
												
												if ($_account && !$_account->IsMailList)
												{
													WmMainFillClass::AccountFromPost($_account, $_sync);

													$_domain->UpdateAccount($_account);
													$_domain->InitInternalAccountLogin($_account);
													
													$_mailProcessor = new MailProcessor($_account, $this->_db, $this->PluginPath().'/common/');
													if (true) // without connect
													{
														if ($_mailProcessor->UpdateAccount($_account, $_sync))
														{
															$_account->ClearSessionArray();
															$_infoDesc = ap_Utils::TakePhrase('WM_ACCOUNT_CREATESUCCESSFUL');
														}
														else
														{
															$_errorDesc = $_mailProcessor->GetError();
															if (strlen($_errorDesc) == 0)
															{
																$_errorDesc = ap_Utils::TakePhrase('WM_ACCOUNT_CREATEUNSUCCESSFUL');
															}
														}
													}
													else
													{
														$_errorDesc = $_mailProcessor->MailStorage->GetError();
														if (strlen($_errorDesc) == 0)
														{
															$_errorDesc = ap_Utils::TakePhrase('WM_ACCOUNT_CREATEUNSUCCESSFUL');
														}
													}

													
												}
												else if ($_account && $_account->IsMailList)
												{
													WmMainFillClass::AccountFromPost($_account, $_sync);
													
													if (!$this->_db->SaveMailingList($_account))
													{
														$_errorDesc = $this->_db->GetError();
														if (strlen($_errorDesc) == 0)
														{
															$_errorDesc = ap_Utils::TakePhrase('WM_ACCOUNT_CREATEUNSUCCESSFUL');
														}
													}
												}

												if (strlen($_errorDesc) != 0)
												{
													$_ref = '?mode=edit&uid='.urlencode($_POST['uid']);
												}
											}
											break;
									}
								}
								break;
								
						} /* switch ($_POST['mode_name']) */
					} /* else if ($_ap->Tab() == 'users') */
							
					$this->Disconnect();
				}
				else
				{
					$_errorDesc =
						ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL').AP_HTML_BR.
						str_replace(array("\r", "\t", "\n"), ' ', $this->_db->GetError());
				}
			}
			else
			{
				$_saveSettings = false;
			}

			if ($_saveSettings || $_saveSettingsNoSee)
			{
				if ($this->_settings->SaveToXml())
				{
					if (!$_saveSettingsNoSee)
					{
						$_infoDesc = strlen($_infoDesc) > 0 ? $_infoDesc : ap_Utils::TakePhrase('WM_INFO_SAVESUCCESSFUL');
					}
				}
				else
				{
					$_errorDesc = strlen($_errorDesc) > 0 ? $_errorDesc : ap_Utils::TakePhrase('WM_INFO_SAVEUNSUCCESSFUL');
				}
			}
			if (strlen($_errorDesc) > 0)
			{
				$this->_setError($_errorDesc);
			}
			else if(strlen($_infoDesc) > 0)
			{
				if ($isErrorInfo)
				{
					$this->_setInfoAsError($_infoDesc);
				}
				else
				{
					$this->_setInfo($_infoDesc);
				}
			}
					
			return $_ref;
		}
		
		function WritePop()
		{
			if (!$this->_isLoad)
			{
				header('Content-Type: text/plain; charset=utf-8');
				exit(ap_Utils::TakePhrase('WM_INFO_CANTLOAD_SETTINGS'));
			}

			$_ap =& $this->GetAp();
			if ($_ap->AuthType() === AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD ||
					$_ap->AuthType() === AP_SESS_AUTH_TYPE_NONE)
			{
				exit(ap_Utils::TakePhrase('AP_LANG_ADMIN_ONLY_READ'));
			}
			
			$_type = isset($_GET['type']) ? $_GET['type'] : 'null';
			switch ($_type)
			{
				default:
					break;
				case 'event':
				case 'event_all':
				case 'log':
				case 'log_all':
					ap_Utils::SetLimits();

					$_text = '';
					$_minisize = 50000;
					$_fileName = ($_type == 'event' || $_type == 'event_all')
						? $this->_settings->_path.'/logs/events_'.date('Y-m-d').'.txt'
						: $this->_settings->_path.'/logs/log_'.date('Y-m-d').'.txt';

					if (@file_exists($_fileName))
					{
						$_size = @filesize($_fileName);
						if ($_size && $_size > 0)
						{
							$_fh = @fopen($_fileName, 'rb');
							if ($_fh)
							{
								if (($_type == 'event' || $_type == 'log') && $_size > $_minisize)
								{
									@fseek($_fh, $_size - $_minisize);
									$_text = @fread($_fh, $_minisize);
								}
								else
								{
									$_text = @fread($_fh, $_size);
								}
							}
							else
							{
								$_text = 'log file can\'t be read';
							}
						}
					}

					$_text = strlen($_text) > 0 ? $_text : 'log file is empty';
					$_text = str_replace('<?xml', '<?_xml', $_text);

					header('Content-Type: text/plain; charset=utf-8');
					header('Content-Length: '.strlen($_text));
					echo $_text;
					break;
				case 'info':
					$_ap =& $this->GetAp();
					if ($_ap->IsEnable())
					{
						echo '<center><b>ver. '.AP_VERSION.'</b><br />'.WM_INI_DIR.
						'<br />'.WM_WEB_DIR.'<br />'.__FILE__.'<br />'.'<br /><br /></center>';
						phpinfo();
					}
					break;
					
				case 'db':
					if (isset($_GET['action']))
					{
						$_pp = $this->PluginPath();
						include $_pp.'/common/db/dbstorage.php';
						
						switch ($_GET['action'])
						{
							case 'create':
								include $_pp.'/common/db/create.php';
								break;
							case 'update':
								include $_pp.'/common/db/update.php';
								break;
							case 'backup':
								include $_pp.'/common/db/backup.php';
								break;
						}
					}
					
					break;
			}
		}
		
		function InitScreen(&$_screen, $_action) 
		{
			if (!$this->_isLoad)
			{
				$this->_setError(ap_Utils::TakePhrase('WM_INFO_CANTLOAD_SETTINGS'));	
			}
			
			switch ($_action)
			{
				case 'initRootPath':
					$_screen->SetRootPath($this->PluginPath());
					return true;
				case 'initMenu':
					$this->_initMenu($_screen);
					return true;
				case 'initStandardData':
					$this->_initData($_screen);
					return true;
				case 'initSearch':
					$this->_initSearch($_screen);
					return true;
				case 'initFilter':
					$this->_initFilter($_screen);
					return true;
				case 'initTable':
					$this->_initTable($_screen);
					return true;
				case 'initMain':
					$this->_initMain($_screen);
					return true;
			}

			return false;
		}
		
		function IncludeCommon()
		{
			$_pp = $this->PluginPath();
			include $_pp.'/common/constans.php';
			include $_pp.'/common/settings.php';
			
			include $_pp.'/common/class_datetime.php';
			include $_pp.'/common/class_account.php';
			
			include $_pp.'/common/class_collectionbase.php';
			include $_pp.'/common/class_folders.php';
			
			include $_pp.'/common/class_mailprocessor.php';
			include $_pp.'/common/class_filldata.php';
			
			if (!$this->_loadSettings())
			{
				$this->_isLoad = false;
			}
		}
		
		/**
		 * @return	bool
		 */
		function Connect()
		{
			if ($this->_isConnect)
			{
				CAdminPanel::Log('DB : Repeating a connection (return true)');
				return true;
			}
			
			if (!defined('INCLUDEDBCLASS'))
			{
				include $this->PluginPath().'/common/db/dbstorage.php';
				define('INCLUDEDBCLASS', 1);
			}
			
			$this->_db =& DbStorageCreator::CreateDatabaseStorage($this->_settings);
			CAdminPanel::Log('DB : Start Connect');
			if ($this->_db->Connect())
			{
				$this->_isConnect = true;
			}
			else
			{
				CAdminPanel::Log('DB : Connect Error ('.$this->_db->_connector->ErrorDesc.')');	
			}
			
			return $this->_isConnect;
		}
		
		function Disconnect()
		{
			if ($this->_isConnect && $this->_db)
			{
				$this->_db->Disconnect();
				$this->_isConnect = false;
			}
		}
		
		/**
		 * @return bool
		 */
		function IsXMailExist()
		{
			return in_array(AP_TYPE_XMAIL, $this->_pluginIndexs);
		}

		/**
		 * @return bool
		 */
		function IsInternalServerExist()
		{
			return in_array(AP_TYPE_XMAIL, $this->_pluginIndexs) || in_array(AP_TYPE_BUNDLE, $this->_pluginIndexs);
		}

		/**
		 * @return bool
		 */
		function IsBundleExist()
		{
			return in_array(AP_TYPE_BUNDLE, $this->_pluginIndexs);
		}
		
		function _initSearch(&$_screen)
		{
			if (strlen($this->_searchDesc) > 0)
			{
				$_screen->SetSearchDesc($this->_searchDesc);
			}
		}
		
		function _initMenu(&$_screen)
		{
			$_ap =& $_screen->GetAp();
			switch ($_ap->Tab())
			{
				case 'wm':
					$isDomainListExist = false;
					if ($_ap->PType())
					{
						if ($this->Connect())
						{
							$_screen->InitFilter();
							$dlist = $this->_db->FilterDomainList();
							if ($dlist && count($dlist) > 0)
							{
								$filterItem = new ap_Screen_Filter_Item();
								$filterItem->href = '';
								$filterItem->name = 'Default Settings';
								$_screen->_filter->AddItem($filterItem);
	
								foreach ($dlist as $domainItem)
								{
									if ($_ap->AuthType() === AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD &&
											in_array(strtolower($domainItem[0]), $this->HideDomains))
									{
										continue;
									}
									
									$filterItem = new ap_Screen_Filter_Item();
									$filterItem->href = $domainItem[0];
									$filterItem->name = $domainItem[0];
	
									$_screen->_filter->AddItem($filterItem);
									unset($filterItem);
								}
								
								$isDomainListExist = true;
							}
						}
						
						$_screen->AddMenuItem(WM_MODE_WEBMAIL, WM_TABNAME_WEBMAIL, 'webmail.php');
						$_screen->AddMenuItem(WM_MODE_INTERFACE, WM_TABNAME_INTERFACE, 'interface.php');
					}
					else
					{
						$_screen->AddMenuItem(WM_MODE_WEBMAIL, WM_TABNAME_WEBMAIL, 'webmail-lite.php');
						$_screen->AddMenuItem(WM_MODE_INTERFACE, WM_TABNAME_INTERFACE, 'interface-lite.php');
					}
					$_screen->AddMenuItem(WM_MODE_LOGIN, WM_TABNAME_LOGIN, 'login.php');

					if ($isDomainListExist)
					{
						$_screen->AddMenuSeparator('Global', null);
					}

					if (CAdminPanel::UseDb())
					{
						$_screen->AddMenuItem(WM_MODE_DB, WM_TABNAME_DB, 'db.php');
						if ($_ap->PType())
						{
							$_screen->AddMenuItem(WM_MODE_CONTACTS, WM_TABNAME_CONTACTS, 'contacts.php');
							$_screen->AddMenuItem(WM_MODE_CAL, WM_TABNAME_CAL, 'cal.php', array('cal.js'));
						}
					}

					$_screen->AddMenuItem(WM_MODE_DEBUG, WM_TABNAME_DEBUG, 'debug.php');
					if ($_ap->PType() && CAdminPanel::UseDb())
					{
						$_screen->AddMenuItem(WM_MODE_MOBILE_SYNC, WM_TABNAME_MOBILE_SYNC, 'mobile.php');
					}
					
					$_screen->SetDefaultMode(WM_MODE_WEBMAIL);
					break;
				case 'users':
					$_screen->AddTopMenuButton('New User', 'user_new.png', 'DoNew("new", "'.AP_INDEX_FILE.'");');
					$_screen->AddTopMenuButton('Delete', 'delete.gif', 'DoDeleteUsers();', null, true);
					
					$buttons_array = array(
								new ap_Screen_Tables_MenuButton($this->_ap->AdminFolder(), 'Disable', 'user_disable.png', 'DoDisableUsers();'),
								new ap_Screen_Tables_MenuButton($this->_ap->AdminFolder(), 'Enable', 'user_enable.png', 'DoEnableUsers();')
							);
					$_screen->AddTopMenuExtendedButton('Disable', $_screen, $buttons_array);
					break;
				case 'domains':
					if ($_ap->AuthType() === AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD ||
						$_ap->AuthType() === AP_SESS_AUTH_TYPE_SUPER_ADMIN)
					{
						$_name = ($this->IsInternalServerExist()) ? 'New External Domain' : 'New Domain';
						$_screen->AddTopMenuButton($_name, 'new-domain.png', 'DoNew("ext", "'.AP_INDEX_FILE.'");', null, true);
						$_screen->AddTopMenuButton('Delete', 'delete.gif', 'DoDeleteDomains();', null, true);
					}
					break;
				case 'install':
					$_screen->AddMenuAsLink(WM_INST_MODE_CHECK, WM_INST_TABNAME_CHECK, 'install.php');
					if ($this->_isLoad)
					{
						$_screen->AddGreyMenu(WM_INST_MODE_LICENSETEXT, WM_INST_TABNAME_LICENSETEXT);
						if ($_ap->PType())
						{
							$_screen->AddGreyMenu(WM_INST_MODE_LICENSE, WM_INST_TABNAME_LICENSE);
						}
						$_screen->AddGreyMenu(WM_INST_MODE_DB, WM_INST_TABNAME_DB);
						if ($_ap->PType())
						{
							$_screen->AddGreyMenu(WM_INST_MODE_MOBILESYNC, WM_INST_TABNAME_MOBILESYNC);
						}
						$_screen->AddGreyMenu(WM_INST_MODE_COMMON, WM_INST_TABNAME_COMMON);
						$_screen->AddGreyMenu(WM_INST_MODE_SOCKET, WM_INST_TABNAME_SOCKET);
						$_screen->AddGreyMenu(WM_INST_MODE_END, WM_INST_TABNAME_END);
						
						switch ($_ap->Mode())
						{
							case WM_INST_MODE_END:
								$_screen->AddMenuItem(WM_INST_MODE_END, WM_INST_TABNAME_END, 'install-end.php');
								$_screen->SetDefaultMode(WM_INST_MODE_END);
							case WM_INST_MODE_SOCKET:
								$_screen->AddMenuItem(WM_INST_MODE_SOCKET, WM_INST_TABNAME_SOCKET, 'install-socket-check.php');
								$_screen->SetDefaultMode(WM_INST_MODE_SOCKET);
							case WM_INST_MODE_COMMON:
								$_screen->AddMenuItem(WM_INST_MODE_COMMON, WM_INST_TABNAME_COMMON, 'install-common.php');
								$_screen->SetDefaultMode(WM_INST_MODE_COMMON);
							case WM_INST_MODE_MOBILESYNC:
								if ($_ap->PType())
								{
									$_screen->AddMenuItem(WM_INST_MODE_MOBILESYNC, WM_INST_TABNAME_MOBILESYNC, 'install-mobilesync.php');
									$_screen->SetDefaultMode(WM_INST_MODE_MOBILESYNC);
								}
							case WM_INST_MODE_DB:
								$_screen->AddMenuItem(WM_INST_MODE_DB, WM_INST_TABNAME_DB, 'install-db.php');
								$_screen->SetDefaultMode(WM_INST_MODE_DB);
							case WM_INST_MODE_LICENSE:
								if ($_ap->PType())
								{
									$_screen->AddMenuItem(WM_INST_MODE_LICENSE, WM_INST_TABNAME_LICENSE, 'install-licensekey.php');
									$_screen->SetDefaultMode(WM_INST_MODE_LICENSE);
								}
							default:
							case WM_INST_MODE_LICENSETEXT:
								$_screen->AddMenuItem(WM_INST_MODE_LICENSETEXT, WM_INST_TABNAME_LICENSETEXT, ($_ap->PType()) ? 'install-license.php' : 'install-license-lite.php');
								$_screen->SetDefaultMode(WM_INST_MODE_LICENSETEXT);
						}
					}
					else
					{
						$_screen->AddGreyMenu(WM_INST_MODE_LICENSETEXT, WM_INST_TABNAME_LICENSETEXT);
						if ($_ap->PType())
						{
							$_screen->AddGreyMenu(WM_INST_MODE_LICENSE, WM_INST_TABNAME_LICENSE);
						}
						$_screen->AddGreyMenu(WM_INST_MODE_DB, WM_INST_TABNAME_DB);
						$_screen->AddGreyMenu(WM_INST_MODE_MOBILESYNC, WM_INST_TABNAME_MOBILESYNC);
						$_screen->AddGreyMenu(WM_INST_MODE_COMMON, WM_INST_TABNAME_COMMON);
						$_screen->AddGreyMenu(WM_INST_MODE_SOCKET, WM_INST_TABNAME_SOCKET);
						$_screen->AddGreyMenu(WM_INST_MODE_END, WM_INST_TABNAME_END);
					}
					break;
			}
		}
		
		function _initInstallData(&$_screen)
		{
			$_ap =& $_screen->_ap;
			$_ap->AddJsFile($_ap->AdminFolder().'/plugins/'.$this->PluginName().'/js/install.js');
			
			$max = 8;
			if (!$_ap->PType())
			{
				$max -= 2;
			}
			
			if (isset($_SESSION[WM_INST_SESS_MSG_TYPE]))
			{
				$_name = 'InfoMsg';
				$_info = '';
				if (isset($_SESSION[WM_INST_SESS_MSG_TEXT]))
				{
					$_info = $_SESSION[WM_INST_SESS_MSG_TEXT];
				}
				else if (isset($_SESSION[WM_INST_SESS_MSG_TEST_TEXT]))
				{
					$_name = 'TestConnectInfoMsg';
					$_info = $_SESSION[WM_INST_SESS_MSG_TEST_TEXT];
				}
				else if (isset($_SESSION[WM_INST_SESS_MSG_CREATE_DB_TEXT]))
				{
					$_name = 'CreateDatabaseInfoMsg';
					$_info = $_SESSION[WM_INST_SESS_MSG_CREATE_DB_TEXT];
				}
				
				if ($_SESSION[WM_INST_SESS_MSG_TYPE] == WM_INST_MSG_TYPE_BAD)
				{
					$_info = '<font color="red">'.$_info;
				}
				else 
				{
					$_info = '<font color="green">'.$_info;
				}
				
				$_screen->data->SetValue($_name, $_info.'</font>');
				unset($_SESSION[WM_INST_SESS_MSG_TYPE]);
				unset($_SESSION[WM_INST_SESS_MSG_TEXT]);
				unset($_SESSION[WM_INST_SESS_MSG_TEST_TEXT]);
				unset($_SESSION[WM_INST_SESS_MSG_CREATE_DB_TEXT]);
			}
			
			$_screen->data->SetValue('isPro', $_ap->PType());
			
			switch ($_ap->Mode())
			{
				case WM_INST_MODE_END:
					$_screen->SetScreenStep($_ap->PType() ? 8 : 6);
					$_screen->data->SetValue('StepCount', ($_ap->PType() ? 8 : 6).' of '.$max);
					$_ap->SetTitle(WM_INST_TABNAME_END.' — '.WM_INST_TITLE);
					if ($_ap->PType())
					{
						$_screen->data->SetValue('ProductName', 'AfterLogic WebMail Pro PHP');
					}
					else
					{
						$_screen->data->SetValue('ProductName', 'AfterLogic WebMail Lite PHP');	
					}
					
					$_screen->data->SetValue('onClickBack', 'document.location=\''.AP_INDEX_FILE.'?mode='.WM_INST_MODE_SOCKET.'\'');
					break;
				case WM_INST_MODE_SOCKET:
					$_screen->SetScreenStep($_ap->PType() ? 7 : 5);
					$_screen->data->SetValue('StepCount', ($_ap->PType() ? 7 : 5).' of '.$max);
					$_ap->SetTitle(WM_INST_TABNAME_SOCKET.' — '.WM_INST_TITLE);
					
					if (!WM_INST_CONNECTION_TEST_SSL)
					{
						$_screen->data->SetValue('ssl_start_comment', ' <!-- ');
						$_screen->data->SetValue('ssl_end_comment', ' //--> ');
					}
					
					if (isset($_SESSION[WM_INST_SESS_CH_HOST]))
					{
						$_screen->data->SetValue('txtHost', $_SESSION[WM_INST_SESS_CH_HOST]);
					}
					else 
					{
						$_screen->data->SetValue('txtHost', 'localhost');
					}

					if (isset($_SESSION[WM_INST_SESS_CH_PROTOCOLS]))
					{
						$_p = (int) $_SESSION[WM_INST_SESS_CH_PROTOCOLS];
						$_screen->data->SetValue('chPOP3', (($_p & 1) == 1));
						$_screen->data->SetValue('chIMAP4', (($_p & 2) == 2));
						$_screen->data->SetValue('chSMTP', (($_p & 4) == 4));
					}
					else 
					{
						$_screen->data->SetValue('chSMTP', true);
					}
					
					if (isset($_SESSION[WM_INST_SESS_CH_MSG]))
					{
						$_screen->data->SetValue('InfoCheckMsg', $_SESSION[WM_INST_SESS_CH_MSG]);
						unset($_SESSION[WM_INST_SESS_CH_MSG]);
					}
					
					$_screen->data->SetValue('onClickBack', 'document.location=\''.AP_INDEX_FILE.'?mode='.WM_INST_MODE_COMMON.'\'');
					if ($_ap->PType())
					{
						$_screen->data->SetValue('onClickNext', 'window.open(\'http://www.afterlogic.com/congratulations/webmail-pro-php\');');
					}
					else
					{
						$_screen->data->SetValue('onClickNext', 'window.open(\'http://www.afterlogic.com/congratulations/webmail-lite-php\');');
					}
					break;
				case WM_INST_MODE_COMMON:
					$_screen->SetScreenStep($_ap->PType() ? 6 : 4);
					$_screen->data->SetValue('StepCount', ($_ap->PType() ? 6 : 4).' of '.$max);
					$_ap->SetTitle(WM_INST_TABNAME_COMMON.' — '.WM_INST_TITLE);
					
					$loginArray = $_ap->GetCfg('login');
					if ($loginArray && isset($loginArray['user']))
					{
						$_screen->data->SetValue('UserName', $loginArray['user']);
						
						if (isset($_SESSION[WM_INST_IS_ADM_PSW]) && isset($loginArray['password']))
						{
							if (strlen($loginArray['password']) > 0)
							{
								$_screen->data->SetValue('txtPassword1', AP_DUMMYPASSWORD);
								$_screen->data->SetValue('txtPassword2', AP_DUMMYPASSWORD);
							}
						}
					}

					if ($_ap->PType())
					{
						$_screen->data->SetValue('onClickBack', 'document.location=\''.AP_INDEX_FILE.'?mode='.WM_INST_MODE_MOBILESYNC.'\'');
					}
					else
					{
						$_screen->data->SetValue('onClickBack', 'document.location=\''.AP_INDEX_FILE.'?mode='.WM_INST_MODE_DB.'\'');
					}
					break;
				case WM_INST_MODE_MOBILESYNC:
					$_screen->SetScreenStep(5);
					$_screen->data->SetValue('StepCount', '5 of '.$max);
					$_ap->SetTitle(WM_INST_TABNAME_MOBILESYNC.' — '.WM_INST_TITLE);
					$_screen->data->SetValue('onClickBack', 'document.location=\''.AP_INDEX_FILE.'?mode='.WM_INST_MODE_DB.'\'');

					$bIsWin = ap_AddUtils::IsWin();
					if ($bIsWin)
					{
						$_screen->data->SetValue('classShowLinux', 'wm_hide');
					}
					else
					{
						$_screen->data->SetValue('classShowWindows', 'wm_hide');
					}
					$_screen->data->SetValue('textJdbcDb', $this->_settings->DbName);
					$_screen->data->SetValue('textJdbcHost', $this->_settings->DbHost);
					$_screen->data->SetValue('textJdbcUser', $this->_settings->DbLogin);

					$_screen->data->SetValue('chEnableMobileSync', (bool) $this->_settings->EnableMobileSync);
					$_screen->data->SetValue('txtMobileSyncUrl', $this->_settings->MobileSyncUrl);
					$_screen->data->SetValue('txtMobileSyncContactDatabase', $this->_settings->MobileSyncContactDataBase);
					$_screen->data->SetValue('txtMobileSyncCalendarDatabase', $this->_settings->MobileSyncCalendarDataBase);
					break;
				case WM_INST_MODE_DB:
					$_screen->SetScreenStep($_ap->PType() ? 4 : 3);
					$_screen->data->SetValue('StepCount', ($_ap->PType() ? 4 : 3).' of '.$max);
					$_ap->SetTitle(WM_INST_TABNAME_DB.' — '.WM_INST_TITLE);
					
					$_screen->data->SetValue('intDbTypeValue0', AP_DB_MSSQLSERVER);
					$_screen->data->SetValue('intDbTypeValue1', AP_DB_MYSQL);
					
					$_screen->data->SetValue('isMySQL_JS', 
						@extension_loaded('mysql') ? 'true' : 'false');
					
					$_screen->data->SetValue('isMSSQL_JS', 
						@extension_loaded('mssql') ? 'true' : 'false');
						
					$_screen->data->SetValue('isODBC_JS', 
						@extension_loaded('odbc') ? 'true' : 'false');
						
					if (isset($_SESSION[WM_INST_SESS_SETTINGS]) && is_string($_SESSION[WM_INST_SESS_SETTINGS]))
					{
						$this->_settings = unserialize($_SESSION[WM_INST_SESS_SETTINGS]);
					}

					switch ($this->_settings->DbType)
					{
						case AP_DB_MSSQLSERVER:
							$_screen->data->SetValue('intDbType0', true);
							break;
						default:
						case AP_DB_MYSQL:
							$_screen->data->SetValue('intDbType1', true);
							break;
					}
					
					$_screen->data->SetValue('txtSqlLogin', $this->_settings->DbLogin);
					if (strlen($this->_settings->DbPassword) > 0)
					{
						$_screen->data->SetValue('txtSqlPassword', AP_DUMMYPASSWORD);
					}
					$_screen->data->SetValue('txtSqlName', $this->_settings->DbName);
					
					$_screen->data->SetValue('txtSqlSrc', $this->_settings->DbHost);
					
					if ($this->_settings->UseCustomConnectionString)
					{
						$_screen->data->SetValue('useCS', $this->_settings->UseCustomConnectionString);	
					}
					else 
					{
						$_screen->data->SetValue('useDSN', $this->_settings->UseDsn);
					}
					
					$_screen->data->SetValue('txtSqlDsn', $this->_settings->DbDsn);
					$_screen->data->SetValue('odbcConnectionString', $this->_settings->DbCustomConnectionString);
					$_screen->data->SetValue('txtPrefix', $this->_settings->DbPrefix);

					if ($_ap->PType())
					{
						$_screen->data->SetValue('onClickBack', 'document.location=\''.AP_INDEX_FILE.'?mode='.WM_INST_MODE_LICENSE.'\'');
					}
					else
					{
						$_screen->data->SetValue('onClickBack', 'document.location=\''.AP_INDEX_FILE.'?mode='.WM_INST_MODE_LICENSETEXT.'\'');
					}
					break;
				case WM_INST_MODE_LICENSE:
					$_screen->SetScreenStep(3);
					$_screen->data->SetValue('StepCount', '3 of '.$max);
					$_ap->SetTitle(WM_INST_TABNAME_LICENSE.' — '.WM_INST_TITLE);
					$_key = $this->_settings->LicenseKey;
					$_key = strlen($_key) > 0 ? $_key : (isset($_SESSION['licensekeysession']) ? $_SESSION['licensekeysession'] : '');
					$_screen->data->SetValue('txtLicenseKey', $_key);
					
					$_screen->data->SetValue('txtGetTrialId', 22);
					
					if (isset($_SESSION[WM_INST_IS_F_K]) || strlen($this->_settings->LicenseKey) > 0)
					{
						if ($_ap->PType() && !$_ap->InstallOL($this->_settings->LicenseKey))
						{
							if (strlen($this->_settings->LicenseKey) == 0)
							{	
								$_screen->data->SetValue('txtLicenseKey', '');
								$_screen->data->SetValue('txtLicenseKeyText', ap_Utils::TakePhrase('WM_INST_ENTER_VALID_KEY_HERE'));
							}
							else
							{
								$_screen->data->SetValue('txtLicenseKeyText', ap_Utils::TakePhrase('WM_INST_ENTER_VALID_KEY'));
							}
						}
						else 
						{
							$_screen->data->SetValue('txtHideGetTrialClass', 'class="wm_hide"');
						}
					}
					else 
					{
						$_SESSION[WM_INST_IS_F_K] = true;
					}
					
					if (@file_exists('CS'))
					{
						if (strlen($this->_settings->LicenseKey) == 0)
						{
							if ($_ap->PType())
							{
								$lll = $_ap->InitDataCr();
								if ($_ap->InstallOL($lll))
								{
									$_screen->data->SetValue('txtLicenseKey', $lll);
									$_screen->data->SetValue('txtLicenseKeyText', '');
								}
							}
						}
						
						$_screen->data->SetValue('txtHideGetTrialClass', 'class="wm_hide"');
					}
					
					$_screen->data->SetValue('onClickBack', 'document.location=\''.AP_INDEX_FILE.'?mode='.WM_INST_MODE_LICENSETEXT.'\'');
					break;
				default:
				case WM_INST_MODE_LICENSETEXT:
					$_screen->SetScreenStep(2);
					$_screen->data->SetValue('StepCount', '2 of '.$max);
					$_ap->SetTitle(WM_INST_TABNAME_LICENSETEXT.' — '.WM_INST_TITLE);
					
					$_screen->data->SetValue('onClickBack', 'document.location=\'install.php\'');
					break;
			}			
		}
		
		function _initFilter(&$_screen)
		{
			$_ap =& $_screen->GetAp();
			if ($_ap->Tab() == 'users')
			{
				if ($this->Connect())
				{
					$_screen->InitFilter();

					$_accessDomains = $_ap->GetSubAdminDomainsId();
					
					$_addAll = true;
					if ((is_array($_accessDomains) && !in_array(0, $_accessDomains)) ||
						$_ap->AuthType() === AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD)
					{
						$_addAll = false;
					}

					if ($_addAll)
					{
						$_filterItem = new ap_Screen_Filter_Item();
						$_filterItem->href = AP_TYPE_WEBMAIL.AP_TYPE_DELIMITER.'all';
						$_filterItem->name = WM_FILTER_ALL;
						$_filterItem->type = AP_TYPE_WEBMAIL;
						$_filterItem->class = 'domWebMail';
						$_screen->_filter->AddItem($_filterItem, true);
					}
					
					$_dlist = $this->_db->DomainList();
					if (is_array($_dlist))
					{
						foreach ($_dlist as $_id => $_domain)
						{
							if ((is_array($_accessDomains) && !in_array($_id, $_accessDomains)) ||
								($_ap->AuthType() === AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD 
									&&  in_array(strtolower($_domain[0]), $this->HideDomains)))
							{
								continue;
							}

							if (count($_domain) > 3)
							{
								$_filterItem = new ap_Screen_Filter_Item();
								$_filterItem->href = AP_TYPE_WEBMAIL.AP_TYPE_DELIMITER.$_id;
								$_filterItem->name = $_domain[0];
								$_filterItem->type = AP_TYPE_WEBMAIL;
								$_filterItem->class = ($_domain[2]) ? 'domXMail' : 'domWebMail';

								$_screen->_filter->AddItem($_filterItem);
								unset($_filterItem);
							}
						}
					}
				}
				else
				{
					$_screen->InitFilter();
						
					$_filterItem = new ap_Screen_Filter_Item();
					$_filterItem->href = AP_TYPE_WEBMAIL.AP_TYPE_DELIMITER.'all';
					$_filterItem->name = WM_FILTER_ALL;
					$_filterItem->type = AP_TYPE_WEBMAIL;
					$_filterItem->class = 'domWebMail';
					$_screen->_filter->AddItem($_filterItem, true);
				}
			}
		}
		
		function _initTable(&$_screen)
		{
			$_ap =& $_screen->GetAp();
			$_apTab = $_ap->Tab();
			if ($this->Connect())
			{
				if ($_apTab == 'domains')
				{
					$_screen->SetNullPhrase(ap_Utils::TakePhrase('AP_LANG_NODOMAINS'));
					$_screen->InitTable();
					
					$_screen->ClearHeaders();
					$_screen->AddHeader('Type', 42);
					$_screen->AddHeader('Name', 138, true);
					
					$this->_initDomainsData($_screen);
				}
				else if ($_apTab == 'users')
				{
					$_screen->SetNullPhrase(ap_Utils::TakePhrase('AP_LANG_NOUSERSINDOMAIN'));
					$_screen->InitTable();
					
					$_screen->ClearHeaders();
					$_screen->AddHeader('Type', 42);
					$_screen->AddHeader('Email', 138, true);
					
					$this->_initUsersData($_screen);
					
					if ($this->_settings->EnableWmServer && !@file_exists($this->_settings->WmServerRootPath.'/server.tab'))
					{
						$this->_setError(ap_Utils::TakePhrase('WM_INFO_ROOTPATHINCORRECT'));		
					}
				}
			}
			else
			{
				if ($_apTab == 'domains')
				{
					$_screen->SetNullPhrase(ap_Utils::TakePhrase('AP_LANG_NODOMAINS'));
					$_screen->InitTable();
					
					$_screen->ClearHeaders();
					$_screen->AddHeader('Type', 42);
					$_screen->AddHeader('Name', 138, true);
				}
				else if ($_apTab == 'users')
				{
					$_screen->SetNullPhrase(ap_Utils::TakePhrase('AP_LANG_NOUSERSINDOMAIN'));
					$_screen->InitTable();
					
					$_screen->ClearHeaders();
					$_screen->AddHeader('Type', 42);
					$_screen->AddHeader('Name', 138, true);
				}
				
				$this->_setError(
					ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL').AP_HTML_BR.
					str_replace(array("\r", "\t", "\n"), ' ', $this->_db->GetError()));
			}
		}
		
		function _initDomainsData(&$_screen)
		{
			$_ap =& $_screen->GetAp();
			$_accessDomains = $_ap->GetSubAdminDomainsId();

			$_list = $this->_db->DomainList($this->_searchDesc);
			if (is_array($_list))
			{
				foreach ($_list as $_id => $_domainArray)
				{
					if ((is_array($_accessDomains) && !in_array($_id, $_accessDomains)) ||
						($_ap->AuthType() === AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD  
							&&	in_array(strtolower($_domainArray[0]), $this->HideDomains)))
					{
						continue;
					}
					
					if (count($_domainArray) > 3)
					{
						$_tableItem = new ap_Screen_Table_Item();
						$_tableItem->href = AP_TYPE_WEBMAIL.AP_TYPE_DELIMITER.$_id;
						$_tableItem->name = $_domainArray[0];
						$_tableItem->type = AP_TYPE_WEBMAIL;
						$_tableItem->values = array(
							'Type' => ($_domainArray[2])
								? '<img src="'.$_ap->AdminFolder().'/images/xmail-domain-icon-big.png">'
								: '<img src="'.$_ap->AdminFolder().'/images/wm-domain-icon-big.png">',
							'Name' => $_domainArray[0]
						);

						$_screen->_table->AddItem($_tableItem);
						unset($_tableItem);
					}
				}
			}
		}
		
		function _initUsersData(&$_screen)
		{
			$_ap =& $_screen->GetAp();
			$_AllUsersCount = $this->_getAllUserCount();
				if ($_ap->AuthType() !== AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD &&
					$_ap->AuthType() !== AP_SESS_AUTH_TYPE_NONE)
			{
				$_screen->SetLowToolBarText(ap_Utils::TakePhrase('AP_LANG_TOTAL_USERS').': '.$_AllUsersCount);
			}
			
			include_once $this->PluginPath().'/common/class_domains.php';
							
			$_domainId = -1;
			
			$_uids = explode(AP_UIDS_DELIMITER, $_screen->GetSelectedItemKey());
			foreach ($_uids as $_value)
			{
				$_arr = ap_Utils::UidExplode($_value);
				if ($_arr[0] == AP_TYPE_WEBMAIL)
				{
					$_domainId = ($_arr[1] === 'all') ? 0 : (int) $_arr[1];
					break;
				}
			}
			
			$_domain = null;
			if ($_domainId == 0)
			{
				$_domain = new CWebMailDomain();
				$_domain->Init('', WM_MAILPROTOCOL_ALL, '', 0, '', 0, true);
				$_domain->_id = 0;
			}
			else if ($_domainId > 0)
			{
				$_domain = $this->_db->SelectDomainById($_domainId);
			}
			
			if ($_domain)
			{
				if ($_domain->_isInternal)
				{
					$_screen->AddTopMenuButton('New Mailing List', 'new_contact.gif', 'DoNew("mlist", "'.AP_INDEX_FILE.'");');
				}
				
				$_screen->ClearHeaders();
				$_screen->AddHeader('Type', 42);
				$_screen->AddHeader('Email', 150, true);
				$_screen->AddHeader('Last Login', 95);
				$_screen->AddHeader('Mailbox Size', 100);

				$_UsersCount = 0;
				if (strlen($this->_searchDesc) > 0)
				{
					$_UsersCount = $this->_db->AccountCount($_domain->_id, $this->_searchDesc);
				}
				else
				{
					$_UsersCount = $this->_db->AccountCount($_domain->_id);
				}
				
				if ($_UsersCount > 0)
				{
					$_screen->_table->UseCurrentList();
					$_screen->_table->SetListCount($_UsersCount);
					$_page = $_screen->_table->GetPage($_UsersCount);

					$_list = $this->_db->AccountList($_domain->_id, $_page, $_screen->_table->GetLinePerPage(),
						$this->_tableFieldToDbColumn($_screen->_table->_orderColumn),
						(bool) $_screen->_table->_orderType,
						$this->_searchDesc);

					if (is_array($_list))
					{
						foreach ($_list as $_id => $_acctArray)
						{
							if (count($_acctArray) == 9)
							{
								$_mailboxSize = ap_Utils::GetFriendlySize($_acctArray[4]);
								
								/* $_screen->SetLowToolBarText('Account count: '.$_UsersCount); */
								
								$_datetime = new CDateTime();
								$_datetime->SetFromANSI($_acctArray[2]);
								$_lastLogin = date('Y-m-d H:i', $_datetime->TimeStamp);
							
								$_tableItem = new ap_Screen_Table_Item();
								$_emailArray = ap_Utils::ParseEmail($_acctArray[1]);
								
								$_acctDomain = (count($_emailArray) == 2) ? $_emailArray[0] : $_acctArray[1];
								
								$_xmailHref = ($_domain->_id == 0)
									? AP_TYPE_WEBMAIL.AP_TYPE_DELIMITER.$_id
									: AP_TYPE_XMAIL.AP_TYPE_DELIMITER.$_acctDomain.AP_UIDS_DELIMITER.AP_TYPE_WEBMAIL.AP_TYPE_DELIMITER.$_id;
								
								$_tableItem->href = ($_acctArray[7] == WM_MAILPROTOCOL_WMSERVER)
									? $_xmailHref
									: AP_TYPE_WEBMAIL.AP_TYPE_DELIMITER.$_id;

								$_tableItem->href = AP_TYPE_WEBMAIL.AP_TYPE_DELIMITER.$_id;

								$_tableItem->name = $_acctArray[1];
								$_tableItem->type = AP_TYPE_WEBMAIL;
								
								$_img = ($_acctArray[6]) ? 'M' : 'U';
								$_img = ($_acctArray[8]) ? $_img."_disable" : $_img;
								$_tableItem->values = array(
									'Type' => '<img src="'.$_ap->AdminFolder().'/images/icons/'.$_img.'.gif">',
									'Email' => $_acctArray[1], 
									'Last Login' => $_lastLogin,
									'Mailbox Size' => $_mailboxSize
								);
									
								$_screen->_table->AddItem($_tableItem);
								unset($_tableItem);
							}
						}
					}
					else if ($_list === false)
					{
						$this->_setError(ap_Utils::TakePhrase('WM_CANTGET_ACCOUNTS_LIST'));
					}
				}
			}
		}
		
		/**
		 * @param	string	$field
		 * @return	string|null
		 */
		function _tableFieldToDbColumn($_field)
		{
			switch ($_field)
			{
				case 'Email': return 'email';
				case 'Last Login': return 'nlast_login';
				case 'Logins': return 'logins_count';
				case 'Mailbox Size': return 'mailbox_size';
			}
			return null;
		}
		
		function _initMain(&$_screen)
		{
			$_ap =& $_screen->GetAp();
			$_accessDomains = $_ap->GetSubAdminDomainsId();
			if ($_ap->Tab() == 'domains')
			{
				switch ($_ap->Mode())
				{
					case 'new':
		
						$_switcher = null;
						$_type = (isset($_GET['type'])) ? $_GET['type'] : '';

						if ($_type == 'ext')
						{
							$_screen->_main->AddSwitcher('ext', 'This is external domain', $this->PluginPath().'/templates/new-domain.php');
							$_switcher =& $_screen->_main->GetSwitcher('ext');
						}
						
						if ($_switcher !== null)
						{
							include_once $this->PluginPath().'/common/class_domains.php';
							
							$_domain = new CWebMailDomain();
							$_domain->SetInternal(isset($_GET['int']));
							if ($_domain->IsInternal())
							{
								$_domain->Init('', WM_MAILPROTOCOL_IMAP4, 'localhost', 143, 'localhost', 25, true, true);
							}
							$_domain->UpdateFromSessionArray();
							
							if (!$_ap->PType())
							{
								$_switcher->data->SetValue('filterHrefClass', 'wm_hide');
							}

							if ($_ap->AuthType() == AP_SESS_AUTH_TYPE_SUBADMIN)
							{
								$_switcher->data->SetValue('webmailDoaminSettingsHrefClass', 'wm_hide');
							}
							
							WmMainFillClass::ScreenDataFromDomain($_switcher->data, $_domain, $_ap->PType());
						}
						break;
						
					case 'edit':
						if (isset($_GET['uid']) && strlen($_GET['uid']) > 3 && strpos($_GET['uid'], AP_TYPE_WEBMAIL) !== false)
						{
							if ($this->Connect())
							{
								include_once $this->PluginPath().'/common/class_domains.php';
								
								$_domainId = -1;
								
								$_uids = explode(AP_UIDS_DELIMITER, $_GET['uid']);
								foreach ($_uids as $_value)
								{
									$_arr = ap_Utils::UidExplode($_value);
									if ($_arr[0] == AP_TYPE_WEBMAIL)
									{
										$_domainId = (int) $_arr[1];
										break;
									}
								}

								$_domain = null;
								if (!is_array($_accessDomains) || in_array($_domainId, $_accessDomains))
								{
									$_domain = $this->_db->SelectDomainById($_domainId);
								}
								
								if ($_domain)
								{
									$_screen->_main->AddSwitcher('edit', 'This is edit domain', $this->PluginPath().'/templates/edit-domain.php');
									$_switcher =& $_screen->_main->GetSwitcher('edit');
									if ($_switcher !== null)
									{
										$_switcher->data->SetValue('uid', urlencode($_GET['uid']));
										$_domain->UpdateFromSessionArray();

										if (!$_ap->PType())
										{
											$_switcher->data->SetValue('filterHrefClass', 'wm_hide');
										}
										if ($_ap->AuthType() == AP_SESS_AUTH_TYPE_SUBADMIN)
										{
											$_switcher->data->SetValue('webmailDoaminSettingsHrefClass', 'wm_hide');
										}
										WmMainFillClass::ScreenDataFromDomain($_switcher->data, $_domain, $_ap->PType());
									}
								}
							}
							else
							{
								$this->_setError(
									ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL').AP_HTML_BR.
									str_replace(array("\r", "\t", "\n"), ' ', $this->_db->GetError()));
							}
						}
						break;
				}
			}
			else if ($_ap->Tab() == 'users')
			{
				$_filterDomain = -1;
				$_resp = $this->_thisIsMyUid($_screen->GetSelectedItemKey());
				if ($_resp !== false)
				{
					$_filterDomain = (int) substr($_resp, 3);
				}

				$_domain = null;
				if (!$this->Connect())
				{
					$this->_setError(
						ap_Utils::TakePhrase('WM_INFO_CONNECTUNSUCCESSFUL').AP_HTML_BR.
						str_replace(array("\r", "\t", "\n"), ' ', $this->_db->GetError()));

					return false;
				}
				else if (is_array($_accessDomains) && !in_array($_filterDomain, $_accessDomains))
				{
					
				}
				else if ($_filterDomain == 0)
				{
					include_once $this->PluginPath().'/common/class_domains.php';

					$_domain = new CWebMailDomain();
					$_domain->Init(ap_Utils::TakePhrase('WM_FILTER_ALL'),
						WM_MAILPROTOCOL_ALL,
						$this->_settings->IncomingMailServer,
						$this->_settings->IncomingMailPort,
						$this->_settings->OutgoingMailServer,
						$this->_settings->OutgoingMailPort,
						true);

					$_domain->_id = 0;
				}
				else if ($_filterDomain > 0)
				{
					include_once $this->PluginPath().'/common/class_domains.php';
					$_domain = $this->_db->SelectDomainById($_filterDomain);
				}

				switch ($_ap->Mode())
				{
					case 'new':

						$_switcher = null;
						$_type = (isset($_GET['type'])) ? $_GET['type'] : '';
						
						if ($_domain && $_type == 'new')
						{
							$mailsuiteSuffix = ($_domain->_isInternal) ? '-mailsuite': '';
							switch ($_domain->_mailProtocol)
							{
								case WM_MAILPROTOCOL_POP3:
									$_screen->_main->AddSwitcher('createUser', 'Add remote mail account', $this->PluginPath().'/templates/edit-user-pop3'.$mailsuiteSuffix.'.php');
									break;
								case WM_MAILPROTOCOL_IMAP4:
									$_screen->_main->AddSwitcher('createUser', 'Add remote mail account', $this->PluginPath().'/templates/edit-user-imap4'.$mailsuiteSuffix.'.php');
									break;
								case WM_MAILPROTOCOL_ALL:
									$_screen->_main->AddSwitcher('createUser', 'Add remote mail account', $this->PluginPath().'/templates/new-user-pop3-imap4.php');
									break;
							}

							$_settings = $this->_settings;

							if ($_ap->PType() && $_domain->_id > 0)
							{
								$_domain->UpdateSettings($_settings);
							}
							
							$_account = new Account($this->_settings, $_domain);
							$_account->MailProtocol = ($_account->MailProtocol == WM_MAILPROTOCOL_ALL) ? $this->_settings->IncomingMailProtocol : $_account->MailProtocol;
								
							$_switcher =& $_screen->_main->GetSwitcher('createUser');
							if ($_switcher !== null)
							{
								$_switcher->data->SetValue('TopText', 'Create User');
								$_switcher->data->SetValue('DomainName', $_domain->_name);
								$_switcher->data->SetValue('intDomainId', $_domain->_id);

								$_switcher->data->SetValue('classHideEmailTr', $_domain->_isInternal ? 'wm_hide' : '');

								$_switcher->data->SetValue('classLoginField', 'wm_input');
								$_switcher->data->SetValue('classLoginSpan', 'wm_hide');

								/*
						$_switcher->data->SetValue('classEmailSpan', 'wm_hide');
						$_switcher->data->SetValue('classEmailField', 'wm_input');
								*/

								$_folderSyncType = WM_FOLDERSYNC_AllHeadersOnly;
								if ($_account->MailProtocol == WM_MAILPROTOCOL_POP3)
								{
									$_folderSyncType = WM_FOLDERSYNC_AllEntireMessages;	
								}
								
								$_folderSyncType = $_account->GetDefaultFolderSync();
								
								$_switcher->data->SetValue('folderSyncType', $_folderSyncType);

								$_account->UpdateFromSessionArray();

								WmMainFillClass::ScreenDataFromAccount($_switcher->data, $_account, true);

								$_switcher->data->SetValue('edit-user-advanced-control', $this->PluginPath().'/templates/edit-user-advanced-control.php');

								$_switcher->data->SetValue('aliases-control', $this->PluginPath().'/templates/aliases-control.php');
								$_switcher->data->SetValue('forwards-control', $this->PluginPath().'/templates/forwards-control.php');
								$_switcher->data->SetValue('DomainNameAlias', $_domain->Name());

								$start = isset($_GET['start']) ? (int) $_GET['start'] : 1;
								$_switcher->data->SetValue('StartWmJs', '
SwitchItem(useredittabs, "content_custom_tab_'.$start.'"); SetAllOf();
var startTab = document.getElementById("custom_tab_'.$start.'");
if (startTab) {
	startTab.className = "wm_settings_switcher_select_item";
};');
							}
						}
						else if ($_domain && $_type == 'mlist')
						{
							$_screen->_main->AddSwitcher('mlist', 'This is new mlist user', $this->PluginPath().'/templates/new-maillist.php');
							$_switcher =& $_screen->_main->GetSwitcher('mlist');
							if ($_switcher !== null)
							{
								include $this->PluginPath().'/common/class_user_mini.php';

								$UserMini = new CExminUserMini();
								$UserMini->UpdateFromSessionArray();

								$_switcher->data->SetValue('UserLogin', $UserMini->_login);
								$_switcher->data->SetValue('DomainName', $_domain->Name());
							}
						}
						break;
						
					case 'edit':
						if ($_domain && isset($_GET['uid']))
						{
							$_account = null;
							$_acctId = -1;
							
							$_resp = $this->_thisIsMyUid($_GET['uid']);
							if ($_resp !== false)
							{
								$_arr = ap_Utils::UidExplode($_resp);
								if (strlen($_arr[0]) > 0)
								{
									$_acctId = (int) $_arr[1];
								}
							}
							
							if ($_acctId > 0)
							{
								$_account =& $this->_db->SelectAccountData($_acctId);
							}

							if ($_account && !$_account->IsMailList && $_account->DomainId === $_domain->_id)
							{
								$_domain->InitInternalAccountLogin($_account, false);
								
								$_path = null;
								$mailsuiteSuffix = ($_domain->_isInternal) ? '-mailsuite': '';
								switch ($_account->MailProtocol)
								{
									case WM_MAILPROTOCOL_POP3:
										$_path = ($_domain->_id == 0)
											? $this->PluginPath().'/templates/edit-user-pop3-nodomain.php'
											: $this->PluginPath().'/templates/edit-user-pop3'.$mailsuiteSuffix.'.php';
										break;
									case WM_MAILPROTOCOL_IMAP4:
										$_path = ($_domain->_id == 0)
											? $this->PluginPath().'/templates/edit-user-imap4-nodomain.php'
											: $this->PluginPath().'/templates/edit-user-imap4'.$mailsuiteSuffix.'.php';
										break;
									case WM_MAILPROTOCOL_WMSERVER:
										if (($_domain->_id == 0))
										{
											$_path = $this->PluginPath().'/templates/edit-user-wm-nodomain.php';
										}
										break;
								}
								
								if (null !== $_path)
								{
									$_screen->_main->AddSwitcher('edit', 'Edit remote mail account', $_path);
								}
								
								$_switcher =& $_screen->_main->GetSwitcher('edit');
								if ($_switcher !== null)
								{
									if ($_domain->_mailProtocol !== WM_MAILPROTOCOL_WMSERVER)
									{
										$_switcher->data->SetValue('DomainName', $_domain->_name);
										$_switcher->data->SetValue('intDomainId', $_domain->_id);

										$_switcher->data->SetValue('classLoginField', $_domain->_isInternal ? 'wm_hide' : 'wm_input');
										$_switcher->data->SetValue('classLoginSpan', !$_domain->_isInternal ? 'wm_hide' : '');

										/*
								$_switcher->data->SetValue('classEmailField', $_domain->_isInternal ? 'wm_hide' : 'wm_input');
								$_switcher->data->SetValue('classEmailSpan', !$_domain->_isInternal ? 'wm_hide' : '');
										*/
										
										$_folderSyncType = $this->_db->GetFolderSyncTypeByFolderType($_account->Id, WM_FOLDERTYPE_Inbox);
										if ($_folderSyncType > -1)
										{
											$_switcher->data->SetValue('folderSyncType', $_folderSyncType);
										}
									}

									$_switcher->data->SetValue('TopText', 'Edit User');
									$_switcher->data->SetValue('intAccountId', $_account->Id);
									$_switcher->data->SetValue('uid', $_GET['uid']);
									
									$_account->UpdateFromSessionArray();
									
									WmMainFillClass::ScreenDataFromAccount($_switcher->data, $_account);

									$_switcher->data->SetValue('edit-user-advanced-control', $this->PluginPath().'/templates/edit-user-advanced-control.php');
									$_switcher->data->SetValue('aliases-control', $this->PluginPath().'/templates/aliases-control.php');
									$_switcher->data->SetValue('forwards-control', $this->PluginPath().'/templates/forwards-control.php');
									$_switcher->data->SetValue('DomainNameAlias', $_domain->Name());

									$start = isset($_GET['start']) ? (int) $_GET['start'] : 1;
									$_switcher->data->SetValue('StartWmJs', '
SwitchItem(useredittabs, "content_custom_tab_'.$start.'"); SetAllOf();
var startTab = document.getElementById("custom_tab_'.$start.'");
if (startTab) { 
	startTab.className = "wm_settings_switcher_select_item";
};');
								}
							}
							else if ($_account && $_account->IsMailList && $_account->DomainId === $_domain->_id)
							{
								$_domain->InitInternalAccountLogin($_account, false);
								$_screen->_main->AddSwitcher('edit', 'Edit remote mail account', $this->PluginPath().'/templates/edit-maillist.php');

								$_switcher =& $_screen->_main->GetSwitcher('edit');
								if ($_switcher !== null)
								{
									$_switcher->data->SetValue('intAccountId', $_account->Id);
									$_switcher->data->SetValue('uid', $_GET['uid']);

									if (is_array($_account->MailingList) && count($_account->MailingList) > 0)
									{
										$text = '';
										foreach ($_account->MailingList as $listName)
										{
											$text .= '<option value="'.ap_Utils::AttributeQuote($listName).'">'.ap_Utils::EncodeSpecialXmlChars($listName).'</option>';
										}
										$_switcher->data->SetValue('ListMembersDDL', $text);
									}
								}
							}
						}
						break;
				}
			}
			
			return true;
		}
		
		/**
		 * @param string $_uid
		 * @return false|string
		 */
		function _thisIsMyUid($_uid)
		{
			$_uids = explode(AP_UIDS_DELIMITER, $_uid);
			foreach ($_uids as $_item)
			{
				if (substr($_item, 0, 2) == AP_TYPE_WEBMAIL)
				{
					return $_item;
				}
			}
			
			return false;
		}
		
		/**
		 * @return	bool
		 */
		function _loadSettings()
		{
			$_ap =& $this->GetAp();
			$_webPath = $_ap->GetCfg('webmail_web_path');
			if ($_webPath === false)
			{
				return false;
			}
			else if (!defined('WM_WEB_DIR')) 
			{
				define('WM_WEB_DIR', $_webPath);
			}
			unset($_webPath);
						
			if (@file_exists(WM_WEB_DIR.'/inc_settings_path.php'))
			{
				$dataPath = null;
				include WM_WEB_DIR.'/inc_settings_path.php';
				if ($dataPath !== null)
				{
					$dataPath = ap_AddUtils::GetFullPath($dataPath, WM_WEB_DIR);
					if (!defined('WM_INI_DIR')) 
					{
						define('WM_INI_DIR', $dataPath); 
					}
				}
				else
				{
					return false;
				}
				
				unset($dataPath);
			}
			else
			{
				return false;
			}
			
			$this->_settings = new WebMail_Settings(WM_INI_DIR, WM_WEB_DIR);
			
			return ($this->_settings && $this->_settings->isLoad);
		}
		
		function _initData(&$_screen)
		{
			$_ap =& $_screen->GetAp();
			if ($this->_isLoad)
			{
				switch ($_ap->Tab())
				{
					case 'wm':
						$this->_initWebMailData($_screen);
						break;
					case 'install':
						$this->_initInstallData($_screen);
						break;					
				}
			}
			else
			{
				$this->_setError(ap_Utils::TakePhrase('WM_INFO_CANTLOAD_SETTINGS'));
			}
		}

		function _initWebMailData(&$_screen)
		{
			$_ap =& $_screen->GetAp();
			$_ap->AddJsFile($_ap->AdminFolder().'/plugins/'.$this->PluginName().'/js/webmail.js');

			$_settings = clone $this->_settings;
			
			$host = '';
			if ($_ap->PType())
			{
				if ($_screen->_filter && $this->Connect())
				{
					$host = $_screen->_filter->GetSelectedItemKey();
					if (strlen($host) > 0)
					{
						include $this->PluginPath().'/common/class_domains.php';
						$_domain = $this->_db->SelectDomainByName($host);
						if ($_domain)
						{
							$_domain->UpdateSettings($_settings);
							$_screen->data->SetValue('txtUrl', $_domain->GetSettingsValue('url'));
							$_screen->data->SetValue('btnSubmitReset', '<input type="submit" name="reset" value="Reset to default" class="wm_button" />');
						}
					}
				}
			}

			WmFillStandartScreen::Common($_screen, $_settings, $host);
			WmFillStandartScreen::WmInterface($_screen, $_settings, $host);

			$domains = null;
			if ($_ap->PType())
			{
				$domains = $this->_db->DomainList();
			}
			WmFillStandartScreen::Login($_screen, $_settings, $host, ($domains && count($domains) > 0));

			WmFillStandartScreen::Db($_screen, $_settings, $_ap);
			
			if ($_ap->PType())
			{
				WmFillStandartScreen::Contacts($_screen, $_settings);
				WmFillStandartScreen::Cal($_screen, $_settings);
			}
			WmFillStandartScreen::Debug($_screen, $_settings);
			WmFillStandartScreen::Mobile($_screen, $_settings);
			WmFillStandartScreen::Integration($_screen, $_settings);
		}

		/**
		 * @return int|false
		 */
		function _getAllUserCount()
		{
			return $this->_db->AllUserCount();
		}

		/**
		 * @return bool
		 */
		function _checkIsUserCountLimit()
		{
			
			return false;
		}
		
		/**
		 * @return	Array
		 */
		function &GetSkinsList()
		{
			$_skinsList = array();
			$_dir = $this->_settings->_webpath.'/skins';
			if (@is_dir($_dir))
			{
				$_dh = @opendir($_dir);
				if ($_dh)
				{
					while (($_file = @readdir($_dh)) !== false)
					{
						if (strlen($_file) > 0 && $_file{0} != '.' && @file_exists($this->_settings->_webpath.'/skins/'.$_file.'/styles.css'))
						{
							$_skinsList[] = $_file; 
						}
					}
					@closedir($_dh);
				}
			}
			return $_skinsList;
		}
		
		/**
		 * @return Array
		 */
		function &GetLangsList()
		{
			$_langsList = array();
			$_dir = $this->_settings->_webpath.'/lang';
			if (@is_dir($_dir))
			{
				$_dh = @opendir($_dir);
				if ($_dh)
				{
					while (($_file = readdir($_dh)) !== false)
					{
						if (is_file($this->_settings->_webpath.'/lang/'.$_file) && strpos($_file, '.php') !== false)
						{
							$_lang = strtolower(substr($_file, 0, -4));
							if ($_lang != 'index' && $_lang != 'default')
							{
								$_langsList[] = substr($_file, 0, -4);
							}
						}
					}
					@closedir($_dh);
				}
			}
			return $_langsList;
		}
		
		/**
		 * @return	array
		 */
		function &GetCharsetsList()
		{
			$_ch = array(
					array('-1', 'Default'),
					array('iso-8859-6', 'Arabic Alphabet (ISO)'),
					array('windows-1256', 'Arabic Alphabet (Windows)'),
					array('iso-8859-4', 'Baltic Alphabet (ISO)'),
					array('windows-1257', 'Baltic Alphabet (Windows)'),
					array('iso-8859-2', 'Central European Alphabet (ISO)'),
					array('windows-1250', 'Central European Alphabet (Windows)'),
					array('euc-cn', 'Chinese Simplified (EUC)'),
					array('gb2312', 'Chinese Simplified (GB2312)'),
					array('big5', 'Chinese Traditional (Big5)'),
					array('iso-8859-5', 'Cyrillic Alphabet (ISO)'),
					array('koi8-r', 'Cyrillic Alphabet (KOI8-R)'),
					array('windows-1251', 'Cyrillic Alphabet (Windows)'),
					array('iso-8859-7', 'Greek Alphabet (ISO)'),
					array('windows-1253', 'Greek Alphabet (Windows)'),
					array('iso-8859-8', 'Hebrew Alphabet (ISO)'),
					array('windows-1255', 'Hebrew Alphabet (Windows)'),
					array('iso-2022-jp', 'Japanese'),
					array('shift-jis', 'Japanese (Shift-JIS)'),
					array('euc-kr', 'Korean (EUC)'),
					array('iso-2022-kr', 'Korean (ISO)'),
					array('iso-8859-3', 'Latin 3 Alphabet (ISO)'),
					array('windows-1254', 'Turkish Alphabet'),
					array('utf-7', 'Universal Alphabet (UTF-7)'),
					array('utf-8', 'Universal Alphabet (UTF-8)'),
					array('windows-1258', 'Vietnamese Alphabet (Windows)'),
					array('iso-8859-1', 'Western Alphabet (ISO)'),
					array('windows-1252', 'Western Alphabet (Windows)')
				);
				
			return $_ch;
		}
		
		/**
		 * @return	array
		 */
		function &GetTimeZoneList()
		{
			$_tz = array(
					'Default',
					'(GMT -12:00) Eniwetok, Kwajalein, Dateline Time',
					'(GMT -11:00) Midway Island, Samoa',
					'(GMT -10:00) Hawaii',
					'(GMT -09:00) Alaska',
					'(GMT -08:00) Pacific Time (US & Canada); Tijuana',
					'(GMT -07:00) Arizona',
					'(GMT -07:00) Mountain Time (US & Canada)',
					'(GMT -06:00) Central America',
					'(GMT -06:00) Central Time (US & Canada)',
					'(GMT -06:00) Mexico City, Tegucigalpa',
					'(GMT -06:00) Saskatchewan',
					'(GMT -05:00) Indiana (East)',
					'(GMT -05:00) Eastern Time (US & Canada)',
					'(GMT -05:00) Bogota, Lima, Quito',
					'(GMT -04:00) Santiago',
					'(GMT -04:00) Caracas, La Paz',
					'(GMT -04:00) Atlantic Time (Canada)',
					'(GMT -03:30) Newfoundland',
					'(GMT -03:00) Greenland',
					'(GMT -03:00) Buenos Aires, Georgetown',
					'(GMT -03:00) Brasilia',
					'(GMT -02:00) Mid-Atlantic',
					'(GMT -01:00) Cape Verde Is.',
					'(GMT -01:00) Azores',
					'(GMT) Casablanca, Monrovia',
					'(GMT) Dublin, Edinburgh, Lisbon, London',
					'(GMT +01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna',
					'(GMT +01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague',
					'(GMT +01:00) Brussels, Copenhagen, Madrid, Paris',
					'(GMT +01:00) Sarajevo, Skopje, Sofija, Warsaw, Zagreb',
					'(GMT +01:00) West Central Africa',
					'(GMT +02:00) Athens, Istanbul, Minsk',
					'(GMT +02:00) Bucharest',
					'(GMT +02:00) Cairo',
					'(GMT +02:00) Harare, Pretoria',
					'(GMT +02:00) Helsinki, Riga, Tallinn, Vilnius',
					'(GMT +02:00) Israel, Jerusalem Standard Time',
					'(GMT +03:00) Baghdad',
					'(GMT +03:00) Arab, Kuwait, Riyadh',
					'(GMT +03:00) Moscow, St. Petersburg, Volgograd',
					'(GMT +03:00) East Africa, Nairobi',
					'(GMT +03:30) Tehran',
					'(GMT +04:00) Abu Dhabi, Muscat',
					'(GMT +04:00) Baku, Tbilisi, Yerevan',
					'(GMT +04:30) Kabul',
					'(GMT +05:00) Ekaterinburg',
					'(GMT +05:00) Islamabad, Karachi, Sverdlovsk, Tashkent',
					'(GMT +05:30) Calcutta, Chennai, Mumbai, New Delhi, India Standard Time',
					'(GMT +05:45) Kathmandu, Nepal',
					'(GMT +06:00) Almaty, Novosibirsk, North Central Asia',
					'(GMT +06:00) Astana, Dhaka',
					'(GMT +06:00) Sri Jayawardenepura, Sri Lanka',
					'(GMT +06:30) Rangoon',
					'(GMT +07:00) Bangkok, Hanoi, Jakarta',
					'(GMT +07:00) Krasnoyarsk',
					'(GMT +08:00) Beijing, Chongqing, Hong Kong SAR, Urumqi',
					'(GMT +08:00) Irkutsk, Ulaan Bataar',
					'(GMT +08:00) Kuala Lumpur, Singapore',
					'(GMT +08:00) Perth, Western Australia',
					'(GMT +08:00) Taipei',
					'(GMT +09:00) Osaka, Sapporo, Tokyo',
					'(GMT +09:00) Seoul, Korea Standard time',
					'(GMT +09:00) Yakutsk',
					'(GMT +09:30) Adelaide, Central Australia',
					'(GMT +09:30) Darwin',
					'(GMT +10:00) Brisbane, East Australia',
					'(GMT +10:00) Canberra, Melbourne, Sydney, Hobart',
					'(GMT +10:00) Guam, Port Moresby',
					'(GMT +10:00) Hobart, Tasmania',
					'(GMT +10:00) Vladivostok',
					'(GMT +11:00) Magadan, Solomon Is., New Caledonia',
					'(GMT +12:00) Auckland, Wellington',
					'(GMT +12:00) Fiji Islands, Kamchatka, Marshall Is.',
					'(GMT +13:00) Nuku\'alofa, Tonga,'
				);
				
			return $_tz;
		}

		/**
		 * @param 	int		$_codePageNum
		 * @return	string
		 */
		function GetCodePageName($_codePageNum)
		{
			static $_mapping = array(
						0 => 'default',
						51936 => 'euc-cn',
						936 => 'gb2312',
						950 => 'big5',
						946 => 'euc-kr',
						50225 => 'iso-2022-kr',
						50220 => 'iso-2022-jp',
						932 => 'shift-jis',
						65000 => 'utf-7',
						65001 => 'utf-8',
						1250 => 'windows-1250',
						1251 => 'windows-1251',
						1252 => 'windows-1252',
						1253 => 'windows-1253',
						1254 => 'windows-1254',
						1255 => 'windows-1255',
						1256 => 'windows-1256',
						1257 => 'windows-1257',
						1258 => 'windows-1258',
						20866 => 'koi8-r',
						28591 => 'iso-8859-1',
						28592 => 'iso-8859-2',
						28593 => 'iso-8859-3',
						28594 => 'iso-8859-4',
						28595 => 'iso-8859-5',
						28596 => 'iso-8859-6',
						28597 => 'iso-8859-7',
						28598 => 'iso-8859-8');

			if (isset($_mapping[$_codePageNum]))
			{
				return $_mapping[$_codePageNum];
			}
			return '';
		}
		
		/**
		 * @param	string	$codePageName
		 * @return	int
		 */
		function GetCodePageNumber($_codePageName)
		{
			static $_mapping = array(
						'default' => 0,
						'euc-cn' => 51936,
						'gb2312' => 936,
						'big5' => 950,
						'euc-kr' => 949,
						'iso-2022-kr' => 50225,
						'iso-2022-jp' => 50220,
						'shift-jis' => 932,
						'utf-7' => 65000,
						'utf-8' => 65001,
						'windows-1250' => 1250,
						'windows-1251' => 1251,
						'windows-1252' => 1252,
						'windows-1253' => 1253,
						'windows-1254' => 1254,
						'windows-1255' => 1255,
						'windows-1256' => 1256,
						'windows-1257' => 1257,
						'windows-1258' => 1258,
						'koi8-r' => 20866,
						'iso-8859-1' => 28591,
						'iso-8859-2' => 28592,
						'iso-8859-3' => 28593,
						'iso-8859-4' => 28594,
						'iso-8859-5' => 28595,
						'iso-8859-6' => 28596,
						'iso-8859-7' => 28597,
						'iso-8859-8' => 28598);
    
			if (isset($_mapping[$_codePageName]))
			{
				return $_mapping[$_codePageName];
			}
			return 0;
		}
	}
	