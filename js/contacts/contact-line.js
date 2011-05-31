/*
 * Classes:
 *  CContactsTableController(contactsScr)
 *  CContactLine(contact, tr)
 */

function CContactsTableController(contactsScr)
{
	this.ResizeHandler = 'ResizeContactsTab';
	this.ListContanerClass = 'wm_contact_list_div';
	this.CurrId = -1;
	this.CurrIsGroup = false;
	
	this.CreateLine = function (obj, tr)
	{
		tr.id = obj.Id + STR_SEPARATOR + obj.IsGroup + STR_SEPARATOR + obj.Name + STR_SEPARATOR + obj.ClearEmail;
		tr.Email = (!obj.IsGroup && obj.Name.length > 0) ? '"' + obj.Name + '" ' + '&lt;' + obj.ClearEmail + '&gt;' : obj.ClearEmail;
		return new CContactLine(obj, tr);
	};

	this.ClickLine = function (id)
	{
		var params = id.split(STR_SEPARATOR);
		if (params.length == 4)
			this.CurrId = params[0];
			if (params[1] == '0') {
				this.CurrIsGroup = false;
				SetHistoryHandler(
					{
						ScreenId: SCREEN_CONTACTS,
						Entity: PART_VIEW_CONTACT,
						IdAddr: params[0]
					}
				);
			}
			else {
				this.CurrIsGroup = true;
				SetHistoryHandler(
					{
						ScreenId: SCREEN_CONTACTS,
						Entity: PART_VIEW_GROUP,
						IdGroup: params[0]
					}
				);
			}
	};
	
	this.DblClickLine = function (tr)
	{
		MailAllHandler(HtmlDecode(tr.Email), '', '')
	};
	
	this.Delete = function ()
	{
		contactsScr.DeleteSelected();
	};
	
	this.SetEventsHandlers = function (obj, tr)
	{
		var objController, clickElem, clickTagName, tdElem;
		objController = this;
		tr.onclick = function(e) {
			e = e ? e : window.event;
			clickElem = (Browser.Mozilla) ? e.target : e.srcElement;
			clickTagName = (clickElem) ? clickElem.tagName : 'NOTHING';
			if (clickTagName == 'INPUT' || e.ctrlKey) {
				obj._selection.CheckCtrlLine(this.id);
			} else if (e.shiftKey) {
				obj._selection.CheckShiftLine(this.id);
			}
			else {
				tdElem = clickElem;
				while (tdElem && tdElem.tagName != 'TD') {
					tdElem = tdElem.parentNode;
				}
				if (tdElem.name != 'not_view') {
					objController.ClickLine(this.id);
					obj._selection.CheckLine(this.id);
				}
			}
		};
		tr.ondblclick = function (e) {
			objController.DblClickLine(this);
			return false;//?
		};
	};
};

function CContactLine(contact, tr)
{
	this.ContactId = contact.Id;
	this.IsGroup = contact.IsGroup;
	
	tr.onmousedown = function() { return false; };//don't select content in Opera
	tr.onselectstart = function() { return false; };//don't select content in IE
	tr.onselect = function() { return false; };//don't select content in IE

	this.Node = tr;
	this.Id = tr.id;
	this._className = 'wm_inbox_read_item';
	this.Checked = false;
	this.ApplyClassName();
	
	this.fCheck = new CCheckBoxCell();
	
	var content = contact.IsGroup ? 'wm_inbox_lines_group' : '';
	this.fIsGroup = new CImageCell('', '', content);

	this.fName = new CTextCell(contact.Name);
	this.fEmail = new CTextCell(contact.Email);
}

CContactLine.prototype = 
{
    View: function (viewed)
    {
		this._viewed = viewed;
		this.ApplyClassName();
    },
    
	Check: function()
	{
		this.Checked = true;
		this.fCheck.Node.checked = true;
		this.ApplyClassName();
	},

	Uncheck: function()
	{
		this.Checked = false;
		this.fCheck.Node.checked = false;
		this.ApplyClassName();
	},
	
	ApplyClassName: function ()
	{
		if (this._viewed) {
			this.Node.className = this._className + '_view';
		}
		else if (this.Checked) {
			this.Node.className = this._className + '_select';
		}
		else {
			this.Node.className = this._className;
		}
	},
	
	SetContainer: function (field, container)
	{
		if (field == 'fCheck' || field == 'fIsGroup')
			container.name = 'not_view';
		this[field].SetContainer(container);
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}