/*
 * Classes:
 *  CSettings()
 *  CAccountProperties()
 *  CFilters()
 *  CFilterProperties(idFolder, folderName) 
 *  CSignatureData()
 *  CAutoresponderData()
 *  CMobileSyncData(rootElement)
 */

function CSettings()
{
	this.Type = TYPE_USER_SETTINGS;
	this.MsgsPerPage = null;
	this.ContactsPerPage = null;
	this.AutoCheckMailInterval = null;
	this.DisableRte = null;
	this.CharsetInc = null;
	this.CharsetOut = null;
	this.TimeOffset = null;
	this.TimeFormat = null;
	this.ViewMode = null;
	this.DefSkin = null;
	this.Skins = Array();
	this.DefLang = null;
	this.Langs = Array();
}

CSettings.prototype = {
	GetStringDataKeys: function()
	{
		return '';
	},
	
	GetInXML: function ()
	{
		var attrs = '';
		if (this.MsgsPerPage != null) {
			attrs += ' msgs_per_page="' + this.MsgsPerPage + '"';
		}
		if (this.ContactsPerPage != null) {
			attrs += ' contacts_per_page="' + this.ContactsPerPage + '"';
		}
		if (this.AutoCheckMailInterval != null) {
			attrs += ' auto_checkmail_interval="' + this.AutoCheckMailInterval + '"';
		}
		if (this.DisableRte != null) {
			attrs += (this.DisableRte) ? ' allow_dhtml_editor="0"' : ' allow_dhtml_editor="1"';
		}
		if (this.CharsetInc != null) {
			attrs += ' def_charset_inc="' + this.CharsetInc + '"';
		}
		if (this.CharsetOut != null) {
			attrs += ' def_charset_out="' + this.CharsetOut + '"';
		}
		if (this.TimeOffset != null) {
			attrs += ' def_timezone="' + this.TimeOffset + '"';
		}
		if (this.TimeFormat != null) {
			attrs += ' time_format="' + this.TimeFormat + '"';
		}
		if (this.ViewMode != null) {
			attrs += ' view_mode="' + this.ViewMode + '"';
		}
		var nodes = '';
		if (this.DefSkin != null) {
			nodes += '<def_skin>' + GetCData(this.DefSkin) + '</def_skin>';
		}
		if (this.DefLang != null) {
			nodes += '<def_lang>' + GetCData(this.DefLang) + '</def_lang>';
		}
		return '<settings' + attrs + '>' + nodes + '</settings>';
	},
	
	GetFromXML: function(RootElement)
	{
		var attr, parts, def, j;
		attr = RootElement.getAttribute('msgs_per_page');if (attr) this.MsgsPerPage = attr - 0;
		attr = RootElement.getAttribute('contacts_per_page');if (attr) this.ContactsPerPage = attr - 0;
		attr = RootElement.getAttribute('auto_checkmail_interval');if (attr) this.AutoCheckMailInterval = attr - 0;
		attr = RootElement.getAttribute('allow_dhtml_editor');if (attr) this.DisableRte = (attr == 1) ? false : true;
		attr = RootElement.getAttribute('def_charset_inc');if (attr) this.CharsetInc = attr - 0;
		attr = RootElement.getAttribute('def_charset_out');if (attr) this.CharsetOut = attr - 0;
		attr = RootElement.getAttribute('def_timezone');if (attr) this.TimeOffset = attr - 0;
		attr = RootElement.getAttribute('time_format');if (attr) this.TimeFormat = attr - 0;
		attr = RootElement.getAttribute('view_mode');if (attr) this.ViewMode = attr - 0;
		var SettingsParts = RootElement.childNodes;
		var count = SettingsParts.length;
		for (var i=count-1; i>=0; i--) {
			parts = SettingsParts[i].childNodes;
			var partsCount = parts.length;
			switch (SettingsParts[i].tagName) {
				case 'skins':
					var defSkin = '';
					for (j=0; j<partsCount; j++) {
						def = false;
						var skin = '';
						attr = parts[j].getAttribute('def');
						if (attr) def = (attr == 1) ? true : false;
						var part = parts[j].childNodes;
						if (part.length > 0 && parts[j].tagName == 'skin')
							skin = part[0].nodeValue;
						if (skin.length > 0) {
							this.Skins.push(skin);
							if (def) this.DefSkin = skin;
							if (skin.toLowerCase() == 'afterlogic') defSkin = skin;
						}
					}
					if (this.DefSkin == null && defSkin.length > 0)
						this.DefSkin = defSkin;
					break;
				case 'langs':
					var defLang = '';
					for (j=0; j<partsCount; j++) {
						def = false;
						var lang = '';
						attr = parts[j].getAttribute('def');
						if (attr) def = (attr == 1) ? true : false;
						part = parts[j].childNodes;
						if (part.length > 0 && parts[j].tagName == 'lang') {
							lang = part[0].nodeValue;
						}
						if (lang.length > 0) {
							this.Langs.push(lang);
							if (def) this.DefLang = lang;
							if (lang.toLowerCase() == 'english') defLang = lang;
						}
					}
					if (this.DefLang == null && defLang.length > 0)
						this.DefLang = defLang;
					break;
			}//switch
		}//for
	}//GetFromXML
};

function CAccountProperties()
{
	this.Type = TYPE_ACCOUNT_PROPERTIES;
	this.Id = -1;

	this.DefAcct = false;
	this.DefOrder = 0;
	this.GetMailAtLogin = true;
	this.InboxSyncType = SYNC_TYPE_NEW_MSGS;
	this.Linked = false;
	this.MailIncPort = POP3_PORT;
	this.MailMode = 1;
	this.MailOutPort = SMTP_PORT;
	this.MailOutAuth = true;
	this.MailProtocol = POP3_PROTOCOL;
	this.MailsOnServerDays = 1;
	this.SignatureOpt = SIGNATURE_OPT_DONT_ADD_TO_ALL;
	this.SignatureType = SIGNATURE_TYPE_PLAIN;
	this.Size = 0;
	this.UseFriendlyNm = false;

	this.Email = '';
	this.FriendlyNm = '';
	this.MailIncHost = '';
	this.MailIncLogin = '';
	this.MailIncPass = '';
	this.MailOutHost = '';
	this.MailOutLogin = '';
	this.MailOutPass = '';
	this.Signature = '';
	
	this.Filters = null;
	this.IsInternal = false;
	
	this.SaveMail = 0;
	
}

CAccountProperties.prototype = {
	GetStringDataKeys: function()
	{
		return this.Id;
	},
	
	AplyNewAccountProperties: function (newAcctProp)
	{
		var changedDirectMode = false;
		if (this.InboxSyncType != newAcctProp.InboxSyncType && 
		    (SYNC_TYPE_DIRECT_MODE == this.InboxSyncType || SYNC_TYPE_DIRECT_MODE == newAcctProp.InboxSyncType)) {
			changedDirectMode = true;
		}

		this.DefAcct = newAcctProp.DefAcct;
		this.GetMailAtLogin = newAcctProp.GetMailAtLogin;
		this.InboxSyncType = newAcctProp.InboxSyncType;
		this.MailIncPort = newAcctProp.MailIncPort;
		this.MailMode = newAcctProp.MailMode;
		this.MailOutPort = newAcctProp.MailOutPort;
		this.MailOutAuth = newAcctProp.MailOutAuth;
		this.MailProtocol = newAcctProp.MailProtocol;
		this.MailsOnServerDays = newAcctProp.MailsOnServerDays;
		this.UseFriendlyNm = newAcctProp.UseFriendlyNm;

		this.Email = newAcctProp.Email;
		this.FriendlyNm = newAcctProp.FriendlyNm;
		this.MailIncHost = newAcctProp.MailIncHost;
		this.MailIncLogin = newAcctProp.MailIncLogin;
		this.MailIncPass = newAcctProp.MailIncPass;
		this.MailOutHost = newAcctProp.MailOutHost;
		this.MailOutLogin = newAcctProp.MailOutLogin;
		this.MailOutPass = newAcctProp.MailOutPass;

		this.IsInternal = newAcctProp.IsInternal;
		
		this.SaveMail = newAcctProp.SaveMail;
		
		return changedDirectMode;
	},

	GetInXML: function ()
	{
		var attrs = '';
		attrs += ' mail_inc_port="' + this.MailIncPort + '"';
		attrs += ' mail_out_port="' + this.MailOutPort + '"';
		if (this.Id != -1) attrs += ' id="' + this.Id + '"';
		attrs += (this.DefAcct) ? ' def_acct="1"' : ' def_acct="0"';
		attrs += ' mail_protocol="' + this.MailProtocol + '"';
		attrs += (this.MailOutAuth) ? ' mail_out_auth="1"' : ' mail_out_auth="0"';
		attrs += (this.UseFriendlyNm) ? ' use_friendly_nm="1"' : ' use_friendly_nm="0"';
		attrs += ' mails_on_server_days="' + this.MailsOnServerDays + '"';
		attrs += ' mail_mode="' + this.MailMode + '"';
		attrs += (this.GetMailAtLogin) ? ' getmail_at_login="1"' : ' getmail_at_login="0"';
		attrs += ' inbox_sync_type="' + this.InboxSyncType + '"';
		attrs += (this.IsInternal) ? ' is_internal="1"' : ' is_internal="0"';
		attrs += ' save_mail="' + this.SaveMail + '"';

		var nodes = '';
		nodes += '<friendly_nm>' + GetCData(this.FriendlyNm) + '</friendly_nm>';
		nodes += '<mail_out_host>' + GetCData(this.MailOutHost) + '</mail_out_host>';
		nodes += '<mail_out_login>' + GetCData(this.MailOutLogin) + '</mail_out_login>';
		nodes += '<mail_out_pass>' + GetCData(this.MailOutPass) + '</mail_out_pass>';
		nodes += '<email>' + GetCData(this.Email) + '</email>';
		nodes += '<mail_inc_host>' + GetCData(this.MailIncHost) + '</mail_inc_host>';
		nodes += '<mail_inc_login>' + GetCData(this.MailIncLogin) + '</mail_inc_login>';
		nodes += '<mail_inc_pass>' + GetCData(this.MailIncPass) + '</mail_inc_pass>';

		var xml = '<account' + attrs + '>' + nodes + '</account>';
		return xml;
	},

	GetFromXML: function (RootElement)
	{
		var attr;
		attr = RootElement.getAttribute('id');if (attr) this.Id = attr - 0;
		attr = RootElement.getAttribute('def_acct');if (attr) this.DefAcct = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('def_order');if (attr) this.DefOrder = attr - 0;
		attr = RootElement.getAttribute('getmail_at_login');if (attr) this.GetMailAtLogin = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('inbox_sync_type');if (attr) this.InboxSyncType = attr - 0;
		attr = RootElement.getAttribute('linked');if (attr) this.Linked = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('mail_inc_port');if (attr) this.MailIncPort = attr - 0;
		attr = RootElement.getAttribute('mail_mode');if (attr) this.MailMode = attr - 0;
		attr = RootElement.getAttribute('mail_out_port');if (attr) this.MailOutPort = attr - 0;
		attr = RootElement.getAttribute('mail_out_auth');if (attr) this.MailOutAuth = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('mail_protocol');if (attr) this.MailProtocol = attr - 0;
		attr = RootElement.getAttribute('mails_on_server_days');if (attr) this.MailsOnServerDays = attr - 0;
		attr = RootElement.getAttribute('signature_opt');if (attr) this.SignatureOpt = attr - 0;
		attr = RootElement.getAttribute('signature_type');if (attr) this.SignatureType = attr - 0;
		attr = RootElement.getAttribute('size');if (attr) this.Size = attr - 0;
		attr = RootElement.getAttribute('use_friendly_nm');if (attr) this.UseFriendlyNm = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('is_internal');if (attr) this.IsInternal = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('save_mail');if (attr) this.SaveMail = attr - 0;

		var SettingsParts = RootElement.childNodes;
		var count = SettingsParts.length;
		for (var i=count-1; i>=0; i--) {
			var parts = SettingsParts[i].childNodes;
			var partsCount = parts.length;
			if (partsCount > 0) {
				switch (SettingsParts[i].tagName) {
					case 'email':
						this.Email = parts[0].nodeValue;
						break;
					case 'friendly_name':
						this.FriendlyNm = parts[0].nodeValue;
						break;
					case 'mail_inc_host':
						this.MailIncHost = parts[0].nodeValue;
						break;
					case 'mail_inc_login':
						this.MailIncLogin = parts[0].nodeValue;
						break;
					case 'mail_inc_pass':
						this.MailIncPass = parts[0].nodeValue;
						break;
					case 'mail_out_host':
						this.MailOutHost = parts[0].nodeValue;
						break;
					case 'mail_out_login':
						this.MailOutLogin = parts[0].nodeValue;
						break;
					case 'mail_out_pass':
						this.MailOutPass = parts[0].nodeValue;
						break;
					case 'signature':
						this.Signature = HtmlDecode(parts[0].nodeValue);
						break;
				}//switch
			}
		}//for
	},//GetFromXML
	
	Copy: function (acctProp)
	{
	    this.Id = acctProp.Id;
	    this.DefAcct = acctProp.DefAcct;
	    this.MailProtocol = acctProp.MailProtocol;
	    this.MailIncPort = acctProp.MailIncPort;
	    this.MailOutPort = acctProp.MailOutPort;
	    this.MailOutAuth = acctProp.MailOutAuth;
	    this.UseFriendlyNm = acctProp.UseFriendlyNm;
	    this.MailsOnServerDays = acctProp.MailsOnServerDays;
	    this.MailMode = acctProp.MailMode;
	    this.GetMailAtLogin = acctProp.GetMailAtLogin;
	    this.InboxSyncType = acctProp.InboxSyncType;
	    this.FriendlyNm = acctProp.FriendlyNm;
	    this.Email = acctProp.Email;
	    this.MailIncHost = acctProp.MailIncHost;
	    this.MailIncLogin = acctProp.MailIncLogin;
	    this.MailIncPass = acctProp.MailIncPass;
	    this.MailOutHost = acctProp.MailOutHost;
	    this.MailOutLogin = acctProp.MailOutLogin;
	    this.MailOutPass = acctProp.MailOutPass;
	    this.Linked = acctProp.Linked;
		this.IsInternal = acctProp.IsInternal;
	}
};

function CFilters() {
	this.Type = TYPE_FILTERS;
	this.Id = -1;
	this.Items = Array();
}

CFilters.prototype = {
	GetStringDataKeys: function()
	{
		return '';
	},

	GetFromXML: function(RootElement)
	{
		var attr = RootElement.getAttribute('id_acct');if (attr) this.Id = attr - 0;
		var filters = RootElement.childNodes;
		var iCount = filters.length;
		for (var i=0; i<iCount; i++) {
			var filterProp = new CFilterProperties();
			filterProp.GetFromXML(filters[i]);
			this.Items.push(filterProp);
		}
	}
};

function CFilterProperties(idFolder, folderName) {
	this.Type = TYPE_FILTER_PROPERTIES;
	this.Id = -1;
	this.Field = 0;
	this.Condition = 0;
	this.Action = 3;
	this.IdFolder = -1;
	if (idFolder != undefined) {
		this.IdFolder = idFolder;
	}
	this.FolderName = '';
	if (folderName != undefined) {
		this.FolderName = folderName;
	}
	this.Value = '';
	this.Status = FILTER_STATUS_NEW;
	this.Applied = true;
}

CFilterProperties.prototype = {
	GetStringDataKeys: function()
	{
		return this.Id;
	},
	
	GetInXML: function ()
	{
		if (this.Status == FILTER_STATUS_REMOVED && this.Id == -1) {
			return '';
		}
	
		var attrs = '';
		var value = '';
		if (this.Status != FILTER_STATUS_NEW) {
			attrs += ' id="' + this.Id + '"';
		}
		attrs += ' status="' + this.Status + '"';
		if (this.Status != FILTER_STATUS_REMOVED) {
			attrs += ' field="' + this.Field + '"';
			attrs += ' condition="' + this.Condition + '"';
			attrs += ' action="' + this.Action + '"';
			attrs += ' id_folder="' + this.IdFolder + '"';
			attrs += ' applied="' + (this.Applied ? '1' : '0') + '"';
			value = GetCData(this.Value);
		}

		var xml = '<filter' + attrs + '>' + value + '</filter>';
		return xml;
	},

	GetFromXML: function (RootElement)
	{
		var attr;
		attr = RootElement.getAttribute('id');if (attr) this.Id = attr - 0;
		attr = RootElement.getAttribute('field');if (attr) this.Field = attr - 0;
		attr = RootElement.getAttribute('condition');if (attr) this.Condition = attr - 0;
		attr = RootElement.getAttribute('action');if (attr) this.Action = attr - 0;
		attr = RootElement.getAttribute('id_folder');if (attr) this.IdFolder = attr - 0;
		attr = RootElement.getAttribute('applied');if (attr) this.Applied = (attr == 1) ? true : false;
		var filterNodes = RootElement.childNodes;
		if (filterNodes.length > 0) {
			this.Value = filterNodes[0].nodeValue;
		}
		this.Status = FILTER_STATUS_UNCHANGED;
	}
};

function CSignatureData() {
	this.Type = TYPE_SIGNATURE;
	this.IdAcct = -1;
	this.isHtml = false;
	this.Opt = 0;
	this.Value = '';
}

CSignatureData.prototype = {
	GetStringDataKeys: function()
	{
		return '';
	},

	GetInXML: function ()
	{
		var attrs = (this.isHtml) ? ' type="1"' : ' type="0"';
		attrs += ' opt="' + this.Opt + '"';
		var xml = '<param name="id_acct" value="' + this.IdAcct + '"/>';
		xml += '<signature' + attrs + '>' + GetCData(this.Value) + '</signature>';
		return xml;
	}
};

function CAutoresponderData() {
	this.Type = TYPE_AUTORESPONDER;
	this.IdAcct = -1;
	this.Enable = false;
	this.Subject = '';
	this.Message = '';
}

CAutoresponderData.prototype = {
	GetStringDataKeys: function()
	{
		return '';
	},

	GetInXML: function ()
	{
		var attrs = (this.Enable) ? ' enable="1"' : ' type="0"';
		var nodes = '<subject>' + GetCData(this.Subject) + '</subject>';
		nodes += '<message>' + GetCData(this.Message) + '</message>';
		var xml = '<param name="id_acct" value="' + this.IdAcct + '"/>';
		xml += '<autoresponder' + attrs + '>' + nodes + '</autoresponder>';
		return xml;
	},
	
	GetFromXML: function (RootElement)
	{
		var attr, parts;
		attr = RootElement.getAttribute('id_acct');if (attr) this.IdAcct = attr - 0;
		attr = RootElement.getAttribute('enable');if (attr) this.Enable = (attr == '1') ? true : false;
		var autoresponderNodes = RootElement.childNodes;
		for (var i=0; i<autoresponderNodes.length; i++) {
			switch (autoresponderNodes[i].tagName) {
				case 'subject':
					parts = autoresponderNodes[i].childNodes;
					if (parts.length > 0) this.Subject = HtmlDecode(Trim(parts[0].nodeValue));
					break;
				case 'message':
					parts = autoresponderNodes[i].childNodes;
					if (parts.length > 0) this.Message = HtmlDecode(Trim(parts[0].nodeValue));
					break;
			}
		}
	}
};

function CMobileSyncData(rootElement)
{
	this.Type = TYPE_MOBILE_SYNC;
	this.Url = '';
	this.Login = '';
	this.ContactDataBase = '';
	this.CalendarDataBase = '';
	this.EditEnable = false;
	this.UserEnable = false;
    if (rootElement != undefined) {
        this.GetFromXML(rootElement);
    }
}

CMobileSyncData.prototype = {
	GetStringDataKeys: function ()
	{
		return '';
	},

    Copy: function (mobileSync)
    {
        this.Url = mobileSync.Url;
        this.Login = mobileSync.Login;
        this.ContactDataBase = mobileSync.ContactDataBase;
        this.CalendarDataBase = mobileSync.CalendarDataBase;
        this.EditEnable = mobileSync.EditEnable;
        this.UserEnable = mobileSync.UserEnable;
    },

	GetInXML: function ()
	{
		var attrs = (this.UserEnable) ? ' enable_account="1"' : ' enable_account="0"';
		return '<mobile_sync' + attrs + '></mobile_sync>';
	},

	GetFromXML: function(rootElement)
	{
		var attr = rootElement.getAttribute('enable_system');
        if (attr) this.EditEnable = attr - 0;
		attr = rootElement.getAttribute('enable_account');
        if (attr) this.UserEnable = attr - 0;
		var SettingsParts = rootElement.childNodes;
		var count = SettingsParts.length;
		for (var i = count - 1; i >= 0; i--) {
			var parts = SettingsParts[i].childNodes;
			switch (SettingsParts[i].tagName) {
				case 'url':
					this.Url = parts[0].nodeValue;
					break;
				case 'contact_db':
					this.ContactDataBase = parts[0].nodeValue;
					break;
				case 'calendar_db':
					this.CalendarDataBase = parts[0].nodeValue;
					break;
				case 'login':
					this.Login = parts[0].nodeValue;
					break;
			}
		}
	}//GetFromXML
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}