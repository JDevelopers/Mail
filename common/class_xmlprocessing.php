<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 *
 */

/**
 * only for processing.php
 */
class CXmlProcessing
{
	function GetMessagesList(&$_xmlRes, &$_messageCollection, &$_account, &$_settings, &$_processor, &$_folder, $_lookFor, $_lookFields, $_page, $_sortField, $_sortOrder)
	{
		if ($_messageCollection != null && $_account != null && $_folder != null)
		{
			$_msgsNode = new XmlDomNode('messages');
			$_msgsNode->AppendAttribute('id_acct', $_account->Id);
			$_msgsNode->AppendAttribute('page', $_page);
			$_msgsNode->AppendAttribute('sort_field', $_sortField);
			$_msgsNode->AppendAttribute('sort_order', $_sortOrder);
			$_msgsNode->AppendAttribute('count', $_folder->MessageCount);
			$_msgsNode->AppendAttribute('count_new', $_folder->UnreadMessageCount);

			$_folderOutNode = new XmlDomNode('folder');
			$_folderOutNode->AppendAttribute('id', $_folder->IdDb);
			$_folderOutNode->AppendAttribute('type', $_folder->Type);
			
			if (ConvertUtils::IsLatin($_folder->Name))
			{
				$_folderOutNode->AppendChild(new XmlDomNode('name',
					ConvertUtils::ConvertEncoding($_folder->Name,
					CPAGE_UTF7_Imap, CPAGE_UTF8), true));
			}
			else
			{
				$_folderOutNode->AppendChild(new XmlDomNode('name',
					ConvertUtils::ConvertEncoding($_folder->Name,
					$_processor->_account->DefaultIncCharset, CPAGE_UTF8), true));
			}
			
			$_folderOutNode->AppendChild(new XmlDomNode('full_name', $_folder->FullName, true));
			$_msgsNode->AppendChild($_folderOutNode);
			unset($_folderOutNode);
			
			$_lookForNode = new XmlDomNode('look_for', $_lookFor, true);
			$_lookForNode->AppendAttribute('fields', (int) $_lookFields);
			$_msgsNode->AppendChild($_lookForNode);

			$_msgFolderFullNames = array();
			$maf =& MessageActionFilters::CreateInstance();
			$mafNoReply = $maf->GetNoReplyEmails();
			$mafNoReplyAll = $maf->GetNoReplyAllEmails();
			$mafNoForward = $maf->GetNoForwardEmails();
			for ($_i = 0, $_c = $_messageCollection->Count(); $_i < $_c; $_i++)
			{
				$_msg =& $_messageCollection->Get($_i);
				$_msgNode = new XmlDomNode('message');
				$_msgNode->AppendAttribute('id', $_msg->IdMsg);
				$_msgNode->AppendAttribute('has_attachments', (int) $_msg->HasAttachments());
				$_msgNode->AppendAttribute('priority', $_msg->GetPriorityStatus());
				$_msgNode->AppendAttribute('size', $_msg->Size);
				$_msgNode->AppendAttribute('flags', $_msg->Flags);
				$_msgNode->AppendAttribute('charset', $_msg->Charset);

				if (!isset($_msgFolderFullNames[$_msg->IdFolder]))
				{
					$_msgFolderFullNames[$_msg->IdFolder] = $_processor->GetFolderFullName($_msg->IdFolder, $_account->Id);
				}

				$_folderMsgNode = new XmlDomNode('folder', $_msgFolderFullNames[$_msg->IdFolder], true);
				$_folderMsgNode->AppendAttribute('id', $_msg->IdFolder);
				$_msgNode->AppendChild($_folderMsgNode);
				unset($_folderMsgNode);

				$_msgNode->AppendChild(new XmlDomNode('from', $_msg->GetFromAsStringForSend(), true));
				$_msgNode->AppendChild(new XmlDomNode('to', $_msg->GetToAsStringForSend(), true));
				$_msgNode->AppendChild(new XmlDomNode('reply_to', $_msg->GetReplyToAsStringForSend(), true));
				$_msgNode->AppendChild(new XmlDomNode('cc', $_msg->GetCcAsStringForSend(), true));
				$_msgNode->AppendChild(new XmlDomNode('bcc', $_msg->GetBccAsStringForSend(), true));

				$_msgNode->AppendChild(new XmlDomNode('subject', $_msg->GetSubject(true), true));

				$_msgNode->AppendAttribute('sensivity', $_msg->GetSensitivity());

				$fromEmail = $_msg->GetFrom();
				$fromEmail = $fromEmail->Email;

				$_msgNode->AppendAttribute('no_reply', (count($mafNoReply) > 0 && in_array($fromEmail, $mafNoReply)) ? '1' : '0');
				$_msgNode->AppendAttribute('no_reply_all', (count($mafNoReplyAll) > 0 && in_array($fromEmail, $mafNoReplyAll)) ? '1' : '0');
				$_msgNode->AppendAttribute('no_forward', (count($mafNoForward) > 0 && in_array($fromEmail, $mafNoForward)) ? '1' : '0');

				$_date =& $_msg->GetDate();
				$_date->FormatString = $_account->DefaultDateFormat;
				$_date->TimeFormat = $_account->DefaultTimeFormat;

				if ($_settings->AllowUsersChangeTimeZone)
				{
					$_msgNode->AppendChild(new XmlDomNode('short_date', $_date->GetFormattedShortDate($_account->GetDefaultTimeOffset()), true));
					$_msgNode->AppendChild(new XmlDomNode('full_date', $_date->GetFormattedFullDate($_account->GetDefaultTimeOffset()), true));
				}
				else
				{
					$_msgNode->AppendChild(new XmlDomNode('short_date', $_date->GetFormattedShortDate($_account->GetDefaultTimeOffset($_settings->DefaultTimeZone)), true));
					$_msgNode->AppendChild(new XmlDomNode('full_date', $_date->GetFormattedFullDate($_account->GetDefaultTimeOffset($_settings->DefaultTimeZone)), true));
				}

				$_msgNode->AppendChild(new XmlDomNode('uid', $_msg->Uid, true));
				$_msgsNode->AppendChild($_msgNode);
				unset($_msgNode, $_msg);
			}

			$_xmlRes->XmlRoot->AppendChild($_msgsNode);
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_MSG_LIST, $_xmlRes);
		}
	}

	function GetSignature(&$_xmlRes, &$_account)
	{
		if ($_account)
		{
			$_signatureNode = new XmlDomNode('signature', $_account->Signature, true);
			$_signatureNode->AppendAttribute('id_acct', $_account->Id);
			$_signatureNode->AppendAttribute('type', $_account->SignatureType);
			$_signatureNode->AppendAttribute('opt', $_account->SignatureOptions);
			$_xmlRes->XmlRoot->AppendChild($_signatureNode);
		}
	}

	function GetSettingsList(&$_xmlRes, &$_account, $_settings, &$_dbStorage, &$mailProcessor)
	{
		$_mailBoxesSize = $_account->MailboxSize;
		$_accountSize = $_account->MailboxSize;
		if ($_account->ImapQuota)
		{
			if ($mailProcessor && $mailProcessor->MailStorage->Connect(true))
			{
				$usedQuota = $mailProcessor->GetUsedQuota();
				if (false !== $usedQuota)
				{
					$_mailBoxesSize = $usedQuota;
					$_accountSize = $usedQuota;
				}
			}
		}
		else
		{
			if (USE_DB && $_dbStorage && $_dbStorage->Connect())
			{
				$_mailBoxesSize = $_dbStorage->SelectMailboxesSize();
			}
		}
		
		$_settingsListNode = new XmlDomNode('settings_list');
		$_settingsListNode->AppendAttribute('show_text_labels', (int) $_settings->ShowTextLabels);
		$_settingsListNode->AppendAttribute('allow_change_settings', (int) $_account->AllowChangeSettings);
		$_settingsListNode->AppendAttribute('allow_dhtml_editor', (int) $_account->AllowDhtmlEditor);
		$_settingsListNode->AppendAttribute('allow_add_account', (int) $_settings->AllowUsersAddNewAccounts);
		$_settingsListNode->AppendAttribute('allow_account_def', (int) $_settings->AllowUsersChangeAccountsDef);
		$_settingsListNode->AppendAttribute('msgs_per_page', (int) $_account->MailsPerPage);
		$_settingsListNode->AppendAttribute('contacts_per_page', (int) $_account->ContactsPerPage);
		$_settingsListNode->AppendAttribute('auto_checkmail_interval', (int) $_account->AutoCheckMailInterval);
		$_settingsListNode->AppendAttribute('mailbox_limit', GetGoodBigInt($_account->MailboxLimit));
		$_settingsListNode->AppendAttribute('enable_mailbox_size_limit', (int) $_settings->EnableMailboxSizeLimit);
		$_settingsListNode->AppendAttribute('mailbox_size', GetGoodBigInt($_mailBoxesSize));
		$_settingsListNode->AppendAttribute('account_size', GetGoodBigInt($_accountSize));
		$_settingsListNode->AppendAttribute('hide_folders', (int) $_account->HideFolders);
		$_settingsListNode->AppendAttribute('horiz_resizer', (int) $_account->HorizResizer);
		$_settingsListNode->AppendAttribute('vert_resizer', (int) $_account->VertResizer);
		$_settingsListNode->AppendAttribute('mark', (int) $_account->Mark);
		$_settingsListNode->AppendAttribute('reply', (int) $_account->Reply);
		$_settingsListNode->AppendAttribute('view_mode', (int) $_account->ViewMode);
		$_settingsListNode->AppendAttribute('def_timezone', $_account->DefaultTimeZone);
		$_settingsListNode->AppendAttribute('allow_direct_mode', (int) $_account->AllowDirectMode);
		$_settingsListNode->AppendAttribute('direct_mode_is_default', (int) $_settings->DirectModeIsDefault);
		$_settingsListNode->AppendAttribute('allow_contacts', (int) $_settings->AllowContacts);
		$_settingsListNode->AppendAttribute('allow_calendar', (int) $_settings->AllowCalendar);
		$_settingsListNode->AppendAttribute('imap4_delete_like_pop3', (int) $_settings->Imap4DeleteLikePop3);
		$_settingsListNode->AppendAttribute('idle_session_timeout', (int) $_settings->IdleSessionTimeout);

		$_settingsListNode->AppendAttribute('allow_insert_image', (int) $_settings->AllowInsertImage);
		$_settingsListNode->AppendAttribute('allow_body_size', (int) $_settings->AllowBodySize);
		$_settingsListNode->AppendAttribute('max_body_size', (int) $_settings->MaxBodySize);
		$_settingsListNode->AppendAttribute('max_subject_size', (int) $_settings->MaxSubjectSize);
		$_settingsListNode->AppendAttribute('mobile_sync_enable_system', (int) ($_settings->EnableMobileSync && function_exists('mcrypt_encrypt')));
		
		$_skin = '';
		$_skins =& FileSystem::GetSkinsList();

		$_hasDefSettingsSkin = false;
		foreach ($_skins as $_skinName)
		{
			if ($_skinName == $_settings->DefaultSkin)
			{
				$_hasDefSettingsSkin = true;
			}

			if ($_skinName == $_account->DefaultSkin)
			{
				$_skin = $_account->DefaultSkin;
				break;
			}
		}

		if ($_skin === '')
		{
			$_skin = ($_hasDefSettingsSkin) ? $_settings->DefaultSkin : $_skins[0];
		}

		$_settingsListNode->AppendChild(new XmlDomNode('def_skin', $_skin, true));

		$_settingsListNode->AppendChild(new XmlDomNode('def_lang', $_account->DefaultLanguage, true));
		$_settingsListNode->AppendChild(new XmlDomNode('def_date_fmt', $_account->DefaultDateFormat, true));
		$_settingsListNode->AppendAttribute('time_format', $_account->DefaultTimeFormat);

		if (is_array($_account->Columns) && count($_account->Columns) > 0)
		{
			$_columnsNode = new XmlDomNode('columns');
			foreach ($_account->Columns AS $_id_column => $_column_value)
			{
				$_columnNode = new XmlDomNode('column');
				$_columnNode->AppendAttribute('id', $_id_column);
				$_columnNode->AppendAttribute('value', $_column_value);
				$_columnsNode->AppendChild($_columnNode);
				unset($_columnNode);
			}
			$_settingsListNode->AppendChild($_columnsNode);
		}

		$_xmlRes->XmlRoot->AppendChild($_settingsListNode);
	}

	/**
	 * @param Account $_account
	 * @param XmlDocument $_xmlObj
	 * @return WebMailMessage
	 */
	function CreateConfirmationMessage(&$_account, &$_xmlObj, &$_xmlRes)
	{
		$_message = null;
		$_confirmation = $_xmlObj->XmlRoot->GetChildValueByTagName('confirmation');
		if ($_confirmation && strlen($_confirmation) > 0 && $_account)
		{
			$_message = new WebMailMessage();
			$GLOBALS[MailDefaultCharset] = $_account->GetUserCharset();
			$GLOBALS[MailInputCharset] = $_account->GetUserCharset();
			$GLOBALS[MailOutputCharset] = $_account->GetDefaultOutCharset();

			$_message->Headers->SetHeaderByName(MIMEConst_MimeVersion, '1.0');
			$_message->Headers->SetHeaderByName(MIMEConst_XMailer, XMAILERHEADERVALUE);

			$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
			if (null !== $ip)
			{
				$_message->Headers->SetHeaderByName(MIMEConst_XOriginatingIp, $ip);
			}

			$_serverAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['SERVER_NAME'] : 'cantgetservername';
			$_message->Headers->SetHeaderByName(MIMEConst_MessageID,
				'<'.substr(session_id(), 0, 7).'.'.md5(time()).'@'. $_serverAddr .'>');

			$emailAccount = $_account->Email;
			/* custom class */
			wm_Custom::StaticUseMethod('ChangeAccountEmailToFake', array(&$emailAccount));

			$_message->SetFromAsString(ConvertUtils::WMBackHtmlSpecialChars($emailAccount));
			$_message->SetToAsString(ConvertUtils::WMBackHtmlSpecialChars($_confirmation));
			$_message->SetSubject(ConvertUtils::WMBackHtmlSpecialChars(ReturnReceiptSubject));
			$_message->SetDate(new CDateTime(time()));

			$_confSubject = $_xmlObj->XmlRoot->GetChildValueByTagName('subject');

			$bodyText = ReturnReceiptMailText1.' '.$_account->Email.' '.ReturnReceiptMailText3.' "'.$_confSubject."\".\r\n\r\n".ReturnReceiptMailText2;

			$_message->TextBodies->PlainTextBodyPart =
					str_replace("\n", CRLF,
					str_replace("\r", '', ConvertUtils::WMBackHtmlNewCode($bodyText)));
		}
		
		return $_message;
	}

	/**
	 * @param Account $_account
	 * @param XmlDocument $_xmlObj
	 * @return WebMailMessage
	 */
	function &CreateMessage(&$_account, &$_xmlObj, &$_xmlRes)
	{
		$_messageNode =& $_xmlObj->XmlRoot->GetChildNodeByTagName('message');
		$_headersNode =& $_messageNode->GetChildNodeByTagName('headers');

		$_message = new WebMailMessage();
		$GLOBALS[MailDefaultCharset] = $_account->GetUserCharset();
		$GLOBALS[MailInputCharset] = $_account->GetUserCharset();
		$GLOBALS[MailOutputCharset] = $_account->GetDefaultOutCharset();

		$_message->Headers->SetHeaderByName(MIMEConst_MimeVersion, '1.0');
		$_message->Headers->SetHeaderByName(MIMEConst_XMailer, XMAILERHEADERVALUE);

		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
		if (null !== $ip)
		{
			$_message->Headers->SetHeaderByName(MIMEConst_XOriginatingIp, $ip);
		}

		$_fromAcctId = $_messageNode->GetAttribute('from_acct_id', -1);
		$_message->IdMsg = $_messageNode->GetAttribute('id', -1);
		$_message->SetPriority($_messageNode->GetAttribute('priority', 3));
		$_message->SetSensivity($_messageNode->GetAttribute('sensivity', MIME_SENSIVITY_NOTHING));

		$_message->Uid = $_messageNode->GetChildValueByTagName('uid');

		$_serverAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['SERVER_NAME'] : 'cantgetservername';
		$_message->Headers->SetHeaderByName(MIMEConst_MessageID,
			'<'.substr(session_id(), 0, 7).'.'.md5(time()).'@'. $_serverAddr .'>');

		if ($_fromAcctId > 0)
		{
			$_fromAcct = null;
			if ($_account->Id == $_fromAcctId)
			{
				$_fromAcct = $_account;
			}
			else
			{
				CXmlProcessing::CheckAccountAccess($_fromAcctId, $_xmlRes);
				$_fromAcct = CXmlProcessing::AccountCheckAndLoad($_xmlRes, $_fromAcctId, false, false);
			}
			
			if ($_fromAcct)
			{
				$email = $_fromAcct->GetFriendlyEmail();
				/* custom class */
				wm_Custom::StaticUseMethod('ChangeAccountEmailToFake', array(&$email));
				$_message->SetFromAsString(ConvertUtils::WMBackHtmlSpecialChars($email));
			}
		}
		else
		{
			$_temp = $_headersNode->GetChildValueByTagName('from');
			if ($_temp)
			{
				/* custom class */
				wm_Custom::StaticUseMethod('ChangeAccountEmailToFake', array(&$_temp));
				$_message->SetFromAsString(ConvertUtils::WMBackHtmlSpecialChars($_temp));
			}
		}
		$_temp = $_headersNode->GetChildValueByTagName('to');
		if ($_temp)
		{
			$_message->SetToAsString(ConvertUtils::WMBackHtmlSpecialChars($_temp));
		}
		$_temp = $_headersNode->GetChildValueByTagName('cc');
		if ($_temp)
		{
			$_message->SetCcAsString(ConvertUtils::WMBackHtmlSpecialChars($_temp));
		}
		$_temp = $_headersNode->GetChildValueByTagName('bcc');
		if ($_temp)
		{
			$_message->SetBccAsString(ConvertUtils::WMBackHtmlSpecialChars($_temp));
		}
		$_temp = $_headersNode->GetChildValueByTagName('mailconfirmation');
		if ($_temp)
		{
			/* custom class */
			wm_Custom::StaticUseMethod('ChangeAccountEmailToFake', array(&$_temp));
			$_message->SetReadMailConfirmationAsString(ConvertUtils::WMBackHtmlSpecialChars($_temp));
		}

		$_message->SetSubject(ConvertUtils::WMBackHtmlSpecialChars($_headersNode->GetChildValueByTagName('subject')));

		$_message->SetDate(new CDateTime(time()));

		$_bodyNode =& $_messageNode->GetChildNodeByTagName('body');
		if (isset($_bodyNode->Attributes['is_html']) && $_bodyNode->Attributes['is_html'])
		{
			$_message->TextBodies->HtmlTextBodyPart =
				ConvertUtils::AddHtmlTagToHtmlBody(
					str_replace("\n", CRLF,
					str_replace("\r", '',
						ConvertUtils::BackImagesToHtmlBody(
							ConvertUtils::WMBackHtmlNewCode($_bodyNode->Value)))));

			$_message->TextBodies->PlainTextBodyPart = $_message->TextBodies->HtmlToPlain();
		}
		else
		{
			$_message->TextBodies->PlainTextBodyPart =
				str_replace("\n", CRLF,
				str_replace("\r", '', 
					ConvertUtils::WMBackHtmlNewCode($_bodyNode->Value)));
		}

		$_attachmentsNode =& $_messageNode->GetChildNodeByTagName('attachments');

		if ($_attachmentsNode != null)
		{
			$tempFiles =& CTempFiles::CreateInstance($_account);

			$_log =& CLog::CreateInstance();
			$_attachmentsKeys = array_keys($_attachmentsNode->Children);
			foreach ($_attachmentsKeys as $_key)
			{
				$_attachNode =& $_attachmentsNode->Children[$_key];

				$_tempName = $_attachNode->GetChildValueByTagName('temp_name');
				$_fileName = $_attachNode->GetChildValueByTagName('name');

				$_attachCid = 'attach.php?img&amp;tn='.$_tempName.'&amp;filename='.$_fileName;
				$_replaceCid = md5(time().$_fileName);

				$_mime_type = $_attachNode->GetChildValueByTagName('mime_type');
				if ($_mime_type === '')
				{
					$_mime_type = ConvertUtils::GetContentTypeFromFileName($_fileName);
				}

				$_isInline = (bool) $_attachNode->GetAttribute('inline', false);

				if (!$_message->Attachments->AddFromBinaryBody($tempFiles->LoadFile($_attachNode->GetChildValueByTagName('temp_name')),
						$_fileName, $_mime_type, $_isInline))
				{
					$_log->WriteLine('Error Add tempfile in message: '.getGlobalError(), LOG_LEVEL_ERROR);
				}

				if (isset($_bodyNode->Attributes['is_html']) && $_bodyNode->Attributes['is_html'])
				{
					if (strpos($_message->TextBodies->HtmlTextBodyPart, $_attachCid) !== false)
					{
						$_attachment =& $_message->Attachments->GetLast();
						if ($_attachment)
						{
							$_attachment->MimePart->Headers->SetHeaderByName(MIMEConst_ContentID, '<'.$_replaceCid.'>');
							$_message->TextBodies->HtmlTextBodyPart = str_replace($_attachCid, 'cid:'.$_replaceCid, $_message->TextBodies->HtmlTextBodyPart);

							$_attachname = ConvertUtils::EncodeHeaderString($_attachNode->GetChildValueByTagName('name'), $_account->GetUserCharset(), $GLOBALS[MailOutputCharset]);
							$_attachment->MimePart->Headers->SetHeaderByName(MIMEConst_ContentDisposition, MIMEConst_InlineLower.';'.CRLF."\t".MIMEConst_FilenameLower.'="'.$_attachname.'"', false);
						}
						unset($_attachment);
					}
					else if ($_isInline)
					{
						$_message->Attachments->DeleteLast();
					}
				}
				unset($_attachNode);
			}
		}

		return $_message;
	}

	/**
	 * @param DbStorage $_dbStorage
	 * @param int $_idAddress
	 * @return XmlDomNode
	 */
	function GetContactNodeFromAddressBookRecord(&$_account, &$_settings, $_idAddress)
	{
		$contactManager =& ContactCreator::CreateContactStorage($_account, $_settings);
		$_addressBookRecord = $contactManager->GetContact($_idAddress);
		if ($_addressBookRecord != null)
		{
			$_contactNode = new XmlDomNode('contact');
			$_contactNode->AppendAttribute('id', $_idAddress);
			$_contactNode->AppendAttribute('primary_email', $_addressBookRecord->PrimaryEmail);
			$_contactNode->AppendAttribute('use_friendly_name', (int) $_addressBookRecord->UseFriendlyName);

			$_contactNode->AppendChild(new XmlDomNode('title', $_addressBookRecord->Title, true));
			$_contactNode->AppendChild(new XmlDomNode('fullname', $_addressBookRecord->FullName, true));
			$_contactNode->AppendChild(new XmlDomNode('firstname', $_addressBookRecord->FirstName, true));
			$_contactNode->AppendChild(new XmlDomNode('surname', $_addressBookRecord->SurName, true));
			$_contactNode->AppendChild(new XmlDomNode('nickname', $_addressBookRecord->NickName, true));

			$_birthdayNode = new XmlDomNode('birthday');

			$_birthdayNode->AppendAttribute('day', $_addressBookRecord->BirthdayDay);
			$_birthdayNode->AppendAttribute('month', $_addressBookRecord->BirthdayMonth);
			$_birthdayNode->AppendAttribute('year', $_addressBookRecord->BirthdayYear);

			$_contactNode->AppendChild($_birthdayNode);

			$_personalNode = new XmlDomNode('personal');
			$_personalNode->AppendChild(new XmlDomNode('email', $_addressBookRecord->HomeEmail, true));
			$_personalNode->AppendChild(new XmlDomNode('street', $_addressBookRecord->HomeStreet, true));
			$_personalNode->AppendChild(new XmlDomNode('city', $_addressBookRecord->HomeCity, true));
			$_personalNode->AppendChild(new XmlDomNode('state', $_addressBookRecord->HomeState, true));
			$_personalNode->AppendChild(new XmlDomNode('zip', $_addressBookRecord->HomeZip, true));
			$_personalNode->AppendChild(new XmlDomNode('country', $_addressBookRecord->HomeCountry, true));
			$_personalNode->AppendChild(new XmlDomNode('fax', $_addressBookRecord->HomeFax, true));
			$_personalNode->AppendChild(new XmlDomNode('phone', $_addressBookRecord->HomePhone, true));
			$_personalNode->AppendChild(new XmlDomNode('mobile', $_addressBookRecord->HomeMobile, true));
			$_personalNode->AppendChild(new XmlDomNode('web', $_addressBookRecord->HomeWeb, true));

			$_contactNode->AppendChild($_personalNode);

			$_businessNode = new XmlDomNode('business');
			$_businessNode->AppendChild(new XmlDomNode('email', $_addressBookRecord->BusinessEmail, true));
			$_businessNode->AppendChild(new XmlDomNode('company', $_addressBookRecord->BusinessCompany, true));
			$_businessNode->AppendChild(new XmlDomNode('job_title', $_addressBookRecord->BusinessJobTitle, true));
			$_businessNode->AppendChild(new XmlDomNode('department', $_addressBookRecord->BusinessDepartment, true));
			$_businessNode->AppendChild(new XmlDomNode('office', $_addressBookRecord->BusinessOffice, true));
			$_businessNode->AppendChild(new XmlDomNode('street', $_addressBookRecord->BusinessStreet, true));
			$_businessNode->AppendChild(new XmlDomNode('city', $_addressBookRecord->BusinessCity, true));
			$_businessNode->AppendChild(new XmlDomNode('state', $_addressBookRecord->BusinessState, true));
			$_businessNode->AppendChild(new XmlDomNode('zip', $_addressBookRecord->BusinessZip, true));
			$_businessNode->AppendChild(new XmlDomNode('country', $_addressBookRecord->BusinessCountry, true));
			$_businessNode->AppendChild(new XmlDomNode('fax', $_addressBookRecord->BusinessFax, true));
			$_businessNode->AppendChild(new XmlDomNode('phone', $_addressBookRecord->BusinessPhone, true));
			$_businessNode->AppendChild(new XmlDomNode('mobile', $_addressBookRecord->BusinessMobile, true));
			$_businessNode->AppendChild(new XmlDomNode('web', $_addressBookRecord->BusinessWeb, true));

			$_contactNode->AppendChild($_businessNode);

			$_otherNode = new XmlDomNode('other');
			$_otherNode->AppendChild(new XmlDomNode('email', $_addressBookRecord->OtherEmail, true));
			$_otherNode->AppendChild(new XmlDomNode('notes', $_addressBookRecord->Notes, true));

			$_contactNode->AppendChild($_otherNode);

			$_groupsNode = new XmlDomNode('groups');

			$_groupsArray = $contactManager->GetGroupsOfContact($_idAddress);
			foreach ($_groupsArray as $_id => $_value)
			{
				$_groupNode = new XmlDomNode('group');
				$_groupNode->AppendAttribute('id', $_id);
				$_groupNode->AppendChild(new XmlDomNode('name', $_value, true));

				$_groupsNode->AppendChild($_groupNode);
				unset($_groupNode);
			}

			$_contactNode->AppendChild($_groupsNode);

			return $_contactNode;
		}

		return null;
	}

	/**
	 * @param XmlDomNode $_xmlObj
	 * @param AddressBookRecord $_addressBookRecord
	 * @param int $_accountId
	 */
	function UpdateContactFromRequest(&$_xmlObj, &$_addressBookRecord, $_accountId)
	{
		$_contactNode =& $_xmlObj->GetChildNodeByTagName('contact');

		$_account =& Account::LoadFromDb($_accountId);

		$_addressBookRecord->IdUser = $_account->IdUser;
		if (isset($_contactNode->Attributes['id']))
		{
			$_addressBookRecord->IdAddress = $_contactNode->Attributes['id'];
		}

		$_addressBookRecord->PrimaryEmail = $_contactNode->GetAttribute('primary_email', $_addressBookRecord->PrimaryEmail);
		$_addressBookRecord->UseFriendlyName = (bool) $_contactNode->GetAttribute('use_friendly_nm', $_addressBookRecord->UseFriendlyName);

		$_addressBookRecord->Title = $_contactNode->GetChildValueByTagName('title', true);
		$_addressBookRecord->FullName = $_contactNode->GetChildValueByTagName('fullname', true);
		$_addressBookRecord->FirstName = $_contactNode->GetChildValueByTagName('firstname', true);
		$_addressBookRecord->SurName = $_contactNode->GetChildValueByTagName('surname', true);
		$_addressBookRecord->NickName = $_contactNode->GetChildValueByTagName('nickname', true);

		$_birthdayNode =& $_contactNode->GetChildNodeByTagName('birthday');

		$_personalNode =& $_contactNode->GetChildNodeByTagName('personal');
		$_addressBookRecord->HomeEmail = $_personalNode->GetChildValueByTagName('email', true);
		$_addressBookRecord->HomeStreet = $_personalNode->GetChildValueByTagName('street', true);
		$_addressBookRecord->HomeCity = $_personalNode->GetChildValueByTagName('city', true);
		$_addressBookRecord->HomeState = $_personalNode->GetChildValueByTagName('state', true);
		$_addressBookRecord->HomeZip = $_personalNode->GetChildValueByTagName('zip', true);
		$_addressBookRecord->HomeCountry = $_personalNode->GetChildValueByTagName('country', true);
		$_addressBookRecord->HomeFax = $_personalNode->GetChildValueByTagName('fax', true);
		$_addressBookRecord->HomePhone = $_personalNode->GetChildValueByTagName('phone', true);
		$_addressBookRecord->HomeMobile = $_personalNode->GetChildValueByTagName('mobile', true);
		$_addressBookRecord->HomeWeb = $_personalNode->GetChildValueByTagName('web', true);

		$_businessNode =& $_contactNode->GetChildNodeByTagName('business', true);

		$_addressBookRecord->BusinessEmail = $_businessNode->GetChildValueByTagName('email', true);
		$_addressBookRecord->BusinessCompany = $_businessNode->GetChildValueByTagName('company', true);
		$_addressBookRecord->BusinessJobTitle = $_businessNode->GetChildValueByTagName('job_title', true);
		$_addressBookRecord->BusinessDepartment = $_businessNode->GetChildValueByTagName('department', true);
		$_addressBookRecord->BusinessOffice = $_businessNode->GetChildValueByTagName('office', true);
		$_addressBookRecord->BusinessStreet = $_businessNode->GetChildValueByTagName('street', true);
		$_addressBookRecord->BusinessCity = $_businessNode->GetChildValueByTagName('city', true);
		$_addressBookRecord->BusinessState = $_businessNode->GetChildValueByTagName('state', true);
		$_addressBookRecord->BusinessZip = $_businessNode->GetChildValueByTagName('zip', true);
		$_addressBookRecord->BusinessCountry = $_businessNode->GetChildValueByTagName('country', true);
		$_addressBookRecord->BusinessFax = $_businessNode->GetChildValueByTagName('fax', true);
		$_addressBookRecord->BusinessPhone = $_businessNode->GetChildValueByTagName('phone', true);
		$_addressBookRecord->BusinessMobile = $_businessNode->GetChildValueByTagName('modile', true);
		$_addressBookRecord->BusinessWeb = $_businessNode->GetChildValueByTagName('web', true);

		$_otherNode =& $_contactNode->GetChildNodeByTagName('other', true);
		$_addressBookRecord->OtherEmail = $_otherNode->GetChildValueByTagName('email', true);
		$_addressBookRecord->Notes = $_otherNode->GetChildValueByTagName('notes', true);

		if (isset($_birthdayNode->Attributes['day'], $_birthdayNode->Attributes['month'], $_birthdayNode->Attributes['year']))
		{
			$_addressBookRecord->BirthdayDay = $_birthdayNode->Attributes['day'];
			$_addressBookRecord->BirthdayMonth = $_birthdayNode->Attributes['month'];
			$_addressBookRecord->BirthdayYear = $_birthdayNode->Attributes['year'];
		}
		
		$_groupsNode =& $_contactNode->GetChildNodeByTagName('groups');

		$_groupsKeys = array_keys($_groupsNode->Children);
		foreach ($_groupsKeys as $_key)
		{
			$_gc =& $_groupsNode->Children[$_key];
			if (isset($_gc->Attributes['id']))
			{
				$_addressBookRecord->GroupsIds[] = $_gc->Attributes['id'];
			}
			unset($_gc);
		}
	}

	/**
	 * @param XmlDomNode $_xmlObj
	 * @param Account $_account
	 * @param bool $changeEmail = true;
	 */
	function UpdateAccountFromRequest(&$_xmlObj, &$_account, $_isUpdate = false)
	{
		$_accountNode = $_xmlObj->GetChildNodeByTagName('account');

		if ($_isUpdate && !$_account->AllowChangeSettings && isset($_accountNode->Attributes['use_friendly_nm']))
		{
			$_account->UseFriendlyName = (bool) $_accountNode->Attributes['use_friendly_nm'];
			$_account->FriendlyName = $_accountNode->GetChildValueByTagName('friendly_nm');
		}
		else
		{
			if (isset($_accountNode->Attributes['def_acct'], $_accountNode->Attributes['mail_protocol'],
					$_accountNode->Attributes['mail_inc_port'], $_accountNode->Attributes['mail_out_port'],
					$_accountNode->Attributes['mail_out_auth'], $_accountNode->Attributes['use_friendly_nm'],
					$_accountNode->Attributes['mails_on_server_days'], $_accountNode->Attributes['mail_mode'],
					$_accountNode->Attributes['getmail_at_login']))
			{
				$_account->DefaultAccount = (bool) $_accountNode->Attributes['def_acct'];
				$_account->MailProtocol = $_accountNode->Attributes['mail_protocol'];
				$_account->MailIncPort = $_accountNode->Attributes['mail_inc_port'];
				$_account->MailOutPort = $_accountNode->Attributes['mail_out_port'];
				$_account->MailOutAuthentication = $_accountNode->Attributes['mail_out_auth'];
				$_account->UseFriendlyName = (bool) $_accountNode->Attributes['use_friendly_nm'];
				$_account->MailsOnServerDays = $_accountNode->Attributes['mails_on_server_days'];
				$_account->MailMode = $_accountNode->Attributes['mail_mode'];
				$_account->GetMailAtLogin = (bool) $_accountNode->Attributes['getmail_at_login'];
			}
			$_account->FriendlyName = $_accountNode->GetChildValueByTagName('friendly_nm');
			
			$email = $_accountNode->GetChildValueByTagName('email');

			/* custom class */
			wm_Custom::StaticUseMethod('ChangeFakeEmailToAccountEmail', array(&$email));

			$_account->Email = $email;

			$_account->MailIncHost = $_accountNode->GetChildValueByTagName('mail_inc_host');
			$_account->MailIncLogin = $_accountNode->GetChildValueByTagName('mail_inc_login');

			$_mailIncPass = $_accountNode->GetChildValueByTagName('mail_inc_pass');
			if ($_mailIncPass != DUMMYPASSWORD)
			{
				$_account->MailIncPassword = $_mailIncPass;
			}

			$_account->MailOutHost = $_accountNode->GetChildValueByTagName('mail_out_host');
			$_account->MailOutLogin = $_accountNode->GetChildValueByTagName('mail_out_login');

			$_mailOutPass = $_accountNode->GetChildValueByTagName('mail_out_pass');
			if ($_mailOutPass != DUMMYPASSWORD)
			{
				$_account->MailOutPassword = $_mailOutPass;
			}
		}
	}

	/**
	 * @param XmlDomNode $_xmlObj
	 * @return Filter
	 */
	function &GetFilterFromRequest(&$_xmlObj)
	{
		$_filterNode =& $_xmlObj->GetChildNodeByTagName('filter');

		$_filter = null;

		if (isset($_filterNode->Attributes['id_acct'], $_filterNode->Attributes['field'], $_filterNode->Attributes['condition'],
			$_filterNode->Attributes['action'], $_filterNode->Attributes['id_folder']))
		{
			$_filter = new Filter();

			$_filter->IdAcct = $_filterNode->Attributes['id_acct'];
			if (isset($_filterNode->Attributes['id']))
			{
				$_filter->Id = $_filterNode->Attributes['id'];
			}
			$_filter->Field = $_filterNode->Attributes['field'];
			$_filter->Condition = $_filterNode->Attributes['condition'];
			$_filter->Action = $_filterNode->Attributes['action'];
			$_filter->IdFolder = $_filterNode->Attributes['id_folder'];
			$_filter->Applied = (isset($_filterNode->Attributes['applied']) && $_filterNode->Attributes['applied'] == 1);
			$_filter->Filter = $_filterNode->Value;
		}

		return $_filter;
	}


	/**
	 * @param XmlDomNode $_filterNode
	 * @return Filter
	 */
	function &GetOneFilterFromRequest($_filterNode, $idAcct)
	{
		$_filter = null;

		if (isset($_filterNode->Attributes['field'], $_filterNode->Attributes['condition'],
			$_filterNode->Attributes['action'], $_filterNode->Attributes['id_folder']))
		{
			$_filter = new Filter();

			$_filter->IdAcct = $idAcct;
			if (isset($_filterNode->Attributes['id']))
			{
				$_filter->Id = $_filterNode->Attributes['id'];
			}
			$_filter->Field = $_filterNode->Attributes['field'];
			$_filter->Condition = $_filterNode->Attributes['condition'];
			$_filter->Action = $_filterNode->Attributes['action'];
			$_filter->IdFolder = $_filterNode->Attributes['id_folder'];
			$_filter->Applied = (isset($_filterNode->Attributes['applied']) && $_filterNode->Attributes['applied'] == 1);
			$_filter->Filter = $_filterNode->Value;
		}

		return $_filter;
	}

	/**
	 * @param int $_accountId
	 * @param XmlDomNode $_xmlRes
	 */
	function GetFiltersList(&$_xmlRes, $_accountId)
	{
		if (!USE_DB)
		{
			$_filtersNode = new XmlDomNode('filters');
			$_filtersNode->AppendAttribute('id_acct', $_accountId);
			$_xmlRes->XmlRoot->AppendChild($_filtersNode);
			return true;
		}
		
		$_null = null;
		$_dbStorage =& DbStorageCreator::CreateDatabaseStorage($_null);
		if ($_dbStorage->Connect())
		{
			$_filters =& $_dbStorage->SelectFilters($_accountId);
			if (null !== $_filters)
			{
				$_filtersNode = new XmlDomNode('filters');
				$_filtersNode->AppendAttribute('id_acct', $_accountId);

				$_filterKeys = array_keys($_filters->Instance());
				foreach ($_filterKeys as $_key)
				{
					$_filter =& $_filters->Get($_key);
					if ($_filter->IsSystem)
					{
						continue;
					}

					$_filterNode = new XmlDomNode('filter', $_filter->Filter, true);
					$_filterNode->AppendAttribute('id', $_filter->Id);
					$_filterNode->AppendAttribute('field', $_filter->Field);
					$_filterNode->AppendAttribute('condition', $_filter->Condition);
					$_filterNode->AppendAttribute('action', $_filter->Action);
					$_filterNode->AppendAttribute('id_folder', $_filter->IdFolder);
					$_filterNode->AppendAttribute('applied', (int) $_filter->Applied);
					$_filtersNode->AppendChild($_filterNode);
					unset($_filterNode, $_filter);
				}

				$_xmlRes->XmlRoot->AppendChild($_filtersNode);
			}
			else
			{
				CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_FILTER_LIST, $_xmlRes);
			}
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
		}
	}

	/**
	 * @param Account $_account
	 * @param XmlDomNode $_xmlObj
	 * @param XmlDomNode $_xmlRes
	 * @param int $_newAddrId = -1
	 */
	function GetContactList(&$_account, &$_settings, &$_xmlObj, &$_xmlRes, $_newAddrId = -1)
	{
		$contactManager =& ContactCreator::CreateContactStorage($_account, $_settings);
		if ($contactManager)
		{
			$_pageNumber = $_xmlObj->GetParamValueByName('page');
			$_sortField = $_xmlObj->GetParamValueByName('sort_field');
			$_sortOrder = (bool) $_xmlObj->GetParamValueByName('sort_order');

			$_idGroup = $_xmlObj->GetParamValueByName('id_group');
			$_lookForNode = $_xmlObj->XmlRoot->GetChildNodeByTagName('look_for');
			$_lookForField = $_xmlObj->XmlRoot->GetChildValueByTagName('look_for', true);
			$_lookForType = 0;

			if ($_lookForNode)
			{
				$_lookForType = isset($_lookForNode->Attributes['type']) ? (int) $_lookForNode->Attributes['type'] : 0;
			}
			
			$_countArray = $contactManager->GetContactsAndGroupsCount($_idGroup, $_lookForType, $_lookForField);
			
			$_contactsNode = new XmlDomNode('contacts_groups');
			$_contactsNode->AppendAttribute('contacts_count', $_countArray[0]);
			$_contactsNode->AppendAttribute('groups_count', $_countArray[1]);

			$_contactsNode->AppendAttribute('page', $_pageNumber);
			$_contactsNode->AppendAttribute('sort_field', $_sortField);
			$_contactsNode->AppendAttribute('sort_order', (int) $_sortOrder);

			$_contactsNode->AppendAttribute('id_group', (int) $_idGroup);
			$_contactsNode->AppendAttribute('added_contact_id', (int) $_newAddrId);

			$_newLookForNode = new XmlDomNode('look_for', $_lookForField, true);
			$_newLookForNode->AppendAttribute('type', $_lookForType);

			$_contactsNode->AppendChild($_newLookForNode);

			$_contacts = $contactManager->GetContactsAndGroups($_countArray, $_pageNumber, $_sortField, $_sortOrder, $_idGroup, $_lookForType, $_lookForField);

			$aEmailsInList = array();
			if (null != $_contacts)
			{
				$_contactsKeys = array_keys($_contacts->Instance());
				foreach ($_contactsKeys as $_key)
				{
					$_contact =& $_contacts->Get($_key);

					$_contactNode = new XmlDomNode('contact_group');
					$_contactNode->AppendAttribute('id', $_contact->Id);
					$_contactNode->AppendAttribute('is_group', (int) $_contact->IsGroup);
					$_contactNode->AppendChild(new XmlDomNode('name', $_contact->Name, true));

					if ($_contact->IsGroup)
					{
						$_emailsOfGroup = '';
						$_groupContacts = $contactManager->GetContactsOfGroup($_contact->Id);

						if ($_groupContacts)
						{
							for ($_i = 0, $_c = $_groupContacts->Count(); $_i < $_c; $_i++)
							{
								$_contactOfGroup =& $_groupContacts->Get($_i);
								if (strlen($_contactOfGroup->Email) > 0)
								{
									$_emailsOfGroup .= ((strlen($_contactOfGroup->Name) > 0) && ($_contactOfGroup->UseFriendlyName))
										? '"'.$_contactOfGroup->Name.'" <'.$_contactOfGroup->Email . '>, '
										: $_contactOfGroup->Email . ', ';
								}
							}
						}

						$sEmailToAdd = trim(trim($_emailsOfGroup), ',');
						unset($_groupContacts);
					}
					else
					{
						$sEmailToAdd = trim(trim($_contact->Email), ',');
					}
					
					$_contactNode->AppendChild(new XmlDomNode('email', $sEmailToAdd, true));

					if (1 === $_lookForType)
					{
						if (isset($aEmailsInList[$_contact->Name.$sEmailToAdd]))
						{
							unset($_contactNode);
							continue;
						}
						else
						{
							$aEmailsInList[$_contact->Name.$sEmailToAdd] = true;
						}
					}
					$_contactsNode->AppendChild($_contactNode);
					unset($_contactNode);
				}
			}

			$_xmlRes->XmlRoot->AppendChild($_contactsNode);

			if ($_newAddrId > 0)
			{
				$_bigContactNode = CXmlProcessing::GetContactNodeFromAddressBookRecord($_account, $_settings, $_newAddrId);
				if (null != $_bigContactNode)
				{
					$_xmlRes->XmlRoot->AppendChild($_bigContactNode);
				}
			}
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
		}
	}

	/**
	 * @param XmlDomNode $_xmlRes
	 * @param Account $_account
	 */
	function GetAccount(&$_xmlRes, &$_account, &$_dbStorage)
	{
		if ($_account)
		{
			$_accountNode = new XmlDomNode('account');
			$_accountNode->AppendAttribute('id', $_account->Id);
			$_accountNode->AppendAttribute('linked', (int) ($_account->IdDomain > 0));
			$_accountNode->AppendAttribute('def_acct', (int) $_account->DefaultAccount);
			$_accountNode->AppendAttribute('def_order', $_account->DefaultOrder);
			$_accountNode->AppendAttribute('mail_protocol', $_account->MailProtocol);
			$_accountNode->AppendAttribute('mail_inc_port', $_account->MailIncPort);
			$_accountNode->AppendAttribute('mail_out_port', $_account->MailOutPort);
			$_accountNode->AppendAttribute('mail_out_auth', (int) $_account->MailOutAuthentication);
	
			$_accountNode->AppendAttribute('use_friendly_nm', (int) $_account->UseFriendlyName);
			$_accountNode->AppendAttribute('mails_on_server_days', $_account->MailsOnServerDays);
			$_accountNode->AppendAttribute('mail_mode', $_account->MailMode);
			$_accountNode->AppendAttribute('getmail_at_login', (int) $_account->GetMailAtLogin);
			
			$_accountNode->AppendAttribute('signature_opt', (int) $_account->SignatureOptions);
			$_accountNode->AppendAttribute('signature_type', (int) $_account->SignatureType);
			$_accountNode->AppendAttribute('save_mail', (int) $_account->SaveInSent);
			$_accountNode->AppendAttribute('size', $_account->MailboxSize);
			$_accountNode->AppendAttribute('is_internal', (int) $_account->IsInternal);
	
			if ($_account->MailProtocol == MAILPROTOCOL_POP3)
			{
				$syncType = $_dbStorage->GetFolderSyncTypeByIdAcct($_account->Id, FOLDERTYPE_Inbox);
				$_accountNode->AppendAttribute('inbox_sync_type', $syncType);
			}

			$email = $_account->Email;
			/* custom class */
			wm_Custom::StaticUseMethod('ChangeAccountEmailToFake', array(&$email));
			
			$_accountNode->AppendChild(new XmlDomNode('email', $email, true));

			$_accountNode->AppendChild(new XmlDomNode('friendly_name', $_account->FriendlyName, true));
			$_accountNode->AppendChild(new XmlDomNode('mail_inc_host', $_account->MailIncHost, true));
			$_accountNode->AppendChild(new XmlDomNode('mail_inc_login', $_account->MailIncLogin, true));
			$_accountNode->AppendChild(new XmlDomNode('mail_inc_pass', ($_account->MailIncPassword === '') ? '' : DUMMYPASSWORD, true));
			$_accountNode->AppendChild(new XmlDomNode('mail_out_host', $_account->MailOutHost, true));
			$_accountNode->AppendChild(new XmlDomNode('mail_out_login', $_account->MailOutLogin, true));
			$_accountNode->AppendChild(new XmlDomNode('mail_out_pass', ($_account->MailOutPassword === '') ? '' : DUMMYPASSWORD, true));
			$_accountNode->AppendChild(new XmlDomNode('signature', $_account->Signature, true));
			
			$_xmlRes->AppendChild($_accountNode);
			unset($_accountNode);
		}
	}
	
	/**
	 * @param XmlDomNode $_xmlRes
	 * @param Account $_account
	 * @param int $_lastId
	 * @param int $_currId
	 */
	function GetAccountList($_dbStorage, &$_xmlRes, $_account, $_lastId, $_currId = '')
	{
		$_currId = ($_currId) ? $_currId : $_account->Id;

		if ($_dbStorage->Connect())
		{
			$_accounts = null;
			if (USE_DB)
			{
				$_accounts =& $_dbStorage->SelectAccounts($_account->IdUser);
			}
			else
			{
				$_accounts = array($_account->Id => array());
			}
			
			if (null !== $_accounts)
			{
				$_acctsNode = new XmlDomNode('accounts');
				$_acctsNode->AppendAttribute('last_id', $_lastId);
				$_acctsNode->AppendAttribute('curr_id', $_currId);
				foreach ($_accounts as $_acct_id => $_acctArray)
				{
					$_f_account = Account::LoadFromDb($_acct_id, true, false);
					CXmlProcessing::GetAccount($_acctsNode, $_f_account, $_dbStorage);
					unset($_f_account);
				}
				$_xmlRes->XmlRoot->AppendChild($_acctsNode);
			}
			else
			{
				CXmlProcessing::PrintErrorAndExit(PROC_CANT_GET_ACCT_LIST, $_xmlRes);
			}
		}
		else
		{
			CXmlProcessing::PrintErrorAndExit(getGlobalError(), $_xmlRes);
		}
	}

	/**
	 * @param FolderCollection $_folders
	 * @param XmlDomNode $_nodeTree
	 * @param MailProcessor $_processor
	 */
	function GetFoldersTreeXml(&$_folders, &$_nodeTree, &$_processor)
	{
		for ($_i = 0, $_count = $_folders->Count(); $_i < $_count; $_i++)
		{
			$_folder =& $_folders->Get($_i);
			$_folderNode = new XmlDomNode('folder');
			$_folderNode->AppendAttribute('id', $_folder->IdDb);
			$_folderNode->AppendAttribute('id_parent', $_folder->IdParent);
			$_folderNode->AppendAttribute('type', $_folder->Type);
			$_folderNode->AppendAttribute('sync_type', $_folder->SyncType);
			$_folderNode->AppendAttribute('hide', (int) $_folder->Hide);
			$_folderNode->AppendAttribute('fld_order', (int) $_folder->FolderOrder);

			if ($_folder->SyncType == FOLDERSYNC_DirectMode)
			{
				$_processor->GetFolderMessageCount($_folder);
			}

			$_folderNode->AppendAttribute('count', $_folder->MessageCount);
			$_folderNode->AppendAttribute('count_new', $_folder->UnreadMessageCount);
			$_folderNode->AppendAttribute('size', $_folder->Size);

			if (ConvertUtils::IsLatin($_folder->Name))
			{
				$_folderNode->AppendChild(new XmlDomNode('name',
					ConvertUtils::ConvertEncoding($_folder->Name,
					CPAGE_UTF7_Imap, CPAGE_UTF8), true));
			}
			else
			{
				$_folderNode->AppendChild(new XmlDomNode('name',
					ConvertUtils::ConvertEncoding($_folder->Name,
					$_processor->_account->DefaultIncCharset, CPAGE_UTF8), true));
			}

			$_folderNode->AppendChild(new XmlDomNode('full_name', $_folder->FullName, true));

			if ($_folder->SubFolders != null && $_folder->SubFolders->Count() > 0)
			{
				$_foldersNode = new XmlDomNode('folders');
				CXmlProcessing::GetFoldersTreeXml($_folder->SubFolders, $_foldersNode, $_processor);
				$_folderNode->AppendChild($_foldersNode);
				unset($_foldersNode);
			}

			$_nodeTree->AppendChild($_folderNode);
			unset($_folderNode, $_folder);
		}
	}

	function GetMessageNode(&$_xmlRes, &$_message, &$_folder, &$_processor, &$_account, &$_settings, $_mode, $_charsetNum, $_isFromSave)
	{
		$_safety = true;
		$_messageNode = new XmlDomNode('message');

		$_msgId = $_message->IdMsg;
		$_msgUid = $_message->Uid;

		$_messageInfo = new CMessageInfo();
		$_messageInfo->SetInfo($_msgId, $_msgUid, $_folder->IdDb, $_folder->FullName);

		$_messageClassType = $_message->TextBodies->ClassType();

		$_messageNode->AppendAttribute('id', $_msgId);
		$_messageNode->AppendAttribute('size', $_message->GetMailSize());
		$_messageNode->AppendAttribute('html', (int) (($_messageClassType & 2) == 2));
		$_messageNode->AppendAttribute('plain', (int) (($_messageClassType & 1) == 1));
		$_messageNode->AppendAttribute('priority', $_message->GetPriorityStatus());
		$_messageNode->AppendAttribute('mode', $_mode);
		$_messageNode->AppendAttribute('charset', $_charsetNum);
		$_messageNode->AppendAttribute('has_charset', (int) $_message->HasCharset);
		$_messageNode->AppendAttribute('downloaded', (int) $_message->Downloaded);
		$_messageNode->AppendAttribute('sensivity', $_message->GetSensitivity());

		$maf =& MessageActionFilters::CreateInstance();
		$mafNoReply = $maf->GetNoReplyEmails();
		$mafNoReplyAll = $maf->GetNoReplyAllEmails();
		$mafNoForward = $maf->GetNoForwardEmails();

		$fromEmail = $_message->GetFrom();
		$fromEmail = $fromEmail->Email;

		$_textCharset = $_message->GetTextCharset();
		$_rtl = 0;
		if (null !== $_textCharset)
		{
			switch (ConvertUtils::GetCodePageNumber($_textCharset))
			{
				case 1255:
				case 1256:
				case 28596:
				case 28598:
					$_rtl = 1;
					break;
			}
		}
		$_messageNode->AppendAttribute('rtl', $_rtl);
		$_messageNode->AppendChild(new XmlDomNode('uid', $_msgUid, true));

		$_folderNode = new XmlDomNode('folder', $_folder->FullName, true);
		$_folderNode->AppendAttribute('id', $_folder->IdDb);
		$_messageNode->AppendChild($_folderNode);

		$_signature_html = '';
		$_signature_plain = '';

		if ($_account->SignatureOptions == SIGNATURE_OPTION_AddToAll)
		{
			if ($_account->SignatureType == 1)
			{
				$_signature_html = '<br />'.$_account->Signature;

				require_once WM_ROOTPATH.'libs/class_converthtml.php';
				$_pars = new convertHtml($_account->Signature, false);
				$_signature_plain = CRLF.$_pars->get_text();
			}
			else
			{
				$_signature_plain = CRLF.$_account->Signature;
				$_signature_html = '<br />'.nl2br($_account->Signature);
			}

			$_signature_plain = ConvertUtils::WMHtmlSpecialChars($_signature_plain);
		}

		$_accountOffset = ($_settings->AllowUsersChangeTimeZone)
			? $_account->GetDefaultTimeOffset()
			: $_account->GetDefaultTimeOffset($_settings->DefaultTimeZone);

		if (($_mode & 1) == 1)
		{
			$_headersNode = new XmlDomNode('headers');
			$_fromNode = new XmlDomNode('from');

			$_id_addr = -1;
			$_from4search =& $_message->GetFrom();
			if ($_from4search && USE_DB)
			{
				$_id_addr = $_processor->DbStorage->GetContactIdByEmail($_from4search->Email, $_account->IdUser);
			}

			if ($_id_addr > 0)
			{
				$_fromNode->AppendAttribute('contact_id', $_id_addr);
				$_bigContactNode = CXmlProcessing::GetContactNodeFromAddressBookRecord($_account, $_settings, $_id_addr);
				if (null != $_bigContactNode)
				{
					$_xmlRes->XmlRoot->AppendChild($_bigContactNode);
				}
			}

			$_fromNode->AppendChild(new XmlDomNode('short', WebMailMessage::ClearForSend(trim($_from4search->DisplayName)), true));
			$_fromNode->AppendChild(new XmlDomNode('full', $_from4search->ToDecodedString(), true));
			$_headersNode->AppendChild($_fromNode);

			$_headersNode->AppendChild(new XmlDomNode('to', $_message->GetToAsString(true), true));
			$_headersNode->AppendChild(new XmlDomNode('cc', $_message->GetCcAsString(true), true));
			$_headersNode->AppendChild(new XmlDomNode('bcc', $_message->GetBccAsString(true), true));

			$_headersNode->AppendChild(new XmlDomNode('reply_to', $_message->GetReplyToAsString(true), true));
			$_headersNode->AppendChild(new XmlDomNode('subject', $_message->GetSubject(true), true));
			$_mailConfirmation = $_message->GetReadMailConfirmationAsString();
			if (strlen($_mailConfirmation) > 0)
			{
				$_headersNode->AppendChild(new XmlDomNode('mailconfirmation', $_mailConfirmation, true));
			}

			$_date =& $_message->GetDate();
			$_date->FormatString = $_account->DefaultDateFormat;
			$_date->TimeFormat = $_account->DefaultTimeFormat;

			$_headersNode->AppendChild(new XmlDomNode('short_date', $_date->GetFormattedShortDate($_accountOffset), true));
			$_headersNode->AppendChild(new XmlDomNode('full_date', $_date->GetFormattedFullDate($_accountOffset), true));
			$_headersNode->AppendChild(new XmlDomNode('time', $_date->GetFormattedTime($_accountOffset), true));

			$_messageNode->AppendChild($_headersNode);
		}

		$html_part = '';
		if (($_mode & 2) == 2 && ($_messageClassType & 2) == 2)
		{
			$html_part = ConvertUtils::ReplaceJSMethod(
				$_message->GetCensoredHtmlWithImageLinks(true, $_messageInfo));

			if (($_account->ViewMode == VIEW_MODE_PREVIEW_PANE_NO_IMG ||
				$_account->ViewMode == VIEW_MODE_WITHOUT_PREVIEW_PANE_NO_IMG) && !$_isFromSave)
			{
				$html_part = ConvertUtils::HtmlBodyWithoutImages($html_part);
				if (isset($GLOBALS[GL_WITHIMG]) && $GLOBALS[GL_WITHIMG])
				{
					$GLOBALS[GL_WITHIMG] = false;
					$_safety = false;
				}
			}
		}

		$modified_plain_text = '';
		if (($_mode & 4) == 4 || ($_mode & 2) == 2 && ($_messageClassType & 2) != 2)
		{
			$modified_plain_text = $_message->GetCensoredTextBody(true);
		}

		if (($_mode & 8) == 8)
		{
			if (($_account->ViewMode == VIEW_MODE_PREVIEW_PANE_NO_IMG ||
				$_account->ViewMode == VIEW_MODE_WITHOUT_PREVIEW_PANE_NO_IMG) && !$_isFromSave)
			{
				$_messageNode->AppendChild(new XmlDomNode('reply_html',
					ConvertUtils::AddToLinkMailToCheck(
						ConvertUtils::HtmlBodyWithoutImages(ConvertUtils::ReplaceJSMethod($_signature_html.$_message->GetRelpyAsHtml(true, $_accountOffset, $_messageInfo)))), true, true));
						
				if (isset($GLOBALS[GL_WITHIMG]) && $GLOBALS[GL_WITHIMG])
				{
					$GLOBALS[GL_WITHIMG] = false;
					$_safety =  false;
				}
			}
			else
			{
				$_messageNode->AppendChild(new XmlDomNode('reply_html', 
					ConvertUtils::AddToLinkMailToCheck(
						ConvertUtils::ReplaceJSMethod($_signature_html.$_message->GetRelpyAsHtml(true, $_accountOffset, $_messageInfo))), true, true));
			}
		}

		if (($_mode & 16) == 16)
		{
			$_messageNode->AppendChild(new XmlDomNode('reply_plain', 
				ConvertUtils::AddToLinkMailToCheck(
					$_signature_plain.$_message->GetRelpyAsPlain(true, $_accountOffset)), true, true));
		}

		if (($_mode & 32) == 32)
		{
			if (($_account->ViewMode == VIEW_MODE_PREVIEW_PANE_NO_IMG ||
				$_account->ViewMode == VIEW_MODE_WITHOUT_PREVIEW_PANE_NO_IMG) && !$_isFromSave)
			{
				$_messageNode->AppendChild(new XmlDomNode('forward_html',
					ConvertUtils::AddToLinkMailToCheck(
						ConvertUtils::HtmlBodyWithoutImages(ConvertUtils::ReplaceJSMethod($_signature_html.$_message->GetRelpyAsHtml(true, $_accountOffset, $_messageInfo)))), true, true));
						
				if (isset($GLOBALS[GL_WITHIMG]) && $GLOBALS[GL_WITHIMG])
				{
					$GLOBALS[GL_WITHIMG] = false;
					$_safety =  false;
				}
			}
			else
			{
				$_messageNode->AppendChild(new XmlDomNode('forward_html', 
					ConvertUtils::AddToLinkMailToCheck(
						ConvertUtils::ReplaceJSMethod($_signature_html.$_message->GetRelpyAsHtml(true, $_accountOffset, $_messageInfo))), true, true));
			}
		}

		if (($_mode & 64) == 64)
		{
			$_messageNode->AppendChild(new XmlDomNode('forward_plain', 
				ConvertUtils::AddToLinkMailToCheck($_signature_plain.$_message->GetRelpyAsPlain(true, $_accountOffset)), true, true));
		}

		if (($_mode & 128) == 128)
		{
			$_messageNode->AppendChild(new XmlDomNode('full_headers',
				$_message->ClearForSend(ConvertUtils::ConvertEncoding(
				$_message->OriginalHeaders, $GLOBALS[MailInputCharset], $_account->GetUserCharset())), true, true));
		}

		$_messageNode->AppendAttribute('safety', (int) $_safety);
		$_msqAttachLine = 'msg_id='.$_msgId.'&msg_uid='.urlencode($_msgUid).
			'&folder_id='.$_folder->IdDb.'&folder_fname='.urlencode($_folder->FullName);

		$addAttachArray = array();
		if (($_mode & 256) == 256 || ($_mode & 8) == 8 || ($_mode & 16) == 16 || ($_mode & 32) == 32 || ($_mode & 64) == 64)
		{
			$_attachments =& $_message->Attachments;
			if ($_attachments && $_attachments->Count() > 0)
			{
				$tempFiles =& CTempFiles::CreateInstance($_account);
				$_attachmentsNode = new XmlDomNode('attachments');
				$_attachmentsKeys = array_keys($_attachments->Instance());
				foreach ($_attachmentsKeys as $_key)
				{
					$attachArray = array();
					
					$_attachment =& $_attachments->Get($_key);
					$_tempname = $_message->IdMsg.'-'.$_key.'_'.ConvertUtils::ClearFileName($_attachment->GetTempName());
					$_filename = ConvertUtils::ClearFileName(ConvertUtils::ClearUtf8($_attachment->GetFilenameFromMime(), $GLOBALS[MailInputCharset], $_account->GetUserCharset()));
					$_size = 0;
					$_isBodyStructureAttachment = false;
					if ($_attachment->MimePart && $_attachment->MimePart->BodyStructureIndex !== null && $_attachment->MimePart->BodyStructureSize !== null)
					{
						$_isBodyStructureAttachment = true;
						$_size = $_attachment->MimePart->BodyStructureSize;
					}
					else
					{
						$_size = $tempFiles->SaveFile($_tempname, $_attachment->GetBinaryBody()); 
						$_size = ($_size < 0) ? 0 : $_size;
					}

					$attachArray['name'] = $_filename;
					$attachArray['tempname'] = $_tempname;
					$attachArray['size'] = $_size;

					$_bodyStructureUrlAdd = '';
					if ($_isBodyStructureAttachment)
					{
						$_bodyStructureUrlAdd = 'bsi='.urlencode($_attachment->MimePart->BodyStructureIndex);
						if ($_attachment->MimePart->BodyStructureEncode !== null && strlen($_attachment->MimePart->BodyStructureEncode) > 0)
						{
							$_bodyStructureUrlAdd .= '&bse='.urlencode(ConvertUtils::GetBodyStructureEncodeType($_attachment->MimePart->BodyStructureEncode));
						}
					}

					$_attachNode = new XmlDomNode('attachment');
					$_attachNode->AppendAttribute('size', $_size);
					$_attachNode->AppendAttribute('inline', ($_attachment->IsInline) ? '1': '0');

					$_attachNode->AppendChild(new XmlDomNode('filename', $_filename, true));

					$viewUrl = (substr(strtolower($_filename), -4) == '.eml')
						? 'message-view.php?type='.MESSAGE_VIEW_TYPE_ATTACH.'&tn='.urlencode($_tempname)
						: 'view-image.php?img&tn='.urlencode($_tempname).'&filename='.urlencode($_filename);

					if ($_isBodyStructureAttachment)
					{
						$viewUrl .= '&'.$_bodyStructureUrlAdd.'&'.$_msqAttachLine;
					}

					$_attachNode->AppendChild(new XmlDomNode('view', $viewUrl, true));

					$linkUrl = 'attach.php?tn='.urlencode($_tempname);
					if ($_isBodyStructureAttachment)
					{
						$linkUrl .= '&'.$_bodyStructureUrlAdd.'&'.$_msqAttachLine;
					}

					$downloadUrl = $linkUrl.'&filename='.urlencode($_filename);
					
					$attachArray['download'] = $downloadUrl;
					$attachArray['link'] = $linkUrl;

					$_attachNode->AppendChild(new XmlDomNode('download', $downloadUrl, true));
					$_attachNode->AppendChild(new XmlDomNode('tempname', $_tempname, true));
					$mime_type = ConvertUtils::GetContentTypeFromFileName($_filename);
					$_attachNode->AppendChild(new XmlDomNode('mime_type', $mime_type, true));

					$attachArray['mime_type'] = $mime_type;
					$attachArray['download'] = $downloadUrl;

					$addAttachArray[] = $attachArray;
					$_attachmentsNode->AppendChild($_attachNode);
					unset($_attachment, $_attachNode, $attachArray);
				}

				$_messageNode->AppendChild($_attachmentsNode);
			}
		}

		ChangeHtmlTextFromAttachment($html_part, $modified_plain_text, $addAttachArray);

		if (($_mode & 2) == 2 && ($_messageClassType & 2) == 2)
		{
			$_messageNode->AppendChild(new XmlDomNode('html_part',
				ConvertUtils::AddToLinkMailToCheck($html_part), true, true));
		}

		if (($_mode & 4) == 4 || ($_mode & 2) == 2 && ($_messageClassType & 2) != 2)
		{
			$_messageNode->AppendChild(new XmlDomNode('modified_plain_text',
				ConvertUtils::AddToLinkMailToCheck($modified_plain_text), true, true));
		}

		if (($_mode & 512) == 512)
		{
			$_messageNode->AppendChild(new XmlDomNode('unmodified_plain_text', $_message->GetNotCensoredTextBody(true), true, true));
		}

		$_messageNode->AppendChild(new XmlDomNode('save_link', 'attach.php?'.$_msqAttachLine, true));
		$_messageNode->AppendChild(new XmlDomNode('print_link', 'message-view.php?type='.MESSAGE_VIEW_TYPE_PRINT.'&'.$_msqAttachLine.'&charset='.$_charsetNum, true));

		$_messageNode->AppendAttribute('no_reply', (count($mafNoReply) > 0 && in_array($fromEmail, $mafNoReply)) ? '1' : '0');
		$_messageNode->AppendAttribute('no_reply_all', (count($mafNoReplyAll) > 0 && in_array($fromEmail, $mafNoReplyAll)) ? '1' : '0');
		$_messageNode->AppendAttribute('no_forward', (count($mafNoForward) > 0 && in_array($fromEmail, $mafNoForward)) ? '1' : '0');

		$_xmlRes->XmlRoot->AppendChild($_messageNode);
	}

	/**
	 * @param XmlDomNode $_mNode
	 * @param MailProcessor $_processor
	 */
	function ReplySetFlag(&$_mNode, &$_processor)
	{
		$_replyNode =& $_mNode->GetChildNodeByTagName('reply_message');
		if ($_replyNode && isset($_replyNode->Attributes['action']))
		{
			$_rFlag = null;
			switch ($_replyNode->Attributes['action'])
			{
				case 'reply':
					$_rFlag = MESSAGEFLAGS_Answered;
					break;
				case 'forward':
					$_rFlag = MESSAGEFLAGS_Forwarded;
					break;
			}

			if (null !== $_rFlag && isset($_replyNode->Attributes['id']))
			{
				$_rId = (int) $_replyNode->Attributes['id'];
				$_rUid = $_replyNode->GetChildValueByTagName('uid', true);
				$_rFolderNode =& $_replyNode->GetChildNodeByTagName('folder');
				if ($_rFolderNode && isset($_rFolderNode->Attributes['id']))
				{
					$_rFolderId = (int) $_rFolderNode->Attributes['id'];
					$_rFolderFullName = $_rFolderNode->GetChildValueByTagName('full_name', true);

					$_processor->SetFlagFromReply($_rId, $_rUid, $_rFolderId, $_rFolderFullName, $_rFlag);
				}
			}
		}
	}

	/**
	 * @param	int	$id
	 * @return	bool
	 */
	function CheckAccountAccess($_id, $_xmlRes = null)
	{
		$_result = User::AccountAccess($_id);
		if (!$_result && $_xmlRes !== null)
		{
			CXmlProcessing::PrintErrorAndExit(PROC_WRONG_ACCT_ACCESS, $_xmlRes);
		}

		return $_result;
	}

	/**
	 * @param XmlDomNode $_xmlRes
	 * @param int $_idAcct
	 * @param bool $_getSignature = null
	 * @param bool $_getColumns = null
	 * @return Account
	 */
	function &AccountCheckAndLoad($_xmlRes, $_idAcct, $_getSignature = null, $_getColumns = null)
	{
		if ($_getSignature === null)
		{
			$_getSignature = true;
		}
		if ($_getColumns === null)
		{
			$_getColumns = true;
		}
		$_account =& Account::LoadFromDb($_idAcct, $_getSignature, $_getColumns);
		if (!$_account)
		{
			CXmlProcessing::PrintErrorAndExit('', $_xmlRes, 2);
		}
		return $_account;
	}

	/**
	 * @param XmlDocument $_xmlObj
	 */
	function PrintXML(&$_xmlObj, $_startTime = null)
	{
		global $BackgroundXmlParam;
		
		if ($_xmlObj)
		{
			if (isset($_SESSION[ISINFOERROR], $_SESSION[INFORMATION]) && $_SESSION[ISINFOERROR])
			{
				$_xmlNode = new XmlDomNode('error', $_SESSION[INFORMATION], true);
				$_xmlObj->XmlRoot->AppendChild($_xmlNode);

				unset($_SESSION[ISINFOERROR], $_SESSION[INFORMATION]);
			}

			if ($BackgroundXmlParam === 1)
			{
				$_xmlObj->XmlRoot->AppendAttribute('background', 1);
			}

			echo $_xmlObj->ToString();
			if (null !== $_startTime)
			{
				$_log =& CLog::CreateInstance();
				$_log->WriteLine('XML Time: '.(getmicrotime() - $_startTime));
			}
			
			exit();
		}
		
		exit();
	}

	/**
	 * @param string $_errorString
	 * @param XmlDocument $_xmlObj
	 * @param int $_code = null
	 */
	function PrintErrorAndExit($_errorString, &$_xmlObj, $_code = null)
	{
		if ($_xmlObj)
		{
			$_errorNote = new XmlDomNode('error', $_errorString, true);
			if (null !== $_code)
			{
				$_errorNote->AppendAttribute('code', (int) $_code);
			}

			$_xmlObj->XmlRoot->AppendChild($_errorNote);
		}

		CXmlProcessing::PrintXML($_xmlObj);
	}
}

function ChangeHtmlTextFromAttachment(&$html_part, &$modified_plain_text, $addAttachArray)
{
}
