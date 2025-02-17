<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import LazyImage from '@/components/LazyImage.vue';
import { Hotspot } from '@/models/FindTheHotspotsTask';
import { fileIdToUrl } from '@/models/TaskDefinition';
import { ImageElement } from '@/models/common';

export default defineComponent({
  name: 'ImageWithHotspots',
  components: { LazyImage },
  props: {
    hotspots: {
      type: Object as PropType<Hotspot[]>,
      required: true,
    },
    image: {
      type: Object as PropType<ImageElement>,
      required: true,
    },
  },
  methods: {
    fileIdToUrl,
    getHotspotStyle(hotspot: Hotspot): Partial<CSSStyleDeclaration> {
      if (hotspot.type === 'rectangle') {
        return {
          left: `${hotspot.x}%`,
          top: `${hotspot.y}%`,
          width: `${hotspot.width * 100}%`,
          height: `${hotspot.height * 100}%`,
        };
      } else {
        return {
          left: `${hotspot.x}%`,
          top: `${hotspot.y}%`,
          width: `${hotspot.diameter * 100}%`,
          aspectRatio: '1',
          borderRadius: '50%',
        };
      }
    },
  },
});
</script>

<template>
  <div class="image-and-hotspots-container-wrapper">
    <div class="image-and-hotspots-container">
      <div
        v-for="hotspot in hotspots"
        :key="hotspot.uuid"
        class="hotspot"
        :style="getHotspotStyle(hotspot)"
      />
      <LazyImage
        :src="fileIdToUrl(image.file_id)"
        :alt="image.altText"
        @click="deselectHotspot"
        class="image hotspots-image"
      />
    </div>
  </div>
</template>

<style scoped>
.image-and-hotspots-container-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-and-hotspots-container {
  position: relative;
  height: max-content;
}

.hotspots-image {
  user-select: none;
  max-height: 400px;
  width: 100%;
}

.hotspot {
  position: absolute;
  border: 2px dashed rgba(0, 0, 0, 0.7);
  background-color: rgba(255, 255, 255, 0.5);
}

.hotspot.selected {
  border: 2px dashed #0099ff;
}
</style>
