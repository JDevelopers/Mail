<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

class Validate 
{
	/**
	 * @param String $strEmail
	 * @return bool
	 */
	function checkEmail($strEmail)
	{
		$pattern = '/[A-Z0-9\!#\$%\^\{\}`~&\'\+-=_\.]+@[A-Z0-9\.-]/i';  
		$strEmail = ConvertUtils::SubStr(trim($strEmail), 0, 255);
		return (preg_match($pattern, $strEmail));
	}
	
	/**
	 * @param string $strLogin
	 * @return bool
	 */
	function checkLogin($strLogin)
	{
		$strLogin = ConvertUtils::SubStr(trim($strLogin), 0, 255);
		return (!Validate::HasSpecSymbols($strLogin));
	}
	
	/**
	 * @param int $port
	 * @return bool
	 */
	function checkPort($port)
	{
		$port = intval($port);
		return ($port > 0 && $port < 65535);
	}
	
	/**
	 * @param String $strServerName
	 * @return bool
	 */
	function checkServerName($strServerName)
	{
		$pattern = '/[^A-Z0-9\.\-\:\/]/i';
		$strServerName = ConvertUtils::SubStr(trim($strServerName), 0, 255);
		return (!preg_match($pattern, $strServerName));
	}
		
	/**
	 * @param String $strValue
	 * @return bool
	 */
	function HasSpecSymbols($_srt)
    {
        return preg_match('/["\/\\\*\?<>\|:]/', $_srt);
    }
	
    /**
     * @param String $strWeb
     * @return String
     */
    function cleanWebPage($strWebPage)
    {
    	$pattern = '/^[\/;<=>\[\\#\?]+/';
    	$strWebPage = ConvertUtils::SubStr(trim($strWebPage), 0, 255);
    	return preg_replace($pattern, '', $strWebPage);
    }
}
