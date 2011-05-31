/*
 * Functions:
 *  CreateToolBarItemClick(type)
 *  CreateReplyClick(type)
 *  DoPrefetch()
 *  CreateMoveActionFunc(id, fullName)
 *  CreateFolderClickFunc(id, fullName, obj, element)
 * Prototypes:
 *  MessageListPrototype
 */

function CreateToolBarItemClick(type)
{
	return function () { 
		RequestMessagesOperationHandler(type, [], []);
	};
}

function CreateReplyClick(type)
{
	return function () {
		WebMail.ReplyClick(type);
	};
}

function DoPrefetch() {
	if (window.UsePrefetch && WebMail.ScreenId == WebMail.ListScreenId && preFetchFolder && preFetchData) {
		var xml = preFetchData.GetInXML(preFetchFolder);
		if (xml.length > 0) {
			WebMail.DataSource.Get(TYPE_MESSAGES_BODIES, {}, [], xml);
			preFetchStartFlag = true;
		} else {
			preFetchStartFlag = false;
		}
	} else {
		preFetchStartFlag = false;
	}
}

function CreateMoveActionFunc(id, fullName) {
	return function() { 
		RequestMessagesOperationHandler(TOOLBAR_MOVE_TO_FOLDER, [], [], id, fullName);
	};
}

function CreateFolderClickFunc(id, fullName, obj, element) {
	return function() {
		obj.FolderClick(id, fullName, element);
		return false;
	};
}

var MessageListPrototype = {

	PlaceData: function(Data) {
		var Type = Data.Type;
		switch (Type) {
			case TYPE_FOLDER_LIST:
				this.PlaceFolderList(Data);
				break;
			case TYPE_MESSAGE_LIST:
				this.PlaceMessageList(Data);
				break;
			case TYPE_MESSAGE:
				var currentMessageId = Data.GetIdForList(this.Id);
				/*var currentLineId = null;
				var checkedLines = this._selection.GetCheckedLines();
				if (checkedLines && checkedLines.IdArray && checkedLines.IdArray.length == 1) {
				currentLineId = checkedLines.IdArray[0];
				}*/
				var currentLineObj = this._selection.GetViewedLine();
				var currentLineId = (currentLineObj == null) ? null : currentLineObj.Id;
				if (currentLineId == null || currentLineId == currentMessageId) {
					this._msgObj = Data;
					var id = Data.Id;
					var folderId = Data.FolderId;
					var folderFullName = Data.FolderFullName;
					var uid = Data.Uid;
					var charset = Data.Charset;
					if (null != this.msgForView && this.msgForView.IsCorrectData(id, uid, folderId, folderFullName, charset)) {
						SelectScreenHandler(SCREEN_VIEW_MESSAGE);
						this.msgForView = null;
					}
					this._messageId = id;
					this._messageUid = uid;
					this._msgCharset = charset;
					this.FillByMessage();
					this.ResetReplyTools(Data);
				}
				break;
			case TYPE_MESSAGES_OPERATION:
				this.PlaceMessagesOperation(Data);
				break;
		}
	},

	GetCurrMessageHistoryObject: function() {
		var historyObj = this.GetCurrFolderHistoryObject();
		historyObj.MsgId = this._messageId,
		historyObj.MsgUid = this._messageUid,
		historyObj.MsgFolderId = this._folderId,
		historyObj.MsgFolderFullName = this._folderFullName,
		historyObj.MsgCharset = this._msgCharset,
		historyObj.MsgSize = this._msgSize,
		historyObj.MsgParts = [PART_MESSAGE_HEADERS, PART_MESSAGE_HTML, PART_MESSAGE_ATTACHMENTS];
		return historyObj;
	},

	ResizeBody: function(mode) {
		if (this.isBuilded) {
			this.ResizeScreen(mode);
			if (!Browser.IE && mode == RESIZE_MODE_ALL) {
				this.ResizeScreen(mode);
			}
		}
	},

	ParseSettings: function(settings) {
		if (this.Id == SCREEN_MESSAGE_LIST_TOP_PANE) {
			this._spaceInfoObj.ParseSettings(settings);
		}
		this._useImapTrash = settings.UseImapTrash;
		this._allowContacts = settings.AllowContacts;
		this._showTextLabels = settings.ShowTextLabels;
		this._defaultInboxHeight = settings.HorizResizer;
		this.ChangeSkin(settings.DefSkin);
		if (this._timeOffset != settings.TimeOffset ||
		this._defLang != settings.DefLang ||
		this._messagesPerPage != settings.MsgsPerPage) {
			var page = this._page;
			if (this._messagesPerPage != settings.MsgsPerPage) {
				page = 1;
			}
			this._timeOffset = settings.TimeOffset;
			this._defLang = settings.DefLang;
			this._messagesPerPage = settings.MsgsPerPage;
			if (this.isBuilded && (this._folderId != -1 || this._folderFullName != '')) {
				SetHistoryHandler(
					{
						ScreenId: this.Id,
						FolderId: this._folderId,
						FolderFullName: this._folderFullName,
						Page: page,
						SortField: this._sortField,
						SortOrder: this._sortOrder,
						LookForStr: '',
						SearchMode: 0,
						RedrawType: REDRAW_NOTHING,
						RedrawObj: null,
						MsgId: null,
						MsgUid: null,
						MsgFolderId: null,
						MsgFolderFullName: null,
						MsgCharset: null,
						MsgSize: null,
						MsgParts: null,
						ForcedRequest: true
					}
				);
			}
			else {
				this.RedrawPages(this._page);
			}
		}
		else {
			this.RedrawPages(this._page);
		}
	}, //ParseSettings

	ChangeSkin: function(newSkin) {
		if (this._skinName != newSkin) {
			this._skinName = newSkin;
			if (this.isBuilded) {
				this._foldersPane.ChangeSkin();
				this.CleanFolderList();
				this.FillByFolders();
				this.RedrawPages(this._page);
				this.FillByMessages();
			}
		}
	},

	RedrawFolderControls: function(redrawElement, id, fullName) {
		if (redrawElement) {
			if (this._currFolder) this._currFolder.className = 'wm_folder';
			redrawElement.className = 'wm_select_folder';
			this._currFolder = redrawElement;
			if (this._folderId != id || this._folderFullName != fullName) {
				this.CleanMessageBody(true);
			}
		}
		if (id && fullName) {
			this.ChangeFolder(id, fullName);
		}
		if (id == -1 && fullName == '') {
			if (this._currFolder) this._currFolder.className = 'wm_folder';
			this.ChangeFolder(id, fullName);
		}
	},

	CleanMessageBody: function(showInfo) {
		this._msgObj = null;
		this._previewPaneMessageHeaders.Clean();
		if (this._messagesObj != null && this._messagesObj.MessagesCount > 0 && showInfo) {
			this._msgViewer.Clean('<div class="wm_inbox_info_message">' + Lang.InfoNoMessageSelected +
			'<br /><div class="wm_view_message_info">' + Lang.InfoSingleDoubleClick + '</div></div>');
		}
		else {
			//TODO empty message is not good
			this._msgViewer.Clean();
		}
		this._picturesControl.Hide();
		this._sensivityControl.Hide();
		this._readConfirmationControl.Hide();
		if (this.Id == SCREEN_MESSAGE_LIST_CENTRAL_PANE) {
			this._replyPane.Hide();
			this.ResizeScreen(RESIZE_MODE_MSG_PANE);
		}
		else {
			this.ResizeScreen(RESIZE_MODE_ALL);
		}
		this.ResetReplyTools(null);
	},

	ShowPictures: function(safety) {
		var msg = this._msgObj;
		if (msg.Safety != safety) {
			msg.ShowPictures();
			msg.Safety = safety;
			this._msgViewer.Fill(msg);
		}
		this.FillMessageInfo(msg);
		this.ResizeScreen(RESIZE_MODE_MSG_HEIGHT);
	},

	FillMessageInfo: function(msg) {
		this._picturesControl.SetSafety(msg.Safety);
		switch (msg.Safety) {
			case SAFETY_NOTHING:
				this._picturesControl.Show();
				var fromParts = GetEmailParts(HtmlDecode(msg.FromAddr));
				this._picturesControl.SetFromAddr(fromParts.Email);
				break;
			case SAFETY_MESSAGE:
				this._picturesControl.Show();
				break;
			case SAFETY_FULL:
				this._picturesControl.Hide();
				break;
		}
		if (this._sensivityControl) {
			this._sensivityControl.Show(msg.Sensivity);
		}
		if (this._readConfirmationControl) {
			if (msg.MailConfirmationValue && msg.MailConfirmationValue.length > 0) {
				this._readConfirmationControl.Show();
			}
			else {
				this._readConfirmationControl.Hide();
			}
		}
	},

	FillByMessage: function() {
		var contactId = this._msgObj.FromContact.Id;
		var contact = this._msgObj.FromContact;
		if (contactId != -1) {
			var stringDataKey = WebMail.DataSource.GetStringDataKey(TYPE_CONTACT, { IdAddr: contactId });
			if (WebMail.DataSource.Cache.ExistsData(TYPE_CONTACT, stringDataKey)) {
				contact = WebMail.DataSource.Cache.GetData(TYPE_CONTACT, stringDataKey);
			}
		}
		this._previewPaneMessageHeaders.Fill(this._msgObj, contact);

		this.FillMessageInfo(this._msgObj);

		var htmlMode = this._msgViewer.Fill(this._msgObj);
		if (!htmlMode) this._picturesControl.Hide();
		if (this.Id == SCREEN_MESSAGE_LIST_CENTRAL_PANE) {
			this._replyPane.Show(this._msgObj);
			this.ResizeScreen(RESIZE_MODE_MSG_PANE);
		}
		else {
			this.ResizeScreen(RESIZE_MODE_ALL);
		}
		if (null != this._pageSwitcher) this._pageSwitcher.Replace();
	},

	SwitchToHtmlPlain: function() {
		this._msgViewer.SwitchToHtmlPlain();
	},

	GetCurrFolderHistoryObject: function() {
		return {
			ScreenId: this.Id,
			FolderId: this._folderId,
			FolderFullName: this._folderFullName,
			Page: this._page,
			SortField: this._sortField,
			SortOrder: this._sortOrder,
			LookForStr: this._lookForStr,
			SearchMode: this._searchMode,
			RedrawType: REDRAW_NOTHING,
			RedrawObj: null,
			MsgId: null,
			MsgUid: null,
			MsgFolderId: null,
			MsgFolderFullName: null,
			MsgCharset: null,
			MsgParts: null
		};
	},

	ClearSearch: function() {
		var historyObj = this.GetCurrFolderHistoryObject();
		if (historyObj.FolderId == -1) {
			historyObj.FolderId = this.InboxId;
			historyObj.FolderFullName = this.InboxFullName;
		}
		historyObj.LookForStr = '';
		SetHistoryHandler(historyObj);
	},

	GetSortField: function(folderId, folderFullName) {
		if (this._sortField == SORT_FIELD_FROM && (this.DraftsId == folderId && this.DraftsFullName == folderFullName ||
			this.SentId == folderId && this.SentFullName == folderFullName)) {
			return SORT_FIELD_TO;
		}
		if (this._sortField == SORT_FIELD_TO && (this.DraftsId != folderId || this.DraftsFullName != folderFullName) &&
			(this.SentId != folderId || this.SentFullName != folderFullName)) {
			return SORT_FIELD_FROM;
		}
		return this._sortField;
	},

	PlaceFolderList: function(Data) {
		this._foldersObj = Data;
		if (this.shown || this._folderId == -1 && this._folderFullName == '' && this.isBuilded) {
			this.CleanFolderList();
			this._foldersParam = Array();
			this.FillByFolders();
		}
		if (this.shown) {
			if (Data.Sync != 2) {
				if (Data.Sync == 1) {
					GetMessageListHandler(REDRAW_NOTHING, null, this._folderId, this._folderFullName, this.GetSortField(this._folderId, this._folderFullName), this._sortOrder, this._page, this._lookForStr, this._searchMode);
				}
				else {//if (this._lookForStr.length == 0) {
					SetHistoryHandler(
						{
							ScreenId: this.Id,
							FolderId: this._folderId,
							FolderFullName: this._folderFullName,
							Page: this._page,
							SortField: this.GetSortField(this._folderId, this._folderFullName),
							SortOrder: this._sortOrder,
							LookForStr: '',
							SearchMode: 0,
							RedrawType: REDRAW_NOTHING,
							RedrawObj: null,
							MsgId: null,
							MsgUid: null,
							MsgFolderId: null,
							MsgFolderFullName: null,
							MsgCharset: null,
							MsgParts: null
						}
					);
				}
			}
			this.ResizeBody(RESIZE_MODE_ALL);
		}
	},

	PlaceMessageList: function(Data) {
		var paramIndex, params;
		this._messagesObj = Data;
		if (this.shown) {
			// init prefetch vars
			preFetchData = Data.MessagesBodies;
			preFetchFolder = this._foldersParam;

			if (!preFetchStartFlag) {
				DoPrefetch();
			}

			this._sortField = Data.SortField;
			this._sortOrder = Data.SortOrder;
			this._lookForStr = Data.LookFor;
			this._searchMode = Data._searchFields;

			this.PlaceSearchData(Data._searchFields, Data.LookFor)

			this._page = Data.Page;
			if (this._folderId != Data.FolderId || this._folderFullName != Data.FolderFullName) {
				paramIndex = Data.FolderId + Data.FolderFullName;
				params = this._foldersParam[paramIndex];
				if (params) {
					params.ChangeMsgsCounts(Data.MessagesCount, Data.NewMsgsCount, Data.LookFor.length > 0);
					this.ChangeCurrFolder(Data.FolderId, Data.FolderFullName, params._div, Data.MessagesCount, params._syncType, params._type);
				} else {
					this.RedrawFolderControls(null, Data.FolderId, Data.FolderFullName);
					this.WriteMsgsCountInFolder(Data.MessagesCount);
				}
			} else {
				paramIndex = this._folderId + this._folderFullName;
				params = this._foldersParam[paramIndex];
				if (params) {
					params.SetPage(Data.Page);
					params.ChangeMsgsCounts(Data.MessagesCount, Data.NewMsgsCount, Data.LookFor.length > 0);
					this.RedrawFolderControls(params._div, Data.FolderId, Data.FolderFullName);
				}
				this.WriteMsgsCountInFolder(Data.MessagesCount);
				this._useOrFreeSort(Data.MessagesCount);
			}
			this.FillByMessages();
			this.RepairToolBar();
		}
	},

	EndCheckMail: function() {
		WebMail.CheckMail.End();
		this._checkMailTool.Enable();
	},

	EnableToolsByOperation: function(operationType, deleteLikePop3) {
		switch (operationType) {
			case TOOLBAR_DELETE:
			case TOOLBAR_NO_MOVE_DELETE:
				this.EnableDeleteTools(deleteLikePop3);
				break;
			case TOOLBAR_NOT_SPAM:
				this._notSpamTool.Enable();
				break;
			case TOOLBAR_IS_SPAM:
				this._isSpamTool.Enable();
				break;
		}
	},

	PlaceMessagesOperation: function(Data) {
		this.EnableToolsByOperation(Data.OperationInt, this.DeleteLikePop3());
		if (this.shown) {
			var fId = this._folderId; var fName = this._folderFullName;
			if (Data.OperationInt == TOOLBAR_PURGE && this.DeleteLikePop3()) {
				fId = this.TrashId; fName = this.TrashFullName;
			}
			if (Data.OperationInt == TOOLBAR_EMPTY_SPAM) {
				fId = this.SpamId; fName = this.SpamFullName;
			}
			var params = this._foldersParam[fId + fName];
			if (!params && this._lookForStr.length == 0) {
				var dict = Data.Messages;
				var keys = dict.keys();
				if (keys.length == 1) {
					var folder = dict.getVal(keys[0]);
					fId = folder.FolderId; fName = folder.FolderFullName;
					params = this._foldersParam[fId + fName];
				}
			}
			if (params) {
				switch (Data.OperationInt) {
					case TOOLBAR_MARK_ALL_READ:
					case TOOLBAR_MARK_READ:
						params.Read();
						break;
					case TOOLBAR_MARK_ALL_UNREAD:
					case TOOLBAR_MARK_UNREAD:
						params.Unread();
						break;
					case TOOLBAR_MOVE_TO_FOLDER:
					case TOOLBAR_IS_SPAM:
					case TOOLBAR_NOT_SPAM:
						params.Remove();
						var paramIndex = Data.ToFolderId + Data.ToFolderFullName;
						if (Data.OperationInt == TOOLBAR_IS_SPAM) {
							paramIndex = this.SpamId + this.SpamFullName;
						}
						else if (Data.OperationInt == TOOLBAR_NOT_SPAM) {
							paramIndex = this.InboxId + this.InboxFullName;
						}
						if (this._foldersParam[paramIndex]) {
							this._foldersParam[paramIndex].Append();
						}
						if (null != this._pageSwitcher) {
							var page = this._pageSwitcher.GetLastPage(this._removeCount);
							if (page < this._page) {
								this._page = page;
							}
						}
						break;
					case TOOLBAR_DELETE:
					case TOOLBAR_NO_MOVE_DELETE:
						params.Remove();
						if (this.DeleteLikePop3()) {
							var paramIndexT = this.TrashId + this.TrashFullName;
							if (this._foldersParam[paramIndexT]) {
								this._foldersParam[paramIndexT].Append();
							}
							else if (this.needToRefreshFolders) {
								GetHandler(TYPE_FOLDER_LIST, { IdAcct: WebMail._idAcct, Sync: GET_FOLDERS_NOT_SYNC }, [], '');
							}
						}
						if (null != this._pageSwitcher) {
							var page = this._pageSwitcher.GetLastPage(this._removeCount);
							if (page < this._page) {
								this._page = page;
							}
						}
						break;
					case TOOLBAR_PURGE:
						if (this.DeleteLikePop3()) {
							params.ChangeMsgsCounts(0, 0, false);
						}
					case TOOLBAR_EMPTY_SPAM:
						params.ChangeMsgsCounts(0, 0, false);
						break;
					case TOOLBAR_FLAG:
					case TOOLBAR_UNFLAG:
						WebMail.DataSource.Cache.ClearMessageList(fId, fName, true);
						break;
				}
				WebMail.DataSource.Cache.SetMessagesCount(fId, fName, params.MsgsCount, params._newMsgsCount);
			}
			else if (this._lookForStr.length > 0) {
				WebMail.DataSource.Cache.ClearMessageList(fId, fName);
				GetHandler(TYPE_FOLDER_LIST, { IdAcct: WebMail._idAcct, Sync: GET_FOLDERS_NOT_SYNC }, [], '');
			}

			if (Data.OperationInt == TOOLBAR_PURGE && this.DeleteLikePop3() && this.IsTrash()) {
				this.CleanInboxLines(Lang.InfoEmptyFolder);
				this.WriteMsgsCountInFolder(0);
			}
			else if (Data.OperationInt == TOOLBAR_EMPTY_SPAM && this.IsSpam()) {
				this.CleanInboxLines(Lang.InfoEmptyFolder);
				this.WriteMsgsCountInFolder(0);
			}
			else if (Data.OperationInt == TOOLBAR_DELETE || Data.OperationInt == TOOLBAR_NO_MOVE_DELETE ||
					Data.OperationInt == TOOLBAR_IS_SPAM || Data.OperationInt == TOOLBAR_NOT_SPAM ||
					Data.OperationInt == TOOLBAR_MOVE_TO_FOLDER) {
				GetMessageListHandler(REDRAW_NOTHING, null, this._folderId, this._folderFullName, this._sortField, this._sortOrder, this._page, this._lookForStr, this._searchMode);
			}
			else {
				if (Data.OperationField != '') {
					var dict = Data.Messages;
					var keys = dict.keys();
					var idArray = [];
					for (var i in keys) {
						var folder = dict.getVal(keys[i]);
						for (var j in folder.IdArray) {
							var msg = folder.IdArray[j];
							var msgH = new CMessageHeaders();
							msgH.Id = msg.Id;
							msgH.Uid = msg.Uid;
							msgH.Charset = msg.Charset;
							msgH.Size = msg.Size;
							msgH.FolderId = folder.FolderId;
							msgH.FolderFullName = folder.FolderFullName;
							idArray.push(msgH.GetIdForList(this.Id));
						}
					}
					this._selection.SetParams(idArray, Data.OperationField, Data.OperationValue, Data.isAllMess);
				}
			}
		}
	},

	Show: function(settings, historyArgs) {
		this.shown = true;
		this._mainContainer.className = 'wm_background';
		this._toolBar.Show();
		this.ShowSearchForm();
		this.ParseSettings(settings);
		this.ResizeBody(RESIZE_MODE_ALL);
		if (null != historyArgs) {
			this.RestoreFromHistory(historyArgs);
		}
		if (this._foldersObj == null && -1 != WebMail._idAcct || this.needToRefreshFolders) {
			GetHandler(TYPE_FOLDER_LIST, { IdAcct: WebMail._idAcct, Sync: GET_FOLDERS_NOT_SYNC }, [], '');
			this.needToRefreshFolders = false;
		}
		if (this._showTextLabels) {
			this._toolBar.ShowTextLabels();
		}
		else {
			this._toolBar.HideTextLabels();
		}
	},

	ChangeFromFieldInFolder: function(id, fullName) {
		var paramIndex = id + fullName;
		var params = this._foldersParam[paramIndex];
		if (params) {
			if (params.SentDraftsType) {
				this._inboxTable.ChangeField(IH_FROM, IH_TO, InboxHeaders[IH_TO]);
			}
			else {
				this._inboxTable.ChangeField(IH_TO, IH_FROM, InboxHeaders[IH_FROM]);
			}
		}
	},

	FolderClick: function(id, fullName, newFolder) {
		var paramIndex = id + fullName;
		var params = this._foldersParam[paramIndex];
		if (params) {
			SetHistoryHandler(
				{
					ScreenId: this.Id,
					FolderId: id,
					FolderFullName: fullName,
					Page: params.Page,
					SortField: this.GetSortField(id, fullName),
					SortOrder: this._sortOrder,
					LookForStr: '',
					SearchMode: 0,
					RedrawType: REDRAW_FOLDER,
					RedrawObj: newFolder,
					MsgId: null,
					MsgUid: null,
					MsgFolderId: null,
					MsgFolderFullName: null,
					MsgCharset: null,
					MsgSize: null,
					MsgParts: null
				}
			);
		}
	},

	_showSearchingMessage: function(folderId, folderFullName, lookForString) {
		var paramIndex = folderId + folderFullName;
		var params = this._foldersParam[paramIndex];
		if (params) {
			var searchResultsMessage = this._getSearchResultsMessage(folderId, params.Name, params.Type, lookForString);
			this.CleanInboxLines(searchResultsMessage, Lang.Searching);
		}
		else {
			this.CleanInboxLines(Lang.Searching);
		}
		this.CleanMessageBody(false);
	},

	RestoreFromHistory: function(args) {
		if (null != args) {
			if (args.AcctChanged) {
				this._folderId = -1;
				this._folderFullName = '';
				this.InboxId = -1;
				this.InboxFullName = '';
				this.SentId = -1;
				this.SentFullName = '';
				this.DraftsId = -1;
				this.DraftsFullName = '';
				this.TrashId = -1;
				this.TrashFullName = '';
				this.SpamId = -1;
				this.SpamFullName = '';
				this._page = 1;
				this._lookForStr = '';
				this._searchMode = 0;
				this._messagesObj = null;
				this.needToRefreshFolders = false;
				this.CleanFolderList();
				this.CleanInboxLines(Lang.InfoMessagesLoad);
				this.CleanMessageBody(true);
				var stringDataKey = WebMail.DataSource.GetStringDataKey(TYPE_FOLDER_LIST, { IdAcct: args.IdAcct });
				if (WebMail.DataSource.Cache.ExistsData(TYPE_FOLDER_LIST, stringDataKey)) {
					WebMail.DataSource.NeedInfo = false;
					RequestHandler('update', 'id_acct', '<param name="id_acct" value="' + WebMail._idAcct + '"/>');
					GetHandler(TYPE_FOLDER_LIST, { IdAcct: args.IdAcct, Sync: GET_FOLDERS_NOT_SYNC }, [], '');
				} else {
					GetHandler(TYPE_ACCOUNT_BASE, { IdAcct: args.IdAcct, ChangeAcct: 1 }, [], '');
				}
			}
			else {
				var needMsg = null != args.MsgId && null != args.MsgUid && null != args.MsgFolderId &&
					null != args.MsgFolderFullName && null != args.MsgCharset && null != args.MsgParts && null != args.MsgSize;
				if (null == args.FolderId && (this._folderId != -1 || this._lookForStr.length != 0)
					|| this.needToRefreshMessages) {
					if (!needMsg) {
						this.CleanMessageBody(false);
					}
					if (this._lookForStr.length != 0) {
						this._showSearchingMessage(args.FolderId, args.FolderFullName, this._lookForStr);
					}
					GetMessageListHandler(REDRAW_NOTHING, null, this._folderId, this._folderFullName, this._sortField, this._sortOrder, this._page, this._lookForStr, this._searchMode);
					this.needToRefreshMessages = false;
				}
				else if (args.ForcedRequest || null == this._messagesObj || this._folderId != args.FolderId || this._folderFullName != args.FolderFullName ||
					this._sortField != args.SortField || this._sortOrder != args.SortOrder || this._page != args.Page ||
					this._lookForStr != args.LookForStr || this._searchMode != args.SearchMode) {
					if (args.FolderId != null && args.FolderId != -1 || args.LookForStr != null && args.LookForStr.length != 0) {
						var paramIndex = args.FolderId + args.FolderFullName;
						var params = this._foldersParam[paramIndex];
						if (params) {
							this.ChangeCurrFolder(args.FolderId, args.FolderFullName, args.RedrawObj, params.MsgsCount, params._syncType, params._type);
						}
						if (!needMsg) {
							this.CleanMessageBody(false);
						}
						if (args.LookForStr.length != 0) {
							this._showSearchingMessage(args.FolderId, args.FolderFullName, args.LookForStr);
						}
						GetMessageListHandler(args.RedrawType, args.RedrawObj, args.FolderId, args.FolderFullName, args.SortField, args.SortOrder, args.Page, args.LookForStr, args.SearchMode);
					}
				}
				if (needMsg) {
					GetMessageHandler(args.MsgId, args.MsgUid, args.MsgFolderId, args.MsgFolderFullName, args.MsgParts, args.MsgCharset);
				}
			}
		}
	},

	Hide: function() {
		this.shown = false;
		this._mainContainer.className = 'wm_hide';
		this._toolBar.Hide();
		this.HideSearchForm();
		if (null != this._pageSwitcher) {
			this._pageSwitcher.Hide();
		}
	},

	ClickBody: function(ev) {
		this.msgBodyFocus = this._msgViewer.overMsgBody;
		this.CheckVisibilitySearchForm(ev);
	},

	KeyupBody: function(key, ev) {
		switch (key) {
			case Keys.Space:
				var scrolled = this._msgViewer.ScrollDown();
				if (scrolled) return;
				this._inboxTable.KeyUpHandler(Keys.Down, ev);
				break;
			case Keys.N:
				if (ev.shiftKey || ev.ctrlKey || ev.altKey) return;
				SetHistoryHandler({ ScreenId: SCREEN_NEW_MESSAGE });
				break;
			case Keys.R:
				if (ev.shiftKey || ev.ctrlKey || ev.altKey) return;
				WebMail.ReplyClick(TOOLBAR_REPLY);
				break;
			case Keys.S:
				if (ev.altKey) {
					this.FocusSearchForm();
				}
				break;
			default:
				if ((key == Keys.Shift || key == Keys.Ctrl) && ev.shiftKey && ev.ctrlKey) {
					this._msgViewer.ChangeRTL();
				}
				var msgBodyFocus = (Browser.Mozilla || Browser.Opera) ? this.msgBodyFocus : this._msgViewer.focusMsgBody;
				if (msgBodyFocus && (key == Keys.Up || key == Keys.Down || key == Keys.Home ||
					key == Keys.End || key == Keys.PageUp || key == Keys.PageDown)) {
					break;
				}
				this._inboxTable.KeyUpHandler(key, ev);
				break;
		}
	},

	ResizeInboxContainerWidth: function(width) {
		this._inboxWidth = width;
		this._inboxContainer.style.width = width + 'px';
	},

	IsInbox: function() {
		return (this.InboxId == this._folderId && this.InboxFullName == this._folderFullName);
	},

	IsSent: function() {
		return (this.SentId != -1 && this.SentFullName != '' &&
				this.SentId == this._folderId && this.SentFullName == this._folderFullName);
	},

	IsDrafts: function() {
		return (this.DraftsId != -1 && this.DraftsFullName != '' &&
				this.DraftsId == this._folderId && this.DraftsFullName == this._folderFullName);
	},

	IsTrash: function() {
		return (this.TrashId != -1 && this.TrashFullName != '' &&
				this.TrashId == this._folderId && this.TrashFullName == this._folderFullName);
	},

	DeleteLikePop3: function() {
		return ((WebMail.Accounts.CurrMailProtocol != IMAP4_PROTOCOL) || this._useImapTrash);
	},

	DeleteLikeImap: function() {
		return (WebMail.Accounts.CurrMailProtocol == IMAP4_PROTOCOL && !this._useImapTrash);
	},

	IsSpam: function() {
		return (this.SpamId != -1 && this.SpamFullName != '' &&
				this.SpamId == this._folderId && this.SpamFullName == this._folderFullName);
	},

	CleanFolderList: function() {
		this._foldersPane.CleanList();
		this.CleanMoveMenu();
		this.CleanSearchFolders(this._foldersObj.AllFoldersInDm);
	},

	CleanInboxLines: function(msg1, msg2) {
		this._inboxTable.CleanLines(msg1, msg2);
		if (null != this._pageSwitcher) {
			this._pageSwitcher.Hide();
		}
	},

	SetNoMessagesFoundMessage: function() {
		this._inboxTable.SetNoMessagesFoundMessage();
		if (null != this._pageSwitcher) {
			this._pageSwitcher.Hide();
		}
	},

	RedrawControls: function(redrawIndex, redrawElement, sortField, sortOrder, page) {
		switch (redrawIndex - 0) {
			case REDRAW_FOLDER:
				this.RedrawFolderControls(redrawElement);
				if (WebMail.ListScreenId == SCREEN_MESSAGE_LIST_TOP_PANE) this._inboxTable.SetSort(sortField, sortOrder);
				if (WebMail.ListScreenId == SCREEN_MESSAGE_LIST_CENTRAL_PANE) this._messageListPane.SetSort(sortField, sortOrder);
				this.RedrawPages(page);
				break;
			case REDRAW_HEADER:
				if (WebMail.ListScreenId == SCREEN_MESSAGE_LIST_TOP_PANE) this._inboxTable.SetSort(sortField, sortOrder);
				if (WebMail.ListScreenId == SCREEN_MESSAGE_LIST_CENTRAL_PANE) this._messageListPane.SetSort(sortField, sortOrder);
				break;
			case REDRAW_PAGE:
				this.RedrawPages(page);
				break;
		}
		//this.CleanInboxLines(Lang.InfoMessagesLoad);
	},

	ChangeDefOrder: function(defOrder) {
		if ((defOrder % 2) == SORT_ORDER_ASC) {
			this._sortField = defOrder - SORT_ORDER_ASC;
			this._sortOrder = SORT_ORDER_ASC;
		} else {
			this._sortField = defOrder;
			this._sortOrder = SORT_ORDER_DESC;
		}
	},

	GetDefOrder: function() {
		return this._sortField + this._sortOrder;
	},

	ChangeCurrFolder: function(id, fullName, div, count, syncType) {
		if (count == 0) {
			if (this._lookForStr.length > 0) {
				this.SetNoMessagesFoundMessage();
			}
			else {
				this.CleanInboxLines(Lang.InfoEmptyFolder);
			}
		}
		if (div) {
			this.RedrawFolderControls(div, id, fullName);
		}
		else {
			this.ChangeFolder(id, fullName);
		}
		this.WriteMsgsCountInFolder(count);
		this.RepairToolBar();

		this.isDirectMode = (syncType == SYNC_TYPE_DIRECT_MODE);
		this._useOrFreeSort(count);
	},

	_useOrFreeSort: function(msgsCountInFolder) {
		if (this.isDirectMode) {
			if (msgsCountInFolder > 0) {
				this._inboxTable.UseSort();
			}
			else {
				this._inboxTable.FreeSort();
			}
		}
		else {
			if (msgsCountInFolder > 0) {
				this._inboxTable.UseSort();
			}
			else {
				this._inboxTable.FreeSort();
			}
		}
	},

	GetCurrFolder: function() {
		if (this._folderId != -1) {
			return { Id: this._folderId, FullName: this._folderFullName };
		}
		else {
			return { Id: this.InboxId, FullName: this.InboxFullName };
		}
	},

	CleanMoveMenu: function() {
		CleanNode(this._moveMenu);
		this._inboxMoveItem = null;
	},

	AddToMoveMenu: function(folderId, folderFullName, folderName, isInboxFolder) {
		var item = CreateChild(this._moveMenu, 'div');
		item.onmouseover = function() { this.className = "wm_menu_item_over"; };
		item.onmouseout = function() { this.className = "wm_menu_item"; };
		item.onclick = CreateMoveActionFunc(folderId, folderFullName);
		item.className = 'wm_menu_item';
		item.innerHTML = folderName;
		if (isInboxFolder) {
			this._inboxMoveItem = item;
		}
	},

	AddToSearchFolders: function(name, id, fullName) {
		var option = CreateChild(this._searchIn, 'option');
		option.innerHTML = name;
		option.value = id + STR_SEPARATOR + fullName;
	},

	GetSearchParameters: function() {
		var searchIn = this._searchIn.value.split(STR_SEPARATOR);
		var searchMode = (this._quickSearch.checked) ? 0 : 1;
		var searchString = this.SearchFormObj.GetStringValue();
		return { FolderId: searchIn[0], FolderFullName: searchIn[1], Mode: searchMode, String: searchString };
	},

	PlaceSearchData: function(searchFields, lookFor) {
		this._quickSearch.checked = (searchFields == 0);
		this._slowSearch.checked = (searchFields == 1);
		this.SearchFormObj.SetStringValue(lookFor);
	},

	FocusSearchForm: function() {
		this.SearchFormObj.FocusSmallForm();
	},

	HideSearchFolders: function() {
		if (null != this.SearchFormObj && this.SearchFormObj.isShown == 0) {
			this._searchIn.className = 'wm_hide';
		}
	},

	CheckVisibilitySearchForm: function(ev) {
		if (null != this.SearchFormObj) {
			this.SearchFormObj.checkVisibility(ev, Browser.Mozilla);
		}
	},

	CleanSearchFolders: function(allFoldersInDirectMode) {
		CleanNode(this._searchIn);
		if (!allFoldersInDirectMode && window.UseDb) {
			var option = CreateChild(this._searchIn, 'option');
			option.value = '-1' + STR_SEPARATOR;
			option.innerHTML = Lang.AllMailFolders;
		}
		this.HideSearchFolders();
	},

	ShowSearchForm: function() {
		if (null != this.SearchFormObj) {
			this.SearchFormObj.Show();
		}
	},

	HideSearchForm: function() {
		if (null != this.SearchFormObj) {
			this.SearchFormObj.Show();
		}
	},

	Pop3DeleteToolEnabled: function() {
		var enabled = (this._pop3DeleteTool.enabled == false) ? false : true;
		return enabled;
	},

	AlreadyPop3Deleted: function(idArray) {
		if (isEqualArray(this._pop3DeleteTool.idArray, idArray)) {
			return true;
		}
		return false;
	},

	DisablePop3DeleteTool: function(idArray) {
		this._pop3DeleteTool.enabled = false;
		this._pop3DeleteTool.className = "wm_tb wm_toolbar_item_disabled";
		this._pop3DeleteTool.idArray = idArray;
	},

	ClearDeleteTools: function() {
		if (this._imap4DeleteTool) {
			this._imap4DeleteTool.Enable();
			if (this._imap4DeleteTool.idArray) {
				this._imap4DeleteTool.idArray = [];
			}
		}
		if (this._pop3DeleteTool) {
			this._pop3DeleteTool.enabled = true;
			this._pop3DeleteTool.className = "wm_tb";
			if (this._pop3DeleteTool.idArray) {
				this._pop3DeleteTool.idArray = [];
			}
		}
	},

	ImapDeleteToolEnabled: function() {
		var enabled = (this._imap4DeleteTool.enabled == false) ? false : true;
		return enabled;
	},

	AlreadyImapDeleted: function(idArray) {
		if (isEqualArray(this._imap4DeleteTool.idArray, idArray)) {
			return true;
		}
		return false;
	},

	DisableImapDeleteTool: function(idArray) {
		this._imap4DeleteTool.Disable();
		this._imap4DeleteTool.idArray = idArray;
	},

	SpamToolEnabled: function(type) {
		var tool = (type == TOOLBAR_IS_SPAM) ? this._isSpamTool : this._notSpamTool;
		var enabled = (tool.enabled == false) ? false : true;
		return enabled;
	},

	AlreadyMarkedSpam: function(type, idArray) {
		var tool = (type == TOOLBAR_IS_SPAM) ? this._isSpamTool : this._notSpamTool;
		if (isEqualArray(tool.idArray, idArray)) {
			return true;
		}
		tool.Disable();
		tool.idArray = idArray;
		return false;
	},

	GetXmlMessagesOperation: function(type, idArray, sizeArray, toFolderId, toFolderFullName) {
		if (type == TOOLBAR_DELETE && this.DeleteLikePop3() && !this.Pop3DeleteToolEnabled() ||
				type == TOOLBAR_DELETE && this.DeleteLikeImap() && !this.ImapDeleteToolEnabled() ||
				type == TOOLBAR_NOT_SPAM && !this.SpamToolEnabled(TOOLBAR_NOT_SPAM) ||
				type == TOOLBAR_IS_SPAM && !this.SpamToolEnabled(TOOLBAR_IS_SPAM)) {
			return false;
		}

		if ((type == TOOLBAR_IS_SPAM || type == TOOLBAR_NOT_SPAM) && WebMail._isDemo) {
			WebMail.ShowReport(DemoWarning);
			return false;
		}

		var xml = '';
		if (type == TOOLBAR_MOVE_TO_FOLDER /*|| type == TOOLBAR_COPY_TO_FOLDER*/) {
			if (toFolderId == this._folderId && toFolderFullName == this._folderFullName) {
				return false;
			}
		}
		else if (type == TOOLBAR_IS_SPAM) {
			toFolderId = this.SpamId;
			toFolderFullName = this.SpamFullName;
		}
		else if (type == TOOLBAR_NOT_SPAM) {
			toFolderId = this.InboxId;
			toFolderFullName = this.InboxFullName;
		}
		else {
			toFolderId = -1;
			toFolderFullName = '';
		}
		var unreaded = 0;
		var messagesXml = '<messages>';
		messagesXml += '<look_for fields="' + this._searchMode + '">' + GetCData(this._lookForStr) + '</look_for>';
		messagesXml += '<to_folder id="' + toFolderId + '"><full_name>' + GetCData(toFolderFullName) + '</full_name></to_folder>';
		if (type == TOOLBAR_PURGE && this.DeleteLikePop3()) {
			messagesXml += '<folder id="' + this.TrashId + '"><full_name>' + GetCData(this.TrashFullName) + '</full_name></folder>';
		}
		else {
			messagesXml += '<folder id="' + this._folderId + '"><full_name>' + GetCData(this._folderFullName) + '</full_name></folder>';
		}
		if (type == TOOLBAR_MARK_ALL_READ || type == TOOLBAR_MARK_ALL_UNREAD || type == TOOLBAR_PURGE || type == TOOLBAR_EMPTY_SPAM) {
			xml = messagesXml + '</messages>';
		}
		else {
			if (idArray.length == 0) {
				var res = this._selection.GetCheckedLines();
				idArray = res.IdArray;
				sizeArray = res.SizeArray;
				unreaded = res.Unreaded;
			}
			var removeMessage = false;
			for (var i in idArray) {
				var msg = new CMessage();
				msg.GetFromIdForList(idArray[i]);
				msg.Size = sizeArray[i];
				xml += '<message id="' + msg.Id + '" charset="' + msg.Charset + '" size="' + msg.Size + '">';
				xml += '<uid>' + GetCData(HtmlDecode(msg.Uid)) + '</uid>';
				xml += '<folder id="' + msg.FolderId + '"><full_name>' + GetCData(msg.FolderFullName) + '</full_name></folder>';
				xml += '</message>';
				if (null != this._msgObj && this._msgObj.IsEqual(msg)) {
					removeMessage = true; //message in preview pane is message for operation
				}
			}
			if (xml != '') {
				xml = messagesXml + xml + '</messages>';
			}
		}
		if (xml == '') {
			alert(Lang.WarningMarkListItem);
			return false;
		}
		var paramIndex;
		var count = idArray.length;
		if (type == TOOLBAR_MOVE_TO_FOLDER || type == TOOLBAR_IS_SPAM
			|| type == TOOLBAR_NOT_SPAM /*|| type == TOOLBAR_COPY_TO_FOLDER*/) {
			paramIndex = toFolderId + toFolderFullName;
			if (this._foldersParam[paramIndex]) {
				this._foldersParam[paramIndex].AddToAppend(count, unreaded);
			}
		}
		paramIndex = this._folderId + this._folderFullName;
		var params = this._foldersParam[paramIndex];
		if (params) {
			switch (type) {
				case TOOLBAR_MARK_ALL_READ:
					params.AddAllToRead();
					break;
				case TOOLBAR_MARK_ALL_UNREAD:
					params.AddAllToUnread();
					break;
				case TOOLBAR_MARK_READ:
					params.AddToRead(unreaded);
					break;
				case TOOLBAR_MARK_UNREAD:
					params.AddToUnread(count - unreaded);
					break;
				case TOOLBAR_MOVE_TO_FOLDER:
				case TOOLBAR_IS_SPAM:
				case TOOLBAR_NOT_SPAM:
					params.AddToRemove(count, unreaded);
					this._removeCount = count;
					break;
				case TOOLBAR_DELETE:
					if (!this.IsSpam()) {
						params.AddToRemove(count, unreaded);
						this._removeCount = count;
						if (this.DeleteLikePop3()) {
							var paramIndexT = this.TrashId + this.TrashFullName;
							if (this._foldersParam[paramIndexT]) {
								this._foldersParam[paramIndexT].AddToAppend(count, unreaded);
							}
							else {
								var stringDataKey = WebMail.DataSource.GetStringDataKey(TYPE_FOLDER_LIST, { IdAcct: WebMail._idAcct });
								WebMail.DataSource.Cache.RemoveData(TYPE_FOLDER_LIST, stringDataKey);
								this.needToRefreshFolders = true;
							}
						}
					}
					break;
			}
		}
		if (xml.length > 0) {
			if (type == TOOLBAR_DELETE && this.DeleteLikeImap()) {
				if (!confirm(Lang.ConfirmDirectModeAreYouSure)) xml = '';
			}
			if (type == TOOLBAR_PURGE || type == TOOLBAR_EMPTY_SPAM) {
				if (!confirm(Lang.ConfirmMessagesPermanentlyDeleted)) xml = '';
			}
			if (type == TOOLBAR_DELETE && this.DeleteLikePop3()
				&& (this.IsTrash() || this.IsSpam())) {
				if (!confirm(Lang.ConfirmAreYouSure)) xml = '';
			}
			if (xml.length > 0) {
				if (type == TOOLBAR_FLAG || type == TOOLBAR_UNFLAG) {
					var operationValue = (type == TOOLBAR_FLAG);
					this._selection.SetParams(idArray, 'Flagged', operationValue, false);
					WebMail.DataSource.NeedInfo = false;
				}
				if (type == TOOLBAR_DELETE || type == TOOLBAR_NO_MOVE_DELETE) {
					if (this.DeleteLikePop3()) {
						if (type == TOOLBAR_DELETE && this.AlreadyPop3Deleted(idArray)) {
							return false;
						}
						this.DisablePop3DeleteTool(idArray);
					}
					if (this.DeleteLikeImap()) {
						if (type == TOOLBAR_DELETE && this.AlreadyImapDeleted(idArray)) {
							return false;
						}
						this.DisableImapDeleteTool(idArray);
					}
				}
				if (type == TOOLBAR_NOT_SPAM && this.AlreadyMarkedSpam(TOOLBAR_NOT_SPAM, idArray)) {
					return false;
				}
				if (type == TOOLBAR_IS_SPAM && this.AlreadyMarkedSpam(TOOLBAR_IS_SPAM, idArray)) {
					return false;
				}
				WebMail.DataSource.Request({ action: 'operation_messages', request: OperationTypes[type] }, xml);
				var viewMessageRemoved = (type == TOOLBAR_MOVE_TO_FOLDER || type == TOOLBAR_IS_SPAM
					|| type == TOOLBAR_NOT_SPAM || type == TOOLBAR_DELETE || type == TOOLBAR_NO_MOVE_DELETE)
					&& removeMessage;
				var allMessagesRemoved = (type == TOOLBAR_PURGE || type == TOOLBAR_EMPTY_SPAM);
				if (viewMessageRemoved || allMessagesRemoved) {
					//message in preview pane will remove
					this.CleanMessageBody(true);
				}
			}
		}
		return true;
	},

	IsEmptyFolder: function(folderId, folderFullName) {
		return false;
		var paramIndex = folderId + folderFullName;
		var params = this._foldersParam[paramIndex];
		if (params && params.MsgsCount == 0 && !params.SearchResults) {
			return true;
		}
		else {
			return false;
		}
	},

	FillByFolders: function() {
		this._dragNDrop.CleanDropObjects();
		var arFolders = this._foldersObj.Folders;
		for (var key in arFolders) {
			var thisFolder = arFolders[key];
			if (!thisFolder.ListHide) {
				var intIndent = thisFolder.Level * FOLDERS_TREES_INDENT;
				var strIndent = '';
				for (var j = 0; j < thisFolder.Level; j++) strIndent += '&nbsp;&nbsp;&nbsp;&nbsp;';
				if (this._folderId == -1 && thisFolder.Type == FOLDER_TYPE_INBOX && this._lookForStr.length == 0) {
					this.ChangeFolder(thisFolder.Id, thisFolder.FullName);
				}
				if (this.InboxId == -1 && thisFolder.Type == FOLDER_TYPE_INBOX) {
					this.InboxId = thisFolder.Id;
					this.InboxFullName = thisFolder.FullName;
					if (thisFolder.SyncType == SYNC_TYPE_DIRECT_MODE) {
						this.isInboxDirectMode = true;
					}
					else {
						this.isInboxDirectMode = false;
					}
				}

				switch (thisFolder.Type) {
					case FOLDER_TYPE_SENT:
						this.SentId = thisFolder.Id;
						this.SentFullName = thisFolder.FullName;
						break;
					case FOLDER_TYPE_DRAFTS:
						this.DraftsId = thisFolder.Id;
						this.DraftsFullName = thisFolder.FullName;
						break;
					case FOLDER_TYPE_TRASH:
						this.TrashId = thisFolder.Id;
						this.TrashFullName = thisFolder.FullName;
						break;
					case FOLDER_TYPE_SPAM:
						this.SpamId = thisFolder.Id;
						this.SpamFullName = thisFolder.FullName;
						break;
				}

				var fName = thisFolder.GetNameByType();
				var notDirectMode = (thisFolder.SyncType != SYNC_TYPE_DIRECT_MODE);
				var notPop3 = (WebMail.Accounts.CurrMailProtocol != POP3_PROTOCOL);
				if (notDirectMode || notPop3) {
					var isInboxFolder = (thisFolder.Id == this.InboxId && thisFolder.FullName == this.InboxFullName);
					this.AddToMoveMenu(thisFolder.Id, thisFolder.FullName, strIndent + fName, isInboxFolder);
					this.AddToSearchFolders(strIndent + fName, thisFolder.Id, thisFolder.FullName);
				}

				var paramIndex = thisFolder.Id + thisFolder.FullName;
				var params = this._foldersParam[paramIndex];
				if (!params) {
					params = new CFolderParams(thisFolder.Id, thisFolder.FullName, thisFolder.SentDraftsType, thisFolder.Type, thisFolder.SyncType, thisFolder.MsgCount, thisFolder.NewMsgCount, fName, intIndent);
					if (WebMail.Accounts.CurrMailProtocol == IMAP4_PROTOCOL) {
						params.ChangeImgType();
					}
				}

				var div = CreateChild(this._foldersPane.List, 'div', [['class', 'wm_folder']]);
				var obj = this;
				var clickHandler = CreateFolderClickFunc(thisFolder.Id, thisFolder.FullName, obj, div);
				params.SetDiv(div, this._skinName, clickHandler);
				if (this._folderId == thisFolder.Id && this._folderFullName == thisFolder.FullName) {
					this.ChangeCurrFolder(thisFolder.Id, thisFolder.FullName, div, params.MsgsCount, thisFolder.SyncType, thisFolder.Type);
				}
				div.id = thisFolder.Id + STR_SEPARATOR + thisFolder.FullName;
				if (thisFolder.Type == FOLDER_TYPE_INBOX) {
					this._dragNDrop.SetInboxId(div.id);
				}
				this._dragNDrop.AddDropObject(div);
				this._foldersParam[paramIndex] = params;
			}
		} //for
		this.RepairToolBar();
		this.HideSearchFolders();
	}, //FillByFolders

	_getSearchResultsMessage: function(folderId, folderName, folderType, lookForString) {
		if (folderId == -1) {
			return Lang.SearchResultsInAllFolders.replace(/#s/g, lookForString);
		}
		else {
			var normalNameFolder = new CFolder(0, false);
			normalNameFolder.Type = folderType;
			normalNameFolder.Name = folderName;
			var normalName = normalNameFolder.GetNameByType();
			return Lang.SearchResultsInFolder.replace(/#s/g, lookForString).replace(/#f/g, normalName);
		}
	},

	FillByMessages: function() {
		var msgsArray = new Array();
		var msgsObj = this._messagesObj;
		if (msgsObj != null) {
			msgsArray = msgsObj.List;
		}
		if (msgsArray.length == 0) {
			if (this._lookForStr.length > 0) {
				this.SetNoMessagesFoundMessage();
			} else {
				this.CleanInboxLines(Lang.InfoEmptyFolder);
			}
		} else {
			if (WebMail.ListScreenId == SCREEN_MESSAGE_LIST_TOP_PANE) this._inboxTable.SetSort(this._sortField, this._sortOrder);
			if (WebMail.ListScreenId == SCREEN_MESSAGE_LIST_CENTRAL_PANE) this._messageListPane.SetSort(this._sortField, this._sortOrder);
			var doFlag = !(WebMail.Accounts.CurrMailProtocol == POP3_PROTOCOL && this.isInboxDirectMode == true && this.IsInbox());
			this._inboxController.SetDoFlag(doFlag);
			var additionalMessage = '';
			if (msgsObj.LookFor.length > 0) {
				additionalMessage = this._getSearchResultsMessage(msgsObj.FolderId, msgsObj.FolderName, msgsObj.FolderType, msgsObj.LookFor)
				if (msgsObj.FolderId == -1) {
					additionalMessage = Lang.SearchResultsInAllFolders.replace(/#s/g, msgsObj.LookFor);
				} else {
					var normalNameFolder = new CFolder(0, false);
					normalNameFolder.Type = msgsObj.FolderType;
					normalNameFolder.Name = msgsObj.FolderName;
					var normalName = normalNameFolder.GetNameByType();
					additionalMessage = Lang.SearchResultsInFolder.replace(/#s/g, msgsObj.LookFor).replace(/#f/g, normalName);
				}
				/*additionalMessage = (msgsObj.FolderId == -1)
				? Lang.SearchResultsInAllFolders.replace(/#s/g, msgsObj.LookFor)
				: Lang.SearchResultsInFolder.replace(/#s/g, msgsObj.LookFor).replace(/#f/g, msgsObj.FolderFullName); */
			}

			var addClearLink = true;
			/*
			if (additionalMessage == '') {
			if (this.IsSpam()) {
			additionalMessage = 'SPAM';
			addClearLink = false;
			}
			else if (this.IsTrash()) {
			additionalMessage = 'TRASH';
			addClearLink = false;
			}
			}*/

			this._inboxTable.Fill(msgsArray, this.Id, additionalMessage, addClearLink);
			this.RedrawPages(this._page);
			if (this.Id == SCREEN_MESSAGE_LIST_TOP_PANE) {
				if (this.IsDrafts()) {
					this._replyTool.className = 'wm_hide';
					this._forwardTool.Hide();
				} else {
					this._replyTool.className = 'wm_tb';
					this._forwardTool.Show();
				}
				if (null != this._pageSwitcher) {
					this._pageSwitcher.Replace();
				}
				this.ResizeInboxWidth();
			}
			else {
				WebMail.ResizeBody(RESIZE_MODE_ALL);
			}
		}
	}, //FillByMessages

	GetPage: function(page) {
		SetHistoryHandler({ ScreenId: this.Id,
			FolderId: this._folderId,
			FolderFullName: this._folderFullName,
			Page: page,
			SortField: this._sortField,
			SortOrder: this._sortOrder,
			LookForStr: this._lookForStr,
			SearchMode: this._searchMode,
			RedrawType: REDRAW_PAGE,
			RedrawObj: null,
			MsgId: null,
			MsgUid: null,
			MsgFolderId: null,
			MsgFolderFullName: null,
			MsgCharset: null,
			MsgParts: null
		}
		);
	},

	RedrawPages: function(page) {
		if (this._messagesObj && this._pageSwitcher) {
			var perPage = this._messagesPerPage;
			var count = this._messagesObj.MessagesCount;
			if (this.shown && null != this._pageSwitcher) {
				this._pageSwitcher.Show(page, perPage, count, 'GetPageMessagesHandler(', ');');
				this._pageSwitcher.Replace();
			}
		}
	},

	HeaderClickFunc: function(sortField, sortOrder) {
		SetHistoryHandler(
			{
				ScreenId: this.Id,
				FolderId: this._folderId,
				FolderFullName: this._folderFullName,
				Page: this._page,
				SortField: sortField,
				SortOrder: sortOrder,
				LookForStr: this._lookForStr,
				SearchMode: this._searchMode,
				RedrawType: REDRAW_HEADER,
				RedrawObj: null,
				MsgId: null,
				MsgUid: null,
				MsgFolderId: null,
				MsgFolderFullName: null,
				MsgCharset: null,
				MsgParts: null
			}
		);
	},

	BuildInboxTable: function() {
		this._inboxController = new CMessageListTableController('ClickMessageHandler');
		var inboxTable;
		if (this.Id == SCREEN_MESSAGE_LIST_CENTRAL_PANE) {
			inboxTable = new CMessageListDisplay(this._selection, this._dragNDrop, this._inboxController);
		}
		else {
			inboxTable = new CVariableTable(SortMessagesHandler, this._selection, this._dragNDrop, this._inboxController);
		}
		inboxTable.AddColumn(IH_CHECK, InboxHeaders[IH_CHECK]);
		if (window.AddPriorityHeader) {
			inboxTable.AddColumn(IH_PRIORITY, InboxHeaders[IH_PRIORITY]);
		}
		if (window.AddSensivityHeader) {
			inboxTable.AddColumn(IH_SENSIVITY, InboxHeaders[IH_SENSIVITY]);
		}
		inboxTable.AddColumn(IH_ATTACHMENTS, InboxHeaders[IH_ATTACHMENTS]);
		inboxTable.AddColumn(IH_FLAGGED, InboxHeaders[IH_FLAGGED]);
		if (this.Id == SCREEN_MESSAGE_LIST_CENTRAL_PANE) {
			inboxTable.AddColumn(IH_FROM, InboxHeaders[IH_FROM]);
			inboxTable.Build(this._messageListPane.MainContainer);
		}
		else {
			this._fromColumn = inboxTable.AddColumn(IH_FROM, InboxHeaders[IH_FROM]);
			this._dateColumn = inboxTable.AddColumn(IH_DATE, InboxHeaders[IH_DATE]);
			this._sizeColumn = inboxTable.AddColumn(IH_SIZE, InboxHeaders[IH_SIZE]);
			this._subjectColumn = inboxTable.AddColumn(IH_SUBJECT, InboxHeaders[IH_SUBJECT]);
			inboxTable.Build(this._inboxContainer);
		}
		this._inboxTable = inboxTable;
	},

	ResetReplyTools: function(msg) {
		if (msg == null || msg.Sensivity != SENSIVITY_NOTHING || msg.NoForward) {
			this._forwardTool.Disable();
		}
		else {
			this._forwardTool.Enable();
		}
		if (msg == null || msg.NoReply) {
			this._replyButton.Disable();
			this._replyPopupMenu.disable = true;
		}
		else {
			this._replyButton.Enable();
			this._replyPopupMenu.disable = false;
		}
		if (msg == null || msg.NoReplyAll) {
			this._replyAllButton.Disable();
		}
		else {
			var recipients = msg.GetRecipientsFromEmailStr(msg.ToAddr);
			if (!((msg.BCCAddr == "") && (msg.CCAddr == "") && (recipients.length == 1))) {
				this._replyAllButton.Enable();
			}
			else {
				this._replyAllButton.Disable();
			}
		}
	},

	BuildAdvancedSearchForm: function() {
		var obj = this;
		var div = CreateChild(document.body, 'div', [['id', this.SearchFormId]]);
		this._bigSearchForm = div;
		div.className = 'wm_hide';
		var frm = CreateChild(div, 'form');
		frm.onsubmit = function() { return false; };
		var tbl = CreateChild(frm, 'table');
		var tr = tbl.insertRow(0);
		var td = tr.insertCell(0);
		td.className = 'wm_search_title';
		td.innerHTML = Lang.LookFor;
		WebMail.LangChanger.Register('innerHTML', td, 'LookFor', '');
		td = tr.insertCell(1);
		td.className = 'wm_search_value';
		var lookForBigInp = CreateChild(td, 'input', [['type', 'text'], ['maxlength', '255']]);
		lookForBigInp.className = 'wm_search_input';
		this._toolBar.CreateSearchButton(td, function() { obj.RequestSearchResults(); });
		lookForBigInp.onkeypress = function(ev) {
			if (isEnter(ev)) {
				obj.RequestSearchResults();
			}
		};
		tr = tbl.insertRow(1);
		td = tr.insertCell(0);
		td.className = 'wm_search_title';
		td.innerHTML = Lang.SearchIn;
		WebMail.LangChanger.Register('innerHTML', td, 'SearchIn', '');
		td = tr.insertCell(1);
		td.className = 'wm_search_value';
		this._searchIn = CreateChild(td, 'select');
		if (null != this.SearchFormObj) this.SearchFormObj.SetSearchIn(this._searchIn);
		tr = tbl.insertRow(2);
		td = tr.insertCell(0);
		td.className = 'wm_search_value';
		td.colSpan = 2;
		var nobr = CreateChild(td, 'nobr');
		var inp = CreateChild(nobr, 'input', [['type', 'radio'], ['name', 'qsmode' + this.Id], ['id', 'qmode' + this.Id]]);
		this._quickSearch = inp;
		inp.className = 'wm_checkbox';
		inp.checked = true;
		var lbl = CreateChild(nobr, 'label', [['for', 'qmode' + this.Id]]);
		lbl.innerHTML = Lang.QuickSearch;
		WebMail.LangChanger.Register('innerHTML', lbl, 'QuickSearch', '');
		tr = tbl.insertRow(3);
		td = tr.insertCell(0);
		td.className = 'wm_search_value';
		td.colSpan = 2;
		nobr = CreateChild(td, 'nobr');
		inp = CreateChild(nobr, 'input', [['type', 'radio'], ['name', 'qsmode' + this.Id], ['id', 'smode' + this.Id]]);
		this._slowSearch = inp;
		inp.className = 'wm_checkbox';
		inp.checked = false;
		lbl = CreateChild(nobr, 'label', [['for', 'smode' + this.Id]]);
		lbl.innerHTML = Lang.SlowSearch;
		WebMail.LangChanger.Register('innerHTML', lbl, 'SlowSearch', '');

		return lookForBigInp;
	} // BuildAdvancedSearchForm
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}