<?php

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

	require_once(WM_ROOTPATH.'core/base/base_model.php');
	require_once WM_ROOTPATH.'common/class_log.php';
	

/**
 * Class DB layer
 */
class BaseDBModel extends BaseModel
{
	const ERR_MSG_DRIVER_NOT_SET = 'ERR_MSG_DRIVER_NOT_SET';
	const ERR_NO_DRIVER_NOT_SET = 1;
	const ERR_MSG_COMANDCREATOR_NOT_SET = 'ERR_MSG_COMANDCREATOR_NOT_SET';
	const ERR_NO_COMANDCREATOR_NOT_SET = 2;
	const ERR_MSG_EXECUTE_FAIL = 'ERR_MSG_EXECUTE_FAIL';
	const ERR_NO_EXECUTE_FAIL = 3;
	const ERR_MSG_GETLAST_INSERT_ID_FAIL = 'ERR_MSG_GETLAST_INSERT_ID_FAIL';
	const ERR_NO_GETLAST_INSERT_ID_FAIL = 4;

	/**
	* @access protected
	* @var DbGeneralSql
	*/
	protected $_driver = null;

	/**
	 * @access protected
	 * @var ComandCreatorBase
	 */
	protected $_commandCreator = null;

	/**
	 * @access private
	 * @var log
	 */
	public $log = null;

	public function  __construct()
	{
		$this->log =& CLog::CreateInstance();
		parent::__construct();
	}

	public function SetDriver(DbGeneralSql $dbDriver)
	{
		$this->_driver = $dbDriver;
		return true;
	}

	public function SetComandCreator(BaseCommandCreator $comandCreator)
	{
		$this->_commandCreator = $comandCreator;
		return true;
	}

	public function IsReady()
	{
		if (!($this->_driver instanceof DbGeneralSql))
		{
			$errorMsg = self::ERR_MSG_DRIVER_NOT_SET;
			$errorCode = self::ERR_NO_DRIVER_NOT_SET;
			$this->_lastError = new BaseDBException($errorMsg, $errorCode);
			return false;
		}
		if (!($this->_commandCreator instanceof BaseCommandCreator))
		{
			$errorMsg = self::ERR_MSG_COMANDCREATOR_NOT_SET;
			$errorCode = self::ERR_NO_COMANDCREATOR_NOT_SET;
			$this->_lastError = new BaseDBException($errorMsg, $errorCode);
			return false;
		}
		return true;
	}

	/**
	 * @access protected
	 * @param string $sql
	 * @param string $errorMsg
	 * @param int $errorCode
	 * @param bool $accumulatedErrors
	 * @return bool
	 */
	protected function _executeSql($sql)
	{
		$this->_isDriverAllow();
		$result = $this->_driver->Execute($sql);
		if (false === $result)
		{
			throw new BaseDBModelException($this->_driver->ErrorDesc, $this->_driver->ErrorCode);
		}
		return true;
	}

	/**
	 * @access protected
	 * @param string $sql
	 * @param string $errorMsg
	 * @param int $errorCode
	 * @param bool $accumulatedErrors
	 * @return bool|array
	 */
	protected function _query($sql)
	{
		$isSuccess = $this->_executeSql($sql);
		$result = $this->_driver->GetResultAsAssocArray();
		return $result;
	}

	protected function _getRowCount()
	{
		$this->_isDriverAllow();
		$result = $this->_driver->ResultCount();
		return $result;
	}

	protected function _isDriverAllow()
	{
		if (is_null($this->_driver))
		{
			$errorMsg = self::ERR_MSG_DRIVER_NOT_SET;
			$errorCode = self::ERR_NO_DRIVER_NOT_SET;
			throw new BaseDBException($errorMsg, $errorCode);
		}
		return true;
	}

	protected function _getLastInsertId()
	{
		$id = $this->_driver->GetLastInsertId();
		if (0 === $id)
		{
			$errorMsg = self::ERR_MSG_GETLAST_INSERT_ID_FAIL;
			$errorCode = self::ERR_NO_GETLAST_INSERT_ID_FAIL;
			throw new BaseDBException($errorMsg, $errorCode);
		}
		return $id;
	}
}

/**
 * Specyfied error for models family
 */
class BaseDBModelException extends BaseModelException
{}