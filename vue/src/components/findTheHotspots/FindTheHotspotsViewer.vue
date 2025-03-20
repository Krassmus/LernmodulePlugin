<template>
  <ImageWithHotspots :hotspots="task.hotspots" :image="task.image" />
  <div class="feedback-and-button-container">
    <FeedbackElement
      v-if="showResults"
      :achievedPoints="points"
      :maxPoints="maxPoints"
      :feedback="task.feedback"
      :resultMessage="resultMessage"
    />
  </div>
  <pre :style="{ flexBasis: '50%', flexGrow: 0, flexShrink: 0 }">{{
    { points, clicks, maxPoints, clickedHotspots, editable }
  }}</pre>
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
  isEditable,
});

const points = ref<number>(0);
const clicks = ref<number>(0);
const clickedHotspots = ref<string[]>([]);

const showResults = computed(() => points.value > 0);

const maxPoints = computed(
  () => props.task.hotspots.filter((hotspot) => hotspot.correct).length
);

const editable = computed(
  () => points.value < maxPoints.value && clicks.value < maxPoints.value
);

const resultMessage = computed(() => {
  let result = props.task.strings.resultMessage.replace(
    ':correct',
    points.value.toString()
  );
  result = result.replace(':total', maxPoints.value.toString());
  return result;
});

function isEditable() {
  return points.value < maxPoints.value && clicks.value < maxPoints.value;
}

function clickHotspot(id: string | undefined) {
  if (!editable.value) return;

  console.log('Viewer: Clicked hotspot with id', id);
  if (!id) {
    console.error('No id for clicked hotspot.');
    return;
  }

  const hotspot = props.task.hotspots.find((hotspot) => hotspot.uuid === id);
  if (!hotspot) {
    console.error('No hotspot for given id:', id);
    return;
  }

  if (clickedHotspots.value.includes(id)) {
    console.log('Already clicked this hotspot.');
    return;
  }

  clickedHotspots.value?.push(id);
  clicks.value++;

  if (hotspot.correct) {
    points.value++;
  }
}

function clickBackground() {
  if (!editable.value) return;
  console.log('Viewer: Clicked background');
  clicks.value++;
}
</script>

<style scoped></style>
