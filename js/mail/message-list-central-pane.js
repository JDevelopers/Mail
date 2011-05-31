/*
 * Classes:
 * 	CMessageListCentralPane(container, PopupMenus)
 */

function CMessageListCentralPane(container, PopupMenus)
{
	this._sortDescriptions = [
		{ Field: SORT_FIELD_DATE, FieldLang: 'SortFieldDate', Order: SORT_ORDER_ASC, OrderLang: 'SortOrderAscending' },
		{ Field: SORT_FIELD_DATE, FieldLang: 'SortFieldDate', Order: SORT_ORDER_DESC, OrderLang: 'SortOrderDescending' },
		{ Field: SORT_FIELD_FROM, FieldLang: 'SortFieldFrom', Order: SORT_ORDER_ASC, OrderLang: 'SortOrderAscending' },
		{ Field: SORT_FIELD_FROM, FieldLang: 'SortFieldFrom', Order: SORT_ORDER_DESC, OrderLang: 'SortOrderDescending' },
		{ Field: SORT_FIELD_SIZE, FieldLang: 'SortFieldSize', Order: SORT_ORDER_ASC, OrderLang: 'SortOrderAscending' },
		{ Field: SORT_FIELD_SIZE, FieldLang: 'SortFieldSize', Order: SORT_ORDER_DESC, OrderLang: 'SortOrderDescending' },
		{ Field: SORT_FIELD_SUBJECT, FieldLang: 'SortFieldSubject', Order: SORT_ORDER_ASC, OrderLang: 'SortOrderAscending' },
		{ Field: SORT_FIELD_SUBJECT, FieldLang: 'SortFieldSubject', Order: SORT_ORDER_DESC, OrderLang: 'SortOrderDescending' },
		{ Field: SORT_FIELD_FLAG, FieldLang: 'SortFieldFlag', Order: SORT_ORDER_ASC, OrderLang: 'SortOrderAscending' },
		{ Field: SORT_FIELD_FLAG, FieldLang: 'SortFieldFlag', Order: SORT_ORDER_DESC, OrderLang: 'SortOrderDescending' },
		{ Field: SORT_FIELD_ATTACH, FieldLang: 'SortFieldAttachments', Order: SORT_ORDER_ASC, OrderLang: 'SortOrderAscending' },
		{ Field: SORT_FIELD_ATTACH, FieldLang: 'SortFieldAttachments', Order: SORT_ORDER_DESC, OrderLang: 'SortOrderDescending' },
	];
	//this._sortDescriptions.length=13 in ie7
	this._sortDescriptionsLength = 12;
		
	this.PageSwitcherBar = null;
	this._messageListDisplay = null;
	
	this._toolBar = null;
	this._checkMailTool = null;
	this._markTool = null;
	this._moveMenu = null;
	this._inboxMoveItem = null;
	this._pop3DeleteTool = null;
	this._imap4DeleteTool = null;
	this._isSpamTool = null;
	this._notSpamTool = null;
	
	this.Id = '.message_list_central_pane.';
	this.SearchFormId = 'search_form' + this.Id;
	this.SearchFormObj = null;
	this._lookForSmallObj = null;
	this._bigSearchForm = null;
	this._searchIn = null;
	this._quickSearch = null;
	this._slowSearch = null;
	
	this._additionalBar = null;

	this.MainContainer = CreateChild(container, 'div');
	var borders = GetBorders(this.MainContainer);
	this._horBordersWidth = borders.Left + borders.Right;
	this._vertBordersWidth = borders.Top + borders.Bottom;
	this._buildToolBar(PopupMenus);
	this._buildAdditionalBar();
}

CMessageListCentralPane.prototype = {
	ResizeHeight: function (height) {
		var innerHeight = height - this._vertBordersWidth;
		this.MainContainer.style.height = innerHeight + 'px';
		var toolBarHeight = this._toolBar.GetHeight();
		var lowToolBarHeight = this.PageSwitcherBar.offsetHeight;
		var additionalBarHeight = this._additionalBar.offsetHeight;
		this._messageListDisplay.SetLinesHeight(innerHeight - toolBarHeight - lowToolBarHeight - additionalBarHeight);
	},
	
	ResizeWidth: function (width)
    {
		this._messageListDisplay.Resize(width - this._horBordersWidth);
	},
	
	RepairDeleteTools: function (deleteLikePop3)
	{
		if (deleteLikePop3) {
			this._pop3DeleteTool.className = 'wm_tb';
			this._imap4DeleteTool.Hide();
		}
		else {
			this._pop3DeleteTool.className = 'wm_hide';
			this._imap4DeleteTool.Show();
		}
	},
	
	RepairSpamTools: function (showSpam, showNotSpam)
	{
		this._isSpamTool.Hide();
		this._notSpamTool.Hide();
		if (showSpam) {
			this._isSpamTool.Show();
		}
		if (showNotSpam) {
			this._notSpamTool.Show();
		}
	},
	
	EnableDeleteTools: function (deleteLikePop3)
	{
		if (deleteLikePop3) {
			this._pop3DeleteTool.enabled = true;
			this._pop3DeleteTool.className = "wm_tb";
		}
		else {
			this._imap4DeleteTool.Enable();
		}
	},
	
	EnableTools: function (deleteLikePop3)
	{
		this._checkMailTool.Enable();
		this.EnableDeleteTools(deleteLikePop3);
		this._isSpamTool.Enable();
		this._notSpamTool.Enable();
	},

	EnableCheckMailTool: function ()
	{
		this._checkMailTool.Enable();
	},
	
	DisableCheckMailTool: function ()
	{
		var allowCheckMail = false;
	    if (this._checkMailTool.Enabled()) {
	        this._checkMailTool.Disable();
	        allowCheckMail = true;
	    }
	    return allowCheckMail;
	},
	
	ShowMarkTool: function ()
	{
		this._markTool.className = 'wm_tb';
	},
	
	HideMarkTool: function ()
	{
		this._markTool.className = 'wm_hide';
	},
	
	DisableInSearch: function (disable)
	{
		this._toolBar.DisableInSearch(disable);
	},
	
	
	ShowInboxMoveItem: function ()
	{
		if (this._inboxMoveItem == null) return;
		this._inboxMoveItem.className = 'wm_menu_item';
	},
	
	HideInboxMoveItem: function ()
	{
		if (this._inboxMoveItem == null) return;
		this._inboxMoveItem.className = 'wm_hide';
	},
	
	_buildToolBar: function (PopupMenus)
	{
		this._toolBar = new CToolBar(this.MainContainer, TOOLBAR_VIEW_WITH_CURVE);
		//check mail tool
		var obj = this;
		this._checkMailTool = this._toolBar.AddItem(TOOLBAR_CHECK_MAIL, function () {
			var allowCheckMail = obj.DisableCheckMailTool();
			if (allowCheckMail) {
				WebMail.CheckMail.Start();
			}
		}, true);

		//mark tool; absent in inbox in direct mode in pop3
		this._markTool = this._toolBar.AddMarkItem(PopupMenus, false);
		
		//move to folder tool; absent in inbox in direct mode in pop3
		var div = CreateChild(document.body, 'div');
		this._moveMenu = div;
		div.className = 'wm_hide';
		this._toolBar.AddMoveItem(TOOLBAR_MOVE_TO_FOLDER, PopupMenus, div, false);
		
		//delete tools
		this._pop3DeleteTool = this._toolBar.AddPop3DeleteItem(PopupMenus, false);
		var deleteFunc = CreateToolBarItemClick(TOOLBAR_DELETE);
		this._imap4DeleteTool = this._toolBar.AddItem(TOOLBAR_DELETE, deleteFunc, false);
		
		// spam tools
		this._isSpamTool = this._toolBar.AddItem(TOOLBAR_IS_SPAM, function () {RequestMessagesOperationHandler(TOOLBAR_IS_SPAM, [], []);}, false);
		this._notSpamTool = this._toolBar.AddItem(TOOLBAR_NOT_SPAM, function () {RequestMessagesOperationHandler(TOOLBAR_NOT_SPAM, [], []);}, false);
	},
	
	SetCurrSearchFolder: function (id, fullName)
	{
		this._searchIn.value = id + STR_SEPARATOR + fullName;
	},
	
	RequestSearchResults: function ()
	{
		var screen = WebMail.Screens[WebMail.ListScreenId];
		if (screen) {
			screen.RequestSearchResults();
		}
	},
	
	_fillSortMenu: function ()
	{
		var createSortFunc = function (field, order) {
			return function () {
				SortMessagesHandler.call({ SortField: field, SortOrder: order });
			}
		}
		for (var sortDescIndex = 0; sortDescIndex < this._sortDescriptionsLength; sortDescIndex++) {
			var sortDescription = this._sortDescriptions[sortDescIndex];
			var item = CreateChild(this._sortMenu, 'div');
			item.innerHTML = Lang[sortDescription.FieldLang] + ', ' + Lang[sortDescription.OrderLang];
			item.onclick = createSortFunc(sortDescription.Field, sortDescription.Order);
		}
	},
	
	SetSort: function (sortField, sortOrder)
	{
		for (var sortDescIndex = 0; sortDescIndex < this._sortDescriptionsLength; sortDescIndex++) {
			var sortDescription = this._sortDescriptions[sortDescIndex];
			if (sortDescription.Field == sortField && sortDescription.Order == sortOrder) {
				this._sortTitle.innerHTML = Lang[sortDescription.FieldLang] + ', ' + Lang[sortDescription.OrderLang];
			}
		}
	},
	
	_buildSortPopup: function (container)
	{
		this._sortMenu = CreateChild(document.body, 'div');
		this._sortMenu.className = 'wm_hide';
		
		var title = CreateChild(container, 'span', [['class', 'wm_arranged_by_title']]);
		title.innerHTML = Lang.ArrangedBy  + ': ';
		
		var sortReplace = CreateChild(container, 'span', [['class', 'wm_sort_popup_control']]);
		
		var sortControl;
		if (window.RTL) {
			sortControl = CreateChild(sortReplace, 'span');
		}
		
		this._sortTitle = CreateChild(sortReplace, 'span', [['class', 'wm_toolbar_item']]);
		this._fillSortMenu();
		
		if (!window.RTL) {
			sortControl = CreateChild(sortReplace, 'span');
		}
		sortControl.className = 'wm_toolbar_item';
		sortControl.innerHTML = '<span class="wm_control_icon"> </span>'
		
		var sortPopupMenu = new CPopupMenu(this._sortMenu, sortControl, 'wm_sort_popup_menu', sortReplace, this._sortTitle, 
			'wm_sort_popup_control', 'wm_sort_popup_control', 'wm_toolbar_item', 'wm_toolbar_item');
		WebMail.PopupMenus.addItem(sortPopupMenu);
	},
	
	_buildAdditionalBar: function ()
	{
		this._additionalBar = CreateChild(this.MainContainer, 'div', [['class', 'wm_additional_bar wm_central_pane_view']]);
		//vasil
		CreateChild(this._additionalBar, 'div', [['class', 'wm_additional_bar_corner1']]);
		CreateChild(this._additionalBar, 'div', [['class', 'wm_additional_bar_corner2']]);
		CreateChild(this._additionalBar, 'div', [['class', 'wm_additional_bar_corner3']]);
		var additionalBarCont = CreateChild(this._additionalBar, 'div', [['class', 'wm_additional_bar_container']]);

		var lookForBigInp = this.BuildAdvancedSearchForm();
		var searchParts = this._toolBar.AddSearchItems(additionalBarCont, true);
		this.SearchFormObj = new CSearchForm(this._bigSearchForm, searchParts.SmallForm, searchParts.DownButton.Cont,
			searchParts.UpButton.Cont, this.SearchFormId, lookForBigInp, searchParts.LookFor, true);
		if (null != this._searchIn) {
			this.SearchFormObj.SetSearchIn(this._searchIn);
		}
		this._lookForSmallObj = searchParts.LookFor;
		var obj = this;
		searchParts.LookFor.onkeypress = function (ev) {
			if (isEnter(ev)) {
				obj.RequestSearchResults();
			}
		};
		searchParts.ActionImg.onclick = function () {
			obj.RequestSearchResults();
		};

		// var div = CreateChild(this._additionalBar, 'div', [['class', 'wm_list_checkbox_container']]);
		var div = CreateChild(additionalBarCont, 'span', [['class', 'wm_list_checkbox_container']]);
		this._checkbox = CreateChild(div, 'input', [['type', 'checkbox']]);
		// this._buildSortPopup(this._additionalBar);
		this._buildSortPopup(additionalBarCont);

		// div = CreateChild(this._additionalBar, 'div', [['class', 'wm_additional_bar_right_border']]);
		// div = CreateChild(additionalBarCont, 'div', [['class', 'wm_additional_bar_right_border']]);

		CreateChild(additionalBarCont, 'div', [['class', 'clear']]);
	},
	
	Build: function (messageListDisplay) {
		this._messageListDisplay = messageListDisplay;
		
		this._messageListDisplay._selection.SetCheckBox(this._checkbox);

		this.PageSwitcherBar = CreateChild(this.MainContainer, 'div', [['class', 'wm_page_switcher_bar']]);

		CreateChild(this.PageSwitcherBar, 'div', [['class', 'wm_page_switcher_container']]);
		CreateChild(this.PageSwitcherBar, 'div', [['class', 'wm_page_switcher_corner3']]);
		CreateChild(this.PageSwitcherBar, 'div', [['class', 'wm_page_switcher_corner2']]);
		CreateChild(this.PageSwitcherBar, 'div', [['class', 'wm_page_switcher_corner1']]);
	}
}

CMessageListCentralPane.prototype.CleanMoveMenu = MessageListPrototype.CleanMoveMenu;
CMessageListCentralPane.prototype.AddToMoveMenu = MessageListPrototype.AddToMoveMenu;

CMessageListCentralPane.prototype.Pop3DeleteToolEnabled = MessageListPrototype.Pop3DeleteToolEnabled;
CMessageListCentralPane.prototype.AlreadyPop3Deleted = MessageListPrototype.AlreadyPop3Deleted;
CMessageListCentralPane.prototype.DisablePop3DeleteTool = MessageListPrototype.DisablePop3DeleteTool;
CMessageListCentralPane.prototype.ClearDeleteTools = MessageListPrototype.ClearDeleteTools;
CMessageListCentralPane.prototype.ImapDeleteToolEnabled = MessageListPrototype.ImapDeleteToolEnabled;
CMessageListCentralPane.prototype.AlreadyImapDeleted = MessageListPrototype.AlreadyImapDeleted;
CMessageListCentralPane.prototype.DisableImapDeleteTool = MessageListPrototype.DisableImapDeleteTool;
CMessageListCentralPane.prototype.SpamToolEnabled = MessageListPrototype.SpamToolEnabled;
CMessageListCentralPane.prototype.AlreadyMarkedSpam = MessageListPrototype.AlreadyMarkedSpam;
CMessageListCentralPane.prototype.EnableToolsByOperation = MessageListPrototype.EnableToolsByOperation;

CMessageListCentralPane.prototype.BuildAdvancedSearchForm = MessageListPrototype.BuildAdvancedSearchForm;
CMessageListCentralPane.prototype.GetSearchParameters = MessageListPrototype.GetSearchParameters;
CMessageListCentralPane.prototype.HideSearchFolders = MessageListPrototype.HideSearchFolders;
CMessageListCentralPane.prototype.CheckVisibilitySearchForm = MessageListPrototype.CheckVisibilitySearchForm;
CMessageListCentralPane.prototype.CleanSearchFolders = MessageListPrototype.CleanSearchFolders;
CMessageListCentralPane.prototype.ShowSearchForm = MessageListPrototype.ShowSearchForm;
CMessageListCentralPane.prototype.HideSearchForm = MessageListPrototype.HideSearchForm;
CMessageListCentralPane.prototype.PlaceSearchData = MessageListPrototype.PlaceSearchData;
CMessageListCentralPane.prototype.FocusSearchForm = MessageListPrototype.FocusSearchForm;
CMessageListCentralPane.prototype.AddToSearchFolders = MessageListPrototype.AddToSearchFolders;

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}