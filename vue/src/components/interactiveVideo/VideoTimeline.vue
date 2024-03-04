<script lang="ts">
import {
  ComponentPublicInstance,
  defineComponent,
  inject,
  PropType,
  StyleValue,
} from 'vue';
import { VideoMetadata } from '@/components/interactiveVideo/events';
import { throttle } from 'lodash';
import {
  iconForInteraction,
  Interaction,
  InteractiveVideoTask,
  printInteractionType,
} from '@/models/InteractiveVideoTask';
import { $gettext } from '../../language/gettext';
import {
  EditorState,
  editorStateSymbol,
} from '@/components/interactiveVideo/editorState';
import getEmValueFromElement from '@/components/interactiveVideo/getEmValueFromElement';
import { select } from 'd3-selection';
import { D3DragEvent, drag } from 'd3-drag';

type DragState =
  | { type: 'timeMarker' }
  | { type: 'panTimeline' }
  | {
      type: 'interaction';
      id: string;
      mouseStartPos: [number, number]; // clientX, clientY
      interactionStartTime: number; // Seconds
      interactionDuration: number; // Seconds
    }
  | {
      type: 'interactionStart';
      id: string;
      time: number; // Seconds
    }
  | {
      type: 'interactionEnd';
      id: string;
      time: number; // Seconds
    }
  | undefined;
export default defineComponent({
  name: 'VideoTimeline',
  setup() {
    return {
      editor: inject<EditorState>(editorStateSymbol),
    };
  },
  props: {
    currentTime: {
      type: Number,
      required: true,
    },
    videoMetadata: {
      type: Object as PropType<VideoMetadata>,
      required: true,
    },
    task: {
      type: Object as PropType<InteractiveVideoTask>,
      required: true,
    },
    selectedInteractionId: {
      type: String,
      required: false,
    },
  },
  data() {
    return {
      dragState: undefined as DragState,
      zoomTransform: { t: 0, k: 1 },
      viewportWidthEm: 1,
    };
  },
  mounted() {
    // When timeline axis becomes visible, its width should be recalculated.
    // TODO recalculate also when it changes size (e.g. window is resized)
    const observer = new IntersectionObserver(() =>
      this.recomputeViewportWidth()
    );
    observer.observe(this.$refs.timelineAxis as HTMLElement);

    // Set up d3-drag
    const dragTimeline = (ev: D3DragEvent<HTMLElement, unknown, unknown>) => {
      const dSeconds = this.pixelsToSeconds(ev.dx);
      const translate = this.zoomTransform.t - dSeconds;
      this.zoomTransform.t = this.constrainZoomTranslate(translate);
    };
    const dragBehavior = drag<HTMLElement, unknown>()
      .filter((event: D3DragEvent<HTMLElement, unknown, unknown>) => {
        return (event as any).button !== 0; // All buttons except 'left click'
      })
      .on('start', () => {
        this.dragState = { type: 'panTimeline' };
      })
      .on('drag', dragTimeline)
      .on('end', () => {
        this.dragState = undefined;
      });
    select(this.$refs.timeline as HTMLElement).call(dragBehavior);
  },
  watch: {
    // Automatically scroll timeline to keep the current playback position in view
    currentTime(currentTime: number) {
      if (currentTime < this.viewportStart || currentTime > this.viewportEnd) {
        this.zoomTransform.t = this.constrainZoomTranslate(currentTime - 0.5);
      }
    },
  },
  computed: {
    visibleInteractions(): Interaction[] {
      const visibleInteractions: Interaction[] = [];
      for (let i of this.task.interactions) {
        const isWithinViewport =
          i.startTime < this.viewportEnd && i.endTime > this.viewportStart;
        // An Interaction element should not suddenly disappear if it is dragged
        // outside of the timeline viewport. That would cause the drag-drop
        // interaction to unexpectedly end in a confusing way.
        const isBeingDragged =
          (this.dragState?.type === 'interaction' ||
            this.dragState?.type === 'interactionStart' ||
            this.dragState?.type === 'interactionEnd') &&
          this.dragState.id === i.id;
        if (isWithinViewport || isBeingDragged) {
          visibleInteractions.push(i);
        }
      }
      return visibleInteractions;
    },
    viewportWidthSeconds(): number {
      const secondsPerEm = 0.5 / this.zoomTransform.k;
      return this.viewportWidthEm * secondsPerEm;
    },
    viewportStart(): number {
      return this.zoomTransform.t;
    },
    viewportEnd(): number {
      return this.viewportStart + this.viewportWidthSeconds;
    },
    positionForTimeMarker(): string {
      return `${this.secondsToTimelinePercentage(this.currentTime)}%`;
    },
    axisScale(): number[] {
      const points = 25;
      const start = this.viewportStart;
      const end = this.viewportEnd;
      const scale: number[] = [];
      for (let i = 0; i < points; i++) {
        scale.push(start + ((end - start) / points) * i);
      }
      scale.push(end);
      return scale;
    },
  },
  methods: {
    iconForInteraction,
    printInteractionType,
    $gettext,
    secondsToTimelinePercentage(seconds: number): number {
      const deltaT = seconds - this.viewportStart;
      return (deltaT / this.viewportWidthSeconds) * 100;
    },
    interactionMarkerStyle(
      interaction: Interaction
    ): Partial<CSSStyleDeclaration> {
      return {
        left: `${this.secondsToTimelinePercentage(interaction.startTime)}%`,
      };
    },
    zoom(delta: number, time?: number) {
      if (!time) {
        time = (this.viewportStart + this.viewportEnd) / 2;
      }
      // Save information needed to recalculate 't' (translate) parameter after zoom
      const viewportStart0 = this.zoomTransform.t;
      const viewportWidth0 = this.viewportWidthSeconds;

      // Calculate new zoom level
      const exp = Math.exp(delta);
      const newK = this.zoomTransform.k * exp;
      // Prevent zooming too far in/out
      this.zoomTransform.k = Math.max(0.005, Math.min(5, newK));

      // Calculate new translation in order to keep the point under the mouse
      // cursor stationary.
      // viewportWidthSeconds is recalculated (because it's a computed property)
      // after zoomTransform.k is modified in the previous block of code..
      const viewportWidth1 = this.viewportWidthSeconds;
      const viewportStart1 =
        time - ((time - viewportStart0) / viewportWidth0) * viewportWidth1;
      // Prevent panning before video start or after video end
      this.zoomTransform.t = this.constrainZoomTranslate(viewportStart1);
    },
    onWheel(event: WheelEvent) {
      event.preventDefault(); // Prevent scrolling page up/down
      const t = this.xCoordinateToTime(event.clientX);
      const delta = -event.deltaY / 1000;
      this.zoom(delta, t);
    },
    constrainZoomTranslate(t: number): number {
      return Math.max(0, Math.min(this.videoMetadata.length, t));
    },
    pixelsToEm(pixels: number): number {
      const timeline = this.$refs.timelineAxis;
      if (!timeline) {
        return 1;
      }
      return (
        getEmValueFromElement(this.$refs.timelineAxis as HTMLElement, pixels) ??
        NaN
      );
    },
    recomputeViewportWidth() {
      const axis = this.$refs.timelineAxis as HTMLElement | null;
      if (!axis) {
        console.warn('recomputeViewportWidth: timelineAxis is null');
        return;
      }
      const rect = axis.getBoundingClientRect();
      const widthInEm = this.pixelsToEm(rect.width);
      this.viewportWidthEm = widthInEm;
    },
    // Format video timestamp for timeline axis. e.g. 1m30s becomes 00;01;30;00
    formatVideoTimestamp(timestampSeconds: number): string {
      let hours = 0,
        minutes = 0,
        seconds = 0,
        centiseconds = 0;
      while (timestampSeconds >= 3600) {
        hours++;
        timestampSeconds -= 3600;
      }
      while (timestampSeconds >= 60) {
        minutes++;
        timestampSeconds -= 60;
      }
      while (timestampSeconds >= 1) {
        seconds++;
        timestampSeconds -= 1;
      }
      centiseconds = Math.floor(timestampSeconds * 100);
      function twoDigits(n: number): string {
        return n.toLocaleString('de-DE', {
          minimumIntegerDigits: 2,
          maximumFractionDigits: 0,
        });
      }
      return `${twoDigits(hours)};${twoDigits(minutes)};${twoDigits(
        seconds
      )};${twoDigits(centiseconds)}`;
    },
    pixelsToSeconds(pixels: number): number {
      const rect = (
        this.$refs.timelineAxis as HTMLElement
      ).getBoundingClientRect();
      const fraction = pixels / rect.width;
      return fraction * this.viewportWidthSeconds;
    },
    /**
     * Convert the x coordinate in pixels on the timeline to a time in seconds
     * in the video, clamped between 0 and the video's length.
     */
    xCoordinateToTime(clientX: number): number {
      const rect = (
        this.$refs.timelineAxis as HTMLElement
      ).getBoundingClientRect();
      const x = clientX - rect.left; //x position within the element.
      const xFraction = x / rect.width;
      const time = this.viewportStart + xFraction * this.viewportWidthSeconds;
      const clampedTime = Math.min(
        this.videoMetadata.length,
        Math.max(0, time)
      );
      return clampedTime;
    },
    timelineInteractionStyle(interaction: Interaction): StyleValue {
      const startDeltaT = interaction.startTime - this.viewportStart;
      const startPercent = (startDeltaT / this.viewportWidthSeconds) * 100;
      const endDeltaT = interaction.endTime - this.viewportStart;
      const endPercent = (endDeltaT / this.viewportWidthSeconds) * 100;
      return {
        left: `${startPercent}%`,
        width: `${endPercent - startPercent}%`,
      };
    },
    onClickInteraction(interaction: Interaction) {
      console.log('onClickInteraction');
      this.$emit('clickInteraction', interaction);
    },
    onPointerDownInteraction(event: PointerEvent, interaction: Interaction) {
      console.log('onPointerDownInteraction');
      if (event.button !== 0) {
        // Prevent unintentionally selecting interaction when dragging to
        // scroll around in the timeline
        return;
      }
      const interactionLength = interaction.endTime - interaction.startTime;
      this.dragState = {
        type: 'interaction',
        id: interaction.id,
        mouseStartPos: [event.clientX, event.clientY],
        interactionStartTime: interaction.startTime,
        interactionDuration: interactionLength,
      };
      (event.target as HTMLElement).setPointerCapture(event.pointerId);
      this.editor!.selectInteraction(interaction.id);
    },
    onPointerMoveInteraction(event: PointerEvent, interaction: Interaction) {
      if (
        this.dragState?.type === 'interaction' &&
        this.dragState.id === interaction.id
      ) {
        const mouseDx = event.clientX - this.dragState.mouseStartPos[0];
        const rect = (
          this.$refs.timelineAxis as HTMLElement
        ).getBoundingClientRect();
        const secondsPerPixel = this.viewportWidthSeconds / rect.width;
        const dSeconds = mouseDx * secondsPerPixel;
        const seconds = this.dragState.interactionStartTime + dSeconds;
        // Prevent from dragging so far that the endTime > video length
        const maxTime =
          this.videoMetadata.length - this.dragState.interactionDuration;
        const secondsClamped = Math.max(0, Math.min(maxTime, seconds));
        const id = this.dragState.id;
        this.editor?.dragInteractionTimeline(id, secondsClamped);
      }
    },
    onPointerUpInteraction(event: PointerEvent, interaction: Interaction) {
      console.log('onPointerUpInteraction');
      this.dragState = undefined;
      (event.target as HTMLElement).releasePointerCapture(event.pointerId);
    },
    onPointerDownInteractionStart(ev: PointerEvent, interaction: Interaction) {
      this.dragState = {
        type: 'interactionStart',
        id: interaction.id,
        time: interaction.startTime,
      };
      (ev.target as HTMLElement).setPointerCapture(ev.pointerId);
    },
    onPointerDownInteractionEnd(ev: PointerEvent, interaction: Interaction) {
      this.dragState = {
        type: 'interactionEnd',
        id: interaction.id,
        time: interaction.endTime,
      };
      (ev.target as HTMLElement).setPointerCapture(ev.pointerId);
    },
    onPointerMoveInteractionStart(ev: PointerEvent, interaction: Interaction) {
      if (
        this.dragState?.type === 'interactionStart' &&
        this.dragState.id === interaction.id
      ) {
        const dSeconds = this.pixelsToSeconds(ev.movementX);
        this.dragState.time += dSeconds;
        const timeClamped = Math.max(
          0,
          Math.min(interaction.endTime - 1, this.dragState.time)
        );
        // TODO make undoable
        interaction.startTime = timeClamped;
      }
    },
    onPointerMoveInteractionEnd(ev: PointerEvent, interaction: Interaction) {
      if (
        this.dragState?.type === 'interactionEnd' &&
        this.dragState.id === interaction.id
      ) {
        const dSeconds = this.pixelsToSeconds(ev.movementX);
        this.dragState.time += dSeconds;
        const secondsClamped = Math.max(
          interaction.startTime + 1,
          Math.min(this.videoMetadata.length, this.dragState.time)
        );
        // TODO make undoable
        interaction.endTime = secondsClamped;
      }
    },
    onPointerUpInteractionStart(ev: PointerEvent, interaction: Interaction) {
      this.dragState = undefined;
      (ev.target as HTMLElement).releasePointerCapture(ev.pointerId);
    },
    onPointerUpInteractionEnd(ev: PointerEvent, interaction: Interaction) {
      this.dragState = undefined;
      (ev.target as HTMLElement).releasePointerCapture(ev.pointerId);
    },
    onPointerDownAxis(e: PointerEvent) {
      const time = this.xCoordinateToTime(e.clientX);
      this.$emit('timelineSeek', time);
    },
    onDragStartTimeMarker(e: DragEvent) {
      console.log('dragstart time marker');
      this.dragState = { type: 'timeMarker' };
    },
    onDragOverTimeline(e: DragEvent) {
      if (this.dragState?.type === 'timeMarker') {
        e.preventDefault(); // Stop ghost image from flying back after drop
        const time = this.xCoordinateToTime(e.clientX);
        this.emitTimelineSeekThrottled(time);
      }
    },
    emitTimelineSeekThrottled: throttle(function emitTimelineSeek(
      this: ComponentPublicInstance,
      time: number
    ) {
      this.$emit('timelineSeek', time);
    },
    100),
  },
});
</script>

<template>
  <div ref="root" class="timeline-root" @dragover.capture="onDragOverTimeline">
    <div
      class="timeline-axis"
      ref="timelineAxis"
      @pointerdown="onPointerDownAxis"
    >
      <div class="tick" v-for="(point, index) in axisScale" :key="index">
        <div class="tick-label">
          {{ index % 5 === 0 ? formatVideoTimestamp(point) : ' ' }}
        </div>
        <div
          class="tick-line"
          :class="{
            major: index % 5 === 0,
          }"
        ></div>
      </div>
      <button
        v-for="interaction in visibleInteractions"
        :key="interaction.id"
        class="interaction-marker"
        :style="interactionMarkerStyle(interaction)"
        @pointerdown.stop
        @click.stop="
          onClickInteraction(interaction);
          $emit('timelineSeek', interaction.startTime + 0.1);
        "
      ></button>
    </div>
    <div
      class="timeline"
      :class="{ 'pan-timeline': this.dragState?.type === 'panTimeline' }"
      ref="timeline"
      @pointerdown.self="onPointerDownAxis"
      @wheel="onWheel"
      @contextmenu.prevent
    >
      <div class="timeline-interactions">
        <div
          v-for="interaction in visibleInteractions"
          :key="interaction.id"
          class="timeline-interaction"
          :class="{
            selected: selectedInteractionId === interaction.id,
          }"
          :style="timelineInteractionStyle(interaction)"
        >
          <div class="overflow-container">
            <button
              type="button"
              class="button select-interaction"
              :class="iconForInteraction(interaction)"
              @click.stop="onClickInteraction(interaction)"
              @pointerdown="onPointerDownInteraction($event, interaction)"
              @pointermove="onPointerMoveInteraction($event, interaction)"
              @pointerup="onPointerUpInteraction($event, interaction)"
            >
              <span class="timeline-interaction-label">
                {{ printInteractionType(interaction) }}
              </span>
            </button>
            <!--  The delete button is hidden (v-if) unless you can see the end
            of the interaction within the timeline viewport, because otherwise,
            you can 'tab' over to it while it's off screen, and that causes a
            visual glitch, as the button is automatically scrolled into view. -->
            <button
              type="button"
              class="small-button trash delete-interaction"
              :title="$gettext('Löschen')"
              @click="$emit('deleteInteraction', interaction.id)"
              v-if="interaction.endTime < viewportEnd"
            ></button>
          </div>
          <div
            class="interaction-drag-handle start"
            @pointerdown="onPointerDownInteractionStart($event, interaction)"
            @pointermove="onPointerMoveInteractionStart($event, interaction)"
            @pointerup="onPointerUpInteractionStart($event, interaction)"
          ></div>
          <div
            class="interaction-drag-handle end"
            @pointerdown="onPointerDownInteractionEnd($event, interaction)"
            @pointermove="onPointerMoveInteractionEnd($event, interaction)"
            @pointerup="onPointerUpInteractionEnd($event, interaction)"
          ></div>
        </div>
      </div>

      <div
        class="time-marker"
        draggable="true"
        @dragstart="onDragStartTimeMarker"
        :style="{
          left: positionForTimeMarker,
        }"
      />
    </div>
    <div style="white-space: pre-wrap; display: none">
      {{
        {
          zoomTransform,
          viewportWidthEm,
          viewportWidthSeconds,
          viewportStart,
          viewportEnd,
          dragState: dragState,
        }
      }}
    </div>
  </div>
</template>

<style scoped lang="scss">
.timeline-root {
  position: relative;
  margin-left: 1em;
  margin-right: 1em;
}
.timeline {
  position: relative;
  width: 100%;
  height: 5em;
  border: 1px solid black;
  cursor: text;

  &.pan-timeline,
  &.pan-timeline * {
    cursor: move !important;
  }

  .time-marker {
    position: absolute;
    z-index: 2;
    top: 0;
    height: 5em;
    width: 2px;
    background-color: blue;
    &::before {
      content: '';
      position: absolute;
      top: -1em;
      height: 1em;
      width: 1em;
      background-color: blue;
    }
  }

  .timeline-interactions {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;

    .timeline-interaction {
      position: absolute;
      height: 100%;
      background: var(--content-color-20);
      cursor: default;

      .overflow-container {
        position: absolute;
        height: 100%;
        width: 100%;
        overflow: hidden;
      }

      button.select-interaction {
        /* CSS Reset for button styles */
        padding: 0;
        font: inherit;
        color: inherit;
        background-color: transparent;
        cursor: pointer;
        box-sizing: border-box;
        // Remove stud.ip 'button' class margin
        margin: 0;

        height: 100%;
        width: 100%;
        min-width: 0;
        overflow: hidden;

        box-sizing: border-box;
        border: 2px solid darkgrey;

        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        &::before {
          position: absolute;
          left: 50%;
          top: calc(50% - 16px);
        }

        .timeline-interaction-label {
          width: 100%;
          overflow: hidden;
          text-overflow: ellipsis;
          text-align: center;
        }
      }

      &.selected {
        z-index: 1;
        button.select-interaction {
          border: 2px solid black;
          &:focus-visible {
            outline: none;
            border-style: dashed;
          }
        }
      }
      &:not(.selected) {
        &:has(button:focus-visible) {
          z-index: 2;
        }
        button.select-interaction {
          &:focus-visible {
            outline: none;
            border: 2px dashed var(--content-color-80);
          }
        }
      }

      &:hover {
        background: var(--content-color-40);
      }

      .interaction-drag-handle {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 5px;
        cursor: ew-resize;
        &.start {
          left: -3px;
        }
        &.end {
          right: -4px;
        }
      }

      button.delete-interaction {
        position: absolute;
        top: 0;
        right: 0;
        padding: 0;
        display: none;
      }
      &.selected button.delete-interaction {
        display: block;
      }
    }
  }
}

.timeline-axis {
  position: relative;
  top: 0;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1em;
  cursor: text;
  user-select: none;
  .tick {
    position: relative;
    .tick-label {
      position: absolute;
      top: -1.5em;
      transform: translateX(-50%);
      text-align: center;
      white-space: pre;
    }
    .tick-line {
      width: 1px;
      border-left: thin solid lightgray;
      height: 1em;
      &.major {
        height: 1.5em;
      }
    }
  }
  .interaction-marker {
    /* CSS Reset for button styles */
    padding: 0;
    border: none;
    font: inherit;
    color: inherit;
    cursor: pointer;
    box-sizing: border-box;

    $radius: 0.85em;
    position: absolute;
    bottom: 0.5em;
    shape-outside: circle();
    clip-path: circle();
    width: $radius;
    height: $radius;
    background-color: black;
    &::after {
      display: block;
      content: ' ';
      shape-outside: circle();
      clip-path: circle();
      width: calc($radius - 2px);
      height: calc($radius - 2px);
      transform: translate(1px, 0px);
      background-color: var(--content-color-20);
    }

    &:hover {
      &::after {
        background-color: black;
      }
    }
    // Ensure that an outline is drawn when element is focused with the keyboard
    &:focus-visible {
      clip-path: unset;
      &::after {
        border: 2px solid black;
      }
    }
  }
}
</style>
