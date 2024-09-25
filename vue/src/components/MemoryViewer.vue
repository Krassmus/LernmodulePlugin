<template>
  <div class="h5p-module">
    <div
      class="h5pMemoryGame"
      :style="{
        gridTemplateColumns: gridTemplateColumns,
        gridTemplateRows: gridTemplateRows,
      }"
    >
      <MemoryCardComponent
        v-for="card in this.cards"
        :key="card.uuid"
        :card="card"
        :flipside="task.flipside"
        @click="onClickCard(card)"
      />
    </div>

    <div style="margin-top: 1em">
      <span
        class="memory-info-header"
        v-text="$gettext('Umgedrehte Karten:')"
      />
      <span v-text="' ' + this.amountOfFlips.toString()" />
    </div>

    <div>
      <span class="memory-info-header" v-text="$gettext('Zeit:')" />
      <span
        v-text="
          ' ' +
          $gettext('%{ time } Sekunden', {
            time: this.timer.toString(),
          })
        "
      />
    </div>

    <div>
      <span
        class="memory-info-header"
        v-text="$gettext('Aufgedeckte Paare:')"
      />
      <span
        v-text="
          ' ' +
          $gettext('%{ amountOfPairsSolved } von %{ totalAmountOfPairs }', {
            amountOfPairsSolved: this.amountOfPairsSolved.toString(),
            totalAmountOfPairs: this.totalAmountOfPairs.toString(),
          })
        "
      />
    </div>

    <div v-if="showResults" class="result-message">
      {{ this.resultMessage }}
    </div>

    <div class="h5p-button-panel">
      <button
        v-if="showRetryButton"
        v-text="this.task.strings.retryButton"
        @click="onClickTryAgain"
        type="button"
        class="h5p-button"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MemoryTask, Image } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import MemoryCardComponent from '@/components/MemoryCard.vue';
import { v4 } from 'uuid';

export interface ViewerMemoryCard extends Image {
  flipped: boolean;
  solved: boolean;
  matchingCardId: string | undefined;
}

export default defineComponent({
  name: 'MemoryViewer',
  components: { MemoryCardComponent },
  emits: ['updateAttempt'],
  props: {
    task: {
      type: Object as PropType<MemoryTask>,
      required: true,
    },
  },
  data() {
    return {
      cards: [] as ViewerMemoryCard[],
      firstFlippedCardId: undefined as string | undefined,
      amountOfFlips: 0 as number,

      timer: 0, // Track elapsed time in seconds
      timerStarted: false, // Flag to check if timer has started
      timerInterval: null as number | null, // Store interval ID to control timer
    };
  },
  methods: {
    $gettext,

    onClickCard(card: ViewerMemoryCard): void {
      // do nothing if card is already open
      if (card.flipped) return;

      card.flipped = true;
      console.log('Flipped', card.altText);

      // Start timer
      if (!this.timerStarted) {
        this.startTimer();
      }

      this.amountOfFlips++;

      if (!this.firstFlippedCardId) {
        // This is the first of two cards to be flipped around
        // Reset all previous unsuccessful cardflips except the card we just flipped
        this.resetAllUnsolvedCardsExcept(card.uuid);

        this.firstFlippedCardId = card.uuid;
      } else {
        // We flipped the second card around, check if we got a pair
        if (card.matchingCardId === this.firstFlippedCardId) {
          // We found a pair!
          // Set both cards' solved attribute to true
          card.solved = true;
          const firstCard = this.getCardById(this.firstFlippedCardId);
          firstCard.solved = true;
          console.log(
            'Found a pair of',
            card.altText,
            'and',
            firstCard.altText
          );
        }

        this.firstFlippedCardId = undefined;
      }
    },

    startTimer() {
      this.timerStarted = true;

      // Start a timer that increments every second
      this.timerInterval = setInterval(() => {
        this.timer++;
      }, 1000) as unknown as number;
    },

    stopTimer() {
      // Stop the timer and clear the interval
      if (this.timerInterval !== null) {
        clearInterval(this.timerInterval);
        this.timerInterval = null; // Reset after clearing
      }

      this.timerStarted = false;
    },

    resetAllUnsolvedCardsExcept(exceptionId: string): void {
      for (const card of this.cards) {
        if (!card.solved && card.uuid !== exceptionId) {
          card.flipped = false;
        }
      }
    },

    onClickTryAgain(): void {
      this.amountOfFlips = 0;
      this.timer = 0;
      this.firstFlippedCardId = undefined;
      this.resetCards();
      this.shuffleCards();
    },

    resetCards(): void {
      // Set all cards' flipped and solved attribute to false
      for (const card of this.cards) {
        card.flipped = false;
        card.solved = false;
      }
    },

    shuffleCards(): void {
      // Shuffle the cards
      // https://stackoverflow.com/a/46545530
      this.cards = this.cards
        .map((card) => ({ card, sort: Math.random() }))
        .sort((card1, card2) => card1.sort - card2.sort)
        .map(({ card }) => card);
    },

    getCardById(cardId: string): ViewerMemoryCard {
      const card = this.cards.find((card) => card.uuid === cardId);
      if (!card) {
        throw new Error('No card found with the given ID: ' + cardId);
      }
      return card;
    },
  },

  computed: {
    totalAmountOfPairs(): number {
      return this.task.cards.length;
    },

    amountOfPairsSolved(): number {
      let amountOfCardsSolved = 0;
      for (const card of this.cards) {
        if (card.solved) amountOfCardsSolved++;
      }
      return amountOfCardsSolved / 2;
    },

    gameIsOver(): boolean {
      return this.amountOfPairsSolved === this.totalAmountOfPairs;
    },

    showResults(): boolean {
      return this.gameIsOver;
    },

    showRetryButton(): boolean {
      return this.gameIsOver;
    },

    resultMessage(): string {
      return this.task.strings.resultMessage;
    },

    gridTemplateColumns(): string {
      if (this.task.squareLayout) {
        const columns = Math.ceil(Math.sqrt(this.cards.length));
        return `repeat(${columns}, 1fr)`;
      } else {
        return 'repeat(auto-fill, minmax(12em, 1fr))'; // Responsive behavior
      }
    },

    gridTemplateRows(): string {
      if (this.task.squareLayout) {
        const rows = Math.ceil(
          this.cards.length / Math.ceil(Math.sqrt(this.cards.length))
        );
        return `repeat(${rows}, 1fr)`;
      } else {
        return 'auto'; // For responsive layout, let the rows be created automatically
      }
    },
  },
  watch: {
    task: {
      handler() {
        console.log('watcher for this.task');
        this.firstFlippedCardId = undefined;
        this.amountOfFlips = 0;

        // Make two copies of each card in the task. Add the flipped, solved and matchingCardId attributes
        this.cards = this.task.cards.flatMap((card) => {
          if (!card.second) {
            // The two copies of each card are linked to each other through the matchingCardId attribute
            const duplicateCardId = v4();
            return [
              // The original card with the set matchingCardId attribute
              {
                ...card.first,
                flipped: false,
                solved: false,
                uuid: card.first.uuid,
                matchingCardId: duplicateCardId,
              },
              // The duplicate card
              {
                ...card.first,
                flipped: false,
                solved: false,
                uuid: duplicateCardId,
                matchingCardId: card.first.uuid,
              },
            ];
          } else {
            return [
              // The original card with the set matchingCardId attribute
              {
                ...card.first,
                flipped: false,
                solved: false,
                matchingCardId: card.second.uuid,
              },
              // The second card
              {
                ...card.second,
                flipped: false,
                solved: false,
                matchingCardId: card.first.uuid,
              },
            ];
          }
        });

        // Shuffle the cards
        this.shuffleCards();
      },
      immediate: true, // Ensure that the watcher is also called immediately when the component is first mounted
    },
    gameIsOver(newValue) {
      if (newValue) {
        this.stopTimer();
      }
    },
  },
});
</script>

<style scoped>
.h5pMemoryGame {
  display: grid;
  grid-gap: 1em;
}

.memory-info-header {
  font-weight: 700;
}

.result-message {
  font-size: 18px;
  font-weight: bold;
  color: #1a73d9;
  margin: 8px 0;
}
</style>
