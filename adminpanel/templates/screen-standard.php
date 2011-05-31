<div class="wm_settings">
	<div class="wm_settings_row"><!--wm_settings_row_without_menu-->
		<div class="wm_settings_cont" style="height: auto;">
			<div class="wm_contacts" id="main_contacts">
				<table class="wm_settings" id="settings_main" style="border-top: 0px solid #CCCCCC;">
					<tr>
						<!--<td class="wm_settings_nav" id="settings_nav" valign="top" align="left">
							<br />
				<?php
					//$screen->WriteFilter();
					//$screen->WriteMenu();
				?>
							<br/>
						</td>-->
						<td class="wm_settings_cont" valign="top" id="center_tables">
						
				<?php $screen->Main(); ?>


						</td>
					</tr>
				</table>
			</div>
		</div>
		<div id="settings_nav" class="wm_settings_nav" style="height: auto;">
			<?php
				$screen->WriteFilter();
				$screen->WriteMenu();
			?>
		</div>
		<div class="clear">
		</div>
	</div>
</div>
<?php 
$screen->WriteJS();
$screen->WriteInitJS();
?>