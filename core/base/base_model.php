<?php

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

	require_once(WM_ROOTPATH.'core/base/base_exception.php');

/**
 * base class for Model 's family classes.
 *
 * Provides main methods to be used by clients
 *
 * Provides unified __call() interface for published Aspect-like programming methods
 * If you need to add method, that is not __call() compatible you need to declare it
 * as "public" and call it directly
 *
 * if you'd like to add another Aspect-like handled method, use the following steps:
 * 1. declare protected method, starting with "_" character and named according to
 * "public" methods naming standard
 *
 * 2. in case of success returns TRUE of any reasonable result in case of "get" methods
 * in case of any error __call() will throw exception stacking this exception over
 * initial exception
 * 
 * 3. at the beginning of each function the following model properties
 *  MUST BE INITIALIZED:
 *	protected $_errorMsg;
 *	protected $_errorNo;
 * according to these properties error stack is revinded
 *
 * @TODO gather all string error messages into one array and generate keys for
 * these error messages by function's name
 *
 * If you'd like to use any methods of "Model" class directly USE __call()
 * wrapper in order to preserve errors stack
 */
abstract class BaseModel
{
	const ERR_MSG_BASEMANAGER_METHOD_NOT_EXIST = 'ERR_MSG_BASEMANAGER_METHOD_NOT_EXIST';
	const ERR_NO_BASEMANAGER_METHOD_NOT_EXIST = 1;

	/**
	 * @var BaseError
	 */
	private $_lastError = null;

	private $_lastOperationResult = null;

	abstract public function IsReady();

	/**
	 * @return BaseError
	 */
	public function GetLastError()
	{
		return $this->_lastError;
	}

	/**
	 * @var string
	 */
	protected $_errorMsg;

	/**
	 * @var int
	 */
	protected $_errorNo;

	protected $_modelExceptionName;

	protected $_currentEvent;

	public function  __construct()
	{
		$this->_modelExceptionName = get_class($this).'Exception';
	}
	/**
	 * @todo move to BaseModel
	 *
	 * @param string $functionName
	 * @param array $params
	 * @return mixed
	 */
	public function __call($functionName, $params)
	{
		if (method_exists($this, '_'.$functionName))
		{
			try
			{
				return call_user_func_array(array($this, '_'.$functionName), $params);
			}
			/*
			catch(BaseNoticeException $e)
			{
				throw $e;
			}
			*/
			catch(BaseException $e)
			{
				throw new $this->_modelExceptionName($this->_errorMsg, $this->_errorNo, $e);
			}
		}
		else
		{
			throw new BaseManagerException(
				self::ERR_MSG_BASEMANAGER_METHOD_NOT_EXIST . $functionName,
				self::ERR_NO_BASEMANAGER_METHOD_NOT_EXIST);
		}
	}

	public function GetLastOperationResult()
	{
		return $this->_lastOperationResult;
	}
}

/**
 * Specified error for models family
 */
class BaseModelException extends BaseException
{}