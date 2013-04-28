/*!
 * RPI.validation
 * version: 2.7.0
 * Copyright © 2009 RPI Limited. All rights reserved.
 */

/**
 * @type RPI.validation
 * @author Matt Dunn
 */

RPI.validation = new function() {
	RPI.core.event.manager.call(this,
//        "init",
        
		"beforevalidate", 
		"validate", 

		"beforerendermessage",
		"rendermessage",

		"displaycustommessages", 

		"beforemessagealert",
		"messagealert",

		"beforevalidationcomplete",
		"validationcomplete"
	);

	this.VERSION = "2.7.0";
	this.NAME = "RPI.validation";

	var _self = this;
	
	var _isValidating = false;
	var _forms = [];
	var _activeElementName = null;
	var _defaultElementNameDefined = false;
	var _registeredForms = [];
	
	this.form = function(name) {
		this.name = name;
		this.formItems = [];
		this.validators = [];
	};
	
	this.formItem = function(name) {
        this.hasError = function() {
            for(var i = 0; i < this.validators.length; i++) {
                if(this.validators[i].hasError) {
                    return true;
                }
            }
            
            return false;
        }
		this.validators = [];
		this.name = name;
	};
	
	// = CUSTOM VALIDATORS  ==================================================================================================================================
	
	// Custom expression validator
	this.Custom = function(message, buttons, expression) {
		this.type = "Custom";
		this.formItem = null; // Parent
		this.hasError = false;
		this.message = message;
		this.buttons = buttons;
        
        this.validating = false;
			
		this.expression = expression;
        
		this.validate = function(formItem, form) {
            if(!this.validating) {
                this.hasError = false;
                jQuery(formItem).bind("keypress",
                    function(e) {
						if(e.keyCode != 9) {
                            e.preventDefault();
                        }
                    }
                );
                if(!this.formItem.hasError()) {
                    var formValues = serializeForm(form);
                    
                    var validator = this;
		
                    this.validating = true;
                
                    var oFormItem = jQuery(formItem);
                    setTimeout(
                        function() {
                            oFormItem.parent(".c:first").addClass("processing");
                        },
                        1000
                    );
                    var component = jQuery(form).parents(".component:first");
                    RPI.webService.call("/ws/form/", "validateCustom", {"id": component.data("id"), "bind": formItem.name, "value": _getFormElementValue(formItem), "formValues": formValues}, 
                        function(data, response, sourceData) {
                            validator.validating = false;
                            oFormItem.unbind("keypress");
                            oFormItem.parent(".c:first").removeClass("processing");
                            
                            if(data.hasError == true) {
                                this.hasError = true;
                                _self._setControlMessage(true, form.elements[formItem.name], data.message);
                            } else {
                                _self._setControlMessage(false, form.elements[formItem.name], null, true);
                            }
                        },
                        function(response, textStatus, errorThrown, isAuthenticationException, sourceData) {
                            oFormItem.unbind("keypress")
                            oFormItem.parent(".c:first").removeClass("processing");
                            validator.validating = false;
                        }
                    );
                }
            }
            
            return !this.hasError;
		};
	};
	
	//  ------------------------------------------------------------------------------------------------------------------------------------------------------
	
	// Regular expression validator
	this.RegularExpression = function(message, buttons, expression) {
		this.type = "RegularExpression";
		this.formItem = null; // Parent
		this.hasError = false;
		this.message = message;
		this.buttons = buttons;
			
		this.expression = expression;
		
		this.validate = function(formItem, form) {
			if(_getFormElementValue(formItem) != "") {
				this.hasError = !this.expression.test(_getFormElementValue(formItem));
			} else {
				this.hasError = false;
			}
			return !this.hasError;
		};
	};
	
	//  ------------------------------------------------------------------------------------------------------------------------------------------------------
	
	// Is Required validator
	this.IsRequired = function(message, buttons) {
		this.type = "IsRequired";
		this.formItem = null; // Parent
		this.hasError = false;
		this.message = message;
		this.buttons = buttons;
		
		this.validate = function(formItem, form) {
			this.hasError = (_getFormElementValue(formItem).trim() == "");
			return !this.hasError;
		};
	};
	
	//  ------------------------------------------------------------------------------------------------------------------------------------------------------
	
	// Confirmation validator
	this.Confirm = function(message, buttons, confirmName, ignoreCase) {
		this.type = "Confirm";
		this.formItem = null; // Parent
		this.hasError = false;
		this.message = message;
		this.buttons = buttons;
		
		this.confirmName = confirmName;
		this.ignoreCase = ignoreCase;
		
		this.validate = function(formItem, form) {
			var oConfirmValue = form.elements[this.confirmName];
			if(oConfirmValue) {
				if(this.ignoreCase) {
					this.hasError = (oConfirmValue.value.toLowerCase() != _getFormElementValue(formItem).toLowerCase());
				} else {
					this.hasError = (oConfirmValue.value != _getFormElementValue(formItem));
				}
			} else {
				this.hasError = false;
			}
			return !this.hasError;
		};
	};
	
	//  ------------------------------------------------------------------------------------------------------------------------------------------------------
	
	// Comparison validator
	this.Comparison = function(message, buttons, compareName, expression, validator) {
		this.type = "Comparison";
		this.formItem = null; // Parent
		this.hasError = false;
		this.message = message;
		this.buttons = buttons;
		
		this.compareName = compareName;
		if(expression) {
			this.expression = new RegExp(expression);
		}
		this.validator = validator;

		this.validate = function(formItem, form) {
			var oCompareValue = form.elements[this.compareName];
			this.hasError = false;
			if(oCompareValue) {
				var sValue;
				if(!oCompareValue.type) {
					sValue = _getRadioValue(oCompareValue);
				} else {
					sValue = _getFormElementValue(oCompareValue);
				}
				if(this.expression && this.expression.test(sValue)) {
					this.hasError = !this.validator.validate(formItem, form);
					// Commented this out as the back-end uses the *comparision* validator message
//					if(this.hasError) {
//						this.message = this.validator.message;
//					}
				}
			}
			return !this.hasError;
		};
	};
	
	//  ------------------------------------------------------------------------------------------------------------------------------------------------------
	
    // TODO: implement server Number validator
	// Number validator
//	this.Number = function(message, buttons, decimalPlaces, allowZero, allowNegative) {
//		this.type = "Number";
//		this.formItem = null; // Parent
//		this.hasError = false;
//		this.message = message;
//		this.buttons = buttons;
//		
//		this.decimalPlaces = decimalPlaces;
//		this.allowZero = allowZero;
//		this.allowNegative = allowNegative;
//		
//		this.validate = function(formItem, form) {
//			this.hasError = false;
//		
//			var val = _getFormElementValue(formItem);
//			
//			if(val != "") {
//				if(isNaN(val)) {
//					this.hasError = true;
//				} else {
//					if((!this.allowNegative && val < 0) ||
//							(!this.allowZero && val == 0)) {
//						this.hasError = true;
//					} else {
//						var decimalIndex = val.indexOf(".");
//						if(decimalIndex != -1 && this.decimalPlaces == 0) {
//							this.hasError = true;
//						} else {
//							var d = val.substr(decimalIndex + 1);
//							if(decimalIndex != -1 && d.length > this.decimalPlaces) {
//								this.hasError = true;
//							}
//						}
//					}
//				}
//			}
//			
//			return !this.hasError;
//		};
//	};
	
	//  ------------------------------------------------------------------------------------------------------------------------------------------------------

    // TODO: implement server Range validator
	// Number range validator
//	this.Range = function(message, buttons, min, max) {
//		this.type = "Range";
//		this.formItem = null; // Parent
//		this.hasError = false;
//		this.message = message;
//		this.buttons = buttons;
//		
//		this.max = max;
//		this.min = min;
//		
//		this.validate = function(formItem, form) {
//			this.hasError = false;
//		
//			var val = _getFormElementValue(formItem);
//			
//			if(val != "") {
//				if(isNaN(val) || val < this.min || val > this.max) {
//					this.hasError = true;
//				}
//			}
//			
//			return !this.hasError;
//		};
//	};
	
	//  ======================================================================================================================================================

    function serializeForm(form, ignoreEmptyValue) {
        var formValues = [], radioElementNames = "", formItemValue, bSerializeItem;
        
        for(var i = 0; i < form.elements.length; i++) {
            if(form.elements[i].name && (form.elements[i].type != "image" && form.elements[i].type != "submit")) {
                formItemValue = _getFormElementValue(form.elements[i]);
                if(!(ignoreEmptyValue === true && formItemValue.trim() == "")) {
                    bSerializeItem = true;
                    // Only pass the first radio element with the selected value:
                    if(form.elements[i].type == "radio") {
                        if(radioElementNames.indexOf("[" + form.elements[i].name + "]") == -1) {
                            radioElementNames += ("[" + form.elements[i].name + "]");
                        } else {
                            bSerializeItem = false;
                        }
                    }

                    if(bSerializeItem) {
                        formValues.push({"name": form.elements[i].name, "value": formItemValue});
                    }
                }
            }
        }
        
        return formValues;
    }
    
	function _getFormElementValue(formElement) {
		// TODO: support for multiple select values?
		if(formElement.type == "checkbox") {
			if(formElement.checked) {
				return formElement.value;
			} else {
				return "";
			}
		} else if(formElement.type == "radio") {
			return _getRadioValue(formElement.form.elements[formElement.name]);
		} else if(formElement.type == "textarea") {
			// Ensure that a new line in a textarea is always treated as 2 characters in the js...
			return formElement.value.replace(/\r{0,}\n/g, "\r\n");
		}
		
		return formElement.value;
	}
	
	function _getRadioValue(o) {
		if(o) {
			for(var i = 0; i < o.length; i++) {
				if(o[i].checked) {
					return o[i].value;
				}
			}
		}
		return "";
	}
	
	
	// Add a validator
	this.add = function(name, formItems, validators) {
		var f = _forms[name];
		if(!f) {
			f = _forms[name] = new _self.form(name);
		}

		if(formItems) {
			for(var i = 0; i < formItems.length; i++) {
				_appendFormItem(f, formItems[i]);
			}
		}
		
		if(validators) {
			for(var i = 0; i < validators.length; i++) {
				validators[i].v.formItemName = validators[i].n;
				_appendValidator(f, validators[i].v);
			}
		}
	};
	
	function _appendFormItem(form, formItem) {
		if(formItem) {
			var fi = form.formItems[formItem.n];
			if(!fi) {
				fi = form.formItems[formItem.n] = new _self.formItem(formItem.n);
			}
			if(formItem.db) {
				fi.defaultButton = formItem.db;
			}

			if(formItem.v) {
				for(var i = 0; i < formItem.v.length; i++) {
					formItem.v[i].formItem = fi;
					_appendValidator(fi, formItem.v[i]);
				}
			}
		}
	}
	
	function _appendValidator(formItem, validator) {
		formItem.validators.push(validator);
	}
	
	this.detachForm = function(formId) {
        _forms[formId] = null;
        
		if(document.getElementById && document.getElementsByName) {
			var formElement = document.getElementById(formId);
			if(formElement) {
				jQuery(formElement).find("input,select,textarea").off("blur");
				jQuery(formElement).find("input,select,button").off("focus");
				jQuery(formElement).unbind("submit");
				jQuery(formElement).unbind("click");
				jQuery(formElement).unbind("keypress");
			}
		}
    }
    
	// Attach Form Events
	this.attachForm = function(formId, formItems, validators) {
		this.add(formId, formItems, validators);
		
		if(document.getElementById && document.getElementsByName) {
			var formElement = document.getElementById(formId);
			if(formElement) {
				jQuery(formElement).find("input,select,textarea").on(
                    "blur", 
                    function() {
                        validateFormItem(this, null, null, true);
                    }
                );
				jQuery(formElement).find("input,select,button").on(
                    "focus", 
					function(e) {
						if(e.target && !_defaultElementNameDefined) { // && (e.target.type == "image" || e.target.type == "submit")) {
                            if((e.target.type == "image" || e.target.type == "submit")) {
    							_activeElementName = e.target.value;
                            } else if(e.target.name) {
    							_activeElementName = e.target.name;
                            } else if(e.target.id) {
    							_activeElementName = e.target.id;
                            }
						}
//                        console.log("Active: " + _activeElementName)
                        e.target.focus();
//                        return false;
					}
                );
				jQuery(formElement).bind("submit", validateForm);
				jQuery(formElement).bind("click",
					function(e) {
						if(e.target && !_defaultElementNameDefined) { // && (e.target.type == "image" || e.target.type == "submit")) {
                            if((e.target.type == "image" || e.target.type == "submit")) {
    							_activeElementName = e.target.value;
//                            } else if(e.target.name) {
//    							_activeElementName = e.target.name;
//                            } else if(e.target.id) {
//    							_activeElementName = e.target.id;
                            }
						}
//                        _defaultElementNameDefined = true;
//                        console.log("Active: " + _activeElementName)
                        return true;
					}
				);
				jQuery(formElement).bind("keypress",
					function(e) {
						if(e.target && e.target.id && e.keyCode == 13 && e.target.tagName.toUpperCase() != "TEXTAREA") { // &&  (e.target.type == "image" || e.target.type == "submit")) {
							if(e.target.form && _forms[e.target.form.id]) {
                                var formItemId = e.target.id;
                                // If the form item has a reference defined (e.g. radio options) use that instead
                                if(jQuery(e.target).data("ref")) {
                                    formItemId = jQuery(e.target).data("ref");
                                }
								var fi = _forms[e.target.form.id].formItems[formItemId];
								if(fi && fi.defaultButton) {
									_activeElementName = fi.defaultButton;
									_defaultElementNameDefined = true;
                                    if(e.target.tagName.toUpperCase() == "SELECT") {
                                        // Find the associated form button and emulate a click on the default button
                                        for(var i = 0; i < e.target.form.elements.length; i++) {
                                            if(e.target.form.elements[i].value == _activeElementName) {
                                                e.target.form.elements[i].click();
                                                break;
                                            }
                                        }
                                    }
//                                    validateForm(e, e.target.form);
								}
							}
						}
					}
				);
                    
                jQuery(formElement).find(".auto:first").each(
                    function() {
                        var focusElement = jQuery(this).find("input,select")
                        focusElement.focus();
                        focusElement.caretToEnd();
                    }
                );
			}
		}
	};

// TODO: required?
//	this.init = function(formId) {
//		var formElement = document.getElementById(formId), elements;
//		if(formElement) {
//			elements = formElement.elements;
//		}
//        _self.dispatchEvent("init", {form: this, formId: formId, elements: elements});
//	};

	function validateForm(e, oForm) {
        if(!oForm) {
            oForm = this;
        }
		
		if(oForm.submitting) {
			return false;
		}
        
        oForm.submitting = true;
		
		var oEvent = _self.dispatchEvent("beforevalidate", {form : oForm}, true);
		if(!oEvent.returnValue) {
            oForm.submitting = false;
			e.preventDefault();
			_self.dispatchEvent("validationcomplete");
			return false;
		}
		
		_isValidating = true;
		
		_self.dispatchEvent("validate", {form : oForm});

		var firstError, firstErrorDetails = {control : null, message : "", validator : null};

		var formItems = _forms[oForm.id].formItems;
		var totalErrorCount = 0, oFormElement, message, validatorMessage, radioElementNames = "", bValidate;
		for(var i = 0; i < oForm.elements.length; i++) {
            bValidate = true;
            oFormElement = oForm.elements[i];
            if(oFormElement.type == "radio") {
                if(radioElementNames.indexOf("[" + oFormElement.name + "]") == -1) {
                    radioElementNames += ("[" + oFormElement.name + "]");
                } else {
                    bValidate = false;
                }
            }
            if(bValidate) {
                var details = {firstError:null, message: null};
                totalErrorCount += validateFormItem(oFormElement, formItems, details);
                if(details.firstError && !firstError) {
                    firstError = details.firstError;
                    message = details.message;
                }
            }
		}

        if(!firstErrorDetails.control) {
			// NOTE: Custom messages are only supported client-side to provide additional information that will not be available with scripting  turned off
			var oCustomMessageHandler = new function() {
				oForm.form = oForm;
		
				var _messages = [];
				
				oForm.setMessage = function(control, message) {
					if(control) {
						_messages[_messages.length] = {control : control, message : message};
					} else {
						throw new Error("RPI.validation.custommessage.setMessage: control passed to setMessage does not exist");
					}
				};

				_self.dispatchEvent("displaycustommessages", this);
	
				if(_messages.length > 0) {
					firstErrorDetails.control = _messages[0].control;
					firstErrorDetails.message = _messages[0].message;
		
					for(var i = 0; i < _messages.length; i++) {
						_self._setControlMessage(true, _messages[i].control, _messages[i].message);
					}
				} else {
					firstErrorDetails.control = firstError;
					firstErrorDetails.message = message;
				}
			};
		}
		
		if(firstErrorDetails.control) {
			if(firstErrorDetails.message) {
				var oEvent = _self.dispatchEvent("beforemessagealert", {form : oForm, details : firstErrorDetails}, true);
				if(oEvent.returnValue) {
                    jQuery(oForm).find(".error:first input").focus();
					
					_self.dispatchEvent("messagealert", {form : oForm, details : firstErrorDetails});
				}
			}

            oForm.submitting = false;
			e.preventDefault();
		}

        var oEvent = _self.dispatchEvent("beforevalidationcomplete", {form : oForm, hasErrors : (totalErrorCount > 0)}, true);
		if(!oEvent.returnValue) {
            oForm.submitting = false;
			e.preventDefault();
		}

		_isValidating = false;

        if(totalErrorCount == 0) {
            if(_activeElementName) {
                var button = null;
                for(var i = 0; i < oForm.elements.length; i++) {
                    if(oForm.elements[i].value == _activeElementName) {
                        button = oForm.elements[i];
                        break;
                    }
                }
                
                if(button && jQuery(button).data("dynamic") == true) {
                    oForm.submitting = false;
        			e.preventDefault();
                    
                    if(!button.processing) {
                        button.processing = true;

                        var formValues = serializeForm(oForm, true);
                        formValues.push({"name": button.name, "value": button.value});

                        var component = jQuery(oForm).parents(".component:first");
                        component.overlayShow(500);
                        
                        _self.detachForm(oForm.id);
                        
                        RPI.webService.call("/ws/form/", "post", {"id": component.data("id"), "formValues": formValues}, 
                            function(data, response, sourceData) {
                                button.processing = false;
                                component.replaceWith(data.xhtml);
                                
                            },
                            function(response, textStatus, errorThrown, isAuthenticationException, sourceData) {
                                component.overlayHide();
                                button.processing = false;
                            }
                        );
                    }
                }
            }
        }
        
		_self.dispatchEvent("validationcomplete");
//        console.log(oForm.submitting)

        if(oForm.submitting) {
            jQuery(oForm).parents(".component:first").overlayShow();
        }

        _activeElementName = null;
		_defaultElementNameDefined = false;
        return true;
	}
    
    /**
     * Validate an individual form item
     * @param oFormElement required             HTML form element to validate
     * @param formItems optional                Collection of formItem objects associated with the oFormElement form
     * @param details optional                  Details about the validation. {firstError: formelement, message: validation message}
     * @param ignoreButtons optional            Force validation to ignore any buttons associate with validators
     **/
    function validateFormItem(oFormElement, formItems, details, ignoreButtons) {
        // Hide and messages against the component:
        jQuery(".h-messages").hide();
        jQuery(oFormElement).parents(".component:first").find(".c-messages").hide();
        
        var errorCount = 0;
        if(oFormElement.name) {
            var validatorMessage = "";
            var oForm = oFormElement.form;

            if(!formItems) {
                formItems = _forms[oForm.id].formItems;
            }

            if(oFormElement.id && oFormElement.type != "hidden" && oFormElement.type != "button" && oFormElement.type != "submit") {
                var bValidValidators = false;

//                var fi = null;
//                
//                var formElementName = new String(oFormElement.id);
//                var iIndex = formElementName.indexOf("_");
//                if(iIndex != -1) {
//                    var elementIndex = formElementName.substr(iIndex + 1);
//                    if(parseInt(elementIndex) == elementIndex) {
//                        var baseName = formElementName.substr(0, iIndex);
//                        if(baseName) {
//                            fi = formItems[baseName];
//                        }
//                    }
//                }
//
//                if(!fi) {
//                    fi = formItems[oFormElement.id];
//                }

                var elementId = oFormElement.id;
                if(oFormElement.type == "radio") {
                    elementId = jQuery(oFormElement).data("ref");
                }
                var fi = formItems[elementId];
                if(fi && fi.validators && fi.validators.length > 0) {
                    for(var ii = 0; ii < fi.validators.length; ii++) {
                        if(!_activeElementName || ignoreButtons || !fi.validators[ii].buttons || fi.validators[ii].buttons.length == 0 || (("[" + fi.validators[ii].buttons.join("][")  + "]").indexOf("[" + _activeElementName + "]") != -1)) {
                            fi.validators[ii].validate(oFormElement, oForm);
                            if(fi.validators[ii].hasError) {
                                errorCount++;

                                if(!validatorMessage) {
                                    validatorMessage = fi.validators[ii].message;
                                }

                                if(details && !details.firstError) {
                                    details.firstError = oFormElement;
                                    details.message = validatorMessage;
                                }
                                break;
                            } else {
                                bValidValidators = true;
                                // Reset if the validator was a custom validator. This will make sure the form item is not set to 'success' or 'error' until the completion of the ajax call
                                if(fi.validators[ii].type == "Custom") {
                                    bValidValidators = false;
                                }
                            }
                        }
                    }
                }

                _self._setControlMessage((errorCount > 0), oFormElement, validatorMessage, bValidValidators);
            }
        }

        return errorCount;
    }
	
	this._setControlMessage = function(hasError, oFormElement, validatorMessage, hasValidValidators) {
		var oEvent = _self.dispatchEvent("beforerendermessage", {hasError : hasError, formElement : oFormElement, message : validatorMessage},  true);
		if(!oEvent.returnValue) {
			return;
		}
        var id = oFormElement.id + "-emsg";
        var messageElementType = "span";
        if(oFormElement.type == "radio") {
            id = jQuery(oFormElement).data("ref") + "-emsg";
            messageElementType = "p";
        }
		var o = jQuery("#" + id);
		var formElement = jQuery(oFormElement);
		var formElementParent = formElement.closest("p");
		if(formElement.attr("type") == "radio") {
			formElementParent = formElementParent.closest("fieldset");
		}

		if(hasError) {
			formElementParent.removeClass("success");
			formElementParent.addClass("error");
	
			if(validatorMessage != "") {
				if(o.length == 0) {
					o = jQuery.create(messageElementType, {id : id, "class" : "msg"});
					formElementParent.prepend(o);
				}
				o.css("display", "block");
				jQuery(o).html(validatorMessage);
			}
		} else {
			if(o.length > 0) {
				o.css("display", "none");
			}
			if(formElementParent.find(messageElementType + ".msg:visible").length <= 0) {
				formElementParent.removeClass("error");
                if(hasValidValidators) {
                    formElementParent.addClass("success");
                } else {
                    formElementParent.removeClass("success");
                }
			}
		}
		
		_self.dispatchEvent("rendermessage", {hasError : hasError, formElement : oFormElement, message : validatorMessage});
	};
};


// Aliases:
var IsRequired = RPI.validation.IsRequired;
var RegularExpression = RPI.validation.RegularExpression;
var Range = RPI.validation.Range;
var Number = RPI.validation.Number;
var Confirm = RPI.validation.Confirm;
var Comparison = RPI.validation.Comparison;
var Custom = RPI.validation.Custom;


// TODO: Remove debug logging
/*
jQuery(RPI.validation).bind("beforevalidate",
	function(e) {
//		e.preventDefault();
		console.log("beforevalidate");
	}
);

jQuery(RPI.validation).bind("validate",
	function(e) {
		console.log("validate");
	}
);

jQuery(RPI.validation).bind("beforerendermessage",
	function(e) {
//		e.preventDefault();
		console.log("beforerendermessage: " + e.originalEvent.formElement.name + " - " + e.originalEvent.hasError);
	}
);

jQuery(RPI.validation).bind("rendermessage",
	function(e) {
		console.log("rendermessage");
	}
);

jQuery(RPI.validation).bind("displaycustommessages",
	function(e) {
		console.log("displaycustommessages");
		//if(document.getElementById("name").value == "x") {
		//	e.setMessage(document.getElementById("name"), "Custom message for address...");
		//}
	}
);

jQuery(RPI.validation).bind("beforemessagealert",
	function(e) {
//		e.preventDefault();
		console.log("beforemessagealert");
	}
);

jQuery(RPI.validation).bind("messagealert",
	function(e) {
		console.log("messagealert");
	}
);

jQuery(RPI.validation).bind("beforevalidationcomplete",
	function(e) {
//		e.preventDefault();
		console.log("beforevalidationcomplete");
	}
);

jQuery(RPI.validation).bind("validationcomplete",
	function(e) {
		console.log("validationcomplete");
	}
);
*/



