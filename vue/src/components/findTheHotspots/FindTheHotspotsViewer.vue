<template>
  <div class="stud5p-task">
    <div style="position: relative">
      <ImageWithHotspots :hotspots="task.hotspots" :image="task.image" />
      <span
        v-for="(click, index) in clickHistory"
        :key="index"
        class="click-indicator"
        :class="{
          pulse: index === clickHistory.length - 1,
          correct: click.correct,
          incorrect: !click.correct,
        }"
        :style="{
          left: `${click.x - 16}px`,
          top: `${click.y - 16}px`,
        }"
      >
        {{ click.correct ? '✔' : '✖' }}
      </span>
    </div>
    <div class="feedback-and-button-container">
      <FeedbackElement
        v-if="showResults"
        :achievedPoints="points"
        :maxPoints="maxPoints"
        :feedback="task.feedback"
        :resultMessage="resultMessage"
      />

      <div class="button-panel">
        <button
          v-if="showRetryButton"
          v-text="task.strings.retryButton"
          @click="onClickRetry"
          type="button"
          class="stud5p-button"
        />
      </div>
    </div>
  </div>
  <pre :style="{ flexBasis: '50%', flexGrow: 0, flexShrink: 0 }">{{
    {
      points,
      clicks,
      maxPoints,
      clickedHotspots,
      editable,
      clickHistory,
    }
  }}</pre>
</template>

<script setup lang="ts">
import {
  defineProps,
  PropType,
  computed,
  provide,
  ref,
  defineEmits,
} from 'vue';
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

defineEmits(['updateAttempt']);

interface Click {
  correct: boolean;
  x: number;
  y: number;
}

const clickHistory = ref<Click[]>([]);

const points = ref<number>(0);
const clicks = ref<number>(0);
const clickedHotspots = ref<string[]>([]);

const showResults = computed(() => points.value > 0);

const maxPoints = computed(
  () => props.task.hotspots.filter((hotspot) => hotspot.correct).length
);

const editable = computed(
  () =>
    (points.value < maxPoints.value && clicks.value < maxPoints.value) || true
);

const resultMessage = computed(() => {
  let result = props.task.strings.resultMessage.replace(
    ':correct',
    points.value.toString()
  );
  result = result.replace(':total', maxPoints.value.toString());
  return result;
});

const showRetryButton = computed(() => {
  return !editable.value;
});

function clickHotspot(id: string | undefined, x: number, y: number) {
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

  const click = {
    correct: hotspot.correct,
    x: x,
    y: y,
  };
  clickHistory.value.push(click);
}

function clickBackground(x: number, y: number) {
  if (!editable.value) return;
  console.log('Viewer: Clicked background');
  clicks.value++;

  const click = {
    correct: false,
    x: x,
    y: y,
  };
  clickHistory.value.push(click);
}

function onClickRetry() {
  points.value = 0;
  clicks.value = 0;
  clickedHotspots.value = [];
}
</script>

<style scoped>
.click-indicator {
  position: absolute;
  cursor: default;

  width: 32px;
  height: 32px;
  line-height: 32px;
  font-size: 16px;
  border-radius: 50%;
  text-align: center;
  pointer-events: none;

  box-shadow: 0 0 0.25em 0 rgba(0, 0, 0, 0.5);

  &.correct {
    color: #39692e;
    background: #d1e2ce;
  }

  &.incorrect {
    color: #c33f62;
    background: #e6ced1;
  }
}

@keyframes pulse {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.2);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

.pulse {
  animation: pulse 0.25s ease-in-out 0s 1;
}
</style>
