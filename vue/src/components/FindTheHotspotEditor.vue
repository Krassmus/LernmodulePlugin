<template>
  <span>Find the Hotspot - Editor</span><br />
  <input type="text" v-model="taskDefinition.template" />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FindTheHotspotTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';

export default defineComponent({
  name: 'FindTheHotspotEditor',
  props: {
    task: { type: Object as PropType<FindTheHotspotTask>, required: true },
  },
  data() {
    return {};
  },
  methods: {},
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as FindTheHotspotTask,
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],
  },
});
</script>

<style scoped></style>
