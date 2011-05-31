<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/'));
	
	header('Content-type: text/xml; charset=utf-8');

	require_once(WM_ROOTPATH.'common/inc_top.php');
	require WM_ROOTPATH.'common/class_session.php';

	function _disable_magic_quotes_gpc()
	{
		if (@get_magic_quotes_gpc() == 1)
		{
			$_GET = array_map('stripslashes', $_GET);
			$_POST = array_map('stripslashes', $_POST);
		}
	}

	@_disable_magic_quotes_gpc();

	require_once(WM_ROOTPATH.'common/class_xmldocument.php');
	require_once(WM_ROOTPATH.'common/class_account.php');
	require_once(WM_ROOTPATH.'common/class_webmailmessages.php');
	require_once(WM_ROOTPATH.'common/class_mailprocessor.php');
	require_once(WM_ROOTPATH.'common/class_filters.php');
	require_once(WM_ROOTPATH.'common/class_contacts.php');
	require_once(WM_ROOTPATH.'common/class_filesystem.php');
	require_once(WM_ROOTPATH.'common/class_i18nstring.php');
	require_once(WM_ROOTPATH.'common/class_convertutils.php');
	require_once(WM_ROOTPATH.'common/inc_constants.php');
	require_once(WM_ROOTPATH.'common/class_smtp.php');
	require_once(WM_ROOTPATH.'common/class_validate.php');
	require_once(WM_ROOTPATH.'common/class_xmlprocessing.php');
	require_once(WM_ROOTPATH.'common/class_processorswitch.php');
	require_once(WM_ROOTPATH.'common/class_tempfiles.php');
	require_once(WM_ROOTPATH.'common/class_contactstorage.php');
	require_once(WM_ROOTPATH.'common/class_actionfilters.php');
	

	$_zz = 0;
	$_null = null;
	$_startTime = _localMicrotime();
	
	@ob_start('Qitsra');
	
	$_xmlResponse = new XmlDocument();
	$_xmlResponse->CreateElement('webmail');

	$_xml = isset($_POST['xml']) ? $_POST['xml'] : '';

	$_xmlRequest = new XmlDocument();
	$_xmlRequest->ParseFromString($_xml);

	$_log =& CLog::CreateInstance();
	if (!USE_FULLPARSE_XML_LOG)
	{
		$_log->WriteLine('<<<[client_xml]<<<'."\r\n".$_xml);
	}
	else if ($_log->Enabled)
	{
		$_log->WriteLine('<<<[client_xml]<<<'."\r\n".$_xmlRequest->ToString(true));
	}
	
	$_settings =& Settings::CreateInstance();
	if (!$_settings || !$_settings->isLoad)
	{
		_localPrintErrorAndExit('', $_xmlResponse, 3);
	}
	
	$_action = $_xmlRequest->GetParamValueByName('action');
	$_request = $_xmlRequest->GetParamValueByName('request');

	$BackgroundXmlParam = (int) $_xmlRequest->GetParamValueByName('background');
	
	
	
	if (!isset($_SESSION[ACCOUNT_ID]) &&
			$_action != 'login' && $_action != 'registration' && $_action != 'resetpassword')
	{
		$_xmlResponse->XmlRoot->AppendChild(new XmlDomNode('session_error'));
		_localPrintXML($_xmlResponse, $_startTime);
	}

	$_accountId = isset($_SESSION[ACCOUNT_ID]) ? $_SESSION[ACCOUNT_ID] : null;

	
	
	if (!$_settings->IncludeLang())
	{
		_localPrintErrorAndExit('', $_xmlResponse, 6);
	}
	
	
		
	$_dbStorage =& DbStorageCreator::CreateDatabaseStorage($_null);
	if (!$_dbStorage || !$_dbStorage->Connect())
	{
		_localPrintErrorAndExit(getGlobalError(), $_xmlResponse);
	}
	
	
	
	$_args = array('_dbStorage' => &$_dbStorage, '_settings' => &$_settings, '_xmlRequest' => &$_xmlRequest, '_xmlResponse' => &$_xmlResponse, '_accountId' => $_accountId);
	if (CProcessingSwitch::UseMethod($_action, $_request, $_args))
	{
		_localPrintXML($_xmlResponse, $_startTime);
	}
	else
	{
		_localPrintErrorAndExit(WebMailException, $_xmlResponse);
	}
	
	function _localPrintErrorAndExit($_errorString, &$_xmlObj, $_code = null)
	{
		CXmlProcessing::PrintErrorAndExit($_errorString, $_xmlObj, $_code);
	}
	
	
	
	function _localPrintXML(&$_xmlRes, $_startTime)
	{
		CXmlProcessing::PrintXML($_xmlRes, $_startTime);
	}
	
	

	function NumOLCallBackFunction(&$_settings, &$_dbStorage, &$errorString)
	{
		$_bResult = true;
		
		return $_bResult;
	}

	function GetBaseProcessingCallBackFunction(&$_settings, &$_xmlRes)
	{
	}
	
	function _localMicrotime()
	{
		return getmicrotime();
	}
	
	/**
	 * @param string $_string
	 * @return string
	 */
	function Qitsra($_string)
	{
		$_log =& CLog::CreateInstance();
		if ($_log->Enabled && strlen(trim($_string)) > 0)
		{
			if (!USE_FULLPARSE_XML_LOG)
			{
				$_log->WriteLine('>>>[server_xml]>>>'."\r\n".$_string);
			}
			else
			{
				$_xmlTemp = new XmlDocument();
				$_xmlTemp->ParseFromString($_string);
				$_log->WriteLine('>>>[server_xml]>>>'."\r\n".$_xmlTemp->ToString(true));
			}
		}

		if (USE_PROCESSING_GZIP)
		{
			$_string = obStartGzip($_string);
		}
		
		return $_string;
	}

