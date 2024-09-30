<template>
  <div class="memory-card-image-and-button-container">
    <img
      :src="fileIdToUrl(image.file_id)"
      :alt="image.altText"
      class="memory-card-image"
    />
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
  name: 'EditedMemoryCardImage',
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

.memory-card-image {
  max-width: 100%;
  max-height: 14em;
}

.delete-image-button {
  margin: 0;
}
</style>
