<table class="default">
    <caption>
        <?= dgettext("lernmoduleplugin","Auswertung von ").htmlReady(get_fullname($user_id)) ?>
    </caption>
    <thead>
        <tr>
            <th><?= dgettext("lernmoduleplugin","Modul") ?></th>
            <th><?= dgettext("lernmoduleplugin","Beginn") ?></th>
            <th><?= dgettext("lernmoduleplugin","Dauer") ?></th>
            <th><?= dgettext("lernmoduleplugin","Erfolg") ?></th>
        </tr>
    </thead>
    <tbody>
    <? if (count($attempts)) : ?>
        <? foreach ($attempts as $attempt) : ?>
            <tr>
                <td>
                    <?= htmlReady($attempt->modul['name']) ?>
                </td>
                <td data-timestamp="<?= htmlReady($attempt['mkdate']) ?>">
                    <?= date("j.n.Y G:i", $attempt['mkdate'])." ".dgettext("lernmoduleplugin","Uhr") ?>
                </td>
                <td>
                    <?
                        $duration = $attempt['chdate'] - $attempt['mkdate'];
                        if ($duration <= 90) {
                            echo $duration." ".dgettext("lernmoduleplugin","Sekunden");
                        } elseif ($duration < 60 * 90) {
                            echo floor($duration / 60)." ".dgettext("lernmoduleplugin","Minuten");
                        } else {
                            echo floor($duration / (60 * 60))." ".dgettext("lernmoduleplugin","Stunden");
                        }
                    ?>
                </td>
                <td data-timestamp="<?= htmlReady($attempt['successful']) ?>">
                    <?= Icon::create("checkbox-".($attempt['successful'] ? "" : "un")."checked", "info")->asImg(20) ?>
                </td>
            </tr>
        <? endforeach ?>
    <? else : ?>
        <tr>
            <td colspan="100" style="text-align: center;">
                <?= dgettext("lernmoduleplugin","Noch keine Module absolviert.") ?>
            </td>
        </tr>
    <? endif ?>
    </tbody>
</table>
