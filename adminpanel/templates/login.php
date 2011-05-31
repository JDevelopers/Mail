<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html>
<head>
	<title><?php $this->Title(); ?></title>
<?php

$this->TopJS();
$this->TopStyles();

$AdmloginInput = $AdmpasswordInput = '';
if (strlen(AP_DEMO_LOGIN) > 0)
{
	$AdmloginInput = AP_DEMO_LOGIN;
	$AdmpasswordInput = AP_DUMMYPASSWORD;
}
?>
</head>
<body style="background-color: #e5e5e5" onload="var login=document.getElementById('loginId'); if (login) {login.focus();};" >
	<div align="center" class="wm_content">
		<div id="login_screen" class="wm_login">
			<form action="<?php echo AP_INDEX_FILE; ?>?login" method="post">
				<div align="center" class="<?php echo $this->ContentClass(); ?>">
					<div class="wm_logo" id="logo"></div>
					<?php echo ap_Utils::TakePhrase('LOGIN_FORM_ERROR_MESS'); ?>
					<div class="wm_login">
						<div class="a top"></div>
						<div class="b top"></div>
						<div class="login_table" style="margin: 0px;">
							<div class="wm_login_header">Administration Login</div>
							<div class="wm_login_content">
								<table id="login_table" border="0" cellspacing="0" cellpadding="10">
								<tr>
									<td class="wm_title" style="font-size: 12px; width: 70px">Login:</td>
									<td>
										<input class="wm_input" size="20" type="text" id="loginId" name="AdmloginInput"
											style="width: 99%; font-size: 16px;"
											onfocus="this.style.background = '#FFF9B2';"
											onblur="this.style.background = 'white';"
											value="<?php echo $AdmloginInput; ?>" />
									</td>
								</tr>
								<tr>
									<td class="wm_title" style="font-size: 12px; width: 70px">Password:</td>
									<td>
										<input class="wm_input" type="password" size="20" id="passwordId" name="AdmpasswordInput"
											style="width: 99%; font-size: 16px;" 
											onfocus="this.style.background = '#FFF9B2';"
											onblur="this.style.background = 'white';"
											value="<?php echo $AdmpasswordInput; ?>"/>
									</td>
								</tr>
								<tr>
									<td align="right" colspan="2">
										<span class="wm_login_button">
											<input class="wm_button" type="submit" name="enter" value="Login" />
										</span>
									</td>
								</tr>
							</table>
							</div>
						</div>
						<div class="b"></div>
						<div class="a"></div>
					</div>
<?php if (strlen(AP_DEMO_LOGIN) > 0): ?>
					<div class="info" id="demo_info" dir="ltr">
						<div class="demo_note">
							This is a demo version of administrative interface. <br />For WebMail Pro demo interface, click <a href="..">here</a>.
						</div>
					</div>
<?php endif; ?>
				</div>
			</form>
		</div>
	</div>
	<?php $this->Copyright(true); ?>
</body>
</html>