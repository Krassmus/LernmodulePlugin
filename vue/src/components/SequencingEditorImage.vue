<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Bild') }}</legend>
      <div>
        <h4>{{ $gettext('Das Bild') }}</h4>
        <img
          v-if="image.file_id"
          :src="fileIdToUrl(image.file_id)"
          :alt="image.altText"
          class="sequencing-editor-image"
        />
        <FileUpload v-else @file-uploaded="onUploadImage" />
      </div>
      <label
        >{{ $gettext('Alternativer Text') }}
        <input
          type="text"
          :value="taskDefinition.images[imageIndex].altText"
          @input="onInputImageAltText"
        />
      </label>
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { $gettext } from '@/language/gettext';
import { fileIdToUrl, Image, SequencingTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import produce from 'immer';
import FileUpload from '@/components/FileUpload.vue';
import { FileRef } from '@/routes/jsonApi';

export default defineComponent({
  name: 'SequencingEditorImage',
  components: { FileUpload },
  props: {
    image: {
      type: Object as PropType<Image>,
      required: true,
    },
    imageIndex: {
      type: Number as PropType<number>,
      required: true,
    },
  },
  data() {
    return {};
  },
  methods: {
    fileIdToUrl,
    $gettext,
    onUploadImage(file: FileRef): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.images[this.imageIndex].file_id = file.id;
      });
      taskEditorStore.performEdit({
        newTaskDefinition,
        undoBatch: {},
      });
    },
    onInputImageAltText(ev: InputEvent): void {
      const value = (ev.target as HTMLInputElement).value;
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.images[this.imageIndex].altText = value;
      });
      taskEditorStore.performEdit({
        newTaskDefinition,
        undoBatch: { type: 'EditedImageAltText' },
      });
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as SequencingTask,
  },
});
</script>

<style scoped>
.sequencing-editor-image {
  object-fit: contain;
  object-position: center;
  width: 8em;
  height: 8em;
}
</style>
