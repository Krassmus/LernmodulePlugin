<?
$student_data = array();
foreach ($students as $student) {
    $data = $student->toArray();
    $solved = 0;
    foreach ($module as $mod) {
        if (LernmodulAttempt::countBySql("user_id = ? AND module_id = ? AND successful = '1'", array($student['user_id'], $mod->getId()))) {
            $solved++;
        }
    }
    $data['solved'] = $solved;
    $student_data[] = $data;
}
usort($student_data, function ($data1, $data2) {
    if ($data1['solved'] == $data2['solved']) {
        return 0;
    }
    return ($data1['solved'] < $data2['solved']) ? 1 : -1;
});
?>

<table class="default">
    <thead>
        <tr>
            <th></th>
            <th><?= _("Name") ?></th>
            <th><?= _("Absolvierte Lernmodule") ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($student_data as $student) : ?>
            <tr>
                <td>
                    <? $link = Config::get()->LERNMODUL_PARTICIPANT_EVALUATION && $GLOBALS['perm']->have_studip_perm(Config::get()->LERNMODUL_PARTICIPANT_EVALUATION, Context::get()->id)
                        ? PluginEngine::getLink($plugin, array(), "participants/evaluation/".$student['username'])
                        : URLHelper::getLink("dispatch.php/profile", array('username' => $student['username'])) ?>
                    <a href="<?= $link ?>">
                    <div style="width: 50px; height: 50px; background-size: 100% auto; background-image: url('<?= Avatar::getAvatar($student['user_id'])->getURL(Avatar::MEDIUM) ?>'); background-position:  center center; background-repeat: no-repeat;"></div>
                    </a>
                </td>
                <td>
                    <a href="<?= $link ?>">
                    <?= htmlReady($student['vorname']." ".$student['nachname']) ?>
                    </a>
                </td>
                <td>
                    <? if ($student['solved'] > 0 && $student['solved'] >= count($module)) : ?>
                    <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                        ? Icon::create("crown", "status-yellow")->asImg(20, array('class' => "text-bottom", 'title' => _("Besser geht es nicht!")))
                        : Assets::img("icons/yellow/20/crown", array('class' => "text-bottom", 'title' => _("Besser geht es nicht!")))
                    ?>
                    <? endif ?>
                    <?= $student['solved'] ?>
                </td>
            </tr>
        <? endforeach ?>
    </tbody>
</table>