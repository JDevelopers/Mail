<?php
/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 *
 */

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../../'));
require_once(WM_ROOTPATH.'plugins/outlooksync/class_sync_base.php');

class SyncContacts extends SyncBase
{
	/**
	 * @param	Account	$account
	 * @param	string	$action
	 */
	public function __construct($account, $action)
	{
		parent::__construct();
		
		$this->managerPath = WM_ROOTPATH.'api/webmail/contact_manager.php';
		$this->managerName = 'ContactManager';
		$this->init($account, $action);
		$this->manager->InitAccount($account);
	}

	public function processing()
	{
		if (is_object($this->manager) && $this->manager->InitManager())
		{
			switch ($this->action)
			{
				case SYNC_ACTION_GET:
					$this->_exportVcard();
				break;
				case SYNC_ACTION_POST:
					$this->_importVcard();
				break;
				default:
					$this->responseStatus = X_WMP_RESPONSE_STATUS_ERROR_ACTION;
				break;
			}
		}
	}

	private function _exportVcard()
	{
		$contactsList = $this->manager->GetFullContactsList();
		if (is_array($contactsList))
		{
			foreach ($contactsList as $contactContainer)
			{
				$this->response .= $this->manager->ExportVcard($contactContainer);
			}
			
			$this->responseStatus = X_WMP_RESPONSE_STATUS_OK;
		}
		else
		{
			$this->responseStatus = X_WMP_RESPONSE_STATUS_ERROR_DB_FAULT;
		}
	}

	private function _importVcard()
	{
		$vcardText = implode('', file('php://input'));

		if (class_exists('CLog'))
		{
			$log =& CLog::CreateInstance();
			$log->WriteLine('Sync: Import Contacts[Body] / LENGTH = '.strlen($vcardText));
			$log->WriteLine('Sync: Import Contacts[Body] / RAW = '."\r\n".$vcardText);
		}
		
		$tmpfname = tempnam('/tmp', '');
		file_put_contents($tmpfname, $vcardText);
		$result = $this->manager->ImportVcard($tmpfname);
		unlink($tmpfname);
		$this->responseStatus = ($result) ? X_WMP_RESPONSE_STATUS_OK : X_WMP_RESPONSE_STATUS_ERROR_DB_FAULT;
		$this->response = $result;
	}
}