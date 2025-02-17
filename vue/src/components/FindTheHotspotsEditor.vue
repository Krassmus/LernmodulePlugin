<template>
  <div class="button-bar">
    <button @click="addRectangularHotspot">Add Rectangular Hotspot</button>
    <button @click="addCircularHotspot">Add Circular Hotspot</button>
    <button @click="removeAllHotspots">Remove All Hotspots</button>
  </div>
  <div v-if="taskDefinition.image.file_id" class="image-and-hotspots-container">
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
      class="image"
    />
  </div>
  <FileUpload v-else @fileUploaded="onImageUploaded" />
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';
import {
  fileIdToUrl,
  Hotspot,
  HotspotType,
  FindTheHotspotsTask,
} from '@/models/TaskDefinition';
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

export default defineComponent({
  name: 'FindTheHotspotsEditor',
  components: { LazyImage, FileUpload },
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  data() {
    return {
      hotspots: [] as Hotspot[],
      selectedHotspot: null as Hotspot | null,
    };
  },
  mounted() {
    this.hotspots = this.taskDefinition.hotspots;
  },
  methods: {
    fileIdToUrl,
    onImageUploaded(file: FileRef): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.image.file_id = file.id;
      });
      taskEditorStore.performEdit({
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
        width: 180,
        height: 180,
      };
      this.hotspots.push(newHotspot);
      this.updateHotspotsInTaskDefinition();
    },
    addCircularHotspot(): void {
      const newHotspot: Hotspot = {
        uuid: v4(),
        type: 'circle',
        x: 50,
        y: 50,
        diameter: 180,
      };
      this.hotspots.push(newHotspot);
      this.updateHotspotsInTaskDefinition();
    },
    removeAllHotspots(): void {
      this.hotspots = [];
      this.updateHotspotsInTaskDefinition();
    },
    getHotspotStyle(hotspot: Hotspot) {
      if (hotspot.type === 'rectangle') {
        return {
          left: `${hotspot.x}%`,
          top: `${hotspot.y}%`,
          width: `${hotspot.width}px`,
          height: `${hotspot.height}px`,
        };
      } else {
        return {
          left: `${hotspot.x}%`,
          top: `${hotspot.y}%`,
          width: `${hotspot.diameter}px`,
          height: `${hotspot.diameter}px`,
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
    updateHotspotsInTaskDefinition(): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.hotspots = this.hotspots;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as FindTheHotspotsTask,

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

.image-and-hotspots-container {
  position: relative;
}

.image {
  border: 1px solid steelblue;
  user-select: none;
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
