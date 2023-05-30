<div>This is where the Vue 3 code for the Lernmodule Courseware Block should be included</div>
<!--  Hide all of Stud.IP's layout elements and remove extraneous padding.-->
<style>
    #barBottomContainer, #flex-header, .secondary-navigation, #layout-sidebar, #layout_footer, #page_title_container {
        display: none !important;
    }

    #layout_wrapper, #layout_content, #layout_container {
        padding: 0;
    }
</style>

<div id="app">
    <h1>Lernmodule Courseware Block Template</h1>
    Wenn die Vue-App nach Laden der Seite an dieser Stelle nicht erscheint, kann es sein, dass der
    Dev-Server nicht läuft oder der Production-Build nicht ausgeführt wurde.
    <pre><?= Config::get()->LERNMODULE_DEBUG ?
            'LERNMODULE_DEBUG === true. Überprüfe, ob der Dev-Server läuft.' :
            'LERNMODULE_DEBUG === false. Überprüfe die Inhalte von /vue/dist.' ?></pre>
</div>

<script>
  window.STUDIP.LernmoduleVueJS = {};
</script>

<?php if (Config::get()->LERNMODULE_DEBUG) : ?>
    <script src="http://localhost:8080/js/courseware.js"></script>
    <script src="http://localhost:8080/js/chunk-vendors.js"></script>
    <script src="http://localhost:8080/js/chunk-common.js"></script>
<?php else : ?>
    <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/courseware.js' ?>"></script>
    <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/chunk-vendors.js' ?>"></script>
    <script src="<?= $plugin->getPluginUrl() . '/vue/dist/js/chunk-common.js' ?>"></script>
    <link rel="stylesheet"
          href="<?= $plugin->getPluginUrl() . '/vue/dist/css/courseware.css' ?>"></link>
<?php endif ?>
