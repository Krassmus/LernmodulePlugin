<template>
  <div class="stud5p-task">
    <div>
      <p style="margin: 0">Words:</p>
      <span v-for="word in words" :key="word">{{ word }}</span>
    </div>

    <div>
      <p style="margin: 0">Alphabet:</p>
      <span v-for="letter in alphabet" :key="letter">{{ letter }}</span>
    </div>

    <canvas
      id="c"
      width="640"
      height="640"
      @pointerdown.stop="onPointerdownCanvas($event)"
      @pointermove.stop="onPointermoveCanvas($event)"
      @pointerup.stop="onPointerupCanvas($event)"
    />
  </div>

  <pre>{{ dragState }}</pre>
</template>

<script setup lang="ts">
import { computed, defineProps, onMounted, PropType, ref, watch } from 'vue';
import { FindTheWordsTask } from '@/models/TaskDefinition';
import { v4 } from 'uuid';

type DragState =
  | {
      type: 'drag';
      dragId: string;
      pointerStartPos: [number, number]; // clientX, clientY
    }
  | undefined;

const props = defineProps({
  task: {
    type: Object as PropType<FindTheWordsTask>,
    required: true,
  },
});

watch(
  () => props.task,
  () => {
    drawMatrix();
  },
  { deep: true }
);

// State
const dragState = ref<DragState>();

// Computed properties
const words = computed(() => {
  if (props.task?.words) {
    return props.task.words.split(',');
  }
  return [];
});

const alphabet = computed(() => {
  if (props.task?.alphabet) {
    return props.task.alphabet
      .toUpperCase()
      .split('')
      .filter((char) => char.match(/\p{L}/u));
  }
  return [];
});

function randomLetter() {
  if (alphabet.value.length > 0) {
    const randomIndex = Math.floor(Math.random() * alphabet.value.length);
    return alphabet.value[randomIndex];
  }
  return '';
}

// Function to draw the matrix of letters
function drawMatrix() {
  const canvas = document.getElementById('c') as HTMLCanvasElement;
  const ctx = canvas.getContext('2d');
  if (ctx) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.font = 'bold 24px serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    for (let x = 0; x < 10; x++) {
      for (let y = 0; y < 10; y++) {
        ctx.strokeRect(x * 64, y * 64, 64, 64);
        ctx.fillText(randomLetter(), x * 64 + 32, y * 64 + 32);
      }
    }
  }
}

function onPointerdownCanvas(event: PointerEvent) {
  console.log('Pointer Down');
  const canvas = document.getElementById('c') as HTMLCanvasElement;
  const rect = canvas.getBoundingClientRect();
  const x = event.clientX - rect.left;
  const y = event.clientY - rect.top;
  const cellX = Math.floor(x / 64);
  const cellY = Math.floor(y / 64);
  console.log([cellX, cellY]);

  dragState.value = {
    type: 'drag',
    dragId: v4(),
    pointerStartPos: [event.clientX, event.clientY],
  };
}

function onPointermoveCanvas(event: PointerEvent) {
  if (dragState.value) {
    console.log('Pointer Move');
    const canvas = document.getElementById('c') as HTMLCanvasElement;
    const rect = canvas.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    const cellX = Math.floor(x / 64);
    const cellY = Math.floor(y / 64);
    console.log([cellX, cellY]);
  }
}

function onPointerupCanvas(event: PointerEvent) {
  console.log('Pointer Up');
  const canvas = document.getElementById('c') as HTMLCanvasElement;
  const ctx = canvas.getContext('2d');

  if (dragState.value) {
    dragState.value = undefined;
  }
}

// Call the function to draw the matrix
onMounted(() => {
  drawMatrix();
});
</script>

<style scoped></style>
