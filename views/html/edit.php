<? if (LernmodulePlugin::mayEditSandbox()) : ?>
    <label>
        <input type="hidden" name="module[sandbox]" value="0">
        <input type="checkbox" name="module[sandbox]" value="1"<?= $module['sandbox'] ? " checked" : "" ?> onChange="jQuery('#edit_end_file,#edit_passing_ids').toggle(!this.checked);">
        <?= dgettext("lernmoduleplugin","Im abgesicherten Modus abspielen") ?>
    </label>
    <label id="edit_passing_ids"<?= $module['sandbox'] ? ' style="display: none;"' : "" ?>>
        <input type="hidden" name="module[customdata][pass_studip_ids]" value="0">
        <input type="checkbox" name="module[customdata][pass_studip_ids]" value="1"<?= $module['customdata']['pass_studip_ids'] ? " checked" : "" ?>>
        <?= dgettext("lernmoduleplugin","Aktuelle Veranstaltungs-ID ins Lernmodul übertragen") ?>
    </label>
<? endif ?>

<label>
    <?= dgettext("lernmoduleplugin","Startdatei (.html)") ?>
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
            <?= dgettext("lernmoduleplugin","Enddatei (.html)") ?>
            <select name="module[customdata][end_file]">
                <option value=""><?= dgettext("lernmoduleplugin","Keine") ?></option>
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
