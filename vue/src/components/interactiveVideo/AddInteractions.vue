<template>
  <VideoPlayer
    ref="videoPlayer"
    :task="taskDefinition"
    @timeupdate="onTimeUpdate"
    @metadataChange="onVideoMetadataChange"
    @clickInteraction="(i: Interaction) => selectInteraction(i.id)"
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
    <KeepAlive>
      <component
        v-if="selectedInteraction.type === 'lmbTask'"
        :key="selectedInteraction.id"
        :is="editorForTaskType(selectedInteraction.taskDefinition.task_type)"
        :taskDefinition="selectedInteraction.taskDefinition"
      >
      </component>
    </KeepAlive>
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
</style>

<script setup lang="ts">
import { computed, defineProps, PropType, provide, ref } from 'vue';
import type {
  Interaction,
  InteractiveVideoTask,
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
import {
  EditorState,
  editorStateSymbol,
} from '@/components/interactiveVideo/editorState';
import { printInteractionType } from '@/models/InteractiveVideoTask';

const props = defineProps({
  taskDefinition: {
    type: Object as PropType<InteractiveVideoTask>,
    required: true,
  },
});
const currentTime = ref(0);
const videoMetadata = ref<VideoMetadata>({ length: 1 });
const selectedInteractionId = ref<string | undefined>(undefined);
const videoPlayer = ref<InstanceType<typeof VideoPlayer> | undefined>(
  undefined
);

provide(editorStateSymbol, {
  selectInteraction,
  selectedInteractionId,
  dragInteraction,
  dragInteractionTimeline,
});

const selectedInteraction = computed(() =>
  props.taskDefinition.interactions.find(
    (interaction) => interaction.id === selectedInteractionId.value
  )
);

function selectInteraction(selectionId: string) {
  selectedInteractionId.value = selectionId;
}
function onVideoMetadataChange(data: VideoMetadata) {
  videoMetadata.value = data;
}
function onTimeUpdate(time: number) {
  currentTime.value = time;
}
function onTimelineSeek(time: number) {
  console.log('onTImelineSeek', time);
  videoPlayer.value!.player!.currentTime(time);
}
function insertInteraction(type: TaskDefinition['task_type']) {
  console.log('insertInteraction', type);
  const task = newTask(type);
  const interaction: Interaction = {
    type: 'lmbTask',
    id: v4(),
    taskDefinition: task,
    startTime: currentTime.value,
    endTime: Math.min(videoMetadata.value.length, currentTime.value + 10),
    x: 0.5,
    y: 0.5,
  };
  // TODO make undoable ?
  // eslint-disable-next-line vue/no-mutating-props
  props.taskDefinition.interactions.push(interaction);
  selectedInteractionId.value = interaction.id;
}
function deleteInteraction(id: string) {
  console.log('deleteInteraction', id);
  const index = props.taskDefinition.interactions.findIndex((i) => i.id === id);
  // TODO make undoable... Don't want to delete a whole task permanently with no undo
  // eslint-disable-next-line vue/no-mutating-props
  props.taskDefinition.interactions.splice(index, 1);
}
function dragInteraction(id: string, xFraction: number, yFraction: number) {
  const interaction = props.taskDefinition?.interactions.find(
    (i) => i.id === id
  );
  if (!interaction) {
    throw new Error(`Interaction with id ${id} not found`);
  }
  // TODO make undoable ?
  interaction.x = xFraction;
  interaction.y = yFraction;
}
function dragInteractionTimeline(id: string, startTime: number) {
  const interaction = props.taskDefinition?.interactions.find(
    (i) => i.id === id
  );
  if (!interaction) {
    throw new Error(`Interaction with id ${id} not found`);
  }
  // TODO make undoable ?
  interaction.startTime = startTime;
}
</script>
