/*
 * Classes:
 * 	CMessageListDisplay(selection, dragNDrop, controller)
 */

function CMessageListDisplay(selection, dragNDrop, controller)
{
	this._lines = null;
	this._linesTbl = null;
	this._linesContainer = null;
	this._controller = controller;
	this._fromDisplay = null;
	this._totalWidth = 160; // sum of the widths of all columns (except for From) and paddings of all columns
	this._selection = selection;
	this._dragNDrop = dragNDrop;
	this._lineHeight = 39; // the height of one line is needed for scrolling by keyboard
	this._columnsArr = [];
	this._columnsCount = 0;
	this.LastClickLineId = '';

	this.HorBordersWidth = 2;
	this.VertBordersWidth = 0;
	this.LinesMarginWidth = 2;
}

CMessageListDisplay.prototype = 
{
	_clean: function ()
	{
		this._selection.Free();
		if (null != this._dragNDrop) {
			this._dragNDrop.SetSelection(null);
		}
		CleanNode(this._lines);
		this._linesTbl = null;
		this._fromDisplay = null;
		this.LastClickLineId = '';
	},
	
	Resize: function (width)
	{
		var maxWidth = this._linesContainer.offsetWidth;
		if (!maxWidth) maxWidth = width;
		var minWidth = this._totalWidth + 200;
		width = Validator.CorrectNumber(width - this.HorBordersWidth - this.LinesMarginWidth, minWidth, maxWidth);

		this._lines.style.width = width + 'px';
		var scrollWidth = 0;
		if (this._linesTbl != null) {
			if (this._linesTbl.offsetHeight > this._lines.offsetHeight) {
				scrollWidth = 18;
			}
			this._linesTbl.style.width = (width - scrollWidth) + 'px';
		}
		if (this._fromDisplay != null) {
			this._fromDisplay.style.width = (width - scrollWidth - this._totalWidth) + 'px';
		}
	},
	
	SetLinesHeight: function (height)
	{
		this._lines.style.height = height +'px';
	},
	
	GetHeight: function ()
	{
		return this._lines.offsetHeight;
	},

	AddColumn: function (id, params)
	{
		var columnIndex = this._columnsCount++;
		this._setColumn(id, params, columnIndex);
	},
	
	_setColumn: function (id, params, index)
	{
		params.Id = id;
		this._columnsArr[index] = params;
	},
	
    ChangeField: function (oldId, newId, params)
    {
        for (var i = 0; i < this._columnsCount; i++) {
            var column = this._columnsArr[i];
            if (column.Id == oldId) {
                this._setColumn(newId, params, i);
            }
        }
    },

	Fill: function (msgArr, screenId, info, addClearLink)
	{
		this.ScrollLines(0);
		this._selection.SaveCheckedLines();
		this._clean();
		if (null != this._dragNDrop) {
			this._dragNDrop.SetSelection(this._selection);
		}
		this._addMessageToList(info, addClearLink);
		var tbl = CreateChild(this._lines, 'table', [['dir', 'ltr']]);
		this._linesTbl = tbl;
		
		var tr = null;
		for (var msgIndex = 0; msgIndex < msgArr.length; msgIndex++) {
			tr = tbl.insertRow(msgIndex);
			var msg = msgArr[msgIndex];
			var line = this._controller.CreateLine(msg, tr, screenId);
			
			var td = tr.insertCell(0);
			td.className = 'wm_inbox_read_item_view_corner';
			CreateChild(td, 'div');
			
			var totalWidth = 0;
			var columnIndex = 0;
			for (; columnIndex < this._columnsCount; columnIndex++) {
				td = tr.insertCell(columnIndex + 1);
				var params = this._columnsArr[columnIndex];
				td.style.paddingTop = params.PaddingTopBottom + 'px';
				td.style.paddingRight = params.PaddingLeftRight + 'px';
				td.style.paddingBottom = params.PaddingTopBottom + 'px';
				td.style.paddingLeft = params.PaddingLeftRight + 'px';
				td.style.textAlign = params.Align;
				if (msgIndex == 0) {
					td.style.width = params.PermanentWidth + 'px';
					if (params.Id == IH_FROM || params.Id == IH_TO) {
						this._fromDisplay = td;
						totalWidth += 2 * params.PaddingLeftRight;
					}
					else {
						totalWidth += params.PermanentWidth + 2 * params.PaddingLeftRight;
					}
				}
				line.SetContainer(params.DisplayField, td);
			}
			
			td = tr.insertCell(columnIndex + 1);
			td.className = 'wm_inbox_read_item_view_corner';
			CreateChild(td, 'div');
			
			if (msgIndex == 0) {
				this._totalWidth = totalWidth + 2;
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
	
	Build: function (parent)
	{
        if (window.RTL) {
            this._columnsArr.reverse();
        }
		this._linesContainer = parent;
		this._lines = CreateChild(parent, 'div', [['class', 'wm_inbox_lines']]);
		this._lines.onkeydown = function (ev) {
			var key = Keys.GetCodeFromEvent(ev);
			if (key == Keys.Up || key == Keys.Down || key == Keys.PageUp || key == Keys.PageDown || key == Keys.Space) {
				return false;
			}
			return true;
		};
		var borders = GetBorders(this._lines);
		this.HorBordersWidth = borders.Left + borders.Right;
		this.VertBordersWidth = borders.Top + borders.Bottom;
	}
};

CMessageListDisplay.prototype.CleanLines = CVariableTable.prototype.CleanLines;
CMessageListDisplay.prototype.SetNoMessagesFoundMessage = CVariableTable.prototype.SetNoMessagesFoundMessage;
CMessageListDisplay.prototype._addClearSearchLink = CVariableTable.prototype._addClearSearchLink;
CMessageListDisplay.prototype.GetLines = CVariableTable.prototype.GetLines;
CMessageListDisplay.prototype.ScrollLines = CVariableTable.prototype.ScrollLines;
CMessageListDisplay.prototype.ClickCheckLine = CVariableTable.prototype.ClickCheckLine;
CMessageListDisplay.prototype.KeyUpHandler = CVariableTable.prototype.KeyUpHandler;
CMessageListDisplay.prototype._addMessageToList = CVariableTable.prototype._addMessageToList;

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}