<template>
  <div class="imagePairingRow">
    <div
      class="draggableImagesColumn"
      draggable="false"
      @dragover.prevent
      @dragenter.prevent
      @drop="onDropOnInteractiveImages($event)"
    >
      <div
        v-for="draggableImageId in draggableImages"
        :key="draggableImageId"
        :draggable="
          !this.isDraggableImageUsed(draggableImageId) && !this.showResults
        "
        @dragstart="startDragImage($event, draggableImageId)"
        @dragend="endDragImage($event, draggableImageId)"
        @click="onClickDraggableImage(draggableImageId)"
        class="draggableImageContainer"
        :class="{
          disabled:
            this.isDraggableImageUsed(draggableImageId) ||
            this.showResults ||
            this.draggedImageId === draggableImageId,
          selected: this.imageIdInteractedWith === draggableImageId,
        }"
      >
        <img
          class="draggableImage"
          :src="fileIdToUrl(getImageById(draggableImageId).file_id)"
          :alt="getImageById(draggableImageId).altText"
          draggable="false"
          ref="draggableImages"
        />
      </div>
    </div>
    <div class="targetImagesColumn">
      <TargetImage
        v-for="imagePair in this.task.imagePairs"
        class="targetImage"
        :class="{
          outlined:
            !this.imagesDraggedOntoTargets.hasOwnProperty(
              imagePair.targetImage.uuid
            ) &&
            (this.draggedImageId || this.imageIdInteractedWith),
        }"
        :draggable-image="getImageDraggedOntoTarget(imagePair.targetImage.uuid)"
        :target-image="getImageById(imagePair.targetImage.uuid)"
        :isCorrect="this.isAnswerCorrect(imagePair.targetImage.uuid)"
        :showResult="this.showResults"
        :key="imagePair.uuid"
        @drop="onDropOnTargetImage($event, imagePair.targetImage.uuid)"
        :draggable="
          getImageDraggedOntoTarget(imagePair.targetImage.uuid) &&
          !this.showResults
        "
        @dragstart="startDragTargetImage($event, imagePair.targetImage.uuid)"
        @dragend="endDragTargetImage($event, imagePair.targetImage.uuid)"
        @dragover.prevent
        @dragenter.prevent
        @click="onClickTargetImage(imagePair.targetImage.uuid)"
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
import { fileIdToUrl, PairElement, PairingTask } from '@/models/TaskDefinition';
import TargetImage from '@/components/TargetImage.vue';
import FeedbackElement from '@/components/FeedbackElement.vue';

type Uuid = string;

export default defineComponent({
  name: 'PairingViewer',
  components: {
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
      imageIdInteractedWith: undefined as Uuid | undefined,
      draggedImageId: undefined as Uuid | undefined,
      // We used Record instead of Set because I wasn't sure if sets are
      // reactive in Vue 3.
      // Record from target ID -> draggable image ID.
      imagesDraggedOntoTargets: {} as Record<Uuid, Uuid>,
      showResults: false as boolean,
    };
  },
  methods: {
    fileIdToUrl,
    isAnswerCorrect(targetId: Uuid): boolean {
      const userInput = this.imagesDraggedOntoTargets[targetId];
      if (!userInput) {
        return false;
      }
      // A little bit awkward -- we have to find the pair to which the target
      // belongs so that we can check whether the user's solution is right
      // This method itself is therefore O(n), giving an O(n^2) if you check
      // every user answer in a loop every time the user makes an input.
      // But it should be fine, because even if there are 50 images in a set
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

    getImageDraggedOntoTarget(targetId: Uuid): PairElement | undefined {
      const draggedImageId = this.imagesDraggedOntoTargets[targetId];
      if (draggedImageId) {
        return this.getImageById(draggedImageId);
      } else {
        return undefined;
      }
    },

    startDragImage(dragEvent: DragEvent, imageId: Uuid): void {
      if (!this.isDraggableImageUsed(imageId)) {
        console.log('Dragging image:', imageId);
        this.imageIdInteractedWith = imageId;
        this.draggedImageId = imageId;

        dragEvent.dataTransfer!.dropEffect = 'move';
        dragEvent.dataTransfer!.effectAllowed = 'move';

        // Change the drag image to the parent element to include the border
        const refToImage = (
          this.$refs.draggableImages as HTMLImageElement[]
        ).find(
          (value) =>
            value.src == fileIdToUrl(this.getImageById(imageId).file_id)
        );

        const parentElementOfImage = refToImage?.parentElement!;

        if (refToImage && parentElementOfImage) {
          const cloneOfParent = parentElementOfImage.cloneNode(
            true
          ) as HTMLElement;

          cloneOfParent.style.top = '0';
          cloneOfParent.style.left = '0';
          cloneOfParent.style.position = 'absolute';
          cloneOfParent.style.zIndex = '-999';

          parentElementOfImage.parentElement!.append(cloneOfParent);

          dragEvent.dataTransfer!.setDragImage(
            cloneOfParent,
            parentElementOfImage.clientWidth / 2,
            parentElementOfImage.clientHeight / 2
          );

          setTimeout(() => cloneOfParent.remove());
        }
      }
    },

    endDragImage(dragEvent: DragEvent, imageId: string): void {
      this.imageIdInteractedWith = undefined;
      this.draggedImageId = undefined;
    },

    startDragTargetImage(dragEvent: DragEvent, targetImageId: Uuid): void {
      dragEvent.dataTransfer!.dropEffect = 'move';
      dragEvent.dataTransfer!.effectAllowed = 'move';
      // Remember that the image has been dragged away from a target
      // where it had been placed by the user
      dragEvent.dataTransfer!.setData('targetId', targetImageId);

      // Check if an image has been dragged onto the target already
      const userDraggedImageId = this.imagesDraggedOntoTargets[targetImageId];
      if (userDraggedImageId) {
        this.imageIdInteractedWith = userDraggedImageId;

        // Add the interactive image to the cursor even if the user
        // clicked on the target image
        const refToImage = (
          this.$refs.draggableImages as HTMLImageElement[]
        ).find(
          (value) =>
            value.src ==
            fileIdToUrl(this.getImageById(userDraggedImageId).file_id)
        );

        const parentElementOfImage = refToImage?.parentElement!;

        if (parentElementOfImage) {
          dragEvent.dataTransfer!.setDragImage(
            parentElementOfImage,
            parentElementOfImage.clientWidth / 2,
            parentElementOfImage.clientHeight / 2
          );
        }

        console.log(
          'Dragging image:',
          this.imageIdInteractedWith,
          'from target image',
          targetImageId
        );
      }
    },

    endDragTargetImage(event: DragEvent, targetImageId: Uuid) {
      this.imageIdInteractedWith = undefined;
      this.draggedImageId = undefined;
    },

    onDropOnTargetImage(event: DragEvent, targetImageId: Uuid): void {
      if (!this.imageIdInteractedWith) {
        return;
      }

      // If the image has been dragged away from another target, then it should
      // be removed from that target when it is dropped onto this target.
      const otherTargetId = event.dataTransfer?.getData('targetId');
      if (otherTargetId) {
        delete this.imagesDraggedOntoTargets[otherTargetId];
      }
      // Save the dragged image onto the target where it has been dropped
      this.imagesDraggedOntoTargets[targetImageId] = this.imageIdInteractedWith;

      console.log(
        'Dropped image:',
        this.imageIdInteractedWith,
        'on target:',
        targetImageId
      );
      // Mark that the drag interaction is over.
      this.imageIdInteractedWith = undefined;
      this.draggedImageId = undefined;
    },

    getImageById(imageId: string): PairElement {
      const image = this.imagesById[imageId];
      if (!image) {
        throw new Error('No image found with the given ID: ' + imageId);
      }
      return image;
    },

    onClickTargetImage(targetImageId: Uuid): void {
      console.log('Clicked on target image', targetImageId);

      // Do nothing if we are displaying the results already
      if (this.showResults) return;

      // Remove the image, if any, that has been dragged by the user onto
      // the target they have clicked.
      const usedImageId = this.imagesDraggedOntoTargets[targetImageId];
      if (usedImageId) {
        delete this.imagesDraggedOntoTargets[targetImageId];
      }
      // Check if the user clicked a draggable image and wants to put it here
      if (this.imageIdInteractedWith) {
        this.imagesDraggedOntoTargets[targetImageId] =
          this.imageIdInteractedWith;
        this.imageIdInteractedWith = undefined;
      }
    },

    // This has a complexity of O(n).  When it is called in our v-for to set
    // the class of every draggable image element, it makes it O(n^2).
    // But that should be OK, because no one will put more than (say) 100
    // images in a single task, right?
    isDraggableImageUsed(draggableImageId: Uuid): boolean {
      return Object.values(this.imagesDraggedOntoTargets).includes(
        draggableImageId
      );
    },

    onClickDraggableImage(imageId: Uuid): void {
      if (!this.isDraggableImageUsed(imageId) && !this.showResults) {
        console.log('Clicked on image:', imageId);
        this.imageIdInteractedWith = imageId;
      }
    },

    onDropOnInteractiveImages(event: DragEvent): void {
      if (!this.imageIdInteractedWith) {
        return;
      }

      // If the image has been dragged away from a target, then it should
      // be removed from that target when it is dropped here.
      const otherTargetId = event.dataTransfer?.getData('targetId');
      if (otherTargetId) {
        delete this.imagesDraggedOntoTargets[otherTargetId];
      }

      console.log('Returned image:', this.imageIdInteractedWith);

      // Mark that the interaction is over.
      this.imageIdInteractedWith = undefined;
    },

    checkResults(): void {
      this.showResults = true;
    },

    reset(): void {
      this.showResults = false;
      this.imagesDraggedOntoTargets = {};
    },
  },
  computed: {
    draggableImages(): Uuid[] {
      // Shuffle the images
      // https://stackoverflow.com/a/46545530
      return this.task.pairs
        .map((pair) => ({ pair: pair, sort: Math.random() }))
        .sort((pair1, pair2) => pair1.sort - pair2.sort)
        .map(({ pair }) => pair.draggableElement.uuid);
    },

    imagesById(): Record<Uuid, PairElement> {
      const imagesById: Record<Uuid, PairElement> = {};
      for (const pair of this.task.pairs) {
        imagesById[pair.draggableElement.uuid] = pair.draggableElement;
        imagesById[pair.targetElement.uuid] = pair.targetElement;
      }
      return imagesById;
    },

    correctAnswers(): number {
      let correctAnswers = 0;
      for (const imagePair of this.task.pairs) {
        if (this.isAnswerCorrect(imagePair.targetElement.uuid))
          correctAnswers++;
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

<style scoped>
.imagePairingRow {
  display: flex;
  flex-direction: row;
}

.draggableImagesColumn {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  align-items: center;
  user-select: none;
  border: 1px solid #cbd5de;
}

.targetImagesColumn {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  align-items: center;
  background-color: #eef1f4;
  user-select: none;
  border: 1px solid #cbd5de;
}

.draggableImage {
  object-fit: contain;
  object-position: center;
  display: flex;
  justify-content: center;
  width: 8em;
  height: 8em;
  box-shadow: inset 0 2px 74px 0 #cbd5de;
}

.disabled {
  cursor: default;
  opacity: 25%;
}

.outlined {
  cursor: pointer;
  border: 2px dashed #dbe2e8;
}

.draggableImageContainer {
  border: 2px solid #dbe2e8;
  border-radius: 6px;
  margin: 6px;
  padding: 6px;
}

.draggableImageContainer.selected {
  border: 2px solid #7ba4d3;
}

.draggableImageContainer:not(.disabled):not(.selected):hover {
  cursor: grab;
  border: 2px solid #7ba4d3;
  box-shadow: 0 0 10px 0 #406ef3;
}
</style>