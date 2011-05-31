<?php

require_once (WM_ROOTPATH.'/core/base/base_ics_formatter.php');

class ContactVCardFormatter extends BaseIcsFormatter
{
	const MAGIC_CONTACT_STR_ID_PREFIX = '040000008200E00074C5B7101A82E008';
	const MAGIC_GROUP_STR_ID_PREFIX = '5765624D61696C50726F';

	protected $_name = 'VCARD';
	protected $_value = '';
	
	public function __construct()
	{
		require WM_ROOTPATH.'webmail/helpers/vcard/configuration.php';
		
		$this->_map = array(
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
				'X-WR-GROUPID' => 'GroupStrId'
			),
			
			'static' => array(
				'VERSION' => CONTACT_VCARD_VERSION,
			),

			'specialInsideTreatments' => array(),
			
			'specialTreatments' => array(),

			'tokensWithSpecialTreatment' => array(
				//'UID' => array('_contactStrIdForm', 'IdAddress', 'StrId'),
				//'X-WR-GROUPID' => array('_groupStrIdForm', 'GroupId', 'GroupStrId'),
				'ADR;TYPE=home' => array('_addrForm', 
					null, 'HomeStreet', 'HomeCity',	'HomeState', 'HomeZip', 'HomeCountry'),
				'ADR;TYPE=work' => array('_addrForm',
					'BusinessOffice', 'BusinessStreet', 'BusinessCity',
					'BusinessState', 'BusinessZip', 'BusinessCountry'),
				'ORG' => array('_orgForm', 'BusinessDepartment', 'BusinessCompany'),
				'BDAY' => array('_bdayForm', 'BirthdayDay', 'BirthdayMonth', 'BirthdayYear'),
				'REV' => array('_utcDateForm', 'DateModified')
			)
		);
	}

	protected function _utcDateForm($token, $dateFieldName)
	{
		$date = $this->_container->GetValue($dateFieldName, 'string');
		$dateArr = $this->dateParse($date);
		if (is_array($dateArr) && isset($dateArr['month'], $dateArr['day'], $dateArr['year'],
				$dateArr['hour'], $dateArr['minute'], $dateArr['second']))
		{
			$result =
				date('Ymd', mktime(0, 0, 0, $dateArr['month'], $dateArr['day'], $dateArr['year'])).'T'.
				date("His", mktime($dateArr['hour'], $dateArr['minute'], $dateArr['second'], 0, 0, 0)).'Z';

			return $token.':'.$this->_escapeValue($result);
		}
		return '';
	}

	protected function _contactStrIdForm($token, $contactIdFieldName, $contactStrIdFieldName)
	{
		$contactStrId =  $this->_container->GetValue($contactStrIdFieldName, 'string');
		if (empty($contactStrId))
		{
			$contactId = $this->_container->GetValue($contactIdFieldName, 'int');
			$contactStrId = self::MAGIC_CONTACT_STR_ID_PREFIX.$contactId;
		}

		return $token.':'.$this->_escapeValue($contactStrId);
	}

	protected function _groupStrIdForm($token, $groupIdFieldName, $groupStrIdFieldName)
	{
		$groupId = $this->_container->GetDefaultedValue($groupIdFieldName, 'int', 0);
		if ($groupId > 0)
		{
			$groupStrId =  $this->_container->GetDefaultedValue($groupStrIdFieldName, 'string', '');
			if (empty($groupStrId))
			{
				$groupStrId = self::MAGIC_GROUP_STR_ID_PREFIX.$groupId;
			}
			return $token.':'.$this->_escapeValue($groupStrId);
		}
		return '';
	}
	
	protected function _bdayForm($token, $dayFieldName, $monthFieldName, $yearFieldName)
	{
		$day = $this->_container->GetValue($dayFieldName, 'int');
		$month = $this->_container->GetValue($monthFieldName, 'int');
		$year = $this->_container->GetValue($yearFieldName, 'int');

		if ($day > 0 && $month > 0 && $year > 0)
		{
			return $token.':'.$year.'-'.$month.'-'.$day;
		}
		
		return '';
	}

	protected function _orgForm($token, $departmentFieldName, $companyFieldName)
	{
		$department = $this->_container->GetValue($departmentFieldName);
		$company = $this->_container->GetValue($companyFieldName);

		$result = '';
		if (strlen($department) > 0)
		{
			$result .= sprintf('%s;%s', $this->_escapeValue($company), $this->_escapeValue($department));
		}
		else if (strlen($company) > 0)
		{
			$result .= $company;
		}
		
		return (strlen($result) > 0 ) ? $token.':'.$result : '';
	}

	protected function _addrForm($token,
			$officeFieldName = null, $streetFieldName = null, $cityFieldName = null,
			$stateFieldName = null,	$zipFieldName = null, $countryFieldName = null)
	{
		$office		= (null !== $officeFieldName) ? $this->_container->GetValue($officeFieldName) : '';
		$street		= (null !== $streetFieldName) ? $this->_container->GetValue($streetFieldName) : '';
		$city		= (null !== $cityFieldName)	? $this->_container->GetValue($cityFieldName) : '';
		$state		= (null !== $stateFieldName) ? $this->_container->GetValue($stateFieldName) : '';
		$zip		= (null !== $zipFieldName) ? $this->_container->GetValue($zipFieldName) : '';
		$country	= (null !== $countryFieldName) ? $this->_container->GetValue($countryFieldName) : '';

		$result = sprintf('%s;%s;%s;%s;%s;%s;%s', '', 
				$this->_escapeValue($office), $this->_escapeValue($street),
				$this->_escapeValue($city), $this->_escapeValue($state),
				$this->_escapeValue($zip), $this->_escapeValue($country));
		
		return $token.':'.$result;
	}

	protected function _formTagsFromContainer()
	{
		foreach ($this->_map['tokens'] as $token => $propName)
		{
			if ($this->_container->IsValueSet($propName))
			{
				$value = $this->_container->GetValue($propName, 'string');
				if (strlen($value) > 0)
				{
					$this->_writeToken($token, $this->_escapeValue($value));
				}
			}
		}

		foreach ($this->_map['tokensWithSpecialTreatment'] as $token => $propArray)
		{
			$functionName = $this->_map['tokensWithSpecialTreatment'][$token][0];
			$propArray[0] = $token;
			$result = call_user_func_array(array(&$this, $functionName), $propArray);
			$this->_writeLine($result);
		}
	}
	
	protected function _writeToken($token, $value)
	{
		$this->_value .= $this->_writeLine($token.':'.$value);
	}
}