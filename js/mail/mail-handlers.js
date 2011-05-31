/*
 * Handlers:
 *  MailAllHandler(toAddr, ccAddr, bccAddr)
 *  MailToHandler(toAddr)
 *  MailAllHandlerWithDropDown(Addr)
 *  SendConfirmationHandler(toAddr, subject)
 *  ViewMessageInNewTab(msg)
 *  ViewMessageInFullScreenMode(msg)
 *  EditMessageFromDrafts(msg)
 *  DblClickHandler()
 *  ClickMessageHandler(id)
 *  GetPageMessagesHandler(page)
 *  SortMessagesHandler()
 *  ResizeMessagesTab(number)
 *  GetMessageListHandler(redrawIndex, redrawElement, folderId, folderFullName, sortField, sortOrder, page, lookFor, searchFields, background)
 *  GetMessageHandler(messageId, messageUid, folderId, folderFullName, messageParts, charset)
 *  MoveToFolderHandler(id)
 *  RequestMessagesOperationHandler(type, idArray, sizeArray, toFolderId, toFolderFullName)
 *  SetCounterValueHandler()
 *  LoadAttachmentHandler(attachment)
 *  SetStateTextHandler(text)
 *  SetCheckingFolderHandler(folder, count)
 *  SetRetrievingMessageHandler(number)
 *  SetDeletingMessageHandler(number)
 *  SetUpdatedFolders(foldersArray, showInfo)
 *  EndCheckMailHandler(error)
 *  CheckEndCheckMailHandler()
 *  GetAutoFillingContactsHandler()
 *  SelectSuggestionHandler()
 *  PlaceViewMessageHandler()
 *  ShowPicturesHandler(safety)
 *  SetMessageSafetyHandler(msg)
 *  SetSenderSafetyHandler(fromAddr)
 *  MarkMsgAsRepliedHandler(msg)
 *  ClearSentAndDraftsHandler()
 *  ClearDraftsAndSetMessageId(id, uid)
 *  NewMessageClickHandler(ev, toAddr)
 */

function MailAllHandler(toAddr, ccAddr, bccAddr)
{
	var historyObj = {
			ScreenId: SCREEN_NEW_MESSAGE,
			FromDrafts: false,
			ForReply: false,
			FromContacts: true,
			ToField: toAddr,
			CcField: ccAddr,
			BccField: bccAddr
		};
	SetHistoryHandler(historyObj);
}

function MailToHandler(toAddr)
{
	MailAllHandler(toAddr, '', '');
}

var addrDropDown;
function MailAllHandlerWithDropDown(Addr)
{
	if (UseCustomContacts) {
		if (Addr && Addr.length > 0) {
			if (addrDropDown) {
				addrDropDown.InitAddr(Addr);
				addrDropDown.Hide();
			} else {
				addrDropDown = new CToAddressDropDown(Addr);
			}
			addrDropDown.Show();
		}
	} else {
		MailToHandler(Addr);
	}
}

function SendConfirmationHandler(toAddr, subject)
{
	var xml = '<confirmation>' + GetCData(toAddr) + '</confirmation><subject>' + GetCData(subject) + '</subject>';
	RequestHandler('send', 'confirmation', xml);
}

function ViewMessageInNewWindow(msg)
{
    msg.SetMode([PART_MESSAGE_HEADERS, PART_MESSAGE_HTML, PART_MESSAGE_MODIFIED_PLAIN_TEXT, PART_MESSAGE_ATTACHMENTS]);
	var params = '?open_mode=view';
	params += '&msg_id=' + msg.Id;
	params += '&msg_uid=' + msg.Uid;
	params += '&folder_id=' + msg.FolderId;
	params += '&folder_full_name=' + msg.FolderFullName;
	params += '&charset=' + msg.Charset;
	params += '&mode=' + msg.Parts;
	params += '&size=' + msg.Size;
	WindowOpener.Open(MiniWebMailUrl + params, msg.Id+msg.Uid+msg.FolderId+msg.FolderFullName);
}

function ViewMessageInNewTab(msg)
{
    msg.SetMode([PART_MESSAGE_HEADERS, PART_MESSAGE_HTML, PART_MESSAGE_REPLY_HTML, PART_MESSAGE_ATTACHMENTS]);
	var form = CreateChild(document.body, 'form', [['action', WebMailUrl], ['method', 'post'], ['class', 'wm_hide'],
		['enctype', 'multipart/form-data'], ['target', '_blank'], ['id', 'view_message_form']]);
	CreateChild(form, 'input', [['type', 'hidden'], ['value', '5'], ['name', 'start']]);
	CreateChild(form, 'input', [['type', 'hidden'], ['value', msg.Id], ['name', 'msg_id']]);
	CreateChild(form, 'input', [['type', 'hidden'], ['value', msg.Uid], ['name', 'msg_uid']]);
	CreateChild(form, 'input', [['type', 'hidden'], ['value', msg.FolderId], ['name', 'folder_id']]);
	CreateChild(form, 'input', [['type', 'hidden'], ['value', msg.FolderFullName], ['name', 'folder_full_name']]);
	CreateChild(form, 'input', [['type', 'hidden'], ['value', msg.Charset], ['name', 'charset']]);
	CreateChild(form, 'input', [['type', 'hidden'], ['value', msg.Parts], ['name', 'mode']]);
	CreateChild(form, 'input', [['type', 'hidden'], ['value', msg.Size], ['name', 'size']]);
	form.submit();
}

function ViewMessageInFullScreenMode(msg)
{
	SetHistoryHandler(
		{
			ScreenId: SCREEN_VIEW_MESSAGE,
			MsgId: msg.Id,
			MsgUid: msg.Uid,
			MsgFolderId: msg.FolderId,
			MsgFolderFullName: msg.FolderFullName,
			MsgCharset: msg.Charset,
			MsgParts: [PART_MESSAGE_HEADERS, PART_MESSAGE_HTML, PART_MESSAGE_ATTACHMENTS]
		}
	);
}

function EditMessageFromDrafts(msg)
{
	SetHistoryHandler(
		{
			ScreenId: SCREEN_NEW_MESSAGE,
			FromDrafts: true,
			MsgId: msg.Id,
			MsgUid: msg.Uid,
			MsgFolderId: msg.FolderId,
			MsgFolderFullName: msg.FolderFullName,
			MsgCharset: msg.Charset,
			MsgParts: [PART_MESSAGE_HEADERS, PART_MESSAGE_HTML, PART_MESSAGE_UNMODIFIED_PLAIN_TEXT, PART_MESSAGE_ATTACHMENTS]
		}
	);
}

function DblClickHandler(shift)
{
	var screen = WebMail.GetCurrentListScreen();
	if (screen == null) return;

	var msg = new CMessage();
	msg.GetFromIdForList(this.id);
	msg.SetSize(this.size);

	if (screen.IsDrafts()) {
		EditMessageFromDrafts(msg);
	}
	else if (shift) {
		ViewMessageInNewWindow(msg);
	}
	else {
        if (WebMail.Settings.ViewMessageInNewTab) {
            ViewMessageInNewTab(msg);
        }
        else {
            ViewMessageInFullScreenMode(msg);
        }
	}	
}

function ClickMessageHandler(id)
{
	var screen = WebMail.GetCurrentListScreen();
	if (screen == null) return;

	screen._needPlain = false;
	var msg = new CMessage();
	msg.GetFromIdForList(id);
	var line = screen._selection.GetLineById(id);
	msg.Size = line.MsgSize;
	if (null == screen._msgObj || msg.Id != screen._msgObj.Id || msg.Uid != screen._msgObj.Uid ||
	  msg.FolderId != screen._msgObj.FolderId || msg.FolderFullName != screen._msgObj.FolderFullName ||
	  msg.Charset != screen._msgObj.Charset) {
		screen.CleanMessageBody(false);
		var parts = [PART_MESSAGE_HEADERS, PART_MESSAGE_HTML, PART_MESSAGE_ATTACHMENTS];
		if (screen.IsDrafts()) {
			parts = [PART_MESSAGE_HEADERS, PART_MESSAGE_HTML, PART_MESSAGE_UNMODIFIED_PLAIN_TEXT, PART_MESSAGE_ATTACHMENTS];
		}
		var args = {
			ScreenId: screen.Id,
			FolderId: screen._folderId,
			FolderFullName: screen._folderFullName,
			Page: screen._page,
			SortField: screen._sortField,
			SortOrder: screen._sortOrder,
			LookForStr: screen._lookForStr,
			SearchMode: screen._searchMode,
			RedrawType: REDRAW_NOTHING,
			RedrawObj: null,
			MsgId: msg.Id,
			MsgUid: msg.Uid,
			MsgFolderId: msg.FolderId,
			MsgFolderFullName: msg.FolderFullName,
			MsgCharset: msg.Charset,
			MsgSize: msg.Size,
			MsgParts: parts
		};

		var check = WebMail.CheckHistoryObject(args, true);
		if (check != null) {
			SetHistoryHandler(args);
		}
		else if (null == screen._msgObj) {
			GetMessageHandler(args.MsgId, args.MsgUid, args.MsgFolderId, args.MsgFolderFullName, args.MsgParts, args.MsgCharset);
		}
	}
}

function GetPageMessagesHandler(page)
{
	var screen = WebMail.GetCurrentListScreen();
	if (screen == null) return;
	screen.GetPage(page);
}

function SortMessagesHandler()
{
	var screen = WebMail.GetCurrentListScreen();
	if (screen == null) return;
	
	SetHistoryHandler(
		{
			ScreenId: screen.Id,
			FolderId: screen._folderId,
			FolderFullName: screen._folderFullName,
			Page: screen._page,
			SortField: this.SortField,
			SortOrder: this.SortOrder,
			LookForStr: screen._lookForStr,
			SearchMode: screen._searchMode,
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
}

function ResizeMessagesTab(number)
{
	var screen = WebMail.GetCurrentListScreen(SCREEN_MESSAGE_LIST_TOP_PANE);
	if (screen == null) return;

	screen._inboxTable.ResizeColumnsWidth(number);
}

function GetMessageListHandler(redrawIndex, redrawElement, folderId, folderFullName, sortField, sortOrder, page, lookFor, searchFields, background) {
	HistoryStorage.Log = '';
	var xml = '<folder id="' + folderId + '"><full_name>' + GetCData(folderFullName) + '</full_name></folder>';
	xml += '<look_for fields="' + searchFields + '">' + GetCData(lookFor) + '</look_for>';
	var screen = WebMail.Screens[WebMail.ListScreenId];
	var emptyFolder = false;
	if (screen && !background) {
		screen.RedrawControls(redrawIndex, redrawElement, sortField, sortOrder, page);
		emptyFolder = screen.IsEmptyFolder(folderId, folderFullName);
	}
	if (emptyFolder) {
		var msgs = new CMessages();
		msgs.FolderId = folderId;
		msgs.FolderFullName = folderFullName;
		msgs.SortField = sortField;
		msgs.SortOrder = sortOrder;
		msgs.Page = page;
		msgs.LookFor = lookFor;
		msgs._searchFields = searchFields;
		WebMail.PlaceData(msgs);
	}
	else {
		GetHandler(TYPE_MESSAGE_LIST, {IdAcct: WebMail._idAcct, Page: page, SortField: sortField, SortOrder: sortOrder, 
			FolderId: folderId, FolderFullName: folderFullName, LookFor: lookFor, SearchFields: searchFields}, [], xml, background );
	}
}

function GetMessageHandler(messageId, messageUid, folderId, folderFullName, messageParts, charset) {
	var screen = WebMail.Screens[WebMail.ListScreenId];
	var readed = 0;
	var msgId = '';
	var msgSize = '';
	if (screen && null != screen._selection) {
		var msg = new CMessage();
		msg.Id = messageId;
		msg.Uid = messageUid;
		msg.FolderId = folderId;
		msg.FolderFullName = folderFullName;
		msg.Charset = charset;
		msgId = msg.GetIdForList(screen.Id);
		readed = screen._selection.SetParams([msgId], 'Read', true, false);

		var line = screen._selection.GetLineById(msgId);
		if (line) {
			msg.Size = line.MsgSize;
		}
		msgSize = msg.Size;

		if (readed != 0) {
			var paramIndex = screen._folderId + screen._folderFullName;
			var params = screen._foldersParam[paramIndex];
			if (params) {
				params.Read(readed);
				WebMail.DataSource.Cache.SetMessagesCount(screen._folderId, screen._folderFullName, params.MsgsCount, params._newMsgsCount);
			}
		}
		screen._selection.CheckLine(msgId);
		if (screen._inboxTable != null && screen._inboxTable.LastClickLineId != msgId) {
			screen._inboxTable.LastClickLineId = msgId;
		}
	}
	charset = charset ? charset : AUTOSELECT_CHARSET;
	var xml = '<param name="uid">' + GetCData(HtmlDecode(messageUid)) + '</param>';
	xml += '<folder id="' + folderId + '"><full_name>' + GetCData(folderFullName) + '</full_name></folder>';
	WebMail.DataSource.Get(TYPE_MESSAGE, {Id: messageId, Charset: charset, Uid: messageUid, FolderId: folderId, FolderFullName: folderFullName, Size: msgSize}, messageParts, xml );
	if (readed != 0 && msgId != '' && msgSize != '' && WebMail.DataSource.LastFromCache) {
		WebMail.DataSource.NeedInfo = false;
		RequestMessagesOperationHandler(TOOLBAR_MARK_READ, [msgId], [msgSize]);
	}
}

function MoveToFolderHandler(id)
{
	var screenId = WebMail.ScreenId;
	var screen = WebMail.Screens[screenId];
	if (screen && screenId == WebMail.ListScreenId) {
		var folderParams = id.split(STR_SEPARATOR);
		if (2 == folderParams.length) {
			RequestMessagesOperationHandler(TOOLBAR_MOVE_TO_FOLDER, [], [], folderParams[0], folderParams[1]);
		}
	}
}

function RequestMessagesOperationHandler(type, idArray, sizeArray, toFolderId, toFolderFullName) {
	var screen = WebMail.Screens[WebMail.ListScreenId];
	if (screen && type != -1) {
		screen.GetXmlMessagesOperation(type, idArray, sizeArray, toFolderId, toFolderFullName);
	}
}

function LoadAttachmentHandler(attachment) {
	var screen = WebMail.Screens[SCREEN_NEW_MESSAGE];
	if (screen) {
		screen.LoadAttachment(attachment);
	}
}

function SetCounterValueHandler ()
{
	var screen = WebMail.Screens[SCREEN_NEW_MESSAGE];
	if (screen) {
		screen.SetCounterValue();
	}
}

/* check mail handlers */
function SetStateTextHandler(text) {
	WebMail.CheckMail.SetText(text);
}

function SetCheckingFolderHandler(folder, count) {
	WebMail.CheckMail.SetFolder(folder, count);
}

function SetRetrievingMessageHandler(number) {
	WebMail.CheckMail.SetMsgNumber(number);
}

function SetDeletingMessageHandler(number) {
	WebMail.CheckMail.DeleteMsg(number);
}

function SetUpdatedFolders(foldersArray, showInfo)
{
	showInfo = (typeof showInfo != 'undefined') ? showInfo : true;
    WebMail.FoldersToUpdate = foldersArray;
	if (showInfo && foldersArray.length == 0) {
		WebMail.ShowReport(Lang.InfoNoNewMessages);
	}
}

function EndCheckMailHandler(error) {
	var screenId = WebMail.ListScreenId;
	var screen = WebMail.Screens[screenId];
	if (screen) {
		screen.EndCheckMail();
		if (WebMail.FoldersToUpdate.length > 0) {
		    GetHandler(TYPE_FOLDER_LIST, {IdAcct: WebMail.Accounts.CurrId, Sync: GET_FOLDERS_SYNC_MESSAGES}, [], '');
		}
	}
	if (error.length > 0 && !WebMail.CheckMail.hidden) {
		if (error == 'session_error') {
			document.location = LoginUrl + '?error=1';
		}
		else {
			this.ErrorDesc = error;
			ErrorHandler.call(this);
		}
	}
}

function CheckEndCheckMailHandler() {
	var screenId = WebMail.ListScreenId;
	var screen = WebMail.Screens[screenId];
	if (screen && WebMail.CheckMail.started) {
	    WebMail.FoldersToUpdate = [{id: -1, fullName: ''}];
		screen.EndCheckMail();
		GetHandler(TYPE_FOLDER_LIST, {IdAcct: WebMail.Accounts.CurrId, Sync: GET_FOLDERS_SYNC_MESSAGES}, [], '');
        if (!WebMail.CheckMail.hidden) {
            this.ErrorDesc = Lang.ErrorCheckMail;
            ErrorHandler.call(this);
        }
	}
}
/*-- check mail handlers */

/* auto filling handlers */
function GetAutoFillingContactsHandler()
{
	var contactsGroups = new CContacts();
	contactsGroups.LookFor = this.Keyword;
	contactsGroups.SearchType = 1;
	GetHandler(TYPE_CONTACTS, 
	{
		Page: 1,
		SortField: SORT_FIELD_USE_FREQ,
		SortOrder: SORT_ORDER_ASC,
		IdGroup: -1,
		LookFor: this.Keyword
	}, [], contactsGroups.GetInXml());
}

function SelectSuggestionHandler()
{
	if (this.ContactGroup.IsGroup) {
		var screen = WebMail.Screens[SCREEN_NEW_MESSAGE];
		if (screen) {
			screen.AddSenderGroup(this.ContactGroup.Id);
		}
	}
}
/*-- auto filling handlers */

function PlaceViewMessageHandler()
{
    var getRequest = WebMail.DataSource.DataTypes[TYPE_MESSAGE].GetRequest;
    var stringDataKey = getRequest + STR_SEPARATOR + window.ViewMessage.GetStringDataKeys(STR_SEPARATOR);
    WebMail.DataSource.Cache.AddData(TYPE_MESSAGE, stringDataKey, window.ViewMessage);
}

function ShowPicturesHandler(safety)
{
	if (WebMail.ScreenId != SCREEN_MESSAGE_LIST_CENTRAL_PANE
		&& WebMail.ScreenId != SCREEN_MESSAGE_LIST_TOP_PANE
		&& WebMail.ScreenId != SCREEN_VIEW_MESSAGE) return;
	var screen = WebMail.Screens[WebMail.ScreenId];
	if (screen) {
		screen.ShowPictures(safety);
	}
}

function SetMessageSafetyHandler(msg)
{
	if (msg == undefined) {
		if (WebMail.ScreenId != SCREEN_MESSAGE_LIST_CENTRAL_PANE
			&& WebMail.ScreenId != SCREEN_MESSAGE_LIST_TOP_PANE
			&& WebMail.ScreenId != SCREEN_VIEW_MESSAGE) return;
		var screen = WebMail.Screens[WebMail.ScreenId];
		if (screen) {
			msg = screen._msgObj;
		}
		else return;
	}
	WebMail.DataSource.Cache.SetMessageSafety(msg.Id, msg.Uid, msg.FolderId, msg.FolderFullName, SAFETY_MESSAGE);
}

function SetSenderSafetyHandler(fromAddr)
{
	var xml = '<param name="safety" value="1"/>';
	xml += '<param name="sender">' + GetCData(HtmlDecode(fromAddr)) + '</param>';
	RequestHandler('set', 'sender', xml);
	WebMail.DataSource.Cache.SetSenderSafety(fromAddr, SAFETY_FULL);
}

function MarkMsgAsRepliedHandler(msg)
{
	if (!msg.ReplyMsg) return;

	var operationField = (msg.ReplyMsg.Action == TOOLBAR_FORWARD) ? 'Forwarded' : 'Replied';
	var folderSyncType = WebMail.GetFolderSyncType(msg.ReplyMsg.FolderId);
	if (folderSyncType == SYNC_TYPE_DIRECT_MODE && msg.ReplyMsg.Action == TOOLBAR_FORWARD) {
	}
	else {
		var idArray = [{Id: msg.ReplyMsg.Id, Uid: msg.ReplyMsg.Uid}];
		var msgData = [idArray, msg.ReplyMsg.FolderId, msg.ReplyMsg.FolderFullName];
		WebMail.DataSource.Set(msgData, operationField, true, false);
	}
}

function ClearSentAndDraftsHandler()
{
	var screen = WebMail.Screens[WebMail.ListScreenId];
	if (screen) {
		WebMail.DataSource.Cache.ClearMessageList(screen.SentId, screen.SentFullName);
		WebMail.DataSource.Cache.ClearMessageList(screen.DraftsId, screen.DraftsFullName);
		if (typeof(screen.ResetReplyPaneFlags) == 'function') screen.ResetReplyPaneFlags(SEND_MODE);
	}
}

function ClearDraftsAndSetMessageId(id, uid)
{
	var screen = WebMail.Screens[WebMail.ListScreenId];
	if (screen) {
		WebMail.DataSource.Cache.ClearMessageList(screen.DraftsId, screen.DraftsFullName);
		if (typeof(screen.SetMessageId) == 'function') screen.SetMessageId(id, uid);
		if (WebMail.Accounts.CurrMailProtocol == POP3_PROTOCOL) {
			WebMail.DataSource.Cache.ClearMessage(id, uid, screen.DraftsId, screen.DraftsFullName, '');
		}
	}
}

function NewMessageClickHandler(ev, toAddr)
{
	ev = ev ? ev : window.event;
	if (ev.shiftKey) {
		var params = '?open_mode=new';
		if (toAddr != undefined) {
			params += '&to=' + toAddr;
		}
		WindowOpener.Open(MiniWebMailUrl + params, 'new_message_window');
	}
	else {
		if (toAddr != undefined) {
			MailAllHandler(toAddr, '', '');
		}
		else {
			SetHistoryHandler({ScreenId: SCREEN_NEW_MESSAGE});
		}
	}
}

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}