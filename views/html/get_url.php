<form class="default">

    <label>
        <?= dgettext("lernmoduleplugin","URL") ?>
        <input type="text" readonly value="<?= htmlReady($module->getStartURL()) ?>">
    </label>


</form>
