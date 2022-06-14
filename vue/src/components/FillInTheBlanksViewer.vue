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

  <button @click="onClickSubmit">Submit</button>

  userInputs:
  <pre>{{ userInputs }}</pre>
  submittedAnswers:
  <pre>{{ submittedAnswers }}</pre>
  answers:
  <pre>{{ answers }}</pre>
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
      userInputs: [] as string[],
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
        return '';
      }
      return this.submittedAnswers[index] === this.answers[index]
        ? 'correct'
        : 'incorrect';
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
</style>
