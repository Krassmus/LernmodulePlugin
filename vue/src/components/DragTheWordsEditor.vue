<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Lückentext') }}</legend>
      <button @click="addBlank" class="button" type="button">
        {{ $gettext('Lücke hinzufügen') }}
      </button>
      <div>
        <textarea
          v-model="taskDefinition.template"
          ref="theTextArea"
          class="h5pFillInTheBlanksEditor"
        />
      </div>
    </fieldset>

    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Einstellungen') }}</legend>

      <label>
        {{ $gettext('Korrigiert wird') }}
        <select v-model="taskDefinition.instantFeedback">
          <option :value="false">
            {{ $gettext('manuell per Button') }}
          </option>
          <option :value="true">
            {{ $gettext('automatisch nach Eingabe') }}
          </option>
        </select>
      </label>

      <label :class="taskDefinition.instantFeedback ? 'setting-disabled' : ''">
        {{ $gettext('Text im Button:') }}
        <input
          type="text"
          :disabled="taskDefinition.instantFeedback"
          v-model="taskDefinition.strings.checkButton"
        />
      </label>

      <label>
        <input type="checkbox" v-model="taskDefinition.alphabeticOrder" />
        {{ $gettext('Antworten alphabetisch sortieren') }}
      </label>

      <label>
        <input type="checkbox" v-model="taskDefinition.retryAllowed" />
        {{ $gettext('Mehrere Versuche erlauben') }}
      </label>
      <label :class="taskDefinition.retryAllowed ? '' : 'setting-disabled'"
        >{{ $gettext('Text im Button:') }}

        <input
          type="text"
          :disabled="!taskDefinition.retryAllowed"
          v-model="taskDefinition.strings.retryButton"
        />
      </label>

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
        {{ $gettext('Lösungen können angezeigt werden') }}
      </label>
      <label
        :class="taskDefinition.showSolutionsAllowed ? '' : 'setting-disabled'"
      >
        {{ $gettext('Text im Button:') }}
        <input
          type="text"
          :disabled="!taskDefinition.showSolutionsAllowed"
          v-model="taskDefinition.strings.solutionsButton"
        />
      </label>
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { DragTheWordsTaskDefinition } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import { $gettext } from '../language/gettext';

export default defineComponent({
  name: 'DragTheWordsEditor',
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as DragTheWordsTaskDefinition,
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],
  },
  methods: {
    $gettext,
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

.h5pTextArea {
  display: block;
  width: 100%;
  height: 4em;
  resize: none;
  /*border: none;*/
}
</style>
