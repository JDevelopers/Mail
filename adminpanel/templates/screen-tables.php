<?php $screen->WriteTopMenu(); ?>

<div class="wm_contacts" id="main_contacts">
	<div class="wm_contacts_list" id="contacts" >
		<div id="contact_list_div" class="wm_contact_list_div">
			<div id="list_top_search" class="wm_contact_list_div_top" style="border-right: 0px none; border-buttom: 0px none; padding: 8px;">
				<form id="searchform" action="<?php echo AP_INDEX_FILE;?>?search" method="POST" >
	<?php $screen->WriteFilter(); ?>
					<div class="wm_toolbar_search_item" id="search_control">
						<nobr>
							<span>Search:</span>
							<input type="text" id="searchdesc" name="searchdesc" class="wm_search_input" value="<?php echo $screen->GetSearchDesc(); ?>" /><span onclick="document.getElementById('searchform').submit();" class="wm_search_icon_standard" style="background-position: -560px 0px;">&nbsp;</span>
							<!--<img class="wm_menu_big_search_img" src="<?php /*echo $this->AdminFolder().'/';*/ ?>images/menu/search_button_big.gif" onclick="document.getElementById('searchform').submit();"/>-->
						</nobr>
					</div>
				</form>
				<div class="clear"></div>
				<div id="search_desc" style="border-right: 0px none;"><?php echo $screen->GetSearchFullDesc(); ?></div>
			</div>
			<form id="table_form" action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST">
				<input type="hidden" name="mode_name" value="collection">
				<input type="hidden" name="action" id="action" value="">
	<?php $screen->WriteTable(); ?>
			</form>
		</div>
	</div>
	<div class="wm_contacts_view_edit" id="contacts_viewer">
<?php  $screen->WriteCard(); ?>
	</div>
</div>
<div id="lowtoolbar" class="wm_lowtoolbar">
	<span class="wm_lowtoolbar_messages">
<?php $screen->WriteLowToolBar(); ?>
	</span>
</div>
<!--
<table class="wm_hide" id="ps_container">
	<tr>
		<td><div class="wm_inbox_page_switcher_left"></div></td>
		<td class="wm_inbox_page_switcher_pages" id="ps_pages"></td>
		<td><div class="wm_inbox_page_switcher_right"></div></td>
	</tr>
</table>
-->
<script type="text/javascript">
<?php $screen->WriteTableJS(); ?>
var List = new CList();
var Selection;
Init_list("list");
ResizeElements("all");

<?php
	if (isset($_GET['uid']))
	{
		echo 'Selection.CheckLine("'.ap_Utils::ReBuildStringToJavaScript(urlencode($_GET['uid']), '"').'");';
	}
?>

function ViewAddressRecord(id) {
	document.location = "<?php echo AP_INDEX_FILE;?>?mode=edit&uid=" + id;
}
</script>
<?php $screen->WriteInitJS(); ?>
