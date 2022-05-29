<div id="app">
  <h1>Vue.js Edit Template</h1>
  Wenn die Vue-App nach Laden der Seite an dieser Stelle nicht erscheint, kann es sein, dass der
  Dev-Server nicht läuft oder der Production-Build nicht ausgeführt wurde.

  <pre><?= $plugin->config['production'] ?
          '$production === true. Überprüfe die Inhalte von /vue/dist.' :
          '$production === false. Überprüfe, ob der Dev-Server läuft.' ?></pre>
</div>


<script>
  STUDIP.LernmoduleVueJS = <?= json_encode($javascript_global_variables) ?>;
</script>

<? if (!$plugin->config[production]) : ?>
  <script src="http://localhost:8080/js/editor.js"></script>
  <script src="http://localhost:8080/js/chunk-vendors.js"></script>
  <script src="http://localhost:8080/js/chunk-common.js"></script>
<? else : ?>
  <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/editor.js' ?>"></script>
  <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/chunk-vendors.js' ?>"></script>
  <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/chunk-common.js' ?>"></script>
  <link rel="stylesheet" href="<?= $plugin->getPluginUrl() . '/vue/dist/css/editor.css' ?>"></link>
<? endif ?>

<?php
$actions = new ActionsWidget();
$actions->addLink(
    dgettext("lernmoduleplugin", "Abspieloptionen bearbeiten"),
    PluginEngine::getURL($plugin, array(), "lernmodule/edit/" . $mod->getId()),
    Icon::create("edit", "clickable")
);
Sidebar::get()->addWidget($actions);

$views = new ViewsWidget();
$views->addLink(
    $mod['name'],
    PluginEngine::getURL($plugin, array(), "lernmodule/view/" . $mod->getId()),
    null,
    array()
);
$views->addLink(
    dgettext("lernmoduleplugin", "Auswertung"),
    PluginEngine::getURL($plugin, array(), "lernmodule/evaluation/" . $mod->getId()),
    null,
    array()
);

Sidebar::Get()->addWidget($views);
