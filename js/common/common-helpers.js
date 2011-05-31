/*
 * Classes:
 *  CBrowser()
 *  CInfoContainer(name, fadeEffect)
 *  CWindowOpener()
 * Objects:
 *  Validator
 *  Keys
 *  Logger
 */

function CBrowser()
{
	this._init = function ()
	{
		var len = this.Profiles.length;
		for (var i = 0; i < len; i++) {
			if (this.Profiles[i].Criterion) {
				this.Name = this.Profiles[i].Id;
				this.Version = this.Profiles[i].Version();
				this.Allowed = (this.Version >= this.Profiles[i].AtLeast);
				break;
			}
		}
		this.IE = (this.Name == 'Microsoft Internet Explorer');
		this.Opera = (this.Name == 'Opera');
		this.Mozilla = (this.Name == 'Mozilla' || this.Name == 'Firefox' || this.Name == 'Netscape' 
			|| this.Name == 'Chrome' || this.Name == 'Safari');
		this.Safari = (this.Name == 'Safari');
		this.Chrome = (this.Name == 'Chrome');
		this.Gecko = (this.Opera || this.Mozilla);
	};

	this.Profiles = [
		{
			Id: 'Opera',
			Criterion: window.opera,
			AtLeast: 8,
			Version: function () {
				var start, end, r, start1, start2;
				r = navigator.userAgent;
				start1 = r.indexOf('Opera/');
				start2 = r.indexOf('Opera ');
				if (-1 == start1) {
					start = start2 + 6;
					end = r.length;
				} else {
					start = start1 + 6;
					end = r.indexOf(' ');
				}
				r = parseFloat(r.slice(start, end));
				return r;
			}
		},
		{
			Id: 'Chrome',
			Criterion:
			(
				(navigator.appCodeName.toLowerCase() == 'mozilla') &&
				(navigator.appName.toLowerCase() == 'netscape') &&
				(navigator.product.toLowerCase() == 'gecko') &&
				(navigator.userAgent.toLowerCase().indexOf('chrome') != -1)
			),
			AtLeast: 0,
			Version: function () {
				return parseFloat(navigator.userAgent.split('Chrome/').reverse().join('Chrome/'));
			}
		},
		{
			Id: 'Safari',
			Criterion:
			(
				(navigator.appCodeName.toLowerCase() == 'mozilla') &&
				(navigator.appName.toLowerCase() == 'netscape') &&
				(navigator.product.toLowerCase() == 'gecko') &&
				(navigator.userAgent.toLowerCase().indexOf('safari') != -1)
			),
			AtLeast: 1.2,
			Version: function () {
				var r = navigator.userAgent;
				return parseFloat(r.split('Version/').reverse().join(' '));
			}
		},
		{
			Id: 'Firefox',
			Criterion:
			(
				(navigator.appCodeName.toLowerCase() == 'mozilla') &&
				(navigator.appName.toLowerCase() == 'netscape') &&
				(navigator.product.toLowerCase() == 'gecko') &&
				((navigator.userAgent.toLowerCase().indexOf('firefox') != -1) ||
				(navigator.userAgent.toLowerCase().indexOf('iceweasel') != -1))
			),
			AtLeast: 1,
			Version: function () {
				var userAgent = navigator.userAgent.toLowerCase();
				if (userAgent.indexOf('firefox/') != -1) {
					return parseFloat(userAgent.split('firefox/').reverse().join('firefox/'));
				}
				if (userAgent.indexOf('iceweasel/') != -1) {
					return parseFloat(userAgent.split('iceweasel/').reverse().join('iceweasel/'));
				}
				return 0;
			}
		},
		{
			Id: 'Netscape',
			Criterion:
			(
				(navigator.appCodeName.toLowerCase() == 'mozilla') &&
				(navigator.appName.toLowerCase() == 'netscape') &&
				(navigator.product.toLowerCase() == 'gecko') &&
				(navigator.userAgent.toLowerCase().indexOf('netscape') != -1)
			),
			AtLeast: 7,
			Version: function () {
				var r = navigator.userAgent.split(' ').reverse().join(' ');
				r = parseFloat(r.slice(r.indexOf('/') + 1, r.indexOf(' ')));
				return r;
			}
		},
		{
			Id: 'Mozilla',
			Criterion:
			(
				(navigator.appCodeName.toLowerCase() == 'mozilla') &&
				(navigator.appName.toLowerCase() == 'netscape') &&
				(navigator.product.toLowerCase() == 'gecko') &&
				(navigator.userAgent.toLowerCase().indexOf('mozilla') != -1)
			),
			AtLeast: 1,
			Version: function () {
				var r = navigator.userAgent;
				return parseFloat(r.split('Firefox/').reverse().join('Firefox/'));
			}
		},
		{
			Id: 'Microsoft Internet Explorer',
			Criterion:
			(
				(navigator.appName.toLowerCase() == 'microsoft internet explorer') &&
				(navigator.appVersion.toLowerCase().indexOf('msie') !== 0) &&
				(navigator.userAgent.toLowerCase().indexOf('msie') !== 0) &&
				(!window.opera)
			),
			AtLeast: 5,
			Version: function () {
				var r = navigator.userAgent.toLowerCase();
				r = parseFloat(r.slice(r.indexOf('msie') + 4, r.indexOf(';', r.indexOf('msie') + 4)));
				return r;
			}
		}
	];

	this._init();
}

function CInfoContainer(name, fadeEffect)
{
	this._infoMessage = null;
	this._infoObj = null;
	this._infoVisible = true;
	this._infoShown = false;
	this.ErrorObj = null;
	this.ReportObj = null;
	this._name = name;
	this._cont = null;
	this._build(fadeEffect);
}

CInfoContainer.prototype = {
	Resize: function ()
	{
		this.ErrorObj.Resize();
		this.ReportObj.Resize();
		this._infoObj.Resize();
	},

	Unvisible: function()
	{
		this.ErrorObj.Unvisible();
		this.ErrorObj.Hide();
		this.ReportObj.Unvisible();
		this.ReportObj.Hide();
		this._infoVisible = false;
	},

	Visible: function()
	{
		this.ErrorObj.Visible();
		if (!this.ErrorObj.Shown) {
			this.ReportObj.Visible();
			this._infoVisible = true;
			if (this._infoShown) {
				this._infoObj.Show();
				this._infoObj.Resize();
			}
		}
	},

	ShowInfo: function(info)
	{
		this._infoMessage.innerHTML = info;
		if (this._infoVisible) {
			this._infoObj.Show();
			this._infoObj.Resize();
		}
		this._infoShown = true;
		this.HideReport();
	},

	HideInfo: function()
	{
		this._infoObj.Hide();
		this._infoShown = false;
	},

	ShowError: function(errorDesc)
	{
		this.ErrorObj.Show(errorDesc);
		this.ReportObj.Unvisible();
		this._infoVisible = false;
	},

	HideError: function()
	{
		this.ErrorObj.Hide();
		this.ReportObj.Visible();
		if (!this.ReportObj.Shown) {
			this._infoVisible = true;
			if (this._infoShown) {
				this._infoObj.Show();
				this._infoObj.Resize();
			}
		}
	},

	ShowReport: function(report, priorDelay)
	{
		this.ReportObj.Show(report, priorDelay);
		this.HideInfo();
	},

	HideReport: function()
	{
		this.ReportObj.Hide();
	},

	_build: function (fadeEffect)
	{
		var tbl = document.getElementById('info_cont');
		this._infoMessage = document.getElementById('info_message');
		this._infoObj = new CInformation(tbl, 'wm_information wm_status_information');

		this.ErrorObj = new CError(this._name + '.ErrorObj');
		this.ErrorObj.Build();

		this.ReportObj = new CReport(this._name + '.ReportObj');
		this.ReportObj.Build();

		if (!Browser.IE) {
			this.ErrorObj.SetFade(fadeEffect);
			this.ReportObj.SetFade(fadeEffect);
		}
	}
};

WindowOpener = {
	Open: function (url, popupName)
	{
		var allHeight = GetHeight();
		var allWidth = GetWidth();
		var height = 600;
		if (height >= allHeight) height = Math.ceil(allHeight*2/3);
		var width = 800;
		if (width >= allWidth) width = Math.ceil(allWidth*2/3);
		var top = Math.ceil((allHeight - height)/2);
		var left = Math.ceil((allWidth - width)/2);
		var win = window.open(url, popupName, 'toolbar=yes,status=no,scrollbars=yes,resizable=yes,width=' + width + ',height=' + height + ',top=' + top + ',left=' + left);
		win.focus();
		return win;
	},

	OpenAndWrite: function (popupName, headers, body, bodyAttributes)
	{
		var win = WindowOpener.Open('', popupName);
		if (Browser.Mozilla) {
			win.document.open();
		}
		win.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />\n');
		win.document.write('<html>\n');
		win.document.write('<head>\n');
		win.document.write(headers);
		win.document.write('</head>\n');
		if (bodyAttributes == undefined) bodyAttributes = '';
		win.document.write('<body' + bodyAttributes + '>\n');
		win.document.write(body);
		win.document.write('</body>\n');
		win.document.write('</html>\n');
		if (Browser.Mozilla) {
			win.document.close();
		}
	}
}

var Validator = {
    IsEmpty: function (strValue)
    {
		return (strValue.replace(/\s+/g, '') == '');
    },
    
    HasEmailForbiddenSymbols: function (strValue)
    {
		return (strValue.match(/[^A-Z0-9\"!#\$%\^\{\}`~&'\+\-=_@\.]/i));
    },
    
    IsCorrectEmail: function (strValue)
    {
		return (strValue.match(/^[A-Z0-9\"!#\$%\^\{\}`~&'\+\-=_\.]+@[A-Z0-9\.\-]+$/i));
    },
    
    IsCorrectServerName: function (strValue)
    {
		return (!strValue.match(/[^A-Z0-9\.\-\:\/]/i));
    },
    
    IsPositiveNumber: function (intValue)
    {
        if (isNaN(intValue) || intValue <= 0 || Math.round(intValue) != intValue) {
            return false;
        }
        return true;
    },
    
    CorrectNumber: function (value, minValue, maxValue)
    {
        if (isNaN(value) || value <= minValue) {
            return minValue;
        }
        if (maxValue != undefined && value >= maxValue) {
			return maxValue;
		}
        return Math.round(value);
    },
    
    IsPort: function (intValue)
    {
		return (this.IsPositiveNumber(intValue) && intValue <= 65535);
    },
    
    HasSpecSymbols: function (strValue)
    {
		return (strValue.match(/["\/\\*?<>|:]/));
    },
    
    IsCorrectFileName: function (strValue)
    {
        if (!this.HasSpecSymbols(strValue)) {
			return !strValue.match(/^(CON|AUX|COM1|COM2|COM3|COM4|LPT1|LPT2|LPT3|PRN|NUL)$/i);
        }
        return false;
    },
    
    CorrectWebPage: function (strValue)
    {
        return strValue.replace(/^[\/;<=>\[\\#\?]+/g, '');
    },
    
    HasFileExtention: function (strValue, strExtension)
    {           
		return (strValue.substr(strValue.length - strExtension.length - 1, strExtension.length + 1).toLowerCase() == '.' + strExtension.toLowerCase());
    }
};

var Keys = 
{
	Enter: 13,
	Shift: 16,
	Ctrl: 17,
	Space: 32,
	PageUp: 33,
	PageDown: 34,
	End: 35,
	Home: 36,
	Up: 38,
	Down: 40,
	Delete: 46,
	A: 65,
	C: 67,
	N: 78,
	P: 80,
	R: 82,
	S: 83,
	Comma: 188,
	Dot: 190,

	GetCodeFromEvent: function (ev)
	{
		var key = -1;
		if (window.event) {
			key = window.event.keyCode;
		}
		else if (ev) {
			key = ev.which;
		}
		return key;
	}
};

var Logger = {
	_container: null,
	_initialized: false,
	
	_init: function ()
	{
		if (this._initialized == true) {
			return;
		}
		this._container = CreateChild(document.body, 'div');
		this._container.dir = 'ltr';
		with (this._container.style) {
			color = 'black';
			border = 'solid 2px black';
			background = 'white';
			width = '700px';
			height = '100px';
			bottom = '0px';
			right = '0px';
			position = 'absolute';
			zIndex = '10';
			textAlign = 'left';
			overflow = 'auto';
		}
		this._initialized = true;
	},
	
	_write: function (msg)
	{
		this._init();
		if (!this._initialized) return;
		this._container.innerHTML = this._container.innerHTML + msg;
	},
	
	Write: function (msg)
	{
		this._write(msg + '; ');
	},
	
	WriteLine: function (msg)
	{
		this._write(msg + '<br />');
	},
	
	Clear: function ()
	{
		this._init();
		if (!this._initialized) return;
		this._container.innerHTML = '';
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}