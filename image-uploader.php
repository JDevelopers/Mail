<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

	$imageUrl = isset($_GET['image_file']) ? 'http://favicon.yandex.net/favicon/www.softkey.ru' : '';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<script type="text/javascript">
	function InsertImage(url) {
		if (url == '') return;
		window.opener.InsertImageHandler(url);
		window.close();
	}
	
	InsertImage('<?php echo $imageUrl; ?>');
</script>
<body>
    <form method="post" action="image-uploader.php">
		<input type="file" name="image_file" />
		<input type="submit" value="OK" />
		<input type="button" value="Cancel" onclick="window.close();" />
    </form>
</body>
</html>
