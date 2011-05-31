<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 *
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

	require_once(WM_ROOTPATH.'common/class_settings.php');
	require_once(WM_ROOTPATH.'common/class_account.php');
	require_once(WM_ROOTPATH.'common/class_log.php');

	require_once(WM_ROOTPATH.'common/contacts/abstracts.php');
	require_once(WM_ROOTPATH.'common/contacts/interface.php');
	require_once(WM_ROOTPATH.'common/contacts/ldap_handler.php');


class ContactsLDAPDriver extends ContactsDriver implements IContactsDriver
{
	const OBJECT_CLASS_CONTACT = 'pabperson';
	const OBJECT_CLASS_GROUP = 'pabgroup';

	/**
	 * @var	array
	 */
	var $_objectMap = array();

	/**
	 * @var	array
	 */
	var $_contactObject = array();
	
	/**
	 * @var	array
	 */
	var $_groupObject = array();

	/**
	 * @var	string
	 */
	var $_pabURI;

	var $_host;
	var $_port;
	var $_search_dn;

	var $_memberofpab;


	/**
	 * @var	resource
	 */
	var $_link;

	/**
	 * @var	resource
	 */
	var $_search;

	public function InitDriver()
	{
		$this->_pabURI = isset($this->_account->CustomValues['paburi']) ? $this->_account->CustomValues['paburi'] : '';
		$this->_link = null;
		$this->_search = null;

		$this->WriteLog('ldap: parse pbaURI = '.$this->_pabURI);
		$uriParseResult = ConvertUtils::LdapUriParse($this->_pabURI);
		$this->_host = $uriParseResult['host'];
		$this->_port = $uriParseResult['port'];
		$this->_search_dn = $uriParseResult['search_dn'];

		$this->_objectMap = LdapHandlerClass::GetLdapObjectMap();
		$this->_contactObject = LdapHandlerClass::GetLdapContactObjectEntry();
		$this->_groupObject = LdapHandlerClass::GetLdapGroupObjectEntry();

		$this->_memberofpab = '';
		if (isset($this->_account->CustomValues['memberofpab'])
			&& strlen($this->_account->CustomValues['memberofpab']) > 0)
		{
			$this->_contactObject['memberofpab'] = $this->_account->CustomValues['memberofpab'];
			$this->_groupObject['memberofpab'] = $this->_account->CustomValues['memberofpab'];
			$this->_memberofpab = $this->_account->CustomValues['memberofpab'];
		}
	}

	private function _connect()
	{
		if (!extension_loaded('ldap'))
		{
			$errorString = 'Can\'t load LDAP extension.';
			$this->WriteLog('ldap: error: '.$errorString, LOG_LEVEL_ERROR);
			setGlobalError($errorString);
			return false;
		}
		if (!is_resource($this->_link))
		{
			$this->WriteLog('ldap: connect to '.$this->_host.':'.$this->_port);
			$this->_link = @ldap_connect($this->_host, $this->_port);
			if ($this->_link)
			{
				@register_shutdown_function(array(&$this, 'RegDisconnect'));

				@ldap_set_option($this->_link, LDAP_OPT_PROTOCOL_VERSION, 3);
				@ldap_set_option($this->_link, LDAP_OPT_REFERRALS, 0);

				$this->WriteLog('ldap: bind '.LDAP_CONTACT_BIND_DN.'/'.LDAP_CONTACT_PASSWORD);
				if (!@ldap_bind($this->_link, LDAP_CONTACT_BIND_DN, LDAP_CONTACT_PASSWORD))
				{
					$this->_checkBoolReturn(false);
					$this->_disconnect();
					return false;
				}
			}
			else
			{
				$this->_checkBoolReturn(false);
				return false;
			}
		}

		return true;
	}

	public function RegDisconnect()
	{
		static $isReg = false;
		if (!$isReg)
		{
			$this->_disconnect();
			$isReg = true;
		}
	}

	private function _disconnect()
	{
		if (is_resource($this->_link))
		{
			$this->WriteLog('ldap: ldap_unbind("'.LDAP_CONTACT_BIND_DN.'")');
			@ldap_unbind($this->_link, LDAP_CONTACT_BIND_DN);
			$this->WriteLog('ldap: diconnect');
			@ldap_close($this->_link);
			$this->_link = null;
		}
	}

	private function _search($objectFilter)
	{
		//if (!is_resource($this->_search))
		//{
			$this->WriteLog('ldap: ldap_search("'.$this->_search_dn.'", "'.$objectFilter.'")');
			$this->_search = @ldap_search($this->_link, $this->_search_dn, $objectFilter);
			$this->_checkBoolReturn($this->_search);
		//}
		return is_resource($this->_search);
	}

	/**
	 * Order a search in ascending and descending order.
	 * @url http://ru.php.net/manual/en/function.ldap-sort.php#85317
	 *
	 * @param resource from ldap_connect()
	 * @param resource from ldap_search()
	 * @param string of attribute to order
	 * @param string "asc" or "desc"
	 * @param integer page number
	 * @param integer entries per page
	 * @return array
	 */
	function _ldapSortPaginate($rConnection, $rSearch, $sField, $sOrder = 'asc', $iPage = null, $iPerPage = null)
	{
		$iTotalEntries = ldap_count_entries($rConnection, $rSearch);
		if ($iPage === null || $iPerPage === null)
		{
			# fetch all in one page
			$iStart = 0;
			$iEnd = $iTotalEntries - 1;
		}
		else
		{
			$iPage -= 1;
			$iPage = ($iPage < 0) ? 0 : $iPage;

			# calculate range of page
			if ($sOrder === 'desc')
			{
				$iStart = $iTotalEntries - (($iPage + 1) * $iPerPage);
			}
			else
			{
				$iStart = $iPage * $iPerPage;
			}

			$iStart = ($iStart < 0) ? 0 : $iStart;
			$iEnd = $iStart + $iPerPage - 1;
			$iEnd = ($iEnd > $iTotalEntries - 1) ? $iTotalEntries - 1 : $iEnd;
		}

		# fetch entries
		ldap_sort($rConnection, $rSearch, $sField);
		$aList = array();
		for ($iCurrent = 0, $rEntry = ldap_first_entry($rConnection, $rSearch);
			$iCurrent <= $iEnd && is_resource($rEntry);
			$iCurrent++, $rEntry = ldap_next_entry($rConnection, $rEntry)
		)
		{
			if ($iCurrent >= $iStart)
			{
				array_push($aList, ldap_get_attributes($rConnection, $rEntry));
			}
		}

		# if order is desc revert page's entries
		return ($sOrder === 'desc') ? array_reverse($aList) : $aList;
	}


	function _initAddressBookRecordByLdapEntries(&$addressBookRecord, $entry)
	{
		$map = $this->getContactMap(true);
		foreach ($entry as $key => $row)
		{
			if (isset($map[$key]))
			{
				$addressBookRecord->{$map[$key]} = isset($row[0]) ? $row[0] : '';
			}
		}
	}

	/**
	 * @param	mix		$contactId
	 * @return	AddressBookRecord
	 */
	public function GetContact($contactId)
	{
		$addressBookRecord = null;
		if ($this->_connect())
		{
			if ($this->_search('(&(objectClass='.self::OBJECT_CLASS_CONTACT.')(un='.$contactId.'))'))
			{
				$return = ldap_get_entries($this->_link, $this->_search);
				$this->_checkBoolReturn($return);
				if ($return && isset($return[0]))
				{
					$row = $return[0];
					if ($row && isset($row['un'][0]))
					{
						$addressBookRecord = new AddressBookRecord();
						$addressBookRecord->IdAddress = $row['un'][0];
						$addressBookRecord->IdUser = $_SESSION[USER_ID];

						$this->_initAddressBookRecordByLdapEntries($addressBookRecord, $row);

						$addressBookRecord->UseFriendlyName = true;

						$addressBookRecord->PrimaryEmail = null;
						if (PRIMARY_DEFAULT_EMAIL === PRIMARYEMAIL_Home)
						{
							if ($addressBookRecord->HomeEmail && 0 < strlen($addressBookRecord->HomeEmail))
							{
								$addressBookRecord->PrimaryEmail = PRIMARYEMAIL_Home;
							}
							else if ($addressBookRecord->BusinessEmail && 0 < strlen($addressBookRecord->BusinessEmail))
							{
								$addressBookRecord->PrimaryEmail = PRIMARYEMAIL_Business;
							}
						}
						else
						{
							if ($addressBookRecord->BusinessEmail && 0 < strlen($addressBookRecord->BusinessEmail))
							{
								$addressBookRecord->PrimaryEmail = PRIMARYEMAIL_Business;
							}
							else if ($addressBookRecord->HomeEmail && 0 < strlen($addressBookRecord->HomeEmail))
							{
								$addressBookRecord->PrimaryEmail = PRIMARYEMAIL_Home;
							}
						}
						
						if (null === $addressBookRecord->PrimaryEmail && $addressBookRecord->OtherEmail && 0 < strlen($addressBookRecord->OtherEmail))
						{
							$addressBookRecord->PrimaryEmail = PRIMARYEMAIL_Other;
						}

						$dateofbirth = isset($row['dateofbirth'][0]) ? $row['dateofbirth'][0] : '';
						if (strlen($dateofbirth) > 0)
						{
							$dateofbirthArray = explode('/', $dateofbirth, 3);
							if (count($dateofbirthArray) === 3)
							{
								$addressBookRecord->BirthdayDay = (int) $dateofbirthArray[0];
								$addressBookRecord->BirthdayMonth = (int) $dateofbirthArray[1];
								$addressBookRecord->BirthdayYear = (int) $dateofbirthArray[2];
							}
						}

						if (isset($row['memberofpabgroup']))
						{
							unset($row['memberofpabgroup']['count']);

							$addressBookRecord->GroupsIds = array_values($row['memberofpabgroup']);
							$addressBookRecord->GroupsIds = is_array($addressBookRecord->GroupsIds)
								? $addressBookRecord->GroupsIds : array();
						}

						LdapHandlerClass::ContactObjReparse($addressBookRecord);
					}
				}
			}
		}

		return $addressBookRecord;
	}

	/**
	 * @param	int			$groupId
	 * @param	int			$lookForType
	 * @param	string		$lookForField
	 * @return	array		array(contact_count, group_count)
	 */
	public function GetContactsAndGroupsCount($groupId, $lookForType, $lookForField)
	{
		$countArray = array(0, 0);
		if ($this->_connect())
		{
			$filter = '(objectClass='.self::OBJECT_CLASS_CONTACT.')';
			if (strlen($lookForField) > 0)
			{
				$condition = ($lookForType == 1) ? $lookForField.'*' : '*'.$lookForField.'*';
				$groupCondition = ($groupId != -1 && strlen($groupId) > 0) ? '(memberofpabgroup='.$groupId.')' : '';
				$filter = '(&(objectClass='.self::OBJECT_CLASS_CONTACT.')'.$groupCondition.'(|(cn='.$condition.')(mail='.$condition.')))';
			}

			$filter = strlen($this->_memberofpab) > 0
				? '(&(memberOfPAB='.$this->_memberofpab.')'.$filter.')' : $filter;

			if ($this->_search($filter))
			{
				$cnt = ldap_count_entries($this->_link, $this->_search);
				$this->_checkBoolReturn($cnt);
				if (false !== $cnt)
				{
					$countArray[0] = $cnt;
				}
			}

			if ($groupId == -1 || strlen($groupId) == 0)
			{
				$filter = '(objectClass='.self::OBJECT_CLASS_GROUP.')';
				if (strlen($lookForField) > 0)
				{
					$condition = ($lookForType == 1) ? $lookForField.'*' : '*'.$lookForField.'*';
					$filter = '(&(objectClass='.self::OBJECT_CLASS_GROUP.')(cn='.$condition.'))';
				}
				if ($this->_search($filter))
				{
					$cnt = ldap_count_entries($this->_link, $this->_search);
					$this->_checkBoolReturn($cnt);
					if (false !== $cnt)
					{
						$countArray[1] = $cnt;
					}
				}
			}
		}
		$this->WriteLog('ldap: return counts = ['.$countArray[0].', '.$countArray[0].']');
		return $countArray;
	}

	/**
	 * @param	array		$counts			array(contact_count, group_count)
	 * @param	int			$page
	 * @param	string		$sortField
	 * @param	int			$sortOrder
	 * @param	int			$groupId
	 * @param	int			$lookForType
	 * @param	string		$lookForField
	 * @return	ContactCollection
	 */
	public function GetContactsAndGroups($counts, $page, $sortField, $sortOrder, $groupId, $lookForType, $lookForField)
	{
		$contacts = null;
		$countContactsAndGroups = $counts[0] + $counts[1];

		if ($countContactsAndGroups < ($page - 1) * $this->_account->ContactsPerPage)
		{
			$page = 1;
		}

		if ($this->_connect())
		{
			$filter = '(|(objectClass='.self::OBJECT_CLASS_CONTACT.')(objectClass='.self::OBJECT_CLASS_GROUP.'))';
			$filter = strlen($this->_memberofpab) > 0
				? '(&(memberOfPAB='.$this->_memberofpab.')'.$filter.')' : $filter;
			
			if (strlen($lookForField) > 0)
			{
				$condition = ($lookForType == 1) ? $lookForField.'*' : '*'.$lookForField.'*';
				$groupCondition = ($groupId != -1 && strlen($groupId) > 0) ? '(memberofpabgroup='.$groupId.')' : '';
				$filter = '(&(|(objectClass='.self::OBJECT_CLASS_CONTACT.')(objectClass='.self::OBJECT_CLASS_GROUP.'))'.$groupCondition.'(|(mail='.$condition.')(cn='.$condition.')))';
			}
			if ($this->_search($filter))
			{
				$sortField = (int) $sortField;
				$sortOrder = (int) $sortOrder;

				$pagSortField = 'mail';
				switch ($sortField)
				{
					case 1:
						$pagSortField = 'cn';
						break;
					case 2:
						$pagSortField = 'mail';
						break;
				}

				$pagSortOrder = (0 === $sortOrder) ? 'asc' : 'desc';
				$return = $this->_ldapSortPaginate($this->_link, $this->_search, $pagSortField, $pagSortOrder, $page, $this->_account->ContactsPerPage);
				if ($return)
				{
					$contacts = new ContactCollection();
					$k = 0;

					while (isset($return[$k]))
					{
						if ($lookForType == 1 && $k > SUGGESTCONTACTS)
						{
							break;
						}

						$row = $return[$k];
						if (is_array($row) && isset($row['un'][0], $row['objectClass']) && is_array($row['objectClass']))
						{
							$contact = new Contact();
							$contact->Id = $row['un'][0];
							$contact->IsGroup = in_array(self::OBJECT_CLASS_GROUP, $row['objectClass']);;
							$contact->Name = isset($row['cn'][0]) ? $row['cn'][0] : '';
							$contact->Email = isset($row['mail'][0]) ? $row['mail'][0] : '';
							$contact->Frequency = 0;
							$contact->UseFriendlyName = true;

							$contacts->Add($contact);
							unset($contact);
						}

						$k++;
					}
				}
			}
		}
		return $contacts;
	}

	/**
	 * @param	int		$groupId
	 * @return	ContactCollection
	 */
	public function GetContactsOfGroup($groupId)
	{
		$contacts = null;
		if ($this->_connect())
		{
			if ($this->_search('(&(objectClass='.self::OBJECT_CLASS_CONTACT.')(memberofpabgroup='.$groupId.'))'))
			{
				$contacts = new ContactCollection();
				$return = ldap_get_entries($this->_link, $this->_search);
				$this->_checkBoolReturn($return);
				if ($return)
				{
					$k = 0;
					while (isset($return[$k]))
					{
						$row = $return[$k];
						if (is_array($row) && isset($row['un'][0]))
						{
							$contact = new Contact();
							$contact->Id = $row['un'][0];
							$contact->Name = isset($row['cn'][0]) ? $row['cn'][0] : '';
							$contact->Email = isset($row['mail'][0]) ? $row['mail'][0] : '';
							$contact->UseFriendlyName = true;

							$contacts->Add($contact);
							unset($contact);
						}

						$k++;
					}
				}
			}
		}

		return $contacts;
	}

	/**
	 * @param	int		$contactId
	 * @return	AddressGroup
	 */
	public function GetGroup($groupId)
	{
		$group = null;
		if ($this->_connect())
		{
			if ($this->_search('(&(objectClass='.self::OBJECT_CLASS_GROUP.')(un='.$groupId.'))'))
			{
				$return = ldap_get_entries($this->_link, $this->_search);
				$this->_checkBoolReturn($return);
				if ($return && isset($return[0]))
				{
					$row = $return[0];
					if ($row && isset($row['un'][0]))
					{
						$group = new AddressGroup();
						$group->Id = $row['un'][0];
						$group->IdUser = $_SESSION[USER_ID];
						$group->Name = isset($row['cn'][0]) ? $row['cn'][0]: '';
						$group->IsOrganization = false;
					}
				}
			}
		}
		return $group;
	}

	/**
	 * @return	array	array(id => name)
	 */
	public function GetGroups()
	{
		$groups = array();
		if ($this->_connect())
		{
			if ($this->_search('(objectClass='.self::OBJECT_CLASS_GROUP.')'))
			{
				$return = ldap_get_entries($this->_link, $this->_search);
				$this->_checkBoolReturn($return);
				if ($return)
				{
					$k = 0;
					while (isset($return[$k]))
					{
						$row = $return[$k];
						if (isset($row['un'][0]))
						{
							$groups[$row['un'][0]] = isset($row['cn'][0]) ? $row['cn'][0] : '';
						}
						$k++;
					}
				}
			}
		}
		return $groups;
	}

	/**
	 * @param	int		$contactId
	 * @return	array	array(id => name)
	 */
	public function GetGroupsOfContact($contactId)
	{
		$groups = array();
		$contact = $this->GetContact($contactId);
		if ($contact && count($contact->GroupsIds) > 0)
		{
			foreach ($contact->GroupsIds as $id)
			{
				$group = $this->GetGroup($id);
				if ($group)
				{
					$groups[$id] = $group->Name;
				}
			}
		}
		return $groups;
	}

	/**
	 * @return	bool
	 */
	public function DeleteContact($contactId)
	{
		$return = false;
		if ($this->_connect())
		{
			$this->WriteLog('ldap: ldap_delete: un='.$contactId.','.$this->_search_dn);
			$return = @ldap_delete($this->_link, 'un='.$contactId.','.$this->_search_dn);
			$this->_checkBoolReturn($return);
		}
		return $return;
	}

	/**
	 * @return	bool
	 */
	public function DeleteGroup($groupId)
	{
		$return = false;
		if ($this->_connect())
		{
			$this->WriteLog('ldap: ldap_delete("un='.$groupId.','.$this->_search_dn.'")');
			$return = @ldap_delete($this->_link, 'un='.$groupId.','.$this->_search_dn);
			$this->_checkBoolReturn($return);
		}
		return $return;
	}

	/**
	 * @param	int		$contactId
	 * @param	int		$groupId
	 * @return	bool
	 */
	public function AddContactToGroup($contactId, $groupId)
	{
		$return = false;
		if ($this->_connect())
		{
			$contact = $this->GetContact($contactId);
			if ($contact)
			{
				if (!is_array($contact->GroupsIds) || !in_array($groupId, $contact->GroupsIds))
				{
					$contact->GroupsIds = is_array($contact->GroupsIds) ? $contact->GroupsIds : array();
					$contact->GroupsIds[] = $groupId;

					$entry = array('memberofpabgroup' => $contact->GroupsIds);
					$this->WriteLog('ldap: ldap_modify("un='.$contactId.','.$this->_search_dn.'", $entry)');
					$return = @ldap_modify($this->_link, 'un='.$contactId.','.$this->_search_dn, $entry);
					$this->_checkBoolReturn($return);
				}
			}
		}
		return $return;
	}

	/**
	 * @param	int		$contactId
	 * @param	int		$groupId
	 * @return	bool
	 */
	public function InsertContactToGroup($contactId, $groupId)
	{
		return $this->AddContactToGroup($contactId, $groupId);
	}

	/**
	 * @param	int		$contactId
	 * @param	int		$groupId
	 * @return	bool
	 */
	public function DeleteContactFromGroup($contactId, $groupId)
	{
		$return = false;
		if ($this->_connect())
		{
			$contact = $this->GetContact($contactId);
			if ($contact)
			{
				if (is_array($contact->GroupsIds) && in_array($groupId, $contact->GroupsIds))
				{
					for ($i = 0, $c = count($contact->GroupsIds); $i < $c; $i++)
					{
						if ($groupId == $contact->GroupsIds[$i])
						{
							unset($contact->GroupsIds[$i]);
						}
					}

					$entry = array('memberofpabgroup' => $contact->GroupsIds);
					$this->WriteLog('ldap: ldap_modify("un='.$contactId.','.$this->_search_dn.'", $entry)');
					$return = @ldap_modify($this->_link, 'un='.$contactId.','.$this->_search_dn, $entry);
					$this->_checkBoolReturn($return);
				}
				else
				{
					$return = true;
				}
			}
		}
		return $return;
	}

	/**
	 * @param	string	$name
	 * @param	string	$email
	 * @return	bool
	 */
	public function IsContactExist($name, $email)
	{
		if ($this->_connect())
		{
			$contactNameSearch = (strlen($name) > 0) ? '(cn='.$name.')' : '';
			if ($this->_search('(&(objectClass='.self::OBJECT_CLASS_CONTACT.')'.$contactNameSearch.'(mail='.$email.'))'))
			{
				$cnt = ldap_count_entries($this->_link, $this->_search);
				return ($cnt && $cnt > 0);
			}
		}
		return false;
	}

	/**
	 * @param	AddressGroup	$group
	 * @return	array
	 */
	function getGroupEntryFromAddressGroup($group)
	{
		$entry = $this->_groupObject;
		$entry['un'] = $group->Id;
		$entry['cn'] = $group->Name;
		return $entry;
	}

	/**
	 * @param	AddressBookRecord	$addressBookRecord
	 * @return	array
	 */
	function getContactEntryFromAddressBookRecord($addressBookRecord)
	{
		$entry = $this->_contactObject;
		$entry['un'] = $addressBookRecord->IdAddress;

		$map = $this->getContactMap();
		foreach ($map as $entryKey => $objectKey)
		{
			if (strlen($addressBookRecord->{$objectKey}) > 0)
			{
				$entry[$entryKey] = $addressBookRecord->{$objectKey};
			}
		}

		if ($addressBookRecord->BirthdayDay && $addressBookRecord->BirthdayMonth && $addressBookRecord->BirthdayYear)
		{
			$entry['dateOfBirth'] = $addressBookRecord->BirthdayDay.'/'.$addressBookRecord->BirthdayMonth.'/'.$addressBookRecord->BirthdayYear;
		}

		if (is_array($addressBookRecord->GroupsIds))
		{
			foreach($addressBookRecord->GroupsIds as $groupId)
			{
				$entry['memberofpabgroup'][] = $groupId;
			}
		}

		$entry['memberofpabgroup'] = isset($entry['memberofpabgroup']) ? $entry['memberofpabgroup'] : array();

		return $entry;
	}

	function getContactMap($lowerKeys = false)
	{
		return $lowerKeys ? array_change_key_case($this->_objectMap, CASE_LOWER) : $this->_objectMap;
	}

	/**
	 * @param	AddressBookRecord	$contact
	 * @return	bool
	 */
	public function CreateContact($contact)
	{
		$return = false;
		if ($this->_connect())
		{
			$un = LdapHandlerClass::CreteNewContactUn($contact);
			$contact->IdAddress = $un;
			LdapHandlerClass::ContactObjReparse($contact);

			$entry = $this->getContactEntryFromAddressBookRecord($contact);
			if (isset($entry['memberofpabgroup']) && is_array($entry['memberofpabgroup']) && count($entry['memberofpabgroup']) == 0)
			{
				unset($entry['memberofpabgroup']);
			}

			$this->WriteLog('ldap: ldap_add("un='.$un.','.$this->_search_dn.'", $entry)');
			$this->WriteLog('ldap: $entry = '.print_r($entry, true));
			$return = @ldap_add($this->_link, 'un='.$un.','.$this->_search_dn, $entry);
			$this->_checkBoolReturn($return);
		}
		return $return;
	}

	/**
	 * @param	AddressBookRecord	$contact
	 * @return	bool
	 */
	public function UpdateContact($contact)
	{
		$return = false;
		if ($this->_connect())
		{
			LdapHandlerClass::ContactObjReparse($contact);

			$entry = $this->getContactEntryFromAddressBookRecord($contact);

			$this->WriteLog('ldap: ldap_modify("un='.$contact->IdAddress.','.$this->_search_dn.'", $entry)');
			$this->WriteLog('ldap: $entry = '.print_r($entry, true));
			$return = @ldap_modify($this->_link, 'un='.$contact->IdAddress.','.$this->_search_dn, $entry);
			$this->_checkBoolReturn($return);
		}
		return $return;
	}

	/**
	 * @param	string	$name
	 * @return	bool
	 */
	public function IsGroupExist($name)
	{
		if ($this->_connect())
		{
			if ($this->_search('(&(objectClass='.self::OBJECT_CLASS_GROUP.')(cn='.$name.'))'))
			{
				$cnt = ldap_count_entries($this->_link, $this->_search);
				return ($cnt && $cnt > 0);
			}
		}
		return false;
	}

	/**
	 * @param	AddressGroup	$group
	 * @return	bool
	 */
	public function CreateGroup($group)
	{
		$return = false;
		if ($this->_connect())
		{
			$un = LdapHandlerClass::CreteNewGroupUn($group);
			$group->Id = $un;
			$entry = $this->getGroupEntryFromAddressGroup($group);

			$this->WriteLog('ldap: ldap_add("un='.$un.','.$this->_search_dn.'", $entry)');
			$this->WriteLog('ldap: $entry = '.print_r($entry, true));
			$return = @ldap_add($this->_link, 'un='.$un.','.$this->_search_dn, $entry);
			$this->_checkBoolReturn($return);
		}
		return $return;
	}

	/**
	 * @param	AddressGroup	$group
	 * @return	bool
	 */
	public function UpdateGroup($group)
	{
		$return = false;
		if ($this->_connect())
		{
			$entry = array(
				'cn' => $group->Name
			);

			$contacts = $this->GetContactsOfGroup($group->Id);
			$serverIds = array();

			if ($contacts && $contacts->Count() > 0)
			{
				$contactKeys = array_keys($contacts->Instance());
				foreach ($contactKeys as $key)
				{
					$contact =& $contacts->Get($key);
					if ($contact)
					{
						$serverIds[] = $contact->Id;
					}
					unset($contact);
				}
			}

			$deleteIds = array();
			foreach ($serverIds as $id)
			{
				if (!in_array($id, $group->ContactsIds))
				{
					$deleteIds[] = $id;
				}
			}

			foreach ($deleteIds as $id)
			{
				$this->DeleteContactFromGroup($id, $group->Id);
			}

			$this->WriteLog('ldap: ldap_modify: un='.$group->Id.','.$this->_search_dn);
			$return = ldap_modify($this->_link, 'un='.$group->Id.','.$this->_search_dn, $entry);
			$this->_checkBoolReturn($return);
		}
		return $return;
	}

	private function _checkBoolReturn($return)
	{
		if (false === $return)
		{
			$this->WriteLog('ldap: error #'.@ldap_errno($this->_link).': '.@ldap_error($this->_link), LOG_LEVEL_ERROR);
		}
		return $return;
	}
}

