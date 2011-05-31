/*
 * Classes:
 *  CAccountPropertiesScreenPart(parentScreen)
 */

function CAccountPropertiesScreenPart(parentScreen)
{
	this._parentScreen = parentScreen;

	this.NewAccountProperties = null;

	this.hasChanges = false;
	this.hasForAccountsChanges = false;
	this.shown = false;
	this._idAcct = -1;
	this._mainForm = null;
	this._parent = null;

	this._accountInternalHide = null;
	this._useForLoginObj = null;
	this._useForLoginCont = null;
	this._friendlyNmObj = null;
	this._EmailObj = null;
	this._emailCont = null;
	this._getmailAtLoginCont = null;
	this._mailIncPassCont = null;
	this._newPassCont = null;
	this._mailIncLoginCont = null;
	this._mailIncHostObj = null;
	this._mailMode0Obj = null;
	this._mailMode1Obj = null;
	this._mailMode2Obj = null;
	this._mailMode3Obj = null;
	this._mailsOnServerDaysObj = null;
	this._mailProtocolSpan = null;
	this._mailProtocolObj = null;
	this._mailModeCont = null;
	this._mailIncPortObj = null;
	this._mailIncLoginObj = null;
	this._mailIncPassObj = null;
	this._newPassObj = null;
	this._confirmPassObj = null;
	this._mailOutHostObj = null;
	this._mailOutPortObj = null;
	this._mailOutLoginObj = null;
	this._mailOutPassObj = null;
	this._mailOutAuthObj = null;
	this._useFriendlyNmObj = null;
	this._getmailAtLoginObj = null;
	this._pop3InboxSyncTypeObj = null;
	this._pop3InboxSyncTypeCont = null;
	this._directModeOpt = null;
	this._deleteFromDbObj = null;
	this._deleteFromDbCont = null;
	this._requiredFieldsCont = null;
	this._btnCancel = null;

	this._mailOutAuthCont = null;
	this._mailIncHostCont = null;
	this._mailOutHostCont = null;
	this._mailOutPassCont = null;
	this._mailOutLoginCont = null;
}

CAccountPropertiesScreenPart.prototype = {
	Show: function()
	{
		if (!this.shown) {
			this.shown = true;
			this._mainForm.className = (window.UseDb) ? '' : 'wm_hide';
		}
		this.hasChanges = false;
		this.Fill(this._parentScreen._newMode);
	},

	Hide: function()
	{
		this.shown = false;
		if (!WebMail._isDemo && this.hasChanges && confirm(Lang.ConfirmSaveAcctProp)) {
			this.SaveChanges();
		}
		this.hasChanges = false;
		this._mainForm.className = 'wm_hide';
	},

	GetNewAccountProperties: function ()
	{
		return this.NewAccountProperties;
	},

	Fill: function (newMode)
	{
		this._newMode = newMode;
		if (this.shown) {
			var acctProp;
			if (newMode) {
				acctProp = new CAccountProperties();
				if (WebMail.Settings.AllowDirectMode && WebMail.Settings.DirectModeIsDefault) {
					acctProp.InboxSyncType = SYNC_TYPE_DIRECT_MODE;
				}
			}
			else {
				acctProp = WebMail.Accounts.GetEditableAccount();
			}
			this._idAcct = acctProp.Id;
			if (acctProp.DefAcct) {
				this._useForLoginObj.checked = true;
				this._useForLoginObj.disabled = (WebMail.Accounts.GetDefCount() < 2);
			} else {
				this._useForLoginObj.checked = false;
				this._useForLoginObj.disabled = false;
			}
			this._friendlyNmObj.value = HtmlDecode(acctProp.FriendlyNm);
			//this._accountInternalHide.value = (int)

			this._EmailObj.value = HtmlDecode(acctProp.Email);
			this._mailIncHostObj.value = HtmlDecode(acctProp.MailIncHost);
			if (acctProp.Id == -1) {
				CleanNode(this._mailProtocolObj);
				var pop3Opt = CreateChild(this._mailProtocolObj, 'option', [['value', POP3_PROTOCOL]]);
				pop3Opt.innerHTML = Lang.Pop3;
				pop3Opt.selected = true;
				var imap4Opt = CreateChild(this._mailProtocolObj, 'option', [['value', IMAP4_PROTOCOL]]);
				imap4Opt.innerHTML = Lang.Imap4;
			} else {
				this._mailProtocolSpan.innerHTML = (acctProp.MailProtocol == POP3_PROTOCOL) ? Lang.Pop3 : Lang.Imap4;
			}
			this._mailIncPortObj.value = acctProp.MailIncPort;
			this._mailIncLoginObj.value = HtmlDecode(acctProp.MailIncLogin);
			this._mailIncPassObj.value = HtmlDecode(acctProp.MailIncPass);
			this._newPassObj.value = '';
			this._confirmPassObj.value = '';

			this._mailOutHostObj.value = HtmlDecode(acctProp.MailOutHost);
			this._mailOutPortObj.value = acctProp.MailOutPort;
			this._mailOutLoginObj.value = HtmlDecode(acctProp.MailOutLogin);
			this._mailOutPassObj.value = HtmlDecode(acctProp.MailOutPass);
			this._mailOutAuthObj.checked = acctProp.MailOutAuth;
			this._useFriendlyNmObj.checked = acctProp.UseFriendlyNm;
			this._getmailAtLoginObj.checked = acctProp.GetMailAtLogin;
			var DELETE_MESSAGES_FROM_SERVER = 0;
			var LEAVE_MESSAGES_ON_SERVER = 1;
			var KEEP_MESSAGES_X_DAYS = 2;
			var DELETE_MESSAGE_WHEN_REMOVED_FROM_TRASH = 3;
			var KEEP_AND_DELETE_WHEN_REMOVED_FROM_TRASH = 4;
			switch (acctProp.MailMode) {
				case DELETE_MESSAGES_FROM_SERVER:
					this._mailMode0Obj.checked = true;
					this._mailMode1Obj.checked = false;
					this._mailMode2Obj.checked = false;
					this._mailMode3Obj.checked = false;
					break;
				case LEAVE_MESSAGES_ON_SERVER:
					this._mailMode0Obj.checked = false;
					this._mailMode1Obj.checked = true;
					this._mailMode2Obj.checked = false;
					this._mailMode3Obj.checked = false;
					break;
				case KEEP_MESSAGES_X_DAYS:
					this._mailMode0Obj.checked = false;
					this._mailMode1Obj.checked = true;
					this._mailMode2Obj.checked = true;
					this._mailMode3Obj.checked = false;
					break;
				case DELETE_MESSAGE_WHEN_REMOVED_FROM_TRASH:
					this._mailMode0Obj.checked = false;
					this._mailMode1Obj.checked = true;
					this._mailMode2Obj.checked = false;
					this._mailMode3Obj.checked = true;
					break;
				case KEEP_AND_DELETE_WHEN_REMOVED_FROM_TRASH:
					this._mailMode0Obj.checked = false;
					this._mailMode1Obj.checked = true;
					this._mailMode2Obj.checked = true;
					this._mailMode3Obj.checked = true;
					break;
			}
			this._mailsOnServerDaysObj.value = IsNum(acctProp.MailsOnServerDays) ? acctProp.MailsOnServerDays : '7';

			var type = acctProp.InboxSyncType;
			CleanNode(this._pop3InboxSyncTypeObj);
			var opt1 = CreateChild(this._pop3InboxSyncTypeObj, 'option', [['value', SYNC_TYPE_NEW_HEADERS]]);
			opt1.innerHTML = Lang.Pop3SyncTypeEntireHeaders;
			var opt3 = CreateChild(this._pop3InboxSyncTypeObj, 'option', [['value', SYNC_TYPE_NEW_MSGS]]);
			opt3.innerHTML = Lang.Pop3SyncTypeEntireMessages;
			if (WebMail.Settings.AllowDirectMode || SYNC_TYPE_DIRECT_MODE == type) {
				var opt5 = CreateChild(this._pop3InboxSyncTypeObj, 'option', [['value', SYNC_TYPE_DIRECT_MODE]]);
				opt5.innerHTML = Lang.Pop3SyncTypeDirectMode;
				this._directModeOpt = opt5;
			}

            // fix for pop gmail
            if (this._mailIncHostObj.value == 'pop.gmail.com') {
                this._pop3InboxSyncTypeObj.disabled = true;
            }

			switch (type) {
				case SYNC_TYPE_NEW_HEADERS:
					opt1.selected = true;
					this._deleteFromDbObj.checked = false;
					break;
				case SYNC_TYPE_ALL_HEADERS:
					opt1.selected = true;
					this._deleteFromDbObj.checked = true;
					type = SYNC_TYPE_NEW_HEADERS;
					break;
				case SYNC_TYPE_NEW_MSGS:
					opt3.selected = true;
					this._deleteFromDbObj.checked = false;
					break;
				case SYNC_TYPE_ALL_MSGS:
					opt3.selected = true;
					this._deleteFromDbObj.checked = true;
					type = SYNC_TYPE_NEW_MSGS;
					break;
				case SYNC_TYPE_DIRECT_MODE:
					opt5.selected = true;
					break;
			}
			this.SetDisabling(type);
			this.SetHidding(acctProp.Linked, acctProp.MailProtocol, (acctProp.Id == -1));
			this.hasChanges = false;

			if (this._parentScreen) {
				this._parentScreen.ResizeBody();
			}
		}
	},//Fill

	SetHidding: function (linked, protocol, newAccount)
	{
		this._emailCont.className = 'wm_hide';
		this._mailIncLoginCont.className = 'wm_hide';
		this._mailIncPassCont.className = 'wm_hide';
		this._newPassCont.className = 'wm_hide';
		this._getmailAtLoginCont.className = 'wm_hide';
		this._mailOutPassCont.className = 'wm_hide';
		this._mailOutLoginCont.className = 'wm_hide';
		this._mailOutAuthCont.className = 'wm_hide';
		this._mailIncHostCont.className = 'wm_hide';
		this._mailOutHostCont.className = 'wm_hide';
		this._pop3InboxSyncTypeCont.className = 'wm_hide';
		this._btnCancel.className = 'wm_hide';
		this._mailProtocolSpan.className = 'wm_hide';
		this._mailProtocolObj.className = 'wm_hide';
		this._mailModeCont.className = 'wm_hide';
		this._pop3InboxSyncTypeCont.className = 'wm_hide';
		this._deleteFromDbCont.className = 'wm_hide';
		this._requiredFieldsCont.className = 'wm_hide';
		this._useForLoginCont.className = 'wm_hide';
		if (!WebMail.Settings.AllowChangeSettings && !newAccount) return;
		this._mailIncLoginCont.className = '';
		this._getmailAtLoginCont.className = '';
		this._requiredFieldsCont.className = 'wm_secondary_info';
		if (!linked) {
			this._mailOutAuthCont.className = '';
			this._mailIncHostCont.className = '';
			this._mailOutHostCont.className = '';
		}
		if (protocol != WMSERVER_PROTOCOL) {
			this._emailCont.className = '';
			this._mailOutPassCont.className = '';
			this._mailOutLoginCont.className = '';
		}
		if (newAccount) {
			this._btnCancel.className = 'wm_button';
			this._mailProtocolObj.className = '';
			this._mailIncPassCont.className = '';
			this.SetHiddingPop3SyncSettings(POP3_PROTOCOL);
		}
		else {
			this.SetHiddingPop3SyncSettings(protocol);
			this._mailProtocolSpan.className = '';
			if (protocol == WMSERVER_PROTOCOL || window.hMailServer === true) {
				this._newPassCont.className = '';
			}
			else {
				this._mailIncPassCont.className = '';
			}
		}
		if ((WebMail.Accounts.Count > 1 || newAccount) && WebMail.Settings.AllowChangeAccountsDef) {
			this._useForLoginCont.className = '';
		}
	},

	SetHiddingPop3SyncSettings: function (protocol)
	{
		if (protocol == POP3_PROTOCOL) {
			this._pop3InboxSyncTypeCont.className = '';
			this._mailModeCont.className = '';
			this._deleteFromDbCont.className = '';
		}
		else {
			this._pop3InboxSyncTypeCont.className = 'wm_hide';
			this._mailModeCont.className = 'wm_hide';
			this._deleteFromDbCont.className = 'wm_hide';
		}
	},

	SetDisabling: function (type)
	{
		this._mailMode0Obj.disabled = true;
		this._mailMode1Obj.disabled = true;
		this._mailMode2Obj.disabled = true;
		this._mailMode3Obj.disabled = true;
		this._mailsOnServerDaysObj.disabled = true;
		this._deleteFromDbObj.disabled = true;
		if (type == SYNC_TYPE_NEW_HEADERS || type == SYNC_TYPE_NEW_MSGS) {
			if (type == SYNC_TYPE_NEW_MSGS) {
				this._mailMode0Obj.disabled = false;
			}
			else {
				this._mailMode1Obj.checked = true;
			}
			this._mailMode1Obj.disabled = false;
			if (this._mailMode0Obj.checked || this._mailMode2Obj.checked) {
				this._deleteFromDbObj.checked = false;
			}
			else {
				this._deleteFromDbObj.disabled = false;
			}
			if (this._mailMode1Obj.checked) {
				this._mailMode2Obj.disabled = false;
				this._mailMode3Obj.disabled = false;
				if (this._mailMode2Obj.checked) {
					this._mailsOnServerDaysObj.disabled = false;
				}
			}
		}
		else {
			this._deleteFromDbObj.checked = false;
		}
	},

	SetInputKeyPress: function (inp)
	{
	    var obj = this;
		inp.onkeypress = function (ev) { if (isEnter(ev)) obj.SaveChanges(); };
	},

	SaveChanges: function ()
	{
		if (WebMail._isDemo)
		{
			WebMail.ShowReport(DemoWarning);
			return;
		}

		/* validation */
		var emailValue = Trim(this._EmailObj.value);
		if (Validator.IsEmpty(emailValue)) {
			alert(Lang.WarningEmailFieldBlank);
			return;
		}
		if (!Validator.IsCorrectEmail(emailValue)) {
			alert(Lang.WarningCorrectEmail);
			return;
		}

		var incHostValue = Trim(this._mailIncHostObj.value);
		if (Validator.IsEmpty(incHostValue)) {
			alert(Lang.WarningIncServerBlank);
			return;
		}
		if (!Validator.IsCorrectServerName(incHostValue)) {
			alert(Lang.WarningCorrectIncServer);
			return;
		}

		var incPortValue = Trim(this._mailIncPortObj.value);
		if (Validator.IsEmpty(incPortValue)) {
			alert(Lang.WarningIncPortBlank);
			return;
		}
		if (!Validator.IsPort(incPortValue)) {
			alert(Lang.WarningIncPortNumber + Lang.DefaultIncPortNumber);
			return;
		}

		var incLoginValue = Trim(this._mailIncLoginObj.value);
		if (Validator.IsEmpty(incLoginValue)) {
			alert(Lang.WarningLoginFieldBlank);
			return;
		}

		var outHostValue = Trim(this._mailOutHostObj.value);
		if (Validator.IsEmpty(outHostValue)) {
			alert(Lang.WarningOutServerBlank);
			return;
		}
		if (!Validator.IsCorrectServerName(outHostValue)) {
			alert(Lang.WarningCorrectSMTPServer);
			return;
		}

		var outPortValue = Trim(this._mailOutPortObj.value);
		if (Validator.IsEmpty(outPortValue)) {
			alert(Lang.WarningOutPortBlank);
			return;
		}
		if (!Validator.IsPort(outPortValue)) {
			alert(Lang.WarningOutPortNumber + Lang.DefaultOutPortNumber);
			return;
		}

		var acctProp = (this._newMode) ? new CAccountProperties() : WebMail.Accounts.GetEditableAccount();
		var incPassValue = this._mailIncPassObj.value;
		var protocolValue = (-1 == acctProp.Id)
			? this._mailProtocolObj.value - 0
			: acctProp.MailProtocol;
		if (-1 != acctProp.Id && (protocolValue == WMSERVER_PROTOCOL || window.hMailServer === true)) {
			var newPassValue = this._newPassObj.value;
			var confirmPassValue = this._confirmPassObj.value;
			if ((newPassValue != confirmPassValue) && (newPassValue.length > 0 || confirmPassValue.length > 0)) {
				alert(Lang.AccountPasswordsDoNotMatch);
				return;
			}
			incPassValue = newPassValue;
		}
		else {
			if (incPassValue.length == 0) {
				alert(Lang.WarningIncPassBlank);
				return;
			}
		}

		if (!IsNum(this._mailsOnServerDaysObj.value) || this._mailsOnServerDaysObj.value < 1) {
			this._mailsOnServerDaysObj.value = '7';
		}

		var mailsOnServerDaysValue = Trim(this._mailsOnServerDaysObj.value);
		if (Validator.IsEmpty(mailsOnServerDaysValue) || !Validator.IsPositiveNumber(mailsOnServerDaysValue)) {
			alert(Lang.WarningMailsOnServerDays);
			return;
		}

		/* saving */
		var newAcctProp = new CAccountProperties();
	    newAcctProp.Copy(acctProp);

		newAcctProp.UseFriendlyNm = this._useFriendlyNmObj.checked;
		newAcctProp.FriendlyNm = this._friendlyNmObj.value;

        if (WebMail.Settings.AllowChangeSettings) {
		    newAcctProp.Email = emailValue;
		    newAcctProp.MailIncHost = incHostValue;
		    newAcctProp.MailIncPort = incPortValue - 0;
		    newAcctProp.MailIncLogin = incLoginValue;
		    newAcctProp.MailOutPort = outPortValue - 0;
		    newAcctProp.MailIncPass = incPassValue;
		    newAcctProp.Linked = acctProp.Linked;

		    newAcctProp.Id = acctProp.Id;
		    newAcctProp.DefAcct = this._useForLoginObj.checked;
		    newAcctProp.MailProtocol = protocolValue;
		    newAcctProp.MailOutAuth = this._mailOutAuthObj.checked;
		    if (this._mailMode1Obj.checked && this._mailMode2Obj.checked) {
			    newAcctProp.MailsOnServerDays = mailsOnServerDaysValue - 0;
		    }
		    else {
			    newAcctProp.MailsOnServerDays = acctProp.MailsOnServerDays;
		    }
		    if (this._mailMode0Obj.checked) {
			    newAcctProp.MailMode = 0;
		    }
		    else {
			    if (this._mailMode2Obj.checked && this._mailMode3Obj.checked) {
				    newAcctProp.MailMode = 4;
			    }
			    else if (this._mailMode3Obj.checked) {
				    newAcctProp.MailMode = 3;
			    }
			    else if (this._mailMode2Obj.checked) {
				    newAcctProp.MailMode = 2;
			    }
			    else {
				    newAcctProp.MailMode = 1;
			    }
		    }
		    newAcctProp.GetMailAtLogin = this._getmailAtLoginObj.checked;
		    if (acctProp.MailProtocol == POP3_PROTOCOL) {
			    var value = this._pop3InboxSyncTypeObj.value - 0;
			    switch (value) {
				    case SYNC_TYPE_NEW_HEADERS:
					    if (this._deleteFromDbObj.checked) value = SYNC_TYPE_ALL_HEADERS;
					    break;
				    case SYNC_TYPE_NEW_MSGS:
					    if (this._deleteFromDbObj.checked) value = SYNC_TYPE_ALL_MSGS;
					    break;
			    }
			    newAcctProp.InboxSyncType = value;
		    }

		    newAcctProp.MailOutHost = outHostValue;
		    newAcctProp.MailOutLogin = this._mailOutLoginObj.value;
		    newAcctProp.MailOutPass = this._mailOutPassObj.value;
        }
		this.NewAccountProperties = newAcctProp;
		var xml = newAcctProp.GetInXML();
		var requestName = (-1 == newAcctProp.Id) ? 'new' : 'update';
		RequestHandler(requestName, 'account', xml);
		this.hasChanges = false;
	},

	BuildPasswordTable: function (cont)
	{
		var obj = this;
		var div = CreateChild(cont, 'div', [['class', 'wm_settings_pass_frame']]);
		var tbl = CreateChild(div, 'table');
		var rowIndex = 0;
		var tr = tbl.insertRow(rowIndex++);
		var td = tr.insertCell(0);
		td.style.width = '130px';
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.AccountNewPassword + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'AccountNewPassword', ':', '');
		td = tr.insertCell(1);
		td.style.width = '368px';
		var inp = CreateChild(td, 'input', [['class', 'wm_input wm_settings_input'], ['type', 'password'], ['maxlength', '255']]);
		this.SetInputKeyPress(inp);
		inp.onchange = function () { obj.hasChanges = true; };
		this._newPassObj = inp;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.AccountConfirmNewPassword + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'AccountConfirmNewPassword', ':', '');
		td = tr.insertCell(1);
		inp = CreateChild(td, 'input', [['class', 'wm_input wm_settings_input'], ['type', 'password'], ['maxlength', '255']]);
		this.SetInputKeyPress(inp);
		inp.onchange = function () { obj.hasChanges = true; };
		this._confirmPassObj = inp;
    },

	Build: function(container, parent)
	{
		var span;
		this._parent = parent;
		var obj = this;
		this._mainForm = CreateChild(container, 'form');
		this._mainForm.onsubmit = function () { return false; };
		this._mainForm.className = 'wm_hide';
		var mainTable = CreateChild(this._mainForm, 'table');
		mainTable.className = 'wm_email_settings_edit_zone';
		var mainTr = mainTable.insertRow(0);
		var mainTd = mainTr.insertCell(0);
		mainTd.className = 'wm_email_settings_edit_zone_cell';
		var tbl = CreateChild(mainTd, 'table');
		tbl.className = 'wm_settings_properties';

		var rowIndex = 0;
		var tr = tbl.insertRow(rowIndex++);
		var td = tr.insertCell(0);
		td.style.width = '145px';
		td = tr.insertCell(1);
		td.style.width = '280px';
		td = tr.insertCell(2);
		td.style.width = '95px';

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.colSpan = 3;
		var inp = CreateChild(td, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['id', 'def_acct'], ['value', '1']]);
		var lbl = CreateChild(td, 'label', [['for', 'def_acct']]);
		lbl.innerHTML = Lang.UseForLogin;
		WebMail.LangChanger.Register('innerHTML', lbl, 'UseForLogin', '');
		this._useForLoginObj = inp;
		this._useForLoginObj.onchange = function () { obj.hasChanges = true; };
		this._useForLoginCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.MailFriendlyName + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'MailFriendlyName', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_input wm_settings_input'], ['type', 'text'], ['maxlength', '65']]);
		this.SetInputKeyPress(inp);
		inp.onchange = function () { obj.hasChanges = true; obj.hasForAccountsChanges = true; };
		this._friendlyNmObj = inp;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = '* ' + Lang.MailEmail + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'MailEmail', ':', '* ');
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_input wm_settings_input'], ['type', 'text'], ['maxlength', '255']]);
		this.SetInputKeyPress(inp);
		inp.onchange = function () { obj.hasChanges = true; obj.hasForAccountsChanges = true; };
		this._EmailObj = inp;
		this._emailCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = '* ' + Lang.MailIncHost + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'MailIncHost', ':', '* ');
		td = tr.insertCell(1);
		inp = CreateChild(td, 'input', [['class', 'wm_input'], ['type', 'text'], ['maxlength', '255']]);
		this.SetInputKeyPress(inp);
		inp.onchange = function () { obj.hasChanges = true; };
		this._mailIncHostObj = inp;
		span = CreateChild(td, 'span');
		span.innerHTML = '&nbsp;';
		var sel = CreateChild(td, 'select');
		sel.className = 'wm_hide';
		sel.onchange = function () {
			if (this.value - 0 == POP3_PROTOCOL) {
				obj._mailIncPortObj.value = POP3_PORT;
			}
			else {
				obj._mailIncPortObj.value = IMAP4_PORT;
			}
			obj.SetHiddingPop3SyncSettings(this.value - 0);
			obj.hasChanges = true;
		};
		this._mailProtocolObj = sel;
		span = CreateChild(td, 'span');
		span.className = 'wm_hide';
		this._mailProtocolSpan = span;
		td = tr.insertCell(2);
		span = CreateChild(td, 'span');
		span.innerHTML = '* ' + Lang.MailIncPort + ':';
		WebMail.LangChanger.Register('innerHTML', span, 'MailIncPort', ':', '* ');
		inp = CreateChild(td, 'input', [['class', 'wm_input wm_port_input'], ['type', 'text'], ['size', '3'], ['maxlength', '5']]);
		this.SetInputKeyPress(inp);
		this._mailIncPortObj = inp;
		this._mailIncHostCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = '* ' + Lang.MailIncLogin + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'MailIncLogin', ':', '* ');
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_input wm_settings_input'], ['type', 'text'], ['maxlength', '255']]);
		this.SetInputKeyPress(inp);
		inp.onchange = function () { obj.hasChanges = true; };
		this._mailIncLoginObj = inp;
		this._mailIncLoginCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = '* ' + Lang.MailIncPass + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'MailIncPass', ':', '* ');
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_input wm_settings_input'], ['type', 'password'], ['maxlength', '255']]);
		this.SetInputKeyPress(inp);
		inp.onchange = function () { obj.hasChanges = true; };
		this._mailIncPassObj = inp;
		this._mailIncPassCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.colSpan = 3;
		this.BuildPasswordTable(td);
		this._newPassCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = '* ' + Lang.MailOutHost + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'MailOutHost', ':', '*');
		td = tr.insertCell(1);
		inp = CreateChild(td, 'input', [['class', 'wm_input'], ['type', 'text'], ['maxlength', '255']]);
		this.SetInputKeyPress(inp);
		inp.onfocus = function () { if (this.value.length == 0) { this.value = obj._mailIncHostObj.value; this.select(); } };
		inp.onchange = function () { obj.hasChanges = true; };
		this._mailOutHostObj = inp;
		td = tr.insertCell(2);
		span = CreateChild(td, 'span');
		span.innerHTML = '* ' + Lang.MailOutPort + ':';
		WebMail.LangChanger.Register('innerHTML', span, 'MailOutPort', ':', '* ');
		inp = CreateChild(td, 'input', [['class', 'wm_input wm_port_input'], ['type', 'text'], ['size', '3'], ['maxlength', '5']]);
		this.SetInputKeyPress(inp);
		this._mailOutPortObj = inp;
		this._mailOutHostCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.MailOutLogin + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'MailOutLogin', ':', '');
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_input wm_settings_input'], ['type', 'text'], ['maxlength', '255']]);
		this.SetInputKeyPress(inp);
		inp.onchange = function () { obj.hasChanges = true; };
		this._mailOutLoginObj = inp;
		this._mailOutLoginCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.MailOutPass + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'MailOutPass', ':', '');
		td = tr.insertCell(1);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['class', 'wm_input wm_settings_input'], ['type', 'password'], ['maxlength', '255']]);
		this.SetInputKeyPress(inp);
		inp.onchange = function () { obj.hasChanges = true; };
		this._mailOutPassObj = inp;
		this._mailOutPassCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.colSpan = 3;
		inp = CreateChild(td, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['id', 'mail_out_auth'], ['value', '1']]);
		lbl = CreateChild(td, 'label', [['for', 'mail_out_auth']]);
		lbl.innerHTML = Lang.MailOutAuth1;
		WebMail.LangChanger.Register('innerHTML', lbl, 'MailOutAuth1', '', '');
		CreateChild(td, 'br');
		lbl = CreateChild(td, 'label');
		lbl.innerHTML = Lang.MailOutAuth2;
		lbl.className = 'wm_secondary_info wm_nextline_info';
		WebMail.LangChanger.Register('innerHTML', lbl, 'MailOutAuth2', '', '');
		inp.onchange = function () { obj.hasChanges = true; };
		this._mailOutAuthObj = inp;
		this._mailOutAuthCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.colSpan = 3;
		inp = CreateChild(td, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['id', 'use_friendly_nm'], ['value', '1']]);
		lbl = CreateChild(td, 'label', [['for', 'use_friendly_nm']]);
		lbl.innerHTML = Lang.UseFriendlyNm1;
		WebMail.LangChanger.Register('innerHTML', lbl, 'UseFriendlyNm1', '', '');
		lbl = CreateChild(td, 'label');
		lbl.innerHTML = Lang.UseFriendlyNm2;
		lbl.className = 'wm_secondary_info wm_inline_info';
		WebMail.LangChanger.Register('innerHTML', lbl, 'UseFriendlyNm2', '', '');
		inp.onchange = function () { obj.hasChanges = true; obj.hasForAccountsChanges = true; };
		this._useFriendlyNmObj = inp;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.colSpan = 3;
		inp = CreateChild(td, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['id', 'getmail_at_login'], ['value', '1']]);
		lbl = CreateChild(td, 'label', [['for', 'getmail_at_login']]);
		lbl.innerHTML = Lang.GetmailAtLogin;
		WebMail.LangChanger.Register('innerHTML', lbl, 'GetmailAtLogin', '', '');
		inp.onchange = function () { obj.hasChanges = true; };
		this._getmailAtLoginObj = inp;
		this._getmailAtLoginCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.colSpan = 3;
		inp = CreateChild(td, 'input', [['class', 'wm_checkbox'], ['type', 'radio'], ['id', 'mail_mode_0'], ['name', 'mail_mode'], ['value', '1']]);
		lbl = CreateChild(td, 'label', [['for', 'mail_mode_0']]);
		lbl.innerHTML = Lang.MailMode0;
		WebMail.LangChanger.Register('innerHTML', lbl, 'MailMode0', '', '');
		inp.onclick = function () {
			if (this.checked) {
				obj._mailMode2Obj.disabled = true;
				obj._mailMode3Obj.disabled = true;
				obj._mailsOnServerDaysObj.disabled = true;
				obj._deleteFromDbObj.disabled = true;
				obj._deleteFromDbObj.checked = false;
			};
			obj.hasChanges = true;
		};
		this._mailMode0Obj = inp;

		CreateChild(td, 'br');
		inp = CreateChild(td, 'input', [['class', 'wm_checkbox'], ['type', 'radio'], ['id', 'mail_mode_1'], ['name', 'mail_mode'], ['value', '1']]);
		lbl = CreateChild(td, 'label', [['for', 'mail_mode_1']]);
		lbl.innerHTML = Lang.MailMode1;
		WebMail.LangChanger.Register('innerHTML', lbl, 'MailMode1', '', '');
		inp.onclick = function () {
			if (this.checked) {
				obj._mailMode2Obj.disabled = false;
				if (obj._mailMode2Obj.checked) {
					obj._mailsOnServerDaysObj.disabled = false;
					obj._deleteFromDbObj.disabled = true;
				}
				else {
					obj._mailsOnServerDaysObj.disabled = true;
					obj._deleteFromDbObj.disabled = false;
				};
				obj._mailMode3Obj.disabled = false;
			};
			obj.hasChanges = true;
		};
		this._mailMode1Obj = inp;

		CreateChild(td, 'br');
		inp = CreateChild(td, 'input', [['class', 'wm_checkbox wm_settings_para'], ['type', 'checkbox'], ['id', 'mail_mode_2'], ['value', '1']]);
		lbl = CreateChild(td, 'label', [['for', 'mail_mode_2']]);
		lbl.innerHTML = Lang.MailMode2 + ' ';
		WebMail.LangChanger.Register('innerHTML', lbl, 'MailMode2', ' ', '');
		inp.onclick = function () {
			if (this.checked) {
				obj._mailsOnServerDaysObj.disabled = false;
				obj._deleteFromDbObj.disabled = true;
				obj._deleteFromDbObj.checked = false;
			} else {
				obj._mailsOnServerDaysObj.disabled = true;
				obj._deleteFromDbObj.disabled = false;
				var d = parseInt(obj._mailsOnServerDaysObj.value);

				if (!IsNum(obj._mailsOnServerDaysObj.value) || obj._mailsOnServerDaysObj.value < 1) {
					obj._mailsOnServerDaysObj.value = '7';
				}
			};
			obj.hasChanges = true;
		};
		this._mailMode2Obj = inp;
		inp = CreateChild(td, 'input', [['class', 'wm_input'], ['type', 'text'], ['size', '1'], ['maxlength', '6']]);
		this.SetInputKeyPress(inp);
		span = CreateChild(td, 'span');
		span.innerHTML = ' ' + Lang.MailsOnServerDays;
		WebMail.LangChanger.Register('innerHTML', span, 'MailsOnServerDays', '', ' ');
		inp.onchange = function () { obj.hasChanges = true; };
		this._mailsOnServerDaysObj = inp;
		this._mailsOnServerDaysObj.value = '7';
		CreateChild(td, 'br');
		inp = CreateChild(td, 'input', [['class', 'wm_checkbox wm_settings_para'], ['type', 'checkbox'], ['id', 'mail_mode_3'], ['value', '1']]);
		lbl = CreateChild(td, 'label', [['for', 'mail_mode_3']]);
		lbl.innerHTML = Lang.MailMode3;
		WebMail.LangChanger.Register('innerHTML', lbl, 'MailMode3', '', '');
		inp.onchange = function () { obj.hasChanges = true; };
		this._mailMode3Obj = inp;
		this._mailModeCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.colSpan = 3;
		span = CreateChild(td, 'span');
		span.innerHTML = Lang.InboxSyncType + ':&nbsp;';
		WebMail.LangChanger.Register('innerHTML', span, 'InboxSyncType', ':&nbsp;', '');
		sel = CreateChild(td, 'select');
		sel.onchange = function () {
			if (!WebMail.Settings.AllowDirectMode && obj._directModeOpt != null) {
				obj._pop3InboxSyncTypeObj.removeChild(obj._directModeOpt);
				obj._directModeOpt = null;
			};
			obj.SetDisabling(this.value - 0);
			obj.hasChanges = true;
		};
		this._pop3InboxSyncTypeObj = sel;
		this._pop3InboxSyncTypeCont = tr;

		tr = tbl.insertRow(rowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.colSpan = 3;
		inp = CreateChild(td, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['id', 'delete_from_db']]);
		lbl = CreateChild(td, 'label', [['for', 'delete_from_db']]);
		lbl.innerHTML = Lang.DeleteFromDb;
		WebMail.LangChanger.Register('innerHTML', lbl, 'DeleteFromDb', '', '');
		inp.onchange = function () { obj.hasChanges = true; };
		this._deleteFromDbObj = inp;
		this._deleteFromDbCont = tr;

		tbl = CreateChild(mainTd, 'table');
		tbl.className = 'wm_settings_buttons';
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		td.className = 'wm_secondary_info';
		td.innerHTML = Lang.InfoRequiredFields;
		WebMail.LangChanger.Register('innerHTML', td, 'InfoRequiredFields', '');
		this._requiredFieldsCont = td;
		td = tr.insertCell(1);
		inp = CreateChild(td, 'input', [['class', 'wm_button'], ['type', 'button'], ['value', Lang.Save]]);
		WebMail.LangChanger.Register('value', inp, 'Save', '', '');
		inp.onclick = function () {
			obj.SaveChanges();
		};
		span = CreateChild(td, 'span');
		span.innerHTML = ' ';
		span.className = 'wm_hide';
		inp = CreateChild(span, 'input', [['class', 'wm_button'], ['type', 'button'], ['value', Lang.Cancel]]);
		WebMail.LangChanger.Register('value', inp, 'Cancel', '', '');
		inp.onclick = function () {
			SetHistoryHandler(
				{
					ScreenId: SCREEN_USER_SETTINGS,
					SelectIdAcct: obj._idAcct,
					Entity: PART_ACCOUNT_PROPERTIES,
					NewMode: false
				}
			);
		};
		this._btnCancel = span;
	}//Build
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}
