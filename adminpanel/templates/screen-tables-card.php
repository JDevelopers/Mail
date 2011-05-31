
<div id="wm_contacts_card">
	
	<div class="wm_contacts_card_line1"></div>
	<div class="wm_contacts_card_line2"></div>
	<div class="wm_contacts_card_line3"></div>
	<div class="wm_contacts_card_line4"></div>
	<div class="wm_contacts_card_line5"></div>

	<div class="wm_contacts_card_content">

<div style="float: right; margin: 10px;">
	<img src="images/close-popup.png" class="wm_close_icon" onclick="document.location = '<?php echo AP_INDEX_FILE; ?>?mode=close'" title="Close" alt="X" />
</div>
<div style="padding: 20px;">
	<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="main_form" onsubmit="OnlineLoadInfo(_langSaving); return true;">
		<input type="hidden" name="mode_name" value="<?php $this->WriteMode(); ?>">
		<?php $this->WriteMainText(); ?>
	</form>
</div>

	    </div>
	<div class="wm_contacts_card_line5"></div>
	<div class="wm_contacts_card_line4"></div>
	<div class="wm_contacts_card_line3"></div>
	<div class="wm_contacts_card_line2"></div>
	<div class="wm_contacts_card_line1"></div>
</div>

	<!--
 	<div class="wm_contacts_card_line1"></div>
	<div class="wm_contacts_card_line2"></div>
	<div class="wm_contacts_card_line3"></div>
	<div class="wm_contacts_card_line4"></div>
	<div class="wm_contacts_card_line5"></div>
		<table class="wm_contacts_card_content" id="wm_contacts_card">
		    <tr>
			    <td class="wm_contacts_card_top_left">
				    <div class="wm_contacts_card_corner"></div>
			    </td>
			    <td class="wm_contacts_card_top"></td>
			    <td class="wm_contacts_card_top_right">
				    <div class="wm_contacts_card_corner"></div>
			    </td>
		    </tr>
		    <tr>
			    <td class="wm_contacts_card_left"></td>
			    <td>

			    </td>
			    <td class="wm_contacts_card_right"></td>
		    </tr>
		    <tr>
			    <td class="wm_contacts_card_bottom_left">
				    <div class="wm_contacts_card_corner"></div>
			    </td>
			    <td class="wm_contacts_card_bottom"></td>
			    <td class="wm_contacts_card_bottom_right">
				    <div class="wm_contacts_card_corner"></div>
			    </td>
		    </tr>
	    </table>
	<div class="wm_contacts_card_line5"></div>
	<div class="wm_contacts_card_line4"></div>
	<div class="wm_contacts_card_line3"></div>
	<div class="wm_contacts_card_line2"></div>
	<div class="wm_contacts_card_line1"></div>
//-->
