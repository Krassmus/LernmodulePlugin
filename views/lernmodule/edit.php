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

    <? if (count($lernmodule)) : ?>
        <?= _("Abhängig von") ?>
        <ul class="clean">
            <? $dependencies = array_map(function ($dep) { return $dep['depends_from_module_id']; }, $module->getDependencies($_SESSION['SessionSeminar'])) ?>
            <? foreach ($lernmodule as $lernmodul) : ?>
                <li>
                    <label>
                        <input type="checkbox" name="dependencies[]" value="<?= htmlReady($lernmodul->getId()) ?>"<?= in_array($lernmodul->getId(), $dependencies) ? " checked" : "" ?>>
                        <?= htmlReady($lernmodul['name']) ?>
                    </label>
                </li>
            <? endforeach ?>
        </ul>
        <select name="module[depends_from_module_id]">
            <option value=""><?= _("keine") ?></option>

        </select>
    <? endif ?>

    <? if ($module['type'] === "html" && !$module->isNew()) : ?>
        <label>
            <?= _("Startdatei (.html)") ?>
            <? if ($module->isNew()) : ?>
            <input type="text" name="module[start_file]" value="<?= htmlReady($module['start_file']) ?>">
            <? else : ?>
                <select name="module[start_file]">
                    <? $files = $module->scanForFiletypes(array("html", "htm")) ?>
                    <? foreach ($files as $file) : ?>
                        <? if (!is_dir($module->getPath()."/".$file)) : ?>
                        <option value="<?= htmlReady($file) ?>"<?= $file === $module['start_file'] ? " selected" : "" ?>><?= htmlReady($file) ?></option>
                        <? endif ?>
                    <? endforeach ?>
                </select>
            <? endif ?>
        </label>
    <? endif ?>

    <? if (LernmodulePlugin::mayEditSandbox()) : ?>
        <label>
            <input type="checkbox" name="module[sandbox]" value="1"<?= $module['sandbox'] ? " checked" : "" ?>>
            <?= _("Im abgesicherten Modus abspielen") ?>
        </label>

        <label>
            <?= _("Enddatei (.html)") ?>
            <? if ($module->isNew()) : ?>
                <input type="text" name="module[start_file]" value="<?= htmlReady($module['start_file']) ?>">
            <? else : ?>
                <select name="module[end_file]">
                    <? $files = $module->scanForFiletypes(array("html", "htm")) ?>
                    <? foreach ($files as $file) : ?>
                        <? if (!is_dir($module->getPath()."/".$file)) : ?>
                            <option value="<?= htmlReady($file) ?>"<?= $file === $module['end_file'] ? " selected" : "" ?>><?= htmlReady($file) ?></option>
                        <? endif ?>
                    <? endforeach ?>
                </select>
            <? endif ?>
        </label>
    <? endif ?>

    <? if (!$module->isNew()) : ?>
        <? $images = $module->scanForImages() ?>
        <? if (count($images)) : ?>
            <label>
                <?= _("Bild auswählen") ?>
                <select id="select_image" name="module[image]" onChange="jQuery('#image_preview').css('background-image', 'url(' + jQuery('#image_preview').data('url_base') + '/' + this.value + ')'); ">
                    <? foreach ($images as $image) : ?>
                        <option value="<?= htmlReady($image) ?>"<?= $module['image'] === $image ? " selected" : "" ?>><?= htmlReady($image) ?></option>
                    <? endforeach ?>
                </select>
            </label>
            <div>
                <a href="" onClick="jQuery('#select_image option:selected').removeAttr('selected').prev().attr('selected', 'selected').trigger('change'); return false;">
                    <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                            ? Icon::create("arr_1left", "clickable")->asImg(20, array('style' => "vertical-align: middle;"))
                            : Assets::img("icons/blue/20/arr_1left", array('style' => "vertical-align: middle;"))
                    ?>
                </a>
                <div id="image_preview" data-url_base="<?= htmlReady($module->getURL()) ?>" style="display: inline-block; vertical-align: middle; margin: 10px; border: white solid 4px; box-shadow: rgba(0,0,0,0.3) 0px 0px 7px; width: 300px; height: 100px; max-width: 300px; max-height: 100px; background-size: 100% auto; background-repeat: no-repeat; background-position: center center;<?= $module['image'] ? " background-image: url('".htmlReady($module->getURL()."/".$module['image'])."');" : "" ?>"></div>
                <a href="" onClick="jQuery('#select_image option:selected').removeAttr('selected').next().attr('selected', 'selected').trigger('change'); return false;">
                    <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                        ? Icon::create("arr_1right", "clickable")->asImg(20, array('style' => "vertical-align: middle;"))
                        : Assets::img("icons/blue/20/arr_1right", array('style' => "vertical-align: middle;"))
                    ?>
                </a>
            </div>
        <? endif ?>
    <? endif ?>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern")) ?>
        <?= \Studip\Button::create(_("Löschen"), "delete", array(
            'formaction' => PluginEngine::getLink($plugin, array(), "lernmodule/delete/".$module->getId()),
            'onClick' => "return window.confirm('"._("Wirklich löschen?")."');"
        )) ?>
    </div>

</form>
