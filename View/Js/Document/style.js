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
    jQuery(document).ready(
        function() {
            jQuery(".document img[title]").each(
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
    );
})();
