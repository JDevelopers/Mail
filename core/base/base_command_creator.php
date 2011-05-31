<?php
/**
 * Base class for ComandCreators in Calendar
 */
class BaseCommandCreator
{
    /**
     *
     * @var string
     */
    protected $_dbPrefix;
    
    /**
     *
     * @var int
     */
    protected $_escapeType;

    /**
     *
     * @var array
     */
    protected $_buffer;

    /**
     *
     * @var BaseContainer
     */
    protected $_currentContainer;

    /**
     *
     * @param string $dbPrefix
     * @param int $escapeType
     */
    public function BaseCommandCreator($dbPrefix, $escapeType)
    {
        $this->_dbPrefix = $dbPrefix;
        $this->_escapeType = $escapeType;
    }

    /**
     * @access protected
     * @param string $str
     * @return string
     */
    protected function _escapeString($str, $nullable = FALSE)
    {
		if ($nullable && is_null($str))
		{
			return 'NULL';
		}
        if (is_null($str) || $str === '' )
        {
            return "''";
        }
		
        $str = ConvertUtils::ClearUtf8($str);

        switch ($this->_escapeType)
        {            
            case QUOTE_DOUBLE:
                return "'".str_replace("'", "''", $str)."'";
            case QUOTE_ESCAPE:
            default:
                return "'".addslashes($str)."'";
        }
        
    }

    /**
     * @access protected
     */
    protected function _cleanBuffer()
    {
        $this->_buffer = array();
        $this->_currentContainer = null;
    }

    /**
     * @access protected
     * @param string $key
     * @param string $fieldName
     */
    protected function _setIntValueToBuffer($key, $fieldName)
    {
        if ($this->_currentContainer->isValueSet($key))
        {
            $value = $this->_currentContainer->getValue($key, 'integer');
            $this->_buffer[] = $fieldName . ' = '. $value;
        }
    }

    /**
     * @access protected
     * @param string $key
     * @param string $fieldName
     */
    protected function _setStringValueToBuffer($key, $fieldName)
    {
        if ($this->_currentContainer->isValueSet($key))
        {
            $value = $this->_currentContainer->getValue($key);
            $this->_buffer[] = $fieldName . ' = '. $this->_escapeString($value);
        }
    }

	/**
     * @access protected
     * @param string $key
     * @param string $fieldName
     */
    protected function _setValueToBuffer($fieldName, $fieldVame)
    {
        if ($this->_currentContainer->isValueSet($key))
        {
            $this->_buffer[] = $fieldName.' = '.$fieldVame;
        }
    }

	protected function convertDate($fieldName)
	{
		return $fieldName;
	}

	protected function convertInsertDate($fieldValue)
	{
		return $this->_escapeString($fieldValue);
	}

	protected function getUtcDate()
	{
		$time = time();
		$time = $time - date('O') / 100 * 60 * 60;
		$time = date('Y-m-d H:i:s', $time);
		return $this->convertInsertDate($time);
	}
}

class BaseCommandCreatorException extends BaseException
{}