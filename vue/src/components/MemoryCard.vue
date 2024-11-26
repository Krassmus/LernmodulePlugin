<template>
  <button
    type="button"
    class="memory-card no-select"
    :class="{ flipped: card.flipped }"
    v-disable-drag
  >
    <span class="memory-card-front">
      <lazy-image
        v-if="card.file_id"
        v-disable-drag
        :src="fileIdToUrl(card.file_id)"
        :alt="card.altText"
        :class="{ 'solved-card': card.solved }"
      />
    </span>
    <span class="memory-card-back">
      <lazy-image
        v-if="flipside"
        v-disable-drag
        :src="fileIdToUrl(flipside.file_id)"
        :alt="flipside.altText"
      />
      <img
        v-else
        v-disable-drag
        src="../assets/memoryCardBack.png"
        :alt="$gettext('Rückseite einer Memory Karte.')"
        class="memory-card-back-image"
      />
    </span>
  </button>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ViewerMemoryCard } from '@/components/MemoryViewer.vue';
import { fileIdToUrl, Image } from '@/models/TaskDefinition';
import LazyImage from '@/components/LazyImage.vue';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'MemoryCard',
  components: { LazyImage },
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
  methods: { $gettext, fileIdToUrl },
});
</script>

<style scoped>
.memory-card {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;

  width: 100%;
  max-width: 11em;
  aspect-ratio: 1;

  border: 2px solid #d0d7e3;
  color: #28497c;
  background: #e7ebf1;

  transform-style: preserve-3d;
  perspective: 1000px;

  transition: transform 0.48s ease-in-out, border-color 0.12s ease-in-out,
    box-shadow 0.24s ease-in-out;
}

.flipped {
  transform: rotate3d(0, 1, 0, 180deg);
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

.memory-card-back-image {
  height: 100%;
  width: 100%;
  object-fit: contain;
}

.memory-card:not(.flipped):hover {
  cursor: grab;
  border: 2px solid #7ba4d3;
  box-shadow: 0 0 10px 0 #406ef3;
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
