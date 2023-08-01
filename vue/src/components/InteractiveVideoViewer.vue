<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { InteractiveVideoTask } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'InteractiveVideoViewer',
  props: {
    task: {
      type: Object as PropType<InteractiveVideoTask>,
      required: true,
    },
  },
});
</script>

<template><div>InteractiveVideoViewer</div></template>

<style scoped></style>
