<? if (!count($module)) : ?>
    <?= MessageBox::info(_("Es sind noch keine Lernmodule vorhanden.")) ?>
<? else : ?>
    <div id="moduleoverview">
        <? $already_displayed_mods = array() ?>
        <? do { ?>
            <? $last_mod_number = count($module) ?>
            <? foreach ($module as $key => $mod) : ?>
                <? $allowed = true;
                foreach ($mod->getDependencies($_SESSION['SessionSeminar']) as $dependency) {
                    if (!in_array($dependency['depends_from_module_id'], $already_displayed_mods)) {
                        $allowed = false;
                        break;
                    }
                }

                if ($allowed) :

                    $active = true;
                    if (!$GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) {
                        foreach ($mod->getDependencies($_SESSION['SessionSeminar']) as $dependency) {
                            if (!LernmodulVersuch::countBySql("module_id = ? AND user_id = ? AND successful = '1'", array(
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
                        <div class="module"<?= $mod['image'] ? " style=\"background-image: url('".$mod->getURL()."/".htmlReady($mod['image'])."');\"" : "" ?>>
                            <div class="shadow">
                                <? if ($mod->isWritable()) : ?>
                                    <a href="<?= PluginEngine::getLink($plugin, array(), "lernmodule/edit/".$mod->getId()) ?>" data-dialog>
                                        <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                                            ? Icon::create("edit", "info_alt")->asImg(20)
                                            : Assets::img("icons/white/20/edit")
                                        ?>
                                    </a>
                                <? else : ?>
                                    <? if (LernmodulVersuch::findOneBySQL("successful = '1' AND module_id = ? AND user_id = ?", array($mod->getId(), $GLOBALS['user']->id))) : ?>
                                        <span style="background: white; border-radius: 20px; display: inline-block; height: 30px; width: 30px; text-align: center; line-height: 27px;">
                                        <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                                            ? Icon::create("crown", "status-yellow")->asImg(20, array('title' => _("Erfolgreich abgeschlossen"), 'class' => "text-bottom"))
                                            : Assets::img("icons/yellow/20/crown", array('title' => _("Erfolgreich abgeschlossen"), 'class' => "text-bottom"))
                                        ?>
                                        </span>
                                    <? endif ?>
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
                    <? else : ?>
                        <div class="module" style="opacity: 0.3;<?= $mod['image'] ? " background-image: url('".$mod->getURL()."/".htmlReady($mod['image'])."');" : "" ?>" title="<?= _("Aktivieren Sie dieses Modul, indem Sie die anderen Module durcharbeiten.") ?>">
                            <div class="shadow" style="text-align: center;">
                                <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                                    ? Icon::create("question-circle", "info_alt")->asImg(80, array('style' => "vertical-align: middle;"))
                                    : Assets::img("icons/white/80/question-circle", array('style' => "vertical-align: middle;"))
                                ?>
                            </div>
                        </div>
                    <? endif ?>
                    <? $already_displayed_mods[] = $mod->getId() ?>
                    <? unset($module[$key]) ?>
                <? else : ?>

                <? endif ?>
            <? endforeach ?>
        <? } while (count($module) < $last_mod_number) ?>
        <? foreach ($module as $mod) : ?>
            <div class="module"<?= $mod['image'] ? " style=\"background-image: url('".$mod->getURL()."/".htmlReady($mod['image'])."');\"" : "" ?>>
                <div class="shadow">
                    <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                        ? Icon::create("exclaim-circle", "attention")->asImg(40, array('style' => "vertical-align: middle;", 'title' => _("Dieses Modul hat Kreis-Abh�ngigkeiten und kann von neuen Teilnehmern nie erreicht werden.")))
                        : Assets::img("icons/red/40/exclaim-circle", array('style' => "vertical-align: middle;", 'title' => _("Dieses Modul hat Kreis-Abh�ngigkeiten und kann von neuen Teilnehmern nie erreicht werden.")))
                    ?>
                    <? if ($mod->isWritable()) : ?>
                        <a href="<?= PluginEngine::getLink($plugin, array(), "lernmodule/edit/".$mod->getId()) ?>" data-dialog>
                            <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                                ? Icon::create("edit", "info_alt")->asImg(20)
                                : Assets::img("icons/white/20/edit")
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
if ($GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) {
    $actions->addLink(
        _("Lernmodul hinzuf�gen"),
        PluginEngine::getURL($plugin, array(), "lernmodule/edit"),
        version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
            ? Icon::create("learnmodule+add", "info")
            : Assets::image_path("icons/black/16/add/learnmodule"),
        array('data-dialog' => 1)
    );
}
Sidebar::Get()->addWidget($actions);