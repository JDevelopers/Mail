/*
 * Classes:
 *  CContactsScreen(skinName)
 */
 
function CContactsScreen(skinName)
{
	this.Id = SCREEN_CONTACTS;
	this.isBuilded = false;
	this.hasCopyright = false;
	this.BodyAutoOverflow = true;
	this._skinName = skinName;
	this.Contacts = null;
	this.Contact = null;
	this.Groups = null; 
	this._groupsOutOfDate = true;
	this._groupsDeleted = false;
	this.HistoryArgs = null;
	
	this._mainDiv = null;

	this._bigSearchForm = null;
	this._searchIn = null;
	this.SearchFormObj = null;

	this._contactsToMenu = null;

	this._page = 1;
	this._pageSwitcher = null;
	this._contactsPerPage = 20;
	this._sortOrder = SORT_ORDER_DESC;
	this._sortField = SORT_FIELD_EMAIL;
	this._searchGroup = -1;
	this._lookFor = '';
	this.IdAddrForEdit = -1;

	this._contactsController = null;
	this._contactsTable = null;
	this._selection = new CSelection(FillSelectedContactsHandler);
	
	//logo + accountslist + toolbar + lowtoolbar
	this._externalHeight = 58 + 32 + 27 + 28 + 40;
	this._minListHeight = 150;//counted variable, depends on (contacts + groups) count on page
	this._listWidthPercent = 40;

	this._logo = null;
	this._accountsBar = null;
	this._toolBar = null;
	this._lowToolBar = null;
	this._contactsCount = null;

	this._contactViewerDiv = null;
	this._cardMinWidth = null;
	
	var obj = this;
	this._newContactObj = new CEditContactScreenPart(skinName, obj);
	this._viewContactObj = new CViewContactScreenPart(skinName);
	this._newGroupObj = new CEditGroupScreenPart(skinName, obj);
	this._importContactsObj = new CImportContactsScreenPart(skinName);
	this._selectedContactsObj = new CSelectedContactsScreenPart(obj);

	this._addContactsCount = 0;
	this._addGroupName = '';
	
	this._emptyCard = true;
}

CContactsScreen.prototype = {
	PlaceData: function(Data)
	{
		var Type = Data.Type;
		switch (Type){
			case TYPE_CONTACTS:
				if (this.HistoryArgs.Entity == PART_CONTACTS || null != this.Contacts) {
					this.ShowEmpty();
				}
				this._newContactObj.CheckContactUpdate();
				this.Contacts = Data;
				if (this._groupsOutOfDate || this._groupsDeleted) {
					GetHandler(TYPE_GROUPS, {}, [], '');
				}
				this.Fill();
				if (!this._groupsDeleted && this.HistoryArgs.Entity == PART_VIEW_GROUP) {
					this.RestoreFromHistory(this.HistoryArgs);
				}
				if (this.HistoryArgs.Entity == PART_EDIT_CONTACT) {
					SetHistoryHandler(
						{
							ScreenId: SCREEN_CONTACTS,
							Entity: PART_VIEW_CONTACT,
							IdAddr: this.HistoryArgs.IdAddr
						}
					);
				}
				if (Data.AddedContactId != -1 && this._newContactObj.DefEmail.length > 0) {
					WebMail.DataSource.Cache.ReplaceFromContactFromMessages(Data.AddedContactId, this._newContactObj.DefEmail);
				}
			break;
			case TYPE_CONTACT:
				this.Contact = Data;
				if (this.IdAddrForEdit == -1) {
					this._cardTitle.innerHTML = Lang.TitleViewContact;
					this._viewContactObj.UpdateContact(Data);
					this.ShowViewContact();
					var contactId = Data.GetIdForList(STR_SEPARATOR);
					this._selection.CheckLine(contactId);
				}
				else {
					this._cardTitle.innerHTML = Lang.TitleEditContact;
					this._newContactObj.Fill(this.Contact);
					this.ShowNewContact();
					this.IdAddrForEdit = -1;
				}
			break;
			case TYPE_GROUPS:
				this._newGroupObj.CheckGroupUpdate();
				this.Groups = Data;
				this._groupsOutOfDate = false;
				this._groupsDeleted = false;
				this.FillGroups();
				this._newContactObj.FillGroups(Data);
			break;
			case TYPE_GROUP:
				this.Group = Data;
				this.ShowNewGroup();
				this._cardTitle.innerHTML = Lang.TitleViewGroup;
				this._newGroupObj.Fill(Data);
			break;
			case TYPE_UPDATE:
				if (Data.Value == 'group') {
					if (this._addContactsCount > 0) {
						WebMail.DataSource.Cache.ClearAllContactsGroupsList();
						WebMail.ShowReport(Lang.ReportContactAddedToGroup + ' "' + this._addGroupName + '".');
						this._addContactsCount = 0;
						this._addGroupName = '';
						
						SetHistoryHandler(
							{
								ScreenId: SCREEN_CONTACTS,
								Entity: PART_CONTACTS,
								Page: this._page,
								SortField: this._sortField,
								SortOrder: this._sortOrder,
								SearchIn: this._searchGroup,
								LookFor: this._lookFor
							}
						);
					}
				} else if (Data.Value == 'sync_contacts') {
					WebMail.DataSource.Cache.ClearAllContactsGroupsList();
					WebMail.ShowReport(Lang.ReportContactSyncDone);

					SetHistoryHandler({
						ScreenId: SCREEN_CONTACTS,
						Entity: PART_CONTACTS,
						Page: this._page,
						SortField: this._sortField,
						SortOrder: this._sortOrder,
						SearchIn: this._searchGroup,
						LookFor: this._lookFor
					});
				}
			break;
		}
	},
	
	ClickBody: function(ev)
	{
		if (null != this.SearchFormObj) {
			this.SearchFormObj.checkVisibility(ev, Browser.Mozilla);
		}
	},

	KeyupBody: function (key, ev)
	{
		switch (key) {
			case Keys.Space:
				this._contactsTable.KeyUpHandler(Keys.Down, ev);
				break;
			case Keys.N:
				if (ev.shiftKey || ev.ctrlKey || ev.altKey) return;
				this.MailContactsTo();
				break;
			case Keys.S:
				if (ev.altKey) {
					this.SearchFormObj.FocusSmallForm();
				}
				break;
			default:
				this._contactsTable.KeyUpHandler(key, ev);
				break;
		}
	},

	ResizeBody: function()
	{
		//if (!Browser.IE || Browser.Version >= 7) {
			var listBorderHeight = 1;
			var lishHeaderHeight = 24;
			var height = GetHeight() - this.GetExternalHeight();
			if (height < this._minListHeight) {
				height = this._minListHeight;
			}
			var tableHeight = this._contactsTable.GetHeight();
			var cardHeight = this._cardTable.offsetHeight;
			if (height < tableHeight) {
				height = tableHeight;
			}
			if (height < cardHeight) {
				height = cardHeight;
			}
			this._mainDiv.style.height = height + 'px';
			this._contactsTable.SetLinesHeight(height - listBorderHeight - lishHeaderHeight);

			//var listWidth = this._leftDiv.offsetWidth;
			//this._contactsTable.Resize(listWidth);
			this._cardTable.style.width = 'auto';
			var cardWidth = this._cardTable.offsetWidth;
			var rightWidth = this._rightDiv.offsetWidth;
			if (cardWidth < rightWidth) {
				cardWidth = rightWidth;
			}
			this._cardTable.style.width = cardWidth - 1 + 'px';
			//if (listWidth != this._leftDiv.offsetWidth) {
				this._contactsTable.Resize(this._leftDiv.offsetWidth);
			//}
		/*}
		else {
			this._mainDiv.style.width = ((document.documentElement.clientWidth || document.body.clientWidth) < 850) && (!this._emptyCard) ? '850px' : '100%';
			var listWidth = this._leftDiv.offsetWidth;
			this._contactsTable.Resize(listWidth);

			var width = GetWidth();
			if (width < 850) width = 850;
			this._cardTable.style.width = width - listWidth - 4 + 'px';
		};*/
		this._pageSwitcher.Replace();
	},
	
	GetExternalHeight: function()
	{
		var res = 0;
		var offsetHeight = this._logo.offsetHeight;		if (offsetHeight) { res += offsetHeight; }
		offsetHeight = this._accountsBar.offsetHeight;	if (offsetHeight) { res += offsetHeight; } else { return this._externalHeight; }
		offsetHeight = this._toolBar.GetHeight();		if (offsetHeight) { res += offsetHeight; } else { return this._externalHeight; }
		offsetHeight = this._lowToolBar.offsetHeight;	if (offsetHeight) { res += offsetHeight; } else { return this._externalHeight; }
		this._externalHeight = res;
		return this._externalHeight;
	},

	Show: function(settings, historyArgs)
	{
		this._mainDiv.className = 'wm_contacts';
		this._lowToolBar.className = 'wm_lowtoolbar';
		this._toolBar.Show();
		if (WebMail.Settings.ShowTextLabels) {
			this._toolBar.ShowTextLabels();
		}
        else {
			this._toolBar.HideTextLabels();
		}
		if (null != this.SearchFormObj) {
			this.SearchFormObj.Show();
		}
		if (this.Groups == null || this._groupsOutOfDate || this._contactsPerPage != settings.ContactsPerPage) {
			this._contactsPerPage = settings.ContactsPerPage;
			if (null != historyArgs && 'undefined' != historyArgs.Page && 'undefined' != typeof historyArgs.Page && null != historyArgs.Page) {
				historyArgs.Page = 1;
			}
            WebMail.DataSource.Cache.ClearAllContactsGroupsList();
            this.Contacts = null;
			GetHandler(TYPE_GROUPS, {}, [], '');
		}
		if (null == historyArgs) {
			historyArgs = {Entity: PART_CONTACTS, Page: 1, SearchIn: 0, LookFor: '', SortField: CH_NAME, SortOrder: SORT_ORDER_DESC};
		}
		this.RestoreFromHistory(historyArgs);
		this.ResizeBody();
	},

    _checkContacts: function ()
    {
        if (null == this.Contacts) {
            var requestArgs = {
                Page: this._page,
                SortField: this._sortField,
                SortOrder: this._sortOrder,
                IdGroup: -1,
                LookFor: ''
            };
            this._requestContacts(requestArgs);
        }
    },

    _showCurrentContacts: function ()
    {
        var lastPage = this._pageSwitcher.GetLastPage(0, this._contactsPerPage);
        var page = (lastPage < this._page) ? lastPage : this._page;
        var requestArgs = {
            Page: page,
            SortField: this._sortField,
            SortOrder: this._sortOrder,
            IdGroup: this._searchGroup,
            LookFor: this._lookFor
        };
        this._requestContacts(requestArgs);
    },

    _showRequestedContacts: function (historyArgs)
    {
        var requestArgs = {
            Page: historyArgs.Page,
            SortField: !isNaN(historyArgs.SortField) ? historyArgs.SortField : this._sortField,
            SortOrder: !isNaN(historyArgs.SortOrder) ? historyArgs.SortOrder : this._sortOrder,
            IdGroup: !isNaN(historyArgs.SearchIn) ? historyArgs.SearchIn : this._searchGroup,
            LookFor: (typeof(historyArgs.LookFor) == 'string') ? historyArgs.LookFor : ''
        };
        this._requestContacts(requestArgs);
    },

    _requestContacts: function (requestArgs)
    {
        var contactsGroups = new CContacts(requestArgs.IdGroup, requestArgs.LookFor);
        var xml = contactsGroups.GetInXml();
        GetHandler(TYPE_CONTACTS, requestArgs, [], xml);
    },
	
	RestoreFromHistory: function (historyArgs)
	{
		this.HistoryArgs = historyArgs;
		if (historyArgs.Entity != PART_CONTACTS) {
            if (this._pageSwitcher.PagesCount > 0) {
                this._pageSwitcher.Show(0);
            }
    		this._checkContacts();
        }
		switch (historyArgs.Entity) {
			case PART_CONTACTS:
				if ('undefined' == typeof(historyArgs.Page) || 'undefined' == historyArgs.Page || null == historyArgs.Page) {
					this._showCurrentContacts();
				}
                else {
					this._showRequestedContacts(historyArgs);
				}
			break;
			case PART_NEW_CONTACT:
				this._selection.UncheckAll();
				var contact = new CContact();
				if (historyArgs.Name) {
					contact.Name = historyArgs.Name;
				}
				if (historyArgs.Email) {
					contact.hEmail = historyArgs.Email;
				}
				contact.UseFriendlyNm = true;
				this._newContactObj.Fill(contact);
				this._cardTitle.innerHTML = Lang.TitleNewContact;
				this.ShowNewContact();
			break;
			case PART_EDIT_CONTACT:
				if (this.Contact.Id == historyArgs.IdAddr) {
					this._newContactObj.Fill(this.Contact);
					this._cardTitle.innerHTML = Lang.TitleEditContact;
					this.ShowNewContact();
				}
				else {
					this.IdAddrForEdit = historyArgs.IdAddr;
					GetHandler(TYPE_CONTACT, { IdAddr: historyArgs.IdAddr }, [], '');
				}
			break;
			case PART_VIEW_CONTACT:
				this._contactsController.CurrIsGroup = false;
				this._contactsController.CurrId = historyArgs.IdAddr;
				GetHandler(TYPE_CONTACT, { IdAddr: historyArgs.IdAddr }, [], '');
			break;
			case PART_NEW_GROUP:
				this._selection.UncheckAll();
				var group = new CGroup();
				if (null != historyArgs.Contacts) group.Contacts = historyArgs.Contacts;
				this._cardTitle.innerHTML = Lang.TitleNewGroup;
				this._newGroupObj.Fill(group);
				this.ShowNewGroup();
			break;
			case PART_VIEW_GROUP:
				GetHandler(TYPE_GROUP, { IdGroup: historyArgs.IdGroup }, [], '');
			break;
			case PART_IMPORT_CONTACT:
				this._selection.UncheckAll();
				this.ShowImportContacts();
			break;
		}
	},
	
	ParseSettings: function () { },

	ContactsImported: function ()
	{
		WebMail.DataSource.Cache.ClearAllContactsGroupsList();
		SetHistoryHandler(
			{
				ScreenId: SCREEN_CONTACTS,
				Entity: PART_CONTACTS,
				Page: this._page,
				SortField: this._sortField,
				SortOrder: this._sortOrder,
				SearchIn: this._searchGroup,
				LookFor: this._lookFor
			}
		);
	},
	
	ShowSelectedContacts: function ()
	{
		this._showPartOfScreen('ShowSelectedContacts');
	},

	ShowEmpty: function ()
	{
		this._showPartOfScreen('ShowEmpty');
	},

	ShowNewContact: function ()
	{
		this._showPartOfScreen('ShowNewContact');
	},
	
	ShowViewContact: function ()
	{
		this._showPartOfScreen('ShowViewContact');
	},
	
	ShowNewGroup: function ()
	{
		this._showPartOfScreen('ShowNewGroup');
	},

	ShowImportContacts: function ()
	{
		this._showPartOfScreen('ShowImportContacts');
	},

	_showPartOfScreen: function (type)
	{
		if ('ShowEmpty' == type){
			this._contactViewerDiv.className = 'wm_hide';
			this._emptyCard = true;
		} else {
			this._contactViewerDiv.className = '';
			this._emptyCard = false;
		}
		
		this._selectedContactsObj.Hide();
		this._newContactObj.Hide();
		this._viewContactObj.Hide();
		this._newGroupObj.Hide();
		this._importContactsObj.Hide();

		switch (type) {
			case 'ShowSelectedContacts':
				this._cardTitle.innerHTML = Lang.TitleSelectedContacts;
				this._selectedContactsObj.Show();
				break;
			case 'ShowNewContact':
				this._newContactObj.Show();
				break;
			case 'ShowViewContact':
				this._viewContactObj.Show();
				break;
			case 'ShowNewGroup':
				this._newGroupObj.Show();
				break;
			case 'ShowImportContacts':
				this._cardTitle.innerHTML = Lang.TitleImportContacts;
				this._importContactsObj.Show();
				break;
		}
		
		this.ResizeBody();
	},

	Hide: function()
	{
		this._mainDiv.className = 'wm_hide';
		this._lowToolBar.className = 'wm_hide';
		this._toolBar.Hide();
		if (null != this.SearchFormObj) {
			this.SearchFormObj.Hide();
		}
		this._pageSwitcher.Hide();
	},
	
	GetXmlParams: function ()
	{
		var params = '';
		params += '<param name="page" value="' + this._page + '"/>';
		params += '<param name="sort_field" value="' + this._sortField + '"/>';
		params += '<param name="sort_order" value="' + this._sortOrder + '"/>';
		return params;
	},
	
	RequestSearchResults: function ()
	{
		SetHistoryHandler(
			{
				ScreenId: SCREEN_CONTACTS,
				Entity: PART_CONTACTS,
				Page: this._page,
				SortField: this._sortField,
				SortOrder: this._sortOrder,
				SearchIn: this._searchIn.value,
				LookFor: this.SearchFormObj.GetStringValue()
			}
		);
	},
	
	DeleteSelected: function ()
	{
		if (this._selection != null) {
			var idArray = this._selection.GetCheckedLines().IdArray;
			var iCount = idArray.length;
			if (iCount > 0) {
				var contacts = '';
				var groups = '';
				for (var i=0; i<iCount; i++) {
					var params = idArray[i].split(STR_SEPARATOR);
					if (params.length == 4)
						if (params[1] == '0') {
							contacts += '<contact id="' + params[0] + '"/>';
							WebMail.DataSource.Cache.RemoveFromContactFromMessages(params[0]);
							WebMail.DataSource.Cache.RemoveFromContactInGroups(params[0]);
						}
						else {
							groups += '<group id="' + params[0] + '"/>';
							WebMail.DataSource.Cache.RemoveFromGroupInContacts(params[0]);
						}
				}
				if (contacts.length != 0 || groups.length != 0) {
					if (groups.length != 0) this._groupsDeleted = true;
					var lastPage = this._pageSwitcher.GetLastPage(iCount);
					if (this._page > lastPage) this._page = lastPage;
					var xml = this.GetXmlParams();
					xml += '<contacts>' + contacts + '</contacts>';
					xml += '<groups>' + groups + '</groups>';
					if (confirm(Lang.ConfirmAreYouSure)) {
						WebMail.DataSource.Cache.ClearAllContactsGroupsList();
						RequestHandler('delete', 'contacts', xml);
					}
				}
			}
			else {
				alert(Lang.AlertNoContactsGroupsSelected);
			}
		}
	},
	
	AddContacts: function (id, name)
	{
		if (null != this._selection) {
			var contacts = (id == '-1') ? Array() : '';
			var idArray = this._selection.GetCheckedLines().IdArray;
			var iCount = idArray.length;
			var contactsCount = 0;
			var contactsForCacheAdding = Array();
			for (var i=0; i<iCount; i++) {
				var params = idArray[i].split(STR_SEPARATOR);
				if (params.length == 4)
					if (params[1] == '0') {
						if (id == '-1') {
							contacts[i] = {Id: params[0], Name: params[2], Email: params[3]};
						}
						else {
							contacts += '<contact id="' + params[0] + '"/>';
							contactsCount++;
						}
						contactsForCacheAdding[i] = {Id: params[0], Name: params[2], Email: params[3]};
					}
			}
			if (contacts.length > 0) {
				if (id == '-1') {
					SetHistoryHandler(
						{
							ScreenId: SCREEN_CONTACTS,
							Entity: PART_NEW_GROUP,
							Contacts: contacts
						}
					);
				}
				else {
					var param = '<param name="id_group" value="' + id + '"/>';
					param += this.GetXmlParams();
					if (contacts.length > 0) {
						if (id != -1) {
							WebMail.DataSource.Cache.AddGroupToContacts(id, name, contactsForCacheAdding);
							var stringDataKey = WebMail.DataSource.GetStringDataKey(TYPE_GROUP, {IdGroup: id});
							WebMail.DataSource.Cache.AddContactsToGroup(stringDataKey, contactsForCacheAdding);
						}
						var xml = param + '<contacts>' + contacts + '</contacts>';
						RequestHandler('add', 'contacts', xml);
						this._addContactsCount = contactsCount;
						this._addGroupName = name;
					}
				}
			}
			else {
				alert(Lang.AlertNoContactsSelected);
			}
		}
	},
	
	MailContacts: function ()
	{
		MailAllHandlerWithDropDown(this._getStrForMailContacts());
	},

	MailContactsTo: function ()
	{
		MailToHandler(this._getStrForMailContacts());
	},

	NewMessageClick: function (ev)
	{
		NewMessageClickHandler(ev, this._getStrForMailContacts());
	},

	_getStrForMailContacts: function ()
	{
		var idArray = [];
		if (null != this._selection) {
			idArray = this._selection.GetCheckedLines().IdArray;
		}
		var iCount = idArray.length;
		var emailArray = [];
		for (var i=0; i<iCount; i++) {
			var params = idArray[i].split(STR_SEPARATOR);
			if (params.length == 4 && params[3].length != 0) {
				emailArray.push(HtmlDecode(params[3]));
			}
		}

		return emailArray.join(', ');
	},
	
	FillGroups: function ()
	{
		var sel = this._searchIn;
		CleanNode(sel);
		var opt = CreateChild(sel, 'option', [['value', '-1']]);
		opt.innerHTML = Lang.AllGroups;
		opt.selected = true;

		var obj = this;
		var menu = this._contactsToMenu;
		CleanNode(menu);
		var groups = this.Groups.Items;
		var iCount = groups.length;
		var div;
		for (var i=0; i<iCount; i++) {
			div = CreateChild(menu, 'div');
			div.className = 'wm_menu_item';
			div.onmouseover = function () { this.className='wm_menu_item_over'; };
			div.onmouseout = function () { this.className='wm_menu_item'; };
			div.id = groups[i].Id;
			div.innerHTML = groups[i].Name;
			div.onclick = function () { obj.AddContacts(this.id, this.innerHTML); };

			opt = CreateChild(sel, 'option', [['value', groups[i].Id]]);
			opt.innerHTML = groups[i].Name;
		}
		div = CreateChild(menu, 'div');
		div.className = 'wm_menu_item_spec';
		div.onmouseover = function () { this.className='wm_menu_item_over_spec'; };
		div.onmouseout = function () { this.className='wm_menu_item_spec'; };
		div.id = '-1';
		div.innerHTML = '- ' + Lang.NewGroup + ' -';
		div.onclick = function () { obj.AddContacts(this.id); };
	},
	
	Fill: function ()
	{
		this._sortField = this.Contacts.SortField;
		this._sortOrder = this.Contacts.SortOrder;
		this._searchGroup = this.Contacts.IdGroup;
		this._lookFor = this.Contacts.LookFor;
		
		if (this.Contacts.Count > 0) {
			this._contactsTable.UseSort();
			this._contactsTable.SetSort(this._sortField, this._sortOrder);
			this._contactsTable.Fill(this.Contacts.List);
		}
		else {
			this._contactsTable.FreeSort();
			this._contactsTable.CleanLines(Lang.InfoNoContactsGroups + 
			'<br /><div class="wm_view_message_info">' + Lang.InfoNewContactsGroups + '</div>');
		}
		
		this._page = this.Contacts.Page;
		var beginHandler = "SetHistoryHandler( { ScreenId: SCREEN_CONTACTS, Entity: PART_CONTACTS, Page: ";
		var endHandler = ", SortField: " + this._sortField + ", SortOrder: " + this._sortOrder + ", SearchIn: " + this._searchGroup + ", LookFor: '" + this._lookFor.replace(/'/g, '\\\'') + "'} );";
		this._pageSwitcher.Show(this._page, this._contactsPerPage, this.Contacts.Count, beginHandler, endHandler);
		this._pageSwitcher.Replace();

		this.SetContactsCount(this.Contacts.ContactsCount);
		this.ResizeBody();
	},
	
	FillSelectedContacts: function (contactsArray, currId, currIsGroup)
	{
		var contCount = contactsArray.length;
		if (contCount == 0) {
			if (this._selectedContactsObj.shown) {
				this.ShowEmpty();
			}
			return;
		}
		if (contCount == 1 && currIsGroup == this._contactsController.CurrIsGroup &&
		 currId == this._contactsController.CurrId) {
			return;
		}
        else if (contCount > 1) {
			this._contactsController.CurrId = null;
		}	
		this.ShowSelectedContacts();
		this._selectedContactsObj.Fill(contactsArray);
	},
	
	BuildAdvancedSearchForm: function()
	{
		var obj = this;
		this._bigSearchForm = CreateChild(document.body, 'div', [['id', 'contacts_search_form']]);
		this._bigSearchForm.className = 'wm_hide';
		var frm = CreateChild(this._bigSearchForm, 'form');
		frm.onsubmit = function () { return false; };
		var tbl = CreateChild(frm, 'table');
		var tr = tbl.insertRow(0);
		var td = tr.insertCell(0);
		td.className = 'wm_search_title';
		td.innerHTML = Lang.LookFor;
		WebMail.LangChanger.Register('innerHTML', td, 'LookFor', '');
		td = tr.insertCell(1);
		td.className = 'wm_search_value';
		var lookForBigInp = CreateChild(td, 'input', [['type', 'text'], ['maxlength', '255']]);
		lookForBigInp.className = 'wm_search_input';
		this._toolBar.CreateSearchButton(td, function () {
			obj.RequestSearchResults();
		});
		lookForBigInp.onkeypress = function (ev) {
			if (isEnter(ev)) {
				obj.RequestSearchResults();
			}
		};
		tr = tbl.insertRow(1);
		td = tr.insertCell(0);
		td.className = 'wm_search_title';
		td.innerHTML = Lang.SearchIn;
		WebMail.LangChanger.Register('innerHTML', td, 'SearchIn', '');
		td = tr.insertCell(1);
		td.className = 'wm_search_value';
		this._searchIn = CreateChild(td, 'select');
		return lookForBigInp;
	},
	
	_buildToolBar: function(PopupMenus)
	{
		var obj = this;
		var toolBar = this._toolBar;

		toolBar.AddItem(TOOLBAR_BACK_TO_LIST, function () {
			SetHistoryHandler(
				{
					ScreenId: WebMail.ListScreenId,
					FolderId: null
				}
			);
		}, true);
		toolBar.AddItem(TOOLBAR_NEW_MESSAGE, function (ev) { obj.NewMessageClick(ev); }, true);
		toolBar.AddItem(TOOLBAR_NEW_CONTACT, function () {
			SetHistoryHandler(
				{
					ScreenId: SCREEN_CONTACTS,
					Entity: PART_NEW_CONTACT
				}
			);
		}, true);
		toolBar.AddItem(TOOLBAR_NEW_GROUP, function () {
			SetHistoryHandler(
				{
					ScreenId: SCREEN_CONTACTS,
					Entity: PART_NEW_GROUP,
					Contacts: null
				}
			);
		}, true);
		this._contactsToMenu = CreateChild(document.body, 'div');
		this._contactsToMenu.className = 'wm_hide';
		toolBar.AddMoveItem(TOOLBAR_ADD_CONTACTS_TO, PopupMenus, this._contactsToMenu, true);
		toolBar.AddItem(TOOLBAR_DELETE, function () { obj.DeleteSelected(); }, true);
		toolBar.AddItem(TOOLBAR_IMPORT_CONTACTS, function () {
			SetHistoryHandler(
				{
					ScreenId: SCREEN_CONTACTS,
					Entity: PART_IMPORT_CONTACT
				}
			);
		}, true);

		var lookForBigInp = this.BuildAdvancedSearchForm();
		var searchParts = toolBar.AddSearchItems();
		this.SearchFormObj = new CSearchForm(this._bigSearchForm, searchParts.SmallForm,
			searchParts.DownButton.Cont, searchParts.UpButton.Cont, 'contacts_search_form',
			lookForBigInp, searchParts.LookFor);
		if (null != this._searchIn) {
			this.SearchFormObj.SetSearchIn(this._searchIn);
		}
		searchParts.LookFor.onkeypress = function (ev) {
			if (isEnter(ev)) {
				obj.RequestSearchResults();
			}
		};
		searchParts.ActionImg.onclick = function () {
			obj.RequestSearchResults();
		};

		toolBar.AddClearDiv();
		toolBar.Hide();
	},

	SetContactsCount: function (count)
	{
		this._contactsCount.innerHTML = count + '&nbsp;' + Lang.ContactsCount;
	},

	ClearContactsLines: function(msg)
	{
		this._selection = null;
		CleanNode(_inboxLines);
		this._isInboxLinesAdded = false;
		var nobr = CreateChild(_inboxLines, 'nobr');
		nobr.innerHTML = msg;
		this._pageSwitcher.Hide();
	},
	
	Build: function(container, accountsBar, popupMenus)
	{
		this._logo = document.getElementById('logo');
		this._accountsBar = accountsBar;

		this._toolBar = new CToolBar(container);
		this._buildToolBar(popupMenus);
		
		var mainDiv = CreateChild(container, 'div');
		mainDiv.className = 'wm_hide';
		this._mainDiv = mainDiv;
		var leftDiv = CreateChild(mainDiv, 'div');
		leftDiv.className = 'wm_contacts_list';
		this._leftDiv = leftDiv;
		
		//contacts list
		this._contactsController = new CContactsTableController(this);
		var contactsTable = new CVariableTable(SortContactsHandler, this._selection, null, this._contactsController);
		contactsTable.AddColumn(CH_CHECK, ContactsHeaders[CH_CHECK]);
		contactsTable.AddColumn(CH_GROUP, ContactsHeaders[CH_GROUP]);
		contactsTable.AddColumn(CH_NAME, ContactsHeaders[CH_NAME]);
		contactsTable.AddColumn(CH_EMAIL, ContactsHeaders[CH_EMAIL]);
		contactsTable.Build(leftDiv);
		this._contactsTable = contactsTable;
		
		this._pageSwitcher = new CPageSwitcher(contactsTable.GetLines(), false);
		
		//contact's card on the left part of screen
		var rightDiv = CreateChild(mainDiv, 'div');
		rightDiv.className = 'wm_contacts_view_edit';
		this._rightDiv = rightDiv;

		this._contactViewerDiv = CreateChild(rightDiv, 'div');
		this._contactViewerDiv.className = 'wm_hide';
		
		var tblCard = CreateChild(this._contactViewerDiv, 'div');
		this._cardTable = tblCard;
		
		CreateChild(this._cardTable, 'div', [['class', 'wm_contacts_card_line1']]);
		CreateChild(this._cardTable, 'div', [['class', 'wm_contacts_card_line2']]);
		CreateChild(this._cardTable, 'div', [['class', 'wm_contacts_card_line3']]);
		CreateChild(this._cardTable, 'div', [['class', 'wm_contacts_card_line4']]);
		CreateChild(this._cardTable, 'div', [['class', 'wm_contacts_card_line5']]);
		
		var divContent = CreateChild(this._cardTable, 'div', [['class', 'wm_contacts_card_content']]);
		var tableContent = CreateChild(divContent, 'table', [['style', 'width: 100%;']]);
		var trTitle = tableContent.insertRow(0);
		var tdTitle = trTitle.insertCell(0);
		tdTitle.style.padding = '0 20px 20px 20px';
		trTitle.style.fontSize = 'large';
		this._cardTitle = tdTitle;
		
		var trContent = tableContent.insertRow(1);
		var tdContent = trContent.insertCell(0);
		//----------//
		
		this._selectedContactsObj.Build(tdContent);
		this._newContactObj.Build(tdContent);
		this._viewContactObj.Build(tdContent);
		this._newGroupObj.Build(tdContent);
		this._importContactsObj.Build(tdContent);
		
		CreateChild(this._cardTable, 'div', [['class', 'wm_contacts_card_line5']]);
		CreateChild(this._cardTable, 'div', [['class', 'wm_contacts_card_line4']]);
		CreateChild(this._cardTable, 'div', [['class', 'wm_contacts_card_line3']]);
		CreateChild(this._cardTable, 'div', [['class', 'wm_contacts_card_line2']]);
		CreateChild(this._cardTable, 'div', [['class', 'wm_contacts_card_line1']]);
		
		var lowDiv = CreateChild(container, 'div');
		lowDiv.className = 'wm_hide';
		this._lowToolBar = lowDiv;
		this._contactsCount = CreateChild(lowDiv, 'span', [['class', 'wm_lowtoolbar_messages']]);
		this.SetContactsCount(0);

		this.isBuilded = true;
	}//Build
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}