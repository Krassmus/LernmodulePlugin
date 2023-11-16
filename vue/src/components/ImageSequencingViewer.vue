<template>
  <div class="imageRow">
    <div
      v-for="image in this.images"
      :key="image.uuid"
      class="imageContainer"
      draggable="true"
      @dragstart="startDragImage($event, image)"
      @drop="onDropImage($event, image)"
    >
      <img
        class="image"
        draggable="false"
        :src="image.imageUrl"
        :alt="image.altText"
      />
      <span class="imageDescription">{{ image.altText }}</span>
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
      images: [] as Image[],
      imageInteractedWith: undefined as Image | undefined,
    };
  },
  beforeMount(): void {
    console.log('Before Mount');
  },
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
  },
  watch: {
    task: {
      handler() {
        console.log('watcher for this.task');
        this.images = this.task.images;
      },
      immediate: true, // Ensure that the watcher is also called immediately when the component is first mounted
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
  display: flex;
  flex-direction: column;
  align-items: center;
  border: 2px solid #dbe2e8;
  border-radius: 6px;
  margin: 6px;
  padding: 6px;
  cursor: grab;
}

.image {
  width: 10em;
  height: 10em;
}

.imageDescription {
  margin-top: 6px;
}
</style>
