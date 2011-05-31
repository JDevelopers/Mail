<?php

defined('CONTACT_PRIMARY_EMAIL_HOME') || define('CONTACT_PRIMARY_EMAIL_HOME', 0);
defined('CONTACT_PRIMARY_EMAIL_BUSSINESS') || define('CONTACT_PRIMARY_EMAIL_BUSSINESS', 1);
defined('CONTACT_PRIMARY_EMAIL_OTHER') || define('CONTACT_PRIMARY_EMAIL_OTHER', 2);

defined('CONTACT_VCARD_VERSION') || define('CONTACT_VCARD_VERSION', '3.0');

$vcardParseMap = array(
	'tokens' => array(),
	'enclosed' => array(
		'VCARD' => array(
			'containerName' => 'ContactContainerWithSingleGroup',
			'tokens' => array(
				//'X-AL-WMP-ADDRID' => 'IdAddress',
				//'X-AL-WMP-GROUPID' => 'GroupId',
				'X-WR-GROUPNAME' => 'GroupName',
				
				'EMAIL;TYPE=internet;TYPE=home' => 'HomeEmail',
				'FN' => 'FullName',
				'NOTE' => 'Notes',
				'TEL;TYPE=home' => 'HomePhone',
				'TEL;TYPE=home;TYPE=fax' => 'HomeFax',
				'TEL;TYPE=home;TYPE=cell' => 'HomeMobile',
				'URL;TYPE=home' => 'HomeWeb',

				'EMAIL;TYPE=internet;TYPE=work' => 'BusinessEmail',
				'TITLE' => 'BusinessJobTitle',
				'TEL;TYPE=work' => 'BusinessPhone',
				'TEL;TYPE=work;TYPE=fax' => 'BusinessFax',
				'URL;TYPE=work' => 'BusinessWeb',

				'EMAIL;TYPE=internet' => 'OtherEmail',

				'UID' => 'StrId',
				'X-WR-GROUPID' => 'GroupStrId',
			),
			'static' => array(
				'VERSION' => CONTACT_VCARD_VERSION,
			),
			'tokensWithSpecialTreatmentImport' => array(
				'ADR;TYPE=home' => '_addrImportForm',
				'ADR;TYPE=work' => '_addrImportForm',
				'ORG' => '_orgImportForm',
				'BDAY' => '_bdayImportForm',
				'REV' => '_utcDateImportForm',
			),
		),
	),
);