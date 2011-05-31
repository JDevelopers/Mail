<?php

	if (!isset($this)) exit();
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo AP_META_CONTENT_TYPE; ?>
	<title><?php $this->Title(); ?></title>
	<script type="text/javascript" src="./js/browser.js<?php echo '?'.$this->ClearAdminVersion(); ?>"></script>
	<script type="text/javascript">
		var Browser = new CBrowser();
		var _apPath = "<?php echo ap_Utils::ReBuildStringToJavaScript($this->AdminFolder(), '"'); ?>";
		var _langSaving = "<?php echo ap_Utils::ReBuildStringToJavaScript(AP_LANG_SAVING, '"'); ?>";
		function ResizeElements(mode) { return true; }
		function GlobalInit() { return true; }
	</script>
<?php 
$this->TopJS();
$this->TopStyles();
?>
</head>
<body onresize="ResizeElements('All');" onload="GlobalInit();">
	<div class="<?php $this->ContentClass(); ?>" align="center">
		<div class="wm_logo" id="logo"></div>
<?php 
$this->MainError();
$this->TopMenu(); 
?>
		<div style="background-color:#fff;">
<?php $this->Main(); ?>
		</div>
<?php $this->Copyright(); ?>
	</div>
</body>
</html><?php $this->Info();