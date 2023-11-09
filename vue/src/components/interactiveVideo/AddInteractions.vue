<template>
  <VideoPlayer
    ref="videoPlayer"
    :task="taskDefinition"
    @timeupdate="onTimeUpdate"
    @metadataChange="onVideoMetadataChange"
    @clickInteraction="(i: Interaction) => selectInteraction(i.id)"
  >
    <template v-for="interaction in visibleInteractions" :key="interaction.id">
      <button
        v-if="interaction.type === 'lmbTask'"
        type="button"
        class="video-player-overlay"
        :style="{
          left: `${interaction.x * 100}%`,
          top: `${interaction.y * 100}%`,
        }"
        @click="selectInteraction(interaction.id)"
      >
        <div>{{ interaction.taskDefinition.task_type }}</div>
      </button>
    </template>
  </VideoPlayer>
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
    @clickInteraction="(i: Interaction) => selectInteraction(i.id)"
    @deleteInteraction="deleteInteraction"
  />
  <form
    v-if="selectedInteraction"
    class="default selected-interaction-properties"
  >
    <fieldset>
      <legend>
        {{ printInteractionType(selectedInteraction) }}
        {{ $gettext('bearbeiten') }}
      </legend>
      <label v-if="selectedInteraction.type === 'pause'">
        {{ $gettext('Zeitpunkt') }}
        <input type="number" v-model="selectedInteraction.startTime" />
      </label>
      <label v-else>
        {{ $gettext('Start') }}
        <input type="number" v-model="selectedInteraction.startTime" />
        {{ $gettext('Ende') }}
        <input type="number" v-model="selectedInteraction.endTime" />
      </label>
    </fieldset>
    <component
      v-if="selectedInteraction.type === 'lmbTask'"
      :key="selectedInteraction.id"
      :is="editorForTaskType(selectedInteraction.taskDefinition.task_type)"
      :taskDefinition="selectedInteraction.taskDefinition"
    >
    </component>
  </form>
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

.selected-interaction-properties {
  margin-top: 2em;
}

.video-player-overlay {
  position: absolute;
  background: white;
  aspect-ratio: 1/1;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  Interaction,
  InteractiveVideoTask,
  printInteractionType,
} from '@/models/InteractiveVideoTask';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import VideoTimeline from '@/components/interactiveVideo/VideoTimeline.vue';
import { VideoMetadata } from '@/components/interactiveVideo/events';
import {
  editorForTaskType,
  newTask,
  TaskDefinition,
} from '@/models/TaskDefinition';
import { v4 } from 'uuid';
import { $gettext } from '../../language/gettext';

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
    selectedInteraction(): Interaction | undefined {
      return this.taskDefinition.interactions.find(
        (interaction) => interaction.id === this.selectedInteractionId
      );
    },
    /**
     * @return a list of Interaction objects whose clickable icons (or other elements)
     * should be shown overlaid over the video at the timestamp given by this.time.
     */
    visibleInteractions(): Interaction[] {
      return this.taskDefinition.interactions.filter((i) => {
        const endTime = i.type === 'pause' ? i.startTime + 1 : i.endTime;
        return i.startTime <= this.currentTime && endTime > this.currentTime;
      });
    },
  },
  methods: {
    printInteractionType,
    $gettext,
    editorForTaskType,
    selectInteraction(selectionId: string) {
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
        x: 0.5,
        y: 0.5,
      };
      // TODO make undoable ?
      // eslint-disable-next-line vue/no-mutating-props
      this.taskDefinition.interactions.push(interaction);
      this.selectedInteractionId = interaction.id;
    },
    deleteInteraction(id: string) {
      console.log('deleteInteraction', id);
      const index = this.taskDefinition.interactions.findIndex(
        (i) => i.id === id
      );
      // eslint-disable-next-line vue/no-mutating-props
      this.taskDefinition.interactions.splice(index, 1);
    },
  },
});
</script>
