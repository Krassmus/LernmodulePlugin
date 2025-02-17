<template>
  <div class="find-the-hotspots-editor">
    <div class="button-bar">
      <button @click="addRectangularHotspot">Add Rectangular Hotspot</button>
      <button @click="addCircularHotspot">Add Circular Hotspot</button>
      <button @click="removeAllHotspots">Remove All Hotspots</button>
    </div>
    <div
      v-if="taskDefinition.image.file_id"
      class="image-and-hotspots-container-wrapper"
    >
      <div class="image-and-hotspots-container">
        <div
          v-for="hotspot in taskDefinition.hotspots"
          :key="hotspot.uuid"
          class="hotspot"
          :style="getHotspotStyle(hotspot)"
        />
        <LazyImage
          :src="fileIdToUrl(taskDefinition.image.file_id)"
          :alt="taskDefinition.image.altText"
          @click="deselectHotspot"
          class="image hotspots-image"
        />
      </div>
    </div>
    <FileUpload v-else @fileUploaded="onImageUploaded" />
  </div>
</template>

<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import { fileIdToUrl } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import FileUpload from '@/components/FileUpload.vue';
import produce from 'immer';
import { v4 } from 'uuid';
import { FileRef } from '@/routes/jsonApi';
import LazyImage from '@/components/LazyImage.vue';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import { FindTheHotspotsTask, Hotspot } from '@/models/FindTheHotspotsTask';

export default defineComponent({
  name: 'FindTheHotspotsEditor',
  components: { LazyImage, FileUpload },
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  props: {
    taskDefinition: {
      type: Object as PropType<FindTheHotspotsTask>,
      required: true,
    },
  },
  data() {
    return {
      selectedHotspot: null as Hotspot | null,
    };
  },
  methods: {
    fileIdToUrl,
    onImageUploaded(file: FileRef): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.image.file_id = file.id;
      });
      this.taskEditor!.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
    addRectangularHotspot(): void {
      const newHotspot: Hotspot = {
        uuid: v4(),
        type: 'rectangle',
        x: 50,
        y: 50,
        width: 0.2,
        height: 0.2,
      };
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.hotspots.push(newHotspot);
      });
      this.taskEditor!.performEdit({ newTaskDefinition, undoBatch: {} });
    },
    addCircularHotspot(): void {
      const newHotspot: Hotspot = {
        uuid: v4(),
        type: 'circle',
        x: 50,
        y: 50,
        diameter: 0.2,
      };
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.hotspots.push(newHotspot);
      });
      this.taskEditor!.performEdit({ newTaskDefinition, undoBatch: {} });
    },
    removeAllHotspots(): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.hotspots = [];
      });
      this.taskEditor!.performEdit({ newTaskDefinition, undoBatch: {} });
    },
    getHotspotStyle(hotspot: Hotspot): Partial<CSSStyleDeclaration> {
      if (hotspot.type === 'rectangle') {
        return {
          left: `${hotspot.x}%`,
          top: `${hotspot.y}%`,
          width: `${hotspot.width * 100}%`,
          height: `${hotspot.height * 100}%`,
        };
      } else {
        return {
          left: `${hotspot.x}%`,
          top: `${hotspot.y}%`,
          width: `${hotspot.diameter * 100}%`,
          aspectRatio: '1',
          borderRadius: '50%',
        };
      }
    },
    selectHotspot(hotspot: Hotspot): void {
      this.selectedHotspot = hotspot;
    },
    deselectHotspot(): void {
      this.selectedHotspot = null;
    },
  },
  computed: {
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],
  },
});
</script>

<style scoped>
.button-bar {
  display: flex;
  gap: 0.5em;
  margin-bottom: 0.5em;
}

.image-and-hotspots-container-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-and-hotspots-container {
  position: relative;
  height: max-content;
}

.hotspots-image {
  user-select: none;
  max-height: 400px;
  width: 100%;
}

.hotspot {
  position: absolute;
  border: 2px dashed rgba(0, 0, 0, 0.7);
  background-color: rgba(255, 255, 255, 0.5);
}

.hotspot.selected {
  border: 2px dashed #0099ff;
}
</style>
