<input name="userType" type="hidden" value="mlist" />
<table>
	<tr>
		<td align="left">
			<span style="font-size: large;">Create Mail List</span>
		</td>
	</tr>
</table>
<table border="0" class="wm_contacts_view">
	<tr>
		<td align="right">
			Username:
		</td>
		<td align="left" colspan="2">
			<nobr>
				<input name="txtXmailLogin" type="text" id="txtXmailLogin" value="<?php $this->data->PrintInputValue('UserLogin'); ?>"
					class="wm_input" style="width: 150px" maxlength="100" />
					&nbsp;<span style="font-size: large;">@<?php $this->data->PrintClearValue('DomainName'); ?></span>
			</nobr>
		</td>
	</tr>
</table>
<br /><hr />
<div align="right">
	<input type="submit" class="wm_button" style="width: 100px;" value="Create">
</div>
<script type="text/javascript">
	Validator.RegisterAllowUserLoginSymbols($('txtXmailLogin'));
</script>