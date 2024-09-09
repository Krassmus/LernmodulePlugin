<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Find the Words') }}</legend>
      <div class="h5pEditorTopPanel">
        <input type="text" style="width: 100%" @submit="addWord" />
        <button
          v-text="$gettext('Wort hinzufügen')"
          @click="addWord"
          class="button"
          type="button"
        />
      </div>
    </fieldset>

    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Einstellungen') }}</legend>
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FindTheWordsTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'FindTheWordsEditor',
  props: {
    taskDefinition: {
      type: Object as PropType<FindTheWordsTask>,
      required: true,
    },
  },
  computed: {},
  methods: {
    $gettext,

    addWord() {},

    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },
  },
});
</script>

<style scoped>
.h5pEditorTopPanel {
  display: flex;
  justify-content: flex-start;
  align-items: center;
}
</style>
