<form action="<?= PluginEngine::getLink($plugin, array(), "lernmodule/edit/".$module->getId()) ?>"
      method="post"
      class="default studip_form"
      enctype="multipart/form-data">

    <fieldset>
        <legend><?= _("Lernmodul hochladen und bearbeiten") ?></legend>

        <label class="file-upload">
            <input type="file" name="modulefile" accept=".zip,.h5p" onChange="if (!jQuery('#modulename').val()) { var name = this.files[0].name; jQuery('#modulename').val(name.lastIndexOf('.') === -1 ? name : name.substr(0, name.lastIndexOf('.'))); }">
            <?= sprintf(_("Lernmodul auswählen (ZIP, maximal %s MB)"), floor(min(LernmodulePlugin::bytesFromPHPIniValue(ini_get('post_max_size')), LernmodulePlugin::bytesFromPHPIniValue(ini_get('upload_max_filesize'))) / 1024 / 1024)) ?>
        </label>

        <label>
            <?= _("Name des Moduls") ?>
            <input type="text" id="modulename" name="module[name]" required value="<?= htmlReady($module['name']) ?>">
        </label>

        <? if (count($lernmodule)) : ?>
            <div style="margin-top: 15px;">
                <?= _("Abhängig von") ?>
                <ul class="clean">
                    <? $dependencies = array_map(function ($dep) { return $dep['depends_from_module_id']; }, $module->getDependencies($_SESSION['SessionSeminar'])) ?>
                    <? foreach ($lernmodule as $lernmodul) : ?>
                        <li>
                            <label style="font-size: 0.9em;">
                                <input type="checkbox" name="dependencies[]" value="<?= htmlReady($lernmodul->getId()) ?>"<?= in_array($lernmodul->getId(), $dependencies) ? " checked" : "" ?>>
                                <?= htmlReady($lernmodul['name']) ?>
                            </label>
                        </li>
                    <? endforeach ?>
                </ul>
            </div>
        <? endif ?>
    </fieldset>

    <fieldset>
        <legend>
            <?= _("Abspieloptionen") ?>
        </legend>

        <? if (!$module->isNew()) : ?>
            <? $images = $module->scanForImages() ?>
            <? if (count($images)) : ?>
                <label>
                    <?= _("Bild auswählen") ?>
                    <select id="select_image" name="module[image]" onChange="jQuery('#image_preview').css('background-image', 'url(' + jQuery('#image_preview').data('url_base') + '/' + this.value + ')'); ">
                        <option value=""><?= _("Keines") ?></option>
                        <? foreach ($images as $image) : ?>
                            <option value="<?= htmlReady($image) ?>"<?= $module['image'] === $image ? " selected" : "" ?>><?= htmlReady($image) ?></option>
                        <? endforeach ?>
                    </select>
                </label>
                <div>
                    <a href="" onClick="jQuery('#select_image option:selected').removeAttr('selected').prev().attr('selected', 'selected').trigger('change'); return false;">
                        <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                                ? Icon::create("arr_1left", "clickable")->asImg(20, array('style' => "vertical-align: middle;"))
                                : Assets::img("icons/blue/20/arr_1left", array('style' => "vertical-align: middle;"))
                        ?>
                    </a>
                    <div id="image_preview" data-url_base="<?= htmlReady($module->getURL()) ?>" style="display: inline-block; vertical-align: middle; margin: 10px; border: white solid 4px; box-shadow: rgba(0,0,0,0.3) 0px 0px 7px; width: 300px; height: 100px; max-width: 300px; max-height: 100px; background-size: 100% auto; background-repeat: no-repeat; background-position: center center;<?= $module['image'] ? " background-image: url('".htmlReady($module->getURL()."/".$module['image'])."');" : "" ?>"></div>
                    <a href="" onClick="jQuery('#select_image option:selected').removeAttr('selected').next().attr('selected', 'selected').trigger('change'); return false;">
                        <?= version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                            ? Icon::create("arr_1right", "clickable")->asImg(20, array('style' => "vertical-align: middle;"))
                            : Assets::img("icons/blue/20/arr_1right", array('style' => "vertical-align: middle;"))
                        ?>
                    </a>
                </div>
            <? endif ?>
        <? endif ?>

        <? if (LernmodulePlugin::mayEditSandbox()) : ?>
            <label>
                <input type="hidden" name="module[sandbox]" value="0">
                <input type="checkbox" name="module[sandbox]" value="1"<?= $module['sandbox'] ? " checked" : "" ?>>
                <?= _("Im abgesicherten Modus abspielen") ?>
            </label>
        <? endif ?>

        <label>
            <input type="hidden" name="modulecourse[anonymous_attempts]" value="0">
            <input type="checkbox" name="modulecourse[anonymous_attempts]" value="1"<?= $modulecourse['anonymous_attempts'] ? " checked" : "" ?>>
            <?= _("Nutzer sollen anonym teilnehmen") ?>
        </label>

        <label>
            <?= _("Frühester Startzeitpunkt") ?>
            <input type="text" id="modulecourse_starttime" name="modulecourse[starttime]" value="<?= $modulecourse['starttime'] ? date("d.m.Y H:i", $modulecourse['starttime']) : "jederzeit" ?>"  data-datetime-picker>
        </label>

        <? if (!$module->isNew() && is_a($module, "CustomLernmodul")) : ?>
            <? $template = $module->getEditTemplate() ?>
            <? if ($template) : ?>
                <?= $template->render() ?>
            <? endif ?>
        <? endif ?>

    </fieldset>

    <? if (!$module->isNew()) : ?>
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
        $max = ceil(max($successful['average'] + $successful['standarddeviation'], $unsuccessful['average'] + $unsuccessful['standarddeviation']));
        $segements = 10;
        $factor = $max / $segements;
        $range = range(0, $segements + 1);
        $result = array();
        foreach ($range as $key => $value) {
            $range[$key] = ceil($value * $factor)."s";
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
                            width: '68vw',
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
                <p><?= _("Zeit der Durchläufe in Sekunden. Blau sind die erfolgreichen Durchläufe, rot die nicht erfolgreichen.") ?></p>
        </fieldset>
        <? endif ?>
    <? endif ?>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern")) ?>
        <?= \Studip\Button::create(_("Löschen"), "delete", array(
            'formaction' => PluginEngine::getLink($plugin, array(), "lernmodule/delete/".$module->getId()),
            'onClick' => "return window.confirm('"._("Wirklich löschen?")."');"
        )) ?>
    </div>

</form>

<?

$actions = new ActionsWidget();
$actions->addLink(
    _("Lernmodul herunterladen"),
    PluginEngine::getURL($plugin, array(), "lernmodule/download/".$module->getId()),
    Icon::create("download", "info")
);

Sidebar::Get()->addWidget($actions);