<template>
  <div>
    <div class="h5p-element-settings">
      <label>
        {{ $gettext('Typ') }}
        <select
          :value="multimediaElement.type"
          @change="onSelectionChange($event)"
        >
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
            @click="removeDraggableImage(selectedPairIndex)"
            v-text="$gettext('Bild löschen')"
            class="button trash element-pair-settings-item"
          />
          <label style="align-self: stretch">
            {{ $gettext('Alt-Text') }}
            <input
              type="text"
              :value="multimediaElement.altText"
              class="element-pair-settings-item"
            />
          </label>
        </template>
        <FileUpload
          class="pairing-file-upload"
          v-else
          @file-uploaded="
            onUploadDraggableImage(this.selectedPairIndex, $event)
          "
        />
      </div>
      <div v-else-if="multimediaElement.type == 'text'">
        <label style="align-self: stretch">
          {{ $gettext('Inhalt') }}
          <textarea
            type="text"
            :value="multimediaElement.content"
            class="element-pair-settings-item"
          />
        </label>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { LernmoduleMultimediaElement } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import MultimediaElement from '@/components/MultimediaElement.vue';
import FileUpload from '@/components/FileUpload.vue';
import { v4 } from 'uuid';

export default defineComponent({
  name: 'PairingElement',
  components: { FileUpload, MultimediaElement },
  props: {
    multimediaElement: {
      type: Object as PropType<LernmoduleMultimediaElement>,
      required: true,
    },
  },
  emits: ['elementChanged'],
  data() {
    return {};
  },
  methods: {
    $gettext,
    onSelectionChange(event: Event) {
      const target = event.target as HTMLSelectElement;
      const newType = target.value;
      this.$emit(
        'elementChanged',
        this.createNewLernmoduleMultimediaElement(newType)
      );
    },
    createNewLernmoduleMultimediaElement(
      type: string
    ): LernmoduleMultimediaElement | undefined {
      if (type === 'image') {
        return {
          uuid: v4(),
          type: type,
          file_id: '',
          altText: '',
        };
      } else if (type === 'audio') {
        return {
          uuid: v4(),
          type: type,
          file_id: '',
          altText: '',
        };
      } else if (type === 'text') {
        return {
          uuid: v4(),
          type: type,
          content: '',
        };
      }

      return undefined;
    },
  },
  computed: {},
});
</script>

<style scoped></style>
