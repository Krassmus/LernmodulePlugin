<template>
  <div class="imagePairingRow">
    <div class="draggableImagesColumn">
      <template
        v-for="draggableImageId in Object.keys(this.usedDraggableImages)"
        :key="draggableImageId"
      >
        <img
          :class="getClassForDraggableImage(draggableImageId)"
          draggable="true"
          @dragstart="startDragImage($event, draggableImageId)"
          :src="getImageById(draggableImageId).imageUrl"
          :alt="getImageById(draggableImageId).altText"
        />
      </template>
    </div>

    <div class="targetImagesColumn">
      <TargetImage
        v-for="imagePair in this.task.imagePairs"
        :draggable-image="getImageDraggedOntoTarget(imagePair.targetImage.uuid)"
        :target-image="getImageById(imagePair.targetImage.uuid)"
        :key="imagePair.uuid"
        @drop="onDropOnTargetImage($event, imagePair.targetImage.uuid)"
        draggable="true"
        @dragstart="startDragTargetImage($event, imagePair.targetImage.uuid)"
        @dragover.prevent
        @dragenter.prevent
        @click="onClickTargetImage(imagePair.targetImage.uuid)"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Image, ImagePair, ImagePairingTask } from '@/models/TaskDefinition';
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
      draggedImageId: undefined as Uuid | undefined,
      imagesById: {} as Record<Uuid, Image>,
      // Basically a Set -- True if the draggable image of a given ID has been used.
      // We used Record instead of Set because I wasn't sure if sets are
      // reactive in Vue 3.
      // See [Footnote1] for thoughts on a possible refactoring.
      usedDraggableImages: {} as Record<Uuid, boolean>,
      // Record from target ID -> draggable image ID
      imagesDraggedOntoTargets: {} as Record<Uuid, Uuid>,
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
      if (dragEvent.dataTransfer) {
        dragEvent.dataTransfer.dropEffect = 'move';
        dragEvent.dataTransfer.effectAllowed = 'move';

        if (!this.usedDraggableImages[imageId]) {
          console.log('Dragging image:', imageId);
          this.draggedImageId = imageId;
        }
      }
    },

    startDragTargetImage(dragEvent: DragEvent, targetImageId: Uuid): void {
      if (dragEvent.dataTransfer) {
        dragEvent.dataTransfer.dropEffect = 'move';
        dragEvent.dataTransfer.effectAllowed = 'move';
        // Remember that the image has been dragged away from a target
        // where it had been placed by the user
        dragEvent.dataTransfer.setData('targetId', targetImageId);
        // Check if an image has been dragged onto the target already
        const userDraggedImageId = this.imagesDraggedOntoTargets[targetImageId];
        if (userDraggedImageId) {
          this.draggedImageId = userDraggedImageId;
          console.log(
            'Dragging image:',
            this.draggedImageId,
            'from target image',
            targetImageId
          );
        }
      }
    },

    onDropOnTargetImage(event: DragEvent, targetImageId: Uuid): void {
      if (!this.draggedImageId) {
        return;
      }

      // Check if an answer has already been input.  If it has, we should do nothing.
      if (!this.imagesDraggedOntoTargets[targetImageId]) {
        // If the image has been dragged away from another target, then it should
        // be removed from that target when it is dropped onto this target.
        const otherTargetId = event.dataTransfer?.getData('targetId');
        if (otherTargetId) {
          delete this.imagesDraggedOntoTargets[otherTargetId];
        }
        // Save the dragged image onto the target where it has been dropped
        this.imagesDraggedOntoTargets[targetImageId] = this.draggedImageId;
        this.usedDraggableImages[this.draggedImageId] = true;
      }

      console.log(
        'Dropped image:',
        this.draggedImageId,
        'on target:',
        targetImageId
      );
      // Mark that the drag interaction is over.
      this.draggedImageId = undefined;
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
        this.usedDraggableImages[usedImageId] = false;
      }
    },
    getClassForDraggableImage(draggableImageId: Uuid) {
      if (this.usedDraggableImages[draggableImageId]) {
        return 'draggableImageHidden';
      } else {
        return 'draggableImage';
      }
    },
  },
  computed: {},
  watch: {
    task: {
      handler() {
        console.log('watcher for this.task');
        // Copy every image into the map imagesById so we can look up images by ID in O(1)
        this.imagesById = {};

        for (const imagePair of this.task.imagePairs) {
          this.imagesById[imagePair.draggableImage.uuid] =
            imagePair.draggableImage;
          this.imagesById[imagePair.targetImage.uuid] = imagePair.targetImage;
          this.usedDraggableImages[imagePair.draggableImage.uuid] = false;
        }
      },
      immediate: true, // Ensure that the watcher is also called immediately when the component is first mounted
    },
  },
});

/*
 [Footnote1] The data structure `usedDraggableImages` isn't really needed, as the set
 of 'used draggable images' corresponds to the set of elements of
 `Object.values(imagesDraggedOntoTargets)`. All expressions of the form
 `usedDraggableImages[draggableImageId]` could be replaced with the expression
 `Object.values(imagesDraggedOntoTargets).contains(draggableImageId)`. The Big-O
 complexity of the computation needed after each user interaction would then be
 worse (I think N^2), but as N (the number of image pairs) will likely
 not exceed 100, the performance for our application would still be acceptable.
 I did not perform this refactoring because I think the code is fine as-is
 -- it works, and we understand it, so why mess around with it? - Ann
*/
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
}

.targetImagesColumn {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  align-items: center;
}

.draggableImage {
  display: flex;
  justify-content: center;
  width: 10em;
  padding: 6px;
  margin: 6px;
  height: 10em;
  border-radius: 6px 6px 6px 6px;
  cursor: pointer;
  border: 2px solid #dbe2e8;
  box-shadow: 2px 2px 0 2px rgba(203, 213, 222, 0.2);
}

.draggableImageHidden {
  display: flex;
  justify-content: center;
  width: 10em;
  padding: 6px;
  margin: 6px;
  height: 10em;
  border-radius: 6px 6px 6px 6px;
  cursor: pointer;
  border: 2px solid #dbe2e8;
  box-shadow: 2px 2px 0 2px rgba(203, 213, 222, 0.2);
  opacity: 20%;
}

.draggableImage:hover {
  border: 2px solid rgba(0, 187, 109, 0.93);
}
</style>
