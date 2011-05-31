<?php
class ManagerAPI
{
//	static private $_listMangers;

	const USER = 'UserManager';

	public function GetManager($managerName)
	{
		$managerName = strtoupper($managerName);
		$manager = self::$managerName;
		return new $manager;
	}
}