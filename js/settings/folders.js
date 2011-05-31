/*
 * Classes:
 *  CManageFoldersScreenPart(parentScreen)
 *  CFolderLine(fold, td, checkInp, spanIcon, spanA, nameA, nameInp, syncSel, hideCheckbox, opt, parent, prevIndex, index, upImg, downImg, countTd, sizeTd, protocol, folders, showType, folderDiv)
 */

function CManageFoldersScreenPart(parentScreen)
{
	this._parentScreen = parentScreen;
	
	this._folders = new CFolderList();
	this.FolderList = [];
	
	this.hasChanges = false;
	this.isChangedFolders = false;
	this.shown = false;
	this.disableCount = 0;

	this._idAcct = -1;
	this._changedIdAcct = false;
	this._protocol = POP3_PROTOCOL;
	
	this._mainCont = null;
	this._infoTbl = null;
	this._foldersEditZone = null;
	this._newFolderDiv = null;
	this._foldersSelLabel = null;
	this._foldersSelCell = null;
	this._foldersSelObj = null;
	this._foldersFakeSelObj = null;
	this._noParentObj = null;
	this._onMailServerTd = null;
	this._onMailServerObj = null;
	this._inWebMailObj = null;
	this._nameObj = null;
	this._checkAllObj = null;
	this._reloadFoldersTreeButton = null;
	this._FoldersTypeLink = null;
	this._FoldersTypeCheckbox = null;
	this._FoldersTypeHeader = null;
}

CManageFoldersScreenPart.prototype = {
	Show: function ()
	{
		this.shown = true;
		this.hasChanges = false;
		this._mainCont.className = '';
		this._foldersEditZone.className = 'wm_email_settings_edit_zone';
		if (this.disableCount > 0) this._infoTbl.className = 'wm_secondary_info';
		this.CloseNewFolder();
		if (this._idAcct != this._parentScreen._idAcct) {
			this._idAcct = this._parentScreen._idAcct;
		    GetHandler(TYPE_FOLDER_LIST, { IdAcct: this._idAcct, Sync: GET_FOLDERS_NOT_CHANGE_ACCT }, [], '');
		}
		else {
			this.Fill();
		}
	},
	
	Hide: function ()
	{
		this.shown = false;
		if (WebMail._isDemo) {
			this.Fill();
		} else if (this.hasChanges) {
			if (confirm(Lang.ConfirmSavefolders)) {
				this.SaveChanges();
			} else {
				this.Fill();
			}
		}
		this.hasChanges = false;
		this._mainCont.className = 'wm_hide';
		this._foldersEditZone.className = 'wm_hide';
		this._infoTbl.className = 'wm_hide';
		this.CloseNewFolder();
	},
	
	AddNewFolder: function ()
	{
		if (this.hasChanges) {
			if (confirm(Lang.ConfirmAddFolder)) {
				this.SaveChanges();
			} else {
				this.Fill();
			}
		}
		this._noParentObj.selected = true;
		this._onMailServerObj.checked = true;
		this._nameObj.value = '';
		this._newFolderDiv.className = '';
		this._foldersSelObj.className = '';
		this._noParentObj.selected = true;
	},
	
	SaveNewFolder: function ()
	{
		if (WebMail._isDemo) {
			WebMail.ShowReport(DemoWarning);
			this.CloseNewFolder();
			return;
		}

		var folderName = this._nameObj.value;
		if (Validator.IsEmpty(folderName)) {
			alert(Lang.WarningEmptyFolderName);
			return;
		}
		if (!Validator.IsCorrectFileName(folderName) || Validator.HasSpecSymbols(folderName))
		{
			alert(Lang.WarningCorrectFolderName);
			return;
		}

		var xml = '<param name="id_acct" value="' + this._idAcct + '"/>';
		var values = this._foldersSelObj.value.split(STR_SEPARATOR);
		var idParent = values[0];
		var fullNameParent = values[1];
		xml += '<param name="id_parent" value="' + idParent + '"/>';
		xml += '<param name="full_name_parent">' + GetCData(fullNameParent) + '</param>';
		xml += '<param name="name">' + GetCData(this._nameObj.value) + '</param>';
		if (this._protocol == IMAP4_PROTOCOL) {
			xml += '<param name="create" value="' + ((this._onMailServerObj.checked) ? '1' : '0') + '"/>';
		} else {
			xml += '<param name="create" value="0" />';
		}
		this.isChangedFolders = true;
		RequestHandler('new', 'folder', xml);
		this.CloseNewFolder();
	},
	
	CloseNewFolder: function ()
	{
		this._newFolderDiv.className = 'wm_hide';
	},
	
	UpdateFolders: function (folders)
	{
		if (this.isChangedFolders) {
			WebMail.ShowReport(Lang.ReportFoldersUpdatedSuccessfuly);
			this.isChangedFolders = false;
		}
		this._folders = folders;
		this._changedIdAcct = false;
		this.Fill();
	},

	UpdateProtocol: function (protocol)
	{
		this._protocol = protocol;
		switch (this._protocol) {
		    case POP3_PROTOCOL:
    			this._onMailServerTd.className = 'wm_hide';
    			this._reloadFoldersTreeButton.className = 'wm_hide';
			    break;
		    case IMAP4_PROTOCOL:
			    this._onMailServerTd.className = (window.UseDb)	? '' : 'wm_hide';
			    this._reloadFoldersTreeButton.className = 'wm_button';
			    break;
			case WMSERVER_PROTOCOL:
    			this._onMailServerTd.className = 'wm_hide';
    			this._reloadFoldersTreeButton.className = 'wm_button';
			    break;
		}
	},

	CheckAll: function (value)
	{
		this._checkAllObj.checked = value;
		var count = this.FolderList.length;
		for (var i=0; i<count; i++) {
			this.FolderList[i].SetChecked(value);
		}
	},
	
	DeleteSelected: function ()
	{
		if (this.hasChanges){
			if (confirm(Lang.ConfirmAddFolder)){
				this.SaveChanges();
			} else {
				this.Fill();
			}
		}
		var count = this.FolderList.length;
		var xml = '<param name="id_acct" value="' + this._idAcct + '"/>';
		var folders = '';
		for (var i=0; i<count; i++) {
			folders += this.FolderList[i].GetCheckedXml();
		}
		xml += '<folders>' + folders + '</folders>';
		if (folders != '') {
			this.isChangedFolders = true;
			RequestHandler('delete', 'folders', xml);
			WebMail.ClearFilterCache();
		}
	},

	Fill: function ()
	{
		if (this._folders.IdAcct == this._idAcct) {
			var fldSel = this._foldersSelObj;
			var fldFakeSel = this._foldersFakeSelObj;
			CleanNode(fldSel);
			CleanNode(fldFakeSel);

			var NoParentId = -1;
			var NoParentName = '';
			var bHideSystemFolders = false;
			if (this._folders.NameSpace.length > 0) {
				NoParentId = this._folders.GetNameSpaceFolderId();
				NoParentName = this._folders.NameSpace.substr(0, this._folders.NameSpace.length - 1)
				bHideSystemFolders = true;
			}
			
			var opt = CreateChild(fldSel, 'option', [['value', NoParentId + STR_SEPARATOR + NoParentName]]);
			opt.innerHTML = Lang.NoParent;
			this._noParentObj = opt;

			CleanNode(this._mainCont);
			var tbl = CreateChild(this._mainCont, 'table');
			tbl.className = 'wm_settings_manage_folders';
			this.BuildHeader(tbl);
	
			var rowIndex = 1;
			var colIndex;
			var folders = this._folders.Folders;
			var tr, td, fold, indent, width;
			var checkInp = null;
			var syncSel = null;
			var nameTd, spanIcon, spanA, nameA, nameInp, hideCheckbox, upImg, downImg, prevIndex;
			var count = 0;
			var size = 0;
			this.FolderList = Array();
			var prevFoldIndexes = Array();
			var foldLine = null;
			var iCount = folders.length;
			this.disableCount = 0;
			var bIsShowFolderSelect = false;
			for (var i=0; i<iCount; i++) {
				fold = folders[i];
				colIndex = 0;
	
				tr = tbl.insertRow(rowIndex++);
				td = tr.insertCell(colIndex++);
				checkInp = CreateChild(td, 'input', [['type', 'checkbox'], ['class', 'wm_checkbox']]);
	
				td = tr.insertCell(colIndex++);
				nameTd = td;
				td.className = 'wm_settings_mf_folder';
				var desc = FolderDescriptions[fold.Type];
				
				var folderDiv = CreateChild(td, 'div', [['class', 'wm_folder']]);
				
				var secondDiv = CreateChild(folderDiv, 'div');
				
				spanIcon = CreateChild(secondDiv, 'span', [['class', 'wm_folder_img', 'style', 'background-position: -' + desc.x*X_ICON_SHIFT + 'px -' + desc.y*Y_ICON_SHIFT + 'px;']]);
				spanIcon.innerHTML = '&nbsp;';
				
				spanA = CreateChild(secondDiv, 'span', [['class', 'wm_folder_name']]);
				spanA.innerHTML = fold.Name;
				nameA = CreateChild(secondDiv, 'span', [['class', 'wm_hide']]);
				var a = CreateChild(nameA, 'a', [['href', '#'], ['onclick', 'return false;']]);
				a.innerHTML = HtmlEncode(fold.Name);
				width = (indent < 190) ? (240 - indent) : 50;
				nameInp = CreateChild(secondDiv, 'input', [['type', 'text'], ['class', 'wm_hide'], ['style', 'width: ' + width + 'px;'], ['maxlength', '30']]);
				
				var countTd = tr.insertCell(colIndex++);
				countTd.innerHTML = fold.MsgCount;
				count += fold.MsgCount;
	
				var sizeTd = tr.insertCell(colIndex++);
				sizeTd.innerHTML = GetFriendlySize(fold.Size);
				sizeTd.className = (window.UseDb) ? '' : 'wm_hide';
				size += fold.Size;
				
				if (this._protocol == IMAP4_PROTOCOL && window.UseDb) {
					td = tr.insertCell(colIndex++);
					syncSel = CreateChild(td, 'select');
				}
	
				td = tr.insertCell(colIndex++);
				hideCheckbox = CreateChild(td, 'input', [['type', 'checkbox'], ['class', 'wm_checkbox']]);
				hideCheckbox.checked = !fold.Hide;
	
				td = tr.insertCell(colIndex++);
				td.className = 'wm_settings_mf_up_down';
	
				upImg = CreateChild(td, 'span', [['class', 'wm_settings_mf_up_inactive']]);
				upImg.innerHTML = '&nbsp;';
				downImg = CreateChild(td, 'span', [['class', 'wm_settings_mf_down_inactive']]);
				downImg.innerHTML = '&nbsp;';

				if (bHideSystemFolders) {
					if (fold.Type != FOLDER_TYPE_DEFAULT) {
						opt = CreateChild(fldFakeSel, 'option', [['value', fold.Id + STR_SEPARATOR + fold.FullName]]);
					} else {
						opt = CreateChild(fldSel, 'option', [['value', fold.Id + STR_SEPARATOR + fold.FullName]]);
						bIsShowFolderSelect = true;
					}
				} else {
					opt = CreateChild(fldSel, 'option', [['value', fold.Id + STR_SEPARATOR + fold.FullName]]);
					bIsShowFolderSelect = true;
				}

				opt.innerHTML = fold.Name;

				var obj = this;
				if (typeof(prevFoldIndexes[fold.IdParent]) == 'number') {
					prevIndex = prevFoldIndexes[fold.IdParent];
					this.FolderList[prevIndex].SetNextFoldLine(i);
				} else {
					prevIndex = -1;
				}

				var isShowType = (this._FoldersTypeCheckbox != null && this._FoldersTypeCheckbox.checked && this._protocol != POP3_PROTOCOL) ? true : false;
				this._FoldersTypeHeader.className = (isShowType) ? 'wm_mapping_head' : 'wm_hide';
				foldLine = new CFolderLine(fold, nameTd, checkInp, spanIcon, spanA, nameA, nameInp, syncSel, hideCheckbox, opt, obj, prevIndex, i, upImg, downImg, countTd, sizeTd, this._protocol, folders, isShowType, folderDiv);
	
				this.disableCount += foldLine.checkDisable;
				this.FolderList[i] = foldLine;
				prevFoldIndexes[fold.IdParent] = i;
			} // for

			if (!bIsShowFolderSelect) {
				this._foldersSelLabel.className = 'wm_hide';
				this._foldersSelCell.className = 'wm_hide';
			} else {
				this._foldersSelLabel.className = '';
				this._foldersSelCell.className = '';
			}

			this._infoTbl.className = (this.disableCount > 0 && this.shown)
				? 'wm_secondary_info' : 'wm_hide';
				
            this._FoldersTypeLink.className =
				(this._protocol == POP3_PROTOCOL || (!window.UseDb && !window.UseLdapSettings))
					? 'wm_hide' : '';
			
			this.BuildTotal(tbl, rowIndex, count, size);
			this.hasChanges = false;
	
			this.CloseNewFolder();

			if (this._parentScreen) {
				this._parentScreen.ResizeBody();
			}
		}
	},//Fill

	ChangeFoldersPlaces: function (prevIndex, index)
	{
		var fold = this.FolderList[index];
		var prop = fold.GetProperties();
		var fldOrder = prop.FldOrder;
		var nextIndex = prop.NextIndex;
		var prevFold = this.FolderList[prevIndex];
		var prevProp = prevFold.GetProperties();
		prop.FldOrder = prevProp.FldOrder;
		prevProp.FldOrder = fldOrder;
		if (nextIndex != -1) {
			var nextFold = this.FolderList[nextIndex];
			var nextProp = nextFold.GetProperties();
		}
		var foldPrevIndex = prevProp.PrevIndex;
		var prevFoldPrevIndex = prop.PrevIndex;
		var prevFoldNextIndex = prop.NextIndex;
		
		var i, childFold, childProp;
		var prevChilds = Array();
		for (i=prevIndex+1; i<index; i++) {
			childFold = this.FolderList[i];
			childProp = childFold.GetProperties();
			prevChilds.push(childProp);
		}
		var prevChCount = prevChilds.length;

		var childs = Array();
		var flag = true;
		var idParent = prop.IdParent;
		var level = prop.Level;
		for (i=index+1; flag; i++) {
			if (i == nextIndex) {
				flag = false;
			} else {
				childFold = this.FolderList[i];
				if (childFold) {
					childProp = childFold.GetProperties();
					if (idParent == childProp.IdParent || level >= childProp.Level) {
						flag = false;
					} else {
						childs.push(childProp);
					}
				} else {
					flag = false;
				}
			}
		}
		var chCount = childs.length;
		
		var newIndex = prevIndex + chCount + 1;
		
		prop.PrevIndex = foldPrevIndex;
		prop.Index = prevIndex;
		prop.NextIndex = newIndex;
		this.FolderList[prevIndex].SetProperties(prop);
		
		for(i=0; i<chCount; i++) {
			if (childs[i].PrevIndex != -1) {
				childs[i].PrevIndex = childs[i].PrevIndex - prevChCount - 1;
			}
			childs[i].Index = childs[i].Index - prevChCount - 1;
			if (childs[i].NextIndex != -1) {
				childs[i].NextIndex = childs[i].NextIndex - prevChCount - 1;
			}
			this.FolderList[prevIndex + 1 + i].SetProperties(childs[i]);
		}
		
		prevProp.PrevIndex = prevFoldPrevIndex;
		prevProp.Index = newIndex;
		prevProp.NextIndex = prevFoldNextIndex;
		this.FolderList[newIndex].SetProperties(prevProp);

		for(i=0; i<prevChCount; i++) {
			if (prevChilds[i].PrevIndex != -1) {
				prevChilds[i].PrevIndex = prevChilds[i].PrevIndex + chCount + 1;
			}
			prevChilds[i].Index = prevChilds[i].Index + chCount + 1;
			if (prevChilds[i].NextIndex != -1) {
				prevChilds[i].NextIndex = prevChilds[i].NextIndex + chCount + 1;
			}
			this.FolderList[newIndex + 1 + i].SetProperties(prevChilds[i]);
		}

		if (nextIndex != -1) {
			nextProp.PrevIndex = newIndex;
			this.FolderList[nextIndex].SetProperties(nextProp);
		}
		this.hasChanges = true;
	},//ChangeFoldersPlaces
	
	SetInputKeyPress: function (inp)
	{
	    var obj = this;
		inp.onkeypress = function (ev) { if (isEnter(ev)) obj.SaveChanges(); };
	},
	
	SetInputKeyPressSaveNewFolder: function (inp)
	{
	    var obj = this;
		inp.onkeypress = function (ev) { if (isEnter(ev)) obj.SaveNewFolder(); };
	},
	
	SaveChanges: function ()
	{
		if (WebMail._isDemo) {
			WebMail.ShowReport(DemoWarning);
			return;
		}

		var nodes = '';
		var count = this.FolderList.length;
		for (var i=0; i<count; i++) {
			nodes += this.FolderList[i].GetInXml();
		}
		var xml = '<param name="id_acct" value="' + this._idAcct + '"/>';
		xml += '<folders>' + nodes + '</folders>';
		RequestHandler('update', 'folders', xml);
		this.hasChanges = false;
		this.isChangedFolders = true;
	},//SaveChanges
	
	BuildHeader: function (tbl)
	{
		var obj = this;
		var colIndex = 0;
		var tr = tbl.insertRow(0);
		tr.className = 'wm_settings_mf_headers';
		var td = tr.insertCell(colIndex++);
		td.style.width = '26px';
		var inp = CreateChild(td, 'input', [['type', 'checkbox'], ['class', 'wm_checkbox']]);
		inp.onclick = function () { obj.CheckAll(this.checked); };
		this._checkAllObj = inp;

		td = tr.insertCell(colIndex++);
		if (this._protocol == IMAP4_PROTOCOL) {
			td.style.width = (window.UseDb === false) ? (270 + 140 + 40) + 'px' : 270 + 'px';
		} else {
			td.style.width = (270 + 140) + 'px';
		}
		td.className = 'wm_settings_mf_folder';

		var headDiv = CreateChild(td, 'div');
		headDiv.innerHTML = Lang.Folder;
		
		this._FoldersTypeHeader = CreateChild(td, 'div', [['class', 'wm_mapping_head']]);
		this._FoldersTypeHeader.innerHTML = Lang.FolderTypeMapTo;

		td = tr.insertCell(colIndex++);
		td.style.width = '40px';
		td.innerHTML = Lang.Msgs;

		td = tr.insertCell(colIndex++);
		td.style.width = '40px';
		td.innerHTML = Lang.Size;
		if (window.UseDb === false) {
			td.className = 'wm_hide';
		}
		
		if (this._protocol == IMAP4_PROTOCOL) {
			td = tr.insertCell(colIndex++);
			td.style.width = '140px';
			td.innerHTML = Lang.Synchronize;
			if (window.UseDb === false) {
				td.className = 'wm_hide';
			}
		}

		td = tr.insertCell(colIndex++);
		td.style.width = '94px';
		if (this._protocol == IMAP4_PROTOCOL) {
			td.innerHTML = Lang.CaptionSubscribed;
		} else {
			td.innerHTML = Lang.ShowThisFolder;
		}

		td = tr.insertCell(colIndex++);
		td.style.width = '40px';
	},
	
	BuildTotal: function (tbl, index, totalCount, totalSize)
	{
		var tr = tbl.insertRow(index);
		tr.className = 'wm_settings_mf_total';
		var td = tr.insertCell(0);
		
		td = tr.insertCell(1);
		td.className = 'wm_settings_mf_folder';
		td.innerHTML = Lang.Total;

		td = tr.insertCell(2);
		td.innerHTML = totalCount;

		td = tr.insertCell(3);
		td.innerHTML = GetFriendlySize(totalSize);
		if (window.UseDb === false) {
			td.className = 'wm_hide';
		}

		td = tr.insertCell(4);
		td.colSpan = (this._protocol == IMAP4_PROTOCOL)? 3 : 2;
		td.className = 'wm_settings_mf_page_switcher';
	},
	
	Build: function(container)
	{
		var tbl, tr, td, lbl;
		var obj = this;
		this._foldersEditZone = CreateChild(container, 'table');
		this._foldersEditZone.className = 'wm_hide';
		var mainTr = this._foldersEditZone.insertRow(0);
		var mainTd = mainTr.insertCell(0);
		mainTd.className = 'wm_email_settings_edit_zone_cell';

		this._mainCont = CreateChild(mainTd, 'div');
		this._mainCont.className = 'wm_hide';

		tbl = CreateChild(mainTd, 'table');
		tbl.className = 'wm_hide';
		this._infoTbl = tbl;
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		td.className = 'wm_secondary_info';
		td.innerHTML = Lang.InfoDeleteNotEmptyFolders;
		WebMail.LangChanger.Register('innerHTML', td, 'InfoDeleteNotEmptyFolders', '');
		
		tbl = CreateChild(mainTd, 'table');
		tbl.className = 'wm_settings_buttons';
		tr = tbl.insertRow(0);
		tr.className = '';
		td = tr.insertCell(0);
		td.className = 'wm_delete_button';
		//add new folder button
		var inp = CreateChild(td, 'input', [['type', 'button'], ['class', 'wm_button'], ['value', Lang.AddNewFolder]]);
		WebMail.LangChanger.Register('value', inp, 'AddNewFolder', '');
		inp.onclick = function () { obj.AddNewFolder(); };
		var span = CreateChild(td, 'span'); span.innerHTML = '&nbsp;';
		//delete selected button
		inp = CreateChild(td, 'input', [['type', 'button'], ['class', 'wm_button'], ['value', Lang.DeleteSelected]]);
		WebMail.LangChanger.Register('value', inp, 'DeleteSelected', '');
		inp.onclick = function () { obj.DeleteSelected(); };
		span = CreateChild(td, 'span'); span.innerHTML = '&nbsp;';
		//reload folders tree button
		inp = CreateChild(td, 'input', [['type', 'button'], ['class', 'wm_button'], ['value', Lang.ReloadFolders]]);
		WebMail.LangChanger.Register('value', inp, 'ReloadFolders', '');
		inp.onclick = function () {
		    GetHandler(TYPE_FOLDER_LIST, { IdAcct: obj._idAcct, Sync: GET_FOLDERS_SYNC_FOLDERS }, [], '');
		    obj.isChangedFolders = true;
		};
		this._reloadFoldersTreeButton = inp;
		td = tr.insertCell(1);
		
		inp = CreateChild(td, 'input', [['type', 'button'], ['class', 'wm_button'], ['value', Lang.Save]]);
		WebMail.LangChanger.Register('value', inp, 'Save', '');
		inp.onclick = function () { obj.SaveChanges(); };
		
		this._FoldersTypeLink = tbl.insertRow(1);
		this._FoldersTypeLink.className = 'wm_hide';
		td = this._FoldersTypeLink.insertCell(0);
		td.className = 'wm_delete_button';

		this._FoldersTypeCheckbox = CreateChild(td, 'input', [['id', 'folderTypeCheckbox'], ['type', 'checkbox'], ['class', 'wm_checkbox']]);
		lbl = CreateChild(td, 'label', [['for', 'folderTypeCheckbox'], ['style', 'font-size: 12px']]);
		lbl.innerHTML = Lang.ShowFoldersMapping;
		WebMail.LangChanger.Register('innerHTML', lbl, 'ShowFoldersMapping', '');
		this._FoldersTypeCheckbox.onclick = function() {
			obj.Fill();
		};

		tbl = CreateChild(td, 'table');
		tbl.className = '';
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		td.className = 'wm_secondary_info';
		td.innerHTML = Lang.ShowFoldersMappingNote;
		WebMail.LangChanger.Register('innerHTML', td, 'ShowFoldersMappingNote', '');

		var div = CreateChild(mainTd, 'div');
		div.className = 'wm_hide';
		this._newFolderDiv = div;

		tbl = CreateChild(div, 'table');
		tbl.className = 'wm_settings_part_info';
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		td.innerHTML = Lang.NewFolder;
		WebMail.LangChanger.Register('innerHTML', td, 'NewFolder', '');

		tbl = CreateChild(div, 'table');
		tbl.className = 'wm_settings_new_folder';
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		this._foldersSelLabel = CreateChild(td, 'span');
		this._foldersSelLabel.innerHTML = Lang.ParentFolder + ':';
		WebMail.LangChanger.Register('innerHTML', this._foldersSelLabel, 'ParentFolder', ':');
		td = tr.insertCell(1);
		this._foldersSelCell = CreateChild(td, 'span');
		var sel = CreateChild(this._foldersSelCell, 'select');
		var sel2 = CreateChild(this._foldersSelCell, 'select', [['class', 'wm_hide']]);
		this._foldersSelObj = sel;
		this._foldersFakeSelObj = sel2;
		var opt = CreateChild(sel, 'option', [['value', '0']]);
		opt.innerHTML = Lang.NoParent;
		this._noParentObj = inp;

		td = tr.insertCell(2);
		this._onMailServerTd = td;
		td.className = 'wm_settings_on_mailserver';
		td.rowSpan = 2;
		inp = CreateChild(td, 'input', [['type', 'radio'], ['class', 'wm_checkbox'], ['id', 'on_mail_server'], ['name', 'on_mail_server']]);
		inp.checked = true;
		this._onMailServerObj = inp;
		lbl = CreateChild(td, 'label', [['for', 'on_mail_server']]);
		lbl.innerHTML = Lang.OnMailServer;
		WebMail.LangChanger.Register('innerHTML', lbl, 'OnMailServer', '');
		CreateChild(td, 'br');
		inp = CreateChild(td, 'input', [['type', 'radio'], ['class', 'wm_checkbox'], ['id', 'in_webmail'], ['name', 'on_mail_server']]);
		this._inWebMailObj = inp;
		lbl = CreateChild(td, 'label', [['for', 'in_webmail']]);
		lbl.innerHTML = Lang.InWebMail;
		WebMail.LangChanger.Register('innerHTML', lbl, 'InWebMail', '');

		tr = tbl.insertRow(1);
		td = tr.insertCell(0);
		td.className = 'wm_settings_title';
		td.innerHTML = Lang.FolderName + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'FolderName', '');
		td = tr.insertCell(1);
		inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['maxlength', '30']]);
		this.SetInputKeyPressSaveNewFolder(inp);
		this._nameObj = inp;

		tbl = CreateChild(div, 'table');
		tbl.className = 'wm_settings_buttons';
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		inp = CreateChild(td, 'input', [['type', 'button'], ['class', 'wm_button'], ['value', Lang.OK]]);
		WebMail.LangChanger.Register('value', inp, 'OK', '');
		inp.onclick = function () { obj.SaveNewFolder(); };
		CreateTextChild(td, ' ');
		inp = CreateChild(td, 'input', [['type', 'button'], ['class', 'wm_button'], ['value', Lang.Cancel]]);
		WebMail.LangChanger.Register('value', inp, 'Cancel', '');
		inp.onclick = function () { obj.CloseNewFolder(); };
		
	}//Build
};

function CFolderLine(fold, td, checkInp, spanIcon, spanA, nameA, nameInp, syncSel, hideCheckbox, opt, parent, prevIndex, index, upImg, downImg, countTd, sizeTd, protocol, folders, showType, folderDiv)
{
	this._protocol = protocol;

	this._folders = folders;
	this._fold = {};
	this._fold.Id = fold.Id;
	this._fold.IdParent = fold.IdParent;
	this._fold.Type = fold.Type;
	this._fold.SyncType = fold.SyncType;
	this._fold.Hide  = fold.Hide;
	this._fold.FldOrder = fold.FldOrder;
	this._fold.hasChilds = fold.hasChilds;
	this._fold.MsgCount = fold.MsgCount;
	this._fold.NewMsgCount = fold.NewMsgCount;
	this._fold.Size = fold.Size;
	
	var langField = FolderDescriptions[fold.Type].langField;
	this._fold.Name = (typeof langField != 'undefined') ? Lang[langField] : fold.Name;
	this._fold.RealName = fold.Name;
	this._fold.FullName = fold.FullName;
	this._fold.Level = fold.Level;
	this._fold.Checked = false;
	this._fold.PrevIndex = prevIndex;
	this._fold.Index = index;
	this._fold.NextIndex = -1;
	
	this._container = td;
	this._checkInp = checkInp;
	this._spanIcon = spanIcon;
	this._nameA = nameA;
	this._spanA = spanA;
	this._nameInp = nameInp;
	this._syncSel = syncSel;
	this._hideCheckbox = hideCheckbox;
	this._opt = opt;
	this._parent = parent;
	this._upImg = upImg;
	this._downImg = downImg;
	this._countTd = countTd;
	this._sizeTd = sizeTd;
	this._directModeOpt = null;

	this._nameSpanIndent = Number.NaN;
	this._nameAIndent = Number.NaN;
	this._nameInpIndent = Number.NaN;
	
	this.checkDisable = 0;
	this._mapSel = null;
	this._showType = showType;
	this._folderDiv = folderDiv;
	this.Init();
}

CFolderLine.prototype = {
	Init: function () {
		var obj, folderMapDiv, strIndent, j, desc;
		
		obj = this;
		if (this._fold.Type == FOLDER_TYPE_DEFAULT) {
			if (this._protocol != POP3_PROTOCOL && (this._fold.hasChilds || this._fold.MsgCount > 0)) {
				this._checkInp.checked = false;
				this._checkInp.disabled = true;
				this._checkInp.onchange = function () {};
				this.checkDisable = 1;
			} else {
				this._checkInp.checked = this._fold.Checked;
				this._checkInp.disabled = false;
				this._checkInp.onchange = function () { 
					obj.SetChecked(this.checked);
				};
			}
			this._checkInp.className = 'wm_checkbox';
		} else {
			this._checkInp.className = 'wm_hide';
		}

		if (this._showType) {
			folderMapDiv = CreateChild(this._container, 'div', [['class', 'wm_mapping_line']]);
			if (this._fold.Type != FOLDER_TYPE_INBOX) {
				this._mapSel = CreateChild(folderMapDiv, 'select');
				if (this._fold.Type != FOLDER_TYPE_DEFAULT) {
					this._mapSel.disabled = true;
				}
			}

			if (this._mapSel != null) {
				this.FillMapSel();
				this._mapSel.onchange = function () {
					obj.ChangeFoldersType(this.value);
					obj._parent._folders.Folders = obj._folders;
					obj._parent.Fill();
					obj._parent.hasChanges = true;
				};
			}
		}
		
		if (window.RTL) {
			this._folderDiv.style.marginRight = FOLDERS_TREES_INDENT * this._fold.Level + 'px';
		} else {
			this._folderDiv.style.marginLeft = FOLDERS_TREES_INDENT * this._fold.Level + 'px';
		}
		strIndent = '';
		for (j = 0; j < this._fold.Level; j++) {
			strIndent += '&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		this._opt.innerHTML = strIndent + this._fold.Name;
		if (this._fold.Type == FOLDER_TYPE_DEFAULT) {
		// if (this._fold.Type != FOLDER_TYPE_INBOX) {
			this._spanA.innerHTML = '';
			this._spanA.className = 'wm_hide';
			this._nameA.innerHTML = '<a href="#" onclick="return false;">' + this._fold.Name + '</a>';
			this._nameA.onclick = function () { 
				obj.EditName();
				return false; 
			};
			this._nameA.className = 'wm_folder_name';
		} else {
			this._spanA.innerHTML = this._fold.Name;
			this._spanA.className = 'wm_folder_name';
			this._nameA.innerHTML = '';
			this._nameA.onclick = function () { 
				return false; 
			};
			this._nameA.className = 'wm_hide';
		}
		desc = FolderDescriptions[this._fold.Type];
		this._spanIcon.style.backgroundPosition = '-' + desc.x * X_ICON_SHIFT + 'px -' + desc.y * Y_ICON_SHIFT + 'px';
		this._nameInp.onkeyup = function (ev) {
			if (isEnter(ev)) {
				obj.SaveName();
			}
		};
		this._nameInp.onblur = function () { 
			obj.SaveName(); 
		};
		
		this._countTd.innerHTML = this._fold.MsgCount;
		this._sizeTd.innerHTML = GetFriendlySize(this._fold.Size);

		if (this._syncSel != null) {
			this.FillSyncSel();
			this._syncSel.onchange = function () {
				if (!WebMail.Settings.AllowDirectMode && obj._directModeOpt != null) {
					obj._syncSel.removeChild(obj._directModeOpt);
					obj._directModeOpt = null;
				}
				obj.SetSyncType(this.value);
			};
		}
		
		this._hideCheckbox.checked = !this._fold.Hide;
		this._hideCheckbox.onclick = function () { 
			obj.ChangeHide(); 
		};
		
		if (this._fold.PrevIndex == -1) {
			this._upImg.className = 'wm_settings_mf_up_inactive';
			this._upImg.onclick = function () {};
		} else {
			this._upImg.className = 'wm_settings_mf_up wm_control_img';
			this._upImg.onclick = function () {
				obj.ChangeWithPrev(); 
			};
		}
	},

	ChangeFoldersType: function (value)
	{
		if (this._fold.Type != FOLDER_TYPE_DEFAULT) {
			return;
		}
		for (var i = 0; i < this._folders.length; i++) {
			if (this._folders[i].Type == value) {
				this._folders[i].Type = FOLDER_TYPE_DEFAULT;
			}
			if (this._folders[i].Id == this._fold.Id) {
				this._folders[i].Type = value;
			}
		}
	},

	FillMapSel: function () {
		var opt, sel;
		
		sel = this._mapSel;
		CleanNode(sel);

		opt = CreateChild(sel, 'option', [['value', FOLDER_TYPE_DEFAULT * 10]]);
		opt.innerHTML =  Lang.FolderTypeDefault;
		opt.selected = true;
		opt = CreateChild(sel, 'option', [['value', FOLDER_TYPE_SENT]]);
		opt.innerHTML = Lang.FolderSentItems;
		if (this._fold.Type == FOLDER_TYPE_SENT) {
			opt.selected = true;
		}
		opt = CreateChild(sel, 'option', [['value', FOLDER_TYPE_DRAFTS]]);
		opt.innerHTML = Lang.FolderDrafts;
		if (this._fold.Type == FOLDER_TYPE_DRAFTS) {
			opt.selected = true;
		}
		opt = CreateChild(sel, 'option', [['value', FOLDER_TYPE_TRASH]]);
		opt.innerHTML = Lang.FolderTrash;
		if (this._fold.Type == FOLDER_TYPE_TRASH) {
			opt.selected = true;
		}
		opt = CreateChild(sel, 'option', [['value', FOLDER_TYPE_SPAM]]);
			opt.innerHTML = Lang.FolderSpam;
			if (this._fold.Type == FOLDER_TYPE_SPAM) {
				opt.selected = true;
		}
		if (this._protocol == WMSERVER_PROTOCOL) {
			opt = CreateChild(sel, 'option', [['value', FOLDER_TYPE_QUARANTINE]]);
			opt.innerHTML = Lang.FolderQuarantine;
			if (this._fold.Type == FOLDER_TYPE_QUARANTINE) {
				opt.selected = true;
			}
		}
	},

	GetTypeName: function (folderType)
	{
		switch (folderType) {
		case FOLDER_TYPE_INBOX:
			return Lang.FolderInbox;
		case FOLDER_TYPE_SENT:
			return Lang.FolderSentItems;
		case FOLDER_TYPE_DRAFTS:
			return Lang.FolderDrafts;
		case FOLDER_TYPE_TRASH:
			return Lang.FolderTrash;
		case FOLDER_TYPE_SPAM:
			return Lang.FolderSpam;
		case FOLDER_TYPE_QUARANTINE:
			return Lang.FolderQuarantine;
		}
		return Lang.FolderTypeDefault;
	},
	
	SetNextFoldLine: function (index)
	{
		this._fold.NextIndex = index;
		if (this._fold.NextIndex == -1) {
			this._downImg.className = 'wm_settings_mf_down_inactive';
			this._downImg.onclick = function () {};
		} else {
			this._downImg.className = 'wm_settings_mf_down wm_control_img';
			var obj = this;
			this._downImg.onclick = function () { 
				obj.ChangeWithNext(); 
			};
		}
	},
	
	ChangeWithPrev: function ()
	{
		this._parent.ChangeFoldersPlaces(this._fold.PrevIndex, this._fold.Index);
	},
	
	ChangeWithNext: function ()
	{
		this._parent.ChangeFoldersPlaces(this._fold.Index, this._fold.NextIndex);
	},

	SetChecked: function (value) {
		if (this._fold.Type == FOLDER_TYPE_DEFAULT && (this._protocol == POP3_PROTOCOL || !this._fold.hasChilds && this._fold.MsgCount == 0)) {
			this._fold.Checked = value;
			this._checkInp.checked = value;
		}
	},
	
	GetCheckedXml: function () {
		if (this._fold.Type == FOLDER_TYPE_DEFAULT && this._fold.Checked) {
			return '<folder id="' + this._fold.Id + '"><full_name>' + GetCData(this._fold.FullName) + '</full_name></folder>';
		} else {
			return '';
		}
	},
	
	EditName: function () {
		this._nameA.className = 'wm_hide';
		this._nameInp.value = HtmlDecode(this._fold.Name);
		this._nameInp.className = 'wm_folder_name';
		this._nameInp.focus();
	},
	
	SaveName: function () {
		var value, strIndent, j;
		value = this._nameInp.value;
		if (Trim(value).length != 0 && this._fold.Name != value) {
			if (!Validator.IsCorrectFileName(value)) {
				alert(Lang.WarningCantUpdateFolder);
			}
			else {
				this._fold.Name = value;
				strIndent = '';
				for (j = 0; j < this._fold.Level; j++) {
					strIndent += '&nbsp;&nbsp;&nbsp;&nbsp;';
				}
				this._opt.innerHTML = strIndent + this._fold.Name;
				this._nameA.innerHTML = '<a href="#" onclick="return false;">' + HtmlEncode(this._fold.Name) + '</a>';
				this._parent.hasChanges = true;
			}
		}
		this.StopEditName();
	},
	
	StopEditName: function () {
		this._nameInp.className = 'wm_hide';
		this._nameInp.blur();
		this._nameA.className = 'wm_folder_name';
	},
	
	SetSyncType: function (value) {
		this._fold.SyncType = value - 0;
		this._parent.hasChanges = true;
	},
	
	ChangeHide: function () {
		this._fold.Hide = !this._hideCheckbox.checked;
		this._parent.hasChanges = true;
	},
	
	GetProperties: function () {
		return this._fold;
	},
	
	SetProperties: function (fold) {
		this._fold = fold;
		this.Init();
		this.SetNextFoldLine(this._fold.NextIndex);
	},
	
	FillSyncSel: function () {
		var sel = this._syncSel;
		CleanNode(sel);
		var opt = CreateChild(sel, 'option', [['value', SYNC_TYPE_NO]]);
		opt.innerHTML = Lang.SyncTypeNo;
		if (this._fold.SyncType == SYNC_TYPE_NO) {
			opt.selected = true;
		}
		opt = CreateChild(sel, 'option', [['value', SYNC_TYPE_NEW_HEADERS]]);
		opt.innerHTML = Lang.SyncTypeNewHeaders;
		if (this._fold.SyncType == SYNC_TYPE_NEW_HEADERS) {
			opt.selected = true;
		}
		opt = CreateChild(sel, 'option', [['value', SYNC_TYPE_ALL_HEADERS]]);
		opt.innerHTML = Lang.SyncTypeAllHeaders;
		if (this._fold.SyncType == SYNC_TYPE_ALL_HEADERS) {
			opt.selected = true;
		}
		opt = CreateChild(sel, 'option', [['value', SYNC_TYPE_NEW_MSGS]]);
		opt.innerHTML = Lang.SyncTypeNewMessages;
		if (this._fold.SyncType == SYNC_TYPE_NEW_MSGS) {
			opt.selected = true;
		}
		opt = CreateChild(sel, 'option', [['value', SYNC_TYPE_ALL_MSGS]]);
		opt.innerHTML = Lang.SyncTypeAllMessages;
		if (this._fold.SyncType == SYNC_TYPE_ALL_MSGS) {
			opt.selected = true;
		}
		if (WebMail.Settings.AllowDirectMode || SYNC_TYPE_DIRECT_MODE == this._fold.SyncType) {
			opt = CreateChild(sel, 'option', [['value', SYNC_TYPE_DIRECT_MODE]]);
			opt.innerHTML = Lang.SyncTypeDirectMode;
			if (this._fold.SyncType == SYNC_TYPE_DIRECT_MODE) {
				opt.selected = true;
			}
			this._directModeOpt = opt;
		}
	},
	
	GetInXml: function () {
		var attrs, nodes;
		attrs = ' id="' + this._fold.Id + '"';
		attrs += ' sync_type="' + this._fold.SyncType + '"';
		attrs += ' type="' + this._fold.Type + '"';
		attrs += (this._fold.Hide) ? ' hide="1"' : ' hide="0"';
		attrs += ' fld_order="' + this._fold.FldOrder + '"';
		nodes = '<name>' + GetCData((this._fold.Type != FOLDER_TYPE_DEFAULT) ? this._fold.RealName : this._fold.Name) + '</name>';
		nodes += '<full_name>' + GetCData(this._fold.FullName) + '</full_name>';
		return '<folder' + attrs + '>' + nodes + '</folder>';
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}