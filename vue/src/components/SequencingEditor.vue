<template>
  <div class="stud5p-task">
    <form class="default">
      <fieldset class="sequencing-editor">
        <legend>{{ $gettext('Sequencing') }}</legend>

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
              class="remove-card-button"
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

        <SequencingEditorCard
          v-if="modelTaskDefinition.cards[selectedCardIndex]"
          :card="modelTaskDefinition.cards[selectedCardIndex]"
          :card-index="selectedCardIndex"
        />

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
            v-model="modelTaskDefinition.resumeAllowed"
            @change="updateTaskDefinition"
            type="checkbox"
          />
          {{ $gettext('Fortsetzen des aktuellen Spielstands erlauben') }}
        </label>

        <label>
          <input
            v-model="modelTaskDefinition.showSolutionsAllowed"
            @change="updateTaskDefinition"
            type="checkbox"
          />
          {{ $gettext('Lösungen anzeigen erlauben') }}
        </label>
      </fieldset>

      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Beschriftungen') }}</legend>

        <label>
          {{ $gettext('Text für Überprüfen-Button:') }}
          <input
            v-model="modelTaskDefinition.strings.checkButton"
            @input="updateTaskDefinition('taskDefinition.strings.checkButton')"
            type="text"
          />
        </label>

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

        <label
          :class="{ 'setting-disabled': !modelTaskDefinition.resumeAllowed }"
        >
          {{ $gettext('Text für Fortsetzen-Button:') }}
          <input
            v-model="modelTaskDefinition.strings.resumeButton"
            @input="updateTaskDefinition('taskDefinition.strings.resumeButton')"
            :disabled="!modelTaskDefinition.resumeAllowed"
            type="text"
          />
        </label>

        <label
          :class="{
            'setting-disabled': !modelTaskDefinition.showSolutionsAllowed,
          }"
        >
          {{ $gettext('Text für Lösungen-Button:') }}
          <input
            v-model="modelTaskDefinition.strings.solutionsButton"
            @input="
              updateTaskDefinition('taskDefinition.strings.solutionsButton')
            "
            :disabled="!modelTaskDefinition.showSolutionsAllowed"
            type="text"
          />
        </label>
      </fieldset>

      <FeedbackEditor
        :feedback="modelTaskDefinition.feedback"
        :result-message="modelTaskDefinition.strings.resultMessage"
        @update:feedback="updateFeedback"
        @update:result-message="updateResultMessage"
      />
    </form>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import {
  LernmoduleMultimediaElement,
  SequencingTask,
} from '@/models/TaskDefinition';
import { Feedback } from '@/models/common';
import { $gettext } from '@/language/gettext';
import produce from 'immer';
import { v4 } from 'uuid';
import SequencingEditorCard from '@/components/SequencingEditorCard.vue';
import FeedbackEditor from '@/components/FeedbackEditor.vue';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import { cloneDeep } from 'lodash';

export default defineComponent({
  name: 'SequencingEditor',
  components: { SequencingEditorCard, FeedbackEditor },
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  props: {
    taskDefinition: {
      type: Object as PropType<SequencingTask>,
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
    if (this.modelTaskDefinition.cards.length > 0) {
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

    updateTaskDefinition(undoBatch?: unknown) {
      // Synchronize state modelTaskDefinition -> taskDefinition.
      this.taskEditor!.performEdit({
        newTaskDefinition: cloneDeep(this.modelTaskDefinition),
        undoBatch: undoBatch ?? {},
      });
    },

    selectCard(index: number) {
      this.selectedCardIndex = index;
    },

    addCard() {
      this.modelTaskDefinition = produce(this.modelTaskDefinition, (draft) => {
        draft.cards.push({
          type: 'image',
          uuid: v4(),
          file_id: '',
          altText: '',
        });
      });

      this.updateTaskDefinition();

      // Select the newly inserted image
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
          this.modelTaskDefinition,
          (taskDraft: SequencingTask) => {
            taskDraft.feedback = updatedFeedback;
          }
        ),
        undoBatch: 'taskDefinition.feedback',
      });
    },

    updateResultMessage(updatedResultMessage: string) {
      this.taskEditor!.performEdit({
        newTaskDefinition: produce(
          this.modelTaskDefinition,
          (taskDraft: SequencingTask) => {
            taskDraft.strings.resultMessage = updatedResultMessage;
          }
        ),
        undoBatch: 'taskDefinition.strings.resultMessage',
      });
    },
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

.cards-list-item:last-of-type {
  margin-bottom: 0.8em;
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
  margin: 0 0 0 0;
}

.no-card-selected-placeholder {
  flex: 1 1 auto;
  height: 150px;
}
</style>
