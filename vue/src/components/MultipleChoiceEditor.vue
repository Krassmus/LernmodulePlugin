<template>
  <!--  The current task: {{ task }}-->
  <div>
    <form class="default">
      <fieldset>
        <legend>{{ $gettext('Multiple Choice Frage') }}</legend>
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
          </div>
        </label>
        <button type="button" @click="addAnswer">
          {{ $gettext('Neue Antwort') }}
        </button>
      </fieldset>
      <fieldset class="collapsable">
        <legend>Einstellungen</legend>
        <div>
          <label>
            {{
              $gettext(
                'Mehrere Antworten können gleichzeitig ausgewählt werden'
              )
            }}
            <input type="checkbox" v-model="taskDefinition.canAnswerMultiple" />
          </label>
          <label>
            {{ $gettext('Mehrere Versuche erlauben') }}
            <input type="checkbox" v-model="taskDefinition.retryAllowed" />
          </label>
        </div>
      </fieldset>
    </form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MultipleChoiceTaskDefinition } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';

export default defineComponent({
  name: 'MultipleChoiceEditor',
  props: {},
  methods: {
    addAnswer(): void {
      this.taskDefinition.answers.push({
        text: this.$gettext('Neue Antwort'),
        correct: true,
      });
    },
  },
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as MultipleChoiceTaskDefinition,
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
  border: 1px solid red;
  width: 100%;
  max-width: 48em;
}

.flex-child-element {
  flex: 1;
  border: 1px solid blueviolet;
  margin: 0px 0px 0px 0px;
  padding: 0px 0px 0px 0px;
  /*vertical-align: center;*/
  /*text-align: center;*/
}

.flex-child-element:first-child {
  flex: none;
}

.checkbox {
  margin: 11px 1px 0px 0px;
}

.textbox {
  margin: 0px 0px 0px 0px;
  padding: 0px 0px 0px 0px;
}
</style>
