<template>
  <!--  Current undo redo state:-->
  <!--  <pre>{{ currentUndoRedoState }}</pre>-->
  <div>
    <form class="default">
      <section class="contentbox">
        <header>
          <h1>{{ $gettext('Lückentext') }}</h1>
        </header>
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
      </section>

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
        <!--    </fieldset>-->
      </fieldset>
    </form>
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

.h5pBehaviorSetting-disabled {
  opacity: 50%;
}

.h5pTextArea {
  display: block;
  width: 100%;
  height: 4em;
  resize: none;
  /*border: none;*/
}
</style>
