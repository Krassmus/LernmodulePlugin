<?
$background_image = $lernmodul['image']
    ? (preg_match("/^[a-f0-9]{32}$/", $lernmodul['image']) ? FileRef::find($lernmodul['image']) : (filter_var($lernmodul['image'], FILTER_VALIDATE_URL) ? $lernmodul['image'] : $lernmodul->getDataURL()."/".$lernmodul['image']))
    : "";
$not_started_yet = $coursemodule['starttime'] && $coursemodule['starttime'] > time();
?>

<? if ($lernmodul->isWritable() || (!$not_started_yet && $coursemodule->matchedPrerequisites())) : ?>
    <div class="module droppable"<?= $background_image ? " style=\"background-image: url('".htmlReady(is_a($background_image, "FileRef") ? $background_image->getDownloadURL() : $background_image)."');\"" : "" ?>
         data-module_id="<?= htmlReady($lernmodul->getId()) ?>"
         data-url="<?= PluginEngine::getLink($plugin, array(), "lernmodule/view/".$lernmodul->getId()) ?>">
        <? if (!$lernmodul->isWritable()) : ?>
            <? if (LernmodulAttempt::findOneBySQL("successful = '1' AND module_id = ? AND user_id = ?", array($lernmodul->getId(), $GLOBALS['user']->id))) : ?>
                <div class="crown">
                    <?= Icon::create("crown", "status-yellow")->asImg(20, array('title' => dgettext("lernmoduleplugin","Erfolgreich abgeschlossen"), 'class' => "text-bottom")) ?>
                </div>
            <? endif ?>
        <? endif ?>
        <div class="shadow">
            <a href="<?= PluginEngine::getLink($plugin, array(), "lernmodule/view/".$lernmodul->getId()) ?>">
                <? if (!$lernmodul->isWritable()) : ?>
                    <?= Icon::create("learnmodule", "info_alt")->asImg(20, array('style' => "vertical-align: middle;")) ?>
                <? endif ?>
                <?= htmlReady($lernmodul['name']) ?>
            </a>
            <? if ($lernmodul->isWritable()) : ?>
                <a href="<?= PluginEngine::getLink($plugin, array(), "lernmodule/edit/".$lernmodul->getId()) ?>">
                    <?= Icon::create("edit", "info_alt")->asImg(20, array('class' => "text-bottom")) ?>
                </a>
                <img src="<?= URLHelper::getLink("plugins_packages/RasmusFuhse/LernmodulePlugin/assets/mover.svg", [], true) ?>"
                     width="20"
                     class="text-bottom mover">
            <? endif ?>
        </div>
    </div>
<? elseif($not_started_yet) : ?>
    <div class="module droppable"
         data-module_id="<?= htmlReady($lernmodul->getId()) ?>"
         style="opacity: 0.5;<?= $background_image ? " background-image: url('".htmlReady(is_a($background_image, "FileRef") ? $background_image->getDownloadURL() : $background_image)."');\"" : "" ?>" title="<?= sprintf(dgettext("lernmoduleplugin","Dieses Modul wird erst ab %s Uhr verfügbar sein."), date("d.m.Y H:i", $coursemodule['starttime'])) ?>">
        <div class="shadow" style="max-height: 108px; height: 108px;">
            <?= Icon::create("date", "info_alt")->asImg(80, array('style' => "vertical-align: middle; margin-left: auto; margin-right: auto;")) ?>
        </div>
    </div>
<? else : ?>
    <div class="module droppable"
         data-module_id="<?= htmlReady($lernmodul->getId()) ?>"
         style="opacity: 0.3;<?= $background_image ? " background-image: url('".htmlReady(is_a($background_image, "FileRef") ? $background_image->getDownloadURL() : $background_image)."');\"" : "" ?>" title="<?= dgettext("lernmoduleplugin","Aktivieren Sie dieses Modul dadurch, dass Sie die anderen Module durcharbeiten.") ?>">
        <div class="shadow" style="max-height: 108px; height: 108px;">
            <?= Icon::create("question-circle", "info_alt")->asImg(80, array('style' => "vertical-align: middle; margin-left: auto; margin-right: auto;")) ?>
        </div>
    </div>
<? endif ?>
