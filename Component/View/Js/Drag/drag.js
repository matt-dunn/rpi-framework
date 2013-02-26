/*!
 * RPI.component.drag
 * version: 1.0.0
 * Copyright © 2013 RPI Limited. All rights reserved.
 */

/**
 * @type RPI.component.drag
 * @author Matt Dunn
 */

RPI._("component").drag = {
    
    jQuery : $,
    
    settings : {
        columns : '.component-editmode.draggable-container .draggable-section',
        widgetSelector: '.component',
        handleSelector: '.drag-move',
        widgetDefault : {
            movable: true
        },
        widgetIndividual : {
            intro : {
                movable: false
            }
        }
    },

    init : function () {
        this.makeSortable();
    },
    
    getWidgetSettings : function (id) {
        var $ = this.jQuery,
            settings = this.settings;
        return (id&&settings.widgetIndividual[id]) ? $.extend({},settings.widgetDefault,settings.widgetIndividual[id]) : settings.widgetDefault;
    },
    
    makeSortable : function () {
        var self = this,
            $ = this.jQuery,
            settings = this.settings,
            $sortableItems = (function () {
                var notSortable = '';
                $(settings.widgetSelector,$(settings.columns)).each(function (i) {
                    if (!self.getWidgetSettings(this.id).movable) {
                        if(!this.id) {
                            this.id = 'widget-no-id-' + i;
                        }
                        notSortable += '#' + this.id + ',';
                    }
                });
                return $('> *:not(' + notSortable + ')', settings.columns);
            })();

        $sortableItems.find(settings.handleSelector).mousedown(function (e) {
            $sortableItems.css({width:''});
            $(this).parent().css({
                width: $(this).parent().width() + 'px'
            });
        }).mouseup(function () {
            if(!$(this).parent().hasClass('dragging')) {
                $(this).parent().css({width:''});
            } else {
                $(settings.columns).sortable('disable');
            }
        });

        $(settings.columns).droppable({
            drop: function(event, ui) {
                $(this).removeClass("ui-state-highlight");
            },
            out: function(event, ui) {
                $(this).removeClass("ui-state-highlight");
            },
            over: function(event, ui) {
                $(this).addClass("ui-state-highlight");
            }
        });

        $(settings.columns).sortable({
            items: $sortableItems,
            connectWith: $(settings.columns),
            handle: settings.handleSelector,
            placeholder: 'widget-placeholder',
            forcePlaceholderSize: true,
            revert: 300,
            delay: 100,
            opacity: 0.8,
            containment: 'document',
            start: function (e,ui) {
                $(ui.helper).addClass('dragging');
            },
            stop: function (e,ui) {
                if (ui.item) {
                    var componentId = ui.item.parents(".draggable-container:first").data("id");
                    var cellBindId = ui.item.parents(".col:first").data("id");
                    
                    var targetComponentId = ui.item.data("id");
                    var position = ui.item.prevAll(".component").length;
                    
                    RPI.webService.call("/ws/component/", "update", {id : componentId, bind: cellBindId, data: {componentId: targetComponentId, position: position}}, 
                        function(data, response, sourceData) {
                        },
                        function(response, textStatus, errorThrown, isAuthenticationException, sourceData) {
                            alert("DEBUG: " + textStatus);
                        }
                    );
                }
                
                $(ui.item).css({width:''}).removeClass('dragging');
                $(settings.columns).sortable('enable');
            }
        });
    }
  
};

jQuery(document).on(
    "load.RPI.component.edit",
    function(e, component, option) {
        if (component.data("type") == "RPI\\Components\\Grid\\Component" && component.hasClass("component-editmode")) {
            RPI.component.drag.init();
            
            var deleteOption = jQuery("<div class=\"option-delete\"></div>")
                .click(
                    function(e) {
                        var targetComponent = jQuery(e.target).parents(".component:first");
                        
                        RPI.webService.call("/ws/component/", "delete", {id : component.data("id"), bind: targetComponent.data("id")}, 
                            function(data, response, sourceData) {
                                targetComponent.remove();
                            },
                            function(response, textStatus, errorThrown, isAuthenticationException, sourceData) {
                                alert("DEBUG: " + textStatus);
                            }
                        );
                    }
                );
            
            jQuery(component)
                .find(".component.component-draggable")
                .addClass("component-delete")
                .prepend(deleteOption)
        }
    }
);
