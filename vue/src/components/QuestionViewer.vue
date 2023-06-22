<template>
  <div class="h5pModule">
    <div class="h5pQuestion" v-html="this.task.question" />
    <template v-if="task.canAnswerMultiple">
      <div
        v-for="(answer, i) in answers"
        :key="i"
        :class="classForAnswer(answer)"
      >
        <label class="answerLabel">
          <input
            type="checkbox"
            :value="answer.text"
            v-model="selectedAnswers[answer.text]"
            :disabled="isSubmitted"
            class="answerCheckbox"
          />
          {{ answer.text }}
        </label>
        <div
          v-if="answer.strings.hint && !isSubmitted"
          class="tooltip tooltip-icon h5pTooltip"
          :data-tooltip="answer.strings.hint"
        />
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
      </div>
    </template>

    <template v-else>
      <div
        v-for="(answer, i) in answers"
        :key="i"
        :class="classForAnswer(answer)"
      >
        <label>
          <input
            type="radio"
            :value="answer"
            v-model="selectedAnswer"
            :disabled="isSubmitted"
          />
          {{ answer.text }}
        </label>
        <div
          v-if="answer.strings.hint && !isSubmitted"
          class="tooltip tooltip-icon h5pTooltip"
          :data-tooltip="answer.strings.hint"
        />
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
      </div>
    </template>

    <button
      v-if="!isSubmitted"
      @click="onClickCheck"
      class="h5pButton"
      style="margin-top: 1em"
    >
      {{ this.task.strings.checkButton }}
    </button>

    <div v-if="isSubmitted">
      <label for="score" style="display: block; padding-top: 1em"
        >{{ points }} {{ $gettext('Punkte') }}</label
      >
      <meter id="score" min="0" :max="maxPoints" :value="points" />
      <img
        v-if="reachedMaxPoints"
        :src="urlForIcon('star')"
        width="36"
        height="36"
      />
    </div>

    <button
      v-if="this.task.retryAllowed && isSubmitted"
      @click="onClickTryAgain"
      class="h5pButton"
    >
      {{ this.task.strings.retryButton }}
    </button>

    <button
      v-if="showSolutionsButton"
      @click="onClickShowSolution"
      class="h5pButton"
    >
      {{ this.task.strings.solutionsButton }}
    </button>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { QuestionAnswer, QuestionTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'QuestionViewer',
  props: {
    task: {
      type: Object as PropType<QuestionTask>,
      required: true,
    },
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
            return 'answer';
          }
        } else {
          if (this.selectedAnswer === answer) {
            if (answer.correct) {
              return 'correctAnswer';
            } else {
              return 'incorrectAnswer';
            }
          } else {
            return 'answer';
          }
        }
      } else {
        return 'answer';
      }
    },
    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },
    shakeit() {
      this.shaking = true;
      setTimeout(() => {
        this.shaking = false;
      }, 1500);
    },
  },
  data() {
    return {
      selectedAnswers: {} as Record<string, boolean>,
      selectedAnswer: this.task.answers[0],
      isSubmitted: false,
      showSolutions: false,
      showFeedback: false,
      shaking: false,
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
        let randomizedAnswers = this.task.answers
          .map((value) => ({ value, sort: Math.random() }))
          .sort((a, b) => a.sort - b.sort)
          .map(({ value }) => value);
        return randomizedAnswers;
      } else {
        return this.task.answers;
      }
    },
    showSolutionsButton(): boolean {
      return this.task.showSolutionsAllowed && this.isSubmitted;
    },
    reachedMaxPoints(): boolean {
      if (this.points === this.maxPoints) {
        return true;
      } else {
        return false;
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

.answerLabel {
  cursor: pointer;
}

.answerCheckbox {
  cursor: pointer;
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
