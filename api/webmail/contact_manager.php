<?php

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

require_once(WM_ROOTPATH.'core/base/base_exception.php');
require_once(WM_ROOTPATH.'core/base/base_manager.php');

require_once(WM_ROOTPATH.'webmail/containers/contact_container.php');
require_once(WM_ROOTPATH.'webmail/containers/group_container.php');

class ContactManager extends BaseManager
{
	private $_account;
	
	public function __construct()
	{
		$this->_defaultCommandCreatorPath = WM_ROOTPATH.'webmail/command_creators/contact_command_creator.php';
		$this->_defaultCommandeCreatorNames[WM_DB_MYSQL] = 'MySqlContactCommandCreator';
		$this->_defaultCommandeCreatorNames[WM_DB_MSSQLSERVER] = 'MsSqlContactCommandCreator';
		$this->_defaultModelPath = WM_ROOTPATH.'webmail/models/contact_model.php';
		$this->_defaultModelName = 'ContactModel';
	}

	public function InitAccount(&$account)
	{
		$this->_account =& $account;
	}

	/**
	 * @return	array
	 */
	protected function _GetFullContactsList($includeDeleted = false)
	{
		return $this->_currentModel->GetFullContactsList($this->_account->IdUser, $includeDeleted);
	}

	protected function _ExportVcard($obj)
	{
		return $this->_currentModel->ExportVcard($obj);
	}

	protected function _ImportVcard($filePath)
	{
		$contactContainers = $this->_currentModel->ImportVcard($filePath);
		if (is_array($contactContainers))
		{
			if (count($contactContainers) > 0)
			{
				$existingContactsStrIds = $this->_currentModel->GetFullContactsStrIdsList($this->_account->IdUser);
				$existingGroupsStrIds = $this->_currentModel->GetFullGroupsStrIdsList($this->_account->IdUser);

				foreach ($contactContainers as $contactContainer)
				{
					$contactStrId = $contactContainer->GetValue('StrId', 'string');
					$groupStrId = $contactContainer->GetValue('GroupStrId', 'string');

					if (0 < strlen($contactStrId) && isset($existingContactsStrIds[$contactStrId]))
					{
						unset($existingContactsStrIds[$contactStrId]);
					}

					if (0 < strlen($groupStrId) && isset($existingGroupsStrIds[$groupStrId]))
					{
						unset($existingGroupsStrIds[$groupStrId]);
					}

					$this->_currentModel->ProcessParsedContact($this->_account->IdUser, $contactContainer);
				}

				$this->_currentModel->RemoveContactsAndGroupsByStrIds($this->_account->IdUser,
					$existingContactsStrIds, $existingGroupsStrIds);
			}
			else
			{
				$this->_currentModel->RemoveAllContactsAndGroups($this->_account->IdUser);
			}
		}

		return true;
	}

	protected function _UpdateContact(ContactContainer $contactContainer)
	{
		return $this->_currentModel->UpdateContact($contactContainer);
	}

	protected function _CreateContact(ContactContainer $contactContainer)
	{
		return $this->_currentModel->CreateContact($contactContainer);
	}

	protected function _UpdateFunambolContactId(ContactContainer $contactContainer)
	{
		return $this->_currentModel->UpdateFunambolContactId($contactContainer);
	}

	protected function _GetUserIdsUpdatedContacts( $dateUpdate )
	{
		return $this->_currentModel->GetUserIdsUpdatedContacts( $dateUpdate );
	}
}
