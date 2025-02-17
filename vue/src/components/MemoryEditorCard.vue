<template>
  <fieldset class="memory-editor-card">
    <legend>{{ $gettext('Karte') }}</legend>

    <label>{{ $gettext(card.second ? 'Erstes Bild' : 'Bild') }}</label>
    <div
      v-if="card.first.type === 'image' && card.first.file_id"
      class="image-memory-card-container"
    >
      <div class="multimedia-element-wrapper">
        <MultimediaElement :element="card.first" />
      </div>

      <button
        type="button"
        @click="onClickDeleteImage(false)"
        class="button trash settings-item"
      >
        {{ $gettext('Bild löschen') }}
      </button>

      <label style="align-self: stretch"
        >{{ $gettext('Bildbeschreibung') }}
        <input
          type="text"
          :value="card.first.altText"
          @input="onInputAltText"
          class="settings-item"
        />
      </label>
    </div>
    <FileUpload v-else @file-uploaded="onUploadImage($event, false)" />

    <template v-if="card.second">
      <label>{{ $gettext('Zweites Bild') }}</label>

      <div
        v-if="card.second.type === 'image' && card.second.file_id"
        id="secondImage"
        class="image-memory-card-container"
      >
        <div class="multimedia-element-wrapper">
          <MultimediaElement
            :element="card.second"
            class="multimedia-element"
          />
        </div>

        <button
          type="button"
          @click="onClickDeleteImage(true)"
          class="button trash settings-item"
        >
          {{ $gettext('Bild löschen') }}
        </button>

        <label style="align-self: stretch"
          >{{ $gettext('Bildbeschreibung') }}
          <input
            type="text"
            :value="card.second.altText"
            @input="onInputAltText($event, true)"
            class="settings-item"
          />
        </label>
      </div>
      <FileUpload v-else @file-uploaded="onUploadImage($event, true)" />
    </template>

    <button
      v-else-if="card.first.file_id"
      type="button"
      @click="addSecondImage"
      class="button add-image-button"
    >
      {{ $gettext('Zweites Bild hinzufügen') }}
    </button>
  </fieldset>
</template>

<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import { fileIdToUrl, MemoryCard, MemoryTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import produce from 'immer';
import { $gettext } from '@/language/gettext';
import FileUpload from '@/components/FileUpload.vue';
import { FileRef } from '@/routes/jsonApi';
import { v4 } from 'uuid';
import MultimediaElement from '@/components/MultimediaElement.vue';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import { ImageElement } from '@/models/common';

export default defineComponent({
  name: 'MemoryEditorCard',
  components: { MultimediaElement, FileUpload },
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  props: {
    card: {
      type: Object as PropType<MemoryCard>,
      required: true,
    },
    cardIndex: {
      type: Number as PropType<number>,
      required: true,
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as MemoryTask,
  },
  methods: {
    fileIdToUrl,
    $gettext,
    onUploadImage(file: FileRef, second: boolean): void {
      if (!second) {
        const newTaskDefinition = produce(this.taskDefinition, (draft) => {
          if (draft.cards[this.cardIndex].first.type === 'image') {
            (draft.cards[this.cardIndex].first as ImageElement).file_id =
              file.id;
          }
        });
        this.taskEditor!.performEdit({
          newTaskDefinition: newTaskDefinition,
          undoBatch: {},
        });
      } else {
        const newTaskDefinition = produce(this.taskDefinition, (draft) => {
          const card = draft.cards?.[this.cardIndex];
          const secondCard = card?.second;
          if (secondCard && secondCard.type === 'image') {
            (draft.cards[this.cardIndex].second as ImageElement).file_id =
              file.id;
          }
        });
        this.taskEditor!.performEdit({
          newTaskDefinition: newTaskDefinition,
          undoBatch: {},
        });
      }
    },
    // This is how you write the @input event handler if you want undo-redo and reactivity to work
    onInputAltText(event: InputEvent, second: boolean): void {
      const altText = (event.target as HTMLInputElement).value;
      if (!second) {
        const newTaskDefinition = produce(this.taskDefinition, (draft) => {
          if (draft.cards[this.cardIndex].first.type === 'image') {
            (draft.cards[this.cardIndex].first as ImageElement).altText =
              altText;
          }
        });
        this.taskEditor!.performEdit({
          newTaskDefinition,
          undoBatch: { type: 'EditedFirstAltText' },
        });
      } else {
        const newTaskDefinition = produce(this.taskDefinition, (draft) => {
          const card = draft.cards?.[this.cardIndex];
          const secondCard = card?.second;

          if (secondCard && secondCard.type === 'image') {
            (draft.cards[this.cardIndex].second as ImageElement).altText =
              altText;
          }
        });
        this.taskEditor!.performEdit({
          newTaskDefinition,
          undoBatch: { type: 'EditedSecondAltText' },
        });
      }
    },
    addSecondImage(): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.cards[this.cardIndex].second = {
          uuid: v4(),
          type: 'image',
          file_id: '',
          altText: '',
        };
      });
      this.taskEditor!.performEdit({
        newTaskDefinition,
        undoBatch: { type: 'AddedSecondImage' },
      });
    },
    onClickDeleteImage(secondCard: boolean): void {
      if (!secondCard) {
        // delete the first card
        const newTaskDefinition = produce(this.taskDefinition, (draft) => {
          const card = draft.cards[this.cardIndex].first;
          if (card.type === 'image') {
            (draft.cards[this.cardIndex].first as ImageElement).file_id = '';
          }
        });

        this.taskEditor!.performEdit({
          newTaskDefinition: newTaskDefinition,
          undoBatch: {},
        });
      } else {
        // delete the second card
        const newTaskDefinition = produce(this.taskDefinition, (draft) => {
          const card = draft.cards[this.cardIndex];
          if (card && card.second) {
            delete card.second;
          }
        });

        this.taskEditor!.performEdit({
          newTaskDefinition: newTaskDefinition,
          undoBatch: {},
        });
      }
    },
  },
});
</script>

<style scoped>
.memory-editor-card {
  flex: 1 1 auto;
}

.add-image-button {
  margin: 0;
}

.multimedia-element-wrapper {
  width: 12em;
  height: 12em;

  display: flex;
  justify-content: center;
  align-items: center;

  border: 2px solid #dbe2e8;
  border-radius: 0.5em;
  background: white;

  padding: 0.5em;
  margin: 0;

  overflow: hidden;
}

.image-memory-card-container {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: flex-start;
  gap: 0.5em;
}

.settings-item {
  /* top | right | bottom | left */
  margin: 0.25em 0 0 0;
}
</style>
