<?php

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

require_once WM_ROOTPATH.'core/base/base_db_model.php';
require_once WM_ROOTPATH.'webmail/containers/funambol_contact_container.php';

/**
 *  Model for API integration
 */
class FunambolContactModel extends BaseDBModel
{
	const ERR_MSG_GET_CONTACTS_LIST = 'ERR_MSG_GET_CONTACTS_LIST';
	const ERR_NO_GET_CONTACTS_LIST = 1;

	/**
	 * @param	int		$idUser
	 * @return	array | false
	 */
	protected function _GetFullContactsList($funambolUserLogin)
	{
		$this->_errorMsg = self::ERR_MSG_GET_CONTACTS_LIST;
		$this->_errorNo = self::ERR_NO_GET_CONTACTS_LIST;

		$sql = $this->_commandCreator->GetFullContactsList($funambolUserLogin);
		$listOfContactsInfo = $this->_query($sql);
		if (is_array($listOfContactsInfo))
		{
			$result = array();
			foreach ($listOfContactsInfo as $contactInfo)
			{
				$funambolContactContainer = new FunambolContactContainer();
				$funambolContactContainer->MassSetValue($contactInfo);

				$result[] = $funambolContactContainer;
			}

			return $result;
		}
		return false;
	}

	/**
	 * @param	int		$idUser
	 * @return	array | false
	 */
	/*
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
	*/

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
	 * @param	int		$idContact
	 * @return	array | false
	 */
	/*
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
	*/
	/*
	protected function _ExportVcard($contactContainer)
	{
		require_once WM_ROOTPATH.'webmail/helpers/vcard/contact_vcard_formatter.php';
		require WM_ROOTPATH.'plugins/outlooksync/configuration.php';

		$return = '';
		if ($contactContainer)
		{
			$groups = $contactContainer->GetValue('Groups');
			if (is_array($groups) && count($groups) > 0)
			{
				$singleContactContainer = new ContactContainerWithSingleGroup($contactContainer);
				foreach ($groups as $groupContainer)
				{
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
		$contactContainer->SetValue('IdUser', $IdUser, 'int');

		$groupId = $contactContainer->GetValue('GroupId', 'int');
		$groupStrId = $contactContainer->GetValue('GroupStrId');
		$groupName = $contactContainer->GetValue('GroupName');

		$contact = new ContactContainer($contactContainer);

		if (!empty($groupName))
		{
			$groupContainer = ($groupId > 0)
				? $this->_GetGroupById($IdUser, $groupId)
				: $this->_GetGroupByStrId($IdUser, $groupStrId);

			if ($groupContainer)
			{
				$groupContainer->MassSetValue($contactContainer->GetContainer());
				$this->_UpdateGroup($groupContainer);
			}
			else
			{
				$groupContainer = new GroupContainer($contactContainer);
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
			return true;
		}
		return false;
	}

	protected function _UpdateGroup(GroupContainer $groupContainer)
	{
		return $this->_executeSql($this->_commandCreator->UpdateGroup($groupContainer));
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
*/
	protected function _ReplaceContact(FunambolContactContainer $fnContactContainer)
	{
		if($fnContactContainer->getValue('id') == null)
		{
			$ids = $this->query($this->_commandCreator->CreateContactId($fnContactContainer));
			$fnContactContainer->SetValue('id', -1);
			if (isset($ids) && count($ids)>0)
			{
				if(isset($ids[0]['min_id']) && $ids[0]['min_id'] < 0)
				{
					$fnContactContainer->SetValue('id', $ids[0]['min_id'] - 1);
				}
			}
		}
		if ($this->_executeSql($this->_commandCreator->ReplaceContact($fnContactContainer)))
		{
			return true;
		}
		
		return false;
	}

	protected function _ReplaceContactAddressInfo(FunambolContactContainer $fnContactContainer)
	{
			if ($this->_executeSql($this->_commandCreator->ReplaceContactAddressInfo($fnContactContainer)))
			{
				return true;
			}
			return false;
	}

	protected function _ReplaceContactOtherInfo(FunambolContactContainer $fnContactContainer)
	{
			if ($this->_executeSql($this->_commandCreator->ReplaceContactOtherInfo($fnContactContainer)))
			{
				return true;
			}
			return false;
	}

/*
	protected function _UpdateVCardContact(ContactContainer $contactsContainer)
	{
		if ($this->_executeSql($this->_commandCreator->UpdateVCardContact($contactsContainer)))
		{
			return $this->_UpdateGroupsInContact($contactsContainer);
		}

		return false;
	}
*/
	protected function _RemoveAllContactsAndGroups($IdUser)
	{
		/*
		$this->_executeSql($this->_commandCreator->RemoveAllContacts($IdUser));
		$this->_executeSql($this->_commandCreator->RemoveAllGroups($IdUser));
		 
		 */
		return true;
	}

	protected function _RemoveContactsAndGroupsByIds($IdUser, $contactsIds, $groupsIds)
	{
		/*
		if (is_array($contactsIds) && count($contactsIds) > 0)
		{
			$this->_executeSql($this->_commandCreator->RemoveContactsByIds($IdUser, $contactsIds));
		}

		if (is_array($groupsIds) && count($groupsIds) > 0)
		{
			$this->_executeSql($this->_commandCreator->RemoveGroupsByIds($IdUser, $groupsIds));
		}
		 */
	}

	/**
	 * @param int $idContact
	 */
	protected function _DeleteContact($idContact)
   {
       $this->query($this->_commandCreator->DeleteContact($idContact));
       $this->query($this->_commandCreator->DeleteContactItems($idContact));
       $this->query($this->_commandCreator->DeleteContactPhoto($idContact));
   }

	protected function _GetUserIdsUpdatedContacts( $dateUpdate )
	{
		$sql = $this->_commandCreator->GetUserIdsUpdatedContacts($dateUpdate);
		$listOfIds = $this->_query($sql);
		if (is_array($listOfIds))
		{
			$result = array();
			foreach ($listOfIds as $anId)
			{
				if (isset($anId['id_user']))
				{
					array_push( $result, $anId['id_user'] );
				}
			}

			return $result;
		}

		return false;
	}

/*
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
 */
}

class FunambolContactModelException extends BaseModelException
{}
