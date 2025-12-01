<template>
  <div class="stud5p-task">
    <div v-for="word in props.task?.words" :key="word.uuid">
      {{ word.solution }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, PropType } from 'vue';
import { CrosswordTask } from '@/models/CrosswordTask';

const props = defineProps({
  task: {
    type: Object as PropType<CrosswordTask>,
    required: true,
  },
});
</script>

<style scoped></style>
