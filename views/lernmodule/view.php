<input type="hidden" id="attempt_id" value="<?= $attempt->getId() ?>">

<? $template = $mod->getViewerTemplate($attempt) ?>
<?= $template ? $template->render() : "" ?>

<script>
    STUDIP.Lernmodule.periodicalPushData = function () {
        return {
            'attempt_id': jQuery("#attempt_id").val(),
            'customData': STUDIP.Lernmodule.attemptCustomData
        };
    };
</script>