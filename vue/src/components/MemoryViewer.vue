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
        console.log('watcher task');
        this.cards = this.task.cards.map((card) => ({
          ...card,
          flipped: false,
        }));
        console.log('cards:', this.cards);
      },
      immediate: true,
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
