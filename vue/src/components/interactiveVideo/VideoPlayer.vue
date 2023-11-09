<script lang="ts">
import { defineComponent, PropType } from 'vue';
import videojs from 'video.js';
import Player from 'video.js/dist/types/player';
require('!style-loader!css-loader!video.js/dist/video-js.css');
import 'videojs-youtube/dist/Youtube.min.js';
import {
  Interaction,
  InteractiveVideoTask,
} from '@/models/InteractiveVideoTask';

export default defineComponent({
  name: 'VideoPlayer',
  props: {
    task: {
      type: Object as PropType<InteractiveVideoTask>,
      required: true,
    },
  },
  data() {
    return {
      player: null as Player | null,
      time: 0,
    };
  },
  watch: {
    'task.video': function (value) {
      console.log('video prop changed', value);
      this.initializePlayer();
    },
  },
  computed: {
    videoUrl(): string {
      switch (this.task.video.type) {
        case 'youtube':
          return this.task.video.url;
        default:
          return '';
      }
    },
    videoType(): string | undefined {
      switch (this.task.video.type) {
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
          this.$emit('timeupdate', time);
          this.time = time;
        }
      });
      this.player.on('loadedmetadata', () => {
        this.$emit('metadataChange', {
          length: this.player!.duration(),
        });
      });
    },
  },
  mounted() {
    this.initializePlayer();
  },
});
</script>

<template>
  <div class="video-player-root">
    <div ref="container"></div>
    <slot />
  </div>
</template>

<style scoped>
.video-player-root {
  position: relative;
}
</style>
