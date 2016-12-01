<? if (!count($module)) : ?>
    <?= MessageBox::info(_("Es sind noch keine Lernmodule vorhanden.")) ?>
<? endif ?>
<div id="moduleoverview">
    <? foreach ($module as $mod) : ?>
        <div class="module"<?= $mod['image'] ? " style=\"background-image: url('".$mod->getURL()."/".htmlReady($mod['image'])."');\"" : "" ?>>
            <div class="shadow">
                <? if ($GLOBALS['perm']->have_studip_perm("tutor", $mod['seminar_id'])) : ?>
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

<?
Sidebar::Get()->setImage(Assets::image_path("sidebar/learnmodule-sidebar.png"));

$actions = new ActionsWidget();
if ($GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) {
    $actions->addLink(
        _("Lernmodul hinzufügen"),
        PluginEngine::getURL($plugin, array(), "lernmodule/edit"),
        Icon::create("learnmodule+add", "info"),
        array('data-dialog' => 1)
    );
}
Sidebar::Get()->addWidget($actions);