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
          <template v-for="word in words" :key="word">
            <s v-if="foundWords.includes(word)"> {{ word }}</s>
            <span v-else>{{ word }}</span>
          </template>
        </div>
      </div>
      <span>{{ score }} of {{ maxScore }} points</span>
    </div>
    <pre>{{ { dragState, foundWords } }}</pre>
  </div>
</template>

<script setup lang="ts">
import { computed, defineProps, onMounted, PropType, ref, watch } from 'vue';
import { FindTheWordsTask } from '@/models/TaskDefinition';
import { v4 } from 'uuid';
import { $gettext } from '@/language/gettext';

const WordSearch = require('@blex41/word-search');

type Direction =
  | 'left'
  | 'right'
  | 'up'
  | 'down'
  | 'up-left'
  | 'up-right'
  | 'down-left'
  | 'down-right'
  | undefined;

type DragState =
  | {
      dragId: string;
      startCell: [number, number];
      currentCell: [number, number];
      direction: Direction;
    }
  | undefined;

// Props
const props = defineProps({
  task: {
    type: Object as PropType<FindTheWordsTask>,
    required: true,
  },
});

// Watchers
watch(
  () => props.task,
  () => {
    initializeGrid();
    drawGrid();
  },
  { deep: true }
);

// State
let grid: string[][] = [];
let selectedCells: boolean[][] = [];
let correctCells: boolean[][] = [];
const foundWords = ref<string[]>([]);
const dragState = ref<DragState>();

// Constants
const cellSize = 48;
const gridSize = 12;

// Lifecycle Hooks
onMounted(() => {
  initializeGrid();
  drawGrid();
});

// Computed properties
const words = computed(() => {
  if (props.task?.words) {
    return props.task.words
      .split(',')
      .filter((word) => word.trim())
      .map((word) => word.trim().toUpperCase());
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

const score = computed(() => {
  return foundWords.value.length;
});

const maxScore = computed(() => {
  return words.value.length;
});

function randomLetter() {
  if (alphabet.value.length > 0) {
    const randomIndex = Math.floor(Math.random() * alphabet.value.length);
    return alphabet.value[randomIndex];
  }
  return '';
}

function resetSelectedCells() {
  selectedCells = [];
  for (let x = 0; x < gridSize; x++) {
    selectedCells[x] = [];
    for (let y = 0; y < gridSize; y++) {
      selectedCells[x][y] = false;
    }
  }
}

function resetCorrectCells() {
  correctCells = [];
  for (let x = 0; x < gridSize; x++) {
    correctCells[x] = [];
    for (let y = 0; y < gridSize; y++) {
      correctCells[x][y] = false;
    }
  }
}

function initializeGrid() {
  resetSelectedCells();
  resetCorrectCells();
  foundWords.value = [];

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

  grid = [];
  for (let x = 0; x < gridSize; x++) {
    grid[x] = [];
    for (let y = 0; y < gridSize; y++) {
      if (allCoordinates.some((coord) => coord.x === x && coord.y === y)) {
        grid[x][y] = ws.grid[y][x];
      } else {
        grid[x][y] = randomLetter();
      }
    }
  }
}

function drawGrid() {
  const canvas = document.getElementById('c') as HTMLCanvasElement;
  const ctx = canvas.getContext('2d');
  if (ctx) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.font = 'bold 26px calibri';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    for (let x = 0; x < gridSize; x++) {
      for (let y = 0; y < gridSize; y++) {
        if (correctCells[x][y]) {
          fillCell(x, y, 'rgb(165,202,158)');
        }
        if (selectedCells[x][y]) {
          fillCell(x, y, 'rgba(140, 180, 255, 0.34)');
        }
        ctx.strokeStyle = 'gainsboro';
        ctx.strokeRect(x * cellSize, y * cellSize, cellSize, cellSize);
        ctx.fillStyle = '#404040';
        ctx.fillText(
          grid[x][y],
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
    dragId: v4(),
    startCell: [cellX, cellY],
    currentCell: [cellX, cellY],
    direction: undefined,
  };

  resetSelectedCells();
  selectedCells[cellX][cellY] = true;
  drawGrid();
}

function onPointermoveCanvas(event: PointerEvent) {
  if (dragState.value) {
    const canvas = document.getElementById('c') as HTMLCanvasElement;
    const rect = canvas.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    const cellX = Math.max(0, Math.min(Math.floor(x / cellSize), gridSize - 1));
    const cellY = Math.max(0, Math.min(Math.floor(y / cellSize), gridSize - 1));

    const direction = getDirection(
      dragState.value.startCell[0],
      dragState.value.startCell[1],
      cellX,
      cellY
    );

    resetSelectedCells();

    selectedCells[dragState.value.startCell[0]][dragState.value.startCell[1]] =
      true;

    if (direction === 'left') {
      for (
        let xCoordinate = dragState.value.startCell[0];
        xCoordinate >= cellX;
        xCoordinate--
      ) {
        selectedCells[xCoordinate][dragState.value.startCell[1]] = true;
      }
    } else if (direction === 'right') {
      for (
        let xCoordinate = dragState.value.startCell[0];
        xCoordinate <= cellX;
        xCoordinate++
      ) {
        selectedCells[xCoordinate][dragState.value.startCell[1]] = true;
      }
    } else if (direction === 'up') {
      for (
        let yCoordinate = dragState.value.startCell[1];
        yCoordinate >= cellY;
        yCoordinate--
      ) {
        selectedCells[dragState.value.startCell[0]][yCoordinate] = true;
      }
    } else if (direction === 'down') {
      for (
        let yCoordinate = dragState.value.startCell[1];
        yCoordinate <= cellY;
        yCoordinate++
      ) {
        selectedCells[dragState.value.startCell[0]][yCoordinate] = true;
      }
    } else if (direction === 'up-right') {
      for (
        let yCoordinate = dragState.value.startCell[1];
        yCoordinate >= cellY;
        yCoordinate--
      ) {
        const xCoordinate =
          dragState.value.startCell[0] -
          (yCoordinate - dragState.value.startCell[1]);
        if (coordinatesAreValid(xCoordinate, yCoordinate)) {
          selectedCells[xCoordinate][yCoordinate] = true;
        }
      }
    } else if (direction === 'up-left') {
      for (
        let yCoordinate = dragState.value.startCell[1];
        yCoordinate >= cellY;
        yCoordinate--
      ) {
        const xCoordinate =
          dragState.value.startCell[0] +
          (yCoordinate - dragState.value.startCell[1]);
        if (coordinatesAreValid(xCoordinate, yCoordinate)) {
          selectedCells[xCoordinate][yCoordinate] = true;
        }
      }
    } else if (direction === 'down-left') {
      for (
        let yCoordinate = dragState.value.startCell[1];
        yCoordinate <= cellY;
        yCoordinate++
      ) {
        const xCoordinate =
          dragState.value.startCell[0] -
          (yCoordinate - dragState.value.startCell[1]);
        if (coordinatesAreValid(xCoordinate, yCoordinate)) {
          selectedCells[xCoordinate][yCoordinate] = true;
        }
      }
    } else if (direction === 'down-right') {
      for (
        let yCoordinate = dragState.value.startCell[1];
        yCoordinate <= cellY;
        yCoordinate++
      ) {
        const xCoordinate =
          dragState.value.startCell[0] +
          (yCoordinate - dragState.value.startCell[1]);
        if (coordinatesAreValid(xCoordinate, yCoordinate)) {
          selectedCells[xCoordinate][yCoordinate] = true;
        }
      }
    }

    dragState.value = {
      dragId: dragState.value.dragId,
      startCell: dragState.value.startCell,
      currentCell: [cellX, cellY],
      direction: direction,
    };

    drawGrid();
  }
}

function onPointerupCanvas(event: PointerEvent) {
  const canvas = document.getElementById('c') as HTMLCanvasElement;
  const rect = canvas.getBoundingClientRect();
  const x = event.clientX - rect.left;
  const y = event.clientY - rect.top;
  const cellX = Math.min(Math.floor(x / cellSize), gridSize - 1);
  const cellY = Math.min(Math.floor(y / cellSize), gridSize - 1);

  const selectedWord = getSelectedWord();
  const reversedSelectedWord = selectedWord.split('').reverse().join('');
  console.log('Selected:', selectedWord, reversedSelectedWord);

  const isWordFound =
    words.value.includes(selectedWord) ||
    words.value.includes(reversedSelectedWord);

  const isNewWord =
    !foundWords.value.includes(selectedWord) &&
    !foundWords.value.includes(reversedSelectedWord);

  if (isWordFound && isNewWord) {
    markSelectedWordCorrect();
    if (words.value.includes(selectedWord)) {
      console.log('Found:', selectedWord);
      foundWords.value.push(selectedWord);
    } else {
      console.log('Found:', reversedSelectedWord);
      foundWords.value.push(reversedSelectedWord);
    }
  }

  dragState.value = undefined;
  drawGrid();
}

function coordinatesAreValid(x: number, y: number): boolean {
  return !(x < 0 || x >= gridSize || y < 0 || y >= gridSize);
}

function getSelectedWord(): string {
  let selectedWord = '';

  const startCell = dragState.value?.startCell;
  const endCell = dragState.value?.currentCell;
  const direction = dragState.value?.direction;

  if (startCell && endCell) {
    if (direction === 'left') {
      for (let x = startCell[0]; x >= endCell[0]; x--) {
        selectedWord += grid[x][startCell[1]];
      }
    } else if (direction === 'right') {
      for (let x = startCell[0]; x <= endCell[0]; x++) {
        selectedWord += grid[x][startCell[1]];
      }
    } else if (direction === 'up') {
      for (let y = startCell[1]; y >= endCell[1]; y--) {
        selectedWord += grid[startCell[0]][y];
      }
    } else if (direction === 'down') {
      for (let y = startCell[1]; y <= endCell[1]; y++) {
        selectedWord += grid[startCell[0]][y];
      }
    } else if (direction === 'up-left') {
      for (let i = 0; i <= startCell[1] - endCell[1]; i++) {
        const x = startCell[0] - i;
        const y = startCell[1] - i;
        if (coordinatesAreValid(x, y)) {
          selectedWord += grid[x][y];
        } else {
          break;
        }
      }
    } else if (direction === 'up-right') {
      for (let i = 0; i <= startCell[1] - endCell[1]; i++) {
        const x = startCell[0] + i;
        const y = startCell[1] - i;
        if (coordinatesAreValid(x, y)) {
          selectedWord += grid[x][y];
        } else {
          break;
        }
      }
    } else if (direction === 'down-left') {
      for (let i = 0; i <= endCell[1] - startCell[1]; i++) {
        const x = startCell[0] - i;
        const y = startCell[1] + i;
        if (coordinatesAreValid(x, y)) {
          selectedWord += grid[x][y];
        } else {
          break;
        }
      }
    } else if (direction === 'down-right') {
      for (let i = 0; i <= endCell[1] - startCell[1]; i++) {
        const x = startCell[0] + i;
        const y = startCell[1] + i;
        if (coordinatesAreValid(x, y)) {
          selectedWord += grid[x][y];
        } else {
          break;
        }
      }
    } else {
      selectedWord = grid[startCell[0]][startCell[1]];
    }
  }

  return selectedWord;
}

function fillCell(xCell: number, yCell: number, fillStyle: string) {
  if (xCell < 0 || xCell > gridSize - 1 || yCell < 0 || yCell > gridSize - 1) {
    return;
  }

  const canvas = document.getElementById('c') as HTMLCanvasElement;
  const ctx = canvas.getContext('2d');

  if (ctx) {
    ctx.fillStyle = fillStyle;
    ctx.fillRect(xCell * cellSize, yCell * cellSize, cellSize, cellSize);
  }
}

function markSelectedWordCorrect() {
  for (let x = 0; x < gridSize; x++) {
    for (let y = 0; y < gridSize; y++) {
      if (!correctCells[x][y]) {
        correctCells[x][y] = selectedCells[x][y];
      }
    }
  }

  resetSelectedCells();
}

function getDirection(
  startX: number,
  startY: number,
  x: number,
  y: number
): Direction {
  const dx = x - startX;
  const dy = y - startY;

  if (dx === 0) {
    if (dy > 0) {
      return 'down';
    } else if (dy < 0) {
      return 'up';
    }
  } else if (dy === 0) {
    if (dx > 0) {
      return 'right';
    } else if (dx < 0) {
      return 'left';
    }
  } else if (dx >= 1 && dy >= 1) {
    return 'down-right';
  } else if (dx <= -1 && dy >= 1) {
    return 'down-left';
  } else if (dx >= 1 && dy <= -1) {
    return 'up-right';
  } else if (dx <= -1 && dy <= -1) {
    return 'up-left';
  } else {
    return undefined;
  }
}
</script>

<style scoped></style>
