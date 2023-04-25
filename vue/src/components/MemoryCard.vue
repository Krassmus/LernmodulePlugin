<template>
  <div class="memoryCard" v-if="card.flipped">
    <!--    <label class="cardDescription"> {{ card.altText }}</label>-->
    <div class="memoryCardFront">
      <img :src="card.imageUrl" :alt="card.altText" class="memoryImage" />
    </div>
  </div>
  <div v-else class="memoryCardBack">
    <img :src="pathForAsset('memoryCardBack', 'png')" class="memoryImage" />
  </div>
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
  methods: {
    pathForAsset(assetName: string, type: string) {
      return window.STUDIP.ASSETS_URL + assetName + '.' + type;
    },
  },
});
</script>

<style scoped>
.memoryCard {
  display: flex;
  flex-flow: column;
  align-items: center;
  aspect-ratio: 1;
  background: #e7ebf1;
  border: 2px solid #d0d7e3;
  color: rgb(40, 73, 124);
  padding: 1px 1px 1px 1px;
}

.memoryImage {
  max-width: 100%;
  max-height: 100%;
}

.memoryCardFront {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-grow: 1;
  width: 100%;
}

.memoryCardBack {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-grow: 1;
  width: 100%;
}
</style>
