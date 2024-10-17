<template>
  <div class="stud5p-mark-the-words">
    <div class="mark-the-words-text">
      <template v-for="element in parsedTemplate" :key="element.uuid">
        <span
          v-if="element.type === 'solution'"
          tabindex="0"
          role="button"
          :aria-pressed="isMarked(element)"
          @click="onClickWord(element)"
          @keydown="(event) => onWordKeydown(event, element)"
          @keyup="(event) => onWordKeyup(event, element)"
          :class="['no-break', classForWord(element)]"
          v-html="element.text"
        ></span>

        <span
          v-else-if="element.type === 'word'"
          tabindex="0"
          role="button"
          :aria-pressed="isMarked(element)"
          @click="onClickWord(element)"
          @keydown="(event) => onWordKeydown(event, element)"
          @keyup="(event) => onWordKeyup(event, element)"
          :class="['no-break', classForWord(element)]"
          v-html="element.text"
        ></span>

        <span
          v-else-if="element.type === 'punctuation'"
          v-html="element.text"
        ></span>

        <br v-else-if="element.type === 'line-break'" />

        <!--  prettier-ignore-->
        <pre v-else-if="element.type === 'space'" class="space"> </pre>
      </template>
    </div>

    <!-- Feedback and button section -->
    <div class="feedback-and-button-container">
      <feedback-element
        v-if="showResults"
        :achieved-points="score"
        :max-points="maxScore"
        :feedback="this.task.feedback"
        :result-message="resultMessage"
      />

      <div class="stud5p-button-panel">
        <button
          v-if="showCheckButton"
          v-text="this.task.strings.checkButton"
          @click="onClickCheck"
          type="button"
          class="stud5p-button"
        />

        <button
          v-if="showRetryButton"
          v-text="this.task.strings.retryButton"
          @click="onClickRetry"
          type="button"
          class="stud5p-button"
        />
      </div>
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

type Uuid = string;

type MarkTheWordsElement = Solution | Word | Space | Punctuation | LineBreak;

interface Solution {
  type: 'solution';
  uuid: Uuid;
  text: string;
}

interface Word {
  type: 'word';
  uuid: Uuid;
  text: string;
}

interface Space {
  type: 'space';
  uuid: Uuid;
}

interface Punctuation {
  type: 'punctuation';
  uuid: Uuid;
  text: string;
}

interface LineBreak {
  type: 'line-break';
  uuid: Uuid;
}

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
          if (word.type === 'solution') {
            return 'correct-answer';
          } else {
            return 'incorrect-answer';
          }
        } else {
          return 'static-word-no-hover';
        }
      } else {
        // User is working on the task
        if (this.isMarked(word)) {
          return 'marked-word';
        } else {
          return 'static-word';
        }
      }
    },

    isMarked(word: MarkTheWordsElement): boolean {
      return this.markedWords.has(word);
    },

    sanitizeText(text: string) {
      // Remove all opening and closing <p> tags and all occurrences of &nbsp;
      return text.replace(/<\/?p>/g, '').replace(/&nbsp;/g, ' ');
    },
  },
  computed: {
    splitTemplate(): string[] {
      // Step 1: Sanitize the template (removes <p> tags and &nbsp;)
      const cleanTemplate = this.sanitizeText(this.task.template);

      // Step 2: Split by solutions (words surrounded by asterisks)
      // This captures solutions as one piece and separates the rest of the text.
      const splitBySolutions = cleanTemplate.split(/(\*[^*]+\*)/g);

      // Step 3: Process each chunk: If it's a solution, leave it intact.
      // If it's not a solution, split it further by line breaks, punctuation and spaces.
      const finalSplit = splitBySolutions.flatMap((part) => {
        if (/^\*[^*]+\*$/.test(part)) {
          // This part is a solution (surrounded by asterisks), leave it intact
          return part;
        } else {
          // This part is regular text, split by line breaks, spaces, and punctuation
          return part.split(/(<br\s*\/?>|\s+|[.,!?;:()–—])/g).filter(Boolean); // Remove empty parts
        }
      });

      // Step 4: Return the final split array
      return finalSplit;
    },

    parsedTemplate(): MarkTheWordsElement[] {
      let parsedTemplate: MarkTheWordsElement[] = [];

      this.splitTemplate.forEach((templateElement) => {
        if (/^\*[^*]+\*$/.test(templateElement)) {
          // If it's a solution (word surrounded by asterisks)
          // Remove the asterisks first
          const content = templateElement.replace(/\*/g, '');

          parsedTemplate.push({
            type: 'solution',
            uuid: uuidv4(),
            text: content,
          });
        } else if (/^\s+$/.test(templateElement)) {
          // If it's a space
          parsedTemplate.push({
            type: 'space',
            uuid: uuidv4(),
          });
        } else if (/^<br\s*\/?>$/.test(templateElement)) {
          // If it's a line break
          parsedTemplate.push({
            type: 'line-break',
            uuid: uuidv4(),
          });
        } else if (/[.,!?;:()–—]/.test(templateElement)) {
          // If it's a punctuation mark
          parsedTemplate.push({
            type: 'punctuation',
            uuid: uuidv4(),
            text: templateElement,
          });
        } else {
          // It's a regular word (including hyphenated words)
          parsedTemplate.push({
            type: 'word',
            uuid: uuidv4(),
            text: templateElement,
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
        if (element.type === 'solution') {
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
        if (element.type === 'solution') {
          maxScore++;
        }
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
.static-word {
  background: #ffffff;
  color: #000000;
}

.static-word:hover {
  box-shadow: 0 0 0 1px #cee0f4;
  border-radius: 0.25em;
  cursor: pointer;
}

.static-word-no-hover {
  background: #ffffff;
  color: #000000;
}

.marked-word {
  box-shadow: 0 0 0 1px #cee0f4;
  border-radius: 0.25em;
  background-color: #d4f1f6;
}

.marked-word:hover {
  box-shadow: 0 0 0 1px #cee0f4;
  border-radius: 0.25em;
  cursor: pointer;
}

.correct-answer {
  color: #255c41;
  box-shadow: 0 0 0 1px #d4f6e6;
  border-radius: 0.25em;
  background-color: #d4f6e6;
}

.incorrect-answer {
  color: #b71c1c;
  box-shadow: 0 0 0 1px #f7d0d0;
  border-radius: 0.25em;
  background-color: #f7d0d0;
  text-decoration: line-through;
}

.space {
  font-family: Sans-Serif;
  display: inline;
}

.no-break {
  white-space: nowrap; /* Prevents line break inside the element */
  display: inline-block; /* Makes sure it behaves like a block in inline context */
}

.feedback-and-button-container {
  display: flex;
  align-items: flex-end;
  gap: 1em;
}
</style>
