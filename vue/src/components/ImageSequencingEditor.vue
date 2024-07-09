<template>
  <div class="main-flex">
    <div class="cards-list">
      <div
        v-for="(image, index) in taskDefinition.images"
        :key="image.uuid"
        :class="{
          'cards-list-item': true,
          'selected-card': index === this.selectedImageIndex,
        }"
        @click="selectImage(index)"
      >
        {{ index + '. ' + image.altText }}
        <!-- Apply .stop modifier to @click so that the click event handler on the
            parent element doesn't get called when the delete button is clicked -->
        <img
          class="flex-child-element removeAnswerButton"
          :src="urlForIcon('trash')"
          @click.stop="deleteImage(index)"
          alt="an icon showing a trash bin"
        />
      </div>
      <button type="button" @click="addImage">
        {{ $gettext('Bild hinzufügen') }}
      </button>
    </div>

    <ImageSequencingEditorImage
      v-if="this.taskDefinition.images[this.selectedImageIndex]"
      class="edited-memory-card"
      :image="this.taskDefinition.images[this.selectedImageIndex]"
      :image-index="this.selectedImageIndex"
    />
    <div v-else class="edited-memory-card no-card-selected-placeholder">
      {{ $gettext('Keine Karte ist zum Bearbeiten ausgewählt.') }}
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ImageSequencingTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import { taskEditorStore } from '@/store';
import produce from 'immer';
import { v4 } from 'uuid';
import ImageSequencingEditorImage from '@/components/ImageSequencingEditorImage.vue';

export default defineComponent({
  name: 'ImageSequencingEditor',
  components: { ImageSequencingEditorImage },
  props: {
    task: {
      type: Object as PropType<ImageSequencingTask>,
      required: true,
    },
  },
  data() {
    return { selectedImageIndex: -1 };
  },
  beforeMount(): void {
    if (this.taskDefinition.images.length > 0) {
      this.selectedImageIndex = 0;
    }
  },
  methods: {
    $gettext,
    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },
    selectImage(index: number) {
      this.selectedImageIndex = index;
    },
    addImage() {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.images.push({
          v: 2,
          uuid: v4(),
          file_id: '',
          altText: '',
        });
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
      // Select the newly inserted image
      this.selectedImageIndex = this.taskDefinition.images.length - 1;
    },
    deleteImage(index: number) {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.images.splice(index, 1);
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
      // Adjust the selection index so the selected card doesn't unexpectedly change
      if (index <= this.selectedImageIndex) {
        this.selectedImageIndex = this.selectedImageIndex - 1;
      }
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as ImageSequencingTask,
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
</style>
