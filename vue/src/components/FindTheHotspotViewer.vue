<template>
  <span>Find The Hotspot - Viewer</span><br />
  <img :src="fileIdToUrl(task.image.file_id)" />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { fileIdToUrl, FindTheHotspotTask } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'FindTheHotspotViewer',
  props: {
    task: { type: Object as PropType<FindTheHotspotTask>, required: true },
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
