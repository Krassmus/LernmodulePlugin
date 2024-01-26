<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';

export default defineComponent({
  name: 'InteractiveVideoViewer',
  components: { VideoPlayer },
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
  <VideoPlayer :task="task" />
</template>

<style scoped></style>
