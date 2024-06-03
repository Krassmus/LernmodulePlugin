<script lang="ts">
import { defineComponent } from 'vue';
import { coursewareBlockStore, taskEditorStore } from '@/store';
import {
  editorForTaskType,
  showViewerAboveEditor,
  viewerForTaskType,
} from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'CoursewareBlock',
  methods: {
    showViewerAboveEditor,
    viewerForTaskType,
    $gettext,
    editorForTaskType,
  },
  computed: {
    showEditingUI: () => coursewareBlockStore.showEditorUI,
    taskDefinition: () => taskEditorStore.taskDefinition,
    saveStatus: () => taskEditorStore.saveStatus,
    saveBlock: () => coursewareBlockStore.saveBlock,
    cancelEditing: () => coursewareBlockStore.cancelEditing,
  },
});
</script>

<template>
  <component
    v-if="!showEditingUI || showViewerAboveEditor(taskDefinition.task_type)"
    :is="viewerForTaskType(taskDefinition.task_type)"
    :task="taskDefinition"
    class="lernmodule-viewer"
  />
  <template v-if="showEditingUI">
    <component
      :is="editorForTaskType(taskDefinition.task_type)"
      :taskDefinition="taskDefinition"
    />
    <div class="save-cancel-buttons">
      <button class="button accept" @click="saveBlock">
        {{ $gettext('Speichern') }}
      </button>
      <button class="button cancel" @click="cancelEditing">
        {{ $gettext('Abbrechen') }}
      </button>
    </div>
  </template>
</template>

<style scoped>
.lernmodule-viewer {
  margin-bottom: 1em;
}
.save-cancel-buttons {
  display: flex;
  justify-content: flex-end;
  margin-right: 0.8em;
}
</style>
