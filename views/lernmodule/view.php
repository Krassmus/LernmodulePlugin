<? if (trim($course_connection['infotext'])) : ?>
    <div class="lernmodule_infotext"><?= formatReady($course_connection['infotext']) ?></div>
<? endif ?>

<input type="hidden" id="attempt_id" value="<?= $attempt->getId() ?>">

<? $template = $mod->getViewerTemplate($attempt, $game_attendence) ?>
<? if ($template) : ?>
    <? $template->set_attribute('plugin', $plugin); ?>
    <? $template->set_attribute(
        'javascript_global_variables',
        [
            'module' => [
                'customdata' => json_decode($mod['customdata']),
                'module_id' => $mod['id'],
                'name' => $mod['name']
            ],
            'attemptId' => $attempt->getId(),
            'saveRoute' => $controller->url_for('vuejseditor/save')
        ]
    ); ?>
    <?= $template->render() ?>
<? endif ?>

<script>
    STUDIP.Lernmodule.periodicalPushData = function () {
        return {
            'attempt_id': jQuery("#attempt_id").val(),
            'customData': STUDIP.Lernmodule.attemptCustomData
        };
    };
</script>

<?
Sidebar::Get()->setImage(Assets::image_path("sidebar/learnmodule-sidebar.png"));
if ($GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
    $actions = Sidebar::Get()->getWidget("actions");
    $add = false;
    if (!$actions) {
        $actions = new ActionsWidget();
        $add = true;
    }
    $actions->addLink(
        dgettext("lernmoduleplugin","Abspieloptionen bearbeiten"),
        PluginEngine::getURL($plugin, array(), "lernmodule/edit/".$mod->getId()),
        Icon::create("edit", "clickable")

    );
    $actions->addLink(
        dgettext("lernmoduleplugin","Lernmodul in Veranstaltung verschieben oder kopieren"),
        PluginEngine::getURL($plugin, array(), "lernmodule/move/" . $mod->getId()),
        Icon::create("seminar+move_up", "clickable"),
        array("data-dialog" => 1)
    );
    if ($mod->getDownloadURL()) {
        $actions->addLink(
            dgettext("lernmoduleplugin","Lernmodul herunterladen"),
            $mod->getDownloadURL(),
            Icon::create("download", "clickable")
        );
        if (class_exists("LernMarktplatz")) {
            $actions->addLink(
                dgettext("lernmoduleplugin","Auf Lernmarktplatz veröffentlichen"),
                PluginEngine::getURL($plugin, array(), "lernmodule/publish/" . $mod->getId()),
                Icon::create("service", "clickable"),
                array("data-dialog" => 1)
            );
        }
    }
    if ($add) {
        Sidebar::Get()->addWidget($actions);
    }
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
        dgettext("lernmoduleplugin","Auswertung"),
        PluginEngine::getURL($plugin, array(), "lernmodule/evaluation/" . $mod->getId()),
        null,
        array()
    );
}

Sidebar::Get()->addWidget($views);
