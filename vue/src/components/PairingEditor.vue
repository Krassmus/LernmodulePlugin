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
        :pair-index="index"
        @click="selectPair(index)"
      />
    </div>
    <div class="h5p-elements-settings">
      <form class="default">
        <fieldset>
          <label>{{ $gettext('Eigenschaften') }}</label>
          <pre>{{ this.taskDefinition.pairs[this.selectedPairIndex] }}</pre>
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
          <template
            v-if="
              this.taskDefinition.pairs[this.selectedPairIndex].draggableElement
                .type == 'image'
            "
            ><img
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
          /></template>
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
          <template
            v-if="
              this.taskDefinition.pairs[this.selectedPairIndex].targetElement
                .type == 'image'
            "
            ><img
              :src="
                fileIdToUrl(
                  this.taskDefinition.pairs[this.selectedPairIndex]
                    .targetElement.file_id
                )
              "
              :alt="
                this.taskDefinition.pairs[this.selectedPairIndex].targetElement
                  .altText
              "
              class="h5p-element-image"
          /></template>
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

export default defineComponent({
  name: 'PairingEditor',
  components: { ElementPair },
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
  width: 60%;
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: left;
}

.h5p-elements-settings {
  width: 40%;
}

.h5p-element-image {
  max-width: 8em;
  max-height: 8em;
}
</style>
