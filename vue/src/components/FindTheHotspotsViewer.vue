<template>
  <span>Find The Hotspots - Viewer</span><br />
  <LazyImage
    v-if="task.image.file_id"
    :src="fileIdToUrl(task.image.file_id)"
    :alt="task.image.altText"
    @click="handleClick"
    class="image"
  />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { fileIdToUrl, FindTheHotspotsTask } from '@/models/TaskDefinition';
import LazyImage from '@/components/LazyImage.vue';

export default defineComponent({
  name: 'FindTheHotspotsViewer',
  components: { LazyImage },
  props: {
    task: { type: Object as PropType<FindTheHotspotsTask>, required: true },
  },
  emits: ['updateAttempt'],
  data() {
    return {};
  },
  methods: {
    fileIdToUrl,

    handleClick(event: MouseEvent) {
      const x = event.offsetX;
      const y = event.offsetY;
      console.log('Image clicked at coordinates:', { x, y });
    },
  },
  computed: {},
});
</script>

<style scoped>
.image {
  border: 1px solid red;
  user-select: none;
}
</style>
