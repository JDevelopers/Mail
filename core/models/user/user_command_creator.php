<?php
require_once(WM_ROOTPATH.'core/base/base_command_creator.php');
class UserCommandCreator extends BaseCommandCreator
{
	public function CreateUser()
    {
        $sql = 'INSERT INTO %sa_users (deleted) VALUES (0)';
        $sql = sprintf($sql, $this->_dbPrefix);
        return $sql;
    }

	public function DeleteUser($userId)
    {
        $sql = "DELETE FROM %sa_users WHERE id_user = %d";
        $sql = sprintf($sql, $this->_dbPrefix, $userId);
        return $sql;
    }

	public function IsUserExists($userId)
	{
		$sql = 'SELECT id_user AS userId FROM %sa_users WHERE id_user = %d';
		$sql = sprintf($sql, $this->_dbPrefix, $userId);
        return $sql;
	}
}

