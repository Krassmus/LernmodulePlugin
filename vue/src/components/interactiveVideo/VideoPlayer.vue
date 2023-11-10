<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import videojs from 'video.js';
import Player from 'video.js/dist/types/player';
require('!style-loader!css-loader!video.js/dist/video-js.css');
import 'videojs-youtube/dist/Youtube.min.js';
import {
  Interaction,
  InteractiveVideoTask,
} from '@/models/InteractiveVideoTask';
import {
  EditorState,
  editorStateSymbol,
} from '@/components/interactiveVideo/editorState';
import LmbTaskInteraction from '@/components/interactiveVideo/interactions/LmbTaskInteraction.vue';

export default defineComponent({
  name: 'VideoPlayer',
  components: { LmbTaskInteraction },
  setup() {
    return {
      editor: inject<EditorState>(editorStateSymbol),
    };
  },
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
    /**
     * @return a list of Interaction objects whose clickable icons (or other elements)
     * should be shown overlaid over the video at the timestamp given by this.time.
     */
    visibleInteractions(): Interaction[] {
      return this.task.interactions.filter((i) => {
        const endTime = i.type === 'pause' ? i.startTime + 1 : i.endTime;
        return i.startTime <= this.time && endTime > this.time;
      });
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
    <template v-for="interaction in visibleInteractions" :key="interaction.id">
      <LmbTaskInteraction
        v-if="interaction.type === 'lmbTask'"
        class="video-player-overlay"
        :style="{
          left: `${interaction.x * 100}%`,
          top: `${interaction.y * 100}%`,
        }"
        :interaction="interaction"
        @pointerdown.capture="editor?.selectInteraction(interaction.id)"
      />
    </template>
  </div>
</template>

<style scoped>
.video-player-root {
  position: relative;
}
.video-player-overlays {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: transparent;
}
.video-player-overlay {
  position: absolute;
  background: white;
  aspect-ratio: 1/1;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
