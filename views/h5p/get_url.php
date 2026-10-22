<form class="default">

    <label>
        <?= dgettext("lernmoduleplugin","URL") ?>
        <? $oldbase = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']) ?>
        <input type="text" readonly value="<?= URLHelper::getLink("plugins.php/lernmoduleplugin/h5p/iframe/".$module->getId(), array()) ?>">
        <? URLHelper::setBaseURL($oldbase) ?>
    </label>

</form>
