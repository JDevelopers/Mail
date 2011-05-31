/*
 * Classes:
 *  CToolButton(container, desc, handler, show)
 *  CToolBar(parent, viewMode)
 */

function CToolButton(container, desc, handler, show)
{
	this.Cont = container;
	this.Icon = null;
	this._text = null;
	this._hover = false;
	this._className = 'wm_toolbar_item';
	this._classNameOver = 'wm_toolbar_item_over';
	this._build(container, desc);
	this.ChangeHandler(handler);
	this.enabled = true;
	this.shown = false;
	if (show) {
		this.Show();
	}
	else {
		this.Hide();
	}
}

CToolButton.prototype = {
	Show: function ()
	{
		this.shown = true;
		this._setView();
	},

	Hide: function ()
	{
		this.shown = false;
		this._setView();
	},
	
	Disable: function ()
	{
		this.enabled = false;
		this._setView();
	},
	
	Enable: function ()
	{
		this.enabled = true;
		this._setView();
	},
	
	Enabled: function ()
	{
		return this.shown && this.enabled;
	},
	
	_setView: function ()
	{
		if (this.Enabled()) {
			var cn = this._className;
			var cno = this._classNameOver;
			if (this._hover) {
				this.Cont.onmouseover = function () { 
					this.className = cno; 
				};
				this.Cont.onmouseout = function () { 
					this.className = cn; 
				};
			}
			this.Cont.className = cn;
		}
		else {
			if (this._hover) {
				this.Cont.onmouseover = function () { };
				this.Cont.onmouseout = function () { };
			}
			if (this.shown) {
				this.Cont.className = this._className + ' wm_toolbar_item_disabled';
			}
			else {
				this.Cont.className = 'wm_hide';
			}
		}
	},
	
	ShowText: function ()
	{
		if (this._text == null) {
			return;
		}
		this._text.className = '';
	},
	
	HideText: function ()
	{
		if (this._text == null) {
			return;
		}
		var prop = ReadStyle(this.Icon, 'display');
		this._text.className = (prop == 'none') ? '' : 'wm_hide';
	},
	
	ChangeHandler: function (handler)
	{
		if (handler) {
			this.Cont.onclick = handler;
		}
	},
	
	ChangeClassName: function (className, classNameOver)
	{
		this._className = className;
		this._classNameOver = classNameOver;
		if (this.shown) {
			this.Show();
		}
	},
	
	_build: function (container, desc)
	{
		var div = CreateChild(container, 'span', [['class', 'wm_toolbar_icon'],
			['style', 'background-position: -' + desc.x * X_ICON_SHIFT + 'px -' + desc.y * Y_ICON_SHIFT + 'px']]);
		div.innerHTML = '&nbsp;';
		var titleLangField = (desc.titleLangField) ? desc.titleLangField : desc.langField;
		if (titleLangField) {
			div.title = Lang[titleLangField];
			WebMail.LangChanger.Register('title', div, titleLangField, '');
		}
		if (desc.iconClassName) {
			div.className = desc.iconClassName;
		}
		this.Icon = div;
		
		if (desc.langField) {
			var span = CreateChild(container, 'span');
			span.innerHTML = Lang[desc.langField];
			WebMail.LangChanger.Register('innerHTML', span, desc.langField, '');
			this._text = span;
		}
		
		if (typeof desc.hover == 'undefined') {
			this._hover = true;
		}
		if (typeof desc.className != 'undefined') {
			this._className = desc.className;
		}
		if (typeof desc.classNameOver != 'undefined') {
			this._classNameOver = desc.classNameOver;
		}
	}
};

function CToolBar(parent, viewMode)
{
	this._viewMode = (viewMode != undefined) ? viewMode : TOOLBAR_VIEW_STANDARD;
	
	this.table = CreateChild(parent, 'div');
	this.table.className = 'wm_toolbar';
	this._container = CreateChild(this.table, 'span');
	this._container.className = 'wm_toolbar_content';
	if (this._viewMode != TOOLBAR_VIEW_STANDARD) {
		this._buildCurve();
	}
	
	// for Safari and Chrome to not display hidden menus
	document.body.style.cursor = 'auto';
	
	this._descriptions = [];
	this._descriptions[TOOLBAR_NEW_MESSAGE] = {langField: 'NewMessage', x: 0, y: 0};
	this._descriptions[TOOLBAR_CHECK_MAIL] = {langField: 'CheckMail', x: 1, y: 0};
	this._descriptions[TOOLBAR_RELOAD_FOLDERS] = {langField: 'ReloadFolders', x: 2, y: 0};
	this._descriptions[TOOLBAR_REPLY] = {langField: 'Reply', x: 3, y: 0};
	this._descriptions[TOOLBAR_REPLYALL] = {langField: 'ReplyAll', x: 4, y: 0, className: 'wm_menu_item', classNameOver: 'wm_menu_item_over'};
	this._descriptions[TOOLBAR_FORWARD] = {langField: 'Forward', x: 5, y: 0};
	this._descriptions[TOOLBAR_MARK_READ] = {langField: 'MarkAsRead', x: 6, y: 0};
	this._descriptions[TOOLBAR_MOVE_TO_FOLDER] = {langField: 'MoveToFolder', x: 7, y: 0};
	this._descriptions[TOOLBAR_DELETE] = {langField: 'Delete', x: 8, y: 0};
	this._descriptions[TOOLBAR_UNDELETE] = {langField: 'Undelete', x: 9, y: 0, className: 'wm_menu_item wm_delete_menu', classNameOver: 'wm_menu_item_over wm_delete_menu'};
	this._descriptions[TOOLBAR_PURGE] = {langField: 'PurgeDeleted', x: 10, y: 0, className: 'wm_menu_item wm_delete_menu', classNameOver: 'wm_menu_item_over wm_delete_menu'};
	this._descriptions[TOOLBAR_EMPTY_TRASH] = {langField: 'EmptyTrash', x: 11, y: 0, className: 'wm_menu_item wm_delete_menu', classNameOver: 'wm_menu_item_over wm_delete_menu'};
	this._descriptions[TOOLBAR_IS_SPAM] = {langField: 'OperationSpam', x: 12, y: 0};
	this._descriptions[TOOLBAR_NOT_SPAM] = {langField: 'OperationNotSpam', x: 13, y: 0};
	this._descriptions[TOOLBAR_EMPTY_SPAM] = {langField: 'EmptySpam', x: 19, y: 0, className: 'wm_menu_item wm_delete_menu', classNameOver: 'wm_menu_item_over wm_delete_menu'};
	this._descriptions[TOOLBAR_SEARCH] = {x: 14, y: 0, iconClassName: 'wm_search_icon_standard', className: 'wm_toolbar_search_item', classNameOver: 'wm_toolbar_search_item_over'};
	this._descriptions[TOOLBAR_BIG_SEARCH] = {x: 15, y: 0};
	this._descriptions[TOOLBAR_SEARCH_ARROW_DOWN] = {x: 16, y: 0, iconClassName: 'wm_search_icon', className: 'wm_toolbar_search_item', classNameOver: 'wm_toolbar_search_item_over'};
	this._descriptions[TOOLBAR_SEARCH_ARROW_UP] = {x: 17, y: 0, iconClassName: 'wm_search_icon', className: 'wm_toolbar_search_item', classNameOver: 'wm_toolbar_search_item_over'};
	this._descriptions[TOOLBAR_ARROW] = {x: 18, y: 0, iconClassName: 'wm_control_icon'};
	this._descriptions[TOOLBAR_LIGHT_SEARCH_ARROW_DOWN] = {x: 0, y: 6, iconClassName: 'wm_search_icon', className: 'wm_toolbar_search_item', classNameOver: 'wm_toolbar_search_item_over'};
	this._descriptions[TOOLBAR_LIGHT_SEARCH_ARROW_UP] = {x: 1, y: 6, iconClassName: 'wm_search_icon', className: 'wm_toolbar_search_item', classNameOver: 'wm_toolbar_search_item_over'};

	this._descriptions[TOOLBAR_BACK_TO_LIST] = {langField: 'BackToList', x: 0, y: 1};
	this._descriptions[TOOLBAR_SEND_MESSAGE] = {langField: 'SendMessage', x: 1, y: 1};
	this._descriptions[TOOLBAR_SAVE_MESSAGE] = {langField: 'SaveMessage', x: 2, y: 1};
	this._descriptions[TOOLBAR_HIGH_IMPORTANCE] = {langField: 'High', x: 3, y: 1, className: 'wm_menu_item wm_importance_menu', classNameOver: 'wm_menu_item_over wm_importance_menu'};
	this._descriptions[TOOLBAR_NORMAL_IMPORTANCE] = {langField: 'Normal', x: 4, y: 1, className: 'wm_menu_item wm_importance_menu', classNameOver: 'wm_menu_item_over wm_importance_menu'};
	this._descriptions[TOOLBAR_LOW_IMPORTANCE] = {langField: 'Low', x: 5, y: 1, className: 'wm_menu_item wm_importance_menu', classNameOver: 'wm_menu_item_over wm_importance_menu'};
	this._descriptions[TOOLBAR_PRINT_MESSAGE] = {langField: 'Print', x: 6, y: 1};
	this._descriptions[TOOLBAR_IMPORTANCE] = {langField: 'Importance', x: 15, y: 1};
	this._descriptions[TOOLBAR_CANCEL] = {langField: 'Cancel', x: 6, y: 5};

	this._descriptions[TOOLBAR_SENSIVITY] = {langField: 'SensivityMenu', x: 7, y: 5};
	this._descriptions[TOOLBAR_SENSIVITY_NOTHING] = {langField: 'SensivityNothingMenu', x: 7, y: 5, className: 'wm_menu_item wm_importance_menu', classNameOver: 'wm_menu_item_over wm_importance_menu'};
	this._descriptions[TOOLBAR_SENSIVITY_CONFIDENTIAL] = {langField: 'SensivityConfidentialMenu', x: 7, y: 5, className: 'wm_menu_item wm_importance_menu', classNameOver: 'wm_menu_item_over wm_importance_menu'};
	this._descriptions[TOOLBAR_SENSIVITY_PRIVATE] = {langField: 'SensivityPrivateMenu', x: 7, y: 5, className: 'wm_menu_item wm_importance_menu', classNameOver: 'wm_menu_item_over wm_importance_menu'};
	this._descriptions[TOOLBAR_SENSIVITY_PERSONAL] = {langField: 'SensivityPersonalMenu', x: 7, y: 5, className: 'wm_menu_item wm_importance_menu', classNameOver: 'wm_menu_item_over wm_importance_menu'};

	this._descriptions[TOOLBAR_TEST] = {langField: 'TestButton', x: 0, y: 0, iconClassName: 'wm_hide'};

	var nextActiveX = 7;
	var nextInactiveX = 8;
	var prevActiveX = 9;
	var prevInactiveX = 10;
	if (window.RTL) {
		nextActiveX = 9;
		nextInactiveX = 10;
		prevActiveX = 7;
		prevInactiveX = 8;
	}
	this._descriptions[TOOLBAR_NEXT_ACTIVE] = {x: nextActiveX, y: 1, titleLangField: 'NextMsg', iconClassName: 'wm_navigate_icon'};
	this._descriptions[TOOLBAR_NEXT_INACTIVE] = {x: nextInactiveX, y: 1, titleLangField: 'NextMsg', hover: false, iconClassName: 'wm_navigate_icon'};
	this._descriptions[TOOLBAR_PREV_ACTIVE] = {x: prevActiveX, y: 1, titleLangField: 'PreviousMsg', iconClassName: 'wm_navigate_icon'};
	this._descriptions[TOOLBAR_PREV_INACTIVE] = {x: prevInactiveX, y: 1, titleLangField: 'PreviousMsg', hover: false, iconClassName: 'wm_navigate_icon'};
	this._descriptions[TOOLBAR_NEW_CONTACT] = {langField: 'NewContact', x: 11, y: 1};
	this._descriptions[TOOLBAR_NEW_GROUP] = {langField: 'NewGroup', x: 12, y: 1};
	this._descriptions[TOOLBAR_ADD_CONTACTS_TO] = {langField: 'AddContactsTo', x: 13, y: 1};
	this._descriptions[TOOLBAR_IMPORT_CONTACTS] = {langField: 'ImportContacts', x: 14, y: 1};
	
	this._descriptions[TOOLBAR_FLAG] = {langField: 'MarkFlag', x: 0, y: 3, className: 'wm_menu_item', classNameOver: 'wm_menu_item_over'};
	this._descriptions[TOOLBAR_UNFLAG] = {langField: 'MarkUnflag', x: 1, y: 3, className: 'wm_menu_item', classNameOver: 'wm_menu_item_over'};
	this._descriptions[TOOLBAR_MARK_ALL_READ] = {langField: 'MarkAllRead', x: 2, y: 3, className: 'wm_menu_item', classNameOver: 'wm_menu_item_over'};
	this._descriptions[TOOLBAR_MARK_ALL_UNREAD] = {langField: 'MarkAllUnread', x: 3, y: 3, className: 'wm_menu_item', classNameOver: 'wm_menu_item_over'};
	this._descriptions[TOOLBAR_MARK_UNREAD] = {langField: 'MarkAsUnread', x: 4, y: 3, className: 'wm_menu_item', classNameOver: 'wm_menu_item_over'};
	
	this._buttons = [];
	
	//this._purgeTool = null;
	this._separatorAll = null;
	this._readAllTool = null;
	this._unreadAllTool = null;
}

CToolBar.prototype = {
	_buildCurve: function ()
	{
		var leftClassName = (this._viewMode == TOOLBAR_VIEW_WITH_CURVE) ? 'wm_toolbar_curve_left' : 'wm_toolbar_new_message_left';
		var rightClassName = (this._viewMode == TOOLBAR_VIEW_WITH_CURVE) ? 'wm_toolbar_curve_right' : 'wm_toolbar_new_message_right';
		
		CreateChild(this.table, 'div', [['class', 'wm_toolbar_curve_inner ' + leftClassName]]);
		CreateChild(this.table, 'div', [['class', 'wm_toolbar_curve_outer ' + leftClassName]]);
		CreateChild(this.table, 'div', [['class', 'wm_toolbar_curve_inner ' + rightClassName]]);
		CreateChild(this.table, 'div', [['class', 'wm_toolbar_curve_outer ' + rightClassName]]);
	},
	
	Show: function ()
	{
		this.table.className = 'wm_toolbar';
	},

	Hide: function ()
	{
		this.table.className = 'wm_hide';
	},

	GetHeight: function ()
	{
		return this.table.offsetHeight;
	},

	ShowTextLabels: function () {
		var iCount = this._buttons.length;
		for (var i = 0; i < iCount; i++) {
			this._buttons[i].ShowText();
		}
	},
	
	HideTextLabels: function () {
		var iCount = this._buttons.length;
		for (var i = 0; i < iCount; i++) {
			this._buttons[i].HideText();
		}
	},

	AddClearDiv: function () {
		CreateChild(this.table, 'div', [['class', 'clear']]);
	},
	
	AddItem: function (itemId, clickHandler, show) {
		var div = CreateChild(this._container, 'span');
		var button = new CToolButton(div, this._descriptions[itemId], clickHandler, show);
		this._buttons.push(button);
		return button;
	},
	
	AddMarkItem: function (popupMenus, show) {
		var markMenu = CreateChild(document.body, 'div');
		markMenu.className = 'wm_hide';
		for (var i = TOOLBAR_MARK_UNREAD; i <= TOOLBAR_MARK_ALL_UNREAD; i++) {
			var div = CreateChild(markMenu, 'div');
			var markFunc = CreateToolBarItemClick(i);
			var button = new CToolButton(div, this._descriptions[i], markFunc, true);
			this._buttons.push(button);
			switch (i) {
			case TOOLBAR_UNFLAG:
				div = CreateChild(markMenu, 'div');
				div.className = 'wm_menu_separate';
				this._separatorAll = div;
				break;
			case TOOLBAR_MARK_ALL_READ:
				this._readAllTool = button;
				break;
			case TOOLBAR_MARK_ALL_UNREAD:
				this._unreadAllTool = button;
				break;
			}
		}

		var markReplace = CreateChild(this._container, 'span');
		markReplace.className = (show) ? 'wm_tb' : 'wm_hide';

		var markControl;
		//vasil
		/* if (window.RTL) {
			markControl = CreateChild(markReplace, 'span');
			markControl.className = 'wm_toolbar_item';
			button = new CToolButton(markControl, this._descriptions[TOOLBAR_ARROW], null, true);
			this._buttons.push(button);
		} */
		var markTitle = CreateChild(markReplace, 'span');
		markTitle.className = 'wm_toolbar_item';
		markFunc = CreateToolBarItemClick(TOOLBAR_MARK_READ);
		button = new CToolButton(markTitle, this._descriptions[TOOLBAR_MARK_READ], markFunc, true);
		this._buttons.push(button);
		
		//vasil
		// if (!window.RTL) {
			markControl = CreateChild(markReplace, 'span');
			markControl.className = 'wm_toolbar_item';
			button = new CToolButton(markControl, this._descriptions[TOOLBAR_ARROW], null, true);
			this._buttons.push(button);
		// }

		var markPopupMenu = new CPopupMenu(markMenu, markControl, 'wm_popup_menu', markReplace, markTitle, 'wm_tb', 'wm_tb_press', 'wm_toolbar_item', 'wm_toolbar_item_over');
		popupMenus.addItem(markPopupMenu);
		return markReplace;
	},
	
	AddMoveItem: function (id, popupMenus, moveMenu, show) {
		var moveControl = CreateChild(this._container, 'span');
		moveControl.className = (show) ? 'wm_toolbar_item' : 'wm_hide';
		var button = new CToolButton(moveControl, this._descriptions[id], null, true);
		this._buttons.push(button);

		if (window.RTL) { 
			CreateTextChild(moveControl, ' '); 
		}
		button = new CToolButton(moveControl, this._descriptions[TOOLBAR_ARROW], null, true);
		this._buttons.push(button);

		var movePopupMenu = new CPopupMenu(moveMenu, moveControl, 'wm_popup_menu', moveControl, moveControl, 'wm_toolbar_item', 'wm_toolbar_item_press', 'wm_toolbar_item', 'wm_toolbar_item_over');
		popupMenus.addItem(movePopupMenu);
		return moveControl;
	},
	
	AddImportanceItem: function (popupMenus, importanceMenu) {
		var importanceControl = CreateChild(this._container, 'span');
		importanceControl.className = 'wm_toolbar_item';
		var button = new CToolButton(importanceControl, this._descriptions[TOOLBAR_IMPORTANCE], null, true);
		this._buttons.push(button);
		button = new CToolButton(importanceControl, this._descriptions[TOOLBAR_ARROW], null, true);
		this._buttons.push(button);
		var div = CreateChild(importanceMenu, 'div');
		var lowButton = new CToolButton(div, this._descriptions[TOOLBAR_LOW_IMPORTANCE], null, true);
		this._buttons.push(lowButton);
		div = CreateChild(importanceMenu, 'div');
		var normalButton = new CToolButton(div, this._descriptions[TOOLBAR_NORMAL_IMPORTANCE], null, true);
		this._buttons.push(normalButton);
		div = CreateChild(importanceMenu, 'div');
		var highButton = new CToolButton(div, this._descriptions[TOOLBAR_HIGH_IMPORTANCE], null, true);
		this._buttons.push(highButton);

		var importancePopupMenu = new CPopupMenu(importanceMenu, importanceControl, 'wm_popup_menu', importanceControl,
			importanceControl, 'wm_toolbar_item', 'wm_toolbar_item_press', 'wm_toolbar_item', 'wm_toolbar_item_over');
		popupMenus.addItem(importancePopupMenu);
		return {Low: lowButton, Normal: normalButton, High: highButton};
	},

	AddSensivityItem: function (popupMenus, sensivityMenu) {
		var sensivityControl = CreateChild(this._container, 'span');
		sensivityControl.className = 'wm_toolbar_item';
		var button = new CToolButton(sensivityControl, this._descriptions[TOOLBAR_SENSIVITY], null, true);
		this._buttons.push(button);
		button = new CToolButton(sensivityControl, this._descriptions[TOOLBAR_ARROW], null, true);
		this._buttons.push(button);
		var div = CreateChild(sensivityMenu, 'div');
		var nothingButton = new CToolButton(div, this._descriptions[TOOLBAR_SENSIVITY_NOTHING], null, true);
		this._buttons.push(nothingButton);
		div = CreateChild(sensivityMenu, 'div');
		var confButton = new CToolButton(div, this._descriptions[TOOLBAR_SENSIVITY_CONFIDENTIAL], null, true);
		this._buttons.push(confButton);
		div = CreateChild(sensivityMenu, 'div');
		var privateButton = new CToolButton(div, this._descriptions[TOOLBAR_SENSIVITY_PRIVATE], null, true);
		this._buttons.push(privateButton);
		div = CreateChild(sensivityMenu, 'div');
		var personalButton = new CToolButton(div, this._descriptions[TOOLBAR_SENSIVITY_PERSONAL], null, true);
		this._buttons.push(personalButton);

		var sensivityPopupMenu = new CPopupMenu(sensivityMenu, sensivityControl, 'wm_popup_menu', sensivityControl,
			sensivityControl, 'wm_toolbar_item', 'wm_toolbar_item_press', 'wm_toolbar_item', 'wm_toolbar_item_over');
		popupMenus.addItem(sensivityPopupMenu);
		return {Nothing: nothingButton, Confidential: confButton, Private: privateButton, Personal: personalButton};
	},
	
	AddReplyItem: function (popupMenus, show, replyFunc, replyAllFunc)
	{
		var replyMenu = CreateChild(document.body, 'div');
		replyMenu.className = 'wm_hide';
		var div = CreateChild(replyMenu, 'div');
		replyAllFunc = (replyAllFunc == undefined) ? CreateReplyClick(TOOLBAR_REPLYALL) : replyAllFunc;
		var replyAllButton = new CToolButton(div, this._descriptions[TOOLBAR_REPLYALL], replyAllFunc, true);
		this._buttons.push(replyAllButton);

		var replyReplace = CreateChild(this._container, 'span');
		replyReplace.className = (show) ? 'wm_tb' : 'wm_hide';

		var replyControl, arrowButton;
		
		//vasil
		/* if (window.RTL) {
			replyControl = CreateChild(replyReplace, 'span');
			arrowButton = new CToolButton(replyControl, this._descriptions[TOOLBAR_ARROW], null, true);
			this._buttons.push(arrowButton);
		} */
		
		var replyTitle = CreateChild(replyReplace, 'span');
		replyFunc = (replyFunc == undefined) ? CreateReplyClick(TOOLBAR_REPLY) : replyFunc;
		var replyButton = new CToolButton(replyTitle, this._descriptions[TOOLBAR_REPLY], replyFunc, true);
		this._buttons.push(replyButton);
		replyTitle.onclick = replyFunc;
		
		//vasil
		// if (!window.RTL) {
			replyControl = CreateChild(replyReplace, 'span');
			arrowButton = new CToolButton(replyControl, this._descriptions[TOOLBAR_ARROW], null, true);
			this._buttons.push(arrowButton);
		// }

		var replyPopupMenu = new CPopupMenu(replyMenu, replyControl, 'wm_popup_menu', replyReplace, replyTitle,
			'wm_tb', 'wm_tb_press', 'wm_toolbar_item', 'wm_toolbar_item_over');
		popupMenus.addItem(replyPopupMenu);
		return {ReplyReplace: replyReplace, ReplyButton: replyButton, ReplyAllButton: replyAllButton, ReplyPopupMenu: replyPopupMenu};
	},
	
	AddPop3DeleteItem: function (popupMenus, show) 
	{
		var deleteMenu = CreateChild(document.body, 'div');
		deleteMenu.className = 'wm_hide';
		
		var div = CreateChild(deleteMenu, 'div');
		var deleteFunc = CreateToolBarItemClick(TOOLBAR_PURGE);
		var button = new CToolButton(div, this._descriptions[TOOLBAR_EMPTY_TRASH], deleteFunc, true);
		this._buttons.push(button);
		
		var spamEmpty = CreateChild(deleteMenu, 'div');
		spamEmpty.className = 'wm_menu_item wm_delete_menu';
		var deleteSpamFunc = CreateToolBarItemClick(TOOLBAR_EMPTY_SPAM);
		button = new CToolButton(spamEmpty, this._descriptions[TOOLBAR_EMPTY_SPAM], deleteSpamFunc, true);
		this._buttons.push(button);
		
		var deleteReplace = CreateChild(this._container, 'span');
		deleteReplace.className = (show) ? 'wm_tb' : 'wm_hide';
		
		var deleteControl;
		//vasil
		// if (window.RTL) {
			// deleteControl = CreateChild(deleteReplace, 'span');
			// button = new CToolButton(deleteControl, this._descriptions[TOOLBAR_ARROW], null, true);
			// this._buttons.push(button);
		// }

		var deleteTitle = CreateChild(deleteReplace, 'span');
		deleteFunc = CreateToolBarItemClick(TOOLBAR_DELETE);
		button = new CToolButton(deleteTitle, this._descriptions[TOOLBAR_DELETE], deleteFunc, true);
		this._buttons.push(button);
		
		//vasil
		// if (!window.RTL) {
			deleteControl = CreateChild(deleteReplace, 'span');
			button = new CToolButton(deleteControl, this._descriptions[TOOLBAR_ARROW], null, true);
			this._buttons.push(button);
		// }

		var deletePopupMenu = new CPopupMenu(deleteMenu, deleteControl, 'wm_popup_menu', deleteReplace, deleteTitle, 
			'wm_tb', 'wm_tb_press', 'wm_toolbar_item', 'wm_toolbar_item_over');
		popupMenus.addItem(deletePopupMenu);
			
		return deleteReplace;
	},
	
	CreateSearchButton: function (parent, handler)
	{
		var desc = this._descriptions[TOOLBAR_BIG_SEARCH];
		var span = CreateChild(parent, 'span', [['class', 'wm_search_icon_advanced wm_control'],
			['style', 'background-position: -' + desc.x * X_ICON_SHIFT + 'px -' + desc.y * Y_ICON_SHIFT + 'px']]);
		span.innerHTML = '&nbsp;';
		span.onclick = handler;
	},

	AddSearchItems: function (container, centralPaneView) {
		if (container == undefined) container = this.table;
		
		var arrowDownId = (centralPaneView) ? TOOLBAR_LIGHT_SEARCH_ARROW_DOWN : TOOLBAR_SEARCH_ARROW_DOWN;
		var downControl = CreateChild(container, 'span');
		var downButton = new CToolButton(downControl, this._descriptions[arrowDownId], null, true);

		var arrowUpId = (centralPaneView) ? TOOLBAR_LIGHT_SEARCH_ARROW_UP : TOOLBAR_SEARCH_ARROW_UP;
		var upControl = CreateChild(container, 'span');
		var upButton = new CToolButton(upControl, this._descriptions[arrowUpId], null, true);

		var smallSearchForm = CreateChild(container, 'span');
		
		if (window.RTL) {
			downButton.Cont.style.marginRight = '0';
			upButton.Cont.style.marginRight = '0';
			smallSearchForm.style.marginLeft = '0';
		}
		else {
			downButton.Cont.style.marginLeft = '0';
			upButton.Cont.style.marginLeft = '0';
			smallSearchForm.style.marginRight = '0';
		}
		
		var lookFor = CreateChild(smallSearchForm, 'input', [['type', 'text'], ['class', 'wm_search_input'],
			['maxlength', '255']]);
		var actionButton = new CToolButton(smallSearchForm, this._descriptions[TOOLBAR_SEARCH], null, true);

		return {DownButton: downButton, UpButton: upButton, SmallForm: smallSearchForm, ActionImg: actionButton.Icon,
			LookFor: lookFor};
	},
	
	DisableInSearch: function (mode) {
		if (mode) {
			//this._purgeTool.Hide();
			if (this._separatorAll != null) {
				this._separatorAll.className = 'wm_hide';
				this._readAllTool.Hide();
				this._unreadAllTool.Hide();
			}
		} else {
			//this._purgeTool.Show();
			if (this._separatorAll != null) {
				this._separatorAll.className = 'wm_menu_separate';
				this._readAllTool.Show();
				this._unreadAllTool.Show();
			}
		}
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}