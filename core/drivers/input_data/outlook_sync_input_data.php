<?php
require_once(WM_ROOTPATH.'plugins/outlooksync/configuration.php');
require_once(WM_ROOTPATH.'core/base/base_input_data.php');
class OutlookSyncInputData extends BaseInputData
{
/**
 * @access private
 * @var array
 */
	var $_legalNames;

	/**
	 * Constructor
	 * set conformity variable name in header and system
	 */
	function OutlookSyncInputData()
	{
		$this->_initLegalNames();
	}

	function _initLegalNames()
	{
		$this->_legalNames['login'] = $this->_formHeaderName(OUTLOOK_SYNC_HEADER_LOGIN);
		$this->_legalNames['email'] = $this->_formHeaderName(OUTLOOK_SYNC_HEADER_EMAIL);
		$this->_legalNames['pass']  = $this->_formHeaderName(OUTLOOK_SYNC_HEADER_PASSWORD);
	//		$this->_legalNames['calendarOutLookSyncDate']  = OUTLOOK_SYNC_HEADER_LAST_SYNC_DATE;
	}

	function _formHeaderName($additionalHeader)
	{

		return strtoupper('HTTP_'.str_replace('-', '_', $additionalHeader));
	}

	/**
	 *
	 * @param string $name
	 * @param string $type
	 * @param string $method (this parameter is not used, retained for InputData interface support)
	 * @return mixed
	 */
	function GetValue($name, $type = null, $method = null)
	{
		if (array_key_exists($name, $this->_legalNames))
		{
			$headerName = $this->_legalNames[$name];
			if (array_key_exists($headerName, $_SERVER))
			{
				$value = $_SERVER[$headerName];
				return $this->_cleanValue($value, $type);
			}
		}
		return null;
	}
}