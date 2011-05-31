<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="interface_form" <?php $this->data->PrintValue('hideClass_interface') ?> onsubmit="OnlineLoadInfo(_langSaving); return true;">
<input type="hidden" name="form_id" value="interface" />
<input type="hidden" name="filter_host" value="<?php $this->data->PrintInputValue('txtFilterHost') ?>" />

<table class="wm_settings_common" width="550">
	<tr>
		<td width="150"></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="2" class="wm_settings_list_select"><b>Interface Settings</b></td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td align="right">Mails per page: </td>
		<td><input type="text" class="wm_input wm_addmargin" name="intMailsPerPage" id="intMailsPerPage" size="4" value="<?php $this->data->PrintIntValue('intMailsPerPage') ?>" maxlength="2" /></td>
	</tr>

	<tr>
		<td align="right">&nbsp;</td>
		<td>
			<input type="checkbox" class="wm_checkbox" name="intRightMessagePane" id="intRightMessagePane" value="1" <?php $this->data->PrintCheckedValue('intRightMessagePane') ?> />
			&nbsp;<label for="intRightMessagePane">The message pane is to the right of the message list, rather than below</label>
		</td>
	</tr>
	<tr>
		<td align="right">&nbsp;</td>
		<td>
			<input type="checkbox" class="wm_checkbox" name="intAlwaysShowPictures" id="intAlwaysShowPictures" value="1" <?php $this->data->PrintCheckedValue('intAlwaysShowPictures') ?> />
			&nbsp;<label for="intAlwaysShowPictures">Always show pictures in messages</label>
		</td>
	</tr>

	<tr>
		<td align="right">Default skin: </td>
		<td>
			<select name="txtDefaultSkin" class="wm_input wm_addmargin" style="width: 150px;">
<?php $this->data->PrintValue('txtDefaultSkin') ?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right">&nbsp;</td>
		<td>
			<input type="checkbox" class="wm_checkbox" name="intAllowUsersChangeSkin" id="intAllowUsersChangeSkin" value="1" <?php $this->data->PrintCheckedValue('intAllowUsersChangeSkin') ?> />
			&nbsp;<label for="intAllowUsersChangeSkin">Allow users to change skin</label>
		</td>
	</tr>
	<tr>
		<td align="right">Default language: </td>
		<td>
			<select name="txtDefaultLanguage" class="wm_input wm_addmargin" style="width: 150px;">
<?php $this->data->PrintValue('txtDefaultLanguage') ?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right">&nbsp;</td>
		<td>
			<input type="checkbox" class="wm_checkbox" name="intAllowUsersChangeLanguage" id="intAllowUsersChangeLanguage" value="1" <?php $this->data->PrintCheckedValue('intAllowUsersChangeLanguage') ?> />
			&nbsp;<label for="intAllowUsersChangeLanguage">Allow users to change interface language</label>
		</td>
	</tr>
	<tr class="wm_hide">
		<td align="right">&nbsp;</td>
		<td>
			<input type="checkbox" class="wm_checkbox" name="intShowTextLabels" id="intShowTextLabels" value="1" <?php $this->data->PrintCheckedValue('intShowTextLabels') ?> />
			&nbsp;<label for="intShowTextLabels">Show text labels</label>
		</td>
	</tr>
	<tr>
		<td align="right" >&nbsp;</td>
		<td>
			<input type="checkbox" class="wm_checkbox" name="intAllowDHTMLEditor" id="intAllowDHTMLEditor" value="1" <?php $this->data->PrintCheckedValue('intAllowDHTMLEditor') ?> />
			&nbsp;<label for="intAllowDHTMLEditor">Allow DHTML editor</label>
		</td>
	</tr>
	
	<tr>
		<td align="right" >&nbsp;</td>
		<td>
			<input type="checkbox" class="wm_checkbox" name="intAllowContacts" id="intAllowContacts" value="1" <?php $this->data->PrintCheckedValue('intAllowContacts') ?> />
			&nbsp;<label for="intAllowContacts">Allow contacts</label>
		</td>
	</tr>
	<tr class="<?php $this->data->PrintInputValue('classAllowCalendar') ?>">
		<td align="right">&nbsp;</td>
		<td>
			<input type="checkbox" class="wm_checkbox" name="intAllowCalendar" id="intAllowCalendar" value="1" <?php $this->data->PrintCheckedValue('intAllowCalendar') ?> />
			&nbsp;<label for="intAllowCalendar">Allow calendar</label>
		</td>
	</tr>

	<tr class="<?php $this->data->PrintInputValue('classSaveInSent') ?>">
		<td align="right">Save outgoing emails in Sent Items:</td>
		<td>
			<select style="width: 150px;" class="wm_input" name="selSaveInSent">
				<option value="<?php $this->data->PrintInputValue('SaveInSentAlwaysIntValue') ?>" <?php $this->data->PrintSelectedValue('SaveInSentAlways') ?> >Always</option>
				<option value="<?php $this->data->PrintInputValue('SaveInSentOnIntValue') ?>" <?php $this->data->PrintSelectedValue('SaveInSentOn') ?> >Default=On</option>
				<option value="<?php $this->data->PrintInputValue('SaveInSentOffIntValue') ?>" <?php $this->data->PrintSelectedValue('SaveInSentOff') ?> >Default=Off</option>
			</select>
		</td>
	</tr>

	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td colspan="2" align="right">
			<input type="submit" name="submit" value="Save" class="wm_button" style="width: 100px" />
			<?php $this->data->PrintValue('btnSubmitReset') ?>
		</td>
	</tr>
</table>
<script type="text/javascript">
	Validator.RegisterAllowNum($('intMailsPerPage'));
</script>
</form>