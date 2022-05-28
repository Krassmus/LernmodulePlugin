<template>
  <h1>Variables passed from server:</h1>
  <pre>{{ LernmoduleVueJS }}</pre>
  <h1>Viewer</h1>
  <component
    :is="componentForTaskType(taskDefinition.task_type)"
    :task="taskDefinition"
  />
</template>

<script lang="ts">
import { Component, defineComponent } from 'vue';
import { TaskDefinition } from '@/models/TaskDefinition';
import FillInTheBlanks from '@/components/FillInTheBlanks.vue';

export default defineComponent({
  name: 'LernmoduleViewer',
  computed: {
    LernmoduleVueJS: () => window.STUDIP.LernmoduleVueJS,
    // TODO: Warning!! Bad!! You should parse the contents, do not just type-cast!!
    taskDefinition: () =>
      window.STUDIP.LernmoduleVueJS.module.customdata as TaskDefinition,
  },
  methods: {
    componentForTaskType(type: TaskDefinition['task_type']): Component {
      switch (type) {
        case 'FillInTheBlanks':
          return FillInTheBlanks;
        default:
          throw new Error('Unimplemented task type: ' + type);
      }
    },
  },
});
</script>

<style scoped></style>
