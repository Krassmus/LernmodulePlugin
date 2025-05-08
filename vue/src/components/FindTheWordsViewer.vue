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

    <canvas id="c" />
  </div>
</template>

<script setup lang="ts">
import { computed, defineProps, onMounted, PropType, watch } from 'vue';
import { FindTheWordsTask } from '@/models/TaskDefinition';

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

// Computed properties
const words = computed(() => {
  if (props.task?.words) {
    return props.task.words.split(',');
  }
  return [];
});

const alphabet = computed(() => {
  if (props.task?.alphabet) {
    return props.task.alphabet.split('').filter((char) => char.match(/\p{L}/u));
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
    canvas.width = 720;
    canvas.height = 800;

    ctx.font = 'bold 48px serif';

    for (let i = 0; i < 10; i++) {
      for (let j = 0; j < 10; j++) {
        ctx.fillText(randomLetter(), j * 72, 40 + i * 72);
      }
    }
  }
}

// Call the function to draw the matrix
onMounted(() => {
  drawMatrix();
});
</script>

<style scoped></style>
