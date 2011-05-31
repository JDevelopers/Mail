<?php

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'./../'));

require_once WM_ROOTPATH.'api/user/funambol_user_manager.php';
require_once WM_ROOTPATH.'common/inc_funambol_constants.php';

class FunambolSyncUsers
{
	/**
	 * @var Account
	 */
	var $_account	= null;
	var $_settings	= null;

	public function  __construct(&$account)
	{
		$this->_account  = $account;
	}

	public function PerformSync()
	{
		$fnUserManager = new FunambolUserManager();

		if (!$fnUserManager->InitManager())
		{
			return false;
		}

		$fnUserManager->ReplaceUser(
				$this->_account->Email,							//	$userName,
				$this->_account->MailIncPassword,				//	$password,
				$this->_account->Email,							//	$email,
				$this->_account->FriendlyName,					//	$firstName,
				FUNAMBOL_USER_PREFIX.$this->_account->IdUser	//	$lastName
		);

		return true;
	}
}
?>
