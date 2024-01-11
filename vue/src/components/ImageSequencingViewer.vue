<template>
  <div class="imageRow">
    <div
      v-for="image in this.images"
      :key="image.uuid"
      class="imageContainer"
      draggable="true"
      @dragstart="startDragImage($event, image)"
      @dragover="onDragOver($event, image)"
      @drop="onDropImage($event, image)"
    >
      <img
        class="image"
        draggable="false"
        @dragover.prevent
        @dragenter.prevent
        :src="image.imageUrl"
        :alt="image.altText"
      />
      <span class="imageDescription" @dragover.prevent @dragenter.prevent>{{
        image.altText
      }}</span>
    </div>
  </div>
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
      console.log('Dropped image', this.imageInteractedWith?.altText);
      this.imageInteractedWith = undefined;
    },

    onDragOver(event: DragEvent, image: Image): void {
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
  font-size: 18px;
}
</style>
