<form class="default studip_form">
    <?
        $successful = array(
            'count' => 0,
            'sum' => 0,
            'variance' => 0,
            'standarddeviation' => 0
        );
        $unsuccessful = array(
            'count' => 0,
            'sum' => 0,
            'variance' => 0,
            'standarddeviation' => 0
        );
        $attempts = LernmodulAttempt::findbyCourseAndModule(Context::get()->id, $module->getId());
        foreach ($attempts as $attempt) {
            if ($attempt['successful']) {
                $successful['count']++;
                $successful['sum'] += ($attempt['chdate'] - $attempt['mkdate']);
            } else {
                $unsuccessful['count']++;
                $unsuccessful['sum'] += ($attempt['chdate'] - $attempt['mkdate']);
            }
        }
        $successful['average'] = $successful['count'] ? ($successful['sum'] / $successful['count']) : 0;
        $unsuccessful['average'] = $unsuccessful['count'] ? ($unsuccessful['sum'] / $unsuccessful['count']) : 0;
        foreach ($attempts as $attempt) {
            if ($attempt['successful']) {
                $successful['variance'] += (pow(($attempt['chdate'] - $attempt['mkdate']) - $successful['average'], 2) / $successful['count']);
            } else {
                $unsuccessful['variance'] += (pow(($attempt['chdate'] - $attempt['mkdate']) - $unsuccessful['average'], 2) / $unsuccessful['count']);
            }
        }
        $successful['standarddeviation'] = sqrt($successful['variance']);
        $unsuccessful['standarddeviation'] = sqrt($unsuccessful['variance']);
        $max = max(round(max($successful['average'] + $successful['standarddeviation'], $unsuccessful['average'] + $unsuccessful['standarddeviation']) / 10) * 10, 60);
        $segements = 9;
        $factor = $max / $segements;
        $range = range(0, $segements + 1);
        $result = array();
        foreach ($range as $key => $value) {
            $range[$key] = $value * $factor > 120
                ? round($value * $factor / 60, 1)."m"
                : ceil($value * $factor)."s";
            $successful['result'][$key] = 0;
            $unsuccessful['result'][$key] = 0;
            foreach ($attempts as $attempt) {
                if ((($attempt['chdate'] - $attempt['mkdate']) <= ($value * $factor) + $factor * 0.5)
                        && (($attempt['chdate'] - $attempt['mkdate']) > ($value * $factor) - $factor * 0.5)) {
                    if ($attempt['successful']) {
                        $successful['result'][$key]++;
                    } else {
                        $unsuccessful['result'][$key]++;
                    }
                }
            }
        }
        ?>
        <? if ($successful['count'] + $unsuccessful['count'] > 0) : ?>
        <fieldset>
            <legend><?= dgettext("lernmoduleplugin","Auswertung") ?></legend>

                <h2><?= dgettext("lernmoduleplugin","Dauer des Lernmoduls") ?></h2>

                <div id="timeline"></div>
                <script>
                    jQuery(function () {
                        var data = {
                            // A labels array that can contain any sort of values
                            labels: <?= json_encode($range) ?>,
                            // Our series array that contains series objects or in this case series data arrays
                            series: [
                                <?= json_encode($unsuccessful['result']) ?>,
                                <?= json_encode($successful['result']) ?>
                            ]
                        };

                        var options = {
                            width: '100%',
                            height: '200px',
                            // Remove this configuration to see that chart rendered with cardinal spline interpolation
                            // Sometimes, on large jumps in data values, it's better to use simple smoothing.
                            lineSmooth: Chartist.Interpolation.none(),
                            fullWidth: true,
                            chartPadding: {
                                right: 20
                            },
                            low: 0
                        };
                        new Chartist.Line('#timeline', data, options);
                    });
                </script>
                <p><?= dgettext("lernmoduleplugin","Zeit der Durchläufe in Sekunden bzw. Minuten. Blau sind die erfolgreichen Durchläufe, orange die nicht erfolgreichen.") ?></p>

            <? if (is_a($module, "CustomLernmodul")) : ?>
                <? $template = $module->getEvaluationTemplate(Context::get()->id) ?>
                <? if ($template) : ?>
                    <?= $template->render() ?>
                <? endif ?>
            <? endif ?>
        </fieldset>
    <? endif ?>

    <? if ($data && count($data)) : ?>
        <table class="default" id="resulttable">
            <thead>
                <tr>
                    <th width="50px"></th>
                    <th><?= dgettext("lernmoduleplugin","Name") ?></th>
                    <th><?= dgettext("lernmoduleplugin","Dauer") ?></th>
                    <th><?= dgettext("lernmoduleplugin","Datum") ?></th>
                    <? foreach ($resultrows as $rowname) : ?>
                        <th><?= htmlReady($rowname) ?></th>
                    <? endforeach ?>
                </tr>
            </thead>
            <tbody>
            <? foreach ($data as $line) : ?>
                <tr>
                    <td>
                        <div style="width: 50px; height: 50px; background-image: url('<?= Avatar::getAvatar($line['studip_user_id'])->getURL(Avatar::MEDIUM) ?>'); background-size: 100% auto; background-position: center center;"></div>
                    </td>
                    <td><?= htmlReady($line['studip_user_id'] ? get_fullname($line['studip_user_id']) : dgettext("lernmoduleplugin","Anonym")) ?></td>
                    <td data-timestamp="<?= htmlReady($line['studip_duration']) ?>"><?
                        if ($line['studip_duration'] < 180) {
                            echo sprintf(dgettext("lernmoduleplugin","%s Sekunden"), $line['studip_duration']);
                        } elseif ($line['studip_duration'] < 60 * 120) {
                            echo sprintf(dgettext("lernmoduleplugin","%s Minuten"), round($line['studip_duration'] / 60));
                        } else {
                            echo sprintf(dgettext("lernmoduleplugin","%s Stunden"), round($line['studip_duration'] / 3600));
                        }
                        ?></td>
                    <td>
                        <?= date("j.n.Y G:i", $line['studip_mkdate'])." ".dgettext("lernmoduleplugin","Uhr") ?>
                    </td>
                    <? foreach ($resultrows as $rowname) : ?>
                        <td><?= htmlReady($line[$rowname]) ?></td>
                    <? endforeach ?>
                </tr>
            <? endforeach ?>
            </tbody>
        </table>
        <script>
            jQuery(function () {
                jQuery("#resulttable").tablesorter({
                    textExtraction: function (node) {
                        var node = jQuery(node);
                        return String(node.data('timestamp') || node.text()).trim();
                    },
                    cssAsc: 'sortasc',
                    cssDesc: 'sortdesc'<? if (count($resultrows > 2)) : ?>,
                    sortList : [[3,0]]
                    <? endif ?>
                });
            });
        </script>
    <? endif ?>

</form>

<?
Sidebar::Get()->setImage(Assets::image_path("sidebar/learnmodule-sidebar.png"));
$actions = new ActionsWidget();
if ($GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
    $actions->addLink(
        dgettext("lernmoduleplugin","Lernmodul herunterladen"),
        PluginEngine::getURL($plugin, array(), "lernmodule/download/" . $module->getId()),
        Icon::create("download", "clickable")
    );
}
if ($GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
    $actions->addLink(
        dgettext("lernmoduleplugin","Bearbeiten"),
        PluginEngine::getURL($plugin, array(), "lernmodule/edit/".$module->getId()),
        Icon::create("edit", "clickable")
    );
}

Sidebar::Get()->addWidget($actions);

$views = new ViewsWidget();
$views->addLink(
    $module['name'],
    PluginEngine::getURL($plugin, array(), "lernmodule/view/".$module->getId()),
    null,
    array()
);
$views->addLink(
    dgettext("lernmoduleplugin","Auswertung"),
    PluginEngine::getURL($plugin, array(), "lernmodule/evaluation/" . $module->getId()),
    null,
    array()
)->setActive(true);


Sidebar::Get()->addWidget($views);
