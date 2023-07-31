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
        v-for="imagePair in this.userLinkedImagePairs"
        :draggable-image="
          imagePair.draggableImageId
            ? getImageById(imagePair.draggableImageId)
            : undefined
        "
        :target-image="getImageById(imagePair.targetImageId)"
        :key="imagePair.uuid"
        @drop="onDropImage($event, imagePair)"
        draggable="true"
        @dragstart="startDragImagePair($event, imagePair)"
        @dragover.prevent
        @dragenter.prevent
        @click="onClickImagePair(imagePair)"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Image, ImagePairingTask } from '@/models/TaskDefinition';
import TargetImage from '@/components/TargetImage.vue';

type Uuid = string;

export interface UserLinkedImagePair {
  uuid: Uuid;
  draggableImageId: Uuid | undefined;
  correctDraggableImageId: Uuid;
  targetImageId: Uuid;
}

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
      usedDraggableImages: {} as Record<Uuid, boolean>,
      userLinkedImagePairs: [] as UserLinkedImagePair[],
    };
  },
  methods: {
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

    startDragImagePair(
      dragEvent: DragEvent,
      imagePair: UserLinkedImagePair
    ): void {
      if (dragEvent.dataTransfer) {
        dragEvent.dataTransfer.dropEffect = 'move';
        dragEvent.dataTransfer.effectAllowed = 'move';

        if (imagePair.draggableImageId !== undefined) {
          this.draggedImageId = imagePair.draggableImageId;

          console.log(
            'Dragging image:',
            this.draggedImageId,
            'from image pair',
            imagePair.uuid
          );
        }
      }
    },

    onDropImage(event: DragEvent, targetImagePair: UserLinkedImagePair): void {
      if (!this.draggedImageId) {
        return;
      }

      if (targetImagePair.draggableImageId === undefined) {
        const originImagePair = this.userLinkedImagePairs.find(
          (imagePair) => imagePair.draggableImageId === this.draggedImageId
        );

        if (originImagePair !== undefined) {
          originImagePair.draggableImageId = undefined;
        }

        targetImagePair.draggableImageId = this.draggedImageId;

        this.usedDraggableImages[this.draggedImageId] = true;
      }

      console.log(
        'Dropped image:',
        this.draggedImageId,
        'on image pair:',
        targetImagePair.uuid
      );

      this.draggedImageId = undefined;
    },
    getImageById(imageId: string): Image {
      const image = this.imagesById[imageId];
      if (!image) {
        throw new Error('No image found with the given ID: ' + imageId);
      }
      return image;
    },
    onClickImagePair(imagePair: UserLinkedImagePair): void {
      console.log('Clicked on image pair', imagePair.uuid);
      if (imagePair.draggableImageId !== undefined) {
        this.usedDraggableImages[imagePair.draggableImageId] = false;
        imagePair.draggableImageId = undefined;
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
        this.userLinkedImagePairs = [];

        for (const imagePair of this.task.imagePairs) {
          this.imagesById[imagePair.draggableImage.uuid] =
            imagePair.draggableImage;
          this.imagesById[imagePair.targetImage.uuid] = imagePair.targetImage;

          this.usedDraggableImages[imagePair.draggableImage.uuid] = false;

          // Record from target ID -> draggable image ID
          // const imagesDraggedOntoTargets = {} as Record<Uuid, Uuid>

          this.userLinkedImagePairs.push({
            uuid: imagePair.uuid,
            draggableImageId: undefined,
            correctDraggableImageId: imagePair.draggableImage.uuid,
            targetImageId: imagePair.targetImage.uuid,
          });
        }
      },
      immediate: true, // Ensure that the watcher is also called immediately when the component is first mounted
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
