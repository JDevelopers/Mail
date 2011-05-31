<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="<?php $this->data->PrintInputValue('inputMode'); ?>_form" <?php $this->data->PrintValue('hideClass_'.$this->data->GetInputValue('inputMode')); ?>>
<input type="hidden" name="form_id" value="<?php $this->data->PrintInputValue('inputMode'); ?>" />

<table class="wm_admin_center" width="550">
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2">
			<span style="font-size: 14px">Step <?php $this->data->PrintValue('StepCount'); ?>:</span>
			<br />
			<span style="font-size: 18px">Installation Completed</span>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div class="wm_install_last_div_ok">
				Congratulations! You have successfully installed <?php $this->data->PrintValue('ProductName'); ?>.
				<br /><br />
				Click Exit to be redirected into the Admin Panel where you can set up
				domains and users. 
				<br /><br />
				Once you entered Admin Panel, be sure to DELETE install.php and install.htm files.
			</div>
		</td>
	</tr>	
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2" align="center">
			<?php $this->data->PrintValue('InfoMsg'); ?>
		</td>
	</tr>
	<tr><td colspan="2"><hr size="1" /></td></tr>
	<tr>
		<td align="left">
			<input type="button" name="back_btn" id="back_btn" value="Back" class="wm_install_button" style="width: 100px" onclick="javascript:<?php $this->data->PrintValue('onClickBack'); ?>" />
		</td>
		<td align="right">
			<a name="foot"></a>
			<input type="submit" name="submit_btn" id="submit_btn" value="Exit" class="wm_install_button" style="width: 100px" />
		</td>
	</tr>
	<tr><td colspan="2"><br /><br /></td></tr>
</table>
</form>