<?php
require_once(WM_ROOTPATH.'core/base/base_exception.php');

/**
 *  Base class for container family.
 *  In unexpectied situation return object of ContainerException
 *
 */
abstract class BaseContainer
{
	const ERR_MSG_CONTAINER_KEY_NOT_REGESTERED = 'ERR_MSG_CONTAINER_KEY_NOT_REGESTERED';
	const ERR_NO_CONTAINER_KEY_NOT_REGESTERED = 1;
	const ERR_MSG_CONTAINER_SETTYPE_ERROR = 'ERR_MSG_CONTAINER_SETTYPE_ERROR';
	const ERR_NO_CONTAINER_SETTYPE_ERROR = 2;
	const ERR_MSG_CONTAINER_PARAMETR_IS_NOT_VALID = 'ERR_MSG_CONTAINER_PARAMETR_IS_NOT_VALID';
	const ERR_NO_CONTAINER_PARAMETR_IS_NOT_VALID = 3;
	const ERR_MSG_NOT_VALID_CONTAINER = 'ERR_MSG_NOT_VALID_CONTAINER';
	const ERR_NO_NOT_VALID_CONTAINER = 4;

    /**
     * @access protected
     * @var array
     */
    protected $_container;

    /**
     * @access protected
     * @var bool
     */
    protected $_isLock = false;

    /**
     * @access protected
     * @var ContainerException
     */
    protected $_error = null;

    /**
     * @access protected
     * @var mixed
     */
    protected $_buffer;

    /**
     * Constructor
     *
     * @access public
     */
    public function BaseContainer()
    {
        $this->_initContainerField();
        $this->_init();
        
    }

    /**
     * Init allowed field in container
     * must be specified in chaild class
     *
     * @abstract
     * @access protected
     */
    abstract protected function _initContainerField();

    /**
     * Init class
     * must be specified in chaild class
     *
     * 
     * @access protected
     */
    protected function _init()
    {
        $this->_isLock = true;
    }

    /**
     * set new value to container
     *
     * @access public
     * @param array $values
     * @return bool|ContainerException
     */
    public function MassSetValue($values)
    {
        if (!is_array($values))
        {
            throw new BaseContainerException(self::ERR_MSG_CONTAINER_PARAMETR_IS_NOT_VALID,
                    self::ERR_NO_CONTAINER_PARAMETR_IS_NOT_VALID);
        }
        foreach ($values as $field => $value)
        {
            if ($this->_isLock && !array_key_exists($field, $this->_container)){
				continue ;
			}
			$this->_container[$field] = $value;
        }
        return true;
    }

	 /**
     * @return array
     */
	public function GetContainer()
	{
		return $this->_container;
	}

    /**
     * set new value to container
     *
     * @access public
     * @param string $name
     * @param mixed $value
     * @param string $type
     * @return bool|ContainerException
     */
    public function SetValue($name, $value, $type = null)
    {
        $this->_checkKey($name);
        $this->_setType($value, $type);
        $this->_container[$name] = $this->_buffer;
		$this->_buffer = null;
        return true;
    }

    /**
     * get value from container by name
     *
     * @access public
     * @param string $name
     * @param string $type
     * @return mixed
     */
    public function GetValue($name, $type = null)
    {
        $this->_checkKey($name);
        $value = $this->_container[$name];
        $this->_setType($value, $type);
		$value = $this->_buffer;
        return $value;
    }

	   /**
     * get value from container by name
     *
     * @access public
     * @param string $name
     * @param string $type
	 * @param mixed $default
     * @return mixed
     */
	public function GetDefaultedValue($name, $type = null, $default = null)
	{
		if ($this->IsValueSet($name))
		{
			return $this->GetValue($name, $type);
		}
		
		return $default;
	}

    /**
     *
     *
     * @access protected
     * @param string $name
     * @return bool
     */
    protected function _checkKey($name)
    {
		$this->_buffer = null;
        if (!$this->_isLock || array_key_exists($name, $this->_container))
        {
            return true;
        }
        throw new BaseContainerException(self::ERR_MSG_CONTAINER_KEY_NOT_REGESTERED,
            self::ERR_NO_CONTAINER_KEY_NOT_REGESTERED);
    }

    /**
     *
     * @access protected
     * @param mixed $value
     * @param string $type
     * @return bool
     */
    protected function _setType($value, $type = null)
    {
        if (!is_null($type))
        {
            if (!@settype($value, $type))
            {
				$this->_buffer = null;
                throw new BaseContainerException(self::ERR_MSG_CONTAINER_SETTYPE_ERROR,
                    self::ERR_NO_CONTAINER_SETTYPE_ERROR);
            }
        }
        $this->_buffer = $value;
        return true;
    }

    /**
     * Check value status
     *
     * @param string $name
     * @return bool
     */
    public function IsValueSet($name)
    {
        if (array_key_exists($name, $this->_container) && !is_null($this->_container[$name]))
        {
            return true;
        }
        return false;
    }

    public function Assert($name, $assertValue, $useStrictType = true)
    {
        if (!$this->isValueSet($name))
        {
            return false;
        }
        $paramValue = $this->GetValue($name);
        if ($useStrictType)
        {
            return ($assertValue === $paramValue);
        }
        return ($assertValue == $paramValue);
    }

}
/**
 * Specified error for Container classes family
 */
class BaseContainerException extends BaseException
{}

