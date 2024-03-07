<template>
  <span>Find the Hotspot - Editor</span><br />
  <FileUpload @fileUploaded="onImageUploaded" />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FindTheHotspotTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import FileUpload from '@/components/FileUpload.vue';
import produce from 'immer';
import { UploadedFile } from '@/routes';

export default defineComponent({
  name: 'FindTheHotspotEditor',
  components: { FileUpload },
  props: {
    taskDefinition: {
      type: Object as PropType<FindTheHotspotTask>,
      required: true,
    },
  },
  data() {
    return {};
  },
  methods: {
    onImageUploaded(file: UploadedFile): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.image.imageUrl = file.url;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
  },
  computed: {
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],
  },
});
</script>

<style scoped></style>
