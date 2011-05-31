/*
 * Functions:
 *  fnum(num, digits)
 *  getSettingsParametr()
 *  String.prototype.parseJSON(filter)
 * Classes:
 *  CCalendarSettingsScreenPart(parentScreen)
 */

var setcache = null;

function fnum(num, digits)
{
	num = String(num);
	while (num.length < digits) {
		num = '0' + num;
	}
	return(num);
}

function getSettingsParametr()
{
	var i, setval;
	WebMail.ShowInfo(Lang.InfoLoading);
	var scache = new Array();
	var res = '{}';
	var netLoader = new CNetLoader();
	var req = netLoader.GetTransport();
	var url = CalendarProcessingUrl + '?action=get_settings&nocache=' + Math.random();
	if (req != null) {
		req.open('GET', url, false);
		req.send(null);
		res = req.responseText;
	}
	WebMail.HideInfo();
	var setparams;
	setparams = res.parseJSON();
	if (setparams == false) return null;
	for (i in setparams) {
		setval = setparams[i]; 
		if (typeof(setval) == 'function') continue;
		scache[i] = setval;
	}
	return scache;
}

/*
 * Based on json.js (2007-07-03)
 * Modified by AfterLogic Corporation
 */
String.prototype.parseJSON = function (filter) {
    var j;

    function walk(k, v) {
        var i;
        if (v && typeof v === 'object') {
            for (i in v) {
                if (v.hasOwnProperty(i)) {
                    v[i] = walk(i, v[i]);
                }
            }
        }
        return filter(k, v);
    }

    if (/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]+$/.test(this.
            replace(/\\./g, '@').
            replace(/"[^"\\\n\r]*"/g, ''))) {

        j = eval('(' + this + ')');
        if (typeof filter === 'function') {
            j = walk('', j);
        }
    	if (j['error'] == 'true') {
    		WebMail.ShowError(j['description']);
    		return false;
    	}
        return j;
    }

    WebMail.ShowError(Lang.ErrorGeneral);
    return false;
};


function CCalendarSettingsScreenPart(parentScreen)
{
	this._parentScreen = parentScreen;
	
	this._mainForm = null;
	this._buttonsTbl = null;
	
	this.hasChanges = false;
	
	this._timeFormat = Array();
	this._defTimeFormatCont	= null;
	this._showWeekends = null;
	this._showWeekendsCont = null;
	this._WorkdayStarts = null;
	this._WorkdayEnds = null;
	this._WorkdayCont = null;
	this._ShowWorkday = null;
	this._ShowWorkdayCont = null;
	this._tabCont = null;
	this._tab = Array();	
	this._Country = null;
	this._CountryCont = null;
	this._UserTimeZone = null;
	this._UserTimeZoneCont = null;
	this._AllTimeZones = null;
	this._AllTimeZonesCont = null;
	this._autoAddInvitation = null;
	this._autoAddInvitationCont = null;
	this._CalncelBtn = null;
	this._SaveBtn = null;
	this.defTimeZone = null;
	this.settingsTimeZone = null;
	this._displayName = null;
	this._displayNameCont = null;
	this._weekStartsOn = null;
	this._weekStartsOnCont = null;
	this._weekStartsOnBuilded = false;

	this._tabs = [
		{ NameField: 'TabDay', Value: '1',  Id: 'set_tab_0'},
		{ NameField: 'TabWeek', Value: '2', Id: 'set_tab_1'},
		{ NameField: 'TabMonth', Value: '3', Id: 'set_tab_2'}
		];
	
	var d = new Date();
	
	var MonField = 'ShortMonthJanuary'; //month
	switch (d.getMonth()+1) {
		case 1: MonField = 'ShortMonthJanuary'; break;
		case 2: MonField = 'ShortMonthFebruary'; break;
		case 3: MonField = 'ShortMonthMarch'; break;
		case 4: MonField = 'ShortMonthApril'; break;
		case 5: MonField = 'ShortMonthMay'; break;
		case 6: MonField = 'ShortMonthJune'; break;
		case 7: MonField = 'ShortMonthJuly'; break;
		case 8: MonField = 'ShortMonthAugust'; break;
		case 9: MonField = 'ShortMonthSeptember'; break;
		case 10: MonField = 'ShortMonthOctober'; break;
		case 11: MonField = 'ShortMonthNovember'; break;
		case 12: MonField = 'ShortMonthDecember'; break;
	}
	
	this._dayFormat = [
		{Name: fnum((d.getMonth()+1),2)+"/"+fnum(d.getDate(),2)+"/"+d.getFullYear(), Value: '1', Id: 'date_0'},
		{Name: fnum(d.getDate(),2)+"/"+fnum((d.getMonth()+1),2)+"/"+d.getFullYear(), Value: '2', Id: 'date_1'},
		{Name: d.getFullYear()+"-"+fnum((d.getMonth()+1),2)+"-"+fnum(d.getDate(),2), Value: '3', Id: 'date_2'},
		{Name: null, NameField: MonField, NameBefore: '', NameAfter: ' ' + d.getDate() + ', ' + d.getFullYear(), Value: '4', Id: 'date_3'},
		{Name: null, NameField: MonField, NameBefore: d.getDate() + ' ', NameAfter: ' ' + d.getFullYear(), Value: '5', Id: 'date_4'}
		];
	
	this._firstWeekDay = [
		{NameField: 'FullDaySunday', Value: '0', Id: 'first_week_day_0'},
		{NameField: 'FullDayMonday', Value: '1', Id: 'first_week_day_1'}
		];
}

CCalendarSettingsScreenPart.prototype =
{
	Show: function ()
	{
		this._mainForm.className = '';
		this._buttonsTbl.className = 'wm_settings_buttons';
		if (setcache == null) setcache = getSettingsParametr();
		this.Fill();
	},
	
	Hide: function ()
	{
		if (this.hasChanges) {
			if (confirm(Lang.ConfirmSaveSettings)) {
				this.SaveChanges();
			} else {
				this.Fill();
			}
		}
		this._mainForm.className = 'wm_hide';
		this._buttonsTbl.className = 'wm_hide';
		this.hasChanges = false;
	},
	
	SetTimeFormat: function(WorkdayContainer, WorkdayValue, TimeFormat)
	{
		for (var i=WorkdayContainer.options.length-1; i>=0; i--) {
			WorkdayContainer.options[i] = null;
		}
		for (i=0; i<24; i++) {
			opt = document.createElement("option");
			opt.value = i;
			WorkdayContainer.appendChild(opt);
			if (TimeFormat == 1) {
			    if (i < 12) {
			        opt.text = ((i == 0) ? 12 : i) + " AM";
			    }
			    else {
			        opt.text = ((i == 12) ? i : (i - 12)) + " PM";
			    }
			} else { 			
				opt.text = (i < 10) ? ("0" + i + ":00") : (i + ":00");
			}
		}
		setTimeout( function(){WorkdayContainer.options[WorkdayValue].selected=true;}, 1);
	},
	
	LoadTimeZones: function(TimeZoneCont, allTimeZones)
	{
		CleanNode(TimeZoneCont);
        var _defTimeZoneVal = "<select id='defTimeZone' style='width: 300px;' name='defTimeZone'>";
        var i = "", index;
		
        var code = this._Country.value;
        if(allTimeZones==1)
		{
            if(this.defTimeZone != null)
			{
				this.settingsTimeZone = this.defTimeZone.options[this.defTimeZone.selectedIndex].value;
			}
			/* var tmp_zone = "";
			for(i in timeZoneForCountry[code])
			{
				if (timeZoneForCountry[code][i] == this.settingsTimeZone)
				{
					tmp_zone = this.settingsTimeZone;
					break;
				}
				else
				{
					tmp_zone = timeZoneForCountry[code][0];
				}
			}
			this.settingsTimeZone = tmp_zone;*/
		    TimeZoneCont.innerHTML = allTimeZone;
            this.defTimeZone = document.getElementById("defTimeZone");
            this.defTimeZone.selectedIndex = parseInt(this.settingsTimeZone, 10) - 1;
        }
        else
		{
            for(i in timeZoneForCountry[code])
			{
                index = timeZoneForCountry[code][i];
                var timeZoneValue = AllTimeZonesArr[index];
				if (typeof(index)=="function") continue;

				if (this.settingsTimeZone == index)
				{
                    _defTimeZoneVal += "<option value='" + index + "' selected='selected'>" + timeZoneValue + "</option>\r\n";
                }
                else
				{
                    _defTimeZoneVal += "<option value='" + index + "'>" + timeZoneValue + "</option>\r\n";
                }
            }
            _defTimeZoneVal += "</select>";
			TimeZoneCont.innerHTML = _defTimeZoneVal;
            this.defTimeZone = document.getElementById("defTimeZone");
			this.settingsTimeZone = this.defTimeZone.options[this.defTimeZone.selectedIndex].value;
        }
	},

	Fill: function ()
	{
		if (setcache == null) return;
		this.hasChanges = false;

		var i, opt;
		
		if (setcache['timeformat'] != null)
		{
			for (i in this._timeFormat)
			{
				var tfObj = this._timeFormat[i];
				if (setcache['timeformat'] == tfObj.Value)
				{
					tfObj.Obj.checked = true;
				}
			}
			this._defTimeFormatCont.className = "wm_hide";
		}
		else this._defTimeFormatCont.className = "wm_hide";

		if (setcache['showweekends'] != null)
		{
			if (setcache['showweekends'] == 1) this._showWeekends.checked = true;
			this._showWeekendsCont.className = '';
		}
		else this._showWeekendsCont.className = 'wm_hide';
		
		if (setcache['workdaystarts'] != null)
		{
			this.SetTimeFormat(this._WorkdayStarts, setcache['workdaystarts'], setcache['timeformat']);
			if (setcache['workdayends'] != null) this.SetTimeFormat(this._WorkdayEnds, setcache['workdayends'], setcache['timeformat']);
			this._WorkdayCont.className = '';
		}
		else this._WorkdayCont.className = 'wm_hide';
			
		if (setcache['showworkday'] != null)
		{
			if (setcache['showworkday'] == 1) this._ShowWorkday.checked = true;
			this._ShowWorkdayCont.className = '';
		}
		else this._ShowWorkdayCont.className = 'wm_hide';

		if (setcache['weekstartson'] != null)
		{
			var sel = this._weekStartsOn;
			if (!this._weekStartsOnBuilded)
			{
				for(i=0; i<this._firstWeekDay.length; i++)
				{
					opt = CreateChild(sel, 'option', [['value', this._firstWeekDay[i].Value]]);
					opt.innerHTML = Lang[this._firstWeekDay[i].NameField];
					WebMail.LangChanger.Register('innerHTML', opt, this._firstWeekDay[i].NameField, '', ' ');
				}
				this._weekStartsOnBuilded = true;
			}
			this._weekStartsOn.value = setcache['weekstartson'];
			this._weekStartsOnCont.className = '';				
		}
		else this._weekStartsOnCont.className = 'wm_hide';

		if (setcache['defaulttab'] != null)
		{
			for (i=0; i<this._tab.length; i++)
			{
				var tabObj = this._tab[i];
				if (setcache['defaulttab'] == tabObj.Value)
				{
					tabObj.Obj.checked = true;
				}
			}
			this._tabCont.className = "";
		}
		else this._tabCont.className = "wm_hide";

		if (setcache['country'] != null)
		{
			CleanNode(this._Country);
			for (i=0; i<Countries.length; i++)
			{
				opt = CreateChild(this._Country, 'option', [['value', Countries[i].Value]]);
				if (Countries[i].Value == setcache['country'])
				{
					opt.selected = true;
				}
				opt.innerHTML = Countries[i].Name;
			}
			this._CountryCont.className = '';
		}
		else this._CountryCont.className = 'wm_hide';
		
		if (setcache['timezone'] != null && setcache['alltimezones'] != null)
		{
			this.settingsTimeZone = setcache['timezone'];
			CleanNode(this._UserTimeZoneTd);
			this.LoadTimeZones(this._UserTimeZoneTd, setcache['alltimezones'], setcache['timezone']);
			this._UserTimeZoneCont.className = '';
		}
		else this._UserTimeZoneCont.className = 'wm_hide';
		
		if (setcache['alltimezones'] != null)
		{
			if (setcache['alltimezones'] == 1) this._AllTimeZones.checked = true;
			this._AllTimeZonesCont.className = '';
		}
		else this._AllTimeZonesCont.className = 'wm_hide';

		if (setcache['autoaddinvitation'] != null)
		{
			if (setcache['autoaddinvitation'] == 1) this._autoAddInvitation.checked = true;
			this._autoAddInvitationCont.className = '';
		}
		else this._autoAddInvitationCont.className = 'wm_hide';
		
		if (setcache['displayname'] != null)
		{
			this._displayName.value = setcache['displayname'];
			this._displayNameCont.className = '';
		}
		else this._displayNameCont.className = 'wm_hide';

		if (this._parentScreen)
		{
			this._parentScreen.ResizeBody();
		}
	},//Fill
	
	SetInputKeyPress: function (inp)
	{
	    var obj = this;
		inp.onkeypress = function (ev) { if (isEnter(ev)) obj.SaveChanges(); };
	},
	
	SaveChanges: function()
	{
		var i, setval;
		var _timeFormat = 1;
		for (i=0; i<this._timeFormat.length; i++) {
			var tfObj = this._timeFormat[i];
			if (tfObj.Obj.checked) {
				_timeFormat = tfObj.Value;
			}
		}
		
		var _showWeekends = (this._showWeekends.checked == true) ? 1 : 0;
		
		var _ShowWorkday = (this._ShowWorkday.checked == true) ? 1 : 0;
		
		var _AllTimeZones = (this._AllTimeZones.checked == true) ? 1 : 0;

		var _autoAddInvitation = (this._autoAddInvitation.checked == true) ? 1 : 0;
		
		var _defTab = 1;
		for (i=0; i < this._tab.length; i++) {
			if (this._tab[i].Obj.checked) {
				_defTab = this._tab[i].Value;
			}
		}
			
		var _defTimeZone = 0;
		if (this.defTimeZone != null) {
			_defTimeZone = this.defTimeZone.value;
		}
		var _displayName = encodeURIComponent(Trim(this._displayName.value));

		var netLoader = new CNetLoader();
		var req = netLoader.GetTransport();
		var url = CalendarProcessingUrl +
				'?action=update_settings' +
				'&timeFormat=' + _timeFormat +
				'&dateFormat=' + setcache['dateformat'] +
				'&showWeekends=' + _showWeekends +
				'&workdayStarts=' + this._WorkdayStarts.value +
				'&WorkdayEnds=' + this._WorkdayEnds.value +
				'&showWorkday=' + _ShowWorkday +
				'&weekstartson=' + this._weekStartsOn.value +
				'&tab=' + _defTab +
				'&country=' + this._Country.value +
				'&TimeZone=' + _defTimeZone +
				'&AllTimeZones=' + _AllTimeZones +
				'&autoAddInvitation=' + _autoAddInvitation +
				'&displayName=' + _displayName;
				'&nocache=' + Math.random();
		var res = '{}';
		if (req != null) {
			WebMail.ShowInfo(Lang.InfoSaving);
			req.open("GET", url, false);
			req.send(null);
			res = req.responseText;
		}
		
		var settingsFromDb;
		settingsFromDb = res.parseJSON();
		if (settingsFromDb == false) {
			WebMail.HideInfo();
			return;
		}
		for (i in settingsFromDb) { 
			setval = settingsFromDb[i]; 
			if (typeof(setval) == 'function') continue;
			setcache[i] = settingsFromDb[i];
		}
		WebMail.HideInfo();
		WebMail.ShowReport(Lang.ReportSettingsUpdated);
		if (this.hasChanges) {
			var screen = WebMail.Screens[SCREEN_CALENDAR];
			if (screen) screen.NeedReload();
			this.hasChanges = false;
		}
	},
	
	Build: function(container)
	{
		var inp, lbl;
		var obj = this;
		this._mainForm = CreateChild(container, 'form');
		this._mainForm.className = 'wm_hide';
		this._mainForm.onsubmit = function () { return false; };
		var tbl_ = CreateChild(this._mainForm, 'table');
		tbl_.className = 'wm_settings_common';

		var rowIndex = 0;
		var tr_ = tbl_.insertRow(rowIndex++);
		tr_.className = 'wm_hide';
		var td_ = tr_.insertCell(0);
		td_.className = 'wm_settings_title';
		td_.innerHTML = Lang.SettingsDisplayName + ':';
		WebMail.LangChanger.Register('innerHTML', td_, 'SettingsDisplayName', ':');
		td_ = tr_.insertCell(1);
		inp = CreateChild(td_, 'input', [['type', 'text'], ['name', 'DisplayName'], ['id', 'DisplayName'], ['value', ''], ['maxlength', '255']]);
		this.SetInputKeyPress(inp);
		inp.onchange = function () { obj.hasChanges = true; };
		this._displayName = inp;
		this._displayNameCont = tr_;
		this._displayName.onblur = function() { obj._displayName.value = Trim(obj._displayName.value); };

		tr_ = tbl_.insertRow(rowIndex++);
		tr_.className = 'wm_hide';
		td_ = tr_.insertCell(0);
		td_.className = 'wm_settings_title';
		td_.innerHTML = Lang.SettingsTimeFormat + ':';
		WebMail.LangChanger.Register('innerHTML', td_, 'SettingsTimeFormat', ':');
		td_ = tr_.insertCell(1);
		this._timeFormat = Array();
		inp = CreateChild(td_, 'input', [['class', 'wm_checkbox'], ['type', 'radio'], ['name', 'defTimeFormat'], ['id', '_defTimeFormat_0'], ['value', '1']]);
		inp.onchange = function () { obj.hasChanges = true; };
		inp.onclick = function() {
			obj.SetTimeFormat(obj._WorkdayStarts, obj._WorkdayStarts.value, 1);
		 	obj.SetTimeFormat(obj._WorkdayEnds, obj._WorkdayEnds.value, 1);	
		};
		lbl = CreateChild(td_, 'label', [['for', '_defTimeFormat_0']]);
		lbl.innerHTML = '1PM&nbsp;&nbsp;&nbsp;';
		this._timeFormat.push({Obj: inp, Value:1});
		inp = CreateChild(td_, 'input', [['class', 'wm_checkbox'], ['type', 'radio'], ['name', 'defTimeFormat'], ['id', '_defTimeFormat_1'], ['value', '2']]);
		inp.onchange = function () { obj.hasChanges = true; };
		inp.onclick = function() {
			obj.SetTimeFormat(obj._WorkdayStarts, obj._WorkdayStarts.value, 2);
		 	obj.SetTimeFormat(obj._WorkdayEnds, obj._WorkdayEnds.value, 2);	
		};
		lbl = CreateChild(td_, 'label', [['for', '_defTimeFormat_1']]);
		lbl.innerHTML = '13:00';
		this._timeFormat.push({Obj: inp, Value:2});
		this._defTimeFormatCont = tr_;
		
		tr_ = tbl_.insertRow(rowIndex++);
		tr_.className = 'wm_hide';
		td_ = tr_.insertCell(0);
		td_ = tr_.insertCell(1);
		inp = CreateChild(td_, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['name', 'showWeekends'], ['id', 'showWeekends'], ['value', '1']]);
		this._showWeekends = inp;
		this._showWeekends.onchange = function () { obj.hasChanges = true; };
		lbl = CreateChild(td_, 'label', [['for', 'showWeekends']]);
		lbl.innerHTML = Lang.SettingsShowWeekends;
		WebMail.LangChanger.Register('innerHTML', lbl, 'SettingsShowWeekends', '');
		this._showWeekendsCont = tr_;

		tr_ = tbl_.insertRow(rowIndex++);
		tr_.className = 'wm_hide';
		td_ = tr_.insertCell(0);
		td_.className = 'wm_settings_title';
		td_.innerHTML = Lang.SettingsWorkdayStarts + ':';
		WebMail.LangChanger.Register('innerHTML', td_, 'SettingsWorkdayStarts', ':');
		td_ = tr_.insertCell(1);
		sel1 = CreateChild(td_, 'select');
		sel1.style.width = "100px";
		this._WorkdayStarts = sel1;
		this._WorkdayStarts.onchange = function () { obj.hasChanges = true; };
		var span = CreateChild(td_, 'span');
		span.innerHTML = '&nbsp;&nbsp;' + Lang.SettingsWorkdayEnds + ': ';
		WebMail.LangChanger.Register('innerHTML', span, 'SettingsWorkdayEnds', ': ', '&nbsp;&nbsp;');
		sel2 = CreateChild(td_, 'select');
		sel2.style.width = "100px";
		this._WorkdayEnds = sel2;
		this._WorkdayEnds.onchange = function () { obj.hasChanges = true; };
		this._WorkdayCont = tr_;
		
		tr_ = tbl_.insertRow(rowIndex++);
		tr_.className = 'wm_hide';
		td_ = tr_.insertCell(0);
		td_ = tr_.insertCell(1);
		inp = CreateChild(td_, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['name', 'showWorkday'], ['id', 'showWorkday'], ['value', '1']]);
		lbl = CreateChild(td_, 'label', [['for', 'showWorkday']]);
		lbl.innerHTML = Lang.SettingsShowWorkday;
		WebMail.LangChanger.Register('innerHTML', lbl, 'SettingsShowWorkday', '');
		inp.onchange = function () { obj.hasChanges = true; };
		this._ShowWorkday = inp;
		this._ShowWorkdayCont = tr_;
		
		tr_ = tbl_.insertRow(rowIndex++);
		tr_.className = 'wm_hide';
		td_ = tr_.insertCell(0);
		td_.className = 'wm_settings_title';
		td_.innerHTML = Lang.SettingsWeekStartsOn + ':';
		WebMail.LangChanger.Register('innerHTML', td_, 'SettingsWeekStartsOn', ':');
		td_ = tr_.insertCell(1);
		sel = CreateChild(td_, 'select');
		this._weekStartsOn = sel;
		this._weekStartsOn.onchange = function () { obj.hasChanges = true; };
		this._weekStartsOnCont = tr_;
		
		tr_ = tbl_.insertRow(rowIndex++);
		tr_.className = 'wm_hide';
		td_ = tr_.insertCell(0);
		td_.className = 'wm_settings_title';
		td_.innerHTML = Lang.SettingsDefaultTab + ':';
		WebMail.LangChanger.Register('innerHTML', td_, 'SettingsDefaultTab', ':');
		td_ = tr_.insertCell(1);
		this._tab = Array();
		/* rtl */
		for (var i=0; i<this._tabs.length; i++) {
			inp = CreateChild(td_, 'input', [['class', 'wm_checkbox'], ['type', 'radio'], ['name', 'defTab'], ['id', this._tabs[i].Id], ['value', this._tabs[i].Value]]);
			inp.onchange = function () { obj.hasChanges = true; };
			lbl = CreateChild(td_, 'label', [['for', this._tabs[i].Id]]);
			lbl.innerHTML = Lang[this._tabs[i].NameField];
			WebMail.LangChanger.Register('innerHTML', lbl, this._tabs[i].NameField, '', '');
			this._tab.push({Obj: inp, Value: this._tabs[i].Value});
		}
		this._tabCont = tr_;

		tr_ = tbl_.insertRow(rowIndex++);
		tr_.className = 'wm_hide';
		td_ = tr_.insertCell(0);
		td_.className = 'wm_settings_title';
		td_.innerHTML = Lang.SettingsCountry + ':';
		WebMail.LangChanger.Register('innerHTML', td_, 'SettingsCountry', ':');
		td_ = tr_.insertCell(1);
		sel = CreateChild(td_, 'select');
		sel.style.width = "300px";
		this._Country = sel;
		this._Country.onchange = function () {
			obj.hasChanges = true; 
			/*reload timezones when change country*/	
			var allZones = (obj._AllTimeZones.checked)?1:0;
			obj.LoadTimeZones(obj._UserTimeZoneTd, allZones);
		};
		this._CountryCont = tr_;
		
		tr_ = tbl_.insertRow(rowIndex++);
		tr_.className = 'wm_hide';
		td_ = tr_.insertCell(0);
		td_.className = 'wm_settings_title';
		td_.innerHTML = Lang.SettingsTimeZone + ':';
		WebMail.LangChanger.Register('innerHTML', td_, 'SettingsTimeZone', ':');
		td_ = tr_.insertCell(1);
		this._UserTimeZoneTd = td_;
		this._UserTimeZoneCont = tr_;
		
		tr_ = tbl_.insertRow(rowIndex++);
		tr_.className = 'wm_hide';
		td_ = tr_.insertCell(0);
		td_ = tr_.insertCell(1);
		inp = CreateChild(td_, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['name', '_AllTimeZones'], ['id', 'AllTimeZones'], ['value', '0']]);
		lbl = CreateChild(td_, 'label', [['for', 'AllTimeZones']]);
		lbl.innerHTML = Lang.SettingsAllTimeZones;
		WebMail.LangChanger.Register('innerHTML', lbl, 'SettingsAllTimeZones', '');
		inp.onchange = function () { obj.hasChanges = true; };
		inp.onclick = function() {
			var allZones = (this.checked)?1:0;
			obj.LoadTimeZones(obj._UserTimeZoneTd, allZones);
		};
		this._AllTimeZones = inp;
		this._AllTimeZonesCont = tr_;

		tr_ = tbl_.insertRow(rowIndex++);
		tr_.className = 'wm_hide';
		td_ = tr_.insertCell(0);
		td_ = tr_.insertCell(1);
		inp = CreateChild(td_, 'input', [['class', 'wm_checkbox'], ['type', 'checkbox'], ['name', 'autoAddInvitation'], ['id', 'autoAddInvitation'], ['value', '0']]);
		lbl = CreateChild(td_, 'label', [['for', 'autoAddInvitation']]);
		lbl.innerHTML = Lang.SettingsAutoAddInvitation;
		WebMail.LangChanger.Register('innerHTML', lbl, 'SettingsAutoAddInvitation', '');
		inp.onchange = function () { obj.hasChanges = true; };
		this._autoAddInvitation = inp;
		this._autoAddInvitationCont = tr_;
	
		tbl_ = CreateChild(this._mainForm, 'table');
		tbl_.className = 'wm_hide';
		tr_ = tbl_.insertRow(0);
		td_ = tr_.insertCell(0);
		
		inp = CreateChild(td_, 'input', [['class', 'wm_button'], ['type', 'button'], ['value', Lang.ButtonSave]]);
		WebMail.LangChanger.Register('value', inp, 'ButtonSave', '');
		inp.onclick = function () {
			if (parseInt(obj._WorkdayStarts.value) >= parseInt(obj._WorkdayEnds.value)) {
				alert(Lang.WarningWorkdayStartsEnds);
			} else { 
				obj.SaveChanges(); 
			}
		};
		this._SaveBtn = inp;

		this._buttonsTbl = tbl_;
	}//Build
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}