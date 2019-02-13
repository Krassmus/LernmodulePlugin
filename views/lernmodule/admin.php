<form action="<?= PluginEngine::getLink($plugin, array(), "lernmodule/admin") ?>"
      method="post"
      class="default"
      data-dialog="reload-on-close">
    <fieldset>
        <legend><?= _("Einstellungen der Veranstaltung") ?></legend>
        <label>
            <?= _("Name des Reiters") ?>
            <input type="text" name="data[tabname]" value="<?= htmlReady($settings['tabname']) ?>" placeholder="<?= _("Lernmodule") ?>">
        </label>
    </fieldset>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern")) ?>
    </div>
</form>