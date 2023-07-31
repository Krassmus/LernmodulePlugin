<template>
  <div class="main-flex">
    <div class="cards-list">
      <div
        v-for="(card, index) in taskDefinition.cards"
        :key="card.uuid"
        :class="{
          'cards-list-item': true,
          'selected-card': index === this.selectedCardIndex,
        }"
        @click="selectCard(index)"
      >
        {{ index }}.
        {{ card.question === '' ? $gettext('Karte') : card.question }}
        <!-- Apply .stop modifier so that the selectCard event handler on the
            parent element doesn't get called when the delete button is clicked -->
        <img
          class="flex-child-element removeAnswerButton"
          :src="urlForIcon('trash')"
          @click="deleteCard(index)"
          alt="an icon showing a trash bin"
        />
      </div>
      <button type="button" @click="addCard">
        {{ $gettext('Karte hinzufügen') }}
      </button>
    </div>

    <EditedMemoryCard
      v-if="this.taskDefinition.cards[this.selectedCardIndex]"
      class="edited-memory-card"
      :card="this.taskDefinition.cards[this.selectedCardIndex]"
      :card-index="this.selectedCardIndex"
    />
    <div v-else class="edited-memory-card no-card-selected-placeholder">
      {{ $gettext('Keine Karte ist zum Bearbeiten ausgewählt.') }}
    </div>
  </div>
</template>

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

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { taskEditorStore } from '@/store';
import { MemoryTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import produce from 'immer';
import { v4 } from 'uuid';
import EditedMemoryCard from '@/components/EditedMemoryCard.vue';

export default defineComponent({
  name: 'MemoryEditor',
  components: { EditedMemoryCard },
  props: {},
  data() {
    return {
      selectedCardIndex: -1,
    };
  },
  beforeMount(): void {
    if (this.taskDefinition.cards.length > 0) {
      this.selectedCardIndex = 0;
    }
  },
  methods: {
    $gettext,
    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },
    selectCard(index: number) {
      this.selectedCardIndex = index;
    },
    addCard() {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.cards.push({
          uuid: v4(),
          imageUrl: '',
          altText: '',
        });
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
      // Select the newly inserted card
      this.selectedCardIndex = this.taskDefinition.cards.length - 1;
    },
    deleteCard(index: number) {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.cards.splice(index, 1);
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
      // Adjust the selection index so the selected card doesn't unexpectedly change
      if (index <= this.selectedCardIndex) {
        this.selectedCardIndex = this.selectedCardIndex - 1;
      }
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as MemoryTask,
  },
});
</script>
