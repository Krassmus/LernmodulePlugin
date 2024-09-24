<template>
  <div class="memory-card-image-and-button-container">
    <img
      :src="fileIdToUrl(card.file_id)"
      :alt="card.altText"
      class="memory-card-image"
    />
    <button
      type="button"
      @click="deleteImage"
      class="memory-card-delete-image-button"
    >
      {{ $gettext('Bild Löschen') }}
    </button>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { fileIdToUrl, MemoryCard, MemoryTask } from '@/models/TaskDefinition';
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
    fileIdToUrl,
    $gettext,
    deleteImage(): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        const cardIndex = draft.cards.findIndex(
          (card) => card.uuid === this.card.uuid
        );
        draft.cards[cardIndex].file_id = '';
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
  },
});
</script>

<style scoped>
.memory-card-image-and-button-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  gap: 0.5em;
}

.memory-card-image {
  max-width: 100%;
  max-height: 480px;
}
</style>
