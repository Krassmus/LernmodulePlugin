<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Karte') }}</legend>
      <div>
        <h4>{{ $gettext('Bild') }}</h4>
        <EditedMemoryCardImage v-if="card.imageUrl" :card="card" />
        <ImageUpload v-else @imageUploaded="onImageUploaded" />
      </div>
      <label
        >{{ $gettext('Alternativer Text') }}
        <input type="text" v-model="taskDefinition.cards[cardIndex].altText" />
      </label>
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MemoryCard, MemoryTaskDefinition } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import ImageUpload from '@/components/ImageUpload.vue';
import produce from 'immer';
import { $gettext } from '@/language/gettext';
import EditedMemoryCardImage from '@/components/EditedMemoryCardImage.vue';

export default defineComponent({
  name: 'EditedMemoryCard',
  components: { EditedMemoryCardImage, ImageUpload },
  props: {
    card: {
      type: Object as PropType<MemoryCard>,
      required: true,
    },
    cardIndex: {
      type: Number as PropType<number>,
      required: true,
    },
  },
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as MemoryTaskDefinition,
  },
  methods: {
    $gettext,
    onImageUploaded(imageUrl: string): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.cards[this.cardIndex].imageUrl = imageUrl;
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
