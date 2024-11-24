<template>
  <img
    v-if="isVisible"
    :src="src"
    :alt="alt"
    @load="onLoad"
    @error="onError"
    class="lazy-image"
  />
  <span v-else class="placeholder" />
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'LazyImage',

  props: {
    src: { type: String, required: true },
    alt: { type: String, default: '' },
  },
  data() {
    return {
      isVisible: false, // Tracks whether the image should load
    };
  },
  methods: {
    onLoad() {
      console.log(`Image loaded: ${this.src}`);
    },
    onError() {
      console.error(`Failed to load image: ${this.src}`);
    },
  },
  mounted() {
    const observer = new IntersectionObserver(([entry]) => {
      if (entry.isIntersecting) {
        this.isVisible = true; // Load the image
        observer.unobserve(entry.target); // Stop observing
        observer.disconnect(); // Clean up the observer
      }
    });

    observer.observe(this.$el as HTMLElement);
  },
});
</script>

<style scoped>
.lazy-image {
  height: 100%;
  width: 100%;
  object-fit: contain;
}

.placeholder {
  height: 100%;
  width: 100%;
}
</style>
