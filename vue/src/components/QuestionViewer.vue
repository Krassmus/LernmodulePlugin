<template>
  <div class="h5pModule">
    <div class="h5pQuestion" v-html="this.task.question" />
    <template v-if="task.canAnswerMultiple">
      <template v-for="(answer, i) in answers" :key="i">
        <label :class="classForAnswer(answer)">
          <input
            type="checkbox"
            :value="answer.text"
            v-model="selectedAnswers[answer.text]"
            :disabled="isSubmitted"
          />
          {{ answer.text }}
          <div
            v-if="answer.strings.hint && !isSubmitted"
            class="tooltip tooltip-icon h5pTooltip"
            :data-tooltip="answer.strings.hint"
          />
        </label>

        <template v-if="isSubmitted">
          <div
            v-if="
              answer.strings.feedbackSelected && selectedAnswers[answer.text]
            "
            class="answerFeedback"
          >
            {{ answer.strings.feedbackSelected }}
          </div>

          <div
            v-else-if="
              answer.strings.feedbackNotSelected &&
              !selectedAnswers[answer.text]
            "
            class="answerFeedback"
          >
            {{ answer.strings.feedbackNotSelected }}
          </div>
        </template>
      </template>
    </template>

    <template v-else>
      <template v-for="(answer, i) in answers" :key="i">
        <label :class="classForAnswer(answer)">
          <input
            type="radio"
            class="answerRadioButton"
            :value="answer"
            v-model="selectedAnswer"
            :disabled="isSubmitted"
          />
          {{ answer.text }}
          <div
            v-if="answer.strings.hint && !isSubmitted"
            class="tooltip tooltip-icon h5pTooltip"
            :data-tooltip="answer.strings.hint"
          />
        </label>

        <template v-if="isSubmitted">
          <div
            v-if="answer.strings.feedbackSelected && selectedAnswer == answer"
            class="answerFeedback"
          >
            {{ answer.strings.feedbackSelected }}
          </div>

          <div
            v-else-if="
              answer.strings.feedbackNotSelected && selectedAnswer !== answer
            "
            class="answerFeedback"
          >
            {{ answer.strings.feedbackNotSelected }}
          </div>
        </template>
      </template>
    </template>

    <FeedbackElement
      v-if="isSubmitted"
      :achieved-points="points"
      :max-points="maxPoints"
    />

    <div class="h5pButtonPanel">
      <button
        v-if="!isSubmitted"
        v-text="this.task.strings.checkButton"
        @click="onClickCheck"
        type="button"
        class="h5pButton"
      />

      <button
        v-if="this.task.retryAllowed && isSubmitted"
        v-text="this.task.strings.retryButton"
        @click="onClickTryAgain"
        type="button"
        class="h5pButton"
      />

      <button
        v-if="showSolutionsButton"
        v-text="this.task.strings.solutionsButton"
        @click="onClickShowSolution"
        type="button"
        class="h5pButton"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { QuestionAnswer, QuestionTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import FeedbackElement from '@/components/FeedbackElement.vue';

export default defineComponent({
  name: 'QuestionViewer',
  props: {
    task: {
      type: Object as PropType<QuestionTask>,
      required: true,
    },
  },
  components: { FeedbackElement },
  data() {
    return {
      selectedAnswers: {} as Record<string, boolean>,
      selectedAnswer: this.task.answers[0],
      isSubmitted: false,
      showSolutions: false,
    };
  },
  methods: {
    $gettext,
    onClickCheck(): void {
      this.isSubmitted = true;
    },
    onClickTryAgain(): void {
      this.isSubmitted = false;
      this.showSolutions = false;
      this.selectedAnswers = {};
    },
    onClickShowSolution(): void {
      this.showSolutions = true;
    },
    selectAnswer(answer: QuestionAnswer): void {
      if (this.isSubmitted) return;

      this.selectedAnswers[answer.text] = !this.selectedAnswers[answer.text];
    },
    classForAnswer(answer: QuestionAnswer): string {
      if (this.showSolutions) {
        if (answer.correct) return 'correctAnswer';
      }

      if (this.isSubmitted) {
        if (this.task.canAnswerMultiple) {
          if (this.selectedAnswers[answer.text]) {
            if (answer.correct) {
              return 'correctAnswer';
            } else {
              return 'incorrectAnswer';
            }
          } else {
            return 'submittedAnswer';
          }
        } else {
          if (this.selectedAnswer === answer) {
            if (answer.correct) {
              return 'correctAnswer';
            } else {
              return 'incorrectAnswer';
            }
          } else {
            return 'submittedAnswer';
          }
        }
      } else {
        return 'answer';
      }
    },
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
          if (this.selectedAnswers[answer.text]) {
            if (answer.correct) {
              points++;
            } else {
              points--;
            }
          }
        });

        // Cannot get less than 0 points
        if (points < 0) points = 0;

        return points;
      } else {
        return this.selectedAnswer.correct ? 1 : 0;
      }
    },
    answers(): QuestionAnswer[] {
      if (this.task.randomOrder) {
        // https://stackoverflow.com/a/46545530
        return this.task.answers
          .map((value) => ({ value, sort: Math.random() }))
          .sort((a, b) => a.sort - b.sort)
          .map(({ value }) => value);
      } else {
        return this.task.answers;
      }
    },
    showSolutionsButton(): boolean {
      return this.task.showSolutionsAllowed && this.isSubmitted;
    },
  },
});
</script>

<style scoped>
.answer {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  border-radius: 0.3em;
  margin: 0.5em 0;

  cursor: pointer;
  border: 0.1em solid transparent;
  background: rgba(230, 230, 230, 0.9);
}

.submittedAnswer {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  border-radius: 0.3em;
  margin: 0.5em 0;

  border: 0.1em solid transparent;
  background: rgba(230, 230, 230, 0.9);
}

.correctAnswer {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  border-radius: 0.3em;
  margin: 0.5em 0;

  border: 0.1em solid #b6e4ce;
  background: #b6e4ce;
  color: #255c41;
  box-shadow: 0 0.1em 0 #a2bdb0;
}

.incorrectAnswer {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  border-radius: 0.3em;
  margin: 0.5em 0;

  border: 0.1em solid #fbd7d8;
  background: #fbd7d8;
  color: #b71c1c;
  box-shadow: 0 0.1em 0 #deb8b8;
}

.answerRadioButton {
  margin-top: 0px;
}

.h5pTooltip {
  margin-left: 0.25em;
}

.answerFeedback {
  margin-left: 0.25em;
  border-left: 6px solid rgba(10, 10, 10, 0.1) !important;
  padding: 0.01em 16px;
}
</style>
