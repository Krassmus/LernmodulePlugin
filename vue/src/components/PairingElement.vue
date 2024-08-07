<template>
  <div>
    <div class="h5p-element-settings">
      <label>
        {{ $gettext('Typ') }}
        <select :value="multimediaElement.type" @change="onChangeType($event)">
          <option :value="'image'">
            {{ $gettext('Bild') }}
          </option>
          <option :value="'text'">
            {{ $gettext('Text') }}
          </option>
          <option :value="'audio'">
            {{ $gettext('Audio') }}
          </option>
        </select>
      </label>
      <div
        v-if="multimediaElement.type == 'image'"
        class="h5p-element-image-container"
      >
        <template v-if="multimediaElement.file_id">
          <MultimediaElement
            :element="multimediaElement"
            class="h5pMultimediaElement"
          />
          <button
            type="button"
            @click="onClickRemoveImage()"
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
          {{ $gettext('Alt-Text') }}
          <input
            type="text"
            :value="multimediaElement.altText"
            @input="onInputAltText($event)"
            class="element-pair-settings-item"
          />
        </label>
      </div>
      <div v-else-if="multimediaElement.type == 'text'">
        <label style="align-self: stretch">
          {{ $gettext('Inhalt') }}
          <textarea
            type="text"
            :value="multimediaElement.content"
            @input="onInputContent($event)"
            class="element-pair-settings-item"
          />
        </label>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  ImageElement,
  LernmoduleMultimediaElement,
  MultimediaElementType,
} from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import MultimediaElement from '@/components/MultimediaElement.vue';
import FileUpload from '@/components/FileUpload.vue';
import { v4 } from 'uuid';
import { FileRef } from '@/routes/jsonApi';

export default defineComponent({
  name: 'PairingElement',
  components: { FileUpload, MultimediaElement },
  props: {
    multimediaElement: {
      type: Object as PropType<LernmoduleMultimediaElement>,
      required: true,
    },
  },
  emits: {
    elementChanged(payload: {
      updatedElement: LernmoduleMultimediaElement;
      undoBatch?: unknown;
    }) {
      return true;
    },
  },
  data() {
    return {};
  },
  methods: {
    $gettext,
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
    onChangeType(event: Event) {
      const target = event.target as HTMLSelectElement;
      const selectedType = target.value as MultimediaElementType;
      this.$emit('elementChanged', {
        updatedElement: this.createNewLernmoduleMultimediaElement(selectedType),
      });
    },
    onClickRemoveImage() {
      this.$emit('elementChanged', {
        updatedElement: {
          ...(this.multimediaElement as ImageElement),
          file_id: '',
        },
      });
    },
    onUploadImage(file: FileRef): void {
      const updatedElement = { ...this.multimediaElement, file_id: file.id };
      this.$emit('elementChanged', { updatedElement });
    },
    onInputAltText(event: Event) {
      const target = event.target as HTMLInputElement;
      const newAltText = target.value;
      const updatedElement = { ...this.multimediaElement, altText: newAltText };
      this.$emit('elementChanged', {
        updatedElement,
        undoBatch: { field: 'altText', uuid: this.multimediaElement.uuid },
      });
    },
    onInputContent(event: Event) {
      const target = event.target as HTMLInputElement;
      const newContent = target.value;
      const updatedElement = { ...this.multimediaElement, content: newContent };
      this.$emit('elementChanged', {
        updatedElement,
        undoBatch: { field: 'content', uuid: this.multimediaElement.uuid },
      });
    },
  },
  computed: {},
});
</script>

<style>
.pairing-file-upload,
.pairing-file-upload input[type='file'] {
  max-width: 100%;
}
</style>

<style scoped>
.h5p-element-image-container {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  gap: 0.5em;
}

.h5pMultimediaElement {
  width: 8em;
  height: 8em;
}

.element-pair-settings-item {
  /* top | right | bottom | left */
  margin: 0.25em 0 0 0;
}
</style>
