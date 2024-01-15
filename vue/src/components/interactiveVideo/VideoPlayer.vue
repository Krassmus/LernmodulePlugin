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
import { printTaskType, viewerForTaskType } from '@/models/TaskDefinition';
import { $gettext } from '../../language/gettext';

type DragState =
  | {
      type: 'dragInteraction';
      interactionId: string;
      mouseStartPos: [number, number]; // clientX, clientY
      interactionStartPos: [number, number]; // fraction x, fraction y
    }
  | undefined;
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
      activeInteraction: undefined as Interaction | undefined,
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
        case 'studipFileReference':
          return this.task.video.file.url;
        default:
          return '';
      }
    },
    videoType(): string | undefined {
      switch (this.task.video.type) {
        case 'youtube':
          return 'video/youtube';
        case 'studipFileReference':
          return this.task.video.file.type;
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
        return i.startTime <= this.time && i.endTime > this.time;
      });
    },
  },
  methods: {
    $gettext,
    printTaskType,
    viewerForTaskType,
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
          techOrder: ['html5', 'youtube'],
          sources: [
            {
              src: this.videoUrl,
              type: this.videoType,
            },
          ],
          controls: true,
          autoplay: this.task.autoplay && !this.editor,
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
        this.player!.currentTime(this.task.startAt);
        if (this.task.autoplay && !this.editor) {
          this.player!.play();
        }
        this.$emit('metadataChange', {
          length: this.player!.duration(),
        });
      });
    },
    activateInteraction(interaction: Interaction) {
      this.activeInteraction = interaction;
      this.player!.pause();
    },
    closeInteraction() {
      this.activeInteraction = undefined;
    },
    onDragStartInteraction(event: DragEvent, interaction: Interaction) {
      console.log('dragStart');
      event.dataTransfer!.setDragImage(
        event.target as Element,
        -999999,
        -999999
      );
      this.dragState = {
        type: 'dragInteraction',
        interactionId: interaction.id,
        mouseStartPos: [event.clientX, event.clientY],
        interactionStartPos: [interaction.x, interaction.y],
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
        const clientDx = event.clientX - this.dragState.mouseStartPos[0];
        const dxFraction = clientDx / rect.width;
        const xFraction = this.dragState.interactionStartPos[0] + dxFraction;
        const clampedXFraction = Math.min(1, Math.max(0, xFraction));
        const clientDy = event.clientY - this.dragState.mouseStartPos[1];
        const dyFraction = clientDy / rect.height;
        const yFraction = this.dragState.interactionStartPos[1] + dyFraction;
        const clampedYFraction = Math.min(1, Math.max(0, yFraction));
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
    <div
      class="cancel-selection-overlay"
      v-if="!!editor?.selectedInteractionId.value"
      @click="editor!.selectInteraction(undefined)"
    ></div>
    <template v-for="interaction in visibleInteractions" :key="interaction.id">
      <LmbTaskInteraction
        v-if="interaction.type === 'lmbTask'"
        class="video-player-interaction"
        :style="{
          left: `${interaction.x * 100}%`,
          top: `${interaction.y * 100}%`,
        }"
        :interaction="interaction"
        :draggable="!!editor"
        @pointerdown.capture="editor?.selectInteraction(interaction.id)"
        @dragstart="onDragStartInteraction($event, interaction)"
        @dragend="onDragEndInteraction($event, interaction)"
        @activateInteraction="activateInteraction"
      />
    </template>
    <Transition name="fade">
      <div
        v-if="activeInteraction?.type === 'lmbTask'"
        class="active-interaction-container"
        @click.self="closeInteraction"
      >
        <div class="active-interaction">
          <h1>
            {{ printTaskType(activeInteraction.taskDefinition.task_type) }}
          </h1>
          <component
            :is="viewerForTaskType(activeInteraction.taskDefinition.task_type)"
            :task="activeInteraction.taskDefinition"
          />
          <button type="button" class="button cancel" @click="closeInteraction">
            {{ $gettext('Schließen') }}
          </button>
        </div>
      </div>
      <div v-else-if="activeInteraction?.type === 'overlay'">Overlay</div>
    </Transition>
  </div>
</template>

<style scoped lang="scss">
.drag-in-progress > :not(.video-player-interaction) {
  pointer-events: none;
}
.video-player-root {
  position: relative;
  overflow: hidden;
}
.cancel-selection-overlay {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  width: 100%;
}
.video-player-interaction {
  position: absolute;
}
.active-interaction-container {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  width: 100%;

  .active-interaction {
    position: absolute;
    top: 1em;
    bottom: 1em;
    left: 1em;
    right: 1em;
    background: white;
    padding: 1em;
    overflow: scroll;
  }
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
