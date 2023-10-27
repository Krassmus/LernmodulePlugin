<template>
  <VideoPlayer
    ref="videoPlayer"
    :video="taskDefinition.video"
    @timeupdate="onTimeUpdate"
    @metadataChange="onVideoMetadataChange"
  />
  <VideoTimeline
    class="video-timeline"
    :currentTime="currentTime"
    :videoMetadata="videoMetadata"
    @on-timeline-seek="onTimelineSeek"
  />
</template>

<style scoped>
.video-timeline {
  margin-top: 2em;
}
</style>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import VideoTimeline from '@/components/interactiveVideo/VideoTimeline.vue';
import { VideoMetadata } from '@/components/interactiveVideo/events';

export default defineComponent({
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
      videoMetadata: { length: 1 } as VideoMetadata,
    };
  },
  methods: {
    onVideoMetadataChange(data: VideoMetadata) {
      this.videoMetadata = data;
    },
    onTimeUpdate(time: number) {
      this.currentTime = time;
    },
    onTimelineSeek(time: number) {
      console.log('onTImelineSeek', time);
    },
  },
});
</script>
