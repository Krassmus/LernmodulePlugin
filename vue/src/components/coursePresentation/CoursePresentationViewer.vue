<script setup lang="ts">
import { defineProps, PropType } from 'vue';
import { CoursePresentationTask } from '@/models/CoursePresentationTask';

const props = defineProps({
  task: {
    type: Object as PropType<CoursePresentationTask>,
    required: true,
  },
});
</script>

<template>
  <div>Course Presentation Viewer</div>
</template>

<style scoped lang="scss"></style>
