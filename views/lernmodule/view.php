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
        Icon::create("edit", "clickable")
    );
    $actions->addLink(
        _("Lernmodul herunterladen"),
        PluginEngine::getURL($plugin, array(), "lernmodule/download/".$mod->getId()),
        Icon::create("download", "clickable")
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

if ($course_connection['anonymous_attempts']) {
    $widget = new SidebarWidget();
    $widget->setTitle(_("Datenschutz"));
    $widget->addElement(
        new WidgetElement(
            Icon::create("visibility-visible", "info")->asImg(16, array('class' => "text-bottom"))
            ." "
            ._("Sie nehmen anonym teil.")
        )
    );
    Sidebar::Get()->addWidget($widget);
}