<template>
  <div>
    <template v-for="(staticString, i) in staticStrings" :key="i">
      <span>
        {{ staticString }}
      </span>
      <input
        v-if="answers[i]"
        type="text"
        v-model="userInputs[i]"
        :readonly="submittedAnswerIsCorrect(i)"
        :class="classForInputElement(i)"
      />
    </template>
  </div>

  <div>
    <button @click="onClickSubmit" class="h5pButton">Check</button>
  </div>

  <div>
    userInputs:
    <pre>{{ userInputs }}</pre>
    submittedAnswers:
    <pre>{{ submittedAnswers }}</pre>
    answers:
    <pre>{{ answers }}</pre>
    Split template:
    <pre>{{ splitTemplate }}</pre>
  </div>
  <!--  <div v-if="false">-->
  <!--    <span-->
  <!--      v-if="isInputCorrect"-->
  <!--      :class="isInputCorrect ? 'resultCorrect' : 'resultIncorrect'"-->
  <!--    >-->
  <!--      Alle Lücken sind richtig ausgefüllt.-->
  <!--    </span>-->

  <!--    <span v-else :class="isInputCorrect ? 'resultCorrect' : 'resultIncorrect'">-->
  <!--      Die Lücken sind nicht richtig ausgefüllt.-->
  <!--    </span>-->
  <!--  </div>-->

  <!--  <div v-if="debug">-->
  <!--    <pre>{{ userInputs }}</pre>-->

  <!--  <input type="text" v-model="userInput" />-->
  <!--  <div :class="isInputCorrect ? 'correct' : 'incorrect'">-->
  <!--    User input: {{ userInput }}-->
  <!--  </div>-->
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FillInTheBlanksDefinition } from '@/models/TaskDefinition';

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
      userInputs: [],
      debug: true,
      submittedAnswers: null as string[] | null,
    };
  },
  methods: {
    submittedAnswerIsCorrect(index: number): boolean {
      const submittedAnswer = this.submittedAnswers?.[index];
      const solution = this.answers[index];
      if (!submittedAnswer) {
        return false;
      } else {
        return this.isAnswerCorrect(submittedAnswer, solution);
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
    onClickSubmit() {
      // Save a copy of the user's inputs.
      // this.userInputs is a sparse array, because it is created using v-model.
      // Copying it with the spread operator fills in the holes.
      this.submittedAnswers = [...this.userInputs];
    },
    classForInputElement(index: number) {
      if (!this.submittedAnswers) {
        return 'h5pBlank';
      }

      if (this.submittedAnswerIsCorrect(index)) {
        return 'h5pBlank correct';
      } else {
        return 'h5pBlank incorrect';
      }
    },
  },
  computed: {
    splitTemplate(): string[] {
      // Returns an array where the even indexes are the static text portions,
      // and the odd indexes are the blanks.
      return this.task.template.split(/{([^{}]*)}/);
    },
    staticStrings(): string[] {
      return this.splitTemplate.filter((value, index) => index % 2 === 0);
    },
    answers(): string[] {
      return this.splitTemplate.filter((value, index) => index % 2 === 1);
    },
  },
});
</script>

<style scoped>
.correct {
  color: black;
  background-color: #66d02e;
}

.incorrect {
  color: black;
  background-color: #f7d0d0;
}

.resultCorrect {
  color: #66d02e;
  font-weight: bold;
}

.resultIncorrect {
  color: #ff0000;
  font-weight: bold;
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
  border-top-color: rgb(160, 160, 160);
  border-right-color: rgb(160, 160, 160);
  border-bottom-color: rgb(160, 160, 160);
  border-left-color: rgb(160, 160, 160);
  padding: 0.1875em 1em 0.1875em 0.5em;
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
  width: 6em;
}
</style>
