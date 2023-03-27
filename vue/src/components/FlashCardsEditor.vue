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
        <button type="button" @click="deleteCard(index)">X</button>
      </div>
      <button type="button" @click="addCard">
        {{ $gettext('Karte hinzufügen') }}
      </button>
    </div>

    <EditedFlashCard
      v-if="this.taskDefinition.cards[this.selectedCardIndex]"
      class="edited-flash-card"
      :card="this.taskDefinition.cards[this.selectedCardIndex]"
    />
    <div v-else class="edited-flash-card no-card-selected-placeholder">
      {{ $gettext('Keine Karte ist zum Bearbeiten ausgewählt.') }}
    </div>
  </div>
  <label>
    {{ this.debug }}
  </label>
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
.edited-flash-card {
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
import { defineComponent } from 'vue';
import { FlashCard, FlashCardTaskDefinition } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import { uploadImage } from '@/routes';
import EditedFlashCard from '@/components/EditedFlashCard.vue';
import { v4 } from 'uuid';

export default defineComponent({
  name: 'FlashCardsEditor',
  components: { EditedFlashCard },
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as FlashCardTaskDefinition,
  },
  methods: {
    async onClickUpload() {
      try {
        const res = await this.uploadImage();
        console.log('image upload result: ', res);
      } catch (error) {
        console.error('image upload failed. ', error);
      }
    },
    async uploadImage() {
      const blob = new Blob();
      return uploadImage(blob);
    },
    selectCard(index: number) {
      this.selectedCardIndex = index;
    },
    addCard() {
      const newCards: FlashCard[] = [
        ...this.taskDefinition.cards,
        {
          uuid: v4(),
          question: '',
          answer: '',
        },
      ];
      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...this.taskDefinition,
          cards: newCards,
        },
        undoBatch: {},
      });
      if (this.selectedCardIndex < 0) {
        this.selectedCardIndex = 0;
      }
    },
    deleteCard(index: number) {
      const newCards = [...this.taskDefinition.cards];
      newCards.splice(index, 1);
      if (index === this.selectedCardIndex) {
        this.selectedCardIndex = -1;
      } else if (index < this.selectedCardIndex) {
        this.selectedCardIndex--;
      }
      taskEditorStore.performEdit({
        newTaskDefinition: {
          ...this.taskDefinition,
          cards: newCards,
        },
        undoBatch: {},
      });
    },
  },
  data() {
    return {
      debug: '' as string,
      selectedCardIndex: -1,
    };
  },
  beforeMount(): void {
    if (this.taskDefinition.cards.length > 0) {
      this.selectedCardIndex = 0;
    }
  },
});
</script>
