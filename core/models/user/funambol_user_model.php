<?php
require_once(WM_ROOTPATH.'core/base/base_db_model.php');

class FunambolUserModel extends BaseDBModel
{
	protected function _ReplaceUser($userName, $password, $email, $firstName, $lastName)
	{
		$key = 'Omnia Gallia in tres partes divida est';
		$key = substr($key, 0, 24);
		$key = str_pad($key, 24, 0x00);

		$padding = 8 - (strlen($password) % 8);
		$str = str_pad($password, strlen($password) + $padding, chr($padding));
		$res_code = base64_encode(mcrypt_encrypt(MCRYPT_TRIPLEDES, $key, $str, MCRYPT_MODE_ECB));

		$sql = $this->_commandCreator->ReplaceUser($userName, $res_code, $email, $firstName, $lastName);
		$sql1 = $this->_commandCreator->ReplaceUserRole($userName, 'sync_user');

		return $this->_executeSql($sql) && $this->_executeSql($sql1);
	}
}

/**
 * Specyfied error for models family
 */
class FunambolUserModelException extends BaseDBModelException
{}
