/*
 * Functions:
 *  InitSwfUploader(newMessageScreen)
 * Classes:
 *  CNewMessageScreen()
 */

function InitSwfUploader(newMessageScreen)
{
	var settings = {
		flash_url : "js/mail/swfupload.swf",
		upload_url: UploadUrl,
		file_size_limit : "100 MB",
		file_types : "*.*",
		file_types_description : "All Files",
		file_upload_limit : 100,
		file_queue_limit : 0,
		custom_settings : {
			progressTarget : "fsUploadProgress",
			attachments: [],
			newMessageScreen: newMessageScreen
		},
		debug: false,
		post_params : {
			"PHPWEBMAILSESSID" : WebMailSessId,
			"flash_upload" : "1"
		},

		// Button settings
		button_image_url: "skins/upload_attachments.png",
		button_width: "154",
		button_height: "34",
		button_placeholder_id: "spanButtonPlaceHolder",
		button_text: '<span class="theFont">' + Lang.AttachmentsUpload  + '</span>',
		button_text_style: ".theFont { font-weight: bold; font-size: 12px; font-family: Verdana; color: #FFFFFF}",
		button_text_left_padding: Lang.AttachmentsUploadPadding,
		button_text_top_padding: 5,

		// The event handler functions are defined in handlers.js
		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccess
	};

	SwfUploader = new SWFUpload(settings);
}

function CNewMessageScreen()
{
	this.Id = SCREEN_NEW_MESSAGE;
	this.isBuilded = false;
	this.hasCopyright = false;
	this.BodyAutoOverflow = true;
	
	this.shown = false;
	
	this._msgObj = new CMessage();
	this._newMessage = true;
	this._sendersGroups = Array();

	this._mainContainer = null;
	this._toolBar = null;
	this._logo = null;
	this._accountsBar = null;
	this._headersTbl = null;
	this._uploadTbl = null;
	//logo + accountslist + toolbar
	this.ExternalHeight = 56 + 32 + 26 + 24;

	this._bccSwitcher = null;
	this._hasBcc = false;
	this._bccCont = null;

	this._fromObj = null;
	this._fromCont = null;
	this._toObj = null;
	this._ccObj = null;
	this._bccObj = null;
	this._subjectObj = null;
	this._priorityLowButton = null;
	this._priorityNormalButton = null;
	this._priorityHighButton = null;
	this._priority = 3;

	this._sensitiveNothingButton = null;
	this._sensivityConfidentialButton = null;
	this._sensivityPrivateButton = null;
	this._sensivityPersonalButton = null;
	this._sensivity = SENSIVITY_NOTHING;

	this.AutoFilling = null;
	this.PopupContacts = null;
	
	this._modeSwitcher = null;
	this._modeSwitcherCont = null;
	this._modeSwitcherTr = null;
	this._mailConfirmationCh = null;
    this._saveMailTr = null;
	this._mode = true;
	this._plainEditorObj = null;
	this._plainEditorDiv = null;
	this._plainEditorCont = null;
	
	this._flashFileUpload = false;
	this._uploadForm = null;
	this._uploadFile = null;
	this._attachments = [];
	this._inlineAttachments = [];
	this._rowIndex = 0;
	this._attachmentsDiv = null;
	this._attachmentsTbl = null;
	
	//this._picturesControl = new CMessagePicturesController(this.ShowPictures, this);

	this._saving = false;
	this._saveTool = null;
	this._sending = false;
	this._sendTool = null;
	this._messageLoaded = false;
	
	
	this._subjectCont = null;
	this._counterCont = null;
	this._counterObj = null;
	this._lastHtmlText = '';
	this._lastPlainText = '';

	this.eventsListForSaver = Array('click', 'keyup');
	this.timer = null;
	this.isSaverWork = false;
	this.isSavedOrSent = false;

	this.resized = false;
	
    this._mailSaveCh = null;
}

CNewMessageScreen.prototype = {
	PlaceData: function (Data)
	{
		if (Data != null) {
			var Type = Data.Type;
			switch (Type){
				case TYPE_CONTACTS:
					var iCount = Data.List.length;
					if (Data.LookFor == '') {
					    if (iCount == 0) {
					        this.PopupContacts.Hide();
						    WebMail.ShowReport(Lang.InfoNoContactsGroups);
					    }
					    else {
						    this.PopupContacts.Fill(Data.List);
					    }
					}
					else {
					    if (iCount == 0) {
					        this.AutoFilling.Hide();
					    } else if (Data.Count > iCount) {
						    this.AutoFilling.Fill(Data.List, Data.LookFor, Lang.InfoListNotContainAddress);
					    } else {
						    this.AutoFilling.Fill(Data.List, Data.LookFor, '');
					    }
					}
				break;
			}
		}
	},//PlaceData
	
	AddSenderGroup: function (id)
	{
		var hasValue = false;
		var iCount = this._sendersGroups.length;
		for (var i=0; i<iCount; i++) {
			if (this._sendersGroups[i] == id) {
				hasValue = true;
			}
		}
		if (!hasValue) {
			this._sendersGroups[iCount] = id;
		}
	},
	
	SetErrorHappen: function ()
	{
		this._saving = false;
		this._saveTool.Enable();
		this._sending = false;
		this._sendTool.Enable();
	},
	
	SetMessageId: function (id, uid)
	{
		this._saving = false;
		this._saveTool.Enable();
		this._msgObj.Id = id;
		this._msgObj.Uid = uid;
	},
	
	Show: function ()
	{
		this._saving = false;
		this._saveTool.Enable();
		this._sending = false;
		this._sendTool.Enable();
		this._sendersGroups = Array();
		this._mainContainer.className = '';
		if (WebMail.Settings.AllowBodySize) {
			this._counterCont.className = 'last_row';
			this._subjectCont.className = '';
		}
		else {
			this._counterCont.className = 'wm_hide';
			this._subjectCont.className = 'last_row';
		}
		this._toolBar.Show();
		if (WebMail.Settings.ShowTextLabels) {
			this._toolBar.ShowTextLabels();
		}
		else {
			this._toolBar.HideTextLabels();
		}
		var obj = this;
		if (WebMail.Settings.AllowDhtmlEditor) {
			HtmlEditorField.Show(7, true);
			HtmlEditorField.SetPlainEditor(this._plainEditorObj, this._modeSwitcher);
			HtmlEditorField.Replace();
			/*Add saving to draft by timeout*/
			if (WebMail && (typeof(WebMail.IdleSessionTimeout) != 'undefined' && WebMail.IdleSessionTimeout > 0))
			{
				HtmlEditorField.UpdateEditorHandlers(
					function()
					{
						WebMail.StartIdleTimer.call(WebMail);
					},
					obj.eventsListForSaver
				);
			}
			/*****/
		}
		this.shown = true;
		this._mailConfirmationCh.checked = false;
		this.Fill();
		this.SetCounterValue();
		this._modeSwitcherCont.className = (WebMail.Settings.AllowDhtmlEditor)
			? 'wm_html_editor_switcher' : 'wm_hide';
		
		
	},
	
	ParseSettings: function () { },
	
	RestoreFromHistory: function () { },

	Hide: function ()
	{
		this.shown = false;
		this.SetNewMessage();
		if (WebMail.Settings.AllowDhtmlEditor) {
			HtmlEditorField.Hide();
		}

		this.PopupContacts.Hide();
		this.AutoFilling.Hide();
		this._mainContainer.className = 'wm_hide';
		this._toolBar.Hide();
		this._messageLoaded = false;
		this.EnableForm();
		this._inlineAttachments = [];
	},
	
	ClearForm: function ()
	{
		this._toObj.value = '';
		this._ccObj.value = '';
		this._bccObj.value = '';
		this._subjectObj.value = '';
		if (WebMail.Settings.AllowDhtmlEditor) {
			HtmlEditorField.SetHtml('');
		}
		this._plainEditorObj.value = '';
	},
	
	EnableForm: function ()
	{
		this._fromObj.disabled = false;
		this._toObj.disabled = false;
		this._ccObj.disabled = false;
		this._bccObj.disabled = false;
		this._subjectObj.disabled = false;
		if (WebMail.Settings.AllowDhtmlEditor) {
			HtmlEditorField.Enable();
		}
		this._plainEditorObj.disabled = false;
		if (!this._flashFileUpload) {
			this._uploadFile.disabled = false;
		}
	},
	
	DisableForm: function ()
	{
		// condition for ie6 on slow connections
/*
		if (this.shown && !this._toObj.disabled) {
			this._toObj.focus();
		}
*/		
		this._fromObj.disabled = true;
		this._toObj.disabled = true;
		this._ccObj.disabled = true;
		this._bccObj.disabled = true;
		this._subjectObj.disabled = true;
		if (WebMail.Settings.AllowDhtmlEditor) {
			HtmlEditorField.Disable();
		}
		this._plainEditorObj.disabled = true;
		if (!this._flashFileUpload) {
			this._uploadFile.disabled = true;
		}
	},
	
	ClickBody: function (ev)
	{
		if (WebMail.Settings.AllowDhtmlEditor && this._mode) {
			HtmlEditorField.ClickBody();
		}
		this.PopupContacts.ClickBody(ev);
		this.AutoFilling.ClickBody(ev);
	},

	GetExternalHeight: function()
	{
		var externalHeight = 0;
		if (this._logo != null) {
			externalHeight += this._logo.offsetHeight;
		}
		if (this._accountsBar != null) {
			externalHeight += this._accountsBar.offsetHeight;
		}
		externalHeight += this._toolBar.GetHeight();
		externalHeight += this._headersTbl.offsetHeight;
		externalHeight -= this._plainEditorCont.offsetHeight;
		externalHeight += this._modeSwitcherTr.offsetHeight;
		externalHeight += this._saveMailTr.offsetHeight;
		externalHeight += this._attachmentsDiv.offsetHeight;
		if (!this._flashFileUpload) {
			externalHeight += this._uploadTbl.offsetHeight;
		}
		if (externalHeight != 0) {
			this.ExternalHeight = externalHeight;
		}
		return this.ExternalHeight;
	},
	
	ResizeBody: function ()
	{
		if (this.isBuilded) {
			var isAuto = false;
			var width = GetWidth();
			if (width < 684) {
				width = 684;
				isAuto = true;
			}
			width = width - 40;
			var padding = 6;
			if (Browser.IE && Browser.Version < 7) {
				padding = 11;
			}
			var screenHeight = GetHeight();
			var externalHeight = this.GetExternalHeight();
			var height = screenHeight - externalHeight - padding;
			if (height < 250) {
				height = 250;
				isAuto = true;
			}
			this._plainEditorDiv.style.height = height - 2 + 'px';
			this._plainEditorDiv.style.width = width + 17 + 'px';

			if (WebMail.Settings.AllowDhtmlEditor) {
				HtmlEditorField.Resize(width, height);
			}
			else {
				this._plainEditorObj.style.height = (height - 1) + 'px';
				this._plainEditorObj.style.width = (width - 2) + 'px';
			}
			this.PopupContacts.Replace();
			this.AutoFilling.Replace();
			SetBodyAutoOverflow(isAuto);
		}
	},
	
	SetNewMessage: function ()
	{
		this._newMessage = true;
		this._msgObj = new CMessage();
		this.ChangeSignature();
		Screens[SCREEN_NEW_MESSAGE].ShowHandler = '';
		this._messageLoaded = true;
	},
	
	ChangeSignature: function ()
	{
	    var account = WebMail.Accounts.GetCurrentAccount();
	    var value = (null != account && account.SignatureOpt != 0) ? account.Signature : '';
		var prefix = '';
		if (value.length > 0) {
			prefix = (WebMail.Settings.AllowDhtmlEditor && this._msgObj.HasHtml) ? '<br/><br/>' : '\r\n\r\n';
		}
		else if (Browser.Mozilla && WebMail.Settings.AllowDhtmlEditor && this._msgObj.HasHtml) {
			prefix = '<br/>';
		}
		if (WebMail.Settings.AllowDhtmlEditor && this._msgObj.HasHtml) {
			this._msgObj.HtmlBody = prefix + value;
		} else {
			this._msgObj.PlainBody = (null != account && account.SignatureType == SIGNATURE_TYPE_HTML)
				? prefix + value.replace(/<br *\/{0,1}>/gi, '\r\n').replace(/<[^>]*>/g, '')
				: prefix + value;
			this._msgObj.HasHtml = false;
			this._msgObj.HasPlain = true;
		}
	},
	
	_getReplyMessage: function (msg, replyAction, replyText)
	{
		var replyMsg = new CMessage();
		var fromField = this._getFromAddrByAcctId(WebMail.Accounts.CurrId);
		replyMsg.PrepareForReply(msg, replyAction, fromField);
		replyMsg.FromAddr = fromField;
		if (replyMsg.HasHtml && replyText != '') {
			replyMsg.HtmlBody = replyText.replace(/\r\n/gi, '<br />').replace(/\n/gi, '<br />').replace(/\r/gi, '<br />') + '<br />' + replyMsg.HtmlBody;
		}
		if ((!replyMsg.HasHtml || replyMsg.HasPlain) && replyText != '') {
			replyMsg.PlainBody = replyText + '\r\n' + replyMsg.PlainBody;
			replyMsg.HasPlain = true;
		}
		return replyMsg;
	},
	
	UpdateMessageForReply: function (msg, replyAction, replyText)
	{
		this._newMessage = false;
		Screens[SCREEN_NEW_MESSAGE].ShowHandler = '';
		this._msgObj = this._getReplyMessage(msg, replyAction, replyText);
		this._messageLoaded = true;
		this.Fill();
	},
	
	UpdateMessageFromContacts: function (toField, ccField, bccField)
	{
		Screens[SCREEN_NEW_MESSAGE].ShowHandler = '';
		this.SetNewMessage();
		this._msgObj.ToAddr = toField || '';
		this._msgObj.CCAddr = ccField || '';
		this._msgObj.BCCAddr = bccField || '';
		this._messageLoaded = true;
		this.Fill();
	},

	UpdateMessageFromConfirmation: function (toField)
	{
		Screens[SCREEN_NEW_MESSAGE].ShowHandler = '';
		this.SetNewMessage();
		this._msgObj.ToAddr = toField;
		this._msgObj.Subject = Lang.ReturnReceiptSubject;
		this._messageLoaded = true;
		var fromField = this._getFromAddrByAcctId(WebMail.Accounts.CurrId);

		if (WebMail.Settings.AllowDhtmlEditor) {
			this._msgObj.HtmlBody = Lang.ReturnReceiptMailText1 + ' <a href="mailto:' + fromField + '">' + fromField + '</a>.<br /><br />' + Lang.ReturnReceiptMailText2;
		} else {
			this._msgObj.PlainBody = Lang.ReturnReceiptMailText1 + ' ' + fromField + '.\r\n\r\n' + Lang.ReturnReceiptMailText2;
		}
		
		this.Fill();
	},

	UpdateMessage: function (message)
	{
		this._newMessage = false;
		Screens[SCREEN_NEW_MESSAGE].ShowHandler = '';
		this._msgObj = new CMessage();
		this._msgObj.PrepareForEditing(message);
		this._messageLoaded = true;
		this.Fill();
	},
	
	/*ShowPictures: function ()
	{
		if (this._msgObj.Safety == SAFETY_NOTHING)
		{
			this._msgObj.ShowPictures();
			if (this._msgObj.HasHtml) {
				HtmlEditorField.SetHtml(this._msgObj.HtmlBody);
			}
			else {
				HtmlEditorField.SetText(this._msgObj.PlainBody);
			}
			this.ResizeBody();
		}
	},*/
	
	FillFromField: function ()
	{
		var sel = this._fromObj;
		CleanNode(sel);
		var accounts = WebMail.Accounts.Items;
		if (accounts.length == 1) {
			this._fromCont.className = 'wm_hide';
			return;
		}
		this._fromCont.className = 'first_row';
		for (var i=0; i<accounts.length; i++) {
			var acct = accounts[i];
			var opt = CreateChild(sel, 'option', [['value', acct.Id]]);
			opt.innerHTML = (acct.UseFriendlyNm && acct.FriendlyNm.length > 0)
				? '"' + acct.FriendlyNm + '" &lt;' + acct.Email + '&gt;'
				: acct.Email;
		}
	},

	SetCounterValue: function ()
	{
		if (!WebMail.Settings.AllowBodySize) return;
		var counterValue = WebMail.Settings.MaxBodySize;
		if ((null != this._msgObj) && this.shown && this._messageLoaded) {
			var msg = this._msgObj;
			if (WebMail.Settings.AllowDhtmlEditor && msg.HasHtml && HtmlEditorField._htmlMode) {
				var htmlText = HtmlEditorField.GetText();
				if (htmlText == false) return;
				var textWithoutNodes = HtmlDecode(htmlText.replace(/<br *\/{0,1}>/gi, '\r\n').replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' '))
				counterValue = WebMail.Settings.MaxBodySize - textWithoutNodes.length;
			}
			else {
				var text = this._plainEditorObj.value;
				counterValue = WebMail.Settings.MaxBodySize - text.length;
			}
		}
		this._counterObj.value = counterValue;
	},
	
	Fill: function ()
	{
		if ((null != this._msgObj) && this.shown && this._messageLoaded) {
			var msg = this._msgObj;
			this.SetPriority(msg.Importance);
			this.SetSensivity(msg.Sensivity);
			
			/*if (msg.Safety != SAFETY_NOTHING || !WebMail.Settings.AllowDhtmlEditor) {
				this._picturesControl.Hide();
			}
			else {
				this.FillMessageInfo(msg);
				//this._picturesControl.Show();
			}*/
			this.FillFromField();
			this._fromObj.value = WebMail.Accounts.GetAccountIdByFullEmail(msg.FromAddr);
			this._toObj.value = msg.ToAddr || '';
			this._ccObj.value = msg.CCAddr || '';
			this._bccObj.value = msg.BCCAddr || '';
			if (msg.BCCAddr.length == 0) {
				this._bccSwitcher.innerHTML = Lang.ShowBCC;
				this._hasBcc = false;
				this._bccCont.className = 'wm_hide';
			}
			else {
				this._bccSwitcher.innerHTML = Lang.HideBCC;
				this._hasBcc = true;
				this._bccCont.className = '';
			}
			this._subjectObj.value = msg.Subject || '';

			if (WebMail.Settings.AllowDhtmlEditor) {
				if (msg.HasHtml) {
					HtmlEditorField.SetHtml(HtmlDecodeBody(msg.HtmlBody));
					HtmlEditorField.Show(7, true);
					this._plainEditorObj.tabIndex = -1;
				}
				else {
					HtmlEditorField.SetText(HtmlDecodeBody(msg.PlainBody));
					this._plainEditorObj.tabIndex = 6;
				}

//				var doc = Browser.IE ? HtmlEditorField._area.document : HtmlEditorField._area.contentDocument;
//				var obj = this;
//				if (doc.addEventListener)
//				{
//					doc.addEventListener('keypress', function(ev) {
//						obj.IsTextChanged.call(obj, ev);
//					}, true);
//				}
//				else if (doc.attachEvent)
//				{
//					doc.attachEvent('onkeydown', function(ev) {
//						obj.IsTextChanged.call(obj, ev);
//					});
//				}

			}
			else {
				if (msg.HasPlain) {
					this._plainEditorObj.value = HtmlDecodeBody(msg.PlainBody);
				}
				else {
					this._plainEditorObj.value = HtmlDecode(HtmlDecodeBody(msg.HtmlBody).replace(/<br *\/{0,1}>/gi, '\n').replace(/<[^>]*>/g, ''));
				}
				this._plainEditorObj.tabIndex = 6;
			}
			this.EnableForm();
			if (this._flashFileUpload) {
				
				this._inlineAttachments = [];
				SwfUploader.customSettings.attachments = [];
				
				var progressTarget = document.getElementById(SwfUploader.customSettings.progressTarget);
				CleanNode(progressTarget);
				progressTarget.className = 'wm_hide';
				
				for (var i = 0; i < msg.Attachments.length; i++) {
					var att = msg.Attachments[i];
					if (att.Inline) {
						this._inlineAttachments.push(att);
					}
					else {
						var file = {id: att.TempName, name: att.FileName, size: att.Size};
						SwfUploader.customSettings.attachments[file.id] = att;
						var progress = new FileProgress(file, SwfUploader.customSettings.progressTarget);
						progress.toggleCancel(SwfUploader);
						progress.setStatus('');
						progress.reset();
					}
				}
			}
			else {
				this.RedrawAttachments(msg.Attachments);
			}
			this.RebuildUploadForm();
			if (!msg.HasHtml || !WebMail.Settings.AllowDhtmlEditor) {
				if (this._plainEditorObj.createTextRange)  
				{  
					var range = this._plainEditorObj.createTextRange();  
					range.collapse(true);  
					range.select();  
				}
				else {
					this._plainEditorObj.selectionStart = 0;
					this._plainEditorObj.selectionEnd = 0;
				}
			}
			if (this._toObj.value.length == 0) {
			    this._toObj.focus();
			}
			else {
			    if (!msg.HasHtml || !WebMail.Settings.AllowDhtmlEditor) {
			        this._plainEditorObj.focus();
			    }
			    else {
			        HtmlEditorField.Focus();
			    }
			}
		} else if (this.shown) {
			this.ClearForm();
			this.DisableForm();
		}
		if (this.shown) {
			this.ResizeBody();
		}
	},//Fill
	
	_getFromAddrByAcctId: function (id)
	{
		var account = WebMail.Accounts.GetAccountById(id);
		return (account.UseFriendlyNm && account.FriendlyNm.length > 0)
			? '"' + account.FriendlyNm + '" <' + account.Email + '>'
			: account.Email;
	},
	
	TrunkHtmlBody: function (htmlBody)
	{
		var pointer = 0;
		var counter = 0;
		var length = htmlBody.length;
		var inNode = false;
		while (pointer < length && counter < WebMail.Settings.MaxBodySize) {
			var symbol = htmlBody.substr(pointer, 1);
			switch (symbol) {
				case '<':
					inNode = true;
					break;
				case '>':
					inNode = false;
					break;
				default:
					if (!inNode) {
						counter++;
					}
					break;
			}
			pointer++;
		}
		return htmlBody.substring(0, pointer)
	},
	
	_checkMsgSize: function (msg)
	{
		var sett = WebMail.Settings;
		if (!sett.EnableMailboxSizeLimit) return true;
		if (!msg) return false;

		var headersSize = 0;

		headersSize += (msg.FromAddr ? msg.FromAddr.length : 0)
			+ (msg.ToAddr ? msg.ToAddr.length : 0)
			+ (msg.CCAddr ? msg.CCAddr.length : 0)
			+ (msg.BCCAddr ? msg.BCCAddr.length : 0)
			+ (msg.Subject ? msg.Subject.length * 2 : 0)
			+ 400;

		var bodySize = (msg.HasHtm) ? msg.HtmlBody.length * 2 : msg.PlainBody.length * 2;

		var attachmentsSize = 0;
		for (var i = 0; i < msg.Attachments.length; i++) {
			var attachment = msg.Attachments[i];
			if (attachment) {
				attachmentsSize += Math.round(parseInt(attachment.Size) * 1.4);
			}
		}

		var messageTotalSize = headersSize + bodySize + attachmentsSize;
		var availableMailboxSize = sett.MailBoxLimit - sett.MailBoxSize;

		return (availableMailboxSize > messageTotalSize);
	},
	
	_showMailboxSizeWarning: function (mode)
	{
		var alertMsg = Lang.MessageSizeExceedsAccountQuota + ' ';
		switch (mode) 
		{
			case SEND_MODE:
				alertMsg += Lang.MessageCannotSent;
				break;
			case SAVE_MODE:
				alertMsg += Lang.MessageCannotSaved;
			    break;
		}
		alert(alertMsg);
	},
	
	SaveChanges: function (mode)
	{
		if (this._sending && mode == SEND_MODE) return;
		if (this._saving && mode == SAVE_MODE) return;
		var fromAcctId = (WebMail.Accounts.HasAccount(this._fromObj.value - 0))
			? this._fromObj.value - 0
			: WebMail.Accounts.CurrId;
		var fromValue = this._getFromAddrByAcctId(fromAcctId);
		var toValue = this._toObj.value;
		var ccValue = this._ccObj.value;
		var bccValue = this._bccObj.value;

		var incorrectEmails = new Array();

		if (toValue.length > 0)
		{
			incorrectEmails = incorrectEmails.concat(validateMessageAddressString(toValue));
		}
		if (ccValue.length > 0)
		{
			incorrectEmails = incorrectEmails.concat(validateMessageAddressString(ccValue));
		}
		if (bccValue.length > 0)
		{
			incorrectEmails = incorrectEmails.concat(validateMessageAddressString(bccValue));
		}

		if (incorrectEmails.length > 0)
		{
			var alertStr = Lang.WarningInputCorrectEmails + '\r\n' + Lang.WrongEmails;
			for (var i in incorrectEmails)
			{
				alertStr += '\r\n' + incorrectEmails[i];
			}
			alert(alertStr);
			return;
		}

		if (mode == SEND_MODE)
		{
			if (toValue.length < 1 && ccValue.length < 1 && bccValue.length < 1)
			{
				alert(Lang.WarningToBlank);
				return;
			}
			
		}
		if (WebMail.Settings.AllowBodySize) {
			if (this._counterObj.value < 0 && !confirm(Lang.ConfirmBodySize1 + ' ' + WebMail.Settings.MaxBodySize + ' ' + Lang.ConfirmBodySize2)) {
				return;
			}
		}
		var subjectValue = this._subjectObj.value;
		var save_anyway = true;
		if (mode == SEND_MODE && subjectValue.length == 0) {
		    save_anyway = confirm(Lang.ConfirmEmptySubject);
		}
		if (save_anyway) {
			var newMsg = new CMessage();
			newMsg.FromAddr = fromValue;
			newMsg.FromAcctId = fromAcctId;
			newMsg.ToAddr = toValue;
			newMsg.CCAddr = ccValue;
			if (this._hasBcc) {
				newMsg.BCCAddr = this._bccObj.value;
			}
			newMsg.Subject = subjectValue;
			newMsg.Importance = this._priority;
			newMsg.Sensivity = this._sensivity;
			
			if (WebMail.Settings.AllowDhtmlEditor && HtmlEditorField._htmlMode) {
				var value = HtmlEditorField.GetText();
				if (typeof(value) == 'string') {
					newMsg.HasHtml = true;
					newMsg.HtmlBody = value;
					if (WebMail.Settings.AllowBodySize && this._counterObj.value < 0) {
						newMsg.HtmlBody = this.TrunkHtmlBody(newMsg.HtmlBody);
					}
				}
			}
			else {
				newMsg.HasHtml = false;
				newMsg.PlainBody = this._plainEditorObj.value;
				if (WebMail.Settings.AllowBodySize) {
					newMsg.PlainBody = newMsg.PlainBody.substr(0, WebMail.Settings.MaxBodySize);
				}
			}
			
			if (this._flashFileUpload) {
				newMsg.Attachments = [];
				for (var i in SwfUploader.customSettings.attachments) {
					newMsg.Attachments.push(SwfUploader.customSettings.attachments[i]);
				}
				newMsg.Attachments = newMsg.Attachments.concat(this._inlineAttachments);
			}
			else {
				newMsg.Attachments = this._attachments.concat(this._inlineAttachments);
			}

			newMsg.Id = this._msgObj.Id;
			newMsg.Uid = this._msgObj.Uid;
			newMsg.ReplyMsg = this._msgObj.ReplyMsg;
			newMsg.SendersGroups = this._sendersGroups;
			newMsg.MailConfirmation = this._mailConfirmationCh.checked;
			if (this._mailSaveCh != null){
			    newMsg.SaveMail = this._mailSaveCh.checked;
            }

			if (!this._checkMsgSize(newMsg)) {
				this._showMailboxSizeWarning(mode);
				return;
			}
			
			var xml = newMsg.GetInXML();
			switch (mode) {
			    case SEND_MODE:
					WebMail.DataSource.Cache.ClearAllContactsGroupsList();
			        RequestHandler('send', 'message', xml);
				    this._sending = true;
				    this._sendTool.Disable();
				    break;
				case SAVE_MODE:
				    RequestHandler('save', 'message', xml);
				    this._saving = true;
				    this._saveTool.Disable();
				    break;
			}
			
			if (window.opener) {
				window.opener.MarkMsgAsRepliedHandler(newMsg);
			}
			else {
				MarkMsgAsRepliedHandler(newMsg);
			}
		}
	},//SaveChanges
	
	SwitchBccMode: function ()
	{
		if (this._hasBcc) {
			this._bccSwitcher.innerHTML = Lang.ShowBCC;
			this._hasBcc = false;
			this._bccCont.className = 'wm_hide';
		}
		else {
			this._bccSwitcher.innerHTML = Lang.HideBCC;
			this._hasBcc = true;
			this._bccCont.className = '';
		}
		if (Browser.Gecko) {
			this.ResizeBody();
		}
		else {
			HtmlEditorField.Replace();
		}
	},
	
	CreateDeleteAttachmentClick: function (index, obj)
	{
		return function () {obj.DeleteAttachment(index);return false;};
	},
	
	LoadAttachment: function (attachment)
	{
		if (attachment.Inline) {
			HtmlEditorField.InsertImageFromWindow(HtmlDecode(attachment.Url));
			this._inlineAttachments.push(attachment);
			return;
		}
		if (this._flashFileUpload) {
			return;
		}
		var obj = this;
		var tbl = this._attachmentsTbl;
		var tr = tbl.insertRow(this._rowIndex);
		var td = tr.insertCell(0);
		td.className = 'wm_attachment';
		var params = GetFileParams(attachment.FileName);
		CreateChild(td, 'div', [['class', 'wm_attachment_image'],
			['style', 'background-position: -' + params.x + 'px -' + params.y + 'px']]);
		var span = CreateChild(td, 'span');
		span.innerHTML = attachment.FileName + '&nbsp;(' + GetFriendlySize(attachment.Size) + ')&nbsp;';
		var a = CreateChild(td, 'a', [['href', '#']]);
		a.onclick = this.CreateDeleteAttachmentClick(this._rowIndex, obj);
		a.innerHTML = Lang.Delete;
		this._attachments[this._rowIndex] = attachment;
		this._rowIndex++;
		this.RebuildUploadForm();
		if (Browser.Gecko) {
			this.ResizeBody();
		}
	},
	
	DeleteAttachment: function (index)
	{
		delete this._attachments[index];
		var attachs = this._attachments;
		this.RedrawAttachments(attachs);
		if (Browser.Gecko) {
			this.ResizeBody();
		}
	},
	
	RedrawAttachments: function (attachs)
	{
		if (this._flashFileUpload) {
			return;
		}
		CleanNode(this._attachmentsDiv);
		this._attachmentsTbl = CreateChild(this._attachmentsDiv, 'table');
		this._attachmentsTbl.className = 'wm_new_message_attachments';
		this._attachments = [];
		this._rowIndex = 0;
		for (var i in attachs) {
			this.LoadAttachment(attachs[i]);
		}
	},
	
	ChangePriority: function ()
	{
		var pr = this._priority;
		switch (pr) {
			case PRIORITY_LOW:
				this.SetPriority(PRIORITY_NORMAL);
				break;
			case PRIORITY_NORMAL:
				this.SetPriority(PRIORITY_HIGH);
				break;
			case PRIORITY_HIGH:
				this.SetPriority(PRIORITY_LOW);
				break;
		}
	},

	ChangeSensivity: function ()
	{
		var sensi = this._sensivity;
		switch (sensi) {
			case SENSIVITY_NOTHING:
			case SENSIVITY_CONFIDENTIAL:
			case SENSIVITY_PRIVATE:
			case SENSIVITY_PERSONAL:
				this.SetSensivity(sensi);
				break;
		}
	},

	SetSensivity: function (sensi)
	{
		switch (sensi) {
			case SENSIVITY_NOTHING:
				this._sensivity = SENSIVITY_NOTHING;
				this._sensitiveNothingButton.ChangeClassName('wm_menu_item_importance wm_importance_menu', 'wm_menu_item_over_importance wm_importance_menu');
				this._sensivityConfidentialButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				this._sensivityPrivateButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				this._sensivityPersonalButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				break;
			case SENSIVITY_CONFIDENTIAL:
				this._sensivity = SENSIVITY_CONFIDENTIAL;
				this._sensitiveNothingButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				this._sensivityConfidentialButton.ChangeClassName('wm_menu_item_importance wm_importance_menu', 'wm_menu_item_over_importance wm_importance_menu');
				this._sensivityPrivateButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				this._sensivityPersonalButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				break;
			case SENSIVITY_PRIVATE:
				this._sensivity = SENSIVITY_PRIVATE;
				this._sensitiveNothingButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				this._sensivityConfidentialButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				this._sensivityPrivateButton.ChangeClassName('wm_menu_item_importance wm_importance_menu', 'wm_menu_item_over_importance wm_importance_menu');
				this._sensivityPersonalButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				break;
			case SENSIVITY_PERSONAL:
				this._sensivity = SENSIVITY_PERSONAL;
				this._sensitiveNothingButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				this._sensivityConfidentialButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				this._sensivityPrivateButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				this._sensivityPersonalButton.ChangeClassName('wm_menu_item_importance wm_importance_menu', 'wm_menu_item_over_importance wm_importance_menu');
				break;
			default:
				alert([sensi, SENSIVITY_NOTHING, SENSIVITY_CONFIDENTIAL, SENSIVITY_PRIVATE, SENSIVITY_PERSONAL]);
				break;
		}
	},
	
	SetPriority: function (pr)
	{
		switch (pr) {
			case PRIORITY_LOW:
				this._priority = PRIORITY_LOW;
				this._priorityLowButton.ChangeClassName('wm_menu_item_importance wm_importance_menu', 'wm_menu_item_over_importance wm_importance_menu');
				this._priorityNormalButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				this._priorityHighButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
			break;
			case PRIORITY_NORMAL:
				this._priority = PRIORITY_NORMAL;
				this._priorityLowButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				this._priorityNormalButton.ChangeClassName('wm_menu_item_importance wm_importance_menu', 'wm_menu_item_over_importance wm_importance_menu');
				this._priorityHighButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
			break;
			case PRIORITY_HIGH:
				this._priority = PRIORITY_HIGH;
				this._priorityLowButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				this._priorityNormalButton.ChangeClassName('wm_menu_item wm_importance_menu', 'wm_menu_item_over wm_importance_menu');
				this._priorityHighButton.ChangeClassName('wm_menu_item_importance wm_importance_menu', 'wm_menu_item_over_importance wm_importance_menu');
			break;
			default:
				alert([pr, PRIORITY_LOW, PRIORITY_NORMAL, PRIORITY_HIGH]);
				break;
		}
	},
	
	_buildToolBar: function (container, popupMenus)
	{
		var obj = this;
		var toolBar = new CToolBar(container);
		this._toolBar = toolBar;

		if (!window.opener) {
			toolBar.AddItem(TOOLBAR_BACK_TO_LIST, function () {
				SetHistoryHandler(
					{
						ScreenId: WebMail.ListScreenId,
						FolderId: null
					}
				);
			}, true);
		}
		this._sendTool = toolBar.AddItem(TOOLBAR_SEND_MESSAGE, function () {obj.SaveChanges(0);}, true);
		this._saveTool = toolBar.AddItem(TOOLBAR_SAVE_MESSAGE, function () {obj.SaveChanges(1);}, true);

		function createSetPriorFunc(obj, pr)
		{
		    return function () {obj.SetPriority(pr);};
		}

		var div = CreateChild(document.body, 'div');
		div.className = 'wm_hide';
		var buttons = toolBar.AddImportanceItem(popupMenus, div);
		this._priorityLowButton = buttons.Low;
		this._priorityLowButton.ChangeHandler(createSetPriorFunc(obj, PRIORITY_LOW));
		this._priorityNormalButton = buttons.Normal;
		this._priorityNormalButton.ChangeHandler(createSetPriorFunc(obj, PRIORITY_NORMAL));
		this._priorityHighButton = buttons.High;
		this._priorityHighButton.ChangeHandler(createSetPriorFunc(obj, PRIORITY_HIGH));

		function createSetSensivityFunc(obj, sensi)
		{
		    return function () {obj.SetSensivity(sensi);};
		}

		var div2 = CreateChild(document.body, 'div');
		div2.className = 'wm_hide';
		var rbuttons = toolBar.AddSensivityItem(popupMenus, div2);
		this._sensitiveNothingButton = rbuttons.Nothing;
		this._sensitiveNothingButton.ChangeHandler(createSetSensivityFunc(obj, SENSIVITY_NOTHING));
		this._sensivityConfidentialButton = rbuttons.Confidential;
		this._sensivityConfidentialButton.ChangeHandler(createSetSensivityFunc(obj, SENSIVITY_CONFIDENTIAL));
		this._sensivityPrivateButton = rbuttons.Private;
		this._sensivityPrivateButton.ChangeHandler(createSetSensivityFunc(obj, SENSIVITY_PRIVATE));
		this._sensivityPersonalButton = rbuttons.Personal;
		this._sensivityPersonalButton.ChangeHandler(createSetSensivityFunc(obj, SENSIVITY_PERSONAL));

		toolBar.AddItem(TOOLBAR_CANCEL, function () {
			if (window.opener) {
				window.close();
				return;
			}
			SetHistoryHandler(
				{
					ScreenId: WebMail.ListScreenId,
					FolderId: null
				}
			);
		}, true);

		toolBar.AddClearDiv();
		toolBar.Hide();
	},

	RebuildUploadForm: function ()
	{
		if (this._flashFileUpload) {
			return;
		}
		var form = this._uploadForm;
		CleanNode(form);
		var span = CreateChild(form, 'span');
		span.innerHTML = Lang.AttachFile + ':&nbsp;';
		var inp = CreateChild(form, 'input', [['type', 'file'], ['class', 'wm_file'], ['name', 'Filedata']]);
		this._uploadFile = inp;
		inp = CreateChild(form, 'input', [['type', 'hidden'], ['value', '0'], ['name', 'inline_image']]);
		inp = CreateChild(form, 'input', [['type', 'hidden'], ['value', '0'], ['name', 'flash_upload']]);
		span = CreateChild(form, 'span');
		span.innerHTML = '&nbsp;';
		inp = CreateChild(form, 'input', [['type', 'submit'], ['class', 'wm_button'], ['value', Lang.Attach]]);
	},
	
	Build: function (container, accountsBar, popupMenus)
	{
		this._logo = document.getElementById('logo');
		this._accountsBar = accountsBar;

		this._buildToolBar(container, popupMenus);
		
		this._mainContainer = CreateChild(container, 'div');
		this._mainContainer.className = 'wm_hide';

		//this._picturesControl.Build(this._mainContainer);
		var tbl = CreateChild(this._mainContainer, 'table');
		this._headersTbl = tbl;
		tbl.className = 'wm_new_message';
		tbl.id = 'wm_new_message';
		var RowIndex = 0;
		
		

		tr = tbl.insertRow(RowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_new_message_title';
		td.innerHTML = Lang.From + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'From', ':');
		td = tr.insertCell(1);
		var sel = CreateChild(td, 'select', [['class', 'wm_input'], ['style', 'width: 585px; padding-left:0px;']]);
		sel.tabIndex = 1;
		this._fromObj = sel;
		this._fromCont = tr;
		
		tr = tbl.insertRow(RowIndex++);
		td = tr.insertCell(0);
		td.className = 'wm_new_message_title';
		var a = CreateChild(td, 'a', [['href', '#']]);
        CreateTextChild(a, Lang.To);
        CreateTextChild(td, ':');
		WebMail.LangChanger.Register('innerHTML', td, 'To', ':');
		td = tr.insertCell(1);
		var inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['style', 'width: 580px']]);
		inp.tabIndex = 2;
		var obj = this;
		inp.onfocus = function () {
			obj.AutoFilling.SetSuggestInput(this);
		};
		inp.onchange = function() {
			obj.isSavedOrSent = false;
		};
		this._toObj = inp;
		a.onclick = function () {
		    obj.PopupContacts.ControlClick(obj._toObj, this);
		    return false;
		};
		
		tr = tbl.insertRow(RowIndex++);
		td = tr.insertCell(0);
		td.className = 'wm_new_message_title';
		a = CreateChild(td, 'a', [['href', '#']]);
        CreateTextChild(a, Lang.CC);
        CreateTextChild(td, ':');
		WebMail.LangChanger.Register('innerHTML', td, 'CC', ':');
		td = tr.insertCell(1);
		var nobr = CreateChild(td, 'nobr');
		inp = CreateChild(nobr, 'input', [['type', 'text'], ['class', 'wm_input'], ['style', 'width: 580px']]);
		inp.tabIndex = 3;
		inp.onfocus = function () {
			obj.AutoFilling.SetSuggestInput(this);
		};
		this._ccObj = inp;
		inp.onchange = function() {
			obj.isSavedOrSent = false;
		};
		a.onclick = function () {
		    obj.PopupContacts.ControlClick(obj._ccObj, this);
		    return false;
		};

		var span = CreateChild(nobr, 'span');
		span.innerHTML = '&nbsp;';
		a = CreateChild(nobr, 'a', [['href', '#']]);
		a.onclick = function () {obj.SwitchBccMode();return false;};
		a.innerHTML = Lang.ShowBCC;
		WebMail.LangChanger.Register('innerHTML', a, 'ShowBCC', '');
		a.tabIndex = -1;
		this._bccSwitcher = a;
		this._hasBcc = false;

		tr = tbl.insertRow(RowIndex++);
		tr.className = 'wm_hide';
		td = tr.insertCell(0);
		td.className = 'wm_new_message_title';
		a = CreateChild(td, 'a', [['href', '#']]);
        CreateTextChild(a, Lang.BCC);
        CreateTextChild(td, ':');
		WebMail.LangChanger.Register('innerHTML', td, 'BCC', ':');
		td = tr.insertCell(1);
		inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['style', 'width: 580px']]);
		inp.tabIndex = 4;
		inp.onfocus = function () {
			obj.AutoFilling.SetSuggestInput(this);
		};
		inp.onchange = function() {
			obj.isSavedOrSent = false;
		};
		this._bccObj = inp;
		this._bccCont = tr;
		a.onclick = function () {
		    obj.PopupContacts.ControlClick(obj._bccObj, this);
		    return false;
		};

		tr = tbl.insertRow(RowIndex++);
		td = tr.insertCell(0);
		td.className = 'wm_new_message_title';
		td.innerHTML = Lang.Subject + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Subject', ':');
		td = tr.insertCell(1);
		inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['style', 'width: 580px'], ['maxlength', WebMail.Settings.MaxSubjectSize]]);
		inp.tabIndex = 5;
		inp.onfocus = function () {
			obj.AutoFilling.Hide();
		};
		inp.onchange = function() {
			obj.isSavedOrSent = false;
		};
		this._subjectObj = inp;
		this._subjectCont = tr;

		tr = tbl.insertRow(RowIndex++);
		tr.className = 'last_row';
		td = tr.insertCell(0);
		td.className = 'wm_new_message_title';
		td.innerHTML = Lang.BodySizeCounter + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'BodySizeCounter', ':');
		td = tr.insertCell(1);
		inp = CreateChild(td, 'input', [['type', 'text'], ['class', 'wm_input'], ['size', '3'], ['value', WebMail.Settings.MaxBodySize]]);
		inp.disabled = true;
		this._counterObj = inp;
		this._counterCont = tr;

		tr = tbl.insertRow(RowIndex++);
		td = tr.insertCell(0);
		td.colSpan = 2;
		var div = CreateChild(td, 'div');
		div.className = 'wm_input wm_plain_editor_container';
		var txt = CreateChild(div, 'textarea');
		txt.className = 'wm_plain_editor_text';
		txt.tabIndex = 6;
		txt.onchange = function() {
			obj.isSavedOrSent = false;
		};
		this._plainEditorObj = txt;
		this._plainEditorDiv = div;
		this._plainEditorCont = td;

		tbl = CreateChild(this._mainContainer, 'table');
		tbl.className = 'wm_new_message';
		tr = tbl.insertRow(0);
		td = tr.insertCell(0);
		nobr = CreateChild(td, 'nobr');
		inp = CreateChild(nobr, 'input', [['id', 'chMailConfirmation'], ['type', 'checkbox'], ['class', 'wm_checkbox']]);
		var lbl = CreateChild(nobr, 'label', [['for', 'chMailConfirmation']]);
		lbl.innerHTML = Lang.RequestReadConfirmation;
		WebMail.LangChanger.Register('innerHTML', lbl, 'RequestReadConfirmation', '');
		this._mailConfirmationCh = inp;
		this._mailConfirmationCh.checked = false;

        var account = WebMail.Accounts.GetCurrentAccount();		
		if (account.SaveMail != 0){
		    tr = tbl.insertRow(0);
		    td = tr.insertCell(0);
		    nobr = CreateChild(td, 'nobr');
		    inp = CreateChild(nobr, 'input', [['id', 'chSaveMailInSentItems'], ['type', 'checkbox'], ['class', 'wm_checkbox']]);
		    var lbl = CreateChild(nobr, 'label', [['for', 'chSaveMailInSentItems']]);
		    lbl.innerHTML = Lang.SaveMailInSentItems;
		    WebMail.LangChanger.Register('innerHTML', lbl, 'SaveMailInSentItems', '');
		    this._mailSaveCh = inp;
		    this._mailSaveCh.checked = false;
            if (account.SaveMail == 1){
    		    this._mailSaveCh.checked = true;
            }
        }
        this._saveMailTr = tr;
        
		td = tr.insertCell(1);
		td.className = 'wm_html_editor_switcher';
		a = CreateChild(td, 'a', [['href', '#']]);
		a.innerHTML = Lang.SwitchToPlainMode;
		this._modeSwitcher = a;
		this._modeSwitcherCont = td;
		this._modeSwitcherTr = tr;

		if (flashInstalled == FLASH_INSTALLED) {
			this._flashFileUpload = true;
			div = CreateChild(this._mainContainer, 'div');
			div.className = 'wm_flash_attachment_uploading';
			CreateChild(div, 'div', [['class', 'wm_hide'], ['id', 'fsUploadProgress']]);
			var divButtonPlaceHolder = CreateChild(div, 'div');
			divButtonPlaceHolder.style.marginTop = '10px';
			CreateChild(divButtonPlaceHolder, 'span', [['id', 'spanButtonPlaceHolder']]);
			InitSwfUploader(this);
			this._attachmentsDiv = div;
		}
		else {
			this._flashFileUpload = false;
			div = CreateChild(this._mainContainer, 'div');
			with (div.style) {
				margin = '0px';
				padding = '0px';
			}
			this._attachmentsDiv = div;
			tbl = CreateChild(div, 'table');
			tbl.className = 'wm_new_message_attachments';
			this._attachmentsTbl = tbl;

			tbl = CreateChild(this._mainContainer, 'table');
			tbl.className = 'wm_new_message_attach';
			tr = tbl.insertRow(0);
			td = tr.insertCell(0);
			td.className = 'wm_attach';
			this._uploadForm = CreateChild(td, 'form', [['action', UploadUrl], ['method', 'post'], ['enctype', 'multipart/form-data'], ['target', 'UploadFrame'], ['id', 'UploadForm']]);
			this.RebuildUploadForm();
			this._uploadForm.onsubmit = function () {
				return (obj._uploadFile.value.length != 0);
			};
			this._uploadTbl = tbl;
		}
		CreateChild(document.body, 'iframe', [['src', EmptyHtmlUrl], ['name', 'UploadFrame'], ['id', 'UploadFrame'], ['class', 'wm_hide']]);
		
        this.PopupContacts = new CPopupContacts(GetAutoFillingContactsHandler, SelectSuggestionHandler);
		this.AutoFilling = new CPopupAutoFilling(GetAutoFillingContactsHandler, SelectSuggestionHandler);

		this.isBuilded = true;
	},//Build

	HasChanges : function ()
	{
		if ((this._toObj.value.length > 0
			|| this._ccObj.value.length > 0
			|| this._bccObj.value.length > 0) && !this.isSavedOrSent)
		{
			return true;
		}
		var count = 0;
		if ((null != this._msgObj) && this.shown && this._messageLoaded) {
			var msg = this._msgObj;
			if (WebMail.Settings.AllowDhtmlEditor && msg.HasHtml && HtmlEditorField._htmlMode) {
				var htmlText = HtmlEditorField.GetText();
				if (htmlText == false) return false;
				var textWithoutNodes = HtmlDecode(htmlText.replace(/<br *\/{0,1}>/gi, '\r\n').replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' '))
				count = textWithoutNodes.length - 1;
			}
			else {
				var text = this._plainEditorObj.value;
				count = text.length;
			}
		}
		if (count > 0 && !this.isSavedOrSent) return true;
		return false;
	},

	IsTextChanged : function (ev)
	{
		if (isTextChanged(ev)) this.isSavedOrSent = false;
	}
};

//CNewMessageScreen.prototype.FillMessageInfo = MessageListPrototype.FillMessageInfo;

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}
