/*
 * Classes:
 *  CMessageListTopPaneScreen(skinName)
 *  CSpaceInfo()
 */

function CMessageListTopPaneScreen(skinName)
{
	this.Id = SCREEN_MESSAGE_LIST_TOP_PANE;
	this.isBuilded = false;
	this.hasCopyright = false;
	this.BodyAutoOverflow = false;
	this.SearchFormId = 'search_form' + this.Id;
	this.shown = false;
	
	this._spaceInfoObj = new CSpaceInfo();

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

	this.SearchFormObj = null;
	this._lookForSmallObj = null;
	this._bigSearchForm = null;
	this._searchIn = null;
	this._quickSearch = null;
	this._slowSearch = null;

	this._toolBar = null;
	this._checkMailTool = null;
	this._pop3DeleteTool = null;
	this._imap4DeleteTool = null;
	this._markTool = null;
	this._moveMenu = null;
	this._inboxMoveItem = null;
	this._isSpamTool = null;
	this._notSpamTool = null;

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

	this._selection = new CSelection();
	this._dragNDrop = new CDragNDrop('Messages');
	this._dragNDrop.SetSelection(this._selection);
	this._inboxWidth = 361;

	this._messagesObj = null; // object CMessage, that is replaced in preview pane

	this._msgViewer = null;
	this.msgBodyFocus = false;

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
	
	this._messagesInFolder = null;

	//logo + accountslist + toolbar + lowtoolbar
	this._externalHeight = 56 + 32 + 26 + 24;
	this._logo = null;
	this._accountsBar = null;
	this._lowToolBar = null;

	//manage folders + hide folders
	this._foldersExternalHeight = 22 + 20;
	this._foldersHeight = 100;
	
	this._hResizerCont = null;
	this._hResizerHeight = 4;
	this._horizResizerObj = null;
	
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
	
	this.ResizeScreen = function (mode)
	{
		if (mode == RESIZE_MODE_MSG_HEIGHT) {
			CreateCookie('wm_horiz_resizer', this._horizResizerObj._topPosition, COOKIE_STORAGE_DAYS);
		}
		var isAuto = false;
		var height = GetHeight();
		var innerHeight = height - this.GetExternalHeight();//logo, accountsBar, toolBar, lowToolBar
		if (innerHeight < 300) {
			innerHeight = 300;
			isAuto = true;
		}

		if (mode == RESIZE_MODE_ALL || mode == RESIZE_MODE_MSG_HEIGHT) {
			this._inboxHeight = this._horizResizerObj._topPosition - this._minUpper;
			this._foldersPane.ResizeHeight(innerHeight);
			this.ResizeInboxHeight(innerHeight);
		}
		
		if (mode != RESIZE_MODE_MSG_HEIGHT) {
			var resizerWidth = this._vResizerCont.offsetWidth;
			resizerWidth = (resizerWidth > 0) ? (resizerWidth + 2) : 6;
			var screenwidth = GetWidth();
			var minListWidth = 550;
			
			var maxFoldersWidth = screenwidth - minListWidth - resizerWidth;
			var foldersWidth = this._foldersPane.ResizeWidth(maxFoldersWidth);
			
			var listWidth = screenwidth - foldersWidth - resizerWidth;
			this.ResizeInboxContainerWidth(listWidth);
			this._horizResizerObj.updateHorizontalSize(listWidth);
			
			if (this._inboxWidth > listWidth) isAuto = true;
		}

		this.ResizeInboxWidth();
		if (mode != RESIZE_MODE_MSG_HEIGHT) {
			this.ResizeMessageWidth();
		} else {
			this._msgViewer.ResizeWidth(this._inboxWidth - this._messageHorBordersWidth);
		}
		if (mode == RESIZE_MODE_ALL || mode == RESIZE_MODE_MSG_HEIGHT) {
			this.ResizeInboxHeight(innerHeight);
		}
		if (null != this._pageSwitcher) this._pageSwitcher.Replace();
		SetBodyAutoOverflow(isAuto);
		this._dragNDrop.Resize();
	};
	
	this.GetExternalHeight = function()
	{
		var x = this._logo.offsetHeight + this._accountsBar.offsetHeight + this._toolBar.GetHeight() 
			+  this._lowToolBar.offsetHeight;
		if (x != 0) {
			this._externalHeight = x;
		}
		return this._externalHeight;
	};
	
	this.ResizeMessageWidth = function()
	{
		this._previewPaneMessageHeaders.Resize(this._inboxWidth - this._messageHorBordersWidth);
		this._msgViewer.ResizeWidth(this._inboxWidth - this._messageHorBordersWidth);
	};
	
	this.GetMessageExternalHeight = function()
	{
		var inboxHeight = this._inboxTable.GetHeight();
		this._hResizerHeight = this._hResizerCont.offsetHeight;
		this._messageHeadersHeight = this._previewPaneMessageHeaders.GetHeight() + this._picturesControl.GetHeight() + 
			this._readConfirmationTbl.offsetHeight + this._sensivityControl.GetHeight();
		return inboxHeight + this._inboxTable.VertBordersWidth + this._messageVertBordersWidth + 
			this._hResizerHeight + this._messageHeadersHeight;
	};
	
	this.ResizeInboxHeight = function(height)
	{
		if (Validator.IsPositiveNumber(height) && height >=100) {
			var messExternalHeight = this.GetMessageExternalHeight();
			var messInnerHeight = height - messExternalHeight;
			if (messInnerHeight < 100) {
				this._inboxHeight -= 100 - messInnerHeight;
				messInnerHeight = 100;
			}
			if (this._inboxHeight < 100) {
				this._inboxHeight = 100;
			}
			this._inboxTable.SetLinesHeight(this._inboxHeight);
			this._horizResizerObj._topPosition = this._inboxHeight + this._minUpper;
			this._msgViewer.ResizeHeight(messInnerHeight);
		}
	};

	this.Build = function(container, accountsBar, PopupMenus, settings)
	{
		this.ParseSettings(settings);
		this._logo = document.getElementById('logo');
		this._accountsBar = accountsBar;

		this._toolBar = new CToolBar(container);
		this._buildToolBar(PopupMenus);

		var div = CreateChild(container, 'div');
		this._mainContainer = div;
		div.className = 'wm_hide';
		var tbl = CreateChild(div, 'table');
		tbl.className = 'wm_mail_container';
		var tr = tbl.insertRow(0);
		var foldersHtmlParent = tr.insertCell(0);
		foldersHtmlParent.rowSpan = 3;

		var td = tr.insertCell(1);
		td.rowSpan = 3;
		td.className = 'wm_vresizer_part';
		this._vResizerCont = td;
		var VResizer = CreateChild(td, 'div');
		VResizer.className = 'wm_vresizer';
		div = CreateChild(td, 'div');
		div.className = 'wm_vresizer_width';

		this._inboxContainer = tr.insertCell(2);
		var obj = this;
		this._inboxContainer.onmousedown = function (ev) {
			if (isRightClick(ev)) {
				obj._selection.UncheckAll();
			}
			return false;
		};
		this.BuildInboxTable();

		tr = tbl.insertRow(1);
		td = tr.insertCell(0);
		td.className = 'wm_hresizer_part';
		this._hResizerCont = td;
		div = CreateChild(td, 'div');
		div.className = 'wm_hresizer_height';
		var HResizer = CreateChild(td, 'div');
		HResizer.className = 'wm_hresizer';
		div = CreateChild(td, 'div');
		div.className = 'wm_hresizer_height';

		tr = tbl.insertRow(2);
		td = tr.insertCell(0);
		td.className = 'wm_message_container_parent_td';
		this.BuildMessageContainer(td);

		tr = tbl.insertRow(3);
		this._lowToolBar = tr.insertCell(0);
		this._lowToolBar.colSpan = 3;
		this._lowToolBar.className = 'wm_lowtoolbar';
		this._messagesInFolder = CreateChild(this._lowToolBar, 'span');
		this._messagesInFolder.className = 'wm_lowtoolbar_messages';
		this.WriteMsgsCountInFolder(0);

		this._spaceInfoObj.Build(this._lowToolBar, settings);

		this._foldersPane = new CFoldersPane(foldersHtmlParent, this._dragNDrop, VResizer, this._mainContainer, false);
		this._horizResizerObj = new CHorizontalResizer(HResizer, this._mainContainer, 2, this._minUpper + 100, this._minLower, this._defaultInboxHeight, 'WebMail.ResizeBody(RESIZE_MODE_MSG_HEIGHT);');

		this._pageSwitcher = new CPageSwitcher(this._inboxTable.GetLines(), false);
		
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
		this._searchIn.value = this._folderId + STR_SEPARATOR + this._folderFullName;
	};

	this._buildToolBar = function (PopupMenus)
	{
		var obj = this;
		var toolbar = this._toolBar;
		//new message tool
		toolbar.AddItem(TOOLBAR_NEW_MESSAGE, NewMessageClickHandler, true);
		//check mail tool
		this._checkMailTool = toolbar.AddItem(TOOLBAR_CHECK_MAIL, function () {
		    if (obj._checkMailTool.Enabled()) {
		        obj._checkMailTool.Disable();
		        WebMail.CheckMail.Start();
		    }
		}, true);

		//reply tool (reply, reply all); absent in drafts
		var replyParts = toolbar.AddReplyItem(PopupMenus, false);
		this._replyTool = replyParts.ReplyReplace;
		this._replyButton = replyParts.ReplyButton;
		this._replyAllButton = replyParts.ReplyAllButton;
		this._replyPopupMenu = replyParts.ReplyPopupMenu;
		//forward tool; absent in drafts
		this._forwardTool = toolbar.AddItem(TOOLBAR_FORWARD, CreateReplyClick(TOOLBAR_FORWARD), false);
		//mark tool; absent in inbox in direct mode in pop3
		this._markTool = toolbar.AddMarkItem(PopupMenus, false);
		//move to folder tool; absent in inbox in direct mode in pop3
		var div = CreateChild(document.body, 'div');
		this._moveMenu = div;
		div.className = 'wm_hide';
		toolbar.AddMoveItem(TOOLBAR_MOVE_TO_FOLDER, PopupMenus, div, false);
		//delete tools
		this._pop3DeleteTool = toolbar.AddPop3DeleteItem(PopupMenus, false);
		var deleteFunc = CreateToolBarItemClick(TOOLBAR_DELETE);
		this._imap4DeleteTool = toolbar.AddItem(TOOLBAR_DELETE, deleteFunc, false);
		// spam tools
		this._isSpamTool = toolbar.AddItem(TOOLBAR_IS_SPAM, function () {RequestMessagesOperationHandler(TOOLBAR_IS_SPAM, [], []);}, false);
		this._notSpamTool = toolbar.AddItem(TOOLBAR_NOT_SPAM, function () {RequestMessagesOperationHandler(TOOLBAR_NOT_SPAM, [], []);}, false);
		
		var lookForBigInp = this.BuildAdvancedSearchForm();
		var searchParts = toolbar.AddSearchItems();
		this.SearchFormObj = new CSearchForm(this._bigSearchForm, searchParts.SmallForm,
			searchParts.DownButton.Cont, searchParts.UpButton.Cont, this.SearchFormId,
			lookForBigInp, searchParts.LookFor);
		if (null != this._searchIn) {
			this.SearchFormObj.SetSearchIn(this._searchIn);
		}
		this._lookForSmallObj = searchParts.LookFor;
		searchParts.LookFor.onkeypress = function (ev) {
			if (isEnter(ev)) {
				obj.RequestSearchResults();
			}
		};
		searchParts.ActionImg.onclick = function () {
			obj.RequestSearchResults();
		};

		toolbar.AddClearDiv();
		toolbar.Hide();
	};

	this.BuildMessageContainer = function(mainTd)
	{
		var div = CreateChild(mainTd, 'div');
		div.className = 'wm_message_container';
		if (this._messageHorBordersWidth == undefined) {
			var borders = GetBorders(div);
			this._messageHorBordersWidth = borders.Left + borders.Right;
			this._messageVertBordersWidth = borders.Top + borders.Bottom;
		}
		
		var tbl = CreateChild(div, 'table');
		var tr = tbl.insertRow(0);
		var td = tr.insertCell(0);

		this._picturesControl.Build(td);
		this._sensivityControl.Build(td);
 		this._readConfirmationTbl = this._readConfirmationControl.Build(td);
 		this._previewPaneMessageHeaders = new CPreviewPaneMessageHeaders();
		this._previewPaneMessageHeaders.Build(td);
		tr = tbl.insertRow(1);
		td = tr.insertCell(0);
		this._msgViewer = new CMessageViewer();
		this._msgViewer.Build(td, 0);
		this._msgViewer.SetSwitcher(this._previewPaneMessageHeaders.SwitcherCont, 'wm_message_right', this._previewPaneMessageHeaders.SwitcherObj);
	};
	
	this.EnableDeleteTools = function ()
	{
		if (this.DeleteLikePop3()) {
			this._pop3DeleteTool.enabled = true;
			this._pop3DeleteTool.className = "wm_tb";
		}
		if (this.DeleteLikeImap()) {
			this._imap4DeleteTool.Enable();
		}
	};

	this.EnableTools = function ()
	{
		this._checkMailTool.Enable();
		this.EnableDeleteTools();
		this._notSpamTool.Enable();
		this._isSpamTool.Enable();
	};

	this.ChangeHeaderFieldsLang = function()
	{
		if (this._fromColumn != undefined) this._fromColumn.SetContent();
		if (this._dateColumn != undefined) this._dateColumn.SetContent();
		if (this._sizeColumn != undefined) this._sizeColumn.SetContent();
		if (this._subjectColumn != undefined) this._subjectColumn.SetContent();
	};
	
	this.ResizeInboxWidth = function ()
	{
		var offsetWidth = this._inboxContainer.offsetWidth;
		if (offsetWidth) {
			var width = this._inboxWidth - this._inboxTable.HorBordersWidth;
			if (offsetWidth > width) {
				this._inboxTable.Resize(width);
			} else {
				this._inboxTable.Resize(offsetWidth);
			}
		}
	};
	
	this.RepairToolBar = function ()
	{
		if (this.isBuilded) {
			this._isSpamTool.Hide();
			this._notSpamTool.Hide();

			if (window.HideSpamButton !== true) {
				if (this.IsSpam()) {
					this._notSpamTool.Show();
				}
				else if (!this.IsSent() && !this.IsDrafts()) {
					this._isSpamTool.Show();
				}
			}

			if (this.DeleteLikePop3()) {
				this._pop3DeleteTool.className = 'wm_tb';
				this._imap4DeleteTool.Hide();
			}
			else {
				this._pop3DeleteTool.className = 'wm_hide';
				this._imap4DeleteTool.Show();
			}
			
			if (WebMail.Accounts.CurrMailProtocol == POP3_PROTOCOL) {
				if (this.isInboxDirectMode) {
					if (this.IsInbox()) {
						this._markTool.className = 'wm_hide';
						this.HideSearchForm();
						if (null != this._inboxMoveItem) this._inboxMoveItem.className = 'wm_menu_item';
					}
					else {
						this._markTool.className = 'wm_tb';
						this.ShowSearchForm();
						if (null != this._inboxMoveItem) this._inboxMoveItem.className = 'wm_hide';
					}
					this._dragNDrop.SetMoveToInbox(false);
				}
				else {
					this._markTool.className = 'wm_tb';
					this.ShowSearchForm();
					if (null != this._inboxMoveItem) this._inboxMoveItem.className = 'wm_menu_item';
					this._dragNDrop.SetMoveToInbox(true);
				}
			}
			else { //WebMail.Accounts.CurrMailProtocol == IMAP4_PROTOCOL || WebMail.Accounts.CurrMailProtocol == WMSERVER_PROTOCOL
				if (this.shown) {
					if (this.isDirectMode && WebMail.Accounts.CurrMailProtocol == WMSERVER_PROTOCOL) {
						this.HideSearchForm();
					} else {
						this.ShowSearchForm();
					}
				}
				this._markTool.className = 'wm_tb';
			}
			if (this.IsDrafts()) {
				this._replyTool.className = 'wm_hide';
				this._forwardTool.Hide();
			} else {
				this._replyTool.className = 'wm_tb';
				this._forwardTool.Show();
			}
			if (this._folderId == -1 && this._folderFullName.length == 0 && this._lookForStr.length > 0) {
				this._toolBar.DisableInSearch(true);
			} else {
				this._toolBar.DisableInSearch(false);
			}
		}
	}; // RepairToolBar

	this.SetAccountSize = function (size)
	{
		this._spaceInfoObj.SetAccountSize(size);
	};
	
	this.WriteMsgsCountInFolder = function (count)
	{
		this._messagesInFolder.innerHTML = (this._lookForStr.length > 0) ?
			count + ' ' + Lang.Messages :
			count + ' ' + Lang.MessagesInFolder;
	};
	
	this.RequestSearchResults = function()
	{
		var search = this.GetSearchParameters();
		
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
		this.ShowSearchForm();
	}; // RequestSearchResults

	this.StartHiddenCheckMail = function()
	{
		if (this._checkMailTool.Enabled()) {
			this._checkMailTool.Disable();
			WebMail.CheckMail.Start(true);
		}
	}
}

CMessageListTopPaneScreen.prototype = MessageListPrototype;

function CSpaceInfo()
{
	this._enable = true;
	this._mailboxLimit = 0;
	this._mailboxSize = 0;
	this._accountSize = 0;
	
	this._progressBarObj = null;
	this._mailboxUsedObj = null;
	this._accountUsedObj = null;
	
	this.ParseSettings = function (settings)
	{
		this._enable = settings.EnableMailboxSizeLimit;
		this._mailboxLimit = settings.MailBoxLimit;
		this._mailboxSize = settings.MailBoxSize;
		this._accountSize = settings.AccountSize;
		this._fill();
	};
	
	this.SetAccountSize = function (size)
	{
		this._accountSize = size;
		this._fill();
	};
	
	this._checkPercent = function (percent)
	{
		if (percent > 100) {
			percent = 100;
		} else if (percent < 0) {
			percent = 0;
		}
		return percent;
	};
	
	this._fill = function ()
	{
		if (!this._enable || this._mailboxLimit == 0 || !this._builded) {
			return;
		}
		var mailboxPercent, accountWidth, mailboxWidth;
		mailboxPercent = 0;
		accountWidth = 0;
		if (this._mailboxLimit > 0) {
			mailboxPercent = this._checkPercent(Math.round(this._mailboxSize / this._mailboxLimit * 100));
			accountWidth = this._checkPercent(Math.round(this._accountSize / this._mailboxLimit * 100));
		}
		mailboxWidth = 0;
		if (mailboxPercent > accountWidth) {
			mailboxWidth = mailboxPercent - accountWidth;
		}
		this._progressBarObj.title = Lang.YouUsing + ' ' + mailboxPercent + '% ' + Lang.OfYour + ' ' + GetFriendlySize(this._mailboxLimit);
		this._mailboxUsedObj.style.width = mailboxWidth + 'px';
		this._accountUsedObj.style.width = accountWidth + 'px';
	};
	
	this.Build = function (container, settings)
	{
		this.ParseSettings(settings);
		if (this._enable && this._mailboxLimit > 0) {
			var div, usedDiv;
			this._progressBarObj = CreateChild(container, 'span');
			this._progressBarObj.className = 'wm_lowtoolbar_space_info';
			div = CreateChild(this._progressBarObj, 'div');
			div.className = 'wm_progressbar';

			usedDiv = CreateChild(div, 'div');
			usedDiv.className = 'wm_progressbar_used';
			this._accountUsedObj = usedDiv;

			usedDiv = CreateChild(div, 'div');
			usedDiv.className = 'wm_progressbar_all_used';
			this._mailboxUsedObj = usedDiv;

			this._builded = true;
		}
		this._fill();
	};
}

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}