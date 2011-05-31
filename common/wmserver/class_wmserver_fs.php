<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

define('GETPARAMSTYPE_ALL', 0);
define('GETPARAMSTYPE_NULL', 1);

class WMserverFS
{
	/**
	 * @var string
	 */
	var $basePath = '';
	
	/**
	 * @var	string
	 */
	var $_uidD = '2,';
	
	/**
	 * @var	string
	 */
	var $_uidSizeD = ',S=';
	
	/**
	 * @var string
	 */
	var $domain = '';
	
	/**
	 * @var string
	 */
	var $path = '';
	var $pathnew = '';
	var $pathcur = '';
	
	/**
	 * @var string
	 */
	var $userName = '';
	
	/**
	 * @var array
	 */
	var $staticParam = array();
	
	/**
	 * @access private
	 * @var CLog
	 */
	var $_log;
	
	/**
	 * @access public
	 * @param string $basePath
	 * @param string $email
	 * @return WMserverFS
	 */
	function WMserverFS($basePath, $email)
	{
		$parsedEmail = ConvertUtils::ParseEmail($email);
		
		$this->userName = trim($parsedEmail[0]);
		$this->domain = trim($parsedEmail[1]);
		
		$this->basePath = rtrim($basePath, '/\\');

		$this->path = $this->basePath.'/domains/'.$this->domain.'/'.$this->userName.'/Maildir/';
		
		$this->_log =& CLog::CreateInstance();
	}
	
	/**
	 * @return array
	 */
	function getFolders()
	{
		$folders = array();

		if (@is_dir($this->path))
		{
			if (is_dir($this->path.'new/') && is_dir($this->path.'cur/'))
			{
				$folders[] = 'INBOX';
			}
			
			$dir = @dir($this->path);
			if ($dir)
			{
				while (false !== ($entry = $dir->read()))
				{
					if($entry != '.' && $entry != '..' && strlen($entry) > 0 && $entry{0} == '.' && is_dir($this->path.$entry))
					{	
						$folders[] = substr($entry, 1);
					}
				}
			}
		}
		else 
		{
			$this->_log('can\'t get xmail folders '.$this->path, LOG_LEVEL_ERROR);
		}
		
		return $folders;
	}
	
	/**
	 * @param string $fullName
	 * @return bool
	 */
	function createMailbox($fullName)
	{
		$result = false;
		if (strtolower($fullName) == 'inbox')
		{
			return $result;
		}
		else if (@is_dir($this->path) && !is_dir($this->path.'.'.$fullName))
		{
			$result = @mkdir($this->path.'.'.$fullName);
			$result &= @mkdir($this->path.'.'.$fullName.'/cur/');
			$result &= @mkdir($this->path.'.'.$fullName.'/new/');
			$result &= @mkdir($this->path.'.'.$fullName.'/tmp/');
			$result &= $this->saveNewInfoTab($this->path.'.'.$fullName.'/.info.tab');
			
			$this->setSubscribeFolder($fullName, true);
		}

		return $result;
	}
	
	/**
	 * @param string $fullName
	 * @return bool
	 */
	function deleteMailbox($fullName)
	{
		$result = true;
		switch (strtolower($fullName))
		{
			case 'inbox':
				break;
			default:
				if (is_dir($this->path.'.'.$fullName) && is_dir($this->path.'.'.$fullName.'/cur/') &&
					is_dir($this->path.'.'.$fullName.'/new/') && is_dir($this->path.'.'.$fullName.'/tmp/'))
				{
					$result = @unlink($this->path.'.'.$fullName.'/.info.tab'); 
					$result &= @rmdir($this->path.'.'.$fullName.'/cur/');
					$result &= @rmdir($this->path.'.'.$fullName.'/new/');
					$result &= @rmdir($this->path.'.'.$fullName.'/tmp/');
					$result &= @rmdir($this->path.'.'.$fullName);
					
					
					if (!$result)
					{
						@mkdir($this->path.'.'.$fullName);
						@mkdir($this->path.'.'.$fullName.'/cur/');
						@mkdir($this->path.'.'.$fullName.'/new/');
						@mkdir($this->path.'.'.$fullName.'/tmp/');
						$this->saveNewInfoTab($this->path.'.'.$fullName.'/.info.tab');
					}
				}
				break;
		}

		return $result;
	}
	
	/**
	 * @param string $_filename
	 * @return bool
	 */
	function saveNewInfoTab($_filename)
	{
		if (!@file_exists($_filename))
		{
			$content = "IS.SUBSCRIBED 1\r\nUIDVALIDITY ".time()."\r\nUIDNEXT 1\r\n";
			$this->_saveFileByFileName($_filename, $content);
			return true;
		}
		return false;
	}
	
	function setSubscribeFolder($folderName, $isSubscribe = true)
	{
		$_folderName = $this->_changePath($folderName);
		$_subfile = rtrim($this->path, '/\\').'/.sub.tab';
		$_filename = $_folderName.'/.info.tab';
		
		if (@file_exists($_filename))
		{
			$_fo = @fopen($_filename, 'r+');
			if ($_fo)
			{
				$_next = false;
				$_contents = @fread($_fo, @filesize($_filename));
				$_pos = strpos($_contents, 'IS.SUBSCRIBED');
				if ($_pos !== false)
				{
					$_pos = $_pos + 14;
					$_next = (int) trim(substr($_contents, $_pos, 1));
					
					if ($_next != (int) $isSubscribe)
					{
						if (@fseek($_fo, $_pos) === 0)
						{
							$_new = (int) $isSubscribe;
							@fputs($_fo, $_new, 1);
						}
					}
				}

				@fclose($_fo); 
			}
		}
		
		$file = array();
		$exist = false;
		if (@file_exists($_subfile))
		{
			$file = @file($_subfile);
			foreach ($file as $key => $line) 
			{
				if (trim('.'.$folderName) == trim($line))
				{
					$exist = true;
					if (!$isSubscribe)
					{
						unset($file[$key]);
					}
				}
			}
		}
		
		if ($isSubscribe && !$exist)
		{
			$_fo = @fopen($_subfile, 'a');
			if ($_fo)
			{
				$text = trim('.'.$folderName)."\r\n";
				@fputs($_fo, $text, strlen($text));
				@fclose($_fo); 
			}
		}
		else if (!$isSubscribe && $exist)
		{
			$_fo = @fopen($_subfile, 'w');
			if ($_fo)
			{
				$text = implode('', $file);
				@fputs($_fo, $text, strlen($text));
				@fclose($_fo); 
			}
		}
	}

	/**
	 * @param string $uid
	 * @param string $fromFolder
	 * @param string $toFolder
	 * @return bool
	 */
	function moveMessage($uidFileName, $newUidFileName, $fromFolder, $toFolder)
	{
		$fromPath = $this->_changePath($fromFolder);
		$toPath = $this->_changePath($toFolder);
		
		if (@file_exists($fromPath.'/cur/'.$uidFileName) && @is_dir($toPath) && !@file_exists($toPath.'/cur/'.$newUidFileName))
		{
			if (@rename($fromPath.'/cur/'.$uidFileName, $toPath.'/cur/'.$newUidFileName))
			{
				$this->touchInfoTab($fromFolder);
				return true;
			}
			$this->_log('can\'t move message '.$fromPath.'/cur/'.$uidFileName.' --> '.$toPath.'/cur/'.$newUidFileName, LOG_LEVEL_ERROR);
			return false;
		}
		$this->_log('can\'t move message (file not exits) '.$fromPath.'/cur/'.$newUidFileName, LOG_LEVEL_ERROR);
		return false;
	}
	
	/**
	 * @param string $folderName
	 * @param string $newFolderName
	 * @return bool
	 */
	function renameFolder($folderName, $newFolderName)
	{
		$folderName = $this->_changePath($folderName);
		$newFolderName = $this->_changePath($newFolderName);
		
		$folders = $this->getFolders();
		
		foreach ($folders as $folder) 
		{
			$folder = $this->_changePath($folder);
			if (strpos($folder, $folderName) === 0 && $folder != $this->path)
			{
				$nextName = substr_replace($folder, $newFolderName, 0, strlen($folderName));
				if (@rename($folder, $nextName))
				{
					$this->_log('rename: '.$folder.' -> '.$nextName);	
				}
				else 
				{
					$this->_log('rename error: '.$folder.' -> '.$nextName, LOG_LEVEL_ERROR);
					return false;
				}
			}
		}

		return true;
	}
	
	/**
	 * @param string $uid
	 * @param string $folderName
	 * @return bool
	 */
	function messageExist($uid, $folderName)
	{
		$path = $this->_changePath($folderName);
		return @file_exists($path.'/cur/'.$uid);
	}
	
	/**
	 * @param string $uid
	 * @param string $folderName
	 * @return bool
	 */
	function deleteMessage($fileName, $folderName)
	{
		$path = $this->_changePath($folderName);
		if (@file_exists($path.'/cur/'.$fileName))
		{
			if (@unlink($path.'/cur/'.$fileName))
			{
				return true;	
			}
			$this->_log('can\'t delete message '.$path.'/cur/'.$fileName, LOG_LEVEL_ERROR);
			return false;
		}
		$this->_log('can\'t delete message (file not exist) '.$path.'/cur/'.$fileName, LOG_LEVEL_ERROR);
		return true;
	}
	
	/**
	 * @param string $fullName
	 * @return bool
	 */
	function moveMsgFromNewToCur($fullName)
	{
		static $farray = array();
		
		if (isset($farray[$fullName]))
		{
			return true;
		}
		else
		{
			$farray[$fullName] = true;
		}
		
		$path = $this->_changePath($fullName);
		
		$doClear = false;
		$newFileName = '';
		if (is_dir($path) && is_dir($path.'/cur/') && is_dir($path.'/new/'))
		{
			$dir = @dir($path.'/new/');
			if ($dir)
			{
				while (false !== ($entry = $dir->read()))
				{
					if($entry != '.' && $entry != '..' && is_file($path.'/new/'.$entry))
					{
						$prefix = 0;
						do
						{
							$tmp_prefix = ($prefix === 0) ? '' : $prefix . '_';
							$prefix = $prefix + 1;
							$newFileName = $path.'/cur/'.$tmp_prefix.$entry;
						}
						while (@file_exists($newFileName));

						$doClear = true;
						rename($path.'/new/'.$entry, $newFileName);	
					}
				}
			}
		}
		
		if ($doClear)
		{
			$this->clearStaticParam($fullName);
		}
		
		return true;
	}
	
	/**
	 * @param string $fullName
	 */
	function clearStaticParam($fullName)
	{
		if (isset($this->staticParam[$fullName]))
		{
			unset($this->staticParam[$fullName]);
		}
	}
	
	function getFileNameByUid($uid, $folderFullName)
	{
		if (isset($this->staticParam['one_'.$folderFullName.$uid]))
		{
			return $this->staticParam['one_'.$folderFullName.$uid];
		}
		
		$start = getmicrotime();
		$path = $this->_changePath($folderFullName);
		if (is_dir($path) && is_dir($path.'/cur/'))
		{
			$glob = @glob($path.'/cur/'.$uid.'*', GLOB_NOSORT);
			if ($glob)
			{
				foreach ($glob as $filename)
				{
					$filename = basename($filename);
					$this->staticParam['one_'.$folderFullName.$uid] = $filename;

					$time = getmicrotime() - $start;
					$this->_log->WriteLine('XMAIL: getFileNameByUid time('.$uid.'): '.$time.' ('.$path.')');
					return $filename;
				}
			}
			else
			{
				$this->_log->WriteLine('XMAIL: error glob '.$path.'/cur/'.$uid.'*');
			}
		}
		$time = getmicrotime() - $start;
		$this->_log->WriteLine('XMAIL: getFileNameByUid (false) time('.$uid.'): '.$time.' ('.$path.')');
		return false;
	}
	
	/**
	 * @param string $fullName
	 * @return array|false
	 */
	function getParamsMessages($fullName, $type = GETPARAMSTYPE_NULL)
	{
		if (isset($this->staticParam['all_'.$fullName.$type]))
		{
			return $this->staticParam['all_'.$fullName.$type];
		}

		$flags = '';
		$size = 0;

		$start = getmicrotime();
		$path = $this->_changePath($fullName);
		
		if (is_dir($path) && is_dir($path.'/cur/'))
		{
			$this->moveMsgFromNewToCur($fullName);
			
			$params = array('f' => array(), 'u' => array());
			$dir = @dir($path.'/cur/');
			if ($dir)
			{
				$arr = glob($path.'/cur/*', GLOB_NOSORT);
				if ($arr)
				{
					foreach ($arr as $entry)
					{
						if (is_file($entry))
						{
							if ($type == GETPARAMSTYPE_ALL)
							{
								$size = filesize($entry);
							}

							$entry = basename($entry);

							if ($type == GETPARAMSTYPE_ALL)
							{
								$flags = $this->_getFlagsFromFileName($entry);
							}

							$uid = $this->_getUidFromFileName($entry);

							$params['u'][$uid] = array();
							$params['u'][$uid]['filename'] = $entry;

							if ($type == GETPARAMSTYPE_ALL)
							{
								$params['u'][$uid]['flags'] = $flags;
								$params['u'][$uid]['size'] = $size;
							}
						}
					}
				}
			}
			
			$this->staticParam['all_'.$fullName.$type] = $params;
			
			if ($this->_log->Enabled)
			{
				$time = getmicrotime() - $start;
				$this->_log->WriteLine('XMAIL: getParamsMessages time('.$type.'): '.$time.' ('.$path.')');
			}
			return $params;
		}

		return false;
	}
	
	/**
	 * @param string $name
	 * @return string
	 */
	function _getFlagsFromFileName($name)
	{
		$out = '';
		if (strpos($name, $this->_uidD) !== false)
		{
			$arr = explode($this->_uidD, $name);
			if (is_array($arr) && count($arr) > 1)
			{
				$flags = strtolower($arr[count($arr)-1]);
				if (strlen($flags) > 0)
				{
					if (strpos($flags, 's') !== false)
					{
						$out .= '\Seen ';
					}
					
					if (strpos($flags, 't') !== false)
					{
						$out .= '\Deleted ';
					}
					
					if (strpos($flags, 'f') !== false)
					{
						$out .= '\Flagged ';
					}

					if (strpos($flags, 'd') !== false)
					{
						$out .= '\Draft ';
					}

					if (strpos($flags, 'a') !== false)
					{
						$out .= '\Answered ';
					}
				}
			}
		}

		return trim($out);
	}
	
	/**
	 * @param string $name
	 * @return string
	 */
	function _getUidFromFileName($name)
	{
		$end = strrpos($name, $this->_uidSizeD);
		if ($end !== false)
		{
			return substr($name, 0, $end);
		}
		
		$end = strrpos($name, $this->_uidD);
		if ($end !== false)
		{
			return substr($name, 0, $end);
		}
		return $name;
	}	

	function _getXmailFlagsFromFileNameAsArray($name)
	{
		$return = array();
		$flagsArray = array('S', 'D', 'T', 'F', 'A');
		if (strrpos($name, $this->_uidD) !== false)
		{
			$arr = explode($this->_uidD, $name);
			if (is_array($arr) && count($arr) > 1)
			{
				$flags = strtoupper($arr[count($arr)-1]);
				for ($i = 0, $len = strlen($flags); $i < $len; $i++)
				{
					$flag = $flags{$i};
					if (in_array($flag, $flagsArray))
					{
						$return[] = $flag;
					}
				}
			}
		}
		return $return;
	}
	
	/**
	 * @param string $fileName
	 * @param Folder $folder
	 * @param int $flags
	 * @param int $action
	 */
	function setFlags($fileName, $folder, $flags, $action)
	{
		$path = $this->_changePath($folder->FullName);
		if (@is_dir($path) && @is_dir($path.'/cur/') && @file_exists($path.'/cur/'.$fileName))
		{
			$xmailFlags = $this->_getXmailFlagsFromFileNameAsArray($fileName);
			$fileUid = $this->_getUidFromFileName($fileName);
			$fileSize = filesize($path.'/cur/'.$fileName);
			
			if(($flags & MESSAGEFLAGS_Seen) == MESSAGEFLAGS_Seen)
			{
				if ($action == ACTION_Set)
				{
					$xmailFlags[] = 'S';
				}
				else
				{
					$this->_removeXmailFlag($xmailFlags, 'S');
				}
			}
			if(($flags & MESSAGEFLAGS_Flagged) == MESSAGEFLAGS_Flagged)
			{
				if ($action == ACTION_Set)
				{
					$xmailFlags[] = 'F';
				}
				else
				{
					$this->_removeXmailFlag($xmailFlags, 'F');
				}
			}
			if(($flags & MESSAGEFLAGS_Deleted) == MESSAGEFLAGS_Deleted)
			{
				if ($action == ACTION_Set)
				{
					$xmailFlags[] = 'T';
				}
				else
				{
					$this->_removeXmailFlag($xmailFlags, 'T');
				}
			}
			if(($flags & MESSAGEFLAGS_Draft) == MESSAGEFLAGS_Draft)
			{
				if ($action == ACTION_Set)
				{
					$xmailFlags[] = 'D';
				}
				else
				{
					$this->_removeXmailFlag($xmailFlags, 'D');
				}
			}
			if (($flags & MESSAGEFLAGS_Answered) == MESSAGEFLAGS_Answered)
			{
				if ($action == ACTION_Set)
				{
					$xmailFlags[] = 'A';
				}
				else
				{
					$this->_removeXmailFlag($xmailFlags, 'A');
				}
			}
			
			$xmailFlags = array_unique($xmailFlags);
			sort($xmailFlags);
			
			$_sep = ConvertUtils::IsWin() ? '_' : ':';
			$newFileName = $fileUid;
			$newFileName .= $this->_uidSizeD.$fileSize.$_sep.$this->_uidD.(implode('', $xmailFlags));
			if ($newFileName != $fileName)
			{
				if (!@rename($path.'/cur/'.$fileName, $path.'/cur/'.$newFileName))
				{
					$this->_log('XMAIL: Can\'t rename file '.$path.'/cur/'.$fileName.' -> '.$path.'/cur/'.$newFileName. ')', LOG_LEVEL_ERROR);
				}
			}
			
			return true;
		}
		else
		{
			$this->_log('XMAIL: Wrong filename '.$path.'/cur/'.$fileName, LOG_LEVEL_ERROR);
		}
		
		return false;
	}
	
	/**
	 * @param array $flags
	 * @param string $flag
	 */
	function _removeXmailFlag(&$flags, $flag)
	{
		foreach ($flags as $key => $value)
		{
			if ($value == $flag)
			{
				unset($flags[$key]);
			}
		}
	}
	
	/**
	 * @param string $fuid
	 * @param string $folderName
	 * @return string|false
	 */
	function getMessageHeader($fuid, $folderName)
	{
		$path = $this->_changePath($folderName);
		return $this->_getHeaderByPath($path.'/cur/'.$fuid);
	}
	
	/**
	 * @param string $fuid
	 * @param string $folderName
	 * @return string|false
	 */
	function getMessage($fuid, $folderName)
	{
		$path = $this->_changePath($folderName);
		if (@file_exists($path.'/cur/'.$fuid))
		{
			return @file_get_contents($path.'/cur/'.$fuid);
		}
		return false;
	}

	/**
	 * @param	string	$folderName
	 * @return  bool
	 */
	function touchInfoTab($folderName)
	{
		$filename = $this->_changePath($folderName).'/.info.tab';
		if (@file_exists($filename))
		{
			return @touch($this->_changePath($folderName).'/.info.tab');
		}
		return false;
	}

	/**
	 * @param	string	$folderName
	 * @param	int		$count
	 * @return int|false
	 */
	function getNextUid($folderName, $count = 1)
	{
		$_filename = $this->_changePath($folderName).'/.info.tab';
		if (@file_exists($_filename))
		{
			$_fo = @fopen($_filename, 'r+');
			if ($_fo)
			{
				$_next = false;
				$_contents = @fread($_fo, @filesize($_filename));
				$_pos = strpos($_contents, 'UIDNEXT');
				if ($_pos !== false)
				{
					$_pos = $_pos + 8;
					$_next = (int) trim(substr($_contents, $_pos, 9));
					if (fseek($_fo, $_pos) === 0)
					{
						$_new = (string)($_next + $count);
						$_new .= "\r\n";
						@fputs($_fo, $_new, strlen($_new));
					}
				}

				@fclose($_fo); 
				return $_next;
			}
		}

		return false;
	}
	
	function saveMessage($folderName, $fileName, $messageRawBody)
	{
		$path = $this->_changePath($folderName);
		if (!@file_exists($path.'/cur/'.$fileName))
		{
			return $this->_saveFileByFileName($path.'/cur/'.$fileName, $messageRawBody); 
		}
		return false;
	}
	
	/**
	 * @param string $folderName
	 * @return bool
	 */
	function purgeFolder($folderName)
	{
		$result = false;
		$path = $this->_changePath($folderName);
		$this->_log('try to purge folder: '.$path.'/cur/');
		if (is_dir($path) && is_dir($path.'/cur/'))
		{
			$dir = @dir($path.'/cur/');
			if ($dir)
			{
				$result = true;
				while (false !== ($entry = $dir->read()))
				{
					if($entry != '.' && $entry != '..' && is_file($path.'/cur/'.$entry))
					{	
						$flags = $this->_getFlagsFromFileName($entry);
						if (strpos($flags, 'Deleted') !== false)
						{
							$result &= @unlink($path.'/cur/'.$entry);
						}
					}
				}
			}
		}
		if (!$result)
		{
			$this->_log('error - purge folder', LOG_LEVEL_ERROR);
		}
		return $result;
	}
	
	/**
	 * @param string $folderName
	 * @return bool
	 */
	function clearFolder($folderName)
	{
		$result = false;
		$path = $this->_changePath($folderName);
		$this->_log('try to clear folder: '.$path.'/cur/');
		if (is_dir($path) && is_dir($path.'/cur/'))
		{
			$dir = @dir($path.'/cur/');
			if ($dir)
			{
				$result = true;
				while (false !== ($entry = $dir->read()))
				{
					if($entry != '.' && $entry != '..' && is_file($path.'/cur/'.$entry))
					{	
						$result &= @unlink($path.'/cur/'.$entry);
					}
				}
			}
		}

		$this->touchInfoTab($folderName);
		
		if (!$result)
		{
			$this->_log('error  - clear folder.', LOG_LEVEL_ERROR);
		}
		return $result;
	}

	/**
	 * @param string $fullName
	 * @return array
	 */
	function getAllAndUreadMsgCount($fullName)
	{
		$all = 0;
		$unread = 0;
		$path = $this->_changePath($fullName); 
		
		if (@is_dir($path) && @is_dir($path.'/cur/') && $dir = @dir($path.'/cur/'))
		{
			while (false !== ($entry = $dir->read()))
			{
				if($entry != '.' && $entry != '..' && is_file($entry))
				{	
					$all++;
					if (strpos('Seen', $this->_getFlagsFromFileName($entry)) === false)
					{
						$unread++;
					}
				}
			}
		}
		else
		{
			$this->_log('can\'t get xmail messages count '.$path, LOG_LEVEL_ERROR);
		}

		return array($all, $unread);
	}
	
	function _saveFileByFileName($fileName, $content)
	{
		$f = @fopen($fileName, 'wb');
		if ($f)
		{
			@fwrite($f, $content); 
			@fclose($f);
			return true;
		}
		
		return false;
	}
	
	/**
	 * @access private
	 * @param string $uid
	 * @return string/false
	 */
	function _getHeaderByPath($path)
	{
		$this->_log('get message header ('.$path.')');
		$handle = @fopen($path, 'r');
		if ($handle !== false)
		{
			$header = '';
			while (!@feof($handle))
			{
				$buffer = @fgets($handle, 4096);
				if($buffer == "\r\n" || $buffer === false)
				{
					break;
				}
				else
				{
					$header .= $buffer;
				}
			}
			@fclose($handle);
			return $header;
		}
		
		$this->_log('can\'t get message header '.$path, LOG_LEVEL_ERROR);
		return false;
	}
	
	/**
	 * @access private
	 * @param string $path
	 * @return int
	 */
	function _getAllMessagesCountByPath($path)
	{
		$count = 0;
		$dir = @dir($path);
		if ($dir)
		{
			while (false !== ($entry = $dir->read()))
			{
				if($entry != '.' && $entry != '..' && is_file($entry))
				{	
					$count++;
				}
			}
			return $count;
		}

		$this->_log('can\'t get xmail messages count '.$path, LOG_LEVEL_ERROR);
		return $count;
	}
	
	function _changePath($fullName)
	{
		$path = $this->path;
		if (strtoupper($fullName) != 'INBOX')
		{
			$path .= '.'.$fullName;
		}
		return rtrim($path, '/\\');
	}
	
	/**
	 * @param string $string
	 */
	function _log($string, $logLevel = LOG_LEVEL_DEBUG)
	{
		if ($this->_log->Enabled)
		{
			$this->_log->WriteLine('XMail: '.$string, $logLevel);
		}
	}
};
