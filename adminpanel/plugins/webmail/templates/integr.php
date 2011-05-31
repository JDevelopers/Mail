<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="integr_form" <?php $this->data->PrintValue('hideClass_integr') ?> onsubmit="OnlineLoadInfo(_langSaving); return true;">
<input type="hidden" name="form_id" value="integr" />
<table class="wm_admin_center" width="550">
	<tr>
		<td width="90"></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="2" class="wm_admin_title">Server Integration</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td align="right">
			Path&nbsp;to&nbsp;server:
		</td>
		<td>
			<input type="text" name="txtWmServerRootPath" id="txtWmServerRootPath" value="<?php $this->data->PrintInputValue('txtWmServerRootPath') ?>" size="50" class="wm_input" maxlength="500" />
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<div class="wm_safety_info">
				Path to the MailRoot folder of AfterLogic XMail Server in your system, for instance "/var/MailRoot".
			</div>
			<br /><br />
		</td>
	</tr>
	<tr>
		<td align="right">
			Server&nbsp;host:
		</td>
		<td>
			<input type="text" name="txtWmServerHostName" id="txtWmServerHostName" value="<?php $this->data->PrintInputValue('txtWmServerHostName') ?>" size="50" class="wm_input" maxlength="500" />
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<div class="wm_safety_info">
				IP address or hostname where AfterLogic XMail Server resides.
			</div>
			<br /><br />
		</td>
	</tr>
	<tr>
		<td align="right">
			<input type="checkbox" class="wm_checkbox" name="intWmAllowManageXMailAccounts" id="intWmAllowManageXMailAccounts" value="1" <?php $this->data->PrintCheckedValue('intWmAllowManageXMailAccounts') ?> />
		</td>
		<td>
			<label for="intWmAllowManageXMailAccounts">
				Allow&nbsp;users&nbsp;to&nbsp;manage&nbsp;accounts&nbsp;on&nbsp;AfterLogic&nbsp;XMail&nbsp;Server
			</label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<div class="wm_safety_info">
				If a user adds or removes a linked account in his primary account settings and domain part of this account matches any of
				your domains hosted by AfterLogic XMail Server, this account will be added/removed on AfterLogic XMail Server.
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<br />
		</td>
	</tr>
	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td colspan="2" align="right">
			<input type="submit" name="save" class="wm_button" value="Save" style="width: 100px;" />
		</td>
	</tr>
</table>
</form>