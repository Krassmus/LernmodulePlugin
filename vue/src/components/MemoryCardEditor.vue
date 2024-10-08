<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Karte') }}</legend>
      <label for="firstImage">{{
        $gettext(
          taskDefinition.cards[cardIndex].second ? 'Erstes Bild' : 'Bild'
        )
      }}</label>
      <div id="firstImage" class="memory-card-preview-and-upload-container">
        <MemoryCardEditorImage
          v-if="taskDefinition.cards[cardIndex].first.file_id"
          :image="taskDefinition.cards[cardIndex].first"
        />
        <FileUpload v-else @file-uploaded="onImageUploaded" />
      </div>
      <label v-if="taskDefinition.cards[cardIndex].first.file_id"
        >{{ $gettext('Alternativer Text') }}
        <input
          type="text"
          :value="taskDefinition.cards[cardIndex].first.altText"
          @input="onInputAltText"
        />
      </label>
      <template v-if="taskDefinition.cards[cardIndex].second">
        <label for="secondImage">{{ $gettext('Zweites Bild') }} </label>
        <div id="secondImage" class="memory-card-preview-and-upload-container">
          <MemoryCardEditorImage
            v-if="taskDefinition.cards[cardIndex].second.file_id"
            :image="taskDefinition.cards[cardIndex].second"
          />
          <FileUpload v-else @file-uploaded="onSecondImageUploaded" />
        </div>
        <label v-if="taskDefinition.cards[cardIndex].second.file_id"
          >{{ $gettext('Alternativer Text') }}
          <input
            type="text"
            :value="taskDefinition.cards[cardIndex].second?.altText"
            @input="onInputSecondImageAltText"
          />
        </label>
      </template>
      <button
        v-else-if="taskDefinition.cards[cardIndex].first.file_id"
        type="button"
        @click="addSecondImage"
        class="button add-image-button"
      >
        {{ $gettext('Zweites Bild hinzufügen') }}
      </button>
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MemoryTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import produce from 'immer';
import { $gettext } from '@/language/gettext';
import FileUpload from '@/components/FileUpload.vue';
import { FileRef } from '@/routes/jsonApi';
import { v4 } from 'uuid';
import MemoryCardEditorImage from '@/components/MemoryCardEditorImage.vue';

export default defineComponent({
  name: 'MemoryCardEditor',
  components: { MemoryCardEditorImage, FileUpload },
  props: {
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
    onImageUploaded(file: FileRef): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.cards[this.cardIndex].first.file_id = file.id;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
    onSecondImageUploaded(file: FileRef): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.cards[this.cardIndex].second = {
          uuid: v4(),
          v: 2,
          file_id: file.id,
          altText: '',
        };
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
        draft.cards[this.cardIndex].first.altText = value;
      });
      taskEditorStore.performEdit({
        newTaskDefinition,
        undoBatch: { type: 'EditedAltText' },
      });
    },
    // This is what results if you write v-model="taskDefinition.cards[this.cardIndex].altText"
    onInputAltTextBAD(ev: InputEvent): void {
      const value = (ev.target as HTMLInputElement).value;
      this.taskDefinition.cards[this.cardIndex].first.altText = value;
    },

    onInputSecondImageAltText(ev: InputEvent): void {
      const value = (ev.target as HTMLInputElement).value;
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        const card = draft.cards[this.cardIndex];
        if (card && card.second) {
          draft.cards[this.cardIndex].second!.altText = value;
        }
      });
      taskEditorStore.performEdit({
        newTaskDefinition,
        undoBatch: { type: 'EditedAltText' },
      });
    },
    addSecondImage(): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.cards[this.cardIndex].second = {
          uuid: v4(),
          v: 2,
          file_id: '',
          altText: '',
        };
      });
      taskEditorStore.performEdit({
        newTaskDefinition,
        undoBatch: { type: 'AddedSecondImage' },
      });
    },
  },
});
</script>

<style scoped>
.add-image-button {
  margin: 0;
}

.memory-card-preview-and-upload-container {
  margin-bottom: 0.5em;
}
</style>
