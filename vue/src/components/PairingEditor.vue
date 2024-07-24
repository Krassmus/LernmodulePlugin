<template>
  <TabsComponent>
    <TabComponent :title="$gettext('1. Aufgabe bearbeiten')" icon="content">
      <div class="main-flex">
        <div class="h5p-elements-overview">
          <ElementPair
            v-for="(pair, index) in taskDefinition.pairs"
            :key="pair.uuid"
            :class="{
              selected: index === selectedPairIndex,
            }"
            :pair="taskDefinition.pairs[index]"
            @click="selectPair(index)"
          />
          <button
            type="button"
            class="add-pair-button"
            @click="addPair()"
            :style="addPairButtonBackgroundImage"
          />
        </div>
        <div class="h5p-elements-settings">
          <form class="default" @submit.prevent>
            <div class="h5p-element-settings">
              <h1>{{ $gettext('Karte A') }}</h1>
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
                  <MultimediaElement
                    :element="selectedPair.draggableElement"
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
                      v-model="selectedPair.draggableElement.altText"
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

              <div v-else-if="selectedPair.draggableElement.type == 'text'">
                <label style="align-self: stretch">
                  {{ $gettext('Inhalt') }}
                  <textarea
                    type="text"
                    v-model="selectedPair.draggableElement.content"
                    class="element-pair-settings-item"
                  />
                </label>
              </div>
            </div>
            <div class="h5p-element-settings">
              <h1>{{ $gettext('Karte B') }}</h1>
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
                  <MultimediaElement
                    :element="selectedPair.targetElement"
                    class="h5pMultimediaElement"
                  />
                  <button
                    type="button"
                    @click="removeTargetImage(this.selectedPairIndex)"
                    v-text="$gettext('Bild löschen')"
                    class="button trash element-pair-settings-item"
                  />
                  <label style="align-self: stretch">
                    {{ $gettext('Alt-Text') }}
                    <input
                      type="text"
                      v-model="selectedPair.targetElement.altText"
                      class="element-pair-settings-item"
                    />
                  </label>
                </template>
                <FileUpload
                  class="pairing-file-upload"
                  v-else
                  @file-uploaded="
                    onUploadTargetImage(this.selectedPairIndex, $event)
                  "
                />
              </div>

              <div v-else-if="selectedPair.targetElement.type == 'text'">
                <label style="align-self: stretch">
                  {{ $gettext('Inhalt') }}
                  <textarea
                    type="text"
                    v-model="selectedPair.targetElement.content"
                    class="element-pair-settings-item"
                  />
                </label>
              </div>
            </div>

            <div class="remove-pair-button-container">
              <button
                type="button"
                @click="deletePair(selectedPairIndex)"
                v-text="$gettext('Paar löschen')"
                class="button trash remove-pair-button"
              />
            </div>
          </form>
        </div>
      </div>
    </TabComponent>
    <TabComponent :title="$gettext('2. Vorschau')" icon="visibility-visible">
      <PairingViewer :task="taskDefinition" />
    </TabComponent>
    <TabComponent v-if="debug" title="Debug" icon="tools">
      <pre v-text="taskDefinition" />
    </TabComponent>
  </TabsComponent>
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
import MultimediaElement from '@/components/MultimediaElement.vue';
import TabsComponent from '@/components/courseware-components-ported-to-vue3/TabsComponent.vue';
import TabComponent from '@/components/courseware-components-ported-to-vue3/TabComponent.vue';
import PairingViewer from '@/components/PairingViewer.vue';

export default defineComponent({
  name: 'PairingEditor',
  components: {
    PairingViewer,
    TabComponent,
    TabsComponent,
    MultimediaElement,
    FileUpload,
    ElementPair,
  },
  props: {
    taskDefinition: {
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
        draft.pairs[pairIndex].draggableElement = {
          uuid: v4(),
          type: 'image',
          file_id: file.id,
          altText: '',
        };
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
        draft.pairs[pairIndex].targetElement = {
          uuid: v4(),
          type: 'image',
          file_id: file.id,
          altText: '',
        };
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
    debug: () => window.STUDIP.LernmoduleVueJS.LERNMODULE_DEBUG,
    selectedPair(): Pair {
      return this.taskDefinition.pairs[this.selectedPairIndex];
    },
    addPairButtonBackgroundImage() {
      return {
        backgroundImage: `url(${this.urlForIcon('add')})`,
      };
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
  gap: 0.5em;
}

.h5p-elements-overview {
  flex-grow: 1;
  /* Adapted from https://stackoverflow.com/a/46099319/7359454 */
  display: grid;
  grid-template-columns: repeat(auto-fill, 8em);
  grid-auto-rows: max-content;
  justify-content: space-around;
  row-gap: 1em;
  column-gap: 0.5em;
  padding: 0.5em 0.5em 0;
}

.h5p-elements-settings {
  flex-grow: 0;
  flex-shrink: 0;
  width: 275px;
  padding: 0.5em 0.5em 0;
}
.h5p-element-settings + .h5p-element-settings {
  margin-top: 1.5ex;
}

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

.add-pair-button {
  box-sizing: border-box;
  height: 8em;
  width: 8em;

  margin: 2px;
  padding: 0;

  border: solid 2px rgba(0, 0, 0, 0);
  border-radius: 0.25em;

  background-size: 40%;
  background-repeat: no-repeat;
  background-position: center;
}

.element-pair-settings-item {
  /* top | right | bottom | left */
  margin: 0.25em 0 0 0;
}

.remove-pair-button {
  margin-right: 0;
}

.remove-pair-button-container {
  text-align: end;
  margin-top: 0.5em;
}
</style>
