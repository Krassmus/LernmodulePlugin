<template>
  <div class="stud5p-task">
    <div class="card-row" tabIndex="-1">
      <button
        type="button"
        v-for="(card, index) in cards"
        :aria-label="getAriaLabel(card, index)"
        :key="card.uuid"
        :ref="'card-' + index"
        class="card"
        :class="{
          dragged: cardInteractedWith === card,
          disabled: isShowingResults,
          correct: isShowingResults && isCardInCorrectPosition(card),
          incorrect: isShowingResults && !isCardInCorrectPosition(card),
        }"
        :draggable="!isShowingResults"
        @dragstart="(event) => startDragCard(card, event)"
        @drag="onDrag(card)"
        @dragend="endDragCard(card)"
        @dragover="onDragOver(card)"
        @click="onClickCard(card)"
        @keydown="(event) => onKeydown(card, event)"
      >
        <span class="image-wrapper">
          <MultimediaElement :element="card" />
        </span>

        <span class="image-description" @dragover.prevent @dragenter.prevent>
          {{ card.altText }}
        </span>
      </button>
    </div>

    <div class="feedback-and-button-container">
      <FeedbackElement
        v-if="isShowingResults && !isShowingSolutions"
        :achievedPoints="correctAnswers"
        :maxPoints="maxPoints"
        :resultMessage="resultMessage"
        :feedback="task.feedback"
      />

      <div class="button-panel">
        <button
          v-if="showCheckButton"
          v-text="task.strings.checkButton"
          @click="onClickCheckButton"
          type="button"
          class="stud5p-button"
        />

        <button
          v-if="showRetryButton"
          v-text="task.strings.retryButton"
          @click="onClickRetryButton"
          type="button"
          class="stud5p-button"
        />

        <button
          v-if="showResumeButton"
          v-text="task.strings.resumeButton"
          @click="onClickResumeButton"
          type="button"
          class="stud5p-button"
        />

        <button
          v-if="showSolutionsButton"
          v-text="task.strings.solutionsButton"
          @click="onClickSolutionsButton"
          type="button"
          class="stud5p-button"
        />
      </div>
    </div>

    <div
      aria-live="polite"
      aria-atomic="true"
      style="position: absolute; width: 1px; height: 1px; overflow: hidden"
    >
      {{ screenReaderAnnouncement }}
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  fileIdToUrl,
  SequencingTask,
  LernmoduleMultimediaElement,
} from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import FeedbackElement from '@/components/FeedbackElement.vue';
import MultimediaElement from '@/components/MultimediaElement.vue';

type Uuid = string;

export default defineComponent({
  name: 'SequencingViewer',

  components: {
    MultimediaElement,
    FeedbackElement,
  },

  props: {
    task: {
      type: Object as PropType<SequencingTask>,
      required: true,
    },
  },

  emits: ['updateAttempt'],

  data() {
    return {
      cards: [] as LernmoduleMultimediaElement[],
      cardInteractedWith: undefined as LernmoduleMultimediaElement | undefined,
      isShowingResults: false as boolean,
      isShowingSolutions: false as boolean,
      screenReaderAnnouncement: '' as string,
    };
  },

  methods: {
    fileIdToUrl,

    $gettext,

    startDragCard(card: LernmoduleMultimediaElement, event: DragEvent) {
      console.log('Starting to drag card', card.uuid);
      this.cardInteractedWith = card;
    },

    onDrag(card: LernmoduleMultimediaElement) {
      console.log('Dragging card', card.uuid);
    },

    endDragCard(card: LernmoduleMultimediaElement) {
      console.log('Ending to drag card', card.uuid);
      this.cardInteractedWith = undefined;
    },

    onDragOver(card: LernmoduleMultimediaElement) {
      if (this.cardInteractedWith && this.cardInteractedWith != card) {
        const fromIndex = this.cards.indexOf(this.cardInteractedWith);
        const toIndex = this.cards.indexOf(card);

        console.log(
          'Dragging card',
          this.cardInteractedWith?.uuid,
          '(',
          fromIndex,
          ') over target',
          card.uuid,
          '(',
          toIndex,
          ')'
        );

        this.moveInArray(fromIndex, toIndex);
      }
    },

    onClickCard(card: LernmoduleMultimediaElement) {
      if (this.isShowingSolutions || this.isShowingResults) return;

      if (this.cardInteractedWith) {
        const fromIndex = this.cards.indexOf(this.cardInteractedWith);
        const toIndex = this.cards.indexOf(card);
        this.moveInArray(fromIndex, toIndex);
        this.cardInteractedWith = undefined;
      } else {
        this.cardInteractedWith = card;
      }
    },

    onKeydown(card: LernmoduleMultimediaElement, event: KeyboardEvent) {
      if (this.isShowingResults || this.isShowingSolutions) return;

      const currentlySelectedIndex = this.cards.indexOf(card);
      const previouslySelectedIndex = this.cardInteractedWith
        ? this.cards.indexOf(this.cardInteractedWith)
        : -1;

      switch (event.key) {
        case 'Enter':
        case ' ':
          event.preventDefault();
          // If an card is already selected and it's different from the current one
          if (this.cardInteractedWith && this.cardInteractedWith !== card) {
            // Move the already selected card to the position of the currently selected card
            this.moveInArray(previouslySelectedIndex, currentlySelectedIndex);
            this.screenReaderAnnouncement = this.$gettext(
              'Moved %1 to position %2.'
            )
              .replace('%1', this.cardInteractedWith.uuid)
              .replace('%2', String(currentlySelectedIndex + 1)); // Convert number to string

            this.cardInteractedWith = undefined;
            this.moveFocus(currentlySelectedIndex);
          } else {
            // Select or deselect the card
            this.cardInteractedWith = this.cardInteractedWith
              ? undefined
              : card;
            this.screenReaderAnnouncement = this.cardInteractedWith
              ? `Selected ${card.uuid} on position ${
                  currentlySelectedIndex + 1
                } for moving.`
              : 'Selection cleared.';
          }
          break;

        case 'Escape':
          this.cardInteractedWith = undefined;
          this.screenReaderAnnouncement = 'Selection canceled.';
          break;
      }
    },

    moveFocus(cardIndex: number) {
      // Wait for the DOM to update before applying focus
      this.$nextTick(() => {
        const buttonRef = this.$refs[
          `card-${cardIndex}`
        ] as HTMLButtonElement[];
        if (buttonRef && buttonRef[0]) {
          // Check if the buttonRef exists and is not undefined
          buttonRef[0].focus(); // Set focus on the new button
        }
      });
    },

    getAriaLabel(card: LernmoduleMultimediaElement, index: number) {
      if (!this.isShowingResults) {
        return `${card.uuid} on position ${index + 1}`;
      } else {
        return `${card.uuid} on position ${index + 1} is ${
          this.isCardInCorrectPosition(card) ? 'correct' : 'not correct'
        }`;
      }
    },

    moveInArray(fromIndex: number, toIndex: number) {
      let element = this.cards[fromIndex];
      this.cards.splice(fromIndex, 1);
      this.cards.splice(toIndex, 0, element);
    },

    isCardInCorrectPosition(card: LernmoduleMultimediaElement): boolean {
      const index = this.cards.findIndex(
        (cardToCheck) => cardToCheck.uuid === card.uuid
      );
      return index !== -1 && this.task.cards[index].uuid === card.uuid;
    },

    onClickCheckButton() {
      this.isShowingResults = true;
      this.screenReaderAnnouncement = this.resultMessage;
    },

    onClickSolutionsButton() {
      this.isShowingSolutions = true;
      this.resetCards();
    },

    onClickResumeButton() {
      this.isShowingResults = false;
    },

    onClickRetryButton() {
      this.isShowingResults = false;
      this.isShowingSolutions = false;
      this.shuffleCards();
    },

    shuffleCards() {
      // Shuffle the cards
      // https://stackoverflow.com/a/46545530
      this.cards = this.cards
        .map((card) => ({ card: card, sort: Math.random() }))
        .sort((card1, card2) => card1.sort - card2.sort)
        .map(({ card }) => card);
    },

    resetCards() {
      this.cards = this.task.cards;
    },
  },

  computed: {
    showCheckButton(): boolean {
      return !this.isShowingResults;
    },

    showRetryButton(): boolean {
      return (
        this.task.retryAllowed &&
        this.isShowingResults &&
        !this.allAnswersAreCorrect
      );
    },

    showResumeButton(): boolean {
      return (
        this.task.resumeAllowed &&
        this.isShowingResults &&
        !this.isShowingSolutions &&
        !this.allAnswersAreCorrect
      );
    },

    showSolutionsButton(): boolean {
      return (
        this.task.showSolutionsAllowed &&
        this.isShowingResults &&
        !this.isShowingSolutions &&
        !this.allAnswersAreCorrect
      );
    },

    correctAnswers(): number {
      let correctAnswers = 0;
      for (let i = 0; i < this.task.cards.length; i++) {
        if (this.task.cards[i].uuid === this.cards[i].uuid) {
          correctAnswers++;
        }
      }
      return correctAnswers;
    },

    maxPoints(): number {
      return this.task.cards.length;
    },

    allAnswersAreCorrect(): boolean {
      return this.correctAnswers === this.maxPoints;
    },

    resultMessage(): string {
      let resultMessage = this.task.strings.resultMessage.replace(
        ':correct',
        this.correctAnswers.toString()
      );

      resultMessage = resultMessage.replace(
        ':total',
        this.maxPoints.toString()
      );

      return resultMessage;
    },
  },

  watch: {
    task: {
      handler() {
        console.log('watcher for this.task');
        this.cards = [...this.task.cards];
        this.shuffleCards();
      },
      immediate: true, // Ensure that the watcher is also called immediately when the component is first mounted
    },
  },
});
</script>

<style scoped>
.stud5p-sequencing {
}

.card-row {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  gap: 0.5em;
}

.card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;

  width: 11.5em;
  height: 13.5em;
  padding: 0.25em 0.25em 0 0.25em;

  border: 2px solid #dbe2e8;
  border-radius: 6px;

  background: #fff;

  cursor: grab;
  user-select: none;

  transition: background-color 0.12s ease, border 0.12s ease,
    box-shadow 0.12s ease;
}

.card:not(.disabled):focus,
.card:not(.disabled):active,
.card:not(.disabled):hover {
  border: 2px solid #7ba4d3;
  box-shadow: 0 0 10px 0 #406ef3;
}

.dragged {
  border: 2px solid #7ba4d3;
  box-shadow: 0 0 10px 0 #406ef3;
  background-color: #dcf6ff;
}

.correct {
  background-color: #9dd8bb;
  border-color: #9dd8bb;
}

.incorrect {
  background-color: #f7d0d0;
  border-color: #f7d0d0;
}

.disabled {
  cursor: default;
}

.image-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;

  flex: 1; /* Occupy remaining vertical space */
  width: 100%;

  overflow: hidden;
}

.image-description {
  width: 100%;
  height: 2em;

  padding-top: 0.4em;

  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-align: center;
}
</style>
