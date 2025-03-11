<template>
  <div class="find-the-hotspots-editor">
    <div v-if="taskDefinition.image.type === 'image'" class="button-bar">
      <button @click="addRectangularHotspot" class="hotspot-button">
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
      <button @click="addEllipseHotspot" class="hotspot-button">
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
      <button @click="removeAllHotspots" class="hotspot-button">
        Remove All Hotspots
      </button>
      <button @click="deleteImage" class="hotspot-button">Delete image</button>
    </div>
    <ImageWithHotspots
      ref="imageWithHotspotsRef"
      v-if="taskDefinition.image.type === 'image'"
      :hotspots="taskDefinition.hotspots"
      :image="taskDefinition.image"
    />
    <FileUpload v-else @fileUploaded="onImageUploaded" />
  </div>
</template>

<script setup lang="ts">
import { inject, PropType, provide, ref, defineProps } from 'vue';
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

const taskEditor = inject<TaskEditorState>(taskEditorStateSymbol);

const props = defineProps({
  taskDefinition: {
    type: Object as PropType<FindTheHotspotsTask>,
    required: true,
  },
});

const selectedHotspotId = ref<string | undefined>(undefined);

provide(findTheHotspotsEditorStateSymbol, {
  selectedHotspotId,
  selectHotspot,
  deleteSelectedHotspot,
  changeHotspotCorrectness,
  dragHotspot,
  resizeHotspot,
});

function onImageUploaded(file: FileRef): void {
  const newTaskDefinition = produce(props.taskDefinition, (draft) => {
    draft.image = {
      uuid: v4(),
      type: 'image',
      file_id: file.id,
      altText: '',
    };
  });
  taskEditor!.performEdit({
    newTaskDefinition: newTaskDefinition,
    undoBatch: {},
  });
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
  };
  const newTaskDefinition = produce(props.taskDefinition, (draft) => {
    draft.hotspots.push(newHotspot);
  });
  taskEditor!.performEdit({ newTaskDefinition, undoBatch: {} });
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
  };
  const newTaskDefinition = produce(props.taskDefinition, (draft) => {
    draft.hotspots.push(newHotspot);
  });
  taskEditor!.performEdit({ newTaskDefinition, undoBatch: {} });
  selectHotspot(newHotspot.uuid);
}

function removeAllHotspots(): void {
  const newTaskDefinition = produce(props.taskDefinition, (draft) => {
    draft.hotspots = [];
  });
  taskEditor!.performEdit({ newTaskDefinition, undoBatch: {} });
}

function deleteImage(): void {
  const newTaskDefinition = produce(props.taskDefinition, (draft) => {
    draft.image = {
      type: 'none',
    };
  });
  taskEditor!.performEdit({ newTaskDefinition, undoBatch: {} });
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
  const newTaskDefinition = produce(props.taskDefinition, (draft) => {
    const index = draft.hotspots.findIndex((hotspot) => hotspot.uuid === id);
    if (index === -1) {
      throw new Error('No hotspot with id ' + id + ' found.');
    }
    draft.hotspots.splice(index, 1);
  });
  taskEditor!.performEdit({ newTaskDefinition, undoBatch: {} });
}

function changeHotspotCorrectness(): void {
  if (!selectedHotspotId.value) {
    console.error(
      'Called deleteSelectedHotspot, but selectedHotspotId is undefined'
    );
    return;
  }

  const newTaskDefinition = produce(props.taskDefinition, (draft) => {
    const hotspot = draft.hotspots.find(
      (hotspot) => hotspot.uuid === selectedHotspotId.value
    );
    if (hotspot) {
      hotspot.correct = !hotspot.correct;
    }
  });

  taskEditor!.performEdit({ newTaskDefinition, undoBatch: {} });
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
  const newTaskDefinition = produce(props.taskDefinition, (draft) => {
    const hotspot = draft.hotspots.find((h) => h.uuid === hotspotId);
    if (!hotspot) {
      throw new Error(`Hotspot with id ${hotspotId} not found`);
    }
    hotspot.x = xFraction;
    hotspot.y = yFraction;
  });
  taskEditor!.performEdit({ newTaskDefinition, undoBatch: { dragId } });
}

function resizeHotspot(
  dragId: string,
  hotspotId: string,
  xFraction: number,
  yFraction: number,
  width: number,
  height: number
): void {
  const newTaskDefinition = produce(props.taskDefinition, (draft) => {
    const hotspot = draft.hotspots.find((h) => h.uuid === hotspotId);
    if (!hotspot) {
      throw new Error(`Hotspot with id ${hotspotId} not found`);
    }
    hotspot.x = xFraction;
    hotspot.y = yFraction;
    hotspot.width = width;
    hotspot.height = height;
  });
  taskEditor!.performEdit({ newTaskDefinition, undoBatch: { dragId } });
}
</script>

<style scoped>
.button-bar {
  display: flex;
  gap: 0.5em;
  margin-bottom: 0.5em;
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
}
</style>
