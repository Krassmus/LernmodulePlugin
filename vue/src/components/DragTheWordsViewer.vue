<template>
  <div class="h5p-module" ref="wrapperElement">
    <template v-for="element in parsedTemplate" :key="element.uuid">
      <span v-if="element.type === 'staticText'" v-html="element.text" />
      <template v-else-if="element.type === 'blank'">
        <span
          v-if="userInputs[element.uuid]"
          :class="classForFilledBlank(element)"
          :draggable="editable"
          @dragstart="
            startDragUsedAnswer($event, element, userInputs[element.uuid])
          "
          @drop="onDropBlank($event, element)"
          @dragover.prevent
          @dragenter.prevent
        >
          {{ getAnswerById(userInputs[element.uuid])?.text }}
        </span>
        <span
          v-else
          class="h5pBlank"
          @drop="onDropBlank($event, element)"
          @dragover.prevent
          @dragenter.prevent
          @click="onClickBlank(element)"
          >&#8203;
        </span>
        <label v-if="element.hint">
          <span class="tooltip tooltip-icon" :data-tooltip="element.hint" />
        </label>
        <span
          v-if="showSolutions && !submittedAnswerIsCorrect(element)"
          class="h5p-solution"
        >
          {{ element.solution }}
        </span>
      </template>
    </template>

    <div
      class="unused-answers-list"
      @drop="onDropUnusedAnswers($event)"
      @dragover.prevent
      @dragenter.prevent
    >
      <div v-for="answer in unusedAnswers" :key="answer.uuid">
        <span
          class="h5pBlankSolution"
          :class="{ disabled: !this.editable }"
          :draggable="editable"
          @dragstart="startDragUnusedAnswer($event, answer)"
          @click="onClickUnusedAnswer(answer)"
        >
          {{ answer.text }}
        </span>
      </div>
    </div>

    <FeedbackElement
      v-if="showResults"
      :achieved-points="correctAnswers"
      :max-points="maxPoints"
      :feedback="task.feedback"
      :result-message="resultMessage"
    />

    <div
      v-if="showFillInAllTheBlanksMessage"
      class="h5pMessage"
      v-text="fillInAllTheBlanksMessage"
    />

    <div class="h5p-button-panel">
      <button
        v-if="showCheckButton"
        v-text="this.task.strings.checkButton"
        @click="onClickCheck"
        type="button"
        class="h5p-button"
      />

      <template v-if="showExtraButtons">
        <button
          v-if="!showSolutions && this.task.showSolutionsAllowed"
          v-text="this.task.strings.solutionsButton"
          @click="onClickShowSolution"
          type="button"
          class="h5p-button"
        />

        <button
          v-if="this.task.retryAllowed"
          v-text="this.task.strings.retryButton"
          @click="onClickTryAgain"
          type="button"
          class="h5p-button"
        />
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { DragTheWordsTask, Feedback } from '@/models/TaskDefinition';
import { v4 as uuidv4 } from 'uuid';
import { isEqual, round } from 'lodash';
import { $gettext } from '@/language/gettext';
import FeedbackElement from '@/components/FeedbackElement.vue';

type DragTheWordsElement = Blank | StaticText;

interface Blank {
  type: 'blank';
  uuid: Uuid;
  solution: string;
  hint: string;
}

interface Answer {
  uuid: Uuid;
  text: string;
}

type StaticText = {
  type: 'staticText';
  uuid: Uuid;
  text: string;
};

type Uuid = string;

export default defineComponent({
  name: 'DragTheWordsViewer',
  components: { FeedbackElement },
  props: {
    task: {
      type: Object as PropType<DragTheWordsTask>,
      required: true,
    },
  },
  data() {
    return {
      // Map from Blank IDs to Answer IDs
      userInputs: {} as Record<Uuid, Uuid>,
      submittedAnswers: null as Record<Uuid, Uuid> | null,
      userWantsToSeeSolutions: false,
      draggedAnswerId: undefined as Uuid | undefined,
      draggedSourceId: undefined as Uuid | undefined,
      clickedAnswerId: undefined as Uuid | undefined,
      editable: true as Boolean,
    };
  },
  methods: {
    $gettext,

    submittedAnswerIsCorrect(blank: Blank): boolean {
      const submittedAnswerId = this.submittedAnswers?.[blank.uuid];
      if (!submittedAnswerId) return false;

      const submittedAnswer = this.getAnswerById(submittedAnswerId)?.text;
      if (!submittedAnswer) return false;

      return this.isAnswerCorrect(submittedAnswer, blank.solution);
    },

    isAnswerCorrect(userAnswer: string, solution: string): boolean {
      return userAnswer === solution;
    },

    updateAttempt() {
      // Tell the server which blanks were filled out correctly.
      const points = {} as Record<string, number>;
      this.blanks.forEach((blank, index) => {
        // TODO Consider what the key should be. This is a bit ugly.
        // The key is shown directly in the 'assessment' UI.
        // Maybe we should change how that works?
        points[`${index} - ${blank.solution}`] = this.submittedAnswerIsCorrect(
          blank
        )
          ? 1
          : 0;
      });
      this.$emit('updateAttempt', {
        points,
        // The attempt is marked as successful if all answers were correct.
        success: this.correctAnswers === this.blanks.length,
      });
    },

    getElement(elementId: Uuid): DragTheWordsElement {
      const el = this.parsedTemplate.find((el) => el.uuid === elementId);
      if (!el) {
        throw new Error(
          'The given element does not exist in parsedTemplate: ' + elementId
        );
      }
      return el;
    },

    startDragUsedAnswer(
      dragEvent: DragEvent,
      blank: Blank,
      answerId: Uuid
    ): void {
      if (!this.editable) return;
      if (dragEvent.dataTransfer) {
        dragEvent.dataTransfer.dropEffect = 'move';
        dragEvent.dataTransfer.effectAllowed = 'move';
      }
      const answer = this.getAnswerById(answerId);
      console.log('Dragging answer: ', answer, 'From blank: ', blank);

      for (const property in this.userInputs) {
        console.log(property);
      }

      this.draggedAnswerId = answerId;
    },

    startDragUnusedAnswer(dragEvent: DragEvent, answer: Answer): void {
      if (dragEvent.dataTransfer) {
        dragEvent.dataTransfer.dropEffect = 'move';
        dragEvent.dataTransfer.effectAllowed = 'move';

        console.log('Dragging answer: ', answer);
        this.draggedAnswerId = answer.uuid;
      }
    },

    onDropBlank(dragEvent: DragEvent, blank: Blank): void {
      if (!this.draggedAnswerId) {
        throw new Error('Dragged answer id is undefined');
      }

      console.log('Dropped answer :', this.draggedAnswer, 'on blank: ', blank);

      // Remove the dropped answer from the blank, if any, where it has been dragged from
      const previousBlank = this.blanks.find(
        (blank) => this.userInputs[blank.uuid] === this.draggedAnswerId
      );

      if (previousBlank) {
        delete this.userInputs[previousBlank.uuid];
      }

      // Set the answer for the blank where the answer was dropped
      this.userInputs[blank.uuid] = this.draggedAnswerId;

      this.draggedAnswerId = undefined;
    },

    onDropUnusedAnswers(ev: DragEvent): void {
      console.error('Not implemented');
      ev.preventDefault(); // Indicate that dropping is al
      if (!this.draggedAnswer) {
        throw new Error('draggedAnswer is undefined');
      }
      // Remove the dropped answer from the blank, if any, where it has been dragged from
      const blank = this.blanks.find(
        (blank) => this.userInputs[blank.uuid] === this.draggedAnswerId
      );
      if (blank) {
        delete this.userInputs[blank.uuid];
      }
    },

    onClickCheck() {
      // Save a copy of the user's inputs.
      this.submittedAnswers = { ...this.userInputs };
      this.editable = false;
    },

    onClickShowSolution() {
      this.userWantsToSeeSolutions = true;
    },

    onClickTryAgain() {
      this.userWantsToSeeSolutions = false;
      this.userInputs = {};
      this.submittedAnswers = null;
      this.editable = true;
    },

    classForFilledBlank(blank: Blank) {
      if (!this.submittedAnswers && !this.task.instantFeedback) {
        return 'h5pFilledBlank';
      }

      if (this.task.instantFeedback) {
        this.submittedAnswers = { ...this.userInputs };
      }

      if (this.userInputs?.[blank.uuid]) {
        if (this.submittedAnswerIsCorrect(blank)) {
          return 'h5pFilledBlank h5pBlankCorrect disabled';
        } else {
          return 'h5pFilledBlank h5pBlankIncorrect disabled';
        }
      } else {
        return 'h5pBlank';
      }
    },

    getAnswerById(id: Uuid): Answer | undefined {
      return this.answers.find((answer) => answer.uuid === id);
    },

    onClickUnusedAnswer(answer: Answer): void {
      console.log('Clicked answer:', answer);
      if (this.editable) this.clickedAnswerId = answer.uuid;
    },

    onClickBlank(blank: Blank): void {
      console.log('Clicked blank:', blank);
      if (!this.clickedAnswerId) return;
      this.userInputs[blank.uuid] = this.clickedAnswerId;
      this.clickedAnswerId = undefined;
    },
  },
  computed: {
    splitTemplate(): string[] {
      // Returns an array where the even indexes are the static text portions,
      // and the odd indexes are the blanks.
      return this.task.template.split(/\*([^*]*)\*/);
    },

    parsedTemplate(): DragTheWordsElement[] {
      return this.splitTemplate.map((value, index) => {
        if (index % 2 === 0) {
          return { type: 'staticText', text: value, uuid: uuidv4() };
        } else {
          let splitString = value.split(':');
          let solution = splitString[0];
          let hint = splitString[1];
          return {
            type: 'blank',
            solution: solution,
            hint: hint,
            uuid: uuidv4(),
          };
        }
      });
    },

    blanks(): Blank[] {
      return this.parsedTemplate.filter(
        (word) => word.type === 'blank'
      ) as Blank[];
    },

    answers(): Answer[] {
      return this.blanks.map((blank) => ({
        uuid: uuidv4(),
        text: blank.solution,
      }));
    },

    draggedAnswer(): Answer | undefined {
      if (!this.draggedAnswerId) {
        return undefined;
      } else {
        return this.getAnswerById(this.draggedAnswerId);
      }
    },

    randomizedAnswers(): Answer[] {
      // https://stackoverflow.com/a/46545530
      return this.answers
        .map((value) => ({ value, sort: Math.random() }))
        .sort((a, b) => a.sort - b.sort)
        .map(({ value }) => value);
    },

    sortedAnswers(): Answer[] {
      // https://stackoverflow.com/a/45544166
      return this.answers
        .map((value) => value)
        .sort((a, b) => a.text.localeCompare(b.text));
    },

    blanksFilled(): number {
      if (!this.submittedAnswers) {
        return 0;
      } else {
        return Object.keys(this.submittedAnswers).length;
      }
    },

    allBlanksAreFilled(): boolean {
      return this.blanksFilled == this.blanks.length;
    },

    correctAnswers(): number {
      return this.blanks.filter((blank) => this.submittedAnswerIsCorrect(blank))
        .length;
    },

    allAnswersAreCorrect(): boolean {
      return this.blanks.every((blank) => this.submittedAnswerIsCorrect(blank));
    },

    maxPoints(): number {
      return this.blanks.length;
    },

    unusedAnswers(): Answer[] {
      if (this.task.alphabeticOrder) {
        return this.sortedAnswers.filter(
          (answer) => !Object.values(this.userInputs).includes(answer.uuid)
        );
      } else {
        return this.randomizedAnswers.filter(
          (answer) => !Object.values(this.userInputs).includes(answer.uuid)
        );
      }
    },

    inputHasChanged(): boolean {
      return !isEqual(this.submittedAnswers, this.userInputs);
    },

    showExtraButtons(): boolean {
      if (this.task.instantFeedback) {
        if (this.allBlanksAreFilled) {
          return (
            this.submittedAnswers !== null &&
            !this.inputHasChanged &&
            !this.allAnswersAreCorrect
          );
        } else {
          return false;
        }
      } else {
        return (
          this.submittedAnswers !== null &&
          !this.inputHasChanged &&
          !this.allAnswersAreCorrect
        );
      }
    },

    showCheckButton(): boolean {
      return (
        (this.submittedAnswers === null || this.inputHasChanged) &&
        !this.task.instantFeedback
      );
    },

    showSolutionButton(): boolean {
      return !this.showSolutions && this.task.showSolutionsAllowed;
    },

    showRetryButton(): boolean {
      return this.task.retryAllowed && this.submittedAnswers !== null;
    },

    showSolutions(): boolean {
      return (
        this.userWantsToSeeSolutions &&
        (this.allBlanksAreFilled ||
          !this.task.allBlanksMustBeFilledForSolutions)
      );
    },

    showFillInAllTheBlanksMessage(): boolean {
      return (
        this.task.allBlanksMustBeFilledForSolutions &&
        this.userWantsToSeeSolutions &&
        !this.allBlanksAreFilled &&
        !this.inputHasChanged
      );
    },

    showResults(): boolean {
      return (
        !(this.submittedAnswers === null) &&
        !this.showFillInAllTheBlanksMessage &&
        (!this.task.instantFeedback || this.allBlanksAreFilled) &&
        !this.inputHasChanged
      );
    },

    resultMessage(): string {
      let resultMessage = this.task.strings.resultMessage.replace(
        ':correct',
        this.correctAnswers.toString()
      );

      resultMessage = resultMessage.replace(
        ':total',
        this.blanks.length.toString()
      );

      return resultMessage;
    },

    fillInAllTheBlanksMessage(): string {
      return this.task.strings.fillInAllBlanksMessage
        ? this.task.strings.fillInAllBlanksMessage
        : $gettext(
            'Alle Lücken müssen ausgefüllt sein, um Lösungen anzuzeigen'
          );
    },
  },
});
</script>

<style scoped>
.h5pStaticText {
  display: inline;
  background: #ffffff;
  color: #000000;
  line-height: 1.875em; /* makes the static text line height the same as the blanks 1.75em line height + 1px border top and bottom */
  margin: 0.1em 0 0 0; /* top, right, bottom, left */
}

.h5pBlank {
  display: inline-block;
  background: #ffffff;
  color: #000000;
  border: 1px solid #a0a0a0;
  border-radius: 0.25em;
  margin: 0.1em 0.1em 0 0.1em; /* top, right, bottom, left */
  min-width: 9em;
}

.h5pFilledBlank {
  display: inline-flex;
  justify-content: center;
  background: #ffffff;
  color: #000000;
  border: 1px solid #a0a0a0;
  border-radius: 0.25em;
  margin: 0.1em 0.1em 0 0.1em; /* top, right, bottom, left */
  min-width: 9em;
  cursor: grabbing;
}

.h5pBlankSolution {
  line-height: 1.25;
  cursor: grabbing;
  border-radius: 0.25em;
  padding: 0.1em 0.6em;
  margin: 0.3em;
  vertical-align: top;
  text-align: center;
  display: inline-block;
  border: 0.1em solid #c6c6c6;
  overflow: hidden;
  background: #ddd;
  box-shadow: 0 0 0.3em rgba(0, 0, 0, 0.2);
  z-index: 3;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

.h5pBlankCorrect {
  background: #9dd8bb;
  border: 1px solid #9dd8bb;
  color: #255c41;
}

.h5pBlankIncorrect {
  background-color: #f7d0d0;
  border: 1px solid #f7d0d0;
  color: #b71c1c;
  text-decoration: line-through;
}

span.item:empty:before {
  content: '\200b';
}

.unused-answers-list {
  margin-top: 0.5em;
  min-height: 2rem;
  border: 1px solid #eee;
  border-radius: 5px;
}

.h5pMessage {
  font-size: 1em;
  color: #1a73d9;
  font-weight: 700;
  padding-top: 0.5em;
}

.disabled {
  cursor: default;
}
</style>
