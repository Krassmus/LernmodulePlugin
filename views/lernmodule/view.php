<input type="hidden" name="attempt_id" value="<?= $attempt->getId() ?>">

<? $template = $mod->getViewerTemplate() ?>
<?= $template ? $template->render() : "" ?>

<script>
    var end_file_found = false;
    window.setInterval(function () {
        if (!end_file_found) {
            //search for iframe-page
            var page = document.getElementById("lernmodule_iframe").contentWindow.location.href;
            var end_file = "<?= htmlReady($mod['end_file']) ?>";
            page = page.split("?")[0];
            if (page.indexOf(end_file) !== -1) {
                end_file_found = true;
                jQuery.post(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>");
            }
        }
    }, 500);
</script>