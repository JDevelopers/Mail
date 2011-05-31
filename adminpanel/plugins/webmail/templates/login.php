<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="login_form" <?php $this->data->PrintValue('hideClass_login') ?> onsubmit="OnlineLoadInfo(_langSaving); return true;">
<input type="hidden" name="form_id" value="login" />
<input type="hidden" name="filter_host" value="<?php $this->data->PrintInputValue('txtFilterHost') ?>" />

<table class="wm_settings_common" width="550">
	<tr>
		<td width="50"></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="2" style="padding: 0px;">
			<div class="wm_safety_info">
				<b>Standard login panel, Hide login field, Hide email field</b>	are sorts of WebMail login panel.
				Choice depends on your mail server configuration and your requirements.
			</div>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2" class="wm_settings_list_select">
			<b>Login Page Settings</b>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<br />
			<br />
			<div id="hideLoginDiv1">
				<div class="back" style="margin: 0px 2px; height: 1px; border: 0px; line-height: 1px; $height: auto;">&nbsp;</div>
				<div class="back" style="margin: 0px 1px; height: 1px; border: 0px; line-height: 1px; $height: auto;">&nbsp;</div>
				<table class="back" style="width: 100%" cellpadding="0" cellspacing="0">
					<tr>
						<td style="width: 40px;" valign="top" align="right">
							<input type="radio" name="hideLoginRadionButton" id="hideLoginRadionButton1" value="0" class="wm_checkbox" <?php $this->data->PrintCheckedValue('hideLoginRadionButton1') ?> />
						</td>
						<td valign="top" align="left">
							<label for="hideLoginRadionButton1">Standard login panel</label>
							<br />
							<br />
						</td>
					</tr>
				</table>
				<div class="back" style="margin: 0px 1px; height: 1px; border: 0px; line-height: 1px; $height: auto;">&nbsp;</div>
				<div class="back" style="margin: 0px 2px; height: 1px; border: 0px; line-height: 1px; $height: auto;">&nbsp;</div>
			</div>
			
			<div id="hideLoginDiv2">
				<div class="back" style="margin: 0px 2px; height: 1px; border: 0px; line-height: 1px; $height: auto;">&nbsp;</div>
				<div class="back" style="margin: 0px 1px; height: 1px; border: 0px; line-height: 1px; $height: auto;">&nbsp;</div>
				<table class="back" style="width: 100%" cellpadding="0" cellspacing="0">
					<tr>
						<td style="width: 40px;" valign="top" align="right">
							<input type="radio" name="hideLoginRadionButton" id="hideLoginRadionButton2" value="1" class="wm_checkbox" <?php $this->data->PrintCheckedValue('hideLoginRadionButton2') ?> />
						</td>
						<td valign="top" align="left">
							<label for="hideLoginRadionButton2">Hide login field</label>
							<br /><br />
							<select name="hideLoginSelect" id="hideLoginSelect" class="wm_input">
								<option value="1" <?php $this->data->PrintSelectedValue('hideLoginSelect1') ?>>Use Email as Login</option>
								<option value="0" <?php $this->data->PrintSelectedValue('hideLoginSelect0') ?>>Use Account-name as Login</option>
							</select>
							<br />
							<br />
						</td>
					</tr>
				</table>
				<div class= "back" style="margin: 0px 1px; height: 1px; border: 0px; line-height: 1px; $height: auto;">&nbsp;</div>
				<div class= "back" style="margin: 0px 2px; height: 1px; border: 0px; line-height: 1px; $height: auto;">&nbsp;</div>
			</div>
		
			<div id="hideLoginDiv3">
				<div class="back" style="margin: 0px 2px; height: 1px; border: 0px; line-height: 1px; $height: auto;">&nbsp;</div>
				<div class="back" style="margin: 0px 1px; height: 1px; border: 0px; line-height: 1px; $height: auto;">&nbsp;</div>
				<table class="back" style="width: 100%" cellpadding="0" cellspacing="0">
					<tr>
						<td style="width: 40px;" valign="top" align="right">
							<input type="radio" name="hideLoginRadionButton" value="2" id="hideLoginRadionButton3" class="wm_checkbox" <?php $this->data->PrintCheckedValue('hideLoginRadionButton3') ?> />
						</td>
						<td valign="top" align="left">
							<label for="hideLoginRadionButton3">Hide email field</label>
							<br /><br />
							<input type="hidden" value="<?php $this->data->PrintInputValue('intDomainsExistValue') ?>" id="intDomainsExistValue" />
							<input type="checkbox" name="intDisplayDomainAfterLoginField" value="1" id="intDisplayDomainAfterLoginField" class="wm_checkbox" <?php $this->data->PrintCheckedValue('intDisplayDomainAfterLoginField') ?> />
							&nbsp;<label for="intDisplayDomainAfterLoginField" id="intDisplayDomainAfterLoginField_label">Display domain after login field as:</label>
							<br /><br />
							&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="intDomainDisplayType" value="1" id="intDomainDisplayType1" class="wm_checkbox" <?php $this->data->PrintCheckedValue('intDomainDisplayType1') ?> />
							&nbsp;<label for="intDomainDisplayType1" id="intDomainDisplayType1_label">Multiple domains selection (available in Domains section).</label>
							<br />
							&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="intDomainDisplayType" value="0" id="intDomainDisplayType2" class="wm_checkbox" <?php $this->data->PrintCheckedValue('intDomainDisplayType2') ?> />
							&nbsp;<label for="intDomainDisplayType2" id="intDomainDisplayType2_label">Single domain</label>
							&nbsp;<input type="text" name="txtUseDomain" value="<?php $this->data->PrintInputValue('txtUseDomain') ?>" id="txtUseDomain" class="wm_input" size="20" />
							<br /><br />
							<input type="checkbox" name="intLoginAsConcatination" id="intLoginAsConcatination" value="1" class="wm_checkbox" <?php $this->data->PrintCheckedValue('intLoginAsConcatination') ?> />
							&nbsp;<label for="intLoginAsConcatination" id="intLoginAsConcatination_label">Login is formed as concatenation of "Login" field + "@" + domain</label>
							<br />
							<br />
						</td>
					</tr>
				</table>
				<div class= "back" style="margin: 0px 1px; height: 1px; border: 0px; line-height: 1px; $height: auto;">&nbsp;</div>
				<div class= "back" style="margin: 0px 2px; height: 1px; border: 0px; line-height: 1px; $height: auto;">&nbsp;</div>
			</div>
			
			<div style="padding-top: 10px;">
				<table style="width: 100%" cellpadding="0" cellspacing="0">
					<tr>
						<td style="width: 40px;" valign="top" align="right">
							<input type="checkbox" class="wm_checkbox" value="1" name="intAllowLangOnLogin" id="intAllowLangOnLogin" <?php $this->data->PrintCheckedValue('intAllowLangOnLogin') ?> />
						</td>
						<td valign="top" align="left">
							<label for="intAllowLangOnLogin">Allow choosing language on login</label>
						</td>
					</tr>
				</table>
			</div>
			
			<div style="padding-top: 10px;">
				<table cellpadding="0" cellspacing="0" style="width: 100%">
					<tr>
						<td style="width: 45px;"></td>
						<td class="wm_safety_info" style="padding: 0px;">
							If enabled, it will be possible to select language on login page.
						</td>
					</tr>
				</table>
			</div>
			
			<br />
				
			<div style="padding-top: 10px;">
				<table style="width: 100%" cellpadding="0" cellspacing="0">
					<tr>
						<td style="width: 40px;" valign="top" align="right">
							<input type="checkbox" class="wm_checkbox" value="1" name="intAllowAdvancedLogin" id="intAllowAdvancedLogin" <?php $this->data->PrintCheckedValue('intAllowAdvancedLogin') ?> />
						</td>
						<td valign="top" align="left">
							<label for="intAllowAdvancedLogin">Allow advanced login</label>
						</td>
					</tr>
				</table>
			</div>
			
			<div style="padding-top: 10px;">
				<table cellpadding="0" cellspacing="0" style="width: 100%">
					<tr>
						<td style="width: 45px;"></td>
						<td class="wm_safety_info" style="padding: 0px;">
							Allows changing SMTP and POP3/IMAP servers addresses, <br />
							port numbers, <nobr>enabling/disabling</nobr> SMTP authentication from login panel.
						</td>
					</tr>
				</table>
			</div>
			
			<br />
			
			<div style="padding-top: 10px;">
				<table style="width: 100%" cellpadding="0" cellspacing="0">
					<tr>
						<td style="width: 40px;" valign="top" align="right">
							<input type="checkbox" value="1" name="intAutomaticHideLogin" class="wm_checkbox" id="intAutomaticHideLogin" <?php $this->data->PrintCheckedValue('intAutomaticHideLogin') ?> />
						</td>
						<td valign="top" align="left">
							<label for="intAutomaticHideLogin">Automatically detect and correct if user inputs e-mail instead of account-name</label>
						</td>
					</tr>
				</table>
			</div>
			
			<div style="padding-top: 10px;">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td style="width: 45px;"></td>
						<td class="wm_safety_info" style="padding: 0px;">
							If a user typed a full e-mail address instead of just an account name during logging in, it'll be automatically
							corrected. Makes sense only with Standard login panel and Hide email field modes.
						</td>
					</tr>
				</table>
			</div>

			<br />

			<div style="padding-top: 10px;">
				<table style="width: 100%" cellpadding="0" cellspacing="0">
					<tr>
						<td style="width: 40px;" valign="top" align="right">
							<input type="checkbox" value="1" name="intUseCaptcha" class="wm_checkbox" id="intUseCaptcha" <?php $this->data->PrintCheckedValue('intUseCaptcha') ?> <?php $this->data->PrintDisabledValue('intUseCaptchaDisabled') ?> />
						</td>
						<td valign="top" align="left">
							<label for="intUseCaptcha" style="<?php $this->data->PrintInputValue('styleUseCaptchaLabel') ?>">Enable CAPTCHA</label>
						</td>
					</tr>
				</table>
			</div>

			<div style="padding-top: 10px;">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td style="width: 45px;"></td>
						<td class="wm_safety_info" style="padding: 0px;">
							<a href="http://en.wikipedia.org/wiki/Captcha" target="_blank">CAPTCHA</a> 
							protects from automated password guessing.
							CAPTCHA appears on 3-rd consequent incorrect login attempt by a particular user
							<div class="<?php $this->data->PrintInputValue('classUseCaptchaError') ?>">
								<br />
								<font color="red">
									This feature is disabled at the moment, because GD2 extension is not available in your PHP configuration.
									Please refer to <a href="http://www.php.net/manual/en/image.installation.php" target="_blank" >this</a> page to learn how to install extension.
								</font>
							</div>
						</td>
					</tr>
				</table>
			</div>

			<br />
			
			<div>
				<hr size="1">
			</div>
			<div style="padding: 10px 0px; text-align: right">
				<input type="submit" name="submit" value="Save" class="wm_button" style="width: 100px" />
				<?php $this->data->PrintValue('btnSubmitReset') ?>
			</div>
		</td>
	</tr>
</table>
</form>