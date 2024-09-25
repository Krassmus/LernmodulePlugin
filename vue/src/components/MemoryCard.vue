<template>
  <button
    type="button"
    class="memory-card no-select"
    :class="{ flipped: card.flipped }"
    v-disable-drag
  >
    <span class="memory-card-front">
      <img
        v-if="card.file_id"
        :src="fileIdToUrl(card.file_id)"
        :alt="card.altText"
        class="memory-card-image"
        :class="{ 'solved-card': card.solved }"
      />
    </span>
    <span class="memory-card-back">
      <img
        v-if="flipside"
        :src="fileIdToUrl(flipside.file_id)"
        :alt="flipside.altText"
        class="memory-card-image"
      />
      <img
        v-else
        src="../assets/memoryCardBack.png"
        alt="The back of a card in the memory game."
        class="memory-card-image"
      />
    </span>
  </button>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ViewerMemoryCard } from '@/components/MemoryViewer.vue';
import { fileIdToUrl, Image } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'MemoryCard',
  props: {
    card: {
      type: Object as PropType<ViewerMemoryCard>,
      required: true,
    },
    flipside: {
      type: Object as PropType<Image>,
      required: false,
    },
  },
  computed: {},
  methods: { fileIdToUrl },
});
</script>

<style scoped>
.memory-card {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;

  width: 100%;
  aspect-ratio: 1;

  border: 2px solid #d0d7e3;
  color: #28497c;
  background: #e7ebf1;

  transform-style: preserve-3d;
  perspective: 1000px;

  transition: transform 0.64s ease-in-out, border-color 0.12s ease-in-out,
    box-shadow 0.24s ease-in-out;
}

.flipped {
  transform: rotate3d(0, 1, 0, 180deg);
}

.memory-card-image {
  width: 100%;
  aspect-ratio: 1;
  object-fit: contain;
}

.memory-card-front,
.memory-card-back {
  width: 100%;
  position: absolute;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  backface-visibility: hidden;
}

.memory-card-front {
  transform: rotateY(180deg); /* Start rotated, flipped with the back side */
}

.memory-card-back {
  transform: rotateY(0deg); /* Back side is initially visible */
}

.memory-card:not(.flipped):hover {
  border-color: rgb(0, 78, 159);
  box-shadow: 0 0 8px rgb(0, 78, 159);
}

.solved-card {
  animation: foundPairEffect 1s ease-in-out;
  animation-fill-mode: forwards; /* Ensures final state persists */
}

@keyframes foundPairEffect {
  50% {
    opacity: 1;
  }
  100% {
    opacity: 0.6;
  }
}
</style>
