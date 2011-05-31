<?php

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

require_once(WM_ROOTPATH.'core/base/base_exception.php');
require_once(WM_ROOTPATH.'core/base/base_manager.php');
require_once(WM_ROOTPATH.'calendar/containers/event_container.php');
require_once(WM_ROOTPATH.'calendar/containers/calendar_container.php');
require_once(WM_ROOTPATH.'calendar/containers/appointment_container.php');

class FunambolCalendarManager extends BaseManager
{
	/**
	 * @access private
	 * @var int
	 */
	var $_userId;

	/**
	 * @access private
	 * @var mixed
	 */
	var $_lastOperationResult;

	private $_userAccount = null;

	/**
	 * Constructor
	 *
	 * @param int $userId
	 */
	public function  __construct($userId = null)
	{
		$this->SetActiveUserId($userId);
		$this->_defaultCommandCreatorPath = WM_ROOTPATH . 'calendar/command_creators/funambol_calendar_command_creator.php';
		$this->_defaultCommandeCreatorNames[WM_DB_MYSQL] = 'MySqlFunambolCalendarCommandCreator';
		$this->_defaultCommandeCreatorNames[WM_DB_MSSQLSERVER] = 'MsSqlFunambolCalendarCommandCreator';
		$this->_defaultModelPath = WM_ROOTPATH . 'calendar/models/funambol_calendar_model.php';
		$this->_defaultModelName = 'FunambolCalendarModel';
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

	/**
	 * Set user id
	 *
	 * @param int $userId
	 */
	public function SetUserAccount($userAccount)
	{
		$this->_userAccount = $userAccount;
	}

	/**
	 *
	 * @return bool
	 */
	public function IsReady()
	{
//		if (!is_int($this->_userId) || (CREATE_DB_RECORD_FAIL_RESULT == $this->_userId))
//		{
//			return false;
//		}
		return parent::IsReady();
	}


	protected function _ReplaceEvent($fnEventContainer)
	{
		return $this->_currentModel->ReplaceEvent($fnEventContainer, $this->_userId);
	}
	protected function _GetEvent($id)
	{
		return $this->_currentModel->GetEvent($id);
	}
	protected function _GetFullEventsList()
	{
		return $this->_currentModel->GetFullEventsList($this->_userAccount);
	}
	protected function _GetEventsListExcluded($funambolEventIds)
	{
		return $this->_currentModel->GetEventsListExcluded($this->_userAccount,$funambolEventIds);
	}

   /**
    * @param int $fnEvent
    * @return string
    */
   protected function _DeleteEvent($fnEvent)
   {
		return $this->_currentModel->DeleteEvent($fnEvent);
   }

   /**
    * @param int $fnEvent
    * @return string
    */
   protected function _DeleteEventException($fnEvent)
   {
		return $this->_currentModel->DeleteEventException($fnEvent);
   }

	protected function _GetUserIdsUpdatedCalendars( $dateUpdate )
	{
		return $this->_currentModel->GetUserIdsUpdatedCalendars( $dateUpdate );
	}

}