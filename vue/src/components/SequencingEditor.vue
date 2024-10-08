<template>
  <form class="default">
    <fieldset class="sequencing-editor">
      <legend>{{ $gettext('Sequencing') }}</legend>

      <div class="cards-list">
        <div
          v-for="(card, index) in taskDefinition.images"
          :key="card.uuid"
          :class="{
            'cards-list-item': true,
            'selected-card': index === this.selectedImageIndex,
          }"
          @click="selectImage(index)"
        >
          <div v-text="listEntryText(index, card)" class="list-entry-text" />

          <!-- Apply .stop modifier so that the selectCard event handler on the
            parent element doesn't get called when the delete button is clicked -->
          <button
            type="button"
            class="flex-child-element remove-card-button"
            @click.stop="deleteImage(index)"
            :aria-label="$gettext('Bild löschen')"
          >
            <img :src="urlForIcon('trash')" alt="" />
          </button>
        </div>

        <button
          type="button"
          class="button add add-card-button"
          @click="addImage"
        >
          {{ $gettext('Bild hinzufügen') }}
        </button>
      </div>

      <SequencingEditorImage
        v-if="this.taskDefinition.images[this.selectedImageIndex]"
        class="edited-memory-card"
        :image="this.taskDefinition.images[this.selectedImageIndex]"
        :image-index="this.selectedImageIndex"
      />
      <div v-else class="edited-memory-card no-card-selected-placeholder">
        {{ $gettext('Keine Karte ist zum Bearbeiten ausgewählt.') }}
      </div>
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Image, SequencingTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import { taskEditorStore } from '@/store';
import produce from 'immer';
import { v4 } from 'uuid';
import SequencingEditorImage from '@/components/SequencingEditorImage.vue';

export default defineComponent({
  name: 'SequencingEditor',

  components: { SequencingEditorImage },

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

    listEntryText(index: number, image: Image) {
      let text = index + 1 + '. ';

      if (image.altText != '') {
        text += image.altText;
      } else {
        text += $gettext('Bild');
      }
      return text;
    },
  },

  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as SequencingTask,
  },
});
</script>

<style scoped>
.sequencing-editor {
  display: flex;
  gap: 1em;
}

.cards-list {
  display: flex;
  flex-direction: column;
  flex: 0 0 200px;
  max-width: 14em;
}

.cards-list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: transparent 2px solid;
  padding: 2px;
  cursor: pointer;
  user-select: none;
}

.selected-card {
  border: #0a78d1 2px solid;
}

.list-entry-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 11em;
}

.remove-card-button {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  border: 1px solid #28497c;
  color: #28497c;
  background: #fff;
  cursor: pointer;
}

.remove-card-button:hover {
  background: rgba(109, 114, 122, 0.2);
}

.add-card-button {
  margin: 0.8em 0 0 0;
}

.edited-memory-card {
  flex: 1 1 auto;
}
</style>
