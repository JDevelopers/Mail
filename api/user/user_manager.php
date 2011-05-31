<?php

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

require_once(WM_ROOTPATH.'core/base/base_exception.php');
require_once(WM_ROOTPATH.'core/base/base_manager.php');

class UserManager extends BaseManager
{
	public function __construct()
	{
		$this->_defaultCommandCreatorPath = WM_ROOTPATH . 'core/models/user/user_command_creator.php';
		$this->_defaultCommandeCreatorNames[WM_DB_MYSQL] = 'UserCommandCreator';
		$this->_defaultCommandeCreatorNames[WM_DB_MSSQLSERVER] = 'UserCommandCreator';
		
		$this->_defaultModelPath = WM_ROOTPATH . 'core/models/user/user_model.php';
		$this->_defaultModelName = 'UserModel';
	}


	/**
	 * @return int
	 */
	protected function _CreateUser()
	{
		return $this->_currentModel->CreateUser();
	}

	/**
	 * @param int $userId
	 * @return bool
	 */
	protected function _DeleteUser($userId)
	{
		return $this->_currentModel->DeleteUser($userId);
	}

	protected function _IsUserExists($userId)
	{
		return $this->_currentModel->IsUserExists($userId);
	}

	protected function _LoginByUserId($userId)
	{
		if ($this->IsUserExists($userId))
		{
			@session_write_close();
			@session_name('PHPWEBMAILSESSID');
			@session_start();
			$_SESSION[USER_ID] = $userId;
			return true;
		}
		return false;
	}

}