<!-- Allow us to mutate the prop 'taskDefinition' as much as we want-->
<!-- TODO refrain from mutating taskDefinition directly -- it breaks undo/redo-->
<!-- eslint-disable vue/no-mutating-props -->
<template>
  <!--  The current task: {{ task }}-->
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Question') }}</legend>
      <label>
        <studip-wysiwyg
          v-model="taskDefinition.question"
          force-soft-breaks
          remove-wrapping-p-tag
          disable-autoformat
        />
      </label>
      <fieldset
        class="collapsable collapsed"
        v-for="(answer, i) in taskDefinition.answers"
        :key="i"
      >
        <legend>{{ answer.text }}</legend>
        <div class="flex-parent-element">
          <input
            class="flex-child-element checkbox"
            type="checkbox"
            v-model="taskDefinition.answers[i].correct"
          />
          <input
            class="flex-child-element textbox"
            type="text"
            v-model="taskDefinition.answers[i].text"
          />
          <button
            type="button"
            class="flex-child-element removeAnswerButton"
            @click="removeAnswer(answer)"
          >
            <img :src="urlForIcon('trash')" :title="$gettext('Löschen')" />
          </button>
        </div>

        <fieldset class="collapsable collapsed feedback">
          <legend>{{ $gettext('Hinweis und Feedback') }}</legend>
          <label>
            <span>{{ $gettext('Hinweis') }}</span>
            <input
              class="textbox"
              type="text"
              v-model="taskDefinition.answers[i].strings.hint"
            />
          </label>

          <label>
            <span>{{ $gettext('Feedback, wenn ausgewählt') }}</span>
            <input
              class="textbox"
              type="text"
              v-model="taskDefinition.answers[i].strings.feedbackSelected"
            />
          </label>

          <label>
            <span>{{ $gettext('Feedback, wenn nicht ausgewählt') }}</span>
            <input
              class="textbox"
              type="text"
              v-model="taskDefinition.answers[i].strings.feedbackNotSelected"
            />
          </label>
        </fieldset>
      </fieldset>
      <button type="button" class="button" @click="addAnswer">
        {{ $gettext('Neue Antwort') }}
      </button>
    </fieldset>

    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Einstellungen') }}</legend>

      <label
        >{{ $gettext('Text im Button:') }}

        <input type="text" v-model="taskDefinition.strings.checkButton" />
      </label>

      <label>
        <input type="checkbox" v-model="taskDefinition.canAnswerMultiple" />
        {{ $gettext('Mehrere Antworten können ausgewählt werden') }}
      </label>

      <label>
        <input type="checkbox" v-model="taskDefinition.randomOrder" />
        {{ $gettext('Zeige Antworten in zufälliger Reihenfolge') }}
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
        <input type="checkbox" v-model="taskDefinition.showSolutionsAllowed" />
        {{ $gettext('Lösungen können angezeigt werden') }}
      </label>
      <label
        :class="taskDefinition.showSolutionsAllowed ? '' : 'setting-disabled'"
        >{{ $gettext('Text im Button:') }}

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
            {{ $gettext('Prozent') }}
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
              @click="removeFeedback(feedback)"
            >
              <img :src="urlForIcon('trash')" width="16" height="16" />
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
// Allow us to mutate the prop 'taskDefinition' as much as we want
// TODO refrain from mutating taskDefinition directly -- it breaks undo/redo
/* eslint-disable vue/no-mutating-props */
import { defineComponent, inject, PropType } from 'vue';
import {
  Feedback,
  QuestionAnswer,
  QuestionTask,
} from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import { $gettext } from '@/language/gettext';
import produce from 'immer';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';

export default defineComponent({
  name: 'QuestionEditor',
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  components: { StudipWysiwyg },
  methods: {
    $gettext,
    addAnswer(): void {
      this.taskDefinition.answers.push({
        text: this.$gettext('Neue Antwort'),
        correct: true,
        strings: {
          hint: '',
          feedbackSelected: '',
          feedbackNotSelected: '',
        },
      });
    },
    removeAnswer(answerToRemove: QuestionAnswer): void {
      this.taskDefinition.answers = this.taskDefinition.answers.filter(
        (answer) => answer !== answerToRemove
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
    addFeedback(): void {
      // Old version directly mutating taskDefinition (breaks undo/redo)
      // this.taskDefinition.feedback.push({
      //   percentage: this.feedbackSortedByScore[0]?.percentage,
      //   message: 'Feedback',
      // });
      // Rewrite to use performEdit over the store (Will not work in Interactive Video)
      // taskEditorStore.performEdit({
      //   newTaskDefinition: produce(
      //     this.taskDefinition,
      //     (taskDraft: FillInTheBlanksTask) => {
      //       taskDraft.feedback.push({
      //         percentage: this.feedbackSortedByScore[0]?.percentage,
      //         message: 'Feedback',
      //       });
      //     }
      //   ),
      //   undoBatch: {},
      // });

      // Rewrite using provide/inject (will work in all of the cases we are
      // considering -- Multiple tasks on the same page,or tasks included inside
      // of each other a la Interactive Video).
      this.taskEditor!.performEdit({
        newTaskDefinition: produce(
          this.taskDefinition,
          (taskDraft: QuestionTask) => {
            taskDraft.feedback.push({
              percentage: this.feedbackSortedByScore[0]?.percentage,
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
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as QuestionTask,

    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],

    feedbackSortedByScore(): Feedback[] {
      return this.taskDefinition.feedback
        .map((value) => value)
        .sort((a, b) => b.percentage - a.percentage);
    },
  },
});
</script>

<style scoped>
.flex-parent-element {
  display: flex;
  width: 100%;
  max-width: 480em;
  align-items: center;
  justify-content: flex-start;
  padding-bottom: 1ex;
}

.flex-child-element {
  margin-right: 0.25em;
}

.removeAnswerButton {
  align-self: stretch; /* Make the item fill the available height */
  display: flex;
  align-items: center;
}

.feedback {
  margin: 0.5em 0 0 0;
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
</style>
