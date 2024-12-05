<template>
  <img
    v-if="isLoaded"
    :src="src"
    :alt="alt"
    @load="onLoad"
    @error="onError"
    class="lazy-image"
    v-bind="$attrs"
    v-disable-drag
  />
  <span v-else class="placeholder" />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { requestQueueStore } from '@/store';

export default defineComponent({
  name: 'LazyImage',

  props: {
    src: { type: String, required: true },
    alt: { type: String, default: '' },
  },
  data() {
    return {
      isLoaded: false, // Tracks if the image has been successfully loaded
    };
  },
  methods: {
    onLoad() {
      console.log(`Image loaded: ${this.src}`);
    },

    onError() {
      console.error(`Failed to load image: ${this.src}`);
    },

    enqueueImageLoad() {
      requestQueueStore.enqueue(async () => {
        try {
          // Simulate an image load request
          const img = new Image();
          img.src = this.src;

          await new Promise<void>((resolve, reject) => {
            img.onload = () => {
              resolve();
            };
            img.onerror = () =>
              reject(new Error(`Failed to load image: ${this.src}`));
          });

          this.isLoaded = true;
        } catch (error) {
          console.error(error);
        }
      });
    },
  },
  computed: {
    useLazyLoading(): boolean {
      return window.STUDIP.LernmoduleVueJS.LERNMODULE_LAZYLOADING;
    },
  },
  mounted() {
    if (!this.useLazyLoading) {
      // Directly load the image
      this.enqueueImageLoad();
      return;
    } else {
      // Load image when in view
      const observer = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting) {
          this.enqueueImageLoad();
          observer.unobserve(entry.target); // Stop observing
          observer.disconnect(); // Clean up the observer
        }
      });

      observer.observe(this.$el as HTMLElement);
    }
  },
});
</script>

<style scoped>
.lazy-image {
  height: 100%;
  width: 100%;
  padding: 0;
  margin: 0;
  object-fit: contain;
}

.placeholder {
  height: 100%;
  width: 100%;
}
</style>
