<?php
/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 *
 */

/**
 * Class SyncContacts.
 */

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));
require_once(WM_ROOTPATH.'plugins/outlooksync/class_sync_base.php');

class SyncCalendar extends SyncBase
{
	/**
	 * @param	Account	$account
	 * @param	string	$action
	 */
	public function __construct($account, $action)
	{
		parent::__construct();
		
		$this->managerPath = WM_ROOTPATH.'api/calendar/calendar_manager.php';
		$this->managerName = 'CalendarManager';
		$this->init($account, $action);
		$this->manager->SetActiveUserId($this->userId);
		$this->manager->SetUserAccount($this->account);
	}

	public function processing()
	{
		if (is_object($this->manager) && $this->manager->InitManager())
		{
			switch ($this->action)
			{
				case SYNC_ACTION_GET:
					$this->_exportIcal();
				break;
				case SYNC_ACTION_POST:
					$this->_importIcal();
				break;
				default:
					$this->responseStatus = X_WMP_RESPONSE_STATUS_ERROR_ACTION;
				break;
			}
		}
	}

	private function _exportIcal()
	{
		$calendarsList = $this->manager->GetCalendarsList(true, true);
		if (is_array($calendarsList))
		{
			foreach ($calendarsList as $calendar)
			{
				$this->response .= $this->manager->ExportIcs($calendar);
			}
			$this->responseStatus = X_WMP_RESPONSE_STATUS_OK;
		}
		else
		{
			$this->responseStatus = X_WMP_RESPONSE_STATUS_ERROR_DB_FAULT;
		}
	}

	private function _importIcal()
	{
		$icsText = implode('', file('php://input'));

		if (class_exists('CLog'))
		{
			$log =& CLog::CreateInstance();
			$log->WriteLine('Sync: Import Calendar[Body] / LENGTH = '.strlen($icsText));
			$log->WriteLine('Sync: Import Calendar[Body] / RAW = '."\r\n".$icsText);
		}
		
		$tmpfname = tempnam('/tmp', '');
		file_put_contents($tmpfname, $icsText);
		$result = $this->manager->ImportIcs($tmpfname);
		unlink($tmpfname);
		$this->responseStatus = ($result) ? X_WMP_RESPONSE_STATUS_OK : X_WMP_RESPONSE_STATUS_ERROR_DB_FAULT;
		$this->response = $result;
	}
}