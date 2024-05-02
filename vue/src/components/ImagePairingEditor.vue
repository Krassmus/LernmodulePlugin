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
          <label>{{ $gettext('Eigenschaften') }}</label>
          <pre>{{
            this.taskDefinition.imagePairs[this.selectedPairIndex]
          }}</pre>
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
            v: 2,
            uuid: v4(),
            file_id: '',
            altText: '',
          },
          targetImage: {
            v: 2,
            uuid: v4(),
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
</style>
