<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	define('ACCOUNT_HIERARCHY_DEPTH', 1);
	
	class FileSystem
	{
		/**
		 * @var string
		 */
		var $RootFolder;
		
		/**
		 * @var string
		 */
		var $AccountName;
		
		/**
		 * @access private
		 * @var int
		 */
		var $_accountId;
		
		/**
		 * @param string $accountName
		 * @param string $rootFolder
		 * @return FileSystem
		 */
		function FileSystem($rootFolder, $accountName, $accountId)
		{
			$this->_accountId = $accountId;
			$this->AccountName = $accountName.'.'.$accountId;
			$this->RootFolder = $rootFolder;
		}
		
		/**
		 * @param Folder $folder
		 * @return bool
		 */
		function CreateFolder(&$folder)
		{
			$path = rtrim($this->_createFolderFullPath($folder->FullName), '/');
			if (@is_dir($path))
			{
				return true;
			}
			
			return $this->_createRecursiveFolderPath($path);
		}
		
		/**
		 * @param Folder $folder
		 * @return string
		 */
		function GetFolderFullPath(&$folder)
		{
			return rtrim($this->_createFolderFullPath($folder->FullName), '/');
		}
		

		/**
		 * @param string $string
		 * @return bool
		 */
		function CreateFolderFromString($string)
		{
			$path = rtrim($this->_createFolderFullPath($string), '/');
			if (@is_dir($path))
			{
				return true;
			}
			
			return $this->_createRecursiveFolderPath($path);
		}

		/**
		 * @access private
		 * @param string $path
		 * @return bool
		 */
		function _createRecursiveFolderPath($path)
		{
			$result = true;
			$rootFolder = substr($path, 0, strrpos($path, '/'));
			if (!@is_dir($rootFolder))
			{
				$result &= $this->_createRecursiveFolderPath($rootFolder);
			}
			$result &= @mkdir($path);
			return $result;
		}
		
		/**
		 * @access private
		 * @param string $folder
		 * @return string
		 */
		function _createFolderFullPath($folder)
		{
			$returnPath = $this->RootFolder.'/';

			for ($i = 0; $i <= ACCOUNT_HIERARCHY_DEPTH - 1; $i++)
			{
				$returnPath .= $this->AccountName{$i}.'/';
			}

			$returnPath .= $this->AccountName.'/'.$folder;
			return rtrim($returnPath, '/\\');
		}
		
		/**
		 * @param Folder $folder
		 */
		function ClearDir(&$folder)
		{
			$path = $this->_createFolderFullPath($folder->FullName);

			if (@is_dir($path))
			{
				$dh = @opendir($path);
				if ($dh)
				{
					while (($file = @readdir($dh)) !== false)
					{
						if ($file != '.' && $file != '..')
						{ 
							@unlink($path.'/'.$file);
						} 
					}
					@closedir($dh);
				}
			}
		}
		
		/**
		 * @param Folder $folder
		 */
		function DeleteDir(&$folder)
		{
			$path = $this->_createFolderFullPath($folder->FullName);
			$count = 0;
			
			if (@is_dir($path))
			{
				$dh = @opendir($path);
				if ($dh)
				{
					while (($file = @readdir($dh)) !== false)
					{
						if ($file != '.' && $file != '..')
						{ 
							$count++;
						} 
					}
					@closedir($dh);
				}
				if ($count) 
				{
					$this->ClearDir($folder);
				}
				@rmdir($path);
			}
		}
		
		/**
		 * @param string $folderPath
		 * @return bool
		 */
		function IsFolderExist($folderPath)
		{
			$accountPath = rtrim($this->_createFolderFullPath(''), '/');
			
			return @is_dir($accountPath.'/'.$folderPath);
		}

		/**
		 * @param string[optional] $subfolder
		 * @return bool
		 */
		function DeleteAccountDirs($subfolder = '')
		{
			$path = $this->_createFolderFullPath($subfolder);
			
			if (@is_dir($path))
			{
				$dh = @opendir($path);
				if ($dh)
				{
					while (($file = @readdir($dh)) !== false)
					{
						if ($file != '.' && $file != '..')
						{ 
							if (@is_dir($path.'/'.$file))
							{
								$this->DeleteAccountDirs($file);
							}
							else 
							{
								@unlink($path.'/'.$file);
							}
						} 
					}
					@closedir($dh);
				}
				@rmdir($path);
			}
			
			return true;
		}

		/**
		 * @param array $filesArray
		 * @param string[optional] $subfolder
		 * @return bool
		 */
		function DeleteTempFilesByArray($filesArray, $subfolder = '')
		{
			if (!is_array($filesArray) || count($filesArray) < 1)
			{
				return true;
			}
			
			$path = $this->_createFolderFullPath($subfolder);

			if (@is_dir($path))
			{
				$dh = @opendir($path);
				if ($dh)
				{
					while (($file = @readdir($dh)) !== false)
					{
						if ($file != '.' && $file != '..')
						{ 
							if (@is_dir($path.'/'.$file))
							{
								$this->DeleteTempFilesByArray($filesArray, $file);
							}
							else 
							{
								if (in_array($file, $filesArray))
								{
									@unlink($path.'/'.$file);
								}
							}
						} 
					}
					@closedir($dh);
				}
			}
		
			return true;
		}

	}
