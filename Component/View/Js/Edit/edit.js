/*!
 * RPI.component.edit
 * version: 1.0.0
 * Copyright © 2010 RPI Limited. All rights reserved.
 */

/**
 * @type RPI.component.edit
 * @author Matt Dunn
 */

//TODO:
//      *1) auto save
//      2) integrate rich editor (tinymce?, https://github.com/balupton/html5edit?)
//      *3) focus element


RPI._("component").edit = (function() {
    var _autoSaveEnabled = true;
    var _autoSaveTimeout = 10000;
    
    var _monitorDOMChangeEvents = true;
    
    function init() {
        jQuery(document).ready(
            function() {
                jQuery(".component-editable").each(
                    function() {
                        loadComponent(this, null, "load");
                    }
                );
            }
        );
        
        jQuery(".component-editable .options").live("click",
            function(e) {
                var component = jQuery(this).parents(".component:first");
                var target = jQuery(e.target);
                
                switch(target.data("option")) {
                    case "edit":
                        beforeLoadComponent(component, target.data("option"));
                        
                        RPI.webService.call(getServiceUrl(component), "edit", {"id" : component.data("id")}, 
                            function(data, response, sourceData) {
                                loadComponent(component, data.xhtml, target.data("option"));
                                    
                                var richEditElements = jQuery(document).find("[data-id='"+ component.data("id") + "'] .editable[data-rich-edit=true]");
                                if(richEditElements.length > 0) {
                                    if(!document.nicEditor) {
                                        document.nicEditor = new nicEditor(
                                            {
                                                buttonList : ['fontFormat', 'bold', 'italic', 'ol', 'ul', 'indent','outdent', 'image', 'upload', 'link', 'unlink', 'subscript', 'superscript']
                                            }
                                        );
                                        document.nicEditor.floatingPanel();
                                    }

                                    richEditElements.each(
                                        function() {
                                            document.nicEditor.addInstance(this);
                                        }
                                    );
                                }

                                jQuery(document).find("[data-id='"+ component.data("id") + "'] .editable:first").focus();
                            },
                            function(response, textStatus, errorThrown, isAuthenticationException, sourceData) {
                            }
                        );
                        break;
                        
                    case "save":
                        var boundElements = [];
                        
                        component.find(".editable").each(
                            function() {
                                if(this.isDirty) {
                                    var o = jQuery(this).clone();
                                    if(beforeSaveComponent(component, o, o.data("bind"), target.data("option"))) {
                                        var content = o.attr("value");
                                        if(!content) {
                                            content = jQuery.htmlClean(o.html());
                                        }
                                        boundElements.push({bind: o.data("bind"), content: content});
                                    }
                                }
                            }
                        );
                        
                        if(boundElements.length > 0) {
                            beforeLoadComponent(component, target.data("option"));

                            RPI.webService.call(getServiceUrl(component), "save", {id : component.data("id"), boundElements: boundElements}, 
                                function(data, response, sourceData) {
                                    removeEditorInstances(component);
                                    
                                    component.find(".editable").each(
                                        function() {
                                            if(this.isDirty) {
                                                this.autosave = this.isDirty = false;
                                                jQuery(document.body).attr("isDirty-count", parseInt(jQuery(document.body).attr("isDirty-count")) - 1);
                                            }
                                        }
                                    );
                                        
                                    loadComponent(component, data.xhtml, target.data("option"));
                                },
                                function(response, textStatus, errorThrown, isAuthenticationException, sourceData) {
                                    component.find(".editable").each(
                                        function() {
                                            if(this.isDirty) {
                                                this.autosave = false;
                                            }
                                        }
                                    );
                                    updateAutoSaveMessage(component, "There was a problem saving the document");
                                    if(!isAuthenticationException) {
                                        console.log("There was a problem saving the document");
                                    }
                                }
                            );
                        }
                        break;
                        
                    case "cancel":
                        beforeLoadComponent(component, target.data("option"));
                        
                        RPI.webService.call(getServiceUrl(component), "cancel", {id : component.data("id")}, 
                            function(data, response, sourceData) {
                                removeEditorInstances(component);
                                
                                component.find(".editable").each(
                                    function() {
                                        if(this.isDirty) {
                                            this.isDirty = false;
                                            jQuery(document.body).attr("isDirty-count", parseInt(jQuery(document.body).attr("isDirty-count")) - 1);
                                        }
                                    }
                                );
                                    
                                loadComponent(component, data.xhtml, target.data("option"));
                           },
                            function(response, textStatus, errorThrown, isAuthenticationException, sourceData) {
                            }
                        );
                        break;
                        
                    case "delete":
                        if(beforeDelete(component, target.data("bind"))) {
                            beforeLoadComponent(component, target.data("option"));

                            RPI.webService.call(getServiceUrl(component), "delete", {id : component.data("id"), bind : target.data("bind")}, 
                                function(data, response, sourceData) {
                                    removeEditorInstances(component);

                                    component.find(".editable").each(
                                        function() {
                                            if(this.isDirty) {
                                                this.isDirty = false;
                                                jQuery(document.body).attr("isDirty-count", parseInt(jQuery(document.body).attr("isDirty-count")) - 1);
                                            }
                                        }
                                    );

                                    loadComponent(component, data.xhtml, target.data("option"));
                                },
                                function(response, textStatus, errorThrown, isAuthenticationException, sourceData) {
                                }
                            );
                        }
                        break;
                        
                    case "add":
                        if(beforeAdd(component, target.data("bind"))) {
                            beforeLoadComponent(component, target.data("option"));

                            RPI.webService.call(getServiceUrl(component), "create", {id : component.data("id"), bind : target.data("bind"), data : {title : "", url : "/"}}, 
                                function(data, response, sourceData) {
                                    removeEditorInstances(component);

                                    component.find(".editable").each(
                                        function() {
                                            if(this.isDirty) {
                                                this.isDirty = false;
                                                jQuery(document.body).attr("isDirty-count", parseInt(jQuery(document.body).attr("isDirty-count")) - 1);
                                            }
                                        }
                                    );

                                    loadComponent(component, data.xhtml, target.data("option"));
                                },
                                function(response, textStatus, errorThrown, isAuthenticationException, sourceData) {
                                }
                            );
                        }
                        break;
                        
//                    default:
//                        alert(target.data("option")); 
               }
            }
        );

        // TODO: need to use something other than use DOMSubtreeModified as it's not supported by IE...
        jQuery(".component-editable .editable").live("change DOMCharacterDataModified DOMNodeInserted DOMNodeRemoved",
            function() {
                if(_monitorDOMChangeEvents) {
                    var _self = this;

                    if(_self.isDirty !== true || _self.autosave === false) {
                        var container = jQuery(this);
                        var component = container.parents(".component:first");

                        if(!_self.isDirty) {
                            var count = jQuery(document.body).attr("isDirty-count");
                            if(!count) {
                                count = 0;
                            }
                            jQuery(document.body).attr("isDirty-count", parseInt(count) + 1);

                            var componentCount = component.attr("isDirty-count");
                            if(!componentCount) {
                                componentCount = 0;
                            }
                            component.attr("isDirty-count", parseInt(componentCount) + 1);
                        }

                        _self.isDirty = true;
                        _self.autosave = true;

                        component.find(".options [data-option='save']").removeClass("d");

                        updateAutoSaveMessage(component, "Waiting to autosave...")

                        if(_autoSaveEnabled) {
                            setTimeout(
                                function() {
                                    if(_self.autosave) {
                                        var o = container.clone();
                                        if(beforeSaveComponent(component, o, container.data("bind"), "autosave")) {
                                            var content = o.attr("value");
                                            if(!content) {
                                                content = jQuery.htmlClean(o.html());
                                            }
                                            RPI.webService.call(getServiceUrl(component), "autoSave", {id : component.data("id"), bind: container.data("bind"), content: content}, 
                                                function(data, response, sourceData) {
                                                    if(_self.isDirty) {
                                                        _self.isDirty = false;
                                                        jQuery(document.body).attr("isDirty-count", parseInt(jQuery(document.body).attr("isDirty-count")) - 1);
                                                        component.attr("isDirty-count", parseInt(component.attr("isDirty-count")) - 1);
                                                    }

                                                    if(parseInt(component.attr("isDirty-count")) == 0) {
                                                        component.find(".options [data-option='save']").addClass("d");
                                                        updateAutoSaveMessage(component)
                                                    }
                                                },
                                                function(response, textStatus, errorThrown, isAuthenticationException, sourceData) {
                                                    _self.autosave = false;
                                                    updateAutoSaveMessage(component, "There was a problem saving the document");
                                                    if(!isAuthenticationException) {
                                                        console.log("There was a problem saving the document");
                                                    }
                                                }
                                            );
                                        }
                                    }
                                },
                                _autoSaveTimeout
                            );
                        }
                    }
                }
            }
        );
            
        jQuery(window).bind("beforeunload",
            function() {
                if(parseInt(jQuery(document.body).attr("isDirty-count")) > 0) {
                    return("You have unsaved content on your page. Do you want to continue?")
                }
            }
        );
    }
    
    function beforeSaveComponent(component, contentElement, bindName, option) {
        _monitorDOMChangeEvents = false;
        
        var event = jQuery.Event(
            "beforesave.RPI.component.edit"
        );

        jQuery(document).trigger(
            event,
            [
                component,
                contentElement,
                bindName,
                option
            ]
        );
        
        _monitorDOMChangeEvents = true;
        
        return !event.isDefaultPrevented();
    }
    
    function beforeLoadComponent(component, option) {
        _monitorDOMChangeEvents = false;
        
        jQuery.event.trigger(
            "beforeload.RPI.component.edit",
            [
                component,
                option
            ]
        );
            
        _monitorDOMChangeEvents = true;
    }
    
    function loadComponent(component, content, option) {
        _monitorDOMChangeEvents = false;
        
        if(content) {
            component.replaceWith(content);

            jQuery.event.trigger(
                "load.RPI.component.edit",
                [
                    jQuery(document).find(".component[data-id='"+ component.data("id") + "']"),
                    option
                ]
            );
        } else {
            jQuery.event.trigger(
                "load.RPI.component.edit",
                [
                    component,
                    option
                ]
            );
        }
            
        _monitorDOMChangeEvents = true;
    }
    
    function beforeAdd(component, bindName) {
        _monitorDOMChangeEvents = false;
        
        var event = jQuery.Event(
            "beforeadd.RPI.component.edit"
        );

        jQuery(document).trigger(
            event,
            [
                component,
                bindName,
                "add"
            ]
        );
        
        _monitorDOMChangeEvents = true;
        
        return !event.isDefaultPrevented();
    }

    function beforeDelete(component, bindName) {
        _monitorDOMChangeEvents = false;
        
        var event = jQuery.Event(
            "beforedelete.RPI.component.edit"
        );

        jQuery(document).trigger(
            event,
            [
                component,
                bindName,
                "delete"
            ]
        );
        
        _monitorDOMChangeEvents = true;
        
        return !event.isDefaultPrevented();
    }
    
    function updateAutoSaveMessage(component, message) {
        if(!message) {
            var now = new Date();
            var timeValue = now.getHours().toString().pad(2) + ':' + now.getMinutes().toString().pad(2) + ':' + now.getSeconds().toString().pad(2);
            message = "Saved " + timeValue;
        }
        
        if(component.find(".autosave-msg").length > 0) {
            component.find(".autosave-msg").replaceWith("<div class=\"autosave-msg\">" + message + "</div>")
        } else {
            component.append("<div class=\"autosave-msg\">" + message + "</div>")
        }
    }
    
    function removeEditorInstances(component) {
        var richEditElements = component.find(".editable[data-rich-edit=true]");
        richEditElements.each(
            function() {
                document.nicEditor.removeInstance(this);
//                console.log(document.nicEditor.nicInstances.length);
            }
        )
    }
    
    function getServiceUrl(component) {
        var serviceUrl = "/ws/component/";
        
        if (component && component.data("service")) {
            serviceUrl += component.data("service");
        }
        
        return serviceUrl;
    }
    
    init();

    return {
//		show : function() {
//		}
	}
})();
