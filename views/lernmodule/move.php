<form action="<?= PluginEngine::getLink($plugin, array(), "lernmodule/move/".$module->getId()) ?>"
      method="post"
      class="default">

    <? if ($GLOBALS['perm']->have_perm("admin")) : ?>
        <label>
            <?= _("Veranstaltung auswählen") ?>
            <?= QuickSearch::get("seminar_id", new StandardSearch("Seminar_id"))->render() ?>
        </label>
    <? else : ?>
        <label>
            <?= _("Veranstaltung auswählen") ?>
            <select name="seminar_id" required>
                <option></option>
                <? foreach ($semcourses as $semester) : ?>
                    <optgroup label="<?= htmlReady($semester['semester']['name']) ?>">
                    <? foreach ($semester['courses'] as $course) : ?>
                        <option value="<?= htmlReady($course->getId()) ?>">
                            <?= htmlReady($course->getFullName()) ?>
                        </option>
                    <? endforeach ?>
                    </optgroup>
                <? endforeach ?>
            </select>
        </label>
    <? endif ?>
    <div data-dialog-button>
        <?= \Studip\Button::create(_("Verschieben"), "move") ?>
        <?= \Studip\Button::create(_("Kopieren"), "copy") ?>
    </div>
</form>
