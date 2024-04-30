<template>
  <img
    :src="fileIdToUrl(image.file_id)"
    :alt="image.altText"
    class="edited-image-pair-image"
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
  data() {
    return {};
  },
  methods: { fileIdToUrl },
  computed: {},
});
</script>

<style scoped>
.edited-image-pair-image {
  object-fit: contain;
  object-position: center;
  width: 8em;
  height: 8em;
}
</style>
