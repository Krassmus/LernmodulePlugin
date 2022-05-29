<template>
  <input type="text" :value="taskDefinition.template" @input="onEditTemplate" />
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
    onEditTemplate(event: InputEvent) {
      taskEditorStore.setTaskDefinition(
        {
          ...this.taskDefinition,
          template: (event.target as HTMLInputElement).value,
        },
        { type: 'editTemplate' }
      );
    },
  },
});
</script>

<style scoped></style>
