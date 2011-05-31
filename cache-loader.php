<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 *
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', dirname(__FILE__).'/');

	@header('Content-type: application/x-javascript; charset=utf-8');

	require_once(WM_ROOTPATH.'common/inc_top.php');
	require_once (WM_ROOTPATH.'common/inc_constants.php');
	require_once (WM_ROOTPATH.'common/last_modified.php');
	require_once (WM_ROOTPATH.'common/class_settings.php');

	$type = '';
	$files = array();

	$v = isset($_GET['v']) ? $_GET['v'] : '';
	if (get_type_and_files($type, $files))
	{
		@ob_start(USE_JS_GZIP ? 'obStartGzip' : 'obStartNoGzip');
		echo js_pack($files);
	}

	/* ---------- functions ------------- */

	/**
	 * @param	array	$filesArray
	 * @return	string
	 */
	function js_pack($filesArray)
	{
		$return = array();
		foreach ($filesArray as $file)
		{
			$return[] = file_load_and_pack(WM_ROOTPATH.$file);
		}

		return implode('', $return);
	}

	/**
	 * @param	string	$filename
	 * @return	string
	 */
	function file_load_and_pack($filename)
	{
		$filename = str_replace('..', '', $filename);
		$return = array();
		if (@file_exists($filename))
		{
			$return[] = text_clear(@file_get_contents($filename))."\r\n";
		}

		return implode('', $return);
	}

	/**
	 * @param	string	$string
	 * @return	string
	 */
	function text_clear($string)
	{
		/*$string = preg_replace('/\/\*(.*?)\*\//s', '', $string);*/
		/*$string = preg_replace('/\/\/[]*$/', '', $string);*/
		/*$string = preg_replace('/[\s]+/', ' ', $string);*/
		return $string;
	}

	/**
	 * @param string $type
	 * @param array $files
	 * @return bool
	 */
	function get_type_and_files(&$type, &$files)
	{
		if (isset($_GET['t']))
		{
			switch ($_GET['t']) {
				case 'login':
					$type = $_GET['t'];
					$files = array('js/login/login-screen.js');
					return true;
				case 'reg':
					$type = $_GET['t'];
					$files = array('js/login/reg-screen.js');
					return true;
				case 'reset':
					$type = $_GET['t'];
					$files = array('js/login/password-reset-screen.js');
					return true;
				case 'common':
					$type = $_GET['t'];
					$files = array(
									'js/common/common-helpers.js',
									'js/common/popups.js');
					return true;
				case 'def':
					$type = $_GET['t'];
					$files = array(
									'js/common/defines.js',
									'js/common/common-helpers.js',
									'js/common/loaders.js',
									'js/common/functions.js',
									'js/common/popups.js');
					return true;
				case 'wm':
					$type = $_GET['t'];
					$files = array(
//									'js/common/defines.js', // already loaded; first for load!
									'js/common/calendar-screen.js',
									'js/common/common-handlers.js',
									//'js/common/common-helpers.js', // already loaded
									'js/common/data-source.js',
//									'js/common/functions.js', // already loaded
//									'js/common/loaders.js', // already loaded
									'js/common/page-switcher.js',
//									'js/common/popups.js', // already loaded
									'js/common/toolbar.js',
									'js/common/variable-table.js',
									'js/common/webmail.js',

									'js/mail/autocomplete-recipients.js',
									'js/mail/folders-pane.js',
									'js/mail/html-editor.js',
									'js/mail/mail-data.js',
//									'js/mail/mail-handlers.js', // can't load because it override functions to work with CheckMail
									'js/mail/message-headers.js',
									'js/mail/message-info.js',
									'js/mail/message-line.js',
									'js/mail/message-list-prototype.js',
									'js/mail/message-list-central-pane.js',
									'js/mail/message-list-central-screen.js', // need to load after message-list-prototype.js
									'js/mail/message-list-display.js',
									'js/mail/message-list-top-screen.js', // need to load after message-list-prototype.js
									'js/mail/new-message-screen.js',
									'js/mail/message-reply-pane.js', // need to load after new-message-screen.js
									'js/mail/resizers.js',
									'js/mail/swfupload.js',
									'js/mail/view-message-screen.js');
					return true;
				case 'wmp':
					$type = $_GET['t'];
					$files = array('js/mail/mail-handlers.js');
					return true;
				case 'cont':
					$type = $_GET['t'];
					$files = array('js/contacts/contact-line.js',
									'js/contacts/contacts-data.js',
									'js/contacts/contacts-handlers.js',
									'js/contacts/contacts-screen.js',
									'js/contacts/edit-contact.js',
									'js/contacts/edit-group.js',
									'js/contacts/import.js',
									'js/contacts/view-contact.js',
									'js/settings/account-list.js',
									'js/settings/account-properties.js',
									'js/settings/autoresponder.js',
									'js/settings/calendar.js',
									'js/settings/common.js',
									'js/settings/defines-calendar.js',
									'js/settings/filters.js',
									'js/settings/folders.js',
									'js/settings/settings-data.js',
									'js/settings/signature.js',
									'js/settings/user-settings-screen.js',
									'js/settings/mobile-sync.js');
					return true;
				case 'cal':
					$type = $_GET['t'];
					$files = array('calendar/js/cal.lib.js',
									'calendar/js/cal.userforms.js',
									'calendar/js/cal.class.calendars.js',
									'calendar/js/cal.class.eventform.js',
									'calendar/js/cal.class.sharingform.js',
									'calendar/js/cal.class.chooseform.js',
									'calendar/js/cal.class.reminder.js',
									'calendar/js/cal.class.appointment.js',
									'calendar/js/cal.class.selection.js'
								);
					return true;
				case 'cal_f':
					$type = $_GET['t'];
					$files = array('calendar/js/cal.functions.js',
									'calendar/js/cal.class.grid.js',
									'calendar/js/cal.class.calendartable.js');

					return true;
				case 'cal_p':
					$type = $_GET['t'];
					$files = array('calendar/js/pub.lib.js', 'calendar/js/pub.userforms.js');
					return true;
			}
		}

		return false;
	}
