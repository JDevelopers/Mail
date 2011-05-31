/*
 * Classes:
 *  CAccounts()
 *  CMessage()
 *  COperationMessages()
 *  CMessageHeaders()
 *  CMessages()
 *  CMessagesBodies()
 *  CFolder(level, listHide)
 *  CFolderList()
 *  CUpdate()
 */

function CAccounts(account)
{
	this.Type = TYPE_ACCOUNT_LIST;
	this.CurrId = null;
	this.EditableId = null;
	this.CurrMailProtocol = POP3_PROTOCOL;
	this.Items = [];
	if (account != undefined) {
		this.Items.push(account);
		this.CurrId = account.Id;
		this.EditableId = account.Id;
		this.CurrMailProtocol = account.MailProtocol;
	}
	this.Count = 0;
}

CAccounts.prototype = {
	GetStringDataKeys: function()
	{
		return '';
	},
	
	GetAccountById: function (id)
	{
		for (var i = this.Items.length - 1; i >= 0; i--) {
			if (this.Items[i].Id == id) {
				return this.Items[i];
			}
		}
		return null;
	},
	
	GetCurrentAccount: function ()
	{
		return this.GetAccountById(this.CurrId);
	},
	
	GetEditableAccount: function ()
	{
		return this.GetAccountById(this.EditableId);
	},
	
	UpdateEditableSignature: function (signature, signatureOpt, isHtmlSignature)
	{
		for (var i = this.Items.length - 1; i >= 0; i--) {
			if (this.Items[i].Id == this.EditableId) {
				this.Items[i].Signature = signature;
				this.Items[i].SignatureOpt = signatureOpt;
				this.Items[i].SignatureType = isHtmlSignature ? SIGNATURE_TYPE_HTML : SIGNATURE_TYPE_PLAIN;
			}
		}
	},
	
	UpdateFilters: function (filters)
	{
		for (var i = this.Items.length - 1; i >= 0; i--) {
			if (this.Items[i].Id == filters.Id) {
				this.Items[i].Filters = filters.Items;
			}
		}
	},

	DeleteCurrFilters: function ()
	{
		for (var i = this.Items.length - 1; i >= 0; i--) {
			if (this.Items[i].Id == this.CurrId) {
				this.Items[i].Filters = null;
			}
		}
	},

	GetEditableFilters: function ()
	{
		for (var i = this.Items.length - 1; i >= 0; i--) {
			if (this.Items[i].Id == this.EditableId) {
				return this.Items[i].Filters;
			}
		}
		return null;
	},

	HasAccount: function (id)
	{
		for (var i = this.Items.length - 1; i >= 0; i--) {
			if (this.Items[i].Id == id) {
				return true;
			}
		}
		return false;
	},
	
	GetAccountProtocol: function (id)
	{
		for (var i = this.Items.length - 1; i >= 0; i--) {
			if (this.Items[i].Id == id) {
				return this.Items[i].MailProtocol;
			}
		}
		return -1;
	},

	GetAccountInternal: function (id)
	{
		for (var i = this.Items.length - 1; i >= 0; i--) {
			if (this.Items[i].Id == id) {
				return this.Items[i].IsInternal;
			}
		}
		return false;
	},
	
	GetAccountIdByFullEmail: function (fullEmail)
	{
		var emailParts = GetEmailParts(fullEmail);
		var email = emailParts.Email;
		for (var i = this.Items.length - 1; i >= 0; i--) {
			if (this.Items[i].Email == email) {
				return this.Items[i].Id;
			}
		}
		return this.CurrId;
	},

	SetAccountDefOrder: function (id, defOrder)
	{
		for (var i = this.Items.length - 1; i >= 0; i--) {
			if (this.Items[i].Id == id){
				this.Items[i].DefOrder = defOrder;
			}
		}
	},
	
	ChangeCurrAccount: function (id)
	{
		for (var i = this.Items.length - 1; i >= 0; i--) {
			if (this.Items[i].Id == id){
				this.CurrId = id;
				this.CurrMailProtocol = this.Items[i].MailProtocol;
				return;
			}
		}
	},
	
	ChangeEditableAccount: function (id)
	{
		this.EditableId = id;
	},
	
	GetDefCount: function ()
	{
		var defCount = 0;
		for (var i = this.Items.length - 1; i >= 0; i--) {
			if (this.Items[i].DefAcct) {
				defCount++;
			}
		}
		return defCount;
	},
	
	AplyNewAccountProperties: function (newAcctProp)
	{
		for (var i = this.Items.length - 1; i >= 0; i--) {
			if (this.Items[i].Id == newAcctProp.Id) {
				return this.Items[i].AplyNewAccountProperties(newAcctProp);
			}
		}
		return false;
	},

	GetFromXML: function(RootElement)
	{
		var attr = RootElement.getAttribute('last_id');
		if (attr) this.EditableId = attr - 0;
		attr = RootElement.getAttribute('curr_id');
		if (attr) this.CurrId = attr - 0;
		if (this.EditableId == null || this.EditableId == -1 ) {
			this.EditableId = this.CurrId;
		}
		var AccountsParts = RootElement.childNodes;
		for (var i=0; i<AccountsParts.length; i++) { 
			if (AccountsParts[i].tagName == 'account') {
		        var account = new CAccountProperties();
		        account.GetFromXML(AccountsParts[i]);
				if (account.Id == this.CurrId) {
					this.CurrMailProtocol = account.MailProtocol;
				}
				this.Items.push(account);
			} // if
		} // for
		this.Count = this.Items.length;
	} // GetFromXML
};

// for message
function CMessage()
{
	this.Type = TYPE_MESSAGE;
	this.Parts = 0;
	//	0 - Common Headers
	//	1 - HtmlBody
	//	2 - PlainBody
	//	3 - FullHeaders
	//	4 - Attachments
	//	5 - ReplyHtml;
	//	6 - ReplyPlain;
	//	7 - ForwardHtml;
	//	8 - ForwardPlain;
	this.FolderId = -1;
	this.FolderFullName = '';
	this.Size = 0;

	this.Id = -1;
	this.Uid = '';
	this.HasHtml = true;
	this.HasPlain = false;
	this.IsReplyHtml = false;
	this.IsReplyPlain = false;
	this.IsForwardHtml = false;
	this.IsForwardPlain = false;
	this.Importance = PRIORITY_NORMAL;
	this.Sensivity = SENSIVITY_NOTHING;
	this.Charset = AUTOSELECT_CHARSET;
	this.HasCharset = true;
	this.RTL = false;
	this.Safety = SAFETY_MESSAGE;
	this.Downloaded = false;
	
	// Common Headers
	this.FromAddr = '';
	this.FromDisplayName = '';
	this.FromContact = null;
	this.FromAcctId = -1;
	this.ToAddr = '';
	this.ShortToAddr = '';
	this.CCAddr = '';
	this.BCCAddr = '';
	this.SendersGroups = Array();//for auto-filling To, CC, BCC fields
	this.ReplyToAddr = '';//if it's equal with from, set empty value
	this.Subject = '';
	this.Date = '';
	this.FullDate = '';
	this.Time = '';
	this.MailConfirmation = false;
	this.MailConfirmationValue = null;
	this.SaveMail = true;

	// Body
	this.HtmlBody = '';
	this.PlainBody = '';
	this.ClearPlainBody = '';

	// Body for reply
	this.ReplyHtml = '';
	this.ReplyPlain = '';

	// Body for forward
	this.ForwardHtml = '';
	this.ForwardPlain = '';

	// FullHeaders
	this.FullHeaders = '';
	
	// Attachments - array of objects with fields FileName, Size[, Id, Download, View] (for getting) [, TempName, MimeType] (for sending)
	this.Attachments = [];
	
	this.SaveLink = '#';
	this.PrintLink = '#';
	
	this.ReplyMsg = null;
	
	this.NoReply = false;
	this.NoReplyAll = false;
	this.NoForward = false;
}	

CMessage.prototype = {
	GetStringDataKeys: function ()
	{
		var arDataKeys = [ this.Id, this.Charset, this.Uid, this.FolderId, this.FolderFullName ];
		return arDataKeys.join(STR_SEPARATOR);
	},
	
	IsEqual: function (msg)
	{
		if (msg == null) return false;
		if (this.Id == msg.Id && this.Charset == msg.Charset && this.Uid == msg.Uid 
			&& this.FolderId == msg.FolderId && this.FolderFullName == msg.FolderFullName)
		{
			return true;
		}
		return false;
	},
	
	IsCorrectData: function (msgId, msgUid, msgFolderId, msgFolderFullName, charset)
	{
		charset = (charset == undefined) ? this.Charset : charset;
		if (this.Id == msgId && this.Charset == charset && this.Uid == msgUid && this.FolderId == msgFolderId 
			&& this.FolderFullName == msgFolderFullName)
		{
			return true;
		}
		return false;
	},
	
	GetFromIdForList: function (id)
	{
		var identifiers = id.split(STR_SEPARATOR);
		this.Id = identifiers[0];
		this.Uid = identifiers[1];
		this.FolderId = identifiers[2];
		this.FolderFullName = identifiers[3];
		this.Charset = identifiers[4];
	},

	GetIdForList: function(id)
	{
		var identifiers = [this.Id, this.Uid, this.FolderId, this.FolderFullName, this.Charset, id];
		return identifiers.join(STR_SEPARATOR);
	},

	SetSize: function(size)
	{
		this.Size = size;
	},

	SetMode: function(modeArray)
	{
        var mode = 0;
        for (var key in modeArray) {
            mode = (1 << modeArray[key]) | mode;
        }
        this.Parts = mode;
	},

	PrepareForEditing: function (msg)
	{
		this.Safety = msg.Safety;
		this.FolderId = msg.FolderId;
		this.FolderFullName = msg.FolderFullName;

		this.Id = msg.Id;
		this.Uid = HtmlDecode(msg.Uid);
		this.HasHtml = msg.HasHtml;
		this.HasPlain = msg.HasPlain;
		this.Importance = msg.Importance;
		this.Sensivity = msg.Sensivity;
		
		this.FromAddr = HtmlDecode(msg.FromAddr);
		this.ToAddr = HtmlDecode(msg.ToAddr);
		this.CCAddr = HtmlDecode(msg.CCAddr);
		this.BCCAddr = HtmlDecode(msg.BCCAddr);
		this.Subject = HtmlDecode(msg.Subject);
		this.Date = HtmlDecode(msg.Date);

		this.HtmlBody = msg.HtmlBody;
		this.PlainBody = msg.ClearPlainBody;

		this.Attachments = msg.Attachments;
		this.Size = msg.Size;
	},
	
	ParseEmailStr: function (recipients, fromAddr, onlyOne)
	{
		if (null == recipients)  return [];
		if (undefined == onlyOne) onlyOne = false;
		
		var arRecipients = Array();
		var sWorkingRecipients = Trim(HtmlDecode(recipients)) + " ";

		//var i, iCount;
		var emailStartPos = 0;
		var emailEndPos = 0;

		var isInQuotes = false;
		var chQuote = '"';
		var isInAngleBrackets = false;
		var isInBrackets = false;

		var currentPos = 0;
		
		var sWorkingRecipientsLen = sWorkingRecipients.length;
		
		while (currentPos < sWorkingRecipientsLen) {
			var currentChar = sWorkingRecipients.substring(currentPos, currentPos+1);
			switch (currentChar) {
				case '\'':
				case '"':
					if (!isInQuotes) {
						chQuote = currentChar;
						isInQuotes = true;
					}
					else if (chQuote == currentChar) {
						isInQuotes = false;
					}
				break;
				case '<':
					if (!isInAngleBrackets) {
						isInAngleBrackets = true;
					}
				break;
				case '>':
					if (isInAngleBrackets) {
						isInAngleBrackets = false;
					}
				break;
				case '(':
					if (!isInBrackets) {
						isInBrackets = true;
					}
				break;
				case ')':
					if (isInBrackets) {
						isInBrackets = false;
					}
				break;
				default:
				    if (currentChar != ',' && currentChar != ';' && currentPos != (sWorkingRecipientsLen-1)) break;
					if (!isInAngleBrackets && !isInBrackets && !isInQuotes) {
						emailEndPos = (currentPos != (sWorkingRecipientsLen-1)) ? currentPos : sWorkingRecipientsLen;
						var str = sWorkingRecipients.substring(emailStartPos, emailEndPos);
						if (Trim(str).length > 0) {
							var sRecipient = GetEmailParts(str);
							var inList = false;
							var jCount = arRecipients.length;
							for (var j = 0; j < jCount; j++) {
								if (arRecipients[j].Email == sRecipient.Email) inList = true;
							}
							if (!inList) {
								arRecipients.push(sRecipient);
							}
						}
						emailStartPos = currentPos + 1;
					}
				break;
			}
			currentPos++;
		}
		
		var iCount = arRecipients.length;
		Recipients = Array();
		var fromRecipient = GetEmailParts(fromAddr);
		for (var i = 0; i < iCount; i++) {
			//if (iCount > 1) {
				if (fromRecipient.Email != arRecipients[i].Email) {
					Recipients.push(Trim(arRecipients[i].FullEmail));
					if (onlyOne) break;
				}
			//} else {
			//	Recipients.push(Trim(arRecipients[i].FullEmail));
			//	if (onlyOne) break;
			//}
		}
		return Recipients.join(', ');
	},
	
	GetRecipientsFromEmailStr: function(recipients) {
		if (null == recipients) return [];

		var arRecipients = Array();
		var sWorkingRecipients = Trim(HtmlDecode(recipients)) + " ";

		//var i, iCount;
		var emailStartPos = 0;
		var emailEndPos = 0;

		var isInQuotes = false;
		var chQuote = '"';
		var isInAngleBrackets = false;
		var isInBrackets = false;

		var currentPos = 0;

		var sWorkingRecipientsLen = sWorkingRecipients.length;

		while (currentPos < sWorkingRecipientsLen) {
			var currentChar = sWorkingRecipients.substring(currentPos, currentPos + 1);
			switch (currentChar) {
				case '\'':
				case '"':
					if (!isInQuotes) {
						chQuote = currentChar;
						isInQuotes = true;
					}
					else if (chQuote == currentChar) {
						isInQuotes = false;
					}
					break;
				case '<':
					if (!isInAngleBrackets) {
						isInAngleBrackets = true;
					}
					break;
				case '>':
					if (isInAngleBrackets) {
						isInAngleBrackets = false;
					}
					break;
				case '(':
					if (!isInBrackets) {
						isInBrackets = true;
					}
					break;
				case ')':
					if (isInBrackets) {
						isInBrackets = false;
					}
					break;
				default:
					if (currentChar != ',' && currentChar != ';' && currentPos != (sWorkingRecipientsLen - 1)) break;
					if (!isInAngleBrackets && !isInBrackets && !isInQuotes) {
						emailEndPos = (currentPos != (sWorkingRecipientsLen - 1)) ? currentPos : sWorkingRecipientsLen;
						var str = sWorkingRecipients.substring(emailStartPos, emailEndPos);
						if (Trim(str).length > 0) {
							var sRecipient = GetEmailParts(str);
							var inList = false;
							var jCount = arRecipients.length;
							for (var j = 0; j < jCount; j++) {
								if (arRecipients[j].Email == sRecipient.Email) inList = true;
							}
							if (!inList) {
								arRecipients.push(sRecipient);
							}
						}
						emailStartPos = currentPos + 1;
					}
					break;
			}
			currentPos++;
		}

		return arRecipients;
	},

	PrepareCommonReplyParts: function (msg)
	{
		this.HasHtml = msg.IsReplyHtml;
		this.HasPlain = msg.IsReplyPlain;
		this.HtmlBody = (msg.Sensivity != SENSIVITY_NOTHING) ? '' : msg.ReplyHtml;
		this.PlainBody = (msg.Sensivity != SENSIVITY_NOTHING) ? '' : '\n\n' + msg.ReplyPlain;
		this.Subject = (msg.Sensivity != SENSIVITY_NOTHING) ? '' : Lang.Re + ': ' + HtmlDecode(msg.Subject);
        if (!msg.HasPlain && msg.HasHtml) {
			this.Attachments = [];
			var iCount = msg.Attachments.length;
			var j = 0;
			for (var i=0; i<iCount; i++) {
				if (msg.Attachments[i].Inline) {
					this.Attachments[j] = msg.Attachments[i];
					j++;
				}
			}
		}
	},
	
	PrepareForReply: function (msg, replyAction, fromAddr)
	{
		replyAction = replyAction - 0;
		this.ReplyMsg = {Action: replyAction, Id: msg.Id, Uid: msg.Uid, 
		    FolderId: msg.FolderId, FolderFullName: msg.FolderFullName};
		this.Safety = msg.Safety;
		this.FromAddr = '';
		this.Date = '';
		this.Size = msg.Size;
		switch (replyAction) {
			case TOOLBAR_REPLY:
				this.ToAddr = (msg.ReplyToAddr.length > 0)
					? HtmlDecode(msg.ReplyToAddr) : HtmlDecode(msg.FromAddr);
				this.CCAddr = '';
				this.BCCAddr = '';
				this.PrepareCommonReplyParts(msg);
			break;
			case TOOLBAR_REPLYALL:
				this.ToAddr = (msg.ReplyToAddr.length > 0)
					? this.ParseEmailStr(msg.ReplyToAddr, fromAddr, true)
					: this.ParseEmailStr(msg.FromAddr, fromAddr, true);
				this.CCAddr = this.ParseEmailStr(
					this.ParseEmailStr(msg.ToAddr + ',' + msg.CCAddr, fromAddr), this.ToAddr);
				if (this.CCAddr == this.ToAddr) this.CCAddr = '';
				this.BCCAddr = this.ParseEmailStr(
					this.ParseEmailStr(msg.BCCAddr, fromAddr), this.ToAddr);
				if (this.BCCAddr == this.ToAddr || this.BCCAddr == this.CCAddr) this.BCCAddr = '';
				this.PrepareCommonReplyParts(msg);
			break;
			case TOOLBAR_FORWARD:
				this.HasHtml = msg.IsForwardHtml;
				this.HasPlain = msg.IsForwardPlain;
				this.HtmlBody = msg.ForwardHtml;
				this.PlainBody = msg.ForwardPlain;
				this.ToAddr = '';
				this.CCAddr = '';
				this.BCCAddr = '';
				this.Subject = Lang.Fwd + ': ' + HtmlDecode(msg.Subject);
				this.Attachments = msg.Attachments;
			break;
		}
	},//PrepareForReply
	
	GetInXML: function()
	{
		var strResult = '';
		var strHeaders = '';
		strHeaders += '<from>' + GetCData(this.FromAddr) + '</from>';
		strHeaders += '<to>' + GetCData(this.ToAddr) + '</to>';
		strHeaders += '<cc>' + GetCData(this.CCAddr) + '</cc>';
		strHeaders += '<bcc>' + GetCData(this.BCCAddr) + '</bcc>';
		strHeaders += '<subject>' + GetCData(this.Subject) + '</subject>';

		if (this.MailConfirmation)
		{
			strHeaders += '<mailconfirmation>' + GetCData(this.FromAddr) + '</mailconfirmation>';
		}

		var strGroups = '';
		var iCount = this.SendersGroups.length;
		for (var i=0; i<iCount; i++) {
			strGroups += '<group id="' + this.SendersGroups[i] + '" />';
		}
		strHeaders += '<groups>' + strGroups + '</groups>';
		strHeaders = '<headers>' + strHeaders + '</headers>';

		var strBody = (this.HasHtml)
			? '<body is_html="1">' + GetCData(this.HtmlBody, true) + '</body>'
			: '<body is_html="0">' + GetCData(this.PlainBody, true) + '</body>';
			
		var strAttachments = '';
		for (var j=0; j<this.Attachments.length; j++) {
			var Attachment = this.Attachments[j];
			var strAttachment = '';
			strAttachment += '<temp_name>' + GetCData(Attachment.TempName) + '</temp_name>';
			strAttachment += '<name>' + GetCData(Attachment.FileName) + '</name>';
			strAttachment += '<mime_type>' + GetCData(Attachment.MimeType) + '</mime_type>';
			var atAttrs = '';
			atAttrs += ' size="' + Attachment.Size + '"';
			if (Attachment.Inline)
				atAttrs += ' inline="1"';
			else
				atAttrs += ' inline="0"';
			strAttachments += '<attachment' + atAttrs + '>' + strAttachment + '</attachment>';
		}
		strAttachments = '<attachments>' + strAttachments + '</attachments>';
		var attrs = ' id="' + this.Id + '"';
		var uid = (this.Id != -1) ? '<uid>' + GetCData(HtmlDecode(this.Uid)) + '</uid>' : '<uid/>';

		if (this.FromAcctId != -1) {
			attrs += ' from_acct_id="' + this.FromAcctId + '"';
		}
		attrs += ' sensivity="' + this.Sensivity + '"';
		attrs += ' size="' + this.Size + '"';
		attrs += ' priority="' + this.Importance + '"';
		
		var save_mail = '0';
		if (this.SaveMail){
		    save_mail = '1'
		}
		
		attrs += ' save_mail="' + save_mail + '"';
		
		var replyMsg = '';
		if (null != this.ReplyMsg) {
		    var action = (this.ReplyMsg.Action == TOOLBAR_FORWARD) ? 'forward' : 'reply';
		    replyMsg = '<reply_message action="' + action + '" id="' + this.ReplyMsg.Id + '">';
		    replyMsg += '<uid>' + GetCData(this.ReplyMsg.Uid) + '</uid>';
		    replyMsg += '<folder id="' + this.ReplyMsg.FolderId + '">';
		    replyMsg += '<full_name>' + GetCData(this.ReplyMsg.FolderFullName) + '</full_name></folder></reply_message>';
		}
		strResult = '<message' + attrs + '>' + uid + strHeaders + strBody + strAttachments + replyMsg + '</message>';
		return strResult;
	},//GetInXML

	ShowPictures: function ()
	{
		if (this.HasHtml) {
			this.HtmlBody = this.HtmlBody.ReplaceStr('wmx_background', 'background');
			this.HtmlBody = this.HtmlBody.ReplaceStr('wmx_src', 'src');
			this.HtmlBody = this.HtmlBody.ReplaceStr('wmx_url(', 'url(');
		}
		if (this.IsReplyHtml) {
			this.ReplyHtml = this.ReplyHtml.ReplaceStr('wmx_background', 'background');
			this.ReplyHtml = this.ReplyHtml.ReplaceStr('wmx_src', 'src');
			this.ReplyHtml = this.ReplyHtml.ReplaceStr('wmx_url(', 'url(');
		}
		if (this.IsForwardHtml) {
			this.ForwardHtml = this.ForwardHtml.ReplaceStr('wmx_background', 'background');
			this.ForwardHtml = this.ForwardHtml.ReplaceStr('wmx_src', 'src');
			this.ForwardHtml = this.ForwardHtml.ReplaceStr('wmx_url(', 'url(');
		}
	},
	
	GetFromXML: function(RootElement)
	{
		this.HasHtml = false;
		var attr = RootElement.getAttribute('id');if (attr) this.Id = attr - 0;
		attr = RootElement.getAttribute('size');if (attr) this.Size = attr - 0;
		attr = RootElement.getAttribute('html');if (attr) this.HasHtml= (attr == 1) ? true : false;
		attr = RootElement.getAttribute('plain');if (attr) this.HasPlain = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('priority');if (attr) this.Importance = attr - 0;
		var safety = SAFETY_NOTHING;
		attr = RootElement.getAttribute('safety');if (attr) safety = attr - 0;
		var needShowPic = false;
		if (this.Parts == 0) {
		    this.Safety = safety;
		}
		else {
		    needShowPic = (safety == SAFETY_NOTHING && this.Safety > SAFETY_NOTHING);
		}
		attr = RootElement.getAttribute('mode');if (attr) this.Parts = this.Parts | (attr - 0);
		attr = RootElement.getAttribute('charset');if (attr) this.Charset = attr - 0;
		attr = RootElement.getAttribute('has_charset');if (attr) this.HasCharset = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('rtl');if (attr) this.RTL = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('downloaded');if (attr) this.Downloaded = (attr == 1) ? true : false;

		attr = RootElement.getAttribute('no_reply');if (attr) this.NoReply = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('no_reply_all');if (attr) this.NoReplyAll = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('no_forward');if (attr) this.NoForward = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('sensivity');if (attr) this.Sensivity = attr - 0;
		if (this.Sensivity != SENSIVITY_NOTHING) {
			this.ReplyHtml = '';
			this.ReplyPlain = '';
			this.ForwardHtml = '';
			this.ForwardPlain = '';
			this.Parts = this.Parts | (1 << PART_MESSAGE_REPLY_HTML) | (1 << PART_MESSAGE_REPLY_PLAIN);
			this.Parts = this.Parts | (1 << PART_MESSAGE_FORWARD_HTML) | (1 << PART_MESSAGE_FORWARD_PLAIN);
		}
		var MessageParts = RootElement.childNodes;
		for (var i=0; i<MessageParts.length; i++) {
			var part = MessageParts[i].childNodes;
			if (part.length == 0) continue;
			switch (MessageParts[i].tagName) {
				case 'uid':
					this.Uid = Trim(part[0].nodeValue);
					if (this.Uid == '-1') this.Uid = '';
				break;
				case 'folder':
					attr = MessageParts[i].getAttribute('id');
					if (attr) this.FolderId = attr - 0;
					this.FolderFullName = HtmlDecode(part[0].nodeValue);
				break;
				case 'headers':
					var HeadersParts = MessageParts[i].childNodes;
					for (var j=0; j<HeadersParts.length; j++) {
						var part_ = HeadersParts[j].childNodes;
						if (part_.length == 0) continue;
						switch (HeadersParts[j].tagName) {
							case 'from':
								attr = HeadersParts[j].getAttribute('contact_id');
								if (attr) this.FromContact = {Id: attr - 0};
								else this.FromContact = {Id: -1};
								var fromParts = HeadersParts[j].childNodes;
								for (var fromIndex=0; fromIndex<fromParts.length; fromIndex++) {
									var fromPartChilds = fromParts[fromIndex].childNodes;
									if (fromPartChilds.length == 0) continue;
									switch (fromParts[fromIndex].tagName) {
										case 'short':
											this.FromDisplayName = Trim(fromPartChilds[0].nodeValue);
											break;
										case 'full':
											this.FromAddr = Trim(fromPartChilds[0].nodeValue);
											break;
									}
								}
							break;
							case 'to':
								this.ToAddr = Trim(part_[0].nodeValue);
							break;
							case 'short_to':
								this.ShortToAddr = Trim(part_[0].nodeValue);
							break;
							case 'cc':
								this.CCAddr = Trim(part_[0].nodeValue);
							break;
							case 'bcc':
								this.BCCAddr = Trim(part_[0].nodeValue);
							break;
							case 'reply_to':
								this.ReplyToAddr = Trim(part_[0].nodeValue);
							break;
							case 'subject':
								this.Subject = Trim(part_[0].nodeValue);
							break;
							case 'mailconfirmation':
								this.MailConfirmationValue = Trim(part_[0].nodeValue);
							break;
							case 'short_date':
								this.Date = Trim(part_[0].nodeValue);
							break;
							case 'full_date':
								this.FullDate = Trim(part_[0].nodeValue);
							break;
							case 'time':
								this.Time = Trim(part_[0].nodeValue);
							break;
						}//switch
					}//for
				break;
				case 'html_part':
					this.HtmlBody = Trim(part[0].nodeValue);
					if (this.HtmlBody.length > 0) this.HasHtml = true;
				break;
				case 'modified_plain_text':
					this.PlainBody = Trim(part[0].nodeValue);
					this.ClearPlainBody = this.PlainBody;
					if (this.PlainBody.length > 0) this.HasPlain = true;
				break;
				case 'unmodified_plain_text':
					this.ClearPlainBody = Trim(part[0].nodeValue);
				break;
				case 'reply_html':
					this.ReplyHtml = Trim(part[0].nodeValue);
					this.ForwardHtml = this.ReplyHtml;
					if (this.ReplyHtml.length > 0) {
						this.IsReplyHtml = true;
						this.IsForwardHtml = true;
						this.Parts = (1 << PART_MESSAGE_FORWARD_HTML) | this.Parts;
					}
				break;
				case 'reply_plain':
					this.ReplyPlain = Trim(part[0].nodeValue);
					this.ForwardPlain = this.ReplyPlain;
					if (this.ReplyPlain.length > 0) {
						this.IsReplyPlain = true;
						this.IsForwardPlain = true;
						this.Parts = (1 << PART_MESSAGE_FORWARD_PLAIN) | this.Parts;
					}
				break;
				case 'forward_html':
					this.ForwardHtml = Trim(part[0].nodeValue);
					this.ReplyHtml = this.ForwardHtml;
					if (this.ForwardHtml.length > 0) {
						this.IsForwardHtml = true;
						this.IsReplyHtml = true;
						this.Parts = (1 << PART_MESSAGE_REPLY_HTML) | this.Parts;
					}
				break;
				case 'forward_plain':
					this.ForwardPlain = Trim(part[0].nodeValue);
					this.ReplyPlain = this.ForwardPlain;
					if (this.ForwardPlain.length > 0) {
						this.IsForwardPlain = true;
						this.IsReplyPlain = true;
						this.Parts = (1 << PART_MESSAGE_REPLY_PLAIN) | this.Parts;
					}
				break;
				case 'full_headers':
					this.FullHeaders = Trim(part[0].nodeValue);
				break;
				case 'attachments':
					var Attachments = MessageParts[i].childNodes;
					this.Attachments = [];
					for (j=0; j<Attachments.length; j++) {
						var id = -1;
						attr = Attachments[j].getAttribute('id');
						if (attr) id = attr - 0;
						var size = 0;
						attr = Attachments[j].getAttribute('size');
						if (attr) size = attr;
						var inline = false;
						attr = Attachments[j].getAttribute('inline');
						if (attr) inline = (attr == 1) ? true : false;
						var References = Attachments[j].childNodes;
						var fileName = '';var tempName = '';
						var download = '#';var view = '#';
						var mimeType = '';
						var refCount = References.length;
						for (var k = refCount-1; k >= 0; k--) {
							var ref = References[k].childNodes;
							if (ref.length > 0 )
								switch (References[k].tagName) {
									case 'filename':
										fileName = Trim(ref[0].nodeValue);
										break;
									case 'tempname':
										tempName = Trim(ref[0].nodeValue);
										break;
									case 'mime_type':
										mimeType = Trim(ref[0].nodeValue);
										break;
									case 'download':
										download = HtmlDecode(Trim(ref[0].nodeValue));
										if (download == '') download = '#';
										break;
									case 'view':
										view = HtmlDecode(Trim(ref[0].nodeValue));
										if (view == '') view = '#';
										break;
								}//switch
						}//for
						this.Attachments.push({Id: id, Inline: inline, FileName: fileName, Size: size, Download: download, View: view, TempName: tempName, MimeType: mimeType});
					}//for 
				break;
				case 'save_link':
					var links = MessageParts[i].childNodes;
					if (links.length > 0)	{
						this.SaveLink = HtmlDecode(Trim(links[0].nodeValue));
					}
				break;
				case 'print_link':
					var links = MessageParts[i].childNodes;
					if (links.length > 0) {
						this.PrintLink = HtmlDecode(Trim(links[0].nodeValue));
					}
				break;
			}//switch
		}//for
		if (needShowPic) {
		    this.ShowPictures();
		}
	},//GetFromXML

    AttachmentsToString: function ()
    {
        var str = '';
        for (var i=0; i<this.Attachments.length; i++) {
            var attach = this.Attachments[i];
            str += i + ', {Id: ' + attach.Id + ', ';
            str += 'Inline: ' + attach.Inline + ', ';
            str += 'FileName: ' + attach.FileName + ', ';
            str += 'Download: ' + attach.Download + ', ';
            str += 'View: ' + attach.View + ', ';
            str += 'TempName: ' + attach.TempName + ', ';
            str += 'MimeType: ' + attach.MimeType + '};\n';
        }
        return str;
    }
};

function COperationMessages()
{
	this.Type = TYPE_MESSAGES_OPERATION;
	this.OperationType = '';
	this.OperationField = '';
	this.OperationValue = true;
	this.OperationInt = -1;
	this.isAllMess = false;
	this.FolderId = -1;
	this.FolderFullName = '';
	this.ToFolderId = -1;
	this.ToFolderFullName = '';
	this.Messages = new CDictionary();
	this.GetMessageAfterDelete = false;
	this.IsMoveError = false;
}

COperationMessages.prototype = {
	GetInXML: function ()
	{
		var getmsg = (this.GetMessageAfterDelete) ? 1 : 0;
		var nodes = '<messages getmsg="' + getmsg + '">';
		nodes += '<look_for fields="0">' + GetCData('') + '</look_for>';
		nodes += '<to_folder id="' + this.ToFolderId + '"><full_name>' + GetCData(this.ToFolderFullName) + '</full_name></to_folder>';
		nodes += '<folder id="' + this.FolderId + '"><full_name>' + GetCData(this.FolderFullName) + '</full_name></folder>';
		var keys = this.Messages.keys();
		var iCount = keys.length;
		for (var i=0; i<iCount; i++) {
			var msg = this.Messages.getVal(keys[i]);
			var jCount = msg.IdArray.length;
			for (var j=0; j<jCount; j++) {
				nodes += '<message id="' + msg.IdArray[j].Id + '" charset="' + msg.IdArray[j].Charset + '" size="' + msg.IdArray[j].Size + '">';
				nodes += '<uid>' + GetCData(msg.IdArray[j].Uid) + '</uid>';
				nodes += '<folder id="' + msg.FolderId + '"><full_name>' + GetCData(msg.FolderFullName) + '</full_name></folder>';
				nodes += '</message>';
			}
		}
		nodes += '</messages>';
		return nodes;
	},
	
	GetFromXML: function (RootElement)
	{
		var attr = RootElement.getAttribute('type');
		if (attr) this.OperationType = attr;
		this.GetOperation();
		this.isAllMess = (this.OperationInt == TOOLBAR_MARK_ALL_READ || this.OperationInt == TOOLBAR_MARK_ALL_UNREAD);
		var OperationElements = RootElement.childNodes;
		var elemCount = OperationElements.length;
		for (var j=0; j<elemCount; j++) {
			var part = OperationElements[j].childNodes;
			if (part.length > 0) {
				switch (OperationElements[j].tagName) {
					case 'to_folder':
						attr = OperationElements[j].getAttribute('id');
						if (attr) this.ToFolderId = attr - 0;
						this.ToFolderFullName = HtmlDecode(part[0].nodeValue);
					break;
					case 'folder':
						attr = OperationElements[j].getAttribute('id');
						if (attr) this.FolderId = attr - 0;
						this.FolderFullName = HtmlDecode(part[0].nodeValue);
					break;
					case 'messages':
						var messagesElement = OperationElements[j];
						var getmsg = messagesElement.getAttribute('getmsg');
						this.GetMessageAfterDelete = (getmsg == '1');
						var nomove = messagesElement.getAttribute('no_move');
						this.IsMoveError = (nomove == '1');
						var messagesArray = messagesElement.childNodes;
						var messCount = messagesArray.length;
						for (var i=0; i<messCount; i++) {
							if (typeof(messagesArray[i]) == 'object') {
								var id = -1;
								var uid = '';
								var size = 0;
								var charset = AUTOSELECT_CHARSET;
								var folderId = '';
								var folderFullName = '';
								attr = messagesArray[i].getAttribute('id');
								if (attr) id = attr - 0;
								attr = messagesArray[i].getAttribute('charset');
								if (attr) charset = attr - 0;
								attr = messagesArray[i].getAttribute('size');
								if (attr) size = attr - 0;
								var messageParts = messagesArray[i].childNodes;
								var messPartsCount = messageParts.length;
								for (var k=0; k<messPartsCount; k++) {
									var part_ = messageParts[k].childNodes;
									if (part_.length > 0) {
										switch (messageParts[k].tagName) {
											case 'uid':
												uid = Trim(part_[0].nodeValue);
												break;
											case 'folder':
												attr = messageParts[k].getAttribute('id');
												if (attr) folderId = attr - 0;
												folderFullName = HtmlDecode(part_[0].nodeValue);
												break;
										}
									}
								}
								var idArray = Array();
								if (this.Messages.exists(folderId + folderFullName)) {
									var folder = this.Messages.getVal(folderId + folderFullName);
									idArray = folder.IdArray;
								}
								idArray.push({Id: id, Uid: uid, Charset: charset, Size: size});
								this.Messages.setVal(folderId + folderFullName, {IdArray: idArray, FolderId: folderId, FolderFullName: folderFullName});
							}
						}
					break;
				}//switch
			}//if
		}
	},//GetFromXML
	
	GetOperation: function ()
	{
		switch (this.OperationType) {
			case OperationTypes[TOOLBAR_DELETE]:
				this.OperationField = 'Deleted';
				this.OperationValue = true;
				this.OperationInt = TOOLBAR_DELETE;
			break;
			case OperationTypes[TOOLBAR_NO_MOVE_DELETE]:
				this.OperationField = 'Deleted';
				this.OperationValue = true;
				this.OperationInt = TOOLBAR_NO_MOVE_DELETE;
			break;
			case OperationTypes[TOOLBAR_UNDELETE]:
				this.OperationField = 'Deleted';
				this.OperationValue = false;
				this.OperationInt = TOOLBAR_UNDELETE;
			break;
			case OperationTypes[TOOLBAR_PURGE]:
				this.OperationInt = TOOLBAR_PURGE;
			break;
			case OperationTypes[TOOLBAR_EMPTY_SPAM]:
				this.OperationInt = TOOLBAR_EMPTY_SPAM;
			break;
			case OperationTypes[TOOLBAR_MARK_READ]:
				this.OperationField = 'Read';
				this.OperationValue = true;
				this.OperationInt = TOOLBAR_MARK_READ;
			break;
			case OperationTypes[TOOLBAR_MARK_UNREAD]:
				this.OperationField = 'Read';
				this.OperationValue = false;
				this.OperationInt = TOOLBAR_MARK_UNREAD;
			break;
			case OperationTypes[TOOLBAR_FLAG]:
				this.OperationField = 'Flagged';
				this.OperationValue = true;
				this.OperationInt = TOOLBAR_FLAG;
			break;
			case OperationTypes[TOOLBAR_UNFLAG]:
				this.OperationField = 'Flagged';
				this.OperationValue = false;
				this.OperationInt = TOOLBAR_UNFLAG;
			break;
			case OperationTypes[TOOLBAR_MARK_ALL_READ]:
				this.OperationField = 'Read';
				this.OperationValue = true;
				this.OperationInt = TOOLBAR_MARK_ALL_READ;
			break;
			case OperationTypes[TOOLBAR_MARK_ALL_UNREAD]:
				this.OperationField = 'Read';
				this.OperationValue = false;
				this.OperationInt = TOOLBAR_MARK_ALL_UNREAD;
			break;
			case OperationTypes[TOOLBAR_IS_SPAM]:
				this.OperationInt = TOOLBAR_IS_SPAM;
			break;
			case OperationTypes[TOOLBAR_NOT_SPAM]:
				this.OperationInt = TOOLBAR_NOT_SPAM;
			break;
			case OperationTypes[TOOLBAR_MOVE_TO_FOLDER]:
				this.OperationInt = TOOLBAR_MOVE_TO_FOLDER;
			break;
		}
	}
};

// for message in messages list
function CMessageHeaders()
{
	this.Id = -1;
	this.Uid = '';
	this.HasAttachments = false;
	this.Importance = PRIORITY_NORMAL;
	this.Sensivity = SENSIVITY_NOTHING;

	this.FolderId = -1;
	this.FolderType = -1;
	this.FolderFullName = '';
	this.FolderName = '';
	this.Charset = AUTOSELECT_CHARSET;
	this.Random = Math.random;

	this.Read = false;
	this.Replied = false;
	this.Forwarded = false;
	this.Flagged = false;
	this.Deleted = false;
	this.Gray = false;

	this.FromAddr = '';
	this.ToAddr = '';
	this.CCAddr = '';
	this.BCCAddr = '';
	this.ReplyToAddr = '';
	this.Size = '';
	this.Subject = '';
	this.Date = '';
	this.FullDate = '';

	this.NoReply = false;
	this.NoReplyAll = false;
	this.NoForward = false;
}

CMessageHeaders.prototype = {
	GetFromXML: function(RootElement)
	{
		var attr = RootElement.getAttribute('id');if (attr) this.Id = attr - 0;
		attr = RootElement.getAttribute('has_attachments');if (attr) this.HasAttachments = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('priority');if (attr) this.Importance = attr - 0;
		attr = RootElement.getAttribute('size');if (attr) this.Size = attr - 0;
		attr = RootElement.getAttribute('flags');
		if (attr) {
			var Flags = attr - 0;
			if (Flags & 1) this.Read = true;
			if (Flags & 2) this.Replied = true;
			if (Flags & 4) this.Flagged = true;
			if (Flags & 8) this.Deleted = true;
			if (Flags & 256) this.Forwarded = true;
			if (Flags & 512) this.Gray = true;
		}
		attr = RootElement.getAttribute('charset');if (attr) this.Charset = attr - 0;
		attr = RootElement.getAttribute('no_reply');if (attr) this.NoReply = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('no_reply_all');if (attr) this.NoReplyAll = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('no_forward');if (attr) this.NoForward = (attr == 1) ? true : false;
		attr = RootElement.getAttribute('sensivity');if (attr) this.Sensivity = attr - 0;
		var HeadersParts = RootElement.childNodes;
		for (var i=0; i<HeadersParts.length; i++) {
			var part = HeadersParts[i].childNodes;
			if (part.length > 0) {
				switch (HeadersParts[i].tagName) {
					case 'folder':
						attr = HeadersParts[i].getAttribute('id');
						if (attr) this.FolderId = attr - 0;
						this.FolderFullName = HtmlDecode(part[0].nodeValue);
					break;
					case 'from':
						this.FromAddr = Trim(part[0].nodeValue);
					break;
					case 'to':
						this.ToAddr = Trim(part[0].nodeValue);
					break;
					case 'cc':
						this.CCAddr = Trim(part[0].nodeValue);
					break;
					case 'bcc':
						this.BCCAddr = Trim(part[0].nodeValue);
					break;
					case 'reply_to':
						this.ReplyToAddr = Trim(part[0].nodeValue);
					break;
					case 'subject':
						this.Subject = Trim(part[0].nodeValue);
						break;
					case 'short_date':
						this.Date = Trim(part[0].nodeValue);
					break;
					case 'full_date':
						this.FullDate = Trim(part[0].nodeValue);
					break;
					case 'uid':
						this.Uid = Trim(part[0].nodeValue);
						if (this.Uid == '-1') this.Uid = '';
					break;
				}//switch
			}
		}//for
	},//GetFromXML
	
	MakeSearchResult: function (searchString)
	{
		this.FromAddr = this.FromAddr.ReplaceStr(searchString, HighlightMessageLine);
		this.Subject = this.Subject.ReplaceStr(searchString, HighlightMessageLine);
	}
};

CMessageHeaders.prototype.GetIdForList = CMessage.prototype.GetIdForList;
CMessageHeaders.prototype.IsCorrectData = CMessage.prototype.IsCorrectData;
CMessageHeaders.prototype.SetSize = CMessage.prototype.SetSize;
CMessageHeaders.prototype.IsEqual = CMessage.prototype.IsEqual;

function CMessages()
{
	this.Type = TYPE_MESSAGE_LIST;
	this.IdAcct = WebMail._idAcct;
	this.FolderId = -1;
	this.FolderType = -1;
	this.FolderFullName = '';
	this.FolderName = '';
	this.SortField = 0;//0=from, 1=date, 2=size, 3=subject
	this.SortOrder = 0;//0=ASC, 1=DESC
	this.Page = 1;
	this.MessagesCount = 0;
	this.NewMsgsCount = 0;
	this.LookFor = '';
	this._searchFields = 0;
	this.List = [];
	this.MessagesBodies = new CMessagesBodies();
}

CMessages.prototype = {
	GetStringDataKeys: function()
	{
		var arDataKeys = [ this.IdAcct, this.Page, this.SortField, this.SortOrder, this.FolderId, this.FolderFullName, this.LookFor, this._searchFields ];
		return arDataKeys.join(STR_SEPARATOR);
	},

	GetFromXML: function(RootElement)
	{
		var attr = RootElement.getAttribute('id_acct');
		if (attr) this.IdAcct = attr - 0;
		attr = RootElement.getAttribute('page');
		if (attr) this.Page = attr - 0;
		attr = RootElement.getAttribute('sort_field');
		if (attr) this.SortField = attr - 0;
		attr = RootElement.getAttribute('sort_order');
		if (attr) this.SortOrder = attr - 0;
		attr = RootElement.getAttribute('count');
		if (attr) this.MessagesCount = attr - 0;
		attr = RootElement.getAttribute('count_new');
		if (attr) this.NewMsgsCount = attr - 0;
		var MessagesXML = RootElement.childNodes;
		var MHeaders = null;
		var msgsCount = 0;
		for (var i=0; i<MessagesXML.length; i++) {
			var part = MessagesXML[i].childNodes;
			if (part.length > 0) {
				switch (MessagesXML[i].tagName) {
					case 'folder':
						attr = MessagesXML[i].getAttribute('id');
						if (attr) this.FolderId = attr - 0;
						attr = MessagesXML[i].getAttribute('type');
						if (attr) this.FolderType = attr - 0;
						var FoldersParts = MessagesXML[i].childNodes;
						for (var q=0; q<FoldersParts.length; q++) {
							var partFolder = FoldersParts[q].childNodes;
							if (partFolder.length > 0) {
								switch (FoldersParts[q].tagName) {
									case 'name':
										this.FolderName = HtmlDecode(partFolder[0].nodeValue);
										break;
									case 'full_name':
										this.FolderFullName = HtmlDecode(partFolder[0].nodeValue);
										break;
								}
							}
						}
					break;
					case 'look_for':
						attr = MessagesXML[i].getAttribute('fields');
						if (attr) this._searchFields = attr - 0;
						this.LookFor = Trim(part[0].nodeValue);
					break;
					case 'message':
						MHeaders = new CMessageHeaders();
						MHeaders.GetFromXML(MessagesXML[i]);
						if (this.LookFor != '') {
							MHeaders.MakeSearchResult(this.LookFor);
						}
						this.List[msgsCount++] = MHeaders;
						this.MessagesBodies.Add(MHeaders);
					break;
				}
			}
		}//for
	},//GetFromXML
	
	GetMessageIndex: function (msg)
	{
		var index = -1;
		for (var i=0; i<this.List.length; i++) {
			var lMsg = this.List[i];
			if (lMsg && lMsg.IsEqual(msg)) {
				index = i;
				lMsg.Charset = msg.Charset;
				this.List[i] = lMsg;
			}
		}
		return index;
	},
	
	MakeMessageRead: function (messageParams)
	{
		for (var i=0; i<this.List.length; i++) {
			if (this.List[i] && this.List[i].Id == messageParams[0] && this.FolderId == messageParams[1] && this.FolderFullName == messageParams[2]) {
				this.List[i].Read = true;
			}
		}
	}
};

function CMessagesBodies()
{
	this.Type = TYPE_MESSAGES_BODIES;
	this.Folders = {};
	
	this.Add = function (msg)
	{
		if (msg.Size > 76800 && WebMail.Accounts.CurrMailProtocol != IMAP4_PROTOCOL) return;
		var folderIndex = msg.FolderId + msg.FolderFullName;
		if (this.Folders[folderIndex] == undefined) {
			this.Folders[folderIndex] = {Id: msg.FolderId, FullName: msg.FolderFullName, Messages: []};
		}
		this.Folders[folderIndex].Messages.push({Id: msg.Id, Uid: msg.Uid, Charset: msg.Charset, Size: msg.Size});
	};
	
	this.GetInXML = function (foldersParam)
	{
		var xml = '';
		var wmServer = (WebMail.Accounts.CurrMailProtocol == WMSERVER_PROTOCOL);
		var imapServer = (WebMail.Accounts.CurrMailProtocol == IMAP4_PROTOCOL);
		var folderIndex;
		for (folderIndex in this.Folders) {
			var folder = this.Folders[folderIndex];
			var params = foldersParam[folderIndex];
			if (!wmServer && !imapServer && (!params || params._syncType == SYNC_TYPE_DIRECT_MODE || 
				params._syncType == SYNC_TYPE_NEW_HEADERS || params._syncType == SYNC_TYPE_ALL_HEADERS)) {
				continue;
			}
			var msgNodes = '';
			var msgCount = folder.Messages.length;
			var msgInit = 0;
			for (var msgIndex = 0; msgIndex < msgCount; msgIndex++) {
				var msg = folder.Messages[msgIndex];

				var cacheKey = WebMail.DataSource.GetStringDataKey(TYPE_MESSAGE, {Id: msg.Id, Charset: msg.Charset, Uid: msg.Uid,
					FolderId: folder.Id, FolderFullName: folder.FullName});
				
				if (typeof(preFetchCache[cacheKey]) != 'undefined') {
					continue;
				}
				
				if (WebMail.DataSource.Cache.ExistsData(TYPE_MESSAGE, cacheKey)) {
					continue;
				}

				if (window.UsePrefetch) {
					if (++msgInit > preFetchMsgLimit) {
						break;
					}
					
					preFetchCache[cacheKey] = true;
				}
				
				msgNodes += '<message id="' + msg.Id + '" charset="' + msg.Charset + '" size="' + msg.Size + '">';
				msgNodes += '<uid>' + GetCData(msg.Uid) + '</uid>';
				msgNodes += '</message>';
			}
			if (msgNodes.length == 0) {
				continue;
			}
			xml += '<folder id="' + folder.Id + '">';
			xml += '<full_name>' + GetCData(folder.FullName) + '</full_name>';
			xml += msgNodes + '</folder>';
		}
		return xml;
	};
}

function CFolder(level, listHide)
{
	this.Id = 0;
	this.IdParent = 0;
	this.Type = 0;
	this.SentDraftsType = false;
	this.SyncType = SYNC_TYPE_NO;
	this.Hide = false;
	this.ListHide = listHide;
	this.FldOrder = 0;
	this.MsgCount = 0;
	this.NewMsgCount = 0;
	this.Size = 0;
	this.Name = '';
	this.FullName = '';
	this.Level = level;
	this.hasChilds = false;
	this.Folders = new Array();
}

CFolder.prototype = {
	GetNameByType: function()
	{
		var fName = this.Name;
		switch (this.Type) {
			case FOLDER_TYPE_INBOX:
				fName = Lang.FolderInbox;
			break;
			case FOLDER_TYPE_SENT:
				fName = Lang.FolderSentItems;
			break;
			case FOLDER_TYPE_DRAFTS:
				fName = Lang.FolderDrafts;
			break;
			case FOLDER_TYPE_TRASH:
				fName = Lang.FolderTrash;
			break;
			case FOLDER_TYPE_SPAM:
				fName = Lang.FolderSpam;
			break;
			case FOLDER_TYPE_QUARANTINE:
				fName = Lang.FolderQuarantine;
			break;
		}
		return fName;
	},

	GetFromXML: function(RootElement, parentSentDraftsType)
	{
		var attr, part, FoldersXML, jCount, j, folder, childFolders;
		attr = RootElement.getAttribute('id');if (attr) this.Id = attr - 0;
		attr = RootElement.getAttribute('id_parent');if (attr) this.IdParent = attr - 0;
		attr = RootElement.getAttribute('type');if (attr) this.Type = attr - 0;
		this.SentDraftsType = (parentSentDraftsType || this.Type == FOLDER_TYPE_SENT || this.Type == FOLDER_TYPE_DRAFTS);
		attr = RootElement.getAttribute('sync_type');if (attr) this.SyncType = attr - 0;
		attr = RootElement.getAttribute('hide');if (attr) this.Hide = (attr == '1') ? true : false;
		this.ListHide = (this.Hide) ? this.Hide : this.ListHide;
		attr = RootElement.getAttribute('fld_order');if (attr) this.FldOrder = attr - 0;
		attr = RootElement.getAttribute('count');if (attr) this.MsgCount = attr - 0;
		attr = RootElement.getAttribute('count_new');if (attr) this.NewMsgCount = attr - 0;
		attr = RootElement.getAttribute('size');if (attr) this.Size = attr - 0;
		var FolderNames = RootElement.childNodes;
		var iCount = FolderNames.length;
		for (var i=0; i<iCount; i++) {
			part = FolderNames[i].childNodes;
			if (part.length > 0) {
				switch (FolderNames[i].tagName) {
					case 'name':
						this.Name = Trim(HtmlDecode(part[0].nodeValue));
					break;
					case 'full_name':
						this.FullName = HtmlDecode(part[0].nodeValue);
					break;
					case 'folders':
						FoldersXML = FolderNames[i].childNodes;
						jCount = FoldersXML.length;
						for (j=0; j<jCount; j++) {
							folder = new CFolder(this.Level + 1, this.ListHide);
							folder.GetFromXML(FoldersXML[j], this.SentDraftsType);
							childFolders = folder.Folders;
							if (childFolders.length > 0) folder.hasChilds = true;
							delete folder.Folders;
							this.Folders.push(folder);
							this.Folders = this.Folders.concat(childFolders);
						}
					break;
				}//switch
			}
		}//for
		if (this.Type != FOLDER_TYPE_INBOX && this.Type != FOLDER_TYPE_SENT && 
			this.Type != FOLDER_TYPE_DRAFTS && this.Type != FOLDER_TYPE_TRASH && 
			this.Type != FOLDER_TYPE_SPAM && this.Type != FOLDER_TYPE_QUARANTINE &&
			this.Type != FOLDER_TYPE_SYSTEM){
			this.Type = FOLDER_TYPE_DEFAULT;
		}
	}//GetFromXML
};

function CFolderList()
{
	this.Type = TYPE_FOLDER_LIST;
	this.Folders = new Array();
	this.IdAcct = -1;
	this.Sync = 0;
	this.NameSpace = '';
	this.AllFoldersInDm = false;
}

CFolderList.prototype = {
	GetStringDataKeys: function()
	{
		return this.IdAcct;
	},

	GetFromXML: function(RootElement)
	{
		var attr, folder, childFolders, i, iCount;
		attr = RootElement.getAttribute('id_acct'); if (attr) this.IdAcct = attr - 0;
		attr = RootElement.getAttribute('sync'); if (attr) this.Sync = attr - 0;
		attr = RootElement.getAttribute('namespace'); if (attr) this.NameSpace = attr + '';
		
		var FoldersXML = RootElement.childNodes;
		iCount = FoldersXML.length;
		for (i=0; i<iCount; i++) {
			folder = new CFolder(0, false);
			folder.GetFromXML(FoldersXML[i], false);
			childFolders = folder.Folders;
			if (childFolders.length > 0) folder.hasChilds = true;
			delete folder.Folders;
			this.Folders.push(folder);
			this.Folders = this.Folders.concat(childFolders);
		}
		iCount = this.Folders.length;
		var dmFoldersCount = 0;
		for (i=0; i<iCount; i++) {
			if (this.NameSpace.length > 0 && this.Folders[i].Level > 0
				&& this.Folders[i].FullName.substr(0, this.NameSpace.length) == this.NameSpace)
			{
				this.Folders[i].Level = this.Folders[i].Level - 1;
			}
		
		    if (this.Folders[i].SyncType == SYNC_TYPE_DIRECT_MODE) dmFoldersCount++;
		}
		this.AllFoldersInDm = (iCount == dmFoldersCount);
	},

	GetNameSpaceFolderId: function()
	{
		if (this.NameSpace.length > 0) {
			var i, iCount = this.Folders.length;
			for (i=0; i<iCount; i++) {
				if (this.Folders[i].FullName == this.NameSpace.substr(0, this.NameSpace.length - 1)) {
					return this.Folders[i].Id;
				}
			}
		}
		
		return -1;
	},
	
	SetMessagesCount: function (folderId, folderFullName, count, countNew)
	{
		for (var i=this.Folders.length-1; i>=0; i--) {
			var folder = this.Folders[i];
			if (folder.Id == folderId && folder.FullName == folderFullName) {
				folder.MsgCount = count;
				folder.NewMsgCount = countNew;
			}
		}
	}
};

function CUpdate()
{
	this.Type = TYPE_UPDATE;
	this.Value = '';
	this.Id = '-1';
	this.Uid = '';
}

CUpdate.prototype = {
	GetStringDataKeys: function()
	{
		return '';
	},

	GetFromXML: function(RootElement)
	{
		var attr = RootElement.getAttribute('value');
		if (attr) {
			this.Value = attr;
		    if (attr == 'save_message') {
		        attr = RootElement.getAttribute('id');
		        if (attr) this.Id = attr;
				var UpdateParts = RootElement.childNodes;
				if (UpdateParts.length > 0) {
					var part = UpdateParts[0].childNodes;
					if (part.length > 0 && UpdateParts[0].tagName == 'uid') {
						this.Uid = Trim(part[0].nodeValue);
						if (this.Uid == '-1') this.Uid = '';
					}
				}
		    }
		}
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}