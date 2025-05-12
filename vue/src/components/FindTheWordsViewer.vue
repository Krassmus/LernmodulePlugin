<template>
  <div style="display: flex">
    <div class="stud5p-task">
      <div style="display: flex; flex-direction: row; gap: 1em">
        <canvas
          id="c"
          width="576"
          height="576"
          @pointerdown.stop="onPointerdownCanvas($event)"
          @pointermove.stop="onPointermoveCanvas($event)"
          @pointerup.stop="onPointerupCanvas($event)"
        />

        <div style="display: flex; flex-direction: column">
          <p style="margin: 0; font-weight: bold">
            {{ $gettext('Wörter') }}
          </p>
          <span v-for="word in words" :key="word">{{ word }}</span>
        </div>
      </div>
    </div>
    <pre>{{ { dragState } }}</pre>
  </div>
</template>

<script setup lang="ts">
import { computed, defineProps, onMounted, PropType, ref, watch } from 'vue';
import { FindTheWordsTask } from '@/models/TaskDefinition';
import { v4 } from 'uuid';
import { $gettext } from '@/language/gettext';

const WordSearch = require('@blex41/word-search');

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
let selectedCells: boolean[][] = [];
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
      .filter((char) => char.match(/[0-9\p{L}]/u));
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

const cellSize = 48;
const gridSize = 12;

function updateMatrix() {
  for (let x = 0; x < gridSize; x++) {
    selectedCells[x] = [];
    for (let y = 0; y < gridSize; y++) {
      selectedCells[x][y] = false;
    }
  }

  const options = {
    cols: gridSize,
    rows: gridSize,
    disabledDirections: [],
    dictionary: words.value,
    maxWords: words.value.length,
    backwardsProbability: 0.3,
    upperCase: true,
    diacritics: true,
  };

  // Create a new puzzle
  const ws = new WordSearch(options);

  if (ws.words.length !== words.value.length) {
    console.log('Es konnten nicht alle Wörter untergebracht werden.');
  }

  const allCoordinates: { x: number; y: number }[] = [];

  ws.words.forEach((word: any) => {
    allCoordinates.push(...word.path);
  });

  matrix = [];
  for (let x = 0; x < gridSize; x++) {
    matrix[x] = [];
    for (let y = 0; y < gridSize; y++) {
      if (allCoordinates.some((coord) => coord.x === x && coord.y === y)) {
        matrix[x][y] = ws.grid[y][x];
      } else {
        matrix[x][y] = randomLetter();
      }
    }
  }
}

// Function to draw the matrix of letters
function drawMatrix() {
  const canvas = document.getElementById('c') as HTMLCanvasElement;
  const ctx = canvas.getContext('2d');
  if (ctx) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.font = 'bold 26px calibri';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    for (let x = 0; x < gridSize; x++) {
      for (let y = 0; y < gridSize; y++) {
        if (selectedCells[x][y]) {
          fillCell(x, y);
        }
        ctx.strokeStyle = 'gainsboro';
        ctx.strokeRect(x * cellSize, y * cellSize, cellSize, cellSize);
        ctx.fillStyle = '#404040';
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

  selectedCells[cellX][cellY] = true;
  drawMatrix();
}

function onPointermoveCanvas(event: PointerEvent) {
  if (dragState.value) {
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

    selectedCells[cellX][cellY] = true;
    drawMatrix();
  }
}

function onPointerupCanvas(event: PointerEvent) {
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

    selectedCells[cellX][cellY] = true;
    dragState.value = undefined;
    drawMatrix();
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
