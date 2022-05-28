<div id="app">
  <h1>Vuejs View template</h1>
  Wenn die Vue-App nach Laden der Seite an dieser Stelle nicht erscheint, kann es sein, dass der
  Dev-Server nicht läuft oder der Production-Build nicht ausgeführt wurde.

  <pre><?= $plugin->config['production'] ?
          '$production === true. Überprüfe die Inhalte von /vue/dist.' :
          '$production === false. Überprüfe, ob der Dev-Server läuft.' ?></pre>
</div>


<script>
  STUDIP.LernmoduleVueJS = <?= json_encode($javascript_global_variables) ?>
</script>

<div><?= $plugin->config['production'] ?></div>
<? if (!$plugin->config['production']) : ?>
  <script src="http://localhost:8080/js/viewer.js"></script>
  <script src="http://localhost:8080/js/chunk-vendors.js"></script>
  <script src="http://localhost:8080/js/chunk-common.js"></script>
<? else : ?>
  <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/viewer.js' ?>"></script>
  <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/chunk-vendors.js' ?>"></script>
  <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/chunk-common.js' ?>"></script>
  <link rel="stylesheet" href="<?= $plugin->getPluginUrl() . '/vue/dist/css/viewer.css' ?>"></link>
<? endif ?>
