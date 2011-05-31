<?php

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

	require_once(WM_ROOTPATH.'core/base/base_exception.php');
	require_once(WM_ROOTPATH.'core/drivers/db_driver_factory.php');
	require_once(WM_ROOTPATH.'common/class_log.php'); // Debug
/**
 *
 * base class for Manager 's family classes.
 * Provides main methods to be used by clients
 *
 * Provides unified __call() interface for published Aspect-like programming methods
 * If you need to add method, that is not __call() compatible you need to declare it
 * as "public" and call it directly
 *
 * if you'd like to add another Aspect-like handled method, use the following steps:
 * 1. declare protected method, starting with "_" character and named according to
 * "public" methods naming standard
 * 2. in case of success returns TRUE of any reasonable result in case of "get" methods
 * in case of any error it may either throw exception - it will be caught by __call()
 * and translated to FALSE return by __call() or return FALSE directly
 *
 * If you'd like to use any methods of "Manager" class directly DO NOT use __call()
 * wrapper. Call this method directly instead.
 *
 * @todo add opportunity using multimodel
 */
abstract class BaseManager
{
	const ERR_MSG_BASEMANAGER_METHOD_NOT_EXIST = 'ERR_MSG_BASEMANAGER_METHOD_NOT_EXIST';
	const ERR_NO_BASEMANAGER_METHOD_NOT_EXIST = 1;
	
	protected $_currentModel;
	protected $_defaultCommandCreatorPath;
	protected $_defaultCommandeCreatorNames = array();
	protected $_defaultModelPath;
	protected $_defaultModelName;


	/**
	 * @var BaseException
	 */
	private $_lastError = null;
	
	/**
	 * Set active model
	 *
	 * @param BaseModel $apiModel
	 * @return bool
	 */
	public function SetApiModel(BaseModel $apiModel)
	{
		$this->_currentModel = $apiModel;
		return true;
	}

	/**
	 * @return bool
	 */
	public function IsReady()
	{
		if ($this->_currentModel instanceof BaseModel)
		{
			return true;
		}
		return false;
	}
	
	public function __call($functionName, $params)
	{
		try
		{
			if (method_exists($this, '_'.$functionName))
			{
				return call_user_func_array(array($this, '_'.$functionName), $params);
			}
			
			throw new BaseManagerException(
				$functionName.' => '. self::ERR_MSG_BASEMANAGER_METHOD_NOT_EXIST . $functionName,
				self::ERR_NO_BASEMANAGER_METHOD_NOT_EXIST);
		}
		catch(BaseException $e)
		{
			$this->_registerException($e);
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function InitManager()
	{
		require_once(WM_ROOTPATH.'common/class_settings.php');
		$settings =& Settings::CreateInstance();
		try
		{
			$factory = new DbDriverFactory($settings);
			$dbDriver = $factory->GetDriver();

			$dbPrefix = $settings->DbPrefix;
			$escapeType = $factory->GetEscapeType();
		}
		catch(BaseException $e)
		{
			$this->_registerException($e);
			return false;
		}
		if (isset($this->_defaultCommandeCreatorNames[$settings->DbType]))
		{
			require_once($this->_defaultCommandCreatorPath);
			$comandCreator = new $this->_defaultCommandeCreatorNames[$settings->DbType]($dbPrefix, $escapeType);
			//TODO the following refactoring is needed:
			//add possibility to use multiple models simultaneously
			$this->_initModel($dbDriver, $comandCreator);
			return $this->IsReady();
		}
		
		return false;
	}

	private function _initModel($dbDriver, $comandCreator)
	{
		require_once($this->_defaultModelPath);
		$model = new $this->_defaultModelName;
		$model->SetDriver($dbDriver);
		$model->SetComandCreator($comandCreator);
		if ($model->IsReady())
		{
			$this->_currentModel = $model;
		}
	}

	public function GetLastError()
	{
		$msg = null;
		if ($this->_lastError instanceof BaseException)
		{
			$msg = $this->_lastError->getMessage();
		}
		return $msg;
	}
	
	public function GetTraceError()
	{
		$msg = null;
		if ($this->_lastError instanceof BaseException)
		{
			$msg = $this->_lastError->getTraceAsString();
		}
		return $msg;
	}

	protected function _registerException(BaseException $e)
	{
		$this->_lastError = $e;
	}
}

class BaseManagerException extends BaseException 
{}
