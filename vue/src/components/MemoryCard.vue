<template>
  <div class="h5pMemoryCard">
    <label> {{ card.altText }}</label>
    <div class="memoryImageContainer">
      <img
        :src="card.imageUrl"
        :alt="card.altText"
        class="memoryImage"
        @click="onClickCard(card)"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MemoryCard } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'MemoryCard',
  props: {
    card: {
      type: Object as PropType<MemoryCard>,
      required: true,
    },
  },
  computed: {},
  methods: {
    onClickCard(card: MemoryCard) {},
  },
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
