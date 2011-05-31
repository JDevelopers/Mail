/*
 * Classes:
 *  CEditGroupScreenPart(skinName, parent)
 */

function CEditGroupScreenPart(skinName, parent)
{
	this._skinName = skinName;
	this._parent = parent;
	
	this._mainContainer = null;
	this._groupContactsCont = null;
	this._buttonsTbl = null;
	this._GroupOrganizationTab = null;
	this._contacts = Array();
	this._isEditName = false;

	this._groupNameObj = null;

	this._groupNameSpan = null;
	this._groupNameA = null;
	this._saveButton = null;
	this._createButton = null;
	this._mailButton = null;
	
	this.isCreateGroup = false;
	this._createdGroupName = '';
	this.isSaveGroup = false;
	
	this._tabs = Array();
	this._groupOrganizationObj = null;
	this._emailObj = null;
	this._companyObj = null;
	this._streetObj = null;
	this._cityObj = null;
	this._faxObj = null;
	this._stateObj = null;
	this._phoneObj = null;
	this._zipObj = null;
	this._countryObj = null;
	this._webObj = null;
}

CEditGroupScreenPart.prototype = {
	Show: function ()
	{
	    var obj = this;
		this._mainContainer.className = '';
		this._buttonsTbl.className = 'wm_contacts_view';
		this._groupNameObj.onkeypress = function (ev) { if (isEnter(ev)) obj.SaveChanges(); };
		this._groupNameObj.onblur = function () { };
	},
	
	
	Hide: function ()
	{
		this._mainContainer.className = 'wm_hide';
		this._groupContactsCont.className = 'wm_hide';
		this._buttonsTbl.className = 'wm_hide';
		this._GroupOrganizationTab.className = 'wm_hide';
		this._tabs[0].Hide();
	},
	
	CheckGroupUpdate: function ()
	{
		if (this.isCreateGroup) {
			WebMail.ShowReport(Lang.ReportGroupSuccessfulyAdded1 + ' "' + this._createdGroupName + '" ' + Lang.ReportGroupSuccessfulyAdded2);
			this.isCreateGroup = false;
		}
		else if (this.isSaveGroup) {
			WebMail.ShowReport(Lang.ReportGroupUpdatedSuccessfuly);
			this.isSaveGroup = false;
		}
	},
	
	EditName: function ()
	{
		var obj = this;
		this._groupNameObj.value = HtmlDecode(this._groupNameSpan.innerHTML);
		this._groupNameObj.onkeypress = function (ev) { if (isEnter(ev)) obj.SaveName(); };
		this._groupNameObj.onblur = function () { obj.SaveName(); };
		this._groupNameObj.className = 'wm_input wm_group_name_input';
		this._groupNameObj.focus();
		this._groupNameSpan.className = 'wm_hide';
		this._groupNameA.className = 'wm_hide';
		this._isEditName = true;
	},
	
	SaveName: function ()
	{
		this._groupNameSpan.innerHTML = HtmlEncode(this._groupNameObj.value);
		this.CloseNameEditor();
	},
	
	CloseNameEditor: function ()
	{
		this._groupNameObj.onkeypress = function () { };
		this._groupNameObj.onblur = function () { };
		this._groupNameObj.className = 'wm_hide';
		this._groupNameSpan.className = '';
		this._groupNameA.className = '';
		this._isEditName = false;
	},
	
	MailGroup: function ()
	{
		var iCount = this._contacts.length;
		var selected = Array();
		for (var i=0; i<iCount; i++) {
			var cont = this._contacts[i];
			if (cont.Email.length > 0) {
				selected.push(cont.Email);
			}
		}
		if (selected.length == 0) {
			return;
		}
		MailAllHandlerWithDropDown(selected.join(', '));
	},
	
	MailSelected: function ()
	{
		var iCount = this._contacts.length;
		var selected = Array();
		for (var i=0; i<iCount; i++) {
			var cont = this._contacts[i];
			if (cont.Inp.checked && cont.Email.length > 0) {
				selected.push(cont.Email);
			}
		}
		if (selected.length == 0) {
			alert(Lang.AlertNoContactsSelected);
			return;
		}
		MailAllHandlerWithDropDown(selected.join(', '));
	},
	
	Fill: function (group)
	{
		var obj = this;
		this.Show();
		this.Group = group;
		if (group.Id == -1) {
			this._groupNameObj.value = '';
			this._groupNameObj.className = 'wm_input wm_group_name_input';
			this._groupNameSpan.className = 'wm_hide';
			this._groupNameA.className = 'wm_hide';
			this._saveButton.className = 'wm_hide';
			this._createButton.className = 'wm_button';
			this._mailButton.className = 'wm_hide';
		}
		else {
			this._groupNameSpan.innerHTML = group.Name;
			this._groupNameObj.className = 'wm_hide';
			this._groupNameSpan.className = '';
			this._groupNameA.className = '';
			this._saveButton.className = 'wm_button';
			this._createButton.className = 'wm_hide';
			this._mailButton.className = 'wm_button_link wm_control';
		}

		this._groupOrganizationObj.checked = group.isOrganization;
		if (group.isOrganization) {
			this._tabs[0].Show();
		}
		else {
			this._tabs[0].Hide();
		}
		this._emailObj.value = HtmlDecode(group.Email);
		this._companyObj.value = HtmlDecode(group.Company);
		this._streetObj.value = HtmlDecode(group.Street);
		this._cityObj.value = HtmlDecode(group.City);
		this._faxObj.value = HtmlDecode(group.Fax);
		this._stateObj.value = HtmlDecode(group.State);
		this._phoneObj.value = HtmlDecode(group.Phone);
		this._zipObj.value = HtmlDecode(group.Zip);
		this._countryObj.value = HtmlDecode(group.Country);
		this._webObj.value = HtmlDecode(group.Web);

		var iCount = group.Contacts.length;
		this._contacts = Array();
		if (iCount > 0) {
			this._groupContactsCont.className = '';
			CleanNode(this._groupContactsCont);
			var tbl = CreateChild(this._groupContactsCont, 'table');
			tbl.className = 'wm_contacts_in_group_lines';
			var rowIndex = 0;
			
			var tr = tbl.insertRow(rowIndex++);
			tr.className = 'wm_contacts_headers';
			var td = tr.insertCell(0);
			td.style.width = '12px';
			var inp = CreateChild(td, 'input', [['type', 'checkbox'], ['class', 'wm_checkbox']]);
			inp.onclick = function () { obj.CheckAllLines(this.checked); };
			td = tr.insertCell(1);
			td.style.width = '100px';
			td.innerHTML = Lang.Name;
			td = tr.insertCell(2);
			td.style.width = '164px';
			td.innerHTML = Lang.Email;
			for (var i=0; i<iCount; i++) {
				tr = tbl.insertRow(rowIndex++);
				tr.className = 'wm_inbox_read_item';
				td = tr.insertCell(0);
				inp = CreateChild(td, 'input', [['type', 'checkbox'], ['class', 'wm_checkbox'], ['id', group.Contacts[i].Id]]);
				inp.onclick = function () { obj.CheckLine(this.id, this.checked); };
				td = tr.insertCell(1);
				td.innerHTML = group.Contacts[i].Name;
				td = tr.insertCell(2);
				td.innerHTML = group.Contacts[i].Email;
				this._contacts[i] = {Id: group.Contacts[i].Id, Name: group.Contacts[i].Name, 
					Email: group.Contacts[i].Email, Tr: tr, Inp: inp, Deleted: false};
			}

			var contactsTableWidth = tbl.offsetWidth;
			if (contactsTableWidth < 300) contactsTableWidth = 300;
			tbl = CreateChild(this._groupContactsCont, 'table');
			tbl.className = 'wm_contacts_in_group_actions';
			tbl.style.width = contactsTableWidth + 'px';
			rowIndex = 0;
			tr = tbl.insertRow(rowIndex++);
			td = tr.insertCell(0);
			td.colSpan = 2;
			var a = CreateChild(td, 'a', [['href', '#']]);
			a.onclick = function () { obj.MailSelected(); return false; };
			a.innerHTML = Lang.MailSelected;
			td = tr.insertCell(1);
			/*rtl*/
			td.style.textAlign = window.RIGHT;
			a = CreateChild(td, 'a', [['href', '#']]);
			a.onclick = function () { obj.DeleteSelected(); return false; };
			a.innerHTML = Lang.RemoveFromGroup;
		}
		else {
			this._groupContactsCont.className = 'wm_hide';
		}
	},
	
	CheckLine: function (id, checked)
	{
		var iCount = this._contacts.length;
		for (var i=0; i<iCount; i++) {
			var cont = this._contacts[i];
			if (cont.Id == id && !cont.Deleted)
				if (checked) {
					cont.Tr.className = 'wm_inbox_read_item_select';
				}
				else {
					cont.Tr.className = 'wm_inbox_read_item';
				}
		}
	},
	
	CheckAllLines: function (checked)
	{
		var iCount = this._contacts.length;
		for (var i=0; i<iCount; i++) {
			var cont = this._contacts[i];
			cont.Inp.checked = checked;
			if (checked) {
				cont.Tr.className = 'wm_inbox_read_item_select';
			}
			else {
				cont.Tr.className = 'wm_inbox_read_item';
			}
		}
	},
	
	DeleteSelected: function ()
	{
		var iCount = this._contacts.length;
		var delCount = 0;
		var deleted = false;
		for (var i=0; i<iCount; i++) {
			var cont = this._contacts[i];
			if (cont.Inp.checked) {
				if (!cont.Deleted) deleted = true;
				cont.Tr.className = 'wm_hide';
				cont.Deleted = true;
				delCount++;
			}
		}
		if (!deleted) {
			alert(Lang.AlertNoContactsSelected);
		}
		if (delCount == iCount)
			this._groupContactsCont.className = 'wm_hide';
	},

	SetInputKeyPress: function (inp)
	{
	    var obj = this;
		inp.onkeypress = function (ev) { if (isEnter(ev)) obj.SaveChanges(); };
	},
	
	SaveChanges: function ()
	{
		/* validation */
		var id = this.Group.Id;
		var name = (id == -1 || this._isEditName)
			? Trim(this._groupNameObj.value) : Trim(HtmlDecode(this._groupNameSpan.innerHTML));
		
		if (Validator.IsEmpty(name)) {
			alert(Lang.WarningGroupNotComplete);
			return;
		}
		
		/* saving */
		var group = new CGroup();
		group.Id = id;
		group.isOrganization = this._groupOrganizationObj.checked;
		group.Name = name;
		group.Email = this._emailObj.value;
		group.Company = this._companyObj.value;
		group.Street = this._streetObj.value;
		group.City = this._cityObj.value;
		group.Fax = this._faxObj.value;
		group.State = this._stateObj.value;
		group.Phone = this._phoneObj.value;
		group.Zip = this._zipObj.value;
		group.Country = this._countryObj.value;
		group.Web = this._webObj.value;
		var i;
		var iCount = this._contacts.length;
		for (i=0; i<iCount; i++) {
			var cont = this._contacts[i];
			if (cont.Deleted == false) {
				group.Contacts.push({ Id: cont.Id, Name: cont.Name, Email: cont.Email });
			}
		}

		var stringDataKey;
		var xml = group.GetInXml(this._parent.GetXmlParams());
		if (id == -1) {
			WebMail.DataSource.Cache.ClearAllContactsGroupsList();
			for (i in group.Contacts) {
				stringDataKey = WebMail.DataSource.GetStringDataKey(TYPE_CONTACT, {IdAddr: group.Contacts[i].Id});
				WebMail.DataSource.Cache.RemoveData(TYPE_CONTACT, stringDataKey);
			}
			RequestHandler('new', 'group', xml);
			this.isCreateGroup = true;
			this._createdGroupName = name;
		} else {
			stringDataKey = WebMail.DataSource.GetStringDataKey(group.Type, {IdGroup: group.Id});
			WebMail.DataSource.Cache.ReplaceData(group.Type, stringDataKey, group);
			WebMail.DataSource.Cache.RenameRemoveGroupInContacts(group.Id, group.Name, group.Contacts);
			if (this.Group.Name != group.Name) {
				WebMail.DataSource.Cache.ClearAllContactsGroupsList();
			}
			RequestHandler('update', 'group', xml);
			this.isSaveGroup = true;
		}
		this._parent._groupsOutOfDate = true;
	},
	
	Build: function (container)
	{
		var obj = this;
		
		var mailTbl = CreateChild(container, 'table');
		mailTbl.style.width = '100%';
		mailTbl.className = 'wm_hide';
		this._mainContainer = mailTbl;
		var mainTr = mailTbl.insertRow(0);
		var mainTd = mainTr.insertCell(0);
		mainTd.style.textAlign = 'left';

		var tbl = CreateChild(mainTd, 'table');
		tbl.className = 'wm_contacts_view';
		tbl.style.marginTop = '0';
		var tr = tbl.insertRow(0);
		var td = tr.insertCell(0);
		td.innerHTML = Lang.GroupName + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'GroupName', ':');
		td = tr.insertCell(1);
		td.className = 'wm_contacts_name';
		var inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input wm_group_name_input'], ['maxlength', '85']]);
		this._groupNameObj = inp;
		var a, span;
		/*rtl*/
		if (window.RTL) {
		    a = CreateChild(td, 'a', [['href', '#']]);
		    span = CreateChild(td, 'span');
		    span.innerHTML = '&nbsp;';
		}
		span = CreateChild(td, 'span');
		span.className = 'wm_hide';
		this._groupNameSpan = span;
		if (!window.RTL) {
	    	span = CreateChild(td, 'span');
    		span.innerHTML = '&nbsp;';
		    a = CreateChild(td, 'a', [['href', '#']]);
		}
		a.onclick = function () { obj.EditName(); return false; };
		a.innerHTML = Lang.Rename;
		WebMail.LangChanger.Register('innerHTML', a, 'Rename', '');
		a.className = 'wm_hide';
		this._groupNameA = a;
		
		tr = tbl.insertRow(1);
		td = tr.insertCell(0);
		td.colSpan = 2;
		inp = CreateChild(td, 'input', [['type', 'checkbox'], ['class', 'wm_checkbox'], ['id', 'group-organization']]);
		var lbl = CreateChild(td, 'label', [['for', 'group-organization']]);
		lbl.innerHTML = Lang.TreatAsOrganization;
		WebMail.LangChanger.Register('innerHTML', lbl, 'TreatAsOrganization', '');
		inp.onclick = function () {
			if (this.checked) {
				obj._tabs[0].Show();
			}
			else {
				obj._tabs[0].Hide();
			}
			obj._parent.ResizeBody();
		};
		this._groupOrganizationObj = inp;
		if (UseCustomContacts || UseCustomContacts1)
		{
			tr.className = 'wm_hide';
		}
		
		mainTd = mainTr.insertCell(1);
        mainTd.style.textAlign = window.RIGHT;
		mainTd.style.verticalAlign = 'top';
		
		var div;
		/*rtl*/

		div = CreateChild(mainTd, 'span');
		//div.style.margin = '0 20px 10px';
		div.className = 'wm_button_link wm_control';
		div.onclick = function () { obj.MailGroup(); };

		var divCh = CreateChild(div, 'span');
		divCh.innerHTML = Lang.MailGroup;
		WebMail.LangChanger.Register('innerHTML', divCh, 'MailGroup', '');
/*
	    div = CreateChild(mainTd, 'div', [['style', 'float: ' + window.RIGHT + ';']]);
		div.style.margin = '0 20px 10px';
		div.className = 'wm_button_link wm_control';
		div.onclick = function () { obj.MailGroup(); };

		var divCh = CreateChild(div, 'div');
		divCh.innerHTML = Lang.MailGroup;
		WebMail.LangChanger.Register('innerHTML', divCh, 'MailGroup', '');
*/
		this._mailButton = div;
		div = CreateChild(container, 'div', [['style', 'width: 0; height: 0; padding: 0; overflow: hidden; clear: both;']]);

		this.BuildGroupOrganization(container);
		
		/*------Group contacts------*/
		
		div = CreateChild(container, 'div');
		this._groupContactsCont = div;
		div.className = 'wm_hide';

		/*------New contacts------*/
		tbl = CreateChild(container, 'table');
		this._buttonsTbl = tbl;
		tbl.className = 'wm_hide';
		tbl.style.width = '90%';
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		td.className = 'wm_contacts_save_button';
		inp = CreateChild(td, 'input', [['type', 'button'], ['class', 'wm_button'], ['value', Lang.Save]]);
		WebMail.LangChanger.Register('value', inp, 'Save', '');
		inp.onclick = function () { obj.SaveChanges(); };
		this._saveButton = inp;
		inp = CreateChild(td, 'input', [['type', 'button'], ['class', 'wm_button'], ['value', Lang.CreateGroup]]);
		WebMail.LangChanger.Register('value', inp, 'CreateGroup', '');
		inp.onclick = function () { obj.SaveChanges(); };
		this._createButton = inp;
	},
	
	TextAreaLimit: function (ev)
	{
		return TextAreaLimit(ev, this, 85);
	},
	
	BuildGroupOrganization: function (container)
	{
		var obj = this;
		
		var tabTbl = CreateChild(container, 'table');
		tabTbl.style.marginTop = '20px';
		this._GroupOrganizationTab = tabTbl;
		tabTbl.onclick = function () {
			obj._tabs[0].ChangeTabMode(obj._skinName);
			obj._parent.ResizeBody();
		};
		tabTbl.className = 'wm_contacts_tab';
		var tr = tabTbl.insertRow(0);
		var td = tr.insertCell(0);
		var span = CreateChild(td, 'span');
		span.className = 'wm_contacts_tab_name';
		span.innerHTML = Lang.Organization;
		WebMail.LangChanger.Register('innerHTML', span, 'Organization', '');
		var imgDiv = CreateChild(td, 'div');
		imgDiv.className = 'wm_contacts_tab_open_mode';

		var tbl = CreateChild(container, 'table');
		tbl.className = 'wm_contacts_view';
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		td.className = 'wm_contacts_view_title';
		td.style.width = '20%';
		td.innerHTML = Lang.Email + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Email', ':');
		td = tr.insertCell(1);
		td.style.width = '80%';
		td.colSpan = 4;
		var inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['size', '45'], ['maxlength', '255']]);
		this.SetInputKeyPress(inp);
		this._emailObj = inp;

		tr = tbl.insertRow(1);
		td = tr.insertCell(0);
		td.className = 'wm_contacts_view_title';
		td.innerHTML = Lang.Company + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Company', ':');
		td = tr.insertCell(1);
		td.colSpan = 4;
		inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['size', '18'], ['maxlength', '65']]);
		this.SetInputKeyPress(inp);
		this._companyObj = inp;

		tr = tbl.insertRow(2);
		td = tr.insertCell(0);
		td.className = 'wm_contacts_view_title';
		td.innerHTML = Lang.StreetAddress + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'StreetAddress', ':');
		td = tr.insertCell(1);
		td.colSpan = 4;
		var txt = CreateChild(td, 'textarea', [['class', 'wm_input'], ['cols', '35'], ['rows', '2']]);
		txt.onkeydown = this.TextAreaLimit;
		this._streetObj = txt;

		tr = tbl.insertRow(3);
		td = tr.insertCell(0);
		td.style.width = '20%';
		td.className = 'wm_contacts_view_title';
		td.innerHTML = Lang.City + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'City', ':');
		td = tr.insertCell(1);
		td.style.width = '30%';
		inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['size', '18'], ['maxlength', '65']]);
		this.SetInputKeyPress(inp);
		this._cityObj = inp;
		td = tr.insertCell(2);
		td.style.width = '5%';
		td = tr.insertCell(3);
		td.style.width = '15%';
		td.className = 'wm_contacts_view_title';
		td.innerHTML = Lang.Fax + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Fax', ':');
		td = tr.insertCell(4);
		td.style.width = '30%';
		inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['size', '18'], ['maxlength', '50']]);
		this.SetInputKeyPress(inp);
		this._faxObj = inp;

		tr = tbl.insertRow(4);
		td = tr.insertCell(0);
		td.className = 'wm_contacts_view_title';
		td.innerHTML = Lang.StateProvince + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'StateProvince', ':');
		td = tr.insertCell(1);
		inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['size', '18'], ['maxlength', '65']]);
		this.SetInputKeyPress(inp);
		this._stateObj = inp;
		td = tr.insertCell(2);
		td = tr.insertCell(3);
		td.className = 'wm_contacts_view_title';
		td.innerHTML = Lang.Phone + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Phone', ':');
		td = tr.insertCell(4);
		inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['size', '18'], ['maxlength', '50']]);
		this.SetInputKeyPress(inp);
		this._phoneObj = inp;

		tr = tbl.insertRow(5);
		td = tr.insertCell(0);
		td.className = 'wm_contacts_view_title';
		td.innerHTML = Lang.ZipCode + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'ZipCode', ':');
		td = tr.insertCell(1);
		inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['size', '18'], ['maxlength', '10']]);
		this.SetInputKeyPress(inp);
		this._zipObj = inp;
		td = tr.insertCell(2);
		td = tr.insertCell(3);
		td.className = 'wm_contacts_view_title';
		td.innerHTML = Lang.CountryRegion + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'CountryRegion', ':');
		td = tr.insertCell(4);
		inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['size', '18'], ['maxlength', '65']]);
		this.SetInputKeyPress(inp);
		this._countryObj = inp;

		tr = tbl.insertRow(6);
		td = tr.insertCell(0);
		td.className = 'wm_contacts_view_title';
		td.innerHTML = Lang.WebPage + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'WebPage', ':');
		td = tr.insertCell(1);
		td.colSpan = 4;
		inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['size', '45'], ['maxlength', '85']]);
		this.SetInputKeyPress(inp);
		this._webObj = inp;
		inp = CreateChild(td, 'input', [['type', 'button'], ['class', 'wm_go_button'], ['value', Lang.Go]]);
		WebMail.LangChanger.Register('value', inp, 'Go', '');
		inp.onclick = function () { OpenURL(obj._webObj.value); };
		
		var hr = CreateChild(container, 'hr', [['style', 'background-color: #e1e1e1; color: #e1e1e1; border: 0; height: 1px; padding: 0; margin: 0 15px; width: 94%']]);
		this._tabs[0] = new CContactTab(tbl, imgDiv, tabTbl, hr);
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}