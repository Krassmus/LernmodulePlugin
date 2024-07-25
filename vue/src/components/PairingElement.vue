<template>
  <div>
    <div class="h5p-element-settings">
      <label>
        {{ $gettext('Typ') }}
        <select v-model="localElement.type" @change="onSelectionChange($event)">
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
        v-if="localElement.type == 'image'"
        class="h5p-element-image-container"
      >
        <template v-if="localElement.file_id">
          <MultimediaElement
            :element="localElement"
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
              v-model="localElement.altText"
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
      <div v-else-if="localElement.type == 'text'">
        <label style="align-self: stretch">
          {{ $gettext('Inhalt') }}
          <textarea
            type="text"
            v-model="localElement.content"
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
    return {
      localElement: {} as LernmoduleMultimediaElement,
    };
  },
  watch: {
    multimediaElement: {
      immediate: true,
      handler(newVal) {
        this.localElement = { ...newVal };
      },
    },
  },
  methods: {
    $gettext,
    onSelectionChange() {
      this.$emit('elementChanged', this.localElement);
    },
  },
  computed: {},
});
</script>

<style scoped></style>
