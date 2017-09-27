<form action="<?= PluginEngine::getLink($plugin, array(), "editor/metadata") ?>"
      method="post"
      class="default"
      enctype="multipart/form-data">
    <label>
        <?= _("Name des Lernmoduls") ?>
        <input type="text" name="name" placeholder="<?= _("...")?>" required>
    </label>

    <label class="file-upload">
        <?= _("Logo-Bild") ?>
        <input type="file" name="logo" accept="image/*">
    </label>
    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern")) ?>
    </div>
</form>