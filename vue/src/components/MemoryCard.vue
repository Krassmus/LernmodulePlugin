<template>
  <div
    class="memory-card no-select"
    :class="{ flipped: card.flipped }"
    v-disable-drag
  >
    <div class="memory-card-front">
      <img
        :src="fileIdToUrl(card.file_id)"
        :alt="card.altText"
        class="memory-card-image"
      />
    </div>
    <div class="memory-card-back">
      <img
        src="../assets/memoryCardBack.png"
        alt="The back of a card in the memory game."
        class="memory-card-image"
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
.memory-card {
  display: flex;
  flex-flow: column;
  align-items: center;
  aspect-ratio: 1;
  border: 2px solid #d0d7e3;
  color: rgb(40, 73, 124);
  transition: all 0.24s ease-in-out;
  transform-style: preserve-3d;
  perspective: 1000px; /* Perspective for 3D flip */
  position: relative;
}

.flipped {
  transform: rotateY(180deg);
}

.memory-card-image {
  max-width: 100%;
  max-height: 100%;
}

.memory-card-front,
.memory-card-back {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-grow: 1;
  width: 100%;
  position: absolute;
}

.memory-card-front {
  transform: rotateY(180deg); /* Start rotated, flipped with the back side */
  backface-visibility: visible;
}

.memory-card-back {
  transform: rotateY(0deg); /* Back side is initially visible */
  backface-visibility: hidden;
}

.memory-card:not(.flipped):hover {
  transition: all 0.1s ease-out;
  border-color: rgb(0, 78, 159);
  box-shadow: 0 0 8px rgb(0, 78, 159);
}
</style>
