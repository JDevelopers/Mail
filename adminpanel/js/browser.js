
function CBrowser() {
	this.Init = function() {
		var len = this.Profiles.length;
		for (var i = 0; i < len; i++) {
			if (this.Profiles[i].Criterion) {
				this.Name = this.Profiles[i].Id;
				this.Version = this.Profiles[i].Version();
				this.Allowed = this.Version >= this.Profiles[i].AtLeast;
				break;
			}
		};
		this.IE = (this.Name == "Microsoft Internet Explorer");
		this.Opera = (this.Name == "Opera");
		this.Mozilla = (this.Name == "Mozilla" || this.Name == "Firefox" || this.Name == "Netscape");
		this.Safari = (this.Name == 'Safari');
		this.Gecko = (this.Opera || this.Mozilla);
	};

	this.Profiles = [
		{
			Id: "Opera",
			Criterion: window.opera,
			AtLeast: 8,
			Version: function() {
				var start, end;
				var r = navigator.userAgent;
				var start1 = r.indexOf("Opera/");
				var start2 = r.indexOf("Opera ");
				if (-1 == start1) {
					start = start2 + 6;
					end = r.length;
				} else {
					start = start1 + 6;
					end = r.indexOf(" ");
				};
				r = parseFloat(r.slice(start, end));
				return r;
			}
		},
		{
			Id: "Safari",
			Criterion:
			(
				(navigator.appCodeName.toLowerCase() == "mozilla") &&
				(navigator.appName.toLowerCase() == "netscape") &&
				(navigator.product.toLowerCase() == "gecko") &&
				(navigator.userAgent.toLowerCase().indexOf("safari") != -1)
			),
			AtLeast: 1.2,
			Version: function() {
				var r = navigator.userAgent;
				return parseFloat(r.split("Version/").reverse().join(" "));
			}
		},
		{
			Id: "Firefox",
			Criterion:
			(
				(navigator.appCodeName.toLowerCase() == "mozilla") &&
				(navigator.appName.toLowerCase() == "netscape") &&
				(navigator.product.toLowerCase() == "gecko") &&
				(navigator.userAgent.toLowerCase().indexOf("firefox") != -1)
			),
			AtLeast: 1,
			Version: function() {
				var r = navigator.userAgent.split(" ").reverse().join(" ");
				r = parseFloat(r.slice(r.indexOf("/")+1,r.indexOf(" ")));
				return r;
			}
		},
		{
			Id: "Netscape",
			Criterion:
			(
				(navigator.appCodeName.toLowerCase() == "mozilla") &&
				(navigator.appName.toLowerCase() == "netscape") &&
				(navigator.product.toLowerCase() == "gecko") &&
				(navigator.userAgent.toLowerCase().indexOf("netscape") != -1)
			),
			AtLeast: 7,
			Version: function() {
				var r = navigator.userAgent.split(" ").reverse().join(" ");
				r = parseFloat(r.slice(r.indexOf("/")+1,r.indexOf(" ")));
				return r;
			}
		},
		{
			Id: "Mozilla",
			Criterion:
			(
				(navigator.appCodeName.toLowerCase() == "mozilla") &&
				(navigator.appName.toLowerCase() == "netscape") &&
				(navigator.product.toLowerCase() == "gecko") &&
				(navigator.userAgent.toLowerCase().indexOf("mozilla") != -1)
			),
			AtLeast: 1,
			Version: function() {
				var r = navigator.userAgent;
				return parseFloat(r.split("Firefox/").reverse().join(" "));
			}
		},
		{
			Id: "Microsoft Internet Explorer",
			Criterion:
			(
				(navigator.appName.toLowerCase() == "microsoft internet explorer") &&
				(navigator.appVersion.toLowerCase().indexOf("msie") != 0) &&
				(navigator.userAgent.toLowerCase().indexOf("msie") != 0) &&
				(!window.opera)
			),
			AtLeast: 5,
			Version: function() {
				var r = navigator.userAgent.toLowerCase();
				r = parseFloat(r.slice(r.indexOf("msie")+4,r.indexOf(";",r.indexOf("msie")+4)));
				return r;
			}
		}
	];

	this.Init();
}
