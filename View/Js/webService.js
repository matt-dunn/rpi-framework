/*!
 * RPI.webService
 * version: 1.0.2
 * Copyright © 2011 RPI Limited. All rights reserved.
 */

/**
 * @type RPI.webService
 * @author Matt Dunn
 *
 * 1.0.2: added compatibility for JSON-RPC (http://json-rpc.org/wiki/specification)
 */

RPI._("webService").request = function(methodName, params) {
	this.request = {
		timestamp: new Date().getTime(),
		method: {name: methodName, format: "json", params: params}
	};
};

RPI.webService.responseStatus = {success: "success", error: "error", aborted: "aborted"};

RPI.webService.call = function(service, methodName, params, callback, errorCallback, mode, sourceUrl, sourceData) {
	var request = new RPI.webService.request(methodName, params);

	if(!mode) {
		mode = "abort";
	}

	if(RPI.loader) {
		RPI.loader.show();
	}

	return jQuery.ajax({
		type: "POST",
		async: true,
		mode: mode,	// Mode is only used if jQuery.ajaxQueue plugin is available
		port: (service + methodName).replace(/\//g, "_"),
		url: service,
		data: jQuery.json.encode(request),
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		sourceUrl: sourceUrl,
        headers: {"Document-Location": document.location.href},
		success: function(o) {
			if(RPI.loader) {
				RPI.loader.hide();
			}

			if(o) {
				if(o.status == RPI.webService.responseStatus.success && callback) {
					callback(o.result, o, sourceData);
				} else if(o.status == RPI.webService.responseStatus.error) {
					if(errorCallback) {
						errorCallback(o, o.status, o.error, (o.error.type == "RPI\\Framework\\WebService\\Exceptions\\Authentication") || o.error.type == "RPI\\Framework\\WebService\\Exceptions\\Forbidden", sourceData);
					}
					if(o.error.type == "RPI\\Framework\\WebService\\Exceptions\\Authentication") {
						alert(o.error.message);
						document.location.href = "/account/login/?from=" + (this.sourceUrl ? this.sourceUrl.URLEncode() : document.location.href.URLEncode());
					} else if(o.error.type == "RPI\\Framework\\WebService\\Exceptions\\Forbidden") {
						alert(o.error.message);
					}
				}
			} else {
				// Aborted
				errorCallback(null, RPI.webService.responseStatus.aborted, null, null, sourceData);
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			if(RPI.loader) {
				RPI.loader.hide();
			}

			if(errorCallback) {
				errorCallback(XMLHttpRequest, textStatus, errorThrown, null, sourceData);
			}

			if(XMLHttpRequest) {
				if(XMLHttpRequest.status == 401) {
					alert("Please press 'OK' to log-in.");
					document.location.href = "/account/login/?from=" + (this.sourceUrl ? this.sourceUrl.URLEncode() : document.location.href.URLEncode());
				} else if(XMLHttpRequest.status == 403) {
					alert("You do not have permission to perform this action");
				}
			}
		}
	});
};
