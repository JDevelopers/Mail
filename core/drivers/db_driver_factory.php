<?php

	define('WM_DB_MYSQL', 3);
	define('WM_DB_MSSQLSERVER', 1);

	defined('QUOTE_ESCAPE') || define('QUOTE_ESCAPE', 1);
	defined('QUOTE_DOUBLE') || define('QUOTE_DOUBLE', 2);
	
	defined('WM_DB_LIBS_PATH') || define('WM_DB_LIBS_PATH', WM_ROOTPATH . 'db/');

	include_once WM_ROOTPATH.'/mime/inc_constants.php';
	// include_once WM_DB_LIBS_PATH.'/class_dbsql.php';

/**
 * Factory for DB drivers
 */
class DbDriverFactory
{
	const ERR_MSG_DBDRIVERFACTORY_DRIVER_NOT_SUPPORTIED = '';
	const ERR_NO_DBDRIVERFACTORY_DRIVER_NOT_SUPPORTIED = 1;
	const ERR_MSG_DBDRIVERFACTORY_ESCQPE_TYPE_NOT_REGISTERED= '';
	const ERR_NO_DBDRIVERFACTORY_ESCQPE_TYPE_NOT_REGISTERED = 2;
	const ERR_MSG_DBDRIVERFACTORY_CONNECT_FAILED= '';
	const ERR_NO_DBDRIVERFACTORY_CONNECT_FAILED = 3;

    /**
     * @access private
     * @var Settings
     */
    private $_settings = null;

    /**
     * @access private
     * @var array
     */
    private $_driversList = null;

    /**
     * @access private
     * @var int
     */
    private $_dbType = null;

    /**
     *
     * @param Settings $settings
     */
    public function DbDriverFactory(Settings &$settings)
    {
        $this->_settings = $settings;
        
        // List of drivers must be in settings :-(
        $this->_driversList[WM_DB_MYSQL]['fileName'] = '/class_dbmysql.php';
        $this->_driversList[WM_DB_MYSQL]['driverName'] = 'DbMySql';
        $this->_driversList[WM_DB_MYSQL]['escapeType'] = QUOTE_ESCAPE;
        
        $this->_driversList[WM_DB_MSSQLSERVER]['fileName'] = '/class_dbmssql.php';
        $this->_driversList[WM_DB_MSSQLSERVER]['driverName'] = 'DbMSSql';
        $this->_driversList[WM_DB_MSSQLSERVER]['escapeType'] = QUOTE_DOUBLE;
    }

    /**
     * Get driver for current configuration
     * @return DbGeneralSql
     */
    public function GetDriver()
    {
        $this->_dbType = $this->_settings->DbType;
        if ($this->_settings->UseCustomConnectionString || $this->_settings->UseDsn)
		{
            $driver = $this->_setOdbcDriver();
        }
        else
        {
            if (isset($this->_driversList[$this->_dbType]))
            {
                $driverFileName = $this->_driversList[ $this->_dbType ]['fileName'];
                $driverName = $this->_driversList[ $this->_dbType ]['driverName'];
                include_once WM_DB_LIBS_PATH . $driverFileName;
                $driver = new $driverName($this->_settings->DbHost, $this->_settings->DbLogin, $this->_settings->DbPassword, $this->_settings->DbName);
            }
            else
            {
                throw new DbDriverFactoryException(
                            self::ERR_MSG_DBDRIVERFACTORY_DRIVER_NOT_SUPPORTIED,
                            self::ERR_NO_DBDRIVERFACTORY_DRIVER_NOT_SUPPORTIED);
            }
        }
        if (!$driver->Connect())
        {
            throw new DbDriverFactoryException(
                            self::ERR_MSG_DBDRIVERFACTORY_CONNECT_FAILED,
                            self::ERR_NO_DBDRIVERFACTORY_CONNECT_FAILED);
        }
        return $driver;
    }

    /**
     * @access private
     * @return DbOdbc
     */
    private function _setOdbcDriver()
    {
        include_once WM_DB_LIBS_PATH.'/class_dbodbc.php';
		if ($this->_settings->UseCustomConnectionString)
		{
            $connectionString = $this->_settings->DbCustomConnectionString;
		}
		else
		{
            $connectionString = 'DSN='.$this->_settings->DbDsn.';';
		}
        $driver = new DbOdbc($connectionString, $this->_dbType,
                                $this->_settings->DbLogin, $this->_settings->DbPassword);
        return $driver;
    }

    /**
     * Escape type for sql query
     *
     * @return int
     */
    public function GetEscapeType()
    {
        if (isset($this->_driversList[ $this->_dbType ]['escapeType']))
        {
            return $this->_driversList[ $this->_dbType ]['escapeType'];
        }
        throw new DbDriverFactoryException(
                        self::ERR_MSG_DBDRIVERFACTORY_ESCQPE_TYPE_NOT_REGISTERED ,
                        self::ERR_NO_DBDRIVERFACTORY_ESCQPE_TYPE_NOT_REGISTERED );
    }
}

class DbDriverFactoryException extends BaseException{}