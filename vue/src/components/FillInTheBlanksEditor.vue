<template>
  Current undo redo state:
  <pre>{{ currentUndoRedoState }}</pre>

  <button @click="addBlank" class="button">Lücke hinzufügen</button>

  <textarea
    :value="taskDefinition.template"
    @input="onEditTemplate"
    ref="theTextArea"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { FillInTheBlanksDefinition } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';

export default defineComponent({
  name: 'FillInTheBlanksEditor',
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as FillInTheBlanksDefinition,
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],
  },
  methods: {
    onEditTemplate(event: InputEvent) {
      const newValue = (event.target as HTMLInputElement).value;
      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...this.taskDefinition,
          template: newValue,
        },
        undoBatch: { type: 'editFillInTheBlanksTemplate' },
      });
    },
    addBlank() {
      const textArea = this.$refs.theTextArea as HTMLTextAreaElement;

      const selectedText = this.taskDefinition.template.slice(
        textArea.selectionStart,
        textArea.selectionEnd
      );

      const blank = selectedText.replace(
        selectedText.trim(),
        '{' + selectedText.trim() + '}'
      );

      const newText =
        this.taskDefinition.template.substring(0, textArea.selectionStart) +
        blank +
        this.taskDefinition.template.substring(textArea.selectionEnd);

      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...this.taskDefinition,
          template: newText,
        },
        undoBatch: { type: 'editFillInTheBlanksTemplate' },
      });
    },
  },
});
</script>

<style scoped>
.button {
  font-size: 1em;
  line-height: 1.2;
  margin: 0 0.5em 1em;
  padding: 0.5em 1.25em;
  border-radius: 2em;
  background: #1a73d9;
  color: #fff;
  cursor: pointer;
  border: none;
  box-shadow: none;
  transform: translateZ(0);
  display: inline-block;
  text-align: center;
  text-shadow: none;
  text-decoration: none;
  vertical-align: baseline;
}
</style>
