<template>
  <div class="h5pModule">
    <pre>
selectedAnswers: {{ selectedAnswers }}
selectedAnswer: {{ selectedAnswer }}
    </pre>
    <div class="h5pQuestion">
      {{ this.task.question }}
    </div>
    <template v-if="task.canAnswerMultiple">
      <div v-for="(answer, i) in task.answers" :key="i">
        <label>
          <input
            type="checkbox"
            :value="answer.text"
            v-model="selectedAnswers[answer.text]"
            :disabled="isSubmitted"
          />
          {{ answer.text }}
        </label>
      </div>
    </template>

    <template v-else>
      <div v-for="(answer, i) in task.answers" :key="i">
        <label>
          <input
            type="radio"
            :value="answer"
            v-model="selectedAnswer"
            :disabled="isSubmitted"
          />
          {{ answer.text }}
        </label>
      </div>
    </template>

    <button @click="onClickCheck" v-if="!isSubmitted" style="margin-top: 1em">
      {{ this.task.strings.checkButton }}
    </button>

    <div v-if="isSubmitted">
      <label for="score" style="display: block; padding-top: 1em"
        >{{ points }} {{ $gettext('Punkte') }}</label
      >
      <meter id="score" min="0" :max="maxPoints" :value="points" />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MultipleChoiceTaskDefinition } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'MultipleChoiceViewer',
  props: {
    task: {
      type: Object as PropType<MultipleChoiceTaskDefinition>,
      required: true,
    },
  },
  methods: {
    onClickCheck(): void {
      this.isSubmitted = true;
    },
  },
  data() {
    return {
      selectedAnswers: {} as Record<string, boolean>,
      selectedAnswer: this.task.answers[0],
      isSubmitted: false,
    };
  },
  computed: {
    maxPoints(): number {
      if (this.task.canAnswerMultiple) {
        let maxPoints = 0;
        this.task.answers.forEach((answer) => {
          if (answer.correct) {
            maxPoints++;
          }
        });
        return maxPoints;
      } else {
        return 1;
      }
    },
    points(): number {
      if (this.task.canAnswerMultiple) {
        let points = 0;

        this.task.answers.forEach((answer) => {
          if (this.selectedAnswers[answer.text] && answer.correct) {
            points++;
          }
        });

        return points;
      } else {
        return this.selectedAnswer.correct ? 1 : 0;
      }
    },
  },
});
</script>

<style scoped>
meter {
  width: 300px;
  height: 20px;
}

button {
  font-size: 1em;
  line-height: 1.2;
  margin: 0 0.5em 1em;
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
</style>
