/*
 * Objects:
 *  WebMail
 * Functions:
 *  ShowPicturesHandler(safety)
 *  BodyLoaded()
 * Classes:
 *  CPreviewPaneInNewWindow()
 */
 
var isBodyLoaded = false;

WebMail.Init = function ()
{
	this.LangChanger = {
		Register: function () {}
	};
	this._html = document.getElementById('html');
	this.FadeEffect = new CFadeEffect('WebMail.FadeEffect');
	this.InfoContainer = new CInfoContainer('WebMail.InfoContainer', this.FadeEffect);
	this.Accounts = new CAccounts(CurrentAccount);
	this.SetTitle();
	var dataTypes = [
		new CDataType(TYPE_CONTACTS, true, 5, false, { Page: 'page', SortField: 'sort_field', SortOrder: 'sort_order' }, 'contacts_groups' )
	];
	this.DataSource = new CDataSource( dataTypes, ActionUrl, ErrorHandler, LoadHandler, TakeDataHandler, ShowLoadingInfoHandler );
	this.PopupMenus = new CPopupMenus();
	this.HideInfo();
};

WebMail.ClickBody = function (ev)
{
	if (!isBodyLoaded) return;
	if (WebMail.ScreenId == SCREEN_NEW_MESSAGE) {
		NewMessageScreen.ClickBody(ev);
	}
	if (WebMail.PopupMenus) {
		WebMail.PopupMenus.checkShownItems();
	}
};

WebMail.ShowError = function(errorDesc)
{
	this.InfoContainer.ShowError(errorDesc);
	if (WebMail.ScreenId == SCREEN_NEW_MESSAGE) {
		NewMessageScreen.SetErrorHappen();
	}
	else {
		PreviewPane.ResetFlags();
	}
};

WebMail.HideError = function()
{
	this.InfoContainer.HideError();
};

WebMail.ShowInfo = function(info)
{
	this.InfoContainer.ShowInfo(info);
};

WebMail.HideInfo = function()
{
	this.InfoContainer.HideInfo();
};

WebMail.ShowReport = function(report, priorDelay)
{
	this.InfoContainer.ShowReport(report, priorDelay);
};

WebMail.HideReport = function()
{
	this.InfoContainer.HideReport();
};

WebMail.SetTitle = function ()
{
	var strTitle = (window.ViewMessage && ViewMessage.Subject) ? ViewMessage.Subject : '';
	var titleLangField = (window.ViewMessage) ? Screens[SCREEN_VIEW_MESSAGE].TitleLangField
		: Screens[SCREEN_NEW_MESSAGE].TitleLangField;
	document.title = (strTitle != '')
		? strTitle + ' - ' + this._title + ' - ' + Lang[titleLangField]
		: this._title + ' - ' + Lang[titleLangField];
};

WebMail.ResizeBody = function ()
{
	if (!isBodyLoaded) return;
	if (WebMail.ScreenId == SCREEN_NEW_MESSAGE) {
		NewMessageScreen.ResizeBody();
	}
	else {
		PreviewPane.Resize();
	}
	if (WebMail.InfoContainer) {
		WebMail.InfoContainer.Resize();
	}
};

WebMail.SwitchToHtmlPlain = function ()
{
	PreviewPane.SwitchToHtmlPlain();
};

WebMail.PlaceData = function (Data)
{
	switch (Data.Type) {
		case TYPE_UPDATE:
			switch (Data.Value) {


				case 'send_message':
					this.ShowReport(Lang.ReportMessageSent);

					window.opener.ClearSentAndDraftsHandler();
					break;
				case 'save_message':
					this.ShowReport(Lang.ReportMessageSaved);
					window.opener.ClearDraftsAndSetMessageId(Data.Id, Data.Uid);
					if (WebMail.ScreenId == SCREEN_NEW_MESSAGE) {
						NewMessageScreen.SetMessageId(Data.Id, Data.Uid);
					}
					else {
						PreviewPane.SetMessageId(Data.Id, Data.Uid);
					}
					break;
			}
		break;
		case TYPE_CONTACTS:
			NewMessageScreen.PlaceData(Data);
			break;
	}
}

WebMail.ReplyMessageClick = function (type, msg, text)
{
	if (msg == null) {
		return;
	}
	if (msg.NoReply && type == TOOLBAR_REPLY || msg.NoReplyAll && type == TOOLBAR_REPLYALL) {
		return;
	}
	if ((msg.Sensivity != SENSIVITY_NOTHING || msg.NoForward) && type == TOOLBAR_FORWARD) {
		return;
	}
	if (text == undefined) text = '';
	PreviewPane.Hide();
	NewMessageScreen.Build(document.body, null, WebMail.PopupMenus);
	NewMessageScreen.Show();
	NewMessageScreen.UpdateMessageForReply(msg, type, text);
	WebMail.ScreenId = SCREEN_NEW_MESSAGE;
	setTimeout('NewMessageScreen.ResizeBody();', 1000);
};

function ShowPicturesHandler(safety)
{
	PreviewPane.ShowPictures(safety);
}

function LoadHandler() {
	WebMail.DataSource.ParseXML(this.responseXML, this.responseText);
}

function ErrorHandler() {
	WebMail.ShowError(this.ErrorDesc);
}

function ShowLoadingInfoHandler() {
    var infoMessage = Lang.Loading;
    if (this.request == 'message') {
        switch (this.action) {
            case 'save':
                infoMessage = Lang.Saving;
                break;
            case 'send':
                infoMessage = Lang.Sending;
                break;
        }
    }
	WebMail.ShowInfo(infoMessage);
}

function TakeDataHandler() {
	if (this.Data) {
		WebMail.PlaceData(this.Data);
	}
}

function RequestHandler(action, request, xml) {
	WebMail.DataSource.Request({action: action, request: request}, xml);
}

function BodyLoaded()
{
	Browser = new CBrowser();
	window.onresize = WebMail.ResizeBody;
	document.body.onclick = WebMail.ClickBody;
	WebMail.Init();
	HtmlEditorField.Build(UseDb);
	switch (OpenMode) {
		case 'view':
			PreviewPane = new CPreviewPaneInNewWindow();
			NewMessageScreen = new CNewMessageScreen();
			WebMail.ScreenId = SCREEN_VIEW_MESSAGE;
			break;
		case 'new':
			NewMessageScreen = new CNewMessageScreen();
			NewMessageScreen.Build(document.body, null, WebMail.PopupMenus);
			if (ToAddr != '') {
				NewMessageScreen.UpdateMessageFromContacts(ToAddr, '', '');
			}
			else {
				NewMessageScreen.SetNewMessage();
			}
			NewMessageScreen.Show();
			WebMail.ScreenId = SCREEN_NEW_MESSAGE;
			break;
	}
	WebMail.Screens = [];
	WebMail.Screens[SCREEN_NEW_MESSAGE] = NewMessageScreen;
	isBodyLoaded = true;
	setTimeout('NewMessageScreen.ResizeBody();', 1000);
}

function CPreviewPaneInNewWindow()
{
	this._mainContainer = null;
	this._picturesControl = new CMessagePicturesController(this.ShowPictures, this);
	this._sensivityControl = new CMessageSensivityController();
	this._readConfirmationControl = new CMessageReadConfirmationController(this.SendConfirmation, this);
	this._readConfirmationTbl = null;
	this._previewPaneMessageHeaders = new CPreviewPaneMessageHeaders(true);
	this._msgViewer = new CMessageViewer();
	this._replyPane = null;

	this._build();
	this._fill();
	this.FillMessageInfo(ViewMessage);
	this.Show();
	this.Resize();
}

CPreviewPaneInNewWindow.prototype = {
	ShowPictures: function (safety) {
		if (ViewMessage.Safety != safety) {
			ViewMessage.ShowPictures();
			this._msgViewer.Fill(ViewMessage);
			this.Resize();
		}
	},

	SendConfirmation: function () {
		if (ViewMessage && ViewMessage.MailConfirmationValue && ViewMessage.MailConfirmationValue.length) {
			window.opener.SendConfirmationHandler(ViewMessage.MailConfirmationValue, ViewMessage.Subject);
		}
	},

	_buildToolBar: function ()
	{
		var toolBar = new CToolBar(this._mainContainer);
		this._toolBar = toolBar;

        function CreateReplyClickFromReplyPane(obj, replyAction)
        {
            return function () {
                obj._replyPane.SwitchToFullForm(replyAction);
            }
        }
        var replyFunc = CreateReplyClickFromReplyPane(this, TOOLBAR_REPLY);
        var replyAllFunc = CreateReplyClickFromReplyPane(this, TOOLBAR_REPLYALL);
		//reply tool (reply, reply all)
		toolBar.AddReplyItem(WebMail.PopupMenus, true, replyFunc, replyAllFunc);

		//forward tool
        function CreateForwardClick()
        {
            return function () {
               WebMail.ReplyMessageClick(TOOLBAR_FORWARD, ViewMessage);
            }
        }
		toolBar.AddItem(TOOLBAR_FORWARD, CreateForwardClick(), true);
		toolBar.AddClearDiv();
	},

	_build: function ()
	{
		var mainContainer = CreateChild(document.body, 'div');
		this._mainContainer = mainContainer;
		this._buildToolBar();
		this._picturesControl.Build(mainContainer);
		this._sensivityControl.Build(mainContainer);
		this._readConfirmationTbl = this._readConfirmationControl.Build(mainContainer);
		this._previewPaneMessageHeaders.Build(mainContainer);
		this._msgViewer.Build(mainContainer, 0);
		this._msgViewer.SetSwitcher(this._previewPaneMessageHeaders.SwitcherCont, 'wm_message_right', this._previewPaneMessageHeaders.SwitcherObj);
		this._replyPane = new CMessageReplyPane(mainContainer);
	},

	_fill: function ()
	{
		this._previewPaneMessageHeaders.Fill(ViewMessage, null);
		this._msgViewer.Fill(ViewMessage);
		this._replyPane.Show(ViewMessage);
	},

	Show: function ()
	{
		this._mainContainer.className = '';
	},

	Hide: function ()
	{
		this._mainContainer.className = 'wm_hide';
	},

	ResetFlags: function ()
	{
		this._replyPane.ResetFlags();
	},

	SetMessageId: function (msgId, msgUid)
	{
		this._replyPane.SetMessageId(msgId, msgUid);
	},

	Resize: function ()
	{
		var externalHeight = this._toolBar.GetHeight();
		externalHeight += this._previewPaneMessageHeaders.GetHeight();
		externalHeight += this._picturesControl.GetHeight();
		externalHeight += this._readConfirmationTbl.offsetHeight;
		externalHeight += this._sensivityControl.GetHeight();
		var msgHeight = GetHeight() - externalHeight;
		this._replyPane.SetMaxHeight(Math.round(msgHeight / 2))
		this._msgViewer.ResizeHeight(msgHeight - this._replyPane.GetHeight());

		var width = GetWidth();
		this._previewPaneMessageHeaders.Resize(width);
		this._msgViewer.ResizeWidth(width);
		this._picturesControl.ResizeWidth(width);
		this._readConfirmationControl.ResizeWidth(width);
		this._sensivityControl.ResizeWidth(width);
		this._replyPane.ResizeWidth(width);
	},

	SwitchToHtmlPlain: function ()
	{
		this._msgViewer.SwitchToHtmlPlainInNewWindow();
		this._msgViewer.Fill(ViewMessage);
	}
};

CPreviewPaneInNewWindow.prototype.FillMessageInfo = MessageListPrototype.FillMessageInfo;
