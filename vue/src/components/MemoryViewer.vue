<template>
  <span>Cardflips: {{ this.amountOfFlips }}</span>
  <div class="h5pMemoryGame">
    <MemoryCardComponent
      v-for="card in this.cards"
      :key="card.uuid"
      :card="card"
      @click="onClickCard(card)"
    ></MemoryCardComponent>
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
        this.flipAllUnsolvedCardsExcept(card.uuid);

        this.firstFlippedCardId = card.uuid;
      } else {
        // We flipped the second card around, check if we got a pair
        if (card.matchingCardId === this.firstFlippedCardId) {
          // We found a pair!
          // Set both cards' solved attribute to true
          card.solved = true;
          this.solveCard(this.firstFlippedCardId);
          console.log('Found a pair of', card.altText);
        }

        this.firstFlippedCardId = undefined;
      }
    },

    flipAllUnsolvedCardsExcept(exceptionId: string): void {
      this.cards = this.cards.map((card) => ({
        ...card,
        flipped: card.solved || card.uuid === exceptionId ? true : false,
      }));
    },

    solveCard(cardToSolveId: string): void {
      this.cards = this.cards.map((card) => {
        return {
          ...card,
          solved: card.uuid === cardToSolveId ? true : card.solved,
        };
      });
    },
  },
  watch: {
    task: {
      handler() {
        console.log('watcher for this.task');
        // Make a copy of all of the cards in the task and add the flipped, solved and matchingCardId attributes
        this.cards = this.task.cards.map((card) => {
          // Retain the flipped and solved statuses of the existing cards
          const oldCard = this.cards.find(
            (existingCard) => existingCard.uuid === card.uuid
          );
          return {
            ...card,
            flipped: oldCard ? oldCard.flipped : false,
            solved: oldCard ? oldCard.solved : false,
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

        // Shuffle the cards
        // https://stackoverflow.com/a/46545530
        this.cards = duplicatedCards
          .map((value) => ({ value, sort: Math.random() }))
          .sort((a, b) => a.sort - b.sort)
          .map(({ value }) => value);
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
