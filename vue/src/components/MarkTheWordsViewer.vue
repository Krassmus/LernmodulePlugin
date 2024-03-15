<template>
  <div class="h5pModule">
    <div class="h5pMarkTheWordText" ref="wrapperElement">
      <span v-for="(element, index) in parsedTemplate" :key="element.uuid">
        <span
          tabindex="0"
          role="button"
          :aria-pressed="isMarked(element)"
          @click="onClickWord(element)"
          @keydown="(event) => onWordKeydown(event, element)"
          @keyup="(event) => onWordKeyup(event, element)"
          :class="classForWord(element)"
          v-html="element.text"
        />
        <!--  prettier-ignore-->
        <pre v-if="index < parsedTemplate.length" class="space"> </pre>
      </span>
    </div>

    <feedback-element
      v-if="showResults"
      :achieved-points="score"
      :max-points="maxScore"
      :feedback="this.task.feedback"
      :result-message="resultMessage"
    />

    <div class="h5pButtonPanel">
      <button
        v-if="showCheckButton"
        @click="onClickCheck"
        class="h5pButton"
        v-text="this.task.strings.checkButton"
      />
      <button
        v-if="showRetryButton"
        @click="onClickRetry"
        class="h5pButton"
        v-text="this.task.strings.retryButton"
      />
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
import { MarkTheWordsTask } from '@/models/TaskDefinition';
import { v4 as uuidv4 } from 'uuid';
import FeedbackElement from '@/components/FeedbackElement.vue';

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
      type: Object as PropType<MarkTheWordsTask>,
      required: true,
    },
  },
  components: {
    FeedbackElement,
  },
  data() {
    return {
      markedWords: new Set<MarkTheWordsElement>(),
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
      if (this.showResults) return;

      if (this.isMarked(word)) {
        this.markedWords.delete(word);
      } else {
        this.markedWords.add(word);
      }
    },

    /**
     * Marks/unmarks a word using the enter key.
     */
    onWordKeydown(event: KeyboardEvent, word: MarkTheWordsElement) {
      // The action button is activated by space on the keyup event, but the
      // default action for space is already triggered on keydown. It needs to be
      // prevented to stop scrolling the page before activating the button.
      if (event.code === 'Space') {
        event.preventDefault();
      }
      // If enter is pressed, activate the button
      else if (event.code === 'Enter' || event.code === 'Return') {
        event.preventDefault();
        this.onClickWord(word);
      }
    },

    /**
     * Marks/unmarks a word with the space key.
     */
    onWordKeyup(event: KeyboardEvent, word: MarkTheWordsElement) {
      if (event.code === 'Space') {
        event.preventDefault();
        this.onClickWord(word);
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
          return 'h5pStaticTextNoHover';
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
      return this.markedWords.has(word);
    },
  },
  computed: {
    /**
     * Returns an array where the even indexes are the static text portions,
     * and the odd indexes are the correct words to be marked.
     */
    splitTemplate(): string[] {
      // Split the text into chunks based on pairs of asterisks
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
            .filter((el) => el !== '')
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
      return !this.showResults;
    },

    showRetryButton(): boolean {
      return this.task.retryAllowed && this.showResults;
    },

    score(): number {
      let score = 0;

      for (const element of this.markedWords) {
        if (element.correct) {
          score++;
        } else {
          score--;
        }
      }

      if (score < 0) score = 0;

      return score;
    },

    maxScore(): number {
      let maxScore = 0;
      for (const element of this.parsedTemplate) {
        if (element.correct) maxScore++;
      }
      return maxScore;
    },

    resultMessage(): string {
      let resultMessage = this.task.strings.resultMessage.replace(
        ':correct',
        this.score.toString()
      );

      resultMessage = resultMessage.replace(':total', this.maxScore.toString());

      return resultMessage;
    },
  },
});
</script>

<style scoped>
.h5pStaticText {
  background: #ffffff;
  color: #000000;
}

.h5pStaticText:hover {
  box-shadow: 0 0 0 1px #cee0f4;
  border-radius: 0.25em;
  cursor: pointer;
}

.h5pStaticTextNoHover {
  background: #ffffff;
  color: #000000;
}

.h5pMarkedWord {
  box-shadow: 0 0 0 1px #cee0f4;
  border-radius: 0.25em;
  background-color: #d4f1f6;
}

.h5pMarkedWord:hover {
  box-shadow: 0 0 0 1px #cee0f4;
  border-radius: 0.25em;
  cursor: pointer;
}

.h5pCorrectAnswer {
  color: #255c41;
  box-shadow: 0 0 0 1px #d4f6e6;
  border-radius: 0.25em;
  background-color: #d4f6e6;
}

.h5pIncorrectAnswer {
  color: #b71c1c;
  box-shadow: 0 0 0 1px #f7d0d0;
  border-radius: 0.25em;
  background-color: #f7d0d0;
  text-decoration: line-through;
}

.h5pMarkTheWordText {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
}

.space {
  font-family: Sans-Serif;
  display: inline;
}
</style>
