<template>
  <div class="stud5p-task">
    <div v-for="word in words" :key="word">{{ word }}</div>
    <span v-for="letter in alphabet" :key="letter">{{ letter }}</span>
  </div>
</template>

<script setup lang="ts">
import { computed, defineProps, PropType } from 'vue';
import { FindTheWordsTask } from '@/models/TaskDefinition';

const props = defineProps({
  task: {
    type: Object as PropType<FindTheWordsTask>,
    required: true,
  },
});

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
</script>

<style scoped></style>
