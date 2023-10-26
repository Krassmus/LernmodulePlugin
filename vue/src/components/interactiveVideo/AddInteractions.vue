<template>
  <VideoPlayer :video="taskDefinition.video" v-model:time="currentTime" />
  <VideoTimeline
    :current-time="currentTime"
    :video-length="videoLength"
    @on-timeline-seek="onTimelineSeek"
  />
</template>
<script lang="ts">
import { PropType } from 'vue';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import VideoTimeline from '@/components/interactiveVideo/VideoTimeline.vue';

export default {
  name: 'AddInteractions',
  components: { VideoTimeline, VideoPlayer },
  props: {
    taskDefinition: {
      type: Object as PropType<InteractiveVideoTask>,
      required: true,
    },
  },
  data() {
    return {
      currentTime: 0,
      videoLength: 1,
    };
  },
  methods: {
    onTimelineSeek(time: number) {
      console.log('onTImelineSeek', time);
    },
  },
};
</script>
