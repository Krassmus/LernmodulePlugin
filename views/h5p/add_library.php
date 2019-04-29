<form action="<?= PluginEngine::getLink($plugin, array(), "h5p/add_library") ?>"
      method="post"
      enctype="multipart/form-data"
      class="default">
    <fieldset>
        <legend>
            <?= dgettext("lernmoduleplugin","Bibliotheken hochladen") ?>
        </legend>

        <label class="file-upload">
            <input type="file" name="file" accept=".zip,.h5p">
            <?= sprintf(dgettext("lernmoduleplugin","H5P-Lernmodul oder ZIP mit Bibliotheken auswählen (maximal %s MB)"), floor(min(LernmodulePlugin::bytesFromPHPIniValue(ini_get('post_max_size')), LernmodulePlugin::bytesFromPHPIniValue(ini_get('upload_max_filesize'))) / 1024 / 1024)) ?>
        </label>

        <label>
            <input type="checkbox" name="activate" value="1" checked>
            <?= dgettext("lernmoduleplugin","Gefundene Bibliotheken gleich aktivieren") ?>
        </label>

        <label>
            <?= dgettext("lernmoduleplugin","Bestehende Bibliotheken überschreiben") ?>
            <select name="overwrite">
                <option value="always"><?= dgettext("lernmoduleplugin","Immer") ?></option>
                <option value="nonactivated"><?= dgettext("lernmoduleplugin","Nur die nicht aktivierten") ?></option>
                <option value="patch"><?= dgettext("lernmoduleplugin","Nur, wenn die Patch-Version höher ist") ?></option>
            </select>
        </label>
    </fieldset>
    <div data-dialog-button>
        <?= \Studip\Button::create(dgettext("lernmoduleplugin","Speichern")) ?>
    </div>
</form>
