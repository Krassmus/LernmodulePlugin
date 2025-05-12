<template>
  <div class="stud5p-task">
    <form class="default" @submit.prevent>
      <fieldset>
        <legend>{{ $gettext('Find the Words') }}</legend>
        <label>
          {{ $gettext('Zu findende Wörter:') }}
          <input
            v-model="modelTaskDefinition.words"
            @input="onInputWords($event.target.value)"
            type="text"
          />
        </label>
        <label>
          {{ $gettext('Zeichen, mit denen die Tafel aufgefüllt wird:') }}
          <span
            style="
              display: flex;
              flex-direction: row;
              align-items: center;
              gap: 1em;
            "
          >
            <input
              v-model="modelTaskDefinition.alphabet"
              @input="onInputAlphabet($event.target.value)"
              type="text"
            />
            <button type="button" @click="resetAlphabet">
              {{ $gettext('Zurücksetzen auf A - Z') }}
            </button>
          </span>
        </label>
      </fieldset>

      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Einstellungen') }}</legend>
      </fieldset>
    </form>
  </div>
</template>

<script setup lang="ts">
import { defineProps, inject, PropType, ref } from 'vue';
import { FindTheWordsTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import { cloneDeep } from 'lodash';
import produce from 'immer';

const taskEditor = inject<TaskEditorState>(taskEditorStateSymbol);

const props = defineProps({
  taskDefinition: {
    type: Object as PropType<FindTheWordsTask>,
    required: true,
  },
});

const debug = window.STUDIP.LernmoduleVueJS.LERNMODULE_DEBUG;

// State
const modelTaskDefinition = ref<FindTheWordsTask>(
  cloneDeep(props.taskDefinition)
);

function onInputWords(words: string): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.words = words;
  });

  // Synchronize state modelTaskDefinition -> taskDefinition.
  console.log('update task definition');
  taskEditor!.performEdit({
    newTaskDefinition: cloneDeep(modelTaskDefinition.value),
  });
}

function onInputAlphabet(alphabet: string): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.alphabet = alphabet;
  });

  updateTaskDefinition();
}

function resetAlphabet(): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.alphabet = 'abcdefghijklmnopqrstuvwxyz';
  });

  updateTaskDefinition();
}

function updateTaskDefinition(undoBatch?: unknown): void {
  // Synchronize state modelTaskDefinition -> taskDefinition.
  console.log('update task definition');
  taskEditor!.performEdit({
    newTaskDefinition: cloneDeep(modelTaskDefinition.value),
    undoBatch: undoBatch ?? {},
  });
}
</script>

<style scoped></style>
