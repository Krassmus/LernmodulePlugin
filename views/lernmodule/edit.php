<form action="<?= PluginEngine::getLink($plugin, array(), "lernmodule/edit/".$module->getId()) ?>"
      method="post"
      class="default"
      enctype="multipart/form-data">

    <label class="file-upload">
        <input type="file" name="modulefile" accept=".zip,.h5p">
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

    <? if (!$module->isNew()) : ?>
        <label>
            <?= _("Bild auswählen") ?>
            <select name="module[image]" onChange="jQuery('#image_preview').css('background-image', 'url(' + jQuery('#image_preview').data('url_base') + '/' + this.value + ')'); ">
                <? foreach ($module->scanForImages() as $image) : ?>
                    <option value="<?= htmlReady($image) ?>"><?= htmlReady($image) ?></option>
                <? endforeach ?>
            </select>
        </label>
        <div id="image_preview" data-url_base="<?= htmlReady($module->getURL()) ?>" style="margin: 10px; border: white solid 4px; box-shadow: rgba(0,0,0,0.3) 0px 0px 7px; width: 300px; height: 100px; max-width: 300px; max-height: 100px; background-size: 100% auto; background-repeat: no-repeat; background-position: center center;<?= $module['image'] ? " background-image: url('".htmlReady($module->getURL()."/".$module['image'])."');" : "" ?>"></div>
    <? endif ?>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern")) ?>
    </div>

</form>