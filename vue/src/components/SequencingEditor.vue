<template>
  <form class="default">
    <fieldset class="sequencing-editor">
      <legend>{{ $gettext('Sequencing') }}</legend>

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
          <div v-text="listEntryText(index, card)" class="list-entry-text" />

          <!-- Apply .stop modifier so that the selectCard event handler on the
            parent element doesn't get called when the delete button is clicked -->
          <button
            type="button"
            class="flex-child-element remove-card-button"
            @click.stop="deleteCard(index)"
            :aria-label="$gettext('Karte löschen')"
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

      <SequencingElement
        v-if="this.taskDefinition.cards[this.selectedCardIndex]"
        :card="this.taskDefinition.cards[this.selectedCardIndex]"
        :card-index="this.selectedCardIndex"
      />
      <div v-else class="edited-card no-card-selected-placeholder">
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
        <input v-model="taskDefinition.resumeAllowed" type="checkbox" />
        {{ $gettext('Fortsetzen des aktuellen Spielstands erlauben') }}
      </label>

      <label>
        <input v-model="taskDefinition.showSolutionsAllowed" type="checkbox" />
        {{ $gettext('Lösungen anzeigen erlauben') }}
      </label>
    </fieldset>

    <fieldset class="collapsable collapsed">
      <legend>{{ $gettext('Beschriftungen') }}</legend>

      <label>
        {{ $gettext('Text für Überprüfen-Button:') }}
        <input v-model="taskDefinition.strings.checkButton" type="text" />
      </label>

      <label :class="{ 'setting-disabled': !taskDefinition.retryAllowed }">
        {{ $gettext('Text für Wiederholen-Button:') }}
        <input
          v-model="taskDefinition.strings.retryButton"
          :disabled="!taskDefinition.retryAllowed"
          type="text"
        />
      </label>

      <label :class="{ 'setting-disabled': !taskDefinition.resumeAllowed }">
        {{ $gettext('Text für Fortsetzen-Button:') }}
        <input
          v-model="taskDefinition.strings.resumeButton"
          :disabled="!taskDefinition.resumeAllowed"
          type="text"
        />
      </label>

      <label
        :class="{ 'setting-disabled': !taskDefinition.showSolutionsAllowed }"
      >
        {{ $gettext('Text für Lösungen-Button:') }}
        <input
          v-model="taskDefinition.strings.solutionsButton"
          :disabled="!taskDefinition.showSolutionsAllowed"
          type="text"
        />
      </label>
    </fieldset>

    <feedback-editor
      :feedback="taskDefinition.feedback"
      :result-message="taskDefinition.strings.resultMessage"
      @update:feedback="updateFeedback"
      @update:result-message="updateResultMessage"
    />
  </form>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';
import {
  Feedback,
  LernmoduleMultimediaElement,
  SequencingTask,
} from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import { taskEditorStore } from '@/store';
import produce from 'immer';
import { v4 } from 'uuid';
import SequencingElement from '@/components/SequencingElement.vue';
import FeedbackEditor from '@/components/FeedbackEditor.vue';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';

export default defineComponent({
  name: 'SequencingEditor',
  components: { SequencingElement, FeedbackEditor },
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  data() {
    return { selectedCardIndex: -1 };
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
          type: 'image',
          uuid: v4(),
          file_id: '',
          altText: '',
        });
      });

      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });

      // Select the newly inserted image
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

    listEntryText(index: number, card: LernmoduleMultimediaElement) {
      let text = index + 1 + '. ';

      if (card.type === 'image' && card.altText != '') {
        text += card.altText;
      } else {
        text += $gettext('Bild');
      }
      return text;
    },

    updateFeedback(updatedFeedback: Feedback[]) {
      this.taskEditor!.performEdit({
        newTaskDefinition: produce(
          this.taskDefinition,
          (taskDraft: SequencingTask) => {
            taskDraft.feedback = updatedFeedback;
          }
        ),
        undoBatch: {},
      });
    },

    updateResultMessage(updatedResultMessage: string) {
      this.taskEditor!.performEdit({
        newTaskDefinition: produce(
          this.taskDefinition,
          (taskDraft: SequencingTask) => {
            taskDraft.strings.resultMessage = updatedResultMessage;
          }
        ),
        undoBatch: {},
      });
    },
  },

  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as SequencingTask,
  },
});
</script>

<style scoped>
.sequencing-editor {
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

.edited-card {
  flex: 1 1 auto;
}
</style>
