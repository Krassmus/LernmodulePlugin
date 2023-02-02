<template>
  <!--  Current undo redo state:-->
  <!--  <pre>{{ currentUndoRedoState }}</pre>-->
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Lückentext') }}</legend>
      <button @click="addBlank" class="button" type="button">
        {{ $gettext('Lücke hinzufügen') }}
      </button>
      <studip-wysiwyg v-model="taskDefinition.template" id="ckeditorElement" />
    </fieldset>
    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Einstellungen') }}</legend>
      <h1>{{ $gettext('Korrektur') }}</h1>
      <label>
        {{ $gettext('Korrigiert wird') }}
        <select v-model="taskDefinition.autoCorrect">
          <option :value="false">
            {{ $gettext('manuell per Button') }}
          </option>
          <option :value="true">
            {{ $gettext('automatisch nach Eingabe') }}
          </option>
        </select>
      </label>
      <label>
        {{ $gettext('Text im Button:') }}
        <input
          type="text"
          :disabled="taskDefinition.autoCorrect"
          v-model="taskDefinition.strings.checkButton"
        />
      </label>
      <label>
        {{
          $gettext(
            'Ergebnismitteilung (mögliche Variablen :correct und :total):'
          )
        }}
        <input
          type="text"
          v-model="taskDefinition.strings.resultMessage"
          style="width: 100%"
        />
      </label>
      <label>
        <input
          type="checkbox"
          id="caseSensitiveCheckbox"
          v-model="taskDefinition.caseSensitive"
        />
        {{ $gettext('Groß- und Kleinschreibung beachten') }}
      </label>

      <h1>Versuche</h1>
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

      <h1>{{ $gettext('Lösungen') }}</h1>
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
      <label
        :class="taskDefinition.showSolutionsAllowed ? '' : 'setting-disabled'"
      >
        <input
          type="checkbox"
          :disabled="!taskDefinition.showSolutionsAllowed"
          v-model="taskDefinition.allBlanksMustBeFilledForSolutions"
        />
        {{
          $gettext(
            'Alle Lücken müssen ausgefüllt sein, um Lösungen anzuzeigen.'
          )
        }}
      </label>
      <label
        :class="
          taskDefinition.allBlanksMustBeFilledForSolutions
            ? ''
            : 'setting-disabled'
        "
      >
        {{ $gettext('Mitteilung wenn nicht alle Lücken ausgefüllt sind:') }}
        <input
          type="text"
          :disabled="!taskDefinition.allBlanksMustBeFilledForSolutions"
          v-model="taskDefinition.strings.fillInAllBlanksMessage"
          style="width: 100%"
        />
      </label>
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { FillInTheBlanksTaskDefinition } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'FillInTheBlanksEditor',
  components: { StudipWysiwyg },
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as FillInTheBlanksTaskDefinition,
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],
  },
  methods: {
    $gettext,
    addBlank() {
      const editor = window.CKEDITOR.instances['ckeditorElement'];

      const selectedText = editor.getSelection().getSelectedText();

      const blank = selectedText.replace(
        selectedText.trim(),
        '*' + selectedText.trim() + '*'
      );

      editor.insertText(blank);

      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...this.taskDefinition,
          template: editor.getData(),
        },
        undoBatch: { type: 'editFillInTheBlanksTemplate' },
      });
    },
  },
});
</script>

<style scoped></style>
