<template>
  <div>
    <label v-if="debug">
      {{ $gettext('Typ') }}
      <select :value="card.type" @change="onChangeType($event)">
        <option :value="'image'">
          {{ $gettext('Bild') }}
        </option>
        <option :value="'text'">
          {{ $gettext('Text') }}
        </option>
      </select>
    </label>

    <div v-if="card.type == 'image'" class="h5p-element-image-container">
      <template v-if="card.file_id">
        <MultimediaElement :element="card" />
        <button
          type="button"
          @click="onClickDeleteImage()"
          v-text="$gettext('Bild löschen')"
          class="button trash element-pair-settings-item"
        />
      </template>
      <FileUpload
        class="pairing-file-upload"
        v-else
        @file-uploaded="onUploadImage($event)"
      />
      <label style="align-self: stretch">
        {{ $gettext('Bildbeschreibung') }}
        <input
          type="text"
          :value="card.altText"
          :placeholder="
            $gettext(
              'Geben Sie eine Bildbeschreibung ein, diese wird als Beschriftung und für Screenreader verwendet.'
            )
          "
          @input="onInputAltText($event)"
          class="element-pair-settings-item"
        />
      </label>
    </div>
  </div>
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
  name: 'SequencingEditorImage',
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
.h5p-element-image-container {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  gap: 0.5em;
}
</style>
