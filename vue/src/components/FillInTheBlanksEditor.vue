<template>
  <!--  Current undo redo state:-->
  <!--  <pre>{{ currentUndoRedoState }}</pre>-->
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
    <h1 class="h5pBehaviorSetting">Einstellungen</h1>
    <div class="h5pBehaviorSetting">
      <h2>Korrektur</h2>
      <label for="correctBehaviorSelect">Korrigiert wird </label>

      <select id="correctBehaviorSelect" v-model="taskDefinition.autoCorrect">
        <option :value="false">manuell per Button</option>
        <option :value="true">automatisch nach Eingabe</option>
      </select>
      <div
        :class="
          !taskDefinition.autoCorrect
            ? 'h5pBehaviorSetting'
            : 'h5pBehaviorSetting-disabled'
        "
      >
        <label for="checkButtonStringInput">Text im Button: </label>
        <input
          type="text"
          id="checkButtonStringInput"
          :disabled="taskDefinition.autoCorrect"
          v-model="taskDefinition.stringCheckButton"
        />
      </div>
      <div class="h5pBehaviorSetting">
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

    <h2>Versuche</h2>
    <div>
      <input
        type="checkbox"
        id="retryCheckbox"
        v-model="taskDefinition.retryAllowed"
      />
      <label for="retryCheckbox">Erlaube mehrere Versuche</label>
    </div>
    <div class="h5pBehaviorSetting">
      <label
        for="retryButtonStringInput"
        :class="
          taskDefinition.retryAllowed
            ? 'h5pBehaviorSetting'
            : 'h5pBehaviorSetting-disabled'
        "
        >Text im Button:
      </label>
      <input
        type="text"
        id="retryButtonStringInput"
        :disabled="!taskDefinition.retryAllowed"
        v-model="taskDefinition.stringRetryButton"
      />
    </div>

    <h2>Lösungen</h2>
    <div class="h5pBehaviorSetting">
      <input
        type="checkbox"
        id="showSolutionsCheckbox"
        v-model="taskDefinition.showSolutionsAllowed"
        @change="
          !taskDefinition.showSolutionsAllowed
            ? (taskDefinition.allBlanksMustBeFilledForSolutions =
                taskDefinition.showSolutionsAllowed)
            : ''
        "
      />
      <label for="showSolutionsCheckbox"
        >Lösungen können angezeigt werden</label
      >
    </div>
    <div
      :class="
        taskDefinition.showSolutionsAllowed
          ? 'h5pBehaviorSetting'
          : 'h5pBehaviorSetting-disabled'
      "
    >
      <label for="solutionsButtonStringInput">Text im Button: </label>
      <input
        type="text"
        id="solutionsButtonStringInput"
        :disabled="!taskDefinition.showSolutionsAllowed"
        v-model="taskDefinition.stringSolutionsButton"
      />
    </div>
    <div
      :class="
        taskDefinition.showSolutionsAllowed
          ? 'h5pBehaviorSetting'
          : 'h5pBehaviorSetting-disabled'
      "
    >
      <input
        type="checkbox"
        :disabled="!taskDefinition.showSolutionsAllowed"
        id="allBlanksMustBeFilledForSolutionCheckbox"
        v-model="taskDefinition.allBlanksMustBeFilledForSolutions"
      />
      <label for="allBlanksMustBeFilledForSolutionCheckbox"
        >Alle Lücken müssen ausgefüllt sein, um Lösungen anzuzeigen</label
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

.h5pBehaviorSetting {
  margin-top: 0.5em;
}

.h5pBehaviorSetting-disabled {
  margin-top: 0.5em;
  opacity: 50%;
}
</style>
