<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	class ap_Screen
	{
		/**
		 * @var	string
		 */
		var $_mode;
		
		/**
		 * @var CAdminPanel
		 */
		var $_ap;
		
		/**
		 * @var	string
		 */
		var $_js_text = '';
		
		/**
		 * @var	string
		 */
		var $_js_init_text = '';
		
		/**
		 * @var	ap_Screen_Data
		 */
		var $data;
		
		/**
		 * @var	string
		 */
		var $_search = '';
		
		/**
		 * @var	string
		 */
		var $_null_message = '';
		
		/**
		 * @var string
		 */
		var $_def_mode = '';
		
		/**
		 * @var	string
		 */
		var $_rootPath;
		
		/**
		 * @return ap_Screen
		 */
		function ap_Screen()
		{
			$this->data = new ap_Screen_Data();
		}
		
		/**
		 * @param CAdminPanel $ap
		 */
		function InitByAp(&$ap)
		{
			$this->_ap =& $ap;
			$this->_mode = $ap->Mode();
		}
		
		/**
		 * @param	string	$jsString
		 */
		function AddJsText($jsString)
		{
			$this->_js_text .= $jsString;
		}
		
		/**
		 * @param	string	$jsString
		 */
		function AddJsInitText($jsString)
		{
			$this->_js_init_text .= $jsString;
		}

		/**
		 * @param	string	$mode
		 */
		function SetDefaultMode($mode)
		{
			if ($this->_mode === '' || $this->_mode == 'default')
			{
				$this->_mode = $mode;
			}
			$this->_def_mode = $mode;
		}
		
		/**
		 * @param	string	$text
		 */
		function SetSearchDesc($text)
		{
			$this->_search = $text;
		}
		
		/**
		 * @return	string
		 */
		function GetSearchDesc()
		{
			return $this->_search;
		}
		
		/**
		 * @return	string
		 */
		function GetSearchFullDesc()
		{
			return (strlen($this->_search) > 0) ? '<br />Search results for: "<b>'.$this->_search.'</b>"
<br /><a href="'.AP_INDEX_FILE.'?reset_search">Reset search</a>' : '';
		}
		
		/**
		 * @param	string	$str
		 */
		function SetNullPhrase($str)
		{
			if (strlen($this->_null_message) == 0)
			{
				$this->_null_message = $str;
			}
		}
		
		/**
		 * @param	string	$_path
		 */
		function SetRootPath($_path)
		{
			$this->_rootPath = $_path;
		}

		/**
		 * @param	array	$plugins
		 */
		function InitPlugins() { return true; }
		
		/**
		 * @return	string
		 */
		function IncludeTemplateFile() { return 'screen-null.php'; }
		
		function WriteInitJS()
		{
			if (strlen($this->_js_init_text) > 0)
			{
				echo AP_CRLF.'<script type="text/javascript">'.AP_CRLF.'function GlobalInit() {'.
						AP_CRLF.$this->_js_init_text.AP_CRLF.
						'};</script>'.AP_CRLF;
			}
			
		}
		
		/**
		 * @return	CAdminPanel
		 */
		function &GetAp()
		{
			return $this->_ap;
		}
	}
	
	class ap_Screen_Tables_Main_Switcher
	{
		/**
		 * @var	string
		 */
		var $_id;
		
		/**
		 * @var	string
		 */
		var $_name;
		
		/**
		 * @var	string
		 */
		var $_template;
		
		function ap_Screen_Tables_Main_Switcher($id, $name, $template = null)
		{
			$this->_id = $id;
			$this->_name = $name;
			$this->_template = $template;
			$this->data = new ap_Screen_Data();
		}
		
		function WriteSwitcherHead($type)
		{
			switch ($type)
			{
				case AP_SCR_SWITCHER_TYPE_SELECT:
					echo '<option value="'.ap_Utils::AttributeQuote($this->_id).'">'.$this->_name.'</option>';
					break;
				case AP_SCR_SWITCHER_TYPE_TABS:
					echo $this->_name.'<br/>';
					break;
			}
		}
		
		function OneSwitcherHidden()
		{
			echo '<input type="hidden" name="switchElement" value="'.ap_Utils::AttributeQuote($this->_id).'" />';
		}
		
		function WriteSwitcherBody()
		{
			echo '<div id="switcher_'.ap_Utils::AttributeQuote($this->_id).'" style="width: 100%">';
			if (strlen($this->_template) > 0 && @file_exists($this->_template))
			{
				include $this->_template;
			}
			echo '</div>';
		}
	}
	
	class ap_Screen_Tables_Main extends ap_Screen
	{
		/**
		 * @var	array
		 */
		var $_general_data = array();
		
		/**
		 * @var	array
		 */
		var $_switchers = array();
		
		/**
		 * @var	int
		 * @example:
		 * 		select:		AP_SCR_SWITCHER_TYPE_SELECT
		 * 		advanced:	AP_SCR_SWITCHER_TYPE_ADVANCED
		 */
		var $_switcherType = 0;
		
		function WriteSwitcherHeadTop($type)
		{
			echo '<script type="text/javascript">'.AP_CRLF;
			foreach ($this->_switchers as $_sw)
			{
				echo 'SwitchAdd("'.$_sw->_id.'");'.AP_CRLF;
			}
			echo '</script>';
			
			switch ($type)
			{
				case AP_SCR_SWITCHER_TYPE_SELECT:
					echo '<table width="400" border="0" cellspacing="0" cellpadding="0" class="wm_edit_table">
<tr><td width="24px"></td><td><br /><select name="switchElement" id="switchElement" class="wm_input" onchange="SwitchMain(this);">';
					break;
				case AP_SCR_SWITCHER_TYPE_TABS:
					echo '<br />';
					break;
			}
		}
		
		function WriteSwitcherHeadFoot($type)
		{
			switch ($type)
			{
				case AP_SCR_SWITCHER_TYPE_SELECT:
					echo '</select><td></tr></table>';
					break;
				case AP_SCR_SWITCHER_TYPE_TABS:
					echo '555<br />';
					break;
			}
		}
		
		function WriteString()
		{
			if (count($this->_switchers) > 0)
			{ 
				foreach ($this->_general_data as $html)
				{
					echo $html.AP_CRLF;
				}
	
				if (count($this->_switchers) == 1)
				{
					foreach ($this->_switchers as $sw)
					{
						$sw->OneSwitcherHidden();
						$sw->WriteSwitcherBody();
						break;
					}
				}
				/*else if (count($this->_switchers) > 1)
				{
					$this->WriteSwitcherHeadTop($this->_switcherType);
					foreach ($this->_switchers as $sw)
					{
						$sw->WriteSwitcherHead($this->_switcherType);
					}
					$this->WriteSwitcherHeadFoot($this->_switcherType);
					
					foreach ($this->_switchers as $sw)
					{
						$sw->WriteSwitcherBody();
					}
					echo '<script type="text/javascript">SwitchInit("switchElement");</script>';
				}*/
			}
		}
		
		function AddGeneralForms($name, $html)
		{
			$this->_general_data[$name] = $html;
		}
		
		function AddSwitcher($id, $name, $template = null)
		{
			$data = null;
			if (isset($this->_switchers[$id]))
			{
				$sw =& $this->_switchers[$id];
				$data =& $sw->data;
			}
			
			$this->_switchers[$id] = new ap_Screen_Tables_Main_Switcher($id, $name, $template);
			if ($data !== null)
			{
				$nsw =& $this->_switchers[$id];
				$nsw->data =& $data;
			}
			unset($data);
		}
		
		/**
		 * @param	string	$id
		 * @return	ap_Screen_Tables_Main_Switcher
		 */
		function &GetSwitcher($id)
		{
			$return = null;
			if (isset($this->_switchers[$id]))
			{
				$return =& $this->_switchers[$id];
			}
			return $return;
		}
		
		/**
		 * @param	int	$type
		 */
		function SetSwitcherType($type)
		{
			$this->_switcherType = $type;
		}
	}

	class ap_Screen_Tables extends ap_Screen
	{
		/**
		 * @var	array
		 */
		var $_topmenu = array();
		
		/**
		 * @var	array
		 */
		var $_topmenu_back = array();
		
		var $_infopanel;
		
		var $_table;
		
		/**
		 * @var	ap_Screen_Tables_Filter
		 */
		var $_filter;
		
		/**
		 * @var	ap_Screen_Tables_Main
		 */
		var $_main;
		
		/**
		 * @var	string
		 */
		var $_lowToolBarText = '';
		
		function ap_Screen_Tables()
		{
			ap_Screen::ap_Screen();
			$this->_main = new ap_Screen_Tables_Main();
		}
		
		/**
		 * @return int
		 */
		function FilterCount()
		{
			return ($this->_filter) ? $this->_filter->Count() : null;
		}
		
		/**
		 * @param	array	$plugins
		 * @return	bool
		 */
		function InitPlugins(&$plugins)
		{
			$result = true;
			
			foreach ($plugins as $plugin)
			{
				if ($result)
				{
					$result = $plugin->InitScreen($this, 'initMenu');
				}
			}
		
			foreach ($plugins as $plugin)
			{
				if ($result)
				{
					$result = $plugin->InitScreen($this, 'initSearch');
				}
			}
			
			foreach ($plugins as $plugin)
			{
				if ($result)
				{
					$result = $plugin->InitScreen($this, 'initFilter');
				}
			}
			
			$this->_ap->UpdateFilterList($this->_filter);
			
			foreach ($plugins as $plugin)
			{
				if ($result)
				{
					$result = $plugin->InitScreen($this, 'initTable');
				}
			}
			
			$this->_ap->UpdateTableList($this->_table);
			
			foreach ($plugins as $plugin)
			{
				if ($result)
				{
					$result = $plugin->InitScreen($this, 'initMain');
				}
			}
			
			return $result;
		}
		
		/**
		 * @param 	bool	$value
		 */
		function UseSort($value = true)
		{
			$this->_table->UseSort($value);
		}
		
		function SetLowToolBarText($text)
		{
			$this->_lowToolBarText = $text;
		}

		/**
		 * @param	string	$str
		 */
		function SetSearchDesc($text)
		{
			if (strlen($text) > 0)
			{
				$this->_search = $text;
				$this->SetNullPhrase(ap_Utils::TakePhrase('AP_LANG_RESULTEMPTY'));
			}
		}
		
		function WriteTopMenu()
		{
			$this->_ap->AddJsFile($this->_ap->AdminFolder().'/js/screen-tables.js');
			
			echo '
<table class="wm_toolbar" id="toolbar">
	<tr>
		<td>';
			if (count($this->_topmenu) > 0)
			{
				foreach ($this->_topmenu as $item)
				{
					echo $item->ToString().AP_CRLF;
				}
			}
			if (count($this->_topmenu_back) > 0)
			{
				foreach ($this->_topmenu_back as $item)
				{
					echo $item->ToString().AP_CRLF;
				}
			}
			echo '
		</td>
	</tr>
</table>';
			
		}
		
		function WriteLowToolBar()
		{
			echo $this->_lowToolBarText;
		}
		
		function WriteFilter()
		{
			if ($this->_filter)
			{
				echo $this->_filter->ToString();
			}
		}
		
		function WriteMainText()
		{
			$this->_main->WriteString();
		}
		
		function WriteMode()
		{
			echo ap_Utils::AttributeQuote($this->_mode);
		}
		
		function WriteTable()
		{
			if ($this->_table)
			{
				$this->_table->SetLightStr($this->GetSearchDesc());
				echo $this->_table->ToString();
			}
		}
		
		function WriteTableJS()
		{
			if ($this->_table)
			{
				echo $this->_table->ToJS();
			}
		}
		
		function WriteCard()
		{
			if (($this->_mode == 'new' || $this->_mode == 'edit') && count($this->_main->_switchers) > 0)
			{
				include CAdminPanel::RootPath().'/templates/screen-tables-card.php';
			}
		}
		
		/**
		 * @param	string	$name
		 * @param	string	$img
		 * @param	string	$js
		 * @param	string	$title[optional] = null
		 */
		function AddTopMenuButton($name, $img, $js, $title = null, $isBack = false)
		{
			if (!isset($this->_topmenu[$js]) && !isset($this->_topmenu_back[$js]))
			{
				if ($isBack)
				{
					$this->_topmenu_back[$js] = new ap_Screen_Tables_MenuButton($this->_ap->AdminFolder(), $name, $img, $js, $title);
				}
				else
				{
					$this->_topmenu[$js] = new ap_Screen_Tables_MenuButton($this->_ap->AdminFolder(), $name, $img, $js, $title);
				}
			}
		}
		
		/**
		 * @param	array	$buttons
		 */
		function AddTopMenuExtendedButton($name, $screen, $buttons, $isBack = false)
		{
			$js = 'alert("'.$name.'");';
			if (!isset($this->_topmenu[$js]) && !isset($this->_topmenu_back[$js]))
			{
				if ($isBack)
				{
					$this->_topmenu_back[$js] = new ap_Screen_Tables_MenuExtendedButton($this->_ap->AdminFolder(), $name, $screen, $buttons);
				}
				else
				{
					$this->_topmenu[$js] = new ap_Screen_Tables_MenuExtendedButton($this->_ap->AdminFolder(), $name, $screen, $buttons);
				}
			}
		}

		/**
		 * @param	string	$js
		 */
		function DeleteTopMenuButton($js)
		{
			if (isset($this->_topmenu[$js]))
			{
				unset($this->_topmenu[$js]);
			}
			
			if (isset($this->_topmenu_back[$js]))
			{
				unset($this->_topmenu_back[$js]);
			}
		}
		
		function InitTable()
		{
			if (!$this->_table)
			{
				$this->_table = new ap_Screen_Tables_List($this->_ap->AdminFolder());
				$this->_table->_null_message = $this->_null_message;
			}
		}
		
		function InitFilter()
		{
			if (!$this->_filter)
			{
				$this->_filter = new ap_Screen_Tables_Filter('Domains');
			}
		}

		function AddHeader($name, $size, $orderField = false)
		{
			$this->_table->AddHeader($name, $size, $orderField);
		}
		
		function ClearHeaders()
		{
			$this->_table->ClearHeaders();
		}
		
		/**
		 * @return string
		 */
		function GetSelectedItemKey()
		{
			return ($this->_filter) ? $this->_filter->GetSelectedItemKey() : '';
		}
		
		/**
		 * @return	string
		 */
		function IncludeTemplateFile()
		{
			return 'screen-tables.php';
		}
	}
	
	class ap_Screen_Install extends ap_Screen_Standard
	{
		function ap_Screen_Install()
		{
			ap_Screen_Standard::ap_Screen_Standard();
		}
		
		function WriteMenu()
		{
			$useMode = false;
			
			foreach ($this->_menu as $item)
			{
				if ($this->_mode == $item->_mode)
				{
					$useMode = true;
				}
			}
			
			if (!$useMode && strlen($this->_def_mode) > 0)
			{
				$this->_mode = $this->_def_mode;
			}
			
			$key = isset($_SESSION['licensekeysession']) ? $_SESSION['licensekeysession'] : '';
			$src = ($this->data->GetValueAsBool('isPro')) ? 'http://afterlogic.com/img/wmp-php-install-logo.png' : 'http://afterlogic.com/img/wml-php-install-logo.png';
			$src .= (strlen($key) > 0) ? '?key='.$key.'&' : '?';
			$src .= 'step='.($this->GetScreenStep() + 1).'&rnd='.rand(10000, 99999);
			if ($this->data->GetValueAsBool('isPro') && @file_exists('CS'))
			{
				$src = 'images/wmp-php-install-logo.png';
			}
			
			echo '
					<div>
						<img style="width:100px; height:75px; margin: 0px 0px 0px 15px;" src="'.$src.'" />
					</div>';
			
			foreach ($this->_menu as $item)
			{
				$img = '';
				$class = ($this->_mode == $item->_mode) ? 'wm_selected_install_item' : 'wm_install_item';
				$class = ($item->_hide) ? 'wm_hide' : $class;
				
				if ($item->_grey)
				{
					echo '
					<div class="wm_install_item_noactiv">
						<nobr><b>'.$item->_name.'</b></nobr>
					</div>';
				}
				else 
				{
					if ($this->_mode == $item->_mode)
					{
						echo '
					<div class="'.$class.'" id="'.$item->_mode.'_div">
						<nobr><b>'.$item->_name.'</b></nobr>
					</div>';
					}
					else 
					{
						if (null === $item->_link)
						{
							echo '
					<div class="'.$class.'" id="'.$item->_mode.'_div">
						<nobr><a href="'.ap_Utils::AttributeQuote(AP_INDEX_FILE.'?mode='.$item->_mode).'"><b>'.$item->_name.'</b></a>
						'.$img.'
						</nobr>
					</div>';
						}
						else 
						{
							echo '
					<div class="'.$class.'" id="'.$item->_mode.'_div">
						<nobr><a href="'.ap_Utils::AttributeQuote($item->_link).'"><b>'.$item->_name.'</b></a>
						'.$img.'
						</nobr>
					</div>';
						}
					}
				}
			}
		}
		
		function Main()
		{
			$arrModes = array_keys($this->_menu);
			foreach ($arrModes as $mode)
			{
				if ($this->_mode == $mode && isset($this->_templates[$mode]) && 
						@file_exists($this->_rootPath.'/templates/'.$this->_templates[$mode]))
				{

					if (isset($this->_js[$mode]) && count($this->_js[$mode]) > 0)
					{
						foreach ($this->_js[$mode] as $jsFileName)
						{
							echo '<script type="text/javascript" src="'.$this->_ap->AdminFolder().'/plugins/'.$this->_pluginFolderName.'/js/'.$jsFileName.'?'.$this->_ap->ClearAdminVersion().'"></script>'.AP_CRLF;
						}
					}
					
					$this->data->SetValue('inputMode', $mode);
					include $this->_rootPath.'/templates/'.$this->_templates[$mode];
					echo AP_CRLF;
				}
			}
		}
		
		/**
		 * @return	string
		 */
		function IncludeTemplateFile()
		{
			return 'screen-install.php';
		}
		
	}
	
	class ap_Screen_InfoTab extends ap_Screen
	{
		/**
		 * @var	string
		 */
		var $_pluginFolderName;
		
		/**
		 * @var	string
		 */
		var $_template;
		
		/**
		 * @var	array
		 */
		var $_js = array();
		
		function ap_Screen_InfoTab()
		{
			ap_Screen::ap_Screen();
		}
		
		/**
		 * @param	array	$plugins
		 */
		function InitPlugins(&$plugins)
		{
			$result = true;
			
			foreach ($plugins as $plugin)
			{
				if ($result)
				{
					$result = $plugin->InitScreen($this, 'initRootPath');
					$this->_pluginFolderName = @basename($this->_rootPath);
				}
			}
			
			foreach ($plugins as $plugin)
			{
				if ($result)
				{
					$result = $plugin->InitScreen($this, 'initTemplate');
				}
			}
			
			foreach ($plugins as $plugin)
			{
				if ($result)
				{
					$result = $plugin->InitScreen($this, 'initInfoData');
				}
			}
			
			return $result;
		}
		
		/**
		 * @return	string
		 */
		function GetPluginFolderName()
		{
			return $this->_pluginFolderName;
		}
		
		function WriteJS()
		{
			if (strlen($this->_js_text) > 0)
			{
				echo AP_CRLF.'<script type="text/javascript">'.AP_CRLF.AP_TAB.$this->_js_text.'</script>'.AP_CRLF;
			}
		}
		
		function Main()
		{
			if (strlen($this->_template) > 0 &&  
					@file_exists($this->_rootPath.'/templates/'.$this->_template))
			{
				include $this->_rootPath.'/templates/'.$this->_template;
			}
		}
		
		/**
		 * @return	string
		 */
		function IncludeTemplateFile()
		{
			return 'screen-info.php';
		}
	}
	
	class ap_Screen_Standard extends ap_Screen
	{
		/**
		 * @var	array
		 */
		var $_menu = array();
		
		/**
		 * @var	string
		 */
		var $_pluginFolderName;
		
		/**
		 * @var	array
		 */
		var $_templates = array();
		
		/**
		 * @var	array
		 */
		var $_js = array();

		/**
		 * @var	ap_Screen_Standard_Filters
		 */
		var $_filter;
		
		/**
		 * @var int
		 */
		var $_screenStep;
		
		/**
		 * @return ap_Screen_Standard
		 */
		function ap_Screen_Standard()
		{
			ap_Screen::ap_Screen();
		}
		
		/**
		 * @param	array	$plugins
		 */
		function InitPlugins(&$plugins)
		{
			$result = true;
			
			foreach ($plugins as $plugin)
			{
				if ($result)
				{
					$result = $plugin->InitScreen($this, 'initRootPath');
					$this->_pluginFolderName = @basename($this->_rootPath);
				}
			}
			
			foreach ($plugins as $plugin)
			{
				if ($result)
				{
					$result = $plugin->InitScreen($this, 'initMenu');
				}
			}
			
			foreach ($plugins as $plugin)
			{
				if ($result)
				{
					$result = $plugin->InitScreen($this, 'initStandardData');
				}
			}
			
			return $result;
		}

		function InitFilter()
		{
			if (!$this->_filter)
			{
				$this->_filter = new ap_Screen_Standard_Filters('Select domain');
			}
		}

		function WriteFilter()
		{
			if ($this->_filter)
			{
				echo $this->_filter->ToString();
			}
		}
		
		function WriteMenu()
		{
			$useMode = false;
			
			foreach ($this->_menu as $item)
			{
				if ($this->_mode == $item->_mode)
				{
					$useMode = true;
				}
			}
			
			if (!$useMode && strlen($this->_def_mode) > 0)
			{
				$this->_mode = $this->_def_mode;
			}
			
			$_af = $this->_ap->AdminFolder();
			
			$this->_ap->AddJsFile($_af.'/js/screen-standart.js');
			
			echo '<div style="width:215px; height:1px; overflow:hidden; padding: 0px"></div>';
			foreach ($this->_menu as $item)
			{
				$class = ($this->_mode == $item->_mode) ? 'wm_selected_settings_item' : 'wm_settings_item';
				$class = ($item->_hide) ? 'wm_hide' : $class;

				if ($item->_separator)
				{
					echo '<br />';
					if (strlen($item->_name) > 0)
					{
						echo '<div class="wm_standart_menu_header">'.$item->_name.'</div>';
					}
				}
				else if ($item->_grey)
				{
					echo '
					<div class="wm_settings_item">
						<nobr><img src="'.$_af.'/images/dot.png" /><b> '.$item->_name.'</b></nobr>
					</div>';
				}
				else 
				{
					echo '
					<div class="'.$class.'" id="'.$item->_mode.'_div">
						<nobr><img src="'.$_af.'/images/dot.png" /> <a href="'.AP_INDEX_FILE.'?mode='.$item->_mode.'">'.$item->_name.'</a></nobr>
					</div>';
				}
			}
			
			$this->AddJsInitText(AP_CRLF.AP_TAB.'TabsController.Init("'.ap_Utils::ReBuildStringToJavaScript($this->_mode, '"').'");'.AP_CRLF);
		}
		
		function WriteJS()
		{
			if (strlen($this->_js_text) > 0)
			{
				echo AP_CRLF.'<script type="text/javascript">'.AP_CRLF.AP_TAB.$this->_js_text.'</script>'.AP_CRLF;
			}
		}
		
		function Main()
		{
			$arrModes = array_keys($this->_menu);
			foreach ($arrModes as $mode)
			{
				if (isset($this->_templates[$mode]) && 
						@file_exists($this->_rootPath.'/templates/'.$this->_templates[$mode]))
				{

					if (isset($this->_js[$mode]) && count($this->_js[$mode]) > 0)
					{
						foreach ($this->_js[$mode] as $jsFileName)
						{
							echo '<script type="text/javascript" src="'.$this->_ap->AdminFolder().'/plugins/'.$this->_pluginFolderName.'/js/'.$jsFileName.'?'.$this->_ap->ClearAdminVersion().'"></script>'.AP_CRLF;
						}
					}
					if ($this->_mode != $mode)
					{
						$this->data->SetValue('hideClass_'.$mode, 'class="wm_hide"');
					}
					
					$this->data->SetValue('inputMode', $mode);
					include $this->_rootPath.'/templates/'.$this->_templates[$mode];
					echo AP_CRLF;
				}
			}
		}
		
		/**
		 * @param	string	$mode
		 * @param	string	$name
		 * @param	string	$templateFile
		 * @param	array	$jsArray = array()
		 * @param	bool	$isHide = false
		 */
		function AddMenuItem($mode, $name, $templateFile, $jsArray = array(), $isHide = false)
		{
			$this->_menu[$mode] = new ap_Screen_Standard_MenuItem($mode, $name, $isHide);
			$this->_templates[$mode] = $templateFile;
			$this->_js[$mode] = $jsArray;
		}

		function AddMenuSeparator($name = '', $separatorKey = null)
		{
			$separatorKey = (null === $separatorKey) ? 'separator_'.count($this->_menu) : $separatorKey;
			$this->_menu[$separatorKey] = new ap_Screen_Standard_MenuSeparator($name, $separatorKey);
		}

		/**
		 * @param	int	$step
		 */
		function SetScreenStep($step)
		{
			$this->_screenStep = $step;
		}
		
		/**
		 * @param	int	$step
		 */
		function GetScreenStep()
		{
			return $this->_screenStep;
		}
		
		/**
		 * @param	string	$mode
		 * @param	string	$name
		 * @param	string	$link
		 */
		function AddMenuAsLink($mode, $name, $link)
		{
			$this->_menu[$mode] = new ap_Screen_Standard_MenuLink($mode, $name, $link);
		}
		
		/**
		 * @param	string	$mode
		 * @param	string	$name
		 */
		function AddGreyMenu($mode, $name)
		{
			$this->_menu[$mode] = new ap_Screen_Standard_MenuGrey($mode, $name);
		}
		
		/**
		 * @param	string	$mode
		 */
		function DeleteMenuItem($mode)
		{
			if (isset($this->_menu[$mode]))
			{
				unset($this->_menu[$mode]);
			}
			if (isset($this->_templates[$mode]))
			{
				unset($this->_templates[$mode]);
			}
			if (isset($this->_js[$mode]))
			{
				unset($this->_js[$mode]);
			}
		}
		
		/**
		 * @param	string	$mode
		 * @param	bool	$hide[options] = false
		 */
		function SetMenuVisibility($mode, $hide = false)
		{
			if (isset($this->_menu[$mode]))
			{
				$item =& $this->_menu[$mode];
				if ($item)
				{
					$item->SetVisibility($hide);
				}
			}
		}
		
		/**
		 * @return	string
		 */
		function IncludeTemplateFile()
		{
			return 'screen-standard.php';
		}
	}

	class ap_Screen_Standard_MenuItem
	{
		/**
		 * @var	string
		 */
		var $_mode;
		
		/**
		 * @var	string
		 */
		var $_name;

		/**
		 * @var	bool
		 */
		var $_hide = false;

		/**
		 * @var	bool
		 */
		var $_grey = false;

		/**
		 * @var	string
		 */
		var $_link = null;

		/**
		 * @var	bool
		 */
		var $_separator = false;
		
		/**
		 * @param	string	$mode
		 * @param	string	$name
		 * @param	bool	$isHide = false
		 * @return	ap_Screen_Standard_MenuItem
		 */
		function ap_Screen_Standard_MenuItem($mode, $name, $isHide = false, $isGrey = false)
		{
			$this->_mode = $mode;
			$this->_name = $name;
			$this->_hide = $isHide;
			$this->_grey = $isGrey;
		}

		function SetVisibility($isHide)
		{
			$this->_hide = $isHide;
		}
	}

	class ap_Screen_Standard_MenuGrey extends ap_Screen_Standard_MenuItem
	{
		/**
		 * @return	ap_Screen_Standard_MenuGrey
		 */
		function ap_Screen_Standard_MenuGrey($mode, $name)
		{
			$this->_mode = $mode;
			$this->_name = $name;
			$this->_hide = false;
			$this->_grey = true;
		}
	}
	
	class ap_Screen_Standard_MenuSeparator extends ap_Screen_Standard_MenuItem
	{
		/**
		 * @return	ap_Screen_Standard_MenuSeparator
		 */
		function ap_Screen_Standard_MenuSeparator($name, $separatorKey)
		{
			$this->_mode = $separatorKey;
			$this->_name = $name;
			$this->_hide = false;
			$this->_grey = false;
			$this->_separator = true;
		}
	}

	class ap_Screen_Standard_MenuLink extends ap_Screen_Standard_MenuItem
	{
		/**
		 * @return	ap_Screen_Standard_MenuLink
		 */
		function ap_Screen_Standard_MenuLink($mode, $name, $link)
		{
			$this->_mode = $mode;
			$this->_name = $name;
			$this->_link = $link;
		}
	}

	class ap_Screen_Standard_Filters
	{
		/**
		 * @var	string
		 */
		var $_name;
		
		/**
		 * @var	array
		 */
		var $_items = array();

		/**
		 * @var	string
		 */
		var $_selectedItem;

		/**
		 * @return	ap_Screen_Tables_Filter
		 */
		function ap_Screen_Standard_Filters($name)
		{
			$this->_name = $name;
			if (isset($_GET['filter_off']) && isset($_SESSION[AP_SESS_STANDARD_FILTER]))
			{
				unset($_SESSION[AP_SESS_STANDARD_FILTER]);
			}
			else if (isset($_GET['filter']) && strlen($_GET['filter']) > 0)
			{
				$_SESSION[AP_SESS_STANDARD_FILTER] = $_GET['filter'];
			}

			if (isset($_SESSION[AP_SESS_STANDARD_FILTER]))
			{
				$this->_selectedItem = $_SESSION[AP_SESS_STANDARD_FILTER];
			}
		}

		/**
		 * @param ap_Screen_Filter_Item $filter
		 */
		function AddItem($filter)
		{
			$this->_items[] = $filter;
		}

		/**
		 * @return	int
		 */
		function Count()
		{
			return count($this->_items);
		}

		/**
		 * @return	string
		 */
		function GetSelectedItemKey()
		{
			$arr = array();
			$listKeys = array_keys($this->_items);
			foreach ($listKeys as $href)
			{
				if ($href == $this->_selectedItem)
				{
					return $this->_selectedItem;
				}
				$arr[] = $href;
			}

			if (count($arr) > 0)
			{
				$this->_selectedItem = $arr[0];
				$_SESSION[AP_SESS_STANDARD_FILTER] = $arr[0];
			}
			else
			{
				$this->_selectedItem = '';
				if (isset($_SESSION[AP_SESS_STANDARD_FILTER]))
				{
					unset($_SESSION[AP_SESS_STANDARD_FILTER]);
				}
			}

			return $this->_selectedItem;
		}

		/**
		 * @return	string
		 */
		function ToString()
		{
			$options = '';
			foreach ($this->_items as $item)
			{
				$selected = '';
				if ($item)
				{
					$selected = ($item->href == $this->_selectedItem) ? ' selected="selected"' : '';
					$options .= '<option value="'.$item->href.'"'.$selected.'>'.$item->name.'</option>';
				}
			}
			
			return count($this->_items) == 0 ? '' : '<div class="wm_standart_menu_header">'.$this->_name.'<br /><select onchange="javascript:if (this.value == \'\') {window.location = \'?filter_off\';} else {window.location = \'?filter=\' + this.value;} " class="wm_filter_select">'.$options.'</select></div><br/>';
		}
	}

	class ap_Screen_Data
	{
		/**
		 * @var	array
		 */
		var $_data = array();

		/**
		 * @param	string $name
		 * @return	string|false
		 */
		function GetIncludeConrolPath($name)
		{
			$filename = $this->GetValue($name);
			if (@file_exists($filename))
			{
				return $filename;
			}
			return false;
		}

		/**
		 * @param	string	$name
		 * @return	mix
		 */
		function GetValue($name)
		{
			return isset($this->_data[$name]) ? $this->_data[$name] : null;
		}
		
		/**
		 * @param	string	$name
		 * @return	bool
		 */
		function ValueExist($name)
		{
			return isset($this->_data[$name]);
		}
		
		/**
		 * @param	string	$name
		 * @return	string
		 */
		function GetValueAsString($name)
		{
			return (string) $this->GetValue($name);
		}
		
		/**
		 * @param	string	$name
		 * @return	int
		 */
		function GetValueAsInt($name)
		{
			return GetGoodBigInt($this->GetValue($name));
		}
		
		/**
		 * @param	string	$name
		 * @return	bool
		 */
		function GetValueAsBool($name)
		{
			return (bool) $this->GetValue($name);
		}
		
		/**
		 * @param	string	$name
		 * @return	string
		 */
		function GetInputValue($name)
		{
			return ap_Utils::AttributeQuote($this->GetValueAsString($name));
		}
		
		/**
		 * @param	string	$name
		 */
		function PrintCheckedValue($name)
		{
			echo ($this->GetValueAsBool($name)) ? ' checked="checked" ' : '';
		}
		
		/**
		 * @param	string	$name
		 */
		function PrintSelectedValue($name)
		{
			echo ($this->GetValueAsBool($name)) ? ' selected="selected" ' : '';
		}

		/**
		 * @param	string	$name
		 */
		function PrintDisabledValue($name)
		{
			echo ($this->GetValueAsBool($name)) ? ' disabled="disabled" ' : '';
		}
		
		/**
		 * @param	string	$name
		 */
		function PrintInputValue($name)
		{
			echo $this->GetInputValue($name);
		}
		
		/**
		 * @param	string	$name
		 */
		function PrintClearValue($name)
		{
			echo $this->GetValueAsString($name);
		}
		
		/**
		 * @param	string	$name
		 */
		function PrintValue($name)
		{
			echo $this->GetValueAsString($name);
		}
		
		/**
		 * @param	string	$name
		 */
		function PrintIntValue($name)
		{
			echo $this->GetValueAsInt($name);
		}
		
		/**
		 * @param	string	$name
		 */
		function PrintJsValue($name)
		{
			echo ap_Utils::ReBuildStringToJavaScript($this->GetValueAsString($name), '"');
		}
		
		/**
		 * @param	string	$name
		 * @param	mix		$value
		 */
		function SetValue($name, $value)
		{
			$this->_data[$name] = $value;
		}
	}
	
	class ap_Screen_Tables_MenuButton
	{
		/**
		 * @var	string
		 */
		var $_name = 'NO NAME';
		
		/**
		 * @var	string
		 */
		var $_title = 'NO TYTLE';
		
		/**
		 * @var	string
		 */
		var $_image = '';
		
		/**
		 * @var	string
		 */
		var $_afolder = 'adminpanel';
		
		/**
		 * @var string
		 */
		var $_jsfunction = 'void(0);';
		
		/**
		 * @param	string	$adminfolder
		 * @param	string	$name
		 * @param	string	$image
		 * @param	string	$js
		 * @param	string	$title[optional] = null
		 * @return ap_Screen_Tables_MenuButton
		 */
		function ap_Screen_Tables_MenuButton($adminfolder, $name, $image, $js, $title = null)
		{
			$this->_afolder = $adminfolder;
			$this->_name = $name;
			$this->_image = $image;
			$this->_jsfunction = $js;
			$this->_title = ($title === null) ? $name : $title;
		}
		
		/**
		 * @return	string
		 */
		function ToString()
		{
			return '
<div class="wm_toolbar_item" onmouseover="this.className=\'wm_toolbar_item_over\'" onmouseout="this.className=\'wm_toolbar_item\'" onclick="'.ap_Utils::AttributeQuote($this->_jsfunction).'">
	<img title="'.ap_Utils::AttributeQuote($this->_title).'" src="'.$this->_afolder.'/images/menu/'.ap_Utils::AttributeQuote($this->_image).'" />
	<span>'.$this->_name.'</span>
</div>';
		}
	}
	
	class ap_Screen_Tables_MenuExtendedButton
	{
		/**
		 * @var	array
		 */
		var $_buttons = array();
		
		/**
		 * @param	string	$adminfolder
		 * @param	string	$name
		 * @param	object	$screen
		 * @param	array	$buttons
		 * @return ap_Screen_Tables_MenuExtendedButton
		 */
		function ap_Screen_Tables_MenuExtendedButton($adminfolder, $name, $screen, $buttons)
		{
			$this->_afolder = $adminfolder;
			$this->_name = $name;			
			$this->_screen = $screen;			
			$this->_buttons = $buttons;
		}
		
		/**
		 * @return	string
		 */
		function ToString()
		{
			$result = '';
			if (count($this->_buttons) > 0)
			{
				$result = '<div class="wm_tb" id="popup_replace_'.$this->_name .'">
						<div onclick="javascript:'.ap_Utils::AttributeQuote($this->_buttons[0]->_jsfunction).';" id="popup_title_'.$this->_name .'" class="wm_toolbar_item">
							<img title="'.ap_Utils::AttributeQuote($this->_buttons[0]->_title).'" src="'.$this->_buttons[0]->_afolder.'/images/menu/'.ap_Utils::AttributeQuote($this->_buttons[0]->_image).'" />
							<span class="">'.$this->_buttons[0]->_name.'</span>
						</div>
						<div id="popup_control_'.$this->_name .'" class="wm_toolbar_item">
							<span class="wm_control_icon" style="background-position: -720px -0px">&nbsp;</span>
						</div>
					</div>';
					
				$_js = 'PopupMenu = new CPopupMenus();
				PopupMenu.addItem(document.getElementById(\'popup_menu_'.$this->_name .'\'), document.getElementById(\'popup_control_'.$this->_name .'\'), \'wm_popup_menu\', document.getElementById(\'popup_replace_'.$this->_name .'\'), document.getElementById(\'popup_title_'.$this->_name .'\'), \'wm_tb\', \'wm_tb_press\', \'wm_toolbar_item\', \'wm_toolbar_item_over\');';
				$this->_screen->AddJsInitText($_js);

				if (count($this->_buttons) > 1)
				{
					for ($i = 1, $c = count($this->_buttons); $i < $c; $i++)
					{
						$result = $result.'<div id="popup_menu_'.$this->_name .'" class="wm_hide">
								<div onmouseout="this.className=\'wm_menu_item\';" onmouseover="this.className=\'wm_menu_item_over\'" class="wm_menu_item" onclick="javascript:'.ap_Utils::AttributeQuote($this->_buttons[$i]->_jsfunction).'">
								<img title="'.ap_Utils::AttributeQuote($this->_buttons[$i]->_title).'" src="'.$this->_buttons[$i]->_afolder.'/images/menu/'.ap_Utils::AttributeQuote($this->_buttons[$i]->_image).'" />
								<span class="">'.$this->_buttons[$i]->_name.'</span>
								</div>
							</div>';
					}
				}
			}

			return $result;	
		}
	}	
	
	class ap_Screen_Tables_Filter
	{
		/**
		 * @var	string
		 */
		var $_name;
		
		/**
		 * @var	array
		 */
		var $_items = array();

		/**
		 * @var array
		 */
		var $_top_items = array();
		
		/**
		 * @var	array
		 */
		var $_list = array();
		
		/**
		 * @var	string
		 */
		var $_selectedItem;
		
		/**
		 * @var	bool
		 */
		var $_listIsPrepared = false;
		
		/**
		 * @param	string	$name
		 * @return	ap_Screen_Tables_Filter
		 */
		function ap_Screen_Tables_Filter($name)
		{
			$this->_name = $name;
			$this->_selectedItem = isset($_SESSION[AP_SESS_FILTER]) ? $_SESSION[AP_SESS_FILTER] : '';
			if (isset($_GET['filter']) && strlen($_GET['filter']) > 0)
			{
				$this->_selectedItem = $_GET['filter'];
				$_SESSION[AP_SESS_FILTER] = $_GET['filter'];
			}	
		}
		
		/**
		 * @param ap_Screen_Filter_Item $filter
		 * @param bool					$isTop = false
		 */
		function AddItem($filter, $isTop = false)
		{
			if ($isTop)
			{
				array_unshift($this->_top_items, $filter);
			}
			else
			{
				$this->_items[] = $filter;
			}
		}
		
		/**
		 * @return	int
		 */
		function Count()
		{
			if ($this->_listIsPrepared)
			{
				return count($this->_list);
			}
			return count($this->_items) + count($this->_top_items);
		}
		
		/**
		 * @return	string
		 */
		function GetSelectedItemKey()
		{
			$arr = array();
			$listKeys = array_keys($this->_list);
			foreach ($listKeys as $href)
			{
				if ($href == $this->_selectedItem)
				{
					return $this->_selectedItem;
				}
				$arr[] = $href;
			}

			if (count($arr) > 0)
			{
				$this->_selectedItem = $arr[0];
				$_SESSION[AP_SESS_FILTER] = $arr[0];
			}
			else 
			{
				$this->_selectedItem = '';
				$_SESSION[AP_SESS_FILTER] = '';
			}
			
			return $this->_selectedItem;
		}
		
		/**
		 * @return	string
		 */
		function ToString()
		{
			$activ = $options = '';
			$c = 0;
			
			foreach ($this->_list as $href => $value)
			{
				if (count($value) == 2)
				{
					$activ = ($c === 0) 
						? '<a class="l1" title="'.ap_Utils::AttributeQuote($value[0]).'" href="javascript:void(0);"><div class="link '.$value[1].'"><div>'.$value[0].'</div></div>'
						: $activ;
					
					$addClass = (($this->_selectedItem === null && $c === 0) || $href == $this->_selectedItem) ? ' SelectedDomain' : '';
					$c++;	
					$activ = ($href == $this->_selectedItem) ? '<a class="l1" title="'.ap_Utils::AttributeQuote($value[0]).'" href="javascript:void(0);"><div class="link '.$value[1].'"><div>'.$value[0].'</div></div>' : $activ;
					
					$mhref = AP_INDEX_FILE.'?filter='.urlencode(ap_Utils::AttributeQuote($href));
					$options .= '<a class="l2" title="'.ap_Utils::AttributeQuote($value[0]).'" href="'.$mhref.'"><div class="'.$value[1].$addClass.'">'.$value[0].'</div></a>'.AP_CRLF;
				}
			}
			
			return count($this->_list) == 0 ? '' : '
<div style="float: left; height: 16px; padding: 4px;">
	<span>'.$this->_name.':</span>
</div>
<div class="menu_select">
	'.$activ.'<!--[if gte IE 7]><!--></a><!--<![endif]-->
		<div class="dd">
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td>
						'.$options.'
					</td>
				</tr>
			</table>
		</div>
		<!--[if lte IE 6]></a><![endif]-->
</div>';
		}
	}
	
	class ap_Screen_Table_Item
	{
		/**
		 * @var	int
		 */
		var $type;
		
		/**
		 * @var string
		 */
		var $href;
		
		/**
		 * @var string
		 */
		var $name;
		
		/**
		 * @var array
		 */
		var $values = array();
		
	}
	
	class ap_Screen_Filter_Item
	{
		/**
		 * @var	int
		 */
		var $type;
		
		/**
		 * @var string
		 */
		var $href;
		
		/**
		 * @var string
		 */
		var $name;
		
		/**
		 * @var string
		 */
		var $class;
	}
	
	class ap_Screen_Tables_List
	{
		/**
		 * @var	int
		 */
		var $_linePerPage = 20;
		
		/**
		 * @var	array
		 */
		var $_headers = array();
		
		/**
		 * @var	array
		 */
		var $_items = array();
		
		/**
		 * @var	bool
		 */
		var $_listIsPrepared = false;
		
		/**
		 * @var	bool
		 */
		var $_useCurrentList = false;
		
		/**
		 * @var	array
		 */
		var $_list = array();
		
		/**
		 * @var	int
		 */
		var $_page = 1;
		
		/**
		 * @var	int
		 */
		var $_list_cnt = 0;
		
		/**
		 * @var	string
		 */
		var $_orderColumn = 'Name';
		
		/**
		 * @var	int
		 */
		var $_orderType = 0;
		
		/**
		 * @var	string
		 */
		var $_delim = AP_TYPE_DELIMITER;

		/**
		 * @var	bool
		 */
		var $_useSort = true;
		
		/**
		 * @var	string
		 */
		var $_light = '';
		
		/**
		 * @var string
		 */
		var $_null_message = '';
		
		var $_afolder = 'adminpanel';
		
		/**
		 * @param string $adminfolder
		 * @return ap_Screen_Tables_List
		 */
		function ap_Screen_Tables_List($adminfolder)
		{
			$this->_afolder = $adminfolder;
			
			if (isset($_GET['page']))
			{	
				$_SESSION[AP_SESS_PAGE] = (int) $_GET['page'];
			}
			$this->_page = isset($_SESSION[AP_SESS_PAGE]) ? (int) $_SESSION[AP_SESS_PAGE] : $this->_page;
			
			if (isset($_GET['scolumn']))
			{
				$_SESSION[AP_SESS_COLUMN] = $_GET['scolumn'];
			}
			$this->_orderColumn = isset($_SESSION[AP_SESS_COLUMN]) ? $_SESSION[AP_SESS_COLUMN] : $this->_orderColumn;
			
			if (isset($_GET['sorder']))
			{
				$_SESSION[AP_SESS_ORDER] = (bool) $_GET['sorder'];
			}
			$this->_orderType = isset($_SESSION[AP_SESS_ORDER]) ? (bool) $_SESSION[AP_SESS_ORDER] : $this->_orderType;
			
			$this->AddHeader('Null', 100);
		}
		
		/**
		 * @param	bool	$value
		 */
		function UseSort($value = true)
		{
			$this->_useSort = $value;
		}
		
		/**
		 * @param	string	$str
		 */
		function SetLightStr($str)
		{
			$this->_light = $str;
		}
		
		/**
		 * @param	ap_Screen_Table_Item	$item
		 */
		function AddItem($item)
		{
			$this->_items[] = $item;
		}
		
		/**
		 * @param	string	$name
		 * @param	int		$size
		 * @param	bool	$orderField[optional] = false;
		 */
		function AddHeader($name, $size, $orderField = false)
		{
			$this->_headers[$name] = $size;
			if ($orderField && !isset($_GET['scolumn']) && !isset($_SESSION[AP_SESS_COLUMN]))
			{
				$this->_orderColumn = $name;
			}
		}
		
		function ClearHeaders()
		{
			$this->_headers = array();
		}
		
		/**
		 * @return	string
		 */
		function ToString()
		{
			$return = AP_CRLF.'<div style="position:relative; overflow:hidden;border-top: 1px solid #c3c3c3;">

<table class="wm_hide" id="ps_container">
	<tr>
		<td><div class="wm_inbox_page_switcher_left"></div></td>
		<td class="wm_inbox_page_switcher_pages" id="ps_pages"></td>
		<td><div class="wm_inbox_page_switcher_right"></div></td>
	</tr>
</table>

<table style="width: 100%;" cellpadding="0" cellspacing="0" id="list">';
			$return .= $this->_getHeaderHtml();

			if (!$this->_useCurrentList)
			{
				$this->_list = $this->_sortByHeader($this->_list, $this->_orderColumn, (bool) $this->_orderType);
				
				if ($this->_page < 1 || $this->_page > ceil(count($this->_list) / $this->_linePerPage))
				{
					$this->_page = 1;
				}
				
				$this->SetPage($this->_page);
				$this->SetListCount(count($this->_list));
				
				$this->_list = array_slice($this->_list, 
									$this->_linePerPage * ($this->_page - 1), $this->_linePerPage);
			}
			
			$return .= $this->_getListHtml();
			
			return $return.AP_CRLF.'</table></div>';
		}
		
		function _sortByHeader($arr, $header, $rev = false)
		{
			$out = array();
			foreach ($arr as $key => $value)
			{
				$str = '';
				foreach ($value as $type => $desc)
				{
					if ($type == $header)
					{
						$str = $desc.$str;
					}
					else
					{
						$str .= $desc;
					}
				}
				
				$out[$key] = $str;
			}

			natcasesort($out);
			if ($rev)
			{
				$out = array_reverse($out, true);
			}

			foreach ($out as $key => $value)
			{
				$out[$key] = $arr[$key];
			}
			
			return $out;
		}
		
		/**
		 * @return	int
		 */
		function GetLinePerPage()
		{
			return $this->_linePerPage;
		}
		
		/**
		 * @return	int
		 */
		function GetPage($allUserCount = null)
		{
			if ($allUserCount !== null)
			{
				if (($this->_page - 1) * $this->GetLinePerPage() >= $allUserCount)
				{
					$this->SetPage(1);
				}
			}
			
			return $this->_page;
		}
		
		/**
		 * @param	int	$page
		 */
		function SetPage($page)
		{
			$this->_page = (int) $page;
			$_SESSION[AP_SESS_PAGE] = $this->_page;
		}
		
		function UseCurrentList()
		{
			$this->_useCurrentList = true;
		}

		/**
		 * @param	int	$cnt
		 */
		function SetListCount($cnt)
		{
			$this->_list_cnt = (int) $cnt;
		}
		
		/**
		 * @return string
		 */
		function ToJS()
		{
			return 'PageSwitcher = new CPageSwitcher();
PageSwitcher.Build();
PageSwitcher.Show('.$this->_page.', '.$this->_linePerPage.', '.$this->_list_cnt.', "Pager(", ");");
function Pager(page) {
	document.location = "'.AP_INDEX_FILE.'?page=" + page;
}'.AP_CRLF;
		}
		
		function _getOrderImg()
		{
			return ($this->_orderType == 1) ? 'order_arrow_down.gif' : 'order_arrow_up.gif';
		}
		
		function _getHeaderHtml()
		{
			if (count($this->_list) == 0)
			{
				$this->UseSort(false);
			}
			
			$return = '';
			if (count($this->_headers) > 0)
			{
				$return .= '
	<tr id="contact_list_headers" class="wm_inbox_headers">
		<td style="text-align: center; padding-top: 0pt; padding-left: 2px; padding-right: 2px; width: 22px">
			<input type="checkbox" id="allcheck" class="wm_checkbox" 
				onclick="Selection.CheckAllBox(this);" />
		</td>
';
				$c = count($this->_headers);
				foreach ($this->_headers as $name => $size)
				{
					$c--;
					$size = ($c == 0) ? '' : 'style="width: '.$size.'px"';
					$ord = ($this->_orderColumn == $name) ? (int) !$this->_orderType : 0;
					
					$class = 'wm_inbox_headers_from_subject';
					$class .= ($this->_useSort) ? ' wm_control' : '';
					
					$onclick = ($this->_useSort) ? 'onclick="document.location=\''.AP_INDEX_FILE.'?scolumn='.urlencode(ap_Utils::AttributeQuote($name)).'&sorder='.urlencode($ord).'\'"' : '';
					$img = ($this->_useSort) ? ($this->_orderColumn == $name) ? '<img src="'.$this->_afolder.'/images/menu/'.$this->_getOrderImg().'">': '' : '';
					
					$return .= '
<td class="wm_inbox_headers_separate_noresize" style="width: 1px"></td>
<td id="'.$name.'" class="'.$class.'" '.$size.' '.$onclick.'>
	<nobr>'.$name.$img.'</nobr>
</td>
';
				}
			}
			
			$return .= '
	</tr>';
			
			return $return;
		}
		
		/**
		 * @param	string $value
		 * @param	string $chars
		 * @return	string
		 */
		function _lightStr($name, $value, $chars)
		{
			static $allowNames = array('Email', 'Name');
			if (in_array($name, $allowNames) && strlen($chars) > 0)
			{
				return str_replace($chars, '<b>'.$chars.'</b>', $value);
			}
			
			return $value;
		}
		
		function _getListHtml()
		{
			$return = '';
			if (count($this->_headers) > 0)
			{
				$nobrArray = array('Email', 'Last Login');
				$cnt = count($this->_list);
				if ($cnt > 0 && $cnt <= $this->_linePerPage)
				{
					foreach ($this->_list as $href => $value)
					{
						$return .= '
	<tr id="'.ap_Utils::AttributeQuote(urlencode($href)).'" class="wm_inbox_read_item">
		<td id="none" class="wm_inbox_none">
			<input name="chCollection[]" type="checkbox" value="'.ap_Utils::AttributeQuote($href).'" class="wm_checkbox" />
		</td>';
						$headerKeys = array_keys($this->_headers);
						foreach ($headerKeys as $name)
						{
							if (isset($value[$name]))
							{
								$nobrStr0 = $nobrStr1 = '';
								if (in_array($name, $nobrArray))
								{
									$nobrStr0 = '<nobr>';
									$nobrStr1 = '</nobr>';
								}
								$pretext = ($name == 'Email') ? '' : '';
								$return .= '
		<td></td>
		<td class="wm_inbox_from_subject" style="overflow:hidden;">
			'.$nobrStr0.$pretext.($this->_lightStr($name, $value[$name], $this->_light)).$nobrStr1.'
		</td>							
	';	
							}
							else
							{
								$return .= '
		<td></td>
		<td class="wm_inbox_from_subject" style="padding-left: 4px;">
		</td>
		';
							}
						}
	
						$return .= '
	</tr>';
					}
				}
				else if ($cnt == 0)
				{
					$return .= '<tr><td colspan="'.((count($this->_headers) * 2) + 1).'"><div class="wm_inbox_info_message">'.$this->_null_message.'</div></td></tr>';
				}
				else
				{
					$return .= '<tr><td colspan="'.((count($this->_headers) * 2) + 1).'"><div class="wm_inbox_info_message">ERROR</div></td></tr>';
				}
			}
			
			return $return;
			
		}
	}
