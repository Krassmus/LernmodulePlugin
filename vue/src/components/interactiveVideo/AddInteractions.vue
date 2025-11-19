<template>
  <div class="add-interactions-root">
    <VideoPlayer
      ref="videoPlayer"
      :task="taskDefinition"
      @timeupdate="onTimeUpdate"
      @metadataChange="onVideoMetadataChange"
      @clickInteraction="(i: Interaction) => selectInteraction(i.id)"
    />
    <div class="toolbar-under-video">
      <div class="insert-interactions-buttons">
        <button
          type="button"
          class="button item"
          @click="insertOverlay"
          :title="
            $gettext('%{ interactionType } einfügen', {
              interactionType: $gettext('Einblendung'),
            })
          "
        ></button>
        <button
          v-for="taskType in taskTypes"
          :key="taskType"
          type="button"
          class="button"
          :class="iconForTaskType(taskType)"
          @click="insertLmbTaskInteraction(taskType)"
          :title="
            $gettext('%{ interactionType } einfügen', {
              interactionType: printTaskType(taskType),
            })
          "
        ></button>
      </div>
      <div class="video-controls">
        <button
          type="button"
          class="button zoom-in"
          :title="$gettext('Vergrößern')"
          @click="onClickZoomIn"
        ></button>
        <button
          type="button"
          class="button zoom-out"
          :title="$gettext('Verkleinern')"
          @click="onClickZoomOut"
        ></button>
        <button
          type="button"
          class="button play"
          :title="$gettext('Abspielen')"
          @click="onClickPlay"
        ></button>
        <button
          type="button"
          class="button pause"
          :title="$gettext('Pausieren')"
          @click="onClickPause"
        ></button>
      </div>
    </div>

    <VideoTimeline
      class="video-timeline"
      ref="videoTimeline"
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
.toolbar-under-video {
  display: flex;
  justify-content: space-between;
}
.insert-interactions-buttons button,
.video-controls button {
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
import {
  computed,
  defineProps,
  inject,
  nextTick,
  PropType,
  provide,
  ref,
} from 'vue';
import type {
  Interaction,
  InteractiveVideoTask,
  LmbTaskInteraction,
  OverlayInteraction,
} from '@/models/InteractiveVideoTask';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import VideoTimeline from '@/components/interactiveVideo/VideoTimeline.vue';
import SelectedInteractionProperties from '@/components/interactiveVideo/SelectedInteractionProperties.vue';
import {
  DragState,
  TimelineDragState,
  VideoMetadata,
} from '@/components/interactiveVideo/events';
import {
  iconForTaskType,
  newTask,
  printTaskType,
  TaskDefinition,
  TaskDefinitionMinusInteractiveVideo,
} from '@/models/TaskDefinition';
import { v4 } from 'uuid';
import { interactiveVideoEditorStateSymbol } from '@/components/interactiveVideo/interactiveVideoEditorState';
import { $gettext } from '../../language/gettext';
import { printInteractionType } from '@/models/InteractiveVideoTask';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import produce from 'immer';
import { isEqual } from 'lodash';
import strings from '@/components/interactiveVideo/strings';

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
const videoTimeline = ref<InstanceType<typeof VideoTimeline> | undefined>(
  undefined
);
const selectedInteractionProperties = ref<
  InstanceType<typeof SelectedInteractionProperties> | undefined
>(undefined);

const taskTypes: Array<TaskDefinition['task_type']> = [
  'DragTheWords',
  'FillInTheBlanks',
  'FindTheHotspots',
  'FindTheWords',
  'MarkTheWords',
  'Memory',
  'Pairing',
  'Question',
  'Sequencing',
];

provide(interactiveVideoEditorStateSymbol, {
  selectInteraction,
  selectedInteractionId,
  editInteraction,
  dragInteraction,
  resizeOverlay,
  deleteInteraction,
  dragInteractionTimeline,
});

/**
 *  Do the necessary so that calls to performEdit from within the LMB Task
 *  editor will not overwrite the state of the Interactive Video task
 *  that contains them.
 *  TODO Make a graphic diagram of all provides/injects in LernmodulePlugin
 */

provide(taskEditorStateSymbol, {
  performEdit: performEditForEditedLmbInteraction,
});

const taskEditor = inject<TaskEditorState>(taskEditorStateSymbol);

function performEditForEditedLmbInteraction(payload: {
  newTaskDefinition: TaskDefinitionMinusInteractiveVideo;
  undoBatch: unknown;
}): void {
  // Apply the new task definition for the edited interaction.
  const newDefinition = produce(
    props.taskDefinition,
    (videoTask: InteractiveVideoTask) => {
      const interactionIndex = videoTask.interactions.findIndex(
        (i) => i.id === selectedInteractionId.value
      );
      if (interactionIndex === -1) {
        throw new Error('cant find selected interaction in interactions array');
      }
      const interaction = videoTask.interactions[interactionIndex];
      if (interaction.type !== 'lmbTask') {
        throw new Error('Selected interaction is not of type lmbTask');
      }
      interaction.taskDefinition = payload.newTaskDefinition;
    }
  );
  const undoBatch = isEqual(payload.undoBatch, {})
    ? // If the edited interaction's LMB task editor sends empty object as undoBatch,
      // pass it through. This way, a new undo/redo state will always be created.
      {}
    : // If it doesn't send the empty object, that means that this edit should
      // be merged together with other edits with the same undoBatch to the same
      // Interaction.
      {
        type: 'editedLmbInteractionTask',
        interactionId: selectedInteractionId.value,
        undoBatch: payload.undoBatch,
      };
  taskEditor!.performEdit({
    newTaskDefinition: newDefinition,
    undoBatch: undoBatch,
  });
}

const selectedInteraction = computed((): Interaction | undefined =>
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
function onClickZoomIn() {
  videoTimeline.value!.zoom(0.18);
}
function onClickZoomOut() {
  videoTimeline.value!.zoom(-0.18);
}
function onClickPlay() {
  videoPlayer.value!.player!.play();
}
function onClickPause() {
  videoPlayer.value!.player!.pause();
}
function insertOverlay() {
  const interaction: OverlayInteraction = {
    type: 'overlay',
    v: 2,
    id: v4(),
    startTime: currentTime.value,
    endTime: Math.min(videoMetadata.value.length, currentTime.value + 10),
    x: 0.4,
    y: 0.4,
    width: 0.2,
    height: 0.2,
    content_wysiwyg: $gettext('Einblendung'),
    pauseWhenVisible: true,
  };
  taskEditor!.performEdit({
    newTaskDefinition: produce(
      props.taskDefinition,
      (draft: InteractiveVideoTask) => {
        draft.interactions.push(interaction);
      }
    ),
  });
  selectedInteractionId.value = interaction.id;
}

function insertLmbTaskInteraction(type: TaskDefinition['task_type']) {
  if (type === 'InteractiveVideo') {
    throw new Error(strings.forbiddenToNestInteractiveVideos);
  }
  console.log('insertLmbTaskInteraction', type);
  const task = newTask(type) as TaskDefinitionMinusInteractiveVideo;
  const interaction: LmbTaskInteraction = {
    type: 'lmbTask',
    id: v4(),
    taskDefinition: task,
    startTime: currentTime.value,
    endTime: Math.min(videoMetadata.value.length, currentTime.value + 10),
    x: 0.5,
    y: 0.5,
    pauseWhenVisible: true,
  };
  taskEditor!.performEdit({
    newTaskDefinition: produce(
      props.taskDefinition,
      (draft: InteractiveVideoTask) => {
        draft.interactions.push(interaction);
      }
    ),
  });
  selectedInteractionId.value = interaction.id;
}
function deleteInteraction(id: string) {
  console.log('deleteInteraction', id);
  const interaction = props.taskDefinition.interactions.find(
    (i) => i.id === id
  );
  if (!interaction) {
    throw new Error(strings.interactionNotFoundError);
  }
  const prompt = $gettext('%{ interaction } löschen', {
    interaction: printInteractionType(interaction),
  });
  const confirmed = window.confirm(prompt);
  if (!confirmed) {
    console.info('Delete prompt canceled by user');
    return;
  }
  const index = props.taskDefinition.interactions.findIndex((i) => i.id === id);
  if (index === -1) {
    throw new Error(strings.interactionNotFoundError);
  }
  taskEditor!.performEdit({
    newTaskDefinition: produce(
      props.taskDefinition,
      (draft: InteractiveVideoTask) => {
        draft.interactions.splice(index, 1);
      }
    ),
  });
}
function dragInteraction(
  id: string,
  xFraction: number,
  yFraction: number,
  // Used in order to batch actions for undo/redo.  Each time you click, drag,
  // and let go, that should result in a new state in the undo/redo history.
  dragState: DragState
) {
  taskEditor!.performEdit({
    newTaskDefinition: produce(
      props.taskDefinition,
      (draft: InteractiveVideoTask) => {
        const interaction = draft.interactions.find((i) => i.id === id);
        if (!interaction) {
          throw new Error(`Interaction with id ${id} not found`);
        }
        interaction.x = xFraction;
        interaction.y = yFraction;
      }
    ),
    undoBatch: dragState,
  });
}
function resizeOverlay(
  id: string,
  x: number,
  y: number,
  width: number,
  height: number,
  dragState: DragState
) {
  taskEditor!.performEdit({
    newTaskDefinition: produce(
      props.taskDefinition,
      (draft: InteractiveVideoTask) => {
        const interaction = draft.interactions.find((i) => i.id === id);
        if (!interaction) {
          throw new Error(`Interaction with id ${id} not found`);
        }
        if (interaction.type !== 'overlay') {
          throw new Error(strings.notAnOverlayError);
        }
        interaction.x = x;
        interaction.y = y;
        interaction.width = width;
        interaction.height = height;
      }
    ),
    undoBatch: dragState,
  });
}
function dragInteractionTimeline(
  id: string,
  startTime: number,
  dragState: TimelineDragState
) {
  taskEditor!.performEdit({
    newTaskDefinition: produce(
      props.taskDefinition,
      (draft: InteractiveVideoTask) => {
        const interaction = draft.interactions.find((i) => i.id === id);
        if (!interaction) {
          throw new Error(`Interaction with id ${id} not found`);
        }
        const duration = interaction.endTime - interaction.startTime;
        interaction.endTime = startTime + duration;
        interaction.startTime = startTime;
      }
    ),
    undoBatch: dragState,
  });
}
</script>
