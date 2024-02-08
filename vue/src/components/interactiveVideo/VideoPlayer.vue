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
import { createPopper, Instance } from '@popperjs/core';
import type { StrictModifiers } from '@popperjs/core';
import { v4 } from 'uuid';

type DragState =
  | {
      type: 'dragInteraction';
      interactionId: string;
      mouseStartPos: [number, number]; // clientX, clientY
      interactionStartPos: [number, number]; // fraction x, fraction y
    }
  | undefined;

let popperInstance: Instance | undefined;
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
  computed: {
    // A unique ID for this instance of VideoPlayer, so that we can refer
    // to elements inside of it by ID when there are multiple VideoPlayers
    // (e.g. viewer on one tab, editor on another tab).
    uid(): string {
      return v4();
    },
    // Return the Interaction, if any, for which a popper tooltip should be shown
    interactionForPopper(): Interaction | undefined {
      // The popper should only be rendered if the selected interaction is visible.
      if (
        this.selectedInteraction &&
        this.visibleInteractions.includes(this.selectedInteraction)
      ) {
        return this.selectedInteraction;
      } else {
        return undefined;
      }
    },
    selectedInteraction(): Interaction | undefined {
      return this.task.interactions.find(
        (i) => i.id === this.selectedInteractionId
      );
    },
    selectedInteractionId(): string | undefined {
      return this.editor?.selectedInteractionId.value;
    },
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
    // Computed property created so that we can register a watcher which triggers
    // when either of these things changes.
    timeAndVisibleInteractions(): [number, Interaction[]] {
      return [this.time, this.visibleInteractions];
    },
  },
  watch: {
    'task.video': function (value) {
      console.log('video prop changed', value);
      this.initializePlayer();
    },
    // Handle pausing the video when an interaction becomes visible.
    timeAndVisibleInteractions(
      [newTime, newVisibleInteractions]: [number, Interaction[]],
      [oldTime, oldVisibleInteractions]: [number, Interaction[]]
    ): void {
      const isPlayingForward = newTime > oldTime;
      const newlyVisibleInteractionWithPause = newVisibleInteractions.find(
        (i) => !oldVisibleInteractions.includes(i) && i.pauseWhenVisible
      );
      if (isPlayingForward && newlyVisibleInteractionWithPause) {
        console.log(
          'pausing for interaction: ',
          newlyVisibleInteractionWithPause
        );
        this.player!.pause();
      }
    },
    interactionForPopper: {
      immediate: true,
      handler: function (interaction: Interaction | undefined) {
        popperInstance?.destroy();
        if (interaction) {
          // Run on next tick, because we need to wait for the interactionElement
          // to be rendered by Vue, in case it isn't yet visible at the moment this
          // watcher is triggered.
          // For example, if the user clicks on the timeline to jump to a point
          // in the video where the currently selected interaction is visible,
          // when it previously was not.
          this.$nextTick(() => {
            const interactionElement = document.getElementById(
              `interaction-${this.uid}-${interaction.id}`
            ) as HTMLElement;
            const tooltipElement = this.$refs
              .selectedInteractionTooltip as HTMLElement;
            popperInstance = createPopper(interactionElement, tooltipElement, {
              placement: 'top',
              modifiers: [
                {
                  name: 'offset',
                  options: {
                    offset: [0, 8],
                  },
                },
              ],
            });
          });
        }
      },
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
      (this.$refs.videoJsContainer as HTMLDivElement).appendChild(
        playerElement
      );

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
    editInteraction(interaction: Interaction) {
      this.editor!.editInteraction(interaction.id);
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
        popperInstance?.update();
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
    <div ref="videoJsContainer"></div>
    <div
      class="cancel-selection-overlay"
      v-if="!!selectedInteractionId"
      @click="editor!.selectInteraction(undefined)"
    ></div>
    <div
      ref="selectedInteractionTooltip"
      v-if="editor"
      class="selected-interaction-tooltip"
      :class="{
        hidden: !interactionForPopper,
      }"
    >
      <button
        type="button"
        class="small-button visibility-visible"
        @click="activateInteraction(selectedInteraction)"
        :title="$gettext('Vorschau')"
      ></button>
      <button
        type="button"
        class="small-button edit"
        @click="editInteraction(selectedInteraction)"
        :title="$gettext('Bearbeiten')"
      ></button>
      <button
        type="button"
        class="small-button trash"
        @click="editor?.deleteInteraction(selectedInteractionId)"
        :title="$gettext('Löschen')"
      ></button>
      <div class="arrow" data-popper-arrow></div>
    </div>
    <template v-for="interaction in visibleInteractions" :key="interaction.id">
      <LmbTaskInteraction
        v-if="interaction.type === 'lmbTask'"
        :id="`interaction-${uid}-${interaction.id}`"
        class="video-player-interaction"
        :style="{
          left: `${interaction.x * 100}%`,
          top: `${interaction.y * 100}%`,
        }"
        :interaction="interaction"
        :draggable="!!editor"
        @pointerdown.capture="editor?.selectInteraction(interaction.id)"
        @click="editor?.selectInteraction(interaction.id)"
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
}
.selected-interaction-tooltip {
  &.hidden {
    display: none;
  }
  z-index: 2;
  position: absolute;
  display: flex;
  gap: 0.5em;
  background: white;
  color: black;
  border-radius: 12px;
  padding: 8px;
  > .arrow,
  > .arrow::before {
    position: absolute;
    width: 8px;
    height: 8px;
    background: inherit;
  }

  > .arrow {
    visibility: hidden;
  }

  > .arrow::before {
    visibility: visible;
    content: '';
    transform: rotate(45deg);
  }

  &[data-popper-placement^='top'] > .arrow {
    bottom: -4px;
  }

  &[data-popper-placement^='bottom'] > .arrow {
    top: -4px;
  }

  &[data-popper-placement^='left'] > .arrow {
    right: -4px;
  }

  &[data-popper-placement^='right'] > .arrow {
    left: -4px;
  }
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
  &:focus {
    z-index: 2;
  }
}
.active-interaction-container {
  z-index: 3;
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