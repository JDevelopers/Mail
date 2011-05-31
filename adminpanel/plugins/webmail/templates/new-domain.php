<table>
	<tr>
		<td align="left">
			<span style="font-size: large;">Create Domain</span>
		</td>
	</tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="wm_contacts_view">
	<tr>
		<td></td>
		<td align="left" colspan="2">
			<?php $this->data->PrintValue('DomainTopTitle'); ?>
		</td>
	</tr>
	<tr><td colspan="3"><br /></td></tr>
	<tr>
		<td align="right" width="100">
			Domain&nbsp;name:
		</td>
		<td colspan="2">
			<input name="textDomainName" type="text" id="textDomainName" class="wm_input wm_addmargin" maxlength="100" value="<?php $this->data->PrintInputValue('DomainName'); ?>" />
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<br />
		</td>
	</tr>
	<tr class="<?php $this->data->PrintInputValue('classNewDomainEditZone'); ?>">
		<td align="right">
			Incoming mail:
		</td>
		<td align="left" colspan="2">
			<input name="txtIncomingMail_domain" type="text" id="txtIncomingMail_domain"
					value="<?php $this->data->PrintInputValue('txtIncomingMail_domain'); ?>" maxlength="100" class="wm_input wm_addmargin" />
			&nbsp;&nbsp;		
			<nobr>
				Port: 
				<input name="intIncomingMailPort_domain" type="text" id="intIncomingMailPort_domain" maxlength="5" size="3"
						class="wm_input" value="<?php $this->data->PrintInputValue('intIncomingMailPort_domain'); ?>" />
				&nbsp;
				<select name="intIncomingMailProtocol_domain" id="intIncomingMailProtocol_domain" class="wm_input" onchange="$('intIncomingMailPort_domain').value=(this.value=='POP3')?110:143">
					<option value="POP3" <?php $this->data->PrintSelectedValue('intIncomingMailProtocolPOP3_domain'); ?> >POP3</option>
					<option value="IMAP4" <?php $this->data->PrintSelectedValue('intIncomingMailProtocolIMAP4_domain'); ?> >IMAP4</option>
				</select>
			</nobr>
			<input name="intIsInternal_domain" type="hidden" id="intIsInternal_domain" value="<?php $this->data->PrintInputValue('intIsInternal_domain'); ?>" />
		</td>
	</tr>
	<tr class="<?php $this->data->PrintInputValue('classNewDomainEditZone'); ?>">
		<td align="right">
			Outgoing mail:
		</td>
		<td align="left" colspan="2">
			<input name="txtOutgoingMail_domain" type="text" id="txtOutgoingMail_domain" maxlength="100" class="wm_input wm_addmargin"
					value="<?php $this->data->PrintInputValue('txtOutgoingMail_domain'); ?>" />
			&nbsp;&nbsp;
			<nobr>
			Port: <input name="intOutgoingMailPort_domain" type="text" id="intOutgoingMailPort_domain" maxlength="5" size="3"
					class="wm_input" value="<?php $this->data->PrintInputValue('intOutgoingMailPort_domain'); ?>" />
			</nobr>
		</td>
	</tr>
	<tr class="<?php $this->data->PrintInputValue('classNewDomainEditZone'); ?>">
		<td>
			&nbsp;
		</td>
		<td colspan="2" align="left">
			<input name="intReqSmtpAuthentication_domain" type="checkbox" class="wm_checkbox" id="intReqSmtpAuthentication_domain" value="1"
					style="vertical-align: middle" <?php $this->data->PrintCheckedValue('intReqSmtpAuthentication_domain'); ?>/>
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
<script type="text/javascript">
	Validator.RegisterAllowDomainSymbols($('textDomainName'));
	Validator.RegisterAllowNum($('intIncomingMailPort_domain'));
	Validator.RegisterAllowNum($('intOutgoingMailPort_domain'));
</script>
<br /><hr />
<div align="right">
	<input type="submit" class="wm_button" style="width: 100px;" value="Save">
</div>