<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="<?php $this->data->PrintInputValue('inputMode'); ?>_form" <?php $this->data->PrintValue('hideClass_'.$this->data->GetInputValue('inputMode')); ?>>
<input type="hidden" name="form_id" value="<?php $this->data->PrintInputValue('inputMode'); ?>" />
<input type="hidden" name="isTestConnection" id="isTestConnection" value="0" />
<input type="hidden" name="isCreateDb" id="isCreateDb" value="0" />

<table class="wm_admin_center" width="550">
	<tr>
		<td width="200"></td>
		<td><br /></td>
	</tr>
	<tr>
		<td colspan="2">
			<span style="font-size: 14px">Step <?php $this->data->PrintValue('StepCount'); ?>:</span>
			<br />
			<span style="font-size: 18px">Specify Database Settings</span>
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
						<b>Select database engine to use</b>
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
						<b>Enter connection settings</b>
						<br /><br />
						<table id="dbSwitcher">
							<tr>
								<td align="right" valign="top">
									<span id="txtSqlLogin_label">SQL&nbsp;login:</span>
								</td>
								<td>
									<input type="text" class="wm_install_input" name="txtSqlLogin" id="txtSqlLogin" value="<?php $this->data->PrintInputValue('txtSqlLogin') ?>" size="45" />
								</td>
							</tr>
							<tr>
								<td align="right" valign="top">
									<span id="txtSqlPassword_label">SQL&nbsp;password:</span>
								</td>
								<td>
									<input type="password" class="wm_install_input" name="txtSqlPassword" id="txtSqlPassword" value="<?php $this->data->PrintInputValue('txtSqlPassword') ?>" size="45" />
								</td>
							</tr>
							<tr>
								<td align="right" valign="top">
									<span id="txtSqlName_label">Database&nbsp;name:</span>
								</td>
								<td>
									<input type="text" class="wm_install_input" name="txtSqlName" id="txtSqlName" value="<?php $this->data->PrintInputValue('txtSqlName') ?>" size="35" />
									<input type="submit" name="create_db_btn" id="create_db_btn" value="Create" class="wm_create_db_button" />
									<?php 
										if ($this->data->ValueExist('CreateDatabaseInfoMsg'))
										{
											echo '<br /><br />';
											$this->data->PrintValue('CreateDatabaseInfoMsg');
											echo '<br /><br />';	
										}
									?>
								</td>
							</tr>		
							<tr>
								<td align="right" valign="top">
									<span id="txtSqlSrc_label">Host:</span>
									</td>
								<td>
									<input type="text" class="wm_install_input" name="txtSqlSrc" id="txtSqlSrc" value="<?php $this->data->PrintInputValue('txtSqlSrc') ?>" size="45" />
								</td>
							</tr>
							<tr><td colspan="2"><div id="dbMessageDiv" class="wm_install_db_msg_null"><br /></div></td></tr>
							<tr>
								<td align="right">
									<input type="checkbox" value="1" class="wm_checkbox" name="useDSN" id="useDSN" <?php $this->data->PrintCheckedValue('useDSN') ?> />
									<label for="useDSN" id="useDSN_label">ODBC&nbsp;Data&nbsp;source&nbsp;(DSN):</label>
								 </td>
								<td>
									<input type="text" class="wm_install_input" name="txtSqlDsn" id="txtSqlDsn" value="<?php $this->data->PrintInputValue('txtSqlDsn') ?>" size="45" />
								</td>
							</tr>
							<tr>
								<td align="right">
									<input type="checkbox" value="1" class="wm_checkbox" name="useCS" id="useCS" <?php $this->data->PrintCheckedValue('useCS') ?> />
									<label for="useCS" id="useCS_label"><font style="font-size: 12px">ODBC&nbsp;Connection&nbsp;&nbsp;String:</font></label>
								</td>
								<td>
									<input type="text" class="wm_install_input" name="odbcConnectionString" id="odbcConnectionString" value="<?php $this->data->PrintInputValue('odbcConnectionString') ?>" size="45" />
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
								<td valign="top">
									<input type="submit" name="test_btn" id="test_btn" value="Test database" class="wm_test_button" />
								</td>
								<td valign="middle">
									<?php $this->data->PrintValue('TestConnectInfoMsg'); ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr><td colspan="2"><br /></td></tr>
				<tr>
					<td valign="top">
						<b>4.</b>
					</td>
					<td valign="top">
						<b>Specify prefix for table names (optional)</b>
						<br /><br />
						For instance, if you specify prefix as "my_", awm_accounts table will
						be created as "my_awm_accounts". You can leave it empty.
						<br /><br />
						<input type="text" class="wm_install_input" name="prefixString" id="prefixString" value="<?php $this->data->PrintInputValue('txtPrefix') ?>" size="15" />
					</td>
				</tr>
				<tr><td colspan="2"><br /></td></tr>
				<tr>
					<td valign="top">
						<b>5.</b>
					</td>
					<td valign="top">
						<b>Create Database Tables</b><br /><br />If enabled, this installer will create tables required by WebMail Pro. Disable it if you've already created the tables.
						<br /><br />
						<input type="checkbox" class="wm_checkbox" name="chNotCreate" id="chNotCreate" value="1" checked="checked" />
						<label for="chNotCreate">Create Database Tables</label>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2" align="center">
			<?php $this->data->PrintValue('InfoMsg'); ?>
		</td>
	</tr>
	<tr><td colspan="2" align="center"><br />Click Next to apply the settings and proceed.</td></tr>
	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td align="left">
			<input type="button" name="back_btn" id="back_btn" value="Back" class="wm_install_button" style="width: 100px" onclick="javascript:<?php $this->data->PrintValue('onClickBack'); ?>" />
		</td>
		<td align="right">
			<a name="foot"></a>
			<input type="submit" name="submit_btn" id="submit_btn" value="Next" class="wm_install_button" style="width: 100px;" />
		</td>
	</tr>
	<tr><td colspan="2"><br /><br /></td></tr>
</table>
</form>
<script type="text/javascript">
if (window.SettingsObjects && SettingsObjects["db"]) {
	SettingsObjects["db"].InitDB(<?php 
		$this->data->PrintJsValue('isMySQL_JS'); 
		echo ', '; 
		$this->data->PrintJsValue('isMSSQL_JS');
		echo ', '; 
		$this->data->PrintJsValue('isODBC_JS'); 
		?>);
	SettingsObjects["db"].Init();
}
</script>