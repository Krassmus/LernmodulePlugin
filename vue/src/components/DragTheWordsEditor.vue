<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Drag the Words') }}</legend>
      <div class="h5pEditorTopPanel">
        <button
          @click="addBlank"
          class="button"
          type="button"
          style="margin-right: 0.1em"
        >
          {{ $gettext('Lücke hinzufügen') }}
        </button>
        <div class="tooltip tooltip-icon" :data-tooltip="instructions" />
      </div>
      <studip-wysiwyg v-model="taskDefinition.template" ref="ckeditorElement" />
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
import { DragTheWordsTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import { $gettext } from '@/language/gettext';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';

export default defineComponent({
  name: 'DragTheWordsEditor',
  components: { StudipWysiwyg },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as DragTheWordsTask,
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],
    instructions(): string {
      return $gettext(
        'Fügen Sie Lücken hinzu, indem Sie ein Sternchen (*) vor und hinter dem korrekten Wort bzw. den Wörtern setzen oder markieren Sie ein Wort und klicken Sie den "Lücke hinzufügen"–Button.' +
          ' Außerdem können Sie einen Tooltip mit einem Doppelpunkt (:) hinzufügen.'
      );
    },
  },
  methods: {
    $gettext,
    addBlank() {
      const editor = window.STUDIP.wysiwyg.getEditor(
        this.$refs.ckeditorElement as Element
      )!;

      // TODO implement selection
      // const selectedText = editor.getSelection().getSelectedText();
      //
      // const blank = selectedText.replace(
      //   selectedText.trim(),
      //   '*' + selectedText.trim() + '*'
      // );
      //
      // editor.insertText(blank);

      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...this.taskDefinition,
          template: editor.getData(),
        },
        undoBatch: { type: 'editDragTheWordsTemplate' },
      });
    },
  },
});
</script>

<style scoped>
.h5pTextArea {
  display: block;
  width: 100%;
  height: 4em;
  resize: none;
  /*border: none;*/
}
</style>
