<?php

require_once(WM_ROOTPATH.'core/base/base_model.php');

	define('START_PAGE_IS_MAILBOX', 0);
	define('START_PAGE_IS_NEW_MESSAGE', 1);
	define('START_PAGE_IS_SETTINGS', 2);
	define('START_PAGE_IS_CONTACTS', 3);
	define('START_PAGE_IS_CALENDAR', 4);

/**
 *  Model for API integration
 */
class WebMailModel extends BaseModel
{
	public function IsReady()
	{
		return true;
	}
	
	public function SetDriver()
	{
		return true;
	}
	
	public function SetComandCreator()
	{
		return true;
	}
	
	/**
	 * @param	Account	$account
	 * @return	bool
	 */
	protected function _CreateAccount($account)
	{
		require_once(WM_ROOTPATH.'common/class_account.php');
		require_once(WM_ROOTPATH.'common/class_dbstorage.php');
		require_once(WM_ROOTPATH.'common/class_mailprocessor.php');
		
		$settings =& Settings::CreateInstance();
		if (!$settings || !$settings->isLoad)
		{
			throw new WebMailModelException('settings error');
		}
		if (!$settings->IncludeLang())
		{
			throw new WebMailModelException('lang error');
		}
		
		$dbStorage =& DbStorageCreator::CreateDatabaseStorage($account);
		if ($dbStorage && $dbStorage->Connect())
		{
			$domain =& $dbStorage->SelectDomainByName(EmailAddress::GetDomainFromEmail($account->Email));
			if (null !== $domain)
			{
				$domain->UpdateAccount($account, $settings);
			}
		}
		else
		{
			throw new WebMailModelException(getGlobalError());
		}

		$validate = $account->ValidateData();
		if ($validate !== true)
		{
			throw new WebMailModelException($validate);
		}

		if ($account->IsInternal)
		{
			require_once(WM_ROOTPATH.'common/class_exim.php');

			$aExplodeArray = explode('@', $account->MailIncLogin, 2);
			if (!isset($aExplodeArray[1]) ||
				!CExim::CreateUserShell($aExplodeArray[0], $aExplodeArray[1], $account->MailboxLimit))
			{
				throw new WebMailModelException(CantCreateUser);
			}

			$dbStorage->InsertAccountData($account);
		}

		$processor = new MailProcessor($account);
		if ($processor && $processor->MailStorage->Connect())
		{
			$inboxSync = $account->GetDefaultFolderSync($settings);

			$user = new User();
			$user->Id = $account->IdUser;

			if (!$dbStorage->IsSettingsExists($account->IdUser))
			{
				if (!$dbStorage->InsertSettings($account))
				{
					throw new WebMailModelException(getGlobalError());
				}
			}

			if ($user->CreateAccount($account, $inboxSync, true))
			{
				return true;
			}
		}
		throw new WebMailModelException(getGlobalError());
	}

	/**
	 * @param	Account	$account
	 * @return	bool
	 */
	protected function _UpdateAccount($account)
	{
		require_once(WM_ROOTPATH.'common/class_account.php');
		require_once(WM_ROOTPATH.'common/class_dbstorage.php');
		require_once(WM_ROOTPATH.'common/class_mailprocessor.php');

		$settings =& Settings::CreateInstance();
		if (!$settings || !$settings->isLoad)
		{
			throw new WebMailModelException('settings error');
		}
		if (!$settings->IncludeLang())
		{
			throw new WebMailModelException('lang error');
		}

		$oldAcct = null;

		$dbStorage =& DbStorageCreator::CreateDatabaseStorage($account);
		if ($dbStorage && $dbStorage->Connect())
		{
			$oldAcct =& $dbStorage->SelectAccountData($account->Id);
			if ($oldAcct)
			{
				if ($account->Email != $oldAcct->Email)
				{
					$domain =& $dbStorage->SelectDomainByName(EmailAddress::GetDomainFromEmail($account->Email));
					if (null !== $domain)
					{
						$domain->UpdateAccount($account, $settings);
					}
				}
			}
			else
			{
				throw new WebMailModelException(getGlobalError());
			}
		}
		else
		{
			throw new WebMailModelException(getGlobalError());
		}

		$validate = $account->ValidateData();
		if (true !== $validate)
		{
			throw new WebMailModelException($validate);
		}
		else
		{
			if ($account->AllowChangeSettings && !$settings->StoreMailsInDb && $account->Email != $oldAcct->Email)
			{
				$_fs = new FileSystem(INI_DIR.'/mail', strtolower($oldAcct->Email), $account->Id);
				if (!$_fs->MoveFolders($account->Email))
				{
					throw new WebMailModelException(PROC_CANT_UPDATE_ACCT);
				}
			}

			if ($account->Update(null))
			{
				return true;
			}
			else
			{
				if (isset($GLOBALS[ErrorDesc]))
				{
					throw new WebMailModelException(getGlobalError());
				}
				else
				{
					throw new WebMailModelException(PROC_CANT_UPDATE_ACCT);
				}
			}
		}
		
		throw new WebMailModelException(getGlobalError());
	}


	/**
	 * @param	int		$idAcct
	 * @return	bool
	 */
	protected function _DeleteAccount($idAcct)
	{
		require_once(WM_ROOTPATH.'common/class_mailprocessor.php');
		require_once(WM_ROOTPATH.'common/class_account.php');
		require_once(WM_ROOTPATH.'common/class_dbstorage.php');
		
		$settings =& Settings::CreateInstance();
		if (!$settings || !$settings->isLoad)
		{
			throw new WebMailModelException('settings error');
		}
		if (!$settings->IncludeLang())
		{
			throw new WebMailModelException('lang error');
		}

		$account = null;
		$dbStorage =& DbStorageCreator::CreateDatabaseStorage($account, $settings);

		if ($dbStorage->Connect())
		{
			$account =& $dbStorage->SelectAccountData($idAcct);
		}

		if ($account)
		{
			$processor = new MailProcessor($account);
			if ($processor->DeleteAccount())
			{
				return true;
			}
		}

		throw new WebMailModelException('Can\'t delete account');
	}

	/**
	 * @param	string	$email
	 * @param	string	$login
	 * @return	bool
	 */
	protected function _AccountExist($email, $login)
	{
		try
		{
			$this->GetAccountByMailLogin($email, $login);
		}
		catch(BaseException $e)
		{
			throw new WebMailModelException('', 0, $e);
		}
		return true;
	}
	
	protected function _GetAccountByMailLogin($email, $login)
	{
		require_once(WM_ROOTPATH.'common/class_account.php');
		require_once(WM_ROOTPATH.'common/class_dbstorage.php');
		require_once(WM_ROOTPATH.'common/class_mailprocessor.php');
		
		$settings =& Settings::CreateInstance();
		if (!$settings || !$settings->isLoad)
		{
			throw new WebMailModelException('settings error');
		}
		if (!$settings->IncludeLang())
		{
			throw new WebMailModelException('lang error');
		}

		$acct = null;
		$dbStorage =& DbStorageCreator::CreateDatabaseStorage($acct, $settings);
		if ($dbStorage->Connect())
		{
			$acct =& $dbStorage->SelectAccountFullDataByLogin($email, $login);
			if ($acct)
			{
				if ($acct->IdDomain > 0)
				{
					$domain =& $dbStorage->SelectDomainById($acct->IdDomain);
					if (null !== $domain)
					{
						$domain->UpdateAccount($acct, $settings);
					}
				}
				return $acct;
			}
		}
		
		throw new WebMailModelException(getGlobalError());
	}

	protected function _GetAccountByEmail($email)
	{
		require_once(WM_ROOTPATH.'common/class_account.php');
		require_once(WM_ROOTPATH.'common/class_dbstorage.php');
		require_once(WM_ROOTPATH.'common/class_mailprocessor.php');

		$settings =& Settings::CreateInstance();
		if (!$settings || !$settings->isLoad)
		{
			throw new WebMailModelException('settings error');
		}
		if (!$settings->IncludeLang())
		{
			throw new WebMailModelException('lang error');
		}

		$account = null;
		$dbStorage =& DbStorageCreator::CreateDatabaseStorage($account, $settings);

		if ($dbStorage->Connect())
		{
			$account =& $dbStorage->SelectAccountDataOnlyByEmail($email);
			if ($account !== null)
			{
				return $account;
			}
		}

		throw new WebMailModelException(getGlobalError());
	}
	
	protected function _GetAccountById($id,$isUserId = false)
	{
		require_once(WM_ROOTPATH.'common/class_account.php');
		require_once(WM_ROOTPATH.'common/class_dbstorage.php');
		require_once(WM_ROOTPATH.'common/class_mailprocessor.php');
		
		$settings =& Settings::CreateInstance();
		if (!$settings || !$settings->isLoad)
		{
			throw new WebMailModelException('settings error');
		}
		if (!$settings->IncludeLang())
		{
			throw new WebMailModelException('lang error');
		}
		
		$acct = null;
		$dbStorage =& DbStorageCreator::CreateDatabaseStorage($acct, $settings);
			
		if ($dbStorage->Connect())
		{
			$acct =& $dbStorage->SelectAccountData($id,true,true,$isUserId);
			if ($acct)
			{
				if ($acct->IdDomain > 0)
				{
					$domain =& $dbStorage->SelectDomainById($acct->IdDomain);
					if (null !== $domain)
					{
						$domain->UpdateAccount($acct, $settings);
					}
				}
				return $acct;
			}
		}
		
		throw new WebMailModelException(getGlobalError());
	}

	/**
	 * @param	string	$email
	 * @param	string	$login
	 * @param	string	$password = null
	 * @return	bool
	 */
	protected function _UserLoginByEmail($email, $login, $password = null)
	{
		require_once(WM_ROOTPATH.'common/class_account.php');
		require_once(WM_ROOTPATH.'common/class_dbstorage.php');
		require_once(WM_ROOTPATH.'common/class_mailprocessor.php');

		$newAccount = new Account();
		$settings =& Settings::CreateInstance();
		if (!$settings || !$settings->isLoad)
		{
			throw new WebMailModelException('settings error');
		}
		if (!$settings->IncludeLang())
		{
			throw new WebMailModelException('lang error');
		}

		$loginArray =& Account::LoadFromDbByLogin($email, $login);
		if ($loginArray != null)
		{
			if ($loginArray[2] == '1')
			{
				if ($password === null)
				{
					@session_write_close();
					@session_name('PHPWEBMAILSESSID');
					@session_start();

					$_SESSION[ACCOUNT_ID] = $loginArray[0];
					$_SESSION[USER_ID] = $loginArray[3];
					return true;
				}	
				else if ($password == ConvertUtils::DecodePassword($loginArray[1], $newAccount))
				{
					@session_write_close();
					@session_name('PHPWEBMAILSESSID');
					@session_start();

					$_SESSION[ACCOUNT_ID] = $loginArray[0];
					$_SESSION[USER_ID] = $loginArray[3];
					return true;
				}
				else
				{
					$account =& Account::LoadFromDb($loginArray[0]);
					$account->MailIncPassword = $password;

					$newprocessor = new MailProcessor($account);
					
					if ($newprocessor->MailStorage->Connect(true))
					{
						if ($account->Update())
						{
							@session_write_close();
							@session_name('PHPWEBMAILSESSID');
							@session_start();
		
							$_SESSION[ACCOUNT_ID] = $account->Id;
							$_SESSION[USER_ID] = $account->IdUser;
							$_SESSION[SESSION_LANG] = $account->DefaultLanguage;
							return true;
						}
						else 
						{
							throw new WebMailModelException(getGlobalError());
						}
					}
					else 
					{
						throw new WebMailModelException(ErrorPOP3IMAP4Auth);
					}
				}
			}
			else 
			{
				throw new WebMailModelException(PROC_CANT_LOG_NONDEF);
			}
		}
		else 
		{
			throw new WebMailModelException(ErrorPOP3IMAP4Auth);
		}
	}
	
	/**
	 * @param	int		$startPage = null
	 * @param	string	$toEmail = null
	 * @param	bool	$isSeparated = false
	 * @return	string|bool
	 */	
	protected function _GetApplicationBaseUrl($startPage = null, $toEmail = null, $isSeparated = false)
	{
		require_once(WM_ROOTPATH.'common/class_settings.php');
		
		$settings =& Settings::CreateInstance();
		if (!$settings || !$settings->isLoad)
		{
			throw new WebMailModelException('settings error');
		}
		if (!isset($_SESSION[ACCOUNT_ID]) || !isset($_SESSION[USER_ID]))
		{
			throw new WebMailModelException('access error');
		}
		
		if ($isSeparated)
		{
			$_SESSION[SEPARATED] = $isSeparated;
		}
		
		$url = 'webmail.php?check=1';
		if (null !== $startPage)
		{
			switch ($startPage)
			{
				case START_PAGE_IS_NEW_MESSAGE:
					$url .= '&start='.START_PAGE_IS_NEW_MESSAGE;
	
					if ($toEmail && strlen($toEmail) > 0)
					{
						$url .= '&to='.$toEmail;
					}
					break;
				case START_PAGE_IS_MAILBOX:
				case START_PAGE_IS_SETTINGS:
				case START_PAGE_IS_CONTACTS:
					$url .= '&start='.$startPage;
					break;
				case START_PAGE_IS_CALENDAR:
					if ($isSeparated)
					{
						$url = 'calendar.php';
					}
					else
					{
						$url .= '&start='.START_PAGE_IS_CALENDAR;
					}
					break;
			}
		}
		
		return $url;
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
		require_once(WM_ROOTPATH.'common/class_settings.php');
		require_once(WM_ROOTPATH.'common/class_account.php');
		require_once(WM_ROOTPATH.'common/class_webmailmessages.php');
		require_once(WM_ROOTPATH.'common/class_smtp.php');

		$settings =& Settings::CreateInstance();
		if (!$settings || !$settings->isLoad)
		{
			throw new WebMailModelException('settings error');
		}
		if (!$settings->IncludeLang())
		{
			throw new WebMailModelException('lang error');
		}
		
		$message = new WebMailMessage();
		$GLOBALS[MailDefaultCharset] = $account->GetUserCharset();
		$GLOBALS[MailInputCharset] = $account->GetUserCharset();
		$GLOBALS[MailOutputCharset] = $account->GetDefaultOutCharset();

		$message->Headers->SetHeaderByName(MIMEConst_MimeVersion, '1.0');
		$message->Headers->SetHeaderByName(MIMEConst_XMailer, XMAILERHEADERVALUE);

		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
		if (null !== $ip)
		{
			$message->Headers->SetHeaderByName(MIMEConst_XOriginatingIp, $ip);
		}

		$message->IdMsg = -1;
		$message->Uid = -1;

		$_serverAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['SERVER_NAME'] : 'cantgetservername';
		$message->Headers->SetHeaderByName(MIMEConst_MessageID,
			'<'.substr(session_id(), 0, 7).'.'.md5(time()).'@'. $_serverAddr .'>');

		$message->SetFromAsString($account->GetFriendlyEmail());
		$message->SetToAsString(implode(', ', $to));
		$message->SetSubject($subject);
		$message->SetDate(new CDateTime(time()));

		if ($isBodyHtml)
		{
			$message->TextBodies->HtmlTextBodyPart =
				ConvertUtils::AddHtmlTagToHtmlBody(
					str_replace("\n", CRLF,
					str_replace("\r", '', ConvertUtils::WMBackHtmlNewCode($bodyText))));
		}
		else
		{
			$message->TextBodies->PlainTextBodyPart =
				str_replace("\n", CRLF,
				str_replace("\r", '', ConvertUtils::WMBackHtmlNewCode($bodyText)));
		}

		if (count($attachmentsFileName) > 0)
		{
			foreach ($attachmentsFileName as $attachmentFileName)
			{
				$message->Attachments->AddFromFile($attachmentFileName,
					basename($attachmentFileName),
					ConvertUtils::GetContentTypeFromFileName(basename($attachmentFileName)));
			}
		}

		$message->OriginalMailMessage = $message->ToMailString(true);
		$message->Flags |= MESSAGEFLAGS_Seen;

		return CSmtp::SendMail($settings, $account, $message, $account->GetFriendlyEmail(), implode(', ', $to));
	}

	/**
	 * @param string $email
	 * @return int
	 */
	protected function _CheckCountOfUserAccounts($email)
	{
		require_once(WM_ROOTPATH.'common/class_account.php');
		require_once(WM_ROOTPATH.'common/class_dbstorage.php');

		$settings =& Settings::CreateInstance();
		if (!$settings || !$settings->isLoad)
		{
			throw new WebMailModelException('settings error');
		}
		if (!$settings->IncludeLang())
		{
			throw new WebMailModelException('lang error');
		}

		$acct = null;
		$dbStorage =& DbStorageCreator::CreateDatabaseStorage($acct, $settings);
		if ($dbStorage->Connect())
		{
			return $dbStorage->CheckCountOfUserAccounts($email);
		}

		throw new WebMailModelException(getGlobalError());
	}
}

class WebMailModelException extends BaseModelException
{}
