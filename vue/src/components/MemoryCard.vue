<template>
  <div
    class="memoryCard no-select"
    :class="{ memoryCardFlipped: card.flipped }"
    v-disable-drag
  >
    <div class="memoryCardFront">
      <img
        :src="fileIdToUrl(card.file_id)"
        :alt="card.altText"
        class="memoryImage"
      />
    </div>
    <div class="memoryCardBack">
      <img
        src="../assets/memoryCardBack.png"
        alt="The back of a card in the memory game."
        class="memoryImage"
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
  transition: all 0.2s ease-out;
  position: relative;
}

.memoryCardFlipped {
  transition: transform 0.4s ease-in-out;
  transform-style: preserve-3d;
  perspective: 1000px; /* Perspective for 3D flip */
  transform: rotateY(180deg);
}

.memoryImage {
  max-width: 100%;
  max-height: 100%;
}

.memoryCardFront,
.memoryCardBack {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-grow: 1;
  width: 100%;
  position: absolute;
  backface-visibility: hidden;
}

.memoryCardFront {
  transform: rotateY(180deg); /* Start rotated, flipped with the back side */
}

.memoryCardBack {
  transform: rotateY(0deg); /* Back side is initially visible */
}

.memoryCard:not(.memoryCardFlipped):hover {
  border-color: rgb(0, 78, 159);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgb(0, 78, 159);
}
</style>
