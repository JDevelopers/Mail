<input name="intDomainId" type="hidden" value="<?php $this->data->PrintInputValue('intDomainId'); ?>" />
<input name="intAccountId" type="hidden" value="<?php $this->data->PrintInputValue('intAccountId'); ?>" />
<input name="intMailProtocol" type="hidden" value="<?php echo WM_MAILPROTOCOL_IMAP4; ?>" />
<input name="uid" type="hidden" value="<?php $this->data->PrintInputValue('uid'); ?>" />

<script type="text/javascript">
	var useredittabs = new Array();
	useredittabs.push("content_custom_tab_1");
	useredittabs.push("content_custom_tab_2");

	function SetAllOf()
	{
		$('custom_tab_1').className = 'wm_settings_switcher_item';
		$('custom_tab_2').className = 'wm_settings_switcher_item';
	}
</script>

<table>
	<tr>
		<td align="left">
			<span style="font-size: large;">Edit User</span>
		</td>
	</tr>
</table>

<div class="wm_settings_accounts_info" style="width: 100%; margin: 15px 0px">
	<div class="wm_settings_switcher_indent"></div>
	<div id="custom_tab_2" class="wm_settings_switcher_item" onclick="SwitchItem(useredittabs, 'content_custom_tab_2'); SetAllOf(); this.className = 'wm_settings_switcher_select_item';">
		<a href="javascript:void(0)">Webmail Settings</a>
	</div>
	<div id="custom_tab_1" class="wm_settings_switcher_select_item" onclick="SwitchItem(useredittabs, 'content_custom_tab_1'); SetAllOf(); this.className = 'wm_settings_switcher_select_item';">
		<a href="javascript:void(0)">Basic Settings</a>
	</div>
</div>

<div id="content_custom_tab_1" style="padding-top: 10px;">
	<table class="wm_contacts_view_new">
		<tr>
			<td align="left" colspan="3">
				<input name="chkUserEnabled" type="checkbox" id="chkUserEnabled" class="wm_checkbox" style="VERTICAL-ALIGN: middle" <?php $this->data->PrintCheckedValue('chkUserEnabled'); ?> value="1" />
				<label for="chkUserEnabled">
					User Enabled
				</label>
			</td>
		</tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<tr>
			<td align="right">
				<nobr>* Login:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="txtIncomingLogin" type="text" id="txtIncomingLogin" class="wm_input" style="width: 350px" maxlength="100" value="<?php $this->data->PrintInputValue('txtIncomingLogin'); ?>" />
			</td>
		</tr>
		<tr>
			<td align="right">
				<nobr>* Password:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="txtIncomingPassword" type="password" maxlength="100" id="txtIncomingPassword" class="wm_input" style="width: 350px;" value="<?php $this->data->PrintInputValue('txtIncomingPassword'); ?>" />
			</td>
		</tr>
		<tr>
			<td align="right">
				<nobr>Mailbox Limit:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="intLimitMailbox" type="text" id="intLimitMailbox" class="wm_input" size="17" value="<?php $this->data->PrintIntValue('intLimitMailbox'); ?>" maxlength="7" />
				&nbsp;KB
			</td>
		</tr>
		<tr>
			<td align="right">
				<nobr>Name:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="txtFriendlyName" type="text" id="txtFriendlyName" class="wm_input" style="width: 350px" maxlength="100" value="<?php $this->data->PrintInputValue('txtFriendlyName'); ?>" />
			</td>
		</tr>
		<tr class="wm_hide">
			<td align="right">
				<nobr>SMTP login:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="txtSmtpLogin" type="text" id="txtSmtpLogin" class="wm_input" style="width: 350px" maxlength="100" value="<?php $this->data->PrintInputValue('txtSmtpLogin'); ?>"  />
			</td>
		</tr>
		<tr class="wm_hide">
			<td align="right">
				<nobr>SMTP password:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="txtSmtpPassword" type="password" maxlength="100" id="txtSmtpPassword" class="wm_input" style="width: 350px;" value="<?php $this->data->PrintInputValue('txtSmtpPassword'); ?>"/>
			</td>
		</tr>
		<tr class="wm_hide">
			<td align="left" colspan="3">
				<input name="chkUseSmtpAuth" type="checkbox" id="chkUseSmtpAuth" class="wm_checkbox" style="VERTICAL-ALIGN: middle" <?php $this->data->PrintCheckedValue('chkUseSmtpAuth'); ?> value="1" />
				<label for="chkUseSmtpAuth">
					Use SMTP authentication (You may leave SMTP login/password fields blank,
					if they're the same as POP3 login/password)
				</label>
			</td>
		</tr>
		<tr>
			<td align="left" colspan="3">
				<input name="chkUseFriendlyName" type="checkbox" id="chkUseFriendlyName" class="wm_checkbox" style="VERTICAL-ALIGN: middle" <?php $this->data->PrintCheckedValue('chkUseFriendlyName'); ?> value="1" />
				<label for="chkUseFriendlyName">
					Use Friendly Name in "From:" field (Your name &lt;sender@mail.com&gt;)
				</label>
			</td>
		</tr>
		<tr>
			<td align="left" colspan="3">
				<input name="chkGetMailAtLogin" type="checkbox" id="chkGetMailAtLogin" class="wm_checkbox" style="VERTICAL-ALIGN: middle" <?php $this->data->PrintCheckedValue('chkGetMailAtLogin'); ?> value="1"/>
				<label for="chkGetMailAtLogin">
					Get/Synchronize Mails at login
				</label>
			</td>
		</tr>
		<tr>
			<td align="left" colspan="3">
				<input name="chkAllowChangeEmail" type="checkbox" id="chkAllowChangeEmail" class="wm_checkbox" style="VERTICAL-ALIGN: middle" <?php $this->data->PrintCheckedValue('chkAllowChangeEmail'); ?> value="1" />
				<label for="chkAllowChangeEmail">
					Allow User to Change Account Settings
				</label>
			</td>
		</tr>
	</table>
</div>
<div id="content_custom_tab_2" style="padding-top: 10px;">
<table class="wm_contacts_view_new">
<?php
	$path = $this->data->GetIncludeConrolPath('edit-user-advanced-control');
	if (false !== $path)
	{
		include $path;
	}
?>
</table>
</div>
<script type="text/javascript">
<?php $this->data->PrintValue('StartWmJs'); ?>
	Validator.RegisterAllowNum($('intLimitMailbox'));
</script>
<br />
<hr />
<div align="right">
	<span class="wm_secondary_info" style="float: left;">* required fields</span>
	<input type="submit" class="wm_button" style="width: 100px;" value="Save">
</div>