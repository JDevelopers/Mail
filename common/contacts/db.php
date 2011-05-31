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

class ContactsDbDriver extends ContactsDriver implements IContactsDriver
{
	/**
	 * @var MySqlStorage
	 */
	var $_db;

	/**
	 * Initialize driver class params
	 */
	public function InitDriver()
	{
		$this->_db =& DbStorageCreator::CreateDatabaseStorage($this->_account, $this->_settings);
		$this->_db->Connect();
	}

	/**
	 * @param	mix		$contactId
	 * @return	AddressBookRecord
	 */
	public function GetContact($contactId)
	{
		return $this->_db->SelectAddressBookRecord($contactId);
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
		if ($lookForField === '')
		{
			$countArray =& $this->_db->SelectAddressContactsAndGroupsCount($lookForType, $this->_account->IdUser);
		}
		else
		{
			if ($groupId == -1)
			{
				$countArray =& $this->_db->SelectAddressContactsAndGroupsCount($lookForType, $this->_account->IdUser, $lookForField);
			}
			else
			{
				$countArray =& $this->_db->SelectAddressContactsAndGroupsCount($lookForType, $this->_account->IdUser, $lookForField, $groupId);
			}

			if (!$this->_account->IsDemo)
			{
				$countArray[0] += $this->_db->SelectAccountsCountForEmailSharing($lookForType, $lookForField, $this->_account);
			}
		}
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

		if ($lookForField === '')
		{
			$contacts =& $this->_db->LoadContactsAndGroups($page, $sortField, $sortOrder);
		}
		else
		{
			if ($countContactsAndGroups > 0)
			{
				$contacts =& $this->_db->SearchContactsAndGroups($page, $lookForField, $groupId, $sortField, $sortOrder, $lookForType);
			}
		}

		return $contacts;
	}

	/**
	 * @param	int		$groupId
	 * @return	array
	 */
	public function GetContactsOfGroup($groupId)
	{
		return $this->_db->SelectAddressGroupContacts($groupId);
	}

	/**
	 * @param	int		$contactId
	 * @return	AddressGroup
	 */
	public function GetGroup($groupId)
	{
		return $this->_db->SelectGroupById($groupId);
	}

	/**
	 * @return	array	array(id => name)
	 */
	public function GetGroups()
	{
		return $this->_db->SelectUserAddressGroupNames();
	}

	/**
	 * @param	int		$contactId
	 * @return	array	array(id => name)
	 */
	public function GetGroupsOfContact($contactId)
	{
		return $this->_db->SelectAddressGroupContact($contactId);
	}

	/**
	 * @return	bool
	 */
	public function DeleteContact($contactId)
	{
		return $this->_db->DeleteAddressBookRecord($contactId);
	}

	/**
	 * @return	bool
	 */
	public function DeleteGroup($groupId)
	{
		return $this->_db->DeleteAddressGroup($groupId);
	}

	/**
	 * @param	int		$contactId
	 * @param	int		$groupId
	 * @return	bool
	 */
	public function AddContactToGroup($contactId, $groupId)
	{
		$return = true;
		$return &= $this->_db->DeleteAddressGroupsContacts($contactId, $groupId);
		$return &= $this->_db->InsertAddressGroupContact($contactId, $groupId);
		return $return;
	}

	/**
	 * @param	int		$contactId
	 * @param	int		$groupId
	 * @return	bool
	 */
	public function InsertContactToGroup($contactId, $groupId)
	{
		return $this->_db->InsertAddressGroupContact($contactId, $groupId);
	}

	/**
	 * @param	AddressBookRecord	$contact
	 * @return	bool
	 */
	public function IsContactExist($name, $email)
	{
		return $this->_db->ExistAddressBookRecordDoublet($name, $email, $this->_account->IdUser);
	}

	/**
	 * @param	AddressBookRecord	$contact
	 * @return	bool
	 */
	public function CreateContact($contact)
	{
		return $this->_db->InsertAddressBookRecord($contact);
	}

	/**
	 * @param	AddressBookRecord	$contact
	 * @return	bool
	 */
	public function UpdateContact($contact)
	{
		if ($this->_db->UpdateAddressBookRecord($contact))
		{
			$result = $this->_db->DeleteAddressGroupsContactsByIdAddress($contact->IdAddress);
			if ($result && $contact->GroupsIds && count($contact->GroupsIds) > 0)
			{
				foreach ($contact->GroupsIds as $groupId)
				{
					$result &= $this->InsertContactToGroup($contact->IdAddress, $groupId);
				}
			}
			return $result;
		}
		return false;
	}

	/**
	 * @param	string	$name
	 * @return	bool
	 */
	public function IsGroupExist($name)
	{
		return $this->_db->CheckExistsAddresGroupByName($name, $this->_account->IdUser);
	}

	/**
	 * @param	AddressGroup	$group
	 * @return	bool
	 */
	public function CreateGroup($group)
	{
		return $this->_db->InsertAddressGroup($group);
	}

	/**
	 * @param	AddressGroup	$group
	 * @return	bool
	 */
	public function UpdateGroup($group)
	{
		if ($this->_db->UpdateAddressGroup($group))
		{
			$result = $this->_db->DeleteAddressGroupsContactsByIdGroup($group->Id);
			if ($result && $group->ContactsIds && count($group->ContactsIds) > 0)
			{
				foreach ($group->ContactsIds as $contactId)
				{
					$result &= $this->InsertContactToGroup($contactId, $group->Id);
				}
			}
			return $result;
		}

		return false;
	}
}
