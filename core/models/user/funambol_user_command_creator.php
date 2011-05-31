<?php
require_once(WM_ROOTPATH.'core/base/base_command_creator.php');

/**
 * SQL script generator for user
 */
class FunambolUserCommandCreator extends BaseCommandCreator {}

class MySqlFunambolUserCommandCreator extends FunambolUserCommandCreator
{
	function ReplaceUser($userName, $password, $email, $firstName, $lastName)
	{
		$sql = 'REPLACE INTO %sfnbl_user VALUES (%s, %s, %s, %s, %s)';
		$sql = sprintf($sql, $this->_dbPrefix,
				$this->_escapeString($userName),
				$this->_escapeString($password),
				$this->_escapeString($email),
				$this->_escapeString($firstName),
				$this->_escapeString($lastName));

		return $sql;
	}

	function ReplaceUserRole($userName, $role)
	{
		$sql = 'REPLACE INTO %sfnbl_user_role VALUES (%s, %s)';
		$sql = sprintf($sql, $this->_dbPrefix,
				$this->_escapeString($userName),
				$this->_escapeString($role));

		return $sql;
	}
}

class MsSqlFunambolUserCommandCreator extends FunambolUserCommandCreator {}


?>
