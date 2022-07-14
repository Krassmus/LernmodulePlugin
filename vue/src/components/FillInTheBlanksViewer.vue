<template>
  <div class="h5pModule">
    <div>
      <template v-for="element in parsedTemplate" :key="element.uuid">
        <!--        <span v-if="element.type === 'staticText'" v-html="element.text" />-->
        <span v-if="element.type === 'staticText'">
          {{ element.text }}
        </span>
        <span v-else-if="element.type === 'blank'">
          <input
            type="text"
            v-model="userInputs[element.uuid]"
            :readonly="submittedAnswerIsCorrect(element) || showSolutions"
            :disabled="submittedAnswerIsCorrect(element) || showSolutions"
            :class="classForInput(element)"
            @blur="onInputBlurOrEnter"
            @keyup.enter="onInputBlurOrEnter"
          />
          <span
            v-if="showSolutions && !submittedAnswerIsCorrect(element)"
            class="h5pCorrectAnswer"
          >
            {{ element.solution }}
          </span>
        </span>
      </template>
    </div>

    <div>
      <span v-if="showResults" class="h5pAnswerFeedback"
        >You got {{ correctAnswers }} of {{ blanks.length }} blanks correct.
      </span>

      <span v-if="showFillInAllTheBlanksMessage" class="h5pAnswerFeedback">
        Please fill in all blanks to view solution
      </span>

      <button @click="onClickCheck" v-if="showCheckButton" class="h5pButton">
        Check
      </button>

      <div v-if="showExtraButtons">
        <button
          v-if="!showSolutions && this.task.showSolutionsAllowed"
          @click="onClickShowSolution"
          class="h5pButton"
        >
          Show solutions
        </button>

        <button
          v-if="this.task.retryAllowed"
          @click="onClickTryAgain"
          class="h5pButton"
        >
          Try again
        </button>
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
import { FillInTheBlanksDefinition } from '@/models/TaskDefinition';
import { v4 as uuidv4 } from 'uuid';
import { isEqual } from 'lodash';

type FillInTheBlanksElement = Blank | StaticText;
type Blank = {
  type: 'blank';
  uuid: Uuid;
  solution: string;
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
      type: Object as PropType<FillInTheBlanksDefinition>,
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
    submittedAnswerIsCorrect(blank: Blank): boolean {
      const submittedAnswer = this.submittedAnswers?.[blank.uuid];
      if (!submittedAnswer) {
        return false;
      } else {
        return this.isAnswerCorrect(submittedAnswer, blank.solution);
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
    classForInput(blank: Blank) {
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
    onInputBlurOrEnter() {
      this.userWantsToSeeSolutions = false;
      if (this.task.autoCorrect) {
        this.onClickCheck();
      }
    },
  },
  computed: {
    splitTemplate(): string[] {
      // Returns an array where the even indexes are the static text portions,
      // and the odd indexes are the blanks.
      return this.task.template.split(/{([^{}]*)}/);
    },
    parsedTemplate(): FillInTheBlanksElement[] {
      return this.splitTemplate.map((value, index) => {
        if (index % 2 === 0) {
          return { type: 'staticText', text: value, uuid: uuidv4() };
        } else {
          return { type: 'blank', solution: value, uuid: uuidv4() };
        }
      });
    },
    correctAnswers(): number {
      return this.blanks.filter((blank) => this.submittedAnswerIsCorrect(blank))
        .length;
    },
    blanks(): Blank[] {
      return this.parsedTemplate.filter(
        (word) => word.type === 'blank'
      ) as Blank[];
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
    showSolutions(): boolean {
      return this.userWantsToSeeSolutions && this.allBlanksAreFilled;
    },
    showFillInAllTheBlanksMessage(): boolean {
      return (
        this.userWantsToSeeSolutions &&
        !this.allBlanksAreFilled &&
        !this.inputHasChanged
      );
    },
    showResults(): boolean {
      return (
        !(this.submittedAnswers === null) &&
        !this.showFillInAllTheBlanksMessage &&
        (!this.task.autoCorrect || this.allBlanksAreFilled)
      );
    },
    blanksFilled(): number {
      return Object.keys(this.submittedAnswers ?? {}).length;
    },
    allBlanksAreFilled(): boolean {
      return this.blanksFilled == this.blanks.length;
    },
    allAnswersAreCorrect(): boolean {
      return (
        this.blanks.filter((blank) => this.submittedAnswerIsCorrect(blank))
          .length == this.blanks.length
      );
    },
    inputHasChanged(): boolean {
      return !isEqual(this.submittedAnswers, this.userInputs);
    },
  },
});
</script>

<style scoped>
.h5pModule {
  border: 2px solid #eee;
  padding: 0.5em 0.5em 0.5em 0.5em;
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
  font-size: 1em;
  line-height: 1.2;
  margin: 1em 0.5em 1em;
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

.h5pBlank {
  font-family: sans-serif;
  font-size: 1em;
  border-radius: 0.25em;
  border: 1px solid #a0a0a0;
  /* top, right, bottom, left */
  padding: 0.1875em 1em 0.1875em 0.5em;
  margin-bottom: 0.2em;
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
  width: 6em;
}

.h5pBlank.autocorrect:focus {
  /*  Irgendwas damit es nicht rot oder grün gehighlightet wird, während noch drin getippt wird */
}

.h5pCorrectAnswer {
  color: #255c41;
  font-weight: bold;
  border: 1px #255c41 dashed;
  background-color: #d4f6e6;
  padding: 0.15em;
  border-radius: 0.25em;
  margin-left: 0.5em;
}

.h5pAnswerFeedback {
  font-family: Sans-Serif;
  font-weight: 700;
  color: #1a73d9;
  font-size: 1em;
  display: inline-block;
}
</style>
