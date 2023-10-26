<script lang="ts">
import { defineComponent, PropType } from 'vue';
import videojs from 'video.js';
import Player from 'video.js/dist/types/player';
require('!style-loader!css-loader!video.js/dist/video-js.css');
import 'videojs-youtube/dist/Youtube.min.js';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';

export default defineComponent({
  name: 'VideoPlayer',
  props: {
    video: {
      type: Object as PropType<InteractiveVideoTask['video']>,
      required: true,
    },
  },
  data() {
    return {
      player: null as Player | null,
      currentTime: 0,
    };
  },
  computed: {
    videoUrl(): string {
      switch (this.video.type) {
        case 'youtube':
          return this.video.url;
        default:
          return '';
      }
    },
    videoType(): string | undefined {
      switch (this.video.type) {
        case 'youtube':
          return 'video/youtube';
        default:
          return '';
      }
    },
  },
  methods: {
    onPlayerReady() {
      console.log('player ready');
    },
    initializePlayer() {
      // If player has already been created, it must be destroyed before we
      // create a new one.
      this.player?.dispose();

      // The dispose() method removes the player element from the dom, so we must
      // create and append the player element by hand to the DOM each time.
      const playerElement = document.createElement('video-js');
      playerElement.classList.add('video-js', 'vjs-fluid');
      (this.$refs.container as HTMLDivElement).appendChild(playerElement);

      // Create the player and set up event listeners
      this.player = videojs(
        playerElement,
        {
          techOrder: ['youtube'],
          sources: [
            {
              src: this.videoUrl,
              type: this.videoType,
            },
          ],
          controls: true,
        },
        this.onPlayerReady
      );
      this.player.on('timeupdate', () => {
        const time = this.player!.currentTime();
        if (time) {
          this.currentTime = time;
        }
      });
    },
  },
  mounted() {
    this.initializePlayer();
  },
  watch: {
    video: {
      handler(newVal) {
        console.log('video prop changed', newVal);
        this.initializePlayer();
      },
    },
  },
});
</script>

<template>
  <div>
    <div ref="container"></div>
    <pre>{{ currentTime }}</pre>
  </div>
</template>

<style scoped></style>
