/*!
 * RPI.framework.view.document.style
 * version: 1.0.0
 * Copyright © 2013 RPI Limited. All rights reserved.
 */

/**
 * @type RPI.framework.view.document.style
 * @author Matt Dunn
 */

RPI._("framework.view.document").style = (function() {
//    jQuery(document).live(
//        "beforeadd.RPI.component.edit",
//        function(e, component, bindName, option) {
//            console.log(e.type + " - " + bindName + " - " + option)
////            e.preventDefault();
//        }
//    );
        
//    jQuery(document).live(
//        "beforedelete.RPI.component.edit",
//        function(e, component, bindName, option) {
//            console.log(e.type + " - " + bindName + " - " + option)
//            e.preventDefault();
//        }
//    );
        
//    jQuery(document).live(
//        "beforeload.RPI.component.edit",
//        function(e, component, option) {
//            console.log(e.type + " - " + option)
//        }
//    );
        
    jQuery(document).live(
        "load.RPI.component.edit",
        function(e, component, option) {
//            console.log(e.type + " - " + option)
            insertCaptions(jQuery(component).find(".document img[title]"));
        }
    );
        
    jQuery(document).live(
        "beforesave.RPI.component.edit",
        function(e, component, contentElement, bindName, option) {
//            console.log(e.type + " - " + bindName + " - " + option)
            removeCaptionsAndUpdateImageAttributes(contentElement.find("img[title]"));
//            e.preventDefault();
        }
    );
        
        
        
    function insertCaptions(elements) {
        jQuery(elements).each(
            function() {
                var element = jQuery(this);
                var captionClassName = "c-caption-right";
                if(element.hasClass("right")) {
                    captionClassName = "c-caption-right";
                } else if(element.hasClass("left")) {
                    captionClassName = "c-caption-left";
                }

                element.wrap("<span class=\"c-caption " + captionClassName + "\"/>");
                element.parent().append("<span>" + element.attr("title") + "</span>");
                element.removeClass("left");
                element.removeClass("right");
                element.parent().width(element.width());
            }
        );
    }
    
    function removeCaptionsAndUpdateImageAttributes(elements) {
        jQuery(elements).each(
            function() {
                var element = jQuery(this);
                var container = element.parents(".c-caption:first");
                var title = container.find("span").text();
                
                element.attr("title", title);
                container.replaceWith(element);
            }
        );
    }
})();
