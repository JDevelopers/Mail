
/*
 * AfterLogic Admin Panel by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE.txt
 * 
 */

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
	_create_db_button: null,
	
	_hidden: null,
	_hiddenDb: null,
	
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
		this._create_db_button = document.getElementById('create_db_btn');
		this._hidden = document.getElementById('isTestConnection');
		this._hiddenDb = document.getElementById('isCreateDb');
		
		this._dsnSwitcher = document.getElementById("useDSN");
		this._csSwitcher = document.getElementById("useCS");
		this._csSwitcherChild = document.getElementById("odbcConnectionString");
		
		this._allGood = (this._radioMySQL && this._radioMSSQL && this._msg &&
			this._login && this._password && this._dbname && this._dsn && this._host &&
			this._button && this._create_db_button && this._hidden && this._hiddenDb && this._csSwitcher && this._csSwitcherChild && this._dsnSwitcher);
			
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
			
			this._create_db_button.onclick = function() { 
				obj._hiddenDb.value = "1";
				return true;
			};
			
			FormButtonDisable([this._button, this._create_db_button, document.getElementById("submit_btn")]);
			
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
			o0.onclick = function() { obj.InitTest(); };
			o1.onclick = function() { obj.InitTest(); };
			o2.onclick = function() { obj.InitTest(); };
			
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
			SetDisabled(this._create_db_button, (this._csSwitcher.checked || _isDsn || _useOdbc || _allOff), true);
			SetDisabled(this._host, (this._csSwitcher.checked || _isDsn || _useOdbc || _allOff), true);
			
			this.SetInfo(_msg);
		}
	}
};

var wmCheckSocket = {
	_button: null,
	_hidden: null,
	_c1: null,
	_c2: null,
	_c3: null,

	Init: function () {
		this._c1 = document.getElementById("chSMTP");
		this._c2 = document.getElementById("chPOP3");
		this._c3 = document.getElementById("chIMAP4");
		this._button = document.getElementById("test_btn");
		this._hidden = document.getElementById('isTestConnection');
		
		FormButtonDisable([this._button]);
		
		var obj = this;
		if (this._c1 && this._c2 && this._c3) {
			this._c1.onclick = function() { return obj.CheckCh(); };
			this._c2.onclick = function() { return obj.CheckCh(); };
			this._c3.onclick = function() { return obj.CheckCh(); };
		};
		
		this._button.onclick = function() { 
			obj._hidden.value = "1";
			return true;
		};
		
		this.CheckCh();
	},
	
	CheckCh: function () {
		if (!this._c1.checked && !this._c2.checked && !this._c3.checked){
			this._button.disabled = true;
		} else {
			this._button.disabled = false;
		}
		return true;
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
}

var wmMobileSynk = {
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
		SetDisabled(document.getElementById('txtMobileSyncUrl'), !bIsChecked, true);
		SetDisabled(document.getElementById('txtMobileSyncContactDatabase'), !bIsChecked, true);
		SetDisabled(document.getElementById('txtMobileSyncCalendarDatabase'), !bIsChecked, true);
	}
}

var SettingsObjects = Array();
SettingsObjects['db'] = wmDbSettings;
SettingsObjects['socket'] = wmCheckSocket;
SettingsObjects['mobilesync'] = wmMobileSynk;