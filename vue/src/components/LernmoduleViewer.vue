<template>
  <h1>Variables passed from server:</h1>
  <pre>{{ LernmoduleVueJS }}</pre>
  <h1>Viewer</h1>
  <component
    :is="viewerForTaskType(taskDefinition.task_type)"
    :task="taskDefinition"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { TaskDefinition, viewerForTaskType } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'LernmoduleViewer',
  computed: {
    LernmoduleVueJS: () => window.STUDIP.LernmoduleVueJS,
    // TODO: Warning!! Bad!! You should parse the contents, do not just type-cast!!
    taskDefinition: () =>
      window.STUDIP.LernmoduleVueJS.module.customdata as TaskDefinition,
  },
  methods: {
    viewerForTaskType,
  },
});
</script>

<style scoped></style>
