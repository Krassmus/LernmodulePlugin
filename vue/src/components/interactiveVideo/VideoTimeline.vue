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
      const deltaT = this.currentTime - this.viewportStart;
      return `${(deltaT / this.viewportWidthSeconds) * 100}%`;
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
    onWheel(event: WheelEvent) {
      event.preventDefault(); // Prevent scrolling page up/down

      // Save information needed to recalculate 't' (translate) parameter after zoom
      const viewportStart0 = this.zoomTransform.t;
      const viewportWidth0 = this.viewportWidthSeconds;
      const t = this.xCoordinateToTime(event.clientX);

      // Calculate new zoom level
      const dy = -event.deltaY / 1000;
      const exp = Math.exp(dy);
      const newK = this.zoomTransform.k * exp;
      // Prevent zooming too far in/out
      this.zoomTransform.k = Math.max(0.005, Math.min(5, newK));

      // Calculate new translation in order to keep the point under the mouse
      // cursor stationary.
      // viewportWidthSeconds is recalculated (because it's a computed property)
      // after zoomTransform.k is modified in the previous block of code..
      const viewportWidth1 = this.viewportWidthSeconds;
      const viewportStart1 =
        t - ((t - viewportStart0) / viewportWidth0) * viewportWidth1;
      // Prevent panning before video start or after video end
      this.zoomTransform.t = this.constrainZoomTranslate(viewportStart1);
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
      this.$emit('clickInteraction', interaction);
    },
    onDragStartInteraction(event: DragEvent, interaction: Interaction) {
      event.dataTransfer!.setDragImage(event.target as Element, -99999, -99999);
      const interactionLength = interaction.endTime - interaction.startTime;
      this.dragState = {
        type: 'interaction',
        id: interaction.id,
        mouseStartPos: [event.clientX, event.clientY],
        interactionStartTime: interaction.startTime,
        interactionDuration: interactionLength,
      };
      this.editor!.selectInteraction(interaction.id);
    },
    onDragEndInteraction(event: DragEvent, interaction: Interaction) {
      this.dragState = undefined;
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
      } else if (this.dragState?.type === 'interaction') {
        const mouseDx = e.clientX - this.dragState.mouseStartPos[0];
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
      @pointerdown.capture="onPointerDownAxis"
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
          v-for="interaction in task.interactions"
          :key="interaction.id"
          class="timeline-interaction button"
          tabindex="0"
          :class="{
            selected: selectedInteractionId === interaction.id,
            [iconForInteraction(interaction)]: true,
          }"
          :style="timelineInteractionStyle(interaction)"
          :draggable="!dragState"
          @click.stop="onClickInteraction(interaction)"
          @dragstart="onDragStartInteraction($event, interaction)"
          @dragend="onDragEndInteraction($event, interaction)"
        >
          <div class="timeline-interaction-label">
            {{ printInteractionType(interaction) }}
          </div>
          <button
            type="button"
            class="small-button trash delete-interaction"
            @click="$emit('deleteInteraction', interaction.id)"
            :title="$gettext('Löschen')"
          ></button>
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
      box-sizing: border-box;
      border: 2px solid darkgrey;
      background: var(--content-color-20);
      cursor: default;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;

      .timeline-interaction-label {
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: center;
      }

      &::before {
        position: absolute;
        left: 50%;
        top: calc(50% - 16px);
      }

      &.selected {
        border: 3px solid black;
        z-index: 1;
      }

      &:focus-within:not(.selected) {
        border: 3px dashed lightgrey;
        z-index: 2;
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
}
</style>
