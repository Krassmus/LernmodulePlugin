<template>
  <div class="stud5p-task">
    <form class="default">
      <fieldset class="pairing-editor">
        <legend>{{ $gettext('Pairing') }}</legend>

        <div class="pairs-overview">
          <PairingEditorPair
            v-for="(pair, index) in taskDefinition.pairs"
            :key="pair.uuid"
            :class="{
              selected: index === selectedPairIndex,
            }"
            :pair="taskDefinition.pairs[index]"
            @click="onClickPair(index)"
          />

          <button
            type="button"
            class="add-pair-button"
            @click="onClickAddPair()"
            :style="addPairButtonBackgroundImage"
          />
        </div>

        <fieldset v-if="selectedPair" class="selected-pair">
          <legend>{{ $gettext('Paar') }}</legend>

          <PairingEditorCard
            :title="$gettext('Karte 1')"
            :multimedia-element="selectedPair.draggableElement"
            @element-changed="onChangeDraggableElement"
          />

          <PairingEditorCard
            :title="$gettext('Karte 2')"
            :multimedia-element="selectedPair.targetElement"
            @element-changed="onChangeTargetElement"
          />

          <div class="remove-pair-button-container">
            <button
              type="button"
              @click="onClickDeletePair(selectedPairIndex)"
              v-text="$gettext('Paar löschen')"
              class="button trash remove-pair-button"
            />
          </div>
        </fieldset>
      </fieldset>

      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Einstellungen') }}</legend>

        <label>
          <input v-model="taskDefinition.retryAllowed" type="checkbox" />
          {{ $gettext('Mehrere Versuche erlauben') }}
        </label>

        <label>
          <input
            v-model="taskDefinition.showSolutionsAllowed"
            type="checkbox"
          />
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

      <FeedbackEditor
        :feedback="taskDefinition.feedback"
        :result-message="taskDefinition.strings.resultMessage"
        @update:feedback="updateFeedback"
        @update:result-message="updateResultMessage"
      />
    </form>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';
import {
  Feedback,
  fileIdToUrl,
  LernmoduleMultimediaElement,
  Pair,
  PairingTask,
} from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import produce from 'immer';
import { v4 } from 'uuid';
import PairingEditorPair from '@/components/PairingEditorPair.vue';
import PairingEditorCard from '@/components/PairingEditorCard.vue';
import FeedbackEditor from '@/components/FeedbackEditor.vue';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import { taskEditorStore } from '@/store';

export default defineComponent({
  name: 'PairingEditor',
  components: {
    PairingEditorPair,
    PairingEditorCard,
    FeedbackEditor,
  },
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
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

    onClickPair(index: number) {
      this.selectedPairIndex = index;
    },

    onClickAddPair() {
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

      this.taskEditor!.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });

      // Select the newly inserted card
      this.selectedPairIndex = newTaskDefinition.pairs.length - 1;
    },

    onClickDeletePair(index: number) {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs.splice(index, 1);
      });

      this.taskEditor!.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });

      // Adjust the selection index so the selected card doesn't unexpectedly change
      if (this.taskDefinition.pairs.length === 0) {
        this.selectedPairIndex = -1;
      } else if (index <= this.selectedPairIndex) {
        this.selectedPairIndex = Math.max(0, this.selectedPairIndex - 1);
      }
    },

    onChangeDraggableElement(payload: {
      updatedElement: LernmoduleMultimediaElement;
      undoBatch?: unknown;
    }): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs[this.selectedPairIndex].draggableElement =
          payload.updatedElement;
      });

      this.taskEditor!.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: payload.undoBatch ?? {},
      });
    },

    onChangeTargetElement(payload: {
      updatedElement: LernmoduleMultimediaElement;
      undoBatch?: unknown;
    }): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs[this.selectedPairIndex].targetElement =
          payload.updatedElement;
      });

      this.taskEditor!.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: payload.undoBatch ?? {},
      });
    },

    updateFeedback(updatedFeedback: Feedback[]) {
      this.taskEditor!.performEdit({
        newTaskDefinition: produce(
          this.taskDefinition,
          (taskDraft: PairingTask) => {
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
          (taskDraft: PairingTask) => {
            taskDraft.strings.resultMessage = updatedResultMessage;
          }
        ),
        undoBatch: {},
      });
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as PairingTask,

    debug: () => window.STUDIP.LernmoduleVueJS.LERNMODULE_DEBUG,

    selectedPair(): Pair | undefined {
      return this.taskDefinition.pairs[this.selectedPairIndex];
    },

    addPairButtonBackgroundImage() {
      return {
        backgroundImage: `url(${this.urlForIcon('add')})`,
      };
    },
  },
});
</script>

<style scoped>
.pairing-editor {
  display: flex;
  gap: 1em;
}

.pairs-overview {
  flex-grow: 1;
  /* Adapted from https://stackoverflow.com/a/46099319/7359454 */
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(9em, auto));
  grid-auto-rows: max-content;
  justify-content: space-around;
  row-gap: 1em;
  column-gap: 0.5em;
}

.selected-pair {
  flex-grow: 0;
  flex-shrink: 0;
  width: 275px;
}

.add-pair-button {
  box-sizing: border-box;
  width: 100%;
  aspect-ratio: 1;

  margin: 0;
  padding: 0;

  border: solid 2px rgba(0, 0, 0, 0);
  border-radius: 0.25em;

  background-size: 40%;
  background-repeat: no-repeat;
  background-position: center;
}

.remove-pair-button {
  margin-right: 0;
}

.remove-pair-button-container {
  text-align: end;
  margin-top: 0.5em;
}
</style>
