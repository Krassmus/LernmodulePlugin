<template>
  Current undo redo state:
  <pre>{{ currentUndoRedoState }}</pre>
  <template v-for="(template, index) in taskDefinition.templates" :key="index">
    <input
      type="text"
      :value="template"
      @input="(ev) => onEditTemplate(ev, index)"
    />
    <button @click="deleteTemplate(index)">Delete</button>
  </template>
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
    deleteTemplate(templateIndex: number) {
      const oldTemplates = this.taskDefinition.templates;
      const newTemplates = oldTemplates
        .slice(0, templateIndex)
        .concat(oldTemplates.slice(templateIndex + 1, oldTemplates.length));
      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...this.taskDefinition,
          templates: newTemplates,
        },
        undoBatch: {},
      });
    },
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
