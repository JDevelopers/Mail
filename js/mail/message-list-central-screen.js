/*
 * Classes:
 *  CMessageListCentralPaneScreen(skinName)
 */

function CMessageListCentralPaneScreen(skinName)
{
	this.Id = SCREEN_MESSAGE_LIST_CENTRAL_PANE;
	this.isBuilded = false;
	this.hasCopyright = false;
	this.BodyAutoOverflow = false;
	this.SearchFormId = 'search_form' + this.Id;
	this.shown = false;
	
	this._showTextLabels = true;
	this._allowContacts = true;
	this._skinName = skinName;
	this._timeOffset = 0;
	this._defLang = '';
	this._messagesPerPage = 20;
	this.isDirectMode = false;
	this._useImapTrash = false;

	this._page = 1;
	this._lookForStr = '';
	this._searchMode = 0;
	this._folderId = -1;
	this._folderFullName = '';
	this._sortField = SORT_FIELD_DATE;
	this._sortOrder = SORT_ORDER_DESC;
	this._removeCount = 0;

	this.isInboxDirectMode = false;

	this._mainContainer = null;
	this._mailContDiv = null;

	this._toolBar = null;

	this._foldersObj = null;
	this.needToRefreshFolders = false;
	this.needToRefreshMessages = false;
	this._foldersParam = Array();
	this.InboxId = -1;
	this.InboxFullName = '';
	this.SentId = -1;
	this.SentFullName = '';
	this.DraftsId = -1;
	this.DraftsFullName = '';
	this.TrashId = -1;
	this.TrashFullName = '';
	this.SpamId = -1;
	this.SpamFullName = '';
	this._currFolder = null;

	this._vResizerCont = null;

	this._pageSwitcher = null;

	this._inboxContainer = null;
	this._inboxTable = null;
	this._messageListPane = null;
	this._selection = new CSelection();
	this._dragNDrop = new CDragNDrop('Messages');
	this._dragNDrop.SetSelection(this._selection);
	this._inboxWidth = 361;

	this._messagesObj = null; // object CMessage, that is replaced in preview pane

	this._msgViewer = null;
	this.msgBodyFocus = false;
	this._replyPane = null;

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

	this._messageId = -1;
	this._messageUid = '';
	this._msgCharset = AUTOSELECT_CHARSET;
	this._msgSize = 0;
	this._msgObj = null;
	this.msgForView = null;
	this.forEditParams = [];
	this.fromDraftsParams = [];
	
	//logo + accountslist + toolbar + lowtoolbar
	this._externalHeight = 56 + 32 + 26 + 24;
	this._logo = null;
	this._accountsBar = null;

	//manage folders + hide folders
	this._foldersExternalHeight = 22 + 20;
	this._foldersHeight = 100;
	
	this._msgResizerCont = null;
	this._msgResizerObj = null;
	
	this._inboxHeight = 100;
	this._inboxHeadersHeight = 21;
	//border + inbox headers
	this._defaultInboxHeight = 150;
	this._minUpper = 1 + this._inboxHeadersHeight;

	this._replyTool = null;
	this._replyButton = null;
	this._replyAllButton = null;
	this._replyPopupMenu = null;
	this._forwardTool = null;

	this._previewPaneMessageHeaders = null;
	this._messageHeadersHeight = 48;
	this._messagePadding = 16;
	this._minLower = 200;

	this._nextMessageIndex = null;
	
	this._minListWidth = 460;
	this._minMessageWidth = 400;

	this._mailContMargins = null;

	this._msgFrameHorWidth = 6;
	this._msgFrameVertWidth = 10;
	this._parentContainer = null;
	
	this._getHelpWidth = function ()
	{
		var fldResizerWidth = this._vResizerCont.offsetWidth;
		fldResizerWidth = (fldResizerWidth > 0) ? (fldResizerWidth + 2) : 8;
		var msgResizerWidth = this._msgResizerCont.offsetWidth;
		msgResizerWidth = (msgResizerWidth > 0) ? (msgResizerWidth + 2) : 8;

		var marginWidth = (this._mailContMargins != null) 
			? (this._mailContMargins.Left + this._mailContMargins.Right) 
			: 0;
		var externalWidth = fldResizerWidth + msgResizerWidth + marginWidth;

		var screenWidth = GetWidth();
		return { screen: screenWidth, margin: marginWidth, fldResizer: fldResizerWidth, 
			msgResizer: msgResizerWidth, external: externalWidth };
	};
	
	this.ResizeScreen = function (mode)
	{
		var isAuto = false;
		var helpWidth = this._getHelpWidth();
		if (mode == RESIZE_MODE_FOLDERS || mode == RESIZE_MODE_ALL) {
			var maxFoldersWidth = helpWidth.screen - (this._minListWidth + helpWidth.external + this._messageWidth) + (helpWidth.margin / 2);
			var fldWidth = this._foldersPane.ResizeWidth(maxFoldersWidth, (helpWidth.margin / 2));
			
			var listWidth = helpWidth.screen - (fldWidth + helpWidth.external + this._messageWidth);
			listWidth = Validator.CorrectNumber(listWidth, this._minListWidth);
			
			var msgMinLeftPos = fldWidth + this._minListWidth + helpWidth.external - helpWidth.margin / 2;
			this._msgResizerObj.updateMinLeftWidth(msgMinLeftPos);
			this.ResizeInboxContainerWidth(listWidth);
			this.ResizeInboxWidth();
		}
		
		if (mode == RESIZE_MODE_MSG_WIDTH || mode == RESIZE_MODE_ALL) {
			var msgLeftBound = this._msgResizerObj.LeftPosition + helpWidth.msgResizer;
			var msgWidth = Validator.CorrectNumber((helpWidth.screen - msgLeftBound), this._minMessageWidth);
			
			var fldWidth = this._foldersPane.GetWidth();
			
			var listWidth = helpWidth.screen - (fldWidth + helpWidth.external + msgWidth);
			var correctListWidth = Validator.CorrectNumber(listWidth, this._minListWidth);
			var difference = listWidth - correctListWidth;
			if (difference > 0) {
				msgWidth = Validator.CorrectNumber(msgWidth - difference, this._minMessageWidth);
			}
			this._messageWidth = msgWidth;
			
			var fldMinRightPos = msgWidth + this._minListWidth + helpWidth.external;
			this._foldersPane.SetResizerMinRightPos(fldMinRightPos);
			this._picturesControl.ResizeWidth(msgWidth - this._msgFrameHorWidth);
			this._readConfirmationControl.ResizeWidth(msgWidth - this._msgFrameHorWidth);
			this._sensivityControl.ResizeWidth(msgWidth - this._msgFrameHorWidth);
			this.ResizeMessageWidth(msgWidth - this._msgFrameHorWidth);
			this.ResizeInboxContainerWidth(listWidth);
			this.ResizeInboxWidth();
			this._msgResizerObj.LeftPosition = helpWidth.screen - (msgWidth + helpWidth.msgResizer);
			CreateCookie('wm_msg_resizer', this._msgResizerObj.LeftPosition, COOKIE_STORAGE_DAYS);
		}

		if (mode == RESIZE_MODE_MSG_PANE) {
			this.ResizeMessageWidth(this._messageWidth - this._msgFrameHorWidth);
		}
		
		// height resizing start
		if (mode == RESIZE_MODE_ALL || mode == RESIZE_MODE_MSG_HEIGHT || 
            mode == RESIZE_MODE_MSG_WIDTH || mode == RESIZE_MODE_MSG_PANE)
        {
			var screenHeight = GetHeight();
			if (screenHeight < MIN_SCREEN_HEIGHT) {
				screenHeight = MIN_SCREEN_HEIGHT;
				isAuto = true;
			}
			var externalHeight = this.GetExternalHeight(); //logo, accountsBar
			var marginHeight = (this._mailContMargins != null)
				? (this._mailContMargins.Top + this._mailContMargins.Bottom)
				: 0;
			var innerHeight = screenHeight - externalHeight - marginHeight;
			this._inboxHeight = innerHeight;
			this._mailContDiv.style.height = innerHeight + 'px';

			this._foldersPane.ResizeHeight(innerHeight);
			this._messageListPane.ResizeHeight(innerHeight);
			this._msgResizerObj.updateVerticalSize(innerHeight);
			var msgHeight = innerHeight - this.GetMessageExternalHeight();
			this._replyPane.SetMaxHeight(Math.round(msgHeight / 2))
			this._msgViewer.ResizeHeight(msgHeight - this._replyPane.GetHeight());
		}
		// height resizing end

        if (null != this._pageSwitcher) this._pageSwitcher.Replace();
		SetBodyAutoOverflow(isAuto);
		this._dragNDrop.Resize();
	};
	
	this.GetExternalHeight = function()
	{
		var x = this._logo.offsetHeight + this._accountsBar.offsetHeight;
		if (x != 0) {
			this._externalHeight = x;
		}
		return this._externalHeight;
	};
	
	this.ResizeMessageWidth = function(width)
	{
		this._previewPaneMessageHeaders.Resize(width);
		this._msgViewer.ResizeWidth(width);
		this._parentContainer.style.width = width + this._msgFrameHorWidth + 'px';
		this._replyPane.ResizeWidth(width);
	};
	
	this.GetMessageExternalHeight = function()
	{
		this._messageHeadersHeight = this._toolBar.GetHeight() + this._previewPaneMessageHeaders.GetHeight() +
			this._picturesControl.GetHeight() + this._readConfirmationTbl.offsetHeight + this._sensivityControl.GetHeight();
		return this._msgFrameVertWidth + this._messageHeadersHeight;
	};

    this._createResizer = function (parent)
    {
		var container = CreateChild(parent, 'div', [['class', 'wm_vresizer_transparent_part'],
			['style', 'display: inline; width: 6px; float: left; margin: 0 1px 0 -1px;']]);
		var resizer = CreateChild(container, 'div');
		resizer.className = 'wm_vresizer';
		var div = CreateChild(container, 'div');
		div.className = 'wm_vresizer_width';
        return {container: container, resizer: resizer};
    };
	
	this.Build = function (container, accountsBar, PopupMenus, settings)
	{
		this.ParseSettings(settings);
		this._logo = document.getElementById('logo');
		this._accountsBar = accountsBar;
		
		var layout3PaneDiv = CreateChild(container, 'div');
		layout3PaneDiv.id = 'layout_3pane';
		layout3PaneDiv.className = 'wm_hide';
		this._mainContainer = layout3PaneDiv;
		
		this._mailContDiv = CreateChild(layout3PaneDiv, 'div', [['class', 'wm_mail_container wm_central_list_pane']]);
		this._mailContMargins = GetMargins(this._mailContDiv);

        var messagePaneContainer, msgResizer, fldResizer, foldersHtmlParent;
        if (window.RTL) {
            messagePaneContainer = CreateChild(this._mailContDiv, 'div', [['class', 'wm_message_container_parent_td'],
                ['style', 'display: inline; float: left;']]);
            msgResizer = this._createResizer(this._mailContDiv);
            this._inboxContainer = CreateChild(this._mailContDiv, 'div', [['class', 'wm_message_list'],
                ['style', 'display: inline; float: left;']]);
            fldResizer = this._createResizer(this._mailContDiv);
            foldersHtmlParent = CreateChild(this._mailContDiv, 'div', [['style', 'display: inline; float: left;']]);
        }
        else {
            foldersHtmlParent = CreateChild(this._mailContDiv, 'div', [['style', 'display: inline; float: left;']]);
            fldResizer = this._createResizer(this._mailContDiv);
            this._inboxContainer = CreateChild(this._mailContDiv, 'div', [['class', 'wm_message_list'],
                ['style', 'display: inline; float: left;']]);
            msgResizer = this._createResizer(this._mailContDiv);
            messagePaneContainer = CreateChild(this._mailContDiv, 'div', [['class', 'wm_message_container_parent_td'],
                ['style', 'display: inline; float: left;']]);
        }

		this._messageListPane = new CMessageListCentralPane(this._inboxContainer, PopupMenus);
		this.BuildInboxTable();
		this._messageListPane.Build(this._inboxTable);

        this._vResizerCont = fldResizer.container;
        this._msgResizerCont = msgResizer.container;
		this.BuildMessageContainer(messagePaneContainer, PopupMenus);

		this._foldersPane = new CFoldersPane(foldersHtmlParent, this._dragNDrop, fldResizer.resizer, this._mainContainer, true);
      	
		var msgResizerWidth = 4;
		var minLeftWidth = 620;
		var minRightWidth = this._minMessageWidth + 2;
		this._msgResizerObj = new CVerticalResizer(msgResizer.resizer, layout3PaneDiv, msgResizerWidth, minLeftWidth,
			minRightWidth, WebMail.Settings.MsgResizer, 'WebMail.ResizeBody(RESIZE_MODE_MSG_WIDTH);');

		this._pageSwitcher = new CPageSwitcher(this._messageListPane.PageSwitcherBar, true);
		
		this.isBuilded = true;
	};//Build
	
	this.ChangeFolder = function (id, fullName)
	{
		this._folderId = id;
		this._folderFullName = fullName;
		this.ChangeFromFieldInFolder(id, fullName);
		if (this._folderId != id || this._folderFullName != fullName) {
			this.CleanMessageBody(true);
		}
		this._messageListPane.SetCurrSearchFolder(this._folderId, this._folderFullName);
	};

	this._buildToolBar = function (container, PopupMenus)
	{
		var toolBar = new CToolBar(container, TOOLBAR_VIEW_WITH_CURVE);
		this._toolBar = toolBar;

        function CreateReplyClickFromReplyPane(obj, replyAction)
        {
            return function () {
                obj._replyPane.SwitchToFullForm(replyAction);
            }
        }
        var replyFunc = CreateReplyClickFromReplyPane(this, TOOLBAR_REPLY);
        var replyAllFunc = CreateReplyClickFromReplyPane(this, TOOLBAR_REPLYALL);
		//reply tool (reply, reply all); absent in drafts
		var replyParts = toolBar.AddReplyItem(PopupMenus, false, replyFunc, replyAllFunc);
		this._replyTool = replyParts.ReplyReplace;
		this._replyButton = replyParts.ReplyButton;
		this._replyAllButton = replyParts.ReplyAllButton;
		this._replyPopupMenu = replyParts.ReplyPopupMenu;
		
		//forward tool; absent in drafts
		this._forwardTool = toolBar.AddItem(TOOLBAR_FORWARD, CreateReplyClick(TOOLBAR_FORWARD), false);
	};

	this.BuildMessageContainer = function(mainTd, PopupMenus)
	{
		this._parentContainer = mainTd;

		var div, bordersHeight = 0;
		var parentdiv = CreateChild(mainTd, 'div');
		//div.className = 'wm_message_container';

		this._buildToolBar(parentdiv, PopupMenus);

		var divTopCorners = CreateChild(parentdiv, 'div');
		div = CreateChild(divTopCorners, 'div', [['class', 'wm_message_pane_corner1']]);
		bordersHeight += GetHeightStyle(div);
		div = CreateChild(divTopCorners, 'div', [['class', 'wm_message_pane_corner2']]);
		bordersHeight += GetHeightStyle(div);
		div = CreateChild(divTopCorners, 'div', [['class', 'wm_message_pane_corner3']]);
		bordersHeight += GetHeightStyle(div);
		div = CreateChild(divTopCorners, 'div', [['class', 'wm_message_pane_corner4']]);
		bordersHeight += GetHeightStyle(div);
		div = CreateChild(divTopCorners, 'div', [['class', 'wm_message_pane_corner5']]); //!!!
		bordersHeight += GetHeightStyle(div);

		var divMain = CreateChild(parentdiv, 'div', [['class', 'wm_message_pane_border']]);
		var borders = GetBorders(divMain);
		this._msgFrameHorWidth = borders.Left + borders.Right;

		this._picturesControl.Build(divMain);
		this._sensivityControl.Build(divMain);
 		this._readConfirmationTbl = this._readConfirmationControl.Build(divMain);
 		this._previewPaneMessageHeaders = new CPreviewPaneMessageHeaders();
		this._previewPaneMessageHeaders.Build(divMain);
		this._msgViewer = new CMessageViewer();
		this._msgViewer.Build(divMain, 0);
		this._msgViewer.SetSwitcher(this._previewPaneMessageHeaders.SwitcherCont, 'wm_message_right', this._previewPaneMessageHeaders.SwitcherObj);

		this._replyPane = new CMessageReplyPane(divMain);

		var divBottomCorners = CreateChild(parentdiv, 'div');
		div = CreateChild(divBottomCorners, 'div', [['class', 'wm_message_pane_corner5 bottom']]); //!!!
		bordersHeight += GetHeightStyle(div);
		div = CreateChild(divBottomCorners, 'div', [['class', 'wm_message_pane_corner4 bottom']]); //!!!
		bordersHeight += GetHeightStyle(div);
		div = CreateChild(divBottomCorners, 'div', [['class', 'wm_message_pane_corner3 bottom']]); //!!!
		bordersHeight += GetHeightStyle(div);
		div = CreateChild(divBottomCorners, 'div', [['class', 'wm_message_pane_corner2 bottom']]); //!!!
		bordersHeight += GetHeightStyle(div);
		div = CreateChild(divBottomCorners, 'div', [['class', 'wm_message_pane_corner1 bottom']]); //!!!
		bordersHeight += GetHeightStyle(div);
		this._msgFrameVertWidth = bordersHeight;
	}
}

CMessageListCentralPaneScreen.prototype.EnableDeleteTools = function ()
{
	this._messageListPane.EnableDeleteTools(this.DeleteLikePop3());
};

CMessageListCentralPaneScreen.prototype.EnableTools = function ()
{
	this._messageListPane.EnableTools(this.DeleteLikePop3());
	this._replyPane.ResetFlags();
};

CMessageListCentralPaneScreen.prototype.SetMessageId = function (msgId, msgUid)
{
	this._replyPane.SetMessageId(msgId, msgUid);
};

CMessageListCentralPaneScreen.prototype.ResetReplyPaneFlags = function (mode)
{
	this._replyPane.ResetFlags(mode);
};

CMessageListCentralPaneScreen.prototype.EndCheckMail = function ()
{
	WebMail.CheckMail.End();
	this._messageListPane.EnableCheckMailTool();
};

CMessageListCentralPaneScreen.prototype.StartHiddenCheckMail = function()
{
	var allowCheckMail = this._messageListPane.DisableCheckMailTool();
	if (allowCheckMail) {
		WebMail.CheckMail.Start(true);
	}
};

CMessageListCentralPaneScreen.prototype.RepairToolBar = function ()
{
	if (!this.isBuilded) return;
	
	var showNotSpam = this.IsSpam();
	var showSpam = (!this.IsSent() && !this.IsDrafts() && !this.IsSpam());
	this._messageListPane.RepairSpamTools(showSpam, showNotSpam);
	
	this._messageListPane.RepairDeleteTools(this.DeleteLikePop3());
	
	if (WebMail.Accounts.CurrMailProtocol == POP3_PROTOCOL) {
		if (this.isInboxDirectMode) {
			if (this.IsInbox()) {
				this._messageListPane.HideMarkTool();
				this._messageListPane.HideSearchForm();
				this._messageListPane.ShowInboxMoveItem();
			}
			else {
				this._messageListPane.ShowMarkTool();
				this._messageListPane.ShowSearchForm();
				this._messageListPane.HideInboxMoveItem();
			}
			this._dragNDrop.SetMoveToInbox(false);
		}
		else {
			this._messageListPane.ShowMarkTool();
			this._messageListPane.ShowSearchForm();
			this._messageListPane.ShowInboxMoveItem();
			this._dragNDrop.SetMoveToInbox(true);
		}
	}
	else { //WebMail.Accounts.CurrMailProtocol == IMAP4_PROTOCOL || WebMail.Accounts.CurrMailProtocol == WMSERVER_PROTOCOL
		if (this.shown) {
			if (this.isDirectMode && WebMail.Accounts.CurrMailProtocol == WMSERVER_PROTOCOL) {
				this._messageListPane.HideSearchForm();
			} else {
				this._messageListPane.ShowSearchForm();
			}
		}
		this._messageListPane.ShowMarkTool();
	}
		if (this.IsDrafts()) {
			this._replyTool.className = 'wm_hide';
			this._forwardTool.Hide();
		} else {
			this._replyTool.className = 'wm_tb';
			this._forwardTool.Show();
		}
	if (this._folderId == -1 && this._folderFullName.length == 0 && this._lookForStr.length > 0) {
		this._messageListPane.DisableInSearch(true);
	} else {
		this._messageListPane.DisableInSearch(false);
	}
},

CMessageListCentralPaneScreen.prototype.Pop3DeleteToolEnabled = function ()
{
	return this._messageListPane.Pop3DeleteToolEnabled();
};

CMessageListCentralPaneScreen.prototype.AlreadyPop3Deleted = function (idArray)
{
	return this._messageListPane.AlreadyPop3Deleted(idArray);
};

CMessageListCentralPaneScreen.prototype.DisablePop3DeleteTool = function (idArray)
{
	return this._messageListPane.DisablePop3DeleteTool(idArray);
};

CMessageListCentralPaneScreen.prototype.ClearDeleteTools = function ()
{
	return this._messageListPane.ClearDeleteTools();
};

CMessageListCentralPaneScreen.prototype.ImapDeleteToolEnabled = function ()
{
	return this._messageListPane.ImapDeleteToolEnabled();
};

CMessageListCentralPaneScreen.prototype.AlreadyImapDeleted = function (idArray)
{
	return this._messageListPane.AlreadyImapDeleted(idArray);
};

CMessageListCentralPaneScreen.prototype.DisableImapDeleteTool = function (idArray)
{
	return this._messageListPane.DisableImapDeleteTool(idArray);
};

CMessageListCentralPaneScreen.prototype.SpamToolEnabled = function (type)
{
	return this._messageListPane.SpamToolEnabled(type);
};
	
CMessageListCentralPaneScreen.prototype.AlreadyMarkedSpam = function (type, idArray)
{
	return this._messageListPane.AlreadyMarkedSpam(type, idArray);
};

CMessageListCentralPaneScreen.prototype.EnableToolsByOperation = function (operationType, deleteLikePop3)
{
	return this._messageListPane.EnableToolsByOperation(operationType, deleteLikePop3);
};

CMessageListCentralPaneScreen.prototype.WriteMsgsCountInFolder = function (count) { };

CMessageListCentralPaneScreen.prototype.HideSearchFolders = function ()
{
	return this._messageListPane.HideSearchFolders();
};

CMessageListCentralPaneScreen.prototype.CheckVisibilitySearchForm = function (ev)
{
	return this._messageListPane.CheckVisibilitySearchForm(ev);
};

CMessageListCentralPaneScreen.prototype.CleanSearchFolders = function ()
{
	return this._messageListPane.CleanSearchFolders();
};

CMessageListCentralPaneScreen.prototype.ShowSearchForm = function ()
{
	return this._messageListPane.ShowSearchForm();
};

CMessageListCentralPaneScreen.prototype.HideSearchForm = function ()
{
	return this._messageListPane.HideSearchForm();
};

CMessageListCentralPaneScreen.prototype.PlaceSearchData = function (searchFields, lookFor)
{
	return this._messageListPane.PlaceSearchData(searchFields, lookFor);
};

CMessageListCentralPaneScreen.prototype.FocusSearchForm = function ()
{
	return this._messageListPane.FocusSearchForm();
};

CMessageListCentralPaneScreen.prototype.AddToSearchFolders = function (name, id, fullName)
{
	return this._messageListPane.AddToSearchFolders(name, id, fullName);
};

CMessageListCentralPaneScreen.prototype.CleanMoveMenu = function ()
{
	return this._messageListPane.CleanMoveMenu();
};

CMessageListCentralPaneScreen.prototype.AddToMoveMenu = function (folderId, folderFullName, folderName, isInboxFolder)
{
	return this._messageListPane.AddToMoveMenu(folderId, folderFullName, folderName, isInboxFolder);
};

CMessageListCentralPaneScreen.prototype.RequestSearchResults = function ()
{
	var search = this._messageListPane.GetSearchParameters();
	
	var redrawType = REDRAW_NOTHING;
	var redrawObj = null;
	var folder = { Id: search.FolderId, FullName: search.FolderFullName };
	if (search.String.length == 0 && folder.Id == -1) {
		folder = this.GetCurrFolder();
		var paramIndex = folder.Id + folder.FullName;
		var params = this._foldersParam[paramIndex];
		if (params) {
			redrawType = REDRAW_FOLDER;
			redrawObj = params._div;
		}
	}
	
	SetHistoryHandler(
		{
			ScreenId: this.Id,
			FolderId: folder.Id,
			FolderFullName: folder.FullName,
			Page: 1,
			SortField: this._sortField,
			SortOrder: this._sortOrder,
			LookForStr: search.String,
			SearchMode: search.Mode,
			RedrawType: redrawType,
			RedrawObj: redrawObj,
			MsgId: null,
			MsgUid: null,
			MsgFolderId: null,
			MsgFolderFullName: null,
			MsgCharset: null,
			MsgParts: null
		}
	);
	
	this._messageListPane.ShowSearchForm();
};

CMessageListCentralPaneScreen.prototype.ResizeInboxWidth = function ()
{
	this._messageListPane.ResizeWidth(this._inboxWidth);
}

CMessageListCentralPaneScreen.prototype.PlaceData = MessageListPrototype.PlaceData;
CMessageListCentralPaneScreen.prototype.GetCurrMessageHistoryObject = MessageListPrototype.GetCurrMessageHistoryObject;
CMessageListCentralPaneScreen.prototype.ResizeBody = MessageListPrototype.ResizeBody;
CMessageListCentralPaneScreen.prototype.ParseSettings = MessageListPrototype.ParseSettings;
CMessageListCentralPaneScreen.prototype.ChangeSkin = MessageListPrototype.ChangeSkin;
CMessageListCentralPaneScreen.prototype.RedrawFolderControls = MessageListPrototype.RedrawFolderControls;
CMessageListCentralPaneScreen.prototype.CleanMessageBody = MessageListPrototype.CleanMessageBody;
CMessageListCentralPaneScreen.prototype.FillByMessage = MessageListPrototype.FillByMessage;
CMessageListCentralPaneScreen.prototype.SwitchToHtmlPlain = MessageListPrototype.SwitchToHtmlPlain;
CMessageListCentralPaneScreen.prototype.GetCurrFolderHistoryObject = MessageListPrototype.GetCurrFolderHistoryObject;
CMessageListCentralPaneScreen.prototype.ClearSearch = MessageListPrototype.ClearSearch;
CMessageListCentralPaneScreen.prototype.GetSortField = MessageListPrototype.GetSortField;
CMessageListCentralPaneScreen.prototype.PlaceFolderList = MessageListPrototype.PlaceFolderList;
CMessageListCentralPaneScreen.prototype.PlaceMessageList = MessageListPrototype.PlaceMessageList;
CMessageListCentralPaneScreen.prototype.PlaceMessagesOperation = MessageListPrototype.PlaceMessagesOperation;
CMessageListCentralPaneScreen.prototype.Show = MessageListPrototype.Show;
CMessageListCentralPaneScreen.prototype.ChangeFromFieldInFolder = MessageListPrototype.ChangeFromFieldInFolder;
CMessageListCentralPaneScreen.prototype.FolderClick = MessageListPrototype.FolderClick;
CMessageListCentralPaneScreen.prototype._showSearchingMessage = MessageListPrototype._showSearchingMessage;
CMessageListCentralPaneScreen.prototype.RestoreFromHistory = MessageListPrototype.RestoreFromHistory;
CMessageListCentralPaneScreen.prototype.Hide = MessageListPrototype.Hide;
CMessageListCentralPaneScreen.prototype.ClickBody = MessageListPrototype.ClickBody;
CMessageListCentralPaneScreen.prototype.KeyupBody = MessageListPrototype.KeyupBody;
CMessageListCentralPaneScreen.prototype.ResizeInboxContainerWidth = MessageListPrototype.ResizeInboxContainerWidth;
CMessageListCentralPaneScreen.prototype.IsInbox = MessageListPrototype.IsInbox;
CMessageListCentralPaneScreen.prototype.IsSent = MessageListPrototype.IsSent;
CMessageListCentralPaneScreen.prototype.IsDrafts = MessageListPrototype.IsDrafts;
CMessageListCentralPaneScreen.prototype.IsTrash = MessageListPrototype.IsTrash;
CMessageListCentralPaneScreen.prototype.DeleteLikePop3 = MessageListPrototype.DeleteLikePop3;
CMessageListCentralPaneScreen.prototype.DeleteLikeImap = MessageListPrototype.DeleteLikeImap;
CMessageListCentralPaneScreen.prototype.IsSpam = MessageListPrototype.IsSpam;
CMessageListCentralPaneScreen.prototype.CleanFolderList = MessageListPrototype.CleanFolderList;
CMessageListCentralPaneScreen.prototype.CleanInboxLines = MessageListPrototype.CleanInboxLines;
CMessageListCentralPaneScreen.prototype.SetNoMessagesFoundMessage = MessageListPrototype.SetNoMessagesFoundMessage;
CMessageListCentralPaneScreen.prototype.RedrawControls = MessageListPrototype.RedrawControls;
CMessageListCentralPaneScreen.prototype.SetPageSwitcher = MessageListPrototype.SetPageSwitcher;
CMessageListCentralPaneScreen.prototype.ChangeDefOrder = MessageListPrototype.ChangeDefOrder;
CMessageListCentralPaneScreen.prototype.GetDefOrder = MessageListPrototype.GetDefOrder;
CMessageListCentralPaneScreen.prototype.ChangeCurrFolder = MessageListPrototype.ChangeCurrFolder;
CMessageListCentralPaneScreen.prototype.ChangeCurrFolder = MessageListPrototype.ChangeCurrFolder;
CMessageListCentralPaneScreen.prototype._useOrFreeSort = function () {};
CMessageListCentralPaneScreen.prototype.GetXmlMessagesOperation = MessageListPrototype.GetXmlMessagesOperation;
CMessageListCentralPaneScreen.prototype.IsEmptyFolder = MessageListPrototype.IsEmptyFolder;
CMessageListCentralPaneScreen.prototype.FillByFolders = MessageListPrototype.FillByFolders;
CMessageListCentralPaneScreen.prototype._getSearchResultsMessage = MessageListPrototype._getSearchResultsMessage;
CMessageListCentralPaneScreen.prototype.ShowPictures = MessageListPrototype.ShowPictures;
CMessageListCentralPaneScreen.prototype.FillMessageInfo = MessageListPrototype.FillMessageInfo;
CMessageListCentralPaneScreen.prototype.FillByMessages = MessageListPrototype.FillByMessages;
CMessageListCentralPaneScreen.prototype.GetPage = MessageListPrototype.GetPage;
CMessageListCentralPaneScreen.prototype.RedrawPages = MessageListPrototype.RedrawPages;
CMessageListCentralPaneScreen.prototype.HeaderClickFunc = MessageListPrototype.HeaderClickFunc;
CMessageListCentralPaneScreen.prototype.BuildInboxTable = MessageListPrototype.BuildInboxTable;
CMessageListCentralPaneScreen.prototype.BuildFoldersPart = MessageListPrototype.BuildFoldersPart;
CMessageListCentralPaneScreen.prototype.ResetReplyTools = MessageListPrototype.ResetReplyTools;

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}