<template>
  <div class="stud5p-task">
    <form class="default">
      <fieldset>
        <legend>{{ $gettext('Fill in the Blanks') }}</legend>

        <div class="fill-in-the-blanks-editor-top-panel">
          <button
            @click="addBlank"
            class="button"
            type="button"
            style="margin-right: 0.1em"
          >
            {{ $gettext('Lücke hinzufügen') }}
          </button>
          <div class="tooltip tooltip-icon" :data-tooltip="instructions" />
        </div>

        <studip-wysiwyg
          :modelValue="modelTaskDefinition.template"
          @update:modelValue="onEditTemplate"
          ref="wysiwyg"
          force-soft-breaks
          remove-wrapping-p-tag
          disable-autoformat
        />
      </fieldset>

      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Einstellungen') }}</legend>

        <label>
          <input
            v-model="modelTaskDefinition.caseSensitive"
            @change="updateTaskDefinition"
            type="checkbox"
          />
          {{ $gettext('Groß- und Kleinschreibung beachten') }}
        </label>

        <label>
          <input
            v-model="modelTaskDefinition.acceptTypos"
            @change="updateTaskDefinition"
            type="checkbox"
          />
          {{ $gettext('Rechtschreib- und Tippfehler ignorieren') }}
        </label>

        <label>
          <input
            v-model="modelTaskDefinition.autoCorrect"
            @change="updateTaskDefinition"
            type="checkbox"
          />
          {{ $gettext('Lücken automatisch prüfen') }}
        </label>

        <label>
          <input
            v-model="modelTaskDefinition.retryAllowed"
            @change="updateTaskDefinition"
            type="checkbox"
          />
          {{ $gettext('Mehrere Versuche erlauben') }}
        </label>

        <label>
          <input
            v-model="modelTaskDefinition.showSolutionsAllowed"
            @change="updateShowSolutionsAllowed"
            type="checkbox"
          />
          {{ $gettext('Lösungen anzeigen erlauben') }}
        </label>

        <label
          :class="{
            'setting-disabled': !modelTaskDefinition.showSolutionsAllowed,
          }"
        >
          <input
            v-model="modelTaskDefinition.allBlanksMustBeFilledForSolutions"
            @change="updateTaskDefinition"
            :disabled="!modelTaskDefinition.showSolutionsAllowed"
            type="checkbox"
          />
          {{
            $gettext('Lösungen nur anzeigen, wenn alle Lücken ausgefüllt sind')
          }}
        </label>
      </fieldset>

      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Beschriftungen') }}</legend>

        <label :class="{ 'setting-disabled': modelTaskDefinition.autoCorrect }">
          {{ $gettext('Text für Überprüfen-Button:') }}
          <input
            v-model="modelTaskDefinition.strings.checkButton"
            @input="updateTaskDefinition('taskDefinition.strings.checkButton')"
            :disabled="modelTaskDefinition.autoCorrect"
            type="text"
          />
        </label>

        <label
          :class="{ 'setting-disabled': !modelTaskDefinition.retryAllowed }"
        >
          {{ $gettext('Text für Wiederholen-Button:') }}
          <input
            v-model="modelTaskDefinition.strings.retryButton"
            @input="updateTaskDefinition('taskDefinition.strings.retryButton')"
            :disabled="!modelTaskDefinition.retryAllowed"
            type="text"
          />
        </label>

        <label
          :class="{
            'setting-disabled': !modelTaskDefinition.showSolutionsAllowed,
          }"
        >
          {{ $gettext('Text für Lösungen-Button:') }}
          <input
            v-model="modelTaskDefinition.strings.solutionsButton"
            @input="
              updateTaskDefinition('taskDefinition.strings.solutionsButton')
            "
            :disabled="!modelTaskDefinition.showSolutionsAllowed"
            type="text"
          />
        </label>

        <label
          :class="{
            'setting-disabled':
              !modelTaskDefinition.showSolutionsAllowed ||
              !modelTaskDefinition.allBlanksMustBeFilledForSolutions,
          }"
        >
          {{ $gettext('Hinweis, wenn nicht alle Lücken ausgefüllt sind:') }}
          <input
            v-model="modelTaskDefinition.strings.fillInAllBlanksMessage"
            @input="
              updateTaskDefinition(
                'taskDefinition.strings.fillInAllBlanksMessage'
              )
            "
            :disabled="
              !modelTaskDefinition.showSolutionsAllowed ||
              !modelTaskDefinition.allBlanksMustBeFilledForSolutions
            "
            type="text"
          />
        </label>
      </fieldset>

      <feedback-editor
        :feedback="modelTaskDefinition.feedback"
        :result-message="modelTaskDefinition.strings.resultMessage"
        @update:feedback="updateFeedback"
        @update:result-message="updateResultMessage"
      />
    </form>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import { FillInTheBlanksTask } from '@/models/TaskDefinition';
import { Feedback } from '@/models/common';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import FeedbackEditor from '@/components/FeedbackEditor.vue';
import { $gettext } from '@/language/gettext';
import produce from 'immer';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import { cloneDeep } from 'lodash';

export default defineComponent({
  name: 'FillInTheBlanksEditor',
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  components: { StudipWysiwyg, FeedbackEditor },
  props: {
    taskDefinition: {
      type: Object as PropType<FillInTheBlanksTask>,
      required: true,
    },
  },
  data() {
    return {
      modelTaskDefinition: cloneDeep(this.taskDefinition),
    };
  },
  watch: {
    // Synchronize state taskDefinition -> modelTaskDefinition.
    taskDefinition: {
      immediate: true,
      handler: function (): void {
        this.modelTaskDefinition = cloneDeep(this.taskDefinition);
      },
    },
  },
  computed: {
    instructions(): string {
      return $gettext(
        'Um eine Lücke zu erstellen, setzen Sie ein Sternchen (*) vor und hinter das korrekte Wort oder markieren Sie das Wort und klicken Sie auf den Button „Lücke hinzufügen“. Sie können auch einen Tooltip hinzufügen, indem Sie einen Doppelpunkt (:) vor den Tooltip-Text schreiben.'
      );
    },
  },
  methods: {
    $gettext,

    /**
     * Surround the selected text with two asterisks
     */
    addBlank() {
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

    onEditTemplate(data: string): void {
      // Copy new data from wysiwyg editor into task definition
      this.taskEditor!.performEdit({
        newTaskDefinition: produce(
          this.modelTaskDefinition,
          (draft: FillInTheBlanksTask) => {
            draft.template = data;
          }
        ),
        undoBatch: 'taskDefinition.template',
      });
    },

    updateTaskDefinition(undoBatch?: unknown) {
      // Synchronize state modelTaskDefinition -> taskDefinition.
      console.log('update task definition');
      this.taskEditor!.performEdit({
        newTaskDefinition: cloneDeep(this.modelTaskDefinition),
        undoBatch: undoBatch ?? {},
      });
    },

    updateShowSolutionsAllowed() {
      if (!this.modelTaskDefinition.showSolutionsAllowed) {
        this.modelTaskDefinition.allBlanksMustBeFilledForSolutions = false;
      }
      this.updateTaskDefinition();
    },

    updateFeedback(updatedFeedback: Feedback[]) {
      this.taskEditor!.performEdit({
        newTaskDefinition: produce(
          this.modelTaskDefinition,
          (taskDraft: FillInTheBlanksTask) => {
            taskDraft.feedback = updatedFeedback;
          }
        ),
        undoBatch: 'taskDefinition.feedback',
      });
    },

    updateResultMessage(updatedResultMessage: string) {
      this.taskEditor!.performEdit({
        newTaskDefinition: produce(
          this.modelTaskDefinition,
          (taskDraft: FillInTheBlanksTask) => {
            taskDraft.strings.resultMessage = updatedResultMessage;
          }
        ),
        undoBatch: 'taskDefinition.strings.resultMessage',
      });
    },

    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },
  },
});
</script>

<style scoped>
.fill-in-the-blanks-editor-top-panel {
  display: flex;
  justify-content: flex-start;
  align-items: center;
}
</style>
