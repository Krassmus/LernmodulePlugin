<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Bild') }}</legend>
      <div>
        <h4>{{ $gettext('Das Bild') }}</h4>
        <EditedImagePairImage v-if="image.imageUrl" :image="image" />
        <ImageUpload v-else @imageUploaded="onUploadImage" />
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
import ImageUpload from '@/components/ImageUpload.vue';
import { $gettext } from '@/language/gettext';
import {
  Image,
  ImagePair,
  ImagePairingTask,
  ImageSequencingTask,
} from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import produce from 'immer';
import EditedImagePairImage from '@/components/EditedImagePairImage.vue';

export default defineComponent({
  name: 'EditedImage',
  components: { ImageUpload },
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
    $gettext,
    onUploadImage(imageUrl: string): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.images[this.imageIndex].imageUrl = imageUrl;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
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
    taskDefinition: () => taskEditorStore.taskDefinition as ImageSequencingTask,
  },
});
</script>

<style scoped></style>
