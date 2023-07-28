<template>
  <div class="imagePairGrid">
    <div class="imagePairColumn">
      <div
        v-for="imagePair in this.task.imagePairs"
        :key="imagePair.uuid"
        class="imagePairItem"
        draggable="true"
        @dragstart="startDragImage($event, imagePair.draggableImage)"
        @drop="onDropImage($event, imagePair.draggableImage)"
        @dragover.prevent
        @dragenter.prevent
      >
        <img
          :src="imagePair.draggableImage.imageUrl"
          :alt="imagePair.draggableImage.altText"
          class="pairImage"
        />
      </div>
    </div>

    <div class="imagePairColumn">
      <div
        v-for="imagePair in this.task.imagePairs"
        :key="imagePair.uuid"
        class="imagePairItem"
        draggable="true"
        @dragstart="startDragImage($event, imagePair.targetImage)"
        @drop="onDropImage($event, imagePair.targetImage)"
        @dragover.prevent
        @dragenter.prevent
      >
        <img
          :src="imagePair.targetImage.imageUrl"
          :alt="imagePair.targetImage.altText"
          class="pairImage"
        />
      </div>
    </div>
  </div>
  <pre>{{ draggedImageId }}</pre>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ImagePairingTask } from '@/models/TaskDefinition';

interface Image {
  uuid: Uuid;
  imageUrl: string;
  altText: string;
}

type Uuid = string;

export default defineComponent({
  name: 'ImagePairingViewer',
  props: {
    task: {
      type: Object as PropType<ImagePairingTask>,
      required: true,
    },
  },
  data() {
    return {
      draggedImageId: undefined as Uuid | undefined,
    };
  },
  methods: {
    startDragImage(dragEvent: DragEvent, image: Image): void {
      if (dragEvent.dataTransfer) {
        dragEvent.dataTransfer.dropEffect = 'move';
        dragEvent.dataTransfer.effectAllowed = 'move';

        console.log('Dragging image: ', image);
        this.draggedImageId = image.uuid;
      }
    },

    onDropImage(dragEvent: DragEvent, image: Image): void {
      if (!this.draggedImageId) {
        throw new Error('Dragged image id is undefined');
      }

      console.log(
        'Dropped image :',
        this.draggedImageId,
        'on image: ',
        image.altText
      );

      this.draggedImageId = undefined;
    },
  },
  computed: {},
});
</script>

<style scoped>
.pairImage {
  max-width: 100%;
  max-height: 100%;
}

.imagePairGrid {
  display: flex;
  flex-direction: row;
}

.imagePairColumn {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.imagePairItem {
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

.imagePairItem:hover {
  border: 2px solid rgba(0, 187, 109, 0.93);
}
</style>
