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
	
	class MessageActionFilters
	{
		private $_main;

		/**
		 * @return MessageActionFilters
		 */
		public function &CreateInstance()
		{
			static $instance;
    		if (null === $instance)
    		{
				$instance = new MessageActionFilters();
    		}

    		return $instance;
		}

		/**
		 * @return MessageActionFilters
		 */
		private function MessageActionFilters()
		{
			$this->_main = array();
			$this->LoadXML();
		}

		/**
		 * @return	bool
		 */
		protected function LoadXML()
		{
			if (@file_exists(INI_DIR.'/settings/filters.xml'))
			{
				$return = file_get_contents(INI_DIR.'/settings/filters.xml');
				if (false !== $return)
				{
					$xmlDocument = new XmlDocument();
					if ($xmlDocument->LoadFromString($return))
					{
						return $this->initFromXmlRoot($xmlDocument->XmlRoot, $this->_main);
					}
				}
				return false;
			}
			return true;
		}

		/**
		 * @return	array
		 */
		public function GetNoReplyEmails()
		{
			return $this->GetIndexEmails('NoReply');
		}

		/**
		 * @return	array
		 */
		public function GetNoReplyAllEmails()
		{
			return $this->GetIndexEmails('NoReplyAll');
		}

		/**
		 * @return	array
		 */
		public function GetNoForwardEmails()
		{
			return $this->GetIndexEmails('NoForward');
		}
		
		/**
		 * @return	array
		 */
		public function GetIndexEmails($index)
		{
			$return = array();
			if (isset($this->_main['MessageActionFilters'][$index]['email'])
					&& is_array($this->_main['MessageActionFilters'][$index]['email']))
			{
				$return = $this->_main['MessageActionFilters'][$index]['email'];
			}
			return $return;
		}

		/**
		 * @param	XmlDomNode	$xmlTree
		 * @param	array		$mainArray
		 */
		protected function initFromXmlRoot(&$xmlTree, &$mainArray)
		{
			if ($xmlTree)
			{
				if ($xmlTree->Children && count($xmlTree->Children) > 0)
				{
					$mainArray[$xmlTree->TagName] = array();
					foreach ($xmlTree->Children as $node)
					{
						$this->initFromXmlRoot($node, $mainArray[$xmlTree->TagName]);
					}
				}
				else
				{
					if (isset($mainArray[$xmlTree->TagName]))
					{
						if (is_array($mainArray[$xmlTree->TagName]))
						{
							$mainArray[$xmlTree->TagName][] = ConvertUtils::WMBackHtmlSpecialChars($xmlTree->Value);
						}
						else
						{
							$mainArray[$xmlTree->TagName] = array(
									$mainArray[$xmlTree->TagName],
									ConvertUtils::WMBackHtmlSpecialChars($xmlTree->Value)
								);
						}
					}
					else
					{
						$mainArray[$xmlTree->TagName] = ConvertUtils::WMBackHtmlSpecialChars($xmlTree->Value);
					}
				}
			}
		}
	}
	