<div class="file_select_possibilities">

    <a href="<?= PluginEngine::getLink($plugin, array(), "h5peditor/edit") ?>">
        <?= Icon::create("learnmodule", "clickable")->asImg(50) ?>
        <?= _("Editor") ?>
    </a>

    <a href="<?= PluginEngine::getLink($plugin, array('type' => "url"), "lernmodule/edit") ?>">
        <?= Icon::create("globe", "clickable")->asImg(50) ?>
        <?= _("Webadresse") ?>
    </a>

    <a href="<?= PluginEngine::getLink($plugin, array('type' => "upload"), "lernmodule/edit") ?>">
        <?= Icon::create("upload", "clickable")->asImg(50) ?>
        <?= _("Hochladen") ?>
    </a>

    <? if (false) : ?>
        <a href="<?= PluginEngine::getLink($plugin, array('type' => "upload"), "lernmodule/edit") ?>">
            <?= Icon::create("service", "clickable")->asImg(50) ?>
            <?= _("OER-Campus") ?>
        </a>
    <? endif ?>

</div>

<?
Sidebar::Get()->setImage(Assets::image_path("sidebar/learnmodule-sidebar.png"));