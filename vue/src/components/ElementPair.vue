<template>
  <div class="h5pElementPair">
    <div class="h5pElement">
      <EditedImagePairImage
        v-if="pair.draggableImage.imageUrl"
        :image="pair.draggableImage"
      />
      <FileUpload v-else @file-uploaded="onUploadDraggableImage" />
    </div>
    <div class="h5pElement">
      <EditedImagePairImage
        v-if="pair.targetImage.imageUrl"
        :image="pair.targetImage"
      />
      <FileUpload v-else @file-uploaded="onUploadTargetImage" />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { $gettext } from '@/language/gettext';
import { ImagePair } from '@/models/TaskDefinition';
import EditedImagePairImage from '@/components/EditedImagePairImage.vue';
import FileUpload from '@/components/FileUpload.vue';

export default defineComponent({
  name: 'ElementPair',
  components: { FileUpload, EditedImagePairImage },
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
  },
  computed: {},
});
</script>

<style scoped>
.h5pElementPair {
  display: flex;
  flex: 0 0 auto;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  border: rgba(0, 0, 0, 0) 2px solid;
  border-radius: 0.25em;
  gap: 0.25em;
  padding: 0.25em;
}

.h5pElement {
  border: #888888 1px solid;
  border-radius: 0.25em;
  padding: 0.25em;
}

.selected {
  border: #0a78d1 2px solid;
}
</style>
