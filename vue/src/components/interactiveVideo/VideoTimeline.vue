<template>
  <div class="timeline-root">
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
  },
});
</script>
<style scoped>
.timeline {
  width: 100%;
  height: 5em;
  border: 1px solid black;
}
.timeline-root {
  position: relative;
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
