<? if (count($pointclasses)) : ?>
    <table class="default nohover">
        <thead>
            <th><?= _("Auswertung") ?></th>
            <th><?= _("Minimalwert") ?></th>
            <th><?= _("Höchstwert") ?></th>
        </thead>
        <tbody>
        <? $i = 1 ?>
        <? foreach ($pointclasses as $pointclass) : ?>
            <tr>
                <?
                $minimum = null;
                foreach ($attempts as $attempt) {
                    if ($attempt['successful'] && isset($attempt['customdata']['points'][$pointclass])) {
                        $minimum = $minimum === null
                            ? $attempt['customdata']['points'][$pointclass]
                            : min($minimum, $attempt['customdata']['points'][$pointclass]);
                    }
                }
                $maximum = null;
                foreach ($attempts as $attempt) {
                    if ($attempt['successful'] && isset($attempt['customdata']['points'][$pointclass])) {
                        $maximum = max($maximum, $attempt['customdata']['points'][$pointclass]);
                    }
                }
                ?>
                <td<?= $maximum ? ' style="border-bottom: none;"' : '' ?>>
                    <strong><?= htmlReady($pointclass) ?></strong>
                </td>
                <td<?= $maximum ? ' style="border-bottom: none;"' : '' ?>>
                    <?= htmlReady($minimum ?: 0) ?>
                </td>
                <td<?= $maximum ? ' style="border-bottom: none;"' : '' ?>>
                    <?= htmlReady($maximum ?: 0) ?>
                </td>
                <? $i++ ?>
            </tr>
            <? if ($maximum) : ?>
                <tr>
                    <td colspan="3">
                        <div id="line<?= $i ?>"></div>
                        <script>
                            <?
                            $segements = 9;
                            $factor = ($maximum - $minimum) / $segements;
                            $range = range(0, $segements + 1);
                            $result = array();
                            foreach ($range as $key => $value) {
                                $range[$key] = ceil($minimum + ($value * $factor));
                                $result[$key] = 0;
                                foreach ($attempts as $attempt) {
                                    if (($attempt['customdata']['points'][$pointclass] <= $minimum + ($value * $factor) + $factor * 0.5)
                                        && ($attempt['customdata']['points'][$pointclass] > $minimum + ($value * $factor) - $factor * 0.5)) {
                                        if ($attempt['successful']) {
                                            $result[$key]++;
                                        }
                                    }
                                }
                            }
                            ?>
                            jQuery(function () {
                                var data = {
                                    // A labels array that can contain any sort of values
                                    labels: <?= json_encode($range) ?>,
                                    // Our series array that contains series objects or in this case series data arrays
                                    series: [
                                        <?= json_encode($result) ?>
                                    ]
                                };

                                var options = {
                                    width: '100%',
                                    height: '120px',
                                    // Remove this configuration to see that chart rendered with cardinal spline interpolation
                                    // Sometimes, on large jumps in data values, it's better to use simple smoothing.
                                    lineSmooth: Chartist.Interpolation.none(),
                                    fullWidth: true,
                                    //low: 0,
                                    classNames: {
                                        series: 'ct-series-dominant'
                                    }
                                };
                                new Chartist.Line('#line<?= $i ?>', data, options);
                            });
                        </script>
                    </td>
                </tr>
            <? endif ?>
        <? endforeach ?>
        </tbody>
    </table>
<? endif ?>