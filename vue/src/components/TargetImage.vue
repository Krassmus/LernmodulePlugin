<template>
  <!--  TODO #28 this should be a button, not a div, because it is clickable. -->
  <div v-if="draggableImage" class="grid-square two-images">
    <div class="image-container back">
      <img
        :src="fileIdToUrl(targetImage.file_id)"
        :alt="targetImage.altText"
        class="image"
        draggable="false"
        :class="{
          correct: this.isCorrect,
          incorrect: !this.isCorrect,
          showResult: this.showResult,
        }"
      />
    </div>
    <div class="image-container front">
      <img
        :src="fileIdToUrl(draggableImage.file_id)"
        :alt="draggableImage.altText"
        class="image"
        draggable="false"
        :class="{
          correct: this.isCorrect,
          incorrect: !this.isCorrect,
          showResult: this.showResult,
        }"
      />
    </div>
  </div>
  <div v-else class="grid-square one-image">
    <img
      draggable="false"
      :src="fileIdToUrl(targetImage.file_id)"
      :alt="targetImage.altText"
      class="image"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { fileIdToUrl, Image } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'TargetImage',
  components: {},
  props: {
    draggableImage: {
      type: Object as PropType<Image>,
      required: false,
    },
    targetImage: {
      type: Object as PropType<Image>,
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
  width: 8em;
  padding: 6px;
  margin: 6px;
  height: 8em;
  border-radius: 6px;
  border: 2px solid transparent;
}

.grid-square.one-image {
  display: flex;
  justify-content: center;
  border-color: #dbe2e8;
  box-shadow: 2px 2px 0 2px rgba(203, 213, 222, 0.2);
  background-color: #ffffff;
}

.grid-square.two-images {
  position: relative;
  background-color: #eef1f4;
}

.grid-square.two-images .image {
  border-radius: 6px;
}

.grid-square.two-images:not(:hover):not(.showResult) .image {
  border: 2px solid white;
}

.grid-square.two-images .image:is(.correct):is(.showResult) {
  border: 2px solid #64a877;
}

.grid-square.two-images .image:is(.incorrect):is(.showResult) {
  border: 2px solid #dd2e2e;
}

.grid-square.two-images:hover .image:not(.showResult) {
  cursor: grab;
  border: 2px solid #7ba4d3;
  box-shadow: 0 0 10px 0 #406ef3;
}

.grid-square.two-images:hover .image:is(.showResult) {
  cursor: default;
}

.image-container {
  position: absolute;
  width: 55%;
  height: 55%;
}

.image-container.front {
  top: 0.5em;
  left: 0.5em;
}

.image-container.back {
  bottom: 0.5em;
  right: 0.5em;
}

.image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  object-position: center;
  box-sizing: border-box;
  background-color: #eef1f4;
}
</style>
