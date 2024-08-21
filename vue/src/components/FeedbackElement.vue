<template>
  <div class="feedback-container">
    <div class="feedback-text">
      {{ resultMessage }}
    </div>
    <div v-if="maxPoints" class="feedback-meter-and-star">
      <div class="custom-meter">
        <div
          class="custom-meter-bar"
          ref="meterbar"
          :style="{ width: `${percentageCorrect}%` }"
        />
      </div>
      <img
        v-if="achievedMaxPoints"
        class="star-symbol"
        :src="urlForIcon('star')"
        width="36"
        height="36"
        :alt="$gettext('Ein Stern')"
      />
    </div>
    <div v-else>{{ achievedPoints }}</div>
    <div v-if="feedbackMessage" class="feedback-text">
      {{ feedbackMessage }}
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Feedback } from '@/models/TaskDefinition';
import { round } from 'lodash';

export default defineComponent({
  name: 'FeedbackElement',
  props: {
    achievedPoints: Number,
    maxPoints: Number,
    resultMessage: String,
    feedback: Object as PropType<Feedback[]>,
  },
  data() {
    return {};
  },
  mounted() {},
  methods: {
    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },
  },
  computed: {
    feedbackSortedByScore(): Feedback[] {
      if (this.feedback) {
        return this.feedback
          .map((value) => value)
          .sort((a, b) => b.percentage - a.percentage);
      }
      return [];
    },

    feedbackMessage(): string | undefined {
      if (
        typeof this.achievedPoints === 'undefined' ||
        typeof this.maxPoints === 'undefined'
      ) {
        return undefined;
      }

      const percentageCorrect = round(
        (this.achievedPoints / this.maxPoints) * 100
      );

      for (const feedback of this.feedbackSortedByScore) {
        if (percentageCorrect >= feedback.percentage) {
          return feedback.message;
        }
      }

      return undefined;
    },

    percentageCorrect(): number {
      if (this.achievedPoints && this.maxPoints)
        return round((this.achievedPoints / this.maxPoints) * 100);
      else return 0;
    },

    achievedMaxPoints(): boolean {
      return this.achievedPoints === this.maxPoints;
    },
  },
});
</script>

<style>
.feedback-text {
  font-size: 1em;
  color: #1a73d9;
  font-weight: 700;
}

.feedback-container {
  padding-top: 0.5em;
}

.feedback-meter-and-star {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  justify-content: flex-start;
  align-items: center;
}

.custom-meter {
  background: #e0e0e0;
  border: 1px solid #ccc;
  border-radius: 5px;
  width: 100%;
  height: 20px;
  overflow: hidden; /* Ensure the bar doesn’t overflow */
  max-width: 240px;
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}

.custom-meter-bar {
  height: 100%;
  background: #4caf50;
  width: 0; /* Initial width is 0% */
  transition: width 0.5s ease;
}

.star-symbol {
  padding-bottom: 0.25em;
  padding-left: 0.1em;
}
</style>
