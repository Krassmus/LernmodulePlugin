<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Bilderpaar') }}</legend>
      <div>
        <h4>{{ $gettext('Bild 1') }}</h4>
        <EditedImagePairImage
          v-if="pair.image1.imageUrl"
          :image="pair.image1"
        />
        <ImageUpload v-else @imageUploaded="onImage1Uploaded" />
      </div>
      <label
        >{{ $gettext('Alternativer Text') }}
        <input
          type="text"
          :value="taskDefinition.imagePairs[pairIndex].image1.altText"
          @input="onInputAltText1"
        />
      </label>
      <div>
        <h4>{{ $gettext('Bild 2') }}</h4>
        <EditedImagePairImage
          v-if="pair.image2.imageUrl"
          :image="pair.image2"
        />
        <ImageUpload v-else @imageUploaded="onImage2Uploaded" />
      </div>
      <label
        >{{ $gettext('Alternativer Text') }}
        <input
          type="text"
          :value="taskDefinition.imagePairs[pairIndex].image2.altText"
          @input="onInputAltText2"
        />
      </label>
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import ImageUpload from '@/components/ImageUpload.vue';
import { $gettext } from '../language/gettext';
import { ImagePair, ImagePairingTask } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import produce from 'immer';
import EditedImagePairImage from '@/components/EditedImagePairImage.vue';

export default defineComponent({
  name: 'EditedImagePair',
  components: { EditedImagePairImage, ImageUpload },
  props: {
    pair: {
      type: Object as PropType<ImagePair>,
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
    onImage1Uploaded(imageUrl: string): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.imagePairs[this.pairIndex].image1.imageUrl = imageUrl;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
    onImage2Uploaded(imageUrl: string): void {
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.imagePairs[this.pairIndex].image2.imageUrl = imageUrl;
      });
      taskEditorStore.performEdit({
        newTaskDefinition: newTaskDefinition,
        undoBatch: {},
      });
    },
    onInputAltText1(ev: InputEvent): void {
      const value = (ev.target as HTMLInputElement).value;
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.imagePairs[this.pairIndex].image1.altText = value;
      });
      taskEditorStore.performEdit({
        newTaskDefinition,
        undoBatch: { type: 'EditedAltText1' },
      });
    },
    onInputAltText2(ev: InputEvent): void {
      const value = (ev.target as HTMLInputElement).value;
      const newTaskDefinition = produce(this.taskDefinition, (draft) => {
        draft.imagePairs[this.pairIndex].image2.altText = value;
      });
      taskEditorStore.performEdit({
        newTaskDefinition,
        undoBatch: { type: 'EditedAltText2' },
      });
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as ImagePairingTask,
  },
});
</script>

<style scoped></style>
