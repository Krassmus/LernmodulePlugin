<table class="default admin_libraries">
    <thead>
        <tr>
            <th><?= dgettext("lernmoduleplugin","Bibliothek") ?></th>
            <th><?= dgettext("lernmoduleplugin","Version") ?></th>
            <th><?= dgettext("lernmoduleplugin","Benutzt von") ?></th>
            <th title="<?= dgettext("lernmoduleplugin", "Viele H5P-Module melden leider nicht an Stud.IP, wenn der Nutzer sie erfolgreich absolviert hat. Mit dieser Option an, speichert Stud.IP das selbstständig nach 30 Sekunden.")  ?>"><?= dgettext("lernmoduleplugin","Auto-Rückmeldung") ?></th>
            <th><?= dgettext("lernmoduleplugin","Aktiv") ?></th>
            <th class="actions"><?= dgettext("lernmoduleplugin","Aktion") ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($libs as $lib) : ?>
        <tr data-lib_id="<?= htmlReady($lib->getId()) ?>">
            <td><?= htmlReady($lib['name']) ?></td>
            <td title="<?= dgettext("lernmoduleplugin","Patch Version: ").$lib['patch_version'] ?>">
                <?= htmlReady($lib['major_version'].".".$lib['minor_version']) ?>
            </td>
            <td>
                <?= htmlReady($lib->countUsage()) ?>
            </td>
            <td>
                <a href="#" class="simple_view_success<?= !$lib['simple_view_success'] ? " notyet" : "" ?>">
                    <?= Icon::create("checkbox-unchecked")->asImg(20, array('class' => "text-bottom unchecked")) ?>
                    <?= Icon::create("checkbox-checked")->asImg(20, array('class' => "text-bottom checked")) ?>
                </a>
            </td>
            <td>
                <a href="#" class="allowed<?= !$lib['allowed'] ? " notyet" : "" ?>">
                    <?= Icon::create("checkbox-unchecked")->asImg(20, array('class' => "text-bottom unchecked")) ?>
                    <?= Icon::create("checkbox-checked")->asImg(20, array('class' => "text-bottom checked")) ?>
                </a>
            </td>
            <td class="actions">
                <a href="<?= PluginEngine::getLink($plugin, array('lib' => $lib['name']."-".$lib['major_version'].".".$lib['minor_version']), "h5p/export_library") ?>">
                    <?= Icon::create("download", "clickable")->asImg(20, ['class' => "text-bottom"]) ?>
                </a>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
</table>

<?

$actions = new ActionsWidget();
$actions->addLink(
    dgettext("lernmoduleplugin","Bibliotheken hinzufügen"),
    PluginEngine::getURL($plugin, array(), "h5p/add_library"),
    Icon::create("add", "clickable"),
    array('data-dialog' => 1)
);
$actions->addLink(
    dgettext("lernmoduleplugin","Alle Bibliotheken exportieren"),
    PluginEngine::getURL($plugin, array(), "h5p/export_libraries"),
    Icon::create("export", "clickable")
);
Sidebar::Get()->addWidget($actions);
