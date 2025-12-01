<template>
  <div class="stud5p-task">
    <form class="default" @submit.prevent>
      <fieldset>
        <legend>{{ $gettext('Crossword') }}</legend>

        <TabsComponent>
          <TabComponent :title="$gettext('Einstellungen')" icon="settings">
            <p>debug</p>
          </TabComponent>
          <TabComponent :title="$gettext('Wörter eingeben')" icon="content">
            <div class="words-flex-box">
              <div class="word-list">
                <div
                  v-for="word in modelTaskDefinition.words"
                  :key="word.uuid"
                  class="word-list-item"
                >
                  <input
                    type="text"
                    v-model="word.hint"
                    @input="onInputHint(word.uuid, $event.target.value)"
                  />
                  <input
                    type="text"
                    v-model="word.solution"
                    @input="onInputSolution(word.uuid, $event.target.value)"
                  />
                  <button
                    type="button"
                    class="small-button trash"
                    @click="deleteWord(word.uuid)"
                    :title="$gettext('Dieses Wort löschen')"
                  />
                </div>
                <button type="button" class="button" @click="addWord">
                  {{ $gettext('Neues Wort hinzufügen') }}
                </button>
              </div>
              <div class="word-details">2</div>
            </div>
          </TabComponent>
          <TabComponent :title="$gettext('Vorschau')" icon="visibility-visible">
            <p>debug</p>
          </TabComponent>
        </TabsComponent>
      </fieldset>

      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Einstellungen') }}</legend>
      </fieldset>

      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Beschriftungen') }}</legend>

        <label>
          {{ $gettext('Text für Wiederholen-Button:') }}
          <input
            v-model="modelTaskDefinition.strings.retryButton"
            @input="updateTaskDefinition('taskDefinition.strings.retryButton')"
            type="text"
          />
        </label>
      </fieldset>

      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Feedback') }}</legend>

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
      </fieldset>
    </form>
  </div>
  <pre
    v-if="debug && true"
    :style="{ flexBasis: '50%', flexGrow: 0, flexShrink: 0 }"
    >{{
      {
        taskDefinition,
      }
    }}</pre
  >
</template>

<script setup lang="ts">
import { inject, PropType, ref, defineProps, watch, computed } from 'vue';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import { CrosswordTask, Word } from '@/models/CrosswordTask';
import { cloneDeep } from 'lodash';
import { $gettext } from '@/language/gettext';
import TabComponent from '@/components/courseware-components-ported-to-vue3/TabComponent.vue';
import TabsComponent from '@/components/courseware-components-ported-to-vue3/TabsComponent.vue';
import produce from 'immer';
import { v4 } from 'uuid';

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
};

const debug = window.STUDIP.LernmoduleVueJS.LERNMODULE_DEBUG;

// State
const modelTaskDefinition = ref<CrosswordTask>(cloneDeep(props.taskDefinition));

// Computed properties

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
  };
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.words.push(newWord);
  });
  updateTaskDefinition();
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
</script>

<style scoped>
.words-flex-box {
  display: flex;
  justify-content: space-between;
}

.word-list {
  border: 1px solid red;
}

.word-list-item {
  border: 1px solid green;
  display: flex;
}

.word-details {
  border: 1px solid blue;
}
</style>
