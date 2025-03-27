<template>
  <div class="stud5p-task">
    <form class="default">
      <fieldset>
        <legend>{{ $gettext('Find the Hotspots') }}</legend>

        <div class="find-the-hotspots-editor">
          <div v-if="taskDefinition.image.type === 'image'" class="button-bar">
            <button
              @click="addRectangularHotspot"
              type="button"
              class="hotspot-button"
            >
              <svg
                width="20"
                height="20"
                viewBox="0 0 32 32"
                xmlns="http://www.w3.org/2000/svg"
              >
                <rect
                  x="1"
                  y="1"
                  width="30"
                  height="30"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2px"
                  rx="2"
                />
              </svg>
            </button>
            <button
              @click="addEllipseHotspot"
              type="button"
              class="hotspot-button"
            >
              <svg
                width="20"
                height="20"
                viewBox="0 0 32 32"
                xmlns="http://www.w3.org/2000/svg"
              >
                <circle
                  cx="16"
                  cy="16"
                  r="15"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2px"
                />
              </svg>
            </button>
            <button
              @click="removeAllHotspots"
              type="button"
              class="hotspot-button"
            >
              {{ $gettext('Alle Hotspots entfernen') }}
            </button>
            <button @click="deleteImage" type="button" class="hotspot-button">
              {{ $gettext('Bild löschen') }}
            </button>
          </div>
          <ImageWithHotspots
            ref="imageWithHotspotsRef"
            v-if="taskDefinition.image.type === 'image'"
            :hotspots="taskDefinition.hotspots"
            :image="taskDefinition.image"
          />
          <FileUpload v-else @fileUploaded="onImageUploaded" />
        </div>
      </fieldset>
      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Einstellungen') }}</legend>

        <label>
          {{
            $gettext(
              'Ergebnismitteilung (mögliche Variablen :correct und :total):'
            )
          }}
          <input
            v-model="modelTaskDefinition.strings.resultMessage"
            @input="
              updateTaskDefinition('taskDefinition.strings.resultMessage')
            "
            type="text"
          />
        </label>

        <label>
          {{ $gettext('Feedback, wenn in den leeren Bereich geklickt wurde:') }}
          <input
            v-model="modelTaskDefinition.strings.feedbackWhenClickingBackground"
            @input="
              updateTaskDefinition(
                'taskDefinition.strings.feedbackWhenClickingBackground'
              )
            "
            type="text"
          />
        </label>

        <label>
          {{
            $gettext(
              'Feedback, wenn auf einen bereits gefundenen Hotspot geklickt wurde:'
            )
          }}
          <input
            v-model="
              modelTaskDefinition.strings.feedbackWhenClickingHotspotAgain
            "
            @input="
              updateTaskDefinition(
                'taskDefinition.strings.feedbackWhenClickingHotspotAgain'
              )
            "
            type="text"
          />
        </label>

        <label>
          {{ $gettext('Anzahl zu findender Hotspots:') }}
          <input
            v-model="modelTaskDefinition.hotspotsToFind"
            @input="updateTaskDefinition('taskDefinition.hotspotsToFind')"
            type="number"
            :min="1"
            :max="numberOfCorrectHotspots"
          />
        </label>

        <label>
          <input v-model="limitAnswers" type="checkbox" />
          {{ $gettext('Anzahl erlaubter Klicks limitieren') }}
          <input
            v-model="allowedClicks"
            :disabled="!limitAnswers"
            :class="{ 'setting-disabled': !limitAnswers }"
            @input="updateAllowedClicksInTaskDefinition"
            type="number"
            :min="modelTaskDefinition.hotspotsToFind"
          />
        </label>
      </fieldset>
    </form>
  </div>
</template>

<script setup lang="ts">
import {
  inject,
  PropType,
  provide,
  ref,
  defineProps,
  watch,
  computed,
} from 'vue';
import FileUpload from '@/components/FileUpload.vue';
import produce from 'immer';
import { v4 } from 'uuid';
import { FileRef } from '@/routes/jsonApi';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import { FindTheHotspotsTask, Hotspot } from '@/models/FindTheHotspotsTask';
import { findTheHotspotsEditorStateSymbol } from '@/components/findTheHotspots/findTheHotspotsEditorState';
import ImageWithHotspots from '@/components/findTheHotspots/ImageWithHotspots.vue';
import { cloneDeep } from 'lodash';
import { $gettext } from '@/language/gettext';

const taskEditor = inject<TaskEditorState>(taskEditorStateSymbol);

const props = defineProps({
  taskDefinition: {
    type: Object as PropType<FindTheHotspotsTask>,
    required: true,
  },
});

const modelTaskDefinition = ref<FindTheHotspotsTask>(
  cloneDeep(props.taskDefinition)
);

watch(
  () => props.taskDefinition,
  (newTaskDefinition) => {
    modelTaskDefinition.value = cloneDeep(newTaskDefinition);
  },
  { deep: true }
);

const limitAnswers = ref<boolean>(false);
const allowedClicks = ref<number | null>(null);

watch(limitAnswers, (newValue) => {
  if (newValue) {
    allowedClicks.value = modelTaskDefinition.value.hotspotsToFind;
  } else {
    allowedClicks.value = null;
  }
  updateAllowedClicksInTaskDefinition();
});

const selectedHotspotId = ref<string | undefined>(undefined);

const numberOfCorrectHotspots = computed(
  () =>
    modelTaskDefinition.value.hotspots.filter((hotspot) => hotspot.correct)
      .length
);

provide(findTheHotspotsEditorStateSymbol, {
  selectedHotspotId,
  selectHotspot,
  deleteSelectedHotspot,
  changeHotspotCorrectness,
  setHotspotFeedback,
  dragHotspot,
  resizeHotspot,
});

function updateTaskDefinition(undoBatch?: unknown) {
  // Synchronize state modelTaskDefinition -> taskDefinition.
  console.log('update task definition');
  taskEditor!.performEdit({
    newTaskDefinition: cloneDeep(modelTaskDefinition.value),
    undoBatch: undoBatch ?? {},
  });
}

function updateAllowedClicksInTaskDefinition() {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.allowedClicks = allowedClicks.value ? allowedClicks.value : 0;
  });
  updateTaskDefinition('taskDefinition.allowedClicks');
}

function onImageUploaded(file: FileRef): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.image = {
      uuid: v4(),
      type: 'image',
      file_id: file.id,
      altText: '',
    };
  });

  updateTaskDefinition();
}

const imageWithHotspotsRef = ref<
  InstanceType<typeof ImageWithHotspots> | undefined
>();
function addRectangularHotspot(): void {
  const component = imageWithHotspotsRef.value;
  if (!component) {
    console.warn('Not inserting anything.');
    return;
  }
  const el = component.$el as HTMLElement;
  const imgEl = el.getElementsByClassName('hotspots-image')[0];
  const imageWidthPixels = imgEl.clientWidth;
  const imageHeightPixels = imgEl.clientHeight;
  let hotspotWidthPercent: number, hotspotHeightPercent: number;
  const size = 0.3;
  const smallestDim = imageWidthPixels > imageHeightPixels ? 'height' : 'width';
  if (smallestDim === 'height') {
    hotspotHeightPercent = size;
    const hotspotHeightPixels = hotspotHeightPercent * imageHeightPixels;
    hotspotWidthPercent = hotspotHeightPixels / imageWidthPixels;
  } else {
    hotspotWidthPercent = size;
    const hotspotWidthPixels = hotspotWidthPercent * imageWidthPixels;
    hotspotHeightPercent = hotspotWidthPixels / imageHeightPixels;
  }

  const newHotspot: Hotspot = {
    uuid: v4(),
    type: 'rectangle',
    x: 0.5 - hotspotWidthPercent / 2,
    y: 0.5 - hotspotHeightPercent / 2,
    width: hotspotWidthPercent,
    height: hotspotHeightPercent,
    correct: true,
    feedback: '',
  };
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.hotspots.push(newHotspot);
  });

  updateTaskDefinition();

  selectHotspot(newHotspot.uuid);
}

function addEllipseHotspot(): void {
  const component = imageWithHotspotsRef.value;
  if (!component) {
    console.warn('Not inserting anything.');
    return;
  }
  const el = component.$el as HTMLElement;
  const imgEl = el.getElementsByClassName('hotspots-image')[0];
  const imageWidthPixels = imgEl.clientWidth;
  const imageHeightPixels = imgEl.clientHeight;
  let hotspotWidthPercent: number, hotspotHeightPercent: number;
  const size = 0.3;
  const smallestDim = imageWidthPixels > imageHeightPixels ? 'height' : 'width';
  if (smallestDim === 'height') {
    hotspotHeightPercent = size;
    const hotspotHeightPixels = hotspotHeightPercent * imageHeightPixels;
    hotspotWidthPercent = hotspotHeightPixels / imageWidthPixels;
  } else {
    hotspotWidthPercent = size;
    const hotspotWidthPixels = hotspotWidthPercent * imageWidthPixels;
    hotspotHeightPercent = hotspotWidthPixels / imageHeightPixels;
  }

  const newHotspot: Hotspot = {
    uuid: v4(),
    type: 'ellipse',
    x: 0.5 - hotspotWidthPercent / 2,
    y: 0.5 - hotspotHeightPercent / 2,
    width: hotspotWidthPercent,
    height: hotspotHeightPercent,
    correct: true,
    feedback: '',
  };
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.hotspots.push(newHotspot);
  });

  updateTaskDefinition();

  selectHotspot(newHotspot.uuid);
}

function removeAllHotspots(): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.hotspots = [];
  });

  updateTaskDefinition();
}

function deleteImage(): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.image = {
      type: 'none',
    };
  });

  updateTaskDefinition();
}

function deleteSelectedHotspot(): void {
  if (!selectedHotspotId.value) {
    console.error(
      'Called deleteSelectedHotspot, but selectedHotspotId is undefined'
    );
    return;
  }
  deleteHotspot(selectedHotspotId.value);
}

function deleteHotspot(id: string): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    const index = draft.hotspots.findIndex((hotspot) => hotspot.uuid === id);
    if (index === -1) {
      throw new Error('No hotspot with id ' + id + ' found.');
    }
    draft.hotspots.splice(index, 1);
  });

  updateTaskDefinition();
}

function changeHotspotCorrectness(): void {
  if (!selectedHotspotId.value) {
    console.error(
      'Called deleteSelectedHotspot, but selectedHotspotId is undefined'
    );
    return;
  }

  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    const hotspot = draft.hotspots.find(
      (hotspot) => hotspot.uuid === selectedHotspotId.value
    );
    if (hotspot) {
      hotspot.correct = !hotspot.correct;
    }
  });

  updateTaskDefinition();
}

function setHotspotFeedback(feedback: string): void {
  console.log('setHotspotFeedback', feedback);

  if (!selectedHotspotId.value) {
    console.error(
      'Called setHotspotFeedback, but selectedHotspot is undefined'
    );
    return;
  }

  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    const hotspot = draft.hotspots.find(
      (hotspot) => hotspot.uuid === selectedHotspotId.value
    );
    if (hotspot) {
      hotspot.feedback = feedback;
    }
  });

  updateTaskDefinition('task.hotspots.feedback' + selectedHotspotId.value);
}

function selectHotspot(id: string): void {
  selectedHotspotId.value = id;
}

function dragHotspot(
  dragId: string,
  hotspotId: string,
  xFraction: number,
  yFraction: number
): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    const hotspot = draft.hotspots.find((h) => h.uuid === hotspotId);
    if (!hotspot) {
      throw new Error(`Hotspot with id ${hotspotId} not found`);
    }
    hotspot.x = xFraction;
    hotspot.y = yFraction;
  });

  updateTaskDefinition(dragId);
}

function resizeHotspot(
  dragId: string,
  hotspotId: string,
  xFraction: number,
  yFraction: number,
  width: number,
  height: number
): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    const hotspot = draft.hotspots.find((h) => h.uuid === hotspotId);
    if (!hotspot) {
      throw new Error(`Hotspot with id ${hotspotId} not found`);
    }
    hotspot.x = xFraction;
    hotspot.y = yFraction;
    hotspot.width = width;
    hotspot.height = height;
  });

  updateTaskDefinition(dragId);
}
</script>

<style scoped>
.find-the-hotspots-editor {
  border: 1px solid #ccc;
}

.button-bar {
  display: flex;
  gap: 0.5em;
  padding: 0.25em;
  border-bottom: 1px solid #ccc;
  background: #f5f5f5;
}

.hotspot-button {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #444;

  cursor: pointer;

  background: linear-gradient(to bottom, #fff 0, #f2f2f2 100%);
  border: 1px solid #ccc;
  border-radius: 0.25em;
  padding: 0.75em;

  &:hover {
    border: 1px solid #999;
  }

  &:nth-child(2) {
    margin-right: auto;
  }
}
</style>
