/*
 * Classes:
 *  CPopupMenu(popup_menu, popup_control, menu_class, popup_move, popup_title, move_class, move_press_class, title_class, title_over_class)
 *  CPopupMenus()
 *  CSearchForm(BigSearchForm, SmallSearchForm, downButton, upButton, bigFormId, bigLookFor, smallLookFor, centralPaneView)
 *  CFadeEffect(name)
 *  CInformation(cont, cls)
 *  CError(name)
 *  CReport(name)
 * Prototypes:
 *  ReportPrototype
 */

var POPUP_SHOWED = 2;
var POPUP_READY = 1;
var POPUP_HIDDEN = 0;

function CPopupMenu(popup_menu, popup_control, menu_class, popup_move, popup_title, move_class, move_press_class,
	title_class, title_over_class)
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
	this.disable = false;
}

function CPopupMenus()
{
	this.items = [];
	this.isShown = POPUP_HIDDEN;
}

CPopupMenus.prototype = {
	getLength: function ()
	{
		return this.items.length;
	},
	
	addItem: function (popup_menu)
	{
		this.items.push(popup_menu);
		this.hideItem(this.getLength() - 1);
	},
	
	showItem: function (item_id)
	{
		this.hideAllItems();
		var item = this.items[item_id];

		item.popup.className = item.menu_class;
		if (item.title_class && item.title_class != '') {
			item.control.className = item.title_class;
			item.title.className = item.title_class + (item.disable ? ' wm_toolbar_item_disabled' : '');
		}
		if (item.move_press_class && item.move_press_class != '')
			item.move.className = item.move_press_class;
		var obj = this;
		item.control.onclick = function () {
			obj.hideItem(item_id);
		};
		var borders = 1;
		if (item.title_over_class != '') {
			item.control.onmouseover = function () {};
			item.control.onmouseout = function () {};
			item.title.onmouseover = function () {};
			item.title.onmouseout = function () {};
			borders = 2;
		}
		this.isShown = POPUP_SHOWED;
		this.replaceItem(item, borders);
		this.resizeHeight(item);
	},
	
	resizeHeight: function (item)
	{
		item.popup.style.height = 'auto';
		var pOffsetHeight = item.popup.offsetHeight;
		var height = GetHeight();
		if (pOffsetHeight > height * 2 / 3) {
			item.popup.style.height = Math.round(height * 2 / 3) + 'px';
			item.popup.style.overflowY = 'auto';
		}
		else {
			item.popup.style.overflowY = 'hidden';
		}
	},
	
	replaceItem: function (item, borders)
	{
		var bounds = GetBounds(item.move);
		if (!window.RTL) {
			item.popup.style.left = bounds.Left + 'px';
		}
		item.popup.style.top = bounds.Top + bounds.Height + 'px';
		
		item.popup.style.width = 'auto';
		var pOffsetWidth = item.popup.offsetWidth;
		var cOffsetWidth = item.control.offsetWidth;
		var tOffsetWidth = (item.control == item.title) ? 0 : item.title.offsetWidth;
		item.popup.style.width = (pOffsetWidth < (cOffsetWidth + tOffsetWidth - borders)) ?
			(cOffsetWidth + tOffsetWidth - borders) + 'px' : (pOffsetWidth + borders) + 'px';

		/* rtl */
		if (window.RTL) {
			item.popup.style.left = (bounds.Left + bounds.Width - item.popup.offsetWidth) + 'px';
		}
	},
	
	hideItem: function (item_id)
	{
		var item = this.items[item_id];
		item.popup.className = 'wm_hide';
		if (item.move_class && item.move_class != '' && item.move.className != 'wm_hide')
			item.move.className = item.move_class;
		var obj = this;
		item.control.onclick = function () {
			obj.showItem(item_id);
		};
		if (item.title_over_class != '') {
			item.control.onmouseover = function () {
				item.title.className = item.title_over_class + (item.disable ? ' wm_toolbar_item_disabled' : ''); 
				item.control.className = item.title_over_class;
			};
			item.control.onmouseout = function () {
				item.title.className = item.title_class + (item.disable ? ' wm_toolbar_item_disabled' : ''); 
				item.control.className = item.title_class; 
			};
			item.title.onmouseover = function () {
				item.title.className = item.title_over_class + (item.disable ? ' wm_toolbar_item_disabled' : ''); 
			};
			item.title.onmouseout = function () {
				item.title.className = item.title_class + (item.disable ? ' wm_toolbar_item_disabled' : ''); 
			};
		}
	},
	
	hideAllItems: function ()
	{
		for (var i = this.getLength() - 1; i >= 0; i--) {
			this.hideItem(i);
		}
		this.isShown = POPUP_HIDDEN;
	},
	
	checkShownItems: function ()
	{
		if (this.isShown == POPUP_READY) {
			this.hideAllItems();
		}
		if (this.isShown == POPUP_SHOWED) {
			this.isShown = POPUP_READY;
		}
	}
};

function CSearchForm(BigSearchForm, SmallSearchForm, downButton, upButton, bigFormId, bigLookFor, smallLookFor, centralPaneView)
{
	this.form = BigSearchForm;
	this._bigFormId = bigFormId;
	this.smallForm = SmallSearchForm;
	this.downButton = downButton;
	this.upButton = upButton;
	this._bigLookFor = bigLookFor;
	this._smallLookFor = smallLookFor;
	this.isShown = POPUP_HIDDEN;
	this.shown = false;
	this._searchIn = null;
	this._focused = false;

	this._isEmpty = true;

	this._bigClassName = (centralPaneView) ? 'wm_search_form wm_central_pane_view' : 'wm_search_form';
	this._init();
}

CSearchForm.prototype = 
{
	SetStringValue: function (string)
	{
		if (string == '') {
			string = Lang.SearchInputText;
			this._isEmpty = true;
		}
		this._setStringToInput(string);
	},

	_setStringToInput: function (string)
	{
		this._setStyle();
		this._bigLookFor.value = string;
		this._smallLookFor.value = string;
	},
	
	_setStyle: function ()
	{
		if (this._isEmpty) {
			this._bigLookFor.style.color = '#bbb';
			this._bigLookFor.style.fontStyle = 'italic';
			this._smallLookFor.style.color = '#bbb';
			this._smallLookFor.style.fontStyle = 'italic';
		}
		else {
			this._bigLookFor.style.color = 'Black';
			this._bigLookFor.style.fontStyle = 'normal';
			this._smallLookFor.style.color = 'Black';
			this._smallLookFor.style.fontStyle = 'normal';
		}
	},
	
	_init: function ()
	{
		var obj = this;
		this._bigLookFor.onfocus = function () {
			obj.Focus();
		};
		this._bigLookFor.onblur = function () {
			obj.Blur();
		};
		this._smallLookFor.onfocus = function () {
			obj.Focus();
		};
		this._smallLookFor.onblur = function () {
			obj.Blur();
		};
		this._bigLookFor.value = Lang.SearchInputText;
		this._smallLookFor.value = Lang.SearchInputText;
		this._isEmpty = true;
		this._setStyle();
	},
	
	Blur: function ()
	{
		var string = this._getStringFromInput();
		this.SetStringValue(string);
	},

	_getStringFromInput: function ()
	{
		var string = (this.isShown != POPUP_HIDDEN) ? this._bigLookFor.value : this._smallLookFor.value;
		return string;
	},

	GetStringValue: function ()
	{
		var string = this._getStringFromInput();
		if (this._isEmpty == true) {
			string = '';
		}
		return string;
	},

	FocusSmallForm: function ()
	{
		this._smallLookFor.focus();
	},

	Focus: function ()
	{
		var string = this.GetStringValue();
		this._isEmpty = false;
		this._setStringToInput(string);
	},
	
	Show: function ()
	{
		this.shown = true;
		this.isShown = POPUP_HIDDEN;
		this.smallForm.className = 'wm_toolbar_search_item';
		var obj = this;
		this.downButton.onclick = function () {
			obj.ShowBigForm(); 
		};
		this.ShowDownButton();
		this.form.className = 'wm_hide';
		if (null !== this._searchIn) {
			this._searchIn.className = 'wm_hide';
		}
		this.HideUpButton();
	},
	
	ShowDownButton: function ()
	{
		var obj = this;
		this.downButton.onmouseover = function () {
			obj.downButton.className = 'wm_toolbar_search_item_over';
			obj.smallForm.className = 'wm_toolbar_search_item_over';
		};
		this.downButton.onmouseout = function () {
			obj.downButton.className = 'wm_toolbar_search_item';
			obj.smallForm.className = 'wm_toolbar_search_item';
		};
		this.downButton.className = 'wm_toolbar_search_item';
	},
	
	HideDownButton: function ()
	{
		this.downButton.onmouseover = function () {};
		this.downButton.onmouseout = function () {};
		this.downButton.className = 'wm_hide';
	},
	
	ShowUpButton: function ()
	{
		var obj = this;
		this.upButton.onmouseover = function () {
			obj.upButton.className = 'wm_toolbar_search_item_over';
		};
		this.upButton.onmouseout = function () {
			obj.upButton.className = 'wm_toolbar_search_item';
		};
		this.upButton.className = 'wm_toolbar_search_item';
	},
	
	HideUpButton: function ()
	{
		this.upButton.onmouseover = function () {};
		this.upButton.onmouseout = function () {};
		this.upButton.className = 'wm_hide';
	},
	
	Hide: function ()
	{
		this.shown = false;
		this.smallForm.className = 'wm_hide';
		this.HideDownButton();
		this.form.className = 'wm_hide';
		if (null !== this._searchIn) {
			this._searchIn.className = 'wm_hide';
		}
		this.HideUpButton();
	},
	
	SetSearchIn: function (searchIn)
	{
		this._searchIn = searchIn;
	},
	
	ShowBigForm: function ()
	{
		var bounds = GetBounds(this.smallForm);
		this.form.style.top = bounds.Top + 'px';
		if (window.RTL) {
		    this.form.style.left = bounds.Left + 'px';
		} else {
		    this.form.style.right = (GetWidth() - bounds.Left - bounds.Width) + 'px';
		}
		this.form.className = this._bigClassName;
		this.smallForm.className = 'wm_hide';
		this.HideDownButton();
		this.ShowUpButton();
		this.isShown = POPUP_SHOWED;
		this._bigLookFor.value = this._smallLookFor.value;
		if (null !== this._searchIn) {
			this._searchIn.className = '';
		}
	},
	
	checkVisibility: function (ev, isM)
	{
		if (this.isShown == POPUP_READY) {
			ev = ev ? ev : window.event;
			var elem = (isM) ? ev.target : ev.srcElement;
			while (elem && elem.tagName != 'DIV') {
				if (elem.parentNode) { 
					elem = elem.parentNode;
				}
                else {
					break; 
				}
			}
			if (elem.id != this._bigFormId) {
				this.Show();
			}
		}
		if (this.isShown == POPUP_SHOWED) {
			this.isShown = POPUP_READY;
		}
	}
};

function CFadeEffect(name)
{
    this._name = name;
    this._elem = null;
    this._interval = 50;
    this._timer = null;
}

CFadeEffect.prototype = 
{
    Go: function (elem, delay)
    {
        this._elem = elem;
        clearTimeout(this._timer);
        this._timer = setTimeout(this._name + '.SetOpacityAndRestartTimeout(1)', delay);
        return (delay + 10 * this._interval);
    },

    Stop: function ()
    {
        this._setOpacity(1);
        clearTimeout(this._timer);
    },
	
    _isIE5Plus: function ()
    {
        if (document.body.filters)
        {
            var marray = navigator.appVersion.match(/MSIE ([\d.]+);/);
            if (marray && marray.length > 1 && marray[1] >= 5.5) {
                return true;
            }
        }
        return false;
    },

    _setOpacity: function (opacity)
    {
		if (this._elem == null) return;
        opacity = Math.round(opacity * 10) / 10;
        var elem = this._elem;
        // Internet Exploder 5.5+
        if (this._isIE5Plus()) {
            var opacityIe = opacity * 100;
            var oAlpha = elem.filters['DXImageTransform.Microsoft.alpha'] || elem.filters.alpha;
            if (oAlpha) {
                oAlpha.opacity = opacityIe;
            }
            else {
                elem.style.filter += 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + opacityIe + ')';
            }
        }
        else {
            elem.style.opacity = opacity;       // CSS3 compliant (Moz 1.7+, Safari 1.2+, Opera 9)
            elem.style.MozOpacity = opacity;	// Mozilla 1.6-, Firefox 0.8
            elem.style.KhtmlOpacity = opacity;	// Konqueror 3.1, Safari 1.1
        }
    },

    SetOpacityAndRestartTimeout: function (opacity)
    {
        this._setOpacity(opacity);
        clearTimeout(this._timer);
        if (opacity > 0) {
            this._timer = setTimeout(this._name + '.SetOpacityAndRestartTimeout(' + (opacity - 0.1) + ')', this._interval);
        }
    }
};

/* for control placement and displaying of information block */
function CInformation(cont, cls)
{
	this._mainContainer = cont;
	this._containerClass = cls;
}

CInformation.prototype = {
	Show: function ()
	{
		this._mainContainer.className = this._containerClass;
	},
	
	Hide: function ()
	{
		this._mainContainer.className = 'wm_hide';
	},

	Resize: function ()
	{
		var cont = this._mainContainer;
		cont.style.right = 'auto';
		cont.style.width = 'auto';
		var offsetWidth = cont.offsetWidth;
		var width = GetWidth();
		if (offsetWidth >  0.4 * width) {
			cont.style.width = '40%';
		}
		offsetWidth = cont.offsetWidth;
		cont.style.top = this.GetScrollY() + 'px';
		cont.style.left = Math.round((width - offsetWidth) / 2) + 'px';
	},

	GetScrollY: function ()
	{
		var scrollY = 0;
		if (document.body && typeof document.body.scrollTop != 'undefined') {
			scrollY += document.body.scrollTop;
			if (scrollY === 0 && document.body.parentNode && typeof document.body.parentNode != 'undefined') {
				scrollY += document.body.parentNode.scrollTop;
			}
		} else if (typeof window.pageXOffset != 'undefined') {
			scrollY += window.pageYOffset;
		}
		return scrollY;
	}
};

CInfoContainer.prototype.GetScrollY = CInformation.prototype.GetScrollY;

function CError()
{
	this._hideFunction = 'WebMail.HideError()';
	this._containerObj = null;
	this._messageObj = null;
	this._controlObj = null;
	this._fadeObj = null;
	this._delay = 10000;
	this._hideTimer = null;
	this._className = 'wm_information wm_error_information';
	this._hasCloseImage = true;
	this.Shown = false;
	this._visible = true;
}

function CReport()
{
	this._hideFunction = 'WebMail.HideReport()';
	this._containerObj = null;
	this._messageObj = null;
	this._controlObj = null;
	this._fadeObj = null;
	this._delay = 5000;
    this._hideTimer = null;
	this._className = 'wm_information wm_report_information';
	this._hasCloseImage = false;
	this.Shown = false;
	this._visible = true;
}

var ReportPrototype = 
{
	Show: function (msg, priorDelay)
	{
		this._messageObj.innerHTML = msg;
		if (this._visible) {
			this._controlObj.Show();
			this._controlObj.Resize();
		}
        var interval = (priorDelay) ? priorDelay : this._delay;
        if (null !== this._fadeObj) {
            interval = this._fadeObj.Go(this._containerObj, interval);
        }
        clearTimeout(this._hideTimer);
        this._hideTimer = setTimeout(this._hideFunction, interval);
		this.Shown = true;
	},
	
	SetFade: function (fadeObj)
	{
		this._fadeObj = fadeObj;
	},
	
	Hide: function ()
	{
		clearTimeout(this._hideTimer);
		this._controlObj.Hide();
		if (null !== this._fadeObj) {
			this._fadeObj.Stop();
		}
		this.Shown = false;
	},
	
	Unvisible: function ()
	{
		this._controlObj.Hide();
		this._visible = false;
	},

	Visible: function ()
	{
		if (this.Shown) {
			this._controlObj.Show();
			this._controlObj.Resize();
		}
		this._visible = true;
	},

	Resize: function ()
	{
		this._controlObj.Resize();		
	},

	Build: function (parent)
	{
		if (parent == undefined) parent = document.body;
		var tbl = CreateChild(parent, 'table',
			[['class', 'wm_hide'],
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
		if (this._hasCloseImage) {
			CreateChild(infoDiv, 'div', [['class', 'wm_info_image']]);
			var closeImageDiv = CreateChild(infoDiv, 'div', [['class', 'wm_close_info_image wm_control']]);
			closeImageDiv.onclick = function () {
				WebMail.HideError();
			};
		}

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
		this._messageObj = CreateChild(infoDiv, 'span');
		this._controlObj = new CInformation(tbl, this._className);
	}
};

CReport.prototype = ReportPrototype;
CError.prototype = ReportPrototype;

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}
