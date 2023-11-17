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

type DragState = { type: 'dragInteraction'; interactionId: string } | undefined;
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
      dragState: undefined as DragState,
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
    onDragStartInteraction(event: DragEvent, interaction: Interaction) {
      console.log('dragStart');
      event.dataTransfer!.setDragImage(event.target as Element, -99999, -99999);
      this.dragState = {
        type: 'dragInteraction',
        interactionId: interaction.id,
      };
    },
    onDragEndInteraction(event: DragEvent, interaction: Interaction) {
      console.log('dragEnd');
      this.dragState = undefined;
    },
    onDragoverRoot(event: DragEvent) {
      console.log('onDragoverRoot');
      if (this.dragState?.type === 'dragInteraction') {
        const rect = (this.$refs.root as HTMLElement).getBoundingClientRect();
        const x = event.clientX - rect.left;
        const xFraction = x / rect.width;
        const clampedXFraction = Math.min(1, Math.max(0, xFraction));
        const y = event.clientY - rect.top;
        const yFraction = y / rect.height;
        const clampedYFraction = Math.min(1, Math.max(0, yFraction));
        console.log(
          'clientX',
          event.clientX,
          'rect.left',
          rect.left,
          'rect.width',
          rect.width,
          'x',
          x,
          'xFraction',
          xFraction,
          'clampedXFraction',
          clampedXFraction
        );
        const id = this.dragState.interactionId;
        this.editor?.dragInteraction(id, clampedXFraction, clampedYFraction);
      }
    },
  },
  mounted() {
    this.initializePlayer();
  },
});
</script>

<template>
  <div
    class="video-player-root"
    ref="root"
    @dragover.capture="onDragoverRoot"
    :class="{ 'drag-in-progress': !!dragState }"
  >
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
        :draggable="!!editor"
        @pointerdown.capture="editor?.selectInteraction(interaction.id)"
        @dragstart="onDragStartInteraction($event, interaction)"
        @dragend="onDragEndInteraction($event, interaction)"
      />
    </template>
  </div>
</template>

<style scoped>
.drag-in-progress > :not(.video-player-overlay) {
  pointer-events: none;
}
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
