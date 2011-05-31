<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="<?php $this->data->PrintInputValue('inputMode'); ?>_form" <?php $this->data->PrintValue('hideClass_'.$this->data->GetInputValue('inputMode')); ?> onsubmit="OnlineLoadInfo(_langSaving); return true;">
<input type="hidden" name="form_id" value="<?php $this->data->PrintInputValue('inputMode'); ?>">

<table class="wm_settings_common" width="550">
	<tr>
		<td width="150"></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="2" class="wm_settings_list_select">
			 <b>License Key Settings</b>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td align="right">
			 License key:
		</td>
		<td>
			<input name="txtLicenseKey" type="text" class="wm_input" style="width: 350px" value="<?php $this->data->PrintInputValue('txtLicenseKey') ?>" />
		</td>
	</tr>
	
	<tr>
		<td></td>
		<td>
			<font color="red"><?php $this->data->PrintClearValue('txtLicenseKeyText') ?></font>
			<br /><a <?php $this->data->PrintValue('txtHideGetTrialClass') ?> href="http://www.afterlogic.com/download/get-trial-key?productid=<?php $this->data->PrintValue('txtGetTrialId') ?>" target="_blank">Get a trial key</a>
		</td>
	</tr>
	
	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td colspan="2" align="right">
			<input type="submit" name="submit_btn" value="Save" class="wm_button" style="width: 100px">
		</td>
	</tr>
</table>
</form>