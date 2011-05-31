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
			<span style="font-size: large;"><?php $this->data->PrintValue('TopText'); ?></span>
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
				<nobr id="intLimitMailbox_label">Mailbox Limit:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="intLimitMailbox" type="text" id="intLimitMailbox" class="wm_input" size="17" value="<?php $this->data->PrintIntValue('intLimitMailbox'); ?>" maxlength="7" />
				&nbsp;KB
			</td>
		</tr>
		<tr class="<?php $this->data->PrintInputValue('classTakeImapQuota'); ?>">
			<td>&nbsp;</td>
			<td align="left" colspan="2">
				<input name="intTakeImapQuota" type="checkbox" id="intTakeImapQuota" class="wm_checkbox" value="1" <?php $this->data->PrintCheckedValue('intTakeImapQuota'); ?> />
				<label for="intTakeImapQuota" id="intTakeImapQuota_label">
					Take quota value from IMAP server
				</label>
				<?php $this->data->PrintValue('infoTakeImapQuotaText'); ?>
				<?php $this->data->PrintValue('infoTakeImapQuotaJs'); ?>
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
		<tr>
			<td align="right">
				<nobr>* Email:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="txtEmail" type="text" id="txtEmail" class="wm_input" style="width: 350px" maxlength="100" value="<?php $this->data->PrintInputValue('txtEmail'); ?>" />
			</td>
		</tr>
		<tr>
			<td align="right">
				<nobr>SMTP login:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="txtSmtpLogin" type="text" id="txtSmtpLogin" class="wm_input" style="width: 350px" maxlength="100" value="<?php $this->data->PrintInputValue('txtSmtpLogin'); ?>"  />
			</td>
		</tr>
		<tr>
			<td align="right">
				<nobr>SMTP password:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="txtSmtpPassword" type="password" maxlength="100" id="txtSmtpPassword" class="wm_input" style="width: 350px;" value="<?php $this->data->PrintInputValue('txtSmtpPassword'); ?>"/>
			</td>
		</tr>
		<tr>
			<td align="left" colspan="3">
				<input name="chkUseFriendlyName" type="checkbox" id="chkUseFriendlyName" class="wm_checkbox" style="VERTICAL-ALIGN: middle" <?php $this->data->PrintCheckedValue('chkUseFriendlyName'); ?> value="1" />
				<label for="chkUseFriendlyName">
					Use friendly name in "From:" field (Your name &lt;sender@mail.com&gt;)
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
		<tr id="tr_4">
			<td align="left" colspan="3">
				<input name="chkAllowDM" type="checkbox" id="chkAllowDM" class="wm_checkbox" style="VERTICAL-ALIGN: middle" <?php $this->data->PrintCheckedValue('chkAllowDM'); ?> value="1" />
				<label for="chkAllowDM">
					Allow Direct Mode (<a href="http://www.afterlogic.com/kb/articles/synchronization-modes-how-do-they-work" target="_blank">What's this?</a>) 
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

	function InitImapQuota() {
		var ch = document.getElementById("intTakeImapQuota");
		var inp = document.getElementById("intLimitMailbox");
		if (ch && inp) {
			SetDisabled(inp, ch.checked, true);
			ch.onclick = function() {
				SetDisabled(inp, this.checked, true);
			}
		}
	}
	InitImapQuota();
</script>
<br />
<hr />
<div align="right">
	<span class="wm_secondary_info" style="float: left;">* required fields</span>
	<input type="submit" class="wm_button" style="width: 100px;" value="Save">
</div>