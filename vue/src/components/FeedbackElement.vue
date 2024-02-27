<template>
  <div class="h5pFeedbackContainer">
    <div class="h5pFeedbackContainerTop">
      <div class="h5pFeedbackText">
        {{ resultMessage }}
      </div>

      <meter
        v-if="maxPoints"
        id="score"
        min="0"
        :low="lowNumber"
        :high="highNumber"
        :optimum="maxPoints"
        :max="maxPoints"
        :value="achievedPoints"
      />
      <div v-else>{{ achievedPoints }}</div>
    </div>
    <div v-if="feedbackMessage" class="h5pFeedbackText">
      {{ feedbackMessage }}
    </div>
  </div>
</template>

<script lang="ts">
// need v-model to provide and get content -> <studip-wysiwyg v-model="content" />
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
  methods: {},
  computed: {
    lowNumber(): Number {
      if (this.maxPoints) return this.maxPoints / 3;
      return 0;
    },

    highNumber(): Number {
      if (this.maxPoints) return (this.maxPoints * 2) / 3;
      return 0;
    },

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
  },
});
</script>

<style>
.h5pFeedbackText {
  font-size: 1em;
  margin-left: 0.5em;
  color: #1a73d9;
  font-weight: 700;
}

.h5pFeedbackContainer {
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}

.h5pFeedbackContainerTop {
}

.h5pFeedbackContainerCenter {
  display: flex;
  justify-content: flex-start;
  align-items: center;
}

.h5pFeedbackContainerBottom {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  flex-wrap: wrap;
  gap: 1em;
}

meter {
  /* Reset the default appearance */
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;

  width: 15em;
  height: 30px;

  /* For Firefox */
  background: #fff;
  box-shadow: 0 2px 3px rgba(0, 0, 0, 0.2) inset;
  border-radius: 1.5em;

  display: -webkit-inline-flex;
  max-width: 100%;
  padding: 0.625em;
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-sizing: border-box;
}

meter::-webkit-meter-bar {
  background: none; /* Required to get rid of the default background property */
  background-color: whiteSmoke;
  box-shadow: 0 5px 5px -5px #333 inset;
}

meter::-webkit-meter-optimum-value {
  box-shadow: 0 5px 5px -5px #999 inset;
  background-image: linear-gradient(
    90deg,
    #8bcf69 5%,
    #e6d450 5%,
    #e6d450 15%,
    #f28f68 15%,
    #f28f68 55%,
    #cf82bf 55%,
    #cf82bf 95%,
    #719fd1 95%,
    #719fd1 100%
  );
  background-size: 100% 100%;
}
</style>
