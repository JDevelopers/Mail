/*
 * Classes:
 *  CPopupAutoFilling(requestHandler, selectHandler)
 *  CPopupContacts(requestHandler, selectHandler)
 */

function CPopupAutoFilling(requestHandler, selectHandler)
{
	this._suggestInput = null;

	this._requestHandler = requestHandler;
	this._selectHandler = selectHandler;

	this._popup = null;
	this._shown = false;

	this._keyword = '';
	this._requestKeyword = '';
	this._pickPos = -1;
	this._lines = Array();

	this._timeOut = null;

	this.Build();
}

CPopupAutoFilling.prototype =
{
	Show: function ()
	{
		this._popup.className = 'wm_auto_filling_cont';
		this._shown = true;
		this.Replace();
	},
	
	Hide: function ()
	{
		this._keyword = '';
		this._popup.className = 'wm_hide';
		this._shown = false;
	},
	
	SetSuggestInput: function (suggestInput)
	{
		this.Hide();
		if (this._suggestInput != null) {
			this._suggestInput.onkeyup = function () {};
		}
		this._suggestInput = suggestInput;
		suggestInput.setAttribute("autocomplete", "off");  
		var obj = this;
		this._suggestInput.onkeyup = function (ev) {
			obj.KeyUpHandler(ev);
		};
	},
	
	Replace: function ()
	{
		if (!this._shown) return;
		
		var siBounds = GetBounds(this._suggestInput);
		this._popup.style.top = siBounds.Top + siBounds.Height + 'px';
		this._popup.style.left = siBounds.Left + 'px';
		this._popup.style.width = 'auto';
		/*get borders' width to set correct popup width and height*/
		var popupBorders = GetBorders(this._popup);
		var vertBordersWidth = popupBorders.Top + popupBorders.Bottom;
		var horizBordersWidth = popupBorders.Left + popupBorders.Right;
		var pWidth = this._popup.offsetWidth;
		/*set popup width in absolute value for hiding select under popup in ie6*/
		if (siBounds.Width > pWidth) {
			this._popup.style.width = (siBounds.Width - horizBordersWidth) + 'px';
		}
		else {
			this._popup.style.width = (pWidth - horizBordersWidth) + 'px';
		}
		this._popup.style.height = 'auto';
		var pHeight = this._popup.offsetHeight;
		/*set popup height in absolute value for hiding select under popup in ie6*/
		this._popup.style.height = (pHeight - vertBordersWidth) + 'px';
	},
	
	ClickBody: function (ev)
	{
		if (!this._shown) return;

		ev = ev ? ev : window.event;
		var elem = (Browser.Mozilla) ? ev.target : ev.srcElement;
		if (elem && elem.tagName == 'IMG' && elem.parentNode) {
			elem = elem.parentNode;
		}
		if (elem && elem.tagName == 'B' && elem.parentNode) {
			elem = elem.parentNode;
		}
		if (elem && isNaN(elem.Number) && elem.tagName != 'INPUT') {
			this.Hide();
		}
		else if (elem && elem.tagName == 'DIV') {
			this.SelectLine(elem);
		}
	},

	Fill: function (itemsArr, keywordStr, lastPhrase)
	{
		var obj = this;
		this._keyword = keywordStr;
		this._requestKeyword = '';
		CleanNode(this._popup);
		MakeOpaqueOnSelect(this._popup);
		this._pickPos = -1;
		this._lines = Array();
		var iCount = itemsArr.length;
		for (var i = 0; i < iCount; i++) {
			var div = CreateChild(this._popup, 'div');
			var innerHtml = '';
			if (itemsArr[i].IsGroup) {
				innerHtml += '<span class="wm_inbox_lines_group">&nbsp;</span>';
			}
			div.innerHTML = innerHtml + itemsArr[i].DisplayText;
			div.ContactGroup = itemsArr[i];
			div.Number = i;
			div.onmouseover = function () {
				obj.PickLine(this.Number);
			};
			div.onmouseout = function () {
				this.className = '';
				if (obj._pickPos == this.Number) {
					obj._pickPos = -1;
				}
			};
			this._lines[i] = div;
		}
		if (lastPhrase && lastPhrase.length > 0) {
			var lastPhraseDiv = CreateChild(this._popup, 'div');
			lastPhraseDiv.className = 'wm_secondary_info';
			lastPhraseDiv.innerHTML = lastPhrase;
		}
		this.Show();
	},
	
	GetKeyword: function ()
	{
		var arr = this._suggestInput.value.replace(/;/g, ',').split(',');
		return Trim(arr[arr.length - 1]);
	},
	
	SetSuggestions: function (suggestionStr)
	{
		var lastKeyLength = this.GetKeyword().length;
		var end = this._suggestInput.value.length - lastKeyLength;
		if (end > 0) {
			if (lastKeyLength > 0) {
				this._suggestInput.value = this._suggestInput.value.slice(0, end);
				this._suggestInput.value += suggestionStr;
			}
		}
		else {
			this._suggestInput.value = suggestionStr;
		}
	},
	
	SelectLine: function (obj)
	{
		this.Hide();
		this.SetSuggestions(obj.ContactGroup.ReplaceText) ;
		this._pickPos = -1;
		this._suggestInput.focus();
		if (Browser.IE) {
			var textRange = this._suggestInput.createTextRange();
			textRange.collapse(false);
			textRange.select();
		}
		this._selectHandler.call(obj);
	},
	
	PickLine: function (posInt)
	{
		if (this._pickPos != -1) {
			this._lines[this._pickPos].className = '';
		}
		this._pickPos = posInt;
		if (this._pickPos != -1) {
			this._lines[this._pickPos].className = 'wm_auto_filling_chosen';
		}
	},
	
	KeyUpHandler: function (ev)
	{
		var key = Keys.GetCodeFromEvent(ev);
		switch (key) {
			case Keys.Enter:
				if (this._pickPos != -1) {
					var td = this._lines[this._pickPos];
					this.SelectLine(td);
				}
				break;
			case Keys.Up:
				if (this._pickPos > -1) {
					this.PickLine(this._pickPos - 1);
				}
				break;
			case Keys.Down:
				if (this._pickPos < (this._lines.length - 1)) {
					this.PickLine(this._pickPos + 1);
				}
				break;
			default:
				var keyword = this.GetKeyword();
				if (this.CheckRequestKeyword(keyword)) {
					if (this._timeOut != null) {
						clearTimeout(this._timeOut);
					}
					var obj = this;
					this._timeOut = setTimeout ( function () {
						obj.RequestKeyword(); 
					}, 500 );
				}
				else if (keyword.length == 0) {
					this.Hide();
				}
				break;
		}
	},
	
	CheckRequestKeyword: function (keyword)
	{
		if (keyword.length > 0 && this._keyword != keyword) {
			if (this._requestKeyword.length > 0) {
				var reg = new RegExp(this._requestKeyword.PrepareForRegExp(), 'gi');
				var res = reg.exec(keyword);
				if (res != null && res.index == 0) {
					return false;
				}
				else {
					return true;
				}
			}
			return true;
		}
		else {
			return false;
		}
	},
	
	RequestKeyword: function ()
	{
		var keyword = this.GetKeyword();
		if (this.CheckRequestKeyword(keyword)) {
			this._requestKeyword = keyword;
			this._requestHandler.call({Keyword: keyword});
		}
	},

	Build: function ()
	{
		this._popup = CreateChild(document.body, 'div');
		this._popup.style.position = 'absolute';
		this.Hide();
	}
};

function CPopupContacts(requestHandler, selectHandler)
{
	this._suggestInput = null;
	this._suggestControl = null;

	this._requestHandler = requestHandler;
	this._selectHandler = selectHandler;

	this._popup = null;
	this._shown = false;
	this._controlClick = false;

	this._pickPos = -1;
	this._lines = Array();

	this._timeOut = null;

	this.Build();
}

CPopupContacts.prototype =
{
	ControlClick: function (suggestInput, suggestControl)
	{
		if (this._shown && this._suggestInput == suggestInput) {
			this.Hide();
			return;
		} else {
			this._controlClick = true;
			this._suggestInput = suggestInput;
			this._suggestControl = suggestControl;
			this._requestHandler.call({Keyword: ''});
		}
	},

	Show: function ()
	{
		this._popup.className = 'wm_popular_contacts_cont';
		this._shown = true;
		this.Replace();
	},
	
	Hide: function ()
	{
		this._popup.style.width = 'auto'; // for Opera
		this._popup.style.height = 'auto'; // for Opera
		this._popup.className = 'wm_hide';
		this._shown = false;
		this._controlClick = false;
	},
	
	Replace: function ()
	{
		if (this._shown)
		{
			var siBounds = GetBounds(this._suggestInput);
			this._popup.style.top = siBounds.Top + siBounds.Height + 1 + 'px';
			var scBounds = GetBounds(this._suggestControl);
			if (!window.RTL) {
				this._popup.style.left = scBounds.Left + 'px';
			}

			this._popup.style.width = 'auto';
			this._popup.style.height = 'auto';
			var pWidth = this._popup.offsetWidth;
			var pHeight = this._popup.offsetHeight;
			var bordersHeight = 2;
			var bordersWidth = 2;
			var paddingHeight = 16;
			/* set popup width and height in absolute value for hiding select under popup in ie6 */
			this._popup.style.width = pWidth - bordersWidth + 'px';
			this._popup.style.height = pHeight - bordersHeight - paddingHeight + 'px';
			if (window.RTL) {
				this._popup.style.left = (scBounds.Left + scBounds.Width - pWidth) + 'px';
			}
		}
	},
	
	ClickBody: function (ev)
	{
		if (this._shown && !this._controlClick) {
			ev = ev ? ev : window.event;
			if (Browser.Mozilla) {
				elem = ev.target;
			} else {
				elem = ev.srcElement;
			}
			while (elem && elem.tagName != 'DIV' && elem.parentNode) {
				elem = elem.parentNode;
			}
			if (elem && elem.className != 'wm_popular_contacts_cont' && elem.parentNode) {
				elem = elem.parentNode;
			}
			if (elem && elem.className != 'wm_popular_contacts_cont') {
				this.Hide();
			}
		}
		this._controlClick = false;
	},

	Fill: function (itemsArr)
	{
		var obj, imgDiv, iCount, i, div, innerHtml;
		obj = this;
		CleanNode(this._popup);
		MakeOpaqueOnSelect(this._popup);
		imgDiv = CreateChild(this._popup, 'div', [['class', 'wm_popular_contacts_image wm_control']]);
		imgDiv.onclick = function () {
			obj.Hide();
		};
		this._pickPos = -1;
		this._lines = [];
		iCount = itemsArr.length;
		for (i = 0; i < iCount; i++) {
			div = CreateChild(this._popup, 'div');
			innerHtml = '';
			if (itemsArr[i].IsGroup) {
				innerHtml += '<span class="wm_inbox_lines_group">&nbsp;</span>';
			}
			div.innerHTML = innerHtml + itemsArr[i].DisplayText;
			div.ContactGroup = itemsArr[i];
			div.Number = i;
			div.onmouseover = function () {
				obj.PickLine(this.Number);
			};
			div.onmouseout = function () {
				this.className = '';
				if (obj._pickPos == this.Number) {
					obj._pickPos = -1;
				}
			};
			div.onclick = function () {
				obj.SelectLine(this);
			};
			div.onmousedown = function () {
				return false; //don't select content in Opera
			};
			div.onselectstart = function () {
				return false; //don't select content in IE
			};
			div.onselect = function () {
				return false; //don't select content in IE
			};
			this._lines[i] = div;
		}
		this.Show();
	},
	
	SetSuggestions: function (suggestionStr)
	{
		suggestionStr = Trim(suggestionStr);
		var inputValue = Trim(this._suggestInput.value);
		if (inputValue.length > 0) {
			if (suggestionStr.length > 0) {
				this._suggestInput.value = inputValue + ', ' + suggestionStr;
			}
		} else {
			this._suggestInput.value = suggestionStr;
		}
	},
	
	SelectLine: function (obj)
	{
		this.SetSuggestions(obj.ContactGroup.ReplaceText) ;
		this._selectHandler.call(obj);
	},
	
	PickLine: function (posInt)
	{
		if (this._pickPos != -1) {
			this._lines[this._pickPos].className = '';
		}
		this._pickPos = posInt;
		if (this._pickPos != -1) {
			this._lines[this._pickPos].className = 'wm_auto_filling_chosen';
		}
	},
	
	Build: function ()
	{
		this._popup = CreateChild(document.body, 'div');
		this._popup.style.position = 'absolute';
		this.Hide();
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}