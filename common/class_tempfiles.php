<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

class CTempFiles
{
	/**
	 * 	# CFileSystemTempFilesDriver
	 * 	# CDataBaseTempFilesDriver
	 * 
	 * @access private
	 * @var CFileSystemTempFilesDriver
	 */
	var $_driver;
	
	/**
	 * @var string $tempName
	 * @return string|false
	 */	
	function LoadFile($tempName)
	{
		return $this->_driver->LoadFile($tempName);
	}
	
	/**
	 * @var string $tempName
	 * @var string $body
	 * @return int (file size or -1)
	 */	
	function SaveFile($tempName, $body)
	{
		return $this->_driver->SaveFile($tempName, $body);
	}
	
	/**
	 * @var string $tempName
	 * @return bool
	 */
	function IsFileExist($tempName)
	{
		return $this->_driver->IsFileExist($tempName);
	}
	
	/**
	 * @var string $tempName
	 * @return int | false
	 */
	function FileSize($tempName)
	{
		return $this->_driver->FileSize($tempName);
	}
	
	function MoveUploadedFile($serverTempFile, $fileTempName)
	{
		return $this->_driver->MoveUploadedFile($serverTempFile, $fileTempName);
	}
	
	function ClearAccountCompletely()
	{
		return $this->_driver->ClearAccountCompletely();
	}
	
	function ClearAccount()
	{
		return $this->_driver->ClearAccount();
	}
	
	/**
	 * @static
	 * @return CTempFiles
	 */
	function &CreateInstance($account)
	{
		static $instance = null;
    	if (null === $instance)
    	{
			$instance = new CTempFiles($account);
    	}

    	return $instance;
	}
	
	/**
	* @access private
	*/
	function CTempFiles($account)
	{
		$this->_driver = (false) 
			? new CDataBaseTempFilesDriver($account)
			: new CFileSystemTempFilesDriver($account);	
	}
}

class CFileSystemTempFilesDriver
{
	/**
	 * @access private
	 * @var Folder
	 */
	var $_folder;
	
	/**
	 * @access private
	 * @var FileSystem
	 */
	var $_fs;
	
	/**
	 * @param	Account	$accout
	 * @return	CFileSystemTempFilesDriver
	 */
	function CFileSystemTempFilesDriver($account)
	{
		$this->_fs = new FileSystem(INI_DIR.'/temp', strtolower($account->Email), $account->Id);
	    $this->_folder = new Folder($account->Id, -1, GetSessionAttachDir());
	}
	
	/**
	 * @var string $tempName
	 * @return string|false
	 */	
	function LoadFile($tempName)
	{
		return @file_get_contents($this->_fs->GetFolderFullPath($this->_folder).'/'.$tempName);
	}
	
	/**
	 * @var string $tempName
	 * @var string $body
	 * @return int (save file size or -1)
	 */	
	function SaveFile($tempName, $body)
	{
		$this->_fs->CreateFolder($this->_folder);
		
		$fileName = $this->_fs->GetFolderFullPath($this->_folder).'/'.$tempName;
		$returnBool = true;
		$fh = @fopen($fileName, 'wb');
		if ($fh)
		{
			if (!@fwrite($fh, $body))
			{
				setGlobalError('can\'t write file: '.$fileName);
				$returnBool = false;
			}
			@fclose($fh);
		}
		else 
		{
			setGlobalError('can\'t open file(wb): '.$fileName);
			$returnBool = false;
		}
		
		if ($returnBool && null !== $body)
		{
			return strlen($body);
		}
		
		return -1;
	}
	
	/**
	 * @var string $tempName
	 * @return bool
	 */
	function IsFileExist($tempName)
	{
		return $this->_fs->IsTempFileExist($this->_folder, $tempName);
	}
	
	/**
	 * @var string $tempName
	 * @return int | false
	 */
	function FileSize($tempName)
	{
		return @filesize($this->_fs->GetFolderFullPath($this->_folder).'/'.$tempName);
	}
	
	function MoveUploadedFile($serverTempFile, $fileTempName)
	{
		$this->_fs->CreateFolder($this->_folder);
		return @move_uploaded_file($serverTempFile, $this->_fs->GetFolderFullPath($this->_folder).'/'.$fileTempName);
	}
	
	function ClearAccountCompletely()
	{
		$this->_fs->DeleteAccountDirs();
		return true;
	}
	
	function ClearAccount()
	{
		$this->_fs->DeleteDir($this->_folder);
		return true;
	}
}

class CDataBaseTempFilesDriver
{
	/**
	 * @var string
	 */
	var $_hash;
	
	/**
	 * @access private
	 * @var Folder
	 */
	var $_folder;
	
	/**
	 * @access private
	 * @var FileSystem
	 */
	var $_fs;
	
	/**
	 * @access private
	 * @var Folder
	 */
	var $_db;
	
	/**
	 * @param	Account	$accout
	 * @return	CDataBaseTempFilesDriver
	 */
	function CDataBaseTempFilesDriver($account)
	{
		$this->_hash = GetSessionAttachDir();
		$this->_fs = new FileSystem(INI_DIR.'/temp', strtolower($account->Email), $account->Id);
	    $this->_folder = new Folder($account->Id, -1, GetSessionAttachDir());
		$this->_db =& DbStorageCreator::CreateDatabaseStorage($account);
	}
	
	/**
	 * @var string $tempName
	 * @return string|false
	 */	
	function LoadFile($tempName)
	{
		if ($this->_db->Connect())
		{
			return $this->_db->TempFilesLoadFile($tempName, $this->_hash);
		}
		return false;
	}
	
	/**
	 * @var string $tempName
	 * @var string $body
	 * @return int (save file size or -1)
	 */	
	function SaveFile($tempName, $body)
	{
		if ($this->_db->Connect())
		{
			$size = $this->_db->TempFilesFileSize($tempName, $this->_hash);
			if (false === $size)
			{
				return $this->_db->TempFilesSaveFile($tempName, $this->_hash, $body);
			}
			
			return $size;
		}
		return -1;
	}
	
	/**
	 * @var string $tempName
	 * @return bool
	 */
	function IsFileExist($tempName)
	{
		if ($this->_db->Connect())
		{
			return $this->_db->TempFilesIsFileExist($tempName, $this->_hash);
		}
		return false;
	}
	
	/**
	 * @var string $tempName
	 * @return int | false
	 */
	function FileSize($tempName)
	{
		if ($this->_db->Connect())
		{
			return $this->_db->TempFilesFileSize($tempName, $this->_hash);
		}
		return false;
	}
	
	
	function MoveUploadedFile($serverTempFile, $fileTempName)
	{
		$this->_fs->CreateFolder($this->_folder);
		if (@move_uploaded_file($serverTempFile, $this->_fs->GetFolderFullPath($this->_folder).'/'.$fileTempName))
		{
			$file = @file_get_contents($this->_fs->GetFolderFullPath($this->_folder).'/'.$fileTempName);
			if (false !== $file)
			{
				$this->SaveFile($fileTempName, $file);
				@unlink($this->_fs->GetFolderFullPath($this->_folder).'/'.$fileTempName);
				return true;
			}
		}
		return false;
	}
	
	
	function ClearAccountCompletely()
	{
		$this->_fs->DeleteAccountDirs();
		if ($this->_db->Connect())
		{
			return $this->_db->TempFilesClearAccountCompletely();
		}
		return false;
	}
	
	
	function ClearAccount()
	{
		$this->_fs->DeleteDir($this->_folder);
		if ($this->_db->Connect())
		{
			return $this->_db->TempFilesClearAccount($this->_hash);
		}
		return false;
	}
}
