jQuery(function () {
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
});