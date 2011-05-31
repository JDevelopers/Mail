<?php

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

require_once(WM_ROOTPATH.'core/base/base_exception.php');
require_once(WM_ROOTPATH.'core/base/base_manager.php');

require_once(WM_ROOTPATH.'calendar/containers/user_settings_container.php');

class UserSettingsManager extends BaseManager
{
	/**
	 * @var int
	 */
	private $_userId;
	
	public function __construct($userId = null) 
	{
		$this->SetActiveUserId($userId);
		$this->_defaultCommandCreatorPath = WM_ROOTPATH . 'calendar/command_creators/user_settings_command_creator.php';
		$this->_defaultCommandeCreatorNames[WM_DB_MYSQL] = 'CalendarUserSettingsCommandCreator';
		$this->_defaultCommandeCreatorNames[WM_DB_MSSQLSERVER] = 'CalendarUserSettingsCommandCreator';
		$this->_defaultModelPath = WM_ROOTPATH . 'calendar/models/user_settings_model.php';
		$this->_defaultModelName = 'CalendarUserSettingsModel';
	}
	
	/**
	 * Set user id
	 *
	 * @param int $userId
	 */
	public function SetActiveUserId($userId)
	{
		$this->_userId = (int) $userId;
	}
	
	protected function _LoadSettings()
	{
		return $this->_currentModel->LoadSettings($this->_userId);
	}
	
	protected function _SaveSettings($calendarUserSettingsContainer, $createSettings = false)
	{
		return $this->_currentModel->SaveSettings($calendarUserSettingsContainer, $createSettings);
	}
}

class UserSettingsManagerException extends BaseManagerException
{}