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
          <input type="checkbox" v-model="taskDefinition.answers[i].correct" />
          <input type="text" v-model="taskDefinition.answers[i].text" />
        </label>
        <button type="button" @click="addAnswer">
          {{ $gettext('Neue Antwort') }}
        </button>
      </fieldset>
    </form>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
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

<style scoped></style>
