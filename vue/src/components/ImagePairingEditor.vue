<template>
  <div class="main-flex">
    <div class="h5p-elements-overview">
      <ElementPair
        v-for="(pair, index) in taskDefinition.imagePairs"
        :key="pair.uuid"
        :class="{
          selected: index === this.selectedPairIndex,
        }"
        :pair="this.taskDefinition.imagePairs[index]"
        :pair-index="index"
        @click="selectPair(index)"
      />
    </div>
    <div class="h5p-elements-settings">
      <form class="default">
        <fieldset>
          <label>Einstellungen</label>
        </fieldset>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ImagePairingTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import produce from 'immer';
import { taskEditorStore } from '@/store';
import { v4 } from 'uuid';
import ElementPair from '@/components/ElementPair.vue';

export default defineComponent({
  name: 'ImagePairingEditor',
  components: { ElementPair },
  props: {
    task: {
      type: Object as PropType<ImagePairingTask>,
      required: true,
    },
  },
  data() {
    return {
      selectedPairIndex: -1,
    };
  },
  beforeMount(): void {
    if (this.taskDefinition.imagePairs.length > 0) {
      this.selectedPairIndex = 0;
    }
  },
  methods: {
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
        draft.imagePairs.push({
          uuid: v4(),
          draggableImage: {
            uuid: v4(),
            imageUrl: '',
            altText: '',
          },
          targetImage: {
            uuid: v4(),
            imageUrl: '',
            altText: '',
          },
        });
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
      // Select the newly inserted card
      this.selectedPairIndex = this.taskDefinition.imagePairs.length - 1;
    },
    deletePair(index: number) {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.imagePairs.splice(index, 1);
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
    taskDefinition: () => taskEditorStore.taskDefinition as ImagePairingTask,
  },
});
</script>

<style scoped>
.main-flex {
  display: flex;
  justify-content: space-between;
  width: 100%;
  gap: 1em;
}

.cards-list {
  display: flex;
  flex-direction: column;
  flex: 0 0 200px;
}

.cards-list-item {
  display: flex;
  justify-content: space-between;
}

.selected-card {
  border: #0a78d1 2px solid;
}

.h5p-elements-overview {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: left;
}
</style>
