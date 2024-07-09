<template>
  <div class="h5p-module" ref="wrapperElement">
    <div class="fill-in-the-blanks-text">
      <template v-for="element in parsedTemplate" :key="element.uuid">
        <span v-if="element.type === 'staticText'" v-html="element.text" />
        <template v-else-if="element.type === 'blank'">
          <input
            type="text"
            v-model="userInputs[element.uuid]"
            :readonly="!this.editable"
            :disabled="!this.editable"
            :class="classForInput(element)"
            @blur="onInputBlurOrEnter"
            @keyup.enter="onInputBlurOrEnter"
            @input="onInput"
          />
          <label v-if="element.hint">
            <span class="tooltip tooltip-icon" :data-tooltip="element.hint" />
          </label>
          <span
            v-if="showSolutions && !submittedAnswerIsCorrect(element)"
            class="h5p-solution"
          >
            {{ element.solutions[0] }}
          </span>
        </template>
      </template>
    </div>

    <FeedbackElement
      v-if="showResults"
      :achievedPoints="correctAnswers"
      :maxPoints="maxPoints"
      :feedback="task.feedback"
      :resultMessage="resultMessage"
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
        @click="onClickCheck(false)"
        type="button"
        class="h5p-button"
      />

      <template v-if="showExtraButtons">
        <button
          v-if="showSolutionButton"
          v-text="this.task.strings.solutionsButton"
          @click="onClickShowSolution"
          type="button"
          class="h5p-button"
        />

        <button
          v-if="showRetryButton"
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
import { Feedback, FillInTheBlanksTask } from '@/models/TaskDefinition';
import { v4 as uuidv4 } from 'uuid';
import { isEqual, round } from 'lodash';
import { $gettext } from '@/language/gettext';
import FeedbackElement from '@/components/FeedbackElement.vue';

type FillInTheBlanksElement = Blank | StaticText;
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

const dljs = require('damerau-levenshtein-js');

export default defineComponent({
  name: 'FillInTheBlanksViewer',
  components: {
    FeedbackElement,
  },
  props: {
    task: {
      type: Object as PropType<FillInTheBlanksTask>,
      required: true,
    },
  },
  data() {
    return {
      userInputs: {} as Record<Uuid, string>,
      submittedAnswers: null as Record<Uuid, string> | null,
      userWantsToSeeSolutions: false,
      editable: true,
    };
  },
  methods: {
    $gettext,

    submittedAnswerIsCorrect(element: FillInTheBlanksElement): boolean {
      const blank = element as Blank;

      const submittedAnswer = this.submittedAnswers?.[blank.uuid];
      if (!submittedAnswer) {
        return false;
      } else {
        return blank.solutions.some((solution) =>
          this.isAnswerCorrect(submittedAnswer, solution)
        );
      }
    },

    isAnswerCorrect(userAnswer: string, solution: string): boolean {
      if (this.task.caseSensitive) {
        if (this.task.acceptTypos) {
          return this.isAnswerCorrectWithTypo(userAnswer, solution);
        } else {
          return userAnswer === solution;
        }
      } else {
        // TODO: Was ist, wenn das in einem Sprachkurs mit einer nichtlateinischen Schrift verwendet wird? :D
        if (this.task.acceptTypos) {
          return this.isAnswerCorrectWithTypo(
            userAnswer.toLowerCase(),
            solution.toLowerCase()
          );
        } else {
          return userAnswer.toLowerCase() === solution.toLowerCase();
        }
      }
    },

    isAnswerCorrectWithTypo(userAnswer: string, solution: string): boolean {
      if (userAnswer === solution) return true;

      // Calculate the Damerau-Levenshtein distance between the answer and the solution
      const distance = dljs.distance(userAnswer, solution);

      // If the word is between 4 and 9 characters, a distance of 1 will be accepted
      // If the word is more than 9 characters long, a distance of 2 will be accepted
      return (
        (userAnswer.length > 3 && distance <= 1) ||
        (userAnswer.length > 9 && distance <= 2)
      );
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

    /**
     * Adapted from the H5P source code, MIT License
     * https://github.com/h5p/h5p-blanks/blob/e9bf6862211c082a5724d9873496e66c489d23f7/js/blanks.js#L401
     * TODO: Put the license in the appropriate place
     */
    autoGrowTextField(input: HTMLInputElement) {
      // Do not set text field size when separate lines is enabled
      // if (this.params.behaviour.separateLines) {
      //   return;
      // }
      const computedStyles = window.getComputedStyle(input, null);
      const computedFontSize = computedStyles.getPropertyValue('font-size');
      const computedFontFamily = computedStyles.getPropertyValue('font-family');
      const computedPadding = computedStyles.getPropertyValue('padding');
      const fontSize = parseInt(computedFontSize, 10);
      const minEm = 6;
      const minPx = fontSize * minEm;
      const rightPadEm = 3.25;
      const rightPadPx = fontSize * rightPadEm;
      const static_min_pad = 0.5 * fontSize;

      setTimeout(() => {
        const tmp = document.createElement('div');
        tmp.textContent = input.value;
        const tmpStyles = {
          position: 'absolute',
          'white-space': 'nowrap',
          'font-size': computedFontSize,
          'font-family': computedFontFamily,
          padding: computedPadding,
          width: 'initial',
        };
        for (const [key, value] of Object.entries(tmpStyles)) {
          tmp.style[key as any] = value;
        }
        input.parentElement!.append(tmp);
        const width = tmp.clientWidth;
        const parentWidth = (this.$refs.wrapperElement as HTMLDivElement)
          .clientWidth;
        tmp.remove();
        if (width <= minPx) {
          // Apply min width
          input.style.width = (minPx + static_min_pad).toString() + 'px';
        } else if (width + rightPadPx >= parentWidth) {
          // Apply max width of parent
          input.style.width = (parentWidth - rightPadPx).toString() + 'px';
        } else {
          // Apply width that wraps input
          input.style.width = (width + static_min_pad).toString() + 'px';
        }
      }, 1);
    },

    onClickCheck(taskEditableAfterCheck: boolean) {
      // Save a copy of the user's inputs.
      this.submittedAnswers = { ...this.userInputs };
      this.updateAttempt();
      this.editable = taskEditableAfterCheck;
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

    onInputBlurOrEnter() {
      this.userWantsToSeeSolutions = false;
      if (this.task.autoCorrect) {
        this.onClickCheck(true);
      }
    },

    onInput(event: Event) {
      this.autoGrowTextField(event.target as HTMLInputElement);
    },

    classForInput(blank: FillInTheBlanksElement) {
      if (!this.submittedAnswers) {
        return 'h5pBlank';
      }

      if (this.submittedAnswers?.[blank.uuid] != undefined) {
        if (this.submittedAnswerIsCorrect(blank)) {
          return 'h5pBlank h5pBlankCorrect';
        } else {
          return 'h5pBlank h5pBlankIncorrect';
        }
      } else {
        if (this.task.autoCorrect) {
          return 'h5pBlank';
        } else {
          return 'h5pBlank h5pBlankIncorrect';
        }
      }
    },

    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },
  },
  computed: {
    splitTemplate(): string[] {
      // Returns an array where the even indexes are the static text portions,
      // and the odd indexes are the blanks.
      return this.task.template.split(/\*([^*]*)\*/);
    },

    parsedTemplate(): FillInTheBlanksElement[] {
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

    inputHasChanged(): boolean {
      return !isEqual(this.submittedAnswers, this.userInputs);
    },

    showExtraButtons(): boolean {
      if (this.task.autoCorrect) {
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
        !this.task.autoCorrect
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
        (!this.task.autoCorrect || this.allBlanksAreFilled) &&
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

    feedbackSortedByScore(): Feedback[] {
      return this.task.feedback
        .map((value) => value)
        .sort((a, b) => b.percentage - a.percentage);
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
input[type='text'] {
  max-height: 1em;
  font-family: Lato, sans-serif;
}

.fill-in-the-blanks-text {
  word-break: break-word;
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

.h5pBlank {
  border-radius: 0.25em;
  border: 1px solid #a0a0a0;
  /* top, right, bottom, left */
  padding: 0.1875em 0em 0.1875em 0.5em;
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
  /* Calculated to be the minPx number spit out by autoGrowTextField */
  width: 91px;
}

.h5pBlank.autocorrect:focus {
  /*  Irgendwas damit es nicht rot oder grün gehighlightet wird, während noch drin getippt wird */
}

.h5pMessage {
  font-size: 1em;
  color: #1a73d9;
  font-weight: 700;
  padding-top: 0.5em;
}
</style>
