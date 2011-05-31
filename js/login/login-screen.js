/*
 * Functions:
 *  LoadWebMailScript()
 *  LoginErrorHandler()
 *  LoginHandler()
 *  TryLoginHandler()
 *  SetCheckingAccountHandler(accountName)
 *  SetStateTextHandler(text)
 *  SetCheckingFolderHandler(folder, count)
 *  SetRetrievingMessageHandler(number)
 *  SetDeletingMessageHandler(number)
 *  SetUpdatedFolders()
 *  EndCheckMailHandler(error)
 *  CheckEndCheckMailHandler()
 * Classes:
 *  CLoginScreen(submitHandler)
 *  CLoginDemoLangClass()
 * Functions:
 *  Init()
 */

var WebMail;
var LoginScreen, LoginDemoLangClass;
var infoObj, infoMessage;
var Browser;
var NetLoader;
var checkMail;

var timerlen = 50;
var slideAniLen = 500;

var timerID = [];
var startTime = [];
var obj = [];
var endHeight = [];
var moving = [];
var dir = [];

var WebMailScripts = [
	//'js/common/defines.js', // already loaded; first for load!
	'js/common/calendar-screen.js',
	'js/common/common-handlers.js',
	//'js/common/common-helpers.js', // already loaded
	'js/common/data-source.js',
	//'js/common/functions.js', // already loaded
	//'js/common/loaders.js', // already loaded
	'js/common/page-switcher.js',
	//'js/common/popups.js', // already loaded
	'js/common/toolbar.js',
	'js/common/variable-table.js',
	'js/common/webmail.js',
	
	'js/mail/autocomplete-recipients.js',
	'js/mail/folders-pane.js',
	'js/mail/html-editor.js',
	'js/mail/mail-data.js',
	//'js/mail/mail-handlers.js', // can't load because it override functions to work with CheckMail
	'js/mail/message-headers.js',
	'js/mail/message-info.js',
	'js/mail/message-line.js',
	'js/mail/message-list-prototype.js',
	'js/mail/message-list-central-pane.js',
	'js/mail/message-list-central-screen.js', // need to load after message-list-prototype.js
	'js/mail/message-list-display.js',
	'js/mail/message-list-top-screen.js', // need to load after message-list-prototype.js
	'js/mail/new-message-screen.js',
	'js/mail/message-reply-pane.js', // need to load after new-message-screen.js
	'js/mail/resizers.js',
	'js/mail/swfupload.js',
	'js/mail/view-message-screen.js',

	'js/contacts/contact-line.js',
	'js/contacts/contacts-data.js',
	'js/contacts/contacts-handlers.js',
	'js/contacts/contacts-screen.js',
	'js/contacts/edit-contact.js',
	'js/contacts/edit-group.js',
	'js/contacts/import.js',
	'js/contacts/view-contact.js',
	
	'js/settings/account-list.js',
	'js/settings/account-properties.js',
	'js/settings/autoresponder.js',
	'js/settings/calendar.js',
	'js/settings/common.js',
	'js/settings/defines-calendar.js',
	'js/settings/filters.js',
	'js/settings/folders.js',
	'js/settings/mobile-sync.js',
	'js/settings/settings-data.js',
	'js/settings/signature.js',
	'js/settings/user-settings-screen.js'
];
var ScriptToLoadIndex = 0;
var ScriptLoader = new CScriptLoader();

function LoadWebMailScript()
{
	if (ScriptToLoadIndex >= WebMailScripts.length) {
		return;
	}
	ScriptLoader.Load([WebMailScripts[ScriptToLoadIndex]], LoadWebMailScript);
	ScriptToLoadIndex++;
}

function LoginErrorHandler()
{
	infoObj.Hide();
	LoginScreen.ShowError(this.ErrorDesc);
	LoginScreen.ReloadCaptcha();
}

function LoginHandler()
{
	var XmlDoc, RootElement, ErrorTag, ErrorDesc, Objects, iCount, i, j, attr, hashParts, jCount, part, infoDiv;
	var hash, id;
	infoObj.Hide();
	XmlDoc = this.responseXML;
	if (XmlDoc && XmlDoc.documentElement && typeof(XmlDoc) == 'object' && typeof(XmlDoc.documentElement) == 'object') {
		RootElement = XmlDoc.documentElement;
		if (RootElement && RootElement.tagName == 'webmail') {
			ErrorTag = RootElement.getElementsByTagName('error')[0];
			
			if (ErrorTag) {
				ErrorDesc = ErrorTag.childNodes[0].nodeValue;
				LoginScreen.ShowError((ErrorDesc && ErrorDesc.length > 0) ? ErrorDesc : Lang.ErrorWithoutDesc);
				CaptchaTag = RootElement.getElementsByTagName('captcha')[0];
				if (CaptchaTag && CaptchaTag.childNodes[0].nodeValue == '1') {
					LoginScreen.ShowCaptcha();
				}
				LoginScreen.ReloadCaptcha();
			} else {
				Objects = RootElement.childNodes;
				if (Objects.length == 0) {
					LoginScreen.ShowError(Lang.ErrorEmptyXmlPacket);
					LoginScreen.ReloadCaptcha();
				} else {
					iCount = Objects.length;
					for (i = iCount - 1; i >= 0; i--) {
						if (Objects[i].tagName == 'login') {
							hash = '';
							id = -1;
							attr = Objects[i].getAttribute('id_acct');
							if (attr) {
								id = attr - 0;
							}
							hashParts = Objects[i].childNodes;
							jCount = hashParts.length;
							for (j = jCount - 1; j >= 0; j--) {
								part = hashParts[j].childNodes;
								if (part.length > 0 && hashParts[j].tagName == 'hash') {
									hash = part[0].nodeValue;
								}
							}
							if (id != -1 && hash != '') {
								CreateCookie('awm_autologin_data', hash, 14);
								CreateCookie('awm_autologin_id', id, 14);
							}
							checkMail.Start();
							infoDiv = document.getElementById('demo_info');
							if (infoDiv) { infoDiv.className = 'wm_hide'; }
						}
					}
				}
			}//if (ErrorTag)
		}
		else {
			LoginScreen.ShowError(Lang.ErrorParsing + '<br/>Error code 2.<br/>' + Lang.ResponseText + '<br/>' + this.responseText);
			LoginScreen.ReloadCaptcha();
		}//if (RootElement)
	}
	else {
		LoginScreen.ShowError(Lang.ErrorParsing + '<br/>Error code 1.<br/>' + Lang.ResponseText + '<br/>' + this.responseText);
		LoginScreen.ReloadCaptcha();
	}//if (XmlDoc)
}

function TryLoginHandler()
{
	infoMessage.innerHTML = Lang.Loading;
	infoObj.Show();
	infoObj.Resize();
	NetLoader.LoadXMLDoc(ActionUrl, 'xml=' + encodeURIComponent(this.Xml), LoginHandler, LoginErrorHandler);
}

function SetCheckingAccountHandler(accountName)
{
	LoginScreen.Hide();
	checkMail.SetAccount(accountName);
}

function SetStateTextHandler(text)
{
	checkMail.SetText(text);
}

function SetCheckingFolderHandler(folder, count)
{
	checkMail.SetFolder(folder, count);
}

function SetRetrievingMessageHandler(number)
{
	checkMail.SetMsgNumber(number);
}

function SetDeletingMessageHandler(number)
{
	checkMail.DeleteMsg(number);
}

function SetUpdatedFolders() {}

function EndCheckMailHandler(error)
{
	document.location = (error == 'session_error') ? LoginUrl + '?error=1' : WebMailUrl;
}

function CheckEndCheckMailHandler() {
	if (checkMail.started) {
		document.location = WebMailUrl;
	}
}

function CLoginScreen(submitHandler)
{
	this.isBuilded = true;
	this.Tip = new CTip();
    this.onSubmit = submitHandler;
	this._langsIsShown = false;
	this.LangChanger = null;

	this._container = document.getElementById('login_screen');
	this._loginErrorCont = document.getElementById('login_error');
	this._loginErrorMess = document.getElementById('login_error_message');
	
	this._mode = (AdvancedLogin == 1) ? 'advanced' : 'standard';

	this._incoming = document.getElementById('incoming');
	this._incProtocol = document.getElementById('inc_protocol');
	this._outgoing = document.getElementById('outgoing');
	this._authentication = document.getElementById('authentication');
	this.LoginForm = document.getElementById('login_form');
	this._loginTable = document.getElementById('login_table');
	this._email = document.getElementById('email');
	this._emailCont = document.getElementById('email_cont');
	this._login = document.getElementById('login');
	this._loginCont = document.getElementById('login_cont');
	this._loginParent = document.getElementById('login_parent');
	this._domain = document.getElementById('domain');
	this._password = document.getElementById('password');
	this._incServer = document.getElementById('inc_server');
	this._incPort = document.getElementById('inc_port');
	this._outServer = document.getElementById('out_server');
	this._outPort = document.getElementById('out_port');
	this._smtpAuth = document.getElementById('smtp_auth');
	this._signMe = document.getElementById('sign_me');
	this._language = document.getElementById('language');
	this._body = document.getElementById('mbody');
	this._captchaContent = document.getElementById('captcha_content');
	this._captcha = document.getElementById('captcha');
	this._captchaImg = document.getElementById('captcha_img');
	this._captchaReloadLink = document.getElementById('lang_CaptchaReloadLink');
	this._langs_collection = document.getElementById("langs_collection");
	this._langs_selected = document.getElementById("langs_selected");
	this._reg_link = document.getElementById("reg_link_id");
	this._reset_link = document.getElementById("reset_link_id");

	this.Init();
	this.MakeView();
}

CLoginScreen.prototype = {
	Init: function ()
	{
		var obj = this;
		
		/* email */
		this._email.onfocus = function () {
			this.className = 'wm_input_focus';
			obj.EmailFocus();
		};
		this._email.onkeypress = function (ev) {
			if (!isEnter(ev)) {
				obj.Tip.Hide('email');
			}
		};
		/* login */
		this._login.onfocus = function () {
			this.className = 'wm_input_focus';
			obj.LoginFocus();
		};
		this._login.onkeypress = function (ev) {
			if (!isEnter(ev)) {
				obj.Tip.Hide('login');
			}
		};
		
		/* password */
		this._password.onfocus = function () {
			this.className = 'wm_input_focus wm_password_input';
			obj.PasswordFocus();
		};
		this._password.onkeypress = function (ev) {
			if (!isEnter(ev)) {
				obj.Tip.Hide('password');
			}
		};

		this._body.onclick = function (event) { 
			obj.ShowLangs(event);
		};

		this.AjaxInit();

		if (this._captchaImg) {
			this._captchaImg.onclick = function () {
				obj.ReloadCaptcha();
			};

			if (this._captchaReloadLink) {
				this._captchaReloadLink.onclick = function () {
					obj.ReloadCaptcha();
					return false;
				};
			}
		}
		
		if (this._incServer == null) { 
			return;
		}
		
		/* incoming mail */
		this._incServer.onkeypress = function (ev) {
			if (!isEnter(ev)) {
				obj.Tip.Hide('inc_server');
			}
		};
		this._incPort.onkeypress = function (ev) {
			if (!isEnter(ev)) {
				obj.Tip.Hide('inc_port');
			}
		};
		this._incProtocol.onchange = function () {
			obj._incPort.value = (this.value == IMAP4_PROTOCOL) ? IMAP4_PORT : POP3_PORT;
		};
		/* ougoing mail */
		this._outServer.onkeypress = function (ev) {
			if (!isEnter(ev)) {
				obj.Tip.Hide('out_server');
			}
		};
		this._outPort.onkeypress = function (ev) {
			if (!isEnter(ev)) {
				obj.Tip.Hide('out_port');
			}
		};

		return true;
	},

	ShowCaptcha: function () {
		if (this._captchaContent) {
			this._captchaContent.className = '';
		}
	},

	ReloadCaptcha: function () {
		if (this._captchaImg) {
			this._captchaImg.src = this._captchaImg.src + '&c' + Math.round(Math.random() * 1000);
			if (this._captcha) {
				this._captcha.value= '';
			}
		}
	},

	ShowLangs: function (e) {
		if (this._langs_collection) {
			e = e ? e : window.event;
			var tgt = window.event ? window.event.srcElement : e.target;
			if (tgt && tgt.parentNode && tgt.parentNode.id == 'langs_selected') {
				if (this._langsIsShown) {
					this._langs_collection.style.display = "none";
					this._langsIsShown = false;
				} else {
					this._langs_collection.style.display = "block";
					this._langsIsShown = true;
				}
			} else {
				this._langs_collection.style.display = "none";
				this._langsIsShown = false;
			}
		}
		return false;
	},

	ChangeLang: function (object) {
		var obj, isRtl;
		if (null == this._language) {
			return;
		}
		obj = this;
		if (object && object.name && object.name.length > 4 && object.name.substr(0, 4) == 'lng_') {
			isRtl = IsRtlLanguage(object.name.substr(4));
			if (window.RTL && !isRtl || !window.RTL && isRtl) {
				document.location = LoginUrl + '?lang=' + object.name.substr(4);
			} 
			else
			{
				this._language.value = object.name.substr(4);
				document.getElementById('langs_selected').innerHTML = '<span>' + object.innerHTML + '</span><font>&nbsp;</font><span class="wm_login_lang_switcher">&nbsp;</span>';
				CreateCookie('awm_defLang', object.name.substr(4), 635);
				ScriptLoader.Load([LanguageUrl + '?v=' + WmVersion + '&lang=' + object.name.substr(4)], function()
				{
				    obj.ChangeLangProcess.call(obj);
				});
			}
		}
	},

	ChangeLangProcess: function () {
		/*var obj = this;
		ScriptLoader.Load(['js/common/defines-lang.js'], function () {
			obj.LangChanger.Go();
		});*/
		this.LangChanger.Go();
		if (this._loginModeSwitcher) {
			this._loginModeSwitcher.innerHTML = (this._mode == 'standard') ? Lang.AdvancedLogin : Lang.StandardLogin;
		}
	},

	InitLangs: function () {
		var langObj = document.getElementById('lang_LoginInfo');
		this.LangChanger.Register('innerHTML', langObj, 'LANG_LoginInfo', '');

		langObj = document.getElementById('lang_Email');
		this.LangChanger.Register('innerHTML', langObj, 'LANG_Email', ':');

		langObj = document.getElementById('lang_Login');
		this.LangChanger.Register('innerHTML', langObj, 'LANG_Login', ':');

		langObj = document.getElementById('lang_Password');
		this.LangChanger.Register('innerHTML', langObj, 'LANG_Password', ':');

		langObj = document.getElementById('lang_Captcha');
		this.LangChanger.Register('innerHTML', langObj, 'Captcha', ':');

		langObj = document.getElementById('lang_CaptchaReloadLink');
		this.LangChanger.Register('innerHTML', langObj, 'CaptchaReloadLink', '');

		langObj = document.getElementById('lang_IncServer');
		this.LangChanger.Register('innerHTML', langObj, 'LANG_IncServer', ':');

		langObj = document.getElementById('lang_IncPort');
		this.LangChanger.Register('innerHTML', langObj, 'LANG_IncPort', ':');

		langObj = document.getElementById('lang_OutServer');
		this.LangChanger.Register('innerHTML', langObj, 'LANG_OutServer', ':');

		langObj = document.getElementById('lang_OutPort');
		this.LangChanger.Register('innerHTML', langObj, 'LANG_OutPort', ':');

		langObj = document.getElementById('lang_UseSmtpAuth');
		this.LangChanger.Register('innerHTML', langObj, 'LANG_UseSmtpAuth', '');

		langObj = document.getElementById('lang_SignMe');
		this.LangChanger.Register('innerHTML', langObj, 'LANG_SignMe', '');

		langObj = document.getElementById('submit');
		this.LangChanger.Register('value', langObj, 'LANG_Enter', '');

		langObj = document.getElementById('reset_link_id');
		this.LangChanger.Register('innerHTML', langObj, 'IndexResetLink', '');
		
		langObj = document.getElementById('reg_link_id');
		this.LangChanger.Register('innerHTML', langObj, 'IndexRegLink', '');
	},
	
	MakeView: function ()
	{
		var isAdvancedMode = this._mode == 'advanced' || AdvancedLogin == '1';
		if (isAdvancedMode) {
			this._emailCont.className = '';
			this._email.tabIndex = 1;
			this._loginCont.className = '';
			this._login.tabIndex = 2;
			this._email.focus();
		}
		else if (HideLoginMode >= 20) {
			this.Tip.Hide('email');
			this._emailCont.className = 'wm_hide';
			this._email.tabIndex = -1;
			this._loginCont.className = '';
			this._login.tabIndex = 2;
			this._login.focus();
		}
		else if (HideLoginMode >= 10) {
			this.Tip.Hide('login');
			this._emailCont.className = '';
			this._email.tabIndex = 1;
			this._loginCont.className = 'wm_hide';
			this._login.tabIndex = -1;
			this._email.focus();
		} else {
			this._email.focus();
		}
		if (isAdvancedMode || HideLoginMode != 21 && HideLoginMode != 23) {
			this._login.style.width = '224px';
			this._domain.innerHTML = '';
		} else {
			this._login.style.width = '120px';
			this._domain.innerHTML = '@' + DomainOptional;
		}
	},
	
	EmailFocus: function ()
	{
		this._email.select();
	},

	LoginFocus: function ()
	{
		if (this._login.value.length == 0 && this._email.value.length != 0) {
			this._login.value = this._email.value;
		}
		this._login.select();
	},

	PasswordFocus: function ()
	{
		this._password.select();
	},

	CheckLoginForm: function ()
	{
		var isAdvancedMode, vEmail, vLogin, vPassword, vIncServer, vIncPort, vOutServer, vOutPort;
		this.Tip.Hide('');
		isAdvancedMode = this._mode == 'advanced' || AdvancedLogin == '1';
		/* email */
		vEmail = Trim(this._email.value);
		if (Validator.IsEmpty(vEmail) && (isAdvancedMode || HideLoginMode < 20)) {
			this.Tip.Show(Lang.WarningEmailBlank, this._email, 'email');
			return false;
		}
		if (!Validator.IsCorrectEmail(vEmail) && (isAdvancedMode || HideLoginMode < 20)) {
			this.Tip.Show(Lang.WarningCorrectEmail, this._email, 'email');
			return false;
		}
		/* login */
		vLogin = Trim(this._login.value);
		if (Validator.IsEmpty(vLogin) && (isAdvancedMode || HideLoginMode != 10 && HideLoginMode != 11)) {
			this.Tip.Show(Lang.WarningLoginBlank, this._login, 'login');
			return false;
		}
		/* password */
		vPassword = Trim(this._password.value);
		if (Validator.IsEmpty(vPassword)) {
			this.Tip.Show(Lang.WarningPassBlank, this._password, 'password');
			return false;
		}
		if (this._incServer == null) {
			return true;
		}
		
		/* incoming mail */
		vIncServer = Trim(this._incServer.value);
		if (Validator.IsEmpty(vIncServer) && (isAdvancedMode)) {
			this.Tip.Show(Lang.WarningIncServerBlank, this._incPort, 'inc_server');
			return false;
		}
		if (!Validator.IsCorrectServerName(vIncServer) && (isAdvancedMode)) {
			this.Tip.Show(Lang.WarningCorrectIncServer, this._incPort, 'inc_server');
			return false;
		}
		vIncPort = Trim(this._incPort.value);
		if (Validator.IsEmpty(vIncPort) && (isAdvancedMode)) {
			this.Tip.Show(Lang.WarningIncPortBlank, this._incPort, 'inc_port');
			return false;
		}
		else if (!Validator.IsPort(vIncPort) && (isAdvancedMode)) {
			this.Tip.Show(Lang.WarningIncPortNumber + '<br />' + Lang.DefaultIncPortNumber, this._incPort, 'inc_port');
			return false;
		}
		/* outgoing mail */
		vOutServer = Trim(this._outServer.value);
		if (Validator.IsEmpty(vOutServer) && (isAdvancedMode)) {
			this.Tip.Show(Lang.WarningOutServerBlank, this._outPort, 'out_server');
			return false;
		}
		if (!Validator.IsCorrectServerName(vOutServer) && (isAdvancedMode)) {
			this.Tip.Show(Lang.WarningCorrectSMTPServer, this._outPort, 'out_server');
			return false;
		}
		vOutPort = Trim(this._outPort.value);
		if (Validator.IsEmpty(vOutPort) && (isAdvancedMode)) {
			this.Tip.Show(Lang.WarningOutPortBlank, this._outPort, 'out_port');
			return false;
		}
		if (!Validator.IsPort(vOutPort) && (isAdvancedMode)) {
			this.Tip.Show(Lang.WarningOutPortNumber + '<br />' + Lang.DefaultOutPortNumber, this._outPort, 'out_port');
			return false;
		}
		return true;
	},
	
	SendLoginForm: function ()
	{
		if (!this.CheckLoginForm()) {
			return;
		}
		var incServer, incPort, incProtocol, outServer, outPort, outAuth, signMe, xml, lang, getRequestParams;
		incServer = 'localhost';
		incPort = '110';
		incProtocol = '0';
		outServer = 'localhost';
		outPort = '25';
		outAuth = '0';
		signMe = this._signMe.checked ? '1' : '0';
		if (this._incServer != null) {
			incServer = this._incServer.value;
			incPort = this._incPort.value;
			incProtocol = this._incProtocol.value;
			outServer = this._outServer.value;
			outPort = this._outPort.value;
			outAuth = this._smtpAuth.checked ? '1' : '0';
		}
		this.HideError();
		this.AdvancedLogin = (this._mode == 'advanced') ? '1' : '0';
		xml = '<param name="action" value="login" /><param name="request" value="" />';
		xml += '<param name="email">' + GetCData(this._email.value) + '</param>';
		xml += '<param name="mail_inc_login">' + GetCData(this._login.value) + '</param>';
		xml += '<param name="mail_inc_pass">' + GetCData(this._password.value) + '</param>';
		xml += '<param name="mail_inc_host">' + GetCData(incServer) + '</param>';
		xml += '<param name="mail_inc_port" value="' + incPort + '"/>';
		xml += '<param name="mail_protocol" value="' + incProtocol + '"/>';
		xml += '<param name="mail_out_host">' + GetCData(outServer) + '</param>';
		xml += '<param name="mail_out_port" value="' + outPort + '"/>';
		xml += '<param name="mail_out_auth" value="' + outAuth + '"/>';
		xml += '<param name="sign_me" value="' + signMe + '"/>';

		var _domainSelect = document.getElementById('domainSelect');
		if (_domainSelect) {
			xml += '<param name="domain_name">' + GetCData(_domainSelect.value) + '</param>';
		}

		if (this._captcha) {
			xml += '<param name="captcha" value="' + this._captcha.value + '"/>';
		}
		
		if (window.UseDb === false) {
			var d = new Date();
			var js_timeoffset = -d.getTimezoneOffset();
			xml += '<param name="js_timeoffset" value="' + js_timeoffset + '"/>';	
		}
		
		if (this._language == null) {
			xml += '<param name="language">' + GetCData('') + '</param>';
		}
		else {
			lang = this._language.value;
			if (lang.length == 0) {
				getRequestParams = ParseGetParams();
				if (getRequestParams.lang && getRequestParams.lang.length > 0) {
					lang = getRequestParams.lang;
				}
			}
			xml += '<param name="language">' + GetCData(lang) + '</param>';
		}
		xml += '<param name="advanced_login" value="' + this.AdvancedLogin + '"/>';
		this.Xml = '<?xml version="1.0" encoding="utf-8"?><webmail>' + xml + '</webmail>';
		if (Browser.IE) {
			this._email.blur();
			this._login.blur();
			this._password.blur();
			if (this._incServer != null) {
				this._incServer.blur();
				this._incPort.blur();
				this._outServer.blur();
				this._outPort.blur();
			}
		}
		this.onSubmit.call(this);
	},
	
	ChangeMode: function ()
	{
		if (this._incServer == null) {
			return;
		}
		this.Tip.Hide('');
		if (this._mode == 'standard') {
			this._mode = 'advanced';
			this._incProtocol.className = 'wm_advanced_input';
			this._loginModeSwitcher.innerHTML = Lang.StandardLogin;
		}
		else {
			this._mode = 'standard';
			this._incProtocol.className = 'wm_hide';
			this._loginModeSwitcher.innerHTML = Lang.AdvancedLogin;
		}
		this.MakeView();
	},
	
	SlideIt: function (objname)
	{
		if (moving[objname]) {
			return;
		}
		moving[objname] = true;
		dir[objname] = (dir[objname] == 'down') ? 'up' : 'down';
		obj[objname] = document.getElementById(objname);
		endHeight[objname] = parseInt(obj[objname].style.height, 10);
		startTime[objname] = (new Date()).getTime();
		if (dir[objname] == 'down') {
			obj[objname].style.height = '1px';
		}
		obj[objname].style.display = 'block';
		if (dir[objname] == 'down') {
			this.ChangeMode();
		}
		timerID[objname] = setInterval('LoginScreen.SlideTick(\'' + objname + '\')', timerlen);
	},
	
	SlideTick: function (objname) {
		var elapsed, d;
		elapsed = (new Date()).getTime() - startTime[objname];
		if (elapsed > slideAniLen) {
			clearInterval(timerID[objname]);
			if (dir[objname] == 'up') {
				obj[objname].style.display = 'none';
				this.ChangeMode();
			}
			obj[objname].style.height = endHeight[objname] + 'px';
			delete(moving[objname]);
			delete(timerID[objname]);
			delete(startTime[objname]);
			delete(endHeight[objname]);
			delete(obj[objname]);
		} else {
			d = Math.round(elapsed / slideAniLen * endHeight[objname]);
			if (dir[objname] == 'up') {
				d = endHeight[objname] - d;
			} else {
				d = endHeight[objname] * Math.sin((d / endHeight[objname]) * Math.PI / 2);
			}
			obj[objname].style.height = d + 'px';
		}
	},
	
	AjaxInit: function ()
	{
		var obj, submit, ahrefs, name, rname, i;
		obj = this;
		if (AllowAdvancedLogin) {
			this._loginModeSwitcher = document.getElementById('login_mode_switcher');
			this._loginModeSwitcher.href = '#';
			dir.advanced_fields = (AdvancedLogin == 0) ? 'up' : 'down';
			this._loginModeSwitcher.onclick = function () {
				obj.SlideIt('advanced_fields');
				return false;
			};
		}
		this.LoginForm.onsubmit = function () {
			return false; 
		};
		submit = document.getElementById('submit');
		submit.onclick = function () { 
			obj.SendLoginForm(); 
		};
		if (NeedToSubmit) {
			this.SendLoginForm();
		}

		this.LangChanger = new CLanguageChanger();
		this.InitLangs();

		if (this._langs_collection) {
			ahrefs = this._langs_collection.getElementsByTagName('A');
			name = rname = '';
			for (i = 0; i < ahrefs.length; i++) {
				name = ahrefs[i].getAttribute('name');
				if (name.length > 4 && name.substr(0, 4) == 'lng_') {
					rname = name.substr(4);
					ahrefs[i].onclick = function () {
						obj.ChangeLang(this);
						return false;
					};
				}
			}
		}
	},

	ShowError: function (errorDesc)
	{
		this._loginErrorCont.className = 'wm_login_error';
		this._loginErrorMess.innerHTML = errorDesc;
	},

	HideError: function ()
	{
		this._loginErrorCont.className = 'wm_hide';
	},
	
	Show: function ()
	{
		this._container.className = '';
		if (this._reg_link) {
			this._reg_link.className = 'wm_reg_link';
		}
	},
	
	Hide: function ()
	{
		this._container.className = 'wm_hide';
		if (this._reg_link) {
			this._reg_link.className = 'wm_hide';
		}
	}
};

function CLoginDemoLangClass()
{
	this.currentLang = '';
	this.AddClassName = 'active';
	this.ContOne = document.getElementById('langDemoTop');
	this.ContTwo = document.getElementById('langDemoBottom');
}

CLoginDemoLangClass.prototype = {
	CheckLang: function (name)
	{
		if (this.currentLang == name) {
			return;
		}

		var i;
		var childsOne = (this.ContOne) ? this.ContOne.getElementsByTagName('a') : [];
		var childsTwo = (this.ContTwo) ? this.ContTwo.getElementsByTagName('a') : [];
		
		for (i = 0; i < childsOne.length; i++) {
			this.uncheckNode(childsOne.item(i));
		}
		for (i = 0; i < childsTwo.length; i++) {
			this.uncheckNode(childsTwo.item(i));
		}
		for (i = 0; i < childsOne.length; i++) {
			this.initNode(childsOne.item(i), name);
		}
		for (i = 0; i < childsTwo.length; i++) {
			this.initNode(childsTwo.item(i), name);
		}
	},
	
	initNode: function (aNode, name)
	{
		if (aNode && aNode.name == name){
			aNode.className = aNode.className + ' active';
			this.currentLang = aNode.name;
		}
	},
	
	uncheckNode: function (aNode)
	{
		if (aNode) {
			aNode.className = aNode.className.replace(/ active/g, '');
		}
	}
};

function Init()
{
	var transport, infoElem, errorCont, errorMess;
	Browser = new CBrowser();
	
	NetLoader = new CNetLoader();
	transport = NetLoader.GetTransport();
	
	if (transport) {
		checkMail = new CCheckMail(1);
	
	    infoElem = document.getElementById('info');
	    infoMessage = document.getElementById('info_message');
	    infoObj = new CInformation(infoElem, 'wm_information wm_status_information');
	    setTimeout('LoadWebMailScript();', 3000);
	
		LoginScreen = new CLoginScreen(TryLoginHandler);
		LoginDemoLangClass = new CLoginDemoLangClass();
	}
	else {
		
		errorCont = document.getElementById('login_error');
		errorMess = document.getElementById('login_error_message');
		errorCont.className = 'wm_login_error';
		errorMess.innerHTML = 'Sorry, this web browser is not supported.<br/>' + 
			'We recommend to use one of the following browsers:<br/>' +
			'<a href="http://www.microsoft.com/windows/internet-explorer/default.aspx">Internet Explorer 7</a>, ' +
			'<a href="http://www.firefox.com/">Mozilla Firefox 2</a>, ' +
			'<a href="http://www.apple.com/safari/download/">Safari 2</a>, ' +
			'<a href="http://www.opera.com/">Opera 9</a> ' +
			'or newer versions of these browsers.';
		
	}
	
	if (window.defaultInit) {
		window.defaultInit();
	}
}

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}