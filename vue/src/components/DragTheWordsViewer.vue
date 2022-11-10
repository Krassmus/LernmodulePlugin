<template>
  <div class="h5pModule">
    <div ref="wrapperElement">
      <template v-for="element in parsedTemplate" :key="element.uuid">
        <span v-if="element.type === 'staticText'" class="h5pStaticText">
          {{ element.text }}
        </span>
        <template v-else-if="element.type === 'blank'">
          <span v-if="userInputs[element.uuid]">
            {{ getBlankText(userInputs[element.uuid]) }}
          </span>
          <span
            v-else
            class="h5pBlank"
            @drop="onDrop($event, element)"
            @dragover.prevent
            @dragenter.prevent
            @id="element.uuid;"
          >
          </span>
        </template>
      </template>
    </div>

    <template v-for="element in unusedAnswers" :key="element.uuid">
      <div>
        <span
          class="h5pBlankSolution"
          draggable="true"
          @dragstart="startDrag($event, element)"
        >
          {{ element.solutions[0] }}
        </span>
      </div>
    </template>

    <div>
      <button @click="onClickCheck" v-if="showCheckButton" class="h5pButton">
        {{ this.task.strings.checkButton }}
      </button>

      <button
        v-if="!showSolutions && this.task.showSolutionsAllowed"
        @click="onClickShowSolution"
        class="h5pButton"
      >
        {{ this.task.strings.solutionsButton }}
      </button>

      <button
        v-if="this.task.retryAllowed"
        @click="onClickTryAgain"
        class="h5pButton"
      >
        {{ this.task.strings.retryButton }}
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { DragTheWordsTaskDefinition } from '@/models/TaskDefinition';
import { v4 as uuidv4 } from 'uuid';
import { isEqual } from 'lodash';

type DragTheWordsElement = Blank | StaticText;
type Blank = {
  type: 'blank';
  uuid: Uuid;
  solutions: string[];
  hint: string;
};
type StaticText = {
  type: 'staticText';
  uuid: Uuid;
  text: string;
};
type Uuid = string;

export default defineComponent({
  name: 'DragTheWordsViewer',
  components: {},
  props: {
    task: {
      type: Object as PropType<DragTheWordsTaskDefinition>,
      required: true,
    },
  },
  data() {
    return {
      userInputs: {} as Record<Uuid, Uuid>,
      submittedAnswers: null as Record<Uuid, string> | null,
      debug: false,
      userWantsToSeeSolutions: false,
      draggedItemId: undefined as Uuid | undefined,
    };
  },
  methods: {
    isAnswerUsed(elementId: Uuid): boolean {
      return Object.values(this.userInputs).includes(elementId);
    },
    getBlankText(blankId: Uuid): string {
      const el = this.getElement(blankId);
      if (el.type !== 'blank') {
        throw new Error(
          'The element with the given id is not a blank: ' + blankId
        );
      }
      return el.solutions[0];
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
    startDrag(dragEvent: DragEvent, blank: Blank): void {
      if (dragEvent.dataTransfer) {
        dragEvent.dataTransfer.dropEffect = 'move';
        dragEvent.dataTransfer.effectAllowed = 'move';
        this.draggedItemId = blank.uuid;
      }
    },
    onDrop(dragEvent: DragEvent, blank: Blank): void {
      if (!this.draggedItemId) {
        throw new Error('Dragged item id is undefined');
      }
      this.userInputs[blank.uuid] = this.draggedItemId;
      this.draggedItemId = undefined;
    },
    // ignored by ann
    submittedAnswerIsCorrect(element: DragTheWordsElement): boolean {
      const blank = element as Blank;

      const submittedAnswer = this.submittedAnswers?.[blank.uuid];
      if (!submittedAnswer) {
        return false;
      } else {
        return blank.solutions.some((solution) =>
          this.isAnswerCorrect(submittedAnswer, solution)
        );
      }
    },
    isAnswerCorrect(userAnswer: string, solution: string): boolean {
      return userAnswer === solution;
    },
    onClickCheck() {
      // Save a copy of the user's inputs.
      this.submittedAnswers = { ...this.userInputs };
    },
    onClickShowSolution() {
      this.userWantsToSeeSolutions = true;
    },
    onClickTryAgain() {
      this.userWantsToSeeSolutions = false;
      this.userInputs = {};
      this.submittedAnswers = null;
    },
    classForInput(blank: DragTheWordsElement) {
      if (!this.submittedAnswers) {
        return 'h5pBlank';
      }

      if (this.userInputs?.[blank.uuid] != undefined) {
        if (this.submittedAnswerIsCorrect(blank)) {
          return 'h5pBlank h5pBlankCorrect';
        } else {
          if (
            this.submittedAnswers?.[blank.uuid] ===
            this.userInputs?.[blank.uuid]
          ) {
            return 'h5pBlank h5pBlankIncorrect';
          } else {
            return 'h5pBlank';
          }
        }
      } else {
        return 'h5pBlank';
      }
    },
  },
  computed: {
    splitTemplate(): string[] {
      // Returns an array where the even indexes are the static text portions,
      // and the odd indexes are the blanks.
      return this.task.template.split(/\*([^*]*)\*/);
    },
    parsedTemplate(): DragTheWordsElement[] {
      return this.splitTemplate.map((value, index) => {
        if (index % 2 === 0) {
          return { type: 'staticText', text: value, uuid: uuidv4() };
        } else {
          let splitString = value.split(':');
          let solutions = splitString[0].split('/');
          let hint = splitString[1];
          return {
            type: 'blank',
            solutions: solutions,
            hint: hint,
            uuid: uuidv4(),
          };
        }
      });
    },
    unusedAnswers(): Blank[] {
      return this.blanks.filter(
        (blank) => !Object.values(this.userInputs).includes(blank.uuid)
      );
    },
    blanks(): Blank[] {
      return this.parsedTemplate.filter(
        (word) => word.type === 'blank'
      ) as Blank[];
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
    showSolutions(): boolean {
      return this.userWantsToSeeSolutions;
    },
  },
});
</script>

<style scoped>
.h5pModule {
  border: 2px solid #eee;
  padding: 0.5em 0.5em 0.5em 0.5em;
}

.h5pStaticText {
  background: #ffffff;
  color: #000000;
}

.h5pBlank {
  background: #ffe;
  border: 1px solid #9dd8bb;
  color: #d8d29d;
  display: inline-block;
  width: 5em;
  height: 1em;
}

.h5pBlankSolution {
  background: #d8d29d;
  border: 1px solid #9dd8bb;
}

.h5pBlankCorrect {
  background: #9dd8bb;
  border: 1px solid #9dd8bb;
  color: #255c41;
}

.h5pBlankIncorrect {
  background-color: #f7d0d0;
  border: 1px solid #f7d0d0;
  color: #b71c1c;
  text-decoration: line-through;
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
</style>
