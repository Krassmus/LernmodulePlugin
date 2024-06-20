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
    </div>
    <div class="h5p-elements-settings">
      <form class="default">
        <fieldset>
          <label>{{ $gettext('Eigenschaften') }}</label>
          <label>{{ $gettext('Karte A') }}</label>
          <label>
            {{ $gettext('Typ') }}
            <select
              v-model="
                this.taskDefinition.pairs[this.selectedPairIndex]
                  .draggableElement.type
              "
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
            v-if="
              this.taskDefinition.pairs[this.selectedPairIndex].draggableElement
                .type == 'image'
            "
            class="h5p-element-image-container"
          >
            <template
              v-if="
                this.taskDefinition.pairs[this.selectedPairIndex]
                  .draggableElement.file_id
              "
            >
              <img
                :src="
                  fileIdToUrl(
                    this.taskDefinition.pairs[this.selectedPairIndex]
                      .draggableElement.file_id
                  )
                "
                :alt="
                  this.taskDefinition.pairs[this.selectedPairIndex]
                    .draggableElement.altText
                "
                class="h5p-element-image"
              />
              <button
                @click="removeDraggableImage(this.selectedPairIndex)"
                type="button"
              >
                {{ $gettext('Bild löschen') }}
              </button>
            </template>
            <FileUpload
              v-else
              @file-uploaded="
                onUploadDraggableImage(this.selectedPairIndex, $event)
              "
            />
          </div>
          <label>{{ $gettext('Karte B') }}</label>
          <label>
            {{ $gettext('Typ') }}
            <select
              v-model="
                this.taskDefinition.pairs[this.selectedPairIndex].targetElement
                  .type
              "
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
            v-if="
              this.taskDefinition.pairs[this.selectedPairIndex].targetElement
                .type == 'image'
            "
            class="h5p-element-image-container"
          >
            <template
              v-if="
                this.taskDefinition.pairs[this.selectedPairIndex].targetElement
                  .file_id
              "
            >
              <img
                :src="
                  fileIdToUrl(
                    this.taskDefinition.pairs[this.selectedPairIndex]
                      .targetElement.file_id
                  )
                "
                :alt="
                  this.taskDefinition.pairs[this.selectedPairIndex]
                    .targetElement.altText
                "
                class="h5p-element-image"
              />
              <button
                @click="removeTargetImage(this.selectedPairIndex)"
                type="button"
              >
                {{ $gettext('Bild löschen') }}
              </button>
            </template>
            <FileUpload
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
import { fileIdToUrl, PairingTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import produce from 'immer';
import { taskEditorStore } from '@/store';
import { v4 } from 'uuid';
import ElementPair from '@/components/ElementPair.vue';
import FileUpload from '@/components/FileUpload.vue';
import { FileRef } from '@/routes/jsonApi';

export default defineComponent({
  name: 'PairingEditor',
  components: { FileUpload, ElementPair },
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
  },
});
</script>

<style scoped>
.main-flex {
  display: flex;
  flex-direction: row;
}

.h5p-elements-overview {
  width: 70%;
  display: flex;
  flex-wrap: wrap;
  align-content: flex-start; /* Alignment of multiple lines when there is extra space in the vertical axis. */
  column-gap: 0.5em;
  row-gap: 1em;
}

.h5p-elements-settings {
  width: 30%;
}

.h5p-element-image-container {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
}

.h5p-element-image {
  width: 8em;
  height: 8em;
  object-fit: contain;
  border: #888888 1px solid;
  border-radius: 0.25em;
  padding: 0.25em;
  margin-bottom: 0.5em; /* distance to delete image button */
}
</style>
