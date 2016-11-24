<table class="default">
    <thead>
        <tr>
            <th><?= _("Name") ?></th>
            <th><?= _("Hochgeladen") ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($module as $mod) : ?>
            <tr>
                <td><?= htmlReady($mod['name']) ?></td>
                <td><?= htmlReady(get_fullname($mod['user_id'])) ?></td>
            </tr>
        <? endforeach ?>
    </tbody>
</table>

<?

$actions = new ActionsWidget();
$actions->addLink(
    _("Lernmodul hinzufügen"),
    PluginEngine::getURL($plugin, array(), "lernmodule/add"),
    Icon::create("lernmodule+add", "info")
);

Sidebar::Get()->addWidget($actions);