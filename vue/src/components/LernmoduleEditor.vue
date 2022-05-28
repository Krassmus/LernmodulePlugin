<template>
  <h1>Variables passed from server:</h1>
  <pre>{{ LernmoduleVueJS }}</pre>
  <h1>Editor</h1>
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
    <component
      :is="componentForTaskType(taskDefinition.task_type)"
      :task="taskDefinition"
    />
  </div>
  <button @click="saveTask">Save</button>
  <pre v-if="hasUnsavedChanges" class="save-status unsaved">
Unsaved changes</pre
  >
  <pre>{{ saveStatus }}</pre>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { newTask, TaskDefinition } from '@/models/TaskDefinition';
import FillInTheBlanksEditor from '@/components/FillInTheBlanksEditor.vue';
import { taskEditorStore } from '@/store';

export default defineComponent({
  name: 'LernmoduleEditor',
  computed: {
    LernmoduleVueJS: () => window.STUDIP.LernmoduleVueJS,
    // TODO: Warning!! Bad!! You should parse the contents, do not just type-cast!!
    taskDefinition: () => taskEditorStore.taskDefinition,
    moduleName: () => taskEditorStore.moduleName,
    saveStatus: () => taskEditorStore.saveStatus,
    hasUnsavedChanges: () => taskEditorStore.hasUnsavedChanges,
  },
  methods: {
    componentForTaskType(type: TaskDefinition['task_type']) {
      switch (type) {
        case 'FillInTheBlanks':
          return FillInTheBlanksEditor;
        default:
          throw new Error('Unimplemented task type: ' + type);
      }
    },
    onSelectTaskType(type: TaskDefinition['task_type']): void {
      taskEditorStore.setTaskDefinition(newTask(type));
    },
    saveTask: taskEditorStore.saveTask,
    onInputModuleName(event: InputEvent) {
      const name = (event.target as HTMLInputElement).value;
      taskEditorStore.setModuleName(name);
    },
  },
});
</script>

<style scoped>
.save-status.unsaved {
  font-weight: bold;
}
</style>
