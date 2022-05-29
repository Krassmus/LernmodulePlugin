<template>
  <input
    v-for="(template, i) in taskDefinition.templates"
    :key="i"
    type="text"
    :value="template"
    @input="(ev) => onEditTemplate(ev, i)"
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
  },
  methods: {
    onEditTemplate(event: InputEvent, templateIndex: number) {
      const newTemplates = [...this.taskDefinition.templates];
      newTemplates[templateIndex] = (event.target as HTMLInputElement).value;
      taskEditorStore.performEdit({
        taskDefinition: {
          ...this.taskDefinition,
          templates: newTemplates,
        },
        undoBatch: { type: 'editTemplate', templateIndex },
      });
    },
    addSubtask() {
      const newTemplates = [...this.taskDefinition.templates, ''];
      taskEditorStore.performEdit({
        taskDefinition: {
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
