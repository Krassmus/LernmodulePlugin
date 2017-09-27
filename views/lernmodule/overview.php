<? if (!count($module)) : ?>
    <?= MessageBox::info(_("Es sind noch keine Lernmodule vorhanden.")) ?>
<? else : ?>
    <div id="moduleoverview">
        <? $already_displayed_mods = array() ?>
        <? do { ?>
            <? $last_mod_number = count($module) ?>
            <? foreach ($module as $key => $mod) : ?>
                <? $allowed = true;
                foreach ($mod->getDependencies($course_id) as $dependency) {
                    if (!in_array($dependency['depends_from_module_id'], $already_displayed_mods)) {
                        $allowed = false;
                        break;
                    }
                }

                if ($allowed) :

                    $active = true;
                    if (!$GLOBALS['perm']->have_studip_perm("tutor", $course_id)) {
                        foreach ($mod->getDependencies($course_id) as $dependency) {
                            if (!LernmodulAttempt::countBySql("module_id = ? AND user_id = ? AND successful = '1'", array(
                                $dependency['depends_from_module_id'],
                                $GLOBALS['user']->id
                            ))) {
                                $active = false;
                                break;
                            }
                        }
                    }
                    ?>
                    <? if ($active) : ?>
                        <? $coursemodule = LernmodulCourse::findOneBySQL("seminar_id = ? AND module_id = ?", array($course_id, $mod->getId())) ?>
                        <? if (!$coursemodule || !$coursemodule['starttime'] || ($coursemodule['starttime'] <= time()) || $mod->isWritable()) : ?>
                            <div class="module"<?= $mod['image'] ? " style=\"background-image: url('".(!$mod['url'] ? $mod->getDataURL()."/" : "").htmlReady($mod['image'])."');\"" : "" ?>>
                                <? if (!$mod->isWritable()) : ?>
                                    <? if (LernmodulAttempt::findOneBySQL("successful = '1' AND module_id = ? AND user_id = ?", array($mod->getId(), $GLOBALS['user']->id))) : ?>
                                        <div class="crown">
                                            <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                                                ? Icon::create("crown", "status-yellow")->asImg(20, array('title' => _("Erfolgreich abgeschlossen"), 'class' => "text-bottom"))
                                                : Assets::img("icons/yellow/20/crown", array('title' => _("Erfolgreich abgeschlossen"), 'class' => "text-bottom"))
                                            ?>
                                            </div>
                                    <? endif ?>
                                <? endif ?>
                                <div class="shadow">
                                    <a href="<?= PluginEngine::getLink($plugin, array(), "lernmodule/view/".$mod->getId()) ?>">
                                        <? if (!$mod->isWritable()) : ?>
                                            <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                                                ? Icon::create("learnmodule", "info_alt")->asImg(20, array('style' => "vertical-align: middle;"))
                                                : Assets::img("icons/white/20/learnmodule", array('style' => "vertical-align: middle;"))
                                            ?>
                                        <? endif ?>
                                        <?= htmlReady($mod['name']) ?>
                                    </a>
                                    <? if ($mod->isWritable()) : ?>
                                        <a href="<?= PluginEngine::getLink($plugin, array(), "lernmodule/edit/".$mod->getId()) ?>">
                                            <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                                                ? Icon::create("edit", "info_alt")->asImg(20, array('class' => "text-bottom"))
                                                : Assets::img("icons/white/20/edit", array('class' => "text-bottom"))
                                            ?>
                                        </a>
                                    <? endif ?>
                                </div>
                            </div>
                        <? else : ?>
                        <div class="module" style="opacity: 0.5;<?= $mod['image'] ? " background-image: url('".(!$mod['url'] ? $mod->getDataURL()."/" : "").htmlReady($mod['image'])."');" : "" ?>" title="<?= sprintf(_("Dieses Modul wird erst ab %s Uhr verfügbar sein."), date("d.m.Y H:i", $coursemodule['starttime'])) ?>">
                            <div class="shadow" style="max-height: 108px; height: 108px;">
                                <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                                    ? Icon::create("date", "info_alt")->asImg(80, array('style' => "vertical-align: middle; margin-left: auto; margin-right: auto;"))
                                    : Assets::img("icons/white/80/date", array('style' => "vertical-align: middle; margin-left: auto; margin-right: auto;"))
                                ?>
                            </div>
                        </div>
                        <? endif ?>
                    <? else : ?>
                        <div class="module" style="opacity: 0.3;<?= $mod['image'] ? " background-image: url('".(!$mod['url'] ? $mod->getDataURL()."/" : "").htmlReady($mod['image'])."');" : "" ?>" title="<?= _("Aktivieren Sie dieses Modul dadurch, dass Sie die anderen Module durcharbeiten.") ?>">
                            <div class="shadow" style="max-height: 108px; height: 108px;">
                                <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                                    ? Icon::create("question-circle", "info_alt")->asImg(80, array('style' => "vertical-align: middle; margin-left: auto; margin-right: auto;"))
                                    : Assets::img("icons/white/80/question-circle", array('style' => "vertical-align: middle; margin-left: auto; margin-right: auto;"))
                                ?>
                            </div>
                        </div>
                    <? endif ?>
                    <? $already_displayed_mods[] = $mod->getId() ?>
                    <? unset($module[$key]) ?>
                <? endif ?>
            <? endforeach ?>
        <? } while (count($module) < $last_mod_number) ?>
        <? foreach ($module as $mod) : ?>
            <div class="module"<?= $mod['image'] ? " style=\"background-image: url('".(!$mod['url'] ? $mod->getDataURL()."/" : "")."/".htmlReady($mod['image'])."');\"" : "" ?>>
                <div class="shadow">
                    <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                        ? Icon::create("exclaim-circle", "attention")->asImg(40, array('style' => "vertical-align: middle;", 'title' => _("Dieses Modul hat Kreis-Abhängigkeiten und kann von neuen Teilnehmern nie erreicht werden.")))
                        : Assets::img("icons/red/40/exclaim-circle", array('style' => "vertical-align: middle;", 'title' => _("Dieses Modul hat Kreis-Abhängigkeiten und kann von neuen Teilnehmern nie erreicht werden.")))
                    ?>
                    <? if ($mod->isWritable()) : ?>
                        <a href="<?= PluginEngine::getLink($plugin, array(), "lernmodule/edit/".$mod->getId()) ?>" data-dialog>
                            <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                                ? Icon::create("edit", "info_alt")->asImg(16)
                                : Assets::img("icons/white/16/edit")
                            ?>
                        </a>
                    <? endif ?>
                    <a href="<?= PluginEngine::getLink($plugin, array(), ($mod['type'] === "html" ? "lernmodule" : $mod['type'])."/view/".$mod->getId()) ?>">
                        <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                            ? Icon::create("learnmodule", "info_alt")->asImg(27, array('style' => "vertical-align: middle;"))
                            : Assets::img("icons/white/20/learnmodule", array('style' => "vertical-align: middle;"))
                        ?>
                        <?= htmlReady($mod['name']) ?>
                    </a>
                </div>
            </div>
        <? endforeach ?>
    </div>
<? endif ?>

<?
Sidebar::Get()->setImage(Assets::image_path("sidebar/learnmodule-sidebar.png"));

$actions = new ActionsWidget();
if ($GLOBALS['perm']->have_studip_perm("tutor", $course_id)) {
    $actions->addLink(
        _("Lernmodul hinzufügen"),
        PluginEngine::getURL($plugin, array(), "lernmodule/edit"),
        version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
            ? Icon::create("learnmodule+add", "clickable")
            : Assets::image_path("icons/black/16/blue/learnmodule")
    );
}
Sidebar::Get()->addWidget($actions);