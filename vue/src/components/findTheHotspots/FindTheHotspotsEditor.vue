<template>
  <div class="find-the-hotspots-editor">
    <div class="button-bar">
      <button @click="addRectangularHotspot">Add Rectangular Hotspot</button>
      <button @click="addEllipseHotspot">Add Ellipse Hotspot</button>
      <button @click="removeAllHotspots">Remove All Hotspots</button>
    </div>
    <ImageWithHotspots
      v-if="taskDefinition.image.file_id"
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
});

function onImageUploaded(file: FileRef): void {
  const newTaskDefinition = produce(props.taskDefinition, (draft) => {
    draft.image.file_id = file.id;
  });
  taskEditor!.performEdit({
    newTaskDefinition: newTaskDefinition,
    undoBatch: {},
  });
}

function addRectangularHotspot(): void {
  const newHotspot: Hotspot = {
    uuid: v4(),
    type: 'rectangle',
    x: 0.4,
    y: 0.4,
    width: 0.2,
    height: 0.2,
  };
  const newTaskDefinition = produce(props.taskDefinition, (draft) => {
    draft.hotspots.push(newHotspot);
  });
  taskEditor!.performEdit({ newTaskDefinition, undoBatch: {} });
}

function addEllipseHotspot(): void {
  const newHotspot: Hotspot = {
    uuid: v4(),
    type: 'ellipse',
    x: 0.4,
    y: 0.4,
    width: 0.2,
    height: 0.2,
  };
  const newTaskDefinition = produce(props.taskDefinition, (draft) => {
    draft.hotspots.push(newHotspot);
  });
  taskEditor!.performEdit({ newTaskDefinition, undoBatch: {} });
}

function removeAllHotspots(): void {
  const newTaskDefinition = produce(props.taskDefinition, (draft) => {
    draft.hotspots = [];
  });
  taskEditor!.performEdit({ newTaskDefinition, undoBatch: {} });
}

function selectHotspot(id: string): void {
  selectedHotspotId.value = id;
}
</script>

<style scoped>
.button-bar {
  display: flex;
  gap: 0.5em;
  margin-bottom: 0.5em;
}
</style>
