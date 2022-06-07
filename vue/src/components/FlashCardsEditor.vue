<template>
  The aufgabe that we are editing: {{ task }}
  (TODO: Implement an editor for flash cards here :)
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FlashCardTaskDefinition } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'FlashCardsEditor',
  props: {
    task: {
      type: Object as PropType<FlashCardTaskDefinition>,
      required: true,
    },
  },
});
</script>

<style scoped></style>
