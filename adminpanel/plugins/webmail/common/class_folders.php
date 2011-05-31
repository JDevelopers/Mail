<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	class Folder
	{
		/**
		 * @var int
		 */
		var $IdDb;
		
		/**
		 * @var int
		 */
		var $IdAcct;
		
		/**
		 * @var int
		 */
		var $IdParent = -1;
		
		/**
		 * @var short
		 */
		var $Type;

		/**
		 * @var string
		 */
		var $Name;
		
		/**
		 * @var string
		 */
		var $FullName;
		
		/**
		 * @var short
		 */
		var $SyncType;
		
		/**
		 * @var bool
		 */
		var $Hide = false;
		
		/**
		 * @var int
		 */
		var $FolderOrder;
		
		/**
		 * @var int
		 */
		var $MessageCount = 0;

		/**
		 * @var int
		 */
		var $UnreadMessageCount = 0;
		
		/**
		 * @var int
		 */
		var $Size = 0;
		
		/**
		 * @var FolderCollection
		 */
		var $SubFolders = null;
		
		/**
		 * @var int
		 */
		var $Level;
		
		/**
		 * @var bool
		 */
		var $ToFolder = false;
		
		/**
		 * @param string $name
		 * @param string $fullName
		 * @param string $name optional
		 * @return Folder
		 */
		function Folder($idAcct, $idDb, $fullName, $name = null, $syncType = WM_FOLDERSYNC_DontSync, $forceType = null)
		{
			$this->IdAcct = $idAcct;
			$this->IdDb = $idDb;
			$this->FullName = $fullName;

			if ($name != null)
			{
				$this->Name = $name;
				
				$this->SyncType = $syncType;
				
				if (null !== $forceType)
				{
					$this->Type = $forceType;
				}
				else 
				{
					switch(strtolower($name))
					{
						case strtolower(WM_FOLDERNAME_Inbox):
							$this->Type = WM_FOLDERTYPE_Inbox;
							break;
						case strtolower(WM_FOLDERNAME_Sent):
						case strtolower(WM_FOLDERNAME_SentItems):
							$this->Type = WM_FOLDERTYPE_SentItems;
							break;
						case strtolower(WM_FOLDERNAME_Drafts):
							$this->Type = WM_FOLDERTYPE_Drafts;
							break;
						case strtolower(WM_FOLDERNAME_Trash):
							$this->Type = WM_FOLDERTYPE_Trash;
							break;
						case strtolower(WM_FOLDERNAME_Spam):
							$this->Type = WM_FOLDERTYPE_Spam;
							break;
						case strtolower(WM_FOLDERNAME_Virus):
							$this->Type = WM_FOLDERTYPE_Virus;
							break;
						default:
							$this->Type = WM_FOLDERTYPE_Custom;
					}
				}
			}
		}

		function SetFolderSync($syncType = FOLDERSYNC_DontSync)
		{
			$this->SyncType = $syncType;
		}
		
		/**
		 * @return string/bool
		 */
		function ValidateData()
		{
			if (empty($this->Name))
			{
				return JS_LANG_WarningEmptyFolderName;
			}
			elseif(!ConvertUtils::CheckDefaultWordsFileName($this->Name) || Validate::HasSpecSymbols($this->Name))
			{
				return WarningCorrectFolderName;
			}
			
			return true;	
		}
	}
	
	class FolderCollection extends CollectionBase
	{
		function FolderCollection()
		{
			CollectionBase::CollectionBase();
		}
		
		/**
		 * @param Folder $folder
		 */
		function Add(&$folder)
		{
			$this->List->Add($folder);
		}
		
		/**
		 * @param Folder $folder
		 */
		function AddCopy($folder)
		{
			$this->List->Add($folder);
		}
		
		/**
		 * @param int $index
		 * @return Folder
		 */
		function &Get($index)
		{
			return $this->List->Get($index);
		}
		
		/**
		 * @param short $type
		 * @return Folder
		 */
		function &GetFolderByType($type)
		{
			$null = null;
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder =& $this->Get($i);
				if (!$folder)
				{
					continue;
				}
				
				if ($folder->Type === $type)
				{
					return $folder;
				}
				if ($folder->Type === WM_FOLDERTYPE_Inbox && $folder->SubFolders !== null)
				{
					$inboxSub =& $folder->SubFolders->GetFolderByType($type);
					if ($inboxSub !== null)
					{
						return $inboxSub;
					}
				}
			}

			return $null;
		}
		
		/**
		 * @param string $name
		 * @return Folder
		 */
		function &GetFolderByName($name)
		{
			$null = null;
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder = &$this->Get($i);
				if (!$folder)
				{
					continue;
				}
				if ($folder->Name == $name)
				{
					return $folder;
				}
			}

			return $null;
		}
		
		/**
		 * @param short $type
		 * @return Folder
		 */
		function &GetFirstNotHideFolder()
		{
			$null = null;
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder = &$this->Get($i);
				if (!$folder)
				{
					continue;
				}
				if (!$folder->Hide)
				{
					return $folder;
				}
			}

			return $null;
		}
		
		/**
		 * @param int $type
		 * @return Folder
		 */
		function &GetFolderById($id)
		{
			$folders = &$this->CreateFolderListFromTree();
			$null = null;
			for ($i = 0, $c = $folders->Count(); $i < $c; $i++)
			{
				$curfolder = &$folders->Get($i);
				if (!$curfolder)
				{
					continue;
				}
				
				if ($curfolder->IdDb === $id)
				{
					return $curfolder;
				}
			}

			return $null;
		}
		
		/**
		 * @return FolderCollection
		 */
		function &CreateFolderListFromTree()
		{
			$folderList = new FolderCollection();
			$this->_createFolderListFromTree($folderList);
			return $folderList;
		}

		/**
		 * @access private
		 * @param FolderCollection $folderList
		 */
		function _createFolderListFromTree(&$folderList)
		{
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder =& $this->Get($i);
				if (!$folder)
				{
					continue;
				}
				
				$folderList->Add($folder);
				if (!is_null($folder->SubFolders) && $folder->SubFolders->Count() > 0)
				{
					$folder->SubFolders->_createFolderListFromTree($folderList);
				}
				unset($folder);
			}
		}
		
		function SetSyncTypeToAll($syncType)
		{
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder =& $this->Get($i);
				if (!$folder)
				{
					continue;
				}
				
				$folder->SyncType = $syncType;
				
				if (!is_null($folder->SubFolders) && $folder->SubFolders->Count() > 0)
				{
					$folder->SubFolders->SetSyncTypeToAll($syncType);
				}
			}			
		}
		
		/**
		 * @param Folder $folder
		 */
		function InitToFolder(&$folder)
		{
			$sent =& $this->GetFolderByType(WM_FOLDERTYPE_SentItems);
			if ($sent)
			{
				if ($sent->IdDb == $folder->IdDb)
				{
					$folder->ToFolder = true;
					return;
				}
				else if ($sent->SubFolders && $sent->SubFolders->Count() > 0)
				{
					$sent->SubFolders->_setToFolderInSentDrafts($folder);
				}
			}
			
			$drafts =& $this->GetFolderByType(WM_FOLDERTYPE_Drafts);
			if ($drafts)
			{
				if ($drafts->IdDb == $folder->IdDb)
				{
					$folder->ToFolder = true;
					return;
				}
				else if ($drafts->SubFolders && $drafts->SubFolders->Count() > 0)
				{
					$drafts->SubFolders->_setToFolderInSentDrafts($folder);
				}
			}
		}
		
		/**
		 * @param Folder $initFolder
		 */
		function _setToFolderInSentDrafts(&$initFolder)
		{
			for ($i = 0, $c = $this->Count(); $i < $c; $i++)
			{
				$folder =& $this->Get($i);
				if ($folder)
				{
					if ($initFolder->IdDb == $folder->IdDb)
					{
						$initFolder->ToFolder = true;
						return;
					}
					else if ($folder->SubFolders && $folder->SubFolders->Count() > 0)
					{
						$folder->SubFolders->_setToFolderInSentDrafts($initFolder);
					}
				}
			}
		}
		
		/**
		 * @return FolderCollection $folders
		 */
		function SortRootTree()
		{
			return $this->_sortFolderCollection(true);
		}
		
		/**
		 * @param bool $sortSpecialFolders[optional] = false
		 * @return FolderCollection
		 */
		function _sortFolderCollection($sortSpecialFolders = false)
		{
			$newFoldersArray = $topArray = $footArray = array();
			$newFolders = new FolderCollection();
			
			foreach ($this->Instance() as $folder)
			{
				if (strlen($folder->Name) > 0 && $folder->Name[0] == '&')
				{
					$footArray[] = $folder->Name;
				}
				else 
				{
					$topArray[] = $folder->Name;
				}
			}
			unset($folder);
			
			natcasesort($topArray); 
			
			foreach ($topArray as $value)
			{
				$newFoldersArray[strtolower($value)] = $value;
			}
			foreach ($footArray as $value)
			{
				$newFoldersArray[strtolower($value)] = $value;
			}
			unset($topArray, $footArray);

			if ($sortSpecialFolders)
			{
				$this->_localSortFunction(WM_FOLDERNAME_Inbox, $newFoldersArray, $newFolders, true);
				$this->_localSortFunction(WM_FOLDERNAME_SentItems, $newFoldersArray, $newFolders);
				$this->_localSortFunction(WM_FOLDERNAME_Sent, $newFoldersArray, $newFolders);
				$this->_localSortFunction(WM_FOLDERNAME_Drafts, $newFoldersArray, $newFolders);
				$this->_localSortFunction(WM_FOLDERNAME_Spam, $newFoldersArray, $newFolders);
				$this->_localSortFunction(WM_FOLDERNAME_Trash, $newFoldersArray, $newFolders);
			}
			
			foreach ($newFoldersArray as $folderName)
			{
				$folder =& $this->GetFolderByName($folderName);
				if ($folder)
				{
					if ($folder->SubFolders && $folder->SubFolders->Count() > 1)
					{
						$folder->SubFolders = $folder->SubFolders->_sortFolderCollection();
					}
					$newFolders->Add($folder);
				}
				unset($folder);
			}
			
			return $newFolders;
		}

		function _localSortFunction($folderName, &$newFoldersArray, &$newFolders, $subSort = false)
		{
			if (isset($newFoldersArray[strtolower($folderName)]))
			{
				$folder =& $this->GetFolderByName($newFoldersArray[strtolower($folderName)]);
				if ($folder)
				{
					if ($folder->SubFolders && $folder->SubFolders->Count() > 1)
					{
						$folder->SubFolders = $folder->SubFolders->_sortFolderCollection($subSort);
					}
					$newFolders->Add($folder);
					unset($newFoldersArray[strtolower($folderName)]);
					unset($folder);
				}
			}
		}
	}