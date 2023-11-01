<template>
  <VideoPlayer
    ref="videoPlayer"
    :video="taskDefinition.video"
    @timeupdate="onTimeUpdate"
    @metadataChange="onVideoMetadataChange"
  />
  <div class="insert-interactions-buttons">
    <button
      type="button"
      class="button file-office"
      @click="insertInteraction('FillInTheBlanks')"
    ></button>
  </div>
  <VideoTimeline
    class="video-timeline"
    :currentTime="currentTime"
    :videoMetadata="videoMetadata"
    @timelineSeek="onTimelineSeek"
  />
</template>

<style scoped lang="scss">
@use '../../../../../../../../resources/assets/stylesheets/scss/variables' as *;
@use '../../../../../../../../resources/assets/stylesheets/scss/buttons' as
  buttons;

.button.file-office {
  @include buttons.button-with-icon(file-office, clickable, info_alt);
}

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
import { TaskDefinition } from '@/models/TaskDefinition';

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
      (
        this.$refs.videoPlayer as InstanceType<typeof VideoPlayer>
      ).player!.currentTime(time);
    },
    insertInteraction(type: TaskDefinition['task_type']) {
      console.log('insertInteraction', type);
    },
  },
});
</script>
