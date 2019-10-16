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
    },
    uploadNewLogo: function (module_id) {
        var data = new FormData();
        if (module_id.hasOwnProperty('originalEvent')) {
            //if drag & drop applied
            var event = module_id;
            module_id = jQuery(this).data("module_id");
            event.preventDefault();
            if (event.originalEvent.dataTransfer.items) {
                // Use DataTransferItemList interface to access the file(s)
                if (event.originalEvent.dataTransfer.items[0].kind === 'file') {
                    var file = event.originalEvent.dataTransfer.items[0].getAsFile();
                    data.append("logo", file, file.name.normalize());
                }
            } else {
                // Use DataTransfer interface to access the file(s)
                data.append("logo", event.originalEvent.dataTransfer.files[0], event.originalEvent.dataTransfer.files[0].name.normalize());
            }
            var dragged = true;
            var module = this;
        } else {
            //if
            var file = this.files[0];
            if (file.size == 0) {
                return;
            }
            data.append("logo", file, file.name.normalize());
            var dragged = false;
        }

        jQuery.ajax({
            'url': STUDIP.URLHelper.getURL("plugins.php/lernmoduleplugin/lernmodule/add_logo/" + module_id),
            'data': data,
            'cache': false,
            'contentType': false,
            'processData': false,
            'type': 'POST',
            'success': function (url) {
                if (!dragged) {
                    location.reload();
                } else {
                    jQuery(module).css("background-image", "url(" + url + ")");
                    jQuery(module).removeClass("dragover");
                }
            },
        });
    }
};