<?php

require_once(WM_ROOTPATH.'core/base/base_command_creator.php');

/**
 * SQL script generator for webmail
 */
class MySqlContactCommandCreator extends BaseCommandCreator
{
	const ERR_MSG_SQL_UPDATE_GROUP = 'ERR_MSG_SQL_UPDATE_GROUP';
	const ERR_NO_SQL_UPDATE_GROUP = 1;
	
	const ERR_MSG_SQL_UPDATE_CONTACT = 'ERR_MSG_SQL_UPDATE_CONTACT';
	const ERR_NO_SQL_UPDATE_CONTACT = 2;

	/**
	 * @param int $idUser
	 * @return string
	 */
	function GetFullContactsList($idUser, $includeDeleted = false)
	{
		$sql = 'SELECT 
					id_addr AS IdAddress,
					id_user AS IdUser,
					str_id AS StrId,
					deleted AS Deleted,
					fnbl_pim_id AS FunambolContactId,
					h_email AS HomeEmail,
					fullname AS FullName,
					notes AS Notes,
					use_friendly_nm AS UseFriendlyName,
					h_street AS HomeStreet,
					h_city AS HomeCity,
					h_state AS HomeState,
					h_zip AS HomeZip,
					h_country AS HomeCountry,
					h_phone AS HomePhone,
					h_fax AS HomeFax,
					h_mobile AS HomeMobile,
					h_web AS HomeWeb,
					b_email AS BusinessEmail,
					b_company AS BusinessCompany,
					b_street AS BusinessStreet,
					b_city AS BusinessCity,
					b_state AS BusinessState,
					b_zip AS BusinessZip,
					b_country AS BusinessCountry,
					b_job_title AS BusinessJobTitle,
					b_department AS BusinessDepartment,
					b_office AS BusinessOffice,
					b_phone AS BusinessPhone,
					b_fax AS BusinessFax,
					b_web AS BusinessWeb,
					other_email AS OtherEmail,
					primary_email AS PrimaryEmail,
					birthday_day AS BirthdayDay,
					birthday_month AS BirthdayMonth,
					birthday_year AS BirthdayYear,
					'.$this->convertDate('date_created').' AS DateCreated,
					'.$this->convertDate('date_modified').' AS DateModified
				FROM %sawm_addr_book
				WHERE %s id_user = %d';

		$deletedCondition = '';
		if (!$includeDeleted)
		{
			$deletedCondition = 'deleted = 0 AND';
		}

		return sprintf($sql, $this->_dbPrefix, $deletedCondition, $idUser);
	}


	/**
	 * @param int $idUser
	 * @return string
	 */
	function GetFullContactsIdsList($idUser)
	{
		$sql = 'SELECT id_addr AS IdAddress FROM %sawm_addr_book WHERE deleted = 0 AND id_user = %d';
		return sprintf($sql, $this->_dbPrefix, $idUser);
	}

	/**
	 * @param int $idUser
	 * @return string
	 */
	function GetFullContactsStrIdsList($idUser)
	{
		$sql = 'SELECT str_id AS StrId FROM %sawm_addr_book WHERE deleted = 0 AND id_user = %d';
		return sprintf($sql, $this->_dbPrefix, $idUser);
	}

	/**
	 * @param int $idUser
	 * @return string
	 */
	function GetFullGroupsIdsList($idUser)
	{
		$sql = 'SELECT id_group AS IdGroup FROM %sawm_addr_groups WHERE id_user = %d';
		return sprintf($sql, $this->_dbPrefix, $idUser);
	}

	/**
	 * @param int $idUser
	 * @return string
	 */
	function GetFullGroupsStrIdsList($idUser)
	{
		$sql = 'SELECT group_str_id AS GroupStrId FROM %sawm_addr_groups WHERE id_user = %d';
		return sprintf($sql, $this->_dbPrefix, $idUser);
	}

	/**
	 * @param int $idUser
	 * @return string
	 */
	function GetGroupsByIdContact($idUser, $idContact)
	{
		$sql = 'SELECT
					gr.id_group AS GroupId,
					gr.group_str_id AS GroupStrId,
					gr.id_user AS UserId,
					gr.group_nm AS GroupName
				FROM %sawm_addr_groups AS gr
				INNER JOIN %sawm_addr_groups_contacts AS grcont ON gr.id_group = grcont.id_group
				WHERE gr.id_user = %d AND grcont.id_addr = %d';

		return sprintf($sql, $this->_dbPrefix, $this->_dbPrefix, $idUser, $idContact);
	}

	/**
	 * @param int $idUser
	 * @param string $contactStrId
	 * @return string
	 */
	function GetContactIdByStrId($idUser, $contactStrId)
	{
		$sql = 'SELECT id_addr
				FROM %sawm_addr_book WHERE deleted = 0 AND id_user = %d AND str_id = %s';

		return sprintf($sql, $this->_dbPrefix, $idUser, $this->_escapeString($contactStrId));
	}

	/**
	 * @param int $idUser
	 * @param string $groupStrId
	 * @return string
	 */
	function GetGroupIdByStrId($idUser, $groupStrId)
	{
		$sql = 'SELECT id_group
				FROM %sawm_addr_groups WHERE id_user = %d AND group_str_id = %s';

		return sprintf($sql, $this->_dbPrefix, $idUser, $this->_escapeString($groupStrId));
	}

	/**
	 * @param int $groupId
	 * @return string
	 */
	function GetGroupById($idUser, $groupId)
	{
		$sql = 'SELECT
					id_group AS GroupId,
					group_str_id AS GroupStrId,
					id_user AS UserId,
					group_nm AS GroupName
				FROM %sawm_addr_groups WHERE id_user = %d AND id_group = %d';

		return sprintf($sql, $this->_dbPrefix, $idUser, $groupId);
	}

	/**
	 * @param string $groupStrId
	 * @return string
	 */
	function GetGroupByStrId($idUser, $groupStrId)
	{
		$sql = 'SELECT
					id_group AS GroupId,
					group_str_id AS GroupStrId,
					id_user AS UserId,
					group_nm AS GroupName
				FROM %sawm_addr_groups WHERE id_user = %d AND group_str_id = %s';

		return sprintf($sql, $this->_dbPrefix, $idUser, $this->_escapeString($groupStrId));
	}

	/**
	 * @param GroupContainer $groupContainer
	 * @return string
	 */
	function CreateGroup(GroupContainer $groupContainer)
	{
		$idUser = $groupContainer->GetValue('IdUser', 'int');
		$groupStrId = $groupContainer->GetValue('GroupStrId');
		$groupName = $groupContainer->GetValue('GroupName');

		$sql = 'INSERT INTO %sawm_addr_groups (id_user, group_nm, group_str_id) VALUES (%d, %s, %s)';

		return sprintf($sql, $this->_dbPrefix, $idUser,
				$this->_escapeString($groupName),
				$this->_escapeString($groupStrId));
	}

	/**
	 * @param GroupContainer $groupContainer
	 * @return string
	 */
	function UpdateGroup(GroupContainer $groupContainer)
	{
		$this->_cleanBuffer();
        $this->_currentContainer = $groupContainer;
		$groupId = $groupContainer->GetValue('GroupId', 'int');

		$this->_setStringValueToBuffer('GroupStrId', 'group_str_id');
		$this->_setStringValueToBuffer('GroupName', 'group_nm');

        if (count($this->_buffer) > 0)
        {
            $sql = 'UPDATE %sawm_addr_groups SET %s WHERE id_group = %d';
            $sql = sprintf($sql, $this->_dbPrefix, implode(', ', $this->_buffer), $groupId);
            return $sql;
        }
        return new ContactCommandCreatorException(self::ERR_MSG_SQL_UPDATE_GROUP,
												self::ERR_NO_SQL_UPDATE_GROUP);
	}

	/**
	 * @return string
	 */
	function UpdateGroupStrId($groupId, $groupStrId)
	{
		$sql = 'UPDATE %sawm_addr_groups SET group_str_id = %s WHERE id_group = %d';
        return sprintf($sql, $this->_dbPrefix, $this->_escapeString($groupStrId), $groupId);
	}

	function IsContactInGroup($IdContact, $IdGroup)
	{
		$sql = 'SELECT id_addr FROM %sawm_addr_groups_contacts WHERE id_addr = %d AND id_group = %d';
		return sprintf($sql, $this->_dbPrefix, $IdContact, $IdGroup);
	}

	function AddContactToGroup($IdContact, $IdGroup)
	{
		$sql = 'INSERT INTO %sawm_addr_groups_contacts (id_addr, id_group) VALUES (%d, %d)';
		return sprintf($sql, $this->_dbPrefix, $IdContact, $IdGroup);
	}

	function RemoveContactFromGroups($IdContact)
	{
		$sql = 'DELETE FROM %sawm_addr_groups_contacts WHERE id_addr = %d';
		return sprintf($sql, $this->_dbPrefix, $IdContact);
	}

	function RemoveContactsFromGroup($IdGroup)
	{
		$sql = 'DELETE FROM %sawm_addr_groups_contacts WHERE id_group = %d';
		return sprintf($sql, $this->_dbPrefix, $IdGroup);
	}

	function RemoveAllContacts($idUser)
	{
		// $sql = 'DELETE FROM %sawm_addr_book WHERE id_user = %d';
		$sql = 'UPDATE %sawm_addr_book SET deleted = 1 WHERE id_user = %d';
		return sprintf($sql, $this->_dbPrefix, $idUser);
	}

	function RemoveContactsByIds($idUser, $contactsIds)
	{
		// $sql = 'DELETE FROM %sawm_addr_book WHERE id_user = %d AND id_addr IN (%s)';
		$sql = 'UPDATE %sawm_addr_book SET deleted = 1 WHERE id_user = %d AND id_addr IN (%s)';
		return sprintf($sql, $this->_dbPrefix, $idUser, implode(', ', $contactsIds));
	}

	function RemoveGroupsByIds($idUser, $groupsIds)
	{
		$sql = '
DELETE %1$sawm_addr_groups_contacts
FROM %1$sawm_addr_groups_contacts, %1$sawm_addr_groups
WHERE %1$sawm_addr_groups_contacts.id_group = %1$sawm_addr_groups.id_group
AND %1$sawm_addr_groups.id_group IN (%2$s)
AND %1$sawm_addr_groups.id_user = %3$d';

		return sprintf($sql, $this->_dbPrefix, implode(', ', $groupsIds), $idUser);
	}

	function RemoveContactsByStrIds($idUser, $contactsStrIds)
	{
		//$sql = 'DELETE FROM %sawm_addr_book WHERE id_user = %d AND str_id IN (%s)';
		$sql = 'UPDATE %sawm_addr_book SET deleted = 1 WHERE id_user = %d AND str_id IN (%s)';
		$contactsStrIds = array_map(array(&$this, '_escapeString'), $contactsStrIds);
		return sprintf($sql, $this->_dbPrefix, $idUser, implode(', ', $contactsStrIds));
	}

	function RemoveGroupsByStrIds($idUser, $groupsStrIds)
	{
		$groupsStrIds = array_map(array(&$this, '_escapeString'), $groupsStrIds);
		$sql = '
DELETE %1$sawm_addr_groups_contacts
FROM %1$sawm_addr_groups_contacts, %1$sawm_addr_groups
WHERE %1$sawm_addr_groups_contacts.id_group = %1$sawm_addr_groups.id_group
AND %1$sawm_addr_groups.group_str_id IN (%2$s)
AND %1$sawm_addr_groups.id_user = %3$d';

		return sprintf($sql, $this->_dbPrefix, implode(', ', $groupsStrIds), $idUser);
	}

	function RemoveAllGroups($idUser)
	{
		$sql = '
DELETE %1$sawm_addr_groups_contacts
FROM %1$sawm_addr_groups_contacts, %1$sawm_addr_groups
WHERE %1$sawm_addr_groups_contacts.id_group = %1$sawm_addr_groups.id_group
AND %1$sawm_addr_groups.id_user = %2$d';

		return sprintf($sql, $this->_dbPrefix, $idUser);
	}

	function GetUserIdsUpdatedContacts($dateUpdate)
	{
		$sql = "
			SELECT
				DISTINCT(id_user)
			FROM
				%sawm_addr_book
			WHERE
				date_modified >= %s
		";
		return sprintf($sql, $this->_dbPrefix, $this->_escapeString($dateUpdate));
	}

	function UpdateFunambolContactId(ContactContainer &$contactContainer)
	{
		$sql = 'UPDATE %sawm_addr_book SET fnbl_pim_id=%d WHERE id_addr=%d';
		return sprintf($sql, $this->_dbPrefix,
				$contactContainer->GetValue('FunambolContactId', 'int'),
				$contactContainer->GetValue('IdAddress', 'int'));
	}

	function UpdateContact(ContactContainer &$contactContainer)
	{
		$cc = $contactContainer;

		$sql = 'REPLACE INTO %sawm_addr_book (
	id_addr, id_user, str_id, deleted, fnbl_pim_id, h_email, fullname, notes,
	use_friendly_nm, h_street, h_city, h_state,	h_zip, h_country, h_phone, h_fax,
	h_mobile, h_web, b_email, b_company, b_street, b_city, b_state, b_zip,
	b_country, b_job_title, b_department, b_office,	b_phone, b_fax, b_web,
	other_email, primary_email, id_addr_prev, tmp, birthday_day, birthday_month,
	birthday_year, date_created, date_modified
		) VALUES (
	%d, %d, %s, %d, %d, %s, %s, %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s,
	%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %s, %s
		)';

		$now = $this->getUtcDate();
		$dateModified	= $cc->GetValue('DateModified');

		$sql = sprintf($sql, $this->_dbPrefix,
				$cc->GetValue('IdAddress', 'int'),
				$cc->GetValue('IdUser', 'int'),
				$this->_escapeString($cc->GetValue('StrId')),
				$cc->GetValue('Deleted','int'),
				$cc->GetValue('FunambolContactId','int'),
				$this->_escapeString($cc->GetValue('HomeEmail')),
				$this->_escapeString($cc->GetValue('FullName')),
				$this->_escapeString($cc->GetValue('Notes')),
				$cc->GetValue('UseFriendlyName', 'int'),
				$this->_escapeString($cc->GetValue('HomeStreet')),
				$this->_escapeString($cc->GetValue('HomeCity')),
				$this->_escapeString($cc->GetValue('HomeState')),
				$this->_escapeString($cc->GetValue('HomeZip')),
				$this->_escapeString($cc->GetValue('HomeCountry')),
				$this->_escapeString($cc->GetValue('HomePhone')),
				$this->_escapeString($cc->GetValue('HomeFax')),
				$this->_escapeString($cc->GetValue('HomeMobile')),
				$this->_escapeString($cc->GetValue('HomeWeb')),
				$this->_escapeString($cc->GetValue('BusinessEmail')),
				$this->_escapeString($cc->GetValue('BusinessCompany')),
				$this->_escapeString($cc->GetValue('BusinessStreet')),
				$this->_escapeString($cc->GetValue('BusinessCity')),
				$this->_escapeString($cc->GetValue('BusinessState')),
				$this->_escapeString($cc->GetValue('BusinessZip')),
				$this->_escapeString($cc->GetValue('BusinessCountry')),
				$this->_escapeString($cc->GetValue('BusinessJobTitle')),
				$this->_escapeString($cc->GetValue('BusinessDepartment')),
				$this->_escapeString($cc->GetValue('BusinessOffice')),
				$this->_escapeString($cc->GetValue('BusinessPhone')),
				$this->_escapeString($cc->GetValue('BusinessFax')),
				$this->_escapeString($cc->GetValue('BusinessWeb')),
				$this->_escapeString($cc->GetValue('OtherEmail')),
				$cc->GetValue('PrimaryEmail', 'int'),
				$cc->GetValue('IdPreviousAddress', 'int'),
				$cc->GetValue('Temp', 'int'),
				$cc->GetValue('BirthdayDay', 'int'),
				$cc->GetValue('BirthdayMonth', 'int'),
				$cc->GetValue('BirthdayYear', 'int'),
				$this->_escapeString($cc->GetValue('DateCreated')),
				((is_null($dateModified)	|| empty($dateModified))	? $now : $this->_escapeString($dateModified))
				);
		return $sql;
	}

	function UpdateContactStrId($contactId, $contactStrId)
	{
		$sql = 'UPDATE %sawm_addr_book SET str_id = %s WHERE id_addr = %d';
		return sprintf($sql, $this->_dbPrefix, $this->_escapeString($contactStrId), $contactId);
	}

	function CreateContact(ContactContainer &$contactContainer)
	{
		$cc = $contactContainer;
		
		$sql = 'INSERT INTO %sawm_addr_book (
	id_user, str_id, deleted, fnbl_pim_id, h_email, fullname, notes, use_friendly_nm,
	h_street, h_city, h_state, h_zip, h_country, h_phone, h_fax, h_mobile, h_web,
	b_email, b_company, b_street, b_city, b_state, b_zip, b_country, b_job_title,
	b_department, b_office,	b_phone, b_fax, b_web, other_email, primary_email,
	id_addr_prev, tmp, birthday_day, birthday_month, birthday_year, date_created,
	date_modified
		) VALUES (
	%d, %s, %d, %d, %s, %s, %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s,
	%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %s, %s
		)';

		$now = $this->getUtcDate();
		$dateCreated	= $cc->GetValue('DateCreated');
		$dateModified	= $cc->GetValue('DateModified');

		return sprintf($sql, $this->_dbPrefix,
				$cc->GetValue('IdUser', 'int'),
				$this->_escapeString($cc->GetValue('StrId')),
				$cc->GetValue('Deleted','int'),
				$cc->GetValue('FunambolContactId','int'),
				$this->_escapeString($cc->GetValue('HomeEmail')),
				$this->_escapeString($cc->GetValue('FullName')),
				$this->_escapeString($cc->GetValue('Notes')),
				$cc->GetValue('UseFriendlyName', 'int'),
				$this->_escapeString($cc->GetValue('HomeStreet')),
				$this->_escapeString($cc->GetValue('HomeCity')),
				$this->_escapeString($cc->GetValue('HomeState')),
				$this->_escapeString($cc->GetValue('HomeZip')),
				$this->_escapeString($cc->GetValue('HomeCountry')),
				$this->_escapeString($cc->GetValue('HomePhone')),
				$this->_escapeString($cc->GetValue('HomeFax')),
				$this->_escapeString($cc->GetValue('HomeMobile')),
				$this->_escapeString($cc->GetValue('HomeWeb')),
				$this->_escapeString($cc->GetValue('BusinessEmail')),
				$this->_escapeString($cc->GetValue('BusinessCompany')),
				$this->_escapeString($cc->GetValue('BusinessStreet')),
				$this->_escapeString($cc->GetValue('BusinessCity')),
				$this->_escapeString($cc->GetValue('BusinessState')),
				$this->_escapeString($cc->GetValue('BusinessZip')),
				$this->_escapeString($cc->GetValue('BusinessCountry')),
				$this->_escapeString($cc->GetValue('BusinessJobTitle')),
				$this->_escapeString($cc->GetValue('BusinessDepartment')),
				$this->_escapeString($cc->GetValue('BusinessOffice')),
				$this->_escapeString($cc->GetValue('BusinessPhone')),
				$this->_escapeString($cc->GetValue('BusinessFax')),
				$this->_escapeString($cc->GetValue('BusinessWeb')),
				$this->_escapeString($cc->GetValue('OtherEmail')),
				$cc->GetValue('PrimaryEmail', 'int'),
				$cc->GetValue('IdPreviousAddress', 'int'),
				$cc->GetValue('Temp', 'int'),
				$cc->GetValue('BirthdayDay', 'int'),
				$cc->GetValue('BirthdayMonth', 'int'),
				$cc->GetValue('BirthdayYear', 'int'),
				((is_null($dateCreated)		|| empty($dateCreated))		? $now : $this->_escapeString($dateCreated)),
				((is_null($dateModified)	|| empty($dateModified))	? $now : $this->_escapeString($dateModified))
				);
	}

	function UpdateVCardContact(ContactContainer $contactContainer)
	{
		$this->_cleanBuffer();
        $this->_currentContainer = $contactContainer;
		$contactId = $contactContainer->GetValue('IdAddress', 'int');

		$this->_setStringValueToBuffer('StrId', 'str_id');
		$this->_setStringValueToBuffer('FullName', 'fullname');
		$this->_setStringValueToBuffer('Notes', 'notes');
		
		$this->_setStringValueToBuffer('HomeEmail', 'h_email');
		$this->_setStringValueToBuffer('HomeStreet', 'h_street');
		$this->_setStringValueToBuffer('HomeCity', 'h_city');
		$this->_setStringValueToBuffer('HomeState', 'h_state');
		$this->_setStringValueToBuffer('HomeZip', 'h_zip');
		$this->_setStringValueToBuffer('HomeCountry', 'h_country');
		$this->_setStringValueToBuffer('HomePhone', 'h_phone');
		$this->_setStringValueToBuffer('HomeFax', 'h_fax');
		$this->_setStringValueToBuffer('HomeMobile', 'h_mobile');
		$this->_setStringValueToBuffer('HomeWeb', 'h_web');
		
		$this->_setStringValueToBuffer('BusinessEmail', 'b_email');
		$this->_setStringValueToBuffer('BusinessCompany', 'b_company');
		$this->_setStringValueToBuffer('BusinessStreet', 'b_street');
		$this->_setStringValueToBuffer('BusinessCity', 'b_city');
		$this->_setStringValueToBuffer('BusinessState', 'b_state');
		$this->_setStringValueToBuffer('BusinessZip', 'b_zip');
		$this->_setStringValueToBuffer('BusinessCountry', 'b_country');
		$this->_setStringValueToBuffer('BusinessJobTitle', 'b_job_title');
		$this->_setStringValueToBuffer('BusinessDepartment', 'b_department');
		$this->_setStringValueToBuffer('BusinessOffice', 'b_office');
		$this->_setStringValueToBuffer('BusinessPhone', 'b_phone');
		$this->_setStringValueToBuffer('BusinessFax', 'b_fax');
		$this->_setStringValueToBuffer('BusinessWeb', 'b_web');

		$this->_setStringValueToBuffer('OtherEmail', 'other_email');
		$this->_setIntValueToBuffer('BirthdayDay', 'birthday_day');
		$this->_setIntValueToBuffer('BirthdayMonth', 'birthday_month');
		$this->_setIntValueToBuffer('BirthdayYear', 'birthday_year');
		
		$this->_setStringValueToBuffer('DateModified', 'date_modified');

		$dateModified = $contactContainer->GetValue('DateModified');
		$dateModified = $this->convertInsertDate($dateModified);
		
		$this->_setValueToBuffer('date_modified', $dateModified);

		if (count($this->_buffer) > 0)
        {
            $sql = "UPDATE %sawm_addr_book SET %s WHERE id_addr = %d";
            $sql = sprintf($sql, $this->_dbPrefix, implode(', ', $this->_buffer), $contactId);
            return $sql;
        }
        return new ContactCommandCreatorException(self::ERR_MSG_SQL_UPDATE_CONTACT,
												self::ERR_NO_SQL_UPDATE_CONTACT);
	}

	/*
	protected function convertDate($fieldName)
	{
		return 'DATE_FORMAT('.$fieldName.', \'%Y-%m-%d %T\')';
	}
	 */
}

class MsSqlContactCommandCreator extends MySqlContactCommandCreator
{

	function RemoveGroupsByIds($idUser, $groupsIds)
	{
		$sql = '
DELETE
FROM %1$sawm_addr_groups_contacts
FROM %1$sawm_addr_book
WHERE %1$sawm_addr_groups_contacts.id_group = %1$sawm_addr_book.id_group
AND %1$sawm_addr_book.id_group IN (%2$s)
AND %1$sawm_addr_book.id_user = %3$d';

		return sprintf($sql, $this->_dbPrefix, implode(', ', $groupsIds), $idUser);
	}
	
	function RemoveAllGroups($idUser)
	{
		$sql = '
DELETE
FROM %1$sawm_addr_groups_contacts
FROM %1$sawm_addr_groups
WHERE %1$sawm_addr_groups_contacts.id_group = %1$sawm_addr_groups.id_group
AND %1$sawm_addr_groups.id_user = %2$d';
		
		return sprintf($sql, $this->_dbPrefix, $idUser);
	}

	protected function convertDate($fieldName)
	{
		return 'CONVERT(VARCHAR, '.$fieldName.', 120)';
	}

	protected function convertInsertDate($fieldValue)
	{
		return 'CONVERT(DATETIME, '.$this->_escapeString($fieldValue).', 120)';
	}
}

class ContactCommandCreatorException extends BaseCommandCreatorException
{}
