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
import { TaskDefinition, viewerForTaskType } from '@/models/TaskDefinition';
import { updateAttempt } from '@/routes';

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
    onUpdateAttempt(payload: {
      points: Record<string, number>;
      success: boolean;
    }) {
      console.log(`onUpdateAttempt.  ${JSON.stringify(payload)}`);
      const attemptId = window.STUDIP.LernmoduleVueJS.attemptId;
      if (!attemptId) {
        // TODO flash an error
        console.warn(
          'LernmoduleVueJS.attemptId is undefined.  ' +
            'Your progress in this Lernmodul will not be saved.'
        );
        return;
      }
      updateAttempt(attemptId, payload.points, payload.success);
    },
  },
});
</script>

<style scoped></style>
