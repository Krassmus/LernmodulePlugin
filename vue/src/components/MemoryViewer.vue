<template>
  <span>Cardflips: {{ this.amountOfFlips }}</span>

  <span>
    Solved: {{ this.amountOfPairsSolved }} of
    {{ this.totalAmountOfPairs }}
  </span>
  <div class="h5pMemoryGame">
    <MemoryCardComponent
      v-for="card in this.cards"
      :key="card.uuid"
      :card="card"
      @click="onClickCard(card)"
    ></MemoryCardComponent>
  </div>

  <div class="h5pFeedbackContainer">
    <div class="h5pFeedbackContainerTop">
      <label v-if="showResults && feedbackMessage" class="h5pFeedbackText">
        {{ this.feedbackMessage }}
      </label>
    </div>
    <div class="h5pFeedbackContainerCenter">
      <label v-if="showResults" class="h5pFeedbackText">
        {{ this.resultMessage }}
      </label>
    </div>
    <div class="h5pFeedbackContainerBottom">
      <button v-if="showRetryButton" @click="onClickTryAgain" class="h5pButton">
        {{ this.task.strings.retryButton }}
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MemoryTask, MemoryCard } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import MemoryCardComponent from '@/components/MemoryCard.vue';
import { v4 } from 'uuid';

export interface ViewerMemoryCard extends MemoryCard {
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
      showResults: false as boolean,
      feedbackMessage: undefined as string | undefined,
      resultMessage: undefined as string | undefined,
    };
  },
  methods: {
    $gettext,

    onClickCard(card: ViewerMemoryCard): void {
      // do nothing if card is already open
      if (card.flipped) return;

      card.flipped = true;
      console.log('Flipped', card.altText);

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
          console.log('Found a pair of', card.altText);
        }

        this.firstFlippedCardId = undefined;
      }
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

    showRetryButton(): boolean {
      return this.amountOfPairsSolved === this.totalAmountOfPairs;
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
          // The two copies of each card are linked to each other through the matchingCardId attribute
          const duplicateCardId = v4();
          return [
            // The original card with the set matchingCardId attribute
            {
              ...card,
              flipped: false,
              solved: false,
              uuid: card.uuid,
              matchingCardId: duplicateCardId,
            },
            // The duplicate card
            {
              ...card,
              flipped: false,
              solved: false,
              uuid: duplicateCardId,
              matchingCardId: card.uuid,
            },
          ];
        });

        // Shuffle the cards
        this.shuffleCards();
      },
      immediate: true, // Ensure that the watcher is also called immediately when the component is first mounted
    },
  },
});
</script>

<style scoped>
.h5pMemoryGame {
  display: grid;
  grid-gap: 1em;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  /*grid-template-rows: repeat(auto-fit, minmax(250px, 1fr));*/
}
</style>
