<template>
  Current undo redo state:
  <pre>{{ currentUndoRedoState }}</pre>
  <input
    v-for="(template, index) in taskDefinition.templates"
    :key="index"
    type="text"
    :value="template"
    @input="(ev) => onEditTemplate(ev, index)"
  />
  <button @click="addSubtask">Add subtask</button>
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
    onEditTemplate(event: InputEvent, templateIndex: number) {
      const newTemplates = [...this.taskDefinition.templates];
      newTemplates[templateIndex] = (event.target as HTMLInputElement).value;
      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...this.taskDefinition,
          templates: newTemplates,
        },
        undoBatch: { type: 'editTemplate', templateIndex },
      });
    },
    addSubtask() {
      const newTemplates = [...this.taskDefinition.templates, ''];
      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...this.taskDefinition,
          templates: newTemplates,
        },
        undoBatch: {},
      });
    },
  },
});
</script>

<style scoped></style>
