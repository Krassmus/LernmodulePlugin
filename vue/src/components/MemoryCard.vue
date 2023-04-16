<template>
  <div class="h5pMemoryCard">
    <label> {{ card.altText }}</label>
    <img
      :src="card.imageUrl"
      :alt="card.altText"
      width="100"
      height="100"
      @click="onClickCard(card)"
    />
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
  border: 2px solid #0a78d1;
}
</style>
