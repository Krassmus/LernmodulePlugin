<label>
    <?= _("Startdatei (.html)") ?>
    <? if ($module->isNew()) : ?>
        <input type="text" name="module[customdata][start_file]" value="<?= htmlReady($module['start_file']) ?>">
    <? else : ?>
        <select name="module[customdata][start_file]">
            <? $files = $module->scanForFiletypes(array("html", "htm")) ?>
            <? foreach ($files as $file) : ?>
                <? if (!is_dir($module->getPath()."/".$file)) : ?>
                    <option value="<?= htmlReady($file) ?>"<?= $file === $module['customdata']['start_file'] ? " selected" : "" ?>><?= htmlReady($file) ?></option>
                <? endif ?>
            <? endforeach ?>
        </select>
    <? endif ?>
</label>

<? if (LernmodulePlugin::mayEditSandbox()) : ?>
    <? if (!$module->isNew()) : ?>
        <label>
            <?= _("Enddatei (.html)") ?>
            <select name="module[customdata][end_file]">
                <option value=""><?= _("Keine") ?></option>
                <? $files = $module->scanForFiletypes(array("html", "htm")) ?>
                <? foreach ($files as $file) : ?>
                    <? if (!is_dir($module->getPath()."/".$file)) : ?>
                        <option value="<?= htmlReady($file) ?>"<?= $file === $module['customdata']['end_file'] ? " selected" : "" ?>><?= htmlReady($file) ?></option>
                    <? endif ?>
                <? endforeach ?>
            </select>
        </label>
    <? endif ?>
<? endif ?>
