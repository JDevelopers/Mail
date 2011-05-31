/*
 * Functions:
 *  AddEvent
 * Objects:
 *  HtmlEditorField
 * Functions:
 *  MisspelCliq()
 *  getCodeAndWhich(ev)
 *  isTextChanged(ev)
 *  EditKeyHandle(ev)
 *  AddMisspelEvents()
 *  ReplaceWord()
 *  SpellCheck()
 *  ShowLoadingHandler()
 * Classes:
 *  CSpellchecker
 */

function AddEvent(obj, eventType, eventFunction)
{
	if (obj.addEventListener) {
		obj.addEventListener(eventType, eventFunction, false);
		return true;
	}
	else if (obj.attachEvent) {
		return obj.attachEvent('on' + eventType, eventFunction);
	}
	return false;
}

var Fonts = ['Arial', 'Arial Black', 'Courier New', 'Tahoma', 'Times New Roman', 'Verdana'];
var X_ICON_SHIFT = 40;
var Y_ICON_SHIFT = 40;

var HtmlEditorField = {
	_defaulFontName: 'Tahoma',
	_defaulFontSize: 2,
	
	_mainTbl: null,
	_header: null,
	_iframesContainer: null,
	_colorPalette: null,
	_colorTable: null,

	_btnFontColor: null,
	_btnBgColor: null,
	_btnInsertLink: null,
	_btnInsertImage: null,
	_fontFaceSel: null,
	_fontSizeSel: null,

	_editor: null,
	_area: null,
	
	_loaded: false,
	_designMode: false,
	_designModeStart: false,

	_colorMode: -1,
	_colorChoosing: 0,
	_currentColor: null,

	_range: null,
	shown: false,
	
	_plainEditor: null,
	_htmlSwitcher: null,
	_htmlMode: true,
	_waitHtml: null,
	
	_width: 0,
	_height: 0,
	
	_disabler: null,
	_disable: false,
	
	_builded: false,
	
	_tabindex: -1,

	_imgUploaderCont: null,
	_imgUploaderForm: null,
	_imgUploaderFile: null,

	SetPlainEditor: function (plainEditor, htmlSwitcher)
	{
		if (!this._builded) {
			return;
		}
		this._plainEditor = plainEditor;
		this._htmlSwitcher = htmlSwitcher;
		this.Replace();
		var obj = this;
		this._htmlSwitcher.onclick = function () {
			obj.SwitchHtmlMode(true);
			return false;
		};
	},
	
	SwitchHtmlMode: function (needConfirm)
	{
		var value;
		if (this._htmlMode) {
			if (this._designMode) {
				value = this.GetText();
				if ((Browser.IE || Browser.Opera) && value.length > 0) {
					value = value.ReplaceStr('<style> p { margin-top: 0px; margin-bottom: 0px; } </style>', '');
					value = value.ReplaceStr('<style> .misspel { background: url(skins/redline.gif) repeat-x bottom; display: inline; } </style>', '');
				}
			} else {
				value = this._waitHtml;
			}
			if (!needConfirm || confirm(Lang.ConfirmHtmlToPlain)) {
				value = HtmlDecode(value.replace(/<br *\/{0,1}>/gi, '\n').replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' '));
				this.SetText(value);
			}
		} else {
			value = HtmlEncode(this._plainEditor.value).replace(/\n/g, '<br/>').replace(/ /g, '&nbsp;');
			this.Show();
			this.SetHtml(value);
		}
	},
	
	LoadEditArea: function ()
	{
		this._loaded = true;
		this.DesignModeOn();
	},
	
	Disable: function ()
	{
		if (!this._designMode) {
			return;
		}
		if (this._loaded && this.shown) {
			if (this._disabler == null) {
				this._disabler = CreateChild(document.body, 'div');
			}
			this._disabler.className = '';
			this.ResizeDisabler();
			this._disable = true;
		}
	},
	
	Enable: function ()
	{
		if (!this._designMode) {
			return;
		}
		if (this._loaded && this.shown) {
			if (this._disabler != null) {
				this._disabler.className = 'wm_hide';
			}
			this._disable = false;
		}
	},
	
	ResizeDisabler: function ()
	{
		if (this._disabler != null) {
			var bounds = GetBounds(this._editor);
			this._disabler.style.position = 'absolute';
			this._disabler.style.left = bounds.Left + 'px';
			this._disabler.style.top = (bounds.Top - 1) + 'px';
			this._disabler.style.width = bounds.Width + 'px';
			this._disabler.style.height = bounds.Height + 'px';
			this._disabler.style.background = '#fff';
		}
	},
	
	SwitchOnRtl: function ()
	{
		if (window.RTL && Browser.IE && this._area != null &&
				this._area.document != null && this._area.document.body != null) {
			if (Browser.Version >= 7) {
				this._area.document.body.dir = 'rtl';
			} else {
				this._area.document.dir = 'rtl';
			}
		}
	},
	
	DesignModeOn: function ()
	{
		if (this._loaded && this.shown) {
			var doc = this._area.document;
			if (!Browser.IE) {
				doc = this._area.contentDocument;
			}
			try {
				doc.designMode = 'on';
				if (doc.designMode.toLowerCase() == 'on')	{
					this._designMode = true;
				}
			}
			catch (err) {}
			
			if (this._designMode && this._designModeStart) {
				this.SetWaitHtml();
			} else {
				this._designModeStart = true;
				setTimeout('DesignModeOnHandler();', 5);
			}
		}
	},

    ShowHtmlEditor: function ()
    {
		this._mainTbl.className = 'wm_html_editor';
		this._header.className = 'wm_html_editor_toolbar';
		this._editor.tabIndex = this._tabindex;
		this._htmlMode = true;
		this._htmlSwitcher.innerHTML = Lang.SwitchToPlainMode;
    },
    
    ShowPlainEditor: function ()
    {
		this._header.className = 'wm_hide';
		this._htmlMode = false;
		this._htmlSwitcher.innerHTML = Lang.SwitchToHTMLMode;
		this.Hide();
    },
    
	Show: function (tabindex, useInsertImage)
	{
		if (typeof useInsertImage == 'undefined') { useInsertImage = false; }
		if (!this._builded) {
			return;
		}
		this._colorMode = -1;
		this._mainTbl.className = 'wm_html_editor';
		if (this._editor == null) {
			var url, editor;
			url = (window.RTL) ? EditAreaUrl + '?rtl=1' : EditAreaUrl;
			editor = CreateChild(this._iframesContainer, 'iframe', [['src', url], ['frameborder', '0px'], ['id', 'EditorFrame']]);
			editor.className = 'wm_editor';
			this._editor = editor;
			this._editor.style.width = '100px';
			this._area = (Browser.IE) ? frames('EditorFrame') : editor;
		}
		this._btnInsertImage.className = (useInsertImage && WebMail.Settings.AllowInsertImage)
			? 'wm_toolbar_item' : 'wm_hide';
		
		if (tabindex) {
			this._tabindex = tabindex;
		}
		this.shown = true;
		if (!this._disable && this._disabler != null) {
			this._disabler.className = 'wm_hide';
		}
	},
	
	Hide: function ()
	{
		if (!this._builded) {
			return;
		}
		if (this.shown) {
			this._mainTbl.focus();
			this._editor.tabIndex = -1;
		}
		this.shown = false;
		this._mainTbl.className = 'wm_hide';
		this._colorPalette.className = 'wm_hide';
		this._imgUploaderCont.className = 'wm_hide';
	},
	
	Replace: function ()
	{
		if (!this._builded) {
			return;
		}
		if (this._plainEditor != null) {
			var bounds = GetBounds(this._plainEditor);
			this._mainTbl.style.position = 'absolute';
			this._mainTbl.style.left = (bounds.Left - 1) + 'px';
			this._mainTbl.style.top = (bounds.Top - 2) + 'px';
		}
		this.ResizeDisabler();
	},
	
	Resize: function (width, height)
	{
		if (!this._builded) {
			return;
		}
		if (typeof(width) != 'undefined' && typeof(height) != 'undefined') {
		    this._width = width;
		    this._height = height;
		} else {
		    width = this._width;
		    height = this._height;
		}
		if (this._plainEditor != null) {
			this._plainEditor.style.width = (width - 2) + 'px';
			this._plainEditor.style.height = (height - 1) + 'px';
		}
		this._mainTbl.style.width = (width + 20) + 'px';
		this._mainTbl.style.height = (height - 2) + 'px';
		if (this._editor != null) {
			this._editor.style.width = width + 'px';
			var offsetHeight = this._header.offsetHeight;
			if (offsetHeight && height - offsetHeight > 0) {
				this._editor.style.height = (height - offsetHeight) + 'px';
			}  else {
				this._editor.style.height = height + 'px';
			}
		}
		this.Replace();
	},

	SetText: function (txt) {
		if (!this._builded) {
			return;
		}
		this._plainEditor.value = txt;
		this._htmlMode = false;
		this._htmlSwitcher.innerHTML = Lang.SwitchToHTMLMode;
		this.ShowPlainEditor();
		SetCounterValueHandler();
	},
	
	SetWaitHtml: function () {
		if (this._waitHtml != null) {
			this.SetHtml(this._waitHtml);
		}
	},
	
	FillFontSelects: function ()
	{
		var fontName, fontSize;
		fontName = this._comValue('FontName');
		switch (fontName) {
		case false:
		case null:
		case '':
			fontName = this._defaulFontName;
			break;
		default:
			fontName = fontName.replace(/'/g, '');
			break;
		}
		fontSize = this._comValue('FontSize');
		switch (fontSize) {
		case '10px':
			fontSize = '1';
			break;
		case '13px':
			fontSize = '2';
			break;
		case '16px':
			fontSize = '3'; 
			break;
		case '18px':
			fontSize = '4';
			break;
		case '24px':
			fontSize = '5';
			break;
		case '32px':
			fontSize = '6';
			break;
		case '48px':
			fontSize = '7';
			break;
		case null:
		case '':
			fontSize = this._defaulFontSize;
			break;
		default:
			fontSize = parseInt(fontSize, 10);
			if (fontSize > 7) {
				fontSize = 7;
			} else if (fontSize < 1) {
				fontSize = 1;
			}
			break;
		}
		if (fontName && fontSize) {
			this._fontFaceSel.value = fontName;
			this._fontSizeSel.value = fontSize;
		}
	},
	
	_setDefaultFont: function ()
	{
		var doc = null;
		if (typeof this._area.document != 'undefined') {
			doc = this._area.document;
		} else if (typeof this._area.contentDocument != 'undefined') {
			doc = this._area.contentDocument;
		}
		if (doc != null) {
			doc.body.style.fontFamily = this._defaulFontName;
			this._fontFaceSel.value = this._defaulFontName;
			if (!Browser.Opera) {
				switch (this._defaulFontSize) {
				case '1': 
					doc.body.style.fontSize = '10px';
					break;
				default:
				case '2':
					doc.body.style.fontSize = '13px';
					break;
				case '3':
					doc.body.style.fontSize = '16px';
					break;
				case '4': 
					doc.body.style.fontSize = '18px';
					break;
				case '5': 
					doc.body.style.fontSize = '24px';
					break;
				case '6': 
					doc.body.style.fontSize = '32px';
					break;
				case '7': 
					doc.body.style.fontSize = '48px';
					break;
				}
				this._fontSizeSel.value = this._defaulFontSize;
			}
		}
	},
	
	_blur: function ()
	{
		if (Browser.IE || Browser.Opera) {
			this._area.blur();
		} else {
			this._editor.contentWindow.blur();
		}
	},
	
	Focus: function ()
	{
		if (this._disable) {
			return;
		}
		if (Browser.IE || Browser.Opera) {
			this._area.focus();
		} else {
			this._editor.contentWindow.focus();
		}
	},
	
	_setFontCheckers: function ()
	{
		var obj = this;
		if (this._editor.contentWindow && this._editor.contentWindow.addEventListener) {
			this._editor.contentWindow.addEventListener('mouseup', function () {
				obj.FillFontSelects();
				SetCounterValueHandler();
			}, false);
			this._editor.contentWindow.addEventListener('keyup', function () {
				obj.FillFontSelects();
				SetCounterValueHandler();
			}, false);
		} else if (Browser.IE) {
			this._area.document.onmouseup = function () {
				obj.FillFontSelects();
				SetCounterValueHandler();
			};
			this._area.document.onkeyup = function () {
				obj.FillFontSelects();
				SetCounterValueHandler();
			};
		}
		this._plainEditor.onmouseup = function () {
			SetCounterValueHandler();
		};
		this._plainEditor.onkeyup = function () {
			SetCounterValueHandler();
		};
	},

	SetHtml: function (txt) {
		if (!this._builded) {
			return;
		}
		if (this._designMode) {
			var styles = '';
			if (Browser.IE) {
				styles = '<style> .misspel { background: url(skins/redline.gif) repeat-x bottom; display: inline; } </style>';
				styles += '<style> p { margin-top: 0px; margin-bottom: 0px; } </style>';
				this._area.document.open();
				this._area.document.writeln(styles + txt);
				this._area.document.close();
				this.SwitchOnRtl();
			} else {
				this._area.contentDocument.body.innerHTML = styles + txt;
			}
			this._setDefaultFont();
			this._setFontCheckers();
			this._waitHtml = null;
			this.ShowHtmlEditor();
			this.Resize(this._width, this._height);
			SetCounterValueHandler();
		}
		else {
			this._waitHtml = txt;
			if (this._loaded) {
				this.DesignModeOn();
			}
		}
	},
	
	GetText: function () {
		if (!this._builded) {
			return false;
		}
		if (this._designMode) {
			if (Browser.IE) {
				var value = this._area.document.body.innerHTML;
				return value.replace(/<\/p>/gi, '<br />').replace(/<p>/gi, '');
			} else {
				var value = this._area.contentDocument.body.innerHTML;
				/*value = value.replace(/<\/pre>/gi, '<br />').replace(/<pre[^>]*>/gi, '');
				value = value.replace(/<\/code>/gi, '<br />').replace(/<code[^>]*>/gi, '');*/
                return value
			}
		}
		return false;
	},
	
	_comValue: function (cmd) {
		if (this._designMode) {
			if (typeof this._area.document != 'undefined') {
				return this._area.document.queryCommandValue(cmd);
			} else if (typeof this._area.contentDocument != 'undefined') {
				return this._area.contentDocument.queryCommandValue(cmd, false, null);
			}
		}
		return '';
	},

	_execCom: function (cmd, param) {
		if (this._designMode) {
			if (!Browser.Opera) {
				this.Focus();
			}
			if (Browser.IE) {
				if (param) {
					this._area.document.execCommand(cmd, false, param);
				} else {
					this._area.document.execCommand(cmd);
				}
			} else {
				if (param) {
					var res = this._area.contentDocument.execCommand(cmd, false, param);
				} else {
					this._area.contentDocument.execCommand(cmd, false, null);
				}
			}
			if (!Browser.Opera) {
				this.Focus();
			}
		}
	},

	CreateLink: function () {
		if (Browser.IE) {
			this._execCom('CreateLink');
		}
		else if (this._designMode) {
			var bounds, top;
			bounds = GetBounds(this._btnInsertLink);
			top = bounds.Top + bounds.Height;
			window.open('linkcreator.html', 'ha_fullscreen', 
				'toolbar=no,menubar=no,personalbar=no,width=380,height=100,left=' + bounds.Left + ',top=' + top + 
				'scrollbars=no,resizable=no,modal=yes,status=no');
		}
	},

	CreateLinkFromWindow: function (url) {
		this._execCom('createlink', url);
	},
	
	Unlink: function () {
		if (Browser.IE) {
			this._execCom('Unlink');
		} else if (this._designMode) {
			this._execCom('unlink');
		}
	},

	InsertImage: function () {
		if (!WebMail.Settings.AllowInsertImage) return;
		this._imgUploaderCont.className = 'wm_image_uploader_cont';
		this._rebuildUploadForm();
		var bounds = GetBounds(this._btnInsertImage);
		var iuStyle = this._imgUploaderCont.style;
        iuStyle.top = bounds.Top + bounds.Height + 'px';
        if (window.RTL) {
            iuStyle.right = GetWidth() - (bounds.Left + bounds.Width) + 'px';
        }
        else {
            iuStyle.left = bounds.Left + 'px';
        }
	},

	InsertImageFromWindow: function (url) {
		if (!WebMail.Settings.AllowInsertImage) return;
		this._imgUploaderCont.className = 'wm_hide';
		if (Browser.IE) {
			this._execCom('InsertImage', url);
		} else if (this._designMode) {
			this._execCom('insertimage', url);
		}
	},

	InsertOrderedList: function () {
		this._execCom('InsertOrderedList');
	},

	InsertUnorderedList: function () {
		this._execCom('InsertUnorderedList');
	},

	InsertHorizontalRule: function () {
		this._execCom('InsertHorizontalRule');
	},

	FontName: function (name) {
		this._fontFaceSel.value = name;
		this._execCom('FontName', name);
	},

	FontSize: function (size) {
		this._fontSizeSel.value = size;
		this._execCom('FontSize', size);
	},

	Bold: function () {
		this._execCom('Bold');
	},

	Italic: function () {
		this._execCom('Italic');
	},

	Underline: function () {
		this._execCom('Underline');
	},

	JustifyLeft: function () {
		this._execCom('JustifyLeft');
	},

	JustifyCenter: function () {
		this._execCom('JustifyCenter');
	},

	JustifyRight: function () {
		this._execCom('JustifyRight');
	},

	JustifyFull: function () {
		this._execCom('JustifyFull');
	},

	ChooseColor: function (mode)
	{
		if (this._designMode) {
			if (this._colorMode == mode) {
				this._colorPalette.className = 'wm_hide';
				this._colorChoosing = 0;
				this._colorMode = -1;
			} else {
				this._colorMode = mode;
				var bounds = GetBounds((mode == 0) ? this._btnFontColor : this._btnBgColor);
				this._colorPalette.style.left = bounds.Left + 'px';
				this._colorPalette.style.top = bounds.Top + bounds.Height + 'px';
				this._colorPalette.className = 'wm_color_palette';

				if (Browser.IE) {
					this._range = this._area.document.selection.createRange();
					this._colorPalette.style.height = this._colorTable.offsetHeight + 8 + 'px';
					this._colorPalette.style.width = this._colorTable.offsetWidth + 8 + 'px';
				} else {
					this._colorPalette.style.height = this._colorTable.offsetHeight + 'px';
					this._colorPalette.style.width = this._colorTable.offsetWidth + 'px';
				}
				this._colorChoosing = 2;
			}
		}
	},

	SelectFontColor: function (color)
	{
		if (this._designMode) {
			if (Browser.IE) {
				this._range.select();
				this._range.execCommand((this._colorMode == 0) ? 'ForeColor' : 'BackColor', false, color);
			} else {
				this._area.contentDocument.execCommand((this._colorMode == 0) ? 'ForeColor' : 'hilitecolor', false, color);
			}
			this._area.focus();
			this._colorPalette.className = 'wm_hide';
			this._colorMode = -1;
		}
	},
	
	ChangeLang: function ()
	{
		var key, but;
		if (!this._builded) {
			return;
		}
		for (key in this._buttons) {
			but = this._buttons[key];
			but.imgDiv.title = Lang[but.langField];
		}
	},
	
	_buttons: {
		'link': {x: 0 * X_ICON_SHIFT, y: 0 * X_ICON_SHIFT, langField: 'InsertLink'},
		'unlink': {x: 1 * X_ICON_SHIFT, y: 0 * X_ICON_SHIFT, langField: 'RemoveLink'},
		'number': {x: 2 * X_ICON_SHIFT, y: 0 * X_ICON_SHIFT, langField: 'Numbering'},
		'list': {x: 3 * X_ICON_SHIFT, y: 0 * X_ICON_SHIFT, langField: 'Bullets'},
		'hrule': {x: 4 * X_ICON_SHIFT, y: 0 * X_ICON_SHIFT, langField: 'HorizontalLine'},
		'bld': {x: 5 * X_ICON_SHIFT, y: 0 * X_ICON_SHIFT, langField: 'Bold'},
		'itl': {x: 6 * X_ICON_SHIFT, y: 0 * X_ICON_SHIFT, langField: 'Italic'},
		'undrln': {x: 7 * X_ICON_SHIFT, y: 0 * X_ICON_SHIFT, langField: 'Underline'},
		'lft': {x: 8 * X_ICON_SHIFT, y: 0 * X_ICON_SHIFT, langField: 'AlignLeft'},
		'cnt': {x: 9 * X_ICON_SHIFT, y: 0 * X_ICON_SHIFT, langField: 'Center'},
		'rt': {x: 10 * X_ICON_SHIFT, y: 0 * X_ICON_SHIFT, langField: 'AlignRight'},
		'full': {x: 11 * X_ICON_SHIFT, y: 0 * X_ICON_SHIFT, langField: 'Justify'},
		'font_color': {x: 0 * X_ICON_SHIFT, y: 1 * X_ICON_SHIFT, langField: 'FontColor'},
		'bg_color': {x: 1 * X_ICON_SHIFT, y: 1 * X_ICON_SHIFT, langField: 'Background'},
		'spell': {x: 2 * X_ICON_SHIFT, y: 1 * X_ICON_SHIFT, langField: 'Spellcheck'},
		'insert_image': {x: 10 * X_ICON_SHIFT, y: 1 * X_ICON_SHIFT, langField: 'InsertImage'}
	},
	
	_addToolBarItem: function (parent, imgIndex)
	{
		var child, cdiv, desc, imgDiv;
		child = CreateChild(parent, 'a', [['href', 'javascript:void(0);']]);
		cdiv = CreateChild(child, 'div');
		
		cdiv.className = 'wm_toolbar_item';
		cdiv.onmouseover = function () {
			this.className = 'wm_toolbar_item_over'; 
		};
		cdiv.onmouseout = function () {
			this.className = 'wm_toolbar_item';
		};
		desc = this._buttons[imgIndex];
		imgDiv = CreateChild(cdiv, 'div', [['title', Lang[desc.langField]],
			['style', 'background-position: -' + desc.x + 'px -' + desc.y + 'px']]);
		this._buttons[imgIndex].imgDiv = imgDiv;
		
		return cdiv;
	},
	
	_addToolBarSeparate: function (parent)
	{
		var child = CreateChild(parent, 'div');
		child.className = 'wm_toolbar_separate';
		return child;
	},
	
	ClickBody: function ()
	{
		if (!this._builded) {
			return;
		}
		switch (this._colorChoosing) {
		case 2:
			this._colorChoosing = 1;
			break;
		case 1:
			this._colorChoosing = 0;
			this._colorPalette.className = 'wm_hide';
			this._colorMode = -1;
			break;
		}
	},
	
	SetCurrentColor: function (color) {
		this._currentColor.style.backgroundColor = color;
	},

	_rebuildUploadForm: function ()
	{
		if (!WebMail.Settings.AllowInsertImage) return;
		var form = this._imgUploaderForm;
		CleanNode(form);
		var tbl = CreateChild(form, 'table');
		var tr = tbl.insertRow(0);
		var td = tr.insertCell(0);
		var span = CreateChild(td, 'span');
		span.innerHTML = Lang.ImagePath + ': ';
		var br = CreateChild(td, 'br');
		var inp = CreateChild(td, 'input', [['type', 'file'], ['class', 'wm_file'], ['name', 'Filedata']]);
		this._imgUploaderFile = inp;
		inp = CreateChild(td, 'input', [['type', 'hidden'], ['value', '1'], ['name', 'inline_image']]);
		inp = CreateChild(form, 'input', [['type', 'hidden'], ['value', '0'], ['name', 'flash_upload']]);
		
		td = tr.insertCell(1);
		inp = CreateChild(td, 'input', [['type', 'submit'], ['class', 'wm_button'], ['value', Lang.ImageUpload]]);
		br = CreateChild(td, 'br');
		inp = CreateChild(td, 'input', [['type', 'button'], ['class', 'wm_button'], ['value', Lang.Cancel]]);
		var obj = this;
		inp.onclick = function () { obj._imgUploaderCont.className = 'wm_hide'; };
	},
	
	_buildUploadForm: function ()
	{
		this._imgUploaderCont = CreateChild(document.body, 'div', [['class', 'wm_hide']]);
		this._imgUploaderForm = CreateChild(this._imgUploaderCont, 'form', [['action', UploadUrl], ['method', 'post'], ['enctype', 'multipart/form-data'], ['target', 'UploadFrame'], ['id', 'ImageUploadForm']]);
		var obj = this;
		this._imgUploaderForm.onsubmit = function () {
			if (!WebMail.Settings.AllowInsertImage) return false;
			if (obj._imgUploaderFile.value.length == 0) return false;
			var ext = GetExtension(obj._imgUploaderFile.value);
			switch (ext) {
				case 'jpg':
				case 'jpeg':
				case 'png':
				case 'bmp':
				case 'gif':
				case 'tif':
				case 'tiff':
					break;
				default:
					alert(Lang.WarningImageUpload);
					return false;
			}
			return true;
		};
	},
	
	_buildColorPalette: function ()
	{
		var div = CreateChild(document.body, 'div');
		div.className = 'wm_hide';
		this._colorPalette = div;
		var tbl = CreateChild(div, 'table');
		this._colorTable = tbl;
		var rowIndex = 0;
		var colors = ['#000000', '#333333', '#666666', '#999999', '#CCCCCC', '#FFFFFF', '#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#00FFFF', '#FF00FF'];
		var colorIndex = 0;
		var symbols = ['00', '33', '66', '99', 'CC', 'FF'];
		var obj = this;
		for (var jStart = 0; jStart < 6; jStart += 3) {
			for (var i = 0; i < 6; i++) {
				var tr = tbl.insertRow(rowIndex++);
				var cellIndex = 0;
				var td;
				if (rowIndex == 1) {
					td = tr.insertCell(cellIndex++);
					td.rowSpan = 12;
					td.className = 'wm_current_color_td';
					this._currentColor = CreateChild(td, 'div');
					this._currentColor.className = 'wm_current_color';
				}
				td = tr.insertCell(cellIndex++);
				td.className = 'wm_palette_color';
				td = tr.insertCell(cellIndex++);
				td.bgColor = colors[colorIndex++];
				td.className = 'wm_palette_color';
				td.onmouseover = function () {
					obj.SetCurrentColor(this.bgColor);
				};
				td.onclick = function () {
					obj.SelectFontColor(this.bgColor);
				};
				td = tr.insertCell(cellIndex++);
				td.className = 'wm_palette_color';
				for (var j = jStart; j < jStart + 3; j++) {
					for (var k = 0; k < 6; k++) {
						td = tr.insertCell(cellIndex++);
						td.bgColor = '#' + symbols[j] + symbols[k] + symbols[i];
						td.className = 'wm_palette_color';
						td.onmouseover = function () {
							obj.SetCurrentColor(this.bgColor);
						};
						td.onclick = function () {
							obj.SelectFontColor(this.bgColor);
						};
					}
				}
			}
		}
	}, //_buildColorPalette
	
	Build: function (disableSpellChecker)
	{
		var tbl, tr, td, div, obj, fontFaceSel, fontSizeSel, i, opt;
		if (this._builded) {
			return;
		}
		obj = this;
		tbl = CreateChild(document.body, 'table');
		this._mainTbl = tbl;
		tbl.className = 'wm_hide';
		tr = tbl.insertRow(0);
		this._header = tr;
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		this._btnInsertLink = this._addToolBarItem(td, 'link');
		this._btnInsertLink.onclick = function () {
			obj.CreateLink();
		};
		div = this._addToolBarItem(td, 'unlink');
		div.onclick = function () {
			obj.Unlink();
		};
		this._btnInsertImage = this._addToolBarItem(td, 'insert_image');
		this._btnInsertImage.onclick = function () {
			obj.InsertImage();
		};
		div = this._addToolBarItem(td, 'number');
		div.onclick = function () {
			obj.InsertOrderedList();
		};
		div = this._addToolBarItem(td, 'list');
		div.onclick = function () {
			obj.InsertUnorderedList(); 
		};
		div = this._addToolBarItem(td, 'hrule');
		div.onclick = function () {
			obj.InsertHorizontalRule(); 
		};
		div = this._addToolBarSeparate(td);

		div = CreateChild(td, 'div');
		div.className = 'wm_toolbar_item';
		fontFaceSel = CreateChild(div, 'select');
		fontFaceSel.className = 'wm_input wm_html_editor_select';
		for (i in Fonts) {
			opt = CreateChild(fontFaceSel, 'option', [['value', Fonts[i]]]);
			opt.innerHTML = Fonts[i];
			if (Fonts[i] == this._defaulFontName) {
				opt.selected = true;
			}
		}
		fontFaceSel.onchange = function () {
			obj.FontName(this.value);
		};
		this._fontFaceSel = fontFaceSel;
		div.style.margin = '0px';
		
		div = CreateChild(td, 'div');
		div.className = 'wm_toolbar_item';
		fontSizeSel = CreateChild(div, 'select');
		fontSizeSel.className = 'wm_input wm_html_editor_select';
		for (i = 1; i < 8; i++) {
			opt = CreateChild(fontSizeSel, 'option', [['value', i]]);
			opt.innerHTML = i;
			if (i == this._defaulFontSize) {
				opt.selected = true;
			}
		}
		fontSizeSel.onchange = function () {
			obj.FontSize(this.value); 
		};
		this._fontSizeSel = fontSizeSel;
		div.style.margin = '0px';
		
		div = this._addToolBarSeparate(td);
		div = this._addToolBarItem(td, 'bld');
		div.onclick = function () { 
			obj.Bold(); 
		};
		div = this._addToolBarItem(td, 'itl');
		div.onclick = function () { 
			obj.Italic();
		};
		div = this._addToolBarItem(td, 'undrln');
		div.onclick = function () { 
			obj.Underline(); 
		};
		div = this._addToolBarItem(td, 'lft');
		div.onclick = function () { 
			obj.JustifyLeft(); 
		};
		div = this._addToolBarItem(td, 'cnt');
		div.onclick = function () { 
			obj.JustifyCenter(); 
		};
		div = this._addToolBarItem(td, 'rt');
		div.onclick = function () {
			obj.JustifyRight(); 
		};
		div = this._addToolBarItem(td, 'full');
		div.onclick = function () { 
			obj.JustifyFull(); 
		};
		this._btnFontColor = this._addToolBarItem(td, 'font_color');
		this._btnFontColor.onclick = function () { 
			obj.ChooseColor(0); 
		};
		this._btnBgColor = this._addToolBarItem(td, 'bg_color');
		this._btnBgColor.onclick = function () { 
			obj.ChooseColor(1); 
		};
		
		if (!disableSpellChecker) {
			div = this._addToolBarSeparate(td);
			div = this._addToolBarItem(td, 'spell');
			div.onclick = function () {
				SpellCheck();
			};
		}
		
		tr = tbl.insertRow(1);
		td = tr.insertCell(0);
		td.className = 'wm_html_editor_cell';
		td.colSpan = 1;
		this._iframesContainer = td;
		
		this._buildColorPalette();
		this._buildUploadForm();
		this._builded = true;
	}, //Build
	
	CleanSpell_IE: function ()
	{
		if (this._area.document.selection && this._area.document.selection.createRange()) {
			var range, cursorRange, cursorPos, ghostElement, element, textNode, elParent, zeta;
			range = this._area.document.selection.createRange();
			
			// getting a cursor position
			cursorRange = range.duplicate();
			cursorRange.moveStart('textedit', -1);
			cursorPos = cursorRange.text.length; 

			range.pasteHTML('<span id="#31337" />');
			ghostElement = this._area.document.getElementById('#31337');
			element = ghostElement.parentNode;
			element.removeChild(ghostElement);
			if (element.className == 'misspel') {
				textNode = this._area.document.createTextNode(element.innerHTML);
				elParent = element.parentNode;
				if (element.nextSibling != null) {
					elParent.insertBefore(textNode, element.nextSibling);
				}
				elParent.removeChild(element);
				
				// moving cursor to last position
				range.moveStart('textedit', -1);
				zeta = cursorPos - range.text.length;
				range = this._area.document.selection.createRange();
				range.move('character', zeta);
				range.select();
			}
	    }
	},
	
	CleanSpell_Gecko: function () 
	{
		var sel, range, focusOffset, element, parent, newText, repIn;
		sel = this._area.contentWindow.getSelection();
		range = sel.getRangeAt(0);
		focusOffset = sel.focusOffset; 
		if (range.collapsed) {
			element = range.commonAncestorContainer;
			if (element && element.parentNode) {
				parent = element.parentNode;
				if (parent.className == 'misspel') {
					newText = this._area.contentDocument.createTextNode(element.nodeValue); 
					repIn = parent.parentNode;
					repIn.replaceChild(newText, parent);
					sel.collapse(newText, focusOffset);
				}
			}
		}
	},

	UpdateEditorHandlers : function (eventFunction, eventsList)
	{
		var doc = Browser.IE ? HtmlEditorField._area.document : HtmlEditorField._area.contentWindow;
		for (var i in eventsList)
		{
			$addHandler(doc, eventsList[i],  eventFunction);
		}
	}	
};

function MisspelCliq() 
{
	var spell, lastWord, popupDiv, xml, bounds, ifr_bounds, browserDoc, scrollY;
	spell = WebMail._spellchecker;
	lastWord = spell.currentWord;
	spell.currentWord = this.innerHTML;
	spell.misElement = this;
	popupDiv = WebMail._spellchecker.popupDiv;
	if (spell.suggestWait) { 
		WebMail._spellchecker.DataSource.NetLoader.CheckRequest(); 
	}
	if (spell.misGetWords[spell.currentWord]) {
		CleanNode(popupDiv);
		if (spell.misGetWords[spell.currentWord].length == 0) {
			spell.popupShow(Lang.SpellNoSuggestions);
		} else {
			popupDiv.appendChild(spell.suggestionTable(spell.misGetWords[spell.currentWord]));
		}
		spell.suggestWait = false;
	}
	else {
		if (spell.currentWord != lastWord) {
			spell.suggestWait = true;
			xml = '<param name="action" value="spellcheck" /><param name="request" value="suggest" />';
			xml += '<word>' + GetCData(spell.currentWord, false) + '</word>';
			WebMail._spellchecker.DataSource.Request([], xml);
			spell.popupShow(Lang.SpellWait);
		}
	}
	WebMail._spellchecker.popupDiv.className = 'spell_popup_show';
	bounds = GetBounds(this);
	ifr_bounds = GetBounds(HtmlEditorField._editor);
	browserDoc = Browser.IE ? HtmlEditorField._area.document : HtmlEditorField._area.contentDocument;
	scrollY = WebMail._spellchecker.getScrollY(browserDoc);
	popupDiv.style.top = (bounds.Top + ifr_bounds.Top - scrollY + 20) + 'px';
	popupDiv.style.left = (bounds.Left + ifr_bounds.Left) + 'px';
}

function getCodeAndWhich(ev) {
	var key, inst, which;
	key = -1;
	if  (Browser.IE) { 
		inst = HtmlEditorField._area; 
		if (inst.window.event) {
			key = inst.window.event.keyCode;
			which = key;
		}
	} else if (ev) {
		key = ev.keyCode;
		which = ev.which;
	}
	return { k: key, w: which };
}

function isTextChanged(ev) {
	var kw, key, which;
	kw = getCodeAndWhich(ev);
	key = kw.k;
	which = kw.w;

	return (!(ev.ctrlKey==1 || ev.altKey==1) && //check pressed Alt or Ctrl when another key was pressed.
			key != 16 &&				//shift
			key != 17 &&				//ctrl
			key != 18 &&				//alt
			key != 35 &&				//end
			key != 36 &&				//home
			key != 37 &&				//to the right
			key != 38 &&				//up
			key != 39 &&				//to the left
			key != 40 ||				//down
			(key == 0 && which != 0));	// FireFox;
}

function EditKeyHandle(ev) {
	if (isTextChanged(ev)) {
		if (Browser.IE) {
			HtmlEditorField.CleanSpell_IE();
		} else {
			HtmlEditorField.CleanSpell_Gecko();
		}
	}
}

function AddMisspelEvents() 
{
	var childs, HtmlEditor, node, doc;
	HtmlEditor = HtmlEditorField;
	if (Browser.IE) {
		childs = HtmlEditor._area.document.getElementsByTagName('span');
		HtmlEditor._area.document.onmousedown = function () {
			WebMail._spellchecker.popupHide();
		};
		HtmlEditor._area.document.body.onscroll = function () {};
		HtmlEditor._area.document.body.onscroll = function () {
			WebMail._spellchecker.popupRecalcCoords();
		};
	} else {
		childs = HtmlEditor._area.contentDocument.getElementsByTagName('span');
		HtmlEditor._area.contentDocument.addEventListener('mousedown', function () {
			WebMail._spellchecker.popupHide();
		}, false);
		HtmlEditor._area.contentDocument.addEventListener('scroll', function () {
			WebMail._spellchecker.popupRecalcCoords(); 
		}, false);
	}
	for (i = 0; i < childs.length; i++) {
		node = childs.item(i);
		if (node.className && node.className == 'misspel') {
			if (Browser.IE) {
				node.onclick = MisspelCliq;
			} else {
				node.addEventListener('click', MisspelCliq, false);
			}
		}
	}
	
	doc = Browser.IE ? HtmlEditor._area.document : HtmlEditor._area.contentDocument;
	if (doc.addEventListener) {
		doc.addEventListener('keypress', EditKeyHandle, true); 
	} else if (doc.attachEvent) {
		doc.attachEvent('onkeydown', EditKeyHandle);
	}
}

function ReplaceWord() {
	var strWord, doc, newTextNode, misElement, elParent, text;

	strWord = Browser.IE ? this.innerText : this.textContent;
	doc = Browser.IE ? HtmlEditorField._area.document : HtmlEditorField._area.contentDocument;
	newTextNode = doc.createTextNode(strWord);
	misElement = WebMail._spellchecker.misElement;
	elParent = misElement.parentNode;
	elParent.replaceChild(newTextNode, misElement);
	text = HtmlEditorField.GetText();
	WebMail._spellchecker.suggestWait = false;
	WebMail._spellchecker.popupHide();
}

function SpellCheck() {
	var request, ReadyStateComplete, text;
	request = WebMail._spellchecker.DataSource.NetLoader.Request;
	ReadyStateComplete = 4;
	if (!WebMail._spellchecker.misspelWait || request.readyState == ReadyStateComplete) {
		WebMail._spellchecker.misGetWords = [];
		WebMail._spellchecker.misspelWait = true;
		text = HtmlEditorField.GetText();
		text = WebMail._spellchecker.StripMissTags(text);
		xml = '<param name="action" value="spellcheck" /><param name="request" value="spell" />';
		xml += '<text>' + GetCData(text, true) + '</text>';
		WebMail._spellchecker.DataSource.Request([], xml);
		
	} 
}

function ShowLoadingHandler() {
	if (!WebMail._spellchecker.suggestWait) {
		WebMail.ShowInfo(Lang.Loading);
	}
}

function CSpellchecker() 
{
	this.misspelPos = [];
	this.misspelWait = false;
	this.suggestion = [];
	this.misElement = null;
	this.suggestWait = false;
	this.misGetWords = [];
	this.currentWord = '';
	this.popupDiv = document.getElementById('spell_popup_menu');
	this.DataSource = new CDataSource([], SpellcheckerUrl, ErrorHandler, LoadHandler, TakeDataHandler, ShowLoadingHandler);
}

CSpellchecker.prototype = {
	StrokeIt: function (word) 
	{
		return (word) ? '<span class="misspel">' + word + '</span>' : '';
	},
	
	// misspel is Array
	StrokeText: function (misspel, text) 
	{
		var newText, lastPos, i, misPos, misLen, begin, misWord;
		newText = '';
		lastPos = 0;
		if (text && misspel) {
			for (i = 0; i < misspel.length; i++) {
				misPos = misspel[i][0];
				misLen = misspel[i][1];
				begin = text.substring(lastPos, misPos);
				misWord = text.substring(misPos, misPos + misLen);
				newText = newText + begin + this.StrokeIt(misWord);
				lastPos = misPos + misLen;
			}
			newText += text.substring(lastPos, text.length);
		}
		return newText;
	},
	
	StripMissTags: function (text) {
		var resText, rep, repIE, inText;
		
		resText = text;
		rep = /<span class="misspel">(.*?)<\/span>/i;
		repIE = /<span class=misspel>(.*?)<\/span>/i;

		if (Browser.IE) {
			inText = repIE.exec(resText);
			while (inText != null) {
				resText = resText.replace(repIE, inText[1]);
				inText = repIE.exec(resText);
			}
		} else {
			inText = rep.exec(resText);
			while (inText != null) {
				resText = resText.replace(rep, inText[1]);
				inText = rep.exec(resText);
			}
		}
		return resText;
	},
	
	popupHide: function (caller) {
		if (caller && caller == 'document') {
			if (this.popupVisible()) {
				if (this.suggestWait) {
					this.DataSource.NetLoader.CheckRequest();
					this.suggestWait = false;
					this.currentWord = '';
				}
				this.popupDiv.className = 'spell_popup_hide';
			}
		} else {
			if (!this.suggestWait && this.popupVisible()) {
				this.popupDiv.className = 'spell_popup_hide';
			} 
		}
	},
	
	popupShow: function (text) {
		if (text) {
			CleanNode(this.popupDiv);
			var textNode = document.createElement('div');
			textNode.innerHTML = text;
			textNode.className = 'spell_spanDeactive';
			this.popupDiv.appendChild(textNode);	
			this.popupDiv.className = 'spell_popup_show';
		} else if (!this.popupVisible()) {
			this.popupDiv.className = 'spell_popup_show';
		}
	},
	
	popupVisible: function () {
		return (this.popupDiv.className == 'spell_popup_show') ? true : false;
	},
	
	popupRecalcCoords: function () {
		if (this.misElement) {
			var browserDoc, scrollY, bounds, ifr_bounds;
			browserDoc = (Browser.IE) ? HtmlEditorField._area.document : HtmlEditorField._area.contentDocument;
			scrollY = this.getScrollY(browserDoc);
			bounds = GetBounds(this);
			ifr_bounds = GetBounds(HtmlEditorField._area);
			this.popupDiv.style.top = (bounds.Top + ifr_bounds.Top - scrollY + 20) + 'px';
			this.popupDiv.style.left = (bounds.Left + ifr_bounds.Left) + 'px';
		}
	},
	
	suggestionTable: function (suggestions) {
		var sugTable, sugTBody, i, sugTRow, sugNode;
		sugTable = document.createElement('TABLE');
		sugTable.style.width = '180px';
		sugTBody = document.createElement('TBODY');
		sugTable.appendChild(sugTBody);
		for (i = 0; i < suggestions.length; i++) {
			sugTRow =  document.createElement('TR');
			sugNode = document.createElement('TD');
			if (Browser.IE) {
				sugNode.innerText = suggestions[i];
				sugNode.onclick = ReplaceWord;
				sugNode.onmouseover = this.Menu_hightlight_on;
				sugNode.onmouseout = this.Menu_hightlight_off;
			} else {
				sugNode.textContent = suggestions[i];
				sugNode.addEventListener('mouseover', this.Menu_hightlight_on, false);
				sugNode.addEventListener('mouseout', this.Menu_hightlight_off, false);
				sugNode.addEventListener('click', ReplaceWord, false);
			}
			sugNode.className = 'spell_spanDeactive';
			sugTBody.appendChild(sugTRow).appendChild(sugNode);
		}
		return sugTable;
	},
	
	getScrollY: function (doc) {
		var scrollY = 0;
		if (doc.body && typeof doc.body.scrollTop != 'undefined') {
			scrollY += doc.body.scrollTop;
			if (scrollY == 0 && doc.body.parentNode && typeof doc.body.parentNode != 'undefined') {
				scrollY += doc.body.parentNode.scrollTop;
			}
		} else if (typeof window.pageXOffset != 'undefined') {
			scrollY += window.pageYOffset;
		}
		return scrollY;
	},
	
	GetFromXML: function (RootElement) {
		var HtmlEditor, action, SpellParts, text, i, newText, suggestNode, childs, word, SuggestWords, s, errorStr;
		HtmlEditor = HtmlEditorField;
		action = RootElement.getAttribute('action');
		SpellParts = RootElement.childNodes;
		if (action == 'spellcheck') {
			text = HtmlEditor.GetText();
			text = this.StripMissTags(text);
			
			this.misspelPos = [];
			for (i = 0; i < SpellParts.length; i++) {
				mispNode = SpellParts.item(i);
				if (mispNode.nodeName == 'misp') {
					misPos = mispNode.getAttribute('pos');
					misLen = mispNode.getAttribute('len');
					this.misspelPos[this.misspelPos.length] = [(misPos - 0), (misLen - 0)];
				}
			}
			newText = this.StrokeText(this.misspelPos, text);
			HtmlEditor.SetHtml(newText);
			WebMail._spellchecker.misspelWait = false;
			AddMisspelEvents();
		}
		else if (action == 'suggest') {
			this.suggestion = [];
			suggestNode = [];
			for (i = 0; i < SpellParts.length; i++) {
				suggestNode = SpellParts.item(i);
				if (suggestNode.nodeName == 'word') {
					childs = suggestNode.childNodes;
					word = (childs.length > 0) ? Trim(childs[0].nodeValue) : '';
					this.suggestion[this.suggestion.length] = word;
				}
			}
			s = '';
			SuggestWords = [];
			for (i = 0; i < this.suggestion.length; i++) {
				s = s +  this.suggestion[i] + ' ';
				SuggestWords[i] = this.suggestion[i];
			}
			WebMail._spellchecker.misGetWords[WebMail._spellchecker.currentWord] = SuggestWords;
			
			CleanNode(this.popupDiv);
			if (this.suggestion.length > 0) {
				this.popupDiv.appendChild(this.suggestionTable(this.suggestion));
			} else {
				this.popupShow(Lang.SpellNoSuggestions);
			}
			this.popupShow();
			WebMail._spellchecker.suggestWait = false;  
		} else if (action == 'error') {
			errorStr = RootElement.getAttribute('errorstr');
			WebMail._errorObj.Show(errorStr);
			WebMail._spellchecker.suggestWait = false;
			WebMail._spellchecker.misspelWait = false;			
		}
	}, 
	
	Menu_hightlight_on: function () {
		this.className = 'spell_spanActive';
	},
	
	Menu_hightlight_off: function () {
		this.className = 'spell_spanDeactive';
	}
};

/* html editor handlers */
function EditAreaLoadHandler() {
	HtmlEditorField.LoadEditArea();
}

function CreateLinkHandler(url) {
	HtmlEditorField.CreateLinkFromWindow(url);
}

function InsertImageHandler(url) {
	HtmlEditorField.InsertImageFromWindow(url);
}

function DesignModeOnHandler(mode) {
	HtmlEditorField.DesignModeOn();
}
/*-- html editor handlers */

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}