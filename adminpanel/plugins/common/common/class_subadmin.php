<?php

class CCommonSubAdmin
{
	/**
	 * @var	int
	 */
	var $Id;

	/**
	 * @var	string
	 */
	var $Login;

	/**
	 * @var	string
	 */
	var $Password;

	/**
	 * @var	string
	 */
	var $Description;

	/**
	 * @var	array
	 */
	var $DomainIds;

	function CCommonSubAdmin()
	{
		$this->Id = -1;
		$this->Login = '';
		$this->Password = '';
		$this->Description = '';
		$this->DomainIds = array();
	}

	function InitByDbRow($row)
	{
		$this->Id = $row->id_admin;
		$this->Login = $row->login;
		$this->Password = $row->password;
		$this->Description = $row->description;
	}

	function SetSessionArray()
	{
		$array = array(
'Id' => $this->Id,
'Login' => $this->Login,
'Password' => $this->Password,
'Description' => $this->Description,
'DomainIds' => $this->DomainIds
				);

			$_SESSION[CM_SESS_SUBADMIN] = $array;
	}

	function ClearSessionArray()
	{
		if (isset($_SESSION[CM_SESS_SUBADMIN]))
		{
			unset($_SESSION[CM_SESS_SUBADMIN]);
		}
	}

	/**
	 * @return bool
	 */
	function IsSessionData()
	{
		return isset($_SESSION[CM_SESS_SUBADMIN]);
	}

	function UpdateFromSessionArray()
	{
		$sessionArray = isset($_SESSION[CM_SESS_SUBADMIN]) ? $_SESSION[CM_SESS_SUBADMIN] : array();
		if (count($sessionArray) > 0)
		{
			$this->Id = ap_Utils::ArrayValue($sessionArray, 'Id', $this->Id);
			$this->Login = ap_Utils::ArrayValue($sessionArray, 'Login', $this->Login);
			$this->Password = ap_Utils::ArrayValue($sessionArray, 'Password', $this->Password);
			$this->Description = ap_Utils::ArrayValue($sessionArray, 'Description', $this->Description);
			$this->DomainIds = ap_Utils::ArrayValue($sessionArray, 'DomainIds', $this->DomainIds);
			
			$this->ClearSessionArray();
		}
	}

	function Validate()
	{
		$return = true;

		if (empty($this->Login))
		{
			$return = ap_Utils::TakePhrase('CM_REQ_FIELDS_CANNOT_BE_EMPTY');
		}
		else if (empty($this->Password))
		{
			$return = ap_Utils::TakePhrase('CM_REQ_FIELDS_CANNOT_BE_EMPTY');
		}
		else if (!is_array($this->DomainIds) || count($this->DomainIds) < 1)
		{
			$return = ap_Utils::TakePhrase('CM_REQ_FIELDS_CANNOT_BE_EMPTY');
		}

		return $return;
	}
}