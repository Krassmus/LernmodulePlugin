<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Question') }}</legend>

      <label>
        <studip-wysiwyg
          v-model="taskDefinition.question"
          force-soft-breaks
          remove-wrapping-p-tag
          disable-autoformat
        />
      </label>

      <fieldset
        class="collapsable collapsed"
        v-for="(answer, i) in taskDefinition.answers"
        :key="answer.uuid"
      >
        <legend>{{ answer.text }}</legend>

        <div class="flex-parent-element">
          <input
            class="flex-child-element checkbox"
            type="checkbox"
            v-model="taskDefinition.answers[i].correct"
          />

          <input
            class="flex-child-element textbox"
            type="text"
            v-model="taskDefinition.answers[i].text"
          />

          <button
            type="button"
            class="flex-child-element remove-answer-button"
            @click="removeAnswer(answer)"
            :aria-label="$gettext('Antwort löschen')"
            :title="$gettext('Antwort löschen')"
          >
            <img :src="urlForIcon('trash')" alt="" />
          </button>
        </div>

        <fieldset class="collapsable collapsed feedback">
          <legend>{{ $gettext('Hinweis und Feedback') }}</legend>

          <label>
            <span>{{ $gettext('Hinweis') }}</span>
            <input
              class="textbox"
              type="text"
              v-model="taskDefinition.answers[i].strings.hint"
            />
          </label>

          <label>
            <span>{{ $gettext('Feedback, wenn ausgewählt') }}</span>
            <input
              class="textbox"
              type="text"
              v-model="taskDefinition.answers[i].strings.feedbackSelected"
            />
          </label>

          <label>
            <span>{{ $gettext('Feedback, wenn nicht ausgewählt') }}</span>
            <input
              class="textbox"
              type="text"
              v-model="taskDefinition.answers[i].strings.feedbackNotSelected"
            />
          </label>
        </fieldset>
      </fieldset>

      <button type="button" class="button" @click="addAnswer">
        {{ $gettext('Neue Antwort') }}
      </button>
    </fieldset>

    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Einstellungen') }}</legend>

      <label>
        <input v-model="taskDefinition.canAnswerMultiple" type="checkbox" />
        {{ $gettext('Mehrfachauswahl erlauben') }}
      </label>

      <label>
        <input v-model="taskDefinition.randomOrder" type="checkbox" />
        {{ $gettext('Antworten in zufälliger Reihenfolge anzeigen') }}
      </label>

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
import {
  Feedback,
  QuestionAnswer,
  QuestionTask,
} from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import { $gettext } from '@/language/gettext';
import produce from 'immer';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import FeedbackEditor from '@/components/FeedbackEditor.vue';
import { v4 } from 'uuid';

export default defineComponent({
  name: 'QuestionEditor',
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  components: { FeedbackEditor, StudipWysiwyg },
  methods: {
    $gettext,

    addAnswer(): void {
      this.taskDefinition.answers.push({
        uuid: v4(),
        text: this.$gettext('Neue Antwort'),
        correct: true,
        strings: {
          hint: '',
          feedbackSelected: '',
          feedbackNotSelected: '',
        },
      });
    },

    removeAnswer(answerToRemove: QuestionAnswer): void {
      this.taskDefinition.answers = this.taskDefinition.answers.filter(
        (answer) => answer !== answerToRemove
      );
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
          (taskDraft: QuestionTask) => {
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
          (taskDraft: QuestionTask) => {
            taskDraft.strings.resultMessage = updatedResultMessage;
          }
        ),
        undoBatch: {},
      });
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as QuestionTask,

    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],
  },
});
</script>

<style scoped>
.flex-parent-element {
  display: flex;
  width: 100%;
  max-width: 480em;
  align-items: center;
  justify-content: flex-start;
  padding-bottom: 1ex;
}

.flex-child-element {
  margin-right: 0.25em;
}

.remove-answer-button {
  align-self: stretch; /* Make the item fill the available height */
  display: flex;
  align-items: center;
}

.feedback {
  margin: 0.5em 0 0 0;
}
</style>
