<?php
require_once(WM_ROOTPATH.'calendar/base/class_baseinputdata.php');
define('INPUT_METHOD_GET', 'get');
define('INPUT_METHOD_POST', 'post');

/**
 * layer for input data
 */
class HttpInputData extends BaseInputData
{

    /**
     * @param string $name
     * @param string $type
     * @param string $method
     * @return mixed
     */
    function GetValue($name, $type = null, $method = null)
    {
        if (INPUT_METHOD_GET === $method)
        {
            return $this->getValueFromGet($name);
        }
        if (INPUT_METHOD_POST === $method)
        {
            return $this->getValueFromPost($name);
        }
        if (isset($_REQUEST[$name]))
        {
            return $this->_cleanValue($_REQUEST[$name], $type);
        }
        return null;
    }

    /**
     * @param string $name
     * @param string $type
     * @return mixed
     */
    function GetValueFromGet($name, $type = null)
    {
        if (isset($_GET[$name]))
        {
            return $this->_cleanValue($_GET[$name], $type);
        }
        return null;
    }

    /**
     * @param string $name
     * @param string $type
     * @return mixed
     */
    function GetValueFromPost($name, $type = null)
    {
        if (isset($_POST[$name]))
        {
            return $this->_cleanValue($_POST[$name], $type);
        }
        return null;
    }

    
}

/**
 * InputData for magic quots situation
 */
class InputDataWithCleaning extends HttpInputData
{

    /**
     * @access protected
     *
     * @param string $var
     * @param string $type
     * @return string
     */
    function _cleanValue($var, $type = null)
    {
        $result = null;
        if (is_array($var))
        {
            foreach ($var as $key=>$value)
            {
                $result[$key] = $this->_cleanValue($value);
            }
        }
        else
        {
            $result = addslashes($var);
            $result = parent::_cleanValue($result, $type);
        }
        return $result;
    }
}