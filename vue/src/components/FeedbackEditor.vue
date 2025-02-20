<template>
  <fieldset class="collapsable collapsed">
    <legend>{{ $gettext('Feedback') }}</legend>

    <label v-if="localResultMessage">
      {{
        $gettext('Ergebnismitteilung (mögliche Variablen :correct und :total):')
      }}
      <input
        type="text"
        v-model="localResultMessage"
        @input="emitUpdatedResultMessage"
      />
    </label>

    <label for="feedback-container"
      >{{
        $gettext('Benutzerdefiniertes Feedback für beliebige Punktebereiche:')
      }}
    </label>
    <div id="feedback-container" class="feedback-container">
      <div class="feedback-percentages-child">
        <label>
          {{ $gettext('Ab Prozent') }}
        </label>
        <template v-for="(feedback, i) in feedbackList" :key="i">
          <input
            type="number"
            min="0"
            max="100"
            v-model="feedbackList[i].percentage"
            @input="emitUpdatedFeedback"
          />
        </template>
      </div>

      <div class="feedback-messages-child">
        <label>
          {{ $gettext('Mitteilung') }}
        </label>
        <div
          class="feedback-messages-child-subdivision"
          v-for="(feedback, i) in feedbackList"
          :key="i"
        >
          <input
            type="text"
            v-model="feedbackList[i].message"
            @input="emitUpdatedFeedback"
          />
          <button
            type="button"
            :title="titleForDeleteButtonForFeedback(feedback)"
            class="remove-feedback-button"
            @click="removeFeedback(feedback)"
          >
            <img
              :src="urlForIcon('trash')"
              :alt="
                $gettext(
                  'Trash can icon in a button used to delete a feedback interval'
                )
              "
              width="16"
              height="16"
            />
          </button>
        </div>
      </div>
    </div>

    <button type="button" class="button" @click="addFeedback">
      {{ $gettext('Neuer Bereich') }}
    </button>
  </fieldset>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { $gettext } from '@/language/gettext';
import { Feedback } from '@/models/TaskDefinition';
import { cloneDeep } from 'lodash';

export default defineComponent({
  name: 'FeedbackEditor',

  props: {
    feedback: {
      type: Array as PropType<Feedback[]>,
      required: true,
    },
    resultMessage: {
      type: String,
      required: false,
    },
  },
  emits: ['update:feedback', 'update:result-message'],
  data() {
    return {
      feedbackList: cloneDeep(this.feedback), // Local copy of the feedback to edit
      localResultMessage: this.resultMessage, // Local copy of the result message
    };
  },
  watch: {
    // Synchronize state props -> local copies.
    feedback: {
      immediate: true,
      handler: function (): void {
        this.feedbackList = cloneDeep(this.feedback);
      },
    },
    resultMessage: {
      immediate: true,
      handler: function (): void {
        this.localResultMessage = this.resultMessage;
      },
    },
  },
  methods: {
    $gettext,

    titleForDeleteButtonForFeedback(feedback: Feedback): string {
      return this.$gettext(
        'Entferne den Feedback-Bereich, der ab %{ percentage }% anfängt.',
        { percentage: feedback.percentage.toString() }
      );
    },

    addFeedback() {
      const newFeedback = {
        percentage: 25, // Default value, you can adjust it
        message: 'Feedback',
      };
      this.feedbackList.push(newFeedback);
      this.emitUpdatedFeedback();
    },

    removeFeedback(feedbackToRemove: Feedback) {
      this.feedbackList = this.feedbackList.filter(
        (feedback) => feedback !== feedbackToRemove
      );
      this.emitUpdatedFeedback();
    },

    emitUpdatedFeedback() {
      this.$emit('update:feedback', [...this.feedbackList]);
    },

    emitUpdatedResultMessage() {
      this.$emit('update:result-message', this.localResultMessage);
    },

    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },
  },
});
</script>

<style scoped>
.feedback-container {
  display: flex;
  gap: 0.5em;
  justify-content: flex-start;
  align-items: center;
  max-width: 48em;
}

.feedback-percentages-child {
  flex: 0 100px;
  display: flex;
  flex-direction: column;
}

.feedback-messages-child {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.feedback-messages-child-subdivision {
  display: flex;
  align-items: center;
  gap: 0.5em;
}

.remove-feedback-button {
  display: flex;
  align-items: center;
  height: 28px;
}
</style>
