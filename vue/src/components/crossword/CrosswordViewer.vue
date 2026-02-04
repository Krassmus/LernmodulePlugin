<template>
  <div class="stud5p-task">
    <template v-if="task.words.length > 0">
      <div class="canvas-and-hints-list-container">
        <div ref="canvasWrapperRef" class="canvas-wrapper">
          <!-- tabindex="0" is needed to make the canvas focusable and the keydown listener to work -->
          <canvas
            ref="canvasRef"
            tabindex="0"
            :width="canvasSize"
            :height="canvasSize"
            :style="{ width: canvasSize + 'px', height: canvasSize + 'px' }"
            @pointerdown.stop="onPointerDownCanvas($event)"
            @keydown.stop="onKeyDownCanvas($event)"
            class="canvas"
          />
        </div>
        <div class="hint-list">
          <div
            v-for="word in task.words"
            :key="word.uuid"
            @click="onClickHint(word)"
            class="hint"
            :class="{ 'selected-hint': selectedWord?.uuid === word.uuid }"
          >
            {{ word.hint }}
          </div>
        </div>
      </div>
    </template>
    <div v-else>
      {{
        $gettext('Fügen Sie Wörter hinzu um ein Kreuzworträtsel zu erstellen.')
      }}
    </div>
    <div class="feedback-and-button-container">
      <div class="feedback-container">
        <FeedbackElement
          v-if="taskSubmitted"
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
          v-if="taskSubmitted"
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
import { CrosswordTask, Word } from '@/models/CrosswordTask';
import { $gettext } from '@/language/gettext';
import FeedbackElement from '@/components/FeedbackElement.vue';

const debug = window.STUDIP.LernmoduleVueJS.LERNMODULE_DEBUG;

// Types
type Cell = { x: number; y: number; letter: string };
type Path = Cell[];
type PlacedWord = Word & { path: Path };

// Props
const props = defineProps({
  task: {
    type: Object as PropType<CrosswordTask>,
    required: true,
  },
});

// Refs
const canvasWrapperRef = ref<HTMLElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null);

// State
const selectedCell = ref<Cell>();
const selectedWord = ref<PlacedWord>();
const solutionGrid = ref<string[][]>([]);
const userInputGrid = ref<string[][]>([]);
const taskSubmitted = ref<boolean>(false);
const showSolutions = ref<boolean>(false);
const placedWords = ref<PlacedWord[]>([]);
const canvasSize = ref(0);

// Lifecycle Hooks

onMounted(() => {
  initializeGrids();
  updateCanvasSize();
  drawGrid();
  window.addEventListener('resize', updateCanvasSize);
});
onBeforeUnmount(() => {
  window.removeEventListener('resize', updateCanvasSize);
});

// Computed properties
const gridSize = computed(() => {
  let size = 0;

  props.task.words.forEach((word) => {
    if (word.direction === 'across') {
      if (word.x + word.solution.length > size) {
        size = word.x + word.solution.length;
      }
    } else if (word.direction === 'down') {
      if (word.y + word.solution.length > size) {
        size = word.y + word.solution.length;
      }
    }
  });

  if (debug) console.log('Grid size is', size, 'x', size);

  return size;
});

const cellSize = computed(() =>
  gridSize.value > 0 ? Math.floor(canvasSize.value / gridSize.value) : 0
);

const fontSize = computed(() =>
  cellSize.value > 0 ? Math.floor(cellSize.value / 2) : 0
);

const maxScore = computed(() => {
  let maxScore = 0;
  for (let x = 0; x < gridSize.value; x++) {
    for (let y = 0; y < gridSize.value; y++) {
      if (solutionGrid.value[x][y]) {
        maxScore++;
      }
    }
  }
  return maxScore;
});

const score = computed(() => {
  let score = 0;
  for (let x = 0; x < gridSize.value; x++) {
    for (let y = 0; y < gridSize.value; y++) {
      if (
        solutionGrid.value[x][y] &&
        userInputGrid.value[x][y] === solutionGrid.value[x][y]
      ) {
        score++;
      }
    }
  }
  return score;
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
  return !taskSubmitted.value;
});

// Watchers
watch(
  () => props.task,
  () => {
    initializeGrids();
    if (canvasRef.value) {
      if (canvasRef.value.getContext('2d')) {
        canvasRef.value
          ?.getContext('2d')
          ?.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
      }
    }
    drawGrid();
  },
  { deep: true }
);

watch(
  () => userInputGrid.value,
  () => {
    drawGrid();
  },
  { deep: true }
);

watch(
  () => selectedCell.value,
  () => {
    drawGrid();
    if (debug) {
      console.log(
        'selected cell:',
        selectedCell.value?.x,
        selectedCell.value?.y
      );
    }
  },
  { deep: true }
);

watch(
  () => selectedWord.value,
  () => {
    drawGrid();
    if (debug) {
      console.log('selected word:', selectedWord.value?.solution);
    }
  },
  { deep: true }
);

// Functions
function updateCanvasSize() {
  const wrapper = canvasWrapperRef.value;
  if (!wrapper) return;
  canvasSize.value = Math.floor(
    Math.min(wrapper.clientWidth, wrapper.clientHeight)
  );
}

function onClickCheck(): void {
  taskSubmitted.value = true;
  selectedCell.value = undefined;
  selectedWord.value = undefined;
}

function onClickRetry(): void {
  taskSubmitted.value = false;
  showSolutions.value = false;
  selectedCell.value = undefined;
  initializeGrids();
  drawGrid();
}

function onClickShowSolutions(): void {
  showSolutions.value = true;
  selectedCell.value = undefined;
  selectedWord.value = undefined;
  drawGrid();
}

function initializeGrids() {
  selectedWord.value = undefined;
  selectedCell.value = undefined;

  solutionGrid.value = Array.from({ length: gridSize.value }, () =>
    Array.from({ length: gridSize.value }, () => '')
  );

  userInputGrid.value = Array.from({ length: gridSize.value }, () =>
    Array.from({ length: gridSize.value }, () => '')
  );

  // Create a path for each word based on it's starting coordinates and direction
  placedWords.value = props.task.words.map((word) => ({
    ...word,
    path: createWordPath(word),
  }));

  // Place each word in the solution grid
  for (const word of placedWords.value) {
    for (const cell of word.path) {
      solutionGrid.value[cell.x][cell.y] = cell.letter;
    }
  }
}

/**
 * Creates a path (set of coordinates) based on the starting coordinates and direction of the word
 *
 * @param word
 */
function createWordPath(word: Word): Path {
  let path = [];

  const directionalDeltas = {
    across: { dx: 1, dy: 0 },
    down: { dx: 0, dy: 1 },
  };
  const { dx, dy } =
    directionalDeltas[word.direction as keyof typeof directionalDeltas];

  const length = word.solution.length;

  for (let i = 0; i < length; i++) {
    const x = word.x + dx * i;
    const y = word.y + dy * i;
    path.push({ x: x, y: y, letter: word.solution[i].toUpperCase() });
  }

  return path;
}

function drawGrid() {
  const canvas = canvasRef.value;
  if (!canvas) {
    console.error('No Canvas');
    return;
  }
  const ctx = canvas.getContext('2d');
  if (!ctx) {
    return;
  }

  ctx.clearRect(0, 0, canvas.width, canvas.height);

  // Highlight the selected word
  selectedWord.value?.path.forEach((cell) => {
    fillCell(cell, 'rgba(140, 180, 255, 0.13)');
  });

  // Highlight the selected cell
  if (selectedCell.value) {
    fillCell(selectedCell.value, 'rgba(140, 180, 255, 0.34)');
  }

  for (let x = 0; x < gridSize.value; x++) {
    for (let y = 0; y < gridSize.value; y++) {
      // Draw the grid lines around letters of the solution grid
      if (solutionGrid.value[x][y]) {
        ctx.strokeStyle = 'gainsboro';
        ctx.strokeRect(
          x * cellSize.value,
          y * cellSize.value,
          cellSize.value,
          cellSize.value
        );
      } else if (props.task.colorEmptyCells) {
        ctx.fillStyle = 'rgba(129,129,129,0.1)';
        ctx.fillRect(
          x * cellSize.value,
          y * cellSize.value,
          cellSize.value,
          cellSize.value
        );
      }

      // If the task is submitted, color the cells depending on if they're correct or incorrect
      if (
        taskSubmitted.value &&
        !showSolutions.value &&
        userInputGrid.value[x][y]
      ) {
        if (userInputGrid.value[x][y] === solutionGrid.value[x][y]) {
          ctx.fillStyle = '#9dd8bb';
          ctx.fillRect(
            x * cellSize.value,
            y * cellSize.value,
            cellSize.value,
            cellSize.value
          );
        } else {
          ctx.fillStyle = '#f7d0d0';
          ctx.fillRect(
            x * cellSize.value,
            y * cellSize.value,
            cellSize.value,
            cellSize.value
          );
        }
      }

      // Draw the solutions or the user input
      ctx.font = 'bold ' + fontSize.value + 'px calibri';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';

      if (showSolutions.value) {
        ctx.fillStyle = '#404040';
        ctx.fillText(
          solutionGrid.value[x][y],
          x * cellSize.value + cellSize.value / 2,
          y * cellSize.value + cellSize.value / 2
        );
      } else {
        ctx.fillStyle = '#404040';
        ctx.fillText(
          userInputGrid.value[x][y],
          x * cellSize.value + cellSize.value / 2,
          y * cellSize.value + cellSize.value / 2
        );
      }
    }
  }
}

function onPointerDownCanvas(event: PointerEvent) {
  if (taskSubmitted.value) return;

  // Find the coordinates of the cell under the cursor
  const cellCoordinates = getCellCoordinatesUnderCursor(event);
  if (!cellCoordinates) return;

  // Select it if it's part of the solution grid
  if (solutionGrid.value[cellCoordinates.x][cellCoordinates.y] !== '') {
    selectedCell.value = {
      x: cellCoordinates.x,
      y: cellCoordinates.y,
      letter: solutionGrid.value[cellCoordinates.x][cellCoordinates.y],
    };
  } else {
    selectedCell.value = undefined;
    selectedWord.value = undefined;
  }

  // Find and set the selected word based on the selected cell
  if (
    selectedCell.value &&
    selectedCell.value.x !== undefined &&
    selectedCell.value.y !== undefined
  ) {
    const selectableWords = placedWords.value.filter((word) => {
      return word.path.some(
        (cell) =>
          cell.x === selectedCell.value!.x && cell.y === selectedCell.value!.y
      );
    });

    if (selectableWords) {
      if (selectableWords.length === 1) {
        selectedWord.value = selectableWords[0];
      } else if (selectableWords.length === 2) {
        if (selectedWord.value === selectableWords[0]) {
          selectedWord.value = selectableWords[1];
        } else {
          selectedWord.value = selectableWords[0];
        }
      }
    }
  }
}

function onKeyDownCanvas(event: KeyboardEvent) {
  if (taskSubmitted.value) {
    return;
  }

  if (selectedCell.value) {
    // Check if the key is a letter and place it in the grid
    if (event.key.match(/^[a-zA-Z]$/)) {
      userInputGrid.value[selectedCell.value.x][selectedCell.value.y] =
        event.key.toUpperCase();

      // Jump to the next cell in the selected word or the next word if it was the last cell
      if (selectedWord.value) {
        const indexOfCellInWord = selectedWord.value.path.findIndex((cell) => {
          return (
            cell.x === selectedCell.value!.x && cell.y === selectedCell.value!.y
          );
        });
        if (indexOfCellInWord < selectedWord.value.path.length - 1) {
          selectedCell.value = selectedWord.value.path[indexOfCellInWord + 1];
        } else if (indexOfCellInWord === selectedWord.value.path.length - 1) {
          const currentWordIndex = placedWords.value.findIndex((word) => {
            return word.uuid == selectedWord.value?.uuid;
          });
          if (currentWordIndex < placedWords.value.length - 1) {
            selectedWord.value = placedWords.value[currentWordIndex + 1];
            selectedCell.value = selectedWord.value.path[0];
          }
        }
      }
    } else if (event.key === 'Backspace') {
      userInputGrid.value[selectedCell.value.x][selectedCell.value.y] = '';

      // Jump to the previous cell in the word
      if (selectedWord.value) {
        const indexOfCellInWord = selectedWord.value.path.findIndex((cell) => {
          return (
            cell.x === selectedCell.value!.x && cell.y === selectedCell.value!.y
          );
        });
        if (indexOfCellInWord > 0) {
          selectedCell.value = selectedWord.value.path[indexOfCellInWord - 1];
        }
      }
    } else if (event.key === 'Delete') {
      userInputGrid.value[selectedCell.value.x][selectedCell.value.y] = '';

      // Jump to the next cell in the selected word
      if (selectedWord.value) {
        const indexOfCellInWord = selectedWord.value.path.findIndex((cell) => {
          return (
            cell.x === selectedCell.value!.x && cell.y === selectedCell.value!.y
          );
        });
        if (indexOfCellInWord < selectedWord.value.path.length - 1) {
          selectedCell.value = selectedWord.value.path[indexOfCellInWord + 1];
        }
      }
    } else if (
      ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(event.key)
    ) {
      event.preventDefault(); // don't scroll up or down with arrow keys while canvas is focussed
      if (selectedWord.value) {
        const indexOfCellInWord = selectedWord.value.path.findIndex((cell) => {
          return (
            cell.x === selectedCell.value!.x && cell.y === selectedCell.value!.y
          );
        });
        if (selectedWord.value.direction === 'across') {
          if (event.key === 'ArrowLeft') {
            // select previous cell in across word
            if (indexOfCellInWord > 0) {
              selectedCell.value =
                selectedWord.value.path[indexOfCellInWord - 1];
            } else if (indexOfCellInWord === 0) {
              selectedCell.value =
                selectedWord.value.path[selectedWord.value.path.length - 1];
            }
          } else if (event.key === 'ArrowRight') {
            // select next cell in across word
            if (indexOfCellInWord < selectedWord.value.path.length - 1) {
              selectedCell.value =
                selectedWord.value.path[indexOfCellInWord + 1];
            } else if (
              indexOfCellInWord ===
              selectedWord.value.path.length - 1
            ) {
              selectedCell.value = selectedWord.value.path[0];
            }
          } else if (event.key === 'ArrowUp') {
            // if there is an occupied cell above the currently selected cell, select that one and select the corresponding word
            const wordAbove = placedWords.value.find((word) => {
              return word.path.some((cell) => {
                return (
                  cell.x === selectedCell.value!.x &&
                  cell.y === selectedCell.value!.y - 1
                );
              });
            });
            if (wordAbove) {
              selectedWord.value = wordAbove;
              selectedCell.value = wordAbove.path.find((cell) => {
                return (
                  cell.x === selectedCell.value!.x &&
                  cell.y === selectedCell.value!.y - 1
                );
              });
            }
          } else if (event.key === 'ArrowDown') {
            // if there is an occupied cell below the currently selected cell, select that one and select the corresponding word
            const wordBelow = placedWords.value.find((word) => {
              return word.path.some((cell) => {
                return (
                  cell.x === selectedCell.value!.x &&
                  cell.y === selectedCell.value!.y + 1
                );
              });
            });
            if (wordBelow) {
              selectedWord.value = wordBelow;
              selectedCell.value = wordBelow.path.find((cell) => {
                return (
                  cell.x === selectedCell.value!.x &&
                  cell.y === selectedCell.value!.y + 1
                );
              });
            }
          }
        } else if (selectedWord.value.direction === 'down') {
          if (event.key === 'ArrowUp') {
            // select previous cell in down word
            if (indexOfCellInWord > 0) {
              selectedCell.value =
                selectedWord.value.path[indexOfCellInWord - 1];
            } else if (indexOfCellInWord === 0) {
              selectedCell.value =
                selectedWord.value.path[selectedWord.value.path.length - 1];
            }
          } else if (event.key === 'ArrowDown') {
            // select next cell in down word
            if (indexOfCellInWord < selectedWord.value.path.length - 1) {
              selectedCell.value =
                selectedWord.value.path[indexOfCellInWord + 1];
            } else if (
              indexOfCellInWord ===
              selectedWord.value.path.length - 1
            ) {
              selectedCell.value = selectedWord.value.path[0];
            }
          } else if (event.key === 'ArrowLeft') {
            // if there is an occupied cell left of the currently selected cell, select that one and select the corresponding word
            const wordToTheLeft = placedWords.value.find((word) => {
              return word.path.some((cell) => {
                return (
                  cell.x === selectedCell.value!.x - 1 &&
                  cell.y === selectedCell.value!.y
                );
              });
            });
            if (wordToTheLeft) {
              selectedWord.value = wordToTheLeft;
              selectedCell.value = wordToTheLeft.path.find((cell) => {
                return (
                  cell.x === selectedCell.value!.x - 1 &&
                  cell.y === selectedCell.value!.y
                );
              });
            }
          } else if (event.key === 'ArrowRight') {
            // if there is an occupied cell right of the currently selected cell, select that one and select the corresponding word
            const wordToTheRight = placedWords.value.find((word) => {
              return word.path.some((cell) => {
                return (
                  cell.x === selectedCell.value!.x + 1 &&
                  cell.y === selectedCell.value!.y
                );
              });
            });
            if (wordToTheRight) {
              selectedWord.value = wordToTheRight;
              selectedCell.value = wordToTheRight.path.find((cell) => {
                return (
                  cell.x === selectedCell.value!.x + 1 &&
                  cell.y === selectedCell.value!.y
                );
              });
            }
          }
        }
      }
    } else if (event.key === 'Tab') {
      event.preventDefault();

      const currentWordIndex = placedWords.value.findIndex((word) => {
        return word === selectedWord.value;
      });

      if (event.shiftKey) {
        if (currentWordIndex > 0) {
          selectedWord.value = placedWords.value[currentWordIndex - 1];
        } else {
          selectedWord.value = placedWords.value[placedWords.value.length - 1];
        }
      } else {
        if (currentWordIndex < placedWords.value.length - 1) {
          selectedWord.value = placedWords.value[currentWordIndex + 1];
        } else {
          selectedWord.value = placedWords.value[0];
        }
      }

      selectedCell.value = selectedWord.value?.path[0];
    }
  } else {
    // If we currently don't have any word and cell selected, pressing tab or an arrow key selects the first cell of the first word
    if (
      ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Tab'].includes(
        event.key
      )
    ) {
      event.preventDefault(); // don't scroll up or down with arrow keys while canvas is focussed
      selectedWord.value = placedWords.value[0];
      selectedCell.value = selectedWord.value.path[0];
    }
  }
}

function onClickHint(word: Word) {
  if (debug) console.log('Clicked on hint:', word.hint);

  if (taskSubmitted.value) return;

  selectedWord.value = placedWords.value.find(
    (value) => value.uuid === word.uuid
  );
  selectedCell.value = selectedWord.value?.path[0];

  canvasRef.value?.focus();
}

function getCellCoordinatesUnderCursor(event: PointerEvent):
  | {
      x: number;
      y: number;
    }
  | undefined {
  if (!canvasRef.value) return;

  const rect = canvasRef.value.getBoundingClientRect();
  const x = event.clientX - rect.left;
  const y = event.clientY - rect.top;
  const cellX = Math.max(
    0,
    Math.min(Math.floor(x / cellSize.value), gridSize.value - 1)
  );
  const cellY = Math.max(
    0,
    Math.min(Math.floor(y / cellSize.value), gridSize.value - 1)
  );

  return { x: cellX, y: cellY };
}

function fillCell(cell: Cell, fillStyle: string) {
  if (
    cell.x < 0 ||
    cell.x > gridSize.value - 1 ||
    cell.y < 0 ||
    cell.y > gridSize.value - 1
  ) {
    return;
  }

  if (!canvasRef.value) return;
  const ctx = canvasRef.value.getContext('2d');

  if (ctx) {
    ctx.fillStyle = fillStyle;
    ctx.fillRect(
      cell.x * cellSize.value,
      cell.y * cellSize.value,
      cellSize.value,
      cellSize.value
    );
  }
}
</script>

<style scoped>
.canvas-and-hints-list-container {
  height: 60dvh;
  display: flex;
  flex-direction: row;
  align-items: stretch; /** makes children match containers height **/
}

.canvas-wrapper {
  flex: 1 1 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 0;
  min-height: 0;
}

.canvas {
  border: 1px solid #ddd;
  outline: none;
}

.hint-list {
  flex: 0 0 clamp(16rem, 20vw, 28rem);
  display: flex;
  flex-direction: column;
  gap: 0.75em;
  background: #f5f5f5;
  padding-left: 0.5em;
  border: 1px solid #ccc;
  overflow: scroll;
}

.hint {
  cursor: default;
}

.selected-hint {
  background: rgba(140, 180, 255, 0.13);
}
</style>
