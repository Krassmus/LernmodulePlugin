<table class="default">
    <caption>
        <?= _("Auswertung von ").htmlReady(get_fullname($user_id)) ?>
    </caption>
    <thead>
        <tr>
            <th><?= _("Modul") ?></th>
            <th><?= _("Beginn") ?></th>
            <th><?= _("Dauer") ?></th>
            <th><?= _("Erfolg") ?></th>
        </tr>
    </thead>
    <tbody>
<? foreach ($attempts as $attempt) : ?>
    <tr>
        <td>
            <?= htmlReady($attempt->modul['name']) ?>
        </td>
        <td>
            <?= date("/r", $attempt['mkdate']) ?>
        </td>
        <td>
            <?=
                ($attempt['chdate'] - $attempt['mkdate'])." Sekunden"
            ?>
        </td>
        <td>
            <?= Icon::create("checkbox-".($attempt['successful'] ? "" : "un")."checked", "info")->asImg(20) ?>
        </td>
    </tr>
<? endforeach ?>
    </tbody>
</table>
