<table class="default admin_libraries">
    <thead>
        <tr>
            <th><?= dgettext("lernmoduleplugin","Bibliothek") ?></th>
            <th><?= dgettext("lernmoduleplugin","Version") ?></th>
            <th><?= dgettext("lernmoduleplugin","Benutzt von") ?></th>
            <th><?= dgettext("lernmoduleplugin","Erlaubt") ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($libs as $lib) : ?>
        <tr data-lib_id="<?= htmlReady($lib->getId()) ?>">
            <td><?= htmlReady($lib['name']) ?></td>
            <td>
                <?= htmlReady($lib['major_version'].".".$lib['minor_version']) ?>
            </td>
            <td>
                <?= htmlReady($lib->countUsage()) ?>
            </td>
            <td>
                <a href="#" class="allowed<?= !$lib['allowed'] ? " notyet" : "" ?>">
                    <?= Icon::create("checkbox-unchecked")->asImg(20, array('class' => "text-bottom unchecked")) ?>
                    <?= Icon::create("checkbox-checked")->asImg(20, array('class' => "text-bottom checked")) ?>
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
    dgettext("lernmoduleplugin","Bibliotheken exportieren"),
    PluginEngine::getURL($plugin, array(), "h5p/export_libraries"),
    Icon::create("export", "clickable")
);
Sidebar::Get()->addWidget($actions);
