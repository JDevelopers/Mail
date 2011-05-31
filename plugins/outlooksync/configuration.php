<?php

if (!defined('OUTLOOK_SYNC_HEADER_LOGIN'))
{
	define('OUTLOOK_SYNC_HEADER_LOGIN', 'x-wmp-login');
}
if (!defined('OUTLOOK_SYNC_HEADER_EMAIL'))
{
	define('OUTLOOK_SYNC_HEADER_EMAIL', 'x-wmp-email');
}
if (!defined('OUTLOOK_SYNC_HEADER_PASSWORD'))
{
	define('OUTLOOK_SYNC_HEADER_PASSWORD', 'x-wmp-password');
}
if (!defined('OUTLOOK_SYNC_HEADER_LAST_SYNC_DATE'))
{
	define('OUTLOOK_SYNC_HEADER_LAST_SYNC_DATE', 'x-wmp-lastdate');
}
if (!defined('OUTLOOK_SYNC_HEADER_CALENDAR_ERROR'))
{
	define('OUTLOOK_SYNC_HEADER_CALENDAR_ERROR', 'x-wmp-calendar-error');
}
if (!defined('OUTLOOK_SYNC_HEADER_CALENDAR_ERROR_COUNT'))
{
	define('OUTLOOK_SYNC_HEADER_CALENDAR_ERROR_COUNT', 'x-wmp-calendar-error-count');
}
if (!defined('OUTLOOK_SYNC_HEADER_SYNC_VERSION'))
{
	define('OUTLOOK_SYNC_HEADER_SYNC_VERSION', 'x-wmp-sync-version');
}
if (!defined('X_WMP_RESPONSE_STATUS_OK'))
{
	define('X_WMP_RESPONSE_STATUS_OK', 0);
}
if (!defined('X_WMP_RESPONSE_STATUS_ERROR_AUTH'))
{
	define('X_WMP_RESPONSE_STATUS_ERROR_AUTH', 1);
}
if (!defined('X_WMP_RESPONSE_STATUS_ERROR_DB_FAULT'))
{
	define('X_WMP_RESPONSE_STATUS_ERROR_DB_FAULT', 2);
}
if (!defined('X_WMP_RESPONSE_STATUS_ERROR_ACTION'))
{
	define('X_WMP_RESPONSE_STATUS_ERROR_ACTION', 3);
}
if (!defined('X_WMP_RESPONSE_STATUS_ERROR_DATA'))
{
	define('X_WMP_RESPONSE_STATUS_ERROR_DATA', 4);
}
if (!defined('OUTLOOK_SYNC_DATA_NONE'))
{
	define('OUTLOOK_SYNC_DATA_NONE', 0);
}
if (!defined('OUTLOOK_SYNC_DATA_CALENDAR'))
{
	define('OUTLOOK_SYNC_DATA_CALENDAR', 1);
}
if (!defined('OUTLOOK_SYNC_DATA_CONTACTS'))
{
	define('OUTLOOK_SYNC_DATA_CONTACTS', 2);
}
if (!defined('SYNC_ACTION_GET'))
{
	define('SYNC_ACTION_GET', 'GET');
}
if (!defined('SYNC_ACTION_POST'))
{
	define('SYNC_ACTION_POST', 'POST');
}
