<div class="file_select_possibilities">
    <? if (StudipVersion::newerThan("4.5.99")) : ?>
    <div>
    <? endif ?>

    <? if ($h5plibs > 0) : ?>
        <a href="<?= PluginEngine::getLink($plugin, array('block_id' => Request::option("block_id")), "h5peditor/edit") ?>">
            <?= Icon::create("learnmodule", "clickable")->asImg(50) ?>
            <?= _("Editor") ?>
        </a>
    <? endif ?>

    <a href="<?= PluginEngine::getLink($plugin, array('type' => "url", 'block_id' => Request::option("block_id")), "lernmodule/edit") ?>">
        <?= Icon::create("globe", "clickable")->asImg(50) ?>
        <?= _("Webadresse") ?>
    </a>

    <a href="<?= PluginEngine::getLink($plugin, array('type' => "upload", 'block_id' => Request::option("block_id")), "lernmodule/edit") ?>">
        <?= Icon::create("upload", "clickable")->asImg(50) ?>
        <?= _("Hochladen") ?>
    </a>

    <? if (false) : ?>
        <a href="<?= PluginEngine::getLink($plugin, array('type' => "upload", 'block_id' => Request::option("block_id")), "lernmodule/edit") ?>">
            <?= Icon::create("service", "clickable")->asImg(50) ?>
            <?= _("OER-Campus") ?>
        </a>
    <? endif ?>

    <? if (StudipVersion::newerThan("4.5.99")) : ?>
    </div>
    <? endif ?>

</div>

<?
Sidebar::Get()->setImage(Assets::image_path("sidebar/learnmodule-sidebar.png"));
