<template>
  <div class="stud5p-task">
    <form class="default">
      <fieldset>
        <legend>{{ $gettext('Find the Hotspots') }}</legend>

        <div
          v-if="taskDefinition.image.type === 'image'"
          class="find-the-hotspots-editor"
        >
          <div class="button-bar">
            <button
              @click="addRectangularHotspot"
              type="button"
              class="hotspot-button"
              :title="$gettext('Rechteckigen Hotspot hinzufügen')"
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
              :title="$gettext('Runden Hotspot hinzufügen')"
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
            :hotspots="taskDefinition.hotspots"
            :image="taskDefinition.image"
          />
        </div>

        <label v-else>
          {{ $gettext('Neues Bild hochladen') }}
          <FileUpload @file-uploaded="onImageUploaded" :accept="'image/*'" />
        </label>
      </fieldset>
      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Einstellungen') }}</legend>
        <template v-if="numberOfCorrectHotspots">
          <label style="margin-bottom: 0"> </label>
          <label style="margin-bottom: 0">
            <span style="display: flex; align-items: center; gap: 0.25em">
              <input
                v-model="limitHotspotsToFind"
                @change="updateHotspotsToFindInTaskDefinition"
                type="checkbox"
              />
              {{ $gettext('Anzahl zu findender Hotspots begrenzen auf:') }}
              <input
                v-model="hotspotsToFind"
                style="
                  margin-bottom: 0.25em;
                  width: 4em;
                  padding: 0 0.25em 0 0.25em;
                "
                :disabled="!limitHotspotsToFind"
                :class="{ 'setting-disabled': !limitHotspotsToFind }"
                @change="updateHotspotsToFindInTaskDefinition"
                type="number"
                :min="1"
                :max="numberOfCorrectHotspots"
              />
            </span>
          </label>

          <label>
            <span style="display: flex; align-items: center; gap: 0.25em">
              <input
                v-model="limitClicks"
                @change="updateAllowedClicksInTaskDefinition"
                type="checkbox"
              />
              {{ $gettext('Anzahl erlaubter Klicks begrenzen auf: ') }}
              <input
                v-model="allowedClicks"
                style="
                  margin-bottom: 0.25em;
                  width: 4em;
                  padding: 0 0.25em 0 0.25em;
                "
                :disabled="!limitClicks"
                :class="{ 'setting-disabled': !limitClicks }"
                @change="updateAllowedClicksInTaskDefinition"
                type="number"
                :min="
                  Math.max(
                    modelTaskDefinition.hotspotsToFind
                      ? modelTaskDefinition.hotspotsToFind
                      : numberOfCorrectHotspots,
                    1
                  )
                "
              />
            </span>
          </label>
        </template>
        <label v-else>{{
          $gettext('Fügen Sie mindestens einen korrekten Hotspot hinzu.')
        }}</label>
      </fieldset>
      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Beschriftungen') }}</legend>

        <label>
          {{ $gettext('Text für Wiederholen-Button:') }}
          <input
            v-model="modelTaskDefinition.strings.retryButton"
            @input="updateTaskDefinition('taskDefinition.strings.retryButton')"
            type="text"
          />
        </label>
      </fieldset>
      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Feedback') }}</legend>

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
      </fieldset>
    </form>
  </div>
  <pre
    v-if="debug && true"
    :style="{ flexBasis: '50%', flexGrow: 0, flexShrink: 0 }"
    >{{
      {
        numberOfCorrectHotspots,
        limitHotspotsToFind,
        hotspotsToFind,
        limitClicks,
        allowedClicks,
        numberOfClicksAllowed,
        numberOfHotspotsToFind,
      }
    }}</pre
  >
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

const debug = window.STUDIP.LernmoduleVueJS.LERNMODULE_DEBUG;

// State
const modelTaskDefinition = ref<FindTheHotspotsTask>(
  cloneDeep(props.taskDefinition)
);
const selectedHotspotId = ref<string | undefined>(undefined);
const limitHotspotsToFind = ref<boolean>(false);
const hotspotsToFind = ref<number | null>(null);
const limitClicks = ref<boolean>(false);
const allowedClicks = ref<number | null>(null);
const imageWithHotspotsRef = ref<
  InstanceType<typeof ImageWithHotspots> | undefined
>();

provide(findTheHotspotsEditorStateSymbol, {
  selectedHotspotId,
  selectHotspot,
  deleteSelectedHotspot,
  changeHotspotCorrectness,
  setHotspotFeedback,
  dragHotspot,
  resizeHotspot,
});

// Computed properties
const numberOfCorrectHotspots = computed(
  () =>
    modelTaskDefinition.value.hotspots.filter((hotspot) => hotspot.correct)
      .length
);

const numberOfHotspotsToFind = computed(
  () => modelTaskDefinition.value.hotspotsToFind
);

const numberOfClicksAllowed = computed(
  () => modelTaskDefinition.value.allowedClicks
);

// Watchers
watch(
  () => props.taskDefinition,
  (newTaskDefinition, oldTaskDefinition) => {
    modelTaskDefinition.value = cloneDeep(newTaskDefinition);
  },
  { deep: true }
);

watch(numberOfCorrectHotspots, (newValue) => {
  // Reset settings if numberOfCorrectHotspots changes
  limitHotspotsToFind.value = false;
  limitClicks.value = false;
  updateHotspotsToFindInTaskDefinition();
});

function updateTaskDefinition(undoBatch?: unknown): void {
  // Synchronize state modelTaskDefinition -> taskDefinition.
  console.log('update task definition');
  taskEditor!.performEdit({
    newTaskDefinition: cloneDeep(modelTaskDefinition.value),
    undoBatch: undoBatch ?? {},
  });
}

function updateAllowedClicksInTaskDefinition(): void {
  if (!limitClicks.value) {
    // If the setting is not checked we set the allowedClicks value to 0 which means unlimited clicks
    allowedClicks.value = null;
    modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
      draft.allowedClicks = 0;
    });
  } else {
    // If the setting is checked we need to make sure we set a valid value for the limit
    if (
      allowedClicks.value &&
      allowedClicks.value >=
        (numberOfHotspotsToFind.value
          ? numberOfHotspotsToFind.value
          : numberOfCorrectHotspots.value)
    ) {
      // We already have a valid value, set it in the taskDefinition
      modelTaskDefinition.value = produce(
        modelTaskDefinition.value,
        (draft) => {
          draft.allowedClicks = allowedClicks.value as number;
        }
      );
    } else {
      // The value in allowedClicks is not valid, make it valid
      allowedClicks.value = Math.max(
        numberOfHotspotsToFind.value
          ? numberOfHotspotsToFind.value
          : numberOfCorrectHotspots.value,
        1
      );
      modelTaskDefinition.value = produce(
        modelTaskDefinition.value,
        (draft) => {
          draft.allowedClicks = allowedClicks.value as number;
        }
      );
    }
  }

  updateTaskDefinition();
}

function updateHotspotsToFindInTaskDefinition(): void {
  if (!limitHotspotsToFind.value) {
    // If the setting is not checked we set the hotspotsToFind value to 0 which means all
    hotspotsToFind.value = null;
    modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
      draft.hotspotsToFind = 0;
    });
  } else {
    // If the setting is checked we need to make sure we set a valid value for the limit
    if (
      hotspotsToFind.value &&
      hotspotsToFind.value <= numberOfCorrectHotspots.value
    ) {
      // We already have a valid value, let's set it in the taskDefinition
      modelTaskDefinition.value = produce(
        modelTaskDefinition.value,
        (draft) => {
          draft.hotspotsToFind = hotspotsToFind.value
            ? hotspotsToFind.value
            : numberOfCorrectHotspots.value;
        }
      );
    } else {
      // The value in hotspotsToFind is not valid, let's make it valid
      hotspotsToFind.value = Math.max(numberOfCorrectHotspots.value, 1);
      modelTaskDefinition.value = produce(
        modelTaskDefinition.value,
        (draft) => {
          draft.hotspotsToFind = hotspotsToFind.value
            ? hotspotsToFind.value
            : numberOfCorrectHotspots.value;
        }
      );
    }
  }

  updateTaskDefinition();

  // If we changed the number of hotspotsToFind we also need to update
  // the allowed clicks limit to make sure the task is still valid
  updateAllowedClicksInTaskDefinition();
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

function addRectangularHotspot(): void {
  const imgEl = document.querySelector('.hotspots-image');
  const imageWidthPixels = imgEl ? imgEl.clientWidth : 10;
  const imageHeightPixels = imgEl ? imgEl.clientHeight : 10;
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
  const imgEl = document.querySelector('.hotspots-image');
  const imageWidthPixels = imgEl ? imgEl.clientWidth : 10;
  const imageHeightPixels = imgEl ? imgEl.clientHeight : 10;
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

function changeHotspotCorrectness(correct: boolean): void {
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
      hotspot.correct = correct;
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
