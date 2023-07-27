<template>
  <div class="imagePairGrid">
    <div class="imagePairColumn">
      <div
        v-for="imagePair in this.task.imagePairs"
        :key="imagePair.uuid"
        class="imagePairItem"
      >
        <img
          :src="imagePair.image1.imageUrl"
          :alt="imagePair.image1.altText"
          class="pairImage"
        />
      </div>
    </div>

    <div class="imagePairColumn">
      <div
        v-for="imagePair in this.task.imagePairs"
        :key="imagePair.uuid"
        class="imagePairItem"
      >
        <img
          :src="imagePair.image2.imageUrl"
          :alt="imagePair.image2.altText"
          class="pairImage"
        />
      </div>
    </div>
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

<style scoped>
.pairImage {
  max-width: 100%;
  max-height: 100%;
}

.imagePairGrid {
  display: flex;
  flex-direction: row;
}

.imagePairColumn {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.imagePairItem {
  display: flex;
  justify-content: center;
  width: 10em;
  padding: 6px;
  height: 10em;
  float: left;
  border-radius: 6px 6px 6px 6px;
  margin: 6px;
  cursor: pointer;
  border: 2px solid #dbe2e8;
  box-shadow: 2px 2px 0 2px rgba(203, 213, 222, 0.2);
}

.imagePairItem:hover {
  border: 2px solid rgba(0, 187, 109, 0.93);
}
</style>
