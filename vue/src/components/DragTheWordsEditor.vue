<!-- Allow us to mutate the prop 'taskDefinition' as much as we want-->
<!-- TODO refrain from mutating taskDefinition directly -- it breaks undo/redo-->
<!-- eslint-disable vue/no-mutating-props -->
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
      <studip-wysiwyg
        v-model="taskDefinition.template"
        ref="wysiwyg"
        force-soft-breaks
        remove-wrapping-p-tag
        disable-autoformat
      />

      <label style="margin-top: 1.5ex">
        {{ $gettext('Irreführende Antworten') }}
        <span
          class="tooltip tooltip-icon"
          :data-tooltip="distratorInstructions"
        />
        <input type="text" v-model="taskDefinition.distractors" />
      </label>
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

    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Feedback') }}</legend>
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
      <label>{{
        $gettext('Benutzerdefiniertes Feedback für beliebige Punktebereiche:')
      }}</label>
      <div class="feedbackContainer">
        <div class="feedbackPercentagesChild">
          <label>
            {{ $gettext('Ab Prozent') }}
          </label>
          <template v-for="(feedback, i) in taskDefinition.feedback" :key="i">
            <input
              type="number"
              min="0"
              max="100"
              v-model="taskDefinition.feedback[i].percentage"
            />
          </template>
        </div>

        <div class="feedbackMessagesChild">
          <label>
            {{ $gettext('Mitteilung') }}
          </label>
          <div
            class="feedbackMessagesChildSubdivision"
            v-for="(feedback, i) in taskDefinition.feedback"
            :key="i"
          >
            <input type="text" v-model="taskDefinition.feedback[i].message" />
            <button
              type="button"
              :title="titleForDeleteButtonForFeedback(feedback)"
              class="remove-feedback-button"
              @click="removeFeedback(feedback)"
            >
              <img
                :src="urlForIcon('trash')"
                :alt="
                  $gettext(
                    'Trash can icon in a button used to delete a feedback interval'
                  )
                "
                width="16"
                height="16"
              />
            </button>
          </div>
        </div>
      </div>

      <button type="button" class="button" @click="addFeedback">
        {{ $gettext('Neuer Bereich') }}
      </button>
    </fieldset>
  </form>
</template>

<script lang="ts">
// Allow us to mutate the prop 'taskDefinition' as much as we want.
// TODO refrain from mutating taskDefinition directly -- it breaks undo/redo
/* eslint-disable vue/no-mutating-props */
import { defineComponent, inject, PropType } from 'vue';
import { DragTheWordsTask, Feedback } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import { $gettext } from '@/language/gettext';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import produce from 'immer';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';

export default defineComponent({
  name: 'DragTheWordsEditor',
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  components: { StudipWysiwyg },
  props: {
    taskDefinition: {
      type: Object as PropType<DragTheWordsTask>,
      required: true,
    },
  },
  computed: {
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],

    instructions(): string {
      return $gettext(
        'Um eine Lücke zu erstellen, setzen Sie ein Sternchen (*) vor und hinter das korrekte Wort oder markieren Sie das Wort und klicken Sie auf den Button „Lücke hinzufügen“. Sie können auch einen Tooltip hinzufügen, indem Sie einen Doppelpunkt (:) vor den Tooltip-Text schreiben.'
      );
    },

    distratorInstructions(): string {
      return $gettext(
        'Geben Sie falsche Antworten als Distraktoren ein. Verwenden Sie das gleiche Sternchen (*)-Schema wie im Text.'
      );
    },

    feedbackSortedByScore(): Feedback[] {
      return this.taskDefinition.feedback
        .map((value) => value)
        .sort((a, b) => b.percentage - a.percentage);
    },
  },
  methods: {
    $gettext,

    /**
     * Surround the selected text with two asterisks
     */
    addBlank() {
      const wysiwygEl = (this.$refs.wysiwyg as any)?.$el;
      const editor = window.STUDIP.wysiwyg.getEditor(wysiwygEl);
      if (!editor) {
        console.error('getEditor(wysiwygEl) returned: ', editor);
        throw new Error('Could not get reference to wysiwyg editor');
      }
      const selection = editor.model.document.selection;
      const start = selection.getFirstPosition();
      const end = selection.getLastPosition();
      if (!start || !end) {
        console.error('selection start: ', start, ' selection end: ', end);
        throw new Error('Could not get selection in editor');
      }
      editor.model.change((writer) => {
        writer.insertText('*', end);
        writer.insertText('*', start);
      });
    },

    addFeedback(): void {
      // Rewrite using provide/inject (will work in all of the cases we are
      // considering -- Multiple tasks on the same page,or tasks included inside
      // of each other a la Interactive Video).
      const percentage =
        this.feedbackSortedByScore?.length > 0
          ? Math.min(this.feedbackSortedByScore[0].percentage * 2, 100)
          : 25; // Default to 100 if no feedback is available

      this.taskEditor!.performEdit({
        newTaskDefinition: produce(
          this.taskDefinition,
          (taskDraft: DragTheWordsTask) => {
            taskDraft.feedback.push({
              percentage: percentage,
              message: 'Feedback',
            });
          }
        ),
        undoBatch: {},
      });
    },

    removeFeedback(feedbackToRemove: Feedback): void {
      this.taskDefinition.feedback = this.taskDefinition.feedback.filter(
        (feedback) => feedback !== feedbackToRemove
      );
    },

    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },

    titleForDeleteButtonForFeedback(feedback: Feedback): string {
      return this.$gettext(
        'Entferne den Feedback-Bereich, der ab %{ percentage }% anfängt.',
        { percentage: feedback.percentage.toString() }
      );
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

.feedbackContainer {
  display: flex;
  gap: 0.5em;
  justify-content: flex-start;
  align-items: center;
  max-width: 48em;
}

.feedbackPercentagesChild {
  flex: 0 100px;
  display: flex;
  flex-direction: column;
}

.feedbackMessagesChild {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.feedbackMessagesChildSubdivision {
  display: flex;
  align-items: center;
  gap: 0.5em;
}

.remove-feedback-button {
  display: flex;
  align-items: center;
  height: 28px;
}
</style>
