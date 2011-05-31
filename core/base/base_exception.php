<?php

	require_once WM_ROOTPATH . 'common/class_log.php';

/**
 * Simple error class, base for errors class family
 */
class BaseException extends Exception
{
	/**
	 * @var BaseError
	 */
	protected $_cause = null;

	/**
	 * @var bool
	 */
	protected $_isLog = false;

	/**
	 * @var string
	 */
	protected $_id;

	/**
	 * Constructor
	 *
	 * @param string $message
	 * @param int $code
	 * @param BaseException $cause
	 */
	public function __construct($message = '', $code = 0, $cause = null )
	{
		parent::__construct($message, $code);
		$this->_id = md5(__CLASS__ . microtime());
		if ($cause instanceof BaseError)
		{
			$this->_cause = $cause;
		}
		if (true === $this->_isLog)
		{
			$this->_writeLog();
		}
	}


	/**
	 * get id error hash
	 * @return string
	 */
	public function getErrorId()
	{
		return $this->_id;
	}

	/**
	 * get error cause
	 *
	 * @return BaseError
	 */
	public function getCause()
	{
		return $this->_cause;
	}

	/**
	 * write error to log
	 */
	protected function _writeLog()
	{
		$log =& CLog::CreateInstance();
		$msg = (string) $this;
		$log->WriteLine($msg, LOG_LEVEL_ERROR);
	}

	public function  __toString()
	{
		$msg = 'Exception Error['.$this->getErrorId().']: '.get_class($this) . ':: '.$this->getMessage().' ('.$this->getCode().')';
		if (!is_null($this->_cause))
		{
			$msg .= "\r\n".'Exception Cause: '. $this->_cause->getErrorId();
		}
		return $msg;
	}
}

class BaseNoticeException extends BaseException
{
	public function  __toString()
	{
		$msg = 'Exception Notice['.$this->getErrorId().']: '.get_class($this) . ':: '.$this->getMessage().' ('.$this->getCode().')';
		if (!is_null($this->_cause))
		{
			$msg .= "\r\n".'Exception Cause: '. $this->_cause->getErrorId();
		}
		return $msg;
	}
}