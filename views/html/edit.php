<? if (LernmodulePlugin::mayEditSandbox() && !$module['url']) : ?>
    <label>
        <input type="hidden" name="module[sandbox]" value="0">
        <input type="checkbox" name="module[sandbox]" value="1"<?= $module['sandbox'] ? " checked" : "" ?> onChange="jQuery('#edit_end_file').toggle(!this.checked);">
        <?= _("Im abgesicherten Modus abspielen") ?>
    </label>
<? endif ?>

<label>
    <?= _("Startdatei (.html)") ?>
    <? if ($module->isNew()) : ?>
        <input type="text" name="module[customdata][start_file]" value="<?= htmlReady($module['customdata']['start_file']) ?>">
    <? else : ?>
        <select name="module[customdata][start_file]">
            <? $files = $module->scanForFiletypes(array("html", "htm", "pdf")) ?>
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
        <label id="edit_end_file"<?= $module['sandbox'] ? ' style="display: none;"' : "" ?>>
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
