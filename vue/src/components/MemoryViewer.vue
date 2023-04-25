<template>
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
  matchingCardId: string | undefined;
  solved: boolean;
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
      selectedCardIndex: -1,
      cards: [] as ViewerMemoryCard[],
      // Instead of storing a 'flipped' attribute on the cards, one could also store all of their 'flipped' statuses
      // in a record, like this....
      flippedStates: {} as Record<string, boolean>,
      // Maybe it would make sense to store the ID fo the first card that a player turns over
      firstFlippedCardId: undefined as string | undefined,
    };
  },
  methods: {
    $gettext,
    onClickCard(card: ViewerMemoryCard): void {
      // do nothing if card is already open
      if (card.flipped) return;

      card.flipped = true;

      if (!this.firstFlippedCardId) {
        // This is the first flipped card
        this.flipAllUnsolvedCards(card.uuid);
        this.firstFlippedCardId = card.uuid;
        console.log('Flipped ', card.altText);
        return;
      } else {
        // We flipped the second card open
        if (card.matchingCardId === this.firstFlippedCardId) {
          // We found a pair!
          card.solved = true;
          this.solveCard(this.firstFlippedCardId);
          this.firstFlippedCardId = undefined;
          return;
        } else {
          // We did not find a pair :(
          this.firstFlippedCardId = undefined;
          return;
        }
      }
    },
    flipAllUnsolvedCards(exceptionUuid: string): void {
      this.cards = this.cards.map((card) => ({
        uuid: card.uuid,
        imageUrl: card.imageUrl,
        altText: card.altText,
        flipped: card.solved || card.uuid === exceptionUuid ? true : false,
        matchingCardId: card.matchingCardId,
        solved: card.solved,
      }));
    },
    solveCard(uuid: string): void {
      this.cards = this.cards.map((card) => {
        return {
          ...card,
          solved: card.uuid === uuid ? true : card.solved,
        };
      });
    },
  },
  watch: {
    task: {
      handler() {
        console.log('watcher for this.task');
        // Make a copy of all of the cards in the task and add the flipped attribute.
        this.cards = this.task.cards.map((card) => {
          // Retain the flipped statuses of the existing cards
          const oldCard = this.cards.find(
            (existingCard) => existingCard.uuid === card.uuid
          );
          return {
            ...card,
            flipped: oldCard ? oldCard.flipped : false,
            matchingCardId: undefined,
            solved: oldCard ? oldCard.solved : false,
          };
        });

        let duplicatedCards = [] as ViewerMemoryCard[];

        this.cards.forEach((card) => {
          const partnerCard = {
            uuid: v4(),
            imageUrl: card.imageUrl,
            altText: card.altText,
            flipped: card.flipped,
            matchingCardId: card.uuid,
            solved: card.solved,
          };

          duplicatedCards.push(partnerCard);

          duplicatedCards.push({
            uuid: card.uuid,
            imageUrl: card.imageUrl,
            altText: card.altText,
            flipped: card.flipped,
            matchingCardId: partnerCard.uuid,
            solved: card.solved,
          });
        });

        // https://stackoverflow.com/a/46545530
        let randomizedCards = duplicatedCards
          .map((value) => ({ value, sort: Math.random() }))
          .sort((a, b) => a.sort - b.sort)
          .map(({ value }) => value);

        this.cards = randomizedCards;
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
