<template>
  <div class="h5pModule" ref="wrapperElement">
    <template v-for="element in parsedTemplate" :key="element.uuid">
      <span v-if="element.type === 'staticText'" v-html="element.text" />
      <template v-else-if="element.type === 'blank'">
        <input
          type="text"
          v-model="userInputs[element.uuid]"
          :readonly="submittedAnswerIsCorrect(element) || showSolutions"
          :disabled="submittedAnswerIsCorrect(element) || showSolutions"
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
          class="h5pSolution"
        >
          {{ element.solutions[0] }}
        </span>
      </template>
    </template>

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
            v-if="showSolutionButton"
            @click="onClickShowSolution"
            class="h5pButton"
          >
            {{ this.task.strings.solutionsButton }}
          </button>

          <button
            v-if="showRetryButton"
            @click="onClickTryAgain"
            class="h5pButton"
          >
            {{ this.task.strings.retryButton }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="debug">
      userInputs:
      <pre>{{ userInputs }}</pre>
      submittedAnswers:
      <pre>{{ submittedAnswers }}</pre>
      Split template:
      <pre>{{ splitTemplate }}</pre>
      Parsed template:
      <pre>{{ parsedTemplate }}</pre>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  Feedback,
  FillInTheBlanksTaskDefinition,
} from '@/models/TaskDefinition';
import { v4 as uuidv4 } from 'uuid';
import { isEqual, round } from 'lodash';
import { $gettext } from '@/language/gettext';

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

export default defineComponent({
  name: 'FillInTheBlanksViewer',
  props: {
    task: {
      type: Object as PropType<FillInTheBlanksTaskDefinition>,
      required: true,
    },
  },
  data() {
    return {
      userInputs: {} as Record<Uuid, string>,
      submittedAnswers: null as Record<Uuid, string> | null,
      debug: false,
      userWantsToSeeSolutions: false,
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
        return userAnswer === solution;
      } else {
        // TODO: Was ist, wenn das in einem Sprachkurs mit einer nichtlateinischen Schrift verwendet wird? :D
        return userAnswer.toLowerCase() === solution.toLowerCase();
      }
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
    onClickCheck() {
      // Save a copy of the user's inputs.
      this.submittedAnswers = { ...this.userInputs };
      this.updateAttempt();
    },
    onClickShowSolution() {
      this.userWantsToSeeSolutions = true;
    },
    onClickTryAgain() {
      this.userWantsToSeeSolutions = false;
      this.userInputs = {};
      this.submittedAnswers = null;
    },
    onInputBlurOrEnter() {
      this.userWantsToSeeSolutions = false;
      if (this.task.autoCorrect) {
        this.onClickCheck();
      }
    },
    onInput(event: InputEvent) {
      this.autoGrowTextField(event.target as HTMLInputElement);
    },
    classForInput(blank: FillInTheBlanksElement) {
      if (!this.submittedAnswers) {
        return 'h5pBlank';
      }

      if (this.userInputs?.[blank.uuid] != undefined) {
        if (this.submittedAnswerIsCorrect(blank)) {
          return 'h5pBlank h5pBlankCorrect';
        } else {
          if (
            this.submittedAnswers?.[blank.uuid] ===
            this.userInputs?.[blank.uuid]
          ) {
            return 'h5pBlank h5pBlankIncorrect';
          } else {
            return 'h5pBlank';
          }
        }
      } else {
        return 'h5pBlank';
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
input[type='text'] {
  font-size: 16px;
  line-height: 1.75em;
  font-family: Lato, sans-serif;
}

.h5pModule {
  border: 2px solid #eee;
  padding: 0.5em 0.5em 0.5em 0.5em;
  font-size: 16px;
  line-height: 1.75em;
  font-family: Lato, sans-serif;
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

.h5pBlank {
  border-radius: 0.25em;
  border: 1px solid #a0a0a0;
  /* top, right, bottom, left */
  padding: 0.1875em 0em 0.1875em 0.5em;
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
  width: 104px;
}

.h5pBlank.autocorrect:focus {
  /*  Irgendwas damit es nicht rot oder grün gehighlightet wird, während noch drin getippt wird */
}

.h5pFeedbackContainer {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: flex-start;

  margin-top: 1em;
}

.h5pFeedbackContainerTop {
}

.h5pFeedbackContainerCenter {
  display: flex;
  flex-direction: row;
  justify-content: flex-start;
  align-items: center;
  flex-wrap: nowrap;
}

.h5pFeedbackContainerBottom {
  display: flex;
  flex-direction: row;
  justify-content: flex-start;
  align-items: center;
  flex-wrap: nowrap;
}

meter {
  //display: block;
  margin: 0 auto;
  width: 200px;
}

.h5pSolution {
  color: #255c41;
  font-weight: bold;
  border: 1px #255c41 dashed;
  background-color: #d4f6e6;
  padding: 0.15em;
  border-radius: 0.25em;
  margin-left: 0.5em;
}

.h5pFeedbackText {
  font-family: sans-serif;
  font-weight: 700;
  color: #1a73d9;
  font-size: 1em;
}

.myDivWithBackground {
  /*This absolute URL to the icon will not work in production, because it has a
  hard-coded localhost in it....  But this shows how to get it working for a
  prototype, anyway.
  TODO: Ask Jan if he knows a solution to do this correctly in a plugin */
  background-image: url(http://localhost/51/assets/images/icons/blue/edit.svg);
  background-repeat: no-repeat;
}
</style>
