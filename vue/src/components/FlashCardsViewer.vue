<template>
  Hier sollte die fertige Flash Cards aufgabe zum Bearbeiten angezeigt werden.
  Hier ist der Quellcode: {{ task }}
  (TODO: Implement the viewer for flash cards here :)
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FlashCardTask } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'FlashCardsViewer',
  props: {
    task: {
      type: Object as PropType<FlashCardTask>,
      required: true,
    },
  },
});
</script>

<style scoped></style>
