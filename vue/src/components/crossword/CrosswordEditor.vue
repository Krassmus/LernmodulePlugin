<template>
  <div class="stud5p-task">
    <form class="default" @submit.prevent>
      <fieldset>
        <legend>{{ $gettext('Crossword') }}</legend>

        <TabsComponent>
          <TabComponent
            :title="$gettext('Wörter eingeben')"
            icon="content"
            class="tab-component"
          >
            <div class="word-list-and-word-details-container">
              <div class="word-list-container">
                <draggable
                  v-model="wordList"
                  item-key="uuid"
                  class="word-list"
                  :filter="'input,button'"
                  :preventOnFilter="false"
                >
                  <template #item="{ element }">
                    <div
                      class="word"
                      @click="onClickWord(element.uuid)"
                      :class="{
                        'selected-word': selectedWord?.uuid === element.uuid,
                      }"
                    >
                      <input
                        type="text"
                        v-model="element.hint"
                        @input="onInputHint(element.uuid, element.hint)"
                        :placeholder="$gettext('Hinweis')"
                      />
                      <input
                        type="text"
                        v-model="element.solution"
                        @input="onInputSolution(element.uuid, element.solution)"
                        :placeholder="$gettext('Lösung')"
                      />
                      <button
                        type="button"
                        class="small-button trash"
                        @click="deleteWord(element.uuid)"
                        :title="$gettext('Dieses Wort löschen')"
                      />
                    </div>
                  </template>
                </draggable>

                <button type="button" class="button" @click="addWord">
                  {{ $gettext('Neues Wort hinzufügen') }}
                </button>
              </div>
              <div class="word-details">
                <fieldset>
                  <legend>{{ $gettext('Wort bearbeiten') }}</legend>

                  <template v-if="selectedWord">
                    <label>
                      {{ $gettext('Richtung') }}
                      <select
                        v-model="selectedWord.direction"
                        @change="onChangeDirection(selectedWord.uuid, $event)"
                      >
                        <option value="across">
                          {{ $gettext('Waagerecht') }}
                        </option>
                        <option value="down">
                          {{ $gettext('Senkrecht') }}
                        </option>
                      </select>
                    </label>
                    <label>
                      {{ $gettext('Spalte') }}
                      <input
                        type="number"
                        v-model.number="selectedWord.x"
                        min="0"
                        max="100"
                        step="1"
                        @input="
                          onInputXCoordinate(selectedWord.uuid, selectedWord.x)
                        "
                        :placeholder="'x'"
                      />
                    </label>
                    <label>
                      {{ $gettext('Reihe') }}
                      <input
                        type="number"
                        v-model.number="selectedWord.y"
                        min="0"
                        max="100"
                        step="1"
                        @input="
                          onInputYCoordinate(selectedWord.uuid, selectedWord.y)
                        "
                        :placeholder="'y'"
                      />
                    </label>
                  </template>
                  <template v-else>
                    <p>
                      {{
                        $gettext(
                          'Bitte wählen Sie ein Wort aus, um es zu bearbeiten.'
                        )
                      }}
                    </p>
                  </template>
                </fieldset>
              </div>
            </div>
          </TabComponent>
          <TabComponent
            :title="$gettext('Einstellungen')"
            icon="settings"
            class="tab-component"
          >
            <label>
              {{ $gettext('Leere Felder einfärben:') }}
              <input
                v-model="modelTaskDefinition.colorEmptyCells"
                @change="updateTaskDefinition('taskDefinition.colorEmptyCells')"
                type="checkbox"
              />
            </label>
          </TabComponent>
          <TabComponent
            :title="$gettext('Beschriftungen')"
            icon=""
            class="tab-component"
          >
            <label>
              {{ $gettext('Text für Überprüfen-Button:') }}
              <input
                v-model="modelTaskDefinition.strings.checkButton"
                @input="
                  updateTaskDefinition('taskDefinition.strings.checkButton')
                "
                type="text"
              />
            </label>

            <label>
              {{ $gettext('Text für Wiederholen-Button:') }}
              <input
                v-model="modelTaskDefinition.strings.retryButton"
                @input="
                  updateTaskDefinition('taskDefinition.strings.retryButton')
                "
                type="text"
              />
            </label>

            <label>
              {{ $gettext('Text für Lösungen-Button:') }}
              <input
                v-model="modelTaskDefinition.strings.solutionsButton"
                @input="
                  updateTaskDefinition('taskDefinition.strings.solutionsButton')
                "
                type="text"
              />
            </label>
          </TabComponent>
          <TabComponent :title="$gettext('Feedback')" class="tab-component">
            <label>
              {{
                $gettext(
                  'Ergebnismitteilung (mögliche Variablen :correct und :total):'
                )
              }}
              <input
                v-model="modelTaskDefinition.strings.resultMessage"
                @input="
                  updateTaskDefinition('taskDefinition.strings.resultMessage')
                "
                type="text"
              />
            </label>
          </TabComponent>
          <TabComponent
            v-if="debug"
            :title="'Task Definition (debug)'"
            icon="visibility-visible"
          >
            <pre
              v-if="debug && true"
              :style="{ flexBasis: '50%', flexGrow: 0, flexShrink: 0 }"
              >{{
                {
                  taskDefinition,
                }
              }}</pre
            >
          </TabComponent>
        </TabsComponent>
      </fieldset>
    </form>
  </div>
</template>

<script setup lang="ts">
import { inject, PropType, ref, defineProps, watch, computed } from 'vue';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import { CrosswordTask, Word, Direction } from '@/models/CrosswordTask';
import { cloneDeep } from 'lodash';
import { $gettext } from '@/language/gettext';
import TabComponent from '@/components/studip/TabComponent.vue';
import TabsComponent from '@/components/studip/TabsComponent.vue';
import produce from 'immer';
import { v4 } from 'uuid';
import draggable from 'vuedraggable';

const taskEditor = inject<TaskEditorState>(taskEditorStateSymbol);

const props = defineProps({
  taskDefinition: {
    type: Object as PropType<CrosswordTask>,
    required: true,
  },
});

const components = {
  TabComponent,
  TabsComponent,
  draggable,
};

const debug = window.STUDIP.LernmoduleVueJS.LERNMODULE_DEBUG;

// State
const selectedWord = ref<Word>();
const modelTaskDefinition = ref<CrosswordTask>(cloneDeep(props.taskDefinition));

// Computed properties
const wordList = computed({
  get() {
    return modelTaskDefinition.value.words;
  },
  set(newValue) {
    modelTaskDefinition.value.words = newValue;
    updateTaskDefinition();
  },
});

// Watchers
watch(
  () => props.taskDefinition,
  (newTaskDefinition, oldTaskDefinition) => {
    modelTaskDefinition.value = cloneDeep(newTaskDefinition);
  },
  { deep: true }
);

function updateTaskDefinition(undoBatch?: unknown): void {
  // Synchronize state modelTaskDefinition -> taskDefinition.
  console.log('update task definition');
  taskEditor!.performEdit({
    newTaskDefinition: cloneDeep(modelTaskDefinition.value),
    undoBatch: undoBatch ?? {},
  });
}

function deleteWord(uuid: string): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    const index = draft.words.findIndex((word) => word.uuid === uuid);
    if (index === -1) {
      throw new Error('No word with id ' + uuid + ' found.');
    }
    draft.words.splice(index, 1);
  });

  updateTaskDefinition();
}

function addWord(): void {
  const newWord: Word = {
    uuid: v4(),
    hint: '',
    solution: '',
    direction: 'across',
    x: 0,
    y: 0,
  };
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.words.push(newWord);
  });
  updateTaskDefinition();
}

function onClickWord(uuid: string): void {
  selectedWord.value = modelTaskDefinition.value.words.find(
    (word) => word.uuid === uuid
  );
}

function onInputHint(uuid: string, hint: string): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    const index = draft.words.findIndex((word) => word.uuid === uuid);
    if (index === -1) {
      throw new Error('No word with id ' + uuid + ' found.');
    }
    draft.words[index].hint = hint;
  });

  updateTaskDefinition('hint');
}

function onInputSolution(uuid: string, solution: string): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    const index = draft.words.findIndex((word) => word.uuid === uuid);
    if (index === -1) {
      throw new Error('No word with id ' + uuid + ' found.');
    }
    draft.words[index].solution = solution;
  });

  updateTaskDefinition('solution');
}

function onInputXCoordinate(uuid: string, x: number): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    const index = draft.words.findIndex((word) => word.uuid === uuid);
    if (index === -1) {
      throw new Error('No word with id ' + uuid + ' found.');
    }
    draft.words[index].x = x;
  });

  updateTaskDefinition('x-coordinate');
}

function onInputYCoordinate(uuid: string, y: number): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    const index = draft.words.findIndex((word) => word.uuid === uuid);
    if (index === -1) {
      throw new Error('No word with id ' + uuid + ' found.');
    }
    draft.words[index].y = y;
  });

  updateTaskDefinition('y-coordinate');
}

function onChangeDirection(uuid: string, event: Event) {
  const selectedDirection = (event.target as HTMLSelectElement).value;
  console.log(selectedDirection);
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    const index = draft.words.findIndex((word) => word.uuid === uuid);
    if (index === -1) {
      throw new Error('No word with id ' + uuid + ' found.');
    }
    if (selectedDirection === 'across') {
      draft.words[index].direction = Direction.Enum.across;
    } else if (selectedDirection === 'down') {
      draft.words[index].direction = Direction.Enum.down;
    }
  });

  updateTaskDefinition('direction');
}
</script>

<style scoped>
.tab-component {
  padding: 0.5em;
}

.word-list-and-word-details-container {
  display: flex;
  justify-content: space-between;
}

.word-list-container {
}

.word-list {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
}

.word {
  display: flex;
  gap: 1em;
  padding: 1em;
  align-items: center;
  cursor: grab;
  border: 1px solid #ccc;
}

.selected-word {
  background: rgba(140, 180, 255, 0.13);
}

.word-details {
  width: 400px;
}
</style>
