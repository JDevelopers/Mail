<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="<?php $this->data->PrintInputValue('inputMode'); ?>_form" <?php $this->data->PrintValue('hideClass_'.$this->data->GetInputValue('inputMode')); ?>>
<input type="hidden" name="form_id" value="<?php $this->data->PrintInputValue('inputMode'); ?>" />

<table class="wm_admin_center" width="550">
	<tr>
		<td width="200"></td>
		<td><br /></td>
	</tr>
	<tr>
		<td colspan="2">
			<span style="font-size: 14px">Step <?php $this->data->PrintValue('StepCount'); ?>:</span>
			<br />
			<span style="font-size: 18px">Mobile sync</span>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td align="right"></td>
		<td>
			<input name="chEnableMobileSync" id="chEnableMobileSync" type="checkbox" class="wm_install_checkbox"
				value="1" <?php $this->data->PrintCheckedValue('chEnableMobileSync') ?> />
			<label for="chEnableMobileSync">Enable mobile sync</label>
		</td>
	</tr>
	
	<tr>
		<td align="right">
			<span id="txtMobileSyncUrl_label">Mobile sync URL:</span>
		</td>
		<td>
			<input name="txtMobileSyncUrl" id="txtMobileSyncUrl" type="text" class="wm_install_input" size="50"
				value="<?php $this->data->PrintInputValue('txtMobileSyncUrl') ?>" />
		</td>
	</tr>

	<tr>
		<td align="right">
			<span id="txtMobileSyncContactDatabase_label">Mobile sync contact database:</span>
		</td>
		<td>
			<input name="txtMobileSyncContactDatabase" id="txtMobileSyncContactDatabase" type="text" class="wm_install_input" size="20"
				value="<?php $this->data->PrintInputValue('txtMobileSyncContactDatabase'); ?>" />
		</td>
	</tr>

	<tr>
		<td align="right">
			<span id="txtMobileSyncCalendarDatabase_label">Mobile sync calendar database:</span>
		</td>
		<td>
			<input name="txtMobileSyncCalendarDatabase" id="txtMobileSyncCalendarDatabase" type="text" class="wm_install_input" size="20"
				value="<?php $this->data->PrintInputValue('txtMobileSyncCalendarDatabase'); ?>" />
		</td>
	</tr>
	
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2" align="center">
			<?php $this->data->PrintValue('InfoMsg'); ?>
		</td>
	</tr>
	<span class="<?php $this->data->PrintInputValue('classShowWindows'); ?>">
		windows
	</span>
	<span class="<?php $this->data->PrintInputValue('classShowLinux'); ?>">
		linux
	</span>

	<tr>
		<td colspan="2" class="wm_install_note_td">
			<b>TODO</b>
			<br />
			<br />

In order to provide Mobile Synchronization you'll need to install Funambol Data Synchronization Server.
<br /><br />

You can download Funambol server from here:
<br />
<span class="<?php $this->data->PrintInputValue('classShowWindows'); ?>">
<br /><b>Windows</b><br />
<!-- https://www.forge.funambol.org/servlets/OCNDirector?id=V85FUNSERVWIN -->
<a href="http://www.funambol.com/opensource/download_response.php?file_id=funambol-8.5.2.exe">Funambol Server for Wndows</a>
<br /><b>Windows 64</b><br />
<!-- https://www.forge.funambol.org/servlets/OCNDirector?id=V85FUNSERVWIN64 -->
<a href="http://www.funambol.com/opensource/download_response.php?file_id=funambol-8.5.2-x64.exe">Funambol Server for 64 bit Windows</a>
</span>
<span class="<?php $this->data->PrintInputValue('classShowLinux'); ?>">
<br /><b>Linux</b><br />
<!-- https://www.forge.funambol.org/servlets/OCNDirector?id=V85FUNSERVLIN -->
<a href="http://www.funambol.com/opensource/download_response.php?file_id=funambol-8.5.2.bin">Funambol Server for Linux</a>
<br /><b>Linux64</b><br />
<!-- https://www.forge.funambol.org/servlets/OCNDirector?id=V85FUNSERVLIN64 -->
<a href="http://www.funambol.com/opensource/download_response.php?file_id=funambol-8.5.2-x64.bin">Funambol Server for 64 bit Linux</a>
</span>
<br /><br />

<!--
On Funambol's Download page you can either fill up your contact information of skip it by clicking
"No thanks - please take me straight to the downloads!" and go directly to download page.
<br /><br />
-->
Upon obtaining Funambol Data Synchronization Server please install it into
<br />
<span class="<?php $this->data->PrintInputValue('classShowWindows'); ?>">
C:\Program Files\Funambol
</span>
<span class="<?php $this->data->PrintInputValue('classShowLinux'); ?>">
/opt/Funambol
</span>
<br /><br />
In the rest of this document, this Funambol installation directory will be referred to as $FUNAMBOL_HOME.
<br /><br />
Once installation is completed, you need to configure Funambol Server.
It should be configured to use the same MySQL Database that was specified for WebMail.
You need to configure properties file for Funambol Data Synchronization Server.
The $FUNAMBOL_HOME/install.properties file is the central configuration information storage 
that is used by the installation procedure to set up the Funambol Data Synchronization Server.
Please, follow the below steps to configure Funambol Server:
<br /><br />
1. Download MySQL connector from <a href="http://www.mysql.com/downloads/connector/j/">official MySQL page</a>
<br /><br />
2. Copy MySQL connector to 
<span class="<?php $this->data->PrintInputValue('classShowWindows'); ?>">
$FUNAMBOL_HOME\funambol\tools\jre-1.6.0\jre\lib\ext
</span>
<span class="<?php $this->data->PrintInputValue('classShowLinux'); ?>">
$FUNAMBOL_HOME/funambol/tools/jre-1.6.0/jre/lib/ext
</span>
<br /><br />
3. Modify the following file:<br />
<span class="<?php $this->data->PrintInputValue('classShowWindows'); ?>">
$FUNAMBOL_HOME\funambol\ds-server\install.properties
</span>
<span class="<?php $this->data->PrintInputValue('classShowLinux'); ?>">
$FUNAMBOL_HOME/funambol/ds-server/install.properties
</span>
<br />
Change the value of the dbms parameter to: dbms=mysql
<br /><br />
4. Comment out the hypersonic configuration section:
#jdbc.classpath=../tools/hypersonic/lib/hsqldb.jar<br />
#jdbc.driver=org.hsqldb.jdbcDriver<br />
#jdbc.url=jdbc:hsqldb:hsql://localhost/funambol<br />
#jdbc.user=sa<br />
#jdbc.password=<br />
<br />
5. Place the MySQL configuration: details:<br />
for example:<br />
<span class="<?php $this->data->PrintInputValue('classShowLinux'); ?>">
	jdbc.classpath=/opt/Funambol/tools/jre-1.6.0/lib/ext/mysql-connector-java-&lt;version&gt;-bin.jar
</span>
<span class="<?php $this->data->PrintInputValue('classShowWindows'); ?>">
jdbc.classpath="C:\Program Files\Funambol\tools\jre-1.6.0\jre\lib\ext\mysql-connector-java-&lt;version&gt;-bin.jar
</span>
<br />
jdbc.driver=com.mysql.jdbc.Driver<br />
jdbc.url=jdbc:mysql://<?php $this->data->PrintInputValue('textJdbcHost'); ?>/<?php $this->data->PrintInputValue('textJdbcDb'); ?>?characterEncoding=UTF-8<br />
jdbc.user=<?php $this->data->PrintInputValue('textJdbcUser'); ?><br />
jdbc.password= ~ password ~ <br />
<br />
6. Run the following command:
<br />
<span class="<?php $this->data->PrintInputValue('classShowWindows'); ?>">
$FUNAMBOL_HOME\funambol\bin\install.cmd
</span>
<span class="<?php $this->data->PrintInputValue('classShowLinux'); ?>">
$FUNAMBOL_HOME/funambol/bin/install
</span>
<br />
answering 'y' to all questions.
<br />
After that, run the service
<br />
<span class="<?php $this->data->PrintInputValue('classShowWindows'); ?>">
Start->Programs->Funambol->Data Synchronization Server->Start Server
</span>
<span class="<?php $this->data->PrintInputValue('classShowLinux'); ?>">
$FUNAMBOL_HOME/funambol/bin/funambol start
</span>
<br /><br />
then specify Mobile Sync URL (at the top of this page), like<br />
http://your.host.com:8080/funambol/ds

<br /><br />
The final step in setting up Synchronization - you should set the following script to be launched by your OS once a minute:
$WEBMAIL_ROOR/calendar/cron/funambol.php Cron job should run PHP interpreter with funambol.php file as a parameter. Something like this:<br /> php -f /path/to/calendar/cron/funambol.php
		</td>
	</tr>
	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td align="left">
			<input type="button" name="back_btn" id="back_btn" value="Back" class="wm_install_button" style="width: 100px" onclick="javascript:<?php $this->data->PrintValue('onClickBack'); ?>" />
		</td>
		<td align="right">
			<a name="foot"></a>
			<input type="submit" name="submit_btn" id="submit_btn" value="Next" class="wm_install_button" style="width: 100px" />
		</td>
	</tr>
	<tr><td colspan="2"><br /><br /></td></tr>
</table>
</form>
<script type="text/javascript">
if (window.SettingsObjects && SettingsObjects["mobilesync"]) {
	SettingsObjects["mobilesync"].Init();
}
</script>