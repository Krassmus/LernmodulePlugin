<template>
  <div class="stud5p-pairing">
    <div
      class="draggableElementsColumn"
      draggable="false"
      @dragover.prevent
      @dragenter.prevent
      @drop="onDropOnInteractiveElements($event)"
    >
      <button
        type="button"
        v-for="draggableElementId in draggableElements"
        :key="draggableElementId"
        :draggable="
          !this.isDraggableElementUsed(draggableElementId) && !this.showResults
        "
        @dragstart="startDragElement($event, draggableElementId)"
        @dragend="endDragElement()"
        @click="onClickDraggableElement(draggableElementId)"
        class="draggableElement"
        :class="{
          disabled:
            this.isDraggableElementUsed(draggableElementId) ||
            this.showResults ||
            this.draggedElementId === draggableElementId,
          selected: this.elementIdInteractedWith === draggableElementId,
        }"
      >
        <MultimediaElement
          :element="getElementById(draggableElementId)"
          draggable="false"
          ref="draggableImages"
        />
      </button>
    </div>
    <div class="targetElementsColumn">
      <TargetImage
        v-for="pair in this.task.pairs"
        :class="{
          outlined:
            !this.elementsDraggedOntoTargets.hasOwnProperty(
              pair.targetElement.uuid
            ) &&
            (this.draggedElementId || this.elementIdInteractedWith),
        }"
        :draggable-image="getElementDraggedOntoTarget(pair.targetElement.uuid)"
        :target-image="getElementById(pair.targetElement.uuid)"
        :isCorrect="this.isAnswerCorrect(pair.targetElement.uuid)"
        :showResult="this.showResults"
        :key="pair.uuid"
        @drop="onDropOnTargetElement($event, pair.targetElement.uuid)"
        :draggable="
          getElementDraggedOntoTarget(pair.targetElement.uuid) &&
          !this.showResults
        "
        @dragstart="startDragTargetElement($event, pair.targetElement.uuid)"
        @dragend="endDragTargetElement()"
        @dragover.prevent
        @dragenter.prevent
        @click="onClickTargetElement(pair.targetElement.uuid)"
      />
    </div>
  </div>
  <br />

  <FeedbackElement
    v-if="showResults"
    :achievedPoints="correctAnswers"
    :maxPoints="maxPoints"
    :resultMessage="resultMessage"
    :feedback="task.feedback"
  />

  <div class="button-panel">
    <button
      v-if="!this.showResults"
      v-text="this.task.strings.checkButton"
      @click="checkResults()"
      type="button"
      class="stud5p-button"
    />

    <button
      v-if="this.showResults"
      v-text="this.task.strings.retryButton"
      @click="reset()"
      type="button"
      class="stud5p-button"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  fileIdToUrl,
  LernmoduleMultimediaElement,
  PairingTask,
} from '@/models/TaskDefinition';
import TargetImage from '@/components/TargetImage.vue';
import FeedbackElement from '@/components/FeedbackElement.vue';
import MultimediaElement from '@/components/MultimediaElement.vue';

type Uuid = string;

export default defineComponent({
  name: 'PairingViewer',
  components: {
    MultimediaElement,
    TargetImage,
    FeedbackElement,
  },
  props: {
    task: {
      type: Object as PropType<PairingTask>,
      required: true,
    },
  },
  emits: ['updateAttempt'],
  data() {
    return {
      elementIdInteractedWith: undefined as Uuid | undefined,
      draggedElementId: undefined as Uuid | undefined,
      // We used Record instead of Set because I wasn't sure if sets are
      // reactive in Vue 3.
      // Record from target ID -> draggable element ID.
      elementsDraggedOntoTargets: {} as Record<Uuid, Uuid>,
      showResults: false as boolean,
    };
  },
  methods: {
    fileIdToUrl,

    isAnswerCorrect(targetId: Uuid): boolean {
      const userInput = this.elementsDraggedOntoTargets[targetId];
      if (!userInput) {
        return false;
      }
      // A little bit awkward -- we have to find the pair to which the target
      // belongs so that we can check whether the user's solution is right
      // This method itself is therefore O(n), giving an O(n^2) if you check
      // every user answer in a loop every time the user makes an input.
      // But it should be fine, because even if there are 50 elements in a set
      // (very unlikely), this comes out to 2500 comparisons, which should
      // complete in under a millisecond.
      const pair = this.task.pairs.find(
        (pair) => pair.targetElement.uuid === targetId
      );
      if (!pair) {
        throw new Error(
          'could not find the pair to which the target id ' +
            targetId +
            ' belongs'
        );
      }
      return pair.draggableElement.uuid === userInput;
    },

    getElementDraggedOntoTarget(
      targetId: Uuid
    ): LernmoduleMultimediaElement | undefined {
      const draggedElementId = this.elementsDraggedOntoTargets[targetId];
      if (draggedElementId) {
        return this.getElementById(draggedElementId);
      } else {
        return undefined;
      }
    },

    startDragElement(dragEvent: DragEvent, elementId: Uuid): void {
      /**
       * TODO #27: After creating MultimediaElement: Refactor drag-drop interactions.
       * The HTML5 drag-drop is hard to use with our planned refactoring
       * to create the component 'MultimediaElement'.
       * Since for HTML5 drag-drop, we have to do some hacking to make the
       * ghost image look correct, and this hacking is not very robust and
       * will fail for text/audio type content.
       * So instead of using HTML5 drag-drop (draggable=true, ondrag, ondrop), let's
       * use pointer events (pointerdown, pointermove and pointerup) and pointer capture API.
       * (See Gitlab for rest of comment...)
       */
      if (!this.isDraggableElementUsed(elementId)) {
        console.log('Dragging element:', elementId);
        this.elementIdInteractedWith = elementId;
        this.draggedElementId = elementId;

        dragEvent.dataTransfer!.dropEffect = 'move';
        dragEvent.dataTransfer!.effectAllowed = 'move';
      }
    },

    endDragElement(): void {
      this.elementIdInteractedWith = undefined;
      this.draggedElementId = undefined;
    },

    startDragTargetElement(dragEvent: DragEvent, targetElementId: Uuid): void {
      dragEvent.dataTransfer!.dropEffect = 'move';
      dragEvent.dataTransfer!.effectAllowed = 'move';
      // Remember that the element has been dragged away from a target
      // where it had been placed by the user
      dragEvent.dataTransfer!.setData('targetId', targetElementId);

      // Check if an element has been dragged onto the target already
      const userDraggedElementId =
        this.elementsDraggedOntoTargets[targetElementId];
      if (userDraggedElementId) {
        this.elementIdInteractedWith = userDraggedElementId;

        console.log(
          'Dragging element:',
          this.elementIdInteractedWith,
          'from target element',
          targetElementId
        );
      }
    },

    endDragTargetElement() {
      this.elementIdInteractedWith = undefined;
      this.draggedElementId = undefined;
    },

    onDropOnTargetElement(event: DragEvent, targetElementId: Uuid): void {
      if (!this.elementIdInteractedWith) {
        return;
      }

      // If the element has been dragged away from another target, then it should
      // be removed from that target when it is dropped onto this target.
      const otherTargetId = event.dataTransfer?.getData('targetId');
      if (otherTargetId) {
        delete this.elementsDraggedOntoTargets[otherTargetId];
      }
      // Save the dragged element onto the target where it has been dropped
      this.elementsDraggedOntoTargets[targetElementId] =
        this.elementIdInteractedWith;

      console.log(
        'Dropped element:',
        this.elementIdInteractedWith,
        'on target:',
        targetElementId
      );
      // Mark that the drag interaction is over.
      this.elementIdInteractedWith = undefined;
      this.draggedElementId = undefined;
    },

    getElementById(elementId: string): LernmoduleMultimediaElement {
      const element = this.elementsById[elementId];
      if (!element) {
        throw new Error('No element found with the given ID: ' + elementId);
      }
      return element;
    },

    onClickTargetElement(targetElementId: Uuid): void {
      console.log('Clicked on target element', targetElementId);

      // Do nothing if we are displaying the results already
      if (this.showResults) return;

      // Remove the element, if any, that has been dragged by the user onto
      // the target they have clicked.
      const usedElementId = this.elementsDraggedOntoTargets[targetElementId];
      if (usedElementId) {
        delete this.elementsDraggedOntoTargets[targetElementId];
      }
      // Check if the user clicked a draggable element and wants to put it here
      if (this.elementIdInteractedWith) {
        this.elementsDraggedOntoTargets[targetElementId] =
          this.elementIdInteractedWith;
        this.elementIdInteractedWith = undefined;
      }
    },

    // This has a complexity of O(n).  When it is called in our v-for to set
    // the class of every draggable element, it makes it O(n^2).
    // But that should be OK, because no one will put more than (say) 100
    // elements in a single task, right?
    isDraggableElementUsed(draggableElementId: Uuid): boolean {
      return Object.values(this.elementsDraggedOntoTargets).includes(
        draggableElementId
      );
    },

    onClickDraggableElement(elementId: Uuid): void {
      if (!this.isDraggableElementUsed(elementId) && !this.showResults) {
        console.log('Clicked on element:', elementId);
        this.elementIdInteractedWith = elementId;
      }
    },

    onDropOnInteractiveElements(event: DragEvent): void {
      if (!this.elementIdInteractedWith) {
        return;
      }

      // If the element has been dragged away from a target, then it should
      // be removed from that target when it is dropped here.
      const otherTargetId = event.dataTransfer?.getData('targetId');
      if (otherTargetId) {
        delete this.elementsDraggedOntoTargets[otherTargetId];
      }

      console.log('Returned element:', this.elementIdInteractedWith);

      // Mark that the interaction is over.
      this.elementIdInteractedWith = undefined;
    },

    checkResults(): void {
      this.showResults = true;
    },

    reset(): void {
      this.showResults = false;
      this.elementsDraggedOntoTargets = {};
    },
  },
  computed: {
    draggableElements(): Uuid[] {
      // Shuffle the elements
      // https://stackoverflow.com/a/46545530
      return this.task.pairs
        .map((pair) => ({ pair: pair, sort: Math.random() }))
        .sort((pair1, pair2) => pair1.sort - pair2.sort)
        .map(({ pair }) => pair.draggableElement.uuid);
    },

    elementsById(): Record<Uuid, LernmoduleMultimediaElement> {
      const elementsById: Record<Uuid, LernmoduleMultimediaElement> = {};
      for (const pair of this.task.pairs) {
        elementsById[pair.draggableElement.uuid] = pair.draggableElement;
        elementsById[pair.targetElement.uuid] = pair.targetElement;
      }
      return elementsById;
    },

    correctAnswers(): number {
      let correctAnswers = 0;
      for (const pair of this.task.pairs) {
        if (this.isAnswerCorrect(pair.targetElement.uuid)) correctAnswers++;
      }
      return correctAnswers;
    },

    maxPoints(): number {
      return this.task.pairs.length;
    },

    resultMessage(): string {
      let resultMessage = this.task.strings.resultMessage.replace(
        ':correct',
        this.correctAnswers.toString()
      );

      resultMessage = resultMessage.replace(
        ':total',
        this.maxPoints.toString()
      );

      return resultMessage;
    },
  },
});
</script>

<style scoped lang="scss">
.stud5p-pairing {
  display: flex;
  flex-direction: row;
}

.draggableElementsColumn {
  @extend .pairing-elements-grid;
  flex-grow: 1;
  user-select: none;
  margin: 0.5em;
}

.targetElementsColumn {
  @extend .pairing-elements-grid;
  flex-grow: 1;
  user-select: none;
  margin: 0.5em;
  border-radius: 0.25em;
  background-color: #eef1f4;
}

.pairing-elements-grid {
  /* Adapted from https://stackoverflow.com/a/46099319/7359454 */
  display: grid;
  grid-template-columns: repeat(auto-fill, 8em);
  grid-auto-rows: max-content;
  justify-content: space-around;
  row-gap: 1em;
  column-gap: 0.5em;
  padding: 0.5em;
}

.disabled {
  cursor: default;
  opacity: 25%;
}

.outlined {
  cursor: pointer;
  border: 2px dashed #dbe2e8;
}

.draggableElement {
  display: flex;
  margin: unset;
  padding: unset;
  background: unset;
  border: 2px solid transparent;
  border-radius: 0.5em;
  width: 8em;
  height: 8em;
  box-sizing: content-box;
}

.draggableElement.selected {
  border: 2px solid #7ba4d3;
}

.draggableElement:not(.disabled):not(.selected):hover {
  cursor: grab;
  border: 2px solid #7ba4d3;
  box-shadow: 0 0 10px 0 #406ef3;
}
</style>
