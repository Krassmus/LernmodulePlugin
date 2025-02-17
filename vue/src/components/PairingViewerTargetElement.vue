<template>
  <button
    type="button"
    class="grid-square"
    :class="{
      'two-images': !!this.draggableImage,
      'one-image': !this.draggableImage,
      correct: this.isCorrect,
      incorrect: !this.isCorrect,
      showResult: this.showResult,
    }"
  >
    <template v-if="this.draggableImage">
      <div class="image-container back">
        <MultimediaElement
          :element="targetImage"
          class="image"
          :class="{
            correct: this.isCorrect,
            incorrect: !this.isCorrect,
            showResult: this.showResult,
          }"
        />
      </div>
      <div class="image-container front" :draggable="!this.showResult">
        <MultimediaElement
          :element="draggableImage"
          class="image"
          :class="{
            correct: this.isCorrect,
            incorrect: !this.isCorrect,
            showResult: this.showResult,
          }"
        />
      </div>
    </template>
    <MultimediaElement v-else :element="targetImage" />
  </button>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { fileIdToUrl } from '@/models/TaskDefinition';
import MultimediaElement from '@/components/MultimediaElement.vue';
import { ImageElement } from '@/models/common';

export default defineComponent({
  name: 'TargetImage',
  components: { MultimediaElement },
  props: {
    draggableImage: {
      type: Object as PropType<ImageElement>,
      required: false,
    },
    targetImage: {
      type: Object as PropType<ImageElement>,
      required: true,
    },
    isCorrect: {
      type: Boolean,
      required: false,
    },
    showResult: {
      type: Boolean,
      required: false,
    },
  },
  computed: {},
  methods: { fileIdToUrl },
});
</script>

<style scoped>
.grid-square {
  display: flex;
  margin: unset;
  width: 8em;
  height: 8em;
  box-sizing: content-box;

  border: 2px solid #dbe2e8;
  border-radius: 0.5em;
  background: white;
  padding: 0.5em;
}

.grid-square.one-image {
  display: flex;
  justify-content: center;
}

.grid-square.two-images {
  position: relative;
}

.grid-square.two-images .image {
  border-radius: 0.5em;
  border: 2px solid #dbe2e8;
  padding: 0.25em;
  background: white;
}

.grid-square.two-images:not(:hover):not(.showResult) {
  background: unset;
  border: 2px solid transparent;
}

.grid-square.two-images:is(.correct):is(.showResult) {
  border: 2px solid #64a877;
  box-shadow: inset 0 0 120px #64a877;
}

.grid-square.two-images:is(.incorrect):is(.showResult) {
  border: 2px solid #dd2e2e;
  box-shadow: inset 0 0 120px #dd2e2e;
}

.grid-square.two-images:hover:not(.showResult) {
  cursor: grab;
  border: 2px solid #7ba4d3;
  box-shadow: inset 0 0 72px #cbd5de, 0 0 10px 0 #406ef3;
}

.grid-square.two-images:hover .image:is(.showResult) {
  cursor: default;
}

.image-container {
  position: absolute;
  width: 60%;
  height: 60%;
}

.image-container.front {
  top: 0.25em;
  left: 0.25em;
}

.image-container.back {
  bottom: 0.25em;
  right: 0.25em;
}

.image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  object-position: center;
  box-sizing: border-box;
}
</style>
