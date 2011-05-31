<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="mobile_form" <?php $this->data->PrintValue('hideClass_debug') ?> onsubmit="OnlineLoadInfo(_langSaving); return true;">
<input type="hidden" name="form_id" value="mobile" />
<table class="wm_settings_common" width="550">
	<tr>
		<td width="120"></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="2" class="wm_settings_list_select">
			<b>Mobile sync</b>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="chEnableMobileSync" id="chEnableMobileSync" value="1"
				<?php $this->data->PrintCheckedValue('chEnableMobileSync') ?> <?php $this->data->PrintDisabledValue('intEnableMobileSyncDisabled') ?> class="wm_checkbox" />
			<label for="chEnableMobileSync" id="chEnableMobileSync_label"
				style="<?php $this->data->PrintInputValue('styleEnableMobileSyncLabel') ?>">Enable mobile sync</label>
		</td>
	</tr>
	<tr>
		<td align="right">
			<span id="txtPathForMobileSync_label">Mobile sync URL:</span>
		</td>
		<td>
			<input type="text" name="txtPathForMobileSync" id="txtPathForMobileSync" value="<?php $this->data->PrintInputValue('txtPathForMobileSync') ?>" class="wm_input" style="width: 430px" />
		</td>
	</tr>
	<tr>
		<td align="right">
			<span id="txtMobileSyncContactDataBase_label">Mobile sync contact database:</span>
		</td>
		<td>
			<input type="text" name="txtMobileSyncContactDataBase" id="txtMobileSyncContactDataBase" value="<?php $this->data->PrintInputValue('txtMobileSyncContactDataBase') ?>" class="wm_input" style="width: 430px" />
		</td>
	</tr>
	<tr>
		<td align="right">
			<span id="txtMobileSyncCalendarDataBase_label">Mobile sync calendar database:</span>
		</td>
		<td>
			<input type="text" name="txtMobileSyncCalendarDataBase" id="txtMobileSyncCalendarDataBase" value="<?php $this->data->PrintInputValue('txtMobileSyncCalendarDataBase') ?>" class="wm_input" style="width: 430px" />
		</td>
	</tr>
	<tr>
		<td></td>
		<td style="padding-right: 0px;">
			<div class="wm_safety_info" style="width: 415px">
				Provide URL address where Funambol Data Synchronization Server is running, contact and calendar database names (as treated by SyncML-enabled devices). Default values are card and cal for contacts and calendar respectively.

				<div class="<?php $this->data->PrintInputValue('classEnableMobileSyncError') ?>">
					<br />
					<font color="red">
						This feature is disabled at the moment, because Mcrypt extension is not available in your PHP configuration.
						Please refer to <a href="http://www.php.net/manual/en/mcrypt.installation.php" target="_blank" >this</a> page to learn how to install extension.
					</font>
				</div>
			</div>
			<br />
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

<script type="text/javascript">
if (window.SettingsObjects && SettingsObjects["mobilesync"]) {
	SettingsObjects["mobilesync"].Init();
}
</script>