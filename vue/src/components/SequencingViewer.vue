<template>
  <div class="stud5p-sequencing">
    <div
      v-for="image in images"
      :key="image.uuid"
      class="image-container"
      :class="{
        dragged: imageInteractedWith === image,
        disabled: isShowingResults,
        correct: isShowingResults && isImageInCorrectPosition(image),
        incorrect: isShowingResults && !isImageInCorrectPosition(image),
      }"
      :draggable="!isShowingResults"
      @dragstart="(event) => startDragImage(image, event)"
      @drag="onDrag(image)"
      @dragend="endDragImage(image)"
      @dragover="onDragOver(image)"
      @click="onClick(image)"
    >
      <img
        class="image"
        draggable="false"
        @dragover.prevent
        @dragenter.prevent
        :src="fileIdToUrl(image.file_id)"
        :alt="image.altText"
      />
      <span class="image-description" @dragover.prevent @dragenter.prevent>{{
        image.altText
      }}</span>
    </div>
  </div>

  <FeedbackElement
    v-if="isShowingResults && !isShowingSolutions"
    :achievedPoints="correctAnswers"
    :maxPoints="maxPoints"
    :resultMessage="resultMessage"
    :feedback="task.feedback"
  />

  <div class="h5p-button-panel">
    <button
      v-if="!isShowingResults"
      v-text="task.strings.checkButton"
      @click="showResults()"
      type="button"
      class="h5p-button"
    />

    <button
      v-if="isShowingResults && !isShowingSolutions && !allAnswersAreCorrect"
      v-text="task.strings.solutionsButton"
      @click="showSolutions()"
      type="button"
      class="h5p-button"
    />

    <button
      v-if="isShowingResults"
      v-text="task.strings.retryButton"
      @click="reset()"
      type="button"
      class="h5p-button"
    />

    <button
      v-if="isShowingResults && !isShowingSolutions && !allAnswersAreCorrect"
      v-text="task.strings.continueButton"
      @click="continueTask()"
      type="button"
      class="h5p-button"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { fileIdToUrl, Image, SequencingTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import FeedbackElement from '@/components/FeedbackElement.vue';

export default defineComponent({
  name: 'SequencingViewer',

  components: {
    FeedbackElement,
  },

  props: {
    task: {
      type: Object as PropType<SequencingTask>,
      required: true,
    },
  },

  emits: ['updateAttempt'],

  data() {
    return {
      images: [] as Image[],
      imageInteractedWith: undefined as Image | undefined,
      isShowingResults: false as boolean,
      isShowingSolutions: false as boolean,
    };
  },

  methods: {
    fileIdToUrl,

    $gettext,

    startDragImage(image: Image, event: DragEvent) {
      console.log('Starting to drag image', image.altText);
      this.imageInteractedWith = image;

      if (event.dataTransfer) {
        const emptyDiv = document.createElement('div');

        emptyDiv.style.background = 'white';
        emptyDiv.style.width = '1px';
        emptyDiv.style.height = '1px';
        emptyDiv.style.opacity = '0.01';

        document.body.appendChild(emptyDiv);
        event.dataTransfer.setDragImage(emptyDiv, 0, 0);

        // Remove the div after the drag has started
        setTimeout(() => {
          document.body.removeChild(emptyDiv);
        }, 0);
      }
    },

    onDrag(image: Image) {
      console.log('Dragging image', image.altText);
    },

    endDragImage(image: Image) {
      console.log('Ending to drag image', image.altText);
      this.imageInteractedWith = undefined;
    },

    onDragOver(image: Image) {
      if (this.imageInteractedWith && this.imageInteractedWith != image) {
        const fromIndex = this.images.indexOf(this.imageInteractedWith);
        const toIndex = this.images.indexOf(image);

        console.log(
          'Dragging image',
          this.imageInteractedWith?.altText,
          '(',
          fromIndex,
          ') over target',
          image.altText,
          '(',
          toIndex,
          ')'
        );

        this.moveInArray(fromIndex, toIndex);
      }
    },

    onClick(image: Image) {
      if (this.isShowingSolutions || this.isShowingResults) return;

      if (this.imageInteractedWith) {
        const fromIndex = this.images.indexOf(this.imageInteractedWith);
        const toIndex = this.images.indexOf(image);
        this.swapInArray(fromIndex, toIndex);
        this.imageInteractedWith = undefined;
      } else {
        this.imageInteractedWith = image;
      }
    },

    moveInArray(fromIndex: number, toIndex: number) {
      let element = this.images[fromIndex];
      this.images.splice(fromIndex, 1);
      this.images.splice(toIndex, 0, element);
    },

    swapInArray(fromIndex: number, toIndex: number) {
      [this.images[fromIndex], this.images[toIndex]] = [
        this.images[toIndex],
        this.images[fromIndex],
      ];
    },

    isImageInCorrectPosition(image: Image): boolean {
      const index = this.images.findIndex((img) => img.uuid === image.uuid);
      return index !== -1 && this.task.images[index].uuid === image.uuid;
    },

    showResults() {
      this.isShowingResults = true;
    },

    showSolutions() {
      this.isShowingSolutions = true;
      this.resetImagesToCorrectOrder();
    },

    continueTask() {
      this.isShowingResults = false;
    },

    reset() {
      this.isShowingResults = false;
      this.isShowingSolutions = false;
      this.shuffleImages();
    },

    shuffleImages() {
      // Shuffle the images
      // https://stackoverflow.com/a/46545530
      this.images = this.images
        .map((image) => ({ image: image, sort: Math.random() }))
        .sort((image1, image2) => image1.sort - image2.sort)
        .map(({ image }) => image);
    },

    resetImagesToCorrectOrder() {
      this.images = this.task.images;
    },
  },

  computed: {
    correctAnswers(): number {
      let correctAnswers = 0;
      for (let i = 0; i < this.task.images.length; i++) {
        if (this.task.images[i].uuid === this.images[i].uuid) {
          correctAnswers++;
        }
      }
      return correctAnswers;
    },

    maxPoints(): number {
      return this.task.images.length;
    },

    allAnswersAreCorrect(): boolean {
      return this.correctAnswers === this.maxPoints;
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

  watch: {
    task: {
      handler() {
        console.log('watcher for this.task');
        this.images = [...this.task.images];
        this.shuffleImages();
      },
      immediate: true, // Ensure that the watcher is also called immediately when the component is first mounted
    },
  },
});
</script>

<style scoped>
.stud5p-sequencing {
  display: flex;
  flex-direction: row;
  padding-bottom: 1em;
  flex-wrap: wrap;
}

.image-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  border: 2px solid #dbe2e8;
  border-radius: 6px;
  margin: 6px;
  padding: 6px;
  transition: background-color 0.12s ease, border 0.12s ease,
    box-shadow 0.12s ease;
  cursor: grab;
  user-select: none;
  background: #fff;
}

.image-container:not(.disabled):hover {
  border: 2px solid #7ba4d3;
  box-shadow: 0 0 10px 0 #406ef3;
}

.dragged {
  border: 2px solid #7ba4d3;
  box-shadow: 0 0 10px 0 #406ef3;
  background-color: #dcf6ff;
}

.correct {
  background-color: #9dd8bb;
  border-color: #9dd8bb;
}

.incorrect {
  background-color: #f7d0d0;
  border-color: #f7d0d0;
}

.disabled {
  cursor: default;
}

.image {
  width: 10em;
  height: 10em;
}

.image-description {
  margin-top: 6px;
}
</style>
