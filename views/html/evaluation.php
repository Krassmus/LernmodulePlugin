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
                    <td></td>
                    <td colspan="2">
                        <div id="line<?= $i ?>"></div>
                        <script>
                            jQuery(function () {
                                var data = {
                                    // A labels array that can contain any sort of values
                                    labels: [ <?= htmlReady($minimum ?: 0) ?>, <?= htmlReady($maximum ?: 0) ?> ],
                                    // Our series array that contains series objects or in this case series data arrays
                                    series: [
                                        [3, 5]
                                    ]
                                };

                                var options = {
                                    width: '100%',
                                    height: '120px',
                                    // Remove this configuration to see that chart rendered with cardinal spline interpolation
                                    // Sometimes, on large jumps in data values, it's better to use simple smoothing.
                                    lineSmooth: Chartist.Interpolation.none(),
                                    fullWidth: true,
                                    chartPadding: {
                                        right: 20
                                    },
                                    low: 0
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