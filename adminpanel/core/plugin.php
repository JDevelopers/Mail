<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

class ap_Plugin
{
	/**
	 * @var	CAdminPanel
	 */
	var $_ap;
	
	/**
	 * @var	ap_PluginInfo
	 */
	var $_pluginInfo;
	
	/**
	 * @var	array
	 */
	var $_pluginIndexs;
	
	/**
	 * @var	obj
	 */
	var $_screen;
	
	/**
	 * @var	string
	 */
	var $_searchDesc = '';
	
	/**
	 * @var	bool
	 */
	var $HasInstall = false;
	
	/**
	 * @return	ap_Plugin
	 */
	function ap_Plugin()
	{
		if (isset($_POST['searchdesc']))
		{
			$_SESSION[AP_SESS_SEARCHDESC] = $_POST['searchdesc'];
		}
		$this->_searchDesc = isset($_SESSION[AP_SESS_SEARCHDESC]) ? $_SESSION[AP_SESS_SEARCHDESC] : '';
	}
	
	function InitScreen() {	return true; }
	
	/**
	 * @param	string	$action
	 * @param	array	$arg 
	 */
	function GlobalFunction() { return true; }

	/**
	 * @param	array	$cfg
	 * @return	bool
	 */
	function ValidateCfg() { return true; }
	
	/**
	 * @return	array
	 */
	function &GetTabs()
	{
		return $this->_pluginInfo->Tabs;
	}

	/**
	 * @return	string
	 */
	function GetIndex()
	{
		return $this->_pluginInfo->Index;
	}
	
	/**
	 * @param	CAdminPanel		$ap
	 * @param	ap_PluginInfo	$pluginInfo
	 */
	function Init(&$ap, $pluginInfo, &$pluginIndexs)
	{
		$this->_ap =& $ap;
		$this->_pluginInfo = $pluginInfo;
		$this->_pluginIndexs =& $pluginIndexs;
	}
	
	function IncludeCommon() {}
	
	function IncludeCommonOnce()
	{
		static $_mem = false;
		if (!$_mem)
		{
			$this->IncludeCommon();
			$_mem = true;
		}
	}
	
	function WriteAction() { echo 'plugin->WriteAction()'.AP_HTML_BR.AP_CRLF; exit(); }
	
	function WriteMain() { echo 'plugin->WriteMain()'.AP_HTML_BR.AP_CRLF; exit(); }
	
	function WritePop() { echo 'plugin->WriteMain()'.AP_HTML_BR.AP_CRLF; exit();  }
	
	function GetScreenName() { echo 'plugin->GetScreenName()'.AP_HTML_BR.AP_CRLF; exit();  }
	
	/**
	 * @param	string	$desc
	 */
	function _setInfo($desc)
	{
		$this->_ap->SetInfo($desc, 1);
	}

	/**
	 * @param	string	$desc
	 */
	function _setInfoAsError($desc)
	{
		$this->_ap->SetInfo($desc, 2);
	}

	/**
	 * @param	string	$desc
	 */
	function _setError($desc)
	{
		$this->_ap->SetInfo($desc, 2);
	}
	
	/**
	 * @return	string
	 */
	function PluginPath()
	{
		return $this->_pluginInfo->Path;
	}
	
	/**
	 * @return	string
	 */
	function PluginName()
	{
		return basename($this->_pluginInfo->Path);
	}
	
	/**
	 * @return CAdminPanel
	 */
	function &GetAp()
	{
		return $this->_ap;
	}
}

class ap_PluginInfo
{
	/**
	 * @var	string
	 */
	var $Name;
	
	/**
	 * @var	string
	 */
	var $Index;
	
	/**
	 * @var	string
	 */
	var $ClassName;

	/**
	 * @var	array
	 */
	var $Tabs = array();
	
	/**
	 * @var	string
	 */
	var $Version = '0.0.0';
	
	/**
	 * @var	string
	 */
	var $Path;
	
	/**
	 * @var	string
	 */
	var $UpdateIndex;
	
	/**
	 * @var int
	 */
	var $UpdateLevel = 1;
	
	/**
	 * @var	bool
	 */
	var $HasInstall = false;

	/**
	 * @var	array
	 */
	var $HTabs = array();

	function InitBySelf()
	{
		if ('wm' == $this->Index && !CAdminPanel::UseDb() && count($this->Tabs[0]) > 0)
		{
			$this->Tabs = array($this->Tabs[0]);
		}

		if (!CAdminPanel::PType())
		{
			$newTabs = array();
			foreach ($this->Tabs as $TabItem)
			{
				if (isset($TabItem[2]) && in_array($TabItem[2], $this->HTabs))
				{
					continue;
				}
				$newTabs[] = $TabItem;
			}
			$this->Tabs = $newTabs;
		}
	}
}
