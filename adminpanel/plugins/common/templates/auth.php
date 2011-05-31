<!--?php $this->data->PrintValue('hideClass_'.$this->data->GetInputValue('inputMode')); ?-->
<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="<?php $this->data->PrintInputValue('inputMode'); ?>_form"  onsubmit="OnlineLoadInfo(_langSaving); return true;">
<input type="hidden" name="form_id" value="<?php $this->data->PrintInputValue('inputMode'); ?>">

<table class="wm_settings_common" width="550">
	<tr>
		<td colspan="2" class="wm_safety_info">
			You can change main admin (mailadm) password here.
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2" class="wm_settings_list_select">
			<b>Change Password</b>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td align="right">
			Username:
		</td>
		<td>
			<b><?php $this->data->PrintValue('UserName') ?></b>
			<input name="txtLogin" type="hidden" value="<?php $this->data->PrintInputValue('UserName') ?>" />
		</td>
	</tr>    
	<tr>
		<td align="right">
			New&nbsp;password:
		</td>
		<td>
			<input name="txtPassword1" type="password" maxlength="100" id="txtPassword1" tabindex="2" class="wm_input" value="<?php $this->data->PrintInputValue('txtPassword1') ?>" />                    
		</td>
	</tr>
	<tr>
		<td align="right">
			Confirm&nbsp;new&nbsp;password:
		</td>
		<td>
			<input name="txtPassword2" type="password" maxlength="100" id="txtPassword2" tabindex="2" class="wm_input" value="<?php $this->data->PrintInputValue('txtPassword2') ?>" />                    
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td colspan="2" align="right">
			<input type="submit" name="submit_btn" value="Save" class="wm_button" style="width: 100px">
		</td>
	</tr>
</table>
</form>