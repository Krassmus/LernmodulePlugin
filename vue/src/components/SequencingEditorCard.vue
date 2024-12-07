<template>
  <fieldset class="sequencing-editor-card">
    <legend>{{ $gettext('Karte') }}</legend>

    <label>{{ $gettext('Bild') }}</label>
    <div
      v-if="card.type == 'image' && card.file_id"
      class="image-card-container"
    >
      <div class="multimedia-element-wrapper">
        <MultimediaElement :element="card" />
      </div>

      <button
        type="button"
        @click="onClickDeleteImage"
        class="button trash settings-item"
      >
        {{ $gettext('Bild löschen') }}
      </button>

      <label style="align-self: stretch">
        {{ $gettext('Bildbeschreibung') }}
        <input
          type="text"
          :value="card.altText"
          @input="onInputAltText"
          class="settings-item"
        />
      </label>
    </div>

    <FileUpload v-else @file-uploaded="onUploadImage" />
  </fieldset>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { $gettext } from '@/language/gettext';
import {
  fileIdToUrl,
  ImageElement,
  LernmoduleMultimediaElement,
  MultimediaElementType,
  SequencingTask,
} from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import produce from 'immer';
import FileUpload from '@/components/FileUpload.vue';
import { FileRef } from '@/routes/jsonApi';
import MultimediaElement from '@/components/MultimediaElement.vue';
import { v4 } from 'uuid';

export default defineComponent({
  name: 'SequencingEditorCard',
  components: { MultimediaElement, FileUpload },
  props: {
    card: {
      type: Object as PropType<LernmoduleMultimediaElement>,
      required: true,
    },
    cardIndex: {
      type: Number as PropType<number>,
      required: true,
    },
  },
  data() {
    return {
      debug: false,
    };
  },
  methods: {
    fileIdToUrl,
    $gettext,
    onUploadImage(file: FileRef): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        if (draft.cards[this.cardIndex].type === 'image') {
          (draft.cards[this.cardIndex] as ImageElement).file_id = file.id;
        }
      });
      taskEditorStore.performEdit({
        newTaskDefinition,
        undoBatch: {},
      });
    },
    onInputAltText(ev: InputEvent): void {
      const value = (ev.target as HTMLInputElement).value;
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        if (draft.cards[this.cardIndex].type === 'image') {
          (draft.cards[this.cardIndex] as ImageElement).altText = value;
        }
      });
      taskEditorStore.performEdit({
        newTaskDefinition,
        undoBatch: { type: 'EditedImageAltText' },
      });
    },
    onClickDeleteImage(): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        if (draft.cards[this.cardIndex].type === 'image') {
          (draft.cards[this.cardIndex] as ImageElement).file_id = '';
        }
      });

      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
    onChangeType(event: Event) {
      const target = event.target as HTMLSelectElement;
      const selectedType = target.value as MultimediaElementType;
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.cards[this.cardIndex].type = selectedType;
      });

      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
    createNewLernmoduleMultimediaElement(
      type: MultimediaElementType
    ): LernmoduleMultimediaElement {
      switch (type) {
        case 'image':
          return {
            uuid: v4(),
            type: type,
            file_id: '',
            altText: '',
          };
        case 'audio':
          return {
            uuid: v4(),
            type: type,
            file_id: '',
            altText: '',
          };
        case 'text':
          return {
            uuid: v4(),
            type: type,
            content: '',
          };
        default:
          throw new Error(
            $gettext('Ungültiges MultimediaElementType: ') + type
          );
      }
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as SequencingTask,
  },
});
</script>

<style scoped>
.sequencing-editor-card {
  flex: 1 1 auto;
}

.multimedia-element-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;

  width: 11.5em;
  height: 11.5em;
  padding: 0.5em;

  border: 2px solid #dbe2e8;
  border-radius: 0.5em;
  background: white;
}

.image-card-container {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: flex-start;
  gap: 0.5em;
}

.settings-item {
  /* top | right | bottom | left */
  margin: 0.25em 0 0 0;
}
</style>
