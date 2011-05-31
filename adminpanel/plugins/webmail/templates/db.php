<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="db_form" <?php $this->data->PrintValue('hideClass_db') ?> onsubmit="OnlineLoadInfo(_langSaving); return true;">
<input type="hidden" name="form_id" value="db" />
<input type="hidden" name="isTestConnection" id="isTestConnection" value="0" />

<table class="wm_settings_common" width="550">
	<tr>
		<td width="200"></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="2" class="wm_settings_list_select">
			<b>Database Settings</b>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2" align="left">
			<table>
				<tr>
					<td valign="top">
						<b>1.</b>
					</td>
					<td valign="top">
						<b>Database engine</b>
						<br /><br />
						<input type="radio" class="wm_checkbox" name="intDbType" id="intDbType1" value="<?php $this->data->PrintInputValue('intDbTypeValue1') ?>" 
							<?php $this->data->PrintCheckedValue('intDbType1') ?> />&nbsp;<label id="intDbType1_label"
							for="intDbType1"><font style="font-size: 12px">MySQL</font></label>
						<br />
						<input type="radio" class="wm_checkbox" name="intDbType" id="intDbType0" value="<?php $this->data->PrintInputValue('intDbTypeValue0') ?>" 
							<?php $this->data->PrintCheckedValue('intDbType0') ?> />&nbsp;<label id="intDbType0_label"
							for="intDbType0"><font style="font-size: 12px">MS&nbsp;SQL</font></label>
					</td>
				</tr>
				<tr><td colspan="2"><br /></td></tr>
				<tr>
					<td valign="top">
						<b>2.</b>
					</td>
					<td valign="top">
						<b>Connection settings</b>
						<br /><br />
						<table id="dbSwitcher">
							<tr>
								<td align="right">
									<span id="txtSqlLogin_label">SQL&nbsp;login:</span>
								</td>
								<td>
									<input type="text" class="wm_input" name="txtSqlLogin" id="txtSqlLogin" value="<?php $this->data->PrintInputValue('txtSqlLogin') ?>" size="45" />
								</td>
							</tr>
							<tr>
								<td align="right">
									<span id="txtSqlPassword_label">SQL&nbsp;password:</span>
								</td>
								<td>
									<input type="password" class="wm_input" name="txtSqlPassword" id="txtSqlPassword" value="<?php $this->data->PrintInputValue('txtSqlPassword') ?>" size="45" />
								</td>
							</tr>
							<tr>
								<td align="right">
									<span id="txtSqlName_label">Database&nbsp;name:</span>
								</td>
								<td>
									<input type="text" class="wm_input" name="txtSqlName" id="txtSqlName" value="<?php $this->data->PrintInputValue('txtSqlName') ?>" size="45" />
								</td>
							</tr>		
							<tr>
								<td align="right">
									<span id="txtSqlSrc_label">Host:</span>
									</td>
								<td>
									<input type="text" class="wm_input" name="txtSqlSrc" id="txtSqlSrc" value="<?php $this->data->PrintInputValue('txtSqlSrc') ?>" size="45" />
								</td>
							</tr>
							<tr><td colspan="2"><div id="dbMessageDiv" class="wm_install_db_msg_null"><br /></div></td></tr>
							<tr>
								<td align="right">
									<input type="checkbox" value="1" class="wm_checkbox" name="useDSN" id="useDSN" <?php $this->data->PrintCheckedValue('useDSN') ?> />
									<label for="useDSN" id="useDSN_label">ODBC&nbsp;Data&nbsp;source&nbsp;(DSN):</label>
								 </td>
								<td>
									<input type="text" class="wm_input" name="txtSqlDsn" id="txtSqlDsn" value="<?php $this->data->PrintInputValue('txtSqlDsn') ?>" size="45" />
								</td>
							</tr>
							<tr>
								<td align="right">
									<input type="checkbox" value="1" class="wm_checkbox" name="useCS" id="useCS" <?php $this->data->PrintCheckedValue('useCS') ?> />
									<label for="useCS" id="useCS_label"><font style="font-size: 12px">ODBC&nbsp;Connection&nbsp;&nbsp;String:</font></label>
								</td>
								<td>
									<input type="text" class="wm_input" name="odbcConnectionString" id="odbcConnectionString" value="<?php $this->data->PrintInputValue('odbcConnectionString') ?>" size="45" />
								</td>
							</tr>
						</table>						
					</td>
				</tr>
				<tr><td colspan="2"><br /></td></tr>
				<tr>
					<td valign="top">
						<b>3.</b>
					</td>
					<td valign="top">
						<b>Test database connectivity to check if the specified settings are correct (recommended)</b>
						<br /><br />
						<table border="0" cellpadding="0" cellspacing="0">
							<tr>	
								<td valign="top" colspan="2">
									<input type="submit" name="test_btn" id="test_btn" value="Test connection" class="wm_button" style="font-size: 11px;" />
									<input type="button" name="create_btn" id="create_btn" value="Create tables" class="wm_button" style="font-size: 11px;" onclick="PopUpWindow('<?php echo AP_INDEX_FILE; ?>?mode=pop&type=db&action=create');" />
									<input type="button" name="update_btn" id="update_btn" value="Update" class="wm_button" style="font-size: 11px;" onclick="PopUpWindow('<?php echo AP_INDEX_FILE; ?>?mode=pop&type=db&action=update');" />
									<?php $this->data->PrintValue('txtCreateDropDb') ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td colspan="2" align="right">
			<input type="submit" name="submit_btn" value="Save" class="wm_button" style="width: 100px" />
		</td>
	</tr>
</table>
</form>
<script type="text/javascript">
if (window.SettingsObjects && SettingsObjects["db"]) {
	SettingsObjects["db"].InitDB(<?php 
$this->data->PrintJsValue('isMySQL_JS') ?>, <?php 
$this->data->PrintJsValue('isMSSQL_JS') ?>, <?php 
$this->data->PrintJsValue('isODBC_JS') ?>);
}
</script>