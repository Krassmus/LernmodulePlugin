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

<?

if ($GLOBALS['perm']->have_studip_perm("tutor", $_SESSION["SessionSeminar"])) {
    $actions = new ActionsWidget();
    $actions->addLink(
        _("Bearbeiten"),
        PluginEngine::getURL($plugin, array(), "lernmodule/edit/".$mod->getId()),
        Icon::create("edit", "info")
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
$views->addLink(
    _("Auswertung"),
    PluginEngine::getURL($plugin, array(), "lernmodule/evaluation/".$mod->getId()),
    null,
    array()
);

Sidebar::Get()->addWidget($views);