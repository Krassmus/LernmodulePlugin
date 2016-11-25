<table class="default">
    <thead>
        <tr>
            <th><?= _("Name") ?></th>
            <th><?= _("Hochgeladen") ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <? if (count($module)) : ?>
        <? foreach ($module as $mod) : ?>
            <tr>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array(), "lernmodule/view/".$mod->getId()) ?>">
                        <?= htmlReady($mod['name']) ?>
                    </a>
                </td>
                <td><?= htmlReady(get_fullname($mod['user_id'])) ?></td>
                <td>
                    <? if ($GLOBALS['perm']->have_studip_perm("tutor", $mod['seminar_id'])) : ?>
                        <a href="<?= PluginEngine::getLink($plugin, array(), "lernmodule/edit/".$mod->getId()) ?>" data-dialog>
                            <?= Icon::create("edit", "clickable")->asImg(20) ?>
                        </a>
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
        <? else : ?>
            <tr>
                <td colspan="100"><?= _("Noch keine Lernmodule vorhanden") ?></td>
            </tr>
        <? endif ?>
    </tbody>
</table>

<?
Sidebar::Get()->setImage(Assets::image_path("sidebar/learnmodule-sidebar.png"));

$actions = new ActionsWidget();
$actions->addLink(
    _("Lernmodul hinzufügen"),
    PluginEngine::getURL($plugin, array(), "lernmodule/edit"),
    Icon::create("learnmodule+add", "info"),
    array('data-dialog' => 1)
);

Sidebar::Get()->addWidget($actions);