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
          {{ $gettext('Antwort') }}
          <div class="flex-parent-element">
            <div class="flex-child-element">
              <input
                type="checkbox"
                v-model="taskDefinition.answers[i].correct"
                class="checkbox"
              />
            </div>
            <div class="flex-child-element">
              <input
                type="text"
                v-model="taskDefinition.answers[i].text"
                class="textbox"
              />
            </div>
            <div class="flex-child-element">
              <button
                type="button"
                @click="removeAnswer(answer)"
                class="removeAnswerButton"
              >
                X
              </button>
            </div>
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
        </div>
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
.parent {
  border: 1px solid black;
  margin: 0rem;
  padding: 0rem 0rem;
  text-align: left;
}
.child {
  display: inline-block;
  border: 1px solid red;
  padding: 0rem 0rem;
  vertical-align: middle;
}

.float-parent-element {
  width: 50%;
}
.float-child-element {
  float: left;
  width: 50%;
}

.flex-parent-element {
  display: flex;
  border: 1px solid black;
  width: 100%;
  max-width: 48em;
}

.flex-child-element {
  flex: 1;
  border: 1px solid #1d75b3;
  margin: 0px 0px 0px 0px;
  padding: 0px 0px 0px 0px;
  /*vertical-align: center;*/
  /*text-align: center;*/
}

.flex-child-element:first-child {
  flex: none;
  /*border: 1px solid red;*/
}

.flex-child-element:last-child {
  flex: none;
  /*border: 1px solid #00a8c6;*/
}

.checkbox {
  margin: 11px 1px 0px 0px;
}

.textbox {
  margin: 0px 0px 0px 0px;
  padding: 0px 0px 0px 0px;
}

.h5pBehaviorSetting-disabled {
  opacity: 50%;
}
</style>
