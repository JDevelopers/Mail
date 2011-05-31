/*
 * Functions:
 *  CreateRemoveClickFunc(id)
 *  CreateAccountClickFunc(id)
 * Classes:
 *  CAccountListScreenPart(parentScreen, manageFolders)
 */

function CreateRemoveClickFunc(id)
{
	return function () {
		if (confirm(Lang.ConfirmDeleteAccount)) {
			WebMail.DataSource.Request({action: 'delete', request: 'account', 'id_acct': id}, '');
		}
		return false;
	};
}

function CreateAccountClickFunc(id)
{
	return function () {
		SetHistoryHandler(
			{
				ScreenId: SCREEN_USER_SETTINGS,
				SelectIdAcct: id,
				Entity: PART_ACCOUNT_PROPERTIES,
				NewMode: false
			}
		);
		return false;
	};
}

function CAccountListScreenPart(parentScreen, manageFolders)
{
	this._parentScreen = parentScreen;
	
	this._idAcct = -1;
	this._manageFoldersObj = manageFolders;
	this._mainContainer = null;
	this.shown = false;
}

CAccountListScreenPart.prototype = {
	Show: function()
	{
		if (!this.shown) {
			this.shown = true;
			this._mainContainer.className = (window.UseDb) ? 'wm_settings_list' : 'wm_hide';
		}
		this.Fill();
	},
	
	Hide: function()
	{
		this.shown = false;
		this._mainContainer.className = 'wm_hide';
	},
	
	Fill: function ()
	{
		if (this.shown) {
			this._idAcct = WebMail.Accounts.EditableId;
			CleanNode(this._mainContainer);
			var tbl = CreateChild(this._mainContainer, 'table');
			var arrAccounts = WebMail.Accounts.Items;
			var rowIndex = 0;
			for (var i in arrAccounts) {
				var account = arrAccounts[i];
				var tr = tbl.insertRow(rowIndex++);
				var td = tr.insertCell(0);
				if (account.Id == WebMail.Accounts.EditableId) {
					tr.className = 'wm_settings_list_select';
					td.innerHTML = '<b>' + account.Email + '</b>';
					this._manageFoldersObj.UpdateProtocol(account.MailProtocol);
				} else {
					td.className = 'wm_control';
					td.innerHTML = account.Email;
					td.onclick = CreateAccountClickFunc(account.Id);
				}
				if (!WebMail.Settings.AllowChangeSettings) continue;
				td = tr.insertCell(1);
				td.style.width = '10px';
				if (!account.IsInternal) {
					var a = CreateChild(td, 'a', [['href', '#']]);
					a.innerHTML = Lang.Delete;
					a.onclick = CreateRemoveClickFunc(account.Id);
					if (WebMail._isDemo) {
						a.onclick = function () {
							WebMail.ShowReport(DemoWarning);
							return false;
						};
					}
				}
			}
			if (this._parentScreen) {
				this._parentScreen.ResizeBody();
			}
		}
	},
	
	Build: function(container)
	{
		this._mainContainer = CreateChild(container, 'div');
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}