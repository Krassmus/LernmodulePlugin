<template>
  <ImageWithHotspots :hotspots="task.hotspots" :image="task.image" />
</template>

<script setup lang="ts">
import { defineProps, PropType, computed, provide, ref } from 'vue';
import { FindTheHotspotsTask } from '@/models/FindTheHotspotsTask';
import ImageWithHotspots from '@/components/findTheHotspots/ImageWithHotspots.vue';
import FeedbackElement from '@/components/FeedbackElement.vue';
import { findTheHotspotsViewerStateSymbol } from '@/components/findTheHotspots/findTheHotspotsViewerState';

const props = defineProps({
  task: {
    type: Object as PropType<FindTheHotspotsTask>,
    required: true,
  },
});

provide(findTheHotspotsViewerStateSymbol, {
  clickHotspot,
  clickBackground,
});

const count = ref<number>(0);
const showResults = computed(() => true);
const maxPoints = computed(
  () => props.task.hotspots.filter((hotspot) => hotspot.correct).length
);

function clickHotspot(id: string | undefined) {
  console.log('Viewer: Clicked hotspot with id', id);
  const hotspot = props.task.hotspots.find((hotspot) => hotspot.uuid === id);
  if (hotspot && hotspot.correct) {
    count.value++;
  }
}

function clickBackground() {
  console.log('Viewer: Clicked background');
}
</script>

<style scoped></style>
