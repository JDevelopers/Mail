<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));

final class ContactCreator
{
	static $instance;

	private function ContactCreator() {}
	
	/**
	 * @param	Account		$account
	 * @param	Settings	$settings
	 * @return	ContactsDbDriver
	 */
	static public function &CreateContactStorage($account, $settings)
	{
		if (!self::$instance)
		{
			if (USE_LDAP_CONTACT)
			{
				require_once(WM_ROOTPATH.'common/contacts/ldap.php');
				self::$instance = new ContactsLDAPDriver($account, $settings);
			}
			else
			{
				require_once(WM_ROOTPATH.'common/contacts/db.php');
				self::$instance = new ContactsDbDriver($account, $settings);
			}
		}
		else
		{
			self::$instance->InitMain($account, $settings);
		}
		
		return self::$instance;
	}
}
	