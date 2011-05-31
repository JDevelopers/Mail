<?php

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));

require_once(WM_ROOTPATH.'core/base/base_exception.php');
require_once(WM_ROOTPATH.'core/base/base_manager.php');
require_once(WM_ROOTPATH.'common/class_account.php');

class WebMailManager extends BaseManager
{
	public function __construct()
	{
		$this->_defaultCommandCreatorPath = WM_ROOTPATH.'webmail/command_creators/webmail_command_creator.php';
		$this->_defaultCommandeCreatorNames[WM_DB_MYSQL] = 'WebMailCommandCreator';
		$this->_defaultCommandeCreatorNames[WM_DB_MSSQLSERVER] = 'WebMailCommandCreator';
		
		$this->_defaultModelPath = WM_ROOTPATH.'webmail/models/webmail_model.php';
		$this->_defaultModelName = 'WebMailModel';
	}
	
	/**
	 * @param	Account	$account
	 * @return	bool
	 */
	protected function _CreateAccount($account)
	{
		return $this->_currentModel->CreateAccount($account);
	}

	/**
	 * @param	Account	$account
	 * @return	bool
	 */
	protected function _UpdateAccount($account)
	{
		return $this->_currentModel->UpdateAccount($account);
	}

	/**
	 * @param	int		$idAcct
	 * @return	bool
	 */
	protected function _DeleteAccount($idAcct)
	{
		return $this->_currentModel->DeleteAccount($idAcct);
	}
	
	/**
	 * @param	string	$email
	 * @param	string	$login
	 * @return	bool
	 */
	protected function _AccountExist($email, $login)
	{
		return $this->_currentModel->AccountExist($email, $login);
	}

	/**
	 * @param	string	$email
	 * @param	string	$login
	 * @param	string	$password = null
	 * @return	bool
	 */
	protected function _UserLoginByEmail($email, $login, $password = null)
	{
		return $this->_currentModel->UserLoginByEmail($email, $login, $password);
	}
	
	/**
	 * @param	int		$startPage = null
	 * @param	string	$toEmail = null
	 * @param	bool	$isSeparated = false
	 * @return	string|bool
	 */	
	protected function _GetApplicationBaseUrl($startPage = null, $toEmail = null, $isSeparated = false)
	{
		return $this->_currentModel->GetApplicationBaseUrl($startPage, $toEmail, $isSeparated);
	}

	/**
	 * @param	string	$email
	 * @param	string	$login
	 * @return	object
	 */
	protected function _GetAccountByMailLogin($email, $login)
	{
		return $this->_currentModel->GetAccountByMailLogin($email, $login);
	}
	
	/**
	 * @param	string	$email
	 * @return	array
	 */
	protected function _GetAccountByEmail($email)
	{
		return $this->_currentModel->GetAccountByEmail($email);
	}

	/**
	 * @param	int	$id
	 * @return	array
	 */
	protected function _GetAccountById($id)
	{
		return $this->_currentModel->GetAccountById($id);
	}

	protected function _GetAccountByUserId($id)
	{
		return $this->_currentModel->GetAccountById($id,true);
	}

	/**
	 * @param	Account		$account
	 * @param	array		$to
	 * @param	string		$subject
	 * @param	string		$bodyText
	 * @param	bool		$isBodyHtml = false
	 * @param	array		$attachmentsFileName = array()
	 * @return	bool
	 */
	protected function _SendMessage($account, $to, $subject, $bodyText, $isBodyHtml = false, $attachmentsFileName = array())
	{
		return $this->_currentModel->SendMessage($account, $to, $subject, $bodyText, $isBodyHtml, $attachmentsFileName);
	}

	/**
	 * Check if this email  is multiuser.
	 * Return 0 if bd has no this user,
	 * 1 - one user in db,
	 * 2 and more - a few accounts has this email.
	 *
	 * @param string $email
	 * @return int
	 */
	protected function _CheckCountOfUserAccounts($email)
	{
		return $this->_currentModel->CheckCountOfUserAccounts($email);
	}

}