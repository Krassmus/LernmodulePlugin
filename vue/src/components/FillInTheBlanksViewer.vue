<template>
  <div>
    <template v-for="(str, i) in splitTemplate" :key="i">
      <span v-if="i % 2 === 0">
        {{ str }}
      </span>
      <input
        v-else
        type="text"
        v-model="userInputs[i]"
        :readonly="
          submittedAnswers?.[Math.floor(i / 2)] === answers[Math.floor(i / 2)]
        "
        :class="classForInputElement(Math.floor(i / 2))"
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

  <!--    Split template:-->
  <!--    <pre>{{ splitTemplate }}</pre>-->
  <!--  </div>-->

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
    onClickSubmit() {
      this.submittedAnswers = this.userInputs.filter((wert, i) => i % 2 === 1);
    },
    classForInputElement(index: number) {
      if (!this.submittedAnswers) {
        return 'h5pBlank';
      }

      if (this.task.caseSensitive) {
        return this.submittedAnswers[index] === this.answers[index]
          ? 'h5pBlank correct'
          : 'h5pBlank incorrect';
      } else {
        if (this.submittedAnswers[index] != null) {
          return this.submittedAnswers[index].toLowerCase() ===
            this.answers[index].toLowerCase()
            ? 'h5pBlank correct'
            : 'h5pBlank incorrect';
        } else {
          return 'h5pBlank incorrect';
        }
      }
    },
  },
  computed: {
    splitTemplate(): string[] {
      // Returns an array where the even indexes are the static text portions,
      // and the odd indexes are the blanks.
      return this.task.template.split(/{([^{}]*)}/);
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
