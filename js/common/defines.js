
var DemoWarning = '';

var LEFT = (window.RTL) ? 'right' : 'left';
var RIGHT = (window.RTL) ? 'left' : 'right';
var HideSpamButton = false;

var UsePrefetch = true;
var preFetchData = null;
var preFetchFolder = null;
var preFetchMsgLimit = 5;
var preFetchStartFlag = false;
var preFetchCache = {};

var STR_SEPARATOR = '#@%';

// defines for sections
var SECTION_MAIL = 0;
var SECTION_SETTINGS = 1;
var SECTION_CONTACTS = 2;
var SECTION_CALENDAR = 3;

// defines for screens
var SCREEN_MESSAGE_LIST_TOP_PANE = 0;
var SCREEN_MESSAGE_LIST_CENTRAL_PANE = 1;
var SCREEN_VIEW_MESSAGE = 2;
var SCREEN_NEW_MESSAGE = 3;

var SCREEN_USER_SETTINGS = 4;
	var PART_COMMON_SETTINGS = 0;
	var PART_ACCOUNT_PROPERTIES = 1;
	var PART_FILTERS = 2;
	var PART_SIGNATURE = 3;
	var PART_AUTORESPONDER = 4;
	var PART_MANAGE_FOLDERS = 5;
	var PART_CALENDAR_SETTINGS = 6;
	var PART_MOBILE_SYNC = 7;
	
var SCREEN_CONTACTS = 5;
	var PART_CONTACTS = 0;
	var PART_NEW_CONTACT = 1;
	var PART_VIEW_CONTACT = 2;
	var PART_EDIT_CONTACT = 3;
	var PART_NEW_GROUP = 4;
	var PART_VIEW_GROUP = 5;
	var PART_IMPORT_CONTACT = 6;
	
var SCREEN_CALENDAR = 6;

var Sections = [];
Sections[SECTION_MAIL] = {Scripts: [], Screens: []};
Sections[SECTION_MAIL].Screens[SCREEN_MESSAGE_LIST_TOP_PANE] = 'screen = new CMessageListTopPaneScreen(SkinName);';
Sections[SECTION_MAIL].Screens[SCREEN_MESSAGE_LIST_CENTRAL_PANE] = 'screen = new CMessageListCentralPaneScreen(SkinName);';
Sections[SECTION_MAIL].Screens[SCREEN_VIEW_MESSAGE] = 'screen = new CViewMessageScreen(SkinName);';
Sections[SECTION_MAIL].Screens[SCREEN_NEW_MESSAGE] = 'screen = new CNewMessageScreen(SkinName);';

Sections[SECTION_SETTINGS] = {Scripts: [], Screens: []};
Sections[SECTION_SETTINGS].Screens[SCREEN_USER_SETTINGS] = 'screen = new CUserSettingsScreen(SkinName);';

Sections[SECTION_CONTACTS] = {Scripts: [], Screens: []};
Sections[SECTION_CONTACTS].Screens[SCREEN_CONTACTS] = 'screen = new CContactsScreen(SkinName);';
Sections[SECTION_CALENDAR] = {Scripts: [], Screens: []};
Sections[SECTION_CALENDAR].Screens[SCREEN_CALENDAR] = 'screen = new CCalendarScreen(SkinName);';

var Screens = [];
Screens[SCREEN_MESSAGE_LIST_TOP_PANE] = {SectionId: SECTION_MAIL, PreRender: true, ShowHandler: '', TitleLangField: 'TitleMessagesList'};
Screens[SCREEN_MESSAGE_LIST_CENTRAL_PANE] = {SectionId: SECTION_MAIL, PreRender: true, ShowHandler: '', TitleLangField: 'TitleMessagesList'};
Screens[SCREEN_VIEW_MESSAGE] = {SectionId: SECTION_MAIL, PreRender: true, ShowHandler: '', TitleLangField: 'TitleViewMessage'};
Screens[SCREEN_NEW_MESSAGE] = {SectionId: SECTION_MAIL, PreRender: true, ShowHandler: '', TitleLangField: 'TitleNewMessage'};
Screens[SCREEN_USER_SETTINGS] = {SectionId: SECTION_SETTINGS, PreRender: true, ShowHandler: '', TitleLangField: 'TitleSettings'};
Screens[SCREEN_CONTACTS] = {SectionId: SECTION_CONTACTS, PreRender: true, ShowHandler: '', TitleLangField: 'TitleContacts'};
Screens[SCREEN_CALENDAR] = {SectionId: SECTION_CALENDAR, PreRender: true, ShowHandler: '', TitleLangField: 'Calendar'};

// defines data types
var TYPE_ACCOUNT_BASE = 0; // includes FOLDER_LIST, MESSAGE_LIST
var TYPE_ACCOUNT_PROPERTIES = 1;
var TYPE_ACCOUNT_LIST = 2;
var TYPE_AUTORESPONDER = 3;
var TYPE_BASE = 4; // includes SETTINGS_LIST, ACCOUNT_LIST, FOLDER_LIST, MESSAGE_LIST
var TYPE_CONTACT = 5;
var TYPE_CONTACTS = 6;
var TYPE_FILTER_PROPERTIES = 8;
var TYPE_FILTERS = 9;
var TYPE_FOLDERS_BASE = 10; // includes MESSAGE_LISTs for 1 page in several folders
var TYPE_FOLDER_LIST = 11;
var TYPE_GROUP = 12;
var TYPE_GROUPS = 13;
var TYPE_MESSAGE = 14;
var TYPE_MESSAGES_BODIES = 15; // includes MESSAGE for messages with size<=75K in last MESSAGE_LIST
var TYPE_MESSAGE_LIST = 16;
var TYPE_MESSAGES_OPERATION = 17;
var TYPE_MOBILE_SYNC = 18;
var TYPE_SETTINGS_LIST = 19;
var TYPE_SIGNATURE = 20;
var TYPE_SPELLCHECK = 21;
var TYPE_UPDATE = 22;
var TYPE_USER_SETTINGS = 23;

//defines for folder types
var FOLDER_TYPE_DEFAULT = 0;
var FOLDER_TYPE_INBOX = 1;
var FOLDER_TYPE_SENT = 2;
var FOLDER_TYPE_DRAFTS = 3;
var FOLDER_TYPE_TRASH = 4;
var FOLDER_TYPE_SPAM = 5;
var FOLDER_TYPE_QUARANTINE = 6;
var FOLDER_TYPE_SYSTEM = 9;
var FOLDER_TYPE_DEFAULT_SYNC = 20;
var FOLDER_TYPE_INBOX_SYNC = 21;
var FOLDER_TYPE_SENT_SYNC = 22;
var FOLDER_TYPE_DRAFTS_SYNC = 23;
var FOLDER_TYPE_TRASH_SYNC = 24;
var FOLDER_TYPE_SPAM_SYNC = 25;
var FOLDER_TYPE_QUARANTINE_SYNC = 26;
var FOLDER_TYPE_SYSTEM_SYNC = 29;

var FolderDescriptions = [];
FolderDescriptions[FOLDER_TYPE_DEFAULT] = {x: 0, y: 2};
FolderDescriptions[FOLDER_TYPE_DEFAULT_SYNC] = {x: 1, y: 2};
FolderDescriptions[FOLDER_TYPE_DRAFTS] = {x: 2, y: 2, langField: 'FolderDrafts'};
FolderDescriptions[FOLDER_TYPE_DRAFTS_SYNC] = {x: 3, y: 2, langField: 'FolderDrafts'};
FolderDescriptions[FOLDER_TYPE_INBOX] = {x: 4, y: 2, langField: 'FolderInbox'};
FolderDescriptions[FOLDER_TYPE_INBOX_SYNC] = {x: 5, y: 2, langField: 'FolderInbox'};
FolderDescriptions[FOLDER_TYPE_SENT] = {x: 6, y: 2, langField: 'FolderSentItems'};
FolderDescriptions[FOLDER_TYPE_SENT_SYNC] = {x: 7, y: 2, langField: 'FolderSentItems'};
FolderDescriptions[FOLDER_TYPE_TRASH] = {x: 8, y: 2, langField: 'FolderTrash'};
FolderDescriptions[FOLDER_TYPE_TRASH_SYNC] = {x: 9, y: 2, langField: 'FolderTrash'};
FolderDescriptions[FOLDER_TYPE_SPAM] = {x: 10, y: 2, langField: 'FolderSpam'};
FolderDescriptions[FOLDER_TYPE_SPAM_SYNC] = {x: 11, y: 2, langField: 'FolderSpam'};
FolderDescriptions[FOLDER_TYPE_QUARANTINE] = {x: 0, y: 2, langField: 'FolderQuarantine'};
FolderDescriptions[FOLDER_TYPE_QUARANTINE_SYNC] = {x: 1, y: 2, langField: 'FolderQuarantine'};
FolderDescriptions[FOLDER_TYPE_SYSTEM] = {x: 0, y: 2};
FolderDescriptions[FOLDER_TYPE_SYSTEM_SYNC] = {x: 1, y: 2};

//defines for sync type
var SYNC_TYPE_NO = 0;
var SYNC_TYPE_NEW_HEADERS = 1;
var SYNC_TYPE_ALL_HEADERS = 2;
var SYNC_TYPE_NEW_MSGS = 3;
var SYNC_TYPE_ALL_MSGS = 4;
var SYNC_TYPE_DIRECT_MODE = 5;

var SORT_FIELD_NOTHING = -1;
var SORT_FIELD_DATE = 0;
var SORT_FIELD_FROM = 2;
var SORT_FIELD_TO = 4;
var SORT_FIELD_SIZE = 6;
var SORT_FIELD_SUBJECT = 8;
var SORT_FIELD_ATTACH = 10;
var SORT_FIELD_FLAG = 12;
var SORT_ORDER_DESC = 0;
var SORT_ORDER_ASC = 1;

//defines for inbox headers
var IH_CHECK = 0;
var IH_ATTACHMENTS = 1;
var IH_FLAGGED = 2;
var IH_FROM = 3;
var IH_TO = 4;
var IH_DATE = 5;
var IH_SIZE = 6;
var IH_SUBJECT = 7;
var IH_PRIORITY = 8;
var IH_SENSIVITY = 9;

/*
SortIconPlace values:
	0 - left of content
	1 - instead of content
	2 - right of content
Align values: 'left', 'center', 'right'
*/
var InboxHeaders = [];
InboxHeaders[IH_CHECK] =
{
	DisplayField: 'Check',
	LangField: '',
	Picture: '',
	SortField: SORT_FIELD_NOTHING,
	SortIconPlace: 1,
	Align: 'center', 
	Width: 24,
	MinWidth: 24,
	IsResize: false,
	PaddingLeftRight: 2,
	PaddingTopBottom: 2
};
InboxHeaders[IH_ATTACHMENTS] =
{
	DisplayField: 'HasAttachments',
	LangField: '',
	Picture: 'wm_inbox_lines_attachment',
	SortField: SORT_FIELD_ATTACH,
	SortIconPlace: 1,
	Align: 'center', 
	Width: 20,
	MinWidth: 20,
	IsResize: false,
	PaddingLeftRight: 2,
	PaddingTopBottom: 2
};
InboxHeaders[IH_PRIORITY] =
{
	DisplayField: 'Importance',
	LangField: '',
	Picture: 'wm_inbox_lines_priority_header',
	SortField: SORT_FIELD_NOTHING,
	SortIconPlace: 1,
	Align: 'center', 
	Width: 24,
	MinWidth: 24,
	IsResize: false,
	PaddingLeftRight: 2,
	PaddingTopBottom: 2
};
InboxHeaders[IH_SENSIVITY] =
{
	DisplayField: 'Sensivity',
	LangField: '',
	Picture: 'wm_inbox_lines_sensivity_header',
	SortField: SORT_FIELD_NOTHING,
	SortIconPlace: 1,
	Align: 'center', 
	Width: 24,
	MinWidth: 24,
	IsResize: false,
	PaddingLeftRight: 2,
	PaddingTopBottom: 2
};
InboxHeaders[IH_FLAGGED] =
{
	DisplayField: 'Flagged',
	LangField: '',
	Picture: 'wm_inbox_lines_flag',
	SortField: SORT_FIELD_FLAG,
	SortIconPlace: 1,
	Align: 'center', 
	Width: 20,
	MinWidth: 20,
	IsResize: false,
	PaddingLeftRight: 2,
	PaddingTopBottom: 2
};
InboxHeaders[IH_FROM] =
{
	DisplayField: 'FromAddr',
	LangField: 'From',
	Picture: '',
	SortField: SORT_FIELD_FROM,
	SortIconPlace: 2,
	Align: window.LEFT, 
	Width: 150,
	MinWidth: 100,
	IsResize: true,
	PaddingLeftRight: 6,
	PaddingTopBottom: 2
};
InboxHeaders[IH_TO] =
{
	DisplayField: 'ToAddr',
	LangField: 'To',
	Picture: '',
	SortField: SORT_FIELD_TO,
	SortIconPlace: 2,
	Align: window.LEFT, 
	Width: 150,
	MinWidth: 100,
	IsResize: true,
	PaddingLeftRight: 6,
	PaddingTopBottom: 2
};
InboxHeaders[IH_DATE] =
{
	DisplayField: 'Date',
	LangField: 'Date',
	Picture: '',
	SortField: SORT_FIELD_DATE,
	SortIconPlace: 2,
	Align: 'center', 
	Width: 80,
	MinWidth: 80,
	IsResize: true,
	PaddingLeftRight: 2,
	PaddingTopBottom: 2
};
InboxHeaders[IH_SIZE] =
{
	DisplayField: 'Size',
	LangField: 'Size',
	Picture: '',
	SortField: SORT_FIELD_SIZE,
	SortIconPlace: 2,
	Align: 'center', 
	Width: 50,
	MinWidth: 40,
	IsResize: true,
	PaddingLeftRight: 2,
	PaddingTopBottom: 2
};
InboxHeaders[IH_SUBJECT] =
{
	DisplayField: 'Subject',
	LangField: 'Subject',
	Picture: '',
	SortField: SORT_FIELD_SUBJECT,
	SortIconPlace: 2,
	Align: window.LEFT, 
	Width: 150,
	MinWidth: 100,
	IsResize: true,
	PaddingLeftRight: 2,
	PaddingTopBottom: 2
};

//defines for parts of message type
var PART_MESSAGE_HEADERS = 0;
var PART_MESSAGE_HTML = 1;
var PART_MESSAGE_MODIFIED_PLAIN_TEXT = 2;
var PART_MESSAGE_REPLY_HTML = 3;
var PART_MESSAGE_REPLY_PLAIN = 4;
var PART_MESSAGE_FORWARD_HTML = 5;
var PART_MESSAGE_FORWARD_PLAIN = 6;
var PART_MESSAGE_FULL_HEADERS = 7;
var PART_MESSAGE_ATTACHMENTS = 8;
var PART_MESSAGE_UNMODIFIED_PLAIN_TEXT = 9;

// defines for toolbar view mode
var TOOLBAR_VIEW_STANDARD = 0;
var TOOLBAR_VIEW_WITH_CURVE = 1;
var TOOLBAR_VIEW_NEW_MESSAGE = 2;

// defines for toolbar items
var TOOLBAR_NEW_MESSAGE = 1;
var TOOLBAR_CHECK_MAIL = 2;
var TOOLBAR_RELOAD_FOLDERS = 3;
var TOOLBAR_REPLY = 4;
var TOOLBAR_REPLYALL = 5;
var TOOLBAR_FORWARD = 6;
var TOOLBAR_MARK_READ = 7;
var TOOLBAR_MOVE_TO_FOLDER = 8;
var TOOLBAR_DELETE = 9;
var TOOLBAR_UNDELETE = 10;
var TOOLBAR_PURGE = 11;
var TOOLBAR_EMPTY_TRASH = 12;
var TOOLBAR_IS_SPAM = 13;
var TOOLBAR_NOT_SPAM = 14;
var TOOLBAR_SEARCH = 15;
var TOOLBAR_BIG_SEARCH = 16;
var TOOLBAR_SEARCH_ARROW_DOWN = 17;
var TOOLBAR_SEARCH_ARROW_UP = 18;
var TOOLBAR_ARROW = 19;

//second line in mail.png
var TOOLBAR_BACK_TO_LIST = 20;
var TOOLBAR_SEND_MESSAGE = 21;
var TOOLBAR_SAVE_MESSAGE = 22;
var TOOLBAR_HIGH_IMPORTANCE = 23;
var TOOLBAR_LOW_IMPORTANCE = 24;
var TOOLBAR_NORMAL_IMPORTANCE = 25;
var TOOLBAR_PRINT_MESSAGE = 26;
var TOOLBAR_NEXT_ACTIVE = 27;
var TOOLBAR_NEXT_INACTIVE = 28;
var TOOLBAR_PREV_ACTIVE = 29;
var TOOLBAR_PREV_INACTIVE = 30;
var TOOLBAR_NEW_CONTACT = 31;
var TOOLBAR_NEW_GROUP = 32;
var TOOLBAR_ADD_CONTACTS_TO = 33;
var TOOLBAR_IMPORT_CONTACTS = 34;
var TOOLBAR_IMPORTANCE = 35;
var TOOLBAR_CANCEL = 36;

//third line in mail.png
var TOOLBAR_MARK_UNREAD = 37;
var TOOLBAR_FLAG = 38;
var TOOLBAR_UNFLAG = 39;
var TOOLBAR_MARK_ALL_READ = 40;
var TOOLBAR_MARK_ALL_UNREAD = 41;

var TOOLBAR_EMPTY_SPAM = 42;

var TOOLBAR_SENSIVITY = 43;
var TOOLBAR_SENSIVITY_NOTHING = 44;
var TOOLBAR_SENSIVITY_CONFIDENTIAL = 45;
var TOOLBAR_SENSIVITY_PRIVATE = 46;
var TOOLBAR_SENSIVITY_PERSONAL = 47;

var TOOLBAR_NO_MOVE_DELETE = 48;
//var TOOLBAR_COPY_TO_FOLDER = 49;
var TOOLBAR_LIGHT_SEARCH_ARROW_DOWN = 51;
var TOOLBAR_LIGHT_SEARCH_ARROW_UP = 52;

var TOOLBAR_TEST = 99;

var OperationTypes = [];
OperationTypes[TOOLBAR_DELETE] = 'delete';
OperationTypes[TOOLBAR_UNDELETE] = 'undelete';
OperationTypes[TOOLBAR_PURGE] = 'purge';
OperationTypes[TOOLBAR_MARK_READ] = 'mark_read';
OperationTypes[TOOLBAR_MARK_UNREAD] = 'mark_unread';
OperationTypes[TOOLBAR_FLAG] = 'flag';
OperationTypes[TOOLBAR_UNFLAG] = 'unflag';
OperationTypes[TOOLBAR_MARK_ALL_READ] = 'mark_all_read';
OperationTypes[TOOLBAR_MARK_ALL_UNREAD] = 'mark_all_unread';
OperationTypes[TOOLBAR_MOVE_TO_FOLDER] = 'move_to_folder';
// OperationTypes[TOOLBAR_COPY_TO_FOLDER] = 'copy_to_folder';
OperationTypes[TOOLBAR_IS_SPAM] = 'spam';
OperationTypes[TOOLBAR_NOT_SPAM] = 'not_spam';
OperationTypes[TOOLBAR_EMPTY_SPAM] = 'clear_spam';
OperationTypes[TOOLBAR_NO_MOVE_DELETE] = 'no_move_delete';

var REDRAW_NOTHING = 0;
var REDRAW_FOLDER = 1;
var REDRAW_HEADER = 2;
var REDRAW_PAGE = 3;

var COOKIE_STORAGE_DAYS = 20;
var FOLDERS_TREES_INDENT = 8;
var AUTOSELECT_CHARSET = -1;
var X_ICON_SHIFT = 40;
var Y_ICON_SHIFT = 40;

var POP3_PROTOCOL = 0;
var IMAP4_PROTOCOL = 1;
var WMSERVER_PROTOCOL = 2;
var POP3_PORT = 110;
var IMAP4_PORT = 143;
var SMTP_PORT = 25;

var VIEW_MODE_CENTRAL_LIST_PANE = 1;
var VIEW_MODE_SHOW_PICTURES = 2;

//defines for contacts headers
var CH_CHECK = 20;
var CH_GROUP = 21;
var CH_NAME = 22;
var CH_EMAIL = 23;

var SORT_FIELD_GROUP = 0;
var SORT_FIELD_NAME = 1;
var SORT_FIELD_EMAIL = 2;
var SORT_FIELD_USE_FREQ = 3;

var ContactsHeaders = [];
ContactsHeaders[CH_CHECK] =
{
	DisplayField: 'Check',
	LangField: '',
	Picture: '',
	SortField: SORT_FIELD_NOTHING,
	SortIconPlace: 1,
	Align: 'center', 
	Width: 24,
	MinWidth: 24,
	IsResize: false
};
ContactsHeaders[CH_GROUP] =
{
	DisplayField: 'IsGroup',
	LangField: '',
	Picture: 'wm_inbox_lines_group',
	SortField: SORT_FIELD_GROUP,
	SortIconPlace: 1,
	Align: 'center', 
	Width: 25,
	MinWidth: 25,
	IsResize: false
};
ContactsHeaders[CH_NAME] =
{
	DisplayField: 'Name',
	LangField: 'Name',
	Picture: '',
	SortField: SORT_FIELD_NAME,
	SortIconPlace: 2,
	Align: window.LEFT, 
	Width: 150,
	MinWidth: 100,
	IsResize: true
};
ContactsHeaders[CH_EMAIL] =
{
	DisplayField: 'Email',
	LangField: 'Email',
	Picture: '',
	SortField: SORT_FIELD_EMAIL,
	SortIconPlace: 2,
	Align: window.LEFT,
	Width: 150,
	MinWidth: 100,
	IsResize: true
};

var PRIMARY_HOME_EMAIL = 0;
var PRIMARY_BUSS_EMAIL = 1;
var PRIMARY_OTHER_EMAIL = 2;
var PRIMARY_DEFAULT_EMAIL = PRIMARY_HOME_EMAIL;
var UseCustomContacts = false;
var UseCustomContacts1 = false;

var GET_FOLDERS_NOT_CHANGE_ACCT = -1;
var GET_FOLDERS_NOT_SYNC = 0;
var GET_FOLDERS_SYNC_MESSAGES = 1;
var GET_FOLDERS_SYNC_FOLDERS = 2;

var SIGNATURE_TYPE_PLAIN = 0;
var SIGNATURE_TYPE_HTML = 1;
var SIGNATURE_OPT_DONT_ADD_TO_ALL = 0;
var SIGNATURE_OPT_ADD_TO_ALL = 1;
var SIGNATURE_OPT_DONT_ADD_TO_REPLIES = 2;

var PRIORITY_LOW = 5;
var PRIORITY_NORMAL = 3;
var PRIORITY_HIGH = 1;

var SENSIVITY_NOTHING = 0;
var SENSIVITY_CONFIDENTIAL = 1;
var SENSIVITY_PRIVATE = 2;
var SENSIVITY_PERSONAL = 3;

var FILTER_STATUS_NEW = 'new';
var FILTER_STATUS_UPDATED = 'updated';
var FILTER_STATUS_UNCHANGED = 'unchanged';
var FILTER_STATUS_REMOVED = 'removed';

var AddPriorityHeader = false;
var AddSensivityHeader = false;

var CustomTopLinks = [
	//{ Name: 'Google', Link: 'http://google.com/' }
];

var POPUP_SHOWED = 2;
var POPUP_READY = 1;
var POPUP_HIDDEN = 0;

var MIN_SCREEN_HEIGHT = 400;
var MIN_SCREEN_WIDTH = 600;

var SEND_MODE = 0;
var SAVE_MODE = 1;

var RESIZE_MODE_ALL = 0;
var RESIZE_MODE_FOLDERS = 1;
var RESIZE_MODE_MSG_WIDTH = 2;
var RESIZE_MODE_MSG_HEIGHT = 3;
var RESIZE_MODE_MSG_PANE = 4;

var SAFETY_NOTHING = 0;
var SAFETY_FULL = 1;
var SAFETY_MESSAGE = 2;

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}