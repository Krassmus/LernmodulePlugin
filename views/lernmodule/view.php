<input type="hidden" id="attempt_id" value="<?= $attempt->getId() ?>">

<? $template = $mod->getViewerTemplate($attempt, $game_attendence) ?>
<?= $template ? $template->render() : "" ?>

<script>
    STUDIP.Lernmodule.periodicalPushData = function () {
        return {
            'attempt_id': jQuery("#attempt_id").val(),
            'customData': STUDIP.Lernmodule.attemptCustomData
        };
    };
</script>

<?

if ($GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
    $actions = new ActionsWidget();
    $actions->addLink(
        _("Bearbeiten"),
        PluginEngine::getURL($plugin, array(), "lernmodule/edit/".$mod->getId()),
        version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
            ? Icon::create("edit", "clickable")
            : Assets::image_path("icons/black/16/blue/edit")

    );
    $actions->addLink(
        _("Lernmodul herunterladen"),
        PluginEngine::getURL($plugin, array(), "lernmodule/download/".$mod->getId()),
        version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
            ? Icon::create("download", "clickable")
            : Assets::image_path("icons/black/16/blue/download")
    );
    Sidebar::Get()->addWidget($actions);
}

$views = new ViewsWidget();
$views->addLink(
    $mod['name'],
    PluginEngine::getURL($plugin, array(), "lernmodule/view/".$mod->getId()),
    null,
    array()
)->setActive(true);
if ($course_connection['evaluation_for_students'] || $GLOBALS['perm']->have_studip_perm("tutor", $course_connection['seminar_id'])) {
    $views->addLink(
        _("Auswertung"),
        PluginEngine::getURL($plugin, array(), "lernmodule/evaluation/" . $mod->getId()),
        null,
        array()
    );
}

Sidebar::Get()->addWidget($views);