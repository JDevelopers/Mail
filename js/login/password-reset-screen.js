/*
 * Classes:
 *  CResetScreen(submitHandler, stepData)
 * Functions:
 *  ResetErrorHandler()
 *  ResetHandler()
 *  TryResetHandler()
 *  Init(stepData)
 */

var WebMail;
var infoObj, infoMessage;
var Browser;
var NetLoader;
var ResetScreen;

var ScriptLoader = new CScriptLoader();

function CResetScreen(submitHandler, stepData)
{
	this.step = 1;
	this.isBuilded = true;
	this.Tip = new CTip();
	this.onSubmit = submitHandler;
	this._langsIsShown = false;
	this.LangChanger = null;
	this.initStepLocal = stepData;

	this.ResetForm = document.getElementById('reset_form');

	this._step_1 = document.getElementById('step_1');
	this._step_2 = document.getElementById('step_2');
	this._step_3 = document.getElementById('step_3');
	this._step_4 = document.getElementById('step_4');

	this._step2_email = document.getElementById('step2Email');
	this._step2_q1 = document.getElementById('step2Q1');
	this._step2_q2 = document.getElementById('step2Q2');

	this._step3_name = document.getElementById('step3Name');
	this._step3_email = document.getElementById('step3Email');

	this._title = document.getElementById('top_title');

	this._body = document.getElementById('mbody');
	this._captcha = document.getElementById('captcha');

	this._login = document.getElementById('reset_login');
	this._domain = document.getElementById('reset_domain');

	this._answer1 = document.getElementById('reset_answer1');
	this._answer2 = document.getElementById('reset_answer2');

	this._password1 = document.getElementById('reset_password1');
	this._password2 = document.getElementById('reset_password2');

	this._container = document.getElementById('reset_screen');
	this._resetErrorCont = document.getElementById('reset_error');
	this._resetErrorMess = document.getElementById('reset_error_message');

	this._captchaImg = document.getElementById('captcha_img');
	this._captchaReloadLink = document.getElementById('lang_CaptchaReloadLink');
	this._language = document.getElementById('language');
	this._langs_collection = document.getElementById('langs_collection');
	this._langs_selected = document.getElementById('langs_selected');
	
	this._return_button = document.getElementById('submitId4');

	this.InitStep(stepData);
	this.Init();
}

CResetScreen.prototype = {
	Init: function ()
	{
		var obj = this;

		this._body.onclick = function (event) {
			obj.ShowLangs(event);
		};

		this._return_button.onclick = function () {
			document.location = LoginUrl;
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

		return true;
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
				document.location = ResetUrl + '?lang=' + object.name.substr(4);
			} else {
				this._language.value = object.name.substr(4);
				document.getElementById('langs_selected').innerHTML = '<span>' + object.innerHTML + '</span><font>&nbsp;</font><span class="wm_login_lang_switcher">&nbsp;</span>';
				CreateCookie('awm_defLang', object.name.substr(4), 635);
				ScriptLoader.Load([LanguageUrl + '?v=' + WmVersion + '&lang=' + object.name.substr(4)], function () {
					obj.ChangeLangProcess();
				});
			}
		}
	},

	ChangeLangProcess: function () {
		/*var obj = this;
		ScriptLoader.Load(['js/common/defines-lang.js'], function () {
			obj.LangChanger.Go();
			obj.SetTitle(obj.step);
			obj.SetStep3NameEmail(obj.initStepLocal);
		});*/
		this.LangChanger.Go();
		this.SetTitle(obj.step);
		this.SetStep3NameEmail(obj.initStepLocal);
	},

	InitLangs: function () {
		var langObj = document.getElementById('lang_ResetEmail');
		this.LangChanger.Register('innerHTML', langObj, 'ResetEmail', ':');

		langObj = document.getElementById('lang_ResetEmailDesc');
		this.LangChanger.Register('innerHTML', langObj, 'ResetEmailDesc', '');

		langObj = document.getElementById('lang_ResetCaptcha');
		this.LangChanger.Register('innerHTML', langObj, 'ResetCaptcha', ':');

		langObj = document.getElementById('lang_CaptchaReloadLink');
		this.LangChanger.Register('innerHTML', langObj, 'CaptchaReloadLink', '');
		
		langObj = document.getElementById('submitId1');
		this.LangChanger.Register('value', langObj, 'ResetSubmitStep1', '');

		langObj = document.getElementById('lang_ResetTopDesc1Step2');
		this.LangChanger.Register('innerHTML', langObj, 'ResetTopDesc1Step2', '');

		langObj = document.getElementById('lang_ResetTopDesc2Step2');
		this.LangChanger.Register('innerHTML', langObj, 'ResetTopDesc2Step2', '');

		langObj = document.getElementById('lang_ResetQuestion1');
		this.LangChanger.Register('innerHTML', langObj, 'ResetQuestion1', ':');

		langObj = document.getElementById('lang_ResetAnswer1');
		this.LangChanger.Register('innerHTML', langObj, 'ResetAnswer1', ':');

		langObj = document.getElementById('lang_ResetQuestion2');
		this.LangChanger.Register('innerHTML', langObj, 'ResetQuestion2', ':');

		langObj = document.getElementById('lang_ResetAnswer2');
		this.LangChanger.Register('innerHTML', langObj, 'ResetAnswer2', ':');

		langObj = document.getElementById('submitId2');
		this.LangChanger.Register('value', langObj, 'ResetSubmitStep2', '');

		langObj = document.getElementById('lang_ResetTopDescStep3');
		this.LangChanger.Register('innerHTML', langObj, 'ResetTopDescStep3', '');

		langObj = document.getElementById('lang_ResetPass1');
		this.LangChanger.Register('innerHTML', langObj, 'ResetPass1', ':');

		langObj = document.getElementById('lang_ResetPass2');
		this.LangChanger.Register('innerHTML', langObj, 'ResetPass2', ':');

		langObj = document.getElementById('submitId3');
		this.LangChanger.Register('value', langObj, 'ResetSubmitStep3', '');

		langObj = document.getElementById('lang_ResetDescStep4');
		this.LangChanger.Register('innerHTML', langObj, 'ResetDescStep4', '');

		langObj = document.getElementById('submitId4');
		this.LangChanger.Register('value', langObj, 'ResetSubmitStep4', '');

		langObj = document.getElementById('return_id');
		this.LangChanger.Register('innerHTML', langObj, 'ResetReturnLink', '');
	},

	InitStep: function (InitStep)
	{
		this.HideError();
		this.step = 1;
		if (InitStep && InitStep.step) {
			this.step = InitStep.step;
		}

		this.initStepLocal = InitStep;

		if (this.step == 1) {
			this._step_1.className = "wm_login_content";
			this._step_2.className = "wm_hide";
			this._step_3.className = "wm_hide";
			this._step_4.className = "wm_hide";
		} else if (this.step == 2) {
			this._step_1.className = "wm_hide";
			this._step_2.className = "wm_login_content";
			this._step_3.className = "wm_hide";
			this._step_4.className = "wm_hide";

			this._step2_email.innerHTML = InitStep.email;
			this._step2_q1.innerHTML = InitStep.Q1;
			this._step2_q2.innerHTML = InitStep.Q2;
		} else if (this.step == 3) {
			this._step_1.className = "wm_hide";
			this._step_2.className = "wm_hide";
			this._step_3.className = "wm_login_content";
			this._step_4.className = "wm_hide";

			this.SetStep3NameEmail(InitStep);
		} else if (this.step == 4) {
			this._step_1.className = "wm_hide";
			this._step_2.className = "wm_hide";
			this._step_3.className = "wm_hide";
			this._step_4.className = "wm_login_content";
		}
		this.SetTitle(this.step);
	},

	SetStep3NameEmail: function(InitStep)
	{
		if (InitStep) {
			var stepName = Lang.NullUserNameonReset;
			if (InitStep.name && InitStep.name.length > 0) {
				stepName = InitStep.name;
			}

			this._step3_name.innerHTML = stepName;
			this._step3_email.innerHTML = InitStep.email;
		}
	},

	SetTitle: function (step)
	{
		if (step > 0 && step < 5) {
			this._title.innerHTML = Lang.PasswordResetTitle.replace('%d', step);
		}
	},

	CheckRegForm: function(step) {
		this.Tip.Hide('');

		if (step == 1) {
			var vLogin = Trim(this._login.value);
			if (Validator.IsEmpty(vLogin)) {
				this.Tip.Show(Lang.WarningEmailBlank, this._login, 'reset_login');
				return false;
			}
			var vEmail = vLogin + '@' + Trim(this._domain.value);
			if (!Validator.IsCorrectEmail(vEmail)) {
				this.Tip.Show(Lang.WarningCorrectEmail, this._login, 'reset_login');
				return false;
			}
			if (this._captcha) {
				var vCapcha = Trim(this._captcha.value);
				if (Validator.IsEmpty(vCapcha)) {
					this.Tip.Show(Lang.WarningFieldBlank, this._captcha, 'captcha');
					return false;
				}
			}
		} else if (step == 2) {
			var vA1 = Trim(this._answer1.value);
			if (Validator.IsEmpty(vA1)) {
				this.Tip.Show(Lang.WarningFieldBlank, this._answer1, 'reset_answer1');
				return false;
			}
			var vA2 = Trim(this._answer2.value);
			if (Validator.IsEmpty(vA2)) {
				this.Tip.Show(Lang.WarningFieldBlank, this._answer2, 'reset_answer2');
				return false;
			}
		} else if (step == 3) {
			var vP1 = Trim(this._password1.value);
			if (Validator.IsEmpty(vP1)) {
				this.Tip.Show(Lang.WarningFieldBlank, this._password1, 'reset_password1');
				return false;
			}
			var vP2 = Trim(this._password2.value);
			if (Validator.IsEmpty(vP2)) {
				this.Tip.Show(Lang.WarningFieldBlank, this._password2, 'reset_password2');
				return false;
			}
			if (vP1 != vP2) {
				this.Tip.Show(Lang.WarningPassNotMatch, this._password2, 'reset_password2');
				return false;
			}
		}

		return true;
	},

	SendResetForm: function(step)
	{
		if (!this.CheckRegForm(step)) {
			return;
		}

		var xml = '<param name="action" value="resetpassword" /><param name="request" value="" />';

		if (step == 1) {
			xml += '<param name="step" value="' + step + '"/>';
			xml += '<param name="login">' + GetCData(this._login.value) + '</param>';
			xml += '<param name="domain">' + GetCData(this._domain.value) + '</param>';
			if (this._captcha) {
				xml += '<param name="captcha">' + GetCData(this._captcha.value) + '</param>';
			}
		} else if (step == 2) {
			xml += '<param name="step" value="' + step + '"/>';
			xml += '<param name="answer1">' + GetCData(this._answer1.value) + '</param>';
			xml += '<param name="answer2">' + GetCData(this._answer2.value) + '</param>';
		} else if (step == 3) {
			xml += '<param name="step" value="' + step + '"/>';
			xml += '<param name="password1">' + GetCData(this._password1.value) + '</param>';
			xml += '<param name="password2">' + GetCData(this._password2.value) + '</param>';

		} else {
			return;
		}
		
		this.Xml = '<?xml version="1.0" encoding="utf-8"?><webmail>' + xml + '</webmail>';
		this.onSubmit.call(this);
	},

	AjaxInit: function ()
	{
		var submit1, submit2, submit3;
		var ahrefs, name, i;
		var obj = this;

		this.ResetForm.onsubmit = function () {
			obj.SendResetForm(obj.step);
			return false;
		};

		submit1 = document.getElementById('submitId1');
		submit1.onclick = function () {
			obj.SendResetForm(1);
		};

		submit2 = document.getElementById('submitId2');
		submit2.onclick = function () {
			obj.SendResetForm(2);
		};

		submit3 = document.getElementById('submitId3');
		submit3.onclick = function () {
			obj.SendResetForm(3);
		};
		
		this.LangChanger = new CLanguageChanger();
		this.InitLangs();

		if (this._langs_collection) {
			ahrefs = this._langs_collection.getElementsByTagName('A');
			name = '';
			for (i = 0; i < ahrefs.length; i++) {
				name = ahrefs[i].getAttribute('name');
				if (name.length > 4 && name.substr(0, 4) == 'lng_') {
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
		this._resetErrorCont.className = 'wm_login_error';
		this._resetErrorMess.innerHTML = errorDesc;
	},

	HideError: function ()
	{
		this._resetErrorCont.className = 'wm_hide';
	},

	Show: function ()
	{
		this._container.className = '';
	},

	Hide: function ()
	{
		this._container.className = 'wm_hide';
	}
};


function ResetErrorHandler()
{
	infoObj.Hide();
	ResetScreen.ShowError(this.ErrorDesc);
	ResetScreen.ReloadCaptcha();
}

function ResetHandler()
{
	var XmlDoc, RootElement, ErrorTag, ErrorDesc, Objects, iCount, i, j, attr, hashParts, jCount, part;
	infoObj.Hide();
	XmlDoc = this.responseXML;
	if (XmlDoc && XmlDoc.documentElement && typeof(XmlDoc) == 'object' && typeof(XmlDoc.documentElement) == 'object') {
		RootElement = XmlDoc.documentElement;
		if (RootElement && RootElement.tagName == 'webmail') {
			ErrorTag = RootElement.getElementsByTagName('error')[0];
			if (ErrorTag) {
				ErrorDesc = ErrorTag.childNodes[0].nodeValue;
				ResetScreen.ShowError((ErrorDesc && ErrorDesc.length > 0) ? ErrorDesc : Lang.ErrorWithoutDesc);
				ResetScreen.ReloadCaptcha();
			} else {
				Objects = RootElement.childNodes;
				if (Objects.length == 0) {
					ResetScreen.ShowError(Lang.ErrorEmptyXmlPacket);
					ResetScreen.ReloadCaptcha();
				} else {
					iCount = Objects.length;
					for (i = iCount - 1; i >= 0; i--) {
						if (Objects[i].tagName == 'reset') {
							var part, localParts, jCount;
							var localStep = 1;
							attr = Objects[i].getAttribute('step');if (attr) localStep = attr - 0;
							if (localStep == 2) {
								var localEmail, localQ1, localQ2, localTagName;
								localEmail = localQ1 = localQ2 = localTagName = '';
								
								localParts = Objects[i].childNodes;
								jCount = localParts.length;
								for (j = jCount - 1; j >= 0; j--) {
									part = localParts[j].childNodes;
									if (part.length > 0) {
										localTagName = localParts[j].tagName;
										if (localTagName == 'email') {
											localEmail = part[0].nodeValue;
										} else if (localTagName == 'q1') {
											localQ1 = part[0].nodeValue;
										} else if (localTagName == 'q2') {
											localQ2 = part[0].nodeValue;
										}
									}
								}

								if (localEmail.length > 0 && localQ1.length > 0 && localQ2.length > 0) {
									ResetScreen.InitStep({
										step: 2,
										email: localEmail,
										Q1: localQ1,
										Q2: localQ2
									});
								} else {
									ResetScreen.ShowError(Lang.WebMailException);
									ResetScreen.ReloadCaptcha();
								}
							}
							else if (localStep == 3) {
								var localEmail, localName;
								localEmail = localName = '';

								localParts = Objects[i].childNodes;
								jCount = localParts.length;
								for (j = jCount - 1; j >= 0; j--) {
									part = localParts[j].childNodes;
									if (part.length > 0) {
										localTagName = localParts[j].tagName;
										if (localTagName == 'email') {
											localEmail = part[0].nodeValue;
										} else if (localTagName == 'name') {
											localName = part[0].nodeValue;
										}
									}
								}
								
								ResetScreen.InitStep({
									step: 3,
									email: localEmail,
									name: localName
								});
							}
							else if (localStep == 4) {
								ResetScreen.InitStep({step: 4});
							}
						}
					}
				}
			}//if (ErrorTag)
		}
		else {
			ResetScreen.ShowError(Lang.ErrorParsing + '<br/>Error code 2.<br/>' + Lang.ResponseText + '<br/>' + this.responseText);
			ResetScreen.ReloadCaptcha();
		}//if (RootElement)
	}
	else {
		ResetScreen.ShowError(Lang.ErrorParsing + '<br/>Error code 1.<br/>' + Lang.ResponseText + '<br/>' + this.responseText);
		ResetScreen.ReloadCaptcha();
	}//if (XmlDoc)
}

function TryResetHandler()
{
	infoMessage.innerHTML = Lang.Loading;
	infoObj.Show();
	infoObj.Resize();
	NetLoader.LoadXMLDoc(ActionUrl, 'xml=' + encodeURIComponent(this.Xml), ResetHandler, ResetErrorHandler);
}


function Init(stepData)
{
	var transport, infoElem;
	Browser = new CBrowser();

	NetLoader = new CNetLoader();
	transport = NetLoader.GetTransport();

	if (transport) {
	    infoElem = document.getElementById('info');
	    infoMessage = document.getElementById('info_message');
	    infoObj = new CInformation(infoElem, 'wm_information wm_status_information');

		ResetScreen = new CResetScreen(TryResetHandler, stepData);
	}
}