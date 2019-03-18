<form action="<?= PluginEngine::getLink($plugin, array(), "lernmodule/blubber") ?>"
      method="post"
      class="default">
    <label>
        <?= _("Wollen Sie folgendes Blubbern?") ?>
        <textarea name="message"><?= htmlReady(Request::get("message")) ?></textarea>
    </label>
    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern"), "send") ?>
        <?= \Studip\LinkButton::create(_("Nicht mehr nachfragen"), "#", array('onClick' => "STUDIP.Lernmodule.dont_blubber = true; STUDIP.Dialog.close(); return false;")) ?>
    </div>
</form>