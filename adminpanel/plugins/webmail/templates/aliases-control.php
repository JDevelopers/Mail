		<tr>
			<td>
				Account Alias:
			</td>
		</tr>
		<tr>
			<td>
				<input type="hidden" name="startID" value="3" />
				<nobr>
					<input type="text" maxlength="100" name="AccountAliasID" id="AccountAliasID" tabindex="17" class="wm_input" style="width: 250px;" />
					&nbsp;<span style="font-size: large;">@<?php $this->data->PrintClearValue('DomainNameAlias'); ?></span>
				</nobr>
			</td>
		</tr>
		<tr>
			<td>
				<input type="button" name="AliasesAdd" value="Add New Alias" id="AccountAliasButton"
					tabindex="18" class="wm_button" onclick="AddValueToList($('AccountAliasID'), $('AliasesListDDL'));" />
			</td>
		</tr>
		<tr>
			<td><br /></td>
		</tr>
		<tr>
			<td>
				  <select size="6" name="AliasesListDDL[]" id="AliasesListDDL" tabindex="19" class="wm_input" style="width: 256px;" multiple="multiple"><?php $this->data->PrintValue('AliasesListDDL'); ?></select>
			</td>
		</tr>
		<tr>
			<td>
				<input type="button" name="AliasesDel" value="Delete Alias" id="AliasesListButton"
					tabindex="20" class="wm_button" onclick="DeleteSelectedFromList($('AliasesListDDL'));" />
			</td>
		</tr>