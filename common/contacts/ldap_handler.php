<?php

	if (!class_exists('LdapHandlerClass'))
	{
		class LdapHandlerClass
		{
			static public function CreteNewContactUn($contact)
			{
				return 'contact_'.md5($contact->FullName.$contact->BusinessEmail.time());
			}

			static public function CreteNewGroupUn($group)
			{
				return 'group_'.md5($group->Name.time());
			}

			static public function ContactObjReparse(&$contact)
			{
			}

			static public function GetLdapObjectMap()
			{
				return array(
					'sn' => 'SurName',
					'cn' => 'FullName',

					'mail' => 'HomeEmail',
					'street' => 'HomeStreet',
					'l' => 'HomeCity',
					'st' => 'HomeState',
					'postalcode' => 'HomeZip',
					'co' => 'HomeCountry',
					'facsimileTelephoneNumber' => 'HomeFax',
					'mobile' => 'HomeMobile',
					'homePhone' => 'HomePhone',
					'labeledUri' => 'HomeWeb',

					'description' => 'Notes',
				);
			}

			static public function GetLdapContactObjectEntry()
			{
				return array(
					'un' => '',
					'sn' => '',
					'cn' => '',
					'objectClass' => array(
						'top',
						'person',
						'pabperson',
						'organizationalPerson',
						'inetOrgPerson'
					)
				);
			}

			static public function GetLdapGroupObjectEntry()
			{
				return array(
					'un' => '',
					'cn' => '',
					'objectClass' => array('top', 'pabGroup')
				);
			}
		}
	}