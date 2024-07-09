<template>
  <div class="imageRow">
    <div
      v-for="image in this.images"
      :key="image.uuid"
      class="imageContainer"
      :class="{
        grabbable: !this.showResults,
      }"
      :draggable="!this.showResults"
      @dragstart="startDragImage(image)"
      @dragover="onDragOver(image)"
      @drop="onDropImage()"
    >
      <img
        class="image"
        draggable="false"
        @dragover.prevent
        @dragenter.prevent
        :src="fileIdToUrl(image.file_id)"
        :alt="image.altText"
      />
      <span class="imageDescription" @dragover.prevent @dragenter.prevent>{{
        image.altText
      }}</span>
    </div>
  </div>

  <FeedbackElement
    v-if="showResults"
    :achievedPoints="correctAnswers"
    :maxPoints="maxPoints"
    :resultMessage="resultMessage"
    :feedback="task.feedback"
  />

  <div class="h5pButtonPanel">
    <button
      v-if="!this.showResults"
      v-text="this.task.strings.checkButton"
      @click="checkResults()"
      type="button"
      class="h5pButton"
    />

    <button
      v-if="this.showResults"
      v-text="this.task.strings.retryButton"
      @click="reset()"
      type="button"
      class="h5pButton"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  fileIdToUrl,
  Image,
  ImageSequencingTask,
} from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import { taskEditorStore } from '@/store';
import FeedbackElement from '@/components/FeedbackElement.vue';

export default defineComponent({
  name: 'ImageSequencingViewer',
  components: {
    FeedbackElement,
  },
  props: {
    task: {
      type: Object as PropType<ImageSequencingTask>,
      required: true,
    },
  },
  emits: ['updateAttempt'],
  data() {
    return {
      images: [] as Image[],
      imageInteractedWith: undefined as Image | undefined,
      showResults: false as boolean,
    };
  },
  beforeMount(): void {
    console.log('Before Mount');
  },
  methods: {
    fileIdToUrl,
    $gettext,

    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },

    startDragImage(image: Image) {
      console.log('Dragging image', image.altText);
      this.imageInteractedWith = image;
    },

    onDropImage(): void {
      console.log('Dropped image', this.imageInteractedWith?.altText);
      this.imageInteractedWith = undefined;
    },

    onDragOver(image: Image): void {
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

        this.moveInArray(this.images, fromIndex, toIndex);
      }
    },

    moveInArray(array: Image[], fromIndex: number, toIndex: number) {
      let element = array[fromIndex];
      array.splice(fromIndex, 1);
      array.splice(toIndex, 0, element);
    },

    checkResults(): void {
      this.showResults = true;
    },

    reset(): void {
      this.showResults = false;
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
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as ImageSequencingTask,
    correctAnswers(): Number {
      let correctAnswers = 0;
      for (let i = 0; i < this.task.images.length; i++) {
        if (this.task.images[i].uuid === this.images[i].uuid) {
          correctAnswers++;
        }
      }
      return correctAnswers;
    },
    maxPoints(): Number {
      return this.task.images.length;
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
.imageRow {
  display: flex;
  flex-direction: row;
  padding-bottom: 1em;
  flex-wrap: wrap;
}

.imageContainer {
  display: flex;
  flex-direction: column;
  align-items: center;
  border: 2px solid #dbe2e8;
  border-radius: 6px;
  margin: 6px;
  padding: 6px;
}

.grabbable {
  cursor: grab;
}

.image {
  width: 10em;
  height: 10em;
}

.imageDescription {
  margin-top: 6px;
  font-size: 18px;
}
</style>
