<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Paar') }}</legend>
      <div>
        <h4>{{ $gettext('Das zu ziehende Bild') }}</h4>
        <EditedImagePairImage
          v-if="pair.draggableImage.file_id"
          :image="pair.draggableImage"
        />
        <FileUpload v-else @file-uploaded="onUploadDraggableImage" />
      </div>
      <label
        >{{ $gettext('Alternativer Text') }}
        <input
          type="text"
          :value="taskDefinition.imagePairs[pairIndex].draggableImage.altText"
          @input="onInputDraggableImageAltText"
        />
      </label>
      <div>
        <h4>{{ $gettext('Das Zielbild') }}</h4>
        <EditedImagePairImage
          v-if="pair.targetImage.file_id"
          :image="pair.targetImage"
        />
        <FileUpload v-else @file-uploaded="onUploadTargetImage" />
      </div>
      <label
        >{{ $gettext('Alternativer Text') }}
        <input
          type="text"
          :value="taskDefinition.imagePairs[pairIndex].targetImage.altText"
          @input="onInputTargetImageAltText"
        />
      </label>
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { $gettext } from '@/language/gettext';
import { Pair, PairingTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import produce from 'immer';
import EditedImagePairImage from '@/components/EditedImagePairImage.vue';
import FileUpload from '@/components/FileUpload.vue';
import { FileRef } from '@/routes/jsonApi';

export default defineComponent({
  name: 'EditedImagePair',
  components: { FileUpload, EditedImagePairImage },
  props: {
    pair: {
      type: Object as PropType<Pair>,
      required: true,
    },
    pairIndex: {
      type: Number as PropType<number>,
      required: true,
    },
  },
  data() {
    return {};
  },
  methods: {
    $gettext,
    onUploadDraggableImage(file: FileRef): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs[this.pairIndex].draggableElement.file_id = file.id;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
    onUploadTargetImage(file: FileRef): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs[this.pairIndex].targetElement.file_id = file.id;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
    onInputDraggableImageAltText(ev: InputEvent): void {
      const value = (ev.target as HTMLInputElement).value;
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs[this.pairIndex].draggableElement.altText = value;
      });
      taskEditorStore.performEdit({
        newTaskDefinition,
        undoBatch: { type: 'EditedDraggableImageAltText' },
      });
    },
    onInputTargetImageAltText(ev: InputEvent): void {
      const value = (ev.target as HTMLInputElement).value;
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.pairs[this.pairIndex].targetElement.altText = value;
      });
      taskEditorStore.performEdit({
        newTaskDefinition,
        undoBatch: { type: 'EditedTargetImageAltText' },
      });
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as PairingTask,
  },
});
</script>

<style scoped></style>
