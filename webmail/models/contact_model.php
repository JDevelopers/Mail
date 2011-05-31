<?php

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

require_once WM_ROOTPATH.'core/base/base_db_model.php';

/**
 *  Model for API integration
 */
class ContactModel extends BaseDBModel
{
	const ERR_MSG_GET_CONTACTS_LIST = 'ERR_MSG_GET_CONTACTS_LIST';
	const ERR_NO_GET_CONTACTS_LIST = 1;

	const ERR_MSG_GET_GROUPS_BY_ID_CONTACT = 'ERR_MSG_GET_GROUPS_BY_ID_CONTACT';
	const ERR_NO_GET_GROUPS_BY_ID_CONTACT = 2;

	const ERR_MSG_GET_GROUPS_LIST = 'ERR_MSG_GET_GROUPS_LIST';
	const ERR_NO_GET_GROUPS_LIST = 3;

	/**
	 * @param	int		$idUser
	 * @return	array | false
	 */
	protected function _GetFullContactsList($idUser, $includeDeleted = false)
	{
		$this->_errorMsg = self::ERR_MSG_GET_CONTACTS_LIST;
		$this->_errorNo = self::ERR_NO_GET_CONTACTS_LIST;

		$sql = $this->_commandCreator->GetFullContactsList($idUser, $includeDeleted);

		$listOfContactsInfo = $this->_query($sql);
		if (is_array($listOfContactsInfo))
		{
			$result = array();
			foreach ($listOfContactsInfo as $contactInfo)
			{
				$contactContainer = new ContactContainer();
				$contactContainer->MassSetValue($contactInfo);

				$idAddress = $contactContainer->GetValue('IdAddress', 'int');
				$groups = $this->_GetGroupsByIdContact($idUser, $idAddress);
				if (is_array($groups))
				{
					$contactContainer->SetValue('Groups', $groups);
				}

				$result[] = $contactContainer;
				unset($contactContainer, $groups);
			}
			
			return $result;
		}

		return false;
	}

	/**
	 * @param	int		$idUser
	 * @return	array | false
	 */
	protected function _GetFullGroupsIdsList($idUser)
	{
		$this->_errorMsg = self::ERR_MSG_GET_GROUPS_LIST;
		$this->_errorNo = self::ERR_NO_GET_GROUPS_LIST;

		$sql = $this->_commandCreator->GetFullGroupsIdsList($idUser);
		$listOfGroupsInfo = $this->_query($sql);
		if (is_array($listOfGroupsInfo))
		{
			$result = array();
			foreach ($listOfGroupsInfo as $groupInfo)
			{
				if (isset($groupInfo['IdGroup']) && (int) $groupInfo['IdGroup'] > 0)
				{
					$result[(int) $groupInfo['IdGroup']] = (int) $groupInfo['IdGroup'];
				}
			}

			return $result;
		}

		return false;
	}

	/**
	 * @param	int		$idUser
	 * @return	array | false
	 */
	protected function _GetFullGroupsStrIdsList($idUser)
	{
		$this->_errorMsg = self::ERR_MSG_GET_GROUPS_LIST;
		$this->_errorNo = self::ERR_NO_GET_GROUPS_LIST;

		$sql = $this->_commandCreator->GetFullGroupsIdsList($idUser);
		$listOfGroupsInfo = $this->_query($sql);
		if (is_array($listOfGroupsInfo))
		{
			$result = array();
			foreach ($listOfGroupsInfo as $groupInfo)
			{
				if (isset($groupInfo['GroupStrId']) && 0 < strlen($groupInfo['GroupStrId']))
				{
					$result[$groupInfo['GroupStrId']] = $groupInfo['GroupStrId'];
				}
			}

			return $result;
		}

		return false;
	}

	/**
	 * 
	 */
	protected function _GetUserIdsUpdatedContacts( $dateUpdate )
	{
		$sql = $this->_commandCreator->GetUserIdsUpdatedContacts($dateUpdate);
		$listOfIds = $this->_query($sql);
		if (is_array($listOfIds))
		{
			$result = array();
			foreach ($listOfIds as $anId)
			{
				if (isset($anId['id_user']) && (int) $anId['id_user'] > 0)
				{
					array_push( $result, $anId['id_user'] );
				}
			}

			return $result;
		}

		return false;
	}


	/**
	 * @param	int		$idUser
	 * @return	array | false
	 */
	protected function _GetFullContactsIdsList($idUser)
	{
		$this->_errorMsg = self::ERR_MSG_GET_CONTACTS_LIST;
		$this->_errorNo = self::ERR_NO_GET_CONTACTS_LIST;

		$sql = $this->_commandCreator->GetFullContactsIdsList($idUser);
		$listOfContactsInfo = $this->_query($sql);
		if (is_array($listOfContactsInfo))
		{
			$result = array();
			foreach ($listOfContactsInfo as $contactInfo)
			{
				if (isset($contactInfo['IdAddress']) && (int) $contactInfo['IdAddress'] > 0)
				{
					$result[(int) $contactInfo['IdAddress']] = (int) $contactInfo['IdAddress'];
				}
			}

			return $result;
		}

		return false;
	}

	/**
	 * @param	int		$idUser
	 * @return	array | false
	 */
	protected function _GetFullContactsStrIdsList($idUser)
	{
		$this->_errorMsg = self::ERR_MSG_GET_CONTACTS_LIST;
		$this->_errorNo = self::ERR_NO_GET_CONTACTS_LIST;

		$sql = $this->_commandCreator->GetFullContactsStrIdsList($idUser);
		$listOfContactsInfo = $this->_query($sql);
		if (is_array($listOfContactsInfo))
		{
			$result = array();
			foreach ($listOfContactsInfo as $contactInfo)
			{
				if (isset($contactInfo['StrId']) && 0 < strlen($contactInfo['StrId']))
				{
					$result[$contactInfo['StrId']] = $contactInfo['StrId'];
				}
			}

			return $result;
		}

		return false;
	}
	
	/**
	 * @param	int		$idContact
	 * @return	array | false
	 */
	protected function _GetGroupsByIdContact($idUser, $idContact)
	{
		$this->_errorMsg = self::ERR_MSG_GET_GROUPS_BY_ID_CONTACT;
		$this->_errorNo = self::ERR_NO_GET_GROUPS_BY_ID_CONTACT;

		$sql = $this->_commandCreator->GetGroupsByIdContact($idUser, $idContact);
		$listOfGroupsInfo = $this->_query($sql);
		if (is_array($listOfGroupsInfo))
		{
			$result = array();
			foreach ($listOfGroupsInfo as $groupInfo)
			{
				$groupContainer = new GroupContainer();
				$groupContainer->MassSetValue($groupInfo);
				$result[] = $groupContainer;
				unset($groupContainer);
			}

			return $result;
		}
		
		return false;
	}

	protected function _ExportVcard($contactContainer)
	{
		require_once WM_ROOTPATH.'webmail/helpers/vcard/contact_vcard_formatter.php';
		require WM_ROOTPATH.'plugins/outlooksync/configuration.php';

		$return = '';
		if ($contactContainer)
		{
			$sContactStrId = $contactContainer->GetValue('StrId');
			if (empty($sContactStrId))
			{
				$contactContainer->GenerateStrId();
				$this->_UpdateContactStrId(
					$contactContainer->GetValue('IdAddress'),
					$contactContainer->GetValue('StrId'));
			}

			$groups = $contactContainer->GetValue('Groups');
			if (is_array($groups) && count($groups) > 0)
			{
				$singleContactContainer = new ContactContainerWithSingleGroup($contactContainer);
				foreach ($groups as $groupContainer)
				{
					$sGroupStrId = $groupContainer->GetValue('GroupStrId');
					if (empty($sGroupStrId))
					{
						$groupContainer->GenerateStrId();
						$this->_UpdateGroupStrId(
							$groupContainer->GetValue('GroupId'),
							$groupContainer->GetValue('GroupStrId'));
					}

					$singleContactContainer->InitByGroup($groupContainer);

					$vCard = new ContactVCardFormatter();
					$vCard->SetContainer($singleContactContainer);
					$vCard->Form();
					$return .= $vCard->GetValue();
					unset($vCard);
				}
			}
			else
			{
				$vCard = new ContactVCardFormatter();
				$vCard->SetContainer($contactContainer);
				$vCard->Form();
				$return = $vCard->GetValue();
			}
		}
		return $return;
	}

	protected function _ImportVcard($filePath)
	{
		require_once WM_ROOTPATH.'calendar/helpers/ical/data_source_reader.php';
		require WM_ROOTPATH.'webmail/helpers/vcard/configuration.php';
		require WM_ROOTPATH.'plugins/outlooksync/configuration.php';
		require_once WM_ROOTPATH.'calendar/helpers/ical/infobox.php';

		$dataSource = new ICalDataSourceReader();
		$dataSource->ParseVcard($filePath);

		$collection = new IVcardInfoBox();
		$collection->InitParameters($vcardParseMap);
		$collection->ParseData($dataSource);
		$collection->ClearDataSource();

		$listOfContacts = $collection->GetParameter('VCARD');
		$contactContainers = array();
		if (is_array($contactContainers))
		{
			foreach ($listOfContacts as $contacts)
			{
				$contactParameters = $contacts->GetParametersList();
				$contactsContainerWithSingleGroup = new ContactContainerWithSingleGroup();
				$contactsContainerWithSingleGroup->MassSetValue($contactParameters);
				$contactContainers[] = $contactsContainerWithSingleGroup;
				unset($contactsContainerWithSingleGroup);
			}
		}
		return $contactContainers;
	}

	protected function _ProcessParsedContact($IdUser, ContactContainerWithSingleGroup $contactContainer)
	{
		$ll =& CLog::CreateInstance();
		$contactContainer->SetValue('IdUser', $IdUser, 'int');

		$contactStrId = $contactContainer->GetValue('StrId');
		$groupStrId = $contactContainer->GetValue('GroupStrId');
		$groupName = $contactContainer->GetValue('GroupName');

		$contact = new ContactContainer($contactContainer);
		$contact->SetValue('IdUser', $IdUser, 'int');
		$ContactId = $this->_GetContactIdByStrId($IdUser, $contactStrId);
		if (0 < $ContactId)
		{
			$contact->SetValue('IdAddress', $ContactId, 'int');
		}
		
		if (!empty($groupName))
		{
			$groupContainer = $this->_GetGroupByStrId($IdUser, $groupStrId);
			if ($groupContainer)
			{
				$groupId = $groupContainer->GetValue('GroupId');
				$groupContainer->MassSetValue($contactContainer->GetContainer());
				$groupContainer->SetValue('GroupId', $groupId, 'int');
				$groupContainer->SetValue('IdUser', $IdUser, 'int');
				$this->_UpdateGroup($groupContainer);
			}
			else
			{
				$groupContainer = new GroupContainer($contactContainer);
				$groupContainer->SetValue('IdUser', $IdUser, 'int');
				$this->_CreateGroup($groupContainer);
			}

			$contact->SetValue('Groups', array($groupContainer));
		}

		if (0 < $contact->GetValue('IdAddress', 'int'))
		{
			$this->_UpdateVCardContact($contact);
		}
		else
		{
			$this->_CreateContact($contact);
		}
	}

	protected function _GetContactIdByStrId($IdUser, $contactStrId)
	{
		$dbResult = $this->_query($this->_commandCreator->GetContactIdByStrId($IdUser, $contactStrId));
		return (isset($dbResult[0]['id_addr']) && 0 < (int) $dbResult[0]['id_addr'])
			? (int) $dbResult[0]['id_addr'] : null;
	}

	protected function _GetGroupIdByStrId($IdUser, $groupStrId)
	{
		$dbResult = $this->_query($this->_commandCreator->GetGroupIdByStrId($IdUser, $groupStrId));
		return (isset($dbResult[0]['id_group']) && 0 < (int) $dbResult[0]['id_group'])
			? (int) $dbResult[0]['id_group'] : null;
	}

	protected function _GetGroupById($IdUser, $groupId)
	{
		$dbResult = $this->_query($this->_commandCreator->GetGroupById($IdUser, $groupId));

		$groupContainer = null;
		if (is_array($dbResult) && isset($dbResult[0]))
		{
			$groupContainer = new GroupContainer();
			$groupContainer->MassSetValue($dbResult[0]);
		}
		
		return $groupContainer;
	}

	protected function _GetGroupByStrId($IdUser, $groupStrId)
	{
		$dbResult = $this->_query($this->_commandCreator->GetGroupByStrId($IdUser, $groupStrId));

		$groupContainer = null;
		if (is_array($dbResult) && isset($dbResult[0]))
		{
			$groupContainer = new GroupContainer();
			$groupContainer->MassSetValue($dbResult[0]);
		}

		return $groupContainer;
	}

	protected function _CreateGroup(GroupContainer &$groupContainer)
	{
		$sql = $this->_commandCreator->CreateGroup($groupContainer);
		if ($this->_executeSql($sql))
		{
			$groupId = $this->_getLastInsertId();
			$groupContainer->SetValue('GroupId', $groupId, 'int');

			if ('' === $groupContainer->GetValue('GroupStrId', 'string', ''))
			{
				$groupContainer->GenerateStrId();
				$this->_UpdateGroupStrId($groupId, $groupContainer->GetValue('GroupStrId'));
			}

			return true;
		}
		return false;
	}

	protected function _UpdateGroup(GroupContainer $groupContainer)
	{
		return $this->_executeSql($this->_commandCreator->UpdateGroup($groupContainer));
	}

	protected function _UpdateContactStrId($contactId, $contactStrId)
	{
		return $this->_executeSql($this->_commandCreator->UpdateContactStrId($contactId, $contactStrId));
	}

	protected function _UpdateGroupStrId($groupId, $groupStrId)
	{
		return $this->_executeSql($this->_commandCreator->UpdateGroupStrId($groupId, $groupStrId));
	}

	protected function _IsContactInGroup($IdContact, $IdGroup)
	{
		$result = $this->_executeSql($this->_commandCreator->IsContactInGroup($IdContact, $IdGroup));
		return (bool) $this->_getRowCount($result);
	}

	protected function _AddContactToGroup($IdContact, $IdGroup)
	{
		if (!$this->_IsContactInGroup($IdContact, $IdGroup))
		{
			return $this->_executeSql($this->_commandCreator->AddContactToGroup($IdContact, $IdGroup));
		}
		
		return true;
	}

	protected Function _UpdateFunambolContactId(ContactContainer $contactContainer)
	{
		return $this->_executeSql($this->_commandCreator->UpdateFunambolContactId($contactContainer));
	}

	protected function _UpdateContact(ContactContainer $contactContainer)
	{
		return $this->_executeSql($this->_commandCreator->UpdateContact($contactContainer));
	}

	protected function _CreateContact(ContactContainer $contactsContainer)
	{
		if ($this->_executeSql($this->_commandCreator->CreateContact($contactsContainer)))
		{
			$contactId = $this->_getLastInsertId();
			$contactsContainer->SetValue('IdAddress', $contactId, 'int');

			if ('' === $contactsContainer->GetValue('StrId', 'string', ''))
			{
				$contactsContainer->GenerateStrId();
				$this->_UpdateContactStrId($contactId, $contactsContainer->GetValue('StrId'));
			}

			return $this->_UpdateGroupsInContact($contactsContainer);
		}

		return false;
	}

	protected function _UpdateVCardContact(ContactContainer $contactsContainer)
	{
		if ($this->_executeSql($this->_commandCreator->UpdateVCardContact($contactsContainer)))
		{
			return $this->_UpdateGroupsInContact($contactsContainer);
		}

		return false;
	}

	protected function _RemoveAllContactsAndGroups($IdUser)
	{
		$this->_executeSql($this->_commandCreator->RemoveAllContacts($IdUser));
		$this->_executeSql($this->_commandCreator->RemoveAllGroups($IdUser));
		return true;
	}

	protected function _RemoveContactsAndGroupsByStrIds($IdUser, $contactsStrIds, $groupsStrIds)
	{
		if (is_array($contactsStrIds) && count($contactsStrIds) > 0)
		{
			$this->_executeSql($this->_commandCreator->RemoveContactsByStrIds($IdUser, $contactsStrIds));
		}

		if (is_array($groupsStrIds) && count($groupsStrIds) > 0)
		{
			$this->_executeSql($this->_commandCreator->RemoveGroupsByStrIds($IdUser, $groupsStrIds));
		}
	}

	protected function _RemoveContactsAndGroupsByIds($IdUser, $contactsIds, $groupsIds)
	{
		if (is_array($contactsIds) && count($contactsIds) > 0)
		{
			$this->_executeSql($this->_commandCreator->RemoveContactsByIds($IdUser, $contactsIds));
		}

		if (is_array($groupsIds) && count($groupsIds) > 0)
		{
			$this->_executeSql($this->_commandCreator->RemoveGroupsByIds($IdUser, $groupsIds));
		}
	}

	private function _UpdateGroupsInContact(ContactContainer $contactsContainer)
	{
		$IdContact = $contactsContainer->GetValue('IdAddress', 'int');
		$groupsArray = $contactsContainer->GetValue('Groups');

		if ($IdContact > 0)
		{
			$this->_executeSql($this->_commandCreator->RemoveContactFromGroups($IdContact));
			
			if ($groupsArray && is_array($groupsArray) && count($groupsArray) > 0)
			{
				foreach ($groupsArray as $groupContainer)
				{
					$IdGroup = $groupContainer->GetValue('GroupId', 'int');
					if ($IdGroup > 0)
					{
						$this->_AddContactToGroup($IdContact, $IdGroup);
					}
				}
			}
		}

		return true;
	}
}

class ContactModelException extends BaseModelException
{}
