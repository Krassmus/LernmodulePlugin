<script lang="ts">
import { defineComponent } from 'vue';
import { taskEditorStore } from '@/store';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';

export default defineComponent({
  name: 'InteractiveVideoEditor',
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as InteractiveVideoTask,
  },
});
</script>

<template>
  <div>Editor for interactive video</div>
  <div>
    Task definition:
    <pre>{{ taskDefinition }}</pre>
  </div>
</template>

<style scoped></style>
