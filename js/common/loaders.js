/*
 * Classes:
 *  CCheckMail(type)
 *  CDictionary()
 *  CScriptLoader()
 *  CNetLoader()
 *  CLanguageChanger()
 *  CTip()
 */

var CHECK_MAIL_BY_CLICK = 0;
var CHECK_MAIL_AT_LOGIN = 1;

function CCheckMail(type)
{
	this.isBuilded = false;
	this._type = (type) ? type : CHECK_MAIL_BY_CLICK;
	this.started = false;
    this.hidden = false;
	
	this._url = CheckMailUrl;
	this._email = '';
	this._msgsCount = 0;
	this._preText = '';
	
	this._form = null;
	this._typeObj = null;
	
	this._mainContainer = null;
	this._infomation = null;
	this._message = null;
	this._progressBarUsed = null;

	this.IeCheckTimer = null;
	this.FfCheckTimer = null;
}

CCheckMail.prototype = {
	Start: function (hide)
	{
		if (!this.hidden && this._type == CHECK_MAIL_BY_CLICK) {
			WebMail.InfoContainer.Unvisible();
		}
		clearTimeout(this.IeCheckTimer);
		clearTimeout(this.FfCheckTimer);
		hide = hide || false;
		if (this.started) {
			return;
	    }
        this.hidden = hide;
		if (this.isBuilded) {
			if (!hide && this._type == CHECK_MAIL_BY_CLICK) {
				this._infomation.Show();
			}
		}
		else {
			this.Build(hide);
		}
		this._preText = '';
		if (!hide) {
			this.SetText(Lang.LoggingToServer);
			this.UpdateProgressBar(0);
		}
		this._msgsCount = 0;
		this._typeObj.value = (hide) ? 2 : this._type;
		this._form.action = this._url + '?param=' + Math.random();
		this._form.submit();
		this.started = true;
	},

	SetAccount: function (account)
	{
		this._email = account;
		this._mainContainer.className = 'wm_connection_information';
		this._preText = '<b>' + this._email + '</b><br/>';
	},

	SetFolder: function (folderName, msgsCount)
	{
		this.UpdateProgressBar(0);
		this._folderName = folderName;
		this._msgsCount = msgsCount;
		this._preText = '';
		if (this._email.length > 0) {
			this._preText += '<b>' + this._email + '</b><br/>';
		}
		this._preText += Lang.Folder + ' <b>' + this._folderName + '</b><br/>';
	},
	
	SetText: function (text)
	{
		this._message.innerHTML = this._preText + text;
		if (this._type == CHECK_MAIL_BY_CLICK) {
			this._infomation.Resize();
		}
	},
	
	DeleteMsg: function (msgNumber) {
		if (msgNumber == -1) {
			this.SetText(Lang.DeletingMessages);
		}
		else {
			this.SetText(Lang.DeletingMessage + ' #' + msgNumber + ' ' + Lang.Of + ' ' + this._msgsCount);
			this.UpdateProgressBar(msgNumber);
		}
	},
	
	SetMsgNumber: function (msgNumber)
	{
		if (msgNumber <= this._msgsCount) {
			this.SetText(Lang.RetrievingMessage + ' #' + msgNumber + ' ' + Lang.Of + ' ' + this._msgsCount);
		}
		this.UpdateProgressBar(msgNumber);
	},
	
	UpdateProgressBar: function (msgNumber)
	{
		if (this._msgsCount > 0) {
			var percent = Math.ceil((msgNumber - 1) * 100 / this._msgsCount);
			if (percent < 0) { 
				percent = 0; 
			}
			else if (percent > 100) {
				percent = 100;
			}
			this._progressBarUsed.style.width = percent + 'px';
		}
	},
	
	End: function ()
	{
		if (this._type == CHECK_MAIL_BY_CLICK) {
			this._infomation.Hide();
		}
		this.started = false;
		if (!this.hidden && this._type == CHECK_MAIL_BY_CLICK) {
			WebMail.InfoContainer.Visible();
		}
	},
	
	BuildCheckMailByClick: function ()
	{
		var tbl = CreateChild(document.body, 'table',
			[['class', 'wm_information wm_connection_information'],
			['id', 'info_cont'],
			['cellpadding', '0'],
			['cellspacing', '0']]);

		var tr = CreateChild(tbl, 'tr', [['style', 'position:relative;z-index:20']]);
		CreateChild(tr, 'td', [['class', 'wm_shadow'],
			['style', 'width:2px;font-size:1px;']]);

		var td = CreateChild(tr, 'td');
		var infoDiv = CreateChild(td, 'div', [['class', 'wm_info_message'], ['id', 'info_message']]);
		var aDiv = CreateChild(td, 'div', [['class', 'a']]);
		aDiv.innerHTML = '&nbsp;';
		var bDiv = CreateChild(td, 'div', [['class', 'b']]);
		bDiv.innerHTML = '&nbsp;';
		this._message = CreateChild(infoDiv, 'span');
		var divPB = CreateChild(infoDiv, 'div', [['class', 'wm_progressbar']]);
		CreateChild(tr, 'td', [['class', 'wm_shadow'],
			['style', 'width:2px;font-size:1px;']]);

		tr = CreateChild(tbl, 'tr');

		td = CreateChild(tr, 'td', [['class', 'wm_shadow'],
			['colspan', '3'],
			['style', 'height:2px;background:none;']]);
		aDiv = CreateChild(td, 'div', [['class', 'a']]);
		aDiv.innerHTML = '&nbsp;';
		bDiv = CreateChild(td, 'div', [['class', 'b']]);
		bDiv.innerHTML = '&nbsp;';

		tr = CreateChild(tbl, 'tr', [['style', 'position:relative;z-index:19']]);
		td = CreateChild(tr, 'td', [['style', 'height:2px;'], ['colspan', '3']]);
		var div = CreateChild(td, 'div', [['class', 'a wm_shadow'],
			['style', 'margin:0px 2px;height:2px; top:-4px; position:relative; border:0px;background:#555;']]);
		div.innerHTML = '&nbsp;';

		this._containerObj = tbl;
		
		this._progressBarUsed = CreateChild(divPB, 'div', [['class', 'wm_progressbar_used']]);
		this._infomation = new CInformation(tbl, 'wm_information wm_connection_information');
		this._infomation.Resize();
	},
	
	BuildCheckMailAtLogin: function ()
	{
		var parentCont = document.getElementById('content');
		parentCont = (parentCont) ? parentCont : document.body
		var tbl = CreateChild(parentCont, 'table', [['style', 'margin-top: 30px; position: static;']]);
		tbl.className = 'wm_hide';
		this._mainContainer = tbl;
		var tr = tbl.insertRow(0);
		var td = tr.insertCell(0);
		td.className = 'wm_connection_header';
		td.colSpan = '3';
		td.innerHTML = Lang.Connection;
		CreateChild(td, 'span');
		tr = tbl.insertRow(1);
		td = tr.insertCell(0);
		td.className = 'wm_connection_icon';
		td = tr.insertCell(1);
		td.className = 'wm_connection_message';
		td.align = 'center';
		this._message = td;
		td = tr.insertCell(2);
		td.className = 'wm_connection_empty';
		tr = tbl.insertRow(2);
		td = tr.insertCell(0);
		td.className = 'wm_connection_progressbar';
		td.colSpan = 3;
		var div = CreateChild(td, 'div', [['align', 'center']]);
		var subDiv = CreateChild(div, 'div', [['class', 'wm_progressbar']]);
		this._progressBarUsed = CreateChild(subDiv, 'div', [['class', 'wm_progressbar_used']]);
	},
	
	Build: function (hide)
	{
		var ifrm = CreateChild(document.body, 'iframe', [['id', 'CheckMailIframe'], ['name', 'CheckMailIframe'], 
			['src', EmptyHtmlUrl], ['class', 'wm_hide']]);
		var obj = this;
		ifrm.onreadystatechange = function () {
            if (this.started && this.readyState == 'complete') {
                obj.IeCheckTimer = setTimeout('CheckEndCheckMailHandler()', 1000);
            }
        }; // for IE
		ifrm.onload = function () {
			if (this.started) {
				obj.FfCheckTimer = setTimeout('CheckEndCheckMailHandler()', 1000);
			}
        }; // for other browsers
		var frm = CreateChild(document.body, 'form', [['action', this._url], ['target', 'CheckMailIframe'], ['method', 'post'], ['id', 'CheckMailForm'], ['name', 'CheckMailForm'], ['class', 'wm_hide']]);
		this._typeObj = CreateChild(frm, 'input', [['name', 'Type'], ['value', this._type]]);
		this._form = frm;
		switch (this._type) {
			case CHECK_MAIL_BY_CLICK:
				this.BuildCheckMailByClick();
				break;
			case CHECK_MAIL_AT_LOGIN:
				this.BuildCheckMailAtLogin();
				break;
		}
		
		this.isBuilded = true;
		if (this._infomation && hide) {
			this._infomation.Hide();	
		}
	}
};

function CDictionary()
{
	this.count = 0;
	this.Obj = {};
}

CDictionary.prototype = {
	exists: function (sKey)
	{
		return (this.Obj[sKey]) ? true : false;
	},

	add: function (sKey, aVal)
	{
		var K = String(sKey);
		if (this.exists(K)) {
			return false;
		}
		this.Obj[K] = aVal;
		this.count++;
		return true;
	},

	remove: function (sKey)
	{
		var K = String(sKey);
		if (!this.exists(K)) {
			return false;
		}
		delete this.Obj[K];
		this.count--;
		return true;
	},

	removeAll: function ()
	{
		for (var key in this.Obj) {
			delete this.Obj[key];
		}
		this.count = 0;
	},

	values: function ()
	{
		var Arr, key;
		Arr = [];
		for (key in this.Obj) {
			Arr[Arr.length] = this.Obj[key];
		}
		return Arr;
	},

	keys: function ()
	{
		var Arr, key;
		Arr = [];
		for (key in this.Obj) {
			Arr[Arr.length] = key;
		}
		return Arr;
	},

	items: function ()
	{
		var Arr, A, key;
		Arr = [];
		for (key in this.Obj) {
			A = [key, this.Obj[key]];
			Arr[Arr.length] = A;
		}
		return Arr;
	},

	getVal: function (sKey)
	{
		var K = String(sKey);
		return this.Obj[K];
	},

	setVal: function (sKey, aVal)
	{
		var K = String(sKey);
		if (this.exists(K)) {
			this.Obj[K] = aVal;
		} else {
			this.add(K, aVal);
		}
	},

	setKey: function (sKey, sNewKey)
	{
		var K, Nk;
		K = String(sKey);
		Nk = String(sNewKey);
		if (this.exists(K)) {
			if (!this.exists(Nk)) {
				this.add(Nk, this.getVal(K));
				this.remove(K);
			}
		} else if (!this.exists(Nk)) {
			this.add(Nk, null);
		}
	}
};

function CScriptLoader()
{
	this.onLoad = null;
	this.loadedCount = 0;
	this.scriptsCount = 0;
	this._onLoad = null;
	this._scripts = new CDictionary();
}

CScriptLoader.prototype = {
	Load: function (urlArray, loadHandler)
	{
		this.onLoad = loadHandler;
		this.loadedCount = 0;
		this.scriptsCount = urlArray.length;
		if (this.scriptsCount == 0) {
			this.onLoad.call();
		}
		for (var i in urlArray) {
			this.LoadItem(urlArray[i], this.ScriptLoadHandler);
		}
	},
	
	ScriptLoadHandler: function ()
	{
		this.loadedCount++;
		if (this.loadedCount == this.scriptsCount) {
			this.onLoad.call();
		}
	},
	
	LoadItem: function (url, loadHandler)
	{
		var script, obj, HeadElements;
		this._onLoad = loadHandler;
		script = document.createElement('script');
		script.setAttribute('type', 'text/javascript');
		obj = this;
		if (Browser.IE) {
			script.onreadystatechange = function ()
			{
			    if (this.readyState == 'complete' || this.readyState == 'loaded') {
			        if (obj._scripts.exists(this.src)) obj._scripts.remove(this.src);
					obj._onLoad.call(obj);
				}
			};
		}
		else {
			script.onload = function () {
				obj._scripts.remove(this.src);
				obj._onLoad.call(obj);
			};
		}
		this._scripts.add(url, true);
		script.src = url;
		HeadElements = document.getElementsByTagName('head');
		HeadElements[0].appendChild(script);
	}
};

function CNetLoader()
{
	this.Url = null;
	this.onLoad = null;
	this.onError = null;
	this.responseXML = null;
	this.responseText = null;
	this.ErrorDesc = null;
	this.Request = null;
	this.Log = '';
}

CNetLoader.prototype = {
	GetTransport: function ()
	{
		var transport = null;
		if (window.XMLHttpRequest) {
			transport = new XMLHttpRequest();
		}
		else {
			if (window.ActiveXObject) {
				try {
					transport = new ActiveXObject('Msxml2.XMLHTTP');
				}
				catch (err) {
					try {
						transport = new ActiveXObject('Microsoft.XMLHTTP');
					}
					catch (err2) {
					}
				}
			}
		}
		return transport;
	},

	LoadXMLDoc: function (Url, PostParams, onLoad, onError)
	{
		var Request, obj;
		this.Url = Url;
		this.onLoad = onLoad;
		this.onError = onError;
		Request = this.GetTransport();
		if (Request) {
			try {
				Request.open('POST', this.Url, true);
				Request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				obj = this;
				Request.onreadystatechange = function () {
					obj.OnReadyState(Request); 
				};
				Request.send(PostParams);
			}
			catch (err) {
				this.ErrorDesc = Lang.ErrorRequestFailed;
				this.onError.call(this);
			}
		}
		else {
			this.ErrorDesc = Lang.ErrorAbsentXMLHttpRequest;
			this.onError.call(this);
		}
		this.Request = Request;
	},
	
	OnReadyState: function (Request)
	{
		var ReadyStateComplete, Ready, HttpStatus, HttpStatusText;
		ReadyStateComplete = 4;
		Ready = Request.readyState;
		if (Ready == ReadyStateComplete) {
			try {
				HttpStatus = (typeof Request.status != 'undefined') ? Request.status : 13030;
				HttpStatusText = (typeof Request.statusText != 'undefined') ? Request.statusText : 'empty status text';
			}
			catch (e) {
				// 13030 is the custom code to indicate the condition -- in Mozilla/FF --
				// when the o object's status and statusText properties are
				// unavailable, and a query attempt throws an exception.
				HttpStatus = 13030;
				HttpStatusText = 'empty status text';
			}
			if (HttpStatus == 200 || HttpStatus == 0) {
				if (HttpStatus == 0 && Request.getResponseHeader('Content-Type') == null) {
					return;
				}
				this.responseXML = Request.responseXML;
				this.responseText = Request.responseText;
				this.onLoad.call(this);
			} else if (HttpStatus != 13030) {
				this.ErrorDesc = Lang.ErrorConnectionFailed + ' (' + HttpStatus + ' - ' + HttpStatusText + ')\n' + Request.responseText;
				this.onError.call(this);
			}
		}
	},
	
	CheckRequest: function ()
	{
		if (null != this.Request) {
			this.Request.onreadystatechange = null;
			this.Request.abort();
		}
	}
};

function CLanguageChanger()
{
	this._innerHTML = Array();
	this._iCount = 0;
	this._value = Array();
	this._vCount = 0;
	this._title = Array();
	this._tCount = 0;
}

CLanguageChanger.prototype = {
	Register: function (type, obj, field, end, start, number)
	{
		if (!start) {
			start = '';
		}
		switch (type) {
		default:
		case 'innerHTML':
			if (!number) {
				number = this._iCount;
				this._iCount++;
			}
			this._innerHTML[number] = {Elem: obj, Field: field, End: end, Start: start};
			return number;
		case 'value':
			if (!number) {
				number = this._vCount;
				this._vCount++;
			}
			this._value[number] = {Elem: obj, Field: field, End: end, Start: start};
			return number;
		case 'title':
			if (!number) {
				number = this._tCount;
				this._tCount++;
			}
			this._title[number] = {Elem: obj, Field: field, End: end, Start: start};
			return number;
		}
	},

	Go: function ()
	{
		var i, obj, iCount;
		iCount = this._innerHTML.length;
		for (i = 0; i < iCount; i++) {
			obj = this._innerHTML[i];
			if (obj && obj.Elem) {
				obj.Elem.innerHTML = obj.Start + Lang[obj.Field] + obj.End;
			}
		}

		iCount = this._value.length;
		for (i = 0; i < iCount; i++) {
			obj = this._value[i];
			if (obj && obj.Elem) {
				obj.Elem.value = Lang[obj.Field] + obj.End;
			}
		}

		iCount = this._title.length;
		for (i = 0; i < iCount; i++) {
			obj = this._title[i];
			if (obj && obj.Elem) {
				obj.Elem.title = Lang[obj.Field] + obj.End;
			}
		}
	}
};

function CTip()
{
	var tr, td;
	this._container = CreateChild(document.body, 'table');
	this._container.className = 'wm_hide';
	tr = this._container.insertRow(0);
	td = tr.insertCell(0);
	CreateChild(td, 'div', [['class', 'wm_tip_arrow']]);
	CreateChild(td, 'div', [['class', 'wm_tip_icon']]);
	this._message = CreateChild(td, 'div', [['class', 'wm_tip_message']]);
	this._base = '';
}

CTip.prototype = {
	SetMessageText: function (text)
	{
		this._message.innerHTML = text;
	},

	SetCoord: function (element)
	{
		var bounds = GetBounds(element);
		this._container.style.top = (bounds.Top + bounds.Height / 2 - 16) + 'px';
		if (window.RTL) {
			this._container.style.right = (GetWidth() - bounds.Left + 6) + 'px';
		} else {
			this._container.style.left = (bounds.Left + bounds.Width + 6) + 'px';
		}
	},

	Show: function (text, element, base)
	{
		this.SetMessageText(text);
		this.SetCoord(element);
		this._base = base;
		this._container.className = 'wm_tip';
	},

	Hide: function (base)
	{
		if (this._base == base || base == '') {
			this._container.className = 'wm_hide';
		}
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}