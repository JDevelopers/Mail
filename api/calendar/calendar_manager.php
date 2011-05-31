<?php
defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

require_once(WM_ROOTPATH.'core/base/base_exception.php');
require_once(WM_ROOTPATH.'core/base/base_manager.php');
require_once(WM_ROOTPATH.'common/inc_functions.php');
require_once(WM_ROOTPATH.'calendar/containers/event_container.php');
require_once(WM_ROOTPATH.'calendar/containers/calendar_container.php');
require_once(WM_ROOTPATH.'calendar/containers/appointment_container.php');
require_once(WM_ROOTPATH.'calendar/containers/reminder_container.php');

define('CREATE_DB_RECORD_FAIL_RESULT', 0);

class CalendarManager extends BaseManager
{
	const CALENDAR_STR_ID_PREFIX = '5765624D61696C50726F';
	
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
		$this->_defaultCommandCreatorPath = WM_ROOTPATH . 'calendar/command_creators/calendar_command_creator.php';
		$this->_defaultCommandeCreatorNames[WM_DB_MYSQL] = 'MySqlCalendarCommandCreator';
		$this->_defaultCommandeCreatorNames[WM_DB_MSSQLSERVER] = 'MsSqlCalendarCommandCreator';
		$this->_defaultModelPath = WM_ROOTPATH . 'calendar/models/calendar_model.php';
		$this->_defaultModelName = 'CalendarModel';

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
		return parent::IsReady();
	}

	/**
	 * Create new calendar
	 *
	 * @param CalendarContainer $calendarContainer
	 * @return int
	 */
	protected function _CreateCalendar(&$calendarContainer)
	{
		$calendarId = $this->_currentModel->CreateEmptyCalendar($this->_userId, $calendarContainer);
		$calendarContainer->SetValue('calendarId', $calendarId);
		if ($calendarContainer->IsEventsSet())
		{
			$eventsList = $calendarContainer->GetValue('calendarEvents');
			foreach($eventsList as $eventContainer)
			{
				$eventContainer->SetValue('calendarId', $calendarId);
				$this->_CreateEvent($eventContainer);
			}
		}
		return $calendarId;
	}

	protected function _lastLevelException($e)
	{
		$msg = $e->getErrorId() . $e->getTraceAsString();
	}

	/**
	 * Update calendar by calendarId, if calendarId is null then call CreateCalendar
	 * introduce of event will be add later
	 *
	 * @param CalendarContainer $calendarContainer
	 * @return bool
	 */
	protected function _UpdateCalendarInfo($calendarContainer)
	{
		if (!$calendarContainer->isValueSet('calendarId'))
		{
			return $this->CreateCalendar($calendarContainer);
		}
		$isCalendarUpdatable = $this->_currentModel->IsCalendarUpdatable($this->_userId, $calendarContainer);
		$result = $this->_currentModel->UpdateCalendarInfo($calendarContainer);
		return $result;
	}

	/**
	 * Gets the list of IDs of all calendars of the current user
	 *
	 * @return bool|array of objects
	 */
	protected function _GetCalendarsList($getObjects = true, $includeEvents = false,
			$timeSpans = null, $buildRepeats = false)
	{
		$calendarsList = $this->_currentModel->GetCalendarsList($this->_userId, $getObjects);
		if ($includeEvents === true && $getObjects === true && !empty($calendarsList))
		{
			foreach ($calendarsList as $calendarContainer)
			{
				$calendarId = $calendarContainer->GetValue('calendarId', 'int');
				if (!$calendarContainer->IsValueSet('calendarStringId'))
				{
					$calendarContainer->SetValue('calendarStringId', self::CALENDAR_STR_ID_PREFIX .  $this->_currentModel->GetUniqueKey(), 'string');
					$this->_UpdateCalendarInfo($calendarContainer);
				}
				$eventsList = $this->_GetEventsList($calendarId, true, $timeSpans, $buildRepeats);
				$calendarContainer->SetValue('calendarEvents', $eventsList);
				if (!empty($this->_userAccount)) {
					$calendarContainer->SetValue('calendarUser', $this->_userAccount);
				}
			}
		}
		return $calendarsList;
	}

	 /**
	  *	Gets the calendar by its id, with all events or only one event
	  *
	  * @param int $calendarId
	  * @param bool $includeEvents
	  * @param int $timeSpans
	  * @param int $eventId
	  * @return bool|CalendarContainer
	  */
	protected function _GetCalendar($calendarId, $includeEvents = true, 
									$timeSpans = null, $eventId = null,
									$isDelete = null)
	{
		$calendarContainer = false;

		$calendarContainer = $this->_currentModel->GetCalendarInfo($calendarId);

		if (!$calendarContainer->IsValueSet('calendarStringId')
				|| $calendarContainer->GetValue('calendarStringId') == '')
    	{
			$calendarContainer->SetValue('calendarStringId', self::CALENDAR_STR_ID_PREFIX .  $this->_currentModel->GetUniqueKey(), 'string');
			$this->_UpdateCalendarInfo($calendarContainer);
    	}

		if ($includeEvents && is_a($calendarContainer, 'CalendarContainer'))
		{
			if (isset($eventId))
			{
				$eventsList[] = $this->_GetEvent($eventId, $isDelete);
			}
			else
			{
				$eventsList = $this->_GetEventsList($calendarId, true, $timeSpans);
			}
			$calendarContainer->SetValue('calendarEvents', $eventsList);
		}
		
		return $calendarContainer;
	}
	
	 /**
	  *	Gets the calendar by its hash, with all events
	  *
	  * @param string $calendarHash
	  * @return bool|CalendarContainer
	  */
	protected function _GetCalendarByHash($calendarHash)
	{
		$calendarContainer = false;
		$calendarContainer = $this->_currentModel->GetCalendarInfoByHash($calendarHash);

		if (is_a($calendarContainer, 'CalendarContainer'))
		{
			$calendarId = $calendarContainer->GetValue('calendarId');
			$eventsList = $this->_GetEventsList($calendarId);
			$calendarContainer->SetValue('calendarEvents', $eventsList);
		}

		return $calendarContainer;
	}

	 /**
	  *	Get the calendar by its string id, with all events
	  *
	  * @param string $calendarStringId
	  * @return bool|CalendarContainer
	  */
	protected function _GetCalendarByStringId($calendarStringId)
	{
		$calendarContainer = false;
		$calendarContainer = $this->_currentModel->GetCalendarInfoByStringId($calendarStringId);

		if (is_a($calendarContainer, 'CalendarContainer'))
		{
			$calendarId = $calendarContainer->GetValue('calendarId');
			$eventsList = $this->_GetEventsList($calendarId);
			$calendarContainer->SetValue('calendarEvents', $eventsList);
		}
		return $calendarContainer;
	}

	/**
	 * Delete calendar by calendar id
	 *
	 * @param int|CalendarContainer $calendarId|$calendarContainer
	 * @return bool
	 */
	protected function _DeleteCalendar($calendar)
	{
		$result = false;
		$isCalendarUpdatable = $this->_currentModel->IsCalendarUpdatable($this->_userId, $calendar);
		if ($calendar instanceof CalendarContainer)
		{
			$calendarId = $calendar->GetValue('calendarId');
		}
		else
		{
			$calendarId = $calendar;
		}
		$result = $this->_currentModel->DeleteCalendar($calendarId);
		return $result;
	}

	/**
	 * Delete all events from calendar
	 *
	 * @param int|CalendarContainer $calendarId|$calendarContainer
	 * @return bool
	 */
	protected function _ClearCalendar($calendarContainer)
	{
		$result = false;
		$result = $this->_DeleteAllEvents($calendarContainer);
		return $result;
	}

	/**
	 * Create new event
	 *
	 * @param EventContainer $eventContainer
	 * @return int
	 */
	protected function _CreateEvent($eventContainer, $checkOverlap = false)
	{
		$this->_currentModel->IsCalendarUpdatable($this->_userId, $eventContainer);
		$eventId = $this->_currentModel->CreateEvent($eventContainer, $this->_userId, $checkOverlap);
		$eventContainer->SetValue('eventId', $eventId);
		return $eventId;
	}


	/**
	 * Delete event
	 *
	 * @param int|EventContainer $eventId|$eventContainer
	 * @return bool
	 */
	protected function _DeleteEvent($event, $forced = false, $physicalDelete = false)
	{
		if ($event instanceof EventContainer)
		{
			$eventId = $event->GetValue('eventId', 'int');
		}
		else
		{
			$eventId = (int)$event;
		}
		if(!$forced)
		{
			$this->_currentModel->IsEventUpdatable($this->_userId, $eventId);
		}
		$result = $this->_currentModel->DeleteEvent($eventId, true, $physicalDelete);
		return $result;
	}

	/**
	 * Delete all events for calendar
	 *
	 * @param int|CalendarContainer $calendarId|$calendarContainer
	 * @return bool
	 */
	protected function _DeleteAllEvents($calendar)
	{
		if ($calendar instanceof CalendarContainer)
		{
			$calendarId = $calendar->GetValue('calendarId');
		}
		else
		{
			$calendarId = $calendar;
		}

		$this->_currentModel->IsCalendarUpdatable($this->_userId, $calendar);
		$result = $this->_currentModel->DeleteEvent($calendarId, false);
		return $result;
	}

	/**
	 * Update event by eventId, if eventId is null then call CreateEvent
	 *
	 * @param EventContainer $eventContainer
	 * @return bool
	 */
	protected function _UpdateEvent($eventContainer, $checkOverlap = false)
	{
		if ($eventContainer->Assert('eventId', 0))
		{
			return $this->CreateEvent($eventContainer, $checkOverlap);
		}
		$this->_currentModel->IsEventUpdatable($this->_userId, $eventContainer);
		if ($this->isValueSet('calendarNewId') && !$eventContainer->Assert('calendarNewId', 0, false) )
		{
			$this->_isNewCalendarUpdatable($eventContainer);
			$calendarId = $eventContainer->GetValue('calendarNewId', 'int');
			$eventContainer->setValue('calendarId', $calendarId);
		}
		$result = $this->_currentModel->UpdateEvent($eventContainer, $this->_userId);
		return $result;
	}

	/**
	 * @access private
	 * @return bool
	 */
	function _isNewCalendarUpdatable($eventContainer)
	{
		$calendarNewId = $eventContainer->getValue('calendarNewId');
		$isCalendarUpdatable = $this->_currentModel->IsCalendarUpdatable($this->_userId, $eventContainer);
		return $isCalendarUpdatable;
	}

	/**
	 * Get list of event for calendar.
	 * @todo $timeSpans need to be realized
	 *
	 * @param int|pointer to CalendarContainer $calendarId|$calendarContainer
	 * @param $timeSpans (reserved)
	 * @return
	 */
	protected function _GetEventsList($calendar, $getObjects = true, 
									$timeSpans = null, $buildRepeats = false)
	{
		if (is_a($calendar, 'CalendarContainer'))
		{
			$calendarId = $calendar->GetValue('calendarId');
		}
		else
		{
			$calendarId = $calendar;
		}

		$result = $this->_currentModel->GetEventsList($calendarId, $getObjects, 
													$timeSpans, $buildRepeats,
													$this->_userAccount, $this->_userId);
		return $result;
	}

	protected function _GetEvent($eventId, $isDelete = false)
	{
		return $this->_currentModel->GetEvent($eventId, $this->_userId, $this->_userAccount, $isDelete);
	}

	protected function _GetEventByStringId($eventStringId, $isDelete = false)
	{
		return $this->_currentModel->GetEvent($eventStringId, $this->_userId,
							$this->_userAccount, $isDelete, 'event_str_id', 'string');
	}

	/**
	 * @return	string|bool
	 */	
	protected function _GetApplicationBaseUrl()
	{
		return $this->_currentModel->GetApplicationBaseUrl();
	}

	protected function _ImportIcs($filePath)
	{
		$calendarContainers = $this->_currentModel->ImportIcs($filePath);
		$webmailCalendarContainer = false;
		$webmailEventsIdsList = array();
		$updatedEventsIdsList = array();
		$calendarId = 0;
		foreach ($calendarContainers as $calendarContainer)
		{
			if ($calendarContainer->IsValueSet('calendarStringId'))
			{
				$calendarStringId = $calendarContainer->GetValue('calendarStringId');
				$webmailCalendarContainer = $this->_GetCalendarByStringId($calendarStringId);
				if (!is_a($webmailCalendarContainer, 'CalendarContainer'))
				{
					$this->_CreateCalendar($calendarContainer);
					continue;
				}
				else
				{
					$calendarId = $webmailCalendarContainer->GetValue('calendarId');
					$calendarContainer->SetValue('calendarId', $calendarId);
					$this->_currentModel->IsCalendarUpdatable($this->_userId, $calendarContainer);
					$this->_UpdateCalendarInfo($calendarContainer);
				}

				// Get exist events ids in webmail.
				$webmailEventsIdsList = $this->_GetEventsList($calendarId, false);
				$eventsList = $calendarContainer->GetValue('calendarEvents');
				foreach ($eventsList as $eventContainer)
				{
					$eventStringId = $eventContainer->GetValue('eventStringId');
					$webmailEvent = $this->_GetEventByStringId($eventStringId);
					$eventContainer->SetValue('calendarId', $calendarId);
					$eventContainer->SetValue('eventOwnerEmail', $this->_userAccount->Email);
					if (is_a($webmailEvent, 'EventContainer'))
					{
						$webmailEventLastModified = $webmailEvent->GetValue('eventLastModified');
						$eventLastModified = $eventContainer->GetValue('eventLastModified');
						$webmailEventId = $webmailEvent->GetValue('eventId');
						if (strtotime($eventLastModified) > strtotime($webmailEventLastModified))
						{
							$eventContainer->SetValue('eventId', $webmailEventId);
							$eventContainer->SetValue('userId', $this->_userId);
							$this->_UpdateEvent($eventContainer);
						}
						$updatedEventsIdsList[] = $webmailEventId;
					}
					else
					{
						$this->_CreateEvent($eventContainer);
					}
				}
				$eventsIdListForDelete = array_diff($webmailEventsIdsList, $updatedEventsIdsList);
				if ($eventsIdListForDelete && !empty($eventsIdListForDelete))
				{
					foreach ($eventsIdListForDelete as $eventId)
					{
						$this->_DeleteEvent($eventId);
					}
				}
			}
			else
			{
				return false;
			}
		}
		return true;
	}

	protected function _ExportIcs($obj, $isDelete = false)
	{
		if ($obj instanceof CalendarContainer)
		{
			$container = $obj;
		}
		else
		{
			$container = $this->_GetCalendar((int)$obj);
		}
		return $this->_currentModel->ExportIcs($container, $isDelete);
	}

	/**
	 * *** APPOINTMENTS ***
	 */

	/**
	 * Get appointment info the specified appointment id
	 *
	 * @param integer $appointmentId
	 * @return	AppointmentContainer
	 */
	protected function _GetAppointment($appointmentId)
	{
		return $this->_currentModel->GetAppointment($appointmentId);
	}

	/**
	 * Get all appointments for the specified event
	 *
	 * @param integer $eventId
	 * @return	array|bool array of appointment's containers or false
	 */
	protected function _GetAppointmentsList($eventId, $withOrganizer = false, $userAccount = null)
	{
		if ($withOrganizer)
		{
			if (!isset($userAccount) && !empty($this->_userAccount))
			{
				$userAccount = $this->_userAccount;
			}
		}
		return $this->_currentModel->GetAppointmentsList($eventId, $userAccount);
	}

	/**
	 * Get all appointments for each event from events list
	 *
	 * @param array of events ids
	 * @return	array|bool array of appointment's containers or false
	 */
	protected function _GetAppointmentsForEventsList($eventIds)
	{
		$appointments = array();
		$dbresult = $this->_currentModel->GetAppointmentsForEventsList($eventIds);
		if ($dbresult != false && is_array($dbresult))
		{
			foreach ($dbresult as $value)
			{
				$appointments[$value["eventId"]][$value["appointmentId"]] = $value;
			}
			return $appointments;
		}
		return false;
	}

	/**
	 * Get appointments for for the specified user
	 *
	 * @param integer $userId
	 * @return	array|bool array of appointment's containers or false
	 */
	protected function _GetAppointmentedEventsForUserId($userId)
	{
		$events = array();
		$dbresult = $this->_currentModel->GetAppointmentedEventsForUserId($userId);
		if ($dbresult != false && is_array($dbresult))
		{
			foreach ($dbresult as $value)
			{
				$events[] = $value['eventId'];
			}
			return array_unique($events);
		}
		return false;
	}


	/**
	 * Create new apointment
	 *
	 * @param AppointmentContainer $appointmentContainer
	 * @return int
	 */
	protected function _CreateAppointment(&$appointmentContainer)
	{
		$email = $appointmentContainer->GetValue('email', 'string');
		$eventId = $appointmentContainer->GetValue('eventId', 'integer');
		$hash = md5($eventId . $email . time());
		$appointmentContainer->SetValue('hash', $hash, 'string');
		$appointmentId = $this->_currentModel->CreateAppointment($appointmentContainer);
		$appointmentContainer->SetValue('appointmentId', $appointmentId, 'integer');
		return $appointmentId;
	}

	/**
	 * Update appointment by appointmentId,
	 * if appointmentId is null then call CreateAppointment
	 *
	 * @param AppointmentContainer $appointmentContainer
	 * @return bool
	 */
	protected function _UpdateAppointment($appointmentId, $accessType = null, $status = null)
	{
		$appointmentContainer = $this->calendarManager->GetAppointment($appointmentId);
		if (isset($accessType))
		{
			$appointmentContainer->SetValue('accessType', $accessType, 'integer');
		}
		if (isset($status))
		{
			$appointmentContainer->SetValue('status', $status, 'integer');
		}

		if ($appointmentContainer->assert('appointmentId', 0))
		{
			return $this->CreateAppointment($appointmentContainer);
		}
		$result = $this->_currentModel->UpdateAppointment($appointmentContainer);
		return $result;
	}

	/**
	 * Delete the one appointment.
	 *
	 * @param int|AppointmentContainer $appointment - appointment container or appointment id which will be deleted.
	 * @return bool
	 */
	protected function _DeleteAppointment($appointment)
	{
		if ($appointment instanceof AppointmentContainer)
		{
			$appointmentId = $appointment->GetValue('appointmentId');
		}
		else
		{
			$appointmentId = $appointment;
		}

		$result = $this->_currentModel->DeleteAppointment($appointmentId);
		return $result;
	}

	/**
	 * Delete all appointments for the specified event.
	 *
	 * @param int|EventContainer $event - event container or event id for which appointment will be deleted.
	 * @return bool
	 */
	protected function _DeleteAllAppointmentsForEvent($event)
	{
		if ($event instanceof EventContainer)
		{
			$eventId = $event->GetValue('eventId');
		}
		else
		{
			$eventId = $event;
		}
		$result = $this->_currentModel->DeleteAllAppointmentsForEvent($eventId);
		return $result;
	}

	/**
	 *
	 * @param <type> $calendarId
	 * @param <type> $eventContainer
	 */
	protected function _PushEvent($calendarId, $eventContainer)
	{
		return $this->_currentModel->PushEvent($calendarId, $eventContainer);
	}
	
	protected function _SetEventFunambolId($wmEvent, $funambolEventId)
	{
		return $this->_currentModel->SetEventFunambolId($wmEvent, $funambolEventId);
	}

	protected function _GetUserIdsUpdatedCalendars( $dateUpdate )
	{
		return $this->_currentModel->GetUserIdsUpdatedCalendars( $dateUpdate );
	}

}

class CalendarManagerException extends BaseManagerException
{}
