/*
 * Classes:
 *  CMobileSyncSettingsScreenPart(parentScreen)
 */

function CMobileSyncSettingsScreenPart(parentScreen)
{
	this._parentScreen = parentScreen;
	
	this._mainForm = null;
	
	this._settings = null;
	this._newSettings = null;
	
	this.hasChanges = false;
	this._shown = false;
	
	this._enableObj = null;
	this._urlObj = null;
	this._loginObj = null;
	this._contactDataBaseObj = null;
	this._calendarDataBaseObj = null;
}

CMobileSyncSettingsScreenPart.prototype = {
	Show: function()
	{
		this.hasChanges = false;
		this._mainForm.className = (window.UseDb || window.UseLdapSettings) ? '' : 'wm_hide';
		this._shown = true;
		if (this._settings == null) {
			GetHandler(TYPE_MOBILE_SYNC, { }, [], '');
		}
        else {
			this.Fill();
		}
	},
	
	Hide: function()
	{
		if (this.hasChanges) {
			if (confirm(Lang.ConfirmSaveSettings)) {
				this.SaveChanges();
			}
            else {
				this.Fill();
			}
		}
		this._mainForm.className = 'wm_hide';
		this.hasChanges = false;
		this._shown = false;
	},
	
	SetSettings: function (settings)
	{
		this._settings = settings;
		this.Fill();
	},
	
	UpdateSettings: function ()
	{
		this._settings = this._newSettings;
		this.Fill();
	},

	Fill: function ()
	{
		if (!this._shown) return;

        this.hasChanges = false;
        var mobileSync = this._settings;
        this._enableObj.checked = mobileSync.UserEnable;
        this._urlObj.value = mobileSync.Url;
        this._loginObj.value = mobileSync.Login;
        this._contactDataBaseObj.value = mobileSync.ContactDataBase;
        this._calendarDataBaseObj.value = mobileSync.CalendarDataBase;

        this._parentScreen.ResizeBody();
	},
	
	SaveChanges: function ()
	{
		var newSettings = new CMobileSyncData();
        newSettings.Copy(this._settings);
		newSettings.UserEnable = this._enableObj.checked;

		var xml = newSettings.GetInXML();
		RequestHandler('update', 'mobile_sync', xml);

		this._newSettings = newSettings;
		this.hasChanges = false;
	},

	Build: function(container)
	{
		var obj = this;
		this._mainForm = CreateChild(container, 'form');
		this._mainForm.onsubmit = function () { return false; };
		this._mainForm.className = 'wm_hide';
		var tbl = CreateChild(this._mainForm, 'table');
		tbl.className = 'wm_settings_common';

		var rowIndex = 0;
		var tr = tbl.insertRow(rowIndex++);
		tr.className = '';
		var td = tr.insertCell(0);
		td.className = '';
		td.colSpan = 3;
		td.innerHTML = '<br />';

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td = tr.insertCell(1);
		td.colSpan = 2;
		var inp = CreateChild(td, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['id', 'enable_mobile_sync']]);
		var lbl = CreateChild(td, 'label', [['for', 'enable_mobile_sync']]);
		lbl.innerHTML = Lang.MobileSyncEnableLabel;
		WebMail.LangChanger.Register('innerHTML', lbl, 'MobileSyncEnableLabel', '');
		inp.onchange = function ()  { obj.hasChanges = true; };
		this._enableObj = inp;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.MobileSyncUrlTitle + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'MobileSyncUrlTitle', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_input'], ['type', 'text'], ['size', '50'], ['readonly', 'readonly']]);
		this._urlObj = inp;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.MobileSyncLoginTitle + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'MobileSyncLoginTitle', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_input'], ['type', 'text'], ['size', '25'], ['readonly', 'readonly']]);
		this._loginObj = inp;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.MobileSyncContactDataBaseTitle + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'MobileSyncContactDataBaseTitle', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_input'], ['type', 'text'], ['size', '15'], ['readonly', 'readonly']]);
		this._contactDataBaseObj = inp;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.MobileSyncCalendarDataBaseTitle + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'MobileSyncCalendarDataBaseTitle', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_input'], ['type', 'text'], ['size', '15'], ['readonly', 'readonly']]);
		this._calendarDataBaseObj = inp;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = '';
		td = tr.insertCell(1);
		td.colSpan = 2;
		var div = CreateChild(td, 'div', [['class', 'syncTextTitleClass']]);
		div.innerHTML = Lang.MobileSyncTitleText;
		WebMail.LangChanger.Register('innerHTML', div, 'MobileSyncTitleText', '');

		tbl = CreateChild(this._mainForm, 'table');
		tbl.className = 'wm_settings_buttons';
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		inp = CreateChild(td, 'input', [['class', 'wm_button'], ['type', 'button'], ['value', Lang.Save]]);
		WebMail.LangChanger.Register('value', inp, 'Save', '');
		inp.onclick = function () {
			obj.SaveChanges();
		};
	}//Build
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}