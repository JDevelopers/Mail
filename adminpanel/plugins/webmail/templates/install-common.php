<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="<?php $this->data->PrintInputValue('inputMode'); ?>_form" <?php $this->data->PrintValue('hideClass_'.$this->data->GetInputValue('inputMode')); ?>>
<input type="hidden" name="form_id" value="<?php $this->data->PrintInputValue('inputMode'); ?>" />

<table class="wm_admin_center" width="550">
	<tr>
		<td width="200"></td>
		<td width="350"><br /></td>
	</tr>
	<tr>
		<td colspan="2">
			<span style="font-size: 14px">Step <?php $this->data->PrintValue('StepCount'); ?>:</span>
			<br />
			<span style="font-size: 18px">Set Admin Panel Password</span>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2">
			To restrict access to the admin panel, it's strongly recommended to	set a complex password.
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
			Password:
		</td>
		<td>
			<input name="txtPassword1" type="password" maxlength="100" id="txtPassword1" tabindex="2" class="wm_install_input" value="<?php $this->data->PrintInputValue('txtPassword1') ?>" />                    
		</td>
	</tr>
	<tr>
		<td align="right">
			Confirm&nbsp;password:
		</td>
		<td>
			<input name="txtPassword2" type="password" maxlength="100" id="txtPassword2" tabindex="2" class="wm_install_input" value="<?php $this->data->PrintInputValue('txtPassword2') ?>" />                    
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2" align="center">
			<?php $this->data->PrintValue('InfoMsg'); ?>
		</td>
	</tr>
	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td align="left">
			<input type="button" name="back_btn" id="back_btn" value="Back" class="wm_install_button" style="width: 100px" onclick="javascript:<?php $this->data->PrintValue('onClickBack'); ?>" />
		</td>
		<td align="right">
			<a name="foot"></a>
			<input type="submit" name="submit_btn" id="submit_btn" value="Next" class="wm_install_button" style="width: 100px" />
		</td>
	</tr>
	<tr><td colspan="2"><br /><br /></td></tr>
</table>
</form>