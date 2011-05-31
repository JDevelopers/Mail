/*
 * Classes:
 *  CFiltersScreenPart(parentScreen)
 *  CFilterEditor(filter, parentDiv, filterFields, filterConditions, filterActions, parentObj)
 *  CPopupChooser(values)
 */

function CFiltersScreenPart(parentScreen)
{
	this._parentScreen = parentScreen;
	
	this.shown = false;
	this.isSaveFilters = false;

	this._foldersIdAcct = -1;
	
	this._mainTbl = null;
	this._filtersParentDiv = null;
	this._noFiltersDiv = null;

	this._filterFields = [	{Id: 0, Value: Lang.From, Shift: ''},
							{Id: 1, Value: Lang.To, Shift: ''},
							{Id: 2, Value: Lang.Subject, Shift: ''}];
	this._filterConditions = [	{Id: 0, Value: Lang.FiltersCondContainSubstr, Shift: ''},
								{Id: 1, Value: Lang.FiltersCondEqualTo, Shift: ''},
								{Id: 2, Value: Lang.FiltersCondNotContainSubstr, Shift: ''}];
	this._filterActions = [	{Id: 1, Value: Lang.FiltersActionDelete, Shift: ''},
							{Id: 3, Value: Lang.FiltersActionMove, Shift: ''}];
	this._filterFolders = [];

	this.FieldChooser = null;
	this.ConditionChooser = null;
	this.ActionChooser = null;
	this.FolderChooser = null;
	
	this._fltEditors = [];
	this.InboxId = -1;
	this.InboxName = '';
}

CFiltersScreenPart.prototype = {
	Show: function()
	{
		if (!this.shown) {
			this.shown = true;
			this._mainTbl.className = (window.UseDb || window.UseLdapSettings)
				? 'wm_email_settings_edit_zone' : 'wm_hide';
		}
		if (this._foldersIdAcct != WebMail.Accounts.EditableId) {
			this._foldersIdAcct = WebMail.Accounts.EditableId;
			GetHandler(TYPE_FOLDER_LIST, { IdAcct: WebMail.Accounts.EditableId, Sync: GET_FOLDERS_NOT_CHANGE_ACCT }, [], '');
		}
		this.Fill();
	},
	
	Hide: function()
	{
		this.shown = false;
		this._mainTbl.className = 'wm_hide';
		this.HideChoosers();
	},
	
	HideChoosers: function ()
	{
		this.FieldChooser.Hide();
		this.ConditionChooser.Hide();
		this.ActionChooser.Hide();
		this.FolderChooser.Hide();
	},
	
	SetFilters: function (filters)
	{
		if (this.isSaveFilters) {
			WebMail.ShowReport(Lang.ReportFiltersUpdatedSuccessfuly);
			this.isSaveFilters = false;
		}
		this.FillFilters();
	},
	
	Fill: function ()
	{
		this.FillFilters();
		if (this._parentScreen) {
			this._parentScreen.ResizeBody();
		}
	},
	
	FillFolders: function (folders)
	{
		this._filterFolders = [];
		var levelShift = ['', '&nbsp;&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;', 
			'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'];
		for (var i = 0; i < folders.length; i++) {
			var folder = folders[i];
			var folderName = folder.Name;
			var langField = FolderDescriptions[folder.Type].langField;
			if (langField) {
				folderName = Lang[langField];
			}
			var level = (folder.Level > levelShift.length) ? levelShift.length : folder.Level;
			this._filterFolders[i] = {Id: folder.Id, Value: folderName, Shift: levelShift[level]};
			if (folder.Type == FOLDER_TYPE_INBOX) {
				this.InboxId = folder.Id;
				this.InboxName = folderName;
			}
		}
	},
	
	FillFilters: function ()
	{
		if (!this.shown) return;
		
        var filters = WebMail.Accounts.GetEditableFilters();
		if (filters == null) {
			this._noFiltersDiv.innerHTML = Lang.FiltersLoading;
			this._noFiltersDiv.className = 'wm_filters_line';
			GetHandler(TYPE_FILTERS, { IdAcct: WebMail.Accounts.EditableId }, [], '');
			return;
		}
		CleanNode(this._filtersParentDiv);
		this._fltEditors = [];
		for (var i in filters) {
			var filter = filters[i];
			for(var j = 0; j < this._filterFolders.length; j++) {
				if (this._filterFolders[j].Id == filter.IdFolder) {
					filter.FolderName = this._filterFolders[j].Value;
				}
			}
			this.AddFilter(filter);
		}
		this.CheckNoFilters();
	},
	
	CheckNoFilters: function ()
	{
		var hasFilters = false;
		for (var i in this._fltEditors) {
			if (this._fltEditors[i].Status != FILTER_STATUS_REMOVED) {
				hasFilters = true;
				break;
			}
		}
		if (hasFilters) {
			this._noFiltersDiv.className = 'wm_hide';
		}
		else {
			this._noFiltersDiv.innerHTML = Lang.FiltersNo;
			this._noFiltersDiv.className = 'wm_filters_line';
		}
	},
	
	AddFilter: function (filter, resize)
	{
		var obj = this;
		function CreateActionChooserShowFunc(obj, fltEditor)
		{
			return function() {
				obj.ActionChooser.Show(this, fltEditor);
			};
		}
		var fltEditor = new CFilterEditor(filter, this._filtersParentDiv, this._filterFields, this._filterConditions, this._filterActions, this);
		fltEditor.FieldLink.onclick = function () { obj.FieldChooser.Show(this); };
		fltEditor.ConditionLink.onclick = function () { obj.ConditionChooser.Show(this); };
		fltEditor.ActionLink.onclick = CreateActionChooserShowFunc(obj, fltEditor);
		fltEditor.FolderLink.onclick = function () { obj.FolderChooser.Show(this, undefined, obj._filterFolders); };
		this._fltEditors.push(fltEditor);
		if (resize && this._parentScreen) {
			this._parentScreen.ResizeBody();
		}
		this.CheckNoFilters();
	},
	
	ClickBody: function (ev)
	{
		ev = ev ? ev : window.event;
		if (Browser.Mozilla) {
			elem = ev.target;
		} else {
			elem = ev.srcElement;
		}
		while (elem && elem.tagName != 'DIV' && elem.parentNode) {
			elem = elem.parentNode;
		}
		if (elem && elem.className != 'wm_choices_popup' && elem.parentNode) {
			elem = elem.parentNode;
		}
		if (elem && elem.className != 'wm_choices_popup') {
			this.HideChoosers();
		}
	},
	
	SaveChanges: function ()
	{
		if (WebMail._isDemo) {
			WebMail.ShowReport(DemoWarning);
			return;
		}

		var xml = '';
		for (var i = 0; i < this._fltEditors.length; i++) {
			var filter = this._fltEditors[i].GetNewFilter();
			if (filter.Status != FILTER_STATUS_REMOVED && Validator.IsEmpty(filter.Value)) {
				alert(Lang.WarningEmptyFilter);
				this._fltEditors[i].StringInput.focus();
				return;
			}
			xml += filter.GetInXML();
		}
		xml = '<filters id_acct="' + WebMail.Accounts.EditableId + '">' + xml + '</filters>';
		RequestHandler('update', 'filters', xml);
		this.isSaveFilters = true;
	},

	Build: function(container)
	{
		var opt;
		var obj = this;
		this._mainTbl = CreateChild(container, 'table');
		this._mainTbl.className = 'wm_hide';
		var mainTr = this._mainTbl.insertRow(0);
		var mainTd = mainTr.insertCell(0);
		mainTd.className = 'wm_email_settings_edit_zone_cell';

		var filtersContDiv = CreateChild(mainTd, 'div', [['class', 'wm_filters_cont']]);
		this._filtersParentDiv = CreateChild(filtersContDiv, 'div', []);
		this._noFiltersDiv = CreateChild(filtersContDiv, 'div', [['class', 'wm_filters_line']]);
		this._noFiltersDiv.innerHTML = Lang.FiltersLoading;

		tbl = CreateChild(mainTd, 'table');
		tbl.className = 'wm_settings_buttons';
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		td.style.textAlign = 'left';
		var moreFiltersA = CreateChild(td, 'a', [['href', 'javascript:void(0)'], ['class', 'wm_filters_add_link']]);
		moreFiltersA.innerHTML = Lang.FiltersAdd;
		WebMail.LangChanger.Register('innerHTML', moreFiltersA, 'FiltersAdd', '');
		moreFiltersA.onclick = function () {
			obj.AddFilter(new CFilterProperties(obj.InboxId, obj.InboxName), true);
		};

		td = tr.insertCell(1);
		inp = CreateChild(td, 'input', [['class', 'wm_button'], ['type', 'button'], ['value', Lang.Save]]);
		WebMail.LangChanger.Register('value', inp, 'Save', '');
		inp.onclick = function () {
			obj.SaveChanges();
		};

		this.FieldChooser = new CPopupChooser(this._filterFields);
		this.ConditionChooser = new CPopupChooser(this._filterConditions);
		this.ActionChooser = new CPopupChooser(this._filterActions);
		this.FolderChooser = new CPopupChooser(this._filterFolders);
	}//Build
};

function CFilterEditor(filter, parentDiv, filterFields, filterConditions, filterActions, parentObj)
{
	this._filter = filter;
	this._parentDiv = parentDiv;
	this.Status = filter.Status;
	
	this.AppliedCheck = null;
	this.FieldLink = null;
	this.ConditionLink = null;
	this.ActionLink = null;
	this.RemoveLink = null;
	this.FolderPart = null;
	this.FolderLink = null;
	this.StringInput = null;
	
	this._filterDiv = null;
	
	this._build(filterFields, filterConditions, filterActions, parentObj);
}

CFilterEditor.prototype = {
	ActionChangedHandler: function ()
	{
		if (this.ActionLink.value == 3) {
			this.FolderPart.className = '';
		}
		else {
			this.FolderPart.className = 'wm_hide';
		}
	},
	
	GetNewFilter: function ()
	{
		var applied = this.AppliedCheck.checked;
		var field = this.FieldLink.value;
		var condition = this.ConditionLink.value;
		var action = this.ActionLink.value;
		var folderId = this.FolderLink.value;
		var folderName = this.FolderLink.innerHTML;
		var string = this.StringInput.value;
		
		var filter = this._filter;
		if (this.Status == FILTER_STATUS_UNCHANGED && (applied != filter.Applied || field != filter.Field 
			|| condition != filter.Condition || action != filter.Action
			|| folderId != filter.IdFolder || string != filter.Value)) {
			this.Status = FILTER_STATUS_UPDATED;
		}
		
		filter.Field = field;
		filter.Condition = condition;
		filter.Action = action;
		filter.IdFolder = folderId;
		filter.FolderName = folderName;
		filter.Value = string;
		filter.Status = this.Status;
		filter.Applied = applied;
		return filter;
	},
	
	_getValue: function (values, id)
	{
		for(var i = 0; i < values.length; i++) {
			if (values[i].Id == id) return values[i].Value;
		}
		return '';
	},

	_build: function (filterFields, filterConditions, filterActions, parentObj)
	{
		var filter = this._filter;
		var filterId = (filter.Id == -1) ? Math.random() : filter.Id;
		
		var checked = filter.Applied ? 'checked="checked"' : '';
		var filterPhrase = '<input id="applied_check_' + filterId + '" type="checkbox" class="wm_checkbox" ' + checked + '/> ' + Lang.FilterPhrase;

		var fieldLink = '<a id="field_link_' + filterId + '" href="javascript:void(0)" class="wm_choices_menu_link">' + this._getValue(filterFields, filter.Field) + '</a>';
		filterPhrase = filterPhrase.replace(/%field/g, fieldLink);

		var conditionLink = '<a id="condition_link_' + filterId + '" href="javascript:void(0)" class="wm_choices_menu_link">' + this._getValue(filterConditions, filter.Condition) + '</a>';
		filterPhrase = filterPhrase.replace(/%condition/g, conditionLink);

		var inp = '<input id="string_input_' + filterId + '" type="text" value="' + filter.Value + '" class="wm_input" />';
		filterPhrase = filterPhrase.replace(/%string/g, inp);

		var actionLink = '<a id="action_link_' + filterId + '" href="javascript:void(0)" class="wm_choices_menu_link">' + this._getValue(filterActions, filter.Action) + '</a>';
		filterPhrase = filterPhrase.replace(/%action/g, actionLink);

		var folderLink = '<a id="folder_link_' + filterId + '" href="javascript:void(0)" class="wm_choices_menu_link">' + filter.FolderName + '</a>';
		filterPhrase += '<span id="folder_part_' + filterId + '" class="wm_hide"> ' + Lang.FiltersActionToFolder.replace(/%folder/g, folderLink) + '</span>';

		filterPhrase += ' <a id="remove_link_' + filterId + '" href="javascript:void(0)" class="wm_hide">' + Lang.Remove + '</a>';

		this._filterDiv = CreateChild(this._parentDiv, 'div', [['class', 'wm_filters_line']]);
		this._filterDiv.innerHTML = filterPhrase;
		
		this.AppliedCheck = document.getElementById('applied_check_' + filterId);
		
		this.FieldLink = document.getElementById('field_link_' + filterId);
		this.FieldLink.value = filter.Field;
		
		this.ConditionLink = document.getElementById('condition_link_' + filterId);
		this.ConditionLink.value = filter.Condition;
		
		this.StringInput = document.getElementById('string_input_' + filterId);

		this.ActionLink = document.getElementById('action_link_' + filterId);
		this.ActionLink.value = filter.Action;

		this.FolderPart = document.getElementById('folder_part_' + filterId);
		this.ActionChangedHandler();
		
		this.FolderLink = document.getElementById('folder_link_' + filterId);
		this.FolderLink.value = filter.IdFolder;

		this.RemoveLink = document.getElementById('remove_link_' + filterId);
		var obj = this;
		this.RemoveLink.onclick = function () {
			obj.Status = FILTER_STATUS_REMOVED;
			obj._filterDiv.className = 'wm_hide';
			parentObj.CheckNoFilters();
		};
		this._filterDiv.onmouseover = function () {
			obj.RemoveLink.className = '';
		};
		this._filterDiv.onmouseout = function () {
			obj.RemoveLink.className = 'wm_hide';
		};
	}
};

function CPopupChooser(values)
{
	this._values = values;
	this._choices = [];
	this._choosenId = null;
	this._link = null;
	this._popupDiv = CreateChild(document.body, 'div', [['class', 'wm_hide']]);
	this._builded = false;
	this._showStatus = 0;
	this._fltEditor = null;
}

CPopupChooser.prototype = {
	Show: function (link, fltEditor, values)
	{
		this._link = link;
		this._link.className = 'wm_choices_menu_link';
		if (fltEditor != undefined) {
			this._fltEditor = fltEditor;
		}
		if (values != undefined) {
			this._values = values;
			this._builded = false;
		}
		if (!this._builded) {
			this._choosenId = link.value;
			this._build();
		}
		else {
			this._changeActiveChoice(link.value);
		}
		this._popupDiv.className = 'wm_choices_popup';
		var activeChoice = this._choices[this._choosenId];
		var linkBounds = GetBounds(this._link);
		var activeBounds = GetBounds(activeChoice);
		var popupBounds = GetBounds(this._popupDiv);
		this._popupDiv.style.left = linkBounds.Left + 'px';
		this._popupDiv.style.top = popupBounds.Top - activeBounds.Top + linkBounds.Top + 'px';
		this._showStatus = 1;
	},
	
	_changeActiveChoice: function (id)
	{
		this._choices[this._choosenId].className = 'wm_choice';
		this._choosenId = id;
		this._choices[this._choosenId].className = 'wm_active_choice';
	},
	
	Hide: function (id)
	{
		if (!this._builded) return;
		if (id != undefined) {
			for(var i=0; i<this._values.length; i++) {
				if (this._values[i].Id == id) {
					this._link.value = this._values[i].Id;
					this._link.innerHTML = this._values[i].Value;
				}
			}
			if (this._fltEditor != null) {
				this._fltEditor.ActionChangedHandler();
			}
		}
		if (this._showStatus == 2) {
			this._popupDiv.className = 'wm_hide';
			this._showStatus = 1;
		}
		else {
			this._showStatus = 2;
		}
	},
	
	_build: function ()
	{
		CleanNode(this._popupDiv);
		for (var i = 0; i < this._values.length; i++) {
			var value = this._values[i];
			var valueDiv = CreateChild(this._popupDiv, 'div');
			var valueLink = CreateChild(valueDiv, 'a', [['id', value.Id]]);
			if (value.Id == this._choosenId) {
				valueLink.className = 'wm_active_choice';
			}
			else {
				valueLink.className = 'wm_choice';
			}
			valueLink.innerHTML = value.Shift + value.Value;
			valueLink.href = 'javascript:void(0)';
			var obj = this;
			valueLink.onclick = function () { obj.Hide(this.id); };
			this._choices[value.Id] = valueLink;
		}
		this._builded = true;
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}