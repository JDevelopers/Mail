<input name="intAccountId" type="hidden" value="<?php $this->data->PrintInputValue('intAccountId'); ?>" />
<input name="uid" type="hidden" value="<?php $this->data->PrintInputValue('uid'); ?>" />
<input name="uType" type="hidden" value="mlist" />
<table>
	<tr>
		<td align="left">
			<span style="font-size: large;">Edit Mail List</span>
		</td>
	</tr>
</table>
<table width="400" border="0" cellspacing="0" cellpadding="0" class="wm_contacts_view">
	<tr>
		<td>
			<span style="font-size: large"><?php $this->data->PrintClearValue('UserEmail'); ?></span>
		</td>
	</tr>
</table>
<script type="text/javascript">
	var virtdomaintabs = new Array();
	virtdomaintabs.push("content_custom_tab_1");
	
	function SelectAllOnSubmit() {
		SelectListAll("ListMembersDDL");
	}
	
	function SetAllOf() {
		$('custom_tab_1').className = 'wm_settings_switcher_item';
	}
</script>
<div class="wm_settings_accounts_info" style="width: 100%; margin: 10px 0px">
	<div class="wm_settings_switcher_indent"></div>
	<div id="custom_tab_1" class="wm_settings_switcher_select_item" onclick="SwitchItem(virtdomaintabs, 'content_custom_tab_1'); SetAllOf(); this.className = 'wm_settings_switcher_select_item';">
		<a href="javascript:void(0)">Users in list</a>
	</div>
</div>
<div id="content_custom_tab_1" style="padding-top: 20px;">
	<table class="wm_contacts_view" width="200" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>New user Address:</td>
		</tr>
		<tr>
			<td>
				<input name="NewUserAddressID" id="NewUserAddressID" type="text" value=""
					   maxlength="100" tabindex="11" class="wm_input" style="width: 250px;" />
			</td>
		</tr>
		<tr>
			<td>
				<input type="button" name="AddButton" value="Add user to list" id="AddButton" tabindex="14" class="wm_button"
					onclick="LocalAddValueToList($('NewUserAddressID'), $('ListMembersDDL'));"/>
			</td>
		</tr>

		<tr>
			<td>
				<br /><br />
			</td>
		</tr>
		
		<tr>
			<td>List members:</td>
		</tr>
		<tr>
			<td valign="top">
				<select size="9" name="ListMembersDDL[]" id="ListMembersDDL" tabindex="15" class="wm_input" 
						style="width: 250px;" multiple="multiple"><?php $this->data->PrintValue('ListMembersDDL'); ?></select>
			</td>
		</tr>
		<tr>
			<td>
				<input type="button" name="DeleteButton" value="Delete user from list" id="DeleteButton"
					tabindex="16" class="wm_button" onclick="DeleteSelectedFromList($('ListMembersDDL'));"/>
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
<?php $this->data->PrintValue('StartJs'); ?>

Validator.RegisterAllowEmailSymbols($('NewUserAddressID'));

$("ListMembersDDL").form.onsubmit = function() {
	OnlineLoadInfo(_langSaving);
	SelectAllOnSubmit();
};

function LocalAddValueToList(from, to)
{
	if (IsEmailAddress(from))
	{
		AddValueToList(from, to);
	}
}

$("NewUserAddressID").onkeypress = function(ev) {
	ev = (window.event) ? window.event : ev;
	if (ev.keyCode == 13) {
		LocalAddValueToList($('NewUserAddressID'), $('ListMembersDDL'));
		return false;
	}
	return true;
};

$("ListMembersDDL").onkeypress = function(ev) {
	ev = (window.event) ? window.event : ev;	
	if (ev.keyCode == 46) {
		DeleteSelectedFromList($('ListMembersDDL'));
	}
	return (ev.keyCode != 13);
};

</script>
<br /><hr />
<div align="right">
	<input type="submit" class="wm_button" style="width: 100px;" value="Save">
</div>