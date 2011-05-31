<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="webmail_form" <?php $this->data->PrintValue('hideClass_webmail') ?> onsubmit="OnlineLoadInfo(_langSaving); return true;">
<input type="hidden" name="form_id" value="webmail" />
<input type="hidden" name="filter_host" value="<?php $this->data->PrintInputValue('txtFilterHost') ?>" />

<table class="wm_settings_common" width="550">
	<tr>
		<td width="150"></td>
		<td width="160"></td>
		<td></td>
	</tr>
	<tr class="<?php $this->data->PrintInputValue('txtDefaultClassUrl') ?>">
		<td colspan="3" style="padding: 0px;">
			<div class="wm_safety_info">
These settings apply to user accounts with no domain specified (out of domain). Please note this does not apply to existing accounts.
			</div>
		</td>
	</tr>
	<tr class="<?php $this->data->PrintInputValue('txtDomainClassUrl') ?>">
		<td colspan="3" style="padding: 0px;">
			<div class="wm_safety_info">
If you have multiple domains, web domain name lets WebMail Pro pick the appropriate mail domain depending on which domain the user enters in the browser.
<br />
<br />
Example:
<br />
- you have mail domains maildomain-a and maildomain-b;
<br />
- you configured two aliases webmail.domain-a and webmail.domain-b for your WebMail Pro installation;
<br />
- now you should specify webmail.domain-a as web domain for maildomain-a and webmail.domain-b for maildomain-b;
<br />
<br />
Result:
<br />
- if the user navigates to http://webmail.domain-a, WebMail Pro applies "Common Settings", "Interface Settings" and "Login Page Settings" of maildomain-a;
<br />
- if another user navigates to http://webmail.domain-b, maildomain-b settings are in use;
<br />
- if another user navigates to http://unknown-webmail-url (i.e. the domain name does not match any web domain name in Domains of WebMail Pro), WebMail Pro applies the Default domain settings;
			</div>
		</td>
	</tr>
	<tr><td colspan="3"><br /></td></tr>
	<tr>
		<td colspan="3" class="wm_settings_list_select"><b>Common Settings</b></td>
	</tr>
	<tr><td colspan="3"><br /></td></tr>
	<tr class="<?php $this->data->PrintInputValue('txtDomainClassUrl') ?>">
		<td align="right">
			Web domain name:
		</td>
		<td colspan="2">
			<input type="text" name="txtUrl" value="<?php $this->data->PrintInputValue('txtUrl') ?>" class="wm_input wm_addmargin" maxlength="50" />
		</td>
	</tr>
	<tr>
		<td align="right">
			Site name:
		</td>
		<td colspan="2">
			<input type="text" name="txtSiteName" value="<?php $this->data->PrintInputValue('txtSiteName') ?>" size="50" class="wm_input wm_addmargin" maxlength="100" />
		</td>
	</tr>
	<tr><td colspan="3"><br /></td></tr>
	<tr>
		<td colspan="3" class="wm_settings_list_select"><b>Default Mail Server Settings</b></td>
	</tr>
	<tr><td colspan="3"><br /></td></tr>
	<tr>
		<td align="right">
			Incoming mail:
		</td>
		<td>
			<input type="text" class="wm_input wm_addmargin" name="txtIncomingMail"  id="txtIncomingMail" value="<?php $this->data->PrintInputValue('txtIncomingMail') ?>" maxlength="100" />
		</td>
		<td>
			<nobr>
				Port:&nbsp;<input type="text" class="wm_input" name="intIncomingMailPort" id="intIncomingMailPort" size="3" value="<?php $this->data->PrintInputValue('intIncomingMailPort') ?>" maxlength="5" />
				&nbsp;
				<select name="intIncomingMailProtocol" id="intIncomingMailProtocol" class="wm_input">
					<option value="<?php $this->data->PrintInputValue('intIncomingMailProtocolPop3Value') ?>" <?php $this->data->PrintSelectedValue('intIncomingMailProtocol0') ?>>POP3</option>
					<option value="<?php $this->data->PrintInputValue('intIncomingMailProtocolImap4Value') ?>" <?php $this->data->PrintSelectedValue('intIncomingMailProtocol1') ?>>IMAP4</option>
				</select>
			</nobr>
		</td>
	</tr>
	<tr>
		<td align="right">Outgoing mail: </td>
		<td>
			<input type="text" class="wm_input wm_addmargin" name="txtOutgoingMail" value="<?php $this->data->PrintInputValue('txtOutgoingMail') ?>" maxlength="100" />
		</td>
		<td>
			Port:&nbsp;<input type="text" class="wm_input" name="intOutgoingMailPort" size="3" value="<?php $this->data->PrintIntValue('intOutgoingMailPort') ?>" maxlength="5" />
		</td>
	</tr>
	<tr>
		<td align="right">&nbsp;</td>
		<td colspan="2">
			<input type="checkbox" class="wm_checkbox" name="intReqSmtpAuthentication" id="intReqSmtpAuthentication" value="1" <?php $this->data->PrintCheckedValue('intReqSmtpAuthentication') ?>/>
			&nbsp;<label for="intReqSmtpAuthentication">Requires SMTP authentication</label>
		</td>
	</tr>
	<tr>
		<td align="right" >&nbsp;</td>
		<td colspan="2">
			<input type="checkbox" class="wm_checkbox" name="intAllowDirectMode" id="intAllowDirectMode" value="1" <?php $this->data->PrintCheckedValue('intAllowDirectMode') ?>/>
			&nbsp;<label for="intAllowDirectMode">Allow direct mode (<a href="http://www.afterlogic.com/kb/articles/synchronization-modes-how-do-they-work" target="_blank">What's this?</a>)</label>
		</td>
	</tr>
	<tr>
		<td align="right">&nbsp;</td>
		<td colspan="2">
			<input type="checkbox" class="wm_checkbox" name="intDirectModeIsDefault" id="intDirectModeIsDefault" value="1" <?php $this->data->PrintCheckedValue('intDirectModeIsDefault') ?>/>
			&nbsp;<label for="intDirectModeIsDefault" id="intDirectModeIsDefault_label">Direct mode is default</label>
		</td>
	</tr>
	<tr>
		<td align="right">Attachment size limit: </td>
		<td colspan="2">
			<input type="text" class="wm_input wm_addmargin" name="intAttachmentSizeLimit" id="intAttachmentSizeLimit" style="width: 85px" value="<?php $this->data->PrintIntValue('intAttachmentSizeLimit') ?>" maxlength="6" /> KB
			&nbsp;&nbsp;&nbsp;
			<input type="checkbox" class="wm_checkbox" name="intEnableAttachSizeLimit" id="intEnableAttachSizeLimit" value="1" <?php $this->data->PrintCheckedValue('intEnableAttachSizeLimit') ?>/>
			&nbsp;<label for="intEnableAttachSizeLimit">Enable attachment size limit</label>
		</td>
	</tr>
	<tr>
		<td align="right">Mailbox size limit: </td>
		<td colspan="2">
			<input type="text" class="wm_input wm_addmargin" name="intMailboxSizeLimit" id="intMailboxSizeLimit" style="width: 85px" value="<?php $this->data->PrintIntValue('intMailboxSizeLimit') ?>" maxlength="7" /> KB
			&nbsp;&nbsp;&nbsp;
			<input type="checkbox" class="wm_checkbox" name="intEnableMailboxSizeLimit" id="intEnableMailboxSizeLimit" value="1" <?php $this->data->PrintCheckedValue('intEnableMailboxSizeLimit') ?>/>
			&nbsp;<label for="intEnableMailboxSizeLimit">Enable mailbox size limit</label>
		</td>
	</tr>
	<tr>
		<td align="right">&nbsp;</td>
		<td colspan="2">
			<input type="checkbox" class="wm_checkbox" name="intTakeImapQuota" id="intTakeImapQuota" value="1" <?php $this->data->PrintCheckedValue('intTakeImapQuota') ?>/>
			&nbsp;<label for="intTakeImapQuota" id="intTakeImapQuota_label">Take quota value from IMAP server</label>
		</td>
	</tr>
	<tr>
		<td align="right">&nbsp;</td>
		<td colspan="2">
			<input type="checkbox" class="wm_checkbox" name="intAllowUsersChangeEmailSettings" id="intAllowUsersChangeEmailSettings" value="1" <?php $this->data->PrintCheckedValue('intAllowUsersChangeEmailSettings') ?>/>
			&nbsp;<label for="intAllowUsersChangeEmailSettings">Allow new users to change email settings</label>
		</td>
	</tr>
	<tr>
		<td align="right">&nbsp;</td>
		<td colspan="2">
			<input type="checkbox" class="wm_checkbox" name="intAllowNewUsersRegister" id="intAllowNewUsersRegister" value="1" <?php $this->data->PrintCheckedValue('intAllowNewUsersRegister') ?>/>
			&nbsp;<label for="intAllowNewUsersRegister">Allow automatic registration of new users on first login</label>
		</td>
	</tr>

	<tr>
		<td align="right">&nbsp;</td>
		<td colspan="2">
			<input type="checkbox" class="wm_checkbox" name="intAllowUsersAddNewAccounts" id="intAllowUsersAddNewAccounts" value="1" <?php $this->data->PrintCheckedValue('intAllowUsersAddNewAccounts') ?>/>
			&nbsp;<label for="intAllowUsersAddNewAccounts">Allow users to add new email accounts</label>
		</td>
	</tr>

	<tr>
		<td align="right">&nbsp;</td>
		<td colspan="2">
			<input type="checkbox" class="wm_checkbox" name="intAllowUsersChangeAccountsDef" id="intAllowUsersChangeAccountsDef" value="1" <?php $this->data->PrintCheckedValue('intAllowUsersChangeAccountsDef') ?>/>
			&nbsp;<label for="intAllowUsersChangeAccountsDef" id="intAllowUsersChangeAccountsDef_label">Allow users to change accounts which can be used to log in</label>
		</td>
	</tr>
	
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="3" class="wm_settings_list_select"><b>Internationalization Support</b></td>
	</tr>
	<tr><td colspan="3"><br /></td></tr>
	<tr>
		<td align="right">Default user charset </td>
		<td colspan="2">
			<select name="txtDefaultUserCharset" class="wm_input wm_addmargin" style="width: 320px;">
<?php $this->data->PrintValue('txtDefaultUserCharset') ?>
			</select> 
		</td>
	</tr>
	<tr>
		<td align="right">&nbsp;</td>
		<td colspan="2">
			<input type="checkbox" class="wm_checkbox" name="intAllowUsersChangeCharset" id="intAllowUsersChangeCharset" value="1" <?php $this->data->PrintCheckedValue('intAllowUsersChangeCharset') ?>/>
			&nbsp;<label for="intAllowUsersChangeCharset">Allow users to change charset</label>
		</td>
	</tr>
	<tr>
		<td align="right">Default user time offset</td>
		<td colspan="2">
			<select name="txtDefaultTimeZone" class="wm_input wm_addmargin" style="width: 320px;">
<?php $this->data->PrintValue('txtDefaultTimeZone') ?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right" >&nbsp;</td>
		<td colspan="2">
			<input type="checkbox" class="wm_checkbox" name="intAllowUsersChangeTimeZone" id="intAllowUsersChangeTimeZone" value="1" <?php $this->data->PrintCheckedValue('intAllowUsersChangeTimeZone') ?>/>
			&nbsp;<label for="intAllowUsersChangeTimeZone">Allow users to change time offset</label>
		</td>
	</tr>
	<tr><td colspan="3"><hr size="1"></td></tr>
	<tr>
		<td colspan="3" align="right">
			<input type="submit" name="submit" class="wm_button" value="Save" style="width: 100px; font-weight: bold" />
			<?php $this->data->PrintValue('btnSubmitReset') ?>
		</td>
	</tr>
</table>
</form>