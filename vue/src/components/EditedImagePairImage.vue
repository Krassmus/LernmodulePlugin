<template>
  <img
    :src="fileIdToUrl(image.file_id)"
    :alt="image.altText"
    class="edited-image-pair-image"
    draggable="false"
  />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { fileIdToUrl, Image } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'EditedImagePairImage',
  props: {
    image: {
      type: Object as PropType<Image>,
      required: true,
    },
  },
  methods: { fileIdToUrl },
});
</script>

<style scoped>
.edited-image-pair-image {
  width: 8em;
  height: 8em;
  padding: 0.25em;
  object-fit: contain;
  box-shadow: inset 0 0 72px #cbd5de; /* x-offset, y-offset, blur-radius */
  border: solid 1px #cbd5de;
  border-radius: 0.25em;
}
</style>
