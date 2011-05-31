/*
 * Classes:
 *  CError
 *  CReport
 *  ReportPrototype
 *  CInformation
 *  CFadeEffect
 * Objects:
 *  MsgBox
 *  Tip
 *	PopupDiv
 * Functions:
 *  PopUpWindow
 *  CreateChild
 *  GetWidth
 *  GetHeight
 *  GetBounds
 *  OnlineMsgError
 *  OnlineMsgInfo
 *  SwitchItem
 *  SelectListAll
 *  DeleteSelectedFromList
 *  AddValueToList
 *  SetDisabled
 *  AjaxSwitchMode
 */

var ID_PREFIX = '';
String.prototype.trim = function () {
	if (this != null) return this.replace(/^\s+/, '').replace(/\s+$/, '');
	return this;
};

function PopUpWindow(url) {
	var shown = window.open(url, 'Popup',
		'left=50,top=150, toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,'+
		'copyhistory=no,width=750,height=500');
	shown.focus();
	return false;
}

function $(id) {
	var elementId = '';
	if (typeof(id) == 'string') elementId = id;
	if (typeof(ID_PREFIX) == 'string') elementId = ID_PREFIX + elementId;
	if (elementId == '') {
		MsgBox.Show('Error on client side: Incorrect element ID - "' + elementId + '".', 2);
		return null;
	}
	var element = document.getElementById(elementId);
	if (!element) {
		MsgBox.Show('Error on client side: No element with ID "' + elementId + '".', 2);
		return null;
	}
	return element;
}

function CreateChild(parent, tagName) {
	var node = document.createElement(tagName);
	parent.appendChild(node);
	return node;
}

function GetWidth() {
	var width = 1024;
	if (document.documentElement && document.documentElement.clientWidth)
		width = document.documentElement.clientWidth;
	else if (document.body.clientWidth)
		width = document.body.clientWidth;
	else if (self.innerWidth)
		width = self.innerWidth;
	return width;
}

function GetHeight() {
	var height = 768;
	if (self.innerHeight)
		height = self.innerHeight;
	else if (document.documentElement && document.documentElement.clientHeight)
		height = document.documentElement.clientHeight;
	else if (document.body.clientHeight)
		height = document.body.clientHeight;
	return height;
}

function GetBounds(object) {
	if (object == null) return {Left: 0, Top: 0, Width: 0, Height: 0};
	var left = object.offsetLeft;
	var top = object.offsetTop;
	for (var parent = object.offsetParent; parent; parent = parent.offsetParent) {
		left += parent.offsetLeft;
		top += parent.offsetTop;
	};
	return {Left: left, Top: top, Width: object.offsetWidth, Height: object.offsetHeight};
}

function CreateChildWithAttrs(parent, tagName, arAttrs)
{
	var i, t, key, val, node, attrsLen, strAttrs;
	if (Browser.IE) {
		strAttrs = '';
		attrsLen = arAttrs.length;
		for (i = attrsLen - 1; i >= 0; i--) {
			t = arAttrs[i];
			key = t[0];
			val = t[1];
			strAttrs += ' ' + key + '="' + val + '"';
		}
		tagName = '<' + tagName + strAttrs + '>';
		node = document.createElement(tagName);
	} else {
		node = document.createElement(tagName);
		attrsLen = arAttrs.length;
		for (i = attrsLen - 1; i >= 0; i--) {
			t = arAttrs[i];
			key = t[0];
			val = t[1];
			node.setAttribute(key, val);
		}
	}
	parent.appendChild(node);
	return node;
}

function CError(name)
{
	this._name = name;
	this._containerObj = null;
	this._messageObj = null;
	this._controlObj = null;
	this._fadeObj = null;
	this._delay = 10000;

	this.Build = function ()
	{
		var tbl, tr, td, div, shadowDiv, aDiv, infoDiv, bDiv, imageDiv, closeImageDiv, obj;
		tbl = CreateChildWithAttrs(document.body, 'table', [['class', 'wm_hide']]);
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		div = CreateChildWithAttrs(td, 'div', [['class', 'wm_info_block']]);
		shadowDiv = CreateChildWithAttrs(div, 'div', [['class', 'wm_shadow']]);
		aDiv = CreateChildWithAttrs(shadowDiv, 'div', [['class', 'a']]);
		aDiv.innerHTML = '&nbsp;';
		infoDiv = CreateChildWithAttrs(div, 'div', [['class', 'wm_info_message']]);
		aDiv = CreateChildWithAttrs(div, 'div', [['class', 'a']]);
		aDiv.innerHTML = '&nbsp;';
		bDiv = CreateChildWithAttrs(div, 'div', [['class', 'b']]);
		bDiv.innerHTML = '&nbsp;';
		this._containerObj = tbl;
		imageDiv = CreateChildWithAttrs(infoDiv, 'div', [['class', 'wm_info_image']]);
		this._messageObj = CreateChild(infoDiv, 'span');
		closeImageDiv = CreateChildWithAttrs(infoDiv, 'div', [['class', 'wm_close_info_image wm_control']]);
		obj = this;
		closeImageDiv.onclick = function () {
			obj.Hide();
		};
		this._controlObj = new CInformation(tbl, 'wm_information wm_error_information');
	};
}

function CReport(name)
{
	this._name = name;
	this._containerObj = null;
	this._messageObj = null;
	this._controlObj = null;
	this._fadeObj = null;
	this._delay = 5000;

	this.Build = function ()
	{
		var tbl, tr, td, div, shadowDiv, aDiv, infoDiv, bDiv;
		tbl = CreateChildWithAttrs(document.body, 'table', [['class', 'wm_hide']]);
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		div = CreateChildWithAttrs(td, 'div', [['class', 'wm_info_block']]);
		shadowDiv = CreateChildWithAttrs(div, 'div', [['class', 'wm_shadow']]);
		aDiv = CreateChildWithAttrs(shadowDiv, 'div', [['class', 'a']]);
		aDiv.innerHTML = '&nbsp;';
		infoDiv = CreateChildWithAttrs(div, 'div', [['class', 'wm_info_message']]);
		aDiv = CreateChildWithAttrs(div, 'div', [['class', 'a']]);
		aDiv.innerHTML = '&nbsp;';
		bDiv = CreateChildWithAttrs(div, 'div', [['class', 'b']]);
		bDiv.innerHTML = '&nbsp;';
		this._containerObj = tbl;
		this._messageObj = infoDiv;
		this._controlObj = new CInformation(tbl, 'wm_information wm_report_information');
	};
}

function CInfo(name) {
	this._name = name;
	this._containerObj = null;
	this._messageObj = null;
	this._controlObj = null;
	this._fadeObj = null;
	this._delay = 5000;

	this.Build = function () {
		var tbl, tr, td, div, shadowDiv, aDiv, infoDiv, bDiv;
		tbl = CreateChildWithAttrs(document.body, 'table', [['class', 'wm_hide']]);
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		div = CreateChildWithAttrs(td, 'div', [['class', 'wm_info_block']]);
		shadowDiv = CreateChildWithAttrs(div, 'div', [['class', 'wm_shadow']]);
		aDiv = CreateChildWithAttrs(shadowDiv, 'div', [['class', 'a']]);
		aDiv.innerHTML = '&nbsp;';
		infoDiv = CreateChildWithAttrs(div, 'div', [['class', 'wm_info_message']]);
		aDiv = CreateChildWithAttrs(div, 'div', [['class', 'a']]);
		aDiv.innerHTML = '&nbsp;';
		bDiv = CreateChildWithAttrs(div, 'div', [['class', 'b']]);
		bDiv.innerHTML = '&nbsp;';
		this._containerObj = tbl;
		this._messageObj = infoDiv;
		this._controlObj = new CInformation(tbl, 'wm_information wm_status_information');
	};
}

var ReportPrototype = {
	Show: function (msg, priorDelay) {
		this._messageObj.innerHTML = msg;
		this._controlObj.Show();
		this._controlObj.Resize();
		if (null != this._fadeObj) {
			var interval = (priorDelay)
				? this._fadeObj.Go(this._containerObj, priorDelay)
				: this._fadeObj.Go(this._containerObj, this._delay);
			
			if (this._name) setTimeout(this._name + '.Hide()', interval);
		} else {
			if (this._name) {
				if (priorDelay){
					setTimeout(this._name + '.Hide()', priorDelay);
				} else {
					setTimeout(this._name + '.Hide()', this._delay);
				}
			}
		}
	},
	
	SetFade: function (fadeObj) {
		this._fadeObj = fadeObj;
	},
	
	Hide: function () {
		this._controlObj.Hide();
		if (null != this._fadeObj) this._fadeObj.SetOpacity(1, IsGoodIE());
	},
	
	Resize: function () {
		this._controlObj.Resize();		
	}
};

CInfo.prototype = ReportPrototype;
CReport.prototype = ReportPrototype;
CError.prototype = ReportPrototype;

/* for control placement and displaying of information block */
function CInformation(cont, cls) {
	this._mainContainer = cont;
	this._containerClass = cls;
}

CInformation.prototype = {
	Show: function () {
		this._mainContainer.className = this._containerClass;
	},
	
	Hide: function () {
		this._mainContainer.className = 'wm_hide';
	},

	Resize: function () {
		var tbl = this._mainContainer;
		tbl.style.width = 'auto';
		var offsetWidth = tbl.offsetWidth;
		var width = GetWidth();

		var tblLeft = Math.round(width / 2 - offsetWidth / 2);
		tbl.style.left =  tblLeft + 'px';
		tbl.style.top = this.GetScrollY() + 'px';
	},

	GetScrollY: function() {
		var scrollY = 0;
		if (document.body && typeof document.body.scrollTop != "undefined") {
			scrollY += document.body.scrollTop;
			if (scrollY == 0 && document.body.parentNode && typeof document.body.parentNode != "undefined") {
				scrollY += document.body.parentNode.scrollTop;
			}
		}
		else if (typeof window.pageXOffset != "undefined")  {
			scrollY += window.pageYOffset;
		};
		return scrollY;
	}
};

function CFadeEffect(name) {
	this._name = name;
	this._elem = null;
}

CFadeEffect.prototype = {
	Go: function (elem, delay) {
		this._elem = elem;
		var interval = 50;
		var iCount = 10;
		var diff = 1/iCount;
		var isIE = IsGoodIE() ? 'true' : 'false';
		for(var i=0; i<=iCount; i++) {
			setTimeout(this._name + '.SetOpacity('+ (1 - diff*i) + ', ' + isIE + ')', delay + interval*i);
		};
		return delay + interval*iCount;
	},
	
	SetOpacity: function (opacity, isIE) {
		var elem = this._elem;
		/* Internet Exploder 5.5+ */
		if (isIE)
		{
			opacity *= 100;
			var oAlpha = elem.filters['DXImageTransform.Microsoft.alpha'] || elem.filters.alpha;
			if (oAlpha) {
				oAlpha.opacity = opacity;
			} else {
				elem.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity="+opacity+")";
			}
		} else {
			elem.style.opacity = opacity;		/* CSS3 compliant (Moz 1.7+, Safari 1.2+, Opera 9) */
			elem.style.MozOpacity = opacity;	/* Mozilla 1.6-, Firefox 0.8 */
			elem.style.KhtmlOpacity = opacity;	/* Konqueror 3.1, Safari 1.1 */
		}
	}
};

var MsgBox = {
	_fadeObj: null,
	_errorObj: null,
	_reportObj: null,
	_infoObj: null,
	_skin: 'adminpanel',
	
	Init: function () {
		this._skin = _apPath;
		if (this._fadeObj == null) this._fadeObj = new CFadeEffect('MsgBox._fadeObj');
		if (this._errorObj == null) {
			this._errorObj = new CError('MsgBox._errorObj');
			this._errorObj.Build(this._skin);
			this._errorObj.SetFade(this._fadeObj);
		}
		if (this._reportObj == null) {
			this._reportObj = new CReport('MsgBox._reportObj');
			this._reportObj.Build();
			this._reportObj.SetFade(this._fadeObj);
		}
		if (this._infoObj == null) {
			this._infoObj = new CInfo('MsgBox._infoObj');
			this._infoObj.Build();
			this._infoObj.SetFade(this._fadeObj);
		}
	},
	
	/*
	 * type = 0 - info
	 * type = 1 - report
	 * type = 2 - error
	 */
	Show: function (msg, type, delay) {
		this.Init();
		if (!type) type = 0;
		switch (type) {
			case 0:this._infoObj.Show(msg, delay);break;
			case 1:this._reportObj.Show(msg, delay);break;
			case 2:this._errorObj.Show(msg, delay);break;
		}
	}
};

function OnlineMsgError(skin, text) {
	MsgBox.Show(text, 2);
}

function OnlineMsgInfo(text) { 
	MsgBox.Show(text, 1);
}

function OnlineLoadInfo(text) {
	MsgBox.Show(text, 0, 20000);
}

var Tip = {
	_container: null,
	_message: null,
	_base: '',
	_initialized: false,
	
	_init: function () {
		if (this._initialized) return;
		this._container = CreateChild(document.body, 'table');
		this._container.className = 'wm_hide';
		var tr = this._container.insertRow(0);
		var td = tr.insertCell(0);
		td.className = 'wm_tip_arrow';
		this._message = tr.insertCell(1);
		this._message.className = 'wm_tip_info';
		this._initialized = true;
	},

	SetMessageText: function(text) {
		this._message.innerHTML = text;
	},

	SetCoord: function(element) {
		var bounds = GetBounds(element);
		this._container.style.top = (bounds.Top + bounds.Height/2 - 16) + 'px';
		this._container.style.left = (bounds.Left + bounds.Width - 5) + 'px';
	},

	Show: function(text, element, base) {
		this._init();
		this.SetMessageText(text);
		this.SetCoord(element);
		this._base = base;
		this._container.className = 'wm_tip';
	},

	Hide: function(base) {
		this._init();
		if (this._base == base || this._base == '') this._container.className = 'wm_hide';
	}
};

var PopupDiv = {
	_container: null,
	_initialized: false,

	_init: function () {
		if (this._initialized) return;
		this._container = $('popup_restart_help');
		if (this._container) this._container.className = 'wm_hide';
		this._initialized = true;
	},

	SetCoord: function() {
		if (this._container) {
			var height = GetHeight();
			var width = GetWidth();

			height = (height/2 - 400/2);
			width = (width/2 - 420/2);
			height = (height < 50) ? 50 : Math.round(height); 
			width = (width < 50) ? 50 :  Math.round(width);;

			this._container.style.top = height + 'px';
			this._container.style.left = width + 'px';
		}
	},

	Show: function() {
		this._init();
		this.SetCoord();
		if (this._container) this._container.className = 'wm_restart_div';
	},

	Hide: function(base) {
		this._init();
		if (this._container) this._container.className = 'wm_hide';
	}
};

function SwitchItem(array, index) {
	var i, item;
	var c = array.length;
	for (i = 0; i < c; i++) {
		item = $(array[i]);
		if (item) {
			item.className = (array[i] == index) ? "" : "wm_hide";
		}
	}
	ResizeElements('All');
}

function ResizeMainError()
{
	var mainErrorObj = document.getElementById('mainIdObj');
	if (mainErrorObj) {
		mainErrorObj.style.width = 'auto';
		mainErrorObj.style.left = Math.round(
			(GetWidth() / 2) - (mainErrorObj.offsetWidth / 2)) + 'px';
	}
}

function SelectListAll(id)
{
	var list = $(id);
	if (list && list.multiple == true) {
		for (var i = 0; i < list.options.length; i++) {
			list.options[i].selected = true;
		}
	}
}

function DeleteSelectedFromList(list) {
	if (list && list.multiple == true) {
		for (i = list.options.length - 1; i >= 0 ; i--) {
			if (list.options[i].selected) list.remove(i);
		}
	}
}

function AddValueToList(input, list) {
	if (list && input) {
		var str = input.value;
		if (str && str.length > 0) {
			var op = document.createElement('option');
			op.text = str;
			op.value = str;
			/* op.style.background = "#eee"; */
			try {
				list.add(op, null); /* standards compliant */
			} catch(ex) {
				list.add(op); /* IE only */
			}
		}
		input.value = "";
		input.focus();
	}
}

function AddValueToListSpec(input, list, radio1, radio2) {
	if (list && input && radio1 && radio2) {
		Tip.Hide();
		if (!IsEmailAddress(input)) {
			Tip.Show('Email address is incorrect.', input);
			return false;
		}

		var inputValue = input.value.trim();
		if (inputValue == '') {
			Tip.Show('Email address is incorrect.', input);
			return false;
		}

		var type = "RS";
		var str, value; 

		if (radio2.checked) {type = "RW";}
		
		if (type == "RS") {
			str = inputValue + " (Read)";
		} else if (type == "RW") {
			str = inputValue + " (Read/Post)";
		}

		value = type + "|" + inputValue;

		var listOptions = list.getElementsByTagName('option');
		var listItem;
		var isAdd = true;
		for (var i = 0; i < listOptions.length; i++) {
			listItem = listOptions[i];
			if (listItem.getAttribute('muser') == inputValue) {
				listItem.value = value;
				listItem.text = str;
				/* listItem.style.background = "#eee"; */
				listItem.setAttribute("mtype", type);
				isAdd = false;
			}
		}
		
		if (isAdd && str && value && str.length > 0 && value.length > 0) {
			var op = document.createElement('option');
			op.text = str;
			op.value = value;
			op.setAttribute("mtype", type);
			op.setAttribute("muser", inputValue);
			/* op.style.background = "#eee"; */
			try {
				list.add(op, null); /* standards compliant */
			} catch(ex) {
				list.add(op); /* IE only */
			}
		}
		
		input.value = "";
		input.focus();
	}
}

function SetDisabled(obj, isDisabled, withLabel) {
	if (obj) {
		isDisabled = (typeof isDisabled == 'undefined') ? false : isDisabled;
		if (isDisabled) {
			if (!obj.type || obj.type == 'checkbox' || obj.type == 'radio' || obj.type == 'button' || obj.type == 'submit') {}
			else {
				obj.style.background = "#ddd";
			}
			obj.disabled = true;
		} else {
			obj.disabled = false;
			if (!obj.type || obj.type == 'checkbox' || obj.type == 'radio' || obj.type == 'button' || obj.type == 'submit') {}
			else {
				obj.style.background = "#fff";
			}
		}
		
		withLabel = (withLabel == undefined) ? false : withLabel;
		if (withLabel) {
			var _l = document.getElementById(obj.id + "_label"); 
			if (_l) {
				_l.style.color = (isDisabled) ? "#aaaaaa" : "#000000"; 
			}
		}
	}
}

/* check symbols */
function isEnter(ev)
{
	var key = -1;
	if (window.event)
		key = window.event.keyCode;
	else if (ev)
		key = ev.which;
	
	return (key == 13);
}

var InputSymbols = {
	_REPLACE_ACTION: 0,
	_REPORT_ACTION: 1,
	
	/*
	 * Checks input through regExp. In case of failure performs action (replace or report).
	 */
	_check: function (input, regExp, action, msg) {
		try {
			if (action == this._REPORT_ACTION) Tip.Hide('');
			var value = input.value.trim();
			if (value != '') {
				var temp_value = value.replace(regExp, '');
				if (temp_value != value) {
					switch (action) {
						case this._REPLACE_ACTION:
							input.value = temp_value;
							break;
						case this._REPORT_ACTION:
							Tip.Show(msg, input, '');
							break;
					}
					return false;
				}
			}
			return true;
		}
		catch (errorMsg) {
			ShowError(errorMsg, '003');
			return false;
		}
	},
	
	/*
	 * Replaces all the characters except 0-9.
	 */
	ReplaceNonDigits: function (input) {
		this._check(input, /[^0-9]/gi, this._REPLACE_ACTION);
	},
	
	/*
	 * Replaces all the characters except 0-9 and '.'.
	 */
	ReplaceNonFloatNumber: function (input) {
		this._check(input, /[^0-9.]/gi, this._REPLACE_ACTION);
	},
	
	/*
	 * Replaces all the characters except 0-9 and ','.
	 */
	ReplaceNonDigitsComma: function (input) {
		this._check(input, /[^0-9,]/gi, this._REPLACE_ACTION);
	},
	
	ReplaceDomainCharacters: function (input) {
		this._check(input, /[^A-Za-z0-9_.-]/gi, this._REPLACE_ACTION);
	},
	
	ReplaceUserLoginCharacters: function (input) {
		this._check(input, /[^A-Za-z0-9_.-]/gi, this._REPLACE_ACTION);
	},
	
	/*
	 * Reports about all characters except 0-9, '.' and ','.
	 */
	CheckDigitsDotComma: function (input, msg) {
		this._check(input, /[^0-9,.]/gi, this._REPORT_ACTION, msg);
	},
	
	/*
	 * Reports about all characters except A-Z, a-z, 0-9, '_', '.' and '-'.
	 */
	CheckDomainCharacters: function (input, msg) {
		return this._check(input, /[^A-Za-z0-9_.-]/gi, this._REPORT_ACTION, msg);
	},

	/*
	 * Reports about all characters except A-Z, a-z, 0-9, '_', '.', and '-'.
	 */
	CheckUserLoginCharacters: function (input, msg) {
		this._check(input, /[^A-Za-z0-9_.-]/gi, this._REPORT_ACTION, msg);
	},
		
	/*
	 * Reports about all characters except A-Z, a-z, 0-9, '_', '.', '@' and '-'.
	 */
	CheckEmailCharacters: function (input, msg) {
		this._check(input, /[^A-Za-z0-9_.@-]/gi, this._REPORT_ACTION, msg);
	},
	
	/*
	 * Reports about all characters except A-Z, a-z, 0-9 and _.~#$()=+|><,.":;'-.
	 */
	CheckUserPasswordCharacters: function (input, msg) {
		return this._check(input, /[^A-Za-z0-9_.~#$()=+|><,."":;''-]/gi, this._REPORT_ACTION, msg);
	},

	CheckCommaSeparatedIP: function (input, msg) {
		try
		{
			Tip.Hide('');
			var value = input.value.trim();
			if (value != '') {
				var arrIP = new Array();
				var i=0;
				arrIP = value.split(',');
				if (arrIP.length > 0) {
					for(i=0; i < arrIP.length; i++) {
						if(!IsIPAddress(arrIP[i])) {
							Tip.Show(msg, input, '');
							break;
						}
					}
				}
			}
		}
		catch (errorMsg) {
			ShowError(errorMsg, '007');
		}
	},
	
	CheckDomain: function (input, msg)
	{
		Tip.Hide();
		var value = input.value.trim();
		if(!DomainNameIsOk(value)) Tip.Show(msg, input, ''); 
	},
	
	CheckEmail: function (input, msg)
	{
		Tip.Hide(); 
		if(!IsEmailAddress(input)) Tip.Show('Email address is incorrect', input, ''); 
	}
};

var Validator = {
	_COMMA_SEPARATED_IP_REPORT: 'Only IP Addresses or comma separated IP Addresses allowing here.',
	_INCORRECT_CHARACTERS_DOMAIN_MESSAGE: 'Incorrect characters in the domain name.',
	_INCORRECT_DOMAIN_MESSAGE: 'Your Domain name or IP address is incorrect.',
	_INCORRECT_CHARACTERS_EMAIL_MESSAGE: 'Incorrect characters in the email address.',
	_INCORRECT_EMAIL_MESSAGE: 'Email address is incorrect.',
	_INCORRECT_ALIAS_MESSAGE: 'Alias is incorrect.',
	_INCORRECT_ADMIN_ACCOUNT_MESSAGE: 'Incorrect character for an User/Account Name.',
	_INCORRECT_USER_PASSWORD_MESSAGE: 'Incorrect characters in the password.',

	RegisterAllowNum: function (input) {
		if (null == input) return;
		input.onkeyup = function () {InputSymbols.ReplaceNonDigits(this);};
		input.onblur = function () {InputSymbols.ReplaceNonDigits(this);};
	},
	
	RegisterAllowCommaSeparatedNum: function (input) {
		if (null == input) return;
		input.onkeyup = function () {InputSymbols.ReplaceNonDigitsComma(this);};
		input.onblur = function () {InputSymbols.ReplaceNonDigitsComma(this);};
	},

	RegisterAllowFloat: function (input) {
		if (null == input) return;
		input.onkeyup = function () {InputSymbols.ReplaceNonFloatNumber(this);};
		input.onblur = function () {InputSymbols.ReplaceNonFloatNumber(this);};
	},
	
	RegisterAllowCommaSeparatedIP: function (input) {
		if (null == input) return;
		var obj = this;
		input.onkeyup = function () {
			InputSymbols.CheckDigitsDotComma(this, obj._COMMA_SEPARATED_IP_REPORT);
		};
		input.onblur = function () {
			InputSymbols.CheckCommaSeparatedIP(this, obj._COMMA_SEPARATED_IP_REPORT);
		};
	},
	
	RegisterAllowDomainSymbols: function (input) {
		if (null == input) return;
		var obj = this;
		
		input.onkeyup = function () { 
			/* InputSymbols.CheckDomainCharacters(this, obj._INCORRECT_CHARACTERS_DOMAIN_MESSAGE); */
			InputSymbols.ReplaceDomainCharacters(this); 
		};
		input.onblur = function () {
			/* InputSymbols.CheckDomain(this, obj._INCORRECT_DOMAIN_MESSAGE); */
			InputSymbols.ReplaceDomainCharacters(this);
		};
	},
	
	RegisterAllowUserLoginSymbols: function (input) {
		if (null == input) return;
		var obj = this;
		input.onkeyup = function () {
			InputSymbols.ReplaceUserLoginCharacters(this);
		};
		input.onblur = function () {
			InputSymbols.ReplaceUserLoginCharacters(this);
		};
	},

	RegisterAllowEmailSymbols: function (input) {
		if (null == input) return;
		var obj = this;
		input.onkeyup = function () {
			InputSymbols.CheckEmailCharacters(this, obj._INCORRECT_CHARACTERS_EMAIL_MESSAGE);
		};
		input.onblur = function () {
			InputSymbols.CheckEmail(this, obj._INCORRECT_EMAIL_MESSAGE);
		};
	},
	
	RegisterAllowAliasSymbols: function (input) {
		if (null == input) return;
		var obj = this;
		input.onkeyup = function () {
			InputSymbols.CheckEmailCharacters(this, obj._INCORRECT_ALIAS_MESSAGE);
		};
		input.onblur = function () {
			InputSymbols.CheckEmail(this, obj._INCORRECT_ALIAS_MESSAGE);
		};
	},
	
	RegisterAllowAdminsAccountSymbols: function (input) {
		if (null == input) return;
		var obj = this;
		input.onkeyup = function () {
			InputSymbols.CheckDomainCharacters(this, obj._INCORRECT_ADMIN_ACCOUNT_MESSAGE);
		};
		input.onblur = function () {
			InputSymbols.CheckDomainCharacters(this, obj._INCORRECT_ADMIN_ACCOUNT_MESSAGE);
		};
		input.onkeydown = function (ev) {
			if (isEnter(ev)) return false;
		};
	},
	
	RegisterAllowUserPasswordSymbols: function (input) {
		if (null == input) return;
		var obj = this;
		input.onkeyup = function () {
			InputSymbols.CheckUserPasswordCharacters(this, obj._INCORRECT_USER_PASSWORD_MESSAGE);
		};
		input.onblur = function () {
			InputSymbols.CheckUserPasswordCharacters(this, obj._INCORRECT_USER_PASSWORD_MESSAGE);
		};
		input.onkeydown = function (ev) {
			if (isEnter(ev)) return false;
		};
	}
};

function IsIPAddress(sIPValue) {
	try
	{
		if (sIPValue == '0.0.0.0' || sIPValue == '255.255.255.255') return false;
		
		var ipPattern = /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/;
		var aIpArray = sIPValue.match(ipPattern);
		if (aIpArray == null) return false;
		
		var thisSegment;
		for (var i = 1; i < 5; i++) { /* because element aIpArray[0] - is the hole ip address! */
			thisSegment = aIpArray[i];
			if (thisSegment > 255) return false;
		}
		return true;
	}
	catch (errorMsg) {
		ShowError(errorMsg, '017');
		return false;
	}
}

function IsDomainName(sDomainName) {
	try
	{
		sDomainName = '' + sDomainName;
		sDomainName = sDomainName.trim();
		if (sDomainName == '') return false;

		var arrTest = new Array();
		arrTest = sDomainName.split('.');
		if(arrTest[0] == '' || arrTest[arrTest.length - 1] == '') return false;

		var temp_value = sDomainName.replace(/[^A-Za-z0-9_.-]/gi, '');
		return (temp_value == sDomainName);
	}
	catch (errorMsg) {
		ShowError(errorMsg, '016');
		return false;
	}
}

function DomainNameIsOk(param) {
	return (IsDomainName(param) && IsIPAddress(param));
}

function IsDomainMaskOk(sDomainName) {
	try {
		sDomainName = ('' + sDomainName).trim();
		if (sDomainName == '') return false;

		var arrTest = new Array();
		arrTest = sDomainName.split('.');
		if(arrTest[0] == '' || arrTest[arrTest.length - 1] == '') return false;
		
		var temp_value = sDomainName.replace(/[^A-Za-z0-9_.*-]/gi, '');
		return (temp_value == sDomainName);
	}
	catch (errorMsg) {
		ShowError(errorMsg, '171');
		return false;
	}
}

function IsAlias(input)
{
	var aIpArray = input.value.match(/^[A-Za-z0-9_.-]+$/gi);
	if (aIpArray == null) {
		Tip.Show('Incorrect Alias in the input field', input, '');
		return false;
	}
	return true;
}

function IsEmailAddress(input)
{
	try {
		var emailStr = input.value.trim();
		if (emailStr == '') return true;
		var arrIP = emailStr.split('@');
		if (arrIP.length != 2 || arrIP[0].length == 0 || arrIP[1].length == 0) return false;

		var leftPartOk = IsDomainMaskOk(arrIP[0]);
		var rightPartOk = (arrIP.length > 2) ? false : true;
		if (rightPartOk && arrIP.length == 2) rightPartOk = IsDomainMaskOk(arrIP[1]);
		if (leftPartOk && rightPartOk) {
			/* if user type more than one */
			input.value = ConvertDataToNormalView(emailStr);
			return true;
		} else {
			Tip.Show('Incorrect characters in the input field', input, '');
			return false;
		}
	}
	catch (errorMsg) {
		ShowError(errorMsg, '018');
		return false;
	}
}

function ConvertDataToNormalView(param)
{
	try {
		var i=0;
		var result = '';
		if(param.length < 2) return param;

		for(i=1; i < param.length; i++) {
			if(param.charAt(i-1) == '*' && param.charAt(i) == '*') {
				if(result.charAt(result.length-1) != '*') result += '*';
				i++;
			} else if(param.charAt(i-1) == '*' && param.charAt(i) != '*') {
				if(result.charAt(result.length-1) != '*') result += '*';
			} else {
				result += param.charAt(i-1);
			}
		}
		if(i != 0) result += param.charAt(i-1);
		return result;
	}
	catch (errorMsg) {
		ShowError(errorMsg, '172');
		return false;
	}
}

/* tooltip */
var l = 0, t = 0;
var IE = document.all ? true : false;
var tooltip = document.createElement("div");
tooltip.id = 'tooltip';

var currentMainTab = 'ServerSettingsTabID';
var currentPanel;
var currentServerTab = "S_0";

function ShowError(errorMsg, errorCode) {
	var ErrorPrefix = "Error on client side - Code:";
	MsgBox.Show(ErrorPrefix + ' ' + errorCode + '<br/>' + errorMsg, 2);
}

function getMouseXY(e) {
	try {
		if (IE) {
			l = event.clientX + document.documentElement.scrollLeft;
			t = event.clientY + document.documentElement.scrollTop;
		} else {
			l = e.pageX;
			t = e.pageY;
		}
		tooltip.style.left = l + "px";
		tooltip.style.top = t + "px";
		return true;
	} catch(errorMsg) {
		ShowError(errorMsg, '103');
	}
}

var _seeToolTip = 0;
function AddToolTip(tooltip_text) {
	try {
		if (window.event) getMouseXY(window.event);
		document.onmousemove = getMouseXY;
		document.body.appendChild(tooltip);
		tooltip.innerHTML = tooltip_text;
		_seeToolTip++;
	} catch(errorMsg) {
		ShowError(errorMsg, '104');
	}
}

function RemoveToolTip() {
	try {
		document.onmousemove = '';
		if (tooltip && _seeToolTip > 0) {
			_seeToolTip--;
			document.body.removeChild(tooltip);
		}
	} catch(errorMsg) {
		ShowError(errorMsg, '105');
	}
}

function mainTabSwitch(id) {
	try {
		if (id == 'mainTab1') {
			document.getElementById(currentMainTab).className ='wm_accountslist_email';
			document.getElementById(id).className='wm_accountslist_email_activ';
			document.getElementById('ServerSettingsTabID').className='wm_settings';
			document.getElementById('DomainsAndUsersTabID').className='hide';
			viewPanel('new_domain_panel');
			currentMainTab = document.getElementById(id).id;
		} else {
			document.getElementById(currentMainTab).className ='wm_accountslist_email';
			document.getElementById(id).className='wm_accountslist_email_activ';
			document.getElementById('DomainsAndUsersTabID').className='wm_settings'; 
			document.getElementById('ServerSettingsTabID').className='hide'; 
			viewPanel('new_domain_panel');
			currentMainTab = document.getElementById(id).id;
		}
	} catch(errorMsg) {
		ShowError(errorMsg, '106');
	}
}

function toggle_sTab(x) {
	try {
		ShowServerSettingsSaveButton();
		if (x.className == 'wm_selected_settings_item') {
			x.className = 'wm_settings_item';
		} else {
			document.getElementById(currentServerTab).className = 'wm_settings_item';
			x.className = 'wm_selected_settings_item';
			currentServerTab = x.id;
		}
	} catch(errorMsg) {
		ShowError(errorMsg, '107');
	}
}

function viewPanel(Panel) {
	try {
		if (currentPanel == undefined) currentPanel = 'server_tab_1';
		document.getElementById(currentPanel).className = 'hide';
		document.getElementById(Panel).className = 'wm_admin_center';
		currentPanel = Panel;
	} catch(errorMsg) {
		ShowError(errorMsg, '108');
	}
}

function advSwitch (el) {
	try {
		if (el.parentNode.className == 'expanded') {
			el.parentNode.className = 'convoluted';
		} else {
			el.parentNode.className = 'expanded';
		}
	} catch(errorMsg) {
		ShowError(errorMsg, '109');
	}
}

function OpenNewWindowWithInnerText(obj)
{
	if (obj) {
		var text = obj.innerHTML;
		var shown = window.open('', 'Text',
'left=50,top=150, toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,'+
'copyhistory=no,width=500,height=100');
		if (shown && text) {
			shown.document.write(text); 
		}
	}
}

function IsGoodIE()
{
	if (document.body.filters) {
		var marray = navigator.appVersion.match(/MSIE ([\d.]+);/);
		if (marray && marray.length > 1 && marray[1] >= 5.5) {
			return true;
		}
	}
	return false;
}

var staticScript = null;
function AjaxSwitchMode(mode)
{
	if (null != staticScript) {
		staticScript.parentNode.removeChild(staticScript);
		staticScript = null;
	}
	staticScript = document.createElement('script');
	staticScript.setAttribute('type', 'text/javascript');
	staticScript.src = '?change_mode=' + mode + '&rnd=' + Math.random();
	document.body.appendChild(staticScript);
}

function CPopupMenu(popup_menu, popup_control, menu_class, popup_move, popup_title, move_class, move_press_class, title_class, title_over_class)
{
	this.popup = popup_menu;
	this.control = popup_control;
	this.move = popup_move;
	this.title = popup_title;
	this.menu_class = menu_class;
	this.move_class = move_class;
	this.move_press_class = move_press_class;
	this.title_class = title_class;
	this.title_over_class = title_over_class;
}

function CPopupMenus()
{
	this.items = Array();
	this.isShown = 0;
}

CPopupMenus.prototype = {
	getLength: function()
	{
		return this.items.length;
	},
	
	addItem: function(popup_menu, popup_control, menu_class, popup_move, popup_title, move_class, move_press_class, title_class, title_over_class)
	{
		this.items.push(new CPopupMenu(popup_menu, popup_control, menu_class, popup_move, popup_title, move_class, move_press_class, title_class, title_over_class));
		this.hideItem(this.getLength() - 1);
	},
	
	showItem: function(item_id)
	{
		this.hideAllItems();
		var item = this.items[item_id];
		var bounds = GetBounds(this.items[item_id].move);
		if (!window.RTL) {
    		item.popup.style.left = bounds.Left + 'px';
		}
		item.popup.style.top = bounds.Top + bounds.Height + 'px';

		item.popup.className = item.menu_class;
		if (item.title_class && item.title_class != '') {
			item.control.className = item.title_class;
			item.title.className = item.title_class;
		}
		if (item.move_press_class && item.move_press_class != '')
			item.move.className = item.move_press_class;
		var obj = this;
		item.control.onclick = function() {
			obj.hideItem(item_id);
		};
		var borders = 1;
		if (item.title_over_class != '') {
			item.control.onmouseover = function(){};
			item.control.onmouseout = function(){};
			item.title.onmouseover = function(){};
			item.title.onmouseout = function(){};
			borders = 2;
		}
		this.isShown = 2;
		item.popup.style.width = 'auto';
		var pOffsetWidth = item.popup.offsetWidth;
		var cOffsetWidth = item.control.offsetWidth;
		var tOffsetWidth = (item.control == item.title) ? 0 : item.title.offsetWidth;
		if (pOffsetWidth < (cOffsetWidth + tOffsetWidth - borders)) {
			item.popup.style.width = (cOffsetWidth + tOffsetWidth - borders) + 'px';
		}
		else {
			item.popup.style.width = (pOffsetWidth + borders) + 'px';
		}
		if (window.RTL) {
    		/* rtl */
	    	item.popup.style.left = (bounds.Left + bounds.Width - item.popup.offsetWidth) + 'px';
		}

		item.popup.style.height = 'auto';
		var pOffsetHeight = item.popup.offsetHeight;
		var height = GetHeight();
		if (pOffsetHeight > height*2/3) {
			item.popup.style.height = Math.round(height*2/3) + 'px';
			item.popup.style.overflowY = 'auto';
		}
		else {
			item.popup.style.overflowY = 'hidden';
		}
		
	},
	
	hideItem: function(item_id)
	{
		this.items[item_id].popup.className = 'wm_hide';
		if (this.items[item_id].move_class && this.items[item_id].move_class != '' && this.items[item_id].move.className != 'wm_hide')
			this.items[item_id].move.className = this.items[item_id].move_class;
		var obj = this;
		this.items[item_id].control.onclick = function() {
			obj.showItem(item_id);
		};
		if (obj.items[item_id].title_over_class != ''){
			this.items[item_id].control.onmouseover = function() {
				obj.items[item_id].title.className = obj.items[item_id].title_over_class; 
				obj.items[item_id].control.className = obj.items[item_id].title_over_class;
			};
			this.items[item_id].control.onmouseout = function() {
				obj.items[item_id].title.className = obj.items[item_id].title_class; 
				obj.items[item_id].control.className = obj.items[item_id].title_class; 
			};
			this.items[item_id].title.onmouseover = function() {
				obj.items[item_id].title.className = obj.items[item_id].title_over_class; 
			};
			this.items[item_id].title.onmouseout = function() {
				obj.items[item_id].title.className = obj.items[item_id].title_class; 
			}
		}
	},
	
	hideAllItems: function()
	{
		for (var i = this.getLength() - 1; i >= 0; i--) {
			this.hideItem(i);
		}
		this.isShown = 0;
	},
	
	checkShownItems: function()
	{
		if (this.isShown == 1) {
			this.hideAllItems()
		}
		if (this.isShown == 2) {
			this.isShown = 1;
		}
	}
};

function closeMainError(objId)
{
	var obj = document.getElementById(objId);
	if (obj) {
		obj.className = 'wm_hide';
	}
}

function SetDisabledArray(chId, IdsArray)
{
	var chObj = document.getElementById(chId);
	if (chObj) {
		for (var aId in IdsArray)
		{
			SetDisabled(document.getElementById(IdsArray[aId]), !chObj.checked, true);
		}
	}
}