<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */


	class Attachment
	{
		/**
		 * @var string $Filename
		 */
		var $Filename;
		
		/**
		 * @var MimePart
		 */
		var $MimePart;
		
		/**
		 * @var bool
		 */
		var $IsInline = false; 
		
		/**
		 * Gets the Content-ID value of the attachment.
		 * @return string
		 */
		function GetContentID()
		{
			return $this->MimePart->GetContentID();
		}
		
		function GetBinaryBody()
		{
			return $this->MimePart->GetBinaryBody();
		}
		
		/**
		 * Gets the content location of the attachment.
		 * @return string
		 */
		function GetContentLocation()
		{
			return $this->MimePart->GetContentLocation();
		}
		
		/**
		 * Gets the content type of the attachment.
		 * @return string
		 */
		function GetContentType()
		{
			return $this->MimePart->GetContentType();
		}
		
		/**
		 * Gets the description of the attachment as a string.
		 * @return string
		 */
		function GetDescription()
		{
			return $this->MimePart->GetDescription();
		}
		
		/**
		 * Gets the filename of the attachment.
		 * @return string
		 */
		function GetFilenameFromMime()
		{
			$filename = $this->MimePart->GetFilename();
			$result = '';
			if ($filename === '')
			{
				$contentName = $this->MimePart->GetContentTypeName();
				if ($contentName)
				{
					$result = $contentName;
				}
				else 
				{
					$contentType = strtolower($this->GetContentType());
					$contentTypeArray = explode(';', $contentType);
					$contentType = isset($contentTypeArray[0]) ? $contentTypeArray[0] : $contentType;
					
					$result = 'attachment.dat';
					if (strpos($contentType, 'image') !== false)
					{
						$result = str_replace(array('/', '\\'), '.', $contentType);
					}
					else if (strpos($contentType, 'message') !== false)
					{
						$result = 'message.eml';
					}
				}
			} 
			else 
			{
				$result = $filename;
			}
			return $result;
		}
		
		function GetFilename()
		{
			return $this->Filename;
		}
		
		/**
		 * Gets the collection of the attachment headers.
		 * @return HeaderCollection
		 */
		function &GetHeaders()
		{
			return $this->MimePart->GetHeaders();
		}
		
		/**
		 * @param MimePart $mimePart
		 * @return Attachment
		 */
		function Attachment(&$mimePart, $isInline = false)
		{
			$this->MimePart =& $mimePart;
			$this->Filename = $this->GetFilenameFromMime();
			$this->IsInline = $isInline;
		}
		
		/**
		 * @param string $filename
		 * @return bool
		 */
		function SaveToFile($filename)
		{
			$body = null;
			$returnBool = true;
			$fh = @fopen($filename, 'wb');
			if ($fh)
			{
				$body = $this->GetBinaryBody();
				if (!@fwrite($fh, $body))
				{
					setGlobalError('can\'t write file: '.$filename);
					$returnBool = false;
				}
				@fclose($fh);
			}
			else 
			{
				setGlobalError('can\'t open file(wb): '.$filename);
				$returnBool = false;
			}
			
			if ($returnBool && null !== $body)
			{
				return strlen($body);
			}
			
			return -1;
		}
		
		/**
		 * @return string
		 */
		function GetTempName()
		{
			$name = $this->GetFilename();
			$exe = array_pop(explode('.', $name));
			$exe = ($exe && $exe != $name) ? $exe : 'tmp';
			if (strlen($exe) > 7) 
			{
				$exe = substr($exe, 0, 7);
			}
			return md5($name).'.'.$exe;
		}
		
	}