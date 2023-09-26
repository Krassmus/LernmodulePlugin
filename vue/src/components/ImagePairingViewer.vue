<template>
  <div class="imagePairingRow">
    <div
      class="draggableImagesColumn"
      @dragover.prevent
      @dragenter.prevent
      @drop="onDropOnInteractiveImages($event)"
    >
      <div
        v-for="draggableImageId in draggableImages"
        :key="draggableImageId"
        class="draggableImageContainer"
        :class="{
          disabled: this.isDraggableImageUsed(draggableImageId),
          selected: this.imageIdInteractedWith === draggableImageId,
        }"
      >
        <img
          class="draggableImage"
          :draggable="!this.isDraggableImageUsed(draggableImageId)"
          @dragstart="startDragImage($event, draggableImageId)"
          @click="onClickDraggableImage(draggableImageId)"
          :src="getImageById(draggableImageId).imageUrl"
          :alt="getImageById(draggableImageId).altText"
          ref="draggableImages"
        />
      </div>
    </div>
    <div class="targetImagesColumn">
      <TargetImage
        v-for="imagePair in this.task.imagePairs"
        class="targetImage"
        :draggable-image="getImageDraggedOntoTarget(imagePair.targetImage.uuid)"
        :target-image="getImageById(imagePair.targetImage.uuid)"
        :isCorrect="this.isAnswerCorrect(imagePair.targetImage.uuid)"
        :showResult="this.showResults"
        :key="imagePair.uuid"
        @drop="onDropOnTargetImage($event, imagePair.targetImage.uuid)"
        :draggable="getImageDraggedOntoTarget(imagePair.targetImage.uuid)"
        @dragstart="startDragTargetImage($event, imagePair.targetImage.uuid)"
        @dragover.prevent
        @dragenter.prevent
        @click="onClickTargetImage(imagePair.targetImage.uuid)"
      />
    </div>
  </div>
  <br />
  <button
    v-if="!this.showResults"
    type="button"
    class="h5pButton"
    @click="checkResults()"
  >
    {{ this.task.strings.checkButton }}
  </button>
  <button
    v-if="this.showResults"
    type="button"
    class="h5pButton"
    @click="reset()"
  >
    {{ this.task.strings.retryButton }}
  </button>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Image, ImagePairingTask } from '@/models/TaskDefinition';
import TargetImage from '@/components/TargetImage.vue';

type Uuid = string;

export default defineComponent({
  name: 'ImagePairingViewer',
  components: {
    TargetImage,
  },
  props: {
    task: {
      type: Object as PropType<ImagePairingTask>,
      required: true,
    },
  },
  data() {
    return {
      imageIdInteractedWith: undefined as Uuid | undefined,
      // We used Record instead of Set because I wasn't sure if sets are
      // reactive in Vue 3.
      // Record from target ID -> draggable image ID
      imagesDraggedOntoTargets: {} as Record<Uuid, Uuid>,
      showResults: false as boolean,
    };
  },
  methods: {
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
      const pair = this.task.imagePairs.find(
        (pair) => pair.targetImage.uuid === targetId
      );
      if (!pair) {
        throw new Error(
          'could not find the pair to which the target id ' +
            targetId +
            ' belongs'
        );
      }
      return pair.draggableImage.uuid === userInput;
    },

    getImageDraggedOntoTarget(targetId: Uuid): Image | undefined {
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

        dragEvent.dataTransfer!.dropEffect = 'move';
        dragEvent.dataTransfer!.effectAllowed = 'move';

        // Change the drag image to the parent element to include the border
        const refToImage = (
          this.$refs.draggableImages as HTMLImageElement[]
        ).find((value) => value.src == this.getImageById(imageId).imageUrl);

        const parentElementOfImage = refToImage?.parentElement!;

        if (parentElementOfImage) {
          dragEvent.dataTransfer!.setDragImage(
            parentElementOfImage,
            parentElementOfImage.clientWidth / 2,
            parentElementOfImage.clientHeight / 2
          );
        }
      }
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
          (value) => value.src == this.getImageById(userDraggedImageId).imageUrl
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
    },

    getImageById(imageId: string): Image {
      const image = this.imagesById[imageId];
      if (!image) {
        throw new Error('No image found with the given ID: ' + imageId);
      }
      return image;
    },

    onClickTargetImage(targetImageId: Uuid): void {
      console.log('Clicked on target image', targetImageId);
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
      if (!this.isDraggableImageUsed(imageId)) {
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
      return this.task.imagePairs
        .map((imagePair) => ({ imagePair: imagePair, sort: Math.random() }))
        .sort((imagePair1, imagePair2) => imagePair1.sort - imagePair2.sort)
        .map(({ imagePair }) => imagePair.draggableImage.uuid);
    },

    imagesById(): Record<Uuid, Image> {
      const imagesById: Record<Uuid, Image> = {};
      for (const imagePair of this.task.imagePairs) {
        imagesById[imagePair.draggableImage.uuid] = imagePair.draggableImage;
        imagesById[imagePair.targetImage.uuid] = imagePair.targetImage;
      }
      return imagesById;
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

.draggableImageContainer {
  border: 2px solid #dbe2e8;
  border-radius: 6px;
  margin: 6px;
  padding: 6px;
}

.draggableImageContainer.selected {
  border: 2px solid #1a73d9;
}

.draggableImageContainer:not(.disabled):not(.selected):hover {
  border: 2px solid #1a73d9;
}
</style>
