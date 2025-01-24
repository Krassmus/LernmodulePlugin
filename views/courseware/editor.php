<!-- This is the template for the iframe where our Vue 3 code is embedded inside of our Vue 2 Courseware block component.
  In the future, this can be refactored and removed when Courseware
  is finally updated to Vue 3.
  It is called editor.php, but applies for the "read" mode in Courseware as well.
  (Sorry for the confusing file name -Ann) -->

<!--  Hide all of Stud.IP's layout elements and remove extraneous padding.-->
<style>
    #barBottomContainer, #flex-header, .secondary-navigation, #layout-sidebar, #layout_footer, #page_title_container, #skip_link_navigation, #main-header, #sidebar, #main-footer, #scroll-to-top {
        display: none !important;
    }

    #layout_wrapper, #layout_content, #layout_container {
        padding: 0 !important;
    }
    #content-wrapper {
        padding: 0 !important;
        margin: 0 !important;
        grid-column: 1/3 !important;
        grid-row: 1/3 !important;
    }
</style>

<!-- Make all links open in a new tab, unless otherwise specified.
This prevents the issue where users click on links inside of our Courseware
blocks and it, confusingly, opens the link inside of our iframe in place of
the block. -->
<base target="_blank" />

<!-- data-iframe-height indicates that this element determines the height
that the iframe should be resized to by the iFrameSizer library -->
<div id="app" data-iframe-height>
    <?php if (!Config::get()->LERNMODULE_DEBUG) : ?>
  <div style='display: none;'> <?php endif ?>
    <h1>Lernmodule Courseware Block Template</h1>
    Wenn die Vue-App nach Laden der Seite an dieser Stelle nicht erscheint, kann es sein, dass der
    Dev-Server nicht läuft oder der Production-Build nicht ausgeführt wurde.
    <pre><?= Config::get()->LERNMODULE_DEBUG ?
            'LERNMODULE_DEBUG === true. Überprüfe, ob der Dev-Server läuft.' :
            'LERNMODULE_DEBUG === false. Überprüfe die Inhalte von /vue/dist.' ?></pre>
      <?php if (!Config::get()->LERNMODULE_DEBUG) : ?> </div> <?php endif ?>
</div>

<script>
  STUDIP.LernmoduleVueJS = <?= json_encode($javascript_global_variables) ?>;
</script>

<!-- iFrameResizer -- This script facilitates resizing the iframe to fit its contents automatically -->
<script src='<?= $plugin->getPluginUrl(
) . '/assets/courseware-block/iframeResizer.contentWindow.min.js' ?>'></script>

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
  <link rel="stylesheet" href="<?= $plugin->getPluginUrl() . '/vue/dist/css/chunk-common.css' ?>"></link>
<?php endif ?>
