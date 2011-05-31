/*
 * Classes:
 *  CSignatureScreenPart(parentScreen)
 */

function CSignatureScreenPart(parentScreen)
{
	this._parentScreen = parentScreen;
	
	this._signature = null;
	
	this.hasChanges = false;
	this.shown = false;

	this._plainEditorObj = null;
	this._plainEditorDiv = null;
	this._modeSwitcher = null;
	this._modeSwitcherCont = null;

    this._signatureEditZone = null;	
	this._opt1Obj = null;
	this._opt2Obj = null;
}

CSignatureScreenPart.prototype = {
	Show: function()
	{
		this.hasChanges = false;
		this._signatureEditZone.className = (window.UseDb || window.UseLdapSettings)
			? 'wm_email_settings_edit_zone' : 'wm_hide';

		var width = 664;
		var height = 170;
		this._plainEditorDiv.style.height = height + 'px';
		this._plainEditorDiv.style.width = width + 'px';

		if (WebMail.Settings.AllowDhtmlEditor && (window.UseDb || window.UseLdapSettings)) {
			HtmlEditorField.Show();
			HtmlEditorField.SetPlainEditor(this._plainEditorObj, this._modeSwitcher);
			HtmlEditorField.Resize(width, height);
		}
		else {
			this._plainEditorObj.style.height = (height - 1) + 'px';
			this._plainEditorObj.style.width = (width - 2) + 'px';
		}
		this.shown = true;
		this.Fill();
		if (WebMail.Settings.AllowDhtmlEditor && (window.UseDb || window.UseLdapSettings)) {
			this._modeSwitcherCont.className = '';
		}
		else {
			this._modeSwitcherCont.className = 'wm_hide';
			HtmlEditorField.Hide();
		}
	},//Show
	
	ClickBody: function (ev)
	{
		if (WebMail.Settings.AllowDhtmlEditor) {
			HtmlEditorField.ClickBody();
		}
	},
	
	ReplaceHtmlEditorField: function ()
	{
		if (WebMail.Settings.AllowDhtmlEditor) {
			HtmlEditorField.Replace();
		}
	},

	Hide: function()
	{
		this.shown = false;
		if (!WebMail._isDemo && this.hasChanges && confirm(Lang.ConfirmSaveSignature)) {
			this.SaveChanges();
		}
		this.hasChanges = false;
		if (WebMail.Settings.AllowDhtmlEditor) {
			HtmlEditorField.Hide();
		}
		this._signatureEditZone.className = 'wm_hide';
	},
	
	DesignModeOn: function ()
	{
		this._modeSwitcherCont.className = '';
	},

	GetNewSignature: function ()
	{
		return this._signature;
	},

	Fill: function ()
	{
		if (this.shown) {
			var acct = WebMail.Accounts.GetEditableAccount();
			if (WebMail.Settings.AllowDhtmlEditor) {
				if (acct.SignatureType == SIGNATURE_TYPE_HTML) {
					HtmlEditorField.SetHtml(acct.Signature);
				}
				else {
					HtmlEditorField.SetText(acct.Signature);
				}
			}
			else {
				if (acct.SignatureType == SIGNATURE_TYPE_HTML) {
					this._plainEditorObj.value = HtmlDecode(acct.Signature.replace(/<br *\/{0,1}>/gi, '\n').replace(/<[^>]*>/g, ''));
				}
				else {
					this._plainEditorObj.value = acct.Signature;
				}
			}
			switch (acct.SignatureOpt) {
				case SIGNATURE_OPT_DONT_ADD_TO_ALL:
					this._opt1Obj.checked = false;
					this._opt2Obj.checked = false;
					this._opt2Obj.disabled = true;
					break;
				case SIGNATURE_OPT_ADD_TO_ALL:
					this._opt1Obj.checked = true;
					this._opt2Obj.checked = false;
					this._opt2Obj.disabled = false;
					break;
				case SIGNATURE_OPT_DONT_ADD_TO_REPLIES:
					this._opt1Obj.checked = true;
					this._opt2Obj.checked = true;
					this._opt2Obj.disabled = false;
					break;
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
		if (WebMail._isDemo) {
			WebMail.ShowReport(DemoWarning);
			return;
		}

		var signature = new CSignatureData();
		if (WebMail.Settings.AllowDhtmlEditor && HtmlEditorField._htmlMode) {
			signature.isHtml = true;
			signature.Value = HtmlEditorField.GetText();
		} else {
			signature.isHtml = false;
			signature.Value = this._plainEditorObj.value;
		}
		signature.Opt = SIGNATURE_OPT_DONT_ADD_TO_ALL;
		if (this._opt1Obj.checked) {
			signature.Opt = (this._opt2Obj.checked) ? SIGNATURE_OPT_DONT_ADD_TO_REPLIES : SIGNATURE_OPT_ADD_TO_ALL;
		}
		signature.IdAcct = WebMail.Accounts.EditableId;
		this._signature = signature;
		var xml = signature.GetInXML();
		RequestHandler('update', 'signature', xml);
		this.hasChanges = false;
	},
	
	Build: function(container)
	{
		var lbl, inp, a;
		var obj = this;
		this._signatureEditZone = CreateChild(container, 'table');
		this._signatureEditZone.className = 'wm_hide';
		var mainTr = this._signatureEditZone.insertRow(0);
		var mainTd = mainTr.insertCell(0);
		mainTd.className = 'wm_email_settings_edit_zone_cell';

		var tbl = CreateChild(mainTd, 'table');
		tbl.className = 'wm_settings_signature';
		var tr = tbl.insertRow(0);
		var td = tr.insertCell(0);
		var div = CreateChild(td, 'div');
		div.className = 'wm_input wm_plain_editor_container';
		var txt = CreateChild(div, 'textarea');
		txt.className = 'wm_plain_editor_text';
		this._plainEditorObj = txt;
		this._plainEditorDiv = div;

		tr = tbl.insertRow(1);
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		a = CreateChild(td, 'a', [['href', '#']]);
		a.className = '';
		a.innerHTML = Lang.SwitchToHTMLMode;
		this._modeSwitcher = a;
		this._modeSwitcherCont = tr;
		
		tr = tbl.insertRow(2);
		td = tr.insertCell(0);
		inp = CreateChild(td, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['id', 'opt1']]);
		lbl = CreateChild(td, 'label', [['for', 'opt1']]);
		lbl.innerHTML = Lang.AddSignatures;
		WebMail.LangChanger.Register('innerHTML', lbl, 'AddSignatures', '');
		inp.onclick = function () {
			obj._opt2Obj.disabled = !this.checked;
			obj.hasChanges = true;
		};
		this._opt1Obj = inp;
		tr = tbl.insertRow(3);
		td = tr.insertCell(0);
		inp = CreateChild(td, 'input', [['class', 'wm_checkbox wm_settings_para'], ['type', 'checkbox'], ['id', 'opt2']]);
		lbl = CreateChild(td, 'label', [['for', 'opt2']]);
		lbl.innerHTML = Lang.DontAddToReplies;
		WebMail.LangChanger.Register('innerHTML', lbl, 'DontAddToReplies', '');
		inp.onchange = function () { obj.hasChanges = true; };
		this._opt2Obj = inp;

		tbl = CreateChild(mainTd, 'table');
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