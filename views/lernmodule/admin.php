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

    <div data-dialog-button>
        <?= \Studip\Button::create(dgettext("lernmoduleplugin","Speichern")) ?>
    </div>
</form>
<?
Sidebar::Get()->setImage(Assets::image_path("sidebar/learnmodule-sidebar.png"));
