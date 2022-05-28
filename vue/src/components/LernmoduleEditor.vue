<template>
  <h1>Editor (rendered with vue.js)</h1>
  <div>
    Variables passed from server:
    <pre>{{ LernmoduleVueJS }}</pre>
  </div>
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
  },
});
</script>

<style scoped>
.save-status.unsaved {
  font-weight: bold;
}
</style>
