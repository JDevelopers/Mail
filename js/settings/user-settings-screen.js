/*
 * Classes:
 * 	CUserSettingsScreen()
 */

var SETTINGS_TAB_COMMON = 0;
var SETTINGS_TAB_ACCOUNTS = 1;
var SETTINGS_TAB_CALENDAR = 2;
var SETTINGS_TAB_MOBILE_SYNC = 3;

var SettingsTabDescription = [];
SettingsTabDescription[SETTINGS_TAB_COMMON] = {x: 5*X_ICON_SHIFT, y: 4*Y_ICON_SHIFT};
SettingsTabDescription[SETTINGS_TAB_ACCOUNTS] = {x: 3*X_ICON_SHIFT, y: 4*Y_ICON_SHIFT};
SettingsTabDescription[SETTINGS_TAB_CALENDAR] = {x: 4*X_ICON_SHIFT, y: 4*Y_ICON_SHIFT};
SettingsTabDescription[SETTINGS_TAB_MOBILE_SYNC] = {x: 8*X_ICON_SHIFT, y: 5*Y_ICON_SHIFT};

function CUserSettingsScreen()
{
	this.Id = SCREEN_USER_SETTINGS;
	this.isBuilded = false;
	this.hasCopyright = false;
	this.BodyAutoOverflow = true;
	this.HistoryArgs = null;

	this._allowAutoresponder = true;

	this._idAcct = -1;
	
	this.Settings = null;
	this.NewSettings = this.Settings;

	this.Autoresponders = [];

	this._mainContainer = null;
	this._nav = null;
	this._cont = null;
	this._addAccountTbl = null;

	this._calendarSettingsDiv = null;
	this._accountsSettingsDiv = null;
	this._commonSettingsDiv = null;

	this._commonSettingsObj = new CCommonSettingsScreenPart(this);
	this._accountPropertiesObj = new CAccountPropertiesScreenPart(this);
	this._filtersObj = new CFiltersScreenPart(this);
	this._signatureObj = new CSignatureScreenPart(this);
	this._autoresponderObj = new CAutoresponderScreenPart(this);
	this._manageFoldersObj = new CManageFoldersScreenPart(this);
	this._accountsListObj = new CAccountListScreenPart(this, this._manageFoldersObj);
	this._calendarSettingsObj = new CCalendarSettingsScreenPart(this);
	this._mobileSyncObj = new CMobileSyncSettingsScreenPart(this);
	this._currPart = this._commonSettingsObj;

	this._manageFoldersSwitcher = null;
	this._manageFoldersTypeSwitcher = null;
	this._signatureSwitcher = null;
	this._autoresponderSwitcher = null;
	this._filtersSwitcher = null;
	this._propertiesSwitcher = null;

	this.SwitcherMode = PART_ACCOUNT_PROPERTIES;
	this._newMode = false;
}

CUserSettingsScreen.prototype = {
	PlaceData: function(Data) {
		if (Data) {
			var Type = Data.Type;
			switch (Type) {
				case TYPE_ACCOUNT_LIST:
					this._newMode = false;
					if (this._accountsListObj.shown)
						this.ShowSettingsSwitcher(this.SwitcherMode);
					if (Data.HasAccount(this._idAcct) && !this._newMode) {
						this.ChangeAccountId(this._idAcct);
					} else {
						if (-1 != Data.EditableId) {
							this.ChangeAccountId(Data.EditableId);
						} else {
							this.ChangeAccountId(Data.CurrId);
						}
					}
					break;
				case TYPE_FOLDER_LIST:
					this._filtersObj.FillFolders(Data.Folders);
					this.isChangedFolders = this._manageFoldersObj.isChangedFolders;
					this._manageFoldersObj.UpdateFolders(Data);
					break;
				case TYPE_MOBILE_SYNC:
					this._mobileSyncObj.SetSettings(Data);
					break;
				case TYPE_USER_SETTINGS:
					this.Settings = Data;
					this._commonSettingsObj.SetSettings(Data);
					break;
				case TYPE_FILTERS:
					this._filtersObj.SetFilters(Data.Items);
					break;
				case TYPE_AUTORESPONDER:
					this.Autoresponders[Data.IdAcct] = Data;
					if (this._idAcct == Data.IdAcct) {
						this._autoresponderObj.SetAutoresponder(Data);
					}
					break;
			} //switch
		}
	},
	
	GetNewAccountProperties: function() {
		return this._accountPropertiesObj.GetNewAccountProperties();
	},

	GetNewSettings: function() {
		this.Settings = this._commonSettingsObj.GetNewSettings();
		return this.Settings;
	},

	UpdateMobileSync: function() {
		this._mobileSyncObj.UpdateSettings();
	},

	GetNewSignature: function() {
		return this._signatureObj.GetNewSignature();
	},

	SetNewAutoresponder: function() {
		var autoresponder = this._autoresponderObj.GetNewAutoresponder();
		this.Autoresponders[autoresponder.IdAcct] = autoresponder;
	},

	ClickBody: function(ev) {
		this._signatureObj.ClickBody();
		this._filtersObj.ClickBody(ev);
	},

	ResizeBody: function() {
		if (this.isBuilded) {
			if (this._signatureObj.shown) {
				this._signatureObj.ReplaceHtmlEditorField();
			}
			if (this.userSettContainer) {
				var height = GetHeight();
				var offsetH = this.GetOffsetH();
				if (offsetH) {
					height -= offsetH;
				}
				if (height < 300) {
					height = 300;
				}
				this.userSettContainer.resizeMainHeight(height);
			}
		}
	},

    _showTabs: function (currentTabId, switcherMode)
    {
        var showCommon = (window.UseDb || window.UseLdapSettings);
        var showCalendar = (window.UseDb && WebMail.Settings.AllowCalendar);
        var showMobileSync = (WebMail.Settings.MobileSyncEnable);
        
		this._commonSettingsDiv.className = (showCommon) ? 'wm_settings_item' : 'wm_hide';
		this._accountsSettingsDiv.className = 'wm_settings_item';
		this._calendarSettingsDiv.className = (showCalendar) ? 'wm_settings_item' : 'wm_hide';
		this._mobileSyncDiv.className = (showMobileSync) ? 'wm_settings_item' : 'wm_hide';

        var newPart;
        var showAccountsList = false;
        var showSettingsSwitcher = false;
        switch (currentTabId) {
            case SETTINGS_TAB_COMMON:
                this._commonSettingsDiv.className = (showCommon) ? 'wm_selected_settings_item' : 'wm_hide';
                newPart = this._commonSettingsObj;
                break;
            case SETTINGS_TAB_ACCOUNTS:
                this._accountsSettingsDiv.className = 'wm_selected_settings_item';
                showAccountsList = true;
                newPart = this._accountPropertiesObj;
                showSettingsSwitcher = !this._newMode;
                switch (switcherMode) {
                    case PART_ACCOUNT_PROPERTIES:
                        newPart = this._accountPropertiesObj;
                        break;
                    case PART_FILTERS:
                        newPart = this._filtersObj;
                        break;
                    case PART_AUTORESPONDER:
                        newPart = this._autoresponderObj;
                        break;
                    case PART_SIGNATURE:
                        newPart = this._signatureObj;
                        break;
                    case PART_MANAGE_FOLDERS:
                        newPart = this._manageFoldersObj;
                        break;
                }
                break;
            case SETTINGS_TAB_CALENDAR:
                this._calendarSettingsDiv.className = (showCalendar) ? 'wm_selected_settings_item' : 'wm_hide';
                newPart = this._calendarSettingsObj;
                break;
            case SETTINGS_TAB_MOBILE_SYNC:
                this._mobileSyncDiv.className = (showMobileSync) ? 'wm_selected_settings_item' : 'wm_hide';
                newPart = this._mobileSyncObj;
                break;
        }
        if (newPart == null) return;

		this._settingsSwitcher.className = 'wm_hide';
		if (showSettingsSwitcher) {
			this.ShowSettingsSwitcher(switcherMode);
        }
        else {
			this._settingsSwitcher.className = 'wm_hide';
		}
        if (showAccountsList) {
    		this._accountsListObj.Show();
            var addAccountClass = (WebMail.Settings.AllowAddAccount && WebMail.Settings.AllowChangeSettings && window.UseDb)
                ? 'wm_settings_add_account_button' : 'wm_hide';
    		this._addAccountTbl.className = addAccountClass;
        }
        else {
    		this._accountsListObj.Hide();
    		this._addAccountTbl.className = 'wm_hide';
        }
		if (this._currPart != newPart) {
			this._currPart.Hide();
		}
		this._currPart = newPart;
		this._currPart.Show();
   },

	ChangeAccountId: function(id) {
		if (-1 != id) {
			this._idAcct = id;
			WebMail.Accounts.ChangeEditableAccount(id);
			var protocol = WebMail.Accounts.GetAccountProtocol(id);
			var isInternal = WebMail.Accounts.GetAccountInternal(id);
			this._allowAutoresponder = (protocol == WMSERVER_PROTOCOL || isInternal || window.hMailServer === true);
			this._accountsListObj.Fill();
			this._accountPropertiesObj.Fill(this._newMode);
			if (this.Autoresponders[id]) {
				this._autoresponderObj.SetAutoresponder(this.Autoresponders[id]);
			}
			if (this._autoresponderObj && isInternal) {
				this._autoresponderObj.SetSubjectView(true);
			}
			if (this._manageFoldersObj.shown) {
				this._manageFoldersObj.Show(id);
			}
		}
	},

	ShowSettingsSwitcher: function(mode) {
		this.SwitcherMode = mode;
		var obj = this;
		var a, div;

		this._settingsSwitcher.className = 'wm_settings_accounts_info';

		div = this._manageFoldersSwitcher;
		div.innerHTML = '';
		if (mode == PART_MANAGE_FOLDERS) {
			div.className = 'wm_settings_switcher_select_item';
			div.innerHTML = Lang.ManageFolders;
		} else {
			div.className = 'wm_settings_switcher_item';
			a = CreateChild(div, 'a', [['href', '#']]);
			a.innerHTML = Lang.ManageFolders;
			a.onclick = function() {
				SetHistoryHandler(
					{
						ScreenId: SCREEN_USER_SETTINGS,
						SelectIdAcct: obj._idAcct,
						Entity: PART_MANAGE_FOLDERS,
						SetIdAcct: false,
						NewMode: false
					}
				);
				return false;
			};
		}

		if (window.UseDb || window.UseLdapSettings) {
			div = this._signatureSwitcher;
			div.innerHTML = '';
			if (mode == PART_SIGNATURE) {
				div.className = 'wm_settings_switcher_select_item';
				div.innerHTML = Lang.Signature;
			}
			else {
				div.className = 'wm_settings_switcher_item';
				a = CreateChild(div, 'a', [['href', '#']]);
				a.innerHTML = Lang.Signature;
				a.onclick = function() {
					SetHistoryHandler(
						{
							ScreenId: SCREEN_USER_SETTINGS,
							SelectIdAcct: obj._idAcct,
							Entity: PART_SIGNATURE,
							NewMode: false
						}
					);
					return false;
				};
			}
		}

		if (this._allowAutoresponder && (window.UseDb || window.UseLdapSettings)) {
			div = this._autoresponderSwitcher;
			div.innerHTML = '';
			if (mode == PART_AUTORESPONDER) {
				div.className = 'wm_settings_switcher_select_item';
				div.innerHTML = Lang.AutoresponderTitle;
			} else {
				div.className = 'wm_settings_switcher_item';
				a = CreateChild(div, 'a', [['href', '#']]);
				a.innerHTML = Lang.AutoresponderTitle;
				a.onclick = function() {
					SetHistoryHandler(
						{
							ScreenId: SCREEN_USER_SETTINGS,
							SelectIdAcct: obj._idAcct,
							Entity: PART_AUTORESPONDER,
							NewMode: false
						}
					);
					return false;
				};
			}
		}
		else {
		    this._autoresponderSwitcher.className = 'wm_hide';
		}

		if (window.UseDb) {
			div = this._filtersSwitcher;
			div.innerHTML = '';
			if (mode == PART_FILTERS) {
				div.className = 'wm_settings_switcher_select_item';
				div.innerHTML = Lang.Filters;
			}
			else {
				div.className = 'wm_settings_switcher_item';
				a = CreateChild(div, 'a', [['href', '#']]);
				a.innerHTML = Lang.Filters;
				a.onclick = function() {
					SetHistoryHandler(
						{
							ScreenId: SCREEN_USER_SETTINGS,
							SelectIdAcct: obj._idAcct,
							Entity: PART_FILTERS,
							NewMode: false
						}
					);
					return false;
				};
			}
		}

		if (window.UseDb) {
			div = this._propertiesSwitcher;
			div.innerHTML = '';
			if (mode == PART_ACCOUNT_PROPERTIES) {
				div.className = 'wm_settings_switcher_select_item';
				div.innerHTML = Lang.Properties;
			}
			else {
				div.className = 'wm_settings_switcher_item';
				a = CreateChild(div, 'a', [['href', '#']]);
				a.innerHTML = Lang.Properties;
				a.onclick = function() {
					SetHistoryHandler(
						{
							ScreenId: SCREEN_USER_SETTINGS,
							SelectIdAcct: obj._idAcct,
							Entity: PART_ACCOUNT_PROPERTIES,
							NewMode: false
						}
					);
					return false;
				};
			}
		}
	}, //ShowSettingsSwitcher

	Show: function(settings, historyArgs) {
		this.ChangeAccountId(WebMail.Accounts.EditableId);
		if (this.isBuilded) {
			this._mainContainer.className = '';
			if (null != historyArgs) {
				this.RestoreFromHistory(historyArgs);
			}
            else {
				if (window.UseDb || window.UseLdapSettings) {
            		this._showTabs(SETTINGS_TAB_COMMON);
				}
                else {
               		this._showTabs(SETTINGS_TAB_ACCOUNTS, PART_MANAGE_FOLDERS);
				}
			}
		}
		/*if (this._accountsListObj.shown) {
			this._addAccountTbl.className = this._addAccountClass;
		}*/
		if (-1 != this._idAcct) {
			GetHandler(TYPE_FOLDER_LIST, {IdAcct: this._idAcct, Sync: GET_FOLDERS_NOT_CHANGE_ACCT}, [], '');
		}
	},

	RestoreFromHistory: function(historyArgs) {
		this.HistoryArgs = historyArgs;
		var newIdAcctNotNull = ('undefined' != historyArgs.SelectIdAcct && null != historyArgs.SelectIdAcct &&
			-1 != historyArgs.SelectIdAcct);
		var idAcctChanged = (newIdAcctNotNull && historyArgs.SelectIdAcct != this._idAcct);
		if (idAcctChanged) {
			this.ChangeAccountId(historyArgs.SelectIdAcct);
		}
		switch (historyArgs.Entity) {
			case PART_COMMON_SETTINGS:
        		this._showTabs(SETTINGS_TAB_COMMON);
				break;
			case PART_ACCOUNT_PROPERTIES:
				this._newMode = historyArgs.NewMode;
        		this._showTabs(SETTINGS_TAB_ACCOUNTS, PART_ACCOUNT_PROPERTIES);
				break;
			case PART_FILTERS:
			case PART_SIGNATURE:
			case PART_MANAGE_FOLDERS:
        		this._showTabs(SETTINGS_TAB_ACCOUNTS, historyArgs.Entity);
				break;
			case PART_AUTORESPONDER:
                if (this._allowAutoresponder) {
                    this._showTabs(SETTINGS_TAB_ACCOUNTS, PART_AUTORESPONDER);
                }
				break;
			case PART_CALENDAR_SETTINGS:
        		this._showTabs(SETTINGS_TAB_CALENDAR);
				break;
			case PART_MOBILE_SYNC:
        		this._showTabs(SETTINGS_TAB_MOBILE_SYNC);
				break;
		}
		if (Browser.Mozilla) {
			var navHeight = this._nav.offsetHeight;
			var contHeight = this._cont.offsetHeight;
			if (navHeight > contHeight) {
				this._cont.style.height = navHeight + 'px';
			}
			else if (navHeight != contHeight) {
				this._nav.style.height = contHeight + 'px';
			}
			this._cont.style.height = 'auto';
			this._nav.style.height = 'auto';
		}
	},

	ParseSettings: function () { },

	Hide: function() {
		if (this.isBuilded) {
			this._commonSettingsObj.Hide();
			this._accountsListObj.Hide();
			this._calendarSettingsObj.Hide();
			this._accountPropertiesObj.Hide();
			this._filtersObj.Hide();
			this._signatureObj.Hide();
			this._autoresponderObj.Hide();
			this._manageFoldersObj.Hide();
			this._mobileSyncObj.Hide();
			this._settingsSwitcher.className = 'wm_hide';
			this._mainContainer.className = 'wm_hide';
			this._addAccountTbl.className = 'wm_hide';
		}
	},

	GetOffsetH: function()
	{
		var offsetH = 0;
		var _account_bar = document.getElementById('account_bar_id');
		if (_account_bar.offsetHeight) {
			offsetH += _account_bar.offsetHeight;
		}

		var _logo = document.getElementById('logo');
		if (_logo && _logo.offsetHeight) {
			offsetH += _logo.offsetHeight;
		}
		return offsetH;
	},

	Build: function(container)
	{
		this._mainContainer = CreateChild(container, 'div');
		this._mainContainer.className = 'wm_hide';

		this.userSettContainer = new UserSettingsContainer(this._mainContainer, this._idAcct);

		this._nav = this.userSettContainer._nav;

		this._commonSettingsDiv = this.userSettContainer.addNavItem(SETTINGS_TAB_COMMON, PART_COMMON_SETTINGS, "Common", true);
		this._accountsSettingsDiv = this.userSettContainer.addNavItem(SETTINGS_TAB_ACCOUNTS, 
			(window.UseDb) ? PART_ACCOUNT_PROPERTIES : PART_MANAGE_FOLDERS, "EmailAccounts");
		this._calendarSettingsDiv = this.userSettContainer.addNavItem(SETTINGS_TAB_CALENDAR, PART_CALENDAR_SETTINGS, "SettingsTabCalendar");
		this._mobileSyncDiv = this.userSettContainer.addNavItem(SETTINGS_TAB_MOBILE_SYNC, PART_MOBILE_SYNC, "SettingsTabMobileSync");

		/////// proceed !!!!!!!!!!!
		this._cont = this.userSettContainer._cont;
		var td = this._cont;
		///////

		this._commonSettingsObj.Build(td);

		this._accountsListObj.Build(td);

		var tbl_ = CreateChild(td, 'table');
		tbl_.className = 'wm_hide';
		this._addAccountTbl = tbl_;
		var tr_ = tbl_.insertRow(0);
		var td_ = tr_.insertCell(0);
		var inp = CreateChild(td_, 'input', [['class', 'wm_button'], ['type', 'button'], ['value', Lang.AddNewAccount]]);
		WebMail.LangChanger.Register('value', inp, 'AddNewAccount', '');
		inp.onclick = function() {
			SetHistoryHandler(
				{
					ScreenId: SCREEN_USER_SETTINGS,
					SelectIdAcct: obj._idAcct,
					Entity: PART_ACCOUNT_PROPERTIES,
					NewMode: true
				}
			);
		};
		if (WebMail._isDemo) {
			inp.onclick = function() {WebMail.ShowReport(DemoWarning);};
		}

		var div = CreateChild(td, 'div');
		div.className = 'wm_hide';
		var div_ = CreateChild(div, 'div');
		div_.className = 'wm_settings_switcher_indent';
		this._manageFoldersTypeSwitcher = CreateChild(div, 'div');
		this._manageFoldersSwitcher = CreateChild(div, 'div');
		this._autoresponderSwitcher = CreateChild(div, 'div');
		this._signatureSwitcher = CreateChild(div, 'div');
		this._filtersSwitcher = CreateChild(div, 'div');
		this._propertiesSwitcher = CreateChild(div, 'div');
		this._settingsSwitcher = div;
		var obj = this;
		this._accountPropertiesObj.Build(td, obj);
		this._filtersObj.Build(td);
		this._signatureObj.Build(td);
		this._autoresponderObj.Build(td);
		this._manageFoldersObj.Build(td);

		this._calendarSettingsObj.Build(td);

		this._mobileSyncObj.Build(td);

		this.isBuilded = true;

		this.ResizeBody();
	} //Build
};

// Added by Neox 2009.02.11
function UserSettingsContainer(domParent, idAcct) {
	this._domParent = null;
	this._mainTable = null;
	this._nav = null;
	this._cont = null;
	this.navItems = null;
	this._idAcct = null;

	this.navItemCss = "wm_settings_item";
	this.navItemSelCss = "wm_selected_settings_item";
	
	this._initialize.apply(this, arguments);
}

UserSettingsContainer.prototype =
{
	_initialize: function(domParent, idAcct) {
		// parent DomObj
		this._domParent = domParent;
		this._idAcct = idAcct;

		this.createContainer();
	},

	createContainer: function() {
		var tbl = CreateChild(this._domParent, 'div');
		this._mainTable = tbl;

		var tr = CreateChild(tbl, 'div');
		tr.className = 'wm_settings_row';

		var td = CreateChild(tr, 'div');
		this._cont = td;

		td = CreateChild(tr, 'div');
		this._nav = td;

		td = CreateChild(tr, 'div');
		td.className = 'clear';

		this._mainTable.className = 'wm_settings';
		this._nav.className = 'wm_settings_nav';
		this._cont.className = 'wm_settings_cont';
	},

	resizeMainHeight: function(heightValue){
		if (this._cont.offsetHeight > heightValue) {
			heightValue = this._cont.offsetHeight;
		} else {
			var topb = GetStyleValue(this._mainTable, 'border-top-width', 'borderTopWidth');
			topb = parseInt(topb);
			if (topb && topb > 0) {
				heightValue -= topb; // this._mainTable top border
			}
		}
		this._mainTable.style.height = heightValue + 'px';
	},

	addNavItem: function(imgIndex, Entity, Name, Selected) {
		var imgBgPosition = '-' + SettingsTabDescription[imgIndex].x + 'px -' + SettingsTabDescription[imgIndex].y + 'px';
		var div = document.createElement('div');
		div.className = (Selected) ? this.navItemSelCss : this.navItemCss;
		var img = document.createElement('span');
		img.style.backgroundPosition = imgBgPosition;
		div.appendChild(img);
		var a = document.createElement('a');
		a.href = 'javascript:void(0)';
		a.appendChild(document.createTextNode(Lang[Name]));
		var historyObj = {
			ScreenId: SCREEN_USER_SETTINGS,
			SelectIdAcct: this._idAcct,
			Entity: Entity,
			NewMode: false
		};
		var aClickDlg = $createCallback(this, this.navItemClick, historyObj);
		div.appendChild(a);
		$addHandler(a, 'click', aClickDlg);
		WebMail.LangChanger.Register('innerHTML', a, Name, '');
		this._nav.appendChild(div);
		return div;
	},

	navItemClick: function(e, historyObj) {
		SetHistoryHandler(historyObj);
		return false;
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}