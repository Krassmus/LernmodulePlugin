<? if (!count($module) && !$GLOBALS['perm']->have_studip_perm("tutor", $course_id)) : ?>
    <?= MessageBox::info(dgettext("lernmoduleplugin","Es sind noch keine Lernmodule vorhanden.")) ?>
<? endif ?>

<div id="blockcontainer">
<? foreach ($blocks as $block) : ?>
    <div class="block" data-block_id="<?= htmlReady($block->getId()) ?>">
        <? if (trim($block['title'])) : ?>
            <h2>
                <? if ($GLOBALS['perm']->have_studip_perm("tutor", $course_id) && (count($blocks) > 1)) : ?>
                    <img src="<?= URLHelper::getLink("plugins_packages/RasmusFuhse/LernmodulePlugin/assets/mover_blue.svg") ?>"
                         width="20"
                         class="blockmover text-bottom">
                <? endif ?>
                <?= htmlReady($block['title']) ?>
            </h2>
        <? endif ?>
        <? if (trim($block['infotext'])) : ?>
            <div class="lernmodule_infotext"><?= formatReady($block['infotext']) ?></div>
        <? endif ?>

        <div class="moduleoverview<?= $settings['singlecolumn'] ? ' singlecolumn' : "" ?>"
             data-block_id="<?= htmlReady($block->getId()) ?>">
            <? if ($GLOBALS['perm']->have_studip_perm("tutor", $course_id)) : ?>
                <div class="module"
                     style="background-image: url('<?= $plugin->getPluginURL() ?>/assets/background_add.png');"
                     title="<?= dgettext("lernmoduleplugin","Erstellen Sie ein neues Lernmodul entweder im Editor, durch ein gezipptes HTML-Dokument, mit einem PDF oder eine Webseite im Internet.") ?>">
                    <a href="<?= PluginEngine::getLink($plugin, array('block_id' => $block->getId()), "lernmodule/add") ?>"
                       data-dialog="size=auto"
                       class="shadow"
                       style="max-height: 108px; height: 108px;">
                        <?= Icon::create("add-circle", "info_alt")->asImg(80, array('style' => "vertical-align: middle; margin-left: auto; margin-right: auto;")) ?>
                    </a>
                </div>
            <? endif ?>

            <? foreach ($block->coursemodules as $coursemodule) : ?>
                <?= $this->render_partial("lernmodule/_lernmodul_kachel", [
                    'lernmodul' => $coursemodule->module,
                    'coursemodule' => $coursemodule
                ]) ?>
            <? endforeach ?>
        </div>
    </div>
<? endforeach ?>
</div>

<? if ($GLOBALS['perm']->have_studip_perm("tutor", $course_id)) : ?>
    <script>
        jQuery(function () {
            jQuery(".module.droppable").on("dragover", function (event) {
                jQuery(this).addClass("dragover");
                event.preventDefault();
            });
            jQuery(".module.droppable").on("dragleave", function (event) {
                jQuery(this).removeClass("dragover");
                event.preventDefault();
            });
            jQuery(".module.droppable").on("drop", STUDIP.Lernmodule.uploadNewLogo);
        });
    </script>
<? endif ?>

<?
Sidebar::Get()->setImage(Assets::image_path("sidebar/learnmodule-sidebar.png"));

$actions = new ActionsWidget();
if ($GLOBALS['perm']->have_studip_perm("tutor", $course_id)) {
    $actions->addLink(
        dgettext("lernmoduleplugin","Bereich konfigurieren"),
        PluginEngine::getURL($plugin, array(), "lernmodule/admin"),
        Icon::create("admin", "clickable"),
        array('data-dialog' => 1)
    );
    $actions->addLink(
        dgettext("lernmoduleplugin","Lernmodul hinzufügen"),
        PluginEngine::getURL($plugin, array('block_id' => $blocks[count($blocks) - 1]->getId()), "lernmodule/add"),
        Icon::create("add", "clickable"),
        array('data-dialog' => "size=auto")
    );
}
Sidebar::Get()->addWidget($actions);
