<?php

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

require_once(WM_ROOTPATH.'core/base/base_exception.php');
require_once(WM_ROOTPATH.'core/base/base_manager.php');

class FunambolUserManager extends BaseManager
{
	public function __construct()
	{
		$this->_defaultCommandCreatorPath = WM_ROOTPATH.'core/models/user/funambol_user_command_creator.php';
		$this->_defaultCommandeCreatorNames[WM_DB_MYSQL] = 'MySqlFunambolUserCommandCreator';
		$this->_defaultCommandeCreatorNames[WM_DB_MSSQLSERVER] = 'MsSqlFunambolUserCommandCreator';
		$this->_defaultModelPath = WM_ROOTPATH.'core/models/user/funambol_user_model.php';
		$this->_defaultModelName = 'FunambolUserModel';
	}

	protected function _ReplaceUser($userName, $password, $email, $firstName, $lastName)
	{
		return $this->_currentModel->ReplaceUser($userName, $password, $email, $firstName, $lastName);
	}

}

?>
