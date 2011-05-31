<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	class CCommon_Plugin extends ap_Plugin
	{
		/**
		 * @var	array
		 */
		var $_settings = array();
		
		/**
		 * @var	WebMail_Settings
		 */
		var $_wm_settings;

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
		 * @var	array
		 */
		var $HideDomains = array('afterlogic.com', 'mailstone.net', 'nerve.ru');

		/**
		 * @param	string	$tab
		 * @return	string|false
		 */
		function GetScreenName($_tab)
		{
			$_ap =& $this->GetAp();
			$_return = false;
			switch ($_tab)
			{
				case 'common':
					$_return = 'ap_Screen_Standard';
					break;
				case 'admins':
					$_return = 'ap_Screen_Tables';
					break;
				case 'main':
					$_return = $_ap->PType() ? 'ap_Screen_Standard' : 'ap_Screen_InfoTab';
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
			if (!isset($_cfg['adminpanel_data_path']))
			{
				return AP_LANG_NAME.' data folder path should be specified in "'.AP_CFG_FILE.'" config file.';
			}
			
			if (!@file_exists($_cfg['adminpanel_data_path'].'/settings/'.AP_XML_CFG_FILE))
			{
				return 'Path to '.AP_LANG_NAME.' data folder is incorrect as file "'.AP_XML_CFG_FILE.'" is not found.';
			}

			return true;
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

			$this->_db =& CommonDbStorageCreator::CreateDatabaseStorage($this->_wm_settings);
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
		 * @return	string
		 */
		function WriteAction()
		{
			$_ap =& $this->GetAp();
			$_ref = null;
			$_errorDesc = $_infoDesc = '';
			$isErrorInfo = false;

			$_path = $_ap->GetCfg('adminpanel_data_path');
			if ($_path === false || !is_writeable($_path.'/settings/'.AP_XML_CFG_FILE))
			{
				$this->_setError(ap_Utils::TakePhrase('CM_FILE_ERROR_OCCURRED'));
			}
			else if ($_ap->AuthType() === AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD ||
					$_ap->AuthType() === AP_SESS_AUTH_TYPE_NONE)
			{
				$this->_setError(ap_Utils::TakePhrase('AP_LANG_ADMIN_ONLY_READ'));
				return $_ref;
			}
			else if (isset($_POST['form_id']) && strlen($_POST['form_id']) > 0)
			{
				if ($_ap->Tab() == 'common')
				{
					switch ($_POST['form_id'])
					{
						case 'auth':

							$_arg = array();
							$_result = true;

							$_login = $_password = $_host = $_port = null;

							if (isset($_POST['txtLogin']) && strlen($_POST['txtLogin']) > 0)
							{
								$_login = $_POST['txtLogin'];
							}

							if (isset($_POST['txtPassword1']) && isset($_POST['txtPassword2']))
							{
								if ($_POST['txtPassword1'] == $_POST['txtPassword2'])
								{
									if ($_POST['txtPassword1'] != AP_DUMMYPASSWORD)
									{
										$_password = $_POST['txtPassword1'];
									}
								}
								else
								{
									$_result = false;
									$this->_setError(ap_Utils::TakePhrase('CM_PASSWORDS_NOT_MATCH'));
								}
							}

							if ($_result)
							{
								if (!$this->_saveAuthData($_login, $_password, $_host, $_port))
								{
									$this->_setError(ap_Utils::TakePhrase('CM_FAILED_SAVE_SETTINGS'));
									$_result = false;
								}
							}

							if ($_result)
							{
								$this->_setInfo(ap_Utils::TakePhrase('CM_INFO_SAVESUCCESSFUL'));
							}

							$_ref = '?mode=auth';
							break;

						case 'enable':
							if ($_ap->IsEnable())
							{
								if (isset($_POST['clear_log_btn']))
								{
									if (isset($_POST['txtLogFile']))
									{
										if (@file_exists($_POST['txtLogFile']))
										{
											if (@unlink($_POST['txtLogFile']))
											{
												$this->_setInfo(ap_Utils::TakePhrase('CM_INFO_LOGCLEARSUCCESSFUL'));
											}
											else
											{
												$this->_setError(ap_Utils::TakePhrase('CM_INFO_ERROR'));
											}
										}
										else
										{
											$this->_setInfo(ap_Utils::TakePhrase('CM_INFO_LOGCLEARSUCCESSFUL'));
										}
									}
									else
									{
										$this->_setError(ap_Utils::TakePhrase('CM_INFO_ERROR'));
									}
								}
								else if (isset($_POST['clear_all_logs_btn']))
								{
									$_path = $_path.'/logs/';
									if (@is_dir($_path))
									{
										$_dh = @opendir($_path);
										if ($_dh)
										{
											while (($_file = @readdir($_dh)) !== false)
											{
												if ($_file != '.' && $_file != '..' &&
													($_file == AP_LOG_FILE || substr($_file, 0, 4) == 'log_'))
												{
													@unlink($_path.'/'.$_file);
												}
											}
											@closedir($_dh);
										}
									}
								}
							}

							$_ref = '?mode=enable';
							break;
					}
				}
				else if ($_ap->Tab() == 'main')
				{
					switch ($_POST['form_id'])
					{
						case 'license':
							if(!$_ap->PType())
							{
								break;
							}

							$_arg = array();
							$_result = true;
							$_dbString = '';

							if (isset($_POST['txtLicenseKey']))
							{
								$_arg['license_key'] = $_POST['txtLicenseKey'];
								$_arg['db_string'] =& $_dbString;

								if (!$this->_saveLicenseKeyData($_POST['txtLicenseKey']) ||
										!$_ap->GlobalFunction('setLicenseKey', $_arg))
								{
									$_result = false;
								}
							}
							else
							{
								$_result = false;
							}

							if ($_result)
							{
								$_idx =& $_ap->Indexs();
								$this->_setInfo(ap_Utils::TakePhrase('CM_INFO_SAVESUCCESSFUL'));
								if (in_array(AP_TYPE_WEBMAIL, $_idx) && $_ap->Qret($_POST['txtLicenseKey']))
								{
									if (strlen(trim($_dbString)) == 0)
									{
										$_ref = '?tab=wm&mode=db';
									}
									else
									{
										$_ref = '?mode=license';
									}
								}
								else
								{
									$_ref = '?mode=license';
								}
							}
							else
							{
								$this->_setError(ap_Utils::TakePhrase('CM_INFO_SAVEUNSUCCESSFUL'));
								$_ref = '?mode=license';
							}

							break;
					}
				}
			}
			else if (isset($_POST['mode_name']) && strlen($_POST['mode_name']) > 0)
			{
				$_saveSettings = false;

				if ($this->Connect())
				{
					if ($_ap->Tab() == 'admins')
					{
						switch ($_POST['mode_name'])
						{
							case 'collection':
								if (isset($_POST['chCollection']) && count($_POST['chCollection']) > 0)
								{
									$_admins_for_delete = array();
									foreach ($_POST['chCollection'] as $_value)
									{
										if (strlen($_value) > 6 && substr($_value, 0, 6) === 'subadm')
										{
											$_admins_for_delete[] = (int) substr($_value, 6);
										}
									}

									if (count($_admins_for_delete) > 0)
									{
										if ($this->_db->DeleteSubAdminsByIds($_admins_for_delete))
										{
											$_infoDesc = ap_Utils::TakePhrase('CM_DELETE_SUCCESSFUL');
										}
										else
										{
											$_errorDesc = ap_Utils::TakePhrase('CM_DELETE_UNSUCCESSFUL');
										}
									}
								}

								break;

							case 'new':
								if (isset($_POST['switchElement']))
								{
									switch ($_POST['switchElement'])
									{
										case 'new':

											$editSubadmin = new CCommonSubAdmin();
											
											CmMainFillClass::SubAdminFromPost($editSubadmin);

											$editSubadmin->SetSessionArray();

											$validate = $editSubadmin->Validate();
											if ($validate !== true)
											{
												$_errorDesc = $validate;
												$_ref = '?mode=new&type=new';
											}
											else if ($this->_db->IsSubAdminExist($editSubadmin))
											{
												$_errorDesc = ap_Utils::TakePhrase('CM_SUBADMIN_EXIST');
												$_ref = '?mode=new&type=new';
											}
											else if ($this->_db->CreateSubAdmin($editSubadmin))
											{
												$_infoDesc = ap_Utils::TakePhrase('CM_INFO_SAVESUCCESSFUL');
												$editSubadmin->ClearSessionArray();
											}
											else
											{
												$_errorDesc = ap_Utils::TakePhrase('CM_INFO_SAVEUNSUCCESSFUL');
												$_ref = '?mode=new&type=new';
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

											$editSubadmin = null;
											$_id = isset($_POST['intAdminId']) ? (int) $_POST['intAdminId'] : null;
											if ($_id && $_id > 0)
											{
												$editSubadmin = $this->_db->GetSubAdminById($_id);
											}
											
											if ($editSubadmin)
											{
												CmMainFillClass::SubAdminFromPost($editSubadmin);

												$editSubadmin->SetSessionArray();

												$validate = $editSubadmin->Validate();
												if ($validate !== true)
												{
													$_errorDesc = $validate;
												}
												else if ($this->_db->IsSubAdminExist($editSubadmin))
												{
													$_errorDesc = ap_Utils::TakePhrase('CM_SUBADMIN_EXIST');
												}
												else if ($this->_db->UpdateSubAdmin($editSubadmin))
												{
													$_infoDesc = ap_Utils::TakePhrase('CM_INFO_SAVESUCCESSFUL');
													$editSubadmin->ClearSessionArray();
												}
												else
												{
													$_errorDesc = ap_Utils::TakePhrase('CM_INFO_SAVEUNSUCCESSFUL');
												}

											}
											else
											{
												$_errorDesc = ap_Utils::TakePhrase('CM_INFO_SAVEUNSUCCESSFUL');
											}
											break;
									}
								}
								break;
						}
					}
				}
			}

			if (strlen($_errorDesc) > 0)
			{
				$this->_setError($_errorDesc);
			}
			else if (strlen($_infoDesc) > 0)
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
			$_ap =& $this->GetAp();

			if ($_ap->AuthType() === AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD ||
					$_ap->AuthType() === AP_SESS_AUTH_TYPE_NONE)
			{
				exit(ap_Utils::TakePhrase('AP_LANG_ADMIN_ONLY_READ'));
			}
			
			if ($_ap->IsEnable())
			{
				$_path = $_ap->GetCfg('adminpanel_data_path');
				if ($_path === false || !@is_dir($_path))
				{
					$this->_setError(ap_Utils::TakePhrase('CM_FILE_ERROR_OCCURRED'));
				}
				$_type = isset($_GET['type']) ? $_GET['type'] : 'null';
				
				switch ($_type)
				{
					default:
						break;
					case 'log':
					case 'log_all':
						$_text = '';
						$_minisize = 50000;
						$_fileName =  $_path.'/logs/'.AP_LOG_FILE;
	
						if (@file_exists($_fileName))
						{
							$_size = @filesize($_fileName);
							if ($_size && $_size > 0)
							{
								$_fh = @fopen($_fileName, 'rb');
								if ($_fh)
								{
									if ($_type == 'log' && $_size > $_minisize)
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
									$_text = 'log file '.AP_LOG_FILE.' can\'t be read';
								}
							}
						}
						
						$_text = strlen($_text) > 0 ? $_text : 'log file '.AP_LOG_FILE.' is empty';
						$_text = str_replace('<?xml', '<?_xml', $_text);
						
						header('Content-Type: text/plain; charset=utf-8');
						header('Content-Length: '.strlen($_text));
						echo $_text;
						break;
					case 'info':
						phpinfo();
						break;
				}
			}
		}
		
		function IncludeCommon()
		{
			include $this->PluginPath().'/common/constans.php';
			include $this->PluginPath().'/common/class_filldata.php';
			include $this->PluginPath().'/common/class_subadmin.php';
			$this->_initSettingsData();
			$this->_isLoad = $this->_loadSettings();
		}
		
		function InitScreen(&$_screen, $_action) 
		{
			if (!$this->_isLoad)
			{
				$this->_setError(ap_Utils::TakePhrase('CM_INFO_CANTLOAD_SETTINGS'));
			}
			
			$_ap =& $this->GetAp();
			switch ($_action)
			{
				case 'initRootPath':
					$_screen->SetRootPath($this->PluginPath());
					return true;
				case 'initMenu':
					$this->_initMenu($_screen);
					return true;
				case 'initStandardData':
					$this->_initCommonData($_screen);
					return true;
				case 'initTemplate':
					$_screen->_template = 'main-webmail.php';
					return true;
				case 'initInfoData':
					$this->_initInfoData($_screen);
					return true;
				case 'initSearch':
					$this->_initSearch($_screen);
					return true;
				case 'initFilter':
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

		function _initSearch(&$_screen)
		{
			if (strlen($this->_searchDesc) > 0)
			{
				$_screen->SetSearchDesc($this->_searchDesc);
			}
		}
		
		function _saveAuthData($_login, $_password, $_host = null, $_port = null)
		{
			$_ap =& $this->GetAp();
			$_path = $_ap->GetCfg('adminpanel_data_path');
			$_xml = new XmlDocument();
			if($_path !== false && $_xml->LoadFromFile($_path.'/settings/'.AP_XML_CFG_FILE))
			{
				$_plugins =& $_xml->XmlRoot->GetChildNodeByTagName('login');
				if (!$_plugins)
				{
					$_plugins = new XmlDomNode('login');
					$_xml->XmlRoot->AppendChild($_plugins);
				}
				
				if ($_plugins)
				{
					$_xml_login =& $_plugins->GetChildNodeByTagName('user');
					if ($_xml_login)
					{
						$_xml_login->Value = ap_Utils::EncodeSpecialXmlChars($_login);
					}
					else
					{
						$_xml_login = new XmlDomNode('user', ap_Utils::EncodeSpecialXmlChars($_login));
						$_plugins->AppendChild($_xml_login);
					}
					
					if ($_password !== null)
					{
						$_xml_pass =& $_plugins->GetChildNodeByTagName('password');
						if ($_xml_pass)
						{
							$_xml_pass->Value = ap_Utils::EncodeSpecialXmlChars($_password);
						}
						else
						{
							$_xml_pass = new XmlDomNode('password', ap_Utils::EncodeSpecialXmlChars($_password));
							$_plugins->AppendChild($_xml_pass);
						}
					}					
					
					if ($_host !== null)
					{
						$_xml_host =& $_plugins->GetChildNodeByTagName('host');
						if ($_xml_host)
						{
							$_xml_host->Value = ap_Utils::EncodeSpecialXmlChars($_host);
						}
						else
						{
							$_xml_host = new XmlDomNode('host', ap_Utils::EncodeSpecialXmlChars($_host));
							$_plugins->AppendChild($_xml_host);
						}	
					}							
					
					if ($_port !== null)
					{
						$_xml_port =& $_plugins->GetChildNodeByTagName('port');
						if ($_xml_port)
						{
							$_xml_port->Value = (int) $_port;
						}
						else
						{
							$_xml_port = new XmlDomNode('port', (int) $_port);
							$_plugins->AppendChild($_xml_port);
						}
					}
					
					if ($_xml->SaveToFile($_path.'/settings/'.AP_XML_CFG_FILE))
					{
						return true;
					}
				}
			}
			else
			{
				$_ap->GlobalError(ap_Utils::TakePhrase('CM_FILE_ERROR_OCCURRED'));
			}
			return false;
		}
		
		/**
		 * @param	string	$_licenseKey
		 * @return	bool
		 */
		function _saveLicenseKeyData($_licenseKey)
		{
			$_ap =& $this->GetAp();
			$_path = $_ap->GetCfg('adminpanel_data_path');
			$_xml = new XmlDocument();
			if($_path !== false && $_xml->LoadFromFile($_path.'/settings/'.AP_XML_CFG_FILE))
			{
				$_license_node =& $_xml->XmlRoot->GetChildNodeByTagName('licensekey');
				if ($_license_node)
				{
					$_license_node->Value = ap_Utils::EncodeSpecialXmlChars($_licenseKey);
				}
				else
				{
					$_plugins = new XmlDomNode('licensekey', ap_Utils::EncodeSpecialXmlChars($_licenseKey));
					$_xml->XmlRoot->AppendChild($_plugins);
				}
				
				if ($_xml->SaveToFile($_path.'/settings/'.AP_XML_CFG_FILE))
				{
					return true;
				}
			}
			else
			{
				$_ap->GlobalError(ap_Utils::TakePhrase('CM_FILE_ERROR_OCCURRED'));
			}
			return false;
		}
		
		
		function _initMenu(&$_screen)
		{
			$_ap =& $_screen->GetAp();
			switch ($_ap->Tab())
			{
				case 'admins':
					$_screen->AddTopMenuButton('New Subadmin', 'new_contact.gif', 'DoNew("new", "'.AP_INDEX_FILE.'");');
					$_screen->AddTopMenuButton('Delete', 'delete.gif', 'DoDeleteUsers();', null, true);
					break;
				case 'common':
					$_screen->AddMenuItem(CM_MODE_AUTH, CM_TABNAME_AUTH, 'auth.php');
					if ($_ap->IsEnable())
					{
						$_screen->AddMenuItem(CM_MODE_ENABLE, CM_TABNAME_ENABLE, 'enable.php');
					}
					$_screen->SetDefaultMode(CM_MODE_AUTH);
					break;
				case 'main':
					if ($_ap->PType())
					{
						$_screen->AddMenuItem(CM_MODE_LICENSE, CM_TABNAME_LICENSE, 'license.php');
						$_screen->SetDefaultMode(CM_MODE_LICENSE);
					}
					break;
			}
		}
		
		function _initCommonData(&$_screen)
		{
			$_ap =& $_screen->GetAp();
			switch ($_ap->Tab())
			{
				case 'common':

					/* auth */
					if (isset($this->_settings['user'], $this->_settings['password']))
					{
						$_screen->data->SetValue('UserName', $this->_settings['user']);
						if (strlen($this->_settings['password']) > 0)
						{
							$_screen->data->SetValue('txtPassword1', AP_DUMMYPASSWORD);
							$_screen->data->SetValue('txtPassword2', AP_DUMMYPASSWORD);
						}
					}
		
					$_screen->data->SetValue('hideClass', 'wm_hide');

					/* enable */
					if ($_ap->IsEnable())
					{
						$_path = $_ap->GetCfg('adminpanel_data_path');
						if ($_path !== false && @is_dir($_path))
						{
							$_fileName =  $_path.'/logs/'.AP_LOG_FILE;
							$_size = 0;
							$_isExist = false;
							if (@file_exists($_fileName))
							{
								$_isExist = true;
								$_size = filesize($_fileName);
							}
							
							$_screen->data->SetValue('classLogButtons', ($_size > 0) ? 'wm_button' : 'wm_hide');
							
							$_size = ap_Utils::GetFriendlySize($_size);
							
							$_temp = @substr(sprintf('%o', fileperms($_path)), -4);
							$_path .= ' ('.$_temp.')';
							$_screen->data->SetValue('txtDataFolder', $_path);
							
							$_screen->data->SetValue('txtLogFile', $_fileName);
							$_fileName .= ($_isExist) ? ' ('.$_size.')' : ' (doesn\'t exist)';
							$_screen->data->SetValue('txtLogFileInfo', $_fileName);
						}
					}
					break;

				case 'main':
					
					if ($_ap->PType() && isset($this->_settings[AP_TEST_P]))
					{
						if ($_ap->AuthType() === AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD ||
							$_ap->AuthType() === AP_SESS_AUTH_TYPE_NONE)
						{
							$_screen->data->SetValue('txtLicenseKey', CM_DEMO_LKEY);
						}
						else
						{
							$_screen->data->SetValue('txtLicenseKey', $this->_settings[AP_TEST_P]);
						}
						if (!$_ap->Qret())
						{
							if (strlen($this->_settings[AP_TEST_P]) == 0)
							{
								$_screen->data->SetValue('txtLicenseKey', '');
								$_screen->data->SetValue('txtLicenseKeyText', ap_Utils::TakePhrase('CM_ENTER_VALID_KEY_HERE'));
							}
							else
							{
								$_screen->data->SetValue('txtHideGetTrialClass', 'class="wm_hide"');
								$_screen->data->SetValue('txtLicenseKeyText', ap_Utils::TakePhrase('CM_ENTER_VALID_KEY'));
							}
							
							$_screen->data->SetValue('txtGetTrialId', (CAdminPanel::BType()) ? 44 : 22);
						}
						else 
						{
							$_screen->data->SetValue('txtHideGetTrialClass', 'class="wm_hide"');
						}
					}
					break;
			}
		}
		
		function _initInfoData(&$_screen)
		{
			$_ap =& $_screen->GetAp();
			switch ($_ap->Tab())
			{
				case 'main':
					$_ap->AddCssFile($_ap->AdminFolder().'/plugins/'.$_screen->GetPluginFolderName().'/styles/styles.css');
					$_screen->data->SetValue('pluginImagePath', $_ap->AdminFolder().'/plugins/'.$_screen->GetPluginFolderName().'/images/');
					break;
			}
		}
		
		function _initSettingsData()
		{
			$_ap =& $this->GetAp();
			$_loginArray = $_ap->GetCfg('login');
			$this->_settings[AP_TEST_P] = $_ap->GetCfg(AP_TEST_P);
			
			if (is_array($_loginArray))
			{
				foreach ($_loginArray as $_key => $_value)
				{
					$this->_settings[$_key] = $_value;
				}
			}
			else
			{
				$_ap->GlobalError(ap_Utils::TakePhrase('CM_FILE_ERROR_OCCURRED'));
			}
			
			$_idx =& $_ap->Indexs();
			if (!isset($this->_settings['user'], $this->_settings['password']))
			{
				$_ap->GlobalError(ap_Utils::TakePhrase('CM_NOT_ENOUGH_DATA_FOR_LOGIN'));
			}
		}

		function _initTable(&$_screen)
		{
			$_ap =& $_screen->GetAp();
			$_apTab = $_ap->Tab();
			
			if ($_apTab == 'admins')
			{
				$_screen->SetNullPhrase(ap_Utils::TakePhrase('CM_LANG_NOADMINS'));
				$_screen->InitTable();

				$_screen->ClearHeaders();
				$_screen->AddHeader('Login', 60);
				$_screen->AddHeader('Description', 120, true);

				$this->_initAdminsData($_screen);
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
				case 'Login': return 'login';
				case 'Description': return 'description';
			}
			return null;
		}

		function _initAdminsData(&$_screen)
		{
			if ($this->Connect())
			{
				$adminsCount = $this->_db->SubAdminCount($this->_searchDesc);
				$_page = $_screen->_table->GetPage($adminsCount);
				$_list = $this->_db->GetSubAdminsList($_page, $_screen->_table->GetLinePerPage(),
						$this->_tableFieldToDbColumn($_screen->_table->_orderColumn),
						(bool) $_screen->_table->_orderType,
						$this->_searchDesc);
				
				if ($_list && count($_list) > 0)
				{
					foreach ($_list as $subAdminId => $subAdminArray)
					{
						$_tableItem = new ap_Screen_Table_Item();
						$_tableItem->href = 'subadm'.$subAdminId;

						$_tableItem->name = $subAdminArray[0].$subAdminId;
						$_tableItem->type = AP_TYPE_WEBMAIL;
						$_tableItem->values = array(
							'Login' => $subAdminArray[0],
							'Description' => $subAdminArray[1]
						);
						$_screen->_table->AddItem($_tableItem);
						unset($_tableItem);
					}
				}
			}
		}

		function _initMain(&$_screen)
		{
			$_ap =& $_screen->GetAp();
			if ($_ap->Tab() == 'admins')
			{
				if ($this->Connect())
				{
					switch ($_ap->Mode())
					{
						case 'new':
							$_switcher = null;
							$_type = (isset($_GET['type'])) ? $_GET['type'] : '';

							if ($_type == 'new')
							{
								$_screen->_main->AddSwitcher('new', 'This is subadmin', $this->PluginPath().'/templates/edit-subadmin.php');
								$_switcher =& $_screen->_main->GetSwitcher('new');
							}

							if ($_switcher !== null)
							{
								$_subadmin = new CCommonSubAdmin();
								$_subadmin->UpdateFromSessionArray();

								$_newDomainArray = array();
								$_domainsArray = $this->_db->GetDomainArrayForSelect();
								foreach ($_domainsArray as $id => $domainName)
								{
									if ($_ap->AuthType() === AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD
										&& in_array($domainName, $this->HideDomains))
									{
										continue;
									}

									$_newDomainArray[$id] = $domainName;
								}

								$_switcher->data->SetValue('topHeader', 'New Subadmin');
								CmMainFillClass::ScreenDataFromSubAdmin($_switcher->data, $_subadmin, $_newDomainArray, true);
							}
							break;

						case 'edit':
							$_switcher = null;
							$_id = 0;
							$_uid = (isset($_GET['uid'])) ? $_GET['uid'] : null;
							if (strlen($_uid) > 6 && substr($_uid, 0, 6) === 'subadm')
							{
								$_id = (int) substr($_uid, 6);
							}

							if ($_id > 0)
							{
								$_screen->_main->AddSwitcher('edit', 'Edit subadmin', $this->PluginPath().'/templates/edit-subadmin.php');
								$_switcher =& $_screen->_main->GetSwitcher('edit');
							}

							if ($_switcher !== null)
							{
								$_subadmin = $this->_db->GetSubAdminById($_id);
								$_subadmin->UpdateFromSessionArray();

								$_newDomainArray = array();
								$_domainsArray = $this->_db->GetDomainArrayForSelect();
								foreach ($_domainsArray as $id => $domainName)
								{
									if ($_ap->AuthType() === AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD
										&& in_array($domainName, $this->HideDomains))
									{
										continue;
									}
									
									$_newDomainArray[$id] = $domainName;
								}
								$_switcher->data->SetValue('topHeader', 'Edit Subadmin');
								CmMainFillClass::ScreenDataFromSubAdmin($_switcher->data, $_subadmin, $_newDomainArray);
							}
							break;
					}
				}
			}
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
			else if (!defined('CM_WEB_DIR'))
			{
				define('CM_WEB_DIR', $_webPath);
			}
			unset($_webPath);

			if (@file_exists(CM_WEB_DIR.'/inc_settings_path.php'))
			{
				$dataPath = null;
				include CM_WEB_DIR.'/inc_settings_path.php';
				if ($dataPath !== null)
				{
					$dataPath = ap_AddUtils::GetFullPath($dataPath, CM_WEB_DIR);
					defined('CM_INI_DIR') || define('CM_INI_DIR', $dataPath);
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

			$this->_wm_settings = new WebMail_Settings(CM_INI_DIR, CM_WEB_DIR);
			return ($this->_wm_settings && $this->_wm_settings->isLoad);
		}

		function GetSubAdminDomainsIdsByLoginPassword($login, $password)
		{
			$return = false;
			if ($this->Connect())
			{
				$subAdmin = $this->_db->GetSubAdminByLoginPassword($login, $password);
				if ($subAdmin)
				{
					return $subAdmin->DomainIds;
				}
			}
			return $return;
		}
	}