<template>
  <span>The image pairing viewer is here.</span>
  <div v-for="imagePair in this.task.imagePairs" :key="imagePair.uuid">
    <div>{{ imagePair.image1 }}</div>
    <div>{{ imagePair.image2 }}</div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ImagePairingTask } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'ImagePairingViewer',
  props: {
    task: {
      type: Object as PropType<ImagePairingTask>,
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
