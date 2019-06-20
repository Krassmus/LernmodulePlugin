<form action="<?= PluginEngine::getLink($plugin, array(), "lernmodule/blubber") ?>"
      method="post"
      class="default">
    <label>
        <?= dgettext("lernmoduleplugin","Wollen Sie folgendes Blubbern?") ?>
        <textarea name="message"><?= htmlReady(Request::get("message")) ?></textarea>
    </label>
    <div data-dialog-button>
        <?= \Studip\Button::create(dgettext("lernmoduleplugin","Speichern"), "send") ?>
        <?= \Studip\LinkButton::create(dgettext("lernmoduleplugin","Nicht mehr nachfragen"), "#", array('onClick' => "STUDIP.Lernmodule.dont_blubber = true; STUDIP.Dialog.close(); return false;")) ?>
    </div>
</form>
<?
Sidebar::Get()->setImage(Assets::image_path("sidebar/learnmodule-sidebar.png"));