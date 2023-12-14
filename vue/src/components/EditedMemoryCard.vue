<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Karte') }}</legend>
      <div>
        <h4>{{ $gettext('Bild') }}</h4>
        <EditedMemoryCardImage v-if="card.imageUrl" :card="card" />
        <FileUpload v-else @file-uploaded="onImageUploaded" />
      </div>
      <label
        >{{ $gettext('Alternativer Text') }}
        <input
          type="text"
          :value="taskDefinition.cards[cardIndex].altText"
          @input="onInputAltText"
        />
      </label>
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MemoryCard, MemoryTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import ImageUpload from '@/components/ImageUpload.vue';
import produce from 'immer';
import { $gettext } from '@/language/gettext';
import EditedMemoryCardImage from '@/components/EditedMemoryCardImage.vue';
import FileUpload from '@/components/FileUpload.vue';

export default defineComponent({
  name: 'EditedMemoryCard',
  components: { FileUpload, EditedMemoryCardImage },
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
    taskDefinition: () => taskEditorStore.taskDefinition as MemoryTask,
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
    // This is how you write the @input event handler if you want undo-redo and reactivity to work
    onInputAltText(ev: InputEvent): void {
      const value = (ev.target as HTMLInputElement).value;
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.cards[this.cardIndex].altText = value;
      });
      taskEditorStore.performEdit({
        newTaskDefinition,
        undoBatch: { type: 'EditedAltText' },
      });
    },
    // This is what results if you write v-model="taskDefinition.cards[this.cardIndex].altText"
    onInputAltTextBAD(ev: InputEvent): void {
      const value = (ev.target as HTMLInputElement).value;
      this.taskDefinition.cards[this.cardIndex].altText = value;
    },
  },
});
</script>

<style scoped></style>
