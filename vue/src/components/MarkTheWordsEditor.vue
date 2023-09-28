<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Mark the Words') }}</legend>
      <div class="h5pEditorTopPanel">
        <button
          @click="addSolution"
          class="button"
          type="button"
          style="margin-right: 0.1em"
        >
          {{ $gettext('Richtiges Wort markieren') }}
        </button>
        <div class="tooltip tooltip-icon" :data-tooltip="instructions" />
      </div>
      <StudipWysiwyg v-model="taskDefinition.template" ref="wysiwyg" />
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MarkTheWordsTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import { $gettext } from '@/language/gettext';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';

export default defineComponent({
  name: 'MarkTheWordsEditor',
  components: { StudipWysiwyg },
  methods: {
    $gettext,
    /**
     * Surround the selected text with two asterisks
     */
    addSolution() {
      const wysiwygEl = (this.$refs.wysiwyg as any)?.$el;
      const editor = window.STUDIP.wysiwyg.getEditor(wysiwygEl);
      if (!editor) {
        console.error('getEditor(wysiwygEl) returned: ', editor);
        throw new Error('Could not get reference to wysiwyg editor');
      }

      const selection = editor.model.document.selection;
      const start = selection.getFirstPosition();
      const end = selection.getLastPosition();
      if (!start || !end) {
        console.error('selection start: ', start, ' selection end: ', end);
        throw new Error('Could not get selection in editor');
      }
      editor.model.change((writer) => {
        writer.insertText('*', end);
        writer.insertText('*', start);
      });
    },
  },

  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as MarkTheWordsTask,
    instructions(): string {
      return $gettext(
        'Markieren Sie ein Wort als Lösung, indem Sie ein Sternchen (*) vor und hinter dem Wort setzen oder markieren Sie ein Wort und klicken Sie den "Richtiges Wort markieren"–Button.'
      );
    },
  },
});
</script>

<style scoped></style>
