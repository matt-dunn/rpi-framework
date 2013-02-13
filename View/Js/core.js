/*!
 * RPI.core
 * version: 2.1.3
 * Copyright © 2009 RPI Limited. All rights reserved.
 */

/**
 * @type RPI.core
 * @author Matt Dunn
 */

/* = PROTOTYPES and GLOBAL ======================================================================================== */

function createNamespace(obj, namespace) {
	if(namespace) {
		var parts = namespace.split(".");
		for(var i = 0; i < parts.length; i++) {
			if(!obj[parts[i]]) {
				obj[parts[i]] = {};
			}
			obj = obj[parts[i]];
		}
		return obj;
	} else {
		throw new Error("RPI._: parameter 'namespace' must be defined");
	}
}

if(!window.console) {
	// TODO:
	window.console = {
		log : function() {},
		dir : function() {},
		error : function() {}
	};
}

window.assert = function(condition, ex, throwException) {
	if(!condition) {
		handleException(ex, throwException);
	}
	
	return condition;
};

window.handleException = function(ex, throwException) {
	log(ex, true);
	if(throwException) {
		throw ex;
	}
};

function guid() {
	function S4() {
	   return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
	}
   return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
}

Array.prototype.ISARRAY = true;

/* string prototypes */
String.prototype.rtrim = function() {
	return this.replace(/\s*$/,"");
};

String.prototype.ltrim = function() {
	return this.replace(/^\s*/,"");
};

String.prototype.trim = function() {
	return this.ltrim().rtrim();
};

String.prototype.capitaliseFirstWord = function() {
	return this.charAt(0).toUpperCase() + this.substring(1).toLowerCase();
};

String.prototype.beginsWith = function(s) {
	return (this.substr(0, s.length) == s);
};

String.prototype.endsWith = function(s) {
	return (this.substr(this.length - s.length) == s);
};

String.prototype.repeat = function(num) {
    return new Array(num + 1).join( this );
}

String.prototype.pad = function(num, character) {
    if(!character) {
        character = "0";
    } else {
        character = character.toString();
    }
    var pad = character.repeat(num);
    
    return pad.substr(0, num - this.length) + this;
}

String.prototype.URLEncode = function() {
	var SAFECHARS = "0123456789" +
		"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +
		"abcdefghijklmnopqrstuvwxyz" +
		"-_.!~*'()";
	var HEX = "0123456789ABCDEF";

	function gethex(decimal) {
		return "%" + HEX.charAt(decimal >> 4) + HEX.charAt(decimal & 0xF);
	}

	var encoded = "";
	for (var i = 0; i < this.length; i++ ) {
		var ch = this.charAt(i);
		if(ch == " ") {
			encoded += "+";								// x-www-urlencoded, rather than %20
		} else if(SAFECHARS.indexOf(ch) != -1) {
			encoded += ch;
		} else {
			var charCode = ch.charCodeAt(0);

			if(charCode < 128) {
				encoded = encoded + gethex(charCode);
			} else if(charCode > 127 && charCode < 2048) {
				encoded = encoded + gethex((charCode >> 6) | 0xC0);
				encoded = encoded + gethex((charCode & 0x3F) | 0x80);
			} else if(charCode > 2047 && charCode < 65536) {
				encoded = encoded + gethex((charCode >> 12) | 0xE0);
				encoded = encoded + gethex(((charCode >> 6) & 0x3F) | 0x80);
				encoded = encoded + gethex((charCode & 0x3F) | 0x80);
			} else if(charCode > 65535) {
				encoded = encoded + gethex((charCode >> 18) | 0xF0);
				encoded = encoded + gethex(((charCode >> 12) & 0x3F) | 0x80);
				encoded = encoded + gethex(((charCode >> 6) & 0x3F) | 0x80);
				encoded = encoded + gethex((charCode & 0x3F) | 0x80);
			}
		}
	}

	return encoded;
};





var RPI = {
	VERSION : "2.1.3",
	NAME : "RPI",

	_ : function(namespace) {
		return createNamespace(this, namespace);
	},

	core : {event : {}},
	framework : {},
	DOM : {},

	debug : {
		trace : true
	}
};


Date.format = {ABBR : "dateAbbr", SHORT : "dateShort", LONG : "dateLong"};

Date.prototype.dayName = function (format, day) {
	var format = format || Date.format.LONG, day = (day === undefined ? this.getDay() : day) % 7;
	var days = RPI.core.locale.get().dayName[format];
	if(!days) {
		throw new Error("Date.dayName: Invalid format");
	}
	return days[day];
};

Date.prototype.monthName = function (format, month) {
	var format = format || Date.format.LONG, month = (month === undefined ? this.getMonth() : month) % 12;
	var months = RPI.core.locale.get().monthName[format];
	if(!months) {
		throw new Error("Date.monthName: Invalid format");
	}
	return months[month];
};

Date.prototype.getDaysInMonth = function(month, year) {
	month = (month !== undefined ? month : this.getMonth());
	year = (year !== undefined ? year : this.getFullYear());
	return 32 - new Date(year, month, 32).getDate();
};

Date.prototype.getWeekNumber = function() {
	var d = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
	var DoW = d.getDay();
	d.setDate(d.getDate() - (DoW + 6) % 7 + 3); // Nearest Thu
	var ms = d.valueOf(); // GMT
	d.setMonth(0);
	d.setDate(4); // Thu in Week 1
	return Math.round((ms - d.valueOf()) / (7 * 864e5)) + 1;
};

/* = ABSTRACT ======================================================================================== */

RPI.core.event.manager = function() {
	this.VERSION = (this.VERSION || "") + "(1.0.2)";
	this.NAME = (this.NAME || "") + "(RPI.core.event.manager)";

	var oEvents = [];

	function register(args) {
		// Register the available events...
		for(var i = 0; i < args.length; i++) {
			oEvents[args[i]] = [];
		}
	}
	
	this.registerEvents = function() {
		register(arguments);
	};
	
	register(arguments);
	
	this.addEventListener = function(eventName, functionPointer) {
		var oEvent = oEvents[eventName];
		if(!oEvent) {
			throw new Error("RP.core.event.manager.addEventListener: '" + eventName + "' is not a registered event");
		} else {
			oEvent[oEvent.length] = functionPointer;
		}
	};
	
	this.removeEventListener = function(eventName, functionPointer) {
		var oEvent = oEvents[eventName];
		if(!oEvent) {
			throw new Error("RP.core.event.manager.removeEventListener: '" + eventName + "' is not a registered event");
		} else {
			for(var i = 0; i < oEvent.length; i++) {
				if(oEvent[i] === functionPointer) {
					delete oEvent[i];
					oEvent.splice(i, 1);
					break;
				}
			}
		}
	};

	this.dispatchEvent = function(eventName, e, cancelable) {
		var oEvent = oEvents[eventName];
		if(oEvent) {
			e = e || {};
			e.type = e.type || eventName;
			e.timeStamp = new Date().getTime();
			e.cancelEvent = e.cancelEvent || false;
			e.cancelBubble = e.cancelBubble || false;
			e.returnValue = true;
			e.target = e.target || this;
			e.canceldisabled = (cancelable ? false : true);
			for(var i = 0; i < oEvent.length && !e.cancelBubble; i++) {
				oEvent[i](e);
			}
		} else {
			throw new Error("RPI.core.event.manager.dispatchEvent: '" + eventName + "' is not a registered event");
		}
		
		return e;
	};
};

RPI.DOM.node = (function() {
	return {
		hasAncestor : function(node, id) {
			while(node) {
				if(node.id == id) {
					return true;
				}
				node = node.parentNode;
			}
			return false;
		}
	};
})();

RPI.core.locale = (function() {
	var _lang;
	
	return {
		set : function(locale) {
			if(locale && this[locale.toUpperCase()]) {
				_lang = this[locale.toUpperCase()];
			} else if(this["EN"]) {
				_lang = this["EN"];
			} else {
				throw new Error("No locale language set found for '" + locale + "'");
			}
		},
		get : function(object) {
			if(!_lang) {
				this.set("EN");
			}

			return _lang;
		}
	};
})();


RPI.DOM.helpers = (function() {
	return {
		makeSameHeight : function(elements, offsetIndex, offsetLength) {
			if(!offsetIndex) {
				offsetIndex = 0;
			}
			if(!offsetLength) {
				offsetLength = elements.length;
			} else {
				offsetLength += offsetIndex;
			}
			var maxHeight = 0;
			for(var i = offsetIndex; i < offsetLength; i++) {
				if(elements[i]) {
					elementHeight = jQuery(elements[i]).height();
					if(elementHeight > maxHeight) {
						maxHeight = elementHeight;
					}
				}
			}

			for(var i = offsetIndex; i < offsetLength; i++) {
				jQuery(elements[i]).height(maxHeight);
			}
		},
		makeRowSameHeight : function(elements, columns) {
			if(elements && elements.length > 0) {
				if(!columns) {
					var matches = elements[0].className.match(/columns-(\d*)/i);
					if(matches && matches.length > 1) {
						columns = parseInt(matches[1]);
					} else {
						columns = 1;
					}
				}

				if(isNaN(columns) || columns < 1) {
					columns = 1;
				}

				for(var i = 0; i < elements.length; i += columns) {
					this.makeSameHeight(elements, i, columns);
				}
			}
		}
	};
})();

RPI.framework.components = {
	register : function(component) {
		this[component] = component;
	},
	
	init : function() {
		for(var component in RPI.framework.components) {
			var oComponent = RPI.framework.components[component];
			if(oComponent && oComponent.create && oComponent.selector) {
				oComponent.create({elements : jQuery(oComponent.selector)});
			} else if(oComponent && oComponent.create && oComponent.defaultClassName) {
				oComponent.create({elements : jQuery("." + oComponent.defaultClassName)});
			}
		}
	}
};

/* TODO: should this always be set?
if(window.jQuery) {
	jQuery.noConflict();
}
*/


jQuery().ready(
	function() {
//		jQuery(document.body).addClass("component_js");
		
		RPI.framework.components.init();

        jQuery(document).on(
            "click",
            "a[rel='external']:not([contenteditable='true'])",
			function(e) {
				e.preventDefault();
				window.open(this.href);
			}
		);
	}
);

// Wrapper function for inline javascript support
//		Debug loaded JS use this function to ensure inline code that have not been loaded by call time are postponed to after all dynamoc script files have loaded
if(!window._) {
	function _(o) {
		if(o) {
			o.call(document);
		}
	}
}

