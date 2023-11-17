<script lang="ts">
import {
  ComponentPublicInstance,
  defineComponent,
  PropType,
  StyleValue,
} from 'vue';
import { VideoMetadata } from '@/components/interactiveVideo/events';
import { throttle } from 'lodash';
import {
  Interaction,
  InteractiveVideoTask,
  printInteractionType,
} from '@/models/InteractiveVideoTask';
import { $gettext } from '../../language/gettext';

type DragState = { type: 'timeMarker' } | undefined;
export default defineComponent({
  name: 'VideoTimeline',
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
    };
  },
  computed: {
    positionForTimeMarker(): string {
      return `${(this.currentTime / this.videoMetadata.length) * 100}%`;
    },
    axisScale(): number[] {
      const points = 25;
      const start = 0;
      const end = this.videoMetadata.length;
      const scale: number[] = [];
      for (let i = 0; i < points; i++) {
        scale.push(start + ((end - start) / points) * i);
      }
      scale.push(end);
      return scale;
    },
  },
  methods: {
    printInteractionType,
    $gettext,
    // mm:ss
    formatVideoTimestamp(seconds: number): string {
      const date = new Date(0);
      date.setSeconds(seconds);
      const timeString = date.toISOString().substring(14, 19);
      return timeString;
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
      const time = xFraction * this.videoMetadata.length;
      const clampedTime = Math.min(
        this.videoMetadata.length,
        Math.max(0, time)
      );
      return clampedTime;
    },
    timelineInteractionStyle(interaction: Interaction): StyleValue {
      const endTime =
        interaction.type === 'pause'
          ? interaction.startTime + 1
          : interaction.endTime;
      const startPercent =
        (interaction.startTime / this.videoMetadata.length) * 100;
      const endPercent = (endTime / this.videoMetadata.length) * 100;
      const isSelected = interaction.id === this.selectedInteractionId;
      return {
        left: `${startPercent}%`,
        width: `${endPercent - startPercent}%`,
        border: isSelected ? '3px solid black' : undefined,
      };
    },
    onClickInteraction(interaction: Interaction) {
      this.$emit('clickInteraction', interaction);
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
  <div class="timeline-root" @dragover.capture="onDragOverTimeline">
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
    <div class="timeline" @pointerdown.self="onPointerDownAxis">
      <div
        v-for="interaction in task.interactions"
        :key="interaction.id"
        class="timeline-interaction"
        :style="timelineInteractionStyle(interaction)"
        @click.stop="onClickInteraction(interaction)"
      >
        {{ printInteractionType(interaction) }}
        <button
          type="button"
          class="button delete"
          @click="$emit('deleteInteraction', interaction.id)"
        >
          {{ $gettext('Löschen') }}
        </button>
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
  .timeline-interaction {
    position: absolute;
    height: 100%;
    box-sizing: border-box;
    border: 2px solid darkgrey;
    background: #e7ebf1;
    cursor: default;
  }
}
.time-marker {
  position: absolute;
  top: 0;
  height: 5em;
  width: 2px;
  background-color: blue;
}
.time-marker::before {
  content: '';
  position: absolute;
  top: -1em;
  height: 1em;
  width: 1em;
  background-color: blue;
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
