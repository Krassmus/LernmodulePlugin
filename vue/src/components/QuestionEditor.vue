<template>
  <!--  The current task: {{ task }}-->
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Frage') }}</legend>
      <label>
        <studip-wysiwyg v-model="taskDefinition.question" />
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
          <img
            class="flex-child-element removeAnswerButton"
            :src="urlForIcon('trash')"
            @click="removeAnswer(answer)"
          />
        </div>

        <fieldset class="collapsable collapsed">
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
            <span>{{ $gettext('Feedback wenn ausgewählt') }}</span>
            <input
              class="textbox"
              type="text"
              v-model="taskDefinition.answers[i].strings.feedbackSelected"
            />
          </label>

          <label>
            <span>{{ $gettext('Feedback wenn nicht ausgewählt') }}</span>
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
  </form>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { QuestionAnswer, QuestionTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'QuestionEditor',
  components: { StudipWysiwyg },
  props: {},
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
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as QuestionTask,
    currentUndoRedoState: () =>
      taskEditorStore.undoRedoStack[taskEditorStore.undoRedoIndex],
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
</style>
