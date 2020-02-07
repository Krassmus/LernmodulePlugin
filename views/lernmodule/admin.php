<form action="<?= PluginEngine::getLink($plugin, array(), "lernmodule/admin") ?>"
      method="post"
      class="default"
      data-dialog="reload-on-close">
    <fieldset>
        <legend><?= dgettext("lernmoduleplugin","Einstellungen der Veranstaltung") ?></legend>
        <label>
            <?= dgettext("lernmoduleplugin","Name des Reiters") ?>
            <input type="text"
                   name="data[tabname]"
                   value="<?= htmlReady($settings['tabname']) ?>"
                   placeholder="<?= Config::get()->LERNMODUL_GLOBAL_NAME ?: dgettext("lernmoduleplugin","Lernmodule") ?>">
        </label>

        <label>
            <input type="hidden" name="data[singlecolumn]" value="0">
            <input type="checkbox" name="data[singlecolumn]" value="1"<?= $settings['singlecolumn'] ? " checked" : "" ?>>
            <?= _("Einspaltiges Layout") ?>
        </label>
    </fieldset>


    <fieldset>
        <legend>
            <?= _("Darstellungsblöcke") ?>
        </legend>
        <? foreach ($blocks as $block) : ?>
            <div class="lernmodule_infotext">
                <input type="hidden" name="blocks_order[]" value="<?= htmlReady($block->getId()) ?>">
                <label>
                    <?= _("Titel des Blocks") ?>
                    <input type="text" name="block[<?= htmlReady($block->getId()) ?>][title]" value="<?= htmlReady($block['title']) ?>">
                </label>
                <label>
                    <?= _("Informationstext") ?>
                    <textarea class="add_toolbar wysiwyg" name="block[<?= htmlReady($block->getId()) ?>][infotext]"><?= formatReady($block['infotext']) ?></textarea>
                </label>

                <a href="<?= PluginEngine::getLink($plugin, array('delete_block' => $block->getId()), "lernmodule/admin") ?>" data-dialog data-confirm="<?= _("Wirklich den ganzen Block und die Inhalte löschen?") ?>">
                    <?= Icon::create("trash", "clickable")->asImg(16, ['class' => "text-bottom"]) ?>
                    <?= _("Block und Inhalte darin löschen") ?>
                </a>
            </div>
        <? endforeach ?>
    </fieldset>


    <div data-dialog-button>
        <?= \Studip\Button::create(dgettext("lernmoduleplugin","Speichern")) ?>
        <?= \Studip\Button::create(dgettext("lernmoduleplugin","Block hinzufügen"), "add_block") ?>
    </div>
</form>
<?
Sidebar::Get()->setImage(Assets::image_path("sidebar/learnmodule-sidebar.png"));
