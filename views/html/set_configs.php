<form class="default" method="post" action="<?= PluginEngine::getLink($plugin, array(), "html/set_configs") ?>" data-dialog>

    <input type="hidden" name="module_id" value="<?= $lernmodulcourse['module_id'] ?>">
    <table class="default">
    <? foreach (Request::getArray("configs") as $name => $value) : ?>
        <tr>
            <td>
                <?= htmlReady($name) ?>
            </td>
            <td>
                <input type="text" name="configs[<?= htmlReady($name) ?>]" value="<?= htmlReady(isset($lernmodulcourse['customdata']['configs'][$name]) ? $lernmodulcourse['customdata']['configs'][$name] : $value) ?>">
            </td>
        </tr>
    <? endforeach ?>
    </table>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern")) ?>
    </div>
</form>