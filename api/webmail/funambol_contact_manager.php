<?php

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

require_once(WM_ROOTPATH.'core/base/base_exception.php');
require_once(WM_ROOTPATH.'core/base/base_manager.php');

require_once(WM_ROOTPATH.'webmail/containers/contact_container.php');
require_once(WM_ROOTPATH.'webmail/containers/group_container.php');

class FunambolContactManager extends BaseManager
{
	private $_funambolUserLogin;

	public function __construct()
	{
		$this->_defaultCommandCreatorPath = WM_ROOTPATH.'webmail/command_creators/funambol_contact_command_creator.php';
		$this->_defaultCommandeCreatorNames[WM_DB_MYSQL] = 'MySqlFunambolContactCommandCreator';
		$this->_defaultCommandeCreatorNames[WM_DB_MSSQLSERVER] = 'MsSqlFunambolContactCommandCreator';
		$this->_defaultModelPath = WM_ROOTPATH.'webmail/models/funambol_contact_model.php';
		$this->_defaultModelName = 'FunambolContactModel';
	}

	public function InitAccount($funambolUserLogin)
	{
		$this->_funambolUserLogin = $funambolUserLogin;
	}

	/**
	 * @return	array
	 */
	protected function _GetFullContactsList()
	{
		return $this->_currentModel->GetFullContactsList($this->_funambolUserLogin);
	}

	/*
	protected function _ExportVcard($obj)
	{
		return $this->_currentModel->ExportVcard($obj);
	}
	 */
	protected function _ReplaceContact(FunambolContactContainer $fnContactContainer)
	{
		$this->_currentModel->ReplaceContact($fnContactContainer);
	}
	protected function _ReplaceContactAddressInfo(FunambolContactContainer $fnContactContainer)
	{
		$this->_currentModel->ReplaceContactAddressInfo($fnContactContainer);
	}
	protected function _ReplaceContactOtherInfo(FunambolContactContainer $fnContactContainer)
	{
		$this->_currentModel->ReplaceContactOtherInfo($fnContactContainer);
	}

   /**
    * @param int $fnContact
    * @return string 
    */
   protected function _DeleteContact($fnContact)
   {
       return $this->_currentModel->DeleteContact($fnContact);
   }

   /**
    * @param int $fnContact
    * @return string
    */
   protected function _DeleteContactItems($fnContact)
   {
       return $this->_currentModel->DeleteContactItems($fnContact);
   }

   /**
    * @param int $fnContact
    * @return string
    */
	protected function _DeleteContactPhoto($fnContact)
	{
		return $this->_currentModel->DeleteContactPhoto($fnContact);
	}

	protected function _GetUserIdsUpdatedContacts( $dateUpdate )
	{
		return $this->_currentModel->GetUserIdsUpdatedContacts( $dateUpdate );
	}


}
