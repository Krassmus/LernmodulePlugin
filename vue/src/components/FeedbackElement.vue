<template>
  <div class="feedback-container" v-disable-drag>
    <div class="meter-container">
      <div class="meter-background">
        <div class="meter-bar" ref="meterBar" />
        <img
          v-if="starVisible"
          class="star-symbol"
          src="../assets/star.png"
          width="38"
          height="38"
          :alt="
            $gettext(
              'Ein goldener Stern, der den Abschluss der Aufgabe mit perfekter Leistung anzeigt'
            )
          "
        />
      </div>
      <div v-if="resultMessage" class="result-text" v-text="resultMessage" />
    </div>
    <div
      v-if="feedbackMessage"
      class="feedback-text"
      v-text="feedbackMessage"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, nextTick, PropType } from 'vue';
import { Feedback } from '@/models/common';
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
    this.adjustBarWidth();
  },
  watch: {
    achievedPoints: {
      immediate: true,
      handler: function (): void {
        this.adjustBarWidth();
        if (this.achievedMaxPoints) {
          // Show the star after the meter filled up
          setTimeout(() => {
            this.starVisible = true;
          }, 350);
        } else {
          this.starVisible = false;
        }
      },
    },
  },
  methods: {
    async adjustBarWidth() {
      // Wait for the next DOM update cycle
      await nextTick();

      const meterBar = this.$refs.meterBar as HTMLElement;
      if (meterBar) {
        // Trigger a reflow to ensure the width change is animated
        meterBar.offsetHeight;

        meterBar.style.width = `${this.percentageCorrect}%`;
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
.feedback-container {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 0.75em;
}

.meter-container {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  justify-content: flex-start;
  align-items: center;
  gap: 1.5em;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 2em;
  padding: 6px 12px 6px 12px;
}

.meter-background {
  flex-shrink: 0;
  background: #e0e0e0;
  border: 1px solid #ccc;
  border-radius: 5px;
  width: 140px;
  height: 16px;
  margin-top: 0.5em;
  margin-bottom: 0.5em;
  position: relative;
}

.meter-bar {
  height: 100%;
  background: linear-gradient(to right, #34a86e, #2b8c5c);
  width: 0; /* Initial width is 0% */
  transition: width 0.5s ease; /* Smooth fade-in */
}

.star-symbol {
  position: absolute;
  right: -23px;
  top: -14px;
  animation: pulse 0.5s ease-in-out 0s 1;
}

@keyframes pulse {
  0% {
    transform: scale(0.85);
  }
  50% {
    transform: scale(1.15);
  }
  100% {
    transform: scale(1);
  }
}

.result-text {
  min-width: 0;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
  font-size: 1em;
  font-weight: bold;
  color: #000;
}

.feedback-text {
  font-size: 1.25em;
  font-weight: bold;
  color: #1a73d9;
}
</style>
