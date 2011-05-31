<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	class AAdminPanel
	{
		function InitDataCr()
		{
			return '';
		}

		function _mainError()
		{
			return true;
		}
		
		function _isGoodOl()
		{
			return true;
		}
		
		function PType()
		{
			return false;
		}
		
		function _chePluginsTabs($_isGood, $_tab)
		{
			return true;
		}
		
		function InstallOL()
		{
			return true;
		}

		function BType()
		{
			return false;
		}
	}

	
	class CAdminPanelType extends AAdminPanel
	{}
	
	
	class CAdminPanelRoot extends CAdminPanelType
	{}
	

	class CAdminPanel extends CAdminPanelRoot
	{
		/**
		 * @var	string
		 */
		var $_mode = 'default';
		
		/**
		 * @var	string
		 */
		var $_tab = 'default';
		
		/**
		 * @var	array
		 */
		var $_main = array();
		
		/**
		 * @var	array
		 */
		var $_main_all = array();
		
		/**
		 * @var	array
		 */
		var $_pluginsInfo = array();

		/**
		 * @var	array
		 */
		var $_pluginsInfoLevel = array();
		
		/**
		 * @var	array
		 */
		var $_pluginsTabs = array();
		
		/**
		 * @var	array
		 */
		var $_cfg = array();
		
		/**
		 * @var	bool
		 */
		var $_useLog = false;
		
		/**
		 * @var	bool
		 */
		var $_authDone = false;

		/**
		 * @var	int
		 */
		var $_authType;
		
		/**
		 * @var	bool
		 */
		var $_isInstall = false;
		
		/**
		 * @var string
		 */
		var $_logo = '';
		
		/**
		 * @var string
		 */
		var $_title = 'Administration';
		
		/**
		 * @var	string
		 */
		var $_checkEnvironment = '';
		
		/**
		 * @var	array
		 */
		var $_plugIndexs = array();
		
		/**
		 * @var	array
		 */
		var $_css_files = array();
		
		/**
		 * @var	array
		 */
		var $_js_files = array();

		/**
		 * @param bool $_value
		 */
		function UseLog($_value)
		{
			$this->_useLog = $_value;
		}

		/**
		 * @return bool
		 */
		function UseDb()
		{
			return AP_USE_DB;
		}

		/**
		 * @return	CAdminPanel
		 */
		function CAdminPanel($_fileName = null)
		{
			if (null !== $_fileName)
			{
				$_newFileName = basename($_fileName);
				if (strlen($_newFileName) > 3)
				{
					define('AP_INDEX_FILE', $_newFileName);
				}
			}

			$this->_cfg = array();

			CAdminPanel::InitConstans();
			CAdminPanel::InitVersion();
			
			$this->_logo = $this->GetLogoType();
			
			defined('AP_INDEX_FILE') || define('AP_INDEX_FILE', AP_INDEX_FILE_TEMP);

			$_rp = CAdminPanel::RootPath();
			if (strtolower(AP_INDEX_FILE) == 'install.php' && @file_exists($_rp.'/install.php') &&
				isset($_SESSION[AP_SESS_GOODORBAD]) && $_SESSION[AP_SESS_GOODORBAD])
			{
				if (false)
				{
					@header('Location: install.php');
					exit();
				}
				$_SESSION[AP_SESS_INSTALL] = true;
			}
			
			$GLOBALS[AP_START_TIME] = ap_Utils::Microtime();
			$GLOBALS[AP_DB_COUNT] = $GLOBALS[AP_WM_COUNT] = $GLOBALS[AP_WM_TIME] = 0;

			include $_rp.'/core/xmldocument.php';
			include $_rp.'/core/plugin.php';
			include $_rp.'/core/screens.php';
			
			$this->_isInstall = isset($_SESSION[AP_SESS_INSTALL]);

			if (isset($_GET['logout']) || ($this->_isInstall && (!isset($_GET['mode']) && !isset($_GET['check']))))
			{
				$_SESSION = array();
				@header('Location: '.AP_INDEX_FILE.'?login');
			}
			
			if (isset($_GET['enable']) && strlen($_GET['enable']) > 0)
			{
				if ($_GET['enable'] == 'off' && isset($_SESSION[AP_SESS_ENABLE]))
				{
					unset($_SESSION[AP_SESS_ENABLE]);
				}
				else if (isset($_SESSION[AP_SESS_ENABLE]))
				{
					$_SESSION[AP_SESS_ENABLE][$_GET['enable']] = true;
				}
				else
				{
					$_SESSION[AP_SESS_ENABLE] = array($_GET['enable'] => true);
				}
			}
			
			$this->_initPath();
			
			/* custom class file load */
			if (defined('AP_DATA_FOLDER') && @file_exists(AP_DATA_FOLDER.'/custom/custom_data_class.php'))
			{
				require_once(AP_DATA_FOLDER.'/custom/custom_data_class.php');
			}
			
			include $_rp.'/core/custom.php';
			
			$this->_initSettings();
			$this->_init();
		}

		/**
		 * @static
		 */
		function InitConstans()
		{
			$_rp = CAdminPanel::RootPath();
			if (file_exists($_rp.'/core/enc.php'))
			{
				include_once $_rp.'/core/enc.php';
			}
			include_once $_rp.'/core/constans.php';
			include_once $_rp.'/core/addutils.php';
			include_once $_rp.'/core/utils.php';
		}
		
		/**
		 * @static
		 */		
		function InitVersion()
		{
			$_app_version = @file_get_contents(CAdminPanel::RootPath().'/VERSION');
			define('AP_VERSION', (false === $_app_version) ? '0.0.0' : $_app_version);
		}
		
		/**
		 * @return bool
		 */
		function IsInstall()
		{
			return $this->_isInstall;
		}
		
		/**
		 * @param	string	$_fileName
		 */
		function AddCssFile($_fileName)
		{
			$this->_css_files[$_fileName] = $_fileName;
		}
		
		/**
		 * @param	string	$_fileName
		 */
		function AddJsFile($_fileName)
		{
			$this->_js_files[$_fileName] = $_fileName;
		}
		
		/**
		 * @param	string	$_pathName
		 * @return	string|false
		 */
		function GetCfg($_pathName)
		{
			return isset($this->_cfg[$_pathName]) ? $this->_cfg[$_pathName] : false;
		}
		
		/**
		 * @return	string
		 */
		function Mode()
		{
			return $this->_mode;
		}
		
		function Title()
		{
			echo $this->_title;
		}
		
		function TopStyles()
		{
			foreach ($this->_css_files as $_fileName) 
			{
				echo AP_TAB.'<link rel="stylesheet" href="'.ap_Utils::AttributeQuote($_fileName.'?'.$this->ClearAdminVersion()).'" type="text/css" />'.AP_CRLF;
			}
		}
		
		function TopJS()
		{
			foreach ($this->_js_files as $_fileName) 
			{
				echo AP_TAB.'<script type="text/javascript" src="'.ap_Utils::AttributeQuote($_fileName.'?'.$this->ClearAdminVersion()).'"></script>'.AP_CRLF;
			}
		}
		
		/**
		 * @param	string	$_title
		 */
		function SetTitle($_title)
		{
			$this->_title = $_title;
		}
		
		/**
		 * @return string
		 */
		function Tab()
		{
			return $this->_tab;
		}
		
		/**
		 * @return bool
		 */
		function IsAuth()
		{
			return $this->_authDone;
		}
		
		/**
		 * @return array
		 */
		function &Config()
		{
			return $this->_cfg;
		}
		
		/**
		 * @return array
		 */
		function &Indexs()
		{
			return $this->_plugIndexs;
		}
		
		/**
		 * @param	array	$_cfg
		 * @return	true|string
		 */
		function ValidateCfg($_cfg)
		{
			if (!isset($_cfg['adminpanel_data_path']))
			{
				return ap_Utils::TakePhrase('AP_LANG_NOT_ENOUGH_DATA_PATH');
			}
			
			if (!@file_exists($_cfg['adminpanel_data_path'].'/settings/'.AP_XML_CFG_FILE))
			{
				return ap_Utils::TakePhrase('AP_LANG_NOT_ENOUGH_DATA_FILENOTEXIST');
			}
			
			if (!isset($_cfg['plugins']) || !is_array($_cfg['plugins']) || count($_cfg['plugins']) == 0)
			{
				return ap_Utils::TakePhrase('AP_LANG_NOT_ENOUGH_DATA_PLUGINS');
			}

			if (!isset($_cfg['login']['user'], $_cfg['login']['password']))
			{
				return ap_Utils::TakePhrase('AP_LANG_NOT_ENOUGH_DATA_FOR_LOGIN');
			}
	
			return true;
		}

		function SetAdminAccessType($_accessType = AP_SESS_AUTH_TYPE_NONE)
		{
			$_SESSION[AP_SESS_AUTH_TYPE] = (int) $_accessType;
			$this->_authType = (int) $_accessType;
			if (in_array($this->_authType, array(AP_SESS_AUTH_TYPE_SUBADMIN,
				AP_SESS_AUTH_TYPE_SUPER_ADMIN, AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD)))
			{
				$this->_authDone = true;
			}
		}

		/**
		 * @return	int
		 */
		function AuthType()
		{
			return $this->_authType;
		}

		function SetAdminAccessDomains($_domains)
		{
			$_SESSION[AP_SESS_AUTH_DOMAINS] = is_array($_domains) ? $_domains : null;
		}

		/**
		 * @return	array
		 */
		function GetSubAdminDomainsId()
		{
			if ($this->_authType === AP_SESS_AUTH_TYPE_SUBADMIN)
			{
				return isset($_SESSION[AP_SESS_AUTH_DOMAINS]) ? $_SESSION[AP_SESS_AUTH_DOMAINS] : null;
			}
			return null;
		}
		
		function _initAuth()
		{
			global $session_is_start;

			if (isset($_GET['login'], $_POST['AdmloginInput'], $_POST['AdmpasswordInput'])
					&& isset($this->_cfg['login']['user'], $this->_cfg['login']['password']))
			{
				@$this->_disable_magic_quotes_gpc();
				
				$_settings_login = $this->_cfg['login']['user'];
				$_settings_password = $this->_cfg['login']['password'];

				if ($_POST['AdmloginInput'] == $_settings_login && $_POST['AdmpasswordInput'] == $_settings_password)
				{
					$_SESSION[AP_SESS_AUTH] = @session_id();
					$this->SetAdminAccessType(AP_SESS_AUTH_TYPE_SUPER_ADMIN);
					@header('Location: '.AP_INDEX_FILE.'?enter');
					exit();
				}
				else if (strlen(AP_DEMO_LOGIN) > 0 && $_POST['AdmloginInput'] == AP_DEMO_LOGIN)
				{
					$_SESSION[AP_SESS_AUTH] = @session_id();
					$this->SetAdminAccessType(AP_SESS_AUTH_TYPE_SUPER_ADMIN_ONLYREAD);
					@header('Location: '.AP_INDEX_FILE.'?enter');
					exit();
				}
				else if ($this->PType())
				{
					if (is_array($this->_main_all))
					{
						foreach ($this->_main_all as $_plugins)
						{
							if ('cm' === $_plugins->GetIndex())
							{
								$domains = $_plugins->GetSubAdminDomainsIdsByLoginPassword(
												$_POST['AdmloginInput'], $_POST['AdmpasswordInput']);
								if ($domains)
								{
									$_SESSION[AP_SESS_AUTH] = @session_id();
									$this->SetAdminAccessType(AP_SESS_AUTH_TYPE_SUBADMIN);
									$this->SetAdminAccessDomains($domains);
									@header('Location: '.AP_INDEX_FILE.'?enter');
									exit();
								}
							}
						}
					}

					@session_destroy();
					@header('Location: '.AP_INDEX_FILE.'?auth_error');
					exit();
				}
				else
				{
					@session_destroy();
					@header('Location: '.AP_INDEX_FILE.'?auth_error');
					exit();
				}
			}
			else
			{
				if ($session_is_start)
				{
					if (CAdminPanel::IsStaticAuth() && isset($_SESSION[AP_SESS_AUTH_TYPE]))
					{
						$this->SetAdminAccessType((int) $_SESSION[AP_SESS_AUTH_TYPE]);
					}
				}
				else
				{
					$this->_ge(ap_Utils::TakePhrase('AP_LANG_SESSION_ERROR'));
					@session_destroy();
				}
			}
		}

		function MainError()
		{
			if ($this->_mainError() && !$this->_isInstall && @file_exists(CAdminPanel::RootPath().'/install.php'))
			{
				echo AP_CRLF.'<div id="mainIdObj" class="wm_install_del_message"><img src="./images/error.gif" /> Please delete install.php and install.htm files.
<div class="wm_close_info_image wm_control" onclick="closeMainError(\'mainIdObj\')"></div></div>'.AP_CRLF;
			}
		}
		
		/**
		 * @static
		 * @return bool
		 */
		function IsStaticAuth()
		{
			CAdminPanel::InitConstans();
			return (isset($_SESSION[AP_SESS_AUTH]) && $_SESSION[AP_SESS_AUTH] == @session_id());
		}
		
	 	/**
	 	 * @param	string	$_licenseKey
		 * @return	bool
		 */
		function SaveLicenseKeyData($_licenseKey)
		{
			$_path = $this->GetCfg('adminpanel_data_path');
			$_xml = new XmlDocument();
			if($_path !== false && $_xml->LoadFromFile($_path.'/settings/'.AP_XML_CFG_FILE))
			{
				$_license_node =& $_xml->XmlRoot->GetChildNodeByTagName('licensekey');
				if ($_license_node)
				{
					$_license_node->Value =  ap_Utils::EncodeSpecialXmlChars($_licenseKey);
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
			return false;
		}
		
		/**
	 	 * @param	string	$_password
		 * @return	bool
		 */
		function SaveAdminPassword($_password)
		{
			$_path = $this->GetCfg('adminpanel_data_path');
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

					if ($_xml->SaveToFile($_path.'/settings/'.AP_XML_CFG_FILE))
					{
						return true;
					}
				}
			}
			return false;
		}
		
		function ContentClass()
		{
			echo ap_Utils::AttributeQuote($this->_logo);
		}
		
		/**
		 * @return	string
		 */
		function GetLogoType()
		{
			/*$_prod_type = $this->PType() ? 'pro' : 'lite';
			$_sol_type = $this->BType() ? 'wm_content_bundle_' : $_sol_type;
			return $_sol_type.$_prod_type;*/
			return 'wm_content';
		}
		
		function _initSettings()
		{
			$_path = $this->GetCfg('adminpanel_data_path');
			$_xml = new XmlDocument();
			if ($_path === false || !@file_exists($_path.'/settings/'.AP_XML_CFG_FILE))
			{
				$this->_ge(ap_Utils::TakePhrase('AP_LANG_FILE_ERROR_OCCURRED'));
				exit();
			}
			
			if ($_xml->LoadFromFile($_path.'/settings/'.AP_XML_CFG_FILE))
			{
				$_advanced_options =& $_xml->XmlRoot->GetChildNodeByTagName('advancedoptions');
				if ($_advanced_options)
				{
					$this->_cfg['advancedoptions'] = (bool) $_advanced_options->Value;
				}
				
				$_license_node =& $_xml->XmlRoot->GetChildNodeByTagName('licensekey');
				if ($_license_node)
				{
					$this->_cfg[AP_TEST_P] = trim(ap_Utils::DecodeSpecialXmlChars($_license_node->Value));
				}
				
				$_plugins =& $_xml->XmlRoot->GetChildNodeByTagName('plugins');
				if ($_plugins)
				{
					$this->_cfg['plugins'] = array();
					
					if (!$_plugins->Children)
					{
						$_plugins->Children = array();
					}
					
					foreach ($_plugins->Children as $_node)
					{
						if ($_node->TagName == 'plugin')
						{
							$_ipath = CAdminPanel::RootPath().'/plugins/'.trim($_node->Value).'/index.php';
							if (@file_exists($_ipath))
							{
								$this->_cfg['plugins'][] = trim(ap_Utils::DecodeSpecialXmlChars($_node->Value));
							}
						}
					}
					
					if (@file_exists(CAdminPanel::RootPath().'/plugins/common/index.php'))
					{
						$this->_cfg['plugins'][] = 'common';
					}
				}
				
				$_login_node =& $_xml->XmlRoot->GetChildNodeByTagName('login');
				if ($_login_node && $_login_node->Children && is_array($_login_node->Children))
				{
					foreach ($_login_node->Children as $_node)
					{
						$this->_cfg['login'][trim($_node->TagName)] = trim(ap_Utils::DecodeSpecialXmlChars($_node->Value));
					}
				}
				
				$_resp = $this->ValidateCfg($this->_cfg);
				if ($_resp !== true)
				{
					$this->_ge($_resp);
				}
			}
		}
		
		function _init()
		{
			if (isset($_GET['help']))
			{
				if ($this->BType())
				{
					header('Location: http://www.afterlogic.com/wiki/MailSuite_Pro_5_Linux_documentation');
				}
				else
				{
					header('Location: http://www.afterlogic.com/wiki/WebMail_Pro_5_PHP_documentation');
				}

				exit();
			}

			if (isset($_GET['archiving']) && $this->PType() && false)
			{
				header('Location: '.$this->AdminFolder().'/archiving/');
				exit();
			}
			
			if ($this->_isInstall)
			{
				$_SESSION[AP_SESS_TAB] = 'install';
			}
			else if (isset($_GET['tab']) && strlen($_GET['tab']) > 0)
			{
				$_SESSION[AP_SESS_TAB] = $_GET['tab'];
				
				unset(
				//	$_SESSION[AP_SESS_MODE],
					$_SESSION[AP_SESS_SEARCHDESC],
					$_SESSION[AP_SESS_PAGE],
					$_SESSION[AP_SESS_COLUMN]
				);
			}
			
			if (isset($_GET['reset_search']))
			{
				unset($_SESSION[AP_SESS_SEARCHDESC]);
			}
			
			if (isset($_GET['mode']) && strlen($_GET['mode']) > 0 && $_GET['mode'] !== 'submit' && $_GET['mode'] !== 'pop')
			{
				$_SESSION[AP_SESS_MODE] = $_GET['mode'];
			}
			
			if (isset($_GET['change_mode']) && strlen($_GET['change_mode']) > 0)
			{
				$_SESSION[AP_SESS_MODE] = $_GET['change_mode'];
				exit();
			}
			
			$this->_mode = isset($_SESSION[AP_SESS_MODE]) ? $_SESSION[AP_SESS_MODE] : 'default';
			if (isset($_GET['mode']) && strlen($_GET['mode']) > 0)
			{
				if ($_GET['mode'] === 'submit')
				{
					$this->_mode = 'submit';
				}
				else if ($_GET['mode'] === 'pop')
				{
					$this->_mode = 'pop';
				}
			}
			
			$this->_tab = isset($_SESSION[AP_SESS_TAB]) ? $_SESSION[AP_SESS_TAB] : 'default';
			
			$this->_initPluginsInfo();
			$this->_initMainAll();

			$this->_initAuth();
			$this->_initPluginsTabs();
			
			$this->_initMain();
		}
		
		function _initPath()
		{
			$_result = true;
			if (@file_exists(CAdminPanel::RootPath().'/'.AP_CFG_FILE))
			{
				$settings_path = null;
				include CAdminPanel::RootPath().'/'.AP_CFG_FILE;
				if (isset($settings_path))
				{
					if (is_array($settings_path) && count($settings_path) > 0)
					{
						foreach ($settings_path as $_name => $_path)
						{
							if (is_string($_path))
							{
								$this->_cfg[$_name.'_path'] = ap_AddUtils::GetFullPath($_path, CAdminPanel::RootPath());
							}
						}
						
						$dataPath = null;
						if (isset($this->_cfg['webmail_web_path']))
						{
							if (@file_exists($this->_cfg['webmail_web_path'].'/inc_settings_path.php'))
							{
								include $this->_cfg['webmail_web_path'].'/inc_settings_path.php';
							}
							
							if (isset($dataPath) && $dataPath !== null)
							{
								$dataPath = ap_AddUtils::GetFullPath($dataPath, $this->_cfg['webmail_web_path']);
								if (!isset($this->_cfg['adminpanel_data_path']))
								{
									$this->_cfg['adminpanel_data_path'] = $dataPath;
								}
								$this->_cfg['webmail_data_path'] = $dataPath;
							}
						}
						
						if (isset($this->_cfg['adminpanel_data_path']))
						{
							define('AP_DATA_FOLDER', $this->_cfg['adminpanel_data_path']);
						}
						unset($dataPath);
					}
				}
				unset($settings_path);
			}
			else
			{
				$_result = false;
			}
			
			if (!$_result)
			{
				$this->_ge();
			}
		}
		
		function _initPluginsInfo()
		{
			$_rp = CAdminPanel::RootPath();
			if (isset($this->_cfg['plugins']))
			{
				if (is_array($this->_cfg['plugins']))
				{
					foreach ($this->_cfg['plugins'] as $_name)
					{
						$_path = $_rp.'/plugins/'.$_name.'/index.php';
						if (@file_exists($_path))
						{
							$pl_info = null;
							include	$_path;

							if ($this->_isInstall)
							{
								if ($pl_info->HasInstall)
								{
									$this->RegPlugin($pl_info);
									break;
								}
							}
							else
							{
								$this->RegPlugin($pl_info);
							}
							
							unset($pl_info);
						}
					}
				}
			}
			else
			{
				$this->_ge(ap_Utils::TakePhrase('AP_LANG_FILE_ERROR_OCCURRED'));
			}
		}
		
		function _initPluginsTabs()
		{
			$_isGood = $this->_isGoodOl();
			$_array = array();
			foreach ($this->_pluginsInfo as $_pi)
			{
				if (is_array($_pi->Tabs))
				{
					foreach ($_pi->Tabs as $_tab)
					{
						if (is_array($_tab))
						{
							if ($this->_chePluginsTabs($_isGood, $_tab))
							{
								if (isset($_array[$_tab[2]][0]) && is_array($_array[$_tab[2]][0]))
								{
									$_array[$_tab[2]][0] = $_tab[0];
									$_array[$_tab[2]][1][] = $_tab[1];
								}
								elseif (isset($_array[$_tab[2]][0]))
								{
									$_array[$_tab[2]][0] = $_tab[0];
									$_array[$_tab[2]][1] = array($_array[$_tab[2]][1], $_tab[1]);
								}
								else
								{
									$_array[$_tab[2]][0] = $_tab[0];
									$_array[$_tab[2]][1] = $_tab[1];
								}
							}
						}
					}
				}
			}

			$_out = null;
			if ($this->_authType === AP_SESS_AUTH_TYPE_SUBADMIN && is_array($_array))
			{
				$_out = array();
				foreach ($_array as $_key => $_tab)
				{
					if (in_array($_key, array('users', 'domains')))
					{
						$_out[$_key] = $_tab;
					}
				}
			}
			
			$this->_pluginsTabs = (null === $_out) ? $_array : $_out;
		}

		function _initMainAll()
		{
			foreach ($this->_pluginsInfo as $_info)
			{
				if (@file_exists($_info->Path.'/plugin.php'))
				{
					include $_info->Path.'/plugin.php';
					$_plugin = new $_info->ClassName;
					$_resp = $_plugin->ValidateCfg($this->_cfg);
					if ($_resp === true)
					{
						$_plugin->Init($this, $_info, $_pluginIndexs);
						$_plugin->IncludeCommonOnce();
						
						$_pluginIndexs[] = $_info->Index;
						$this->_main_all[] =& $_plugin;
					}
					else
					{
						$this->_ge($_resp);
					}

					unset($_plugin, $_info);
				}
			}
		}
		
		function _initMain()
		{
			$this->_tab = $this->_getRealTab($this->_tab);

			$_pluginIndexs = array();

			foreach ($this->_main_all as $_plugin)
			{
				$_tabs =& $_plugin->GetTabs();
				foreach ($_tabs as $_tab)
				{
					if (($_plugin->HasInstall && $this->IsInstall()) || (count($_tab) > 2 && $_tab[2] == $this->_tab))
					{
						$this->_main[] =& $_plugin;
						break;
					}
				}
				
				unset($_plugin);
			}
		}
		
		/**
		 * @param string $_action
		 * @param array $_arg 
		 */
		function GlobalFunction($_action, &$_arg)
		{
			$_return = true;
			if (count($this->_main_all) > 0)
			{
				foreach ($this->_main_all as $_plugin)
				{
					$_return &= $_plugin->GlobalFunction($_action, $_arg);
				}
			}
			return $_return;
		}
		
		/**
		 * @param	null	$_filter
		 */
		function UpdateFilterList(&$_filter)
		{
			if (!$_filter) 
			{
				return;
			}
			
			$_items =& $_filter->_items;
			$_top_items =& $_filter->_top_items;
			
			$_t1 = $this->_sortFilters($_items);
			$_t2 = $this->_sortFilters($_top_items);
			
			$_filter->_list = array_merge($_t2, $_t1);
			$_filter->_listIsPrepared = true;
		}
		
		/**
		 * @param	array	$_items
		 * @return	array
		 */
		function _sortFilters($_items)
		{
			if (!is_array($_items) || count($_items) == 0)
			{
				return array();
			}
			
			$_tempList = $_sortArray = $_nlist = array();

			foreach ($_items as $_item)
			{
				$_tempList[$_item->name][] = $_item;
			}
			
			foreach ($_tempList as $_value)
			{
				if (count($_value) > 1)
				{
					$_href = '';
					$_firstItem = $_value[0];
					
					$_isUnite = true;
					/*foreach ($_value as $_item)
					{
						if ($_item->type != AP_TYPE_XMAIL)
						{
							$_isUnite = false;
						}
					}*/
					
					if ($_isUnite)
					{
						foreach ($_value as $_item)
						{
							$_href .= AP_UIDS_DELIMITER.$_item->href;
						}
						$_nlist[ltrim($_href, AP_UIDS_DELIMITER)] = array($_firstItem->name, $_firstItem->class);
						$_sortArray[ltrim($_href, AP_UIDS_DELIMITER)] = $_firstItem->name;
					}
					else
					{
						foreach ($_value as $_item)
						{
							$_nlist[$_item->href] = array($_item->name, $_item->class);
							$_sortArray[$_item->href] = $_item->name;
						}
					}
				}
				else
				{
					$_item =& $_value[0];
					$_nlist[$_item->href] = array($_item->name, $_item->class);
					$_sortArray[$_item->href] = $_item->name;
				}
			}
			
			natcasesort($_sortArray);
			
			foreach ($_sortArray as $_key => $_value)
			{
				$_sortArray[$_key] = $_nlist[$_key];
			}	

			return $_sortArray;
		}
		
		/**
		 * @param	ap_Screen_Tables_List	$_table
		 */
		function UpdateTableList(&$_table)
		{
			if (!$_table) 
			{
				return;
			}
			
			$_items =& $_table->_items;
			$_list =& $_table->_list;
			
			$_tempList = array();

			foreach ($_items as $_item)
			{
				$_tempList[$_item->name][] = $_item;
				/*
				if ($_item->type != AP_TYPE_XMAIL_CUSTOM)
				{
					$_tempList[$_item->name][] = $_item;
				}
				else
				{
					$_tempList[AP_TYPE_XMAIL_CUSTOM.'-'.$_item->name.'-'.AP_TYPE_XMAIL_CUSTOM][] = $_item;
				}
				 */
			}
			
			foreach ($_tempList as $_value)
			{
				if (count($_value) > 1)
				{
					$_href = '';
					$_firstItem = $_value[0];
					
					$_isUnite = false;
					/*foreach ($_value as $_item)
					{
						if ($_item->type != AP_TYPE_XMAIL)
						{
							$_isUnite = false;
						}
					}*/
					
					if ($_isUnite)
					{
						foreach ($_value as $_item)
						{
							$_href .= AP_UIDS_DELIMITER.$_item->href;
						}
						$_list[ltrim($_href, AP_UIDS_DELIMITER)] = $_firstItem->values;
					}
					else
					{
						foreach ($_value as $_item)
						{
							$_list[$_item->href] = $_item->values;
						}
					}
				}
				else
				{
					$_item =& $_value[0];
					$_list[$_item->href] = $_item->values;
					unset($_item);
				}
			}
			
			$_table->_listIsPrepared = true;
		}

		function _getRealTab($_tabName)
		{
			if ('install' === $_tabName)
			{
				return $_tabName;
			}
			
			$_returnTab = false;
			foreach ($this->_pluginsTabs as $_name => $_info)
			{
				if (false === $_returnTab)
				{
					$_returnTab = $_name;
				}

				if ($_tabName === $_name)
				{
					return $_tabName;
				}
			}

			return $_returnTab;
		}
		
		function Write()
		{
			if ($this->_useLog)
			{
				$GLOBALS[AP_USELOG] = true;
			}
			
			if (!$this->IsAuth() && !$this->_isInstall)
			{
				if (isset($_GET['auth_error']))
				{
					define('LOGIN_FORM_ERROR_MESS', '<div class="wm_login_error"><div class="wm_login_error_icon"></div><div class="wm_login_error_message" id="login_error_message">'.ap_Utils::TakePhrase('AP_LANG_LOGIN_AUTH_ERROR').'</div></div>');	
				}
				else if (isset($_GET['sess_error']))
				{
					define('LOGIN_FORM_ERROR_MESS', '<div class="wm_login_error"><div class="wm_login_error_icon"></div><div class="wm_login_error_message" id="login_error_message">'.ap_Utils::TakePhrase('AP_LANG_LOGIN_SESS_ERROR').'</div></div>');
				}
				else if (isset($_GET['access_error']))
				{
					define('LOGIN_FORM_ERROR_MESS', '<div class="wm_login_error"><div class="wm_login_error_icon"></div><div class="wm_login_error_message" id="login_error_message">'.ap_Utils::TakePhrase('AP_LANG_LOGIN_ACCESS_ERROR').'</div></div>');
				}
				else
				{
					define('LOGIN_FORM_ERROR_MESS', '');
				}
				
				$this->AddCssFile($this->AdminFolder().'/styles/styles.css');
				
				include  CAdminPanel::RootPath().'/templates/login.php';
			}
			else if ($this->_mode == 'submit' && isset($_POST) && count($_POST) > 0)
			{
				@ob_start();
				@$this->_disable_magic_quotes_gpc();
				
				$_ref = '?root';
				foreach ($this->_main as $_main)
				{
					$_newref = $_main->WriteAction();
					if ($_newref)
					{
						$_ref = $_newref;
					}
					
					if ($this->HasError())
					{
						break;
					}
				}
				if ($this->PType() && isset($GLOBALS[AP_GLOBAL_USERFILTER_HREFS]) && is_array($GLOBALS[AP_GLOBAL_USERFILTER_HREFS]))
				{
					$_ref = '?tab=users&filter='.urlencode(implode(AP_UIDS_DELIMITER, $GLOBALS[AP_GLOBAL_USERFILTER_HREFS]));
				}

				/* if (!$this->_isInstall) { $_SESSION[AP_SESS_MODE] = 'default'; } */
				
				$_error = @ob_get_clean();
				if (strlen($_error) > 0)
				{
					CAdminPanel::Log('PHP < WriteAction()'.AP_CRLF.$_error);
				}

				header('Location: '.AP_INDEX_FILE.$_ref);
			}
			else if ($this->_mode == 'pop')
			{
				foreach ($this->_main as $_main)
				{
					$_main->WritePop();
				}
			}
			else
			{
				$this->AddJsFile($this->AdminFolder().'/js/common.js');
				$this->AddCssFile($this->AdminFolder().'/styles/styles.css');
				$this->_mainRun();

				$this->SetMainObStart();
				@header('Content-type: text/html; charset=utf-8');
				include CAdminPanel::RootPath().'/templates/main.php';
			}
		}

		function SetMainObStart()
		{
			@ob_start(AP_USE_GZIP ? 'obStartGzip' : 'obStartNoGzip');
		}
		
		function TopMenu()
		{
			if (!$this->_isInstall)
			{
				echo '
			<div class="wm_accountslist" id="accountslist">
				';

				$_first = true;
				foreach ($this->_pluginsTabs as $_name => $_tab)
				{
					$_firstAdd = '';
					if (true === $_first)
					{
						$_firstAdd = ' first';
						$_first = false;
					}
					
					$_class = ($this->_tab == $_name)
						? 'wm_accountslist_contacts wm_active_tab' : 'wm_accountslist_contacts';
					$_class .= $_firstAdd;
					
					$_nameTitle = (is_array($_tab[0]) && count($_tab[0]) == 2) ? ($this->PType() ? $_tab[0][1] : $_tab[0][0]) : $_tab[0];
						
					echo '
						<div class="'.$_class.'">
							<a href="'.AP_INDEX_FILE.'?tab='.$_name.'">'.$_nameTitle.'</a>
						</div>';
				}

				if ($this->PType() && false && @is_dir(CAdminPanel::RootPath().'/archiving/'))
				{
					echo '
						<div class="wm_accountslist_contacts">
							<a href="'.AP_INDEX_FILE.'?archiving" target="_blank">Archiving</a>
						</div>';
				}
				
				echo '
						<div class="wm_accountslist_logout">
							<a href="'.AP_INDEX_FILE.'?logout">Logout</a>
						</div>
						<div class="wm_accountslist_logout">
							<a href="'.AP_INDEX_FILE.'?help" target="_blank">Help</a>
						</div>
			</div>
';
			}
			
		}
		
		function _mainRun()
		{
			@ob_start();
			if (count($this->_main) > 0)
			{
				$_main =& $this->_main[0];
				$_screenName = $_main->GetScreenName($this->_tab);
				
				if ($_screenName === false)
				{
					$this->_ge(ap_Utils::TakePhrase('AP_LANG_CANTGETSCREENNAME'));
				}
				
				$screen = new $_screenName;
				$screen->InitByAp($this);
				$screen->InitPlugins($this->_main);
				
				include CAdminPanel::RootPath().'/templates/'.$screen->IncludeTemplateFile();
			}
			else
			{
				include CAdminPanel::RootPath().'/templates/screen-null.php';
			}
			
			$this->_mainContent = @ob_get_clean();
		}
		
		function Main()
		{
			echo $this->_mainContent;
		}
		
		function Info()
		{
			$_info = $this->GetInfo();
			if (strlen($_info) > 0)
			{
				$_type = $this->GetInfoType();
				echo AP_CRLF.'<script type="text/javascript">MsgBox.Show("'.ap_Utils::ReBuildStringToJavaScript($_info, '"').'", '.$_type.');</script>';
				$this->ClearInfo();
			}
		}
		
		function Copyright($force = false)
		{
			if ($force)
			{
				echo '<div class="wm_copyright" id="copyright">';
				include CAdminPanel::RootPath().'/templates/copyright.php';
				echo '</div>';
			}
		}
		
		/**
		 * @return	float
		 */
		function GetTimeFromStart()
		{
			return ap_Utils::Microtime() - $GLOBALS[AP_START_TIME];
		}
		
		/**
		 * @param	ap_PluginInfo	$_pluginInfo
		 */
		function RegPlugin($_pluginInfo)
		{
			if ($_pluginInfo)
			{
				if (!isset($this->_pluginsInfo[$_pluginInfo->UpdateIndex]) || $this->_pluginsInfoLevel[$_pluginInfo->UpdateIndex] < $_pluginInfo->UpdateLevel)
				{
					$this->_pluginsInfo[$_pluginInfo->UpdateIndex] =& $_pluginInfo;
					$this->_pluginsInfoLevel[$_pluginInfo->UpdateIndex] = $_pluginInfo->UpdateLevel;
					$this->_plugIndexs[] = $_pluginInfo->Index;
				}
			}
		}
		
		function _disable_magic_quotes_gpc()
		{
			if (@get_magic_quotes_gpc() == 1)
			{
				$_GET = isset($_GET) ? $this->_fixed_array_map_stripslashes($_GET) : array();
				$_POST = isset($_POST) ? $this->_fixed_array_map_stripslashes($_POST) : array();
			}
		}
		
		/**
		 * @param	array	$_array
		 * @return	array
		 */
		function _fixed_array_map_stripslashes($_array)
		{
			if (is_array($_array))
			{
				foreach ($_array as $_key => $_value)
				{
					$_array[$_key] = (is_array($_value))
							? @$this->_fixed_array_map_stripslashes($_value)
							: @stripslashes($_value);
				}
			}
			return $_array;
		}
		
		/**
		 * @param	string	$_desc
		 * @param	int		$_type
		 */
		function SetInfo($_desc, $_type)
		{
			$_SESSION[AP_SESS_INFORMATION] = $_desc;
			$_SESSION[AP_SESS_INFORMATION_TYPE] = (int) $_type;
		}
		
		function HasError()
		{
			return isset($_SESSION[AP_SESS_INFORMATION], $_SESSION[AP_SESS_INFORMATION_TYPE]) && $_SESSION[AP_SESS_INFORMATION_TYPE] == 2;
		}
		
		/**
		 * @return	string
		 */
		function GetInfo()
		{
			return isset($_SESSION[AP_SESS_INFORMATION]) ? $_SESSION[AP_SESS_INFORMATION] : '';
		}

		/**
		 * @return	string
		 */
		function AdminFolder()
		{
			return AP_FOLDER;
		}
		
		/**
		 * @return string|false
		 */
		function AdminDataFolder()
		{
			return defined('AP_DATA_FOLDER') ? AP_DATA_FOLDER : false;
		}
		
		/**
		 * @return	string
		 */
		function ClearAdminVersion()
		{
			return str_replace('.', '', AP_VERSION);
		}

		/**
		 * @return	int
		 */
		function GetInfoType()
		{
			return isset($_SESSION[AP_SESS_INFORMATION_TYPE]) ? (int) $_SESSION[AP_SESS_INFORMATION_TYPE] : 0;
		}
		
		function ClearInfo()
		{
			unset($_SESSION[AP_SESS_INFORMATION], $_SESSION[AP_SESS_INFORMATION_TYPE]);
		}
		
		/**
		 * @param	string	$_msg
		 */
		function GlobalError($_msg = null)
		{
			$_errorDesc = ap_Utils::TakePhrase('AP_LANG_NOT_CONFIGURED');
			$_errorDesc .= ($_msg !== null) ? '<br /><br />'.$_msg : ''; 
		
			$this->AddCssFile($this->AdminFolder().'/styles/styles.css');
			
			define('ERROR_TEML_DESC', $_errorDesc);
			include CAdminPanel::RootPath().'/templates/error.php';
			exit();
		}
		
		/**
		 * @param string $_msg
		 */
		function _ge($_msg = null)
		{
			$this->GlobalError($_msg);
		}
		
		/**
		 * @return bool
		 */
		function IsEnable()
		{
			return isset($_SESSION[AP_SESS_ENABLE]['on']);
		}
		
		/**
		 * @static
		 * @param string $_str
		 */
		function Log($_str)
		{
			static $_isFirst = true;
			static $_use = null;
			if ($_use === null)
			{
				$_use = isset($GLOBALS[AP_USELOG]);
				if (!$_use)
				{
					$_use = isset($_SESSION[AP_SESS_ENABLE]['log']);
				}
			}

			if ($_use)
			{
				$_date = @date('H:i:s');
				$_path = CAdminPanel::AdminDataFolder();
				if (false === $_path)
				{
					return;
				}
				
				if ($_isFirst)
				{
					$_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
					$_isFirst = false;
					$_post = (isset($_POST) && count($_POST) > 0) ? ' [POST('.count($_POST).')]' : '';
					@error_log("\r\n".'['.$_date.'] ---[start]---> '.$_uri.$_post.AP_CRLF, 3, $_path.'/logs/'.AP_LOG_FILE);
					if (strlen($_post) > 0)
					{
						@error_log('['.$_date.'] POST > ['.implode(', ', array_keys($_POST))."]\r\n", 3, $_path.'/logs/'.AP_LOG_FILE);
					}
				}
				
				$_str = str_replace(AP_CRLF, "\r\n\t", $_str);
				@error_log('['.$_date.'] '.$_str.AP_CRLF, 3, $_path.'/logs/'.AP_LOG_FILE);
			}
		}

		/**
		 * @static
		 * @return	string
		 */
		function RootPath()
		{
			defined('AP_ROOTPATH') || define('AP_ROOTPATH', rtrim(dirname(__FILE__), '/\\'));
			return AP_ROOTPATH;
		}
	}
