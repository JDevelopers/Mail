<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="<?php $this->data->PrintInputValue('inputMode'); ?>_form" <?php $this->data->PrintValue('hideClass_'.$this->data->GetInputValue('inputMode')); ?> onsubmit="OnlineLoadInfo(_langSaving); return true;">
<input type="hidden" name="form_id" value="<?php $this->data->PrintInputValue('inputMode'); ?>">

<table class="wm_settings_common" width="550">
	<tr>
		<td colspan="2" class="wm_settings_list_select">
			Additional options
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td>
			<b>PHP:</b> <?php echo phpversion(); 
			if (!function_exists('openssl_open'))
			{
				echo ' (ssl: off)';
			}
			?>
		</td>
	</tr>
	<tr>
		<td>
			<b>Admin Panel:</b> <?php echo AP_VERSION ?>
		</td>
	</tr>
	<tr>
		<td>
			<b>Folder:</b> <?php $this->data->PrintValue('txtDataFolder'); ?>
		</td>
	</tr>
	<tr>
		<td>
			<b>Admin Panel Log File:</b> <?php $this->data->PrintValue('txtLogFileInfo'); ?>
			<input name="txtLogFile" type="hidden" value="<?php $this->data->PrintInputValue('txtLogFile') ?>" />
		</td>
	</tr>
	<tr>
		<td>
			<input type="submit" value=" Clear log " name = "clear_log_btn" class="<?php $this->data->PrintInputValue('classLogButtons') ?>"
			style="font-size: 11px; margin-right: 4px;" 
			/><input type="submit" value=" Delete logs " name = "clear_all_logs_btn" class="wm_button" style="font-size: 11px;" />
		</td>
	</tr>
	<tr>
		<td><br /></td>
	</tr>
	<tr>
		<td>
			<input
				type="button" onclick="PopUpWindow('<?php echo AP_INDEX_FILE; ?>?mode=pop&type=log_all');" 
				value="View entire log" class="<?php $this->data->PrintInputValue('classLogButtons') ?>" style="font-size: 11px; width: 150px;" 
				/><input
				type="button" onclick="PopUpWindow('<?php echo AP_INDEX_FILE; ?>?mode=pop&type=log');" value="View last 50KB of log" 
				class="<?php $this->data->PrintInputValue('classLogButtons') ?>" style="font-size: 11px; width: 150px; margin-left: 4px; margin-right: 4px;" 
				/><input
				type="button" onclick="PopUpWindow('<?php echo AP_INDEX_FILE; ?>?mode=pop&type=info');" value="View PHP information" 
				class="wm_button" style="font-size: 11px; width: 150px;"
				/>
			<br /><br />
		</td>
	</tr>
	
	<tr>
		<td>
			<input type="button" value=" Exit " name = "exit_btn" class="wm_button" style="font-size: 11px; width: 100px;" onclick="document.location='<?php echo AP_INDEX_FILE; ?>?enable=off'" /> 
		</td>
	</tr>
</table>
</form>