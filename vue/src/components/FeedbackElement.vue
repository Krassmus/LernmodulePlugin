<template>
  <div class="feedback-container">
    <div
      v-if="feedbackMessage"
      class="feedback-text"
      v-text="feedbackMessage"
    />

    <div v-if="maxPoints" class="meter-and-star-container">
      <div class="custom-meter">
        <div class="custom-meter-bar" ref="meterbar" />
      </div>

      <img
        v-if="achievedMaxPoints"
        class="star-symbol"
        :class="{ 'star-show': starVisible }"
        src="../assets/star.svg"
        width="38"
        height="38"
        :alt="
          $gettext(
            'Ein goldener Stern, der den Abschluss der Aufgabe mit perfekter Leistung anzeigt'
          )
        "
      />

      <div
        class="result-text"
        :class="{ 'result-text-offset': achievedMaxPoints }"
        v-text="resultMessage"
      />
    </div>

    <div v-else>{{ achievedPoints }}</div>
  </div>
</template>

<script lang="ts">
import { defineComponent, nextTick, PropType } from 'vue';
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
    return {
      starVisible: false,
    };
  },
  mounted() {
    this.startTransition();
  },
  methods: {
    async startTransition() {
      // Wait for the next DOM update cycle
      await nextTick();

      // Access the ref safely and apply the style
      const meterBar = this.$refs.meterbar as HTMLElement;
      if (meterBar) {
        // Trigger a reflow to ensure the width change is animated
        meterBar.offsetHeight; // Trigger reflow

        meterBar.style.width = `${this.percentageCorrect}%`;

        // Show the star after the meter fills up
        setTimeout(() => {
          this.starVisible = true;
        }, 500); // Adjust this delay to match the duration of the meter fill
      }
    },
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

<style scoped>
.feedback-text {
  font-size: 1.25em;
  font-weight: bold;
  color: #1a73d9;
}

.result-text {
  flex-grow: 1;
  min-width: 0;
  padding-left: 8px;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
  font-size: 1em;
  font-weight: bold;
  color: #000;
}

.result-text-offset {
  padding-left: 24px;
}

.feedback-container {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.custom-meter {
  flex-shrink: 0;
  background: #e0e0e0;
  border: 1px solid #ccc;
  border-radius: 5px;
  width: 140px;
  height: 16px;
  overflow: hidden; /* Ensure the bar doesn’t overflow */
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}

.custom-meter-bar {
  height: 100%;
  background: linear-gradient(to right, #34a86e, #2b8c5c);
  width: 0; /* Initial width is 0% */
  transition: width 0.5s ease; /* Smooth fade-in */
}

.meter-and-star-container {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  justify-content: flex-start;
  align-items: center;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 2em;
  padding: 6px 12px 6px 12px;
  max-width: 480px;
  position: relative;
}

.star-symbol {
  position: absolute;
  left: 134px;
  top: 1px;
  opacity: 0;
  transition: opacity 0.5s ease; /* Smooth fade-in */
}

@keyframes pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
  }
}

.star-show {
  opacity: 1; /* Show the star */
  animation: pulse 0.5s ease-in-out 0s 1; /* Apply the pulse animation */
}
</style>
