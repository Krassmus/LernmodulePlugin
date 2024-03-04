<template>
  <div class="add-interactions-root">
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
        class="button tan3"
        @click="insertOverlay"
        :title="$gettext('Overlay')"
      ></button>
      <button
        v-for="taskType in taskTypes"
        :key="taskType"
        type="button"
        class="button"
        :class="iconForTaskType(taskType)"
        @click="insertLmbTaskInteraction(taskType)"
        :title="printTaskType(taskType)"
      ></button>
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
    <SelectedInteractionProperties
      v-if="selectedInteraction"
      ref="selectedInteractionProperties"
      :selectedInteraction="selectedInteraction"
    />
  </div>
</template>

<style scoped lang="scss">
.insert-interactions-buttons button {
  // Make the buttons into little squares so a lot of them will fit next to each
  // other in one row.
  min-width: unset;
  width: 0;
}
.add-interactions-root {
  overflow: hidden;
}
.video-timeline {
  margin-top: 2em;
}

.selected-interaction-properties {
  margin-top: 1em;
}
</style>

<script setup lang="ts">
import { computed, defineProps, nextTick, PropType, provide, ref } from 'vue';
import type {
  Interaction,
  InteractiveVideoTask,
  OverlayInteraction,
} from '@/models/InteractiveVideoTask';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import VideoTimeline from '@/components/interactiveVideo/VideoTimeline.vue';
import SelectedInteractionProperties from '@/components/interactiveVideo/SelectedInteractionProperties.vue';
import { VideoMetadata } from '@/components/interactiveVideo/events';
import {
  iconForTaskType,
  newTask,
  printTaskType,
  TaskDefinition,
} from '@/models/TaskDefinition';
import { v4 } from 'uuid';
import { editorStateSymbol } from '@/components/interactiveVideo/editorState';
import { $gettext } from '../../language/gettext';

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
const selectedInteractionProperties = ref<
  InstanceType<typeof SelectedInteractionProperties> | undefined
>(undefined);

const taskTypes: Array<TaskDefinition['task_type']> = [
  'FillInTheBlanks',
  'DragTheWords',
  'MarkTheWords',
  'Question',
];

provide(editorStateSymbol, {
  selectInteraction,
  selectedInteractionId,
  editInteraction,
  dragInteraction,
  resizeOverlay,
  deleteInteraction,
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
function editInteraction(id: string) {
  selectedInteractionId.value = id;
  nextTick(() => {
    const el = selectedInteractionProperties.value?.$el as HTMLElement;
    el.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    });
  });
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
function insertOverlay() {
  const interaction: OverlayInteraction = {
    type: 'overlay',
    id: v4(),
    startTime: currentTime.value,
    endTime: Math.min(videoMetadata.value.length, currentTime.value + 10),
    x: 0.4,
    y: 0.4,
    width: 0.2,
    height: 0.2,
    text: $gettext('Overlay'),
    pauseWhenVisible: true,
  };
  // TODO make undoable ?
  // eslint-disable-next-line vue/no-mutating-props
  props.taskDefinition.interactions.push(interaction);
  selectedInteractionId.value = interaction.id;
}

function insertLmbTaskInteraction(type: TaskDefinition['task_type']) {
  console.log('insertLmbTaskInteraction', type);
  const task = newTask(type);
  const interaction: Interaction = {
    type: 'lmbTask',
    id: v4(),
    taskDefinition: task,
    startTime: currentTime.value,
    endTime: Math.min(videoMetadata.value.length, currentTime.value + 10),
    x: 0.5,
    y: 0.5,
    pauseWhenVisible: true,
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
function resizeOverlay(
  id: string,
  x: number,
  y: number,
  width: number,
  height: number
) {
  const interaction = props.taskDefinition?.interactions.find(
    (i) => i.id === id
  );
  if (!interaction) {
    throw new Error(`Interaction with id ${id} not found`);
  }
  // TODO make undoable ?
  interaction.x = x;
  interaction.y = y;
  interaction.width = width;
  interaction.height = height;
}
function dragInteractionTimeline(id: string, startTime: number) {
  const interaction = props.taskDefinition?.interactions.find(
    (i) => i.id === id
  );
  if (!interaction) {
    throw new Error(`Interaction with id ${id} not found`);
  }
  // TODO make undoable ?
  const duration = interaction.endTime - interaction.startTime;
  interaction.endTime = startTime + duration;
  interaction.startTime = startTime;
}
</script>
