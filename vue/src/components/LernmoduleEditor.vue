<template>
  <!--  <h1>Variables passed from server:</h1>-->
  <!--  <pre>{{ LernmoduleVueJS }}</pre>-->
  <div>
    <label>Aufgabentyp auswählen: </label>
    <select :value="taskDefinition.task_type" @input="onSelectTaskType">
      <option value="FillInTheBlanks">Lückentext</option>
      <option value="FlashCards">Karteikarten</option>
    </select>
  </div>

  <div>
    <component :is="editorForTaskType(taskDefinition.task_type)" />

    <div class="save-undo-redo">
      <button
        class="studipButton"
        @click="saveTask"
        :disabled="saveStatus.status === 'saving'"
      >
        Speichern
      </button>
      <!--      <span-->
      <!--        :class="saveStatusText === 'Modified' ? 'save-status-modified' : ''"-->
      <!--        >{{ saveStatusText }}</span-->
      <!--      >-->
    </div>

    <h1 style="margin-top: 1em">Vorschau</h1>
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
    onSelectTaskType(event: InputEvent): void {
      const taskType = (event.target as HTMLInputElement).value;
      taskEditorStore.performEdit({
        newTaskDefinition: newTask(taskType as TaskDefinition['task_type']),
        undoBatch: {},
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
  margin-top: 5px;
}
.save-status-modified {
  font-weight: bold;
}
.save-status-modified::after {
  content: '*';
  color: red;
}

.task-name-input {
  flex-grow: 1;
  border: none;
}
.task-name-input:focus {
  outline: none;
}

.studipButton {
  background: #fff;
  border: 1px solid #28497c;
  border-radius: 0;
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
  color: #28497c;
  cursor: pointer;
  display: inline-block;
  font-family: Lato, sans-serif;
  font-size: 14px;
  line-height: 130%;
  margin: 0.8em 0.6em 0.8em 0;
  margin-top: 0.8em;
  margin-bottom: 0.8em;
  min-width: 100px;
  overflow: visible;
  padding: 5px 15px;
  position: relative;
  text-align: center;
  text-decoration: none;
  vertical-align: middle;
  white-space: nowrap;
  width: auto;
  -webkit-transition: none;
  transition: none;
}

.studipButton:hover {
  background: #28497c;
  color: #fff;
  outline: 0;
}
</style>
