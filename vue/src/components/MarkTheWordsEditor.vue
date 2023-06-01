<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Text erstellen') }}</legend>
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
      <studip-wysiwyg v-model="taskDefinition.template" id="ckeditorElement" />
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
    addSolution() {
      const editor = window.CKEDITOR.instances['ckeditorElement'];

      const selectedText = editor.getSelection().getSelectedText();

      const solution = selectedText.replace(
        selectedText.trim(),
        '*' + selectedText.trim() + '*'
      );

      editor.insertText(solution);

      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...this.taskDefinition,
          template: editor.getData(),
        },
        undoBatch: { type: 'editMarkTheWordsTemplate' },
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
