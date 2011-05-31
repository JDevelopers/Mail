<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	include_once CAdminPanel::RootPath().'/core/db/commandcreator.php';

	/**
	 * @abstract
	 */
	class main_CommandCreator extends baseMain_CommandCreator
	{
		/**
		 * @param	int		$type
		 * @param	array	$columnEscape
		 * @param	string	$prefix
		 * @return	main_CommandCreator
		 */
		function main_CommandCreator($type, $columnEscape = array('', ''), $prefix = '')
		{
			baseMain_CommandCreator::baseMain_CommandCreator($type, $columnEscape, $prefix);
		}
		
		/**
		 * @param	string	$_dbName
		 * @return	string
		 */
		function CreateDatabase($_dbName)
		{
			$sql = 'CREATE DATABASE %s';
			return sprintf($sql, $this->_escapeColumn($_dbName));
		}

		function CreateFunctions()
		{
			return false;
		}
		
		/**
		 * @param	string	$_dbName
		 * @return	string
		 */
		function DropDatabase($_dbName)
		{
			$sql = 'DROP DATABASE %s';
			return sprintf($sql, $this->_escapeColumn($_dbName));
		}

		/**
		 * @return	string
		 */
		function InsertNewUser()
		{
			$sql = 'INSERT INTO %sa_users (deleted) VALUES (%d)';
			return sprintf($sql, $this->_prefix, 0);
		}
		
		/**
		 * @param int $userId
		 * @return string
		 */
		function DeleteUserData($userId)
		{
			$sql = 'DELETE FROM %sa_users WHERE id_user = %d';
			return sprintf($sql, $this->_prefix, $userId);
		}
		
		/**
		 * @param int $userId
		 * @return string
		 */
		function ClearSettings($userId)
		{
			$sql = 'DELETE FROM %sawm_settings WHERE id_user = %d';
			return sprintf($sql, $this->_prefix, $userId);
		}
		
		/**
		 * @param int $userId
		 * @return string
		 */
		function CountAccountsByUserId($userId)
		{
			$sql = 'SELECT COUNT(id_acct) AS cnt FROM %sawm_accounts WHERE id_user = %d';
			return sprintf($sql, $this->_prefix, $userId);
		}
		
		/**
		 * @param	int		$domainId
		 * @param	string	$condition[optional] = null
		 * @return	string
		 */
		function GetAccountCount($domainId, $condition = null)
		{
            $add = '';
			if ($condition !== null && strlen($condition) > 0)
			{
				$add = ' AND email LIKE '.$this->_escapeString('%'.$condition.'%');
			}
			
			/*$sql = '
SELECT COUNT(acct.id_acct) AS acct_cnt
FROM %sawm_accounts AS acct
INNER JOIN %sawm_settings AS sett ON acct.id_user = sett.id_user WHERE id_domain = %d%s';
			*/
			
			$sql = 'SELECT COUNT(id_acct) AS acct_cnt FROM %sawm_accounts WHERE id_domain = %d%s';
			return sprintf($sql, $this->_prefix, $domainId, $add);
		}

		function DeleteFunambolContacts($userid)
		{
			$sql = 'DELETE FROM %sfnbl_pim_contact WHERE userid=%s';

			return sprintf($sql, $this->_prefix, $this->_escapeString($userid));
		}

		function DeleteFunambolEvents($userid)
		{
			$sql = 'DELETE FROM %sfnbl_pim_calendar WHERE userid=%s';

			return sprintf($sql, $this->_prefix, $this->_escapeString($userid));
		}


		/**
		 * @return	string
		 */
		function AllUserCount()
		{
			$sql = 'SELECT COUNT(id_user) AS user_cnt FROM %sa_users WHERE deleted = 0';
			return sprintf($sql, $this->_prefix);
		}
		
		/**
		 * @param int $acctId
		 * @return string
		 */
		function GetUserIdFromAcctId($acctId)
		{
			$sql = 'SELECT id_user FROM %sawm_accounts WHERE id_acct = %d';
			return sprintf($sql, $this->_prefix, $acctId);
		}
		
		/**
		 * @param Account $account
		 * @return string
		 */
		function InsertSettings(&$account)
		{
			$sql = '
INSERT INTO %sawm_settings (id_user, msgs_per_page, white_listing, x_spam, last_login,
	logins_count, def_skin, def_lang, def_charset_inc, def_charset_out,
	def_timezone, def_date_fmt, hide_folders, mailbox_limit, allow_change_settings,
	allow_dhtml_editor, allow_direct_mode, hide_contacts, db_charset,
	horiz_resizer, vert_resizer, mark, reply, contacts_per_page, view_mode)
VALUES(%d, %d, %d, %d, %s, %d, %s, %s, %d, %d, %d, %s,
	%d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d)';
			
			$date = new CDateTime(time());
			return sprintf($sql, $this->_prefix,
									$account->IdUser,
									$account->MailsPerPage,
									$account->WhiteListing,
									$account->XSpam,
									$this->UpdateDateFormat($date->ToANSI()),
									$account->LoginsCount,
									$this->_escapeString($account->DefaultSkin),
									$this->_escapeString($account->DefaultLanguage),
									ap_Utils::GetCodePageNumber($account->DefaultIncCharset),
									ap_Utils::GetCodePageNumber($account->DefaultOutCharset),
									$account->DefaultTimeZone,
									$this->_escapeString($account->DefaultDateFormat),
									$account->HideFolders,
									$account->MailboxLimit,
									$account->AllowChangeSettings,
									$account->AllowDhtmlEditor,
									$account->AllowDirectMode,
									$account->HideContacts,
									ap_Utils::GetCodePageNumber($account->DbCharset),
									$account->HorizResizer,
									$account->VertResizer,
									$account->Mark,
									$account->Reply,
									$account->ContactsPerPage,
									$account->ViewMode);
		}
		
		/**
		 * @param Account $account
		 * @return string
		 */
		function InsertAccount($account)
		{
			$sql = '
INSERT INTO %sawm_accounts (id_user, def_acct, deleted, email, mail_protocol,
	mail_inc_host, mail_inc_login, mail_inc_pass, mail_inc_port, mail_out_host,
	mail_out_login, mail_out_pass, mail_out_port, mail_out_auth, friendly_nm,
	use_friendly_nm, def_order, getmail_at_login, mail_mode, mails_on_server_days,
	signature, signature_type, signature_opt, delimiter, personal_namespace, id_domain, mailing_list, imap_quota)
VALUES (%d, %d, %d, %s, %d, %s, %s, %s, %d, %s, %s, %s, %d, %d, %s, %d, %s, %d, %d, %d,
	%s, %d,	%d, %s, %s, %d, %d, %d)';
			
			return sprintf($sql, $this->_prefix,
									$account->IdUser,
									$account->DefaultAccount,
									$account->Deleted,
									$this->_escapeString($account->Email),
									$account->MailProtocol,
									$this->_escapeString($account->MailIncHost),
									$this->_escapeString($account->MailIncLogin),
									$this->_escapeString(ap_Utils::EncodePassword($account->MailIncPassword)),
									$account->MailIncPort,
									$this->_escapeString($account->MailOutHost),
									$this->_escapeString($account->MailOutLogin),
									$this->_escapeString(ap_Utils::EncodePassword($account->MailOutPassword)),
									$account->MailOutPort,
									$account->MailOutAuthentication,
									$this->_escapeString($account->FriendlyName),
									$account->UseFriendlyName,
									$account->DefaultOrder,
									$account->GetMailAtLogin,
									$account->MailMode,
									$account->MailsOnServerDays,
									$this->_escapeString($account->Signature),
									$account->SignatureType,
									$account->SignatureOptions,
									$this->_escapeString($account->Delimiter),
									$this->_escapeString($account->NameSpace),
									$account->DomainId,
									$account->IsMailList, $account->ImapQuota);
		}
		
		/**
	 	 * @param int $accountId
		 * @return string
		 */
		function SelectAccountData($accountId)
		{
			$sql = '
SELECT id_acct, acct.id_user as id_user, def_acct, deleted, email, mail_protocol,
	mail_inc_host, mail_inc_login, mail_inc_pass, mail_inc_port, mail_out_host,
	mail_out_login, mail_out_pass, mail_out_port, mail_out_auth, friendly_nm,
	use_friendly_nm, def_order,	getmail_at_login, mail_mode, mails_on_server_days,
	signature_type, signature_opt, delimiter, personal_namespace, id_domain,
	msgs_per_page, white_listing, x_spam, %s as last_login, logins_count,	def_skin,
	def_lang, def_charset_inc, def_charset_out, def_timezone, def_date_fmt,
	hide_folders, mailbox_limit, mailbox_size, allow_change_settings,
	allow_dhtml_editor,	allow_direct_mode, hide_contacts, db_charset,
	horiz_resizer, vert_resizer, mark, reply, contacts_per_page, view_mode, mailing_list, imap_quota
FROM %sawm_accounts AS acct
INNER JOIN %sawm_settings AS sett ON acct.id_user = sett.id_user
WHERE id_acct = %d';
			
			return sprintf($sql, $this->GetDateFormat('last_login'), $this->_prefix, $this->_prefix, $accountId);
		}

		/**
		 * @param int $accountId
		 * @return string
		 */
		function SelectSignature($accountId)
		{
			$sql = 'SELECT id_acct, signature FROM %sawm_accounts WHERE id_acct = %d';
			return sprintf($sql, $this->_prefix, $accountId);
		}

		function IsInternalAccountExist($login)
		{
			$sql = 'SELECT count(accts.id_acct) as acount
					FROM %sawm_accounts as accts
					INNER JOIN %sawm_domains as dmns ON accts.id_domain = dmns.id_domain
					WHERE accts.mail_inc_login = %s AND accts.def_acct = 1 AND dmns.is_internal = 1';

			return sprintf($sql, $this->_prefix, $this->_prefix, $this->_escapeString($login));
		}
		
		/**
		 * @param int $accountId
		 * @param string $accountDelimiter
		 * @return string
		 */
		function UpdateAccountDelimiter($accountId, $accountDelimiter)
		{
			$sql = 'UPDATE %sawm_accounts SET delimiter = %s WHERE id_acct = %d';
			return sprintf($sql, $this->_prefix, $this->_escapeString($accountDelimiter), $accountId);		
		}
		
		/**
		 * @param int $accountId
		 * @param string $accountNameSpace
		 * @return string
		 */
		function UpdateAccountNameSpace($accountId, $accountNameSpace)
		{
			$sql = 'UPDATE %sawm_accounts SET personal_namespace = %s WHERE id_acct = %d';
			return sprintf($sql, $this->_prefix, $this->_escapeString($accountNameSpace), $accountId);
		}

		function SelectCountOfActiveCalendars()
		{
			$sql = 'SELECT COUNT(calendar_id) AS cnt FROM %sacal_calendars WHERE calendar_active = 1';
			return sprintf($sql, $this->_prefix);		
		}

		function SetAllCalendarsActive()
		{
			$sql = 'UPDATE %sacal_calendars SET calendar_active = 1';
			return sprintf($sql, $this->_prefix);
		}
		
		/**
		 * @param Account $account
		 * @return string
		 */
		function UpdateAccount(&$account)
		{
			$sql = '
UPDATE %sawm_accounts SET
	id_user = %d,
	def_acct = %d, deleted = %d, email = %s, mail_protocol = %d,
	mail_inc_host = %s, mail_inc_login = %s, mail_inc_pass = %s, mail_inc_port = %d,
	mail_out_host = %s, mail_out_login = %s, mail_out_pass = %s, mail_out_port = %d,
	mail_out_auth = %d, friendly_nm = %s, use_friendly_nm = %d, def_order = %d,
	getmail_at_login = %d, mail_mode = %d, mails_on_server_days = %d,
	signature = %s, signature_type = %d, signature_opt = %d, 
	delimiter = %s, personal_namespace = %s
WHERE id_acct = %d';
			
			return sprintf($sql, $this->_prefix,
									$account->IdUser,
									$account->DefaultAccount,
									$account->Deleted,
									$this->_escapeString($account->Email),
									$account->MailProtocol,
									$this->_escapeString($account->MailIncHost),
									$this->_escapeString($account->MailIncLogin),
									$this->_escapeString(ap_Utils::EncodePassword($account->MailIncPassword)),
									$account->MailIncPort,
									$this->_escapeString($account->MailOutHost),
									$this->_escapeString($account->MailOutLogin),
									$this->_escapeString(ap_Utils::EncodePassword($account->MailOutPassword)),
									$account->MailOutPort,
									$account->MailOutAuthentication,
									$this->_escapeString($account->FriendlyName),
									$account->UseFriendlyName,
									$account->DefaultOrder,
									$account->GetMailAtLogin,
									$account->MailMode,
									$account->MailsOnServerDays,
									$this->_escapeString($account->Signature),
									$account->SignatureType,
									$account->SignatureOptions,
									$this->_escapeString($account->Delimiter),
									$this->_escapeString($account->NameSpace),
									$account->Id);		
		}
		
		/**
		 * @param Account $account
		 * @return string
		 */
		function UpdateSettings(&$account)
		{
			$sql = '
UPDATE %sawm_settings SET
	msgs_per_page = %d, white_listing = %d, x_spam = %d,
	def_skin = %s, def_lang = %s, def_charset_inc = %d,
	def_charset_out = %d, def_timezone = %d, def_date_fmt = %s,
	hide_folders = %d, mailbox_limit = %d, allow_change_settings = %d,
	allow_dhtml_editor = %d, allow_direct_mode = %d, hide_contacts = %d,
	db_charset = %d, horiz_resizer = %d, vert_resizer = %d, mark = %d,
	reply = %d, contacts_per_page = %d, view_mode = %d
WHERE id_user = %d';
			
			return sprintf($sql, $this->_prefix,
									($account->MailsPerPage > 0) ? $account->MailsPerPage : 20,
									$account->WhiteListing,
									$account->XSpam,
									$this->_escapeString($account->DefaultSkin),
									$this->_escapeString($account->DefaultLanguage),
									ap_Utils::GetCodePageNumber($account->DefaultIncCharset),
									ap_Utils::GetCodePageNumber($account->DefaultOutCharset),
									$account->DefaultTimeZone,
									$this->_escapeString(CDateTime::GetDbDateFormat($account->DefaultDateFormat, $account->DefaultTimeFormat)),
									$account->HideFolders,
									$account->MailboxLimit,
									$account->AllowChangeSettings,
									$account->AllowDhtmlEditor,
									$account->AllowDirectMode,
									$account->HideContacts,
									ap_Utils::GetCodePageNumber($account->DbCharset),
									$account->HorizResizer,
									$account->VertResizer,
									$account->Mark,
									$account->Reply,
									($account->ContactsPerPage > 0) ? $account->ContactsPerPage : 20,
									$account->ViewMode,
									$account->IdUser);
		}


		/**
		 * @param int $idUser
		 * @param int $idAcct
		 * @return string
		 */
		function UpdateAccountImapQuotaOff($idUser)
		{
			$sql = '
UPDATE %sawm_accounts SET imap_quota = 0
WHERE id_user = %d AND imap_quota <> -1 AND mail_protocol = 1';
			return sprintf($sql, $this->_prefix, $idUser);
		}

		/**
		 *
		 * @param int $imapQuota
		 * @param int $idAcct
		 * @return string
		 */
		function UpdateAccountImapQuota($imapQuota, $idAcct)
		{
	$sql = '
UPDATE %sawm_accounts SET imap_quota = %d
WHERE id_acct = %d AND imap_quota <> -1 AND mail_protocol = 1';
			return sprintf($sql, $this->_prefix, $imapQuota, $idAcct);
		}
		
		/**
		 * @param int $idUser
		 * @return string
		 */
		function SelectAccountColumnsData($idUser)
		{
			$sql = 'SELECT id_column, column_value FROM %sawm_columns WHERE id_user = %d';
			return sprintf($sql, $this->_prefix, $idUser);
		}
		
		/**
		 * @param int $IdAcct
		 * @return string
		 */
		function SelectAccountAliases($IdAcct)
		{
			$sql = 'SELECT alias_name FROM %sawm_mailaliases WHERE id_acct = %d';
			return sprintf($sql, $this->_prefix, $IdAcct);
		}

		/**
		 * @param int $IdAcct
		 * @return string
		 */
		function SelectAccountForwards($IdAcct)
		{
			$sql = 'SELECT forward_to FROM %sawm_mailforwards WHERE id_acct = %d';
			return sprintf($sql, $this->_prefix, $IdAcct);
		}

		/**
		 * @param	int		$IdAcct
		 * @return	string
		 */
		function SelectMailListAccountUsers($IdAcct)
		{
			$sql = 'SELECT list_to FROM %sawm_mailinglists WHERE id_acct = %d';
			return sprintf($sql, $this->_prefix, $IdAcct);
		}

		/**
		 * @param	int		$alistName
		 * @param	int		$alistDomain
		 * @return	string
		 */
		function IsAliasValidToCreateInAccounts($alistName, $alistDomain)
		{
			$sql = 'SELECT COUNT(id_acct) AS cnt FROM %sawm_accounts WHERE email = %s AND def_acct = 1 AND deleted = 0';
			return sprintf($sql, $this->_prefix, $this->_escapeString($alistName.'@'.$alistDomain));
		}

		/**
		 * @param	int		$alistName
		 * @param	int		$alistDomain
		 * @return	string
		 */
		function IsAliasValidToCreateInAliases($alistName, $alistDomain)
		{
			$sql = 'SELECT COUNT(id) AS cnt FROM %sawm_mailaliases WHERE alias_name = %s AND alias_domain = %s';
			return sprintf($sql, $this->_prefix, $this->_escapeString($alistName), $this->_escapeString($alistDomain));
		}

		/**
	 	 * @param	int		$IdAcct
		 * @return	string
		 */
		function ClearMailAliases($IdAcct)
		{
			$sql = 'DELETE FROM %sawm_mailaliases WHERE id_acct = %d';
			return sprintf($sql, $this->_prefix, $IdAcct);
		}

		/**
	 	 * @param	int		$IdAcct
		 * @return	string
		 */
		function ClearMailForwards($IdAcct)
		{
			$sql = 'DELETE FROM %sawm_mailforwards WHERE id_acct = %d';
			return sprintf($sql, $this->_prefix, $IdAcct);
		}
		
		/**
	 	 * @param	int		$IdAcct
		 * @return	string
		 */
		function ClearMailingList($IdAcct)
		{
			$sql = 'DELETE FROM %sawm_mailinglists WHERE id_acct = %d';
			return sprintf($sql, $this->_prefix, $IdAcct);
		}

		/**
	 	 * @param	int		$idAcct
		 * @param	string	$listName
		 * @param	string	$listTo
		 * @return	string
		 */
		function InsertMailingListItem($idAcct, $listName, $listTo)
		{
			$sql = '
INSERT INTO %sawm_mailinglists (id_acct, list_name, list_to) VALUES (%d, %s, %s)';
			return sprintf($sql,  $this->_prefix, $idAcct, $this->_escapeString($listName), $this->_escapeString($listTo));
		}

		/**
	 	 * @param	int		$idAcct
		 * @param	string	$aliasName
		 * @param	string	$aliasDomain
		 * @param	string	$aliasTo
		 * @return	string
		 */
		function InsertMailAlias($idAcct, $aliasName, $aliasDomain, $aliasTo)
		{
			$sql = '
INSERT INTO %sawm_mailaliases (id_acct, alias_name, alias_domain, alias_to)
VALUES (%d, %s, %s, %s)';
			return sprintf($sql,  $this->_prefix, $idAcct, 
					$this->_escapeString($aliasName),
					$this->_escapeString($aliasDomain),
					$this->_escapeString($aliasTo));
		}

		/**
	 	 * @param	int		$idAcct
		 * @param	string	$aliasName
		 * @param	string	$aliasDomain
		 * @param	string	$aliasTo
		 * @return	string
		 */
		function InsertMailForward($idAcct, $forwardName, $forwardDomain, $forwardTo)
		{
			$sql = '
INSERT INTO %sawm_mailforwards (id_acct, forward_name, forward_domain, forward_to)
VALUES (%d, %s, %s, %s)';
			return sprintf($sql,  $this->_prefix, $idAcct,
					$this->_escapeString($forwardName),
					$this->_escapeString($forwardDomain),
					$this->_escapeString($forwardTo));
		}
		
		/**
		 * @param int $idUser
		 * @param int $id_column
		 * @param int $value_column
		 * @return string
		 */
		function UpdateColumnData($idUser, $id_column, $value_column)
		{
			$sql = '
UPDATE %sawm_columns SET column_value = %d
WHERE id_user = %d AND id_column = %d';
			return sprintf($sql, $this->_prefix, $value_column, $idUser, $id_column);			
		}
		
		/**
		 * @param int $idUser
		 * @param int $id_column
		 * @param int $value_column
		 * @return string
		 */		
		function InsertColumnData($idUser, $id_column, $value_column)
		{
			$sql = '
INSERT INTO %sawm_columns (id_user, id_column, column_value) 
VALUES (%d, %d, %d)';
			return sprintf($sql,  $this->_prefix, $idUser, $id_column, $value_column);
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function UpdateFolder($folder)
		{
			$sql = '
UPDATE %sawm_folders
SET type = %d, sync_type = %d, hide = %d, fld_order = %d
WHERE id_acct = %d AND id_folder = %d';
			
			return sprintf($sql, $this->_prefix, $folder->Type, $folder->SyncType,
								$folder->Hide, $folder->FolderOrder,
								$folder->IdAcct, $folder->IdDb);
		}
		
		/**
		 * @param int $accountId
		 * @param int $type
		 * @return string
		 */
		function GetFolderSyncType($accountId, $type)
		{
			$sql = 'SELECT sync_type FROM %sawm_folders WHERE id_acct = %d AND type = %d';
			return sprintf($sql, $this->_prefix, $accountId, $type);
		}
		
		/**
		 * @param string $email
		 * @param string $login
		 * @param bool $onlyDef[optional] = false
		 * @param int $notInAcctId[optional] = false
		 * @return string
		 */
		function SelectAccountDataByLogin($email, $login, $onlyDef = false, $notInAcctId = null)
		{
			$sql = '
SELECT id_acct, id_user, mail_inc_pass, def_acct
FROM %sawm_accounts
WHERE email = %s AND mail_inc_login = %s';
			
			$sql .= ($onlyDef) ? ' AND def_acct = 1' : '';
			$sql .= ($notInAcctId !== null && $notInAcctId > 0) ? ' AND id_acct <> '.((int) $notInAcctId) : '';
			
			return sprintf($sql, $this->_prefix,
									$this->_escapeString($email),
									$this->_escapeString($login));
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function SelectForCreateFolder($folder)
		{
			$sql = '
SELECT MAX(fld_order) AS norder
FROM %sawm_folders
WHERE id_acct = %d AND id_parent = %d';
       
			return sprintf($sql, $this->_prefix, $folder->IdAcct, $folder->IdParent);
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function CreateFolder($folder)
		{
			$sql = 'INSERT INTO %sawm_folders (id_acct, id_parent, type, name, full_path,
							sync_type, hide, fld_order)
					VALUES (%d, %d, %d, %s, %s, %d, %d, %d)';
			
			return sprintf($sql, $this->_prefix, $folder->IdAcct,
									$folder->IdParent, $folder->Type,
									$this->_escapeString($folder->Name.'#'),
									$this->_escapeString($folder->FullName.'#'),
									$folder->SyncType, $folder->Hide,
									$folder->FolderOrder);
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function CreateFolderTree($folder)
		{
			$sql = '
INSERT INTO %sawm_folders_tree (id_folder, id_parent, folder_level)	
VALUES (%d, %d, 0)';
			
			return sprintf($sql, $this->_prefix, $folder->IdDb, $folder->IdDb);
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function CreateSelectFolderTree($folder)
		{
			$sql = '
INSERT INTO %sawm_folders_tree (id_folder, id_parent, folder_level)	
VALUES (%d, %d, %d)';
			
			return sprintf($sql, $this->_prefix, $folder->IdDb, $folder->IdParent, $folder->Level);			
		}		
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function SelectForCreateFolderTree($folder)
		{
			$sql = '
SELECT id_parent, folder_level
FROM %sawm_folders_tree
WHERE id_folder = %d';
			
			return sprintf($sql, $this->_prefix, $folder->IdParent);		
		}
		
		/**
		 * @param	string	$name
		 * @return	string
		 */
		function SelectDomainsIdByName($name)
		{
			$sql = 'SELECT id_domain FROM %sawm_domains WHERE %s=%s';
			return sprintf($sql, $this->_prefix, $this->_escapeColumn('name'), $this->_escapeString($name));
		}
		
		/**
		 * @param	string	$condition[options] = null
		 * @return	string
		 */
		function GetDomainsList($condition = null)
		{
			if ($condition !== null && strlen($condition) > 0)
			{
				$condition = $this->_escapeString('%'.$condition.'%');
				$sql = 'SELECT id_domain, %s, mail_protocol, is_internal, global_addr_book, ldap_auth FROM %sawm_domains WHERE %s LIKE %s';
				return sprintf($sql, $this->_escapeColumn('name'), $this->_prefix, $this->_escapeColumn('name'), $condition);	
			}
			else
			{
				$sql = 'SELECT id_domain, %s, mail_protocol, is_internal, global_addr_book, ldap_auth FROM %sawm_domains';
				return sprintf($sql, $this->_escapeColumn('name'), $this->_prefix);
			}
		}

		/**
		 * @return	string
		 */
		function FilterDomainList()
		{
			$sql = 'SELECT id_domain, %s, url FROM %sawm_domains';
			return sprintf($sql, $this->_escapeColumn('name'), $this->_prefix);
		}

		/**
		 * @return	string
		 */
		function ClearLdapDomain()
		{
			$sql = 'UPDATE %sawm_domains SET ldap_auth = 0 WHERE ldap_auth = 1';
			return sprintf($sql, $this->_prefix);
		}

		/**
		 * @return	string
		 */
		function SetLdapDomain($domain)
		{
			$sql = 'UPDATE %sawm_domains SET ldap_auth = 1 WHERE name = %s';
			return sprintf($sql, $this->_prefix, $this->_escapeString($domain));
		}

		/**
		 * @param	CWebMailDomain		$domain
		 * @param	WebMail_Settings	$settings
		 * @return	string
		 */
		function CreateDomain($domain)
		{
			$sql = '
INSERT INTO %sawm_domains (
	%s, mail_protocol, mail_inc_host, mail_inc_port, mail_out_host, mail_out_port, mail_out_auth, 
	is_internal, global_addr_book, ldap_auth, save_mail,

	url, site_name, settings_mail_protocol, settings_mail_inc_host, settings_mail_inc_port,
	settings_mail_out_host, settings_mail_out_port, settings_mail_out_auth,
	allow_direct_mode, direct_mode_id_def, attachment_size_limit, allow_attachment_limit,
	mailbox_size_limit, allow_mailbox_limit, take_quota, allow_new_users_change_set,
	allow_auto_reg_on_login, allow_users_add_accounts, allow_users_change_account_def,
	def_user_charset, allow_users_change_charset, def_user_timezone, allow_users_change_timezone,
	msgs_per_page, skin, allow_users_change_skin, lang, allow_users_change_lang, show_text_labels,
	allow_ajax, allow_editor, allow_contacts, allow_calendar,
	hide_login_mode, domain_to_use, allow_choosing_lang, allow_advanced_login,
	allow_auto_detect_and_correct, use_captcha, use_domain_selection, view_mode
)
VALUES (
	%s, %d, %s, %d, %s, %d, %d,
	%d, %d, %d, %d,

	%s, %s, %d, %s, %d, %s, %d, %d,
	%d, %d, %d, %d,
	%d, %d, %d, %d,
	%d, %d, %d,
	%d, %d, %d, %d,
	%d, %s, %d, %s, %d, %d,
	%d, %d, %d, %d,
	%d, %s, %d, %d,
	%d, %d, %d, %d
)';
			
			return sprintf($sql, $this->_prefix, $this->_escapeColumn('name'), 
									$this->_escapeString($domain->_name),
									$domain->_mailProtocol,
									$this->_escapeString($domain->_mailIncomingHost),
									$domain->_mailIncomingPort,
									$this->_escapeString($domain->_mailSmtpHost),
									$domain->_mailSmtpPort,
									$domain->_mailSmtpAuth,
									$domain->_isInternal,
									$domain->_globalAddrBook,
									$domain->_ldapAuth,

									$domain->GetSettingsValue('save_mail'),
									$this->_escapeString($domain->GetSettingsValue('url')),
									$this->_escapeString($domain->GetSettingsValue('site_name')),
									$domain->GetSettingsValue('settings_mail_protocol'),
									$this->_escapeString($domain->GetSettingsValue('settings_mail_inc_host')),
									$domain->GetSettingsValue('settings_mail_inc_port'),
									$this->_escapeString($domain->GetSettingsValue('settings_mail_out_host')),
									$domain->GetSettingsValue('settings_mail_out_port'),
									$domain->GetSettingsValue('settings_mail_out_auth'),
									$domain->GetSettingsValue('allow_direct_mode'),
									$domain->GetSettingsValue('direct_mode_id_def'),
									$domain->GetSettingsValue('attachment_size_limit'),
									$domain->GetSettingsValue('allow_attachment_limit'),
									$domain->GetSettingsValue('mailbox_size_limit'),
									$domain->GetSettingsValue('allow_mailbox_limit'),
									$domain->GetSettingsValue('take_quota'),
									$domain->GetSettingsValue('allow_new_users_change_set'),
									$domain->GetSettingsValue('allow_auto_reg_on_login'),
									$domain->GetSettingsValue('allow_users_add_accounts'),
									$domain->GetSettingsValue('allow_users_change_account_def'),
									$domain->GetSettingsValue('def_user_charset'),
									$domain->GetSettingsValue('allow_users_change_charset'),
									$domain->GetSettingsValue('def_user_timezone'),
									$domain->GetSettingsValue('allow_users_change_timezone'),
									$domain->GetSettingsValue('msgs_per_page'),
									$this->_escapeString($domain->GetSettingsValue('skin')),
									$domain->GetSettingsValue('allow_users_change_skin'),
									$this->_escapeString($domain->GetSettingsValue('lang')),
									$domain->GetSettingsValue('allow_users_change_lang'),
									$domain->GetSettingsValue('show_text_labels'),
									$domain->GetSettingsValue('allow_ajax'),
									$domain->GetSettingsValue('allow_editor'),
									$domain->GetSettingsValue('allow_contacts'),
									$domain->GetSettingsValue('allow_calendar'),
									$domain->GetSettingsValue('hide_login_mode'),
									$this->_escapeString($domain->GetSettingsValue('domain_to_use')),
									$domain->GetSettingsValue('allow_choosing_lang'),
									$domain->GetSettingsValue('allow_advanced_login'),
									$domain->GetSettingsValue('allow_auto_detect_and_correct'),
									$domain->GetSettingsValue('use_captcha'),
									$domain->GetSettingsValue('use_domain_selection'),
									$domain->GetSettingsValue('view_mode')
								);
		}
		
		/**
		 * @param	CWebMailDomain	$domain
		 * @return	string
		 */		
		function UpdateAccountsNullDomainId($domain)
		{
			$sql = '
UPDATE %sawm_accounts
SET id_domain = %d
WHERE id_domain = 0 AND mail_protocol = %d AND mail_protocol IN (%d, %d) AND %s LIKE %s';
			
			return sprintf($sql, $this->_prefix, $domain->_id,
									$domain->_mailProtocol,
									WM_MAILPROTOCOL_POP3, WM_MAILPROTOCOL_IMAP4,
									$this->_escapeColumn('email'), 
									$this->_escapeString('%@'.$domain->_name)
									);
		}
		
		/**
		 * @param	CWebMailDomain	$domain
		 * @return	string
		 */		
		function UpdateAccountsSetNullDomainId($domain)
		{
			$sql = 'UPDATE %sawm_accounts SET id_domain = 0 WHERE id_domain = %d';
			
			return sprintf($sql, $this->_prefix, $domain->_id);
		}
			
		/**
		 * @param	CWebMailDomain	$domain
		 * @return	string
		 */
		function UpdateDomainById($domain)
		{
			$sql = '
UPDATE %sawm_domains SET mail_inc_host = %s, mail_inc_port = %d,
	mail_out_host = %s, mail_out_port = %d, mail_out_auth = %d,
	is_internal = %d, global_addr_book = %d, ldap_auth = %d, save_mail = %d,

	url = %s, site_name = %s, settings_mail_protocol = %d, settings_mail_inc_host = %s, settings_mail_inc_port = %d,
	settings_mail_out_host = %s, settings_mail_out_port = %d, settings_mail_out_auth = %d,
	allow_direct_mode = %d, direct_mode_id_def = %d,
	attachment_size_limit = %d, allow_attachment_limit = %d,
	mailbox_size_limit = %d, allow_mailbox_limit = %d, take_quota = %d,
	allow_new_users_change_set = %d, allow_auto_reg_on_login = %d,
	allow_users_add_accounts = %d, allow_users_change_account_def = %d,
	def_user_charset = %d, allow_users_change_charset = %d,
	def_user_timezone = %d, allow_users_change_timezone = %d,
	msgs_per_page = %d, skin = %s, allow_users_change_skin = %d,
	lang = %s, allow_users_change_lang = %d, show_text_labels = %d,
	allow_ajax = %d, allow_editor = %d, allow_contacts = %d, allow_calendar = %d,
	hide_login_mode = %d, domain_to_use = %s, allow_choosing_lang = %d, allow_advanced_login = %d, 
	allow_auto_detect_and_correct = %d, use_captcha = %d, use_domain_selection = %d, view_mode = %d

WHERE id_domain = %d';
			
			return sprintf($sql, $this->_prefix, $this->_escapeString($domain->_mailIncomingHost),
								$domain->_mailIncomingPort, 
								$this->_escapeString($domain->_mailSmtpHost),
								$domain->_mailSmtpPort, 
								$domain->_mailSmtpAuth,
								$domain->_isInternal,
								$domain->_globalAddrBook,
								$domain->_ldapAuth,

								$domain->GetSettingsValue('save_mail'),
								$this->_escapeString($domain->GetSettingsValue('url')),
								$this->_escapeString($domain->GetSettingsValue('site_name')),
								$domain->GetSettingsValue('settings_mail_protocol'),
								$this->_escapeString($domain->GetSettingsValue('settings_mail_inc_host')),
								$domain->GetSettingsValue('settings_mail_inc_port'),
								$this->_escapeString($domain->GetSettingsValue('settings_mail_out_host')),
								$domain->GetSettingsValue('settings_mail_out_port'),
								$domain->GetSettingsValue('settings_mail_out_auth'),
								$domain->GetSettingsValue('allow_direct_mode'),
								$domain->GetSettingsValue('direct_mode_id_def'),
								$domain->GetSettingsValue('attachment_size_limit'),
								$domain->GetSettingsValue('allow_attachment_limit'),
								$domain->GetSettingsValue('mailbox_size_limit'),
								$domain->GetSettingsValue('allow_mailbox_limit'),
								$domain->GetSettingsValue('take_quota'),
								$domain->GetSettingsValue('allow_new_users_change_set'),
								$domain->GetSettingsValue('allow_auto_reg_on_login'),
								$domain->GetSettingsValue('allow_users_add_accounts'),
								$domain->GetSettingsValue('allow_users_change_account_def'),
								$domain->GetSettingsValue('def_user_charset'),
								$domain->GetSettingsValue('allow_users_change_charset'),
								$domain->GetSettingsValue('def_user_timezone'),
								$domain->GetSettingsValue('allow_users_change_timezone'),
								$domain->GetSettingsValue('msgs_per_page'),
								$this->_escapeString($domain->GetSettingsValue('skin')),
								$domain->GetSettingsValue('allow_users_change_skin'),
								$this->_escapeString($domain->GetSettingsValue('lang')),
								$domain->GetSettingsValue('allow_users_change_lang'),
								$domain->GetSettingsValue('show_text_labels'),
								$domain->GetSettingsValue('allow_ajax'),
								$domain->GetSettingsValue('allow_editor'),
								$domain->GetSettingsValue('allow_contacts'),
								$domain->GetSettingsValue('allow_calendar'),
								$domain->GetSettingsValue('hide_login_mode'),
								$this->_escapeString($domain->GetSettingsValue('domain_to_use')),
								$domain->GetSettingsValue('allow_choosing_lang'),
								$domain->GetSettingsValue('allow_advanced_login'),
								$domain->GetSettingsValue('allow_auto_detect_and_correct'),
								$domain->GetSettingsValue('use_captcha'),
								$domain->GetSettingsValue('use_domain_selection'),
								$domain->GetSettingsValue('view_mode'),
					
								$domain->_id
								);
		}
		
		/**
		 * @param	int		$id
		 * @return	string
		 */
		function SelectDomainById($id)
		{
			return $this->SelecyDomainByX($id, 'id');
		}
		
		/**
		 * @param	string		$name
		 * @return	string
		 */
		function SelectDomainByName($name)
		{
			return $this->SelecyDomainByX($name, 'name');
		}

		function SelecyDomainByX($xvalue, $xtype)
		{
			$sql = '
SELECT id_domain, %s, mail_protocol, mail_inc_host, mail_inc_port,
	mail_out_host, mail_out_port, mail_out_auth, is_internal, global_addr_book, ldap_auth, save_mail,

	url, site_name, settings_mail_protocol, settings_mail_inc_host, settings_mail_inc_port,
	settings_mail_out_host, settings_mail_out_port, settings_mail_out_auth,
	allow_direct_mode, direct_mode_id_def, attachment_size_limit, allow_attachment_limit,
	mailbox_size_limit, allow_mailbox_limit, take_quota, allow_new_users_change_set,
	allow_auto_reg_on_login, allow_users_add_accounts, allow_users_change_account_def,
	def_user_charset, allow_users_change_charset, def_user_timezone, allow_users_change_timezone,
	msgs_per_page, skin, allow_users_change_skin, lang, allow_users_change_lang, show_text_labels,
	allow_ajax, allow_editor, allow_contacts, allow_calendar,
	hide_login_mode, domain_to_use, allow_choosing_lang, allow_advanced_login, 
	allow_auto_detect_and_correct, use_captcha, use_domain_selection, view_mode

FROM %sawm_domains WHERE ';

			$sql = sprintf($sql, $this->_escapeColumn('name'), $this->_prefix);

			switch ($xtype)
			{
				case 'id':
					$sql .=  'id_domain = '.((int) $xvalue);
					break;

				case 'name':
					$sql .= $this->_escapeColumn('name').' = '.$this->_escapeString($xvalue);
					break;
			}

			return $sql;
		}
		
		/**
		 * @param	array	$ids
		 * @return	string
		 */		
		function DeleteDomainsByIds($ids)
		{
			$sql = 'DELETE FROM %sawm_domains WHERE id_domain IN (%s)';
			return sprintf($sql, $this->_prefix, implode(', ', $ids));
		}
		
		/**
		 * @param	array	$ids
		 * @return	string
		 */		
		function UpdateAccountsSetNullDomainIdByIds($ids)
		{
			$sql = 'UPDATE %sawm_accounts SET id_domain = 0 WHERE id_domain IN (%s)';
			
			return sprintf($sql, $this->_prefix, implode(', ', $ids));
		}
		
		/**
		 * @param	array	$names
		 * @return	string
		 */		
		function DeleteDomainsByNames($names)
		{
			$sql = 'DELETE FROM %sawm_domains WHERE %s IN (%s)';
			return sprintf($sql, $this->_prefix, $this->_escapeColumn('name'), implode(', ', $this->_escapeArray($names)));
		}
		
		/**
		 * @param	array	$names
		 * @return	string
		 */		
		function UpdateAccountsSetNullDomainIdByNames($names)
		{
			$sql = 'UPDATE %sawm_accounts SET id_domain = 0 WHERE %s IN (%s)';
			
			return sprintf($sql, $this->_prefix, $this->_escapeColumn('name'), implode(', ', $names));
		}

		/**
		 * @return string
		 */
		function CountAllMailboxSizes()
		{
			$sql = '
SELECT id_user, SUM(mailbox_size) AS mailboxes_size
FROM %sawm_accounts
GROUP BY id_user
ORDER BY id_user';
			
			return sprintf($sql, $this->_prefix);
		}
		
			
		/**
		 * @param int $userId
		 * @return string
		 */
		function GetCalendarIdsByUserId($userId)
		{
			$sql = 'SELECT calendar_id FROM %sacal_calendars WHERE user_id = %d';
			return sprintf($sql, $this->_prefix, $userId);
		}

		/**
		 * @return string
		 */
		function UpdateDeleteAllAUsers()
		{
			return 'UPDATE '.$this->_prefix.'a_users SET deleted = 1';
		}
	}

	class MySQL_CommandCreator extends main_CommandCreator 
	{
		/**
		 * @param	int	$type
		 * @return	MySQL_CommandCreator
		 */
		function MySQL_CommandCreator($type, $columnEscape = array('', ''), $prefix = '')
		{
			main_CommandCreator::main_CommandCreator($type, $columnEscape, $prefix);
		}
		
		function GetDateFormat($fieldName)
		{
			return 'DATE_FORMAT('.$fieldName.', "%Y-%m-%d %T")';
		}

		function UpdateDateFormat($fieldValue)
		{
			return $this->_escapeString($fieldValue);
		}

		/**
		 * @return	string
		 */
		function ScriptUpdateDomainCustomData($settings)
		{
			$sql = '
UPDATE %sawm_domains SET
	url = %s, site_name = %s, settings_mail_protocol = %d, settings_mail_inc_host = %s, settings_mail_inc_port = %d,
	settings_mail_out_host = %s, settings_mail_out_port = %d, settings_mail_out_auth = %d,
	allow_direct_mode = %d, direct_mode_id_def = %d,
	attachment_size_limit = %d, allow_attachment_limit = %d,
	mailbox_size_limit = %d, allow_mailbox_limit = %d, take_quota = %d,
	allow_new_users_change_set = %d, allow_auto_reg_on_login = %d,
	allow_users_add_accounts = %d, allow_users_change_account_def = %d,
	def_user_charset = %d, allow_users_change_charset = %d,
	def_user_timezone = %d, allow_users_change_timezone = %d,
	msgs_per_page = %d, skin = %s, allow_users_change_skin = %d,
	lang = %s, allow_users_change_lang = %d, show_text_labels = %d,
	allow_ajax = %d, allow_editor = %d, allow_contacts = %d, allow_calendar = %d,
	hide_login_mode = %d, domain_to_use = %s, allow_choosing_lang = %d,
	allow_advanced_login = %d, allow_auto_detect_and_correct = %d, use_captcha = %d,
	use_domain_selection = %d, view_mode = %d
	WHERE use_domain_selection IS NULL';

			return sprintf($sql, $this->_prefix,
								$this->_escapeString(''),
								$this->_escapeString($settings->WindowTitle),
								$settings->IncomingMailProtocol,
								$this->_escapeString($settings->IncomingMailServer),
								$settings->IncomingMailPort,
								$this->_escapeString($settings->OutgoingMailServer),
								$settings->OutgoingMailPort,
								$settings->ReqSmtpAuth,
								$settings->AllowDirectMode,
								$settings->DirectModeIsDefault,
								$settings->AttachmentSizeLimit,
								$settings->EnableAttachmentSizeLimit,
								$settings->MailboxSizeLimit,
								$settings->EnableMailboxSizeLimit,
								$settings->TakeImapQuota,
								$settings->AllowUsersChangeEmailSettings,
								$settings->AllowNewUsersRegister,
								$settings->AllowUsersAddNewAccounts,
								$settings->AllowUsersChangeAccountsDef,
								$settings->DefaultUserCharset,
								$settings->AllowUsersChangeCharset,
								$settings->DefaultTimeZone,
								$settings->AllowUsersChangeTimeZone,
								$settings->MailsPerPage,
								$this->_escapeString($settings->DefaultSkin),
								$settings->AllowUsersChangeSkin,
								$this->_escapeString($settings->DefaultLanguage),
								$settings->AllowUsersChangeLanguage,
								$settings->ShowTextLabels,
								$settings->AllowAjax,
								$settings->AllowDhtmlEditor,
								$settings->AllowContacts,
								$settings->AllowCalendar,
								$settings->HideLoginMode,
								$this->_escapeString($settings->DefaultDomainOptional),
								$settings->AllowLanguageOnLogin,
								$settings->AllowAdvancedLogin,
								$settings->AutomaticCorrectLoginSettings,
								$settings->UseCaptcha,
								$settings->UseMultipleDomainsSelection,
								$settings->ViewMode
				);
		}

		function CreateFunctions($functionName = null)
		{
			switch ($functionName) {
				case 'DP1':
					return '
CREATE FUNCTION DP1(password VARCHAR(255)) RETURNS VARCHAR(128)
DETERMINISTIC
READS SQL DATA
BEGIN
	DECLARE result VARCHAR(128) DEFAULT \'\';
	DECLARE passwordLen INT;
	DECLARE decodeByte CHAR(3);
	DECLARE plainBytes VARCHAR(128);
	DECLARE startIndex INT DEFAULT 3;
	DECLARE currentByte INT DEFAULT 1;
	DECLARE hexByte CHAR(3);

	SET passwordLen = LENGTH(password);
	IF passwordLen > 0 AND passwordLen % 2 = 0 THEN
		SET decodeByte = CONV((SUBSTRING(password, 1, 2)), 16, 10);
		SET plainBytes = UNHEX(SUBSTRING(password, 1, 2));

		REPEAT
			SET hexByte = CONV((SUBSTRING(password, startIndex, 2)), 16, 10);
			SET plainBytes = CONCAT(plainBytes, UNHEX(HEX(hexByte ^ decodeByte)));

			SET startIndex = startIndex + 2;
			SET currentByte = currentByte + 1;

		UNTIL startIndex > passwordLen
		END REPEAT;

		SET result = plainBytes;
	END IF;

	RETURN result;
END';

			}

			return false;
		}
		
		/**
		 * @param string $accountId
		 * @return string
		 */
		function GetFolders($accountId)
		{
			$sql = '
SELECT p.id_folder, p.id_parent, p.type, p.name, p.full_path, p.sync_type, p.hide, p.fld_order,
	COUNT(messages.id) AS message_count, COUNT(messages_unread.seen) AS unread_message_count,
	SUM(messages.size) AS folder_size, MAX(folder_level) AS level
FROM (%1$sawm_folders as n, %1$sawm_folders_tree as t, %1$sawm_folders as p)
LEFT OUTER JOIN %1$sawm_messages AS messages ON p.id_folder = messages.id_folder_db
LEFT OUTER JOIN %1$sawm_messages AS messages_unread ON
	p.id_folder = messages_unread.id_folder_db AND 
	messages.id = messages_unread.id AND messages_unread.seen = 0
WHERE n.id_parent = -1
	AND n.id_folder = t.id_parent
	AND t.id_folder = p.id_folder
	AND p.id_acct = %2$d
GROUP BY p.id_folder
ORDER BY p.fld_order';			
			
			return sprintf($sql, $this->_prefix, $accountId);
		}

		/**
		 * @param	int		$domainId
		 * @param	int		$page
		 * @param	int		$pageCnt
		 * @param	string	$orderBy[optional] = null
		 * @param	bool	$asc[optional] = null
		 * @param	string	$condition[optional] = null
		 * @return	string
		 */
		function GetAccountList($domainId, $page, $pageCnt, $orderBy = null, $asc = null, $condition = null)
		{
            $add = '';
			if ($condition !== null && strlen($condition) > 0)
			{
				$add = 'AND email LIKE '.$this->_escapeString('%'.$condition.'%');
			}
			
			if ($orderBy === null)
			{
				$orderBy = 'email';
			}
			
			if ($asc === null)
			{
				$asc = true;
			}
		
			$sql = '
SELECT acct.id_user, id_acct, email, %s AS nlast_login, logins_count, mailbox_size, mailbox_limit, mailing_list, mail_protocol, deleted
FROM %sawm_accounts AS acct
INNER JOIN %sawm_settings AS sett ON acct.id_user = sett.id_user WHERE id_domain = %d %s 
ORDER BY %s %s LIMIT %d, %d';
			
			$start = ($page > 0) ? ($page - 1) * $pageCnt : 0;
			return sprintf($sql, $this->GetDateFormat('last_login'), $this->_prefix, $this->_prefix,
							$domainId, $add, 
							$orderBy, ($asc) ? 'ASC' : 'DESC',
							$start, $pageCnt);
		}
		
		/**
		 * @param int $id
		 * @return string
		 */
		function DeleteFolderTreeById($id)
		{
			$sql = '
DELETE %1$sawm_folders_tree 
FROM %1$sawm_folders, %1$sawm_folders_tree
WHERE %1$sawm_folders.id_folder = %1$sawm_folders_tree.id_folder 
AND %1$sawm_folders.id_acct = %2$d';

			return sprintf($sql, $this->_prefix, $id);
		}
		
		/**
		 * @param int $id
		 * @return string
		 */
		function DeleteAddrGroupsContactsById($id)
		{
			$sql = '
DELETE %1$sawm_addr_groups_contacts
FROM %1$sawm_addr_groups_contacts, %1$sawm_addr_groups
WHERE %1$sawm_addr_groups_contacts.id_group = %1$sawm_addr_groups.id_group 
AND %1$sawm_addr_groups.id_user = %2$d';

			return sprintf($sql, $this->_prefix, $id);
		}
		
		/**
		 * @param int $id
		 * @return string
		 */
		function DeleteCalendarEvents($id)
		{
			$sql = '
DELETE %1$sacal_events
FROM %1$sacal_events, %1$sacal_calendars
WHERE %1$sacal_events.calendar_id = %1$sacal_calendars.calendar_id
AND %1$sacal_calendars.user_id = %2$d';

			return sprintf($sql, $this->_prefix, $id);
		}
		
		/**
		 * @param array $cal_ids
		 * @return string
		 */
		function DeleteCalendarExclusions($cal_ids)
		{
			$is_string = implode(',', $cal_ids);
			$sql = '
DELETE %1$sacal_exclusions
FROM %1$sacal_exclusions, %1$sacal_events
WHERE %1$sacal_exclusions.id_event = %1$sacal_events.event_id 
AND %1$sacal_events.calendar_id IN (%2$d)';

			return sprintf($sql, $this->_prefix, $is_string);
		}
		
		/**
		 * @param array $cal_ids
		 * @return string
		 */
		function DeleteCalendarEventrepeats($cal_ids)
		{
			$is_string = implode(',', $cal_ids);
			$sql = '
DELETE %1$sacal_eventrepeats
FROM %1$sacal_eventrepeats, %1$sacal_events
WHERE %1$sacal_eventrepeats.id_event = %1$sacal_events.event_id
AND %1$sacal_events.calendar_id IN (%2$d)';

			return sprintf($sql, $this->_prefix, $is_string);
		}
		
		/**
		 * @return	string
		 */
		function AllTableNames()
		{
			return 'SHOW TABLES';
		}
		
		/**
		 * @return	string
		 */
		function GetIndexsOfTable($pref, $tableName)
		{
			return 'SHOW INDEX FROM `'.$pref.$tableName.'`';	
		}	
		
		/**
		 * @param	string	$pref
		 * @param	string	$tableName
		 * @return	string
		 */
		function GetTablesColumns($pref, $tableName)
		{
			return 'SHOW COLUMNS FROM `'.$pref.$tableName.'`';
		}
		
		/**
		 * @param	string	$pref
		 * @param	string	$tableName
		 * @param	string	$fieldName
		 * @return	string
		 */
		function CreateIndex($pref, $tableName, $fieldName)
		{
			$temp = (strlen($pref) > 0) ? str_replace('-', '_', $pref.'_') : '';
			return 'CREATE INDEX '.strtoupper($temp.$tableName.'_'.$fieldName).'_INDEX 
						ON `'.$pref.$tableName.'`('.$fieldName.')';
		}
		
		function DropTable($original, $pref)
		{
			$pref = ($pref) ? $pref : '';
			return 'DROP TABLE IF EXISTS `'.$pref.$original.'`';
		}
		
		/**
		 * @param	string	$original
		 * @param	string	$pref
		 * @return	string
		 */
		function CreateTable($original, $pref)
		{
			$pref = ($pref) ? $pref : '';
			switch ($original)
			{
				case DBTABLE_A_USERS:
					return '
CREATE TABLE `'.$pref.'a_users` (
	`id_user` int(11) NOT NULL auto_increment,
	`deleted` tinyint(1) NOT NULL default 0,
	PRIMARY KEY  (`id_user`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';
					
				case DBTABLE_AWM_ACCOUNTS:
					return '
CREATE TABLE `'.$pref.'awm_accounts` (
	`id_acct` int(11) NOT NULL auto_increment,
	`id_user` int(11) NOT NULL default 0,
	`def_acct` tinyint(1) NOT NULL default 0,
	`deleted` tinyint(1) NOT NULL default 0,
	`email` varchar(255) NOT NULL default \'\',
	`mail_protocol` tinyint(1) NOT NULL default 0,
	`mail_inc_host` varchar(255) default NULL,
	`mail_inc_login` varchar(255) default NULL,
	`mail_inc_pass` varchar(255) default NULL,
	`mail_inc_port` int(11) NOT NULL default 110,
	`mail_out_host` varchar(255) default NULL,
	`mail_out_login` varchar(255) default NULL,
	`mail_out_pass` varchar(255) default NULL,
	`mail_out_port` int(11) NOT NULL default 25,
	`mail_out_auth` tinyint(1) NOT NULL default 1,
	`friendly_nm` varchar(200) default NULL,
	`use_friendly_nm` tinyint(1) NOT NULL default 1,
	`def_order` tinyint(4) NOT NULL default 0,
	`getmail_at_login` tinyint(1) NOT NULL default 0,
	`mail_mode` tinyint(4) NOT NULL default 1,
	`mails_on_server_days` smallint(6) NOT NULL,
	`signature` text,
	`signature_type` tinyint(4) NOT NULL default 1,
	`signature_opt` tinyint(4) NOT NULL default 0,
	`delimiter` char(1) NOT NULL default \'/\',
	`mailbox_size` bigint(20) NOT NULL default 0,
	`id_domain` int(11) NOT NULL default 0,
	`mailing_list` tinyint(1) NOT NULL default 0,
	`imap_quota` tinyint(1) NOT NULL default -1,
	`personal_namespace` varchar(50) NOT NULL default \'\',
	PRIMARY KEY  (`id_acct`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_ADDR_BOOK:
					return '
CREATE TABLE `'.$pref.'awm_addr_book` (
	`id_addr` bigint(20) NOT NULL auto_increment,
	`id_user` int(11) NOT NULL default 0,
	`str_id` varchar(100) default NULL,
	`fnbl_pim_id` bigint(20) NULL,
	`deleted` tinyint(1) NOT NULL default 0,
	`date_created` datetime NULL,
	`date_modified` datetime NULL,
	`h_email` varchar(255) default NULL,
	`fullname` varchar(255) default NULL,
	`notes` varchar(255) default NULL,
	`use_friendly_nm` tinyint(1) NOT NULL default 1,
	`h_street` varchar(255) default NULL,
	`h_city` varchar(200) default NULL,
	`h_state` varchar(200) default NULL,
	`h_zip` varchar(10) default NULL,
	`h_country` varchar(200) default NULL,
	`h_phone` varchar(50) default NULL,
	`h_fax` varchar(50) default NULL,
	`h_mobile` varchar(50) default NULL,
	`h_web` varchar(255) default NULL,
	`b_email` varchar(255) default NULL,
	`b_company` varchar(200) default NULL,
	`b_street` varchar(255) default NULL,
	`b_city` varchar(200) default NULL,
	`b_state` varchar(200) default NULL,
	`b_zip` varchar(10) default NULL,
	`b_country` varchar(200) default NULL,
	`b_job_title` varchar(100) default NULL,
	`b_department` varchar(200) default NULL,
	`b_office` varchar(200) default NULL,
	`b_phone` varchar(50) default NULL,
	`b_fax` varchar(50) default NULL,
	`b_web` varchar(255) default NULL,
	`other_email` varchar(255) default NULL,
	`primary_email` tinyint(4) default NULL,
	`id_addr_prev` bigint(20) NOT NULL default 0,
	`tmp` tinyint(1) NOT NULL default 0,
	`use_frequency` int(11) NOT NULL default 0,
	`auto_create` tinyint(1) NOT NULL default 0,
	`birthday_day` tinyint(4) NOT NULL default 0,
	`birthday_month` tinyint(4) NOT NULL default 0,
	`birthday_year` smallint(6) NOT NULL default 0,
	PRIMARY KEY  (`id_addr`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_ADDR_GROUPS:
					return '
CREATE TABLE `'.$pref.'awm_addr_groups` (
	`id_group` int(11) NOT NULL auto_increment,
	`id_user` int(11) NOT NULL default 0,
	`group_nm` varchar(255) default NULL,
	`use_frequency` int(11) NOT NULL default 0,
	`email` varchar(255) default NULL,
	`company` varchar(200) default NULL,
	`street` varchar(255) default NULL,
	`city` varchar(200) default NULL,
	`state` varchar(200) default NULL,
	`zip` varchar(10) default NULL,
	`country` varchar(200) default NULL,
	`phone` varchar(50) default NULL,
	`fax` varchar(50) default NULL,
	`web` varchar(255) default NULL,
	`organization` tinyint(1) NOT NULL default 0,
	`group_str_id` varchar(100) default NULL,
	PRIMARY KEY  (`id_group`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_ADDR_GROUPS_CONTACTS:
					return '
CREATE TABLE `'.$pref.'awm_addr_groups_contacts` (
	`id_addr` bigint(20) NOT NULL default 0,
	`id_group` int(11) NOT NULL default 0
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_FILTERS:
					return '
CREATE TABLE `'.$pref.'awm_filters` (
	`id_filter` int(11) NOT NULL auto_increment,
	`id_acct` int(11) NOT NULL default 0,
	`field` tinyint(4) NOT NULL default 0,
	`condition` tinyint(4) NOT NULL default 0,
	`filter` varchar(255) default NULL,
	`action` tinyint(4) NOT NULL default 0,
	`id_folder` bigint(20) NOT NULL default 0,
	`applied` tinyint(1) NOT NULL default 1,
	PRIMARY KEY  (`id_filter`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_FOLDERS:
					return '
CREATE TABLE `'.$pref.'awm_folders` (
	`id_folder` bigint(20) NOT NULL auto_increment,
	`id_acct` int(11) NOT NULL default 0,
	`id_parent` bigint(20) NOT NULL default 0,
	`type` smallint(6) NOT NULL default 0,
	`name` varchar(100) default NULL,
	`full_path` varchar(255) default NULL,
	`sync_type` tinyint(4) NOT NULL default 0,
	`hide` tinyint(1) NOT NULL default 0,
	`fld_order` smallint(6) NOT NULL default 1,
	PRIMARY KEY  (`id_folder`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_FOLDERS_TREE:
					return '
CREATE TABLE `'.$pref.'awm_folders_tree` (
	`id` int(11) NOT NULL auto_increment,
	`id_folder` bigint(20) NOT NULL default 0,
	`id_parent` bigint(20) NOT NULL default 0,
	`folder_level` tinyint(4) NOT NULL default 0,
	PRIMARY KEY (`id`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_MESSAGES:
					return '
CREATE TABLE `'.$pref.'awm_messages` (
	`id` bigint(20) NOT NULL auto_increment,
	`id_msg` int(11) NOT NULL default 0,
	`id_acct` int(11) NOT NULL default 0,
	`id_folder_srv` bigint(20) NOT NULL,
	`id_folder_db` bigint(20) NOT NULL,
	`str_uid` varchar(255) default NULL,
	`int_uid` bigint(20) NOT NULL default 0,
	`from_msg` varchar(255) default NULL,
	`to_msg` varchar(255) default NULL,
	`cc_msg` varchar(255) default NULL,
	`bcc_msg` varchar(255) default NULL,
	`subject` varchar(255) default NULL,
	`msg_date` datetime default NULL,
	`attachments` tinyint(1) NOT NULL default 0,
	`size` bigint(20) NOT NULL,
	`seen` tinyint(1) NOT NULL default 1,
	`flagged` tinyint(1) NOT NULL default 0,
	`priority` tinyint(4) NOT NULL default 3,
	`downloaded` tinyint(1) NOT NULL default 1,
	`x_spam` tinyint(1) NOT NULL default 0,
	`rtl` tinyint(1) NOT NULL default 0,
	`deleted` tinyint(1) NOT NULL default 0,
	`is_full` tinyint(1) default 1,
	`replied` tinyint(1) default NULL,
	`forwarded` tinyint(1) default NULL,
	`flags` int(11) default NULL,
	`body_text` longtext,
	`grayed` tinyint(1) default 0 NOT NULL,
	`charset` int(11) NOT NULL default -1,
	`sensitivity` tinyint(4) default 0,
	PRIMARY KEY (`id`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';
					
				case DBTABLE_AWM_MESSAGES_INDEX:
					return '
CREATE INDEX '.str_replace('-', '_', $pref.'_').'DBTABLE_AWM_MESSAGES_INDEX ON `'.$pref.'awm_messages`(id_acct, id_msg)';
					
				case DBTABLE_AWM_MESSAGES_BODY:
					return '
CREATE TABLE `'.$pref.'awm_messages_body` (
	`id` bigint(20) NOT NULL auto_increment PRIMARY KEY,
	`id_msg` bigint(20) NOT NULL default 0,
	`id_acct` int(11) NOT NULL default 0,
	`msg` longblob
 ) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';
					
				case DBTABLE_AWM_MESSAGES_BODY_INDEX:
					return '
CREATE UNIQUE INDEX '.str_replace('-', '_', $pref.'_').'DBTABLE_AWM_MESSAGES_INDEX ON `'.$pref.'awm_messages_body`(id_acct, id_msg)';
					
				case DBTABLE_AWM_READS:
					return '
CREATE TABLE `'.$pref.'awm_reads` (
	`id_read` bigint(20) NOT NULL auto_increment,
	`id_acct` int(11) NOT NULL default 0,
	`str_uid` varchar(255) default NULL,
	`tmp` tinyint(1) NOT NULL default 0,
	PRIMARY KEY (`id_read`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_SETTINGS:
					return '
CREATE TABLE `'.$pref.'awm_settings` (
	`id_setting` int(11) NOT NULL auto_increment,
	`id_user` int(11) NOT NULL default 0,
	`msgs_per_page` smallint(6) NOT NULL default 20,
	`white_listing` tinyint(1) NOT NULL default 0,
	`x_spam` tinyint(1) NOT NULL default 0,
	`last_login` datetime default NULL,
	`logins_count` int(11) NOT NULL default 0,
	`def_skin` varchar(255) NOT NULL default \''.WM_DEFAULT_SKIN.'\',
	`def_lang` varchar(50) default NULL,
	`def_charset_inc` int(11) NOT NULL default 1250,
	`def_charset_out` int(11) NOT NULL default 1250,
	`def_timezone` smallint(6) NOT NULL default 0,
	`def_date_fmt` varchar(20) NOT NULL default \'MM/DD/YY\',
	`hide_folders` tinyint(1) NOT NULL default 0,
	`mailbox_limit` bigint(20) NOT NULL default 1000000000,
	`allow_change_settings` tinyint(1) NOT NULL default 1,
	`allow_dhtml_editor` tinyint(1) NOT NULL default 1,
	`allow_direct_mode` tinyint(1) NOT NULL default 1,
	`hide_contacts` tinyint(1) NOT NULL default 0,
	`db_charset` int(11) NOT NULL default 65001,
	`horiz_resizer` smallint(6) NOT NULL default 150,
	`vert_resizer` smallint(6) NOT NULL default 115,
	`mark` tinyint(4) NOT NULL default 0,
	`reply` tinyint(4) NOT NULL default 0,
	`contacts_per_page` smallint(6) NOT NULL default 20,
	`view_mode` tinyint(4) NOT NULL default 1,
	`question_1` varchar(255) default NULL,
	`answer_1` varchar(255) default NULL,
	`question_2` varchar(255) default NULL,
	`answer_2` varchar(255) default NULL,
	`auto_checkmail_interval` int(11) default 0,
	`enable_fnbl_sync` int(11) default 0,
	PRIMARY KEY (`id_setting`),
	UNIQUE KEY `id_user` (`id_user`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_TEMP:
					return '
CREATE TABLE `'.$pref.'awm_temp` (
	`id_temp` bigint(20) NOT NULL auto_increment,
	`id_acct` int(11) NOT NULL default 0,
	`data_val` text,
	PRIMARY KEY (`id_temp`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_SENDERS:
					return '
CREATE TABLE `'.$pref.'awm_senders` (
	`id` int(11) NOT NULL auto_increment,
	`id_user` int(11) NOT NULL default 0,
	`email` varchar(255) NOT NULL,
	`safety`  tinyint(4) NOT NULL default 0,
	PRIMARY KEY (`id`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_COLUMNS:
					return '
CREATE TABLE `'.$pref.'awm_columns` (
	`id` int(11) NOT NULL auto_increment,
	`id_column` int(11) NOT NULL default 0,
	`id_user` int(11) NOT NULL default 0,
	`column_value` int(11) NOT NULL default 0,
	PRIMARY KEY (`id`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_CAL_USERS_DATA:
					return '
CREATE TABLE `'.$pref.'acal_users_data` (
	`settings_id` int(11) NOT NULL auto_increment,
	`user_id` int(11) NOT NULL default 0,
	`timeformat` tinyint(1) NOT NULL default 1,
	`dateformat` tinyint(1) NOT NULL default 1,
	`showweekends` tinyint(1) NOT NULL default 0,
	`workdaystarts` tinyint(2) NOT NULL default 0,
	`workdayends` tinyint(2) NOT NULL default 1,
	`showworkday` tinyint(1) NOT NULL default 0,
	`weekstartson` tinyint(1) NOT NULL default 0,
	`defaulttab` tinyint(1) NOT NULL default 1,
	`country` varchar(2) NOT NULL,
	`timezone` smallint(3) NULL,
	`alltimezones` tinyint(1) NOT NULL default 0,
	`reminders_web_url` varchar(255) NULL,
	`autoaddinvitation` tinyint(1) NOT NULL default 0,
	PRIMARY KEY (`settings_id`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_CAL_CALENDARS:
					return '
CREATE TABLE `'.$pref.'acal_calendars` (
	`calendar_id` int(11) NOT NULL auto_increment,
	`calendar_str_id` varchar(255) NULL,
	`user_id` int(11) NOT NULL default 0,
	`calendar_name` varchar(100) NOT NULL default \'\',
	`calendar_description` text,
	`calendar_color` int(11) NOT NULL default 0,
	`calendar_active` tinyint(1) NOT NULL default 1,
	PRIMARY KEY (`calendar_id`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_CAL_EVENTS:
					return '
CREATE TABLE `'.$pref.'acal_events` (
	`event_id` int(11) NOT NULL auto_increment,
	`event_str_id` varchar(255) NULL,
	`fnbl_pim_id` bigint(20) NULL,
	`calendar_id` int(11) NOT NULL default 0,
	`event_timefrom` datetime default NULL,
	`event_timetill` datetime default NULL, 
	`event_allday` tinyint(1) NOT NULL default 0,
	`event_name` varchar(100) NOT NULL default \'\',
	`event_text` text,
	`event_priority` tinyint(4) NULL,
	`event_repeats` tinyint(1) NOT NULL default 0,
	`event_last_modified` datetime default NULL,
	`event_owner_email` varchar(255) NOT NULL default \'\',
	`event_appointment_access` tinyint(4) NOT NULL default 0,
	`event_deleted` int(11) NOT NULL default 0,
	PRIMARY KEY (`event_id`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';


				case DBTABLE_AWM_DOMAINS:
					return '
CREATE TABLE `'.$pref.'awm_domains` (
	`id_domain` int(11) NOT NULL auto_increment,
	`name` varchar(255) NOT NULL,
	`mail_protocol` tinyint(1) NOT NULL default 0,
	`mail_inc_host` varchar(255) NULL,
	`mail_inc_port` int(11) NOT NULL default 110,
	`mail_out_host` varchar(255) NULL,
	`mail_out_port` int(11) NOT NULL default 25,
	`mail_out_auth` tinyint(1) NOT NULL default 0,
	`is_internal` tinyint(1) NOT NULL default 0,

	`url` varchar(255) NULL,
	`site_name` varchar(255) NULL,
	`settings_mail_protocol` tinyint(1) NULL,
	`settings_mail_inc_host` varchar(255) NULL,
	`settings_mail_inc_port` int(11) NULL,
	`settings_mail_out_host` varchar(255) NULL,
	`settings_mail_out_port` int(11) NULL,
	`settings_mail_out_auth` tinyint(1) NULL,
	`allow_direct_mode` tinyint(1) NULL,
	`direct_mode_id_def` tinyint(1) NULL,
	`attachment_size_limit` bigint(20) NULL,
	`allow_attachment_limit` tinyint(1) NULL,
	`mailbox_size_limit` bigint(20) NULL,
	`allow_mailbox_limit` tinyint(1) NULL,
	`take_quota` tinyint(1) NULL,
	`allow_new_users_change_set` tinyint(1) NULL,
	`allow_auto_reg_on_login` tinyint(1) NULL,
	`allow_users_add_accounts` tinyint(1) NULL,
	`allow_users_change_account_def` tinyint(1) NULL,
	`def_user_charset` int(11) NULL,
	`allow_users_change_charset` tinyint(1) NULL,
	`def_user_timezone` int(11) NULL,
	`allow_users_change_timezone` tinyint(1) NULL,

	`msgs_per_page` smallint(6) NULL,
	`skin` varchar(50) NULL,
	`allow_users_change_skin` tinyint(1) NULL,
	`lang` varchar(50) NULL,
	`allow_users_change_lang` tinyint(1) NULL,
	`show_text_labels` tinyint(1) NULL,
	`allow_ajax` tinyint(1) NULL,
	`allow_editor` tinyint(1) NULL,
	`allow_contacts` tinyint(1) NULL,
	`allow_calendar` tinyint(1) NULL,

	`hide_login_mode` tinyint(1) NULL,
	`domain_to_use` varchar(255) NULL,
	`allow_choosing_lang` tinyint(1) NULL,
	`allow_advanced_login` tinyint(1) NULL,
	`allow_auto_detect_and_correct` tinyint(1) NULL,
	`use_captcha` tinyint(1) NULL,
	`use_domain_selection` tinyint(1) NULL,

	`global_addr_book` tinyint(1) NOT NULL default 0,
	`view_mode` tinyint(4) NOT NULL default 1,
	`ldap_auth` tinyint(1) NOT NULL default 0,
	`save_mail` tinyint(4) NOT NULL default 0,
	PRIMARY KEY (`id_domain`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_CAL_SHARING:
					return '
CREATE TABLE `'.$pref.'acal_sharing` (
	`id_share` int(11) NOT NULL auto_increment,
	`id_user` int(11) NOT NULL,
	`id_calendar` int(11) NOT NULL,
	`id_to_user` int(11) NOT NULL,
	`str_to_email` varchar(255) NOT NULL default \'\',
	`int_access_level` tinyint(4) NOT NULL default 2,
	`calendar_active` tinyint(1) NOT NULL default 1,
	PRIMARY KEY (`id_share`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_CAL_PUBLICATIONS:
					return '
CREATE TABLE `'.$pref.'acal_publications` (
	`id_publication` int(11) NOT NULL auto_increment,
	`id_user` int(11) NOT NULL,
	`id_calendar` int(11) NOT NULL,
	`str_md5` varchar(32) NOT NULL,
	`int_access_level` tinyint(4) NOT NULL default 1,
	`access_type` tinyint(4) NOT NULL default 0,
	PRIMARY KEY (`id_publication`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';
					
				case DBTABLE_CAL_EXCLUSIONS:
					return '
CREATE TABLE `'.$pref.'acal_exclusions` (
	`id_exclusion` int(11) NOT NULL auto_increment,
	`id_event` int(11) NOT NULL,
	`id_calendar` int(11) NOT NULL,
	`id_repeat` int(11) NOT NULL,
	`id_recurrence_date` datetime default NULL,
	`event_timefrom` datetime NOT NULL,
	`event_timetill` datetime NOT NULL,
	`event_name` varchar(100) NOT NULL,
	`event_text` text,
	`event_allday` tinyint(1) NOT NULL default 0,
	`event_last_modified` datetime default NULL,
	`is_deleted` tinyint(1) NOT NULL default 0,
	PRIMARY KEY (`id_exclusion`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';
					
				case DBTABLE_CAL_EVENTREPEATS:
					return '
CREATE TABLE `'.$pref.'acal_eventrepeats` (
	`id_repeat` int(11) NOT NULL auto_increment,
	`id_event` int(11) NOT NULL,
	`repeat_period` tinyint(1) NOT NULL default 0,
	`repeat_order` tinyint(1) NOT NULL default 1,
	`repeat_num` int(11) NOT NULL default 0,
	`repeat_until` datetime default NULL,
	`sun` tinyint(1) NOT NULL default 0,
	`mon` tinyint(1) NOT NULL default 0,
	`tue` tinyint(1) NOT NULL default 0,
	`wed` tinyint(1) NOT NULL default 0,
	`thu` tinyint(1) NOT NULL default 0,
	`fri` tinyint(1) NOT NULL default 0,
	`sat` tinyint(1) NOT NULL default 0,
	`week_number` tinyint(1) default NULL,
	`repeat_end` tinyint(1) NOT NULL default 0,
	`excluded` tinyint(1) NOT NULL default 0,
	PRIMARY KEY (`id_repeat`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_CAL_REMINDERS:
					return '
CREATE TABLE `'.$pref.'acal_reminders` (
	`id_reminder` int(11) NOT NULL auto_increment,
	`id_event` int(11) NOT NULL,
	`id_user` int(11) NULL,
	`notice_type` tinyint(4) NOT NULL default 0,
	`remind_offset` int(11) NOT NULL default 0,
	`sent` int(11) NOT NULL default 0,
	PRIMARY KEY  (`id_reminder`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_CAL_CRON_RUNS:
					return '
CREATE TABLE `'.$pref.'acal_cron_runs` (
	`id_run` int(11) NOT NULL auto_increment,
	`run_date` datetime NOT NULL,
	`latest_date` datetime NOT NULL,
	PRIMARY KEY  (`id_run`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_CAL_APPOINTMENTS:
					return '
CREATE TABLE `'.$pref.'acal_appointments` (
	`id_appointment` int(11) NOT NULL auto_increment,
	`id_event` int(11) NOT NULL,
	`id_user` int(11) NOT NULL default 0,
	`email` varchar(255) NOT NULL,
	`access_type` tinyint(4) NOT NULL default 0,
	`status` tinyint(4) NOT NULL default 0,
	`hash` varchar(32) NOT NULL,
	PRIMARY KEY  (`id_appointment`)
 ) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_A_SESSIONS:
					return '
CREATE TABLE `'.$pref.'a_sessions` (
	`sess_hash` varchar(50) NOT NULL,
	`sess_time` int(11) NOT NULL,
	`sess_data` text,
	PRIMARY KEY  (`sess_hash`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_TEMPFILES:
					return '
CREATE TABLE `'.$pref.'awm_tempfiles` (
	`id` bigint(20) NOT NULL auto_increment,
	`id_acct` int(11) NOT NULL,
	`hash` varchar(100) NOT NULL,
	`file_name` varchar(100) NOT NULL,
	`file_time` int(11) NOT NULL,
	`file_size` int(11) NOT NULL default 0,
	`file_body` longblob,
	PRIMARY KEY  (`id`)
 ) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_A_TEST:
					return '
CREATE TABLE `'.$pref.'a_test` (
	`id` bigint(20) NOT NULL auto_increment,
	PRIMARY KEY  (`id`)
 ) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';
						
				case DBTABLE_AWM_LOGS:
					return '
CREATE TABLE `'.$pref.'awm_logs` (
	`id` bigint(20) NOT NULL auto_increment,
	`file_key` varchar(50) NOT NULL,
	`line` TEXT,
	PRIMARY KEY  (`id`)
 ) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_MAILALIASES:
					return '
CREATE TABLE `'.$pref.'awm_mailaliases` (
	`id` int(11) NOT NULL auto_increment,
	`id_acct` int(11) NOT NULL,
	`alias_name` varchar(200) NOT NULL default \'\',
	`alias_domain` varchar(200) NOT NULL default \'\',
	`alias_to` varchar(255) NOT NULL default \'\',
	PRIMARY KEY  (`id`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_MAILINGLISTS:
					return '
CREATE TABLE `'.$pref.'awm_mailinglists` (
	`id` int(11) NOT NULL auto_increment,
	`id_acct` int(11) NOT NULL,
	`list_name` varchar(255) NOT NULL default \'\',
	`list_to` varchar(255) NOT NULL default \'\',
	PRIMARY KEY  (`id`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_SUBADMINS:
					return '
CREATE TABLE `'.$pref.'awm_subadmins` (
	`id_admin` int(11) NOT NULL auto_increment,
	`login` varchar(255),
	`password` varchar(255),
	`description` varchar(255),
	PRIMARY KEY  (`id_admin`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_SUBADMIN_DOMAINS:
					return '
CREATE TABLE `'.$pref.'awm_subadmin_domains` (
	`id` int(11) NOT NULL auto_increment,
	`id_admin` int(11) NOT NULL,
	`id_domain` int(11) NOT NULL,
	PRIMARY KEY  (`id`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_MAILFORWARDS:
					return '
CREATE TABLE `'.$pref.'awm_mailforwards` (
	`id` int(11) NOT NULL auto_increment,
	`id_acct` int(11) NOT NULL,
	`forward_name` varchar(200) NOT NULL default \'\',
	`forward_domain` varchar(200) NOT NULL default \'\',
	`forward_to` varchar(255) NOT NULL default \'\',
	PRIMARY KEY  (`id`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

				case DBTABLE_AWM_FNBL_RUNS:
					return '
CREATE TABLE `'.$pref.'acal_awm_fnbl_runs` (
	`id_run` int(11) NOT NULL auto_increment,
	`run_date` datetime NULL,
	PRIMARY KEY  (`id_run`)
) /*!40101 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */';

			}
			return '';
		}

		/**
		 * @param int $userLicenceNum
		 * @return string
		 */
		function UpdateAUsersByLicences($userLicenceNum)
		{
			$_tmp = '';
			if ($userLicenceNum > 0)
			{
				$_tmp = ' LIMIT '.$userLicenceNum;
			}

			$sql = '
UPDATE %1$sa_users SET deleted = 0
WHERE %1$sa_users.id_user IN (SELECT %1$sawm_settings.id_user FROM %1$sawm_settings)
ORDER BY %1$sa_users.id_user'.$_tmp;

			return sprintf($sql, $this->_prefix);
		}
	}
	
	class MSSQL_CommandCreator extends main_CommandCreator 
	{
		/**
		 * @param	int	$type
		 * @return	MSSQL_CommandCreator
		 */
		function MSSQL_CommandCreator($type, $columnEscape = array('', ''), $prefix = '')
		{
			main_CommandCreator::main_CommandCreator($type, $columnEscape, $prefix);
		}
		
		function GetDateFormat($fieldName)
		{
			return 'CONVERT(VARCHAR, '.$fieldName.', 120)';
		}

		function UpdateDateFormat($fieldValue)
		{
			return 'CONVERT(DATETIME, '.$this->_escapeString($fieldValue).', 120)';
		}

		/**
		 * @return	string
		 */
		function ScriptUpdateDomainCustomData($settings)
		{
			$sql = '
UPDATE %sawm_domains SET
	url = %s, site_name = %s, settings_mail_protocol = %d, settings_mail_inc_host = %s, settings_mail_inc_port = %d,
	settings_mail_out_host = %s, settings_mail_out_port = %d, settings_mail_out_auth = %d,
	allow_direct_mode = %d, direct_mode_id_def = %d,
	attachment_size_limit = %d, allow_attachment_limit = %d,
	mailbox_size_limit = %d, allow_mailbox_limit = %d, take_quota = %d,
	allow_new_users_change_set = %d, allow_auto_reg_on_login = %d,
	allow_users_add_accounts = %d, allow_users_change_account_def = %d,
	def_user_charset = %d, allow_users_change_charset = %d,
	def_user_timezone = %d, allow_users_change_timezone = %d,
	msgs_per_page = %d, skin = %s, allow_users_change_skin = %d,
	lang = %s, allow_users_change_lang = %d, show_text_labels = %d,
	allow_ajax = %d, allow_editor = %d, allow_contacts = %d, allow_calendar = %d,
	hide_login_mode = %d, domain_to_use = %s, allow_choosing_lang = %d,
	allow_advanced_login = %d, allow_auto_detect_and_correct = %d, use_captcha = %d,
	use_domain_selection = %d, view_mode = %d
	WHERE ISNULL(use_domain_selection, \'99\') == \'99\'';


			return sprintf($sql, $this->_prefix,
								$this->_escapeString(''),
								$this->_escapeString($settings->WindowTitle),
								$settings->IncomingMailProtocol,
								$this->_escapeString($settings->IncomingMailServer),
								$settings->IncomingMailPort,
								$this->_escapeString($settings->OutgoingMailServer),
								$settings->OutgoingMailPort,
								$settings->ReqSmtpAuth,
								$settings->AllowDirectMode,
								$settings->DirectModeIsDefault,
								$settings->AttachmentSizeLimit,
								$settings->EnableAttachmentSizeLimit,
								$settings->MailboxSizeLimit,
								$settings->EnableMailboxSizeLimit,
								$settings->TakeImapQuota,
								$settings->AllowUsersChangeEmailSettings,
								$settings->AllowNewUsersRegister,
								$settings->AllowUsersAddNewAccounts,
								$settings->AllowUsersChangeAccountsDef,
								$settings->DefaultUserCharset,
								$settings->AllowUsersChangeCharset,
								$settings->DefaultTimeZone,
								$settings->AllowUsersChangeTimeZone,
								$settings->MailsPerPage,
								$this->_escapeString($settings->DefaultSkin),
								$settings->AllowUsersChangeSkin,
								$this->_escapeString($settings->DefaultLanguage),
								$settings->AllowUsersChangeLanguage,
								$settings->ShowTextLabels,
								$settings->AllowAjax,
								$settings->AllowDhtmlEditor,
								$settings->AllowContacts,
								$settings->AllowCalendar,
								$settings->HideLoginMode,
								$this->_escapeString($settings->DefaultDomainOptional),
								$settings->AllowLanguageOnLogin,
								$settings->AllowAdvancedLogin,
								$settings->AutomaticCorrectLoginSettings,
								$settings->UseCaptcha,
								$settings->UseMultipleDomainsSelection,
								$settings->ViewMode
				);
		}

		/**
		 * @param string $accountId
		 * @return string
		 */
		function GetFolders($accountId)
		{
			$sql = '
SELECT p.id_folder, p.id_parent, p.type, p.name, p.full_path, p.sync_type, p.hide, p.fld_order,
	COUNT(messages.id) AS message_count, COUNT(messages_unread.seen) AS unread_message_count,
	SUM(messages.size) AS folder_size, MAX(folder_level) AS level
FROM %1$sawm_folders as n, %1$sawm_folders_tree as t, %1$sawm_folders as p
LEFT OUTER JOIN %1$sawm_messages AS messages ON p.id_folder = messages.id_folder_db
LEFT OUTER JOIN %1$sawm_messages AS messages_unread ON
	p.id_folder = messages_unread.id_folder_db AND 
	messages.id = messages_unread.id AND messages_unread.seen = 0
WHERE n.id_parent = -1
	AND n.id_folder = t.id_parent
	AND t.id_folder = p.id_folder
	AND p.id_acct = %2$d
GROUP BY p.id_folder, p.id_parent, p.type, p.name, p.full_path, p.sync_type, p.hide, p.fld_order
ORDER BY p.fld_order';			
			
			return sprintf($sql, $this->_prefix, $accountId);
		}
		
		/**
		 * @param	int		$domainId
		 * @param	int		$page
		 * @param	int		$pageCnt
		 * @param	string	$orderBy[optional] = null
		 * @param	bool	$asc[optional] = null
		 * @param	string	$condition[optional] = null
		 * @return	string
		 */
		function GetAccountList($domainId, $page, $pageCnt, $orderBy = null, $asc = null, $condition = null)
		{
            $add = $addstr = '';
			if ($condition !== null && strlen($condition) > 0)
			{
				$add = 'AND email LIKE '.$this->_escapeString('%'.$condition.'%');
			}
			
			if ($orderBy === null)
			{
				$orderBy = 'email';
			}
			
			if ($asc === null)
			{
				$asc = true;
			}
			
			$start = ($page > 0) ? ($page - 1) * $pageCnt : 0;

			if ($start > 0)
			{
				$addstr = ' AND id_acct NOT IN
(SELECT id_acct FROM
(SELECT TOP %d acct1.id_user as id_user, id_acct, email,
%s AS nlast_login, logins_count, mailbox_size, mailbox_limit,
mailing_list, mail_protocol, def_acct
FROM %sawm_accounts AS acct1
INNER JOIN %sawm_settings AS sett1 ON acct1.id_user = sett1.id_user
WHERE id_acct > -1 AND id_domain = %d %s
ORDER BY %s %s, def_acct DESC) AS stable) ';
				
				$addstr = sprintf($addstr, $start, $this->GetDateFormat('last_login'),
						$this->_prefix, $this->_prefix, $domainId, $add,
						$orderBy, ($asc) ? 'ASC' : 'DESC');				
			}
			
			$sql = '
SELECT TOP %d acct.id_user as id_user, id_acct, email,
%s AS nlast_login, logins_count, mailbox_size, mailbox_limit,
mailing_list, mail_protocol, def_acct
FROM %sawm_accounts AS acct
INNER JOIN %sawm_settings AS sett ON acct.id_user = sett.id_user
WHERE id_acct > -1 AND id_domain = %d %s%s
ORDER BY %s %s, def_acct DESC';
			
			return sprintf($sql, $pageCnt, $this->GetDateFormat('last_login'), 
					$this->_prefix, $this->_prefix, $domainId, $add, $addstr, 
					$orderBy, ($asc) ? 'ASC' : 'DESC');					
		}
		
		function SelectAllAccounts($pageNumber, $accountPerPage, $sortField, $sortOrder, $searchText)
		{
			$nom = ($pageNumber > 0) ? ($pageNumber - 1) * $accountPerPage : 0;
			$dopstr = '';
			$search = trim($searchText);
		
			if ($nom > 0)
			{
				$dopstr = ' AND id_acct NOT IN
						(SELECT id_acct FROM
						(SELECT TOP %d id_acct, acct1.id_user as id_user, deleted, email,
						mail_inc_host, mail_out_host, %s AS nlast_login, logins_count,
						mailbox_size, mailbox_limit, def_acct
						FROM %sawm_accounts AS acct1
						INNER JOIN %sawm_settings AS sett1 ON acct1.id_user = sett1.id_user
						WHERE id_acct > -1 %s
						ORDER BY %s %s, def_acct DESC) AS stable) ';
				
				$dopstr = sprintf($dopstr, $nom, CDateTime::GetMsSqlDateFormat('last_login'),
						$this->_settings->DbPrefix, $this->_settings->DbPrefix, $search,
						$sortField, ($sortOrder)?'DESC':'ASC');
			}
		
			$sql = 'SELECT TOP %d id_acct, acct.id_user as id_user, deleted, email,
						mail_inc_host, mail_out_host, %s AS nlast_login, logins_count,
						mailbox_size, mailbox_limit, def_acct
					FROM %sawm_accounts AS acct
					INNER JOIN %sawm_settings AS sett ON acct.id_user = sett.id_user
					WHERE id_acct > -1 %s%s
					ORDER BY %s %s, def_acct DESC';
					
			
			return sprintf($sql, $accountPerPage, CDateTime::GetMsSqlDateFormat('last_login'), 
					$this->_settings->DbPrefix, $this->_settings->DbPrefix, $search, $dopstr, 
					$sortField, ($sortOrder)?'DESC':'ASC');		
		} 
		
		/**
		 * @param int $id
		 * @return string
		 */
		function DeleteFolderTreeById($id)
		{
			$sql = '
DELETE
FROM %1$sawm_folders_tree
FROM %1$sawm_folders
WHERE %1$sawm_folders.id_folder = %1$sawm_folders_tree.id_folder
AND %1$sawm_folders.id_acct = %2$d';
			
			return sprintf($sql, $this->_prefix, $id);
		}
		
		/**
		 * @param int $id
		 * @return string
		 */
		function DeleteAddrGroupsContactsById($id)
		{
			$sql = '
DELETE 
FROM %1$sawm_addr_groups_contacts
FROM %1$sawm_addr_groups
WHERE %1$sawm_addr_groups_contacts.id_group = %1$sawm_addr_groups.id_group
AND %1$sawm_addr_groups.id_user = %2$d';

			return sprintf($sql, $this->_prefix, $id);
		}
		
		/**
		 * @param int $id
		 * @return string
		 */
		function DeleteCalendarEvents($id)
		{
			$sql = '
DELETE
FROM %1$sacal_events
FROM %1$sacal_calendars
WHERE %1$sacal_events.calendar_id = %1$sacal_calendars.calendar_id
AND %1$sacal_calendars.user_id = %2$d';

			return sprintf($sql, $this->_prefix, $id);
		}
		
		/**
		 * @param array $cal_ids
		 * @return string
		 */
		function DeleteCalendarExclusions($cal_ids)
		{
			$is_string = implode(',', $cal_ids);
			$sql = '
DELETE
FROM %1$sacal_exclusions
FROM %1$sacal_events
WHERE %1$sacal_exclusions.id_event = %1$sacal_events.event_id 
AND %1$sacal_events.calendar_id IN (%2$d)';

			return sprintf($sql, $this->_prefix, $is_string);
		}
		
		/**
		 * @param array $cal_ids
		 * @return string
		 */
		function DeleteCalendarEventrepeats($cal_ids)
		{
			$is_string = implode(',', $cal_ids);
			$sql = '
DELETE
FROM %1$sacal_eventrepeats
FROM %1$sacal_events
WHERE %1$sacal_eventrepeats.id_event = %1$sacal_events.event_id
AND %1$sacal_events.calendar_id IN (%2$d)';

			return sprintf($sql, $this->_prefix, $is_string);
		}
		
		/**
		 * @return	string
		 */
		function AllTableNames()
		{
			return 'SELECT [name] AS tableName FROM sysobjects o WHERE xtype = \'U\' AND OBJECTPROPERTY(o.id, N\'IsMSShipped\')!=1';
		}

		/**
		 * @return	string
		 */
		function GetIndexsOfTable($pref, $tableName)
		{
			return 'sp_helpindex \''.$pref.$tableName.'\'';	
		}
		
		/**
		 * @param	string	$pref
		 * @param	string	$tableName
		 * @return	string
		 */
		function GetTablesColumns($pref, $tableName)
		{
			return '
SELECT INFORMATION_SCHEMA.COLUMNS.COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME=\''.$pref.$tableName.'\'';
		}
		
		/**
		 * @param	string	$pref
		 * @param	string	$tableName
		 * @param	string	$fieldName
		 * @return	string
		 */
		function CreateIndex($pref, $tableName, $fieldName)
		{
			$temp = (strlen($pref) > 0) ? str_replace('-', '_', $pref.'_') : '';
			return '
CREATE INDEX ['.strtoupper($temp.$tableName.'_'.$fieldName).'_INDEX] 
ON ['.$pref.$tableName.'](['.$fieldName.'])';
		}
		
		function DropTable($original, $pref)
		{
			$pref = ($pref) ? $pref : '';
			return 'DROP TABLE ['.$pref.$original.']';
		}
		
		/**
		 * @param	string	$original
		 * @param	string	$pref
		 * @return	string
		 */
		function CreateTable($original, $pref)
		{
			$pref = ($pref) ? $pref : '';
			switch ($original)
			{
				case DBTABLE_A_USERS:
					return '
CREATE TABLE ['.$pref.'a_users] (
	[id_user] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL ,
	[deleted] [bit] NOT NULL DEFAULT (0)
) ON [PRIMARY]';

				case DBTABLE_AWM_ACCOUNTS:
					return '
CREATE TABLE ['.$pref.'awm_accounts] (
	[id_acct] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL ,
	[id_user] [int] NOT NULL DEFAULT (0),
	[def_acct] [bit] NOT NULL DEFAULT (0),
	[deleted] [bit] NOT NULL DEFAULT (0),
	[email] [varchar] (255)  NOT NULL DEFAULT (\'\'),
	[mail_protocol] [smallint] NOT NULL DEFAULT (0),
	[mail_inc_host] [varchar] (255)  NULL ,
	[mail_inc_login] [varchar] (255)  NULL ,
	[mail_inc_pass] [varchar] (255)  NULL ,
	[mail_inc_port] [int] NOT NULL DEFAULT (110),
	[mail_out_host] [varchar] (255)  NULL ,
	[mail_out_login] [varchar] (255)  NULL ,
	[mail_out_pass] [varchar] (255)  NULL ,
	[mail_out_port] [int] NOT NULL DEFAULT (25),
	[mail_out_auth] [bit] NOT NULL DEFAULT (1),
	[friendly_nm] [varchar] (200)  NULL,
	[use_friendly_nm] [bit] NOT NULL DEFAULT (1),
	[def_order] [tinyint] NOT NULL DEFAULT (0),
	[getmail_at_login] [bit] NOT NULL DEFAULT (0),
	[mail_mode] [tinyint] NOT NULL DEFAULT (1),
	[mails_on_server_days] [smallint] NOT NULL ,
	[signature] [text]  NULL ,
	[signature_type] [tinyint] NOT NULL DEFAULT (1),
	[signature_opt] [tinyint] NOT NULL DEFAULT (0),
	[delimiter] [char] (1)  NOT NULL DEFAULT (\'/\'),
	[mailbox_size] [bigint] NULL,
	[id_domain] [int] NOT NULL DEFAULT (0), 
	[mailing_list] [bit] NOT NULL DEFAULT (0),
	[imap_quota] [smallint] NOT NULL DEFAULT ((-1)),
	[personal_namespace] [varchar] (50) NOT NULL DEFAULT (\'\')
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]';

				case DBTABLE_AWM_ADDR_BOOK:
					return '
CREATE TABLE ['.$pref.'awm_addr_book] (
	[id_addr] [bigint] PRIMARY KEY IDENTITY (1, 1) NOT NULL ,
	[id_user] [int] NOT NULL DEFAULT (0),
	[str_id] [varchar] (100) NULL,
	[fnbl_pim_id] [bigint] NULL,
	[deleted] [bit] NOT NULL DEFAULT (0),
	[date_created] [datetime] NULL,
	[date_modified] [datetime] NULL,
	[h_email] [varchar] (255)  NULL ,
	[fullname] [varchar] (255)  NULL ,
	[notes] [varchar] (255)  NULL ,
	[use_friendly_nm] [bit] NOT NULL DEFAULT (1),
	[h_street] [varchar] (255)  NULL ,
	[h_city] [varchar] (200)  NULL ,
	[h_state] [varchar] (200)  NULL ,
	[h_zip] [varchar] (10)  NULL ,
	[h_country] [varchar] (200)  NULL ,
	[h_phone] [varchar] (50)  NULL ,
	[h_fax] [varchar] (50)  NULL ,
	[h_mobile] [varchar] (50)  NULL ,
	[h_web] [varchar] (255)  NULL ,
	[b_email] [varchar] (255)  NULL ,
	[b_company] [varchar] (200)  NULL ,
	[b_street] [varchar] (255)  NULL ,
	[b_city] [varchar] (200)  NULL ,
	[b_state] [varchar] (200)  NULL ,
	[b_zip] [varchar] (10)  NULL ,
	[b_country] [varchar] (200)  NULL ,
	[b_job_title] [varchar] (100)  NULL ,
	[b_department] [varchar] (200)  NULL ,
	[b_office] [varchar] (200)  NULL ,
	[b_phone] [varchar] (50)  NULL ,
	[b_fax] [varchar] (50)  NULL ,
	[b_web] [varchar] (255)  NULL ,
	[other_email] [varchar] (255)  NULL ,
	[primary_email] [tinyint] NULL ,
	[id_addr_prev] [bigint] NOT NULL DEFAULT (0),
	[tmp] [bit] NOT NULL DEFAULT (0),
	[use_frequency] [int] NOT NULL DEFAULT (0),
	[auto_create] [bit] NOT NULL DEFAULT (0),
	[birthday_day] [tinyint] NOT NULL DEFAULT (0),
	[birthday_month] [tinyint] NOT NULL DEFAULT (0),
	[birthday_year] [smallint] NOT NULL DEFAULT (0)
) ON [PRIMARY]';

				case DBTABLE_AWM_ADDR_GROUPS:
					return '
CREATE TABLE ['.$pref.'awm_addr_groups] (
	[id_group] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL ,
	[id_user] [int] NOT NULL DEFAULT (0),
	[group_nm] [varchar] (255)  NULL,
	[use_frequency] [int] NOT NULL DEFAULT (0),
	[email] [varchar] (255) NULL,
	[company] [varchar] (200) NULL,
	[street] [varchar] (255) NULL,
	[city] [varchar] (200) NULL,
	[state] [varchar] (200) NULL,
	[zip] [varchar] (10) NULL,
	[country] [varchar] (200) NULL,
	[phone] [varchar] (50) NULL,
	[fax] [varchar] (50) NULL,
	[web] [varchar] (255) NULL,
	[organization] [bit] NOT NULL DEFAULT (0),
	[group_str_id] [varchar] (100) NULL
) ON [PRIMARY]';

				case DBTABLE_AWM_ADDR_GROUPS_CONTACTS:
					return '
CREATE TABLE ['.$pref.'awm_addr_groups_contacts] (
	[id_addr] [bigint] NOT NULL,
	[id_group] [int] NOT NULL
) ON [PRIMARY]';

				case DBTABLE_AWM_FILTERS:
					return '
CREATE TABLE ['.$pref.'awm_filters] (
	[id_filter] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL ,
	[id_acct] [int] NOT NULL DEFAULT (0),
	[field] [tinyint] NOT NULL DEFAULT (0),
	[condition] [tinyint] NOT NULL DEFAULT (0),
	[filter] [varchar] (255)  NULL ,
	[action] [tinyint] NOT NULL DEFAULT (0),
	[id_folder] [bigint] NOT NULL DEFAULT (0),
	[applied] [bit] NOT NULL DEFAULT (1)
) ON [PRIMARY]';

				case DBTABLE_AWM_FOLDERS:
					return '
CREATE TABLE ['.$pref.'awm_folders] (
	[id_folder] [bigint] PRIMARY KEY IDENTITY (1, 1) NOT NULL ,
	[id_acct] [int] NOT NULL DEFAULT (0),
	[id_parent] [bigint] NOT NULL DEFAULT ((-1)),
	[type] [smallint] NOT NULL DEFAULT (0),
	[name] [varchar] (100)  NULL ,
	[full_path] [varchar] (255)  NULL ,
	[sync_type] [tinyint] NOT NULL DEFAULT (0),
	[hide] [bit] NOT NULL DEFAULT (0),
	[fld_order] [smallint] NOT NULL DEFAULT (0)
) ON [PRIMARY]';

				case DBTABLE_AWM_FOLDERS_TREE:
					return '
CREATE TABLE ['.$pref.'awm_folders_tree] (
	[id] [bigint] PRIMARY KEY IDENTITY (1, 1) NOT NULL ,
	[id_folder] [bigint] NOT NULL DEFAULT (0),
	[id_parent] [bigint] NOT NULL DEFAULT (0),
	[folder_level] [tinyint] NOT NULL DEFAULT (0)
) ON [PRIMARY]';

				case DBTABLE_AWM_MESSAGES:
					return '
CREATE TABLE ['.$pref.'awm_messages] (
	[id] [bigint] PRIMARY KEY IDENTITY (1, 1) NOT NULL ,
	[id_msg] [int] NOT NULL DEFAULT (0),
	[id_acct] [int] NOT NULL DEFAULT (0),
	[id_folder_srv] [bigint] NOT NULL DEFAULT (0),
	[id_folder_db] [bigint] NOT NULL DEFAULT (0),
	[str_uid] [varchar] (255) NULL ,
	[int_uid] [bigint] NOT NULL DEFAULT (0),
	[from_msg] [varchar] (255) NULL ,
	[to_msg] [varchar] (255) NULL ,
	[cc_msg] [varchar] (255) NULL ,
	[bcc_msg] [varchar] (255) NULL ,
	[subject] [varchar] (255) NULL ,
	[msg_date] [datetime] NULL ,
	[attachments] [bit] NOT NULL DEFAULT (0),
	[size] [bigint] NOT NULL ,
	[seen] [bit] NOT NULL DEFAULT (1),
	[flagged] [bit] NOT NULL DEFAULT (0),
	[priority] [tinyint] NOT NULL DEFAULT (3),
	[downloaded] [bit] NOT NULL DEFAULT (1),
	[x_spam] [bit] NOT NULL DEFAULT (0),
	[rtl] [bit] NOT NULL DEFAULT (0),
	[deleted] [bit] NOT NULL DEFAULT (0),
	[is_full] [bit] NULL DEFAULT (1),
	[replied] [bit] NULL ,
	[forwarded] [bit] NULL ,
	[flags] [int] NULL ,
	[body_text] [text] NULL ,
	[grayed] [bit] NOT NULL DEFAULT (0),
	[charset] [int] NOT NULL DEFAULT ((-1)),
	[sensitivity] [tinyint] NULL DEFAULT (0)
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]';
					
				case DBTABLE_AWM_MESSAGES_INDEX:
					return '
CREATE INDEX ['.$pref.'DBTABLE_AWM_MESSAGES_INDEX] ON ['.$pref.'awm_messages]([id_acct], [id_msg]) ON [PRIMARY]';
					
				case DBTABLE_AWM_MESSAGES_BODY:
					return '
CREATE TABLE ['.$pref.'awm_messages_body] (
	[id] [bigint] PRIMARY KEY IDENTITY (1, 1) NOT NULL ,
	[id_acct] [int] NOT NULL DEFAULT (0),
	[id_msg] [int] NOT NULL DEFAULT (0),
	[msg] [image] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]';
					
				case DBTABLE_AWM_MESSAGES_BODY_INDEX:
					return '
CREATE UNIQUE INDEX ['.$pref.'DBTABLE_AWM_MESSAGES_INDEX] ON ['.$pref.'awm_messages_body]([id_acct], [id_msg]) ON [PRIMARY]';

				case DBTABLE_AWM_READS:
					return '
CREATE TABLE ['.$pref.'awm_reads] (
	[id_read] [bigint] PRIMARY KEY IDENTITY (1, 1) NOT NULL,
	[id_acct] [int] NOT NULL DEFAULT (0),
	[str_uid] [varchar] (255) NOT NULL DEFAULT (\'\'),
	[tmp] [bit] NOT NULL DEFAULT (0)
) ON [PRIMARY]';
					
				case DBTABLE_AWM_SETTINGS:
					return '
CREATE TABLE ['.$pref.'awm_settings] (
	[id_setting] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL ,
	[id_user] [int] NOT NULL DEFAULT (0),
	[msgs_per_page] [smallint] NOT NULL DEFAULT (20),
	[white_listing] [bit] NOT NULL DEFAULT (0),
	[x_spam] [bit] NOT NULL DEFAULT (0),
	[last_login] [datetime] NULL ,
	[logins_count] [int] NOT NULL DEFAULT (0),
	[def_skin] [varchar] (255) NOT NULL DEFAULT (\''.WM_DEFAULT_SKIN.'\'),
	[def_lang] [varchar] (50) NULL ,
	[def_charset_inc] [int] NULL ,
	[def_charset_out] [int] NULL ,
	[def_timezone] [smallint] NOT NULL DEFAULT (0),
	[def_date_fmt] [varchar] (20) NOT NULL DEFAULT (\'MM/DD/YY\'),
	[hide_folders] [bit] NOT NULL DEFAULT (0),
	[mailbox_limit] [bigint] NOT NULL DEFAULT (10000000),
	[allow_change_settings] [bit] NOT NULL DEFAULT (1),
	[allow_dhtml_editor] [bit] NOT NULL DEFAULT (1),
	[allow_direct_mode] [bit] NOT NULL DEFAULT (1),
	[hide_contacts] [bit] NOT NULL DEFAULT (0),
	[db_charset] [int] NOT NULL DEFAULT (65001),
	[horiz_resizer] [smallint] NOT NULL DEFAULT (150),
	[vert_resizer] [smallint] NULL DEFAULT (115),
	[mark] [tinyint] NOT NULL DEFAULT (0),
	[reply] [tinyint] NOT NULL DEFAULT (0),
	[contacts_per_page] [smallint] NOT NULL DEFAULT (20),
	[view_mode] [tinyint] NOT NULL DEFAULT (1),
	[question_1] [varchar] (255) NULL,
	[answer_1] [varchar] (255) NULL,
	[question_2] [varchar] (255) NULL,
	[answer_2] [varchar] (255) NULL,
	[auto_checkmail_interval] [int] DEFAULT (0),
	[enable_fnbl_sync] [int] DEFAULT (0)
) ON [PRIMARY]';

				case DBTABLE_AWM_TEMP:
					return '
CREATE TABLE ['.$pref.'awm_temp] (
	[id_temp] [bigint] PRIMARY KEY IDENTITY (1, 1) NOT NULL ,
	[id_acct] [int] NOT NULL DEFAULT (0),
	[data_val] [text] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]';
					
				case DBTABLE_AWM_SENDERS:
					return '
CREATE TABLE ['.$pref.'awm_senders] (
	[id] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL ,
	[id_user] [int] NOT NULL DEFAULT (0),
	[email] [varchar] (255) NOT NULL DEFAULT (\'\'),
	[safety] [tinyint] NOT NULL DEFAULT (0)
) ON [PRIMARY]';
					
				case DBTABLE_AWM_COLUMNS:
					return '
CREATE TABLE ['.$pref.'awm_columns] (
	[id] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL ,
	[id_user] [int] NOT NULL DEFAULT (0),
	[id_column] [int] NOT NULL DEFAULT (0),
	[column_value] [int] NOT NULL DEFAULT (0)
) ON [PRIMARY]';

				case DBTABLE_CAL_USERS_DATA:
					return '
CREATE TABLE ['.$pref.'acal_users_data] (
	[settings_id] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL,
	[user_id] [int] NOT NULL DEFAULT (0),
	[timeformat] [tinyint] NOT NULL DEFAULT (1),
	[dateformat] [tinyint] NOT NULL DEFAULT (1),
	[showweekends] [tinyint] NOT NULL DEFAULT (0),
	[workdaystarts] [tinyint] NOT NULL DEFAULT (0),
	[workdayends] [tinyint] NOT NULL DEFAULT (1),
	[showworkday] [tinyint] NOT NULL DEFAULT (0),
	[weekstartson] [tinyint] NOT NULL default (0),
	[defaulttab] [tinyint] NOT NULL DEFAULT (1),
	[country] [varchar] (2) NULL,
	[timezone] [smallint] NULL,
	[alltimezones] [tinyint] NOT NULL DEFAULT (0),
	[reminders_web_url] [varchar] (255) NULL,
	[autoaddinvitation] [tinyint] NOT NULL DEFAULT (0)
) ON [PRIMARY]';
	
				case DBTABLE_CAL_CALENDARS:
					return '
CREATE TABLE ['.$pref.'acal_calendars] (
	[calendar_id] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL,
	[calendar_str_id] [varchar] (255) NULL,
	[user_id] [int] NOT NULL DEFAULT (0),
	[calendar_name] [varchar] (100)  NOT NULL DEFAULT (\'\'),
	[calendar_description] [text] NULL,
	[calendar_color] [int] NOT NULL DEFAULT (0),
	[calendar_active] [bit] NOT NULL DEFAULT (1)
) ON [PRIMARY]';
					
				case DBTABLE_CAL_EVENTS:
					return '
CREATE TABLE ['.$pref.'acal_events] (
	[event_id] [int] PRIMARY KEY IDENTITY(1, 1) NOT NULL,
	[event_str_id] [varchar] (255) NULL,
	[fnbl_pim_id] [bigint] NULL,
	[calendar_id] [int] NOT NULL DEFAULT (0),
	[event_timefrom] [datetime] NOT NULL,
	[event_timetill] [datetime] NOT NULL,
	[event_allday] [bit] NOT NULL DEFAULT (0),
	[event_name] [varchar] (100) NOT NULL DEFAULT (\'\'),
	[event_text] [text] NULL,
	[event_priority] [tinyint] NULL DEFAULT (0),
	[event_repeats] [bit] NOT NULL DEFAULT (0),
	[event_last_modified] [datetime] NULL,
	[event_owner_email] [varchar] (255) NOT NULL DEFAULT (\'\'),
	[event_appointment_access] [tinyint] NOT NULL default (0),
	[event_deleted] [int] NOT NULL DEFAULT (0)
) ON [PRIMARY]';
					
				case DBTABLE_AWM_DOMAINS:
					return '
CREATE TABLE ['.$pref.'awm_domains] (
	[id_domain] [int] PRIMARY KEY IDENTITY(1, 1) NOT NULL,
	[name] [varchar] (255) NOT NULL,
	[mail_protocol] [smallint] NOT NULL DEFAULT (0),
	[mail_inc_host] [varchar] (255) NULL,
	[mail_inc_port] [int] NOT NULL DEFAULT (110),
	[mail_out_host] [varchar] (255) NULL,
	[mail_out_port] [int] NOT NULL DEFAULT (25),
	[mail_out_auth] [bit] NOT NULL DEFAULT (0),
	[is_internal] [bit] NOT NULL DEFAULT (0),

	[url] [varchar] (255) NULL,
	[site_name] [varchar] (255) NULL,
	[settings_mail_protocol] [smallint] NULL,
	[settings_mail_inc_host] [varchar] (255) NULL,
	[settings_mail_inc_port] [int] NULL,
	[settings_mail_out_host] [varchar] (255) NULL,
	[settings_mail_out_port] [int] NULL,
	[settings_mail_out_auth] [bit] NULL,
	[allow_direct_mode] [bit] NULL,
	[direct_mode_id_def] [bit] NULL,
	[attachment_size_limit] [bigint] NULL,
	[allow_attachment_limit] [bit] NULL,
	[mailbox_size_limit] [bigint] NULL,
	[allow_mailbox_limit] [bit] NULL,
	[take_quota] [bit] NULL,
	[allow_new_users_change_set] [bit] NULL,
	[allow_auto_reg_on_login] [bit] NULL,
	[allow_users_add_accounts] [bit] NULL,
	[allow_users_change_account_def] [bit] NULL,
	[def_user_charset] [int] NULL,
	[allow_users_change_charset] [bit] NULL,
	[def_user_timezone] [int] NULL,
	[allow_users_change_timezone] [bit] NULL,

	[msgs_per_page] [smallint] NULL,
	[skin] [varchar] (50) NULL,
	[allow_users_change_skin] [bit] NULL,
	[lang] [varchar] (50) NULL,
	[allow_users_change_lang] [bit] NULL,
	[show_text_labels] [bit] NULL,
	[allow_ajax] [bit] NULL,
	[allow_editor] [bit] NULL,
	[allow_contacts] [bit] NULL,
	[allow_calendar] [bit] NULL,

	[hide_login_mode] [smallint] NULL,
	[domain_to_use] [varchar] (255) NULL,
	[allow_choosing_lang] [bit] NULL,
	[allow_advanced_login] [bit] NULL,
	[allow_auto_detect_and_correct] [bit] NULL,
	[use_captcha] [bit] NULL,
	[use_domain_selection] [bit] NULL,
	[global_addr_book] [bit] NOT NULL DEFAULT (1),
	[view_mode] [tinyint] NOT NULL DEFAULT (1),
	[ldap_auth] [bit] NOT NULL DEFAULT (0),
	[save_mail] [tinyint] NOT NULL DEFAULT (0)
) ON [PRIMARY]';
					
				case DBTABLE_CAL_SHARING:
					return '
CREATE TABLE ['.$pref.'acal_sharing] (
	[id_share] [int] PRIMARY KEY IDENTITY(1, 1) NOT NULL,
	[id_user] [int] NOT NULL,
	[id_calendar] [int] NOT NULL,
	[id_to_user] [int] NOT NULL,
	[str_to_email] [varchar] (255) NOT NULL DEFAULT (\'\'),
	[int_access_level] [tinyint] NOT NULL DEFAULT (2),
	[calendar_active] [bit] NOT NULL DEFAULT (1)
) ON [PRIMARY]';
					
				case DBTABLE_CAL_PUBLICATIONS:
					return '
CREATE TABLE ['.$pref.'acal_publications] (
	[id_publication] [int] PRIMARY KEY IDENTITY(1, 1) NOT NULL,
	[id_user] [int] NOT NULL,
	[id_calendar] [int] NOT NULL,
	[str_md5] [varchar] (32) NOT NULL,
	[int_access_level] [tinyint] NOT NULL DEFAULT (1),
	[access_type] [tinyint] NOT NULL DEFAULT (0)
) ON [PRIMARY]';

				case DBTABLE_CAL_EXCLUSIONS:
					return '
CREATE TABLE  ['.$pref.'acal_exclusions] (
  [id_exclusion] [int] PRIMARY KEY IDENTITY(1, 1) NOT NULL,
  [id_event] [int] NOT NULL,
  [id_calendar] [int] NOT NULL,
  [id_repeat] [int] NOT NULL,
  [id_recurrence_date] [datetime] NULL,
  [event_timefrom] [datetime] NOT NULL,
  [event_timetill] [datetime] NOT NULL,
  [event_name] [varchar] (100) NOT NULL,
  [event_text] [text] NULL,
  [event_allday] [tinyint] NOT NULL default (0),
  [event_last_modified] [datetime] NULL,
  [is_deleted] [tinyint] NOT NULL default (0)
) ON [PRIMARY]';
					
				case DBTABLE_CAL_EVENTREPEATS:
					return '
CREATE TABLE  ['.$pref.'acal_eventrepeats] (
  [id_repeat] [int] PRIMARY KEY IDENTITY(1, 1) NOT NULL,
  [id_event] [int] NOT NULL,
  [repeat_period] [tinyint] NOT NULL default (0),
  [repeat_order] [tinyint] NOT NULL default (1),
  [repeat_num] [int] NOT NULL default (0),
  [repeat_until] [datetime] NULL default NULL,
  [week_number] [tinyint] NULL default NULL,
  [repeat_end] [tinyint] NOT NULL default (0),
  [excluded] [tinyint] NOT NULL default (0),
  [sun] [tinyint] NOT NULL default (0),
  [mon] [tinyint] NOT NULL default (0),
  [tue] [tinyint] NOT NULL default (0),
  [wed] [tinyint] NOT NULL default (0),
  [thu] [tinyint] NOT NULL default (0),
  [fri] [tinyint] NOT NULL default (0),
  [sat] [tinyint] NOT NULL default (0)
) ON [PRIMARY]';

				case DBTABLE_CAL_REMINDERS:
					return '
CREATE TABLE ['.$pref.'acal_reminders] (
	[id_reminder] [int] PRIMARY KEY IDENTITY(1, 1) NOT NULL,
	[id_event] [int] NOT NULL,
	[id_user] [int] NULL,
	[notice_type] [tinyint] NOT NULL default (0),
	[remind_offset] [int] NOT NULL default (0),
	[sent] [int] NOT NULL default (0)
) ON [PRIMARY]';

				case DBTABLE_CAL_CRON_RUNS:
					return '
CREATE TABLE ['.$pref.'acal_cron_runs] (
	[id_run] [int] PRIMARY KEY IDENTITY(1, 1) NOT NULL,
	[run_date] [datetime] NOT NULL,
	[latest_date] [datetime] NOT NULL
) ON [PRIMARY]';

				case DBTABLE_CAL_APPOINTMENTS:
					return '
CREATE TABLE ['.$pref.'acal_appointments] (
	[id_appointment] [int] PRIMARY KEY IDENTITY(1, 1) NOT NULL,
	[id_event] [int] NOT NULL,
	[id_user] [int] NOT NULL default (0),
	[email] [varchar] (255) NOT NULL,
	[access_type] [tinyint] NOT NULL default (0),
	[status] [tinyint] NOT NULL default (0),
	[hash] [varchar] (32) NOT NULL
) ON [PRIMARY]';
					
				case DBTABLE_A_SESSIONS:
					return '
CREATE TABLE ['.$pref.'a_sessions] (
	[sess_hash] [varchar] (50) PRIMARY KEY NOT NULL,
	[sess_time] [int] NOT NULL,
	[sess_data] [text] NULL
) ON [PRIMARY]';

				case DBTABLE_AWM_TEMPFILES:
					return '
CREATE TABLE ['.$pref.'awm_tempfiles] (
	[id] [bigint] PRIMARY KEY IDENTITY (1, 1) NOT NULL,
	[id_acct] [int] NOT NULL,
	[hash] [varchar] (100) NOT NULL,
	[file_name] [varchar] (100) NOT NULL,
	[file_time] [int] NOT NULL,
	[file_size] [int] NOT NULL DEFAULT (0),
	[file_body] [image] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]';
				
				case DBTABLE_A_TEST:
					return '
CREATE TABLE ['.$pref.'a_test] (
	[id] [bigint] PRIMARY KEY IDENTITY (1, 1) NOT NULL
) ON [PRIMARY]';
					
				case DBTABLE_AWM_LOGS:
					return '
CREATE TABLE ['.$pref.'awm_logs] (
	[id] [bigint] PRIMARY KEY IDENTITY (1, 1) NOT NULL,
	[file_key] [varchar] (50) NOT NULL,
	[line] [text] NULL
) ON [PRIMARY]';

				case DBTABLE_AWM_MAILALIASES:
					return '
CREATE TABLE ['.$pref.'awm_mailaliases] (
	[id] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL,
	[id_acct] [int] NOT NULL,
	[alias_name] [varchar] (200) NOT NULL DEFAULT (\'\'),
	[alias_domain] [varchar] (200) NOT NULL DEFAULT (\'\'),
	[alias_to] [varchar] (255) NOT NULL DEFAULT (\'\')
) ON [PRIMARY]';

				case DBTABLE_AWM_MAILINGLISTS:
					return '
CREATE TABLE ['.$pref.'awm_mailinglists] (
	[id] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL,
	[id_acct] [int] NOT NULL,
	[list_name] [varchar] (255) NOT NULL DEFAULT (\'\'),
	[list_to] [varchar] (255) NOT NULL DEFAULT (\'\')
) ON [PRIMARY]';

				case DBTABLE_AWM_SUBADMINS:
					return '
CREATE TABLE ['.$pref.'awm_subadmins] (
	[id_admin] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL,
	[login] [varchar] (255),
	[password] [varchar] (255),
	[description] [varchar] (255)
) ON [PRIMARY]';

				case DBTABLE_AWM_SUBADMIN_DOMAINS:
					return '
CREATE TABLE ['.$pref.'awm_subadmin_domains] (
	[id] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL,
	[id_admin] [int] NOT NULL,
	[id_domain] [int] NOT NULL
) ON [PRIMARY]';

				case DBTABLE_AWM_MAILFORWARDS:
					return '
CREATE TABLE ['.$pref.'awm_mailforwards] (
	[id] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL,
	[id_acct] [int] NOT NULL,
	[forward_name] [varchar] (200) NOT NULL DEFAULT (\'\'),
	[forward_domain] [varchar] (200) NOT NULL DEFAULT (\'\'),
	[forward_to] [varchar] (255) NOT NULL DEFAULT (\'\')
) ON [PRIMARY]';

				case DBTABLE_AWM_FNBL_RUNS:
					return '
CREATE TABLE ['.$pref.'acal_awm_fnbl_runs] (
	[id_run] [int] PRIMARY KEY IDENTITY (1, 1) NOT NULL,
	[run_date] [datetime] NULL,
) ON [PRIMARY]';
				
			}
			return '';
		}

		/**
		 * @param int $userLicenceNum
		 * @return string
		 */
		function UpdateAUsersByLicences($userLicenceNum)
		{
			$_tmp = '';
			if ($userLicenceNum > 0)
			{
				$_tmp = ' TOP '.$userLicenceNum;
			}

			$sql = '
UPDATE %1$sa_users SET deleted = 0 WHERE id_user IN (
SELECT'.$_tmp.' id_user FROM %1$sawm_settings
INNER JOIN %1$sa_users ON (%1$sawm_settings.id_user = %1$sa_users.id_user)
ORDER BY %1$sa_users.id_user)';

			return sprintf($sql, $this->_prefix);
		}
	}
