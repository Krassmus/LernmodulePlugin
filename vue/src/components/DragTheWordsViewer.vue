<template>
  <div class="h5pModule" ref="wrapperElement">
    <template v-for="element in parsedTemplate" :key="element.uuid">
      <span v-if="element.type === 'staticText'" class="h5pStaticText">
        {{ element.text }}
      </span>
      <template v-else-if="element.type === 'blank'">
        <span v-if="userInputs[element.uuid]" :class="classForInput(element)">
          {{ getBlankText(userInputs[element.uuid]) }}
        </span>
        <span
          v-else
          class="h5pBlank"
          @drop="onDrop($event, element)"
          @dragover.prevent
          @dragenter.prevent
          @id="element.uuid;"
          >&#8203;
        </span>
      </template>
    </template>

    <template v-for="element in unusedAnswers" :key="element.uuid">
      <div>
        <span
          class="h5pBlankSolution"
          draggable="true"
          @dragstart="startDrag($event, element)"
        >
          {{ element.solutions[0] }}
        </span>
      </div>
    </template>

    <!--    <div>-->
    <!--      <button @click="onClickCheck" v-if="showCheckButton" class="h5pButton">-->
    <!--        {{ this.task.strings.checkButton }}-->
    <!--      </button>-->

    <!--      <button-->
    <!--        v-if="showSolutionButton"-->
    <!--        @click="onClickSolution"-->
    <!--        class="h5pButton"-->
    <!--      >-->
    <!--        {{ this.task.strings.solutionsButton }}-->
    <!--      </button>-->

    <!--      <button v-if="showRetryButton" @click="onClickRetry" class="h5pButton">-->
    <!--        {{ this.task.strings.retryButton }}-->
    <!--      </button>-->
    <!--    </div>-->

    <div class="h5pFeedbackContainer">
      <div class="h5pFeedbackContainerTop">
        <div v-if="showFillInAllTheBlanksMessage" class="h5pFeedbackText">
          {{
            this.task.strings.fillInAllBlanksMessage
              ? this.task.strings.fillInAllBlanksMessage
              : $gettext(
                  'Alle Lücken müssen ausgefüllt sein, um Lösungen anzuzeigen'
                )
          }}
        </div>
        <div v-if="showResults && feedbackMessage" class="h5pFeedbackText">
          {{ this.feedbackMessage }}
        </div>
      </div>
      <div class="h5pFeedbackContainerCenter">
        <div v-if="showResults">
          <meter id="score" min="0" :max="maxPoints" :value="correctAnswers" />
          <label for="score" class="h5pFeedbackText" style="margin-left: 0.5em">
            {{ this.resultMessage }}
          </label>
        </div>
      </div>
      <div class="h5pFeedbackContainerBottom">
        <button @click="onClickCheck" v-if="showCheckButton" class="h5pButton">
          {{ this.task.strings.checkButton }}
        </button>

        <div class="h5pFeedbackResultAndButtons" v-if="showExtraButtons">
          <button
            v-if="!showSolutions && this.task.showSolutionsAllowed"
            @click="onClickShowSolution"
            class="h5pButton"
          >
            {{ this.task.strings.solutionsButton }}
          </button>

          <button
            v-if="this.task.retryAllowed"
            @click="onClickTryAgain"
            class="h5pButton"
          >
            {{ this.task.strings.retryButton }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { DragTheWordsTaskDefinition, Feedback } from '@/models/TaskDefinition';
import { v4 as uuidv4 } from 'uuid';
import { isEmpty, isEqual, round } from 'lodash';
import { $gettext } from '../language/gettext';

type DragTheWordsElement = Blank | StaticText;
type Blank = {
  type: 'blank';
  uuid: Uuid;
  solutions: string[];
  hint: string;
};
type StaticText = {
  type: 'staticText';
  uuid: Uuid;
  text: string;
};
type Uuid = string;

export default defineComponent({
  name: 'DragTheWordsViewer',
  components: {},
  props: {
    task: {
      type: Object as PropType<DragTheWordsTaskDefinition>,
      required: true,
    },
  },
  data() {
    return {
      userInputs: {} as Record<Uuid, Uuid>,
      submittedAnswers: null as Record<Uuid, string> | null,
      debug: false,
      userWantsToSeeSolutions: false,
      draggedItemId: undefined as Uuid | undefined,
    };
  },
  methods: {
    $gettext,
    submittedAnswerIsCorrect(element: DragTheWordsElement): boolean {
      const blank = element as Blank;

      const submittedAnswer = this.submittedAnswers?.[blank.uuid];
      if (!submittedAnswer) {
        return false;
      } else {
        // return blank.solutions.some((solution) =>
        //   this.isAnswerCorrect(submittedAnswer, solution)
        // );
        return this.isAnswerCorrect(submittedAnswer, blank.uuid);
      }
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
        points[`${index} - ${blank.solutions[0]}`] =
          this.submittedAnswerIsCorrect(blank) ? 1 : 0;
      });
      this.$emit('updateAttempt', {
        points,
        // The attempt is marked as successful if all answers were correct.
        success: this.correctAnswers === this.blanks.length,
      });
    },
    isAnswerUsed(elementId: Uuid): boolean {
      return Object.values(this.userInputs).includes(elementId);
    },
    getBlankText(blankId: Uuid): string {
      const el = this.getElement(blankId);
      if (el.type !== 'blank') {
        throw new Error(
          'The element with the given id is not a blank: ' + blankId
        );
      }
      return el.solutions[0];
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
    startDrag(dragEvent: DragEvent, blank: Blank): void {
      if (dragEvent.dataTransfer) {
        dragEvent.dataTransfer.dropEffect = 'move';
        dragEvent.dataTransfer.effectAllowed = 'move';
        this.draggedItemId = blank.uuid;
      }
    },
    onDrop(dragEvent: DragEvent, blank: Blank): void {
      if (!this.draggedItemId) {
        throw new Error('Dragged item id is undefined');
      }
      this.userInputs[blank.uuid] = this.draggedItemId;
      this.draggedItemId = undefined;
    },
    onClickCheck() {
      // Save a copy of the user's inputs.
      this.submittedAnswers = { ...this.userInputs };
    },
    onClickShowSolution() {
      this.userWantsToSeeSolutions = true;
    },
    onClickTryAgain() {
      this.userWantsToSeeSolutions = false;
      this.userInputs = {};
      this.submittedAnswers = null;
    },
    classForInput(blank: DragTheWordsElement) {
      if (!this.submittedAnswers) {
        return 'h5pFilledBlank';
      }

      if (this.userInputs?.[blank.uuid] != undefined) {
        if (this.submittedAnswerIsCorrect(blank)) {
          return 'h5pFilledBlank h5pBlankCorrect';
        } else {
          if (
            this.submittedAnswers?.[blank.uuid] ===
            this.userInputs?.[blank.uuid]
          ) {
            return 'h5pFilledBlank h5pBlankIncorrect';
          } else {
            return 'h5pFilledBlank';
          }
        }
      } else {
        return 'h5pBlank';
      }
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
          let solutions = splitString[0].split('/');
          let hint = splitString[1];
          return {
            type: 'blank',
            solutions: solutions,
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
    unusedAnswers(): Blank[] {
      return this.blanks.filter(
        (blank) => !Object.values(this.userInputs).includes(blank.uuid)
      );
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
    feedbackMessage(): string | undefined {
      const percentageCorrect = round(
        (this.correctAnswers / this.blanks.length) * 100
      );

      for (const feedback of this.feedbackSortedByScore) {
        if (percentageCorrect >= feedback.percentage) {
          return feedback.message;
        }
      }

      return undefined;
    },
    feedbackSortedByScore(): Feedback[] {
      return this.task.feedback
        .map((value) => value)
        .sort((a, b) => b.percentage - a.percentage);
    },
  },
});
</script>

<style scoped>
.h5pModule {
  border: 2px solid #eee;
  padding: 0.5em 0.5em 0.5em 0.5em;
}

.h5pStaticText {
  background: #ffffff;
  color: #000000;
  /*border: 1px solid #a0a0a0;*/
  display: inline-block;
  line-height: 2em;
}

.h5pBlank {
  background: #ffffff;
  color: #000000;
  border: 1px solid #a0a0a0;
  border-radius: 0.25em;
  margin: 0em 0.25em 0em 0.25em;
  display: inline-block;
  width: 9em;
}

.h5pFilledBlank {
  background: #ffffff;
  color: #000000;
  border: 1px solid #a0a0a0;
  border-radius: 0.25em;
  margin: 0em 0.25em 0em 0.25em;
  display: inline-flex;
  width: 9em;
  justify-content: center;
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

.h5pButton {
  /* top, right, bottom, left */
  margin: 1em 0.5em 1em 0em;
  /* vertical, horizontal */
  padding: 0.25em 1.25em;
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

span.item:empty:before {
  content: '\200b';
}
</style>
