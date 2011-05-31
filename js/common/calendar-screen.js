/*
 * Classes:
 *  CCalendarScreen(skinName)
 */

function CCalendarScreen(skinName)
{
	this.Id = SCREEN_CALENDAR;
	this.isBuilded = false;
	this.hasCopyright = false;
	this.BodyAutoOverflow = false;
	this._skinName = skinName;
	this._defLang = 'English';
	this._needReload = false;
	this._wasShown = false;
	
	this._ifrCalendar = null;
}

CCalendarScreen.prototype = {
	PlaceData: function(Data) { },
	
	ClickBody: function(ev) { },

	ResizeBody: function()
	{
		if (this._ifrCalendar == null) return;
		this._ifrCalendar.style.width = GetWidth() + 'px';
		this._ifrCalendar.style.height = GetHeight() + 'px';
	},
	
	Create: function ()
	{
		this._ifrCalendar = CreateChild(document.body, 'iframe', [['src', CalendarUrl], ['frameborder', '0']]);
		with (this._ifrCalendar.style) {
			padding = '0';
			margin = '0';
			border = 'none';
			position = 'absolute';
			left = '0';
			top = '0';
			zIndex = '10';
		}
		var obj = this;
		this._ifrCalendar.onresize = function() {
			obj._ifrCalendar.style.width = (GetWidth() + 1) + 'px';
		};
		
		if (WebMail && (typeof(WebMail.IdleSessionTimeout) != 'undefined' && WebMail.IdleSessionTimeout > 0)) {
			obj.SetCalendarFrameHandlers(function(){WebMail.StartIdleTimer.call(WebMail)}, Array('click', 'keyup'));
		}
		
		this.Hide();
	},
	
	Reload: function ()
	{
		/* FireFox2 and IE6,7 can reload iframe without parameter
		 * Opera9 can't reload iframe without parameter
		 */
		this._ifrCalendar.src = CalendarUrl + '?p=' + Math.random();
		this._needReload = false;
		this._wasShown = false;
	},
	
	Show: function ()
	{
		this.ParseSettings();
		if (this._ifrCalendar == null) {
			this.Create();
		}
		else if (this._needReload) {
			this.Reload();
		}
		this.ResizeBody();
		if (this._wasShown) {
			this.Display();
		}
		else {
			WebMail.ShowInfo(Lang.Loading);
		}
	},
	
	Display: function ()
	{
		this._ifrCalendar.className = '';
		this._wasShown = true;
		WebMail.HideInfo();
	},
	
	RestoreFromHistory: function () { },
	
	ParseSettings: function ()
	{
		if (this._skinName != WebMail.Settings.DefSkin || this._defLang != WebMail.Settings.DefLang) {
			this._skinName = WebMail.Settings.DefSkin;
			this._defLang = WebMail.Settings.DefLang;
			this._needReload = true;
		}
	},
	
	NeedReload: function ()
	{
		this._needReload = true;
	},

	Hide: function()
	{
		if (this._ifrCalendar == null) return;
		this._ifrCalendar.className = (Browser.Mozilla)
			? 'wm_unvisible'	// IE7 make iframe unvisible dirty
			: 'wm_hide';		// FireFox2 reload iframe if set "display: none;"
	},
	
	Build: function()
	{
		this.ParseSettings();
		this._needReload = false;
		this.isBuilded = true;
	},

	SetCalendarFrameHandlers : function (eventFunction, eventsList)
	{
		var obj = this;
		var doc = obj._ifrCalendar.contentWindow;
		if (Browser.IE) {
			doc = obj._ifrCalendar.document;
		}
		
		for (var i in eventsList) {
			$addHandler(doc, eventsList[i],  eventFunction);
		}
	}
};

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}