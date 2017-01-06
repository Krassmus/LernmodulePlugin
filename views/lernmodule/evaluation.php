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
        $attempts = LernmodulVersuch::findbyCourseAndModule($_SESSION['SessionSeminar'], $module->getId());
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
            <legend><?= _("Auswertung") ?></legend>

                <h2><?= _("Dauer des Lernmoduls") ?></h2>

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
                <p><?= _("Zeit der Durchläufe in Sekunden bzw. Minuten. Blau sind die erfolgreichen Durchläufe, orange die nicht erfolgreichen.") ?></p>

            <? if (is_a($module, "CustomLernmodul")) : ?>
                <? $template = $module->getEvaluationTemplate($_SESSION['SessionSeminar']) ?>
                <? if ($template) : ?>
                    <?= $template->render() ?>
                <? endif ?>
            <? endif ?>
        </fieldset>
    <? endif ?>

</form>

<?

$actions = new ActionsWidget();
$actions->addLink(
    _("Lernmodul herunterladen"),
    PluginEngine::getURL($plugin, array(), "lernmodule/download/".$module->getId()),
    Icon::create("download", "info")
);

Sidebar::Get()->addWidget($actions);

$views = new ViewsWidget();
$views->addLink(
    $module['name'],
    PluginEngine::getURL($plugin, array(), "lernmodule/view/".$module->getId()),
    null,
    array()
);
$views->addLink(
    _("Auswertung"),
    PluginEngine::getURL($plugin, array(), "lernmodule/evaluation/".$module->getId()),
    null,
    array()
)->setActive(true);

Sidebar::Get()->addWidget($views);