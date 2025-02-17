<template>
  <span>Find The Hotspots - Viewer</span><br />
  <ImageWithHotspots :hotspots="task.hotspots" :image="task.image" />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FindTheHotspotsTask } from '@/models/FindTheHotspotsTask';
import ImageWithHotspots from '@/components/findTheHotspots/ImageWithHotspots.vue';

export default defineComponent({
  name: 'FindTheHotspotsViewer',
  components: { ImageWithHotspots },
  props: {
    task: { type: Object as PropType<FindTheHotspotsTask>, required: true },
  },
  emits: ['updateAttempt'],
  data() {
    return {};
  },
  methods: {},
  computed: {},
});
</script>

<style scoped></style>
