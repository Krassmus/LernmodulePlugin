<template>
  <!--  Current undo redo state:-->
  <!--  <pre>{{ currentUndoRedoState }}</pre>-->
  <div>
    <h1 class="h5pBehaviorSetting">Titel</h1>
    <div class="taskDescription">
      <textarea class="taskDescriptionText" v-model="taskDefinition.title" />
    </div>
    <h1 class="h5pBehaviorSetting">Aufgabenbeschreibung</h1>
    <div class="taskDescription">
      <textarea
        class="taskDescriptionText"
        v-model="taskDefinition.description"
      />
    </div>
    <h1 class="h5pBehaviorSetting">Lückentext</h1>
    <button @click="addBlank" class="studipButton">Lücke hinzufügen</button>
    <div>
      <textarea
        :value="taskDefinition.template"
        @input="(ev) => onEditTemplate(ev)"
        ref="theTextArea"
        class="h5pFillInTheBlanksEditor"
      />
    </div>

    <!--    <fieldset class="collapsed">-->
    <h1 class="h5pBehaviorSetting">Einstellungen</h1>
    <div class="h5pBehaviorSetting">
      <h2>Korrektur</h2>
      <label>
        Korrigiert wird
        <select v-model="taskDefinition.autoCorrect">
          <option :value="false">manuell per Button</option>
          <option :value="true">automatisch nach Eingabe</option>
        </select>
      </label>
      <div
        :class="
          !taskDefinition.autoCorrect
            ? 'h5pBehaviorSetting'
            : 'h5pBehaviorSetting-disabled'
        "
      >
        <label>
          Text im Button:
          <input
            type="text"
            :disabled="taskDefinition.autoCorrect"
            v-model="taskDefinition.stringCheckButton"
          />
        </label>
      </div>
      <div class="h5pBehaviorSetting">
        <label>
          Ergebnismitteilung:
          <input
            type="text"
            v-model="taskDefinition.stringResultMessage"
            style="width: 100%"
          />
        </label>
      </div>
      <div class="h5pBehaviorSetting">
        <label>
          <input
            type="checkbox"
            id="caseSensitiveCheckbox"
            v-model="taskDefinition.caseSensitive"
          />
          Groß- und Kleinschreibung beachten
        </label>
      </div>
    </div>

    <h2>Versuche</h2>
    <div>
      <label>
        <input type="checkbox" v-model="taskDefinition.retryAllowed" />
        Mehrere Versuche erlauben
      </label>
    </div>
    <div class="h5pBehaviorSetting">
      <label
        :class="
          taskDefinition.retryAllowed
            ? 'h5pBehaviorSetting'
            : 'h5pBehaviorSetting-disabled'
        "
        >Text im Button:

        <input
          type="text"
          :disabled="!taskDefinition.retryAllowed"
          v-model="taskDefinition.stringRetryButton"
      /></label>
    </div>

    <h2>Lösungen</h2>
    <div class="h5pBehaviorSetting">
      <label>
        <input
          type="checkbox"
          v-model="taskDefinition.showSolutionsAllowed"
          @change="
            !taskDefinition.showSolutionsAllowed
              ? (taskDefinition.allBlanksMustBeFilledForSolutions =
                  taskDefinition.showSolutionsAllowed)
              : ''
          "
        />
        Lösungen können angezeigt werden
      </label>
    </div>
    <div
      :class="
        taskDefinition.showSolutionsAllowed
          ? 'h5pBehaviorSetting'
          : 'h5pBehaviorSetting-disabled'
      "
    >
      <label>
        Text im Button:
        <input
          type="text"
          :disabled="!taskDefinition.showSolutionsAllowed"
          v-model="taskDefinition.stringSolutionsButton"
        />
      </label>
    </div>
    <div
      :class="
        taskDefinition.showSolutionsAllowed
          ? 'h5pBehaviorSetting'
          : 'h5pBehaviorSetting-disabled'
      "
    >
      <label>
        <input
          type="checkbox"
          :disabled="!taskDefinition.showSolutionsAllowed"
          v-model="taskDefinition.allBlanksMustBeFilledForSolutions"
        />
        Alle Lücken müssen ausgefüllt sein, um Lösungen anzuzeigen.
      </label>
    </div>
    <div
      :class="
        taskDefinition.allBlanksMustBeFilledForSolutions
          ? 'h5pBehaviorSetting'
          : 'h5pBehaviorSetting-disabled'
      "
    >
      <label>
        Mitteilung wenn nicht alle Lücken ausgefüllt sind:
        <input
          type="text"
          :disabled="!taskDefinition.allBlanksMustBeFilledForSolutions"
          v-model="taskDefinition.stringFillInAllBlanksMessage"
          style="width: 100%"
        />
      </label>
    </div>
    <!--    </fieldset>-->
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
        '*' + selectedText.trim() + '*'
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

.h5pBehaviorSetting {
  margin-top: 0.5em;
}

.h5pBehaviorSetting-disabled {
  margin-top: 0.5em;
  opacity: 50%;
}

.taskDescriptionText {
  display: block;
  width: 100%;
  height: 4em;
  resize: none;
}
</style>
