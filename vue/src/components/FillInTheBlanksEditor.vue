<template>
  Current undo redo state:
  <pre>{{ currentUndoRedoState }}</pre>
  <textarea
    :value="taskDefinition.template"
    @input="(ev) => onEditTemplate(ev)"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { FillInTheBlanksDefinition } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';

export default defineComponent({
  name: 'FillInTheBlanksEditor',
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as FillInTheBlanksDefinition,
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],
  },
  methods: {
    onEditTemplate(event: InputEvent) {
      const newValue = (event.target as HTMLInputElement).value;
      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...this.taskDefinition,
          template: newValue,
        },
        undoBatch: { type: 'editFillInTheBlanksTemplate' },
      });
    },
  },
});
</script>

<style scoped></style>
