<template>
  <span class="multimedia-element">
    <span v-if="element.type === 'image'" class="image-element-wrapper">
      <lazy-image
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
  display: flex;
  justify-content: center;
  align-items: center;
}

.image-element {
  border-radius: 0.25em;
  max-width: 100%;
  max-height: 100%;
}

.image-element-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
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
