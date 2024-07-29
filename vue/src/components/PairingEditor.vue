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
            @click="onClickPair(index)"
          />
          <button
            type="button"
            class="add-pair-button"
            @click="onClickAddPair()"
            :style="addPairButtonBackgroundImage"
          />
        </div>
        <div class="h5p-elements-settings">
          <form class="default" @submit.prevent>
            <div class="h5p-element-setting">
              <h1>{{ $gettext('Karte A') }}</h1>
              <PairingElement
                :multimedia-element="selectedPair.draggableElement"
                @element-changed="onChangeDraggableElement"
              />
            </div>
            <div class="h5p-element-setting">
              <h1>{{ $gettext('Karte B') }}</h1>
              <PairingElement
                :multimedia-element="selectedPair.targetElement"
                @element-changed="onChangeTargetElement"
              />
            </div>
            <div class="remove-pair-button-container">
              <button
                type="button"
                @click="onClickDeletePair(selectedPairIndex)"
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
import {
  fileIdToUrl,
  LernmoduleMultimediaElement,
  Pair,
  PairingTask,
} from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import produce from 'immer';
// TODO Remove uses of taskEditorStore here so that Pairing can be used in Interactive Video.
// (Replace them with injection -- See the usage of performEdit in FillInTheBlanksEditor.vue.)
import { taskEditorStore } from '@/store';
import { v4 } from 'uuid';
import ElementPair from '@/components/ElementPair.vue';
import TabsComponent from '@/components/courseware-components-ported-to-vue3/TabsComponent.vue';
import TabComponent from '@/components/courseware-components-ported-to-vue3/TabComponent.vue';
import PairingViewer from '@/components/PairingViewer.vue';
import PairingElement from '@/components/PairingElement.vue';

export default defineComponent({
  name: 'PairingEditor',
  components: {
    PairingViewer,
    TabComponent,
    TabsComponent,
    ElementPair,
    PairingElement,
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
    onClickPair(index: number) {
      this.selectedPairIndex = index;
    },
    onClickAddPair() {
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
    onClickDeletePair(index: number) {
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
    onChangeDraggableElement(payload: {
      updatedElement: LernmoduleMultimediaElement;
      undoBatch?: unknown;
    }): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs[this.selectedPairIndex].draggableElement =
          payload.updatedElement;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: payload.undoBatch ?? {},
      });
    },
    onChangeTargetElement(payload: {
      updatedElement: LernmoduleMultimediaElement;
      undoBatch?: unknown;
    }): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs[this.selectedPairIndex].targetElement =
          payload.updatedElement;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: payload.undoBatch ?? {},
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
.h5p-element-setting + .h5p-element-setting {
  margin-top: 2ex;
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

.remove-pair-button {
  margin-right: 0;
}

.remove-pair-button-container {
  text-align: end;
  margin-top: 0.5em;
}
</style>
