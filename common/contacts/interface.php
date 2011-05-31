<?php

interface IContactsDriver
{
	/**
	 * @param	int			$groupId
	 * @param	int			$lookForType
	 * @param	string		$lookForField
	 * @return	array		array(contact_count, group_count)
	 */
	public function GetContactsAndGroupsCount($groupId, $lookForType, $lookForField);

	/**
	 * @param	array		$counts			array(contact_count, group_count)
	 * @param	int			$page
	 * @param	string		$sortField
	 * @param	int			$sortOrder
	 * @param	int			$groupId
	 * @param	int			$lookForType
	 * @param	string		$lookForField
	 * @return	ContactCollection
	 */
	public function GetContactsAndGroups($counts, $page, $sortField, $sortOrder, $groupId, $lookForType, $lookForField);

	/**
	 * @param	int		$groupId
	 * @return	array
	 */
	public function GetContactsOfGroup($groupId);

	/**
	 * @param	int		$contactId
	 * @return	AddressBookRecord
	 */
	public function GetContact($contactId);

	/**
	 * @param	int		$contactId
	 * @return	AddressGroup
	 */
	public function GetGroup($groupId);

	/**
	 * @return	array	array(id => name)
	 */
	public function GetGroups();

	/**
	 * @param	int		$contactId
	 * @return	array	array(id => name)
	 */
	public function GetGroupsOfContact($contactId);

	/**
	 * @return	bool
	 */
	public function DeleteContact($contactId);

	/**
	 * @return	bool
	 */
	public function DeleteGroup($groupId);

	/**
	 * @param	int		$contactId
	 * @param	int		$groupId
	 * @return	bool
	 */
	public function AddContactToGroup($contactId, $groupId);

	/**
	 * @param	int		$contactId
	 * @param	int		$groupId
	 * @return	bool
	 */
	public function InsertContactToGroup($contactId, $groupId);

	/**
	 * @param	string	$name
	 * @param	string	$email
	 * @return	bool
	 */
	public function IsContactExist($name, $email);

	/**
	 * @param	AddressBookRecord	$contact
	 * @return	bool
	 */
	public function CreateContact($contact);

	/**
	 * @param	AddressBookRecord	$contact
	 * @return	bool
	 */
	public function UpdateContact($contact);

	/**
	 * @param	string	$name
	 * @return	bool
	 */
	public function IsGroupExist($name);

	/**
	 * @param	AddressGroup	$group
	 * @return	bool
	 */
	public function CreateGroup($group);

	/**
	 * @param	AddressGroup	$group
	 * @return	bool
	 */
	public function UpdateGroup($group);

}
