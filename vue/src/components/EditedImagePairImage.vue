<template>
  <img :src="image.imageUrl" :alt="image.altText" />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Image } from '@/models/TaskDefinition';

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
  methods: {},
  computed: {},
});
</script>

<style scoped></style>
