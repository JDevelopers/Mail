<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/'));

	header('Content-type: text/html; charset=utf-8');

	require_once(WM_ROOTPATH.'common/inc_top.php');
	
 	$Error_Desc = '';
	$ErrorInt = 1;
	$contactsCount = 0;
	$ContactArray = array();

	require_once(WM_ROOTPATH.'common/class_settings.php');
	require_once(WM_ROOTPATH.'common/class_account.php');
	require_once(WM_ROOTPATH.'common/class_mailprocessor.php');
	require_once(WM_ROOTPATH.'common/class_contacts.php');
	require_once(WM_ROOTPATH.'common/class_validate.php');
	require_once(WM_ROOTPATH.'common/class_convertutils.php');
	require_once(WM_ROOTPATH.'common/class_contactstorage.php');

	require WM_ROOTPATH.'common/class_session.php';
	
	@ob_start();
	
	$settings =& Settings::CreateInstance();
	if (!$settings || !$settings->isLoad)
	{
		$Error_Desc = 'Can\'t Load Settings file';
	}
	if (!$settings->IncludeLang())
	{
		$Error_Desc = 'Can\'t Load Language file';
	}

	define('FILE_DATA_KEY', 'Filedata');

	$account = null;
	$fs = null;
	$attfolder = null;

	if (!isset($_SESSION[ACCOUNT_ID]))
	{
		$Error_Desc = UnknownUploadError;
	}

	if (empty($Error_Desc))
	{
		$account =& Account::LoadFromDb($_SESSION[ACCOUNT_ID]);
		if ($account)
		{
			$fs = new FileSystem(INI_DIR.'/temp', strtolower($account->Email), $account->Id);
			$attfolder = new Folder($_SESSION[ACCOUNT_ID], -1, GetSessionAttachDir());
		}
		else
		{
			$Error_Desc = UnknownUploadError;
		}
	}

	$tempname = '';
	$isNullFile = false;
	
	if (empty($Error_Desc) && $account && $fs && $attfolder)
	{
		if (isset($_FILES[FILE_DATA_KEY]))
		{
			$tempname = 'import_'.basename($_FILES[FILE_DATA_KEY]['tmp_name']);
			
			$fs->CreateFolder($attfolder);
			if (!@move_uploaded_file($_FILES[FILE_DATA_KEY]['tmp_name'], $fs->GetFolderFullPath($attfolder).'/'.$tempname))
			{
				switch ($_FILES[FILE_DATA_KEY]['error'])
				{
					case 1:
					case 2:
						$Error_Desc = FileIsTooBig;
						break;
					case 3:
						$Error_Desc = FilePartiallyUploaded;
						break;
					case 4:
						$Error_Desc = NoFileUploaded;
						break;
					case 6:
						$Error_Desc = MissingTempFolder;
						break;
					default:
						$Error_Desc = UnknownUploadError;
						break;
				}
			} 
			else
			{
				$filesize = @filesize($fs->GetFolderFullPath($attfolder).'/'.$tempname);
				if ($filesize === false)
				{
					$Error_Desc = MissingTempFile;	
				}
			}
		}
		else 
		{
			$postsize = @ini_get('upload_max_filesize');
			$Error_Desc = ($postsize) ? FileLargerThan.$postsize : FileIsTooBig;
		}
	
		if (empty($Error_Desc))
		{
			ConvertUtils::SetLimits();

			$isNullFile = true;

			@setlocale(LC_CTYPE, 'en_US.UTF-8');
			$handle = @fopen($fs->GetFolderFullPath($attfolder).'/'.$tempname, 'rb');
			if (isset($filesize) && $filesize > 0)
			{
				$isNullFile = false;	
			}
			
			$getdelimiter = fread($handle, 20);
			rewind($handle);
			
			$pos1 = (int) strpos($getdelimiter, ',');
			$pos2 = (int) strpos($getdelimiter, ';');
			
			$delimiter = ($pos1 > $pos2) ? ',' : ';';
			
			$expArray = array(
						'e-mail address' 			=> 'HomeEmail',
						'e-mailaddress' 			=> 'HomeEmail',
						'emailaddress'	 			=> 'HomeEmail',
						'e-mail'		 			=> 'HomeEmail',
						'email'			 			=> 'HomeEmail',
						'notes' 					=> 'Notes',
						'homeaddress' 				=> 'HomeStreet',
						'home street' 				=> 'HomeStreet',
						'homestreet' 				=> 'HomeStreet',
						'home city' 				=> 'HomeCity',
						'homecity' 					=> 'HomeCity',
						'home postal code'			=> 'HomeZip',
						'zip'						=> 'HomeZip',
						'home state'				=> 'HomeState',
						'homestate'					=> 'HomeState',
						'home country/region'		=> 'HomeCountry',
						'home country'				=> 'HomeCountry',
						'homecountry'				=> 'HomeCountry',
						'home phone'				=> 'HomePhone',
						'homephone'					=> 'HomePhone',
						'home fax'					=> 'HomeFax',
						'homefax'					=> 'HomeFax',
						'mobile phone'				=> 'HomeMobile',
						'mobilephone'				=> 'HomeMobile',
						'personal web page'			=> 'HomeWeb',
						'personalwebpage'			=> 'HomeWeb',
						'web page'					=> 'HomeWeb',
						'webpage'					=> 'HomeWeb',
						'company'					=> 'BusinessCompany',
						'business street'			=> 'BusinessStreet',
						'businessstreet'			=> 'BusinessStreet',
						'business city'				=> 'BusinessCity',
						'businesscity'				=> 'BusinessCity',
						'business state'			=> 'BusinessState',
						'businessstate'				=> 'BusinessState',
						'business postal code'		=> 'BusinessZip',
						'business country/region'	=> 'BusinessCountry',
						'business country'			=> 'BusinessCountry',
						'job title'					=> 'BusinessJobTitle',
						'jobtitle'					=> 'BusinessJobTitle',
						'department'				=> 'BusinessDepartment',
						'office location'			=> 'BusinessOffice',
						'officelocation'			=> 'BusinessOffice',
						'business phone'			=> 'BusinessPhone',
						'businessphone'				=> 'BusinessPhone',
						'business fax'				=> 'BusinessFax',
						'businessfax'				=> 'BusinessFax',
						'business web page'			=> 'BusinessWeb',
						'businesswebpage'			=> 'BusinessWeb',

						# custom 'end. de email'			=> 'HomeEmail',
					);

			$headerArray = array();
			while (($data = fgetcsv($handle, 2000, $delimiter)) !== false)
			{
				$num = count($data);
				$contactsCount++;
				
				if ($num < 2)
				{
					$contactsCount = ($contactsCount == 1) ? 0 : $contactsCount;
					continue;
				}
				
				if ($contactsCount === 1)
				{
					$headerArray = $data;
					continue;
				}
				
				if ($contactsCount > 1 && count($headerArray) > 0)
				{
					$newContact = new AddressBookRecord();
					$firstName = '';
					$lastName = '';
					$middleName = '';
					$nickName = '';
					$name = '';
					
					for ($c = 0; $c < $num; $c++)
					{
						if (!isset($data[$c]) || strlen($data[$c]) == 0)
						{
							continue;
						}
						
						$thisHeader = strtolower(trim($headerArray[$c]));

						if ($thisHeader ==  'first name' || $thisHeader ==  'firstname'
								# custom || $thisHeader ==  'primeiro nome' || $thisHeader ==  'nome'
								)
						{
							$firstName = $data[$c];
							continue;
						}
						if ($thisHeader ==  'last name' || $thisHeader ==  'lastname'
								# custom || $thisHeader ==  'sobrenome'
								)
						{
							$lastName = $data[$c];
							continue;
						}
						if ($thisHeader ==  'middle name' || $thisHeader ==  'middlename'
								# custom || $thisHeader ==  'segundo nome'
								)
						{
							$middleName = $data[$c];
							continue;
						}
						if ($thisHeader ==  'nickname'
								# custom || $thisHeader ==  'apelido'
								)
						{
							$nickName = $data[$c];
							continue;
						}
						if ($thisHeader ==  'name'
								# custom || $thisHeader ==  'nome'
								)
						{
							$name = $data[$c];
							continue;
						}
						if ($thisHeader ==  'birthday')
						{
							$pos1 = (int) strrpos($data[$c], '.');
							$pos2 = (int) strrpos($data[$c], '/');
							
							$dateDelimiter = ($pos1 > $pos2) ? '.' : '/';							
							
							$timeArray = explode($dateDelimiter, $data[$c]);
							$cnt = count($timeArray);
							
							if ($cnt >= 3)
							{
								$Month = ((int) $timeArray[$cnt-3] > 0) ? (int) $timeArray[$cnt-3] : null;
								$Day = ((int) $timeArray[$cnt-2] > 0) ? (int) $timeArray[$cnt-2] : null;
								$Year = ((int) $timeArray[$cnt-1] > 0) ? (int) $timeArray[$cnt-1] : null;
								
								if ($Month > 12)
								{
									$temp1 = $Day;
									$Day = $Month;
									$Month = $temp1;
								}
								
								$lenYear = strlen($Year);
								if ($lenYear <= 2)
								{
									if ($Year && $lenYear == 1)
									{
										$Year = (int) '200'.$Year;
									}
									elseif ($Year)
									{
										if ($Year > (int) date('y', time()))
										{
											$Year = (int) '19'.$Year;
										}
										else 
										{
											$Year = (int) '20'.$Year;
										}
									}
								}
								$newContact->BirthdayMonth = $Month;
								$newContact->BirthdayDay = $Day;
								$newContact->BirthdayYear = $Year;
							}

							continue;
						}

						if (isset($expArray[$thisHeader]))
						{
							$newContact->$expArray[$thisHeader] = trim($data[$c]);
						}
					}
					
					$firstName = ($firstName) ? $firstName : $name;
					$newContact->FullName = trim($firstName);
					$newContact->FullName .= ' '.trim($middleName);
					$newContact->FullName = trim($newContact->FullName).' '.trim($lastName);
					$newContact->FullName = ($nickName) ? trim($newContact->FullName).' ('.trim($nickName).')' : trim($newContact->FullName);
					$newContact->FullName = trim($newContact->FullName, ',');
					
					$newContact->IdUser = $account->IdUser;
										
					if($newContact->validateData() === true)
					{
						$ContactArray[] = $newContact;
					}	
				}
			}
			@fclose($handle);
		}
		else 
		{
			$ErrorInt = 0;
		}
		
		// delete import file
		@unlink($fs->GetFolderFullPath($attfolder).'/'.$tempname);
	
		$contactsCount = count($ContactArray);
		if ($contactsCount > 0)
		{
			$insertResult = true;

			$contactManager =& ContactCreator::CreateContactStorage($account, $settings);
			for ($i = 0; $i < $contactsCount; $i++)
			{
				$insertResult &= $contactManager->CreateContact($ContactArray[$i]);
			}
			
			if ($insertResult)
			{
				$_SESSION['action_report'] = JS_LANG_InfoHaveImported.' '.$contactsCount.' '.JS_LANG_InfoNewContacts;
			}
			else
			{
				$ErrorInt = 0;
			}
		}
		else 
		{
			if ($isNullFile)
			{
				$ErrorInt = ($ErrorInt == 1) ? 2 : $ErrorInt;
			}
			else 
			{
				$ErrorInt = 2;
			}
		}
	}
	else 
	{
		die('<script type="text/javascript">alert("'.ConvertUtils::ClearJavaScriptString($Error_Desc, '"').'");</script>');
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html>
<head>
	<title></title>
</head>
<body>
	<script type="text/javascript">
		parent.ImportContactsHandler(<?php echo $ErrorInt;?>, <?php echo $contactsCount;?>);
	</script>
</body>
</html>
<?php @ob_end_flush();