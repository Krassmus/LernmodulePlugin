<script>
  if (!window.STUDIP?.LernmoduleCoursewareBlocksPlugin) {
    window.STUDIP.LernmoduleCoursewareBlocksPlugin = {};
  }
  <? if (Config::get()->LERNMODULE_DEBUG) : ?>
  window.STUDIP.LernmoduleCoursewareBlocksPlugin.debug = true;
  <? endif ?>
  window.STUDIP.LernmoduleCoursewareBlocksPlugin.editorUrl = "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins.php/lernmodulecoursewareblocksplugin/courseware/editor' ?>";
  console.warn('set window.STUDIP.LernmoduleCoursewareBlocksPlugin', window.STUDIP.LernmoduleCoursewareBlocksPlugin);
</script>
