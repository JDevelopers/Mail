/*
 * Classes:
 *  CMessageListTableController(clickHandler)
 *  CMessageLine(msg, tr, doFlag)
 */

function CMessageListTableController(clickHandler)
{
	this._clickHandler = clickHandler;
	this._dblClickHandler = DblClickHandler;
	this._doFlag = true;
	this.ResizeHandler = 'ResizeMessagesTab';
	this.ListContanerClass = 'wm_inbox';
	
	this.SetDoFlag = function (doFlag)
	{
		this._doFlag = doFlag;
	};
	
	this.CreateLine = function (msgHeaders, tr, screenId)
	{
		tr.id = msgHeaders.GetIdForList(screenId);
		return new CMessageLine(msgHeaders, tr, this._doFlag);
	};
	
	this.ClickLine = function (id, obj)
	{
		if (obj.LastClickLineId != id) {
			obj.LastClickLineId = id;
			if (null != obj._timer) {
				clearTimeout(obj._timer);
			}
			obj._timer = setTimeout(this._clickHandler + "('" + EncodeStringForEval(id) + "')", 200);
		}
	};
	
	this.Delete = function ()
	{
		RequestMessagesOperationHandler(TOOLBAR_DELETE, [], []);
	};
	
	this.DblClickLine = function (tr, obj, shift)
	{
		if (null != obj._dragNDrop) {
			obj._dragNDrop.EndDrag();
		}
		if (null != obj._timer) {
			clearTimeout(obj._timer);
		}
		this._dblClickHandler.call(tr, shift);
	};

	this.SetEventsHandlers = function (obj, tr)
	{
		var objController = this;
		tr.onmousedown = function(e) {
			e = e ? e : window.event;
			if (e.button == 2) {
				return false; // right button click
			}
			if (null != obj._dragNDrop) {
				obj._dragNDrop.RequestDrag(e, this);
			}
			var clickElem = (Browser.Mozilla) ? e.target : e.srcElement;
			var clickTagName = clickElem ? clickElem.tagName : 'NOTHING';
			// wait for flag message
			if (objController._doFlag && clickTagName == 'DIV' && clickElem.id.substr(0,8) == 'flag_img') {
				return false;
			}
			// wait for check message with ctrl key
			if (clickTagName == 'INPUT' || e.ctrlKey) {
				return false;
			}
			// wait for check message with shift key
			if (e.shiftKey) {
				return false;
			}
			// wait for multidrag
			if (!obj._selection.SingleForDrag(this.id)) {
				return false;
			}
			// view message
			var tdElem = clickElem;
			while (tdElem && tdElem.tagName != 'TD') {
				tdElem = tdElem.parentNode;
			}
			if (tdElem.name != 'not_view') {
				obj._selection.CheckLine(this.id);
				objController.ClickLine(this.id, obj);
			}
			return false;
		};
		tr.onclick = function(e) {
			if (null != obj._dragNDrop) {
				obj._dragNDrop.EndDrag();
			}
			e = e ? e : window.event;
			var clickElem = (Browser.Mozilla) ? e.target : e.srcElement;
			var clickTagName = (clickElem) ? clickElem.tagName : 'NOTHING';
			// flag message
			if (objController._doFlag && clickTagName == 'DIV' && clickElem.id.substr(0,8) == 'flag_img') {
				obj._selection.FlagLine(this.id);
				return;
			}
			// check message with ctrl key
			if (clickTagName == 'INPUT' || e.ctrlKey) {
				obj._selection.CheckCtrlLine(this.id);
				return;
			}
			// check message with shift key
			if (e.shiftKey) {
				obj._selection.CheckShiftLine(this.id);
				return;
			}
			// view message
			var tdElem = clickElem;
			while (tdElem && tdElem.tagName != 'TD') {
				tdElem = tdElem.parentNode;
			}
			if (tdElem.name != 'not_view') {
				obj._selection.CheckLine(this.id);
				objController.ClickLine(this.id, obj);
			}
		};
		tr.ondblclick = function (e) {
			var clickElem, clickTagName;
			e = e ? e : window.event;
			clickElem = (Browser.Mozilla) ? e.target : e.srcElement;
			clickTagName = (clickElem) ? clickElem.tagName : 'NOTHING';
			if (clickTagName != 'INPUT') {
				objController.DblClickLine(this, obj, e.shiftKey);
			}
		};
	};
};

function CMessageLine(msg, tr, doFlag)
{
	tr.onmousedown = function() { return false; };//don't select content in Opera
	tr.onselectstart = function() { return false; };//don't select content in IE
	tr.onselect = function() { return false; };//don't select content in IE
	tr.size = msg.Size;
	
	this._className = '';
	this.Flagged = msg.Flagged;
	this.Replied = msg.Replied;
	this.Forwarded = msg.Forwarded;
	this.Deleted = msg.Deleted;
	this.Read = msg.Read;
	this.Checked = false;
	this.Gray = msg.Gray;
	this.NoReply = msg.NoReply;
	this.NoReplyAll = msg.NoReplyAll;
	this.NoForward = msg.NoForward;
	this.Sensivity = msg.Sensivity;

	this.MsgFromAddr = msg.FromAddr;
	this.MsgToAddr = msg.ToAddr;
	this.MsgDate = msg.Date;
	this.MsgFullDate = msg.FullDate;
	this.MsgSize = msg.Size;
	this.MsgSubject = msg.Subject;
	this.MsgId = msg.Id;
	this.MsgUid = msg.Uid;
	this.MsgFolderId = msg.FolderId;
	this.MsgFolderFullName = msg.FolderFullName;

	this.Node = tr;
	this.Id = tr.id;
	this.SetClassName();
	this.ApplyClassName();
	
	this.fCheck = new CCheckBoxCell();
	
	var content = msg.HasAttachments ? 'wm_inbox_lines_attachment' : '';
	this.fHasAttachments = new CImageCell('', '', content);

	switch (msg.Importance) {
		case PRIORITY_HIGH:
			content = 'wm_inbox_lines_priority_high';
			break;
		case PRIORITY_LOW:
			content = 'wm_inbox_lines_priority_low';
			break;
		default:
			// content = 'wm_inbox_lines_priority_normal';
			content = '';
			break;
	}
	this.fImportance = new CImageCell('', '', content);

	content = (msg.Sensivity) ? 'wm_inbox_lines_sensivity' : '';
	this.fSensivity = new CImageCell('', '', content);

	var className = doFlag ? 'wm_control_img' : '';
	content = this.Flagged ? 'wm_inbox_lines_flag' : 'wm_inbox_lines_unflag';
	this.fFlagged = new CImageCell(className, 'flag_img' + Math.random(), content);

	if (WebMail.ListScreenId == SCREEN_MESSAGE_LIST_CENTRAL_PANE) {
		this.fFromAddr = new CTextCell(this._getFromToContent(true));
	}
	else {
		this.fFromAddr = new CTextCell(this.MsgFromAddr);
	}
	if (WebMail.ListScreenId == SCREEN_MESSAGE_LIST_CENTRAL_PANE) {
		this.fToAddr = new CTextCell(this._getFromToContent(false));
	}
	else {
		this.fToAddr = new CTextCell(msg.ToAddr);
	}
	this.fDate = new CTextCell(this.MsgDate, this.MsgFullDate);
	this.fSize = new CTextCell(GetFriendlySize(this.MsgSize));
	this.fSubject = new CTextCell(this._getSubjectContent());
}

CMessageLine.prototype = 
{
	_getFromToContent: function (useFrom)
	{
		var content = '';
		content += '<span class="wm_inbox_message_size">' + GetFriendlySize(this.MsgSize) + '</span>';
		content += '<span class="wm_inbox_message_date" title="' + this.MsgFullDate + '">' + this.MsgDate + '</span>';
		content += '<div class="wm_inbox_subject">' + this._getSubjectContent() + '</div>';
		content +=  (useFrom) 
			? '<div>' + this.MsgFromAddr + '</div>'
			: '<div>' + this.MsgToAddr + '</div>';
		return content;
	},
	
    _getSubjectContent: function ()
    {
		var subj = (this.MsgSubject == '')
			? '<span class="wm_no_subject">' + Lang.MessageNoSubject + '</span>'
			: this.MsgSubject;
	    if (this.Replied && this.Forwarded) {
	        return '<span class="wm_inbox_lines_rpl_fwd" title="' + Lang.RepliedForwardedMessageTitle 
				+ '"></span>' + subj;
	    }
	    if (this.Replied) {
	        return '<span class="wm_inbox_lines_replied" title="' + Lang.RepliedMessageTitle 
				+ '"></span>' + subj;
	    }
	    if (this.Forwarded) {
	        return '<span class="wm_inbox_lines_forwarded" title="' + Lang.ForwardedMessageTitle 
				+ '"></span>' + subj;
	    }
	    return subj;
    },
    
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
	
	Flag: function ()
	{
		RequestMessagesOperationHandler(TOOLBAR_FLAG, [this.Id], [this.MsgSize]);
	},
	
	Unflag: function ()
	{
		RequestMessagesOperationHandler(TOOLBAR_UNFLAG, [this.Id], [this.MsgSize]);
	},
	
	SetClassName: function ()
	{
		if (this.Deleted) {
			this._className = 'wm_inbox_deleted_item';
		}
		else if (this.Read) {
			this._className = 'wm_inbox_read_item';
		}
		else {
			this._className = 'wm_inbox_item';
		}
	},
	
	ApplyClassName: function ()
	{
		var className = this._className;
		if (this._viewed) {
			className += '_view';
		}
		else if (this.Checked) {
			className += '_select';
		} else if (this.Gray) {
			className += ' wm_inbox_grey_item';
		}
		this.Node.className = className;
	},
	
	SetContainer: function (field, container)
	{
		if (WebMail.ListScreenId == SCREEN_MESSAGE_LIST_CENTRAL_PANE) {
			field = 'f' + field;
		}
		if (field == 'fCheck' || field == 'fHasAttachments') {
			container.name = 'not_view';
		}
		this[field].SetContainer(container);
	},

	ApplyFlagImg: function ()
	{
		var content = this.Flagged ? 'wm_inbox_lines_flag' : 'wm_inbox_lines_unflag';
		this.fFlagged.SetContent(content);
	},

	ApplyFromSubj: function ()
	{
		this.fFromAddr.SetContent(this.MsgFromAddr);
		this.fSubject.SetContent(this._getSubjectContent());
	},
	
	ApplyRepliedForwarded: function ()
	{
		this.fSubject.SetContent(this._getSubjectContent());
	},
	
	SetParams: function (field, value)
	{
		var readed = 0;
		switch (field) {
			case 'Read':
				if (this.Read == false && value == true) readed = 1;
				if (this.Read == true && value == false) readed = -1;
				this.Read = value;
				this.SetClassName();
				this.ApplyClassName();
				break;
			case 'Deleted':
				this.Deleted = value;
				this.SetClassName();
				this.ApplyClassName();
				break;
			case 'Flagged':
				this.Flagged = value;
				this.ApplyFlagImg();
				break;
			case 'Replied':
				this.Replied = value;
				this.ApplyRepliedForwarded();
				break;
			case 'Forwarded':
				this.Forwarded = value;
				this.ApplyRepliedForwarded();
				break;
			case 'Gray':
				this.Gray = value;
				this.ApplyClassName();
				break;
		}//switch field
		return readed;
	},
	
	IsCorrectIdData: function (msg)
	{
		return (this.MsgId == msg.Id && this.MsgUid == msg.Uid && this.MsgFolderId == msg.FolderId &&
					this.MsgFolderFullName == msg.FolderFullName);
	},
	
	ChangeFromSubjData: function (msg, newId)
	{
		if (newId) {
			this.Id = newId;
			this.Node.id = newId;
		}
		this.Node.size = msg.Size;
		this.MsgFromAddr = msg.FromAddr;
		this.MsgSubject = msg.Subject;
		this.ApplyFromSubj();
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}