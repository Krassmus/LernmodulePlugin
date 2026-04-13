<?
/**
 * @var ParticipantsController $controller
 * @var array $students
 * @var array $module
 */
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
            <th><?= dgettext("lernmoduleplugin","Name") ?></th>
            <th><?= dgettext("lernmoduleplugin","Absolvierte Lernmodule") ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($student_data as $student) : ?>
            <tr>
                <td>
                    <? $link = $controller->linkForStudent($student) ?>
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
                    <? if ($controller->canViewEvaluation($student['user_id']) && $student['solved'] > 0 && $student['solved'] >= count($module)) : ?>
                        <?= Icon::create("crown", "status-yellow")->asImg(20, array('class' => "text-bottom", 'title' => dgettext("lernmoduleplugin","Besser geht es nicht!"))) ?>
                    <? endif ?>
                    <?= $student['solved'] ?>
                </td>
            </tr>
        <? endforeach ?>
    </tbody>
</table>
