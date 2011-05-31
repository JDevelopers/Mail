/*
 * Classes:
 *  CContact()
 *  CContacts()
 *  CGroups()
 *  CGroup()
 */

function CContact()
{
	this.Type = TYPE_CONTACT;
	this.Id = -1;
	this.PrimaryEmail = PRIMARY_DEFAULT_EMAIL;
	this.UseFriendlyNm = false;
	this.Email = '';

	this.Name = ''; //FullName
	this.Title = '';
	this.FirstName = '';
	this.SurName = '';
	this.NickName = '';

	this.Day = 0;
	this.Month = 0;
	this.Year = 0;
	
	this.hEmail = '';
	this.hStreet = '';
	this.hCity = '';
	this.hState = '';
	this.hZip = '';
	this.hCountry = '';
	this.hFax = '';
	this.hPhone = '';
	this.hMobile = '';
	this.hWeb = '';

	this.bEmail = '';
	this.bCompany = '';
	this.bJobTitle = '';
	this.bDepartment = '';
	this.bOffice = '';
	this.bStreet = '';
	this.bCity = '';
	this.bState = '';
	this.bZip = '';
	this.bCountry = '';
	this.bFax = '';
	this.bPhone = '';
	this.bMobile = '';
	this.bWeb = '';
	
	this.OtherEmail = '';
	this.Notes = '';
	
	this.Groups = [];
	this.onlyMainData = true;
	this.hasHomeData = false;
	this.hasBusinessData = false;
	this.hasOtherData = false;
}

CContact.prototype = {
	GetStringDataKeys: function()
	{
		return this.Id;
	},

	GetIdForList: function ()
	{
		var arDataKeys = [ this.Id, 0, this.Name, this.Email ];
		return arDataKeys.join(STR_SEPARATOR);
	},
	
	GetInXML: function (params)
	{
		var attrs = '';
		if (this.Id != -1) attrs += ' id="' + this.Id + '"';
		attrs += ' primary_email="' + this.PrimaryEmail + '"';
		attrs += (this.UseFriendlyNm) ? ' use_friendly_nm="1"' : ' use_friendly_nm="0"';

		var nodes = '<fullname>' + GetCData(this.Name) + '</fullname>';
		nodes += '<title>' + GetCData(this.Title) + '</title>';
		nodes += '<firstname>' + GetCData(this.FirstName) + '</firstname>';
		nodes += '<surname>' + GetCData(this.SurName) + '</surname>';
		nodes += '<nickname>' + GetCData(this.NickName) + '</nickname>';
		nodes += '<birthday day="' + this.Day + '" month="' + this.Month + '" year="' + this.Year + '"/>';

		var personal = '<email>' + GetCData(this.hEmail) + '</email>';
		personal += '<street>' + GetCData(this.hStreet) + '</street>';
		personal += '<city>' + GetCData(this.hCity) + '</city>';
		personal += '<state>' + GetCData(this.hState) + '</state>';
		personal += '<zip>' + GetCData(this.hZip) + '</zip>';
		personal += '<country>' + GetCData(this.hCountry) + '</country>';
		personal += '<fax>' + GetCData(this.hFax) + '</fax>';
		personal += '<phone>' + GetCData(this.hPhone) + '</phone>';
		personal += '<mobile>' + GetCData(this.hMobile) + '</mobile>';
		personal += '<web>' + GetCData(this.hWeb) + '</web>';
		nodes += '<personal>' + personal + '</personal>';

		var business = '<email>' + GetCData(this.bEmail) + '</email>';
		business += '<company>' + GetCData(this.bCompany) + '</company>';
		business += '<job_title>' + GetCData(this.bJobTitle) + '</job_title>';
		business += '<department>' + GetCData(this.bDepartment) + '</department>';
		business += '<office>' + GetCData(this.bOffice) + '</office>';
		business += '<street>' + GetCData(this.bStreet) + '</street>';
		business += '<city>' + GetCData(this.bCity) + '</city>';
		business += '<state>' + GetCData(this.bState) + '</state>';
		business += '<zip>' + GetCData(this.bZip) + '</zip>';
		business += '<country>' + GetCData(this.bCountry) + '</country>';
		business += '<fax>' + GetCData(this.bFax) + '</fax>';
		business += '<phone>' + GetCData(this.bPhone) + '</phone>';
		business += '<modile>' + GetCData(this.bMobile) + '</modile>';
		business += '<web>' + GetCData(this.bWeb) + '</web>';
		nodes += '<business>' + business + '</business>';

		var other = '<email>' + GetCData(this.OtherEmail) + '</email>';
		other += '<notes>' + GetCData(this.Notes) + '</notes>';
		nodes += '<other>' + other + '</other>';

		var groups = '';
		var groupsCount = this.Groups.length;
		for (var groupIndex = 0; groupIndex < groupsCount; groupIndex++) {
			groups += '<group id="' + this.Groups[groupIndex].Id + '"/>';
		}

		nodes += '<groups>' + groups + '</groups>';
		return params + '<contact' + attrs + '>' + nodes + '</contact>';
	},
	
	GetFromXML: function(RootElement)
	{
		var i, j, jCount,attr, parts, nodeValue;
		attr = RootElement.getAttribute('id');if (attr) this.Id = attr;
		attr = RootElement.getAttribute('primary_email');if (attr) this.PrimaryEmail = attr - 0;
		attr = RootElement.getAttribute('use_friendly_name');if (attr) this.UseFriendlyNm = (attr == 1) ? true : false;
		var ContactParts = RootElement.childNodes;
		for (i=0; i<ContactParts.length; i++) {
			switch (ContactParts[i].tagName) {
				case 'fullname':
					parts = ContactParts[i].childNodes;
					if (parts.length > 0) this.Name = Trim(parts[0].nodeValue);
					break;
				case 'title':
					parts = ContactParts[i].childNodes;
					if (parts.length > 0) this.Title = Trim(parts[0].nodeValue);
					break;
				case 'firstname':
					parts = ContactParts[i].childNodes;
					if (parts.length > 0) this.FirstName = Trim(parts[0].nodeValue);
					break;
				case 'surname':
					parts = ContactParts[i].childNodes;
					if (parts.length > 0) this.SurName = Trim(parts[0].nodeValue);
					break;
				case 'nickname':
					parts = ContactParts[i].childNodes;
					if (parts.length > 0) this.NickName = Trim(parts[0].nodeValue);
					break;
				case 'birthday':
					attr = ContactParts[i].getAttribute('day');this.Day = attr - 0;
					attr = ContactParts[i].getAttribute('month');this.Month = attr - 0;
					attr = ContactParts[i].getAttribute('year');this.Year = attr - 0;
					if (this.Day > 0 || this.Month > 0 || this.Year > 0) {
					    this.hasOtherData = true;
					    this.onlyMainData = false;
					}
					break;
				case 'personal':
					var PersonalParts = ContactParts[i].childNodes;
					jCount = PersonalParts.length;
					for (j=0; j<jCount; j++) {
						parts = PersonalParts[j].childNodes;
						nodeValue = '';
						if (parts.length > 0) {
						    nodeValue = parts[0].nodeValue;
						}
						if (nodeValue.length > 0) {
						    this.hasHomeData = true;
							switch (PersonalParts[j].tagName) {
								case 'email':
									this.hEmail = nodeValue;
									if (this.PrimaryEmail != 0) this.onlyMainData = false;
									break;
								case 'street':
									this.hStreet = nodeValue;
									this.onlyMainData = false;
									break;
								case 'city':
									this.hCity = nodeValue;
									this.onlyMainData = false;
									break;
								case 'state':
									this.hState = nodeValue;
									this.onlyMainData = false;
									break;
								case 'zip':
									this.hZip = nodeValue;
									this.onlyMainData = false;
									break;
								case 'country':
									this.hCountry = nodeValue;
									this.onlyMainData = false;
									break;
								case 'fax':
									this.hFax = nodeValue;
									this.onlyMainData = false;
									break;
								case 'phone':
									this.hPhone = nodeValue;
									this.onlyMainData = false;
									break;
								case 'mobile':
									this.hMobile = nodeValue;
									this.onlyMainData = false;
									break;
								case 'web':
									this.hWeb = nodeValue;
									this.onlyMainData = false;
									break;
							}//switch
						}
					}//for
					break;
				case 'business':
					var BusinessParts = ContactParts[i].childNodes;
					jCount = BusinessParts.length;
					for (j=0; j<jCount; j++) {
						parts = BusinessParts[j].childNodes;
						nodeValue = '';
						if (parts.length > 0) {
						    nodeValue = parts[0].nodeValue;
						}
						if (nodeValue.length > 0) {
						    this.hasBusinessData = true;
							switch (BusinessParts[j].tagName) {
								case 'email':
									this.bEmail = nodeValue;
									if (this.PrimaryEmail != 1) this.onlyMainData = false;
									break;
								case 'company':
									this.bCompany = nodeValue;
									this.onlyMainData = false;
									break;
								case 'job_title':
									this.bJobTitle = nodeValue;
									this.onlyMainData = false;
									break;
								case 'department':
									this.bDepartment = nodeValue;
									this.onlyMainData = false;
									break;
								case 'office':
									this.bOffice = nodeValue;
									this.onlyMainData = false;
									break;
								case 'street':
									this.bStreet = nodeValue;
									this.onlyMainData = false;
									break;
								case 'city':
									this.bCity = nodeValue;
									this.onlyMainData = false;
									break;
								case 'state':
									this.bState = nodeValue;
									this.onlyMainData = false;
									break;
								case 'zip':
									this.bZip = nodeValue;
									this.onlyMainData = false;
									break;
								case 'country':
									this.bCountry = nodeValue;
									this.onlyMainData = false;
									break;
								case 'fax':
									this.bFax = nodeValue;
									this.onlyMainData = false;
									break;
								case 'phone':
									this.bPhone = nodeValue;
									this.onlyMainData = false;
									break;
								case 'mobile':
									this.bMobile = nodeValue;
									this.onlyMainData = false;
									break;
								case 'web':
									this.bWeb = nodeValue;
									this.onlyMainData = false;
									break;
							}//switch
						}
					}//for
					break;
				case 'other':
					var otherParts = ContactParts[i].childNodes;
					jCount = otherParts.length;
					for (j=0; j<jCount; j++) {
						parts = otherParts[j].childNodes;
						nodeValue = '';
						if (parts.length > 0) {
						    nodeValue = parts[0].nodeValue;
						}
						if (nodeValue.length > 0) {
						    this.hasOtherData = true;
							switch (otherParts[j].tagName) {
								case 'email':
									this.OtherEmail = nodeValue;
									if (this.PrimaryEmail != 2) this.onlyMainData = false;
									break;
								case 'notes':
									this.Notes = nodeValue;
									this.onlyMainData = false;
									break;
							}
						}
					}
					break;
				case 'groups':
					this.Groups = [];
					var GroupsParts = ContactParts[i].childNodes;
					var len = GroupsParts.length;
					for (j=0; j<len; j++) {
						switch (GroupsParts[j].tagName) {
							case 'group':
								var groupId = -1;
								var groupName = '';
								attr = GroupsParts[j].getAttribute('id');
								if (attr) groupId = attr;
								parts = GroupsParts[j].childNodes;
								if (parts.length > 0) {
									var parts2 = parts[0].childNodes;
									if (parts2.length > 0) groupName = Trim(parts2[0].nodeValue);
								}
								this.Groups.push({Id: groupId, Name: groupName});
								break;
						}
					}
					break;
			}//switch
		}//for

		switch (this.PrimaryEmail) {
			case PRIMARY_BUSS_EMAIL:
				this.Email = this.bEmail;
				break;
			case PRIMARY_OTHER_EMAIL:
				this.Email = this.OtherEmail;
				break;
			default:
				this.Email = this.hEmail;
				break;
		}
	}//GetFromXML
};

function CContacts(idGroup, lookFor)
{
	this.Type = TYPE_CONTACTS;
	this.GroupsCount = 0;
	this.ContactsCount = 0;
	this.Count = 0;
	this.SortField = null;
	this.SortOrder = null;
	this.Page = null;
	this.IdGroup = (idGroup == undefined) ? -1 : idGroup;
	this.LookFor = (lookFor == undefined) ? '' : lookFor;
	this.SearchType = 0;
	this.AddedContactId = -1;
	this.List = [];
}

CContacts.prototype = {
	GetStringDataKeys: function()
	{
		var arDataKeys = [ this.Page, this.SortField, this.SortOrder, this.IdGroup, this.LookFor ];
		return arDataKeys.join(STR_SEPARATOR);
	},
	
	GetInXml: function ()
	{
		var xml = '<param name="id_group" value="' + this.IdGroup + '"/>';
		xml += '<look_for type="' + this.SearchType + '">' + GetCData(this.LookFor) + '</look_for>';
		return xml;
	},

	GetFromXML: function(RootElement)
	{
		var attr;
		var encodeLookFor = '';
		attr = RootElement.getAttribute('groups_count');if (attr) this.GroupsCount = attr - 0;
		attr = RootElement.getAttribute('contacts_count');if (attr) this.ContactsCount = attr - 0;
		this.Count = this.GroupsCount + this.ContactsCount;
		attr = RootElement.getAttribute('page');if (attr) this.Page = attr - 0;
		attr = RootElement.getAttribute('sort_field');if (attr) this.SortField = attr - 0;
		attr = RootElement.getAttribute('sort_order');if (attr) this.SortOrder = attr - 0;
		attr = RootElement.getAttribute('id_group');if (attr) this.IdGroup = attr;
		attr = RootElement.getAttribute('added_contact_id');if (attr) this.AddedContactId = attr - 0;
		var ContactsXML = RootElement.childNodes;
		for (var i=0; i<ContactsXML.length; i++) {
			switch (ContactsXML[i].tagName) {
				case 'contact_group':
					var id = -1;var isGroup = 0;var name = '';var email = '';
					attr = ContactsXML[i].getAttribute('id');if (attr) id = attr;
					attr = ContactsXML[i].getAttribute('is_group');if (attr) isGroup = attr - 0;
					var ContactParts = ContactsXML[i].childNodes;
					var clearEmail = '';
					for (var j=0; j<ContactParts.length; j++) {
						var parts = ContactParts[j].childNodes;
						if (parts.length > 0)
							switch (ContactParts[j].tagName){
								case 'name':
									name = (encodeLookFor.length > 0 && this.SearchType == 0)
										? Trim(parts[0].nodeValue).ReplaceStr(encodeLookFor, HighlightMessageLine)
										: Trim(parts[0].nodeValue);
									break;
								case 'email':
									clearEmail = Trim(parts[0].nodeValue);
									email = (encodeLookFor.length > 0 && this.SearchType == 0)
										? clearEmail.ReplaceStr(encodeLookFor, HighlightMessageLine) : clearEmail;
									break;
							}
					}
					if (this.SearchType == 1) {
						var displayText = '';
						var replaceText = '';
						if (isGroup) {
							displayText = (encodeLookFor.length > 0)
								? name.ReplaceStr(encodeLookFor, HighlightContactLine) : name;
							
							replaceText = HtmlDecode(email);
						} else if (name.length > 0) {
							displayText = (encodeLookFor.length > 0)
								? '"' + name.ReplaceStr(encodeLookFor, HighlightContactLine) + '" &lt;' + email.ReplaceStr(encodeLookFor, HighlightContactLine) + '&gt;'
								: '"' + name + '" &lt;' + email + '&gt;';
								
							replaceText = HtmlDecode('"' + name + '" <' + email + '>');
						} else {
							displayText = (encodeLookFor.length > 0)
								? email.ReplaceStr(encodeLookFor, HighlightContactLine) : email;

							replaceText = HtmlDecode(email);
						}
						this.List.push({Id: id, IsGroup: isGroup, DisplayText: displayText, ReplaceText: replaceText});
					} else {
						if (isGroup && email.length > 0) {
							email = '<span class="wm_secondary_info">' + Lang.GroupMembers + ': </span>' + email;
						}
						this.List.push({Id: id, IsGroup: isGroup, Name: name, Email: email, ClearEmail: clearEmail});
					}
				break;
				case 'look_for':
					attr = ContactsXML[i].getAttribute('type');if (attr) this.SearchType = attr - 0;
					var LookForParts = ContactsXML[i].childNodes;
					if (LookForParts.length > 0) {
						this.LookFor = Trim(LookForParts[0].nodeValue);
						encodeLookFor = HtmlEncode(this.LookFor);
					}
				break;
			}//switch
		}//for
	}//GetFromXML
};

function CGroups()
{
	this.Type = TYPE_GROUPS;
	this.Items = [];
}

CGroups.prototype = {
	GetStringDataKeys: function()
	{
		return '';
	},

	GetFromXML: function(RootElement)
	{
		var groupParts = RootElement.childNodes;
		for (var i=0; i<groupParts.length; i++) {
			if (groupParts[i].tagName == 'group') {
				var id = -1;
				var attr = groupParts[i].getAttribute('id');
				if (attr) id = attr;
				var groupContent = groupParts[i].childNodes;
				if (groupContent.length > 0) {
					var name = '';
					if (groupContent[0].tagName == 'name') {
						var parts = groupContent[0].childNodes;
						if (parts.length > 0)
							name = Trim(parts[0].nodeValue);
					}
				}
				this.Items.push({Id: id, Name: name});
			}
		}
	}
};

function CGroup()
{
	this.Type = TYPE_GROUP;
	this.Id = -1;
	this.Name = '';
	this.Contacts = [];
	this.NewContacts = [];
	this.isOrganization = false;
	this.Email = '';
	this.Company = '';
	this.Street = '';
	this.City = '';
	this.State = '';
	this.Zip = '';
	this.Country = '';
	this.Fax = '';
	this.Phone = '';
	this.Web = '';
}

CGroup.prototype = {
	GetStringDataKeys: function()
	{
		return this.Id;
	},

	GetInXml: function (params)
	{
		var i, iCount;
		var attrs = (this.Id != -1) ? ' id="' + this.Id + '"' : '';
		attrs += (this.isOrganization) ? ' organization="1"' : ' organization="0"';

		var contacts = '';
		iCount = this.Contacts.length;
		for (i=0; i<iCount; i++) {
			contacts += '<contact id="' + this.Contacts[i].Id + '"/>';
		}
		var newContacts = '';
		iCount = this.NewContacts.length;
		for (i=0; i<iCount; i++) {
			newContacts += '<contact><personal><email>' + GetCData(this.NewContacts[i].Email) + '</email></personal></contact>';
		}
		var xml = params + '<group' + attrs + '>';
		xml += '<name>' + GetCData(this.Name) + '</name>';
		xml += '<email>' + GetCData(this.Email) + '</email>';
		xml += '<company>' + GetCData(this.Company) + '</company>';
		xml += '<street>' + GetCData(this.Street) + '</street>';
		xml += '<city>' + GetCData(this.City) + '</city>';
		xml += '<state>' + GetCData(this.State) + '</state>';
		xml += '<zip>' + GetCData(this.Zip) + '</zip>';
		xml += '<country>' + GetCData(this.Country) + '</country>';
		xml += '<fax>' + GetCData(this.Fax) + '</fax>';
		xml += '<phone>' + GetCData(this.Phone) + '</phone>';
		xml += '<web>' + GetCData(this.Web) + '</web>';
		xml += '<contacts>' + contacts + '</contacts>';
		xml += '<new_contacts>' + newContacts + '</new_contacts>';
		xml += '</group>';
		return xml;
	},
	
	GetFromXML: function(RootElement)
	{
		var i, attr, parts;
		attr = RootElement.getAttribute('id');if (attr) this.Id = attr;
		attr = RootElement.getAttribute('organization');if (attr) this.isOrganization = (attr == 1) ? true : false;
		var GroupParts = RootElement.childNodes;
		for (i=0; i<GroupParts.length; i++) {
			switch (GroupParts[i].tagName) {
				case 'name':
					parts = GroupParts[i].childNodes;
					if (parts.length > 0) this.Name = Trim(parts[0].nodeValue);
				break;
				case 'email':
					parts = GroupParts[i].childNodes;
					if (parts.length > 0) this.Email = Trim(parts[0].nodeValue);
				break;
				case 'company':
					parts = GroupParts[i].childNodes;
					if (parts.length > 0) this.Company = Trim(parts[0].nodeValue);
				break;
				case 'street':
					parts = GroupParts[i].childNodes;
					if (parts.length > 0) this.Street = Trim(parts[0].nodeValue);
				break;
				case 'city':
					parts = GroupParts[i].childNodes;
					if (parts.length > 0) this.City = Trim(parts[0].nodeValue);
				break;
				case 'state':
					parts = GroupParts[i].childNodes;
					if (parts.length > 0) this.State = Trim(parts[0].nodeValue);
				break;
				case 'zip':
					parts = GroupParts[i].childNodes;
					if (parts.length > 0) this.Zip = Trim(parts[0].nodeValue);
				break;
				case 'country':
					parts = GroupParts[i].childNodes;
					if (parts.length > 0) this.Country = Trim(parts[0].nodeValue);
				break;
				case 'fax':
					parts = GroupParts[i].childNodes;
					if (parts.length > 0) this.Fax = Trim(parts[0].nodeValue);
				break;
				case 'phone':
					parts = GroupParts[i].childNodes;
					if (parts.length > 0) this.Phone = Trim(parts[0].nodeValue);
				break;
				case 'web':
					parts = GroupParts[i].childNodes;
					if (parts.length > 0) this.Web = Trim(parts[0].nodeValue);
				break;
				case 'contacts':
					var contacts = GroupParts[i].childNodes;
					var jCount = contacts.length;
					for (var j=0; j<jCount; j++) {
						if (contacts[j].tagName == 'contact') {
							var id = -1;
							attr = contacts[j].getAttribute('id');
							if (attr) id = attr;
							var contContent = contacts[j].childNodes;
							var name = '';
							var email = '';
							var kCount = contContent.length;
							for (var k=0; k<kCount; k++) {
								parts = contContent[k].childNodes;
								if (parts.length > 0)
									switch (contContent[k].tagName) {
										case 'fullname':
											name = Trim(parts[0].nodeValue);
											break;
										case 'email':
											email = Trim(parts[0].nodeValue);
											break;
									}
							}
							this.Contacts.push({Id: id, Name: name, Email: email});
						}
					}
					break;
			}//switch
		}//for
	}//GetFromXML
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}