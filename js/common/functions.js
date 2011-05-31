function ReadStyle(element, property)
{
	if (element.style[property]) {
		return element.style[property];
	} else if (element.currentStyle) {
		return element.currentStyle[property];
	} else if (document.defaultView && document.defaultView.getComputedStyle) {
		var style = document.defaultView.getComputedStyle(element, null);
		return style.getPropertyValue(property);
	}
	return null;
}

function GetBorderWidth(style, width)
{
	if (style == 'none') {
		return 0;
	}
	else {
		return ParseStyleWidth(width);
	}
}

function ParseStyleWidth(width)
{
	var floatWidth = parseFloat(width);
	return (isNaN(floatWidth)) ? 0 : Math.round(floatWidth);
}

function GetBorders(element)
{
	var top, right, bottom, left;
	if (Browser.Mozilla) {
		top = GetBorderWidth(ReadStyle(element, 'border-top-style'), ReadStyle(element, 'border-top-width'));
		right = GetBorderWidth(ReadStyle(element, 'border-right-style'), ReadStyle(element, 'border-right-width'));
		bottom = GetBorderWidth(ReadStyle(element, 'border-bottom-style'), ReadStyle(element, 'border-bottom-width'));
		left = GetBorderWidth(ReadStyle(element, 'border-left-style'), ReadStyle(element, 'border-left-width'));
	}
	else {
		top = GetBorderWidth(ReadStyle(element, 'borderTopStyle'), ReadStyle(element, 'borderTopWidth'));
		right = GetBorderWidth(ReadStyle(element, 'borderRightStyle'), ReadStyle(element, 'borderRightWidth'));
		bottom = GetBorderWidth(ReadStyle(element, 'borderBottomStyle'), ReadStyle(element, 'borderBottomWidth'));
		left = GetBorderWidth(ReadStyle(element, 'borderLeftStyle'), ReadStyle(element, 'borderLeftWidth'));
	}
	return { Top: top, Right: right, Bottom: bottom, Left: left };
}

function GetHeightStyle(element)
{
	return GetBorderWidth(ReadStyle(element, 'display'), ReadStyle(element, 'height'));
}

function GetPaddings(element)
{
	return GetStyleWidth(element, 'padding');
}

function GetMargins(element)
{
	return GetStyleWidth(element, 'margin');
}

function GetStyleWidth(element, style)
{
	var top, right, bottom, left;
	if (Browser.Mozilla) {
		top = ParseStyleWidth(ReadStyle(element, style + '-top'));
		right = ParseStyleWidth(ReadStyle(element, style + '-right'));
		bottom = ParseStyleWidth(ReadStyle(element, style + '-bottom'));
		left = ParseStyleWidth(ReadStyle(element, style + '-left'));
	}
	else {
		top = ParseStyleWidth(ReadStyle(element, style + 'Top'));
		right = ParseStyleWidth(ReadStyle(element, style + 'Right'));
		bottom = ParseStyleWidth(ReadStyle(element, style + 'Bottom'));
		left = ParseStyleWidth(ReadStyle(element, style + 'Left'));
	}
	return { Top: top, Right: right, Bottom: bottom, Left: left };
}

function GetMarginLeft(element)
{
	var mLeft = (Browser.Mozilla || Browser.Safari) ? ReadStyle(element, 'margin-left') : ReadStyle(element, 'marginLeft');
	if (mLeft != null) {
		return mLeft.replace(/px/, '') - 0;
	}
	return Number.NaN;
}

function GetMarginRight(element)
{
	var mRight = (Browser.Mozilla || Browser.Safari) ?
		ReadStyle(element, 'margin-right') : ReadStyle(element, 'marginRight');
	if (mRight != null) {
		return mRight.replace(/px/, '') - 0;
	}
	return Number.NaN;
}

function Trim(str) {
    return str.replace(/^\s+/, '').replace(/\s+$/, '');
}

//email parts for adding to contacts
function GetEmailParts(fullEmail)
{
	var quote1, quote2, leftBrocket, prevLeftBroket, rightBrocket, name, email;
	quote1 = fullEmail.indexOf('"');
	quote2 = fullEmail.indexOf('"', quote1 + 1);
	leftBrocket = fullEmail.indexOf('<', quote2);
	prevLeftBroket = -1;
	while (leftBrocket != -1) {
		prevLeftBroket = leftBrocket;
		leftBrocket = fullEmail.indexOf('<', leftBrocket + 1);
	}
	leftBrocket = prevLeftBroket;
	rightBrocket = fullEmail.indexOf('>', leftBrocket + 1);
	name = email = '';
	if (leftBrocket == -1) {
		email = Trim(fullEmail);
	} else {
		name = (quote1 == -1) ?
			Trim(fullEmail.substring(0, leftBrocket)) :
			Trim(fullEmail.substring(quote1 + 1, quote2));
			
		email = Trim(fullEmail.substring(leftBrocket + 1, rightBrocket));
	}
	return {Name: name, Email: email, FullEmail: fullEmail};
}

function PopupWindow(wUrl, wName, wWidth, wHeight, toolbar)
{
	var toolbarVar, wLeft, wTop, wArgs, shown;
	toolbarVar = (toolbar) ? 'yes' : 'no';
	wTop = (window.screen) ? (screen.height - wHeight) / 2 : 200;
	wLeft = (window.screen) ? (screen.width - wWidth) / 2 : 200;
	wArgs = 'toolbar=' + toolbarVar + ',location=no,directories=no,copyhistory=no,';
	wArgs += 'status=yes,scrollbars=yes,resizable=yes,';
	wArgs += 'width=' + wWidth + ',height=' + wHeight + ',left=' + wLeft + ',top=' + wTop;
	shown = window.open(wUrl, wName, wArgs);
	shown.focus();
}

function PopupPrintMessage(url)
{
	PopupWindow(url, 'PopupPrintMessage', 640, 480, true);
	return false;
}

function PopupContacts(wUrl)
{
	PopupWindow(wUrl, 'PopupContacts', 300, 400);
	return false;
}

function SetBodyAutoOverflow(isAuto)
{
	var OverFlow, Scroll;
	OverFlow = 'hidden';
	Scroll = 'no';
	if (isAuto) {
		OverFlow = 'auto';
		Scroll = 'yes';
	}
	if (Browser.IE) {
		WebMail._html.style.overflow = OverFlow;
	}
	else {
		document.body.scroll = Scroll;
		document.body.style.overflow = OverFlow;
	}
}

function OpenURL(strUrl)
{
	var strUrl = Validator.CorrectWebPage(Trim(strUrl));
	if (strUrl.length > 0) {
		var strProt = strUrl.substr(0, 4);
		if (strProt != "http" && strProt != "ftp:") {
			strUrl = "http://" + strUrl;
		}
		var newWin = window.open(encodeURI(strUrl), null, "toolbar=yes,location=yes,directories=yes,status=yes,scrollbars=yes,resizable=yes,copyhistory=yes");
		newWin.focus();
	}
}

function EncodeStringForEval(source)
{
	return source.replace(/\\/g, '\\\\').replace(/'/g, '\\\'').replace(/"/g, '\\"');
}

function HtmlEncode(source)
{
	return source.replace(/&/g, '&amp;').replace(/>/g, '&gt;').replace(/</g, '&lt;');
}

function HtmlDecode(source)
{
	return source.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&');
}

function HtmlEncodeBody(source)
{
	return source.replace(/]]>/g, '&#93;&#93;&gt;');
}

function HtmlDecodeBody(source)
{
	return source.replace(/&#93;&#93;&gt;/g, ']]>');
}

function GetCData(source, isBody)
{
	if (isBody) {
		return '<![CDATA[' + HtmlEncodeBody(source) + ']]>';
	}
	else {
		return '<![CDATA[' + HtmlEncode(source) + ']]>';
	}
}

function isEnter(ev)
{
	var key = -1;
	if (window.event) {
		key = window.event.keyCode;
	} else if (ev) {
		key = ev.which;
	}
	return (key == 13);
}

function TextAreaLimit(ev, obj, count)
{
	ev = ev ? ev : window.event;
	var key = -1;
	if (window.event) {
		key = window.event.keyCode;
	} else if (ev) {
		key = ev.which;
	}
	switch (key) {
	case 8:		//backspace
	case 13:	//enter
	case 16:	//shift
	case 17:	//ctrl
	case 18:	//alt
	case 35:	//end
	case 36:	//home
	case 37:	//to the right
	case 38:	//up
	case 39:	//to the left
	case 40:	//down
	case 46:	//delete
		break;
	default:
		if (!ev.ctrlKey && !ev.shiftKey) {
			if (obj.value.length >= count) {
				return false;
			}
		}
		break;
	}
	return true;
}

function isRightClick(ev)
{
	var key = -1;
	if (window.event) {
		key = window.event.button;
	} else if (ev) {
		key = ev.which;
	}
	return (key == 3 || key == 2);
}

function GetWidth()
{
	var width = 1024;
	if (document.documentElement && document.documentElement.clientWidth) {
		width = document.documentElement.clientWidth;
	} else if (document.body.clientWidth) {
		width = document.body.clientWidth;
	} else if (self.innerWidth) {
		width = self.innerWidth;
	}
	return width;
}

function GetHeight()
{
	var height = 768;
	if (self.innerHeight) {
		height = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) {
		height = document.documentElement.clientHeight;
	} else if (document.body.clientHeight) {
		height = document.body.clientHeight;
	}
	return height;
}

function CreateChild(parent, tagName, arAttrs)
{
	if (arAttrs != undefined) return CreateChildWithAttrs(parent, tagName, arAttrs);
	var node = document.createElement(tagName);
	parent.appendChild(node);
	return node;
}

function CreateTextChild(parent, text)
{
	var node = document.createTextNode(text);
	parent.appendChild(node);
	return node;
}

function CreateChildWithAttrs(parent, tagName, arAttrs)
{
	var node;
	if (Browser.IE) {
		var strAttrs = '';
		var attrsLen = arAttrs.length;
		for (var i = attrsLen - 1; i >= 0; i--) {
			var t = arAttrs[i];
			var key = t[0];
			var val = t[1];
			strAttrs += ' ' + key + '="' + val + '"';
		}
		tagName = '<' + tagName + strAttrs + '>';
		node = document.createElement(tagName);
	}
	else {
		node = document.createElement(tagName);
		var attrsLen = arAttrs.length;
		for (var i = attrsLen - 1; i >= 0; i--) {
			var t = arAttrs[i];
			var key = t[0];
			var val = t[1];
			node.setAttribute(key, val);
		}
	}
	parent.appendChild(node);
	return node;
}

function MakeOpaqueOnSelect(element)
{
	if (Browser.IE && Browser.Version < 7) {
		CreateChild(element, 'iframe',
			[
				['src', EmptyHtmlUrl],
				['scrolling', 'no'],
				['frameborder', '0'],
				['class', 'wm_for_ie_select']
			]
		);
	}
}

function GetBounds(object)
{
	if (object == null) {
		return {Left: 0, Top: 0, Width: 0, Height: 0};
	}
	var left, top, parent;
	left = object.offsetLeft;
	top = object.offsetTop;
	for (parent = object.offsetParent; parent; parent = parent.offsetParent) {
		left += parent.offsetLeft;
		top += parent.offsetTop;
	}
	return {Left: left, Top: top, Width: object.offsetWidth, Height: object.offsetHeight};
}

function GetScrollY(object)
{
	if (object == null) {
		return 0;
	}
    var scrollY = 0;
    if (object && typeof(object.scrollTop) != 'undefined') {
	    scrollY += object.scrollTop;
	    if (scrollY == 0 && object.parentNode && typeof(object.parentNode) != 'undefined') {
		    scrollY += object.parentNode.scrollTop;
	    }
    } else if (typeof object.pageXOffset != 'undefined') {
	    scrollY += object.pageYOffset;
    }
	return scrollY;
}

function CleanNode(object)
{
	while (object.firstChild) {
		object.removeChild(object.firstChild);
	}
}

function GetAppPath()
{
	var path = location.pathname;
	var dotIndex = path.lastIndexOf('.');
	var delimIndex = path.lastIndexOf('/');
	if (delimIndex < dotIndex || delimIndex == path.length - 1) {
		path = path.substring(0, delimIndex);
	};
	if (path.length == 0) {
		path = '/';
	} else if (path.substr(path.length - 1, 1) != '/') {
		path += '/';
	};
	return path;
}

function CreateCookie(name, value, days) {
	var expires = '';
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		expires = '; expires=' + date.toGMTString();
	}
	var path = '; path=' + GetAppPath();
	document.cookie = name + '=' + value + expires + path;
}

function ReadCookie(name) {
	var nameEQ = name + '=';
	var ca = document.cookie.split(';');
	for(var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1, c.length);
		}
		if (c.indexOf(nameEQ) == 0) {
			return c.substring(nameEQ.length, c.length);
		}
	};
	return null;
}

function EraseCookie(name) {
	CreateCookie(name, '', -1);
}

function HighlightMessageLine(source)
{
	return '<font>' + source + '</font>';
}

function HighlightContactLine(source)
{
	return '<b>' + source + '</b>';
}

function isEqualArray(arr1, arr2)
{
    if (!(arr1 instanceof Array) || !(arr2 instanceof Array)) {
    	return false;
    }
    if (arr1.length != arr2.length) {
    	return false;
    }
    for (var i = 0; i < arr1.length; i++) {
        if (arr1[i] != arr2[i]) {
        	return false;
        }
    }
    return true;
}

String.prototype.PrepareForRegExp = function ()
{
	var search = this.replace(/\\/g, '\\\\').replace(/\^/g, '\\^').replace(/\$/g, '\\$');
	search = search.replace(/\./g, '\\.').replace(/\*/g, '\\*').replace(/\+/g, '\\+');
	search = search.replace(/\?/g, '\\?').replace(/\|/g, '\\|').replace(/\(/g, '\\(');
	search = search.replace(/\)/g, '\\)').replace(/\[/g, '\\[');
	return search;
};

String.prototype.ReplaceStr = function (search, replacement)
{
	return this.replace(new RegExp (search.PrepareForRegExp(), 'gi'), replacement);
};

function GetBirthDay(d, m, y)
{
	var res = '';
	if (y != 0) {
		res += y;
		if (d != 0 || m != 0) res += ',';
	}
	if (d != 0) {
		res += ' ' + d;
	}
	switch (m) {
		case 1: res += ' ' + Lang.ShortMonthJanuary; break;
		case 2: res += ' ' + Lang.ShortMonthFebruary; break;
		case 3:	res += ' ' + Lang.ShortMonthMarch; break;
		case 4:	res += ' ' + Lang.ShortMonthApril; break;
		case 5:	res += ' ' + Lang.ShortMonthMay; break;
		case 6:	res += ' ' + Lang.ShortMonthJune; break;
		case 7:	res += ' ' + Lang.ShortMonthJuly; break;
		case 8:	res += ' ' + Lang.ShortMonthAugust;	break;
		case 9: res += ' ' + Lang.ShortMonthSeptember; break;
		case 10: res += ' ' + Lang.ShortMonthOctober; break;
		case 11: res += ' ' + Lang.ShortMonthNovember; break;
		case 12: res += ' ' + Lang.ShortMonthDecember; break;
	}
	return res;
}
	
function GetFriendlySize(byteSize)
{
	var size, mbSize;
	size = Math.ceil(byteSize / 1024);
	mbSize = size / 1024;
	return (mbSize > 1) ? Math.ceil(mbSize * 10) / 10 + Lang.Mb : size + Lang.Kb;
}

function GetExtension(fileName)
{
	var ext, dotPos;
	ext = '';
	dotPos = fileName.lastIndexOf('.');
	if (dotPos > -1) {
		ext = fileName.substr(dotPos + 1).toLowerCase();
	}
	return ext;
}

function GetFileParams(fileName)
{
	var ext = GetExtension(fileName);
	switch (ext) {
	case 'asp':
	case 'asa':
	case 'inc':
		return {x: 12*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: false};
		break;
	case 'css':
		return {x: 11*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: false};
		break;
	case 'docx':
	case 'doc':
		return {x: 10*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: false};
		break;
	case 'html':
	case 'shtml':
	case 'phtml':
	case 'htm':
		return {x: 9*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: false};
		break;
	case 'pdf':
		return {x: 8*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: false};
		break;
	case 'xlsx':
	case 'xls':
		return {x: 7*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: false};
		break;
	case 'bat':
	case 'exe':
	case 'com':
		return {x: 5*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: false};
		break;
	case 'bmp':
		return {x: 4*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: true};
		break;
	case 'gif':
		return {x: 3*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: true};
		break;
	case 'png':
	case 'jpg':
	case 'jpeg':
		return {x: 2*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: true};
		break;
	case 'tiff':
	case 'tif':
		return {x: 1*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: false};
		break;
	case 'txt':
		return {x: 0*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: false};
		break;
	case 'eml':
		return {x: 6*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: true};
		break;
	default:
		return {x: 6*X_ICON_SHIFT, y: 2*Y_ICON_SHIFT, view: false};
		break;
	}
}

// Neox added 2009.02.10 !-->
function $addHandler(object, event, handler)
{
	if (object && event && handler)
	{
		if (typeof object.addEventListener != 'undefined')
		{
			if (event == 'mousewheel')
			{
				event = 'DOMMouseScroll';
			}
			object.addEventListener(event, handler, false);
		} 
		else if (typeof object.attachEvent != 'undefined')
		{
			object.attachEvent('on' + event, handler);
		}
		return true;
	}
	return false;
}

function $removeHandler(object, event, handler)
{
	if (object && event && handler)
	{
		if (typeof object.removeEventListener != 'undefined')
		{
			if (event == 'mousewheel')
			{
				event = 'DOMMouseScroll';
			}
			object.removeEventListener(event, handler, false);
			return true;
		} 
		else if (typeof object.detachEvent != 'undefined')
		{
			object.detachEvent('on' + event, handler);
			return true;
		}
	}
	return false;
}

function $removeHandlers(object, event)
{
	if (!object)
	{
		throw Error("Object must be specified");
	}
	if (object._events)
	{
		var cache = {};
		if (event)
		{
			cache[event] = object._events[event];
		} 
		else
		{
			cache = object._events;
		}
		var cacheEvent, events, handler, len, i;
		for (cacheEvent in cache)
		{
			events = cache[cacheEvent];
			len = events.length;
			for (i = 0; i < len; i++)
			{
				handler = events[i].handler;
				$removeHandler(object, event, handler);
			}
		}
	}
}

function $isHandler(object, event, handler) {
	if (!object) {
		throw Error("Object must be specified");
	}
	if (!event) {
		throw Error("Event must be specified");
	}
	if (!handler) {
		throw Error("Handler must be specified");
	}
	if (object._events && object._events[event]) {
		var events, len, i;
		events = object._events[event];
		len = events.length;
		for (i = 0; i < len; i++) {
			if (events[i].handler == handler) {
				return true;
			}
		}
	}
	return false;
}

function $createDelegate(instance, method) {
	if (!instance) {
		throw Error("Instance must be specified");
	}
	if (!method) {
		throw Error("Method must be specified");
	}
	return function() {
		return method.apply(instance, arguments);
	};
}

function $createCallback(instance, method, context) {
	if (!instance) {
		instance = window;
	}
	if (!method) {
		throw Error("Method must be specified");
	}
	return function() {
		var l, i, args;
		l = arguments.length;
		if (l > 0) {
			args = [];
			for (i = 0; i < l; i++) {
				args[i] = arguments[i];
			}
			args[l] = context;
			return method.apply(instance, args);
		}
		return method.call(instance, context);
	};
}

function IsRtlLanguage(langName) {
	return (langName == 'Hebrew' || langName == 'Arabic');
}

function GetStyleValue(e,type1,type2) {
    if (!type2) {
		type2 = type1;
	}
    if (e.currentStyle&&e.currentStyle[type2] != '') {
		return e.currentStyle[type2];
	} else if (window.getComputedStyle(e, '').getPropertyValue(type1) != '') {
		return window.getComputedStyle(e, '').getPropertyValue(type1);
	}
	return false;
}

function IsNum(value) {
	var reg = /^[0-9]+$/;
	return reg.test(value);
}

function ParseGetParams()
{
	var getRequestParams, paramsArray, keyValueArray, getRequest, i;
    getRequestParams = paramsArray = keyValueArray = []; 
    getRequest = location.search;
    if (getRequest != '') {
    	paramsArray = (getRequest.substr(1)).split('&');
    	for (i = 0; i < paramsArray.length; i++) {
    		keyValueArray = paramsArray[i].split('=');
    		getRequestParams[keyValueArray[0]] = keyValueArray[1];
    	}
    }
    return getRequestParams;
}

function checkLinkHref(href)
{
	if (href.substring(0, 7).toLowerCase() == 'mailto:') {
		var emailTo, questionPos;
		emailTo = href.substring(7);
		questionPos = emailTo.indexOf('?');
		if (questionPos > -1) {
			emailTo = emailTo.substring(0, questionPos);
		}
		MailToHandler(emailTo.toLowerCase());
		return false;
	}
	return true;
}

function BrowserLang()
{
	return (navigator.language || navigator.systemLanguage ||
            navigator.userLanguage || 'en').substr(0, 2).toLowerCase();
}

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}

function validateMessageAddressString(addressStr)
{
	var emailsStrArray = new Array();
	var incorrectEmailsArray = new Array();
	var emailParts;
	emailsStrArray = addressStr.replace(/"[^"]*"/g, '').replace(/;/g, ",").split(',');
	for (var j in emailsStrArray)
	{
		emailParts = GetEmailParts(Trim(emailsStrArray[j]));
		if (!Validator.IsCorrectEmail(emailParts.Email))
		{
			incorrectEmailsArray.push(emailParts.Email);
		}
	}
	return incorrectEmailsArray;
}

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}