<template>
  <span>Find the Hotspot</span><br />
  <FileUpload @fileUploaded="onImageUploaded" />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FindTheHotspotTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import FileUpload from '@/components/FileUpload.vue';
import produce from 'immer';
import { FileRef } from '@/routes/jsonApi';

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
    onImageUploaded(file: FileRef): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.image.file_id = file.id;
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
