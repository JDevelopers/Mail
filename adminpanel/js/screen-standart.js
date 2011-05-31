/* tabs-controller.js */
function CreateTabsOnclickFunction(id) {
	return function () { TabsController.ShowItem(id); };
};

var TabsController = {
	_items: new Array(),
	_currId: '',
	
	Init: function (mode) {
		var cont = document.getElementById('settings_nav');
		var tabs = cont.getElementsByTagName('DIV');
		for (var i=0; i < tabs.length; i++) {
			var idParts = tabs[i].id.split('_');
			if (idParts.length < 2) continue;
			idParts.pop();
			var id = idParts.join('_');
			this._items[id] = new CTab(id, tabs[i]);
			tabs[i].onclick = CreateTabsOnclickFunction(id);
		};
		if (mode && this._items[mode]) {
			this._items[mode].Show();
		};
		ResizeElements('all');
	},
	
	ShowItem: function (id) {
		Tip.Hide();
		if (this._currId == id) return;
		for (var i in this._items) {
			var item = this._items[i];
			if (item.Id == id) {
				item.Show();
				this._currId = item.Id;
			} else {
				item.Hide();
			};
		};
		ResizeElements('all');
	}
};

function CTab(_id, _tab) {
	this.Id = _id;
	var _contentForm = document.getElementById(_id + '_form');
	var _initialized = false;

	this._init = function () {
		var hrefs = _tab.getElementsByTagName('A');
		if (hrefs.length > 0) {
			hrefs[0].onclick = function () { return false; };
		};
	};
	
	this.Show = function () {
		AjaxSwitchMode(this.Id);
		if (_contentForm) {
			_contentForm.className = '';
		};
		if (_tab.className != 'wm_hide') {
			_tab.className = 'wm_selected_settings_item';
		};
		if (!_initialized) {
			if (window.SettingsObjects && SettingsObjects[_id]) {
				SettingsObjects[_id].Init();
			};
			_initialized = true;
		};
	};
	
	this.Hide = function () {
		if (_contentForm) {
			_contentForm.className = 'wm_hide';
		};
		if (_tab.className != 'wm_hide') {
			_tab.className = 'wm_settings_item';
		};
	};
	this._init();
	this.Hide();
}

function ResizeElements(mode) {
	ResizeMainError();
	return; // off
	var h = GetHeight();
	var logo = document.getElementById('logo');
	if (logo) {
		var logoBounds = GetBounds(logo);
		if (logoBounds && logoBounds.Height) {
			h -= logoBounds.Height;
		}
	}

	var toolbar = document.getElementById('accountslist');
	if (toolbar) {
		var toolbarBounds = GetBounds(toolbar);
		if (toolbarBounds && toolbarBounds.Height) {
			h -= toolbarBounds.Height;
		}
	}

	var main = document.getElementById('settings_main');
	if (main){
		h = (h < 300) ? 300 : h;
		main.style.height = h + 'px';
	}
}