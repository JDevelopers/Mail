<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="contacts_form" <?php $this->data->PrintValue('hideClass_contacts') ?> onsubmit="OnlineLoadInfo(_langSaving); return true;">
<input type="hidden" name="form_id" value="contacts" />
<table class="wm_settings_common" width="550">
	<tr>
		<td width="180"></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="2" class="wm_settings_list_select">
			<b>Address Book Settings</b>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2" style="padding: 0px;">
			<div class="wm_safety_info">
				Please enable "Global Address Book" for appropriate domains in "Domains" section in case of "Domain Wide" address book selection.
			</div>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2" align="left" valign="top">
			<input type="radio" name="bGlobalAddressBookArea" id="iGlobalAddressBookAreaSystem"
				value="<?php echo WM_GLOBAL_ADDRESS_BOOK_SYSTEM; ?>" <?php
$this->data->PrintCheckedValue('dGlobalAddressBookAreaSystem');
				?> />
			<label for="iGlobalAddressBookAreaSystem">System Wide</label>
			<br /><br />
			<input type="radio" name="bGlobalAddressBookArea" id="iGlobalAddressBookAreaDomain"
				value="<?php echo WM_GLOBAL_ADDRESS_BOOK_DOMAIN; ?>" <?php
$this->data->PrintCheckedValue('dGlobalAddressBookAreaDomain');
				?> />
			<label for="iGlobalAddressBookAreaDomain">Domain Wide</label>
			<br /><br />
			<input type="radio" name="bGlobalAddressBookArea" id="iGlobalAddressBookAreaOff"
				value="<?php echo WM_GLOBAL_ADDRESS_BOOK_OFF; ?>" <?php
$this->data->PrintCheckedValue('dGlobalAddressBookAreaOff');
				?> />
			<label for="iGlobalAddressBookAreaOff">Off</label>
		</td>
	</tr>

	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td colspan="2" align="right">
			<input type="submit" name="submit_btn" value="Save" class="wm_button" style="width: 100px" />
		</td>
	</tr>
</table>
</form>