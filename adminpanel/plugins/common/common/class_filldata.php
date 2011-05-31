<?php

class CmMainFillClass
{
	/**
	 * @param ap_Screen_Data $data
	 * @param CCommonSubAdmin $_subadmin
	 */
	function ScreenDataFromSubAdmin(&$_data, $_subadmin, $_domainsArray, $_isNew = false)
	{
		$selectH = 6;

		$_data->SetValue('intAdminId', $_subadmin->Id);
		$_data->SetValue('txtLogin', $_subadmin->Login);
		if (!$_isNew)
		{
			$_data->SetValue('txtPassword', strlen($_subadmin->Password) > 0 ? AP_DUMMYPASSWORD : '');
		}
		$_data->SetValue('txtDescription', $_subadmin->Description);

		$_data->SetValue('selDomainsSize', $selectH);

		$domainOptions = '';
		if (is_array($_domainsArray) && count($_domainsArray) > 0)
		{
			if (count($_domainsArray) < $selectH)
			{
				$_data->SetValue('selDomainsSize', count($_domainsArray));
			}
			foreach ($_domainsArray as $domainId => $domainName)
			{
				$addString = in_array($domainId, $_subadmin->DomainIds) ? ' selected="selected"' : '';
				$domainOptions .= '<option value="'.$domainId.'"'.$addString.'>'.$domainName.'</option>';
			}
		}

		$_data->SetValue('selDomains', $domainOptions);
	}

	/**
	 * @param CCommonSubAdmin $_subadmin
	 */
	function SubAdminFromPost(&$_subadmin)
	{
		$_subadmin->Id = isset($_POST['intAdminId']) ? (int) $_POST['intAdminId'] : $_subadmin->Id;
		$_subadmin->Login = isset($_POST['txtLogin']) ? $_POST['txtLogin'] : $_subadmin->Login;
		$_subadmin->Password = (isset($_POST['txtPassword']) && $_POST['txtPassword'] !== AP_DUMMYPASSWORD)
				? $_POST['txtPassword'] : $_subadmin->Password;
		$_subadmin->Description = isset($_POST['txtDescription']) ? $_POST['txtDescription'] : $_subadmin->Description;
		$_subadmin->DomainIds = isset($_POST['selDomains']) ? (array) $_POST['selDomains'] : $_subadmin->DomainIds;
	}
}
