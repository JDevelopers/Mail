<?php

require_once WM_ROOTPATH.'core/base/base_container.php';

class ContactContainer extends BaseContainer
{
	const MAGIC_CONTACT_STR_ID_PREFIX = '040000008200E00074C5B7101A82E008';

	public function ContactContainer(ContactContainerWithSingleGroup $contactContainer = null)
	{
		BaseContainer::BaseContainer();
		if (null !== $contactContainer)
		{
			$this->MassSetValue($contactContainer->GetContainer());
		}
	}

	protected function _initContainerField()
	{
		$this->_container['IdAddress'] = null;
		$this->_container['IdUser'] = null;
		$this->_container['StrId'] = null;
		$this->_container['Deleted'] = 0;
		$this->_container['FunambolContactId'] = null;
		$this->_container['HomeEmail'] = null;
		$this->_container['Title'] = null;
		$this->_container['FullName'] = null;
		$this->_container['FirstName'] = null;
		$this->_container['SurName'] = null;
		$this->_container['NickName'] = null;
		$this->_container['Notes'] = null;
		$this->_container['UseFriendlyName'] = null;
		$this->_container['HomeStreet'] = null;
		$this->_container['HomeCity'] = null;
		$this->_container['HomeState'] = null;
		$this->_container['HomeZip'] = null;
		$this->_container['HomeCountry'] = null;
		$this->_container['HomePhone'] = null;
		$this->_container['HomeFax'] = null;
		$this->_container['HomeMobile'] = null;
		$this->_container['HomeWeb'] = null;
		$this->_container['BusinessEmail'] = null;
		$this->_container['BusinessCompany'] = null;
		$this->_container['BusinessStreet'] = null;
		$this->_container['BusinessCity'] = null;
		$this->_container['BusinessState'] = null;
		$this->_container['BusinessZip'] = null;
		$this->_container['BusinessCountry'] = null;
		$this->_container['BusinessJobTitle'] = null;
		$this->_container['BusinessDepartment'] = null;
		$this->_container['BusinessOffice'] = null;
		$this->_container['BusinessPhone'] = null;
		$this->_container['BusinessMobile'] = null;
		$this->_container['BusinessFax'] = null;
		$this->_container['BusinessWeb'] = null;
		$this->_container['OtherEmail'] = null;
		$this->_container['PrimaryEmail'] = null;
		$this->_container['IdPreviousAddress'] = null;
		$this->_container['Temp'] = null;
		$this->_container['BirthdayDay'] = null;
		$this->_container['BirthdayMonth'] = null;
		$this->_container['BirthdayYear'] = null;
		$this->_container['DateCreated'] = null;
		$this->_container['DateModified'] = null;
		$this->_container['Groups'] = null;
	}

	public function GenerateStrId()
	{
		if (0 === strlen($this->GetValue('StrId', 'string')))
		{
			$IdAddress = $this->GetValue('IdAddress', 'int', '0');
			if (0 < $IdAddress)
			{
				$this->SetValue('StrId', self::MAGIC_CONTACT_STR_ID_PREFIX.$IdAddress);
			}
		}
	}
}

class ContactContainerWithSingleGroup extends ContactContainer
{
	public function ContactContainerWithSingleGroup(ContactContainer $contactContainer = null)
	{
		parent::ContactContainer();
		if (null !== $contactContainer)
		{
			$this->MassSetValue($contactContainer->GetContainer());
		}
	}

	protected function _initContainerField()
	{
		parent::_initContainerField();

		$this->_container['GroupId'] = null;
		$this->_container['GroupStrId'] = null;
		$this->_container['GroupName'] = null;
		unset($this->_container['Groups']);
	}

	public function InitByGroup($groupContainer)
	{
		$this->_container['GroupId'] = $groupContainer->GetValue('GroupId', 'int');
		$this->_container['GroupStrId'] = $groupContainer->GetValue('GroupStrId');
		$this->_container['GroupName'] = $groupContainer->GetValue('GroupName');
	}
}
