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
	$pl_info->Name = 'Security Plugin';
	$pl_info->Index = 'cm';
	$pl_info->UpdateIndex = 'cm';
	$pl_info->UpdateLevel = 2;
	$pl_info->ClassName = 'CCommon_Plugin';
	$pl_info->Path = rtrim(dirname(__FILE__), '/\\');
	$pl_info->Version = '0.0.5';
	$pl_info->Tabs = array(
		array('Security', 'Security Settings', 'common'),
		array('Subadmins', 'Subadmins Settings', 'admins'),
		array(array('Upgrade to Pro', 'Licensing'), 'Main Settings', 'main', true)
	);
	$pl_info->HTabs = array('admins');
	$pl_info->InitBySelf();