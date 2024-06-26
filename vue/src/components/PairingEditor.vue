<template>
  <div class="main-flex">
    <div class="h5p-elements-overview">
      <ElementPair
        v-for="(pair, index) in taskDefinition.pairs"
        :key="pair.uuid"
        :class="{
          selected: index === this.selectedPairIndex,
        }"
        :pair="this.taskDefinition.pairs[index]"
        @click="selectPair(index)"
      />
      <!-- TODO Make this button look good -->
      <button type="button" class="button add" @click="addPair">
        {{ $gettext('Pair hinzufügen') }}
      </button>
    </div>
    <div class="h5p-elements-settings">
      <form class="default">
        <fieldset>
          <label>{{ $gettext('Eigenschaften') }}</label>
          <label>{{ $gettext('Karte A') }}</label>
          <label>
            {{ $gettext('Typ') }}
            <select v-model="selectedPair.draggableElement.type">
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
            v-if="selectedPair.draggableElement.type == 'image'"
            class="h5p-element-image-container"
          >
            <template v-if="selectedPair.draggableElement.file_id">
              <edited-image-pair-image :image="selectedPair.draggableElement" />
              <button
                type="button"
                @click="removeDraggableImage(this.selectedPairIndex)"
                v-text="$gettext('Bild löschen')"
              />
            </template>
            <FileUpload
              class="pairing-file-upload"
              v-else
              @file-uploaded="
                onUploadDraggableImage(this.selectedPairIndex, $event)
              "
            />
          </div>
          <label>{{ $gettext('Karte B') }}</label>
          <label>
            {{ $gettext('Typ') }}
            <select v-model="selectedPair.targetElement.type">
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
            v-if="selectedPair.targetElement.type == 'image'"
            class="h5p-element-image-container"
          >
            <template v-if="selectedPair.targetElement.file_id">
              <edited-image-pair-image :image="selectedPair.targetElement" />
              <button
                type="button"
                @click="removeTargetImage(this.selectedPairIndex)"
                v-text="$gettext('Bild löschen')"
              />
            </template>
            <FileUpload
              class="pairing-file-upload"
              v-else
              @file-uploaded="
                onUploadTargetImage(this.selectedPairIndex, $event)
              "
            />
          </div>
        </fieldset>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { fileIdToUrl, Pair, PairingTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import produce from 'immer';
import { taskEditorStore } from '@/store';
import { v4 } from 'uuid';
import ElementPair from '@/components/ElementPair.vue';
import FileUpload from '@/components/FileUpload.vue';
import { FileRef } from '@/routes/jsonApi';
import EditedImagePairImage from '@/components/EditedImagePairImage.vue';

export default defineComponent({
  name: 'PairingEditor',
  components: { EditedImagePairImage, FileUpload, ElementPair },
  props: {
    task: {
      type: Object as PropType<PairingTask>,
      required: true,
    },
  },
  data() {
    return {
      selectedPairIndex: -1,
    };
  },
  beforeMount(): void {
    if (this.taskDefinition.pairs.length > 0) {
      this.selectedPairIndex = 0;
    }
  },
  methods: {
    fileIdToUrl,
    $gettext,
    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },
    selectPair(index: number) {
      this.selectedPairIndex = index;
    },
    addPair() {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs.push({
          uuid: v4(),
          draggableElement: {
            uuid: v4(),
            type: 'image',
            file_id: '',
            altText: '',
          },
          targetElement: {
            uuid: v4(),
            type: 'image',
            file_id: '',
            altText: '',
          },
        });
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
      // Select the newly inserted card
      this.selectedPairIndex = this.taskDefinition.pairs.length - 1;
    },
    deletePair(index: number) {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs.splice(index, 1);
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
      // Adjust the selection index so the selected card doesn't unexpectedly change
      if (index <= this.selectedPairIndex) {
        this.selectedPairIndex = this.selectedPairIndex - 1;
      }
    },
    onUploadDraggableImage(pairIndex: number, file: FileRef): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs[pairIndex].draggableElement.file_id = file.id;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
    removeDraggableImage(pairIndex: number) {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs[pairIndex].draggableElement = {
          uuid: v4(),
          type: 'image',
          file_id: '',
          altText: '',
        };
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
    onUploadTargetImage(pairIndex: number, file: FileRef): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs[pairIndex].targetElement.file_id = file.id;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
    removeTargetImage(pairIndex: number) {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs[pairIndex].targetElement = {
          uuid: v4(),
          type: 'image',
          file_id: '',
          altText: '',
        };
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as PairingTask,
    selectedPair(): Pair {
      return this.taskDefinition.pairs[this.selectedPairIndex];
    },
  },
});
</script>

<style>
.pairing-file-upload,
.pairing-file-upload input[type='file'] {
  max-width: 100%;
}
</style>

<style scoped>
.main-flex {
  display: flex;
  flex-direction: row;
  gap: 1em;
}

.h5p-elements-overview {
  display: flex;
  flex-grow: 1;
  flex-wrap: wrap;
  align-content: flex-start; /* Alignment of multiple lines when there is extra space in the vertical axis. */
  column-gap: 0.5em;
  row-gap: 1em;
}

.h5p-elements-settings {
  flex-grow: 0;
  flex-basis: 250px;
  flex-shrink: 0;
}

.h5p-element-image-container {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  gap: 0.5em;
}
</style>
