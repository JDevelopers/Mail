<?php

/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	include 'core/top.php';
	include 'core/session.php';
	include 'cadminpanel.php';

	$AdminPanel = new CAdminPanel(__FILE__);
	$AdminPanel->Write();