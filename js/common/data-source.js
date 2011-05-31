/*
 * Classes:
 *  CDataType(Type, Caching, CacheLimit, CacheByParts, RequestParams, GetRequest)
 *  CDataSource(DataTypes, ActionUrl, ErrorHandler, LoadHandler, TakeDataHandler, RequestHandler)
 *  CCache(DataTypes)
 *  CHistoryStorage(SettingsStorage)
 */

function CDataType(Type, Caching, CacheLimit, CacheByParts, RequestParams, GetRequest)
{
	this.Type = Type; //int
	this.Caching = Caching; //bool
	this.CacheLimit = CacheLimit; //int
	this.CacheByParts = CacheByParts; //bool
	this.RequestParams = RequestParams; //obj
	/*
	ex. for messages list: {
			IdFolder: "id_folder",
			SortField: "sort_field",
			SortOrder: "sort_order",
			Page: "page"
		}
	*/
	this.GetRequest = GetRequest; //string; ex. for messages list: 'messages'
}

function CDataSource(DataTypes, ActionUrl, ErrorHandler, LoadHandler, TakeDataHandler, RequestHandler)
{
	this.Cache = new CCache(DataTypes);
	this.NetLoader = new CNetLoader();

	this.ActionUrl = ActionUrl;

	this.onError = ErrorHandler;
	this.onLoad = LoadHandler;
	this.onGet = TakeDataHandler;
	this.onRequest = RequestHandler;

	this.DataTypes = [];
	for (var Key in DataTypes) {
		this.DataTypes[DataTypes[Key].Type] = DataTypes[Key];
	}
	
	this.LastFromCache = false;
	this.NeedInfo = true;
	this.WaitMessagesBodies = false;
	
	this._errorStr = '';
}

CDataSource.prototype = {
	GetStringDataKey: function (intDataType, objDataKeys)
	{
		var dataType = this.DataTypes[intDataType];
		var arDataKeys = [];
		for (var key in objDataKeys) {
			if (key == 'Sync') continue;
			arDataKeys.push(objDataKeys[key]);
		}
		if (dataType.Caching) {
			return dataType.GetRequest + STR_SEPARATOR + arDataKeys.join(STR_SEPARATOR);
		}
        else {
			return dataType.GetRequest;
		}
	},
	
	Get: function (intDataType, objDataKeys, arDataParts, xml, background)
	{
		var cache = this.Cache;
		var dataType = this.DataTypes[intDataType];
		var cacheByParts = dataType.CacheByParts;

		var mode = 0;
		if (cacheByParts) {
			for (var key in arDataParts) {
				mode = (1 << arDataParts[key]) | mode;
			}
		}

		var dataSize = null;
		if (intDataType == TYPE_MESSAGE && typeof(objDataKeys.Size) != 'undefined') {
			dataSize = objDataKeys.Size;
			delete objDataKeys.Size;
		}

		StringDataKeys = this.GetStringDataKey(intDataType, objDataKeys);

		var data = null;
		if (dataType.Caching && cache.ExistsData(intDataType, StringDataKeys)) {// there is in the cache!
			data = cache.GetData(intDataType, StringDataKeys);
			if (cacheByParts) {
				mode = (mode | data.Parts) ^ data.Parts;
			}
		}

		if ((data == null) || (cacheByParts && (mode != 0))) {
			var arParams = [];
			arParams['action'] = 'get';
			arParams['request'] = dataType.GetRequest;
			if (cacheByParts) {
				arParams['mode'] = mode;
			}
			if (background) {
				arParams['background'] = '1';
			}
			var objRequestParams = dataType.RequestParams;
			for (Param in objRequestParams) {
				arParams[objRequestParams[Param]] = objDataKeys[Param];
			}
			if (null !== dataSize) {
				arParams['size'] = dataSize;
			}

			XMLParams = this.GetXML(arParams, xml);
			if (intDataType != TYPE_MESSAGES_BODIES && this.NeedInfo && !background) {
				this.onRequest.call({action: arParams['action'], request: arParams['request']});
			}
			if (intDataType == TYPE_MESSAGES_BODIES) {
				this.WaitMessagesBodies = true;
			}

			this.NetLoader.LoadXMLDoc(this.ActionUrl, 'xml=' + encodeURIComponent(XMLParams), this.onLoad, this.onError);
			this.LastFromCache = false;
		}
		else if (!background) {
			this.onGet.call({Data: data});
			this.LastFromCache = true;
		}
	},
	
	Set: function (messageParams, field, value, isAllMess)
	{
		this.Cache.SetData(TYPE_MESSAGE_LIST, messageParams, field, value, isAllMess);
	},

	Request: function (objParams, xml)
	{
		var XMLParams = this.GetXML(objParams, xml);
		if (this.NeedInfo) {
			this.onRequest.call({action: objParams.action, request: objParams.request});
		}
		this.NetLoader.LoadXMLDoc(this.ActionUrl, 'xml=' + encodeURIComponent(XMLParams), this.onLoad, this.onError);
	},
	
	SetError: function (errorStr)
	{
		var arParams = [];
		arParams['action'] = 'add';
		arParams['request'] = 'error';
		arParams['background'] = '1';
		var XMLParams = this.GetXML(arParams, '<error>' + GetCData(errorStr) + '</error>');
		this.NetLoader.LoadXMLDoc(this.ActionUrl, 'xml=' + encodeURIComponent(XMLParams), this.onLoad, this.onError);
	},
	
	GetXML: function(arParams, xml)
	{
		var strResult = '';
		for (var paramName in arParams) {
			strResult += '<param name="' + paramName + '" value="' + arParams[paramName] + '"/>';
		}
		var errorXml = '';
		if (this._errorStr.length > 0) {
			errorXml = '<error>' + GetCData(this._errorStr) + '</error>';
			this._errorStr = '';
		}
		return '<?xml version="1.0" encoding="utf-8"?><webmail>' + strResult + xml + errorXml + '</webmail>';
	},
	
	OnParsingError: function (errorCode, responseText)
	{
		var errorDesc = Lang.ErrorParsing + '<br/>Error code ' + errorCode + '.<br/>';
		this.onError.call({ErrorDesc: errorDesc});
	},
	
	ParseXML: function(xmlDoc, textDoc)
	{
		var rootElement, complex, trEnd, background, objects, key, dataArray,
				showError, dataObj, intDataType, objDataType, stringDataKeys;
		
		if (!xmlDoc || !(xmlDoc.documentElement) || typeof xmlDoc != 'object' || typeof xmlDoc.documentElement != 'object') {
			this.OnParsingError(1, textDoc);
			return;
		}
		rootElement = xmlDoc.documentElement;
		if (!rootElement || rootElement.tagName != 'webmail') {
			this.OnParsingError(2, textDoc);
			return;
		}
		complex = rootElement.getAttribute('complex');
		trEnd = rootElement.getAttribute('trialend');

		background = rootElement.getAttribute('background');
		objects = rootElement.childNodes;
		if (objects.length == 0 && complex != 'messages_bodies') {
			if (!background){
				this.OnParsingError(3, textDoc);
			}
			return;
		}

		dataArray = {};
		for (key = 0; key < objects.length; key++) {
			showError = (complex != 'messages_bodies' && background != '1');
			dataObj = this.GetData(objects[key], showError);
			if (dataObj == null) {
				continue;
			}
			
			intDataType = dataObj.Type;
			objDataType = this.DataTypes[intDataType];
			if (typeof objDataType == 'object') {
				dataObj.GetFromXML(objects[key]);
				if (objDataType.Caching) {
					stringDataKeys = objDataType.GetRequest + STR_SEPARATOR + dataObj.GetStringDataKeys(STR_SEPARATOR);
					if (this.Cache.ExistsData(intDataType, stringDataKeys)) {
						if (objDataType.CacheByParts) {
							dataObj = this.Cache.GetData(intDataType, stringDataKeys);
							dataObj.GetFromXML(objects[key]);
						}
						this.Cache.ReplaceData(intDataType, stringDataKeys, dataObj);
					}
                    else {
						this.Cache.AddData(intDataType, stringDataKeys, dataObj);
					}
				}
				if (dataObj.Type == TYPE_MESSAGE) {
					this.Set([[dataObj.Id], dataObj.FolderId, dataObj.FolderFullName], 'Read', true);
				}
			}
            else {
				dataObj.GetFromXML(objects[key]);
			}
			if (complex == 'base' && objects[key].tagName == 'accounts') {
				this.onGet.call({Data: dataObj});
			}
            else {
				dataArray[objects[key].tagName] = dataObj;
			}
		}
		if (background == '1' && dataArray['messages']) {
			WebMail.RequestFoldersMessageList();
		}
		if (background == '1' || complex == 'folders_base') {
			return;
		}
		if (complex == 'messages_bodies') {
			this.WaitMessagesBodies = false;
			this.onGet.call({Data: {Type: TYPE_MESSAGES_BODIES}});
			return;
		}
		
		if (complex == 'base' && trEnd && trEnd == '1') {
			WebMail.ShowTrial();
		}

		WebMail.HideInfo();
		this.NeedInfo = true;
		if (complex == 'account_base') {
			this.onGet.call({Data: dataArray['folders_list']});
			return;
		}
		if (dataArray['message']) {
			// to miss contact object for WebMail placing
			this.onGet.call({Data: dataArray['message']});
			return;
		}
		if (dataArray['contacts_groups']) {
			// to miss contact object for WebMail placing
			this.onGet.call({Data: dataArray['contacts_groups']});
			return;
		}
		if (complex == 'base') {
			// to miss messages list object for WebMail placing
			this.onGet.call({Data: dataArray['settings_list']});
			this.onGet.call({Data: dataArray['folders_list']});
			/*if (WebMail.StartScreen == SCREEN_VIEW_MESSAGE) {
				this.onGet.call({Data: dataArray['messages']});
			}*/
			return;
		}
		for (key in dataArray) {
			this.onGet.call({Data: dataArray[key]});
		}
	},
	
	GetData: function (objectXml, showError)
	{
		switch (objectXml.tagName) {
			case 'settings_list':
				return new CSettingsList();
			case 'update':
				return new CUpdate();
			case 'accounts':
				return new CAccounts();
			case 'message':
				return new CMessage();
			case 'messages':
				return new CMessages();
			case 'operation_messages':
				return new COperationMessages();
			case 'folders_list':
				return new CFolderList();
			case 'settings':
				return new CSettings();
			case 'account':
				return new CAccountProperties();
			case 'filters':
				return new CFilters();
			case 'filter':
				return new CFilterProperties();
			case 'contacts_settings':
				return new CContactsSettings();
			case 'autoresponder':
				return new CAutoresponderData();
			case 'mobile_sync':
				return new CMobileSyncData();
			case 'contacts_groups':
				return new CContacts();
			case 'contact':
				return new CContact();
			case 'groups':
				return new CGroups();
			case 'group':
				return new CGroup();
			case 'spellcheck':
				return new CSpellchecker();
			case 'information':
				var info = objectXml.childNodes[0].nodeValue;
				if (info && info.length > 0) {
					WebMail.ShowReport(info, 10000);
				}
				return null;
			case 'error':
				var attr = objectXml.getAttribute('code');
				if (attr) {
					document.location = LoginUrl + '?error=' + attr;
				} else if (showError) {
					var errorDesc = objectXml.childNodes[0].nodeValue;
					if (!errorDesc || errorDesc.length == 0) {
						errorDesc = Lang.ErrorWithoutDesc;
					}
					this.onError.call({ErrorDesc: errorDesc});
				}
				return null;
			case 'session_error':
				document.location = LoginUrl + '?error=1';
				return null;
			default:
				return null;
		}
		return null;
	}
};

function CCache(DataTypes)
{
	this.DataTypes = [];
	this.Dictionaries = [];
	for (var a in DataTypes) {
		this.AddDataType(DataTypes[a]);
	}
}

CCache.prototype = {
	AddDataType: function(ObjectDataType)
	{
		this.DataTypes[ObjectDataType.Type] = ObjectDataType;
		this.Dictionaries[ObjectDataType.Type] = new CDictionary();
	},

	ExistsData: function(DataType, Key)
	{
		if(typeof this.DataTypes[DataType] == 'object' && typeof this.Dictionaries[DataType] == 'object') {
			return this.Dictionaries[DataType].exists( Key );
		}
        else {
			return false;
		}
	},

	AddData: function(DataType, Key, Value)
	{
		var Keys;
		if (this.Dictionaries[DataType].count >= this.DataTypes[DataType].CacheLimit) {
			Keys = this.Dictionaries[DataType].keys();
			this.Dictionaries[DataType].remove(Keys[0]);
		}
		this.Dictionaries[DataType].add(Key, Value);
	},
	
	ReplaceFromContactFromMessages: function(addedContactId, defEmail)
	{
		var dict, keys, i, msg;
		dict = this.Dictionaries[TYPE_MESSAGE];
		keys = dict.keys();
		for (i in keys) {
			msg = dict.getVal(keys[i]);
			if (msg.FromAddr.indexOf(defEmail) != -1) {
				msg.FromContact = {Id: addedContactId};
				dict.setVal(keys[i], msg);
			}
		}
	},
	
	RemoveFromContactFromMessages: function(contactIdForRemove)
	{
		var dict, keys, i, msg;
		dict = this.Dictionaries[TYPE_MESSAGE];
		keys = dict.keys();
		for (i in keys) {
			msg = dict.getVal(keys[i]);
			if (msg.FromContact.Id == contactIdForRemove) {
				msg.FromContact = {Id: -1};
				dict.setVal(keys[i], msg);
			}
		}
	},
	
	SetMessageSafety: function(msgId, msgUid, folderId, folderFullName, safety, isAll)
	{
		var dict = this.Dictionaries[TYPE_MESSAGE];
		var keys = dict.keys();
		for (var i in keys) {
			var msg = dict.getVal(keys[i]);
			if (isAll || msg.IsCorrectData(msgId, msgUid, folderId, folderFullName)) {
				msg.ShowPictures();
				msg.Safety = safety;
				dict.setVal(keys[i], msg);
				if (!isAll) {
					break;
				}
			}
		}
	},
	
	SetSenderSafety: function(fromAddr, safety)
	{
		var fromParts = GetEmailParts(HtmlDecode(fromAddr));
		var fromEmail = fromParts.Email;
		var dict = this.Dictionaries[TYPE_MESSAGE];
		var keys = dict.keys();
		for (var i in keys) {
			var msg = dict.getVal(keys[i]);
			var fParts = GetEmailParts(HtmlDecode(msg.FromAddr));
			if (fromEmail == fParts.Email) {
				msg.ShowPictures();
				msg.Safety = safety;
				dict.setVal(keys[i], msg);
			}
		}
	},
	
	SetMessagesCount: function(folderId, folderFullName, count, countNew)
	{
		var dict = this.Dictionaries[TYPE_MESSAGE_LIST];
		var keys = dict.keys();
		for (var i in keys) {
			var messages = dict.getVal(keys[i]);
			if (messages.FolderId == folderId && messages.FolderFullName == folderFullName && messages.LookFor.length == 0) {
				if (messages.MessagesCount != count) {
					dict.remove(keys[i]);
				}
                else {
					messages.NewMsgsCount = countNew;
					dict.setVal(keys[i], messages);
				}
			}
		}
	},
	
	SetFolderMessagesCount: function(folderId, folderFullName, count, countNew, idAcct)
	{
		var dict = this.Dictionaries[TYPE_FOLDER_LIST];
		var keys = dict.keys();
		for (var i in keys) {
			var folderList = dict.getVal(keys[i]);
			if (folderList.IdAcct == idAcct) {
				folderList.SetMessagesCount(folderId, folderFullName, count, countNew);
			}
		}
	},
	
	ClearMessageList: function(folderId, folderFullName, byFlag)
	{
		var dict = this.Dictionaries[TYPE_MESSAGE_LIST];
		if (folderId == '-1' && folderFullName == '') {
			dict.removeAll();
		}
		else {
			var keys = dict.keys();
			for (var i in keys) {
				var messages = dict.getVal(keys[i]);
				if (byFlag && messages.SortField != SORT_FIELD_FLAG) {
					continue;
				}
				if (messages.FolderId == folderId && messages.FolderFullName == folderFullName ||
					messages.FolderId == '-1' && messages.FolderFullName == '') {
						dict.remove(keys[i]);
				}
			}
		}
	},
	
	ClearAllMessages: function()
	{
		this.Dictionaries[TYPE_MESSAGE_LIST].removeAll();
		this.Dictionaries[TYPE_MESSAGE].removeAll();
	},
	
	ClearMessage: function(id, uid, folderId, folderFullName, charset)
	{
		var deleted = false;
		var dict = this.Dictionaries[TYPE_MESSAGE];
		var keys = dict.keys();
		for (var i in keys) {
			var msg = dict.getVal(keys[i]);
			if (msg.IsCorrectData(id, uid, folderId, folderFullName) && msg.Charset != charset) {
				dict.remove(keys[i]);
				deleted = true;
			}
		}
		return deleted;
	},
	
	RenameRemoveGroupInContacts: function (groupId, groupName, groupContacts)
	{
		var dict = this.Dictionaries[TYPE_CONTACT];
		var keys = dict.keys();
		for (var i in keys) {
			var oldContact = dict.getVal(keys[i]);
			var newContact = null;
			for (var j in groupContacts) {
				if (oldContact.Id == groupContacts[j].Id) {
					newContact = groupContacts[j];
					break;
				}
			}
			var groupsCount = oldContact.Groups.length;
			for (var groupIndex = 0; groupIndex < groupsCount; groupIndex++) {
				if (oldContact.Groups[groupIndex].Id == groupId) {
					if (newContact != null) {
						oldContact.Groups[groupIndex].Name = groupName;
					} else {
						var groups1 = oldContact.Groups.slice(0, groupIndex);
						var groups2 = oldContact.Groups.slice(groupIndex + 1, groupsCount);
						oldContact.Groups = groups1.concat(groups2);
					}
					break;
				}
			}
			dict.setVal(keys[i], oldContact);
		}
	},
	
	RemoveFromGroupInContacts: function (groupId)
	{
		var dict = this.Dictionaries[TYPE_CONTACT];
		var keys = dict.keys();
		for (var i in keys) {
			var oldContact = dict.getVal(keys[i]);
			var groupsCount = oldContact.Groups.length;
			for (var groupIndex = 0; groupIndex < groupsCount; groupIndex++) {
				if (oldContact.Groups[groupIndex].Id == groupId) {
					var groups1 = oldContact.Groups.slice(0, groupIndex);
					var groups2 = oldContact.Groups.slice(groupIndex + 1, groupsCount);
					oldContact.Groups = groups1.concat(groups2);
					break;
				}
			}
			dict.setVal(keys[i], oldContact);
		}
	},
	
	RemoveFromContactInGroups: function (contactId)
	{
		var dict = this.Dictionaries[TYPE_GROUP];
		var keys = dict.keys();
		for (var i in keys) {
			var oldGroup = dict.getVal(keys[i]);
			var contactCount = oldGroup.Contacts.length;
			for (var contactIndex = 0; contactIndex < contactCount; contactIndex++) {
				if (oldGroup.Contacts[contactIndex].Id == contactId) {
					var contacts1 = oldGroup.Contacts.slice(0, contactIndex);
					var contacts2 = oldGroup.Contacts.slice(contactIndex + 1, contactCount);
					oldGroup.Contacts = contacts1.concat(contacts2);
					break;
				}
			}
			dict.setVal(keys[i], oldGroup);
		}
	},
	
	AddGroupToContacts: function (groupId, groupName, groupContacts)
	{
		var dict = this.Dictionaries[TYPE_CONTACT];
		var keys = dict.keys();
		for (var i in keys) {
			var oldContact = dict.getVal(keys[i]);
			var newContact = null;
			for (var j in groupContacts)
			{
				if (oldContact.Id == groupContacts[j].Id) {
					newContact = groupContacts[j];
					break;
				}
			}
			if (newContact != null) {
				var groupsCount = oldContact.Groups.length;
				var finded = false;
				for (var groupIndex = 0; groupIndex < groupsCount; groupIndex++) {
					if (oldContact.Groups[groupIndex].Id == groupId) {
						finded = true;
						break;
					}
				}
				if (!finded) {
					oldContact.Groups.push({Id: groupId, Name: groupName});
				}
			}
			dict.setVal(keys[i], oldContact);
		}
	},
	
	ClearAllContactsGroupsList: function ()
	{
		this.Dictionaries[TYPE_CONTACTS].removeAll();
	},
	
	AddContactsToGroup: function (key, groupContacts)
	{
		var dict = this.Dictionaries[TYPE_GROUP];
		var group = dict.getVal(key);
		if (typeof(group) == 'undefined') return;
		for (var j in groupContacts)
		{
			var contactsCount = group.Contacts.length;
			var finded = false;
			for (var contactIndex = 0; contactIndex < contactsCount; contactIndex++) {
				if (group.Contacts[contactIndex].Id == groupContacts[j].Id) {
					finded = true;
					break;
				}
			}
			if (!finded) {
				group.Contacts.push(groupContacts[j]);
			}
		}
		dict.setVal(key, group);
	},
	
	AddRemoveRenameContactInGroups: function (contact)
	{
		var dict = this.Dictionaries[TYPE_GROUP];
		var keys = dict.keys();
		for (var i in keys) {
			var group = dict.getVal(keys[i]);
			
			var contactHasGroup = false;
			var groupCount = contact.Groups.length;
			for (var groupIndex = 0; groupIndex < groupCount; groupIndex++) {
				if (group.Id == contact.Groups[groupIndex].Id) {
					contactHasGroup = true;
					break;
				}
			}
			
			var contactsCount = group.Contacts.length;
			var finded = false;
			for (var contactIndex = 0; contactIndex < contactsCount; contactIndex++) {
				if (group.Contacts[contactIndex].Id == contact.Id) {
					if (contactHasGroup) {
						group.Contacts[contactIndex] = {Id: contact.Id, Name: contact.Name, Email: contact.Email};
					}
					else {
						var contacts1 = group.Contacts.slice(0, contactIndex);
						var contacts2 = group.Contacts.slice(contactIndex + 1, contactsCount);
						group.Contacts = contacts1.concat(contacts2);
					}
					finded = true;
					break;
				}
			}
			if (!finded && contactHasGroup) {
				group.Contacts.push({Id: contact.Id, Name: contact.Name, Email: contact.Email});
			}
			
			dict.setVal(keys[i], group);
		}
	},
	
	SetData: function (type, messageParams, field, value, isAllMess)
	{
		var folderId = messageParams[1];
		var folderFullName = messageParams[2];
		var dict = this.Dictionaries[type];
		var keys = dict.keys();
		for (var i in keys) {
			var messages = dict.getVal(keys[i]);
			if (messages.FolderId == folderId && messages.FolderFullName == folderFullName ||
				(messages.FolderId == '-1' && messages.FolderFullName == '' && !isAllMess)) {
				var idArray = messageParams[0];
				for (var j in messages.List) {
					var data = messages.List[j];
					if (data) {
						if (isAllMess) {
							data[field] = value;
							messages.List[j] = data;
						}
						else {
							for (var k in idArray) {
								if (data.IsCorrectData(idArray[k].Id, idArray[k].Uid, folderId, folderFullName)) {
									data[field] = value;
									messages.List[j] = data;
								}
							}
						}
					}
				}
				dict.setVal(keys[i], messages);
			}
		}
	},

	GetData: function(DataType, Key)
	{
		return this.Dictionaries[DataType].getVal( Key );
	},
	
	ReplaceData: function(DataType, Key, Value)
	{
		this.Dictionaries[DataType].setVal( Key, Value );
	},
	
	RemoveData: function(DataType, Key)
	{
		this.Dictionaries[DataType].remove(Key);
	}
};

function CHistoryStorage(SettingsStorage)
{
	// this for checking HistoryStorage can working
	this.Ready = false;
	// errors list
	this.Errors = [];
	// save input data
	if (SettingsStorage) {
		this.InputSettings = SettingsStorage;
	}
	// default value for steps limit
	this._DefaultMaxLimitSteps = 50;
	// maximum length for error list
	this._MaxErrorListLength = 20;
	// dictionary for save data
	this.Dictionary = new CDictionary();
	this.InStep = false;
	this.Queue = Array();
	this.KeysInStep = Array();
	this.PrevKey = '';
	
	this._historyKey = null;
	this._historyObjectName = null;
	this._form = null;

	// execute initialization
	this.Initialize();
}

CHistoryStorage.prototype = {
	AddError: function (StrError)
	{
		if (this.Errors.length >= this._MaxErrorListLength) {
			this.Errors.reverse().pop();
			this.Errors.reverse();
		}
		this.Errors[this.Errors.length] = StrError;
	},

	Initialize: function()
	{
		this.Ready = true;
		if (typeof this.InputSettings.Document == 'object' && this.InputSettings.Document != null) {
			this.Document = this.InputSettings.Document;
		}
        else {
			this.Ready = false;
		}
		if (typeof this.InputSettings.Browser == 'object' && this.InputSettings.Browser != null) {
			this.Browser = this.InputSettings.Browser;
		}
        else {
			this.Ready = false;
		}
		if (typeof this.InputSettings.HistoryStorageObjectName == 'string') {
			this.HistoryStorageObjectName = this.InputSettings.HistoryStorageObjectName;
		}
        else {
			this.Ready = false;
		}
		if (typeof this.InputSettings.PathToPageInIframe == 'string') {
			this.PathToPageInIframe = this.InputSettings.PathToPageInIframe;
        }
        else {
			this.Ready = false;
		}

		var _tempLimit = parseInt(this.InputSettings.MaxLimitSteps);
		if (isNaN(_tempLimit)) {
			this.AddError('The maximum number of steps that you specified is invalid. Default value 15 assigned.');
			_tempLimit = this._DefaultMaxLimitSteps;
		}
        else {
			if(_tempLimit < 1) {
				this.AddError('The maximum number of steps that you specified is invalid. Default value 15 assigned.');
				_tempLimit = this._DefaultMaxLimitSteps;
			}
		}
		this.MaxLimitSteps = _tempLimit;

		CreateChild(document.body, 'iframe', [['id', 'HistoryStorageIframe'], ['name', 'HistoryStorageIframe'], ['src', EmptyHtmlUrl], ['class', 'wm_hide']]);
		var frm = CreateChild(document.body, 'form', [['action', this.PathToPageInIframe], ['target', 'HistoryStorageIframe'], ['method', 'post'], ['id', 'HistoryForm'], ['name', 'HistoryForm'], ['class', 'wm_hide']]);
		this._historyKey = CreateChild(frm, 'input', [['type', 'text'], ['name', 'HistoryKey']]);
		this._historyObjectName = CreateChild(frm, 'input', [['type', 'text'], ['name', 'HistoryStorageObjectName']]);
		this._form = frm;
	},

	ProcessHistory: function (HistoryKey) {
		this.InStep = false;
		if (this.KeysInStep[HistoryKey]) {
			delete this.KeysInStep[HistoryKey];
		}
		else {
			this.RestoreFromHistory(HistoryKey);
		}
	},
	
	RestoreFromHistory: function (HistoryKey) {
		if (this.Dictionary.exists(HistoryKey)) {
			var HistoryObject = this.Dictionary.getVal(HistoryKey);
			eval('window.' + HistoryObject.FunctionName + '(HistoryObject.Args)');
		}
        else {
			this.AddError('The specified key doesn\'t exists in history storage');
		}

		if (this.Queue.length > 0) {
			var key = this.Queue.shift();
			if (this.Dictionary.exists(key)) {
				this.DoStep(key);
			}
		}
	},

	AddStep: function(ObjectData){
		
		var newKey = this.GenerateHistoryKey();
		if (this.Dictionary.count >= this.MaxLimitSteps) {
			//remove first step because steps count is more then limit
			var keys = this.Dictionary.keys();
			this.Dictionary.remove( keys[0] );
		}
		//add new step
		this.Dictionary.add( newKey, ObjectData );

		if (this.InStep) {
			//move step key to Queue because previouse step still not finished
			this.Queue.push(newKey);
		}
        else {
			//realize step
			this.DoStep(newKey);
		}
	},

	GenerateHistoryKey: function () {
		var key = String(new Date()) + ' ' + Math.random();
		return key.replace(/[\s\+\-\.]/g, '_').replace(/[^a-zA-Z0-9_]/g, '');
	},
	
	DoStep: function (newKey) {
		if (Browser.Mozilla && !WebMail.DataSource.WaitMessagesBodies) {
			WebMail.DataSource.NetLoader.CheckRequest();
			WebMail.HideInfo();
			//this.InStep = false;
		}
		if (this.Ready && !this.Browser.Opera) {
			if (!Browser.Mozilla || !WebMail.DataSource.WaitMessagesBodies) {
				if (this.KeysInStep[this.PrevKey]) {
					delete this.KeysInStep[this.PrevKey];
				}
				this._historyKey.value = newKey;
				this._historyObjectName.value = this.HistoryStorageObjectName;
				this._form.action = this.PathToPageInIframe + '?param=' + Math.random();
				this._form.submit();
				this.KeysInStep[newKey] = true;
				this.PrevKey = newKey;
			}
			//this.InStep = true;
			this.RestoreFromHistory(newKey);
		}
		else {
			this.RestoreFromHistory(newKey);
			this.AddError('Couldn\'t processing action. See Errors list for details.');
		}
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}