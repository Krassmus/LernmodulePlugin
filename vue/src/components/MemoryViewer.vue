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

export interface ViewerMemoryCard extends MemoryCard {
  flipped: boolean;
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
      card.flipped = !card.flipped;
      console.log('Flipped ', card.altText, ' flipped: ', card.flipped);
    },
  },
  watch: {
    task: {
      handler() {
        console.log('watcher for this.task');
        // Make a copy of all of the cards in the task.
        this.cards = this.task.cards.map((card) => {
          // Retain the flipped statuses of the existing cards
          const oldCard = this.cards.find(
            (existingCard) => existingCard.uuid === card.uuid
          );
          return {
            ...card,
            flipped: oldCard ? oldCard.flipped : false,
          };
        });
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
