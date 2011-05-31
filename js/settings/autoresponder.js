/*
 * Classes:
 *  CAutoresponderScreenPart(parentScreen)
 */

function CAutoresponderScreenPart(parentScreen)
{
	this._parentScreen = parentScreen;
	
	this._autoresponder = null;
	this._newAutoresponder = null;
	
	this.hasChanges = false;
	this.shown = false;

    this._responderEditZone = null;
	this._enableObj = null;
	this._subjectObj = null;
	this._subjectCont = null;
	this._messageObj = null;

	this._idAcct = -1;
}

CAutoresponderScreenPart.prototype = {
	Show: function()
	{
		this.hasChanges = false;
		this._responderEditZone.className = (window.UseDb || window.UseLdapSettings)
			? 'wm_email_settings_edit_zone' : 'wm_hide';

		this.shown = true;
		if (this._idAcct != this._parentScreen._idAcct) {
			this._idAcct = this._parentScreen._idAcct;
			GetHandler(TYPE_AUTORESPONDER, { IdAcct: this._idAcct }, [], '');
		}
		else this.Fill();
	},
	
	ClickBody: function (ev) {},
	
	Hide: function()
	{
		this.shown = false;
		if (WebMail._isDemo) {
			this.Fill();
		}
		else if (this.hasChanges) {
			if (confirm(Lang.ConfirmSaveSignature)) {
				this.SaveChanges();
			}
			else {
				this.Fill();
			}
		}
		this.hasChanges = false;
		this._responderEditZone.className = 'wm_hide';
	},
	
	GetNewAutoresponder: function ()
	{
		var autoresponder = new CAutoresponderData();
		autoresponder.Enable = this._newAutoresponder.Enable;
		autoresponder.Subject = this._newAutoresponder.Subject;
		autoresponder.Message = this._newAutoresponder.Message;
		autoresponder.IdAcct = this._newAutoresponder.IdAcct;
		this._autoresponder = autoresponder;
		return autoresponder;
	},

	SetAutoresponder: function (autoresponder)
	{
		this._autoresponder = autoresponder;
		this._idAcct = autoresponder.IdAcct;
		this.Fill();
	},

	Fill: function ()
	{
		if ((null == this._autoresponder) || !this.shown) return;
		this._enableObj.checked = this._autoresponder.Enable;
		this._subjectObj.value = this._autoresponder.Subject;
		this._messageObj.value = this._autoresponder.Message;
		if (this._enableObj.checked) {
			this._subjectObj.disabled = false;
			this._messageObj.disabled = false;
		}
		else {
			this._subjectObj.disabled = true;
			this._messageObj.disabled = true;
		}

		if (this._parentScreen) {
			this._parentScreen.ResizeBody();
		}
	},
	
	SetInputKeyPress: function (inp)
	{
	    var obj = this;
		inp.onkeypress = function (ev) { if (isEnter(ev)) obj.SaveChanges(); };
	},
	
	SaveChanges: function ()
	{
		if (WebMail._isDemo) {
			WebMail.ShowReport(DemoWarning);
			return;
		}
		var autoresponder = new CAutoresponderData();
		autoresponder.Enable = this._enableObj.checked;
		autoresponder.Subject = this._subjectObj.value;
		autoresponder.Message = this._messageObj.value;
		autoresponder.IdAcct = this._idAcct;
		this._newAutoresponder = autoresponder;
		var xml = autoresponder.GetInXML();
		RequestHandler('update', 'autoresponder', xml);
		this.hasChanges = false;
	},

	SetSubjectView: function(isHide)
	{
		this._subjectCont.className = (isHide) ? 'wm_hide' : '';
	},
	
	Build: function(container)
	{
		var obj = this;
		this._responderEditZone = CreateChild(container, 'table');
		this._responderEditZone.className = 'wm_hide';
		var mainTr = this._responderEditZone.insertRow(0);
		var mainTd = mainTr.insertCell(0);
		mainTd.className = 'wm_email_settings_edit_zone_cell';

		var tbl = CreateChild(mainTd, 'table');
		tbl.className = 'wm_settings_signature';
		var rowIndex = 0;
		var tr = tbl.insertRow(rowIndex++);
		var td = tr.insertCell(0);
		td = tr.insertCell(1);
		var inp = CreateChild(td, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['id', 'enable_ar']]);
		var lbl = CreateChild(td, 'label', [['for', 'enable_ar']]);
		lbl.innerHTML = Lang.AutoresponderEnable;
		WebMail.LangChanger.Register('innerHTML', lbl, 'AutoresponderEnable', '');
		inp.onclick = function () {
			obj.hasChanges = true;
			if (this.checked) {
				obj._subjectObj.disabled = false;
				obj._messageObj.disabled = false;
			}
			else {
				obj._subjectObj.disabled = true;
				obj._messageObj.disabled = true;
			}
		};
		this._enableObj = inp;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.AutoresponderSubject + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'AutoresponderSubject', ':');
		td = tr.insertCell(1);
		inp = CreateChild(td, 'input', [['class', 'wm_input'], ['type', 'text']]);
		this.SetInputKeyPress(inp);
		inp.onchange = function () { obj.hasChanges = true; };
		this._subjectObj = inp;
		this._subjectCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.innerHTML = Lang.AutoresponderMessage + ':';
		td.style.verticalAlign = 'top';
		td.className = 'wm_settings_title';
		WebMail.LangChanger.Register('innerHTML', td, 'AutoresponderMessage', ':');
		td = tr.insertCell(1);
		var txt = CreateChild(td, 'textarea', [['class', 'wm_input']]);
		txt.onchange = function () { obj.hasChanges = true; };
		this._messageObj = txt;

		tbl = CreateChild(mainTd, 'table');
		tbl.className = 'wm_settings_buttons';
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		inp = CreateChild(td, 'input', [['class', 'wm_button'], ['type', 'button'], ['value', Lang.Save]]);
		WebMail.LangChanger.Register('value', inp, 'Save', '');
		inp.onclick = function () { obj.SaveChanges(); };
	}//Build
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}