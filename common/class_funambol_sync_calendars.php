<?php

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'./../'));

require_once WM_ROOTPATH.'api/calendar/calendar_manager.php';
require_once WM_ROOTPATH.'api/calendar/funambol_calendar_manager.php';
require_once WM_ROOTPATH.'calendar/containers/funambol_event_container.php';
require_once WM_ROOTPATH.'common/inc_funambol_constants.php';

/**
 *
 */
class FunambolSyncCalendars extends FunambolSyncBase
{
	/**
	 * @var Account
	 */
	var $_account	= NULL;

	var $_settings	= NULL;

	public function  __construct(&$account, &$settings)
	{
		$this->_account  = $account;
		$this->_settings = $settings;
	}

	private function log( $str )
	{
//		@file_put_contents('c:\qwe.log', $str."\n", FILE_APPEND);
		echo $str."<br/>\n";
	}


	/**
	 *
	 * @param <type> $account
	 * @param <type> $settings
	 * @return <type>
	 */

	public function performSync()
	{
		$user_id = $this->_account->IdUser;
	
		$fnUserId = $this->_account->Email;

$this->log("-----sync calendars for user_id=$user_id fnUserId=$fnUserId");


		$wmCalendarManager = new CalendarManager($user_id);
		$fnCalendarManager = new FunambolCalendarManager($user_id);

		if (!$wmCalendarManager->InitManager() || !$fnCalendarManager->InitManager())
		{
			return false;
		}

		$wmCalendarManager->InitAccount($this->_account);
		
		// in minutes. with dayLightSavingTime adjustment
		// WARNING
		// this is different from contacts
		$accountOffset =	(
							$this->_settings->AllowUsersChangeTimeZone ?
							$this->_account->GetDefaultTimeOffset() :
							$this->_account->GetDefaultTimeOffset($this->_settings->DefaultTimeZone)
							);// - (int)date('I')*60;

		// get full list of calendars from account
		$wmCalendars = $wmCalendarManager->GetCalendarsList(true, true);

		$funambolEventIds	= array();
		$minCalendarId		= NULL;
		foreach ($wmCalendars as $wmCalendar)
		{
			$wmEvents = $wmCalendar->GetValue('calendarEvents');

//echo "<pre>";
//var_dump( $wmEvents );
//echo "</pre>";
//continue;

			if($minCalendarId == NULL) {
				$minCalendarId = $wmCalendar->GetValue('calendarId','int');
			}
			if($wmCalendar->GetValue('calendarId','int') < $minCalendarId) {
				$minCalendarId = $wmCalendar->GetValue('calendarId','int');
			}

			foreach( $wmEvents as $wmEvent )
			{
/*
				if($wmEvent->IsEventRepeat() )
				{
					$internalRepeatForm = $wmEvent->GetInternalRepeatForm();
					if(!is_null($internalRepeatForm)) {
						echo "<br/>&nbsp;&nbsp;RepeatContainer";
						echo "<br/>&nbsp;&nbsp;eventTimeFrom=".$internalRepeatForm->GetValue('eventTimeFrom');
						echo "<br/>&nbsp;&nbsp;repeatPeriod=".$internalRepeatForm->GetValue('repeatPeriod');
						echo "<br/>&nbsp;&nbsp;repeatOrder=".$internalRepeatForm->GetValue('repeatOrder');
						echo "<br/>&nbsp;&nbsp;repeatNum=".$internalRepeatForm->GetValue('repeatNum');
						echo "<br/>&nbsp;&nbsp;repeatUntil=".$internalRepeatForm->GetValue('repeatUntil');
						echo "<br/>&nbsp;&nbsp;repeatWeekNumber=".$internalRepeatForm->GetValue('repeatWeekNumber');
						echo "<br/>&nbsp;&nbsp;repeatEnd=".$internalRepeatForm->GetValue('repeatEnd');
						echo "<br/>&nbsp;&nbsp;repeatSun=".$internalRepeatForm->GetValue('repeatSun');
						echo "<br/>&nbsp;&nbsp;repeatMon=".$internalRepeatForm->GetValue('repeatMon');
						echo "<br/>&nbsp;&nbsp;repeatTue=".$internalRepeatForm->GetValue('repeatTue');
						echo "<br/>&nbsp;&nbsp;repeatWed=".$internalRepeatForm->GetValue('repeatWed');
						echo "<br/>&nbsp;&nbsp;repeatThu=".$internalRepeatForm->GetValue('repeatThu');
						echo "<br/>&nbsp;&nbsp;repeatFri=".$internalRepeatForm->GetValue('repeatFri');
						echo "<br/>&nbsp;&nbsp;repeatSat=".$internalRepeatForm->GetValue('repeatSat');
						echo "<br/>&nbsp;&nbsp;excluded=".$internalRepeatForm->GetValue('excluded');
					}
				}
 */
				if( $wmEvent->GetValue('FunambolEventId','int') == NULL )
				{
					// new event in WM, push event to FN
					// FIXME, calendar name
					$fnEventContainer = $this->ConvertWMToFunambolEventContainer($fnUserId, "", $wmEvent, TRUE, $accountOffset );
					$funambolEventId = $fnCalendarManager->ReplaceEvent($fnEventContainer);
					$wmCalendarManager->SetEventFunambolId($wmEvent, $funambolEventId);

					array_push($funambolEventIds, $funambolEventId);


$this->log("new event in WM. wmEventId=" . $wmEvent->GetValue( 'eventId' ) . " fnEventId=$funambolEventId");

				}
				else
				{
					// this event was already synchronized with FN
					
					$fnEvent = $fnCalendarManager->GetEvent($wmEvent->GetValue('FunambolEventId','int'));
					// if wmEvent is updated lately - overwrite it in FN

$this->log("known event in WM. wmEventId=" . $wmEvent->GetValue( 'eventId' ) . " fnEventId=" . $fnEvent->GetValue('id') );

					// GMT modification date as 2000-12-31 23:59:59
					$wmDateModified			= $wmEvent->GetValue('eventLastModified');
					// seconds from Epoch for this date
					$wmTimestampModified	= strtotime( $wmDateModified );
					// local server's timestamp - offset to get GMT as seconds from Epoch
					$fnTimestampModified	= $this->ConvertFNtoWMTimestamp( $fnEvent->GetValue('last_update'), TRUE );



					if($wmTimestampModified == $fnTimestampModified)
					{
$this->log("known event -- unmodified");

						array_push($funambolEventIds, $wmEvent->GetValue('FunambolEventId','int'));
					}
					else if($wmTimestampModified > $fnTimestampModified)
					{
						// push event to FN
						// FIXME, calendar name
$this->log("known event -- push from WM to FN");
						$fnEventContainer = $this->ConvertWMToFunambolEventContainer($fnUserId, "", $wmEvent, TRUE, $accountOffset);
						$fnCalendarManager->ReplaceEvent($fnEventContainer);

						array_push($funambolEventIds, $wmEvent->GetValue('FunambolEventId','int'));
					}
					else
					{
						// TODO
$this->log("known event -- pull from FN to WM");
						// the simplest way - delete event and create new one;
						$wmCalendarManager->DeleteEvent($wmEvent,TRUE,TRUE);
					}
				} // if(new or old event)
			} // foreach ($wmEvents)
		} // foreach( $wmCalendars )

		// $funambolEventIds contains all events that are in WM
		// pull data from FN which are not in WM already
		$fnCalendarManager->SetUserAccount($fnUserId);
$this->log("Pulling events from FN to WM. funambolEventIds=".join(',',$funambolEventIds));
		$fnEvents = $fnCalendarManager->GetEventsListExcluded($funambolEventIds);

		foreach($fnEvents as $fnEvent)
		{
$this->log("adding new event from FN to WM subj=".$fnEvent->GetValue('subject')." id=".$fnEvent->GetValue('id') );
$this->log("status of FN event:".$fnEvent->GetValue('status'));
$this->log("adding to calendar:".$minCalendarId);

			$wmEvent = $this->ConvertFunambolToWMEventContainer( $fnEvent, TRUE, $accountOffset );

			// FIXME, calendar id
			$wmEvent->SetValue('calendarId', $minCalendarId);
			$wmEvent->ConvertToRRule();

//			echo "<pre>";
//			var_dump($wmEvent);
//			echo "</pre>";


			if ($fnEvent->GetValue('status') != FUNAMBOL_STATUS_DELETED)
			{
				$wmCalendarManager->CreateEvent($wmEvent);
			}
		} // foreach($fnEvents)

		return true;
	} /// function sync

	/***************************************************************************
	 * *************************************************************************
	 * FUNCTIONS
	 * *************************************************************************
	 * *************************************************************************
	 */

	/**
	 *
	 * @param <type> $fnEventContainer
	 * @param <type> $updateDate
	 * @param <type> $accountOffset
	 * @return <type> 
	 */
	private function ConvertFunambolToWMEventContainer( &$fnEventContainer,$updateDate = FALSE, $accountOffset = 0 )
	{
		$wmEventContainer = new EventContainer();

/*
		$this->_container['calendarId'] = null;
		$this->_container['eventId'] = null;
 */

		$wmEventContainer->SetValue('FunambolEventId', $fnEventContainer->GetValue('id','int'));

//		$fnEventContainer->SetValue('userid', $fnUserId);
		if($updateDate)
		{
			$fnDateModified = $this->ConvertFNtoWMTimestamp( $fnEventContainer->GetValue('last_update'), TRUE );
			$wmEventContainer->SetValue('eventLastModified', $fnDateModified);
		}
		else
		{
			//$wmContactContainer->_container['DateCreated']	= null;
			//$wmContactContainer->_container['DateModified']	= null;
		}


		$wmEventContainer->SetValue('eventAllDay',		$fnEventContainer->GetValue('all_day'));
		$wmEventContainer->SetValue('eventText',		$fnEventContainer->GetValue('body'));

$this->log("fnEventContainer->GetValue('dstart')=".$fnEventContainer->GetValue('dstart'));

		$wmEventContainer->SetValue('eventTimeFrom',
				date("Y-m-d H:i:s", strtotime($fnEventContainer->GetValue('dstart')) - date('Z')));
		$wmEventContainer->SetValue('eventTimeTill',
				date("Y-m-d H:i:s", strtotime($fnEventContainer->GetValue('dend')) - date('Z')));

//		$fnEventContainer->SetValue('folder',	"DEFAULT_FOLDER" );//\\" . $wmCalendarName);
//		$this->_container['reminder_time'] = null;
//		$fnEventContainer->SetValue('reminder'] = 0;
//		$fnEventContainer->SetValue('reminder_repeat_count'] = 0;
//		$fnEventContainer->SetValue('sensitivity'] = 0;
		$wmEventContainer->SetValue('eventName',		$fnEventContainer->GetValue('subject'));

		if($fnEventContainer->GetValue('rec_type','int')<0)
		{
			// not repeatable event
		}
		else
		{
			$internalRepeatForm = new RepeatContainer();
			$internalRepeatForm->SetValue('repeatOrder',	$fnEventContainer->GetValue('rec_interval'));
			switch($fnEventContainer->GetValue('rec_type','int'))
			{
				case 0:
					// daily
					$internalRepeatForm->SetValue('repeatPeriod',0);
					break;
				case 1:
					// weekly
					$internalRepeatForm->SetValue('repeatPeriod',1);
					$this->FillDaysFromMask($internalRepeatForm,$fnEventContainer->GetValue('rec_day_of_week_mask'));
					break;
				case 2:
					// monthly, each 4-th of May
					$internalRepeatForm->SetValue('repeatPeriod',2);
					$internalRepeatForm->SetValue('repeatWeekNumber', $fnEventContainer->GetValue('rec_instance','int') - 1);
					$this->FillDaysFromMask($internalRepeatForm,$fnEventContainer->GetValue('rec_day_of_week_mask'));
					break;
				case 3:
					// monthly, each Second Sunday
					$internalRepeatForm->SetValue('repeatPeriod',2);
					$internalRepeatForm->SetValue('repeatWeekNumber', $fnEventContainer->GetValue('rec_instance','int') - 1);
					$this->FillDaysFromMask($internalRepeatForm,$fnEventContainer->GetValue('rec_day_of_week_mask'));
					break;
				case 4:
					break;
				case 5:
					$internalRepeatForm->SetValue('repeatPeriod',3);
					$internalRepeatForm->SetValue('repeatWeekNumber', $fnEventContainer->GetValue('rec_instance','int') - 1);
					$this->FillDaysFromMask($internalRepeatForm,$fnEventContainer->GetValue('rec_day_of_week_mask'));
					break;
				case 6:
					$internalRepeatForm->SetValue('repeatPeriod',3);
					$internalRepeatForm->SetValue('repeatWeekNumber', $fnEventContainer->GetValue('rec_instance','int') - 1);
					$this->FillDaysFromMask($internalRepeatForm,$fnEventContainer->GetValue('rec_day_of_week_mask'));
	//							$fnEventContainer->SetValue('rec_interval', 12);
	//							$fnEventContainer->SetValue('rec_month_of_year', date( "m", strtotime($wmEventContainer->GetValue('eventTimeFrom'))));
					break;
			} // switch

			if($fnEventContainer->GetValue('rec_no_end_date','int'))
			{
				$internalRepeatForm->SetValue('repeatEnd',0);
$this->log("<h1>no end</h1>");
			} else if(($fnEventContainer->GetValue('rec_end_date_pattern')!=NULL)
					&&(strlen($fnEventContainer->GetValue('rec_end_date_pattern'))>0)
			)
			{
$this->log("<h1>end date</h1>");
				$internalRepeatForm->SetValue('repeatEnd',2);
				$internalRepeatForm->SetValue('repeatUntil', date("Y-m-d H:i:s",strtotime($fnEventContainer->GetValue('rec_end_date_pattern'))));
			} else if($fnEventContainer->GetValue('rec_occurrences','int'))
			{
$this->log("<h1>end occurences</h1>");
				$internalRepeatForm->SetValue('repeatEnd',1);
				$internalRepeatForm->SetValue('repeatNum', $fnEventContainer->GetValue('rec_occurrences'));
			}
			$wmEventContainer->SetInternalRepeatForm($internalRepeatForm);
		} // if( repeatable event )

		return $wmEventContainer;
	} // function

	/**
	 *
	 * @param <type> $fnUserId
	 * @param <type> $wmCalendarName
	 * @param <type> $wmEventContainer
	 * @param <type> $updateDate
	 * @param <type> $accountOffset
	 * @return <type> 
	 */
	private function ConvertWMToFunambolEventContainer($fnUserId, $wmCalendarName, &$wmEventContainer,$updateDate = FALSE, $accountOffset = 0)
	{
		$fnEventContainer = new FunambolEventContainer();

		$fnEventContainer->SetValue('id', $wmEventContainer->GetValue('FunambolEventId'));

		$fnEventContainer->SetValue('userid', $fnUserId);
		if($updateDate)
		{
			$wmDateModified = $wmEventContainer->GetValue('eventLastModified'); // as 2000-12-31 23:59:59 -pseudo-GMT
			$wmTimestampModified = strtotime($wmDateModified);
			$fnTimestampModified = $wmTimestampModified + date('Z'); // as seconds from Epoch in localtime
			$fnEventContainer->SetValue('last_update', "".$fnTimestampModified."111");
		}
		else
		{
			$fnEventContainer->SetValue('last_update', "".time()."222");
		}

		if($wmEventContainer->GetValue('eventIsDeleted'))
		{
			$fnEventContainer->SetValue('status', FUNAMBOL_STATUS_DELETED);
		}
		else
		{
			$fnEventContainer->SetValue('status', FUNAMBOL_STATUS_UPDATED);
		}

//		$fnEventContainer->SetValue('type'] = 1)
		$fnEventContainer->SetValue('all_day',	$wmEventContainer->GetValue('eventAllDay'));
		$fnEventContainer->SetValue('body',		$wmEventContainer->GetValue('eventText'));
//		$fnEventContainer->SetValue('busy_status'] = 2;
//		$fnEventContainer->SetValue('categories'] = '';
//		$fnEventContainer->SetValue('companies'] = '';
//		$this->_container['birthday'] = null;
//		$fnEventContainer->SetValue('duration'] = 0;
		$fnEventContainer->SetValue('dstart',	date("Y-m-d H:i:s",strtotime($wmEventContainer->GetValue('eventTimeFrom')) + ($accountOffset*60)));
		$fnEventContainer->SetValue('dend',		date("Y-m-d H:i:s",strtotime($wmEventContainer->GetValue('eventTimeTill')) + ($accountOffset*60)));
		$fnEventContainer->SetValue('folder',	"DEFAULT_FOLDER" );//\\" . $wmCalendarName);
//		$fnEventContainer->SetValue('importance'] = 5;
//		$fnEventContainer->SetValue('location'] = null;
//		$fnEventContainer->SetValue('meeting_status'] = 0;
//		$this->_container['mileage'] = 0;
//		$this->_container['reminder_time'] = null;
//		$fnEventContainer->SetValue('reminder'] = 0;
//		$this->_container['reminder_sound_file'] = null;
//		$fnEventContainer->SetValue('reminder_options'] = 0;
//		$fnEventContainer->SetValue('reminder_repeat_count'] = 0;
//		$fnEventContainer->SetValue('sensitivity'] = 0;
		$fnEventContainer->SetValue('subject',	$wmEventContainer->getValue('eventName'));

		if( $wmEventContainer->IsEventRepeat())
		{
			$internalRepeatForm = $wmEventContainer->GetInternalRepeatForm();
			if(!is_null($internalRepeatForm)) {

				$fnEventContainer->SetValue('rec_interval',	$internalRepeatForm->GetValue('repeatOrder'));
				
				// convert RepeatPeriod into rec_type
				switch( $internalRepeatForm->GetValue('repeatPeriod') ) {
					case 0:
						// daily
						$fnEventContainer->SetValue('rec_type', 0);
						break;
					case 1:
						// weekly
						$fnEventContainer->SetValue('rec_type', 1);
						$mask = $this->GetMaskFromInternalRepeatForm($internalRepeatForm);
						$fnEventContainer->SetValue('rec_day_of_week_mask', $mask);
						break;
					case 2:
						// monthly
//		$fnEventContainer->SetValue('rec_month_of_year'] = 0;
//		$fnEventContainer->SetValue('rec_day_of_month'] = 0;
//
						// each 4-th monday - here is 4-th
						$fnEventContainer->SetValue('rec_instance', $internalRepeatForm->GetValue('repeatWeekNumber','int') + 1);
						$mask = $this->GetMaskFromInternalRepeatForm($internalRepeatForm);
						$fnEventContainer->SetValue('rec_day_of_week_mask', $mask);
						if($mask>0)
						{
							$fnEventContainer->SetValue('rec_type', 3);
						}
						else
						{
							$fnEventContainer->SetValue('rec_type', 2);
						}
						break;
					case 3:
						// yearly
//		$fnEventContainer->SetValue('rec_month_of_year'] = 0;
//		$fnEventContainer->SetValue('rec_day_of_month'] = 0;
//					echo "<br/>&nbsp;&nbsp;repeatWeekNumber=".$internalRepeatForm->GetValue('repeatWeekNumber');
						// each 4-th monday - here is 4-th
						$fnEventContainer->SetValue('rec_instance', $internalRepeatForm->GetValue('repeatWeekNumber','int') + 1);
						$mask = $this->GetMaskFromInternalRepeatForm($internalRepeatForm);
						$fnEventContainer->SetValue('rec_day_of_week_mask', $mask);
						if($mask>0)
						{
							$fnEventContainer->SetValue('rec_interval', 12);
							$fnEventContainer->SetValue('rec_month_of_year', date( "m", strtotime($wmEventContainer->GetValue('eventTimeFrom'))));
							$fnEventContainer->SetValue('rec_type', 6);
						}
						else
						{
							$fnEventContainer->SetValue('rec_type', 5);
						}
						break;
				} // switch( repeatPeriod )

//					echo "<br/>&nbsp;&nbsp;excluded=".$internalRepeatForm->GetValue('excluded');

				$fnEventContainer->SetValue('rec_start_date_pattern', date( "Ymd\THis", strtotime($wmEventContainer->GetValue('eventTimeFrom'))));

$this->log("<br/>&nbsp;&nbsp;repeatEnd=".$internalRepeatForm->GetValue('repeatEnd'));
				switch($internalRepeatForm->GetValue('repeatEnd')) {
					case 0:
						// no end date
						$fnEventContainer->SetValue('rec_no_end_date', 1);
						break;
					case 1:
						// end after N occurences
						$fnEventContainer->SetValue('rec_occurrences',	$internalRepeatForm->GetValue('repeatNum'));
						break;
					case 2:
						// end by DATE
//							echo "<br/>&nbsp;&nbsp;repeatUntil=".;
						$fnEventContainer->SetValue('rec_end_date_pattern', date( "Ymd\THis", strtotime($internalRepeatForm->GetValue('repeatUntil'))));
						break;
				} // switch( repeatEnd )
			} // if( event repeat )
		} // if( repeat event );

		return $fnEventContainer;
	} // function


	/**
	 *
	 * @param <type> $internalRepeatForm
	 * @return <type> 
	 */
	private function GetMaskFromInternalRepeatForm(&$internalRepeatForm)
	{
		$mask = 0;
		if($internalRepeatForm->GetValue('repeatSun')) {
			$mask |= 1;
		}
		if($internalRepeatForm->GetValue('repeatMon')) {
			$mask |= 2;
		}
		if($internalRepeatForm->GetValue('repeatTue')) {
			$mask |= 4;
		}
		if($internalRepeatForm->GetValue('repeatWed')) {
			$mask |= 8;
		}
		if($internalRepeatForm->GetValue('repeatThu')) {
			$mask |= 16;
		}
		if($internalRepeatForm->GetValue('repeatFri')) {
			$mask |= 32;
		}
		if($internalRepeatForm->GetValue('repeatSat')) {
			$mask |= 64;
		}

		return $mask;
	} // function
	
	private function FillDaysFromMask(&$internalRepeatForm,$mask)
	{
		if($mask&1) {
			$internalRepeatForm->SetValue('repeatSun',1);
		}
		if($mask&2) {
			$internalRepeatForm->SetValue('repeatMon',1);
		}
		if($mask&4) {
			$internalRepeatForm->SetValue('repeatTue',1);
		}
		if($mask&8) {
			$internalRepeatForm->SetValue('repeatWed',1);
		}
		if($mask&16) {
			$internalRepeatForm->SetValue('repeatThu',1);
		}
		if($mask&32) {
			$internalRepeatForm->SetValue('repeatFri',1);
		}
		if($mask&64) {
			$internalRepeatForm->SetValue('repeatSat',1);
		}
	} // function
} // class SyncFunambolCalendars
