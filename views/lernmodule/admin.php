<form action="<?= PluginEngine::getLink($plugin, array(), "lernmodule/admin") ?>"
      method="post"
      class="default"
      data-dialog="reload-on-close">
    <fieldset>
        <legend><?= dgettext("lernmoduleplugin","Einstellungen der Veranstaltung") ?></legend>
        <label>
            <?= dgettext("lernmoduleplugin","Name des Reiters") ?>
            <input type="text" name="data[tabname]" value="<?= htmlReady($settings['tabname']) ?>" placeholder="<?= dgettext("lernmoduleplugin","Lernmodule") ?>">
        </label>
    </fieldset>

    <div data-dialog-button>
        <?= \Studip\Button::create(dgettext("lernmoduleplugin","Speichern")) ?>
    </div>
</form>
