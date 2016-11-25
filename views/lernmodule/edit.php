<form action="<?= PluginEngine::getLink($plugin, array(), "lernmodule/edit/".$module->getId()) ?>"
      method="post"
      class="default"
      enctype="multipart/form-data">

    <label class="file-upload">
        <input type="file" name="modulefile" accept="application/zip">
        <?= _("Lernmodul auswählen (ZIP)") ?>
    </label>

    <label>
        <?= _("Name des Moduls") ?>
        <input type="text" name="module[name]" required value="<?= htmlReady($module['name']) ?>">
    </label>

    <label>
        <?= _("Startdatei (.html)") ?>
        <? if ($module->isNew()) : ?>
        <input type="text" name="module[start_file]" value="<?= htmlReady($module['start_file']) ?>">
        <? else : ?>
            <select name="module[start_file]">
                <? foreach (@scandir($module->getPath()) as $file) : ?>
                <? if (!is_dir($module->getPath()."/".$file)) : ?>
                <option value="<?= htmlReady($file) ?>"<?= $file === $module['start_file'] ? " selected" : "" ?>><?= htmlReady($file) ?></option>
                <? endif ?>
                <? endforeach ?>
            </select>
        <? endif ?>
    </label>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern")) ?>
    </div>

</form>