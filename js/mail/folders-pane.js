/*
 * Classes:
 * 	CFoldersPane(htmlParent, dragNDrop, VResizer, resizingObjectsContainer, withToolBar)
 *  CFolderParams(id, fullName, sentDraftsType, type, syncType, count, newCount, name, intIndent)
 */

function CFoldersPane(htmlParent, dragNDrop, VResizer, resizingObjectsContainer, withToolBar)
{
	this._displayed = (WebMail.Settings.HideFolders) ? false : true;
	this._displayWidth = WebMail.Settings.VertResizer;
	this._width = WebMail.Settings.VertResizer;
	this._minHiddenWidth = 5;
	this._minDisplayedWidth = 125;
	
	this._mainContainer = null;
	this._toolBar = null;
	this._withToolBar = withToolBar;
	
	this._hideContainer = null;
	this._hideControl = null;
	
	this.List = null;
	
	this._manageLinkContainer = null;

	this._contentPreDiv = null;
	this._contentAfterDiv = null;
	
	this._vertResizerObj = null;
	this._build(htmlParent, dragNDrop, VResizer, resizingObjectsContainer);
}

CFoldersPane.prototype = 
{
	Show: function ()
	{
		var obj = this;
		this._width = this._displayWidth;
		this._displayed = true;
		CreateCookie('wm_hide_folders', 0, COOKIE_STORAGE_DAYS);
		this._hideControl.className = 'wm_folders_hide wm_control_img';
		this._hideControl.title = Lang.HideFolders;
		this.List.className = 'wm_folders';
		this._manageLinkContainer.className = 'wm_manage_folders';
		this._hideControl.onclick = function() {
			obj.Hide(); 
		};
		this._vertResizerObj.busy(this._width);
		WebMail.ResizeBody(RESIZE_MODE_FOLDERS);
	},
	
	Hide: function ()
	{
		var obj = this;
		this._width = Validator.CorrectNumber(this._hideControl.offsetWidth, this._minHiddenWidth);
		this._displayed = false;
		CreateCookie('wm_hide_folders', 1, COOKIE_STORAGE_DAYS);
		this._hideControl.className = 'wm_folders_show wm_control_img';
		this._hideControl.title = Lang.ShowFolders;
		this.List.className = 'wm_hide';
		this._manageLinkContainer.className = 'wm_hide';
		this._hideControl.onclick = function() { obj.Show(); };
		this._vertResizerObj.free();
		WebMail.ResizeBody(RESIZE_MODE_FOLDERS);
	},
	
	CleanList: function ()
	{
		CleanNode(this.List);
	},
	
	ResizeHeight: function (height)
	{
		if (Validator.IsPositiveNumber(height) && height >=100) {
			this._foldersHeight = height;
			this._calculateBorders();
			var allHeight = this._foldersHeight - this._vertBordersWidth;
			this._mainContainer.style.height = allHeight + 'px';
			var toolBarHeight = (this._withToolBar) ? this._toolBar.GetHeight() : this._hideContainer.offsetHeight;
			this.List.style.height = (allHeight - toolBarHeight - this._manageLinkContainer.offsetHeight -
				this._contentPreDiv.offsetHeight - this._contentAfterDiv.offsetHeight) + 'px';
			this._vertResizerObj.updateVerticalSize(height);
		}
	},
	
	GetHeight: function ()
	{
		return this._mainContainer.offsetHeight;
	},
	
	GetWidth: function ()
	{
		return this._width;
	},
	
	SetResizerMinRightPos: function (minRightPos)
	{
		this._vertResizerObj.updateMinRightWidth(minRightPos);
	},
	
	_calculateWidth: function (maxWidth, leftMargin)
	{
		var foldersImgWidth = (this._withToolBar) ? this._minDisplayedWidth : this._hideControl.offsetWidth;
		if (this._displayed || this._withToolBar) {
			var leftPos = this._vertResizerObj.LeftPosition;
			var minWidth = Validator.CorrectNumber(foldersImgWidth, this._minDisplayedWidth);
			var width = Validator.CorrectNumber(leftPos, minWidth, maxWidth);
			this._vertResizerObj.LeftPosition = width;
			return width - leftMargin;
		}
		else {
			return Validator.CorrectNumber(foldersImgWidth, this._minHiddenWidth, maxWidth);
		}
	},
	
	_calculateBorders: function ()
	{
		if (this._horBordersWidth == undefined) {
			var borders = GetBorders(this.List);
			this._horBordersWidth = borders.Left + borders.Right;
			this._vertBordersWidth = borders.Top + borders.Bottom;
		}
	},
	
	ResizeWidth: function (maxWidth, leftMargin)
	{
		if (leftMargin == undefined) leftMargin = 0;
		this._width = this._calculateWidth(maxWidth, leftMargin);
		this._calculateBorders();
		var innerWidth = this._width - this._horBordersWidth;
		this._mainContainer.style.width = this._width + 'px';
		this.List.style.width = innerWidth + 'px';
		if (!this._withToolBar) {
			this._hideContainer.style.width = innerWidth + 'px';
		}
		if (!this._withToolBar && this._displayed) {
			this._displayWidth = this._width;
			this._vertResizerObj.LeftPosition = this._width;
		}
		CreateCookie('wm_vert_resizer', this._width, COOKIE_STORAGE_DAYS);
		return this._width;
	},
	
	ChangeSkin: function ()
	{
		if (this._withToolBar) return;
		this._hideControl.className = (this._displayed) ? 'wm_folders_hide wm_control_img' : 'wm_folders_show wm_control_img';
	},
	
	_buildToolBar: function ()
	{
		this._toolBar = new CToolBar(this._mainContainer, TOOLBAR_VIEW_NEW_MESSAGE);
		this._toolBar.AddItem(TOOLBAR_NEW_MESSAGE, NewMessageClickHandler, true);
	},

	_build: function (htmlParent, dragNDrop, VResizer, resizingObjectsContainer)
	{
		var resizerWidth = 4;
		this._vertResizerObj = new CVerticalResizer(VResizer, resizingObjectsContainer, resizerWidth, this._minDisplayedWidth, 551, 
			this._displayWidth, 'WebMail.ResizeBody(RESIZE_MODE_FOLDERS);');

		this._mainContainer = htmlParent;//CreateChild(htmlParent, 'div');
		this._mainContainer.className = (this._withToolBar) ? 'wm_folders_part wm_folders_basic_view' : 'wm_folders_part';
		if (this._withToolBar) {
			this._buildToolBar();
		}
		else {
			this._hideContainer = CreateChild(this._mainContainer, 'div', [['class', 'wm_folders_hide_show']]);
			this._hideControl = CreateChild(this._hideContainer, 'span', [['class', 'wm_folders_hide wm_control_img'], ['title', Lang.HideFolders]]);
			this._hideControl.innerHTML = '&nbsp;';
		}

		this._contentPreDiv = CreateChild(this._mainContainer, 'div', [['class', 'wm_folders_top_corners']]);

		var contentDiv = CreateChild(this._mainContainer, 'div', [['class', 'wm_folders_content']]);
		
		this.List = CreateChild(contentDiv, 'div');
		this.List.className = 'wm_folders_list';
		dragNDrop.SetDropContainer(this.List);

		this._manageLinkContainer = CreateChild(contentDiv, 'div', [['align', 'center'], ['class', 'wm_manage_folders']]);
		/*var linkParent = this._manageLinkContainer;
		if (this._withToolBar) {
			linkParent = CreateChild(this._manageLinkContainer, 'div', [['class', 'wm_manage_folders_link_container']]);
		}*/
		var a = CreateChild(this._manageLinkContainer, 'a', [['href', '#']]);
		a.innerHTML = Lang.ManageFolders;
		WebMail.LangChanger.Register('innerHTML', a, 'ManageFolders', '');
		a.onclick = function () {
			SetHistoryHandler(
				{
					ScreenId: SCREEN_USER_SETTINGS,
					Entity: PART_MANAGE_FOLDERS,
					SetIdAcct: true,
					NewMode: false
				}
			);
			return false;
		};

		this._contentAfterDiv = CreateChild(this._mainContainer, 'div', [['class', 'wm_folders_bottom_corners']]);

		if (this._withToolBar) {
			CreateChild(this._contentPreDiv, 'div', [['class', 'wm_manage_folders_corner1']]);
			CreateChild(this._contentPreDiv, 'div', [['class', 'wm_manage_folders_corner2']]);
			CreateChild(this._contentPreDiv, 'div', [['class', 'wm_manage_folders_corner3']]);
			
			CreateChild(this._contentAfterDiv, 'div', [['class', 'wm_manage_folders_corner3 bottom']]);
			CreateChild(this._contentAfterDiv, 'div', [['class', 'wm_manage_folders_corner2 bottom']]);
			CreateChild(this._contentAfterDiv, 'div', [['class', 'wm_manage_folders_corner1 bottom']]);
		}

		var obj = this;
		if (this._withToolBar || this._displayed) {
			if (!this._withToolBar) {
				this._hideControl.onclick = function() {
					obj.Hide();
				};
			}
		}
		else {
			this._width = Validator.CorrectNumber(this._hideControl.offsetWidth, this._minHiddenWidth);
			this._hideControl.className = 'wm_folders_show wm_control_img';
			this._hideControl.title = Lang.ShowFolders;
			this.List.className = 'wm_hide';
			this._manageLinkContainer.className = 'wm_hide';
			this._hideControl.onclick = function() {
				obj.Show();
			};
			this._vertResizerObj.free();
		}
	}
}

function CFolderParams(id, fullName, sentDraftsType, type, syncType, count, newCount, name, intIndent) {
	this._id = id;
	this._fullName = fullName;
	this.SentDraftsType = sentDraftsType;
	this.Type = type;
	this._imgType = type;
	this._syncType = syncType;
	this._div = null;
	this.Page = 1;
	this.MsgsCount = count;
	this._newMsgsCount = newCount;
	this.SearchResults = false;
	this.Name = name;
	this._skinName = '';
	this._toRemoveCount = 0;
	this._toAppendCount = 0;
	this._unreadedToRemoveCount = 0;
	this._unreadedToAppendCount = 0;
	this._toReadCount = 0;
	this._toUnreadCount = 0;
	this._title = null;
	this._clickHandler = null;
	this._intIndent = intIndent;
}

CFolderParams.prototype = {
	ChangeImgType: function ()
	{
		if (this._syncType != SYNC_TYPE_NO && this._syncType != SYNC_TYPE_DIRECT_MODE) {
			switch (this.Type) {
				case FOLDER_TYPE_DEFAULT:
				case FOLDER_TYPE_SYSTEM:
					this._imgType = FOLDER_TYPE_DEFAULT_SYNC;
					break;
				case FOLDER_TYPE_INBOX:
					this._imgType = FOLDER_TYPE_INBOX_SYNC;
					break;
				case FOLDER_TYPE_SENT:
					this._imgType = FOLDER_TYPE_SENT_SYNC;
					break;
				case FOLDER_TYPE_DRAFTS:
					this._imgType = FOLDER_TYPE_DRAFTS_SYNC;
					break;
				case FOLDER_TYPE_TRASH:
					this._imgType = FOLDER_TYPE_TRASH_SYNC;
					break;
			}
		}
	},
	
	SetPage: function (page)
	{
		this.Page = page;
	},
	
	SetDiv: function (div, skinName, clickHandler)
	{
		this._div = div;
		this._clickHandler = clickHandler;
		if (this._title != null) {
			this._div.title = this._title;
		}
		this._skinName = skinName;
		this.SetFolderNameText();
	},

	GetDiv: function ()
	{
		return this._div;
	},
	
	SetFolderNameText: function ()
	{
		var a, secondDiv, desc, spanImg, nameSpan, innerHtml, marginName, ediv;
		CleanNode(this._div);
		secondDiv = CreateChild(this._div, 'div');
		a = CreateChild(secondDiv, 'a');
		a.href = '#';
		a.onclick = this._clickHandler;
		
		desc = FolderDescriptions[this._imgType];
		spanImg = CreateChild(a, 'span', [['class', 'wm_folder_img']]);
		spanImg.innerHTML = '&nbsp;';
		spanImg.style.backgroundPosition = '-' + desc.x * X_ICON_SHIFT + 'px -' + desc.y * Y_ICON_SHIFT + 'px';
		
		nameSpan = CreateChild(a, 'span', [['class', 'wm_folder_name']]);
		innerHtml = this.Name;
		if (this._newMsgsCount > 0) {
			innerHtml += '&nbsp;<span title="' + Lang.NewMessages + '">(' + this._newMsgsCount + ')</span>';
		}
		nameSpan.innerHTML = innerHtml;
		
		if (window.RTL) {
			marginName = GetMarginRight(nameSpan);
			if (!isNaN(marginName)) {
				secondDiv.style.paddingRight = this._intIndent + marginName + 'px';
			}
		} else {
			marginName = GetMarginLeft(nameSpan);
			if (!isNaN(marginName)) {
				secondDiv.style.paddingLeft = this._intIndent + marginName + 'px';
			}
		}

		if (this.MsgsCount > 0) {
			if (this.Type == FOLDER_TYPE_TRASH || this.Type == FOLDER_TYPE_TRASH_SYNC) {
				ediv = CreateChild(secondDiv, 'span', [['class', 'wm_clear_folder'], ['title', Lang.EmptyTrash]]);
				WebMail.LangChanger.Register('title', ediv, 'EmptyTrash', '');
				ediv.onclick = CreateToolBarItemClick(TOOLBAR_PURGE);
				ediv.innerHTML = '&nbsp;';
			} else if (this.Type == FOLDER_TYPE_SPAM || this.Type == FOLDER_TYPE_SPAM_SYNC) {
				ediv = CreateChild(secondDiv, 'span', [['class', 'wm_clear_folder'], ['title', Lang.EmptySpam]]);
				WebMail.LangChanger.Register('title', ediv, 'EmptySpam', '');
				ediv.onclick = CreateToolBarItemClick(TOOLBAR_EMPTY_SPAM);
				ediv.innerHTML = '&nbsp;';
			}
		}
	},
	
	ChangeMsgsCounts: function (count, newCount, isSearchResults)
	{
		if (this.MsgsCount != count || this._newMsgsCount != newCount) {
			WebMail.DataSource.Cache.SetFolderMessagesCount(this._id, this._fullName, count, newCount, WebMail._idAcct);
		}
		this.MsgsCount = count;
		this._newMsgsCount = newCount;
		this.SearchResults = isSearchResults;
		this.SetFolderNameText();
	},
	
	AddToAppend: function (count, unreaded)
	{
		this._toAppendCount += count;
		this._unreadedToAppendCount += unreaded;
	},

	AddToRemove: function (count, unreaded)
	{
		this._toRemoveCount += count;
		this._unreadedToRemoveCount += unreaded;
	},
	
	Append: function ()
	{
		this.MsgsCount += this._toAppendCount;
		this._newMsgsCount += this._unreadedToAppendCount;
		WebMail.DataSource.Cache.SetFolderMessagesCount(this._id, this._fullName, this.MsgsCount, this._newMsgsCount, WebMail._idAcct);
		this._toAppendCount = 0;
		this._unreadedToAppendCount = 0;
		this.SetFolderNameText();
	},
	
	Remove: function ()
	{
		this.MsgsCount += -this._toRemoveCount;
		if (this.MsgsCount < 0) {
			this.MsgsCount = 0;
		}
		this._newMsgsCount += -this._unreadedToRemoveCount;
		if (this._newMsgsCount < 0) {
			this._newMsgsCount = 0;
		}
		WebMail.DataSource.Cache.SetFolderMessagesCount(this._id, this._fullName, this.MsgsCount, this._newMsgsCount, WebMail._idAcct);
		this._toRemoveCount = 0;
		this._unreadedToRemoveCount = 0;
		this.SetFolderNameText();
	},
	
	AddAllToRead: function ()
	{
		this._toReadCount = this._newMsgsCount;
	},
	
	AddAllToUnread: function ()
	{
		this._toUnreadCount = this.MsgsCount - this._newMsgsCount;
	},
	
	AddToRead: function (count)
	{
		this._toReadCount += count;
	},
	
	AddToUnread: function (count)
	{
		this._toUnreadCount += count;
	},
	
	Read: function (count)
	{
		if (count) {
			this._newMsgsCount += -count;
		} else {
			this._newMsgsCount += -this._toReadCount;
			this._toReadCount = 0;
		}
		if (this._newMsgsCount < 0) {
			this._newMsgsCount = 0;
		}
		WebMail.DataSource.Cache.SetFolderMessagesCount(this._id, this._fullName, this.MsgsCount, this._newMsgsCount, WebMail._idAcct);
		this.SetFolderNameText();
	},
	
	Unread: function ()
	{
		this._newMsgsCount += this._toUnreadCount;
		WebMail.DataSource.Cache.SetFolderMessagesCount(this._id, this._fullName, this.MsgsCount, this._newMsgsCount, WebMail._idAcct);
		this._toUnreadCount = 0;
		this.SetFolderNameText();
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}