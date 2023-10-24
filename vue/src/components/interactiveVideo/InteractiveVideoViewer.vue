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
  <div>InteractiveVideoViewer</div>
  <VideoPlayer v-if="task.video.type === 'youtube'" :url="task.video.url" />
</template>

<style scoped></style>
