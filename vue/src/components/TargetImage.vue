<template>
  <div v-if="draggableImage" class="grid-square two-images">
    <div class="image-container back">
      <img
        :src="targetImage.imageUrl"
        :alt="targetImage.altText"
        class="image small"
      />
    </div>
    <div class="image-container front">
      <img
        :src="draggableImage.imageUrl"
        :alt="draggableImage.altText"
        class="image small"
      />
    </div>
  </div>
  <div v-else class="grid-square one-image">
    <img
      draggable="false"
      :src="targetImage.imageUrl"
      :alt="targetImage.altText"
      class="image large"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Image } from '@/models/TaskDefinition';

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
  },
  computed: {},
  methods: {},
});
</script>

<style scoped>
.grid-square {
  width: 8em;
  padding: 6px;
  margin: 6px;
  height: 8em;
  border-radius: 6px 6px 6px 6px;

  border: 2px solid #dbe2e8;
  box-shadow: 2px 2px 0 2px rgba(203, 213, 222, 0.2);
}

.grid-square.one-image {
  display: flex;
  justify-content: center;
}

.grid-square.two-images {
  position: relative;
  cursor: pointer;
}
.grid-square.two-images:not(:hover) .image {
  border: 1px solid white;
}
.grid-square.two-images:hover .image {
  border: 1px solid rgba(0, 187, 109, 0.93);
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
}
</style>
