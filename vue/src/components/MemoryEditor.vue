<template>
  <form class="default">
    <fieldset class="main-flex">
      <legend>{{ $gettext('Memory') }}</legend>

      <div class="cards-list">
        <div
          v-for="(card, index) in taskDefinition.cards"
          :key="card.uuid"
          :class="{
            'cards-list-item': true,
            'selected-card': index === selectedCardIndex,
          }"
          @click="selectCard(index)"
        >
          <div v-text="listEntryText(index, card)" class="list-entry-text" />

          <!-- Apply .stop modifier so that the selectCard event handler on the
            parent element doesn't get called when the delete button is clicked -->
          <button
            type="button"
            class="flex-child-element remove-card-button"
            @click.stop="deleteCard(index)"
            :aria-label="$pgettext('Karte im Memory Spiel', 'Karte löschen')"
          >
            <img :src="urlForIcon('trash')" alt="" />
          </button>
        </div>

        <button
          type="button"
          class="button add add-card-button"
          @click="addCard"
        >
          {{ $gettext('Karte hinzufügen') }}
        </button>
      </div>

      <MemoryCardEditor
        v-if="taskDefinition.cards[selectedCardIndex]"
        class="edited-memory-card"
        :card-index="selectedCardIndex"
      />

      <div v-else class="edited-memory-card no-card-selected-placeholder">
        {{ $gettext('Keine Karte ist zum Bearbeiten ausgewählt.') }}
      </div>
    </fieldset>

    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Einstellungen') }}</legend>

      <label>
        <input v-model="taskDefinition.retryAllowed" type="checkbox" />
        {{ $gettext('Mehrere Versuche erlauben') }}
      </label>

      <label>
        <input v-model="taskDefinition.squareLayout" type="checkbox" />
        {{ $gettext('Karten in einem Quadrat positionieren') }}
      </label>

      <label
        >{{ $gettext('Rückseite') }}
        <span
          v-if="taskDefinition.flipside && taskDefinition.flipside.file_id"
          class="flipside-image-and-button-container"
        >
          <span class="memory-card-flipside">
            <img
              :src="fileIdToUrl(taskDefinition.flipside.file_id)"
              :alt="taskDefinition.flipside.altText"
              class="flipside-image"
            />
          </span>

          <button
            @click="deleteFlipsideImage"
            type="button"
            class="button delete-flipside-image-button"
          >
            {{ $gettext('Bild Löschen') }}
          </button>
        </span>

        <FileUpload v-else @file-uploaded="onFlipsideImageUploaded" />
      </label>
    </fieldset>

    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Beschriftungen') }}</legend>

      <label :class="{ 'setting-disabled': !taskDefinition.retryAllowed }">
        {{ $gettext('Text für Wiederholen-Button:') }}
        <input
          v-model="taskDefinition.strings.retryButton"
          :disabled="!taskDefinition.retryAllowed"
          type="text"
        />
      </label>
    </fieldset>

    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Feedback') }}</legend>

      <label>
        {{ $gettext('Ergebnismitteilung:') }}
        <input v-model="taskDefinition.strings.resultMessage" type="text" />
      </label>
    </fieldset>
  </form>
</template>

<style scoped>
.main-flex {
  display: flex;
  gap: 1em;
}

.cards-list {
  display: flex;
  flex-direction: column;
  flex: 0 0 200px;
  max-width: 14em;
}

.cards-list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: transparent 2px solid;
  padding: 2px;
  cursor: pointer;
  user-select: none;
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

.list-entry-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 11em;
}

.remove-card-button {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  border: 1px solid #28497c;
  color: #28497c;
  background: #fff;
  cursor: pointer;
}

.remove-card-button:hover {
  background: rgba(109, 114, 122, 0.2);
}

.add-card-button {
  margin: 0.8em 0 0 0;
}

.delete-flipside-image-button {
  margin: 0;
}

.flipside-image-and-button-container {
  display: flex;
  flex-direction: row;
  justify-content: flex-start;
  align-items: flex-end;
  gap: 0.5em;
}

.memory-card-flipside {
  display: flex;
  justify-content: center;
  align-items: center;

  width: 100%;
  max-width: 14em;
  aspect-ratio: 1;

  border: 2px solid #d0d7e3;
  color: #28497c;
  background: #e7ebf1;
}

.flipside-image {
  width: 100%;
  aspect-ratio: 1;
  object-fit: contain;
}
</style>

<script lang="ts">
import { defineComponent } from 'vue';
import { taskEditorStore } from '@/store';
import { fileIdToUrl, MemoryCard, MemoryTask } from '@/models/TaskDefinition';
import { $gettext, $pgettext } from '@/language/gettext';
import produce from 'immer';
import { v4 } from 'uuid';
import FileUpload from '@/components/FileUpload.vue';
import { FileRef } from '@/routes/jsonApi';
import MemoryEditorCard from '@/components/MemoryEditorCard.vue';

export default defineComponent({
  name: 'MemoryEditor',
  components: { MemoryEditorCard, FileUpload },
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
    fileIdToUrl,
    $gettext,
    $pgettext,
    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },
    selectCard(index: number) {
      this.selectedCardIndex = index;
    },
    listEntryText(index: number, card: MemoryCard) {
      let text = index + 1 + '. ';

      if (card.first.altText != '') {
        text += card.first.altText;

        if (card.second && card.second?.altText != '') {
          text += ' ' + $gettext('und') + ' ' + card.second.altText;
        }
      } else {
        text += $gettext('Paar');
      }
      return text;
    },
    addCard() {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.cards.push({
          first: {
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
    onFlipsideImageUploaded(file: FileRef): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.flipside = {
          uuid: v4(),
          v: 2,
          file_id: file.id,
          altText: '',
        };
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
    deleteFlipsideImage(): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.flipside = undefined;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as MemoryTask,
  },
});
</script>
