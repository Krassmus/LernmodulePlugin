<template>
  <div>
    <template v-for="element in parsedTemplate" :key="element.uuid">
      <span v-if="element.type === 'staticText'">
        {{ element.text }}
      </span>
      <input
        v-else-if="element.type === 'blank'"
        type="text"
        v-model="userInputs[element.uuid]"
        :readonly="submittedAnswerIsCorrect(element)"
        :class="classForInput(element)"
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
    Split template:
    <pre>{{ splitTemplate }}</pre>
    Parsed template:
    <pre>{{ parsedTemplate }}</pre>
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
import { v4 as uuidv4 } from 'uuid';

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
      debug: true,
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
    onClickSubmit() {
      // Save a copy of the user's inputs.
      this.submittedAnswers = { ...this.userInputs };
    },
    classForInput(blank: Blank) {
      if (!this.submittedAnswers) {
        return 'h5pBlank';
      }

      if (this.submittedAnswerIsCorrect(blank)) {
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
    parsedTemplate(): FillInTheBlanksElement[] {
      return this.splitTemplate.map((value, index) => {
        if (index % 2 === 0) {
          return { type: 'staticText', text: value, uuid: uuidv4() };
        } else {
          return { type: 'blank', solution: value, uuid: uuidv4() };
        }
      });
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
