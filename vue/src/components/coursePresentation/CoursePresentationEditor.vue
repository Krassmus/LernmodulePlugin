<script setup lang="ts">
import { defineProps, PropType } from 'vue';
import { CoursePresentationTask } from '@/models/CoursePresentationTask';

const props = defineProps({
  taskDefinition: {
    type: Object as PropType<CoursePresentationTask>,
    required: true,
  },
});
</script>

<template>
  <div>Course Presentation Editor</div>
</template>

<style scoped lang="scss"></style>
