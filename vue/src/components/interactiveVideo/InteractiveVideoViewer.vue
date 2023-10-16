<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';

export default defineComponent({
  name: 'InteractiveVideoViewer',
  props: {
    task: {
      type: Object as PropType<InteractiveVideoTask>,
      required: true,
    },
  },
  data() {
    return {
      counter: 0,
    };
  },
});
</script>

<template>
  <div>InteractiveVideoViewer</div>
</template>

<style scoped></style>
