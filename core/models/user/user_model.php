<?php
require_once(WM_ROOTPATH.'core/base/base_db_model.php');
class UserModel extends BaseDBModel
{
	const ERR_MSG_CREATE_USER = 'ERR_MSG_CREATE_USER';
	const ERR_CODE_CREATE_USER = 1;

	const ERR_MSG_DELETE_USER = 'ERR_MSG_DELETE_USER';
	const ERR_CODE_DELETE_USER = 2;

	const ERR_MSG_IS_USER_EXISTS = 'ERR_MSG_IS_USER_EXISTS';
	const ERR_NO_IS_USER_EXISTS = 3;

	const ERR_MSG_GET_USER_ID_BY_EMAIL = 'ERR_MSG_GET_USER_ID_BY_EMAIL';
	const ERR_NO_GET_USER_ID_BY_EMAIL = 3;

	protected function _CreateUser()
	{
		$this->_errorMsg = self::ERR_MSG_CREATE_USER;
		$this->_errorNo = self::ERR_CODE_CREATE_USER;
		$sql = $this->_commandCreator->CreateUser();
		$this->_executeSql($sql);
		$result = $this->_getLastInsertId();
		return $result;
	}

	protected function _DeleteUser($userId)
	{
		$this->_errorMsg = self::ERR_MSG_DELETE_USER;
		$this->_errorNo = self::ERR_CODE_DELETE_USER;
		$sql = $this->_commandCreator->DeleteUser($userId);
		$this->_executeSql($sql);
		return true;
	}

	protected function _IsUserExists($userId)
	{
		$this->_errorMsg = self::ERR_MSG_IS_USER_EXISTS;
		$this->_errorNo = self::ERR_NO_IS_USER_EXISTS;
		$sql = $this->_commandCreator->IsUserExists($userId);
		$dbResult = $this->_query($sql);
		if (count($dbResult)>0 && $userId == (int)$dbResult[0]['userId'])
		{
			return true;
		}
		return false;
	}
}

/**
 * Specyfied error for models family
 */
class UserModelException extends BaseDBModelException
{}