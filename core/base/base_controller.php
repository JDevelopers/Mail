<?php
define('ERR_MSG_CONTROLLER_ACTION_NOT_SUPPORTIED', 'action \'%s\' not supportied');
define('ERR_MSG_MODEL_NOT_FOUND', 'action \'%s\' model not found');
define('ERR_DATA_BASE_ACCESS', 2);

/**
 * Controller for MVC system
 */
class BaseController
{

    /**
     * @access private
	 * @var InputDataBase
	 */
    var $_inputData = null;

    /**
     * @access private
     * @var DbGeneralSql
     */
    var $_db = null;

    /**
     * @access private
     * @var CLog
     */
    var $_logger = null;

    /**
     *
     * @var Model
     */
    var $_model = null;

    /**
     * @access private
     * @var string
     */
    var $_modelFile = null;

    /**
     * @access private
     * @var array
     */
    var $_result;

    /**
     * @access private
     * @param InputDataBase $inputDataObject
     * @param DbGeneralSql $dbDriverObject
     */
    function BaseController($inputDataObject = null, $dbDriverObject = null)
    {
        $this->setInputData($inputDataObject);
        $this->setDataBaseDriver($dbDriverObject);
        $this->_logger = & CLog::CreateInstance();
        
    }

    /**
     *
     * @param InputDataBase $inputDataObject
     * @return bool/void
     */
    function setInputData($inputDataObject)
    {
        if (!is_a($inputDataObject, 'InputDataBase'))
        {
            return false;
        }
        $this->_inputData = $inputDataObject;
    }

    /**
     *
     * @param DbGeneralSql $dbDriverObject
     * @return bool/void
     */
    function setDataBaseDriver($dbDriverObject)
    {
        if (!is_a($dbDriverObject, 'DbGeneralSql'))
        {
            return false;
        }
        $this->_db = $dbDriverObject;
    }

    /**
     *
     * @param string $action
     * @return bool
     */
    function run($action)
    {
        $result = false;
        if (!method_exists($this, $action))
        {
            $logMsg = sprintf(ERR_MSG_CONTROLLER_ACTION_NOT_SUPPORTIED, $action);
            $this->_error($logMsg);
            $action = '_defaultAction';
        }
        if ($this->_initModel())
        {
            $logMsg = sprintf(ERR_MSG_MODEL_NOT_FOUND, $action);
            $this->_error($logMsg);
            $action = '_defaultAction';
        }
        $result = $this->$action;
        return $result;
    }

    /**
     * @access private
     * @return vooid
     */
    function _initModel()
    {
        if (!is_null($this->_modelFile))
        {
            if (!file_exists($this->_modelFile))
            {
                return false;
            }
            include_once($this->_modelFile);
            $controllerName = get_class($this);
            $modelName = str_replace('Controller', 'Model', $controllerName);
            if (!class_exists($modelName))
            {
                return false;
            }
            $model = new $modelName($this->_db);
        }
        return true;
    }

    /**
     * @access private
     * @param string $logMsg
     * @return void
     */
    function _error($logMsg)
    {
        if (!is_null($this->_logger))
        {
            $this->_logger->WriteLine($logMsg, LOG_LEVEL_ERROR);
        }
    }

    /**
     * @access private
     * @return array
     */
    function _defaultAction()
    {
        return array();
    }
}