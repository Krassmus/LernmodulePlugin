<template>
  <div class="stud5p-task">
    <template v-if="words.length > 0">
      <div class="canvas-and-word-list-container">
        <canvas
          id="c"
          :width="canvasSize"
          :height="canvasSize"
          @pointerdown.stop="onPointerdownCanvas($event)"
          @pointermove.stop="onPointermoveCanvas($event)"
          @pointerup.stop="onPointerupCanvas($event)"
        />

        <div class="word-list" v-if="task.showWordList">
          <h2 class="word-list-title" v-text="task.strings.wordListTitle" />
          <ul>
            <template v-for="word in words" :key="word">
              <li v-if="foundWords.includes(word)" class="found-word">
                {{ word }}
              </li>
              <li v-else>{{ word }}</li>
            </template>
          </ul>
        </div>
      </div>
      <div class="time-container">
        <span>⏲</span>
        <span class="time-info" v-text="task.strings.timer" />
        <span
          v-text="
            $gettext('%{ minutes }:%{ seconds }', {
              minutes: Math.floor(timer / 60),
              seconds: (timer % 60).toString().padStart(2, '0'),
            })
          "
        />
      </div>
      <div class="feedback-and-button-container">
        <div class="feedback-container">
          <FeedbackElement
            v-if="taskCompleted"
            :achievedPoints="score"
            :maxPoints="maxScore"
            :result-message="resultMessage"
          />
        </div>

        <div class="button-panel">
          <button
            v-if="showCheckButton"
            v-text="task.strings.checkButton"
            @click="onClickCheck"
            type="button"
            class="stud5p-button"
          />

          <button
            v-if="taskCompleted"
            v-text="task.strings.retryButton"
            @click="onClickRetry"
            type="button"
            class="stud5p-button"
          />

          <button
            v-if="taskSubmitted && !showSolutions"
            v-text="task.strings.solutionsButton"
            @click="onClickShowSolutions"
            type="button"
            class="stud5p-button"
          />
        </div>
      </div>
    </template>
    <div v-else>
      {{
        $gettext(
          'Fügen Sie Wörter hinzu um ein Find the Words Rätsel zu erstellen.'
        )
      }}
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  computed,
  defineProps,
  onBeforeUnmount,
  onMounted,
  PropType,
  ref,
  watch,
} from 'vue';
import FeedbackElement from '@/components/FeedbackElement.vue';
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

// State
let grid: string[][] = [];
let selectedCells: boolean[][] = [];
let correctCells: boolean[][] = [];
let solutionCoordinates: { x: number; y: number }[] = [];
const foundWords = ref<string[]>([]);
const dragState = ref<DragState>();
const taskSubmitted = ref<boolean>(false);
const showSolutions = ref<boolean>(false);
const timer = ref<number>(0); // Track elapsed time in seconds
let timerStarted: boolean = false; // Flag to check if timer has started
let timerInterval: number | null = null; // Store interval ID to control timer

// Constants
const cellSize = 48;

// Lifecycle Hooks
onMounted(() => {
  initializeGrid();
  drawGrid();
  document.addEventListener('pointerdown', onPointerdownOutsideCanvas);
  document.addEventListener('pointerup', onPointerupOutsideCanvas);
});

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', onPointerdownOutsideCanvas);
  document.removeEventListener('pointerup', onPointerupOutsideCanvas);
});

// Computed properties
const gridSize = computed(() => {
  let longestWordLength = Math.max(...words.value.map((word) => word.length));
  let numberOfWords = words.value.length;

  return Math.min(Math.max(longestWordLength, numberOfWords, 5), 18);
});

const canvasSize = computed(() => {
  return gridSize.value * cellSize;
});

const words = computed(() => {
  if (props.task?.words) {
    return props.task.words
      .split(',')
      .filter((word) => word.trim())
      .map((word) => word.trim().toUpperCase());
  }
  return [];
});

const disabledDirections = computed(() => {
  let result = [];
  if (props.task?.directions) {
    if (!props.task.directions.n) result.push('N');
    if (!props.task.directions.ne) result.push('NE');
    if (!props.task.directions.e) result.push('E');
    if (!props.task.directions.se) result.push('SE');
    if (!props.task.directions.s) result.push('S');
    if (!props.task.directions.sw) result.push('SW');
    if (!props.task.directions.w) result.push('W');
    if (!props.task.directions.nw) result.push('NW');
  }
  return result;
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

const taskCompleted = computed(() => {
  return score.value === maxScore.value || taskSubmitted.value;
});

const resultMessage = computed(() => {
  let result = props.task.strings.resultMessage.replace(
    ':correct',
    score.value.toString()
  );
  result = result.replace(':total', maxScore.value.toString());

  return result;
});

const showCheckButton = computed(() => {
  return !taskCompleted.value;
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

watch(
  () => taskCompleted.value,
  (newValue) => {
    if (newValue) {
      stopTimer();
    }
  }
);

function startTimer(): void {
  timerStarted = true;

  // Start a timer that increments every second
  timerInterval = setInterval(() => {
    timer.value++;
  }, 1000) as unknown as number;
}

function stopTimer(): void {
  // Stop the timer and clear the interval
  if (timerInterval !== null) {
    clearInterval(timerInterval);
    timerInterval = null; // Reset after clearing
  }

  timerStarted = false;
}

function onClickCheck(): void {
  taskSubmitted.value = true;
  stopTimer();
}

function onClickRetry(): void {
  taskSubmitted.value = false;
  showSolutions.value = false;
  timer.value = 0;
  solutionCoordinates = [];
  stopTimer();
  initializeGrid();
  drawGrid();
}

function onClickShowSolutions(): void {
  showSolutions.value = true;
  drawGrid();
}

function randomLetter() {
  if (alphabet.value.length > 0) {
    const randomIndex = Math.floor(Math.random() * alphabet.value.length);
    return alphabet.value[randomIndex];
  }
  return '';
}

function resetSelectedCells() {
  selectedCells = [];
  for (let x = 0; x < gridSize.value; x++) {
    selectedCells[x] = [];
    for (let y = 0; y < gridSize.value; y++) {
      selectedCells[x][y] = false;
    }
  }
}

function resetCorrectCells() {
  correctCells = [];
  for (let x = 0; x < gridSize.value; x++) {
    correctCells[x] = [];
    for (let y = 0; y < gridSize.value; y++) {
      correctCells[x][y] = false;
    }
  }
}

function initializeGrid() {
  resetSelectedCells();
  resetCorrectCells();
  foundWords.value = [];
  solutionCoordinates = [];

  const options = {
    cols: gridSize.value,
    rows: gridSize.value,
    disabledDirections: disabledDirections.value,
    dictionary: words.value,
    maxWords: words.value.length,
    backwardsProbability: 0.3,
    upperCase: true,
    diacritics: true,
  };

  // Create a new puzzle
  let ws = new WordSearch(options);
  let count = 1;
  while (ws.words.length !== words.value.length && count <= 10) {
    console.log(
      'Es konnten nicht alle Wörter untergebracht werden. Versuche es bis zu 10 mal erneut:',
      count + '. Versuch'
    );
    ws = new WordSearch(options);
    count++;
  }

  ws.words.forEach((word: any) => {
    solutionCoordinates.push(...word.path);
  });

  grid = [];
  for (let x = 0; x < gridSize.value; x++) {
    grid[x] = [];
    for (let y = 0; y < gridSize.value; y++) {
      if (solutionCoordinates.some((coord) => coord.x === x && coord.y === y)) {
        grid[x][y] = ws.grid[y][x];
      } else {
        grid[x][y] = randomLetter();
      }
    }
  }
}

function drawGrid() {
  const canvas = document.getElementById('c') as HTMLCanvasElement;
  if (!canvas) {
    console.error('No Canvas');
    return;
  }
  const ctx = canvas.getContext('2d');
  if (ctx) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.font = 'bold 26px calibri';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    for (let x = 0; x < gridSize.value; x++) {
      for (let y = 0; y < gridSize.value; y++) {
        if (correctCells[x][y]) {
          fillCell(x, y, 'rgb(165,202,158)');
        }
        if (selectedCells[x][y]) {
          fillCell(x, y, 'rgba(140, 180, 255, 0.34)');
        }
        if (
          showSolutions.value &&
          solutionCoordinates.some((coord) => coord.x === x && coord.y === y) &&
          !correctCells[x][y]
        ) {
          fillCell(x, y, 'rgba(165,202,158,0.15)');
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
  if (taskCompleted.value) return;

  if (!timerStarted) {
    console.log('Starting timer');
    startTimer();
  }

  if (!dragState.value) {
    // Start selection
    const { cellX, cellY } = getCellUnderCursor(event);

    dragState.value = {
      dragId: v4(),
      startCell: [cellX, cellY],
      currentCell: [cellX, cellY],
      direction: undefined,
    };

    resetSelectedCells();
    selectedCells[cellX][cellY] = true;
    drawGrid();
  } else {
    // We are already selecting with the two-clicks selection method and this
    // is the second click, so we end the selection
    endSelection();
  }
}

function getCellUnderCursor(event: PointerEvent) {
  const canvas = document.getElementById('c') as HTMLCanvasElement;
  const rect = canvas.getBoundingClientRect();
  const x = event.clientX - rect.left;
  const y = event.clientY - rect.top;
  const cellX = Math.max(
    0,
    Math.min(Math.floor(x / cellSize), gridSize.value - 1)
  );
  const cellY = Math.max(
    0,
    Math.min(Math.floor(y / cellSize), gridSize.value - 1)
  );
  return { cellX, cellY };
}

function onPointermoveCanvas(event: PointerEvent) {
  if (dragState.value) {
    const { cellX, cellY } = getCellUnderCursor(event);

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
  if (dragState.value) {
    const { cellX, cellY } = getCellUnderCursor(event);

    if (
      dragState.value.startCell[0] != cellX ||
      dragState.value.startCell[1] != cellY
    ) {
      // Moved the cursor to another cell before releasing, so we use click-and-hold selection and end the selection
      endSelection();
    }
  }
}

function onPointerdownOutsideCanvas(event: PointerEvent) {
  if (dragState.value) {
    endSelection();
  }
}

function onPointerupOutsideCanvas(event: PointerEvent) {
  if (dragState.value) {
    endSelection();
  }
}

function endSelection() {
  const selectedWord = getSelectedWord();
  const reversedSelectedWord = selectedWord.split('').reverse().join('');
  console.log('Selected:', selectedWord, reversedSelectedWord);

  const isWordFound =
    words.value.includes(selectedWord) ||
    words.value.includes(reversedSelectedWord);

  if (isWordFound) markSelectedWordCorrect();

  const isNewWord =
    !foundWords.value.includes(selectedWord) &&
    !foundWords.value.includes(reversedSelectedWord);

  if (isWordFound && isNewWord) {
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
  return !(x < 0 || x >= gridSize.value || y < 0 || y >= gridSize.value);
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
  if (
    xCell < 0 ||
    xCell > gridSize.value - 1 ||
    yCell < 0 ||
    yCell > gridSize.value - 1
  ) {
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
  for (let x = 0; x < gridSize.value; x++) {
    for (let y = 0; y < gridSize.value; y++) {
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

<style scoped>
.canvas-and-word-list-container {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
}

.word-list {
  display: flex;
  flex-direction: column;
  gap: 1em;
  background: #f5f5f5;
  padding: 0.5em;
  border: 1px solid #ccc;
  align-self: stretch;
}

.word-list-title {
  margin: 0;
  font-weight: bold;
}

.word-list ul {
  padding-left: 0.75em;
  padding-right: 1.5em;
}

.word-list ul .found-word {
  color: #255c41;
  position: relative;
}

.word-list ul .found-word::after {
  content: '✔';
  position: absolute;
  right: -1.5em;
}

.time-container {
  padding-top: 0.5em;
  display: flex;
  flex-direction: row;
  gap: 0.25em;
  justify-content: flex-start;
  align-items: center;
}

.time-info {
  font-weight: bold;
}
</style>
