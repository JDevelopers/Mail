<?php

require_once(WM_ROOTPATH.'core/base/base_command_creator.php');

/**
 * SQL script generator for webmail
 */
class MySqlFunambolContactCommandCreator extends BaseCommandCreator
{
	const ERR_MSG_SQL_UPDATE_GROUP = 'ERR_MSG_SQL_UPDATE_GROUP';
	const ERR_NO_SQL_UPDATE_GROUP = 1;

	const ERR_MSG_SQL_UPDATE_CONTACT = 'ERR_MSG_SQL_UPDATE_CONTACT';
	const ERR_NO_SQL_UPDATE_CONTACT = 2;

	/**
	 * @param int $idUser
	 * @return string
	 */
	function GetFullContactsList($funambolUserLogin)
	{
		$sql = '
			SELECT
				*,
				(SELECT street		FROM fnbl_pim_address WHERE contact=c.id AND type=1 LIMIT 1) AS HomeStreet,
				(SELECT city		FROM fnbl_pim_address WHERE contact=c.id AND type=1 LIMIT 1) AS HomeCity,
				(SELECT state		FROM fnbl_pim_address WHERE contact=c.id AND type=1 LIMIT 1) AS HomeState,
				(SELECT postal_code	FROM fnbl_pim_address WHERE contact=c.id AND type=1 LIMIT 1) AS HomeZip,
				(SELECT country		FROM fnbl_pim_address WHERE contact=c.id AND type=1 LIMIT 1) AS HomeCountry,
				
				(SELECT street		FROM fnbl_pim_address WHERE contact=c.id AND type=2 LIMIT 1) AS BusinessStreet,
				(SELECT city		FROM fnbl_pim_address WHERE contact=c.id AND type=2 LIMIT 1) AS BusinessCity,
				(SELECT state		FROM fnbl_pim_address WHERE contact=c.id AND type=2 LIMIT 1) AS BusinessState,
				(SELECT postal_code	FROM fnbl_pim_address WHERE contact=c.id AND type=2 LIMIT 1) AS BusinessZip,
				(SELECT country		FROM fnbl_pim_address WHERE contact=c.id AND type=2 LIMIT 1) AS BusinessCountry,

				(SELECT value		FROM fnbl_pim_contact_item WHERE contact=c.id AND type=1) AS HomePhone,
				(SELECT value		FROM fnbl_pim_contact_item WHERE contact=c.id AND type=2) AS HomeFax,
				(SELECT value		FROM fnbl_pim_contact_item WHERE contact=c.id AND type=3) AS HomeMobile,
				(SELECT value		FROM fnbl_pim_contact_item WHERE contact=c.id AND type=4) AS HomeEmail,
				(SELECT value		FROM fnbl_pim_contact_item WHERE contact=c.id AND type=5) AS HomeWeb,

				(SELECT value		FROM fnbl_pim_contact_item WHERE contact=c.id AND type=10)AS BusinessPhone,
				(SELECT value		FROM fnbl_pim_contact_item WHERE contact=c.id AND type=11)AS BusinessFax,
				(SELECT value		FROM fnbl_pim_contact_item WHERE contact=c.id AND type=16)AS BusinessEmail,

				(SELECT value		FROM fnbl_pim_contact_item WHERE contact=c.id AND type=23)AS OtherEmail

			FROM
				%sfnbl_pim_contact c
			WHERE
				userid = %s
		';
		return sprintf($sql, $this->_dbPrefix, $this->_escapeString($funambolUserLogin));
	}


	function RemoveAllContacts($idUser)
	{
	}

	function RemoveContactsByIds($idUser, $contactsIds)
	{
	}

	function CreateContactId()
	{
		$sql = '
			SELECT
				MIN(id) AS min_id
			FROM
				%sfnbl_pim_contact
		';
		return sprintf($sql, $this->_dbPrefix);
	}


	function ReplaceContact(FunambolContactContainer &$fnContactContainer)
	{
		$cc = $fnContactContainer;

		$sql = '
			REPLACE INTO
				%sfnbl_pim_contact
			(
				id,
				userid,
				last_update,
				status,
				photo_type,
				importance,
				subject,
				folder,
				anniversary,
				first_name,
				middle_name,
				last_name,
				display_name,
				birthday,
				body,
				categories,
				children,
				hobbies,
				initials,
				languages,
				nickname,
				spouse,
				suffix,
				title,
				gender,
				assistant,
				company,
				department,
				job_title,
				manager,
				mileage,
				office_location,
				profession,
				companies
			) VALUES (
				%d,
				%s,
				%s,
				%s,
				%d,
				%d,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s,
				%s
			)
		';

		return sprintf($sql, $this->_dbPrefix,
				$cc->GetValue('id', 'int'),
				$this->_escapeString($cc->GetValue('userid')),
				$this->_escapeString($cc->GetValue('last_update')),
				$this->_escapeString($cc->GetValue('status')),
				$cc->GetValue('photo_type','int'),
				$cc->GetValue('importance','int'),
				// skip so far $this->_escapeString($cc->GetValue('sensitivity')),
				$this->_escapeString($cc->GetValue('subject'),TRUE),
				$this->_escapeString($cc->GetValue('folder'),TRUE),
				$this->_escapeString($cc->GetValue('anniversary'),TRUE),
				$this->_escapeString($cc->GetValue('first_name'),TRUE),
				$this->_escapeString($cc->GetValue('middle_name'),TRUE),
				$this->_escapeString($cc->GetValue('last_name'),TRUE),
				$this->_escapeString($cc->GetValue('display_name'),TRUE),
				$this->_escapeString($cc->GetValue('birthday'),TRUE),
				$this->_escapeString($cc->GetValue('body'),TRUE),
				$this->_escapeString($cc->GetValue('categories'),TRUE),
				$this->_escapeString($cc->GetValue('children'),TRUE),
				$this->_escapeString($cc->GetValue('hobbies'),TRUE),
				$this->_escapeString($cc->GetValue('initials'),TRUE),
				$this->_escapeString($cc->GetValue('languages'),TRUE),
				$this->_escapeString($cc->GetValue('nickname'),TRUE),
				$this->_escapeString($cc->GetValue('spouce'),TRUE),
				$this->_escapeString($cc->GetValue('suffix'),TRUE),
				$this->_escapeString($cc->GetValue('title'),TRUE),
				$this->_escapeString($cc->GetValue('gender'),TRUE),
				$this->_escapeString($cc->GetValue('assistant'),TRUE),
				$this->_escapeString($cc->GetValue('company'),TRUE),
				$this->_escapeString($cc->GetValue('department'),TRUE),
				$this->_escapeString($cc->GetValue('job_title'),TRUE),
				$this->_escapeString($cc->GetValue('manager'),TRUE),
				$this->_escapeString($cc->GetValue('mileage'),TRUE),
				$this->_escapeString($cc->GetValue('office_location'),TRUE),
				$this->_escapeString($cc->GetValue('profession'),TRUE),
				$this->_escapeString($cc->GetValue('companies'),TRUE)
			);
	}

	function ReplaceContactAddressInfo(FunambolContactContainer &$fnContactContainer)
	{
		$cc = $fnContactContainer;
		$sql = '
			REPLACE INTO
				%sfnbl_pim_address
			(
				contact,
				type,
				street,
				city,
				state,
				postal_code,
				country
			) VALUES (
				%d,
				%d,
				%s,
				%s,
				%s,
				%s,
				%s
			), (
				%d,
				%d,
				%s,
				%s,
				%s,
				%s,
				%s
			)
		';
		return sprintf($sql, $this->_dbPrefix,
				$cc->GetValue('id', 'int'),
				1,
				$this->_escapeString($cc->GetValue('HomeStreet')),
				$this->_escapeString($cc->GetValue('HomeCity')),
				$this->_escapeString($cc->GetValue('HomeState')),
				$this->_escapeString($cc->GetValue('HomeZip')),
				$this->_escapeString($cc->GetValue('HomeCountry')),
				
				$cc->GetValue('id', 'int'),
				2,
				$this->_escapeString($cc->GetValue('BusinessStreet')),
				$this->_escapeString($cc->GetValue('BusinessCity')),
				$this->_escapeString($cc->GetValue('BusinessState')),
				$this->_escapeString($cc->GetValue('BusinessZip')),
				$this->_escapeString($cc->GetValue('BusinessCountry'))
			);
	}

	function ReplaceContactOtherInfo(FunambolContactContainer &$fnContactContainer)
	{
		$cc = $fnContactContainer;
		$sql = '
			REPLACE INTO
				%sfnbl_pim_contact_item
			(
				contact,
				type,
				value
			) VALUES (
				%d,
				%d,
				%s
			), (
				%d,
				%d,
				%s
			), (
				%d,
				%d,
				%s
			), (
				%d,
				%d,
				%s
			), (
				%d,
				%d,
				%s
			), (
				%d,
				%d,
				%s
			), (
				%d,
				%d,
				%s
			), (
				%d,
				%d,
				%s
			), (
				%d,
				%d,
				%s
			)
		';
		return sprintf($sql, $this->_dbPrefix,
				$cc->GetValue('id', 'int'),
				1,
				$this->_escapeString($cc->GetValue('HomePhone')),

				$cc->GetValue('id', 'int'),
				2,
				$this->_escapeString($cc->GetValue('HomeFax')),

				$cc->GetValue('id', 'int'),
				3,
				$this->_escapeString($cc->GetValue('HomeMobile')),

				$cc->GetValue('id', 'int'),
				4,
				$this->_escapeString($cc->GetValue('HomeEmail')),

				$cc->GetValue('id', 'int'),
				5,
				$this->_escapeString($cc->GetValue('HomeWeb')),

				$cc->GetValue('id', 'int'),
				10,
				$this->_escapeString($cc->GetValue('BusinessPhone')),

				$cc->GetValue('id', 'int'),
				11,
				$this->_escapeString($cc->GetValue('BusinessFax')),

				$cc->GetValue('id', 'int'),
				16,
				$this->_escapeString($cc->GetValue('BusinessEmail')),

				$cc->GetValue('id', 'int'),
				23,
				$this->_escapeString($cc->GetValue('OtherEmail'))
			);
	}

	function UpdateVCardContact(ContactContainer $contactContainer)
	{

	}

    /**
     * @param int $idContact
     * @return string
     */
    function DeleteContact($idContact)
    {
        $sql = 'DELETE FROM %sfnbl_pim_contact WHERE id = %d';
        $sql = sprintf($sql, $this->_dbPrefix, $idContact);
        return $sql;
    }

	/**
	* @param int $idContact
	* @return string
	*/
	function DeleteContactItems($idContact)
	{
		$sql = 'DELETE FROM %sfnbl_pim_contact_item WHERE contact = %d';
		$sql = sprintf($sql, $this->_dbPrefix, $idContact);
		return $sql;
	}

	/**
	* @param int $idContact
	* @return string
	*/
	function DeleteContactPhoto($idContact)
	{
		$sql = 'DELETE FROM %sfnbl_pim_contact_photo WHERE contact = %d';
		$sql = sprintf($sql, $this->_dbPrefix, $idContact);
		return $sql;
	}

	function GetUserIdsUpdatedContacts($dateUpdate)
	{
		$sql = "
			SELECT
				DISTINCT(last_name) AS id_user
			FROM
					%sfnbl_user
			WHERE
				username IN (
					SELECT
						DISTINCT(userid)
					FROM
						%sfnbl_pim_contact
					WHERE
						last_update >= CONCAT( UNIX_TIMESTAMP(%s), '000' )
			)
		";
		return sprintf($sql, $this->_dbPrefix, $this->_dbPrefix, $this->_escapeString($dateUpdate));
	}
	

	/*
	protected function convertDate($fieldName)
	{
		return 'DATE_FORMAT('.$fieldName.', \'%Y-%m-%d %T\')';
	}
	 */
}

class MsSqlFunambolContactCommandCreator extends MySqlFunambolContactCommandCreator
{
}

class FunambolContactCommandCreatorException extends BaseCommandCreatorException
{}
