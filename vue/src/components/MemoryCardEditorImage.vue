<template>
  <div class="memory-card-image-and-button-container" v-disable-drag>
    <div class="memory-card">
      <img
        :src="fileIdToUrl(image.file_id)"
        :alt="image.altText"
        class="memory-card-image"
      />
    </div>
    <button
      type="button"
      @click="deleteImage"
      class="button delete-image-button"
    >
      {{ $gettext('Bild Löschen') }}
    </button>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { fileIdToUrl, Image, MemoryTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import produce from 'immer';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'MemoryCardEditorImage',
  props: {
    image: {
      type: Object as PropType<Image>,
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
        const card = draft.cards.find(
          (card) =>
            card.first.uuid === this.image.uuid ||
            card.second?.uuid === this.image.uuid
        );

        if (card) {
          if (card.first.uuid === this.image.uuid) {
            card.first.file_id = '';
          } else if (card.second?.uuid === this.image.uuid) {
            card.second = undefined;
          }
        }
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
  flex-direction: row;
  justify-content: flex-start;
  align-items: flex-end;
  gap: 0.5em;
}

.memory-card {
  display: flex;
  justify-content: center;
  align-items: center;

  width: 100%;
  max-width: 14em;
  aspect-ratio: 1;

  border: 2px solid #d0d7e3;
  color: #28497c;
  background: #e7ebf1;
}

.memory-card-image {
  width: 100%;
  aspect-ratio: 1;
  object-fit: contain;
}

.delete-image-button {
  margin: 0;
}
</style>
