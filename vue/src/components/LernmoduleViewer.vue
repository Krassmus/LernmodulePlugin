<template>
  <!--  <h1>Variables passed from server:</h1>-->
  <!--  <pre>{{ LernmoduleVueJS }}</pre>-->
  <!--  <h1>Viewer</h1>-->
  <component
    :is="viewerForTaskType(taskDefinition.task_type)"
    :task="taskDefinition"
    @updateAttempt="onUpdateAttempt"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  taskDefinitionSchema,
  viewerForTaskType,
} from '@/models/TaskDefinition';
import { updateAttempt } from '@/routes/lernmodule';

export default defineComponent({
  name: 'LernmoduleViewer',
  computed: {
    LernmoduleVueJS: () => window.STUDIP.LernmoduleVueJS,
    taskDefinition: () =>
      taskDefinitionSchema.parse(
        window.STUDIP.LernmoduleVueJS.module.customdata
      ),
  },
  methods: {
    viewerForTaskType,
    onUpdateAttempt(payload: {
      points: Record<string, number>;
      success: boolean;
    }) {
      console.log(`onUpdateAttempt.  ${JSON.stringify(payload)}`);
      updateAttempt(payload.points, payload.success);
    },
  },
});
</script>

<style scoped></style>
