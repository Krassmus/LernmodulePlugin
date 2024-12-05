<template>
  <span class="multimedia-element" v-disable-drag>
    <span v-if="element.type === 'image'" class="image-element-wrapper">
      <LazyImage
        v-if="element.file_id"
        :src="fileIdToUrl(element.file_id)"
        :alt="element.altText"
        class="image-element"
      />
      <span v-else class="image-element-placeholder" />
    </span>
    <span v-else-if="element.type === 'text'" class="text-element">
      <span> {{ element.content }} </span>
    </span>
  </span>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  fileIdToUrl,
  LernmoduleMultimediaElement,
} from '@/models/TaskDefinition';
import LazyImage from '@/components/LazyImage.vue';

export default defineComponent({
  name: 'MultimediaElement',
  components: { LazyImage },
  props: {
    element: {
      type: Object as PropType<LernmoduleMultimediaElement>,
      required: true,
    },
  },
  methods: { fileIdToUrl },
});
</script>

<style scoped>
.multimedia-element {
  width: 100%;
  height: 100%;

  display: flex;
  justify-content: center;
  align-items: center;
}

.image-element {
  width: 100%;
  height: 100%;

  border-radius: 0.25em;
}

.image-element-wrapper {
  width: 100%;
  height: 100%;

  display: flex;
  justify-content: center;
  align-items: center;
  background: white;

  box-shadow: inset 0 2px 74px 0 #cbd5de;
  border-radius: 0.25em;
}

.image-element-placeholder {
  width: 100%;
  height: 100%;
}

.text-element {
  width: 100%;
  height: 100%;

  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
