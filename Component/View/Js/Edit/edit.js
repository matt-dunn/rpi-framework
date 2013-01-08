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
    
    function init() {
        jQuery(".component-editable .options").live("click",
            function(e) {
                var component = jQuery(this).parents(".component:first");
                var target = jQuery(e.target);
                
                switch(target.data("option")) {
                    case "edit":
                        RPI.webService.call("/ws/component/", "edit", {"id" : component.data("id")}, 
                            function(data, response, sourceData) {
                                component.replaceWith(data.xhtml);
                
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
                                var o = jQuery(this);
                                if(this.isDirty) {
                                    var content = o.attr("value");
                                    if(!content) {
                                        content = jQuery.htmlClean(o.html());
                                    }
                                    boundElements.push({bind: o.data("bind"), content: content});
                                }
                            }
                        );
                        
                        if(boundElements.length > 0) {
                            RPI.webService.call("/ws/component/", "save", {id : component.data("id"), boundElements: boundElements}, 
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
                                    component.replaceWith(data.xhtml);
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
                        RPI.webService.call("/ws/component/", "cancel", {id : component.data("id")}, 
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
                                component.replaceWith(data.xhtml);
                            },
                            function(response, textStatus, errorThrown, isAuthenticationException, sourceData) {
                            }
                        );
                        break;
                        
                    case "delete":
                        RPI.webService.call("/ws/component/", "delete", {id : component.data("id"), bind : target.data("bind")}, 
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
                                component.replaceWith(data.xhtml);
                            },
                            function(response, textStatus, errorThrown, isAuthenticationException, sourceData) {
                            }
                        );
                        break;
                        
                    case "add":
                        RPI.webService.call("/ws/component/", "create", {id : component.data("id"), bind : target.data("bind"), data : {title : "", url : "/"}}, 
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
                                component.replaceWith(data.xhtml);
                            },
                            function(response, textStatus, errorThrown, isAuthenticationException, sourceData) {
                            }
                        );
                        break;
                        
//                    default:
//                        alert(target.data("option")); 
               }
            }
        );

        // TODO: need to use something other than use DOMSubtreeModified as it's not supported by IE...
        jQuery(".component-editable .editable").live("change DOMCharacterDataModified DOMNodeInserted DOMNodeRemoved",
            function() {
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
                                    var content = container.attr("value");
                                    if(!content) {
                                        content = jQuery.htmlClean(container.html());
                                    }
                                    RPI.webService.call("/ws/component/", "autoSave", {id : component.data("id"), bind: container.data("bind"), content: content}, 
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
                            },
                            _autoSaveTimeout
                        );
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
    
    init();

    return {
//		show : function() {
//		}
	}
})();
