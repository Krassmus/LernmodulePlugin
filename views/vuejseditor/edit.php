Vue.js Edit Template
<form class="default" method="post"
      action="<?= PluginEngine::getLink($plugin, [], "vuejseditor/save") ?>">
    <?= CSRFProtection::tokenTag() ?>
  <input type="hidden" name="module_id" value="<?= $mod->id ?>"/>
  <input type="hidden" name="block_id" value="<?= $block_id ?>"/>
  <input type="submit" />
</form>