<template>
  <!--  Current undo redo state:-->
  <!--  <pre>{{ currentUndoRedoState }}</pre>-->
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Lückentext') }}</legend>

      <button @click="addBlank" class="button" type="button">
        {{ $gettext('Lücke hinzufügen') }}
      </button>
      <div>
        <studip-wysiwyg
          v-model="taskDefinition.template"
          id="ckeditorElement"
        ></studip-wysiwyg>
      </div>
    </fieldset>
    <fieldset class="collapsable">
      <legend>{{ $gettext('Einstellungen') }}</legend>
      <div>
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
        <div
          :class="
            !taskDefinition.autoCorrect ? '' : 'h5pBehaviorSetting-disabled'
          "
        >
          <label>
            {{ $gettext('Text im Button:') }}
            <input
              type="text"
              :disabled="taskDefinition.autoCorrect"
              v-model="taskDefinition.strings.checkButton"
            />
          </label>
        </div>
        <div>
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
        </div>
        <div>
          <label>
            <input
              type="checkbox"
              id="caseSensitiveCheckbox"
              v-model="taskDefinition.caseSensitive"
            />
            {{ $gettext('Groß- und Kleinschreibung beachten') }}
          </label>
        </div>
      </div>

      <h1>Versuche</h1>
      <div>
        <label>
          <input type="checkbox" v-model="taskDefinition.retryAllowed" />
          {{ $gettext('Mehrere Versuche erlauben') }}
        </label>
      </div>
      <div>
        <label
          :class="
            taskDefinition.retryAllowed ? '' : 'h5pBehaviorSetting-disabled'
          "
          >{{ $gettext('Text im Button:') }}

          <input
            type="text"
            :disabled="!taskDefinition.retryAllowed"
            v-model="taskDefinition.strings.retryButton"
        /></label>
      </div>

      <h1>{{ $gettext('Lösungen') }}</h1>
      <div>
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
      </div>
      <div
        :class="
          taskDefinition.showSolutionsAllowed
            ? ''
            : 'h5pBehaviorSetting-disabled'
        "
      >
        <label>
          {{ $gettext('Text im Button:') }}
          <input
            type="text"
            :disabled="!taskDefinition.showSolutionsAllowed"
            v-model="taskDefinition.strings.solutionsButton"
          />
        </label>
      </div>
      <div
        :class="
          taskDefinition.showSolutionsAllowed
            ? ''
            : 'h5pBehaviorSetting-disabled'
        "
      >
        <label>
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
      </div>
      <div
        :class="
          taskDefinition.allBlanksMustBeFilledForSolutions
            ? ''
            : 'h5pBehaviorSetting-disabled'
        "
      >
        <label>
          {{ $gettext('Mitteilung wenn nicht alle Lücken ausgefüllt sind:') }}
          <input
            type="text"
            :disabled="!taskDefinition.allBlanksMustBeFilledForSolutions"
            v-model="taskDefinition.strings.fillInAllBlanksMessage"
            style="width: 100%"
          />
        </label>
      </div>
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { FillInTheBlanksDefinition } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import { CKEditorInstance } from '@/ckeditor4';

export default defineComponent({
  name: 'FillInTheBlanksEditor',
  components: { StudipWysiwyg },
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as FillInTheBlanksDefinition,
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],
  },
  methods: {
    addBlank() {
      const editor = window.CKEDITOR.instances['ckeditorElement'];

      const selectedText = editor.getSelection().getSelectedText();

      const startIndex = editor.getSelection().getRanges()[0].startOffset;
      const endIndex = startIndex + selectedText.length;

      const blank = selectedText.replace(
        selectedText.trim(),
        '*' + selectedText.trim() + '*'
      );

      const template = this.taskDefinition.template;
      const newText =
        template.substring(0, startIndex) +
        blank +
        template.substring(endIndex);

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
.h5pFillInTheBlanksEditor {
  display: block;
  width: 100%;
  height: 20em;
  resize: none;
}

.h5pBehaviorSetting-disabled {
  opacity: 50%;
}
</style>
