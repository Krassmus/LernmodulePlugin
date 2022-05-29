<template>
  <!--  <h1>Variables passed from server:</h1>-->
  <!--  <pre>{{ LernmoduleVueJS }}</pre>-->
  <h1 class="task-name-header">
    Editing "{{ moduleName }}"
    <span
      :class="saveStatusText === 'Modified' ? 'save-status-modified' : ''"
      >{{ saveStatusText }}</span
    >
  </h1>
  <div class="save-undo-redo">
    <button @click="saveTask" :disabled="saveStatus.status === 'saving'">
      Save
    </button>
    <button @click="undo" :disabled="!canUndo">Undo</button>
    <button @click="redo" :disabled="!canRedo">Redo</button>
  </div>

  <label
    >Name des Moduls
    <input type="text" :value="moduleName" @input="onInputModuleName" />
  </label>
  <div>
    <label> Select task type </label>
    <select :value="this.taskDefinition.task_type" @input="onSelectTaskType">
      <option value="FillInTheBlanks">FillInTheBlanks</option>
    </select>
  </div>
  <div>
    <h2>Task</h2>
    <component :is="editorForTaskType(taskDefinition.task_type)" />
    <h2>Preview</h2>
    <component
      :is="viewerForTaskType(taskDefinition.task_type)"
      :task="taskDefinition"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  editorForTaskType,
  newTask,
  TaskDefinition,
  viewerForTaskType,
} from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';

export default defineComponent({
  name: 'LernmoduleEditor',
  mounted() {
    // Prompt about unsaved changes when leaving the page
    window.addEventListener('beforeunload', this.onBeforeUnload, {
      capture: true,
    });
  },
  unmounted() {
    window.removeEventListener('beforeunload', this.onBeforeUnload, {
      capture: true,
    });
  },
  computed: {
    LernmoduleVueJS: () => window.STUDIP.LernmoduleVueJS,
    // TODO: Warning!! Bad!! You should parse the contents, do not just type-cast!!
    taskDefinition: () => taskEditorStore.taskDefinition,
    moduleName: () => taskEditorStore.moduleName,
    saveStatus: () => taskEditorStore.saveStatus,
    hasUnsavedChanges: () => taskEditorStore.hasUnsavedChanges,
    canUndo: () => taskEditorStore.canUndo,
    canRedo: () => taskEditorStore.canRedo,
    saveStatusText(): string {
      switch (this.saveStatus.status) {
        case 'saved':
          if (this.hasUnsavedChanges) {
            return 'Modified';
          } else {
            return 'Saved';
          }
        case 'saving':
          return 'Saving...';
        case 'error':
          return 'An error occurred while saving.';
      }
      return '';
    },
  },
  methods: {
    editorForTaskType,
    viewerForTaskType,
    saveTask: taskEditorStore.saveTask,
    onBeforeUnload(event: BeforeUnloadEvent) {
      if (this.hasUnsavedChanges) {
        event.preventDefault();
        return (event.returnValue = true);
      }
    },
    undo: taskEditorStore.undo,
    redo: taskEditorStore.redo,
    onInputModuleName(event: InputEvent) {
      const name = (event.target as HTMLInputElement).value;
      taskEditorStore.setModuleName(name);
    },
    onSelectTaskType(type: TaskDefinition['task_type']): void {
      taskEditorStore.setTaskDefinition({
        taskDefinition: newTask(type),
        editType: {},
      });
    },
  },
});
</script>

<style scoped>
.save-undo-redo {
  width: 360px;
  display: grid;
  gap: 10px;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  margin-bottom: 5px;
}
.save-status-modified {
  font-weight: bold;
}
.save-status-modified::after {
  content: '*';
  color: red;
}

.task-name-header {
  display: flex;
  justify-content: space-between;
}
</style>
