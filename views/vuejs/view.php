<div><?= Config::get()->LERNMODULE_DEBUG ? 'LERNMODULE_DEBUG = ' . Config::get()->LERNMODULE_DEBUG : '' ?></div>
<div id="app">
  <h1>Vuejs View template</h1>
  Wenn die Vue-App nach Laden der Seite an dieser Stelle nicht erscheint, kann es sein, dass der
  Dev-Server nicht läuft oder der Production-Build nicht ausgeführt wurde.

  <pre><?= Config::get()->LERNMODULE_DEBUG ?
          'LERNMODULE_DEBUG === true. Überprüfe, ob der Dev-Server läuft.' :
          'LERNMODULE_DEBUG === false. Überprüfe die Inhalte von /vue/dist.' ?></pre>
</div>


<script>
  STUDIP.LernmoduleVueJS = <?= json_encode($javascript_global_variables) ?>;
</script>

<!--
If $production, load the production build of our Vue project which is compiled using 'npm run build'.
  Otherwise, load the development build from the dev server at localhost:8080, which is launched via 'npm run hot'.
  -->
<?php if (Config::get()->LERNMODULE_DEBUG) : ?>
  <script src="http://localhost:8080/js/viewer.js"></script>
  <script src="http://localhost:8080/js/chunk-vendors.js"></script>
  <script src="http://localhost:8080/js/chunk-common.js"></script>
<?php else : ?>
  <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/viewer.js' ?>"></script>
  <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/chunk-vendors.js' ?>"></script>
  <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/chunk-common.js' ?>"></script>
  <link rel="stylesheet" href="<?= $plugin->getPluginUrl() . '/vue/dist/css/viewer.css' ?>"></link>
<?php endif ?>
