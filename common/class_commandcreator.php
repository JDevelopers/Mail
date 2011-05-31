<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 * 
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));
	
	require_once(WM_ROOTPATH.'common/class_settings.php');
	require_once(WM_ROOTPATH.'common/class_contacts.php');

	define('QUOTE_ESCAPE', 1);
	define('QUOTE_DOUBLE', 2);
	
	class CommandCreator
	{
		/**
		 * @access private
		 * @var Settings
		 */
		var $_settings;
		
		/**
		 * @access private
		 * @var short
		 */
		var $_escapeType;
		
		/**
		 * Class Constructor
		 *
		 * @return CommandCreator
		 */
		function CommandCreator($escapeType, $settings = null)
		{
			if (null === $settings)
			{
				$this->_settings =& Settings::CreateInstance();
			}
			else
			{
				$this->_settings =& $settings;
			}
			$this->_escapeType = $escapeType;
		}
		
		/**
		 * @access protected
		 * @param string $str
		 * @return string
		 */
		function _escapeString($str)
		{
			if ($str === '' || $str === null) return "''";
			$str = ConvertUtils::ClearUtf8($str);
			switch ($this->_escapeType)
			{
				default:
				case QUOTE_ESCAPE:
					return "'".addslashes($str)."'";
				case QUOTE_DOUBLE:
					return "'".str_replace("'", "''", $str)."'";
			}
		}
		
		/**
		 * @access protected
		 * @param string $bin
		 * @return string
		 */
		function _escapeBin($bin)
		{
			return $bin;
		}
		
		/**
		 * @param array $_array
		 * @return string
		 */
		function _inOrNot($_array)
		{
			 $_return = 'IN (%s)';
			 if (is_array($_array) && count($_array) == 1)
			 {
			 	$_return = '= %s';
			 }
			 return $_return;
		}
		
		/**
		 * @access protected
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param short $mailProtocol
		 * @return string
		 */
		function _quoteUids($messageIndexSet, $indexAsUid, $mailProtocol)
		{
			/* prepare struids */
			if ($indexAsUid && ($mailProtocol == MAILPROTOCOL_POP3 || $mailProtocol == MAILPROTOCOL_WMSERVER))
			{
				$messageIndexSet = array_map(array(&$this, '_escapeString'), $messageIndexSet);
				return implode(',', $messageIndexSet);
			}
			
			return implode(',', $messageIndexSet);
		}
		
		/**
		 * @access protected
		 * @param bool $indexAsUid
		 * @param short $mailProtocol
		 * @return string
		 */
		function _getMsgIdUidFieldName($indexAsUid, $mailProtocol)
		{
			if (!$indexAsUid)
			{
				return 'id_msg';
			}
			switch ($mailProtocol)
			{
				default:
					return 'str_uid';
				case MAILPROTOCOL_IMAP4:
					return 'int_uid';
			}
		}
		
		/**
		 * @access protected
		 * @param int $order
		 * @param string $filter
		 * @param bool $asc
		 */
		function _setSortOrder($order, &$filter, &$asc)
		{
			switch ($order)
			{
				case DEFAULTORDER_Date:
					$filter = 'msg_date';
					$asc = true;
					break;
				case DEFAULTORDER_DateDesc:
					$filter = 'msg_date';
					$asc = false;
					break;
				case DEFAULTORDER_From:
					$filter = 'from_msg';
					$asc = true;
					break;
				case DEFAULTORDER_FromDesc:
					$filter = 'from_msg';
					$asc = false;
					break;
				case DEFAULTORDER_To:
					$filter = 'to_msg';
					$asc = true;
					break;
				case DEFAULTORDER_ToDesc:
					$filter = 'to_msg';
					$asc = false;
					break;
				case DEFAULTORDER_Size:
					$filter = 'size';
					$asc = true;
					break;
				case DEFAULTORDER_SizeDesc:
					$filter = 'size';
					$asc = false;
					break;
				case DEFAULTORDER_Subj:
					$filter = 'subject';
					$asc = true;
					break;
				case DEFAULTORDER_SubjDesc:
					$filter = 'subject';
					$asc = false;
					break;
				case DEFAULTORDER_Attach:
					$filter = 'attachments';
					$asc = true;
					break;
				case DEFAULTORDER_AttachDesc:
					$filter = 'attachments';
					$asc = false;
					break;
				case DEFAULTORDER_Flag:
					$filter = 'flagged';
					$asc = true;
					break;
				case DEFAULTORDER_FlagDesc:
					$filter = 'flagged';
					$asc = false;
					break;
			}
		}

		function DeleteFunambolContacts($userid)
		{
			$sql = 'DELETE FROM %sfnbl_pim_contact WHERE userid=%s';

			return sprintf($sql, $this->_settings->DbPrefix, $this->_escapeString($userid));
		}

		function DeleteFunambolEvents($userid)
		{
			$sql = 'DELETE FROM %sfnbl_pim_calendar WHERE userid=%s';

			return sprintf($sql, $this->_settings->DbPrefix, $this->_escapeString($userid));
		}

		/**
		 * @param string $email
		 * @return string
		 */
		function SelectSendersByEmail($email, $idUser)
		{
			$sql = 'SELECT safety FROM %sawm_senders WHERE id_user = %d AND email = %s';
			
			return sprintf($sql, $this->_settings->DbPrefix, $idUser, $this->_escapeString($email));
		}
		
		/**
		 * @param String $strGroupName
		 * @return string
		 */
		function CheckExistsGroupByName($strGroupName, $idUser)
		{
			$sql = 'SELECT COUNT(id_group) as mcount FROM %sawm_addr_groups WHERE id_user = %d AND group_nm LIKE %s';
			
			return sprintf($sql, $this->_settings->DbPrefix, $idUser, $this->_escapeString($strGroupName));
		}

		
		/**
		 * @param string $email
		 * @param int $safety
		 * @return string
		 */
		function UpdateSenders($email, $safety, $idUser)
		{
			$sql = 'UPDATE %sawm_senders 
						SET safety = %d
						WHERE id_user = %d AND email = %s';
			
			return sprintf($sql, $this->_settings->DbPrefix, $safety, $idUser, $this->_escapeString($email));
		}
		
		/**
		 * @param string $email
		 * @param int $safety
		 * @return string
		 */
		function InsertSenders($email, $safety, $idUser)
		{
			$sql = 'INSERT INTO %sawm_senders (id_user, email, safety) VALUES (%d, %s, %d)';
			
			return sprintf($sql, $this->_settings->DbPrefix, $idUser, $this->_escapeString($email), (int) $safety);			
		}

		/**
		 * @param Folder $folder
		 * @return string
		 */
		function GetFolderMessageCountAll($folder)
		{
			$sql = 'SELECT COUNT(id) AS message_count FROM %sawm_messages
					WHERE id_acct = %d AND id_folder_db = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $folder->IdAcct, $folder->IdDb);			
		}

		/**
		 * @param Folder $folder
		 * @return string
		 */
		function GetFolderMessageCountUnread($folder)
		{
			$sql = 'SELECT COUNT(id) AS unread_message_count FROM %sawm_messages
					WHERE id_acct = %d AND id_folder_db = %d AND seen = 0';
			
			return sprintf($sql, $this->_settings->DbPrefix, $folder->IdAcct, $folder->IdDb);			
		}
		
		/**
		 * @param int $accountId
		 * @return string
		 */
		function SelectAccountData($accountOrUserId, $accountByUserId = false)
		{
			if($accountByUserId)
			{
				$sql = 'SELECT
							id_acct, acct.id_user as id_user, def_acct, deleted, email, mail_protocol,
							mail_inc_host, mail_inc_login, mail_inc_pass, mail_inc_port, mail_out_host,
							mail_out_login, mail_out_pass, mail_out_port, mail_out_auth, friendly_nm,
							use_friendly_nm, def_order,	getmail_at_login, mail_mode, mails_on_server_days,
							signature_type, signature_opt, delimiter, personal_namespace,
							msgs_per_page, white_listing, x_spam, %s as last_login, logins_count, def_skin,
							def_lang, def_charset_inc, def_charset_out, def_timezone, def_date_fmt,
							hide_folders, mailbox_limit, mailbox_size, id_domain, mailing_list,
							allow_change_settings, allow_dhtml_editor, allow_direct_mode, hide_contacts, db_charset,
							horiz_resizer, vert_resizer, mark, reply, contacts_per_page, view_mode, imap_quota,
							question_1, answer_1, question_2, answer_2, auto_checkmail_interval, enable_fnbl_sync
						FROM %sawm_accounts AS acct
						INNER JOIN %sawm_settings AS sett ON acct.id_user = sett.id_user
						WHERE acct.id_user = %d AND acct.def_acct = 1 AND acct.deleted = 0 AND acct.mailing_list = 0
						ORDER BY acct.email
						LIMIT 1;
				';
			}
			else
			{
				$sql = 'SELECT id_acct, acct.id_user as id_user, def_acct, deleted, email, mail_protocol,
							mail_inc_host, mail_inc_login, mail_inc_pass, mail_inc_port, mail_out_host,
							mail_out_login, mail_out_pass, mail_out_port, mail_out_auth, friendly_nm,
							use_friendly_nm, def_order,	getmail_at_login, mail_mode, mails_on_server_days,
							signature_type, signature_opt, delimiter, personal_namespace,
							msgs_per_page, white_listing, x_spam, %s as last_login, logins_count, def_skin,
							def_lang, def_charset_inc, def_charset_out, def_timezone, def_date_fmt,
							hide_folders, mailbox_limit, mailbox_size, id_domain, mailing_list,
							allow_change_settings, allow_dhtml_editor, allow_direct_mode, hide_contacts, db_charset,
							horiz_resizer, vert_resizer, mark, reply, contacts_per_page, view_mode, imap_quota,
							question_1, answer_1, question_2, answer_2, auto_checkmail_interval, enable_fnbl_sync
						FROM %sawm_accounts AS acct
						INNER JOIN %sawm_settings AS sett ON acct.id_user = sett.id_user
						WHERE id_acct = %d AND mailing_list = 0';
			}
			return sprintf($sql, $this->GetDateFormat('last_login'), $this->_settings->DbPrefix, $this->_settings->DbPrefix, $accountOrUserId);
		}
		
		/** 
		 * @param string $email
		 * @param string $login
		 * @return string
		 */
		function SelectAccountFullDataByLogin($email, $login)
		{
			$sql = 'SELECT id_acct, acct.id_user as id_user, def_acct, deleted, email, mail_protocol,
						mail_inc_host, mail_inc_login, mail_inc_pass, mail_inc_port, mail_out_host,
						mail_out_login, mail_out_pass, mail_out_port, mail_out_auth, friendly_nm,
						use_friendly_nm, def_order, getmail_at_login, mail_mode, mails_on_server_days,
						signature_type, signature_opt, delimiter, personal_namespace,
						msgs_per_page, white_listing, x_spam, %s as last_login, logins_count,	def_skin,
						def_lang, def_charset_inc, def_charset_out, def_timezone, def_date_fmt,
						hide_folders, mailbox_limit, mailbox_size, id_domain, mailing_list,
						allow_change_settings, allow_dhtml_editor, allow_direct_mode, hide_contacts, db_charset,
						horiz_resizer, vert_resizer, mark, reply, contacts_per_page, view_mode, imap_quota
					FROM %sawm_accounts AS acct
					INNER JOIN %sawm_settings AS sett ON acct.id_user = sett.id_user
					WHERE email = %s AND mail_inc_login = %s AND mailing_list = 0';
			
			return sprintf($sql, $this->GetDateFormat('last_login'), $this->_settings->DbPrefix, $this->_settings->DbPrefix,
								$this->_escapeString($email), $this->_escapeString($login));
		}

		/**
		 * @param int $accountId
		 * @return string
		 */
		function SelectSignature($accountId)
		{
			$sql = 'SELECT id_acct, signature FROM %sawm_accounts WHERE id_acct = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $accountId);
		}
		
		function TempFilesLoadFile($acct_id, $hash, $fileName)
		{
			$sql = 'SELECT file_body FROM %sawm_tempfiles
					WHERE id_acct = %d AND hash = %s AND file_name = %s';

			return sprintf($sql, $this->_settings->DbPrefix, $acct_id, 
				$this->_escapeString($hash), $this->_escapeString($fileName));
		}
		
		function TempFilesFileSize($acct_id, $hash, $fileName)
		{
			$sql = 'SELECT file_size FROM %sawm_tempfiles
					WHERE id_acct = %d AND hash = %s AND file_name = %s';

			return sprintf($sql, $this->_settings->DbPrefix, $acct_id, 
				$this->_escapeString($hash), $this->_escapeString($fileName));
		}
		
		function TempFilesSaveFile($acct_id, $hash, $fileName, $rawbody)
		{
			$sql = 'INSERT INTO %sawm_tempfiles (id_acct, hash, file_name, file_size, file_time, file_body)
					VALUES (%d, %s, %s, %d, %d, %s)';
			
			return sprintf($sql, $this->_settings->DbPrefix, $acct_id,
									$this->_escapeString($hash),
									$this->_escapeString($fileName),
									strlen($rawbody),
									time(),
									$this->_escapeBin($rawbody));
		}
		
		function TempFilesClearAccount($acct_id, $hash = null)
		{
			$suffix = (null !== $hash) ? ' AND hash = '.$this->_escapeString($hash) : '';
			$sql = 'DELETE FROM %sawm_tempfiles WHERE id_acct = %d%s';
			return sprintf($sql, $this->_settings->DbPrefix, $acct_id, $suffix);
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function LoadMessagesFromDB($msgArray, $account)
		{
			$sql = 'SELECT id_msg, msg
					FROM %sawm_messages_body
					WHERE id_acct = %d AND id_msg '.$this->_inOrNot($msgArray);

			return sprintf($sql, $this->_settings->DbPrefix, $account->Id,
					$this->_quoteUids($msgArray, false, $account->MailProtocol));
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function PreLoadMessagesFromDB($messageIndes, $indexAsUid, $folder, $account)
		{
			$sql = 'SELECT id_msg, %s AS uid, priority, flags, downloaded, size
					FROM %sawm_messages
					WHERE id_acct = %d AND id_folder_db = %d AND %s '.$this->_inOrNot($messageIndes);

			return sprintf($sql, $this->_getMsgIdUidFieldName(true, $account->MailProtocol),
							$this->_settings->DbPrefix,
							$account->Id, $folder->IdDb, $this->_getMsgIdUidFieldName($indexAsUid, $account->MailProtocol),
							$this->_quoteUids($messageIndes, $indexAsUid, $account->MailProtocol));
		}
		
		/**
		 * @param Account $account
		 * @return string
		 */
		function InsertAccount(&$account)
		{
			$sql = 'INSERT INTO %sawm_accounts (id_user, id_domain, def_acct, deleted, email, mail_protocol,
							mail_inc_host, mail_inc_login, mail_inc_pass, mail_inc_port, mail_out_host,
							mail_out_login, mail_out_pass, mail_out_port, mail_out_auth, friendly_nm,
							use_friendly_nm, def_order, getmail_at_login, mail_mode, mails_on_server_days,
							signature, signature_type, signature_opt, delimiter, imap_quota, personal_namespace)
					VALUES (%d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %d, %s, %s,
							%s, %s,	%d, %s, %d, %s)';
			
			return sprintf($sql, $this->_settings->DbPrefix,
									(int) $account->IdUser,
									(int) $account->IdDomain,
									(int) $account->DefaultAccount,
									(int) $account->Deleted,
									$this->_escapeString($account->Email),
									(int) $account->MailProtocol,
									$this->_escapeString($account->MailIncHost),
									$this->_escapeString($account->MailIncLogin),
									$this->_escapeString(ConvertUtils::EncodePassword($account->MailIncPassword, $account)),
									(int) $account->MailIncPort,
									$this->_escapeString($account->MailOutHost),
									$this->_escapeString($account->MailOutLogin),
									$this->_escapeString(ConvertUtils::EncodePassword($account->MailOutPassword, $account)),
									(int) $account->MailOutPort,
									(int) $account->MailOutAuthentication,
									$this->_escapeString($account->FriendlyName),
									(int) $account->UseFriendlyName,
									$account->DefaultOrder,
									(int) $account->GetMailAtLogin,
									(int) $account->MailMode,
									(int) $account->MailsOnServerDays,
									$this->_escapeString($account->Signature),
									(int) $account->SignatureType,
									(int) $account->SignatureOptions,
									$this->_escapeString($account->Delimiter),
									$account->ImapQuota,
									$this->_escapeString($account->NameSpace));
		}
		
		/**
		 * @param Account $account
		 * @return string
		 */
		function InsertSettings(&$account)
		{
			$sql = 'INSERT INTO %sawm_settings (id_user, msgs_per_page, white_listing, x_spam, last_login,
							logins_count, def_skin, def_lang, def_charset_inc, def_charset_out,
							def_timezone, def_date_fmt, hide_folders, mailbox_limit, allow_change_settings,
							allow_dhtml_editor, allow_direct_mode, hide_contacts, db_charset,
							horiz_resizer, vert_resizer, mark, reply, contacts_per_page, view_mode,
							question_1, answer_1, question_2, answer_2, auto_checkmail_interval, enable_fnbl_sync)
					VALUES(%d, %d, %d, %d, %s, %d, %s, %s, %d, %d, %d, %s,
							%d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %s, %s, %d, %d)';
			
			$date = new CDateTime(time());
			return sprintf($sql, $this->_settings->DbPrefix,
									$account->IdUser,
									$account->MailsPerPage,
									$account->WhiteListing,
									$account->XSpam,
									$this->UpdateDateFormat($date->ToANSI()),
									$account->LoginsCount,
									$this->_escapeString($account->DefaultSkin),
									$this->_escapeString($account->DefaultLanguage),
									ConvertUtils::GetCodePageNumber($account->DefaultIncCharset),
									ConvertUtils::GetCodePageNumber($account->DefaultOutCharset),
									$account->DefaultTimeZone,
									$this->_escapeString($account->DefaultDateFormat),
									$account->HideFolders,
									$account->MailboxLimit,
									$account->AllowChangeSettings,
									$account->AllowDhtmlEditor,
									$account->AllowDirectMode,
									$account->HideContacts,
									ConvertUtils::GetCodePageNumber($account->DbCharset),
									$account->HorizResizer,
									$account->VertResizer,
									$account->Mark,
									$account->Reply,
									$account->ContactsPerPage,
									$account->ViewMode,
									$this->_escapeString($account->Question1),
									$this->_escapeString($account->Answer1),
									$this->_escapeString($account->Question2),
									$this->_escapeString($account->Answer2),
									$account->AutoCheckMailInterval,
									$account->EnableMobileSync
					);
		}
		
		/**
		 * @param Account $account
		 * @param array $emailsArray
		 * @return string
		 */
		function SelectExistEmails($account, $emailsArray)
		{
			$emailsArray = array_map(array(&$this, '_escapeString'), $emailsArray);
			$emailsString = implode(', ', $emailsArray);
			
			$sql = 'SELECT h_email, b_email, other_email
					FROM %sawm_addr_book
					WHERE deleted = 0 AND id_user = %d AND (h_email IN (%s) OR b_email IN (%s) OR other_email IN (%s))';
			
			return sprintf($sql, $this->_settings->DbPrefix, $account->IdUser, $emailsString, $emailsString, $emailsString);
			
		}
		
		/**
		 * @param Account $account
		 * @param string $email
		 * @param string $name
		 * @return string
		 */
		function InsertAutoCreateContact($account, $email, $name = '')
		{
			$sql = 'INSERT INTO %sawm_addr_book
					(id_user, h_email, fullname, primary_email, auto_create, date_created, date_modified)
					VALUES (%d, %s, %s, 0, 1, %s, %s)';

			$date = new CDateTime(time());
			$date->SetTimeStampToUtc();
			$now = $this->UpdateDateFormat($date->ToANSI());
			return sprintf($sql, $this->_settings->DbPrefix, $account->IdUser, $this->_escapeString($email), $this->_escapeString($name), $now, $now);
		}
		
		/**
		 * @param Account $account
		 * @param array $emailsArray
		 * @return string
		 */
		function UpdateContactFrequencyByEmail($account, $emailsArray)
		{
			$emailsArray = array_map(array(&$this, '_escapeString'), $emailsArray);
			$emailsString = implode(', ', $emailsArray);
					
			$sql = 'UPDATE %sawm_addr_book
					SET use_frequency = use_frequency + 1
					WHERE deleted = 0 AND id_user = %d AND (h_email IN (%s) OR b_email IN (%s) OR other_email IN (%s))';
			
			return sprintf($sql, $this->_settings->DbPrefix, $account->IdUser, $emailsString, $emailsString, $emailsString);	
		}
		
		
		/**
		 * @param int $idUser
		 * @return string
		 */
		function SelectAccountColumnsData($idUser)
		{
			$sql = 'SELECT id_column, column_value FROM %sawm_columns WHERE id_user = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $idUser);
		}
		
		/**
		 * @param string $email
		 * @param int $id_user
		 * @return string
		 */
		function GetContactIdByEmail($email, $id_user)
		{
			$email = $this->_escapeString($email);
			$sql = 'SELECT id_addr FROM %sawm_addr_book
					WHERE deleted = 0 AND id_user = %d
						AND ((primary_email = %d AND h_email = %s) 
							OR (primary_email = %d AND b_email = %s)
							OR (primary_email = %d AND other_email = %s))';
			
			return sprintf($sql, $this->_settings->DbPrefix, $id_user,
				PRIMARYEMAIL_Home, $email,
				PRIMARYEMAIL_Business, $email,
				PRIMARYEMAIL_Other, $email);
		}
		
		/**
		 * @param int $idUser
		 * @param int $id_column
		 * @param int $value_column
		 * @return string
		 */
		function UpdateColumnData($idUser, $id_column, $value_column)
		{
			$sql = 'UPDATE %sawm_columns SET column_value = %d
						WHERE id_user = %d AND id_column = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $value_column, $idUser, $id_column);			
		}
		
		/**
		 * @param int $idUser
		 * @param int $id_column
		 * @param int $value_column
		 * @return string
		 */		
		function InsertColumnData($idUser, $id_column, $value_column)
		{
			$sql = 'INSERT INTO %sawm_columns (id_user, id_column, column_value)
						VALUES (%d, %d, %d)';
			return sprintf($sql, $this->_settings->DbPrefix, $idUser, $id_column, $value_column);
		}

		/**
		 * @param int $userId
		 * @return string
		 */
		function SelectAccounts($userId)
		{
			$sql = 'SELECT id_acct, mail_protocol, def_order, use_friendly_nm,
							friendly_nm, email, getmail_at_login, def_acct, mailbox_size
					FROM %sawm_accounts
					WHERE id_user = %d AND mailing_list = 0';

			return sprintf($sql, $this->_settings->DbPrefix, $userId);
		}
		
		/**
		 * @return string
		 */
		function GetAllAccountsIds()
		{
			$sql = 'SELECT id_acct FROM %sawm_accounts WHERE mailing_list = 0';
			return sprintf($sql, $this->_settings->DbPrefix);
		}
		
		/** 
		 * @param string $email
		 * @param string $login
		 * @return string
		 */
		function SelectAccountDataByLogin($email, $login)
		{
			$sql = 'SELECT id_acct, id_user, mail_inc_pass, def_acct
					FROM %sawm_accounts
					WHERE email = %s AND mailing_list = 0 AND mail_inc_login = %s';
			
			return sprintf($sql, $this->_settings->DbPrefix,
									$this->_escapeString($email),
									$this->_escapeString($login));
		}

		/** 
		 * @param string $email
		 * @param string $login
		 * @return string
		 */
		function SelectDefAccountDataByLogin($email, $login)
		{
			$sql = 'SELECT id_acct, id_user, mail_inc_pass, def_acct, deleted
					FROM %sawm_accounts
					WHERE email = %s AND mailing_list = 0 AND mail_inc_login = %s AND def_acct = 1';
			
			return sprintf($sql, $this->_settings->DbPrefix,
									$this->_escapeString($email),
									$this->_escapeString($login));
		}
		
		/** 
		 * @param string $email
		 * @return string
		 */
		function SelectAccountDataOnlyByEmail($email)
		{
			$sql = 'SELECT id_acct, id_user, mail_inc_pass, def_acct, mail_inc_login, deleted
					FROM %sawm_accounts
					WHERE email = %s AND def_acct = 1 AND mailing_list = 0';
			
			return sprintf($sql, $this->_settings->DbPrefix,
									$this->_escapeString($email));
		}
		
		/**
		 * @param array $arrIds
		 * @return string
		 */
		function UpdateGroupsFrequency($arrIds)
		{
			$sql = 'UPDATE %sawm_addr_groups
					SET use_frequency = use_frequency + 1
					WHERE id_group '.$this->_inOrNot($arrIds);
			
			$strIds = (is_array($arrIds)) ? implode(',', $arrIds) : '-1';
			
			return sprintf($sql, $this->_settings->DbPrefix, $strIds);
		}
		
		/** 
		 * @param string $email
		 * @param string $login
		 * @return string
		 */
		function SelectAccountsCountByLogin($email, $login)
		{
			$sql = 'SELECT COUNT(id_acct) AS acct_count
					FROM %sawm_accounts
					WHERE email = %s AND mail_inc_login = %s AND mailing_list = 0';
			
			return sprintf($sql, $this->_settings->DbPrefix,
									$this->_escapeString($email),
									$this->_escapeString($login));
		}
		
		/**
		 * @param string $name
		 * @return string
		 */
		function SelectDomainByName($name)
		{
			$sql = 'SELECT id_domain, name, mail_protocol, mail_inc_host, mail_inc_port,
					mail_out_host, mail_out_port, mail_out_auth, is_internal, global_addr_book, save_mail
					FROM %sawm_domains
					WHERE name = %s';
			
			return sprintf($sql, $this->_settings->DbPrefix, $this->_escapeString($name));
		}
		
		/**
		 * @param int $id
		 * @return string
		 */
		function SelectDomainById($id)
		{
			$sql = 'SELECT id_domain, name, mail_protocol, mail_inc_host, mail_inc_port,
					mail_out_host, mail_out_port, mail_out_auth, is_internal, global_addr_book, save_mail
					FROM %sawm_domains
					WHERE id_domain = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $id);
		}

		/**
		 * @return string
		 */
		function GetDomains()
		{
			$sql = 'SELECT id_domain, name, mail_protocol, mail_inc_host, mail_inc_port,
					mail_out_host, mail_out_port, mail_out_auth, is_internal, global_addr_book, save_mail
					FROM %sawm_domains';

			return sprintf($sql, $this->_settings->DbPrefix);
		}
		
		/** 
		 * @param string $email
		 * @param string $login
		 * @return string
		 */
		function SelectDefAccountsCountByLogin($email, $login, $idAcct = null)
		{
			$temp = ($idAcct !== null) ? ' AND id_acct <> '.(int) $idAcct : '';
			
			$sql = 'SELECT COUNT(id_acct) AS acct_count
					FROM %sawm_accounts
					WHERE email = %s AND mailing_list = 0 AND mail_inc_login = %s AND def_acct = 1' . $temp;
			
			return sprintf($sql, $this->_settings->DbPrefix,
									$this->_escapeString($email),
									$this->_escapeString($login)); 
		}
		
		/**
		 * @param int $accountId
		 * @param int $newAccountId
		 * @return string
		 */
		function SelectIsAccountInRing($accountId, $newAccountId)
		{
			$sql = 'SELECT COUNT(a.id_acct) AS acct_count
					FROM %sawm_accounts AS a
					INNER JOIN %sawm_accounts AS b ON a.id_user = b.id_user
					WHERE a.id_acct = %d AND b.id_acct = %d AND a.mailing_list = 0 AND b.mailing_list = 0';
			
			return sprintf($sql, $this->_settings->DbPrefix, $this->_settings->DbPrefix,
								$accountId, $newAccountId);
		}
		
		/**
		 * @param int $userId
		 * @return string
		 */
		function SelectSetings($userId)
		{
			$sql = 'SELECT * FROM %sawm_settings WHERE id_user = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $userId);		
		}

		/**
		 * @param int $_accountId
		 * @param string $_delimiter
		 * @return string
		 */
		function UpdateDelimiter($_accountId, $_delimiter)
		{
			$sql = 'UPDATE %sawm_accounts SET delimiter = %s WHERE id_acct = %d';
			return sprintf($sql, $this->_settings->DbPrefix,
						$this->_escapeString($_delimiter), $_accountId);
		}

		/**
		 * @param int $_accountId
		 * @param string $_namespace
		 * @return string
		 */
		function UpdateNameSpace($_accountId, $_namespace)
		{
			$sql = 'UPDATE %sawm_accounts SET personal_namespace = %s WHERE id_acct = %d';
			return sprintf($sql, $this->_settings->DbPrefix,
						$this->_escapeString($_namespace), $_accountId);
		}
		
		/**
		 * @param int $_accountId
		 * @param int $defOrder
		 * @return string
		 */
		function UpdateDefaultOrder($_accountId, $defOrder)
		{
			$sql = 'UPDATE %sawm_accounts SET def_order = %d WHERE id_acct = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $defOrder, $_accountId);
		}
		
		/**
	 	 * @param int $_accountId
		 * @param string $_IncPass
		 * @param string $_OutPass
		 * @return string
		 */
		function UpdateAccountPasswords($_accountId, $_IncPass, $_OutPass)
		{
			$sql = 'UPDATE %sawm_accounts SET mail_inc_pass = %s, mail_out_pass = %s WHERE id_acct = %d';
			return sprintf($sql, $this->_settings->DbPrefix, 
					$this->_escapeString($_IncPass),
					$this->_escapeString($_OutPass),
					$_accountId);
		}

		

		/**
		 * @param int $_userId
		 * @param int $_limit
		 * @return string
		 */
		function UpdateMailBoxLimit($_userId, $_limit)
		{
			$sql = 'UPDATE %sawm_settings SET mailbox_limit = %d WHERE id_user = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $_limit, $_userId);
		}
		
		/**
		 * @param int $_userId
		 * @param string $_lang
		 * @return string
		 */
		function UpdateDefaultLanguage($_userId, $_lang)
		{
			$sql = 'UPDATE %sawm_settings SET def_lang = %s WHERE id_user = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $this->_escapeString($_lang), $_userId);
		}
		
		/**
		 * @param int $_userId
		 * @param string $defOrder
		 * @return string
		 */
		function UpdateDefaultIncCharset($_userId, $defaultIncCharset)
		{
			$sql = 'UPDATE %sawm_settings SET def_charset_inc = %d WHERE id_user = %d';
			return sprintf($sql, $this->_settings->DbPrefix, ConvertUtils::GetCodePageNumber($defaultIncCharset), $_userId);
		}

		/**
		 * @param Account $account
		 * @return string
		 */
		function UpdateAccount(&$account)
		{
			$sql = 'UPDATE %sawm_accounts SET
						id_user = %d,
						def_acct = %d, deleted = %d, email = %s, mail_protocol = %d,
						mail_inc_host = %s, mail_inc_login = %s, mail_inc_pass = %s, mail_inc_port = %d,
						mail_out_host = %s, mail_out_login = %s, mail_out_pass = %s, mail_out_port = %d,
						mail_out_auth = %d, friendly_nm = %s, use_friendly_nm = %d, def_order = %d,
						getmail_at_login = %d, mail_mode = %d, mails_on_server_days = %d,
						signature = %s, signature_type = %d, signature_opt = %d,
						delimiter = %s, mailbox_size = %d, personal_namespace = %s
					WHERE id_acct = %d AND mailing_list = 0';
			
			return sprintf($sql, $this->_settings->DbPrefix,
									$account->IdUser,
									$account->DefaultAccount,
									$account->Deleted,
									$this->_escapeString($account->Email),
									$account->MailProtocol,
									$this->_escapeString($account->MailIncHost),
									$this->_escapeString($account->MailIncLogin),
									$this->_escapeString(ConvertUtils::EncodePassword($account->MailIncPassword, $account)),
									$account->MailIncPort,
									$this->_escapeString($account->MailOutHost),
									$this->_escapeString($account->MailOutLogin),
									$this->_escapeString(ConvertUtils::EncodePassword($account->MailOutPassword, $account)),
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
									$account->MailboxSize,
									$this->_escapeString($account->NameSpace),
									$account->Id);		
		}	
		
		/**
		 * @param Account $account
		 * @return string
		 */
		function UpdateSettings(&$account)
		{
			$sql = 'UPDATE %sawm_settings SET
						msgs_per_page = %d, white_listing = %d, x_spam = %d,
						def_skin = %s, def_lang = %s, def_charset_inc = %d,
						def_charset_out = %d, def_timezone = %d, def_date_fmt = %s,
						hide_folders = %d, mailbox_limit = %d, allow_change_settings = %d,
						allow_dhtml_editor = %d, allow_direct_mode = %d, hide_contacts = %d,
						db_charset = %d, horiz_resizer = %d, vert_resizer = %d, mark = %d,
						reply = %d, contacts_per_page = %d, view_mode = %d,
						question_1 = %s, answer_1 = %s, question_2 = %s, answer_2 = %s,
						auto_checkmail_interval = %d, enable_fnbl_sync = %d
					WHERE id_user = %d';

			return sprintf($sql, $this->_settings->DbPrefix,
									((int) $account->MailsPerPage > 0) ? $account->MailsPerPage : 20,
									$account->WhiteListing,
									$account->XSpam,
									$this->_escapeString($account->DefaultSkin),
									$this->_escapeString($account->DefaultLanguage),
									ConvertUtils::GetCodePageNumber($account->DefaultIncCharset),
									ConvertUtils::GetCodePageNumber($account->DefaultOutCharset),
									$account->DefaultTimeZone,
									$this->_escapeString(CDateTime::GetDbDateFormat($account->DefaultDateFormat, $account->DefaultTimeFormat)),
									$account->HideFolders,
									$account->MailboxLimit,
									$account->AllowChangeSettings,
									$account->AllowDhtmlEditor,
									$account->AllowDirectMode,
									$account->HideContacts,
									ConvertUtils::GetCodePageNumber($account->DbCharset),
									$account->HorizResizer,
									$account->VertResizer,
									$account->Mark,
									$account->Reply,
									((int) $account->ContactsPerPage > 0) ? (int) $account->ContactsPerPage : 20,
									(int) $account->ViewMode,
									$this->_escapeString($account->Question1),
									$this->_escapeString($account->Answer1),
									$this->_escapeString($account->Question2),
									$this->_escapeString($account->Answer2),
									$account->AutoCheckMailInterval,
									$account->EnableMobileSync,
									$account->IdUser);
		}

		/**
		 * @param int $userId
		 * @return string
		 */
		function UpdateLastLoginAndLoginsCount($userId)
		{
			$sql = 'UPDATE %sawm_settings
						SET last_login = %s, logins_count = logins_count + 1
					WHERE id_user = %d';
			
			$date = new CDateTime(time());
			return sprintf($sql, $this->_settings->DbPrefix,
							$this->UpdateDateFormat($date->ToANSI()), $userId);
		}

		/**
		 * @param string $host
		 * @return string
		 */
		function GetSettingsByDomainHost($host)
		{
			$sql = 'SELECT
	site_name, settings_mail_protocol, settings_mail_inc_host, settings_mail_inc_port,
	settings_mail_out_host, settings_mail_out_port, settings_mail_out_auth,
	allow_direct_mode, direct_mode_id_def, attachment_size_limit, allow_attachment_limit,
	mailbox_size_limit, allow_mailbox_limit, take_quota, allow_new_users_change_set,
	allow_auto_reg_on_login, allow_users_add_accounts, allow_users_change_account_def,
	def_user_charset, allow_users_change_charset, def_user_timezone, allow_users_change_timezone,
	msgs_per_page, skin, allow_users_change_skin, lang, allow_users_change_lang, show_text_labels,
	allow_ajax, allow_editor, allow_contacts, allow_calendar,
	hide_login_mode, domain_to_use, allow_choosing_lang, allow_advanced_login, 
	allow_auto_detect_and_correct, use_captcha, use_domain_selection, view_mode
					FROM %sawm_domains
					WHERE url = %s';
			
			$sql = 'SELECT * FROM %sawm_domains WHERE url = %s';

			return sprintf($sql, $this->_settings->DbPrefix, $this->_escapeString($host));
		}
		
		/**
		 * @param Account $account
		 * @param int $msgId
		 * @param int $charset
		 * @param WebMailMessage $message
		 * @return string
		 */
		function UpdateMessageCharset(&$account, $msgId, $charset, &$message)
		{
			$sql = 'UPDATE %sawm_messages
				SET charset = %d, from_msg = %s, to_msg = %s, cc_msg = %s, bcc_msg = %s,
					subject = %s, attachments = %d, size = %d
				WHERE id_acct = %d AND id_msg = %d';
			
			$from = new I18nString($message->GetFromAsString(), $account->DbCharset);
			$to = new I18nString($message->GetToAsString(), $account->DbCharset);
			$cc = new I18nString($message->GetCcAsString(), $account->DbCharset);
			$bcc = new I18nString($message->GetBccAsString(), $account->DbCharset);
			$subject = new I18nString($message->GetSubject(), $account->DbCharset);

			return sprintf($sql, $this->_settings->DbPrefix, (int) $charset,
									$this->_escapeString($from->Truncate(255)),
									$this->_escapeString($to->Truncate(255)),
									$this->_escapeString($cc->Truncate(255)),
									$this->_escapeString($bcc->Truncate(255)),
									$this->_escapeString($subject->Truncate(255)),
									$message->HasAttachments(),
									$message->GetMailSize(),
									(int) $account->Id, (int) $msgId);
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param Boolean $indexAsUid
		 * @param Folder $folder
		 * @param int $flags
		 * @param Account $account
		 * @return String
		 */
		function UpdateMessageFlags($messageIndexSet, $indexAsUid, $folder, $flags, $account)
		{
			$sql = 'UPDATE %sawm_messages
					SET flags = %d, seen = %d, flagged = %d, deleted = %d, replied = %d, forwarded = %d, grayed = %d';

			$sql = sprintf($sql, $this->_settings->DbPrefix, $flags,
				(int) (($flags & MESSAGEFLAGS_Seen) == MESSAGEFLAGS_Seen),
				(int) (($flags & MESSAGEFLAGS_Flagged) == MESSAGEFLAGS_Flagged),
				(int) (($flags & MESSAGEFLAGS_Deleted) == MESSAGEFLAGS_Deleted),
				(int) (($flags & MESSAGEFLAGS_Answered) == MESSAGEFLAGS_Answered),
				(int) (($flags & MESSAGEFLAGS_Forwarded) == MESSAGEFLAGS_Forwarded),
				(int) (($flags & MESSAGEFLAGS_Grayed) == MESSAGEFLAGS_Grayed)
			);
			
			if ($messageIndexSet != null)
			{
				$sql .= ' WHERE id_acct = %d AND id_folder_db = %d AND %s '.$this->_inOrNot($messageIndexSet);
				
				return sprintf($sql, $account->Id, $folder->IdDb,
								$this->_getMsgIdUidFieldName($indexAsUid, $account->MailProtocol),
								$this->_quoteUids($messageIndexSet, $indexAsUid, $account->MailProtocol));
			}
			
			$sql .= ' WHERE id_acct = %d AND id_folder_db = %d';
			return sprintf($sql, $account->Id, $folder->IdDb);

		}
		
		/** 
		 * @param int $accountId
		 * @return string
		 */
		function CountAccounts($accountId)
		{
			/* check is this last account or no */
			$sql = 'SELECT COUNT(t1.id_acct) AS count, t1.id_user AS id_user
					FROM %sawm_accounts AS t1
					INNER JOIN %sawm_accounts AS t2 ON t1.id_user = t2.id_user
					WHERE t1.id_acct = %d AND t1.mailing_list = 0
					GROUP BY t1.id_user';
			
			return sprintf($sql, $this->_settings->DbPrefix, $this->_settings->DbPrefix, $accountId);
		}
		
		/**
		 * @param bool $deleted
		 * @return string
		 */
		function InsertUserData($deleted)
		{
			$sql = 'INSERT INTO %sa_users (deleted) VALUES (%d)';
			
			return sprintf($sql, $this->_settings->DbPrefix, $deleted);
		}
		
		/**
		 * @param int $id_user
		 * @return string
		 */
		function IsSettingsExists($id_user)
		{
			$sql = 'SELECT COUNT(id_user) as cnt FROM %sawm_settings WHERE id_user = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $id_user);
		}

		/**
		 * @param int $id
		 * @return string
		 */
		function EraseUserData($id)
		{
			$sql = 'DELETE FROM %sa_users WHERE id_user = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $id);
		}
		
	
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function CreateFolderTree($folder)
		{
			$sql = 'INSERT INTO %sawm_folders_tree (id_folder, id_parent, folder_level)	
					VALUES (%d, %d, 0)';
			
			return sprintf($sql, $this->_settings->DbPrefix, $folder->IdDb, $folder->IdDb);
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function CreateSelectFolderTree($folder)
		{
			$sql = 'INSERT INTO %sawm_folders_tree (id_folder, id_parent, folder_level)	
					VALUES (%d, %d, %d)';
			
			return sprintf($sql, $this->_settings->DbPrefix, $folder->IdDb,
									$folder->IdParent, $folder->Level);			
		}		
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function SelectForCreateFolderTree($folder)
		{
			$sql = 'SELECT id_parent, folder_level
					FROM %sawm_folders_tree
					WHERE id_folder = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $folder->IdParent);		
		}
		
		/**
		 * @param Folder $folder
		 * @param string $newName
		 * @return string
		 */
		function RenameFolder($folder, $newName)
		{
			$sql = 'UPDATE %sawm_folders
					SET full_path = %s
					WHERE id_acct = %d AND id_folder = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $this->_escapeString($newName.'#'),
								$folder->IdAcct, $folder->IdDb);
		}
		
		/**
		 * @param Folder $folder
		 * @param Array $foldersId
		 * @param string $newName
		 * @return string
		 */
		function RenameSubFoldersPath($folder, $foldersId, $newSubPath)
		{
			$sql = 'UPDATE %sawm_folders
					SET full_path = CONCAT("%s", SUBSTRING(full_path, %d))
					WHERE id_acct = %d AND id_folder '.$this->_inOrNot($foldersId).' AND id_folder <> %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $newSubPath, strlen($folder->FullName) + 1,
								$folder->IdAcct, implode(',', $foldersId), $folder->IdDb);
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function DeleteFolder($folder)
		{
			$sql = 'DELETE FROM %sawm_folders
					WHERE %sawm_folders.id_folder = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $this->_settings->DbPrefix, $folder->IdDb);
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function DeleteFolderTree($folder)
		{
			$sql = 'DELETE FROM %sawm_folders_tree
					WHERE %sawm_folders_tree.id_folder = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $this->_settings->DbPrefix, $folder->IdDb);
		}
		
		/**
		 * @param int $id
		 * @param int $id_acct
		 * @return string
		 */
		function GetFolderFullName($id, $id_acct)
		{
			$sql = 'SELECT full_path FROM %sawm_folders WHERE id_acct = %d AND id_folder = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $id_acct, $id);
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function GetFolderInfo($folder)
		{
			$sql = 'SELECT full_path, name, type, sync_type, hide, fld_order, id_parent FROM %sawm_folders
					WHERE id_acct = %d AND id_folder = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $folder->IdAcct, $folder->IdDb);
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function GetFolderChildCount($folder)
		{
			$sql = 'SELECT COUNT(child.id_folder) AS child_count
					FROM %sawm_folders AS parent
					INNER JOIN %sawm_folders AS child ON parent.id_folder = child.id_parent
					WHERE parent.id_acct = %d AND parent.id_folder = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix,
								$this->_settings->DbPrefix, $folder->IdAcct, $folder->IdDb);
		}

		/**
		 * @param Folder $folder
		 * @return string
		 */
		function UpdateFolder($folder)
		{
			$sql = 'UPDATE %sawm_folders
					SET name = %s, type = %d, sync_type = %d, hide = %d, fld_order = %d
					WHERE id_acct = %d AND id_folder = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, 
								$this->_escapeString($folder->Name.'#'),
								$folder->Type, $folder->SyncType,
								$folder->Hide, $folder->FolderOrder,
								$folder->IdAcct, $folder->IdDb);
		}

		/**
		 * @param int $accountId
		 * @param short $type
		 * @return string
		 */
		function GetFolderSyncType($accountId, $type)
		{
			$sql = 'SELECT sync_type FROM %sawm_folders WHERE id_acct = %d AND type = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $accountId, $type);
		}
		
		/**
		 * @param WebMailMessage $message
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function UpdateMessageHeader($message, $folder, $account)
		{
			$sql = 'UPDATE %sawm_messages SET
			 			from_msg = %s, to_msg = %s, cc_msg = %s, bcc_msg = %s, subject = %s,
						msg_date = %s, attachments = %d, size = %d, x_spam = %d,
						seen = %d, flagged = %d, deleted = %d, replied = %d, grayed = %d,
						flags= %d, priority = %d, body_text = %s
					WHERE id_msg = %d AND id_folder_db = %d AND id_acct = %d';
			
			$date =& $message->GetDate();
			$from = new I18nString($message->GetFromAsString(), $account->DbCharset);
			$to = new I18nString($message->GetToAsString(), $account->DbCharset);
			$cc = new I18nString($message->GetCcAsString(), $account->DbCharset);
			$bcc = new I18nString($message->GetBccAsString(), $account->DbCharset);
			$subject = new I18nString($message->GetSubject(), $account->DbCharset);
			
			return sprintf($sql, $this->_settings->DbPrefix,
									$this->_escapeString($from->Truncate(255)),
									$this->_escapeString($to->Truncate(255)),
									$this->_escapeString($cc->Truncate(255)),
									$this->_escapeString($bcc->Truncate(255)),
									$this->_escapeString($subject->Truncate(255)),
									
									$this->UpdateDateFormat($date->ToANSI()),
									(int) $message->HasAttachments(),
									$message->GetMailSize(),
									(int) $message->GetXSpamStatus(),
									(($message->Flags & MESSAGEFLAGS_Seen) == MESSAGEFLAGS_Seen),
									(($message->Flags & MESSAGEFLAGS_Flagged) == MESSAGEFLAGS_Flagged),
									(($message->Flags & MESSAGEFLAGS_Deleted) == MESSAGEFLAGS_Deleted),
									(($message->Flags & MESSAGEFLAGS_Answered) == MESSAGEFLAGS_Answered),
									(($message->Flags & MESSAGEFLAGS_Grayed) == MESSAGEFLAGS_Grayed),
									$message->Flags,
									$message->GetPriorityStatus(),
									$this->_escapeString(substr($message->GetPlainLowerCaseBodyText(), 0, 500000)),
									$message->IdMsg, $folder->IdDb, $account->Id);
		}
		
		/**
		 * @param WebMailMessage $message
		 * @param Folder $folder
		 * @param bool $downloaded
		 * @param Account $account
		 * @return string
		 */
		function SaveMessageHeader($message, $folder, $downloaded, $account)
		{
			/* save message header */
			$sql = 'INSERT INTO %sawm_messages (id_msg, id_acct, id_folder_srv, id_folder_db,
								str_uid, int_uid, from_msg, to_msg, cc_msg, bcc_msg, subject,
								msg_date, attachments, size, downloaded, x_spam,
								seen, flagged, rtl, deleted, replied, grayed, flags,
								priority, body_text, forwarded, charset, sensitivity)
					VALUES (%d, %d,	%d, %d, %s, %d, %s, %s, %s, %s, %s, %s, %d, %d, %d,	%d, %d, %d, %d, %d, %d, %d, %d, %d, %s, 0, -1, %d)';
			
			$date =& $message->GetDate();
			$from = new I18nString($message->GetFromAsString(), $account->DbCharset);
			$to = new I18nString($message->GetToAsString(), $account->DbCharset);
			$cc = new I18nString($message->GetCcAsString(), $account->DbCharset);
			$bcc = new I18nString($message->GetBccAsString(), $account->DbCharset);
			$subject = new I18nString($message->GetSubject(), $account->DbCharset);

			$str_uid = $int_uid = null;
			if ($account->MailProtocol == MAILPROTOCOL_IMAP4)
			{
				$str_uid = '';
				$int_uid = $message->Uid;
			}
			else
			{
				$str_uid = $message->Uid;
				$int_uid = 0;
			}

			return sprintf($sql, $this->_settings->DbPrefix,
									/* $this->_getMsgIdUidFieldName(true, $account->MailProtocol), */
									$message->IdMsg,
									$account->Id,
									$folder->IdDb, $folder->IdDb,
									$this->_escapeString($str_uid), $int_uid,
									
									$this->_escapeString($from->Truncate(255)),
									$this->_escapeString($to->Truncate(255)),
									$this->_escapeString($cc->Truncate(255)),
									$this->_escapeString($bcc->Truncate(255)),
									$this->_escapeString($subject->Truncate(255)),
									
									$this->UpdateDateFormat($date->ToANSI()),
									(int) $message->HasAttachments(),
									$message->GetMailSize(),
									(int) $downloaded,
									(int) $message->GetXSpamStatus(),
									(($message->Flags & MESSAGEFLAGS_Seen) == MESSAGEFLAGS_Seen),
									(($message->Flags & MESSAGEFLAGS_Flagged) == MESSAGEFLAGS_Flagged),
									0,
									(($message->Flags & MESSAGEFLAGS_Deleted) == MESSAGEFLAGS_Deleted),
									(($message->Flags & MESSAGEFLAGS_Answered) == MESSAGEFLAGS_Answered),
									(($message->Flags & MESSAGEFLAGS_Grayed) == MESSAGEFLAGS_Grayed),
									$message->Flags,
									$message->GetPriorityStatus(),
									$this->_escapeString(substr($message->GetPlainLowerCaseBodyText(), 0, 500000)),
									$message->GetSensitivity()
								);
		}
		
		
		function GetMessageSize($message, $folder, $accountId)
		{
			$sql = 'SELECT size FROM %sawm_messages
					WHERE id_msg = %d AND id_folder_db = %d AND id_acct = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $message->IdMsg, $folder->IdDb, $accountId);
		}
		
		/**
		 * @param WebMailMessage $message
		 * @param string $accountId
		 * @return string
		 */
		function SaveBody(&$message, $accountId)
		{
			$sql = 'INSERT INTO %sawm_messages_body (id_acct, id_msg, msg)
					VALUES (%d, %d, %s)';
				
			return sprintf($sql, $this->_settings->DbPrefix, $accountId,
										$message->IdMsg, $this->_escapeBin($message->TryToGetOriginalMailMessage()));
		}				

		/**
		 * @param WebMailMessage $message
		 * @param string $accountId
		 * @return string
		 */
		function UpdateBody(&$message, $accountId)
		{
			$sql = 'UPDATE %sawm_messages_body SET msg = %s
					WHERE id_acct = %d AND id_msg = %d';
				
			return sprintf($sql, $this->_settings->DbPrefix,
						$this->_escapeBin($message->TryToGetOriginalMailMessage()),
						$accountId,	$message->IdMsg);
		}

		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function LoadMessagesFromFileSystem($messageIndexSet, $indexAsUid, $folder, $account)
		{
			/* read messages from the file system */
			$sql = 'SELECT id_msg, %s AS uid, priority, flags
					FROM %sawm_messages AS msg
					WHERE id_acct = %d AND id_folder_db = %d AND %s '.$this->_inOrNot($messageIndexSet);

			return sprintf($sql, $this->_getMsgIdUidFieldName(true, $account->MailProtocol), $this->_settings->DbPrefix, $account->Id, 
							$folder->IdDb, $this->_getMsgIdUidFieldName($indexAsUid, $account->MailProtocol),
							$this->_quoteUids($messageIndexSet, $indexAsUid, $account->MailProtocol));
		}
		
		/**
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function SelectIdMsgAndUid($folder, $account)
		{
			$sql = 'SELECT id_msg, %s AS uid, flags AS flag
					FROM %sawm_messages
					WHERE id_acct = %d AND id_folder_db = %d';
			
			return sprintf($sql, $this->_getMsgIdUidFieldName(true, $account->MailProtocol),
							$this->_settings->DbPrefix, $account->Id, $folder->IdDb);
		}
		
		/**
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function _SelectIdMsgAndUid($folder, $account)
		{
			$sql = 'SELECT id_msg, %s AS uid, flags AS flag
					FROM %sawm_messages
					WHERE id_acct = %d AND id_folder_srv = %d
					ORDER BY id_msg DESC';
			
			return sprintf($sql, $this->_getMsgIdUidFieldName(true, $account->MailProtocol),
							$this->_settings->DbPrefix, $account->Id, $folder->IdDb);
		}
		
		/**
		 * @param int $accountId
		 * @return string
		 */
		function SelectLastIdMsg($accountId)
		{
			$sql = 'SELECT MAX(id_msg) AS nid_msg
					FROM %sawm_messages
					WHERE id_acct = %d';

			return sprintf($sql, $this->_settings->DbPrefix, $accountId);
		}
		
		/**
		 * @param int $messageId
		 * @param Folder $folder
		 * @param int $accountId
		 * @return string
		 */
		function GetMessageDownloadedFlag($messageId, $folder, $accountId)
		{
			$sql = 'SELECT downloaded FROM %sawm_messages
					WHERE id_acct = %d AND id_folder_db = %d AND id_msg = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $accountId, $folder->IdDb, $messageId);
		}
		
		/**
		 * @param int $user_id
		 * @return string
		 */
		function GetAccountIdsByUserId($user_id)
		{
			$sql = 'SELECT id_acct FROM %sawm_accounts WHERE id_user = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $user_id);
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function DeleteMessagesHeaders($messageIndexSet, $indexAsUid, $folder, $account)
		{
			$sql = 'DELETE FROM %sawm_messages 
					WHERE id_acct = %d AND id_folder_db = %d AND %s '.$this->_inOrNot($messageIndexSet);
			
			return sprintf($sql, $this->_settings->DbPrefix, $account->Id, $folder->IdDb, 
							$this->_getMsgIdUidFieldName($indexAsUid, $account->MailProtocol),
							$this->_quoteUids($messageIndexSet, $indexAsUid, $account->MailProtocol));
		}
		
		/**
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function ClearDbFolder($folder, $account)
		{
			$sql = 'DELETE FROM %sawm_messages 
					WHERE id_acct = %d AND id_folder_db = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $account->Id, $folder->IdDb);
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $fromFolder
		 * @param Folder $toFolder
		 * @param Account $account
		 * @return string
		 */
		function MoveMessages($messageIndexSet, $indexAsUid, $fromFolder, $toFolder, $account)
		{
			$sql = 'UPDATE %sawm_messages
					SET id_folder_db = %d
					WHERE id_acct = %d AND id_folder_db = %d  AND %s '.$this->_inOrNot($messageIndexSet);
			
			return sprintf($sql, $this->_settings->DbPrefix, $toFolder->IdDb,
								$account->Id, $fromFolder->IdDb,
								$this->_getMsgIdUidFieldName($indexAsUid, $account->MailProtocol),
								$this->_quoteUids($messageIndexSet, $indexAsUid, $account->MailProtocol));
								
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $fromFolder
		 * @param Folder $toFolder
		 * @param Account $account
		 * @return string
		 */
		function FullMoveMessages($messageIndexSet, $indexAsUid, $fromFolder, $toFolder, $account)
		{
			$sql = 'UPDATE %sawm_messages
					SET id_folder_db = %d, id_folder_srv = %d
					WHERE id_acct = %d AND id_folder_db = %d  AND %s '.$this->_inOrNot($messageIndexSet);
			
			return sprintf($sql, $this->_settings->DbPrefix, $toFolder->IdDb, $toFolder->IdDb,
								$account->Id, $fromFolder->IdDb,
								$this->_getMsgIdUidFieldName($indexAsUid, $account->MailProtocol),
								$this->_quoteUids($messageIndexSet, $indexAsUid, $account->MailProtocol));
								
		}
		
		function MoveMessageWithUidUpdate($_id, $_uid, $fromFolder, $toFolder)
		{
			$sql = 'UPDATE %sawm_messages
					SET id_folder_db = %d, id_folder_srv = %d, str_uid = %s
					WHERE id_folder_db = %d AND id_msg = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $toFolder->IdDb, $toFolder->IdDb, 
								$this->_escapeString($_uid), $fromFolder->IdDb, $_id);
		}
		
		/**
		 * @param string $name
		 * @param string $email
		 * @param int $idUser
		 * @return string
		 */
		function SelectAddressBookRecordsByNameEmail($name, $email, $idUser)
		{
			$sql = 'SELECT h_email, fullname, b_email, other_email, primary_email
					FROM %sawm_addr_book WHERE deleted = 0 AND id_user = %d AND fullname = %s AND
					(h_email = %s OR b_email = %s OR other_email = %s)';
			
			return sprintf($sql, $this->_settings->DbPrefix, $idUser, 
				$this->_escapeString($name), 
				$this->_escapeString($email), 
				$this->_escapeString($email), 
				$this->_escapeString($email)); 
		}
		
		/**
		 * @param int $idAddress
		 * @return string
		 */
		function SelectAddressBookRecord($idAddress, $idUser)
		{
			$sql = 'SELECT id_addr, str_id, id_user, h_email, fullname,
						notes, use_friendly_nm, h_street, h_city, h_state, h_zip, h_country,
						h_phone, h_fax, h_mobile, h_web, b_email, b_company, b_street, b_city,
						b_state, b_zip, b_country, b_job_title, b_department, b_office, b_phone,
						b_fax, b_web, other_email, primary_email, id_addr_prev, tmp, birthday_day, 
						birthday_month, birthday_year
					FROM %sawm_addr_book WHERE deleted = 0 AND id_addr = %d AND id_user = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $idAddress, $idUser);
		}
		
		/**
		 * @param AddressBookRecord $addressBook
		 * @return string
		 */
		function InsertAddressBookRecord($addressBookRecord)
		{
			$sql = 'INSERT INTO %sawm_addr_book (id_user, h_email, fullname,
									notes, use_friendly_nm, h_street, h_city, h_state, h_zip, h_country,
									h_phone, h_fax, h_mobile, h_web, b_email, b_company, b_street, b_city,
									b_state, b_zip, b_country, b_job_title, b_department, b_office, b_phone,
									b_fax, b_web, other_email, primary_email, id_addr_prev, tmp,
									birthday_day, birthday_month, birthday_year, date_created, date_modified)
					VALUES (%d, %s, %s, %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s,
							%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %s, %s)';
			
			$date = new CDateTime(time());
			$date->SetTimeStampToUtc();
			$now = $this->UpdateDateFormat($date->ToANSI());

			return sprintf($sql, $this->_settings->DbPrefix,
									$addressBookRecord->IdUser,
									$this->_escapeString($addressBookRecord->HomeEmail),
									$this->_escapeString($addressBookRecord->FullName),
									$this->_escapeString($addressBookRecord->Notes),
									$addressBookRecord->UseFriendlyName,
									$this->_escapeString($addressBookRecord->HomeStreet),
									$this->_escapeString($addressBookRecord->HomeCity),
									$this->_escapeString($addressBookRecord->HomeState),
									$this->_escapeString($addressBookRecord->HomeZip),
									$this->_escapeString($addressBookRecord->HomeCountry),
									$this->_escapeString($addressBookRecord->HomePhone),
									$this->_escapeString($addressBookRecord->HomeFax),
									$this->_escapeString($addressBookRecord->HomeMobile),
									$this->_escapeString($addressBookRecord->HomeWeb),
									$this->_escapeString($addressBookRecord->BusinessEmail),
									$this->_escapeString($addressBookRecord->BusinessCompany),
									$this->_escapeString($addressBookRecord->BusinessStreet),
									$this->_escapeString($addressBookRecord->BusinessCity),
									$this->_escapeString($addressBookRecord->BusinessState),
									$this->_escapeString($addressBookRecord->BusinessZip),
									$this->_escapeString($addressBookRecord->BusinessCountry),
									$this->_escapeString($addressBookRecord->BusinessJobTitle),
									$this->_escapeString($addressBookRecord->BusinessDepartment),
									$this->_escapeString($addressBookRecord->BusinessOffice),
									$this->_escapeString($addressBookRecord->BusinessPhone),
									$this->_escapeString($addressBookRecord->BusinessFax),
									$this->_escapeString($addressBookRecord->BusinessWeb),
									$this->_escapeString($addressBookRecord->OtherEmail),
									$addressBookRecord->PrimaryEmail,
									$addressBookRecord->IdPreviousAddress,
									$addressBookRecord->Temp,
									$addressBookRecord->BirthdayDay,
									$addressBookRecord->BirthdayMonth,
									$addressBookRecord->BirthdayYear,
									$now, $now);
		}

		/**
		 * @return string
		 */
		function UpdateContactStrId($contactId, $contactStrId)
		{
			$sql = 'UPDATE %sawm_addr_book SET str_id = %s WHERE deleted = 0 AND id_addr = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $this->_escapeString($contactStrId), $contactId);
		}
		
		/**
		 * @param AddressBookRecord $addressBookRecord
		 * @return string
		 */
		function UpdateAddressBookRecord($addressBookRecord)
		{
			$sql = 'UPDATE %sawm_addr_book
					SET h_email = %s, fullname = %s,
						notes = %s, use_friendly_nm = %d, h_street = %s, h_city = %s, h_state = %s,
						h_zip = %s, h_country = %s, h_phone = %s, h_fax = %s, h_mobile = %s, h_web = %s,
						b_email = %s, b_company = %s, b_street = %s, b_city = %s, b_state = %s,
						b_zip = %s, b_country = %s, b_job_title = %s, b_department = %s, b_office = %s,
						b_phone = %s, b_fax = %s, b_web = %s, other_email = %s,
						primary_email = %d, id_addr_prev = %d, tmp = %d,
						birthday_day = %d, birthday_month = %d, birthday_year = %d,
						date_modified = %s
					WHERE deleted = 0 AND id_addr = %d';

			$date = new CDateTime(time());
			$date->SetTimeStampToUtc();
			$now = $this->UpdateDateFormat($date->ToANSI());

			return sprintf($sql, $this->_settings->DbPrefix,
									$this->_escapeString($addressBookRecord->HomeEmail),
									$this->_escapeString($addressBookRecord->FullName),
									$this->_escapeString($addressBookRecord->Notes),
									$addressBookRecord->UseFriendlyName,
									$this->_escapeString($addressBookRecord->HomeStreet),
									$this->_escapeString($addressBookRecord->HomeCity),
									$this->_escapeString($addressBookRecord->HomeState),
									$this->_escapeString($addressBookRecord->HomeZip),
									$this->_escapeString($addressBookRecord->HomeCountry),
									$this->_escapeString($addressBookRecord->HomePhone),
									$this->_escapeString($addressBookRecord->HomeFax),
									$this->_escapeString($addressBookRecord->HomeMobile),
									$this->_escapeString($addressBookRecord->HomeWeb),
									$this->_escapeString($addressBookRecord->BusinessEmail),
									$this->_escapeString($addressBookRecord->BusinessCompany),
									$this->_escapeString($addressBookRecord->BusinessStreet),
									$this->_escapeString($addressBookRecord->BusinessCity),
									$this->_escapeString($addressBookRecord->BusinessState),
									$this->_escapeString($addressBookRecord->BusinessZip),
									$this->_escapeString($addressBookRecord->BusinessCountry),
									$this->_escapeString($addressBookRecord->BusinessJobTitle),
									$this->_escapeString($addressBookRecord->BusinessDepartment),
									$this->_escapeString($addressBookRecord->BusinessOffice),
									$this->_escapeString($addressBookRecord->BusinessPhone),
									$this->_escapeString($addressBookRecord->BusinessFax),
									$this->_escapeString($addressBookRecord->BusinessWeb),
									$this->_escapeString($addressBookRecord->OtherEmail),
									$addressBookRecord->PrimaryEmail,
									$addressBookRecord->IdPreviousAddress,
									$addressBookRecord->Temp,
									$addressBookRecord->BirthdayDay,
									$addressBookRecord->BirthdayMonth,
									$addressBookRecord->BirthdayYear,
									$now,
									$addressBookRecord->IdAddress);
		}

		/**
		 * @param long $idAddress
		 * @return string
		 */
		function DeleteAddressBookRecord($idAddress)
		{
			$sql = 'UPDATE %sawm_addr_book SET deleted = 1, date_modified = %s WHERE id_addr = %d';

			$date = new CDateTime(time());
			$date->SetTimeStampToUtc();
			$now = $this->UpdateDateFormat($date->ToANSI());

			return sprintf($sql, $this->_settings->DbPrefix, $now, $idAddress);
		}
		
		/**
		 * @param int $idAddress
		 * @return string
		 */
		function DeleteAddressGroup($idGroup)
		{
			$sql = 'DELETE FROM %sawm_addr_groups WHERE id_group = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $idGroup);
		}

		/**
		 * @param int $idGroup
		 * @return string
		 */
		function DeleteAddressGroupsContactsByIdGroup($idGroup)
		{
			$sql = 'DELETE FROM %sawm_addr_groups_contacts WHERE id_group = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $idGroup);
		}
		
		/**
		 * @param long $idAddress
		 * @return string
		 */
		function DeleteAddressGroupsContactsByIdAddress($idAddress)
		{
			$sql = 'DELETE FROM %sawm_addr_groups_contacts WHERE id_addr = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $idAddress);
		}

		/**
		 * @param long $idAddress
		 * @param int $idGroup
		 * @return string
		 */
		function DeleteAddressGroupsContacts($idAddress, $idGroup)
		{
			$sql = 'DELETE FROM %sawm_addr_groups_contacts WHERE id_addr = %d AND id_group = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $idAddress, $idGroup);
		}

		/**
		 * @param int $idAddress
		 * @return string
		 */
		function SelectAddressGroupContact($idAddress)
		{
			$sql = 'SELECT groups.id_group AS group_id, group_nm
					FROM %sawm_addr_groups AS groups
					INNER JOIN %sawm_addr_groups_contacts AS grcont ON groups.id_group = grcont.id_group
					WHERE grcont.id_addr = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $this->_settings->DbPrefix, $idAddress);
		}
		
		/**
		 * @param int $idGroup
		 * @return string
		 */
		function SelectAddressGroupContacts($idGroup)
		{
			$sql = 'SELECT book.id_addr AS id, fullname,
						CASE primary_email
							WHEN %s THEN h_email
							WHEN %s THEN b_email
							WHEN %s THEN other_email
						END AS email, book.use_friendly_nm AS usefriendlyname
					FROM %sawm_addr_book AS book
					INNER JOIN %sawm_addr_groups_contacts AS grcont ON book.id_addr = grcont.id_addr
					WHERE deleted = 0 AND id_group = %d';
			
			return sprintf($sql, PRIMARYEMAIL_Home, PRIMARYEMAIL_Business, PRIMARYEMAIL_Other,
								$this->_settings->DbPrefix, $this->_settings->DbPrefix, $idGroup);
		}
		
		/**
		 * @param int $idGroup
		 * @return string
		 */
		function SelectGroupById($idGroup)
		{
			$sql = 'SELECT id_user, group_str_id, group_nm, email, company, street, city, state, zip, country, phone, fax, web, organization
					FROM %sawm_addr_groups
					WHERE id_group = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $idGroup);			
		}

		/**
		 * @param int $idAddress
		 * @param int $idGroup
		 * @return string
		 */
		function InsertAddressGroupContact($idAddress, $idGroup)
		{
			$sql = 'INSERT INTO %sawm_addr_groups_contacts(id_addr, id_group) VALUES (%d, %d)';
			return sprintf($sql, $this->_settings->DbPrefix, $idAddress, $idGroup);
		}
		
		/**
		 * @param AddressGroup $group
		 * @return string
		 */
		function InsertAddressGroup($group)
		{
			$sql = 'INSERT INTO %sawm_addr_groups (id_user, group_nm, email, company, street, city, state, zip, country, phone, fax, web, organization) 
					VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d)';
			return sprintf($sql, $this->_settings->DbPrefix, $group->IdUser,
																$this->_escapeString($group->Name),
																$this->_escapeString($group->Email),
																$this->_escapeString($group->Company),
																$this->_escapeString($group->Street),
																$this->_escapeString($group->City),
																$this->_escapeString($group->State),
																$this->_escapeString($group->Zip),
																$this->_escapeString($group->Country),
																$this->_escapeString($group->Phone),
																$this->_escapeString($group->Fax),
																$this->_escapeString($group->Web),
																$group->IsOrganization);
		}

		/**
		 * @param AddressGroup $group
		 * @return string
		 */
		function UpdateAddressGroup($group)
		{
			$sql = 'UPDATE %sawm_addr_groups
					SET group_nm = %s, email = %s, company = %s, street = %s, city = %s, state = %s,
					zip = %s, country = %s, phone = %s, fax = %s, web = %s, organization = %d
					WHERE id_user = %d AND id_group = %d';

			return sprintf($sql, $this->_settings->DbPrefix,
								$this->_escapeString($group->Name),
								$this->_escapeString($group->Email),
								$this->_escapeString($group->Company),
								$this->_escapeString($group->Street),
								$this->_escapeString($group->City),
								$this->_escapeString($group->State),
								$this->_escapeString($group->Zip),
								$this->_escapeString($group->Country),
								$this->_escapeString($group->Phone),
								$this->_escapeString($group->Fax),
								$this->_escapeString($group->Web),
								(int) $group->IsOrganization,
								$group->IdUser, $group->Id);
		}
		
		/**
		 * @return string
		 */
		function UpdateGroupStrId($groupId, $groupStrId)
		{
			$sql = 'UPDATE %sawm_addr_groups SET group_str_id = %s WHERE id_group = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $this->_escapeString($groupStrId), $groupId);
		}
		
		/**
		 * @param int $idGroup
		 * @return string
		 */
		function SelectAddressContactsCount($lookForType, $idUser, $condition = null, $idGroup = null)
		{
			$temp = '';
			if ($condition) $condition = ($lookForType == 1) ? $this->_escapeString($condition.'%') : $this->_escapeString('%'.$condition.'%');
			if ($idGroup && $idGroup > -1) $temp = 
				' INNER JOIN '.$this->_settings->DbPrefix.'awm_addr_groups_contacts AS gr_cont ON (gr_cont.id_addr = abook.id_addr AND
								id_group = '.$idGroup.')';
			
			$sql = 'SELECT COUNT(abook.id_addr) AS contacts_count
					FROM %sawm_addr_book AS abook
					%s
					WHERE abook.deleted = 0 AND abook.id_user = %d';
			
			$sql = sprintf($sql, $this->_settings->DbPrefix, $temp, $idUser);
			if ($condition) $sql .= ' AND (fullname LIKE '.$condition.' OR h_email LIKE '.$condition.' OR b_email LIKE '.$condition.' OR other_email LIKE '.$condition.')';
			
			return $sql;
		}
		
		/**
		 * @param int $idGroup
		 * @return string
		 */
		function SelectAddressGroupsCount($lookForType, $idUser, $condition = null)
		{
			if ($condition) $condition = ($lookForType == 1) ? $this->_escapeString($condition.'%') : $this->_escapeString('%'.$condition.'%');
			$sql = 'SELECT COUNT(id_group) AS groups_count
					FROM %sawm_addr_groups
					WHERE id_user = %d';
			
			$sql = sprintf($sql, $this->_settings->DbPrefix, $idUser);
			
			if ($condition) $sql .= ' AND group_nm LIKE '.$condition;
			
			return $sql;
		}

		function SelectAccountsCountForEmailSharing($lookForField, $domainId = null)
		{
			$condition = $this->_escapeString($lookForField.'%');

			$where = (null !== $domainId)
				? 'WHERE id_domain = '.((int) $domainId).' AND (email LIKE '.$condition.' OR friendly_nm LIKE '.$condition.')'
				: 'WHERE email LIKE '.$condition.' OR friendly_nm LIKE '.$condition;

			$sql = 'SELECT COUNT(id_acct) AS contacts_count
					FROM %sawm_accounts
					%s';

			return sprintf($sql, $this->_settings->DbPrefix, $where);
		}
		
		
		/**
		 * @param int $idGroup
		 * @return string
		 */
		function SelectAddressGroupName($idGroup)
		{
			$sql = 'SELECT group_nm FROM %sawm_addr_groups WHERE id_group = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $idGroup);
		}

		/**
		 * @param int $idUser
		 * @return string
		 */
		function SelectUserAddressGroupNames($idUser)
		{
			$sql = 'SELECT id_group, group_nm FROM %sawm_addr_groups WHERE id_user = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $idUser);
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @param int $flags
		 * @param short $action
		 * @param Account $account
		 * @return string
		 */
		function SetMessagesFlags($messageIndexSet, $indexAsUid, $folder, $flags, $action, $account)
		{
			switch ($action)
			{
				case ACTION_Set:
					$sql = 'UPDATE %sawm_messages
							SET flags = (flags | %d) & ~768'; /* remove non-Imap flags */

					if (($flags & MESSAGEFLAGS_Seen) == MESSAGEFLAGS_Seen)
					{
						$sql .= ', seen = 1';
					}
					if (($flags & MESSAGEFLAGS_Flagged) == MESSAGEFLAGS_Flagged)
					{
						$sql .= ', flagged = 1';
					}
					if (($flags & MESSAGEFLAGS_Deleted) == MESSAGEFLAGS_Deleted)
					{
						$sql .= ', deleted = 1';
					}
					if (($flags & MESSAGEFLAGS_Answered) == MESSAGEFLAGS_Answered)
					{
						$sql .= ', replied = 1';
					}
					if (($flags & MESSAGEFLAGS_Forwarded) == MESSAGEFLAGS_Forwarded)
					{
						$sql .= ', forwarded = 1';
					}
					if (($flags & MESSAGEFLAGS_Grayed) == MESSAGEFLAGS_Grayed)
					{
						$sql .= ', grayed = 1';
					}
					break;

				case ACTION_Remove:
					$sql = 'UPDATE %sawm_messages
							SET flags = (flags & ~%d) & ~768'; /* remove non-Imap flags */
					
					if (($flags & MESSAGEFLAGS_Seen) == MESSAGEFLAGS_Seen)
					{
						$sql .= ', seen = 0';
					}
					if (($flags & MESSAGEFLAGS_Flagged) == MESSAGEFLAGS_Flagged)
					{
						$sql .= ', flagged = 0';
					}
					if (($flags & MESSAGEFLAGS_Deleted) == MESSAGEFLAGS_Deleted)
					{
						$sql .= ', deleted = 0';
					}
					if (($flags & MESSAGEFLAGS_Answered) == MESSAGEFLAGS_Answered)
					{
						$sql .= ', replied = 0';
					}
					if (($flags & MESSAGEFLAGS_Forwarded) == MESSAGEFLAGS_Forwarded)
					{
						$sql .= ', forwarded = 0';
					}
					if (($flags & MESSAGEFLAGS_Grayed) == MESSAGEFLAGS_Grayed)
					{
						$sql .= ', grayed = 0';
					}
					break;
			}
			
			if ($messageIndexSet != null)
			{
				$sql .= ' WHERE id_acct = %d AND id_folder_db = %d AND %s '.$this->_inOrNot($messageIndexSet);
				return sprintf($sql, $this->_settings->DbPrefix, $flags, $account->Id, $folder->IdDb,
								$this->_getMsgIdUidFieldName($indexAsUid, $account->MailProtocol),
								$this->_quoteUids($messageIndexSet, $indexAsUid, $account->MailProtocol));
			}
			
			$sql .= ' WHERE id_acct = %d AND id_folder_db = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $flags, $account->Id, $folder->IdDb);

		}
		
		/**
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function SelectAllDeletedMsgId($folder, $account, $pop3EmptyTrash = false)
		{
			$sql = 'SELECT id_msg FROM %sawm_messages
							WHERE id_acct = -%d AND id_folder_db = %d AND downloaded = 1';
			
			switch ($account->MailProtocol)
			{
				case MAILPROTOCOL_POP3:
					$sql = 'SELECT id_msg FROM %sawm_messages
							WHERE id_acct = %d AND id_folder_db = %d AND downloaded = 1';
					break;
				case MAILPROTOCOL_WMSERVER:
					$sql = 'SELECT id_msg FROM %sawm_messages
								WHERE id_acct = %d AND id_folder_db = %d AND
									deleted = 1 AND downloaded = 1';
					break;
					
				case MAILPROTOCOL_IMAP4:
					if ($pop3EmptyTrash)
					{
						$sql = 'SELECT id_msg FROM %sawm_messages
								WHERE id_acct = %d AND id_folder_db = %d AND downloaded = 1';
					}
					else
					{
						$sql = 'SELECT id_msg FROM %sawm_messages
								WHERE id_acct = %d AND id_folder_db = %d AND
									deleted = 1 AND downloaded = 1';
					}
					break;
			}
			return sprintf($sql, $this->_settings->DbPrefix, $account->Id, $folder->IdDb);
		}

		/**
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function PurgeAllMessageHeaders($folder, $account, $pop3EmptyTrash = false)
		{
			$sql = 'DELETE FROM %sawm_messages WHERE id_acct = -%d AND id_folder_db = %d';
			switch ($account->MailProtocol)
			{
				case MAILPROTOCOL_POP3:
				case MAILPROTOCOL_WMSERVER:
					$sql = 'DELETE FROM %sawm_messages WHERE id_acct = %d AND id_folder_db = %d';
					break;
					
				case MAILPROTOCOL_IMAP4:
					if ($pop3EmptyTrash)
					{
						$sql = 'DELETE FROM %sawm_messages WHERE id_acct = %d AND id_folder_db = %d';
					}
					else
					{
						$sql = 'DELETE FROM %sawm_messages WHERE id_acct = %d AND id_folder_db = %d AND deleted = 1';
					}
					break;
			}
			
			return sprintf($sql, $this->_settings->DbPrefix, $account->Id, $folder->IdDb);
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Account $account
		 * @return string
		 */
		function SelectDownloadedMessagesIdSet($messageIndexSet, $indexAsUid, $account)
		{
			$sql = 'SELECT id_msg FROM %sawm_messages
					WHERE id_acct = %d AND downloaded = 1 AND %s '.$this->_inOrNot($messageIndexSet);
			
			return sprintf($sql, $this->_settings->DbPrefix, $account->Id,
									$this->_getMsgIdUidFieldName($indexAsUid, $account->MailProtocol),
									$this->_quoteUids($messageIndexSet, $indexAsUid, $account->MailProtocol));
		}
		
		/**
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function SelectAllMessagesUidSetByFolder($folder, $account)
		{
			$sql = 'SELECT %s AS uid FROM %sawm_messages
					WHERE id_acct = %d AND id_folder_db = %d';
			return sprintf($sql, $this->_getMsgIdUidFieldName(true, $account->MailProtocol),
									$this->_settings->DbPrefix, $account->Id, $folder->IdDb);
		}
		
		/**
		 * @param int $accountId
		 * @return string
		 */
		function SelectFilters($accountId)
		{
			$sql = 'SELECT id_filter, field, condition, filter, action, id_folder, applied
					FROM %sawm_filters
					WHERE id_acct = %d
					ORDER BY action';
			return sprintf($sql, $this->_settings->DbPrefix, $accountId);
		}
		
		/**
		 * @param Filter $filter
		 * @return string
		 */
		function InsertFilter($filter)
		{
			$sql = 'INSERT INTO %sawm_filters (id_acct, field, condition, filter, action, id_folder, applied)
					VALUES (%d, %d, %d, %s, %d, %d, %d)';
					
			return sprintf($sql, $this->_settings->DbPrefix, $filter->IdAcct,
									$filter->Field, $filter->Condition,
									$this->_escapeString($filter->Filter),
									$filter->Action, $filter->IdFolder, $filter->Applied);
		}
		
		/**
		 * @param Filter $filter
		 * @return string
		 */
		function UpdateFilter($filter)
		{
			$sql = 'UPDATE %sawm_filters SET
						field = %d, condition = %d, filter = %s, action = %d,
						id_folder = %d, applied= %d
					WHERE id_filter = %d AND id_acct = %d';

			return sprintf($sql, $this->_settings->DbPrefix, $filter->Field,
									$filter->Condition,	$this->_escapeString($filter->Filter),
									$filter->Action, $filter->IdFolder, $filter->Applied,
									$filter->Id, $filter->IdAcct);			
		}

		/**
		 * @param int $filterId
		 * @param int $accountId
		 * @return string
		 */
		function DeleteFilter($filterId, $accountId)
		{
			$sql = 'DELETE FROM %sawm_filters
					WHERE id_filter = %d AND id_acct = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $filterId, $accountId);
		}
		
		/**
		 * @param int $folderId
		 * @param int $accountId
		 * @return string
		 */
		function DeleteFolderFilters($folderId, $accountId)
		{
			$sql = 'DELETE FROM %sawm_filters
					WHERE id_folder = %d AND id_acct = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $folderId, $accountId);
		}

		/**
		 * @param int $accountId
		 * @return string
		 */
		function SelectReadsRecords($accountId)
		{
			$sql = 'SELECT str_uid AS uid
					FROM %sawm_reads
					WHERE id_acct = %d';

			return sprintf($sql, $this->_settings->DbPrefix, $accountId);
		}
		
		/**
		 * @param int $accountId
		 * @param string $pop3Uid
		 * @return string
		 */
		function InsertReadsRecord($accountId, $pop3Uid)
		{
			$sql = 'INSERT INTO %sawm_reads (id_acct, str_uid, tmp) VALUES(%d, %s, 0)';
			return sprintf($sql, $this->_settings->DbPrefix, $accountId, $this->_escapeString($pop3Uid));
		}
		
		/**
		 * @param int $accountId
		 * @return string
		 */
		function DeleteReadsRecords($accountId)
		{
			$sql = 'DELETE FROM %sawm_reads WHERE id_acct = %d';

			return sprintf($sql, $this->_settings->DbPrefix, $accountId);
		}
		
		/**
		 * @param int $accountId
		 * @param array $uids
		 * @return string
		 */
		function DeleteReadsRecordsByUid($accountId, $uids)
		{
			$uids = array_map(array(&$this, '_escapeString'), $uids);
			$str_uids = implode(',', $uids);
			$sql = 'DELETE FROM %sawm_reads WHERE id_acct = %d AND str_uid '.$this->_inOrNot($uids);

			return sprintf($sql, $this->_settings->DbPrefix, $accountId, $str_uids);
		}

		
		/**
		 * @param int $accountId
		 * @return string
		 */
		function CountMailboxSize($accountId)
		{
			$sql = 'SELECT SUM(size) AS mailbox_size
					FROM %sawm_messages WHERE id_acct = %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $accountId);
		}
		
		/**
		 * @param int $size
		 * @param int $accountId
		 * @return string
		 */
		function UpdateMailboxSize($size, $accountId)
		{
			$sql = 'UPDATE %sawm_accounts
					SET mailbox_size = %d
					WHERE id_acct = %d AND mailing_list = 0';
			
			return sprintf($sql, $this->_settings->DbPrefix, $size, $accountId);
		}

		/**
		 * @param int $accountId
		 * @return string
		 */
		function SelectMailboxesSize($userId)
		{
			$sql = 'SELECT SUM(mailbox_size) AS mailboxes_size
					FROM %sawm_accounts WHERE id_user = %d AND mailing_list = 0';
			
			return sprintf($sql, $this->_settings->DbPrefix, $userId);
		}
		
		/**
		 * @param int $userId
		 */
		function GetAccountListByUserId($userId)
		{
			$sql = 'SELECT id_acct FROM %sawm_accounts WHERE id_user = %d AND mailing_list = 0';

			return sprintf($sql, $this->_settings->DbPrefix, $userId);
		}
		
		/**
		 * @param int $userId
		 */
		function GetFullAccountListByUserId($userId)
		{
			$sql = 'SELECT id_acct, def_acct FROM %sawm_accounts WHERE id_user = %d AND mailing_list = 0';

			return sprintf($sql, $this->_settings->DbPrefix, $userId);
		}
		
		/**
		 * @param string $fieldName
		 * @return string
		 */
		function GetDateFormat($fieldName)
		{
			return CDateTime::GetMySqlDateFormat($fieldName);
		}
		
		/**
		 * @param array $intUids
		 * @param Folder $folder
		 * @return string
		 */
		function LoadMessageHeadersByIntUids($intUids, $folder, $account)
		{
			$sql = 'SELECT id_msg, int_uid AS uid, id_folder_db, from_msg, to_msg, cc_msg,
							bcc_msg, subject, %s AS nmsg_date, size, priority, x_spam,
							attachments, seen, flagged, deleted, replied, forwarded, grayed, charset, sensitivity
					FROM %sawm_messages
					WHERE id_acct = %d AND id_folder_db = %d 
						AND int_uid '.$this->_inOrNot($intUids);

			$str_intUids = implode(',', $intUids);

			return sprintf($sql, CDateTime::GetMySqlDateFormat('msg_date'),
								$this->_settings->DbPrefix, $account->Id, $folder->IdDb, $str_intUids);
		}

		/**
		 * @param string $condition
		 * @param FolderCollection $folders
		 * @param bool $inHeadersOnly
		 * @param Account $account
		 * @return string
		 */
		function SearchMessagesCount($condition, $folders, $inHeadersOnly, $account)
		{
			$foldersId = array();
			$_foldersKeys = array_keys($folders->Instance());
			foreach ($_foldersKeys as $key)
			{
				$folder =& $folders->Get($key);
				if (!$folder->Hide)
				{
					$foldersId[] = $folder->IdDb;
				}
				unset($folder);
			}
			unset($folders);
			
	  		$filter = '';
	  		$asc = true;
	  		
	  		$this->_setSortOrder($account->DefaultOrder, $filter, $asc);
			
	  		$condition = $this->_escapeString('%'.$condition.'%');
	  		
	  		$str_foldersId = implode(',', $foldersId);
	  		
			if ($inHeadersOnly)
			{
				$sql = 'SELECT COUNT(id) AS msg_count
						FROM %sawm_messages
						WHERE id_acct = %d AND id_folder_db '.$this->_inOrNot($foldersId).' AND	
							(from_msg LIKE %s OR to_msg LIKE %s OR cc_msg LIKE %s OR bcc_msg
							LIKE %s OR subject LIKE %s)';
				
				return sprintf($sql, $this->_settings->DbPrefix,
									$account->Id, $str_foldersId,
									$condition, $condition, $condition, $condition, $condition);
			}
			else
			{
				$sql = 'SELECT COUNT(id) AS msg_count
						FROM %sawm_messages
						WHERE id_acct = %d AND id_folder_db '.$this->_inOrNot($foldersId).' AND	
							(from_msg LIKE %s OR to_msg LIKE %s OR cc_msg LIKE %s OR bcc_msg
							LIKE %s OR subject LIKE %s OR body_text LIKE %s)';
				
				return sprintf($sql, $this->_settings->DbPrefix,
									$account->Id, $str_foldersId,
									$condition, $condition, $condition, $condition, $condition, $condition);
			}
		}
		
		/**
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function SelectDeletedMessagesId($folder, $account, $pop3EmptyTrash = false)
		{
			$sql = 'SELECT id_msg FROM %sawm_messages
							WHERE id_acct = -%d AND id_folder_db = %d';
			
			switch ($account->MailProtocol)
			{
				
				case MAILPROTOCOL_POP3:
					$sql = 'SELECT id_msg FROM %sawm_messages
							WHERE id_acct = %d AND id_folder_db = %d';
					break;
					
				case MAILPROTOCOL_WMSERVER:
					$sql = 'SELECT id_msg FROM %sawm_messages
							WHERE id_acct = %d AND id_folder_db = %d AND deleted = 1';
					break;
					
				case MAILPROTOCOL_IMAP4:
					if ($pop3EmptyTrash)
					{
						$sql = 'SELECT id_msg FROM %sawm_messages
								WHERE id_acct = %d AND id_folder_db = %d';
					}
					else
					{
						$sql = 'SELECT id_msg FROM %sawm_messages
								WHERE id_acct = %d AND id_folder_db = %d AND deleted = 1';
					}
					break;
			}
			
			return sprintf($sql, $this->_settings->DbPrefix, $account->Id, $folder->IdDb);
		}
		
		/**
		 * @param int $accountId
		 * @param array $msgIds
		 * @return string
		 */
		function PurgeAllMessagesBody($msgIds, $accountId)
		{
			$sql = 'DELETE FROM %sawm_messages_body WHERE id_acct = %d AND id_msg '.$this->_inOrNot($msgIds);
			
			return sprintf($sql, $this->_settings->DbPrefix, $accountId, implode(',', $msgIds));
		}
		
		/**
		 * @param Array $contactIds
		 * @param Account $account
		 */
		function LoadContactsById($contactIds, $account)
		{
			$sql = 'SELECT id_addr AS id, fullname AS name,
						CASE primary_email
							WHEN %s THEN h_email
							WHEN %s THEN b_email
							WHEN %s THEN other_email
						END AS email, 0 AS is_group, use_frequency AS frequency, use_friendly_nm AS usefriendlyname
					FROM %sawm_addr_book
					WHERE deleted = 0 AND id_user = %d AND id_addr '.$this->_inOrNot($contactIds);
			
			return sprintf($sql, PRIMARYEMAIL_Home, PRIMARYEMAIL_Business, PRIMARYEMAIL_Other,
								$this->_settings->DbPrefix, $account->IdUser,
								implode(',', $contactIds));
		}
		
		/**
		 * @param string $conditon
		 * @param int $groupId
		 * @param Account $account
		 */
		function SearchContactsAndGroupsCount($condition, $groupId, $account)
		{
	  		$condition = $this->_escapeString('%'.$condition.'%');
			
			if ($groupId == -1)
			{
				$sql = 'SELECT COUNT(id_addr) AS countId
						FROM %sawm_addr_book
						WHERE deleted = 0 AND id_user = %d AND (fullname LIKE %s OR h_email LIKE %s OR b_email LIKE %s OR other_email LIKE %s)
						UNION
						SELECT count(id_group) AS countId
						FROM %sawm_addr_groups
						WHERE id_user = %d AND group_nm LIKE %s
						';
				
				return sprintf($sql, $this->_settings->DbPrefix, $account->IdUser, $condition, $condition, $condition, $condition,
									$this->_settings->DbPrefix, $account->IdUser, $condition);
			}
			else
			{
				$sql = 'SELECT COUNT(id_addr) AS countId
						FROM %sawm_addr_book
						WHERE deleted = 0 AND id_user = %d AND id_group = %d AND (fullname LIKE %s OR h_email LIKE %s OR b_email LIKE %s OR other_email LIKE %s)';
				
				return sprintf($sql, $this->_settings->DbPrefix, $account->IdUser, $groupId, $condition, $condition, $condition, $condition);
			}
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function SelectForCreateFolder($folder)
		{
			$sql = 'SELECT MAX(fld_order) AS norder
					FROM %sawm_folders
					WHERE id_acct = %d AND id_parent = %d';
       
			return sprintf($sql, $this->_settings->DbPrefix, $folder->IdAcct, $folder->IdParent);
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

			return sprintf($sql, $this->_settings->DbPrefix, $folder->IdAcct,
									$folder->IdParent, $folder->Type,
									$this->_escapeString($folder->Name.'#'),
									$this->_escapeString($folder->FullName.'#'),
									$folder->SyncType, $folder->Hide,
									$folder->FolderOrder);
		}

		/**
		 * @param int	$id_user
		 * @return string
		 */
		function GetAUserDeleted($id_user)
		{
			$sql = 'SELECT deleted FROM %sa_users WHERE id_user = %d';
			return sprintf($sql, $this->_settings->DbPrefix, $id_user);
		}

		/**
		 * @return	string
		 */
		function AllUserCount()
		{
			$sql = 'SELECT COUNT(id_user) AS user_cnt FROM %sa_users WHERE deleted = 0';
			return sprintf($sql, $this->_settings->DbPrefix);
		}

		function SessionRead($hash, $time)
		{
			$sql = 'SELECT sess_data FROM %sa_sessions WHERE sess_hash = %s AND sess_time > %d';
			return sprintf($sql, $this->_settings->DbPrefix, $this->_escapeString($hash), $time);
		}

		function SessionInsertNew($hash)
		{
			$sql = 'INSERT INTO %sa_sessions (sess_hash, sess_time, sess_data) VALUES(%s, %d, %s)';
			return sprintf($sql, $this->_settings->DbPrefix, $this->_escapeString($hash), time(), $this->_escapeString(''));
		}

		function SessionUpdate($hash, $data)
		{
			$sql = 'UPDATE %sa_sessions SET sess_data = %s, sess_time = %d WHERE sess_hash = %s';
			return sprintf($sql, $this->_settings->DbPrefix, $this->_escapeString($data), time(), $this->_escapeString($hash));
		}

		function SessionDestroy($hash)
		{
			$sql = 'DELETE FROM %sa_sessions WHERE sess_hash = %s';
			return sprintf($sql, $this->_settings->DbPrefix, $this->_escapeString($hash));
		}

		function SessionGC($time)
		{
			$sql = 'DELETE FROM %sa_session WHERE sess_time < %d';
			return sprintf($sql, $this->_settings->DbPrefix, $time);
		}

		/**
		 * @param string $email
		 * @param string
		 */
		function CheckCountOfUserAccounts($email)
		{
			$sql = "SELECT id_user FROM %sawm_accounts WHERE email='%s'";
			return sprintf($sql, $this->_settings->DbPrefix, $email);
		}

		/**
		 *
		 * @return <type> 
		 */
		function SelectLastFunabolCronRun()
		{
			$sql = "
				SELECT
					run_date
				FROM
					%sacal_awm_fnbl_runs
				ORDER BY
					id_run DESC
				LIMIT 1
			";
			return sprintf($sql, $this->_settings->DbPrefix);
		}

		function WriteLastFunambolCronRun( $date )
		{
			$sql = "
				INSERT INTO
					%sacal_awm_fnbl_runs
				(
					run_date
				) VALUES (
					%s
				)
			";
			return sprintf($sql, $this->_settings->DbPrefix,$this->_escapeString($date));
		}
	}
		
	class MySqlCommandCreator extends CommandCreator
	{
		function MySqlCommandCreator($settings = null)
		{
			CommandCreator::CommandCreator(QUOTE_ESCAPE, $settings);
		}
		
		/**
		 * @access protected
		 * @param string $bin
		 * @return string
		 */
		function _escapeBin($bin)
		{
			return function_exists('mysql_real_escape_string')
				? '\''.@mysql_real_escape_string($bin).'\'' 
				: '\''.addslashes($bin).'\'';
		}
		
		/**
		 * @param string $fieldName
		 * @return string
		 */
		function GetDateFormat($fieldName)
		{
			return CDateTime::GetMySqlDateFormat($fieldName);
		}

		function UpdateDateFormat($fieldValue)
		{
			return $this->_escapeString($fieldValue);
		}
		
		/**
		 * @param Filter $filter
		 * @return string
		 */
		function InsertFilter($filter)
		{
			$sql = 'INSERT INTO %sawm_filters (id_acct, `field`, `condition`, filter, `action`, id_folder, applied)
					VALUES (%d, %d, %d, %s, %d, %d, %d)';
					
			return sprintf($sql, $this->_settings->DbPrefix, $filter->IdAcct,
									$filter->Field, $filter->Condition,
									$this->_escapeString($filter->Filter),
									$filter->Action, $filter->IdFolder, $filter->Applied);
		}
		
		/**
		 * @param Filter $filter
		 * @return string
		 */
		function UpdateFilter($filter)
		{
			$sql = 'UPDATE %sawm_filters SET
						`field` = %d, `condition` = %d, filter = %s, `action` = %d, `applied` = %d,	id_folder = %d
					WHERE id_filter = %d AND id_acct = %d';

			return sprintf($sql, $this->_settings->DbPrefix, $filter->Field,
									$filter->Condition,	$this->_escapeString($filter->Filter),
									$filter->Action, $filter->Applied, $filter->IdFolder,
									$filter->Id, $filter->IdAcct);			
		}
		
		/**
		 * @param string $accountId
		 * @return string
		 */
		function GetFolders($accountId)
		{

			$sql = 'SELECT p.id_folder, p.id_parent, p.type, p.name, p.full_path, p.sync_type, p.hide, p.fld_order,
							COUNT(messages.id) AS message_count, COUNT(messages_unread.seen) AS unread_message_count,
							SUM(messages.size) AS folder_size, MAX(folder_level) AS level
					FROM (%sawm_folders as n, %sawm_folders_tree as t, %sawm_folders as p)
					LEFT OUTER JOIN %sawm_messages AS messages ON p.id_folder = messages.id_folder_db
					LEFT OUTER JOIN %sawm_messages AS messages_unread ON
							p.id_folder = messages_unread.id_folder_db AND 
							messages.id = messages_unread.id AND messages_unread.seen = 0
					WHERE n.id_parent = -1
					     AND n.id_folder = t.id_parent
					     AND t.id_folder = p.id_folder
					     AND p.id_acct = %d
					GROUP BY p.id_folder
					ORDER BY p.fld_order';			
			
			return sprintf($sql, $this->_settings->DbPrefix, $this->_settings->DbPrefix, $this->_settings->DbPrefix,
									$this->_settings->DbPrefix, $this->_settings->DbPrefix, $accountId);
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function SelectSubFoldersId($folder)
		{
			$sql = 'SELECT c.id_folder
					FROM (%sawm_folders AS n, %sawm_folders_tree AS t, %sawm_folders AS c)
					WHERE n.id_folder = %d AND n.id_folder = t.id_parent AND t.id_folder = c.id_folder';

			return sprintf($sql, $this->_settings->DbPrefix, $this->_settings->DbPrefix, 
									$this->_settings->DbPrefix, $folder->IdDb);
		}
		
		/**
		 * @param int $pageNumber
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function LoadMessageHeaders($pageNumber, $folder, $account)
		{
	  		$filter = '';
	  		$asc = true;
	  		
	  		$this->_setSortOrder($account->DefaultOrder, $filter, $asc);
	  		
			/* read messages from db */
			$sql = 'SELECT id_msg, %s AS uid, id_folder_db, from_msg, to_msg, cc_msg,
							bcc_msg, subject, %s AS nmsg_date, size, priority, x_spam,
							attachments, seen, flagged, deleted, replied, forwarded, grayed, charset, sensitivity
					FROM %sawm_messages
					WHERE id_acct = %d AND id_folder_db = %d
					ORDER BY %s %s
					LIMIT %d, %d';

			return sprintf($sql, $this->_getMsgIdUidFieldName(true, $account->MailProtocol),
								CDateTime::GetMySqlDateFormat('msg_date'),
								$this->_settings->DbPrefix,
								$account->Id, $folder->IdDb,
								$filter, ($asc)?'ASC':'DESC',
								($pageNumber - 1) * $account->MailsPerPage, $account->MailsPerPage);
		}

		function SearchMessagesUids($condition, $folder, $account)
		{
			$filter = '';
	  		$asc = true;

	  		$this->_setSortOrder($account->DefaultOrder, $filter, $asc);

			$sql = 'SELECT %s AS uid FROM %sawm_messages
						WHERE id_acct = %d AND id_folder_db = %d
						ORDER BY %s %s';

			return sprintf($sql, $this->_getMsgIdUidFieldName(true, $account->MailProtocol),
								$this->_settings->DbPrefix,
								$account->Id, $folder->IdDb,
								$filter, ($asc) ? 'ASC' : 'DESC');
		}
		
		/**
		 * @param int $pageNumber
		 * @param string $condition
		 * @param FolderCollection $folders
		 * @param bool $inHeadersOnly
		 * @param Account $account
		 * @return string
		 */
		function SearchMessages($pageNumber, $condition, $folders, $inHeadersOnly, $account)
		{
			$foldersId = array();
			$_foldersKeys = array_keys($folders->Instance());
			foreach ($_foldersKeys as $key)
			{
				$folder =& $folders->Get($key);
				if (!$folder->Hide)
				{
					$foldersId[] = $folder->IdDb;
				}
				unset($folder);
			}
			unset($folders, $_foldersKeys);
			
	  		$filter = '';
	  		$asc = true;
	  		
	  		$this->_setSortOrder($account->DefaultOrder, $filter, $asc);
			
	  		$condition = $this->_escapeString('%'.$condition.'%');
	  		$str_foldersId = implode(',', $foldersId);
	  		
			if ($inHeadersOnly)
			{
				$sql = 'SELECT id_msg, %s AS uid, id_folder_db, from_msg, to_msg, cc_msg,
							bcc_msg, subject, %s AS nmsg_date, size, priority, x_spam,
							attachments, seen, flagged, deleted, replied, forwarded, grayed, charset, sensitivity
						FROM %sawm_messages
						WHERE id_acct = %d AND id_folder_db '.$this->_inOrNot($foldersId).' AND	
							(from_msg LIKE %s OR to_msg LIKE %s OR cc_msg LIKE %s OR bcc_msg
							LIKE %s OR subject LIKE %s)
						ORDER BY %s %s
						LIMIT %d, %d';
				
				return sprintf($sql, $this->_getMsgIdUidFieldName(true, $account->MailProtocol),
									CDateTime::GetMySqlDateFormat('msg_date'),
									$this->_settings->DbPrefix,
									$account->Id, $str_foldersId,
									$condition, $condition, $condition, $condition, $condition,
									$filter, ($asc)?'ASC':'DESC',
									($pageNumber - 1) * $account->MailsPerPage, $account->MailsPerPage);
			}
			else
			{
				$sql = 'SELECT id_msg, %s AS uid, id_folder_db, from_msg, to_msg, cc_msg,
							bcc_msg, subject, %s AS nmsg_date, size, priority, x_spam,
							attachments, seen, flagged, deleted, replied, forwarded, grayed, charset, sensitivity
						FROM %sawm_messages
						WHERE id_acct = %d AND id_folder_db '.$this->_inOrNot($foldersId).' AND	
							(from_msg LIKE %s OR to_msg LIKE %s OR cc_msg LIKE %s OR bcc_msg
							LIKE %s OR subject LIKE %s OR body_text LIKE %s)
						ORDER BY %s %s
						LIMIT %d, %d';
				
				return sprintf($sql, $this->_getMsgIdUidFieldName(true, $account->MailProtocol),
									CDateTime::GetMySqlDateFormat('msg_date'),
									$this->_settings->DbPrefix,
									$account->Id, $str_foldersId,
									$condition, $condition, $condition, $condition, $condition, $condition,
									$filter, ($asc)?'ASC':'DESC',
									($pageNumber - 1) * $account->MailsPerPage, $account->MailsPerPage);
			}
		}
		
		/**
		 * @param int $accountId
		 * @return string
		 */
		function SelectFilters($accountId)
		{
			$sql = 'SELECT id_filter, `field`, `condition`, `filter`, `action`, id_folder, `applied`
					FROM `%sawm_filters`
					WHERE id_acct = %d
					ORDER BY `action`';
			return sprintf($sql, $this->_settings->DbPrefix, $accountId);
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function DeleteMessagesBody($messageIndexSet, $indexAsUid, $folder, $account)
		{
			$sql = 'DELETE %sawm_messages_body
					FROM %sawm_messages_body, %sawm_messages
					WHERE %sawm_messages.id_acct = %d AND %sawm_messages.id_folder_db = %d 
							AND %sawm_messages_body.id_acct = %sawm_messages.id_acct 
							AND %sawm_messages_body.id_msg = %sawm_messages.id_msg 
							AND %sawm_messages.%s '.$this->_inOrNot($messageIndexSet);
			
			return sprintf($sql, $this->_settings->DbPrefix, $this->_settings->DbPrefix,
								 $this->_settings->DbPrefix, $this->_settings->DbPrefix,
								$account->Id, $this->_settings->DbPrefix, $folder->IdDb,
								$this->_settings->DbPrefix, $this->_settings->DbPrefix,
								$this->_settings->DbPrefix, $this->_settings->DbPrefix,
								$this->_settings->DbPrefix,
								$this->_getMsgIdUidFieldName($indexAsUid, $account->MailProtocol),
								$this->_quoteUids($messageIndexSet, $indexAsUid, $account->MailProtocol));
		}

		/**
		 * @param int $pageNumber
		 * @param short $sortField
		 * @param bool $sortOrder
		 * @param Account $account
		 * @return string
		 */
		function LoadContactsAndGroups($pageNumber, $sortField, $sortOrder, $account)
		{
			$filter = 'is_group';
			switch ($sortField)
			{
				case 0:
					$filter = 'is_group';
					break;
				case 1:
					$filter = 'name';
					break;
				case 2:
					$filter = 'email';
					break;
				case 3:
					$filter = 'frequency';
					break;
			}
			
			$order = ($sortOrder) ? 'DESC' : 'ASC';

			$sql = 'SELECT id_addr AS id, fullname AS name, 
						CASE primary_email
							WHEN %s THEN h_email
							WHEN %s THEN b_email
							WHEN %s THEN other_email
						END AS email, 0 AS is_group, use_frequency AS frequency, use_friendly_nm AS usefriendlyname
					FROM %sawm_addr_book
					WHERE deleted = 0 AND id_user = %d
					UNION
					SELECT id_group AS id, group_nm AS name, \'\' AS email, 1 AS is_group, use_frequency AS frequency, 1 AS usefriendlyname
					FROM %sawm_addr_groups
					WHERE id_user = %d
					ORDER BY %s %s, name %s
					LIMIT %d, %d';
			
			return sprintf($sql, PRIMARYEMAIL_Home, PRIMARYEMAIL_Business, PRIMARYEMAIL_Other,
								$this->_settings->DbPrefix, $account->IdUser,
								$this->_settings->DbPrefix, $account->IdUser,
							$filter, $order, $order,
							($pageNumber - 1) * $account->ContactsPerPage, $account->ContactsPerPage);
		}
		
		/**
		 * @param int $pageNumber
		 * @param string $conditon
		 * @param int $groupId
		 * @param short $sortField
		 * @param bool $sortOrder
		 * @param Account $account
		 */
		function SearchContactsAndGroups($pageNumber, $condition, $groupId, $sortField, $sortOrder, $account, $lookForType)
		{
			$filter = 'is_group';
			switch ($sortField)
			{
				case 0:
					$filter = 'is_group';
					break;
				case 1:
					$filter = 'name';
					break;
				case 2:
					$filter = 'email';
					break;
				case 3:
					$filter = 'frequency';
					break;
			}
			
	  		$condition = ($lookForType == 1) ? $this->_escapeString($condition.'%') : $this->_escapeString('%'.$condition.'%');
			
			$order = ($sortOrder) ? 'DESC' : 'ASC';
			
			$contactsResultCount = ($lookForType == 1) ? SUGGESTCONTACTS : $account->ContactsPerPage;

			$sqlAdd = '';
			if ($lookForType == 1 && !$account->IsDemo && $this->_settings->GlobalAddressBook === GLOBAL_ADDRESS_BOOK_DOMAIN && $account->DomainAddressBook && $account->IdDomain > 0)
			{
				$sqlAdd = sprintf('UNION
SELECT id_acct AS id, friendly_nm AS name, email, 0 AS is_group, 0 AS frequency, use_friendly_nm AS usefriendlyname
FROM %sawm_accounts
WHERE id_domain = %d AND (email LIKE %s OR friendly_nm LIKE %s)', $this->_settings->DbPrefix, $account->IdDomain, $condition, $condition);
			}
			else if ($lookForType == 1 && !$account->IsDemo && $this->_settings->GlobalAddressBook === GLOBAL_ADDRESS_BOOK_SYSTEM)
			{
				$sqlAdd = sprintf('UNION
SELECT id_acct AS id, friendly_nm AS name, email, 0 AS is_group, 0 AS frequency, use_friendly_nm AS usefriendlyname
FROM %sawm_accounts
WHERE email LIKE %s OR friendly_nm LIKE %s', $this->_settings->DbPrefix, $condition, $condition);
			}

			if ($groupId == -1)
			{
				$sql = 'SELECT id_addr AS id, fullname AS name,
							CASE primary_email
								WHEN %s THEN h_email
								WHEN %s THEN b_email
								WHEN %s THEN other_email
							END AS email, 0 AS is_group, use_frequency AS frequency, use_friendly_nm AS usefriendlyname
						FROM %sawm_addr_book
						WHERE deleted = 0 AND id_user = %d AND (fullname LIKE %s OR h_email LIKE %s OR b_email LIKE %s OR other_email LIKE %s)
						UNION
						SELECT id_group AS id, group_nm AS name, \'\' AS email, 1 AS is_group, use_frequency AS frequency, 1 AS usefriendlyname
						FROM %sawm_addr_groups
						WHERE id_user = %d AND group_nm LIKE %s
						%s
						ORDER BY %s %s, name %s
						LIMIT %d, %d';
				
				return sprintf($sql, PRIMARYEMAIL_Home, PRIMARYEMAIL_Business, PRIMARYEMAIL_Other,
									$this->_settings->DbPrefix, $account->IdUser, $condition, $condition, $condition, $condition,
									$this->_settings->DbPrefix, $account->IdUser, $condition,
									$sqlAdd,
									$filter, $order, $order,
									($pageNumber - 1) * $contactsResultCount, $contactsResultCount);
			}
			else
			{
				$sql = 'SELECT book.id_addr AS id, fullname AS name,
							CASE primary_email
								WHEN %s THEN h_email
								WHEN %s THEN b_email
								WHEN %s THEN other_email
							END AS email, 0 AS is_group, use_frequency AS frequency, use_friendly_nm AS usefriendlyname
						FROM %sawm_addr_book AS book
						INNER JOIN %sawm_addr_groups_contacts AS gr_cont ON gr_cont.id_addr = book.id_addr AND
								id_group = %d
						WHERE deleted = 0 AND id_user = %d AND (fullname LIKE %s OR h_email LIKE %s OR b_email LIKE %s OR other_email LIKE %s)
						ORDER BY %s %s, name %s
						LIMIT %d, %d';
				
				return sprintf($sql, PRIMARYEMAIL_Home, PRIMARYEMAIL_Business, PRIMARYEMAIL_Other,
									$this->_settings->DbPrefix, $this->_settings->DbPrefix,
									$groupId, $account->IdUser, $condition, $condition, $condition, $condition,
									$filter, $order, $order,
									($pageNumber - 1) * $contactsResultCount, $contactsResultCount);
				
			}
		}

		/**
		 * @param int $id
		 * @return string
		 */
		function DeleteFolderTreeById($id)
		{
			$sql = 'DELETE %1$sawm_folders_tree 
						FROM %1$sawm_folders, %1$sawm_folders_tree
						WHERE %1$sawm_folders.id_folder = %1$sawm_folders_tree.id_folder 
						AND %1$sawm_folders.id_acct = %2$d';

			return sprintf($sql, $this->_settings->DbPrefix, $id);
		}
		
		/**
		 * @param int $id
		 * @return string
		 */
		function DeleteAddrGroupsContactsById($id)
		{
			$sql = 'DELETE %1$sawm_addr_groups_contacts
						FROM %1$sawm_addr_groups_contacts, %1$sawm_addr_groups
						WHERE %1$sawm_addr_groups_contacts.id_group = %1$sawm_addr_groups.id_group 
						AND %1$sawm_addr_groups.id_user = %2$d';

			return sprintf($sql, $this->_settings->DbPrefix, $id);
		}
		
		/**
		 * @param int $id
		 * @return string
		 */
		function DeleteCalendarEvents($id)
		{
			$sql = 'DELETE %1$sacal_events
						FROM %1$sacal_events, %1$sacal_calendars
						WHERE %1$sacal_events.calendar_id = %1$sacal_calendars.calendar_id
						AND %1$sacal_calendars.user_id = %2$d';

			return sprintf($sql, $this->_settings->DbPrefix, $id);
		}
		
		/**
		 * @param Account $account
		 * @return string
		 */
		function SelectExpiredMessageUids($account)
		{
			$sql = 'SELECT str_uid FROM %sawm_messages
					WHERE id_acct = %d AND DATE_ADD(msg_date, INTERVAL %d DAY) < CURDATE()';
			
			return sprintf($sql, $this->_settings->DbPrefix, $account->Id, $account->MailsOnServerDays);
		}
		
		/**
		 * @param Account $account
		 * @param Folder $folder
		 * @param int $_day_cnt
		 * @return string
		 */
		function SelectExpiredMessageUidsInFolder($account, $folder, $_day_cnt)
		{
			$sql = 'SELECT id_msg, str_uid FROM %sawm_messages
					WHERE id_acct = %d AND id_folder_db = %d AND DATE_ADD(msg_date, INTERVAL %d DAY) < CURDATE()';
			
			return sprintf($sql, $this->_settings->DbPrefix, $account->Id, $folder->IdDb, $_day_cnt);
		}

	}

	class MsSqlCommandCreator extends CommandCreator
	{
		function MsSqlCommandCreator($settings = null)
		{
			CommandCreator::CommandCreator(QUOTE_DOUBLE, $settings);
		}
		
		/**
		 * @access protected
		 * @param string $bin
		 * @return string
		 */
		function _escapeBin($bin)
		{
			return "0x".bin2hex($bin);
		}
		
		/**
		 * @param string $fieldName
		 * @return string
		 */
		function GetDateFormat($fieldName)
		{
			return CDateTime::GetMsSqlDateFormat($fieldName);
		}

		function UpdateDateFormat($fieldValue)
		{
			return 'CONVERT(DATETIME, '.$this->_escapeString($fieldValue).', 120)';
		}
		
		/**
		 * @param int $pageNumber
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function LoadMessageHeaders($pageNumber, $folder, $account)
		{
	  		$filter = '';
	  		$tempstr = '';
	  		$asc = true;
	  		
	  		$this->_setSortOrder($account->DefaultOrder, $filter, $asc);
	  		
	  		if (($pageNumber - 1) * $account->MailsPerPage > 0)
	  		{
	  			$tempstr = ' AND id_msg NOT IN 
						(SELECT TOP %d id_msg FROM %sawm_messages WHERE id_acct = %d AND id_folder_db = %d ORDER BY %s %s)';
	  			
				$tempstr = sprintf($tempstr,
					($pageNumber - 1) * $account->MailsPerPage, 
					$this->_settings->DbPrefix,
					$account->Id, $folder->IdDb,
					$filter, ($asc)?'ASC':'DESC');
	  		}
	  		
			/* read messages from db */
			$sql = 'SELECT TOP %d id_msg, %s AS uid, id_folder_db, from_msg, to_msg, cc_msg,
							bcc_msg, subject, %s AS nmsg_date, size, priority, x_spam,
							attachments, seen, flagged, deleted, replied, forwarded, grayed, charset, sensitivity
					FROM %sawm_messages
					WHERE id_acct = %d AND id_folder_db = %d%s
					ORDER BY %s %s';
			
			return sprintf($sql,  $account->MailsPerPage, $this->_getMsgIdUidFieldName(true, $account->MailProtocol),
					CDateTime::GetMsSqlDateFormat('msg_date'),
					$this->_settings->DbPrefix,
					$account->Id, $folder->IdDb,
					$tempstr,
					$filter, ($asc)?'ASC':'DESC');
		}

		/**
		 * @param int $pageNumber
		 * @param string $condition
		 * @param FolderCollection $folders
		 * @param bool $inHeadersOnly
		 * @param Account $account
		 * @return WebMailMessageCollection
		 */
		function SearchMessages($pageNumber, $condition, &$folders, $inHeadersOnly, &$account)
		{
			$tempstr = '';
			$foldersId = array();
			$_foldersKeys = array_keys($folders->Instance());
			foreach ($_foldersKeys as $key)
			{
				$folder =& $folders->Get($key);
				if (!$folder->Hide)
				{
					$foldersId[] = $folder->IdDb;
				}
				unset($folder);
			}
			unset($folders, $_foldersKeys);
			
	  		$filter = '';
	  		$asc = true;
	  		
	  		$this->_setSortOrder($account->DefaultOrder, $filter, $asc);
			
	  		$condition = str_replace('[', '[[]', $condition);
	  		
	  		$condition = $this->_escapeString('%'.$condition.'%');
			$str_foldersId = implode(',', $foldersId);
	  		
	  		if ($inHeadersOnly)
			{
				if (($pageNumber - 1) * $account->MailsPerPage > 0)
		  		{
		  			$tempstr = ' AND id_msg NOT IN 
								(SELECT TOP %d id_msg FROM %sawm_messages 
								WHERE id_acct = %d AND id_folder_db '.$this->_inOrNot($foldersId).' AND	
								(from_msg LIKE %s OR to_msg LIKE %s OR cc_msg LIKE %s OR bcc_msg
								LIKE %s OR subject LIKE %s) ORDER BY %s %s)';
		  			
					$tempstr = sprintf($tempstr,
									($pageNumber - 1) * $account->MailsPerPage,
									$this->_settings->DbPrefix,
									$account->Id, $str_foldersId,
									$condition, $condition, $condition, $condition, $condition,
									$filter, ($asc)?'ASC':'DESC');
		  		}
				$sql = 'SELECT TOP %d id_msg, %s AS uid, id_folder_db, from_msg, to_msg, cc_msg,
							bcc_msg, subject, %s AS nmsg_date, size, priority, x_spam,
							attachments, seen, flagged, deleted, replied, forwarded, grayed, charset, sensitivity
						FROM %sawm_messages
						WHERE id_acct = %d AND id_folder_db '.$this->_inOrNot($foldersId).' AND	
							(from_msg LIKE %s OR to_msg LIKE %s OR cc_msg LIKE %s OR bcc_msg
							LIKE %s OR subject LIKE %s)%s
						ORDER BY %s %s';
				
				return sprintf($sql, $account->MailsPerPage, $this->_getMsgIdUidFieldName(true, $account->MailProtocol),
									CDateTime::GetMsSqlDateFormat('msg_date'),
									$this->_settings->DbPrefix,
									$account->Id, $str_foldersId,
									$condition, $condition, $condition, $condition, $condition,
									$tempstr,
									$filter, ($asc) ? 'ASC' : 'DESC');
			}
			else
			{
				if (($pageNumber - 1) * $account->MailsPerPage > 0)
		  		{
		  			$tempstr = ' AND id_msg NOT IN 
							(SELECT TOP %d id_msg FROM %sawm_messages
							WHERE id_acct = %d AND id_folder_db '.$this->_inOrNot($foldersId).' AND	
								(from_msg LIKE %s OR to_msg LIKE %s OR cc_msg LIKE %s OR bcc_msg
								LIKE %s OR subject LIKE %s OR body_text LIKE %s) ORDER BY %s %s)';
		  			
					$tempstr = sprintf($tempstr,
									($pageNumber - 1) * $account->MailsPerPage,
									$this->_settings->DbPrefix,
									$account->Id, $str_foldersId,
									$condition, $condition, $condition, $condition, $condition, $condition,
									$filter, ($asc) ? 'ASC' : 'DESC');
		  		}				

				$sql = 'SELECT TOP %d id_msg, %s AS uid, id_folder_db, from_msg, to_msg, cc_msg,
							bcc_msg, subject, %s AS nmsg_date, size, priority, x_spam,
							attachments, seen, flagged, deleted, replied, forwarded, grayed, charset, sensitivity
						FROM %sawm_messages
						WHERE id_acct = %d AND id_folder_db '.$this->_inOrNot($foldersId).' AND	
							(from_msg LIKE %s OR to_msg LIKE %s OR cc_msg LIKE %s OR bcc_msg
							LIKE %s OR subject LIKE %s OR body_text LIKE %s)%s
						ORDER BY %s %s';
				
				return sprintf($sql, $account->MailsPerPage, $this->_getMsgIdUidFieldName(true, $account->MailProtocol),
									CDateTime::GetMsSqlDateFormat('msg_date'),
									$this->_settings->DbPrefix,
									$account->Id, $str_foldersId,
									$condition, $condition, $condition, $condition, $condition, $condition,
									$tempstr,
									$filter, ($asc) ? 'ASC' : 'DESC');
			}
		}
		
		/**
		 * @param string $accountId
		 * @return string
		 */
		function GetFolders($accountId)
		{
			
			$sql = 'SELECT p.id_folder, p.id_parent, p.type, p.name, p.full_path, p.sync_type, p.hide, p.fld_order,
							COUNT(messages.id) AS message_count, COUNT(messages_unread.seen) AS unread_message_count,
							SUM(messages.size) AS folder_size, MAX(folder_level) AS level
					FROM %sawm_folders as n, %sawm_folders_tree as t, %sawm_folders as p
					LEFT OUTER JOIN %sawm_messages AS messages ON p.id_folder = messages.id_folder_db
					LEFT OUTER JOIN %sawm_messages AS messages_unread ON
							p.id_folder = messages_unread.id_folder_db AND 
							messages.id = messages_unread.id AND messages_unread.seen = 0
					WHERE n.id_parent = -1
					     AND n.id_folder = t.id_parent
					     AND t.id_folder = p.id_folder
					     AND p.id_acct = %d
					GROUP BY p.id_folder, p.id_parent, p.type, p.name, p.full_path, p.sync_type, p.hide, p.fld_order
					ORDER BY p.fld_order';			
			
			return sprintf($sql, $this->_settings->DbPrefix, $this->_settings->DbPrefix, $this->_settings->DbPrefix,
									$this->_settings->DbPrefix,	$this->_settings->DbPrefix, $accountId);
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function SelectSubFoldersId($folder)
		{
			$sql = 'SELECT c.id_folder
					FROM %sawm_folders AS n, %sawm_folders_tree AS t, %sawm_folders AS c
					WHERE n.id_folder = %d AND n.id_folder = t.id_parent AND t.id_folder = c.id_folder';

			return sprintf($sql, $this->_settings->DbPrefix, $this->_settings->DbPrefix, 
									$this->_settings->DbPrefix, $folder->IdDb);
		}
		
		/**
		 * @param Folder $folder
		 * @param Array $foldersId
		 * @param string $newName
		 * @return string
		 */
		function RenameSubFoldersPath($folder, $foldersId, $newSubPath)
		{
			$sql = 'UPDATE %sawm_folders
					SET full_path = \'%s\' + SUBSTRING(full_path, %d, LEN(full_path)-%d+1)
					WHERE id_acct = %d AND id_folder '.$this->_inOrNot($foldersId).' AND id_folder <> %d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $newSubPath, strlen($folder->FullName)+1,
					strlen($folder->FullName)+1, $folder->IdAcct, implode(',', $foldersId), $folder->IdDb);
		}
		
		/**
		 * @param Array $messageIndexSet
		 * @param bool $indexAsUid
		 * @param Folder $folder
		 * @param Account $account
		 * @return string
		 */
		function DeleteMessagesBody($messageIndexSet, $indexAsUid, $folder, $account)
		{
			$sql = 'DELETE
					FROM %1$sawm_messages_body
					FROM %1$sawm_messages AS msgs
					WHERE msgs.id_acct = %2$d AND msgs.id_folder_db = %3$d 
							AND	%1$sawm_messages_body.id_acct = msgs.id_acct 
							AND %1$sawm_messages_body.id_msg = msgs.id_msg
							AND msgs.%4$s IN (%5$s)';
			
			return sprintf($sql, $this->_settings->DbPrefix,
								$account->Id, $folder->IdDb,
								$this->_getMsgIdUidFieldName($indexAsUid, $account->MailProtocol),
								$this->_quoteUids($messageIndexSet, $indexAsUid, $account->MailProtocol));
		}

		/**
		 * @param int $pageNumber
		 * @param short $sortField
		 * @param bool $sortOrder
		 * @param Account $account
		 * @return string
		 */
		function LoadContactsAndGroups($pageNumber, $sortField, $sortOrder, $account)
		{
			$dopstr = '';
			$filter = 'is_group';
			switch ($sortField)
			{
				default:
				case 0:
					$filter = 'is_group';
					$temp = ($sortOrder) ? 'DESC' : 'ASC';
					$dopstr = ', name '.$temp;
					break;
				case 1:
					$filter = 'name';
					break;
				case 2:
					$filter = 'email';
					break;
				case 3:
					$filter = 'frequency';
					break;
			}

			$str = '';
			$nom = ($pageNumber - 1) * $account->ContactsPerPage;

			if ($nom)
			{
				$str = ' WHERE union_tbl.nuid NOT IN
					(SELECT TOP %d nuid FROM
					(SELECT id_addr AS [id], fullname AS [name],
						CASE primary_email
							WHEN %s THEN h_email
							WHEN %s THEN b_email
							WHEN %s THEN other_email
						END AS email, 0 AS is_group,
						id_addr AS nuid, use_frequency AS frequency, use_friendly_nm AS usefriendlyname
					FROM %sawm_addr_book WHERE deleted = 0 AND id_user = %d
					UNION
					SELECT id_group AS [id], group_nm AS [name], \'\' AS email, 1 AS is_group,
					-id_group AS nuid, use_frequency AS frequency, 1 AS usefriendlyname
					FROM %sawm_addr_groups
					WHERE id_user = %d) AS union_tbl2 ORDER BY %s %s %s)';

				$str = sprintf($str, $nom,
								PRIMARYEMAIL_Home, PRIMARYEMAIL_Business, PRIMARYEMAIL_Other,
								$this->_settings->DbPrefix, $account->IdUser,
								$this->_settings->DbPrefix, $account->IdUser,
								$filter, ($sortOrder)?'DESC':'ASC', $dopstr);
			}

			$sql = 'SELECT TOP %d * FROM
					(SELECT id_addr AS [id], fullname AS [name],
						CASE primary_email
							WHEN %s THEN h_email
							WHEN %s THEN b_email
							WHEN %s THEN other_email
						END AS email, 0 AS is_group,
						id_addr AS nuid, use_frequency AS frequency, use_friendly_nm AS usefriendlyname
					FROM %sawm_addr_book
					WHERE deleted = 0 AND id_user = %d
					UNION
					SELECT id_group AS [id], group_nm AS [name], \'\' AS email, 1 AS is_group,
					-id_group AS nuid, use_frequency AS frequency, 1 AS usefriendlyname
					FROM %sawm_addr_groups
					WHERE id_user = %d) AS union_tbl%s
					ORDER BY %s %s %s';

			return sprintf($sql, $account->ContactsPerPage,
								PRIMARYEMAIL_Home, PRIMARYEMAIL_Business, PRIMARYEMAIL_Other,
								$this->_settings->DbPrefix, $account->IdUser,
								$this->_settings->DbPrefix, $account->IdUser,
								$str,
								$filter, ($sortOrder)?'DESC':'ASC', $dopstr);
		}
		
		/**
		 * @param int $pageNumber
		 * @param string $conditon
		 * @param int $groupId
		 * @param short $sortField
		 * @param bool $sortOrder
		 * @param Account $account
		 */
		function SearchContactsAndGroups($pageNumber, $condition, $groupId, $sortField, $sortOrder, $account, $lookForType)
		{
			$dopstr = '';
			$filter = 'is_group';
			switch ($sortField)
			{
				case 0:
					$filter = 'is_group';
					$temp = ($sortOrder)?'DESC':'ASC';
					$dopstr = ', name '.$temp;
					break;
				default:	
				case 1:
					$filter = 'name';
					break;
				case 2:
					$filter = 'email';
					break;
				case 3:
					$filter = 'frequency';
					break;
			}
			
			$str = '';
			$accountPerPage = ($lookForType == 1) ? SUGGESTCONTACTS : $account->ContactsPerPage;
			$nom = ($pageNumber - 1) * $accountPerPage;
			
	  		$condition = str_replace('[', '[[]', $condition);
			
	  		$condition = ($lookForType == 1) ? $this->_escapeString($condition.'%') : $this->_escapeString('%'.$condition.'%');
			
			if ($groupId == -1)
			{
				if ($nom)
				{
					$str = ' WHERE union_tbl.nuid NOT IN 
						(SELECT TOP %d nuid FROM
						(SELECT id_addr AS [id], fullname AS [name],
							CASE primary_email
								WHEN %s THEN h_email
								WHEN %s THEN b_email
								WHEN %s THEN other_email
							END AS email, 0 AS is_group,
							id_addr AS nuid, use_frequency AS frequency, use_friendly_nm AS usefriendlyname
						FROM %sawm_addr_book WHERE deleted = 0 AND id_user = %d AND
							(fullname LIKE %s OR h_email LIKE %s OR b_email LIKE %s OR other_email LIKE %s)
						UNION
						SELECT id_group AS [id], group_nm AS [name], \'\' AS email, 1 AS is_group,
						-id_group AS nuid, use_frequency AS frequency, 1 AS usefriendlyname
						FROM %sawm_addr_groups
						WHERE id_user = %d AND group_nm LIKE %s) AS union_tbl2 ORDER BY %s %s %s)';
					
					$str = sprintf($str, $nom, 
									PRIMARYEMAIL_Home, PRIMARYEMAIL_Business, PRIMARYEMAIL_Other,
									$this->_settings->DbPrefix, $account->IdUser, $condition, $condition, $condition, $condition,
									$this->_settings->DbPrefix, $account->IdUser, $condition,
									$filter, ($sortOrder)?'DESC':'ASC', $dopstr);
				}
					
				$sql = 'SELECT TOP %d * FROM
						(SELECT id_addr AS [id], fullname AS [name],
							CASE primary_email
								WHEN %s THEN h_email
								WHEN %s THEN b_email
								WHEN %s THEN other_email
							END AS email, 0 AS is_group, 
							id_addr AS nuid, use_frequency AS frequency, use_friendly_nm AS usefriendlyname
						FROM %sawm_addr_book
						WHERE deleted = 0 AND id_user = %d AND
							(fullname LIKE %s OR h_email LIKE %s OR b_email LIKE %s OR other_email LIKE %s)
						UNION
						SELECT id_group AS [id], group_nm AS [name], \'\' AS email, 1 AS is_group,
						-id_group AS nuid, use_frequency AS frequency, 1 AS usefriendlyname
						FROM %sawm_addr_groups
						WHERE id_user = %d AND group_nm LIKE %s) AS union_tbl%s					
						ORDER BY %s %s %s';

				return sprintf($sql, $accountPerPage,
								PRIMARYEMAIL_Home, PRIMARYEMAIL_Business, PRIMARYEMAIL_Other,
								$this->_settings->DbPrefix, $account->IdUser, $condition, $condition, $condition, $condition,
								$this->_settings->DbPrefix, $account->IdUser, $condition,
								$str,								
								$filter, ($sortOrder)?'DESC':'ASC', $dopstr);
			}
			else
			{
				if ($nom)
				{
					$str = ' WHERE union_tbl.nuid NOT IN 
						(SELECT TOP %d nuid FROM
						(SELECT book.id_addr AS [id], fullname AS [name],
							CASE primary_email
								WHEN %s THEN h_email
								WHEN %s THEN b_email
								WHEN %s THEN other_email
							END AS email, 0 AS is_group,
							book.id_addr AS nuid, use_frequency AS frequency, use_friendly_nm AS usefriendlyname
						FROM %sawm_addr_book AS book
						INNER JOIN %sawm_addr_groups_contacts AS gr_cont ON gr_cont.id_addr = book.id_addr AND
								id_group = %d
						WHERE deleted = 0 AND id_user = %d AND
							(fullname LIKE %s OR h_email LIKE %s OR b_email LIKE %s OR other_email LIKE %s)
						) AS union_tbl2 ORDER BY %s %s %s)';
					
					$str = sprintf($str, $nom, 
									PRIMARYEMAIL_Home, PRIMARYEMAIL_Business, PRIMARYEMAIL_Other,
									$this->_settings->DbPrefix, $this->_settings->DbPrefix, $groupId,
									$account->IdUser, $condition, $condition, $condition, $condition,
									$filter, ($sortOrder)?'DESC':'ASC', $dopstr);
				}
					
				$sql = 'SELECT TOP %d * FROM
						(SELECT book.id_addr AS [id], fullname AS [name],
							CASE primary_email
								WHEN %s THEN h_email
								WHEN %s THEN b_email
								WHEN %s THEN other_email
							END AS email, 0 AS is_group, 
							book.id_addr AS nuid, use_frequency AS frequency, use_friendly_nm AS usefriendlyname
						FROM %sawm_addr_book AS book
						INNER JOIN %sawm_addr_groups_contacts AS gr_cont ON gr_cont.id_addr = book.id_addr AND
								id_group = %d
						WHERE deleted = 0 AND id_user = %d AND
							(fullname LIKE %s OR h_email LIKE %s OR b_email LIKE %s OR other_email LIKE %s)
						) AS union_tbl%s					
						ORDER BY %s %s %s';

				return sprintf($sql, $accountPerPage,
								PRIMARYEMAIL_Home, PRIMARYEMAIL_Business, PRIMARYEMAIL_Other,
								$this->_settings->DbPrefix, $this->_settings->DbPrefix, $groupId,
								$account->IdUser, $condition, $condition, $condition, $condition,
								$str,								
								$filter, ($sortOrder)?'DESC':'ASC', $dopstr);
			}

		}
		
		/**
		 * @param int $id
		 * @return string
		 */
		function DeleteFolderTreeById($id)
		{
			$sql = 'DELETE FROM %1$sawm_folders_tree
						FROM %1$sawm_folders
						WHERE %1$sawm_folders.id_folder = %1$sawm_folders_tree.id_folder
						AND %1$sawm_folders.id_acct = %2$d';
			
			return sprintf($sql, $this->_settings->DbPrefix, $id);
		}
		
		
		/**
		 * @param int $id
		 * @return string
		 */
		function DeleteAddrGroupsContactsById($id)
		{
			$sql = 'DELETE FROM %1$sawm_addr_groups_contacts
						FROM %1$sawm_addr_groups
						WHERE %1$sawm_addr_groups_contacts.id_group = %1$sawm_addr_groups.id_group
						AND %1$sawm_addr_groups.id_user = %2$d';

			return sprintf($sql, $this->_settings->DbPrefix, $id);
		}
		
		/**
		 * @param int $id
		 * @return string
		 */
		function DeleteCalendarEvents($id)
		{
			$sql = 'DELETE FROM %1$sacal_events
						FROM %1$sacal_calendars
						WHERE %1$sacal_events.calendar_id = %1$sacal_calendars.calendar_id
						AND %1$sacal_calendars.user_id = %2$d';

			return sprintf($sql, $this->_settings->DbPrefix, $id);
		}
		
		/**
		 * @param Account $account
		 * @return string
		 */
		function SelectExpiredMessageUids($account)
		{
			$sql = 'SELECT str_uid FROM %sawm_messages
					WHERE id_acct = %d AND DATEADD(day, %d, msg_date) < GETDATE()';
			
			return sprintf($sql, $this->_settings->DbPrefix, $account->Id, $account->MailsOnServerDays);
		}
	}
