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
      class="button add"
      @click="insertInteraction('FillInTheBlanks')"
    >
      Fill In The Blanks
    </button>
  </div>
  <VideoTimeline
    class="video-timeline"
    :task="taskDefinition"
    :currentTime="currentTime"
    :videoMetadata="videoMetadata"
    :selectedInteractionId="selectedInteractionId"
    @timelineSeek="onTimelineSeek"
    @interactionSelected="onInteractionSelected"
  />
  <div v-if="selectedInteractionId">
    Selected interaction:
    <pre>{{ selectedInteraction }}</pre>
  </div>
</template>

<style scoped lang="scss">
//@use '../../../../../../../../resources/assets/stylesheets/scss/buttons' as
//  buttons;
//
//.button.file-office {
//  @include buttons.button-with-icon(file-office, clickable, info_alt);
//}

.video-timeline {
  margin-top: 2em;
}
</style>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  Interaction,
  InteractiveVideoTask,
} from '@/models/InteractiveVideoTask';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import VideoTimeline from '@/components/interactiveVideo/VideoTimeline.vue';
import { VideoMetadata } from '@/components/interactiveVideo/events';
import { newTask, TaskDefinition } from '@/models/TaskDefinition';
import { v4 } from 'uuid';

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
      selectedInteractionId: undefined as string | undefined,
    };
  },
  computed: {
    selectedInteraction() {
      return this.taskDefinition.interactions.find(
        (interaction) => interaction.id === this.selectedInteractionId
      );
    },
  },
  methods: {
    onInteractionSelected(selectionId: string) {
      this.selectedInteractionId = selectionId;
    },
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
      const task = newTask(type);
      const interaction: Interaction = {
        type: 'lmbTask',
        id: v4(),
        taskDefinition: task,
        startTime: this.currentTime,
        endTime: Math.min(this.videoMetadata.length, this.currentTime + 10),
      };
      // TODO make undoable ?
      // eslint-disable-next-line vue/no-mutating-props
      this.taskDefinition.interactions.push(interaction);
    },
  },
});
</script>
