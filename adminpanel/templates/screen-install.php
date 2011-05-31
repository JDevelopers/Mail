<table class="wm_settings">
	<tr>
		<td class="wm_install_nav" id="settings_nav" valign="top" align="left">
			<br />
						
<?php $screen->WriteMenu(); ?>

			<br />
		</td>
		<td class="wm_settings_cont" valign="top" id="center_tables">
		
<?php $screen->Main(); ?>


		</td>
	</tr>
</table>
<?php 
$screen->WriteJS();
$screen->WriteInitJS();
?>