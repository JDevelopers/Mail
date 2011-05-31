/*
 * Classes:
 * 	CMessageReplyPane(container)
 */

function CMessageReplyPane(container)
{
	this._mainContainer = null;
	this._textDisplay = null;
	this._msgObj = null;
	
	this._textValue = '';
	this._focused = false;
	this._saving = false;
	this._sending = false;
	this._msgId = -1;
	this._msgUid = '';
	
	this._maxHeight = 20;
	this._maxRows = 0;
	
	this._waitParams = null;
	this._resizeTimeOut = null
	
	this._build(container);
}

CMessageReplyPane.prototype = {
	Show: function (msg)
	{
		this._msgObj = msg;
		this._mainContainer.className = 'wm_reply_pane';
		if (this._waitParams != null) {
			var waitMsg = this._waitParams.Msg;
			if (msg.IsEqual(waitMsg)) {
				this.SendMessage(this._waitParams.Mode);
				this._waitParams = null;
			}
		}
		else {
			this._textValue = '';
			this._textDisplay.value = '';
			this._textDisplay.rows = 2;
			this._textDisplay.blur();
			this._msgId = -1;
			this._msgUid = '';
			this.SetStyle(false);
			this.ResetFlags();
		}
	},
	
	Hide: function ()
	{
		this._mainContainer.className = 'wm_hide';
	},
	
	SetMessageId: function (msgId, msgUid)
	{
		this.ResetFlags(SAVE_MODE);
		this._msgId = msgId;
		this._msgUid = msgUid;
	},
	
	ResetFlags: function (mode)
	{
		switch (mode) {
			case SEND_MODE:
				this._sending = false;
				break;
			case SAVE_MODE:
				this._saving = false;
				break;
			default:
				this._saving = false;
				this._sending = false;
		}
	},
	
	_getStep: function (newCols, width, symbolWidth)
	{
		this._textDisplay.cols = newCols;
		var newWidth = this._textDisplay.offsetWidth;
		var dif = newWidth - width;
		var step = (dif == 0) ? 0 : Math.round((dif / 2) / symbolWidth);
		return step;
	},
	
	ResizeWidth: function (width)
	{
		if (this._textDisplay.offsetWidth == 0 || this._textDisplay.cols == 0) return;
		
		width = width - 22;
		var symbolWidth = this._textDisplay.offsetWidth / this._textDisplay.cols;
		var newCols = Math.round(width / symbolWidth);

		var step = this._getStep(newCols, width, symbolWidth);
		var positiveStep = (step > 0);
		var counter = 0;
		while (positiveStep == (step > 0) && step != 0 && counter < 10) {
			newCols -= step;
			step = this._getStep(newCols, width, symbolWidth);
			counter++;
		}
		if (this._textDisplay.offsetWidth > width) {
			this._textDisplay.cols = newCols - 1;
		}
	},
	
	SetMaxHeight: function (maxHeight)
	{
		maxHeight = maxHeight - 50;
		if (this._maxHeight != maxHeight) {
			this._maxHeight = maxHeight;
			this._maxRows = 0;
		}
	},
	
	ResizeHeight: function ()
	{
		var i, k, j;
		var strs = this._textDisplay.value.replace('/\r\n/g', '\n').replace('/\r/g', '\n').split(/\n/g);
		var lines = strs.length;
		
		for (i = 0; i < strs.length; i++) {
			var words = strs[i].split(' '); 
			for (k = j = 0; j < words.length; j++) {
				k += words[j].length + 1;
				if (k > this._textDisplay.cols) {
					k = 0;
					lines++;
				}
			}
		}

		var rowsCount = lines + 1;
		if (this._maxRows != 0 && rowsCount > this._maxRows) rowsCount = this._maxRows;
		if (this._textDisplay.rows != rowsCount) {
			this._textDisplay.rows = rowsCount;
			while (rowsCount > 2 && this._textDisplay.offsetHeight > this._maxHeight) {
				rowsCount--;
				this._textDisplay.rows = rowsCount;
				this._maxRows = rowsCount;
			}
			WebMail.ResizeBody(RESIZE_MODE_MSG_HEIGHT);
		}
	},

	GetHeight: function ()
	{
		return this._mainContainer.offsetHeight;
	},
	
	SetStyle: function (focused)
	{
		var value = this._textDisplay.value;
		var emptyValue = (value == '');
		var quickValue = (value == Lang.QuickReply && this._textValue == '');
		if (!focused && (emptyValue || quickValue)) {
			this._textDisplay.className = 'wm_reply_text wm_blured_text';
			this._textDisplay.value = Lang.QuickReply;
			this._textValue = '';
		}
		else {
			this._textDisplay.className = 'wm_reply_text wm_focused_text ';
			if (quickValue) {
				this._textDisplay.value = '';
			}
			else {
				this._textValue = this._textDisplay.value;
			}
		}
	},
	
	_getValue: function ()
	{
		var value = this._textDisplay.value;
		if (value == Lang.QuickReply) value = this._textValue;
		return value;
	},
	
	SwitchToFullForm: function (replyAction)
	{
		WebMail.ReplyMessageClick(replyAction, this._msgObj, this._getValue());
	},
	
	SendOrRequestForSend: function (mode)
	{
		if (this._msgObj == null) return;
		
		switch (mode) {
		    case SEND_MODE:
				if (this._sending) return;
			    this._sending = true;
			    break;
			case SAVE_MODE:
				if (this._saving) return;
			    this._saving = true;
			    break;
			default:
				return;
		}
		
		if (this._msgObj.IsReplyHtml || this._msgObj.IsReplyPlain) {
			this.SendMessage(mode);
		}
		else {
			this._waitParams = {Mode: mode, Msg: this._msgObj};
			this._requestMessageReplyPart(this._msgObj, false);
		}
	},
	
	SendMessage: function (mode)
	{
		if (this._msgObj == null) return;
		
		var newMsg = this._getReplyMessage(this._msgObj, TOOLBAR_REPLYALL, this._getValue());
		if (this._msgId != -1) {
			newMsg.Id = this._msgId;
			newMsg.Uid = this._msgUid;
		}

		if (!this._checkMsgSize(newMsg)) {
			this._showMailboxSizeWarning(mode);
			return;
		}
		
		var xml = newMsg.GetInXML();
		switch (mode) {
		    case SEND_MODE:
				if (window.opener) {
					window.opener.WebMail.DataSource.Cache.ClearAllContactsGroupsList();
				}
				else {
					WebMail.DataSource.Cache.ClearAllContactsGroupsList();
				}
		        RequestHandler('send', 'message', xml);
			    break;
			case SAVE_MODE:
			    RequestHandler('save', 'message', xml);
			    break;
		}

		if (window.opener) {
			window.opener.MarkMsgAsRepliedHandler(newMsg);
		}
		else {
			MarkMsgAsRepliedHandler(newMsg);
		}
	},
	
	_build: function (container)
	{
		this._mainContainer = CreateChild(container, 'div', [['class', 'wm_reply_pane']]);
		
		var tbl = CreateChild(this._mainContainer, 'table');
		var tr = tbl.insertRow(0);
		var td = tr.insertCell(0);
		td.colSpan = 2;
		this._textDisplay = CreateChild(td, 'textarea', [['cols', '50'], ['rows', '2']]);
		var obj = this;
		this._textDisplay.onfocus = function () {
			obj.SetStyle(true);
		};
		this._textDisplay.onblur = function () {
			obj.SetStyle(false);
		};
		this._textDisplay.onkeydown = function (ev)
		{
			if (null === obj._resizeTimeOut)
			{
				var TimeoutFunc = function() {obj.ResizeHeight();obj._resizeTimeOut = null;};
				obj._resizeTimeOut = setTimeout(TimeoutFunc, 300);
			}
			
            ev = ev ? ev : window.event;
            var key = Keys.GetCodeFromEvent(ev);
            if (key == Keys.Enter && ev.ctrlKey) {
                obj.SendOrRequestForSend(SEND_MODE);
            }
		};
		
		tr = tbl.insertRow(1);
		td = tr.insertCell(0);
		var sendFunc = function () {obj.SendOrRequestForSend(SEND_MODE);};
		var saveFunc = function () {obj.SendOrRequestForSend(SAVE_MODE);};
		BuildButton(td, 'SendMessage', sendFunc);
		BuildButton(td, 'SaveMessage', saveFunc);

		td = tr.insertCell(1);
		td.style.textAlign = 'right';
		var a = CreateChild(td, 'a', [['href', '#'], ['class', 'wm_reply_full_view_link']]);
		a.innerHTML = Lang.SwitchToFullForm;
		a.onclick = function () {
			obj.SwitchToFullForm(TOOLBAR_REPLYALL);
			return false;
		};
	}
}

CMessageReplyPane.prototype._getFromAddrByAcctId = CNewMessageScreen.prototype._getFromAddrByAcctId;
CMessageReplyPane.prototype._getReplyMessage = CNewMessageScreen.prototype._getReplyMessage;
CMessageReplyPane.prototype._checkMsgSize = CNewMessageScreen.prototype._checkMsgSize;
CMessageReplyPane.prototype._showMailboxSizeWarning = CNewMessageScreen.prototype._showMailboxSizeWarning;

CMessageReplyPane.prototype._requestMessageReplyPart = CWebMail.prototype._requestMessageReplyPart;

function BuildButton(container, langField, func)
{
	var span = CreateChild(container, 'span');
	span.className = 'wm_reply_button wm_control';
	span.onclick = func;

	var spanCh = CreateChild(span, 'span');
	spanCh.innerHTML = Lang[langField];
	WebMail.LangChanger.Register('innerHTML', spanCh, langField, '');
}

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}