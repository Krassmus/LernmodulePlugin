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

      <studip-wysiwyg
        v-model="taskDefinition.template"
        ref="wysiwyg"
        force-soft-breaks
        remove-wrapping-p-tag
        disable-autoformat
      />
    </fieldset>

    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Einstellungen') }}</legend>

      <label>
        <input v-model="taskDefinition.retryAllowed" type="checkbox" />
        {{ $gettext('Mehrere Versuche erlauben') }}
      </label>

      <label>
        <input v-model="taskDefinition.showSolutionsAllowed" type="checkbox" />
        {{ $gettext('Lösungen anzeigen erlauben') }}
      </label>
    </fieldset>

    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Beschriftungen') }}</legend>

      <label>
        {{ $gettext('Text für Überprüfen-Button:') }}
        <input v-model="taskDefinition.strings.checkButton" type="text" />
      </label>

      <label :class="{ 'setting-disabled': !taskDefinition.retryAllowed }">
        {{ $gettext('Text für Wiederholen-Button:') }}
        <input
          v-model="taskDefinition.strings.retryButton"
          :disabled="!taskDefinition.retryAllowed"
          type="text"
        />
      </label>

      <label
        :class="{ 'setting-disabled': !taskDefinition.showSolutionsAllowed }"
      >
        {{ $gettext('Text für Lösungen-Button:') }}
        <input
          v-model="taskDefinition.strings.solutionsButton"
          :disabled="!taskDefinition.showSolutionsAllowed"
          type="text"
        />
      </label>
    </fieldset>

    <feedback-editor
      :feedback="taskDefinition.feedback"
      :result-message="taskDefinition.strings.resultMessage"
      @update:feedback="updateFeedback"
      @update:result-message="updateResultMessage"
    />
  </form>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';
import { Feedback, MarkTheWordsTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import { $gettext } from '@/language/gettext';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import FeedbackEditor from '@/components/FeedbackEditor.vue';
import produce from 'immer';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';

export default defineComponent({
  name: 'MarkTheWordsEditor',
  components: { FeedbackEditor, StudipWysiwyg },
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
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

    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },

    updateFeedback(updatedFeedback: Feedback[]) {
      this.taskEditor!.performEdit({
        newTaskDefinition: produce(
          this.taskDefinition,
          (taskDraft: MarkTheWordsTask) => {
            taskDraft.feedback = updatedFeedback;
          }
        ),
        undoBatch: {},
      });
    },

    updateResultMessage(updatedResultMessage: string) {
      this.taskEditor!.performEdit({
        newTaskDefinition: produce(
          this.taskDefinition,
          (taskDraft: MarkTheWordsTask) => {
            taskDraft.strings.resultMessage = updatedResultMessage;
          }
        ),
        undoBatch: {},
      });
    },
  },

  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as MarkTheWordsTask,

    instructions(): string {
      return $gettext(
        'Markieren Sie ein Wort als Lösung, indem Sie ein Sternchen (*) vor und hinter dem Wort setzen oder markieren Sie ein Wort und klicken Sie den „Richtiges Wort markieren“–Button.'
      );
    },
  },
});
</script>

<style scoped></style>
