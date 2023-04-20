<template>
  <div class="h5pMemoryCard" v-if="card.flipped">
    <label> {{ card.altText }}</label>
    <div class="memoryImageContainer">
      <img :src="card.imageUrl" :alt="card.altText" class="memoryImage" />
    </div>
  </div>
  <div v-else class="h5pMemoryCard">sad</div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ViewerMemoryCard } from '@/components/MemoryViewer.vue';

export default defineComponent({
  name: 'MemoryCard',
  props: {
    card: {
      type: Object as PropType<ViewerMemoryCard>,
      required: true,
    },
  },
  computed: {},
  methods: {},
});
</script>

<style scoped>
.h5pMemoryCard {
  display: flex;
  flex-flow: column;
  border: 2px solid #c1c1c1;
  aspect-ratio: 1;
}

.memoryImage {
  max-width: 100%;
  max-height: 100%;
}

.memoryImageContainer {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-grow: 1;
  width: 100%;
  background: #e6e6e6;
}
</style>
