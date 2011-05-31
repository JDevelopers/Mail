<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="<?php $this->data->PrintInputValue('inputMode'); ?>_form" <?php $this->data->PrintValue('hideClass_'.$this->data->GetInputValue('inputMode')); ?>>
<input type="hidden" name="form_id" value="<?php $this->data->PrintInputValue('inputMode'); ?>" />
<input type="hidden" name="isTestConnection" id="isTestConnection" value="0" />

<table class="wm_admin_center" width="550">
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2">
			<span style="font-size: 14px">Step <?php $this->data->PrintValue('StepCount'); ?>:</span>
			<br />
			<span style="font-size: 18px">Check Connection with E-mail Server</span>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2">
			Here you can test the connectivity with your e-mail server (optional).
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2">
			<table>
				<tr>
					<td>E-mail server host:</td>
					<td>
						<input type="text" name="txtHost" id="txtHost" size="25" class="wm_install_input wm_addmargin" value="<?php $this->data->PrintInputValue('txtHost') ?>" />
						<?php $this->data->PrintValue('ssl_start_comment'); ?>
						
						&nbsp;<input type="checkbox" class="wm_checkbox" name="chSSL" id="chSSL" value="1" />&nbsp;<label for="chSSL" class="">SSL</label>
						<?php $this->data->PrintValue('ssl_end_comment'); ?>
						
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="checkbox" class="wm_checkbox" name="chSMTP" id="chSMTP" value="1" <?php $this->data->PrintCheckedValue('chSMTP') ?> />&nbsp;<label for="chSMTP" class="">SMTP</label>
						&nbsp;
						<input type="checkbox" class="wm_checkbox" name="chPOP3" id="chPOP3" value="1" <?php $this->data->PrintCheckedValue('chPOP3') ?> />&nbsp;<label for="chPOP3" class="">POP3</label>
						&nbsp;
						<input type="checkbox" class="wm_checkbox" name="chIMAP4" id="chIMAP4" value="1" <?php $this->data->PrintCheckedValue('chIMAP4') ?> />&nbsp;<label for="chIMAP4" class="">IMAP4</label>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="submit" name="test_btn" id="test_btn" class="wm_test_button wm_addmargin" value="Test connection" style="width: 120px" />
					</td>
				</tr>
			</table>
		</td>
	</tr>	
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2" align="left">
			<?php $this->data->PrintValue('InfoCheckMsg'); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<?php $this->data->PrintValue('InfoMsg'); ?>
		</td>
	</tr>
	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td align="left">
			<input type="button" name="back_btn" id="back_btn" value="Back" class="wm_install_button" style="width: 100px"
				onclick="javascript:<?php $this->data->PrintValue('onClickBack'); ?>" />
		</td>
		<td align="right">
			<a name="foot"></a>
			<input type="submit" name="submit_btn" id="submit_btn" value="Next" class="wm_install_button" style="width: 100px"
				onclick="javascript:<?php $this->data->PrintValue('onClickNext'); ?>" />
		</td>
	</tr>
	<tr><td colspan="2"><br /><br /></td></tr>
</table>
</form>
<script type="text/javascript">
if (window.SettingsObjects && SettingsObjects["socket"]) {
	SettingsObjects["socket"].Init();
}
</script>