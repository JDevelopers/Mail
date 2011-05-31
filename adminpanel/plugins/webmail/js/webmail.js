
/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */
 
function CHideDbItem(id, radioId, divId, divSwitcher, objSwitcher)
{
	this.Id = id;
    this._radio = document.getElementById(radioId);
    this._div = document.getElementById(divId);
    this._divSwitcher = document.getElementById(divSwitcher);
    this._objSwitcher = objSwitcher;
    
    this.Checked = function ()
    {
    	if (this._radio == null) return false;
    	return this._radio.checked;
    };
    
    this.MakeActive = function ()
    {
    	if (this._div == null) return;
    	this._SetDisabled(false);
    };

    this.MakePassive = function ()
    {
    	if (this._div == null) return;
    	this._SetDisabled(true);
    };
    
    this._SetDisabled = function (bValue)
    {
    	this._div.className = (bValue) ? '' : 'activ';
    	
    	if (!bValue) {
    		this._divSwitcher.appendChild(this._objSwitcher);
    	}
    };

	if (this._radio == null) return;
    this._radio.onclick = function ()
    {
    	if (this.checked)
    		wmDbSettings.SetMode(id);
    };
}

var wmDbSettings = {
	_radioMySQL: null,
	_radioMSSQL: null,
	
	_msg: null,
	
	_login: null,
	_password: null,
	_dbname: null,
	_dsn: null,
	_host: null,
	
	_dsnSwitcher: null,
	
	_csSwitcher: null,
	_csSwitcherChild: null,
	
	_button: null,
	
	_isMSSQL: false,
	_isMySQL: false,
	_isODBC: false,
	
	_allGood: false,
	
	Init: function ()
	{
		var obj = this;
		this._radioMSSQL = document.getElementById('intDbType0');
		this._radioMySQL = document.getElementById('intDbType1');
		
		this._msg = document.getElementById('dbMessageDiv');
		
		this._login = document.getElementById('txtSqlLogin');
		this._password = document.getElementById('txtSqlPassword');
		this._dbname = document.getElementById('txtSqlName');
		this._dsn = document.getElementById('txtSqlDsn');
		this._host = document.getElementById('txtSqlSrc');
		
		this._button = document.getElementById('test_btn');
		this._hidden = document.getElementById('isTestConnection');
		
		this._dsnSwitcher = document.getElementById("useDSN");
		this._csSwitcher = document.getElementById("useCS");
		this._csSwitcherChild = document.getElementById("odbcConnectionString");
		
		this._allGood = (this._radioMySQL && this._radioMSSQL && this._msg &&
			this._login && this._password && this._dbname && this._dsn && this._host &&
			this._button && this._hidden &&	this._csSwitcher && this._csSwitcherChild && this._dsnSwitcher);
		
		if (this._allGood) {
			this._dsnSwitcher.onclick = function() {
				if (obj._dsnSwitcher.checked) {
					obj._csSwitcher.checked = false;
				};
				obj.InitForm();
			};
			this._csSwitcher.onclick = function() {
				if (obj._csSwitcher.checked) {
					obj._dsnSwitcher.checked = false;
				};
				obj.InitForm();
			};
			
			this._radioMySQL.onclick = function() {
				obj.InitForm();
			};
			
			this._radioMSSQL.onclick = function() {
				obj.InitForm();
			};
			
			this._button.onclick = function() { 
				obj._hidden.value = "1";
				return true;
			};
			
			FormButtonDisable([this._button]);
			
			this._dsn.onkeyup = function() {
				obj.InitForm();
			};
		}
		
		this.InitForm();
	},
	
	InitDB: function (MySQL, MSSQL, ODBC)
	{
		this._isMySQL = MySQL;
		this._isMSSQL = MSSQL;
		this._isODBC = ODBC;
		this.Init();
	},
	
	SetCsCheckBoxView: function(isHide)
	{
		if (this._allGood) {
			this._csSwitcher.className = (isHide) ? "wm_hide" : "wm_checkbox";
		}
	},
	
	SetInfo: function (msg)
	{
		if (this._allGood) {
			if (msg.length > 0) {
				this._msg.className = "wm_install_db_msg";
				this._msg.innerHTML = msg;
			} else {
				this._msg.className = "wm_install_db_msg_null";
				this._msg.innerHTML = '<br />';
			}
			
		}
	},
	
	InitTest: function () {
		
		var obj = this;
		var o0 = document.getElementById('c00');
		var o1 = document.getElementById('c01');
		var o2 = document.getElementById('c02');
		
		if (o0 && o1 && o2) {
			o0.onclick = function() { obj.InitTest() };
			o1.onclick = function() { obj.InitTest() };
			o2.onclick = function() { obj.InitTest() };
			
			this._isMySQL = (o0.checked);
			this._isMSSQL = (o1.checked);
			this._isODBC = (o2.checked);
		}
		
		this.InitForm();
	},
	
	InitForm: function ()
	{
		if (this._allGood) {
			var _msg = '';
			var _useOdbc = false;
			var _allOff = (!this._isODBC && !this._isMySQL && !this._isMSSQL);
			
			if (this._isODBC || (this._isMySQL && this._isMSSQL)) {
				SetDisabled(this._radioMySQL, false, true);
				SetDisabled(this._radioMSSQL, false, true);
			} else if (_allOff) {
				_msg = 'Error.';
				SetDisabled(this._radioMySQL, true, true);
				SetDisabled(this._radioMSSQL, true, true);
			} else {
				if (!this._isMySQL) {
					this._radioMSSQL.checked = true;
					SetDisabled(this._radioMySQL, true, true);
					SetDisabled(this._radioMSSQL, false, true);
				} else {
					this._radioMySQL.checked = true;	
					SetDisabled(this._radioMySQL, false, true);
					SetDisabled(this._radioMSSQL, true, true);
				}
			}
			
			if (!this._isODBC) {
				this._csSwitcher.checked = false;
				this._dsnSwitcher.checked = false;
				SetDisabled(this._csSwitcher, true, true);
				SetDisabled(this._dsnSwitcher, true, true);
				SetDisabled(this._dsn, true, true);
			} else {
			
				if ((!this._isMySQL && this._radioMySQL.checked) || (!this._isMSSQL && this._radioMSSQL.checked))
				{
					_msg = '<img src="./images/alarm.png" /> '; 
					_msg += (!this._isMySQL) 
						? 'Native PHP extension for MySQL not detected. The only option available is ODBC/DSN connection.'
						: 'Native PHP extension for MS SQL not detected. The only option available is ODBC/DSN connection.';
				}
			
				SetDisabled(this._csSwitcher, false, true);
				SetDisabled(this._dsnSwitcher, false, true);
				if ((!this._isMSSQL && this._radioMSSQL.checked)
						|| (this._radioMySQL.checked && !this._isMySQL)) {
					_useOdbc = true;
				}
			}
			
			SetDisabled(this._csSwitcherChild, !(this._csSwitcher.checked));
			SetDisabled(this._dsn, !(this._dsnSwitcher.checked));
			
			var _isDsn = (!this._dsn.disabled && this._dsnSwitcher.checked);

			SetDisabled(this._login, ((!this._isMySQL && !this._isMSSQL && !_isDsn && !this._csSwitcher.checked)
				|| ((this._csSwitcher.checked || _isDsn) && !this._radioMSSQL.checked)), true);
			SetDisabled(this._password, ((!this._isMySQL && !this._isMSSQL && !_isDsn && !this._csSwitcher.checked)
				|| ((this._csSwitcher.checked || _isDsn) && !this._radioMSSQL.checked)), true);
			
			SetDisabled(this._dbname, (this._csSwitcher.checked || _isDsn || _useOdbc || _allOff), true);
			SetDisabled(this._host, (this._csSwitcher.checked || _isDsn || _useOdbc || _allOff), true);
			
			this.SetInfo(_msg);
		}
	}
};

var wmWebMailSettings = {
	
	Init: function ()
	{
		var obj = this;
		this.protocolSwitcher = document.getElementById("intIncomingMailProtocol");
		this.protocolPort = document.getElementById("intIncomingMailPort");
		if (this.protocolSwitcher && this.protocolPort){
			Validator.RegisterAllowNum(this.protocolPort);
			this.protocolSwitcher.onchange = function() {
				obj.InitForm(1);
			}				
		}
		
		var outPort = document.getElementById("intOutgoingMailPort");
		if (outPort){
			Validator.RegisterAllowNum(outPort);
		}
		
		var aSizeLimit = document.getElementById("intAttachmentSizeLimit");
		if (aSizeLimit){
			Validator.RegisterAllowNum(aSizeLimit);
		}
		var mSizeLimit = document.getElementById("intMailboxSizeLimit");
		if (mSizeLimit){
			Validator.RegisterAllowNum(mSizeLimit);
		}
		
		this.dmSwitcher = document.getElementById("intAllowDirectMode");
		this.dmSwitcherChild = document.getElementById("intDirectModeIsDefault");
		if (this.dmSwitcher && this.dmSwitcherChild){
			this.dmSwitcher.onchange = function() {
				obj.InitForm(2);
			}				
		}
		
		this.attachSwitcher = document.getElementById("intEnableAttachSizeLimit");
		this.attachSwitcherChild = document.getElementById("intAttachmentSizeLimit");
		if (this.attachSwitcher && this.attachSwitcherChild){
			this.attachSwitcher.onchange = function() {
				obj.InitForm(3);
			}				
		}
		
		this.mailBoxSwitcher = document.getElementById("intEnableMailboxSizeLimit");
		this.mailBoxSwitcherChild = document.getElementById("intMailboxSizeLimit");
		this.takeImapQuota = document.getElementById("intTakeImapQuota");
		if (this.mailBoxSwitcher && this.mailBoxSwitcherChild && this.takeImapQuota){
			this.mailBoxSwitcher.onchange = function() {
				obj.InitForm(4);
			}				
		}

		this.allowUserAddAccountSwitcher = document.getElementById("intAllowUsersAddNewAccounts");
		this.allowUserChangeAccountsDefSwitcher = document.getElementById("intAllowUsersChangeAccountsDef");
		if (this.allowUserAddAccountSwitcher && this.allowUserChangeAccountsDefSwitcher){
			this.allowUserAddAccountSwitcher.onchange = function() {
				obj.InitForm(5);
			}
		}
		
		this.InitForm();
	},
	
	InitForm: function (svalue)
	{
		svalue = (undefined == svalue) ? 0 : parseInt(svalue);
		if ((svalue == 1) && this.protocolSwitcher && this.protocolPort){
			this.protocolPort.value = (this.protocolSwitcher.value == 0) ? '110' : '143';
		}
		if ((svalue == 2 || svalue == 0) && this.dmSwitcher && this.dmSwitcherChild){
			if (svalue == 2 && !(this.dmSwitcher.checked)) {
				this.dmSwitcherChild.checked = false;
			}
			SetDisabled(this.dmSwitcherChild, !(this.dmSwitcher.checked), true);
		}
		if ((svalue == 3 || svalue == 0) && this.attachSwitcher && this.attachSwitcherChild){
			SetDisabled(this.attachSwitcherChild, !(this.attachSwitcher.checked));
		}
		if ((svalue == 4 || svalue == 0) && this.mailBoxSwitcher && this.mailBoxSwitcherChild && this.takeImapQuota){
			if (svalue == 4 && !(this.mailBoxSwitcher.checked)) {
				this.takeImapQuota.checked = false;
			}
			SetDisabled(this.mailBoxSwitcherChild, !(this.mailBoxSwitcher.checked));
			SetDisabled(this.takeImapQuota, !(this.mailBoxSwitcher.checked), true);
		}
		if ((svalue == 5 || svalue == 0) && this.allowUserAddAccountSwitcher && this.allowUserChangeAccountsDefSwitcher){
			if (svalue == 5 && !(this.allowUserAddAccountSwitcher.checked)) {
				this.allowUserChangeAccountsDefSwitcher.checked = false;
			}
			SetDisabled(this.allowUserChangeAccountsDefSwitcher, !(this.allowUserAddAccountSwitcher.checked), true);
		}
	}
};

function CHideLoginItem(id, radioId, divId)
{
	this.Id = id;
    this._radio = document.getElementById(radioId);
    this._div = document.getElementById(divId);
    
    this.Checked = function ()
    {
    	if (this._radio == null) return false;
    	return this._radio.checked;
    };
    
    this.MakeActive = function ()
    {
    	if (this._div == null) return;
    	this._SetDisabled(false);
    };

    this.MakePassive = function ()
    {
    	if (this._div == null) return;
    	this._SetDisabled(true);
    };
    
    this._SetDisabled = function (bValue)
    {
    	this._div.className = (bValue) ? '' : 'activ';
    	var inputs = this._div.getElementsByTagName("INPUT");
    	var selects = this._div.getElementsByTagName("SELECT");
    	var i, c;
    	for (i = 1, c = inputs.length; i < c; i++)
    	{
    		SetDisabled(inputs[i], bValue, true);
    	}
    	for (i = 0, c = selects.length; i < c; i++)
    	{
    		SetDisabled(selects[i], bValue, true);
    	}
    };

	if (this._radio == null) return;
    this._radio.onclick = function () {
    	if (this.checked) {
    		wmLoginSettings.SetMode(id);
		}
    };
}

var wmMobileSync = {
	_checkBox: null,

	Init: function () {
		this._checkBox = document.getElementById('chEnableMobileSync');
		var obj = this;
		if (this._checkBox) {
			this._checkBox.onclick = function() { obj.CheckCh(); };
			this.CheckCh();
		}
	},

	CheckCh: function () {
		var bIsChecked = this._checkBox.checked;
		SetDisabled(document.getElementById('txtPathForMobileSync'), !bIsChecked, true);
		SetDisabled(document.getElementById('txtMobileSyncContactDataBase'), !bIsChecked, true);
		SetDisabled(document.getElementById('txtMobileSyncCalendarDataBase'), !bIsChecked, true);
	}
}

var wmLoginSettings = {
	Constants: {
		StandardLogin: 0,
		HideLogin: 1,
		HideEmail: 2
	},
	_mode: 0,
	_items: Array(),
	_displayDomainAfter: null,
	_displayDomainAfterItems: null,
	_domainsExist: false,
	
	Init: function ()
	{
		var consts = this.Constants;
	    this._items[consts.StandardLogin] = new CHideLoginItem(consts.StandardLogin, 'hideLoginRadionButton1', 'hideLoginDiv1');
	    this._items[consts.HideLogin] = new CHideLoginItem(consts.HideLogin, 'hideLoginRadionButton2', 'hideLoginDiv2');
	    this._items[consts.HideEmail] = new CHideLoginItem(consts.HideEmail, 'hideLoginRadionButton3', 'hideLoginDiv3');
		
		var iCount = this._items.length;
		for (var i=0; i<iCount; i++) {
			var item = this._items[i];
			if (item.Checked()) {
				item.MakeActive();
				this._mode = item.Id;
			} else {
				item.MakePassive();
			}
		}

		this._displayDomainAfter = document.getElementById('intDisplayDomainAfterLoginField');
		this._displayDomainAfterItems = [
			document.getElementById('intDomainDisplayType1'),
			document.getElementById('intDomainDisplayType2'),
			document.getElementById('txtUseDomain')
		];

		var domainsExist = document.getElementById('intDomainsExistValue');
		if (domainsExist && domainsExist.value == '1') {
			this._domainsExist = true;
		}

		var obj = this;
		if (this._displayDomainAfter) {
			this._displayDomainAfter.onchange = function() {
				obj.InitDisplayDomainMode();
			}
		}
		
		this.InitDisplayDomainMode();
	},
	
	SetMode: function (mode)
	{
		var iCount = this._items.length;
		for (var i=0; i<iCount; i++) {
			var item = this._items[i];
			if (item.Id == mode) {
				item.MakeActive();
			}
			else {
				item.MakePassive();
			}
		}
		this.InitDisplayDomainMode();
	},

	InitDisplayDomainMode: function ()
	{
		if (this._displayDomainAfter && !this._displayDomainAfter.disabled) {
			var i, c, item, isEnabled = this._displayDomainAfter.checked;
			for (i = 0, c = this._displayDomainAfterItems.length; i < c; i++) {
				item = this._displayDomainAfterItems[i];
				if (item) {
					if (i == 0 && !this._domainsExist) {
						SetDisabled(item, true, true);
					} else {
						SetDisabled(item, !isEnabled, true);
					}
				}
			}
		}
	}
};

function FormButtonDisable(_arrObj) {
	if (_arrObj.length > 0) {
		var _form = _arrObj[0].form;
		if (_form) {
			_form.onsubmit = function() { DisableButtons(_arrObj);};
		};
	};
};

function DisableButtons(_arrObj) {
	for (var i=(_arrObj.length - 1); i>=0; i--) {
		if (_arrObj[i]) {
			_arrObj[i].disabled = true;
		};
	};
	return true;
};

var SettingsObjects = Array();
SettingsObjects['db'] = wmDbSettings;
SettingsObjects['webmail'] = wmWebMailSettings;
SettingsObjects['login'] = wmLoginSettings;
SettingsObjects['mobilesync'] = wmMobileSync;
/* SettingsObjects['cal'] = wmCalSettings; */