/*
 * Functions:
 *  HideCalendar(link, helpParam)
 *  CreateAccountActionFunc(id)
 * Classes:
 * 	CWebMail(Title, SkinName)
 * 	CSettingsList()
 */

function HideCalendar(link, helpParam)
{
	switch (link) {
		case 'account':
			WebMail.ShowMail(helpParam);
		break;
		case 'contacts':
			WebMail.ShowContacts();
		break;
		case 'settings':
			WebMail.ShowSettings();
		break;
		case 'logout':
			WebMail.LogOut();
		break;
		case 'error':
			WebMail.LogOut(helpParam);
		break;
	}
}

function CreateAccountActionFunc(id)
{
	return function() {
		SetHistoryHandler(
			{
				ScreenId: WebMail.ListScreenId,
				IdAcct: id
			}
		);
	};
}

function CWebMail(Title, SkinName){
	this.isBuilded = false;
	this.shown = false;
	
	this.FolderList = null;
	this.Accounts = null;
	this.SectionId = -1;
	this.Sections = Array();
	this.ScreenId = -1;
	this.Screens = Array();
	this.DataSource = null;
	this.ScriptLoader = new CScriptLoader();
	this.Settings = null;
	this.ListScreenId = -1;
	this.StartScreen = -1;
	this.ScreenIdForLoad = this.ListScreenId;
	this._message = null;
	this._replyAction = -1;
	this._replyText = '';
	this.forEditParams = [];
	this.fromDraftsParams = [];
	this.FoldersToUpdate = [];

	this._title = Title;
	this._skinName = SkinName;
	this.LangChanger = new CLanguageChanger();
	this._allowContacts = true;
	this._allowCalendar = true;
	this._email = '';
	this._isDemo = false;
	this._defOrder = SORT_ORDER_ASC;

	this._html = document.getElementById('html');
	this._content = document.getElementById('content');
	this._copyright = document.getElementById('copyright');
	this.PopupMenus = null;
	this._skinLink = document.getElementById('skin');
	this._newSkinLink = null;
	this._rtlSkinLink = document.getElementById('skin-rtl');
	this._newRtlSkinLink = null;
	this._head = document.getElementsByTagName('head')[0];
	
	this._accountsBar = null;
	this._accountControl = null;
	this._accountsList = null;
	this._accountNameObject = null;
	this._mailTab = null;
	this._contactsTab = null;
	this._calendarTab = null;
	this._settingsTab = null;

	this.FadeEffect = new CFadeEffect('WebMail.FadeEffect');
	this.InfoContainer = new CInfoContainer('WebMail.InfoContainer', this.FadeEffect);

	this.CheckMail = new CCheckMail();

	this._idAcct = -1;
	this.HistoryArgs = null;
	this.HistoryObj = null;
	this.MailHistoryArgs = null;
	
	this._msgsPerPage = 20;
	this._allowDhtmlEditor = false;
	this._allowChangeSettings = true;
	this._timeOffset = 0;
	this._timeFormat = 0;
	this._defLang = '';
	this._viewMode = VIEW_MODE_CENTRAL_LIST_PANE;
	this._useImapTrash = false;	
	
	this._messageList = null;
	
	this._spellchecker = new CSpellchecker();
	
	this._gotFoldersMessageList = [];

//	this.isSaverWork = false;
	this.timer = null;
	this.IdleSessionTimeout = 0;
	this._checkMailInterval = 0;
	this._checkMailIntervalHandle = null;

	this.mouseX = 0;
	this.mouseY = 0;
	var obj = this;
	
	if (this._content) {
		this._content.onmousemove = function (e) {
			if (Browser.IE) { // grab the x-y pos.s if browser is IE
				obj.mouseX = event.clientX + document.body.scrollLeft
				obj.mouseY = event.clientY + document.body.scrollTop
			} else {  // grab the x-y pos.s if browser is NS
				obj.mouseX = e.pageX
				obj.mouseY = e.pageY
			}
			// catch possible negative values in NS4
			if (obj.mouseX < 0) { obj.mouseX = 0 }
			if (obj.mouseY < 0) { obj.mouseY = 0 }
		}
	}
}

CWebMail.prototype = {
	_requestMessageListNextPage: function ()
	{
		var screen = this.Screens[this.ListScreenId];
		if (screen) {
			var histObj = screen.GetCurrFolderHistoryObject();
			var xml = '<folder id="' + histObj.FolderId + '"><full_name>' + GetCData(histObj.FolderFullName) + '</full_name></folder>';
			xml += '<look_for fields="' + histObj.SearchMode + '">' + GetCData(histObj.LookForStr) + '</look_for>';
			var lastPage = screen._pageSwitcher.GetLastPage(0);
			if (lastPage >= (histObj.Page + 1)) {
				GetHandler(TYPE_MESSAGE_LIST, { IdAcct: WebMail._idAcct, Page: (histObj.Page + 1), SortField: histObj.SortField, 
					SortOrder: histObj.SortOrder, FolderId: histObj.FolderId, 
					FolderFullName: histObj.FolderFullName, LookFor: histObj.LookForStr, 
					SearchFields: histObj.SearchMode }, [], xml, true );
			}
			else {
				this.RequestFoldersMessageList();
			}
		}
	},
	
	_requestOtherAccountsFolders: function ()
	{
		for (var i = (this.Accounts.Items.length - 1); i >= 0; i--) {
			var id = this.Accounts.Items[i].Id;
			if (id != this._idAcct) {
				var stringDataKey = WebMail.DataSource.GetStringDataKey(TYPE_FOLDER_LIST, {IdAcct: id});
				if (!WebMail.DataSource.Cache.ExistsData(TYPE_FOLDER_LIST, stringDataKey)) {
					GetHandler(TYPE_ACCOUNT_BASE, { IdAcct: id, ChangeAcct: 0 }, [], '', true);
				}
			}
		}
	},
	
	_requestMessageReplyPart: function (msg, background)
	{
		var xml = '<param name="uid">' + GetCData(HtmlDecode(msg.Uid)) + '</param>';
		xml += '<folder id="' + msg.FolderId + '"><full_name>' + GetCData(msg.FolderFullName) + '</full_name></folder>';
		var parts = (msg.HasHtml) ? [PART_MESSAGE_REPLY_HTML] : [PART_MESSAGE_REPLY_PLAIN];
		GetHandler(TYPE_MESSAGE, {Id: msg.Id, Charset: msg.Charset, Uid: msg.Uid, 
			FolderId: msg.FolderId, FolderFullName: msg.FolderFullName}, parts, xml, background);
	},
	
	PlaceData: function(Data)
	{
		var Type = Data.Type;
		switch (Type) {
			case TYPE_MESSAGES_BODIES:
				DoPrefetch();

				if (!preFetchStartFlag) {
					this._requestMessageListNextPage();
					this._requestOtherAccountsFolders();
				}
				break;
			case TYPE_ACCOUNT_LIST:
				if (this.Accounts != null && this.Accounts.Count > 0 && Data.Count > this.Accounts.Count) {
					this.ShowReport(Lang.ReportAccountCreatedSuccessfuly);
				}
				if (this.Accounts != null && Data.Items.length == 0) {
					document.location = LoginUrl;
				} else {
					if (this.Accounts != null && Data.Items.length != this.Accounts.Items.length ||
					 this._idAcct != Data.CurrId) {
						var screen = this.Screens[SCREEN_CALENDAR];
						if (screen) screen.NeedReload();
						screen = this.Screens[SCREEN_MESSAGE_LIST_CENTRAL_PANE];
						if (screen) screen.needToRefreshFolders = true;
						screen = this.Screens[SCREEN_MESSAGE_LIST_TOP_PANE];
						if (screen) screen.needToRefreshFolders = true;
					}
					this.Accounts = Data;
					this._idAcct = Data.CurrId;
					this.FillAccountsList();
					var screen = this.Screens[SCREEN_USER_SETTINGS];
					if (screen) {
						screen.PlaceData(Data);
					}
				}
				break;
			case TYPE_SETTINGS_LIST:
				Data = this.CheckWmCookies(Data);
				this.Settings = Data;
				this._allowContacts = Data.AllowContacts;
				this._allowCalendar = Data.AllowCalendar;
				this.SetActiveTab();
				this.ParseSettings();
				var screen = this.Screens[this.ListScreenId];
				if (screen) {
					screen.ParseSettings(Data);
				}
				if (this.ScreenId == -1) {
					if (this.StartScreen != -1) {
						SelectScreenHandler(this.StartScreen);
					} else {
						if (this.ListScreenId != -1) {
							SelectScreenHandler(this.ListScreenId);
						} else {
							SelectScreenHandler(SCREEN_MESSAGE_LIST_CENTRAL_PANE);
						}
					}
				}
				break;
			case TYPE_FILTERS:
				this.Accounts.UpdateFilters(Data);
				var screen = this.Screens[SCREEN_USER_SETTINGS];
				if (screen) {
					screen.PlaceData(Data);
				}
				break;
			case TYPE_UPDATE:
				switch (Data.Value) {
					case 'cookie_settings':
						this.EraseWmCookies();
						break;
					case 'settings':
						this.ShowReport(Lang.ReportSettingsUpdatedSuccessfuly);
						var screen = this.Screens[SCREEN_USER_SETTINGS];
						if (screen) {
							var settings = screen.GetNewSettings();
							this.UpdateSettings(settings);
							if (this.ScreenId != -1) {
								this.Screens[this.ScreenId].ParseSettings(this.Settings);
							}
						}
						break;
					case 'mobile_sync':
						this.ShowReport(Lang.ReportSettingsUpdatedSuccessfuly);
						var screen = this.Screens[SCREEN_USER_SETTINGS];
						if (screen) {
							screen.UpdateMobileSync();
						}
						break;
					case 'account':
						this.ShowReport(Lang.ReportAccountUpdatedSuccessfuly);
						var screen = this.Screens[SCREEN_USER_SETTINGS];
						if (screen) {
							var newAcctProp = screen.GetNewAccountProperties();
							var changedDirectMode = this.Accounts.AplyNewAccountProperties(newAcctProp)
							var listScreen = this.Screens[this.ListScreenId];
							if (listScreen) {
								if (newAcctProp.MailProtocol == POP3_PROTOCOL && changedDirectMode) {
								    var stringDataKey = this.DataSource.GetStringDataKey(TYPE_FOLDER_LIST, {IdAcct: newAcctProp.Id});
								    this.DataSource.Cache.RemoveData(TYPE_FOLDER_LIST, stringDataKey);
								    this.DataSource.Cache.ClearMessageList(listScreen.InboxId, listScreen.InboxFullName);
								    var isDirectMode = (SYNC_TYPE_DIRECT_MODE == newAcctProp.InboxSyncType);
									if (newAcctProp.Id == this._idAcct && isDirectMode != listScreen.isInboxDirectMode) {
									    if (this.ListScreenId == this.ScreenId) {
									        GetHandler(TYPE_FOLDER_LIST, { IdAcct: this._idAcct, Sync: GET_FOLDERS_NOT_SYNC }, [], '');
									    } else {
									        listScreen.needToRefreshFolders = true;
									    }
									    var params = listScreen._foldersParam[listScreen.InboxId + listScreen.InboxFullName];
									    if (params != undefined) {
									        params._syncType = newAcctProp.InboxSyncType;
									        listScreen._foldersParam[listScreen.InboxId + listScreen.InboxFullName] = params;
									    }
									    listScreen.needToRefreshMessages = true;
									    listScreen.isInboxDirectMode = isDirectMode;
									    listScreen.RepairToolBar();
									}
								}
							}
						}
						break;
					case 'signature':
						this.ShowReport(Lang.ReportSignatureUpdatedSuccessfuly);
						var screen = this.Screens[SCREEN_USER_SETTINGS];
						if (screen) {
							var signature = screen.GetNewSignature();
							this.Accounts.UpdateEditableSignature(signature.Value, signature.Opt, signature.IsHtml);
						}
						break;
					case 'autoresponder':
						this.ShowReport(Lang.ReportAutoresponderUpdatedSuccessfuly);
						var screen = this.Screens[SCREEN_USER_SETTINGS];
						if (screen) {
							screen.SetNewAutoresponder();
						}
						break;
					case 'send_confirmation':
						this.ShowReport(Lang.ReportMessageSent);
						break;


					case 'send_message':
						this.ShowReport(Lang.ReportMessageSent);

						ClearSentAndDraftsHandler();
						this.Screens[this.ScreenId].isSavedOrSent = true;
						SetHistoryHandler(
							{
								ScreenId: this.ListScreenId,
								FolderId: null
							}
						);
						var screen = this.Screens[this.ListScreenId];
						if (screen) {
							GetMessageListHandler(REDRAW_NOTHING, null, screen.SentId, screen.SentFullName, screen._sortField, 
							    screen._sortOrder, screen._page, screen._lookForStr, screen._searchMode, true);
							GetMessageListHandler(REDRAW_NOTHING, null, screen.DraftsId, screen.DraftsFullName, 
							    screen._sortField, screen._sortOrder, screen._page, screen._lookForStr, screen._searchMode, true);
						}
						break;
					case 'save_message':
						var newMsgScreen = this.Screens[SCREEN_NEW_MESSAGE];
						if (newMsgScreen) {
							newMsgScreen.SetMessageId(Data.Id, Data.Uid);
							this.ShowReport(Lang.ReportMessageSaved);
						}
						ClearDraftsAndSetMessageId(Data.Id, Data.Uid);
						this.Screens[this.ScreenId].isSavedOrSent = true;
						if (this.Accounts.CurrMailProtocol != POP3_PROTOCOL) {
							SetHistoryHandler(
								{
									ScreenId: this.ListScreenId,
									FolderId: null
								}
							);
						}
						var screen = this.Screens[this.ListScreenId];
						if (screen) {
							GetMessageListHandler(REDRAW_NOTHING, null, screen.DraftsId, screen.DraftsFullName, 
							    screen._sortField, screen._sortOrder, screen._page, screen._lookForStr, screen._searchMode, true);
						}
						break;
					case 'group':
					case 'sync_contacts':
						var screen = this.Screens[SCREEN_CONTACTS];
						if (screen) screen.PlaceData(Data);
						break;
				}
			break;
			case TYPE_FOLDER_LIST:
				var settingsScreen = this.Screens[SCREEN_USER_SETTINGS];
				var isChangedFolders = false;
				if (settingsScreen) {
					settingsScreen.PlaceData(Data);
					isChangedFolders = settingsScreen.isChangedFolders;
				}
				if (Data.IdAcct == this._idAcct) {
					this.FolderList = Data;
					if (Data.Sync == GET_FOLDERS_SYNC_MESSAGES) {
						for (var i = this.FoldersToUpdate.length-1; i >= 0; i--) {
							this.DataSource.Cache.ClearMessageList(this.FoldersToUpdate[i].id, this.FoldersToUpdate[i].fullName);
						}
						this.FoldersToUpdate = [];
						GetHandler(TYPE_SETTINGS_LIST, {}, [], '');
					} else if (this.Accounts.CurrMailProtocol == IMAP4_PROTOCOL){
					    var listScreen = this.Screens[this.ListScreenId];
						if (listScreen) {
							for (var key in Data.Folders) {
								var fld = Data.Folders[key];
								var params = listScreen._foldersParam[fld.Id + fld.FullName];
								if (params != undefined && fld.SyncType != params._syncType && 
								 (fld.SyncType == SYNC_TYPE_DIRECT_MODE || params._syncType == SYNC_TYPE_DIRECT_MODE)) {
									this.DataSource.Cache.ClearMessageList(fld.Id, fld.FullName);
									if (listScreen._folderId == fld.Id && listScreen._folderFullName == fld.FullName) {
										listScreen.needToRefreshMessages = true;
									}
								}
							}
						}
					}
					if ((this.StartScreen != SCREEN_NEW_MESSAGE) || this.NotFirstTime) {
						var screen = this.Screens[SCREEN_MESSAGE_LIST_TOP_PANE];
						if (screen) {
							screen.needToRefreshFolders = isChangedFolders;
							screen.PlaceData(Data);
						}
						screen = this.Screens[SCREEN_MESSAGE_LIST_CENTRAL_PANE];
						if (screen) {
							screen.needToRefreshFolders = isChangedFolders;
							screen.PlaceData(Data);
						}
					}
					this.NotFirstTime = true;
				}
				break;
			case TYPE_MESSAGE_LIST:
				this._messageList = Data;
				if (Data.LookFor.length == 0) {
					this.DataSource.Cache.SetMessagesCount(Data.FolderId, Data.FolderFullName, Data.MessagesCount, Data.NewMsgsCount);
				}
				var screen = this.Screens[SCREEN_MESSAGE_LIST_TOP_PANE];
				if (screen) {
					screen.PlaceData(Data);
					if (this.ListScreenId == SCREEN_MESSAGE_LIST_TOP_PANE) {
						this._defOrder = screen.GetDefOrder();
						this.Accounts.SetAccountDefOrder(this._idAcct, this._defOrder);
					}
				}
				screen = this.Screens[SCREEN_MESSAGE_LIST_CENTRAL_PANE];
				if (screen) {
					screen.PlaceData(Data);
					if (this.ListScreenId == SCREEN_MESSAGE_LIST_CENTRAL_PANE) {
						this._defOrder = screen.GetDefOrder();
						this.Accounts.SetAccountDefOrder(this._idAcct, this._defOrder);
					}
				}
				screen = this.Screens[SCREEN_VIEW_MESSAGE];
				if (screen) {
					screen.PlaceData(Data);
				}
				break;
			case TYPE_MESSAGE:
				if (Data.Downloaded || this.Accounts.CurrMailProtocol == WMSERVER_PROTOCOL) {
					this._requestMessageReplyPart(Data, true);
				}
				this._message = Data;
				var id = Data.Id;
				var uid = Data.Uid;
				var fId = Data.FolderId; var fName = Data.FolderFullName;
				if (this.DataSource.Cache.ClearMessage(id , uid, fId, fName, Data.Charset)) {
					this.DataSource.Cache.ClearMessageList(fId, fName);
					var screen = this.Screens[this.ListScreenId];
					if (screen) {
						if (screen._selection) {
							var newId = this._message.GetIdForList(STR_SEPARATOR, screen.Id);
							screen._selection.ChangeLineId(this._message, newId);
						}
					}
				}
				this.DataSource.Set([[{Id: id, Uid: uid}], fId, fName], 'Read', true, false);
				if (3 < this.forEditParams.length && id == this.forEditParams[0] && uid == this.forEditParams[1] &&
				 fId == this.forEditParams[2] && fName == this.forEditParams[3]) {
					if (SCREEN_NEW_MESSAGE == this.ScreenId) {
						this.Screens[SCREEN_NEW_MESSAGE].UpdateMessage(this._message);
					} else {
						Screens[SCREEN_NEW_MESSAGE].ShowHandler = 'screen.UpdateMessage(this._message);';
						SelectScreenHandler(SCREEN_NEW_MESSAGE);
					}
					this.forEditParams = [];
				} else if (this._replyAction != -1) {
                     var screen = this.Screens[SCREEN_NEW_MESSAGE];
                     if (screen) {
                          screen.UpdateMessageForReply(this._message, this._replyAction, this._replyText);
                     }
                     this._replyAction = -1;
                     this._replyText = '';
                }
				if (this.ScreenId == SCREEN_VIEW_MESSAGE || null != this.HistoryArgs &&
					this.HistoryArgs.ScreenId == SCREEN_VIEW_MESSAGE) {
						this.Screens[SCREEN_VIEW_MESSAGE].Fill(this._message);
				}
				if (this.ScreenId == SCREEN_MESSAGE_LIST_TOP_PANE) {
					this.Screens[SCREEN_MESSAGE_LIST_TOP_PANE].PlaceData(Data);
				}
				if (this.ScreenId == SCREEN_MESSAGE_LIST_CENTRAL_PANE) {
					this.Screens[SCREEN_MESSAGE_LIST_CENTRAL_PANE].PlaceData(Data);
				}
				break;
			case TYPE_MESSAGES_OPERATION:
				var screen = this.Screens[this.ListScreenId];
				if ((Data.OperationInt == TOOLBAR_DELETE || Data.OperationInt == TOOLBAR_NO_MOVE_DELETE) && screen) {
					if (Data.OperationInt == TOOLBAR_DELETE && Data.IsMoveError) {
						if (confirm(Lang.NoMoveDelete)) {
							screen.GetXmlMessagesOperation(TOOLBAR_NO_MOVE_DELETE, [], []);
						}
						else
						{
							screen.ClearDeleteTools();
						}
						return;
					}
					else {
						this.DataSource.Cache.ClearMessageList(Data.FolderId, Data.FolderFullName);
						if (screen._folderId != Data.FolderId || screen._folderFullName != Data.FolderFullName) {
							GetMessageListHandler(REDRAW_NOTHING, null, Data.FolderId, Data.FolderFullName, screen._sortField,
								screen._sortOrder, screen._page, screen._lookForStr, screen._searchMode, true);
						}
						if (screen.DeleteLikePop3() && screen.TrashId != -1 || screen.TrashFullName != '') {
							this.DataSource.Cache.ClearMessageList(screen.TrashId, screen.TrashFullName);
							if (screen._folderId != screen.TrashId || screen._folderFullName != screen.TrashFullName) {
								GetMessageListHandler(REDRAW_NOTHING, null, screen.TrashId, screen.TrashFullName, screen._sortField,
									screen._sortOrder, screen._page, screen._lookForStr, screen._searchMode, true);
							}
						}
						if (WMSERVER_PROTOCOL == WebMail.Accounts.GetAccountProtocol(WebMail.Accounts.CurrId)
								&& (screen.TrashId == -1 || screen.TrashFullName == '')) {
							screen.needToRefreshFolders = true;
							GetHandler(TYPE_FOLDER_LIST, { IdAcct: WebMail.Accounts.CurrId, Sync: GET_FOLDERS_SYNC_FOLDERS }, [], '');
						}
						if (Data.OperationInt == TOOLBAR_NO_MOVE_DELETE) {
							screen.needToRefreshFolders = true;
							GetHandler(TYPE_FOLDER_LIST, { IdAcct: WebMail.Accounts.CurrId, Sync: GET_FOLDERS_SYNC_FOLDERS }, [], '');
						}
					}
				} else if (Data.OperationInt == TOOLBAR_PURGE || Data.OperationInt == TOOLBAR_EMPTY_SPAM ||
				 Data.OperationInt == TOOLBAR_MOVE_TO_FOLDER ||
				 Data.OperationInt == TOOLBAR_IS_SPAM || Data.OperationInt == TOOLBAR_NOT_SPAM) {
					this.DataSource.Cache.ClearMessageList(Data.FolderId, Data.FolderFullName);
				    if (screen._folderId != Data.FolderId || screen._folderFullName != Data.FolderFullName) {
						GetMessageListHandler(REDRAW_NOTHING, null, Data.FolderId, Data.FolderFullName, screen._sortField, 
						    screen._sortOrder, screen._page, screen._lookForStr, screen._searchMode, true);
					}
					if (Data.ToFolderId != -1 || Data.ToFolderFullName != '') {
						this.DataSource.Cache.ClearMessageList(Data.ToFolderId, Data.ToFolderFullName);
				        if (screen._folderId != Data.ToFolderId || screen._folderFullName != Data.ToFolderFullName) {
						    GetMessageListHandler(REDRAW_NOTHING, null, Data.ToFolderId, Data.ToFolderFullName, screen._sortField, 
						        screen._sortOrder, screen._page, screen._lookForStr, screen._searchMode, true);
					    }
					}
					if (Data.OperationInt == TOOLBAR_IS_SPAM) {
					    if (screen.SpamId != -1 && screen.SpamFullName != '') {
						    this.DataSource.Cache.ClearMessageList(screen.SpamId, screen.SpamFullName);
				            if (screen._folderId != screen.SpamId || screen._folderFullName != screen.SpamFullName) {
						        GetMessageListHandler(REDRAW_NOTHING, null, screen.SpamId, screen.SpamFullName, screen._sortField, 
						            screen._sortOrder, screen._page, screen._lookForStr, screen._searchMode, true);
					        }
					    }
						if (screen && (screen.SpamId == -1 || screen.SpamFullName == '')) {
							screen.needToRefreshFolders = true;
							GetHandler(TYPE_FOLDER_LIST, { IdAcct: WebMail.Accounts.CurrId, Sync: GET_FOLDERS_SYNC_FOLDERS }, [], '');
						}
					}
					if (Data.OperationInt == TOOLBAR_NOT_SPAM) {
						this.DataSource.Cache.ClearMessageList(screen.InboxId, screen.InboxFullName);
			            if (screen._folderId != screen.InboxId || screen._folderFullName != screen.InboxFullName) {
					        GetMessageListHandler(REDRAW_NOTHING, null, screen.InboxId, screen.InboxFullName, screen._sortField, 
					            screen._sortOrder, screen._page, screen._lookForStr, screen._searchMode, true);
				        }
					}
					if (Data.OperationInt == TOOLBAR_PURGE || Data.OperationInt == TOOLBAR_EMPTY_SPAM) {
						GetHandler(TYPE_SETTINGS_LIST, {}, [], '');
					}
				}
				else {
					if (Data.OperationField != '') {
						if (Data.isAllMess) {
							this.DataSource.Set([[], Data.FolderId, Data.FolderFullName], Data.OperationField, Data.OperationValue, Data.isAllMess);
						}
						else {
							var dict = Data.Messages;
							var keys = dict.keys();
							for (i in keys) {
								var folder = dict.getVal(keys[i]);
								this.DataSource.Set([folder.IdArray, folder.FolderId, folder.FolderFullName], Data.OperationField, Data.OperationValue, Data.isAllMess);
							}
						}
					}
				}
				if ((Data.OperationInt == TOOLBAR_DELETE || Data.OperationInt == TOOLBAR_NO_MOVE_DELETE) && this.ScreenId == SCREEN_VIEW_MESSAGE) {
					screen = this.Screens[SCREEN_VIEW_MESSAGE];
					if (screen) {
						screen.PlaceData(Data);
					}
				}
				screen = this.Screens[this.ListScreenId];
				if (screen) {
					screen.PlaceData(Data);
				}
				break;
			case TYPE_AUTORESPONDER:
			case TYPE_MOBILE_SYNC:
			case TYPE_USER_SETTINGS:
				var screen = this.Screens[SCREEN_USER_SETTINGS];
				if (screen) {
					screen.PlaceData(Data);
				}
				break;
			default:
				if (this.ScreenId != -1) {
					this.Screens[this.ScreenId].PlaceData(Data);
				}
				break;
		}
	},
	
	GetCurrentListScreen: function (screenId)
	{
		if (screenId == undefined) screenId = this.ListScreenId;
		if (this.ScreenId != screenId) return null;
		return this.Screens[this.ScreenId];
	},

	ClearFilterCache: function ()
	{
		this.Accounts.DeleteCurrFilters();
	},

	RequestFoldersMessageList: function ()
	{
		if (this._gotFoldersMessageList[this._idAcct]) return;
		this._gotFoldersMessageList[this._idAcct] = true;
		GetHandler(TYPE_FOLDERS_BASE, { }, [], '', true);
	},

	StartHiddenCheckMail: function ()
	{
		var screen = this.Screens[this.ListScreenId];
		if (screen) { screen.StartHiddenCheckMail(); }
	},
	
	StartCheckMailInterval: function ()
	{
		if (this._checkMailIntervalHandle != null) {
			clearInterval(this._checkMailIntervalHandle);
			this._checkMailIntervalHandle = null;
		}
		if (this._checkMailInterval > 0) {
			var obj = this;
			this._checkMailIntervalHandle = setInterval(function() {
				obj.StartHiddenCheckMail();
			}, this._checkMailInterval * 60000);
			
		}
	},

	GetFolderSyncType: function (id)
	{
		for (var key in this.FolderList.Folders) {
			var fld = this.FolderList.Folders[key];
			if (fld.Id == id) {
				return fld.SyncType;
			}
		}
		return false;
	},
	
	SetActiveTab: function ()
	{
		if (!this.isBuilded) return;
		var contactsClass = 'wm_accountslist_contacts';
		if (!this._allowContacts) {
			contactsClass = 'wm_hide';
		}
		var calendarClass = 'wm_accountslist_contacts';
		if (!this._allowCalendar || window.Seporated || !window.UseDb) {
			calendarClass = 'wm_hide';
		}
		this._mailTab.className = 'wm_accountslist_email';
		this._contactsTab.className = contactsClass;
		this._calendarTab.className = calendarClass;
		this._settingsTab.className = (window.Seporated) ? 'wm_hide' : 'wm_accountslist_settings';
		var screen = Screens[this.ScreenId];
		if (screen) {
			switch (screen.SectionId) {
				case SECTION_MAIL:
					this._mailTab.className = 'wm_accountslist_email wm_active_tab';
					break;
				case SECTION_CONTACTS:
					this._contactsTab.className = contactsClass + ' wm_active_tab';
					break;
				case SECTION_CALENDAR:
					this._calendarTab.className = calendarClass + ' wm_active_tab';
					break;
				case SECTION_SETTINGS:
					if (!window.Seporated && (window.UseDb || window.UseLdapSettings)) {
						this._settingsTab.className = 'wm_accountslist_settings wm_active_tab';
					}
					break;
			}
		}
	},

	ShowMail: function (idAcct)
	{
		if (idAcct) {
			SetHistoryHandler(
				{
					ScreenId: WebMail.ListScreenId,
					IdAcct: idAcct
				}
			);
			return;
		}
		var hasHistArgs = this.MailHistoryArgs != null;
		var screen = Screens[this.ScreenId];
		var isMailSection = screen && (screen.SectionId == SECTION_MAIL);
		if (hasHistArgs && !isMailSection) {
			var args = this.MailHistoryArgs;
			if (!this.Accounts.HasAccount(args.IdAcct)) {
				args = { ScreenId: WebMail.ListScreenId };
			}
			else {
				if (args.ScreenId == SCREEN_MESSAGE_LIST_TOP_PANE || args.ScreenId == SCREEN_MESSAGE_LIST_CENTRAL_PANE) {
					args.ScreenId = WebMail.ListScreenId;
				}
				if (undefined != args.IdAcct) delete args.IdAcct;
				if (undefined != args.AcctChanged) delete args.AcctChanged;
			}
			SetHistoryHandler(args);
			return;
		}
		SetHistoryHandler(
			{
				ScreenId: WebMail.ListScreenId,
				FolderId: null
			}
		);
	},
	
	ShowContacts: function ()
	{
		var screen = Screens[this.ScreenId];
		var isContactsSection = screen && (screen.SectionId == SECTION_CONTACTS);
		screen = this.Screens[SCREEN_CONTACTS];
		if (screen && screen.HistoryArgs && !isContactsSection) {
			var args = screen.HistoryArgs;
			if (undefined == args.IdAcct && undefined == args.AcctChanged)
			{
				SetHistoryHandler(
					{
						ScreenId: SCREEN_CONTACTS,
						Entity: PART_CONTACTS,
						LookFor: ''
					}
				);
			} else {
				if (undefined != args.IdAcct) delete args.IdAcct;
				if (undefined != args.AcctChanged) delete args.AcctChanged;
				SetHistoryHandler(args);
			}
		} else {
			SetHistoryHandler(
				{
					ScreenId: SCREEN_CONTACTS,
					Entity: PART_CONTACTS,
					LookFor: ''
				}
			);
		}
	},
	
	ShowCalendar: function ()
	{
		SetHistoryHandler(
			{
				ScreenId: SCREEN_CALENDAR
			}
		);
	},
	
	ShowSettings: function ()
	{
		var screen = this.Screens[SCREEN_USER_SETTINGS];
		if (screen && screen.HistoryArgs != null) {
			var args = screen.HistoryArgs;
			if (!this.Accounts.HasAccount(args.SelectIdAcct)) {
				args.SelectIdAcct = -1;
			}
			if (undefined != args.IdAcct) delete args.IdAcct;
			if (undefined != args.AcctChanged) delete args.AcctChanged;
			SetHistoryHandler(args);
		}
		else {
			SetHistoryHandler(
				{
					ScreenId: SCREEN_USER_SETTINGS,
					SelectIdAcct: -1,
					Entity: (window.UseDb || window.UseLdapSettings) ? PART_COMMON_SETTINGS : PART_MANAGE_FOLDERS,
					NewMode: false
				}
			);
		}
	},
	
	LogOut: function (errorCode)
	{
		if (errorCode) {
			if (parent) {
				parent.location = LoginUrl + '?error=' + errorCode;
			} else {
				document.location = LoginUrl + '?error=' + errorCode;
			}
		} else {
			EraseCookie('awm_autologin_data');
			EraseCookie('awm_autologin_id');
			if (parent) {
				parent.location = LoginUrl + '?mode=logout';
			} else {
				document.location = LoginUrl + '?mode=logout';
			}
		}
	},
	
	SetStartScreen: function (start)
	{
		var START_NEW_MESSAGE = 1;
		var START_USER_SETTINGS = 2;
		var START_CONTACTS = 3;
		var START_CALENDAR = 4;
		var START_VIEW_MESSAGE = 5;
		switch (start) {
			case START_NEW_MESSAGE:
				this.StartScreen = SCREEN_NEW_MESSAGE;
				Screens[SCREEN_NEW_MESSAGE].ShowHandler = (ToAddr && ToAddr.length > 0)
					? 'screen.UpdateMessageFromContacts(\'' + EncodeStringForEval(ToAddr) + '\')'
					: 'screen.SetNewMessage()';
				break;
			case START_USER_SETTINGS:
				this.StartScreen = SCREEN_USER_SETTINGS;
				break;
			case START_CONTACTS:
				this.StartScreen = SCREEN_CONTACTS;
				break;
			case START_CALENDAR:
				this.StartScreen = SCREEN_CALENDAR;
				break;
			case START_VIEW_MESSAGE:
				this.StartScreen = SCREEN_VIEW_MESSAGE;
				Screens[SCREEN_VIEW_MESSAGE].ShowHandler = 'screen.PlaceData(window.ViewMessage);PlaceViewMessageHandler();';
				break;
			default:
				this.StartScreen = this.ListScreenId;
				break;
		}
	},
	
	ShowTrial: function ()
	{
		var md = CreateChild(document.body, 'div', [['class', 'wm_tr_message']]);
		var msg = 'Your evaluation period is about to expire.';
		if (window.CSType != true) {
			msg += (window.XType == '1')
				? ' You can <a href="http://www.afterlogic.com/purchase/xmail-server-pro" target="_blank">purchase AfterLogic XMail Server Pro here</a>.'
				: ' You can <a href="http://www.afterlogic.com/purchase/webmail-pro" target="_blank">purchase AfterLogic WebMail Pro here</a>.';
		}
		md.innerHTML = msg;
		var x = CreateChild(md, 'span', [['class', 'wm_close_info_image wm_control']]);
		x.onclick = function() { md.className = 'wm_hide'; };
	},
	
	CheckHistoryObject: function (args, onlyCheck)
	{
		if (!args.IdAcct) {
			args.IdAcct = this._idAcct;
		}
		var checked = false; //parameters' set is such as previouse one
		if (null == this.HistoryObj) {
			checked = true;  //another
		}
		if (!checked) {
			switch (args.ScreenId) {
				case SCREEN_MESSAGE_LIST_TOP_PANE:
				case SCREEN_MESSAGE_LIST_CENTRAL_PANE:
				case SCREEN_VIEW_MESSAGE:
					if (args.MsgId != 'undefined' && args.MsgId != null) {
						if (args.IdAcct == this.HistoryObj.IdAcct && args.MsgFolderId == this.HistoryObj.MsgFolderId &&
						 args.MsgFolderFullName == this.HistoryObj.MsgFolderFullName && args.MsgId == this.HistoryObj.MsgId &&
						 args.MsgUid == this.HistoryObj.MsgUid && args.MsgCharset == this.HistoryObj.MsgCharset &&
						 args.ScreenId == this.HistoryObj.ScreenId) {
							checked = false;
						} else {
							checked = true;
						}
					} else {
						checked = true;
					}
				break;
				case SCREEN_USER_SETTINGS:
					if (args.SelectIdAcct == this.HistoryObj.SelectIdAcct &&
					 args.Entity == this.HistoryObj.Entity && args.NewMode == this.HistoryObj.NewMode &&
					 args.ScreenId == this.ScreenId) {
						checked = false;
					} else {
						checked = true;
					}
				break;
				default:
					checked = true;
				break;
			}
		}
		if (checked) {
			if (!onlyCheck) {
				this.HistoryObj = args;
			}
			return args;
		}
		else {
			return null;
		}
	},
	
	RestoreFromHistory: function (args)
	{
		if (args.IdAcct != this._idAcct) {
			var screen = this.Screens[SCREEN_CALENDAR];
			if (screen) {
				screen.NeedReload();
			}
			args.AcctChanged = true;
			this._idAcct = args.IdAcct;
			this.Accounts.ChangeCurrAccount(args.IdAcct);
			this.FillAccountsList();
		} else {
			args.AcctChanged = false;
		}
		this.HistoryArgs = args;
		if (Screens[args.ScreenId] && Screens[args.ScreenId].SectionId == SECTION_MAIL) {
			this.MailHistoryArgs = args;
		}
		switch (args.ScreenId) {
			case SCREEN_NEW_MESSAGE:
				if (args.FromDrafts) {
					this.forEditParams = [args.MsgId, args.MsgUid, args.MsgFolderId, args.MsgFolderFullName];
					GetMessageHandler(args.MsgId, args.MsgUid, args.MsgFolderId, args.MsgFolderFullName, args.MsgParts, args.MsgCharset);
				} else if (args.ForReply) {
					this._replyAction = args.ReplyType;
					this._replyText = args.ReplyText;
					GetMessageHandler(args.MsgId, args.MsgUid, args.MsgFolderId, args.MsgFolderFullName, args.MsgParts, args.MsgCharset);
				}
				else if (args.FromContacts) {
					if (this.ScreenId == SCREEN_NEW_MESSAGE) {
						this.Screens[SCREEN_NEW_MESSAGE].UpdateMessageFromContacts(args.ToField, args.CcField, args.BccField);
					} else {
						Screens[SCREEN_NEW_MESSAGE].ShowHandler = "screen.UpdateMessageFromContacts('" + EncodeStringForEval(args.ToField) + "', '" + EncodeStringForEval(args.CcField) + "', '" + EncodeStringForEval(args.BccField) + "')";
					}
				}
				else if (args.ConfirmEmail && args.ConfirmEmail.length > 0) {
					if (this.ScreenId == SCREEN_NEW_MESSAGE) {
						this.Screens[SCREEN_NEW_MESSAGE].UpdateMessageFromConfirmation(args.ConfirmEmail);
					} else {
						Screens[SCREEN_NEW_MESSAGE].ShowHandler = "screen.UpdateMessageFromConfirmation('" + EncodeStringForEval(args.ConfirmEmail) + "')";
					}
				}
				else {
					if (this.ScreenId == SCREEN_NEW_MESSAGE) {
						this.Screens[SCREEN_NEW_MESSAGE].SetNewMessage();
					} else {
						Screens[SCREEN_NEW_MESSAGE].ShowHandler = 'screen.SetNewMessage()';
					}
				}
			break;
			case SCREEN_VIEW_MESSAGE:
				var listScreen = this.Screens[WebMail.ListScreenId];
				if (listScreen) {
					var msg = new CMessage();
					msg.Id = args.MsgId;
					msg.Uid = args.MsgUid;
					msg.FolderId = args.MsgFolderId;
					msg.FolderFullName = args.MsgFolderFullName;
					msg.Charset = args.MsgCharset;
					msg.Size = args.MsgSize;
					listScreen.msgForView = msg;
				}
				GetMessageHandler(args.MsgId, args.MsgUid, args.MsgFolderId, args.MsgFolderFullName, args.MsgParts, args.MsgCharset);
			break;
			case SCREEN_USER_SETTINGS:
			    if (args.Entity == PART_MANAGE_FOLDERS && args.SetIdAcct == true) {
			        args.SelectIdAcct = this._idAcct;
			    }
			break;
		}
		if (this.ScreenId != args.ScreenId) {
			var isSectionMail = Screens[this.ScreenId] && Screens[this.ScreenId].SectionId == SECTION_MAIL;
			if (SCREEN_VIEW_MESSAGE != args.ScreenId || !isSectionMail) {
				SelectScreenHandler(args.ScreenId);
			}
		} else {
			var screen = this.Screens[this.ScreenId];
			if (screen) {
				screen.RestoreFromHistory(args);
				this.HistoryArgs = null;
			} else {
				SelectScreenHandler(args.ScreenId);
			}
		}
	},
	
	ContactsImported: function (count)
	{
		if (count == 0) {
			this.ShowReport(Lang.ErrorNoContacts);
		}
		if (count > 0) {
			this.ShowReport(Lang.InfoHaveImported + ' ' + count + ' ' + Lang.InfoNewContacts);
			var screen = this.Screens[SCREEN_CONTACTS];
			if (screen) {
				screen.ContactsImported(count);
			}
		}
	},
	
	CheckWmCookies: function (settings)
	{
		var setCookies = false;
		var cookie = ReadCookie('wm_hide_folders');

		if (cookie != null && cookie != '') {
			settings.HideFolders = (cookie == '1');
			setCookies = true;
		}

		cookie = ReadCookie('wm_horiz_resizer');
		if (cookie != null && cookie != '') {
			settings.HorizResizer = cookie - 0;
			setCookies = true;
		}

		cookie = ReadCookie('wm_vert_resizer');
		if (cookie != null && cookie != '') {
			settings.VertResizer = cookie - 0;
			setCookies = true;
		}

		cookie = ReadCookie('wm_msg_resizer');
		if (cookie != null && cookie != '') {
			settings.MsgResizer = cookie - 0;
			setCookies = true;
		}

		var columns = Array();
		var iCount = InboxHeaders.length;
		for (var i=0; i<iCount; i++) {
			InboxHeaders[i].PermanentWidth = InboxHeaders[i].Width;
			cookie = ReadCookie('wm_column_'+i);
			if (cookie != null && cookie != '') {
				InboxHeaders[i].Width = cookie*1;
				setCookies = true;
			} else if (settings.Columns[i]) {
				InboxHeaders[i].Width = settings.Columns[i]*1;
			}
			columns[i] = InboxHeaders[i].Width;
		}
		settings.Columns = columns;
		
		if (setCookies) {
			SetCookieSettingsHandler(settings.HideFolders, settings.HorizResizer, settings.VertResizer, settings.MsgResizer, settings.Columns);
		}
		return settings;
	},
	
	EraseWmCookies: function ()
	{
		EraseCookie('wm_hide_folders');
		EraseCookie('wm_horiz_resizer');
		EraseCookie('wm_vert_resizer');
		//EraseCookie('wm_msg_resizer');
		EraseCookie('wm_mark');
		EraseCookie('wm_reply');
		var iCount = InboxHeaders.length;
		for (var i=0; i<iCount; i++) {
			EraseCookie('wm_column_'+i);
		}
	},
	
	ParseSettings: function ()
	{
		var listScreen;
		var settings = this.Settings;
		this._useImapTrash = settings.UseImapTrash;
		this.IdleSessionTimeout = settings.IdleSessionTimeout;
		this._checkMailInterval = settings.AutoCheckMailInterval;

		this.StartCheckMailInterval();
		
		if (this.Accounts != null) {
			var arrAccounts = this.Accounts.Items;
			for (var key in arrAccounts) {
				if (arrAccounts[key].Id == this.Accounts.CurrId) {
					arrAccounts[key].Size = settings.AccountSize;
				}
			}
		}
		
		if (this._timeOffset != settings.TimeOffset) {
			this._timeOffset = settings.TimeOffset;
			if (this.isBuilded) {
    			this.DataSource.Cache.ClearAllMessages();
    		}
		}
		if (this._msgsPerPage != settings.MsgsPerPage || this._defLang != settings.DefLang) {
			listScreen = this.Screens[this.ListScreenId];
			if (listScreen && this._defLang != settings.DefLang) {
				listScreen.needToRefreshFolders = true;
			}
			this._msgsPerPage = settings.MsgsPerPage;
			this._defLang = settings.DefLang;
			if (this.isBuilded) {
			    this.DataSource.Cache.ClearMessageList(-1, '');
			}
		}
		this._allowDhtmlEditor = settings.AllowDhtmlEditor;
		this._allowChangeSettings = settings.AllowChangeSettings;
		if (this._viewMode != settings.ViewMode || this.ListScreenId == -1) {
			this._viewMode = settings.ViewMode;
			if (this._viewMode & VIEW_MODE_CENTRAL_LIST_PANE == VIEW_MODE_CENTRAL_LIST_PANE) {
				this.ListScreenId = SCREEN_MESSAGE_LIST_CENTRAL_PANE;
			}
			else {
				this.ListScreenId = SCREEN_MESSAGE_LIST_TOP_PANE;
			}
			listScreen = this.Screens[this.ListScreenId];
			if (listScreen) {
				listScreen.needToRefreshFolders = true;
			}
		}
		this.ChangeSkin(settings.DefSkin);
	},

	UpdateSettings: function (newSettings)
	{
		if (null != newSettings.MsgsPerPage) {
			this.Settings.MsgsPerPage = newSettings.MsgsPerPage;
		}
		if (null != newSettings.ContactsPerPage) {
			this.Settings.ContactsPerPage = newSettings.ContactsPerPage;
		}
		if (null != newSettings.AutoCheckMailInterval) {
			this.Settings.AutoCheckMailInterval = newSettings.AutoCheckMailInterval;
		}
		if (null != newSettings.DisableRte) {
			this.Settings.AllowDhtmlEditor = newSettings.DisableRte ? false : true;
		}
		if ((null != newSettings.TimeOffset) && (newSettings.TimeOffset != this.Settings.TimeOffset)) {
			this.Settings.TimeOffset = newSettings.TimeOffset;
		}
		if ((null != newSettings.TimeFormat) && (newSettings.TimeFormat != this.Settings.TimeFormat)) {
			this.Settings.TimeFormat = newSettings.TimeFormat;
			var screen = this.Screens[SCREEN_CALENDAR];
			if (screen) screen.NeedReload();
		}
		if (null != newSettings.ViewMode && this.Settings.ViewMode != newSettings.ViewMode) {
			this.Settings.ViewMode = newSettings.ViewMode;
			switch (this.ListScreenId) {
				case SCREEN_MESSAGE_LIST_CENTRAL_PANE:
					var listTopScreen = this.Screens[SCREEN_MESSAGE_LIST_TOP_PANE];
					if (listTopScreen) {
						listTopScreen.needToRefreshMessages = true;
					}
					break;
				case SCREEN_MESSAGE_LIST_TOP_PANE:
					var listCentralScreen = this.Screens[SCREEN_MESSAGE_LIST_CENTRAL_PANE];
					if (listCentralScreen) {
						listCentralScreen.needToRefreshMessages = true;
					}
					break;
			}
		}
		if (null != newSettings.DefSkin) {
			this.Settings.DefSkin = newSettings.DefSkin;
		}
		if (null != newSettings.DefLang) {
			if (this.Settings.DefLang != newSettings.DefLang) {
				var newRTL = IsRtlLanguage(newSettings.DefLang);
				if ((window.RTL && !newRTL) || (!window.RTL && newRTL)) {
					document.location = WebMailUrl;
				}
				else {
					this.Settings.DefLang = newSettings.DefLang;
					var obj = this;
					this.ScriptLoader.Load([LanguageUrl + '?v=' + WmVersion + '&lang=' + newSettings.DefLang], function () { obj.ChangeLang(); });
				}
			}
		}
		this.ParseSettings();
	},
	
	ChangeLang: function ()
	{
		this.SetTitle();
		this.LangChanger.Go();
		HtmlEditorField.ChangeLang();
		var screen = this.Screens[SCREEN_MESSAGE_LIST_TOP_PANE];
		if (screen) {
			screen.ChangeHeaderFieldsLang();
		}
	},

	SetTitle: function (strTitle)
	{
		var titleLangField = Screens[this.ScreenId].TitleLangField;
		document.title = (typeof(strTitle) == 'string')
			? this._title + ' - ' + Lang[titleLangField] + ' - ' + strTitle
			: this._title + ' - ' + Lang[titleLangField];
	},
	
	ChangeSkin: function (newSkin)
	{
		if (this._skinName != newSkin) {
			this._skinName = newSkin;
			this.AddNewSkinLink(newSkin);
		}
		else {
			this.RemoveOldSkinLink();
		}
	},

	AddNewSkinLink: function (newSkin)
	{
		var newLink = document.createElement('link');
		newLink.setAttribute('type', 'text/css');
		newLink.setAttribute('rel', 'stylesheet');
		newLink.href = 'skins/' + newSkin + '/styles.css';
		this._head.appendChild(newLink);

        if (window.RTL) {
		    var newRtlLink = document.createElement('link');
		    newRtlLink.setAttribute('type', 'text/css');
		    newRtlLink.setAttribute('rel', 'stylesheet');
			newRtlLink.href = 'skins/' + newSkin + '/styles-rtl.css';
			this._head.appendChild(newRtlLink);
		}

		this.RemoveOldSkinLink();

		this._newSkinLink = newLink;
		if (window.RTL) {
		    this._newRtlSkinLink = newRtlLink;
		}
	},
	
	/*
	 * don't delete old skin immediately because of screen tweak in ff
	 */
	RemoveOldSkinLink: function ()
	{
		if (this._newSkinLink != null) {
			this._head.removeChild(this._skinLink);
			this._skinLink = this._newSkinLink;
			this._newSkinLink = null;
		}
		if (window.RTL && this._newRtlSkinLink != null) {
			this._head.removeChild(this._rtlSkinLink);
			this._rtlSkinLink = this._newRtlSkinLink;
			this._newRtlSkinLink = null;
		}
	},

	Build: function()
	{
		this.PopupMenus = new CPopupMenus();
		this.BuildAccountsList();
		document.body.onclick = ClickBodyHandler;
		document.onkeydown = WebMail.KeyupBody;
		this.isBuilded = true;
	},
	
	KeyupBody: function (ev)
	{
		ev = ev ? ev : window.event;
		var clickElem = (Browser.Mozilla) ? ev.target : ev.srcElement;
		if (clickElem && (clickElem.tagName == 'INPUT' || clickElem.tagName == 'TEXTAREA')) {
			return true;
		}
		
		var key = Keys.GetCodeFromEvent(ev);
		var currentScreen = WebMail.Screens[WebMail.ScreenId];
		if (currentScreen && currentScreen.KeyupBody) {
			currentScreen.KeyupBody(key, ev);
		}
		if (key == Keys.A && ev.ctrlKey || (key == Keys.Up || key == Keys.Down) && ev.shiftKey) {
			return false;
		}
		return true;
	},
	
	ResizeBody: function (mode)
	{
		if (this.isBuilded) {
		    if (this.ScreenId != SCREEN_CONTACTS) {
			    var width = GetWidth();
			    if (Browser.IE && Browser.Version < 7) {
				    document.body.style.width = width + 'px';
				}
			} else {
			    if (Browser.IE && Browser.Version < 7) {
				    document.body.style.width = 'auto';
				}
			}
			if (this.ScreenId != -1) {
				this.Screens[this.ScreenId].ResizeBody(mode);
			}
			this.InfoContainer.Resize();
		}
	},
	
	ClickBody: function (ev)
	{
		if (this.isBuilded) {
			this.PopupMenus.checkShownItems();
			if (this.ScreenId != -1) {
				this.Screens[this.ScreenId].ClickBody(ev);
			}
			if (this._spellchecker.popupVisible()) {
				this._spellchecker.popupHide('document');
			}
		}
	},
	
	ReplyClick: function (type)
	{
	
		var msg = this._message;
		var screen = this.Screens[this.ScreenId];
		if (screen) {
			switch (this.ScreenId) {
				case SCREEN_VIEW_MESSAGE:
				case this.ListScreenId:
					msg = screen._msgObj;
					break;
			}
		}
		this.ReplyMessageClick(type, msg);
	},
	
	ReplyMessageClick: function (type, msg, text)
	{
		if (msg == null) {
			return;
		}
		if (msg.NoReply && type == TOOLBAR_REPLY || msg.NoReplyAll && type == TOOLBAR_REPLYALL) {
			return;
		}
		if ((msg.Sensivity != SENSIVITY_NOTHING || msg.NoForward) && type == TOOLBAR_FORWARD) {
			return;
		}
		if (text == undefined) text = '';
		var parts = [];
		if (type == TOOLBAR_FORWARD) {
			parts = (this._allowDhtmlEditor && msg.HasHtml)
				? [PART_MESSAGE_HEADERS, PART_MESSAGE_FORWARD_HTML, PART_MESSAGE_ATTACHMENTS]
				: [PART_MESSAGE_HEADERS, PART_MESSAGE_FORWARD_PLAIN, PART_MESSAGE_ATTACHMENTS];
		} else {
			parts = (this._allowDhtmlEditor && msg.HasHtml)
				? [PART_MESSAGE_HEADERS, PART_MESSAGE_REPLY_HTML, PART_MESSAGE_ATTACHMENTS]
				: [PART_MESSAGE_HEADERS, PART_MESSAGE_REPLY_PLAIN, PART_MESSAGE_ATTACHMENTS];
		}
		SetHistoryHandler(
			{
				ScreenId: SCREEN_NEW_MESSAGE,
				FromDrafts: false,
				ForReply: true,
				ReplyType: type,
				ReplyText: text,
				MsgId: msg.Id,
				MsgUid: msg.Uid,
				MsgFolderId: msg.FolderId,
				MsgFolderFullName: msg.FolderFullName,
				MsgCharset: msg.Charset,
				MsgSize: msg.Size,
				MsgParts: parts
			}
		);
	},
	
	ShowScreen: function(loadHandler)
	{
		var screenId = this.ScreenIdForLoad;
		var section;
		var screen = this.Screens[screenId];
		if (screen) {
			this.RemoveOldSkinLink();
			if (this.ScreenId != -1) {
				if (this.ScreenId == SCREEN_NEW_MESSAGE && this.Screens[this.ScreenId].HasChanges())
				{
					if (!confirm(Lang.ConfirmExitFromNewMessage))
					{
						return;
					}
					
				}
				this.Screens[this.ScreenId].Hide();
			}
			this.ScreenId = screenId;
			this.SectionId = Screens[screenId].SectionId;
			if (!screen.isBuilded) {
				screen.Build(this._content, this._accountsBar, this.PopupMenus, this.Settings);
			}
			
			this._copyright.className = (screen.hasCopyright) ? 'wm_copyright' : 'wm_hide';
			
			SetBodyAutoOverflow(screen.BodyAutoOverflow);
			this.Show();
			this.SetTitle();
			eval(Screens[screenId].ShowHandler);
			switch (screen.Id) {
				case SCREEN_MESSAGE_LIST_TOP_PANE:
					screen.ChangeDefOrder(this._defOrder);
					break;
				case SCREEN_MESSAGE_LIST_CENTRAL_PANE:
					screen.ChangeDefOrder(this._defOrder);
					break;
				case SCREEN_VIEW_MESSAGE:
					screen.ParseSettings(this.Settings);
					if (!(Browser.IE && Browser.Version > 6)) {
						if (null != this._message)
							screen.PlaceData(this._message);
					}
					if (null != this._messageList)
						screen.PlaceData(this._messageList);
					var listScreen = this.Screens[this.ListScreenId];
					if (listScreen) {
						screen.SetTrashParams(listScreen.TrashId, listScreen.TrashFullName, listScreen.Protocol);
					}
					break;
			}
			if (null != this.HistoryArgs && screen.Id == this.HistoryArgs.ScreenId) {
				screen.Show(this.Settings, this.HistoryArgs);
			} else {
				screen.Show(this.Settings, null);
			}
			this.SetActiveTab();
			this.HistoryArgs = null;
		}
		else {
			if (!this.isBuilded) {
				this.Hide();
				this._copyright.className = 'wm_hide';
				this.Build();
				this.DataSource.onError = ErrorHandler;
				this.DataSource.onGet = TakeDataHandler;
			}
			var sectionId = Screens[screenId].SectionId;
			section = this.Sections[sectionId];
			if (section) {
				var sectionScreens = Sections[sectionId].Screens;
				for (var i in sectionScreens) {
					if (!(screen = this.Screens[i])) {
						eval(sectionScreens[i]);
						if (Screens[i].PreRender) {
							screen.Build(this._content, this._accountsBar, this.PopupMenus, this.Settings);
						}
						this.Screens[i] = screen;
					}
				}
				loadHandler.call(this);
			} else {
				this.Sections[sectionId] = true;
				this.ScriptLoader.Load(Sections[sectionId].Scripts, loadHandler);
			}
		}
	},
	
	Show: function ()
	{
		if (!this.shown) {
			this.shown = true;
			this.HideInfo();
			this._content.className = 'wm_content';
			this.FillAccountsList();
		}
	},

	Hide: function ()
	{
		this.shown = false;
		this._content.className = 'wm_hide';
	},
	
	BuildAccountsList: function()
	{
		var a, tr, td, div, i;
		this._accountsBar = CreateChild(this._content, 'table', [['id', 'account_bar_id']]);
		this._accountsBar.className = 'wm_accountslist';
		tr = this._accountsBar.insertRow(0);
		td = tr.insertCell(0);
		this._mailTab = CreateChild(td, 'div');
		this._mailTab.className = 'wm_accountslist_email';
		this._accountNameObject = CreateChild(this._mailTab, 'a');
		this._accountNameObject.href = '#';
		this._accountNameObject.onclick = function() {return false;};
		this._accountControl = CreateChild(td, 'div');
		this._accountControl.className = 'wm_accountslist_selection wm_control';

		this._contactsTab = CreateChild(td, 'div');
		this._contactsTab.className = (this._allowContacts)	? 'wm_accountslist_contacts' : 'wm_hide';
		a = CreateChild(this._contactsTab, 'a'); a.href = '#'; a.innerHTML = Lang.Contacts;
		WebMail.LangChanger.Register('innerHTML', a, 'Contacts', '');
		a.onclick = function() {
			obj.ShowContacts();
			return false;
		};

		this._calendarTab = CreateChild(td, 'div');
		this._calendarTab.className = (!this._allowCalendar || window.Seporated || !window.UseDb)
			 ? 'wm_hide' : 'wm_accountslist_contacts';
		
		a = CreateChild(this._calendarTab, 'a'); a.href = '#'; a.innerHTML = Lang.Calendar;
		WebMail.LangChanger.Register('innerHTML', a, 'Calendar', '');
		a.onclick = function() {
			obj.ShowCalendar();
			return false;
		};

		if (window.CustomTopLinks && window.CustomTopLinks.length > 0) {
			var topLink;
			for (i = 0; i < window.CustomTopLinks.length; i++) {
				topLink = window.CustomTopLinks[i];
				div = CreateChild(td, 'div', [['class', 'wm_accountslist_contacts']]);
				a = CreateChild(div, 'a', [['href', topLink.Link], ['target', '_blank']]);
				a.innerHTML = topLink.Name;
			}
		}

		div = CreateChild(td, 'div'); 
		div.className = (window.Seporated) ? 'wm_hide' : 'wm_accountslist_logout';
		a = CreateChild(div, 'a'); a.href = '#'; a.innerHTML = Lang.Logout;
		WebMail.LangChanger.Register('innerHTML', a, 'Logout', '');
		var obj = this;
		a.onclick = function () {
			obj.LogOut();
			return false;
		};

		this._settingsTab = CreateChild(td, 'div');
		this._settingsTab.className = (window.Seporated) ? 'wm_hide' : 'wm_accountslist_settings';
		a = CreateChild(this._settingsTab, 'a'); a.href = '#'; a.innerHTML = Lang.Settings;
		this.LangChanger.Register('innerHTML', a, 'Settings', '');
		a.onclick = function() {
			obj.ShowSettings();
			return false;
		};

		this._accountsList = CreateChild(document.body, 'div');
		this._accountsList.className = 'wm_hide';
		var accountsListPopupMenu = new CPopupMenu(this._accountsList, this._accountControl, 'wm_account_menu',
			this._mailTab, this._mailTab, '', '', '', '');
		this.PopupMenus.addItem(accountsListPopupMenu);
	},
	
	FillAccountsList: function()
	{
		if (!this.isBuilded || null == this.Accounts || !this.Accounts.Items) return;
		CleanNode(this._accountsList);
		var arrAccounts = this.Accounts.Items;
		for(var key in arrAccounts) {
			var Id = arrAccounts[key].Id;
			if (Id != this.Accounts.CurrId) {
				var div1 = CreateChild(this._accountsList, 'div');
				var div = CreateChild(div1, 'div');
				div.className = 'wm_account_item';
				div.onmouseover = function() {this.className = 'wm_account_item_over';};
				div.onmouseout = function() {this.className = 'wm_account_item';};
				div.onclick = CreateAccountActionFunc(Id);
				div.innerHTML = arrAccounts[key].Email;
			} else {
				var screen = this.Screens[this.ListScreenId];
				if (screen) {
					screen.ChangeDefOrder(arrAccounts[key].DefOrder);
					if (this.ListScreenId == SCREEN_MESSAGE_LIST_TOP_PANE) {
						screen.SetAccountSize(arrAccounts[key].Size);
					}
				}
				this._defOrder = arrAccounts[key].DefOrder;
				this._email = arrAccounts[key].Email;
				
				this._accountNameObject.innerHTML = arrAccounts[key].Email;
				var obj = this;
				this._accountNameObject.onclick = function () {
					obj.ShowMail();
					return false;
				};
			}
		}
		if (this._accountsList.firstChild) {
			this._accountsList.style.width = 'auto';
			this._accountControl.className = 'wm_accountslist_selection';
			this._accountControl.onmouseover = function() { this.className = 'wm_accountslist_selection_over wm_control'; };
			this._accountControl.onmousedown = function() { this.className = 'wm_accountslist_selection_press wm_control'; };
			this._accountControl.onmouseup = function() { this.className = 'wm_accountslist_selection_over wm_control'; };
			this._accountControl.onmouseout = function() { this.className = 'wm_accountslist_selection wm_control'; };
		} else {
			this._accountControl.className = 'wm_accountslist_selection_none';
			this._accountControl.onmouseover = function() { };
			this._accountControl.onmousedown = function() { };
			this._accountControl.onmouseup = function() { };
			this._accountControl.onmouseout = function() { };
		}
		this.PopupMenus.hideAllItems();
	},

	HideInfo: function()
	{
		if (this.shown) {
			this.InfoContainer.HideInfo();
		}
	},
	
	ShowError: function(errorDesc)
	{
		this.InfoContainer.ShowError(errorDesc);
		var screen;
		if (this.ScreenId == SCREEN_NEW_MESSAGE) {
			screen = this.Screens[SCREEN_NEW_MESSAGE];
			if (screen) {
				screen.SetErrorHappen();
			}
		}
		if (this.ScreenId == this.ListScreenId) {
			screen = this.Screens[this.ListScreenId];
			if (screen) {
				screen.EnableTools();
			}
		}
	},
	
	HideError: function()
	{
		this.InfoContainer.HideError();
	},

	ShowInfo: function(info)
	{
		if (this.shown) {
			this.InfoContainer.ShowInfo(info);
		}
	},

	ShowReport: function(report, priorDelay)
	{
		if (this.shown) {
			this.InfoContainer.ShowReport(report, priorDelay);
		}
	},

	HideReport: function()
	{
		this.InfoContainer.HideReport();
	},

    SaveChangesAndLogout: function ()
    {
        if (this.ScreenId == SCREEN_NEW_MESSAGE) {
            var screen = this.Screens[this.ScreenId];
            screen.SaveChanges(SAVE_MODE);
        }
        this.LogOut();
    },

	StartIdleTimer: function()
	{
		this.StopIdleTimer();
		this.timer = setTimeout(
			function()
			{
				WebMail.StopIdleTimer();
				WebMail.SaveChangesAndLogout();
			},
			this.IdleSessionTimeout * 1000
        );
	},

	StopIdleTimer: function()
	{
		if (this.timer != null) {
			clearTimeout(this.timer);
			this.timer = null;
		}
	}
};

function CSettingsList()
{
	this.Type = TYPE_SETTINGS_LIST;
	this.ShowTextLabels = false;
	this.AllowChangeSettings = false;
	this.AllowDhtmlEditor = false;
	this.AllowAddAccount = false;
	this.MsgsPerPage = 20;
	this.ContactsPerPage = 20;
	this.AutoCheckMailInterval = 0;
	this.DefSkin = 'AfterLogic';
	this.DefLang = '';
	this.EnableMailboxSizeLimit = true;
	this.MailBoxLimit = 0;
	this.MailBoxSize = 0;
	this.AccountSize = 0;
	this.HideFolders = false;
	this.HorizResizer = 150;
	this.VertResizer = 115;
	this.ViewMode = VIEW_MODE_CENTRAL_LIST_PANE;
	this.FoldersPerPage = 20;
	this.TimeOffset = 0;
	this.AllowDirectMode = true;
	this.DirectModeIsDefault = false;
	this.AllowContacts = true;
	this.AllowCalendar = true;
	this.Columns = Array();
	this.UseImapTrash = false;
	this.AllowChangeAccountsDef = false;
	this.IdleSessionTimeout = 0;
	
	this.AllowInsertImage = true;
	this.AllowBodySize = false;
	this.MaxBodySize = 20;
	this.MaxSubjectSize = 255;
	
	this.MsgResizer = 620;

    this.MobileSyncEnable = false;
    this.ViewMessageInNewTab = false;
}

CSettingsList.prototype = {
	GetStringDataKeys: function()
	{
		return '';
	},

	GetFromXML: function(RootElement)
	{
		var attr;

		attr = RootElement.getAttribute('show_text_labels');
		if (attr) this.ShowTextLabels = (attr == 1);

		attr = RootElement.getAttribute('allow_change_settings');
		if (attr) this.AllowChangeSettings = (attr == 1);

		attr = RootElement.getAttribute('allow_dhtml_editor');
		if (attr) this.AllowDhtmlEditor = (attr == 1);

		attr = RootElement.getAttribute('allow_add_account');
		if (attr) this.AllowAddAccount = (attr == 1);

		attr = RootElement.getAttribute('allow_account_def');
		if (attr) this.AllowChangeAccountsDef = (attr == 1);

		attr = RootElement.getAttribute('msgs_per_page');
		if (attr) this.MsgsPerPage = attr - 0;

		attr = RootElement.getAttribute('contacts_per_page');
		if (attr) this.ContactsPerPage = attr - 0;

		attr = RootElement.getAttribute('auto_checkmail_interval');
		if (attr) this.AutoCheckMailInterval = attr - 0;

		attr = RootElement.getAttribute('enable_mailbox_size_limit');
		if (attr) this.EnableMailboxSizeLimit = (attr == 1);

		attr = RootElement.getAttribute('mailbox_limit');
		if (attr) this.MailBoxLimit = attr - 0;

		attr = RootElement.getAttribute('mailbox_size');
		if (attr) this.MailBoxSize = attr - 0;

		attr = RootElement.getAttribute('account_size');
		if (attr) this.AccountSize = attr - 0;

		attr = RootElement.getAttribute('hide_folders');
		if (attr) this.HideFolders = attr - 0;

		attr = RootElement.getAttribute('horiz_resizer');
		if (attr) this.HorizResizer = attr - 0;

		attr = RootElement.getAttribute('vert_resizer');
		if (attr) this.VertResizer = attr - 0;

		attr = RootElement.getAttribute('view_mode');
		if (attr) this.ViewMode = attr - 0;

		attr = RootElement.getAttribute('def_timezone');
		if (attr) this.TimeOffset = attr - 0;

		attr = RootElement.getAttribute('folders_per_page');
		if (attr) this.FoldersPerPage = attr - 0;

		attr = RootElement.getAttribute('allow_direct_mode');
		if (attr) this.AllowDirectMode = (attr == 1);

		attr = RootElement.getAttribute('direct_mode_is_default');
		if (attr) this.DirectModeIsDefault = (attr == 1);

		attr = RootElement.getAttribute('allow_contacts');
		if (attr) this.AllowContacts = (attr == 1);

		attr = RootElement.getAttribute('allow_calendar');
		if (attr) this.AllowCalendar = (attr == 1);

		attr = RootElement.getAttribute('imap4_delete_like_pop3');
		if (attr) this.UseImapTrash = (attr == 1);

		attr = RootElement.getAttribute('idle_session_timeout');
		if (attr) this.IdleSessionTimeout = attr - 0;

		attr = RootElement.getAttribute('allow_insert_image');
		if (attr) this.AllowInsertImage = (attr == 1);

		attr = RootElement.getAttribute('allow_body_size');
		if (attr) this.AllowBodySize = (attr == 1);

		attr = RootElement.getAttribute('max_body_size');
		if (attr) this.MaxBodySize = attr - 0;
		
		attr = RootElement.getAttribute('max_subject_size');
		if (attr) this.MaxSubjectSize = attr - 0;
        
		attr = RootElement.getAttribute('mobile_sync_enable_system');
		if (attr) this.MobileSyncEnable = (attr == 1);

		var settingsParts = RootElement.childNodes;
		var settCount = settingsParts.length;
		for (var i = settCount - 1; i >= 0; i--) {
			var part = settingsParts[i].childNodes;
			if (part.length > 0)
				switch (settingsParts[i].tagName) {
					case 'def_skin':
						this.DefSkin = part[0].nodeValue;
						break;
					case 'def_lang':
						this.DefLang = part[0].nodeValue;
						break;
					case 'columns':
						var jCount = part.length;
						for (var j = jCount-1; j >= 0; j--) {
							if (part[j].tagName == 'column') {
								var id, value;
								attr = part[j].getAttribute('id');
								if (attr) id = attr - 0;
								attr = part[j].getAttribute('value');
								if (attr) value = attr - 0;
								if (id && value) {
									this.Columns[id] = value;
								}
							}
						}
                        break;
				}//switch
		}//for
	}//GetFromXML
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}