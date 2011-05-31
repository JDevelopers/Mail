<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	define('FOLDER_LIST_INDEX_DELIMITR', '#$%$#');

	/**
	 * @abstract
	 */
	class MailStorage
	{
		/**
		 * @access protected
		 * @var Account
		 */
		var $_account;
	
		/**
		 * @access protected
		 * @var resource
		 */
		var $_connectionHandle = null;
		
		/**
		 * @var string
		 */
		var $_error = '';
		
		/**
		 * @param Account $account
		 * @return MailStorage
		 */
		function MailStorage(&$account)
		{
			$this->_account =& $account;
		}
		
		function GetFolderCollectionFromArrays($folders, $subScrFolders, $seporator, $existsIndex)
		{
			$newFolderArray = array();
			if ($folders)
			{
				foreach ($folders as $folder)
				{
					$p = null;
					$fullName = array();
					$temp = array();
					$p =& $temp;
					$seporatedNames = explode($seporator, $folder);
					foreach ($seporatedNames as $name)
					{
						$fullName[] = $name;
						$name .= FOLDER_LIST_INDEX_DELIMITR.implode($seporator, $fullName);
						$temp[$name] = null;
						$temp =& $temp[$name];
					}

					$newFolderArray = array_merge_recursive($newFolderArray, $p);
					unset($p, $temp, $fullName);
				}
			}

			$folderCollection = new FolderCollection();
			$this->_recFillFolderCollection($folderCollection, $newFolderArray, $subScrFolders, $existsIndex);
			
			return $folderCollection;
		}
	
		function _recFillFolderCollection(&$folderCollection, $folders, $subScrFolders, &$existsIndex, $isInbox = false)
		{
			foreach ($folders as $folder => $subFolders)
			{
				$folderName = $folderFullName = null;
				$tArray = explode(FOLDER_LIST_INDEX_DELIMITR, $folder);
				if (count($tArray) != 2)
				{
					continue;
				}
				
				$folderName = $tArray[0];
				$folderFullName = $tArray[1];
				
				$folderObj = new Folder($this->_account->Id, -1, $folderFullName, $folderName);
							
				if ($isInbox || $folderName == $folderFullName)
				{
					$this->SetFolderType($folderObj, $existsIndex);
				}
				else 
				{
					$folderObj->Type = WM_FOLDERTYPE_Custom;
				}
				
				$folderObj->Hide = (defined('USE_LSUB') && USE_LSUB) ? false : !in_array($folderObj->FullName, $subScrFolders);
				if ($folderObj->Type != WM_FOLDERTYPE_Custom)
				{
					$folderObj->Hide = false;
				}
				
				if (null !== $subFolders && is_array($subFolders))
				{
					$newCollection = new FolderCollection();
					
					$this->_recFillFolderCollection($newCollection, $subFolders, $subScrFolders, $existsIndex, $folderObj->Type == WM_FOLDERTYPE_Inbox);
					
					if ($newCollection->Count() > 0)
					{
						$folderObj->SubFolders = $newCollection;
					}
					
					unset($newCollection);
				}
				
				$folderCollection->Add($folderObj);
				unset($folderObj);
			}
		}
	
		function SetFolderType(&$folderObj, &$existsIndex)
		{
			$folderObj->Type = WM_FOLDERTYPE_Custom;
		}
		
		/**
		 * @param string $str
		 */
		function SetError($str)
		{
			if ($this->_error === '')
			{
				$this->_error = $str;
			}
		}
		
		/**
		 * @return	string
		 */
		function GetError()
		{
			return $this->_error;
		}
	}
		
	/**
	 * @abstract
	 */
	class MailServerStorage extends MailStorage
	{
		/**
		 * @param Account $account
		 * @return MailServerStorage
		 */
		function MailServerStorage(&$account)
		{
			MailStorage::MailStorage($account);
		}
	}