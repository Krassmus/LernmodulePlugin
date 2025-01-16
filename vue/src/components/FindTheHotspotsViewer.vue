<template>
  <span>Find The Hotspots - Viewer</span><br />
  <img :src="fileIdToUrl(task.image.file_id)" />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { fileIdToUrl, FindTheHotspotsTask } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'FindTheHotspotsViewer',
  props: {
    task: { type: Object as PropType<FindTheHotspotsTask>, required: true },
  },
  emits: ['updateAttempt'],
  data() {
    return {};
  },
  methods: { fileIdToUrl },
  computed: {},
});
</script>

<style scoped></style>
