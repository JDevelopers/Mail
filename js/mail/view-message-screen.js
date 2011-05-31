/*
 * Functions:
 *  CreateAttachViewClick(href)
 * Classes:
 *  CViewMessageScreen()
 *  CMessageViewer()
 */

function CreateAttachViewClick(href)
{
	return function () {
		var shown = window.open(href, 'Popup', 'toolbar=yes,status=no,scrollbars=yes,resizable=yes,width=760,height=480');
		shown.focus();
		return false;
	};
}

function CViewMessageScreen()
{
	this.Id = SCREEN_VIEW_MESSAGE;
	this.isBuilded = false;
	this.hasCopyright = false;
	this.BodyAutoOverflow = false;
	
	this._isInContacts = true;
	
	this._addToAddressBookImg = null;

	this._fromName = '';
	this._fromEmail = '';
	
	this._mainContainer = null;
	this._logo = null;
	this._accountsBar = null;
	this._toolBar = null;
	this._lowToolBar = null;
	this._mode = false;
	//logo + accountslist + toolbar + lowtoolbar
	this.ExternalHeight = 56 + 32 + 26 + 24;

	this._picturesControl = new CMessagePicturesController();
	this._sensivityControl = new CMessageSensivityController();

	this.SendConfirmation = function () {
		var msg = this._msgObj;
		if (msg && msg.MailConfirmationValue && msg.MailConfirmationValue.length) {
			SendConfirmationHandler(msg.MailConfirmationValue, msg.Subject);
		}
		this.ResizeScreen();
	};

	this._readConfirmationControl = new CMessageReadConfirmationController(this.SendConfirmation, this);
	this._readConfirmationTbl = null;
	
	this._completeHeadersTbl = null;
	this._shortHeadersTbl = null;
	this._isShort = false;
	this._rowPadding = 4;
	this._colPadding = 12;

	this._cFromObj = null;
	this._cToObj = null;
	this._cDateObj = null;
	this._CCObj = null;
	this._CCCont = null;
	this._BCCObj = null;
	this._BCCCont = null;
	this._replyToObj = null;
	this._replyToCont = null;
	this._cSubjectObj = null;
	this._charsetObj = null;
	this._charsetCont = null;
	this._showHeadersShower = null;
	this._sFromObj = null;
	this._sToObj = null;
	this._sDateObj = null;
	this._sSubjectObj = null;
	this._importanceImg = null;

	this._msgViewer = null;
	
	this._msgObj = null;
	this.msgId = -1;
	this.msgUid = '';
	this.FolderId = -1;
	this.FolderFullName = '';
	this.Charset = AUTOSELECT_CHARSET;
	this.Size = 0;
	this._needPlain = false;
	this._needHeaders = false;
	this._showHeaders = false;
	this._headersCont = null;
	this._headersObj = null;
	this._headersDiv = null;
	
	this._replyButton = null;
	this._replyAllButton = null;
	this._replyPopupMenu = null;
	this._forwardTool = null;
	this._saveButton = null;
	this._printButton = null;
	this._prevActButton = null;
	this._prevInactButton = null;
	this._nextActButton = null;
	this._nextInactButton = null;

	this.TrashId = -1;
	this.TrashFullName = '';
	this.Protocol = POP3_PROTOCOL;
	
	this._messageList = null;
	this.MessageIndex = -1;
	this.NeedMessageIndex  = -1;
}

CViewMessageScreen.prototype = {
	PlaceData: function(Data)
	{
		var Type = Data.Type;
		switch (Type) {
			case TYPE_MESSAGE:	
                var contact;
                contact = Data.FromContact;		
			    if (contact == null || contact.Id == -1) {
			        this._isInContacts = false;
			    }			     
			    else{
			        this._isInContacts = true;
			    }  
				this.Fill(Data);
			break;
			case TYPE_MESSAGE_LIST:
				this._messageList = Data;
				if (this.MessageIndex == -1) {
					this.FillNextPrevButtons(this._msgObj);
				}
				if (this.NeedMessageIndex != -1) {
					if (this.NeedMessageIndex == 1) {
						this.NeedMessageIndex = this._messageList.List.length - 1;
					}
					this.GetMessage(this.NeedMessageIndex);
					this.NeedMessageIndex = -1;
				}
			break;
			case TYPE_MESSAGES_OPERATION:
				if (Data && Data.GetMessageAfterDelete) {
					this.GetNextMessage(true);
				}
				/*
				SetHistoryHandler(
					{
						ScreenId: WebMail.ListScreenId
					}
				);
				*/
			break;
		}
	},
	
	Show: function ()
	{
		this._mainContainer.className = 'wm_view_message_container';
		this._toolBar.Show();
		if (WebMail.Settings.ShowTextLabels) {
			this._toolBar.ShowTextLabels();
		}
		else {
			this._toolBar.HideTextLabels();
		}
		this._addToAddressBookImg.className = (WebMail.Settings.AllowContacts && !this._isInContacts)
			? 'wm_add_address_book_img' : 'wm_hide';
		
		this.ResizeBody();
	},
	
	RestoreFromHistory: function () { },
	
	ParseSettings: function () { },

	Hide: function ()
	{
		this._mainContainer.className = 'wm_hide';
		this._toolBar.Hide();
		this.HideFullHeaders();
	},
	
	ClickBody: function() { },

	KeyupBody: function (key, ev)
	{
		switch (key) {
			case Keys.Comma:
				if (ev.shiftKey) this.GetPrevMessage();
				break;
			case Keys.Dot:
				if (ev.shiftKey) this.GetNextMessage();
				break;
			case Keys.N:
				if (ev.shiftKey || ev.ctrlKey || ev.altKey) return;
				SetHistoryHandler({ScreenId: SCREEN_NEW_MESSAGE});
				break;
			case Keys.R:
				if (ev.shiftKey || ev.ctrlKey || ev.altKey) return;
				WebMail.ReplyClick(TOOLBAR_REPLY);
				break;
			case Keys.Shift:
			case Keys.Ctrl:
				if (ev.shiftKey && ev.ctrlKey) {
					this._msgViewer.ChangeRTL();
				}
				break;
		}
	},

	ResizeBody: function (mode)
	{
		if (this.isBuilded) {
			this.ResizeScreen(mode);
			if (!Browser.IE && mode == RESIZE_MODE_ALL) {
				this.ResizeScreen();
			}
		}
	},
	
	ResizeScreen: function ()
	{
		var height = GetHeight() - this.GetExternalHeight() - this._rowPadding;
		var isAuto = false;
		if (height < 250 ) {
			height = 250;
			isAuto = true;
		}
		var width = GetWidth();
		if (width < 250 ) {
			width = 250;
			isAuto = true;
		}
		SetBodyAutoOverflow(isAuto);
		this._msgViewer.Resize(width, height);
	},
	
	GetExternalHeight: function()
	{
		var x, res = 0;
		x = this._logo.offsetHeight;
		res += (x) ? x : 0;
		x = this._accountsBar.offsetHeight;
		res += (x) ? x : 0;
		x = this._toolBar.GetHeight();
		res += (x) ? x : 0;
		x = this._picturesControl.GetHeight();
		res += (x) ? x : 0;
		x = (this._isShort)
			? this._shortHeadersTbl.offsetHeight
			: this._completeHeadersTbl.offsetHeight;
		res += (x) ? x : 0;
		x = this._lowToolBar.offsetHeight;
		res += (x) ? x : 0;
		if (res > 0) {
			this.ExternalHeight = res;
		}
		return this.ExternalHeight;
	},
	
	HideFullHeaders: function ()
	{
		this._headersSwitcher.innerHTML = Lang.ShowFullHeaders;
		this._headersCont.className = 'wm_hide';
		this._showHeaders = false;
		this._needHeaders = false;
	},
	
	SetTrashParams: function (id, name, protocol)
	{
		this.TrashId = id;
		this.TrashFullName = name;
		this.Protocol = protocol;
	},

	IsLastMessageOnPage: function (index)
	{
		index = typeof(value) == 'undefined' ? this.MessageIndex : index;
		if (index == this._messageList.List.length - 1) {
			return true;
		}

		var allNextIsNull = true;
		for (var i = index + 1; i < this._messageList.List.length; i++) {
			if (typeof this._messageList.List[i] != 'undefined') {
				allNextIsNull = false;
				break;
			}
		}

		return allNextIsNull;
	},

	IsLastPage: function ()
	{
		return (WebMail.Settings.MsgsPerPage * this._messageList.Page >= this._messageList.MessagesCount);
	},

	IsFirstMessageOnPage: function ()
	{
		if (this.MessageIndex == 0) {
			return true;
		}

		var allPrevIsNull = true;
		for (var i = this.MessageIndex - 1; i >= 0; i--) {
			if (typeof(this._messageList.List[i]) != 'undefined') {
				allPrevIsNull = false;
				break;
			}
		}

		return allPrevIsNull;
	},

	IsFirstPage: function ()
	{
		return (this._messageList.Page == 1);
	},

	DeleteCurrentMessageFromList: function ()
	{
		var isLast = false;
		if (this.IsLastMessageOnPage() && this.IsLastPage()) {
			isLast = true;
		}
		var l = this._messageList.List.length;
		this._messageList.List.splice(this.MessageIndex, 1);
		if (l > 0) {
			this._messageList.List[l - 1] = undefined;
		}
		return isLast;
	},

	IsMessageNull: function (index)
	{
		return (typeof(this._messageList.List[index]) == 'undefined');
	},

	GetNextMessage: function (notInc)
	{
		notInc = typeof(notInc) != 'undefined';
		var index = (notInc) ? 0 : 1;
		index += this.MessageIndex;
		if (this.IsMessageNull(index)){
			this.NeedMessageIndex = index;
			var pageInc = 0;
			if (notInc == false && index > this._messageList.List.length - 1) {
				this.NeedMessageIndex = 0;
				pageInc = 1;
			}

			var xml = '<folder id="' + this._messageList.FolderId + '"><full_name>' + GetCData(this._messageList.FolderFullName) + '</full_name></folder>';
			xml += '<look_for fields="' + this._messageList._searchFields + '">' + GetCData(this._messageList.LookFor) + '</look_for>';

			GetHandler(TYPE_MESSAGE_LIST, {IdAcct: WebMail._idAcct, Page: this._messageList.Page + pageInc, SortField: this._messageList.SortField,
				SortOrder: this._messageList.SortOrder, FolderId: this._messageList.FolderId,
				FolderFullName: this._messageList.FolderFullName, LookFor: this._messageList.LookFor, SearchFields: this._messageList._searchFields}, [], xml );

		}
		else {
			this.GetMessage(index);
		}
	},

	GetPrevMessage: function ()
	{
		if (this.IsFirstMessageOnPage() && this.IsFirstPage()) return;
		if (this.IsFirstMessageOnPage()) {
			this.NeedMessageIndex = 1;
			var xml = '<folder id="' + this._messageList.FolderId + '"><full_name>' + GetCData(this._messageList.FolderFullName) + '</full_name></folder>';
			xml += '<look_for fields="' + this._messageList._searchFields + '">' + GetCData(this._messageList.LookFor) + '</look_for>';
			GetHandler(TYPE_MESSAGE_LIST, {IdAcct: WebMail._idAcct, Page: this._messageList.Page - 1, SortField: this._messageList.SortField,
				SortOrder: this._messageList.SortOrder, FolderId: this._messageList.FolderId,
				FolderFullName: this._messageList.FolderFullName, LookFor: this._messageList.LookFor, SearchFields: this._messageList._searchFields}, [], xml );
		}
		else {
			this.GetMessage(this.MessageIndex - 1);
		}
	},

	GetMessage: function (index)
	{
		var msg = this._messageList.List[index];
		if (msg) {
			SetHistoryHandler(
				{
					ScreenId: SCREEN_VIEW_MESSAGE,
					MsgId: msg.Id,
					MsgUid: msg.Uid,
					MsgFolderId: msg.FolderId,
					MsgFolderFullName: msg.FolderFullName,
					MsgCharset: msg.Charset,
					MsgParts: [PART_MESSAGE_HEADERS, PART_MESSAGE_HTML, PART_MESSAGE_ATTACHMENTS]
				}
			);
		}
	},

	NextButtonView: function(isShow)
	{
		if (isShow) {
			this._nextActButton.Show();
			this._nextInactButton.Hide();
		}
		else {
			this._nextActButton.Hide();
			this._nextInactButton.Show();
		}
	},

	PrevButtonView: function(isShow)
	{
		if (isShow) {
			this._prevActButton.Show();
			this._prevInactButton.Hide();
		}
		else {
			this._prevActButton.Hide();
			this._prevInactButton.Show();
		}
	},
	
	FillNextPrevButtons: function (msg)
	{
		this.MessageIndex = (this._messageList == null) ? -1 : this._messageList.GetMessageIndex(msg);
		if (null == this._messageList || this.MessageIndex == -1) {
			this.NextButtonView(false);
			this.PrevButtonView(false);
		}
		else {
			this.PrevButtonView(!(this.IsFirstMessageOnPage() && this.IsFirstPage()));
			this.NextButtonView(!(this.IsLastMessageOnPage() && this.IsLastPage()));
		}
	},
	
	FillFullHeaders: function (fullHeaders)
	{
		this._needHeaders = false;
		this._showHeaders = true;
		this._headersSwitcher.innerHTML = Lang.HideFullHeaders;
		var height = GetHeight();
		var width = GetWidth();
		var win_height = height*3/5;
		var win_width = width*3/5;
		this._headersCont.style.width = win_width + 'px';
		this._headersCont.style.height = win_height + 'px';
		this._headersCont.style.top = (height - win_height)/2 + 'px';
		this._headersCont.style.left = (width - win_width)/2 + 'px';
		this._headersDiv.style.width = win_width - 10 + 'px';
		this._headersDiv.style.height = win_height - 30 + 'px';
		this._headersCont.className = 'wm_headers';
		if (Browser.IE) {
			this._headersObj.innerText = fullHeaders;
		}
		else {
			this._headersObj.textContent = fullHeaders;
		}
	},
	
	Fill: function (msg)
	{
		this.ResetReplyTools(msg);
		this._msgObj = msg;
		var screen = WebMail.Screens[SCREEN_MESSAGE_LIST_TOP_PANE];
		if (screen) {
			screen._msgObj = msg;
		}
		screen = WebMail.Screens[SCREEN_MESSAGE_LIST_CENTRAL_PANE];
		if (screen) {
			screen._msgObj = msg;
		}
		this.FillNextPrevButtons(msg);
		if (this._needHeaders) {
			this.FillFullHeaders(msg.FullHeaders);
		}
		else {
			this.HideFullHeaders();
		}
		this.msgId = msg.Id;
		this.msgUid = msg.Uid;
		this.FolderId = msg.FolderId;
		this.FolderFullName = msg.FolderFullName;
		this.Charset = msg.Charset;
		this.Size = msg.Size;
		this._importanceImg.className = (msg.Importance == 1)
			? 'wm_importance_img' : 'wm_hide';
		this._cFromObj.innerHTML = msg.FromAddr;
		//email parts for adding to contacts
		var fromParts = GetEmailParts(HtmlDecode(msg.FromAddr));
		this._fromName = fromParts.Name;
		this._fromEmail = fromParts.Email;

		this.FillMessageInfo(this._msgObj);

		this._cToObj.innerHTML = msg.ToAddr;
		this._cDateObj.innerHTML = msg.FullDate;
		if (msg.CCAddr.length != 0) {
			this._CCObj.innerHTML = msg.CCAddr;
			this._CCCont.className = '';
		}
		else {
			this._CCCont.className = 'wm_hide';
		}
		if (msg.BCCAddr.length != 0) {
			this._BCCObj.innerHTML = msg.BCCAddr;
			this._BCCCont.className = '';
		}
		else {
			this._BCCCont.className = 'wm_hide';
		}
		if (msg.ReplyToAddr.length != 0 && msg.ReplyToAddr != msg.FromAddr) {
			this._replyToObj.innerHTML = msg.ReplyToAddr;
			this._replyToCont.className = '';
		}
		else {
			this._replyToCont.className = 'wm_hide';
		}
		this._cSubjectObj.innerHTML = (msg.Subject == '')
			? '<span class="wm_no_subject">' + Lang.MessageNoSubject + '</span>'
			: msg.Subject;
		if (msg.HasCharset && msg.Charset == -1) {
			this._charsetCont.className = 'wm_hide';
			this._showHeadersShower.className = 'wm_control_img';
		}
		else {
			this._charsetCont.className = '';
			this._showHeadersShower.className = 'wm_hide';
			this._charsetObj.value = msg.Charset;
			this._charsetObj.blur();
		}

		this._sFromObj.innerHTML = (HtmlDecode(msg.FromAddr).length <= 20)
			? '&nbsp;' + msg.FromAddr + '&nbsp;' : '&nbsp;' + HtmlEncode(HtmlDecode(msg.FromAddr).substr(0, 20)) + '...&nbsp;';

		this._sToObj.innerHTML = (HtmlDecode(msg.ToAddr).length <= 20)
			? '&nbsp;' + msg.ToAddr + '&nbsp;' : '&nbsp;' + HtmlEncode(HtmlDecode(msg.ToAddr).substr(0, 20)) + '...&nbsp;';

		this._sDateObj.innerHTML = '&nbsp;' + msg.Date + '&nbsp;';

		if (msg.Subject == '') {
			this._sSubjectObj.innerHTML = '<span class="wm_no_subject">' + Lang.MessageNoSubject + '</span>';
		}
		else {
			var decodedSubj = HtmlDecode(msg.Subject);
			if (decodedSubj.length <= 20) {
				this._sSubjectObj.innerHTML = '&nbsp;' + msg.Subject + '&nbsp;';
			}
			else {
				var cutSubj = HtmlEncode(decodedSubj.substr(0, 20));
				this._sSubjectObj.innerHTML = '&nbsp;' + cutSubj + '...&nbsp;';
			}
		}

		this._msgViewer.Fill(msg);
		
		var saveFunc = function () {};
		if (msg.SaveLink != '#') {
			saveFunc = this.CreateSaveLinkFunc(msg.SaveLink);
		}
		this._saveButton.ChangeHandler(saveFunc);

		var printFunc = this.CreatePrintLinkFunc(msg.PrintLink, msg);
		this._printButton.ChangeHandler(printFunc);
		
		this.ResizeBody();
	},
	
	CreateSaveLinkFunc: function (link)
	{
		return function () { document.location = link; };
	},
	
	CreatePrintLinkFunc: function (link, msg)
	{
		if (link != '#') {
			return function () { return PopupPrintMessage(link); };
		}
		return function () {
			var headers = '<link rel="stylesheet" href="./skins/' + WebMail.Settings.DefSkin;
			headers += '/styles.css" type="text/css" />';

			var body = '<div align="center" class="wm_space_before"><table class="wm_print">';
			body += '<tr><td class="wm_print_title">' + Lang.From;
			body += ': </td><td class="wm_print_value">' + msg.FromAddr + '</td></tr>';
			body += '<tr><td class="wm_print_title">' + Lang.To;
			body += ': </td><td class="wm_print_value">' + msg.ToAddr + '</td></tr>';
			if (msg.CCAddr.length > 0) {
				body += '<tr><td class="wm_print_title">' + Lang.CC;
				body += ': </td><td class="wm_print_value">' + msg.CCAddr + '</td></tr>';
			}
			body += '<tr><td class="wm_print_title">' + Lang.Date;
			body += ': </td><td class="wm_print_value">' + msg.Date + '</td></tr>';
			body += '<tr><td class="wm_print_title">' + Lang.Subject;
			body += ': </td><td class="wm_print_value">' + msg.Subject + '</td></tr>';

			var attArray = Array();
			var iCount = msg.Attachments.length;
			for (var i=0; i<iCount; i++) {
				attArray.push(msg.Attachments[i].FileName);
			}
			var attStr = attArray.join(', ');
			if (attStr.length > 0) {
				body += '<tr><td class="wm_print_title">' + Lang.Attachments;
				body += ': </td><td class="wm_print_value">' + attStr + '</td></tr>';
			}

			var messageBody = (msg.HasHtml) ? msg.HtmlBody : msg.PlainBody;
			body += '<tr><td colspan="2" class="wm_print_body"><div class="wm_space_before">';
			body += messageBody + '</div></td></tr></table></div></body></html>';

			WindowOpener.OpenAndWrite('PopupPrintMessage', headers, body, ' class="wm_body"');
		};
	},
	
	FillCharset: function (charset)
	{
		var sel = this._charsetObj;
		CleanNode(sel);
		var opt, obj = this;
		for (var i in Charsets) {
			var value = (Charsets[i].Value == 0) ? AUTOSELECT_CHARSET : Charsets[i].Value;
			opt = CreateChild(sel, 'option', [['value', value]]);
			opt.innerHTML = Charsets[i].Name;
			opt.selected = (charset == value);
		}
		sel.onchange = function () {
			SetHistoryHandler(
				{
						ScreenId: SCREEN_VIEW_MESSAGE,
						MsgId: obj.msgId,
						MsgUid: obj.msgUid,
						MsgFolderId: obj.FolderId,
						MsgFolderFullName: obj.FolderFullName,
						MsgCharset: this.value,
						MsgSize: obj.Size,
						MsgParts: [PART_MESSAGE_HEADERS, PART_MESSAGE_HTML, PART_MESSAGE_ATTACHMENTS]
				}
			);
		};
	},

	ShowCompleteHeaders: function ()
	{
		this._completeHeadersTbl.className = 'wm_view_message';
		this._shortHeadersTbl.className = 'wm_hide';
		this._isShort = false;
		this.ResizeBody();
	},
	
	ShowShortHeaders: function ()
	{
		this._completeHeadersTbl.className = 'wm_hide';
		this._shortHeadersTbl.className = 'wm_view_message';
		this._isShort = true;
		this.ResizeBody();
	},

	Build: function (container, accountsBar, PopupMenus)
	{
		var obj = this;

		this._logo = document.getElementById('logo');
		this._accountsBar = accountsBar;

		this._buildToolBar(container, PopupMenus);

		this._mainContainer = CreateChild(container, 'div');
		this._mainContainer.className = 'wm_hide';

		this._picturesControl.Build(this._mainContainer);
		this._sensivityControl.Build(this._mainContainer);
		this._readConfirmationTbl = this._readConfirmationControl.Build(this._mainContainer);

		var tbl = CreateChild(this._mainContainer, 'table');
		this._completeHeadersTbl = tbl;
		tbl.className = 'wm_view_message';
		var tr = tbl.insertRow(0);
		var td = tr.insertCell(0);
		td.className = 'wm_view_message_title';
		td.innerHTML = Lang.From + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'From', ':');
		td = tr.insertCell(1);
		var span = CreateChild(td, 'span');
		this._cFromObj = span;
		var img = CreateChild(td, 'span', [['class', 'wm_add_address_book_img'], ['title', Lang.AddToAddressBook]]);
		img.innerHTML = '&nbsp;';
		WebMail.LangChanger.Register('title', img, 'AddToAddressBook', '');
		img.onclick = function () {
			SetHistoryHandler(
				{
					ScreenId: SCREEN_CONTACTS,
					Entity: PART_NEW_CONTACT,
					Name: obj._fromName,
					Email: obj._fromEmail
				}
			);
		};
		this._addToAddressBookImg = img;

		td = tr.insertCell(2);
		td.className = 'wm_headers_switcher';
		var nobr = CreateChild(td, 'nobr');
		var a = CreateChild(nobr, 'a', [['href', '#']]);
		a.onclick = function () {
			if (obj._showHeaders) {
				obj.HideFullHeaders();
			}
			else {
				obj._needHeaders = true;
				GetMessageHandler(obj.msgId, obj.msgUid, obj.FolderId, obj.FolderFullName, [PART_MESSAGE_FULL_HEADERS], obj.Charset);
			}
			return false;
		};
		a.innerHTML = Lang.ShowFullHeaders;
		this._headersSwitcher = a;
		
		tr = tbl.insertRow(1);
		td = tr.insertCell(0);
		td.className = 'wm_view_message_title';
		td.innerHTML = Lang.To + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'To', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		this._cToObj = td;

		tr = tbl.insertRow(2);
		td = tr.insertCell(0);
		td.className = 'wm_view_message_title';
		td.innerHTML = Lang.Date + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Date', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		this._cDateObj = td;

		tr = tbl.insertRow(3);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_view_message_title';
		td.innerHTML = Lang.CC + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'CC', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		this._CCObj = td;
		this._CCCont = tr;

		tr = tbl.insertRow(4);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_view_message_title';
		td.innerHTML = Lang.BCC + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'BCC', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		this._BCCObj = td;
		this._BCCCont = tr;

		tr = tbl.insertRow(5);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_view_message_title';
		td.innerHTML = Lang.ReplyTo + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'ReplyTo', ':');
		td = tr.insertCell(1);
		td.colSpan = 2;
		this._replyToObj = td;
		this._replyToCont = tr;

		var imgDiv;
		tr = tbl.insertRow(6);
		td = tr.insertCell(0);
		td.className = 'wm_view_message_title';
		td.innerHTML = Lang.Subject + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Subject', ':');
		td = tr.insertCell(1);
		var imgSpan = CreateChild(td, 'span', [['class', 'wm_importance_img']]);
		imgSpan.innerHTML = '&nbsp;';
		this._importanceImg = imgSpan;
		span = CreateChild(td, 'span');
		this._cSubjectObj = span;
		td = tr.insertCell(2);
		td.className = 'wm_view_message_switcher';
		imgDiv = CreateChild(td, 'div', [['class', 'wm_view_message_close_mode wm_control_img']]);
		imgDiv.onclick = function () {obj.ShowShortHeaders();};
		this._showHeadersShower = imgDiv;

		tr = tbl.insertRow(7);
		td = tr.insertCell(0);
		td.className = 'wm_view_message_title';
		td.innerHTML = Lang.Charset + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Charset', ':');
		td = tr.insertCell(1);
		var sel = CreateChild(td, 'select');
		sel.className = 'wm_view_message_select';
		this._charsetObj = sel;
		this.FillCharset(AUTOSELECT_CHARSET);
		td = tr.insertCell(2);
		td.className = 'wm_view_message_switcher';
		imgDiv = CreateChild(td, 'div', [['class', 'wm_view_message_close_mode wm_control_img']]);
		imgDiv.onclick = function () {obj.ShowShortHeaders();};
		this._charsetCont = tr;
		
		tbl = CreateChild(this._mainContainer, 'table');
		this._shortHeadersTbl = tbl;
		tbl.className = 'wm_hide';
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		td.colSpan = 2;
		var font = CreateChild(td, 'font');
		font.innerHTML = Lang.From + ':';
		WebMail.LangChanger.Register('innerHTML', font, 'From', ':');
		span = CreateChild(td, 'span');
		this._sFromObj = span;
		font = CreateChild(td, 'font');
		font.innerHTML = Lang.To + ':';
		WebMail.LangChanger.Register('innerHTML', font, 'To', ':');
		span = CreateChild(td, 'span');
		this._sToObj = span;
		font = CreateChild(td, 'font');
		font.innerHTML = Lang.Date + ':';
		WebMail.LangChanger.Register('innerHTML', font, 'Date', ':');
		span = CreateChild(td, 'span');
		this._sDateObj = span;
		font = CreateChild(td, 'font');
		font.innerHTML = Lang.Subject + ':';
		WebMail.LangChanger.Register('innerHTML', font, 'Subject', ':');
		span = CreateChild(td, 'span');
		this._sSubjectObj = span;
		td = tr.insertCell(1);
		td.className = 'wm_view_message_switcher';
		imgDiv = CreateChild(td, 'div', [['class', 'wm_view_message_open_mode wm_control_img']]);
		imgDiv.onclick = function () {obj.ShowCompleteHeaders();};

		tbl = CreateChild(this._mainContainer, 'table');
		tbl.className = 'wm_message_viewer';
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		td.className = 'wm_message_viewer_cell';
		this._msgViewer = new CMessageViewer();
		this._msgViewer.Build(td, 15);

		tr = tbl.insertRow(1);
		this._lowToolBar = tr;
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.colSpan = 2;
		span = CreateChild(td, 'span');
		span.className = 'wm_lowtoolbar_plain_html';
		a = CreateChild(span, 'a', [['href', '#']]);
		a.innerHTML = Lang.SwitchToPlain;
		a.onclick = function () {
			obj._msgViewer.SwitchToHtmlPlain();
			return false;
		};

		this._msgViewer.SetSwitcher(tr, 'wm_lowtoolbar', a);
		
		var div = CreateChild(document.body, 'div');
		this._headersCont = div;
		div.className = 'wm_hide';
		var div1 = CreateChild(div, 'div');
		this._headersDiv = div1;
		div1.className = 'wm_message_rfc822';
		var pre = CreateChild(div1, 'pre');
		this._headersObj = pre;
		div1 = CreateChild(div, 'div');
		div1.className = 'wm_hide_headers';
		a = CreateChild(div1, 'a', [['href', '#']]);
		a.onclick = function () {
			obj.HideFullHeaders();
			return false;
		};
		a.innerHTML = Lang.Close;
		WebMail.LangChanger.Register('innerHTML', a, 'Close', '');
		
		this.isBuilded = true;
	},

	_buildToolBar: function (container, PopupMenus)
	{
		var obj = this;
		var toolBar = new CToolBar(container);
		this._toolBar = toolBar;

		toolBar.AddItem(TOOLBAR_BACK_TO_LIST, function () {
			SetHistoryHandler(
				{
					ScreenId: WebMail.ListScreenId,
					FolderId: null
				}
			);
		}, true);

		toolBar.AddItem(TOOLBAR_NEW_MESSAGE, NewMessageClickHandler, true);
		
		var replyParts = toolBar.AddReplyItem(PopupMenus, true);
		this._replyButton = replyParts.ReplyButton;
		this._replyAllButton = replyParts.ReplyAllButton;
		this._replyPopupMenu = replyParts.ReplyPopupMenu;

		this._forwardTool = toolBar.AddItem(TOOLBAR_FORWARD, CreateReplyClick(TOOLBAR_FORWARD), true);
		this._printButton = toolBar.AddItem(TOOLBAR_PRINT_MESSAGE, null, true);
		this._saveButton = toolBar.AddItem(TOOLBAR_SAVE_MESSAGE, null, true);

		toolBar.AddItem(TOOLBAR_DELETE, function () {
			if (confirm(Lang.ConfirmAreYouSure)) {
				var operation = new COperationMessages();
				operation.FolderId = obj.FolderId;
				operation.FolderFullName = obj.FolderFullName;
				operation.Messages.setVal(obj.FolderId + obj.FolderFullName, {IdArray: [{Id: obj.msgId, Uid: obj.msgUid, Charset: obj.Charset, Size: obj.Size}], 
					FolderId: obj.FolderId, FolderFullName: obj.FolderFullName});

				operation.GetMessageAfterDelete = !obj.DeleteCurrentMessageFromList();
				RequestHandler('operation_messages', OperationTypes[TOOLBAR_DELETE], operation.GetInXML());
				if (!operation.GetMessageAfterDelete) {
					SetHistoryHandler(
						{
							ScreenId: WebMail.ListScreenId
						}
					);
				}
			}
		}, true);

		this._prevActButton = toolBar.AddItem(TOOLBAR_PREV_ACTIVE, function () {obj.GetPrevMessage();}, false, false);
		this._prevInactButton = toolBar.AddItem(TOOLBAR_PREV_INACTIVE, null, false, false);
		this._nextActButton = toolBar.AddItem(TOOLBAR_NEXT_ACTIVE, function () {obj.GetNextMessage();}, false, false);
		this._nextInactButton = toolBar.AddItem(TOOLBAR_NEXT_INACTIVE, null, false, false);
		
		toolBar.AddClearDiv();
		toolBar.Hide();
	}
};

CViewMessageScreen.prototype.ResetReplyTools = MessageListPrototype.ResetReplyTools;
CViewMessageScreen.prototype.ShowPictures = MessageListPrototype.ShowPictures;
CViewMessageScreen.prototype.FillMessageInfo = MessageListPrototype.FillMessageInfo;

function CMessageViewer()
{
	this._mainContainer = null;
	this._attachCont = null;
	this._resizerCont = null;
	this._hasAttachments = false;
	this._msgViewUseIframe = false;
	this._msgCont = null;
	this._msgObjCont = null;
	this._resizerObj = null;
	this._attachWidth = 140;
	this._minAttachWidth = 10;
	this._minMessWidth = 40;
	this._resizerWidth = 2;
	this._msgPadding = 16;
	this._colPadding = 0;

	this._switcherCont = null;
	this._switcherClass = '';
	this._switcherObj = null;
	this._htmlMode = false;
	this._needPlain = false;

	this._messageId = -1;
	this._messageUid = '';
	this._folderId = -1;
	this._folderFullName = '';
	this._msgCharset = -1;
	this._msgSize = 0;
	this.RTL = false;
	this.overMsgBody = false;
	this.focusMsgBody = false;
}

CMessageViewer.prototype = {
	ScrollDown: function ()
	{
		var scrollTop1 = GetScrollY(this._msgCont);
		this._msgCont.scrollTop = scrollTop1 + this._msgCont.offsetHeight - 15;
		var scrollTop2 = GetScrollY(this._msgCont);
		if (scrollTop1 == scrollTop2) return false;
		return true;
	},
	
	SwitchToHtmlPlain: function ()
	{
		var part = PART_MESSAGE_MODIFIED_PLAIN_TEXT;
		this._needPlain = true;
		if (!this._htmlMode) {
			this._needPlain = false;
			part = PART_MESSAGE_HTML;
		}
		GetMessageHandler(this._messageId, this._messageUid, this._folderId, this._folderFullName, [part], this._msgCharset);
	},
	
	SwitchToHtmlPlainInNewWindow: function ()
	{
		if (this._htmlMode) {
			this._needPlain = true;
		}
		else {
			this._needPlain = false;
		}
	},

	Resize: function (width, height)
	{
		this.ResizeWidth(width);
		this.ResizeHeight(height);
	},
	
	ResizeWidth: function (width)
	{
		var atWidth;
		if (this._resizerObj != null) {
			atWidth = this._resizerObj._leftShear;
		}
		else {
			atWidth = this._attachWidth;
		}

		var maxAttachWidth = width - this._minMessWidth - this._resizerWidth - this._colPadding;
		if (atWidth > maxAttachWidth) atWidth = maxAttachWidth;
		
		this._mainContainer.style.width = width - this._colPadding + 'px';

		if (this._hasAttachments) {
			this._attachCont.style.left = '0px';
			this._attachCont.style.width = atWidth + 'px';
			if (this._msgViewUseIframe) {
				this._msgCont.style.width = (width - atWidth - this._resizerWidth - this._colPadding) + 'px';
			}
			else {
				this._msgCont.style.width = (width - atWidth - this._resizerWidth - this._colPadding - this._msgPadding) + 'px';
			}
			
			this._msgCont.style.left = (atWidth + this._resizerWidth) + 'px';
			this._resizerCont.style.left = atWidth + 'px';
			this._resizerCont.style.width = this._resizerWidth + 'px';
		}
		else {
			this._msgCont.style.left = '0px';
			if (this._msgViewUseIframe) {
				this._msgCont.style.width = (width - this._colPadding) + 'px';
			}
			else {
				this._msgCont.style.width = (width - this._colPadding - this._msgPadding) + 'px';
			}
			
		}
		var clientWidth = this._msgCont.clientWidth;
		if (!this._msgViewUseIframe && clientWidth > 18) {
			this._msgObjCont.style.width = (clientWidth - 18) + 'px';
		}
	},
	
	ResizeHeight: function (height)
	{
		this._mainContainer.style.height = height + 'px';
		this._attachCont.style.height = height + 'px';
		this._resizerCont.style.height = height + 'px';
		if (this._msgViewUseIframe) {
			this._msgCont.style.height = height + 'px';
		}
		else {
			this._msgCont.style.height = (height - this._msgPadding) + 'px';
		}
	},
	
	Fill: function (msg)
	{
		this._messageId = msg.Id;
		this._messageUid = msg.Uid;
		this._folderId = msg.FolderId;
		this._folderFullName = msg.FolderFullName;
		this._msgCharset = msg.Charset;
		this._msgSize = msg.Size;
		this.RTL = msg.RTL;
		this.UpdateDirClass();
		
		CleanNode(this._attachCont);
		if (msg.Attachments.length == 0) {
			this._hasAttachments = false;
			this._attachCont.className = 'wm_hide';
			this._resizerCont.className = 'wm_hide';
		}
		else {
			this._hasAttachments = true;
			this._attachCont.className = 'wm_message_attachments';
			this._resizerCont.className = 'wm_vresizer_mess';
			var div;
			for (var i in msg.Attachments) {
				div = CreateChild(this._attachCont, 'div', [['style', 'float: left;']]);
				var fileName = msg.Attachments[i].FileName;
				var size = GetFriendlySize(msg.Attachments[i].Size);
				var params = GetFileParams(fileName);
				var title = Lang.ClickToDownload + ' ' + fileName + ' (' + size + ')';
				if (fileName.length > 16) {
					fileName = fileName.substring(0, 15) + '&#8230;';
				}
				var a = CreateChild(div, 'a', [['href', msg.Attachments[i].Download], ['class', 'wm_attach_download_a']]);
				a.onfocus = function () {this.blur();};
				a.innerHTML = '<div style="background-position: -' + params.x + 'px -' + params.y + 'px;" title="' + title + '"></div><span title="' + title + '">' + fileName + '</span>';
				if (params.view && msg.Attachments[i].View != '#') {
					CreateChild(div, 'br');
					a = CreateChild(div, 'a', [['href', ''], ['class', 'wm_attach_view_a']]);
					a.innerHTML = Lang.View;
					a.onclick = CreateAttachViewClick(msg.Attachments[i].View);
				}
			}
			div = CreateChild(this._attachCont, 'div', [['style', 'clear: left; height: 1px;']]);
		}

		this.ClearMessageCont();
		if (msg.HasHtml) {
			if (msg.HasPlain) {
				this._switcherCont.className = this._switcherClass;
				if (this._needPlain) {
					this.WriteMessageCont(msg.PlainBody);
					this._switcherObj.innerHTML = Lang.SwitchToHTML;
					this._htmlMode = false;
					this._needPlain = false;
				}
				else {
					this.WriteMessageCont(msg.HtmlBody);
					this._switcherObj.innerHTML = Lang.SwitchToPlain;
					this._htmlMode = true;
				}
			} else {
				//this._switcherCont.className = 'wm_hide';
				this.WriteMessageCont(msg.HtmlBody);
				this._switcherCont.className = this._switcherClass;
				this._switcherObj.innerHTML = '';
				this._htmlMode = true;
			}
		}
		else {
			//tag pre was removed because server modifications
			this.WriteMessageCont((msg.HasPlain) ? msg.PlainBody : '');

			// this._switcherCont.className = 'wm_hide';
			this._switcherCont.className = this._switcherClass;
			this._switcherObj.innerHTML = '';
			this._htmlMode = false;
		}
		this._msgCont.scrollTop = 0;
		return this._htmlMode;
	},

	ChangeRTL: function ()
	{
		if (!window.RTL) return;
		this.RTL = !this.RTL;
		this.UpdateDirClass();
	},

	WriteMessageCont: function (html)
	{
		if (this._msgViewUseIframe) {
			this._msgCont.contentWindow.document.body.innerHTML = html
		} else {
			this._msgObjCont.innerHTML = html;
		}
	},

	ClearMessageCont: function ()
	{
		if (this._msgViewUseIframe) {
			CleanNode(this._msgCont.contentWindow.document.body);
		}
		else {
			CleanNode(this._msgObjCont);
		}
	},

	UpdateDirClass: function()
	{
		if (this._msgViewUseIframe) {
			this._msgCont.contentWindow.document.body.className = (this.RTL) ? 'wm_message_body_rtl' : 'wm_message_body_ltr';
		}
		else {
			this._msgObjCont.className = (this.RTL) ? 'wm_message_body_rtl' : 'wm_message_body_ltr';
		}
	},
	
	Clean: function (strValue)
	{
		this._attachCont.innerHTML = '';
		this._attachCont.className = 'wm_hide';
		this._resizerCont.className = 'wm_hide';
		this._hasAttachments = false;
		if (typeof(strValue) != 'string') {
			strValue = '';
		}
		this.ClearMessageCont();
		this.WriteMessageCont(strValue);
		this._switcherCont.className = 'wm_hide';
		this._htmlMode = false;
		this._needPlain = false;

		this.RTL = (window.RTL) ? window.RTL : false;
		this.UpdateDirClass();
	},
	
	SetSwitcher: function (sCont, sClass, sObj)
	{
		this._switcherCont = sCont;
		this._switcherClass = sClass;
		this._switcherObj = sObj;
	},
	
	Build: function (container, colP)
	{
		this._colPadding = colP;
		
		var div = CreateChild(container, 'div');
		this._mainContainer = div;
		div.style.position = 'relative';
		div.className = 'wm_view_message_container';

		var atDiv = CreateChild(div, 'div');
		this._attachCont = atDiv;
		atDiv.style.position = 'absolute';
		atDiv.style.top = '0px';
		atDiv.className = 'wm_hide';

		var resDiv = CreateChild(div, 'div');
		this._resizerCont = resDiv;
		resDiv.style.position = 'absolute';
		resDiv.style.top = '0px';
		resDiv.className = 'wm_hide';
		this._hasAttachments = false;

		var mesDiv;
		if (this._msgViewUseIframe) {
			mesDiv = CreateChild(div, 'iframe');
		}
		else {
			mesDiv = CreateChild(div, 'div');
			mesDiv.className = 'wm_message';
		}

		this._msgCont = mesDiv;
		this._msgCont.style.position = 'absolute';
		this._msgCont.style.top = '0px';
		this._msgCont.style.border = 'solid 0px white';
		
		var obj = this;
		if (this._msgViewUseIframe) {
			this._msgCont.onmouseover = function () {obj.overMsgBody = true;};
			this._msgCont.onmouseout = function () {obj.overMsgBody = false;};
			this._msgCont.onfocus = function () {obj.focusMsgBody = true;};
			this._msgCont.onblur = function () {obj.focusMsgBody = false;};
		}
		else {
			this._msgObjCont = CreateChild(mesDiv, 'div');
			this._msgObjCont.onmouseover = function () {obj.overMsgBody = true;};
			this._msgObjCont.onmouseout = function () {obj.overMsgBody = false;};
			this._msgObjCont.onfocus = function () {obj.focusMsgBody = true;};
			this._msgObjCont.onblur = function () {obj.focusMsgBody = false;};
		}

		this._resizerObj = new CVerticalResizer(resDiv, div, this._resizerWidth, this._minAttachWidth, this._minMessWidth, this._attachWidth, "WebMail.ResizeBody(RESIZE_MODE_MSG_PANE);", 1);
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}