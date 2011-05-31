<input name="intDomainId" type="hidden" value="0" />

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
			<span style="font-size: large;">Create User</span>
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
				<nobr>* Incoming mail:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="txtIncomingMail" type="text" id="txtIncomingMail" class="wm_input" size="17" value="<?php $this->data->PrintInputValue('txtIncomingMail'); ?>" />
				&nbsp;&nbsp;
				<nobr>* Port:</nobr>
				<input name="intIncomingPort" type="text" id="intIncomingPort" class="wm_input" size="4" value="<?php $this->data->PrintIntValue('intIncomingPort'); ?>" maxlength="5"/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<select name="intMailProtocol" id="selectMailProtocol" class="wm_input">
					<option value="<?php echo WM_MAILPROTOCOL_POP3; ?>" <?php $this->data->PrintSelectedValue('selectMailProtocolPop3'); ?> >POP3&nbsp;</option>
					<option value="<?php echo WM_MAILPROTOCOL_IMAP4; ?>" <?php $this->data->PrintSelectedValue('selectMailProtocolImap4'); ?> >IMAP4&nbsp;</option>
				</select>
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
		<tr id="tr_iq" class="<?php $this->data->PrintInputValue('classTakeImapQuota'); ?>">
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
				<nobr>* Outgoing mail:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="txtSmtpServer" type="text" id="txtSmtpServer" class="wm_input" size="17" value="<?php $this->data->PrintInputValue('txtSmtpServer'); ?>" />
				&nbsp;&nbsp;
				<nobr>* Port:</nobr>
				<input name="intSmtpPort" type="text" id="intSmtpPort" class="wm_input" size="4" value="<?php $this->data->PrintIntValue('intSmtpPort'); ?>" maxlength="5"/>
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
		<tr id="tr_0">
			<td align="left" colspan="3">
				<input value="1" name="mailMode" type="radio" id="radioDelRecvMsgs" style="VERTICAL-ALIGN: middle" <?php $this->data->PrintCheckedValue('radioDelRecvMsgs'); ?> />
				<label for="radioDelRecvMsgs">
					Delete received messages from server
				</label>
				<br />
				<input value="2" name="mailMode" type="radio" id="radioLeaveMsgs" style="VERTICAL-ALIGN: middle" <?php $this->data->PrintCheckedValue('radioLeaveMsgs'); ?> />
				<label for="radioLeaveMsgs">
					Leave messages on server
				</label>
			</td>
		</tr>
		<tr id="tr_1">
			<td align="left" colspan="3">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input name="chkKeepMsgs" type="checkbox" id="chkKeepMsgs" class="wm_checkbox" style="VERTICAL-ALIGN: middle" <?php $this->data->PrintCheckedValue('chkKeepMsgs'); ?> value="1" />
				<label for="chkKeepMsgs">
					Keep messages on server for
				</label>
				<input name="txtKeepMsgsDays" type="text" id="txtKeepMsgsDays" class="wm_input" size="2" value="<?php $this->data->PrintIntValue('txtKeepMsgsDays'); ?>" maxlength="2" />
				day(s)
				<br />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input name="chkDelMsgsSrv" type="checkbox" id="chkDelMsgsSrv" class="wm_checkbox" style="VERTICAL-ALIGN: middle" <?php $this->data->PrintCheckedValue('chkDelMsgsSrv'); ?> value="1"/>
				<label for="chkDelMsgsSrv">
					Delete message from server when it is removed from Trash
				</label>
			</td>
		</tr>
		<tr id="tr_2">
			<td align="left" colspan="3">
				Type of Inbox Synchronization:
				<select name="synchronizeSelect" id="synchronizeSelect" class="wm_input">
					<option value="1" <?php $this->data->PrintSelectedValue('synchronizeSelect1'); ?> >Headers Only</option>
					<option value="3" <?php $this->data->PrintSelectedValue('synchronizeSelect3'); ?> >Entire Messages</option>
					<option value="5" <?php $this->data->PrintSelectedValue('synchronizeSelect5'); ?> >Direct Mode</option>
				</select>
			</td>
		</tr>
		<tr id="tr_3">
			<td align="left" colspan="3">
				<input name="chkDelMsgsDB" type="checkbox" id="chkDelMsgsDB" class="wm_checkbox" style="VERTICAL-ALIGN: middle" <?php $this->data->PrintCheckedValue('chkDelMsgsDB'); ?> value="1" />
				<label for="chkDelMsgsDB">
					Delete message from database if it no longer exists on mail server
				</label>
			</td>
		</tr>
		<tr>
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

	Validator.RegisterAllowNum($('intIncomingPort'));
	Validator.RegisterAllowNum($('intSmtpPort'));
	Validator.RegisterAllowNum($('intLimitMailbox'));
	Validator.RegisterAllowNum($('txtKeepMsgsDays'));
	
var Pop3Form = {
	_radioDelete: document.getElementById("radioDelRecvMsgs"),
	_radioLeave: document.getElementById("radioLeaveMsgs"),
	
	_chKeep: document.getElementById("chkKeepMsgs"),
	_inpKeep: document.getElementById("txtKeepMsgsDays"),
	_chDeleteFromTrach: document.getElementById("chkDelMsgsSrv"),
	_selectSync: document.getElementById("synchronizeSelect"),
	_chDeleteFromDb: document.getElementById("chkDelMsgsDB"),
	
	Init: function () {
	
		var obj = this;
		if (this._radioDelete && this._radioLeave && this._chKeep && this._inpKeep
				&& this._chDeleteFromTrach && this._selectSync && this._chDeleteFromDb)
		{
			this._radioDelete.onchange = function() { obj.Set(); };
			this._radioLeave.onchange = function() { obj.Set(); };
			this._chKeep.onchange = function() { obj.Set(); };
			this._chDeleteFromTrach.onchange = function() { obj.Set(); };
			this._selectSync.onchange = function() { obj.Set(); };
			this._chDeleteFromDb.onchange = function() { obj.Set(); };
			
			this.Set();
		}
	},
	
	Set: function () {
		
		switch (this._selectSync.value) {
			case "1":
				this._radioLeave.checked = true;
				SetDisabled(this._radioDelete, true);
				SetDisabled(this._radioLeave, false);
				SetDisabled(this._chKeep, false);
				SetDisabled(this._chDeleteFromTrach, false);
				SetDisabled(this._chDeleteFromDb, false);
				break;
			case "3":
				SetDisabled(this._radioDelete, false);
				SetDisabled(this._radioLeave, false);
				SetDisabled(this._chDeleteFromDb, false);
				SetDisabled(this._chKeep, (this._radioLeave.disabled || !this._radioLeave.checked));
				SetDisabled(this._chDeleteFromTrach, (this._radioLeave.disabled || !this._radioLeave.checked));
				break;
			case "5":
				this._radioLeave.checked = true;
				SetDisabled(this._radioDelete, true);
				SetDisabled(this._radioLeave, true);
				SetDisabled(this._chKeep, true);
				SetDisabled(this._chDeleteFromTrach, true);
				SetDisabled(this._chDeleteFromDb, true);
				break;
		}

		SetDisabled(this._inpKeep, (this._chKeep.disabled || !this._chKeep.checked));
	}
};

Pop3Form.Init();
var pSelect = $("selectMailProtocol");
var incPort = $("intIncomingPort");
if(pSelect && incPort) {
	pSelect.onchange = function() { 
		incPort.value = (pSelect.value == '<?php echo WM_MAILPROTOCOL_POP3; ?>') ? 110 : 143;
		InitPop3ImapForm(pSelect); 
	};
};

function InitPop3ImapForm(selectObj) {
	var obj;
	var tr_iq = document.getElementById("tr_iq");
	for (var i = 0; i < 5; i++) {
 		obj = document.getElementById("tr_" + i);
 		if (obj) {
 			obj.className = (selectObj.value != '<?php echo WM_MAILPROTOCOL_POP3; ?>') ? "wm_hide" : "";
 		}
	}
	if (window.TakeImapQuota)
	{
		if (selectObj.value == '<?php echo WM_MAILPROTOCOL_POP3; ?>') {
			var ch = document.getElementById("intTakeImapQuota");
			if (ch && ch.checked){
				ch.click();
			}
			tr_iq.className = "wm_hide";
		} else {
			tr_iq.className = "";
		}

	}
};

InitPop3ImapForm(pSelect);


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