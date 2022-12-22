<template>
  <div class="h5pModule">
    <div ref="wrapperElement">
      <span v-for="element in parsedTemplate" :key="element.uuid">
        <!-- Show clickable words, the user is working on the task-->
        <span
          @click="onClickMarkWord(element.uuid)"
          :class="classForWord(element)"
        >
          {{ element.text }}
        </span>
        <!--  prettier-ignore-->
        <pre class="space"> </pre>
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
    <div v-if="debug">
      selectedWords:
      <pre>{{ markedWords }}</pre>
      Split template:
      <pre>{{ splitTemplate }}</pre>
      Parsed template:
      <pre>{{ parsedTemplate }}</pre>
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
      markedWords: new Set<string>(),
      showResults: false,
      debug: true,
    };
  },

  methods: {
    onClickCheck() {
      this.showResults = true;
    },

    onClickRetry() {
      this.showResults = false;
      this.markedWords.clear();
    },

    onClickMarkWord(wordId: Uuid) {
      let word = this.getElement(wordId);

      this.markedWords.add(wordId);
    },

    classForWord(word: MarkTheWordsElement) {
      if (this.showResults) {
        // User is done marking words and wants to see the results
        if (this.isMarked(word)) {
          if (this.isCorrect(word)) {
            return 'h5pCorrectAnswer';
          } else {
            return 'h5pIncorrectAnswer';
          }
        } else {
          return 'h5pStaticText';
        }
      } else {
        // User is working on the task
        return 'h5pStaticText';
      }
    },

    isMarked(word: MarkTheWordsElement): boolean {
      return this.markedWords.has(word.uuid);
    },

    isCorrect(word: MarkTheWordsElement): boolean {
      // Object.entries(this.markedWords).find((element) => element.uuid === word);
      return true;
    },

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

.h5pCorrectAnswer {
  color: #255c41;
  font-weight: bold;
  border: 1px #255c41 dashed;
  background-color: #d4f6e6;
  padding: 0.15em;
  border-radius: 0.25em;
  margin-left: 0.5em;
}

.space {
  font-family: Sans-Serif;
  display: inline;
}
</style>
