<template>
  <button
    type="button"
    class="memory-card no-select"
    :class="{ flipped: card.flipped }"
    v-disable-drag
  >
    <span class="memory-card-front" :class="{ 'solved-card': card.solved }">
      <MultimediaElement :element="card" />
    </span>
    <span class="memory-card-back">
      <lazy-image
        v-if="flipside"
        :src="fileIdToUrl(flipside.file_id)"
        :alt="flipside.altText"
        class="memory-card-back-image"
      />
      <img
        v-else
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
import { fileIdToUrl, ImageElement } from '@/models/TaskDefinition';
import LazyImage from '@/components/LazyImage.vue';
import { $gettext } from '@/language/gettext';
import MultimediaElement from '@/components/MultimediaElement.vue';

export default defineComponent({
  name: 'MemoryViewerCard',
  components: { MultimediaElement, LazyImage },
  props: {
    card: {
      type: Object as PropType<ViewerMemoryCard>,
      required: true,
    },
    flipside: {
      type: Object as PropType<ImageElement>,
      required: false,
    },
  },
  computed: {},
  methods: { $gettext, fileIdToUrl },
});
</script>

<style scoped>
button {
  all: unset; /* Reset browser styles for all buttons in this component */
}

.memory-card {
  width: 12em;
  height: 12em;

  position: relative;

  display: flex;
  justify-content: center;
  align-items: center;

  border: 2px solid #dbe2e8;
  border-radius: 0.5em;
  background-color: #e7ebf1;

  padding: 0.5em;
  margin: 0;

  transform-style: preserve-3d;
  perspective: 1000px;

  transition: transform 0.48s ease-in-out, box-shadow 0.24s ease-in-out;
}

.flipped {
  transform: rotate3d(0, 1, 0, 180deg);
}

.memory-card-front,
.memory-card-back {
  width: 12em;
  height: 12em;

  position: absolute;

  display: flex;
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

  border-radius: 0.25em;
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
