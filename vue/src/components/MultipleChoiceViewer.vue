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

    <button @click="onClickCheck" v-if="!isSubmitted">
      {{ this.task.strings.checkButton }}
    </button>
    <div v-if="isSubmitted">{{ points }} {{ $gettext('Punkte') }}</div>
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
    points(): number {
      if (this.task.canAnswerMultiple) {
        throw new Error('Not implemented'); // TODO
      } else {
        return this.selectedAnswer.correct ? 1 : 0;
      }
    },
  },
});
</script>

<style scoped></style>
