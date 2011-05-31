<?php
if (!isset($this))
{
	if (!defined('ERROR_TEML_DESC')) 
	{
		define('ERROR_TEML_DESC', 'Admin Panel is not configured properly.');
	}
	exit(ERROR_TEML_DESC);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo AP_META_CONTENT_TYPE; ?>
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<title>Error</title>
<?php
$this->TopJS();
$this->TopStyles(); 
?>
</head>
<body>
<div align="center" id="content" class="<?php $this->ContentClass(); ?>">
	<div class="wm_logo" id="logo"></div>
	<div class="wm_login_error">
		<div class="wm_login_error_icon"></div>
		<div class="wm_login_error_message" id="login_error_message"><?php echo ERROR_TEML_DESC; ?></div>
	</div>
<?php $this->Copyright(); ?>
</div>
</body>
</html>