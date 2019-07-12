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