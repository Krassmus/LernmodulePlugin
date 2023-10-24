<script lang="ts">
import { defineComponent } from 'vue';
import videojs from 'video.js';
require('!style-loader!css-loader!video.js/dist/video-js.css');
import 'videojs-youtube/dist/Youtube.min.js';

export default defineComponent({
  name: 'VideoPlayer',
  props: {
    url: {
      type: String,
      required: true,
    },
  },
  mounted() {
    videojs(this.$refs.videoElement as Element, {
      techOrder: ['youtube'],
      sources: [{ src: this.url, type: 'video/youtube' }],
    });
  },
  watch: {
    url: {
      immediate: true,
      handler(newVal: string) {
        console.log('url changed', newVal);
      },
    },
  },
});
</script>

<template>
  <video-js ref="videoElement" class="video-js"> </video-js>
</template>

<style scoped></style>
