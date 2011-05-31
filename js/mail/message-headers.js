/*
 * Prototypes:
 *  ViewContactPrototype
 * Classes:
 *  CContactCard()
 *  CMessageCharsetSelector(_parent)
 *  CAddToAddressBookImg(_parent)
 *  CPreviewPaneMessageHeaders()
 */
 
var ViewContactPrototype = {
	FillDefSection: function (cont)
	{
		if (cont.Name.length > 0) {
			this._fullnameObj.innerHTML = cont.Name;
			this._fullnameCont.className = '';
		}
		else this._fullnameCont.className = 'wm_hide';
        this._contactEmail = (cont.UseFriendlyNm && cont.Name.length > 0)
            ? '"' + cont.Name + '" <' + HtmlDecode(cont.Email) + '>'
            : HtmlDecode(cont.Email);
		if (cont.Email.length > 0) {
			this._defaultEmailObj.innerHTML = cont.Email;
			this._defaultEmailCont.className = '';
		}
		else {
            this._defaultEmailCont.className = 'wm_hide';
        }
		var birthDay = GetBirthDay(cont.Day, cont.Month, cont.Year);
		if (birthDay.length > 0) {
			this._birthdayObj.innerHTML = birthDay;
			this._birthdayCont.className = '';
		}
		else this._birthdayCont.className = 'wm_hide';
		if (cont.Title.length > 0) {
			this._titleObj.innerHTML = cont.Title;
			this._titleCont.className = '';
		}
		else this._titleCont.className = 'wm_hide';
		if (cont.FirstName.length > 0) {
			this._firstnameObj.innerHTML = cont.FirstName;
			this._firstnameCont.className = '';
		}
		else this._firstnameCont.className = 'wm_hide';
		if (cont.SurName.length > 0) {
			this._surnameObj.innerHTML = cont.SurName;
			this._surnameCont.className = '';
		}
		else this._surnameCont.className = 'wm_hide';
		if (cont.NickName.length > 0) {
			this._nicknameObj.innerHTML = cont.NickName;
			this._nicknameCont.className = '';
		}
		else this._nicknameCont.className = 'wm_hide';
	},
	
	FillHomeSection: function (cont)
	{
		var emptySection = true;
		if (cont.hEmail.length > 0 && cont.PrimaryEmail != PRIMARY_HOME_EMAIL) {
			this._contactHEmail = (cont.UseFriendlyNm && cont.Name.length > 0)
				? '"' + cont.Name + '" <' + HtmlDecode(cont.hEmail) + '>'
				: HtmlDecode(cont.hEmail);
			this._hEmailObj.innerHTML = cont.hEmail;
			this._hEmailCont.className = '';
			emptySection = false;
		}
		else this._hEmailCont.className = 'wm_hide';
		if (cont.hStreet.length > 0) {
			this._hStreetObj.innerHTML = cont.hStreet;
			this._hStreetCont.className = '';
			emptySection = false;
		}
		else this._hStreetCont.className = 'wm_hide';
		if (cont.hCity.length > 0 || cont.hFax.length > 0) {
			this._hCityObj.innerHTML = cont.hCity;
			this._hCityTitle.innerHTML = (cont.hCity.length > 0) ? Lang.City + ':' : '';
			this._hFaxObj.innerHTML = cont.hFax;
			this._hFaxTitle.innerHTML = (cont.hFax.length > 0) ? Lang.Fax + ':' : '';
			this._hCityFaxCont.className = '';
			emptySection = false;
		}
		else this._hCityFaxCont.className = 'wm_hide';
		if (cont.hState.length > 0 || cont.hPhone.length > 0) {
			this._hStateObj.innerHTML = cont.hState;
			this._hStateTitle.innerHTML = (cont.hState.length > 0) ? Lang.StateProvince + ':' : '';
			this._hPhoneObj.innerHTML = cont.hPhone;
			this._hPhoneTitle.innerHTML = (cont.hPhone.length > 0) ? Lang.Phone + ':' : '';
			this._hStatePhoneCont.className = '';
			emptySection = false;
		}
		else this._hStatePhoneCont.className = 'wm_hide';
		if (cont.hZip.length > 0 || cont.hMobile.length > 0) {
			this._hZipObj.innerHTML = cont.hZip;
			this._hZipTitle.innerHTML = (cont.hZip.length > 0) ? Lang.ZipCode + ':' : '';
			this._hMobileObj.innerHTML = cont.hMobile;
			this._hMobileTitle.innerHTML = (cont.hMobile.length > 0) ? Lang.Mobile + ':' : '';
			this._hZipMobileCont.className = '';
			emptySection = false;
		}
		else this._hZipMobileCont.className = 'wm_hide';
		if (cont.hCountry.length > 0) {
			this._hCountryObj.innerHTML = cont.hCountry;
			this._hCountryCont.className = '';
			emptySection = false;
		}
		else this._hCountryCont.className = 'wm_hide';
		if (cont.hWeb.length > 0) {
			this._contactHWeb = cont.hWeb;
			this._hWebObj.innerHTML = cont.hWeb;
			this._hWebCont.className = '';
			emptySection = false;
		}
		else this._hWebCont.className = 'wm_hide';
		if (emptySection) this._personalTbl.className = 'wm_hide';
		else this._personalTbl.className = this._sectionClassName;
	},
	
	FillBusinessSection: function (cont)
	{
		var emptySection = true;
		if (cont.bEmail.length > 0 && cont.PrimaryEmail != PRIMARY_BUSS_EMAIL) {
			this._contactBEmail = (cont.UseFriendlyNm && cont.Name.length > 0)
				? '"' + cont.Name + '" <' + HtmlDecode(cont.bEmail) + '>'
				: HtmlDecode(cont.bEmail);
			this._bEmailObj.innerHTML = cont.bEmail;
			this._bEmailCont.className = '';
			emptySection = false;
		}
		else this._bEmailCont.className = 'wm_hide';
		if (cont.bCompany.length > 0 || cont.bJobTitle.length > 0) {
			this._bCompanyObj.innerHTML = cont.bCompany;
			this._bCompanyTitle.innerHTML = (cont.bCompany.length > 0) ? Lang.Company + ':' : '';
			this._bJobTitleObj.innerHTML = cont.bJobTitle;
			this._bJobTitleTitle.innerHTML = (cont.bJobTitle.length > 0) ? Lang.JobTitle + ':' : '';
			this._bCompanyJobTitleCont.className = '';
			emptySection = false;
		}
		else this._bCompanyJobTitleCont.className = 'wm_hide';
		if (cont.bDepartment.length > 0 || cont.bOffice.length > 0) {
			this._bDepartmentObj.innerHTML = cont.bDepartment;
			this._bDepartmentTitle.innerHTML = (cont.bDepartment.length > 0) ? Lang.Department + ':' : '';
			this._bOfficeObj.innerHTML = cont.bOffice;
			this._bOfficeTitle.innerHTML = (cont.bOffice.length > 0) ? Lang.Office + ':' : '';
			this._bDepartmentOfficeCont.className = '';
			emptySection = false;
		}
		else this._bDepartmentOfficeCont.className = 'wm_hide';
		if (cont.bStreet.length > 0) {
			this._bStreetObj.innerHTML = cont.bStreet;
			this._bStreetCont.className = '';
			emptySection = false;
		}
		else this._bStreetCont.className = 'wm_hide';
		if (cont.bCity.length > 0 || cont.bFax.length > 0) {
			this._bCityObj.innerHTML = cont.bCity;
			this._bCityTitle.innerHTML = (cont.bCity.length > 0) ? Lang.City + ':' : '';
			this._bFaxObj.innerHTML = cont.bFax;
			this._bFaxTitle.innerHTML = (cont.bFax.length > 0) ? Lang.Fax + ':' : '';
			this._bCityFaxCont.className = '';
			emptySection = false;
		}
		else this._bCityFaxCont.className = 'wm_hide';
		if (cont.bState.length > 0 || cont.bPhone.length > 0) {
			this._bStateObj.innerHTML = cont.bState;
			this._bStateTitle.innerHTML = (cont.bState.length > 0) ? Lang.StateProvince + ':' : '';
			this._bPhoneObj.innerHTML = cont.bPhone;
			this._bPhoneTitle.innerHTML = (cont.bPhone.length > 0) ? Lang.Phone + ':' : '';
			this._bStatePhoneCont.className = '';
			emptySection = false;
		}
		else this._bStatePhoneCont.className = 'wm_hide';
		if (cont.bZip.length > 0 || cont.bCountry.length > 0) {
			this._bZipObj.innerHTML = cont.bZip;
			this._bZipTitle.innerHTML = (cont.bZip.length > 0) ? Lang.ZipCode + ':' : '';
			this._bCountryObj.innerHTML = cont.bCountry;
			this._bCountryTitle.innerHTML = (cont.bCountry.length > 0) ? Lang.CountryRegion + ':' : '';
			this._bZipCountryCont.className = '';
			emptySection = false;
		}
		else this._bZipCountryCont.className = 'wm_hide';
		if (cont.bMobile.length > 0) {
			this._bMobileObj.innerHTML = cont.bMobile;
			this._bMobileTitle.innerHTML = Lang.Mobile + ':';
			this._bMobileCont.className = '';
			emptySection = false;
		}
		else this._bMobileCont.className = 'wm_hide';
		if (cont.bWeb.length > 0) {
			this._contactBWeb = cont.bWeb;
			this._bWebObj.innerHTML = cont.bWeb;
			this._bWebCont.className = '';
			emptySection = false;
		}
		else this._bWebCont.className = 'wm_hide';
		if (emptySection) this._businessTbl.className = 'wm_hide';
		else this._businessTbl.className = this._sectionClassName;
	},
	
	FillOtherSection: function (cont)
	{
		var emptySection = true;
		if (cont.OtherEmail.length > 0 && cont.PrimaryEmail != PRIMARY_OTHER_EMAIL) {
			this._contactOtherEmail = (cont.UseFriendlyNm && cont.Name.length > 0)
				? '"' + cont.Name + '" <' + HtmlDecode(cont.OtherEmail) + '>'
				: HtmlDecode(cont.OtherEmail);
			this._otherEmailObj.innerHTML = cont.OtherEmail;
			this._otherEmailCont.className = '';
			emptySection = false;
		}
		else this._otherEmailCont.className = 'wm_hide';
		if (cont.Notes.length > 0) {
			this._notesObj.innerHTML = cont.Notes;
			this._notesCont.className = '';
			emptySection = false;
		}
		else this._notesCont.className = 'wm_hide';
		if (emptySection) this._otherTbl.className = 'wm_hide';
		else this._otherTbl.className = this._sectionClassName;
	},
	
	BuildDefSection: function (container)
	{
		var tbl = CreateChild(container, 'table');
		tbl.className = this._sectionClassName;
		tbl.style.marginTop = '0';

		var rowIndex = 0;
		var tr = tbl.insertRow(rowIndex++);
		var td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.Name + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Name', ':');
		td = tr.insertCell(1);
		td.className = this._nameClassName;
		this._fullnameObj = td;
		this._fullnameCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.ContactTitle + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'ContactTitle', ':');
		td = tr.insertCell(1);
		td.className = this._nameClassName;
		this._titleObj = td;
		this._titleCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.ContactFirstName + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'ContactFirstName', ':');
		td = tr.insertCell(1);
		td.className = this._nameClassName;
		this._firstnameObj = td;
		this._firstnameCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.ContactSurName + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'ContactSurName', ':');
		td = tr.insertCell(1);
		td.className = this._nameClassName;
		this._surnameObj = td;
		this._surnameCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.ContactNickName + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'ContactNickName', ':');
		td = tr.insertCell(1);
		td.className = this._nameClassName;
		this._nicknameObj = td;
		this._nicknameCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.Email + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Email', ':');
		td = tr.insertCell(1);
		td.className = this._emailClassName;
		var a = CreateChild(td, 'a', [['href', '#']]);
		this._defaultEmailObj = a;
		this._defaultEmailCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.Birthday + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Birthday', ':');
		td = tr.insertCell(1);
		this._birthdayObj = td;
		this._birthdayCont = tr;
	},
	
	BuildHomeSection: function (container)
	{
		var tbl = CreateChild(container, 'table');
		this._personalTbl = tbl;
		tbl.className = 'wm_hide';
		var rowIndex = 0;
		var tr = tbl.insertRow(rowIndex++);
		var td = tr.insertCell(0);
		td.className = this._sectionNameClassName;
		td.colSpan = 4;
		td.innerHTML = Lang.Home;
		WebMail.LangChanger.Register('innerHTML', td, 'Home', '');
		
		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.PersonalEmail + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'PersonalEmail', ':');
		td = tr.insertCell(1);
		td.colSpan = 3;
		var a = CreateChild(td, 'a', [['href', '#']]);
		a.onclick = function () { return false; };
		this._hEmailObj = a;
		this._hEmailCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.StreetAddress + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'StreetAddress', ':');
		td = tr.insertCell(1);
		td.colSpan = 3;
		this._hStreetObj = td;
		this._hStreetCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.City + ':';
		this._hCityTitle = td;
		td = tr.insertCell(1);
		this._hCityObj = td;
		td = tr.insertCell(2);
		td.className = this._titleClassName;
		td.innerHTML = Lang.Fax + ':';
		this._hFaxTitle = td;
		td = tr.insertCell(3);
		this._hFaxObj = td;
		this._hCityFaxCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.StateProvince + ':';
		this._hStateTitle = td;
		td = tr.insertCell(1);
		this._hStateObj = td;
		td = tr.insertCell(2);
		td.className = this._titleClassName;
		td.innerHTML = Lang.Phone + ':';
		this._hPhoneTitle = td;
		td = tr.insertCell(3);
		this._hPhoneObj = td;
		this._hStatePhoneCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.ZipCode + ':';
		this._hZipTitle = td;
		td = tr.insertCell(1);
		this._hZipObj = td;
		td = tr.insertCell(2);
		td.className = this._titleClassName;
		td.innerHTML = Lang.Mobile + ':';
		this._hMobileTitle = td;
		td = tr.insertCell(3);
		this._hMobileObj = td;
		this._hZipMobileCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.CountryRegion + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'CountryRegion', ':');
		td = tr.insertCell(1);
		td.colSpan = 3;
		this._hCountryObj = td;
		this._hCountryCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.WebPage + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'WebPage', ':');
		td = tr.insertCell(1);
		td.colSpan = 3;
		a = CreateChild(td, 'a', [['href', '#']]);
		this._hWebObj = a;
		this._hWebCont = tr;
	},
	
	BuildBusinessSection: function (container)
	{
		var tbl = CreateChild(container, 'table');
		this._businessTbl = tbl;
		tbl.className = 'wm_hide';
		var rowIndex = 0;
		var tr = tbl.insertRow(rowIndex++);
		var td = tr.insertCell(0);
		td.className = this._sectionNameClassName;
		td.colSpan = 4;
		td.innerHTML = Lang.Business;
		WebMail.LangChanger.Register('innerHTML', td, 'Business', '');
		
		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.BusinessEmail + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'BusinessEmail', ':');
		td = tr.insertCell(1);
		td.colSpan = 3;
		var a = CreateChild(td, 'a', [['href', '#']]);
		a.onclick = function () { return false; };
		this._bEmailObj = a;
		this._bEmailCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.Company + ':';
		this._bCompanyTitle = td;
		td = tr.insertCell(1);
		this._bCompanyObj = td;
		td = tr.insertCell(2);
		td.className = this._titleClassName;
		td.innerHTML = Lang.JobTitle + ':';
		this._bJobTitleTitle = td;
		td = tr.insertCell(3);
		this._bJobTitleObj = td;
		this._bCompanyJobTitleCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.Department + ':';
		this._bDepartmentTitle = td;
		td = tr.insertCell(1);
		this._bDepartmentObj = td;
		td = tr.insertCell(2);
		td.className = this._titleClassName;
		td.innerHTML = Lang.Office + ':';
		this._bOfficeTitle = td;
		td = tr.insertCell(3);
		this._bOfficeObj = td;
		this._bDepartmentOfficeCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.StreetAddress + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'StreetAddress', ':');
		td = tr.insertCell(1);
		td.colSpan = 3;
		this._bStreetObj = td;
		this._bStreetCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.City + ':';
		this._bCityTitle = td;
		td = tr.insertCell(1);
		this._bCityObj = td;
		td = tr.insertCell(2);
		td.className = this._titleClassName;
		td.innerHTML = Lang.Fax + ':';
		this._bFaxTitle = td;
		td = tr.insertCell(3);
		this._bFaxObj = td;
		this._bCityFaxCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.StateProvince + ':';
		this._bStateTitle = td;
		td = tr.insertCell(1);
		this._bStateObj = td;
		td = tr.insertCell(2);
		td.className = this._titleClassName;
		td.innerHTML = Lang.Phone + ':';
		this._bPhoneTitle = td;
		td = tr.insertCell(3);
		this._bPhoneObj = td;
		this._bStatePhoneCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.ZipCode + ':';
		this._bZipTitle = td;
		td = tr.insertCell(1);
		this._bZipObj = td;
		td = tr.insertCell(2);
		td.className = this._titleClassName;
		td.innerHTML = Lang.CountryRegion + ':';
		this._bCountryTitle = td;
		td = tr.insertCell(3);
		this._bCountryObj = td;
		this._bZipCountryCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.Mobile + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Mobile', ':');
		this._bMobileTitle = td;
		td = tr.insertCell(1);
		td.colSpan = 3;
		this._bMobileObj = td;
		this._bMobileCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.WebPage + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'WebPage', ':');
		td = tr.insertCell(1);
		td.colSpan = 3;
		a = CreateChild(td, 'a', [['href', '#']]);
		this._bWebObj = a;
		this._bWebCont = tr;
	},
	
	BuildOtherSection: function (container)
	{
		var tbl = CreateChild(container, 'table');
		this._otherTbl = tbl;
		tbl.className = 'wm_hide';
		var rowIndex = 0;
		var tr = tbl.insertRow(rowIndex++);
		var td = tr.insertCell(0);
		td.className = this._sectionNameClassName;
		td.colSpan = 2;
		td.innerHTML = Lang.Other;
		WebMail.LangChanger.Register('innerHTML', td, 'Other', '');

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.OtherEmail + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'OtherEmail', ':');
		td = tr.insertCell(1);
		var a = CreateChild(td, 'a', [['href', '#']]);
		a.onclick = function () { return false; };
		this._otherEmailObj = a;
		this._otherEmailCont = tr;

		tr = tbl.insertRow(rowIndex++);
		td = tr.insertCell(0);
		td.className = this._titleClassName;
		td.innerHTML = Lang.Notes + ':';
		WebMail.LangChanger.Register('innerHTML', td, 'Notes', ':');
		td = tr.insertCell(1);
		this._notesObj = td;
		this._notesCont = tr;
	}
};

function CContactCard() 
{
	this._contact = null;
	this._contactEmail = '';
	this._contactHEmail = '';
	this._contactBEmail = '';
	this._contactOtherEmail = '';
	this._contactHWeb = '';
	this._contactBWeb = '';

	this._mainCont = null;
	
	this._fullnameObj = null;
	this._fullnameCont = null;
	this._defaultEmailObj = null;
	this._defaultEmailCont = null;
	this._birthdayObj = null;
	this._birthdayCont = null;

	this._builded = false;
	this._pageX = 0;
	this._pageY = 0;
	
	this._sectionClassName = '';
	this._titleClassName = 'wm_line_title';
	this._nameClassName = '';
	this._emailClassName = '';
	this._sectionNameClassName = 'wm_section_name';

	this._showTimeOut = Math.NaN;
	this._shown = false;

	this.SetContact = function (contact)
	{
		this._contact = contact;
	};
	
	this.Go = function ()
	{
		SetHistoryHandler(
			{
				ScreenId: SCREEN_CONTACTS,
				Entity: PART_VIEW_CONTACT,
				IdAddr: ContactCard._contact.Id,
				IdAcct: WebMail._idAcct
			}
		);
		return false;
	};
	
	this.Show = function (e)
	{
		ContactCard.ClearTimeOut();
		if (ContactCard._shown) {
			return;
		}
		ContactCard._build();
		
		e = e ? e : window.event;
		ContactCard._pageX = e.clientX;
		ContactCard._pageY = e.clientY;
		if (Browser.Mozilla) {
			ContactCard._pageX = e.pageX;
			ContactCard._pageY = e.pageY;
		}
		if (Browser.Opera) {
			ContactCard._pageX += document.documentElement.scrollLeft - document.documentElement.clientLeft;
			ContactCard._pageY += document.documentElement.scrollTop - document.documentElement.clientTop;
		}
		ContactCard._showTimeOut = setTimeout('ContactCard._show()', 200);
	};
	
	this._show = function ()
	{
		ContactCard._mainCont.className = 'wm_contact_card';
		ContactCard.FillDefSection(ContactCard._contact);
		ContactCard.FillHomeSection(ContactCard._contact);
		ContactCard.FillBusinessSection(ContactCard._contact);
		ContactCard.FillOtherSection(ContactCard._contact);
		
		ContactCard._mainCont.style.left = ContactCard._pageX + 10 + 'px';
		ContactCard._mainCont.style.top = ContactCard._pageY + 10 + 'px';
		ContactCard._mainCont.style.backgroundPosition = '0 ' + (ContactCard._mainCont.offsetHeight - 50) + 'px';
		ContactCard._shown = true;
	};
	
	this.Hide = function ()
	{
		ContactCard.ClearTimeOut();
		ContactCard._showTimeOut = setTimeout('ContactCard._hide()', 400);
	};
	
	this.ClearTimeOut = function ()
	{
		if (!isNaN(ContactCard._showTimeOut)) {
			clearTimeout(ContactCard._showTimeOut);
			ContactCard._showTimeOut = Math.NaN;
		}
	};
	
	this._hide = function ()
	{
		ContactCard.ClearTimeOut();
		ContactCard._mainCont.className = 'wm_hide';
		ContactCard._shown = false;
	};
	
	this._mailTo = function (email)
	{
		if (ContactCard._contact != null) {
			MailToHandler(email);
		}
		ContactCard._hide();
	};
	
	this._openUrl = function (url)
	{
		if (ContactCard._contact != null) {
			OpenURL(url);
		}
		ContactCard._hide();
	};
	
	this._build = function ()
	{
		if (this._builded) {
			return;
		}
		
		this._mainCont = CreateChild(document.body, 'table', [['class', 'wm_contact_card']]);
		this._mainCont.onmouseover = ContactCard.Show;
		this._mainCont.onmouseout = ContactCard.Hide;
		var tr = this._mainCont.insertRow(0);
		var td = tr.insertCell(0);
		td.style.textAlign = 'left';
		var a = CreateChild(td, 'a', [['href', '#']]);
		a.onclick = function () {
			ContactCard._hide();
			ContactCard._mailTo(ContactCard._contactEmail);
			return false;
		};
		a.innerHTML = Lang.ContactMail;
		WebMail.LangChanger.Register('innerHTML', a, 'ContactMail', '');
		
		td = tr.insertCell(1);
		td.style.textAlign = 'right';
		a = CreateChild(td, 'a', [['href', '#']]);
		a.onclick = function () {
			if (ContactCard._contact != null) {
				ContactCard._hide();
				ViewAllContactMailsHandler(ContactCard._contact);
			}
			return false;
		};
		a.innerHTML = Lang.ContactViewAllMails;
		a.className = (UseCustomContacts) ? 'wm_hide' : '';
		WebMail.LangChanger.Register('innerHTML', a, 'ContactViewAllMails', '');
		
		tr = this._mainCont.insertRow(1);
		td = tr.insertCell(0);
		td.colSpan = 2;
		td.className = 'wm_view_sections';
		this.BuildDefSection(td);
		this.BuildHomeSection(td);
		this.BuildBusinessSection(td);
		this.BuildOtherSection(td);
		
		this._defaultEmailObj.onclick = function () {
			ContactCard._mailTo(ContactCard._contactEmail);
			return false;
		};
		this._hEmailObj.onclick = function () {
			ContactCard._mailTo(ContactCard._contactHEmail);
			return false;
		};
		this._bEmailObj.onclick = function () {
			ContactCard._mailTo(ContactCard._contactBEmail);
			return false;
		};
		this._otherEmailObj.onclick = function () {
			ContactCard._mailTo(ContactCard._contactOtherEmail);
			return false;
		};
		this._hWebObj.onclick = function () {
			ContactCard._openUrl(ContactCard._contactHWeb);
			return false;
		};
		this._bWebObj.onclick = function () {
			ContactCard. _openUrl(ContactCard._contactBWeb);
			return false;
		};
		this._builded = true;
	};
}
CContactCard.prototype = ViewContactPrototype;
var ContactCard = new CContactCard();

function CAddToAddressBookImg(_parent)
{
	this._fromName = '';
	this._fromEmail = '';
	this._img = null;
	
	this.Show = function (name, email)
	{
		this._fromName = name;
		this._fromEmail = email;
		this._img.className = 'wm_add_address_book_img';
	};
	
	this.Hide = function ()
	{
		this._img.className = 'wm_hide';
	};
	
	this._build = function ()
	{
		var img, obj;
		img = CreateChild(_parent, 'span', [['class', 'wm_hide'], ['style', 'margin: 0 0 0 1px'], ['title', Lang.AddToAddressBook]]);
		img.innerHTML = '&nbsp;';
		WebMail.LangChanger.Register('title', img, 'AddToAddressBook', '');
		obj = this;
		img.onclick = function () {
			if (obj._fromName.length == 0 && obj._fromEmail.length == 0) {
				return;
			}
			SetHistoryHandler(
				{
					ScreenId: SCREEN_CONTACTS,
					Entity: PART_NEW_CONTACT,
					Name: obj._fromName,
					Email: obj._fromEmail
				}
			);
		};
		this._img = img;
	};
	
	this._build();
}

function CMessageCharsetSelector(_parent)
{
	this._hasCharset = true;
	this._charset = AUTOSELECT_CHARSET;
	this._full = false;
	
	this.Show = function ()
	{
		this.Fill(this._hasCharset, this._charset, this._full);
	};
	
	this.Hide = function ()
	{
		_parent.innerHTML = '';
	};
	
	this.GetWidth = function ()
	{
		return _parent.clientWidth;
	};
	
	this.Fill = function (hasCharset, charset, full)
	{
		this._hasCharset = hasCharset;
		this._charset = charset;
		this._full = full;
		_parent.innerHTML = '';
		if (this._hasCharset && this._charset == AUTOSELECT_CHARSET) {
			return false;
		}
		
		if (full) {
			var font = CreateChild(_parent, 'font');
			font.innerHTML = Lang.Charset + ':';
		}
		var sel = CreateChild(_parent, 'select');
		sel.onchange = function () {
			if (WebMail.ScreenId == WebMail.ListScreenId) {
				var screen = WebMail.Screens[WebMail.ScreenId];
				if (screen) {
					var historyObj = screen.GetCurrMessageHistoryObject();
					historyObj.MsgCharset = this.value;
					SetHistoryHandler(historyObj);
				}
			}
		};
		for (var i in Charsets) {
			var value = (Charsets[i].Value == 0) ? AUTOSELECT_CHARSET : Charsets[i].Value;
			var opt = CreateChild(sel, 'option', [['value', value]]);
			opt.innerHTML = Charsets[i].Name;
			opt.selected = (charset == value);
		}
		sel.blur();
		return true;
	};
}

function CPreviewPaneMessageHeaders(inNewWindowMode)
{
	this.InNewWindowMode = inNewWindowMode;
	this._container = null;
	this._contPadding = 6;
	this._spanMargin = 4;
	
	this._shortLines = [];
	this._fullLines = [];

	this._subjCont = null;
	this._showDetailsCont = null;
	this._hideDetailsCont = null;

	this._readConfValue = null;

	this._shortFromCont = null;
	this._shortAddToABCont = null;
	this._shortAddToABObj = null;
	this._shortDateCont = null;

	this._fromCont = null;
	this.SwitcherCont = null;
	this.SwitcherObj = null;
	this._addToABCont = null;
	this._addToABObj = null;
	this._toCont = null;
	this._toLine = null;
	this._toLineClassName = '';
	this._dateCont = null;
	this._charsetSelector = null;
	this._charsetLine = null;
	this._ccCont = null;
	this._bccCont = null;
	this._copiesCont = null;
	
	this._shown = false;
	this._hasCopies = false;
	this._showFull = false;
}

CPreviewPaneMessageHeaders.prototype = {
	ShowShort: function (showDetailsSwitcher)
	{
		for (var shortIndex = 0; shortIndex < this._shortLines.length; shortIndex++) {
			this._shortLines[shortIndex].className = '';
		}
		for (var fullIndex = 0; fullIndex < this._fullLines.length; fullIndex++) {
			this._fullLines[fullIndex].className = 'wm_hide';
		}
		this._toLine.className = 'wm_hide';
		this._copiesCont.className = 'wm_hide';
		if (showDetailsSwitcher) {
			this._showDetailsCont.className = 'wm_message_right';
		}
		this._showFull = false;
	},
	
	ShowFull: function ()
	{
		for (var shortIndex = 0; shortIndex < this._shortLines.length; shortIndex++) {
			this._shortLines[shortIndex].className = 'wm_hide';
		}
		for (var fullIndex = 0; fullIndex < this._fullLines.length; fullIndex++) {
			this._fullLines[fullIndex].className = '';
		}
		this._toLine.className = this._toLineClassName;
		this._copiesCont.className = (this._hasCopies) ? '' : 'wm_hide';
		this._showFull = true;
	},

	SwitchDetails: function ()
	{
		if (this._showFull) {
			this.ShowShort(true);
		}
		else {
			this.ShowFull();
		}
		this.Resize(this._width);
		if (this.InNewWindowMode) {
			WebMail.ResizeBody();
		}
		else if (WebMail.ScreenId == WebMail.ListScreenId) {
			var screen = WebMail.Screens[WebMail.ScreenId];
			if (screen) screen.ResizeScreen(RESIZE_MODE_MSG_HEIGHT);
		}
	},
	
	GetHeight: function ()
	{
		return this._container.offsetHeight;
	},
	
	_fillSubject: function (msg)
	{
		var subj = (msg.Subject == '')
			? '<span class="wm_no_subject">' + Lang.MessageNoSubject + '</span>'
			: msg.Subject;
		if (msg.Importance == PRIORITY_HIGH) {
			this._subjCont.innerHTML = '<span class="wm_importance_img"> </span>' + subj;
		}
		else {
			this._subjCont.innerHTML = subj;
		}
	},
	
	_fillCharset: function (msg)
	{
		var shown = this._charsetSelector.Fill(msg.HasCharset, msg.Charset, true);
		this._charsetLine.className = (shown) ? '' : 'wm_hide';
	},
	
	_fillFrom: function (msg, fromDisplay)
	{
		this._fromCont.innerHTML = '<font>' + Lang.From + ':</font>' + '<span class="wm_message_from">' + msg.FromAddr + '</span>';
		this._shortFromCont.innerHTML = '<span class="wm_message_from">' + fromDisplay + '</span>';
		if (WebMail.Settings.AllowContacts && msg.FromAddr.length > 0) {
			//email parts for adding to contacts
			var fromParts = GetEmailParts(HtmlDecode(msg.FromAddr));
			this._addToABObj.Show(fromParts.Name, fromParts.Email);
			this._shortAddToABObj.Show(fromParts.Name, fromParts.Email);
		}
		else {
			this._addToABObj.Hide();
			this._shortAddToABObj.Hide();
		}
	},
	
	_fillFromWithContact: function (msg, fromDisplay, contact)
	{
			ContactCard.SetContact(contact);

			this._shortFromCont.innerHTML = '';
			var a = CreateChild(this._shortFromCont, 'a', [['href', '#'], ['class', 'wm_message_from'],
				['style', 'text-decoration: underline;']]);
			a.onclick = ContactCard.Go;
			if (contact.Type == TYPE_CONTACT) {
				a.onmouseover = ContactCard.Show;
				a.onmouseout = ContactCard.Hide;
			}
			a.innerHTML = fromDisplay;
			this._shortAddToABObj.Hide();
			
			this._fromCont.innerHTML = '';
			var font = CreateChild(this._fromCont, 'font');
			font.innerHTML = Lang.From + ':';
			a = CreateChild(this._fromCont, 'a', [['href', '#'], ['class', 'wm_message_from'],
				['style', 'text-decoration: underline;']]);
			a.onclick = ContactCard.Go;
			if (contact.Type == TYPE_CONTACT) {
				a.onmouseover = ContactCard.Show;
				a.onmouseout = ContactCard.Hide;
			}
			a.innerHTML = msg.FromAddr;
			this._addToABObj.Hide();
	},
	
	_fillShortToAndDate: function (msg)
	{
		var strTo = msg.ToAddr;
		if (msg.CCAddr.length > 0) {
			strTo += ', ' + msg.CCAddr;
		}
		var title = strTo;
		var maxToLength = 65;
		var currPos, nextPos;
		if (strTo.length > maxToLength) {
			currPos = strTo.indexOf(',');
			nextPos = currPos;
			do {
				currPos = nextPos;
				nextPos = strTo.indexOf(',', currPos + 1);
			} while (nextPos <= maxToLength && nextPos != -1);
			if (nextPos > maxToLength || nextPos == -1) {
				strTo = strTo.substr(0, currPos) + '...';
			}
		}
		var innerHtml = '';
		if (msg.ToAddr) {
			innerHtml += Lang.MessageForAddr + ' <span class="wm_message_from" title="' + title + '">' + strTo + '</span>';
		}
		var time = (msg.Time && msg.Date != msg.Time) ? ', ' + msg.Time : '';
		if (msg.Date) {
			innerHtml += ' (' + msg.Date + time + ')';
		}
		this._shortDateCont.innerHTML = innerHtml;
	},
	
	_fillFullToAndDate: function (msg)
	{
		var toAddr = (msg.ToAddr) ? msg.ToAddr : '';
		if (toAddr == '') {
			this._toLineClassName = 'wm_hide';
		}
		else {
			this._toCont.innerHTML = '<font>' + Lang.To + ':</font>' + (toAddr);
			this._toCont.title = toAddr;
			this._toLineClassName = '';
		}
		this._dateCont.innerHTML = '<font>' + Lang.Date + ':</font>' + ((msg.FullDate) ? msg.FullDate : '');
	},
	
	_fillCopies: function (msg)
	{
		this._hasCopies = false;
		if (msg.CCAddr) {
			this._ccCont.innerHTML = '<font>' + Lang.CC + ':</font>' + msg.CCAddr;
			this._hasCopies = true;
		}
		if (msg.BCCAddr) {
			this._bccCont.innerHTML = '<font>' + Lang.BCC + ':</font>' + msg.BCCAddr;
			this._hasCopies = true;
		}
	},

	Fill: function (msg, contact)
	{
		this._readConfValue = msg.MailConfirmationValue;
		this._fillSubject(msg);
		this._fillCharset(msg);
		var fromDisplay = (msg.FromDisplayName.length > 0) ? msg.FromDisplayName : msg.FromAddr;
		if (contact == null || contact.Id == -1) {
			this._fillFrom(msg, fromDisplay);
		}
		else {
			this._fillFromWithContact(msg, fromDisplay, contact);
		}
		this._fillShortToAndDate(msg);
		this._fillFullToAndDate(msg);
		this._fillCopies(msg);
		this.ShowShort(true);
	},
	
	Clean: function ()
	{
		this._subjCont.innerHTML = '';
		this._showDetailsCont.className = 'wm_hide';
		
		this._shortFromCont.innerHTML = '';
		this._shortAddToABObj.Hide();
		this._shortDateCont.innerHTML = '';

		this._fromCont.innerHTML = '';
		this.SwitcherCont.className = 'wm_hide';
		this._addToABObj.Hide();
		this._toCont.innerHTML = '';
		this._dateCont.innerHTML = '';
		this._charsetSelector.Hide();
		this._ccCont.innerHTML = '';
		this._bccCont.innerHTML = '';
		this._copiesCont.className = 'wm_hide';
		
		this.ShowShort(false);
	},

	_getSpanWidth: function (span)
	{
		var width = span.clientWidth;
		width += (width == 0) ? 0 : this._spanMargin * 2;
		return width;
	},

	Resize: function (inboxWidth)
	{
		this._width = inboxWidth;
		if (this._showFull) {
			this.ResizeFull(inboxWidth);
		}
		else {
			this.ResizeShort(inboxWidth);
		}
	},

	ResizeShort: function (inboxWidth)
	{
		var addrBookWidth = this._shortAddToABCont.clientWidth;
		var showDetailsWidth = this._getSpanWidth(this._showDetailsCont);
		var maxFromDateWidth = inboxWidth - addrBookWidth - showDetailsWidth
			- this._spanMargin - this._contPadding * 2 - 1;
		maxFromDateWidth = Validator.CorrectNumber(maxFromDateWidth, 100);
		var halfFromDateWidth = Math.floor(maxFromDateWidth/2);

		this._shortDateCont.style.width = 'auto';
		this._shortFromCont.style.width = 'auto';
		var dateWidth = this._getSpanWidth(this._shortDateCont);
		var fromWidth = this._getSpanWidth(this._shortFromCont);

		if (fromWidth < halfFromDateWidth) {
			this._shortDateCont.style.width = (maxFromDateWidth - fromWidth - this._spanMargin * 2) + 'px';
		}
		else if (dateWidth < halfFromDateWidth) {
			this._shortFromCont.style.width = (maxFromDateWidth - dateWidth - this._spanMargin * 2) + 'px';
		}
		else {
			this._shortDateCont.style.width = (halfFromDateWidth - this._spanMargin * 2) + 'px';
			this._shortFromCont.style.width = (halfFromDateWidth - this._spanMargin * 2) + 'px';
		}
	},

	ResizeFull: function (inboxWidth)
	{
		var addrBookWidth = this._addToABCont.clientWidth;
		var hideDetailsWidth = this._getSpanWidth(this._hideDetailsCont);
		var maxFromWidth = inboxWidth - addrBookWidth - hideDetailsWidth - 5 - this._contPadding * 2;
		maxFromWidth = Validator.CorrectNumber(maxFromWidth, 100);
		this._fromCont.style.width = 'auto';
		var fromWidth = this._fromCont.clientWidth;
		if (fromWidth > maxFromWidth) {
			this._fromCont.style.width = maxFromWidth + 'px';
		}

		var switcherWidth = this._getSpanWidth(this.SwitcherCont);
		var maxDateWidth = inboxWidth - switcherWidth - 8 - this._contPadding * 2;
		maxDateWidth = Validator.CorrectNumber(maxDateWidth, 100);
		this._dateCont.style.width = 'auto';
		var dateWidth = this._dateCont.clientWidth;
		if (dateWidth > maxDateWidth) {
			this._dateCont.style.width = maxDateWidth + 'px';
		}

		if (this._hasCopies) {
			if (this._ccCont.innerHTML == '') {
				this._bccCont.style.width = inboxWidth - this._spanMargin * 2 - this._contPadding * 2 + 'px';
			}
			else if (this._bccCont.innerHTML == '') {
				this._ccCont.style.width = inboxWidth - this._spanMargin * 2 - this._contPadding * 2 + 'px';
			}
			else {
				var halfWidth = Math.ceil(inboxWidth / 2) - this._spanMargin * 2 - this._contPadding;
				this._ccCont.style.width = 'auto';
				this._bccCont.style.width = 'auto';
				var ccWidth = this._ccCont.clientWidth;
				var bccWidth = this._bccCont.clientWidth;
				if ((ccWidth + bccWidth) > halfWidth * 2) {
					if (ccWidth > halfWidth) {
						if (bccWidth > halfWidth) {
							this._ccCont.style.width = halfWidth + 'px';
							this._bccCont.style.width = halfWidth + 'px';
						} else {
							this._ccCont.style.width = halfWidth * 2 - bccWidth + 'px';
						}
					} else if (bccWidth > halfWidth) {
						this._bccCont.style.width = halfWidth * 2 - ccWidth + 'px';
					}
				}
			}
		}
	},
	
	_addClearDiv: function (cont)
	{
		var styles = 'width: 0; height: 0; padding: 0; overflow: hidden; clear: both;';
		CreateChild(cont, 'div', [['style', styles]]);
	},

	_addSwitchDetailsCont: function (cont, show)
	{
		var switchDetailsCont = CreateChild(cont, 'span');
		var a = CreateChild(switchDetailsCont, 'a', [['href', '#']]);
		var obj = this;
		a.onclick = function () {
			obj.SwitchDetails();
			return false;
		};
		if (show) {
			switchDetailsCont.className = 'wm_hide';
			a.innerHTML = Lang.MessageShowDetails;
			this._showDetailsCont = switchDetailsCont;
		}
		else {
			switchDetailsCont.className = 'wm_message_right';
			a.innerHTML = Lang.MessageHideDetails;
			this._hideDetailsCont = switchDetailsCont;
		}
	},

	Build: function (parent)
	{
		var cont = CreateChild(parent, 'div');
		cont.style.padding = this._contPadding + 'px';
		cont.style.paddingTop = '4px';
		cont.style.paddingBottom = '4px';
		cont.className = 'wm_message_headers';
		this._container = cont;

		var div = CreateChild(cont, 'div');
		this._subjCont = CreateChild(div, 'span', [
			['style', 'font-size: 14px; font-weight: bold; white-space: normal;'],
			['class', 'wm_message_left']]);
		this._addClearDiv(cont);

		div = CreateChild(cont, 'div');
		this._shortFromCont = CreateChild(div, 'span', [['style', 'margin-right: 0;'],
			['class', 'wm_message_left wm_message_resized']]);
		this._shortAddToABCont = CreateChild(div, 'span', [['style', 'margin-left: 0; margin-right: 0;'],
			['class', 'wm_message_left']]);
		this._shortAddToABObj = new CAddToAddressBookImg(this._shortAddToABCont);
		this._shortDateCont = CreateChild(div, 'span', [['class', 'wm_message_left wm_message_resized']]);
		this._addSwitchDetailsCont(div, true);
		this._shortLines.push(div);
		this._addClearDiv(cont);
		
		div = CreateChild(cont, 'div', [['class', 'wm_hide']]);
		this._fromCont = CreateChild(div, 'span', [['style', 'margin-right: 0;'],
			['class', 'wm_message_left wm_message_resized']]);
		this._addToABCont = CreateChild(div, 'span', [['style', 'margin-left: 0; margin-right: 0;'],
			['class', 'wm_message_left']]);
		this._addToABObj = new CAddToAddressBookImg(this._addToABCont);
		this._addSwitchDetailsCont(div, false);
		this._fullLines.push(div);
		this._addClearDiv(cont);

		this._toLine = CreateChild(cont, 'div', [['class', 'wm_hide']]);
		this._toCont = CreateChild(this._toLine, 'span', [['class', 'wm_message_left']]);
		this._addClearDiv(cont);

		div = CreateChild(cont, 'div', [['class', 'wm_hide']]);
		this._ccCont = CreateChild(div, 'span', [['class', 'wm_message_left wm_message_resized']]);
		this._bccCont = CreateChild(div, 'span', [['class', 'wm_message_left'],
			['style', 'overflow: hidden;']]);
		this._copiesCont = div;
		this._addClearDiv(cont);

		div = CreateChild(cont, 'div', [['class', 'wm_hide']]);
		this._dateCont = CreateChild(div, 'span', [['class', 'wm_message_left wm_message_resized']]);
		this.SwitcherCont = CreateChild(div, 'span', [['class', 'wm_message_right']]);
		var a = CreateChild(this.SwitcherCont, 'a', [['href', '#']]);
		a.innerHTML = Lang.SwitchToPlain;
		var obj = this;
		a.onclick = function () {
			if (obj.InNewWindowMode) {
				WebMail.SwitchToHtmlPlain();
			}
			else if (WebMail.ScreenId == WebMail.ListScreenId) {
				var screen = WebMail.Screens[WebMail.ScreenId];
				if (screen) screen.SwitchToHtmlPlain();
			}
			return false;
		};
		this.SwitcherObj = a;
		this._fullLines.push(div);
		this._addClearDiv(cont);

		// charset line
		this._charsetLine = CreateChild(cont, 'div', [['class', 'wm_hide']]);
		var charsetParent = CreateChild(this._charsetLine, 'span', [['class', 'wm_message_right']]);
		this._charsetSelector = new CMessageCharsetSelector(charsetParent);
		this._addClearDiv(cont);
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}
