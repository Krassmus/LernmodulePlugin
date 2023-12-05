<template>
  <div class="h5pFeedbackContainer">
    <div class="h5pFeedbackContainerTop">
      <label for="score" class="h5pFeedbackText">
        {{ resultMessage }}
      </label>
      <meter id="score" min="0" :max="maxPoints" :value="achievedPoints" />
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
    feedbackSortedByScore(): Feedback[] {
      if (this.feedback) {
        return this.feedback
          .map((value) => value)
          .sort((a, b) => b.percentage - a.percentage);
      }

      return {} as Feedback[];
    },

    feedbackMessage(): string | undefined {
      if (this.achievedPoints && this.maxPoints && this.feedbackSortedByScore) {
        const percentageCorrect = round(
          (this.achievedPoints / this.maxPoints) * 100
        );

        for (const feedback of this.feedbackSortedByScore) {
          if (percentageCorrect >= feedback.percentage) {
            return feedback.message;
          }
        }
      }

      return undefined;
    },
  },
});
</script>

<style></style>
