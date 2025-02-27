<template>
  <div class="stud5p-task">
    <form class="default">
      <fieldset class="pairing-editor">
        <legend>{{ $gettext('Pairing') }}</legend>

        <div class="pairs-overview">
          <PairingEditorPair
            v-for="(pair, index) in modelTaskDefinition.pairs"
            :key="pair.uuid"
            :class="{
              selected: index === selectedPairIndex,
            }"
            :pair="modelTaskDefinition.pairs[index]"
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
          <input
            v-model="modelTaskDefinition.retryAllowed"
            @change="updateTaskDefinition"
            type="checkbox"
          />
          {{ $gettext('Mehrere Versuche erlauben') }}
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
import { cloneDeep } from 'lodash';

export default defineComponent({
  name: 'PairingEditor',
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  components: {
    PairingEditorPair,
    PairingEditorCard,
    FeedbackEditor,
  },
  props: {
    taskDefinition: {
      type: Object as PropType<PairingTask>,
      required: true,
    },
  },
  data() {
    return {
      modelTaskDefinition: cloneDeep(this.taskDefinition),
      selectedPairIndex: -1,
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

    updateTaskDefinition(undoBatch?: unknown) {
      // Synchronize state modelTaskDefinition -> taskDefinition.
      this.taskEditor!.performEdit({
        newTaskDefinition: cloneDeep(this.modelTaskDefinition),
        undoBatch: undoBatch ?? {},
      });
    },

    onClickPair(index: number) {
      this.selectedPairIndex = index;
    },

    onClickAddPair() {
      this.modelTaskDefinition = produce(this.modelTaskDefinition, (draft) => {
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

      this.updateTaskDefinition();

      // Select the newly inserted card
      this.selectedPairIndex = this.modelTaskDefinition.pairs.length - 1;
    },

    onClickDeletePair(index: number) {
      this.modelTaskDefinition = produce(this.modelTaskDefinition, (draft) => {
        draft.pairs.splice(index, 1);
      });

      this.updateTaskDefinition();

      // Adjust the selection index so the selected card doesn't unexpectedly change
      if (this.modelTaskDefinition.pairs.length === 0) {
        this.selectedPairIndex = -1;
      } else if (index <= this.selectedPairIndex) {
        this.selectedPairIndex = Math.max(0, this.selectedPairIndex - 1);
      }
    },

    onChangeDraggableElement(payload: {
      updatedElement: LernmoduleMultimediaElement;
      undoBatch?: unknown;
    }): void {
      this.modelTaskDefinition = produce(this.modelTaskDefinition, (draft) => {
        draft.pairs[this.selectedPairIndex].draggableElement =
          payload.updatedElement;
      });

      this.updateTaskDefinition(payload.undoBatch);
    },

    onChangeTargetElement(payload: {
      updatedElement: LernmoduleMultimediaElement;
      undoBatch?: unknown;
    }): void {
      this.modelTaskDefinition = produce(this.modelTaskDefinition, (draft) => {
        draft.pairs[this.selectedPairIndex].targetElement =
          payload.updatedElement;
      });

      this.updateTaskDefinition(payload.undoBatch);
    },

    updateFeedback(updatedFeedback: Feedback[]) {
      this.taskEditor!.performEdit({
        newTaskDefinition: produce(
          this.modelTaskDefinition,
          (taskDraft: PairingTask) => {
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
          (taskDraft: PairingTask) => {
            taskDraft.strings.resultMessage = updatedResultMessage;
          }
        ),
        undoBatch: 'taskDefinition.strings.resultMessage',
      });
    },
  },
  computed: {
    debug: () => window.STUDIP.LernmoduleVueJS.LERNMODULE_DEBUG,

    selectedPair(): Pair | undefined {
      return this.modelTaskDefinition.pairs[this.selectedPairIndex];
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
  /* 8em fixed width image + 2 x 0.25em padding + 2 x 0.125em border */
  grid-template-columns: repeat(auto-fill, 8.75em);
  /* 2 x 8em fixed height images + 0.25em gap + 2 x 0.25em padding + 2 x 0.125em border */
  grid-auto-rows: 17em;
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
  aspect-ratio: 1;

  border: solid 2px rgba(0, 0, 0, 0);
  border-radius: 0.25em;

  background-size: 40%;
  background-repeat: no-repeat;
  background-position: center;

  cursor: pointer;
}

.add-pair-button:hover {
  filter: brightness(0.9);
}

.remove-pair-button {
  margin-right: 0;
}

.remove-pair-button-container {
  text-align: end;
  margin-top: 0.5em;
}
</style>
