<?php

require_once WM_ROOTPATH.'core/base/base_container.php';

class GroupContainer extends BaseContainer
{
	const MAGIC_GROUP_STR_ID_PREFIX = '5765624D61696C50726F';
	
	public function GroupContainer(ContactContainerWithSingleGroup $contactContainer = null)
	{
		BaseContainer::BaseContainer();
		if (null !== $contactContainer)
		{
			$this->MassSetValue($contactContainer->GetContainer());
		}
	}

	protected function _initContainerField()
	{
		$this->_container['GroupId'] = null;
		$this->_container['GroupStrId'] = null;
		$this->_container['IdUser'] = null;
		$this->_container['GroupName'] = null;
	}

	public function GenerateStrId()
	{
		if (0 === strlen($this->GetValue('GroupStrId', 'string')))
		{
			$IdGroup = $this->GetValue('GroupId', 'int', '0');
			if (0 < $IdGroup)
			{
				$this->SetValue('GroupStrId', self::MAGIC_GROUP_STR_ID_PREFIX.$IdGroup);
			}
		}
	}
}
