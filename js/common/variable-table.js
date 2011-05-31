/*
 * Classes:
 *  CVariableColumn(id, params, selection)
 *  CVariableTable(sortHandler, selection, dragNDrop, controller)
 *  CCheckBoxCell()
 *  CImageCell(className, id, content)
 *  CTextCell(text, title)
 *  CSelection(fillSelectedContactsHandler)
 *  CDragNDrop(langField)
 */

function CVariableColumn(id, params, selection)
{
	this.Id = -1;
	this.Field = '';
	this._langField = '';
	this._imgClassName = '';
	this._langNumber = -1;

	this.SortField = SORT_FIELD_NOTHING;
	this.SortOrder = SORT_ORDER_ASC;
	this.Sorted = false;
	this._sortIconPlace = 2;
	this._sortHandler = null;
	this._freeSort = false;

	this.Align = 'center';
	this.Width = 100;
	this.MinWidth = 100;
	this._left = 0;
	this._padding = 2;

	this._htmlElem = null;
	this.LineElem = null;

	this._isResize = false;
	this.Resizer = null;
	this._separator = null;
	this.isLast = false;
	this._resizerWidth = 3;

	this.filled = false;
	this.CheckBox = null;
	if (id == IH_CHECK || id == CH_CHECK) {
		this._isCheckBox = true;
		this._selection = selection;
	}
	else {
		this._isCheckBox = false;
		this._selection = null;
	}
	this.ChangeField(id, params, false);
}

CVariableColumn.prototype = 
{
	ChangeField: function (id, params, setContent)
	{
		this.Id = id;
		this.Field = 'f' + params.DisplayField;
		this._langField = params.LangField;
		this._imgClassName = params.Picture;
		this.SortField = params.SortField;
		this._sortIconPlace = params.SortIconPlace;
		if (params.Align == 'left' || params.Align == 'center' || params.Align == 'right') {
			this.Align = params.Align;
		}
		else {
			this.Align = 'center';
		}
		if (this.filled == false) {
			this.Width = params.Width;
			this.filled = true;
		}
		this.MinWidth = params.MinWidth;
		this._isResize = params.IsResize;
        if (setContent) {
            this.SetContent();
        }
	},
	
	SetContent: function ()
	{
		var contentNode = null;
		if (this._isCheckBox) {
			contentNode = document.createElement('input');
			contentNode.type = 'checkbox';
			this.CheckBox = contentNode;
			this._selection.SetCheckBox(this.CheckBox);
		}
		else if (this._langField.length > 0) {
			contentNode = document.createTextNode(Lang[this._langField]);
		}
		else if (this._imgClassName.length > 0) {
			contentNode = document.createElement('div');
			contentNode.className = this._imgClassName;
		}
		CleanNode(this._htmlElem);
		var nobr = CreateChild(this._htmlElem, 'nobr');
		if (this.Sorted) {
			var sortNode = document.createElement('span');
			sortNode.innerHTML = '&nbsp;';
			if (SORT_ORDER_ASC == this.SortOrder) {
				sortNode.className = 'wm_inbox_lines_sort_asc';
			}
			else {
				sortNode.className = 'wm_inbox_lines_sort_desc';
			}
			switch (this._sortIconPlace) {
			case 0:
				nobr.appendChild(sortNode);
				if (null != contentNode) {
					nobr.appendChild(contentNode);
				}
			break;
			case 1:
				nobr.appendChild(sortNode);
			break;
			case 2:
				if (null != contentNode) {
					nobr.appendChild(contentNode);
				}
				nobr.appendChild(sortNode);
			break;
			}
		}
		else {
			if (null != contentNode) {
				nobr.appendChild(contentNode);
			}
		}
	},
	
	RemoveSort: function ()
	{
		this.SortOrder = 1 - this.SortOrder;
		this.Sorted = false;
		this.SetContent();
	},
	
	SetSort: function (sortOrder)
	{
		this.SortOrder = sortOrder;
		this.Sorted = true;
		this.SetContent();
	},
	
	SetWidth: function (width)
	{
		var newWidth = width - 2*this._padding - this._resizerWidth;
		if (newWidth < 0) {
			this._htmlElem.className = 'wm_hide';
		}
		else {
			if (this._freeSort || this.SortField == SORT_FIELD_NOTHING) {
				if (this._isCheckBox) {
					this._htmlElem.className = 'wm_inbox_header wm_inbox_headers_checkbox';
				}
				else {
					this._htmlElem.className = 'wm_inbox_header';
				}
			}
			else {
				this._htmlElem.className = 'wm_inbox_header wm_control';
			}
			if (this.Width != width) {
				this.Width = width;
				this._htmlElem.style.width = newWidth + 'px';
				if (this.LineElem != null) {
					this.LineElem.style.width = newWidth + this._resizerWidth + 'px';
				}
				CreateCookie('wm_column_' + this.Id, width, COOKIE_STORAGE_DAYS);
			}
		}
	},
	
	ResizeWidth: function ()
	{
		if (window.RTL) {
			var width = this.Width + this._left - this.Resizer.LeftPosition;
			this.SetWidth(width);
			this._left = this.Resizer.LeftPosition;
			this._htmlElem.style.left = this._left + 'px';
			return this._left;
		}
		else {
			var width = this.Resizer.LeftPosition - this._left + this._resizerWidth;
			this.SetWidth(width);
			return this.Resizer.LeftPosition + this._resizerWidth;
		}
	},
	
	ResizeLeft: function (left)
	{
		if (this.isLast) {
			this.SetWidth(this.Width + this._left - left);
		}
		this._left = left;
		this._htmlElem.style.left = left + 'px';
		if (null != this.Resizer) {
			this.Resizer.updateLeftPosition(left + this.Width - this._resizerWidth);
		}
		if (this.isLast) {
			return left;
		}
		else {
			return left + this.Width;
		}
	},
	
	// only for rtl
	ResizeRight: function (right, isLast)
	{
		if (isLast) {
			this.SetWidth(right - this._left);
		}
		this._left = right - this.Width;
		this._htmlElem.style.left = (this._left + this._resizerWidth) + 'px';
		if (null != this.Resizer) {
			this.Resizer.updateLeftPosition(this._left);
		}
		else if (null != this._separator) {
			this._separator.style.left = (this._left) + 'px';
		}
		return this.Width;
	},
	
	FreeSort: function ()
	{
		if (this._isCheckBox) {
			this._htmlElem.className = 'wm_inbox_header wm_inbox_headers_checkbox';
		}
		else {
			this._htmlElem.className = 'wm_inbox_header';
		}
		this._htmlElem.onclick = function () {};
		if (this.Sorted) {
			this.RemoveSort();
		}
		this._freeSort = true;
	},
	
	UseSort: function ()
	{
		if (this.SortField != SORT_FIELD_NOTHING) {
			this._htmlElem.className = 'wm_inbox_header wm_control';
		}
		if (this._sortHandler != null) {
			var obj = this;
			this._htmlElem.onclick = function () { obj._sortHandler.call({SortField: obj.SortField, SortOrder: 1-obj.SortOrder}); };
		}
		this._freeSort = false;
	},
	
	Build: function (parent, xleft, isLast, resizeHandler, sortHandler)
	{
		this._parent = parent;
		this.isLast = isLast;
		var child = CreateChild(parent, 'div');
		child.className = 'wm_inbox_header';
		if (SORT_FIELD_NOTHING != this.SortField) {
			child.className = 'wm_inbox_header wm_control';
			this._sortHandler = sortHandler;
			var obj = this;
			child.onclick = function () {
				sortHandler.call({SortField: obj.SortField, SortOrder: 1-obj.SortOrder}); 
			};
		}
		if (this._isCheckBox) {
			child.className = 'wm_inbox_header wm_inbox_headers_checkbox';
		}
		
		child.style.textAlign = this.Align;
		child.style.paddingLeft = '2px';
		child.style.paddingRight = '2px';
		child.style.width = (this.Width - 2*this._padding - this._resizerWidth) + 'px';
		child.style.left = xleft + 'px';
		child.style.overflow = 'hidden';
		
		this._left = xleft;
		this._htmlElem = child;
		this.SetContent();
		if (!isLast) {
			child = CreateChild(parent, 'div');
			child.className = 'wm_inbox_headers_separate';

			var left;
			if (window.RTL) {
				left = (xleft - this._resizerWidth) + 'px';
			} else {
				left = (xleft + this.Width - this._resizerWidth) + 'px';
			}
			
			child.style.width = this._resizerWidth + 'px';
			child.style.left = left;

			CreateChild(child, 'div');
			if (this._isResize) {
				if (window.RTL) {
					this.Resizer = new CVerticalResizer(child, parent, this._resizerWidth, 10, xleft + this.Width - this.MinWidth, 
						xleft + this.Width - this._resizerWidth, resizeHandler, 2);
				} else {
					this.Resizer = new CVerticalResizer(child, parent, this._resizerWidth, xleft + this.MinWidth, 10, 
						xleft + this.Width - this._resizerWidth, resizeHandler, 2);
				}
			}
			this._separator = child;
			return xleft + this.Width;
		}
		return xleft;
	}
};

function CVariableTable(sortHandler, selection, dragNDrop, controller)
{
	this._sortHandler = sortHandler;
	
	this._columnsCount = 0;
	this._columnsArr = [];
	this._sortedColumn = null;
	this.isSortFree = false;
	this._width = 0;
	
	this._headers = null;
	this._lines = null;
	this._linesTbl = null;
	
	this._selection = selection;
	this._dragNDrop = dragNDrop;
	this._timer = null;
	this.LastClickLineId = '';
	
	this._controller = controller;
	
	this._lineHeight = 20;

	this.HorBordersWidth = 0;
	this.VertBordersWidth = 0;
}

CVariableTable.prototype = 
{
	_clean: function ()
	{
		this._selection.Free();
		if (null != this._dragNDrop) {
			this._dragNDrop.SetSelection(null);
		}
		CleanNode(this._lines);
		this._linesTbl = null;
		this.LastClickLineId = '';
	},
	
	CleanLines: function (msg1, msg2)
	{
		this._clean();
		if (msg2 != undefined) {
			this._addMessageToList(msg1, false);
			msg1 = msg2;
		}
		var div = CreateChild(this._lines, 'div');
		div.className = 'wm_inbox_info_message';
		div.innerHTML = msg1;
	},
	
	ResizeColumnsHeight: function ()
	{
		var hOffsetHeight, lOffsetHeight, minRightWidth, i, column;
		hOffsetHeight = this._headers.offsetHeight;
		lOffsetHeight = this._lines.offsetHeight;
		minRightWidth = 0;
		for (i = this._columnsCount - 1; i >= 0; i--) {
			column = this._columnsArr[i];
			if (column.Resizer != null) {
				column.Resizer.updateVerticalSize(hOffsetHeight - 1, hOffsetHeight + lOffsetHeight - 2);
				if (!window.RTL) {
					column.Resizer.updateMinRightWidth(minRightWidth);
				}
			}
			minRightWidth += (i == this._columnsCount-1) ? column.MinWidth : column.Width;
		}
	},
	
	ResizeColumnsWidth: function (number)
	{
		var i, left, right, column;
		left = this._columnsArr[number].ResizeWidth();
		if (window.RTL) {
			right = left;
			for (i = number - 1; i >= 0; i--) {
				column = this._columnsArr[i];
				if (column.Resizer != null) {
					column.Resizer.updateMinRightWidth(this._width - (right - column.MinWidth));
				}
				right -= column.ResizeRight(right, (i==0));
			}
		}
		else {
			for (i = number + 1; i < this._columnsCount; i++) {
				left = this._columnsArr[i].ResizeLeft(left);
			}
			this._width = left;
		}
		this.ResizeColumnsHeight();
	},
	
	Resize: function (width)
	{
		var i, right, column, lastCell;
		//this._inbox_contaner
		this._headers.style.width = width + 'px';
		this._lines.style.width = width + 'px';
		if (this._linesTbl != null) {
			this._linesTbl.style.width = width + 'px';
		}

		if (window.RTL) {
			right = width;
			if (Browser.Mozilla && Browser.Version >=3 || Browser.Opera && Browser.Version < 9.5 || Browser.Safari || Browser.Chrome) {
				right = this._lines.clientWidth;
				if (this._linesTbl != null) {
					this._linesTbl.style.width = right + 'px';
				}
			}
			for (i = this._columnsCount - 1; i >= 0; i--) {
				column = this._columnsArr[i];
				if (column.Resizer != null) {
					column.Resizer.updateMinRightWidth(width - (right - column.MinWidth));
				}
				right -= column.ResizeRight(right, (i == 0));
				if (column.Resizer != null) {
					if (i > 0) {
						column.Resizer.updateMinLeftWidth(right - this._columnsArr[i-1].MinWidth);
					}
				}
			}
			this._width = width;
		}
		else {
			lastCell = this._columnsArr[this._columnsCount - 1];
			if (lastCell != null) {
				lastCell.SetWidth(width - this._width);
			}
		}
		this.ResizeColumnsHeight();
	},
	
	GetHeight: function ()
	{
		return this._headers.offsetHeight + this._lines.offsetHeight;
	},
	
	GetLines: function ()
	{
		return this._lines;
	},
	
	SetLinesHeight: function (height)
	{
		this._lines.style.height = (height - this._headers.offsetHeight) +'px';
	},
	
    ChangeField: function (oldId, newId, params)
    {
        for (var i = 0; i < this._columnsCount; i++) {
            var column = this._columnsArr[i];
            if (column.Id == oldId) {
                column.ChangeField(newId, params, true);
            }
        }
    },

	AddColumn: function (id, params)
	{
		var column = new CVariableColumn(id, params, this._selection);
		this._columnsArr[this._columnsCount++] = column;
		return column;
	},
	
	SetSort: function (sortField, sortOrder)
	{
		if (!this.isSortFree) {
			if (this._sortedColumn != null) {
				this._sortedColumn.RemoveSort();
			}
			for (var i = 0; i < this._columnsCount; i++) {
				var column = this._columnsArr[i];
				if (column.SortField == sortField) {
					column.SetSort(sortOrder);
					this._sortedColumn = column;
				}
			}
		}
	},

	FreeSort: function ()
	{
		this.isSortFree = true;
		for (var i = 0; i < this._columnsCount; i++) {
			this._columnsArr[i].FreeSort();
		}
	},
	
	UseSort: function ()
	{
		this.isSortFree = false;
		for (var i = 0; i < this._columnsCount; i++) {
			this._columnsArr[i].UseSort();
		}
	},
	
	SetNoMessagesFoundMessage: function ()
	{
		this._clean();
		this._addClearSearchLink(this._lines, '10px');
		var div = CreateChild(this._lines, 'div');
		div.className = 'wm_inbox_info_message';
		div.innerHTML = Lang.InfoNoMessagesFound;
	},
	
	_addClearSearchLink: function (parent, margin)
	{
		var span = CreateChild(parent, 'div', [['style', 'float: ' + window.RIGHT + '; margin: ' + margin + ';']]);
		var a = CreateChild(span, 'a', [['href', '#']]);
		with (a.style) {
			color = '#7E9BAF';
			fontFamily = 'Tahoma';
			fontSize = '11px';
			textDecoration = 'underline';
		}
		a.onclick = function () {
            var screen = WebMail.GetCurrentListScreen();
			if (screen) {
                screen.ClearSearch();
			}
			return false;
		};
		a.innerHTML = Lang.SearchClear;
	},

	_addMessageToList: function (msg, addClearLink)
	{
		if (typeof(msg) == 'string' && msg.length > 0) {
			var div = CreateChild(this._lines, 'div');
			with (div.style) {
				padding = '12px';
				borderBottom = '1px solid #C1CDD8';
			}
			var span = CreateChild(div, 'div', [['style', 'float: ' + window.LEFT + ';']]);
			with (span.style) {
				color = '#CCCCCC';
				fontFamily = 'Tahoma';
				fontSize = '18px';
			}
			span.innerHTML = msg;
			if (addClearLink) {
				this._addClearSearchLink(div, '');
			}
			span = CreateChild(div, 'div', [['style', 'width: 0; height: 0; padding: 0; overflow: hidden; clear: both;']]);
		}
	},

	Fill: function (objsArr, screenId, msg, addClearLink)
	{
		this.ScrollLines(0);
		this._selection.SaveCheckedLines();
		this._clean();
		if (null != this._dragNDrop) {
			this._dragNDrop.SetSelection(this._selection);
		}
		this._addMessageToList(msg, addClearLink);

		var tbl = CreateChild(this._lines, 'table', [['dir', 'ltr']]);
		this._linesTbl = tbl;
		var tr = null;
		for (var i=0; i<objsArr.length; i++) {
			tr = tbl.insertRow(i);
			var obj = objsArr[i];
			var line = this._controller.CreateLine(obj, tr, screenId);
			for (var j=0; j<this._columnsCount; j++) {
				var column = this._columnsArr[j];
				var td = tr.insertCell(j);
				line.SetContainer(column.Field, td);
				with (td.style) {
					textAlign = column.Align;
					paddingLeft = column._padding + 'px';
					paddingRight = column._padding + 'px';
				}
				if (i == 0) {
					column.LineElem = td;
					td.style.width = column.Width - 2*column._padding + 'px';
				}
			}
			this._selection.AddLine(line);
			if (null != this._dragNDrop) this._dragNDrop.AddDragObject(tr);
			this._controller.SetEventsHandlers(this, tr);
		}
		if (tr != null) {
			this._lineHeight = tr.offsetHeight;
		}
		this._selection.SetCheckboxChecked(false);
		this._selection.CheckSavedLines();
	},
	
	ScrollLines: function (lineIndex)
	{
		if (lineIndex == undefined || lineIndex == 0) {
			this._lines.scrollTop = '0';
			return;
		}
		var lineShift = (lineIndex + 1)*this._lineHeight;
		var linesHeight = this._lines.offsetHeight;
		var scrollTop = GetScrollY(this._lines);
		if (lineShift > linesHeight) {
			this._lines.scrollTop = (lineShift > scrollTop) ? lineShift - linesHeight : lineShift;
		}
	},
	
	ClickCheckLine: function (id)
	{
		if (id < 0) return;
		this._selection.CheckLine(id);
		this._controller.ClickLine(id, this);
		this.ScrollLines(this._selection.GetLineIndex());
	},
	
	KeyUpHandler: function (key, ev)
	{
		switch (key) {
			case Keys.Enter:
				if (typeof(this._controller.DblClickLine) != 'function') {
					return;
				}
				var tr = this._selection.GetCurrentTr();
				if (tr == null) {
					return;
				}
				this._controller.DblClickLine(tr, this);
				break;
			case Keys.Up:
				if (ev.shiftKey) {
					var id = this._selection.GetPrevLineId();
					if (id < 0) {
						return;
					}
					this._selection.CheckShiftLine(id);
					this.ScrollLines(this._selection.GetLineIndex());
				}
				else if (!ev.ctrlKey) {
					var id = this._selection.GetPrevViewLineId();
					this.ClickCheckLine(id);
				}
				break;
			case Keys.Down:
				if (ev.shiftKey) {
					var id = this._selection.GetNextLineId();
					if (id < 0) {
						return;
					}
					this._selection.CheckShiftLine(id);
					this.ScrollLines(this._selection.GetLineIndex());
				}
				else if (!ev.ctrlKey) {
					var id = this._selection.GetNextViewLineId();
					this.ClickCheckLine(id);
				}
				break;
			case Keys.Delete:
				this._controller.Delete();
				break;
			case Keys.Home:
				var id = this._selection.GetFirstLineId();
				this.ClickCheckLine(id);
				break;
			case Keys.End:
				var id = this._selection.GetLastLineId();
				this.ClickCheckLine(id);
				break;
			case Keys.PageUp:
				var visibleCount = Math.round(this._lines.offsetHeight/this._lineHeight);
				var id = this._selection.GetPrevViewLineId(visibleCount);
				this.ClickCheckLine(id);
				break;
			case Keys.PageDown:
				var visibleCount = Math.round(this._lines.offsetHeight/this._lineHeight);
				var id = this._selection.GetNextViewLineId(visibleCount);
				this.ClickCheckLine(id);
				break;
			case Keys.A:
				if (ev.ctrlKey) {
					this._selection.CheckAll();
				}
				break;
		}
	},
	
	Build: function (parent)
	{
		var div = CreateChild(parent, 'div');
		div.className = this._controller.ListContanerClass;
		this._inbox_contaner = div;

		var borders = GetBorders(div);
		this.HorBordersWidth = borders.Left + borders.Right;
		this.VertBordersWidth = borders.Top + borders.Bottom;

		var headers = CreateChild(div, 'div');
		headers.className = 'wm_inbox_headers';
		this._headers = headers;

		if (window.RTL) {
			this._columnsArr.reverse();
		}
		var left = 0;
		for (var i=0; i<this._columnsCount; i++) {
			var column = this._columnsArr[i];
			var isLast = (i == this._columnsCount-1);
			if (window.RTL) {
				isLast = (i == 0);
			}
			left = column.Build(headers, left, isLast, this._controller.ResizeHandler + '(' + i + ');', this._sortHandler);
		}
		this._width = left;
		
		var lines = CreateChild(div, 'div');
		lines.className = 'wm_inbox_lines';
		if (Browser.Mozilla) {
			lines.tabIndex = 1;
		}
		lines.onkeydown = function (ev) {
			var key = Keys.GetCodeFromEvent(ev);
			if (key == Keys.Up || key == Keys.Down || key == Keys.PageUp || key == Keys.PageDown || key == Keys.Space) {
				return false;
			}
			return true;
		};
		this._lines = lines;
	}
};

function CCheckBoxCell()
{
	this.Node = document.createElement('input');
	this.Node.type = 'checkbox';

	this.SetContainer = function (container) {
		container.appendChild(this.Node);
	};
}

function CImageCell(className, id, content)
{
	this.Node = document.createElement('div');
	this.Node.className = className + ' ' + content;
	if (id.length > 0) this.Node.id = id;

	this.SetContainer = function (container) {
		container.appendChild(this.Node);
	};
	
	this.SetContent = function (content) {
		this.Node.className = className + ' ' + content;
	};
}

function CTextCell(text, title)
{
	this.Content = text;
	this.Title = (title) ? ' title="' + title + '"' : '';
	this.Node = null;
	
	this.SetContainer = function (container) {
		this.Node = container;
		this._applyContentToContainer();
	};

	this.SetContent = function (content) {
		this.Content = content;
		this._applyContentToContainer();
	};
	
	this._applyContentToContainer = function () {
		this.Node.innerHTML = '<nobr' + this.Title + '>' + this.Content + '</nobr>';
	};
};

function CSelection(fillSelectedContactsHandler)
{
	this._fillSelectedContactsHandler = (fillSelectedContactsHandler) ? fillSelectedContactsHandler : null;
	this._lines = [];
	this._length = 0;
	this._currIdx = -1;
	this._checkbox = null;

	this._savedLinesIds = [];
	
	this._shiftStartIdx = -1;
	this._shiftEndIdx = -1;
}

CSelection.prototype = 
{
	SaveCheckedLines: function ()
	{
		this._savedLinesIds = [];
		for (var i = this._length - 1; i >= 0; i--) {
			var line = this._lines[i];
			if (line.Checked == true) {
				this._savedLinesIds.push(line.Id);
			}
		}
	},

	ClearSavedLines: function ()
	{
		this._savedLinesIds = [];
	},

	CheckSavedLines: function ()
	{
		for (var i = this._length - 1; i >= 0; i--) {
			var line = this._lines[i];
			if (this._lineIdInSaved(line.Id)) {
				line.Check();
			}
		}

		this.ClearSavedLines();
	},

	_lineIdInSaved: function (Id)
	{
		for(var i = 0; i < this._savedLinesIds.length; i++) {
			if (Id == this._savedLinesIds[i]) {
				return true;
			}
		}
		return false;
	},

	SetCheckBox: function (checkbox)
	{
		var selection = this;
		checkbox.onclick = function () {
			if (this.checked) {
				selection.CheckAll();
			}
			else {
				selection.UncheckAll();
			}
		};
		this._checkbox = checkbox;
	},
	
	Free: function ()
	{
		this._lines = [];
		this._length = 0;
		this._currIdx = -1;
		this._shiftStartIdx = -1;
		this._shiftEndIdx = -1;
	},
	
	AddLine: function (line)
	{
		this._lines.push(line);
		this._length = this._lines.length;
	},
	
	SetParams: function (idArray, field, value, isAllMess)
	{
		var readed = 0;
		if (isAllMess) {
			for (var i = this._length - 1; i >= 0; i--) {
				var line = this._lines[i];
				readed += line.SetParams(field, value);
			}
		}
		else {
			for (var j in idArray) {
				for (var i = this._length - 1; i >= 0; i--) {
					var line = this._lines[i];
					if (line.Id == idArray[j]) {
						readed += line.SetParams(field, value);
						break;
					}
				}
			}
		}
		return readed;
	},
	
	ChangeLineId: function (msg, newId)
	{
		for (var i = this._length - 1; i >= 0; i--) {
			var line = this._lines[i];
			if (line.IsCorrectIdData(msg)) {
				line.ChangeFromSubjData(msg, newId);
			}
		}
	},

	GetViewedLine: function ()
	{
		if (this._currIdx != -1) {
			return this._lines[this._currIdx];
		}
		return null;
	},
	
	GetCheckedLines: function ()
	{
		var idArray = [];
		var sizeArray = [];
		var unreaded = 0;
		for (var i = this._length - 1; i >= 0; i--) {
			var line = this._lines[i];
			if (line.Checked == true) {
				if (!line.Read) {
					unreaded++;
				}
				idArray.push(line.Id);
				sizeArray.push(line.MsgSize);
			}
		}
		if (idArray.length == 0) {
			var viewedLine = this.GetViewedLine();
			if (viewedLine != null) {
				if (!viewedLine.Read) {
					unreaded++;
				}
				idArray.push(viewedLine.Id);
				sizeArray.push(viewedLine.MsgSize);
			}
		}
		return {IdArray: idArray, SizeArray: sizeArray, Unreaded: unreaded};
	},

	GetLineById: function (id)
	{
		for (var i = this._length - 1; i >= 0; i--) {
			var line = this._lines[i];
			if (line.Id == id) {
				return line;
			}
		}
		return null;
	},
	
	/* return bool */
	SingleForDrag: function (id)
	{
		var checked = false;
		var singleChecked = true;
		for (var i = this._length - 1; i >= 0; i--) {
			var line = this._lines[i];
			if (line.Checked == true) {
				if (line.Id == id) {
					checked = true;
				} else {
					singleChecked = false;
				}
			}
		}
		if (!checked || singleChecked) {
			return true;
		}
		return false;
	},
	
	GetLineIndex: function ()
	{
		return this._currIdx;
	},
	
	_readyKeysCheckLines: function ()
	{
		return (this._length != 0);
	},
	
	GetFirstLineId: function ()
	{
		if (!this._readyKeysCheckLines()) {
			return -1;
		}
		return this._lines[0].Id;
	},
	
	GetNextLineId: function ()
	{
		if (!this._readyKeysCheckLines()) {
			return -1;
		}
		var indexes = this._getShiftIndexes();
		var idx = indexes.End + 1;
		if (idx >= this._length) {
			idx = this._length - 1;
		}
		return this._lines[idx].Id;
	},
	
	GetPrevLineId: function ()
	{
		if (!this._readyKeysCheckLines()) {
			return -1;
		}
		var indexes = this._getShiftIndexes();
		var idx = indexes.End - 1;
		if (idx < 0) {
			idx = 0;
		}
		return this._lines[idx].Id;
	},
	
	GetNextViewLineId: function (count)
	{
		if (!this._readyKeysCheckLines()) {
			return -1;
		}
		if (!count) {
			count = 1;
		}
		var idx = this._currIdx + count;
		if (idx >= this._length) {
			idx = this._length - 1;
		}
		return this._lines[idx].Id;
	},
	
	GetPrevViewLineId: function (count)
	{
		if (!this._readyKeysCheckLines()) {
			return -1;
		}
		if (!count) {
			count = 1;
		}
		var idx = this._currIdx - count;
		if (idx < 0) {
			idx = 0;
		}
		return this._lines[idx].Id;
	},
	
	GetLastLineId: function ()
	{
		if (!this._readyKeysCheckLines()) {
			return -1;
		}
		return this._lines[this._length - 1].Id;
	},
	
	GetCurrentTr: function ()
	{
		if (this._currIdx < 0 || this._currIdx >= this._length) {
			return null;
		}
		return this._lines[this._currIdx].Node;
	},

	DragItemsNumber: function (id)
	{
		var findLine = null;
		var number = 0;
		for (var i = this._length - 1; i >= 0; i--) {
			var line = this._lines[i];
			if (line.Id == id) {
				findLine = line;
			}
			if (line.Checked) {
				number++;
			}
		}
		if (null == findLine) {
			return 0;
		}
		else if (findLine.Checked) {
			return number;
		}
		else {
			this.CheckLine(id);
			return 1;
		}
	},
	
	FlagLine: function (id)
	{
		for (var i = this._length - 1; i >= 0; i--) {
			var line = this._lines[i];
			if (line.Id == id) {
				if (line.Flagged) {
					line.Unflag();
				}
				else {
					line.Flag();
				}
			}
		}
	},
	
	CheckLine: function (id)
	{
		for (var i = this._length - 1; i >= 0; i--) {
			var line = this._lines[i];
			if (line.Id == id) {
				line.View(true);
				this._currIdx = i;
			}
			else {
				line.View(false);
			}
		}
		this.ReCheckAllBox();
	},
	
	CheckCtrlLine: function (id)
	{
		for (var i = this._length - 1; i >= 0; i--) {
			var line = this._lines[i];
			if (line.Id == id) {
				if (line.Checked == false) {
					line.Check();
					if (this._shiftStartIdx == -1) {
						this._shiftStartIdx = i;
						this._shiftEndIdx = i;
					}
				}
				else {
					line.Uncheck();
					if (this._shiftStartIdx == i) {
						this._shiftStartIdx = -1;
						this._shiftEndIdx = -1;
					}
				}
			}
		}
		this.ReCheckAllBox();
	},
	
	_getShiftIndexes: function (id)
	{
		var startIdx = -1;
		var endIdx = -1;
		if (this._shiftStartIdx != -1) {
			startIdx = this._shiftStartIdx;
		}
		for (var i = 0; i < this._length; i++) {
			var line = this._lines[i];
			if (startIdx == -1 && line.Checked == true) {
				startIdx = i;
			}
			if (line.Id == id) {
				endIdx = i;
			}
		}
		if (startIdx == -1) {
			startIdx = this._currIdx;
		}
		if (startIdx == -1) {
			startIdx = endIdx;
		}
		if (endIdx == -1) {
			endIdx = this._shiftEndIdx;
		}
		if (endIdx == -1) {
			endIdx = startIdx;
		}
		this._shiftStartIdx = startIdx;
		this._shiftEndIdx = endIdx;
		return { Start: startIdx, End: endIdx };
	},

	CheckShiftLine: function (id)
	{
		var indexes = this._getShiftIndexes(id);
		var startIdx = indexes.Start;
		var endIdx = indexes.End;
		if (startIdx > endIdx) {
			startIdx = indexes.End;
			endIdx = indexes.Start;
		}
		for (var i = 0; i < this._length; i++) {
			var line = this._lines[i];
			if (i < startIdx || i > endIdx) {
				line.Uncheck();
			}
			else {
				line.Check();
			}
		}
		this.ReCheckAllBox();
	},
	
	CheckAll: function ()
	{
		for (var i = this._length - 1; i >= 0; i--) {
			this._lines[i].Check();
		}
		this._currIdx = -1;
		this.CheckSelectedContacts();
	},
	
	UncheckAll: function ()
	{
		for (var i = this._length - 1; i >= 0; i--) {
			this._lines[i].Uncheck();
		}
		this._currIdx = -1;
		this._shiftStartIdx = -1;
		this._shiftEndIdx = -1;
		this.CheckSelectedContacts();
	},
	
	ReCheckAllBox: function ()
	{
		var isAllCheck = true;
		for (var i = this._length - 1; i >= 0; i--) {
			if (this._lines[i].Checked == false) {
				isAllCheck = false;
				break;
			}
		}
		this.SetCheckboxChecked(isAllCheck);
		this.CheckSelectedContacts();
	},
	
	CheckSelectedContacts: function ()
	{
		if (this._fillSelectedContactsHandler != null) {
			var contactsArray = [];
			for (var i = 0; i < this._length; i++) {
				var line = this._lines[i];
				if (line.Checked) {
					contactsArray.push(line.Node.Email);
				}
			}
			var currId = -1;
			var currIsGroup = false;
			if (this._currIdx != -1) {
				var currLine = this._lines[this._currIdx];
				currId = currLine.ContactId;
				currIsGroup = (currLine.IsGroup ? true : false);
			}
			this._fillSelectedContactsHandler.call({ContactsArray: contactsArray, CurrId: currId, CurrIsGroup: currIsGroup});
		}
	},
	
	SetCheckboxChecked: function (checkedValue)
	{
		if (null != this._checkbox) {
			this._checkbox.checked = checkedValue;
		}
	}
};

function CDragNDrop(langField)
{
	this._selection = null;
	this._langField = langField;
	this._dragObjects = [];
	this._dragCount = 0;
	this._dropObjects = [];
	this._dropCount = 0;
	this._dropContainer = null;
	this._scrollY = 0;
	this._handle = CreateChild(document.body, 'div', [['class', 'wm_hide']]);
	this._handleImg = CreateChild(this._handle, 'div', [['class', 'wm_drag_handle_img']]);
	this._handleText = CreateChild(this._handle, 'span');
	this._dragId = '';
	this._dropId = '';
	this._dropElem = null;
	this._dropClassName = '';
	this.doMoveToInbox = true;
	this._inboxId = '';
	this._x1 = 0;
	this._y1 = 0;
	this._x2 = 0;
	this._y2 = 0;
	this.first = true;
}

CDragNDrop.prototype = {
	SetMoveToInbox: function (doMoveToInbox)
	{
		this.doMoveToInbox = doMoveToInbox;
	},
	
	SetDropContainer: function (dropContainer)
	{
		this._dropContainer = dropContainer;
	},
	
	SetInboxId: function (id)
	{
		this._inboxId = id;
	},
	
	SetSelection: function (selection)
	{
		this._selection = selection;
		if (null == selection) {
			this._dragObjects = [];
			this._dragCount = 0;
		}
	},
	
	AddDragObject: function (element)
	{
		this._dragObjects[this._dragCount] = element;
		this._dragCount++;
	},
	
	SetCoordinates: function (element)
	{
		var bounds = GetBounds(element);
		element._x1 = bounds.Left;
		element._y1 = bounds.Top - this._scrollY;
		element._x2 = bounds.Left + bounds.Width;
		element._y2 = bounds.Top - this._scrollY + bounds.Height;
		if (this._x1 == 0 && this._y1 == 0 && this._x2 == 0 && this._y2 == 0) {
			this._x1 = element._x1;
			this._y1 = element._y1 + this._scrollY;
			this._x2 = element._x2;
			this._y2 = element._y2 + this._scrollY;
		}
		else {
			if (this._x1 > element._x1) {
				this._x1 = element._x1;
			};
			if (this._y1 > element._y1) {
				this._y1 = element._y1;
			};
			if (this._x2 < element._x2) {
				this._x2 = element._x2;
			};
			if (this._y2 < element._y2) {
				this._y2 = element._y2;
			}
		}
	},
	
	AddDropObject: function (element)
	{
		this.SetCoordinates(element);
		this._dropObjects[this._dropCount] = element;
		this._dropCount++;
	},
	
	Resize: function ()
	{
		this._x1 = 0;
		this._y1 = 0;
		this._x2 = 0;
		this._y2 = 0;
		for (var i = 0; i < this._dropCount; i++) {
			this.SetCoordinates(this._dropObjects[i]);
		}
	},
	
	CleanDropObjects: function ()
	{
		this._dropObjects = [];
		this._dropCount = 0;
	},
	
	Ready: function ()
	{
		if (null == this._selection) return false;
		if (0 == this._dragCount) return false;
		if (0 == this._dragId.length) return false;
		return true;
	},
	
	RequestDrag: function (e, element)
	{
		if (!e.ctrlKey && !e.shiftKey) {
			this._dragId = element.id;
			this._moveCount = 0;
			element.blur();
			var obj = this;
			document.body.onmousemove = function (e) {
				e = e ? e : event;
				obj._moveCount++;
				if (obj._moveCount >= 5) {
					obj.StartDrag(e, this);
				}
			};
		}
	},
	
	StartDrag: function (e, element)
	{
		document.body.onmousemove = function () {};
		if (this.Ready()) {
			var number = this._selection.DragItemsNumber(this._dragId);
			var handle = this._handle;
			handle.className = 'wm_drag_handle';
			handle.style.top = (e.clientY + 5) + 'px';
			handle.style.left = (e.clientX + 5) + 'px';
			this._handleText.innerHTML = number + ' ' + Lang[this._langField];
			this._handleImg.className = 'wm_not_drag_handle_img';
			var obj = this;
			document.body.onmousemove = function(e) {
				e = e ? e : event;
				obj.ProcessDrag(e); 
			};
			document.body.onmouseup = function() {
				obj.EndDrag();
			};
		}
	},
	
	ProcessDrag: function (e)
	{
		var x = e.clientX;
		var y = e.clientY;
		with (this._handle.style) {
			top = (e.clientY + 5) + 'px';
			left = (e.clientX + 5) + 'px';
		}
		if (null != this._dropElem) {
			this._dropElem.className = this._dropClassName;
		}
		var scrollY = GetScrollY(this._dropContainer);
		if (scrollY != this._scrollY) {
			this._scrollY = scrollY;
			this.Resize();
		}
		if (x > this._x1 && x < this._x2 && y > this._y1 && y < this._y2) {
			for (var i=0; i<this._dropCount; i++) {
				var element = this._dropObjects[i];
				if (x > element._x1 && x < element._x2 && y > element._y1 && y < element._y2) {
					if (-1 == this._dragId.indexOf(element.id) && (this.doMoveToInbox || this._inboxId != element.id)) {
						this._dropId = element.id;
						this._dropElem = element;
						this._dropClassName = element.className;
						this._handleImg.className = 'wm_drag_handle_img';
						document.body.style.cursor = 'pointer';
						element.className = 'wm_folder_over';
					}
					else {
						this._dropId = '';
						this._dropElem = null;
						this._handleImg.className = 'wm_not_drag_handle_img';
						document.body.style.cursor = 'auto';
					}
				}
			}
		}
		else {
			this._dropId = '';
			this._handleImg.className = 'wm_not_drag_handle_img';
			document.body.style.cursor = 'auto';
		}
	},
	
	EndDrag: function ()
	{
		if (this._dropId.length > 0) {
			MoveToFolderHandler(this._dropId);
			this.first = false;
		};
		document.body.style.cursor = 'auto'; 
		this._handle.className = 'wm_hide';
		this._dragId = '';
		this._dropId = '';
		document.body.onmousemove = function () {};
		document.body.onmouseup = function () {};

		var i, element;
		for (i = 0; i < this._dropCount; i++) {
			element = this._dropObjects[i];
			element.className = (element.className == 'wm_select_folder') ? 'wm_select_folder' : 'wm_folder';
		}
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}