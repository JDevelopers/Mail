/*
 * Classes:
 *  CCommonSettingsScreenPart(parentScreen)
 */

function CCommonSettingsScreenPart(parentScreen)
{
	this._parentScreen = parentScreen;
	
	this._mainForm = null;
	
	this._settings = null;
	this._newSettings = null;
	
	this.hasChanges = false;
	this._shown = false;
	
	this._messPerPageObj = null;
	this._messPerPageCont = null;
	this._contactsPerPageObj = null;
	this._contactsPerPageCont = null;
	this._autoCheckMailObj = null;
	this._autoCheckMailCont = null;
	this._autoCheckMailBuilded = false;
	this._disableRteObj = null;
	this._disableRteCont = null;
	this._skinObj = null;
	this._skinBuilded = false;
	this._skinCont = null;
	this._charsetOutObj = null;
	this._charsetOutBuilded = false;
	this._charsetOutCont = null;
	this._timeOffsetObj = null;
	this._timeOffsetBuilded = false;
	this._timeOffsetCont = null;
	this._12timeFormatObj = null;
	this._24timeFormatObj = null;
	this._timeFormatCont = null;
	this._languageObj = null;
	this._languageBuilded = false;
	this._languageCont = null;
	this._viewPaneObj = null;
	this._viewPaneCont = null;
	this._viewPicturesObj = null;
	this._viewPicturesCont = null;
}

CCommonSettingsScreenPart.prototype = {
	Show: function()
	{
		this.hasChanges = false;
		this._mainForm.className = (window.UseDb || window.UseLdapSettings) ? '' : 'wm_hide';
		this._shown = true;
		if (this._settings == null) {
			GetHandler(TYPE_USER_SETTINGS, { }, [], '');
		} else {
			this.Fill();
		}
	},
	
	Hide: function()
	{
		if (this.hasChanges) {
			if (confirm(Lang.ConfirmSaveSettings)) {
				this.SaveChanges();
			} else {
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
	
	GetNewSettings: function ()
	{
		var showPic = this._settings.ViewMode & VIEW_MODE_SHOW_PICTURES;
		var newShowPic = this._newSettings.ViewMode & VIEW_MODE_SHOW_PICTURES;
		if (showPic != newShowPic && newShowPic == VIEW_MODE_SHOW_PICTURES) {
			WebMail.DataSource.Cache.SetMessageSafety(-1, '', -1, '', SAFETY_FULL, true);
		}
		this._settings = this._newSettings;
		this.Fill();
		return this._settings;
	},
	
	Fill: function ()
	{
		if (this._shown) {
			var i, opt;
			var settings = this._settings;
			this.hasChanges = false;
			if (settings.MsgsPerPage != null) {
				this._messPerPageObj.value = settings.MsgsPerPage;
				this._messPerPageCont.className = '';
			}
			else {
				this._messPerPageCont.className = 'wm_hide';
			}
			
			if (WebMail.Settings.AllowContacts && settings.ContactsPerPage != null) {
				this._contactsPerPageObj.value = settings.ContactsPerPage;	
				this._contactsPerPageCont.className = '';
			}
			else {
				this._contactsPerPageObj.value = 20;
				this._contactsPerPageCont.className = 'wm_hide';
			}
			if (settings.AutoCheckMailInterval != null) {
				if (!this._autoCheckMailBuilded) {
				    opt = CreateChild(this._autoCheckMailObj, 'option', [['value', 0]]);
				    opt.innerHTML = Lang.AutoCheckMailIntervalDisableName;
					WebMail.LangChanger.Register('innerHTML', opt, 'AutoCheckMailIntervalDisableName', '');

					opt = CreateChild(this._autoCheckMailObj, 'option', [['value', 1]]);
				    opt.innerHTML = '1';

					opt = CreateChild(this._autoCheckMailObj, 'option', [['value', 5]]);
				    opt.innerHTML = '5';

					opt = CreateChild(this._autoCheckMailObj, 'option', [['value', 10]]);
				    opt.innerHTML = '10';
					
					opt = CreateChild(this._autoCheckMailObj, 'option', [['value', 15]]);
				    opt.innerHTML = '15';

					opt = CreateChild(this._autoCheckMailObj, 'option', [['value', 30]]);
				    opt.innerHTML = '30';

    				this._autoCheckMailBuilded = true;
				}

				this._autoCheckMailObj.value = settings.AutoCheckMailInterval;
				this._autoCheckMailCont.className = '';
			}
			else {
				this._messPerPageCont.className = 'wm_hide';
			}
			if (settings.DisableRte != null) {
				this._disableRteObj.checked = settings.DisableRte;
				this._disableRteCont.className = '';
			}
			else {
				this._disableRteCont.className = 'wm_hide';
			}
			if (settings.CharsetOut != null) {
			    if (!this._charsetOutBuilded) {
				    for (i in Charsets) {
					    opt = CreateChild(this._charsetOutObj, 'option', [['value', Charsets[i].Value]]);
					    opt.innerHTML = Charsets[i].Name;
					    if (Charsets[i].Value == '0') {
					        WebMail.LangChanger.Register('innerHTML', opt, 'CharsetDefault', '');
					    }
				    }
    				this._charsetOutBuilded = true;
				}
				this._charsetOutObj.value = settings.CharsetOut;
				this._charsetOutCont.className = '';
			}
			else {
				this._charsetOutCont.className = 'wm_hide';
			}
			if (settings.TimeOffset != null) {
			    if (!this._timeOffsetBuilded) {
				    for (i in TimeOffsets) {
					    opt = CreateChild(this._timeOffsetObj, 'option', [['value', TimeOffsets[i].Value]]);
					    opt.innerHTML = TimeOffsets[i].Name;
					    if (TimeOffsets[i].Value == '0') {
					        WebMail.LangChanger.Register('innerHTML', opt, 'TimeDefault', '');
					    }
				    }
    				this._timeOffsetBuilded = true;
				}
				this._timeOffsetObj.value = settings.TimeOffset;
				this._timeOffsetCont.className = '';
			}
			else {
				this._timeOffsetCont.className = 'wm_hide';
			}
			if (settings.TimeFormat!= null) {
			    if (settings.TimeFormat == 0) {
				    this._12timeFormatObj.checked = false;
				    this._24timeFormatObj.checked = true;
				}
				else {
				    this._12timeFormatObj.checked = true;
				    this._24timeFormatObj.checked = false;
				}
				this._timeFormatCont.className = '';
				if (setcache != null) setcache['timeformat'] = settings.TimeFormat;
			}
			else {
				this._timeFormatCont.className = 'wm_hide';
			}
			if (settings.ViewMode != null) {
				this._viewPaneObj.checked = settings.ViewMode & VIEW_MODE_CENTRAL_LIST_PANE;
				this._viewPicturesObj.checked = settings.ViewMode & VIEW_MODE_SHOW_PICTURES;
				this._viewPaneCont.className = '';
				this._viewPicturesCont.className = '';
			}
			else {
				this._viewPaneCont.className = 'wm_hide';
				this._viewPicturesCont.className = 'wm_hide';
			}
			var skins = settings.Skins;
			if (settings.DefSkin != null) {
			    if (!this._skinBuilded) {
				    for (i in skins) {
					    opt = CreateChild(this._skinObj, 'option', [['value', skins[i]]]);
					    opt.innerHTML = skins[i];
				    }
    				this._skinBuilded = true;
				}
				this._skinObj.value = settings.DefSkin;
				this._skinCont.className = '';
			}
			else {
				this._skinCont.className = 'wm_hide';
			}
			var langs = settings.Langs;
			if (settings.DefLang != null) {
			    if (!this._languageBuilded) {
			    	var langName;
				    for (i in langs) {
				    	langName = Lang['Language' + langs[i].replace(/-/g, '')];
					    opt = CreateChild(this._languageObj, 'option', [['value', langs[i]]]);
					    opt.innerHTML = (typeof(langName) == 'undefined') ? langs[i] : langName;
				    }
    				this._languageBuilded = true;
				}
				this._languageObj.value = settings.DefLang;
				this._languageCont.className = '';
			}
			else {
				this._languageCont.className = 'wm_hide';
			}

			if (this._parentScreen) {
				this._parentScreen.ResizeBody();
			}
		}
	},//Fill
	
	SetInputKeyPress: function (inp)
	{
	    var obj = this;
		inp.onkeypress = function (ev) { if (isEnter(ev)) obj.SaveChanges(); };
	},
	
	SaveChanges: function ()
	{
		var messPerPageValue = Trim(this._messPerPageObj.value);
		if (Validator.IsEmpty(messPerPageValue) || !Validator.IsPositiveNumber(messPerPageValue)) {
			alert(Lang.WarningMessagesPerPage);
			return;
		}
		var contPerPageValue = Trim(this._contactsPerPageObj.value);
		if (Validator.IsEmpty(contPerPageValue) || !Validator.IsPositiveNumber(contPerPageValue)) {
			alert(Lang.WarningContactsPerPage);
			return;
		}
		var autoCheckMailInterval = Trim(this._autoCheckMailObj.value);
		
		var settings = this._settings;
		var newSettings = new CSettings();
		if (settings.MsgsPerPage != null) {
			newSettings.MsgsPerPage = messPerPageValue - 0;
		}
		if (WebMail.Settings.AllowContacts && settings.ContactsPerPage != null) {
			newSettings.ContactsPerPage = contPerPageValue - 0;
		}
		if (settings.AutoCheckMailInterval != null) {
			newSettings.AutoCheckMailInterval = autoCheckMailInterval - 0;
		}
		if (settings.DisableRte != null) {
			newSettings.DisableRte = this._disableRteObj.checked;
		}
		if (settings.CharsetOut != null) {
			newSettings.CharsetOut = this._charsetOutObj.value - 0;
		}
		if (settings.TimeOffset != null) {
			newSettings.TimeOffset = this._timeOffsetObj.value - 0;
		}
		if (settings.TimeFormat != null) {
			newSettings.TimeFormat = this._12timeFormatObj.checked ? 1 : 0;
		}
		if (settings.ViewMode != null) {
			newSettings.ViewMode = this._viewPaneObj.checked * VIEW_MODE_CENTRAL_LIST_PANE | this._viewPicturesObj.checked * VIEW_MODE_SHOW_PICTURES;
		}
		if (settings.DefSkin != null) {
			newSettings.Skins = settings.Skins;
			newSettings.DefSkin = this._skinObj.value;
		}
		if (settings.DefLang != null) {
			newSettings.Langs = settings.Langs;
			newSettings.DefLang = this._languageObj.value;
		}

		var xml = newSettings.GetInXML();
		RequestHandler('update', 'settings', xml);

		this._newSettings = newSettings;
		this.hasChanges = false;
	},//SaveChanges

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
		tr.className = 'wm_hide';
		var td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.MsgsPerPage + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'MsgsPerPage', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		var inp = CreateChild(td, 'input', [['class', 'wm_input'], ['type', 'text'], ['size', '2'], ['maxlength', '2']]);
		this.SetInputKeyPress(inp);
		this._messPerPageObj = inp;
		this._messPerPageObj.onchange = function () { obj.hasChanges = true; };
		this._messPerPageCont = tr;
		
		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.ContactsPerPage + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'ContactsPerPage', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_input'], ['type', 'text'], ['size', '2'], ['maxlength', '2']]);
		this.SetInputKeyPress(inp);
		this._contactsPerPageObj = inp;
		this._contactsPerPageObj.onchange = function () { obj.hasChanges = true; };
		this._contactsPerPageCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.AutoCheckMailIntervalLabel + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'AutoCheckMailIntervalLabel', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		var sel = CreateChild(td, 'select');
		this._autoCheckMailObj = sel;
		this._autoCheckMailObj.onchange = function () { obj.hasChanges = true; };
		this._autoCheckMailCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['id', 'disable_rte'], ['value', '1']]);
		var lbl = CreateChild(td, 'label', [['for', 'disable_rte']]);
		lbl.innerHTML = Lang.DisableRTE;
		WebMail.LangChanger.Register('innerHTML', lbl, 'DisableRTE', '');
		this._disableRteObj = inp;
		this._disableRteObj.onchange = function () { obj.hasChanges = true; };
		this._disableRteCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.Skin + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Skin', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		sel = CreateChild(td, 'select');
		this._skinObj = sel;
		this._skinObj.onchange = function () { obj.hasChanges = true; };
		this._skinCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.DefCharset + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'DefCharset', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		sel = CreateChild(td, 'select');
		this._charsetOutObj = sel;
		this._charsetOutObj.onchange = function () { obj.hasChanges = true; };
		this._charsetOutCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.DefTimeOffset + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'DefTimeOffset', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		sel = CreateChild(td, 'select');
		this._timeOffsetObj = sel;
		this._timeOffsetObj.onchange = function () { obj.hasChanges = true; };
		this._timeOffsetCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.DefTimeFormat + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'DefTimeFormat', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['id', 'def_TimeFormat_0'], ['class', 'wm_checkbox'], ['type', 'radio'], ['name', 'def_TimeFormat']]);
		lbl = CreateChild(td, 'label', [['for', 'def_TimeFormat_0']]);
		lbl.innerHTML = '1PM&nbsp;';
		this._12timeFormatObj = inp;
		this._12timeFormatObj.onchange = function () { obj.hasChanges = true; };
		inp = CreateChild(td, 'input', [['id', 'def_TimeFormat_1'], ['class', 'wm_checkbox'], ['type', 'radio'], ['name', 'def_TimeFormat']]);
		lbl = CreateChild(td, 'label', [['for', 'def_TimeFormat_1']]);
		lbl.innerHTML = '13:00';
		this._24timeFormatObj = inp;
		this._24timeFormatObj.onchange = function () { obj.hasChanges = true; };
		this._timeFormatCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.DefLanguage + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'DefLanguage', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		sel = CreateChild(td, 'select');
		this._languageObj = sel;
		this._languageObj.onchange = function () { obj.hasChanges = true; };
		this._languageCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['id', 'view_pane']]);
		lbl = CreateChild(td, 'label', [['for', 'view_pane']]);
		lbl.innerHTML = Lang.MessagePaneToRight;
		WebMail.LangChanger.Register('innerHTML', lbl, 'MessagePaneToRight', '');
		inp.onchange = function () { obj.hasChanges = true; };
		this._viewPaneObj = inp;
		this._viewPaneCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['id', 'view_pictures']]);
		lbl = CreateChild(td, 'label', [['for', 'view_pictures']]);
		lbl.innerHTML = Lang.AlwaysShowPictures;
		WebMail.LangChanger.Register('innerHTML', lbl, 'AlwaysShowPictures', '');
		inp.onchange = function ()  { obj.hasChanges = true; };
		this._viewPicturesObj = inp;
		this._viewPicturesCont = tr;

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