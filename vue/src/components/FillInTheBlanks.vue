<template>
  <div>
    <input type="text" v-model="blankText" />
  </div>
  <button @click="saveTask" :disabled="this.saveStatus.status === 'saving'">
    Save Task
  </button>
  <div>
    {{ saveStatus.status }}
    <div v-if="saveStatus.status === 'error'">
      {{ saveStatus.error }}
    </div>
  </div>

  <br />

  <div>
    <template v-for="(str, i) in splitTemplate" :key="i">
      <span v-if="i % 2 === 0">
        {{ str }}
      </span>
      <input
        v-else
        type="text"
        v-model="userInputs[i]"
        :class="inputValidations[i] ? 'correct' : 'incorrect'"
      />
    </template>
  </div>

  <br />

  <div v-if="splitTemplate.length > 1">
    <span
      v-if="isInputCorrect"
      :class="isInputCorrect ? 'resultCorrect' : 'resultIncorrect'"
    >
      Alle Lücken sind richtig ausgefüllt.
    </span>

    <span v-else :class="isInputCorrect ? 'resultCorrect' : 'resultIncorrect'">
      Die Lücken sind nicht richtig ausgefüllt.
    </span>
  </div>

  <div v-if="debug">
    <pre>{{ userInputs }}</pre>

    Split template:
    <pre>{{ splitTemplate }}</pre>
    Input validations:
    <pre>{{ inputValidations }}</pre>
  </div>

  <!--  <input type="text" v-model="userInput" />-->
  <!--  <div :class="isInputCorrect ? 'correct' : 'incorrect'">-->
  <!--    User input: {{ userInput }}-->
  <!--  </div>-->
</template>

<script lang="ts">
import { saveTask } from '@/routes';
import { defineComponent } from 'vue';

type Saved = {
  status: 'saved';
};
type Saving = {
  status: 'saving';
};
type SaveError = {
  status: 'error';
  error: unknown;
};
type SaveStatus = Saved | Saving | SaveError;

function sleep(ms: number) {
  return new Promise((r) => setTimeout(r, ms));
}

export default defineComponent({
  data() {
    return {
      template:
        'This kind of berry is a {blue}berry. In Norddeutschland sagt man {moin}.',
      blankText: 'your template {here}',
      userInputs: [],
      debug: true,
      saveStatus: { status: 'saved' } as SaveStatus,
    };
  },
  methods: {
    saveTask() {
      if (this.saveStatus.status === 'saving') {
        return;
      }
      this.saveStatus = { status: 'saving' };
      saveTask(null, {
        type: 'FILL_IN_THE_BLANKS',
        template: this.blankText,
      })
        .then(async (result) => {
          await sleep(1000);
        })
        .then((result) => {
          this.saveStatus = { status: 'saved' };
        })
        .catch((error) => {
          this.saveStatus = {
            status: 'error',
            error: error,
          };
        });
    },
  },
  computed: {
    splitTemplate(): string[] {
      return this.blankText.split(/{([^{}]*)}/);
    },
    isInputCorrect(): boolean {
      return this.splitTemplate.every((el, i) => {
        if (i % 2 === 0) {
          // Das ist kein Input-Feld, sondern ein statischer String im Template
          return true;
        }
        return this.userInputs[i] === this.splitTemplate[i];
      });
    },
    inputValidations(): boolean[] {
      return this.splitTemplate.map(
        (el, i) => this.userInputs[i] === this.splitTemplate[i]
      );
    },
  },
});
</script>

<style scoped>
.correct {
  color: black;
  background-color: #66d02e;
}

.incorrect {
  color: black;
  background-color: #f7d0d0;
}

.resultCorrect {
  color: #66d02e;
  font-weight: bold;
}

.resultIncorrect {
  color: #ff0000;
  font-weight: bold;
}
</style>
