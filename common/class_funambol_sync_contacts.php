<?php
defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'./../'));

require_once WM_ROOTPATH.'api/webmail/webmail_manager.php';
require_once WM_ROOTPATH.'api/webmail/contact_manager.php';
require_once WM_ROOTPATH.'api/webmail/funambol_contact_manager.php';
require_once WM_ROOTPATH.'common/inc_funambol_constants.php';
require_once WM_ROOTPATH.'common/class_funambol_sync_base.php';


/**
 * 
 */
class FunambolSyncContacts extends FunambolSyncBase
{
	var $_account	= NULL;
	var $_settings	= NULL;

	public function  __construct(&$account, &$settings) 
	{
		$this->_account  = $account;
		$this->_settings = $settings;
	}


	/**
	 *
	 * @param Account $account
	 * @param <type> $settings
	 * @return <type>
	 */

	public function performSync()
	{
echo "-----synching contacts<br>\n";
		$fnContactManager = new FunambolContactManager();
		$wmContactManager = new ContactManager();

		if (!$fnContactManager->InitManager() || !$wmContactManager->InitManager())
		{
echo "managers not inited<br>\n";
			return false;
		}

		$user_id = $this->_account->IdUser;

		// in minutes. with dayLightSavingTime adjustment
		// WARNING
		// this is different from calendars
		$accountOffset =	$this->_settings->AllowUsersChangeTimeZone ?
							$this->_account->GetDefaultTimeOffset() :
							$this->_account->GetDefaultTimeOffset($this->_settings->DefaultTimeZone);

echo "making wmContactManager<br>\n";
echo "about to call wmContactManager->InitAccount<br>\n";
		$wmContactManager->InitAccount($this->_account);
echo "about to call wmContactManager->GetFullContactsList<br>\n";
		$wmContactContainers = $wmContactManager->GetFullContactsList(true);

echo "making fnContactManager<br>\n";
		$fnContactManager->InitAccount($this->_account->Email);
		$fnContactContainers = $fnContactManager->GetFullContactsList();

echo "fnContactContainers LOOP STARTED<br>\n";

		foreach ($fnContactContainers as $fnContactContainer)
		{
			// we are interested in the following set of fields from Funambol PIM record
			// 1. id - unique ID of the Funambol PIM record, INT
			// 2. last_update INT which is  UNIX_TIMESTAMP*1000 + MSECS
			// 3. status 'N', 'U' and 'D' respectively
			// first_name
			// middle_name
			// last_name
			$funambolId		= $fnContactContainer->GetValue('id');
			$funambolStatus	= $fnContactContainer->GetValue('status');

	echo "---funambolId=$funambolId, funambolStatus=$funambolStatus<br/>\n";

			$wmContactContainer = $this->GetContactContainerByFunambolId($wmContactContainers, $funambolId);
			if($wmContactContainer == null)
			{
	echo "no in WM<br/>\n";
				// this contact is presented in Funambol DB but is NOT presented in WM
				//if($funambolStatus === FUNAMBOL_STATUS_DELETED)
				//{
	//echo "does not need sync<br/>\n";
					// this case does not need sync
					// account is alredy deleted prior to get known to us
				//}
				//else
				//{
	echo "adding from FN to WM<br/>\n";
					// new account, need to insert in into WM

					$wmContactContainer = $this->ConvertFunambolToWMContactContainer($fnContactContainer, $user_id, TRUE);
					$wmContactManager->CreateContact($wmContactContainer);
				//}
			}
			else
			{
	echo "old known contact, sync it<br/>\n";
	echo "wmId=".$wmContactContainer->GetValue('IdAddress')."<br/>\n";
				// this contact is known for both Funambol and WM
				// 1. compare modification time
				// 2. update more old one

				// GMT modification date as 2000-12-31 23:59:59
				$wmDateModified			= $wmContactContainer->GetValue('DateModified');
				// seconds from Epoch for this date
				$wmTimestampModified	= strtotime( $wmDateModified );
				// local server's timestamp - offset to get GMT as seconds from Epoch
				$fnTimestampModified	= $this->ConvertFNtoWMTimestamp( $fnContactContainer->GetValue('last_update'), TRUE );
	
	echo
	"   wmtime=" . $wmDateModified ." " . $wmTimestampModified . "<br/>\n".
	"   fntime=" . date( 'Y-m-d H:i:s', $fnTimestampModified )." ".$fnTimestampModified." " . $fnContactContainer->GetValue('last_update') . "<br/>\n"
	;
	
				if($wmTimestampModified == $fnTimestampModified)
				{
						echo "already synced<br/>\n";
				}
				else if($wmTimestampModified > $fnTimestampModified)
				{
						echo "WM is newer, updating FN<br/>\n";

					$fnContactContainer = $this->ConvertWMToFunambolContactContainer($wmContactContainer,TRUE);
					$fnContactManager->ReplaceContact($fnContactContainer);
					$fnContactManager->ReplaceContactAddressInfo($fnContactContainer);
					$fnContactManager->ReplaceContactOtherInfo($fnContactContainer);
				}
				else
				{
						echo "FN is newer, updating WM<br/>\n";
					$wmCC = $this->ConvertFunambolToWMContactContainer($fnContactContainer,$user_id, TRUE);
					$wmCC->SetValue('IdAddress',$wmContactContainer->GetValue('IdAddress'),'int');
					$wmCC->SetValue('IdUser',$wmContactContainer->GetValue('IdUser'),'int');
					$wmCC->SetValue('DateCreated',$wmContactContainer->GetValue('DateCreated'));
					if($wmContactManager->UpdateContact($wmCC))
					{
						echo "updated<br/>\n";
					} else {
						echo "NOT updated<br/>\n";
					}

				}

				// unset contact from $wmContactContainers
				// usable for old contacts only
				$this->RemoveWMContactContainer($wmContactContainers, $wmContactContainer);
				
			} // if( this contact is a new one or an old one )

		} // foreach ($fnContactContainers as $fnContactContainer)
echo "fnContactContainers LOOP ENDED<br>\n";

		// here $wmContactContainers contains only newly created contacts on WM -side
		// we need to propagate them to Funambol

echo "wmContactContainers LOOP STARTED<br>\n";
		foreach ($wmContactContainers as $wmContactContainer)
		{
	echo "adding from WM to FN<br/>\n";
			$fnContactContainer = $this->ConvertWMToFunambolContactContainer($wmContactContainer, TRUE);
			$fnContactManager->ReplaceContact($fnContactContainer);
			$fnContactManager->ReplaceContactAddressInfo($fnContactContainer);
			$fnContactManager->ReplaceContactOtherInfo($fnContactContainer);

			// get newly create id in Funambol DB and update WM table with it
			$wmContactContainer->SetValue('FunambolContactId',$fnContactContainer->GetValue('id'));
			$wmContactManager->UpdateFunambolContactId($wmContactContainer);
		} // foreach ($wmContactContainers as $wmContactContainer)
echo "wmContactContainers LOOP ENDED<br>\n";

		return true;
	} // function

	/**
	 *
	 * @param <type> $wmContactContainers
	 * @param <type> $funambolId
	 * @return <type>
	 */
	private function GetContactContainerByFunambolId( &$wmContactContainers, $funambolId )
	{
		foreach( $wmContactContainers as $wmContactContainer )
		{
			if( $wmContactContainer->GetValue( 'FunambolContactId' ) == $funambolId )
			{
				return $wmContactContainer;
			}
		} // foreach()

		// nothing found
		return null;
	}

	/**
	 *
	 * @param <type> $fnContactContainer
	 * @return <type>
	 */
	private function ConvertFunambolToWMContactContainer(&$fnContactContainer, $user_id, $updateDate = FALSE)
	{
		$wmContactContainer = new ContactContainer();

		$wmContactContainer->SetValue('IdUser',				$user_id);
		$wmContactContainer->SetValue('FunambolContactId',	$fnContactContainer->GetValue('id'));
		$wmContactContainer->SetValue('Title',				$fnContactContainer->GetValue('title'));

		$subject = $fnContactContainer->GetValue('subject');
		if(empty($subject)) {
			$fname = $fnContactContainer->GetValue('first_name');
			if(is_null($fname)) $fname = '';
			$lname = $fnContactContainer->GetValue('last_name');
			if(is_null($lname)) $lname = '';
			$wmContactContainer->SetValue('FullName',		$fname.' '.$lname	);
		} else {
			$wmContactContainer->SetValue('FullName',		$subject	);
		}

		if ($fnContactContainer->GetValue('status') == FUNAMBOL_STATUS_DELETED)
		{
			$wmContactContainer->SetValue('Deleted', 1);
		}
		else
		{
			$wmContactContainer->SetValue('Deleted', 0);
		}


		$wmContactContainer->SetValue('FirstName',			$fnContactContainer->GetValue('first_name'));
		$wmContactContainer->SetValue('SurName',			$fnContactContainer->GetValue('last_name'));
		$wmContactContainer->SetValue('NickName',			$fnContactContainer->GetValue('nickname'));
		$wmContactContainer->SetValue('Notes',				$fnContactContainer->GetValue('body'));
		$wmContactContainer->SetValue('UseFriendlyName',	1);

		$wmContactContainer->SetValue('HomeStreet',			$fnContactContainer->GetValue('HomeStreet'));
		$wmContactContainer->SetValue('HomeCity',			$fnContactContainer->GetValue('HomeCity'));
		$wmContactContainer->SetValue('HomeState',			$fnContactContainer->GetValue('HomeState'));
		$wmContactContainer->SetValue('HomeZip',			$fnContactContainer->GetValue('HomeZip'));
		$wmContactContainer->SetValue('HomeCountry',		$fnContactContainer->GetValue('HomeCountry'));
		$wmContactContainer->SetValue('HomePhone',			$fnContactContainer->GetValue('HomePhone'));
		$wmContactContainer->SetValue('HomeFax',			$fnContactContainer->GetValue('HomeFax'));
		$wmContactContainer->SetValue('HomeMobile',			$fnContactContainer->GetValue('HomeMobile'));
		$wmContactContainer->SetValue('HomeEmail',			$fnContactContainer->GetValue('HomeEmail'));
		$wmContactContainer->SetValue('HomeWeb',			$fnContactContainer->GetValue('HomeWeb'));
		$wmContactContainer->SetValue('BusinessEmail',		$fnContactContainer->GetValue('BusinessEmail'));
		$wmContactContainer->SetValue('BusinessCompany',	$fnContactContainer->GetValue('company'));
		$wmContactContainer->SetValue('BusinessStreet',		$fnContactContainer->GetValue('BusinessStreet'));
		$wmContactContainer->SetValue('BusinessCity',		$fnContactContainer->GetValue('BusinessCity'));
		$wmContactContainer->SetValue('BusinessState',		$fnContactContainer->GetValue('BusinessState'));
		$wmContactContainer->SetValue('BusinessZip',		$fnContactContainer->GetValue('BusinessZip'));
		$wmContactContainer->SetValue('BusinessCountry',	$fnContactContainer->GetValue('BusinessCountry'));
		$wmContactContainer->SetValue('BusinessJobTitle',	$fnContactContainer->GetValue('job_title'));
		$wmContactContainer->SetValue('BusinessDepartment',	$fnContactContainer->GetValue('department'));
		$wmContactContainer->SetValue('BusinessOffice',		$fnContactContainer->GetValue('office_location'));
		$wmContactContainer->SetValue('BusinessPhone',		$fnContactContainer->GetValue('BusinessPhone'));
	//	$wmContactContainer->SetValue('BusinessMobile');
		$wmContactContainer->SetValue('BusinessFax',		$fnContactContainer->GetValue('BusinessFax'));
	//	$wmContactContainer->SetValue('BusinessWeb',);
		$wmContactContainer->SetValue('OtherEmail',			$fnContactContainer->GetValue('OtherEmail'));
	//	$wmContactContainer->SetValue('PrimaryEmail',);
	//		$this->_container['IdPreviousAddress'] = null;
	//		$this->_container['Temp'] = null;

		if(strlen($fnContactContainer->GetValue('birthday'))>=10) // 2010-12-31
		{
			// we have sufficient length

			$ts = strtotime($fnContactContainer->GetValue('birthday'));

			$wmContactContainer->SetValue('BirthdayDay',	date('d',$ts)); // 01 through 31
			$wmContactContainer->SetValue('BirthdayMonth',	date('m',$ts)); // 01 through 12
			$wmContactContainer->SetValue('BirthdayYear',	date('Y',$ts)); // 2010
		}
		else
		{
			// birthday looks like bad
		}


		if($updateDate)
		{
			// as seconds from Epoch
			$fnTimestampModified = $this->ConvertFNtoWMTimestamp( $fnContactContainer->GetValue('last_update'), TRUE );
			$fnDateModified = date('Y-m-d H:i:s', $fnTimestampModified);

			$wmContactContainer->SetValue('DateCreated', $fnDateModified);	//FIXIT
			$wmContactContainer->SetValue('DateModified', $fnDateModified);
		}
		else
		{
			//$wmContactContainer->_container['DateCreated']	= null;
			//$wmContactContainer->_container['DateModified']	= null;
		}

		return $wmContactContainer;
	}

	/**
	 * @param <type> $wmContactContainer
	 * @return <type>
	 */

	// TODO remove $updateDate = FALSE parameter
	private function ConvertWMToFunambolContactContainer(&$wmContactContainer,$updateDate = FALSE)
	{
		$fnContactContainer = new FunambolContactContainer();

		$funambol_user = $this->_account->Email;

		$fnContactContainer->SetValue('id', $wmContactContainer->GetValue('FunambolContactId'));
		$fnContactContainer->SetValue('userid', $funambol_user);

		if ($wmContactContainer->GetValue('Deleted') == 1)
		{
			$fnContactContainer->SetValue('status', FUNAMBOL_STATUS_DELETED);
		}
		else
		{
			$fnContactContainer->SetValue('status', FUNAMBOL_STATUS_UPDATED);
		}
		
		if($updateDate)
		{
			$wmDateModified = $wmContactContainer->GetValue('DateModified'); // as 2000-12-31 23:59:59
			$wmTimestampModified = strtotime($wmDateModified);
			$fnTimestampModified = $wmTimestampModified + date('Z'); // as seconds from Epoch in localtime
			$fnContactContainer->SetValue('last_update', "".$fnTimestampModified."111");

		}
		else
		{
			$fnContactContainer->SetValue('last_update', "".time()."222");
		}

		$fnContactContainer->SetValue('title',				$wmContactContainer->GetValue('Title'));
		$fnContactContainer->SetValue('subject',			$wmContactContainer->GetValue('FullName'));

		$wm_fname	= $wmContactContainer->GetValue('FirstName');
		$wm_sname	= $wmContactContainer->GetValue('SurName');
		$wm_full	= $wmContactContainer->GetValue('FullName');
		if(		// no separated first name and last name are specified
				empty($wm_fname) && empty($wm_sname)
				// and FullName is specified
			&& !empty($wm_full)
		) {
			// actually, this is typical situation when coping data from WM
			// copy FullName as first_name
			// leave last_name empty
			// and get user define himself what part of 'FullName' should be
			// copied to last_name

			$lname = NULL;
			$fname = NULL;
			$parts = explode(' ', trim($wmContactContainer->GetValue('FullName')));
			// expecting line as FirstName LastName
			if(count($parts)>0)
			{
				$fname = array_shift($parts);
				$lname = implode(' ',$parts);
//				if(is_null($lname)) $lname='';
//				if(is_null($fname)) $fname='';
			}
			$fnContactContainer->SetValue('first_name',			$fname);
			$fnContactContainer->SetValue('last_name',			$lname);
			
//			$fnContactContainer->SetValue('first_name',			$wmContactContainer->GetValue('FullName'));
//			$fnContactContainer->SetValue('last_name',			'');
		} else {
			$fnContactContainer->SetValue('first_name',			$wmContactContainer->GetValue('FirstName'));
			$fnContactContainer->SetValue('last_name',			$wmContactContainer->GetValue('SurName'));
		}
		
		$fnContactContainer->SetValue('display_name',		$wmContactContainer->GetValue('FullName'));
		$fnContactContainer->SetValue('nickname',			$wmContactContainer->GetValue('NickName'));
		$fnContactContainer->SetValue('body',				$wmContactContainer->GetValue('Notes'));
	//	$fnContactContainer->SetValue('UseFriendlyName',	1);
		$fnContactContainer->SetValue('HomeStreet',			$wmContactContainer->GetValue('HomeStreet'));
		$fnContactContainer->SetValue('HomeCity',			$wmContactContainer->GetValue('HomeCity'));
		$fnContactContainer->SetValue('HomeState',			$wmContactContainer->GetValue('HomeState'));
		$fnContactContainer->SetValue('HomeZip',			$wmContactContainer->GetValue('HomeZip'));
		$fnContactContainer->SetValue('HomeCountry',		$wmContactContainer->GetValue('HomeCountry'));
		$fnContactContainer->SetValue('HomePhone',			$wmContactContainer->GetValue('HomePhone'));
		$fnContactContainer->SetValue('HomeFax',			$wmContactContainer->GetValue('HomeFax'));
		$fnContactContainer->SetValue('HomeMobile',			$wmContactContainer->GetValue('HomeMobile'));
		$fnContactContainer->SetValue('HomeEmail',			$wmContactContainer->GetValue('HomeEmail'));

		$fnContactContainer->SetValue('HomeWeb',			$wmContactContainer->GetValue('HomeWeb'));
		$fnContactContainer->SetValue('BusinessEmail',		$wmContactContainer->GetValue('BusinessEmail'));
		$fnContactContainer->SetValue('company',			$wmContactContainer->GetValue('BusinessCompany'));
		$fnContactContainer->SetValue('BusinessStreet',		$wmContactContainer->GetValue('BusinessStreet'));
		$fnContactContainer->SetValue('BusinessCity',		$wmContactContainer->GetValue('BusinessCity'));
		$fnContactContainer->SetValue('BusinessState',		$wmContactContainer->GetValue('BusinessZip'));
		$fnContactContainer->SetValue('BusinessZip',		$wmContactContainer->GetValue('BusinessZip'));
		$fnContactContainer->SetValue('BusinessCountry',	$wmContactContainer->GetValue('BusinessCountry'));
		$fnContactContainer->SetValue('job_title',			$wmContactContainer->GetValue('BusinessJobTitle'));
		$fnContactContainer->SetValue('department',			$wmContactContainer->GetValue('BusinessDepartment'));
		$fnContactContainer->SetValue('office_location',	$wmContactContainer->GetValue('BusinessOffice'));
		$fnContactContainer->SetValue('companies',			$wmContactContainer->GetValue('BusinessCompany'));
		$fnContactContainer->SetValue('BusinessPhone',		$wmContactContainer->GetValue('BusinessPhone'));
	//	$fmContactContainer->SetValue('BusinessMobile',		$wmContactContainer->GetValue(''));
		$fnContactContainer->SetValue('BusinessFax',		$wmContactContainer->GetValue('BusinessFax'));
	//	$fmContactContainer->SetValue('BusinessWeb',);
		$fnContactContainer->SetValue('OtherEmail',			$wmContactContainer->GetValue('OtherEmail'));
	//	$fmContactContainer->SetValue('PrimaryEmail',);
	//		$this->_container['IdPreviousAddress'] = null;
	//		$this->_container['Temp'] = null;
		$tmp =	$wmContactContainer->GetValue('BirthdayYear')	. "-" .
				$wmContactContainer->GetValue('BirthdayMonth')	. "-" .
				$wmContactContainer->GetValue('BirthdayDay');
		if(strlen($tmp) == 10)
		{	// 2010-12-31 looks like live date
			$fnContactContainer->SetValue('birthday', $tmp);
		}

	//		$this->_container['DateCreated'] = null;
	//		$this->_container['DateModified'] = null;

		return $fnContactContainer;
	}

	/**
	 *
	 * @param <type> $wmContactContainers
	 * @param <type> $wmContactContainer
	 */
	private function RemoveWMContactContainer(&$wmContactContainers, &$wmContactContainer)
	{
		foreach($wmContactContainers as $key=>$contact)
		{
			if($contact->GetValue('IdAddress') == $wmContactContainer->GetValue('IdAddress'))
			{
				unset($wmContactContainers[$key]);
			}
		}
	}
} // class SyncFunambolContacts
