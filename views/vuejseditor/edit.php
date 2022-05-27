Vue.js Edit Template
<form class="default" method="post"
      action="<?= PluginEngine::getLink($plugin, [], "vuejseditor/save") ?>">
    <?= CSRFProtection::tokenTag() ?>
  <input type="hidden" name="module_id" value="<?= $mod->id ?>" />
  <input type="hidden" name="block_id" value="<?= $block_id ?>" />
  <input type="submit" />
</form>

<div id="app">
  Wenn die Vue-App nach Laden der Seite an dieser Stelle nicht erscheint, kann es sein, dass der
  Dev-Server nicht läuft oder der Production-Build nicht ausgeführt wurde.

  <pre><?= $production ?
          '$production === true. Überprüfe die Inhalte von /dist.' :
          '$production === false. Überprüfe, ob der Dev-Server läuft.' ?></pre>
</div>


<script>
  STUDIP.H5P = {
    saveRoute: "<?= $controller->url_for('vuejseditor/save') ?>"
  };
</script>

<? if (!$production) : ?>
  <script src="http://localhost:8080/js/editor.js"></script>
  <script src="http://localhost:8080/js/chunk-vendors.js"></script>
<? else : ?>
  <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/editor.js' ?>"></script>
  <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/chunk-vendors.js' ?>"></script>
  <link rel="stylesheet" href="<?= $plugin->getPluginUrl() . '/vue/dist/css/editor.css' ?>"></link>
<? endif ?>
