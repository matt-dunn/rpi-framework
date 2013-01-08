/*!
 * RPI.loader
 * version: 1.0.0
 * Copyright © 2010 RPI Limited. All rights reserved.
 */

/**
 * @type RPI.loader
 * @author Matt Dunn
 */

RPI.loader = (function() {
	var _container;
	var _timerId;
	var _count = 0;

	function showLoading() {
		if(!_container) {
			_container = document.getElementById("RPI_webService_call_loading");
			if(!_container) {
				_container = jQuery.create("div", {id : "RPI_webService_call_loading"});
				jQuery(document.body).append(_container);
			} else {
				_container = jQuery(_container);
			}
		}
		_container.css("display", "block");
	}

	return {
		show : function() {
			_count++;
			if(!_timerId) {
				_timerId = setTimeout(showLoading, 1000);
			}
		},

		hide : function() {
			_count--;
			if(_count <= 0) {
				if(_timerId) {
					clearTimeout(_timerId);
					_timerId = null;
				}
				if(_container) {
					_container.css("display", "none");
				}
			}
		}
	}
})();
