<template>
  <div v-if="draggableImage" class="twoImages">
    <img
      :src="draggableImage.imageUrl"
      :alt="draggableImage.altText"
      class="smallImage"
    />

    <img
      :src="targetImage.imageUrl"
      :alt="targetImage.altText"
      class="smallImage"
    />
  </div>
  <div v-else class="oneImage">
    <img
      :src="targetImage.imageUrl"
      :alt="targetImage.altText"
      class="bigImage"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Image } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'ViewerImagePair',
  components: {},
  props: {
    draggableImage: {
      type: Object as PropType<Image>,
      required: false,
    },
    targetImage: {
      type: Object as PropType<Image>,
      required: true,
    },
  },
  computed: {},
  methods: {},
});
</script>

<style scoped>
.smallImage {
  width: 50%;
  height: 50%;
}

.bigImage {
  width: 100%;
  height: 100%;
}

.oneImage {
  display: flex;
  justify-content: center;
  width: 10em;
  padding: 6px;
  margin: 6px;
  height: 10em;
  border-radius: 6px 6px 6px 6px;
  cursor: pointer;
  border: 2px solid #dbe2e8;
  box-shadow: 2px 2px 0 2px rgba(203, 213, 222, 0.2);
}

.twoImages {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  width: 10em;
  padding: 6px;
  margin: 6px;
  height: 10em;
  border-radius: 6px 6px 6px 6px;
  cursor: pointer;
  border: 2px solid #dbe2e8;
  box-shadow: 2px 2px 0 2px rgba(203, 213, 222, 0.2);
}
</style>
