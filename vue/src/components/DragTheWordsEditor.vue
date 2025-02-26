<template>
  <div class="stud5p-task">
    <form class="default">
      <fieldset>
        <legend>{{ $gettext('Drag the Words') }}</legend>

        <div class="h5pEditorTopPanel">
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

        <label style="margin-top: 1.5ex">
          {{ $gettext('Irreführende Antworten') }}
          <span
            class="tooltip tooltip-icon"
            :data-tooltip="distratorInstructions"
          />
          <input
            v-model="modelTaskDefinition.distractors"
            @input="updateTaskDefinition('taskDefinition.distractors')"
            type="text"
          />
        </label>
      </fieldset>

      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Einstellungen') }}</legend>

        <label>
          <input
            v-model="modelTaskDefinition.instantFeedback"
            @change="updateTaskDefinition"
            type="checkbox"
          />
          {{ $gettext('Lücken automatisch prüfen') }}
        </label>

        <label>
          <input
            v-model="modelTaskDefinition.alphabeticOrder"
            @change="updateTaskDefinition"
            type="checkbox"
          />
          {{ $gettext('Antworten alphabetisch sortieren') }}
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
            @change="updateShowSolutionsAllowed"
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

        <label
          :class="{ 'setting-disabled': modelTaskDefinition.instantFeedback }"
        >
          {{ $gettext('Text für Überprüfen-Button:') }}
          <input
            v-model="modelTaskDefinition.strings.checkButton"
            @input="updateTaskDefinition('taskDefinition.strings.checkButton')"
            :disabled="modelTaskDefinition.instantFeedback"
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
import { DragTheWordsTask, Feedback } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import { $gettext } from '@/language/gettext';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import produce from 'immer';
import FeedbackEditor from '@/components/FeedbackEditor.vue';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import { cloneDeep } from 'lodash';

export default defineComponent({
  name: 'DragTheWordsEditor',
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  components: { StudipWysiwyg, FeedbackEditor },
  props: {
    taskDefinition: {
      type: Object as PropType<DragTheWordsTask>,
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

    distratorInstructions(): string {
      return $gettext(
        'Geben Sie falsche Antworten als Distraktoren ein. Verwenden Sie das gleiche Sternchen (*)-Schema wie im Text.'
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
          (task: DragTheWordsTask) => {
            task.template = data;
          }
        ),
        undoBatch: 'taskDefinition.template',
      });
    },

    updateTaskDefinition(undoBatch?: unknown) {
      // Synchronize state modelTaskDefinition -> taskDefinition.
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
          (taskDraft: DragTheWordsTask) => {
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
          (taskDraft: DragTheWordsTask) => {
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

<style scoped></style>
