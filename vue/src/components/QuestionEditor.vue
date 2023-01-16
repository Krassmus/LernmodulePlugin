<template>
  <!--  The current task: {{ task }}-->
  <div>
    <form class="default">
      <fieldset>
        <legend>{{ $gettext('Frage') }}</legend>
        <label>
          {{ $gettext('Frage') }}
          <input type="text" v-model="taskDefinition.question" />
        </label>
        <label v-for="(answer, i) in taskDefinition.answers" :key="i">
          {{ $gettext('Antwort') }} {{ i + 1 }}
          <div class="flex-parent-element">
            <input
              class="flex-child-element, checkbox"
              type="checkbox"
              v-model="taskDefinition.answers[i].correct"
            />
            <input
              class="flex-child-element, textbox"
              type="text"
              v-model="taskDefinition.answers[i].text"
            />
            <button
              class="flex-child-element, removeAnswerButton"
              type="button"
              @click="removeAnswer(answer)"
            >
              X
            </button>
          </div>
        </label>
        <button type="button" @click="addAnswer">
          {{ $gettext('Neue Antwort') }}
        </button>
      </fieldset>
      <fieldset class="collapsable">
        <legend>{{ $gettext('Einstellungen') }}</legend>
        <h1>{{ $gettext('Generell') }}</h1>
        <label>
          <input type="checkbox" v-model="taskDefinition.canAnswerMultiple" />
          {{
            $gettext('Mehrere Antworten können gleichzeitig ausgewählt werden')
          }}
        </label>

        <label>
          <input type="checkbox" v-model="taskDefinition.randomOrder" />
          {{ $gettext('Zeige Antworten in zufälliger Reihenfolge') }}
        </label>

        <h1>{{ $gettext('Versuche') }}</h1>
        <label>
          <input type="checkbox" v-model="taskDefinition.retryAllowed" />
          {{ $gettext('Mehrere Versuche erlauben') }}
        </label>
        <label
          :class="
            taskDefinition.retryAllowed ? '' : 'h5pBehaviorSetting-disabled'
          "
          >{{ $gettext('Text im Button:') }}

          <input
            type="text"
            :disabled="!taskDefinition.retryAllowed"
            v-model="taskDefinition.strings.retryButton"
        /></label>
        <h1>{{ $gettext('Lösungen') }}</h1>
        <label>
          <input
            type="checkbox"
            v-model="taskDefinition.showSolutionsAllowed"
          />
          {{ $gettext('Lösungen können angezeigt werden') }}
        </label>
        <label
          :class="
            taskDefinition.showSolutionsAllowed
              ? ''
              : 'h5pBehaviorSetting-disabled'
          "
          >{{ $gettext('Text im Button:') }}

          <input
            type="text"
            :disabled="!taskDefinition.showSolutionsAllowed"
            v-model="taskDefinition.strings.solutionsButton"
        /></label>
      </fieldset>
    </form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  QuestionAnswer,
  QuestionTaskDefinition,
} from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';

export default defineComponent({
  name: 'QuestionEditor',
  props: {},
  methods: {
    addAnswer(): void {
      this.taskDefinition.answers.push({
        text: this.$gettext('Neue Antwort'),
        correct: true,
        hint: '',
      });
    },

    removeAnswer(answerToRemove: QuestionAnswer): void {
      this.taskDefinition.answers = this.taskDefinition.answers.filter(
        (answer) => answer !== answerToRemove
      );
    },
  },
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as QuestionTaskDefinition,
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],
  },
});
</script>

<style scoped>
.flex-parent-element {
  display: flex;
  width: 100%;
  max-width: 48em;
  align-items: center;
}

/*.flex-child-element {*/
/*  flex: 1;*/
/*  border: 4px solid #1d75b3;*/
/*}*/

/*.flex-child-element:first-child {*/
/*  flex: none;*/
/*}*/

/*.flex-child-element:last-child {*/
/*  flex: none;*/
/*}*/

input[type='text'].textbox.textbox {
  margin: 0px 0px 0px 0px;
  padding: 0px 0px 0px 0px;
}

input[type='checkbox'].checkbox.checkbox {
  margin: 0px 0px 0px 0px;
  padding: 0px 0px 0px 0px;
}

/*button[type='button'] {*/
/*  margin: 10px 0px 0px 0px;*/
/*  padding: 10px 0px 0px 0px;*/
/*}*/

.h5pBehaviorSetting-disabled {
  opacity: 50%;
}
</style>
