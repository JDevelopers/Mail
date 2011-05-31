<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="<?php $this->data->PrintInputValue('inputMode'); ?>_form" <?php $this->data->PrintValue('hideClass_'.$this->data->GetInputValue('inputMode')); ?>>
<input type="hidden" name="form_id" value="<?php $this->data->PrintInputValue('inputMode'); ?>" />

<table class="wm_admin_center" width="550">
	<tr>
		<td width="100"></td>
		<td><br /></td>
	</tr>
	<tr>
		<td colspan="2">
			<span style="font-size: 14px">Step <?php $this->data->PrintValue('StepCount'); ?>:</span>
			<br />
			<span style="font-size: 18px">Enter The License Key</span>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td align="right">
			 License key:
		</td>
		<td>
			<input name="txtLicenseKey" type="text" class="wm_install_input" style="width: 350px;" value="<?php $this->data->PrintInputValue('txtLicenseKey') ?>" />
		</td>
	</tr>
	
	<tr>
		<td></td>
		<td>
			<font color="red"><?php $this->data->PrintClearValue('txtLicenseKeyText') ?></font>
			<div <?php $this->data->PrintValue('txtHideGetTrialClass') ?>>
				<br />
				If you do not have a license key, you can get 30-day trial key <a href="http://www.afterlogic.com/download/get-trial-key?productid=<?php $this->data->PrintValue('txtGetTrialId') ?>" target="_blank">here</a>.
			</div>
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