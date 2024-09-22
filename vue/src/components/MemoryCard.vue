<template>
    <div v-if="card.flipped" class="memoryCardFront">
  <div
    class="memoryCard no-select"
    :class="{ memoryCardFlipped: card.flipped }"
  >
      <img
        :src="fileIdToUrl(card.file_id)"
        :alt="card.altText"
        class="memoryImage"
      />
    </div>
    <div v-else class="memoryCardBack">
      <img
        src="../assets/memoryCardBack.png"
        class="memoryImage"
        alt="The back of a card in the memory game."
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ViewerMemoryCard } from '@/components/MemoryViewer.vue';
import { fileIdToUrl } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'MemoryCard',
  props: {
    card: {
      type: Object as PropType<ViewerMemoryCard>,
      required: true,
    },
  },
  computed: {},
  methods: { fileIdToUrl },
});
</script>

<style scoped>
.memoryCard {
  display: flex;
  flex-flow: column;
  align-items: center;
  aspect-ratio: 1;
  border: 2px solid #d0d7e3;
  color: rgb(40, 73, 124);
  padding: 1px 1px 1px 1px;
  transition: all 0.5s ease;
}

.memoryCardFlipped {
  transform: rotateY(180deg);
  transform-style: flat;
}

.memoryImage {
  max-width: 100%;
  max-height: 100%;
}

.memoryCardFront {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-grow: 1;
  width: 100%;
}

.memoryCardBack {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-grow: 1;
  width: 100%;
}

.memoryCard:not(.memoryCardFlipped):hover {
  border-color: rgb(0, 78, 159);
  background: rgb(0, 78, 159);
  padding: 2px 2px 2px 2px;
}
</style>
