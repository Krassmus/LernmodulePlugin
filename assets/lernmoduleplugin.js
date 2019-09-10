jQuery(function () {
    jQuery("#moduleoverview").on("click", ".module", function (event) {
        if (jQuery(this).data("url")) {
            if (!jQuery(event.target).is("a, a *")) {
                location.href = jQuery(this).data("url");
                return false;
            }
        }
    });
});

STUDIP.Lernmodule = {
    selectImage: function () {
        jQuery('#image_preview').css('background-image', 'url(' + encodeURI(jQuery(this).find(':selected').data('url')) + ')');
    },
    selectNextImage: function () {
        var selected = jQuery('#select_image option:selected');
        selected.removeAttr('selected');

        var options = jQuery('#select_image option:not([disabled], .empty)');
        var position = null;
        if (options.length === 0) {
            jQuery('#select_image').trigger("change");
            return;
        }
        options.each(function (index) {
            if (jQuery(this).is(selected)) {
                position = index;
            }
        });
        if ((position >= options.length - 1) || position === null) {
            jQuery(options[0]).attr('selected', 'selected');
        } else {
            jQuery(options[position + 1]).attr('selected', 'selected');
        }

        jQuery('#select_image').trigger("change");
    },
    selectPreviousImage: function () {
        var selected = jQuery('#select_image option:selected');
        selected.removeAttr('selected');

        var options = jQuery('#select_image option:not([disabled], .empty)');
        var position = null;
        if (options.length === 0) {
            jQuery('#select_image').trigger("change");
            return;
        }
        options.each(function (index) {
            if (jQuery(this).is(selected)) {
                position = index;
            }
        });
        if (!position) {
            jQuery(options[options.length - 1]).attr('selected', 'selected');
        } else {
            jQuery(options[position - 1]).attr('selected', 'selected');
        }

        jQuery('#select_image').trigger("change");
    }
};