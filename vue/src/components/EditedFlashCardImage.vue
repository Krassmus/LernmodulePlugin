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
import produce from 'immer';

export default defineComponent({
  name: 'EditedFlashCardImage',
  props: {
    card: {
      type: Object as PropType<FlashCard>,
      required: true,
    },
  },
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as FlashCardTaskDefinition,
  },
  methods: {
    deleteImage(): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        const cardIndex = draft.cards.findIndex(
          (card) => card.uuid === this.card.uuid
        );
        draft.cards[cardIndex].imageUrl = undefined;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
  },
});
</script>

<style scoped></style>
