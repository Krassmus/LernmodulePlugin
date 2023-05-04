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
import { MemoryTaskDefinition, MemoryCard } from '@/models/TaskDefinition';
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
      type: Object as PropType<MemoryTaskDefinition>,
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
      this.cards.forEach((card) => {
        if (card.solved) amountOfCardsSolved++;
      });
      return amountOfCardsSolved / 2;
    },

    showRetryButton(): boolean {
      if (this.amountOfPairsSolved === this.totalAmountOfPairs) {
        return true;
      } else {
        return false;
      }
    },
  },
  watch: {
    task: {
      handler() {
        console.log('watcher for this.task');
        // Make a copy of all of the cards in the task and add the flipped, solved and matchingCardId attributes
        this.cards = this.task.cards.map((card) => {
          return {
            ...card,
            flipped: false,
            solved: false,
            matchingCardId: undefined,
          };
        });

        // Make a duplicate of each card and link them with their original through the matchingCardId attribute
        let duplicatedCards = [] as ViewerMemoryCard[];

        this.cards.forEach((card) => {
          const partnerId = v4();

          // The duplicate card
          duplicatedCards.push({
            ...card,
            uuid: partnerId,
            matchingCardId: card.uuid,
          });

          // The original card with the set matchingCardId attribute
          duplicatedCards.push({
            ...card,
            matchingCardId: partnerId,
          });
        });

        this.cards = duplicatedCards;

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
