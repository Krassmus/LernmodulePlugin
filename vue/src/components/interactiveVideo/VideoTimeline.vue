<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { VideoMetadata } from '@/components/interactiveVideo/events';

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
  },
  computed: {
    positionForTimeMarker(): string {
      return `${(this.currentTime / this.videoMetadata.length) * 100}%`;
    },
    axisScale(): number[] {
      const points = 26;
      const start = 0;
      const end = this.videoMetadata.length;
      const scale: number[] = [];
      for (let i = 0; i < points; i++) {
        scale.push(start + ((end - start) / points) * i);
      }
      return scale;
    },
  },
  methods: {
    // mm:ss
    formatVideoTimestamp(seconds: number): string {
      const date = new Date(0);
      date.setSeconds(seconds);
      const timeString = date.toISOString().substring(14, 19);
      return timeString;
    },
  },
});
</script>

<template>
  <div class="timeline-root">
    <div class="timeline-axis">
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
    <div class="timeline">
      The timeline.
      <div>
        Length:
        <pre>{{ videoMetadata.length }}</pre>
      </div>
    </div>
    <div
      class="time-marker"
      :style="{
        left: positionForTimeMarker,
      }"
    />
  </div>
</template>

<style scoped lang="scss">
.timeline-root {
  position: relative;
  margin-left: 1em;
  margin-right: 1em;
}
.timeline-axis {
  top: 0;
  display: flex;
  justify-content: space-between;
  .tick {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    width: 1em;
    .tick-label {
      white-space: pre;
      align-self: start;
    }
    .tick-line {
      align-self: start;
      width: 1px;
      border-left: thin solid lightgray;
      height: 1em;
      &.major {
        height: 1.5em;
      }
    }
  }
}
.timeline {
  width: 100%;
  height: 5em;
  border: 1px solid black;
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
</style>
