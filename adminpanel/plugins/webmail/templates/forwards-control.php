		<tr>
			<td>
				Account Forwards:
			</td>
		</tr>
		<tr>
			<td>
				<input type="hidden" name="startID" value="4" />
				<nobr>
					<input type="text" maxlength="100" name="AccountForwardID" id="AccountForwardID" tabindex="17" class="wm_input" style="width: 250px;" />
				</nobr>
			</td>
		</tr>
		<tr>
			<td>
				<input type="button" name="ForwardAdd" value="Add New Forward" id="AccountForwardButton"
					tabindex="18" class="wm_button" onclick="AddForwardEmailToList();" />
			</td>
		</tr>
		<tr>
			<td><br /></td>
		</tr>
		<tr>
			<td>
				  <select size="6" name="ForwardsListDDL[]" id="ForwardsListDDL" tabindex="19" class="wm_input" style="width: 256px;" multiple="multiple"><?php $this->data->PrintValue('ForwardsListDDL'); ?></select>
			</td>
		</tr>
		<tr>
			<td>
				<input type="button" name="ForwardDel" value="Delete Forward" id="ForwardListButton"
					tabindex="20" class="wm_button" onclick="DeleteSelectedFromList($('ForwardsListDDL'));" />
			</td>
		</tr>