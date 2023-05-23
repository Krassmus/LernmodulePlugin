<template>
  <div class="h5pModule">
    <div ref="wrapperElement">
      <span v-for="element in parsedTemplate" :key="element.uuid">
        <span
          @click="onClickWord(element)"
          :class="classForWord(element)"
          v-html="element.text"
        />
        <!--  prettier-ignore-->
        <pre class="space"> </pre>
      </span>
    </div>

    <div>
      <button v-if="showCheckButton" @click="onClickCheck" class="h5pButton">
        {{ this.task.strings.checkButton }}
      </button>
      <button v-if="showRetryButton" @click="onClickRetry" class="h5pButton">
        {{ this.task.strings.retryButton }}
      </button>
    </div>

    <div v-if="debug">
      Marked words:
      <pre>{{ markedWords }}</pre>
      template:
      <pre>{{ this.task.template }}</pre>
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

type MarkTheWordsElement = {
  uuid: Uuid;
  text: string;
  correct: boolean;
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
      debug: false,
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

    onClickWord(word: MarkTheWordsElement) {
      if (this.isMarked(word)) {
        this.markedWords.delete(word.uuid);
      } else {
        this.markedWords.add(word.uuid);
      }
    },

    classForWord(word: MarkTheWordsElement) {
      if (this.showResults) {
        // User is done marking words and wants to see the results
        if (this.isMarked(word)) {
          if (word.correct) {
            return 'h5pCorrectAnswer';
          } else {
            return 'h5pIncorrectAnswer';
          }
        } else {
          return 'h5pStaticText';
        }
      } else {
        // User is working on the task
        if (this.isMarked(word)) {
          return 'h5pMarkedWord';
        } else {
          return 'h5pStaticText';
        }
      }
    },

    isMarked(word: MarkTheWordsElement): boolean {
      return this.markedWords.has(word.uuid);
    },
  },
  computed: {
    splitTemplate(): string[] {
      // Returns an array where the even indexes are the static text portions,
      // and the odd indexes are the correct words to be marked.
      return this.task.template.split(/\*([^*]*)\*/);
    },

    parsedTemplate(): MarkTheWordsElement[] {
      let parsedTemplate: MarkTheWordsElement[] = [];
      this.splitTemplate.forEach((templateElement, i) => {
        if (i % 2 === 0) {
          // Even indexes are the static text portions which we split further by spaces here
          templateElement
            .trim()
            .split(' ')
            .forEach((staticWord) => {
              parsedTemplate.push({
                uuid: uuidv4(),
                text: staticWord,
                correct: false,
              });
            });
        } else {
          parsedTemplate.push({
            uuid: uuidv4(),
            text: templateElement,
            correct: true,
          });
        }
      });
      return parsedTemplate;
    },
    showCheckButton(): boolean {
      return !this.task.instantFeedback;
    },
    showRetryButton(): boolean {
      return this.task.retryAllowed;
    },
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
  padding: 0.15em;

  box-shadow: inset 0 0 0 2px #cee0f4;
  cursor: pointer;
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

.h5pMarkedWord {
  color: #255c41;
  font-weight: bold;
  border: 1px solid #0a0e14;
  background-color: #d4f6e6;
}

.space {
  font-family: Sans-Serif;
  display: inline;
}
</style>
