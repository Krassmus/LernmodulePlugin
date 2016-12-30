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
        <td data-timestamp="<?= htmlReady($attempt['mkdate']) ?>">
            <?= date("j.n.Y - G:i", $attempt['mkdate'])." "._("Uhr") ?>
        </td>
        <td>
            <?
                $duration = $attempt['chdate'] - $attempt['mkdate'];
                if ($duration <= 90) {
                    echo $duration." "._("Sekunden");
                } elseif ($duration < 60 * 90) {
                    echo floor($duration / 60)." "._("Minuten");
                } else {
                    echo floor($duration / (60 * 60))." "._("Stunden");
                }
            ?>
        </td>
        <td data-timestamp="<?= htmlReady($attempt['successful']) ?>">
            <?= Icon::create("checkbox-".($attempt['successful'] ? "" : "un")."checked", "info")->asImg(20) ?>
        </td>
    </tr>
<? endforeach ?>
    </tbody>
</table>
