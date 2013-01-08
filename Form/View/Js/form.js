/*!
 * RPI.form
 * version: 1.0.1
 * Copyright © 2012 RPI Limited. All rights reserved.
 */

/**
 * @type RPI.form
 * @author Matt Dunn
 */

// TODO: review
RPI.form = (function() {
	function init() {
		function textareaUpdateCount(element) {
            var o = jQuery(element);
            var maxLength = o.attr("maxlength");
			var remainingCount = maxLength - element.value.length;
            if(!element.textareaCount) {
                element.textareaCount = jQuery.create("div", {"class" : "c-form-ta-count"});
                o.parent().addClass("c-form-ta");
                o.parent().append(element.textareaCount);
            }
			element.textareaCount.html(remainingCount);
		}

        jQuery("textarea[maxlength]").live("keydown keyup paste cut copy",
            function(e) {
                textareaUpdateCount(this);
            }
        );

        jQuery("fieldset .desc *").live("focus",
            function() {
                jQuery(this).parents(".desc:first").addClass("f");
            }
        );

        jQuery("fieldset .desc *").live("blur",
            function() {
                jQuery(this).parents(".desc:first").removeClass("f");
            }
        );
	}

	init();

	return {
	};
})();



