<script lang="ts">
import { defineComponent } from 'vue';
import { coursewareBlockStore, taskEditorStore } from '@/store';
import { editorForTaskType, viewerForTaskType } from '@/models/TaskDefinition';
import { saveTask } from '@/routes';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'CoursewareBlock',
  methods: { viewerForTaskType, $gettext, saveTask, editorForTaskType },
  computed: {
    showEditingUI: () => coursewareBlockStore.showEditorUI,
    taskDefinition: () => taskEditorStore.taskDefinition,
    saveStatus: () => taskEditorStore.saveStatus,
  },
});
</script>

<template>
  <component
    :is="viewerForTaskType(taskDefinition.task_type)"
    :task="taskDefinition"
  />
  <template v-if="showEditingUI">
    <component :is="editorForTaskType(taskDefinition.task_type)" />
    <div class="save-undo-redo">
      <button
        class="button"
        @click="saveTask"
        :disabled="saveStatus.status === 'saving'"
      >
        {{ $gettext('Speichern') }}
      </button>
    </div>
  </template>
</template>

<style scoped></style>
