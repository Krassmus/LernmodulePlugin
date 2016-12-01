<? if (!count($module)) : ?>
    <?= MessageBox::info(_("Es sind noch keine Lernmodule vorhanden.")) ?>
<? endif ?>
<div id="moduleoverview">
    <? foreach ($module as $mod) : ?>
        <div class="module"<?= $mod['image'] ? " style=\"background-image: url('".$mod->getURL()."/".htmlReady($mod['image'])."');\"" : "" ?>>
            <div class="shadow">
                <a href="<?= PluginEngine::getLink($plugin, array(), ($mod['type'] === "html" ? "lernmodule" : $mod['type'])."/view/".$mod->getId()) ?>">
                    <?= Icon::create("learnmodule", "info_alt")->asImg(27, array('style' => "vertical-align: middle;")) ?>
                    <?= htmlReady($mod['name']) ?>
                </a>
                <? if ($GLOBALS['perm']->have_studip_perm("tutor", $mod['seminar_id'])) : ?>
                    <a href="<?= PluginEngine::getLink($plugin, array(), "lernmodule/edit/".$mod->getId()) ?>" data-dialog>
                        <?= Icon::create("edit", "info_alt")->asImg(20) ?>
                    </a>
                <? endif ?>
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