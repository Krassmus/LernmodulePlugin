<template>
  <div class="stud5p-task">
    <form class="default" @submit.prevent>
      <fieldset>
        <legend>{{ $gettext('Find the Words') }}</legend>
        <label>
          {{ $gettext('Zu findende Wörter:') }}
          <input
            v-model="userInput"
            @input="onInputWords($event.target.value)"
            type="text"
          />
          <p v-text="wordsInputMessage" />
        </label>
        <label>
          {{ $gettext('Feldgröße:') }}
          <input
            v-model="modelTaskDefinition.size"
            @change="onInputSize($event.target.value)"
            @blur="clearMessages()"
            min="6"
            max="20"
            type="number"
          />
          <p v-text="numbersInputMessage" />
        </label>
        <label>
          {{ $gettext('Zeichen, mit denen die Tafel aufgefüllt wird:') }}
          <input
            v-model="modelTaskDefinition.alphabet"
            @input="onInputAlphabet($event.target.value)"
            type="text"
          />
          <button
            type="button"
            class="button"
            style="white-space: normal"
            @click="resetAlphabet"
          >
            {{ $gettext('Zurücksetzen auf A - Z') }}
          </button>
          <button
            type="button"
            class="button"
            style="white-space: normal"
            @click="resetAlphabetToUsedLetters"
          >
            {{ $gettext('Buchstaben der Lösungswörter') }}
          </button>
        </label>
      </fieldset>

      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Einstellungen') }}</legend>

        <label>
          <input
            v-model="modelTaskDefinition.showWordList"
            @change="updateTaskDefinition"
            type="checkbox"
          />
          {{ $gettext('Wortliste anzeigen') }}
        </label>

        <fieldset>
          <legend>{{ $gettext('Wortrichtungen') }}</legend>
          <label>
            <input
              v-model="modelTaskDefinition.directions.e"
              @change="updateTaskDefinition"
              type="checkbox"
            />
            {{ $gettext('Horizontal nach rechts') }}
          </label>

          <label>
            <input
              v-model="modelTaskDefinition.directions.w"
              @change="updateTaskDefinition"
              type="checkbox"
            />
            {{ $gettext('Horizontal nach links') }}
          </label>

          <label>
            <input
              v-model="modelTaskDefinition.directions.s"
              @change="updateTaskDefinition"
              type="checkbox"
            />
            {{ $gettext('Vertikal nach unten') }}
          </label>

          <label>
            <input
              v-model="modelTaskDefinition.directions.n"
              @change="updateTaskDefinition"
              type="checkbox"
            />
            {{ $gettext('Vertikal nach oben') }}
          </label>

          <label>
            <input
              v-model="modelTaskDefinition.directions.ne"
              @change="updateTaskDefinition"
              type="checkbox"
            />
            {{ $gettext('Diagonal nach oben rechts') }}
          </label>

          <label>
            <input
              v-model="modelTaskDefinition.directions.se"
              @change="updateTaskDefinition"
              type="checkbox"
            />
            {{ $gettext('Diagonal nach unten rechts') }}
          </label>

          <label>
            <input
              v-model="modelTaskDefinition.directions.nw"
              @change="updateTaskDefinition"
              type="checkbox"
            />
            {{ $gettext('Diagonal nach oben links') }}
          </label>

          <label>
            <input
              v-model="modelTaskDefinition.directions.sw"
              @change="updateTaskDefinition"
              type="checkbox"
            />
            {{ $gettext('Diagonal nach unten links') }}
          </label>
        </fieldset>
      </fieldset>

      <fieldset class="collapsable collapsed">
        <legend>{{ $gettext('Beschriftungen') }}</legend>

        <label>
          {{ $gettext('Titel der Wortliste:') }}
          <input
            v-model="modelTaskDefinition.strings.wordListTitle"
            @input="
              updateTaskDefinition('taskDefinition.strings.wordListTitle')
            "
            type="text"
          />
        </label>

        <label>
          {{ $gettext('Text für Überprüfen-Button:') }}
          <input
            v-model="modelTaskDefinition.strings.checkButton"
            @input="updateTaskDefinition('taskDefinition.strings.checkButton')"
            type="text"
          />
        </label>

        <label>
          {{ $gettext('Text für Wiederholen-Button:') }}
          <input
            v-model="modelTaskDefinition.strings.retryButton"
            @input="updateTaskDefinition('taskDefinition.strings.retryButton')"
            type="text"
          />
        </label>

        <label>
          {{ $gettext('Text für Zeitanzeige:') }}
          <input
            v-model="modelTaskDefinition.strings.timer"
            @input="updateTaskDefinition('taskDefinition.strings.timer')"
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
            type="text"
            v-model="modelTaskDefinition.strings.resultMessage"
            @input="
              updateTaskDefinition('taskDefinition.strings.resultMessage')
            "
          />
        </label>
      </fieldset>
    </form>
  </div>
</template>

<script setup lang="ts">
import { computed, defineProps, inject, PropType, ref, watch } from 'vue';
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
const wordsInputMessage = ref<string>('');
const userInput = ref<string>(modelTaskDefinition.value.words);
const lastValidInput = ref<string>(modelTaskDefinition.value.words);
const numbersInputMessage = ref<string>('');

const words = computed(() => {
  if (props.taskDefinition?.words) {
    return props.taskDefinition.words
      .split(',')
      .filter((word) => word.trim())
      .map((word) => word.trim().toUpperCase());
  }
  return [];
});

watch(
  () => props.taskDefinition,
  (newTaskDefinition, oldTaskDefinition) => {
    modelTaskDefinition.value = cloneDeep(newTaskDefinition);
  },
  { deep: true }
);

function clearMessages(): void {
  wordsInputMessage.value = '';
  numbersInputMessage.value = '';
}

function onInputWords(words: string): void {
  let wordArray = words
    .split(',')
    .filter((word) => word.trim())
    .map((word) => word.trim().toUpperCase());

  let longestWord = 0;
  for (const word of wordArray) {
    if (word.length > longestWord) {
      longestWord = word.length;
    }
  }

  const tooLongWord = longestWord > 20;
  const tooManyWords = wordArray.length > 20;

  if (tooLongWord) {
    wordsInputMessage.value = $gettext(
      'Wörter dürfen nicht länger als 20 Zeichen sein.'
    );
    userInput.value = lastValidInput.value;
    return;
  } else if (tooManyWords) {
    wordsInputMessage.value = $gettext(
      'Es dürfen maximal 20 Wörter eingegeben werden.'
    );
    userInput.value = lastValidInput.value;
    return;
  } else {
    wordsInputMessage.value = '';
    lastValidInput.value = words;
  }

  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.words = words;
  });

  if (longestWord > modelTaskDefinition.value.size) {
    numbersInputMessage.value = '';
    modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
      draft.size = longestWord;
    });
  }

  // Synchronize state modelTaskDefinition -> taskDefinition.
  console.log('update task definition');
  taskEditor!.performEdit({
    newTaskDefinition: cloneDeep(modelTaskDefinition.value),
  });
}

function onInputSize(size: number): void {
  let longestWord = 0;

  for (const word of words.value) {
    console.log(word, word.length);
    if (word.length > longestWord) {
      longestWord = word.length;
    }
  }

  let num = Number(size);
  if (num > 20) {
    console.log(num);
    num = 20;
  }
  if (num < longestWord) {
    num = longestWord;
    numbersInputMessage.value = $gettext(
      'Minimum %{ characters } da ein Wort mit %{ characters } Zeichen existiert',
      { characters: num.toString() }
    );
  } else {
    numbersInputMessage.value = '';
  }

  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.size = num;
  });

  updateTaskDefinition('size');
}

function onInputAlphabet(alphabet: string): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.alphabet = alphabet;
  });

  updateTaskDefinition('alphabet');
}

function resetAlphabet(): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.alphabet = 'abcdefghijklmnopqrstuvwxyz';
  });

  updateTaskDefinition();
}

function resetAlphabetToUsedLetters(): void {
  modelTaskDefinition.value = produce(modelTaskDefinition.value, (draft) => {
    draft.alphabet = Array.from(
      new Set(draft.words.toLowerCase().replace(/[\s,]/g, '').split(''))
    ).join('');
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
