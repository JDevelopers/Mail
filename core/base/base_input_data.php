<?php
/**
 * layer for input data
 */
abstract class BaseInputData
{

    /**
     * @param string $name
     * @param string $type
     * @param string $method
     * @return mixed
     */
    abstract public function getValue($name, $type = null, $method = null);

    /**
     * @access protected
     *
     * @param string $var
     * @param string $type
     * @return string
     */
    protected function _cleanValue($var, $type = null)
    {
        if (!is_null($type))
        {
            if (!settype($var, $type))
            {
                return null;
            }
        }
        return $var;
    }
}
