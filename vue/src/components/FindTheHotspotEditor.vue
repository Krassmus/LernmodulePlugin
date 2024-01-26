<template>
  <span>Find the Hotspot - Editor</span><br />
  <FileUpload @FileUploaded="onFileUploaded" />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FindTheHotspotTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import FileUpload from '@/components/FileUpload.vue';
import produce from 'immer';

export default defineComponent({
  name: 'FindTheHotspotEditor',
  components: { FileUpload },
  props: {
    task: { type: Object as PropType<FindTheHotspotTask>, required: true },
  },
  data() {
    return {};
  },
  methods: {
    onImageUploaded(imageUrl: string): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.image.imageUrl = imageUrl;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as FindTheHotspotTask,
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],
  },
});
</script>

<style scoped></style>
