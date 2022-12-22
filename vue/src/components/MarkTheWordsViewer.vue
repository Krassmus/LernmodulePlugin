<template>
  <div class="h5pModule">
    <div ref="wrapperElement">
      <span v-for="element in parsedTemplate" :key="element.uuid">
        <template v-if="!showResults">
          <!-- Show clickable words, the user is working on the task-->
          <span
            @click="onClickMarkWord(element.uuid)"
            :class="classForWord(element)"
          >
            {{ element.text }}
          </span>
          <!--  prettier-ignore-->
          <pre class="space"> </pre>
        </template>
        <template v-else>
          <!-- Show the results for the user-->
          <span
            @click="onClickMarkWord(element.uuid)"
            class="h5pStaticTextResult"
          >
            {{ element.text }}
          </span>
          <!--  prettier-ignore-->
          <pre class="space"> </pre>
        </template>
      </span>
    </div>

    <div>
      <button v-if="showCheckButton" @click="onClickCheck" class="h5pButton">
        {{ this.task.strings.checkButton }}
      </button>

      <button
        v-if="showSolutionButton"
        @click="onClickSolution"
        class="h5pButton"
      >
        {{ this.task.strings.solutionsButton }}
      </button>

      <button v-if="showRetryButton" @click="onClickRetry" class="h5pButton">
        {{ this.task.strings.retryButton }}
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MarkTheWordsTaskDefinition } from '@/models/TaskDefinition';
import { v4 as uuidv4 } from 'uuid';
import { isEmpty } from 'lodash';

type MarkTheWordsElement = {
  uuid: Uuid;
  text: string;
};

type Uuid = string;

export default defineComponent({
  name: 'MarkTheWordsViewer',
  props: {
    task: {
      type: Object as PropType<MarkTheWordsTaskDefinition>,
      required: true,
    },
  },
  data() {
    return {
      selectedWords: {} as Record<Uuid, string>,
      showResults: false,
    };
  },

  methods: {
    onClickCheck() {
      this.showResults = true;
    },
    onClickRetry() {
      this.showResults = false;
      this.selectedWords = {};
    },
    classForWord(word: MarkTheWordsElement) {
      if (this.showResults) {
        return 'h5pStaticTextResult';
      } else {
        return 'h5pStaticText';
      }

      // if (this.userInputs?.[blank.uuid] != undefined) {
      //   if (this.submittedAnswerIsCorrect(blank)) {
      //     return 'h5pBlank h5pBlankCorrect';
      //   } else {
      //     if (
      //       this.submittedAnswers?.[blank.uuid] ===
      //       this.userInputs?.[blank.uuid]
      //     ) {
      //       return 'h5pBlank h5pBlankIncorrect';
      //     } else {
      //       return 'h5pBlank';
      //     }
      //   }
      // } else {
      //   return 'h5pBlank';
      // }
    },

    onClickMarkWord(wordId: Uuid) {
      let word = this.getElement(wordId);

      this.selectedWords[wordId] = word.text;
    },

    // getBlankText(blankId: Uuid): string {
    //   const el = this.getElement(blankId);
    //   if (el.type !== 'blank') {
    //     throw new Error(
    //       'The element with the given id is not a blank: ' + blankId
    //     );
    //   }
    //   return el.solutions[0];
    // },

    getElement(elementId: Uuid): MarkTheWordsElement {
      const element = this.parsedTemplate.find((el) => el.uuid === elementId);
      if (!element) {
        throw new Error(
          'The given element does not exist in parsedTemplate: ' + elementId
        );
      }
      return element;
    },
  },
  computed: {
    splitTemplate(): string[] {
      // Returns an array of words in the template split by spaces
      return this.task.template.split(' ');
    },
    parsedTemplate(): MarkTheWordsElement[] {
      // Returns an array of MarkTheWordsElements, which is a single word of the template with an UUID
      return this.splitTemplate.map((value) => {
        return { uuid: uuidv4(), text: value };
      });
    },
    showCheckButton(): boolean {
      return !this.task.instantFeedback;
    },
    showRetryButton(): boolean {
      return this.task.retryAllowed;
    },
    // showSolutionButton(): boolean {
    //   return !this.showSolutions && this.task.showSolutionsAllowed;
    // },
  },
});
</script>

<style scoped>
.h5pModule {
  border: 2px solid #eee;
  padding: 0.5em 0.5em 0.5em 0.5em;
}

.h5pButton {
  font-size: 1em;
  line-height: 1.2;
  margin: 1em 0.5em 1em;
  padding: 0.5em 1.25em;
  border-radius: 2em;
  background: #1a73d9;
  color: #fff;
  cursor: pointer;
  border: none;
  box-shadow: none;
  display: inline-block;
  text-align: center;
  text-shadow: none;
  text-decoration: none;
  vertical-align: baseline;
}

.h5pStaticText {
  background: #ffffff;
  color: #000000;
  line-height: 2em;
}

.h5pStaticText:hover {
  border: 1px solid #0a0e14;
}

.h5pStaticTextResult {
  background: #ffffff;
  color: #2fff00;
  line-height: 2em;
}

.space {
  font-family: Sans-Serif;
  display: inline;
}
</style>
