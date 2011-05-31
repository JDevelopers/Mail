/*
 * Handlers:
 *  SetHistoryHandler(args)
 *  SetCookieSettingsHandler(hideFolders, horizResizer, vertResizer, msgResizer, columns)
 *  GetHandler(type, params, parts, xml, background)
 *  SelectScreenHandlerr(screenId)
 *  ShowScreenHandler()
 *  LoadHandler()
 *  ErrorHandler()
 *  ShowLoadingInfoHandler()
 *  TakeDataHandler()
 *  RequestHandler(action, request, xml)
 *  ResizeBodyHandler()
 *  ClickBodyHandler(ev)
 *  EventBodyHandler()
 *  DisplayCalendarHandler()
 *  CreateSessionSaver()
 */

function SetHistoryHandler(args)
{
	args = WebMail.CheckHistoryObject(args);
	if (null != args) {
		if ((args.ScreenId == WebMail.ListScreenId && WebMail.ScreenId == WebMail.ListScreenId) ||
			(args.ScreenId == SCREEN_CONTACTS && WebMail.ScreenId == SCREEN_CONTACTS))
		{
			WebMail.RestoreFromHistory(args);
		}
        else {
			HistoryStorage.AddStep({FunctionName: 'WebMail.RestoreFromHistory', Args: args});
		}
	}
}

function SetCookieSettingsHandler(hideFolders, horizResizer, vertResizer, msgResizer, columns) {
	var xml = '';
	var iCount = columns.length;
	for (var i=0; i<iCount; i++) {
		xml += '<column id="' + i + '" value="' + columns[i] + '"/>';
	}
	xml = '<columns>' + xml + '</columns>';
	var hf = hideFolders ? '1' : '0';
	WebMail.DataSource.Request({action: 'update', request: 'cookie_settings', hide_folders: hf,
		horiz_resizer: horizResizer, vert_resizer: vertResizer, msg_resizer: msgResizer}, xml);
}

function GetHandler(type, params, parts, xml, background) {
	if (type == TYPE_FOLDER_LIST) {
		var stringDataKey = WebMail.DataSource.GetStringDataKey(TYPE_FOLDER_LIST, {IdAcct: params.IdAcct});
		if (params.Sync == GET_FOLDERS_SYNC_MESSAGES || params.Sync == GET_FOLDERS_SYNC_FOLDERS) {
			WebMail.DataSource.Cache.RemoveData(TYPE_FOLDER_LIST, stringDataKey);
		}
	}
	var currDefOrder = WebMail._defOrder;
	WebMail.DataSource.Get(type, params, parts, xml, background);
	if (!background && type == TYPE_MESSAGE_LIST && WebMail.DataSource.LastFromCache && currDefOrder != WebMail._defOrder) {
		WebMail.DataSource.NeedInfo = false;
		RequestHandler('update', 'def_order', '<param name="def_order" value="' + WebMail._defOrder + '"/>');
	}
}

function SelectScreenHandler(screenId) {
	WebMail.ScreenIdForLoad = screenId;
	ShowScreenHandler();
}

function ShowScreenHandler() {
	WebMail.ShowScreen(ShowScreenHandler);
}

function LoadHandler() {
	WebMail.DataSource.ParseXML(this.responseXML, this.responseText);
}

function ErrorHandler() {
	WebMail.ShowError(this.ErrorDesc);
}

function ShowLoadingInfoHandler() {
    var infoMessage = Lang.Loading;
    if (this.request == 'message') {
        switch (this.action) {
            case 'save':
                infoMessage = Lang.Saving;
                break;
            case 'send':
                infoMessage = Lang.Sending;
                break;
        }
    }
	WebMail.ShowInfo(infoMessage);
}

function TakeDataHandler() {
	if (this.Data) {
		WebMail.PlaceData(this.Data);
	}
}

function RequestHandler(action, request, xml) {
	WebMail.DataSource.Request({action: action, request: request}, xml);
}

function ResizeBodyHandler() {
	if (WebMail) {
		WebMail.ResizeBody(RESIZE_MODE_ALL);
	}
}

function ClickBodyHandler(ev) {
	if (WebMail) {
		WebMail.ClickBody(ev);
        EventBodyHandler();
	}
}

function EventBodyHandler() {
	if (WebMail && (typeof(WebMail.IdleSessionTimeout) != 'undefined' && WebMail.IdleSessionTimeout > 0)) {
		WebMail.StartIdleTimer();
	}
}

function DisplayCalendarHandler()
{
	var screen = WebMail.Screens[WebMail.ScreenId];
	if (screen && screen.Id == SCREEN_CALENDAR) {
		screen.Display();
	}
}

function CreateSessionSaver()
{
	CreateChild(document.body, 'iframe', [['id', 'session_saver'], ['name', 'session_saver'], ['src', SessionSaverUrl], ['class', 'wm_hide']]);
}

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}