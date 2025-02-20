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
        @click="onClickDeleteImage('first')"
        class="button trash settings-item"
      >
        {{ $gettext('Bild löschen') }}
      </button>

      <label style="align-self: stretch"
        >{{ $gettext('Bildbeschreibung') }}
        <input
          type="text"
          :value="card.first.altText"
          @input="onInputAltText($event, 'first')"
          class="settings-item"
        />
      </label>
    </div>
    <FileUpload v-else @file-uploaded="onUploadImage($event, 'first')" />

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
          @click="onClickDeleteImage('second')"
          class="button trash settings-item"
        >
          {{ $gettext('Bild löschen') }}
        </button>

        <label style="align-self: stretch"
          >{{ $gettext('Bildbeschreibung') }}
          <input
            type="text"
            :value="card.second.altText"
            @input="onInputAltText($event, 'second')"
            class="settings-item"
          />
        </label>
      </div>
      <FileUpload v-else @file-uploaded="onUploadImage($event, 'second')" />
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
import { defineComponent, PropType } from 'vue';
import { MemoryCard } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import FileUpload from '@/components/FileUpload.vue';
import { FileRef } from '@/routes/jsonApi';
import { v4 } from 'uuid';
import MultimediaElement from '@/components/MultimediaElement.vue';

export default defineComponent({
  name: 'MemoryEditorCard',
  components: { MultimediaElement, FileUpload },
  props: {
    card: {
      type: Object as PropType<MemoryCard>,
      required: true,
    },
  },
  methods: {
    $gettext,
    onUploadImage(file: FileRef, element: 'first' | 'second'): void {
      this.$emit('update:card', {
        ...this.card,
        [element]: {
          ...this.card[element],
          file_id: file.id,
        },
      });
    },
    onInputAltText(event: InputEvent, element: 'first' | 'second'): void {
      const altText = (event.target as HTMLInputElement).value;
      this.$emit('update:card', {
        ...this.card,
        [element]: {
          ...this.card[element],
          altText,
        },
      });
    },
    addSecondImage(): void {
      this.$emit('update:card', {
        ...this.card,
        second: {
          uuid: v4(),
          type: 'image',
          file_id: '',
          altText: '',
        },
      });
    },
    onClickDeleteImage(element: 'first' | 'second'): void {
      if (element === 'first') {
        this.$emit('update:card', {
          ...this.card,
          first: {
            ...this.card.first,
            file_id: '',
          },
        });
      } else {
        this.$emit('update:card', {
          ...this.card,
          second: undefined,
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
