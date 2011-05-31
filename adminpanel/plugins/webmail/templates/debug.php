<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="debug_form" <?php $this->data->PrintValue('hideClass_debug') ?> onsubmit="OnlineLoadInfo(_langSaving); return true;">
<input type="hidden" name="form_id" value="debug" />
<table class="wm_settings_common" width="550">
	<tr>
		<td width="90"></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="2" class="wm_settings_list_select">
			<b>Debug</b>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td align="right">
			<input type="checkbox" class="wm_checkbox" name="intEnableLogging" id="intEnableLogging" value="1" <?php $this->data->PrintCheckedValue('intEnableLogging') ?>/>
		</td>
		<td>
			<label for="intEnableLogging">Enable debug logging</label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td style="padding-right: 0px;">
			<div class="wm_safety_info">
				Enables debug logging helpful for troubleshooting.<br /><br />
				NOTE: "Full debug" mode degrades performance, not recommended for permanent use in production environments.
			</div>
			<br />
		</td>
	</tr>
	<tr>
		<td align="right">
			Log level:
		</td>
		<td>
			<select id="intLogLevel" name="intLogLevel">
				<?php $this->data->PrintValue('optLogLevel') ?>
			</select>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td align="right">
			Path&nbsp;for&nbsp;log:</td>
		<td>
			<input type="text" name="txtPathForLog" value="<?php $this->data->PrintInputValue('txtPathForLog') ?>" class="wm_input" readonly="readonly" style="width: 330px" />
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<br /><input
				type="button" onclick="PopUpWindow('<?php echo AP_INDEX_FILE; ?>?mode=pop&type=log_all');" 
				value="View entire log (<?php $this->data->PrintInputValue('txtLogSize') ?>)"
				class="wm_button" style="font-size: 11px; width: 150px" 
				/>&nbsp;<input
				type="button" onclick="PopUpWindow('<?php echo AP_INDEX_FILE; ?>?mode=pop&type=log');" value="View last 50KB of log" 
				class="wm_button" style="font-size: 11px; width: 150px" 
				/>&nbsp;<input
				type="submit" value=" Clear log " name = "clear_log_btn" 
				class="wm_button" style="font-size: 11px;" />
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2" class="wm_settings_list_select">
			<b>Events</b>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td align="right">
			<input type="checkbox" class="wm_checkbox" name="intEnableEventLogging" id="intEnableEventLogging" value="1" <?php $this->data->PrintCheckedValue('intEnableEventLogging') ?>/>
		</td>
		<td>
			<label for="intEnableEventLogging">Enable event logging</label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td style="padding-right: 0px;">
			<div class="wm_safety_info">
				Enables event logging useful for tracking users' actions.
			</div>
			<br />
			<br />
		</td>
	</tr>
	<tr>
		<td align="right">
			Path&nbsp;for&nbsp;log:</td>
		<td>
			<input type="text" name="txtPathForEventLog" value="<?php $this->data->PrintInputValue('txtPathForEventLog') ?>" class="wm_input" readonly="readonly" style="width: 330px" />
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<br /><input
				type="button" onclick="PopUpWindow('<?php echo AP_INDEX_FILE; ?>?mode=pop&type=event_all');"
				value="View entire log (<?php $this->data->PrintInputValue('txtEventLogSize') ?>)"
				class="wm_button" style="font-size: 11px; width: 150px"
				/>&nbsp;<input
				type="button" onclick="PopUpWindow('<?php echo AP_INDEX_FILE; ?>?mode=pop&type=event');" value="View last 50KB of log"
				class="wm_button" style="font-size: 11px; width: 150px"
				/>&nbsp;<input
				type="submit" value=" Clear log " name = "clear_event_btn"
				class="wm_button" style="font-size: 11px;" />
		</td>
	</tr>

	<tr><td colspan="2"><br /></td></tr>
	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td colspan="2" align="right">
			<input type="submit" name="submit_btn" value="Save" class="wm_button" style="width: 100px" />
		</td>
	</tr>
</table>
</form>