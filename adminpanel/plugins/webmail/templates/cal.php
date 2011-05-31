<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="cal_form" <?php $this->data->PrintValue('hideClass_cal') ?>>
<input type="hidden" name="form_id" value="calendar" />
<table class="wm_settings_common" width="550">
	<tr>
		<td width="150"></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="2" class="wm_settings_list_select"><b>Calendar Settings</b></td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td align="right">Default time format: </td>
		<td>
			<input type="radio" name="defTimeFormat" id="defTimeFormat0" value="1" class="wm_checkbox"
			<?php $this->data->PrintCheckedValue('defTimeFormat0') ?>/>&nbsp;<label for="defTimeFormat0">1PM</label>
			&nbsp;&nbsp;&nbsp;
			<input type="radio" name="defTimeFormat" id="defTimeFormat1" value="2" class="wm_checkbox"
			<?php $this->data->PrintCheckedValue('defTimeFormat1') ?>/>&nbsp;<label for="defTimeFormat1">13:00</label>
		</td>
	</tr>

	<tr>
		<td align="right">Default date format: </td>
		<td >
		<select name="defDateFormat" id="defDateFormat" class="wm_input wm_addmargin">
			<option value="1" <?php $this->data->PrintSelectedValue('defDateFormat1') ?>><?php $this->data->PrintClearValue('defDateFormatValue1') ?></option>
			<option value="2" <?php $this->data->PrintSelectedValue('defDateFormat2') ?>><?php $this->data->PrintClearValue('defDateFormatValue2') ?></option>			
			<option value="3" <?php $this->data->PrintSelectedValue('defDateFormat3') ?>><?php $this->data->PrintClearValue('defDateFormatValue3') ?></option>			
			<option value="4" <?php $this->data->PrintSelectedValue('defDateFormat4') ?>><?php $this->data->PrintClearValue('defDateFormatValue4') ?></option>
			<option value="5" <?php $this->data->PrintSelectedValue('defDateFormat5') ?>><?php $this->data->PrintClearValue('defDateFormatValue5') ?></option>
		</select>
		</td>
	</tr>
		
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="showWeekends" id="showWeekends" class="wm_checkbox" value="1"
			<?php $this->data->PrintCheckedValue('showWeekends') ?>/>
			&nbsp;<label for="showWeekends">Show weekends</label>
		</td>
	</tr>
	
	<tr>
		<td align="right">Workday starts: </td>
		<td>
			<select style="width:100px" name="WorkdayStarts" id="WorkdayStarts" class="wm_input wm_addmargin"></select>
			&nbsp;&nbsp;ends:
			<select style="width:100px" name="WorkdayEnds" id="WorkdayEnds" class="wm_input wm_addmargin"></select>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			 <input type="checkbox" name="showWorkDay" id="showWorkDay" class="wm_checkbox" 
			 value="1" <?php $this->data->PrintCheckedValue('showWorkDay') ?>/>
			 &nbsp;<label for="showWorkDay">Show workday</label>
		</td>
	</tr>
	
	<tr>
		<td align="right">Week starts on: </td>
		<td>
			<select name="weekStartsOn" id="weekStartsOn" class="wm_input wm_addmargin">
				<option value="0" <?php $this->data->PrintSelectedValue('weekStartsOn0') ?>>Sunday</option>
				<option value="1" <?php $this->data->PrintSelectedValue('weekStartsOn1') ?>>Monday</option>			
			</select>
		</td>
	</tr>
	<tr>
		<td align="right">Default tab: </td>
		<td>
			 <input type="radio" name="defTab" class="wm_checkbox" id="defTab0" 
			 value="1" <?php $this->data->PrintCheckedValue('defTab1') ?>/>
			 &nbsp;<label for="defTab0">Day</label>&nbsp;
			 <input type="radio" name="defTab" class="wm_checkbox" id="defTab1" 
			 value="2" <?php $this->data->PrintCheckedValue('defTab2') ?>/>
			 &nbsp;<label for="defTab1">Week</label>&nbsp;
			 <input type="radio" name="defTab" class="wm_checkbox" id="defTab2"
			 value="3" <?php $this->data->PrintCheckedValue('defTab3') ?>/>
			 &nbsp;<label for="defTab2">Month</label> 			 			 
		</td>
	</tr>
	<tr>
		<td align="right">Default country: </td>
		<td>
<select style="width:300px" name="defCountry" id="defCountry" class="wm_input wm_addmargin">
<?php $this->data->PrintValue('Country_dat') ?>
</select>	 			 
		</td>
	</tr>
	
	<tr>
		<td align="right">Default time zone: </td>
		<td id="defTimeZoneCont">
			<select style="width: 300px" name="defTimeZone" id="defTimeZone" class="wm_input wm_addmargin"></select>
		</td>
	</tr>
	<tr>
		<td></td>	
		<td>
			<input type="checkbox" name="allTimeZones" id="allTimeZones" class="wm_checkbox" value="1" 
				<?php $this->data->PrintCheckedValue('allTimeZones') ?>/>
			&nbsp;<label for="allTimeZones">All time zones</label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="allowReminder" id="allowReminder" class="wm_checkbox" value="1"
				<?php $this->data->PrintCheckedValue('allowReminder') ?>/>
			&nbsp;<label for="allowReminder">Allow reminders</label>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td colspan="2" align="right">
			<input type="submit" name="submit_btn" class="wm_button" value="Save" style="width: 100px" />
		</td>
	</tr>
</table>
</form>
<script type="text/javascript">
var admSet = new ASSettings();
admSet.Init(<?php 
$this->data->PrintIntValue('Cal_WorkdayStarts');
echo ', '; 
$this->data->PrintIntValue('Cal_WorkdayEnds');
echo ', '; 
$this->data->PrintIntValue('Cal_DefaultTimeFormat');
echo ', '; 
$this->data->PrintIntValue('Cal_DefaultTimeZone');
?>);
admSet.InitLang("CheckWorkdayTimeError", "<?php $this->data->PrintJsValue('CheckWorkdayTimeError') ?>");
admSet.ShowError = OnlineMsgError;
</script>