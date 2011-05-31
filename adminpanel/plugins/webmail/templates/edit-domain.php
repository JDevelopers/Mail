<table>
	<tr>
		<td align="left">
			<span style="font-size: large;">Edit Domain</span>
		</td>
	</tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="wm_contacts_view">
	<tr>
		<td align="right" width="100">
			<nobr>Domain name:</nobr>
		</td>
		<td colspan="2" align="left">
			<span style="font-size: large;"><?php $this->data->PrintClearValue('DomainName'); ?></span>
			<input type="hidden" name="uid" value="<?php $this->data->PrintInputValue('uid'); ?>" />
		</td>
	</tr>
	<tr>
		<td></td>
		<td align="left" colspan="2">
			<?php $this->data->PrintValue('DomainTopTitle'); ?>
		</td>
	</tr>
	<tr class="<?php $this->data->PrintInputValue('filterHrefClass'); ?>">
		<td colspan="3">
			<div style="padding-left: 30px; padding-top: 10px;">
				<a href="<?php echo AP_INDEX_FILE;?>?tab=users&filter=<?php $this->data->PrintInputValue('uid'); ?>">See users of this domain</a>
				<span class="<?php $this->data->PrintInputValue('webmailDoaminSettingsHrefClass'); ?>">
					<br /><br />
					<a href="<?php echo AP_INDEX_FILE;?>?tab=wm&filter=<?php $this->data->PrintInputValue('filterHref'); ?>">See WebMail-related settings</a>
				</span>
			</div>
		</td>
	</tr>
</table>
<br />
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="wm_contacts_view">
	<tr>
		<td align="right" width="100">
			<nobr>Incoming mail:</nobr>
		</td>
		<td align="left" colspan="2">
			<input name="txtIncomingMail_domain" type="text" id="txtIncomingMail_domain"
					value="<?php $this->data->PrintInputValue('txtIncomingMail_domain'); ?>" maxlength="100" class="wm_input wm_addmargin" />
			&nbsp;&nbsp;
			<nobr>
				<nobr>Port:</nobr>
					<input name="intIncomingMailPort_domain" type="text" id="intIncomingMailPort_domain" maxlength="5" size="3"
						class="wm_input" value="<?php $this->data->PrintInputValue('intIncomingMailPort_domain'); ?>" />
			</nobr>
		</td>
	</tr>
	<tr>
		<td align="right">
			<nobr>Outgoing mail:</nobr>
		</td>
		<td align="left" colspan="2">
			<input name="txtOutgoingMail_domain" type="text" id="txtOutgoingMail_domain" maxlength="100" class="wm_input wm_addmargin"
					value="<?php $this->data->PrintInputValue('txtOutgoingMail_domain'); ?>" />
			&nbsp;&nbsp;
			<nobr>
				<nobr>Port:</nobr>
					<input name="intOutgoingMailPort_domain" type="text" id="intOutgoingMailPort_domain" maxlength="5" size="3"
						class="wm_input" value="<?php $this->data->PrintInputValue('intOutgoingMailPort_domain'); ?>" />
			</nobr>
			<input name="intIsInternal_domain" type="hidden" id="intIsInternal_domain" value="<?php $this->data->PrintInputValue('intIsInternal_domain'); ?>" />
		</td>
	</tr>
	<tr>
		<td>
			&nbsp;
		</td>
		<td colspan="2">
			<input name="intReqSmtpAuthentication_domain" type="checkbox" class="wm_checkbox" id="intReqSmtpAuthentication_domain" value="1"
					style="vertical-align: middle" <?php $this->data->PrintCheckedValue('intReqSmtpAuthentication_domain'); ?> />
			<label for="intReqSmtpAuthentication_domain">Requires SMTP authentication</label>
		</td>
	</tr>

	<tr class="<?php $this->data->PrintInputValue('hideLDAPAuthClass'); ?>"><td colspan="3"><br /></td></tr>
	<tr class="<?php $this->data->PrintInputValue('hideLDAPAuthClass'); ?>">
		<td></td>
		<td align="left" colspan="2">
			<input name="intLDAPAuth" type="checkbox" class="wm_checkbox" id="intLDAPAuth" value="1"
					style="vertical-align: middle" <?php $this->data->PrintCheckedValue('intLDAPAuth'); ?> />
			<label for="intLDAPAuth">LDAP Auth</label>
		</td>
	</tr>

	<tr class="<?php $this->data->PrintInputValue('hideLDAPAuthClass'); ?>"><td colspan="3"></td></tr>
	<tr class="<?php $this->data->PrintInputValue('hideLDAPAuthClass'); ?>">
		<td></td>
		<td align="left" colspan="2" style="padding: 0px;">
			<div class="wm_safety_info" style="width: 400px;">
				LDAP TODO
			</div>
		</td>
	</tr>

	<tr><td colspan="3"><br /></td></tr>
	<tr class="<?php $this->data->PrintInputValue('hideGlobaAddressBookClass'); ?>">
		<td></td>
		<td align="left" colspan="2">
			<input name="intDomainGlobalAddrBook" type="checkbox" class="wm_checkbox" id="intDomainGlobalAddrBook" value="1"
					style="vertical-align: middle" <?php $this->data->PrintCheckedValue('intDomainGlobalAddrBook'); ?> />
			<label for="intDomainGlobalAddrBook">Enable Global Address Book for this domain</label>
		</td>
	</tr>
	
	<tr class="<?php $this->data->PrintInputValue('hideGlobaAddressBookClass'); ?>"><td colspan="3"></td></tr>
	<tr class="<?php $this->data->PrintInputValue('hideGlobaAddressBookClass'); ?>">
		<td></td>
		<td align="left" colspan="2" style="padding: 0px;">
			<div class="wm_safety_info" style="width: 400px;">
				Each user within this domain will see email IDs of other
				users of the same domain in his/her address book.
			</div>
		</td>
	</tr>
</table>
<br /><hr />
<div align="right">
	<input type="submit" name="save" class="wm_button" style="width: 100px;" value="Save" />
</div>

<script type="text/javascript">
	Validator.RegisterAllowNum($('intIncomingMailPort_domain'));
	Validator.RegisterAllowNum($('intOutgoingMailPort_domain'));
</script>