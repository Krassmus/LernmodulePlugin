<script>
  if (!window.STUDIP?.LernmoduleCoursewareBlocksPlugin) {
    window.STUDIP.LernmoduleCoursewareBlocksPlugin = {};
  }
  window.STUDIP.LernmoduleCoursewareBlocksPlugin.editorUrl = "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins.php/lernmodulecoursewareblocksplugin/courseware/editor' ?>";
  <? if (Config::get()->LERNMODULE_DEBUG) : ?>
  window.STUDIP.LernmoduleCoursewareBlocksPlugin.debug = true;
  console.info('set window.STUDIP.LernmoduleCoursewareBlocksPlugin', window.STUDIP.LernmoduleCoursewareBlocksPlugin);
  <? endif ?>
</script>
