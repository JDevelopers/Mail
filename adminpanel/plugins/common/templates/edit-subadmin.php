<input name="intAdminId" type="hidden" value="<?php $this->data->PrintInputValue('intAdminId'); ?>" />

<script type="text/javascript">
	var useredittabs = new Array();
	useredittabs.push("content_custom_tab_1");

	function SetAllOf()
	{
		$('custom_tab_1').className = 'wm_settings_switcher_item';
	}
</script>

<table>
	<tr>
		<td align="left">
			<span style="font-size: large;"><?php $this->data->PrintValue('topHeader'); ?></span>
		</td>
	</tr>
</table>

<div class="wm_settings_accounts_info" style="width: 100%; margin: 15px 0px">
	<div class="wm_settings_switcher_indent"></div>
	<div id="custom_tab_1" class="wm_settings_switcher_select_item" onclick="SwitchItem(useredittabs, 'content_custom_tab_1'); SetAllOf(); this.className = 'wm_settings_switcher_select_item';">
		<a href="javascript:void(0)">Settings</a>
	</div>
</div>

<div id="content_custom_tab_1" style="padding-top: 10px;">
	<table class="wm_contacts_view_new">
		<tr>
			<td align="right">
				<nobr>* Login:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="txtLogin" type="text" id="txtLogin" class="wm_input" style="width: 350px" maxlength="100" value="<?php $this->data->PrintInputValue('txtLogin'); ?>" />
			</td>
		</tr>
		<tr>
			<td align="right">
				<nobr>* Password:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="txtPassword" type="password" maxlength="100" id="txtPassword" class="wm_input" style="width: 350px;" value="<?php $this->data->PrintInputValue('txtPassword'); ?>" />
			</td>
		</tr>
		<tr>
			<td align="right">
				<nobr>Description:</nobr>
			</td>
			<td align="left" colspan="2">
				<input name="txtDescription" type="text" maxlength="255" id="txtDescription" class="wm_input" style="width: 350px;" value="<?php $this->data->PrintInputValue('txtDescription'); ?>" />
			</td>
		</tr>

		<tr>
			<td align="right" style="vertical-align: top;">
				<nobr>* Domains:</nobr>
			</td>
			<td align="left" colspan="2">
				<select name="selDomains[]" id="selDomains" class="wm_input" size="<?php $this->data->PrintIntValue('selDomainsSize'); ?>" multiple="multiple" style="width: 353px">
					<?php $this->data->PrintValue('selDomains'); ?>
				</select>
			</td>
		</tr>
		<tr><td colspan="3"></tr>
		<tr>
			<td align="right"></td>
			<td align="left" colspan="2" style="padding: 0px;">
				<div class="wm_safety_info">
					Defines a list of domains this subadmin is allowed to manage.<br />
					Hold CTRL/SHIFT to select more than one domain.
				</div>
			</td>
		</tr>
	</table>
</div>
<script type="text/javascript">
<?php $this->data->PrintValue('StartWmJs'); ?>
</script>
<br />
<hr />
<div align="right">
	<span class="wm_secondary_info" style="float: left;">* required fields</span>
	<input type="submit" class="wm_button" style="width: 100px;" value="Save">
</div>