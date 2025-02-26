<template>
  <div class="stud5p-task">
    <form class="default">
      <fieldset class="memory-editor">
        <legend>{{ $gettext('Memory') }}</legend>

        <div class="cards-list">
          <div
            v-for="(card, index) in modelTaskDefinition.cards"
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

        <div
          v-if="modelTaskDefinition.cards[selectedCardIndex]"
          class="edited-memory-card"
        >
          <MemoryEditorCard
            :card="modelTaskDefinition.cards[selectedCardIndex]"
            @update:card="updateCard"
          />
        </div>

        <fieldset v-else class="no-card-selected-placeholder">
          <legend>
            {{ $gettext('Keine Karte ausgewählt') }}
          </legend>
        </fieldset>
      </fieldset>

      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Einstellungen') }}</legend>

        <label>
          <input
            v-model="modelTaskDefinition.retryAllowed"
            @change="updateTaskDefinition"
            type="checkbox"
          />
          {{ $gettext('Mehrere Versuche erlauben') }}
        </label>

        <label>
          <input
            v-model="modelTaskDefinition.squareLayout"
            @change="updateTaskDefinition"
            type="checkbox"
          />
          {{ $gettext('Karten in einem Quadrat positionieren') }}
        </label>

        <label
          >{{ $gettext('Rückseite') }}
          <span
            v-if="
              modelTaskDefinition.flipside &&
              modelTaskDefinition.flipside.file_id
            "
            class="flipside-image-and-button-container"
          >
            <LazyImage
              :src="fileIdToUrl(modelTaskDefinition.flipside.file_id)"
              :alt="modelTaskDefinition.flipside.altText"
              class="flipside-image"
            />

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

        <label
          :class="{ 'setting-disabled': !modelTaskDefinition.retryAllowed }"
        >
          {{ $gettext('Text für Wiederholen-Button:') }}
          <input
            v-model="modelTaskDefinition.strings.retryButton"
            @input="updateTaskDefinition('taskDefinition.strings.retryButton')"
            :disabled="!modelTaskDefinition.retryAllowed"
            type="text"
          />
        </label>
      </fieldset>

      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Feedback') }}</legend>

        <label>
          {{ $gettext('Ergebnismitteilung:') }}
          <input
            v-model="modelTaskDefinition.strings.resultMessage"
            @input="
              updateTaskDefinition('taskDefinition.strings.resultMessage')
            "
            type="text"
          />
        </label>
      </fieldset>
    </form>
  </div>
</template>

<style scoped>
.memory-editor {
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
  flex: 1 1 auto;
  height: 150px;
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
  width: 12em;
  height: 12em;

  display: flex;
  justify-content: center;
  align-items: center;

  border: 2px solid #dbe2e8;
  border-radius: 0.5em;
  margin: 0;
}
</style>

<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import { fileIdToUrl, MemoryCard, MemoryTask } from '@/models/TaskDefinition';
import { $gettext, $pgettext } from '@/language/gettext';
import produce from 'immer';
import { v4 } from 'uuid';
import FileUpload from '@/components/FileUpload.vue';
import { FileRef } from '@/routes/jsonApi';
import MemoryEditorCard from '@/components/MemoryEditorCard.vue';
import LazyImage from '@/components/LazyImage.vue';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import { cloneDeep } from 'lodash';

export default defineComponent({
  name: 'MemoryEditor',
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  components: { LazyImage, MemoryEditorCard, FileUpload },
  props: {
    taskDefinition: {
      type: Object as PropType<MemoryTask>,
      required: true,
    },
  },
  data() {
    return {
      selectedCardIndex: -1,
      modelTaskDefinition: cloneDeep(this.taskDefinition),
    };
  },
  watch: {
    // Synchronize state taskDefinition -> modelTaskDefinition.
    taskDefinition: {
      immediate: true,
      handler: function (): void {
        this.modelTaskDefinition = cloneDeep(this.taskDefinition);
      },
    },
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
    updateTaskDefinition(undoBatch?: unknown) {
      // Synchronize state modelTaskDefinition -> taskDefinition.
      this.taskEditor!.performEdit({
        newTaskDefinition: cloneDeep(this.modelTaskDefinition),
        undoBatch: undoBatch ?? {},
      });
    },
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
      text += $gettext('Paar');

      /*
      if (card.first.altText != '') {
        text += card.first.altText;

        if (card.second && card.second?.altText != '') {
          text += ' ' + $gettext('und') + ' ' + card.second.altText;
        }
      } else {
        text += $gettext('Paar');
      }
      */

      return text;
    },
    addCard() {
      this.modelTaskDefinition = produce(this.modelTaskDefinition, (draft) => {
        draft.cards.push({
          uuid: v4(),
          first: {
            uuid: v4(),
            type: 'image',
            file_id: '',
            altText: '',
          },
        });
      });
      this.taskEditor!.performEdit({
        newTaskDefinition: this.modelTaskDefinition,
        undoBatch: {},
      });
      // Select the newly inserted card
      this.selectedCardIndex = this.modelTaskDefinition.cards.length - 1;
    },
    deleteCard(index: number) {
      this.modelTaskDefinition = produce(this.modelTaskDefinition, (draft) => {
        draft.cards.splice(index, 1);
      });

      this.updateTaskDefinition();

      // Adjust the selection index so the selected card doesn't unexpectedly change
      if (index <= this.selectedCardIndex) {
        this.selectedCardIndex = this.selectedCardIndex - 1;
      }
    },
    updateCard(updatedCard: MemoryCard, undoBatch?: unknown) {
      this.modelTaskDefinition = produce(this.modelTaskDefinition, (draft) => {
        draft.cards[this.selectedCardIndex] = updatedCard;
      });
      this.updateTaskDefinition(undoBatch);
    },
    onFlipsideImageUploaded(file: FileRef): void {
      const newTaskDefinition = produce(this.modelTaskDefinition, (draft) => {
        draft.flipside = {
          uuid: v4(),
          type: 'image',
          file_id: file.id,
          altText: '',
        };
      });
      this.taskEditor!.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
    deleteFlipsideImage(): void {
      const newTaskDefinition = produce(this.modelTaskDefinition, (draft) => {
        draft.flipside = undefined;
      });
      this.taskEditor!.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
  },
});
</script>
