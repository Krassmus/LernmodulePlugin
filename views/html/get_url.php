<form class="default">

    <label>
        <?= dgettext("lernmoduleplugin","URL") ?>
        <input type="text" readonly value="<?= htmlReady($module->getStartURL(null, true)) ?>">
    </label>


</form>
