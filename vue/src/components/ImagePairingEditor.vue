<template>
  <div class="main-flex">
    <div class="cards-list">
      <div
        v-for="(pair, index) in taskDefinition.imagePairs"
        :key="pair.uuid"
        :class="{
          'cards-list-item': true,
          'selected-card': index === this.selectedPairIndex,
        }"
        @click="selectPair(index)"
      >
        {{ index }}.
        <!-- Apply .stop modifier to @click so that the click event handler on the
            parent element doesn't get called when the delete button is clicked -->
        <img
          class="flex-child-element removeAnswerButton"
          :src="urlForIcon('trash')"
          @click.stop="deletePair(index)"
          alt="an icon showing a trash bin"
        />
      </div>
      <button type="button" @click="addPair">
        {{ $gettext('Karte hinzufügen') }}
      </button>
    </div>

    <EditedImagePair
      v-if="this.taskDefinition.imagePairs[this.selectedPairIndex]"
      class="edited-memory-card"
      :pair="this.taskDefinition.imagePairs[this.selectedPairIndex]"
      :pair-index="this.selectedPairIndex"
    />
    <div v-else class="edited-memory-card no-card-selected-placeholder">
      {{ $gettext('Keine Karte ist zum Bearbeiten ausgewählt.') }}
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import EditedImagePair from '@/components/EditedImagePair.vue';
import { ImagePairingTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import produce from 'immer';
import { taskEditorStore } from '@/store';
import { v4 } from 'uuid';

export default defineComponent({
  name: 'ImagePairingEditor',
  components: { EditedImagePair },
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

.edited-memory-card {
  flex: 1 1 auto;
}

.no-card-selected-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 200px;
}
</style>
