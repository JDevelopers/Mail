<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

	if (!class_exists('ap_PluginInfo'))
	{
		exit('ap_PluginInfo not found!');
	}

	$pl_info = new ap_PluginInfo();
	$pl_info->Name = 'WebMail Plugin';
	$pl_info->Index = 'wm';
	$pl_info->UpdateIndex = 'wm';
	$pl_info->UpdateLevel = 2;
	$pl_info->ClassName = 'CWebMail_Plugin';
	$pl_info->Path = rtrim(dirname(__FILE__), '/\\');
	$pl_info->Version = '0.0.5';
	$pl_info->HasInstall = true;
	$pl_info->Tabs = array(
		array('WebMail', 'WebMail Settings', 'wm'),
		array('Domains', 'WebMail Domains', 'domains'),
		array('Users', 'WebMail Users', 'users')
	);
	$pl_info->HTabs = array('users');
	$pl_info->InitBySelf();
