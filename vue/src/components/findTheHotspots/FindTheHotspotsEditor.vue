<template>
  <div class="find-the-hotspots-editor">
    <div class="button-bar">
      <button @click="addRectangularHotspot">Add Rectangular Hotspot</button>
      <button @click="addCircularHotspot">Add Circular Hotspot</button>
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

<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import { fileIdToUrl } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import FileUpload from '@/components/FileUpload.vue';
import produce from 'immer';
import { v4 } from 'uuid';
import { FileRef } from '@/routes/jsonApi';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import { FindTheHotspotsTask, Hotspot } from '@/models/FindTheHotspotsTask';
import ImageWithHotspots from '@/components/findTheHotspots/ImageWithHotspots.vue';

export default defineComponent({
  name: 'FindTheHotspotsEditor',
  components: { ImageWithHotspots, FileUpload },
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
</style>
