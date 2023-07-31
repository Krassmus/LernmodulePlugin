<template>
  <img :src="card.imageUrl" :alt="card.altText" />
  <button type="button" @click="deleteImage">
    {{ $gettext('Bild Löschen') }}
  </button>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MemoryCard, MemoryTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import produce from 'immer';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'EditedMemoryCardImage',
  props: {
    card: {
      type: Object as PropType<MemoryCard>,
      required: true,
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as MemoryTask,
  },
  methods: {
    $gettext,
    deleteImage(): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        const cardIndex = draft.cards.findIndex(
          (card) => card.uuid === this.card.uuid
        );
        draft.cards[cardIndex].imageUrl = '';
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
