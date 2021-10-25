jQuery(function () {
    jQuery(".admin_libraries .simple_view_success").on("click", function () {
        jQuery.ajax({
            "url": STUDIP.URLHelper.getURL("plugins.php/lernmoduleplugin/h5p/simple_view_success"),
            "type": "post",
            "data": {
                "lib_id": jQuery(this).closest("tr").data("lib_id"),
                "simple_view_success": jQuery(this).hasClass("notyet") ? 1 : 0
            }
        });
        jQuery(this).toggleClass('notyet');
        return false;
    });
    jQuery(".admin_libraries .allowed").on("click", function () {
        jQuery.ajax({
            "url": STUDIP.URLHelper.getURL("plugins.php/lernmoduleplugin/h5p/activate_library"),
            "type": "post",
            "data": {
                "lib_id": jQuery(this).closest("tr").data("lib_id"),
                "allowed": jQuery(this).hasClass("notyet") ? 1 : 0
            }
        });
        jQuery(this).toggleClass('notyet');
        return false;
    });
    jQuery(".admin_libraries .allowed_in_editor").on("click", function () {
        jQuery.ajax({
            "url": STUDIP.URLHelper.getURL("plugins.php/lernmoduleplugin/h5p/activate_library_in_editor"),
            "type": "post",
            "data": {
                "lib_id": jQuery(this).closest("tr").data("lib_id"),
                "allowed_in_editor": jQuery(this).hasClass("notyet") ? 1 : 0
            }
        });
        jQuery(this).toggleClass('notyet');
        return false;
    });
});
