<template>
  <div style="display: flex">
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
    <pre>{{ { dragState } }}</pre>
  </div>
</template>

<script setup lang="ts">
import { computed, defineProps, onMounted, PropType, ref, watch } from 'vue';
import { FindTheWordsTask } from '@/models/TaskDefinition';
import { v4 } from 'uuid';

type DragState =
  | {
      type: 'drag';
      dragId: string;
      pointerStartPos: [number, number];
      startCell: [number, number];
      pointerCurrentPos: [number, number];
      currentCell: [number, number];
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
    updateMatrix();
    drawMatrix();
  },
  { deep: true }
);

// State
let matrix: string[][] = [];
const dragState = ref<DragState>();

// Computed properties
const words = computed(() => {
  if (props.task?.words) {
    return props.task.words
      .split(',')
      .filter((word) => word.trim())
      .map((word) => word.trim());
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

const cellSize = 32;
const gridSize = 16;

function updateMatrix() {
  matrix = [];
  for (let x = 0; x < gridSize; x++) {
    matrix[x] = [];
    for (let y = 0; y < gridSize; y++) {
      matrix[x][y] = randomLetter();
    }
  }
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
    for (let x = 0; x < gridSize; x++) {
      for (let y = 0; y < gridSize; y++) {
        ctx.strokeRect(x * cellSize, y * cellSize, cellSize, cellSize);
        ctx.fillText(
          matrix[x][y],
          x * cellSize + cellSize / 2,
          y * cellSize + cellSize / 2
        );
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
  const cellX = Math.floor(x / cellSize);
  const cellY = Math.floor(y / cellSize);

  dragState.value = {
    type: 'drag',
    dragId: v4(),
    pointerStartPos: [x, y],
    startCell: [cellX, cellY],
    pointerCurrentPos: [x, y],
    currentCell: [cellX, cellY],
  };

  fillCell(cellX, cellY);
}

function onPointermoveCanvas(event: PointerEvent) {
  if (dragState.value) {
    console.log('Pointer Move');
    const canvas = document.getElementById('c') as HTMLCanvasElement;
    const rect = canvas.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    const cellX = Math.floor(x / cellSize);
    const cellY = Math.floor(y / cellSize);

    dragState.value = {
      type: 'drag',
      dragId: v4(),
      pointerStartPos: dragState.value.pointerStartPos,
      startCell: dragState.value.startCell,
      pointerCurrentPos: [x, y],
      currentCell: [cellX, cellY],
    };

    fillCell(cellX, cellY);
  }
}

function onPointerupCanvas(event: PointerEvent) {
  console.log('Pointer Up');
  const canvas = document.getElementById('c') as HTMLCanvasElement;
  const rect = canvas.getBoundingClientRect();
  const x = event.clientX - rect.left;
  const y = event.clientY - rect.top;
  const cellX = Math.floor(x / cellSize);
  const cellY = Math.floor(y / cellSize);

  if (dragState.value) {
    dragState.value = {
      type: 'drag',
      dragId: v4(),
      pointerStartPos: dragState.value.pointerStartPos,
      startCell: dragState.value.startCell,
      pointerCurrentPos: [x, y],
      currentCell: [cellX, cellY],
    };

    fillCell(cellX, cellY);

    dragState.value = undefined;
  }
}

function fillCell(xCell: number, yCell: number) {
  if (xCell < 0 || xCell > gridSize - 1 || yCell < 0 || yCell > gridSize - 1) {
    return;
  }

  const canvas = document.getElementById('c') as HTMLCanvasElement;
  const ctx = canvas.getContext('2d');

  if (ctx) {
    ctx.fillStyle = 'rgba(140, 180, 255, 0.34)';
    ctx.fillRect(xCell * cellSize, yCell * cellSize, cellSize, cellSize);
  }
}

// Call the function to draw the matrix
onMounted(() => {
  updateMatrix();
  drawMatrix();
});
</script>

<style scoped></style>
