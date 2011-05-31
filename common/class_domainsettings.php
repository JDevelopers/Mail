<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));
		
	require_once(WM_ROOTPATH.'common/inc_constants.php');
	require_once(WM_ROOTPATH.'common/class_xmldocument.php');
	require_once(WM_ROOTPATH.'common/class_convertutils.php');
	require_once(WM_ROOTPATH.'common/class_settings.php');
	
	class DomainSettings
	{
		/**
		 * @var string
		 */
		const FILE_NAME = 'domains.xml';

		/**
		 * @var array
		 */
		private $_main;

		/**
		 * @return MessageActionFilters
		 */
		public function &CreateInstance()
		{
			static $instance;
    		if (null === $instance)
    		{
				$instance = new DomainSettings();
    		}

    		return $instance;
		}

		/**
		 * @return MessageActionFilters
		 */
		private function DomainSettings()
		{
			$this->_main = array();
			$this->LoadXML();
		}

		/**
		 * @return	bool
		 */
		protected function LoadXML()
		{
			if (@file_exists(INI_DIR.'/settings/'.self::FILE_NAME))
			{
				$return = file_get_contents(INI_DIR.'/settings/'.self::FILE_NAME);
				if (false !== $return)
				{
					$xmlDocument = new XmlDocument();
					if ($xmlDocument->LoadFromString($return))
					{
						return $this->initFromXmlRoot($xmlDocument->XmlRoot);
					}
				}
				return false;
			}
			return true;
		}

		/**
		 * @param	Settings	$settings
		 * @return	array
		 */
		public function UpdateSettingsByDomain(&$settings)
		{
			$host = GetCurrentHost();
			if (isset($this->_main[$host]) && is_array($this->_main[$host]))
			{
				foreach ($this->_main[$host] as $key => $value)
				{
					if (isset($settings->$key))
					{
						$settings->$key = $value;
					}
				}
			}
		}

		/**
		 * @param	XmlDomNode	$xmlTree
		 * @param	array		$mainArray
		 */
		protected function initFromXmlRoot(&$xmlTree)
		{
			if ($xmlTree && $xmlTree->Children && count($xmlTree->Children) > 0)
			{
				if ('Domain' === $xmlTree->TagName)
				{
					$name = '';
					$domain = array();
					foreach ($xmlTree->Children as $node)
					{
						if ('Name' == $node->TagName)
						{
							$name = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						}
						else
						{
							$domain[$node->TagName] = ConvertUtils::WMBackHtmlSpecialChars($node->Value);
						}
					}
					if (strlen($name) > 0)
					{
						$this->_main[$name] = $domain;
					}
				}
				else
				{
					foreach ($xmlTree->Children as $node)
					{
						$this->initFromXmlRoot($node);
					}
				}
			}
		}
	}
	