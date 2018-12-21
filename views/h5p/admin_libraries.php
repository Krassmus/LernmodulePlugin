<table class="default admin_libraries">
    <thead>
        <tr>
            <th><?= _("Bibliothek") ?></th>
            <th><?= _("Version") ?></th>
            <th><?= _("Benutzt von") ?></th>
            <th><?= _("Erlaubt") ?></th>
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