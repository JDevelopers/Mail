/*
 * Handlers:
 *  SortContactsHandler()
 *  ResizeContactsTab(number)
 *  ImportContactsHandler(code, count)
 *  FillSelectedContactsHandler()
 *  ViewAllContactMailsHandler(contact)
 */

function SortContactsHandler()
{
	var screen = WebMail.Screens[WebMail.ScreenId];
	if (screen && screen.Id == SCREEN_CONTACTS) {
	    SetHistoryHandler(
		    {
			    ScreenId: SCREEN_CONTACTS,
			    Entity: PART_CONTACTS,
			    Page: screen._page,
				SortField: this.SortField,
				SortOrder: this.SortOrder,
			    SearchIn: screen._searchGroup,
			    LookFor: screen._lookFor
		    }
	    );
	}
}

function ResizeContactsTab(number)
{
	var screen = WebMail.Screens[WebMail.ScreenId];
	if (screen && screen.Id == SCREEN_CONTACTS) {
		screen._contactsTable.ResizeColumnsWidth(number);
	}
}

function ImportContactsHandler(code, count) {
	switch (code) {
		case 0:
			this.ErrorDesc = Lang.ErrorImportContacts;
			ErrorHandler();
			break;
		case 2:
			count = 0;
		case 1:
			WebMail.ContactsImported(count);
			break;
		case 3:
			this.ErrorDesc = Lang.ErrorInvalidCSV;
			ErrorHandler();
			break;
	}
}

function FillSelectedContactsHandler()
{
	var screen = WebMail.Screens[WebMail.ScreenId];
	if (screen && screen.Id == SCREEN_CONTACTS) {
		screen.FillSelectedContacts(this.ContactsArray, this.CurrId, this.CurrIsGroup);
	}
}

function ViewAllContactMailsHandler(contact)
{
	var listScreen = WebMail.Screens[WebMail.ListScreenId];
	if (typeof(listScreen) == 'undefined') {
		return;
	}
	var email = '';
	switch (contact.PrimaryEmail) {
		default:
			email = contact.hEmail;
			break;
		case PRIMARY_BUSS_EMAIL:
			email = contact.bEmail;
			break;
		case PRIMARY_OTHER_EMAIL:
			email = contact.OtherEmail;
			break;
	}
	var historyObj = listScreen.GetCurrFolderHistoryObject();
	historyObj.FolderId = -1;
	historyObj.FolderFullName = '';
	historyObj.Page = 1;
	historyObj.LookForStr = email;
	historyObj.SearchMode = 0;
	SetHistoryHandler(historyObj);
}

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}