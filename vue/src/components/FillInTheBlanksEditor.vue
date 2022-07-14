<template>
  Current undo redo state:
  <pre>{{ currentUndoRedoState }}</pre>
  <div class="h5pModule">
    <button @click="addBlank" class="h5pButton">Lücke hinzufügen</button>
    <div>
      <textarea
        :value="taskDefinition.template"
        @input="(ev) => onEditTemplate(ev)"
        ref="theTextArea"
        class="h5pFillInTheBlanksEditor"
      />
    </div>
    <div>
      <input
        type="checkbox"
        id="retryCheckbox"
        v-model="taskDefinition.retryAllowed"
      />
      <label for="retryCheckbox">Erlaube "Retry"</label>
    </div>
    <div>
      <input
        type="checkbox"
        id="showSolutionsCheckbox"
        v-model="taskDefinition.showSolutionsAllowed"
      />
      <label for="showSolutionsCheckbox">Zeige "Show Solutions" Knopf</label>
    </div>
    <div v-if="taskDefinition.showSolutionsAllowed">
      <input
        type="checkbox"
        id="allBlanksMustBeFilledForSolutionCheckbox"
        v-model="taskDefinition.allBlanksMustBeFilledForSolutions"
      />
      <label for="allBlanksMustBeFilledForSolutionCheckbox"
        >Alle Lücken müssen ausgefüllt sein damit Lösungen angezeigt werden
        können</label
      >
    </div>
    <div>
      <input
        type="checkbox"
        id="autoCorrectCheckbox"
        v-model="taskDefinition.autoCorrect"
      />
      <label for="autoCorrectCheckbox"
        >Ausgefüllte Lücken sofort korrigieren</label
      >
    </div>
    <div>
      <input
        type="checkbox"
        id="caseSensitiveCheckbox"
        v-model="taskDefinition.caseSensitive"
      />
      <label for="caseSensitiveCheckbox"
        >Groß- und Kleinschreibung beachten</label
      >
    </div>
  </div>
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
.h5pModule {
  border: 2px solid #eee;
  padding: 0.5em 0.5em 0.5em 0.5em;
}

.h5pFillInTheBlanksEditor {
  display: block;
  width: 100%;
  height: 20em;
  resize: none;
}

.h5pButton {
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
  display: inline-block;
  text-align: center;
  text-shadow: none;
  text-decoration: none;
  vertical-align: baseline;
}
</style>
