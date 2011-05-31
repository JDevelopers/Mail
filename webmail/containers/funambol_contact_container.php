<?php

require_once WM_ROOTPATH.'core/base/base_container.php';

class FunambolContactContainer extends BaseContainer
{
	public function FunambolContactContainer()
	{
		BaseContainer::BaseContainer();
	}

	protected function _initContainerField()
	{
		$this->_container['id'] = null;
		$this->_container['userid'] = null;
		$this->_container['last_update'] = null;
		$this->_container['status'] = 'N';
		$this->_container['photo_type'] = 0;
		$this->_container['importance'] = 1;
		$this->_container['sensitivity'] = null;
		$this->_container['subject'] = null;
		$this->_container['folder'] = 'DEFAULT_FOLDER';
		$this->_container['anniversary'] = null;
		$this->_container['first_name'] = null;
		$this->_container['middle_name'] = null;
		$this->_container['last_name'] = null;
		$this->_container['display_name'] = null;
		$this->_container['birthday'] = null;
		$this->_container['body'] = null;
		$this->_container['categories'] = null;
		$this->_container['children'] = null;
		$this->_container['hobbies'] = null;
		$this->_container['initials'] = null;
		$this->_container['languages'] = null;
		$this->_container['nickname'] = null;
		$this->_container['spouce'] = null;
		$this->_container['suffix'] = null;
		$this->_container['title'] = null;
		$this->_container['gender'] = null;
		$this->_container['assistant'] = null;
		$this->_container['company'] = null;
		$this->_container['department'] = null;
		$this->_container['job_title'] = null;
		$this->_container['manager'] = null;
		$this->_container['mileage'] = null;
		$this->_container['office_location'] = null;
		$this->_container['profession'] = null;
		$this->_container['companies'] = null;

		$this->_container['HomeStreet'] = null;
		$this->_container['HomeCity'] = null;
		$this->_container['HomeState'] = null;
		$this->_container['HomeZip'] = null;
		$this->_container['HomeCountry'] = null;

		$this->_container['BusinessStreet'] = null;
		$this->_container['BusinessCity'] = null;
		$this->_container['BusinessState'] = null;
		$this->_container['BusinessZip'] = null;
		$this->_container['BusinessCountry'] = null;

		$this->_container['HomePhone'] = null;
		$this->_container['HomeFax'] = null;
		$this->_container['HomeMobile'] = null;
		$this->_container['HomeEmail'] = null;
		$this->_container['HomeWeb'] = null;

		$this->_container['BusinessPhone'] = null;
		$this->_container['BusinessFax'] = null;
		$this->_container['BusinessEmail'] = null;

		$this->_container['OtherEmail'] = null;

		//$this->_container[''] = null;
	}
}
