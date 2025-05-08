<template>
  <div class="stud5p-task">
    <form class="default" @submit.prevent>
      <fieldset>
        <legend>{{ $gettext('Find the Words') }}</legend>
        <input
          v-model="modelTaskDefinition.words"
          @input="onInputWords($event.target.value)"
          type="text"
        />
        <input
          v-model="modelTaskDefinition.alphabet"
          @input="onInputAlphabet($event.target.value)"
          type="text"
        />
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

  // Synchronize state modelTaskDefinition -> taskDefinition.
  console.log('update task definition');
  taskEditor!.performEdit({
    newTaskDefinition: cloneDeep(modelTaskDefinition.value),
  });
}
</script>

<style scoped></style>
