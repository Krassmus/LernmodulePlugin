<template>
  <img :src="card.imageUrl" :alt="card.altText" />
  <button type="button" @click="deleteImage">
    {{ $gettext('Bild Löschen') }}
  </button>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FlashCard, FlashCardTaskDefinition } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';

export default defineComponent({
  name: 'EditedFlashCardImage',
  props: {
    card: {
      type: Object as PropType<FlashCard>,
      required: true,
    },
  },
  methods: {
    deleteImage(): void {
      const taskDefinition =
        taskEditorStore.taskDefinition as FlashCardTaskDefinition;
      const newCards = [...taskDefinition.cards];
      const cardIndex = newCards.findIndex(
        (card) => card.uuid === this.card.uuid
      );
      newCards[cardIndex] = {
        ...newCards[cardIndex],
        imageUrl: undefined,
      };
      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...taskDefinition,
          cards: newCards,
        },
        undoBatch: {},
      });
    },
  },
});
</script>

<style scoped></style>
