<template>
  <span>Find The Hotspot - Viewer</span><br />
  <img :src="this.task.image.imageUrl" />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FindTheHotspotTask } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'FindTheHotspotViewer',
  props: {
    task: { type: Object as PropType<FindTheHotspotTask>, required: true },
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
