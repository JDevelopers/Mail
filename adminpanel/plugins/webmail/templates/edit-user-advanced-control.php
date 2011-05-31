		<tr>
			<td align="right">Messages per page:</td>
			<td>
				<input name="txtMessagesPerPage" type="text" id="txtMessagesPerPage" class="wm_input" maxlength="2" size="2" value="<?php $this->data->PrintIntValue('txtMessagesPerPage') ?>" />
			</td>
		</tr>
		<tr>
			<td align="right">Contacts per page:</td>
			<td>
				<input name="txtContactsPerPage" type="text" id="txtContactsPerPage" class="wm_input" maxlength="2" size="2" value="<?php $this->data->PrintIntValue('txtContactsPerPage') ?>" />
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input name="intDisableRichEditor" type="checkbox" id="intDisableRichEditor" class="wm_checkbox" value="1" <?php $this->data->PrintCheckedValue('intDisableRichEditor') ?>/>
				<label for="intDisableRichEditor">
					Disable rich-text editor
				</label>
			</td>
		</tr>
		<tr>
			<td align="right">Skin:</td>
			<td>
				<select name="txtDefaultSkin" id="txtDefaultSkin" class="wm_input wm_addmargin">
<?php $this->data->PrintValue('txtDefaultSkin') ?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right">Default charset:</td>
			<td>
				<select name="txtDefaultUserCharset" id="txtDefaultUserCharset" class="wm_input wm_addmargin">
<?php $this->data->PrintValue('txtDefaultUserCharset') ?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right">Default time offset:</td>
			<td>
				<select name="txtDefaultTimeZone" id="txtDefaultTimeZone" class="wm_input wm_addmargin">
<?php $this->data->PrintValue('txtDefaultTimeZone') ?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right">Default language:</td>
			<td>
				<select name="txtDefaultLanguage" id="txtDefaultLanguage" class="wm_input wm_addmargin">
<?php $this->data->PrintValue('txtDefaultLanguage') ?>
				</select>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input name="intMessageListWithPreviewPane" type="checkbox" id="intMessageListWithPreviewPane" class="wm_checkbox" value="1" <?php $this->data->PrintCheckedValue('intMessageListWithPreviewPane') ?>/>
				<label for="intMessageListWithPreviewPane">
					The message pane is to the right of the message list, rather than below
				</label>
			</td>

		</tr>
		<tr>
			<td></td>
			<td>
				<input name="intAlwaysShowPictures" type="checkbox" id="intAlwaysShowPictures" class="wm_checkbox" value="1" <?php $this->data->PrintCheckedValue('intAlwaysShowPictures') ?>/>
				<label for="intAlwaysShowPictures">
					Always show pictures in messages
				</label>
			</td>
		</tr>