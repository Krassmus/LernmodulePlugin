<template>
  <div class="stud5p-task">
    <!-- Main container for text and answers -->
    <div class="text-and-answers-container">
      <!-- Render the text and blank elements from parsedTemplate -->
      <div class="text-and-feedback-container">
        <div class="drag-the-words-text">
          <template v-for="element in parsedTemplate" :key="element.uuid">
            <!-- Static text elements -->
            <span
              v-if="element.type === 'staticText'"
              class="static-text"
              v-html="element.text"
            />

            <!-- Blanks -->
            <template v-else-if="element.type === 'blank'">
              <!-- Filled blank -->
              <span
                v-if="userInputs[element.uuid]"
                class="blank filled"
                :class="classForFilledBlank(element)"
                :draggable="editable"
                @dragstart="
                  startDragUsedAnswer($event, element, userInputs[element.uuid])
                "
                @drop="onDropBlank($event, element)"
                @dragover.prevent
                @click="onClickFilledBlank(element)"
              >
                {{ getAnswerById(userInputs[element.uuid])?.text }}
              </span>

              <!-- Empty blank -->
              <span
                v-else
                class="blank"
                @drop="onDropBlank($event, element)"
                @dragenter="onDragEnter($event)"
                @dragleave="onDragLeave($event)"
                @dragover.prevent
                @click="onClickBlank(element)"
              >
                &#8203;
              </span>

              <!-- Hint tooltip -->
              <label v-if="element.hint">
                <span
                  class="tooltip tooltip-icon"
                  :data-tooltip="element.hint"
                />
              </label>

              <!-- Show solution if incorrect and solutions are revealed -->
              <span
                v-if="showSolutions && !submittedAnswerIsCorrect(element)"
                class="stud5p-solution"
              >
                {{ element.solution }}
              </span>
            </template>
          </template>
        </div>

        <!-- Feedback and button section -->
        <div class="feedback-and-button-container">
          <!-- Message prompting user to fill in all blanks before they can show solutions-->
          <div
            v-if="showFillInAllTheBlanksMessage"
            class="stud5p-message"
            v-text="fillInAllTheBlanksMessage"
          />

          <!-- Feedback element displaying points and feedback -->
          <FeedbackElement
            v-if="showResults"
            :achieved-points="correctAnswers"
            :max-points="maxPoints"
            :feedback="task.feedback"
            :result-message="resultMessage"
          />

          <div class="button-panel">
            <button
              v-if="showCheckButton"
              v-text="task.strings.checkButton"
              @click="onClickCheckButton"
              type="button"
              class="stud5p-button"
            />

            <button
              v-if="showSolutionsButton"
              v-text="task.strings.solutionsButton"
              @click="onClickSolutionsButton"
              type="button"
              class="stud5p-button"
            />

            <button
              v-if="showRetryButton"
              v-text="task.strings.retryButton"
              @click="onClickRetryButton"
              type="button"
              class="stud5p-button"
            />
          </div>
        </div>
      </div>

      <!-- Container for unused answers (draggable answers) -->
      <div
        class="unused-answers-list"
        @drop="onDropUnusedAnswers($event)"
        @dragover.prevent
      >
        <span
          v-for="answer in unusedAnswers"
          :key="answer.uuid"
          class="unused-answer"
          :class="{
            disabled: !this.editable,
            selected: answer.uuid === this.clickedAnswerId,
          }"
          :draggable="editable"
          @dragstart="startDragUnusedAnswer($event, answer)"
          @click="onClickUnusedAnswer(answer)"
        >
          {{ answer.text }}
        </span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { DragTheWordsTask } from '@/models/TaskDefinition';
import { v4 as uuidv4 } from 'uuid';
import { isEqual } from 'lodash';
import { $gettext } from '@/language/gettext';
import FeedbackElement from '@/components/FeedbackElement.vue';

type DragTheWordsElement = Blank | StaticText;

interface Blank {
  type: 'blank';
  uuid: Uuid;
  solution: string;
  hint: string;
}

interface Answer {
  uuid: Uuid;
  text: string;
}

type StaticText = {
  type: 'staticText';
  uuid: Uuid;
  text: string;
};

type Uuid = string;

export default defineComponent({
  name: 'DragTheWordsViewer',
  components: { FeedbackElement },
  props: {
    task: {
      type: Object as PropType<DragTheWordsTask>,
      required: true,
    },
  },
  data() {
    return {
      // Map from Blank IDs to Answer IDs
      userInputs: {} as Record<Uuid, Uuid>,
      submittedAnswers: null as Record<Uuid, Uuid> | null,
      userWantsToSeeSolutions: false,
      draggedAnswerId: undefined as Uuid | undefined,
      draggedSourceId: undefined as Uuid | undefined,
      clickedAnswerId: undefined as Uuid | undefined,
      editable: true as Boolean,
    };
  },
  methods: {
    $gettext,

    submittedAnswerIsCorrect(blank: Blank): boolean {
      const submittedAnswerId = this.submittedAnswers?.[blank.uuid];
      if (!submittedAnswerId) return false;

      const submittedAnswer = this.getAnswerById(submittedAnswerId)?.text;
      if (!submittedAnswer) return false;

      return this.isAnswerCorrect(submittedAnswer, blank.solution);
    },

    isAnswerCorrect(userAnswer: string, solution: string): boolean {
      return userAnswer === solution;
    },

    updateAttempt() {
      // Tell the server which blanks were filled out correctly.
      const points = {} as Record<string, number>;
      this.blanks.forEach((blank, index) => {
        // TODO Consider what the key should be. This is a bit ugly.
        // The key is shown directly in the 'assessment' UI.
        // Maybe we should change how that works?
        points[`${index} - ${blank.solution}`] = this.submittedAnswerIsCorrect(
          blank
        )
          ? 1
          : 0;
      });
      this.$emit('updateAttempt', {
        points,
        // The attempt is marked as successful if all answers were correct.
        success: this.correctAnswers === this.blanks.length,
      });
    },

    getElement(elementId: Uuid): DragTheWordsElement {
      const el = this.parsedTemplate.find((el) => el.uuid === elementId);
      if (!el) {
        throw new Error(
          'The given element does not exist in parsedTemplate: ' + elementId
        );
      }
      return el;
    },

    startDragUsedAnswer(
      dragEvent: DragEvent,
      blank: Blank,
      answerId: Uuid
    ): void {
      if (!this.editable) return;
      if (dragEvent.dataTransfer) {
        dragEvent.dataTransfer.dropEffect = 'move';
        dragEvent.dataTransfer.effectAllowed = 'move';
      }
      const answer = this.getAnswerById(answerId);
      console.log('Dragging answer: ', answer, 'From blank: ', blank);

      for (const property in this.userInputs) {
        console.log(property);
      }

      this.draggedAnswerId = answerId;
    },

    startDragUnusedAnswer(dragEvent: DragEvent, answer: Answer): void {
      if (dragEvent.dataTransfer) {
        dragEvent.dataTransfer.dropEffect = 'move';
        dragEvent.dataTransfer.effectAllowed = 'move';

        console.log('Dragging answer: ', answer);
        this.draggedAnswerId = answer.uuid;
      }
    },

    onDropBlank(dragEvent: DragEvent, blank: Blank): void {
      if (!this.draggedAnswerId) {
        throw new Error('Dragged answer id is undefined');
      }

      console.log('Dropped answer :', this.draggedAnswer, 'on blank: ', blank);

      // Remove the dropped answer from the blank, if any, where it has been dragged from
      const previousBlank = this.blanks.find(
        (blank) => this.userInputs[blank.uuid] === this.draggedAnswerId
      );

      if (previousBlank) {
        delete this.userInputs[previousBlank.uuid];
      }

      // Set the answer for the blank where the answer was dropped
      this.userInputs[blank.uuid] = this.draggedAnswerId;

      this.draggedAnswerId = undefined;
    },

    onDropUnusedAnswers(ev: DragEvent): void {
      ev.preventDefault(); // Indicate that dropping is al
      if (!this.draggedAnswer) {
        throw new Error('draggedAnswer is undefined');
      }
      // Remove the dropped answer from the blank, if any, where it has been dragged from
      const blank = this.blanks.find(
        (blank) => this.userInputs[blank.uuid] === this.draggedAnswerId
      );
      if (blank) {
        delete this.userInputs[blank.uuid];
      }
    },

    onDragEnter(event: DragEvent) {
      const target = event.target as HTMLElement | null;
      if (target) {
        target.style.backgroundColor = '#80b4ed';
      }
    },

    onDragLeave(event: DragEvent) {
      const target = event.target as HTMLElement | null;
      if (target) {
        target.style.backgroundColor = '';
      }
    },

    onClickCheckButton() {
      // Save a copy of the user's inputs.
      this.submittedAnswers = { ...this.userInputs };
      this.editable = false;
    },

    onClickSolutionsButton() {
      this.userWantsToSeeSolutions = true;
    },

    onClickRetryButton() {
      this.userWantsToSeeSolutions = false;
      this.userInputs = {};
      this.submittedAnswers = null;
      this.editable = true;
    },

    classForFilledBlank(blank: Blank) {
      if (!this.submittedAnswers && !this.task.instantFeedback) {
        return '';
      }

      if (this.task.instantFeedback) {
        this.submittedAnswers = { ...this.userInputs };
      }

      if (this.userInputs?.[blank.uuid]) {
        if (this.submittedAnswerIsCorrect(blank)) {
          return 'correct disabled';
        } else {
          return 'incorrect disabled';
        }
      } else {
        return '';
      }
    },

    getAnswerById(id: Uuid): Answer | undefined {
      return this.answers.find((answer) => answer.uuid === id);
    },

    onClickUnusedAnswer(answer: Answer): void {
      console.log('Clicked answer:', answer);
      if (this.editable) this.clickedAnswerId = answer.uuid;
    },

    onClickBlank(blank: Blank): void {
      console.log('Clicked blank:', blank);
      if (!this.clickedAnswerId) return;
      this.userInputs[blank.uuid] = this.clickedAnswerId;
      this.clickedAnswerId = undefined;
    },

    onClickFilledBlank(blank: Blank): void {
      console.log('Clicked filled blank:', blank);
      if (!this.editable) return;

      if (blank) {
        delete this.userInputs[blank.uuid];
      }
    },

    removePTags(text: string) {
      // Remove all opening and closing <p> tags
      return text.replace(/<\/?p>/g, '');
    },
  },
  computed: {
    splitTemplate(): string[] {
      const cleanTemplate = this.removePTags(this.task.template);
      // Returns an array where the even indexes are the static text portions,
      // and the odd indexes are the blanks.
      return cleanTemplate.split(/\*([^*]*)\*/);
    },

    parsedTemplate(): DragTheWordsElement[] {
      return this.splitTemplate.map((value, index) => {
        if (index % 2 === 0) {
          return { type: 'staticText', text: value, uuid: uuidv4() };
        } else {
          let splitString = value.split(':');
          let solution = splitString[0];
          let hint = splitString[1];
          return {
            type: 'blank',
            solution: solution,
            hint: hint,
            uuid: uuidv4(),
          };
        }
      });
    },

    parsedDistractors(): string[] {
      // Extract distractors from the input
      const distractors = (this.task.distractors.match(/\*(.*?)\*/g) || []) // Handle cases with no matches
        .map((distractor) => distractor.replace(/\*/g, '').trim());

      return distractors;
    },

    blanks(): Blank[] {
      return this.parsedTemplate.filter(
        (word) => word.type === 'blank'
      ) as Blank[];
    },

    answers(): Answer[] {
      const correctAnswers = this.blanks.map((blank) => ({
        uuid: uuidv4(),
        text: blank.solution,
      }));

      const distractorAnswers = this.parsedDistractors.map((distractor) => ({
        uuid: uuidv4(),
        text: distractor,
      }));

      return [...correctAnswers, ...distractorAnswers];
    },

    draggedAnswer(): Answer | undefined {
      if (!this.draggedAnswerId) {
        return undefined;
      } else {
        return this.getAnswerById(this.draggedAnswerId);
      }
    },

    randomizedAnswers(): Answer[] {
      // https://stackoverflow.com/a/46545530
      return this.answers
        .map((value) => ({ value, sort: Math.random() }))
        .sort((a, b) => a.sort - b.sort)
        .map(({ value }) => value);
    },

    sortedAnswers(): Answer[] {
      // https://stackoverflow.com/a/45544166
      return this.answers
        .map((value) => value)
        .sort((a, b) => a.text.localeCompare(b.text));
    },

    blanksFilled(): number {
      if (!this.submittedAnswers) {
        return 0;
      } else {
        return Object.keys(this.submittedAnswers).length;
      }
    },

    allBlanksAreFilled(): boolean {
      return this.blanksFilled == this.blanks.length;
    },

    correctAnswers(): number {
      return this.blanks.filter((blank) => this.submittedAnswerIsCorrect(blank))
        .length;
    },

    allAnswersAreCorrect(): boolean {
      return this.blanks.every((blank) => this.submittedAnswerIsCorrect(blank));
    },

    maxPoints(): number {
      return this.blanks.length;
    },

    unusedAnswers(): Answer[] {
      if (this.task.alphabeticOrder) {
        return this.sortedAnswers.filter(
          (answer) => !Object.values(this.userInputs).includes(answer.uuid)
        );
      } else {
        return this.randomizedAnswers.filter(
          (answer) => !Object.values(this.userInputs).includes(answer.uuid)
        );
      }
    },

    inputHasChanged(): boolean {
      return !isEqual(this.submittedAnswers, this.userInputs);
    },

    showCheckButton(): boolean {
      return (
        (this.submittedAnswers === null || this.inputHasChanged) &&
        !this.task.instantFeedback
      );
    },

    showSolutionsButton(): boolean {
      return (
        this.task.showSolutionsAllowed &&
        !this.showSolutions &&
        this.showResults &&
        !this.allAnswersAreCorrect
      );
    },

    showRetryButton(): boolean {
      return (
        this.task.retryAllowed &&
        this.showResults &&
        !this.allAnswersAreCorrect &&
        this.submittedAnswers !== null
      );
    },

    showSolutions(): boolean {
      return (
        this.userWantsToSeeSolutions &&
        (this.allBlanksAreFilled ||
          !this.task.allBlanksMustBeFilledForSolutions)
      );
    },

    showFillInAllTheBlanksMessage(): boolean {
      return (
        this.task.allBlanksMustBeFilledForSolutions &&
        this.userWantsToSeeSolutions &&
        !this.allBlanksAreFilled &&
        !this.inputHasChanged
      );
    },

    showResults(): boolean {
      return (
        !(this.submittedAnswers === null) &&
        !this.showFillInAllTheBlanksMessage &&
        (!this.task.instantFeedback || this.allBlanksAreFilled) &&
        !this.inputHasChanged
      );
    },

    resultMessage(): string {
      let resultMessage = this.task.strings.resultMessage.replace(
        ':correct',
        this.correctAnswers.toString()
      );

      resultMessage = resultMessage.replace(
        ':total',
        this.blanks.length.toString()
      );

      return resultMessage;
    },

    fillInAllTheBlanksMessage(): string {
      return this.task.strings.fillInAllBlanksMessage
        ? this.task.strings.fillInAllBlanksMessage
        : $gettext(
            'Alle Lücken müssen ausgefüllt sein, um Lösungen anzuzeigen'
          );
    },
  },
});
</script>

<style scoped>
.static-text {
  display: inline;
  vertical-align: middle;

  background: #ffffff;
  color: #000000;
}

.blank {
  display: inline-block;
  min-width: 9em;
  max-width: 9em;
  vertical-align: middle;
  line-height: 1.25em;

  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;

  background: #cee0f4;
  color: #1a4473;
  text-align: center;

  border: 1px solid #a9c3d0;
  border-radius: 0.25em;
}

.filled {
  cursor: grabbing;
}

.filled:not(.disabled):hover {
  border: 1px solid rgb(212, 190, 216);
  color: #663366;
  background: #edd6e9;
}

.correct {
  background: #9dd8bb;
  border: 1px solid #9dd8bb;
  color: #255c41;
}

.incorrect {
  background-color: #f7d0d0;
  border: 1px solid #f7d0d0;
  color: #b71c1c;
  text-decoration: line-through;
}

span.item:empty:before {
  content: '\200b';
}

.unused-answers-list {
  min-width: 12em;
  padding-top: 0.5em;
  padding-bottom: 0.5em;

  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75em;

  border: 1px solid #eee;
  border-radius: 5px;
}

.unused-answer {
  display: inline-block;
  min-width: 9em;
  max-width: 9em;
  line-height: 1.25em;

  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;

  background: #ddd;
  box-shadow: 0 0 0.3em rgba(0, 0, 0, 0.2);
  text-align: center;

  border: 1px solid #c6c6c6;
  border-radius: 0.25em;

  cursor: grabbing;
}

.unused-answer:not(.disabled):hover {
  border: 0.1em solid rgb(212, 190, 216);
  color: #663366;
  background: #edd6e9;
}

.disabled {
  cursor: default;
}

.selected:not(.disabled) {
  border: 0.1em solid rgb(212, 190, 216);
  color: #663366;
  background: #edd6e9;
}

.text-and-feedback-container {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.drag-the-words-text {
  flex-grow: 0;
}

.text-and-answers-container {
  display: flex;
  gap: 1em;
}
</style>
