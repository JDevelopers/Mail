<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));

	require_once(WM_ROOTPATH.'mime/inc_constants.php');
	require_once(WM_ROOTPATH.'mime/class_attachment.php');


	class AttachmentCollection extends CollectionBase
	{
		/**
		 * @access private
		 * @var string
		 */
		var $_htmlText = '';
		
		/**
		 * @param MailMessage $mailMessage
		 * @return AttachmentCollection
		 */
		function AttachmentCollection(&$mailMessage)
		{
			CollectionBase::CollectionBase();
			
			if ($mailMessage != null) 
			{
				$this->_htmlText =& $mailMessage->TextBodies->HtmlTextBodyPart;
				$this->SearchAttachParts($mailMessage);
			}
		}
		
		/**
		 * @param MimePart $mimePart
		 */
		function AddToCollection(&$_mimePart)
		{
			$_isInline = false;

			if ($this->_htmlText)
			{
				$cId = trim($_mimePart->GetContentID());
				$cLocation = trim($_mimePart->GetContentLocation());
				if (!empty($cId))
				{
					$_isInline = (false !== strpos($this->_htmlText, $cId));
				}

				if (!empty($cLocation))
				{
					$_isInline = (false !== strpos($this->_htmlText, $cLocation));
				}
			}
			
			$this->List->Add(new Attachment($_mimePart, $_isInline));
		}
		
		/**
		 * @param int $index
		 * @return Attachment
		 */
		function &Get($index)
		{
			return $this->List->Get($index);
		}
		
		/**
		 * @return Attachment
		 */
		function &GetLast()
		{
			return $this->List->Get($this->List->Count() - 1);
		}

		/**
		 * @return bool
		 */
		function DeleteLast()
		{
			return $this->List->RemoveAt($this->List->Count() - 1);
		}
		
		/**
		 * @param MimePart $mimePart
		 */
		function SearchAttachParts(&$mimePart)
		{
			if ($mimePart->_subParts == null)
			{
				if ($mimePart->IsMimePartAttachment())
				{
					$this->AddToCollection($mimePart);
				}
			}
			else
			{
				for ($i = 0, $c = $mimePart->_subParts->List->Count(); $i < $c; $i++)
				{
					$subPart =& $mimePart->_subParts->List->Get($i);
					$this->SearchAttachParts($subPart);
					unset($subPart);
				}
			}
		}
		
		/**
		 * @return bool
		 */
		function AddFromFile($filepath, $attachname, $mimetype, $isInline = false)
		{
			$data = '';
			$handle = @fopen($filepath, 'rb');
			if ($handle)
			{
				$size = @filesize($filepath);
				$data = ($size > 0) ? @fread($handle, $size) : '';
				@fclose($handle);
			}
			else 
			{
				setGlobalError(' can\'t open '.$filepath);
				return false;
			}
		
			if ($this->AddFromBinaryBody($data, $attachname, $mimetype, $isInline))
			{
				return true;
			}
			return false;
			
		}
		
		function AddFromBinaryBody($bbody, $attachname, $mimetype, $isInline)
		{
			if (false !== $bbody && null !== $bbody)
			{
				$AttachType = ($isInline) ? MIMEConst_InlineLower : MIMEConst_AttachmentLower;
				
				$attachname = ConvertUtils::EncodeHeaderString($attachname, $GLOBALS[MailInputCharset], 'utf-8');
				
				$mimePart = new MimePart();
				$mimePart->Headers->SetHeaderByName(MIMEConst_ContentType, $mimetype.';'.CRLF."\t".MIMEConst_NameLower.'="'.$attachname.'"', false);
				$mimePart->Headers->SetHeaderByName(MIMEConst_ContentTransferEncoding, MIMEConst_Base64Lower, false);			
				$mimePart->Headers->SetHeaderByName(MIMEConst_ContentDisposition, $AttachType.';'.CRLF."\t".MIMEConst_FilenameLower.'="'.$attachname.'"', false);

				$mimePart->_body = ConvertUtils::base64WithLinebreak($bbody);
				
				$this->List->Add(new Attachment($mimePart, $isInline));
				return true;
			}
			return false;
		}

		function AddFromBodyStructure($attachname, $idx, $size, $encode, $contentId = null)
		{
			$attachname = ConvertUtils::EncodeHeaderString($attachname, $GLOBALS[MailInputCharset], 'utf-8');
			$mimetype = ConvertUtils::GetContentTypeFromFileName($attachname);
			
			$mimePart = new MimePart();
			$mimePart->BodyStructureIndex = $idx;
			$mimePart->BodyStructureSize = $size;
			$mimePart->BodyStructureEncode = $encode;
			$mimePart->Headers->SetHeaderByName(MIMEConst_ContentType, $mimetype.';'.CRLF."\t".MIMEConst_NameLower.'="'.$attachname.'"', false);
			$mimePart->Headers->SetHeaderByName(MIMEConst_ContentTransferEncoding, MIMEConst_Base64Lower, false);
			$mimePart->Headers->SetHeaderByName(MIMEConst_ContentDisposition, MIMEConst_AttachmentLower.';'.CRLF."\t".MIMEConst_FilenameLower.'="'.$attachname.'"', false);
			if (null !== $contentId)
			{
				$mimePart->Headers->SetHeaderByName(MIMEConst_ContentID, '<'.$contentId.'>', false);
			}

			$this->List->Add(new Attachment($mimePart, null !== $contentId));
			
			return true;
		}
	}