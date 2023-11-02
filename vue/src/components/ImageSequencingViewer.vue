<template>
  <div class="imageRow">
    <div
      v-for="image in images"
      :key="image.uuid"
      class="imageContainer"
      @dragover.prevent
      @dragenter.prevent
    >
      <img
        class="draggableImage"
        :src="image.imageUrl"
        :alt="image.altText"
        draggable="true"
        @dragstart="startDragImage($event, image)"
        @drop="onDropImage($event, image)"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Image, ImageSequencingTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import { taskEditorStore } from '@/store';

export default defineComponent({
  name: 'ImageSequencingViewer',
  components: {},
  props: {
    task: {
      type: Object as PropType<ImageSequencingTask>,
      required: true,
    },
  },
  data() {
    return {
      imageInteractedWith: undefined as Image | undefined,
    };
  },
  beforeMount(): void {},
  methods: {
    $gettext,
    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },
    startDragImage(dragEvent: DragEvent, image: Image) {
      this.imageInteractedWith = image;
      console.log('Dragging image', image.altText);
    },

    onDropImage(event: DragEvent, image: Image): void {
      console.log(
        'Dropped image',
        this.imageInteractedWith?.altText,
        'on target:',
        image.altText
      );
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as ImageSequencingTask,
    images(): Image[] {
      // Shuffle the images
      // https://stackoverflow.com/a/46545530
      return this.task.images
        .map((image) => ({ image: image, sort: Math.random() }))
        .sort((image1, image2) => image1.sort - image2.sort)
        .map(({ image }) => image);
    },
  },
});
</script>

<style scoped>
.main-flex {
  display: flex;
  justify-content: space-between;
  width: 100%;
  gap: 1em;
}

.imageRow {
  display: flex;
  flex-direction: row;
}

.imageContainer {
  border: 2px solid #dbe2e8;
  border-radius: 6px;
  margin: 6px;
  padding: 6px;
}

.draggableImage {
  width: 10em;
  height: 10em;
}
</style>
