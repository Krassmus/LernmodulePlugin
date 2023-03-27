<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Karte') }}</legend>
      <label
        >{{ $gettext('Frage') }}
        <input type="text" :value="card.question" />
      </label>
      <label
        >{{ $gettext('Antwort') }}
        <input type="text" :value="card.answer" />
      </label>
      <label>
        {{ $gettext('Bild') }}
        <EditedFlashCardImage v-if="card.imageUrl" :card="card" />
        <ImageUpload v-else @imageUploaded="onImageUploaded" />
      </label>
      <label
        >{{ $gettext('Alternativer Text') }}
        <input type="text" :value="card.altText" />
      </label>
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FlashCard, FlashCardTaskDefinition } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import ImageUpload from '@/components/ImageUpload.vue';
import EditedFlashCardImage from '@/components/EditedFlashCardImage.vue';

export default defineComponent({
  name: 'EditedFlashCard',
  components: { EditedFlashCardImage, ImageUpload },
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
    onImageUploaded(imageUrl: string): void {
      const newCards = [...this.taskDefinition.cards];
      const cardIndex = newCards.findIndex(
        (card) => card.uuid === this.card.uuid
      );
      newCards[cardIndex] = {
        ...newCards[cardIndex],
        imageUrl,
      };
      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...this.taskDefinition,
          cards: newCards,
        },
        undoBatch: {},
      });
    },
  },
});
</script>

<style scoped></style>
